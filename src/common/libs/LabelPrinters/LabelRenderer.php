<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class LabelRenderer
{
    private $widthMm;
    private $heightMm;
    private $dpi;

    public function __construct($widthMm, $heightMm, $dpi = 180)
    {
        $this->widthMm = $widthMm;
        $this->heightMm = $heightMm;
        $this->dpi = $dpi;
    }

    /**
     * Render a label template with data
     * @param array $template Template definition from database
     * @param array $data Data to fill into template
     * @return resource GD image resource
     */
    public function render($template, $data)
    {
        // Calculate pixel dimensions
        $widthPx = $this->mmToPixels($this->widthMm);
        $heightPx = $this->heightMm > 0 ? $this->mmToPixels($this->heightMm) : $widthPx; // Default square

        // Create canvas
        $image = imagecreate($widthPx, $heightPx);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);

        // Render each element
        foreach ($template['elements'] as $element) {
            $this->renderElement($image, $element, $data, $black);
        }

        return $image;
    }

    private function renderElement($image, $element, $data, $color)
    {
        // Get data value for this element
        $value = isset($element['field']) && isset($data[$element['field']])
            ? $data[$element['field']]
            : ($element['text'] ?? '');

        // Convert mm positions to pixels
        $x = $this->mmToPixels($element['x']);
        $y = $this->mmToPixels($element['y']);

        switch ($element['type']) {
            case 'text':
                $this->renderText($image, $value, $x, $y, $element, $color);
                break;
            case 'barcode':
                $this->renderBarcode($image, $value, $x, $y, $element);
                break;
            case 'qrcode':
                $this->renderQRCode($image, $value, $x, $y, $element);
                break;
        }
    }

    private function renderText($image, $text, $x, $y, $element, $color)
    {
        $size = $element['size'] ?? 12;
        $font = 5; // GD built-in font (or use TTF font)

        if (isset($element['bold']) && $element['bold']) {
            // Bold simulation: draw text multiple times with slight offset
            imagestring($image, $font, $x, $y, $text, $color);
            imagestring($image, $font, $x + 1, $y, $text, $color);
        } else {
            imagestring($image, $font, $x, $y, $text, $color);
        }
    }

    private function renderBarcode($image, $value, $x, $y, $element)
    {
        $format = $element['format'] ?? 'CODE_128';
        $widthMm = $element['width'] ?? 20;
        $heightMm = $element['height'] ?? 8;

        // Generate barcode using existing Picqer library
        $generator = new BarcodeGeneratorPNG();

        // Map format names
        $formatMap = [
            'UPC_E' => $generator::TYPE_UPC_E,
            'UPC_A' => $generator::TYPE_UPC_A,
            'CODE_128' => $generator::TYPE_CODE_128,
            'CODE_39' => $generator::TYPE_CODE_39,
            'EAN_13' => $generator::TYPE_EAN_13,
        ];

        $barcodeType = $formatMap[$format] ?? $generator::TYPE_CODE_128;
        $widthFactor = 2;
        $heightPx = $this->mmToPixels($heightMm);

        try {
            $barcodePng = $generator->getBarcode($value, $barcodeType, $widthFactor, $heightPx);
            $barcodeImage = imagecreatefromstring($barcodePng);

            // Copy barcode onto main image
            imagecopy(
                $image,
                $barcodeImage,
                $x,
                $y,
                0,
                0,
                imagesx($barcodeImage),
                imagesy($barcodeImage)
            );
            imagedestroy($barcodeImage);
        } catch (Exception $e) {
            // If barcode generation fails, render error text
            imagestring($image, 2, $x, $y, "Error: " . $e->getMessage(), 0);
        }
    }

    private function renderQRCode($image, $value, $x, $y, $element)
    {
        $sizeMm = $element['size'] ?? 10;
        $sizePx = $this->mmToPixels($sizeMm);

        // Generate QR code using BaconQrCode
        $renderer = new ImageRenderer(
            new RendererStyle($sizePx),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);

        try {
            $qrPng = $writer->writeString($value);
            $qrImage = imagecreatefromstring($qrPng);

            // Copy QR code onto main image
            imagecopy(
                $image,
                $qrImage,
                $x,
                $y,
                0,
                0,
                imagesx($qrImage),
                imagesy($qrImage)
            );
            imagedestroy($qrImage);
        } catch (Exception $e) {
            // Render error text
            imagestring($image, 2, $x, $y, "QR Error", 0);
        }
    }

    private function mmToPixels($mm)
    {
        return (int)($mm * $this->dpi / 25.4);
    }
}