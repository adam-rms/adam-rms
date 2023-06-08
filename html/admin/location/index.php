<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("LOCATIONS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => (isset($_GET['archive']) ? "Archived " : "") . "Locations", "BREADCRUMB" => false];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 60);

$PAGEDATA['allLocations'] = [];
$PAGEDATA['locations'] = [];

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
if (isset($_GET['client'])) $DBLIB->where("locations.clients_id", $_GET['client']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->join("clients", "locations.clients_id=clients.clients_id", "LEFT");
$DBLIB->orderBy("locations_subOf", "ASC");
$DBLIB->orderBy("locations.locations_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		locations.locations_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		locations.locations_address LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
        locations.locations_notes LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
    )");
} elseif (isset($_GET['archive'])) {
    //We need all locations if it's archived
    $DBLIB->where("locations.locations_archived", 1);
    $PAGEDATA['showArchived'] = true;
} else {
    $DBLIB->where("(locations_subOf IS NULL)");
    $DBLIB->where("locations.locations_archived", 0);
    $PAGEDATA['showArchived'] = false;
    
}
$locations = $DBLIB->arraybuilder()->paginate('locations', $page, ["locations.*", "clients.clients_name"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];
function linkedLocations($locationId, $tier, $locationKey)
{
    global $DBLIB, $PAGEDATA, $AUTH, $bCMS;
    $DBLIB->where("locations_subOf", $locationId);
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->orderBy("locations.locations_name", "ASC");
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->where("locations.locations_archived", 0);
    $DBLIB->join("clients", "locations.clients_id=clients.clients_id", "LEFT");
    $locations = $DBLIB->get("locations", null, ["locations.*", "clients.clients_name"]);
    $tier += 1;
    foreach ($locations as $location) {
        $location['files'] = $bCMS->s3List(11, $location['locations_id']);
        $location['tier'] = $tier;
        $PAGEDATA['allLocations'][] = $location;
        $PAGEDATA['locations'][$locationKey]['linkedToThis'][] = $location;
        linkedLocations($location['locations_id'], $tier, $locationKey);
    }
}
foreach ($locations as $index => $location) {
    $location['files'] = $bCMS->s3List(11, $location['locations_id']);
    $PAGEDATA['locations'][] = $location;
    $PAGEDATA['allLocations'][] = $location;
    $PAGEDATA['locations'][$index]['linkedToThis'] = [];
    if (strlen($PAGEDATA['search']) == null) linkedLocations($location['locations_id'], 0, $index); //Don't show linked locations when searching or if listing archived locations
}



$DBLIB->where("clients.clients_deleted", 0);
$DBLIB->where("clients.clients_archived", 0);
$DBLIB->where("clients.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("clients.clients_name", "ASC");
$PAGEDATA['clients'] = $DBLIB->get('clients');

if (isset($_GET['files']) and isset($_GET['id'])) {
    foreach ($PAGEDATA['allLocations'] as $location) {
        if ($location['locations_id'] == $_GET['id']) {
            $PAGEDATA['LOCATION'] = $location;
            $PAGEDATA['pageConfig']["TITLE"] = $location['locations_name'] . " Files";
            echo $TWIG->render('location/location_files.twig', $PAGEDATA);
            exit;
        }
    }
}

echo $TWIG->render('location/location_index.twig', $PAGEDATA);
