<?php
/**
 * Created by PhpStorm.
 * User: danbu
 * Date: 30.3.2020
 * Time: 12:33
 */

include_once "dbLib/TempHum.php";
include_once "dbLib/Doors.php";
include_once "dbLib/State.php";
include_once "dbLib/Database.php";
include_once "dbLib/dbObject.php";

class CompleteState
{
    public $securitySystem;
    public $roomLED;
    public $roomTemp;
    public $roomHum;
    public $mainDoor;
    public $outdoorTemp;
    public $heating;
    public $heatingSettings;

    public function __construct($dbConnection)
    {
        $state = State::getCurrent($dbConnection);
        $tempHumRoom = TempHum::getCurrent(60, $dbConnection); //room
        $tempHumOutdoor = TempHum::getCurrent(0, $dbConnection); //outdoor
        $doors = Doors::getCurrent(50, $dbConnection);
        $this->securitySystem = $state->securitySystem;
        $this->roomLED = $state->roomLED;
        $this->heating = $state->heating;
        $this->heatingSettings = $state->heatingSettings;
        $this->outdoorTemp = $tempHumOutdoor->temperature;
        $this->roomTemp = $tempHumRoom->temperature;
        $this->roomHum = $tempHumRoom->humidity;
        $this->mainDoor = $doors->state;
    }

}