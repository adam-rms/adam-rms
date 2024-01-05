<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Stats", "BREADCRUMB" => true];

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_STATS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['WIDGETS'] = new statsWidgets(explode(",",$AUTH->data['users_widgets']),true);

echo $TWIG->render('instances/instances_stats.twig', $PAGEDATA);
?>
