<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Users", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("USERS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

echo $TWIG->render('server/users.twig', $PAGEDATA);
?>
