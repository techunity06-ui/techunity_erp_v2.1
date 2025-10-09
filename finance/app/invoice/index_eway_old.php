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
	FINANCE_INVOICE_RECEIPT,
	FINANCE_INVOICE_CHALAN,
	FINANCE_INVOICE_EDIT,
	FINANCE_INVOICE_DELETE
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode'])== "detail_show_eway_bill")
{	

	$invoice_id = $POST['invoice_id'];
	$qry = "SELECT inv.enable_consignee,inv.invoice_no,inv.invoice_date,inv_type.invoice_type,inv_type.gst_code,com.vatno,com.company_name,com.address,sta.state_name,sta.gst_state_code,cit.city_name,com.pincode,led.l_name,led.gst_no,led.m_address,led.cust_pincode,cust_sta.state_name as cust_state_name,cust_sta.gst_state_code as cust_gst_state_code,cust_cit.city_name as cust_city_name,con_sta.state_name as con_state_name,con_sta.gst_state_code as con_gst_state_code,con_cit.city_name as con_city_name,con.cust_pincode as con_pincode,con.cust_address as con_address, inv.cust_id FROM `tbl_invoice` as inv 
	left join tbl_invoicetype as inv_type on inv_type.invoicetype_id=inv.invoicetype_id
	left join tbl_company as com on com.company_id=inv.company_id
	left join state_mst as sta on sta.stateid=com.stateid
	left join city_mst as cit on cit.cityid=com.city_id

	left join tbl_ledger as led on led.l_id=inv.cust_id
	left join state_mst as cust_sta on cust_sta.stateid=led.stateid
	left join city_mst as cust_cit on cust_cit.cityid=led.cityid

	left join tbl_custmer_consignee as con on con.cust_id=inv.consignee_id
	left join state_mst as con_sta on con_sta.stateid=con.stateid
	left join city_mst as con_cit on con_cit.cityid=con.cityid

	where invoice_id=".$invoice_id." and invoice_status=0";
	$ex_q = $dbcon->query($qry);
	$row1=brp_mysqli_fetch_assoc($ex_q);
	$row=array();
	$row['supply_type']		="Outward";
	$row['sub_type']		="";
	$row['doc_type']		=$row1['invoice_type']." (".$row1['gst_code'].")";
	$row['doc_no']			=$row1['invoice_no'];
	$row['doc_date']		=date('d-m-Y',strtotime($row1['invoice_date']));

		//Supplier Details
	$row['sup_gst_no']		=$row1['vatno'];
	$row['sup_name']		=$row1['company_name'];
	$row['sup_add1']		=$row1['address'];
	$row['sup_city']		=$row1['city_name'];
	$row['sup_state']		=$row1['state_name']." - (".$row1['gst_state_code'].")";
	$row['sup_pincode']		=$row1['pincode'];


		//Customer Details

	$row['rec_gst_no']		=$row1['gst_no'];
	$row['rec_name']		=$row1['l_name'];
	if($row1['enable_consignee']==1){
		$row['rec_add1']		=$row1['m_address'];
		$row['rec_city']		=$row1['cust_city_name'];
		$row['rec_state']		=$row1['cust_state_name']." - (".$row1['cust_gst_state_code'].")";
		$row['rec_pincode']		=$row1['cust_pincode'];
	}else{
		$row['rec_add1']		=$row1['con_address'];
		$row['rec_city']		=$row1['con_city_name'];
		$row['rec_state']		=$row1['con_state_name']." - (".$row1['con_gst_state_code'].")";
		$row['rec_pincode']		=$row1['con_pincode'];
	}


		//product details	
	$row['eway_product_detail']	= getinvoice_product($dbcon,$invoice_id,$row1['cust_id']);


	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "get_sub_type")
{	
	$return=get_sub_type($dbcon,$POST['type']);
	echo $return;
}
else if(strtolower($POST['mode'])== "add_eway_bill")
{	
	$invoice_id=$POST['invoice_id'];

	$qry = "SELECT inv.enable_consignee,inv.invoice_no,inv.invoice_date,inv.g_total,inv_type.invoice_type,inv_type.gst_code,com.vatno,com.company_name,com.address,sta.state_name,sta.gst_state_code,cit.city_name,com.pincode,led.l_name,led.gst_no,led.m_address,led.cust_pincode,cust_sta.state_name as cust_state_name,cust_sta.gst_state_code as cust_gst_state_code,cust_cit.city_name as cust_city_name,con_sta.state_name as con_state_name,con_sta.gst_state_code as con_gst_state_code,con_cit.city_name as con_city_name,con.cust_pincode as con_pincode,con.cust_address as con_address FROM `tbl_invoice` as inv 
	left join tbl_invoicetype as inv_type on inv_type.invoicetype_id=inv.invoicetype_id
	left join tbl_company as com on com.company_id=inv.company_id
	left join state_mst as sta on sta.stateid=com.stateid
	left join city_mst as cit on cit.cityid=com.city_id

	left join tbl_ledger as led on led.l_id=inv.cust_id
	left join state_mst as cust_sta on cust_sta.stateid=led.stateid
	left join city_mst as cust_cit on cust_cit.cityid=led.cityid

	left join tbl_custmer_consignee as con on con.cust_id=inv.consignee_id
	left join state_mst as con_sta on con_sta.stateid=con.stateid
	left join city_mst as con_cit on con_cit.cityid=con.cityid

	where invoice_id=".$invoice_id." and invoice_status=0";
	$ex_q = $dbcon->query($qry);
	$row1=brp_mysqli_fetch_assoc($ex_q);
	$DocDate=date('Ymd',strtotime($POST['doc_date']));
	$year=date('Y',strtotime($POST['doc_date']));
	$month=date('m',strtotime($POST['doc_date']));
	$TransDocDate=date('Ymd',strtotime($POST['trn_doc_date']));
	if($row1['enable_consignee']==1){
		$rec_add1		=$row1['m_address'];
		$rec_city		=$row1['cust_city_name'];
		$ShipToStateCode=$row1['cust_gst_state_code'];
		$rec_pincode	=$row1['cust_pincode'];
		$IsBillToShipToSame=1;
	}else{
		$rec_add1		=$row1['con_address'];
		$rec_city		=$row1['con_city_name'];
		$ShipToStateCode=$row1['con_gst_state_code'];
		$rec_pincode	=$row1['con_pincode'];
		$IsBillToShipToSame=0;
	}
	$company_config = getCompanyConfiguration($dbcon);
	$product_array1=array();
	$chkInpro = "SELECT mst.*, product.product_name, product.product_desc, product.product_type, product.product_base_unit, cat.unit_name,cat.unit_code, hsn.hsn_code FROM tbl_invoicetrn as mst LEFT JOIN unit_mst as cat on cat.unitid=mst.unit_id LEFT JOIN product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code AS hsn ON hsn.hsn_id = product.product_hsn where trancation_status=0 and invoice_id=".$invoice_id;
	$row = $dbcon->query($chkInpro);
	if(brp_mysqli_num_rows($row)>0){
		while($getpro = brp_mysqli_fetch_assoc($row)){
			$product_array=array(
				"Irn"=> '',
				"GSTIN"=> $row1['vatno'],
				"Year"=> $year,
				"Month"=> $month,
				"SupplyType"=> "O",
				"SubType"=> $POST['sub_type'],
				"DocType"=> $row1['gst_code'],
				"DocNo"=> $row1['invoice_no'],
				"DocDate"=> $DocDate,
				"SupGSTIN"=> $row1['vatno'],
				"SupName"=>$row1['company_name'],
				"SupAdd1"=> $row1['m_address'],
				"SupAdd2"=> "",
				"SupCity"=> $row1['city_name'],
				"SupState"=> $row1['gst_state_code'],
				"SupPincode"=> $row1['pincode'],
				"RecGSTIN"=> $row1['gst_no'],
				"RecName"=> $row1['l_name'],
				"RecAdd1"=> $rec_add1,
				"RecAdd2"=> "",
				"Reccity"=> $rec_city,
				"RecState"=> $row1['cust_gst_state_code'],
				"Recpincode"=> $rec_pincode,
				"TransMode"=> $POST['trn_mode'],
				"TransporterId"=> "",
				"TransporterName"=> $POST['trn_name'],
				"TransDistance"=> $POST['trn_distance'],
				"TransDocNo"=> $POST['trn_doc_no'],
				"TransDocDate"=> $TransDocDate,
				"VehicleType"=> "R",
				"VehicleNo"=> $POST['vehicle_no'],
				"ProductName"=> $getpro['product_name'],
				"ProductDesc"=> $getpro['product_desc'],
				"HSNCode"=> $getpro['hsn_code'],
				"Quantity"=> $getpro['product_qty'],
				"QtyUnit"=> $getpro['unit_code'],
				"TaxableValue"=> $getpro['product_amount'],
				"TotalValue"=> $getpro['total'],
				"SGSTRate"=> $getpro['sgst_tax_per'],
				"SGSTValue"=> $getpro['sgst_tax_rate'],
				"CGSTRate"=> $getpro['cgst_tax_per'],
				"CGSTValue"=> $getpro['cgst_tax_rate'],
				"IGSTRate"=> $getpro['igst_tax_per'],
				"IGSTValue"=> $getpro['igst_tax_rate'],
				"CessRate"=> 0,
				"CessValue"=> 0,
				"EWBUserName"=> $company_config['gsp_username'],//ewaybill portal
				"EWBPassword"=> $company_config['gsp_password'],//ewaybill portal
				"CessNonAdvol"=> 0,
				"SubSupplyDesc"=> "",
				"ShipFromStateCode"=> $row1['gst_state_code'],
				"ShipToStateCode"=> $ShipToStateCode,
				"TotalInvoiceValue"=> $row1['g_total'],
				"CessNonAdvolValue"=> 0,
				"OtherValue"=> 0,
				"dispatchFromGSTIN "=> $row1['vatno'],
				"dispatchFromTradeName"=> $row1['company_name'],
				"ShipToGSTIN"=> $row1['gst_no'],
				"ShipToTradeName"=> "",
				"IsBillFromShipFromSame"=> "1",
				"IsBillToShipToSame"=> $IsBillToShipToSame,
				"IsGSTINSEZ"=> 0
			); 
			array_push($product_array1,$product_array);
		}
	}
	$custLedgerDetails = get_cust_data_arr($dbcon,$row1['cust_id']);
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);

	$chkInsundry = "select b.*,tl.ledger_hsn, tl.l_name from tbl_bill_sundry_transaction as b left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id where b.sundry_voucher_id='".$invoice_id."' and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' AND tl.default_sundry = 0";
	$chksql = $dbcon->query($chkInsundry);
	while($getsql = brp_mysqli_fetch_assoc($chksql)){
		$product_array=array(
			"Irn"=> '',
			"GSTIN"=> $row1['vatno'],
			"Year"=> $year,
			"Month"=> $month,
			"SupplyType"=> "O",
			"SubType"=> $POST['sub_type'],
			"DocType"=> $row1['gst_code'],
			"DocNo"=> $row1['invoice_no'],
			"DocDate"=> $DocDate,
			"SupGSTIN"=> $row1['vatno'],
			"SupName"=>$row1['company_name'],
			"SupAdd1"=> $row1['m_address'],
			"SupAdd2"=> "",
			"SupCity"=> $row1['city_name'],
			"SupState"=> $row1['gst_state_code'],
			"SupPincode"=> $row1['pincode'],
			"RecGSTIN"=> $row1['gst_no'],
			"RecName"=> $row1['l_name'],
			"RecAdd1"=> $rec_add1,
			"RecAdd2"=> "",
			"Reccity"=> $rec_city,
			"RecState"=> $row1['cust_gst_state_code'],
			"Recpincode"=> $rec_pincode,
			"TransMode"=> $POST['trn_mode'],
			"TransporterId"=> "",
			"TransporterName"=> $POST['trn_name'],
			"TransDistance"=> $POST['trn_distance'],
			"TransDocNo"=> $POST['trn_doc_no'],
			"TransDocDate"=> $TransDocDate,
			"VehicleType"=> "R",
			"VehicleNo"=> $POST['vehicle_no'],
			"ProductName"=> $getsql['l_name'],
			"ProductDesc"=> "",
			"HSNCode"=> $getsql['ledger_hsn'],
			"Quantity"=> "1",
			"QtyUnit"=> "NOS",
			"TaxableValue"=> $getsql['sundry_amount'],
			"TotalValue"=> $getsql['sundry_amount']+$getsql['sundry_gst_amount'],
			"SGSTRate"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_per']/2) : 0,
			"SGSTValue"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_amount']/2) : 0,
			"CGSTRate"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_per']/2) : 0,
			"CGSTValue"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_amount']/2) : 0,
			"IGSTRate"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? 0 : $getsql['sundry_gst_per'],
			"IGSTValue"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? 0 : $getsql['sundry_gst_amount'],
			"CessRate"=> 0,
			"CessValue"=> 0,
			"EWBUserName"=> $company_config['gsp_username'],//ewaybill portal
			"EWBPassword"=> $company_config['gsp_password'],//ewaybill portal
			"CessNonAdvol"=> 0,
			"SubSupplyDesc"=> "",
			"ShipFromStateCode"=> $row1['gst_state_code'],
			"ShipToStateCode"=> $ShipToStateCode,
			"TotalInvoiceValue"=> $row1['g_total'],
			"CessNonAdvolValue"=> 0,
			"OtherValue"=> 0,
			"dispatchFromGSTIN "=> $row1['vatno'],
			"dispatchFromTradeName"=> $row1['company_name'],
			"ShipToGSTIN"=> $row1['gst_no'],
			"ShipToTradeName"=> "",
			"IsBillFromShipFromSame"=> "1",
			"IsBillToShipToSame"=> $IsBillToShipToSame,
			"IsGSTINSEZ"=> 0
		);
		array_push($product_array1,$product_array);
	}
	$post_data = array(
		"Push_Data_List"=>$product_array1,
		"Year"=>$year,
		"Month"=>$month,
		"EFUserName"=>EWAY_USERNAME,//webtel provide
		"EFPassword"=>EWAY_PASSWORD,//webtel provide
		"CDKey"=>EWAY_CDKEY
	); 
	$post_data = json_encode($post_data);
	//echo $post_data;
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => EWAY_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $post_data,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		),

	));
		// print_r($post_data);die;
	$response = curl_exec($curl);
	$err = curl_error($curl);
	$response1=json_decode($response,true);
	$arr = json_decode($response1,true);
	curl_close($curl);

			// print_r($arr); die;
	if(!empty($arr[0]['EWayBill'])){
		$info['enable_ewaybill']=1;
		$info['enable_transport']=1;
		$info['eway_bill_no']=$arr[0]['EWayBill'];
		$info['e_way_bill_no']=$arr[0]['EWayBill'];
		$info['lrno']=$POST['trn_doc_no'];
		$info['vehicle_no']=$POST['vehicle_no'];
		$info['lr_date']=$TransDocDate;
		$info['transport_id']=$POST['trn_name'];
		$info['eway_bill_date']=date("Y-m-d h:i:s",strtotime($arr[0]['ValidUpTo']));

		$updateid=update_record('tbl_invoice', $info,"invoice_id=".$invoice_id , $dbcon);

		$res['msg'] = 'E-Way Bill Successfully Generated!!';
		$res['status'] = "1";
	} else {
		$info['enable_ewaybill']=1;
		$info['enable_transport']=1;
		$info['lrno']=$POST['trn_doc_no'];
		$info['vehicle_no']=$POST['vehicle_no'];
		$info['lr_date']=$TransDocDate;
		$info['transport_id']=$POST['trn_name'];

		$updateid=update_record('tbl_invoice', $info,"invoice_id=".$invoice_id , $dbcon);
		$res['msg'] = $arr[0]['ErrorMessage'];
		$res['status'] = "0";
	}
	echo json_encode($res);
}
else if(strtolower($POST['mode'])== "detail_show_e_invoice")
{	

	$invoice_id = $POST['invoice_id'];
	$qry = "SELECT inv.enable_consignee,inv.invoice_no,inv.invoice_date,inv_type.invoice_type,inv_type.gst_code,com.vatno,com.company_name,com.contact_no,com.website,com.address,sta.state_name,sta.gst_state_code,cit.city_name,com.pincode,led.l_name,led.gst_no,led.m_address,led.cust_pincode,cust_sta.state_name as cust_state_name,cust_sta.gst_state_code as cust_gst_state_code,cust_cit.city_name as cust_city_name,con_sta.state_name as con_state_name,con_sta.gst_state_code as con_gst_state_code,con_cit.city_name as con_city_name,con.cust_pincode as con_pincode,con.cust_address as con_address,led.cust_mobile,led.cust_email,con.gst_no as con_gst_no,con.company_name as con_company_name FROM `tbl_invoice` as inv 
	left join tbl_invoicetype as inv_type on inv_type.invoicetype_id=inv.invoicetype_id
	left join tbl_company as com on com.company_id=inv.company_id
	left join state_mst as sta on sta.stateid=com.stateid
	left join city_mst as cit on cit.cityid=com.city_id

	left join tbl_ledger as led on led.l_id=inv.cust_id
	left join state_mst as cust_sta on cust_sta.stateid=led.stateid
	left join city_mst as cust_cit on cust_cit.cityid=led.cityid

	left join tbl_custmer_consignee as con on con.cust_id=inv.consignee_id
	left join state_mst as con_sta on con_sta.stateid=con.stateid
	left join city_mst as con_cit on con_cit.cityid=con.cityid

	where invoice_id=".$invoice_id." and invoice_status=0";
	$ex_q = $dbcon->query($qry);
	$row1=brp_mysqli_fetch_assoc($ex_q);
	$invoice_date=date('d/m/Y',strtotime($row1['invoice_date']));
	$row=array();

	$row['einv_doc_type']		="INV";
	$row['einv_doc_no']			=$row1["invoice_no"];
	$row['einv_doc_date']		=$invoice_date;

			//seller details
	$row['einv_seller_gstn']		=$row1["vatno"];
	$row['einv_seller_name']		=$row1["company_name"];
	$row['einv_seller_add']			=$row1["address"];
	$row['einv_seller_state']		=$row1["state_name"];
	$row['einv_seller_statecode']	=$row1["gst_state_code"];
	$row['einv_seller_pincode']		=$row1["pincode"];
	$row['einv_seller_phoneno']		=$row1["contact_no"];
	$row['einv_seller_email']		=$row1["website"];

		//product details	
	$row['einv_product_detail']		=getinvoice_product($dbcon,$invoice_id,$row1['cust_id']);


	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "add_einv_bill")
{	
			//var_dump($POST['invoice_id']);
	$invoice_id = $POST['invoice_id'];
	$product_array1=array();
	$chkInpro = "SELECT mst.*, product.product_name, product.product_desc, product.product_type, product.product_base_unit, cat.unit_name,cat.unit_code, hsn.hsn_code FROM tbl_invoicetrn as mst 
	LEFT JOIN unit_mst as cat on cat.unitid=mst.unit_id 
	LEFT JOIN product_mst as product on product.product_id=mst.product_id 
	LEFT JOIN mst_hsn_code as hsn on hsn.hsn_id=product.product_hsn 
	where trancation_status=0 and invoice_id=".$invoice_id;
	$row = $dbcon->query($chkInpro);
	if(brp_mysqli_num_rows($row)>0){
		$k=1;
		$gtotal_AssVal=0;
		$gtotal_CgstVal=0;
		$gtotal_SgstVal=0;
		$gtotal_IgstVal=0;
		$gtotal_Discount=0;
		$gtotal_TotInvVal=0;
		$gtotal_TotInvValFc=0;
		while($getpro = brp_mysqli_fetch_assoc($row)){
			$IsServc=N;
			if($getpro['product_type']==8){
				$IsServc=Y;
			}
			$GstRt=$getpro['cgst_tax_per']+$getpro['sgst_tax_per']+$getpro['igst_tax_per'];
			$TotAmt=$getpro['product_qty']*$getpro['product_rate'];
			$product_array=array(
				"SlNo"=> $k,
				"PrdDesc"=> $getpro['product_name'],
				"IsServc"=> $IsServc,
				"HsnCd"=> $getpro['hsn_code'],
				"Barcde"=> "",
				"Qty"=> $getpro['product_qty'],
				"FreeQty"=> 0,
				"Unit"=> $getpro['unit_code'],
				"UnitPrice"=> $getpro['product_rate'],
				"TotAmt"=> $TotAmt,
				"Discount"=>$getpro['product_discount'],
				"PreTaxVal"=> 0,
				"AssAmt"=> $getpro['product_amount'],
				"GstRt"=> $GstRt,
				"IgstAmt"=> $getpro['igst_tax_rate'],
				"CgstAmt"=> $getpro['cgst_tax_rate'],
				"SgstAmt"=> $getpro['sgst_tax_rate'],
				"CesRt"=> 0,
				"CesAmt"=> 0,
				"CesNonAdvlAmt"=> 0,
				"StateCesRt"=> 0,
				"StateCesAmt"=> 0,
				"StateCesNonAdvlAmt"=> 0,
				"OthChrg"=> 0,
				"TotItemVal"=> $getpro['total'],
				"OrdLineRef"=> "",
				"OrgCntry"=> "",
				"PrdSlNo"=> ""
			); 

			$k++;
			$gtotal_AssVal=$gtotal_AssVal+$getpro['product_amount'];
			$gtotal_CgstVal=$gtotal_CgstVal+$getpro['cgst_tax_rate'];
			$gtotal_SgstVal=$gtotal_SgstVal+$getpro['sgst_tax_rate'];
			$gtotal_IgstVal=$gtotal_IgstVal+$getpro['igst_tax_rate'];
			$gtotal_Discount=$gtotal_Discount+$getpro['product_discount'];
			$gtotal_TotInvVal=$gtotal_TotInvVal+$getpro['total'];
			$gtotal_TotInvValFc=$gtotal_TotInvValFc+$getpro['total'];
			$gtotal_Discount=0;
			array_push($product_array1,$product_array);
		}
	}
	
	$qry = "SELECT inv.enable_consignee,inv.invoice_no,inv.invoice_date,inv_type.invoice_type,inv_type.gst_code,com.vatno,com.company_name,com.contact_no,com.website,com.address,sta.state_name,sta.gst_state_code,cit.city_name,com.pincode,led.l_name,led.gst_no,led.m_address,led.cust_pincode,cust_sta.state_name as cust_state_name,cust_sta.gst_state_code as cust_gst_state_code,cust_cit.city_name as cust_city_name,con_sta.state_name as con_state_name,con_sta.gst_state_code as con_gst_state_code,con_cit.city_name as con_city_name,con.cust_pincode as con_pincode,con.cust_address as con_address,led.cust_mobile,led.cust_email,con.gst_no as con_gst_no,con.company_name as con_company_name, inv.cust_id FROM `tbl_invoice` as inv 
	left join tbl_invoicetype as inv_type on inv_type.invoicetype_id=inv.invoicetype_id
	left join tbl_company as com on com.company_id=inv.company_id
	left join state_mst as sta on sta.stateid=com.stateid
	left join city_mst as cit on cit.cityid=com.city_id

	left join tbl_ledger as led on led.l_id=inv.cust_id
	left join state_mst as cust_sta on cust_sta.stateid=led.stateid
	left join city_mst as cust_cit on cust_cit.cityid=led.cityid

	left join tbl_custmer_consignee as con on con.cust_id=inv.consignee_id
	left join state_mst as con_sta on con_sta.stateid=con.stateid
	left join city_mst as con_cit on con_cit.cityid=con.cityid

	where invoice_id=".$invoice_id." and invoice_status=0";
	$ex_q = $dbcon->query($qry);
	$row1=brp_mysqli_fetch_assoc($ex_q);
	$invoice_date=date('d/m/Y',strtotime($row1['invoice_date']));
	$company_config = getCompanyConfiguration($dbcon);
	$custLedgerDetails = get_cust_data_arr($dbcon,$row1['cust_id']);
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
	
	$chkother = "select sum(sundry_amount) as other_amount from tbl_bill_sundry_transaction as b 
				left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id 
				where b.sundry_voucher_id='".$invoice_id."' and tl.l_form!='tax_form' and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' AND tl.default_sundry = 0";
	$chkot = $dbcon->query($chkother);
	$getother = brp_mysqli_fetch_assoc($chkot);
	

	$chkInsundry = "select b.*,tl.ledger_hsn, tl.l_name from tbl_bill_sundry_transaction as b 
				left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id 
				where b.sundry_voucher_id='".$invoice_id."' and tl.l_form!='tax_form' and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' AND tl.default_sundry = 0";
	$chksql = $dbcon->query($chkInsundry);
	while($getsql = brp_mysqli_fetch_assoc($chksql)){
		$IsServc=Y;
		$GstRt=$getsql['sundry_gst_per'];
		$TotAmt=$getsql['sundry_amount']+$getsql['sundry_gst_amount'];
		$product_array=array(
			"SlNo"=> $k,
			"PrdDesc"=> $getsql['l_name'],
			"IsServc"=> $IsServc,
			"HsnCd"=> $getsql['ledger_hsn'],
			"Barcde"=> "",
			"Qty"=> "1",
			"FreeQty"=> 0,
			"Unit"=> "NOS",
			"UnitPrice"=> $getsql['sundry_amount'],
			"TotAmt"=> $getsql['sundry_amount'],
			"Discount"=>0,
			"PreTaxVal"=> 0,
			"AssAmt"=> $getsql['sundry_amount'],
			"GstRt"=> $GstRt,
			"IgstAmt"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? 0 : $getsql['sundry_gst_amount'],
			"CgstAmt"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_amount']/2) : 0,
			"SgstAmt"=> ($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_amount']/2) : 0,
			"CesRt"=> 0,
			"CesAmt"=> 0,
			"CesNonAdvlAmt"=> 0,
			"StateCesRt"=> 0,
			"StateCesAmt"=> 0,
			"StateCesNonAdvlAmt"=> 0,
			"OthChrg"=> 0,
			"TotItemVal"=> $TotAmt,
			"OrdLineRef"=> "",
			"OrgCntry"=> "",
			"PrdSlNo"=> ""
		); 

		$k++;
		$gtotal_AssVal=$gtotal_AssVal+$getsql['sundry_amount'];
		//$gtotal_AssVal=$gtotal_AssVal;
		$gtotal_CgstVal=$gtotal_CgstVal+(($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_amount']/2) : 0);
		$gtotal_SgstVal=$gtotal_SgstVal+(($custLedgerDetails['stateid'] == $company_state['stateid']) ? ($getsql['sundry_gst_amount']/2) : 0);
		$gtotal_IgstVal=$gtotal_IgstVal+(($custLedgerDetails['stateid'] == $company_state['stateid']) ? 0 : $getsql['sundry_gst_amount']);
			// $gtotal_Discount=$gtotal_Discount+$getsql['product_discount'];
		$gtotal_TotInvVal=$gtotal_TotInvVal+$TotAmt;
		$gtotal_TotInvValFc=$gtotal_TotInvValFc+$TotAmt;
		$gtotal_Discount=0;
		array_push($product_array1,$product_array);
	}

	if($row1['enable_consignee']==1){
		$Gstin		=$row1['gst_no'];
		$LglNm		=$row1['l_name'];
		$Addr1		=$row1['m_address'];
		$Loc		=$row1['cust_city_name'];
		$Pin 		=$row1['cust_pincode'];
		$Stcd 		=$row1['cust_gst_state_code'];
	}else{
		$Gstin		=$row1['con_gst_no'];
		$LglNm		=$row1['con_company_name'];
		$Addr1		=$row1['con_address'];
		$Loc		=$row1['con_city_name'];
		$Pin 		=$row1['con_pincode'];
		$Stcd 		=$row1['con_gst_state_code'];
	}
	$TranDtls = array(
		"SupTyp"=> $_POST["einv_supply_type"],
		"RegRev"=> $_POST["rev_charg"],
		"EcmGstin"=> null,
		"IgstOnIntra"=> "N"
	);
	$DocDtls = array(
		"Typ"=> "INV",
		"No"=> $row1["invoice_no"],
		"Dt"=> $invoice_date
	);
	$SellerDtls = array(
		"Gstin"=> $row1["vatno"],
		"LglNm"=> $row1["company_name"],
		"Addr1"=> $row1["address"],
		"Addr2"=> "",
		"Loc"=> $row1["state_name"],
		"Pin"=> $row1["pincode"],
		"Stcd"=> $row1["gst_state_code"],
		"Ph"=> $row1["contact_no"],
		"Em"=> $row1["website"]
	);
	$BuyerDtls = array(
		"Gstin"=> $row1["gst_no"],
		"LglNm"=> $row1["l_name"],
		"TrdNm"=> "",
		"Pos"=> $row1["cust_gst_state_code"],
		"Addr1"=> $row1["m_address"],
		"Addr2"=> "",
		"Loc"=> $row1["cust_state_name"],
		"Pin"=> $row1["cust_pincode"],
		"Stcd"=> $row1["cust_gst_state_code"],
		"Ph"=> $row1["cust_mobile"],
		"Em"=> $row1["cust_email"]
	);
	$DispDtls = array(
		"Nm"=> $row1["company_name"],
		"Addr1"=> $row1["address"],
		"Addr2"=> "",
		"Loc"=> $row1["state_name"],
		"Pin"=> $row1["pincode"],
		"Stcd"=> $row1["gst_state_code"]
	);
	$ShipDtls = array(
		"Gstin"=> $Gstin,
		"LglNm"=> $LglNm,
		"TrdNm"=> "",
		"Addr1"=> $Addr1,
		"Addr2"=> "",
		"Loc"=> $Loc,
		"Pin"=> $Pin,
		"Stcd"=> $Stcd
	);
/*	$ValDtls = array(
		"AssVal"=> $gtotal_AssVal,
		"CgstVal"=> $gtotal_CgstVal,
		"SgstVal"=> $gtotal_SgstVal,
		"IgstVal"=> $gtotal_IgstVal,
		"CesVal"=> 0,
		"StCesVal"=> 0,
		"Discount"=> $gtotal_Discount,
		"OthChrg"=> $getother['other_amount'],
		"RndOffAmt"=> 0,
		"TotInvVal"=> $gtotal_TotInvVal,
		"TotInvValFc"=> $gtotal_TotInvValFc
	); */
	$ValDtls = array(
		"AssVal"=> $gtotal_AssVal,
		"CgstVal"=> $gtotal_CgstVal,
		"SgstVal"=> $gtotal_SgstVal,
		"IgstVal"=> $gtotal_IgstVal,
		"CesVal"=> 0,
		"StCesVal"=> 0,
		"Discount"=> $gtotal_Discount,
		"OthChrg"=> 0,
		"RndOffAmt"=> 0,
		"TotInvVal"=> $gtotal_TotInvVal,
		"TotInvValFc"=> $gtotal_TotInvValFc
	);
	$post_data = array(
		"CDKey"=> EINV_CDKEY,
		"EInvUserName"=> $company_config['gsp_username'],
		"EInvPassword"=> $company_config['gsp_password'],
		"EFUserName"=> EINV_USERNAME,
		"EFPassword"=> EINV_PASSWORD,
		"GSTIN"=> $row1["vatno"],
		"GetQRImg"=> "1",
		"GetSignedInvoice"=> "1",
		"TranDtls"=> $TranDtls,
		"DocDtls"=> $DocDtls,
		"SellerDtls"=> $SellerDtls,
		"BuyerDtls"=> $BuyerDtls,
		"DispDtls"=> $DispDtls,
		"ShipDtls"=> $ShipDtls,
		"ItemList"=> $product_array1,
		"ValDtls"=> $ValDtls
	);

	$product_array3 = json_encode($post_data);
	 //print_r($product_array3); die;
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => EINV_URL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS =>$product_array3,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json'
		),
	));

	$response = curl_exec($curl);
			// print_r($response);
	$response1=json_decode($response,true, 512, JSON_BIGINT_AS_STRING);
			// $arr = json_decode($response1,true);

	curl_close($curl);
	if($response1[0]['Status']==1 && $response1[0]['Irn']!=""){
				//print_r($response1[0]);
		$eininfo['einv_Irn']=$response1[0]['Irn'];
		$eininfo['einv_AckDate']=date("Y-m-d h:i:s", strtotime($response1[0]['AckDate']));
		$eininfo['einv_AckNo']=$response1[0]['AckNo'];
		$eininfo['einv_SignedQRCode']=$response1[0]['SignedQRCode'];
		$eininfo['einv_SignedInvoice']=$response1[0]['SignedInvoice'];
		$eininfo['einv_Remarks']=$response1[0]['Remarks'];
			// print_r($eininfo);die;

		$updateid=update_record('tbl_invoice', $eininfo,"invoice_id=".$invoice_id , $dbcon);
		$res['msg'] = 'E-Invoice Successfully Generated!!';
		$res['status'] = "1";
	}else{
		$res['msg'] = $response1[0]['ErrorMessage'];
		$res['status'] = "0";
	}
	echo json_encode($res);
}

