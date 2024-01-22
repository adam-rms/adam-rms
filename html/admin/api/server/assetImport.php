<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:IMPORT:ASSETS") or !isset($_POST['instances_id'])) die("404");

//Expected list of headers for the CSV file
$CSVHEADERS = ["assetTypes_name","assetTypes_description","assetTypes_productLink","assetTypes_mass","assetTypes_dayRate","assetTypes_weekRate","assetTypes_value","assetCategories_id","manufacturers_name","assets_tag","assets_notes","assets_storageLocation","assets_dayRate","assets_WeekRate","assets_value","assets_mass","assetType_definableFieldsName_1","assetType_definableFieldsName_2","assetType_definableFieldsName_3","assetType_definableFieldsName_4","assetType_definableFieldsName_5","assetType_definableFieldsName_6","assetType_definableFieldsName_7","assetType_definableFieldsName_8","assetType_definableFieldsName_9","assetType_definableFieldsName_10","asset_definableFields_1","asset_definableFields_2","asset_definableFields_3","asset_definableFields_4","asset_definableFields_5","asset_definableFields_6","asset_definableFields_7","asset_definableFields_8","asset_definableFields_9","asset_definableFields_10"];

$createdAssetTypes = [];
$successfulAssets = [];
$failedAssets = [];

//Validate file is what we expect
// Undefined or Multiple Files
if (!isset($_FILES['csvFile']['error']) || is_array($_FILES['csvFile']['error'])) finish(false, "Invalid file parameters");

