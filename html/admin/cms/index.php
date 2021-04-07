<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
$DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id",$_GET['p']);
$DBLIB->orderBy("cmsPages_navOrder","ASC");
$DBLIB->orderBy("cmsPages_id","ASC");
$PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages");
if (!$PAGEDATA['PAGE']) die($TWIG->render('401.twig', $PAGEDATA));

$DBLIB->where("cmsPages_id",$PAGEDATA['PAGE']['cmsPages_id']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
$PAGEDATA['PAGE']['DRAFTS'] = $DBLIB->getOne("cmsPagesDrafts",["cmsPagesDrafts_id","cmsPagesDrafts_data"]);
if ($PAGEDATA['PAGE']['DRAFTS']) $PAGEDATA['PAGE']['DRAFTS']['cmsPagesDrafts_dataARRAY'] = json_decode($PAGEDATA['PAGE']['DRAFTS']['cmsPagesDrafts_data'],true);

$DBLIB->insert("cmsPagesViews",[
    "cmsPages_id" => $PAGEDATA['PAGE']['cmsPages_id'],
    "cmsPagesViews_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid']
]);

echo $TWIG->render('cms/cms_index.twig', $PAGEDATA);
?>
