<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("USERS:VIEW")) {
    http_response_code(403);
    die(json_encode([
        'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'No auth for action'
    ]));
}

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
    $now = date('Y-m-d H:i:s');
    $DBLIB->where("userPositions_end >= '" . $now . "'");
    $DBLIB->where("userPositions_start <= '" . $now . "'");
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
    $DBLIB->where("users_userid", $userIds, "IN");
    $DBLIB->where("(authTokens_adminId IS NULL)");
    $DBLIB->groupBy("users_userid");
    $allLogins = $DBLIB->get("authTokens", null, ["users_userid", "MAX(authTokens_created) AS lastLogin"]);
    if ($allLogins) {
        foreach ($allLogins as $login) {
            $lastLoginByUser[$login['users_userid']] = $login['lastLogin'];
        }
    }

    // Fetch last analytics event for all users on this page
    $DBLIB->where("users_userid", $userIds, "IN");
    $DBLIB->where("(adminUser_users_userid IS NULL)");
    $DBLIB->groupBy("users_userid");
    $allAnalytics = $DBLIB->get("analyticsEvents", null, ["users_userid", "MAX(analyticsEvents_timestamp) AS lastAnalytics"]);
    if ($allAnalytics) {
        foreach ($allAnalytics as $analytics) {
            $lastAnalyticsByUser[$analytics['users_userid']] = $analytics['lastAnalytics'];
        }
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

/**
 *  @OA\Post(
 *      path="/server/users.php",
 *      summary="List Users (DataTables)",
 *      description="Server-side processing endpoint for DataTables. Returns paginated, searchable user list with related data (positions, instances, last login, last page view).
 * Requires Server Permission USERS:VIEW
 * ",
 *      operationId="serverUsers",
 *      tags={"server"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(
 *                  type="object",
 *                  @OA\Property(
 *                      property="draw",
 *                      type="integer",
 *                      description="Draw counter for DataTables",
 *                  ),
 *                  @OA\Property(
 *                      property="recordsTotal",
 *                      type="integer",
 *                      description="Total number of unfiltered records",
 *                  ),
 *                  @OA\Property(
 *                      property="recordsFiltered",
 *                      type="integer",
 *                      description="Total number of records after search filter",
 *                  ),
 *                  @OA\Property(
 *                      property="data",
 *                      type="array",
 *                      description="Array of user objects for the current page",
 *                      @OA\Items(type="object"),
 *                  ),
 *              ),
 *          ),
 *      ),
 *      @OA\Response(
 *          response="403",
 *          description="Permission Error",
 *      ),
 *      @OA\Parameter(
 *          name="draw",
 *          in="query",
 *          description="DataTables draw counter",
 *          required="true",
 *          @OA\Schema(type="integer"),
 *      ),
 *      @OA\Parameter(
 *          name="start",
 *          in="query",
 *          description="Paging start index",
 *          required="true",
 *          @OA\Schema(type="integer"),
 *      ),
 *      @OA\Parameter(
 *          name="length",
 *          in="query",
 *          description="Number of records per page (max 100)",
 *          required="true",
 *          @OA\Schema(type="integer"),
 *      ),
 *      @OA\Parameter(
 *          name="search[value]",
 *          in="query",
 *          description="Global search value applied to username, first name, last name, and email",
 *          required="false",
 *          @OA\Schema(type="string"),
 *      ),
 *      @OA\Parameter(
 *          name="order[0][column]",
 *          in="query",
 *          description="Column index to order by",
 *          required="false",
 *          @OA\Schema(type="integer"),
 *      ),
 *      @OA\Parameter(
 *          name="order[0][dir]",
 *          in="query",
 *          description="Order direction (asc or desc)",
 *          required="false",
 *          @OA\Schema(type="string"),
 *      ),
 *  )
 */
