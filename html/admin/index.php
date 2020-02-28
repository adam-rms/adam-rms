<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Home", "BREADCRUMB" => false];

if (isset($_GET['i'])) {
    $GLOBALS['AUTH']->setInstance($_GET['i']);
    header("Location: " . $CONFIG['ROOTURL'] . "?");
}
if ($AUTH->permissionCheck(18) and isset($_GET['phpversion'])) {
    phpinfo();
    exit;
}
echo $TWIG->render('index.twig', $PAGEDATA);
?>
