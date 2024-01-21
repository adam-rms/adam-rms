<?php
require_once __DIR__ . '/../apiHead.php';
require_once __DIR__ . '/../../common/libs/Search/Search.php';

// Get the GET variables
$term = $_GET['term'] ? $_GET['term'] : "";
$offset = is_numeric($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = is_numeric($_GET['limit']) ? intval($_GET['limit']) : 20;


try {
    //Run the search
    $search = new Search();
    $results = $search->search($term, $limit, $offset);
    finish(true, null, $results);
}
catch (ParseError $e) {
    finish(false, ["Not Authenticated"]);
}
catch (ValueError $e) {
    finish(false, ["message"=>"Business not found"]);
}
catch (Exception $e) {
    throw $e;
}
