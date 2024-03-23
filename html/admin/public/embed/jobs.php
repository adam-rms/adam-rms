<?php
require_once 'head.php';
if (!isset($PAGEDATA['INSTANCE']['publicData']['enableVacancies']) or !$PAGEDATA['INSTANCE']['publicData']['enableVacancies']) die("Disabled by your AdamRMS administrator");

$PAGEDATA['pageConfig'] = ["TITLE" => "Crew Role Vacancies", "BREADCRUMB" => false];
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_deleted",0);
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_open",1);
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_visibleToGroups IS NULL"); //if only visible to certain groups, should not be public - this is just a check
$DBLIB->where("(projectsVacantRoles.projectsVacantRoles_deadline IS NULL OR projectsVacantRoles.projectsVacantRoles_deadline >= '" . date("Y-m-d H:i:s") . "')");
$DBLIB->where("(projectsVacantRoles.projectsVacantRoles_slots > projectsVacantRoles.projectsVacantRoles_slotsFilled)");
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->where("projects.instances_id", $PAGEDATA['INSTANCE']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->where("projectsVacantRoles_showPublic",1);
$DBLIB->orderBy("projects.projects_dates_use_start","ASC");
$DBLIB->orderBy("projectsVacantRoles.projects_id","ASC");
$DBLIB->orderBy("projectsVacantRoles.projectsVacantRoles_deadline","ASC");
$DBLIB->orderBy("projectsVacantRoles.projectsVacantRoles_added","ASC");
$roles = $DBLIB->get("projectsVacantRoles",null,["projectsVacantRoles.*","projects.*", "clients.clients_name", "users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
$PAGEDATA['roles'] = [];
foreach($roles as $role) {
    $role['projectsVacantRoles_questions'] = json_decode($role['projectsVacantRoles_questions'],true);

    //get parent project if set
    if ($role['projects_parent_project_id']) {
        $DBLIB->where("projects_id",$role['projects_parent_project_id']);
        $DBLIB->where("projects_deleted",0);
        $DBLIB->where("projects_archived",0);
        $role['parentProject'] = $DBLIB->getOne("projects",["projects_id","projects_name"]);
    }

    $PAGEDATA['roles'][] = $role;
}

$PAGEDATA['showClients'] = (isset($PAGEDATA['INSTANCE']['publicData']['showVacancyClients']) and $PAGEDATA['INSTANCE']['publicData']['showVacancyClients']);

echo $TWIG->render('public/embed/jobsPublic.twig', $PAGEDATA);
?>
