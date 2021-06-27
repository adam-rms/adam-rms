<?php
ini_set('memory_limit','2048M');
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));

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
if ($AUTH->instancePermissionCheck(22)) {
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $PAGEDATA['clients'] = $DBLIB->get("clients", null, ["clients_id", "clients_name"]);
}

//Edit Options - Locations list
if ($AUTH->instancePermissionCheck(30)) {
    $PAGEDATA['allLocations'] = [];
    $PAGEDATA['locations'] = [];
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->where("(locations_subOf IS NULL)");
    $DBLIB->orderBy("locations.locations_name", "ASC");
    $locations = $DBLIB->get('locations',null,["locations.*"]);
    function linkedLocations($locationId, $tier, $locationKey)
    {
        global $DBLIB, $PAGEDATA, $AUTH, $bCMS;
        $DBLIB->where("locations_subOf", $locationId);
        $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->orderBy("locations.locations_name", "ASC");
        $DBLIB->where("locations.locations_deleted", 0);
        $locations = $DBLIB->get("locations", null, ["locations.*"]);
        $tier += 1;
        foreach ($locations as $location) {
            $location['tier'] = $tier;
            $PAGEDATA['allLocations'][] = $location;
            $PAGEDATA['locations'][$locationKey]['linkedToThis'][] = $location;
            linkedLocations($location['locations_id'], $tier, $locationKey);
        }
    }
    foreach ($locations as $index => $location) {
        $PAGEDATA['locations'][] = $location;
        $PAGEDATA['allLocations'][] = $location;
        $PAGEDATA['locations'][$index]['linkedToThis'] = [];
        linkedLocations($location['locations_id'], 0, $index);
    }
}

//die(var_dump($PAGEDATA['assetsAssignmentsStatus']));

if (isset($_GET['list']) and $PAGEDATA['project']['projectsTypes_config_assets'] == 1 and (count($PAGEDATA['FINANCIALS']['assetsAssigned'])>0 or count($PAGEDATA['FINANCIALS']['assetsAssignedSUB'])>0)) echo $TWIG->render('project/project_assetsPage.twig', $PAGEDATA);
elseif (isset($_GET['pdf'])) {
    $PAGEDATA['GET'] = $_GET;
    die($TWIG->render('project/pdf.twig', $PAGEDATA));
}
else echo $TWIG->render('project/project_index.twig', $PAGEDATA);
?>
