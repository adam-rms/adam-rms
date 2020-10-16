<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(113)) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = ["TITLE" => "Training", "BREADCRUMB" => false];

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_GET['pageLimit']) ? $_GET['pageLimit'] : 20);
$DBLIB->where("modules.modules_deleted", 0);
if (!$AUTH->instancePermissionCheck(113)) $DBLIB->where("modules.modules_show", 1);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$PAGEDATA['modules'] = $DBLIB->arraybuilder()->paginate('modules', $page, ["modules.*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

echo $TWIG->render('training/training_modules.twig', $PAGEDATA);
?>
