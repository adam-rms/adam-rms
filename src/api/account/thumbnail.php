<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if ($_POST['users_userid'] != $PAGEDATA['USERDATA']['users_userid'] && $AUTH->serverPermissionCheck("USERS:EDIT:THUMBNAIL"))	$userid = $bCMS->sanitizeString($_POST['users_userid']);
else $userid = $PAGEDATA['USERDATA']['users_userid'];

$DBLIB->where("users_userid", $userid);
if ($DBLIB->update ('users', ["users_thumbnail" => $bCMS->sanitizeString($_POST['thumbnail'])])) {
	$bCMS->auditLog("UPDATE", "users", "CHANGE THUMBNAIL", $AUTH->data['users_userid'],$userid);
	finish(true);
}
else finish(false);

/** @OA\Post(
 *     path="/account/thumbnail.php", 
 *     summary="Thumbnail", 
 *     description="Set the thumbnail for a user", 
 *     operationId="setThumbnail", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="OK",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="users_userid",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="thumbnail",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */