<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->where("users_userid", $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_dark_mode" => isset($_POST['dark']) ? 1 : 0])) finish(true);
else finish(false);


