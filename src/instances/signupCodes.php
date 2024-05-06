<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Signup Codes", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:USER_SIGNUP_CODES:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->where("signupCodes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("signupCodes_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		signupCodes_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' 
    )");
}
$PAGEDATA['codes'] = $DBLIB->get("signupCodes",null,["signupCodes.*", "(SELECT COUNT(*) FROM userInstances WHERE userInstances.signupCodes_id=signupCodes.signupCodes_id) AS count"]);

$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions",null,["instancePositions_id","instancePositions_displayName"]);

echo $TWIG->render('instances/signupCodes.twig', $PAGEDATA);
?>
