<?php
require_once __DIR__ . '/../common/head.php';
header('Content-type: application/json');

//To prevent errors showing on the json output
error_reporting(0);
ini_set('display_errors', 0);

function finish($result = false, $error = ["code" => null, "message"=> null], $response = []) {
    $dataReturn = ["result" => $result];
    if ($error) $dataReturn["error"] = $error;
    else $dataReturn["response"] = $response;

    die(json_encode($dataReturn));
}