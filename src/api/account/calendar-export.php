<?php
require_once __DIR__ . '/../apiHead.php';
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="cal.ics"');
error_reporting(0);
ini_set('display_errors', 0);

$dtz = new \DateTimeZone($CONFIG['TIMEZONE']); 
date_default_timezone_set($CONFIG['TIMEZONE']);

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->where("users.users_userid", $_POST['uid']);
$DBLIB->where("users_calendarHash", $_POST['key']);
$DBLIB->where("users_calendarHash", NULL, 'IS NOT');
$PAGEDATA['user'] = $DBLIB->getone("users", ["users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$PAGEDATA['user']) die("404");

$DBLIB->where("crewAssignments.users_userid", $PAGEDATA['user']['users_userid']);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("locations","projects.locations_id=locations.locations_id","LEFT");
$DBLIB->join("users AS pmusers", "projects.projects_manager=pmusers.users_userid", "LEFT");
$DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
$DBLIB->orderBy("projects.projects_id", "ASC");
$DBLIB->orderBy("crewAssignments.crewAssignments_rank", "ASC");
$assignments = $DBLIB->get("crewAssignments", null, ["pmusers.users_name1 AS pm_name1", "pmusers.users_name2 AS pm_name2","projects.projects_id","crewAssignments.crewAssignments_role", "projects.projects_description", "projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projectsStatuses.projectsStatuses_name", "locations.locations_name","locations.locations_address"]);

$thisProject = null;
$iCalAssignments = [];
foreach ($assignments as $assignment) {
    if ($thisProject != $assignment['projects_id']) $iCalAssignments[$assignment['projects_id']] = $assignment;
    else $iCalAssignments[$assignment['projects_id']]['crewAssignments_role'] .= " & " . $assignment['crewAssignments_role'];
    $thisProject = $assignment['projects_id'];
}

$vCalendar = new \Eluceo\iCal\Component\Calendar($CONFIG['ROOTURL']);
foreach ($iCalAssignments as $event) {
    $vEvent = new \Eluceo\iCal\Component\Event();
    $vEvent->setUseTimezone(true);
    $vEvent
        ->setDtStart(new \DateTime($event['projects_dates_use_start']))
        ->setDtEnd(new \DateTime($event['projects_dates_use_end']))
        ->setNoTime(false)
        ->setSummary($event['projects_name'] . ($event['clients_name'] ? " (" . $event['clients_name'] . ")" : ""))
        ->setCategories(['events', 'AdamRMS'])
        ->setLocation($event['locations_name'] . "\n" . $event['locations_address'], $event['locations_name'] . "\n" . $event['locations_address'])
        ->setDescription('Role: ' . $event['crewAssignments_role'] . "\n" . "Event Status: " . $event['projectsStatuses_name'] . "\n" . "Description: ". $event['projects_description'] . "\n" . "Project Manager: " . $event['pm_name1'] . " " . $event['pm_name2'])
        ->setMsBusyStatus("FREE")
    ;
    $vCalendar->addComponent($vEvent);
}

echo $vCalendar->render();

/** @OA\Get(
 *     path="/account/calendar-export.php", 
 *     summary="Calendar Export", 
 *     description="Get calendar information for integrating with web calendars", 
 *     operationId="getCalendarExport", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="OK",
 *         @OA\MediaType(
 *             mediaType="text/calendar", 
 *             @OA\Schema( 
 *                 type="string", 
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Error",
 *     ), 
 *     @OA\Parameter(
 *         name="uid",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="key",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */