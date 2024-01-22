<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_GROUPS:EDIT:ASSETS_WITHIN_GROUP")) die("404");
if (!isset($_POST['assetGroups_id']) or !is_numeric($_POST['assetGroups_id']) or !is_numeric($_POST['assetGroups_id'])) finish(false);

$DBLIB->where("assets_id",$_POST['assets_id']);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$asset = $DBLIB->getone("assets", ["assets_assetGroups","assets_tag","assetTypes_name"]);
if (!$asset) finish(false);

$DBLIB->where("assetGroups_id", $_POST['assetGroups_id']);
$DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$DBLIB->where("assetGroups_deleted",0);
$group = $DBLIB->getone('assetGroups',["assetGroups_name","assetGroups_id"]);
if (!$group) finish(false);

$current = $asset['assets_assetGroups'];
$current = explode(",", $current);
if (in_array($_POST['assetGroups_id'],$current)) unset($current[array_search($_POST['assetGroups_id'],$current)]);
$current = implode(",", array_filter($current));

$DBLIB->where("assets_id",$_POST['assets_id']);
if (!$DBLIB->update("assets", ["assets_assetGroups" => $current], 1)) finish(false);

$bCMS->auditLog("UPDATE", "assets", "Remove asset " . $_POST['assets_id'] . " from group " . $_POST['assetGroups_id'], $AUTH->data['users_userid']);

foreach ($bCMS->usersWatchingGroup($_POST['assetGroups_id']) as $user) {
    if ($user != $AUTH->data['users_userid']) notify(17,$user, $AUTH->data['instance']['instances_id'], "Asset " . $bCMS->aTag($asset['assets_tag']) . " removed from group " . $group['assetGroups_name'], "Asset " . $bCMS->aTag($asset['assets_tag']) . " (" . $asset["assetTypes_name"] . ") has been removed from the group " . $group['assetGroups_name'] . " by " . $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2']);
}

finish(true);

/** @OA\Post(
 *     path="/groups/removeAsset.php", 
 *     summary="Remove Asset from Group", 
 *     description="Remove an asset from a group  
Requires Instance Permission ASSETS:ASSET_GROUPS:EDIT:ASSETS_WITHIN_GROUP
", 
 *     operationId="removeAssetFromGroup", 
 *     tags={"groups"}, 
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
 *                     description="A null array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="assetGroups_id",
 *         in="query",
 *         description="The group id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assets_id",
 *         in="query",
 *         description="The asset id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */