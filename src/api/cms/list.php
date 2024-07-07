<?php
require_once __DIR__ . '/../apiHeadSecure.php';

//Essentially a duplicate of headSecure

$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
$DBLIB->where("cmsPages_showNav",1);
$DBLIB->where("cmsPages_subOf",NULL,"IS");
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
$DBLIB->orderBy("cmsPages_navOrder","ASC");
$DBLIB->orderBy("cmsPages_id","ASC");
$NAVIGATIONCMSPages = [];
foreach ($DBLIB->get("cmsPages",null,["cmsPages_fontAwesome","cmsPages_name","cmsPages_id"]) as $page) {
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->where("cmsPages_deleted",0);
    $DBLIB->where("cmsPages_archived",0);
    $DBLIB->where("cmsPages_showNav",1);
    $DBLIB->where("cmsPages_subOf",$page['cmsPages_id']);
    if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
    $DBLIB->orderBy("cmsPages_name","ASC");
    $page['SUBPAGES'] = $DBLIB->get("cmsPages",null,["cmsPages_fontAwesome","cmsPages_name","cmsPages_id"]);
    $NAVIGATIONCMSPages[] = $page;
}
finish(true,null,$NAVIGATIONCMSPages);

/** @OA\Get(
 *     path="/cms/list.php", 
 *     summary="List CMS Pages", 
 *     description="List all pages", 
 *     operationId="listPages", 
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
 *                     description="An Array containing all pages",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     )
 */