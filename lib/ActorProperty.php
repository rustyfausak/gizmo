<?php

namespace Gizmo;

class ActorProperty
{
    /* @var int */
    public $id;
    /* @var string */
    public $class;
    /* @var mixed */
    public $value;

    /**
     * @param Replay $replay
     * @param BinaryReader $br
     * @return ActorProperty
     */
    public static function deserialize($replay, $br)
    {
        return new self();
    }

    /**
     * @param int $id
     * @param string $class
     */
    public function __construct($id, $class)
    {
        $this->id = $id;
        $this->class = $class;
        $this->value = self::getDefault($class);
    }

    /**
     * @param string $class
     * @return mixed
     */
    public static function getDefault($class)
    {
       return null;
    }
}
