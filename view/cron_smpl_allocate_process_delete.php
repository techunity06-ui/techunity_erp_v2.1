<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="select rp_id from tbl_request_product where status=0 and main_request = 1 and rp_po_qty > 0 and indent_status = 3";
	$result1=$dbcon->query($qry1);
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
            
            $info_req['p_status']        = 2;
            
            $updateid=update_record("tbl_allocate_process", $info_req,"p_ref_id=".$rel['rp_id'] , $dbcon); 
           
        }    
    }else{
        echo "no records found ::";
    }
	


?>
