<?php

//Documentation headers etc. This page doesn"t actually render any content

/**
 * @OA\Info(
 *  title="AdamRMS API", 
 *  version="APIVERSION",
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

/**
 * @OA\Tag(name="authentication", description="Authentication"),
 * @OA\Tag(name="account", description="User account management"),
 * @OA\Tag(name="permissions", description="Instance and global prmission management"),
 * @OA\Tag(name="assets", description="Asset management"),
 * @OA\Tag(name="groups", description="Asset Groups"),
 * @OA\Tag(name="maintenance", description="Asset Maintenance"),
 * @OA\Tag(name="maintenanceJobs", description="Asset Maintenance Jobs"),
 * @OA\Tag(name="manufacturers", description="Asset Manufacturers"),
 * @OA\Tag(name="barcodes", description="Asset Barcodes"),
 * @OA\Tag(name="categories", description="Asset Categories"),
 * @OA\Tag(name="projects", description="Project Management"),
 * @OA\Tag(name="crew", description="Project Crew Management"),
 * @OA\Tag(name="recruitment", description="Project Crew Recruitment"),
 * @OA\Tag(name="project_assets", description="Project Asset Management"),
 * @OA\Tag(name="instances", description="Instance Management"),
 * @OA\Tag(name="locations", description="Business Locations"),
 * @OA\Tag(name="clients", description="Client Management"),
 * @OA\Tag(name="training", description="Training"),
 * @OA\Tag(name="modules", description="Training Modules"),
 * @OA\Tag(name="module_steps", description="Module Steps"),
 * @OA\Tag(name="cms", description="CMS Page Management"),
 * @OA\Tag(name="file_uploads", description="Handles interaction with s3 Buckets"),
 * @OA\Tag(name="notifications", description="v1 notification endpoints - These endpoints are not accessible from the web, and are instead used internally."),
 * @OA\Tag(name="assetAssignmentStatus", description="Asset Assignment Statuses"),
 * @OA\Tag(name="projectTypes", description="Project Types"),
 * @OA\Tag(name="projectStatus", description="Project Statuses"),
 * @OA\Tag(name="signupCodes", description="Signup Codes"),
 * @OA\Tag(name="s3files", description="S3 File Management"),
 * @OA\Tag(name="search", description="Global Search"),
 * @OA\Tag(name="icons", description="Internal Icon Library"),
 */
