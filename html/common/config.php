<?php
/*
 * This page must not be edited for each install as it's required by the updater
 *
 * Any settings must go in the environment variables
 */

if(file_exists(__DIR__ . '/../../keys.php')) include_once (__DIR__ . '/../../keys.php');
if (getenv('bCMS__ERRORS') == "true") {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
require_once(__DIR__ . '/../../composer/vendor/autoload.php'); //Composer
require_once(__DIR__ . '/libs/Auth/main.php');
require_once(__DIR__ . '/libs/Email/main.php');
$CONFIG = array(
    'DB_HOSTNAME' => getenv('bCMS__DB_HOSTNAME'),
    'DB_DATABASE' => getenv('bCMS__DB_DATABASE'),
    'DB_USERNAME' => getenv('bCMS__DB_USERNAME'), //CREATE INSERT SELECT UPDATE DELETE
    'DB_PASSWORD' => getenv('bCMS__DB_PASSWORD'),
    'PROJECT_NAME' => getenv('bCMS__SITENAME'),
    'SENDGRID' => ['APIKEY' => getenv('bCMS__SendGridAPIKEY')],
    //'ERRORS' => ['SENTRY' => getenv('bCMS__SENTRYLOGIN'), "SENTRYPublic" => getenv('bCMS__SENTRYLOGINPUBLIC'), 'URL' => 'https://google.com'],
    'ANALYTICS' => ['TRACKINGID' => getenv('bCMS__GoogleAnalytics')],
    "nextHash" => "sha256", //Hashing algorithm to put new passwords in
    "PROJECT_FROM_EMAIL" => getenv('bCMS__EMAIL'),
    "ROOTURL" => "", //Set on a frontend/backend basis
    "PROJECT_SUPPORT_EMAIL" => getenv('bCMS__SUPPORTEMAIL'),
    'AWS' => [
        'KEY' => getenv('bCMS__AWS_SERVER_KEY'),
        'SECRET' => getenv('bCMS__AWS_SERVER_SECRET_KEY'),
        'DEFAULTUPLOADS' => [
            'BUCKET' => getenv('bCMS__AWS_S3_BUCKET_NAME'),
            'ENDPOINT' => getenv('bCMS__AWS_S3_BUCKET_ENDPOINT'),
            'REGION' => getenv('bCMS__AWS_S3_BUCKET_REGION'),
        ],
        "FINEUPLOADER" => [
            "KEY" => getenv('bCMS__AWS_CLIENT_KEY'),
            "SECRET" => getenv('bCMS__AWS_CLIENT_SECRET_KEY')
        ]
    ],
    'DEV' => (getenv('bCMS__ERRORS') == "true" ? true : false),
);
date_default_timezone_set("UTC");
