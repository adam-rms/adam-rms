<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS")) die("404");

if (!isset($_POST['instancePositions_id']) or !is_numeric($_POST['instancePositions_id'])) finish(false);

$DBLIB->where("instancePositions_deleted",0);
$DBLIB->where("instancePositions_id",$_POST['instancePositions_id']);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
if (!$DBLIB->update("instancePositions", ["cmsPages_id" => ($_POST['cmsPages_id'] ? $_POST['cmsPages_id'] : null)], 1)) finish(false);

$bCMS->auditLog("CUSTOMDASHBOARD", "cmsPages", "Set the custom dashboard for " . $_POST['instancePositions_id'] . " to " . $_POST['cmsPages_id'], $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/cms/setCustomDashboard.php", 
 *     summary="Set Custom Dashboard", 
 *     description="Set a custom dashboard  
Requires Instance Permission CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS", 
 *     operationId="setCustomDashboard", 
 *     tags={"cms"}, 
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
 *         response="404", 
 *         description="Error",
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
 *         name="instancePositions_id",
 *         in="query",
 *         description="instance position for the dashboard",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="cmsPages_id",
 *         in="query",
 *         description="The page id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */