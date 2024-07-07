<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Businesses", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("INSTANCES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;
if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 20; //Users per page
$DBLIB->orderBy("instances.instances_id", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		instances_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR instances_address LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR instances_phone LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR instances_email LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR instances_website LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
    )");
}
$instances = $DBLIB->arraybuilder()->paginate('instances', $page, ["instances.*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];
$PAGEDATA['instances'] = [];
foreach ($instances as $instance) {
	//Inventory
	$DBLIB->where("assets.instances_id", $instance['instances_id']);
	$DBLIB->orderBy("assets_inserted", "ASC");
	$DBLIB->where("assets_deleted", 0);
	$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
	$assets = $DBLIB->get("assets", null, ["assets_inserted", "assetTypes_value", "assetTypes_mass"]);
	$instance['assets'] = ["VALUE" => 0.0,"MASS" => 0.0, "COUNT" => 0];
	foreach ($assets as $asset) {
		$instance['assets']['VALUE'] += $asset['assetTypes_value'];
		$instance['assets']['MASS'] += $asset['assetTypes_mass'];
		$instance['assets']['COUNT'] += 1;
	}

	//Storage
	$instance['STORAGEUSED'] = $bCMS->s3StorageUsed($instance['instances_id']);
	//USERS
	$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
	$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
	$DBLIB->where("instances_id",  $instance['instances_id']);
	$DBLIB->where("userInstances.userInstances_deleted",  0);
	$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
	$instance['USERS'] = $DBLIB->getValue("users","COUNT(*)");
	// Activity
	$instance['ACTIVITY'] = [];
	$DBLIB->join("projects", "auditLog.projects_id=projects.projects_id", "LEFT");
	$DBLIB->orderBy("auditLog_timestamp", "DESC");
	$DBLIB->where("projects.instances_id", $instance['instances_id']);
	$DBLIB->where ("auditLog.projects_id", NULL, 'IS NOT');
	$instance['ACTIVITY']['projectAuditLog'] = $DBLIB->getValue("auditLog", "auditLog_timestamp");
	// Other counts
	foreach (['cmsPages', 'maintenanceJobs', 'locations', 'clients', 'modules', 'projects', 'projectsTypes'] as $table) {
		$DBLIB->where("instances_id", $instance['instances_id']);
		$DBLIB->where($table . "_deleted", 0);
		$instance[strtoupper($table)] = $DBLIB->getValue($table, "COUNT(*)");
	}

	// For selecting a billing user
	$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid", "LEFT");
	$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
	$DBLIB->orderBy("users.users_name1", "ASC");
	$DBLIB->where("instances_id", $instance['instances_id']);
	$DBLIB->where("userInstances.userInstances_deleted",  0);
	$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
	$instance['usersForBillingUser'] = $DBLIB->get("users", null, ["users.users_userid", "users.users_name1", "users.users_name2"]);

	$PAGEDATA['instances'][] = $instance;
}

// Server Totals
$DBLIB->where("assets_deleted", 0);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$assetsTotals = $DBLIB->get("assets", null, ["COUNT(assets_id) as count", "SUM(assetTypes_value) as value", "SUM(assetTypes_mass) as mass"]);
$PAGEDATA['totals'] = ["assets" => ["MASS" => $assetsTotals[0]['mass'], "COUNT" => $assetsTotals[0]['count'], "VALUE" => $assetsTotals[0]['value']]];

$DBLIB->where("(s3files_meta_deleteOn IS NULL)");
$DBLIB->where("s3files_meta_physicallyStored", 1);
$PAGEDATA['totals']["STORAGEUSED"] = $DBLIB->getValue("s3files", "SUM(s3files_meta_size)");
$PAGEDATA['totals']["STORAGEALLOWED"] = $DBLIB->getValue("instances", "SUM(instances_storageLimit)");

$PAGEDATA['totals']['users'] = [];
$PAGEDATA['totals']['users']['total'] = $DBLIB->getValue("users", "count(*)");
$DBLIB->where("(SELECT COUNT(*) FROM userInstances WHERE userInstances.users_userid=users.users_userid AND userInstances.userInstances_deleted=0 AND (userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "'))", 1, "<");
$PAGEDATA['totals']['users']['noInstances'] = $DBLIB->getValue("users", "count(*)");

$DBLIB->orderBy("auditLog_timestamp", "DESC");
$PAGEDATA['totals']['lastActivity']['auditLog'] = $DBLIB->getValue("auditLog", "auditLog_timestamp");



echo $TWIG->render('server/instances.twig', $PAGEDATA);
?>
