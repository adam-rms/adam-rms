<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:STATUS")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

if (!isset($_POST['maintenanceJobs_id']) || !isset($_POST['nextDue'])) {
    finish(false, ["code" => "MISSING_FIELDS", "message" => "Missing required fields"]);
}

// Get the maintenance job
$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs.maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs");
if (!$job) {
    finish(false, ["code" => "JOB_NOT_FOUND", "message" => "Job not found"]);
}

// Check if this job is linked to a schedule
if (!$job['assetMaintenanceSchedules_id']) {
    finish(false, ["code" => "NOT_SCHEDULED", "message" => "This job is not linked to a schedule"]);
}

// Update the schedule with new due date
$updateData = [
    'assetMaintenanceSchedules_nextDue' => date('Y-m-d H:i:s', strtotime($_POST['nextDue'])),
];

// Optionally update interval if provided
if (isset($_POST['intervalMonths'])) {
    $updateData['assetMaintenanceSchedules_intervalMonths'] = $_POST['intervalMonths'] ? (int)$_POST['intervalMonths'] : null;
}

$DBLIB->where('assetMaintenanceSchedules_id', $job['assetMaintenanceSchedules_id']);
$scheduleUpdated = $DBLIB->update('assetMaintenanceSchedules', $updateData);

// Get the completed status (first one that's marked as completed)
$DBLIB->where("maintenanceJobsStatuses_deleted", 0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->orderBy("maintenanceJobsStatuses_order", "ASC");
$statuses = $DBLIB->get("maintenanceJobsStatuses");
$completedStatus = null;
foreach ($statuses as $status) {
    if (stripos($status['maintenanceJobsStatuses_name'], 'complete') !== false) {
        $completedStatus = $status['maintenanceJobsStatuses_id'];
        break;
    }
}

// Update the job status to completed
$DBLIB->where('maintenanceJobs_id', $_POST['maintenanceJobs_id']);
$jobUpdateData = [
    'maintenanceJobs_blockAssets' => 0, // Unblock the asset
];
if ($completedStatus) {
    $jobUpdateData['maintenanceJobsStatuses_id'] = $completedStatus;
}
$jobUpdated = $DBLIB->update('maintenanceJobs', $jobUpdateData);

if (!$scheduleUpdated || !$jobUpdated) {
    finish(false, ["code" => "UPDATE_FAILED", "message" => "Could not complete maintenance"]);
}

$bCMS->auditLog(
    "COMPLETE-SCHEDULED-MAINTENANCE",
    "maintenanceJobs",
    "Completed and set next due to " . $_POST['nextDue'],
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['maintenanceJobs_id']
);

finish(true, null, ["nextDue" => $_POST['nextDue']]);