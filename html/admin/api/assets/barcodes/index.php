<?php
require_once __DIR__ . '/../../apiHead.php';
header('Content-Type:'.'image/png');
if (isset($_GET['size'])) $height = intval($_GET['size']);
else $height = 50;
if (isset($_GET['width'])) $width = intval($_GET['width']);
else $width = 1;

$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
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
 * Supported types in the app
QR_CODE
DATA_MATRIX
UPC_A
UPC_E
EAN_8
EAN_13
CODE_39
CODE_93
CODE_128
ITF
PDF_417
AZTEC
 */
/*
 * Therefore the overlap is only:
EAN_8
EAN_13
CODE_39
CODE_93
CODE_128
 */
switch ($_GET['type']) {
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
    default:
        $type = $generator::TYPE_CODE_128;
}

echo $generator->getBarcode($bCMS->sanitizeString($_GET['barcode']), $type, $width, $height, [0,0,0]);