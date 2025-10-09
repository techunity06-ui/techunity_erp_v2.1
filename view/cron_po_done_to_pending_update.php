<?
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");
$godown_id = 23;
echo $query = "SELECT SQL_CALC_FOUND_ROWS po.purchaseordertrn_id, po.mdate, tc.cat_name, pr.product_name, pr.product_icode, dr.drawing_number, pr.product_alias_name, bms.branch_name, po.total, po.purchaseordertrn_status, po.cdate, po.user_id, po.po_ref_type, sum(po.product_qty) as pqty, po.po_ref_id, po.product_id, po.po_trn_req_status, GROUP_CONCAT(po.purchaseordertrn_id) as purchastrn_id, pr.product_base_unit, pr.product_conv_unit, po.unit_id, unit.unit_name, po.branch_id,res.indent_no,res.sp_id,res.indent_date,setm.po_req_no,setm.po_req_date,led.l_name,qled.l_name as vendor_name,inled.l_name as vendor FROM tbl_purchasetrntemp as po
left join product_mst as pr on pr.product_id=po.product_id 
left join unit_mst as unit on unit.unitid=po.unit_id 
left join tbl_drawing as dr on dr.drawing_id = pr.drawing_id
left join tbl_category as tc on pr.product_category=tc.cat_id
left join po_quotation as poq on poq.po_quotation_id=po.po_quotation_id 
left join branch_mst as bms on bms.branch_id=po.branch_id 
left join ( SELECT cardtrn.product_id, cardtrn.vendor_id FROM tbl_purchasecardtrn as cardtrn 
left join tbl_product_party_purchase as pcard on pcard.party_purchase_id = cardtrn.party_purchase_id where pcard.card_status=0 and cardtrn.purchasecardtrn_status=0 and cardtrn.valid_date>='2023-09-20' and cardtrn.affected_date<='2023-09-20' and pcard.is_aproove=1 and pcard.is_active=0 group by cardtrn.product_id ) ppp on pr.product_id=ppp.product_id 
left join tbl_request_product as req on req.rp_id=po.po_ref_id 

left join tbl_pre_trn as prtr on prtr.pre_trn_id=req.pre_trn_id 

left join tbl_ledger as qled on qled.l_id = poq.vender_id 

left join tbl_ledger as inled on inled.l_id=prtr.vender_id 

left join tbl_ledger as led on led.l_id=ppp.vendor_id 

left join tbl_request_product as res on res.rp_id=po.po_ref_id 

left join tbl_set_main_process as setm on setm.sp_id=res.sp_id 

where ( 1 AND po.purchaseordertrn_status = 0 and po_trn_req_status=0 and po. branch_id = 1 and po.mdate between '2022-09-01' AND '2023-09-20' and po.company_id in (1) ) Group by po.po_quotation_id,po.product_id,po.po_trn_req_status,po_ref_id,po_ref_type ORDER BY po.purchaseordertrn_id desc";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){

	$query="select sum(used_qty) as used_qty from tbl_purchaseorder_req_trn where purchaseordertrn_req_status=0 and req_id in (".$row['purchastrn_id'].")";
				
		$rel=brp_mysqli_fetch_assoc($dbcon->query($query));	
		$pending_qty=$row['pqty']-$rel['used_qty'];

		$info = array();
		if(round($pending_qty,4) <= 0){
			$info['po_trn_req_status'] = 1;
            echo $row['purchaseordertrn_id'] . " </br>";
			$updateid=update_record('tbl_purchasetrntemp', $info, "purchaseordertrn_id=".$row['purchaseordertrn_id'] , $dbcon);
		}
} 


?>
