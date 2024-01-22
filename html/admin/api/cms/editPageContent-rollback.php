<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT")) die("404");

if (!isset($_POST['cmsPages_id'])) finish(false);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
$DBLIB->where("cmsPages_id",$_POST['cmsPages_id']);
$page = $DBLIB->getOne("cmsPages",["cmsPages_id"]);
if (!$page) finish(false);

//Get new revision number
$DBLIB->where("cmsPages_id",$_POST['cmsPages_id']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
$lastUpdate = $DBLIB->getOne("cmsPagesDrafts",["cmsPagesDrafts_revisionID"]);
if ($lastUpdate) $revision = $lastUpdate['cmsPagesDrafts_revisionID']+1;
else $revision = 1;

//Get old revision data
$DBLIB->where("cmsPages_id",$_POST['cmsPages_id']);
$DBLIB->where("cmsPagesDrafts_id",$_POST['change']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
$restoreRevision = $DBLIB->getOne("cmsPagesDrafts",["cmsPagesDrafts_revisionID","cmsPagesDrafts_data"]);
if (!$restoreRevision) finish(false);

$insert = $DBLIB->insert("cmsPagesDrafts",[
    "cmsPages_id" => $_POST['cmsPages_id'],
    "cmsPagesDrafts_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "cmsPagesDrafts_data" => $restoreRevision["cmsPagesDrafts_data"],
    "cmsPagesDrafts_changelog" => "Rollback to revision v" . $restoreRevision['cmsPagesDrafts_revisionID'],
    "cmsPagesDrafts_revisionID" => $revision
]);
if ($insert) finish(true);
else finish(false);

/** @OA\Post(
 *     path="/cms/editPageContent-rollback.php", 
 *     summary="Rollback CMS Page Content", 
 *     description="Rollback a page content  
Requires Instance Permission CMS:CMS_PAGES:EDIT", 
 *     operationId="rollbackPageContent", 
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
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="cmsPages_id",
 *         in="query",
 *         description="The ID of the page",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="change",
 *         in="query",
 *         description="The description of the change",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */