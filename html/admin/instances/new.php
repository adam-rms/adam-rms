<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "New Business", "BREADCRUMB" => false];

if (!$AUTH->permissionCheck(8)) die("Sorry - you can't access this page");

echo $TWIG->render('instances/new.twig', $PAGEDATA);
?>
