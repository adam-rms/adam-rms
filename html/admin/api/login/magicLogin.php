<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['formInput'])) {
    if ($AUTH->sendMagicLink($GLOBALS['bCMS']->sanitizeString($_POST['formInput']), $GLOBALS['bCMS']->sanitizeString($_POST['redirect']))) finish(true);
    else finish(false);
} else die("Sorry - page not found");

/**
 *  @OA\Post(
 *      path="/login/magicLogin.php",
 *      summary="Send Magic Link",
 *      description="Send Magic Login Link by Email to user",
 *      operationId="magicLogin",
 *      tags={"authentication"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="formInput",
 *          in="query",
 *          description="Email Address to send link to",
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="redirect",
 *          in="query",
 *          description="Source to redirect to, must be on allowed list in production",
 *          required="false",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *  )
 */
