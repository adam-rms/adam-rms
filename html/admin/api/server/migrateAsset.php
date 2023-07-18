<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:FULL_PERMISSIONS_IN_INSTANCE") or !isset($_POST['assets_id']) or !isset($_POST['instances_id'])) die("404");

//Get asset
$DBLIB->where("assets_id", $_POST['assets_id']);
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("assets_archived IS NULL");
$asset = $DBLIB->getOne("assets");
if (!$asset) finish(false, ["code"=>"ASSET_NOT_FOUND", "message"=>"Asset not found"]);

//Get assetType
$DBLIB->where("assetTypes_id", $asset['assetTypes_id']);
$assetType = $DBLIB->getOne("assetTypes", ["assetTypes.*"]);
if (!$assetType) finish(false, ["code"=>"ASSET_TYPE_NOT_FOUND", "message"=>"Asset type not found"]);

//Check if asset is already in instance
if ($assetType['instances_id'] == $_POST['instances_id']) finish(false, ["code"=>"ASSET_ALREADY_IN_INSTANCE", "message"=>"Asset already in instance"]);

if ($assetType['instances_id'] !== NULL) {
    //See if this instance has an asset with a similar name
    $DBLIB->where("assetTypes_name", "%" . $assetType['assetTypes_name'] . "%", 'LIKE');
    $DBLIB->where("instances_id", $_POST['instances_id']);
    $possibleAssetType = $DBLIB->getOne("assetTypes", ["assetTypes.*"]);
    if ($possibleAssetType) {
        $assetType = $possibleAssetType;
        //Replace assetType with one from this instance
    } else {

        //Get Manufacturer
        $DBLIB->where("manufacturers_id", $assetType['manufacturers_id']);
        $manufacturer = $DBLIB->getOne("manufacturers", ["manufacturers.*"]);
        if (!$manufacturer) finish(false, ["code"=>"MANUFACTURER_NOT_FOUND", "message"=>"Manufacturer not found"]);//Shouldn't be possible to get here!
        //Create new manufacturer if needed
        if ($manufacturer['instances_id'] != $_POST['instances_id'] && $manufacturer['instances_id'] != NULL) {
            //See if they have a similarly named manufacturer
            $DBLIB->where("manufacturers_name", "%" . $manufacturer['manufacturers_name'] . "%", 'LIKE');
            $DBLIB->where("instances_id", $_POST['instances_id']);
            $possibleManufacturer = $DBLIB->getOne("manufacturers", ["manufacturers.*"]);
            if ($possibleManufacturer) {
                $manufacturer = $possibleManufacturer;
            } else {
                $manufacturer['instances_id'] = $_POST['instances_id'];
                $manufacturer['manufacturers_internalAdamRMSNote'] = "Created during asset migration";
                unset($manufacturer['manufacturers_id']);
                $manufacturer['manufacturers_id'] = $DBLIB->insert("manufacturers", $manufacturer);
            }
        } 

        //Get Category
        $DBLIB->where("assetCategories_id", $assetType['assetCategories_id']);
        $DBLIB->where("assetCategories_deleted", 0);
        $category = $DBLIB->getOne("assetCategories", ["assetCategories.*"]);
        if (!$category) finish(false, ["code"=>"CATEGORY_NOT_FOUND", "message"=>"Category not found"]);
        //Create new category if needed
        if ($category['instances_id'] != $_POST['instances_id'] && $category['instances_id'] != NULL) {
            //See if they have a similarly named category
            $DBLIB->where("assetCategories_name", "%" . $category['assetCategories_name'] . "%", 'LIKE');
            $DBLIB->where("instances_id", $_POST['instances_id']);
            $possibleCategory = $DBLIB->getOne("assetCategories", ["assetCategories.*"]);
            if ($possibleCategory) {
                $category = $possibleCategory;
            } else {
                $category['instances_id'] = $_POST['instances_id'];
                unset($category['assetCategories_id']);
                $category['assetCategories_id'] = $DBLIB->insert("assetCategories", $category);
            }
        }

        //Create AssetType
        $assetType['instances_id'] = $_POST['instances_id'];
        $assetType['manufacturers_id'] = $manufacturer['manufacturers_id'];
        $assetType['assetCategories_id'] = $category['assetCategories_id'];
        unset($assetType['assetTypes_id']);
        $assetType['assetTypes_id'] = $DBLIB->insert("assetTypes", $assetType);
    }
}

//Create Asset
$asset['instances_id'] = $_POST['instances_id'];
$asset['assetTypes_id'] = $assetType['assetTypes_id'];
$asset['assets_storageLocation'] = NULL;
$asset['assets_linkedTo'] = NULL; //We don't migrate linked assets
$asset['assets_tag'] = generateNewTag(); //This is now a new asset so needs a unique tag
unset($asset['assets_id']);
$asset['assets_id'] = $DBLIB->insert("assets", $asset);
if (!$asset['assets_id']) finish(false, ["code"=>"ASSET_NOT_CREATED", "message"=>"Asset not created"]);
$bCMS->auditLog("MIGRATE-ASSET", "assets", $asset['assets_id'], $AUTH->data['users_userid']);

//Archive asset in old instance
$archiveAssetData = [
    "assets_archived" => "Asset moved to another Business",
    "assets_endDate" => date("Y-m-d H:i:s", time())
];
$DBLIB->where("assets_id", $_POST['assets_id']);
$result = $DBLIB->update("assets", $archiveAssetData);
if (!$result) finish(false, ["code" => "ARCHIVE-FAIL", "message"=> "Could not archive asset"]);
else $bCMS->auditLog("ARCHIVE", "assets", $asset['assets_id'], $AUTH->data['users_userid']);

finish(true);
