<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Calendar", "BREADCRUMB" => false];

echo $TWIG->render('instances/business_calendar.twig', $PAGEDATA);
?>
