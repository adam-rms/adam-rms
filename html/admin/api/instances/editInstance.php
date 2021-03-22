<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(83)) die("Sorry - you can't access this page");
$array = [];
if (!isset($_POST['formData'])) die("404");
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}

$array['instances_termsAndPayment'] = $bCMS->cleanString($array['instances_termsAndPayment']);

$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$result = $DBLIB->update("instances", array_intersect_key( $array, array_flip( ["instances_name","instances_address","instances_phone","instances_email","instances_website","instances_weekStartDates","instances_logo","instances_emailHeader","instances_termsAndPayment","instances_cableColours"] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update instance"]);
else {
    $bCMS->auditLog("EDIT-INSTANCE", "instances", json_encode($array), $AUTH->data['users_userid'],null, $AUTH->data['instance']["instances_id"]);
    finish(true);
}
