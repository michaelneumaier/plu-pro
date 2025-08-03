import { BrowserMultiFormatReader } from '@zxing/browser';

export default function barcodeScanner() {
    return {
        isScanning: false,
        isSupported: false,
        scannerType: null,
        status: 'Ready',
        permissionStatus: 'unknown',
        isMobile: false,

        // Internal state
        stream: null,
        detector: null,
        reader: null,
        animationFrame: null,
        lastScannedCode: null,
        lastScannedTime: 0,
        debounceDelay: 2000, // 2 seconds to prevent duplicate scans
        enhancedScanTimeout: null,

        // Camera controls
        torchSupported: false,
        torchEnabled: false,
        videoTrack: null,

        // Camera info for debugging
        actualConstraints: null,
        actualCapabilities: null,
        actualSettings: null,
        videoResolution: null,

        async init() {
            // Detect mobile device
            this.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            console.log('Is mobile device:', this.isMobile);
            
            // Check if running as installed PWA
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                               window.navigator.standalone || 
                               document.referrer.includes('android-app://');
            
            if (!isStandalone && this.isMobile) {
                console.log('📱 Running in browser - consider installing as PWA for better camera permission persistence');
            }
            
            // Check camera permission and support
            await this.checkCameraSupport();
            await this.checkPermissions();
        },

        async checkCameraSupport() {
            try {
                // Check if getUserMedia is available
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.status = 'Camera API not available';
                    return;
                }

                // Check if we're on HTTPS or localhost
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    this.status = 'Camera requires HTTPS';
                    return;
                }

                // FORCE ZXing only for testing GS1 DataBar
                this.scannerType = 'zxing';
                this.isSupported = true;
                this.status = 'ZXing scanner ready';

            } catch (error) {
                this.status = 'Error checking camera support';
            }
        },

        async checkPermissions() {
            try {
                if (navigator.permissions) {
                    const permission = await navigator.permissions.query({ name: 'camera' });
                    this.permissionStatus = permission.state;

                    permission.onchange = () => {
                        this.permissionStatus = permission.state;
                    };
                }
            } catch (error) {
                // Could not check camera permissions
            }
        },

        async startScanning() {
            if (!this.isSupported || this.isScanning) {
                return;
            }

            try {
                this.isScanning = true;
                this.status = 'Starting camera...';

                // TRY MULTIPLE CONSTRAINT STRATEGIES for better camera resolution
                let constraints;
                let stream = null;

                // Strategy 1: Ultra high resolution with close-up focus for barcode scanning
                try {
                    constraints = {
                        video: {
                            facingMode: { ideal: 'environment' },
                            // Push for maximum device resolution (4032x3024 on capable phones)
                            width: { ideal: 4032, min: 1920 },   // Prefer max device capability, fallback to 1920
                            height: { ideal: 3024, min: 1080 },  // Prefer max device capability, fallback to 1080
                            // Close-up focus settings for barcode scanning
                            focusMode: { ideal: 'continuous', min: 'manual' },
                            focusDistance: { ideal: 0.1 }, // Close focus for barcodes (10cm)
                            frameRate: { ideal: 30, min: 15 }
                        }
                    };
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (error) {

                    // Strategy 2: High resolution with focus (less aggressive than Strategy 1)
                    try {
                        constraints = {
                            video: {
                                facingMode: { ideal: 'environment' },
                                width: { ideal: 1920, min: 1280 },   // More conservative high-res
                                height: { ideal: 1440, min: 720 },   // More conservative high-res
                                focusMode: { ideal: 'continuous' },  // Focus without exact distance
                                aspectRatio: { ideal: 1.33 },        // Allow 4:3 aspect ratio
                                frameRate: { ideal: 30, min: 15 }
                            }
                        };
                        stream = await navigator.mediaDevices.getUserMedia(constraints);
                    } catch (error) {

                        // Strategy 3: Basic fallback - no constraints except facing mode
                        constraints = {
                            video: {
                                facingMode: { ideal: 'environment' }
                            }
                        };
                        stream = await navigator.mediaDevices.getUserMedia(constraints);
                    }
                }

                this.stream = stream;

                // Get video track for camera controls
                this.videoTrack = this.stream.getVideoTracks()[0];

                // Capture actual camera settings for debugging
                if (this.videoTrack) {
                    // Try to get capabilities and settings
                    try {
                        this.actualCapabilities = this.videoTrack.getCapabilities();
                    } catch (error) {
                        // Error getting capabilities
                    }

                    try {
                        this.actualSettings = this.videoTrack.getSettings();
                    } catch (error) {
                        // Error getting settings
                    }
                }

                this.actualConstraints = constraints;

                // Check if torch (flashlight) is supported
                this.checkTorchSupport();

                const video = this.$refs.video;
                video.srcObject = this.stream;
                video.setAttribute('playsinline', '');



                await video.play();

                // Wait a moment for video to load metadata
                video.addEventListener('loadedmetadata', () => {
                    // Store video resolution for debugging
                    this.videoResolution = {
                        width: video.videoWidth,
                        height: video.videoHeight
                    };

                    // DYNAMIC ASPECT RATIO: Set container to match actual camera stream aspect ratio
                    // Calculate and apply dynamic aspect ratio to eliminate black bars
                    const streamWidth = this.actualSettings.width;
                    const streamHeight = this.actualSettings.height;
                    const aspectRatio = streamWidth / streamHeight;

                    // Apply aspect ratio to video container
                    const videoContainer = video.parentElement;
                    if (videoContainer && aspectRatio) {
                        videoContainer.style.aspectRatio = aspectRatio.toString();
                        videoContainer.style.height = 'auto'; // Let aspect ratio determine height
                        videoContainer.style.minHeight = 'unset'; // Remove fixed min height
                        videoContainer.style.maxHeight = '70vh'; // Keep reasonable max height
                    }
                });

                if (this.scannerType === 'native') {
                    await this.startNativeScanning(video);
                } else {
                    await this.startZXingScanning(video);
                }

            } catch (error) {
                this.status = `Error: ${error.message}`;
                this.stopScanning();
            }
        },

        async startNativeScanning(video) {
            try {
                this.detector = new window.BarcodeDetector({
                    formats: ['ean_13', 'upc_a', 'upc_e']
                });

                this.status = 'Scanning with native detector...';

                const scan = async () => {
                    if (!this.isScanning) return;

                    try {
                        const codes = await this.detector.detect(video);

                        if (codes.length > 0) {
                            const code = codes[0];
                            this.handleScannedCode(code.rawValue);
                            return;
                        }
                    } catch (e) {
                        // Ignore transient detection errors
                    }

                    if (this.isScanning) {
                        this.animationFrame = requestAnimationFrame(scan);
                    }
                };

                this.animationFrame = requestAnimationFrame(scan);

            } catch (error) {
                throw error;
            }
        },

        async startZXingScanning(video) {
            try {
                this.reader = new BrowserMultiFormatReader();
                this.status = 'Scanning with ZXing...';

                // OPTIMIZED HINTS for small GS1 DataBar PLU stickers
                const hints = new Map();
                hints.set(2, 'RSS_14,RSS_EXPANDED,UPC_A,UPC_E,EAN_13'); // Prioritize GS1 DataBar formats first
                hints.set(3, true); // DecodeHintType.ASSUME_GS1 - Enable GS1 parsing
                hints.set(1, true); // DecodeHintType.TRY_HARDER - More aggressive scanning
                hints.set(4, true); // DecodeHintType.PURE_BARCODE - Assume image contains only barcode
                hints.set(5, true); // DecodeHintType.CHARACTER_SET - UTF-8 for international support
                hints.set(6, 0.5);  // DecodeHintType.ALLOWED_LENGTHS - Allow shorter codes

                // Apply hints to reader for small barcode optimization
                this.reader.hints = hints;

                const controls = await this.reader.decodeFromStream(
                    this.stream, // Use OUR pre-configured high-resolution stream
                    video,
                    (result, error, controls) => {
                        if (result) {
                            try {
                                this.handleScannedCode(result.getText());
                                // Don't stop controls immediately - let it continue scanning
                                // controls.stop();
                            } catch (handleError) {
                                alert(`Error processing scan: ${handleError.message}`);
                            }
                        }
                        if (error) {
                            // Handle errors quietly
                            if (error.name === 'AbortError') {
                                alert(`ZXing AbortError: ${error.message}`);
                            }
                        }
                    }
                );

                // Store controls for cleanup
                this.zxingControls = controls;

                // ENHANCED SCANNING for small PLU stickers - digital zoom + cropping
                // Disable on mobile due to performance issues
                if (!this.isMobile) {
                    this.startEnhancedSmallBarcodeScanning(video);
                }

                // Force update the status to show we're actively scanning
                this.status = 'Scanning for barcodes...';

            } catch (error) {
                throw error;
            }
        },

        // ENHANCED SMALL BARCODE SCANNING - Digital zoom and cropping for tiny PLU stickers
        startEnhancedSmallBarcodeScanning(video) {

            // Create canvas for image processing
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Enhanced scanning parameters
            const scanInterval = 1000; // Scan every 1 second (increased from 300ms)
            const zoomFactors = [4]; // Reduced zoom for better performance
            let currentZoomIndex = 0;

            const enhancedScan = () => {
                if (!this.isScanning || !video.videoWidth || !video.videoHeight) {
                    return;
                }

                try {
                    const zoomFactor = zoomFactors[currentZoomIndex];
                    const sourceWidth = video.videoWidth;
                    const sourceHeight = video.videoHeight;



                    // Calculate crop area (center of image)
                    const cropWidth = sourceWidth / zoomFactor;
                    const cropHeight = sourceHeight / zoomFactor;
                    const cropX = (sourceWidth - cropWidth) / 2;
                    const cropY = (sourceHeight - cropHeight) / 2;

                    // Update overlay to show crop area visually
                    this.updateEnhancedScanOverlay(cropX, cropY, cropWidth, cropHeight, sourceWidth, sourceHeight);

                    // Set canvas size for zoomed image
                    canvas.width = cropWidth * 2; // Reduced scale for better performance
                    canvas.height = cropHeight * 2;

                    // Draw cropped and scaled portion
                    ctx.drawImage(
                        video,
                        cropX, cropY, cropWidth, cropHeight, // Source crop area
                        0, 0, canvas.width, canvas.height    // Destination full canvas
                    );

                    // Apply image enhancement for better barcode detection
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    //this.enhanceImageForBarcode(imageData);
                    ctx.putImageData(imageData, 0, 0);

                    // Try to decode from enhanced image

                    canvas.toBlob(blob => {
                        const reader = new FileReader();
                        reader.onload = () => {
                            const img = new Image();
                            img.onload = () => {
                                if (this.reader) {
                                    this.reader.decodeFromImageElement(img)
                                        .then(result => {
                                            this.handleScannedCode(result.getText());
                                        })
                                        .catch(err => {
                                            // Enhanced scan failed
                                        });
                                }
                            };
                            img.src = reader.result;
                        };
                        reader.readAsDataURL(blob);
                    }, 'image/png');

                    // Cycle through zoom factors
                    currentZoomIndex = (currentZoomIndex + 1) % zoomFactors.length;

                } catch (error) {
                    // Enhanced scanning error
                }

                // Schedule next enhanced scan
                if (this.isScanning) {
                    this.enhancedScanTimeout = setTimeout(enhancedScan, scanInterval);
                }
            };

            // Start enhanced scanning
            this.enhancedScanTimeout = setTimeout(enhancedScan, scanInterval);
        },

        // IMAGE ENHANCEMENT for better barcode detection on small stickers
        enhanceImageForBarcode(imageData) {
            const data = imageData.data;

            // Convert to grayscale and increase contrast
            for (let i = 0; i < data.length; i += 4) {
                const gray = Math.round(0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);

                // Increase contrast (make darks darker, lights lighter)
                const enhanced = gray < 128 ? Math.max(0, gray - 40) : Math.min(255, gray + 40);

                data[i] = enhanced;     // Red
                data[i + 1] = enhanced; // Green  
                data[i + 2] = enhanced; // Blue
                // Alpha channel (i + 3) stays the same
            }
        },

        handleScannedCode(code) {
            try {

                const now = Date.now();

                // Debounce duplicate scans
                if (this.lastScannedCode === code &&
                    (now - this.lastScannedTime) < this.debounceDelay) {
                    return;
                }

                this.lastScannedCode = code;
                this.lastScannedTime = now;

                // Process the scanned code to determine type and extract relevant data
                const processedCode = this.processBarcodeData(code);


                this.status = `Scanned: ${processedCode.displayCode} (${processedCode.type})`;

                // Stop scanning after successful scan
                this.stopScanning();

                // Emit event to parent component after a brief delay
                setTimeout(() => {
                    this.$dispatch('barcode-scanned', {
                        code: processedCode.searchCode,
                        type: processedCode.type,
                        originalCode: code
                    });
                }, 100);

                // Provide haptic feedback on mobile
                if (navigator.vibrate) {
                    navigator.vibrate(100);
                }
            } catch (error) {
                console.error('Error in handleScannedCode:', error);
            }
        },

        processBarcodeData(code) {
            // Check if it's a GTIN-14 format (14 digits) - PLU barcode
            if (/^\d{14}$/.test(code)) {

                // For PLU 4593 -> 00684924045936
                // CORRECTED: The PLU is in positions 10-13 (zero-indexed: 9-12)
                const extractedPLU = code.substring(9, 13);

                // Remove leading zeros but keep at least 4 digits
                const cleanedPLU = extractedPLU.replace(/^0+/, '') || extractedPLU;

                return {
                    type: 'PLU',
                    searchCode: cleanedPLU,
                    displayCode: cleanedPLU,
                    originalCode: code
                };
            }

            // Check if it's a UPC format (12-13 digits)
            if (/^\d{12,13}$/.test(code)) {
                return {
                    type: 'UPC',
                    searchCode: code,
                    displayCode: code,
                    originalCode: code
                };
            }

            // Check if it's already a PLU code (4-5 digits)
            if (/^\d{4,5}$/.test(code)) {
                return {
                    type: 'PLU',
                    searchCode: code,
                    displayCode: code,
                    originalCode: code
                };
            }

            // Unknown format - pass through as-is for debugging
            return {
                type: 'UNKNOWN',
                searchCode: code,
                displayCode: code,
                originalCode: code
            };
        },

        isGS1DataBar(code) {
            // GS1 DataBar/RSS formats can produce various outputs:
            // - GTIN-14 format (14 digits)
            // - With GS1 Application Identifier "01" prefix
            // - RSS_14 format from ZXing

            const patterns = [
                /^01\d{14}$/.test(code),  // GS1 AI format: 01 + 14 digits
                /^\d{14}$/.test(code),    // Pure 14 digits
                /^RSS/.test(code),        // RSS prefix
                code.length > 10 && code.length < 20 // Reasonable length for DataBar
            ];

            const isDataBar = patterns.some(pattern => pattern);

            return isDataBar;
        },

        extractPLUFromGS1(code) {
            let workingCode = code;

            // Remove GS1 Application Identifier if present
            if (workingCode.startsWith('01')) {
                workingCode = workingCode.substring(2); // Remove "01" prefix
            }

            // Try multiple extraction strategies
            const extractionStrategies = [
                // Strategy 1: Correct GTIN-14 PLU extraction (positions 10-13, zero-indexed 9-12)
                () => {
                    if (workingCode.length === 14) {
                        return workingCode.substring(9, 13).replace(/^0+/, '') || '0';
                    }
                    return null;
                },

                // Strategy 2: Fallback - old incorrect positions (8-11) in case format varies
                () => {
                    if (workingCode.length === 14) {
                        return workingCode.substring(7, 11).replace(/^0+/, '') || '0';
                    }
                    return null;
                },

                // Strategy 3: Last 4-5 digits (common for simple encoding)
                () => {
                    if (workingCode.length >= 4) {
                        return workingCode.slice(-5, -1).replace(/^0+/, '') || workingCode.slice(-4).replace(/^0+/, '') || '0';
                    }
                    return null;
                },

                // Strategy 4: Look for 4-digit patterns in any position
                () => {
                    const matches = workingCode.match(/(\d{4})/g);
                    if (matches) {
                        for (const match of matches) {
                            const num = parseInt(match);
                            if (num >= 3000 && num <= 9999) {
                                return match.replace(/^0+/, '') || '0';
                            }
                        }
                    }
                    return null;
                }
            ];

            for (let i = 0; i < extractionStrategies.length; i++) {
                const plu = extractionStrategies[i]();
                if (plu) {
                    const pluNum = parseInt(plu);

                    // Validate PLU range
                    if (pluNum >= 3000 && pluNum <= 99999) {
                        return plu;
                    }
                }
            }

            return code;
        },

        stopScanning() {
            this.isScanning = false;
            this.status = 'Ready';

            // Clean up debug interval
            if (this.debugInterval) {
                clearInterval(this.debugInterval);
                this.debugInterval = null;
            }

            // Clean up enhanced scanning timeout
            if (this.enhancedScanTimeout) {
                clearTimeout(this.enhancedScanTimeout);
                this.enhancedScanTimeout = null;
            }

            // Clean up animation frame
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
                this.animationFrame = null;
            }

            // Clean up ZXing controls
            if (this.zxingControls) {
                this.zxingControls.stop();
                this.zxingControls = null;
            }

            // Clean up camera stream
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }

            // Clear video source
            if (this.$refs.video) {
                this.$refs.video.srcObject = null;
            }

            // Reset scanner components
            this.detector = null;
            this.reader = null;

            // Hide enhanced scan overlay
            if (this.$refs.enhancedScanOverlay) {
                this.$refs.enhancedScanOverlay.classList.add('hidden');
            }
        },

        // Update enhanced scan overlay to show crop area
        updateEnhancedScanOverlay(cropX, cropY, cropWidth, cropHeight, videoWidth, videoHeight) {
            if (!this.$refs.enhancedScanOverlay || !this.$refs.video) {
                return;
            }

            const overlay = this.$refs.enhancedScanOverlay;
            const video = this.$refs.video;

            // Get video element display dimensions
            const videoRect = video.getBoundingClientRect();
            const displayWidth = videoRect.width;
            const displayHeight = videoRect.height;

            // Calculate scale factor from actual video resolution to display size
            const scaleX = displayWidth / videoWidth;
            const scaleY = displayHeight / videoHeight;

            // Calculate overlay position and size (scaled to display dimensions)
            const overlayX = cropX * scaleX;
            const overlayY = cropY * scaleY;
            const overlayWidth = cropWidth * scaleX;
            const overlayHeight = cropHeight * scaleY;

            // Position and size the overlay
            overlay.style.left = overlayX + 'px';
            overlay.style.top = overlayY + 'px';
            overlay.style.width = overlayWidth + 'px';
            overlay.style.height = overlayHeight + 'px';

            // Show the overlay
            overlay.classList.remove('hidden');
        },

        toggleScanning() {
            if (this.isScanning) {
                this.stopScanning();
            } else {
                this.startScanning();
            }
        },

        // Handle file input for photo upload fallback
        async handleFileInput(file) {
            if (!file || !this.isSupported) return;

            try {
                this.status = 'Processing image...';

                const imageUrl = URL.createObjectURL(file);

                if (this.scannerType === 'native' && this.detector) {
                    // Use native detector for image
                    const img = new Image();
                    img.onload = async () => {
                        try {
                            const codes = await this.detector.detect(img);
                            if (codes.length > 0) {
                                this.handleScannedCode(codes[0].rawValue);
                            } else {
                                this.status = 'No barcode found in image';
                            }
                        } catch (error) {
                            this.status = 'Error scanning image';
                        }
                        URL.revokeObjectURL(imageUrl);
                    };
                    img.src = imageUrl;
                } else {
                    // Use ZXing for image
                    if (!this.reader) {
                        this.reader = new BrowserMultiFormatReader();
                    }

                    try {
                        const result = await this.reader.decodeFromImageUrl(imageUrl);
                        this.handleScannedCode(result.getText());
                    } catch (error) {
                        this.status = 'No barcode found in image';
                    }

                    URL.revokeObjectURL(imageUrl);
                }

            } catch (error) {
                console.error('File processing error:', error);
                this.status = 'Error processing image';
            }
        },

        // Check if torch (flashlight) is supported
        checkTorchSupport() {
            if (this.videoTrack) {
                const capabilities = this.videoTrack.getCapabilities();
                this.torchSupported = !!(capabilities.torch);
            }
        },

        // Toggle flashlight/torch
        async toggleTorch() {
            if (!this.torchSupported || !this.videoTrack) {
                console.warn('Torch not supported or no video track available');
                return;
            }

            try {
                this.torchEnabled = !this.torchEnabled;
                await this.videoTrack.applyConstraints({
                    advanced: [{ torch: this.torchEnabled }]
                });

                this.status = `Flashlight ${this.torchEnabled ? 'ON' : 'OFF'}`;

                // Reset status after a moment
                setTimeout(() => {
                    if (this.isScanning) {
                        this.status = 'Scanning...';
                    }
                }, 1000);

            } catch (error) {
                console.error('Failed to toggle torch:', error);
                this.torchEnabled = !this.torchEnabled; // Revert on error
            }
        },

        // Set optimal camera settings for close-up scanning
        async optimizeForCloseUp() {
            if (!this.videoTrack) return;

            try {
                const capabilities = this.videoTrack.getCapabilities();

                const constraints = {
                    advanced: []
                };

                // Set focus for close-up if supported
                if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
                    constraints.advanced.push({ focusMode: 'manual' });
                    if (capabilities.focusDistance) {
                        constraints.advanced.push({
                            focusDistance: Math.min(capabilities.focusDistance.max, 0.1)
                        });
                    }
                } else if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                    constraints.advanced.push({ focusMode: 'continuous' });
                }

                // Set zoom if supported
                if (capabilities.zoom) {
                    const zoomLevel = Math.min(capabilities.zoom.max, 2.0); // 2x zoom max
                    constraints.advanced.push({ zoom: zoomLevel });
                }

                if (constraints.advanced.length > 0) {
                    await this.videoTrack.applyConstraints(constraints);
                }

            } catch (error) {
                console.warn('Could not optimize camera for close-up:', error);
            }
        },

        // Cleanup when component is destroyed
        destroy() {
            this.stopScanning();
        }
    };
}