<?php

namespace Gizmo;

class Message
{
	/* @var int */
	public $frame;
	/* @var string */
	public $name;
	/* @var string */
	public $message;

	/**
	 * @param int $frame
	 * @param string $name
	 * @param string $message
	 */
	public function __construct($frame, $name, $message)
	{
		$this->frame = $frame;
		$this->name = $name;
		$this->message = $message;
	}
}
