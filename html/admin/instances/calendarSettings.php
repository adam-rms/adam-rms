<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Calendar Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['calendarSettings'] = $AUTH->data['instance']['calendarSettings'];

echo $TWIG->render('instances/instances_calendarSettings.twig', $PAGEDATA);
