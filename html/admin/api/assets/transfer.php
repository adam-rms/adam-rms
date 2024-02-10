<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['assets_id']) or !isset($_POST['new_instances_id'])) die("404");

//Check user has permission to transfer assets in this instance 
if (!$AUTH->instancePermissionCheck("ASSETS:TRANSFER")) die("404");
//Check other instance exists for this user
if (array_search($_POST['new_instances_id'], array_column($AUTH->data['instances'], 'instances_id')) === false) die("404");
//check user has permission in other instance 
if (!in_array("ASSETS:TRANSFER", $AUTH->data['instances'][array_search($_POST['new_instances_id'], array_column($AUTH->data['instances'], 'instances_id'))]['permissions'])) die("403");

//Get asset from this instance
$DBLIB->where("assets_id", $_POST['assets_id']);
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("assets_archived IS NULL");
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$asset = $DBLIB->getOne("assets");
if (!$asset) finish(false, ["code"=>"ASSET_NOT_FOUND", "message"=>"Asset not found"]);

//Check if asset is already in instance
if ($asset['instances_id'] == $_POST['new_instances_id']) finish(false, ["code"=>"ASSET_ALREADY_IN_INSTANCE", "message"=>"Asset already in new instance"]);

//Get assetType
// $_POST['assetTypes_id'] is either an id of a type in the new instance or ``false`` if we need to create the asset type in the new instance
if (isset($_POST['assetTypes_id'])) {
    //We have been given an assetTypes_id from the new instance
    $DBLIB->where("assetTypes_id", $_POST['assetTypes_id']);
    $DBLIB->where("(instances_id IS NULL or instances_id = " .  $_POST['new_instances_id']. ")");
    $assetType = $DBLIB->getOne("assetTypes", ["assetTypes.*"]);
    if (!$assetType) finish(false, ["code"=>"ASSET_TYPE_NOT_FOUND", "message"=>"Asset type not found"]);

} else {
    //assetTypes_id is false so need to get assetType from the old instance, that will need to be created in the new instance

    //check additional params exist
    if(!isset($_POST['manufacturers_id']) or !isset($_POST['assetCategories_id'])) finish(false, ["code"=>"MISSING_PARAMS", "message"=>"Missing parameters"]);

    //Get assetType from old instance
    $DBLIB->where("assetTypes_id", $asset['assetTypes_id']);
    $DBLIB->where("(instances_id IS NULL or instances_id = " .  $AUTH->data['instance']["instances_id"] . ")");
    $assetType = $DBLIB->getOne("assetTypes", ["assetTypes.*"]);
    if (!$assetType) finish(false, ["code"=>"ASSET_TYPE_NOT_FOUND", "message"=>"Asset type not found"]);

    //Get Manufacturer
    if ($_POST['manufacturers_id']) {
        //We have been given the new manufacturer id to use, double check it exists
        $DBLIB->where("manufacturers_id", $_POST['manufacturers_id']);
        $DBLIB->where("(instances_id IS NULL or instances_id = " .  $_POST['new_instances_id'] . ")");
        $manufacturer = $DBLIB->getOne("manufacturers", ["manufacturers.*"]);
        if (!$manufacturer) finish(false, ["code"=>"MANUFACTURER_NOT_FOUND", "message"=>"Manufacturer from given id not found"]);

    } else {
        //We need to create a manufacturer in the new instance
        $DBLIB->where("manufacturers_id", $assetType['manufacturers_id']);
        $DBLIB->where("(instances_id IS NULL or instances_id = " .  $_POST['new_instances_id'] . ")");
        $oldInstanceManufacturer = $DBLIB->getOne("manufacturers", ["manufacturers.*"]);
        if (!$oldInstanceManufacturer) finish(false, ["code"=>"MANUFACTURER_NOT_FOUND", "message"=>"Manufacturer from asset type not found"]);//Shouldn't be possible to get here!
        //Create new manufacturer if needed
        if ($oldInstanceManufacturer['instances_id'] != NULL) {
            $oldInstanceManufacturer['instances_id'] = $_POST['new_instances_id'];
            $oldInstanceManufacturer['manufacturers_internalAdamRMSNote'] = "Created during asset migration";
            unset($oldInstanceManufacturer['manufacturers_id']);
            $manufacturer['manufacturers_id'] = $DBLIB->insert("manufacturers", $oldInstanceManufacturer);
        }
    }

    //Get Category
    $DBLIB->where("assetCategories_id", $_POST['assetCategories_id']);
    $DBLIB->where("assetCategories_deleted", 0);
    $DBLIB->where("(instances_id IS NULL OR instances_id = " . $_POST['new_instances_id'] . ")");
    $category = $DBLIB->getOne("assetCategories", ["assetCategories.*"]);
    if (!$category) finish(false, ["code"=>"CATEGORY_NOT_FOUND", "message"=>"Category not found"]);

    //Create AssetType
    $assetType['instances_id'] = $_POST['new_instances_id'];
    $assetType['manufacturers_id'] = $manufacturer['manufacturers_id'];
    $assetType['assetCategories_id'] = $category['assetCategories_id'];
    unset($assetType['assetTypes_id']);
    $assetType['assetTypes_id'] = $DBLIB->insert("assetTypes", $assetType);
    if (!$assetType['assetTypes_id']) finish(false, ["code"=>"ASSET_TYPE_NOT_CREATED", "message"=>"Asset type not created"]);

}

//Create Asset
$asset['instances_id'] = $_POST['new_instances_id'];
$asset['assetTypes_id'] = $assetType['assetTypes_id'];
$asset['assets_storageLocation'] = NULL;
$asset['assets_linkedTo'] = NULL; //We don't migrate linked assets
unset($asset['assets_id']);
$asset['assets_id'] = $DBLIB->insert("assets", $asset);
if (!$asset['assets_id']) finish(false, ["code"=>"ASSET_NOT_CREATED", "message"=>"Asset not created"]);
$bCMS->auditLog("TRANSFER-ASSET", "assets", "Asset moved from instance " . $AUTH->data['instance']["instances_id"] . " to " . $_POST['new_instances_id'] . " instance - new id " . $asset['assets_id'], $AUTH->data['users_userid']);

//Archive asset in old instance
$archiveAssetData = [
    "assets_archived" => "Asset moved to another business",
    "assets_endDate" => date("Y-m-d H:i:s", time())
];
$DBLIB->where("assets_id", $_POST['assets_id']);
$result = $DBLIB->update("assets", $archiveAssetData);
if (!$result) finish(false, ["code" => "ARCHIVE-FAIL", "message"=> "Could not archive asset"]);
else $bCMS->auditLog("ARCHIVE", "assets", $_POST['assets_id'], $AUTH->data['users_userid']);

finish(true);

/**
 *  @OA\Post(
 *      path="/assets/transfer.php",
 *      summary="Transfer Asset to Instance",
 *      description="Transfer an Asset to another Instance
 Requires Instance permission ASSETS:TRANSFER",
 *      operationId="transferAsset",
 *      tags={"assets"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="new_instances_id",
 *          in="query",
 *          description="id of the instance to transfer the asset to"
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="assets_id",
 *          in="query",
 *          description="id of the asset to transfer"
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="assetTypes_id",
 *          in="query",
 *          description="assetType Id in the new instance to map the asset to"
 *          required="false",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="manufacturers_id",
 *          in="query",
 *          description="manufacturer Id in the new instance to map the asset to"
 *          required="false",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="assetCategories_id",
 *          in="query",
 *          description="asset category Id in the new instance to map the asset to"
 *          required="false",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 * 
 *  )
 */