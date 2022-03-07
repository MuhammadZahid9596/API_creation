<?php
    class Order_status{
    
        // database connection and table name
        private $conn;
        private $table_name = "order_status";
    
        // object properties
        public $datatime;
        public $ordercode;
        public $status;
        public $status_text;
    
        // constructor with $db as database connection
        public function __construct($db){
            $this->conn = $db;
        }

        // read payments
        function read(){
        
            // select all query
            $query = "SELECT
                        order_status.status_text,order_code
                    FROM
                        order_master
                    LEFT JOIN " . $this->table_name . "
                    ON order_master.order_code = " . $this->table_name . ".ordercode
                    AND order_master.order_status = order_status.status
                    WHERE
                    order_master.order_code = ? GROUP BY `status` ";
        
            // prepare query statement
            $stmt = $this->conn->prepare($query);
        
            // bind city_id of payment method to be fetched
            $stmt->bindParam(1, $this->order_code);

            // execute query
            $stmt->execute();

            //echo $query;

            return $stmt;

        }

    }
?>