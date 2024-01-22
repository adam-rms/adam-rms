<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if (!$AUTH->serverPermissionCheck("PERMISSIONS:EDIT:USER_POSITION")) die("Sorry - you can't access this page");

if (!isset($_POST['action']) or !isset($_POST['users_userid']) or !isset($_POST["userPositions_id"])) finish(false, ["code" => null, "message"=> "Attribute error"]);

if ($_POST['action'] == "DELETE") {
    $DBLIB->where("users_userid", $bCMS->sanitizeString($_POST["users_userid"]));
    $DBLIB->where("userPositions_id", $bCMS->sanitizeString($_POST["userPositions_id"]));
    if ($DBLIB->delete("userPositions")) {
        $bCMS->auditLog("DELETE", "userPositions", $bCMS->sanitizeString($_POST["userPositions_id"]), $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST["users_userid"]));
        finish(true);
    } else finish(false, ["code" => null, "message"=> "Delete error"]);
} elseif ($_POST['action'] == "EDIT") {
    if (strlen($_POST["userPositions_start"]) < 1 or strlen($_POST["userPositions_end"]) < 1 or strlen($_POST["userPositions_show"]) < 1) finish(false, ["code" => null, "message"=> "Attribute data error"]);
    $data = [
        "users_userid"=>$bCMS->sanitizeString($_POST["users_userid"]),
        "positions_id"=>$bCMS->sanitizeString($_POST["positions_id"]),
        "userPositions_start"=>date("Y-m-d H:i:s", strtotime($bCMS->sanitizeString($_POST["userPositions_start"]))),
        "userPositions_end"=>date("Y-m-d H:i:s", strtotime($bCMS->sanitizeString($_POST["userPositions_end"]))),
        "userPositions_show"=>$bCMS->sanitizeString($_POST["userPositions_show"])
    ];
    if ($_POST['userPositions_id'] == 'new') {
        if ($DBLIB->insert("userPositions",$data)) {
            $bCMS->auditLog("CREATE", "userPositions", json_encode($data), $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST["users_userid"]));
            finish(true);
        } else finish(false, ["code" => null, "message"=> "Insert error" . $DBLIB->getlastError()]);
    } else {
        $DBLIB->where("users_userid", $bCMS->sanitizeString($_POST["users_userid"]));
        $DBLIB->where("userPositions_id", $bCMS->sanitizeString($_POST["userPositions_id"]));
        if ($DBLIB->update("userPositions",$data)) {
            $bCMS->auditLog("EDIT", "userPositions", $bCMS->sanitizeString($_POST["userPositions_id"]), $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST["users_userid"]));
            finish(true);
        } else finish(false, ["code" => null, "message"=> "Edit error"]);
    }
} else finish(false, ["code" => null, "message"=> "Attribute action error"]);

/** @OA\Post(
 *     path="/account/permissions.php", 
 *     summary="Permission Management", 
 *     description="Manage user pemissions
Requires server permission PERMISSIONS:EDIT:USER_POSITION", 
 *     operationId="permissionManagement", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="action",
 *         in="query",
 *         description="DELETE, EDIT or new",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="users_userid",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="userPositions_id",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
