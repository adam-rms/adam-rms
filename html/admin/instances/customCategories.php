<?php
require_once __DIR__ . '/../common/headSecure.php';
use Money\Currency;
use Money\Money;

$PAGEDATA['pageConfig'] = ["TITLE" => "Custom Categories", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_CATEGORIES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->orderBy("assetCategoriesGroups.assetCategoriesGroups_order", "ASC");
$DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		assetCategories.assetCategories_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' 
    )");
}
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->where("assetCategories_deleted",0);
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['categories'] = $DBLIB->get('assetCategories');

echo $TWIG->render('instances/customCategories.twig', $PAGEDATA);
?>
