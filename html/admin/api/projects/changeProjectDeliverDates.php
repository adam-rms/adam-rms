<?php
require_once __DIR__ . '/../apiHeadSecure.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck(27) or !isset($_POST['projects_id'])) die("404");
$newDates = ["projects_dates_deliver_start" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_deliver_start'])), "projects_dates_deliver_end" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_deliver_end']))];

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false);

$projectFinanceHelper = new projectFinance();
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);
$priceMathsOld = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);
$priceMathsNew = $projectFinanceHelper->durationMaths($newDates['projects_dates_deliver_start'],$newDates['projects_dates_deliver_end']);

//We're changing dates so we need to find clashes in the new dates
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("assetsAssignments.projects_id", $project['projects_id']);
$DBLIB->join("assets","assetsAssignments.assets_id=assets.assets_id", "LEFT");
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id", "assetsAssignments.assetsAssignments_id","assetsAssignments_customPrice","assetsAssignments_discount","assetTypes_weekRate","assetTypes_dayRate","assets_dayRate","assets_weekRate"]);
if ($assets) {
    $unavailableAssets = [];
    foreach ($assets as $asset) {
        $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
        $DBLIB->join("assets","assetsAssignments.assets_id=assets.assets_id", "LEFT");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
        $DBLIB->where("assetsAssignments.assets_id", $asset['assets_id']);
        $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->where("projectsStatuses.projectsStatuses_assetsAvailable", 0);
        $DBLIB->where("(projects.projects_id != " .  $project['projects_id'] . ")"); //It might be there's a slight overlap with this project so avoid finding that
        $DBLIB->where("((projects_dates_deliver_start >= '" . $newDates["projects_dates_deliver_start"]  . "' AND projects_dates_deliver_start <= '" . $newDates["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $newDates["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $newDates["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $newDates["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $newDates["projects_dates_deliver_start"] . "'))");
        $assignment = $DBLIB->getone("assetsAssignments", null, ["assetsAssignments.assetsAssignments_id", "assetsAssignments.assets_id","assetsAssignments.projects_id", "assetTypes.assetTypes_name", "projects.projects_name", "assets.assets_tag"]);
        if ($assignment) {
            $assignment['old_assetsAssignments_id'] = $asset['assetsAssignments_id'];
            $unavailableAssets[] = $assignment;
        }
    }
    if (count($unavailableAssets) > 0) {
        finish(true, null, ["changed" => false, "assets" => $unavailableAssets]);
    } else {
        foreach ($assets as $asset) {
            //This change is going to go ahead so re-calculate finance
            if ($asset['assetsAssignments_customPrice'] != null) continue; //There is a custom price set - so this asset is date agnostic anyway

            $priceOriginal = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $priceOriginal = $priceOriginal->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsOld['days']));
            $priceOriginal = $priceOriginal->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsOld['weeks']));
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $priceOriginal,true);

            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsNew['days']));
            $price = $price->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsNew['weeks']));
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price,false);

            if ($asset['assetsAssignments_discount'] > 0) {
                //Remove old discount
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $priceOriginal->subtract($priceOriginal->multiply(1 - ($asset['assetsAssignments_discount'] / 100))),true);
                //Set a new discount
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), false);
            }
        }
    }
}

if ($projectFinanceCacher->save()) {
    $DBLIB->where("projects.projects_id", $project['projects_id']);
    $projectUpdate = $DBLIB->update("projects", $newDates);
    if (!$projectUpdate) finish(false);
    $bCMS->auditLog("CHANGE-DATE", "projects", "Set the deliver start date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_deliver_start'])) . "\nSet the deliver end date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_deliver_end'])), $AUTH->data['users_userid'],null, $_POST['projects_id']);
    finish(true, null, ["changed" => true]);
} else finish(false, ["message"=>"Cannot modify finances to change dates"]);