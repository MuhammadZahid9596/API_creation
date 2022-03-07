<?php
    // generate json web token
    include_once '../config/core.php';
    include_once '../config/database.php';
    include_once '../objects/database_conn.php';
    include_once '../libs/php-jwt-master/src/BeforeValidException.php';
    include_once '../libs/php-jwt-master/src/ExpiredException.php';
    include_once '../libs/php-jwt-master/src/SignatureInvalidException.php';
    include_once '../libs/php-jwt-master/src/JWT.php';
    use \Firebase\JWT\JWT;

    //---------db connection-------//
 
    $database = new Database();
    $db = $database->getConnection();
    $db_conn = new Database_conn($db);
    $db_conn->insert_date = date('Y-m-d H:i:s');
    $db_conn->validity_date = date('Y-m-d H:i:s', strtotime('+2 hours'));
    $db_conn->ip = $_SERVER['REMOTE_ADDR'];
    
    //---------get all headers---------//
    $headers = getallheaders();
    
    //--------check user key from header--------------//
    if($headers['user_key']){
        if( $headers['user_key'] == 'kmq7ld09ktma8rwd'){
            $token = array(
                    "iat" => $issued_at,
                    "exp" => $expiration_time,
                    "iss" => $issuer,
                    "user_key" => "kmq7ld09ktma8rwd",
                    "data" => array(
                    "ip" =>  $_SERVER['REMOTE_ADDR'],
                )
             );
          
             // set response code
             http_response_code(200);
          
             // generate jwt
             $jwt = JWT::encode($token, $key);
             $db->jwt = $jwt;
             echo json_encode(
                     array(
                        "status" => "1", 
                        "message" => "Successfully Generated",
                        "token" => $jwt
                     )
                 );
            
            // get the database connection
            try {
                $stmt = $db_conn->insert_token($jwt, $db_conn->insert_date, $db_conn->validity_date, $db_conn->ip );
                // echo "inserted";
            } catch(PDOException $e) {
                echo $insert_key_details . "<br>" . $e->getMessage();
            }
        }
        //----------------trying to trick with wrong key aaan :-p--------------------
        else{
            echo json_encode(
                array(
                    "status" => "0",
                    "message" => "Wrong Key",
                )
            );
        }
    }
    //------------------generate key please---------------//
    else{
        echo json_encode(
            array(
                "status" => "0",
                "message" => "Key cannnot be empty",
            )
        );    
    }
    
?>