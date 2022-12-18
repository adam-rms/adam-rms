<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['limit'])) $_POST['limit'] = 20;

$icons = json_decode(file_get_contents(__DIR__ . '/icons.json'), true);

if (isset($_POST['search'])) {
    $icons = array_filter($icons, function($icon) {
        return strpos($icon['text'], $_POST['search']) !== false;
    });
}

$icons = array_slice($icons, 0, $_POST['limit']);

//format for select2
foreach ($icons as $key => &$value) {
    $value['id'] = 'fas fa-'.$value['text'];
}

$return = new stdClass();
$return->results = $icons;

finish(true, null, $return);