<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:SCHEDULES:CREATE")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

if (!isset($_POST['assets_id']) || !isset($_POST['type']) || !isset($_POST['nextDue'])) {
    finish(false, ["code" => "MISSING_FIELDS", "message" => "Missing required fields"]);
}

// Verify asset belongs to instance
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->where("assets.assets_id", $_POST['assets_id']);
$asset = $DBLIB->getOne("assets", ["assets_id", "assets_tag"]);
if (!$asset) {
    finish(false, ["code" => "ASSET_NOT_FOUND", "message" => "Asset not found"]);
}

$insertData = [
    'assets_id' => $_POST['assets_id'],
    'instances_id' => $AUTH->data['instance']['instances_id'],
    'assetMaintenanceSchedules_type' => $_POST['type'],
    'assetMaintenanceSchedules_nextDue' => date('Y-m-d H:i:s', strtotime($_POST['nextDue'])),
    'assetMaintenanceSchedules_intervalMonths' => isset($_POST['intervalMonths']) ? (int)$_POST['intervalMonths'] : null,
    'assetMaintenanceSchedules_autoCreateJob' => isset($_POST['autoCreateJob']) ? (int)$_POST['autoCreateJob'] : 1,
    'assetMaintenanceSchedules_blockWhenOverdue' => isset($_POST['blockWhenOverdue']) ? (int)$_POST['blockWhenOverdue'] : 1,
    'assetMaintenanceSchedules_enabled' => 1,
];

$scheduleId = $DBLIB->insert("assetMaintenanceSchedules", $insertData);
if (!$scheduleId) {
    finish(false, ["code" => "INSERT_FAILED", "message" => "Could not create schedule"]);
}

$bCMS->auditLog(
    "CREATE-MAINTENANCE-SCHEDULE",
    "assetMaintenanceSchedules",
    "Created " . $_POST['type'] . " schedule for " . $asset['assets_tag'],
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $scheduleId
);

finish(true, null, ["schedule_id" => $scheduleId]);