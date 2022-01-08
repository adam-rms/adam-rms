<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if (!isset($_POST['projectid'])) finish(false);
$DBLIB->where("users_userid", $AUTH->data['users_userid']);
$update = $DBLIB->update("users", ["users_selectedProjectID" => $_POST['projectid']], 1);
if ($update) finish(true);
else finish(false);