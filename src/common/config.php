<?php
/*
 * This page must not be edited for each install as it's required by the updater
 *
 * Any settings must go in the environment variables
 */

$CONFIG = array(
    'VERSION' => ['ENV' => getenv('bCMS__VERSION') ? (strlen(getenv('bCMS__VERSION')) > 7 ? substr(getenv('bCMS__VERSION'), 0, 7) : getenv('bCMS__VERSION')) : false, 'COMMIT' => file_get_contents (__DIR__ . '/version/COMMIT.txt'), 'TAG' => file_get_contents (__DIR__ . '/version/TAG.txt'), "COMMITFULL" => file_get_contents (__DIR__ . '/version/COMMITFULL.txt')], //Version number is the first 7 characters of the commit hash for certain deployments, and for others there's a nice numerical tag.
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
    'ENABLE_DEV_DB_EDITOR' => (getenv('RUNNING_IN_DEVCONTAINER') == "devcontainer" ? true : false),
    'AUTH-PROVIDERS' => [
        "GOOGLE" => [
            'keys' => [
                'id' => getenv('bCMS__OAUTH__GOOGLEKEY'),
                'secret' => getenv('bCMS__OAUTH__GOOGLESECRET')
            ],
            'scope' => 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email'
        ]
    ],
    'DEV' => (getenv('ERRORS') == "true" ? true : false),
);
date_default_timezone_set($CONFIG['TIMEZONE']);