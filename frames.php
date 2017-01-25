<?php

$loader = require __DIR__ . '/vendor/autoload.php';

$dir = 'c:/rocket-league-frames';

foreach (scandir($dir) as $subdir) {
    if (in_array($subdir, ['.', '..'])) {
        continue;
    }
    if (is_dir($dir . '/' . $subdir)) {
        foreach (scandir($dir . '/' . $subdir) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            doFrameFile($dir . '/' . $subdir . '/' . $file);
        }
    }
    else {
        doFrameFile($dir . '/' . $subdir);
    }
}

function doFrameFile($path)
{
    $info = pathinfo(dirname($path));
    if (!file_exists('results/' . $info['filename'] . '.json')) {
        return;
    }
    $br = new Gizmo\BinaryReader(file_get_contents($path), false);
    $frame = Gizmo\Frame::deserialize($br);
    dumpframe($frame);
}

function dumpFrame($frame)
{
    print "Frame@" . sprintf("%01.3f", round($frame->time, 3));
    print "+" . sprintf("%01.3f", round($frame->diff, 3));
    //print "\n";
    foreach ($frame->replications as $r) {
        dumpReplication($r);
    }
}

function dumpReplication($r)
{
    print "\tR#" . $r->actorId . ":" . ($r->channelState ? 'open' : 'closed');
    if (!$r->channelState) {
        return;
    }
    print ":" . ($r->actorState ? 'new' : 'existing') . ":" . $r->actorObjectId . ":" . $r->unknown1;
    print "\t" . ($r->vector ? implode(',', get_object_vars($r->vector)) : '');
    print "\t" . $r->next . "\n";
}
