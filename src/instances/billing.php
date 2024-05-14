<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Billing", "BREADCRUMB" => false];

$instance = [];

//Inventory
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("assets_inserted", "ASC");
$DBLIB->where("assets_deleted", 0);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$assets = $DBLIB->get("assets", null, ["assets_inserted", "assetTypes_value", "assetTypes_mass"]);
$instance['assets'] = ["VALUE" => 0.0, "MASS" => 0.0, "COUNT" => 0];
foreach ($assets as $asset) {
  $instance['assets']['VALUE'] += $asset['assetTypes_value'];
  $instance['assets']['MASS'] += $asset['assetTypes_mass'];
  $instance['assets']['COUNT'] += 1;
  $PAGEDATA['totals']['assets']['VALUE'] += $asset['assetTypes_value'];
  $PAGEDATA['totals']['assets']['MASS'] += $asset['assetTypes_mass'];
  $PAGEDATA['totals']['assets']['COUNT'] += 1;
}

//Storage
$instance['STORAGEUSED'] = $bCMS->s3StorageUsed($AUTH->data['instance']['instances_id']);
$PAGEDATA['totals']['STORAGEUSED'] += $instance['STORAGEUSED'];
$PAGEDATA['totals']['STORAGEALLOWED'] += $instance['instances_storageLimit'];
//USERS
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid", "LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$instance['USERS'] = $DBLIB->getValue("users", "COUNT(*)");
// Activity
$instance['ACTIVITY'] = [];
$DBLIB->join("projects", "auditLog.projects_id=projects.projects_id", "LEFT");
$DBLIB->orderBy("auditLog_timestamp", "DESC");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("auditLog.projects_id", NULL, 'IS NOT');
$instance['ACTIVITY']['projectAuditLog'] = $DBLIB->getValue("auditLog", "auditLog_timestamp");
// Other counts
foreach (['cmsPages', 'maintenanceJobs', 'locations', 'clients', 'modules', 'projects', 'projectsTypes'] as $table) {
  $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
  $DBLIB->where($table . "_deleted", 0);
  $instance[strtoupper($table)] = $DBLIB->getValue($table, "COUNT(*)");
}

$PAGEDATA['stats'] = $instance;


$DBLIB->where("users_userid",  $AUTH->data['instance']['instances_billingUser']);
$PAGEDATA['billingUser'] = $DBLIB->getOne("users", ["users.users_name1", "users.users_name2", "users.users_userid"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$customerId = $DBLIB->getValue("instances", "instances_planStripeCustomerId");
$PAGEDATA['showStripeBillingLink'] = (strlen($CONFIGCLASS->get('STRIPE_KEY')) > 0 and strlen($customerId) > 0) ? true : false;

echo $TWIG->render('instances/instances_billing.twig', $PAGEDATA);
