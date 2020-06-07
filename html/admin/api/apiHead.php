<?php
header('Content-type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

//Copy the payload over to get&post to maintain compatibility between the app and the frontend
$dataPayload = json_decode(file_get_contents('php://input'));
$dataPayload = (array) $dataPayload;
foreach ($dataPayload as $key=>$item) {
    if (is_array($item) or is_object($item)) continue; //Do this for simple values only for now
    $_GET[$key] = $item;
    $_POST[$key] = $item;
}

require_once __DIR__ . '/../common/head.php';
//To prevent errors showing on the json output
error_reporting(0);
ini_set('display_errors', 0);

//Finish function
function finish($result = false, $error = ["code" => null, "message"=> null], $response = []) {
    $dataReturn = ["result" => $result];
    if ($error) $dataReturn["error"] = $error;
    else $dataReturn["response"] = $response;

    die(json_encode($dataReturn));
}