<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));
$PAGEDATA['pageConfig'] = ["TITLE" => "New Project", "BREADCRUMB" => false];

$PAGEDATA['NOCAPACITY'] = !$bCMS->instanceHasProjectCapacity($AUTH->data['instance']['instances_id']);

//Potential project types
$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("projectsTypes_name", "ASC");
$PAGEDATA['potentialProjectTypes'] = $DBLIB->get("projectsTypes");

//Potential project managers for new project
$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
$DBLIB->orderBy("users.users_created", "ASC");
$DBLIB->where("users_deleted", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid", "LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$PAGEDATA['potentialProjectManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);

echo $TWIG->render('project/project_new.twig', $PAGEDATA);
