<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:SCHEDULES:VIEW")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

if (!isset($_POST['assets_id'])) {
    finish(false, ["code" => "MISSING_FIELDS", "message" => "Missing required fields"]);
}

$DBLIB->where("assetMaintenanceSchedules.assets_id", $_POST['assets_id']);
$DBLIB->where("assetMaintenanceSchedules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_deleted", 0);
$DBLIB->orderBy("assetMaintenanceSchedules.assetMaintenanceSchedules_nextDue", "ASC");

$schedules = $DBLIB->get('assetMaintenanceSchedules');

// Add status for each schedule
foreach ($schedules as &$schedule) {
    $dueDate = strtotime($schedule['assetMaintenanceSchedules_nextDue']);
    $now = time();

    if ($dueDate < $now) {
        $schedule['status'] = 'overdue';
        $schedule['daysOverdue'] = floor(($now - $dueDate) / 86400);
    } elseif ($dueDate < ($now + (7 * 86400))) {
        $schedule['status'] = 'due_soon';
        $schedule['daysUntilDue'] = floor(($dueDate - $now) / 86400);
    } else {
        $schedule['status'] = 'upcoming';
        $schedule['daysUntilDue'] = floor(($dueDate - $now) / 86400);
    }
}

finish(true, null, ["schedules" => $schedules]);