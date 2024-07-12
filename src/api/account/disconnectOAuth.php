<?php
	require_once __DIR__ . '/../apiHeadSecure.php';
	if ($_POST['users_userid'] != $AUTH->data["users_userid"]) finish(false);

	if ($_POST['provider'] == "google") $column = "users_oauth_googleid";
elseif ($_POST['provider'] == "microsoft") $column = "users_oauth_microsoftid";
	else finish(false);

	$DBLIB->where("users_userid",$_POST['users_userid']);
	if ($DBLIB->update ('users', [$column => null])) finish(true);
	else finish(false);

/** @OA\Post(
 *     path="/account/disconnectOAuth.php", 
 *     summary="Disconnect OAuth", 
 *     description="Disconnect an OAuth provider", 
 *     operationId="disconnectOAuth", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="provider",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
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
