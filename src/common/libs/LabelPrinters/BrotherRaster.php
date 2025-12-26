<?php

class BrotherRaster
{
    private $width;  // Label width in mm
    private $height; // Label height in mm (0 for continuous)

    const DPI = 180; // PT-P700 resolution
    const RASTER_MODE = 0x02; // Raster graphics mode

    public function __construct($widthMm, $heightMm = 0)
    {
        $this->width = $widthMm;
        $this->height = $heightMm;
    }

    /**
     * Convert GD image resource to Brother raster commands
     * @param resource $image GD image resource
     * @return string Binary raster command data
     */
    public function imageToRaster($image)
    {
        // Initialize printer
        $commands = $this->initializePrinter();

        // Set media width
        $commands .= $this->setMediaWidth($this->width);

        // Convert image to monochrome bitmap
        $bitmap = $this->convertToMonochrome($image);

        // Generate raster lines
        $commands .= $this->generateRasterData($bitmap);

        // Print command
        $commands .= $this->printCommand();

        return $commands;
    }

    private function initializePrinter()
    {
        // ESC @ - Initialize
        return "\x1B\x40";
    }

    private function setMediaWidth($widthMm)
    {
        // Convert mm to dots at 180 DPI
        $widthDots = (int)($widthMm * self::DPI / 25.4);

        // ESC i z n1 n2 - Set media width
        return "\x1B\x69\x7A" . chr($widthDots & 0xFF) . chr(($widthDots >> 8) & 0xFF);
    }

    private function convertToMonochrome($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Create monochrome image
        $mono = imagecreate($width, $height);
        imagecolorallocate($mono, 255, 255, 255); // White background
        $black = imagecolorallocate($mono, 0, 0, 0);

        // Convert to monochrome using threshold
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Grayscale conversion
                $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);

                // Threshold at 128
                if ($gray < 128) {
                    imagesetpixel($mono, $x, $y, $black);
                }
            }
        }

        return $mono;
    }

    private function generateRasterData($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate bytes per line (8 pixels per byte)
        $bytesPerLine = (int)ceil($width / 8);

        $rasterData = '';

        for ($y = 0; $y < $height; $y++) {
            // g - Raster line command
            $rasterData .= "\x67";
            $rasterData .= chr($bytesPerLine & 0xFF);
            $rasterData .= chr(($bytesPerLine >> 8) & 0xFF);

            // Pack pixels into bytes
            $lineData = '';
            for ($x = 0; $x < $width; $x += 8) {
                $byte = 0;
                for ($bit = 0; $bit < 8; $bit++) {
                    $px = $x + $bit;
                    if ($px < $width) {
                        $color = imagecolorat($image, $px, $y);
                        // Black pixel = 1, White = 0
                        if ($color == 0) {
                            $byte |= (1 << (7 - $bit));
                        }
                    }
                }
                $lineData .= chr($byte);
            }

            $rasterData .= $lineData;
        }

        return $rasterData;
    }

    private function printCommand()
    {
        // FF - Form feed / print
        return "\x0C";
    }
}