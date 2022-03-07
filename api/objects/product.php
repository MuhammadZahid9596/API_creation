<?php
    class Product{
    
        // database connection and table name
        private $conn;
        private $table_name = "product_report_view_revamp";
    
        // object properties
        public $product_ref;
        public $product_url;
        public $product_name;
        public $product_desc;
        public $img;
        public $product_old_price;
        public $product_price;
        public $brand_name;
    
        // constructor with $db as database connection
        public function __construct($db){
            $this->conn = $db;
        }

        // read products
        function read(){
        
            // select all query
            $query = "SELECT
                        product_ref , product_code , product_url , product_name , product_desc , 
                        img , product_price AS product_old_price, 
                        IF(sale = 'Y' , product_sale_price , product_price) AS product_price,
                        product_url , quantity ,  brand_name , product_scoring
                    FROM
                        " . $this->table_name . " WHERE product_stat = 'A' AND
                    city_id = ? AND product_loc_code = ? LIMIT ".$this->offset.",100 ";
        
            // prepare query statement
            $stmt = $this->conn->prepare($query);
        
            // bind city_code of product to be fetched
            $stmt->bindParam(1, $this->city_code);

            // bind produuct city code to be fetched
            $stmt->bindParam(2, $this->product_loc_code);

            // execute query
            $stmt->execute();

            // echo $query;

            return $stmt;

        }

        // read products
        function read_single(){
        
            // select all query
            $query = "SELECT
                        product_ref , product_code , product_url , product_name , product_desc , 
                        img , product_price AS product_old_price, 
                        IF(sale = 'Y' , product_sale_price , product_price) AS product_price,
                        product_url , quantity ,  brand_name , product_scoring
                    FROM
                        " . $this->table_name . " WHERE product_stat = 'A' AND
                    city_id = ? AND product_loc_code = ? AND product_ref  = ? LIMIT 100 ";
        
            // prepare query statement
            $stmt = $this->conn->prepare($query);
        
            // bind city_code of product to be fetched
            $stmt->bindParam(1, $this->city_code);

            // bind produuct city code to be fetched
            $stmt->bindParam(2, $this->product_loc_code);

            // bind product ref code to be fetched
            $stmt->bindParam(3, $this->product_ref_code);

            // execute query
            $stmt->execute();

            //echo $query;

            return $stmt;

        }
    }
?>