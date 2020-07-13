<?php

class Rule extends DatabaseObject
{
    protected static $table_name = "rules";
    public $id;
    public $event;
    public $eventSpecification;
    public $action;
    public $actionSpecification;

    /**
     * Rule constructor.
     * @param $event int event's index
     * @param $eventSpecification string JSON with specification of event
     * @param $action int action's index
     * @param $actionSpecification string JSON with specification of action
     * @param $dbConnection \PDO
     */

    public function __construct($event, $eventSpecification, $action, $actionSpecification, $dbConnection)
    {
        parent::__construct($dbConnection);
        $this->event = $event;
        $this->eventSpecification = $eventSpecification;
        $this->action = $action;
        $this->actionSpecification = $actionSpecification;
    }


    /**
     * Saves new rule to the DB
     * @return bool success of of the operation
     */
    public function saveNew()
    {
        try
        {
            $stmt = $this->connection->prepare("insert into " . self::$table_name . "(event, eventSpec, action, actionSpec) values(?, ?, ?, ?)");
            $stmt->execute([$this->event, $this->eventSpecification, $this->action, $this->actionSpecification]);

            $this->id = $this->connection->lastInsertId();
            return true;
        }
        catch (\PDOException $e)
        {
            return false;
        }
    }


    /**
     * Deletes rule from the database
     * @param $id int id of rule to be deleted
     * @param $dbConnection \PDO
     * @return bool success of of the operation
     */
    public static function deleteByID($id, $dbConnection)
    {
        try
        {
            $stmt = $dbConnection->prepare("delete from " . self::$table_name . " where id = ?");
            $stmt->execute([$id]);
            return true;
        }
        catch (\PDOException $e)
        {
            return false;
        }
    }

    /**
     * Deletes the instance of rule from the database
     * @return bool success of of the operation
     */
    public function delete()
    {
        if($this->id == null)
            return false;
        return self::deleteByID($this->id, $this->connection);

    }

    /**
     * Gets all existing rules from the DB
     * @param $dbConnection \PDO
     * @return array|null of existing rules
     */
    public static function getAllRules($dbConnection)
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
            $rule = new Rule($row["event"], $row["eventSpec"], $row["action"], $row["actionSpec"], $dbConnection);
            $rule->id = $row["id"];
            array_push($foundObjects, $rule);
        }
        return $foundObjects;
    }
}