import { BrowserMultiFormatReader } from '@zxing/browser';

export default function barcodeScanner() {
    return {
        isScanning: false,
        isSupported: false,
        scannerType: null,
        status: 'Ready',
        permissionStatus: 'unknown',
        
        // Internal state
        stream: null,
        detector: null,
        reader: null,
        animationFrame: null,
        lastScannedCode: null,
        lastScannedTime: 0,
        debounceDelay: 2000, // 2 seconds to prevent duplicate scans

        async init() {
            // Check camera permission and support
            await this.checkCameraSupport();
            await this.checkPermissions();
        },

        async checkCameraSupport() {
            try {
                // Check if getUserMedia is available
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.status = 'Camera API not available';
                    console.warn('getUserMedia not supported');
                    return;
                }

                // Check if we're on HTTPS or localhost
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    this.status = 'Camera requires HTTPS';
                    console.warn('Camera access requires HTTPS or localhost');
                    return;
                }

                // Check for native BarcodeDetector support
                if ('BarcodeDetector' in window) {
                    try {
                        const supported = await window.BarcodeDetector.getSupportedFormats();
                        const needed = ['ean_13', 'upc_a', 'upc_e', 'gs1_databar', 'gs1_databar_stacked'];
                        
                        if (needed.some(format => supported.includes(format))) {
                            this.scannerType = 'native';
                            this.isSupported = true;
                            this.status = 'Native barcode detection available (UPC + PLU)';
                            return;
                        }
                    } catch (e) {
                        console.warn('BarcodeDetector not fully supported:', e);
                    }
                }

                // Fallback to ZXing
                this.scannerType = 'zxing';
                this.isSupported = true;
                this.status = 'ZXing barcode scanner available (UPC + PLU)';

            } catch (error) {
                console.error('Error checking camera support:', error);
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
                console.warn('Could not check camera permissions:', error);
            }
        },

        async startScanning() {
            if (!this.isSupported || this.isScanning) return;

            try {
                this.isScanning = true;
                this.status = 'Starting camera...';

                // Request camera access with rear camera preference
                const constraints = {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };

                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                
                const video = this.$refs.video;
                video.srcObject = this.stream;
                video.setAttribute('playsinline', '');
                
                await video.play();

                if (this.scannerType === 'native') {
                    await this.startNativeScanning(video);
                } else {
                    await this.startZXingScanning(video);
                }

            } catch (error) {
                console.error('Error starting scanner:', error);
                this.status = `Error: ${error.message}`;
                this.stopScanning();
            }
        },

        async startNativeScanning(video) {
            try {
                this.detector = new window.BarcodeDetector({
                    formats: ['ean_13', 'upc_a', 'upc_e', 'gs1_databar', 'gs1_databar_stacked']
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
                console.error('Native scanning error:', error);
                throw error;
            }
        },

        async startZXingScanning(video) {
            try {
                this.reader = new BrowserMultiFormatReader();
                this.status = 'Scanning with ZXing...';
                
                const hints = new Map();
                hints.set(2, 'UPC_A,UPC_E,EAN_13,RSS_14'); // DecodeHintType.POSSIBLE_FORMATS (RSS_14 = GS1 DataBar)
                hints.set(3, true); // DecodeHintType.ASSUME_GS1
                hints.set(1, true); // DecodeHintType.TRY_HARDER
                
                const controls = await this.reader.decodeFromVideoDevice(
                    null, // Use default camera
                    video,
                    (result, error, controls) => {
                        if (result) {
                            this.handleScannedCode(result.getText());
                            controls.stop();
                        }
                        // Ignore decode errors - they're expected during scanning
                    }
                );
                
                // Store controls for cleanup
                this.zxingControls = controls;
                
            } catch (error) {
                console.error('ZXing scanning error:', error);
                throw error;
            }
        },

        handleScannedCode(code) {
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
            
            // Stop scanning immediately to prevent multiple scans
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
        },

        processBarcodeData(code) {
            // Check if it's a GS1 DataBar (GTIN-14 format)
            if (this.isGS1DataBar(code)) {
                const pluCode = this.extractPLUFromGS1(code);
                return {
                    type: 'PLU',
                    searchCode: pluCode,
                    displayCode: pluCode,
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
            
            // Unknown format - pass through as-is
            return {
                type: 'UNKNOWN',
                searchCode: code,
                displayCode: code,
                originalCode: code
            };
        },

        isGS1DataBar(code) {
            // GS1 DataBar typically produces GTIN-14 format
            // May start with "01" (Application Identifier) or be 14 digits
            return /^01\d{14}$/.test(code) || /^\d{14}$/.test(code);
        },

        extractPLUFromGS1(code) {
            let gtin14;
            
            // Remove GS1 Application Identifier if present
            if (code.startsWith('01')) {
                gtin14 = code.substring(2); // Remove "01" prefix
            } else {
                gtin14 = code;
            }
            
            // Extract PLU from GTIN-14
            // GTIN-14 structure: [Indicator][Company][PLU][Check]
            // For produce, PLU is typically in positions 8-11 or 9-12
            // This may need adjustment based on your PLU database structure
            
            if (gtin14.length === 14) {
                // Try to extract 4-digit PLU from positions 8-11
                let plu = gtin14.substring(7, 11);
                
                // Remove leading zeros
                plu = plu.replace(/^0+/, '') || '0';
                
                // Validate PLU range (typically 3000-9999 for produce)
                const pluNum = parseInt(plu);
                if (pluNum >= 3000 && pluNum <= 9999) {
                    return plu;
                }
                
                // Fallback: try 5-digit PLU from positions 7-11
                plu = gtin14.substring(6, 11);
                plu = plu.replace(/^0+/, '') || '0';
                
                const pluNum5 = parseInt(plu);
                if (pluNum5 >= 3000 && pluNum5 <= 99999) {
                    return plu;
                }
            }
            
            // If extraction fails, return original code
            return code;
        },

        stopScanning() {
            this.isScanning = false;
            this.status = 'Ready';
            
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

        // Cleanup when component is destroyed
        destroy() {
            this.stopScanning();
        }
    };
}