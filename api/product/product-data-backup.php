<?php

$acno = $_SESSION['ac'];

$usrid = $_SESSION['usrid'];

error_reporting(0);

switch ($_REQUEST['type']) {



        case 'get_predefined_vari_list':

            $brand=$_REQUEST['brand'];

            $list_name=$_REQUEST['list_name'];

            $qry="select * from product_predefine_var_list where var_type='".$list_name."' ";

            $row=simplexml_load_string($runQ->RunQuery($qry));

            $i=1;

            $htm="";

            foreach($row->row as $rows){

            

            $htm .="<input type='text' readonly='readonly' name='vari_name".$i."' class='span3' value='".$rows->var_name."' id='".$i."' style='margin-bottom:0px;text-align:center;margin-left:5px;'><i class='splashy-remove' style='cursor:pointer;margin-left:-19px;' id='remove_vari_list_new' onclick='remove_vari_list_new(this,".$i.")' id='rem_vari_list'></i>";

            $i++;

            }

            echo $htm;

       return;

       break;

 

 

     //Import Product

    case 'importproduct' :

        //------------new code of product import

        foreach ($_FILES["files"]["error"] as $key => $error) {

            if ($error == UPLOAD_ERR_OK) {

                $filename = $_FILES["files"]["name"][$key];

                @$ext = end(explode(".", $filename));

                $namewx = $_SESSION['ac'] . date('dmyHms');

                $name = $namewx . "." . $ext;

                if (!file_exists('import')) {

                    mkdir('import', 0777, true);

                }

                if (move_uploaded_file($_FILES["files"]["tmp_name"][$key], "import/" . $name)) {

                    //now import data from file

                    $handle = fopen("import/" . $name, "r");

                    if ($handle !== FALSE) {

                        fgets($handle);

                        $all_codes = array();

                        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {



 



                            //---------------------------PRODUCT START----------------------------------

                            //check if product exist then generate code

                            $product_ref = $data[0];

                            

                            $qry = "SELECT count(*) as cnt,product_code	FROM product WHERE product_ref = '$product_ref' AND acno = '$acno'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $count = $xres->row->cnt;

                            if ($count == 0) {

                                //generate product code

                                $qry = "select ifnull(max(product_code), cast(concat(substr('$acno',5,5),'0000') as unsigned))+1 as product_code

					from product

					where acno = '$acno'";

                                $res = $runQ->RunQuery($qry);

                                $xres = simplexml_load_string($res);

                                $product_code = $xres->row->product_code;

                            } else {

                                $product_code = $xres->row->product_code;

                            }

                            

                            $description = str_replace("'", '"', $data[14]);

                            

                            $description =  htmlspecialchars($description, ENT_QUOTES);

        

                                

                            

                            $qry = "REPLACE INTO `product` (`acno`, `product_code`, `product_name`, `product_date`, `product_time`, `product_ref`,`product_stat`,`product_sale`,`product_featured`,product_inv,	product_weight_type,product_weight,product_type,product_related,product_shipping_weight,brand_code,product_desc) VALUES ('$acno', $product_code, '{$data[1]}', curdate(), date_format(now(),'%H%i'),'{$data[0]}','A','N','N','yes','N','1','A','5','{$data[7]}',(select id from brand where name='{$data[2]}' limit 1),'{$description}')";

                            $runQ->RunQuery($qry);

                            

                            $results = glob('prod-pic/LHE-01262/' .$product_code.'-0-A.*');

                            if(sizeof($results)>0){

                                $qry = 'UPDATE product SET product_exist = "1" where product_code = "'.$product_code.'"';

                                $res = $runQ->RunQuery($qry);

                            }else{

                                $qry = 'UPDATE product SET product_exist = "0" where product_code = "'.$product_code.'"';

                                //echo $qry;

                                $res = $runQ->RunQuery($qry);

                            }

                                

                            //---------------------------PRODUCT END---------------------------



                            /*$level1 = str_replace("'", "\'", $data[3]);

                            $level2 = str_replace("'", "\'", $data[4]);

                            $level3 = str_replace("'", "\'", $data[5]);

                            $level4 = str_replace("'", "\'", $data[6]);*/

                            

                            $level1 = $data[3];

                            $level2 = $data[4];

                            $level3 = $data[5];

                            $level4 = $data[6];

                            

                            

                            

                            $level1 = preg_replace('/\s+/S', " ", $level1);

                            $level2 = preg_replace('/\s+/S', " ", $level2);

                            $level3 = preg_replace('/\s+/S', " ", $level3);

                            $level4 = preg_replace('/\s+/S', " ", $level4);

                            

                            

                            $qry = "SELECT id FROM product_hierarchy_four WHERE name = '$level4'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level4 = $xres->row->id;



                            $qry = "SELECT id FROM product_hierarchy_three WHERE name = '$level3'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level3 = $xres->row->id;



                            $qry = "SELECT id FROM product_hierarchy_two WHERE name = '$level2'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level2 = $xres->row->id;



                            $qry = "SELECT id FROM product_hierarchy_one WHERE name = '$level1'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level1 = $xres->row->id;







                            $qrys = "DELETE FROM product_category where product_code = '".$product_code."'";

                            $runQ->RunQuery($qrys);

                            

                            

                            $qrys = "DELETE FROM product_sub_detail where product_code = '".$product_code."'";

                            $runQ->RunQuery($qrys);

                            

                            

                             

                            //---------------------------CATEGORY STRAT---------------------------

                            //get category code

                             for ($i = 1; $i < 6; $i++) {

                                 

                                 $qry = "INSERT INTO `product_category`(`city_id`,`product_code`, `level_one`, `level_two`, `level_three`, `level_four`, `level_five`, `level_six`, `acno`) VALUES ('".$i."','".$product_code."','".$level1."','".$level2."','".$level3."','".$level4."','0','0','LHE-01262')";

                            $runQ->RunQuery($qry);

                            

                            if($i=='3'){

                                $product_shipamount = $data[8]*1.03;

                            }else{

                                $product_shipamount = $data[8];

                            }

                            

                            $product_commission = $data[9];

                             $qry = "INSERT INTO  `product_sub_detail` ( `city_id`,`product_code`,`product_ref`,`product_related`,`product_stat`,isHamper,product_shipamount,product_commission,product_assortment_type,product_order_limit,product_featured_text,product_price,product_sale_price,product_sale,sku_qty)

                            VALUES( '{$i}','{$product_code}','{$product_ref}','5','I','N','{$product_shipamount}','{$product_commission}','A','2','0','{$data[10]}','{$data[11]}','{$data[12]}','{$data[13]}')";

                            $runQ->RunQuery($qry);



                             }

                            //---------------------------CATEGORY END-----------------------------





                            

                            

                            //--------------------SKU START--------------

                            //create none sku of product

                             $qry = "REPLACE into sku_detail (sku_code, product_code, acno, sku_def, sku_desc, sku_weight, sku_price, sku_qty)

												values(concat($product_code,'00'), $product_code, '$acno', 'none', 'none', null, null, 100000)";

                            $runQ->RunQuery($qry);

                            //-----------------SKU END-------------------





                        }

                        fclose($handle);

                            

                        echo 'File imported succussfully !';

                    } else {

                        echo 'File not importing !';

                    }



                } else {

                    echo 'Error in file uploading';

                }

            }

        }



        return;

        break;

        

    //Import Product OLD

    case 'importproductold' :

        //------------new code of product import

        foreach ($_FILES["files"]["error"] as $key => $error) {

            if ($error == UPLOAD_ERR_OK) {

                $filename = $_FILES["files"]["name"][$key];

                @$ext = end(explode(".", $filename));

                $namewx = $_SESSION['ac'] . date('dmyHms');

                $name = $namewx . "." . $ext;

                if (!file_exists('import')) {

                    mkdir('import', 0777, true);

                }

                if (move_uploaded_file($_FILES["files"]["tmp_name"][$key], "import/" . $name)) {

                    //now import data from file

                    $handle = fopen("import/" . $name, "r");

                    if ($handle !== FALSE) {

                        fgets($handle);

                        $all_codes = array();

                        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {



                            $product_ref = $data[0];

                            /*$level4 = $data[2];

                            $level3 = $data[3];

                            $level2 = $data[4];

                            $level1 = $data[5];



                            $qry = "SELECT product_code,product_ref FROM product WHERE product_ref = '$product_ref'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $product_code = $xres->row->product_code;

                            $product_r = $xres->row->product_ref;







                            if($product_r == $product_ref){



                            $qry = "SELECT id FROM product_hierarchy_four WHERE name = '$level4'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level4 = $xres->row->id;



                            $qry = "SELECT id FROM product_hierarchy_three WHERE name = '$level3'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level3 = $xres->row->id;



                            $qry = "SELECT id FROM product_hierarchy_two WHERE name = '$level2'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level2 = $xres->row->id;



                            $qry = "SELECT id FROM product_hierarchy_one WHERE name = '$level1'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $level1 = $xres->row->id;







                            $qrys = "DELETE FROM product_category where product_code = ".$product_code."";

                            $runQ->RunQuery($qrys);



                            $qry = "INSERT INTO `product_category`(`product_code`, `level_one`, `level_two`, `level_three`, `level_four`, `level_five`, `level_six`, `acno`) VALUES ('".$product_code."','".$level1."','".$level2."','".$level3."','".$level4."','0','0','LHE-01262')";

                            echo $qry;

                            $runQ->RunQuery($qry);



                            }		*/

                            //---------------------------PRODUCT START----------------------------------

                            //check if product exist then generate code

                            $product_ref = $data[0];

                            $qry = "SELECT count(*) as cnt,product_code	FROM product WHERE product_ref = '$product_ref' AND acno = '$acno'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $count = $xres->row->cnt;

                            if ($count == 0) {

                                //generate product code

                                $qry = "select ifnull(max(product_code), cast(concat(substr('$acno',5,5),'0000') as unsigned))+1 as product_code

					from product

					where acno = '$acno'";

                                $res = $runQ->RunQuery($qry);

                                $xres = simplexml_load_string($res);

                                $product_code = $xres->row->product_code;

                            } else {

                                $product_code = $xres->row->product_code;

                            }

                            //$qry = "REPLACE INTO `product` (`acno`, `product_code`, `product_date`, `product_time`, `product_name`, `product_gender`, `product_collection`, `product_cost_price`, `product_price`, `product_ref`,`product_stat`, `product_sale_price`) VALUES ('$acno', $product_code, curdate(), date_format(now(),'%H%i'),'{$data[1]}','{$data[2]}','{$data[4]}','{$data[7]}','{$data[8]}','{$data[0]}','A','{$data[9]}')";

                            $qry = "REPLACE INTO `product` (`acno`, `product_code`, `product_date`, `product_time`, `product_ref`,`product_stat`,`product_sale`,`product_featured`,product_inv,	product_weight_type,product_weight,product_type,product_related) VALUES ('$acno', $product_code, curdate(), date_format(now(),'%H%i'),'{$data[0]}','A','N','N','yes','N','1','P','5')";

                            $runQ->RunQuery($qry);

                            //add product code in array

                            array_push($all_codes, $product_code);

                            //---------------------------PRODUCT END---------------------------





                            //---------------------------CATEGORY STRAT---------------------------

                            //get category code

                            $cat_name = $data[3];

                            $qry = "SELECT count(*) as cnt,cat_code	FROM category WHERE cat_name= '$cat_name' AND acno = '$acno'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $count = $xres->row->cnt;

                            $cat_code = '';

                            if ($count == 0) {

                                //create product category

                                $qry = "SELECT ifnull(max(cat_code),0)+1 as code FROM category where acno='$acno'";

                                $res = simplexml_load_string($runQ->RunQuery($qry));

                                $cat_code = $res->row->code;

                                $qry = "REPLACE INTO category(cat_code, cat_name,cat_desc, acno)

								VALUES('$cat_code', '$cat_name','', '$acno')";

                                $runQ->RunQuery($qry);

                            } else {

                                $cat_code = $xres->row->cat_code;

                            }

                            //get product_cat code

                            $qry = "SELECT count(*) as cnt	FROM product_cat WHERE product_code ='$product_code' and cat_code= '$cat_code' AND acno = '$acno'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $count = $xres->row->cnt;

                            if ($count == 0) {

                                $qry = "REPLACE INTO product_cat(acno, product_code, cat_code)VALUES('$acno', '$product_code', '$cat_code')";

                                $runQ->RunQuery($qry);

                            }

                            //---------------------------CATEGORY END-----------------------------





                            //---------------------------PRODUCT VARIATION AND VARIATION VAR LIST START-----------------------------

                            //create var of product

                            $qry = "SELECT count(*) as cnt

							FROM product_vari

							WHERE product_code = $product_code

							AND acno = '$acno'";

                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $count = $xres->row->cnt;

                            if ($count == 0) {

                                for ($i = 0; $i < 2; $i++) {

                                    if ($i == 0) {

                                        $vari_name = 'Size';

                                        $list_name = $data[5];

                                    }

                                    if ($i == 1) {

                                        $vari_name = 'Color';

                                        $list_name = $data[6];

                                    }

                                    if (!empty($list_name)) {

                                        $qry = "select ifnull(max(vari_code),0)+1 as vari_code	from product_vari where product_code = '$product_code' and acno = '$acno'";

                                        $xres = simplexml_load_string($runQ->RunQuery($qry));

                                        $vari_code = $xres->row->vari_code;

                                        $qry = "REPLACE into product_vari (acno, product_code, vari_code, vari_name)values ('$acno', '$product_code', '$vari_code', '$vari_name')";

                                        $runQ->RunQuery($qry);

                                    }

                                }

                            }

//---------------------------PRODUCT VARIATION END-----------------------------	





//---------------------------PRODUCT VARIATION VAR LIST START-----------------------------								

                            //create var list of product

                            for ($i = 0; $i < 2; $i++) {

                                if ($i == 0) {

                                    $vari_code = 1;

                                    $list_name = $data[5];

                                }

                                if ($i == 1) {

                                    $vari_code = 2;

                                    $list_name = $data[6];

                                }

                                if (!empty($list_name)) {

                                    $qry = "SELECT COUNT(*) AS cnt

									FROM product_vari_list

									WHERE vari_code = '$vari_code'

									AND list_name='$list_name'															

									AND product_code = '$product_code'

									AND acno = '$acno'";

                                    $xres = simplexml_load_string($runQ->RunQuery($qry));

                                    $count = $xres->row->cnt;

                                    if ($count == 0) {

                                        $qry = "select ifnull(max(list_code),0)+1 as list_code

													from product_vari_list

													where vari_code = $vari_code

													and product_code = '$product_code'

													and acno = '$acno'";

                                        $xres = simplexml_load_string($runQ->RunQuery($qry));

                                        $list_code = $xres->row->list_code;



                                        $qry = "REPLACE into product_vari_list (acno, product_code, vari_code, list_code, list_name)values ('$acno', '$product_code', '$vari_code', '$list_code', '$list_name')";

                                        $runQ->RunQuery($qry);

                                    }

                                }

                            }

                            //---------------------------PRODUCT VARIATION VAR LIST  END-----------------------------

                        }

                        fclose($handle);





                        //---------------------------PRODUCT SKU CODE  START-----------------------------

                        $uniqe_product_code = array_unique($all_codes);

                        //print_r($uniqe_product_code);

                        foreach ($uniqe_product_code as &$value) {



                            //create sku of product

                            $code = $value;



                            //first delete sku detail

                            $qry = "delete from sku_detail where product_code = $code and acno = '$acno'";

                            $runQ->RunQuery($qry);



                            $qry = "SELECT count(*) as cnt

										FROM sku_detail

										WHERE product_code = $code

										AND acno = '$acno'";



                            $xres = simplexml_load_string($runQ->RunQuery($qry));

                            $count = $xres->row->cnt;



                            if ($count == 0) {

                                $qry = "select count(*) as cnt

											from product_vari

											where acno = '$acno'

											and product_code = $code";

                                $xres = simplexml_load_string($runQ->RunQuery($qry));

                                $count = $xres->row->cnt;



                                if ($count >= 1) {

                                    $qry = "select vari_code from product_vari

												where acno = '$acno'

												and product_code = $code";



                                    $xres = simplexml_load_string($runQ->RunQuery($qry));

                                    //$vari_code = $xres->row->vari_code;

                                    $sku = " concat($code,";

                                    $df = " concat( ";

                                    $ln = " concat( ";

                                    $tb = "";

                                    $whr = "";

                                    $dm = "";

                                    $tdm = "";

                                    $wdm = "";

                                    foreach ($xres->row as $row) {

                                        $vari_code = $row->vari_code;

                                        $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";

                                        $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                                        $ln .= $dm . " A$vari_code.list_name ";

                                        $tb .= $tdm . " product_vari_list as A$vari_code ";

                                        $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $code and A$vari_code.vari_code = $vari_code ";

                                        $dm = ", ',' , ";

                                        $tdm = ",";

                                        $wdm = "and";

                                    }

                                    $sku .= " ) as sku ";

                                    $df .= " ) as def ";

                                    $ln .= " ) as des";

                                    $qry = "insert into sku_detail

												select $sku, $code, '$acno', $df, $ln, null, null, 0 from $tb where $whr order by 1, 2";

                                    $runQ->RunQuery($qry);

                                    $qry;

                                } else {

                                    $qry = "REPLACE into sku_detail (sku_code, product_code, acno, sku_def, sku_desc, sku_weight, sku_price, sku_qty)

												values(concat($code,'00'), $code, '$acno', 'None', 'None', null, null, 0)";

                                    $runQ->RunQuery($qry);

                                }

                            }



                            //create none sku of product

                            $qry = "REPLACE into sku_detail (sku_code, product_code, acno, sku_def, sku_desc, sku_weight, sku_price, sku_qty)

												values(concat($code,'00'), $code, '$acno', 'None', 'None', null, null, 0)";

                            $runQ->RunQuery($qry);



                            //generate product code

                            $qry = "select ifnull(max(product_code), cast(concat(substr('$acno',5,5),'0000') as unsigned))+1 as product_code

									from product

									where acno = '$acno'";

                            $res = $runQ->RunQuery($qry);

                            $xres = simplexml_load_string($res);

                            $product_code = $xres->row->product_code;



                            // delete extra product

                            $qry = "delete from product_cat where product_code = $product_code and acno = '$acno'";

                            $runQ->RunQuery($qry);

                            $qry = "delete from product_vari where product_code = $product_code and acno = '$acno'";

                            $runQ->RunQuery($qry);

                            $qry = "delete from product_vari_list where product_code = $product_code and acno = '$acno'";

                            $runQ->RunQuery($qry);

                            $qry = "delete from sku_detail where product_code = $product_code and acno = '$acno'";

                            $runQ->RunQuery($qry);



                        }

                        //---------------------------PRODUCT SKU CODE  END-----------------------------

                        echo 'File imported succussfully !';

                    } else {

                        echo 'File not importing !';

                    }



                } else {

                    echo 'Error in file uploading';

                }

            }

        }



        return;

        break;



//----------------------------------------- dashboardv2 coding ----------------------------------------------------//

    case 'enable_inv':

        $prod_code = $_REQUEST['prod_code'];

        $qry1 = "select count(vari_code) as vari_code from product_vari where product_code=$prod_code and acno='$acno'";

        $q = $runQ->RunQuery($qry1);

        $v = simplexml_load_string($q);

        $sc = $v->row->vari_code;



        $qry = "update product set product_inv='yes' where product_code=$prod_code and acno='$acno'";

        $q = $runQ->RunQuery($qry);



        if ($sc == 0) {

            $qry = "update sku_detail set sku_qty=100000 where product_code=$prod_code and acno='$acno' and sku_desc='none'";

            $q = $runQ->RunQuery($qry);

            echo 1;

        } else if ($sc >= 1) {

            $qry2 = "update sku_detail set sku_qty=0 where product_code=$prod_code and acno='$acno'";

            $runQ->RunQuery($qry2);



            sleep(0.5);



            $vari = "select vari_code, vari_name

						from product_vari

						where product_code=$prod_code and acno='$acno' group by vari_code";

            //echo $vari;

            $vari_res = $runQ->RunQuery($vari);

            $vari_xres = simplexml_load_string($vari_res);





            $qry = "";

            $qry = "select * from sku_detail where product_code=$prod_code and sku_desc!='none'";

            $sku = simplexml_load_string($runQ->RunQuery($qry));

            $sku_code = $sku->row->sku_code;

            $sku_def = $sku->row->sku_def;

            $sku_desc = $sku->row->sku_desc;

            $sku_qty = $sku->row->sku_qty;





//				$tab=' <div id="inventory_show">';

            $tab .= '<table class="table table-bordered table-condensed table-hover">';



            $tab .= '<thead><tr><th>SKU Code</th>';

            $tab .= '<th>SKU Description</th>';

            $tab .= '<th style="text-align:center;">Available Inventory</th>';

            $tab .= ' </tr></thead><tbody id="vari_tbody">';

            foreach ($sku->row as $rows) {

                $tab .= '<tr>';

                $tab .= '<td>' . $rows->sku_code . '</td>';

                $tab .= '<td>' . $rows->sku_desc . '</td>';

                $tab .= '<td style="text-align:center;"><a href="javascript:void(0)" onclick="addinv(this)">' . $rows->sku_qty . '</a></td>';

                $tab .= '</tr>';

            }

            $tab .= '</tbody></table>';

            echo $tab;

        }





        return;

        break;



    case 'disable_inv':

        $prod_code = $_REQUEST['prod_code'];



        $qry1 = "select count(vari_code) as vari_code from product_vari where product_code=$prod_code and acno='$acno'";

        $q = $runQ->RunQuery($qry1);

        $v = simplexml_load_string($q);

        $sc = $v->row->vari_code;



        if ($sc == 0) {

            $qry2 = "update sku_detail set sku_qty=1000000 where product_code=$prod_code and acno='$acno' and sku_desc='none'";

            $runQ->RunQuery($qry2);

        } else if ($sc >= 1) {

            $qry2 = "update sku_detail set sku_qty=1000000 where product_code=$prod_code and acno='$acno'";

            $runQ->RunQuery($qry2);

        }



        $qry = "update product set product_inv='' where product_code=$prod_code and acno='$acno'";

        $q = $runQ->RunQuery($qry);

        if ($q) {

            echo 1;

        }



        return;

        break;



    case 'check_sku':

        $prod_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $qry = "select vari_name, vari_code from product_vari where product_code=$prod_code and acno='$acno' and vari_code=$vari_code";

        $q = $runQ->RunQuery($qry);

        $v = simplexml_load_string($q);

        $vc = $v->row->vari_code;



        if ($vc != '') {

            echo 2;

        } else if ($vc == '') {

            echo 1;

        }



        return;

        break;





    case 'save_vari_all_new':

        $prod_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $vari_name = $_REQUEST['vari_name'];

        $inv_enabled = $_REQUEST['inv_enabled'];

        $data_arr = $_REQUEST['data_arr'];
        
        $vari_loc_code = $_REQUEST['vari_loc_code'];

         echo $data_arr;

        //$data_qty='';

        if (isset($_REQUEST['data_qty'])) {

            $data_qty = json_decode($_REQUEST['data_qty'], true);

        }


        //get city id from location code 
        
        $qry_city_id = "select city_id,loc_code from location where loc_active_status='A'";

        $q_city_id = $runQ->RunQuery($qry_city_id);

        $v_city_id = simplexml_load_string($q_city_id);

        $vari_city_id = (int)$v_city_id->row->city_id;

        ////////////////////////////////

        echo $qry = "select count(sku_code) as sku_code from sku_detail where product_code=$prod_code and acno='$acno'";

        $q = $runQ->RunQuery($qry);

        $v = simplexml_load_string($q);

        $sku = (int)$v->row->sku_code;



        $chk_vari1 = "select count(vari_code) as 'vari_code', acno from product_vari where product_code=$prod_code and acno='$acno'";

        $cv1 = $runQ->RunQuery($chk_vari1);

        $nov = simplexml_load_string($cv1);

        $vcodes = (int)$nov->row->vari_code;





        $chek_vari = "SELECT vari_code, vari_name from product_vari where vari_code=$vari_code and product_code=$prod_code and acno='$acno'";

        $vari = $runQ->RunQuery($chek_vari);

        $v = simplexml_load_string($vari);

        $vc = $v->row->vari_code;

        //echo 'vari code='.$vc.' /end';

        if ($vc == '') {
            $qry1 = "INSERT INTO product_vari (vari_code,vari_name,product_code,acno)

							VALUES($vari_code,'$vari_name',$prod_code,'$acno')";

            //echo $qry1;

            $runQ->RunQuery($qry1);

        } else if ($vc != '') {
            $qry1 = "UPDATE product_vari SET vari_name='$vari_name' where vari_code=$vari_code and product_code=$prod_code and acno='$acno'";

            $runQ->RunQuery($qry1);

        }



        $cleanData = json_decode($data_arr, true);



        foreach ($cleanData as $key => $value) {

            $list_code = $key;

            $list_name = $value;



            $chek_list = "SELECT list_code, list_name from product_vari_list where list_code=$list_code and vari_code=$vc and product_code=$prod_code and acno='$acno'";

            $list = $runQ->RunQuery($chek_list);

            $l = simplexml_load_string($list);

            $lc = $l->row->list_code;

            if ($lc == '') {
                $qry2 = "INSERT INTO product_vari_list(vari_code,list_code,list_name,product_code,acno)

								VALUES($vari_code,$list_code,'$list_name',$prod_code,'$acno')";

                $runQ->RunQuery($qry2);

            } else if ($lc != '') {

                $qry2 = "Update product_vari_list SET list_name='$list_name'

								where list_code=$key and vari_code=$vari_code and product_code=$prod_code and acno='$acno'";

                $runQ->RunQuery($qry2);

            }

        }

        sleep(0.5);

        $chk_vari2 = "select count(vari_code) as vari_code, acno from product_vari where product_code=$prod_code and acno='$acno'";

        $cv2 = $runQ->RunQuery($chk_vari2);

        $nov2 = simplexml_load_string($cv2);

        $vcodes2 = (int)$nov2->row->vari_code;


        if (($sku == 1 || $sku > 1) && $inv_enabled == '') {
            // echo 'here';exit;
            $qr = "delete from sku_detail where product_code=$prod_code and acno='$acno' and sku_def!='none'";

            $runQ->RunQuery($qr);



            $qry = "SELECT count(*) as cnt

					FROM sku_detail

					WHERE product_code = $prod_code

					AND acno = '$acno'";



            $xres = simplexml_load_string($runQ->RunQuery($qry));

            $count = $xres->row->cnt;



            if ($count == 1) {

                $qry = "select count(*) as cnt

							from product_vari

							where acno = '$acno'

							and product_code = $prod_code";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $count = $xres->row->cnt;



                if ($count >= 1) {

                    $qry = "select distinct(vari_code)

								from product_vari_list

								where acno = '$acno'

								and product_code = $prod_code";



                    $xres = simplexml_load_string($runQ->RunQuery($qry));

                    //$vari_code = $xres->row->vari_code;

                    $sku = " concat($prod_code,";

                    $df = " concat( ";

                    $ln = " concat( ";

                    $tb = "";

                    $whr = "";

                    $dm = "";

                    $tdm = "";

                    $wdm = "";

                    foreach ($xres->row as $row) {

                        $vari_code = $row->vari_code;

                        $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";

                        $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                        $ln .= $dm . " A$vari_code.list_name ";

                        $tb .= $tdm . " product_vari_list as A$vari_code ";

                        $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $prod_code and A$vari_code.vari_code = $vari_code ";

                        $dm = ", ',' , ";

                        $tdm = ",";

                        $wdm = "and";

                    }

                    $sku .= " ) as sku ";

                    $df .= " ) as def ";

                    $ln .= " ) as des";

                    $qry = "insert into sku_detail

								select $sku, $prod_code, '$acno', $df, $ln, null, null, 100000 from $tb where $whr order by 1, 2";

                    $runQ->RunQuery($qry);

                }

            }

            echo 1;

        } else if ($sku == 1 && $inv_enabled == 'yes') {

            $qry = "SELECT count(*) as cnt

							FROM sku_detail

							WHERE product_code = $prod_code

							AND acno = '$acno'";



            $xres = simplexml_load_string($runQ->RunQuery($qry));

            $count = $xres->row->cnt;



            if ($count == 1) {

                $qry = "select count(*) as cnt

									from product_vari

									where acno = '$acno'

									and product_code = $prod_code";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $count = $xres->row->cnt;



                if ($count >= 1) {

                    $qry = "select distinct(vari_code)

										from product_vari_list

										where acno = '$acno'

										and product_code = $prod_code";



                    $xres = simplexml_load_string($runQ->RunQuery($qry));

                    //$vari_code = $xres->row->vari_code;

                    // $sku = " concat($prod_code,";

                    $df = " concat( ";

                    $ln = " concat( ";

                    $tb = "";

                    $whr = "";

                    $dm = "";

                    $tdm = "";

                    $wdm = "";

                    foreach ($xres->row as $row) {

                        $vari_code = $row->vari_code;

                        // $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";

                        $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                        $ln .= $dm . " A$vari_code.list_name ";

                        $tb .= $tdm . " product_vari_list as A$vari_code ";

                        $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $prod_code and A$vari_code.vari_code = $vari_code ";

                        $dm = ", ',' , ";

                        $tdm = ",";

                        $wdm = "and";

                    }

                    // $sku .= " ) as sku ";

                    $df .= " ) as def ";

                    $ln .= " ) as des";
                    
                    
                    //--------- inserting product in all locations ----------//
                    foreach ($v_city_id->row as $row) {

                        // $vari_code = $row->vari_code;
                        $vari_city_id = (int)$row->city_id;
                        $vari_sku_loc_code = (int)$row->loc_code;
                        $sku = " concat($prod_code,";
                        $sku .= "" . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";
                        $sku .= ",'".$vari_city_id."','".$vari_sku_loc_code."' ) as sku ";


                        $qry = "insert into sku_detail
    
    										select $sku, $prod_code,$vari_city_id, '$acno', $df, $ln, null, null, 0,'".date('Y-m-d H:i:s')."',$vari_sku_loc_code from $tb where $whr order by 1, 2";
                        // echo $qry.'</br>';
                      
                        $runQ->RunQuery($qry);

                    }
                    //------------------------------------------------------//
                }

            }

            $qry = "SELECT sku_detail.sku_code, sku_def, sku_desc, ifnull(sku_weight,0) as sku_weight,

								ifnull(sku_price,0) as sku_price, ifnull(sku_qty,0) as quantity

								FROM sku_detail

								WHERE sku_detail.product_code = $prod_code

								AND acno = '$acno'

								group by sku_detail.sku_code, sku_def, sku_desc";



            $runQ->RunQuery($qry);



            //---------------- getting back response /////////////----------------------------/////////

            sleep(1);

            $vari = "select vari_code, vari_name

									from product_vari

									where product_code=$prod_code and acno='$acno' group by vari_code";

            //echo $vari;

            $vari_res = $runQ->RunQuery($vari);

            $vari_xres = simplexml_load_string($vari_res);





            $qry = "";

            $qry = "select * from sku_detail where product_code=$prod_code and sku_desc!='none'";

            $sku = simplexml_load_string($runQ->RunQuery($qry));

            $sku_code = $sku->row->sku_code;

            $sku_def = $sku->row->sku_def;

            $sku_desc = $sku->row->sku_desc;

            $sku_qty = $sku->row->sku_qty;





            $tab = '<div id="inventory_show">';

            $tab .= '<table class="table table-bordered table-condensed table-hover">';

            $tab .= '<thead><tr><th>SKU Code</th>';

            $tab .= '<th>SKU Description</th>';

            $tab .= '<th style="text-align:center;">Available Inventory</th>';

            $tab .= '<th>City</th>';
            
            $tab .= '<th>Store</th>';
            
            $tab .= ' </tr></thead><tbody id="vari_tbody">';

            foreach ($sku->row as $rows) {

                $tab .= '<tr>';

                $tab .= '<td>' . $rows->sku_code . '</td>';

                $tab .= '<td>' . $rows->sku_desc . '</td>';

                $tab .= '<td style="text-align:center;"><a href="javascript:void(0)" onclick="addinv(this)">' . $rows->sku_qty . '</a></td>';
                
                $qry_city_name = "select loc_city,loc_name from location where city_id=".$rows->city_id." AND loc_code = ".$rows->sku_loc_code;
    
                $sku_city_name = simplexml_load_string($runQ->RunQuery($qry_city_name));
                
                $tab .= '<td>' . $sku_city_name->row->loc_city . '</td>';
                
                $tab .= '<td>' . $sku_city_name->row->loc_name . '</td>';

                $tab .= '</tr>';

            }

            $tab .= '</tbody></table></div>';

            echo $tab;



        } else if ($sku > 1 && $inv_enabled == 'yes') {


            // $qry_city_in_sku_detail="select * from sku_detail where sku_loc_code IS NOT NULL and sku_desc !='none'";
            
            // $sku_city_in_sku_detail = simplexml_load_string($runQ->RunQuery($qry_city_in_sku_detail));
            
            // $previous_variations = array();
            
            // foreach ($sku_city_in_sku_detail->row as $row) {
            //     $result = array(
            //     "sku_code" => $row->sku_code ,
            //     "product_code" => $row->product_code ,
            //     "city_id" => $row->city_id,
            //     "acno" => $row->acno,
            //     "sku_def" => $row->sku_def,
            //     "sku_desc" => $row->sku_desc,
            //     "sku_weight" => $row->sku_weight,
            //     "sku_price" => $row->sku_price,
            //     "sku_qty" => $row->sku_qty,
            //     "updated_timestamp" => $row->updated_timestamp,
            //     "sku_loc_code" => $row->sku_loc_code
            //     );
            //     array_push($previous_variations , $result);
            // }
            
            // // echo json_encode($previous_variations);
            // foreach($previous_variations as $key => $value){
            //     $sku_loc_code = $value['sku_loc_code'];
            //     $sku_product_code = $value['product_code'];
            //     if($sku_loc_code == $vari_loc_code && $prod_code == $sku_product_code){
            //         echo 'matched';
            //     }
            // }
            

            $qr = "delete from sku_detail where product_code=$prod_code and acno='$acno' and sku_def!='none'";

            $runQ->RunQuery($qr);

            sleep(0.5);

            $qry = "SELECT count(*) as cnt

							FROM sku_detail

							WHERE product_code = $prod_code

							AND acno = '$acno'";



            $xres = simplexml_load_string($runQ->RunQuery($qry));

            $count = $xres->row->cnt;



            if ($count == 1) {

                $qry = "select count(*) as cnt

									from product_vari

									where acno = '$acno'

									and product_code = $prod_code";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $count = $xres->row->cnt;



                if ($count >= 1) {

                    $qry = "select distinct(vari_code)

										from product_vari_list

										where acno = '$acno'

										and product_code = $prod_code";



                    $xres = simplexml_load_string($runQ->RunQuery($qry));

                    //$vari_code = $xres->row->vari_code;

                    $sku = " concat($prod_code,";

                    $df = " concat( ";

                    $ln = " concat( ";

                    $tb = "";

                    $whr = "";

                    $dm = "";

                    $tdm = "";

                    $wdm = "";

                    $cccity_id = "";
                    
                    $locccc_id = "";

                    foreach ($xres->row as $row) {
                        
                      foreach ($v_city_id->row as $rows) {

                        // $vari_code = $row->vari_code;
                        $vari_city_id = (int)$rows->city_id;
                        $vari_sku_loc_code = (int)$rows->loc_code;
                          
                        $vari_code = $row->vari_code;
                        
                        echo $vari_code.'   aa   ';

                        $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code,$vari_city_id,$vari_sku_loc_code ";
                        
                        $cccity_id .= $tdm . " $vari_city_id";
                        
                        $locccc_id .= $tdm . " concat($vari_sku_loc_code), ";

                        $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                        $ln .= $dm . " A$vari_code.list_name ";

                        $tb .= $tdm . " product_vari_list as A$vari_code ";

                        $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $prod_code and A$vari_code.vari_code = $vari_code ";

                        $dm = ", ',' , ";

                        $tdm = ",";

                        $wdm = "and";
                        
                        
                        
                        }
                        
                    }

                    $sku .= " ) as sku ";

                    $df .= " ) as def ";

                    $ln .= " ) as des";

                    //--------- inserting product in all locations ----------//
                    // foreach ($v_city_id->row as $row) {

                    //     // $vari_code = $row->vari_code;
                    //     $vari_city_id = (int)$row->city_id;
                    //     $vari_sku_loc_code = (int)$row->loc_code;
                    //     $sku = " concat($prod_code,";
                    //     $sku .= "" . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";
                    //     $sku .= ",'".$vari_city_id."','".$vari_sku_loc_code."' ) as sku ";


                        $qry = "insert into sku_detail
    
    										select $sku, $prod_code,$cccity_id, '$acno', $df, $ln, null, null, 0,'".date('Y-m-d H:i:s')."',$locccc_id from $tb where $whr order by 1, 2";
                        echo $qry.'</br>';
                      
                        $runQ->RunQuery($qry);

                    // }
                    //------------------------------------------------------//

                }

            }



            //---------------- getting back response /////////////----------------------------/////////

            sleep(0.5);

            if ($data_qty != '') {

                foreach ($data_qty as $key => $value) {

                    $qrr = "update sku_detail set sku_qty=$value where product_code=$prod_code and acno='$acno' and sku_code=$key";

                    $runQ->RunQuery($qrr);

                }

            }

            sleep(0.5);



            $vari = "select vari_code, vari_name

									from product_vari

									where product_code=$prod_code and acno='$acno' group by vari_code";

            //echo $vari;

            $vari_res = $runQ->RunQuery($vari);

            $vari_xres = simplexml_load_string($vari_res);





            $qry = "";

            $qry = "select * from sku_detail where product_code=$prod_code and sku_desc!='none'";

            $sku = simplexml_load_string($runQ->RunQuery($qry));

            $sku_code = $sku->row->sku_code;

            $sku_def = $sku->row->sku_def;

            $sku_desc = $sku->row->sku_desc;

            $sku_qty = $sku->row->sku_qty;





            $tab = '<div id="inventory_show">';

            $tab .= '<table class="table table-bordered table-condensed table-hover">';

            $tab .= '<thead><tr><th>SKU Code</th>';

            $tab .= '<th>SKU Description</th>';

            $tab .= '<th style="text-align:center;">Available Inventory</th>';
            
            $tab .= '<th>City</th>';
            
            $tab .= '<th>Store</th>';

            $tab .= ' </tr></thead><tbody id="vari_tbody">';

            foreach ($sku->row as $rows) {

                $tab .= '<tr>';

                $tab .= '<td>' . $rows->sku_code . '</td>';

                $tab .= '<td>' . $rows->sku_desc . '</td>';

                $tab .= '<td style="text-align:center;"><a href="javascript:void(0)" onclick="addinv(this)">' . $rows->sku_qty . '</a></td>';
                
                $qry_city_name = "select loc_city,loc_name from location where city_id=".$rows->city_id." AND loc_code = ".$rows->sku_loc_code;
    
                $sku_city_name = simplexml_load_string($runQ->RunQuery($qry_city_name));
                
                $tab .= '<td>' . $sku_city_name->row->loc_city . '</td>';
                
                $tab .= '<td>' . $sku_city_name->row->loc_name . '</td>';

                $tab .= '</tr>';

            }

            $tab .= '</tbody></table></div>';

            echo $tab;



        }

        return;

        break;



    case 'update_sku':

        $sku_code = $_REQUEST['sku_code'];

        $product_code = $_REQUEST['product_code'];

        $qty = $_REQUEST['value'];



        $qry = "UPDATE sku_detail

						SET sku_qty=$qty

						WHERE sku_code = $sku_code and product_code=$product_code and acno='$acno'";

        $q = $runQ->RunQuery($qry);

        if ($q) {

            echo $qty;

        }



        return;

        break;



    case 'chek_vari_code':

        //$vari_code=$_REQUEST['vari_text'];

        $prod_code = $_REQUEST['prod_code'];

        //$acno=$_REQUEST['acno'];

        $qry = "SELECT count(vari_code) as vari_code from product_vari WHERE product_code=$prod_code AND acno='$acno'";

        $res = $runQ->RunQuery($qry);

        $xres = simplexml_load_string($res);

        $vari_code = $xres->row->vari_code;

        echo $vari_code;



        return;

        break;





    case 'chek_list_code':

        $vari_code = $_REQUEST['vari_id'];

        $prod_code = $_REQUEST['prod_code'];

        $acno = $_REQUEST['acno'];

        $qry = "SELECT max(list_code) as list_code from product_vari_list WHERE vari_code=$vari_code and product_code=$prod_code AND acno=$acno";

        $res = $runQ->RunQuery($qry);

        $xres = simplexml_load_string($res);

        $list_code = $xres->row->list_code;

        echo $list_code;



        return;

        break;





    case 'save_edit_vari':

        $prod_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $vari_name = $_REQUEST['vari_name'];

        $data_arr = $_REQUEST['data_arr'];



        $cleanData = json_decode($data_arr, true);

        $qry1 = "Update product_vari SET vari_name='$vari_name'

							where vari_code=$vari_code 

							and product_code=$prod_code and acno='$acno'";

        //echo $qry1;

        $runQ->RunQuery($qry1);

        foreach ($cleanData as $key => $value) {

            $list_code = $key;

            $list_name = $value;



            $qry2 = "Update product_vari_list SET list_name='$list_name'

								where list_code=$key and vari_code=$vari_code and product_code=$prod_code and acno='$acno'";

            $runQ->RunQuery($qry2);



        }

        echo 1;

        return;

        break;



    case 'remove_vari_all_new':

        $prod_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $inv_enable = $_REQUEST['inv_enable'];



        $data_arr = $_REQUEST['data_arr'];



        $cleanData = json_decode($data_arr, true);



        $qryv = "DELETE FROM product_vari where vari_code=$vari_code AND product_code=$prod_code AND acno='$acno'";

        $runQ->RunQuery($qryv);



        foreach ($cleanData as $key => $value) {

            $list_code = $key;

            $list_name = $value;



            $chek_list = "SELECT list_code, list_name from product_vari_list where list_code=$list_code and product_code=$prod_code and acno='$acno'";

            $list = $runQ->RunQuery($chek_list);

            $l = simplexml_load_string($list);

            $lc = $l->row->list_code;

            if ($lc != '') {

                $qry2 = "DELETE FROM product_vari_list

								where list_code=$key and vari_code=$vari_code and product_code=$prod_code and acno='$acno'";

                //echo $qry2.'<br>';

                $runQ->RunQuery($qry2);

            }

        }



        sleep(0.5);

        $qry1 = "select count(vari_code) as vari_code from product_vari where product_code=$prod_code and acno='$acno'";

        $q = $runQ->RunQuery($qry1);

        $v = simplexml_load_string($q);

        $vc = $v->row->vari_code;



        if ($vc == 0) {

            $qr = "delete from sku_detail where product_code=$prod_code and acno='$acno' and sku_def!='none'";

            $runQ->RunQuery($qr);



            $qr = "update sku_detail set sku_qty=100000 where product_code=$prod_code and acno='$acno' and sku_def='none'";

            $runQ->RunQuery($qr);



            echo 1;

        } else if ($vc >= 1) {



            $qr = "delete from sku_detail where product_code=$prod_code and acno='$acno' and sku_def!='none'";

            $runQ->RunQuery($qr);

            sleep(0.5);



            //--------------------sku -----///////

            $qry = "SELECT count(*) as cnt

								FROM sku_detail

								WHERE product_code = $prod_code

								AND acno = '$acno'";



            $xres = simplexml_load_string($runQ->RunQuery($qry));

            $count = $xres->row->cnt;



            if ($count == 1) {

                $qry = "select count(*) as cnt

									from product_vari

									where acno = '$acno'

									and product_code = $prod_code";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $count = $xres->row->cnt;



                if ($count >= 1) {

                    $qry = "select distinct(vari_code)

									from product_vari_list

									where acno = '$acno'

									and product_code = $prod_code";



                    $xres = simplexml_load_string($runQ->RunQuery($qry));

                    //$vari_code = $xres->row->vari_code;

                    $sku = " concat($prod_code,";

                    $df = " concat( ";

                    $ln = " concat( ";

                    $tb = "";

                    $whr = "";

                    $dm = "";

                    $tdm = "";

                    $wdm = "";

                    foreach ($xres->row as $row) {

                        $vari_code = $row->vari_code;

                        $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";

                        $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                        $ln .= $dm . " A$vari_code.list_name ";

                        $tb .= $tdm . " product_vari_list as A$vari_code ";

                        $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $prod_code and A$vari_code.vari_code = $vari_code ";

                        $dm = ", ',' , ";

                        $tdm = ",";

                        $wdm = "and";

                    }

                    $sku .= " ) as sku ";

                    $df .= " ) as def ";

                    $ln .= " ) as des";

                    $qry = "insert into sku_detail

									select $sku, $prod_code, '$acno', $df, $ln, null, null, 0 from $tb where $whr order by 1, 2";

                    $runQ->RunQuery($qry);

                }

            }



            //---------------- getting back response /////////////----------------------------/////////

            sleep(0.5);

            if ($inv_enable == '') {

                $qry = "update set sku_qty=100000 where product_code=$prod_code and acno='$acno'";

                $runQ->RunQuery($qry);

            }

            sleep(0.5);

            $vari = "select vari_code, vari_name

									from product_vari

									where product_code=$prod_code and acno='$acno' group by vari_code";

            //echo $vari;

            $vari_res = $runQ->RunQuery($vari);

            $vari_xres = simplexml_load_string($vari_res);





            $qry = "";

            $qry = "select * from sku_detail where product_code=$prod_code and sku_desc!='none' and acno='$acno'";

            $sku = simplexml_load_string($runQ->RunQuery($qry));

            $sku_code = $sku->row->sku_code;

            $sku_def = $sku->row->sku_def;

            $sku_desc = $sku->row->sku_desc;

            $sku_qty = $sku->row->sku_qty;





            $tab = ' <div id="inventory_show">';

            $tab .= '<table class="table table-bordered table-condensed table-hover">';



            $tab .= '<thead><tr><th>SKU Code</th>';

            $tab .= '<th>SKU Description</th>';

            $tab .= '<th style="text-align:center;">Available Inventory</th>';

            $tab .= ' </tr></thead><tbody id="vari_tbody">';

            foreach ($sku->row as $rows) {

                $tab .= '<tr>';

                $tab .= '<td>' . $rows->sku_code . '</td>';

                $tab .= '<td>' . $rows->sku_desc . '</td>';

                $tab .= '<td style="text-align:center;"><a href="javascript:void(0)" onclick="addinv(this)">' . $rows->sku_qty . '</a></td>';

                $tab .= '</tr>';

            }

            $tab .= '</tbody></table></div>';



            echo $tab;

        }

        return;

        break;





    case 'remove_vari_list_new':



        $prod_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $list_code = $_REQUEST['list_code'];

        $inv_enabled = $_REQUEST['inv_enabled'];

        $data_qty = '';

        if (isset($_REQUEST['data_qty'])) {

            $data_qty = json_decode($_REQUEST['data_qty'], true);

        }





        $chek_list = "SELECT list_code, list_name from product_vari_list where vari_code=$vari_code and list_code=$list_code and product_code=$prod_code and acno='$acno'";

        $list = $runQ->RunQuery($chek_list);

        $l = simplexml_load_string($list);

        $lc = $l->row->list_code;

        if ($lc != '') {

            $qry2 = "DELETE FROM product_vari_list

								where list_code=$lc and vari_code=$vari_code and product_code=$prod_code and acno='$acno'";

            //echo $qry2.'<br>';

            $runQ->RunQuery($qry2);

        }

        echo 1;

        return;

        break;





//-------------------------------------- end of dashboardv2 coding -------------------------------------------------//



    case 'addproduct' :

        $qry = "select ifnull(max(product_code),

									cast(concat(substr('$acno',5,5),'0000') as unsigned))+1 as product_code, 

									date_format(curdate(),'%d/%m/%Y') as product_date, 

									date_format(now(),'%H%i') as product_time 

									from product

									where acno = '$acno'";

        $res = $runQ->RunQuery($qry);

        $xres = simplexml_load_string($res);

        $product_code = $xres->row->product_code;

        $product_date = $xres->row->product_date;

        $product_time = $xres->row->product_time;



        $qry = "insert into product (product_code, product_date, product_time, acno)

									values($product_code, curdate(), date_format(now(),'%H%i'), '$acno')";

        //careating a dummy 'none' sku of newly added product and assigning it 100000 quantity

        $sku_pro = $product_code . '00';

        $sku_insrt = "insert into sku_detail (sku_code, product_code, acno, sku_def, sku_desc, sku_qty) values($sku_pro, $product_code, '$acno', 'none', 'none', 100000)";

        $runQ->RunQuery($sku_insrt);



        if ($runQ->RunQuery($qry)) {

            echo $res;

        } else {

            echo 0;

        }



        return;

        break;



    case 'saveproduct' :



        $product_code = $_REQUEST['product_code'];

        $product_name = htmlspecialchars($_REQUEST['product_name'], ENT_QUOTES);

        $product_featured_text = htmlspecialchars($_REQUEST['product_featured_text'], ENT_QUOTES);

        if ($product_featured_text == '') {

            $product_featured_text = 0;

        }

        $product_desc = str_replace("'", '"', $_REQUEST['product_desc']);

        $product_nut_info = str_replace("'", '"', $_REQUEST['product_nut_info']);

        $product_ing = str_replace("'", '"', $_REQUEST['product_ing']);

        //$product_tag = str_replace("'", "'", $_REQUEST['product_tag']);

        $product_cost_price = $_REQUEST['product_cost'];

        $product_price = $_REQUEST['product_price'];

        $product_weight = $_REQUEST['product_weight'];

        $product_weight_type = $_REQUEST['product_weight_type'];

        $product_shipping_weight = $_REQUEST['product_shipping_weight'];

        $product_min = $_REQUEST['product_min'];

        $product_max = $_REQUEST['product_max'];

        $product_customer_limit = $_REQUEST['product_customer_limit'];

        $product_order_limit = $_REQUEST['product_order_limit'];

        $fresh_status = $_REQUEST['fresh_status'];

        /*$level_one = $_REQUEST['level_one'];

        $level_two = $_REQUEST['level_two'];

        $level_three = $_REQUEST['level_three'];

        $level_four = $_REQUEST['level_four'];

        $level_five = $_REQUEST['level_five'];

        $level_six = $_REQUEST['level_six'];*/

        $product_ref = str_replace("'", "'", $_REQUEST['product_ref']);

        $product_sale_price = $_REQUEST['product_sale_price'];

        $product_related = $_REQUEST['product_related'];

        $url_slug = $_REQUEST['url_slug'];

        $page_title = $_REQUEST['page_title'];

        $page_meta = $_REQUEST['page_meta'];

        $meta_desc = $_REQUEST['meta_desc'];

        //$cat = $_REQUEST['cat'];

        $product_alter = $_REQUEST['product_alter'];

        if ($product_alter == '') {

            $product_alter = 'N';

        }

        $product_stat = $_REQUEST['product_stat'];

        if ($product_stat == '') {

            $product_stat = 'I';

        }

        $product_featured = $_REQUEST['product_featured'];

        if ($product_featured == '') {

            $product_featured = 'N';

        }

        $product_sale = $_REQUEST['product_sale'];

        if ($product_sale == '') {

            $product_sale = 'N';

        }

        $seasonal_product = $_REQUEST['seasonal_product'];

        if ($seasonal_product == '') {

            $seasonal_product = 'I';

        }

        $specific_brand = $_REQUEST['specific_brand'];

        if ($specific_brand == '') {

            $specific_brand = 'I';

        }

        $product_scoring = $_REQUEST['product_scoring'];

        

        $image_rondom_no = $_REQUEST['image_rondom_no'];

        

        $show_vendor_url = $_REQUEST['show_vendor_url'];

        

         $qry = "SELECT COUNT(*) AS cnt FROM product WHERE product_ref='$product_ref' and deleted != 'Y'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $ref_count = $xres->row->cnt;

        

        if($ref_count > 1){

         echo 'exist';

         exit();

        }

        



         $qry = "update product

								set product_name = '$product_name',

								product_desc = '$product_desc', 

								product_nut_info = '$product_nut_info', 

								product_ing = '$product_ing',

								product_cost_price = 0,

								product_price = 0,

								product_weight = $product_weight,

								product_weight_type = '$product_weight_type',

								product_shipping_weight = '$product_shipping_weight',

								product_min = '$product_min',

								product_max = '$product_max',

								product_customer_limit = '$product_customer_limit',

								product_order_limit = 0,

								product_ref = '$product_ref',

								product_alter = '$product_alter',

								product_stat = 'A',

								product_featured = '$product_featured',

								product_sale = 'N',

								product_sale_price = 0,

								product_related = 1,

								url_slug = '$url_slug', 

								page_title = '$page_title', 

								page_meta = '$page_meta', 

								meta_desc = '$meta_desc',

								product_featured_text = '',

								seasonal_product = '0',

								specific_brand = '0',

								product_scoring = '0',

								fresh_status = '$fresh_status',

								image_rondom_no = '$image_rondom_no',

								show_vendor_url = '$show_vendor_url'

								where product_code = $product_code";



        if ($runQ->RunQuery($qry)) {

            

                    $qrynews = "update product_sub_detail

                    set product_ref = '$product_ref' where product_code = $product_code";

                    $runQ->RunQuery($qrynews);

								

            /*$qry = "insert into product_status_log (product_code,old_product_price,new_product_price,

old_product_sale,new_product_sale,old_product_sale_price,new_product_sale_price,

old_product_stat,new_product_stat,usrid,datetime)

values ('$product_code','{$_REQUEST['product_price_old']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_old']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['product_sale_price_old']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_stat_old']}','{$product_stat}','$usrid',NOW())";

            $runQ->RunQuery($qry);*/



            /*$qry = "delete from product_cat where product_code = $product_code and acno = '$acno'";

            $runQ->RunQuery($qry);

            $crow = explode(',',$cat);

            foreach($crow  as $row){

                $qry = "insert into product_cat (acno, product_code, cat_code)

                        values ('$acno', $product_code, $row)";

                $runQ->RunQuery($qry);

                }*/

            $qry = "delete from product_link where product_code_master = $product_code";

            $runQ->RunQuery($qry);

            $xrow = simplexml_load_string($_REQUEST['cat']);

            foreach ($xrow->row as $row) {

                $qry = "insert into `product_link` (`acno`, `product_code_master`, `product_code_detail`, `sorting`)

									VALUES ('$acno', '{$product_code}', '{$row->pro}', '{$row->sort}')";

                $runQ->RunQuery($qry);

            }

            //start tag data

            /*$qry = "delete from product_tag_relationship where product_code = $product_code ";

            $runQ->RunQuery($qry);

            $crow = explode(',', $product_tag);

            foreach ($crow as $row) {



                //replace tag's space with dash

                $row_new = str_replace(" ", "-", $row);



                //check unique tag

                $qry = "SELECT count(tag_id) AS cnt FROM product_tag WHERE tag_name ='$row'";

                $res = $runQ->RunQuery($qry);

                $xres = simplexml_load_string($res);



                //tag new then insert in table

                if ($xres->row->cnt == 0) {

                    $qry = "insert into product_tag (tag_name,tag_url) values ('$row','$row_new')";

                    $runQ->RunQuery($qry);



                }



                //now insert in tag relationship

                $qry = "insert into product_tag_relationship (product_code, tag_id)

										values ($product_code, (SELECT tag_id FROM product_tag WHERE tag_name='{$row}') )";

                $runQ->RunQuery($qry);

            }*/

            //end tag data



            echo true;

        } else {

            echo false;

        }

        return;

        break;



    case 'savecustomfield' :



        $product_code = $_REQUEST['product_code'];

        $field_name = $_REQUEST['field_name'];

        $field_value = $_REQUEST['field_value'];



        $qry = "insert into product_custom_field (acno, product_code, custom_field_name,

								custom_field_value)

								values ('$acno', $product_code, '$field_name', '$field_value')";



        if ($runQ->RunQuery($qry)) {

            $qry = "select max(custom_field_serial) as custom_field_serial

									from product_custom_field

									where acno = '$acno'

									and product_code = '$product_code'";



            $xres = simplexml_load_string($runQ->RunQuery($qry));

            echo $xres->row->custom_field_serial;

        } else {

            echo false;

        }



        return;

        break;



    case 'editcustomfield' :

        $product_code = $_REQUEST['product_code'];

        $field_serial = $_REQUEST['field_serial'];

        $field_name = $_REQUEST['field_name'];

        $field_desc = $_REQUEST['field_desc'];



        $qry = "update product_custom_field

								set custom_field_name = '$field_name',

								custom_field_value = '$field_desc'

								where custom_field_serial = $field_serial

								and product_code = $product_code

								and acno = '$acno'";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'removecustomfield' :

        $product_code = $_REQUEST['product_code'];

        $field_serial = $_REQUEST['field_serial'];



        $qry = "delete from product_custom_field

								where custom_field_serial = $field_serial

								and product_code = $product_code

								and acno = '$acno'";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'savefilter' :



        $product_code = $_REQUEST['product_code'];

        $filter_name = $_REQUEST['filter_name'];



        $qry = "select ifnull(max(filter_code),0)+1 as filter_code

								from product_filter

								where product_code = $product_code

								and acno = '$acno'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $filter_code = $xres->row->filter_code;



        $qry = "insert into product_filter (acno, product_code, filter_code, filter_name)

								values ('$acno', $product_code, $filter_code, '$filter_name')";



        if ($runQ->RunQuery($qry)) {

            echo $filter_code;

        } else {

            echo false;

        }



        return;

        break;



    case 'removefilter' :



        $product_code = $_REQUEST['product_code'];

        $filter_code = $_REQUEST['filter_code'];



        $qry = "delete from product_filter

								where filter_code = $filter_code

								and product_code = $product_code

								and acno = '$acno'";



        if ($runQ->RunQuery($qry)) {

            $qry = "delete from product_filter_list

								where filter_code = $filter_code

								and product_code = $product_code

								and acno = '$acno'";

            echo $runQ->RunQuery($qry);

        } else {

            echo false;

        }



        return;

        break;



    case 'savefilterlist' :



        $product_code = $_REQUEST['product_code'];

        $filter_code = $_REQUEST['filter_code'];

        $filter_list_name = $_REQUEST['filter_list_name'];



        $qry = "select ifnull(max(filter_list_code),0)+1 as filter_list_code

								from product_filter_list

								where filter_code = $filter_code

								and product_code = $product_code

								and acno = '$acno'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $filter_list_code = $xres->row->filter_list_code;



        $qry = "insert into product_filter_list (acno, product_code, filter_code, filter_list_code,

								filter_list_name)

								values ('$acno', $product_code, $filter_code, $filter_list_code, '$filter_list_name')";



        if ($runQ->RunQuery($qry)) {

            echo $filter_list_code;

        } else {

            echo false;

        }



        return;

        break;



    case 'removefilterlist' :



        $product_code = $_REQUEST['product_code'];

        $filter_code = $_REQUEST['filter_code'];

        $filter_list_code = $_REQUEST['filter_list_code'];



        $qry = "delete from product_filter_list

								where filter_list_code = $filter_list_code

								and filter_code = $filter_code

								and product_code = $product_code

								and acno = '$acno'";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'savevari' :



        $product_code = $_REQUEST['product_code'];

        $vari_name = $_REQUEST['vari_name'];



        $qry = "select ifnull(max(vari_code),0)+1 as vari_code

								from product_vari

								where product_code = $product_code

								and acno = '$acno'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $vari_code = $xres->row->vari_code;



        $qry = "insert into product_vari (acno, product_code, vari_code, vari_name)

								values ('$acno', $product_code, $vari_code, '$vari_name')";



        if ($runQ->RunQuery($qry)) {

            echo $vari_code;

        } else {

            echo false;

        }



        return;

        break;



    case 'removevari' :



        $product_code = $_REQUEST['product_code'];

        $vari_code = $_REQUEST['vari_code'];



        $qry = "delete from product_vari

								where vari_code = $vari_code

								and product_code = $product_code

								and acno = '$acno'";



        if ($runQ->RunQuery($qry)) {

            $qry = "delete from product_vari_list

								where vari_code = $vari_code

								and product_code = $product_code

								and acno = '$acno'";

            echo $runQ->RunQuery($qry);

            $qry = "DELETE FROM sku_detail

										WHERE product_code=$product_code

										AND acno='$acno'

										AND sku_def like '%$vari_code-%'";

            echo $runQ->RunQuery($qry);

        } else {

            echo false;

        }



        return;

        break;



    case 'savevarilist' :



        $product_code = $_REQUEST['product_code'];

        $vari_code = $_REQUEST['vari_code'];

        $list_name = $_REQUEST['vari_list_name'];



        $qry = "select ifnull(max(list_code),0)+1 as list_code

								from product_vari_list

								where vari_code = $vari_code

								and product_code = $product_code

								and acno = '$acno'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $list_code = $xres->row->list_code;



        $qry = "insert into product_vari_list (acno, product_code, vari_code, list_code, list_name)

								values ('$acno', $product_code, $vari_code, $list_code, '$list_name')";

        //echo $qry;

        if ($runQ->RunQuery($qry)) {

            echo $list_code;

        } else {

            echo false;

        }



        return;

        break;



    case 'removevarilist' :



        $product_code = $_REQUEST['product_code'];

        $vari_code = $_REQUEST['vari_code'];

        $vari_list_code = $_REQUEST['vari_list_code'];



        $qry = "delete from product_vari_list

								where list_code = $vari_list_code

								and vari_code = $vari_code

								and product_code = $product_code

								and acno = '$acno'";

        echo $qry . "<br /> <br />";



        if ($runQ->RunQuery($qry)) {

            $qry = "DELETE FROM sku_detail

										WHERE product_code=$product_code

										AND acno='$acno'

										AND sku_def like '%$vari_code-$vari_list_code%'";

            echo $qry . "<br /> <br />";

            echo $runQ->RunQuery($qry);

        }



        return;

        break;



    case 'delproduct' :

        $code = $_REQUEST['code'];

        $qry = "update product set deleted='Y' where product_code = $code and acno = '$acno'";

        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'copy':

        $copycode = $_REQUEST['code'];

        $qry = "select ifnull(max(product_code),

       cast(concat(substr('$acno',5,5),'0000') as unsigned))+1 as product_code 

       from product

       where acno = '$acno'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $product_code = $xres->row->product_code;



        $qry = "insert into product

       select acno, $product_code, curdate(), date_format(now(),'%H%i'), `product_name`, `product_desc`, `product_nut_info`, `product_ing`, `product_tag`, `product_gender`, `product_collection`, `product_cost_price`, `product_price`, `product_inv`, `product_weight`, `product_min`, `product_max`, `product_customer_limit`, `product_order_limit`, `product_ref`, `product_alter`, `product_stat`, `product_sort`, `product_related`, `product_featured`, `product_sale`, `product_sale_price`, `url_slug`, `page_title`, `page_meta`, `meta_desc`, `brand_code`, `deleted`,`product_exist`, `promotion_product_name`, `promotion_start_date`, `promotion_end_date` from product

       where product_code = $copycode and acno = '$acno'";



        $runQ->RunQuery($qry);



        $sku_pro = $product_code . '00';

        $sku_insrt = "insert into sku_detail (sku_code, product_code, acno, sku_def, sku_desc, sku_qty)

      values($sku_pro, $product_code, '$acno', 'none', 'none', 100000)";

        $runQ->RunQuery($sku_insrt);



        $qry = "insert into product_vari

       select vari_code, vari_name, $product_code, acno

       from product_vari 

       where product_code = $copycode and acno = '$acno'";



        $runQ->RunQuery($qry);



        $qry = "insert into product_vari_list

       select vari_code, list_code, list_name, $product_code, acno

       from product_vari_list 

       where product_code = $copycode and acno = '$acno'";

        $runQ->RunQuery($qry);



        $qry = "insert into product_filter

       select filter_code, filter_name, $product_code, acno 

       from product_filter

       where product_code = $copycode and acno = '$acno'";



        $runQ->RunQuery($qry);



        $qry = "insert into product_filter_list

       select filter_code, filter_list_code, filter_list_name, $product_code, acno

       from product_filter_list 

       where product_code = $copycode and acno = '$acno'";

        $runQ->RunQuery($qry);



        $qry = "insert into product_cat

       select acno, $product_code, cat_code

       from product_cat

       where product_code = $copycode and acno = '$acno'";



        $runQ->RunQuery($qry);



        $qry = "insert into product_custom_field

       (acno, product_code, custom_field_name, custom_field_value)

       select acno, $product_code, custom_field_name, custom_field_value

       from product_custom_field

       where product_code = $copycode and acno = '$acno'";



        $runQ->RunQuery($qry);



        sleep(0.5);

        /////////------------ creating sku code -----------------////////////////////

        $qry = "SELECT count(*) as cnt

     FROM sku_detail

     WHERE product_code = $product_code

     AND acno = '$acno'";



        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $count = $xres->row->cnt;



        if ($count == 1) {

            $qry = "select count(*) as cnt

      from product_vari

      where acno = '$acno'

      and product_code = $product_code";

            $xres = simplexml_load_string($runQ->RunQuery($qry));

            $count = $xres->row->cnt;



            if ($count >= 1) {

                $qry2 = "update sku_detail set sku_qty=0 where product_code=$product_code and acno='$acno' and sku_def='none'";

                $runQ->RunQuery($qry2);

                $qry = "select distinct(vari_code)

       from product_vari_list

       where acno = '$acno'

       and product_code = $product_code";



                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $sku = " concat($product_code,";

                $df = " concat( ";

                $ln = " concat( ";

                $tb = "";

                $whr = "";

                $dm = "";

                $tdm = "";

                $wdm = "";

                foreach ($xres->row as $row) {

                    $vari_code = $row->vari_code;

                    $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";

                    $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                    $ln .= $dm . " A$vari_code.list_name ";

                    $tb .= $tdm . " product_vari_list as A$vari_code ";

                    $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $product_code and A$vari_code.vari_code = $vari_code ";

                    $dm = ", ',' , ";

                    $tdm = ",";

                    $wdm = "and";

                }

                $sku .= " ) as sku ";

                $df .= " ) as def ";

                $ln .= " ) as des";

                $qry = "insert into sku_detail

       select $sku, $product_code, '$acno', $df, $ln, null, null, 0 from $tb where $whr order by 1, 2";

                $runQ->RunQuery($qry);

            }



        }

        sleep(0.5);

        $qry = "select * from product where product_code=$product_code and acno='$acno'";

        $xres = simplexml_load_string($runQ->RunQuery($qry));

        if ($xres->row->product_inv == '') {

            $qry2 = "update sku_detail set sku_qty=100000 where product_code=$product_code and acno='$acno' and sku_def!='none'";

            $runQ->RunQuery($qry2);

        }



        echo $product_code;

        return;

        break;



    case 'imglist' :

        $product_code = $_REQUEST['product_code'];

        echo json_encode(glob("prod-pic/$acno/$product_code*S.*", GLOB_BRACE));



        return;

        break;



    case 'delfile':

        $fname = $_REQUEST["fname"];

        $info = pathinfo($fname);

        $name = basename($fname, '.' . $info['extension']);

        $file_name = str_replace('-S', '', $name);

        

        $files = glob("prod-pic/$acno/$file_name*.*");

        foreach ($files as $file) {

            unlink($file);

        }



       //echo $file_name;

       $fupload = explode('-', $file_name);

       

        $results = glob("prod-pic/$acno/$fupload[0]*S.*", GLOB_BRACE);

		

        //echo count($results);

        if (count($results) > 0) {

            $qry = "UPDATE product SET product_exist = '1' where product_code = " . $fupload[0] . "";

            $runQ->RunQuery($qry);

        } else {

            $qry = "UPDATE product SET product_exist = '0' where product_code = " . $fupload[0] . "";

            $runQ->RunQuery($qry);

        }

        echo true;

        return;

        break;



    case 'deliconfile':

        $fname = $_REQUEST["fname"];

        unlink($fname);

        echo true;

        

        $qry = "DELETE FROM product_icons_list

				  		WHERE image = '{$fname}'";



        $runQ->RunQuery($qry);

        

        return;

        break;



    case 'changeorder':

        $action = $_REQUEST["t"];

        $fname = $_REQUEST["fname"];



        $info = pathinfo($fname);

        $dir = $info['dirname'];

        $name = $info['filename'];

        $ext = $info['extension'];



        $fnexp = explode('-', $name);

        $file_name = $fnexp[0];

        $ser = $fnexp[1];



        $szarr = array('S', 'M', 'A');



        if ($action == 't') {

            foreach ($szarr as $sz) {

                rename("$dir/$file_name-0-$sz.$ext", "$dir/$file_name-X-$sz.$ext");

            }

            foreach ($szarr as $sz) {

                rename("$dir/$file_name-$ser-$sz.$ext", "$dir/$file_name-0-$sz.$ext");

            }

            foreach ($szarr as $sz) {

                rename("$dir/$file_name-X-$sz.$ext", "$dir/$file_name-$ser-$sz.$ext");

            }

        } elseif ($action == 'l') {

            $files = glob("$dir/$file_name*S.$ext");



            $nser = 0;

            $fls = "";

            foreach ($files as $file) {

                $info = pathinfo($file);

                $name = $info['filename'];

                $fl = explode('-', $name);

                $nser = $fl[1];

            }



            foreach ($szarr as $sz) {

                rename("$dir/$file_name-$ser-$sz.$ext", "$dir/$file_name-X-$sz.$ext");

            }

            foreach ($szarr as $sz) {

                rename("$dir/$file_name-$nser-$sz.$ext", "$dir/$file_name-$ser-$sz.$ext");

            }

            foreach ($szarr as $sz) {

                rename("$dir/$file_name-X-$sz.$ext", "$dir/$file_name-$nser-$sz.$ext");

            }

        } elseif ($action == 'n') {

            $files = glob("$dir/$file_name*S.$ext");



            $nser = 0;

            foreach ($files as $file) {

                $info = pathinfo($file);

                $name = $info['filename'];

                $fl = explode('-', $name);

                $nser = $fl[1];

            }



            if ($ser == $nser) {

            } else {

                $nser = $ser + 1;

                foreach ($szarr as $sz) {

                    rename("$dir/$file_name-$ser-$sz.$ext", "$dir/$file_name-X-$sz.$ext");

                }

                foreach ($szarr as $sz) {

                    rename("$dir/$file_name-$nser-$sz.$ext", "$dir/$file_name-$ser-$sz.$ext");

                }

                foreach ($szarr as $sz) {

                    rename("$dir/$file_name-X-$sz.$ext", "$dir/$file_name-$nser-$sz.$ext");

                }

            }

        } elseif ($action == 'p') {

            $files = glob("$dir/$file_name*S.$ext");



            if ($ser == 0) {

            } else {

                $nser = $ser - 1;

                foreach ($szarr as $sz) {

                    rename("$dir/$file_name-$ser-$sz.$ext", "$dir/$file_name-X-$sz.$ext");

                }

                foreach ($szarr as $sz) {

                    rename("$dir/$file_name-$nser-$sz.$ext", "$dir/$file_name-$ser-$sz.$ext");

                }

                foreach ($szarr as $sz) {

                    rename("$dir/$file_name-X-$sz.$ext", "$dir/$file_name-$nser-$sz.$ext");

                }

            }

        }



        echo true;

        return;

        break;

        

    case 'statusall' :

        $nstat = $_REQUEST['nstat'];

        $code = $_REQUEST['code'];



