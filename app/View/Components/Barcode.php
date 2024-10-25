<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;


class Barcode extends Component
{
    /**
     * The PLU or UPC code.
     *
     * @var string
     */
    public $code;

    /**
     * The processed 12-digit UPC-A code.
     *
     * @var string|null
     */
    public $upc;

    /**
     * The barcode pattern.
     *
     * @var string|null
     */
    public $pattern;

    /**
     * Create a new component instance.
     *
     * @param string $code
     * @return void
     */
    public function __construct($code)
    {
        $this->code = $code;
        $this->upc = $this->prepareUPC($code);
        $this->pattern = $this->upc ? $this->generateBarcodePattern($this->upc) : null;
    }

    /**
     * Prepare the UPC-A code by padding and calculating the check digit.
     *
     * @param string $code
     * @return string|null
     */
    private function prepareUPC($code)
    {
        // Remove any non-digit characters
        $cleanCode = preg_replace('/\D/', '', $code);

        // Determine if it's a PLU (typically 4-5 digits) or already UPC
        if (strlen($cleanCode) < 11) {
            // Pad with leading zeros to make it 11 digits
            $cleanCode = str_pad($cleanCode, 11, '0', STR_PAD_LEFT);
        } elseif (strlen($cleanCode) === 11) {
            // Already 11 digits
        } elseif (strlen($cleanCode) === 12) {
            // Already a complete UPC, verify check digit
            $calculatedCheckDigit = $this->calculateCheckDigit(substr($cleanCode, 0, 11));
            if ($calculatedCheckDigit != substr($cleanCode, -1)) {
                // Invalid check digit, recalculate
                $cleanCode = substr($cleanCode, 0, 11) . $this->calculateCheckDigit(substr($cleanCode, 0, 11));
            }
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
     * Calculate the UPC-A check digit.
     *
     * @param string $upc
     * @return int|null
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
     * Generate the barcode pattern for UPC-A.
     *
     * @param string $upc
     * @return string|null
     */
    private function generateBarcodePattern($upc)
    {
        // Encoding tables
        $leftOddEncoding = [
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

        $rightEncoding = [
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

        if (strlen($upc) !== 12) {
            return null;
        }

        $patterns = '';

        // Start Guard Pattern: 101
        $patterns .= '101';

        // Left Side: first 6 digits
        for ($i = 0; $i < 6; $i++) {
            $digit = $upc[$i];
            $patterns .= $leftOddEncoding[$digit] ?? '';
        }

        // Center Guard Pattern: 01010
        $patterns .= '01010';

        // Right Side: last 6 digits
        for ($i = 6; $i < 12; $i++) {
            $digit = $upc[$i];
            $patterns .= $rightEncoding[$digit] ?? '';
        }

        // End Guard Pattern: 101
        $patterns .= '101';

        return $patterns;
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render(): View|Closure|string
    {
        return view('components.barcode');
    }
}
