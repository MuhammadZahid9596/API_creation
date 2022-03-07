<?php
 class Database_conn{
    
    // database connection and table name
    private $conn;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // Insert Token
    function insert_token($jwt,$insert_date,$validity_date,$ip){
        
        // Insert into key table
        $query = "INSERT INTO alfa_key (`key`, insert_date, end_date , ip)
        VALUES ('$jwt', '".date('Y-m-d H:i:s')."', '".date('Y-m-d H:i:s', strtotime('+2 hours'))."' ,
        '".$_SERVER['REMOTE_ADDR']."' )";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // execute query
        $stmt->execute();

        // echo $query;

        return $stmt;

    }

    // Validae Token
    function validate_token($key){
        // select token query
        $query = "SELECT *
                    FROM
                  alfa_key 
                    WHERE
                  `key` = '$key'   ";
    
        // prepare query statement
        $stmt = $this->conn->prepare($query);
    
        // execute query
        $stmt->execute();

        // echo $query;

        return $stmt;

    }
}
?>