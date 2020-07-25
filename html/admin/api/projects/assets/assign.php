<?php
require_once __DIR__ . '/../../apiHeadSecure.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck(31)) die("404");

$DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
$DBLIB->where("projects.projects_deleted", 0);
if (isset($_POST['projects_id'])) $DBLIB->where("projects.projects_id", $_POST['projects_id']);
else $DBLIB->where("projects.projects_id", $AUTH->data['users_selectedProjectID']);
$project = $DBLIB->getone("projects", ["projects_id","projects_dates_deliver_start","projects_dates_deliver_end","projects_defaultDiscount","projects_name"]);
if (!$project) finish(false,["message"=>"Project not found"]);

$projectFinanceHelper = new projectFinance();
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);

$assetRequiredFields = ["assetTypes_name","assets_tag","assets_id","assets_dayRate","assets_weekRate","assetTypes_dayRate","assetTypes_weekRate","assetTypes_mass","assetTypes_value","assets_value","assets_mass","assets_assetGroups"];

if (isset($_POST['assets_id'])) $DBLIB->where("assets_id", $_POST['assets_id']);
elseif ($AUTH->instancePermissionCheck(32)) $DBLIB->where("(assets_linkedTo IS NULL)"); //We'll handle linked assets later in the script but for now add all assets
else die("404"); //Can't do an add all
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets_deleted", 0);
$DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$assetIDs = $DBLIB->get("assets", null, $assetRequiredFields);

function linkedAssets($assetId,$linkCount) {
    global $DBLIB,$assetsToProcess,$assetRequiredFields;
    $DBLIB->where("assets_linkedTo", $assetId);
    $DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $assets = $DBLIB->get("assets",null,$assetRequiredFields);
    foreach ($assets as $asset) {
        $asset['linkedto'] = $linkCount;
        $assetsToProcess[] = $asset;
        linkedAssets($asset['assets_id'],(count($assetsToProcess)-1));
    }
}
$assetsToProcess = [];
foreach ($assetIDs as $asset) {
    $asset['linkedto'] = false;
    $assetsToProcess[] = $asset;
    linkedAssets($asset['assets_id'],(count($assetsToProcess)-1));
}
$assetsFailed = [];
$assetsProcessing = [];
foreach ($assetsToProcess as $asset) {
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("(projects.projects_id = '" . $project['projects_id'] . "' OR projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
    $DBLIB->where("((projects_dates_deliver_start >= '" . $project["projects_dates_deliver_start"] . "' AND projects_dates_deliver_start <= '" . $project["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $project["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $project["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $project["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $project["projects_dates_deliver_start"] . "'))");
    $assignment = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id"]);
    $flagsBlocks = assetFlagsAndBlocks($asset['assets_id']);
    if ($assignment or $flagsBlocks['COUNT']['BLOCK']>0) { //Can't assign anything with a block on it
        //It's got a clash so we can't assign it
        if (isset($_POST['assets_id']) and $_POST['assets_id'] == $asset['assets_id']) finish(false,["message"=>"Asset wanted not available"]); //Fail because the one we were supposed to assign hasn't worked
        $assetsFailed[] = ["assets_id" => $asset['assets_id']];
    } else {
        $insertData = [
            "projects_id" => $project['projects_id'],
            "assets_id" => $asset['assets_id'],
            "assetsAssignments_deleted" => 0,
            "assetsAssignments_timestamp" => date('Y-m-d H:i:s'),
            "assetsAssignments_linkedTo" => ($asset['linkedto'] !== false ? $assetsProcessing[$asset['linkedto']]['insertedid'] : null),
            "assetsAssignments_discount" => ($asset['linkedto'] !== false ? $AUTH->data['instance']['instances_config_linkedDefaultDiscount'] : $project['projects_defaultDiscount'])
        ];
        $insert = $DBLIB->insert("assetsAssignments", $insertData);
        if ($insert) {
            //Calculate the maths changes needed for this assignment and add it to the project
            $projectFinanceCacher->adjust('projectsFinanceCache_mass',($asset['assets_mass'] !== null ? $asset['assets_mass'] : $asset['assetTypes_mass']));
            $projectFinanceCacher->adjust('projectsFinanceCache_value',new Money(($asset['assets_value'] !== null ? $asset['assets_value'] : $asset['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency'])));

            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $price = $price->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price,false);

            //Thought a discount can't be set, there might be a default one from the project
            if ($insertData['assetsAssignments_discount'] > 0) $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($insertData['assetsAssignments_discount'] / 100))));

            $asset['insertedid'] = $insert;

            $usersNotified = []; //If user follows multiple groups which this asset is in they'll be notified multiple times otherwise
            foreach (explode(",",$asset['assets_assetGroups']) as $group) {
                if (is_numeric($group)) {
                    foreach ($bCMS->usersWatchingGroup($group) as $user) {
                        if ($user != $AUTH->data['users_userid'] and !in_array($user,$usersNotified)) {
                            array_push($usersNotified,$user);
                            notify(18,$user, $AUTH->data['instance']['instances_id'], "Asset " . $bCMS->aTag($asset['assets_tag']) . " assigned to project", "Asset " . $bCMS->aTag($asset['assets_tag']) . " (" . $asset["assetTypes_name"] . ") has been added to the project " . $project['projects_name'] . " by " . $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2']);
                        }
                    }
                }
            }

            $bCMS->auditLog("ASSIGN-ASSET", "assetsAssignments", $insert, $AUTH->data['users_userid'], null, $project['projects_id']);
        } elseif (isset($_POST['assets_id']) and $_POST['assets_id'] == $asset['assets_id']) finish(false,["message"=>"Cannot insert assignment"]); //Fail because the one we were supposed to assign hasn't worked
        else $assetsFailed[] = $asset;
    }
    $assetsProcessing[] = $asset;
}
if ($projectFinanceCacher->save()) finish(true, null, ["failed" => $assetsFailed]);
else finish(false,["message"=>"Finance Cacher Save failed"]);