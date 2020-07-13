<?php

abstract class DatabaseObject
{
    protected $connection;
    protected static $table_name;


    /**
     * constructor.
     * @param $dbConnection \PDO
     */
    public function __construct($dbConnection)
    {
        $this->connection = $dbConnection;
    }
}