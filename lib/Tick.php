<?php

namespace Gizmo;

class Tick
{
    /* @var string */
    public $type;
    /* @var int */
    public $frame;

    /**
     * @param string $type
     * @param int $frame
     */
    public function __construct($type, $frame)
    {
        $this->type = $type;
        $this->frame = $frame;
    }
}
