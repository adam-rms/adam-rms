<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Stats", "BREADCRUMB" => true];

if (!$AUTH->instancePermissionCheck(80)) die("Sorry - you can't access this page");

$PAGEDATA['WIDGETS'] = new statsWidgets(explode(",",$AUTH->data['users_widgets']));

echo $TWIG->render('instances/instances_stats.twig', $PAGEDATA);
?>
