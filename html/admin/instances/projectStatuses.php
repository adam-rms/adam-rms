<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Project Statuses", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));


$DBLIB->orderBy("projectsStatuses.projectsStatuses_deleted", "ASC");
$DBLIB->orderBy("projectsStatuses.projectsStatuses_rank", "ASC");
$DBLIB->where("projectsStatuses.instances_id", $AUTH->data['instance']["instances_id"]);
$projectStatuses = $DBLIB->get("projectsStatuses");
$PAGEDATA['projectStatuses'] = [];

foreach ($projectStatuses as $projectStatus) {
  $DBLIB->where("projects.projectsStatuses_id", $projectStatus['projectsStatuses_id']);
  $DBLIB->where("projects.projects_deleted", 0);
  $DBLIB->where("projects.instances_id", $AUTH->data['instance']["instances_id"]);  // Should be superfluous
  $projectStatus['project_count'] = $DBLIB->getValue("projects", "count(*)");

  if ($projectStatus['projectsStatuses_deleted'] !== 0) {
    $projectStatus['projectsStatuses_deleted'] = true;
    if ($projectStatus['project_count'] == 0) continue; // Don't add to array if not used by any projects 
  } else $projectStatus['projectsStatuses_deleted'] = false;
  $PAGEDATA['projectStatuses'][] = $projectStatus;
}

echo $TWIG->render('instances/projectStatuses.twig', $PAGEDATA);
?>
