<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Manufacturers", "BREADCRUMB" => false];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		manufacturers.manufacturers_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		manufacturers.manufacturers_website LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' 
    )");
	$PAGEDATA['manufacturers'] = $DBLIB->get('manufacturers', null, ["manufacturers.*"]);
} else {
	//Limit it to where there are assets in the project
	$DBLIB->orderBy("manufacturers_name", "ASC");
	$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
	$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
	$DBLIB->where("(assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' AND assets_deleted = 0)");
	$manufacturers = $DBLIB->get('assets', null,["DISTINCT manufacturers.*"]);
	$PAGEDATA['manufacturers'] = [];
	foreach ($manufacturers as $manufacturer) {
		$PAGEDATA['manufacturers'][$manufacturer['manufacturers_id']] = $manufacturer;
	}
	$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
	$manufacturersInstance = $DBLIB->get("manufacturers");
	foreach ($manufacturersInstance as $manufacturer) {
		$PAGEDATA['manufacturers'][$manufacturer['manufacturers_id']] = $manufacturer;
	}

	usort($PAGEDATA['manufacturers'], function($a, $b) {
		return $a['manufacturers_name'] <=> $b['manufacturers_name'];
	});
}

echo $TWIG->render('manufacturers.twig', $PAGEDATA);
?>
