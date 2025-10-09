<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

//Ankit Sompura 09-01-2021
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	FINANCE_SALE_RETURN,
	FINANCE_SALE_RETURN_CREATE,
	FINANCE_PURCHASE_RETURN_UPDATE,
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

	if(strtolower($POST['mode']) == "fetch") 
	{

		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];

		$where ="  AND DATE(inv.invoice_date) >= '".date('Y-m-d',strtotime($s_date[0]))."' AND  DATE(inv.invoice_date) <= '".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array( 'invtrn.invoice_id','led.l_name','invtrn.currency_id','invtrn.product_hsn_code','invtrn.taxable_value', 'invtrn.cgst_tax_per','invtrn.sgst_tax_rate','invtrn.cgst_tax_rate','invtrn.cgst_tax_rate_conv','invtrn.igst_tax_rate','invtrn.igst_tax_rate_conv','invtrn.sgst_tax_per','invtrn.product_qty','invtrn.sgst_tax_rate','invtrn.sgst_tax_rate_conv','invtrn.igst_tax_per', 'invtrn.trancation_status','inv.cust_id','inv.enable_consignee','inv.consignee_id', 'inv.invoice_no','inv.reverse_charge', 'inv.invoice_date', 'inv.currency_rate', 'inv.g_total as invoice_value', 'led.gst_no', 'led.stateid', 'stmst.state_name','stmst_con.state_name as con_state_name', 'rec.payment_type', 'product.product_name' , 'product.product_type','product.product_icode','product.product_alias_name','product.product_type','product.product_base_unit', 'cat.unit_name as base_unit','ccat.unit_name as conv_unit','rcat.unit_name as rat_unit', 'cate.cat_name', 'pcat.cat_name as pcat_name');
		$sIndexColumn = "invtrn.invoice_id";
		$isWhere = array("invtrn.trancation_status=0 and invtrn.invoice_id != 0". $where);
		$sTable = "tbl_invoicetrn as invtrn";
		$isJOIN = array(
			'left join tbl_invoice as inv on inv.invoice_id = invtrn.invoice_id',
			'left join tbl_receipt as rec on rec.invoice_id = invtrn.invoice_id',
			'left join tbl_ledger as led on led.l_id=inv.cust_id', 
			'left join tbl_ledger as led_con on led_con.l_id=inv.consignee_id', 
			'left join state_mst as stmst on stmst.stateid=led.stateid', 
			'left join state_mst as stmst_con on stmst_con.stateid=led_con.stateid', 
			'left join unit_mst as cat on cat.unitid = invtrn.unit_id', 
			'left join unit_mst as ccat on ccat.unitid = invtrn.conv_unit_id', 
			'left join unit_mst as rcat on rcat.unitid = invtrn.rate_unit', 
			'left join product_mst as product on product.product_id = invtrn.product_id', 
			'left join tbl_drawing as dr on dr.drawing_id = product.drawing_id', 
			'left join tbl_category as cate on cate.cat_id = product.product_category', 
			'left join tbl_category as pcat on pcat.cat_id = product.parent_category');
		$hOrder = "invtrn.invoice_id desc";
		include($path.'include/pagging.php');

		$id=1;
		foreach($sqlReturn as $row) {
			if(!empty($row['currency_id'])){
				$currency=getcurrencydetail($dbcon,$row['currency_id']);
			}else{
				$currency=getcurrencydetail($dbcon,$_SESSION['currency_id']);
			}

			$amount = $row['invoice_value'] * $row['currency_rate'];

			$cgst_rate="-";				
			$cgst_amt="-";				
			$sgst_rate="-";				
			$sgst_amt="-";				
			$igst_rate="-";				
			$igst_amt="-";				

			if($row['cgst_tax_per']!=0)
			{
				$cgst_rate = $row['cgst_tax_per'];
				$cgst_amt = (($row['currency_id']==$_SESSION['currency_id']) ? $row['cgst_tax_rate'] : $row['cgst_tax_rate_conv']);
				$cgst_amt = $currency['currency_symbol']." ".number_format($cgst_amt,2);
			}

			if($row['sgst_tax_per']!=0)
			{
				$sgst_rate = $row['sgst_tax_per'];
				$sgst_amt = (($row['currency_id']==$_SESSION['currency_id']) ? $row['sgst_tax_rate'] : $row['sgst_tax_rate_conv']);
				$sgst_amt = $currency['currency_symbol']." ".number_format($sgst_amt,2);
			}

			if($row['igst_tax_per']!=0)
			{
				$igst_rate = $row['igst_tax_per'];
				$igst_amt = (($row['currency_id']==$_SESSION['currency_id']) ? $row['igst_tax_rate'] : $row['igst_tax_rate_conv']);
				$igst_amt = $currency['currency_symbol']." ".number_format($igst_amt,2);
			}

			$payment_type = "";
			if (isset($row['payment_type'])) {
				$payment_type = $row['payment_type'] == 0 ? 'Regular' : 'PDC';
			}

			$taxable_value_cnt = $amount - $row['taxable_value'];
			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $row['l_name'];
			$row_data[] = $row['gst_no'];
			$row_data[] = $row['state_name'];
			$row_data[] = $row['enable_consignee'] ==  1 ? $row['state_name'] : $row['con_state_name'];
			$row_data[] = $row['invoice_no'];
			$row_data[] = date("d/m/Y", strtotime($row['invoice_date']));
			$row_data[] = number_format($amount,2);
			$row_data[] = $row['product_hsn_code'];
			$row_data[] = $row['product_type'] == 8 ? "Service" : "Goods";
			$row_data[] = number_format($taxable_value_cnt,2);
			$row_data[] = $row['product_qty'];
			$row_data[] = $row['base_unit'];
			$row_data[] = $igst_rate;
			$row_data[] = $igst_amt;
			$row_data[] = $cgst_rate;
			$row_data[] = $cgst_amt;
			$row_data[] = $sgst_rate;
			$row_data[] = $sgst_amt;
			$row_data[] = number_format($row['reverse_charge'],2);
			$row_data[] = $payment_type;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );			
	}
?>