<?php

use Twig\TwigFunction;

require_once __DIR__ . '/common/headSecure.php';
require_once __DIR__ . '/../common/libs/Search/Search.php';

// Get the optional variables
$PAGEDATA['term'] = isset($_GET['term']) && is_string($_GET['term']) ? $_GET['term'] : "";
$PAGEDATA['offset'] = isset($_GET['offset']) && is_numeric($_GET['offset']) ? intval($_GET['offset']) : 0;
$PAGEDATA['limit'] = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : 25;
$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['term'] ? "Search result for: " . $PAGEDATA['term'] : "Search" , "BREADCRUMB" => true];


/**
 * Define a custom twig function to use a swtich case to generate the url the result should resolve to
 */
$function = new TwigFunction('generate_result_url', function ($result) {
    switch ($result['type']){
        case "project":
            return "/project?id=".$result['data']['projects_id'];
        case "location":
            return "/location/";
        case "client":
            return "/clients.php";
        case "page":
            return "/cms/?p=".$result['data']['cmsPages_id'];
        default:
            return null;
    }
});
$TWIG->addFunction($function);

/**
 * Define a custom twig function to define the colour and text of the tag in the result
 */
$function = new TwigFunction('generate_result_tag', function ($result) {
    switch ($result['type']){
        case "project":
            return ["pink", "Project"];
        case "location":
            return ["indigo", "Location"];
        case "client":
            return ["lightblue", "Client"];
        case "page":
            return ["primary", "CMS Page"];
        default:
            return null;
    }
});
$TWIG->addFunction($function);


//Run the search if there is a ter
if ($PAGEDATA['term']) {
    $search = new Search();
    $PAGEDATA['results'] = $search->search($PAGEDATA['term'], $PAGEDATA['limit'],  $PAGEDATA['offset']);
}

// Render the page
echo $TWIG->render('search.twig', $PAGEDATA);
