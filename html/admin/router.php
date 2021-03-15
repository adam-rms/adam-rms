<?php
$lambdaContext = isset($_SERVER['LAMBDA_INVOCATION_CONTEXT']) ? json_decode($_SERVER['LAMBDA_INVOCATION_CONTEXT'], true) : [];
$requestContext = isset($_SERVER['LAMBDA_REQUEST_CONTEXT']) ? json_decode($_SERVER['LAMBDA_REQUEST_CONTEXT'], true) : [];
if (isset($requestContext['path'])) {
    $path = str_replace("/prod","/", $requestContext['path']);
} else $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

if ($path == "/") $includeFile = __DIR__ . DIRECTORY_SEPARATOR . "index.php";
elseif (is_dir ( __DIR__ . $path) and file_exists ( __DIR__ . $path . "index.php")) $includeFile = __DIR__ . $path . "index.php";
elseif (is_dir ( __DIR__ . $path. DIRECTORY_SEPARATOR) and file_exists ( __DIR__ . $path . DIRECTORY_SEPARATOR . "index.php")) $includeFile = __DIR__ . $path . DIRECTORY_SEPARATOR . "index.php";
elseif (file_exists ( __DIR__ . $path)) $includeFile = __DIR__ . $path;
else {
    http_response_code(404);
    $includeFile = __DIR__ . DIRECTORY_SEPARATOR . "404.html";
}

if (file_exists($includeFile)&&(strpos(realpath($includeFile),realpath(__DIR__))===0)) {
    require $includeFile;
} else die("404 Error");

?>