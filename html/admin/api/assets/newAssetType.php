<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(18)) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['manufacturers_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$array['instances_id'] = $AUTH->data['instance']["instances_id"];
$array['assetTypes_inserted'] = date('Y-m-d H:i:s');

$array['assetTypes_definableFields'] = array();
$array['assetTypes_definableFields'][0] = $array['asset_definableFields_1'];
$array['assetTypes_definableFields'][1] = $array['asset_definableFields_2'];
$array['assetTypes_definableFields'][2] = $array['asset_definableFields_3'];
$array['assetTypes_definableFields'][3] = $array['asset_definableFields_4'];
$array['assetTypes_definableFields'][4] = $array['asset_definableFields_5'];
$array['assetTypes_definableFields'][5] = $array['asset_definableFields_6'];
$array['assetTypes_definableFields'][6] = $array['asset_definableFields_7'];
$array['assetTypes_definableFields'][7] = $array['asset_definableFields_8'];
$array['assetTypes_definableFields'][8] = $array['asset_definableFields_9'];
$array['assetTypes_definableFields'][9] = $array['asset_definableFields_10'];
$array['assetTypes_definableFields'] = implode(",", $array['assetTypes_definableFields']);

use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$array['assetTypes_value'] = $moneyParser->parse($array['assetTypes_value'], $AUTH->data['instance']['instances_config_currency'])->getAmount();
$array['assetTypes_dayRate'] = ($array['assetTypes_dayRate'] != null ? $moneyParser->parse($array['assetTypes_dayRate'], $AUTH->data['instance']['instances_config_currency'])->getAmount() : 0);
$array['assetTypes_weekRate'] = ($array['assetTypes_weekRate'] != null ? $moneyParser->parse($array['assetTypes_weekRate'], $AUTH->data['instance']['instances_config_currency'])->getAmount() : 0);

$result = $DBLIB->insert("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_name','assetTypes_productLink','assetCategories_id','manufacturers_id','assetTypes_description','assetTypes_definableFields','assetTypes_mass','assetTypes_inserted',"instances_id","assetTypes_dayRate","assetTypes_weekRate","assetTypes_value"] ) ));
if (!$result) finish(false, ["code" => "INSERT-FAIL", "message"=> "Could not insert asset type"]);
else finish(true, null, ["assetTypes_id" => $result]);