//        $qry = "update product set ";

        $qry = "update product_sub_detail set product_stat = '$nstat'  where product_code = $code";

        

        $qrycity = "select city_id from dashboard_city where city_id!='0' order by city_id ASC";

                $xrescity = simplexml_load_string($runQ->RunQuery($qrycity));

                foreach ($xrescity->row as $orderscity) {

                

                    $qry_stat = "INSERT INTO product_status_log (

                    product_code,

                    old_product_price,

                    new_product_price,

                    old_product_sale,

                    new_product_sale,

                    old_product_sale_price,

                    new_product_sale_price,

                    old_product_stat,

                    new_product_stat,

                    usrid,

                    DATETIME,

                    city_id,

                    product_status_loc_code

                    ) 

                    SELECT 

                    product.`product_code`,

                    product.`product_price`,

                    product.`product_price`,

                    product.`product_sale`,

                    product.`product_sale`,

                    product.`product_sale_price`,

                    product.`product_sale_price`,

                    product.`product_stat`,

                    '$nstat',

                    '$usrid',

                    NOW(),

                    city_id,

                    product_loc_code

                    FROM

                    product_sub_detail product

                    WHERE product_code = '$code' AND city_id='$orderscity->city_id' LIMIT 1 ";

                    $runQ->RunQuery($qry_stat);

                }

        



        //echo $qry;

        echo $runQ->RunQuery($qry);

        return;

        break;    



    case 'chstat' :

        $nstat = $_REQUEST['nstat'];

        $code = $_REQUEST['code'];

        $chtype = $_REQUEST['chtype'];

        $city_id = $_REQUEST['city_id'];

        $store_id = $_REQUEST['store_id'];

        

        $qrys = "SELECT city_id

								FROM dashboard_city

								where city_name = '$city_id'";



       $xress = simplexml_load_string($runQ->RunQuery($qrys));

            $city_id = $xress->row->city_id;

            

            

            $qrys = "SELECT loc_code FROM location WHERE loc_name='$store_id' LIMIT 1";



       $xress = simplexml_load_string($runQ->RunQuery($qrys));

            $store_id = $xress->row->loc_code;



