<?php
require_once __DIR__ . '/../apiHeadSecure.php';
use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
if (!$AUTH->instancePermissionCheck(58)) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetTypes_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$array['assetTypes_value'] = $moneyParser->parse(($array['assetTypes_value'] ?? "0.00"), $AUTH->data['instance']['instances_config_currency'])->getAmount();
$array['assetTypes_dayRate'] = $moneyParser->parse(($array['assetTypes_dayRate'] ?? '0.00'), $AUTH->data['instance']['instances_config_currency'])->getAmount();
$array['assetTypes_weekRate'] = $moneyParser->parse(($array['assetTypes_weekRate'] ?? '0.00'), $AUTH->data['instance']['instances_config_currency'])->getAmount();

if (!$AUTH->serverPermissionCheck("ASSETS:EDIT:ANY_ASSET_TYPE")) {
    $DBLIB->where("(instances_id IS NOT NULL)");
    $DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
}
$DBLIB->where("assetTypes_id", $array['assetTypes_id']);
$assetType = $DBLIB->getone("assetTypes", ['assetTypes.assetTypes_mass','assetTypes.assetTypes_value',"assetTypes.assetTypes_dayRate","assetTypes.assetTypes_weekRate"]);
if (!$assetType) finish(false);

$DBLIB->where("assetTypes_id",$array['assetTypes_id']);
$result = $DBLIB->update("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_name','assetCategories_id','assetTypes_productLink','manufacturers_id','assetTypes_description','assetTypes_definableFields','assetTypes_mass','assetTypes_inserted',"assetTypes_dayRate","assetTypes_weekRate","assetTypes_value"] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update asset type"]);
else {
    $bCMS->auditLog("EDIT-ASSET-TYPE", "assetTypes", json_encode($array), $AUTH->data['users_userid'],null, $array['assetTypes_id']);

    $DBLIB->where("assets.assetTypes_id", $array['assetTypes_id']);
    $DBLIB->where("assets.instances_id",$AUTH->data['instance']["instances_id"]);
    $assets = $DBLIB->get("assets", null,['assets.assets_id','assets.assets_dayRate','assets.assets_weekRate','assets.assets_mass','assets.assets_value']);
    foreach ($assets as $asset) {
        $DBLIB->where("assets_id",$asset['assets_id']);
        $DBLIB->where("assetsAssignments_deleted",0);
        $DBLIB->join("projects","assetsAssignments.projects_id=projects.projects_id","LEFT");
        $assetAssignments = $DBLIB->get("assetsAssignments",null,['projects.projects_id','projects_dates_deliver_start','assetsAssignments_id','projects_dates_deliver_end','assetsAssignments_customPrice','assetsAssignments_discount']);
        foreach ($assetAssignments as $assignment) {
            $projectFinanceHelper = new projectFinance();
            $priceMaths = $projectFinanceHelper->durationMaths($assignment['projects_dates_deliver_start'],$assignment['projects_dates_deliver_end']);
            $projectFinanceCacher = new projectFinanceCacher($assignment['projects_id']);
            //Remove current mass and value
            if ($asset['assets_mass'] == null) {
                $projectFinanceCacher->adjust('projectsFinanceCache_mass',$assetType['assetTypes_mass'],true);
                $projectFinanceCacher->adjust('projectsFinanceCache_mass',$array['assetTypes_mass'],false);
            }
            if ($asset['assets_value'] == null) {
                $projectFinanceCacher->adjust('projectsFinanceCache_value',new Money($assetType['assetTypes_value'], new Currency($AUTH->data['instance']['instances_config_currency'])),true);
                $projectFinanceCacher->adjust('projectsFinanceCache_value',new Money($array['assetTypes_value'], new Currency($AUTH->data['instance']['instances_config_currency'])),false);
            }

            if ($assignment['assetsAssignments_customPrice'] > 0) {
                //Old price stands so ignore it
            } else {
                $oldPrice = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
                $oldPrice = $oldPrice->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $assetType['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
                $oldPrice = $oldPrice->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $assetType['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
                //Price is now manually calculated
                $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
                $price = $price->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $array['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
                $price = $price->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $array['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));

                //Remove the old price
                $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $oldPrice,true);
                $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price,false);

                if ($assignment['assetsAssignments_discount'] > 0) {
                    //If there was already a discount, remove it, then add it again
                    $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $oldPrice->subtract($oldPrice->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))),true);
                    $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))), false);
                }
            }
            $projectFinanceCacher->save();
        }
    }
    finish(true);
}