<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="select * from tbl_request_product where status=0 and job_card_status = 0 and job_card_no='' and main_request = 1 and in_process_qty > 0 AND job_card_date='0000-00-00'";
	$result1=$dbcon->query($qry1);
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
            
            $info_req['job_card_status']        = 1;
            $info_req['job_card_no']            = load_common_no($dbcon,JOBCARD);;
            $info_req['job_card_date']      = date('Y-m-d');

            $updateid=update_record("tbl_request_product", $info_req,"rp_id=".$rel['rp_id'] , $dbcon); 
            echo "rp_id :: " . $rel['rp_id'];
            echo " </br></br>"; 
            if($updateid){
                update_common_no($dbcon,JOBCARD);
            }
        }    
    }else{
        echo "no records found ::";
    }
	


?>
