<?php
require_once __DIR__ . '/apiHead.php';

$positionsGroups = $DBLIB->getValue("positionsGroups", "count(*)");
if (!$positionsGroups or $positionsGroups < 1) {
  http_response_code(500);
  die("ERROR");
} else {
  http_response_code(200);
  die("OK");
}
