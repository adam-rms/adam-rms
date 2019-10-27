<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Home", "BREADCRUMB" => false];

echo $TWIG->render('index.twig', $PAGEDATA);
?>
