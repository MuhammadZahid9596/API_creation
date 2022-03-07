<?php
    class Payment{
    
        // database connection and table name
        private $conn;
        private $table_name = "check_payment_method";
    
        // object properties
        public $city_id;
        public $type;
        public $bankcharges;
        public $easypaisa;
        public $jazzcash;
        public $cod;
        public $mpos;
        public $creditcard;
        public $wallet_status;
        public $wallet_sorting;
    
        // constructor with $db as database connection
        public function __construct($db){
            $this->conn = $db;
        }

        // read payments
        function read(){
        
            // select all query
            $query = "SELECT
                        city_id , type , bankcharges , easypaisa , jazzcash , 
                        cod , mpos , creditcard , wallet_status , wallet_sorting
                    FROM
                        " . $this->table_name . " WHERE
                    city_id = ? LIMIT 100 ";
        
            // prepare query statement
            $stmt = $this->conn->prepare($query);
        
            // bind city_id of payment method to be fetched
            $stmt->bindParam(1, $this->city_id);

            // execute query
            $stmt->execute();

            // echo $query;

            return $stmt;

        }

    }
?>