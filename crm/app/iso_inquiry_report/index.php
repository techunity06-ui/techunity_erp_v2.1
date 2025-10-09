<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "generate_report") {
	$s_date=explode(' - ',$POST['date']);
	$str='';$whr='';
	$set="select comp.*,state.state_name,state.gst_state_code from tbl_company as comp left join state_mst as state on comp.stateid=state.stateid where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	$str.='<table class="display table table-bordered table-striped">
	<thead>
	<tr>
	<th colspan="2" rowspan="3" style="text-align: center;">'.$set_head['company_name'].'</th>
	<th colspan="11" style="text-align: center;">'.$set_head['company_name'].'</th>
	<th></th>
	<th colspan="8" style="text-align: center;">F:MKT:01</th>
	</tr>
	<tr>
	<th colspan="11" style="text-align: center;">Inquiry Register</th>
	<th></th>
	<th colspan="8" style="text-align: center;">Rev. No 00</th>
	</tr>
	<tr>
	<th colspan="11"></th>
	<th></th>
	<th colspan="8" style="text-align: center;">Issue Date : '.$POST['date'].'</th>
	</tr>
	<tr>
	<th>Sr. No.</th>
	<th>Client Name</th>
	<th>Contact person / Mo.</th>
	<th>Inquiry No.</th>
	<th>Inq. Ref. No.</th>
	<th>Inq. Date</th>
	<th>Item Description</th>
	<th>Qty</th>
	<th>Quot. Date</th>
	<th>Quotation No.</th>
	<th>Quot. due date</th>
	<th>PO No.</th>
	<th>PO date</th>
	<th>Order Qty</th>
	<th>Next review on</th>
	<th>Required Delivery date</th>
	<th>Dispatch Qty</th>
	<th>Dispatch No</th>
	<th>Dispatch Date</th>
	<th>Sales Order No</th>
	<th>Sales Order Date</th>
	<th>Inq. loss remark</th>
	<tr>
	</thead>
	<tbody>';
	if(!empty($POST['inquiry_date'])){
		$whr.=" and inq.inquiry_date='".date("Y-m-d",strtotime($POST['inquiry_date']))."'";
	}else{
		$whr.=" and inq.inquiry_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";
	}
	if(!empty($POST['cust_id'])){
		$whr.=" and inq.cust_id = ".$POST['cust_id'];
	}
	$c=1;
	$qry="SELECT trninq.*, inq.*, cust.cust_name, per.c_con_fname, per.c_con_lname, per.c_con_mobile, src.rb_name, pro.product_name, quot.quotation_id, quot.quotation_no, quot.quotation_valid_date, quot.quotation_date, sales.sales_order_id, sales.po_no, sales.po_date, unit.unit_name, sales.delivery_date,strn.sales_ordertrn_id,sales.sales_order_no,sales.sales_order_date
		from tbl_inquiry_trn as trninq 
		left join tbl_inquiry as inq on inq.inquiry_id = trninq.inquiry_id 
		left join tbl_customer as cust on cust.cust_id=inq.cust_id 
		left join tbl_cust_contact as per on per.c_con_id=inq.c_con_id 
		left join tbl_refer_by as src on src.rb_id=cust.cust_source 
		left join product_mst as pro on trninq.product_id=pro.product_id 
		left join unit_mst as unit on unit.unitid = trninq.unitid 
		left join tbl_quotation as quot on quot.inquiry_id = trninq.inquiry_id and quot.revise_status = 0 
		left join tbl_sales_order as sales on sales.quotation_id = quot.quotation_id 
		left join tbl_sales_ordertrn as strn on strn.sales_order_id = sales.sales_order_id 
		

		WHERE trninq.inquiry_trn_status=0 AND inq.company_id =".$_SESSION['company_id']."".$whr." GROUP BY trninq.inquiry_trn_id ORDER BY inq.inquiry_date DESC";
	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$order_unit = $order_qty = $delivery_date = $quotation_valid_date = $quotation_date = $po_date ='';
			if(!empty($rel['sales_order_id'])){
				$chkso = $dbcon->query("SELECT product_qty, unit_id FROM tbl_sales_ordertrn WHERE sales_order_id = ".$rel['sales_order_id']." AND product_id = ".$rel['product_id']);
				if(mysqli_num_rows($chkso) > 0){
					$getso = mysqli_fetch_assoc($chkso);
					$order_unit = getunitname($dbcon, $getso['unit_id']);
					$order_qty = $getso['product_qty'];
				}
			}
			if($rel['delivery_date']!="1970-01-01" && $rel['delivery_date']!="0000-00-00" && $rel['delivery_date']!="")
			{
				$delivery_date=date('d-m-Y',strtotime($rel['delivery_date']));
			}
			if($rel['po_date']!="1970-01-01" && $rel['po_date']!="0000-00-00" && $rel['po_date']!="")
			{
				$po_date=date('d-m-Y',strtotime($rel['po_date']));
			}
			if($rel['quotation_date']!="1970-01-01" && $rel['quotation_date']!="0000-00-00")
			{
				$quotation_date=date('d-m-Y',strtotime($rel['quotation_date']));
			}
			if($rel['quotation_valid_date']!="1970-01-01" && $rel['quotation_valid_date']!="0000-00-00")
			{
				$quotation_valid_date=date('d-m-Y',strtotime($rel['quotation_valid_date']));
			}

			if($rel['sales_order_date']!="1970-01-01" && $rel['sales_order_date']!="0000-00-00" && $rel['sales_order_date']!="")
			{
				$sales_order_date=date('d-m-Y',strtotime($rel['sales_order_date']));
			}

			$que = "SELECT group_concat(intrn.product_qty separator '---') as dispatch_qty, group_concat(inv.invoice_no) as dispatch_no, group_concat(inv.invoice_date) as dispatch_date 
			from tbl_invoicetrn as intrn
			left join tbl_invoice as inv on inv.invoice_id = intrn.invoice_id
			where intrn.trancation_status=0 and intrn.sales_ordertrn_id='".$rel['sales_ordertrn_id']."'";
			//  var_dump($que);
			// var_dump($rel['sales_ordertrn_id']);
			$res = $dbcon->query($que);
			$row = brp_mysqli_fetch_array($res);
			// var_dump($row);
			// echo $row;
			$str.='<tr style="white-space:nowrap;">
			<td class="text-left">'.$c.'</td>
			<td class="text-left">'.$rel['cust_name'].'</td>
			<td class="text-left">'.$rel['c_con_fname'].' '.$rel['c_con_lname'].'<br>'.$rel['c_con_mobile'].'</td>
			<td class="text-left">'.$rel['inquiry_no'].'</td>
			<td class="text-left">'.$rel['rb_name'].'</td>
			<td class="text-left">'.date("d-m-Y",strtotime($rel['inquiry_date'])).'</td>
			<td class="text-left">'.$rel['product_name'].'</td>
			<td class="text-left">'.$rel['product_qty'].' '.$rel['unit_name'].'</td>
			<td class="text-left">'.$quotation_date.'</td>
			<td class="text-left">'.$rel['quotation_no'].'</td>
			<td class="text-left">'.$quotation_valid_date.'</td>
			<td class="text-left">'.$rel['po_no'].'</td>
			<td class="text-left">'.$po_date.'</td>
			<td class="text-left">'.$order_qty.' '.$order_unit.'</td>
			<td class="text-left"></td>
			<td class="text-left">'.$delivery_date.'</td>
			<td class="text-left">'.$row['dispatch_qty'].'</td>
			<td class="text-left">'.$row['dispatch_no'].'</td>
			<td class="text-left">'.$row['dispatch_date'].'</td>
			<td class="text-left">'.$rel['sales_order_no'].'</td>
			<td class="text-left">'.$sales_order_date.'</td>
			<td class="text-left"></td>
			</tr>';
			$c++;
		}
	}else{
		$str.='<tr><td colspan="20" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
	$str.='</tbody>				 
	</table>';

	$resp['html_resp']=$str;
	echo json_encode($resp);
}
?>