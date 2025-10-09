<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	$qry1="select * from  tbl_invoicetrn where cron_status_total=0 and trancation_status =0";
	$result1=$dbcon->query($qry1);
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
        	$orange = 0;
        	$trading = 0;
        	$mfg = 0;
        	$repairing = 0;
        	$other = 0;
        	$qty = $rel['product_qty'];

        	$info = array();

        	if(!empty($rel['orange']) && $rel['orange'] > 0){
        		$info['orange_total'] = $rel['orange'] *  $qty;
        	}

        	if(!empty($rel['trading']) && $rel['trading'] > 0){
        		$info['trading_total'] = $rel['trading'] *  $qty;
        	}

        	if(!empty($rel['mfg']) && $rel['mfg'] > 0){
        		$info['mfg_total'] = $rel['mfg'] *  $qty;
        	}

        	if(!empty($rel['repairing']) && $rel['repairing'] > 0){
        		$info['repairing_total'] = $rel['repairing'] *  $qty;
        	}

        	if(!empty($rel['other']) && $rel['other'] > 0){
        		$info['other_total'] = $rel['other'] *  $qty;
        	}


        	$info['cron_status_total'] = 1;
        	$updateid=update_record("tbl_invoicetrn", $info, "trancation_id=".$rel['trancation_id'], $dbcon);

        }
    }else{
        echo "no records found ::";
    }
	


?>
