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
	if($POST['purchaseorder_id']){
		$where .= " and po.purchaseorder_id=".$POST['purchaseorder_id'];
	}
	if($POST['vender_id']){
		$where .= " and po.vender_id=".$POST['vender_id'];
	}
	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th>Sr.No</th>
			<th>PO.No.</th>
			<th>PO. Date</th>
			<th>Vendor Name</th>
			<th>Item Description</th>
			<th>Base Qty Order</th>
			<th>Conv Qty Order</th>
			<th>Due Date</th>
			<th>Rcv. Date</th>
			<th>Bill No. & Date</th>
			<th>Base Qty.Rec.</th>
			<th>Conv Qty.Rec.</th>
			<th>Pending Qty</th>
			<th>Conv Pending Qty </th>
			<th>Qty Accept</th>
			<th>Conv Qty Accept</th>
		</tr>';

	$companyConfiguration=getCompanyConfiguration($dbcon);
	$purchase_pro_search=$companyConfiguration['purchase_pro_search'];
	$pro_search=explode(",", $purchase_pro_search);
	$query = 'select trn.*, po.purchaseorder_no, po.purchaseorder_date, po.purchaseorder_due_date, led.l_name, pro.product_name, pro.product_icode, dr.drawing_number, grn.grn_no, grn.grn_date, gtrn.product_qty as brcvqty,gtrn.product_conv_qty as gcnvqty,(select sum(d.product_qty) from tbl_grn_trn as d where d.grn_trn_status=0 and d.purchaseordertrn_id=trn.purchaseordertrn_id ) as done_qty,bunit.unit_name,cunit.unit_name as conv_unit from tbl_purchaseordertrn as trn
	left join tbl_purchaseorder as po on po.purchaseorder_id=trn.purchaseorder_id
	left join tbl_grn_trn as gtrn on gtrn.purchaseordertrn_id = trn.purchaseordertrn_id
	left join tbl_grn as grn on grn.grn_id = gtrn.grn_id
	left join tbl_ledger as led on led.l_id=po.vender_id
	left join product_mst as pro on pro.product_id = trn.product_id
	left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
	left join unit_mst as bunit on bunit.unitid  = trn.unit_id
	left join unit_mst as cunit on cunit.unitid  = trn.conv_unit_id
	where trn.purchaseordertrn_status=0 and po.company_id='.$_SESSION['company_id'].' '.$where;

 	$result=$dbcon->query($query);
	$i=1;
	if(brp_mysqli_num_rows($result)>0){
		while($row = brp_mysqli_fetch_array($result)){
		 	if(in_array('drawing',$pro_search)){
	        	$drawing_number = " -- (".$row['drawing_number'].")";
	        }
	        if(in_array('item',$pro_search)){
	            $item_code = " -- (".$row['product_icode'].")";
	        }
	        $disc='';$tol='';
	        if($row['discount_percentage']){
	        	$disc = $row['discount_percentage']." %";
	        }
	        if($row['rate_tolerance']){
	        	$tol = $row['rate_tolerance']." %";
	        }
	        if(date('d-m-Y',strtotime($row['grn_date'])) == '01-01-1970'){
	        	$grn_date = '-';
	        }else{
	        	$grn_date = date('d-m-Y',strtotime($row['grn_date']));
	        }
	        
	        $pending_qty = $row['product_qty'] - $row['brcvqty'];
	        $conv_pending_qty = $row['product_conv_qty'] - $row['gcnvqty'];
	        $str .= '<tr>
	        	<td>'.$i.'</td>
	        	<td>'.$row['purchaseorder_no'].'</td>
	        	<td style="white-space:nowrap">'.date('d-m-Y',strtotime($row['purchaseorder_date'])).'</td>
	        	<td>'.$row['l_name'].'</td>
	        	<td>'.$row['product_name'].' -- '.$row['product_icode'].'</td>
	        	<td>'.number_format($row['product_qty'],4,".","").' '.$row['unit_name'].'</td>
	        	<td>'.number_format($row['product_conv_qty'],4,".","").' '.$row['conv_unit'].'</td>
	        	<td style="white-space:nowrap">'.date('d-m-Y',strtotime($row['purchaseorder_due_date'])).'</td>
	        	<td style="white-space:nowrap">'.$grn_date.'</td>
	        	<td>'.$row['grn_no'].'</td>
	        	<td>'.number_format($row['brcvqty'],4,".","").' '.$row['unit_name'].'</td>
	        	<td>'.number_format($row['gcnvqty'],4,".","").' '.$row['conv_unit'].'</td>
	        	<td>'.number_format($pending_qty,4,".","").' '.$row['unit_name'].'</td>
	        	<td>'.number_format($conv_pending_qty,4,".","").' '.$row['conv_unit'].'</td>
	        	<td>'.number_format($row['brcvqty'],4,".","").' '.$row['unit_name'].'</td>
	        	<td>'.number_format($row['gcnvqty'],4,".","").' '.$row['conv_unit'].'</td>
	        </tr>';
	        $i++;
		}
	}else{
		$str .='<tr>
			<td colspan="12" style="text-align:center">No Data Yet...</td>
		</tr>';
	}
 	
	$str.='</table>';
	echo $str;
}
?>