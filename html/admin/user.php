<?php
require_once __DIR__ . '/common/headSecure.php';

if (!isset($_GET['id'])) $_GET['id'] = $AUTH->data['users_userid'];

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
if ($AUTH->instancePermissionCheck(52)) $DBLIB->where("users.users_userid", $_GET['id']);
else $DBLIB->where("users.users_userid", $AUTH->data['users_userid']);
$PAGEDATA['user'] = $DBLIB->getone("users", ["users.*"]);

if ($PAGEDATA['user']['users_calendarHash'] == null) {
   $characters = 'abcdefghijklmnopqrstuvwxyz';
   $charactersLength = strlen($characters);
   $randomString = '';
   for ($i = 0; $i < 50; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
   }
 $DBLIB->where("users.users_userid", $PAGEDATA['user']['users_userid']);
   $DBLIB->update("users", ['users_calendarHash' => $randomString]);
   //Generate a calendar hash
 $PAGEDATA['user']['users_calendarHash'] = $randomString;
}
$PAGEDATA['user']['users_email_md5'] = md5($PAGEDATA['user']['users_email']);

//Main Crew Roles
$DBLIB->where("crewAssignments.users_userid", $PAGEDATA['user']['users_userid']);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$DBLIB->orderBy("projects.projects_dates_use_end", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$PAGEDATA['user']['crewAssignments'] = $DBLIB->get("crewAssignments", null, ["crewAssignments.*", "projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projects.projects_status", "projects.projects_id"]);

//Project Manager Roles
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
$DBLIB->where("projects.projects_manager",  $PAGEDATA['user']['users_userid']);
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$DBLIB->where("projects.instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("projects.projects_dates_use_end", "ASC");
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->orderBy("projects.projects_name", "ASC");
$PAGEDATA['user']['projectManagement'] = $DBLIB->get("projects", null, ["projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projects.projects_status", "projects.projects_id"]);




$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['user']['users_name1'] . " " . $PAGEDATA['user']['users_name2'], "BREADCRUMB" => false];

echo $TWIG->render('user.twig', $PAGEDATA);
?>
