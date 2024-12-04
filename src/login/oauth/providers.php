<?php

$hybridauth_providers = [];

$PAGEDATA['microsoftAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_APP_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_KEYS_SECRET") != false;
if ($PAGEDATA['microsoftAuthAvailable']) {
    $hybridauth_providers["Microsoft"] = [
        "enabled" => true,
        "keys" => [
            "id" => $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_APP_ID"),
            "secret" => $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_KEYS_SECRET")
        ],
        "scope" => "openid user.read",
        "tenant" => "common",
    ];
}

$PAGEDATA['googleAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_SECRET") != false;
if ($PAGEDATA['googleAuthAvailable']) {
    $hybridauth_providers["Google"] = [
        "enabled" => true,
        "keys" => [
            "id" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_ID"),
            "secret" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_SECRET")
        ],
        "scope" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_SCOPE"),
    ];
}

$hybridauth_configObject = [
    "callback" => $CONFIG['ROOTURL'] . '/login/oauth/',
    "providers" => $hybridauth_providers
];

$hybridauth = new Hybridauth\Hybridauth($hybridauth_configObject);
