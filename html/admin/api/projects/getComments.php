<?php
/**
 * API
 * \projects\getComments.php
 * get comment data for given project
 *
 * Arguments:
 *  - projects_id: a project
 */

require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(20) or !isset($_POST['projects_id'])) finish(false);

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects",["projects_id"]);
if (!$project) finish(false);

$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.projects_id", $project['projects_id']);
$DBLIB->where("auditLog.auditLog_actionType", "QUICKCOMMENT");
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("auditLog.auditLog_timestamp", "DESC");
$DBLIB->orderBy("auditLog.auditLog_id", "DESC");
$auditLog = $DBLIB->get("auditLog",null, ["auditLog.*", "users.users_name1", "users.users_name2", "users.users_email"]);

finish(true, null, $auditLog);