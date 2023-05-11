<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:EDIT")) die("404");

foreach ($_POST['order'] as $count=>$item) {
    if ($item == "") continue;
    $DBLIB->where("projectsStatuses_deleted", 0);
    $DBLIB->where("projectsStatuses_id",$item);
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    if (!$DBLIB->update("projectsStatuses", ["projectsStatuses_rank" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-PROJECTSTATUSES", "projectsStatuses", "Set the order of project statuses", $AUTH->data['users_userid']);
finish(true);
