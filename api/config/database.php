<?php
    class Database{
    
        // specify your own database credentials
        private $host = "158.106.130.202";
        private $db_name = "blueexcl_metronew";
        private $username = "blueexcl_fatima";
        private $password = "saad2521*#";
        public $conn;
    
        // get the database connection
        public function getConnection(){
    
            $this->conn = null;
    
            try{
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
            }catch(PDOException $exception){
                echo "Connection error: " . $exception->getMessage();
            }
    
            return $this->conn;
        }
    }
?>