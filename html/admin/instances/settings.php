<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Basic Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("assetsAssignmentsStatus_order", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$PAGEDATA['USERDATA']['instance']['assetStatus'] = $DBLIB->get("assetsAssignmentsStatus");


echo $TWIG->render('instances/instances_settings.twig', $PAGEDATA);
?>
