<?php

class CardsRFID extends DatabaseObject
{
    protected static $table_name = "rfidCards";
    public $uid;
    public $owner;

    /**
     * CardsRFID constructor.
     * @param $uid string
     * @param $owner string
     * @param $dbConnection \PDO
     */
    public function __construct($uid, $owner, $dbConnection)
    {
        parent::__construct($dbConnection);
        $this->uid = $uid;
        $this->owner = $owner;
    }


    /**
     * @param $dbConnection \PDO
     * @return array|null
     */
    public static function getAll($dbConnection)
    {
        try {
            $stmt = $dbConnection->prepare("select * from " . self::$table_name);
            $stmt->execute();
            if ($stmt->errorCode() != "00000")
                return null;
        } catch (\PDOException $e) {
            return null;
        }
        $foundObjects = array();
        while($row = $stmt->fetch())
        {
            $card = new CardsRFID($row["uid"], $row["owner"], $dbConnection);
            array_push($foundObjects, $card);
        }
        return $foundObjects;
    }

    /**
     * updates owner of RFID card with the uid in DB
     * @param $uid
     * @param $owner string new owner
     * @param $dbConnection \PDO
     * @return bool
     */
    public static function update($uid, $owner, $dbConnection)
    {
        $stmt = $dbConnection->prepare("update " . self::$table_name . " set owner = ? where uid = '" . $uid . "'");
        try
        {
            $stmt->execute([$owner]);
            return true;
        }
        catch (\PDOException $e)
        {
            print_r($e->errorInfo);
            return false;
        }
    }

    /**
     * @param $uid string uid of the card to be deleted
     * @param $dbConnection \PDO
     * @return bool
     */
    public static function delete($uid, $dbConnection)
    {
        try
        {
            $stmt = $dbConnection->prepare("delete from " . self::$table_name . " where uid = ?");
            $stmt->execute([$uid]);
            return true;
        }
        catch (\PDOException $e)
        {
            return false;
        }
    }

    /**
     * @param $uid string
     * @param $dbConnection \PDO
     * @return CardsRFID|null
     */
    public static function getByUID($uid, $dbConnection)
    {
        try {
            $stmt = $dbConnection->prepare("select * from " . self::$table_name . " where uid = ? limit 1");
            $stmt->execute([$uid]);
            if ($stmt->errorCode() != "00000")
                return null;
            $row = $stmt->fetch();
        } catch (\PDOException $e) {
            return null;
        }

        if ($row == null)
            return null;
        $card = new CardsRFID($uid, $row["owner"], $dbConnection);
        return $card;
    }
}