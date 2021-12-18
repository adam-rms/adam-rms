<?php
require_once __DIR__ . '/head.php';

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array["subject"]) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$data = [
    "name" => $PAGEDATA['USERDATA']['users_name1'] . " " . $PAGEDATA['USERDATA']['users_name2'],
    "email" => $PAGEDATA['USERDATA']['users_email'],
    "source" => 2,
];
$data = array_merge($array,$data);

if ($PAGEDATA['USERDATA']['users_emailVerified'] === 1 and freshdeskSubmit("tickets", $data)) finish(true);
else finish(false);