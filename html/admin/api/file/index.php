<?php
require_once __DIR__ . '/../apiHead.php';
/*
 * File interface for Amazon AWS S3.
 *  Parameters
 *      f (required) - the file id as specified in the database
 *      s (filesize) - false to get the original - available is "tiny" (50px) "small" (100px) "medium" (500px) "large" (1000px)
 *      d (optional, default false) - should a download be forced or should it be displayed in the browser? (if set it will download)
 *      r (optional, default false) - should the url be returned by the script as plain text or a redirect triggered? (if set it will redirect)
 *      e (optional, default 1 minute) - when should the link expire? Must be a string describing how long in words basically. If this file type has security features then it will default to 1 minute.
 */
$file = $bCMS->s3URL($_POST['f'], (isset($_POST['s']) ? $_POST['s'] : null), (isset($_POST['d'])),(isset($_POST['e']) ? $_POST['e'] : null));
if (!$file) finish(false,["message"=>"File not found - please check to ensure you are still logged in"]);
else {
    if (isset($_POST['r'])) {
        header("Location: " . $file);
        die();
    } else finish(true,null,["url" => $file]);
}