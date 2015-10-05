<?php

namespace Gizmo;

class KeyFrame
{
    /* @var float */
    public $time;
    /* @var int */
    public $frame;
    /* @var int */
    public $position;

    /**
     * @param float $time
     * @param int $frame
     * @param int $position
     */
    public function __construct($time, $frame, $position)
    {
        $this->time = $time;
        $this->frame = $frame;
        $this->position = $position;
    }
}
