<?php
require_once __DIR__ . '/../../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(124)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}

if (strlen($array['projectsVacantRoles_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("projectsVacantRoles_deleted",0);
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->where("projectsVacantRoles_open",1);
$DBLIB->where("projectsVacantRoles_id",$array['projectsVacantRoles_id']);
$role = $DBLIB->getOne("projectsVacantRoles",['projectsVacantRoles_id','projectsVacantRoles.projects_id','projects_manager','projects_name','projectsVacantRoles_name',"users.users_userid", "users.users_name1", "users.users_name2", "users.users_email","projectsVacantRoles_firstComeFirstServed","projectsVacantRoles_slots","projectsVacantRoles_slotsFilled"]);
if (!$role) finish(false, ['message' => "Sorry this role is no longer open"]);

if ($role['projectsVacantRoles_firstComeFirstServed'] == 1 and $role['projectsVacantRoles_slotsFilled'] >= $role['projectsVacantRoles_slots']) finish(false, ["message" => "Sorry this role is fully subscribed"]);

$DBLIB->where("projectsVacantRoles_id",$role['projectsVacantRoles_id']);
$DBLIB->where("users_userid", $AUTH->data['users_userid']);
$DBLIB->where("projectsVacantRolesApplications_deleted",0);
$DBLIB->where("projectsVacantRolesApplications_withdrawn",0);
if ($DBLIB->getOne("projectsVacantRolesApplications",["projectsVacantRolesApplications_id"])) finish(false, ['message' => "Sorry you can't apply twice"]);
$array['users_userid'] = $AUTH->data['users_userid'];
$array['projectsVacantRolesApplications_submitted'] = date("Y-m-d H:i:s");
$insert = $DBLIB->insert("projectsVacantRolesApplications", $array);
if (!$insert) finish(false);

$bCMS->auditLog("INSERT", "projectsVacantRolesApplications", json_encode($array), $AUTH->data['users_userid']);

if ($role['projects_manager'] and $role['projects_manager'] != $AUTH->data['users_userid']) notify(40,$role['projects_manager'], $AUTH->data['instance']['instances_id'], "New application to role on " . $role['projects_name'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " has applied for the role of " . $role['projectsVacantRoles_name'] . " on " . $role['projects_name']);

if ($role['projectsVacantRoles_firstComeFirstServed']) {
    $insert = $DBLIB->insert("crewAssignments", [
        "projects_id" => $role["projects_id"],
        "users_userid" => $AUTH->data['users_userid'],
        "crewAssignments_role" => $role['projectsVacantRoles_name']
    ]);
    if (!$insert) finish(false, ["message"=>"Application received, but there was a problem assigning you to the event - please contact the project manager"]);

    $DBLIB->where("projectsVacantRoles_id",$role['projectsVacantRoles_id']);
    $DBLIB->update("projectsVacantRoles",["projectsVacantRoles_slotsFilled"=>$role['projectsVacantRoles_slotsFilled']+1],1);

    $bCMS->auditLog("ASSIGN-CREW", "crewAssignments", "Self assign via vacancy application", $AUTH->data['users_userid'],null, $role['projects_id']);
    notify(41,$AUTH->data['users_userid'], $AUTH->data['instance']['instances_id'], "Application successful for " . $role['projects_name'], "You have now been assigned the role of " . $role['projectsVacantRoles_name'] . " on " . $role['projects_name'] . ". If you have any queries please contact the project manager " . $role['users_name1'] . " " .  $role['users_name2'] . " (" . $role['users_email'] . ")");
} else notify(41,$AUTH->data['users_userid'], $AUTH->data['instance']['instances_id'], "Application submitted for " . $role['projects_name'], $role['users_name1'] . " " . $role['users_name2'] . " has received your application for the role of " . $role['projectsVacantRoles_name'] . " on " . $role['projects_name'] . ". If you have any queries, you would like to change your application, or you would like to withdraw it please contact the project manager " . $role['users_name1'] . " " .  $role['users_name2'] ." (" . $role['users_email'] . ")");


finish(true);