//        $qry = "update product set ";

        $qry = "update product_sub_detail set ";

        if ($chtype == 'f') {

            $qry = "update product set   product_featured = '$nstat' where product_code = $code";

        } elseif ($chtype == 's') {

            $qry .= " product_sale = '$nstat'  where product_code = $code and city_id=$city_id and product_loc_code=$store_id";

            $qry_sale = "INSERT INTO product_status_log (

                        product_code,

                        old_product_price,

                        new_product_price,

                        old_product_sale,

                        new_product_sale,

                        old_product_sale_price,

                        new_product_sale_price,

                        old_product_stat,

                        new_product_stat,

                        usrid,

                        DATETIME,

                        city_id,

                        product_status_loc_code

                        ) 

                        SELECT 

                        product.`product_code`,

                        product.`product_price`,

                        product.`product_price`,

                        product.`product_sale`,

                        '$nstat',

                        product.`product_sale_price`,

                        product.`product_sale_price`,

                        product.`product_stat`,

                        product.`product_stat`,

                        '$usrid',

                        NOW(),

                        city_id,

                        product_loc_code

                        FROM

                        product_sub_detail product

                        WHERE product_code = '$code' AND city_id='$city_id' and product_loc_code=$store_id LIMIT 1 ";

            $runQ->RunQuery($qry_sale);



        } elseif ($chtype == 't') {

            $qry .= " product_stat = '$nstat'  where product_code = $code and city_id=$city_id and product_loc_code=$store_id";

            $qry_stat = "INSERT INTO product_status_log (

                        product_code,

                        old_product_price,

                        new_product_price,

                        old_product_sale,

                        new_product_sale,

                        old_product_sale_price,

                        new_product_sale_price,

                        old_product_stat,

                        new_product_stat,

                        usrid,

                        DATETIME,

                        city_id,

                        product_status_loc_code

                        ) 

                        SELECT 

                        product.`product_code`,

                        product.`product_price`,

                        product.`product_price`,

                        product.`product_sale`,

                        product.`product_sale`,

                        product.`product_sale_price`,

                        product.`product_sale_price`,

                        product.`product_stat`,

                        '$nstat',

                        '$usrid',

                        NOW(),

                        city_id,

                        product_loc_code

                        FROM

                        product_sub_detail product

                        WHERE product_code = '$code' AND city_id='$city_id' and product_loc_code='$store_id' LIMIT 1";

                        

            $runQ->RunQuery($qry_stat);

        }





        //echo $qry;

        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'prod_sort' :

        $prod_code = $_REQUEST['prod_code'];

        $value = $_REQUEST['value'];

        $qry = "update product

							  set product_sort=$value

							  where product_code=$prod_code and acno='$acno'";

        $runQ->RunQuery($qry);

        echo $value;



        return;

        break;

