<?php

class State extends DatabaseObject
{
    protected static $table_name = "state";
    public static $id = 1; //in the state table there is just one record with id 1
    public $roomLED;
    public $securitySystem;
    public $heating;
    public $heatingSettings;

    /**
     * @param $dbConnection \PDO
     * @return State|null
     */
    public static function getCurrent($dbConnection)
    {
        try {
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where id = ? limit 1");
            $stmt->execute([self::$id]);
            if ($stmt->errorCode() != "00000")
                return null;
            $row = $stmt->fetch();
        } catch (\PDOException $e) {
            return null;
        }

        if ($row == null)
            return null;
        $state = new State($dbConnection);
        $state->roomLED = $row['roomLED'];
        $state->securitySystem = $row['houseLock'];
        $state->heating = $row['heating'];
        $state->heatingSettings = $row['heatingSettings'];
        return $state;
    }

    /**
     * Saves settings to DB
     * @param $settings string - json with settings
     * @param $dbConnection \PDO
     * @return bool - success of saving to DB
     */
    public static function saveHeatingSettings($settings, $dbConnection)
    {
        $stmt = $dbConnection->prepare("update " . self::$table_name . " set heatingSettings = ? where id = 1");
        try
        {
            $stmt->execute([$settings]);
            return true;
        }
        catch (\PDOException $e)
        {
            print_r($e->errorInfo);
            return false;
        }
    }
}