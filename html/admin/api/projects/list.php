<?php
require_once __DIR__ . '/../apiHeadSecure.php';
$return = [];
foreach ($PAGEDATA['projects'] as $project) {
    $return[] = [
        "projects_id" => $project['projects_id'],
        "projects_name" => $project['projects_name'],
        "clients_name" => $project['clients_name'],
        "projects_manager" => $project['projects_manager'],
        "thisProjectManager" => ($AUTH->data['users_userid'] == $project['projects_manager'])
    ];
}
finish(true, null, $return);