//**********Category

    //Import Category

    case 'importcat' :

        foreach ($_FILES["files"]["error"] as $key => $error) {

            if ($error == UPLOAD_ERR_OK) {

                $filename = $_FILES["files"]["name"][$key];

                @$ext = end(explode(".", $filename));

                $namewx = $_SESSION['ac'] . date('dmyHms');

                $name = $namewx . "." . $ext;

                if (!file_exists('import')) {

                    mkdir('import', 0777, true);

                }

                if (move_uploaded_file($_FILES["files"]["tmp_name"][$key], "import/" . $name)) {

                    //now import data from file

                    $handle = fopen("import/" . $name, "r");

                    if ($handle !== FALSE) {

                        fgets($handle);

                        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {

                            $qry = "REPLACE INTO category (cat_code, cat_name,cat_desc,acno) VALUES ('{$data[0]}', '{$data[1]}', '{$data[2]}', '{$data[3]}')";

                            $runQ->RunQuery($qry);

                        }

                        fclose($handle);



                        echo 'File imported succussfully !';

                    } else {

                        echo 'File not importing !';

                    }



                } else {

                    echo 'Error in file uploading';

                }

            }

        }



        return;

        break;



    case 'categorylist' :

        $qry = "SELECT cat_code, cat_name

								FROM category

								where acno = '$acno'";



        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'brandlist' :

        $qry = "SELECT id, name FROM brand";



        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'categoryau' :

        $code = $_REQUEST['code'];

        $name = $_REQUEST['name'];

        $desc = $_REQUEST['desc'];

        $sort = $_REQUEST['sortord'];

        $cat_url = strtolower(preg_replace('/[^\w\._]+/', '-', $name));

        if ($code == "") {

            $qry = "SELECT ifnull(max(cat_code),0)+1 as code FROM category";



            $res = simplexml_load_string($runQ->RunQuery($qry));

            $code = $res->row->code;

            $qry = "INSERT INTO category(cat_code, cat_name, cat_desc, acno, cat_stat,sort_order,cat_url)

							 		VALUES('$code', '$name', '$desc', '$acno','A','$sort','$cat_url')";

            if ($runQ->RunQuery($qry)) {

                echo $code;

            } else {

                echo false;

            }

        } else {

            $qry = "UPDATE category

									SET cat_name = '$name',

									cat_desc = '$desc',

									sort_order = '$sort',

									cat_url = '$cat_url'

									WHERE cat_code = '$code'";

            echo $runQ->RunQuery($qry);

        }



        return;

        break;



    case 'cat_chstat' :

        $code = $_REQUEST['code'];

        $stat = $_REQUEST['stat'];

        $qry = "update category

				 	   set cat_stat='$stat'

					   where cat_code=$code and acno='$acno'";

        if ($runQ->RunQuery($qry)) {

            echo $stat;

        } else {

            echo 'error';

        }





        return;

        break;



    case 'categorydelete':

        $code = $_REQUEST['code'];



        $qry = "DELETE FROM category

				  		WHERE cat_code = $code";



        echo $runQ->RunQuery($qry);



        return;

        break;





