<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT")) die("Sorry - you can't access this page");

foreach ($_POST['order'] as $count=>$item) {
    if ($item == "") continue;
    $DBLIB->where("assetsAssignmentsStatus_id", $item);
    $DBLIB->where("assetsAssignmentsStatus_deleted", 0);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    if (!$DBLIB->update("assetsAssignmentsStatus", ["assetsAssignmentsStatus_order" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-ASSETSTATUS", "assetStatuses", "Set the order of statuses", $AUTH->data['users_userid']);
finish(true);