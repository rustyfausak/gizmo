<?php

namespace Gizmo;

class Replay
{
    /* @var array of Property */
    public $properties;
    /* @var array of string */
    public $levels;
    /* @var array of KeyFrame */
    public $keyFrames;
    /* @var array of Message */
    public $debugLog;
    /* @var array of Tick */
    public $ticks;
    /* @var array of string */
    public $packages;
    /* @var array of string */
    public $objects;
    /* @var array of string */
    public $names;
    /* @var array */
    public $classMap;
    /* @var array of ClassNetCacheItem */
    public $classNetCache;
    /* @var string */
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
