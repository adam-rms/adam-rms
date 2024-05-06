<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("modules.modules_deleted", 0);
// If user does not have draft edit permissions, only show live modules
if (!$AUTH->instancePermissionCheck("TRAINING:VIEW:DRAFT_MODULES")) $DBLIB->where("modules.modules_show", 1);
// Check if module has group visibility permissions
// if the user can edit modules and is trying to edit this module (ie. viewing steps or users), ignore group visibility permissions
if ($AUTH->data['instance']["instancePositions_id"] && !(($AUTH->instancePermissionCheck("TRAINING:EDIT") && isset($_GET['steps'])) || ($AUTH->instancePermissionCheck("TRAINING:VIEW:USER_PROGRESS_IN_MODULES") && isset($_GET['users'])) )) {
    $DBLIB->where("(modules.modules_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", modules.modules_visibleToGroups) > 0))");
} 
// Check if module has steps to complete - ie. not a physical only module (type 3)
$DBLIB->where("modules.modules_type != 3");
$DBLIB->where("modules.modules_id", $_GET['id']);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$PAGEDATA['module'] = $DBLIB->getOne('modules', ["modules.*"]);
if (!$PAGEDATA['module']) die($TWIG->render('404.twig', $PAGEDATA));


$DBLIB->where("modulesSteps_deleted",0);
$DBLIB->where("modules_id",$PAGEDATA['module']['modules_id']);
$DBLIB->orderBy("modulesSteps_order","ASC");
$DBLIB->orderBy("modulesSteps_deleted", "ASC");
$DBLIB->orderBy("modulesSteps_id", "ASC");
if (!$AUTH->instancePermissionCheck("TRAINING:EDIT") or !isset($_GET['steps'])) $DBLIB->where("modulesSteps_show",1);
$PAGEDATA['module']['steps'] = $DBLIB->get("modulesSteps");

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['module']['modules_name'], "BREADCRUMB" => false];


if (isset($_GET['users']) and $AUTH->instancePermissionCheck("TRAINING:VIEW:USER_PROGRESS_IN_MODULES")) {
    //Users
    if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
    else $PAGEDATA['search'] = null;
    if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
    else $page = 1;
    $DBLIB->pageLimit = 20; //Users per page
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    if (strlen($PAGEDATA['search']) > 0) {
        //Search
        $DBLIB->where("(
		users_username LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR users_name1 LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR users_name2 LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
    )");
    }
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $users = $DBLIB->arraybuilder()->paginate('users', $page, ["users.users_username", "users.users_name1", "users.users_name2", "users.users_userid","users.users_thumbnail"]);
    $PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];
    foreach ($users as $user) {
        //Get whether they've completed the steps
        $DBLIB->where("modules_id",$PAGEDATA['module']['modules_id']);
        $DBLIB->where("userModules.users_userid",$user['users_userid']);
        $DBLIB->orderBy("userModules_id","ASC");
        $userModules = $DBLIB->get("userModules",null,["userModules.*"]);
        $user['userModules'] = [];
        //Get their progress
        $user['allStepsCompleted'] = [];
        $user['dates'] = ["start" => false,"updated" => false];
        foreach ($userModules as $module) {
            if (!$user['dates']['start'] or (strtotime($user['dates']['start']) > strtotime($module['userModules_started']))) $user['dates']['start'] = strtotime($module['userModules_started']);
            if (!$user['dates']['updated'] or (strtotime($user['dates']['updated']) < strtotime($module['userModules_updated']))) $user['dates']['updated'] = strtotime($module['userModules_updated']);

            $module['userModules_stepsCompleted'] = explode(",",$module['userModules_stepsCompleted']);
            foreach ($module['userModules_stepsCompleted'] as $step) {
                array_push($user['allStepsCompleted'],$step);
            }
            $user['userModules'] = $module;
        }

        //Get certifications
        $DBLIB->where("modules_id",$PAGEDATA['module']['modules_id']);
        $DBLIB->where("userModulesCertifications.users_userid",$user['users_userid']);
        $DBLIB->orderBy("userModulesCertifications_revoked","ASC");
        $DBLIB->orderBy("userModulesCertifications_timestamp","DESC");
        $DBLIB->join("users","userModulesCertifications_approvedBy=users.users_userid","LEFT");
        $user['userModulesCertifications'] = $DBLIB->get("userModulesCertifications",null,["userModulesCertifications.*","users.users_name1", "users.users_name2", "users.users_userid"]);
        $PAGEDATA["users"][] = $user;
    }
    echo $TWIG->render('training/training_users.twig', $PAGEDATA);
}
elseif (isset($_GET['steps']) and $AUTH->instancePermissionCheck("TRAINING:EDIT")) echo $TWIG->render('training/training_steps.twig', $PAGEDATA);
else {
    $DBLIB->where("modules_id",$PAGEDATA['module']['modules_id']);
    $DBLIB->where("userModules.users_userid",$AUTH->data['users_userid']);
    $DBLIB->orderBy("userModules_id","ASC");
    $userModules = $DBLIB->get("userModules",null,["userModules.*"]);
    $PAGEDATA['userModules'] = [];

    //Get their progress
    $PAGEDATA['module']['allStepsCompleted'] = [];
    foreach ($userModules as $thismodule) {
        $thismodule['userModules_stepsCompleted'] = explode(",", $thismodule['userModules_stepsCompleted']);
        foreach ($thismodule['userModules_stepsCompleted'] as $step) {
            array_push($PAGEDATA['module']['allStepsCompleted'], $step);
        }
        $PAGEDATA['userModules'] = $thismodule;
    }
    echo $TWIG->render('training/training_progress.twig', $PAGEDATA);
}
?>
