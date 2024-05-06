<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:VIEW:LIST")) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

if (isset($_GET['q'])) $q = $bCMS->sanitizeString($_GET['q']);
else $q = '';

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;

$DBLIB->pageLimit = 50; //Users per page
$DBLIB->orderBy("instancePositions.instancePositions_rank", "ASC");
$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
$DBLIB->orderBy("users.users_created", "ASC");
$DBLIB->where("users_deleted", 0);
if (strlen($q) > 0) {
	//Search
	$DBLIB->where("(
		users_username LIKE '%" . $bCMS->sanitizeString($q) . "%'
		OR users_name1 LIKE '%" . $bCMS->sanitizeString($q) . "%'
		OR users_name2 LIKE '%" . $bCMS->sanitizeString($q) . "%'
		OR users_email LIKE '%" . $bCMS->sanitizeString($q) . "%'
		)");
}
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted", 0);
$DBLIB->where("userInstances.userInstances_archived", null, "IS");
$users = $DBLIB->arraybuilder()->paginate('users', $page, ["users.users_username", "users.users_name1", "users.users_name2", "users.users_userid", "users.users_email", "users.users_emailVerified", "users.users_suspended","users.users_suspended", "instancePositions.instancePositions_displayName", "userInstances.instancePositions_id","users.users_thumbnail"]);
$return = ["users" => [], "pagination" => ["thisPageUsers" => count($users), "thisPage" => $page, "totalPages" => $DBLIB->totalPages, "totalUsers" => $DBLIB->totalCount]];
foreach ($users as $user) {
	$return["users"][] = [
		'users_userid' => $user['users_userid'],
		'users_username' => $user['users_username'],
		'users_name1' => $user['users_name1'],
		'users_name2' => $user['users_name2'],
		'users_email' => $user['users_email'],
		'users_emailVerified' => $user['users_emailVerified'] === 1,
		'users_suspended' => $user['users_suspended'] === 1,
		'instancePositions_id' => $user['instancePositions_id'],
		'instancePositions_displayName' => $user['instancePositions_displayName'],
		'users_thumbnail' => $user['users_thumbnail'],
	];
}

finish(true, null, $return);

/** @OA\Get(
 *     path="/instances/users.php", 
 *     summary="List Users", 
 *     operationId="Requires Instance permission BUSINESS:USERS:VIEW:LIST", 
 *     tags={"instances"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="undefined",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="q",
 *         in="query",
 *         description="Search by users' names, usernames, or emails",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Paginate the results, starting from 1.",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */