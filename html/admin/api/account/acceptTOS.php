<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->where ('users_userid', $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_termsAccepted" => date("Y-m-d H:i:s")])) {
    $bCMS->auditLog("UPDATE", "users", "ACCEPT TOS", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    finish(true);
} else finish(false);