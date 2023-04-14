<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USER_SIGNUP_CODES:VIEW")) die("404");

$DBLIB->where("signupCodes_name", $_GET['signupCode']);
$code = $DBLIB->getOne("signupCodes",["signupCodes_id"]);
if (!$code) finish(true,null,["taken"=>false]);
else finish(true,null,["taken"=>true]);