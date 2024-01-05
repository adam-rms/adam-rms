<?php
/*
 * This page must not be edited for each install as it's required by the updater
 *
 * Any settings must go in the environment variables
 */
require_once(__DIR__ . '/../../vendor/autoload.php'); //Composer
if(file_exists(__DIR__ . '/../../.env')) {
    //Load local env viles
    $dotEnvLib = Dotenv\Dotenv::createMutable(__DIR__. '/../../');
    $dotEnvLib->load();
}

if (getenv('bCMS__ERRORS') == "true") {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ERROR | E_PARSE);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

$CONFIG = array(
    'DB_HOSTNAME' => getenv('bCMS__DB_HOSTNAME'),
    'DB_DATABASE' => getenv('bCMS__DB_DATABASE'),
    'DB_USERNAME' => getenv('bCMS__DB_USERNAME'), //CREATE INSERT SELECT UPDATE DELETE
    'DB_PASSWORD' => getenv('bCMS__DB_PASSWORD'),
    'DB_PORT' => getenv('bCMS__DB_PORT') ?: 3306,
    'PROJECT_NAME' => "AdamRMS",
    'SENDGRID' => ['APIKEY' => getenv('bCMS__SendGridAPIKEY')],
    'ERRORS' => ['SENTRY' => getenv('bCMS__SENTRYLOGIN'), "SENTRYPublic" => getenv('bCMS__SENTRYLOGINPUBLIC')],
    'VERSION' => ['ENV' => getenv('bCMS__VERSION') ? (strlen(getenv('bCMS__VERSION')) > 7 ? substr(getenv('bCMS__VERSION'), 0, 7) : getenv('bCMS__VERSION')) : false, 'COMMIT' => file_get_contents (__DIR__ . '/version/COMMIT.txt'), 'TAG' => file_get_contents (__DIR__ . '/version/TAG.txt'), "COMMITFULL" => file_get_contents (__DIR__ . '/version/COMMITFULL.txt')], //Version number is the first 7 characters of the commit hash for certain deployments, and for others there's a nice numerical tag.
    "nextHash" => "sha256", //Hashing algorithm to put new passwords in
    "PROJECT_FROM_EMAIL" => getenv('bCMS__FROM_EMAIL'),
    "USERGUIDEURL" => "https://adam-rms.com/docs/v1/user-guide/",
    "ROOTURL" => getenv('bCMS__ROOTURL'),
    "TermsOfServiceURL" => getenv('bCMS__TOS_URL'),
    'AWS' => [
        'KEY' => getenv('bCMS__AWS_SERVER_KEY'),
        'SECRET' => getenv('bCMS__AWS_SERVER_SECRET_KEY'),
        'DEFAULTUPLOADS' => [
            'BUCKET' => getenv('bCMS__AWS_S3_BUCKET_NAME'),
            'ENDPOINT' => getenv('bCMS__AWS_S3_BUCKET_ENDPOINT'),
            'REGION' => getenv('bCMS__AWS_S3_BUCKET_REGION'),
            'CDNEndpoint' => getenv('bCMS__AWS_S3_CDN'),
        ],
        "CLOUDFRONT" => [
            "ENABLED" => getenv('bCMS__AWS_ACCOUNT_CLOUDFRONT_ENABLED') == "TRUE",
            "PRIVATEKEY" => str_replace('\n',"\n", str_replace('"', '', getenv('bCMS__AWS_ACCOUNT_PRIVATE_KEY'))),
            "KEYPAIRID" => getenv('bCMS__AWS_ACCOUNT_PRIVATE_KEY_ID')
        ]
    ],
    'DEV' => (getenv('bCMS__ERRORS') == "true" ? true : false),
    'JWTKey' => getenv('bCMS__JWT'),
    'AUTH-PROVIDERS' => [
        "GOOGLE" => [
            'keys' => [
                'id' => getenv('bCMS__OAUTH__GOOGLEKEY'),
                'secret' => getenv('bCMS__OAUTH__GOOGLESECRET')
            ],
            'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'
        ]
    ],
    'FRESHDESK' => [
        'URL' => getenv('bCMS__FRESHDESK_URL'),
        'APIKEY' => getenv('bCMS__FRESHDESK')
    ],
    'NOTIFICATIONS' => [
        "METHODS" => [
            0 => "Post",
            1 => "EMail",
            2 => "SMS",
            3 => "Mobile Push",
            4 => "Slack",
        ],
        "TYPES" =>  [ //These need to be inorder and inorder of group
            [
                "id" => 1,
                "group" => "Account",
                "name" => "Password Reset",
                "methods" => [1],
                "default" => true,
                "canDisable" => false
            ],
            [
                "id" => 3,
                "group" => "Account",
                "name" => "Email verification",
                "methods" => [1],
                "default" => true,
                "canDisable" => false
            ],
            [
                "id" => 4,
                "group" => "Account",
                "name" => "Magic email login link",
                "methods" => [1],
                "default" => true,
                "canDisable" => false
            ],
            [
                "id" => 2,
                "group" => "Account",
                "name" => "Added to Business",
                "methods" => [1],
                "default" => true,
                "canDisable" => false
            ],
            [
                "id" => 11,
                "group" => "Crewing",
                "name" => "Added to Project Crew",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 10,
                "group" => "Crewing",
                "name" => "Removed from Project Crew",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 20,
                "group" => "Crewing",
                "name" => "Crew Role Name Changed",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 12,
                "group" => "Maintenance",
                "name" => "Tagged in new Maintenance Job",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 13,
                "group" => "Maintenance",
                "name" => "Sent message in Maintenance Job",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 14,
                "group" => "Maintenance",
                "name" => "Maintenance Job changed Status",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 15,
                "group" => "Maintenance",
                "name" => "Assigned Maintenance Job",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 16,
                "group" => "Asset Groups Watching",
                "name" => "Asset added to Group",
                "methods" => [1,3,4],
                "default" => false,
                "canDisable" => true
            ],
            [
                "id" => 17,
                "group" => "Asset Groups Watching",
                "name" => "Asset removed from Group",
                "methods" => [1,2,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 18,
                "group" => "Asset Groups Watching",
                "name" => "Asset assigned to Project",
                "methods" => [1,2,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 19,
                "group" => "Asset Groups Watching",
                "name" => "Asset removed from Project",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => true
            ],
            [
                "id" => 30,
                "group" => "Business - Users",
                "name" => "User added to Business using a signup code",
                "methods" => [1,3,4],
                "default" => false,
                "canDisable" => true
            ],
            [
                "id" => 40,
                "group" => "Project",
                "name" => "Application made for a crew vacancy on a project you manage",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => false
            ],
            [
                "id" => 41,
                "group" => "Project",
                "name" => "Application updates for a crew vacancy you applied to",
                "methods" => [1,3,4],
                "default" => true,
                "canDisable" => false
            ],
        ]
    ]
);
date_default_timezone_set("UTC");