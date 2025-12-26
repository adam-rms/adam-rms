<?php

require_once __DIR__ . '/BrotherRaster.php';
require_once __DIR__ . '/LabelRenderer.php';

class CUPSPrinter
{
    private $DBLIB;
    private $printer;

    public function __construct($DBLIB, $printerId)
    {
        $this->DBLIB = $DBLIB;

        // Load printer config
        $DBLIB->where("labelPrinters_id", $printerId);
        $this->printer = $DBLIB->getOne("labelPrinters");

        if (!$this->printer) {
            throw new Exception("Printer not found");
        }
    }

    /**
     * Print a label
     * @param array $template Template definition
     * @param array $data Data to fill template
     * @return array Result with success status and message
     */
    public function print($template, $data)
    {
        try {
            // Create label renderer
            $renderer = new LabelRenderer(
                $template['labelTemplates_width'],
                $template['labelTemplates_height']
            );

            // Render template to image
            $image = $renderer->render(
                json_decode($template['labelTemplates_template'], true),
                $data
            );

            // Convert to Brother raster commands
            $raster = new BrotherRaster(
                $template['labelTemplates_width'],
                $template['labelTemplates_height']
            );
            $rasterData = $raster->imageToRaster($image);
            imagedestroy($image);

            // Write to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'label_') . '.prn';
            file_put_contents($tempFile, $rasterData);

            // Send to CUPS via lpr
            $result = $this->sendToCUPS($tempFile);

            // Clean up
            unlink($tempFile);

            return $result;
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function sendToCUPS($file)
    {
        $cupsServer = $this->printer['labelPrinters_cupsServer'];
        $cupsQueue = $this->printer['labelPrinters_cupsName'];

        // Build lpr command
        $cmd = "lpr";
        if ($cupsServer) {
            $cmd .= " -H " . escapeshellarg($cupsServer);
        }
        $cmd .= " -P " . escapeshellarg($cupsQueue);
        $cmd .= " -o raw"; // Send raw data without processing
        $cmd .= " " . escapeshellarg($file);
        $cmd .= " 2>&1"; // Capture stderr

        // Execute
        exec($cmd, $output, $returnCode);

        if ($returnCode === 0) {
            return [
                'success' => true,
                'message' => 'Print job sent successfully'
            ];
        } else {
            return [
                'success' => false,
                'error' => 'CUPS error: ' . implode("\n", $output)
            ];
        }
    }
}