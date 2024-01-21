<?php

/**
 * API
 * \locations\archive.php
 * Unarchives a location
 *
 * Arguments:
 *  - locations_id [int]: a location's id
 */

require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("LOCATIONS:EDIT")) die("404");

if (!isset($_POST['locations_id'])) die("404");

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->where("locations.locations_id", $_POST['locations_id']);
$location = $DBLIB->update("locations", ["locations.locations_archived" => 0]);

if (!$location) finish(false);

$bCMS->auditLog("UNARCHIVE", "locations", "Moved the location from archive", $AUTH->data['users_userid'], null, $_POST['location_id']);

finish(true);

/**
 *  @OA\Post(
 *      path="/locations/unarchive.php",
 *      summary="UnArchive Location",
 *      description="Restore a location
 Requires Instance permission LOCATIONS:EDIT",
 *      operationId="archiveLocation",
 *      tags={"locations"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="locations_id",
 *          in="query",
 *          description="Id of location to restore",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */