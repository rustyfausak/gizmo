<?php

namespace Gizmo;

class ActorProperty
{
    /* @var int */
    public $id;
    /* @var string */
    public $class;
    /* @var mixed */
    public $value;

    /**
     * @param Replay $replay
     * @param BinaryReader $br
     * @param Actor $actor
     * @return ActorProperty
     */
    public static function deserialize($replay, $br, $actor)
    {
        $id = bindec(strrev($br->readBits($actor->getPropertyBits())));
        $class = $actor->properties[$id];
        $property = new self($id, $class);
        return new self();
    }

    /**
     * @param int $id
     * @param string $class
     */
    public function __construct($id, $class)
    {
        $this->id = $id;
        $this->class = $class;
        $this->value = self::getDefault($class);
    }

    public static function getDefault($type)
    {
        return null;
    }

    /**
     * @param string $class
     * @return mixed
     */
    public static function getType($class)
    {
        if (in_array($class, [
            "Engine.Actor:bNetOwner",
            "Engine.Actor:bProjTarget",
            "Engine.Actor:bBlockActors",
            "Engine.Actor:bCollideWorld",
            "Engine.Actor:bCollideActors",
            "Engine.Actor:bHardAttach",
            "Engine.Actor:bTearOff",
            "Engine.Actor:bHidden",
            "Engine.GameReplicationInfo:bMatchIsOver",
            "Engine.GameReplicationInfo:bMatchHasBegun",
            "Engine.GameReplicationInfo:bStopCountDown",
            "ProjectX.GRI_X:bGameStarted",
            "Engine.PlayerReplicationInfo:bFromPreviousLevel",
            "Engine.PlayerReplicationInfo:bIsInactive",
            "Engine.PlayerReplicationInfo:bBot",
            "Engine.PlayerReplicationInfo:bOutOfLives",
            "Engine.PlayerReplicationInfo:bReadyToPlay",
            "Engine.PlayerReplicationInfo:bWaitingPlayer",
            "Engine.PlayerReplicationInfo:bOnlySpectator",
            "Engine.PlayerReplicationInfo:bIsSpectator",
            "Engine.PlayerReplicationInfo:bAdmin",
            "TAGame.PRI_TA:bVoteToForfeitDisabled",
            "TAGame.PRI_TA:bIsInSplitScreen",
            "TAGame.PRI_TA:bUsingFreecam",
            "TAGame.PRI_TA:bUsingBehindView",
            "TAGame.PRI_TA:bUsingSecondaryCamera",
            "TAGame.PRI_TA:bBusy",
            "TAGame.PRI_TA:bReady",
            "TAGame.PRI_TA:bMatchMVP",
            "TAGame.GameEvent_TA:bHasLeaveMatchPenalty",
            "TAGame.GameEvent_TA:bAllowReadyUp",
            "TAGame.GameEvent_Team_TA:bForfeit",
            "TAGame.GameEvent_Team_TA:bDisableMutingOtherTeam",
            "TAGame.GameEvent_Soccar_TA:bNoContest",
            "TAGame.GameEvent_Soccar_TA:bOverTime",
            "Engine.Pawn:bFastAttachedMove",
            "Engine.Pawn:bRootMotionFromInterpCurve",
            "Engine.Pawn:bUsedByMatinee",
            "Engine.Pawn:bCanSwatTurn",
            "Engine.Pawn:bSimulateGravity",
            "Engine.Pawn:bIsCrouched",
            "Engine.Pawn:bIsWalking",
            "TAGame.RBActor_TA:bFrozen",
            "TAGame.RBActor_TA:bReplayActor",
            "TAGame.Ball_TA:bEndOfGameHidden",
            "TAGame.CarComponent_FlipCar_TA:bFlipRight",
            "TAGame.CarComponent_Boost_TA:bNoBoost",
            "TAGame.CarComponent_Boost_TA:bUnlimitedBoost",
            "TAGame.Vehicle_TA:bReplicatedHandbrake",
            "TAGame.Vehicle_TA:bDriving",
        ])) {
            return 'bool';
        }

        if (in_array($class, [
            "Engine.GameReplicationInfo:TimeLimit",
            "Engine.GameReplicationInfo:GoalScore",
            "Engine.GameReplicationInfo:RemainingMinute",
            "Engine.GameReplicationInfo:ElapsedTime",
            "Engine.GameReplicationInfo:RemainingTime",
            "Engine.TeamInfo:TeamIndex",
            "Engine.PlayerReplicationInfo:PlayerID",
            "TAGame.PRI_TA:MatchShots",
            "TAGame.PRI_TA:MatchSaves",
            "TAGame.PRI_TA:MatchAssists",
            "TAGame.PRI_TA:MatchGoals",
            "TAGame.GameEvent_Team_TA:MaxTeamSize",
            "TAGame.GameEvent_Soccar_TA:SecondsRemaining",
            "Engine.Pawn:HealthMax",
            "Engine.Pawn:Health",
        ])) {
            return 'int';
        }

        if (in_array($class, [
            "Engine.Actor:DrawScale",
            "Engine.TeamInfo:Score",
            "Engine.PlayerReplicationInfo:Deaths",
            "Engine.PlayerReplicationInfo:Score",
            "Engine.Pawn:AirControl",
            "Engine.Pawn:AccelRate",
            "Engine.Pawn:AirSpeed",
            "Engine.Pawn:WaterSpeed",
            "Engine.Pawn:GroundSpeed",
            "Engine.Pawn:JumpZ",
        ])) {
            return 'float';
        }

        if (in_array($class, [
            "Engine.GameReplicationInfo:ServerName",
            "ProjectX.GRI_X:GameServerID",
            "Engine.TeamInfo:TeamName",
            "TAGame.Team_TA:CustomTeamName",
            "Engine.PlayerReplicationInfo:PlayerName",
            "TAGame.PRI_TA:Title",
        ])) {
            return 'string';
        }

        if (in_array($class, [
            "Engine.PlayerReplicationInfo:Ping",
            "Engine.Pawn:FiringMode",
            "Engine.Pawn:FlashCount",
            "Engine.Pawn:RemoteViewPitch",
        ])) {
            return 'byte';
        }

        if (in_array($class, [
            "Engine.Actor:Base",
            "Engine.Actor:Owner",
            "Engine.GameReplicationInfo:Winner",
        ])) {
            return 'Actor';
        }

        if (in_array($class, [
            "Engine.Actor:RelativeLocation",
            "Engine.Actor:Velocity",
            "Engine.Actor:Location",
            "Engine.Pawn:FlashLocation",
            "Engine.Pawn:TearOffMomentum",
            "Engine.Pawn:TakeHitLocation",
        ])) {
            return 'Vector';
        }

        if (in_array($class, [
            "Engine.Actor:RelativeRotation",
            "Engine.Actor:Rotation",
        ])) {
            return 'Rotator';
        }

        if (in_array($class, [
            "Engine.Actor:Instigator",
        ])) {
            return 'Pawn';
        }

        if (in_array($class, [
            "Engine.Actor:ReplicatedCollisionType",
        ])) {
            return 'ECollisionType';
        }

        if (in_array($class, [
            "Engine.Actor:Role",
            "Engine.Actor:RemoteRole",
        ])) {
            return 'ENetRole';
        }

        if (in_array($class, [
            "Engine.Actor:Physics",
        ])) {
            return 'EPhysics';
        }

        if (in_array($class, [
            "Engine.GameReplicationInfo:GameClass",
        ])) {
            return 'class<GameInfo>';
        }

        if (in_array($class, [
            "Engine.PlayerReplicationInfo:Team",
        ])) {
            return 'TeamInfo';
        }

        if (in_array($class, [
            "Engine.PlayerReplicationInfo:UniqueId",
        ])) {
            return 'Uid[8]';
        }

        if (in_array($class, [
            "Engine.Pawn:InvManager",
        ])) {
            return 'InventoryManager';
        }

        if (in_array($class, [
            "Engine.Pawn:DrivenVehicle",
            "TAGame.CarComponent_TA:Vehicle",
        ])) {
            return 'Vehicle';
        }

        if (in_array($class, [
            "Engine.Pawn:HitDamageType",
        ])) {
            return 'class<DamageType>';
        }

        if (in_array($class, [
            "Engine.Pawn:PlayerReplicationInfo",
        ])) {
            return 'PlayerReplicationInfo';
        }

        if (in_array($class, [
            "Engine.Pawn:Controller",
        ])) {
            return 'Controller';
        }

        if (in_array($class, [
            "TAGame.RBActor_TA:ReplicatedRBState",
        ])) {
            return 'RigidBodyState';
        }

        if (in_array($class, [
            "ProjectX.GRI_X:Reservations",
            "ProjectX.GRI_X:ReplicatedGamePlaylist",
            "TAGame.GRI_TA:NewDedicatedServerIP", // string?
            "TAGame.Team_TA:LogoData",
            "TAGame.Team_TA:GameEvent",
            "TAGame.Team_Soccar_TA:GameScore",
            "TAGame.PRI_TA:TotalXP", // int?
            "TAGame.PRI_TA:PartyLeader",
            "TAGame.PRI_TA:CameraYaw",
            "TAGame.PRI_TA:CameraPitch",
            "TAGame.PRI_TA:CameraSettings",
            "TAGame.PRI_TA:RespawnTimeRemaining", // float?
            "TAGame.PRI_TA:ClientLoadout",
            "TAGame.PRI_TA:ReplicatedGameEvent",
            "TAGame.PRI_TA:MatchScore",
            "TAGame.PRI_TA:ServerSetPartyLeader",
            "TAGame.PRI_TA:ServerSplitScreenStatusChanged",
            "TAGame.PRI_TA:ServerSetLoadout",
            "TAGame.PRI_TA:ClientNotifyGainedStat",
            "TAGame.PRI_TA:ClientNotifyStatTickerMessage",
            "TAGame.PRI_TA:ServerReadyUp",
            "TAGame.PRI_TA:ServerSetCameraSettings",
            "TAGame.PRI_TA:ServerSetUsingSecondaryCamera",
            "TAGame.PRI_TA:ServerSetUsingBehindView",
            "TAGame.PRI_TA:ServerSetUsingFreecam",
            "TAGame.PRI_TA:ServerSetCameraRotation",
            "TAGame.PRI_TA:ServerSetVoteStatus",
            "TAGame.PRI_TA:ServerChangeTeam",
            "TAGame.PRI_TA:ServerSpectate",
            "TAGame.PRI_TA:ServerSetTotalXP",
            "TAGame.PRI_TA:ServerVoteToForfeit",
            "TAGame.GameEvent_TA:ReplicatedGameStateTimeRemaining",
            "TAGame.GameEvent_TA:ReplicatedStateIndex",
            "TAGame.GameEvent_TA:ActivatorCar",
            "TAGame.GameEvent_Soccar_TA:ReplicatedMusicStinger",
            "TAGame.GameEvent_Soccar_TA:RoundNum",
            "TAGame.GameEvent_Soccar_TA:ReplicatedScoredOnTeam",
            "TAGame.GameEvent_Soccar_TA:MVP",
            "TAGame.GameEvent_Soccar_TA:MatchWinner",
            "TAGame.GameEvent_Soccar_TA:GameWinner",
            "TAGame.GameEvent_Soccar_TA:ReplayDirector",
            "TAGame.GameEvent_SoccarPrivate_TA:GameOwner",
            "Engine.Pawn:RootMotionInterpCurveLastValue", // float?
            "Engine.Pawn:RootMotionInterpCurrentTime", // float?
            "Engine.Pawn:RootMotionInterpRate", // float?
            "TAGame.Ball_TA:ReplicatedExplosionData",
            "TAGame.Ball_TA:GameEvent",
            "TAGame.Ball_TA:HitTeamNum",
            "TAGame.Ball_TA:ReplicatedWorldBounceScale",
            "TAGame.Ball_TA:ReplicatedBallMesh",
            "TAGame.Ball_TA:ReplicatedBallScale",
            "TAGame.CarComponent_TA:ReplicatedActive",
            "TAGame.CarComponent_FlipCar_TA:FlipCarTime", // float?
            "TAGame.CarComponent_Boost_TA:BoostModifier",
            "TAGame.CarComponent_Boost_TA:CurrentBoostAmount",
            "TAGame.CarComponent_Boost_TA:ServerConfirmBoostAmount",
            "TAGame.CarComponent_Boost_TA:ClientFixBoostAmount",
            "TAGame.CarComponent_Boost_TA:ClientGiveBoost",
            "TAGame.CarComponent_Dodge_TA:DodgeTorque",
            "TAGame.Vehicle_TA:ReplicatedSteer",
            "TAGame.Vehicle_TA:ReplicatedThrottle",
            "TAGame.Car_TA:ReplicatedDemolish",
            "TAGame.Car_TA:TeamPaint",
            "TAGame.CrowdActor_TA:ReplicatedCountDownNumber",
            "TAGame.CrowdActor_TA:ReplicatedOneShotSound",
            "TAGame.CrowdActor_TA:ModifiedNoise",
            "TAGame.CrowdActor_TA:GameEvent",
            "TAGame.CrowdManager_TA:GameEvent",
            "TAGame.CrowdManager_TA:ReplicatedGlobalOneShotSound",
            "TAGame.VehiclePickup_TA:ReplicatedPickupData",
        ])) {
            return 'unknown';
        }

        if (strpos($class, 'TheWorld:PersistentLevel.CrowdActor_TA_') !== false) {
            return 'unknown';
        }

        if (strpos($class, 'TheWorld:PersistentLevel.CrowdManager_TA_') !== false) {
            return 'unknown';
        }

        if (strpos($class, 'TheWorld:PersistentLevel.VehiclePickup_Boost_TA_') !== false) {
            return 'unknown';
        }

        if (strpos($class, 'PersistentLevel.GoalVolume_TA_4.Goal_TA_') !== false) {
            return 'unknown';
        }
    }
}
