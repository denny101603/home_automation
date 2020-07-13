<?php


class Doors extends DatabaseObject
{
    protected static $table_name = "doors";
    public $moduleID;
    public $dateTime;
    public $state;

    /**
     * @param $moduleID int
     * @param $dbConnection \PDO
     * @return Doors|null
     */
    public static function getCurrent($moduleID, $dbConnection)
    {
        try
        {
            //get the newest record
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where moduleID = ? order by dtime desc limit 1");
            $stmt->execute([$moduleID]);
            if($stmt->errorCode() != "00000")
                return null;
            $row = $stmt->fetch();
        }
        catch (\PDOException $e)
        {
            return null;
        }

        if($row == null)
            return null;
        $doors = new Doors($dbConnection);
        $doors->moduleID = $moduleID;
        $doors->state = $row['state'];
        $doors->dateTime = $row['dtime'];
        return $doors;
    }

    public static function getHistory($moduleID, $dbConnection)
    {
        try {
            //get the newest record
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where moduleID = ? order by dtime desc limit " . "3000");
            $stmt->execute([$moduleID]);
            if ($stmt->errorCode() != "00000")
                return null;
        } catch (\PDOException $e) {
            return null;
        }
        $foundObjects = array();
        while($row = $stmt->fetch())
        {
            $door = new Doors($dbConnection);
            $door->moduleID = $moduleID;
            $door->dateTime = $row['dtime'];
            $door->state = $row['state'];
            array_push($foundObjects, $door);
        }
        return $foundObjects;
    }

    /**
     * @param $moduleID
     * @param $hours
     * @param $dbConnection \PDO
     * @return array|null
     */
    public static function getLastXHours($moduleID, $hours, $dbConnection)
    {
        $dateTimeLimit = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . '-' . $hours . ' hours'));
        try {
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where moduleID = ? and dtime >= ?");
            $stmt->execute([$moduleID, $dateTimeLimit]);
            if ($stmt->errorCode() != "00000")
                return null;
        } catch (\PDOException $e) {
            return null;
        }
        $foundObjects = array();
        while($row = $stmt->fetch())
        {
            $door = new Doors($dbConnection);
            $door->moduleID = $moduleID;
            $door->dateTime = $row['dtime'];
            $door->state = $row['state'];
            array_push($foundObjects, $door);
        }
        return $foundObjects;
    }

    /**
     *  refreshes data in Doors according to those in DB
     */
    public function refresh()
    {
        try
        {
            //get the newest record
            $stmt = $this->connection->prepare("select * from " . self::$table_name . " where moduleID = ? order by dtime desc limit 1");
            $stmt->execute([$this->moduleID]);
            if($stmt->errorCode() != "00000")
                return;
            $row = $stmt->fetch();
        }
        catch (\PDOException $e)
        {
            return;
        }

        if($row == null)
            return;
        $this->state = $row['state'];
        $this->dateTime = $row['dtime'];
    }
}