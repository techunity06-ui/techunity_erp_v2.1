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
	if(strtolower($POST['mode']) == "generate_report") {
		$s_date=explode(' - ',$POST['date']);
		$str='';$whr='';$hav='';
		$str.='<table class="display table table-bordered table-striped">
				<thead>
					<tr>
						<th style="white-space:nowrap;">Sr. No.</th>
						<th>Date</th>				  
						<th style="white-space:nowrap;">User</th>				  
						<th>Client</th>				  
						<th>PO No.</th>				  
						<th style="white-space:nowrap;">Cutomer Part No.</th>				  
						<th style="white-space:nowrap;">JT Code No.</th>				  
						<th style="white-space:nowrap;">Cable Description</th>				  
						<th style="white-space:nowrap;">Location</th>				  
						<th style="white-space:nowrap;">Invoice No.</th>				  
						<th style="white-space:nowrap;">Qty</th>				  
						<th style="white-space:nowrap;">Rate</th>				  
						<th style="white-space:nowrap;">Amount</th>				  
						<th style="white-space:nowrap;">Docket No.</th>				  
						<th style="white-space:nowrap;">Courier Services</th>				  
					</tr>
				</thead>
				<tbody>';
	
	$whr.=" and so.sales_order_date between '".date("Y-m-d",strtotime($s_date[0]))."' and '".date("Y-m-d",strtotime($s_date[1]))."'";


	$qry="select so.sales_order_date, us.user_name,led.l_name, so.po_no, pro.product_name, strn.product_disc,led.m_address, ct.city_name, st.state_name, inv.invoice_no, itrn.product_qty, unit.unit_name, itrn.product_rate_conv, itrn.product_amount_conv, tna.transportation_name, trns.transport_doc_no  from tbl_sales_order as so
	left join tbl_sales_ordertrn as strn on strn.sales_order_id = so.sales_order_id
	left join tbl_invoicetrn as itrn on itrn.sales_ordertrn_id = strn.sales_ordertrn_id
	left join tbl_invoice as inv on inv.invoice_id = itrn.invoice_id
	left join tbl_transport_transaction as trns on trns.transport_transaction_table_id = inv.invoice_id and trns.transport_transaction_table = 'tbl_invoice'
	left join transportation_details as tna on tna.id = trns.transport_id
	left join product_mst as pro on pro.product_id = strn.product_id
	left join tbl_ledger as led on led.l_id = so.cust_id
	left join unit_mst as unit on unit.unitid = itrn.unit_id
	left join state_mst as st on st.stateid = led.stateid
	left join city_mst as ct on ct.cityid = led.cityid
	left join users as us on us.user_id = so.user_id
	where strn.sales_ordertrn_status=0 ".$whr." and so.company_id=".$_SESSION['company_id'];
	$qry_rs=$dbcon->query($qry);
	if(mysqli_num_rows($qry_rs)){

		$i =1;
		while($rel=mysqli_fetch_assoc($qry_rs)){
			$sales_order_date='';
			if($rel['sales_order_date']!='1970-01-01' && $rel['sales_order_date']!='0000-00-00'){
				$sales_order_date=date("d-m-Y",strtotime($rel['sales_order_date']));
			}
			
			$str.='<tr>
				<td class="text-left">'.$i.'</td>
				<td class="text-left" style="white-space:nowrap;">'.$sales_order_date.'</td>
				<td class="text-left" >'.$rel['user_name'].'</td>
				<td class="text-left" style="white-space:nowrap;">'.$rel['l_name'].'</td>
				<td class="text-left">'.$rel['po_no'].'</td>
				<td class="text-left"></td>
				<td class="text-left">'.$rel['product_name'].'</td>
				<td class="text-left">'.$rel['product_disc'].'</td>
				<td class="text-left">'.$rel['m_address'].','.$rel['state_name'].','.$rel['city_name'].'</td>
				<td class="text-left">'.$rel['invoice_no'].'</td>
				<td class="text-left">'.$rel['product_qty'].' '.$rel['unit_name'].'</td>
				<td class="text-left">'.$rel['product_rate_conv'].'</td>
				<td class="text-left">'.$rel['product_amount_conv'].'</td>
				<td class="text-left">'.$rel['transport_doc_no'].'</td>
				
				<td class="text-left">'.$rel['transportation_name'].'</td>
			</tr>';
			$i++;
		}
	}
	else{
		$str.='<tr><td colspan="15" class="text-center">NO DATA FOUND !!!</td></tr>';
	}
		
		$str.='</tbody>				 
			</table>';
		
		$resp['html_resp']=$str;
		echo json_encode($resp);
	}
?>