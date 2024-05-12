<?php
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));
$PAGEDATA['USE_TWIG_404'] = true;
require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from

//AuditLog
$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("auditLog.auditLog_actionTable", "projects"); //TODO show more in the log but for now only project stuff
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("auditLog.auditLog_timestamp", "DESC");
$DBLIB->orderBy("auditLog.auditLog_id", "DESC");
$PAGEDATA['project']['auditLog'] = $DBLIB->get("auditLog",null, ["auditLog.*", "users.users_name1", "users.users_name2", "users.users_email"]);

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA["project"]['projects_name'], "BREADCRUMB" => false];

//Edit Options - Client List
if ($AUTH->instancePermissionCheck("PROJECTS:EDIT:CLIENT")) {
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $PAGEDATA['clients'] = $DBLIB->get("clients", null, ["clients_id", "clients_name", "clients_archived"]);
}

//Edit Options - Locations list
if ($AUTH->instancePermissionCheck("PROJECTS:EDIT:ADDRESS")) {
    $thisProjectLocationFound = false;
    $PAGEDATA['locations'] = [];
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->where("locations.locations_archived", 0);
    $DBLIB->where("(locations_subOf IS NULL)");
    $DBLIB->orderBy("locations.locations_name", "ASC");
    $locations = $DBLIB->get('locations',null,["locations.*"]);
    function linkedLocations($locationId, $tier, $locationKey)
    {
        global $DBLIB, $PAGEDATA, $AUTH, $bCMS, $thisProjectLocationFound;
        $DBLIB->where("locations_subOf", $locationId);
        $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->orderBy("locations.locations_name", "ASC");
        $DBLIB->where("locations.locations_archived", 0);
        $DBLIB->where("locations.locations_deleted", 0);
        $locations = $DBLIB->get("locations", null, ["locations.*"]);
        $tier += 1;
        foreach ($locations as $location) {
            $location['tier'] = $tier;
            if ($location['locations_id'] == $PAGEDATA['project']['locations_id']) $thisProjectLocationFound = true;
            $PAGEDATA['locations'][$locationKey]['linkedToThis'][] = $location;
            linkedLocations($location['locations_id'], $tier, $locationKey);
        }
    }
    foreach ($locations as $index => $location) {
        if ($location['locations_id'] == $PAGEDATA['project']['locations_id']) $thisProjectLocationFound = true;
        $PAGEDATA['locations'][] = $location;
        $PAGEDATA['locations'][$index]['linkedToThis'] = [];
        linkedLocations($location['locations_id'], 0, $index);
    }

    if (!$thisProjectLocationFound) {
        // The current location is presumably archived as we can't find it
        $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("locations.locations_id", $PAGEDATA['project']['locations_id']);
        $DBLIB->where("locations.locations_deleted", 0);
        $thisProjectLocation = $DBLIB->getOne('locations');
        if ($thisProjectLocation) $PAGEDATA['locations'][] = $thisProjectLocation;
    }
}

