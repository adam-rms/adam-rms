<?php
require_once 'common/head.php';
if (isset($PAGEDATA['INSTANCE']['publicData']['enableAssets']) and $PAGEDATA['INSTANCE']['publicData']['enableAssets']) echo $TWIG->render('assetsPublic.twig', $PAGEDATA);
?>