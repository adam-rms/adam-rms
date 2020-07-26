<?php
	require_once __DIR__ . '/../apiHeadSecure.php';
	header('Content-Type:text/plain');

		parse_str($_POST['data'],$data); //Convert the data back into an array

	$userTabledata = [
		"users_social_facebook" => trim(strtolower($bCMS->sanitizeString($data['facebook']))),
        "users_social_twitter" => trim(strtolower($bCMS->sanitizeString($data['twitter']))),
        "users_social_linkedin" => trim(strtolower($bCMS->sanitizeString($data['linkedin']))),
        "users_social_snapchat" => trim(strtolower($bCMS->sanitizeString($data['snapchat']))),
        "users_social_instagram" => trim(strtolower($bCMS->sanitizeString($data['instagram'])))
    ];

	if ($_POST['users_userid'] != $PAGEDATA['USERDATA']['users_userid'] && $AUTH->permissionCheck(5))	$userid = $bCMS->sanitizeString($_POST['users_userid']);
	else $userid = $PAGEDATA['USERDATA']['users_userid'];

	$DBLIB->where("users_userid", $userid);
	if ($DBLIB->update ('users', $userTabledata)) {
		$bCMS->auditLog("UPDATE", "users", "CHANGE SOCIAL DETAILS", $AUTH->data['users_userid'],$userid);

		die("1");
	}
	else die("2");

?>
