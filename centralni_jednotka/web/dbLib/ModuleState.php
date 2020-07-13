<?php

class ModuleState extends DatabaseObject
{
    protected static $table_name = "modulesState";
    public $id; //module ID
    public $state; //0 offline or 1 online
    public $lastOnline; //last datetime online

    /**
     * Rule constructor.
     * @param $id int module ID
     * @param $state int 0 offline or 1 online
     * @param $lastOnline string last datetime online
     * @param $dbConnection \PDO
     */
    public function __construct($id, $state, $lastOnline, $dbConnection)
    {
        parent::__construct($dbConnection);
        $this->id = $id;
        $this->state = $state;
        $this->lastOnline = $lastOnline;

    }


    /**
     * Gets all moduleStates from DB
     * @param $dbConnection \PDO
     * @return array|null of existing moduleStates
     */
    public static function getStates($dbConnection)
    {
        try
        {
            $stmt = $dbConnection->prepare("SELECT * FROM " . self::$table_name);
            $stmt->execute();
            if($stmt->errorCode() != "00000")
                return null;
        }
        catch (\PDOException $e)
        {
            return null;
        }
        $foundObjects = array();
        while($row = $stmt->fetch())
        {
            $module = new ModuleState($row["id"], $row["state"], $row["lastOnline"], $dbConnection);
            array_push($foundObjects, $module);
        }
        return $foundObjects;
    }
}