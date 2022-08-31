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

if (!$AUTH->instancePermissionCheck(39)) die("404");

if (!isset($_POST['locations_id'])) die("404");

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->where("locations.locations_id", $_POST['locations_id']);
$location = $DBLIB->update("locations", ["locations.locations_archived" => 0]);

if (!$location) finish(false);

$bCMS->auditLog("UNARCHIVE", "locations", "Moved the location from archive", $AUTH->data['users_userid'], null, $_POST['location_id']);

finish(true);
