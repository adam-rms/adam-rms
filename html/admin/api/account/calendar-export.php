<?php
require_once __DIR__ . '/../apiHead.php';
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="cal.ics"');
error_reporting(0);
ini_set('display_errors', 0);

$tz  = 'Europe/London';
$dtz = new \DateTimeZone($tz);
date_default_timezone_set($tz);

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->where("users.users_userid", $_POST['uid']);
$DBLIB->where("users_calendarHash", $_POST['key']);
$PAGEDATA['user'] = $DBLIB->getone("users", ["users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$PAGEDATA['user']) die("404");

$DBLIB->where("crewAssignments.users_userid", $PAGEDATA['user']['users_userid']);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users AS pmusers", "projects.projects_manager=pmusers.users_userid", "LEFT");
$DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
$DBLIB->orderBy("projects.projects_dates_use_start", "ASC");
$DBLIB->orderBy("projects.projects_dates_use_end", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$PAGEDATA['user']['crewAssignments'] = $DBLIB->get("crewAssignments", null, ["pmusers.users_name1 AS pm_name1", "pmusers.users_name2 AS pm_name2","crewAssignments.crewAssignments_role", "projects.projects_description", "projects.projects_dates_use_start", "projects.projects_dates_use_end", "projects.projects_name", "clients.clients_name", "projects.projects_status", "projects.projects_address"]);

$vCalendar = new \Eluceo\iCal\Component\Calendar($CONFIG['ROOTURL']);
foreach ($PAGEDATA['user']['crewAssignments'] as $event) {
    $vEvent = new \Eluceo\iCal\Component\Event();
    $vEvent->setUseTimezone(true);
    $vEvent
        ->setDtStart(new \DateTime($event['projects_dates_use_start']))
        ->setDtEnd(new \DateTime($event['projects_dates_use_end']))
        ->setNoTime(false)
        ->setSummary($event['projects_name'] . ($event['clients_name'] ? " (" . $event['clients_name'] . ")" : ""))
        ->setCategories(['events', 'AdamRMS'])
        ->setLocation($event['projects_address'], $event['projects_address'])
        ->setDescription('Role: ' . $event['crewAssignments_role'] . "\n" . "Event Status: " . $STATUSES[$event['projects_status']]['name'] . "\n" . "Description: ". $event['projects_description'] . "\n" . "Project Manager: " . $event['pm_name1'] . " " . $event['pm_name2'])
        ->setMsBusyStatus("FREE")
    ;
    $vCalendar->addComponent($vEvent);
}

echo $vCalendar->render();