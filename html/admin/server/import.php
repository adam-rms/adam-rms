<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Import", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("INSTANCES:IMPORT:ASSETS")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("instances.instances_id", "ASC");
$DBLIB->where("instances.instances_deleted", 0);
$instances = $DBLIB->get("instances", null, ["instances.instances_id", "instances.instances_name"]);
$PAGEDATA['instances'] = $instances;

$DBLIB->orderBy("assetCategories.instances_id", "ASC"); 
$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->where("assetCategories_deleted",0);
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$DBLIB->join("instances", "assetCategories.instances_id=instances.instances_id", "LEFT");
$PAGEDATA['categories'] = $DBLIB->get('assetCategories', null, ["assetCategoriesGroups.assetCategoriesGroups_name", "assetCategories.assetCategories_name", "assetCategories.assetCategories_id", "instances.instances_name"]);

echo $TWIG->render('server/import_asset.twig', $PAGEDATA);