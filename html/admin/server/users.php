<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Users", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("USERS:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA["mailings"] = [];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
$DBLIB->orderBy("users.users_created", "ASC");
$DBLIB->where("users_deleted", 0);
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		users_username LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR users_name1 LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR users_name2 LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR users_email LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
    )");
}
//if (!isset($_GET['suspended'])) $DBLIB->where ("users.users_suspended", "0");
$users = $DBLIB->get('users', null, ["users.*"]);
foreach ($users as $user) {
	$DBLIB->where('users_userid', $user['users_userid']);
	$PAGEDATA["mailings"][$user['users_userid']] = $DBLIB->get('emailSent'); //Get user's E-Mails
	$user['emails'] = [];
	foreach ($PAGEDATA["mailings"][$user['users_userid']] as $email) {
		$user['emails'][] = $email['emailSent_id'];
	}
	$user['users_emails'] = implode(",", $user['emails']);

	$DBLIB->where("userInstances.users_userid", $user['users_userid']);
	$DBLIB->where("userInstances.userInstances_deleted",  0);
	$DBLIB->where("instances.instances_deleted", 0);
	$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
	$DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
	$user['instances'] = $DBLIB->get("userInstances", null, ["instances.instances_name", "instances.instances_plan", "userInstances.userInstances_label","userInstances.userInstances_archived","instancePositions.instancePositions_displayName"]);

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
