<?php
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");



/*$sql6="select * from tbl_purchaseordertrn as so_trn 
		where 1";
		$result6=$dbcon->query($sql6);
		while($rel6=brp_mysqli_fetch_assoc($result6)){
			
			$sql7="select * from tbl_purchasetrntemp as so_trn 
			where purchaseordertrn_id IN (".$rel6['temptrn_ref_id'].")";
			$result7=$dbcon->query($sql7);
			while($rel7=brp_mysqli_fetch_assoc($result7)){
				$uph=$rel7['extra_field'].",".$rel6['purchaseordertrn_id'];
				$info13['extra_field'] = $uph;
				$updateid1=update_record('tbl_purchasetrntemp', $info13,"purchaseordertrn_id=".$rel7['purchaseordertrn_id'] , $dbcon);
			}

		}
*/

$sql="select * from tbl_set_main_process as so_trn 
where so_trn.sp_status=0";
$result=$dbcon->query($sql);
while($rel=brp_mysqli_fetch_assoc($result)){
	$info['sp_status'] = 2;
	$updateid=update_record('tbl_set_main_process', $info,"sp_id=".$rel['sp_id'] , $dbcon);
	
	$sql1="select * from tbl_request_product as so_trn 
	where so_trn.sp_id=".$rel['sp_id'];
	$result1=$dbcon->query($sql1);
	while($rel1=brp_mysqli_fetch_assoc($result1)){

		$info1['status'] = 2;
		$updateid1=update_record('tbl_request_product', $info1,"rp_id=".$rel1['rp_id'] , $dbcon);

		$info2['approve_indent_status'] = 2;
		$updateid2=update_record('approve_indent', $info2,"rp_id=".$rel1['rp_id'] , $dbcon);

		$sql2="select * from tbl_purchasetrntemp as so_trn 
		where so_trn.po_ref_id=".$rel1['rp_id'];
		$result2=$dbcon->query($sql2);
		while($rel2=brp_mysqli_fetch_assoc($result2)){

			$info8['purchaseordertrn_status'] = 2;
			$updateid9=update_record('tbl_purchasetrntemp', $info8,"purchaseordertrn_id=".$rel2['purchaseordertrn_id'] , $dbcon);

			$sql3="select * from tbl_purchaseordertrn as so_trn 
			where so_trn.purchaseordertrn_id IN (".$rel2['extra_field'].")";
			$result3=$dbcon->query($sql3);
			while($rel3=brp_mysqli_fetch_assoc($result3)){

				$info3['purchaseordertrn_status'] = 2;
				$updateid8=update_record('tbl_purchaseordertrn', $info3,"purchaseordertrn_id=".$rel3['purchaseordertrn_id'] , $dbcon);

				$info4['purchaseordertrn_req_status'] = 2;
				$updateid3=update_record('tbl_purchaseorder_req_trn', $info4,"purchaseordertrn_id=".$rel3['purchaseordertrn_id'] , $dbcon);

				$info5['status'] = 2;
				$updateid4=update_record('tbl_purchaseorder', $info5,"purchaseorder_id=".$rel3['purchaseorder_id'] , $dbcon);

				$info6['po_delivery_date_status'] = 2;
				$updateid5=update_record('tbl_purchaseorder_delivery_date', $info6,"purchaseordertrn_id=".$rel3['purchaseordertrn_id'] , $dbcon);

				$info7['followup_status'] = 0;
				$updateid6=update_record('tbl_purchaseorder_followup', $info6,"purchaseorder_id=".$rel3['purchaseorder_id'] , $dbcon);

				$sql4="select grn.grn_id from tbl_grn_sub_trn as so_trn 
				left join tbl_grn_trn as gtrn on gtrn.grn_trn_id=so_trn.grn_trn_id
				left join tbl_grn as grn on grn.grn_id=gtrn.grn_id
				where so_trn.purchaseordertrn_id=".$rel3['purchaseordertrn_id'];
				$result4=$dbcon->query($sql4);
				$rel4=brp_mysqli_fetch_assoc($result4);


				$info9['status'] = 2;
				$updateid10=update_record('tbl_batch_data', $info9,"grn_id=".$rel4['grn_id'] , $dbcon);

				$info10['grn_trn_status'] = 2;
				$updateid10=update_record('tbl_grn_trn', $info10,"grn_id=".$rel4['grn_id'] , $dbcon);

				$info12['grn_status'] = 2;
				$updateid10=update_record('tbl_grn', $info12,"grn_id=".$rel4['grn_id'] , $dbcon);


				$sql5="select * from tbl_grn_trn as so_trn 
				where so_trn.grn_id=".$rel4['grn_id'];
				$result5=$dbcon->query($sql5);
				while($rel5=brp_mysqli_fetch_assoc($result5)){
					$info11['status'] = 2;
					$updateid10=update_record('tbl_grn_sub_trn', $info11,"grn_trn_id=".$rel5['grn_trn_id'] , $dbcon);
				}
			}
		}

	}
}
?>
