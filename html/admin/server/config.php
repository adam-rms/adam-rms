<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Config", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("CONFIG:SET")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_POST['changingConfigUsingConfigFormTwig']) and $_POST['changingConfigUsingConfigFormTwig'] == "true") {
	$update = $CONFIGCLASS->formArrayProcess($_POST);
	if (!is_array($update)) {
		header("Location: " . "?");
	}
}

$PAGEDATA["form"] = $CONFIGCLASS->formArrayBuild();
$PAGEDATA["errors"] = is_array($update) ? $update : [];

echo $TWIG->render('server/config.twig', $PAGEDATA);
