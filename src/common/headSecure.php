<?php
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/../assets/widgets/statsWidgets.php'; //Stats on homepage etc.

//THIS IS DUPLICATED SOMEWHAT IN API HEAD SECURE AS SECURITY IS HANDLED SLIGHTLY DIFFERENTLY ON THE API END

if (!$GLOBALS['AUTH']->login) {
    $_SESSION['return'] = str_replace("src/", "", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    if ($CONFIG['DEV']) die($GLOBALS['AUTH']->debug . "<br/><a href='" . $CONFIG['ROOTURL'] . "/login/'>" . $CONFIG['ROOTURL'] . "/login/</a>");
    header("Location: " . $CONFIG['ROOTURL'] . "/login/");
    die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/login/" . '" />');
}
if (!$CONFIG['DEV']) {
    Sentry\configureScope(function (Sentry\State\Scope $scope): void {
        $scope->setUser(['username' => $GLOBALS['AUTH']->data['users_username'], "id" => $GLOBALS['AUTH']->data['users_userid']]);
        if ($GLOBALS['AUTH']->data['instance']) $scope->setExtra('instances_id', $GLOBALS['AUTH']->data['instance']['instances_id']);
    });
} elseif (!$AUTH->serverPermissionCheck("USE-DEV") and !$GLOBALS['AUTH']->data['viewSiteAs']) {
    die("Sorry - you can't use this development version of the site. <a href=\"" . $CONFIG['ROOTURL'] . "/login/?logout\">Logout</a>");
}

$PAGEDATA['AUTH'] = $AUTH;
$PAGEDATA['USERDATA'] = $AUTH->data;
$PAGEDATA['USERDATA']['users_email_md5'] = md5($PAGEDATA['USERDATA']['users_email']);

$DBLIB->insert("analyticsEvents", [
    "analyticsEvents_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "adminUser_users_userid" => $AUTH->data['viewSiteAs'] ? $AUTH->data['viewSiteAs']['users_userid'] : null,
    "authTokens_id" => $AUTH->data['authTokens_id'],
    "instances_id" => $AUTH->data['instance'] ?  $AUTH->data['instance']['instances_id'] : null,
    "analyticsEvents_path" => strtok($_SERVER["REQUEST_URI"], '?'),
    "analyticsEvents_action" => 'PAGE-REQUEST',
    "analyticsEvents_payload" => count($_GET) > 0 ? (strlen(json_encode($_GET)) > 65535 ? null : json_encode($_GET)) : null,
]);

// Create a set of instances that can be joined via the trusted domains route
if ($AUTH->data['users_emailVerified'] == 1) {
    $DBLIB->where("instances_deleted", 0);
    $DBLIB->where("instances_trustedDomains IS NOT NULL");
    if (count($AUTH->data['instance_ids']) > 0) $DBLIB->where("instances_id NOT IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
    $instancesForTrustedDomains = $DBLIB->get("instances", null, ["instances_id", "instances_name", "instances_trustedDomains"]);
    $PAGEDATA['instancesAvailableToJoinAsTrustedDomains'] = [];
    $userEmailDomain = array_pop(explode('@', $AUTH->data['users_email']));
    foreach ($instancesForTrustedDomains as $instance) {
        $instance['trustedDomains'] = json_decode($instance['instances_trustedDomains'], true);
        if (!$instance['trustedDomains']['domains'] or count($instance['trustedDomains']['domains']) < 1 or !$instance['trustedDomains']['instancePositions_id']) continue;
        elseif (!in_array($userEmailDomain, $instance['trustedDomains']['domains'])) continue; // Not eligible to join
        elseif (!$bCMS->instanceHasUserCapacity($instance['instances_id'])) continue; // No space in instance
        else $PAGEDATA['instancesAvailableToJoinAsTrustedDomains'][] = $instance;
    }
} else $PAGEDATA['instancesAvailableToJoinAsTrustedDomains'] = [];

if ($CONFIG['LINKS_TERMSOFSERVICEURL'] and ($PAGEDATA['USERDATA']['users_termsAccepted'] == 0 or $PAGEDATA['USERDATA']['users_termsAccepted'] == null)) {
    $PAGEDATA['pageConfig'] = ["TITLE" => "Accept Terms", "BREADCRUMB" => false, "NOMENU" => true];
    die($TWIG->render('index_acceptTerms.twig', $PAGEDATA));
} elseif ($PAGEDATA['USERDATA']['users_emailVerified'] == 0 and $CONFIGCLASS->get('EMAILS_ENABLED') === "Enabled" and !($AUTH->serverPermissionCheck("CONFIG:SET") and str_ends_with(getcwd(), "server"))) {
    $PAGEDATA['pageConfig'] = ["TITLE" => "Verify Email Address", "BREADCRUMB" => false, "NOMENU" => true];
    $PAGEDATA['fromEmail'] = $CONFIGCLASS->get('EMAILS_FROMEMAIL');
    die($TWIG->render('index_emailVerified.twig', $PAGEDATA));
} elseif ($PAGEDATA['USERDATA']['users_changepass'] == 1) {
    $PAGEDATA['pageConfig'] = ["TITLE" => "Change Password", "BREADCRUMB" => false, "NOMENU" => true];
    die($TWIG->render('index_forceChangePassword.twig', $PAGEDATA));
} elseif ($AUTH->data['instance']) {
    if ($AUTH->data['instance']['instances_suspended'] == 1 and !str_ends_with(getcwd(), "server")) {
        $PAGEDATA['pageConfig'] = ["TITLE" => "Business Suspended", "BREADCRUMB" => false, "NOMENU" => true];
        die($TWIG->render('index_instanceBillingIssue.twig', $PAGEDATA));
    }
    //get all projects
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("projects.projects_archived", 0);
    $DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
    $DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id", "LEFT");
    $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
    $DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
    $DBLIB->orderBy("projects.projects_name", "ASC");
    $DBLIB->orderBy("projects.projects_created", "ASC");
    $projects = $DBLIB->get("projects", null, ["projects_id", "projectsTypes.*", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end", "projects_dates_use_start", "projects_dates_use_end", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour", "projectsStatuses.projectsStatuses_backgroundColour", "projectsStatuses.projectsStatuses_fontAwesome", "projects_manager", "projects_parent_project_id"]);
    $PAGEDATA['projects'] = [];
    $tempProjectKeys = []; //Track the Project IDs of all projects and their place in the array (allows us to preserve sorting)
    foreach ($projects as $index => $project) {
        if ($project['projects_parent_project_id'] == null) {
            $project['subProjects'] = [];
            $tempProjectKeys[$project['projects_id']] = count($PAGEDATA['projects']);
            $PAGEDATA['projects'][] = $project;
        }
    }
    foreach ($projects as $index => $project) { //Loop back throuhg the projects once more and join the subprojects up with their parents, preserving sorting again
        if ($project['projects_parent_project_id'] != null and isset($tempProjectKeys[$project['projects_parent_project_id']])) {
            $PAGEDATA['projects'][$tempProjectKeys[$project['projects_parent_project_id']]]['subProjects'][] = $project;
        }
    }
    unset($tempProjectKeys);

    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("cmsPages_deleted", 0);
    $DBLIB->where("cmsPages_archived", 0);
    $DBLIB->where("cmsPages_showNav", 1);
    $DBLIB->where("cmsPages_subOf", NULL, "IS");
    if (isset($AUTH->data['instance']["instancePositions_id"])) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
    $DBLIB->orderBy("cmsPages_navOrder", "ASC");
    $DBLIB->orderBy("cmsPages_id", "ASC");
    $PAGEDATA['NAVIGATIONCMSPages'] = [];
    foreach ($DBLIB->get("cmsPages", null, ["cmsPages.*"]) as $page) {
        $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("cmsPages_deleted", 0);
        $DBLIB->where("cmsPages_archived", 0);
        $DBLIB->where("cmsPages_showNav", 1);
        $DBLIB->where("cmsPages_subOf", $page['cmsPages_id']);
        if (isset($AUTH->data['instance']["instancePositions_id"])) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))"); //If the user doesn't have a position - they're server admins
        $DBLIB->orderBy("cmsPages_name", "ASC");
        $page['SUBPAGES'] = $DBLIB->get("cmsPages");
        $PAGEDATA['NAVIGATIONCMSPages'][] = $page;
    }
} elseif ($AUTH->serverPermissionCheck("INSTANCES:VIEW") && $AUTH->serverPermissionCheck("INSTANCES:FULL_PERMISSIONS_IN_INSTANCE")) {
    // User is a server admin who has no instance - this is often caused by them deleting one. Select an instance for them to use at random.
    $DBLIB->where("instances_deleted", 0);
    $instance = $DBLIB->getOne("instances", ["instances_id"]);
    if ($instance) {
        $_SESSION['instanceID'] = $instance["instances_id"];
        header("Location: " . $CONFIG['ROOTURL'] . "/server/instances.php");
        exit;
    } else {
        $PAGEDATA['pageConfig'] = ["TITLE" => "No Businesses", "BREADCRUMB" => false, "NOMENU" => true];
        die($TWIG->render('index_noInstances.twig', $PAGEDATA));
    }
} else {
    $PAGEDATA['pageConfig'] = ["TITLE" => "No Businesses", "BREADCRUMB" => false, "NOMENU" => true];
    die($TWIG->render('index_noInstances.twig', $PAGEDATA));
}
