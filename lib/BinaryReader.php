<?php

namespace Gizmo;

class BinaryReader
{
    /* @var int */
    public $position;
    /* @var int */
    public $offset;
    /* @var string */
    public $bitstring;
    /* @var bool */
    public $littleEndian;

    /**
     * @param string $bitstring
     * @param bool $littleEndian
     */
    public function __construct($bitstring = '', $littleEndian = true)
    {
        $this->position = 0;
        $this->offset = 0;
        $this->bitstring = $bitstring;
        $this->littleEndian = $littleEndian;
    }

    /**
     * Returns the length of the binary string.
     *
     * @return int
     */
    public function size()
    {
        return strlen($this->bitstring);
    }

    /**
     * Add bits to the end of the binary string.
     *
     * @param int $bits
     */
    public function append($bits)
    {
        $this->bitstring .= $bits;
    }

    /**
     * Read and return a float.
     *
     * @return float
     */
    public function readFloat()
    {
        return self::asFloat(strrev($this->readBits(32)));
    }

    /**
     * Read and return an int.
     *
     * @param int $count
     * @return int
     */
    public function readInt($count)
    {
        return bindec(strrev($this->readBits($count)));
    }

    /**
     * If reading the last bit of a number as 1 would put the number over $max,
     * don't read that bit and assume it is 0.
     *
     * @param int $max
     * @return int
     */
    public function readSmartInt($max)
    {
        $count = self::numBitsToRepresent($max);
        $check = $this->readInt($count - 1);
        $last_bit_val = pow(2, $count - 1);
        if ($check + $last_bit_val > $max) {
            return $check;
        }
        return intval($check + ($this->readBit() ? $last_bit_val : 0));
    }

    /**
     * Read and return a string.
     *
     * @return string
     */
    public function readString()
    {
        $str = '';
        $size = $this->readInt(32);
        for ($i = 0; $i < $size; $i++) {
            $str .= chr($this->readInt(8));
        }
        return $str;
    }

    /**
     * Read and return a number of bits.
     *
     * @param int $count
     * @return string
     */
    public function readBits($count)
    {
        $bits = '';
        for ($i = 0; $i < $count; $i++) {
            $bits .= $this->readBit();
        }
        return $bits;
    }

    /**
     * Read and return a single bit.
     *
     * @return string
     */
    public function readBit()
    {
        $this->seek($this->position);
        $this->position++;
        return substr($this->bitstring, $this->offset, 1);
    }

    /**
     * Seek to a position in the binary string.
     *
     * @param int $position
     */
    public function seek($position)
    {
        $this->position = $position;
        if ($this->littleEndian) {
            $this->offset = ceil(($position + 1) / 8) * 8 - 1 - ($position % 8);
        } else {
            $this->offset = $this->position;
        }
    }

    /**
     * Returns the float representation of the given bit string of length 32.
     *
     * @param string $bits
     * @return float
     */
    public static function asFloat($bits)
    {
        assert(strlen($bits) == 32);
        $sign = bindec(substr($bits, 0, 1));
        $exponent = bindec(substr($bits, 1, 8));
        $fraction = bindec(substr($bits, 9));
        return pow(-1, $sign) * (1 + $fraction * pow(2, -23)) * pow(2, $exponent - 127);
    }

    /**
     * @param binary string $data
     * @return string
     */
    public static function asBits($data, $littleEndian = true)
    {
        $str = '';
        for ($i = 0; $i < strlen($data); $i += 2) {
            $chr = substr($data, $i, 2);
            if ($littleEndian) {
                $str .= strrev(sprintf("%08b", hexdec($chr)));
            }
            else {
                $str .= sprintf("%08b", hexdec($chr));
            }
            continue;
        }
        return $str;
    }

    /**
     * Swaps the endian-ness of the bytes in the given bits.
     *
     * @param string $bits
     * @return string
     */
    public static function swapEndian($bits)
    {
        $swap = '';
        for ($j = 0; $j < strlen($bits); $j += 8) {
            for ($i = 7; $i >= 0; $i--) {
                if (strlen($bits) - 1 < $i + $j) {
                    $swap .= '0';
                }
                else {
                    $swap .= $bits[$i + $j];
                }
            }
        }
        return $swap;
    }

    /**
     * @param string $bits
     * @return string
     */
    public static function pretty($bits)
    {
        $tmp = '';
        for ($i = 0; $i < strlen($bits); $i++) {
            $tmp .= substr($bits, $i, 1);
            if (($i + 1) % 8 == 0) {
                $tmp .= ' ';
            }
        }
        return $tmp;
    }

    /**
     * Returns the number of bits needed to represent an int of the given value.
     *
     * @param int $int
     * @return int
     */
    public static function numBitsToRepresent($int)
    {
        return ceil(log($int + 1, 2));
    }
}
