<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Manufacturers", "BREADCRUMB" => false];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 20; //Users per page
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		manufacturers.manufacturers_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		manufacturers.manufacturers_website LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' 
    )");
} else $DBLIB->where("(assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' AND assets_deleted = 0)"); //Limit it to where there are assets in the project
$PAGEDATA['manufacturers'] = $DBLIB->arraybuilder()->paginate('assets', $page, ["DISTINCT manufacturers.*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

echo $TWIG->render('manufacturers.twig', $PAGEDATA);
?>
