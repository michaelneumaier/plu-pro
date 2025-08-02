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
        
        // Camera controls
        torchSupported: false,
        torchEnabled: false,
        videoTrack: null,

        async init() {
            console.log('=== BARCODE SCANNER INITIALIZING ===');
            console.log('Scanner component loaded successfully');
            
            // Check camera permission and support
            await this.checkCameraSupport();
            await this.checkPermissions();
            
            console.log('Scanner initialization complete. Support:', this.isSupported, 'Type:', this.scannerType);
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

                // FORCE ZXing only for testing GS1 DataBar
                console.log('Testing: Forcing ZXing scanner for GS1 DataBar support');
                this.scannerType = 'zxing';
                this.isSupported = true;
                this.status = 'ZXing scanner (testing GS1 DataBar support)';

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

                // High-resolution camera constraints for iOS and Android
                const constraints = {
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920, min: 1280 }, // Force higher resolution
                        height: { ideal: 1080, min: 720 },
                        frameRate: { ideal: 30, min: 15 }, // Smooth video feed
                        aspectRatio: { ideal: 16/9 } // Standard aspect ratio
                    }
                };

                this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                
                // Get video track for camera controls
                this.videoTrack = this.stream.getVideoTracks()[0];
                
                // Check if torch (flashlight) is supported
                this.checkTorchSupport();
                
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
                console.error('Native scanning error:', error);
                throw error;
            }
        },

        async startZXingScanning(video) {
            try {
                console.log('=== STARTING ZXING SCANNING ===');
                this.reader = new BrowserMultiFormatReader();
                this.status = 'Scanning with ZXing...';
                
                const hints = new Map();
                hints.set(2, 'UPC_A,UPC_E,EAN_13,RSS_14,RSS_EXPANDED'); // DecodeHintType.POSSIBLE_FORMATS - Added GS1 DataBar formats
                hints.set(3, true); // DecodeHintType.ASSUME_GS1 - Enable GS1 parsing
                hints.set(1, true); // DecodeHintType.TRY_HARDER - More aggressive scanning
                
                console.log('ZXing configured with GS1 DataBar support:', {
                    formats: 'UPC_A,UPC_E,EAN_13,RSS_14,RSS_EXPANDED',
                    assumeGS1: true,
                    tryHarder: true
                });
                
                const controls = await this.reader.decodeFromVideoDevice(
                    null, // Use default camera
                    video,
                    (result, error, controls) => {
                        if (result) {
                            console.log('=== ZXING DECODE SUCCESS ===');
                            console.log('ZXing result object:', result);
                            console.log('ZXing getText():', result.getText());
                            
                            try {
                                this.handleScannedCode(result.getText());
                                // Don't stop controls immediately - let it continue scanning
                                // controls.stop();
                            } catch (handleError) {
                                console.error('Error in handleScannedCode:', handleError);
                                alert(`Error processing scan: ${handleError.message}`);
                            }
                        }
                        if (error) {
                            // Log more errors to understand what's happening
                            if (error.name === 'AbortError') {
                                console.error('ZXing AbortError:', error);
                                alert(`ZXing AbortError: ${error.message}`);
                            } else if (Math.random() < 0.05) { // 5% chance for other errors
                                console.log('ZXing decode attempt:', error.name, error.message);
                            }
                        }
                    }
                );
                
                // Store controls for cleanup
                this.zxingControls = controls;
                console.log('ZXing scanning started successfully');
                
            } catch (error) {
                console.error('ZXing scanning error:', error);
                throw error;
            }
        },

        handleScannedCode(code) {
            try {
                // SUPER VERBOSE LOGGING - Force alerts and console logs
                console.log('=== BARCODE SCAN EVENT ===');
                console.log('Raw scanned code:', code);
                console.log('Code length:', code.length);
                console.log('Code type:', typeof code);
                console.log('Code as string:', String(code));
                
                // Also show an alert so we can see it on mobile
                alert(`SCANNED: ${code} (Length: ${code.length})`);
                
                const now = Date.now();
                
                // Debounce duplicate scans
                if (this.lastScannedCode === code && 
                    (now - this.lastScannedTime) < this.debounceDelay) {
                    console.log('Duplicate scan ignored');
                    return;
                }
                
                this.lastScannedCode = code;
                this.lastScannedTime = now;
                
                // Process the scanned code to determine type and extract relevant data
                const processedCode = this.processBarcodeData(code);
                
                console.log('Processed code result:', processedCode);
                
                this.status = `Scanned: ${processedCode.displayCode} (${processedCode.type})`;
                
                // DON'T stop scanning immediately to avoid AbortError - let it continue
                // this.stopScanning();
                
                // Emit event to parent component after a brief delay
                setTimeout(() => {
                    console.log('Dispatching barcode-scanned event with:', processedCode);
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
                alert(`Error handling scan: ${error.message}`);
            }
        },

        processBarcodeData(code) {
            console.log('DEBUG: Processing scanned code:', code);
            
            // Check if it's a GTIN-14 format (14 digits) - PLU barcode
            if (/^\d{14}$/.test(code)) {
                console.log('DEBUG: Detected 14-digit GTIN-14 format (PLU barcode)');
                
                // For PLU 4593 -> 00684924045936
                // The PLU appears to be in positions 8-11 (zero-indexed: 7-10)
                const extractedPLU = code.substring(7, 11);
                console.log('DEBUG: Extracted PLU from positions 7-10:', extractedPLU);
                
                // Remove leading zeros but keep at least 4 digits
                const cleanedPLU = extractedPLU.replace(/^0+/, '') || extractedPLU;
                console.log('DEBUG: Cleaned PLU:', cleanedPLU);
                
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
            console.log('Testing if GS1 DataBar:', code);
            
            const patterns = [
                /^01\d{14}$/.test(code),  // GS1 AI format: 01 + 14 digits
                /^\d{14}$/.test(code),    // Pure 14 digits
                /^RSS/.test(code),        // RSS prefix
                code.length > 10 && code.length < 20 // Reasonable length for DataBar
            ];
            
            const isDataBar = patterns.some(pattern => pattern);
            console.log('GS1 DataBar check result:', isDataBar, 'Patterns:', patterns);
            
            return isDataBar;
        },

        extractPLUFromGS1(code) {
            console.log('Extracting PLU from GS1/DataBar code:', code);
            
            let workingCode = code;
            
            // Remove GS1 Application Identifier if present
            if (workingCode.startsWith('01')) {
                workingCode = workingCode.substring(2); // Remove "01" prefix
                console.log('Removed GS1 AI prefix, working with:', workingCode);
            }
            
            // Try multiple extraction strategies
            const extractionStrategies = [
                // Strategy 1: Standard GTIN-14 PLU extraction (positions 8-11)
                () => {
                    if (workingCode.length === 14) {
                        return workingCode.substring(7, 11).replace(/^0+/, '') || '0';
                    }
                    return null;
                },
                
                // Strategy 2: Alternative PLU positions (6-10)
                () => {
                    if (workingCode.length === 14) {
                        return workingCode.substring(6, 10).replace(/^0+/, '') || '0';
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
                    console.log(`Strategy ${i + 1} extracted PLU:`, plu, 'Number:', pluNum);
                    
                    // Validate PLU range
                    if (pluNum >= 3000 && pluNum <= 99999) {
                        console.log('Valid PLU found:', plu);
                        return plu;
                    }
                }
            }
            
            console.log('PLU extraction failed, returning original code:', code);
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

        // Check if torch (flashlight) is supported
        checkTorchSupport() {
            if (this.videoTrack) {
                const capabilities = this.videoTrack.getCapabilities();
                this.torchSupported = !!(capabilities.torch);
                console.log('Torch support:', this.torchSupported);
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
                
                console.log('Torch toggled:', this.torchEnabled);
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
                console.log('Camera capabilities:', capabilities);
                
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
                    console.log('Applied close-up camera settings');
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