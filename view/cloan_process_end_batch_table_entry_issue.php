<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="select grn.grn_trn_id,ap.batch_id,grn.product_id,gnr.grn_no,grn.grn_id,grn.product_conv_unit,grn.unit_id,grn.product_conv_qty,grn.product_qty,grn.rate_unit,grn.process_id,grn.product_qc,grn.grn_godown,grn.branch_id from tbl_grn_trn as grn
	 		left join tbl_batch_data as ap on ap.grn_trn_id=grn.grn_trn_id
	 		left join tbl_grn as gnr on gnr.grn_id=grn.grn_id
			where grn.grn_trn_status!=2";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		if(empty($rel['batch_id'])){
			var_dump($rel['grn_trn_id']);
			$product_id=$rel['product_id'];
			$grn_trn_id=$rel['grn_trn_id'];
			$grn_no=$rel['grn_no'];
			$grn_id=$rel['grn_id'];
			$grn_conv_unit=$rel['product_conv_unit'];
			$rate_unit=$rel['rate_unit'];
			$grn_base_qty = floatval($rel['product_qty']);
			$grn_base_unit = $rel['unit_id'];

			$grn_conv_qty = floatval($rel['product_conv_qty']);

			$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id,$process_id);
			
			$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $grn_no."' and grn_id = " . $grn_id." and grn_trn_id=".$grn_trn_id;
			$res12=mysqli_fetch_assoc($dbcon->query($qry12));
	
			$batch_qty = floatval($res12['qty']);
			
			if($rel['product_conv_unit']==$rel['rate_unit']){
				$remaining_qty = $grn_conv_qty-$batch_qty;
			}else{
				$remaining_qty = $grn_base_qty-$batch_qty;
			}

			if($grn_conv_unit==$rate_unit){
				$type="base_unit";
				$conv_qty=$remaining_qty;
				$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
					
				}else{
					$type="conv_unit";
					$base_qty=$remaining_qty;
					$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
				}

				$batch_qty=$base_qty;
				$batch_conv_qty=$conv_qty;


				$pro_qry= "select * from product_mst where product_id = " . $product_id;
					$pro_result=$dbcon->query($pro_qry);
					$pro_res=brp_mysqli_fetch_assoc($pro_result);

					if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' && $companyConfiguration['batch_process'] == '0') {
						// var_dump(1);
						if($batch_no == ""){
							$batch_no = get_batch_no($dbcon,$product_id);	
						}

					}else if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '0' && $companyConfiguration['batch_process'] == '0') {
						// var_dump(2);
						$batch_no = $batch_man_no;
					}
					else if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' && $companyConfiguration['batch_process'] == '1') {
						if($batch_no == ""){
							$batch_no = get_batch_no($dbcon,$product_id);	
						}
						// var_dump(3);
					}
					else if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '0' && $companyConfiguration['batch_process'] == '1') {
						// var_dump(4);
						$batch_no = $batch_man_no;
					}else{
						// var_dump(5);
						$batch_no = "";
					}

					if ($pro_res['batch_wise_stock_manage'] == '0') {
						$batch_no = "";	
					}

					$batch_info['grn_id']			= $grn_id;	
					$batch_info['grn_trn_id']		= $grn_trn_id;	
					$batch_info['batch_no']			= $batch_no;
					$batch_info['batch_qty']		= $remaining_qty;
					$batch_info['order_no']			= $grn_no;
					$batch_info['product_id']		= $product_id;
					$batch_info['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info['production_type']	= '1';			
					$batch_info['status']			= '0';

					$batch_info['qc_status']		= $rel['product_qc'];
					if($qc_paramter_info==0){
						$batch_info['accept_qty']	= $remaining_qty;
						$batch_info['grn_accept_qty']	= $remaining_qty;
						$batch_info['qc_qty']		= $remaining_qty;

					}

					$batch_info['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info['user_id']			= $_SESSION['user_id'];
					$batch_info['company_id']		= $_SESSION['company_id'];	
					$batch_info['branch_id']		= $rel['branch_id'];
					$batch_info['batch_unit']		= $rate_unit;
					$batch_info['base_qty']			= $batch_qty;
					$batch_info['base_unit']		= $grn_base_unit;
					$batch_info['conv_qty']			= $batch_conv_qty;
					$batch_info['conv_unit']		= $grn_conv_unit;
					$batch_info['process_id']		= $process_id;
					$batch_info['grn_godown']		= $rel['grn_godown'];
					$batch_info['auto_store_relese']= 0;
					var_dump($remaining_qty);
					//var_dump($batch_info);
					if($remaining_qty >  0){
				
					$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
						if($batch_gen_id){
							if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
								update_batch_no($dbcon,$product_id);

							}
						}						
					}

					var_dump($batch_gen_id);
					var_dump("New Entry");
			
		}
		
			
	}
				

?>
