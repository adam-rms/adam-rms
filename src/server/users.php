<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Users", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("USERS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
$DBLIB->orderBy("users.users_created", "ASC");
$DBLIB->where("users_deleted", 0);
$users = $DBLIB->get('users', null, ["users.users_email", "users.users_userid", "users.users_emailVerified", "users.users_name1", "users.users_name2", "users.users_suspended", "users.users_termsAccepted", "users.users_thumbnail", "users.users_username"]);
foreach ($users as $user) {
	$DBLIB->where('users_userid', $user['users_userid']);
	$user['emails'] = $DBLIB->get('emailSent', null, ["emailSent_id", "emailSent_subject"]); //Get user's E-Mails
	$user['email_ids'] = array_map(function ($a) {
		return $a['emailSent_id'];
	}, $user['emails']);
	$user['email_ids'] = implode(",", $user['email_ids']);

	$DBLIB->where("userInstances.users_userid", $user['users_userid']);
	$DBLIB->where("userInstances.userInstances_deleted",  0);
	$DBLIB->where("instances.instances_deleted", 0);
	$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
	$DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
	$user['instances'] = $DBLIB->get("userInstances", null, ["instances.instances_name", "instances.instances_planName", "userInstances.userInstances_label","userInstances.userInstances_archived","instancePositions.instancePositions_displayName"]);

	$DBLIB->where("users_userid", $user['users_userid']);
	$DBLIB->where("userPositions_end >= '" . date('Y-m-d H:i:s') . "'");
	$DBLIB->where("userPositions_start <= '" . date('Y-m-d H:i:s') . "'");
	$DBLIB->orderBy("positions_rank", "ASC");
	$DBLIB->orderBy("positions_displayName", "ASC");
	$DBLIB->join("positions", "positions.positions_id=userPositions.positions_id", "LEFT");
	$user['currentPositions'] = $DBLIB->get("userPositions",null,["positions.positions_displayName","userPositions.userPositions_displayName"]);

	$DBLIB->where("users_userid", $user['users_userid']);
	$DBLIB->where("(authTokens_adminId IS NULL)");
	$DBLIB->orderBy("authTokens_created", "DESC");
	$user['lastLogin'] = $DBLIB->getOne("authTokens",["authTokens_created"]);

	$DBLIB->where("users_userid", $user['users_userid']);
	$DBLIB->where("(adminUser_users_userid IS NULL)");
	$DBLIB->orderBy("analyticsEvents_timestamp", "DESC");
	$user['lastAnalytics'] = $DBLIB->getOne("analyticsEvents",["analyticsEvents_timestamp"]);


	$PAGEDATA["users"][] = $user;
}

echo $TWIG->render('server/users.twig', $PAGEDATA);
?>
