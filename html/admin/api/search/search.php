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

/** @OA\Get(
 *     path="/search/search.php", 
 *     summary="Global Search", 
 *     description="Search for a term across the whole RMS
", 
 *     operationId="search", 
 *     tags={"search"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="term",
 *         in="query",
 *         description="Search Term",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="offset",
 *         in="query",
 *         description="Offset",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="limit",
 *         in="query",
 *         description="Limit",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */