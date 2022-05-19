<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Export", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(133)) die($TWIG->render('404.twig', $PAGEDATA));


//["name" => Screen name, "value" => table name]
//Tables to allow initial export from
$PAGEDATA['tables'] = [
    ["name" => "Asset Types", "value" => "assettypes"],
    ["name" => "Assets", "value" => "assets"],
    ["name" => "Projects", "value" => "projects"],
    ["name" => "Locations", "value" => "locations"],
    ["name" => "Clients", "value" => "clients"],
];

echo $TWIG->render('instances/export.twig', $PAGEDATA);
?>