<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(43) or !isset($_POST['assetsAssignments'])) die("404");
use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;

$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$_POST['assetsAssignments_customPrice'] = $moneyParser->parse($_POST['assetsAssignments_customPrice'], $AUTH->data['instance']['instances_config_currency'])->getAmount();

$assignmentsSetDiscount = new assetAssignmentSelector($_POST['assetsAssignments']);
$assignmentsSetDiscount = $assignmentsSetDiscount->getData();
if (!$assignmentsSetDiscount['projectid']) finish(false,["message"=>"Cannot find projectid"]);
$DBLIB->where("projects.projects_id", $assignmentsSetDiscount["projectid"]);
$DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
$DBLIB->where("projects.projects_deleted", 0);
$project = $DBLIB->getone("projects",["projects_id","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false,["message"=>"Cannot find project"]);

$projectFinanceHelper = new projectFinance();
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

foreach ($assignmentsSetDiscount["assignments"] as $assignment) {
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_customPrice" => $_POST['assetsAssignments_customPrice']])) finish(false);
    else {
        $bCMS->auditLog("EDIT-DISCOUNT", "assetsAssignments", $assignment['assetsAssignments_customPrice'], $AUTH->data['users_userid'],null, $assignment['projects_id']);

        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $oldPrice = new Money($assignment['assetsAssignments_customPrice'], new Currency($AUTH->data['instance']['instances_config_currency']));
        } else {
            $oldPrice = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $oldPrice = $oldPrice->add((new Money(($assignment['assets_dayRate'] !== null ? $assignment['assets_dayRate'] : $assignment['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $oldPrice = $oldPrice->add((new Money(($assignment['assets_weekRate'] !== null ? $assignment['assets_weekRate'] : $assignment['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
        }
        //Remove the old price
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $oldPrice,true);

        if ($_POST['assetsAssignments_customPrice'] != null) {
            $price = new Money($_POST['assetsAssignments_customPrice'], new Currency($AUTH->data['instance']['instances_config_currency']));
        } else {
            //Price is now manually calculated
            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($assignment['assets_dayRate'] !== null ? $assignment['assets_dayRate'] : $assignment['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $price = $price->add((new Money(($assignment['assets_weekRate'] !== null ? $assignment['assets_weekRate'] : $assignment['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
        }
        //Add the new price
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price, false);

        if ($assignment['assetsAssignments_discount'] > 0) {
            //If there was already a discount, remove it, then add it again
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $oldPrice->subtract($oldPrice->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))),true);
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))), false);
        }
    }
}
if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);