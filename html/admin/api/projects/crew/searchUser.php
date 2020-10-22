<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(48) or !isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id","projects_name", "projects_dates_use_start","projects_dates_use_end"]);
if (!$project) finish(false);

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
if (strlen($_POST['term']) > 0) {
    $DBLIB->where("(
		users_email LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
		OR users_name1 LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
		OR users_name2 LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'	
		OR CONCAT( users_name1,  ' ', users_name2 ) LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
    )");
}
$users = $DBLIB->get("users", 15, ["users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$users) finish(false, ["code" => "LIST-USERS-FAIL", "message"=> "Could not search for Users"]);
else {
    $userOutput = [];
    foreach ($users as $user) {
        //Search for clashes for that user - (duplicated in the clash checker system for the frontend)
        $DBLIB->where("users_userid", $user['users_userid']);
        $DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
        $DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->where("(crewAssignments.projects_id != " . $project['projects_id'] . ")");
        $DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
        $DBLIB->where("((projects_dates_use_start >= '" . $project["projects_dates_use_start"] . "' AND projects_dates_use_start <= '" . $project["projects_dates_use_end"] . "') OR (projects_dates_use_end >= '" . $project["projects_dates_use_start"] . "' AND projects_dates_use_end <= '" . $project["projects_dates_use_end"] . "') OR (projects_dates_use_end >= '" . $project["projects_dates_use_end"] . "' AND projects_dates_use_start <= '" . $project["projects_dates_use_start"] . "'))");
        $existingAssignments = $DBLIB->get("crewAssignments", null, ["projects.projects_name"]);
        $user['clashes'] = [];
        foreach ($existingAssignments as $assignment) {
            $user['clashes'][] = $assignment['projects_name'];
        }
        $userOutput[] = $user;
    }
    finish(true, null, $userOutput);
}
