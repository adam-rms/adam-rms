<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Training", "BREADCRUMB" => false];

// List of positions to restrict modules to
$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions", null, ["instancePositions_id", "instancePositions_displayName"]);

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 20);
$DBLIB->where("modules.modules_deleted", 0);
if ($AUTH->instancePermissionCheck("TRAINING:VIEW:DRAFT_MODULES") and isset($_GET['drafts'])) {
    $PAGEDATA['drafts'] = true;
    $DBLIB->where("modules.modules_show", 0);
}
else $DBLIB->where("modules.modules_show", 1);
if ($AUTH->data['instance']["instancePositions_id"] && !$AUTH->instancePermissionCheck("TRAINING:EDIT") && !$AUTH->instancePermissionCheck("TRAINING:VIEW:USER_PROGRESS_IN_MODULES")) {
    //If the user doesn't have a position - they're server admins
    //If user has permission to edit modules or view users , let them see all of them, otherwise they'll be impossible to edit
    $DBLIB->where("(modules.modules_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", modules.modules_visibleToGroups) > 0))"); 
}
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("modules.modules_name","ASC");
$modules = $DBLIB->arraybuilder()->paginate('modules', $page, ["modules.*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

foreach ($modules as $module) {
    $DBLIB->where("modules_id",$module['modules_id']);
    $DBLIB->where("userModules.users_userid",$AUTH->data['users_userid']);
    $DBLIB->orderBy("userModules_id","ASC");
    $userModules = $DBLIB->get("userModules",null,["userModules.*"]);
    $PAGEDATA['userModules'] = [];

    //Get their progress
    $module['allStepsCompleted'] = [];
    $module['dates'] = ["start" => false,"updated" => false];
    foreach ($userModules as $thismodule) {
        if (!$module['dates']['start'] or (strtotime($module['dates']['start']) > strtotime($thismodule['userModules_started']))) $module['dates']['start'] = strtotime($thismodule['userModules_started']);
        if (!$module['dates']['updated'] or (strtotime($module['dates']['updated']) < strtotime($thismodule['userModules_updated']))) $module['dates']['updated'] = strtotime($thismodule['userModules_updated']);
        $thismodule['userModules_stepsCompleted'] = explode(",",$thismodule['userModules_stepsCompleted']);
        foreach ($thismodule['userModules_stepsCompleted'] as $step) {
            array_push($module['allStepsCompleted'],$step);
        }
        $PAGEDATA['userModules'] = $thismodule;
    }

    //Get their certifications
    $DBLIB->where("modules_id",$module['modules_id']);
    $DBLIB->where("userModulesCertifications.users_userid",$AUTH->data['users_userid']);
    $DBLIB->where("userModulesCertifications_revoked",0);

    $DBLIB->orderBy("userModulesCertifications_timestamp","DESC");
    $DBLIB->join("users","userModulesCertifications_approvedBy=users.users_userid","LEFT");
    $module['userModulesCertifications'] = $DBLIB->get("userModulesCertifications",null,["userModulesCertifications.*","users.users_name1", "users.users_name2", "users.users_userid"]);


    $DBLIB->where("modules_id",$module['modules_id']);
    $DBLIB->where("modulesSteps_deleted",0);
    $DBLIB->where("modulesSteps_show",1);
    $DBLIB->orderBy("modulesSteps_order","ASC");
    $DBLIB->orderBy("modulesSteps_deleted", "ASC");
    $DBLIB->orderBy("modulesSteps_id", "ASC");
    $module['steps'] = $DBLIB->get("modulesSteps",null,["modulesSteps_id","modulesSteps_completionTime"]);

    //Figure out the point they're at....
    $module['stepsids'] = [];
    $module['completed'] = true;
    $module['totalTime'] = 0;
    foreach ($module['steps'] as $step) {
        if (!in_array($step["modulesSteps_id"],$module['allStepsCompleted'])) $module['completed'] = false;
        $module['totalTime'] += $step["modulesSteps_completionTime"];
    }

    $module['canComplete'] = true;
    //for users with edit permission, check if they're actually allowed to complete this module, or just edit it
    if ($AUTH->instancePermissionCheck("TRAINING:EDIT") && $module['modules_visibleToGroups'] != null && (!in_array($AUTH->data['instance']["instancePositions_id"], explode(',',$module['modules_visibleToGroups'])))){
        $module['canComplete'] = false;
    }

    $module['visibleToGroups'] = [];
    $module['visibleToGroupsString'] = null;
    if ($module['modules_visibleToGroups'] != null) $module['visibleToGroups'] = explode(',',$module['modules_visibleToGroups']);
    foreach($module['visibleToGroups'] as $index=>$group) {
        $groupName = "";
        foreach ($PAGEDATA['positions'] as $position) {
            if ($position['instancePositions_id'] == $group) $groupName = $position['instancePositions_displayName'];
        }
        if (count($module['visibleToGroups']) === 1) $module['visibleToGroupsString'] = $groupName;
        else {
            if (($index+1) === count($module['visibleToGroups'])) $module['visibleToGroupsString'] .= " & " . $groupName;
            elseif ($index > 0) $module['visibleToGroupsString'] .= ", " . $groupName;
            else $module['visibleToGroupsString'] = $groupName;
        }
    }
    $PAGEDATA['modules'][] = $module;
}

echo $TWIG->render('training/training_modules.twig', $PAGEDATA);
?>
