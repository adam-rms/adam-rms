<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Home", "BREADCRUMB" => false];

if (isset($_GET['i'])) {
    $GLOBALS['AUTH']->setInstance($_GET['i']);
    header("Location: " . $CONFIG['ROOTBACKENDURL'] . "?");
}

echo $TWIG->render('index.twig', $PAGEDATA);
?>
