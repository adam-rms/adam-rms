<?php
require_once 'head.php';
if (!isset($PAGEDATA['INSTANCE']['publicData']['enableAssets']) or !$PAGEDATA['INSTANCE']['publicData']['enableAssets']) die($TWIG->render('404Public.twig', $PAGEDATA));

$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("assetTypes.assetTypes_id", $_GET['id']);
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['asset'] = $DBLIB->getone('assetTypes', ["*", "assetTypes.instances_id as assetInstances_id"]); //have to double download it as otherwise manufacturer instance id is returned instead
if (!$PAGEDATA['asset']) die("Error 404");
$PAGEDATA['asset']['thumbnail'] = $bCMS->s3List(2, $PAGEDATA['asset']['assetTypes_id']);

$DBLIB->where("assets.instances_id", $PAGEDATA['INSTANCE']['instances_id']);
$DBLIB->where("assets.assetTypes_id", $PAGEDATA['asset']['assetTypes_id']);
if (isset($_GET['asset'])) {
    $PAGEDATA['asset']['oneasset'] = true;
    $DBLIB->where("assets.assets_id", $_GET['asset']);
} else $PAGEDATA['asset']['oneasset'] = false;
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
$DBLIB->orderBy("assets.asset_definableFields_1","ASC");$DBLIB->orderby("assets.asset_definableFields_2","ASC");$DBLIB->orderby("assets.asset_definableFields_3","ASC");$DBLIB->orderby("assets.asset_definableFields_4","ASC");$DBLIB->orderby("assets.asset_definableFields_5","ASC");$DBLIB->orderby("assets.asset_definableFields_6","ASC");$DBLIB->orderby("assets.asset_definableFields_7","ASC");$DBLIB->orderby("assets.asset_definableFields_8","ASC");$DBLIB->orderby("assets.asset_definableFields_9","ASC");$DBLIB->orderby("assets.asset_definableFields_10","ASC");
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->orderBy("assets.assets_tag","ASC");
$assets = $DBLIB->get("assets");
if (!$assets) die($TWIG->render('404Public.twig', $PAGEDATA));
$PAGEDATA['assets'] = [];
foreach ($assets as $asset) {
    $PAGEDATA['assets'][] = $asset;
}

 echo $TWIG->render('publicSites/embed/assetPublic.twig', $PAGEDATA);
?>
