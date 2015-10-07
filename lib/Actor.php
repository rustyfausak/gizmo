<?php

namespace Gizmo;

class Actor
{
    /* @var int*/
    public $id;
    /* @var string */
    public $archetype;
    /* @var string */
    public $class;
    /* @var array of ActorProperty */
    public $properties;

    /**
     * @return array map string archetype to string class
     */
    public static function getArchetypeToClassMap()
    {
        return [
            'GameInfo_Soccar.GameInfo.GameInfo_Soccar:GameReplicationInfoArchetype' => 'TAGame.GRI_TA',
            'Archetypes.Teams.Team0' => 'TAGame.Team_Soccar_TA',
            'Archetypes.Teams.Team1' => 'TAGame.Team_Soccar_TA',
            'Archetypes.GameEvent.GameEvent_SoccarSplitscreen' => 'TAGame.GameEvent_SoccarSplitscreen_TA',
            'TAGame.Default__PRI_TA' => 'TAGame.PRI_TA',
            'Archetypes.Ball.Ball_Default' => 'TAGame.Ball_TA',
            'Archetypes.Car.Car_Default' => 'TAGame.Car_TA',
            'Archetypes.CarComponents.CarComponent_Boost' => 'TAGame.CarComponent_Boost_TA',
            'Archetypes.CarComponents.CarComponent_Jump' => 'TAGame.CarComponent_Jump_TA',
            'Archetypes.CarComponents.CarComponent_DoubleJump' => 'TAGame.CarComponent_DoubleJump_TA',
            'Archetypes.CarComponents.CarComponent_Dodge' => 'TAGame.CarComponent_Dodge_TA',
            'Archetypes.CarComponents.CarComponent_FlipCar' => 'TAGame.CarComponent_FlipCar_TA',
        ];
    }

    /**
     * @param int $id
     * @param string $archetype
     */
    public function __construct($id, $archetype)
    {
        $this->id = $id;
        $this->archetype = $archetype;
        $map = self::getArchetypeToClassMap();
        if (array_key_exists($this->archetype, $map)) {
            $class = $map[$this->archetype];
        }
        else {
            $class = $this->archetype;
        }
        $this->class = $class;
        $this->properties = [];
    }
}
