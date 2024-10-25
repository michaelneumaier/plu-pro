<?php

namespace App\Livewire;

use Livewire\Component;

class BarcodeGenerator extends Component
{
    public $code = ''; // Public property for binding the input

    // Encoding tables
    private $leftOddEncoding = [
        '0' => '0001101',
        '1' => '0011001',
        '2' => '0010011',
        '3' => '0111101',
        '4' => '0100011',
        '5' => '0110001',
        '6' => '0101111',
        '7' => '0111011',
        '8' => '0110111',
        '9' => '0001011',
    ];

    private $leftEvenEncoding = [
        '0' => '0100111',
        '1' => '0110011',
        '2' => '0011011',
        '3' => '0100001',
        '4' => '0011101',
        '5' => '0111001',
        '6' => '0000101',
        '7' => '0010001',
        '8' => '0001001',
        '9' => '0010111',
    ];

    private $rightEncoding = [
        '0' => '1110010',
        '1' => '1100110',
        '2' => '1101100',
        '3' => '1000010',
        '4' => '1011100',
        '5' => '1001110',
        '6' => '1010000',
        '7' => '1000100',
        '8' => '1001000',
        '9' => '1110100',
    ];

    /**
     * Calculates the UPC-A check digit.
     */
    private function calculateCheckDigit($upc)
    {
        if (strlen($upc) !== 11) {
            return null;
        }

        $sumOdd = 0;
        $sumEven = 0;

        for ($i = 0; $i < 11; $i++) {
            if ($i % 2 === 0) {
                $sumOdd += (int) $upc[$i];
            } else {
                $sumEven += (int) $upc[$i];
            }
        }

        $total = ($sumOdd * 3) + $sumEven;
        $checkDigit = (10 - ($total % 10)) % 10;

        return $checkDigit;
    }

    /**
     * Prepares the UPC-A code by padding and calculating the check digit.
     */
    private function prepareUPC()
    {
        // Remove any non-digit characters
        $cleanCode = preg_replace('/\D/', '', $this->code);

        // Determine if it's a PLU (typically 4-5 digits) or already UPC
        if (strlen($cleanCode) < 11) {
            // Pad with leading zeros to make it 11 digits
            $cleanCode = str_pad($cleanCode, 11, '0', STR_PAD_LEFT);
        } elseif (strlen($cleanCode) === 11) {
            // Already 11 digits
        } elseif (strlen($cleanCode) === 12) {
            // Already a complete UPC, return as is
            return $cleanCode;
        } else {
            // Invalid length
            return null;
        }

        // Calculate check digit
        $checkDigit = $this->calculateCheckDigit($cleanCode);

        if ($checkDigit === null) {
            return null;
        }

        // Append check digit to make a full 12-digit UPC
        return $cleanCode . $checkDigit;
    }

    /**
     * Converts the UPC-A code into barcode patterns.
     */
    private function generateBarcodePatterns($upc)
    {
        if (strlen($upc) !== 12) {
            return null;
        }

        $patterns = '';

        // Start Guard Pattern: 101
        $patterns .= '101';

        // Left Side: first 6 digits
        for ($i = 0; $i < 6; $i++) {
            $digit = $upc[$i];
            $patterns .= $this->leftOddEncoding[$digit];
        }

        // Center Guard Pattern: 01010
        $patterns .= '01010';

        // Right Side: last 6 digits
        for ($i = 6; $i < 12; $i++) {
            $digit = $upc[$i];
            $patterns .= $this->rightEncoding[$digit];
        }

        // End Guard Pattern: 101
        $patterns .= '101';

        return $patterns;
    }

    /**
     * Render the barcode.
     */
    public function render()
    {
        $upc = $this->prepareUPC();

        if ($upc === null) {
            $barcodeHTML = '<p class="text-red-500">Invalid PLU/UPC code. Please enter a 4-12 digit code.</p>';
        } else {
            $patterns = $this->generateBarcodePatterns($upc);

            if ($patterns === null) {
                $barcodeHTML = '<p class="text-red-500">Error generating barcode patterns.</p>';
            } else {
                // Convert the binary pattern into HTML divs with improved styling
                $barcodeHTML = '<div class="barcode flex items-end">';

                foreach (str_split($patterns) as $bit) {
                    if ($bit === '1') {
                        // Black bar
                        $barcodeHTML .= '<div class="bg-black" style="height: 100px; width: 2px; display: inline-block;"></div>';
                    } else {
                        // White space (transparent)
                        $barcodeHTML .= '<div class="bg-white" style="height: 100px; width: 2px; display: inline-block;"></div>';
                    }
                }

                $barcodeHTML .= '</div>';
            }
        }

        return view('livewire.barcode-generator', [
            'barcodeHTML' => $barcodeHTML,
            'upc' => $upc,
        ]);
    }
}