//**********Brand

    case 'brandau' :

        $code = $_REQUEST['code'];

        $name = $_REQUEST['name'];

        $desc = $_REQUEST['desc'];

        if ($code == "") {

            $qry = "SELECT ifnull(max(id),0)+1 as code FROM brand";

            $res = simplexml_load_string($runQ->RunQuery($qry));

            $code = $res->row->code;

            $qry = "INSERT INTO brand(`id`, `name`, `desc`, `acno`)

							 		VALUES('$code', '$name', '$desc', '$acno')";

            if ($runQ->RunQuery($qry)) {

                echo $code;

            } else {

                echo false;

            }

        } else {

            $qry = "UPDATE brand

									SET `name` = '$name',

									`desc` = '$desc'

									WHERE `id` = $code";

            echo $runQ->RunQuery($qry);

        }



        return;

        break;



    case 'branddelete':

        $code = $_REQUEST['code'];



        $qry = "DELETE FROM brand

				  		WHERE id = $code";

        echo $runQ->RunQuery($qry);



        return;

        break;



//**********Inventory				  

    case 'searchsku':

        $code = $_REQUEST['code'];

        $qry = "SELECT count(*) as cnt

					FROM sku_detail

					WHERE product_code = $code

					AND acno = '$acno'";



        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $count = $xres->row->cnt;



        if ($count == 0) {

            $qry = "select count(*) as cnt

						from product_vari

						where acno = '$acno'

						and product_code = $code";

            $xres = simplexml_load_string($runQ->RunQuery($qry));

            $count = $xres->row->cnt;



            if ($count >= 1) {

                $qry = "select distinct(vari_code)

							from product_vari_list

							where acno = '$acno'

							and product_code = $code";



                $xres = simplexml_load_string($runQ->RunQuery($qry));

                //$vari_code = $xres->row->vari_code;

                $sku = " concat($code,";

                $df = " concat( ";

                $ln = " concat( ";

                $tb = "";

                $whr = "";

                $dm = "";

                $tdm = "";

                $wdm = "";

                foreach ($xres->row as $row) {

                    $vari_code = $row->vari_code;

                    $sku .= $tdm . " concat(A$vari_code.vari_code,A$vari_code.list_code) ";

                    $df .= $dm . " concat(A$vari_code.vari_code,'-',A$vari_code.list_code) ";

                    $ln .= $dm . " A$vari_code.list_name ";

                    $tb .= $tdm . " product_vari_list as A$vari_code ";

                    $whr .= $wdm . " A$vari_code.acno = '$acno' and A$vari_code.product_code = $code and A$vari_code.vari_code = $vari_code ";

                    $dm = ", ',' , ";

                    $tdm = ",";

                    $wdm = "and";

                }

                $sku .= " ) as sku ";

                $df .= " ) as def ";

                $ln .= " ) as des";

                $qry = "insert into sku_detail

							select $sku, $code, '$acno', $df, $ln, null, null, 0 from $tb where $whr order by 1, 2";

                $runQ->RunQuery($qry);

                echo $qry;

            } else {

                $qry = "insert into sku_detail (sku_code, product_code, acno, sku_def, sku_desc, sku_weight, sku_price, sku_qty)

							values(concat($code,'00'), $code, '$acno', 'None', 'None', null, null, 0)";

                $runQ->RunQuery($qry);

            }

        }

        $qry = "SELECT sku_detail.sku_code, sku_def, sku_desc, ifnull(sku_weight,0) as sku_weight,

					ifnull(sku_price,0) as sku_price, ifnull(sku_qty,0) as quantity

					FROM sku_detail

					WHERE sku_detail.product_code = $code

					AND acno = '$acno'

					group by sku_detail.sku_code, sku_def, sku_desc";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'addinventory':

        $code = $_REQUEST['code'];

        $sku = $_REQUEST['sku'];

        $qty = $_REQUEST['qty'];



        $tm = date('Hi');



        $qry = "SELECT ifnull(max(sr_code),0)+1  as cd

					FROM product_inventory

					WHERE product_code = $code

					AND sku_code = $sku";



        $xres = simplexml_load_string($runQ->RunQuery($qry));

        $srcd = $xres->row->cd;



        $qry = "insert into product_inventory (sr_code, sr_date, sr_time, qty, product_code, sku_code)

				  values ($srcd, CURDATE(), '$tm', $qty, $code, $sku)";

        if ($runQ->RunQuery($qry)) {

            $qry = "UPDATE sku_detail

					  		SET sku_qty = ifnull(sku_qty,0) + $qty

							WHERE sku_code = $sku";



            $runQ->RunQuery($qry);



            $qry = "SELECT ifnull(sku_qty,0) as quantity

							FROM sku_detail

							WHERE product_code = $code

							AND sku_code = $sku";

            echo $runQ->RunQuery($qry);

        }



        return;

        break;



    case 'getminv':

        $code = $_REQUEST['code'];

        $qry = "SELECT ifnull(sum(sku_qty),0) as quantity

						FROM sku_detail

						WHERE product_code = $code";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'gethist':

        $sku = $_REQUEST['sku'];

        $qry = "SELECT sr_code, DATE_FORMAT(sr_date, '%d/%m/%Y') as srdt,

				  		LPAD(sr_time,4,'0') as srtm, qty

						FROM product_inventory

						WHERE sku_code = $sku

						order by sr_code";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'reminv':

        $sku = $_REQUEST['s'];

        $sr_code = $_REQUEST['r'];



        $qry = "SELECT qty

				  		FROM product_inventory

						WHERE sku_code = $sku

						AND sr_code = $sr_code";



        $xres = simplexml_load_string($runQ->RunQuery($qry));



        $qty = $xres->row->qty;



        $qry = "DELETE FROM product_inventory

						WHERE sku_code = $sku

						AND sr_code = $sr_code";



        $runQ->RunQuery($qry);



        $qry = "UPDATE sku_detail

				  		SET sku_qty = ifnull(sku_qty,0) - $qty

						WHERE sku_code = $sku";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'updatesku':

        $utype = $_REQUEST['utype'];

        $sku_code = $_REQUEST['sku_code'];

        $value = $_REQUEST['value'];



        if ($utype == 'W') {

            $updparam = " sku_weight = $value ";

        } else {

            $updparam = " sku_price = $value ";

        }



        $qry = "UPDATE sku_detail

				  		SET $updparam

				  		WHERE sku_code = $sku_code";



        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'upd_qty_none':

        $pr_code = $_REQUEST['code'];

        $qty = $_REQUEST['inv_qty'];

        $sku_code_up = $pr_code . '00';

        $qry = "UPDATE sku_detail

				  		SET sku_qty=$qty

				  		WHERE sku_code = $sku_code_up

						AND product_code=$pr_code";

        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'update_vari':

        $pr_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $vari_name = $_REQUEST['vari_name'];



        $qry = "UPDATE product_vari

				  		SET vari_name='$vari_name'

				  		WHERE vari_code = $vari_code 

						AND product_code=$pr_code";

        //echo $qry;

        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'update_vari_list':

        $pr_code = $_REQUEST['prod_code'];

        $vari_code = $_REQUEST['vari_code'];

        $vari_list_code = $_REQUEST['vari_list_code'];

        $vari_list_name = $_REQUEST['vari_list_name'];

        $qry = "UPDATE product_vari_list

				  		SET list_name='$vari_list_name'

				  		WHERE list_code = $vari_list_code

						AND vari_code=$vari_code 

						AND product_code=$pr_code";

        echo $runQ->RunQuery($qry);



        return;

        break;





    case 'addbrand':

        $pr_code = $_REQUEST['product_code'];

        $brand_id = $_REQUEST['brand_id'];

        $qry = "UPDATE product

				  		SET brand_code='$brand_id'

				  		WHERE product_code=$pr_code";

        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'delcatgroup' :

        $code = $_REQUEST['code'];



        $qry = "update category_group set `deleted`= 'Y' where

							cat_group_id = $code and acno = '$acno'";

        echo $runQ->RunQuery($qry);



        return;

        break;



    case 'catchstat' :

        $nstat = $_REQUEST['nstat'];

        $code = $_REQUEST['code'];

        $chtype = $_REQUEST['chtype'];



        $qry = "update category_group set cat_group_status = '$nstat'

						where cat_group_id = {$code} and acno = '{$acno}'";



        echo $runQ->RunQuery($qry);

        return;

        break;



    case 'upd_sort' :

        $cat_id = $_REQUEST['cat_id'];

        $sortorder = $_REQUEST['sortorder'];



        $qry = "update category_group set `cat_group_sort`= '$sortorder'

							where cat_group_id = '$cat_id' and acno = '$acno'";

        echo $runQ->RunQuery($qry);



        return;

        break;





    case 'catgroupau':

        $group_id = $_REQUEST['group_id'];

        $group_name = $_REQUEST['group_name'];

        $group_desc = $_REQUEST['group_desc'];

        $group_stat = $_REQUEST['group_stat'];

        $catval = $_REQUEST['catval'];

        $qtype = $_REQUEST['qtype'];

        $group_url = strtolower(preg_replace('/[^\w\._]+/', '-', $group_name));

        //if($type == 'e'){

        if ($qtype == 'u') {

            $qry_cat = "update `category_group` set cat_group_name = '{$group_name}', cat_group_desc = '{$group_desc}',

				 cat_group_status = '{$group_stat}' , cat_group_url = '{$group_url}'WHERE `cat_group_id` = {$group_id} and acno = '{$acno}';";

            if ($runQ->RunQuery($qry_cat)) {

                $qry_del = "DELETE FROM `category_group_detail` WHERE `cat_group_id` = {$group_id} and acno = '{$acno}';";

                $runQ->RunQuery($qry_del);

                $crow = explode(',', $catval);

                foreach ($crow as $row) {



                    $qry_detail = "insert into category_group_detail (cat_code, cat_group_id, acno)

							values ($row, $group_id, '$acno')";

                    $runQ->RunQuery($qry_detail);

                }

                echo 'success update';

            } else {

                echo 'error update';

            }

        }



        if ($qtype == 'a') {



            $qry_cat = "insert into category_group (cat_group_name,cat_group_desc,cat_group_status,cat_group_url, acno)

							values ('$group_name', '$group_desc','$group_stat','$group_url','$acno')";

            if ($last_insert_group_id = $runQ->LastInsertID($qry_cat)) {

                $crow = explode(',', $catval);

                foreach ($crow as $row) {



                    $qry_detail = "insert into category_group_detail (cat_code, cat_group_id, acno)

							values ($row, $last_insert_group_id, '$acno')";



                    $runQ->RunQuery($qry_detail);

                }

                echo $last_insert_group_id;

            } else {

                echo 'error add';

            }

        }



        return;

        break;

    case "levelOne":

        $selectCity = $_REQUEST['city'];

        $assortment_type = $_REQUEST['assortment_type'];

        $qry = "SELECT id,name FROM product_hierarchy_one

