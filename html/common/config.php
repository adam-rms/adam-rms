<?php
/*
 * This page must not be edited for each install as it's required by the updater
 *
 * Any settings must go in the environment variables
 */
require_once(__DIR__ . '/../../composer/vendor/autoload.php'); //Composer
if(file_exists(__DIR__ . '/../../.env')) {
    //Load local env viles
    $dotEnvLib = Dotenv\Dotenv::createMutable(__DIR__. '/../../');
    $dotEnvLib->load();
}

if ($_ENV['bCMS__ERRORS'] == "true") {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
require_once(__DIR__ . '/libs/Auth/main.php');
require_once(__DIR__ . '/libs/Email/main.php');
$CONFIG = array(
    'DB_HOSTNAME' => $_ENV['bCMS__DB_HOSTNAME'],
    'DB_DATABASE' => $_ENV['bCMS__DB_DATABASE'],
    'DB_USERNAME' => $_ENV['bCMS__DB_USERNAME'], //CREATE INSERT SELECT UPDATE DELETE
    'DB_PASSWORD' => $_ENV['bCMS__DB_PASSWORD'],
    'PROJECT_NAME' => $_ENV['bCMS__SITENAME'],
    'SENDGRID' => ['APIKEY' => $_ENV['bCMS__SendGridAPIKEY']],
    //'ERRORS' => ['SENTRY' => $_ENV['bCMS__SENTRYLOGIN'], "SENTRYPublic" => $_ENV['bCMS__SENTRYLOGINPUBLIC'], 'URL' => 'https://google.com'],
    'ANALYTICS' => ['TRACKINGID' => $_ENV['bCMS__GoogleAnalytics']],
    "nextHash" => "sha256", //Hashing algorithm to put new passwords in
    "PROJECT_FROM_EMAIL" => $_ENV['bCMS__EMAIL'],
    "ROOTURL" => "", //Set on a frontend/backend basis
    "PROJECT_SUPPORT_EMAIL" => $_ENV['bCMS__SUPPORTEMAIL'],
    'AWS' => [
        'KEY' => $_ENV['bCMS__AWS_SERVER_KEY'],
        'SECRET' => $_ENV['bCMS__AWS_SERVER_SECRET_KEY'],
        'DEFAULTUPLOADS' => [
            'BUCKET' => $_ENV['bCMS__AWS_S3_BUCKET_NAME'],
            'ENDPOINT' => $_ENV['bCMS__AWS_S3_BUCKET_ENDPOINT'],
            'REGION' => $_ENV['bCMS__AWS_S3_BUCKET_REGION'],
        ],
        "FINEUPLOADER" => [
            "KEY" => $_ENV['bCMS__AWS_CLIENT_KEY'],
            "SECRET" => $_ENV['bCMS__AWS_CLIENT_SECRET_KEY']
        ]
    ],
    'DEV' => ($_ENV['bCMS__ERRORS'] == "true" ? true : false),
);
date_default_timezone_set("UTC");