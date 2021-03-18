<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Assets", "BREADCRUMB" => false];

if (isset($_GET['p'])) {
	//Duplicated in deletion of project page
	$DBLIB->where("users_userid", $AUTH->data['users_userid']);
	$DBLIB->update("users", ["users_selectedProjectID" => $_GET['p']]);
	header("Location: " . $CONFIG['ROOTURL'] . "/assets.php");
}


if (isset($_GET['showtags'])) $PAGEDATA['showTags'] = true;
else $PAGEDATA['showTags'] = false;

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;


if (isset($_GET['listView'])) echo $TWIG->render('assetsListView.twig', $PAGEDATA);
else echo $TWIG->render('assetsShopView.twig', $PAGEDATA);
?>
