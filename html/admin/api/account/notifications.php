<?php
	require_once __DIR__ . '/../apiHeadSecure.php';
	if ($_POST['users_userid'] != $AUTH->data["users_userid"] && $AUTH->serverPermissionCheck("USERS:EDIT:NOTIFICATION_SETTINGS")) $userid = $bCMS->sanitizeString($_POST['users_userid']);
	else $userid = $AUTH->data["users_userid"];
	$DBLIB->where("users_userid", $userid);
	if ($DBLIB->update ('users', ["users_notificationSettings" => json_encode($_POST['settings'])])) {
		$bCMS->auditLog("UPDATE", "users", "CHANGE NOTIFICATION SETTINGS", $AUTH->data['users_userid'],$userid);
		finish(true);
	}
	else finish(false);

/** @OA\Post(
 *     path="/account/notifications.php", 
 *     summary="Notifications", 
 *     description="Set the notification settings for a user", 
 *     operationId="setNotifications", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 * 		   @OA\MediaType(
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
 *         name="settings",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="json"), 
 *         ), 
 * )
 */