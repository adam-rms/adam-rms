<?php
require_once __DIR__ . '/../common/headSecure.php';
use Money\Currency;
use Money\Money;

$PAGEDATA['pageConfig'] = ["TITLE" => "Groups", "BREADCRUMB" => false];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;


$DBLIB->orderBy("users_userid", "DESC");
$DBLIB->orderBy("assetGroups_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		assetGroups_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		assetGroups_description LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
    )");
}
$DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$DBLIB->where("assetGroups_deleted",0);
$groups = $DBLIB->get('assetGroups');
$PAGEDATA['groups'] = [];
foreach($groups as $group) {
    $DBLIB->where("FIND_IN_SET(" . $group['assetGroups_id'] . ", assets.assets_assetGroups)");
    $DBLIB->where("assets_deleted",0);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $group['assets'] = $DBLIB->get("assets", null, ["assets_id","assets_tag","assetTypes_name","manufacturers_name"]);

    $PAGEDATA['groups'][] = $group;
}
$PAGEDATA['watching'] = $AUTH->data['users_assetGroupsWatching'];
$PAGEDATA['watching'] = explode(",",$PAGEDATA['watching']);

echo $TWIG->render('instances/groups.twig', $PAGEDATA);
?>
