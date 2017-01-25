<?php

namespace Gizmo;

class Frame
{
    /* @var int */
    public $number;
    /* @var float */
    public $time;
    /* @var float */
    public $diff;
    /* @var array of Replication */
    public $replications;

    /**
     * @param Replay $replay
     * @param BinaryReader $br
     * @param int $number
     * @return Frame
     */
    public static function deserialize($replay, $br, $number)
    {
        $frame = new self($number);
        $frame->time = $br->readFloat();
        $frame->diff = $br->readFloat();
        while ($br->readBit() == 1) {
            $frame->replications[] = Replication::deserialize($replay, $br, $frame->number);
        }
        return $frame;
    }

    /**
     * @param int $number
     */
    public function __construct($number)
    {
        $this->number = $number;
    }
}
