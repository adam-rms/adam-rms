<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:VIEW:ACCESS_LOG")) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id",$_GET['p']);
$PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages",["cmsPages_id"]);
if (!$PAGEDATA['PAGE']) die($TWIG->render('404.twig', $PAGEDATA));


$DBLIB->orderBy("cmsPagesViews_timestamp","ASC");
$DBLIB->join("users","cmsPagesViews.users_userid=users.users_userid","LEFT");
$DBLIB->where("cmsPages_id",$_GET['p']);
$PAGEDATA['VIEWERS'] = $DBLIB->get("cmsPagesViews",null,["cmsPagesViews.*","users.users_name1","users.users_name2","users.users_userid"]);

echo $TWIG->render('cms/cms_log.twig', $PAGEDATA);
?>
