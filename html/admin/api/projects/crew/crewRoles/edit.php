<?php
require_once __DIR__ . '/../../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(123)) die("404");

$array = [];
$array['projectsVacantRoles_visibleToGroups'] = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;

    if ($item['name'] == 'projectsVacantRoles_visibleToGroups') array_push($array['projectsVacantRoles_visibleToGroups'],$item['value']);
    else $array[$item['name']] = $item['value'];
}

if ($array['projectsVacantRoles_visibleToGroups'] == []) $array['projectsVacantRoles_visibleToGroups'] = null;
else $array['projectsVacantRoles_visibleToGroups'] = implode(",",$array['projectsVacantRoles_visibleToGroups']);

$checkboxes = ['projectsVacantRoles_open','projectsVacantRoles_firstComeFirstServed','projectsVacantRoles_fileUploads','projectsVacantRoles_collectPhone','projectsVacantRoles_privateToPM','projectsVacantRoles_showPublic'];
foreach ($checkboxes as $checkbox) {
    if (isset($array[$checkbox]) and $array[$checkbox] == "on") $array[$checkbox] = 1;
    else $array[$checkbox] = 0;
}
if ($array['projectsVacantRoles_deadline']) $array['projectsVacantRoles_deadline'] = date("Y-m-d H:i:s",strtotime($array['projectsVacantRoles_deadline']));

if (strlen($array['projectsVacantRoles_id']) <1 and $array['projectsVacantRoles_id'] != "NEW") finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects_id", $array['projects_id']);
if (!$DBLIB->getone("projects",["projects_id"])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No project for action"]);

if ($array['projectsVacantRoles_id'] == "NEW") {
    $array['projectsVacantRoles_id'] = null;
    $array['projectsVacantRoles_added'] = date("Y-m-d H:i:s");
    $insert = $DBLIB->insert("projectsVacantRoles", $array);
    if (!$insert) finish(false);
    $bCMS->auditLog("INSERT", "projectsVacantRoles", json_encode($array), $AUTH->data['users_userid']);
    finish(true);
} else {
    unset($array['projects_id']); //Don't allow them to change project for a role

    $DBLIB->where("projectsVacantRoles_deleted", 0);
    $DBLIB->where("projectsVacantRoles_id",$array['projectsVacantRoles_id']);
    $projectVacantRole = $DBLIB->getone("projectsVacantRoles", ["projectsVacantRoles_id","projectsVacantRoles_privateToPM"]);
    if (!$projectVacantRole) finish(false);

    if ($projectVacantRole['projectsVacantRoles_privateToPM'] == 1) $array['projectsVacantRoles_privateToPM'] = 1; //Prevent applications being made visible to others once locked to the PM

    $DBLIB->where("projectsVacantRoles_deleted", 0);
    $DBLIB->where("projectsVacantRoles_id",$projectVacantRole['projectsVacantRoles_id']);
    $update = $DBLIB->update("projectsVacantRoles", $array,1);
    if (!$update) finish(false);

    $bCMS->auditLog("UPDATE", "projectsVacantRoles", json_encode($array), $AUTH->data['users_userid']);
    finish(true);
}
