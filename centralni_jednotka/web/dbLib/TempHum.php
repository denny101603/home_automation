<?php
/**
 * Created by PhpStorm.
 * User: danbu
 * Date: 29.3.2020
 * Time: 15:19
 */

include_once "dbObject.php";

class TempHum extends DatabaseObject
{
    protected static $table_name = "temperatures";
    public $moduleID;
    public $dateTime;
    public $temperature;
    public $humidity;

    /**
     * @param $moduleID int
     * @param $dbConnection \PDO
     * @return TempHum|null
     */
    public static function getCurrent($moduleID, $dbConnection)
    {
        try {
            //get the newest record
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where moduleID = ? order by dtime desc limit 1");
            $stmt->execute([$moduleID]);
            if ($stmt->errorCode() != "00000")
                return null;
            $row = $stmt->fetch();
        } catch (\PDOException $e) {
            return null;
        }

        if ($row == null)
            return null;
        $tempHum = new TempHum($dbConnection);
        $tempHum->moduleID = $moduleID;
        $tempHum->temperature = $row['temperature'];
        $tempHum->humidity = $row['humidity'];
        $tempHum->dateTime = $row['dtime'];
        return $tempHum;
    }

    /**
     *  refreshes data in TempHum according to those in DB
     */
    public function refresh()
    {
        try {
            //get the newest record
            $stmt = $this->connection->prepare("select * from " . self::$table_name . " where moduleID = ? order by dtime desc limit 1");
            $stmt->execute([$this->moduleID]);
            if ($stmt->errorCode() != "00000")
                return;
            $row = $stmt->fetch();
        } catch (\PDOException $e) {
            return;
        }

        if ($row == null)
            return;
        $this->temperature = $row['temperature'];
        $this->humidity = $row['humidity'];
        $this->dateTime = $row['dtime'];
    }

    /**
     * Returns last X hours of temphum values
     * @param $moduleID
     * @param $hours int number of values (1 per hour) to return
     * @param $dbConnection \PDO
     * @return array of last "hours" TempHum objects
     */
    public static function getLastXHours($moduleID, $hours, $dbConnection)
    {
        try {
            //get the newest record
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where moduleID = ? order by dtime desc limit " . $hours);
            $stmt->execute([$moduleID]);
            if ($stmt->errorCode() != "00000")
                return null;
        } catch (\PDOException $e) {
            return null;
        }
        $foundObjects = array();
        while($row = $stmt->fetch())
        {
            $tempHum = new TempHum($dbConnection);
            $tempHum->moduleID = $moduleID;
            $tempHum->temperature = $row['temperature'];
            $tempHum->humidity = $row['humidity'];
            $tempHum->dateTime = $row['dtime'];
            array_push($foundObjects, $tempHum);
        }
        return $foundObjects;
    }
}