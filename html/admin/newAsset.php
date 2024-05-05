<?php
require_once __DIR__ . '/common/headSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));
$PAGEDATA['pageConfig'] = ["TITLE" => "Add Asset", "BREADCRUMB" => false];


$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
$PAGEDATA['manufacturers'] = $DBLIB->get('manufacturers', null, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);

$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->where("assetCategories_deleted",0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['categories'] = $DBLIB->get('assetCategories');

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$assetCapacity = $DBLIB->getvalue("instances", "instances_assetLimit");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets_deleted", 0);
$assetUsed = $DBLIB->getValue("assets", "COUNT(assets_id)");
if ($assetCapacity > 0 and $assetUsed >= $assetCapacity) {
    $PAGEDATA['NOASSETCAPACITY'] = [
        "CAPACITY" => $assetCapacity,
        "USED" => $assetUsed
    ];
}

echo $TWIG->render('newAsset.twig', $PAGEDATA);
?>
