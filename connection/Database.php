<?php

class Database
{

    private $host, $username, $password;

    public function __construct($host, $username, $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect($dbname)
    {
        return new PDO("mysql:dbname={$dbname};host={$this->host}", $this->username, $this->password);
    }

}