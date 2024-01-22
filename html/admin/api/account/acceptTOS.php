<?php
require_once __DIR__ . '/../apiHeadSecure.php';
$DBLIB->where ('users_userid', $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_termsAccepted" => date("Y-m-d H:i:s")])) {
    $bCMS->auditLog("UPDATE", "users", "ACCEPT TOS", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    finish(true);
} else finish(false);

/**
 *  @OA\Get(
 *      path="/account/acceptTOS.php",
 *      summary="Accept Tos",
 *      description="Accepts the Terms of Service for the currently logged in user",
 *      operationId="getAcceptTos",
 *      tags={"account"},
 *      @OA\Response(
 *          response="200",
 *          description="OK or Error",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *  )
 */