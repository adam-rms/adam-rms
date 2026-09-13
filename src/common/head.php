<?php

/**
 * This file is used by every page. It is included in every page and contains the following:
 * - Database connection
 * - Twig setup
 * - Error handling
 * - Sentry error reporting
 * - Global functions ("bCMS" class)
 * - Config Variables 
 */
require_once(__DIR__ . '/../../vendor/autoload.php'); //Composer
require_once __DIR__ . '/libs/Config/Config.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;
use Twig\Extra\String\StringExtension;

//TWIG
$TWIGLOADER = new \Twig\Loader\FilesystemLoader([__DIR__ . '/../']);
if (getenv('DEV_MODE') == "true") {
    $TWIG = new \Twig\Environment($TWIGLOADER, array(
        'debug' => true,
        'auto_reload' => true,
        'charset' => 'utf-8'
    ));
    $TWIG->addExtension(new \Twig\Extension\DebugExtension());
} else {
    $TWIG = new \Twig\Environment($TWIGLOADER, array(
        'debug' => false,
        'auto_reload' => false,
        'cache' => '/tmp/',
        'charset' => 'utf-8'
    ));
}
$TWIG->addExtension(new StringExtension());

if (getenv('DEV_MODE') == "true") {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ERROR | E_PARSE);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

/* DATBASE CONNECTION */
try {
    $DBLIB = new MysqliDb([
        'host' => getenv('DB_HOSTNAME'),
        'username' => getenv('DB_USERNAME'), //CREATE INSERT SELECT UPDATE DELETE
        'password' => getenv('DB_PASSWORD'),
        'db' => getenv('DB_DATABASE'),
        'port' => getenv('DB_PORT') ?: 3306,
        //'prefix' => 'adamrms_',
        'charset' => 'utf8mb4'
    ]);
} catch (Exception $e) {
    // TODO use twig for this
    if (getenv('DEV_MODE') == "true") {
        echo "Could not connect to database: " . $e->getMessage() . "\n\n\nPlease doulbe check you have setup environment variables correctly for the database connection.";
        exit;
    } else {
        echo "Could not connect to database";
        exit;
    }
}

$CONFIGCLASS = new Config;
$CONFIG = $CONFIGCLASS->getConfigArray();
if (count($CONFIGCLASS->CONFIG_MISSING_VALUES) > 0) {
    $update = false;
    if (isset($_POST['settingUpConfigUsingConfigFormTwig']) and $_POST['settingUpConfigUsingConfigFormTwig'] == "true") {
        $update = $CONFIGCLASS->formArrayProcess($_POST);
    }
    if ($update !== true)
        die($TWIG->render('common/libs/Config/configForm.twig', ["form" => $CONFIGCLASS->formArrayBuild(), "errors" => is_array($update) ? $update : []]));
    else {
        header("Location: " . $CONFIG['ROOTURL'] . "?");
        exit;
    }
}

// Set the timezone
date_default_timezone_set($CONFIG['TIMEZONE']);

// Include the bCMS class, which contains useful functions 
require_once __DIR__ . '/libs/bCMS/bCMS.php';
$GLOBALS['bCMS'] = new bCMS;

if (getenv('DEV_MODE') != "true" and $CONFIG['ERRORS_PROVIDERS_SENTRY'] and strlen($CONFIG['ERRORS_PROVIDERS_SENTRY']) > 0) {
    Sentry\init([
        'dsn' => $CONFIG['ERRORS_PROVIDERS_SENTRY'],
        'release' => $bCMS->getVersionNumber(),
        'sample_rate' => 1.0,
    ]);
}

