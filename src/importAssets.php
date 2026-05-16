<?php
require_once __DIR__ . '/common/headSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:IMPORT")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Import Assets", "BREADCRUMB" => false];

$DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
$DBLIB->where("assetCategories_deleted", 0);
$DBLIB->where("(assetCategories.instances_id IS NULL OR assetCategories.instances_id = ?)", [$AUTH->data['instance']['instances_id']]);
$DBLIB->where("(assetCategoriesGroups.instances_id IS NULL OR assetCategoriesGroups.instances_id = ?)", [$AUTH->data['instance']['instances_id']]);
$DBLIB->where("assetCategoriesGroups.assetCategoriesGroups_deleted", 0);
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['categories'] = $DBLIB->get('assetCategories', null, ["assetCategoriesGroups.assetCategoriesGroups_name", "assetCategories.assetCategories_name", "assetCategories.assetCategories_id"]);

echo $TWIG->render('importAssets.twig', $PAGEDATA);
