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
		}
		else {
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
	 * Returns the unsigned int representation of the given bit string.
	 *
	 * @param string $bits
	 * @return int
	 */
	public static function asInt($bits)
	{
		$val = 0;
		for ($i = 0; $i < strlen($bits); $i++) {
			$val += $bits[$i] ? pow(2, $i) : 0;
		}
		return $val;
	}
}
