<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Assets", "BREADCRUMB" => false];

if (isset($_GET['p'])) {
	//Duplicated in deletion of project page
	$DBLIB->where("users_userid", $AUTH->data['users_userid']);
	$DBLIB->update("users", ["users_selectedProjectID" => $_GET['p']]);
	header("Location: " . $CONFIG['ROOTURL'] . "/assets.php");
}


if (isset($_GET['showtags'])) $PAGEDATA['showTags'] = true;
else $PAGEDATA['showTags'] = false;

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 20); //Users per page
if (isset($_GET['category'])) $DBLIB->where("assetTypes.assetCategories_id", $_GET['category']);
if (isset($_GET['manufacturer'])) $DBLIB->where("manufacturers.manufacturers_id", $_GET['manufacturer']);
$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assetTypes.assetTypes_id=assets.assetTypes_id AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' AND assets_deleted = 0) > 0)");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		manufacturers_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		assetTypes_description LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		assetTypes_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' 
    )");
}
$assets = $DBLIB->arraybuilder()->paginate('assetTypes', $page, ["assetTypes.*", "manufacturers.*", "assetCategories.*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

$PAGEDATA['assets'] = [];
foreach ($assets as $asset) {
	$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
	$DBLIB->where("assets.assetTypes_id", $asset['assetTypes_id']);
	$DBLIB->where("assets_deleted", 0);
	$DBLIB->orderBy("assets.assets_tag", "ASC");
	$asset['tags'] = $DBLIB->get("assets", null, ["assets_id", "assets_tag"]);
	$asset['count'] = count($asset['tags']);
	$asset['thumbnail'] = $bCMS->s3List(2, $asset['assetTypes_id']);
	$PAGEDATA['assets'][] = $asset;
}

if (isset($_GET['category'])) {
	$DBLIB->where("assetCategories_id", $_GET['category']);
	$PAGEDATA['thisCategory'] = $DBLIB->getone("assetCategories");
	$PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['thisCategory']['assetCategories_name'] . " Assets";
} else $PAGEDATA['thisCategory'] = false;

if (isset($_GET['manufacturer'])) {
	$DBLIB->where("manufacturers_id", $_GET['manufacturer']);
	$PAGEDATA['thisManufacturer'] = $DBLIB->getone("manufacturers");
	$PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['thisManufacturer']['manufacturers_name'] . " Assets";
} else $PAGEDATA['thisManufacturer'] = false;

if (isset($_GET['listView'])) echo $TWIG->render('assetsListView.twig', $PAGEDATA);
else echo $TWIG->render('assetsShopView.twig', $PAGEDATA);
?>
