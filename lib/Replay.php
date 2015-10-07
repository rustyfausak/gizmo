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
    /* @var array map int actor ID to array Actors */
    public $actors;

    /**
     */
    public function __construct()
    {
        $this->actors = [];
    }

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
     * @param int $actorId
     * @param int $objectId
     * @return Actor
     */
    public function createActor($actorId, $objectId, $frameNumber)
    {
        $actor = new Actor(
            $actorId,
            $this->objects[$objectId],
            $frameNumber
        );
        $classObjectId = array_search($actor->class, $this->objects);
        if ($classObjectId && array_key_exists($classObjectId, $this->cache)) {
           foreach ($this->cache[$classObjectId]['propertyMap'] as $propertyId => $propertyClass) {
                $actor->properties[] = new ActorProperty($propertyId, $propertyClass);
           }
        }
        $this->actors[] = $actor;
        return $actor;
    }

    /**
     * @param int $actorId
     * @return Actor
     */
    public function getActor($actorId)
    {
        foreach (array_reverse($this->actors) as $actor) {
            if ($actor->id == $actorId) {
                return $actor;
            }
        }
        throw new \Exception('Could not find actor with ID "' . $actorId . '"');
    }

    /**
     * @param int $actorId
     */
    public function closeActor($actorId, $frameNumber)
    {
        foreach (array_reverse($this->actors) as $actor) {
            if ($actor->id == $actorId) {
                $actor->close($frameNumber);
                return;
            }
        }
        $this->actors[$actorId]->close($frameNumber);
    }

    /**
     * Returns the full property map for the given branch ID.
     *
     * @param int $branchId
     * @return array
     */
    public function getPropertyMapForBranch($branchId)
    {
        foreach ($this->propertyTree as $branch) {
            if ($branch->id == $branchId) {
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
