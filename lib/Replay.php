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
    /* @var mixed binary data */
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
}
