<?php
require_once '../common/head.php';

$PAGEDATA['url'] = $bCMS->s3URL($_GET['f'], "+20 minutes", false,null,$_GET['key']);
if (!$PAGEDATA['url']) die($TWIG->render('404Public.twig', $PAGEDATA));

$DBLIB->where("s3files_id", $_GET['f']);
$DBLIB->where("instances_id", $PAGEDATA['INSTANCE']['instances_id']);
$PAGEDATA['file'] = $DBLIB->getone("s3files");

echo $TWIG->render('file/filePublic.twig', $PAGEDATA);
?>