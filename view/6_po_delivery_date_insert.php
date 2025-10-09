<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");

	$query = "select potrn.purchaseordertrn_id,po.purchaseorder_due_date,potrn.unit_id,potrn.conv_unit_id,potrn.rate_unit,potrn.product_qty,potrn.product_conv_qty,potrn.product_rate,potrn.product_amount,potrn.used_status from tbl_purchaseordertrn as potrn 
	left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id
	where potrn.purchaseorder_id !=0 and potrn.purchaseordertrn_status=0 and po.delivery_type='po_wise'";
	
	$rs_que = $dbcon->query($query);
	while($row = mysqli_fetch_array($rs_que)){
		$query_deli = "select * from tbl_purchaseorder_delivery_date where purchaseordertrn_id=".$row['purchaseordertrn_id'];
		$rs_deli = $dbcon->query($query_deli);
		if(mysqli_num_rows($rs_deli)>0){
			
		}else{
			$total = $row['product_rate'] * $row['product_qty'];
			if(number_format($total, 2, '.', '') != $row['product_amount']){
				$sqty = $row['product_conv_qty'];
				$unit = $row['conv_unit_id'];
			}else{
				$sqty = $row['product_qty'];
				$unit = $row['unit_id'];
			}
			
			$info['purchaseordertrn_id'] = $row['purchaseordertrn_id'];			
			$info['delivery_date'] 		 = $row['purchaseorder_due_date'];			
			$info['product_qty'] 		 = $sqty;			
			$info['unit_id'] 			 = $unit;	
			$info['grn_status']			 = $row['used_status'];
			$info['user_id'] 			 = $_SESSION['user_id'];			
			$info['cdate'] 				 = date("Y-m-d H:i:s");			
			$info['company_id'] 		 = $_SESSION['company_id'];
			$inser_del = add_record('tbl_purchaseorder_delivery_date', $info, $dbcon, $branch_id);			
		}
	}
?>