<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Users & Settings", "BREADCRUMB" => false];
echo $TWIG->render('instances/instances_navigation.twig', $PAGEDATA);
