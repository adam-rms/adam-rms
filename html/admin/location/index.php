<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(87)) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Locations", "BREADCRUMB" => false];

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 60);

$PAGEDATA['allLocations'] = [];

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
if (isset($_GET['client'])) $DBLIB->where("locations.clients_id", $_GET['client']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->join("clients","locations.clients_id=clients.clients_id","LEFT");
$DBLIB->where("(locations_subOf IS NULL)");
$DBLIB->orderBy("locations.locations_name", "ASC");
$locations = $DBLIB->arraybuilder()->paginate('locations', $page, ["locations.*", "clients.clients_name"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];
function linkedLocations($locationId,$tier,$locationKey) {
    global $DBLIB,$PAGEDATA,$AUTH,$bCMS;
    $DBLIB->where("locations_subOf", $locationId);
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->orderBy("locations.locations_name", "ASC");
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->join("clients","locations.clients_id=clients.clients_id","LEFT");
    $locations = $DBLIB->get("locations",null,["locations.*", "clients.clients_name"]);
    $tier+=1;
    foreach ($locations as $location) {
        $location['files'] = $bCMS->s3List(11, $location['locations_id']);
        $location['tier'] = $tier;
        $PAGEDATA['allLocations'][] = $location;
        $PAGEDATA['locations'][$locationKey]['linkedToThis'][] = $location;
        linkedLocations($location['locations_id'],$tier,$locationKey);
    }
}
foreach ($locations as $index=>$location) {
    $location['files'] = $bCMS->s3List(11, $location['locations_id']);
    $PAGEDATA['locations'][] = $location;
    $PAGEDATA['allLocations'][] = $location;
    $PAGEDATA['locations'][$index]['linkedToThis'] = [];
    linkedLocations($location['locations_id'],0,$index);
}



$DBLIB->where("clients.clients_deleted", 0);
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
?>
