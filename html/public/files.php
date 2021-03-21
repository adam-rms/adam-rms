<?php
require_once 'common/head.php';

if ($PAGEDATA['INSTANCE']['FILES']) echo $TWIG->render('filesPublic.twig', $PAGEDATA);
?>