// TODO move these functions to a class
function generateNewTag()
{
    global $DBLIB;
    //Get highest current tag - sort numerically so A-10000 ranks above A-9999
    $DBLIB->orderBy("CAST(SUBSTRING(assets_tag, 3) AS UNSIGNED)", "DESC");
    $DBLIB->where("assets_tag", 'A-%', 'like');
    $tag = $DBLIB->getone("assets", ["assets_tag"]);
    if ($tag) {
        if (is_numeric(str_replace("A-", "", $tag["assets_tag"]))) {
            $value = intval(str_replace("A-", "", $tag["assets_tag"])) + 1;
            if ($value <= 9999)
                $value = sprintf('%04d', $value);
            return "A-" . $value;
        } else
            return "A-0001";
    } else
        return "A-0001";
}
function assetFlagsAndBlocks($assetid)
{
    global $DBLIB;
    $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
    $DBLIB->where("(maintenanceJobs.maintenanceJobs_blockAssets = 1 OR maintenanceJobs.maintenanceJobs_flagAssets = 1)");
    $DBLIB->where("(FIND_IN_SET(" . $assetid . ", maintenanceJobs.maintenanceJobs_assets) > 0)");
    $DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
    //$DBLIB->join("users AS userCreator", "userCreator.users_userid=maintenanceJobs.maintenanceJobs_user_creator", "LEFT");
    //$DBLIB->join("users AS userAssigned", "userAssigned.users_userid=maintenanceJobs.maintenanceJobs_user_assignedTo", "LEFT");
    $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_priority", "DESC");
    $jobs = $DBLIB->get('maintenanceJobs', null, ["maintenanceJobs.maintenanceJobs_id", "maintenanceJobs.maintenanceJobs_faultDescription", "maintenanceJobs.maintenanceJobs_title", "maintenanceJobs.maintenanceJobs_faultDescription", "maintenanceJobs.maintenanceJobs_flagAssets", "maintenanceJobs.maintenanceJobs_blockAssets", "maintenanceJobsStatuses.maintenanceJobsStatuses_name"]);
    $return = ["BLOCK" => [], "FLAG" => [], "COUNT" => ["BLOCK" => 0, "FLAG" => 0]];
    if (!$jobs)
        return $return;
    foreach ($jobs as $job) {
        if ($job["maintenanceJobs_blockAssets"] == 1) {
            $return['BLOCK'][] = $job;
            $return['COUNT']['BLOCK'] += 1;
        }
        if ($job["maintenanceJobs_flagAssets"] == 1) {
            $return['FLAG'][] = $job;
            $return['COUNT']['FLAG'] += 1;
        }
    }
    return $return;
}
function assetLatestScan($assetid)
{
    if ($assetid == null)
        return false;
    global $DBLIB;
    $DBLIB->orderBy("assetsBarcodesScans.assetsBarcodesScans_timestamp", "DESC");
    $DBLIB->where("assetsBarcodes.assets_id", $assetid);
    $DBLIB->where("assetsBarcodes.assetsBarcodes_deleted", 0);
    $DBLIB->join("assetsBarcodes", "assetsBarcodes.assetsBarcodes_id=assetsBarcodesScans.assetsBarcodes_id");
    $DBLIB->join("locationsBarcodes", "locationsBarcodes.locationsBarcodes_id=assetsBarcodesScans.locationsBarcodes_id", "LEFT");
    $DBLIB->join("assets", "assets.assets_id=assetsBarcodesScans.location_assets_id", "LEFT");
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("locations", "locations.locations_id=locationsBarcodes.locations_id", "LEFT");
    $DBLIB->join("users", "users.users_userid=assetsBarcodesScans.users_userid");
    return $DBLIB->getone("assetsBarcodesScans", ["assetsBarcodesScans.*", "users.users_name1", "users.users_name2", "locations.locations_name", "locations.locations_id", "assets.assetTypes_id", "assetTypes.assetTypes_name", "assets.assets_tag"]);
}
/**
 * Build the asset dispatch board columns for an instance.
 * Asset assignment statuses are soft-deleted, so a status that's been deleted can still be referenced
 * by assets that were assigned to it before deletion (matched here by ID, so a later reorder of the
 * active statuses can never collide with a deleted one). An asset with no status at all (NULL) is
 * always shown in whichever status currently has order 0 - if that status has been deleted, it's
 * included here too, so those assets aren't lost from the board.
 * Columns are keyed by assetsAssignmentsStatus_id.
 * @param int $instancesId The instance that owns the statuses
 * @param array $assetsList The assigned assets to place onto the board, grouped by assetType (as built in projects/data.php)
 * @return array Board columns keyed by assetsAssignmentsStatus_id, each including an "assets" key
 */
