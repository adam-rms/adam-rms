<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_CATEGORIES:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetCategories_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetCategories_deleted", 0);
$DBLIB->where("assetCategories_id", $array['assetCategories_id']);
$category = $DBLIB->update("assetCategories", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "assetCategories", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/categories/edit.php", 
 *     summary="Edit Asset Category", 
 *     description="Edit a category
", 
 *     operationId="editCategory", 
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
 *                     description="A null Array",
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
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The category data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="assetCategories_id", 
 *                 type="integer", 
 *                 description="The ID of the category",
 *             ),
 *             @OA\Property(
 *                 property="assetCategories_name", 
 *                 type="string", 
 *                 description="The name of the category",
 *             ),
 *             @OA\Property(
 *                 property="assetCategories_fontAwesome", 
 *                 type="string", 
 *                 description="The font awesome icon for the category",
 *             ),
 *             @OA\Property(
 *                 property="assetCategoriesGroups_id", 
 *                 type="integer", 
 *                 description="The ID of the category group",
 *             ),
 *         ),
 *     ), 
 * )
 */