<?php

namespace Gizmo;

class KeyFrame
{
    /* @var float */
    public $time;
    /* @var int */
    public $frameNumber;
    /* @var int */
    public $position;

    /**
     * @param float $time
     * @param int $frameNumber
     * @param int $position
     */
    public function __construct($time, $frameNumber, $position)
    {
        $this->time = $time;
        $this->frameNumber = $frameNumber;
        $this->position = $position;
    }
}
