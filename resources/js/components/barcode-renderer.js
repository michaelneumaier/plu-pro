// Client-side UPC-A barcode generation
// Replicates the logic from App\View\Components\Barcode (Barcode.php)

const leftOddEncoding = {
    '0': '0001101', '1': '0011001', '2': '0010011', '3': '0111101', '4': '0100011',
    '5': '0110001', '6': '0101111', '7': '0111011', '8': '0110111', '9': '0001011'
};

const rightEncoding = {
    '0': '1110010', '1': '1100110', '2': '1101100', '3': '1000010', '4': '1011100',
    '5': '1001110', '6': '1010000', '7': '1000100', '8': '1001000', '9': '1110100'
};

function calculateCheckDigit(upc11) {
    if (upc11.length !== 11) return null;

    let sumOdd = 0;
    let sumEven = 0;

    for (let i = 0; i < 11; i++) {
        const digit = parseInt(upc11[i], 10);
        if (i % 2 === 0) {
            sumOdd += digit;
        } else {
            sumEven += digit;
        }
    }

    const total = (sumOdd * 3) + sumEven;
    return (10 - (total % 10)) % 10;
}

function prepareUPC(code) {
    // Remove non-digit characters
    let cleanCode = code.replace(/\D/g, '');

    if (cleanCode.length < 11) {
        // Pad with leading zeros to make 11 digits
        cleanCode = cleanCode.padStart(11, '0');
    } else if (cleanCode.length === 11) {
        // Already 11 digits
    } else if (cleanCode.length === 12) {
        // Already a complete UPC, verify check digit
        const calc = calculateCheckDigit(cleanCode.substring(0, 11));
        if (calc !== null && calc !== parseInt(cleanCode[11], 10)) {
            cleanCode = cleanCode.substring(0, 11) + calc;
        }
        return cleanCode;
    } else if (cleanCode.length === 13) {
        // Convert EAN-13 to UPC-A by removing first digit (if 0)
        if (cleanCode[0] === '0') {
            cleanCode = cleanCode.substring(1);
            const calc = calculateCheckDigit(cleanCode.substring(0, 11));
            if (calc !== null && calc !== parseInt(cleanCode[11], 10)) {
                cleanCode = cleanCode.substring(0, 11) + calc;
            }
            return cleanCode;
        }
        return null;
    } else {
        return null;
    }

    const checkDigit = calculateCheckDigit(cleanCode);
    if (checkDigit === null) return null;

    return cleanCode + checkDigit;
}

function generateBarcodePattern(upc12) {
    if (upc12.length !== 12) return null;

    let pattern = '101'; // Start guard

    // Left side: first 6 digits
    for (let i = 0; i < 6; i++) {
        pattern += leftOddEncoding[upc12[i]] || '';
    }

    pattern += '01010'; // Center guard

    // Right side: last 6 digits
    for (let i = 6; i < 12; i++) {
        pattern += rightEncoding[upc12[i]] || '';
    }

    pattern += '101'; // End guard

    return pattern;
}

function renderBarcode(code, barWidth = '1.5px') {
    const upc = prepareUPC(String(code));
    if (!upc) {
        return '<div class="flex items-center justify-center h-10 bg-gray-100 rounded border-2 border-dashed border-gray-300"><span class="text-xs text-gray-500">No barcode</span></div>';
    }

    const pattern = generateBarcodePattern(upc);
    if (!pattern) {
        return '<div class="flex items-center justify-center h-10 bg-gray-100 rounded border-2 border-dashed border-gray-300"><span class="text-xs text-gray-500">No barcode</span></div>';
    }

    let bars = '';
    for (const bit of pattern) {
        const color = bit === '1' ? 'black' : 'white';
        bars += `<div style="width:${barWidth};height:100%;background:${color};"></div>`;
    }

    return `<div class="flex items-center justify-center h-10 bg-white p-2 rounded border border-gray-200"><div class="flex items-end h-full">${bars}</div></div>`;
}

// Expose globally for Alpine templates
window.renderBarcode = renderBarcode;
window.prepareUPC = prepareUPC;
