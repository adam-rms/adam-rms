<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(34)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['projects_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['payments_date'] = date("Y-m-d H:i:s", strtotime($array['payments_date']));

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $array['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id"]);
if (!$project) finish(false);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

$insert = $DBLIB->insert("payments", $array);
if (!$insert) finish(false);

$projectFinanceCacher->adjustPayment($array['payments_type'],$array['payments_amount']);
$bCMS->auditLog("INSERT", "payments", $insert, $AUTH->data['users_userid'],null, $project['projects_id']);

if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);