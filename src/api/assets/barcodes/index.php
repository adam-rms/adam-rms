<?php
require_once __DIR__ . '/../../apiHead.php';
header('Content-Type:' . 'image/svg+xml');
if (isset($_POST['size'])) $height = intval($_POST['size']);
else $height = 50;
if (isset($_POST['width'])) $width = intval($_POST['width']);
else $width = 1;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

if ($_POST['type'] == "QR_CODE") {
    $renderer = new ImageRenderer(
        new RendererStyle($height),
        new SvgImageBackEnd()
    );
    $writer = new Writer($renderer);
    echo $writer->writeString($bCMS->sanitizeString($_POST['barcode']), 'UTF-8');
} else {
    $generator = new Picqer\Barcode\BarcodeGeneratorSVG();
    /*
    * Supported Types
    TYPE_CODE_39
    TYPE_CODE_39_CHECKSUM
    TYPE_CODE_39E
    TYPE_CODE_39E_CHECKSUM
    TYPE_CODE_93
    TYPE_STANDARD_2_5
    TYPE_STANDARD_2_5_CHECKSUM
    TYPE_INTERLEAVED_2_5
    TYPE_INTERLEAVED_2_5_CHECKSUM
    TYPE_CODE_128
    TYPE_CODE_128_A
    TYPE_CODE_128_B
    TYPE_CODE_128_C
    TYPE_EAN_2
    TYPE_EAN_5
    TYPE_EAN_8
    TYPE_EAN_13
    TYPE_UPC_A
    TYPE_UPC_E
    TYPE_MSI
    TYPE_MSI_CHECKSUM
    TYPE_POSTNET
    TYPE_PLANET
    TYPE_RMS4CC
    TYPE_KIX
    TYPE_IMB
    TYPE_CODABAR
    TYPE_CODE_11
    TYPE_PHARMA_CODE
    TYPE_PHARMA_CODE_TWO_TRACKS
    */
    /*
    * Supported types in the zxing reader
    UPC-A	
    Code 39
    QR Code
    UPC-E
    Code 93
    Data Matrix
    EAN-8
    Code 128
    Aztec
    EAN-13
    Codabar
    */
    /*
    * Therefore the overlap is only:
    TYPE_CODE_39
    TYPE_CODE_93
    TYPE_CODE_128
    TYPE_EAN_8
    TYPE_EAN_13
    TYPE_UPC_A
    TYPE_UPC_E
    */
    switch ($_POST['type']) {
        case "EAN_8":
            $type = $generator::TYPE_EAN_8;
            break;
        case "EAN_13":
            $type = $generator::TYPE_EAN_13;
            break;
        case "CODE_39":
            $type = $generator::TYPE_CODE_39;
            break;
        case "CODE_93":
            $type = $generator::TYPE_CODE_93;
            break;
        case "CODE_128":
            $type = $generator::TYPE_CODE_128;
            break;
        case "UPC_A":
            $type = $generator::TYPE_UPC_A;
            break;
        case "UPC_E":
            $type = $generator::TYPE_UPC_E;
            break;
        default:
            $type = $generator::TYPE_CODE_128;
    }

    $barcodeValue = $bCMS->sanitizeString($_POST['barcode']);
    // CODE_39 supports only: 0-9, A-Z, space, and - . $ / + %
    if ($type === $generator::TYPE_CODE_39) {
        $barcodeValue = strtoupper($barcodeValue);
        $barcodeValue = preg_replace('/[^0-9A-Z .\$\/+%-]/', '', $barcodeValue);
    }

    try {
        echo $generator->getBarcode($barcodeValue, $type, $width, $height, "black");
    } catch (\Picqer\Barcode\Exceptions\InvalidCharacterException $e) {
        http_response_code(400);
        header('Content-Type: text/plain');
        echo 'Barcode value contains a character unsupported by the selected barcode type.';
    }
}
/** @OA\Post(
 *     path="/assets/barcodes/index.php", 
 *     summary="Generate Barcode Image", 
 *     description="Generate an SVG image of a given barcode value
", 
 *     operationId="generateBarcode", 
 *     tags={"barcodes"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Barcode Image",
 *         @OA\MediaType(
 *             mediaType="image/svg+xml", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="The Barcode type",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="barcode",
 *         in="query",
 *         description="Value of the Barcode",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="size",
 *         in="query",
 *         description="Size of returned barcode",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="width",
 *         in="query",
 *         description="Width of the returned barcode",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
