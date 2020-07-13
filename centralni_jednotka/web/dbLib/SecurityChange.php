<?php

class SecurityChange extends DatabaseObject
{
    protected static $table_name = "securityHistory";
    public $newState;
    public $dateTime;
    public $way;

    /**
     * SecurityChange constructor.
     * @param $newState
     * @param $dateTime
     * @param $way
     */
    public function __construct($newState, $dateTime, $way, $dbConnection)
    {
        parent::__construct($dbConnection);
        $this->newState = $newState;
        $this->dateTime = $dateTime;
        $this->way = $way;
    }


    /**
     * @param $hours
     * @param $dbConnection \PDO
     * @return array|null
     */
    public static function getLastXHours($hours, $dbConnection)
    {
        $dateTimeLimit = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s") . '-' . $hours . ' hours'));
        try {
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where dtime >= ?");
            $stmt->execute([$dateTimeLimit]);
            if ($stmt->errorCode() != "00000")
                return null;
        } catch (\PDOException $e) {
            return null;
        }
        $foundObjects = array();
        while($row = $stmt->fetch())
        {
            $record = new SecurityChange($row['newState'], $row['dtime'], $row['way'], $dbConnection);
            array_push($foundObjects, $record);
        }
        return $foundObjects;
    }
}