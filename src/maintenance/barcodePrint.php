<?php
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_BARCODES:VIEW") or !isset($_GET['ids'])) die($TWIG->render('404.twig', $PAGEDATA));

$ids = explode(",", $_GET['ids']);
$groups = explode(",", $_GET['groups']);
$PAGEDATA['assets'] = [];
function checkDuplicate($value, $type)
{
    global $DBLIB;
    $DBLIB->where("assetsBarcodes_value", $value);
    $DBLIB->where("assetsBarcodes_type", $type);
    $result = $DBLIB->getone("assetsBarcodes", ["assetsBarcodes_id"]);
    if ($result) return true;
    else return false;
}
foreach ($groups as $group) {
    if ($group == null) continue;
    $DBLIB->where("FIND_IN_SET(" . $bCMS->sanitizestring($group) . ", assets.assets_assetGroups)");
    $DBLIB->where("assets_deleted", 0);
    $groupAssets = $DBLIB->get("assets", null, ["assets_id"]);
    foreach ($groupAssets as $asset) {
        if ($asset) $ids[] = $asset['assets_id'];
    }
}
foreach ($ids as $id) {
    if ($id == null) continue;
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->where("assets.assets_deleted", 0);
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assets_id", $id);
    $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
    $asset = $DBLIB->getOne("assets", null, ["assets.assets_id", "assetTypes.assetTypes_definableFields", "assets.asset_definableFields_1", "assetTypes.assetTypes_name", "asset.assets_mass", "assetTypes.assetTypes_mass", "assets.assets_tag", "manufacturers.manufacturers_name"]);
    if ($asset) {
        $DBLIB->orderBy("assetsBarcodes.assetsBarcodes_added", "DESC");
        $DBLIB->where("assetsBarcodes.assetsBarcodes_type", isset($_GET['barcodeType']) ? $_GET['barcodeType'] : "CODE_128");
        $DBLIB->where("assetsBarcodes.assetsBarcodes_deleted", 0);
        $DBLIB->where("assetsBarcodes.assets_id", $asset['assets_id']);
        $asset['barcode'] = $DBLIB->getone("assetsBarcodes");
        if (!$asset['barcode']) {
            $assetBarcodeData = [
                "assetsBarcodes_value" => $asset['assets_tag'],
                "assetsBarcodes_type" => isset($_GET['barcodeType']) ? $_GET['barcodeType'] : "CODE_128",
                "assets_id" => $asset['assets_id'],
                "users_userid" => $AUTH->data['users_userid'],
                "assetsBarcodes_added" => date("Y-m-d H:i:s")
            ];
            while (checkDuplicate($assetBarcodeData["assetsBarcodes_value"], $assetBarcodeData["assetsBarcodes_type"])) {
                $assetBarcodeData["assetsBarcodes_value"] = mt_rand(1000, 999999);
            }
            $insert = $DBLIB->insert("assetsBarcodes", $assetBarcodeData);
            if ($insert) {
                $assetBarcodeData['assetsBarcodes_id'] = $insert;
                $asset['barcode'] = $assetBarcodeData;
            } else continue; //Don't handle this asset
        }

        $asset['assetTypes_definableFields'] = explode(",", $asset['assetTypes_definableFields']);
        $asset['isAsset'] = true;
        $PAGEDATA['assets'][] = $asset;
    }
}
if ($_GET['blanks'] > 0) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assetsBarcodes_deleted", 0);
    $DBLIB->join("assets", "assets.assets_id=assetsBarcodes.assets_id", "LEFT");
    $count = $DBLIB->getValue("assetsBarcodes", "COUNT(*)");
    $count += 1; //Add one to it for the new set
    foreach (range(1, $_GET['blanks'], 1) as $asset) {
        $assetBarcodeData = [
            "assetsBarcodes_value" => $count + $asset,
            "assetsBarcodes_type" => isset($_GET['barcodeType']) ? $_GET['barcodeType'] : "CODE_128"
        ];
        while (checkDuplicate($assetBarcodeData["assetsBarcodes_value"], $assetBarcodeData["assetsBarcodes_type"])) {
            $assetBarcodeData["assetsBarcodes_value"] = mt_rand(1000, 99999);
        }
        $asset = [];
        $assetBarcodeData['assetsBarcodes_id'] = null;
        $asset['barcode'] = $assetBarcodeData;
        $asset['isAsset'] = false;
        $PAGEDATA['assets'][] = $asset;
    }
}
$PAGEDATA['GET'] = $_GET;

if (!isset($_GET['instanceName'])) $PAGEDATA['GET']['instanceName'] = false;
elseif ($_GET['instanceName'] == "Hide" or $_GET['instanceName'] == null) $PAGEDATA['GET']['instanceName'] = false;

if (isset($_GET['type']) and $_GET['type'] == "printout") {
    die($TWIG->render('maintenance/barcodePrint.twig', $PAGEDATA));
} else {
    die($TWIG->render('maintenance/barcodePrint-table.twig', $PAGEDATA));
}
