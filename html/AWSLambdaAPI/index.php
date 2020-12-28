<?php
$lambdaContext = json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'], true);
$requestContext = json_decode($_SERVER['LAMBDA_REQUEST_CONTEXT'], true);
if ($requestContext['path']) {
    $path = str_replace("/prod","/", $requestContext['path']);
} else $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$endpoints = [
    [
        "url" => "/info",
        "file" => "endpoints/misc/info.php",
        "secure" => false
    ]
];

foreach ($endpoints as $endpoint) {
    if ($path == $endpoint['url']) {
        if ($endpoint['secure']) $SECURE = true;
        require_once 'lambdaHead.php';
        require $endpoint['file'];
        exit;
    }
}

http_response_code(404);
include "404.html";
exit;
?>