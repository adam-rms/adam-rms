<?php
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/../assets/widgets/statsWidgets.php'; //Stats on homepage etc.

//DON'T FORGET THIS IS DUPLICATED SOMEWHAT IN API HEAD SECURE AS SECURITY IS HANDLED SLIGHTLY DIFFERENTLY ON THE API END

if (!$GLOBALS['AUTH']->login) {
    if ($CONFIG['DEV']) die("<h2>Debugging enabled - auth fail debug details</h2>" . $GLOBALS['AUTH']->debug . "<br/><br/><br/>Login false - redirect to <a href='" . $CONFIG['ROOTURL'] . "/login/'>" . $CONFIG['ROOTURL'] . "/login/</a>");
    $_SESSION['return'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: " . $CONFIG['ROOTURL'] . "/login/");
    die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/login/" . '" />');
}
if (!$CONFIG['DEV']) {
    Sentry\configureScope(function (Sentry\State\Scope $scope): void {
        $scope->setUser(['username' => $GLOBALS['AUTH']->data['users_username'],"id"=> $GLOBALS['AUTH']->data['users_userid']]);
        if ($GLOBALS['AUTH']->data['instance']) $scope->setExtra('instances_id', $GLOBALS['AUTH']->data['instance']['instances_id']);
    });
} elseif (!$AUTH->permissionCheck(17) and !$GLOBALS['AUTH']->data['viewSiteAs']) {
    die("Sorry - you can't use this development version of the site - please visit adam-rms.com. <a href=\"" . $CONFIG['ROOTURL'] . "/login/?logout\">Logout</a>");
}

$PAGEDATA['AUTH'] = $AUTH;
$PAGEDATA['USERDATA'] = $AUTH->data;
$PAGEDATA['USERDATA']['users_email_md5'] = md5($PAGEDATA['USERDATA']['users_email']);


// Create a set of instances that can be joined via the trusted domains route
if ($AUTH->data['users_emailVerified'] == 1) {
    $DBLIB->where("instances_deleted",0);
    $DBLIB->where("instances_trustedDomains IS NOT NULL");
    if (count($AUTH->data['instance_ids']) > 0) $DBLIB->where("instances_id NOT IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
    $instancesForTrustedDomains = $DBLIB->get("instances",null,["instances_id","instances_name", "instances_trustedDomains"]);
    $PAGEDATA['instancesAvailableToJoinAsTrustedDomains'] = [];
    $userEmailDomain = array_pop(explode('@', $AUTH->data['users_email']));
    foreach ($instancesForTrustedDomains as $instance) {
        $instance['trustedDomains'] = json_decode($instance['instances_trustedDomains'],true);
        if (!$instance['trustedDomains']['domains'] or count($instance['trustedDomains']['domains']) < 1 or !$instance['trustedDomains']['instancePositions_id']) continue;
        elseif (!in_array($userEmailDomain,$instance['trustedDomains']['domains'])) continue; // Not eligible to join
        else $PAGEDATA['instancesAvailableToJoinAsTrustedDomains'][] = $instance;
    }
} else $PAGEDATA['instancesAvailableToJoinAsTrustedDomains'] = [];

if ($PAGEDATA['USERDATA']['users_changepass'] == 1) {
    $PAGEDATA['pageConfig'] = ["TITLE" => "Change Password", "BREADCRUMB" => false, "NOMENU" => true];
    die($TWIG->render('index_forceChangePassword.twig', $PAGEDATA));
} elseif ($AUTH->data['instance']) {
    //Potential project types
    $DBLIB->where("projectsTypes_deleted", 0);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->orderBy("projectsTypes_name", "ASC");
    $PAGEDATA['potentialProjectTypes'] = $DBLIB->get("projectsTypes");
    //Potential project managers
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
    $PAGEDATA['potentialProjectManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);

    //get all projects
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("projects.projects_archived", 0);
    $DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
    $DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id", "LEFT");
    $DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
    $DBLIB->orderBy("projects.projects_name", "ASC");
    $DBLIB->orderBy("projects.projects_created", "ASC");
    $projects = $DBLIB->get("projects", null, ["projects_id", "projectsTypes.*","projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_status", "projects_manager", "projects_parent_project_id"]);
    $PAGEDATA['thisProject'] = false;
    $PAGEDATA['projects'] = [];
    $tempProjectKeys = []; //Track the Project IDs of all projects and their place in the array (allows us to preserve sorting)
    foreach ($projects as $index=>$project) {
        if ($AUTH->data['users_selectedProjectID'] != null and $project['projects_id'] == $AUTH->data['users_selectedProjectID']) { //check if project is user's selected project
            $PAGEDATA['thisProject'] = $project;
        }

        if ($project['projects_parent_project_id'] == null) {
            $project['subProjects'] = [];
            $tempProjectKeys[$project['projects_id']] = count($PAGEDATA['projects']);
            $PAGEDATA['projects'][] = $project;
        }
    }
    foreach ($projects as $index=>$project) { //Loop back throuhg the projects once more and join the subprojects up with their parents, preserving sorting again
        if ($project['projects_parent_project_id'] != null and isset($tempProjectKeys[$project['projects_parent_project_id']])) {
            $PAGEDATA['projects'][$tempProjectKeys[$project['projects_parent_project_id']]]['subProjects'][] = $project;
        }
    }
    unset($tempProjectKeys);

    if ($AUTH->data['users_selectedProjectID'] != null and !$PAGEDATA['thisProject']) { //only option left is they're browsing with a project selected from another instance so we need to go grab it
        $DBLIB->where("projects.projects_id", $AUTH->data['users_selectedProjectID']);
        $DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")"); //Duplicated elsewhere
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
        $DBLIB->join("instances", "projects.instances_id=instances.instances_id", "LEFT");
        $DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
        $DBLIB->orderBy("projects.projects_name", "ASC");
        $DBLIB->orderBy("projects.projects_created", "ASC");
        $PAGEDATA['thisProject'] = $DBLIB->getone("projects", null, ["projects_id", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_status", "projects_manager", "instances.instances_name"]);
    }

    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->where("cmsPages_deleted",0);
    $DBLIB->where("cmsPages_archived",0);
    $DBLIB->where("cmsPages_showNav",1);
    $DBLIB->where("cmsPages_subOf",NULL,"IS");
    if (isset($AUTH->data['instance']["instancePositions_id"])) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're bstudios staff
    $DBLIB->orderBy("cmsPages_navOrder","ASC");
    $DBLIB->orderBy("cmsPages_id","ASC");
    $PAGEDATA['NAVIGATIONCMSPages'] = [];
    foreach ($DBLIB->get("cmsPages",null,["cmsPages.*"]) as $page) {
        $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
        $DBLIB->where("cmsPages_deleted",0);
        $DBLIB->where("cmsPages_archived",0);
        $DBLIB->where("cmsPages_showNav",1);
        $DBLIB->where("cmsPages_subOf",$page['cmsPages_id']);
        if (isset($AUTH->data['instance']["instancePositions_id"])) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're bstudios staff
        $DBLIB->orderBy("cmsPages_name","ASC");
        $page['SUBPAGES'] = $DBLIB->get("cmsPages");
        $PAGEDATA['NAVIGATIONCMSPages'][] = $page;
    }


    if ($AUTH->data['instance']['instances_weekStartDates'] != null) {
        $dates = explode("\n", $AUTH->data['instance']['instances_weekStartDates']);
        $AUTH->data['instance']['weekStartDates'] = [];
        foreach ($dates as $date) {
            array_push($AUTH->data['instance']['weekStartDates'], strtotime($date)*1000);
        }
        unset($dates);
        sort($AUTH->data['instance']['weekStartDates']);
        $PAGEDATA['USERDATA']['instance']['weekStartDates'] = $AUTH->data['instance']['weekStartDates']; //Copy the variable
    } else $AUTH->data['instance']['weekStartDates'] = $PAGEDATA['USERDATA']['instance']['weekStartDates'] = false;
} else {
    $PAGEDATA['pageConfig'] = ["TITLE" => "No Businesses", "BREADCRUMB" => false, "NOMENU" => true];
    die($TWIG->render('index_noInstances.twig', $PAGEDATA));
}
?>
