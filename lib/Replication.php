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
        $r->actorId = $br->readInt(10);
        $r->channelState = $br->readBit();
        if (!$r->channelState) {
            $replay->closeActor($r->actorId, $frameNumber);
            return $r;
        }
        $r->actorState = $br->readBit();
        if ($r->actorState) {
            // New actor
            $r->propertyFlag = $br->readBit(); // seems to always be 0 since we are creating a new actor
            $r->actorObjectId = $br->readInt(8);
            $actor = $replay->createActor($r->actorId, $r->actorObjectId, $frameNumber);
            $actor->deserializeInit($br);
            print "[new] Actor #{$actor->id} {$actor->class} R:{$actor->rotator} P:{$actor->position} O:{$actor->orientation}\n";
        }
        else {
            // Existing actor
            $actor = $replay->getActor($r->actorId);
            $last_property_id = null;
            while ($br->readBit()) {
                print "[existing] Actor #{$actor->id}\n";
                $property = ActorProperty::deserialize($replay, $br, $actor);
                print "\tProperty #{$property->id} {$property->class}\n";
                foreach ($property->data as $data) {
                    print "\t\t{$data}\n";
                }
            }
        }
        return $r;
    }
}
