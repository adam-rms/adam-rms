<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
if (isset($_POST['term'])) {
    $DBLIB->where("(
        manufacturers_name LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%' OR
		assetTypes_description LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%' OR
		assets_notes LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%' OR
		assets_tag = '" . $bCMS->reverseATag($bCMS->sanitizeString($_POST['term'])). "' OR
        assetTypes_name LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
    )");
} else $DBLIB->orderBy("assetTypes_name", "ASC");
$assets = $DBLIB->get("assets", 15, ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
if (!$assets) finish(false, ["code" => "LIST-ASSETS-FAIL", "message"=> "Could not search"]);
$assetsReturn = [];
foreach ($assets as $asset) {
    if ($asset['assets_tag'] == null) $asset['tag']= '';
    if ($asset['assets_tag'] <= 9999) $asset['tag'] = "A-" . sprintf('%04d', $asset['assets_tag']);
    else $asset['tag'] = "A-" . $asset['assets_tag'];
    $assetsReturn[] = $asset;
}
finish(true, null, $assetsReturn);
