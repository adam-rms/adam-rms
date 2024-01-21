<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->where("users_userid", $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_dark_mode" => isset($_POST['dark']) ? 1 : 0])) finish(true);
else finish(false);


/** @OA\Post(
 *     path="/account/theme.php", 
 *     summary="Theme", 
 *     description="Set the theme for the current user", 
 *     operationId="setTheme", 
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
 *         name="dark",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */