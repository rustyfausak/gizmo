<?php

namespace Gizmo;

class Vector
{
    /**
     * @param BinaryReader $br
     * @return Vector
     */
    public static function deserialize($br)
    {
        $bits = bindec($br->readBits(5));
        $max = 1 << ($bits + 2);
        $x = bindec($br->readBits($max));
        $y = bindec($br->readBits($max));
        $z = bindec($br->readBits($max));
        return new self($x, $y, $z);
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $z
     */
    public function __construct($x, $y, $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}
