<?php

/**
 * API
 * \clients\archive.php
 * Archives a client
 *
 * Arguments:
 *  - clients_id [int]: a client's id
 */

require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CLIENTS:EDIT")) die("404");

if (!isset($_POST['clients_id'])) die("404");

$DBLIB->where("clients.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("clients.clients_deleted", 0);
$DBLIB->where("clients.clients_id", $_POST['clients_id']);
$client = $DBLIB->update("clients", ["clients.clients_archived" => 1]);

if (!$client) finish(false);

$bCMS->auditLog("ARCHIVE", "clients", "Moved the client to archive", $AUTH->data['users_userid'], null, $_POST['client_id']);

finish(true);

/**
 *  @OA\Post(
 *      path="/clients/archive.php",
 *      summary="Archive Client",
 *      description="Archive (Soft Delete) a client
Requires Instance Permission CLIENTS:EDIT",
 *      operationId="archiveClient",
 *      tags={"clients"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="clients_id",
 *          in="query",
 *          description="Id of the client to archive",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */