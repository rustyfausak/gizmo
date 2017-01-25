<?php

namespace Gizmo;

class PropertyBranch
{
    /* @var int */
    public $classId;
    /* @var int */
    public $parentId;
    /* @var int */
    public $id;
    /* @var array map int object ID to int network ID */
    public $propertyMap;

    /**
     * @param int $classId
     * @param int $parentId
     * @param int $id
     */
    public function __construct($classId, $parentId, $id)
    {
        $this->classId = $classId;
        $this->parentId = null;
        $this->id = null;
        $this->oId = $id;
        $this->oParentId = $parentId;
        if (!$id) {
            $this->id = 0;
        }
        else {
            $this->parentId = $parentId;
            if ($id != $parentId) {
                $this->id = $id;
            }
        }
        $this->propertyMap = [];
    }
}
