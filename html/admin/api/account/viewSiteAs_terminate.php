<?php
require_once __DIR__ . '/../apiHeadSecure.php';


if (!$AUTH->data['viewSiteAs']) die("404");

if ($AUTH->generateToken($AUTH->data['viewSiteAs']['users_userid'], false, "Web", "web-session")) header('Location: '. $CONFIG['ROOTURL']);

/** @OA\Post(
 *     path="/account/viewSiteAs_terminate.php", 
 *     summary="View Site As Terminate", 
 *     description="Terminate the view site as session", 
 *     operationId="viewSiteAsTerminate", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="308", 
 *         description="Success",
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="text/plain", 
 *             @OA\Schema( 
 *                 type="string", 
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="viewSiteAs",
 *         in="body",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"
 *         ), 
 *     ), 
 * )
 */