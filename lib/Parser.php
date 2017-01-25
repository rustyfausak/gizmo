<?php

namespace Gizmo;

class Parser
{
    /**
     * Parses the given file path as a replay and returns a Replay.
     *
     * @param string $path
     * @return Replay
     */
    public static function parse($path)
    {
        $replay = new Replay();

        if (!is_readable($path)) {
            throw new \Exception('Cannot read replay file.');
        }
        if (!filesize($path)) {
            throw new \Exception('No data in replay file.');
        }

        $handle = fopen($path, 'rb');

        // Size of properties section
        $size = self::readInt($handle);
        print "size = {$size}\n";
        // CRC
        $crc = self::readInt($handle);
        print "crc = {$crc}\n";

        $replay->version = self::readInt($handle) . '.' . self::readInt($handle);
        $replay->type = self::readString($handle);
        $replay->properties = self::readProperties($handle);

        // Size of remaining data
        self::readInt($handle);
        // Unknown 4 byte separator
        self::readInt($handle);

        $replay->levels = self::readStrings($handle);
        $replay->keyFrames = self::readKeyFrames($handle);
        $frameData = self::readFrameData($handle);
        $replay->log = self::readLog($handle);
        $replay->ticks = self::readTicks($handle);
        $replay->packages = self::readStrings($handle);
        $replay->objects = self::readStrings($handle);
        $replay->names = self::readStrings($handle);
        $replay->classes = self::readClasses($handle);
        $replay->propertyTree = self::readPropertyTree($handle);

        fclose($handle);

        $replay->buildCache();
        //$replay->frames = self::deserializeFrames($replay, $frameData);

        return $replay;
    }

    /**
     * @param Replay $replay
     * @param binary string $frameData
     */
    public static function deserializeFrames($replay, $frameData)
    {
        $frames = [];
        $br = new BinaryReader(BinaryReader::asBits($frameData), false);
        foreach ($replay->keyFrames as $keyFrame) {
            print "deserializing keyframe at position {$keyFrame->position}\n";
            $br->seek($keyFrame->position);
            $frames[] = Frame::deserialize($replay, $br, $keyFrame->frameNumber);
            print "Done\n";
            exit;
        }
        return $frames;
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
                $length = self::readInt($handle);
                $property->value = [];
                foreach (range(1, $length) as $i) {
                    $property->value[] = self::readProperties($handle);
                }
                break;
            case 'ByteProperty':
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
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $keyFrames[] = new KeyFrame(
                self::readFloat($handle, 4),
                self::readInt($handle),
                self::readInt($handle)
            );
        }
        return $keyFrames;
    }

    /**
     * @param resource $handle
     * @return binary string
     */
    public static function readFrameData($handle)
    {
        $count = self::readInt($handle);
        return unpack('H*', fread($handle, $count))[1];
    }

    /**
     * @param resource $handle
     * @return array of Message
     */
    public static function readLog($handle)
    {
        $messages = [];
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $messages[] = new Message(
                self::readInt($handle),
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
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $ticks[] = new Tick(
                self::readString($handle),
                self::readInt($handle)
            );
        }
        return $ticks;
    }

    /**
     * @param resource $handle
     * @return array
     */
    public static function readClasses($handle)
    {
        $classes = [];
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $class = self::readString($handle);
            $id = self::readInt($handle);
            $classes[$id] = $class;
        }
        return $classes;
    }

    /**
     * @param resource $handle
     * @return array of PropertyBranch
     */
    public static function readPropertyTree($handle)
    {
        $branches = [];
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $branches[] = self::readPropertyBranch($handle);
        }
        return $branches;
    }

    /**
     * @param resource $handle
     * @return PropertyBranch
     */
    public static function readPropertyBranch($handle)
    {
        $branch = new PropertyBranch(
            self::readInt($handle),
            self::readInt($handle),
            self::readInt($handle)
        );
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $branch->propertyMap[self::readInt($handle)] = self::readInt($handle);
        }
        return $branch;
    }

    /**
     * @param resource $handle
     * @param int $length
     * @return int
     */
    public static function readInt($handle, $length = 4)
    {
        $formats = [
            1 => 'C', // unsigned char
            2 => 'v', // unsigned short (16-bit little endian)
            4 => 'V', // unsigned long (32-bit little endian)
            8 => 'H*', // binary string
        ];
        if (!array_key_exists($length, $formats)) {
            throw new \Exception('No int format found for length: ' . $length);
        }
        $format = $formats[$length];
        $value = unpack($format, fread($handle, $length))[1];
        if ($format == 'H*') {
            return bindec(strrev(BinaryReader::asBits($value)));
        }
        return $value;
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
            throw new \Exception('No float format found for length: ' . $length);
        }
        $format = $formats[$length];
        $value = unpack($format, fread($handle, $length))[1];
        return $value;
    }

    /**
     * @param resource $handle
     * @param int|null $length
     * @return string
     */
    public static function readString($handle, $length = null)
    {
        if ($length === null) {
            $length = self::readInt($handle);
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
        $count = self::readInt($handle);
        for ($i = 0; $i < $count; $i++) {
            $length = self::readInt($handle);
            $arr[] = self::readString($handle, $length);
        }
        return $arr;
    }
}
