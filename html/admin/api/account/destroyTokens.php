<?php
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");


if (!$AUTH->serverPermissionCheck("VIEW-AUDIT-LOG") or !isset($_POST['userid'])) die("404");

if ($AUTH->destroyTokens($bCMS->sanitizeString($_POST['userid']))) {
    $bCMS->auditLog("INVALIDATE ALL", "authTokens", null, $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST["userid"]));
    die("1");
} else die("2");


/** @OA\Post(
 *     path="/account/destroyTokens.php", 
 *     summary="Destroy Tokens", 
 *     description="Destroy all tokens for a user", 
 *     operationId="destroyTokens", 
 *     tags={"account"}, 
 *     @OA\Parameter(
 *         name="userid",
 *         in="query",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"
 *         ), 
 *     ), 
 * )
 */