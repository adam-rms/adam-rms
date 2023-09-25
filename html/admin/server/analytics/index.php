<?php
require_once __DIR__ . '/../../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Analytics", "BREADCRUMB" => false];
if (!$AUTH->serverPermissionCheck("VIEW-ANALYTICS")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['analyticsEventsCount'] = $DBLIB->getValue ("analyticsEvents", "count(analyticsEvents_id)");

echo $TWIG->render('server/analytics/analytics_index.twig', $PAGEDATA);
?>




