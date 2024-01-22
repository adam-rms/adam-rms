<?php
require_once __DIR__ . '/apiHead.php';

$positionsGroups = $DBLIB->getValue("positionsGroups", "count(*)");
if (!$positionsGroups or $positionsGroups < 1) {
  http_response_code(500);
  die("ERROR");
} else {
  http_response_code(200);
  die("OK");
}

/**
 *  @OA\Get(
 *    path="/onlineCheck.php",
 *    Summary="Server Status"
 *    description="Ping the server for its status",
 *    operationId="onlineCheck",
 *    @OA\Response(
 *      response="200",
 *      description="OK",
 *      @OA\MediaType(
 *        mediaType="text/plain", 
 *        @OA\Schema(
 *          type="string",
 *        ),
 *      ),
 *    ),
 *    @OA\Response(
 *      response="500",
 *      description="ERROR",
 *      @OA\MediaType(
 *        mediaType="text/plain", 
 *        @OA\Schema(
 *          type="string",
 *        ),
 *      ),
 *    ),
 *  )
 */