<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['other_instances_id'])) {
    //For Asset Migration, we want the categories that are in the instance we are migrating to
    //Check user has permission to transfer assets in this instance 
    if (!$AUTH->instancePermissionCheck("ASSETS:TRANSFER")) die("403");
    //Check other instance exists for this user
    if (array_search($_POST['other_instances_id'], array_column($AUTH->data['instances'], 'instances_id')) === false) die("404");
    //check user has permission in other instance 
    if (!in_array("ASSETS:TRANSFER", $AUTH->data['instances'][array_search($_POST['other_instances_id'], array_column($AUTH->data['instances'], 'instances_id'))]['permissions'])) die("403");

    $instanceID = $_POST['other_instances_id'];
} else {
    //We want the categories that are in the current instance
    $instanceID = $AUTH->data['instance']["instances_id"];
}

if($instanceID == $AUTH->data['instance']["instances_id"]) { // For transfering assets between instances, don't include the subquery
    $assetCategories= $DBLIB->subQuery();
    $assetCategories->where("assets.instances_id",$instanceID);
    $assetCategories->where("assets_deleted",0);
    $assetCategories->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
    $assetCategories->groupBy ("assetTypes.assetCategories_id");
    $assetCategories->get ("assets", null, "assetCategories_id");
    $DBLIB->where("assetCategories_id", $assetCategories, "IN");
}
$DBLIB->orderBy("assetCategoriesGroups.assetCategoriesGroups_order", "ASC");
$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->where("assetCategories_deleted",0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $instanceID . "')");
if (isset($_POST['term']) and $_POST['term']) $DBLIB->where("assetCategories_name","%" . $_POST['term'] . "%","LIKE");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$categories = $DBLIB->get('assetCategories');
finish(true, [], $categories);

/** @OA\Post(
 *     path="/categories/search.php", 
 *     summary="Search Asset Categories", 
 *     description="Search for categories", 
 *     operationId="searchCategories", 
 *     tags={"categories"}, 
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
 *                     description="A list of categories",
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
 *         description="The search term",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="instance_id",
 *         in="query",
 *         description="the InstanceID",
 *         required="false", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */