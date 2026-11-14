<?php
require_once __DIR__ . '/../common/headSecure.php';

use \Firebase\JWT\JWT;

/**
 * Lets a user prove to the new version of AdamRMS that they may migrate one of their businesses to it.
 * The new version sends them here with a `state`; they pick a business, and go back with a short-lived
 * signed statement of its ID. That statement is not a login, and can't be used for anything on this site.
 *
 * Off unless both environment variables are set:
 *  MIGRATE_CALLBACK_URL - where to send the user back to
 *  MIGRATE_SIGNING_KEY  - base64 Ed25519 secret key (sodium_crypto_sign_secretkey)
 */
$callbackUrl = getenv('MIGRATE_CALLBACK_URL');
$signingKey = getenv('MIGRATE_SIGNING_KEY');
$state = isset($_GET['state']) ? $_GET['state'] : '';
if (!$callbackUrl or !$signingKey or !preg_match('/^[A-Za-z0-9_-]{16,128}$/', $state)) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['instances'] = [];
foreach ($AUTH->data['instances'] as $instance) {
    if (in_array("BUSINESS:MIGRATE", $instance['permissions'])) $PAGEDATA['instances'][$instance['instances_id']] = $instance;
}

// Not named instances_id, which would also switch the business the user is working in
if (isset($_POST['migrate_instances_id']) and isset($PAGEDATA['instances'][$_POST['migrate_instances_id']])) {
    $instanceId = (int) $_POST['migrate_instances_id'];
    $assertion = JWT::encode([
        "iss" => $CONFIG['ROOTURL'],
        "aud" => $callbackUrl,
        "sub" => (string) $AUTH->data['users_userid'],
        "instances_id" => $instanceId,
        "state" => $state,
        "iat" => time(),
        "exp" => time() + 60,
        "jti" => bin2hex(random_bytes(16)),
    ], $signingKey, 'EdDSA');
    $bCMS->auditLog("MIGRATE", "instances", "Authorised migration to the new version of AdamRMS", $AUTH->data['users_userid'], null, null, $instanceId);
    header("Location: " . $callbackUrl . (str_contains($callbackUrl, "?") ? "&" : "?") . http_build_query(["assertion" => $assertion, "state" => $state]));
    exit;
}

$PAGEDATA['pageConfig'] = ["TITLE" => "Migrate Business", "BREADCRUMB" => false];
echo $TWIG->render('instances/instances_migrate.twig', $PAGEDATA);
