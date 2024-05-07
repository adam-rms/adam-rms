<?php
require_once __DIR__ . '/../../apiHeadSecure.php';
require_once __DIR__ . '/../../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance_ids'], 'IN');
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id","projects_dates_deliver_start","projects_dates_deliver_end","projects_defaultDiscount","projects_name"]);
if (!$project) finish(false,["message"=>"Project not found"]);

if ($project["projects_dates_deliver_start"] == null or $project["projects_dates_deliver_end"] == null or (strtotime($project["projects_dates_deliver_start"]) >= strtotime($project["projects_dates_deliver_end"]))) finish(false,["message"=>"Please set the dates for the project before attempting to assign assets"]);

$projectFinanceHelper = new projectFinance();
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_id']);

$assetRequiredFields = ["assetTypes_name","assets_tag","assets_id","assets_dayRate","assets_weekRate","assetTypes_dayRate","assetTypes_weekRate","assetTypes_mass","assetTypes_value","assets_value","assets_mass","assets_assetGroups"];

if (isset($_POST['assetGroups_id'])) {
    $DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
    $DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
    $DBLIB->where("assetGroups_id", $_POST['assetGroups_id']);
    $DBLIB->where("assetGroups_deleted",0);
    $group = $DBLIB->getOne("assetGroups",["assetGroups_id"]);
    if (!$group) finish(false,["message"=>"Group not found"]);

    $DBLIB->where("FIND_IN_SET(" . $group['assetGroups_id'] . ", assets.assets_assetGroups)");
} elseif (isset($_POST['assets_id'])) $DBLIB->where("assets_id", $_POST['assets_id']);
elseif (isset($_POST['assetTypes_id'])) $DBLIB->where("assets.assetTypes_id", $_POST['assetTypes_id']);
elseif ($AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_ALL_BUSINESS_ASSETS")) {
    $DBLIB->where("(assets_linkedTo IS NULL)"); //We'll handle linked assets later in the script but for now add all assets
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
} else finish(false,["message"=>"Cannot add all assets"]);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . $project["projects_dates_deliver_end"] . "')");
$DBLIB->where("assets.instances_id", $AUTH->data['instance_ids'], 'IN');
$DBLIB->where("assets_deleted", 0);
$DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$assetIDs = $DBLIB->get("assets", null, $assetRequiredFields);

function linkedAssets($assetId,$linkCount) {
    global $DBLIB,$assetsToProcess,$assetRequiredFields,$project,$assetsLinked;
    array_push($assetsLinked,$assetId);
    $DBLIB->where("assets.assets_linkedTo", $assetId);
    $DBLIB->where("assets.assets_id", $assetsLinked, "NOT IN"); // Make sure an asset is not double counted
    $DBLIB->where("assets.assets_deleted", 0);
    $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . $project["projects_dates_deliver_end"] . "')");
    $DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $assets = $DBLIB->get("assets",null,$assetRequiredFields);
    foreach ($assets as $asset) {
        $asset['linkedto'] = $linkCount;
        $assetsToProcess[] = $asset;
        linkedAssets($asset['assets_id'],(count($assetsToProcess)-1));
    }
}
$assetsToProcess = [];
$assetsLinked = [];
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
    $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("(projects.projects_id = '" . $project['projects_id'] . "' OR projectsStatuses.projectsStatuses_assetsReleased = 0)");
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

/** @OA\Post(
 *     path="/projects/assets/assign.php", 
 *     summary="Assign Asset to Project", 
 *     description="Assign an asset to a project  
Requires Instance Permission PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN
", 
 *     operationId="assignAssetToProject", 
 *     tags={"project_assets"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assetGroups_id",
 *         in="query",
 *         description="Asset Group ID",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assets_id",
 *         in="query",
 *         description="Asset ID",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */