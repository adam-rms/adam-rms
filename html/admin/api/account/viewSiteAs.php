<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("USERS:VIEW_SITE_AS")) die("Sorry - you can't access this page");
if (!(isset($_POST['userid']))) die("No uid passed");

if ($AUTH->generateToken($bCMS->sanitizeString($_POST['userid']), $AUTH->data['users_userid'], "Web - View Site As", "web-session")) {
    $bCMS->auditLog("VIEWSITEAS", "users", null, $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST['userid']));
    header('Location: '. $CONFIG['ROOTURL']);
}

/** @OA\Post(
 *     path="/account/viewSiteAs.php", 
 *     summary="View Site As", 
 *     description="View the site as a given user  
Requires server permission USERS:VIEW_SITE_AS
", 
 *     operationId="viewSiteAs", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="text/plain", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="308", 
 *         description="Success",
 *     ), 
 *     @OA\Parameter(
 *         name="users_userid",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"
 *         ), 
 *     ), 
 * )
 */