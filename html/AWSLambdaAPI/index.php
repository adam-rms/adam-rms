<?php
$lambdaContext = json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'], true);
$requestContext = json_decode($_SERVER['LAMBDA_REQUEST_CONTEXT'], true);
if ($requestContext['path']) {
    $path = str_replace("/prod","admin", $requestContext['path']);
    if (file_exists($path)) {
        include $path;
        exit;
    } elseif (file_exists($path . "/index.php")) {
        include $path . "/index.php";
        exit;
    } elseif (file_exists($path . "index.php")) {
        include $path . "index.php";
        exit;
    } else die ("4042");
} else die("404");
?>