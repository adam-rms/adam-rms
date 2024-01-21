<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT")) die("404");

foreach ($_POST['order'] as $count=>$item) {
    if ($item == "") continue;
    $DBLIB->where("cmsPages_deleted", 0);
    $DBLIB->where("cmsPages_id",$item);
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    if (!$DBLIB->update("cmsPages", ["cmsPages_navOrder" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-CMSPAGES", "cmsPages", "Set the order of pages", $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/cms/editPageRank.php", 
 *     summary="Edit CMS Page Rank", 
 *     description="Edit a page rank  
Requires Instance Permission CMS:CMS_PAGES:EDIT", 
 *     operationId="editPageRank", 
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
 *         name="order",
 *         in="query",
 *         description="The page rank data",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 * )
 */