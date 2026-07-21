<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

/**
 * Generates QR code images from barcode strings.
 *
 * Uses endroid/qr-code to produce PNG files stored in storage/app/temp/qr/.
 * The caller is responsible for cleaning up temp files after use.
 */
class QrGeneratorService
{
    /**
     * Generate a QR code PNG file and return its absolute path.
     *
     * @param  string  $data  The barcode string to encode (full QR including lot/qty)
     * @param  int     $size  Size in pixels (square)
     * @return string  Absolute path to the generated PNG file
     */
    public function generateFile(string $data, int $size = 300): string
    {
        $qrCode = new QrCode(
            data: $data,
            size: $size,
            margin: 4
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Store in local disk under temp/qr/
        $filename = 'temp/qr/' . md5($data . microtime()) . '.png';
        Storage::disk('local')->put($filename, $result->getString());

        return Storage::disk('local')->path($filename);
    }

    /**
     * Generate a QR code as a base64 PNG data URI (for embedding in PDF/HTML).
     *
     * @param  string  $data  The barcode string to encode
     * @param  int     $size  Size in pixels (square)
     * @return string  Data URI "data:image/png;base64,..."
     */
    public function generateDataUri(string $data, int $size = 300): string
    {
        $qrCode = new QrCode(
            data: $data,
            size: $size,
            margin: 4
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
