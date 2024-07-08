<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_BARCODES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Scan Asset Barcodes", "BREADCRUMB" => false];

echo $TWIG->render('maintenance/barcode.twig', $PAGEDATA);
