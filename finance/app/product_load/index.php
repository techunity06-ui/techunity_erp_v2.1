<?php
session_start(); //start session
ini_set('memory_limit', '-1');
set_time_limit(0);
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "product_load") {
    $drawing_number = '';
    $item_code = '';
    $alias = '';
    $whr = '';

    $companyConfiguration=getCompanyConfiguration($dbcon);
    //var_dump($companyConfiguration);
    $so_pro_type=$companyConfiguration['so_pro_type'];
    $sales_pro_search=$companyConfiguration['sales_pro_search'];
    //var_dump($sales_pro_search);

   
    //var_dump($POST['product_type_sel']);
    if($POST['product_type_sel'] != ""){
        $whr .= " and product_type = " . $POST['product_type_sel'];
    }else{
        $whr=' and pro.product_type in('.$so_pro_type.')';
    }
    
    if(isset($POST['product_category']) && $POST['product_category'] != ""){
        $whr .= " and pro.product_category = ". $POST['product_category'];
    }
    
    $pro_search=explode(",", $sales_pro_search);
    // /echo"<pre>";print_r($pro_search);echo"</pre>";
    //var_dump($search);

   
    $query = "SELECT pro.product_id,pro.product_name,pro.product_icode, dr.drawing_number, pro.product_alias_name FROM product_mst as pro left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id where product_status=0 ".$whr;
   
	
    $result=$dbcon->query($query);
	// echo "<pre>"; var_dump($crm_pro_type);var_dump($query); die();
    $i=0;
    while($row=mysqli_fetch_array($result)){
		
        if(in_array('drawing',$pro_search)){
            $drawing_number = " -- (".$row['drawing_number'].")";
        }
        if(in_array('item',$pro_search)){
            $item_code = " -- (".$row['product_icode'].")";
        }
        if(in_array('alias',$pro_search)){
            $alias = " -- (".$row['product_alias_name'].")";
        }
        $row1[0][]=$row['product_id'];
        $row1[1][]=$row['product_name'].' '.$item_code.' '.$drawing_number.' '.$alias;
    }
	//$row=mysqli_fetch_array($result);		
    // print_r($POST);
	//echo "<pre>"; print_r($row1); die();
    echo json_encode($row1); //die();
}
?>