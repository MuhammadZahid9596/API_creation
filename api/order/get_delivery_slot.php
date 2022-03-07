<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    // database connection will be here
    // include database and object files
    include_once '../config/database.php';
    include_once '../objects/delivery_slot.php';

    // required to decode jwt
    include_once '../config/core.php';
    include_once '../config/database.php';
    include_once '../objects/database_conn.php';
    include_once '../libs/php-jwt-master/src/BeforeValidException.php';
    include_once '../libs/php-jwt-master/src/ExpiredException.php';
    include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt-master/src/JWT.php';
    use \Firebase\JWT\JWT;

    // instantiate database and product object
    $database = new Database();
    $db = $database->getConnection();
    
    // initialize object
    $delivery_slot = new Delivery_slot($db);
        
    // set ID property of record to read
    $delivery_slot->city_id = isset($_REQUEST['city_id']) ? $_REQUEST['city_id'] : die();

    $delivery_slot->loc_code = isset($_REQUEST['loc_code']) ? $_REQUEST['loc_code'] : die();
    

    
     // get posted data
   $data = json_decode(file_get_contents("php://input"));
    
   // get jwt
   $jwt=isset($data->jwt) ? $data->jwt : "";
   
   //---------get all headers---------//
   $headers = getallheaders();
    
   $database = new Database();
   $db = $database->getConnection();
   $db_conn = new Database_conn($db);
   if($headers['user_key']){
       $stmt = $db_conn->validate_token($headers['user_key']);
       $num = $stmt->rowCount();
       if($num > 0){
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
           $insert_date =  $row['insert_date'];
           $end_date =  $row['end_date'];
        }
        //------- checking the token is in validity range or not-------//
        if (strtotime(date('Y-m-d H:i:s')) > strtotime($insert_date) && strtotime(date('Y-m-d H:i:s')) < strtotime($end_date)){
            // query payments
            $stmt = $delivery_slot->read();
            $num = $stmt->rowCount();
        }
        else{
            echo 'key not valid';
            exit;
        }     
       }
       else{
           echo 'key not valid';
           exit;
       }
   }
   else{
       echo 'key cannot be empty';
       exit;
    }


if($num > 0){

    // $delivery_slot_arr = array();
    $assortment_prev = "";
    $delivery_slot_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        $product_type = "";
        $time_slot_type = "";
        $assortment_curr = $row['assortment_type'];
        
        //----------current  delivery type----------//
        if($row['time_slot_type'] == 0 ){
            $time_slot_type = "Delivery";
        }
        else{
            $time_slot_type = "Pick up";
        }

        //--------current assortment type------//
        if($assortment_curr == 'F'){
            $product_type = 'food';
        }
        else if($assortment_curr == 'N'){
            $product_type = 'nonfood';
        }
        else if($assortment_curr == 'EX'){
            $product_type = 'express';
        }
        else if($assortment_curr == 'OS'){
            $product_type = 'organic';
        }

        
        $stmt2 = $delivery_slot->delivery_slots($row['assortment_type'],$row['time_slot_type']);
        $delivery_slot_arr_sub = array();

        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){

            $stmt3 = $delivery_slot->order_master($row2['id']);
            while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)){
                var_dump($row3);
            }
            //----------getting time slot---------//
            $delivery_slot_arr_sub[$row2['day']][]= array(
                "value" => $row2['id'],
                "day" => $row2['day'],
                "date" => date("d-m-Y",strtotime(substr($row2['entry_datetime'] ,0 , 10))),
                "timing" => date('h:i:s a', strtotime($row2['start_time']))." - ".date('h:i:s a', strtotime($row2['end_time'])),
                "status" => $row2['status']
            );
        }
        //---------main array delivery--------//    
        $delivery_slot_arr[$product_type][] = array(
            "type_code" => $row['time_slot_type'],
            "type" => $time_slot_type,
            "status" => $row['status'],
            "delivery_slot" => $delivery_slot_arr_sub
        );
       
    }
    // var_dump($delivery_slot_arr);
    echo json_encode($delivery_slot_arr);
}
else{
  
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no products found
    echo json_encode(
        array("message" => "No products found.")
    );
}
?>