<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['other_instances_id'])) {
    //For Asset Migration, we want the manufacturers that are in the instance we are migrating to
    //Check user has permission to transfer assets in this instance 
    if (!$AUTH->instancePermissionCheck("ASSETS:TRANSFER")) die("403");
    //Check other instance exists for this user
    if (array_search($_POST['other_instances_id'], array_column($AUTH->data['instances'], 'instances_id')) === false) die("404");
    //check user has permission in other instance 
    if (!in_array("ASSETS:TRANSFER", $AUTH->data['instances'][array_search($_POST['other_instances_id'], array_column($AUTH->data['instances'], 'instances_id'))]['permissions'])) die("403");

    $instanceID = $_POST['other_instances_id'];
} else {
    //We want the manufacturers that are in the current instance
    $instanceID = $AUTH->data['instance']["instances_id"];
}

if(!isset($_POST['other_instances_id'])) { //We want all manufacturers if we are migrating
    $assetManufacturers= $DBLIB->subQuery();
    $assetManufacturers->where("assets.instances_id",$instanceID);
    $assetManufacturers->where("assets_deleted",0);
    $assetManufacturers->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
    $assetManufacturers->groupBy ("assetTypes.manufacturers_id");
    $assetManufacturers->get ("assets", null, "manufacturers_id");
    $DBLIB->where("manufacturers_id", $assetManufacturers, "IN");
}
$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $instanceID . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
if (isset($_POST['term']) and $_POST['term']) $DBLIB->where("manufacturers_name","%" . $_POST['term'] . "%","LIKE");
$manufacturers = $DBLIB->get('manufacturers', 15, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);
finish(true, null, $manufacturers);

/** @OA\Post(
 *     path="/manufacturer/search.php", 
 *     summary="Search Manufacturers", 
 *     description="Search for a manufacturer", 
 *     operationId="searchManufacturers", 
 *     tags={"manufacturers"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="Array of Manufacturers",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="term",
 *         in="query",
 *         description="Search Term",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="instance_id",
 *         in="query",
 *         description="Instance ID",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */