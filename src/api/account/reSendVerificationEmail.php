<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if ($AUTH->verifyEmail($AUTH->data['users_userid'])) {
    $bCMS->auditLog("UPDATE", "users", "RESEND VERIFICATION EMAIL", $AUTH->data['users_userid'], $AUTH->data['users_userid']);
    finish(true);
} else finish(false);
/**
 *  @OA\Get(
 *      path="/account/reSendVerificationEmail.php",
 *      summary="Resend Verification Email", 
 *      description="Resend the verification email to the user", 
 *      operationId="resendVerificationEmail", 
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
