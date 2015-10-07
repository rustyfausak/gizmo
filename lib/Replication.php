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
    public $actorObjectId;

    /**
     * @param Replay $replay
     * @param BinaryReader $br
     * @return Replication
     */
    public static function deserialize($replay, $br)
    {
        $r = new self();
        $r->actorId = bindec($br->readBits(10));
        $r->channelState = $br->readBit();
        if (!$r->channelState) {
            $replay->destroyActor($r->actorId);
            return $r;
        }
        $r->actorState = $br->readBit();
        if ($r->actorState) {
            // New actor
            $r->propertyFlag = $br->readBit(); // seems to always be 0 since we are creating a new actor
            $r->actorObjectId = bindec(strrev($br->readBits(8)));
            $actor = $replay->createActor($r->actorId, $r->actorObjectId);
        }
        else {
            // Existing actor
            while ($br->readBit()) {
                ActorProperty::deserialize($replay, $br);
                break;
            }
        }
        return $r;
    }
}
