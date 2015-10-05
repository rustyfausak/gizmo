<?php

namespace Gizmo;

class Replay
{
    /* @var string */
    public $version;
    /* @var string */
    public $type;
    /* @var array of Property */
    public $properties;
    /* @var array of string */
    public $levels;
    /* @var array of KeyFrame */
    public $keyFrames;
    /* @var array of Message */
    public $log;
    /* @var array of Tick */
    public $ticks;
    /* @var array of string */
    public $packages;
    /* @var array map int object ID to string name */
    public $objects;
    /* @var array of string */
    public $names;
    /* @var array map int class ID to string name */
    public $classes;
    /* @var array of PropertyBranch */
    public $propertyTree;
    /* @var array map int class ID to array */
    public $cache;
    /* @var binary string */
    public $frameData;

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getPropertyValue($name, $default = null)
    {
        foreach ($this->properties as $property) {
            if ($property->name == $name) {
                return $property->value;
            }
        }
        return $default;
    }

    /**
     * Builds the network cache containing the class IDs, names and property
     * maps.
     */
    public function buildCache()
    {
        $this->cache = [];
        foreach ($this->propertyTree as $branch) {
            $this->cache[$branch->classId] = [
                'class' => $this->classes[$branch->classId],
                'propertyMap' => $this->getPropertyMapForBranch(
                    $branch->id ? $branch->id : $branch->parentId
                )
            ];
        }
    }

    /**
     * Returns the full property map for the given branch ID.
     *
     * @param int $branch_id
     * @return array
     */
    public function getPropertyMapForBranch($branch_id)
    {
        foreach ($this->propertyTree as $branch) {
            if ($branch->id == $branch_id) {
                $properties = [];
                if ($branch->parentId) {
                    $properties = self::getPropertyMapForBranch($branch->parentId);
                }
                foreach ($branch->propertyMap as $objectId => $networkId) {
                    $properties[$networkId] = $this->objects[$objectId];
                }
                return $properties;
            }
        }
        return [];
    }
}
