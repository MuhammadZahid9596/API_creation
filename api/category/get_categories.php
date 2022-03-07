<?php
    // required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
 
    
    // database connection will be here
    // include database and object files
    include_once '../config/database.php';
    include_once '../objects/category.php';
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
    $category = new Category($db);
        
        
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
            // query products
            $stmt = $category->level1();
            $counter = $stmt->rowCount();
        }
        else{
            echo 'key expired';
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
if($counter>0){
      // products array
    $all_images=array();
    $category_arr["records"]=array();
  
    $prefix_url = "https://metro-online.pk/detail/";
    $product_variants = "-";
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
        $level2_arr=array();
        $category_item=array(
            "id"  => $id,
            "name"  => $name,
            "type"  => $type,
            "level"  => 'Level 1',
            "sub_array"  => $level2_arr
        );

        //-------------level2--------------//
        $stmt2 = $category->level2($id);
        $counter2 = $stmt2->rowCount();
        if($counter2>0){
            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
                $level3_arr=array();
                $level2_arr = array(
                    "id"  => $row2['id'],
                    "name"  => $row2['name'],
                    "type"  => $row2['type'],
                    "level"  => 'Level 2',
                    "sub_array"  => $level3_arr
                );

                //-----------------level3-----------------//
                $stmt3 = $category->level3($row2['id']);
                $counter3 = $stmt3->rowCount();
                if($counter3 > 0){
                    while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)){
                        $level4_arr=array();
                        $level3_arr = array(
                            "id"  => $row3['id'],
                            "name"  => $row3['name'],
                            "type"  => $row3['type'],
                            "level"  => 'Level 3',
                            "sub_array"  => $level4_arr
                        );
               
                        //-----------------level4-----------------//
                        $stmt4 = $category->level4($row3['id']);
                        $counter4 = $stmt4->rowCount();
                        if($counter4 > 0){
                            while ($row4 = $stmt4->fetch(PDO::FETCH_ASSOC)){
                                $level4_arr = array(
                                    "id"  => $row3['id'],
                                    "name"  => $row3['name'],
                                    "type"  => $row3['type'],
                                    "level"  => 'Level 4',
                                );
                                array_push($level3_arr["sub_array"], $level4_arr);
                            }            
                        }
                        array_push($level2_arr["sub_array"], $level3_arr);
                    }
                }
                array_push($category_item["sub_array"], $level2_arr);
            }
        }
        array_push($category_arr["records"], $category_item);
    }
        
    // set response code - 200 OK
    http_response_code(200);
  
    // show products data in json format
    echo json_encode($category_arr);
}
  
else{
  
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no products found
    echo json_encode(
        array("message" => "No products found.")
    );
}