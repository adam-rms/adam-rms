<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$instanceID = $AUTH->data['instance']["instances_id"];

$assetCategories= $DBLIB->subQuery();
$assetCategories->where("assets.instances_id",$instanceID);
$assetCategories->where("assets_deleted",0);
$assetCategories->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
$assetCategories->groupBy ("assetTypes.assetCategories_id");
$assetCategories->get ("assets", null, "assetCategories_id");
$DBLIB->where("assetCategories_id", $assetCategories, "IN");
$DBLIB->orderBy("assetCategoriesGroups.assetCategoriesGroups_order", "ASC");
$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->where("assetCategories_deleted",0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $instanceID . "')");
if (isset($_POST['term']) and $_POST['term']) $DBLIB->where("assetCategories_name","%" . $_POST['term'] . "%","LIKE");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$categories = $DBLIB->get('assetCategories');
finish(true, [], $categories);