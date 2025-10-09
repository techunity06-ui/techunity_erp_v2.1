<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	 $qry1="select * from tbl_potrancation as grn
			where grn.potrancation_status=0";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		$product_qty=$rel['product_qty'];
		if($rel['grn_id']!="0"){
			 $qry2="select strn.product_id,grn.grn_id,grn.grn_trn_id,strn.grn_trn_sub_id,strn.purchase_qty,strn.product_qty from tbl_grn_trn as grn
				left join tbl_grn_sub_trn as strn on strn.grn_trn_id=grn.grn_trn_id
			where strn.status=0 and grn.grn_id=".$rel['grn_id']." and grn.product_id=".$rel['product_id'];
			$result2=$dbcon->query($qry2);
			while($rel1=brp_mysqli_fetch_assoc($result2)){
					
					 $pqty=$rel1['product_qty']-$rel1['purchase_qty'];
					
					if($product_qty>=$pqty){
						 $pending_qty=$pqty;
					}else{
						$pending_qty=$product_qty;
					}
						$query_invoicetype = $dbcon->query("UPDATE tbl_grn_sub_trn SET purchase_qty = purchase_qty + ".$pending_qty." WHERE grn_trn_sub_id = ".$rel['grn_trn_sub_id']);
						
						
						 $info_used['potrancation_id']	= $rel['potrancation_id'];
						 $info_used['product_id']		= $rel1['product_id'];
						$info_used['used_qty']			= $pending_qty;
						$info_used['product_rate']		= $rel['product_rate'];
						$info_used['unit_id']			= $rel['unit_id'];
						
						$info_used['grn_id']			= $rel1['grn_id'];
						$info_used['grn_trn_id']		= $rel1['grn_trn_id'];
						$info_used['grn_sub_trn_id']	= $rel1['grn_trn_sub_id'];
						
						$info_used['discount_per']		= $rel['discount_per'];
						$info_used['formulaid']			= $rel['formulaid'];
						
						$info_used['cdate']				= date("Y-m-d H:i:s");
						$info_used['user_id']			= $_SESSION['user_id'];
						$info_used['company_id']		= $_SESSION['company_id'];
						//var_dump($info_used);
						$inserid3=add_record("tbl_po_grn_used", $info_used, $dbcon);
						
						update_grn_sub_trn_to_purchase_status($dbcon,$rel1['grn_trn_sub_id']);
					
					$product_qty=$product_qty-$pending_qty;
			}
	
		}
	}
				

?>
