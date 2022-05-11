<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Export", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(133)) die($TWIG->render('404.twig', $PAGEDATA));

/**
 * READ ME!
 * There is a lot of hardcoded data in this file and this is for a reason!
 * Whilst it is very possible to just get columns from a database, there is a lot of 
 * information that either is AdamRMS specific, or should not be exportable from a system.
 */

//What table is being exported, and can the current user access it?
//tables: Permission => [Public Name, Table Name]
$tables = [
    59 => ["Assets", "assets"],
    20 => ["Projects", "projects"]
];

$PAGEDATA['tables'] = [];

foreach ($tables as $permssion => $names) {
    if($AUTH->instancePermissionCheck($permssion)) {
        array_push($PAGEDATA['tables'], $names);
    }
}

echo $TWIG->render('instances/export.twig', $PAGEDATA);
?>