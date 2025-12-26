<?php
require_once __DIR__ . '/../../apiHeadSecure.php';
require_once __DIR__ . '/../../../common/libs/Maintenance/ScheduledMaintenanceCreator.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:VIEW")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

$creator = new ScheduledMaintenanceCreator();
$result = $creator->processOverdueSchedules($AUTH->data['instance']['instances_id']);

finish(true, null, $result);