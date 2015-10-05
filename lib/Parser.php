<?php

namespace Gizmo;

class Parser
{
    /**
     * @param string $path
     * @return Replay
     */
    public static function parse($path)
    {
        $replay = new Replay();

        if (!file_exists($path)) {
            throw new \Exception('Cannot parse file "' . $path . '"');
        }

        $handle = fopen($path, 'rb');

        // Skip unknown data
        fseek($handle, 16, 1);

        // Skip "TAGame.Replay_Soccar_TA"
        self::readString($handle);

        $replay->properties = self::readProperties($handle);

        // Skip unknown data
        fseek($handle, 8, 1);

        $replay->levels = self::readStrings($handle);
        $replay->keyFrames = self::readKeyFrames($handle);
        if (sizeof($replay->keyFrames)) {
            $replay->frameData = self::readFrames($handle);
        }
        $replay->debugLog = self::readDebugLog($handle);
        $replay->ticks = self::readTicks($handle);
        $replay->packages = self::readStrings($handle);
        $replay->objects = self::readStrings($handle);
        $replay->objects["_"] = null; // Force string keys
        $replay->names = self::readStrings($handle);
        $replay->classMap = self::readClassMap($handle);
        $replay->classNetCache = self::readClassNetCache($handle);

        fclose($handle);

        return $replay;
    }

    /**
     * @param resource $handle
     * @return array of Property
     */
    public static function readProperties($handle)
    {
        $properties = [];
        while (true) {
            $property = self::readProperty($handle);
            if (!$property) {
                break;
            }
            $properties[] = $property;
        }
        return $properties;
    }

    /**
     * @param resource $handle
     * @return Property|null
     */
    public static function readProperty($handle)
    {
        $property = new Property();
        $property->name = self::readString($handle);

        if ($property->name == 'None') {
            return null;
        }

        $property->type = self::readString($handle);

        switch ($property->type) {
            case 'IntProperty':
                $length = self::readInt($handle, 8);
                $property->value = self::readInt($handle, $length);
                break;
            case 'FloatProperty':
                $length = self::readInt($handle, 8);
                $property->value = self::readFloat($handle, $length);
                break;
            case 'StrProperty':
            case 'NameProperty':
                fseek($handle, 8, 1);
                $property->value = self::readString($handle);
                break;
            case 'ArrayProperty':
                fseek($handle, 8, 1);
                $length = self::readInt($handle, 4);
                $property->value = [];
                foreach (range(1, $length) as $i) {
                    $property->value[] = self::readProperties($handle);
                }
                break;
            default:
                throw new \Exception('Unexpected property type "' . $property->type . '"');
                break;
        }

        return $property;
    }

    /**
     * @param resource $handle
     * @return array of KeyFrame
     */
    public static function readKeyFrames($handle)
    {
        $keyFrames = [];
        $count = self::readInt($handle, 4);
        for ($i = 0; $i < $count; $i++) {
            $keyFrames[] = new KeyFrame(
                self::readFloat($handle, 4),
                self::readInt($handle, 4),
                self::readInt($handle, 4)
            );
        }
        return $keyFrames;
    }

    /**
     * @param resource $handle
     */
    public static function readFrames($handle)
    {
        $count = self::readInt($handle, 4);
        return fread($handle, $count);
    }

    /**
     * @param resource $handle
     * @return array of Message
     */
    public static function readDebugLog($handle)
    {
        $messages = [];
        $count = self::readInt($handle, 4);
        for ($i = 0; $i < $count; $i++) {
            $messages[] = new Message(
                self::readInt($handle, 4),
                self::readString($handle),
                self::readString($handle)
            );
        }
        return $messages;
    }

    /**
     * @param resource $handle
     * @return array of Tick
     */
    public static function readTicks($handle)
    {
        $ticks = [];
        $count = self::readInt($handle, 4);
        for ($i = 0; $i < $count; $i++) {
            $ticks[] = new Tick(
                self::readString($handle),
                self::readInt($handle, 4)
            );
        }
        return $ticks;
    }

    /**
     * @param resource $handle
     * @return array
     */
    public static function readClassMap($handle)
    {
        $classMap = [];
        $count = self::readInt($handle, 4);
        for ($i = 0; $i < $count; $i++) {
            $class = self::readString($handle);
            $id = self::readInt($handle, 4);
            $classMap[$id] = $class;
        }
        return $classMap;
    }

    /**
     * @param resource $handle
     * @return array of ClassNetCacheItem
     */
    public static function readClassNetCache($handle)
    {
        $classNetCacheItems = [];
        $count = self::readInt($handle, 4);
        for ($i = 0; $i < $count; $i++) {
            $classNetCacheItems[] = self::readClassNetCacheItem($handle);
        }
        return $classNetCacheItems;
    }

    /**
     * @param resource $handle
     * @return ClassNetCacheItem
     */
    public static function readClassNetCacheItem($handle)
    {
        $classNetCacheItem = new ClassNetCacheItem(
            self::readInt($handle, 4),
            self::readInt($handle, 4),
            self::readInt($handle, 4)
        );
        $property_map_length = self::readInt($handle, 4);
        for ($i = 0; $i < $property_map_length; $i++) {
            $classNetCacheItem->propertyMap[] = new PropertyMapItem(
                self::readInt($handle, 4),
                self::readInt($handle, 4)
            );
        }
        return $classNetCacheItem;
    }

    /**
     * @param resource $handle
     * @param int $length
     * @return int
     */
    public static function readInt($handle, $length)
    {
        $formats = [
            1 => 'C', // unsigned char
            2 => 'v', // unsigned short (16-bit little endian)
            4 => 'V', // unsigned long (32-bit little endian)
            8 => 'V2', // unsigned long long (64-bit little endian)
        ];
        if (!array_key_exists($length, $formats)) {
            die('No format found for length: ' . $length);
        }
        $format = $formats[$length];
        $data = fread($handle, $length);
        $value = unpack($format, $data);
        return $value[1];
    }

    /**
     * @param resource $handle
     * @param int $length
     * @return float
     */
    public static function readFloat($handle, $length)
    {
        $formats = [
            4 => 'f', // float
            8 => 'd', // double
        ];
        if (!array_key_exists($length, $formats)) {
            die('No format found for length: ' . $length);
        }
        $format = $formats[$length];
        $data = fread($handle, $length);
        $value = unpack($format, $data);
        return $value[1];
    }

    /**
     * @param resource $handle
     * @param int|null $length
     * @return string
     */
    public static function readString($handle, $length = null)
    {
        if ($length === null) {
            $length = self::readInt($handle, 4);
        }
        return substr(fread($handle, $length), 0, -1);
    }

    /**
     * @param resource $handle
     * @return array of string
     */
    public static function readStrings($handle)
    {
        $arr = [];
        $count = self::readInt($handle, 4);
        for ($i = 0; $i < $count; $i++) {
            $length = self::readInt($handle, 4);
            $arr[] = self::readString($handle, $length);
        }
        return $arr;
    }
}
