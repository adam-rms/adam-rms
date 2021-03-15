<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(113)) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Training", "BREADCRUMB" => false];

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 20);
$DBLIB->where("modules.modules_deleted", 0);
if (!$AUTH->instancePermissionCheck(114)) $DBLIB->where("modules.modules_show", 1);
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


    $PAGEDATA['modules'][] = $module;
}


echo $TWIG->render('training/training_modules.twig', $PAGEDATA);
?>
