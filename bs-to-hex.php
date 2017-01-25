<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$br = new Gizmo\BinaryReader($argv[1], false);
for ($i = 0; $i <= 7; $i++) {
    $br->seek($i);
    print $i . ' => ';
    while ($br->position < $br->size() - 7) {
        $val = bindec(strrev($br->readBits(8)));
        if ($val >= 32 && $val <= 126) {
            print chr($val);
        }
        else {
            print " ";
        }
    }
    print "\n";
}
