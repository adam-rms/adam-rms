<?php
	require_once __DIR__ . '/../apiHeadSecure.php';
	if ($_POST['users_userid'] != $AUTH->data["users_userid"]) finish(false);

	if ($_POST['provider'] == "google") $column = "users_oauth_googleid";
	elseif ($_POST['provider'] == "slack") $column = "users_oauth_slackid";
	else finish(false);

	$DBLIB->where("users_userid",$_POST['users_userid']);
	if ($DBLIB->update ('users', [$column => null])) finish(true);
	else finish(false);
?>
