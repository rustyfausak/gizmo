<?php

namespace Gizmo;

class Frame
{
    /* @var float */
    public $time;
    /* @var float */
    public $diff;
    /* @var array of Replication */
    public $replications;

    /**
     * @param BinaryReader $br
     * @return Frame
     */
    public static function deserialize($br)
    {
        $frame = new self();
        $frame->time = $br->readFloat();
        $frame->diff = $br->readFloat();
        while ($br->readBit() == 1) {
            $frame->replications[] = Replication::deserialize($br);
            break;
        }
        return $frame;
    }
}
