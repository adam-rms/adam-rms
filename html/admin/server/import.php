<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Import", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("INSTANCES:IMPORT:ASSETS")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("instances.instances_id", "ASC");
$DBLIB->where("instances.instances_deleted", 0);
$instances = $DBLIB->get("instances", null, ["instances.instances_id", "instances.instances_name"]);
$PAGEDATA['instances'] = $instances;

echo $TWIG->render('server/import_asset.twig', $PAGEDATA);