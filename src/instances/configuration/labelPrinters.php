<?php
require_once __DIR__ . '/../../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:VIEW")) {
    die($TWIG->render('404.twig', $PAGEDATA));
}

$PAGEDATA['pageConfig'] = [
    "TITLE" => "Label Printers",
    "BREADCRUMB" => false
];

echo $TWIG->render('instances/configuration/instances_configuration_labelPrinters.twig', $PAGEDATA);