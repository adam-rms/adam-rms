<?php
require_once __DIR__ . '/common/headSecure.php';

$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("assetTypes.assetTypes_id", $_GET['id']);
$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assetTypes.assetTypes_id=assets.assetTypes_id AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' AND assets_deleted = 0) > 0)");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$PAGEDATA['asset'] = $DBLIB->getone('assetTypes');
if (!$PAGEDATA['asset']) die("404 Asset Not Found");

$PAGEDATA['asset']['fields'] = explode(",", $PAGEDATA['asset']['assetTypes_definableFields']);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assetTypes_id", $PAGEDATA['asset']['assetTypes_id']);
if (isset($_GET['asset'])) {
    $PAGEDATA['asset']['oneasset'] = true;
    $DBLIB->where("assets.assets_id", $_GET['asset']);
}
$DBLIB->where("assets.assets_deleted", 0);
$assets = $DBLIB->get("assets", null);
if (!$assets) die("404 Assets Not Found");
$PAGEDATA['assets'] = [];
foreach ($assets as $asset) {

    $PAGEDATA['assets'][] = $asset;
}
$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['asset']['assetTypes_name'], "BREADCRUMB" => false];

echo $TWIG->render('asset.twig', $PAGEDATA);
?>
