<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['instances_id'])) {
    finish($GLOBALS['AUTH']->setInstance($_POST['instances_id']), null, null);
}

finish(false, ["code" => "PARAM-ERROR", "message" => "Provide an instance id"], null);
