<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("USERS:VIEW")) finish(false, ["code" => "AUTH-ERROR", "message" => "No auth for action"]);

// DataTables server-side processing parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? max(0, intval($_POST['start'])) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 25;
if ($length < 1 || $length > 100) $length = 25;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 1;
$orderDir = (isset($_POST['order'][0]['dir']) && strtolower($_POST['order'][0]['dir']) === 'desc') ? 'DESC' : 'ASC';

// Map DataTables column index to database column
$columnMap = [
    0 => 'users_username',
    1 => 'users_name1',
    2 => 'users_name2',
    3 => 'users_email',
    6 => 'users_termsAccepted',
];
$orderColumn = isset($columnMap[$orderColumnIndex]) ? $columnMap[$orderColumnIndex] : 'users_name1';

// Total records (unfiltered)
$DBLIB->where("users_deleted", 0);
$totalRecords = $DBLIB->getValue("users", "count(*)");

// Apply search filter
$searchWhere = "";
if (strlen($searchValue) > 0) {
    $escaped = $bCMS->sanitizeStringMYSQL($searchValue);
    $searchWhere = "(users_username LIKE '%" . $escaped . "%'"
        . " OR users_name1 LIKE '%" . $escaped . "%'"
        . " OR users_name2 LIKE '%" . $escaped . "%'"
        . " OR users_email LIKE '%" . $escaped . "%')";
}

// Filtered records count
$DBLIB->where("users_deleted", 0);
if ($searchWhere) $DBLIB->where($searchWhere);
$filteredRecords = $DBLIB->getValue("users", "count(*)");

// Get paginated data
$DBLIB->where("users_deleted", 0);
if ($searchWhere) $DBLIB->where($searchWhere);
$DBLIB->orderBy($orderColumn, $orderDir);
$users = $DBLIB->get('users', [$start, $length], [
    "users.users_email", "users.users_userid", "users.users_emailVerified",
    "users.users_name1", "users.users_name2", "users.users_suspended",
    "users.users_termsAccepted", "users.users_thumbnail", "users.users_username"
]);

if (!$users) $users = [];

// Batch-fetch related data for the current page's users
$userIds = array_column($users, 'users_userid');
$positionsByUser = [];
$instancesByUser = [];
$lastLoginByUser = [];
$lastAnalyticsByUser = [];

if (count($userIds) > 0) {
    // Fetch current positions for all users on this page
    $DBLIB->where("users_userid", $userIds, "IN");
    $DBLIB->where("userPositions_end >= '" . date('Y-m-d H:i:s') . "'");
    $DBLIB->where("userPositions_start <= '" . date('Y-m-d H:i:s') . "'");
    $DBLIB->orderBy("positions_rank", "ASC");
    $DBLIB->orderBy("positions_displayName", "ASC");
    $DBLIB->join("positions", "positions.positions_id=userPositions.positions_id", "LEFT");
    $allPositions = $DBLIB->get("userPositions", null, ["userPositions.users_userid", "positions.positions_displayName", "userPositions.userPositions_displayName"]);
    if ($allPositions) {
        foreach ($allPositions as $pos) {
            $positionsByUser[$pos['users_userid']][] = $pos;
        }
    }

    // Fetch instances for all users on this page
    $DBLIB->where("userInstances.users_userid", $userIds, "IN");
    $DBLIB->where("userInstances.userInstances_deleted", 0);
    $DBLIB->where("instances.instances_deleted", 0);
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
    $DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
    $allInstances = $DBLIB->get("userInstances", null, ["userInstances.users_userid", "instances.instances_name", "instances.instances_planName", "userInstances.userInstances_label", "userInstances.userInstances_archived", "instancePositions.instancePositions_displayName"]);
    if ($allInstances) {
        foreach ($allInstances as $inst) {
            $instancesByUser[$inst['users_userid']][] = $inst;
        }
    }

    // Fetch last login for all users on this page (most recent auth token per user)
    foreach ($userIds as $uid) {
        $DBLIB->where("users_userid", $uid);
        $DBLIB->where("(authTokens_adminId IS NULL)");
        $DBLIB->orderBy("authTokens_created", "DESC");
        $login = $DBLIB->getOne("authTokens", ["authTokens_created"]);
        if ($login) $lastLoginByUser[$uid] = $login['authTokens_created'];
    }

    // Fetch last analytics event for all users on this page
    foreach ($userIds as $uid) {
        $DBLIB->where("users_userid", $uid);
        $DBLIB->where("(adminUser_users_userid IS NULL)");
        $DBLIB->orderBy("analyticsEvents_timestamp", "DESC");
        $analytics = $DBLIB->getOne("analyticsEvents", ["analyticsEvents_timestamp"]);
        if ($analytics) $lastAnalyticsByUser[$uid] = $analytics['analyticsEvents_timestamp'];
    }
}

// Build response data
$data = [];
foreach ($users as $user) {
    $uid = $user['users_userid'];
    $positions = isset($positionsByUser[$uid]) ? $positionsByUser[$uid] : [];
    $instances = isset($instancesByUser[$uid]) ? $instancesByUser[$uid] : [];
    $lastLogin = isset($lastLoginByUser[$uid]) ? $lastLoginByUser[$uid] : null;
    $lastAnalytics = isset($lastAnalyticsByUser[$uid]) ? $lastAnalyticsByUser[$uid] : null;

    $data[] = [
        'users_userid' => $uid,
        'users_username' => $user['users_username'],
        'users_name1' => $user['users_name1'],
        'users_name2' => $user['users_name2'],
        'users_email' => $user['users_email'],
        'users_emailVerified' => $user['users_emailVerified'] == 1,
        'users_suspended' => $user['users_suspended'] == 1,
        'users_termsAccepted' => $user['users_termsAccepted'],
        'users_thumbnail' => $user['users_thumbnail'],
        'currentPositions' => $positions,
        'instances' => $instances,
        'lastLogin' => $lastLogin,
        'lastAnalytics' => $lastAnalytics,
    ];
}

die(json_encode([
    'draw' => $draw,
    'recordsTotal' => intval($totalRecords),
    'recordsFiltered' => intval($filteredRecords),
    'data' => $data,
]));
