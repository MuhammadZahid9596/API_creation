<?php
    class Category{
    
        // database connection and table name
        private $conn;
        private $table_name1 = "product_hierarchy_one";
        private $table_name2 = "product_hierarchy_two";
        private $table_name3 = "product_hierarchy_three";
        private $table_name4 = "product_hierarchy_four";
    
        // object properties
        public $id;
        public $name;
        public $type;
    
        // constructor with $db as database connection
        public function __construct($db){
            $this->conn = $db;
        }

        // read products
        function level1(){
            // select all query
            $query = "SELECT
                        id , name , type 
                    FROM
                        " . $this->table_name1 . " LIMIT 100 ";
        
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            // execute query
            $stmt->execute();

            $category_arr = array();

            return $stmt;
        }
        
        function level2($level_one_id){
            // select all query
            $query = "SELECT
                        id , name , type 
                    FROM
                        " . $this->table_name2 . " WHERE level_one_id = $level_one_id LIMIT 50 ";
        
            // prepare query statement
            $stmt2 = $this->conn->prepare($query);

            $stmt2->setFetchMode(PDO::FETCH_ASSOC);

            // execute query
            $stmt2->execute();

            $category_arr = array();

            return $stmt2;
        }

        function level3($level_two_id){
            // select all query
            $query = "SELECT
                        id , name , type 
                    FROM
                        " . $this->table_name3 . " WHERE level_id_two = $level_two_id LIMIT 50 ";
        
            // prepare query statement
            $stmt3 = $this->conn->prepare($query);

            $stmt3->setFetchMode(PDO::FETCH_ASSOC);

            // execute query
            $stmt3->execute();

            $category_arr = array();

            return $stmt3;
        }

        function level4($level_three_id){
            // select all query
            $query = "SELECT
                        id , name , type 
                    FROM
                        " . $this->table_name4 . " WHERE level_three_id = $level_three_id LIMIT 50 ";
        
            // prepare query statement
            $stmt4 = $this->conn->prepare($query);

            $stmt4->setFetchMode(PDO::FETCH_ASSOC);

            // execute query
            $stmt4->execute();

            $category_arr = array();

            return $stmt4;
        }

    }
?>