LEFT JOIN product_category_city ON product_hierarchy_one.id = product_category_city.category_id

WHERE   product_category_city.city_id = $selectCity AND product_hierarchy_one.type= '{$assortment_type}' AND product_category_city.`status` = 'A'  GROUP BY	id ORDER BY	id ASC";

        echo $runQ->RunQuery($qry);

        break;





    case "levelTwo":

        $levelOne = $_REQUEST['id'];

        $selectCity = $_REQUEST['city'];

        $assortment_type = $_REQUEST['assortment_type'];

         $qry = "SELECT id,name FROM product_hierarchy_two

 LEFT JOIN product_category_city ON product_hierarchy_two.id = product_category_city.category_id

WHERE  level_one_id ='$levelOne' AND   product_category_city.city_id = $selectCity AND product_hierarchy_two.type= '{$assortment_type}' AND product_category_city.`status` = 'A' GROUP BY	id ORDER BY	id ASC";

        echo $runQ->RunQuery($qry);

        break;



    case "levelThree":

        $levelTwo = $_REQUEST['id'];

        $selectCity = $_REQUEST['city'];

        $assortment_type = $_REQUEST['assortment_type'];

        $qry = "SELECT id,name FROM product_hierarchy_three

LEFT JOIN product_category_city ON product_hierarchy_three.id = product_category_city.category_id

WHERE level_id_two ='$levelTwo' AND   product_category_city.city_id = $selectCity AND product_hierarchy_three.type= '{$assortment_type}' AND product_category_city.`status` = 'A' GROUP BY	id ORDER BY	id ASC";

        echo $runQ->RunQuery($qry);

        break;



    case "levelFour":

        $levelThree = $_REQUEST['id'];

        $selectCity = $_REQUEST['city'];

        $assortment_type = $_REQUEST['assortment_type'];

        $qry = "SELECT id,name FROM product_hierarchy_four

LEFT JOIN product_category_city ON product_hierarchy_four.id = product_category_city.category_id

WHERE level_three_id ='$levelThree' AND   product_category_city.city_id = $selectCity AND product_hierarchy_four.type= '{$assortment_type}' AND product_category_city.`status` = 'A' GROUP BY	id ORDER BY	id ASC";

        echo $runQ->RunQuery($qry);

        break;



    case "levelFive":

        $levelFour = $_REQUEST['id'];

        $selectCity = $_REQUEST['city'];

        $assortment_type = $_REQUEST['assortment_type'];

        $qry = "SELECT id,name FROM product_hierarchy_five

LEFT JOIN product_category_city ON product_hierarchy_five.id = product_category_city.category_id

WHERE level_four_id ='$levelFour' AND   product_category_city.city_id = $selectCity AND product_hierarchy_five.type= '{$assortment_type}' AND product_category_city.`status` = 'A' GROUP BY	id ORDER BY	id ASC";

        echo $runQ->RunQuery($qry);

        break;



    case "levelSix":

        $levelFive = $_REQUEST['id'];

        $selectCity = $_REQUEST['city'];

        $assortment_type = $_REQUEST['assortment_type'];

        $qry = "SELECT id,name FROM product_hierarchy_six

LEFT JOIN product_category_city ON product_hierarchy_six.id = product_category_city.category_id

WHERE level_five_id ='$levelFive' AND   product_category_city.city_id = $selectCity AND product_hierarchy_six.type= '{$assortment_type}' AND product_category_city.`status` = 'A' GROUP BY	id ORDER BY	id ASC";

        echo $runQ->RunQuery($qry);

        break;



    case "levelUpdate":

        //$data = $_REQUEST['data'];

        //$xml = simplexml_load_string($data);

        $id = $_REQUEST['id'];

        $levelName = htmlspecialchars($_REQUEST['levelName'], ENT_QUOTES);

        $level = $_REQUEST['level'];

        $image = $_REQUEST['image'];

        $sorting = $_REQUEST['sorting'];

        $image_rondom_no = $_REQUEST['image_rondom_no'];

        $stratdate = $_REQUEST['startdate'].' '.$_REQUEST['starttime'];

		$enddate = $_REQUEST['enddate'].' '.$_REQUEST['endtime'];

		$sale_text = $_REQUEST['sale_text'];

        $description = str_replace("'", '"', $_REQUEST['product_desc']);

        $hierarchy_url = preg_replace('/[^\w\._]+/', '-', strtolower($_REQUEST['levelName']));

        if ($level == "levelOne") {

             $qry = "UPDATE product_hierarchy_one SET name = '$levelName', image = '$image', sorting = '$sorting', image_rondom = '$image_rondom_no',	hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text' WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        } else if ($level == "levelTwo") {

            $qry = "UPDATE product_hierarchy_two SET name = '$levelName', image = '$image', sorting = '$sorting', image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'  WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        } else if ($level == "levelThree") {

            $qry = "UPDATE product_hierarchy_three SET name = '$levelName', image = '$image', sorting = '$sorting',	image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'  WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        } else if ($level == "levelFour") {

            $qry = "UPDATE product_hierarchy_four SET name = '$levelName', image = '$image', sorting = '$sorting', image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'  WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        } else if ($level == "levelFive") {

            $qry = "UPDATE product_hierarchy_five SET name = '$levelName', image = '$image',

sorting = '$sorting', image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'  WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        } else if ($level == "levelSix") {

            $qry = "UPDATE product_hierarchy_six SET name = '$levelName', image = '$image', sorting = '$sorting', image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'  WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        } else if ($level == "brand") {

            $qry = "UPDATE brand SET name = '$levelName', image = '$image', sorting = '$sorting', image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url',description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'  WHERE id = '$id'";

            echo $runQ->RunQuery($qry);



        } else if ($level == "campaign") {

            $qry = "UPDATE campaign_page SET name = '$levelName', sorting = '$sorting', image_rondom = '$image_rondom_no', hierarchy_url = '$hierarchy_url' ,description = '$description',	sale_start_duration = '$stratdate',	sale_end_duration = '$enddate',	sale_text = '$sale_text'

 WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        }

        break;

        

        case "Updatelevelcities":



        $id = $_REQUEST['id'];

        $level = $_REQUEST['level'];

        $allcitiesarray = $_REQUEST['city'];

        

        $levelnumber='';

        if($level=='levelOne'){$levelnumber='1';}

        if($level=='levelTwo'){$levelnumber='2';}

        if($level=='levelThree'){$levelnumber='3';}

        if($level=='levelFour'){$levelnumber='4';}

        if($level=='levelFive'){$levelnumber='5';}

        if($level=='levelSix'){$levelnumber='6';}

        

        $qry = "DELETE FROM product_category_city WHERE level = '{$levelnumber}' and category_id='{$id}'";

        $runQ->RunQuery($qry);

		 

		 foreach($allcitiesarray as $valueallcities){

            $qry = "INSERT INTO product_category_city(level,category_id,city_id,status) VALUES ('$levelnumber','$id','$valueallcities','A')";

            $runQ->RunQuery($qry);

		 }

        break;

        

        case "Updatelevelcitiesvendor":



        $id = $_REQUEST['id'];

        $level = $_REQUEST['level'];

        $vendor = $_REQUEST['vendor'];

        $allcitiesarray = $_REQUEST['city'];

        

        $levelnumber='';

        if($level=='levelOne'){$levelnumber='1';}

        if($level=='levelTwo'){$levelnumber='2';}

        if($level=='levelThree'){$levelnumber='3';}

        if($level=='levelFour'){$levelnumber='4';}

        if($level=='levelFive'){$levelnumber='5';}

        if($level=='levelSix'){$levelnumber='6';}

        

        if($vendor=='metro'){

            $qry = "DELETE FROM product_category_city WHERE level = '{$levelnumber}' and category_id='{$id}'";

            $runQ->RunQuery($qry);

            

            foreach($allcitiesarray as $valueallcities){

            $qry = "INSERT INTO product_category_city(level,category_id,city_id,status) VALUES ('$levelnumber','$id','$valueallcities','A')";

            $runQ->RunQuery($qry);

            }

        }else{

            $qry = "DELETE FROM product_category_city_sdk WHERE level = '{$levelnumber}' and category_id='{$id}' and vendor_code='{$vendor}'";

            $runQ->RunQuery($qry);

            

            foreach($allcitiesarray as $valueallcities){

            $qry = "INSERT INTO product_category_city_sdk(level,category_id,city_id,status,vendor_code) VALUES ('$levelnumber','$id','$valueallcities','A','{$vendor}')";

            $runQ->RunQuery($qry);

            }

        }

        

        break;

        

        case "getcategorycities":

        $catid = $_REQUEST['id'];

        $level = $_REQUEST['level'];

        

        $levelnumber='';

        if($level=='levelOne'){$levelnumber='1';}

        if($level=='levelTwo'){$levelnumber='2';}

        if($level=='levelThree'){$levelnumber='3';}

        if($level=='levelFour'){$levelnumber='4';}

        if($level=='levelFive'){$levelnumber='5';}

        if($level=='levelSix'){$levelnumber='6';}

        

        $assortment_type = $_REQUEST['assortment_type'];

        $qry = "SELECT * from product_category_city

WHERE  level ='{$levelnumber}' AND  category_id='{$catid}' and status='A'";

        echo $runQ->RunQuery($qry);

        break;

        

        case "getcategorycitiesvendor":

        $catid = $_REQUEST['id'];

        $level = $_REQUEST['level'];

        $vendor = $_REQUEST['vendor_code'];

        

        $levelnumber='';

        if($level=='levelOne'){$levelnumber='1';}

        if($level=='levelTwo'){$levelnumber='2';}

        if($level=='levelThree'){$levelnumber='3';}

        if($level=='levelFour'){$levelnumber='4';}

        if($level=='levelFive'){$levelnumber='5';}

        if($level=='levelSix'){$levelnumber='6';}

        if($vendor=='metro'){

             $qry = "SELECT * from product_category_city

        WHERE  level ='{$levelnumber}' AND  category_id='{$catid}' and status='A'";

        }else{

             $qry = "SELECT * from product_category_city_sdk

    WHERE  level ='{$levelnumber}' AND  category_id='{$catid}' and status='A' and vendor_code='{$vendor}'";

            

        }

        echo $runQ->RunQuery($qry);

        break;

        

          case "levelUpdateCustomCategory":



        $id = $_REQUEST['id'];

        $level = $_REQUEST['level'];

        $sorting = $_REQUEST['sorting'];

        $status = $_REQUEST['status'];

        $product_type_update = $_REQUEST['product_type_update'];

        

        

             echo $qry = "UPDATE product_category_custom_menu SET sorting = '$sorting', category_id = '$level', menu_type = '$product_type_update', status = '$status' WHERE id = '$id'";

            echo $runQ->RunQuery($qry);

        break;

        

    case "custommenuinsert":

        //$data = $_REQUEST['data'];

        //$xml = simplexml_load_string($data);



        $master_menu_type = $_REQUEST['master_menu_type'];

        $level_one = $_REQUEST['level_one'];

        $level_sorting = $_REQUEST['level_sorting'];

        $menu_type = $_REQUEST['menu_type'];

        $city_id = $_REQUEST['city_id'];

        $product_type = $_REQUEST['product_type'];

        

        $levelnumber='1';

        

            if($city_id=='0'){

                echo $qry = "select city_id from dashboard_city where city_id!='0' order by city_id ASC";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $city_dropdown_html = '';

                foreach ($xres->row as $orders) {

                echo $qry = "INSERT INTO product_category_custom_menu(level,sorting,category_id,city_id,status,menu_type,master_menu_type) VALUES ('$levelnumber','$level_sorting','$level_one','$orders->city_id','A','$product_type','$master_menu_type')";

                echo $runQ->RunQuery($qry);

                }

            }else{

                echo $qry = "INSERT INTO product_category_custom_menu(level,sorting,category_id,city_id,status,menu_type,master_menu_type) VALUES ('$levelnumber','$level_sorting','$level_one','$city_id','A','$product_type','$master_menu_type')";

                echo $runQ->RunQuery($qry);

            }

        break;

        

    case "levelInsert":

        //$data = $_REQUEST['data'];

        //$xml = simplexml_load_string($data);



        $level = $_REQUEST['level'];

        $level_one = $_REQUEST['level_one'];

        $level_two = $_REQUEST['level_two'];

        $level_three = $_REQUEST['level_three'];

        $level_four = $_REQUEST['level_four'];

        $level_five = $_REQUEST['level_five'];

        $level_name = $_REQUEST['level_name'];

        $city_id = $_REQUEST['city_id'];

        $vendor_code = $_REQUEST['vendor_code'];

        $product_type = $_REQUEST['product_type'];

        $level_name = htmlspecialchars($level_name, ENT_QUOTES);

        $hierarchy_url = preg_replace('/[^\w\._]+/', '-', strtolower($_REQUEST['level_name']));

        

        $levelnumber='';

        $levelid='';

        if($level=='levelOne'){$levelnumber='1';}

        if($level=='levelTwo'){$levelnumber='2';}

        if($level=='levelThree'){$levelnumber='3';}

        if($level=='levelFour'){$levelnumber='4';}

        if($level=='levelFive'){$levelnumber='5';}

        if($level=='levelSix'){$levelnumber='6';}

        

        

                                    

        if ($level == "levelOne") {

            echo $qry = "INSERT INTO product_hierarchy_one(name,hierarchy_url,type) VALUES ('$level_name','$hierarchy_url','$product_type')";

            echo $levelid = $runQ->LastInsertID($qry);

        } else if ($level == "levelTwo") {

            $qry = "INSERT INTO product_hierarchy_two(name,level_one_id,hierarchy_url,type) VALUES ('$level_name','$level_one','$hierarchy_url','$product_type')";

            echo $levelid = $runQ->LastInsertID($qry);

        } else if ($level == "levelThree") {

            $qry = "INSERT INTO product_hierarchy_three(name,level_id_two,hierarchy_url,type) VALUES ('$level_name','$level_two','$hierarchy_url','$product_type')";

            echo $levelid = $runQ->LastInsertID($qry);

        } else if ($level == "levelFour") {

            $qry = "INSERT INTO product_hierarchy_four(name,level_three_id,hierarchy_url,type) VALUES ('$level_name','$level_three','$hierarchy_url','$product_type')";

            echo $levelid = $runQ->LastInsertID($qry);

        } else if ($level == "levelFive") {

            $qry = "INSERT INTO product_hierarchy_five(name,level_four_id,hierarchy_url) VALUES ('$level_name','$level_four','$hierarchy_url','$product_type')";

            echo $levelid = $runQ->LastInsertID($qry);

        } else if ($level == "levelSix") {

            $qry = "INSERT INTO product_hierarchy_six(name,level_five_id,hierarchy_url,type) VALUES ('$level_name','$level_five','$hierarchy_url','$product_type')";

            echo $levelid = $runQ->LastInsertID($qry);

        } else if ($level == "brand") {

            $qry = "INSERT INTO brand(name,hierarchy_url,type) VALUES ('$level_name','$hierarchy_url','$product_type')";

            echo $runQ->RunQuery($qry);

        } else if ($level == "campaign") {

            $qry = "INSERT INTO campaign_page(name,hierarchy_url,type) VALUES ('$level_name','$hierarchy_url','$product_type')";



            echo $runQ->RunQuery($qry);

        }

        

        if($level != "brand" && $level != "campaign" ){        

            if($city_id=='0'){

                $qry = "select city_id from dashboard_city where city_id!='0' order by city_id ASC";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                foreach ($xres->row as $orders) {

                    $qry = "INSERT INTO product_category_city(level,category_id,city_id,status) VALUES ('$levelnumber','$levelid','$orders->city_id','A')";

                    echo $runQ->RunQuery($qry);

                    

                    

                    if($vendor_code=='0'){

                        $qryvendor = "select vendor_slug_code from vendor_sdk where status='A' order by id ASC";

                        $xresvendor = simplexml_load_string($runQ->RunQuery($qryvendor));

                        foreach ($xresvendor->row as $ordersvendor) {

                        $qry = "INSERT INTO product_category_city_sdk(level,category_id,city_id,status,vendor_code) VALUES ('$levelnumber','$levelid','$orders->city_id','A','$ordersvendor->vendor_slug_code')";

                        $runQ->RunQuery($qry);

                        }

                    }

                }

            

            }else{

                $qry = "INSERT INTO product_category_city(level,category_id,city_id,status) VALUES ('$levelnumber','$levelid','$city_id','A')";

                echo $runQ->RunQuery($qry);

                

                if($vendor_code=='0'){

                        $qryvendor = "select vendor_slug_code from vendor_sdk where status='A' order by id ASC";

                        $xresvendor = simplexml_load_string($runQ->RunQuery($qryvendor));

                        foreach ($xresvendor->row as $ordersvendor) {

                        $qry = "INSERT INTO product_category_city_sdk(level,category_id,city_id,status,vendor_code) VALUES ('$levelnumber','$levelid','$city_id','A','$ordersvendor->vendor_slug_code')";

                        $runQ->RunQuery($qry);

                        }

                    }else{

                        $qry = "INSERT INTO product_category_city_sdk(level,category_id,city_id,status,vendor_code) VALUES ('$levelnumber','$levelid','$city_id','A','$vendor_code')";

                        $runQ->RunQuery($qry);

                    }

            }

        }

        break;

        

    case "levelOneDataCustomMenu":

        $assortmenttype = $_REQUEST['assortmenttype'];

        $qry = "SELECT * FROM product_hierarchy_one where type='{$assortmenttype}' ";

        echo $runQ->RunQuery($qry);

    break;

    

    case "levelOneData":

        $qry = "SELECT * FROM product_hierarchy_one";

        echo $runQ->RunQuery($qry);

        break;

    case "levelTwoData":

        $id = $_REQUEST['id'];

        echo $qry = "SELECT * FROM product_hierarchy_two WHERE level_one_id = '$id'";

        echo $runQ->RunQuery($qry);

        break;

    case "levelThreeData":

        $id = $_REQUEST['id'];

        $qry = "SELECT * FROM product_hierarchy_three WHERE level_id_two = '$id'";

        echo $runQ->RunQuery($qry);

        break;

    case "levelFourData":

        $id = $_REQUEST['id'];

        $qry = "SELECT * FROM product_hierarchy_four WHERE level_three_id = '$id'";

        echo $runQ->RunQuery($qry);

        break;

    case "levelFiveData":

        $id = $_REQUEST['id'];

        $qry = "SELECT * FROM product_hierarchy_five WHERE level_four_id = '$id'";

        echo $runQ->RunQuery($qry);

        break;



    case "product_tag":

        $qry = "SELECT * FROM product_tag ";

        echo $runQ->RunQuery($qry);

        break;



    case "product_link_data":

        $qry = "SELECT

  pl.`product_code_master`,

  pl.`product_code_detail`,

  p.`product_name`,

  product_ref,

  pl.`sorting` 

