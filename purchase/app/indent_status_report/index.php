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
	//$s_date=explode(' - ',$POST['date']);
	$set = "select * from tbl_company where company_id=".$_SESSION
	['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$where = '';

	if($POST['status']){
		$where .= " and indent_status = ".$POST['status'];
	}
	if($POST['work_no']){
		$where .= " and po.sp_id=".$POST['work_no'];
	}

	if($POST['sales_order_no']){
		$where .= " and po.sp_id=".$POST['sales_order_no'];
	}

	if($POST['user_id']){
		$where .= " and po.user_id=".$POST['user_id'];
	}
	
	/*var_dump($report_qty['opening_unit']);
	var_dump($report_qty2['opening_unit']);*/
	$query = "SELECT po.indent_no, po.rp_pid, po.indent_date, po.rp_po_qty,po.purchase_unit,pmst.product_base_unit, unit.unit_name,bunit.unit_name as base_unit, spro.po_req_no, used_qty, pmst.product_name, tc.cat_name, po.rp_id, bms.branch_name, po.indent_status, po.branch_id, po.shortclose_qty, po.sp_id, us.user_name 

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
$query;	
$result=$dbcon->query($query);

	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th colspan="13" style="text-align:center">
				<h4>'.$set_head['company_name'].'</h4>
				<strong>'.$set_head['address'].'</strong><br>
				<strong>Product Name : '.$product_row['product_name'].'</strong>
			</th>
		</tr>
		<tr>
			<th>Sr.No</th>
			<th>Indent No</th>
			<th>Indent Date</th>
			<th>Sales Order No</th>
			<th>WorkOrder No</th>
			<th>Product Name</th>
			<th>Product Category</th>
			<th>Branch Name</th>
			<th>Total Qty</th>
			<th>Pending Qty</th>
			<th>Short Close Qty</th>
			<th>User Name</th>
		</tr>';
	$i=1;
	if(brp_mysqli_num_rows($result)>0){
		while($row = brp_mysqli_fetch_array($result)){
			$so_no = "select sales_order_trn_id from tbl_request_product where sp_id =".$row['sp_id']." and main_request=1";
			$q = $dbcon->query($so_no);
			$r = brp_mysqli_fetch_array($q);

			$get_so = "select so.sales_order_no from tbl_sales_ordertrn as trn
			left join tbl_sales_order as so on so.sales_order_id = trn.sales_order_id
			where trn.sales_ordertrn_id=".$r['sales_order_trn_id']; 
			$exe = $dbcon->query($get_so);
			$rel = brp_mysqli_fetch_array($exe);

			$type="base_unit";
			$ret_qty=convert_stock($dbcon,$row['rp_po_qty'],$row['rp_pid'],$type);

			$pen_qty=convert_stock($dbcon,$max_approve_qty,$row['rp_pid'],$type);

			$short_qty=convert_stock($dbcon,$row['shortclose_qty'],$row['rp_pid'],$type);

			if($row['purchase_unit'] != $row['product_base_unit']){
				
				$qty = '<strong style="color:green">'.$ret_qty.' '.$row['base_unit'].'</strong><br><strong style="color:orange">'.$row['rp_po_qty'].' '.$row['unit_name'].'</strong>';

				$pen  = '<strong style="color:green">'.$pen_qty.' '.$row['base_unit'].'</strong><br><strong style="color:orange">'.$max_approve_qty.' '.$row['unit_name'].'</strong>';

				$shortclose_qty  = '<strong style="color:green">'.$short_qty.' '.$row[''].'</strong><br><strong style="color:orange">'.$row['shortclose_qty'].' '.$row['unit_name'].'</strong>';
			}else{
				$qty = '<strong style="color:green">'.$ret_qty.' '.$row['base_unit'].'</strong>';

				$pen = '<strong style="color:green">'.$pen_qty.' '.$row['base_unit'].'</strong>';

				$shortclose_qty = '<strong style="color:green">'.$short_qty.' '.$row['base_unit'].'</strong>';
			}

			$max_approve_qty=round($row['rp_po_qty'],4)-$row['used_qty']-$row['shortclose_qty'];
			$cat = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$str.='<tr>
				<td>'.$i.'</td>
				<td>'.$row['indent_no'].'</td>
				<td>'.$row['indent_date'].'</td>
				<td>'.$rel['sales_order_no'].'</td>
				<td>'.$row['po_req_no'].'</td>
				<td>'.$row['product_name'].'</td>
				<td>'.$cat.'</td>
				<td>'.$row['branch_name'].'</td>
				<td>'.$qty.'</td>
				<td>'.$pen.'</td>
				<td>'.$shortclose_qty.'</td>
				<td>'.$row['user_name'].'</td>
			</tr>';
			$i++;
		}
	}else{
		$str .= '<tr>
			<td colspan="12" style="text-align:center">No Data Yet...</td>
		</tr>';
	}
	$str.='</table>';
	echo $str;
}
?>