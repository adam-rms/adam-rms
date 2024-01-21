<?php
header('Location: barcodes/search.php');

/** @OA\Get(
 *     path="/assets/searchAssetsBarcode.php", 
 *     summary="Search Assets by Barcode", 
 *     description="Redirects to assets/barcodes/search.php", 
 *     operationId="searchAssetsBarcode", 
 *     @OA\Tag(name="assets"), 
 *     @OA\Response(
 *         response="308", 
 *         description="Redirect",
 *     ), 
 *     )
 */