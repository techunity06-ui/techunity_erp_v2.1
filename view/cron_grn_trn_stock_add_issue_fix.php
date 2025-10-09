<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="SELECT * FROM tbl_stock_trn WHERE stock_flage = 1 AND ref_name = 'tbl_grn_trn' AND stock_status != 2 and base_unit != convert_unit and stock_id > 8927";
	$result1=$dbcon->query($qry1);
    if(brp_mysqli_num_rows($result1) > 0){
        while($rel=brp_mysqli_fetch_assoc($result1)){
            
            $grn_qry = "SELECT * FROM tbl_grn_trn WHERE grn_trn_id = " . $rel['ref_id'];
            $res_grn = $dbcon->query($grn_qry);
           
           	$grn_rw=brp_mysqli_fetch_assoc($res_grn);
           	$info = array();

           	$info['base_stock'] = $grn_rw['product_qty'];
           	$info['convert_stock'] = $grn_rw['product_conv_qty'];

           	if($rel['used_base_stock'] != "" && $rel['used_base_stock'] > 0){
				$info['used_base_stock'] = $grn_rw['product_qty'];
           		$info['used_convert_stock'] = $grn_rw['product_conv_qty'];           		
           	}

           $updateid=update_record('tbl_stock_trn',$info,'stock_id = ' .$rel['stock_id'], $dbcon);


           $reserve_qry = "SELECT * FROM tbl_reserve_stock WHERE stock_flage = 1 and stock_status !=2 AND stock_id = " .$rel['stock_id'];

           $reserve_result = $dbcon->query($reserve_qry);
           $cnt_res = brp_mysqli_num_rows($reserve_result);
           if($cnt_res > 0){
           	$res_info = array();
           		if($cnt_res > 1){
           			echo "stock id = " . $rel['stock_id'] . "</br></br>";
           		}else {
           			$res_rw = brp_mysqli_fetch_assoc($reserve_result);

           			$res_info['base_stock'] = $grn_rw['product_qty'];
		           	$res_info['convert_stock'] = $grn_rw['product_conv_qty'];

		           	if($rel['approve_base_stock'] != "" && $rel['approve_base_stock'] > 0){
						$res_info['approve_base_stock'] = $grn_rw['product_qty'];
		           		$res_info['approve_convert_stock'] = $grn_rw['product_conv_qty'];           
		           	}
		           	 $updateid=update_record('tbl_reserve_stock',$res_info,'reserve_id = ' .$res_rw['reserve_id'], $dbcon);
           		}
           }	
        }    
    }else{
        echo "no records found ::";
    }
	


?>
