<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Calendar", "BREADCRUMB" => false];

if ($AUTH->data['instance']['instances_calendarHash'] == null) {
   $characters = 'abcdefghijklmnopqrstuvwxyz';
   $charactersLength = strlen($characters);
   $randomString = '';
   for ($i = 0; $i < 50; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
   }
   $DBLIB->where("instances.instances_id", $AUTH->data['instance']['instances_id']);
   $DBLIB->update("instances", ['instances_calendarHash' => $randomString]);
   //Generate a calendar hash
   $AUTH->data['instance']['instances_calendarHash'] = $randomString;
   $PAGEDATA['USERDATA']['instance']['instances_calendarHash'] = $randomString; //Update it here so twig uses the new value
}

if (isset($_GET['location'])) {
    $DBLIB->where("locations_deleted", 0);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations_id", $_GET['location']);
    $PAGEDATA['LOCATION'] = $DBLIB->getone("locations", ["locations_id", "locations_name"]);
    if ($PAGEDATA['LOCATION']) $PAGEDATA['pageConfig']['TITLE'] = $PAGEDATA['LOCATION']['locations_name'] . " Project Calendar";
} else $PAGEDATA['LOCATION'] = false;

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
if ($PAGEDATA['LOCATION']) $DBLIB->where("projects.locations_id", $PAGEDATA['LOCATION']['locations_id']);
else $DBLIB->where("projects.projects_archived", 0);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id", "LEFT");
$PAGEDATA['calendarProjects'] = $DBLIB->get("projects", null, ["projects_id", "projectsTypes.*","projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_manager", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour","projectsStatuses.projectsStatuses_backgroundColour"]);

echo $TWIG->render('instances/business_calendar.twig', $PAGEDATA);
?>
