<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(126)) die("404");

if (!isset($_POST['cmsPages_id'])) finish(false);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
$DBLIB->where("cmsPages_id",$_POST['cmsPages_id']);
$page = $DBLIB->getOne("cmsPages",["cmsPages_id"]);
if (!$page) finish(false);

//Get new revision number
$DBLIB->where("cmsPages_id",$_POST['cmsPages_id']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
$lastUpdate = $DBLIB->getOne("cmsPagesDrafts",["cmsPagesDrafts_revisionID"]);
if ($lastUpdate) $revision = $lastUpdate['cmsPagesDrafts_revisionID']+1;
else $revision = 1;

//Sanitize data
$pageData = $_POST['pageData'];
foreach($pageData['cards'] as $i => $card) {
    $pageData['cards'][$i]['content'] = $bCMS->cleanString($card['content']);
}

$insert = $DBLIB->insert("cmsPagesDrafts",[
    "cmsPages_id" => $_POST['cmsPages_id'],
    "cmsPagesDrafts_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "cmsPagesDrafts_data" => json_encode($pageData),
    "cmsPagesDrafts_changelog" => $_POST['changelog'],
    "cmsPagesDrafts_revisionID" => $revision
]);
if ($insert) finish(true,null,["revision"=>$revision,"revisionID"=>$insert]);
else finish(false);