<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Trusted Domains", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:SETTINGS:EDIT:TRUSTED_DOMAINS")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions",null,["instancePositions_id","instancePositions_displayName"]);


$PAGEDATA['instanceTrustedDomains'] = json_decode($AUTH->data['instance']['instances_trustedDomains'],true);

echo $TWIG->render('instances/trustedDomains.twig', $PAGEDATA);
?>