function buildAssetsAssignmentsBoard($instancesId, $assetsList)
{
    global $DBLIB;

    $DBLIB->where("instances_id", $instancesId);
    $DBLIB->where("assetsAssignmentsStatus_deleted", 0);
    $DBLIB->orderBy("assetsAssignmentsStatus_order", "ASC");
    $statuses = $DBLIB->get("assetsAssignmentsStatus");
    $statusIds = array_column($statuses, 'assetsAssignmentsStatus_id');
    $defaultStatusId = null; //the single column NULL-status assets are placed into
    foreach ($statuses as $status) {
        if ((int)$status['assetsAssignmentsStatus_order'] === 0) {
            $defaultStatusId = $status['assetsAssignmentsStatus_id'];
            break;
        }
    }

    $missingIds = [];
    $needsDefaultColumn = false;
    foreach ($assetsList as $assetType) {
        foreach ($assetType['assets'] as $asset) {
            $statusId = $asset['assetsAssignmentsStatus_id'];
            if ($statusId !== null) {
                if (!in_array($statusId, $statusIds)) $missingIds[$statusId] = true;
            } elseif ($defaultStatusId === null) {
                $needsDefaultColumn = true;
            }
        }
    }

    if (!empty($missingIds)) {
        $DBLIB->where("instances_id", $instancesId);
        $DBLIB->where("assetsAssignmentsStatus_id", array_keys($missingIds), "IN");
        foreach ($DBLIB->get("assetsAssignmentsStatus") as $deletedStatus) {
            $statuses[] = $deletedStatus;
            $statusIds[] = $deletedStatus['assetsAssignmentsStatus_id'];
        }
    }
    if ($needsDefaultColumn) {
        $DBLIB->where("instances_id", $instancesId);
        $DBLIB->where("assetsAssignmentsStatus_order", 0);
        $DBLIB->orderBy("assetsAssignmentsStatus_id", "ASC");
        $defaultStatus = $DBLIB->getOne("assetsAssignmentsStatus");
        if ($defaultStatus) {
            $defaultStatusId = $defaultStatus['assetsAssignmentsStatus_id'];
            if (!in_array($defaultStatusId, $statusIds)) $statuses[] = $defaultStatus;
        }
    }
    usort($statuses, function ($a, $b) {
        return $a['assetsAssignmentsStatus_order'] <=> $b['assetsAssignmentsStatus_order'];
    });

    $board = [];
    foreach ($statuses as $status) {
        $tempAssets = [];
        foreach ($assetsList as $assetType) {
            foreach ($assetType['assets'] as $asset) {
                if ($asset['assetsAssignmentsStatus_id'] !== null) {
                    if ($asset['assetsAssignmentsStatus_id'] == $status['assetsAssignmentsStatus_id']) $tempAssets[] = $asset;
                } elseif ($status['assetsAssignmentsStatus_id'] == $defaultStatusId) { //assets with no status at all go in the one default column
                    $tempAssets[] = $asset;
                }
            }
        }
        $status['assets'] = $tempAssets;
        $board[$status['assetsAssignmentsStatus_id']] = $status;
    }
    return $board;
}
/**
 * Strip the "assets" key from each column of a board built by buildAssetsAssignmentsBoard().
 * Used for the copy of the board that's JSON-encoded into the page for JavaScript, which only
 * needs the status metadata - the assets themselves are already rendered from the full board and
 * would otherwise be duplicated in full in the page's HTML and in client memory for no purpose.
 * @param array $board A board as returned by buildAssetsAssignmentsBoard()
 * @return array The same board with each column's "assets" key removed
 */
function stripBoardAssets($board)
{
    $slim = [];
    foreach ($board as $key => $status) {
        unset($status['assets']);
        $slim[$key] = $status;
    }
    return $slim;
}

// Setup the "PAGEDATA" array which is used by Twig
$PAGEDATA = array('CONFIG' => $CONFIG, 'VERSION' => $bCMS->getVersionNumber());

// Setup the "MAINTENANCEJOBPRIORITIES" array which is used by Twig
$GLOBALS['MAINTENANCEJOBPRIORITIES'] = [
    1 => ["class" => "danger", "id" => 1, "text" => "Emergency"],
    2 => ["class" => "danger", "id" => 2, "text" => "Business Critical"],
    3 => ["class" => "danger", "id" => 3, "text" => "Urgent"],
    4 => ["class" => "danger", "id" => 4, "text" => "Routine - High"],
    5 => ["class" => "warning", "id" => 5, "text" => "Routine - Medium", "default" => true],
    6 => ["class" => "warning", "id" => 6, "text" => "Routine - Low"],
    7 => ["class" => "warning", "id" => 7, "text" => "Monthly-cycle Maintenance"],
    8 => ["class" => "success", "id" => 8, "text" => "Annual-cycle Maintenance"],
    9 => ["class" => "success", "id" => 9, "text" => "Long Term"],
    10 => ["class" => "info", "id" => 10, "text" => "Log only"]
];
$PAGEDATA['MAINTENANCEJOBPRIORITIES'] = $GLOBALS['MAINTENANCEJOBPRIORITIES'];


// Include Twig Extensions
require_once __DIR__ . '/libs/twigExtensions.php';

// Try to open up a session cookie
try {
    session_set_cookie_params(43200); //12hours
    session_start(); //Open up the session
} catch (Exception $e) {
    //Do Nothing
}


