<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:INVOICE_NOTES") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_invoiceNotes" => $_POST['projects_invoiceNotes']]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-INVOICENOTES", "projects", "Set the invoice notes to ". $_POST['projects_invoiceNotes'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeInvoiceNotes.php", 
 *     summary="Change Invoice Notes", 
 *     description="Change the invoice notes of a project  
Requires Instance Permission PROJECTS:EDIT:INVOICE_NOTES
", 
 *     operationId="changeInvoiceNotes", 
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
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_invoiceNotes",
 *         in="query",
 *         description="Invoice Notes",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */