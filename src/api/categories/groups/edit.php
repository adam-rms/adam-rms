<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_CATEGORIES:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetCategoriesGroups_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetCategoriesGroups_deleted", 0);
$DBLIB->where("assetCategoriesGroups_id", $array['assetCategoriesGroups_id']);
$category = $DBLIB->update("assetCategoriesGroups", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "assetCategoriesGroups", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/categories/edit.php", 
 *     summary="Edit Asset Category Group", 
 *     description="Edit an Asset Category Group (Parent)", 
 *     operationId="editCategoryGroup", 
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
 *         description="The category group data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="assetCategoriesGroups_id", 
 *                 type="integer", 
 *                 description="The ID of the category group",
 *             ),
 *             @OA\Property(
 *                 property="assetCategoriesGroups_name", 
 *                 type="string", 
 *                 description="The new name of the category group",
 *             ),
 *             @OA\Property(
 *                 property="assetCategoriesGroups_fontAwesome", 
 *                 type="string", 
 *                 description="The font awesome icon for the category group",
 *             ),
 *         ),
 *     ), 
 * )
 */
