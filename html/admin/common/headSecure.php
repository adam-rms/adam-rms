<?php
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/../../common/libs/Auth/main.php';

if (!$GLOBALS['AUTH']->login) {
    $_SESSION['return'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: " . $CONFIG['ROOTURL'] . "/login/");
    die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/login/" . '" />');
}

$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assetTypes_id=assetTypes_id AND assets_deleted = '0' AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "') > 0)");
$assetCategories = $DBLIB->getvalue("assetTypes", "DISTINCT assetCategories_id", null);

if ($assetCategories) {
    $DBLIB->orderBy("assetCategories_rank", "ASC");
    $DBLIB->where("(assetCategories_id IN (" . implode(",", $assetCategories) . "))");
    $PAGEDATA['assetCategories'] = $DBLIB->get("assetCategories", null, ["assetCategories.assetCategories_name", "assetCategories.assetCategories_id", "assetCategories.assetCategories_fontAwesome"]);
} else $PAGEDATA['assetCategories'] = [];


$PAGEDATA['USERDATA'] = $GLOBALS['AUTH']->data;

$USERDATA = $PAGEDATA['USERDATA'];
?>
