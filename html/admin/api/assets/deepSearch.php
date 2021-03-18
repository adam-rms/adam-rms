<?php
require_once __DIR__ . '/../apiHead.php';

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
$result = $bCMS->deepSearch($_POST['instance_id'],$_POST['page'],$_POST['page_limit'],$_POST['category'],$_POST['keyword'],$_POST['manufacturer'],$_POST['group'],($_POST['showlinked'] == 1 ? true : false),($_POST['showarchived'] == 1 ? true : false),$start,$end);
if ($result) {
    if (isset($_POST['html'])) {
        $PAGEDATA['SEARCH'] = $result['SEARCH'];
        $result['html'] = $TWIG->render('assets/assetsShopView.twig', $result);
    }
    finish(true,null,$result);
}
else finish(false, ["message" => "Error loading results"]);