FROM

  product_link pl,

  product p 

WHERE pl.`product_code_detail` = p.`product_code` 

  ORDER BY id ASC";

        echo $runQ->RunQuery($qry);

        break;



    case "product_review":

        $id = $_REQUEST['id'];

        $status = $_REQUEST['status'];

        $qry = "UPDATE product_review SET status = '$status',change_timestamp=NOW(),usrid='{$_SESSION['usrid']}' WHERE id = '$id'";

        echo $runQ->RunQuery($qry);

        break;



    case "get_product_sub_detail":

        $product_sub_code = $_REQUEST['sub_detail_code'];

          $qry = "SELECT 	product_sub_detail.product_sub_code,	product_sub_detail.city_id,product_sub_detail.`product_loc_code`,	product_sub_detail.product_ref,

	product_sub_detail.product_code,	product_sub_detail.product_price,	product_sub_detail.product_sale_price,

	product_sub_detail.product_sale,	product_sub_detail.isHamper,	product_sub_detail.product_hamper_price,

		product_sub_detail.product_order_limit,	product_sub_detail.product_featured_text,	product_sub_detail.product_related,

	product_sub_detail.sku_qty,	product_sub_detail.product_stat,	product_sub_detail.seasonal_product,	product_sub_detail.specific_brand,

	product_sub_detail.product_scoring,	dashboard_city.city_name, product_sub_detail.`open_stock`,product_sub_detail.`product_shipamount`,product_sub_detail.`hblfeaturedtext`,product_sub_detail.`hblprice`,product_sub_detail.`hblstatus`,product_sub_detail.`product_commission`,product_sub_detail.`product_ref_parent`,product_sub_detail.`product_ref_parentunit`,product_sub_detail.`product_order_limit_minimum`,product_sub_detail.`product_price_day2`  

FROM	product_sub_detail

	LEFT JOIN dashboard_city ON product_sub_detail.city_id = dashboard_city.city_id

	 WHERE product_sub_detail.`sub_detail_active_status` = 'A'  AND  product_sub_detail.`product_sub_code`=$product_sub_code order by product_sub_detail.`city_id` ASC";

        echo $runQ->RunQuery($qry);

        break;

        

        case "get_product_sub_detail_new":

        $product_sub_code = $_REQUEST['sub_detail_code'];

          $qry = "SELECT 	product_sub_detail.product_sub_code,	product_sub_detail.city_id,product_sub_detail.`product_loc_code`,	product_sub_detail.product_ref,

	product_sub_detail.product_code,	product_sub_detail.product_price,	product_sub_detail.product_sale_price,

	product_sub_detail.product_sale,	product_sub_detail.isHamper,	product_sub_detail.product_hamper_price,

		product_sub_detail.product_order_limit,	product_sub_detail.product_featured_text,	product_sub_detail.product_related,

	product_sub_detail.sku_qty,	product_sub_detail.product_stat,	product_sub_detail.seasonal_product,	product_sub_detail.specific_brand,

	product_sub_detail.product_scoring,	dashboard_city.city_name, product_sub_detail.`open_stock`,product_sub_detail.`product_shipamount`,product_sub_detail.`hblfeaturedtext`,product_sub_detail.`hblprice`,

	product_sub_detail.`hblstatus`,product_sub_detail.`product_commission`,product_sub_detail.`product_ref_parent`,product_sub_detail.`product_ref_parentunit`,

	product_sub_detail.`product_order_limit_minimum`,product_sub_detail.`product_price_day2` ,product_sub_detail.`isexpress_allow`  

FROM	product_sub_detail

	LEFT JOIN dashboard_city ON product_sub_detail.city_id = dashboard_city.city_id

	 WHERE product_sub_detail.`sub_detail_active_status` = 'A'  AND  product_sub_detail.`product_sub_code`=$product_sub_code order by product_sub_detail.`city_id` ASC";

        echo $runQ->RunQuery($qry);

        break;





    case "save_product_sub_detail":

        $cityid = $_REQUEST['city_id'];

        $product_code = $_REQUEST['product_code'];

        $product_stat = $_REQUEST['product_stat'];

        if ($cityid == 0) {

            $cityqry = "SELECT * FROM `dashboard_city` where city_id IN ('1','2','3','4','5','6','7') ORDER BY city_id ASC";

            $citydata = simplexml_load_string($runQ->RunQuery($cityqry));

            foreach ($citydata as $pcity) {

                $dltqry = "DELETE FROM product_sub_detail WHERE product_code = {$_REQUEST['product_code']}  and city_id='{$pcity->city_id}'";

                /* $dltqry = "UPDATE `product_sub_detail` SET  `product_stat` = 'I',`sub_detail_active_status` = 'I'

                WHERE product_code = {$_REQUEST['product_code']}

  and city_id='{$pcity->city_id}'";*/

                $runQ->RunQuery($dltqry);

                $product_featured_text = '';

                $product_featured_text = htmlspecialchars($_REQUEST['product_featured_text'], ENT_QUOTES);

                  $qry = "INSERT INTO  `product_sub_detail` ( `city_id`,`product_ref`,`product_code`,`product_price`,`product_sale_price`,

`product_sale`,`isHamper`,`product_hamper_price`,`product_order_limit`,`product_featured_text`,`product_related`,

`sku_qty`,`product_stat`,`seasonal_product`,`specific_brand`,`product_scoring`,`open_stock`,`product_shipamount`,`hblstatus`,`hblprice`,`hblfeaturedtext`,

`product_assortment_type`,`product_commission`,`product_order_limit_minimum`,`product_price_day2`)

VALUES( '{$pcity->city_id}','{$_REQUEST['product_ref']}','{$_REQUEST['product_code']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['isHamper']}','{$_REQUEST['hamper_price']}','{$_REQUEST['product_order_limit']}','{$product_featured_text}',

'{$_REQUEST['product_related']}','{$_REQUEST['sku_qty']}',

'{$_REQUEST['product_stat']}','{$_REQUEST['seasonal_product']}','{$_REQUEST['specific_brand']}','{$_REQUEST['product_scoring']}',

'{$_REQUEST['sub_detail_open_stock']}','{$_REQUEST['sub_detail_shipamount']}','{$_REQUEST['product_hbl_status']}',

'{$_REQUEST['product_hbl_price']}','{$_REQUEST['product_hbl_featured_text']}','{$_REQUEST['assortment_type']}',

'{$_REQUEST['product_commission']}','{$_REQUEST['product_order_limit_minimum']}','{$_REQUEST['product_price_day2']}')";

                $runQ->RunQuery($qry);

                $qry = "insert into product_status_log (product_code,old_product_price,new_product_price,

old_product_sale,new_product_sale,old_product_sale_price,new_product_sale_price,

old_product_stat,new_product_stat,usrid,datetime,`city_id`)

values ('$product_code','{$_REQUEST['product_price_old']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_old']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['product_sale_price_old']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_stat_old']}','{$product_stat}','$usrid',NOW(),'{$pcity->city_id}')";

                $runQ->RunQuery($qry);

            }

        } else {

            $dltqry = "DELETE FROM product_sub_detail WHERE product_code = {$_REQUEST['product_code']}

 and city_id='{$_REQUEST['city_id']}'";

            /* $dltqry = "UPDATE `product_sub_detail` SET  `product_stat` = 'I',`sub_detail_active_status` = 'I'

  WHERE product_code = {$_REQUEST['product_code']}  and city_id='{$_REQUEST['city_id']}'";*/

            $runQ->RunQuery($dltqry);

            

            $product_featured_text = '';

                $product_featured_text = htmlspecialchars($_REQUEST['product_featured_text'], ENT_QUOTES);

                

             $qry = "INSERT INTO  `product_sub_detail` ( `city_id`,`product_ref`,`product_code`,`product_price`,`product_sale_price`,

`product_sale`,`isHamper`,`product_hamper_price`,`product_order_limit`,`product_featured_text`,`product_related`,

`sku_qty`,`product_stat`,`seasonal_product`,`specific_brand`,`product_scoring`,`open_stock`,`product_shipamount`,`hblstatus`,`hblprice`,`hblfeaturedtext`,`product_assortment_type`,`product_commission`,`product_order_limit_minimum`,`product_price_day2`)

VALUES( '{$_REQUEST['city_id']}','{$_REQUEST['product_ref']}','{$_REQUEST['product_code']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['isHamper']}','{$_REQUEST['hamper_price']}','{$_REQUEST['product_order_limit']}','{$product_featured_text}',

'{$_REQUEST['product_related']}','{$_REQUEST['sku_qty']}',

'{$_REQUEST['product_stat']}','{$_REQUEST['seasonal_product']}','{$_REQUEST['specific_brand']}','{$_REQUEST['product_scoring']}',

'{$_REQUEST['sub_detail_open_stock']}','{$_REQUEST['sub_detail_shipamount']}','{$_REQUEST['product_hbl_status']}','{$_REQUEST['product_hbl_price']}',

'{$_REQUEST['product_hbl_featured_text']}','{$_REQUEST['assortment_type']}','{$_REQUEST['product_commission']}','{$_REQUEST['product_order_limit_minimum']}',

'{$_REQUEST['product_price_day2']}')";

            $runQ->RunQuery($qry);

            $qry = "insert into product_status_log (product_code,old_product_price,new_product_price,

old_product_sale,new_product_sale,old_product_sale_price,new_product_sale_price,

old_product_stat,new_product_stat,usrid,datetime,`city_id`)

values ('$product_code','{$_REQUEST['product_price_old']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_old']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['product_sale_price_old']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_stat_old']}','{$product_stat}','$usrid',NOW(),'{$_REQUEST['city_id']}')";

            $runQ->RunQuery($qry);

        }



        $qry = "SELECT 	product_sub_detail.product_sub_code,	product_sub_detail.city_id,	product_sub_detail.product_ref,

	product_sub_detail.product_code,	product_sub_detail.product_price,	product_sub_detail.product_sale_price,

	product_sub_detail.product_sale,	product_sub_detail.isHamper,	product_sub_detail.product_hamper_price,

		product_sub_detail.product_order_limit,	product_sub_detail.product_featured_text,	product_sub_detail.product_related,

	product_sub_detail.sku_qty,	product_sub_detail.product_stat,	product_sub_detail.seasonal_product,	product_sub_detail.specific_brand,

	product_sub_detail.product_scoring,	dashboard_city.city_name, product_sub_detail.`open_stock`,product_sub_detail.`product_shipamount`,

	product_sub_detail.`product_order_limit_minimum` ,product_sub_detail.`product_price_day2` ,product_sub_detail.`isexpress_allow`

FROM	product_sub_detail

	LEFT JOIN dashboard_city ON product_sub_detail.city_id = dashboard_city.city_id

	 WHERE product_sub_detail.`sub_detail_active_status` = 'A'  AND  product_sub_detail.`product_code`={$_REQUEST['product_code']} ";

        echo $runQ->RunQuery($qry);

        break;

        

         case "save_product_sub_detail_new":

        $cityid = $_REQUEST['city_id'];

        $product_code = $_REQUEST['product_code'];

        $product_stat = $_REQUEST['product_stat'];

        if ($cityid == 0) {

            //IN ('5','6','7','45','11','9','10','13','12','15','46','47')

            //('45','13','11','12','15','47','46')

           $cityqry = "SELECT  dashboard_city.`city_id`,location.loc_code FROM `location` , dashboard_city WHERE location.`city_id`=dashboard_city.`city_id` AND 

           location.loc_code IN ('5','6','7','45','11','9','10','13','12','15','46','47') ORDER BY location.city_id ASC";

            $citydata = simplexml_load_string($runQ->RunQuery($cityqry));

            foreach ($citydata as $pcity) {

                $dltqry = "DELETE FROM product_sub_detail WHERE product_code = {$_REQUEST['product_code']}  and city_id='{$pcity->city_id}' and product_loc_code='{$pcity->loc_code}'";

                $runQ->RunQuery($dltqry);

                $product_featured_text = '';

                $product_featured_text = htmlspecialchars($_REQUEST['product_featured_text'], ENT_QUOTES);

                  $qry = "INSERT INTO  `product_sub_detail` (`product_loc_code`,`city_id`,`product_ref`,`product_code`,`product_price`,`product_sale_price`,

`product_sale`,`isHamper`,`product_hamper_price`,`product_order_limit`,`product_featured_text`,`product_related`,

`sku_qty`,`product_stat`,`seasonal_product`,`specific_brand`,`product_scoring`,`open_stock`,`product_shipamount`,`hblstatus`,`hblprice`,`hblfeaturedtext`,`product_assortment_type`,

`product_commission`,`product_order_limit_minimum`,`product_price_day2`,`isexpress_allow`)

VALUES( '{$pcity->loc_code}','{$pcity->city_id}','{$_REQUEST['product_ref']}','{$_REQUEST['product_code']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['isHamper']}','{$_REQUEST['hamper_price']}','{$_REQUEST['product_order_limit']}','{$product_featured_text}',

'{$_REQUEST['product_related']}','{$_REQUEST['sku_qty']}',

'{$_REQUEST['product_stat']}','{$_REQUEST['seasonal_product']}','{$_REQUEST['specific_brand']}','{$_REQUEST['product_scoring']}','{$_REQUEST['sub_detail_open_stock']}',

'{$_REQUEST['sub_detail_shipamount']}','{$_REQUEST['product_hbl_status']}','{$_REQUEST['product_hbl_price']}','{$_REQUEST['product_hbl_featured_text']}',

'{$_REQUEST['assortment_type']}','{$_REQUEST['product_commission']}','{$_REQUEST['product_order_limit_minimum']}','{$_REQUEST['product_price_day2']}','{$_REQUEST['isexpress_allow']}')";

                $runQ->RunQuery($qry);

                $qry = "insert into product_status_log (product_code,old_product_price,new_product_price,

old_product_sale,new_product_sale,old_product_sale_price,new_product_sale_price,

old_product_stat,new_product_stat,usrid,datetime,`city_id`,`product_status_loc_code`)

values ('$product_code','{$_REQUEST['product_price_old']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_old']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['product_sale_price_old']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_stat_old']}','{$product_stat}','$usrid',NOW(),'{$pcity->city_id}','{$pcity->loc_code}')";

                $runQ->RunQuery($qry);

            }

        } else {

            $dltqry = "DELETE FROM product_sub_detail WHERE product_code = {$_REQUEST['product_code']} and product_loc_code='{$_REQUEST['city_id']}'";

            /* $dltqry = "UPDATE `product_sub_detail` SET  `product_stat` = 'I',`sub_detail_active_status` = 'I'

  WHERE product_code = {$_REQUEST['product_code']}  and city_id='{$_REQUEST['city_id']}'";*/

            $runQ->RunQuery($dltqry);

            

            $product_featured_text = '';

                $product_featured_text = htmlspecialchars($_REQUEST['product_featured_text'], ENT_QUOTES);

                

             $qry = "INSERT INTO  `product_sub_detail` ( `city_id`,`product_loc_code`,`product_ref`,`product_code`,`product_price`,`product_sale_price`,

`product_sale`,`isHamper`,`product_hamper_price`,`product_order_limit`,`product_featured_text`,`product_related`,

`sku_qty`,`product_stat`,`seasonal_product`,`specific_brand`,`product_scoring`,`open_stock`,`product_shipamount`,`hblstatus`,`hblprice`,`hblfeaturedtext`,`product_assortment_type`,`product_commission`,

`product_order_limit_minimum`,`product_price_day2`,`isexpress_allow`)

