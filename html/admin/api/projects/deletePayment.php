<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(35) or !isset($_POST['payments_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "payments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("payments.payments_id", $_POST['payments_id']);
$project = $DBLIB->getone("payments", ["payments.payments_id", "payments.projects_id","payments_type","payments_amount"]);
if (!$project) finish(false);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

$DBLIB->where("payments_id", $project['payments_id']);
$update = $DBLIB->update("payments", ["payments_deleted" => 1]);
if (!$update) finish(false);

$projectFinanceCacher->adjustPayment($project['payments_type'],-1*$project['payments_amount']);
$bCMS->auditLog("DELETE", "payments", $_POST['payments_id'], $AUTH->data['users_userid'],null, $project['projects_id']);

if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);