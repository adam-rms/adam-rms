<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(125)) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id",$_GET['p']);
$PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages");
if (!$PAGEDATA['PAGE']) die($TWIG->render('404.twig', $PAGEDATA));

echo $TWIG->render('cms/cms_stats.twig', $PAGEDATA);
?>
