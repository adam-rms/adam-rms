<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_PAYMENTS:DELETE") or !isset($_POST['payments_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "payments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("payments.payments_id", $_POST['payments_id']);
$project = $DBLIB->getone("payments", ["payments.payments_id", "payments.projects_id","payments_type","payments_amount","payments_quantity"]);
if (!$project) finish(false);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

$DBLIB->where("payments_id", $project['payments_id']);
$update = $DBLIB->update("payments", ["payments_deleted" => 1]);
if (!$update) finish(false);


$paymentAmount = new Money($project['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
$paymentAmount= $paymentAmount->multiply($project['payments_quantity']);
$projectFinanceCacher->adjustPayment($project['payments_type'],$paymentAmount,true);
$bCMS->auditLog("DELETE", "payments", $_POST['payments_id'], $AUTH->data['users_userid'],null, $project['projects_id']);

if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);

/** @OA\Post(
 *     path="/projects/deletePayment.php", 
 *     summary="Delete Payment", 
 *     description="Delete a payment  
Requires Instance Permission PROJECTS:PROJECT_PAYMENTS:DELETE
", 
 *     operationId="deletePayment", 
 *     tags={"projects"}, 
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
 *         name="payments_id",
 *         in="query",
 *         description="Payment ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */