<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT")) die("404");

$array = [];
$array['cmsPages_visibleToGroups'] = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;

    if ($item['name'] == 'cmsPages_visibleToGroups') array_push($array['cmsPages_visibleToGroups'],$item['value']);
    else $array[$item['name']] = $item['value'];
}

if ($array['cmsPages_visibleToGroups'] == []) $array['cmsPages_visibleToGroups'] = null;
else $array['cmsPages_visibleToGroups'] = implode(",",$array['cmsPages_visibleToGroups']);

$checkboxes = ['cmsPages_showNav'];
foreach ($checkboxes as $checkbox) {
    if (isset($array[$checkbox]) and $array[$checkbox] == "on") $array[$checkbox] = 1;
    else $array[$checkbox] = 0;
}

if (strlen($array['cmsPages_id']) <1 and $array['cmsPages_id'] != "NEW") finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

if ($array['cmsPages_id'] == "NEW") {
    $array['cmsPages_id'] = null;
    $array['cmsPages_added'] = date("Y-m-d H:i:s");
    $array['instances_id'] = $AUTH->data['instance']['instances_id'];
    $insert = $DBLIB->insert("cmsPages", $array);
    if (!$insert) finish(false);
    $bCMS->auditLog("INSERT", "cmsPages", json_encode($array), $AUTH->data['users_userid'],null,null,$insert);
    finish(true);
} else {
    unset($array['instances_id']); //Don't allow them to change instance for a page
    $DBLIB->where("cmsPages_deleted", 0);
    $DBLIB->where("cmsPages_id",$array['cmsPages_id']);
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    $update = $DBLIB->update("cmsPages", $array,1);
    if (!$update) finish(false);

    $bCMS->auditLog("UPDATE", "cmsPages_id", json_encode($array), $AUTH->data['users_userid'],null,null,$array['cmsPages_id']);
    finish(true);
}

/** @OA\Post(
 *     path="/cms/editPageConfig.php", 
 *     summary="Edit CMS Page Config", 
 *     description="Edit a page config  
Requires Instance Permission CMS:CMS_PAGES:EDIT", 
 *     operationId="editPageConfig", 
 *     tags={"cms"}, 
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
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="A null Array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Error",
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The page config data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             ),
 *     ), 
 * )
 */