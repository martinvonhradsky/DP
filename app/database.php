<?php

class Database {

    private $dbHost = "db";
    private $dbName = "module";
    private $dbUser = "secmon";
    private $dbPass = "326598";
    private $dsn;
  
    private $db;
  
    function __construct() {
      $this->dsn = "pgsql:host=$this->dbHost;dbname=$this->dbName";
    }
  
    function connect() {
      try {
        $this->db = new PDO($this->dsn, $this->dbUser, $this->dbPass);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
        echo '{"status": "error","message": " Database connection failed: ' . $e->getMessage() . '"}';
        exit();
      }
    }
    function prepare($select) {
        try {
          return $this->db->prepare($select);
        } catch (PDOException $e) {
          echo '{"status": "error","message": " Prepare statement failed: ' . $e->getMessage() . '"}';
          exit();
        }
    }
    function quote($string) {
        return $this->db->quote($string);
    }
}
