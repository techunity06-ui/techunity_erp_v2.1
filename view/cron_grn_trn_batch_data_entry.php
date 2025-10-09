<?php

session_start(); //start session
$AJAX = true;
//error_reporting(E_ALL); ini_set('display_errors', '1');
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");

 $qry1="select trn.*,grn.grn_no,grn.grn_date from tbl_grn_trn as trn
	left join tbl_grn as grn on grn.grn_id = trn.grn_id
	 where trn.grn_trn_status = 0";
    $result1=$dbcon->query($qry1);
    while($grn_rw=brp_mysqli_fetch_assoc($result1)){
       
        $qry2="select batch_id from tbl_batch_data where status = 0 and grn_trn_id = " . $grn_rw['grn_trn_id'];
        $result2=$dbcon->query($qry2);

        if(brp_mysqli_num_rows($result2) > 0){
        	// echo $qry2;
        }else{
        	// echo $qry2;
        	$info = array();
            $info['grn_id'] = $grn_rw['grn_id'];
			$info['grn_trn_id'] = $grn_rw['grn_trn_id'];
			$info['order_no'] = $grn_rw['grn_no'];
			$info['grn_date'] = $grn_rw['grn_date'];

			if($grn_rw['unit_id']  == $grn_rw['product_conv_unit']){
				$info['batch_unit'] = $grn_rw['unit_id'];
				$info['accept_qty'] = $grn_rw['product_qty'];
				$info['base_qty'] = $grn_rw['product_qty'];
				$info['base_unit'] = $grn_rw['unit_id'];
				$info['conv_qty'] = $grn_rw['product_qty'];
				$info['conv_unit'] = $grn_rw['unit_id'];
				$info['qc_qty'] = $grn_rw['product_qty'];
				$info['grn_accept_qty'] = $grn_rw['product_qty'];
			}else{
				$info['batch_unit'] = $grn_rw['product_conv_unit'];
				$info['accept_qty'] = $grn_rw['product_conv_qty'];
				$info['base_qty'] = $grn_rw['product_qty'];
				$info['base_unit'] = $grn_rw['unit_id'];
				$info['conv_qty'] = $grn_rw['product_conv_qty'];
				$info['conv_unit'] = $grn_rw['product_conv_unit'];
				$info['qc_qty'] = $grn_rw['product_conv_qty'];
				$info['grn_accept_qty'] = $grn_rw['product_conv_qty'];
					
			}
			$info['cdate'] = $grn_rw['cdate'];
			$info['user_id'] = $grn_rw['user_id'];
			$info['company_id'] = $grn_rw['company_id'];
			$info['branch_id'] = $grn_rw['branch_id'];
			$info['qc_status'] = 1;
			
			$info['product_id'] = $grn_rw['product_id'];
			
			
			$info['process_id'] = $grn_rw['process_id'];
			$info['customer_id'] = $grn_rw['customer_id'];
			$info['grn_godown'] = $grn_rw['grn_godown'];
			
			$info['purchaseordertrn_id'] = $grn_rw['purchaseordertrn_id'];
			$batch_id = add_record('tbl_batch_data',$info, $dbcon);		

			echo "</br>" . $batch_id;
        }
    }



?>