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
	if($POST['product_id']){
		$where .= " and req.rp_pid=".$POST['product_id'];
	}
	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th style="width:5%">Sr.No</th>
			<th style="width:35%">Description Of Items</th>
			<th style="width:9%">Make</th>
			<th style="width:9%">Cat No.</th>
			<th style="width:9%">Drawing No</th>
			<th style="width:9%">Total Req. Qty</th>
			<th style="width:9%">Stock</th>
			<th style="width:9%">Net Req. Qty</th>
			<th style="width:9%">Unit</th>
		</tr>';

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
	$pro_search=explode(",", $purchase_pro_search);
	$query = 'select sum(req.rp_po_qty) as req_qty,unit.unit_name,pmst.product_name,pmst.product_icode,pmst.cat_no,draw.drawing_number,req.rp_pid,(select sum(req.rp_po_qty) as net_req_qty from tbl_request_product where req.jobwork_type = 0  AND req.indent_status=3 and rp_pid=req.rp_pid Group by req.rp_pid) as net_req_qty,req.purchase_unit  from tbl_request_product as req 
	left join unit_mst as unit on unit.unitid = req.purchase_unit
	left join product_mst as pmst on pmst.product_id=req.rp_pid 
	left join tbl_drawing as draw on draw.drawing_id=pmst.drawing_id 
	where req.jobwork_type = 0  AND  req.company_id='.$_SESSION['company_id'].' '.$where.' Group by req.rp_pid';

 	$result=$dbcon->query($query);
	$i=1;
	if(brp_mysqli_num_rows($result)>0){
		while($row = brp_mysqli_fetch_array($result)){
		 	$current_stock=get_current_stock_new($dbcon,$row['rp_pid'],$row['purchase_unit']);
	        $str .= '<tr>
	        	<td>'.$i.'</td>
	        	<td>'.$row['product_name'].' -- '.$row['product_icode'].'</td>
	        	<td style="white-space:nowrap">-</td>
	        	<td>'.$row['cat_no'].'</td>
	        	<td>'.$row['drawing_number'].'</td>
	        	<td>'.number_format($row['req_qty'],4,".","").'</td>
	        	<td>'.number_format($current_stock,4,".","").'</td>
	        	<td style="white-space:nowrap">'.number_format($row['net_req_qty'],4,".","").'</td>
	        	<td style="white-space:nowrap">'.$row['unit_name'].'</td>
	        	
	        </tr>';
	        $i++;
		}
	}else{
		$str .='<tr>
			<td colspan="9" style="text-align:center">No Data Yet...</td>
		</tr>';
	}
 	
	$str.='</table>';
	echo $str;
}
?>