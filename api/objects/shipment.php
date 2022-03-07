<?php
    class Shipment{
    
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

        // read product shipment weight
        function product_shippment_weight($product_code){

            $query = 'SELECT product_shipping_weight from product where product_code="'.$product_code.'"';
            
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;

        }

        // read product shipment zone
        function product_shippment_zone($child_city_code,$maincity){

            $query = 'SELECT zone from dashboard_city_detail where city_detail_id="'.$child_city_code.'" and city_id="'.$maincity.'"';
            
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;

        }

        // read product shipment charges
        function product_shippment_charges(){

            $query    = "SELECT * FROM `dashboard_city_shipping_charges`  ";
            
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;

        }

        // read product ship amount
        function product_shippment_amount($product_code,$maincity){

            $query    = 'SELECT  `product_shipamount` FROM `product_sub_detail` WHERE `product_code` ="'.$product_code.'" and 
            city_id = "'.$maincity.'" limit 1';            
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;

        }

        // read product ship slab
        function product_shippment_slab($total_charges_nonfood){

            $query = "SELECT * FROM shipping_rate_slab WHERE `start` <= '{$total_charges_nonfood}' AND `end` >= '{$total_charges_nonfood}' AND `type`='N' ";
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;

        }

        //delivery charges slot
        function product_shippment_delivery_charges_slot($maincity){
            $query = "SELECT minimum_cart_value ,minimum_cart_value_nonfood, delivery_charges,delivery_charges_nonfood, IFNULL(qurbani_charges,0) AS qurbani_charges,zakat_charges FROM delivery_charges_slot where city_id=$maincity";

            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;
        }

        // read product ship slab
        function product_shippment_slab_food($total_charges_food){

            $query = "SELECT * FROM shipping_rate_slab WHERE `start` <= '{$total_charges_food}' AND `end` >= '{$total_charges_food}' AND `type`='F' ";
            // prepare query statement
            $stmt = $this->conn->prepare($query);

            // execute query
            $stmt->execute();

            return $stmt;

        }

    }
?>