// Check upload error value value.
switch ($_FILES['csvFile']['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_NO_FILE:
        finish(false, "No file uploaded");
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        finish(false,'Exceeded filesize limit');
    default:
        finish(false,'Unknown errors');
}
//Check the file is a CSV or an excel file - excel doesn't save csvs correctly
if ($_FILES['csvFile']['type'] != "text/csv" && $_FILES['csvFile']['type'] != "application/vnd.ms-excel") finish(false, "File is not a CSV");
//Check the file is not empty
if ($_FILES['csvFile']['size'] == 0) finish(false, "File is empty");

//File is probably ok, lets try and read it
$csv = array_map('str_getcsv', file($_FILES['csvFile']['tmp_name']));
//Check the file has the correct headers
if ($csv[0] != $CSVHEADERS) finish(false, "File does not have the correct headers");

//File is ok, lets start importing
$DBLIB->where("instances.instances_id", $_POST['instances_id']);
$DBLIB->where("instances.instances_deleted", 0);
$instance = $DBLIB->getOne("instances", ["instances.instances_id", "instances.instances_name"]);
if (!$instance) finish(false, "Instance not found");

//Loop through the CSV file and import each row
//From this point, finish() is not used to return errors, instead the script will 
// continue and return a list of successfully added and failed assets
for ($i = 1; $i < count($csv); $i++) {
    $row = $csv[$i];

    //Validate row data
    array_walk($row, function(&$value, $key) {
        global $bCMS;
        $value = $bCMS->sanitizeStringMYSQL($value);
    });

    //Check if asset with given tag already exists
    if (isset($row[9]) and $row[9] != null){
        $DBLIB->where("assets_tag", $row[9]);
        $DBLIB->where("assets.instances_id", $_POST['instances_id']);
        $DBLIB->where("assets.assets_deleted", 0);
        $asset = $DBLIB->getOne("assets", ["assets.assets_id"]);
        if ($asset) {
            //Don't override existing information
            array_push($failedAssets, ["row" => $i, "tag" => $row[9], "reason" => "Asset with tag " . $row[9] . " already exists"]);
            continue; 
        }
    } else $row[9] = generateNewTag();
    
    //Asset Type
    if (!isset($row[0]) or $row[0] == null) {
        array_push($failedAssets, ["row" => $i, "tag" => $row[9], "reason" => "Asset Type not specified"]);
        continue;
    }
    $DBLIB->where("assetTypes_name", "%" . $row[0] . "%", "LIKE");
    $DBLIB->where("(assetTypes.instances_id = ? or assetTypes.instances_id IS NULL)", [$_POST['instances_id']]);
    $assetType = $DBLIB->getOne("assetTypes", ["assetTypes.assetTypes_id"]);

    if(!$assetType){
        //Create new asset type
        //Manufacturer
        if($row[8] == "") $row[8] = "Unknown/Generic"; //Use the generic manufacturer if none specified
        $DBLIB->where("manufacturers_name", "%" . $row[8] . "%", "LIKE");
        $DBLIB->where("(manufacturers.instances_id = ? or manufacturers.instances_id IS NULL)", [$_POST['instances_id']]);
        $manufacturer = $DBLIB->getOne("manufacturers", ["manufacturers.manufacturers_id"]);
        if (!$manufacturer) {
            $manufacturer = [
                "manufacturers_name" => $row[8],
                "instances_id" => $_POST['instances_id']
            ];
            $manufacturer['manufacturers_id'] = $DBLIB->insert("manufacturers", $manufacturer);
        }
        
        //Asset Category
        $DBLIB->where("assetCategories_id", $row[7],);
        $DBLIB->where("(assetCategories.instances_id = ? or assetCategories.instances_id IS NULL)", [$_POST['instances_id']]);
        $DBLIB->where("assetCategories.assetCategories_deleted", 0);
        $assetCategory = $DBLIB->getOne("assetCategories", ["assetCategories.assetCategories_id"]);
        if (!$assetCategory) {
            //Asset Category not found
            //This is the one thing we can't just create with data from the CSV
            array_push($failedAssets, ["row" => $i, "tag" => $row[9], "reason" => "Asset Category with id '". $row[7] . "' not found in this instance"]);
            continue;
        }

        //Map definable fields
        $definableFields = "";
        for ($j = 17; $j < 27; $j++) {
            $definableFields .= $row[$j] . ",";
        }
        $definableFields = rtrim($definableFields, ",");

         
        //Actually create new asset type
        $assetType = [
            "assetTypes_name" => $row[0],
            "assetCategories_id" => $row[7],
            "manufacturers_id" => $manufacturer['manufacturers_id'],
            "instances_id" => $_POST['instances_id'],
            "assetTypes_description" => $row[1],
            "assetTypes_productLink" => $row[2],
            "assetTypes_definableFields" => $definableFields,
            "assetTypes_mass" => $row[3] ?: 0,
            "assetTypes_inserted" => date('Y-m-d H:i:s'),
            "assetTypes_dayRate" => $row[4] ?: 0,
            "assetTypes_weekRate" => $row[5] ?: 0,
            "assetTypes_value" => $row[6] ?: 0,   
        ];
        $assetType['assetTypes_id'] = $DBLIB->insert("assetTypes", $assetType);
        if ($assetType['assetTypes_id']) array_push($createdAssetTypes, $assetType);
        else array_push($failedAssets, ["row" => $i, "tag" => $row[9], "reason" => "Error creating Asset Type"]);

    }

    //Asset
    $asset = [
        "assets_tag" => $row[9],
        "assetTypes_id" => $assetType['assetTypes_id'],
        "assets_notes" => $row[10],
        "instances_id" => $_POST['instances_id'],
        "asset_definableFields_1" => $row[26],
        "asset_definableFields_2" => $row[27],
        "asset_definableFields_3" => $row[28],
        "asset_definableFields_4" => $row[29],
        "asset_definableFields_5" => $row[30],
        "asset_definableFields_6" => $row[31],
        "asset_definableFields_7" => $row[32],
        "asset_definableFields_8" => $row[33],
        "asset_definableFields_9" => $row[34],
        "asset_definableFields_10" => $row[35],
        "assets_dayRate" => $row[12] ?: 0,
        "assets_weekRate" => $row[13] ?: 0,
        "assets_value" => $row[14] ?: 0,
        "assets_mass" => $row[15] ?: 0,
    ];
    $asset['assets_id'] = $DBLIB->insert("assets", $asset);

    //Add Row ID to asset array for logging output
    $asset['row'] = $i;

    if ($asset['assets_id']) array_push($successfulAssets, $asset);
    else array_push($failedAssets, ["row" => $i, "tag" => $row[9], "reason" => "Unknown error"]);
}

return finish(true, null, ["createdTypes" => $createdAssetTypes,"successfulAssets" => $successfulAssets, "failedAssets" => $failedAssets]);

/**
 *  @OA\Post(
 *      path="/server/assetImport.php",
 *      summary="Bulk Asset Import",
 *      description="Bulk import assets, using templated csv",
 *      operationId="assetImport",
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="csvFile",
 *          in="files",
 *          description="CSV File with assets to import",
 *          required="true",
 *      ),
 *      @OA\Parameter(
 *          name="instances_id",
 *          in="query",
 *          description="Instance Id to import assets to",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */