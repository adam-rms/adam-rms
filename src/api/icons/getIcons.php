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

/**
 *  @OA\Post(
 *      path="/icons/getIcons.php",
 *      summary="List Icons",
 *      description="Get a list of the first 20 available icons",
 *      operationId="getIcons",
 *      tags={"icons"},
 *       @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="search",
 *          in="query",
 *          description="Icon search term",
 *          required="false",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *  )
 */