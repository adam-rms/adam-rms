<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "New Business", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("INSTANCES:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));

echo $TWIG->render('instances/instances_new.twig', $PAGEDATA);
?>
