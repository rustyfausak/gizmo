<?php

namespace Gizmo;

class Replication
{
    /* @var int */
    public $actorId;
    /* @var bool */
    public $channelState;
    /* @var bool */
    public $actorState;
    /* @var int */
    public $actorTypeId;
    /* @var Vector */
    public $vector;

    /**
     * @param BinaryReader $br
     * @return Replication
     */
    public static function deserialize($br)
    {
        $r = new self();
        $r->actorId = bindec($br->readBits(10));
        $r->channelState = $br->readBit();
        if (!$r->channelState) {
            return $r;
        }
        $r->actorState = $br->readBit();
        if ($r->actorState) {
            // New actor
            $r->unknown1 = $br->readBit();
            $r->actorTypeId = bindec(strrev($br->readBits(8)));
            $r->vector = Vector::deserialize($br);
            $r->next = $br->readBits(32);
            /*$r->pitch = $br->readBits(8);
            $r->yaw = $br->readBits(8);
            $r->roll = $br->readBits(8);*/
        }
        else {
            // Existing actor
        }
        return $r;
    }
}
