<?php

namespace Gizmo;

class Tick
{
    /* @var string */
    public $type;
    /* @var int */
    public $frameNumber;

    /**
     * @param string $type
     * @param int $frameNumber
     */
    public function __construct($type, $frameNumber)
    {
        $this->type = $type;
        $this->frameNumber = $frameNumber;
    }
}
