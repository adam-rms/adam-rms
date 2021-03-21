<?php
require_once __DIR__ . '/../apiHead.php';

if (!isset($_POST['term'])) finish(false, ["message"=> "No data for action"]);
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

$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $instanceID . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
$DBLIB->where("manufacturers_name","%" . $_POST['term'] . "%","LIKE");
$manufacturers = $DBLIB->get('manufacturers', null, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);
finish(true, null, $manufacturers);