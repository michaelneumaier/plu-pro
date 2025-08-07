import './bootstrap';
import './components/list-manager';
import './components/pwa-handler';
import barcodeScanner from './components/barcode-scanner';
import barcodeScannerWasm from './components/barcode-scanner-wasm';
import QRCode from 'qrcode';

// Make QRCode and barcodeScanner available globally for Alpine.js components
window.QRCode = QRCode;
window.barcodeScanner = barcodeScanner;
window.barcodeScannerWasm = barcodeScannerWasm;
