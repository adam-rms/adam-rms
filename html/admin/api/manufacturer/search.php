<?php
require_once __DIR__ . '/../apiHead.php';

if (isset($_POST['instance_id'])) {
    $DBLIB->where("instances_id", $_POST['instance_id']);
    $DBLIB->where("instances_deleted",0);
    $instance = $DBLIB->getone("instances",['instances_publicConfig','instances_id']);
    if (!$instance) finish(false);
    $instance['instances_publicConfig'] = json_decode($instance['instances_publicConfig'],true);
    if (!$instance['instances_publicConfig']['enableAssets'] and !$AUTH->login) finish(false);
    else $instanceID = $instance['instances_id'];
} elseif ($AUTH->login) $instanceID = $AUTH->data['instance']["instances_id"];
else finish (false);

$assetManufacturers= $DBLIB->subQuery();
$assetManufacturers->where("assets.instances_id",$instanceID);
$assetManufacturers->where("assets_deleted",0);
$assetManufacturers->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
$assetManufacturers->groupBy ("assetTypes.manufacturers_id");
$assetManufacturers->get ("assets", null, "manufacturers_id");
$DBLIB->where("manufacturers_id", $assetManufacturers, "IN");
$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $instanceID . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
if (isset($_POST['term']) and $_POST['term']) $DBLIB->where("manufacturers_name","%" . $_POST['term'] . "%","LIKE");
$manufacturers = $DBLIB->get('manufacturers', null, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);
finish(true, null, $manufacturers);