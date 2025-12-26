<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:STATUS")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

if (!isset($_POST['assetMaintenanceSchedules_id']) || !isset($_POST['nextDue'])) {
    finish(false, ["code" => "MISSING_FIELDS", "message" => "Missing required fields"]);
}

// Get the schedule
$DBLIB->where("assetMaintenanceSchedules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_deleted", 0);
$DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_id", $_POST['assetMaintenanceSchedules_id']);
$schedule = $DBLIB->getOne("assetMaintenanceSchedules");
if (!$schedule) {
    finish(false, ["code" => "SCHEDULE_NOT_FOUND", "message" => "Schedule not found"]);
}

// Update the schedule with new due date
$updateData = [
    'assetMaintenanceSchedules_nextDue' => date('Y-m-d H:i:s', strtotime($_POST['nextDue'])),
];

// Optionally update interval if provided
if (isset($_POST['intervalMonths']) && $_POST['intervalMonths'] !== '') {
    $updateData['assetMaintenanceSchedules_intervalMonths'] = (int)$_POST['intervalMonths'];
}

$DBLIB->where('assetMaintenanceSchedules_id', $_POST['assetMaintenanceSchedules_id']);
$scheduleUpdated = $DBLIB->update('assetMaintenanceSchedules', $updateData);

if (!$scheduleUpdated) {
    finish(false, ["code" => "UPDATE_FAILED", "message" => "Could not update schedule"]);
}

// Find any maintenance jobs associated with this schedule that are still blocking
$DBLIB->where("maintenanceJobs.assetMaintenanceSchedules_id", $_POST['assetMaintenanceSchedules_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs.maintenanceJobs_blockAssets", 1);
$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$jobs = $DBLIB->get("maintenanceJobs");

// Mark any associated jobs as completed and unblock the asset
if ($jobs) {
    // Get the completed status
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

    foreach ($jobs as $job) {
        $jobUpdateData = [
            'maintenanceJobs_blockAssets' => 0, // Unblock the asset
        ];
        if ($completedStatus) {
            $jobUpdateData['maintenanceJobsStatuses_id'] = $completedStatus;
        }

        $DBLIB->where('maintenanceJobs_id', $job['maintenanceJobs_id']);
        $DBLIB->update('maintenanceJobs', $jobUpdateData);

        $bCMS->auditLog(
            "COMPLETE-SCHEDULED-MAINTENANCE",
            "maintenanceJobs",
            "Completed via schedule and set next due to " . $_POST['nextDue'],
            $AUTH->data['users_userid'],
            null,
            $AUTH->data['instance']['instances_id'],
            $job['maintenanceJobs_id']
        );
    }
}

$bCMS->auditLog(
    "UPDATE-SCHEDULED-MAINTENANCE",
    "assetMaintenanceSchedules",
    "Updated next due to " . $_POST['nextDue'],
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['assetMaintenanceSchedules_id']
);

finish(true, null, ["nextDue" => $_POST['nextDue'], "jobsUpdated" => count($jobs ?? [])]);