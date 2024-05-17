<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("USERS:DELETE") or !isset($_POST['users_userid'])) die("404");

$AUTH->destroyTokens($bCMS->sanitizeString($_POST['users_userid']));
$bCMS->auditLog("INVALIDATE ALL", "authTokens", null, $AUTH->data['users_userid'], $bCMS->sanitizeString($_POST["users_userid"]));

$DBLIB->where('users_userid', $bCMS->sanitizeString($_POST['users_userid']));
if ($DBLIB->update('users', ["users_deleted" => 1], 1)) {
    $bCMS->auditLog("UPDATE", "users", "DELETE ", $AUTH->data['users_userid'], $bCMS->sanitizeString($_POST['users_userid']));
    finish(true);
} else finish(false, ["code" => "DB-ERROR", "message" => "Database error"]);

/** @OA\Post(
 *     path="/account/softDelete.php", 
 *     summary="Soft Delete", 
 *     description="Soft delete a user", 
 *     operationId="softDelete", 
 *     tags={"account"}, 
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
 *                     property="message", 
 *                     type="null", 
 *                     description="an empty array",
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
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
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
 * )
 */
