<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	$qry1="select p_id from tbl_allocate_process where p_status!=2 and process_priority = 1 and previous_process_id=0 and p_id not in (select p_id from tbl_store_release_material_trn)";
	$result1=$dbcon->query($qry1);
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
            $qry2="select * FROM tbl_store_release where p_id = " . $rel['p_id'];
			$result2=$dbcon->query($qry2);
    		if(brp_mysqli_num_rows($result2) > 0){
		        while($rel1=brp_mysqli_fetch_assoc($result2)){
		        	$info_material = array();

		        	$rp_qry = "SELECT * FROM tbl_request_product WHERE status !=2 AND rp_id = " . $rel1['rp_id'];
		        	$rp_result = $dbcon->query($rp_qry);
		            $rp_rw = brp_mysqli_fetch_assoc($rp_result);
		            	$info_material['release_id'] = $rel1['release_id'];
						$info_material['p_id'] = $rel['p_id'];
						$info_material['product_id'] = $rp_rw['rp_pid'];
						$info_material['process_id'] = $rel1['process_id'];
						
						$info_material['request_qty'] = $rp_rw['rp_req_qty'];

						$info_material['release_qty'] = $rel1['release_qty'] * $rp_rw['req_qty_one'];
						$info_material['release_unit'] = $rp_rw['process_unit'];
						$info_material['release_conv_qty'] = convert_stock($dbcon, $info_material['release_qty'],  $rp_rw['rp_p_id'], "conv_unit");
						$info_material['release_conv_unit'] = $rp_rw['purchase_unit'];
						$info_material['cdate']		= date("Y-m-d H:i:s");
						$info_material['user_id']	= $_SESSION['user_id'];
						$info_material['company_id']	= $_SESSION['company_id'];
						$info_material['branch_id']	= $rel1['branch_id'];

						$m_req_id = add_record('tbl_store_release_material_trn',$info_material, $dbcon);
		        }    
		    }        
        }    
    }else{
        echo "no records found ::";
    }

?>
