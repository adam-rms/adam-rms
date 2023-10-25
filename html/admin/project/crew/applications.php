<?php
require_once __DIR__ . '/../../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("projectsVacantRoles_deleted",0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->where("projectsVacantRoles_id",$_GET['id']);
$PAGEDATA['role'] = $DBLIB->getOne("projectsVacantRoles");
if (!$PAGEDATA['role']) die($TWIG->render('404.twig', $PAGEDATA));

if ($PAGEDATA['role']['projectsVacantRoles_privateToPM'] == 1) {
    if ($PAGEDATA['role']['projects_manager'] != $AUTH->data["users_userid"]) die($TWIG->render('404.twig', $PAGEDATA));
} elseif ($PAGEDATA['role']['projectsVacantRoles_applicationVisibleToUsers'] != null) {
    if (!in_array($AUTH->data["users_userid"], explode(",",$PAGEDATA['role']['projectsVacantRoles_applicationVisibleToUsers'])) and $PAGEDATA['role']['projects_manager'] != $AUTH->data["users_userid"]) die($TWIG->render('404.twig', $PAGEDATA));
}


$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['role']['projectsVacantRoles_name'] . " - Applications | " . $PAGEDATA['role']['projects_name'], "BREADCRUMB" => false];

$DBLIB->where("projectsVacantRoles_id",$PAGEDATA['role']["projectsVacantRoles_id"]);
$DBLIB->where("projectsVacantRolesApplications_deleted",0);
$DBLIB->join("users","projectsVacantRolesApplications.users_userid=users.users_userid","LEFT");
$DBLIB->orderBy("projectsVacantRolesApplications_submitted","ASC");
$applications = $DBLIB->get("projectsVacantRolesApplications", null, ["projectsVacantRolesApplications.*","users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
$PAGEDATA['applications'] = [];
foreach ($applications as $application) {
    $application['projectsVacantRolesApplications_questionAnswers'] = json_decode($application['projectsVacantRolesApplications_questionAnswers'],true);

    if ($application['projectsVacantRolesApplications_files'] != "") {
        $DBLIB->where("s3files_meta_type", 18);
        $DBLIB->where("s3files_id", explode(",",$application['projectsVacantRolesApplications_files']),"IN");
        $DBLIB->where("s3files_meta_subType", $PAGEDATA['role']["projectsVacantRoles_id"]);
        $DBLIB->where("(s3files_meta_deleteOn >= '". date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)");
        $DBLIB->where("s3files_meta_physicallyStored",1);
        $DBLIB->orderBy("s3files_meta_uploaded", "ASC");
        $application['files'] = $DBLIB->get("s3files", null, ["s3files_id", "s3files_extension", "s3files_name","s3files_meta_size", "s3files_meta_uploaded"]);
    } else $application['files'] = [];


    $PAGEDATA['applications'][] = $application;
}

echo $TWIG->render('project/crew/applications.twig', $PAGEDATA);
?>
