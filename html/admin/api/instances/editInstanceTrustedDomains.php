<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(133)) die("Sorry - you can't access this page");
$array = [];
if (!isset($_POST['formData'])) die("404");
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    elseif ($item['value'] == "on") $item['value'] = true;

    $array[$item['name']] = $item['value'];
}
$oldData = json_decode($AUTH->data['instance']['instances_trustedDomains'],true);

$array['domains'] = array_filter(explode(",",trim($array['domains'])));

$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$result = $DBLIB->update("instances", ["instances_trustedDomains" => json_encode($array)]);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update instance"]);
else {
    $bCMS->auditLog("EDIT-INSTANCE", "instances", "Trusted Domains - " . json_encode($array), $AUTH->data['users_userid'],null, $AUTH->data['instance']["instances_id"]);
    finish(true);
}
