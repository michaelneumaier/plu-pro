import './bootstrap';
import './components/list-manager';
import './components/pwa-handler';
import barcodeScanner from './components/barcode-scanner';
import QRCode from 'qrcode';

// Make QRCode and barcodeScanner available globally for Alpine.js components
window.QRCode = QRCode;
window.barcodeScanner = barcodeScanner;
