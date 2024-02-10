<?php 
require_once __DIR__ . '/common/headSecure.php';

$scriptStartTime = microtime (true);

$dates = explode(" - ", $_GET['dates']);
if (count($dates) == 2) {
    $dateStart = $dates[0];
    $dateEnd = $dates[1];
} else {
    $dateStart = false;
    $dateEnd = false;
}

$DBLIB->setTrace(true, $_SERVER['SERVER_ROOT']);
$SEARCH = [
    "INSTANCE_ID" => in_array($_GET['instance_id'],$AUTH->data['instance_ids']) ? $_GET['instance_id'] : $AUTH->data['instance']['instances_id'],
    "PROJECT_ID" => $_GET['project'] ?: false,
    "PROJECT_REFERER" => $_GET['project_referer'] ?: false,
    "PAGE" =>  $_GET['page'] ? intval($_GET['page']) : 1,
    "PAGE_LIMIT" => $_GET['resultsperpage'] ? intval($_GET['resultsperpage']) : 20,
    "SETTINGS" => [
        "SHOWLINKED" => ($_GET['showlinked'] == 1 ? true : false),
        "SHOWARCHIVED" => ($_GET['showarchived'] == 1 ? true : false),
        "HIDEIMAGES" => ($_GET['hideimages'] == 1 ? true : false),
    ],
    "TERMS" => [
        "CATEGORY" => is_array($_GET['category']) ? $_GET['category'] : [],
        "KEYWORDS" => is_array($_GET['keyword']) ? $_GET['keyword'] : [],
        "MANUFACTURER" => is_array($_GET['manufacturer']) ? $_GET['manufacturer'] : [],
        "GROUPS" => is_array($_GET['group']) ? $_GET['group'] : [],
        "DATE-START" => $dateStart,
        "DATE-END" => $dateEnd,
        "SORT" => $_GET['sort'] ?: "alphabet-a",
        "TAGS" => (is_array($_GET['tags'])) ? $_GET['tags'] : [],
    ],
    "SELECTED_TERMS" => [
      "MANUFACTURER" => [],
      "CATEGORY" => [],
      "GROUPS" => [],
    ]
];
$RETURN = [
    "PAGINATION" => [
        "PAGE" => $SEARCH['PAGE']
    ],
    "ASSETS" => [],
    "PROJECT" => [
        "ID" => false,
        "NAME" => false
    ]
];

$DBLIB->where("instances_id",$SEARCH['INSTANCE_ID']);
$DBLIB->where("instances_deleted",0);
$SEARCH['INSTANCE'] = $DBLIB->getone("instances",['instances_id','instances_config_currency']);
if (!$SEARCH['INSTANCE']) die($TWIG->render('404.twig', $PAGEDATA));

//Evaluate dates or project
if ($SEARCH['PROJECT_ID'] and $AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN")) {
    $DBLIB->where("projects_id", $SEARCH['PROJECT_ID']);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("projects.projects_dates_deliver_start",NULL,"IS NOT");
    $DBLIB->where("projects.projects_dates_deliver_end",NULL,"IS NOT");
    $thisProject = $DBLIB->getone("projects",["projects_name", "projects_dates_deliver_start","projects_dates_deliver_end"]);
    if (!$thisProject) {
        $dateStart = false;
        $dateEnd = false;
    } else {
        $dateStart = strtotime($thisProject['projects_dates_deliver_start']);
        $dateEnd = strtotime($thisProject['projects_dates_deliver_end']);
        $RETURN['PROJECT']['ID'] = $SEARCH['PROJECT_ID'];
        $RETURN['PROJECT']['NAME'] = $thisProject['projects_name'];
    }
} elseif ($dateStart and $dateEnd) {
  $dateStart = strtotime($dateStart);
  $dateEnd = strtotime($dateEnd);
  if ($dateEnd <= $dateStart) {
      $dateStart = false;
      $dateEnd = false;
  }
} else {
    $dateStart = false;
    $dateEnd = false;
}
$RETURN['PROJECT']['DATESTART'] = $dateStart;
$RETURN['PROJECT']['DATEEND'] = $dateEnd;

