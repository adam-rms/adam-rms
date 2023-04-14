<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Project Types", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_TYPES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("projectsTypes_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		projectsTypes_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' 
    )");
}
$PAGEDATA['types'] = $DBLIB->get("projectsTypes",null,["projectsTypes.*", "(SELECT COUNT(*) FROM projects WHERE projects.projectsTypes_id=projectsTypes.projectsTypes_id AND projects.projects_deleted=0) AS count"]);

$PAGEDATA['options'] = [
    "projectsTypes_config_finance" => "Finance",
    "projectsTypes_config_files" => "Files",
    "projectsTypes_config_assets" => "Assets",
    "projectsTypes_config_client" => "Client Assignment",
    "projectsTypes_config_venue" => "Venue",
    "projectsTypes_config_notes" => "Notes",
    "projectsTypes_config_crew" => "Crew"
];

echo $TWIG->render('instances/projectTypes.twig', $PAGEDATA);
?>
