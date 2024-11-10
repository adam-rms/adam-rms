<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Custom Categories", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_CATEGORIES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->where("assetCategoriesGroups_deleted", 0);
$DBLIB->orderBy("assetCategoriesGroups_order", "ASC");
$PAGEDATA['categoryGroups'] = $DBLIB->get("assetCategoriesGroups");

foreach ($PAGEDATA['categoryGroups'] as $key => $group) {
  $DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
  $DBLIB->where("assetCategories_deleted", 0);
  $DBLIB->where("assetCategoriesGroups_id", $group['assetCategoriesGroups_id']);
  $PAGEDATA['categoryGroups'][$key]['categories'] = $DBLIB->get('assetCategories');
}

echo $TWIG->render('instances/customCategories.twig', $PAGEDATA);
