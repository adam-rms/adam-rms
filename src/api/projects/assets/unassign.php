<?php
require_once __DIR__ . '/../../apiHeadSecure.php';
require_once __DIR__ . '/../../../common/libs/bCMS/projectFinance.php';

use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN") or (!isset($_POST['assetsAssignments']) and !isset($_POST['assets_id']) and !isset($_POST['assetTypes_id']))) die("404");

if (isset($_POST['assets_id']) and isset($_POST['projects_id']) and !isset($_POST['assetsAssignments'])) {
    //Convert for where only the asset id and project is known
    $DBLIB->where("assets_id", $_POST['assets_id']);
    $DBLIB->where("projects_id", $_POST['projects_id']);
    $DBLIB->where("assetsAssignments_deleted", 0);
    $assignment = $DBLIB->getone("assetsAssignments", ["assetsAssignments_id"]);
    if ($assignment) $_POST['assetsAssignments'] = [$assignment['assetsAssignments_id']];
    else finish(false, ["message" => "Could not find assignment"]);
}

if (isset($_POST['assetTypes_id']) and isset($_POST['projects_id']) and !isset($_POST['assetsAssignments'])) {
    //convert for where we only know assetType ID and project
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id");
    $DBLIB->where("assets.assetTypes_id", $_POST['assetTypes_id']);
    $DBLIB->where("assetsAssignments.projects_id", $_POST['projects_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $assignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments_id"]);
    if ($assignments) $_POST['assetsAssignments'] = array_column($assignments, "assetsAssignments_id");
    else finish(false, ["message" => "Could not find Assignment"]);
}

$assignmentsRemove = new assetAssignmentSelector($_POST['assetsAssignments']);
$assignmentsRemove = $assignmentsRemove->getData();
if (!$assignmentsRemove['projectid']) finish(false, ["message" => "Cannot find projectid"]);
$DBLIB->where("projects.projects_id", $assignmentsRemove["projectid"]);
$DBLIB->where("projects.instances_id", $AUTH->data['instance_ids'], 'IN');
$DBLIB->where("projects.projects_deleted", 0);
$project = $DBLIB->getone("projects", ["projects_id", "projects_name"]);
if (!$project) finish(false, ["message" => "Cannot find project"]);

$projectFinanceHelper = new projectFinance();
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_id']);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

$assignmentsIDs = [];
foreach ($assignmentsRemove["assignments"] as $assignment) {
    if (in_array($assignment['assetsAssignments_id'], $assignmentsIDs)) continue; //Prevents removing something twice
    array_push($assignmentsIDs, $assignment['assetsAssignments_id']);
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_deleted" => 1])) finish(false);
    else {
        $bCMS->auditLog("UNASSIGN-ASSET", "assetsAssignments", $assignment['assetsAssignments_id'], $AUTH->data['users_userid'], null, $assignment['projects_id']);
        $projectFinanceCacher->adjust('projectsFinanceCache_mass', ($assignment['assets_mass'] !== null ? $assignment['assets_mass'] : $assignment['assetTypes_mass']), true);
        $projectFinanceCacher->adjust('projectsFinanceCache_value', new Money(($assignment['assets_value'] !== null ? $assignment['assets_value'] : $assignment['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency'])), true);


        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $price = new Money($assignment['assetsAssignments_customPrice'], new Currency($AUTH->data['instance']['instances_config_currency']));
        } else {
            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($assignment['assets_dayRate'] !== null ? $assignment['assets_dayRate'] : $assignment['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $price = $price->add((new Money(($assignment['assets_weekRate'] !== null ? $assignment['assets_weekRate'] : $assignment['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
        }
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price, true);
        if ($assignment['assetsAssignments_discount'] > 0) $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))), true);

        $usersNotified = []; //If user follows multiple groups which this asset is in they'll be notified multiple times otherwise
        foreach (explode(",", $assignment['assets_assetGroups']) as $group) {
            if (is_numeric($group)) {
                foreach ($bCMS->usersWatchingGroup($group) as $user) {
                    if ($user != $AUTH->data['users_userid'] and !in_array($user, $usersNotified)) {
                        array_push($usersNotified, $user);
                        notify(19, $user, $AUTH->data['instance']['instances_id'], "Asset " . $bCMS->aTag($assignment['assets_tag']) . " removed from project", "Asset " . $bCMS->aTag($assignment['assets_tag']) . " (" . $assignment["assetTypes_name"] . ") has been removed from the project " . $project['projects_name'] . " by " . $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2']);
                    }
                }
            }
        }
    }
}
if ($projectFinanceCacher->save()) finish(true);
else finish(false, ["message" => "Finance Cacher Save failed"]);

/** @OA\Post(
 *     path="/projects/assets/unassign.php", 
 *     summary="Unassign Asset", 
 *     description="Unassign an asset from a project  
Requires Instance Permission PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN
", 
 *     operationId="unassignAsset", 
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
 *         name="assetsAssignments",
 *         in="query",
 *         description="Asset Assignment IDs",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assets_id",
 *         in="query",
 *         description="Asset ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
