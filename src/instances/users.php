<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Users in Business", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:VIEW:LIST")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("userInstances.userInstances_archived","ASC");
$DBLIB->orderBy("instancePositions.instancePositions_rank", "ASC");
$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
$DBLIB->orderBy("users.users_created", "ASC");
$DBLIB->where("users_deleted", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$users = $DBLIB->get('users', null, ["users.users_username", "users.users_name1", "users.users_name2", "users.users_userid", "users.users_email", "users.users_emailVerified", "users.users_suspended", "users.users_suspended", "instancePositions.instancePositions_displayName", "userInstances.userInstances_label", "userInstances.userInstances_id", "userInstances.instancePositions_id", "users.users_thumbnail", "userInstances.userInstances_archived"]);
foreach ($users as $user) {
	$PAGEDATA["users"][] = $user;
}


$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions", null, ["instancePositions_id", "instancePositions_displayName"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$userCapacity = $DBLIB->getvalue("instances", "instances_userLimit");
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid", "LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$userUsed = $DBLIB->getValue("users", "COUNT(users.users_userid)");
if ($userCapacity > 0 and $userUsed >= $userCapacity) {
	$PAGEDATA['NOCAPACITY'] = [
		"CAPACITY" => $userCapacity,
		"USED" => $userUsed
	];
}

echo $TWIG->render('instances/instances_users.twig', $PAGEDATA);
?>
