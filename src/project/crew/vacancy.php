<?php
require_once __DIR__ . '/../../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("projectsVacantRoles.projectsVacantRoles_id",$_GET['id']);
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_deleted",0);
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_open",1);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(projectsVacantRoles.projectsVacantRoles_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", projectsVacantRoles.projectsVacantRoles_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
$DBLIB->where("(projectsVacantRoles.projectsVacantRoles_deadline IS NULL OR projectsVacantRoles.projectsVacantRoles_deadline >= '" . date("Y-m-d H:i:s") . "')");
$DBLIB->where("(projectsVacantRoles.projectsVacantRoles_slots > projectsVacantRoles.projectsVacantRoles_slotsFilled)");
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);

$role = $DBLIB->getone("projectsVacantRoles",["projectsVacantRoles.*","projects.*","users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$role) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("projectsVacantRoles_id",$role['projectsVacantRoles_id']);
$DBLIB->where("projectsVacantRolesApplications_deleted",0);
$DBLIB->where("projectsVacantRolesApplications_withdrawn",0);
$DBLIB->where("users_userid",$AUTH->data['users_userid']);
$role['application'] = $DBLIB->getOne("projectsVacantRolesApplications",["projectsVacantRolesApplications_id"]);
$role['projectsVacantRoles_questions'] = json_decode($role['projectsVacantRoles_questions'],true);
$PAGEDATA['role'] = $role;

// Users for security settings
if ($PAGEDATA['role']['projectsVacantRoles_applicationVisibleToUsers'] != null) {
  $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
  $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
  $DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
  $DBLIB->where("userInstances.userInstances_deleted",  0);
  $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
  $DBLIB->where("users.users_userid", explode(",", $PAGEDATA['role']['projectsVacantRoles_applicationVisibleToUsers']),"IN");
  $PAGEDATA['role']['canViewApplications'] = $DBLIB->get("users", null, ["users.users_userid", "users.users_name1", "users.users_name2"]);
} else $PAGEDATA['role']['canViewApplications'] = [];

if (isset($_GET['from'])) $PAGEDATA['referringPage'] = $_GET['from'];

$PAGEDATA['pageConfig'] = ["TITLE" => 'Crew Vacancy: ' . $role['projects_name'] . ' - ' . $role['projectsVacantRoles_name'], "BREADCRUMB" => true];

echo $TWIG->render('project/crew/vacancy.twig', $PAGEDATA);
?>
