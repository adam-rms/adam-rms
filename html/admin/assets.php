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

if (isset($_GET['q'])) {
	$PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
	if (is_numeric($bCMS->reverseATag($PAGEDATA['search']))) {
		$DBLIB->where("assets_tag", $bCMS->reverseATag($PAGEDATA['search']));
		$DBLIB->where("assets_deleted", 0);
		$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
		$assetTagSearch = $DBLIB->getOne("assets",['assets_id',"assetTypes_id"]);
		if ($assetTagSearch) {
			header("Location: " . $CONFIG['ROOTURL'] . '/asset.php?id=' . $assetTagSearch['assetTypes_id'] . '&asset=' . $assetTagSearch['assets_id']);
			die('<a href="' . $CONFIG['ROOTURL'] . '/asset.php?id=' . $assetTagSearch['assetTypes_id'] . '&asset=' . $assetTagSearch['assets_id'] . '">Continue</a>');
		}
	}
}
else $PAGEDATA['search'] = null;


if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 20); //Users per page
if (isset($_GET['category'])) $DBLIB->where("assetTypes.assetCategories_id", $_GET['category']);
if (isset($_GET['manufacturer'])) $DBLIB->where("manufacturers.manufacturers_id", $_GET['manufacturer']);
$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assetTypes.assetTypes_id=assets.assetTypes_id AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' " . (!isset($_GET['archive']) ? "AND (assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP()) " : "") . "AND assets_deleted = 0" . (!isset($_GET['all']) ? ' AND assets.assets_linkedTo IS NULL' : '') . (isset($_GET['group']) ? " AND FIND_IN_SET(" . $bCMS->sanitizeString($_GET['group']) . ", assets.assets_assetGroups)" : "") .") > 0)");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		manufacturers_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		assetTypes_description LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		assetTypes_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' 
    )");
}
$assets = $DBLIB->arraybuilder()->paginate('assetTypes', $page, ["assetTypes.*", "manufacturers.*", "assetCategories.*", "assetCategoriesGroups_name"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

$PAGEDATA['assets'] = [];
foreach ($assets as $asset) {
	$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
	$DBLIB->where("assets.assetTypes_id", $asset['assetTypes_id']);
	$DBLIB->where("assets_deleted", 0);
	if (!isset($_GET['archive'])) $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
	if (!isset($_GET['all'])) $DBLIB->where("(assets.assets_linkedTo IS NULL)");
	if (isset($_GET['group'])) $DBLIB->where("FIND_IN_SET(" . $bCMS->sanitizeStringMYSQL($_GET['group']) . ", assets.assets_assetGroups)");
	if (isset($_GET['barcodes'])){
		if ($_GET['barcodes'] == 1) {
			$DBLIB->where("((SELECT COUNT(assetsBarcodes_id) FROM assetsBarcodes WHERE assetsBarcodes.assets_id = assets.assets_id AND assetsBarcodes_deleted = 0) >0)");
		} else {
			$DBLIB->where("((SELECT COUNT(assetsBarcodes_id) FROM assetsBarcodes WHERE assetsBarcodes.assets_id = assets.assets_id AND assetsBarcodes_deleted = 0) <1)");
		}
	}
	$DBLIB->orderBy("assets.assets_tag", "ASC");
	$assetTags = $DBLIB->get("assets", null, ["assets_id", "assets_notes","assets_tag","asset_definableFields_1","asset_definableFields_2","asset_definableFields_3","asset_definableFields_4","asset_definableFields_5","asset_definableFields_6","asset_definableFields_7","asset_definableFields_8","asset_definableFields_9","asset_definableFields_10","assets_dayRate","assets_weekRate","assets_value","assets_mass","assets_endDate"]);
	$asset['count'] = count($assetTags);
	if ($asset['count'] < 1) continue;
	$asset['fields'] = explode(",", $asset['assetTypes_definableFields']);
	$asset['thumbnail'] = $bCMS->s3List(2, $asset['assetTypes_id'],'s3files_meta_uploaded','ASC',1);
	$asset['tags'] = [];
	foreach ($assetTags as $tag) {
		if ($AUTH->data['users_selectedProjectID'] != null and $AUTH->instancePermissionCheck(31)) {
			//Check availability
			$DBLIB->where("assets_id", $tag['assets_id']);
			$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
			$DBLIB->where("(projects.projects_id = '" . $PAGEDATA['thisProject']['projects_id'] . "' OR projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
			$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
			$DBLIB->where("projects.projects_deleted", 0);
			$DBLIB->where("((projects_dates_deliver_start >= '" . $PAGEDATA['thisProject']["projects_dates_deliver_start"]  . "' AND projects_dates_deliver_start <= '" . $PAGEDATA['thisProject']["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $PAGEDATA['thisProject']["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $PAGEDATA['thisProject']["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $PAGEDATA['thisProject']["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $PAGEDATA['thisProject']["projects_dates_deliver_start"] . "'))");
			$tag['assignment'] = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id", "projects.projects_name"]);
		}
		$tag['flagsblocks'] = assetFlagsAndBlocks($tag['assets_id']);
		$asset['tags'][] = $tag;
	}

	$PAGEDATA['assets'][] = $asset;
}

if (isset($_GET['category'])) {
	$DBLIB->where("assetCategories_id", $_GET['category']);
	$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
	$PAGEDATA['thisCategory'] = $DBLIB->getone("assetCategories");
	$PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['thisCategory']['assetCategories_name'] . " Assets";
} else $PAGEDATA['thisCategory'] = false;

if (isset($_GET['manufacturer'])) {
	$DBLIB->where("manufacturers_id", $_GET['manufacturer']);
	$PAGEDATA['thisManufacturer'] = $DBLIB->getone("manufacturers");
	$PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['thisManufacturer']['manufacturers_name'] . " Assets";
} else $PAGEDATA['thisManufacturer'] = false;

if (isset($_GET['group'])) {
	$DBLIB->where("assetGroups_id", $_GET['group']);
	$DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
	$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
	$DBLIB->where("assetGroups_deleted",0);
	$PAGEDATA['thisGroup'] = $DBLIB->getone("assetGroups");
	$PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['thisGroup']['assetGroups_name'] . " Assets";
} else $PAGEDATA['thisGroup'] = false;

if (isset($_GET['listView'])) echo $TWIG->render('assetsListView.twig', $PAGEDATA);
else echo $TWIG->render('assetsShopView.twig', $PAGEDATA);
?>
