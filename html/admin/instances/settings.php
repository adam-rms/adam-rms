<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Basic Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(81)) die("Sorry - you can't access this page");

echo $TWIG->render('instances/instances_settings.twig', $PAGEDATA);
?>
