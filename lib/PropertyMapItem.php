<?php

namespace Gizmo;

class PropertyMapItem
{
    /* @var int */
    public $object_id;
    /* @var int */
    public $netId;

    /**
     * @param int $object_id
     * @param int $netId
     */
    public function __construct($object_id, $netId)
    {
        $this->object_id = $object_id;
        $this->netId = $netId;
    }
}
