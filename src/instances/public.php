<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Public Site Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['publicData'] = $AUTH->data['instance']['publicData'];

$PAGEDATA['files'] = $bCMS->s3List(15, $AUTH->data['instance']['instances_id']);

echo $TWIG->render('instances/instances_public.twig', $PAGEDATA);
?>