VALUES( (SELECT city_id FROM location WHERE loc_code ='{$_REQUEST['city_id']}' LIMIT 1),'{$_REQUEST['city_id']}','{$_REQUEST['product_ref']}','{$_REQUEST['product_code']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['isHamper']}','{$_REQUEST['hamper_price']}','{$_REQUEST['product_order_limit']}','{$product_featured_text}',

'{$_REQUEST['product_related']}','{$_REQUEST['sku_qty']}',

'{$_REQUEST['product_stat']}','{$_REQUEST['seasonal_product']}','{$_REQUEST['specific_brand']}','{$_REQUEST['product_scoring']}','{$_REQUEST['sub_detail_open_stock']}',

'{$_REQUEST['sub_detail_shipamount']}','{$_REQUEST['product_hbl_status']}','{$_REQUEST['product_hbl_price']}','{$_REQUEST['product_hbl_featured_text']}',

'{$_REQUEST['assortment_type']}','{$_REQUEST['product_commission']}','{$_REQUEST['product_order_limit_minimum']}','{$_REQUEST['product_price_day2']}','{$_REQUEST['isexpress_allow']}')";

            $runQ->RunQuery($qry);

             $qry = "insert into product_status_log (product_code,old_product_price,new_product_price,

old_product_sale,new_product_sale,old_product_sale_price,new_product_sale_price,

old_product_stat,new_product_stat,usrid,datetime,`product_status_loc_code`,`city_id`)

values ('$product_code','{$_REQUEST['product_price_old']}','{$_REQUEST['product_price']}','{$_REQUEST['product_sale_old']}',

'{$_REQUEST['product_sale']}','{$_REQUEST['product_sale_price_old']}','{$_REQUEST['product_sale_price']}',

'{$_REQUEST['product_stat_old']}','{$product_stat}','$usrid',NOW(),'{$_REQUEST['city_id']}',(SELECT city_id FROM location WHERE loc_code ='{$_REQUEST['city_id']}' LIMIT 1))";

            $runQ->RunQuery($qry);

        }



        $qry = "SELECT 	product_sub_detail.product_sub_code,	product_sub_detail.city_id,product_sub_detail.product_loc_code,	product_sub_detail.product_ref,

	product_sub_detail.product_code,	product_sub_detail.product_price,	product_sub_detail.product_sale_price,

	product_sub_detail.product_sale,	product_sub_detail.isHamper,	product_sub_detail.product_hamper_price,

		product_sub_detail.product_order_limit,	product_sub_detail.product_featured_text,	product_sub_detail.product_related,

	product_sub_detail.sku_qty,	product_sub_detail.product_stat,	product_sub_detail.seasonal_product,	product_sub_detail.specific_brand,

	product_sub_detail.product_scoring,	dashboard_city.`city_name`,

location.loc_name, product_sub_detail.`open_stock`,product_sub_detail.`product_shipamount`,product_sub_detail.`product_order_limit_minimum` ,product_sub_detail.`product_price_day2`,isexpress_allow

FROM product_sub_detail

	LEFT JOIN dashboard_city ON product_sub_detail.city_id = dashboard_city.city_id

	LEFT JOIN location ON location.`loc_code` = product_sub_detail.`product_loc_code`

	 WHERE product_sub_detail.`sub_detail_active_status` = 'A'  AND  product_sub_detail.`product_code`={$_REQUEST['product_code']} ";

        echo $runQ->RunQuery($qry);

        break;

    /* case "remove_product_sub_detail":

         $id = $_REQUEST['id'];

         $dltqry = "UPDATE `product_sub_detail` SET `product_stat` = 'I', `sub_detail_active_status` = 'I'   WHERE product_code = $product_code and product_ref='$product_ref'

  and city_id='$pcity'";

         $runQ->RunQuery($dltqry);

         break;*/

    case "sub_detail_status_change":

        $sub_detail_code = $_REQUEST['sub_detail_code'];

        $product_stat = $_REQUEST['product_stat'];

        $qry = "UPDATE `product_sub_detail`  SET `product_stat` = '$product_stat' WHERE `product_sub_code` = $sub_detail_code";

        $runQ->RunQuery($qry);

        break;

    case "sub_detail_remove":

        $sub_detail_code = $_REQUEST['sub_detail_code'];

        /* $dltqry = "UPDATE `product_sub_detail` SET  `product_stat` = 'I',`sub_detail_active_status` = 'I'

 WHERE `product_sub_code` = $sub_detail_code";*/

        $dltqry = "DELETE FROM product_sub_detail WHERE `product_sub_code` = $sub_detail_code";

        $runQ->RunQuery($dltqry);

        break;

    case "save_product_category":

        $selectCity = $_REQUEST['city'];

        

        

        if($selectCity=='0'){

                $qry = "select city_id from dashboard_city where city_id IN ('1','2','3','4','5','6','7') ORDER BY city_id ASC";

                $xres = simplexml_load_string($runQ->RunQuery($qry));

                $city_dropdown_html = '';

                foreach ($xres->row as $orders) {

                    $qry = "INSERT INTO product_category (product_code,acno, `city_id`, level_one,level_two,level_three,level_four,level_five,level_six)

                    VALUES( '{$_REQUEST['product_code']}','{$acno}','{$orders->city_id}','{$_REQUEST['level1']}','{$_REQUEST['level2']}','{$_REQUEST['level3']}','{$_REQUEST['level4']}','{$_REQUEST['level5']}','{$_REQUEST['level6']}')";

                    $runQ->RunQuery($qry);

                }

            }else{

                $qry = "INSERT INTO product_category (product_code,acno, `city_id`, level_one,level_two,level_three,level_four,level_five,level_six)

                VALUES( '{$_REQUEST['product_code']}','{$acno}','{$selectCity}','{$_REQUEST['level1']}','{$_REQUEST['level2']}','{$_REQUEST['level3']}','{$_REQUEST['level4']}','{$_REQUEST['level5']}','{$_REQUEST['level6']}')";

                $runQ->RunQuery($qry);

            }

            

        

        $qry = "SELECT product_category.`id`,	dashboard_city.city_name,

								  product_hierarchy_one.`name` AS level_one_name,

								  product_hierarchy_two.`name` AS level_two_name,

								  product_hierarchy_three.`name` AS level_three_name,

								  product_hierarchy_four.`name` AS level_four_name,

								  product_hierarchy_five.`name` AS level_five_name,

								  product_hierarchy_six.`name` AS level_six_name

								FROM

								  product_category

								  LEFT JOIN product_hierarchy_one

								    ON product_hierarchy_one.`id` = product_category.`level_one`

								  LEFT JOIN product_hierarchy_two

								    ON product_hierarchy_two.`id` = product_category.`level_two`

								  LEFT JOIN product_hierarchy_three

								    ON product_hierarchy_three.`id` = product_category.`level_three`

								  LEFT JOIN product_hierarchy_four

								    ON product_hierarchy_four.`id` = product_category.`level_four`

								  LEFT JOIN product_hierarchy_five

								    ON product_hierarchy_five.`id` = product_category.`level_five`

								  LEFT JOIN product_hierarchy_six

								    ON product_hierarchy_six.`id` = product_category.`level_six`

								    LEFT JOIN dashboard_city ON product_category.city_id = dashboard_city.city_id

								    WHERE product_category.`product_code`={$_REQUEST['product_code']}";

        echo $runQ->RunQuery($qry);



        /*add product in theme product sorting () pg Site Content> Category pg. (table theme_custom_product)*/

       /* $category = '0';

        if (isset($_REQUEST['level1']) && $_REQUEST['level1'] != '') {

            $category = $_REQUEST['level1'];

            if (isset($_REQUEST['level2']) && $_REQUEST['level2'] != '') {

                $category .= ',' . $_REQUEST['level2'];

                if (isset($_REQUEST['level3']) && $_REQUEST['level3'] != '') {

                    $category .= ',' . $_REQUEST['level3'];

                    if (isset($_REQUEST['level4']) && $_REQUEST['level4'] != '') {

                        $category .= ',' . $_REQUEST['level4'];

                        if (isset($_REQUEST['level5']) && $_REQUEST['level5'] != '') {

                            $category .= ',' . $_REQUEST['level5'];

                            if (isset($_REQUEST['level6']) && $_REQUEST['level6'] != '') {

                                $category .= ',' . $_REQUEST['level6'];

                            }

                        }

                    }

                }

            }

        }

        $getThemeUstomListQry = "SELECT id,CAST( theme_custom_product.product_codes AS CHAR) AS `product_codes`  FROM `theme_custom_product` where theme_custom_list = '$category'  and related_to!=0";

        $resThemeUstomList = $runQ->RunQuery($getThemeUstomListQry);

        $xress = simplexml_load_string($resThemeUstomList);

        $theme_id = $xress->row->id;

        $product_codes = $xress->row->product_codes;

        $expproductcode = explode(',', $product_codes);

        $flag = 1;

        foreach ($expproductcode as $products) {

            if ($products == $_REQUEST['product_code']) {

                $flag = 0;

                break;

            }

        }

        if ($flag == 1) {

            $newproductscode = $product_codes . ',' . $_REQUEST['product_code'];

            $updateThemeCustomProductListqry = "UPDATE `theme_custom_product`

 SET `product_codes` = '$newproductscode'

 WHERE theme_custom_list = '$category'  AND related_to != 0  AND id = $theme_id";

            $runQ->RunQuery($updateThemeCustomProductListqry);

        }*/

        /*end product in theme product sorting () pg Site Content> Category pg. (table theme_custom_product)*/

        

        

        break;



    case

    "remove_product_category":

        $id = $_REQUEST['id'];





        /*remove product in theme product sorting () pg Site Content> Category pg. (table theme_custom_product)*/

        $ProductCategoryqry = "SELECT * FROM `product_category` WHERE product_category.`id`='$id'";

        $getProductCategory = $runQ->RunQuery($ProductCategoryqry);

        $xress = simplexml_load_string($getProductCategory);

        $level1 = $xress->row->level_one;

        $level2 = $xress->row->level_two;

        $level3 = $xress->row->level_three;

        $level4 = $xress->row->level_four;

        $level5 = $xress->row->level_five;

        $level6 = $xress->row->level_six;

        $product_code = $xress->row->product_code;



        $category = '0';

        if (isset($level1) && $level1 != '' && $level1 != 0) {

            $category = $level1;

            if (isset($level2) && $level2 != '' && $level2 != 0) {

                $category .= ',' . $level2;

                if (isset($level3) && $level3 != '' && $level3 != 0) {

                    $category .= ',' . $level3;

                    if (isset($level4) && $level4 != '' && $level4 != 0) {

                        $category .= ',' . $level4;

                        if (isset($level5) && $level5 != '' && $level5 != 0) {

                            $category .= ',' . $level5;

                            if (isset($level6) && $level6 != '' && $level6 != 0) {

                                $category .= ',' . $level6;

                            }

                        }

                    }

                }

            }

        }

        $getThemeUstomListQry = "SELECT id,CAST( theme_custom_product.product_codes AS CHAR) AS `product_codes`  FROM `theme_custom_product` where theme_custom_list = '$category'  and related_to!=0";

        $resThemeUstomList = $runQ->RunQuery($getThemeUstomListQry);

        $xress = simplexml_load_string($resThemeUstomList);

        $theme_id = $xress->row->id;

        $product_codes = $xress->row->product_codes;

        $expproductcode = explode(',', $product_codes);

        $newproductscode = '';

        foreach ($expproductcode as $key => $products) {

            if ($products == $product_code) {

            } else {

                if ($key == 0) {

                    $newproductscode .= $products;

                } else {

                    $newproductscode .= ',' . $products;

                }



            }

        }

        $updateThemeCustomProductListqry = "UPDATE `theme_custom_product`

SET `product_codes` = '$newproductscode'

WHERE theme_custom_list = '$category'  AND related_to != 0  AND id = $theme_id";

        $runQ->RunQuery($updateThemeCustomProductListqry);

        /*end remove product in theme product sorting () pg Site Content> Category pg. (table theme_custom_product)*/



        $qry = "DELETE FROM product_category WHERE id = '$id'";

        echo $runQ->RunQuery($qry);

        break;

        

        case 'save_deep_link':

        $code = $_REQUEST['code'];

        $level = $_REQUEST['level'];

        $type = $_REQUEST['assortmenttype'];

        $pagetype = $_REQUEST['pagetype'];

        $city = $_REQUEST['city'];

        

        if($level == 2){

			  $sql_categories = "SELECT IFNULL(count(`product_hierarchy_three`.`id`), 0 ) AS idcount FROM

	          `product_hierarchy_three` LEFT JOIN product_category_city ON product_hierarchy_three.id = product_category_city.category_id

									   WHERE level_id_two = '".$code."'

									    AND product_category_city.city_id = '".$city."'

									   	AND product_category_city.`status` = 'A'

									   	AND product_category_city.`level` = 3 and product_hierarchy_three.type = '".$type."'";

										$ressql_categories = $runQ->RunQuery($sql_categories);

                                        $xress =  simplexml_load_string($ressql_categories);

                                        $catcount = $xress->row->idcount;

			  }

              if($level == 3){

			  $sql_categories = "SELECT IFNULL( count(`product_hierarchy_four`.`id`), 0 ) AS idcount FROM

	          `product_hierarchy_four` LEFT JOIN product_category_city ON product_hierarchy_four.id = product_category_city.category_id

									   WHERE level_three_id = '".$code."'

									   AND product_category_city.city_id = '".$city."'

									   	AND product_category_city.`status` = 'A'

									   	AND product_category_city.`level` = 4  and product_hierarchy_four.type = '".$type."'";

					                     $ressql_categories = $runQ->RunQuery($sql_categories);

                                        $xress =  simplexml_load_string($ressql_categories);

                                        $catcount = $xress->row->idcount;

			  

			  }

              if($level == 4){

			  $sql_categories = "SELECT IFNULL( count(`product_hierarchy_five`.`id`), 0 ) AS idcount FROM

	          `product_hierarchy_five` LEFT JOIN product_category_city ON product_hierarchy_five.id = product_category_city.category_id

									   WHERE level_four_id = '".$code."'

									   AND product_category_city.city_id = '".$city."'

									   	AND product_category_city.`status` = 'A'

									   	AND product_category_city.`level` = 5  and product_hierarchy_five.type = '".$type."'";

					                     $ressql_categories = $runQ->RunQuery($sql_categories);

                                        $xress =  simplexml_load_string($ressql_categories);

                                        $catcount = $xress->row->idcount;

			  

			  }

			  

		

		

        

        $qry_cat = "insert into deeplinkdata (`code`,`level`,`type`,`pagetype`,`usrid`,`city_id`,catcount)

        values ('$code', '$level','$type','$pagetype','$usrid','$city','$catcount')";

        if ($last_insert_group_id = $runQ->LastInsertID($qry_cat)){

        

        if($pagetype=='Detail'){

         $getlastcatname = "SELECT 

        CASE

        WHEN product_category.`level_four` != 0 

        THEN (SELECT `product_hierarchy_four`.`name` FROM `product_hierarchy_four` WHERE `product_hierarchy_four`.`id`=product_category.`level_four` LIMIT 1) 

        WHEN product_category.`level_three` != 0 

        THEN (SELECT `product_hierarchy_three`.`name` FROM `product_hierarchy_three` WHERE `product_hierarchy_three`.`id`=product_category.`level_three` LIMIT 1) 

        WHEN product_category.`level_two` != 0 

        THEN (SELECT `product_hierarchy_two`.`name` FROM `product_hierarchy_two` WHERE `product_hierarchy_two`.`id`=product_category.`level_two` LIMIT 1) 

        WHEN product_category.`level_one` != 0 

        THEN (SELECT `product_hierarchy_one`.`name` FROM `product_hierarchy_one` WHERE `product_hierarchy_one`.`id`=product_category.`level_one` LIMIT 1)  

        END AS cat_name 

        FROM

        `product_category` 

        WHERE product_category.product_code = '{$code}'

        AND product_category.`city_id`='{$city}' AND product_category.`level_one` NOT IN ('24','59') LIMIT 1";

        $reslastcatname = $runQ->RunQuery($getlastcatname);

        $xresslastcatname = simplexml_load_string($reslastcatname);

        $xresslastcatname->row->cat_name;

                

                 echo $last_insert_group_id.'_'.rawurlencode($xresslastcatname->row->cat_name);

            }else{

                 echo $last_insert_group_id.'&catcount='.$catcount;

            }

       

        }

    

        return;

        break;

        

        

        case 'save_allow_review':

        $code = $_REQUEST['id'];

        $value = $_REQUEST['value'];

        $qry = "update product_hierarchy_two set allow_review='{$value}' where id='{$code}' ";

        echo $q = $runQ->RunQuery($qry);

    

        return;

        break;

		

		case 'saveproductdesc' :



        $product_ref_list = trim($_REQUEST['product_ref_list']);

        $desc_type = trim($_REQUEST['desc_type']);

        $product_desc = str_replace("'", '"', $_REQUEST['product_desc']);

        //$prolidt = explode(',',$product_ref_list);

        //foreach($prolidt as $row){

        //echo $row;

        if($desc_type == 'new'){

        $qry = "update product set product_desc = '$product_desc' where product_ref IN(".$product_ref_list.")";

        }else{

        $qry = "update product set product_desc = CONCAT(product_desc,'','$product_desc') where product_ref IN(".$product_ref_list.")";   

        }

        $res = $runQ->RunQuery($qry);

        //echo $qry;

        //}

       

       



       if($res == 1){

            echo 1;

		}

	     else {

            echo 0 ;

		}

        return;

        break;



    default :

        return;

}

?>