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

$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->where("assetCategories_deleted",0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $instanceID . "')");
if (isset($_POST['term']) and $_POST['term']) $DBLIB->where("assetCategories_name","%" . $_POST['term'] . "%","LIKE");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$categories = $DBLIB->get('assetCategories');
finish(true, [], $categories);