<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(113)) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("modules.modules_deleted", 0);
if (!$AUTH->instancePermissionCheck(113)) $DBLIB->where("modules.modules_show", 1);
$DBLIB->where("modules.modules_id", $_GET['id']);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$PAGEDATA['module'] = $DBLIB->getOne('modules', ["modules.*"]);
if (!$PAGEDATA['module']) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['module']['modules_name'], "BREADCRUMB" => false];

if (isset($_GET['users'])) echo $TWIG->render('training/training_users.twig', $PAGEDATA);
elseif (isset($_GET['steps'])) echo $TWIG->render('training/training_steps.twig', $PAGEDATA);
else {
    //User is trying to complete the module - try and work out what stage they're at
}
?>
