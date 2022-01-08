<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "404 Page not Found", "BREADCRUMB" => false];

echo $TWIG->render('404.twig', $PAGEDATA);
?>
