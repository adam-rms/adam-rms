<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Maintenance Jobs", "BREADCRUMB" => false];

$PAGEDATA['showCompleted'] = isset($_GET['completed']);

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 60);
$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
if (!$PAGEDATA['showCompleted']) $DBLIB->where("(maintenanceJobsStatuses.maintenanceJobsStatuses_showJobInMainList = 1 OR maintenanceJobs.maintenanceJobsStatuses_id IS NULL)");
else $DBLIB->where("maintenanceJobsStatuses.maintenanceJobsStatuses_showJobInMainList",0);
$DBLIB->join("users AS userCreator", "userCreator.users_userid=maintenanceJobs.maintenanceJobs_user_creator", "LEFT");
$DBLIB->join("users AS userAssigned", "userAssigned.users_userid=maintenanceJobs.maintenanceJobs_user_assignedTo", "LEFT");
$DBLIB->orderBy("maintenanceJobsStatuses.maintenanceJobsStatuses_order", "ASC");
$DBLIB->orderBy("maintenanceJobs.maintenanceJobs_priority", "ASC");
$DBLIB->orderBy("maintenanceJobs.maintenanceJobs_timestamp_due", "ASC");
$DBLIB->orderBy("maintenanceJobs.maintenanceJobs_timestamp_added", "ASC");
$PAGEDATA['jobs'] = $DBLIB->arraybuilder()->paginate('maintenanceJobs', $page, ["maintenanceJobs.*", "maintenanceJobsStatuses.maintenanceJobsStatuses_name","userCreator.users_userid AS userCreatorUserID", "userCreator.users_name1 AS userCreatorUserName1", "userCreator.users_name2 AS userCreatorUserName2", "userCreator.users_email AS userCreatorUserEMail","userCreator.users_thumbnail AS userCreatorUserThumb","userAssigned.users_name1 AS userAssignedUserName1","userAssigned.users_userid AS userAssignedUserID", "userAssigned.users_name2 AS userAssignedUserName2", "userAssigned.users_email AS userAssignedUserEMail", "userAssigned.users_thumbnail AS userAssignedUserThumb"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];



echo $TWIG->render('maintenance/maintenance_index.twig', $PAGEDATA);
?>
