<?php

$loader = require __DIR__ . '/vendor/autoload.php';

if (!isset($argv[1])) {
    die('Usage: ' . basename(__FILE__) . " <replay file>\n\n");
}

$output_dir = __DIR__ . '/results';

if (!is_dir($output_dir)) {
	mkdir($output_dir);
}

$replay = Gizmo\Parser::parse($argv[1]);
$replay->frameData = null;
file_put_contents(
	$output_dir . '/' . $replay->getPropertyValue('Id') . '.json',
	json_encode($replay, JSON_PRETTY_PRINT)
);
