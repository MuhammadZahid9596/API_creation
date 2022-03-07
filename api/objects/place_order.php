<?php
    class Place_order{
    
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
        function placeOrder($arr){

            //begin shipping detail array

            $city = $arr[0]['orderdetail'][0]['city_id'];
            $shippingname = $arr[0]['customerdetail'][0]["firstname"] . ' ' . $arr[0]['customerdetail'][0]["lastname"];
            $shippingaddress = $arr[0]['customerdetail'][0]["shipaddress"];
            $shippingcountry = $arr[0]['customerdetail'][0]["shipcountry"];
            $shippingarea = $arr[0]['customerdetail'][0]["shiparea"];
            $shippingcity = $arr[0]['customerdetail'][0]["shipcity"];
            $shippingemail = $arr[0]['customerdetail'][0]["shipemail"];
            $shippingphone = $arr[0]['customerdetail'][0]["shipphone"];
            $shippinglat = $arr[0]['customerdetail'][0]["shiplat"];
            $shippinglng = $arr[0]['customerdetail'][0]["shiplng"];
            $shippingcnic = $arr[0]['customerdetail'][0]["cnic"];
            
            if ($shippinglat == '') {
                $shippinglat = '0';
            }

            if ($shippinglng == '') {
                $shippinglng = '0';
            }
            //end shipping detail array

            //start billing detail array
            $billingname = $arr[0]['customerdetail'][0]["firstname"] . ' ' . $arr[0]['customerdetail'][0]["lastname"];
            $billingaddress = $arr[0]['customerdetail'][0]["billaddress"];
            $billingcountry = $arr[0]['customerdetail'][0]["billcountry"];
            $billingarea = $arr[0]['customerdetail'][0]["billarea"];
            $billingcity = $arr[0]['customerdetail'][0]["billcity"];
            $billingemail = $arr[0]['customerdetail'][0]["billemail"];
            $billingphone = $arr[0]['customerdetail'][0]["billphone"];
            $billinglat = $arr[0]['customerdetail'][0]["billlat"];
            $billinglng = $arr[0]['customerdetail'][0]["billlng"];
            $billingcnic = $arr[0]['customerdetail'][0]["cnic"];
            $comment = $arr[0]['customerdetail'][0]["comment"];

            
            if ($billinglat == '') {
                $billinglat = '0';
            }

            if ($billinglng == '') {
                $billinglng = '0';
            }
            //end shipping detail array

            //start delivery array
            $deliverytype = $arr[0]['orderdetail'][0]["delivery_type"];
            $deliverysubtotal = $arr[0]['orderdetail'][0]["subtotal"];
            $deliverytotalquantity = $arr[0]['orderdetail'][0]["totalquantity"];
            $deliverygrandtotal = $arr[0]['orderdetail'][0]["grandtotal"];
            $deliverytime = $arr[0]['orderdetail'][0]["deliveryid"];
            $deliverydate = $arr[0]['orderdetail'][0]["deliverydate"];

            $deliverypaymode = '';
            if ($arr[0]['orderdetail'][0]["paymentmode"] == '1') {
                $deliverypaymode = 'COD';
                $paymentmsg = 'Cash on Delivery';
                $sendmail = 1;
            } 
            elseif ($arr[0]['orderdetail'][0]["paymentmode"] == '2') {
                $deliverypaymode = 'MPOS';
                $paymentmsg = 'MPOS';
                $sendmail = 1;
            } 
            elseif ($arr[0]['orderdetail'][0]["paymentmode"] == '3') {
                $deliverypaymode = 'CC';
                $paymentmsg = 'Credit Card';
                $sendmail = 0;
            } 
            elseif ($arr[0]['orderdetail'][0]["paymentmode"] == '4') { 
                $deliverypaymode = 'JC';
                $paymentmsg = 'Jazz Cash';
                $sendmail = 0;
            }
            elseif ($arr[0]['orderdetail'][0]["paymentmode"] == '5') {
                $deliverypaymode = 'BT';
                $paymentmsg='Bank Transfer';
                $sendmail = 1;
            }
            elseif ($arr[0]['orderdetail'][0]["paymentmode"] == '6') {
                $deliverypaymode = 'EP';
                $paymentmsg='Easy Paisa';
                $sendmail = 0;
            }
            $deliveryorderchannel = "WEB";
            $deliverystoreid = $arr[0]['orderdetail'][0]["storeid"];
            $deliverycouponcode = $arr[0]['orderdetail'][0]["coupon_code"];
            $deliverycouponamount = $arr[0]['orderdetail'][0]["coupon_amount"];
            $assortment_type = $arr[0]['orderdetail'][0]["assortment_type"];
            $promotion_id = $arr[0]['orderdetail'][0]["promotioin_id"];
            $promotion_name = $arr[0]['orderdetail'][0]["promotion_name"];

            $shippingaddress =     htmlspecialchars(trim($shippingaddress), ENT_QUOTES);
            $billingaddress =     htmlspecialchars(trim($billingaddress), ENT_QUOTES);
    
            $qryod = "SELECT

            CONCAT(
           
            CONCAT(
           
            96,
           
            RIGHT ( YEAR ( CURDATE( ) ), 2 ),
           
            SUBSTRING( MAX( order_code ), 5, 6 ) + 1
           
            ),
           
            LPAD(WEEK ( CURDATE( ), 1 ), 2, '0'),
           
            FIELD( DATE_FORMAT( CURDATE( ), '%a' ), 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' )
           
            ) AS order_code
           
           FROM
           
            order_master";

            // prepare query statement
            $query_order_stmt = $this->conn->prepare($qryod);

            // execute query
            $query_order_stmt->execute();

            while ($row = $query_order_stmt->fetch(PDO::FETCH_ASSOC)){
                $order_code = $row['order_code'].$city;
            }

            // inserting into order master
            $query_insert_order_master = "INSERT INTO order_master(
                
            order_code, ship_name , ship_address , ship_country , ship_area,
            
            ship_contact , ship_city , ship_email , shiplat, shiplng,
            
            ship_cnic , payment_type , channel , delivery_time , shipping_charges,
            
            total_amount, total_quantity, delivery_date, acno, order_date,
            
            order_time, order_status, deleted, assortment_type, bill_name, 
            
            bill_email, bill_contact, bill_address, bill_city, bill_country,
            
            bill_area, billlat, billlng, customer_comment, city_id, loc_code,
            
            bill_cnic, coupon_code, coupon_amount, promotion_id) VALUES (
                
            '{$order_code}','{$shippingname}','{$shippingaddress}','{$shippingcountry}','{$shippingarea}',
            
            '{$shippingphone}','{$shippingcity}', '{$shippingemail}','{$shippinglat}','{$shippinglng}' ,
            
            '{$shippingcnic}','{$deliverypaymode}','{$deliveryorderchannel}','{$deliverytime}', '{$deliverygrandtotal}',
            
            '{$deliverysubtotal}','{$deliverytotalquantity}','{$deliverydate}','LHE-01262', NOW(),
            
             NOW(),'W','','{$assortment_type}','{$billingname}',
             
            '{$billingemail}','{$billingphone}', '{$billingaddress}','{$billingcity}','{$billingcountry}' ,
            
            '{$billingarea}','{$billinglat}','{$billinglng}', '{$comment}', '{$city}', '{$deliverystoreid}',
            
            '{$billingcnic}','{$deliverycouponcode}','{$deliverycouponamount}','{$promotion_id}')";

            // prepare query statement
            $insert_order_master_stmt = $this->conn->prepare($query_insert_order_master);

            // execute query
            if($insert_order_master_stmt->execute()){
                // return $stmt;
                
                // checking if coupon code exists or not
                if ($deliverycouponcode != '0') {

                    $query_insert_coupon = "INSERT INTO customer_coupon_used_status (promotion_id , member_id , coupon ,
                                            status , acno, used_datetime, order_code, used_amount) 
                                            VALUES ('{$promotion_id}',(SELECT id FROM members WHERE email='{$shippingemail}'),
                                            '{$deliverycouponcode}', '1', 'LHE-01262', NOW(),'{$order_code}','{$deliverycouponamount}')";

                    // prepare query statement
                    // $stmt = $this->conn->prepare($query_insert_coupon);

                    // // execute query
                    // $stmt->execute();

                    // echo $query_insert_coupon;
                    $query_update_coupon_status = "update customer_coupon_status set status='1', used_datetime=NOW() where coupon='{$deliverycouponcode}' and member_id=(SELECT id FROM members WHERE email='{$shippingemail}')";

                    // prepare query statement
                    // $stmt = $this->conn->prepare($query_update_coupon_status);

                    // // execute query
                    // $stmt->execute();

                    $query_update_coupon_master = "UPDATE `customer_coupon_master` SET `balance_discount_value` = `balance_discount_value`-{$deliverycouponamount} WHERE `coupon`='{$deliverycouponcode}' and coupon_type='giftcard'";
                    // prepare query statement
                    // $stmt = $this->conn->prepare($query_update_coupon_master);

                    // // execute query
                    // $stmt->execute();

                //}

                }

                $query_time_slot = "SELECT start_time , end_time FROM delivery_time_slot WHERE id = '$deliverytime'";
                $timeslot_stmt = $this->conn->prepare($query_time_slot);

                // execute query
                $timeslot_stmt->execute();

                // return $stmt;
                while ($timeslot_result = $timeslot_stmt->fetch(PDO::FETCH_ASSOC)){
                    $start_time = $timeslot_result['start_time'];
                    $end_time = $timeslot_result['end_time'];
                }
                $displayslot = date("h:i a", strtotime($start_time)) . ' - ' .date("h:i a", strtotime($end_time));
                $delivery_datetime = $deliverydate . "," . $start_time . "," . $end_time;
                
                $query_order_status = "INSERT INTO order_status (datatime , ordercode , status , delivery_datetime ,
                                        usrid , acno , status_text,city_id) 
                                        VALUES (NOW() , '$order_code' , 'W' , '$delivery_datetime' , 'Shopsy API' ,
                                        'LHE-01262' , 'Awaiting Confirmation','{$city}')";
                    
                $order_status_stmt = $this->conn->prepare($query_order_status);
        
                // execute query
        
                $order_status_stmt->execute();
                
                $serial_no = 1;

                foreach ($arr[0]['cartdetails'] as $key => $value) {
                    $query_weight = "SELECT product_weight,product_featured_text,
                                    CEIL( IFNULL( `product_view_new`.`product_price`, 0 ) ) AS product_price,
                                    IF(( `product_view_new`.`product_sale` = 'Y' ),
                                    ( CEIL( IFNULL( `product_view_new`.`product_sale_price`, 0 ) ) ),
                                    ( CEIL( IFNULL( `product_view_new`.`product_price`, 0 ) ) )) AS new_price,
                                    IFNULL( product_view_new.`product_weight_type`, 'N' ) AS product_weight_type 
                                    FROM product_view_new 
                                    WHERE product_code = '{$value['refcode']}' and city_id='{$city}' 
                                    and product_loc_code ='{$deliverystoreid}' LIMIT 1";

                    $query_weight_stmt = $this->conn->prepare($query_weight);
            
                    // execute query

                    $query_weight_stmt->execute();
                    
                    while ($resweight = $query_weight_stmt->fetch(PDO::FETCH_ASSOC)){
                        $product_weight = $resweight ['product_weight'];
                        $weight_type = $resweight['product_weight_type'];
                        $product_price = $resweight['product_price'];
                        $product_featured_text = $resweight['product_featured_text'];
                    }
                    
                    if ($value["weight"] == 'N') {

                        $cartdetailsquantity = $value["quantity"];

                        $total_weight = '0';

                        $cartqty = $value["quantity"];

                    } else {

                        $cartdetailstotalquantity = '1';

                        $total_weight = $value["quantity"];

                        $cartqty = $value["quantity"];

                    }

                    if ($weight_type == 'KG') {

                        $actual_amount = ($product_price * $total_weight) / $product_weight;

                    } else {

                        $actual_amount = $product_price;

                    }

                    // echo $actual_amount;

                    $zeropriceerror='0';
                
                    $query_insert_order_detail = "INSERT INTO order_detail (
                            order_code , serial_no, product_type, item_number , item_name ,
                            item_price, item_oldprice, item_quantity, weight, actual_price, 
                            discount, total_weight, product_weight, item_vari, sku_desc,city_id,
                            assortment_type)
                            VALUES (
                                '$order_code','$serial_no', 'products', '{$value['refcode']}', '{$value['productname']}',
                                '{$value['unitproductprice']}','{$value['unitproductprice']}', '{$cartdetailsquantity}','{$value['weight']}','{$actual_amount}',
                                '{$value['unitpricediscount']}','{$total_weight}',
                            (SELECT IFNULL(product_weight,'0') 
                                FROM product 
                            WHERE product_code = '".$value['refcode']."' LIMIT 1)
                            ,
                            '{$value['refcode']}00','none','{$city}','$assortment_type')";

                    $insert_order_detail_stmt = $this->conn->prepare($query_insert_order_detail);
            
                    // execute query

                    $insert_order_detail_stmt->execute();
                            
                    // echo $query_insert_order_detail;
                    $serial_no++;
                }
                $responsearray = array("status" => "1","message" => "order placed successfully" ,"ordercode" => $order_code);
            }
            else{
                $responsearray = array("status" => "0","message" => "order not placed");
            }
            echo json_encode($responsearray);    
        }
    }
?>