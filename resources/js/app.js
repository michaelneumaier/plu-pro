import './bootstrap';
import './components/list-manager';
import QRCode from 'qrcode';

// Make QRCode available globally for Alpine.js components
window.QRCode = QRCode;