//**START CHONKY QUERY**

//Evaluate categories
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
if ($SEARCH['TERMS']['CATEGORY']) $DBLIB->where('assetTypes.assetCategories_id', $SEARCH['TERMS']['CATEGORY'], 'IN');

//Evaluate manufacturers
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
if ($SEARCH['TERMS']['MANUFACTURER']) $DBLIB->where('manufacturers.manufacturers_id',$SEARCH['TERMS']['MANUFACTURER'], 'IN');

//Sorting
$sortArray = explode("-",$SEARCH['TERMS']['SORT']);
if (count($sortArray) == 2) {
    if ($sortArray[0] == "price") $DBLIB->orderBy("assetTypes.assetTypes_weekRate", ($sortArray[1] == "a" ? "ASC" : "DESC"));
    elseif ($sortArray[0] == "value") $DBLIB->orderBy("assetTypes.assetTypes_value", ($sortArray[1] == "a" ? "ASC" : "DESC"));
    elseif ($sortArray[0] == "alphabet") $DBLIB->orderBy("assetTypes.assetTypes_name", ($sortArray[1] == "a" ? "ASC" : "DESC"));
    elseif ($sortArray[0] == "mass") $DBLIB->orderBy("assetTypes.assetTypes_mass", ($sortArray[1] == "a" ? "ASC" : "DESC"));
    elseif ($sortArray[0] == "date") $DBLIB->orderBy("assetTypes.assetTypes_inserted", ($sortArray[1] == "a" ? "ASC" : "DESC"));
    else $DBLIB->orderBy("assetTypes.assetTypes_name", "ASC"); //Default
} else $DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");

$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC"); // Last item in the sort each time

//Keywords
if (count($SEARCH['TERMS']['KEYWORDS']) > 0) {
    $thisWhere = false;
    $thisValues = [];
    foreach ($SEARCH['TERMS']['KEYWORDS'] as $word) {
        if ($word != null) {
            if ($thisWhere != false) $thisWhere .= ' OR ';
            else $thisWhere = "(";
            $thisWhere .= "manufacturers.manufacturers_name LIKE ? OR assetTypes.assetTypes_description LIKE ? OR assetTypes.assetTypes_name LIKE ?";
            array_push($thisValues,'%' . $word . '%','%' . $word . '%','%' . $word . '%');
        }
    }
    $DBLIB->where($thisWhere . ")",$thisValues);
}


