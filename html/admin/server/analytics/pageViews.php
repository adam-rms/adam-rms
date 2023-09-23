<?php
require_once __DIR__ . '/../../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Analytics - Page Views", "BREADCRUMB" => false];
if (!$AUTH->serverPermissionCheck("VIEW-ANALYTICS")) die($TWIG->render('404.twig', $PAGEDATA));

/*
$DBLIB->join("users", "users.users_userid=analyticsEvents.users_userid", "LEFT");
$DBLIB->groupBy("users.users_userid");
$DBLIB->orderBy("count", "DESC");
$PAGEDATA['viewsByUser'] = $DBLIB->get("analyticsEvents",null,["COUNT(analyticsEvents.analyticsEvents_id) AS count", "users.users_name1", "users.users_name2", "users.users_userid"]);
*/

$DBLIB->orderBy("countSum", "DESC");
$PAGEDATA['viewsByUser'] = $DBLIB->get("users",null,["users.users_name1", "users.users_name2", "users.users_userid", "(SELECT COUNT(*) FROM analyticsEvents WHERE analyticsEvents.users_userid=users.users_userid AND analyticsEvents.analyticsEvents_action='API-CALL') AS countApi", "(SELECT COUNT(*) FROM analyticsEvents WHERE analyticsEvents.users_userid=users.users_userid AND analyticsEvents.analyticsEvents_action='PAGE-REQUEST') AS countPages", "(SELECT COUNT(*) FROM analyticsEvents WHERE analyticsEvents.users_userid=users.users_userid) AS countSum"]);

$DBLIB->groupBy("analyticsEvents.analyticsEvents_path");
$DBLIB->orderBy("count", "DESC");
if (!isset($_GET['showAPI'])) $DBLIB->where("analyticsEvents.analyticsEvents_action", "API-CALL", "!=");
$PAGEDATA['viewsByPage'] = $DBLIB->get("analyticsEvents",null,["COUNT(analyticsEvents.analyticsEvents_id) AS count", "analyticsEvents.analyticsEvents_path"]);


$DBLIB->groupBy("hour");
$DBLIB->groupBy("day");
$DBLIB->orderBy("day", "ASC");
$DBLIB->orderBy("hour", "ASC");
$PAGEDATA['viewsByHour'] = $DBLIB->get("analyticsEvents", null, ["HOUR(analyticsEvents_timestamp) as hour", "DAYNAME(analyticsEvents_timestamp) as day", "count(*) AS count"]);

echo $TWIG->render('server/analytics/pageViews.twig', $PAGEDATA);
?>
