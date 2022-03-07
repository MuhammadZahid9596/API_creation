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
    include_once '../objects/order_status.php';

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
    $order_status = new Order_status($db);
        
    // set ID property of record to read
    $order_status->order_code = isset($_REQUEST['order_code']) ? $_REQUEST['order_code'] : die();
    
    // read order_statuss will be here

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
            // query order_statuss
            $stmt = $order_status->read();
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

  
// check if more than 0 record found
if($num>0){
  
    // payments array
    $all_images=array();
    $order_arr["records"]=array();

    $prefix_url = "https://metro-online.pk/detail/";
    $payment_variants = "-";
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $order_item = array(
            "status" => $row['status_text'],
            "ordercode" => $row['order_code']  
        );

        array_push($order_arr["records"], $order_item);

    }
    
    // set response code - 200 OK
    http_response_code(200);
  
    // show products data in json format
    echo json_encode($order_arr);
}
  
else{
  
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no products found
    echo json_encode(
        array("message" => "No products found.")
    );
}