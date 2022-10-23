<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$instanceID = $AUTH->data['instance']["instances_id"];


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
$manufacturers = $DBLIB->get('manufacturers', 15, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);
finish(true, null, $manufacturers);