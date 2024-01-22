<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS")) die("404");

if (!isset($_POST['instancePositions_id']) or !is_numeric($_POST['instancePositions_id'])) finish(false);

$DBLIB->where("instancePositions_deleted",0);
$DBLIB->where("instancePositions_id",$_POST['instancePositions_id']);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
if (!$DBLIB->update("instancePositions", ["cmsPages_id" => ($_POST['cmsPages_id'] ? $_POST['cmsPages_id'] : null)], 1)) finish(false);

$bCMS->auditLog("CUSTOMDASHBOARD", "cmsPages", "Set the custom dashboard for " . $_POST['instancePositions_id'] . " to " . $_POST['cmsPages_id'], $AUTH->data['users_userid']);
finish(true);
