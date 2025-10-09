<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	$qry1="select * from tbl_reserve_stock where cron_status=0 and stock_status !=2 and request_id > 0";
	$result1=$dbcon->query($qry1);
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
           echo $qry2="select perent_id from tbl_request_product where rp_id= " . $rel['request_id'];
		   echo "<br>";		   ;
			$result2=$dbcon->query($qry2);

			if(brp_mysqli_num_rows($result2) > 0){
		    	$rel2=brp_mysqli_fetch_assoc($result2);

		    echo	$qry3="select p_id  from tbl_allocate_process where p_status != 2 and previous_process_id = 0 and process_priority = 1 and p_ref_id =" . $rel2['perent_id'];
echo "<br>";		   			
			$result3=$dbcon->query($qry3);

				if(brp_mysqli_num_rows($result3) > 0){
			    	$rel3=brp_mysqli_fetch_assoc($result3);

			    	$info = array();
			    	$info['p_id'] = $rel3['p_id'];
					$info['cron_status'] = 1;
			    	$updateid=update_record("tbl_reserve_stock", $info, "reserve_id=".$rel['reserve_id'], $dbcon);
			    }
		    }
        }
    }else{
        echo "no records found ::";
    }
	


?>
