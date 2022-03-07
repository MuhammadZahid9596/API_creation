<?php
    class Delivery_slot{
    
        // database connection and table name
        private $conn;
        private $table_name = "delivery_time_slot";
    
        // object properties
        public $day;
        public $start_time;
        public $end_time;
        public $number_of_order;
        public $time_slot_type;
        public $status;
        public $assortment_type;

    
        // constructor with $db as database connection
        public function __construct($db){
            $this->conn = $db;
        }

        // read assortment types and day
        function read(){
        
            // select all query
            $query = "SELECT
                        `day` , start_time , end_time ,
                         number_of_order , time_slot_type , 
                        `status` , assortment_type 
                    FROM
                        " . $this->table_name . " WHERE 
                    city_id = ? AND loc_code = ? AND 
                    assortment_type IN ('F','N','EX','OS') GROUP BY assortment_type , time_slot_type ";
        
            // prepare query statement
            $stmt = $this->conn->prepare($query);
        
            // bind city_id of payment method to be fetched
            $stmt->bindParam(1, $this->city_id);

            $stmt->bindParam(2, $this->loc_code);

            // execute query
            $stmt->execute();

            //  echo $query;

            return $stmt;

        }

        // read delivery slots
        function delivery_slots($assortment_type , $time_slot_type){
                
            // select all query
            $query = "SELECT
                        id , `day` , start_time , end_time ,
                        number_of_order , time_slot_type , 
                        `status` , assortment_type , entry_datetime
                    FROM
                        " . $this->table_name . " WHERE
                    assortment_type = '$assortment_type' AND time_slot_type = '$time_slot_type'
                    GROUP BY DAY,time_slot_type,start_time
                    ORDER BY FIELD(DAY, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),start_time";

            // prepare query statement
            $stmt2 = $this->conn->prepare($query);

            // execute query
            $stmt2->execute();

            //  echo $query;

            return $stmt2;

        }

        // copare from order master
        function order_master($delivery_time){
                
            // select all query
            $query = "SELECT
                        order_code , count(delivery_time)
                    FROM
                        order_master om , delivery_time_slot ds WHERE
                        om.order_code = ds.number_of_order
                        delivery_time = '$delivery_time'  ";

            // prepare query statement
            $stmt3 = $this->conn->prepare($query);

            // execute query
            $stmt3->execute();

            //  echo $query;

            return $stmt3;

        }

    }
?>