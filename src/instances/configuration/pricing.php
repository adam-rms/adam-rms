<?php
require_once __DIR__ . '/../../common/headSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Pricing Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['dayRateMultipliers'] = projectFinance::parseDayRateMultipliers($AUTH->data['instance']['instances_dayRateMultipliers']);

echo $TWIG->render('instances/configuration/instances_configuration_pricing.twig', $PAGEDATA);
?>
