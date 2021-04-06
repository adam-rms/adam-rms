<?php
require_once 'common/head.php';

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404Public.twig', $PAGEDATA));
$DBLIB->where("instances_id", $PAGEDATA['INSTANCE']['instances_id']);
$DBLIB->where("cmsPages_deleted", 0);
$DBLIB->where("cmsPages_archived", 0);
$DBLIB->where("cmsPages_showPublic",1);
$DBLIB->where("cmsPages_id",$_GET['p']);
$DBLIB->orderBy("cmsPages_navOrder", "ASC");
$DBLIB->orderBy("cmsPages_id", "ASC");
$PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages");
if (!$PAGEDATA['PAGE']) die($TWIG->render('404Public.twig', $PAGEDATA));

echo $TWIG->render('cmsPagePublic.twig', $PAGEDATA);
?>
