<?php
require_once __DIR__ . '/../../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Project Crew Recruitment & Advertising", "BREADCRUMB" => false];

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*"]);
if (!$PAGEDATA['project']) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("projectsVacantRoles_deleted",0);
$DBLIB->where("projects_id",$PAGEDATA['project']['projects_id']);
$DBLIB->orderBy("projectsVacantRoles_added","ASC");
$roles = $DBLIB->get("projectsVacantRoles");
$PAGEDATA['roles'] = [];
foreach($roles as $role) {
    $DBLIB->where("projectsVacantRoles_id",$role['projectsVacantRoles_id']);
    $DBLIB->where("projectsVacantRolesApplications_deleted",0);
    $DBLIB->where("projectsVacantRolesApplications_withdrawn",0);
    $role['applications'] = $DBLIB->getvalue("projectsVacantRolesApplications","count(*)");
    $role['projectsVacantRoles_questionsArray'] = json_decode($role['projectsVacantRoles_questions'],true);

    if ($role['projectsVacantRoles_privateToPM'] == 1) {
        if ($PAGEDATA['project']['projects_manager'] == $AUTH->data["users_userid"]) $role['canViewApplications'] = true;
        else $role['canViewApplications'] = false;
    } elseif ($role['projectsVacantRoles_applicationVisibleToUsers'] != null) {
        if (in_array($AUTH->data["users_userid"], explode(",",$role['projectsVacantRoles_applicationVisibleToUsers'])) or $PAGEDATA['project']['projects_manager'] == $AUTH->data["users_userid"]) $role['canViewApplications'] = true;
        else $role['canViewApplications'] = false;
    } else $role['canViewApplications'] = true;

    $PAGEDATA['roles'][] = $role;
}

// Positions for edit form
$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions");

// Users for security settings
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$PAGEDATA['users'] = $DBLIB->get("users", null, ["users.users_userid", "users.users_name1", "users.users_name2"]);

$PAGEDATA['publicData'] = json_decode($AUTH->data['instance']['instances_publicConfig'],true);

echo $TWIG->render('project/crew/vacantCrew.twig', $PAGEDATA);
?>
