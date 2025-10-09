<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/* Dhruv start code*/
function f_get_group_ledger($dbcon,$sales_group,$where,$ledger_id=''){

	$str='';

	//print_r($ledger_id);exit;
	
	$query="select * from tbl_ledger as pro where l_status=0 ".$where." and company_id = $_SESSION[company_id] and l_group IN ($sales_group) order by TRIM(l_name) ASC";
	$rs_dispatch=$dbcon->query($query);	
	$str .= '<option value="0">--select ledger--</option>';
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

	$qry="SELECT l.stateid,l.l_name,sm.gst_state_code FROM `tbl_ledger` as l left join state_mst as sm on sm.stateid= l.stateid
		where l.l_id=".$cust_id.""; 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result['gst_state_code'].','.$result['stateid'];
}

function get_grossbalance($dbcon,$cust_id){

	$qry="SELECT sum(i.g_total) as gtotal 
FROM `tbl_invoice` as i
left join tbl_financial_year as fy on fy.financial_year_id=i.financial_year_id and fy.current_status=1
where i.cust_id=".$cust_id."";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result['gtotal'];
}

function get_hsn_details($dbcon,$code){

	$qry="SELECT hsn_id,hsn_code,sale_gst FROM `mst_hsn_code` where hsn_status=2 and hsn_code=".$code."";
 
 	$result=brp_mysqli_fetch_assoc($dbcon->query($qry));
 	return $result;
}

function get_bill_sundry_ledger($dbcon){
	$str='';
	
	$query="select * from tbl_ledger where l_status=0 ".$where." and company_id = $_SESSION[company_id] and enable_bill_sunfry=1 order by l_id";
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

function submitEwayApi($data){
	//$url="https://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/authenticate?action=ACCESSTOKEN&aspid=1664451121&password=brp@%23123&gstin=34AACCC1596Q002&username=TaxProEnvPON&ewbpwd=abc34*";
//print_r($data);exit;

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://gstsandbox.charteredinfo.com/ewaybillapi/dec/v1.03/ewayapi?aspid=1664451121&action=GENEWAYBILL&Gstin=34AACCC1596Q002&password=brp@%23123&AuthToken=wLwHeHALRoWtbWU3ShGw61Rzm&user_name=TaxProEnvPON",
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
	    "cache-control: no-cache",
	    "postman-token: bb72f485-cc49-a87b-08a6-524f1372a3fd"
	  ),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
	  echo "cURL Error #:" . $response;
	} else {
	  echo $response;
	}
}

/*Dhruv end code*/

// dhaval upadhyay code : start 

function get_invoice_by_cust($dbcon,$cust,$edit_id='',$where='')
{
	$qry = "select invoice_id,invoice_no,invoice_date,g_total from tbl_invoice where invoice_status='0' and cust_id='$cust' ".$where;
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
		
		$str.='<option value="'.$row['invoice_id'].'" '.$sel.'>'.$row['invoice_no'].'</option>';
		
	}
	echo $str;
}

function get_product_from_invoice($dbcon,$invoice,$edit_id='',$where='')
{
	$qry = "select trn.product_id,trn.invoice_id,p.product_id,p.product_name from tbl_invoicetrn as trn left join product_mst as p on p.product_id=trn.product_id where trn.invoice_id='$invoice' and trn.trancation_status='0' ".$where;
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Product--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['product_id']==$edit_id)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['product_id'].'" '.$sel.'>'.$row['product_name'].'</option>';
		
	}
	return $str;
}

function get_tax_category_new($dbcon,$eid='')
{
	$qry = "select * from tbl_tax_category where isdelete='0'";
	$select = $dbcon->query($qry);
	$str='';
	$str.='<option value="">--Select Tax Category--</option>';
	while($row=brp_mysqli_fetch_assoc($select))
	{
		$sel='';
		
		if($row['tax_cat_id']==$eid)
		{
			$sel='selected=selected';
		}
		
		$str.='<option value="'.$row['tax_cat_id'].'" '.$sel.'>'.$row['tax_cat_name'].'</option>';
		
	}
	return $str;
}

?>