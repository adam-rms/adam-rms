<?php
require_once __DIR__ . '/../apiHeadSecure.php';
use Money\Currency;
use Money\Money;
if (!$AUTH->instancePermissionCheck(34)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['projects_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['payments_date'] = date("Y-m-d H:i:s", strtotime($array['payments_date']));
if (!$array['payments_quantity']) $array['payments_quantity'] = 1;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$array['payments_amount'] = $moneyParser->parse($array['payments_amount'], $AUTH->data['instance']['instances_config_currency'])->getAmount();



$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $array['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id"]);
if (!$project) finish(false);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

$insert = $DBLIB->insert("payments", $array);
if (!$insert) finish(false);

$paymentAmount = new Money($array['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
$paymentAmount = $paymentAmount->multiply($array['payments_quantity']);
$projectFinanceCacher->adjustPayment($array['payments_type'],$paymentAmount,false);

$bCMS->auditLog("INSERT", "payments", $insert, $AUTH->data['users_userid'],null, $project['projects_id']);

if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);