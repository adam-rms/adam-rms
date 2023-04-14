<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT")) die("404");

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

//Get old revision data
$DBLIB->where("cmsPages_id",$_POST['cmsPages_id']);
$DBLIB->where("cmsPagesDrafts_id",$_POST['change']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
$restoreRevision = $DBLIB->getOne("cmsPagesDrafts",["cmsPagesDrafts_revisionID","cmsPagesDrafts_data"]);
if (!$restoreRevision) finish(false);

$insert = $DBLIB->insert("cmsPagesDrafts",[
    "cmsPages_id" => $_POST['cmsPages_id'],
    "cmsPagesDrafts_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "cmsPagesDrafts_data" => $restoreRevision["cmsPagesDrafts_data"],
    "cmsPagesDrafts_changelog" => "Rollback to revision v" . $restoreRevision['cmsPagesDrafts_revisionID'],
    "cmsPagesDrafts_revisionID" => $revision
]);
if ($insert) finish(true);
else finish(false);