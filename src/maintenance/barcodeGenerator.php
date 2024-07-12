<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_BARCODES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Generate Asset Barcodes", "BREADCRUMB" => true];

$PAGEDATA['preFetch'] = [];
if (isset($_GET['all'])) {
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->where("assets.assets_deleted", 0);
    $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $assets = $DBLIB->get("assets", null, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
    foreach ($assets as $asset) {
        $PAGEDATA['preFetch'][$asset['assets_id']] = $asset;
    }
} else {
    if (isset($_GET['category'])) {
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
        $DBLIB->where("assets.assets_deleted", 0);
        $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assetTypes.assetCategories_id", $_GET['category']);
        $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
        $assets = $DBLIB->get("assets", null, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
        foreach ($assets as $asset) {
            $PAGEDATA['preFetch'][$asset['assets_id']] = $asset;
        }
    }
    if (isset($_GET['manufacturer'])) {
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
        $DBLIB->where("assets.assets_deleted", 0);
        $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assetTypes.manufacturers_id", $_GET['manufacturer']);
        $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
        $assets = $DBLIB->get("assets", null, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
        foreach ($assets as $asset) {
            $PAGEDATA['preFetch'][$asset['assets_id']] = $asset;
        }
    }
    if (isset($_GET['type'])) {
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
        $DBLIB->where("assets.assets_deleted", 0);
        $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assetTypes.assetTypes_id", $_GET['type']);
        $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
        $assets = $DBLIB->get("assets", null, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
        foreach ($assets as $asset) {
            $PAGEDATA['preFetch'][$asset['assets_id']] = $asset;
        }
    }
    if (isset($_GET['id'])) {
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
        $DBLIB->where("assets.assets_deleted", 0);
        $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assets.assets_id", $_GET['id']);
        $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
        $assets = $DBLIB->get("assets", null, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
        foreach ($assets as $asset) {
            $PAGEDATA['preFetch'][$asset['assets_id']] = $asset;
        }
    }
}

echo $TWIG->render('maintenance/barcodeGenerator.twig', $PAGEDATA);
