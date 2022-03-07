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
    include_once '../objects/shipment.php';

    
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
    $shipment = new Shipment($db);

    // read products will be here

    // get posted data
   $data = json_decode(file_get_contents("php://input"));
    
   // get jwt
   $jwt=isset($data->jwt) ? $data->jwt : "";
   
   //---------get all headers---------//
   $headers = getallheaders();
    
   $database = new Database();
   $db = $database->getConnection();
   $db_conn = new Database_conn($db);

    $total_charges_nonfood = '0';
    $array = (array) $data;

    $total_charges_nonfood= 0;
    $total_charges_food= 0;
    $total_charges_foodExpress =  0;
    $count_weight= 0;
    $count_qty= 0;
    $count_price= 0;
    $multiplication_var = 0;
    $total_charges_sum = 0;
    $total_charges_sum_foodExpress = 0;
    //$array["otherstatus"] = "Y"

    foreach($array["cartproduct"] as $item){
        $item_arr = (array) $item;
        $stmt = $shipment->product_shippment_weight($item_arr["ref_code"]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            // print_r($row);
            $row['product_shipping_weight'] * $item_arr['quantity'];
            $count_weight += $row['product_shipping_weight'] * $item_arr["quantity"] * 1;
            $count_qty += $item_arr["quantity"] * 1;
            $count_price += $item_arr["quantity"] * $item_arr["totalprice"];
            $total_charges_sum += $item_arr["totalprice"]*1;
            $total_charges_sum_foodExpress += $item_arr["totalprice"]*1;

        }   
    }
    
    
    // if assortment type is nonfood 
    if($array["assortment_type"] != 'F' OR $array["assortment_type"] != 'EX'){
        if($array["deliverytype"] == 1){
            $total_charges_nonfood = '0';
        }
        else{
            if($array["city_id"] == 5){
                // shipment zones
                $stmt = $shipment->product_shippment_zone($array["childcitycode"],$array["city_id"]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $zone = $row["zone"];
                }
                
                // shipment charges
                $stmt = $shipment->product_shippment_charges();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    if($row['min_weight'] <= $count_weight && $row['max_weight'] >= $count_weight){
                                        
                        if($zone=='Self'){
                            $total_charges_nonfood = $row['self'];
                        }

                        elseif($zone=='Zone A'){
                            if($row['id']=='3'){
                                //formula 190 + ((W-1)*170) + TI (X)
                                $total_charges_nonfood = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                            }
                            elseif($row['id']=='5'){
                                //formula 400 + ((W-10) * 30) + TI (X)
                                $total_charges_nonfood = 400+(($count_weight-10)*30)+$count_qty*($multiplication_var);
                            }
                            else{
                                $total_charges_nonfood = $row['zone_a'];
                            }
                        }

                        elseif($zone=='Zone B - Fast'){
                            if($row['id']=='3'){
                            //formula 205 + ((W-1)*180) + TI (X)
                                $total_charges_nonfood = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                            }
                            elseif($row['id']=='5'){
                                //formula 400 + ((W-10) * 30) + TI (X)
                                $total_charges_nonfood = 400+(($count_weight-10)*30)+$count_qty*($multiplication_var);
                            }
                            else{
                                $total_charges_nonfood = $row['zone_b_fast'];
                            }
                        }

                        elseif($zone=='Zone B - Slow'){
                            if($row['id']=='3'){
                                //formula 205 + ((W-1)*180) + TI (X)
                                $total_charges_nonfood = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                            }
                            elseif($row['id']=='5'){
                                //formula  400 + ((W-10) * 30) + TI (X)
                                $total_charges_nonfood = 400+(($count_weight-10)*30)+$count_qty*($multiplication_var);
                            }
                            else{
                                $total_charges_nonfood = $row['zone_b_slow'];
                            }
                        }
                        
                        elseif($zone=='Zone C'){
                            if($row['id']=='3'){
                                //formula 205 + ((W-1)*180) + TI (X)
                                $total_charges_nonfood = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                            }
                            elseif($row['id']=='4'){
                                //formula 205 + ((W-1)*180) + TI (X)
                                $total_charges_nonfood = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                            }
                            elseif($row['id']=='5'){
                                //formula  450 + ((W-5) * 90) + TI (X)
                                $total_charges_nonfood = 450+(($count_weight-5)*90)+$count_qty*($multiplication_var);
                            }
                            elseif($row['id']=='6'){
                                //formula  450 + ((W-5) * 90) + TI (X)
                                $total_charges_nonfood = 450+(($count_weight-5)*90)+$count_qty*($multiplication_var);
                            }
                            else{
                                $total_charges_nonfood = $row['zone_c'];
                            }
                        }
                    }
                }
            }
            else{
                foreach($array["cartproduct"] as $item){
                    $stmt = $shipment->product_shippment_amount($item_arr["ref_code"],$array["city_id"]);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        $shipamount = $row['product_shipamount'];
                        $total_charges_nonfood += $shipamount * $item_arr["quantity"] * 1;
                    }
                    $stmt = $shipment->product_shippment_slab($total_charges_nonfood);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        if($row[$array["city_id"]]!=0){
                            $total_charges_nonfood =  $row[$array["city_id"]];
                        }
                        $total_charges_nonfood = ceil($total_charges_nonfood / 100) * 100;
                    }
        
                }
            }
            if($total_charges_nonfood!='0'){
                $total_charges_nonfood = $total_charges_nonfood;
            }
        }
    }

    //if assortment type is food
    if($array["assortment_type"] == 'F'){
        if($array["deliverytype"]=='1'){         
            $total_charges_food = ceil($count_qty / 20) * 41;
            
            if($total_charges_food > '82'){
                $total_charges_food = '82';
            }
                
            $total_charges_food = $total_charges_food;
            
            $total_charges_food = '0';
            
        }
        else{
            if($array["city_id"] == 5){
                    
                //foreach ($arr as $k=>$v){
                //$maincity = $v["maincity"];
                //$childcity = $v["childcity"];
                //echo '<br>';
                $total_charges_food='0';
                $count_weight='0';
                $count_qty='0';
                $multiplication_var ='0';

                $stmt = $shipment->product_shippment_zone($array["childcitycode"],$array["city_id"]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $zone = $row["zone"];
                }
                $stmt = $shipment->product_shippment_charges();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    if($row['min_weight'] <= $count_weight && $row['max_weight'] >= $count_weight){
                                
                        if($zone=='Self'){
                        $total_charges_food = $row['self'];
                        }
                        elseif($zone=='Zone A'){
                        if($row['id']=='3'){
                            //formula 190 + ((W-1)*170) + TI (X)
                            $total_charges_food = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                        }elseif($row['id']=='5'){
                            //formula 400 + ((W-10) * 30) + TI (X)
                            $total_charges_food = 400+(($count_weight-10)*30)+$count_qty*($multiplication_var);
                        }else{
                            $total_charges_food = $row['zone_a'];
                        }
                    }
                    elseif($zone=='Zone B - Fast'){
                        if($row['id']=='3'){
                            //formula 205 + ((W-1)*180) + TI (X)
                            $total_charges_food = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                        }elseif($row['id']=='5'){
                            //formula 400 + ((W-10) * 30) + TI (X)
                            $total_charges_food = 400+(($count_weight-10)*30)+$count_qty*($multiplication_var);
                        }else{
                            $total_charges_food = $row['zone_b_fast'];
                        }
                    }
                    elseif($zone=='Zone B - Slow'){
                        if($row['id']=='3'){
                            //formula 205 + ((W-1)*180) + TI (X)
                            $total_charges_food = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                        }elseif($row['id']=='5'){
                            //formula  400 + ((W-10) * 30) + TI (X)
                            $total_charges_food = 400+(($count_weight-10)*30)+$count_qty*($multiplication_var);
                        }else{
                            $total_charges_food = $row['zone_b_slow'];
                        }
                    }
                    elseif($zone=='Zone C'){
                        if($row['id']=='3'){
                            //formula 205 + ((W-1)*180) + TI (X)
                            $total_charges_food = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                        }elseif($row['id']=='4'){
                            //formula 205 + ((W-1)*180) + TI (X)
                            $total_charges_food = 180+(($count_weight-1)*120)+$count_qty*($multiplication_var);
                        }elseif($row['id']=='5'){
                            //formula  450 + ((W-5) * 90) + TI (X)
                            $total_charges_food = 450+(($count_weight-5)*90)+$count_qty*($multiplication_var);
                        }elseif($row['id']=='6'){
                            //formula  450 + ((W-5) * 90) + TI (X)
                            $total_charges_food = 450+(($count_weight-5)*90)+$count_qty*($multiplication_var);
                        }else{
                            $total_charges_food = $row['zone_c'];
                        }
                    }
                }
                }
                if($total_charges_food!='0'){
                    $total_charges_food = $total_charges_food;
                }
            }
            else if($array["city_id"]==1 || $array["city_id"]==2 || $array["city_id"]==3 || $array["city_id"]==4 || $array["city_id"]==7){
                $stmt = $shipment->product_shippment_delivery_charges_slot($array["city_id"]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $minimum_cart_value = $row["minimum_cart_value"];
                    $total_charges_food = $row["delivery_charges"];
                }   
                if($total_charges_sum >= $minimum_cart_value){
                    $total_charges_food = '0';
                }else{
                    //check slab 
                    $stmt = $shipment->product_shippment_slab_food($total_charges_food);
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        if($row[$array["city_id"]]!=0){
                            $total_charges_food =  $row[$array["city_id"]];
                        }
                    }
                    $total_charges_food = $total_charges_food;
                }
            }
        }
    }

    // if assortment type is express
    if($array["assortment_type"] == 'EX'){  
        if($array["deliverytype"] == '1'){
            $total_charges_foodExpress = '0';
        }else{    
            $total_charges_foodExpress='49';
        }
    } 
    // echo 'Rs'.$count_price.' Quantity'.$count_qty.' Wegiht'.$count_weight.'shipp'.$total_charges_food;
    $cart_arr = array(
        "Total Price" => "Rs ".$count_price,
        "Quantity" => $count_qty." KG",
        "Total Weight" => $count_weight,
        "Total Charges" => "Rs ".$total_charges_food
    );
    echo json_encode($cart_arr);

