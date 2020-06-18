<?php
//Change the instance the user is logged in to
require_once __DIR__ . '/../apiHeadSecure.php';

if ($GLOBALS['AUTH']->setInstance($_POST['instances_id'])) finish(true);
else finish(false);
