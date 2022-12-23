<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$icons = json_decode(file_get_contents(__DIR__ . '/icons.json'), true);

if (isset($_POST['search'])) {
    $icons = array_filter($icons, function($icon) {
        return strpos($icon['code'], $_POST['search']) !== false;
    });
}

$icons = array_slice($icons, 0, 20);

finish(true, null, $icons);