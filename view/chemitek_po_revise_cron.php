<?php
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");

	/*$sel=$dbcon->query("SELECT * FROM `tbl_purchaseordertrn` WHERE `purchaseorder_id` IN (14,96,93,127,137,152,168,179,179,209,210,119,291,180,80,7,181,359) and po_ref_id !=''");

	while($row = mysqli_fetch_array($sel) ){
		
		$info['po_ref_id'] 		= $row['po_ref_id'];
		$info['temptrn_ref_id']	= $row['temptrn_ref_id'];
		
		$updateid = update_record('tbl_purchaseordertrn', $info,"prev_purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);

		$res=$dbcon->query("SELECT purchaseordertrn_id FROM `tbl_purchaseordertrn` WHERE `prev_purchaseordertrn_id` = ".$row['purchaseordertrn_id']);

		$row1 = mysqli_fetch_array($res);

		$info1['purchaseordertrn_id'] = $row1['purchaseordertrn_id'];
		$updateid1 = update_record('tbl_purchaseorder_req_trn', $info1,"purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);
	}*/
	
	$update_req_trn = $dbcon->query("UPDATE `tbl_purchaseorder_req_trn` SET `purchaseordertrn_id`=819 WHERE `purchaseordertrn_id`=806");

	$update_req_trn = $dbcon->query("UPDATE `tbl_purchaseorder_req_trn` SET `purchaseordertrn_id`=820 WHERE `purchaseordertrn_id`=807");

	$sel=$dbcon->query("SELECT gstr.*,preq.used_qty,preq.rp_id as work_order_id FROM `tbl_grn_sub_trn` as gstr
	left join tbl_purchaseordertrn as ptr on ptr.purchaseordertrn_id = gstr.purchaseordertrn_id
	left join tbl_purchaseorder_req_trn as preq on preq.purchaseordertrn_id=ptr.purchaseordertrn_id
	WHERE preq.purchaseordertrn_req_status=0 and `grn_trn_id` IN (510,544,545,576,1208,1209,1233,1234,1235,1248,1250,1252) and gstr.product_qty !=''");
	
	while($row2 = brp_mysqli_fetch_array($sel) ){
		//	echo "<pre>"; print_r($row2); echo "</pre>";
		if(number_format($row2['product_qty'],4,".","")==number_format($row2['used_qty'],4,".","")){
			$info2['rp_id']	= $row2['work_order_id'];
			$updateid1 = update_record('tbl_grn_sub_trn', $info2,"grn_trn_sub_id=".$row['grn_trn_sub_id'] , $dbcon);
		}else{
			if(number_format($row2['product_qty'],4,".","")>number_format($row2['used_qty'],4,".","")){
				
				$sub_q = "select product_qty,product_conv_qty from tbl_grn_sub_trn where grn_trn_sub_id=".$row2['grn_trn_sub_id'];
				$sur_q = $dbcon->query($sub_q); 
				$row3=brp_mysqli_fetch_array($sur_q);

				$info5['product_id']				= $row2['product_id'];
				$info5['grn_trn_id']				= $row2['grn_trn_id'];
				$info5['purchaseordertrn_id']		= $row2['purchaseordertrn_id'];
				$info5['jobwork_id']				= $row2['jobwork_id'];
				$info5['job_work_trn_id']			= $row2['job_work_trn_id'];
				$info5['job_work_sub_trn_id']		= $row2['job_work_sub_trn_id'];
				$info5['process_allocate_id']		= $row2['process_allocate_id'];
				$info5['product_qty']				= $row2['used_qty'];
				$info5['product_stock_used_qty']	= $row2['product_stock_used_qty'];
				$info5['product_base_unit']			= $row2['product_base_unit'];
				$info5['product_conv_qty']			= $row2['used_qty'];
				$info5['product_conv_unit']			= $row2['product_conv_unit'];
				$info5['status']					= $row2['status'];
				$info5['cdate']						= $row2['cdate'];
				$info5['user_id']					= $row2['user_id'];
				$info5['company_id']				= $row2['company_id'];
				$info5['branch_id']					= $row2['branch_id'];
				$info5['purchase_status']			= $row2['purchase_status'];
				$info5['purchase_qty']				= $row2['purchase_qty'];
				$info5['job_work_po_trn_id']		= $row2['job_work_po_trn_id'];
				$info5['returnable_trn_id']			= $row2['returnable_trn_id'];
				$info5['customer_id']				= $row2['customer_id'];
				$info5['product_scrap_id']			= $row2['product_scrap_id'];
				$info5['scrap_unit']				= $row2['scrap_unit'];
				$info5['scrap_qty']					= $row2['scrap_qty'];
				$info5['rp_id']						= $row2['work_order_id'];	
				$info5['product_process_rate']		= $row2['product_process_rate'];
				$info5['product_process_unit']		= $row2['product_process_unit'];
				$info5['material_rate']				= $row2['material_rate'];
				$info5['process_pus_material_rate']	= $row2['process_pus_material_rate'];
				$info5['material_conv_rate']		= $row2['material_conv_rate'];
				$info5['process_pus_material_conv_rate']= $row2['process_pus_material_conv_rate'];
				$info5['total_process_rate']		= $row2['total_process_rate'];
				$info5['total_process_conv_rate']	= $row2['total_process_conv_rate'];
				$info5['stock_transfer_id']			= $row2['stock_transfer_id'];	
				$info5['stock_transfer_trn_id']		= $row2['stock_transfer_trn_id'];
				
				$insersubgrn = add_record('tbl_grn_sub_trn', $info5, $dbcon);

				$info4['product_qty']		= $row3['product_qty']-$row2['used_qty'];
				$info4['product_conv_qty']	= $row3['product_qty']-$row2['used_qty'];
				$updateid1 = update_record('tbl_grn_sub_trn', $info4,"grn_trn_sub_id=".$row2['grn_trn_sub_id'] , $dbcon);
			}else{
				if(number_format($row2['product_qty'],4,".","")<number_format($row2['used_qty'],4,".","")){
					$info3['rp_id']	= $row2['work_order_id'];
					$updateid1 = update_record('tbl_grn_sub_trn', $info3,"grn_trn_sub_id=".$row['grn_trn_sub_id'] , $dbcon);
				}else{
					/*$info4['product_qty']		= $row2['product_qty']-$row2['used_qty'];
					//$info4['product_conv_qty']	= $row2['product_conv_qty']-$gstr_conv_qty;

					
					$updateid1 = update_record('tbl_grn_sub_trn', $info4,"grn_trn_sub_id=".$row['grn_trn_sub_id'] , $dbcon);*/
				}

			}
		}
	}

	if($updateid1){
		echo "Cron Run SuccessFully.....!!!!";
	}
?>