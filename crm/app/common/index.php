<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode'])== "has_quotation") {
        if($POST['inquiry_id']){
            $quotation_id = check_has_quotation($dbcon,$POST['inquiry_id']);
        }
        echo ($quotation_id) ? $quotation_id : 0;
}
else if(strtolower($POST['mode'])== "has_product") {
        $products = get_inquiry_products($dbcon,$POST['inquiry_id']);
        echo ($products) ? json_encode($products) : 0;
}

else if(strtolower($POST['mode'])== "load_inquiry_data"){
    $inq_qry = "select * from tbl_inquiry where inquiry_id =".$POST['inquiry_id'];
    $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
    echo json_encode($inq_data);
}
?>