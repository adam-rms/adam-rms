<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Join Business", "BREADCRUMB" => false];

echo $TWIG->render('instances/instances_join.twig', $PAGEDATA);
?>
