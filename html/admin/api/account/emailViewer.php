<?php
require_once __DIR__ . '/../apiHeadSecure.php';
header('Content-type: text/html');
if (!$AUTH->serverPermissionCheck("USERS:VIEW:MAILINGS")) die("Sorry you don't have access to this");
	$PAGEDATA['title'] = "E-Mail Viewer";
	$output = '<style>
					* {
						font-family: sans-serif !important;
						font-size: 14px
					}
					.pagebreak { page-break-before: always; }
				</style>';
	if (isset($_POST['email']) and  $_POST['email'] != '') {
		$DBLIB->where ('emailSent_id IN (' . $bCMS->sanitizeStringMYSQL($_POST['email']) . ')');
		$emails = $DBLIB->get('emailSent');
		if (!isset($emails[0])) die('E-Mail not found');
	} else die('Nothing to see here!');
	foreach ($emails as $email) {
		$output .= '
		<table border="0" style="width: 100%;">
			<tr>
				<td><b>From:</b></td>
				<td>' . $email['emailSent_fromName'] . ' [' . $email['emailSent_fromEmail'] . ']'. '</td>
			</tr>
			<tr>
				<td><b>Sent:</b></td>
				<td>' . date("l, F j, Y h:i A", strtotime($email['emailSent_sent'])). ' - Email ID ' . $email['emailSent_id'] . '</td>
			</tr>
			<tr>
				<td><b>To:</b></td>
				<td>' . $email['emailSent_toName'] . ' (' . $email['users_userid'] . ') [' . $email['emailSent_toEmail'] . ']'. '</td>
			</tr>
			<tr>
				<td><b>Subject:</b></td>
				<td>' . $email['emailSent_subject']. '</td>
			</tr>
			<tr>
				<td colspan="2"><iframe style="width:100%;height:580px;border:0;" srcdoc="' . str_replace(array("\n", "\r"), '', htmlspecialchars($email['emailSent_html'])). '"></iframe></td>
		</table>
		<hr /><div class="pagebreak"> </div>';
	}

	echo $output;

/** @OA\Get(
 *     path="/account/emailViewer.php", 
 *     summary="Email Viewer", 
 *     description="Get the HTML of an email  
Requires server permission USERS:VIEW:MAILINGS", 
 *     operationId="getEmailViewer", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="OK",
 *         @OA\MediaType(
 *             mediaType="text/html", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Not Found",
 *     ), 
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
