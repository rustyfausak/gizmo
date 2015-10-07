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
     * @param int $frameNumber
     * @return Replication
     */
    public static function deserialize($replay, $br, $frameNumber)
    {
        $r = new self();
        $r->actorId = bindec(strrev($br->readBits(10)));
        $r->channelState = $br->readBit();
        if (!$r->channelState) {
            $replay->closeActor($r->actorId, $frameNumber);
            return $r;
        }
        $r->actorState = $br->readBit();
        if ($r->actorState) {
            // New actor
            $r->propertyFlag = $br->readBit(); // seems to always be 0 since we are creating a new actor
            $r->actorObjectId = bindec(strrev($br->readBits(8)));
            $actor = $replay->createActor($r->actorId, $r->actorObjectId, $frameNumber);
            $actor->deserializeInit($br);
            print "Actor #{$actor->id} {$actor->archetype} {$actor->class}\n";
            print $actor->init . "\n";
        }
        else {
            // Existing actor
            print "existing actor\n";
            $actor = $replay->getActor($r->actorId);
            while ($br->readBit()) {
                $actor->updateProperty(
                    ActorProperty::deserialize($replay, $br, $actor)
                );
            }
        }
        return $r;
    }
}
