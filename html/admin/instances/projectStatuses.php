<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Project Statuses", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->orderBy("projectsstatuses.projectsStatuses_rank", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
      projectsstatuses.projectsStatuses_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' 
    )");
}
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->where("projectsstatuses.projectsStatuses_deleted",0);
$PAGEDATA['projectStatuses'] = $DBLIB->get("projectsstatuses");

echo $TWIG->render('instances/projectStatuses.twig', $PAGEDATA);
?>
