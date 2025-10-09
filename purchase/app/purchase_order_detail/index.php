<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}


if(strtolower($POST['mode']) == "fetch") {
}

else if(strtolower($POST['mode']) == "po_product_report")
{
	//var_dump($POST);
	$s_date=explode(' - ',$POST['date']);
	$set = "select * from tbl_company where company_id=".$_SESSION
	['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$where = '';

	
	if($POST['work_no']){
		$where .= " and req.sp_id=".$POST['work_no'];
	}

	if($POST['sales_order_no']){
		$where .= " and req.sales_order_id=".$POST['sales_order_no'];
	}

	if($POST['product_id']){
		$where .= " and ptrn.product_id=".$POST['product_id'];
	}

	if($POST['vender_id']){
		$where .= " and ptrn.vendor_id=".$POST['vender_id'];
	}

	$where.="  and po.purchaseorder_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND po.purchaseorder_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

	
	/*var_dump($report_qty['opening_unit']);
	var_dump($report_qty2['opening_unit']);*/
	$query = "select ptrn.product_id, po.vender_id, led.l_name,pro.product_name, po.purchaseorder_no, po.purchaseorder_date, po.purchaseorder_id, cat.cat_name, po.purchaseorder_due_date, po.remark, ptrn.product_qty, (select sum(gtr.product_qty) from tbl_grn_sub_trn as gtr where gtr.status=0 and gtr.purchaseordertrn_id = ptrn.purchaseordertrn_id) as receive_qty, setr.po_req_no, setr.po_req_date, pro.product_category, brn.branch_name, ptrn.product_des, ptrn.short_close_qty, so.sales_order_no, so.sales_order_date 

	from tbl_purchaseordertrn as ptrn
	left join tbl_purchaseorder as po on po.purchaseorder_id=ptrn.purchaseorder_id
	left join tbl_purchaseorder_req_trn as retr on retr.purchaseordertrn_id = ptrn.purchaseordertrn_id
	left join tbl_request_product as req on req.rp_id = retr.rp_id
	left join tbl_set_main_process as setr on setr.sp_id = req.sp_id
	left join product_mst as pro on pro.product_id = ptrn.product_id
	left join tbl_category as cat on cat.cat_id = pro.product_category
	left join tbl_ledger as led on led.l_id = po.vender_id
	left join tbl_sales_order as so on so.sales_order_id = req.sales_order_id
	left join branch_mst as brn on brn.branch_id=po.branch_id
	where ptrn.purchaseordertrn_status=0 and retr.purchaseordertrn_req_status=0 and po.po_approval_status=1 ".$where." and po.company_id=".$_SESSION['company_id']." ORDER BY po.purchaseorder_id desc";

	/*$query = "SELECT po.indent_no, po.rp_pid, po.indent_date, po.rp_po_qty,po.purchase_unit,pmst.product_base_unit, unit.unit_name,bunit.unit_name as base_unit, spro.po_req_no, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id, po.shortclose_qty, po.sp_id, us.user_name 

FROM tbl_request_product as po 

left join tbl_set_main_process as spro on spro.sp_id=po.sp_id 
left join product_mst as pmst on pmst.product_id=po.rp_pid 
left join tbl_category as tc on pmst.product_category=tc.cat_id 
left join branch_mst as bms on bms.branch_id=po.branch_id 
left join unit_mst as unit on unit.unitid=po.purchase_unit
left join unit_mst as bunit on bunit.unitid= pmst.product_base_unit 
left join (select round(IFNULL(sum(req.approve_qty),0),4) as used_qty,req.rp_id from approve_indent as req where req.approve_indent_status=0 group by req.rp_id) as rereq on rereq.rp_id=po.rp_id 
left join users as us on us.user_id=po.user_id 

where  po.jobwork_type = 0 AND po.status !=2  and po.indent_status NOT IN (0,2) and po.company_id in (".$_SESSION['company_id'].") ".$where." Group by po.rp_id ORDER BY po.rp_id desc";
$query;	*/
$result=$dbcon->query($query);

	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th colspan="17" style="text-align:center">
				<h4>'.$set_head['company_name'].'</h4>
				<strong>'.$set_head['address'].'</strong><br>
			</th>
		</tr>
		<tr>
			<th>Sr.No</th>
			<th>Sales Order No</th>
			<th>Sales Order Date</th>
			<th>WorkOrder No</th>
			<th>Work Order Date</th>
			<th>Po No.</th>
			<th>Po Date</th>
			<th>Vendor Name</th>
			<th>Product Name</th>
			<th>Product Category</th>
			<th>Branch Name</th>
			<th>PO Qty</th>
			<th>Recieve Qty</th>
			<th>Pending Qty</th>
			<th>Short Close Qty</th>
			<th>Del.Date</th>
			<th>Remark</th>
		</tr>';
	$i=1;
	if(brp_mysqli_num_rows($result)>0){
		while($row = brp_mysqli_fetch_array($result)){
			$sales_order_date='';
			if($row['sales_order_date']!="1970-01-01" && $row['sales_order_date']!="0000-00-00" && $row['sales_order_date']!="")
			{
				$sales_order_date=date('d-m-Y',strtotime($row['sales_order_date']));
			}

			$workorderdate='';
			if($row['po_req_date']!="1970-01-01" && $row['po_req_date']!="0000-00-00" && $row['po_req_date']!="")
			{
				$workorderdate=date('d-m-Y',strtotime($row['po_req_date']));
			}

			$purchaseorder_date='';
			if($row['purchaseorder_date']!="1970-01-01" && $row['purchaseorder_date']!="0000-00-00" && $row['purchaseorder_date']!="")
			{
				$purchaseorder_date=date('d-m-Y',strtotime($row['purchaseorder_date']));
			}

			$delivery_date='';
			if($row['purchaseorder_due_date']!="1970-01-01" && $row['purchaseorder_due_date']!="0000-00-00" && $row['purchaseorder_due_date']!="")
			{
				$delivery_date=date('d-m-Y',strtotime($row['purchaseorder_due_date']));
			}

			$product_cat = 'Primary';
			if($row['product_category']!=0){
				$product_cat = $row['cat_name'];
			}

			if($row['used_status']==1){
				$pending_qty = 0;
			}else{
				$pending_qty = $row['product_qty']-$row['receive_qty'];
			}

			$str.='<tr>
				<td>'.$i.'</td>
				<td>'.$row['sales_order_no'].'</td>
				<td>'.$sales_order_date.'</td>
				<td>'.$row['po_req_no'].'</td>
				<td>'.$workorderdate.'</td>
				<td>'.$row['purchaseorder_no'].'</td>
				<td>'.$purchaseorder_date.'</td>
				<td>'.$row['l_name'].'</td>
				<td>'.$row['product_name'].'</td>
				<td>'.$product_cat.'</td>
				<td>'.$row['branch_name'].'</td>
				<td>'.$row['product_qty'].'</td>
				<td>'.$row['receive_qty'].'</td>
				<td>'.$pending_qty.'</td>
				<td>'.$row['short_close_qty'].'</td>
				<td>'.$delivery_date.'</td>
				<td>'.$row['product_des'].'</td>
				
			</tr>';
			$i++;
		}
	}else{
		$str .= '<tr>
			<td colspan="16" style="text-align:center">No Data Yet...</td>
		</tr>';
	}
	$str.='</table>';
	echo $str;
}
?>