<?php

namespace Gizmo;

class BinaryReader
{
    /* @var int */
    public $position;
    /* @var int */
    public $offset;
    /* @var string */
    public $binary_str;
    /* @var bool */
    public $little_endian;

    /**
     * @param string $binary_str
     * @param bool $little_endian
     */
    public function __construct($binary_str = '', $little_endian = true)
    {
        $this->position = 0;
        $this->offset = 0;
        $this->binary_str = $binary_str;
        $this->little_endian = $little_endian;
    }

    /**
     * Returns the length of the binary string.
     *
     * @return int
     */
    public function size()
    {
        return strlen($this->binary_str);
    }

    /**
     * Add bits to the end of the binary string.
     *
     * @param int $bits
     */
    public function append($bits)
    {
        $this->binary_str .= $bits;
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
        return substr($this->binary_str, $this->offset, 1);
    }

    /**
     * Seek to a position in the binary string.
     *
     * @param int $position
     */
    public function seek($position)
    {
        $this->position = $position;
        if ($this->little_endian) {
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
    public static function asBits($data, $little_endian = true)
    {
        $str = '';
        for ($i = 0; $i < strlen($data); $i += 2) {
            $chr = substr($data, $i, 2);
            if ($little_endian) {
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
}
