<?php

/**
 * API
 * \locations\archive.php
 * Archives a location
 *
 * Arguments:
 *  - location_id [int]: a location's id
 */

require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(39)) die("404");

if (!isset($_POST['location_id'])) die("404");

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->where("locations.locations_id", $_POST['location_id']);
$client = $DBLIB->update("locations", ["locations.locations_archived" => 1]);

if (!$client) finish(false);

$bCMS->auditLog("ARCHIVE", "locations", "Moved the client to archive", $AUTH->data['users_userid'], null, $_POST['location_id']);

finish(true);
