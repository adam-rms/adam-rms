<?php
require_once __DIR__ . '/../common/head.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Login"];

if (isset($_SESSION['return'])) {
	$PAGEDATA['return'] = $_SESSION['return'];
} else $PAGEDATA['return'] =$CONFIG['ROOTURL'];

$AUTH->logout(); //Log em out even if they didn't want to - It solves a few issues!

echo $TWIG->render('login/login1.twig', $PAGEDATA);
?>