//    if($headers['user_key']){
//        $stmt = $db_conn->validate_token($headers['user_key']);
//        $num = $stmt->rowCount();
//        if($num > 0){
//         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//            $insert_date =  $row['insert_date'];
//            $end_date =  $row['end_date'];
//         }
//         //------- checking the token is in validity range or not-------//
//         if (strtotime(date('Y-m-d H:i:s')) > strtotime($insert_date) && strtotime(date('Y-m-d H:i:s')) < strtotime($end_date)){
//             // query products
//             $stmt = $product->read();
//             $num = $stmt->rowCount();              }
//         else{
//             echo 'key not valid';
//             exit;
//         }     
//        }
//        else{
//            echo 'key not valid';
//            exit;
//        }
//    }
//    else{
//        echo 'key cannot be empty';
//        exit;
//     }

// check if more than 0 record found
// if($num>0){
  
//     // products array
//     $products_arr=array();
//     $all_images=array();
//     $products_arr["records"]=array();
  
//     $prefix_url = "https://metro-online.pk/detail/";
//     $product_variants = "-";
//     // retrieve our table contents
//     // fetch() is faster than fetchAll()
//     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//         // extract row
//         // this will make $row['name'] to
//         // just $name only
//         extract($row);
//         $product_code = $product_code;
//         $product_name_dash = str_replace(array('&', '; ',' '),"-",$product_name); 
//         $product_name_dash = rtrim($product_name_dash,"-").'/'.$product_code; 

//         $product_item=array(
//             "product_ref" => $product_ref,
//             "product_name" => $product_name,
//             "product_desc" => html_entity_decode($product_desc),
//             "img" => $img,
//             "all_images" => $all_images,
//             "product_old_price" => $product_old_price,
//             "product_price" => $product_price,
//             "bread_crumb" => $product_url,
//             "product_url" => $prefix_url.$product_url.'/'.$product_name_dash,
//             "quantity" => $quantity,
//             "brand_name" => $brand_name,
//             "product_variants" => $product_variants,
//             "rating" => $product_scoring
//         );
  
//         array_push($products_arr["records"], $product_item);
//     }
  
//     // set response code - 200 OK
//     http_response_code(200);
  
//     // show products data in json format
//     echo json_encode($products_arr);
// }
  
// else{
  
//     // set response code - 404 Not found
//     http_response_code(404);
  
//     // tell the user no products found
//     echo json_encode(
//         array("message" => "No products found.")
//     );
// }