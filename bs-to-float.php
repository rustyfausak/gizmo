<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$br = new Gizmo\BinaryReader($argv[1], false);
for ($i = 0; $i < $br->size() - 31; $i++) {
    $br->seek($i);
    $float = $br->readFloat();
    if ($float > 0.0001 && $float < 1000) {
        print "{$i}. " . $float . "\n";
    }
}