/*Dhruv end code*/
function getinvoice_product($dbcon, $invoice_id, $cust_id){
	$chkInpro = "SELECT mst.*, product.product_name, product.product_desc, product.product_type, product.product_base_unit, cat.unit_name, cat.unit_code, hsn.hsn_code FROM tbl_invoicetrn as mst LEFT JOIN unit_mst as cat on cat.unitid=mst.unit_id LEFT JOIN product_mst as product on product.product_id=mst.product_id LEFT JOIN mst_hsn_code AS hsn ON hsn.hsn_id = product.product_hsn where trancation_status=0 and invoice_id=".$invoice_id;
	$row = $dbcon->query($chkInpro);
	$str = "<table class='table table-bordered table-stripped'>
	<thead>
	<tr>
	<th>Product Name</th>
	<th>HSN Code</th>
	<th>Quantity</th>
	<th>Taxable Value</th>
	<th>Total value</th>
	<th>SGST Rate%</th>
	<th>SGST Value</th>
	<th>CGST Rate%</th>
	<th>CGST Value</th>
	<th>IGST Rate%</th>
	<th>IGST Value</th>
	</tr>
	</thead>
	<tbody>";
	$custLedgerDetails = get_cust_data_arr($dbcon,$cust_id);
	$company_state = get_company_data($dbcon,$_SESSION['company_id']);
	if(brp_mysqli_num_rows($row)>0){
		$total_qty = 0;$total_amt = 0;$total = 0;$sgst = 0;$igst = 0;$cgst = 0;
		while($getpro = brp_mysqli_fetch_assoc($row)){
			$str.="<tr>
			<td><strong>".$getpro['product_name']."<br>Des:</strong> ".(($getpro['product_desc']) ? $getpro['product_desc'] : '') ."</td>
			<td>".$getpro['hsn_code']."</td>
			<td>".$getpro['product_qty']." ".$getpro['unit_name']."</td>
			<td>".$getpro['product_amount']."</td>
			<td>".$getpro['total']."</td>
			<td>".$getpro['sgst_tax_per']." %</td>
			<td>".$getpro['sgst_tax_rate']."</td>
			<td>".$getpro['cgst_tax_per']." %</td>
			<td>".$getpro['cgst_tax_rate']."</td>
			<td>".$getpro['igst_tax_per']." %</td>
			<td>".$getpro['igst_tax_rate']."</td>
			</tr>";
			$total_qty+=$getpro['product_qty'];
			$total_amt+=$getpro['product_amount'];
			$total+=$getpro['total'];
			$sgst+=$getpro['sgst_tax_rate'];
			$igst+=$getpro['igst_tax_rate'];
			$cgst+=$getpro['cgst_tax_rate'];
		}
		$sql = "select b.*,tl.ledger_hsn, tl.l_name from tbl_bill_sundry_transaction as b left join tbl_ledger as tl on b.sundry_ledger_id=tl.l_id where b.sundry_voucher_id='".$invoice_id."' and b.sundry_voucher_table='tbl_invoice' and b.isdelete='0' AND tl.default_sundry = 0";
		$chksql = $dbcon->query($sql);
		while($getsql = brp_mysqli_fetch_assoc($chksql)){
			$str.="<tr>
			<td>".$getsql['l_name']."</td>
			<td>".$getsql['ledger_hsn']."</td>
			<td></td>
			<td>".($getsql['sundry_amount'])."</td>
			<td>".($getsql['sundry_amount']+$getsql['sundry_gst_amount'])."</td>";
			if($custLedgerDetails['stateid'] == $company_state['stateid']){
				$str.="<td>".($getsql['sundry_gst_per']/2)." %</td>
				<td>".($getsql['sundry_gst_amount']/2)."</td>
				<td>".($getsql['sundry_gst_per']/2)." %</td>
				<td>".($getsql['sundry_gst_amount']/2)."</td>
				<td>0.00 %</td>
				<td>0.00</td>";
				$sgst+=($getsql['sundry_gst_amount']/2);
				$cgst+=($getsql['sundry_gst_amount']/2);
			}else{
				$str.="<td>0.00 %</td>
				<td>0.00</td>
				<td>0.00 %</td>
				<td>0.00</td>
				<td>".($getsql['sundry_gst_per'])." %</td>
				<td>".($getsql['sundry_gst_amount'])."</td>";
				$igst+=($getsql['sundry_gst_amount']);
			}
			$str.="</tr>";
			$total_amt+=$getsql['sundry_amount'];
			$total+=($getsql['sundry_amount']+$getsql['sundry_gst_amount']);
		}
		$str.="<tr>
		<td colspan='2' style='text-align: right; font-weight: bold;'>Total</td>
		<td style='font-weight: bold;'>".$total_qty."</td>
		<td style='font-weight: bold;'>".$total_amt."</td>
		<td style='font-weight: bold;'>".$total."</td>
		<td style='font-weight: bold;'></td>
		<td style='font-weight: bold;'>".$sgst."</td>
		<td style='font-weight: bold;'></td>
		<td style='font-weight: bold;'>".$cgst."</td>
		<td style='font-weight: bold;'></td>
		<td style='font-weight: bold;'>".$igst."</td>
		</tr>";
	}else{
		$str.="<tr>
		<td colspan='11'>No Data Found</td>
		</tr>";
	}
	$str.="</tbody>
	</table>";
	return $str;
}
?>