<?php
require_once __DIR__ . '/../apiHeadSecure.php';
$return = [];

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->orderBy("projects.projects_created", "ASC");
$projects = $DBLIB->get("projects", null, ["projects_id", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_status", "projects_manager"]);
foreach ($projects as $project) {
    $return[] = [
        "projects_id" => $project['projects_id'],
        "projects_name" => $project['projects_name'],
        "clients_name" => $project['clients_name'],
        "projects_manager" => $project['projects_manager'],
        "thisProjectManager" => ($AUTH->data['users_userid'] == $project['projects_manager'])
    ];
}
finish(true, null, $return);