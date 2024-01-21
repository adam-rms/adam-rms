<?php
require_once __DIR__ . '../../../common/head.php';

//Documentation headers etc. This page doesn't actually render any content


//Defines for the top level api documentation
define('VERSION', $CONFIG['VERSION']['TAG']);

/**
 * @OA\Info(
 *  title="AdamRMS API", 
 *  version=VERSION,
 *  description="AdamRMS is a free, open source advanced Rental Management System for Theatre, AV & Broadcast. This is the API listing for the v1 API, which is currently in Production and active development. You can find out more about AdamRMS at [https://adam-rms.com](https://adam-rms.com).",
 *  termsOfService="https://adam-rms.com/legal",
 *  @OA\Contact(
 *   name="AdamRMS Support",
 *   email="support@adam-rms.com",
 *   url="https://github.com/adam-rms",
 *  ),
 *  @OA\License(
 *   name="GNU Affero General Public License v3.0",
 *   url="https://github.com/adam-rms/adam-rms/blob/main/LICENSE",
 *  )
 * )
 */

/**
 * @OA\Server(
 *  description="Production",
 *  url="https://dash.adam-rms.com/api/",
 * )
 */

/**
 * @OA\ExternalDocumentation(
 *  description="AdamRMS Documentation",
 *  url="https://adam-rms.com",
 * )
 */

/**
 *  @OA\Schema(
 *      schema="SimpleResponse",
 *      description="Simple Response Model, with a boolean status and optional error object",
 *      title="Simple Response Model",
 *      @OA\Property(
 *          property="result", 
 *          type="boolean", 
 *          description="Whether the request was successful",
 *      ),
 *      @OA\Property(
 *          property="error", 
 *          type="array", 
 *          description="An Array containing an error code and a message",
 *      ),     
 *  )
 */

