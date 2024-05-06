<?php
require_once __DIR__ . '/../apiHead.php';
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="cal.ics"');
error_reporting(0); 
ini_set('display_errors', 0);

$dtz = new \DateTimeZone($CONFIG['TIMEZONE']);
date_default_timezone_set($CONFIG['TIMEZONE']);

// Check instance ID and key
$DBLIB->where("instances.instances_deleted", 0);
$DBLIB->where("instances.instances_id", $_POST['id']);
$DBLIB->where("instances.instances_calendarHash", $_POST['key']);
$DBLIB->where("instances.instances_calendarHash", NULL, 'IS NOT');
$PAGEDATA['instance'] = $DBLIB->getone("instances", ["instances.instances_id"]);
if (!$PAGEDATA['instance']) die("404");

// Get all projects, and their location, client, and project manager
$DBLIB->where("projects.instances_id", $PAGEDATA['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("locations","projects.locations_id=locations.locations_id","LEFT");
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$DBLIB->join("users AS pmusers", "projects.projects_manager=pmusers.users_userid", "LEFT");
$DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$iCalProjects = $DBLIB->get("projects", null, ["pmusers.users_name1 AS pm_name1", "pmusers.users_name2 AS pm_name2","projects.projects_id", "projects.projects_description", "projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projectsStatuses.projectsStatuses_name", "locations.locations_name","locations.locations_address"]);

$vCalendar = new \Eluceo\iCal\Component\Calendar($CONFIG['ROOTURL']);

foreach ($iCalProjects as $event) {
    $vEvent = new \Eluceo\iCal\Component\Event();
    $vEvent->setUseTimezone(true);
    $vEvent
        ->setDtStart(new \DateTime($event['projects_dates_use_start']))
        ->setDtEnd(new \DateTime($event['projects_dates_use_end']))
        ->setNoTime(false)
        ->setSummary($event['projects_name'] . ($event['clients_name'] ? " (" . $event['clients_name'] . ")" : ""))
        ->setCategories(['events', 'AdamRMS'])
        ->setLocation($event['locations_name'] . "\n" . $event['locations_address'], $event['locations_name'] . "\n" . $event['locations_address'])
        ->setDescription("Event Status: " . $event['projectsStatuses_name'] . "\n" . "Description: ". $event['projects_description'] . "\n" . "Project Manager: " . $event['pm_name1'] . " " . $event['pm_name2'])
    ;
    $vCalendar->addComponent($vEvent);
}

echo $vCalendar->render();

/**
 *  @OA\Post(
 *      path="/instances/calendar-export.php",
 *      summary="Export instance Calendar",
 *      description="Get a list of the first 20 available icons",
 *      operationId="instanceCalendar",
 *      tags={"instanceCalendarSettings"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="text/calendar", 
 *             @OA\Schema( 
 *                 type="string", 
 *             ),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="id",
 *          in="query",
 *          description="Id of Instance to get calendar",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="key",
 *          in="query",
 *          description="Identifying Hash for this calendar",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */