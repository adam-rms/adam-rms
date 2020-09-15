<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(109)) die("404");

$DBLIB->where("signupCodes_name", $_GET['signupCode']);
$code = $DBLIB->getOne("signupCodes",["signupCodes_id"]);
if (!$code) finish(true,null,["taken"=>false]);
else finish(true,null,["taken"=>true]);