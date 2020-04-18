<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Basic Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(81)) die($TWIG->render('404.twig', $PAGEDATA));

echo $TWIG->render('instances/instances_settings.twig', $PAGEDATA);
?>
