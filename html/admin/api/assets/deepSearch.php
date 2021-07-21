<?php
require_once __DIR__ . '/../apiHead.php';


if ($AUTH->login and isset($_POST['projectid']) and is_numeric($_POST['projectid']) and $_POST['projectid'] != $AUTH->data["users_selectedProjectID"]) {
    $DBLIB->where("users_userid", $AUTH->data['users_userid']);
    $update = $DBLIB->update("users", ["users_selectedProjectID" => $_POST['projectid']], 1);
    if (!$update) finish(false,["message"=>"Selected project change error"]);
    else $AUTH->data['users_selectedProjectID'] = $_POST['projectid'];
}

if (isset($_POST['public']) and $AUTH->login) {
    //Simulate not being logged out, even if they are logged in, on public mode
    $GLOBALS['AUTH']->login = false;
    $PAGEDATA['AUTH'] = false;
}
$dates = explode(" - ", $_POST['dates']);
if (count($dates) == 2) {
    $start = $dates[0];
    $end = $dates[1];
} else {
    $start = false;
    $end = false;
}
$result = $bCMS->deepSearch($_POST['instance_id'],$_POST['page'],$_POST['page_limit'],$_POST['sort'],$_POST['category'],$_POST['keyword'],$_POST['manufacturer'],$_POST['group'],($_POST['showlinked'] == 1 ? true : false),($_POST['showarchived'] == 1 ? true : false),$start,$end,$_POST['tags']);
if ($result) {
    if (isset($_POST['html'])) {
        $PAGEDATA['SEARCH'] = $result['SEARCH'];
        if ($AUTH->login) $result['AUTH'] = $GLOBALS['AUTH']; //Provide an auth context
        $result['html'] = $TWIG->render('assets/assetsShopView.twig', $result);
    }
    finish(true,null,$result);
}
else finish(false, ["message" => "Error loading results"]);