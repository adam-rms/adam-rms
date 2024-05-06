<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id",$_GET['p']);
$PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages",["cmsPages_id"]);
if (!$PAGEDATA['PAGE']) die($TWIG->render('404.twig', $PAGEDATA));

//get all views for page
$DBLIB->where("cmsPages_id",$_GET['p']);
$DBLIB->orderBy("cmsPagesViews_timestamp","ASC");
$views = $DBLIB->get("cmsPagesViews");

/**
 * Hello future editor of this
 * Obviously, fetching this information as SQL queries would be better for pageload times.
 * This method of using php to do the calculations is easier to expand on, and so is
 * hopefully easier for you to play with the cms page data.
 *
 * Enjoy
 */


//declare stats array
$accessedUsers = [];
$PAGEDATA['stats'] = [
    'today' => 0,
    'week' => 0,
    'all' => 0,
];

//Create actual stats here
foreach ($views as $key => $view){
    $date = new DateTime($view['cmsPagesViews_timestamp']);
    $date = $date->format('Y-m-d'); //get the timestamp as just a date for comparisons
    if ($date == date("Y-m-d")){ //is today
        $PAGEDATA['stats']['today'] ++;
        $PAGEDATA['stats']['week'] ++;
    } elseif ($date > date('Y-m-d', strtotime("-7 days"))){//is past week
        $PAGEDATA['stats']['week'] ++;
    }
    $PAGEDATA['stats']['all'] ++; //all records

    //This bit is for views by user
    if (isset($accessedUsers[$view['users_userid']])) {
        //user already exists so just increment
        $accessedUsers[$view['users_userid']]['accessed'] ++;
    } else {
        //add new user + id combination to accessed user list
        $accessedUsers[$view['users_userid']]['accessed'] = 1;

        //get username
        if ($view['users_userid'] != null) { //Check if it was a public view
            $accessedUsers[$view['users_userid']]['users_userid'] = $view['users_userid'];
            $DBLIB->where("users_userid", $view['users_userid']);
            $accessedUsers[$view['users_userid']]['user'] = $DBLIB->getOne("users", ["users_name1", "users_name2"]);
        }
    }
    //update timestamp as should be newer - should probs check this but oh well
    $accessedUsers[$view['users_userid']]['last'] = $view['cmsPagesViews_timestamp'];

}
//add user data to page data
$PAGEDATA['userViews'] = $accessedUsers;

echo $TWIG->render('cms/cms_stats.twig', $PAGEDATA);
?>
