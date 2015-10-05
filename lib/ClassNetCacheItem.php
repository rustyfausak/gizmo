<?php

namespace Gizmo;

class ClassNetCacheItem
{
	/* @var int */
	public $classId;
	/* @var int */
	public $parentNetId;
	/* @var int */
	public $netId;
	/* @var array of PropertyMapItem */
	public $propertyMap;

	/**
	 * @param int $classId
	 * @param int $parentNetId
	 * @param int $netId
	 */
	public function __construct($classId, $parentNetId, $netId)
	{
		$this->classId = $classId;
		$this->parentNetId = $parentNetId;
		$this->netId = null;
		if ($netId != $parentNetId) {
			$this->netId = $netId;
		}
		$this->propertyMap = [];
	}
}
