<?php
require_once __DIR__ . '/../common/head.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Login"];

if (isset($_SESSION['return'])) {
	$PAGEDATA['return'] = $_SESSION['return'];
} else $PAGEDATA['return'] =$CONFIG['ROOTURL'];

if (isset($_GET['logout'])) {
	$AUTH->logout();
	header("Location: " . $CONFIG['ROOTURL'] . "/");
	die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/" . '" />');
}
elseif ($GLOBALS['AUTH']->login) {
	//If they're logged in, take them back to root - this fixes a loop redirect issue
	header("Location: " . $CONFIG['ROOTURL'] . "/");
	die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/" . '" />');
}

if (isset($_GET['signup'])) echo $TWIG->render('login/signup.twig', $PAGEDATA);
if (isset($_GET['login'])) echo $TWIG->render('login/login1.twig', $PAGEDATA);
echo $TWIG->render('login/login.twig', $PAGEDATA);
?>
