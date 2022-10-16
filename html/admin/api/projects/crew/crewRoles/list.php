<?php
require_once __DIR__ . '/../../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(124)) die("404");

$DBLIB->where("projectsVacantRoles.projectsVacantRoles_deleted",0);
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_open",1);
$DBLIB->where("(projectsVacantRoles.projectsVacantRoles_deadline IS NULL OR projectsVacantRoles.projectsVacantRoles_deadline >= '" . date("Y-m-d H:i:s") . "')");
$DBLIB->where("(projectsVacantRoles.projectsVacantRoles_slots > projectsVacantRoles.projectsVacantRoles_slotsFilled)");
if (isset($_POST['projects_id'])){
    $DBLIB->where("projectsVacantRoles.projects_id", $_POST['projects_id']);
}
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->orderBy("projects.projects_dates_use_start","ASC");
$DBLIB->orderBy("projectsVacantRoles.projects_id","ASC");
$DBLIB->orderBy("projectsVacantRoles.projectsVacantRoles_deadline","ASC");
$DBLIB->orderBy("projectsVacantRoles.projectsVacantRoles_added","ASC");
$roles = $DBLIB->get("projectsVacantRoles",null,["projectsVacantRoles.*","projects.*","users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
$formattedRole = [];
foreach($roles as $role) {
    $DBLIB->where("projectsVacantRoles_id",$role['projectsVacantRoles_id']);
    $DBLIB->where("projectsVacantRolesApplications_deleted",0);
    $DBLIB->where("projectsVacantRolesApplications_withdrawn",0);
    $DBLIB->where("users_userid",$AUTH->data['users_userid']);
    $role['application'] = $DBLIB->getOne("projectsVacantRolesApplications",["projectsVacantRolesApplications_id"]);
    $role['projectsVacantRoles_questions'] = json_decode($role['projectsVacantRoles_questions'],true);
    $formattedRole[] = $role;
}

finish(true, null, $formattedRole);