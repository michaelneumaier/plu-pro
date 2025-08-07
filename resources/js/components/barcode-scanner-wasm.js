// Alpine component providing a ZXing WASM-based scanner using a Worker
import ZXingWorker from '../workers/zxing-wasm.worker.js?worker&inline';
const FORCE_MAIN_THREAD_WASM = true; // Set false to allow worker when same-origin is guaranteed
import { readBarcodes, prepareZXingModule } from 'zxing-wasm/reader';
export default function barcodeScannerWasm() {
    return {
        isScanning: false,
        isSupported: false,
        scannerType: 'zxing-wasm',
        status: 'Ready',
        isMobile: false,

        // internals
        stream: null,
        videoTrack: null,
        worker: null,
        useMainThreadWasm: false,
        decodeIntervalId: null,
        lastScannedCode: null,
        lastScannedTime: 0,
        debounceDelay: 2000,

        // ROI config (relative to video dimensions)
        roiFraction: { w: 0.6, h: 0.3 },
        cadenceMs: 80, // ~12.5 Hz

        torchSupported: false,
        torchEnabled: false,

        async init() {
            this.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            await this.checkCameraSupport();
            if (!this.isSupported) return;
            // Defer ZXing initialization until scanning starts
            this.useMainThreadWasm = (FORCE_MAIN_THREAD_WASM || import.meta?.env?.DEV);
        },

        // ensure WASM is ready when modal opens (handles Livewire/SPA navigations)
        ensureReadyOnOpenHandler: null,
        registerOpenHandlerOnce() {
            if (this.ensureReadyOnOpenHandler) return;
            this.ensureReadyOnOpenHandler = () => {
                if (this.useMainThreadWasm) {
                    // re-warm module in case of navigation/unmount
                    prepareZXingModule({ fireImmediately: true }).catch(() => { });
                }
            };
            window.addEventListener('scanner-open', this.ensureReadyOnOpenHandler, { passive: true });
        },

        async checkCameraSupport() {
            if (!navigator.mediaDevices?.getUserMedia) {
                this.status = 'Camera API not available';
                this.isSupported = false;
                return;
            }
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                this.status = 'Camera requires HTTPS';
                this.isSupported = false;
                return;
            }
            this.isSupported = true;
            this.status = 'ZXing WASM scanner ready';
        },

        setupWorker() {
            try {
                // Inline worker (blob) avoids cross-origin issues in dev
                this.worker = new ZXingWorker();
                this.worker.onmessage = (ev) => {
                    const msg = ev.data;
                    if (!msg) return;
                    if (msg.type === 'ready') {
                        // wasm initialized
                    } else if (msg.type === 'result') {
                        if (msg.ok && msg.text) {
                            this.handleScannedCode(msg.text);
                        }
                    } else if (msg.type === 'error') {
                        this.status = `WASM error: ${msg.error}`;
                    }
                };
                // Initialize with formats we care about
                this.worker.postMessage({
                    type: 'init',
                    options: {
                        tryHarder: true,
                        maxNumberOfSymbols: 1,
                        formats: ['EAN-13', 'UPC-A', 'UPC-E', 'DataBar', 'DataBarExpanded', 'DataBarLimited'],
                    },
                });
            } catch (e) {
                console.error('Failed to start WASM worker (inline). Falling back to main-thread WASM.', e);
                // Fall back to main-thread WASM decode without await (fire and forget)
                this.status = 'Initializing WASM (main thread)...';
                this.useMainThreadWasm = true;
                prepareZXingModule({ fireImmediately: true })
                    .then(() => { this.status = 'ZXing WASM ready (main thread)'; })
                    .catch((e3) => {
                        this.status = `Failed to init WASM: ${e3?.message || 'unknown error'}`;
                        this.isSupported = false;
                    });
            }
        },

        async startScanning() {
            if (!this.isSupported || this.isScanning) return;
            // Lazy-init ZXing when scanning actually starts
            if (this.useMainThreadWasm && !this.wasmReady) {
                this.status = 'Initializing decoder...';
                try {
                    await prepareZXingModule({ fireImmediately: true });
                    this.wasmReady = true;
                } catch (e) {
                    this.status = 'Decoder init failed';
                    return;
                }
            } else if (!this.useMainThreadWasm && !this.worker) {
                this.setupWorker();
            }
            this.isScanning = true;
            this.status = 'Starting camera...';

            // Acquire stream with high-res preferences
            let constraints = {
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1920, min: 1280 },
                    height: { ideal: 1440, min: 720 },
                    frameRate: { ideal: 30, min: 15 },
                },
            };
            try {
                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            } catch (e) {
                // basic fallback
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } } });
            }

            const video = this.$refs.video;
            video.srcObject = this.stream;
            video.setAttribute('playsinline', '');
            await video.play();

            this.videoTrack = this.stream.getVideoTracks()[0];
            this.checkTorchSupport();
            this.status = 'Scanning (WASM)...';

            // Set container aspect ratio for proper sizing (especially desktop)
            this.setupVideoContainer(video);
            video.addEventListener('loadedmetadata', () => this.setupVideoContainer(video), { once: true });

            // Start decode loop
            this.startDecodeLoop(video);
        },

        startDecodeLoop(video) {
            const tick = async () => {
                if (!this.isScanning || !video.videoWidth || !video.videoHeight) return;

                const { sx, sy, sw, sh } = this.computeRoi(video);
                try {
                    const bitmap = await createImageBitmap(video, sx, sy, sw, sh);
                    if (this.worker && !this.useMainThreadWasm) {
                        this.worker.postMessage({ type: 'decode', bitmap }, [bitmap]);
                    } else {
                        // Main-thread WASM path: ensure module is ready, then decode
                        if (!this.wasmReady) {
                            try {
                                await prepareZXingModule({ fireImmediately: true });
                                this.wasmReady = true;
                            } catch (_) {
                                // If we can't init, skip this tick
                                return;
                            }
                        }
                        const imageData = this.bitmapToImageData(bitmap);
                        try {
                            const results = await readBarcodes(imageData, {
                                tryHarder: true,
                                maxNumberOfSymbols: 1,
                                formats: ['EAN-13', 'UPC-A', 'UPC-E', 'DataBar', 'DataBarExpanded', 'DataBarLimited'],
                            });
                            if (results && results.length > 0) {
                                this.handleScannedCode(results[0].text);
                            }
                        } catch (_) {
                            // ignore decode errors per tick
                        }
                    }
                } catch (_) {
                    // ignore frame errors
                }
            };
            // use setInterval to throttle
            this.decodeIntervalId = setInterval(tick, this.cadenceMs);
        },

        bitmapToImageData(bitmap) {
            const { width, height } = bitmap;
            const canvas = document.createElement('canvas');
            canvas.width = width; canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(bitmap, 0, 0);
            const imageData = ctx.getImageData(0, 0, width, height);
            bitmap.close();
            return imageData;
        },

        computeRoi(video) {
            const vw = video.videoWidth;
            const vh = video.videoHeight;
            const sw = Math.floor(vw * this.roiFraction.w);
            const sh = Math.floor(vh * this.roiFraction.h);
            const sx = Math.floor((vw - sw) / 2);
            const sy = Math.floor((vh - sh) / 2);
            // Update overlay (if present)
            this.updateEnhancedScanOverlay(sx, sy, sw, sh, vw, vh);
            return { sx, sy, sw, sh };
        },

        handleScannedCode(text) {
            const now = Date.now();
            if (this.lastScannedCode === text && (now - this.lastScannedTime) < this.debounceDelay) return;
            this.lastScannedCode = text;
            this.lastScannedTime = now;

            const processed = this.processBarcodeData(text);
            this.status = `Scanned: ${processed.displayCode} (${processed.type})`;
            this.stopScanning();
            setTimeout(() => {
                this.$dispatch('barcode-scanned', {
                    code: processed.searchCode,
                    type: processed.type,
                    originalCode: text,
                });
            }, 50);
            if (navigator.vibrate) navigator.vibrate(100);
        },

        processBarcodeData(code) {
            if (/^\d{14}$/.test(code)) {
                const extractedPLU = code.substring(9, 13);
                const cleanedPLU = extractedPLU.replace(/^0+/, '') || extractedPLU;
                return { type: 'PLU', searchCode: cleanedPLU, displayCode: cleanedPLU, originalCode: code };
            }
            if (/^\d{12,13}$/.test(code)) {
                return { type: 'UPC', searchCode: code, displayCode: code, originalCode: code };
            }
            if (/^\d{4,5}$/.test(code)) {
                return { type: 'PLU', searchCode: code, displayCode: code, originalCode: code };
            }
            return { type: 'UNKNOWN', searchCode: code, displayCode: code, originalCode: code };
        },

        checkTorchSupport() {
            if (this.videoTrack) {
                try {
                    const caps = this.videoTrack.getCapabilities();
                    this.torchSupported = !!caps.torch;
                } catch { }
            }
        },

        async toggleTorch() {
            if (!this.torchSupported || !this.videoTrack) return;
            try {
                this.torchEnabled = !this.torchEnabled;
                await this.videoTrack.applyConstraints({ advanced: [{ torch: this.torchEnabled }] });
                this.status = `Flashlight ${this.torchEnabled ? 'ON' : 'OFF'}`;
                setTimeout(() => { if (this.isScanning) this.status = 'Scanning (WASM)...'; }, 800);
            } catch {
                this.torchEnabled = !this.torchEnabled;
            }
        },

        updateEnhancedScanOverlay(cropX, cropY, cropWidth, cropHeight, videoWidth, videoHeight) {
            if (!this.$refs.enhancedScanOverlay || !this.$refs.video) return;
            const overlay = this.$refs.enhancedScanOverlay;
            const video = this.$refs.video;
            const rect = video.getBoundingClientRect();
            const displayWidth = rect.width;
            const displayHeight = rect.height;
            const scaleX = displayWidth / videoWidth;
            const scaleY = displayHeight / videoHeight;
            overlay.style.left = cropX * scaleX + 'px';
            overlay.style.top = cropY * scaleY + 'px';
            overlay.style.width = cropWidth * scaleX + 'px';
            overlay.style.height = cropHeight * scaleY + 'px';
            overlay.classList.remove('hidden');
        },

        setupVideoContainer(video) {
            const container = video.parentElement;
            const vw = video.videoWidth;
            const vh = video.videoHeight;
            if (!container || !vw || !vh) return;
            const aspectRatio = vw / vh;
            // Handle mobile portrait where camera stream is landscape
            const isPortraitDisplay = window.innerWidth < window.innerHeight;
            const streamIsLandscape = vw > vh;
            if (this.isMobile && isPortraitDisplay && streamIsLandscape) {
                container.style.aspectRatio = (1 / aspectRatio).toString();
            } else {
                container.style.aspectRatio = aspectRatio.toString();
            }
            container.style.height = 'auto';
            container.style.minHeight = 'unset';
            container.style.maxHeight = '70vh';
        },

        async handleFileInput(file) {
            if (!file) return;
            this.status = 'Processing image...';
            // Post blob to worker directly
            this.worker?.postMessage({ type: 'decodeBlob', blob: file });
        },

        stopScanning() {
            this.isScanning = false;
            this.status = 'Ready';
            this.wasmReady = false;
            if (this.decodeIntervalId) {
                clearInterval(this.decodeIntervalId);
                this.decodeIntervalId = null;
            }
            if (this.stream) {
                try { this.stream.getTracks().forEach(t => t.stop()); } catch { }
                this.stream = null;
            }
            if (this.$refs.video) {
                try { this.$refs.video.pause(); } catch { }
                this.$refs.video.srcObject = null;
            }
        },

        destroy() {
            this.stopScanning();
            try { this.worker?.terminate(); } catch { }
            this.worker = null;
        },
    };
}


