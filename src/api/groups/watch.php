<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['assetGroups_id']) or !is_numeric($_POST['assetGroups_id'])) finish(false);

$current = $AUTH->data['users_assetGroupsWatching'];
$current = explode(",",$current);
if (in_array($_POST['assetGroups_id'],$current)) unset($current[array_search($_POST['assetGroups_id'],$current)]);
else array_push($current, $_POST['assetGroups_id']);

$current = implode(",",array_filter($current));

$DBLIB->where("users_userid", $AUTH->data['users_userid']);
if (!$DBLIB->update("users",["users_assetGroupsWatching" => $current],1)) finish(false);
$bCMS->auditLog("UPDATE", "users", "Set watching to " . $current, $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/groups/watch.php", 
 *     summary="Watch Group", 
 *     description="Watch a group
", 
 *     operationId="watchGroup", 
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
 * )
 */