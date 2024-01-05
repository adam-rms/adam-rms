<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("projectsNotes_deleted", 0);
$DBLIB->where("projectsNotes_id", $_GET['id']);
$PAGEDATA['note'] = $DBLIB->getone("projectsNotes");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $PAGEDATA['note']['projects_id']);
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*"]);
if (!$PAGEDATA['project']) die($TWIG->render('404.twig', $PAGEDATA));

echo $TWIG->render('project/export-note.twig', $PAGEDATA);
?>
