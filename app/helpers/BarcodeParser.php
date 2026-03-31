<?php
// Barcode / Cedula Parser Helper

class BarcodeParser
{
    /**
     * Parse barcode scanner input and extract document number
     * Handles two formats:
     *   1. Plain number: "1234567890"
     *   2. Colombian cedula PDF417: encoded string with document at positions 2-12
     *
     * @param string $raw Raw input from barcode scanner
     * @return array ['documento' => string, 'formato' => 'simple'|'pdf417']
     */
    public static function parse($raw)
    {
        $raw = trim($raw);

        // Remove non-numeric characters
        $clean = preg_replace('/[^0-9]/', '', $raw);

        if (empty($clean)) {
            return ['documento' => '', 'formato' => 'desconocido'];
        }

        // If longer than 15 digits, it's likely a PDF417 encoded cedula
        if (strlen($clean) > 15) {
            // Colombian cedula PDF417 format:
            // Positions 2-12 contain the document number
            $documento = substr($clean, 2, 10);
            $documento = ltrim($documento, '0');

            return [
                'documento' => $documento,
                'formato'   => 'pdf417',
            ];
        }

        // Simple numeric format
        return [
            'documento' => ltrim($clean, '0'),
            'formato'   => 'simple',
        ];
    }

    /**
     * Validate that a document number looks reasonable
     */
    public static function isValidDocumento($documento)
    {
        $clean = preg_replace('/[^0-9]/', '', $documento);
        return strlen($clean) >= 5 && strlen($clean) <= 20;
    }
}
