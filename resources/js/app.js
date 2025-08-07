import './bootstrap';
import './components/list-manager';
import './components/pwa-handler';
import barcodeScannerWasm from './components/barcode-scanner-wasm';
import QRCode from 'qrcode';

// Make QRCode and barcodeScanner available globally for Alpine.js components
window.QRCode = QRCode;
window.barcodeScannerWasm = barcodeScannerWasm;
