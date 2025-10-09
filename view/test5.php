<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
		$qry2="SELECT * FROM `tbl_grn_trn` as potrn
				left join tbl_grn as grn on grn.grn_id=potrn.grn_id
				WHERE potrn.po_ref_id!=0 and ref_type=2";
		$result2=$dbcon->query($qry2);
		while($rel1=brp_mysqli_fetch_assoc($result2))
		{
			
			$qry4="SELECT * FROM `tbl_grn_sub_trn` as g_trn
					left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id=g_trn.purchaseordertrn_id
					WHERE grn_trn_id=".$rel1['grn_trn_id'];
			$result4=$dbcon->query($qry4);
			while($rel4=brp_mysqli_fetch_assoc($result4))
			{
				$qry_up11="UPDATE `tbl_purchaseorder` SET `status` = '2' WHERE purchaseorder_id=".$rel4['purchaseorder_id'];
				$result_up11=$dbcon->query($qry_up11);
				
				$qry_up12="UPDATE `tbl_purchaseordertrn` SET `purchaseordertrn_status` = '2' WHERE purchaseordertrn_id=".$rel4['purchaseordertrn_id'];
				$result_up12=$dbcon->query($qry_up12);
				
				$qry_up12="UPDATE `tbl_tax_trn` SET `tx_status` = '2' WHERE tx_transaction_type='purchase_order' and tx_tran_type_id=".$rel4['purchaseordertrn_id'];
				$result_up12=$dbcon->query($qry_up12);
			}
				
			
					
				$qry_up1="UPDATE `tbl_grn_sub_trn` SET `status` = '2' WHERE `grn_trn_id` = ".$rel1['grn_trn_id'];
				$result_up1=$dbcon->query($qry_up1);
				
				$qry_up="UPDATE `tbl_grn` SET `grn_status` = '2' WHERE `grn_id` =".$rel1['grn_id'];
				$result_up=$dbcon->query($qry_up);
				
				$qry_up2="UPDATE `tbl_grn_trn` SET `grn_trn_status` = '2' WHERE `grn_trn_id` =".$rel1['grn_trn_id'];
				$result_up2=$dbcon->query($qry_up2);
				
				$qry_up3="UPDATE `tbl_stock_trn` SET `stock_status` = '2' WHERE ref_name='tbl_grn_trn' and ref_id=".$rel1['grn_trn_id'];
				$result_up3=$dbcon->query($qry_up3);
				
				$qry3="SELECT * FROM `tbl_po_grn_used` as po_g
				left join tbl_potrancation as po_trn on po_trn.potrancation_id=po_g.grn_id
				left join tbl_pono as po on po.po_id=po_trn.po_id
				WHERE grn_trn_id=".$rel1['grn_trn_id'];
				$result3=$dbcon->query($qry3);
				while($rel3=brp_mysqli_fetch_assoc($result3)){
					
					$qry_up4="UPDATE `tbl_pono` SET `status` = '2' WHERE po_id=".$rel3['po_id'];
					$result_up4=$dbcon->query($qry_up4);
					
					$qry_up5="UPDATE `tbl_potrancation` SET `potrancation_status` = '2' WHERE potrancation_id=".$rel3['potrancation_id'];
					$result_up5=$dbcon->query($qry_up5);
					
					$qry_up6="DELETE FROM `tbl_purchase_exp` WHERE exp_in_id=".$rel3['po_id'];
					$result_up6=$dbcon->query($qry_up6);
					
					$qry_up7="UPDATE `tbl_general_book` SET `genral_book_status` = '2' WHERE table_name='tbl_purchase' and table_id=".$rel3['po_id'];
					$result_up7=$dbcon->query($qry_up7);
					
					$qry_up8="UPDATE `tbl_used_tax` SET `tax_used_status` = '2' WHERE table_name='tbl_pono' and used_transaction_id=".$rel3['po_id'];
					$result_up8=$dbcon->query($qry_up8);
					
					$qry_up9="UPDATE `tbl_used_tax` SET `tax_used_status` = '2' WHERE table_name='tbl_potrancation' and used_transaction_id=".$rel3['potrancation_id'];
					$result_up9=$dbcon->query($qry_up9);
					
					$qry_up10="UPDATE `tbl_po_grn_used` SET `po_grn_used_status` = '2' WHERE po_grn_used_id=".$rel3['po_grn_used_id'];
					$result_up10=$dbcon->query($qry_up10);
				
	
				}
		}
		
		
		$qry5="SELECT * FROM `tbl_grn_trn` as potrn
				left join tbl_grn as grn on grn.grn_id=potrn.grn_id
				WHERE potrn.po_ref_id!=0 and ref_type=2";
		$result5=$dbcon->query($qry5);
		while($rel5=brp_mysqli_fetch_assoc($result5))
		{
			
			
		}
	

?>