//Limit the assets correctly
$subQuery = $DBLIB->subQuery();
$subQuery->where("assets.instances_id",$SEARCH['INSTANCE_ID']);
$subQuery->where("assets_deleted",0);
if (!$SEARCH['SETTINGS']['SHOWARCHIVED']) $subQuery->where ("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . date ("Y-m-d H:i:s") . "')");

if ($SEARCH['TERMS']['GROUPS']) {
    $thisWhere = false;
    $thisValues = [];

    foreach ($SEARCH['TERMS']['GROUPS'] as $group) {
        if ($group != null) {
            if ($thisWhere != false) $thisWhere .= ' OR ';
            else $thisWhere = "(";
            $thisWhere .= "FIND_IN_SET(?, assets.assets_assetGroups)";
            array_push($thisValues,intval($group));
        }
    }
    if ($thisWhere) $subQuery->where($thisWhere . ")",$thisValues);
}
if (!$SEARCH['SETTINGS']['SHOWLINKED']) $subQuery->where ("assets.assets_linkedTo", NULL, 'IS');
if ($SEARCH['TERMS']['TAGS']) {
    $thisWhere = false;
    $thisValues = [];
    foreach ($SEARCH['TERMS']['TAGS'] as $word) {
        if ($word != null) {
            if ($thisWhere != false) $thisWhere .= ' OR ';
            else $thisWhere = "(";
            $thisWhere .= "assets.assets_tag LIKE ?";
            array_push($thisValues,'%' . $word . '%');
        }
    }
    if ($thisWhere) $subQuery->where($thisWhere . ")",$thisValues);
}
$subQuery->groupBy ("assetTypes_id");
$subQuery->get("assets", null, "assetTypes_id");
$DBLIB->where("assetTypes_id", $subQuery, 'in');

//The actual select
$DBLIB->pageLimit = $SEARCH["PAGE_LIMIT"];
$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = ?)",[$SEARCH['INSTANCE_ID']]);
$assets = $DBLIB->arraybuilder()->paginate('assetTypes', $SEARCH["PAGE"], ["assetTypes.*", "manufacturers.*", "assetCategories.*", "assetCategoriesGroups_name"]);
$RETURN['PAGINATION']['TOTAL-PAGES'] = $DBLIB->totalPages;
$RETURN['PAGINATION']['COUNT'] = $DBLIB->totalCount;
$RETURN['PAGINATION']['OFFSET'] = $SEARCH["PAGE_LIMIT"]*($SEARCH["PAGE"]-1);
foreach ($assets as $asset) {
    $DBLIB->where("assets.assetTypes_id", $asset['assetTypes_id']);
    $DBLIB->where("assets.instances_id",$SEARCH['INSTANCE_ID']);
    $DBLIB->where("assets_deleted",0);
    if (!$SEARCH['SETTINGS']['SHOWARCHIVED']) $DBLIB->where ("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . date ("Y-m-d H:i:s") . "')");
    if ($SEARCH['TERMS']['GROUPS']) {
        $thisWhere = false;
        $thisValues = [];

        foreach ($SEARCH['TERMS']['GROUPS'] as $group) {
            if ($group != null) {
                if ($thisWhere != false) $thisWhere .= ' OR ';
                else $thisWhere = "(";
                $thisWhere .= "FIND_IN_SET(?, assets.assets_assetGroups)";
                array_push($thisValues,intval($group));
            }
        }
        if ($thisWhere) $DBLIB->where($thisWhere . ")",$thisValues);
    }
    if (!$SEARCH['SETTINGS']['SHOWLINKED']) $DBLIB->where ("assets.assets_linkedTo", NULL, 'IS');
    if ($SEARCH['TERMS']['TAGS']) {
        $thisWhere = false;
        $thisValues = [];
        foreach ($SEARCH['TERMS']['TAGS'] as $word) {
            if ($word != null) {
                if ($thisWhere != false) $thisWhere .= ' OR ';
                else $thisWhere = "(";
                $thisWhere .= "assets.assets_tag LIKE ?";
                array_push($thisValues,'%' . $word . '%');
            }
        }
        if ($thisWhere) $DBLIB->where($thisWhere . ")",$thisValues);
    }
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $assetTags = $DBLIB->get("assets", null, ["assets_id", "assets_notes", "assets_tag", "asset_definableFields_1", "asset_definableFields_2", "asset_definableFields_3", "asset_definableFields_4", "asset_definableFields_5", "asset_definableFields_6", "asset_definableFields_7", "asset_definableFields_8", "asset_definableFields_9", "asset_definableFields_10", "assets_dayRate", "assets_weekRate", "assets_value", "assets_mass", "assets_endDate"]);
    if (!$assetTags) continue;
    $asset['count'] = count($assetTags);
    $asset['countBlocked'] = 0;
    $asset['countAvailable'] = 0;
    $asset['fields'] = explode(",", $asset['assetTypes_definableFields']);
    $asset['thumbnail'] = $bCMS->s3List(2, $asset['assetTypes_id'],'s3files_meta_uploaded','ASC',1);
    $asset['tags'] = [];
    foreach ($assetTags as $tag) {
        if ($dateStart and $dateEnd) {
            //Check availability
            $DBLIB->where("assets_id", $tag['assets_id']);
            $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
            $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
            $DBLIB->where("projects.projects_deleted", 0);
            $DBLIB->where("((projects_dates_deliver_start >= '" . date ("Y-m-d H:i:s",$dateStart)  . "' AND projects_dates_deliver_start <= '" . date ("Y-m-d H:i:s",$dateEnd) . "') OR (projects_dates_deliver_end >= '" . date ("Y-m-d H:i:s",$dateStart) . "' AND projects_dates_deliver_end <= '" . date ("Y-m-d H:i:s",$dateEnd) . "') OR (projects_dates_deliver_end >= '" . date ("Y-m-d H:i:s",$dateEnd) . "' AND projects_dates_deliver_start <= '" . date ("Y-m-d H:i:s",$dateStart) . "'))");
            $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
            if ($RETURN['PROJECT']['ID']) {
                // If a project is being searched for specifically then we need to check if the asset is assigned to that project or if it is assigned to another project
                $DBLIB->where("(projectsStatuses.projectsStatuses_assetsReleased = 0 OR projects.projects_id = '" . $RETURN['PROJECT']['ID'] . "')");
            } else $DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
            $tag['assignment'] = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assetsAssignments_id", "assetsAssignments.projects_id", "projects.projects_name"]);
        }
        $tag['flagsblocks'] = assetFlagsAndBlocks($tag['assets_id']);
        if ($tag['assignment'] or $tag['flagsblocks']['COUNT']['BLOCK'] > 0) $asset['countBlocked']++;
        $asset['tags'][] = $tag;
    }
    $asset['countAvailable'] = $asset['count'] - $asset['countBlocked'];
    $RETURN['ASSETS'][] = $asset;
}
$RETURN['SPEED'] = microtime(true) - $scriptStartTime;


$PAGEDATA['searchOptions'] = [];

// Projects for search
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->where("projects.projects_dates_deliver_start",NULL,"IS NOT");
$DBLIB->where("projects.projects_dates_deliver_end",NULL,"IS NOT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->orderBy("projects.projects_created", "ASC");
$PAGEDATA['searchOptions']['projects'] = $DBLIB->get("projects", null, ["projects_id", "projects_name", "clients_name"]);

// Manufacturers / Groups / Categories dynamically populated with AJAX need to have their names looked up before a search
if (count($SEARCH['TERMS']['GROUPS']) > 0) {
  $DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
  $DBLIB->where("assetGroups_id", $SEARCH['TERMS']['GROUPS'], "IN");
  $DBLIB->where("instances_id", $SEARCH['INSTANCE_ID']);
  $DBLIB->where("assetGroups_deleted",0);
  $SEARCH['SELECTED_TERMS']['GROUPS'] = $DBLIB->get('assetGroups',null,["assetGroups_name","assetGroups_id"]);
} else $SEARCH['SELECTED_TERMS']['GROUPS'] = [];

if (count($SEARCH['TERMS']['MANUFACTURER']) > 0) {
  $DBLIB->where("manufacturers_id", $SEARCH['TERMS']['MANUFACTURER'], "IN");
  $DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . intval($SEARCH['INSTANCE_ID']) . "')");
  $SEARCH['SELECTED_TERMS']['MANUFACTURER'] = $DBLIB->get('manufacturers', null, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);
} else $SEARCH['SELECTED_TERMS']['MANUFACTURER'] = [];

if (count($SEARCH['TERMS']['CATEGORY']) > 0) {
  $DBLIB->where("assetCategories_id", $SEARCH['TERMS']['CATEGORY'], "IN");
  $DBLIB->where("assetCategories_deleted",0);
  $DBLIB->where("(instances_id IS NULL OR instances_id = '" . intval($SEARCH['INSTANCE_ID']) . "')");
  $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
  $SEARCH['SELECTED_TERMS']['CATEGORY'] = $DBLIB->get('assetCategories', null, ["assetCategories_id", "assetCategories_name", "assetCategoriesGroups_name"]);
} else $SEARCH['SELECTED_TERMS']['CATEGORY'] = [];

// Pass vars to twig

$RETURN['SEARCH'] = $SEARCH;
$PAGEDATA['searchResults'] = $RETURN;
echo $TWIG->render('assets.twig', $PAGEDATA);