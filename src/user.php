<?php
require_once __DIR__ . '/common/headSecure.php';
require_once __DIR__ . '/api/notifications/notificationTypes.php';

if (!isset($_GET['id'])) $_GET['id'] = $AUTH->data['users_userid'];

$PAGEDATA['googleAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_SECRET") != false;
$PAGEDATA['microsoftAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_APP_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_KEYS_SECRET") != false;

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
if ($AUTH->instancePermissionCheck("BUSINESS:USERS:VIEW:INDIVIDUAL_USER") or $AUTH->serverPermissionCheck("USERS:EDIT")) {
   $DBLIB->where("users.users_userid", $_GET['id']);
   if (!$AUTH->serverPermissionCheck("USERS:EDIT")) { //Superadmins can get at any user, but restrict users in that instance to just instance users
      $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
      $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
      $DBLIB->where("userInstances.userInstances_deleted",  0);
      $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
      $DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
   }
}
else $DBLIB->where("users.users_userid", $AUTH->data['users_userid']); //Only allow them to select themselves
$PAGEDATA['user'] = $DBLIB->getone("users", ["users.*"]);
if (!$PAGEDATA['user']) die($TWIG->render('404.twig', $PAGEDATA));

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
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$DBLIB->orderBy("projects.projects_dates_use_end", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->where("projects.instances_id",  $AUTH->data['instance']['instances_id']);
$PAGEDATA['user']['crewAssignments'] = $DBLIB->get("crewAssignments", null, ["crewAssignments.*", "projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projects.projects_id", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour","projectsStatuses.projectsStatuses_backgroundColour"]);

//Project Manager Roles
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
$DBLIB->where("projects.projects_manager",  $PAGEDATA['user']['users_userid']);
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$DBLIB->where("projects.instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("projects.projects_dates_use_end", "ASC");
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->where("projects.projects_parent_project_id IS NULL");
$PAGEDATA['user']['projectManagement'] = $DBLIB->get("projects", null, ["projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projects.projects_id", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour","projectsStatuses.projectsStatuses_backgroundColour"]);

$DBLIB->where("users_userid", $PAGEDATA['user']['users_userid']);
$DBLIB->orderBy("userPositions_start", "ASC");
$DBLIB->orderBy("userPositions_end", "ASC");
$DBLIB->join("positions", "positions.positions_id=userPositions.positions_id", "LEFT");
$PAGEDATA['user']['POSITIONS'] = $DBLIB->get("userPositions");

$DBLIB->where("users_userid", $PAGEDATA['user']['users_userid']);
$DBLIB->where("userPositions_end >= '" . date('Y-m-d H:i:s') . "'");
$DBLIB->where("userPositions_start <= '" . date('Y-m-d H:i:s') . "'");
$PAGEDATA['user']['currentPositions'] = $DBLIB->getvalue("userPositions","COUNT(*)"); //To see if they can login

$DBLIB->orderBy("positions_rank", "ASC");
$DBLIB->orderBy("positions_displayName", "ASC");
$PAGEDATA['POSSIBLEPOSITIONS'] = $DBLIB->get("positions");

$PAGEDATA['user']['notifications'] = $bCMS->notificationSettings($PAGEDATA['user']['users_userid']);

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['user']['users_name1'] . " " . $PAGEDATA['user']['users_name2'], "BREADCRUMB" => false];

$PAGEDATA['NOTIFICATIONTYPES'] = $NOTIFICATIONTYPES;

echo $TWIG->render('user.twig', $PAGEDATA);
?>
