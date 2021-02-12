<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Dashboard", "BREADCRUMB" => false];

if (isset($_GET['i'])) {
    if ($AUTH->permissionCheck(21)) {
        $_SESSION['instanceID'] = $_GET['i']; //It doesn't even bother to verify the instance ID as the user is trusted to be BStudios Staff
        header("Location: " . $CONFIG['ROOTURL'] . "?");
    } else {
        $GLOBALS['AUTH']->setInstance($_GET['i']);
        header("Location: " . $CONFIG['ROOTURL'] . "?");
    }
}
if ($AUTH->permissionCheck(18) and isset($_GET['phpversion'])) {
    phpinfo();
    exit;
}

$DBLIB->orderBy("authTokens_created","DESC");
$DBLIB->where("users_userid",$AUTH->data['users_userid']);
$DBLIB->where("(authTokens_deviceType != 'Web')");
$PAGEDATA['APPLOGIN'] = $DBLIB->getone("authTokens",["authTokens_deviceType"]);


$PAGEDATA['WIDGETS'] = new statsWidgets(explode(",",$AUTH->data['users_widgets']));

echo $TWIG->render('dashboard.twig', $PAGEDATA);
?>
