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
        $bits = bindec(strrev($br->readBits(4)));
        $bias = 1 << ($bits + 1);
        $x = bindec(strrev($br->readBits($bits + 2))) - $bias;
        $y = bindec(strrev($br->readBits($bits + 2))) - $bias;
        $z = bindec(strrev($br->readBits($bits + 2))) - $bias;
        return new self($x, $y, $z);
    }

    /**
     * @param BinaryReader $br
     * @return Vector
     */
    public static function deserializeByteVector($br)
    {
        return new self(
            bindec(strrev($br->readBits(8))) / 128,
            bindec(strrev($br->readBits(8))) / 128,
            bindec(strrev($br->readBits(8))) / 128
        );
    }

    /**
     * @param BinaryReader $br
     * @return Vector
     */
    public static function deserializeOrientation($br)
    {
        $p = 0;
        $y = 0;
        $r = 0;
        if ($br->readBit()) {
            $p = bindec(strrev($br->readBits(8)));
        }
        if ($br->readBit()) {
            $y = bindec(strrev($br->readBits(8)));
        }
        if ($br->readBit()) {
            $r = bindec(strrev($br->readBits(8)));
        }
        return new self($p, $y, $r);
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

    /**
     * @return string
     */
    public function __toString()
    {
        return 'v3f{' . $this->x . ', ' . $this->y . ', ' . $this->z . '}';
    }
}
