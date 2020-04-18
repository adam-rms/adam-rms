<?php
require_once __DIR__ . '/common/headSecure.php';

if (!$AUTH->instancePermissionCheck(17)) die("Sorry - you can't access this page");
$PAGEDATA['pageConfig'] = ["TITLE" => "Add Asset", "BREADCRUMB" => false];


$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
$PAGEDATA['manufacturers'] = $DBLIB->get('manufacturers', null, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);

$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['categories'] = $DBLIB->get('assetCategories');

echo $TWIG->render('newAsset.twig', $PAGEDATA);
?>
