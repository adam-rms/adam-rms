<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['p']) or strlen($_POST['p']) < 1) finish(false, ["code" => "ARG_MISSING", "message" => "Missing argument 'p'"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted", 0);
$DBLIB->where("cmsPages_archived", 0);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id", $_POST['p']);
$PAGE = $DBLIB->getOne("cmsPages");
if (!$PAGE) finish(false, ["code" => "NO_PAGE", "message" => "Page not found"]);

$DBLIB->where("cmsPages_id", $PAGE['cmsPages_id']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp", "DESC");
$PAGE['DRAFTS'] = $DBLIB->getOne("cmsPagesDrafts", ["cmsPagesDrafts_id", "cmsPagesDrafts_data", "cmsPagesDrafts_revisionID"]);
if ($PAGE['DRAFTS']) {
    $PAGE['DRAFTS']['cmsPagesDrafts_dataARRAY'] = json_decode($PAGE['DRAFTS']['cmsPagesDrafts_data'], true);
} else {
    //we have no current drafts
    $PAGE['DRAFTS']['cmsPagesDrafts_dataARRAY'] = [];
}

$DBLIB->insert("cmsPagesViews", [
    "cmsPages_id" => $PAGE['cmsPages_id'],
    "cmsPagesViews_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "cmsPages_type" => 2
]);
$PAGE['CONTENT'] = $TWIG->render('assets/templates/cmsPage.twig', ['pageData' => $PAGE]);
finish(true, null, $PAGE);

/** @OA\Post(
 *     path="/cms/get.php", 
 *     summary="Get CMS Page", 
 *     description="Get a page", 
 *     operationId="getPage", 
 *     tags={"cms"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/html", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="PUBLIC", 
 *                     type="boolean", 
 *                     description="undefined",
 *                 ),
 *                 @OA\Property(
 *                     property="html", 
 *                     type="html", 
 *                     description="HTML code for the page content",
 *                 ),
 *         ),
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
 *         name="p",
 *         in="query",
 *         description="The page id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
