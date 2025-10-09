<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* Malik Start */
function get_payment_mode($dbcon,$paymentmodeid){
	$str='';
	
	$query="select * from tbl_common_mst as pmode where common_category_id='12'  and company_id = $_SESSION[company_id] and isdelete=0";
	
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="">--Select Payment Mode--</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['common_mst_id']==$paymentmodeid)
		{$sel ="selected='selected'";}
		
		$str .= '<option '.$sel.' value="'.$rel['common_mst_id'].'">'.$rel['common_mst_name'].'</option>';
	}
	return $str;
}
/* Maulik End */
/* Dhruv start code*/
function f_get_group_ledger($dbcon,$sales_group,$ledger_id,$where=""){

	$str='';
	
	$query="select * from tbl_ledger as pro where l_status=0 ".$where." and company_id = $_SESSION[company_id] and l_group IN ($sales_group) order by TRIM(l_name) ASC";
	
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="">--select ledger--</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if($rel['l_id']==$ledger_id)
		{$sel ="selected='selected'";}
		
		$str .= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
	return $str;
}

function get_gst_statecode($dbcon,$cust_id){

	$qry="SELECT l.stateid,l.l_name,sm.gst_state_code,sm.state_name FROM `tbl_ledger` as l left join state_mst as sm on sm.stateid= l.stateid
		where l.l_id=".$cust_id.""; 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result['gst_state_code'].','.$result['stateid'];
}

function get_gst_statecode_details($dbcon,$cust_id){

	$qry="SELECT l.stateid,l.l_name,sm.gst_state_code,sm.state_name FROM `tbl_ledger` as l left join state_mst as sm on sm.stateid= l.stateid
		where l.l_id=".$cust_id.""; 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_grossbalance($dbcon,$cust_id){

	$qry="SELECT sum(i.g_total) as gtotal 
FROM `tbl_invoice` as i
left join tbl_financial_year as fy on fy.financial_year_id=i.financial_year_id and fy.current_status=1
where i.cust_id=".$cust_id."";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result['gtotal'];
}

function get_grossbalance_purchase($dbcon,$cust_id){

	$qry="SELECT sum(i.g_total) as gtotal 
FROM `tbl_pono` as i
left join tbl_financial_year as fy on fy.financial_year_id=i.financial_year_id and fy.current_status=1
where i.vender_id=".$cust_id."";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result['gtotal'];
}

function get_hsn_details($dbcon,$code){

	$qry="SELECT hsn_id,hsn_code,sale_gst FROM `mst_hsn_code` where hsn_status=0 and hsn_code=".$code."";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_hsn_details_by_id($dbcon,$code){

	$qry="SELECT hsn_id,hsn_code,sale_gst FROM `mst_hsn_code` where hsn_status=0 and hsn_id=".$code."";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));

 	return $result;
}

