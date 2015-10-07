<?php

namespace Gizmo;

class Message
{
    /* @var int */
    public $frameNumber;
    /* @var string */
    public $name;
    /* @var string */
    public $message;

    /**
     * @param int $frameNumber
     * @param string $name
     * @param string $message
     */
    public function __construct($frameNumber, $name, $message)
    {
        $this->frameNumber = $frameNumber;
        $this->name = $name;
        $this->message = $message;
    }
}
