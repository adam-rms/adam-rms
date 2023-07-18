<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:FULL_PERMISSIONS_IN_INSTANCE")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Instance Utilites", "BREADCRUMB" => false];

$DBLIB->orderBy("instances.instances_id", "ASC");
$DBLIB->where("instances.instances_deleted", 0);
$instances = $DBLIB->get("instances", null, ["instances.instances_id", "instances.instances_name"]);
$PAGEDATA['instances'] = $instances;

echo $TWIG->render('server/utilities.twig', $PAGEDATA);