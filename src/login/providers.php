<?php
require_once __DIR__ . '/../../common/head.php';

$providers = [];

$PAGEDATA['microsoftAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_APP_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_KEYS_SECRET") != false;
if ($PAGEDATA['microsoftAuthAvailable']) {
	$providers["Microsoft"] = [
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
if (!$PAGEDATA['googleAuthAvailable']) {
	$providers["Google"] = [
		"enabled" => true,
		"keys" => [
			"id" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_ID"),
			"secret" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_SECRET")
		],
		"scope" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_SCOPE"),
	];
}

$configObject = [
	"callback" => $CONFIG['ROOTURL'] . '/login/oauth/',
	"providers" => $providers
];

$hybridauth = new Hybridauth\Hybridauth($config);
