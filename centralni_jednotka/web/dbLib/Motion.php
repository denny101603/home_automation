<?php

class Motion extends DatabaseObject
{
    protected static $table_name = "motionHistory";
    public $moduleID;
    public $dateTime;
    public $stopTime;

    /**
     * Motion constructor.
     * @param $moduleID
     * @param $dateTime string time when the motion started
     * @param $stopTime string time when the motion stopped
     * @param $dbConnection
     */
    public function __construct($moduleID, $dateTime, $stopTime, $dbConnection)
    {
        parent::__construct($dbConnection);
        $this->moduleID = $moduleID;
        $this->dateTime = $dateTime;
        $this->stopTime = $stopTime;
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
            $motion = new Motion($moduleID, $row['dtime'], $row['stopTime'], $dbConnection);
            array_push($foundObjects, $motion);
        }
        return $foundObjects;
    }
}