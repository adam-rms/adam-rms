<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));
elseif (!isset($_GET['id'])) {
    $PAGEDATA['pageConfig'] = ["TITLE" => "New Maintenance Job", "BREADCRUMB" => false];
    die($TWIG->render('maintenance/maintenance_newJob.twig', $PAGEDATA));
}

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs.maintenanceJobs_id", $_GET['id']);
$DBLIB->join("users AS userCreator", "userCreator.users_userid=maintenanceJobs.maintenanceJobs_user_creator", "LEFT");
$DBLIB->join("users AS userAssigned", "userAssigned.users_userid=maintenanceJobs.maintenanceJobs_user_assignedTo", "LEFT");
$PAGEDATA['job'] = $DBLIB->getone("maintenanceJobs", ["maintenanceJobs.*", "userCreator.users_userid AS userCreatorUserID", "userCreator.users_name1 AS userCreatorUserName1", "userCreator.users_name2 AS userCreatorUserName2", "userCreator.users_email AS userCreatorUserEMail","userAssigned.users_name1 AS userAssignedUserName1","userAssigned.users_userid AS userAssignedUserID", "userAssigned.users_name2 AS userAssignedUserName2", "userAssigned.users_email AS userAssignedUserEMail"]);
if (!$PAGEDATA['job']) die($TWIG->render('404.twig', $PAGEDATA));

// Statuses
$DBLIB->where("maintenanceJobsStatuses_deleted", 0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->orderBy("maintenanceJobsStatuses_order", "ASC");
$DBLIB->orderBy("maintenanceJobsStatuses_name", "ASC");
$PAGEDATA['jobStatuses'] = $DBLIB->get("maintenanceJobsStatuses", null, ["maintenanceJobsStatuses.maintenanceJobsStatuses_id", "maintenanceJobsStatuses.maintenanceJobsStatuses_name"]);
$PAGEDATA['job']['status'] = [];
foreach ($PAGEDATA['jobStatuses'] as $status) {
    if ($status['maintenanceJobsStatuses_id'] == $PAGEDATA['job']['maintenanceJobsStatuses_id']) {
        $PAGEDATA['job']['status'] = $status;
        break;
    }
}


//Potentially assigned to job
if ($AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:USER_ASSIGNED_TO_JOB")) {
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
    $PAGEDATA['potentialManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
}

// Users tagged
$PAGEDATA['job']['tagged'] = [];
if ($PAGEDATA['job']['maintenanceJobs_user_tagged'] != "") {
    $DBLIB->where("(users.users_userid IN (" . $PAGEDATA['job']['maintenanceJobs_user_tagged'] . "))");
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
    $PAGEDATA['job']['tagged'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
}


//AuditLog
$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.auditLog_actionTable", "maintenanceJobs");
$DBLIB->where("auditLog.auditLog_targetID", $PAGEDATA['job']['maintenanceJobs_id']);
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("auditLog.auditLog_timestamp", "DESC");
$DBLIB->orderBy("auditLog.auditLog_id", "DESC");
$PAGEDATA['job']['auditLog'] = $DBLIB->get("auditLog",null, ["auditLog.*", "users.users_name1", "users.users_name2", "users.users_email"]);

// Assets
if ($PAGEDATA['job']['maintenanceJobs_assets'] != null) $PAGEDATA['job']['maintenanceJobs_assets'] = explode(",", $PAGEDATA['job']['maintenanceJobs_assets']);
else $PAGEDATA['job']['maintenanceJobs_assets'] = [];
$PAGEDATA['job']['assets'] = [];
if (count($PAGEDATA['job']['maintenanceJobs_assets']) > 0) {
    $DBLIB->where("(assets_id IN (" . implode(",", $PAGEDATA['job']['maintenanceJobs_assets']) . "))");
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
    $DBLIB->orderBy("assetTypes.assetTypes_id", "ASC");
    $DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $DBLIB->where("assets.assets_deleted", 0);
    $assets = $DBLIB->get("assets", null, ["assetTypes.assetTypes_id", "assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name", "assetCategories.assetCategories_fontAwesome"]);
    $PAGEDATA['job']['assets'] = $assets;
}

//Messages
$DBLIB->where("maintenanceJobsMessages.maintenanceJobsMessages_deleted", 0);
$DBLIB->where("maintenanceJobsMessages.maintenanceJobs_id", $PAGEDATA['job']['maintenanceJobs_id']);
$DBLIB->join("users", "maintenanceJobsMessages.users_userid=users.users_userid", "LEFT");
$DBLIB->join("s3files", "s3files.s3files_id=maintenanceJobsMessages.maintenanceJobsMessages_file", "LEFT");
$DBLIB->orderBy("maintenanceJobsMessages.maintenanceJobsMessages_timestamp", "ASC");
$DBLIB->orderBy("maintenanceJobsMessages.maintenanceJobsMessages_id", "ASC");
$PAGEDATA['job']['messages'] = $DBLIB->get("maintenanceJobsMessages",null, ["s3files.s3files_extension", "s3files.s3files_name", "maintenanceJobsMessages.maintenanceJobsMessages_text", "maintenanceJobsMessages.maintenanceJobsMessages_file", "maintenanceJobsMessages.maintenanceJobsMessages_timestamp","users.users_name1", "users.users_name2", "users.users_email", "users.users_userid","users.users_thumbnail"]);


$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['job']['maintenanceJobs_title'], "BREADCRUMB" => false];

echo $TWIG->render('maintenance/job.twig', $PAGEDATA);
?>