function get_tax_cat_by_hsn($dbcon,$id)
{
	$qry="SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_code='$id'";


	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_tax_cat_by_hsn_id($dbcon,$id)
{
	 $qry="SELECT h.hsn_id,h.hsn_code,h.sale_gst,t.tax_gst,t.tax_cat_id FROM `mst_hsn_code` as h left join tbl_tax_category as t on t.tax_cat_id=h.sale_gst where h.hsn_status=0 and h.hsn_id='$id'";
	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_check_addition_tax($dbcon,$tax_id,$product_amount,$inserid,$product_id,$edit_id,$branch_id,$trn_table,$currency_id,$currency_rate,$product_amt_conv)
{
	$qry=$dbcon->query("SELECT * from tbl_tax_category_details where tax_additional='1' and isdelete=0 and tax_cat='$tax_id'");
	if(brp_mysqli_num_rows($qry)>0)
	{
		while($row = brp_mysqli_fetch_assoc($qry)) {
           // $rows[] = $row;
			if($currency_id==$_SESSION['currency_id']){
				$tax_amt = ($product_amount*$row['tax_per'])/100;
				$tax_amt_conv = ($currency_rate*$product_amount*$row['tax_per'])/100;
			}else{
				$tax_amt = ($currency_rate*$product_amount*$row['tax_per'])/100;
				$tax_amt_conv = ($product_amount*$row['tax_per'])/100;
			}

		   $tax_amt = ($product_amount*$row['tax_per'])/100;
			$insert_tax = add_tax_transaction_record($dbcon,$row['tax_id'],$row['tax_per'],$tax_amt,$inserid,$trn_table,$product_id,3,$edit_id,$branch_id,$currency_id,$currency_rate,$tax_amt_conv);
        }
	}
	else
	{
		$rows = 0;
	}
	//return $rows;
}



function get_bill_sundry_ledger($dbcon,$default_sundry){
	$str='';

	$query="select * from tbl_ledger where l_status=0 and company_id = $_SESSION[company_id] and enable_bill_sunfry=1 and default_sundry='".$default_sundry."' order by l_id";
	$result=brp_mysqli_fetch_all($dbcon->query($query));
 	return $result;
}

function get_invoice_total_tax($dbcon){
	$query="SELECT sum(cgst_tax_rate) as cgst_rate,sum(sgst_tax_rate) as sgst_rate,sum(igst_tax_rate) as igst_rate FROM `tbl_invoicetrn` where invoice_id=0 and trancation_status!=2 group by cgst_tax_per,sgst_tax_per,igst_tax_per";
	$result=brp_mysqli_fetch_all($dbcon->query($query));
 	return $result;
}

function get_eway_token(){
	$url="https://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/authenticate?action=ACCESSTOKEN&aspid=1664451121&password=brp@%23123&gstin=34AACCC1596Q002&username=TaxProEnvPON&ewbpwd=abc34*";
	
	$curl = curl_init();
	//$certificate_location = "/usr/local/openssl-0.9.8/certs/cacert.pem";

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/authenticate?action=ACCESSTOKEN&aspid=1664451121&password=brp%40%23123&gstin=34AACCC1596Q002&username=TaxProEnvPON&ewbpwd=abc34*",

	// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $certificate_location);
	// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $certificate_location);
	  // CURLOPT_SSL_VERIFYHOST => $certificate_location,
	  // CURLOPT_SSL_VERIFYPEER => $certificate_location,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 400,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
	    "cache-control: no-cache",
	    "postman-token: 2252c465-2d6f-e336-376a-a441347184d5"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	print_r($response);exit;

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  echo $curl;
	}
}

function get_item_details($dbcon){
	//$str='';
	
	$query="select pm.product_name as productName,pm.product_hsn as hsnCode,it.product_qty as quantity,it.cgst_tax_rate as cgstRate,it.sgst_tax_rate as sgstRate,it.igst_tax_rate as igstRate FROM `tbl_invoicetrn` as it left join product_mst as pm on it.product_id=pm.product_id where it.invoice_id=0 and it.trancation_status!=2
";
	$result=brp_mysqli_fetch_all($dbcon->query($query));
 	return $result;
}

function get_trans_by_inv_id($dbcon,$invoiceid){
	$query="select pm.product_name as productName,pm.product_desc,hc.hsn_code as hsnCode,um.unit_code,it.product_qty as quantity,it.cgst_tax_rate as cgstValue,it.sgst_tax_rate as sgstValue,it.igst_tax_rate as igstValue,it.cgst_tax_per as cgstPer,it.sgst_tax_per as sgstPer,it.igst_tax_per as igstper,it.taxable_value,it.total, it.product_qty FROM `tbl_invoicetrn` as it left join product_mst as pm on it.product_id=pm.product_id left join mst_hsn_code as hc on hc.hsn_id= pm.product_hsn left join unit_mst as um on um.unitid=it.unit_id where it.invoice_id=".$invoiceid."
";
	$result=brp_mysqli_fetch_all($dbcon->query($query));
 	return $result;	
}

function get_trans_by_sale_return_id($dbcon,$id)
{
	$query="select pm.product_name as productName,pm.product_desc,hc.hsn_code as hsnCode,um.unit_code,it.sale_return_qty,it.sale_return_cgst_tax_amt as cgstValue,it.sale_return_sgst_tax_amt as sgstValue,it.sale_return_igst_tax_amt as igstValue,it.sale_return_cgst_tax_per as cgstPer,it.sale_return_sgst_tax_per as sgstPer,it.sale_return_igst_tax_per as igstper,it.sale_return_total_amount,it.sale_return_amount FROM `tbl_sale_return_transaction` as it left join product_mst as pm on it.sale_return_product=pm.product_id left join mst_hsn_code as hc on hc.hsn_id= pm.product_hsn left join unit_mst as um on um.unitid=it.sale_return_unit where it.sale_return_id=".$id."
";
	$result=brp_mysqli_fetch_all($dbcon->query($query));
 	return $result;	
}

function get_state_details($dbcon,$where){

	$qry="SELECT * FROM `state_mst` where state_status=0 $where";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_city_details($dbcon,$where){

	$qry="SELECT * FROM `city_mst` where city_status=0 $where";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_trasport_data($dbcon,$where){
	$qry="SELECT * FROM `transportation_details` where status=0 $where";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_common_mst_data($dbcon,$where){
	$qry="SELECT * FROM `tbl_common_mst` where isdelete=0 $where";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_item_details_sale_return($dbcon,$id=""){
	//$str='';
	
	$query="select pm.product_name as productName,pm.product_hsn as hsnCode,it.product_qty as quantity,it.cgst_tax_rate as cgstRate,it.sgst_tax_rate as sgstRate,it.igst_tax_rate as igstRate FROM `tbl_sale_return_transaction` as it left join product_mst as pm on it.sale_return_product=pm.product_id where it.sale_return_id='$id' and it.trancation_status='1'";
	$result=brp_mysqli_fetch_all($dbcon->query($query));
 	return $result;
}

function submitEwayApi($data){
//	$url="https://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/authenticate?action=ACCESSTOKEN&aspid=1664451121&password=brp@#3123&gstin=34AACCC1596Q002&username=TaxProEnvPON&ewbpwd=abc34*";
//print_r($data);exit;

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://ip.webtel.in/ewaygsp2/Sandbox/EWayBill/v1.3/GenEWB",
	  CURLOPT_SSL_VERIFYHOST => 0,
	  CURLOPT_SSL_VERIFYPEER => 0,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => $data,
	  CURLOPT_HTTPHEADER => array(
	  	'Content-Type:application/json',
	    "Authorization: /IalkRmh3z4=:::ZH4TUvIeJ3A=",
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	return $response;
	
}

function getTcsDetails($dbcon)
{
	
	$qry="SELECT * from tbl_tcs_deduction_transaction where isdelete=0";
	$result=$dbcon->query($qry);
	$row=brp_mysqli_fetch_all($result);
	return $row;
}

function getTransportEwayDetails($dbcon,$voucher='',$voucher_id='')
{
	$where='';
	if($voucher!='')
	{
		$where.=" and tt.transport_voucher='$voucher'";
	}

	$qry="SELECT tt.*,et.* FROM `tbl_transport_transaction` as tt 
		left join tbl_ewaybill_transaction as et on et.eway_bill_transport_transaction_id = tt.transport_transaction_id and et.isdelete=0
		where tt.isdelete=0 and tt.transport_transaction_table_id=0".$where;
	$result=$dbcon->query($qry);
	$row=brp_mysqli_fetch_assoc($result);
	return $row;
}

function get_ledger_by_name($dbcon,$name){

	$qry="SELECT l_id FROM `tbl_ledger` where l_status=0 and company_id = $_SESSION[company_id] and l_name = '".$name."' ";
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function add_tax_transaction_record($dbcon,$ledger_id,$tx_tax_value_per,$tx_taxable_value,$transaction_id,$table_name,$product_id,$tx_status,$edit_id,$branch_id,$currency_id,$currency_rate,$tx_taxable_value_conv,$delete_tax='')
{	
	$info1['tx_tax_id'] = $ledger_id;
	$info1['tx_tax_value'] = $tx_tax_value_per;
	$info1['tx_taxable_value'] = $tx_taxable_value;
	$info1['tx_transaction_id'] = $transaction_id;
	$info1['tx_transaction_type'] = $table_name;
	$info1['tx_product_id']	= $product_id;
	$info1['tx_status'] = $tx_status;
	$info1['cdate']	 = date("Y-m-d H:i:s");
	$info1['user_id'] = $_SESSION['user_id'];
	$info1['company_id'] = $_SESSION['company_id'];
	$info1['branch_id'] = $branch_id;
	
	$info1['currency_id'] = $currency_id;
	$info1['currency_rate'] = $currency_rate;
	if($currency_id==$_SESSION['currency_id']){
		$info1['tx_taxable_value'] = $tx_taxable_value;
		$info1['tx_taxable_value_conv'] = $tx_taxable_value_conv;
	}else{
		$info1['tx_taxable_value'] = $tx_taxable_value_conv;
		$info1['tx_taxable_value_conv'] = $tx_taxable_value;
	}
	
	$query = 'select * from tbl_tax_trn where tx_transaction_type="'.$table_name.'" and tx_transaction_id='.$transaction_id.' and tx_status='.$tx_status.' and tx_tax_id='.$ledger_id;

	$result = $dbcon->query($query);
	$cnt = brp_mysqli_num_rows($result);
	//var_dump($delete_tax);
	if($delete_tax != ''){
		if(!empty($edit_id)){
			$info3['tx_status'] = 2;
			$updateid = update_record("tbl_tax_trn", $info3,"tx_transaction_id=".$edit_id." and tx_transaction_type='".$table_name."' and tx_tax_id=".$ledger_id , $dbcon, $branch_id);
		}
	}else{
		if($cnt==0){
			$inserid=add_record("tbl_tax_trn",$info1, $dbcon,$branch_id);
			/*echo $inserid;*/
		}else{
			if($currency_id==$_SESSION['currency_id']){
				$info2['tx_taxable_value'] = $tx_taxable_value;
				$info2['tx_taxable_value_conv'] = $tx_taxable_value_conv;
			}else{
				$info2['tx_taxable_value'] = $tx_taxable_value_conv;
				$info2['tx_taxable_value_conv'] = $tx_taxable_value;
			}
			$info2['tx_tax_value'] 	= $tx_tax_value_per;
			$info2['tx_product_id']	= $product_id;
			$updateid = update_record("tbl_tax_trn", $info2,"tx_transaction_id=".$edit_id." and tx_transaction_type='".$table_name."' and tx_status=".$tx_status , $dbcon, $branch_id);
		}
	}
}


function get_invoice_by_cust($dbcon,$cust,$edit_id='',$where='')
{
	$qry = "select invoice_id,invoice_no,invoice_date,g_total,currency_enable from tbl_invoice where invoice_status='0' and cust_id='$cust' ".$where;
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Invoice--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['invoice_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['invoice_id'].'" '.$sel.' data-currency_enable="'.$row['currency_enable'].'" >'.$row['invoice_no'].'</option>';
		
	}
	echo $str;
}

//Added By Dhruv
function getInvoiceByCust($dbcon,$cust,$edit_id='')
{
	$qry = "select invoice_id,invoice_no,invoice_date,g_total,currency_enable from tbl_invoice where invoice_status='0' and cust_id='$cust' ";
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Invoice--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		if($row['invoice_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		$str.='<option value="'.$row['invoice_id'].'" '.$sel.' data-bill_date="'.date("d-m-Y",strtotime($row['invoice_date'])).'" >'.$row['invoice_no'].'</option>';	
	}
	return $str;
}

function getPurchaseInvoiceByCust($dbcon,$cust,$edit_id='')
{
	$qry = "select po_id,po_no,po_date,g_total,currency_enable from tbl_pono where status='0' and vender_id='$cust' ";
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Purchase Invoice--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['po_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['po_id'].'" '.$sel.' data-bill_date="'.date("d-m-Y",strtotime($row['po_date'])).'" >'.$row['po_no'].'</option>';
		
	}
	return $str;
}

//End code By Dhruv

function get_purchase_invoice_by_cust($dbcon,$cust,$edit_id='',$where='')
{
	$qry = "select po_id,po_no,po_date,g_total,currency_enable from tbl_pono where status='0' and vender_id='$cust' ".$where;
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Purchase Invoice--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['po_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['po_id'].'" '.$sel.' data-currency_enable="'.$row['currency_enable'].'" >'.$row['po_no'].'</option>';
		
	}
	echo $str;
}

function get_due_invoices_by_cust($dbcon,$cust,$edit_id='')
{
	$qry = "SELECT invoice_no as ref,g_total as due_amount,invoice_id as id,0 as 'ref_type' FROM `tbl_invoice` WHERE `payment_status` != 1 and sale_return_status =0 and cust_id=".$cust." union SELECT bill_ref_no as ref,due_amount,bill_opening_id as id,1 as 'ref_type' FROM `tbl_ledger_billbybill_opening` WHERE `payment_status` != 1 and isdelete=0 and bill_ledger_id=".$cust."";
	$select = $dbcon->query($qry);
	//$row=brp_mysqli_fetch_assoc($select);
	$str='';
	$str.='<option value="0">--Select Ref No--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['invoice_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['id'].'" '.$sel.' data-type="'.$row['ref_type'].'" data-dueamount="'.$row['due_amount'].'" >'.$row['ref'].'</option>';
		
	}
	return $str;
}

function get_product_from_invoice($dbcon,$invoice,$product_id='',$edit_id='',$where='')
{
	$qry = "select trn.product_id,trn.invoice_id,p.product_id,p.product_name,trn.trancation_id from tbl_invoicetrn as trn left join product_mst as p on p.product_id=trn.product_id where trn.invoice_id='$invoice' and trn.trancation_status='0' ".$where;
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Product--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['product_id']==$product_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['product_id'].'" data-transaction_id="'.$row['trancation_id'].'" '.$sel.'>'.$row['product_name'].'</option>';
		
	}
	return $str;
}

function get_product_from_purchase_invoice($dbcon,$invoice,$product_id='',$edit_id='',$where='')
{
	$qry = "select trn.product_id,trn.po_id,p.product_id,p.product_name,trn.potrancation_id from tbl_potrancation as trn left join product_mst as p on p.product_id=trn.product_id where trn.po_id='$invoice' and trn.potrancation_status='0' ".$where;
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Product--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['product_id']==$product_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['product_id'].'" data-transaction_id="'.$row['potrancation_id'].'" '.$sel.'>'.$row['product_name'].'</option>';
		
	}
	return $str;
}

function get_sundry_details($dbcon,$id)
{
	$qry = "select * from tbl_ledger_bill_sundry where isdelete=0 and sundry_ledger_id='$id'";
	$select = $dbcon->query($qry);
	return brp_mysqli_fetch_assoc($select);
	//return $qry;
}

function get_ledger_details($dbcon,$id)
{
	$qry = "select * from tbl_ledger where l_id='$id'";
	$select = $dbcon->query($qry);
	return brp_mysqli_fetch_assoc($select);
}

function get_salesman_ledger_selectbox($dbcon,$salesman_id)
{
	$qry = "select * from tbl_ledger where enable_salesman='1' and l_status='0'";
	$select = $dbcon->query($qry);
	//$row=brp_mysqli_fetch_array($select);
	$str="";
	while($row=brp_mysqli_fetch_array($select))
	{
		$sel='';
		
		if($row['l_id']==$salesman_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['l_id'].'" data-transaction_id="" '.$sel.'>'.$row['l_name'].'</option>';
		
	}
	
	echo $str;
}

function get_unclear_stock($dbcon,$product_id,$unit_id,$table,$product_qty,$search_product_id,$where)
{
	$qry = "select sum($product_qty) as unclear_qty from $table where $search_product_id=$product_id ".$where;
	$row=brp_mysqli_fetch_assoc($dbcon->query($qry));
	$sum_qty = $row['unclear_qty'];
	return $sum_qty;
}

function get_financial_year_new($dbcon)
{
	$select = $dbcon->query("select * from tbl_financial_year where current_status='1' and isdelete='0'");
	$row = brp_mysqli_fetch_assoc($select);
	return $row;
}
//Added by dhruv 
function get_receipt_payment_trn_detail($dbcon,$payment_type,$receipt_id)
{
	$select = $dbcon->query("select * from tbl_receipt_payment_trn where receipt_id=".$receipt_id." and payment_type=".$payment_type." and isdelete='0'");
	$row = brp_mysqli_fetch_all($select);
	return $row;
}

function get_billby_trn_details($dbcon,$id)
{
	$select = $dbcon->query("SELECT sum(bill_amount) as amount FROM `tbl_bill_by_bill_adjustment_transaction` where bill_ref=".$id." and isdelete=0 and company_id in (0,".$_SESSION['company_id'].")");
	$row = brp_mysqli_fetch_assoc($select);
	return $row;
}
function get_id_detail($dbcon,$table,$table_id,$select_id,$field)
{
	
	$q=$dbcon->query("select $field from $table where $table_id=$select_id");
	$row=brp_mysqli_fetch_array($q);
	return $row[$field];
}

function get_sales_order_by_allocation($dbcon,$id)
{
	$q=$dbcon->query("SELECT so.sales_order_id,s.sales_order_no from tbl_sales_ordertrn as so left join tbl_sales_order as s on s.sales_order_id=so.sales_order_id where so.sales_ordertrn_id='".$id."'");
	$row=brp_mysqli_fetch_array($q);
	return $row['sales_order_no'];
	
}

//Added by Dhruv
function get_total_receipt_payment($dbcon,$payment_type,$receiptid='')
{
	if(!empty($receiptid)){
		$where="and receipt_id= ".$receiptid." ";
	}else{
		$where="and receipt_id= 0";
	}	
	$q = $dbcon -> query("SELECT sum(amount) as t_amount FROM tbl_receipt_payment_trn WHERE payment_type=".$payment_type." and isdelete=0 and company_id in (0,".$_SESSION['company_id'].") ".$where." ");
	$r = brp_mysqli_fetch_assoc($q);
	if(empty($r['t_amount'])){
		return 0;
	}else{
		return $r['t_amount'];
	}
}
//End code by dhruv

//GST Reports - gstR1 Start - dhaval

function get_b2b_invoice($dbcon,$st_date,$end_date)
{
	
	
	$q = "select i.invoice_id, 
	(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as cgst_total,
	(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=0)  ) as sgst_total,
	(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=0)  ) as igst_total,
	(select sum(tcs_amount*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=0)  ) as tcs_total,
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.invoice_id) as total_count,l.cust_gst_reg from tbl_invoice as i 
	left join tbl_ledger as l on l.l_id=i.cust_id 
	where i.sales_type=1 and i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='0' or l.enable_sez='1'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_b2c_large_invoice($dbcon,$st_date,$end_date,$company_state)
{		
	$q = "select i.invoice_id, 
	(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=1 and i.g_total*i.currency_rate > 250000) ) as cgst_total,
	(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=1 and i.g_total*i.currency_rate > 250000) ) as sgst_total,
	(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0  and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=1 and i.g_total*i.currency_rate > 250000) ) as igst_total,
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.invoice_id) as total_count,l.cust_gst_reg from tbl_invoice as i 
	left join tbl_ledger as l on l.l_id=i.cust_id 
	where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='1' and i.g_total*i.currency_rate > 250000";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_b2c_small_invoice($dbcon,$st_date,$end_date,$company_state)
{		
	$q = "select i.invoice_id, 
	(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and IF(l.stateid!='$company_state',i.g_total*i.currency_rate <= 250000, i.g_total*i.currency_rate >0)=1 and l.cust_gst_reg='1') ) as cgst_total,
	(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and IF(l.stateid!='$company_state',i.g_total*i.currency_rate <= 250000, i.g_total*i.currency_rate >0)=1 and l.cust_gst_reg='1') ) as sgst_total,
	(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and IF(l.stateid!='$company_state',i.g_total*i.currency_rate <= 250000, i.g_total*i.currency_rate >0)=1 and l.cust_gst_reg='1') ) as igst_total,
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.invoice_id) as total_count,l.cust_gst_reg from tbl_invoice as i 
	left join tbl_ledger as l on l.l_id=i.cust_id 
	where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and IF(l.stateid!='$company_state',i.g_total*i.currency_rate <= 250000, i.g_total*i.currency_rate >0)=1 and l.cust_gst_reg='1'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}


