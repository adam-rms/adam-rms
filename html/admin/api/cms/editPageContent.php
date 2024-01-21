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

//Sanitize data
$pageData = $_POST['pageData'];
foreach($pageData['cards'] as $i => $card) {
    $pageData['cards'][$i]['content'] = $bCMS->cleanString($card['content']);
}

$insert = $DBLIB->insert("cmsPagesDrafts",[
    "cmsPages_id" => $_POST['cmsPages_id'],
    "cmsPagesDrafts_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "cmsPagesDrafts_data" => json_encode($pageData),
    "cmsPagesDrafts_changelog" => $_POST['changelog'],
    "cmsPagesDrafts_revisionID" => $revision
]);
if ($insert) finish(true,null,["revision"=>$revision,"revisionID"=>$insert]);
else finish(false);

/** @OA\Post(
 *     path="/cms/editPageContent.php", 
 *     summary="Edit CMS Page Content", 
 *     description="Edit a page content  
Requires Instance Permission CMS:CMS_PAGES:EDIT", 
 *     operationId="editPageContent", 
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
 *         name="pageData",
 *         in="query",
 *         description="The page data",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="changelog",
 *         in="query",
 *         description="The description of the change",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */