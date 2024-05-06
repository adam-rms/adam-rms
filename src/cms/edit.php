<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT")) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id",$_GET['p']);
$PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages");
if (!$PAGEDATA['PAGE']) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("cmsPages_id",$PAGEDATA['PAGE']['cmsPages_id']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
$DBLIB->join("users","cmsPagesDrafts.users_userid=users.users_userid","LEFT");
$PAGEDATA['PAGE']['DRAFTS'] = $DBLIB->get("cmsPagesDrafts",null,['cmsPagesDrafts.*',"users.users_name1","users.users_name2"]);
if ($PAGEDATA['PAGE']['DRAFTS']) {
    $PAGEDATA['PAGE']['DRAFTS'][0]['cmsPagesDrafts_dataARRAY'] = json_decode($PAGEDATA['PAGE']['DRAFTS'][0]['cmsPagesDrafts_data'],true);
}

if ($AUTH->instancePermissionCheck("BUSINESS:BUSINESS_STATS:VIEW")) $PAGEDATA['WIDGETS'] = new statsWidgets(explode(",",$AUTH->data['users_widgets']),true);

echo $TWIG->render('cms/cms_edit.twig', $PAGEDATA);
?>
