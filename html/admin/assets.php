<?php
require_once __DIR__ . '/common/headSecure.php';

echo $TWIG->render('assets.twig', $PAGEDATA);