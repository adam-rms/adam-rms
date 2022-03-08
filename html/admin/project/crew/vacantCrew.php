<?php
require_once __DIR__ . '/../../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(123)) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Project Crew Recruitment & Advertising", "BREADCRUMB" => false];

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*"]);
if (!$PAGEDATA['project']) die("404");

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
    $PAGEDATA['roles'][] = $role;
}

$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions");

$PAGEDATA['publicData'] = json_decode($AUTH->data['instance']['instances_publicConfig'],true);

echo $TWIG->render('project/crew/vacantCrew.twig', $PAGEDATA);
?>
