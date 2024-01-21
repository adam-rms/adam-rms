<?php

// Allow this PHP script to run via CLI only.
if ( 'cli' !== php_sapi_name() ) {
    echo '404';
	exit;
}

require("vendor/autoload.php");

$finder = \Symfony\Component\Finder\Finder::create()->files()->name('*.php')->in(__DIR__ . '/../../');

$openapi = \OpenApi\Generator::scan($finder, ['logger' => new \Psr\Log\NullLogger()]);

echo $openapi->toYaml();