function get_gst_nill_invoice($dbcon,$st_date,$end_date,$company_state)
{		
	/*$q = "select i.invoice_id,(select IFNULL(sum(product_amount * currency_rate),0) from tbl_invoicetrn where trancation_status=0 and invoice_id in(select i.invoice_id from tbl_invoice as i where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date')  and product_tax_cat=22 OR product_tax_cat=23 OR product_tax_cat=24) as total_amount 
		from tbl_invoice as i  
		where i.invoice_status=0 and i.invoice_date between '$st_date' and '$end_date'"; */

	$q = "select i.invoice_id,IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total_amount,count(i.invoice_id) as total_count
	from tbl_invoice as i
	left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id 
	where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and trn.product_tax_cat='22' OR trn.product_tax_cat='23' OR trn.product_tax_cat='24'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_crdr_note_registered($dbcon,$st_date,$end_date,$company_state)
{
	/*$q = "select i.sale_return_id, 
	(select sum(sale_return_cgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as cgst_total,
	(select sum(sale_return_sgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as sgst_total,
	(select sum(sale_return_igst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0  and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as igst_total,
	IFNULL(sum(i.sale_return_gtotal*i.currency_rate),0) as total,count(i.sale_return_id) as total_count,l.cust_gst_reg from tbl_sale_return as i 
	left join tbl_ledger as l on l.l_id=i.sale_return_customer 
	where i.isdelete='0' and i.sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg='0'"; */
	
	$q_credit = "select i.sale_return_id, 
	(select sum(sale_return_cgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as cgst_total,
	(select sum(sale_return_sgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as sgst_total,
	(select sum(sale_return_igst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0  and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as igst_total,
	IFNULL(sum(i.sale_return_gtotal*i.currency_rate),0) as total,count(i.sale_return_id) as total_count,l.cust_gst_reg from tbl_sale_return as i 
	left join tbl_ledger as l on l.l_id=i.sale_return_customer 
	where i.isdelete='0' and i.sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg='0'";
	
	$q_debit = "select i.debitnote_id, 
	(select sum(purchase_return_cgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id in (SELECT i.debitnote_id FROM `tbl_debitnote` as i left join tbl_ledger as l on l.l_id=i.vender_id where debit_note_status=0 and debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as cgst_total,
	(select sum(purchase_return_sgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id in (SELECT i.debitnote_id FROM `tbl_debitnote` as i left join tbl_ledger as l on l.l_id=i.vender_id where debit_note_status=0 and debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as sgst_total,
	(select sum(purchase_return_igst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0  and debitnote_id in (SELECT i.debitnote_id FROM `tbl_debitnote` as i left join tbl_ledger as l on l.l_id=i.vender_id where debit_note_status=0 and debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg=0) ) as igst_total,
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.debitnote_id) as total_count,l.cust_gst_reg from tbl_debitnote as i 
	left join tbl_ledger as l on l.l_id=i.vender_id 
	where i.debit_note_status='0' and i.debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg='0'";
	
	$q_advance = "select sum(p.trn_amount*p.currency_rate) as total,count(p.transaction_id) as total_count,sum(trn_gst) as gst,p.trn_voucher_id,r.receipt_date,l.stateid from tbl_advacne_receipt_trn as p left join tbl_receipt as r on r.receipt_id=p.trn_voucher_id  left join tbl_ledger as l on l.l_id=p.cust_id where l.cust_gst_reg='0' and r.receipt_date between '$st_date' and '$end_date' and p.advance_receipt_type='1' ";
	
	
	$row=array();
	
	$query_credit = $dbcon->query($q_credit);
	$row_credit = brp_mysqli_fetch_array($query_credit);
	
	$query_debit = $dbcon->query($q_debit);
	$row_debit = brp_mysqli_fetch_array($query_debit);
	
	$query_advance = $dbcon->query($q_advance);
	$row_advance = brp_mysqli_fetch_array($query_advance);
	
	if($row_advance['stateid']==$company_state)
	{
		$cgst_advance = (($row_advance['gst']/2)*$row_advance['total'])/100;
		$sgst_advance = (($row_advance['gst']/2)*$row_advance['total'])/100;
		$igst_advance=0;
	}
	else
	{
		$cgst_advance=0;
		$sgst_advance=0;
		$igst_advance = ($row_advance['gst']*$row_advance['total'])/100;
	}
	
	$row['total_count'] = $row_credit['total_count']+$row_debit['total_count']+$row_advance['total_count'];
	$row['total'] = $row_credit['total']+$row_advance['total']+$row_debit['total'];
	$row['cgst_total'] = $row_credit['cgst_total']+$row_debit['cgst_total']+$cgst_advance;
	$row['sgst_total'] = $row_credit['sgst_total']+$row_debit['sgst_total']+$sgst_advance;
	$row['igst_total'] = $row_credit['igst_total']+$row_debit['igst_total']+$igst_advance;
	
	return $row;
	
}

function get_crdr_note_unregistered($dbcon,$st_date,$end_date,$company_state)
{
	
	$q_credit = "select i.sale_return_id, 
	(select sum(sale_return_cgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=1) ) as cgst_total,
	(select sum(sale_return_sgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=1) ) as sgst_total,
	(select sum(sale_return_igst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0  and sale_return_id in (SELECT i.sale_return_id FROM `tbl_sale_return` as i left join tbl_ledger as l on l.l_id=i.sale_return_customer where isdelete=0 and sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg=1) ) as igst_total,
	IFNULL(sum(i.sale_return_gtotal*i.currency_rate),0) as total,count(i.sale_return_id) as total_count,l.cust_gst_reg from tbl_sale_return as i 
	left join tbl_ledger as l on l.l_id=i.sale_return_customer 
	where i.isdelete='0' and i.sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg='1'";
	
	$q_debit = "select i.debitnote_id, 
	(select sum(purchase_return_cgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id in (SELECT i.debitnote_id FROM `tbl_debitnote` as i left join tbl_ledger as l on l.l_id=i.vender_id where debit_note_status=0 and debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg=1) ) as cgst_total,
	(select sum(purchase_return_sgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id in (SELECT i.debitnote_id FROM `tbl_debitnote` as i left join tbl_ledger as l on l.l_id=i.vender_id where debit_note_status=0 and debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg=1) ) as sgst_total,
	(select sum(purchase_return_igst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0  and debitnote_id in (SELECT i.debitnote_id FROM `tbl_debitnote` as i left join tbl_ledger as l on l.l_id=i.vender_id where debit_note_status=0 and debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg=1) ) as igst_total,
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.debitnote_id) as total_count,l.cust_gst_reg from tbl_debitnote as i 
	left join tbl_ledger as l on l.l_id=i.vender_id 
	where i.debit_note_status='0' and i.debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg='1'";
	
	$q_advance = "select sum(p.trn_amount*p.currency_rate) as total,count(p.transaction_id) as total_count,sum(trn_gst) as gst,p.trn_voucher_id,r.receipt_date,l.stateid from tbl_advacne_receipt_trn as p left join tbl_receipt as r on r.receipt_id=p.trn_voucher_id  left join tbl_ledger as l on l.l_id=p.cust_id where l.cust_gst_reg='1' and r.receipt_date between '$st_date' and '$end_date' and p.advance_receipt_type='0' ";
	
	
	$row=array();
	$query_credit = $dbcon->query($q_credit);
	$row_credit = brp_mysqli_fetch_array($query_credit);
	
	$query_debit = $dbcon->query($q_debit);
	$row_debit = brp_mysqli_fetch_array($query_debit);
	
	$query_advance = $dbcon->query($q_advance);
	$row_advance = brp_mysqli_fetch_array($query_advance);
	
	if($row_advance['stateid']==$company_state)
	{
		$cgst_advance = (($row_advance['gst']/2)*$row_advance['total'])/100;
		$sgst_advance = (($row_advance['gst']/2)*$row_advance['total'])/100;
		$igst_advance=0;
	}
	else
	{
		$cgst_advance=0;
		$sgst_advance=0;
		$igst_advance = ($row_advance['gst']*$row_advance['total'])/100;
	}
	
	$row['total_count'] = $row_credit['total_count']+$row_debit['total_count']+$row_advance['total_count'];
	$row['total'] = $row_credit['total']+$row_debit['total']+$row_advance['total'];
	$row['cgst_total'] = $row_credit['cgst_total']+$row_debit['cgst_total']+$cgst_advance;
	$row['sgst_total'] = $row_credit['sgst_total']+$row_debit['sgst_total']+$sgst_advance;
	$row['igst_total'] = $row_credit['igst_total']+$row_debit['igst_total']+$igst_advance;
	
	return $row;
	
}

function get_export_invoice_gst($dbcon,$st_date,$end_date,$company_state)
{
	$q = "select i.invoice_id, 
	(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1') ) as cgst_total,
	(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1')  ) as sgst_total,
	(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1')  ) as igst_total,
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.invoice_id) as total_count,l.cust_gst_reg from tbl_invoice as i 
	left join tbl_ledger as l on l.l_id=i.cust_id 
	where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_tax_liability($dbcon,$st_date,$end_date,$company_state)
{
	
	$q_advance = "select sum(p.trn_amount*p.currency_rate) as total,count(p.transaction_id) as total_count,sum(trn_gst) as gst,p.trn_voucher_id,r.receipt_date,l.stateid from tbl_advacne_receipt_trn as p left join tbl_receipt as r on r.receipt_id=p.trn_voucher_id  left join tbl_ledger as l on l.l_id=p.cust_id where r.receipt_date between '$st_date' and '$end_date' and p.advance_receipt_type='0' ";
	
	$query_advance = $dbcon->query($q_advance);
	$row_advance = brp_mysqli_fetch_array($query_advance);
	
	if($row_advance['stateid']==$company_state)
	{
		$cgst_advance = (($row_advance['gst']/2)*$row_advance['total'])/100;
		$sgst_advance = (($row_advance['gst']/2)*$row_advance['total'])/100;
		$igst_advance=0;
	}
	else
	{
		$cgst_advance=0;
		$sgst_advance=0;
		$igst_advance = ($row_advance['gst']*$row_advance['total'])/100;
	}
	
	$row['total_count'] = $row_advance['total_count'];
	$row['total'] = $row_advance['total'];
	$row['cgst_total'] = $cgst_advance;
	$row['sgst_total'] = $sgst_advance;
	$row['igst_total'] = $igst_advance;
	
	return $row;
}

function get_hsn_summary($dbcon,$st_date,$end_date,$company_state)
{
	$q = "select i.invoice_id,invtrn.product_hsn_code,
	
	(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id left join tbl_invoicetrn as invtrn on invtrn.invoice_id=i.invoice_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' group by invtrn.product_hsn_code ) ) as cgst_total,
	
	(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id left join tbl_invoicetrn as invtrn on invtrn.invoice_id=i.invoice_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' group by invtrn.product_hsn_code )  ) as sgst_total,
	
	(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id left join tbl_invoicetrn as invtrn on invtrn.invoice_id=i.invoice_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' group by invtrn.product_hsn_code )  ) as igst_total,
	
	IFNULL(sum(i.g_total*i.currency_rate),0) as total,count(i.invoice_id) as total_count,l.cust_gst_reg from tbl_invoice as i 
	left join tbl_ledger as l on l.l_id=i.cust_id
	left join tbl_invoicetrn as invtrn on invtrn.invoice_id=i.invoice_id
	where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' group by invtrn.product_hsn_code";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}


//GST Reports - gstR1 End - dhaval

//-------------- GST 3B Reports start - Dhaval -----------

function get_outward_invoice($dbcon,$st_date,$end_date)
{
	
	$q = "select IFNULL(sum(itr.product_amount*itr.currency_rate),0) as total , IFNULL(sum(itr.cgst_tax_rate*itr.currency_rate),0) as cgst_rate , IFNULL(sum(itr.sgst_tax_rate*itr.currency_rate),0) as sgst_rate, IFNULL(sum(itr.igst_tax_rate*itr.currency_rate),0) as igst_rate 
	from tbl_invoice as i
	left join tbl_invoicetrn as itr  on i.invoice_id=itr.invoice_id
	where itr.trancation_status='0' and itr.product_tax_cat NOT IN (".GST_NILL_RATED.",".GST_EXEMPTED.",".GST_ZERO_RATED.",".NON_GST.") and i.invoice_date between '$st_date' and '$end_date' ";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_outward_invoice_zero($dbcon,$st_date,$end_date)
{
	
	$q = "select IFNULL(sum(product_amount*currency_rate),0) as total , IFNULL(sum(cgst_tax_rate*currency_rate),0) as cgst_rate , IFNULL(sum(sgst_tax_rate*currency_rate),0) as sgst_rate, IFNULL(sum(igst_tax_rate*currency_rate),0) as igst_rate from tbl_invoicetrn where trancation_status='0' and product_tax_cat='".GST_ZERO_RATED."'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_outward_invoice_nill($dbcon,$st_date,$end_date)
{
	
	$q = "select IFNULL(sum(product_amount*currency_rate),0) as total , IFNULL(sum(cgst_tax_rate*currency_rate),0) as cgst_rate , IFNULL(sum(sgst_tax_rate*currency_rate),0) as sgst_rate, IFNULL(sum(igst_tax_rate*currency_rate),0) as igst_rate from tbl_invoicetrn where trancation_status='0' and product_tax_cat IN (".GST_NILL_RATED.",".GST_EXEMPTED.")";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
}

function get_outward_invoice_non($dbcon,$st_date,$end_date)
{
	
	$q = "select IFNULL(sum(product_amount*currency_rate),0) as total , IFNULL(sum(cgst_tax_rate*currency_rate),0) as cgst_rate , IFNULL(sum(sgst_tax_rate*currency_rate),0) as sgst_rate, IFNULL(sum(igst_tax_rate*currency_rate),0) as igst_rate from tbl_invoicetrn where trancation_status='0' and product_tax_cat='".NON_GST."'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;

}

function get_unreg_supply($dbcon,$st_date,$end_date)
{
	
	$q = "select i.invoice_id,i.cust_id,l.stateid,(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0  and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=1 group by l.stateid) ) as igst_total,(select sum(product_amount*currency_rate) from tbl_invoicetrn where trancation_status=0  and invoice_id in (SELECT i.invoice_id FROM `tbl_invoice` as i left join tbl_ledger as l on l.l_id=i.cust_id where invoice_status=0 and invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg=1 group by l.stateid ) ) as total from tbl_invoice as i inner join tbl_ledger as l on l.l_id=i.cust_id where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date'  and l.cust_gst_reg='1' group by l.stateid ";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;
	
}

//-------------- GST 3B Reports end - Dhaval -----------

//-------------- Start For Maulik Kapatel Dispatch To Invoice --------------
function get_so_for_finance($dbcon,$vender_id,$id,$mode)
{	
	
	$query="select * from tbl_sales_order where sales_order_status=0 and approve_status!=0 and cust_id=".$vender_id." and company_id=".$_SESSION['company_id'];
	
	/* $query="select mst.sales_order_id,mst.sales_order_no from tbl_sales_order as mst
	where mst.sales_order_status=0 and mst.invoice_status=0 and cust_id=".$vender_id." and company_id=".$_SESSION['company_id']; */
	
	//var_dump($query);
	$str='';

	$rs_dispatch=$dbcon->query($query);
	$count = brp_mysqli_num_rows($rs_dispatch);
	if($count>0)
	{
		$str = '<option value="">Choose Sales Order</option>';
		$grn_no_array = [];
		while($rel=brp_mysqli_fetch_assoc($rs_dispatch))
		{	
			
			if(!in_array($rel['sales_order_no'], $grn_no_array)){
				$sel=''; 
				if($rel['sales_order_id']==$id)
				{$sel ="selected='selected'";}
				$str .= '<option '.$sel.' value="'.$rel['sales_order_id'].'">'.$rel['sales_order_no'].'</option>';
			}
			$grn_no_array[] = $rel['sales_order_no'];
			
		}
	}
	else
	{
		$str='0';
	}
	return $str;
}

function invoice_time_check_stock($dbcon,$product_id){
	$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
	$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			
	$product_type_arr = array("0", "1", "2", "3", "4", "5");
	if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
		if(!empty($POST['unit_id'])){
			$unit_id=$POST['unit_id'];
		}else{
			$unit_id=$get_pro_type_rel['product_base_unit'];
		}
		$current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
		
		$where=" and trancation_status!='2' and invoice_id='0'";
		$unclear_qty = get_unclear_stock($dbcon,$product_id,$unit_id,'tbl_invoicetrn','product_qty','product_id',$where);
		$stock = $current_stock-$unclear_qty;
	}
	return $stock;
}


function get_landing_cost($dbcon,$product,$check_price)
{
	//$check_price = implode(",",$check_price);
	
	if($check_price[0]=="LAST_PURCHASE")
	{
		$query = $dbcon->query("select product_rate,product_id from tbl_potrancation where potrancation_status='0' and product_id='$product'  limit 0,1");
		$count=brp_mysqli_num_rows($query);
		if($count>0)
		{
			$row = brp_mysqli_fetch_assoc($query);
			$product_rate = $row['product_rate'];
		}
		else
		{
			$product_rate = 0;
		}
	}
	
	return $product_rate;
}


function check_bom($dbcon,$product)
{
	
	$query = $dbcon->query("select bom_id from tbl_bom where bom_product='$product'");
	$count = brp_mysqli_num_rows($query);
	if($count==0)
	{
		return 0;
	}
	else
	{
		$row = brp_mysqli_fetch_assoc($query);
		return $row['bom_id'];
	}
}

function check_product_price_list($dbcon,$product,$price_list_id)
{
	$sel =  $dbcon->query("select * from tbl_price_list_details where product_id='$product' and price_list_id='$price_list_id'");
	$count = brp_mysqli_num_rows($sel);
	return $count;
}

function check_count_product_price_list_parent($dbcon,$parent)
{
	$sel =  $dbcon->query("select * from tbl_price_list_details where parent_id='$parent'");
	$count = brp_mysqli_num_rows($sel);
	return $count;
}

function check_product_price_list_child($dbcon,$parent,$product,$price_list_id)
{
	$sel =  $dbcon->query("select * from tbl_price_list_details where parent_id='$parent' and product_id='$product' and price_list_id='$price_list_id'");
	$count = brp_mysqli_num_rows($sel);
	return $count;
}

function check_parent_price_list($dbcon,$product,$price_list_id)
{
	$sel = $dbcon->query("select p.*,pro.product_name from tbl_price_list_details as p left join product_mst as pro on pro.product_id=p.parent_id where p.product_id='$product' and p.price_list_id='$price_list_id'");
	$row=brp_mysqli_fetch_array($sel);
	return $row;
}

function bom_current_level_pricelist($dbcon,$product,$eid)
{
	$sel = $dbcon->query("select bom_level from tbl_price_list_details where product_id='$product' and price_list_id='$eid'");
	$row = brp_mysqli_fetch_array($sel);
	
	$current_level = $row['bom_level'];
	
	return $current_level;
}

function get_parent_price_list($dbcon,$parent_id,$price_list_id)
{
	$sel = $dbcon->query("select * from tbl_price_list_details where price_list_id='$price_list_id' and product_id='$parent_id'");
	$row = brp_mysqli_fetch_array($sel);
	
	$parent = $row['parent_id'];
	
	return $parent;
}

function get_bom_verion_price_list($dbcon,$parent_id,$price_list_id)
{
	$sel = $dbcon->query("select * from tbl_price_list_details where price_list_id='$price_list_id' and product_id='$parent_id'");
	$row = brp_mysqli_fetch_array($sel);
	
	$bom_version_id = $row['bom_version_id'];
	
	return $bom_version_id;
}

function get_chart_of_account_tree($dbcon,$group_id){
    if($group_id){
        $html = '';
        $group_qry = "SELECT g_id, g_name FROM `tbl_group` WHERE g_status = 0 And `g_pid` IN (".$group_id.")";
        $result = brp_mysqli_query($dbcon,$group_qry);
        $group_data = brp_mysqli_fetch_all($result);
        
        if($group_data){ 
            //$html .= '<ul id="subgroup_'.$group_id.'" class="subgroups" style="display:none">';
            foreach($group_data as $group){
                $html .= '<div class = "tree-folder" id="li_'.$group['g_id'].'">
                            <div class="tree-folder-header">
                                <i class="fa fa-folder" onClick="show_sub_group(this,'.$group['g_id'].');"></i>
                                <div class="tree-folder-name">';
                $html .= '<span id="group_name_'.$group['g_id'].'">'.brp_ucwords(brp_strtolower($group['g_name'])).'</span>';
                
                //if(in_array(FINANCE_CHARTS_OF_ACCOUNT_EDIT,$bulkAccessArray)){
                    $edit_icon = '<i class="fa fa-pencil" onClick="edit_group('.$group['g_id'].');"></i>';
                //}
                $delete_icon = '<i class="fa fa-trash-o" onClick="delete_group('.$group['g_id'].')"></i>';
                $has_child = $dbcon->query("select g_id FROM `tbl_group` WHERE g_status = 0 and g_pid =".$group['g_id']." and company_id=".$_SESSION['company_id'])
                        ->fetch_object()->g_id;

                //$active_icon = '<i class="fa fa-check-square-o"></i>';
                if($has_child){
                    $delete_icon = '';
                    $active_icon = '';
                }
                //if(in_array(FINANCE_CHARTS_OF_ACCOUNT_CREATE,$bulkAccessArray)){
                    $add_icon = '<i class="fa fa-plus" onClick="add_group('.$group['g_id'].')"></i>';
                //}
                $html .= '<div class="tree-actions">
                        '.$add_icon.'
                        '.$edit_icon.'
                        '.$delete_icon.'
                        '.$active_icon;
                $html .= '</div>';
                $html .= '<span class="group-balance flt-right">'.indian_number(get_group_balance($dbcon,$group['g_id']),2).'</span>
                    </div>
                </div>';
                if($has_child){
                    $html .= '<div class="tree-folder-content" id="subgroup_'.$group['g_id'].'" style="display:none">';
                    $html .= get_chart_of_account_tree($dbcon, $group['g_id']);
                    $html .= get_ledgers_by_group($dbcon, $group['g_id']);
                    $html .= '</div>';
                } else {
                    $html .= '<div class="tree-folder-content" id="subgroup_'.$group['g_id'].'" style="display:none">';
                    $html .= get_ledgers_by_group($dbcon, $group['g_id']);
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
        }
        return $html;
    }
}

// get all ledgers by group
function get_ledgers_by_group($dbcon,$group_id){
    if($group_id){
        $ledger_qry = "SELECT l_id as id, l_name as name FROM `tbl_ledger` WHERE l_status = 0 AND company_id=".$_SESSION['company_id']." AND `l_group` = ".$group_id;
        $result = brp_mysqli_query($dbcon,$ledger_qry);
        $ledgers = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

        if($ledgers){
            $html = '';
            foreach ($ledgers as $ledger){
                $html .= '<div class="tree-item" style="display: block;">';
                $html .= '<i class="tree-dot"></i><div class="tree-item-name"><i class="fa fa-file-o"></i>&nbsp;'.$ledger['name'].'</div>';
                $html .= '<span class="group-balance flt-right">'.indian_number(get_ledger_balance($dbcon,$ledger['id']),2).'</span>';
                $html .= '</div>';
            }
        }
    }
    return $html;
}

// get total balance of group
function get_group_balance($dbcon,$group_id){
    $dates = get_current_financial_year();
    extract($dates);

    $sub_qry = "SELECT g_id AS sub_group FROM `tbl_group` WHERE g_status = 0 And `g_pid`= ".$group_id;
    $result = mysqli_query($dbcon,$sub_qry);
    $sub_groups = mysqli_fetch_all($result,MYSQLI_ASSOC);

    $total = 0;
    if($sub_groups){
        //echo 'in group';
        foreach ($sub_groups as $sub_group) {
            $sub_group_id = $sub_group['sub_group'];
            if($sub_group_id){
                $sub_groups = implode(',',get_sub_group($dbcon, $sub_group_id));
                $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE l_status = ".ACTIVE." AND l_group IN (".$sub_groups.")";
                $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
                //print_r($sub_ledger);die();

                $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,
                sum(debitamount) as debitamount ,sum(creditamount) as creditamount,
                (SELECT g_name FROM `tbl_group` WHERE `g_id` = ".$sub_group_id.") as group_name
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".DEBIT." and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= ".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".CREDIT." and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = ".ACTIVE." AND company_id = ".$_SESSION['company_id']." 
                    AND cust.l_id IN (".$sub_ledger.")
                ";

                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result);
                if($ca_result){
                    foreach ($ca_result as $value) {
                        $balance_type = $value['balance_typeid'];
                        //$balance_type = ($sub_group_id == SUNDRY_DEBTORS) ? '2' : $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount']-$value['creditamount']);

                        $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
				                where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id IN ('.$sub_ledger.') 
                                GROUP BY payment.entry_type
                                ORDER BY payment.ref_date
                                ';
                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                        if($payment_result){
                            foreach ($payment_result as $payment) {
                                if($payment['entry_type']==2){
                                    $balance += $payment['amount'];

                                }else{
                                    $balance -= $payment['amount'];
                                }
                            }
                        }
                        $total = $total + $balance;
                    }
                }
            }
        }
    }

    // get total from all ledgers related to group
    $all_ledger_qry = "SELECT l_id FROM `tbl_ledger` WHERE l_status = 0 AND l_group IN (".$group_id.")";
    $result = mysqli_query($dbcon, $all_ledger_qry);
    $all_ledger = mysqli_fetch_all($result,MYSQLI_ASSOC);
    //print_r($all_ledger);die();
    foreach ($all_ledger as $ledger){
        $ledger_balance = get_ledger_balance($dbcon, $ledger['l_id']);
        $total = $total + $ledger_balance;
    }


    //die($total);
    return abs($total);
}

function get_ledger_balance($dbcon,$ledger_id){
    $dates = get_current_financial_year();
    extract($dates);

    $ledger_qry = "select sum(opn_balance) as opening_balance,balance_typeid,sum(debitamount) as debitamount,
                sum(creditamount) as creditamount,l_name as ledger_name, l_id as ledger_id
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=0 and table_name!='tbl_ledger' 
                            and entry_type= 2 and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= 0 and table_name!='tbl_ledger' 
                            and entry_type= 1 and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = 0 AND company_id = ".$_SESSION['company_id']." 
                    AND cust.l_id IN (".$ledger_id.")
                    group by cust.l_id
                    Order by l_name ASC ";

    $result = mysqli_query($dbcon, $ledger_qry);
    $ledger_data = mysqli_fetch_all($result,MYSQLI_ASSOC);

    if($ledger_data){
        foreach ($ledger_data as $value) {
            $balance_type = $value['balance_typeid'];
            $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
            $balance = $op_balance + ($value['debitamount']-$value['creditamount']);

            $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
				                where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id IN ('.$ledger_id.') 
                                GROUP BY payment.entry_type
                                ORDER BY payment.ref_date
                                ';
            $result = mysqli_query($dbcon, $payment_qry);
            $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

            if($payment_result){
                foreach ($payment_result as $payment) {
                    if($payment['entry_type']==2){
                        $balance += $payment['amount'];

                    }else{
                        $balance -= $payment['amount'];
                    }
                }
            }
        }
    }
    return abs($balance);
}


function get_price_list($dbcon,$select="")
{
	$str="";
	$sel = $dbcon->query("select * from tbl_price_list where isdelete='0'");
	while($row = brp_mysqli_fetch_array($sel))
	{
		if($select!=''&&$select==$row['price_list_id'])
		{
			$sel1="selected";
		}
		else
		{
			$sel1="";
		}
		$str.="<option ".$sel1." value='".$row['price_list_id']."'>".$row['price_list_version']."</option>";
	}
	return $str;
}

function get_price_from_price_list($dbcon,$version_id,$product_id)
{
	$sel = $dbcon->query("select * from tbl_price_list_details where price_list_id='$version_id' and product_id='$product_id' and isdelete='0'");
	$row = brp_mysqli_fetch_array($sel);

	return $row;
}

function get_invoice_total($dbcon,$month,$ledger,$year="")
{
	$where="";

	if($year!='')
	{
		$where.=" and YEAR(invoice_date)=$year";
	}else{
		$current_year = date("Y");
		$where.=" and YEAR(invoice_date)=$current_year";
	}

	$q = "select IFNULL(sum(g_total),0) as total from tbl_invoice where cust_id='$ledger' and invoice_status='0' and MONTH(invoice_date)=$month".$where;
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_assoc($query);
	return $row['total'];
}

function get_invoice_total_by_product($dbcon,$product)
{
	$financial_year=getFinacialyear_data($dbcon);

    $start_date = date("m",strtotime($financial_year['financial_start_date']));
    $end_date = date("m",strtotime($financial_year['financial_end_date']));
    $current_date = date("m");

    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");

	$q= "select IFNULL(sum(tr.total),0) as total,i.invoice_date from tbl_invoicetrn as tr 
	left join tbl_invoice as i on i.invoice_id=tr.invoice_id
	where tr.trancation_status='0' and tr.product_id='$product' and MONTH(invoice_date) between '$start_date' and '$current_date' and YEAR(invoice_date) between '$start_year' and '$current_year' and tr.user_id='$_SESSION[user_id]' and tr.trancation_status='0'";
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_assoc($query);
	return $row['total'];
}

function get_invoice_total_forecast($dbcon,$ledger)
{
	$financial_year=getFinacialyear_data($dbcon);

    $start_date = date("m",strtotime($financial_year['financial_start_date']));
    $end_date = date("m",strtotime($financial_year['financial_end_date']));
    $current_date = date("m");

    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");

    $q = "select IFNULL(sum(g_total),0) as total from tbl_invoice where cust_id='$ledger' and invoice_status='0' and MONTH(invoice_date) between '$start_date' and '$current_date' and YEAR(invoice_date) between '$start_year' and '$current_year' and user_id='$_SESSION[user_id]' ";
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_assoc($query);
	return $row['total'];
}
function get_invoice_current_forecast($dbcon,$ledger,$month)
{
	$financial_year=getFinacialyear_data($dbcon);

    $start_date = date("m",strtotime($financial_year['financial_start_date']));
    $end_date = date("m",strtotime($financial_year['financial_end_date']));
    $current_date = date("m");

    $start_year= date("Y",strtotime($financial_year['financial_start_date']));
    $end_year = date("Y",strtotime($financial_year['financial_end_date']));
    $current_year = date("Y");

    $q = "select IFNULL(sum(total),0) as total from tbl_invoicetrn as invtrn left join tbl_invoice as inv on inv.invoice_id=invtrn.invoice_id where inv.cust_id='$ledger' and inv.invoice_status='0' AND invtrn.trancation_status=0 and MONTH(inv.invoice_date) = '$month' and YEAR(inv.invoice_date) between '$start_year' and '$current_year' AND inv.company_id = ".$_SESSION['company_id'];
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_assoc($query);
	return $row['total'];
}
function get_gst_document($dbcon,$st_date,$end_date)
{		
	$companyid = $_SESSION['company_id'];
	
	$q = "select 
		(select count(invoice_id) from tbl_invoice where invoice_status='0' and company_id='$companyid' and invoice_date between '$st_date' and '$end_date')  as inv_count,
		(select count(sale_return_id) from tbl_sale_return where isdelete='0' and company_id='$companyid' and sale_return_date between '$st_date' and '$end_date' and is_without_item='0') as cr_count,
		 (select count(journal_id) from tbl_journal where journal_status='0' and company_id='$companyid' and gst_nature='97' and journal_date between '$st_date' and '$end_date') as jv_count
		 ";
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	$total=$row['inv_count']+$row['cr_count']+$row['jv_count'];
	return $total;
}

function get_gst_document_by_type($dbcon,$st_date,$end_date,$type,$where="")
{
	$companyid = $_SESSION['company_id'];

	if($type=='invoice')
	{
		$q = "select count(invoice_id) as total from tbl_invoice where invoice_status='0' and company_id='$companyid' and invoice_date between '$st_date' and '$end_date'";
	}
	if($type=='cr_note')
	{
		$q = "select count(sale_return_id) as total from tbl_sale_return where isdelete='0' and company_id='$companyid' and sale_return_date between '$st_date' and '$end_date' and is_without_item='0'";
	}
	if($type=='jv')
	{
		$q = "select count(journal_id) as total from tbl_journal where journal_status='0' and company_id='$companyid' and gst_nature='97' and journal_date between '$st_date' and '$end_date'";
	}

	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row['total'];
}

function get_import_goods_itc($dbcon,$st_date,$end_date)
{
	
	$q = "select IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total , IFNULL(sum(trn.cgst_tax_rate*trn.currency_rate),0) as cgst_rate , IFNULL(sum(trn.sgst_tax_rate*trn.currency_rate),0) as sgst_rate, IFNULL(sum(trn.igst_tax_rate*trn.currency_rate),0) as igst_rate from tbl_potrancation as trn
		left join product_mst as p on p.product_id=trn.product_id
		left join tbl_pono as po on po.po_id=trn.po_id
		 where trn.potrancation_status='0' and p.product_type!='".SERVICE."' and po_date between '$st_date' and '$end_date'  and po.company_id='$_SESSION[company_id]' ";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;

}

function get_import_service_itc($dbcon,$st_date,$end_date)
{
	
	$q = "select IFNULL(sum(trn.product_amount*trn.currency_rate),0) as total , IFNULL(sum(trn.cgst_tax_rate*trn.currency_rate),0) as cgst_rate , IFNULL(sum(trn.sgst_tax_rate*trn.currency_rate),0) as sgst_rate, IFNULL(sum(trn.igst_tax_rate*trn.currency_rate),0) as igst_rate from tbl_potrancation as trn
		left join product_mst as p on p.product_id=trn.product_id
		left join tbl_pono as po on po.po_id=trn.po_id
		where trn.potrancation_status='0' and p.product_type='".SERVICE."' and po_date between '$st_date' and '$end_date' and po.company_id='$_SESSION[company_id]'";
	
	$query = $dbcon->query($q);
	$row = brp_mysqli_fetch_array($query);
	return $row;

}

function get_aging_payable_ledger_by_slab($dbcon,$ledger_id,$start_day,$end_day,$bill_status_on,$start_date,$end_date)
{
	//return $ledger_id;
	// $q= "SELECT cust_id,
 //    DATEDIFF($bill_status_on,invoice_date) AS days_past_due,
 //    SUM(IF(days_past_due = 0,g_total,0)),
 //    SUM(IF(days_past_due BETWEEN 1 AND 30, g_total, 0)),
 //    SUM(IF(days_past_due BETWEEN 31 AND 60, g_total, 0)),
 //    SUM(IF(days_past_due BETWEEN 61 AND 90, g_total, 0)),
 //    SUM(IF(days_past_due > 90, g_total, 0))
	// FROM tbl_invoice
	// GROUP BY cust_id";
	$q="select SUM(IF(DATEDIFF('$bill_status_on', invoice_date) BETWEEN '$start_day' AND '$end_day', g_total, 0)) as inv_total from tbl_invoice where invoice_date between '$start_date' and '$end_date' and cust_id='$ledger_id' and invoice_date < '$bill_status_on' and company_id='$_SESSION[company_id]' and invoice_status='0'";
	$q_inv = $dbcon->query($q);
	$r_inv = brp_mysqli_fetch_array($q_inv);
	$inv_total = $r_inv['inv_total'];
	//return round($inv_total,2);
	return $inv_total;
}

function get_due_purchase_bill_by_cust($dbcon,$cust,$edit_id='')
{
	$qry = "SELECT po_no as ref,g_total as due_amount,po_id as id,2 as 'ref_type' FROM `tbl_pono` WHERE `payment_status` != 1 and vender_id=".$cust." union SELECT bill_ref_no as ref,due_amount,bill_opening_id as id,1 as 'ref_type' FROM `tbl_ledger_billbybill_opening` WHERE `payment_status` != 1 and isdelete=0 and bill_ledger_id=".$cust."";
	$select = $dbcon->query($qry);
	//$row=brp_mysqli_fetch_assoc($select);
	$str='';
	$str.='<option value="0">--Select Ref No--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['po_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['id'].'" '.$sel.' data-type="'.$row['ref_type'].'" data-dueamount="'.$row['due_amount'].'" >'.$row['ref'].'</option>';
		
	}
	return $str;
}

function get_due_by_cust_jv($dbcon,$cust,$edit_id='')
{
	$qry = "SELECT po_no as ref,g_total as due_amount,po_id as id,2 as 'ref_type' FROM `tbl_pono` WHERE `payment_status` != 1 and vender_id=".$cust."

		union SELECT invoice_no as ref,g_total as due_amount,invoice_id as id,0 as 'ref_type' FROM `tbl_invoice` WHERE `payment_status` != 1 and sale_return_status =0 and cust_id=".$cust."
		
		union SELECT bill_ref_no as ref,due_amount,bill_opening_id as id,1 as 'ref_type' FROM `tbl_ledger_billbybill_opening` WHERE `payment_status` != 1 and isdelete=0 and bill_ledger_id=".$cust."";
	$select = $dbcon->query($qry);
	//$row=brp_mysqli_fetch_assoc($select);
	$str='';
	$str.='<option value="0">--Select Ref No--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['po_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['id'].'" '.$sel.' data-type="'.$row['ref_type'].'" data-dueamount="'.$row['due_amount'].'" >'.$row['ref'].'</option>';
		
	}
	return $str;
}

function clear_bank_entry($dbcon,$general_id)
{
	$q = $dbcon->query("select g.table_id,j.journal_id from tbl_general_book as g left join tbl_journal_trn as j on j.journal_trn_id=g.table_id where general_book_id='$general_id'");
	$row = brp_mysqli_fetch_array($q);

	$table_id = $row['journal_id'];

	$q1 = $dbcon->query("select journal_trn_id from tbl_journal_trn where journal_id='$table_id'");
	while($row1 = brp_mysqli_fetch_assoc($q1))
	{
		$info_gen['cleared_status'] = 1;
		update_record('tbl_general_book',$info_gen,"table_id= ".$row1['journal_trn_id']." " , $dbcon);
	}

	if($table_id)
	{
		return 1;
	}
	else
	{
		return 0;
	}

}

function get_product_tds($dbcon,$product,$vendor)
{
	$q = $dbcon->query("select ledger_id from product_mst where product_id='$product'");
	$row = brp_mysqli_fetch_array($q);
	$product_ledger_id = $row['ledger_id'];

	if($product_ledger_id!=0)
	{
		$q1 = $dbcon->query("select party_pay_cat,enable_tds from tbl_ledger where l_id='$product_ledger_id'");
		$row1 = brp_mysqli_fetch_assoc($q1);
		$product_payee = $row1['party_pay_cat'];

		$q2 = $dbcon->query("select party_pay_cat,enable_tds from tbl_ledger where l_id='$vendor' ");
		$row2 = brp_mysqli_fetch_assoc($q2);
		$vendor_payee = $row2['party_pay_cat'];

		$q3 = $dbcon->query("select tc.*,t.tds_cat_id,t.tds_cat_name,t.effected_ledger_id from tbl_tds_tax_category_detail as tc 
			left join tbl_tds_tax_category as t on tc.tds_cat_id=t.tds_cat_id
		 	where tc.tds_cat_id='$product_payee' and tc.tds_payee='$vendor_payee'");
		$row3 = brp_mysqli_fetch_assoc($q3);
		
		return $row3;
	}
	else
	{
		return "0";
	}

}

function get_tds_percentage($dbcon,$cust,$alias)
{
	$query = $dbcon->query("select m_pan,party_pay_cat from tbl_ledger where l_id='$cust'");
	$row = brp_mysqli_fetch_array($query);
	$pan = $row['m_pan'];
	$cust_payee = $row['party_pay_cat'];
	//return $cust_payee;exit;
	$query1 = $dbcon->query("select td.tds_with_pan,td.tds_without_pan,l.ledger_alias,t.tds_cat_name 
		from tbl_tds_tax_category_detail as td 
		left join tbl_tds_tax_category as t on t.tds_cat_id=td.tds_cat_id
		left join tbl_ledger as l on l.l_id=t.effected_ledger_id
		where td.tds_cat_detail_id='$cust_payee'
		");
	
	$row1=brp_mysqli_fetch_assoc($query1);
	if($pan=='' || $pan=='0')
	{
		return $row1['tds_without_pan'];
	}
	else
	{
		return $row1['tds_with_pan'];
	}
}

function f_get_group_ledger_price_list($dbcon,$sales_group,$ledger_id,$where=""){

	$str='';
	
	$query="select * from tbl_ledger as pro where l_status=0 ".$where." and company_id = $_SESSION[company_id] and l_group IN ($sales_group) order by TRIM(l_name) ASC";
	
	$rs_dispatch=$dbcon->query($query);	
	if(in_array('0', $ledger_id)){ $psel='selected="selected"';}
	$str .= '<option value="">--select ledger--</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{	
		$sel=''; 
		if(in_array($rel['l_id'], $ledger_id))
			{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$rel['l_id'].'">'.$rel['l_name'].'</option>';
	}
	return $str;
}


function get_unsettled_profit_loss($dbcon,$start_date,$end_date)
{
    $fa_query = "SELECT (select IFNULL(sum(amount),0) from tbl_general_book where entry_type=1 and ledger_id=gb.ledger_id and genral_book_status=0) as cr_amount,(select IFNULL(sum(amount),0) from tbl_general_book where entry_type=2 and ledger_id=gb.ledger_id  and genral_book_status=0 ) as db_amount
        FROM `tbl_general_book` gb 
        LEFT join tbl_ledger as led ON led.l_id= gb.ledger_id 
        LEFT join tbl_group as gro ON gro.g_id=led.l_group 
        WHERE led.l_status = ".ACTIVE."
            AND gb.genral_book_status = ".ACTIVE."        
            AND led.`l_group` = ".PROFIT_LOSS."
            AND led.company_id = ".$_SESSION['company_id']."
            AND gb.ref_date < '$start_date'
        GROUP BY gb.ledger_id";

    $query1 = $dbcon->query($fa_query);
        
    $ra_query = brp_mysqli_fetch_assoc($query1);

    $row=array();
    $row['credit'] = $ra_query['cr_amount'];
    $row['debit'] = $ra_query['db_amount'];

    //get p&l ledgers 

    $pal_query = "select (select sum(opn_balance) from tbl_ledger where l_group='".PROFIT_LOSS."' and  balance_typeid='1') as credit_opn , (select sum(opn_balance) from tbl_ledger where l_group='".PROFIT_LOSS."' and balance_typeid='2') as debit_opn";
    $query2 = $dbcon->query($pal_query);
    $pal_select = brp_mysqli_fetch_array($query2);

    $row['opening'] = $pal_select['credit_opn']-$pal_select['debit_opn'];

    return json_encode($row);
}

function get_settled_profit_loss($dbcon,$start_date,$end_date)
{
    $fa_query = "SELECT (select IFNULL(sum(amount),0) from tbl_general_book where entry_type=1 and ledger_id=gb.ledger_id and genral_book_status=0) as cr_amount,(select IFNULL(sum(amount),0) from tbl_general_book where entry_type=2 and ledger_id=gb.ledger_id  and genral_book_status=0 ) as db_amount
        FROM `tbl_general_book` gb 
        LEFT join tbl_ledger as led ON led.l_id= gb.ledger_id 
        LEFT join tbl_group as gro ON gro.g_id=led.l_group 
        WHERE led.l_status = ".ACTIVE."
            AND gb.genral_book_status = ".ACTIVE."        
            AND led.`l_group` = ".PROFIT_LOSS."
            AND led.company_id = ".$_SESSION['company_id']."
            AND gb.ref_date between '$start_date' and '$end_date'
        GROUP BY gb.ledger_id";

    $query1 = $dbcon->query($fa_query);
        
    $ra_query = brp_mysqli_fetch_assoc($query1);

    $row=array();
    $row['credit'] = $ra_query['cr_amount'];
    $row['debit'] = $ra_query['db_amount'];

    return json_encode($row);
}
function get_invoice_taxable_total($dbcon){
	$query = $dbcon->query("SELECT SUM(invs.g_total) as g_total, SUM(invtrn.product_amount) as total FROM tbl_invoicetrn as invtrn LEFT JOIN tbl_invoice as invs ON invs.invoice_id = invtrn.invoice_id WHERE invs.invoice_status = 0 AND invtrn.trancation_status = 0 AND invs.company_id = '".$_SESSION['company_id']."'");
	$res = brp_mysqli_fetch_assoc($query);
	return $res['g_total'].','.$res['total'];
}

function get_salesorder_invoicedone($dbcon, $sales_ordertrn_id, $invoice_id){
	$query = "select (select sum(product_qty) from tbl_invoicetrn as itrn where itrn.trancation_status=0 and itrn.sales_ordertrn_id=strn.sales_ordertrn_id) as invoice_qty, strn.product_qty, strn.sales_ordertrn_id, strn.sales_order_id from tbl_sales_ordertrn as strn where sales_ordertrn_id=".$sales_ordertrn_id;

	$result = $dbcon->query($query);
	$row =  brp_mysqli_fetch_array($result);

	$remaning_invoice_qty = $row['product_qty'] - $row['invoice_qty'];

	if(number_format($remaning_invoice_qty,4,".","") <= 0){
		$info_so_trans['remaning_invoice_qty'] =  0 ;
		$info_so_trans['invoice_status'] =  1 ;
	}else{
		$info_so_trans['remaning_invoice_qty'] = $remaning_invoice_qty;
		$info_so_trans['invoice_status'] =  0;
	}
	$update_sotransid=update_record('tbl_sales_ordertrn', $info_so_trans,"sales_ordertrn_id=".$sales_ordertrn_id , $dbcon);
	
	$sales_order = "select (select count(sales_ordertrn_id) from tbl_sales_ordertrn strn where invoice_status=1 and strn.sales_ordertrn_status=0 and strn.sales_order_id=so.sales_order_id ) as done_inv, (select count(sales_ordertrn_id) from tbl_sales_ordertrn as strn where strn.sales_ordertrn_status =0 and strn.sales_order_id = so.sales_order_id) as total_trn from tbl_sales_order as so where sales_order_id=".$row['sales_order_id'];
	$result_sales = $dbcon->query($sales_order);
	$row_sales = brp_mysqli_fetch_array($result_sales);	

	if(number_format($row_sales['total_trn'],4,".","")==number_format($row_sales['done_inv'],4,".","")){
		$info['invoice_status']	= 1;
		//$info['used_invoice_id'] = $invoice_id;
		$updatesoid=update_record('tbl_sales_order', $info, "sales_order_id= '".$row['sales_order_id']."' " , $dbcon);
	}else{
		$info['invoice_status']	= 0;
		//$info['used_invoice_id'] = $invoice_id;
		$updatesoid=update_record('tbl_sales_order', $info, "sales_order_id= '".$row['sales_order_id']."' " , $dbcon);
	}
}

?>