// Include the content security policy
$CSP = [
    "default-src" => [
        ["value" => "'none'", "comment" => ""]
    ],
    "script-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "'unsafe-inline'", "comment" => "We have loads of inline JS"],
        ["value" => "'unsafe-eval'", "comment" => ""],
        ["value" => "https://*.adam-rms.com", "comment" => ""],
        ["value" => "https://cdnjs.cloudflare.com", "comment" => ""],
        ["value" => "https://static.cloudflareinsights.com", "comment" => ""],
        ["value" => "https://www.youtube.com", "comment" => "Training modules allow youtube embed"],
        ["value" => "https://*.ytimg.com", "comment" => "Training modules allow youtube embed"],
        ["value" => "https://js.stripe.com", "comment" => "Stripe payment pricing table"]
    ],
    "style-src" => [
        ["value" => "'unsafe-inline'", "comment" => "We have loads of inline CSS"],
        ["value" => "'self'", "comment" => ""],
        ["value" => "https://*.adam-rms.com", "comment" => ""],
        ["value" => "https://cdnjs.cloudflare.com", "comment" => ""],
        ["value" => "https://fonts.googleapis.com", "comment" => "Google fonts is used extensivley"]
    ],
    "font-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "data:", "comment" => ""],
        ["value" => "https://*.adam-rms.com", "comment" => ""],
        ["value" => "https://fonts.googleapis.com", "comment" => "Google fonts is used extensivley"],
        ["value" => "https://fonts.gstatic.com", "comment" => "Google fonts is used extensivley"],
        ["value" => "https://cdnjs.cloudflare.com", "comment" => "Libraries referenced in HTML"]
    ],
    "manifest-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "https://*.adam-rms.com", "comment" => "Show images on mobile devices like favicons"]
    ],
    "img-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "data:", "comment" => ""],
        ["value" => "blob:", "comment" => ""],
        ["value" => "https://*.adam-rms.com", "comment" => ""],
        ["value" => "https://cdnjs.cloudflare.com", "comment" => "Libraries referenced in HTML"],
        ["value" => "https://cloudflareinsights.com", "comment" => ""],
        ["value" => "https://*.ytimg.com", "comment" => "Training modules allow youtube embed"]
    ],
    "connect-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "https://*.adam-rms.com", "comment" => ""],
        ["value" => "https://sentry.io", "comment" => ""],
        ["value" => "https://cloudflareinsights.com", "comment" => ""],
        ["value" => "https://*.amazonaws.com", "comment" => "To allow S3 uploads"],
        ["value" => "https://*.r2.cloudflarestorage.com", "comment" => "Cloudflare R2 bucket uploads"],
        ["value" => "https://cdnjs.cloudflare.com", "comment" => "Browser fetches source maps from CDN scripts"],
    ],
    "frame-src" => [
        ["value" => "https://www.youtube.com", "comment" => "Training modules allow youtube embed"],
        ["value" => "https://js.stripe.com", "comment" => "Stripe payment pricing table"]
    ],
    "object-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "blob:", "comment" => "Inline PDFs generated by the system"]
    ],
    "worker-src" => [
        ["value" => "'self'", "comment" => ""],
        ["value" => "blob:", "comment" => "Use of camera"]
    ],
    "frame-ancestors" => [
        ["value" => "'self'", "comment" => ""]
    ],
    "report-uri" => [
        ["value" => "https://o83272.ingest.sentry.io/api/5204912/security/?sentry_key=3937ab95cc404dfa95b0e0cb91db5fc6", "comment" => "Report to sentry"]
    ]
];
if (!empty($CONFIG['ANALYTICS_CLARITY_PROJECT_ID'])) {
    // https://learn.microsoft.com/en-us/clarity/setup-and-installation/clarity-csp
    $CSP['script-src'][] = ["value" => "https://*.clarity.ms", "comment" => "Microsoft Clarity analytics"];
    $CSP['connect-src'][] = ["value" => "https://*.clarity.ms", "comment" => "Microsoft Clarity analytics"];
    $CSP['connect-src'][] = ["value" => "https://c.bing.com", "comment" => "Microsoft Clarity telemetry"];
    $CSP['img-src'][] = ["value" => "https://*.clarity.ms", "comment" => "Microsoft Clarity"];
}

if ($CONFIG['CSP_ENABLED'] === "Enabled") {
    $CSPString = "Content-Security-Policy: ";
    foreach ($CSP as $key => $value) {
        $CSPString .= $key;
        foreach ($value as $subvalue) {
            $CSPString .= " " . $subvalue['value'];
        }
        $CSPString .= ";";
    }
    header($CSPString);
}


// Include the Auth class
require_once __DIR__ . '/libs/Auth/main.php';
$GLOBALS['AUTH'] = new bID;
