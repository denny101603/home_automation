<?php

class Database
{
    private $host = 'localhost';
    private $dbName = 'homebp';
    private $username = 'put your username here'; //username to database server
    private $password = 'put your password here'; //password to database server
    private $connection = null;
    public function getConnection()
    {
        if($this->connection != null)
            return $this->connection;

        if(0) //debug on other DB
        {
            $this->username = 'root';
            $this->password = '';
        }
        try
        {
            $this->connection = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbName, $this->username, $this->password);
            $this->connection->exec("set names utf8");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->connection;
    }
}
?>