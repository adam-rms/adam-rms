<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

// Check for delete permission if deleting
if (isset($_POST['deleted']) && $_POST['deleted'] == 1) {
    if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:SCHEDULES:DELETE")) {
        finish(false, ["code" => "AUTH", "message" => "No permission to delete"]);
    }
} else {
    if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:SCHEDULES:EDIT")) {
        finish(false, ["code" => "AUTH", "message" => "No permission"]);
    }
}

if (!isset($_POST['assetMaintenanceSchedules_id'])) {
    finish(false, ["code" => "MISSING_FIELDS", "message" => "Missing schedule ID"]);
}

// Verify schedule belongs to instance
$DBLIB->where("assetMaintenanceSchedules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_deleted", 0);
$DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_id", $_POST['assetMaintenanceSchedules_id']);
$schedule = $DBLIB->getOne("assetMaintenanceSchedules");
if (!$schedule) {
    finish(false, ["code" => "SCHEDULE_NOT_FOUND", "message" => "Schedule not found"]);
}

$updateData = [];

// Handle deletion
if (isset($_POST['deleted']) && $_POST['deleted'] == 1) {
    $updateData['assetMaintenanceSchedules_deleted'] = 1;
}

// Update next due date
if (isset($_POST['nextDue'])) {
    $updateData['assetMaintenanceSchedules_nextDue'] = date('Y-m-d H:i:s', strtotime($_POST['nextDue']));
}

// Update interval
if (isset($_POST['intervalMonths'])) {
    $updateData['assetMaintenanceSchedules_intervalMonths'] = $_POST['intervalMonths'] ? (int)$_POST['intervalMonths'] : null;
}

// Update enabled status
if (isset($_POST['enabled'])) {
    $updateData['assetMaintenanceSchedules_enabled'] = (int)$_POST['enabled'];
}

// Update block when overdue
if (isset($_POST['blockWhenOverdue'])) {
    $updateData['assetMaintenanceSchedules_blockWhenOverdue'] = (int)$_POST['blockWhenOverdue'];
}

// Update auto-create job
if (isset($_POST['autoCreateJob'])) {
    $updateData['assetMaintenanceSchedules_autoCreateJob'] = (int)$_POST['autoCreateJob'];
}

// Update type
if (isset($_POST['type'])) {
    $updateData['assetMaintenanceSchedules_type'] = $_POST['type'];
}

if (empty($updateData)) {
    finish(false, ["code" => "NO_CHANGES", "message" => "No fields to update"]);
}

$DBLIB->where('assetMaintenanceSchedules_id', $_POST['assetMaintenanceSchedules_id']);
$result = $DBLIB->update('assetMaintenanceSchedules', $updateData);

if (!$result) {
    finish(false, ["code" => "UPDATE_FAILED", "message" => "Could not update schedule"]);
}

$action = isset($_POST['deleted']) && $_POST['deleted'] == 1 ? "DELETE-MAINTENANCE-SCHEDULE" : "UPDATE-MAINTENANCE-SCHEDULE";
$description = isset($_POST['deleted']) && $_POST['deleted'] == 1 ? "Deleted schedule" : "Updated schedule";
if (isset($_POST['nextDue'])) {
    $description .= " - Next due: " . $_POST['nextDue'];
}

$bCMS->auditLog(
    $action,
    "assetMaintenanceSchedules",
    $description,
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['assetMaintenanceSchedules_id']
);

finish(true);