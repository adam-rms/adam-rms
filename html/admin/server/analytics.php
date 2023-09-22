<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Analytics", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("VIEW-ANALYTICS")) die($TWIG->render('404.twig', $PAGEDATA));

echo $TWIG->render('server/analytics.twig', $PAGEDATA);
?>