//Crew Recruitment
if ($AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES")) {
    $DBLIB->where("projectsVacantRoles.projectsVacantRoles_deleted",0);
    $DBLIB->where("projectsVacantRoles.projectsVacantRoles_open",1);
    $DBLIB->where("(projectsVacantRoles.projectsVacantRoles_deadline IS NULL OR projectsVacantRoles.projectsVacantRoles_deadline >= '" . date("Y-m-d H:i:s") . "')");
    $DBLIB->where("(projectsVacantRoles.projectsVacantRoles_slots > projectsVacantRoles.projectsVacantRoles_slotsFilled)");
    $DBLIB->where("projectsVacantRoles.projects_id", $PAGEDATA['project']['projects_id']);
    if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(projectsVacantRoles.projectsVacantRoles_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", projectsVacantRoles.projectsVacantRoles_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
    $PAGEDATA['crewRecruitment'] = $DBLIB->get("projectsVacantRoles");
}

/**
 * Variables for the board view (asset dispatch)
 */
$PAGEDATA['BOARDASSETS'] = [];
$PAGEDATA['BOARDSTATUSES'] = []; //A slimmer version of the above for loading into Javascript!
foreach ($PAGEDATA['assetsAssignmentsStatus'] as $status) {
    $tempAssets = [];
    foreach ($PAGEDATA['FINANCIALS']['assetsAssigned'] as $assetType){
        foreach ($assetType['assets'] as $asset){
            if ($asset['assetsAssignmentsStatus_order'] == null && $status['assetsAssignmentsStatus_order'] == 0) { //if asset status is null, add to the first column
                $tempAssets[] = $asset;
            } elseif ($asset['assetsAssignmentsStatus_order'] == $status['assetsAssignmentsStatus_order']) {
                $tempAssets[] = $asset;
            }
        }
    }
    $PAGEDATA['BOARDASSETS'][$AUTH->data['instance']['instances_id']][$status['assetsAssignmentsStatus_order']] = $status;
    $PAGEDATA['BOARDSTATUSES'][$AUTH->data['instance']['instances_id']][$status['assetsAssignmentsStatus_order']] = $status; //For the JS Array
    $PAGEDATA['BOARDASSETS'][$AUTH->data['instance']['instances_id']][$status['assetsAssignmentsStatus_order']]["assets"] = $tempAssets;
}
foreach ($PAGEDATA['FINANCIALS']['assetsAssignedSUB'] as $instance) { //Go through the sub projects
    $DBLIB->orderBy("assetsAssignmentsStatus_order","ASC");
    $DBLIB->where("assetsAssignmentsStatus.instances_id", $instance['instance']['instances_id']);
    $DBLIB->where("assetsAssignmentsStatus.assetsAssignmentsStatus_deleted", 0);
    $PAGEDATA['BOARDASSETS'][$instance['instance']['instances_id']] = $DBLIB->get("assetsAssignmentsStatus");
    $PAGEDATA['BOARDSTATUSES'][$instance['instance']['instances_id']] = $PAGEDATA['BOARDASSETS'][$instance['instance']['instances_id']]; //for the JS Array
    foreach ($PAGEDATA['BOARDASSETS'][$instance['instance']['instances_id']] as $status) {
        $tempAssets=[];
        foreach ($instance["assets"] as $assetType){
            foreach ($assetType['assets'] as $asset){
                if ($asset['assetsAssignmentsStatus_order'] == null && $status['assetsAssignmentsStatus_order'] == 0) { //if asset status is null, add to the first column
                    $tempAssets[] = $asset;
                } elseif ($asset['assetsAssignmentsStatus_order'] == $status['assetsAssignmentsStatus_order']) {
                    $tempAssets[] = $asset;
                }
            }
        }
        $PAGEDATA['BOARDASSETS'][$instance['instance']['instances_id']][$status['assetsAssignmentsStatus_order']]["assets"] = $tempAssets;
    }
}

//Edit Options - Project Statuses list
if ($AUTH->instancePermissionCheck("PROJECTS:EDIT:STATUS")) {
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projectsStatuses_deleted", 0);
    $DBLIB->orderBy("projectsStatuses_rank", "ASC");
    $PAGEDATA['POSSIBLEPROJECTSTATUSES'] = $DBLIB->get("projectsStatuses", null, ["projectsStatuses_id", "projectsStatuses_name", "projectsStatuses_description", "projectsStatuses_assetsReleased"]);
}

//Edit options - Potential project types for changing project type
$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("projectsTypes_name", "ASC");
$PAGEDATA['potentialProjectTypes'] = $DBLIB->get("projectsTypes");

//Edit options - Potential project managers for changing manager
$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
$DBLIB->orderBy("users.users_created", "ASC");
$DBLIB->where("users_deleted", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid", "LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$PAGEDATA['potentialProjectManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);


//Edit Options - can create sub projects?
$PAGEDATA['canCreateSubProjects'] = $bCMS->instanceHasProjectCapacity($AUTH->data['instance']['instances_id']);

if (isset($_GET['list']) and $PAGEDATA['project']['projectsTypes_config_assets'] == 1 and (count($PAGEDATA['FINANCIALS']['assetsAssigned'])>0 or count($PAGEDATA['FINANCIALS']['assetsAssignedSUB'])>0)) echo $TWIG->render('project/project_assetsPage.twig', $PAGEDATA);
else echo $TWIG->render('project/project_index.twig', $PAGEDATA);
?>
