<?php

$loader = require __DIR__ . '/vendor/autoload.php';

if (!isset($argv[1])) {
    die('Usage: ' . basename(__FILE__) . " <replay file>\n\n");
}

$replay = Gizmo\Parser::parse($argv[1]);
$replay->frameData = null;
var_dump($replay);
