<?php
require_once 'head.php';
if (isset($PAGEDATA['INSTANCE']['publicData']['enableAssets']) and $PAGEDATA['INSTANCE']['publicData']['enableAssets']) echo $TWIG->render('public/embed/assetsPublic.twig', $PAGEDATA);
else die("Disabled by your AdamRMS Administrator")
?>