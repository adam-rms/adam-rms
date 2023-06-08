<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Projects", "BREADCRUMB" => false];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['status'])) {
    $DBLIB->where("projectsStatuses.projectsStatuses_id", intval($_GET['status']));
    $DBLIB->where("projectsStatuses.instances_id", $AUTH->data['instance']["instances_id"]);
    $PAGEDATA['STATUS'] = $DBLIB->getOne("projectsStatuses", ["projectsStatuses_id", "projectsStatuses_name"]);
    if ($PAGEDATA['STATUS']) $PAGEDATA['pageConfig']['TITLE'] = "Projects with status " . $PAGEDATA['STATUS']['projectsStatuses_name'];
} else $PAGEDATA['STATUS'] = null;


if (isset($_GET['client'])) {
    $DBLIB->where("clients.clients_deleted", 0);
    $DBLIB->where("clients.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("clients.clients_id", $_GET['client']);
    $PAGEDATA['CLIENT'] = $DBLIB->getone("clients", ["clients_id", "clients_name", "clients_archived"]);
    if ($PAGEDATA['CLIENT']) $PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['CLIENT']['clients_name'] . " Projects";
} else $PAGEDATA['CLIENT'] = false;


if (isset($_GET['location'])) {
    $DBLIB->where("locations_deleted", 0);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations_id", $_GET['location']);
    $PAGEDATA['LOCATION'] = $DBLIB->getone("locations", ["locations_id", "locations_name", "locations_archived"]);
    if ($PAGEDATA['LOCATION']) $PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['LOCATION']['locations_name'] . " Projects";
} else $PAGEDATA['LOCATION'] = false;


if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 30);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_parent_project_id IS NULL");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		projects.projects_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
    )");
}
if ($PAGEDATA['CLIENT']) $DBLIB->where("projects.clients_id", $PAGEDATA['CLIENT']['clients_id']);
if ($PAGEDATA['LOCATION']) $DBLIB->where("projects.locations_id", $PAGEDATA['LOCATION']['locations_id']);
if ($PAGEDATA['STATUS']) $DBLIB->where("projects.projectsStatuses_id", $PAGEDATA['STATUS']['projectsStatuses_id']);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id", "LEFT");
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");

$DBLIB->orderBy("projects.projects_archived", "ASC");
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->orderBy("projects.projects_created", "ASC");
$projectlist = $DBLIB->arraybuilder()->paginate("projects", $page, ["projects_id", "projectsTypes.*","projects_archived", "projects_name", "clients_name", "projects.clients_id", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_manager", "users.users_name1", "users.users_name2", "users.users_email", "users.users_thumbnail", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_description"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];
$PAGEDATA['PROJECTSLIST'] = [];
foreach ($projectlist as $project) {
    $DBLIB->where("projects_id", $project['projects_id']);
    $DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
    $project['finance'] = $DBLIB->getOne("projectsFinanceCache");

    $DBLIB->where("projects_parent_project_id", $project['projects_id']);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
    $DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
    $DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id", "LEFT");
    $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
    $DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
    $DBLIB->orderBy("projects.projects_name", "ASC");
    $DBLIB->orderBy("projects.projects_created", "ASC");
    $subProjects = $DBLIB->get("projects", null, ["projects_id", "projectsTypes.*","projects_archived", "projects_name", "clients_name", "projects.clients_id", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_manager", "users.users_name1", "users.users_name2", "users.users_email", "users.users_thumbnail", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_description"]);
    $project['subProjects'] = [];
    foreach ($subProjects as $subProject) {
        $DBLIB->where("projects_id", $project['projects_id']);
        $DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
        $subProject['finance'] = $DBLIB->getOne("projectsFinanceCache");
        $project['subProjects'][] = $subProject; 
    }
    $PAGEDATA['PROJECTSLIST'][] = $project;
}
echo $TWIG->render('project/project_list.twig', $PAGEDATA);
