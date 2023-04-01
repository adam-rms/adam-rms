<?php
require_once 'loginAjaxHead.php';

use \Firebase\JWT\JWT;

$baseLocation = $CONFIG['ROOTURL'];
if (!isset(($_SESSION['app-oauth'])) || !isset($_SESSION['oauth2'])) {
  //We're not using an app, so just redirect to the root
  header("Location: " . $CONFIG['ROOTURL']);
}

if ($_SESSION['app-oauth'] == "v2") {
  //we're logging in using the V2 App
  $baseLocation = $_SESSION['oauth2']['redirect_uri'] . "/?state=" . $_SESSION['oauth2']['state'];
} else {
  //Legacy App
  $baseLocation = $_SESSION['app-oauth'] . 'oauth_callback?legacy=true';
}

if ($_SESSION['app-oauth']['userid'] && isset($_GET['approved'])) {
  $token = $GLOBALS['AUTH']->generateToken($_SESSION['app-oauth']['userid'], false, null, true, "App OAuth2");
  $jwt = JWT::encode(array(
    "iss" => $CONFIG['ROOTURL'],
    "uid" => $_SESSION['app-oauth']['userid'],
    "token" => $token,
    "exp" => time() + 21 * 24 * 60 * 60, //21 days token expiry
    "iat" => time()
  ), $CONFIG['JWTKey']);

  //unset the session oauth now its used
  $_SESSION['app-oauth'] = null;

  header("Location: " . $baseLocation . "&token=" . $jwt);
  exit;
} else {
  if (isset($_GET['end_session'])) {
    $AUTH->logout();
  }
  header("Location: " . $baseLocation . "&error=access_denied");
  exit;
}
