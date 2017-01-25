<?php

$loader = require __DIR__ . '/vendor/autoload.php';

print Gizmo\BinaryReader::bitsToRepresent($argv[1]) . "\n";
