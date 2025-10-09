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
	FINANCE_SALE_RETURN_UPDATE,
]);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}



	if(strtolower($POST['mode']) == "fetch") {
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		 //branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('s', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('s');

		$where.=" $where_company";

		$where_user=check_user('s');

		//$where.=" $where_user";

		// branch , comapny , user check end - dhaval 
		
		//check_user('invoice')
			
			$where.="  and s.sale_return_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND s.sale_return_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
			$appData = array();
			$i=1;
			$aColumns = array('s.sale_return_id','s.sal_return_voucher_no','cust.l_name','s.sale_return_date','s.sale_return_gtotal','s.cdate','s.user_id','s.usertype_id','s.sale_return_customer');
			$sIndexColumn = "sale_return_id";
			$isWhere = array("s.isdelete = 0 and s.is_without_item=0  ".$where);
			$sTable = "tbl_sale_return as s";	
			$isJOIN = array('inner join tbl_ledger cust on s.sale_return_customer=cust.l_id');
			$hOrder = "s.sale_return_date desc";
			include($path.'include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				
				$row_data[] = $id;
				$row_data[] = $row['sal_return_voucher_no'];
				$row_data[] = date('d, M y',strtotime($row['sale_return_date']));
				$row_data[] = $row['l_name'];
				$row_data[] = $row['sale_return_gtotal'];
				
				$edit_btn='';$delete_btn='';$print='';
				if(in_array(FINANCE_SALE_RETURN_UPDATE,$bulkAccessArray)){
					$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'salereturnedit/'.$row['sale_return_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				if(in_array(FINANCE_SALE_RETURN_UPDATE,$bulkAccessArray)){
					$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_sale_return('.$row['sale_return_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				$sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 28 AND approve_status = 1 AND status = 0 ORDER BY priority");
				while($res = mysqli_fetch_assoc($sql)){
					if(in_array($res['id'],$menu_show_permissions)) {
						$print.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank"  href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['sale_return_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
				    }
				}

				$row_data[] = $edit_btn.' '.$delete_btn.' '.$print; 				 
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
            
			//echo '<pre>'; print_r($POST);exit;
			 if(isset($POST['currency_enable'])){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }
			
			
			$info['sales_ledger_id']	= $POST['sales_ledger_id'];
			$info['branch_id']	= $POST['branch_id'];
	        $info['sale_return_customer']	= $POST['cust_id'];
	        $info['sal_return_voucher_no'] = $POST['voucher_no']; //Added new by dhaval
	        $info['sale_return_material_center']	= $POST['mat_center'];
	        $info['sale_return_date']	= date('Y-m-d',strtotime($POST['sale_return_date']));	

	        $info['sale_return_currency_enable'] = (isset($POST['sale_enable_multi_currency']) && ($POST['sale_enable_multi_currency']=='yes')) ? 1 : 0;
	       
		    $info['currency_id'] = $POST['currency_conv']; //Added new by dhaval    
	        $info['currency_rate']	= $POST['currency_conv_rate'];
	        $info['sale_return_total']	= $POST['total'];
	        $info['sale_return_gtotal']	= $POST['g_total'];
			
	        $info['sale_return_cost_center_enable']	= (isset($POST['enable_cost_center']) && ($POST['enable_cost_center']=='yes')) ? 1 : 0;
	        $info['sale_return_tcs_enable']	= (isset($POST['enable_tcs_details']) && ($POST['enable_tcs_details']=='yes')) ? 1 : 0;
	        $info['sale_return_eway_enable']	= (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill']=='yes')) ? 1 : 0;
	        $info['sale_return_salesman_enable']	= (isset($POST['enable_salesman']) && ($POST['enable_salesman']=='yes')) ? 1 : 0;
	        
			$info['sale_return_eway_bill_no']	= $POST['eway_bill_no'];
	        $info['sale_return_eway_bill_date']	= $POST['eway_bill_date'];
	        $info['sale_return_narration']	= $POST['remark'];
			
			$info['cdate']			= date("Y-m-d H:i:s");
	        $info['user_id']		= $_SESSION['user_id'];
	        $info['company_id']		= $_SESSION['company_id'];
	        $info['usertype_id']	= $_SESSION['usertype_id'];
			//$info['branch_id']		= $_SESSION['branch_id'];
			
			//echo '<pre>'; print_r($eway_row);exit;
			
			$insertid=add_record('tbl_sale_return', array_merge($info,$curncy_trn), $dbcon,'');
			
			/*Update sale return Trn Table Start by Dhaval */
	        if($insertid){
				$inv_trn['sale_return_id']	= $insertid;
				$inv_trn['trancation_status'] = 0;
				$updatetrnid=update_record('tbl_sale_return_transaction', array_merge($inv_trn,$curncy_trn)," sale_return_id=0 and trancation_status='1' and  user_id=".$_SESSION['user_id'] , $dbcon,'');
			}
			
			/*Update Cost center Trn Table Start by Dhaval */
	        if($insertid && $POST['enable_cost_center']=='yes'){
				$cost_trn['cost_center_ledger_id']	= $POST['cust_id'];
				$cost_trn['cost_center_table_id'] = $insertid;
				$updatecosttrnid=update_record('tbl_cost_center_transaction', array_merge($cost_trn,$curncy_trn),"isdelete=0 and cost_center_table='tbl_sale_return' and  user_id=".$_SESSION['user_id']  , $dbcon);
			}
			
			
			/*Update TCS Reverse Table Start by Dhaval */
	        if($insertid && $POST['enable_tcs_details']=='yes'){
				$tcs_trn['sale_return_voucher_id']	= $insertid;
				
				$ins_tcs_id=update_record('tbl_tcs_reverse', $tcs_trn,"isdelete=0 and sale_return_voucher_id='0' and  user_id=".$_SESSION['user_id']  , $dbcon);
				
				$tcs_ret_trn['sale_return_id']	= $ins_tcs_id;
				$tcs_ret_trn['sale_invoice_id']	= $insertid;
				
				$updatetcstrnid=update_record('tbl_tcs_reverse_transaction',array_merge($tcs_ret_trn,$curncy_trn),"isdelete=0 and sale_return_trn_status=0 and user_id=".$_SESSION['user_id'] , $dbcon);
				
				$sel=$dbcon->query("select sale_ref_no from tbl_tcs_reverse_transaction where sale_invoice_id='$insertid' and isdelete=0");
				while($row=brp_mysqli_fetch_assoc($sel))
				{
					$invoice_ref_no = $row['sale_ref_no'];
					
					$invoice_set['sale_return_status']=1;
					
					update_record('tbl_invoice', $invoice_set," invoice_id='$invoice_ref_no'", $dbcon);
				}
				
			}
			
			/** Insert in general book table By Dhaval **/
			if($insertid)
			{
            	add_general_book_entry($dbcon,"tbl_sale_return",$insertid,2,$POST['sales_ledger_id'],$POST['total'],'',$POST['sale_return_date'],'',$curncy_trn);
				
            	add_general_book_entry($dbcon,"tbl_sale_return",$insertid,1,$POST['cust_id'],$POST['g_total'],'',$POST['sale_return_date'],'',$curncy_trn);

            	foreach ($POST['bill_sundry_tax'] as $bill_sundry_tax_id => $bill_sundry_tax_amount) {
					
					//sundry transaction table  
					
					$info_sundry['sundry_ledger_id']=$bill_sundry_tax_id;
					$info_sundry['sundry_amount']=$bill_sundry_tax_amount;
					$info_sundry['sundry_voucher_id']=$insertid;
					$info_sundry['sundry_voucher_type']=SALES_RETURN_VOUCHER;
					$info_sundry['sundry_voucher_table']='tbl_sale_return';
					
					$sundry_insert=add_record('tbl_bill_sundry_transaction', array_merge($info_sundry,$curncy_trn), $dbcon,'');
					
            		add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_insert,2,$bill_sundry_tax_id,$bill_sundry_tax_amount,'',$POST['sale_return_date'],$POST['branch_id'],$curncy_trn);
            	}

            	foreach ($POST['bill_sundry_addon'] as $bill_sundry_addon_id => $bill_sundry_addon_amount) {
					
					$info_sundry_addon['sundry_ledger_id']=$bill_sundry_addon_id;
					$info_sundry_addon['sundry_amount']=$bill_sundry_addon_amount;
					$info_sundry_addon['sundry_voucher_id']=$insertid;
					$info_sundry_addon['sundry_voucher_type']=SALES_RETURN_VOUCHER;
					$info_sundry_addon['sundry_voucher_table']='tbl_sale_return';
					
					$sundry_insert_addon=add_record('tbl_bill_sundry_transaction', array_merge($info_sundry_addon,$curncy_trn), $dbcon,'');

					if($bill_sundry_addon_amount < 0){
						add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_insert_addon,1,$bill_sundry_addon_id,abs($bill_sundry_addon_amount),'',$POST['sale_return_date'],'',$curncy_trn);

						$info_gen1['table_name']	= 'tbl_sale_return';
						$info_gen1['table_id']		= $insertid;
						$info_gen1['entry_type']	= 2;
						$info_gen1['ref_date']		= date('Y-m-d',strtotime($POST['sale_return_date']));
						$info_gen1['ledger_id']		= $POST['cust_id'];
						$info_gen1['amount']		= abs($bill_sundry_addon_amount);
						$info_gen1['user_id']		= $_SESSION['user_id'];
						$info_gen1['cdate']			= date("Y-m-d H:i:s");
						$info_gen1['company_id']	= $_SESSION['company_id'];
						$info_gen1['ref_by'] = 'tbl_addon_bill_sundry';
						
						//$inserid_gen1=add_record("tbl_general_book", array_merge($info_gen1,$curncy_trn) , $dbcon);

					}else{
						add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_insert_addon,2,$bill_sundry_addon_id,$bill_sundry_addon_amount,'',$POST['sale_return_date'],'',$curncy_trn);

						$info_gen2['table_name']	= 'tbl_sale_return';
						$info_gen2['table_id']		= $insertid;
						$info_gen2['entry_type']	= 1;
						$info_gen2['ref_date']	  = date('Y-m-d',strtotime($POST['sale_return_date']));
						$info_gen2['ledger_id']		= $POST['cust_id'];
						$info_gen2['amount']		= $bill_sundry_addon_amount;
						$info_gen2['user_id']		= $_SESSION['user_id'];
						$info_gen2['cdate']			= date("Y-m-d H:i:s");
						$info_gen2['company_id']	= $_SESSION['company_id'];
						$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';
						
						//$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);

					}
										
            		//add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_insert_addon,1,$bill_sundry_addon_id,$bill_sundry_addon_amount,'',$POST['invoice_date'],$POST['branch_id'],$curncy_trn);
            	}

            	foreach($POST['bill_sundry_addon_tax'] as $addon_id=>$addon_value){

            		$addon_explode = explode("-",$addon_value);

            		$info_addon['sundry_gst_per'] = $addon_explode[1];
            		$info_addon['sundry_gst_amount'] = $addon_explode[0];
            		$updateaddontaxid=update_record('tbl_bill_sundry_transaction', $info_addon,"sundry_voucher_table='tbl_sale_return' and isdelete=0 and sundry_voucher_id=".$insertid." and sundry_ledger_id=".$addon_id." " , $dbcon);
            	}
			
        	}
			
				
			/*Update Tax Trn Table Start by Dhaval*/
			if($insertid){
				$tax_trn['tx_status'] = 0;
				$tax_trn['tx_trn_ref_id'] = $insertid;
				$updatetcstrnid=update_record('tbl_tax_trn', array_merge($tax_trn,$curncy_trn),"tx_transaction_type='tbl_sale_return_transaction' and tx_status = 3" , $dbcon);
			}
			

			//add general book entry for service and capital goods products 

        	if($insertid){

        		$sel_gen = $dbcon->query("select trn.*,p.product_type,p.ledger_id from tbl_sale_return_transaction as trn 
        			left join product_mst as p on trn.sale_return_product=p.product_id
        			where trn.sale_return_id='$insertid' and trn.trancation_status!='2'");

        		while($r_gen = brp_mysqli_fetch_assoc($sel_gen))
        		{
        			add_general_book_entry($dbcon,"tbl_sale_return_transaction",$r_gen['sale_return_transaction_id'],2,$r_gen['ledger_id'],$r_gen['sale_return_amount'],'',$POST['sale_return_date'],'',$curncy_trn); 
        		}
        	}

			/* Update transport table & Eway bil table */
			
			if($insertid)
			{
				$transport_trn['transport_transaction_table_id']=$insertid;
				$update_transid=update_record('tbl_transport_transaction', array_merge($transport_trn,$curncy_trn),"transport_voucher='29' and transport_transaction_table_id = 0 and user_id='$_SESSION[user_id]'" , $dbcon);
				
				$eway_trn['eway_bill_voucher_id']=$insertid;
				$updatetcstrnid=update_record('tbl_ewaybill_transaction', array_merge($transport_trn,$eway_trn),"eway_bill_voucher_type='29' and eway_bill_transport_transaction_id = '$update_transid' and user_id='$_SESSION[user_id]'" , $dbcon);
				//echo $insertid."-".$update_transid;exit;
			}
			
			/*Update Salesman Table Start by Dhaval*/
			if($insertid){
				$sales_trn['transaction_table_id'] = $insertid;
				$updatetcstrnid=update_record('tbl_salesman_transaction',array_merge($transport_trn,$sales_trn),"transaction_voucher_type='29' and transaction_table_id = 0" , $dbcon);
			}
			
			/* Update voucher No */
			if($insertid){
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status = 0 AND type_id=31 and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			}
			
			
			/* Eway Bill API */
			//if($insertid && $POST['enable_ewaybill']=='yes')
				
			if($insertid && $POST['enable_ewaybill']=='yes')
			{
				$eway_row=getTransportEwayDetails($dbcon,SALES_RETURN_VOUCHER);
				$company_data = get_company_data($dbcon,$_SESSION['company_id']);
				$customer_ledger_data = get_ledger_details($dbcon,$POST['cust_id']);
				$product_details = get_trans_by_sale_return_id($dbcon,$insertid);
				$company_state_data = get_state_details($dbcon,'and stateid = '.$company_data['stateid'].'');
				$cust_state_data = get_state_details($dbcon,'and stateid = '.$customer_ledger_data['stateid'].'');
				$cust_city_data = get_city_details($dbcon,'and cityid = '.$customer_ledger_data['cityid'].'');
				$trasport_gst = get_trasport_data($dbcon,'and id = '.$eway_row['transport_id'].'');
				$sub_type = get_common_mst_data($dbcon,'and common_mst_id = '.$eway_row['eway_sub_type'].'');
				$trans_mode = get_common_mst_data($dbcon,'and common_mst_id = '.$eway_row['transport_mode'].'');
				
				$jsonobj .='
				{
					"Push_Data_List": [ ';

				foreach ($product_details as $product_data) {

					$jsonobj .='{
							 "GSTIN": "'.$company_data['vatno'].'",  
							 "Year": "'.date('Y',strtotime($POST['sale_return_date'])).'",       
							 "Month": "'.date('m',strtotime($POST['sale_return_date'])).'",      
							 "SupplyType": "O",
							 "SubType": "'.$sub_type['common_mst_desc'].'",       
							 "DocType": "INV",        
							 "DocNo": "'.$POST['voucher_no'].'", 
							 "DocDate": "'.date('Ymd',strtotime($POST['sale_return_date'])).'",    
							 "SupGSTIN": "'.$company_data['vatno'].'",
							 "SupName": "'.$company_data['company_name'].'",       
							 "SupAdd1": "'.$company_data['address'].'",
							 "SupAdd2": "",				
							 "SupCity": "'.$company_data['city_name'].'",			
							 "SupState": "'.$company_state_data['gst_state_code'].'",				
							 "SupPincode": "'.$company_data['pincode'].'",		

							 "RecGSTIN": "'.$customer_ledger_data['gst_no'].'",     
							 "RecName": "'.$customer_ledger_data['l_name'].'",					
							 "RecAdd1": "'.$customer_ledger_data['m_address'].'",	 
							 "RecAdd2": "",						// blank
						     "Reccity": "'.$cust_city_data['city_name'].'",				
							 "RecState": "'.$cust_state_data['gst_state_code'].'",				
							 "Recpincode": "'.$customer_ledger_data['cust_pincode'].'",		

							 "TransMode": "'.$trans_mode['common_mst_desc'].'",
							 "TransporterId": "'.$trasport_gst['transportation_gst_number'].'", 
							 "TransporterName": "'.$trasport_gst['transportation_name'].'",
							 "TransDistance": "'.$eway_row['distance_km'].'",
							 "TransDocNo": "'.$eway_row['transport_doc_no'].'", 
							 "TransDocDate": "'.$eway_row['transport_doc_date'].'",
							 "VehicleType": "R",
							 "VehicleNo": "'.$eway_row['transport_vehicle_no'].'",


							 "ProductName": "'.$product_data['productName'].'",
							 "ProductDesc": "'.$product_data['product_desc'].'",
							 "HSNCode": "'.$product_data['hsnCode'].'",
							 "Quantity": "'.$product_data['sale_return_qty'].'",
							 "QtyUnit": "'.$product_data['unit_code'].'",
							 "TaxableValue": "'.$product_data['sale_return_total_amount'].'",
							 "TotalValue": "'.$product_data['sale_return_total_amount'].'",
							 "SGSTRate": "'.$product_data['sgstPer'].'",
							 "SGSTValue": "'.$product_data['sgstValue'].'",
							 "CGSTRate": "'.$product_data['cgstPer'].'",
							 "CGSTValue": "'.$product_data['cgstValue'].'",
							 "IGSTRate": "'.$product_data['igstper'].'",
							 "IGSTValue": "'.$product_data['igstValue'].'",
							 "CessRate": 0,
							 "CessValue": 0,

							 "EWBUserName": "05AAACW3775F012",
							 "EWBPassword": "Admin!23",
							 "CessNonAdvol": 0,
							 "SubSupplyDesc": "",
							 "ShipFromStateCode": "05",
							 "ShipToStateCode": "05",

							 "TotalInvoiceValue": "'.$POST['g_total'].'",
							 "CessNonAdvolValue": 0,
							 "OtherValue": 0,

							 "dispatchFromGSTIN ": "'.$company_data['vatno'].'",
							 "dispatchFromTradeName": "'.$company_data['company_name'].'",	
							 "ShipToGSTIN": "'.$customer_ledger_data['gst_no'].'",		
							 "ShipToTradeName": "'.$customer_ledger_data['l_name'].'",	
							 "IsBillFromShipFromSame": "1",			
							 "IsBillToShipToSame": "1",

							 "IsGSTINSEZ": "'.$customer_ledger_data['enable_sez'].'"  
							 }, ';
					}		 
				$jsonobj .= ' ],
					 "Year": "'.date("Y").'",
					 "Month": "'.date("m").'",
					 "EFUserName": "29AAACW3775F000",
					 "EFPassword": "Admin!23..",
					 "CDKey": "1000687"
				}';
				
				
				$callEway = submitEwayApi($jsonobj);
			
				$obj = json_decode($callEway);
			
				$arr=json_decode($obj);

				//echo '<pre>';print_r($arr[0]);exit;
				
				$eway_bill_status=$arr[0]->IsSuccess;
				
				
				
				//echo $eway_bill_status;exit;
			

			if($eway_bill_status=='true')
			{
				$eway_status_trn['eway_bill_status']=1;
			}
			else
			{
				$eway_status_trn['eway_bill_status']=2;
				$eway_status_trn['eway_rejection_reason']=$arr[0]->ErrorMessage;
			}


			//Update Invoice table with eway_no & date
			if($POST['enable_ewaybill'] == 'yes' && $eway_bill_status=='true'){
				$info_invtbl['sale_return_eway_bill_no'] = $arr[0]->EWayBill;
				$info_invtbl['sale_return_eway_bill_date'] = date('Y-m-d H:i:s',strtotime($arr[0]->Date));	
			}else{
				$info_invtbl['sale_return_eway_bill_no'] = $POST['eway_bill_no'];
				$info_invtbl['sale_return_eway_bill_date'] = date('Y-m-d',strtotime($POST['eway_bill_date']));	
			}
			
			$updateinvtbl=update_record('tbl_sale_return', $info_invtbl,"invoice_id='".$insertid."'" , $dbcon);
			
				//echo $eway_bill_status;exit;
				
				$updatetcstrnid=update_record('tbl_ewaybill_transaction', $eway_status_trn,"eway_bill_voucher_type='29' and eway_bill_voucher_id ='$insertid'" , $dbcon);
				
				//print_r($jsonobj);exit;
			}
			
			if($insertid)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		
		else if(strtolower($POST['mode']) == "edit") {
			
			if(isset($POST['sale_enable_multi_currency'])){
            	$curncy_trn['currency_id'] = $POST['currency_conv'];
            	$curncy_trn['currency_rate'] = $POST['currency_conv_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }
			
			$info['sales_ledger_id']	= $POST['sales_ledger_id'];
			$info['sale_return_branch']	= $POST['branch_id'];
	        $info['sale_return_customer']	= $POST['cust_id'];
	        $info['sal_return_voucher_no'] = $POST['voucher_no']; //Added new by dhaval
	        $info['sale_return_material_center']	= $POST['mat_center'];
	        $info['sale_return_date']	= date('Y-m-d',strtotime($POST['sale_return_date']));	

	        $info['sale_return_currency_enable'] = (isset($POST['sale_enable_multi_currency']) && ($POST['sale_enable_multi_currency']=='yes')) ? 1 : 0;
	       
	        $info['sale_return_total']	= $POST['total'];
	        $info['sale_return_gtotal']	= $POST['g_total'];
			
	        $info['sale_return_cost_center_enable']	= (isset($POST['enable_cost_center']) && ($POST['enable_cost_center']=='yes')) ? 1 : 0;
	        $info['sale_return_tcs_enable']	= (isset($POST['enable_tcs_details']) && ($POST['enable_tcs_details']=='yes')) ? 1 : 0;
	        $info['sale_return_eway_enable']	= (isset($POST['enable_ewaybill']) && ($POST['enable_ewaybill']=='yes')) ? 1 : 0;
	        $info['sale_return_salesman_enable']	= (isset($POST['enable_salesman']) && ($POST['enable_salesman']=='yes')) ? 1 : 0;
	        
			$info['sale_return_eway_bill_no']	= $POST['eway_bill_no'];
	        $info['sale_return_eway_bill_date']	= $POST['eway_bill_date'];
	        $info['sale_return_narration']	= $POST['remark'];
			
			$info['cdate']			= date("Y-m-d H:i:s");
	        $info['user_id']		= $_SESSION['user_id'];
	        $info['company_id']		= $_SESSION['company_id'];
			
			//print_r($info);exit;
			//echo '<pre>'; print_r($eway_row);exit;

			$insertid=update_record('tbl_sale_return', $info,"sale_return_id=".$POST['eid'] , $dbcon);
			
			$transport_trn['transport_transaction_table_id']=$POST['eid'];
			$update_transid=update_record('tbl_transport_transaction', array_merge($transport_trn,$curncy_trn),"transport_voucher='29' and transport_transaction_table_id = 0 and user_id='$_SESSION[user_id]'" , $dbcon);
			//Added by dhruv
			if($insertid)
			{
				$query1="select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[eid]'  and isdelete=0 and sundry_voucher_table='tbl_sale_return'  ";
				$rel1=brp_mysqli_fetch_all($dbcon->query($query1));

				foreach ($rel1 as $bill_sundry_addon){
					$info_general_sundry['ref_date'] = date('Y-m-d',strtotime($POST['sale_return_date']));
					update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_addon['sundry_ledger_id']." and table_name='tbl_bill_sundry_transaction' 
							and table_id= ".$bill_sundry_addon['sundry_id']." " ,$dbcon);
				}

				$info_invoice_sundry['ref_date'] = date('Y-m-d',strtotime($POST['sale_return_date']));
				update_record("tbl_general_book",$info_invoice_sundry,"table_name='tbl_sale_return' 
							and table_id= ".$POST['eid']." " ,$dbcon);
			}
			
			
			if($insertid)
			{
				echo "update";
			}
			else
			{
				echo "0";
			}
			
			
		}
	
		
		else if(strtolower($POST['mode']) == "delete") {
			$query="SELECT * FROM `tbl_sale_return_transaction` where sale_return_id=".$POST['eid'];
			$result=$dbcon->query($query);
			while($row=mysqli_fetch_assoc($result)){		 
				$info_de['stock_status']=2;
				$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$row['sale_return_transaction_id'] ,$dbcon);
			}		 
					 
			$info['isdelete']	= 1;
			$info1['trancation_status']	= 2;
			$informdr['status'] = 2;
			$info_sales_order['invoice_status']  = 0;
			$info_srl['inv_srl_trn_status']  = 0;

			$updatesalesid=update_record('tbl_sales_order', $info_sales_order,"used_invoice_id=".$POST['eid'], $dbcon);

			$updateinvoiceid=update_record('tbl_sale_return', $info,"sale_return_id=".$POST['eid'] , $dbcon);	
			$updatetrancationid=update_record('tbl_sale_return_transaction', $info1,"sale_return_id=".$POST['eid'] , $dbcon);	
			$updatesrlid=update_record('tbl_inv_srl_trn', $info_srl,"invoice_id=".$POST['eid'] , $dbcon);	
			//Update Payment Reminder
			$updatermdrid=update_record('todo_mst', $informdr,"ref_id=".$POST['eid']." and ref_table='tbl_invoice'" , $dbcon);
			//Update Serial Number
			//$deleteid=delete_record('tbl_serialtrn',"invoice_id=".$POST['eid'], $dbcon);
			
			$info_gen['genral_book_status']		= 2;
			$updateinvoiceid=update_record('tbl_general_book', $info_gen,"table_name='tbl_sale_return' and table_id=".$POST['eid'] , $dbcon);	
			
			
			// $qry="select * from `tbl_sale_return_transaction` as popro where sale_return_id=".$POST['eid'];
			// $result=$dbcon->query($qry);
			// $info_ta['tax_used_status']		= 2;
			// while($row=mysqli_fetch_assoc($result)){
				
			// 	$updateinvoiceid=update_record('tbl_used_tax', $info_ta,"table_name='tbl_sale_return_transaction' and used_transaction_id=".$row['trancation_id'] , $dbcon);
			// }

			//Bill Sundry Transaction
			
			$info_bsun['isdelete'] = 1;
			$updateid1=update_record("tbl_bill_sundry_transaction", $info_bsun,
				"sundry_voucher_table='tbl_sale_return' and sundry_voucher_id=".$POST['eid']."", $dbcon);
			
			$sel_bsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_table='tbl_sale_return' and sundry_voucher_id=".$POST['eid']." and isdelete='1'");
			while($r_bsun=brp_mysqli_fetch_array($sel_bsun))
			{
				$info_bsun_general['genral_book_status'] = 2;
				$updateid1=update_record("tbl_general_book", $info_bsun_general, "table_name='tbl_bill_sundry_transaction' and table_id=".$r_bsun['sundry_id']." ", $dbcon);

			}

			$sel_itrn = $dbcon->query("select * from  tbl_sale_return_transaction where sale_return_id='$POST[eid]' and trancation_status='2'");
			while($r_itrn=brp_mysqli_fetch_array($sel_itrn))
			{
				$info_tax_trn['tx_status']=2;
				update_record("tbl_tax_trn", $info_tax_trn,"tx_transaction_id='$r_itrn[sale_return_transaction_id]' and tx_transaction_type='tbl_sale_return_transaction'" ,$dbcon);

				$info_general['genral_book_status'] = 2;
				update_record('tbl_general_book', $info_general,"table_name='tbl_sale_return_transaction' and table_id=".$r_itrn['sale_return_transaction_id'] , $dbcon);
			}
			
			
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"invoice_add",3,"tbl_invoice",$POST['eid']);
		
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		
		else if(strtolower($POST['mode']) == "update_total") {
				
			//update total , net total , general books entry at edit time start - dhaval 
			//$bill_sundry_tax = array_filter($POST['bill_sundry_tax']);
			$bill_sundry_tax = array_combine($POST['bill_sundry_tax'],$POST['bill_sundry_tax1']);
			
			if($POST['invoice_id']>0)
			{
				//$query="select sales_ledger_id,cust_id from tbl_invoice where invoice_id=".$POST['invoice_id']." ";
				$query="SELECT sales_ledger_id,sale_return_customer FROM tbl_sale_return where sale_return_id=".$POST['invoice_id']." ";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));


				$update_invoice['sale_return_gtotal'] = $POST['g_total'];
				$update_invoice['sale_return_total'] = $POST['basic_total'];
				
				update_record("tbl_sale_return",$update_invoice," sale_return_id=".$POST['invoice_id'] ,$dbcon);

				//Update Basic total in General book for sale return table - sales ledger entry
				$info_gen['amount'] = $POST['basic_total'];
				$info_gen['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_gen," table_id=".$POST['invoice_id']." and ledger_id=".$rel['sales_ledger_id']." and table_name='tbl_sale_return'" ,$dbcon);

				//Update Basic total in General book for sale return table - customer ledger entry
				$info_gen1['amount'] = $POST['g_total'];
				$info_gen1['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
				update_record("tbl_general_book",$info_gen1," table_id=".$POST['invoice_id']." and ledger_id=".$rel['sale_return_customer']." and ref_by='' and genral_book_status=0  and table_name='tbl_sale_return'" ,$dbcon);
				
				//update bill sundry in bill sundry table and general table 
				
				foreach ($bill_sundry_tax as $bill_sundry_tax_id => $bill_sundry_tax_amount) {

					$info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
					$info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			        $info_sundry_tax['user_id']	= $_SESSION['user_id'];
			        $info_sundry_tax['company_id']	= $_SESSION['company_id'];
					$info_sundry_tax['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
					$update_sundryid = update_record("tbl_bill_sundry_transaction",$info_sundry_tax," sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_sale_return' and sundry_voucher_id='$POST[invoice_id]'" ,$dbcon);
					
					$query1="select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and sundry_voucher_table='tbl_sale_return' and sundry_ledger_id=".$bill_sundry_tax_id." and isdelete=0  ";
					$rel1=brp_mysqli_fetch_assoc($dbcon->query($query1)); 
					
					$info_general_sundry['amount'] = $bill_sundry_tax_amount;
					$info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			        $info_general_sundry['user_id']	= $_SESSION['user_id'];
			        $info_general_sundry['company_id']	= $_SESSION['company_id'];
					$info_general_sundry['ref_date'] = date('Y-m-d',strtotime($POST['invoice_date']));
					update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_tax_id." and table_name='tbl_bill_sundry_transaction' 
						and table_id= ".$rel1['sundry_id']." " ,$dbcon);

           			
					// $info_sundry_tax['sundry_amount']=$bill_sundry_tax_amount;
					// $info_sundry_tax['cdate']	= date("Y-m-d H:i:s");
			  //       $info_sundry_tax['user_id']	= $_SESSION['user_id'];
			  //       $info_sundry_tax['company_id']	= $_SESSION['company_id'];
					
					// update_record("tbl_bill_sundry_transaction",$info_sundry_tax," sundry_ledger_id=".$bill_sundry_tax_id." and sundry_voucher_table='tbl_sale_return' and sundry_voucher_id='$POST[invoice_id]'" ,$dbcon);
					
					// $info_general_sundry['amount'] = $bill_sundry_tax_amount;
					// $info_general_sundry['cdate']	= date("Y-m-d H:i:s");
			  //       $info_general_sundry['user_id']	= $_SESSION['user_id'];
			  //       $info_general_sundry['company_id']	= $_SESSION['company_id'];
					
					// update_record("tbl_general_book",$info_general_sundry," ledger_id=".$bill_sundry_tax_id." and table_name='tbl_bill_sundry_transaction'" ,$dbcon);
					//add_general_book_entry($dbcon,"tbl_bill_sundry_transaction",$sundry_tax_insert,1,$bill_sundry_tax_id,$bill_sundry_tax_amount,'',$POST['invoice_date'],'',$curncy_trn);
					
					//echo $bill_sundry_tax_id.'-'.$bill_sundry_tax_amount."<br>";
            	}
				
			 /* $dsun = $dbcon->query("select * from tbl_bill_sundry_transaction where sundry_voucher_id='$POST[invoice_id]' and isdelete='0'");
			    while($r=brp_mysqli_fetch_array($dsun))
				{
					
					$sundry_id = $r['sundry_id'];
					
					$sundry['sundry_amount'] = $r['sundry_amount'];
					$sundry['cdate']			= date("Y-m-d H:i:s A");
					$sundry['user_id']			= $_SESSION['user_id'];
					$sundry['company_id']		= $_SESSION['company_id'];					
					
					update_record("tbl_bill_sundry_transaction",$sundry," sundry_id=".$sundry_id." and sundry_voucher_table='tbl_invoice'" ,$dbcon);
									
					$sundry_general['amount'] = $r['sundry_amount'];
					$sundry_general['entry_type'] = 1;
					
					$sundry_general['branch_id'] = $POST['branch_id'];
					$sundry_general['cdate']			= date("Y-m-d H:i:s A");
					$sundry_general['user_id']			= $_SESSION['user_id'];
					$sundry_general['company_id']		= $_SESSION['company_id'];
					
					
					update_record("tbl_general_book", $sundry_general," table_id=".$sundry_id." and table_name='tbl_bill_sundry_transaction'" ,$dbcon);
					
				
				} */
				
			}
			//update total , net total , general books entry at edit time end - dhaval 
			
			//print_r($bill_sundry_tax);
			//print_r($bill_sundry_addon);
			
		}
		
		else if(strtolower($POST['mode']) == "fieldadd") {
			
			if(isset($POST['currency_enable']) && $POST['currency_enable']==1){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }

			$company_state = get_company_data($dbcon,$_SESSION['company_id']);
			//$sale_gst = get_hsn_details($dbcon,$POST['product_hsn_code']);
			$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);
			$sale_gst = get_tax_cat_by_hsn($dbcon,$POST['product_hsn_code']);
			//echo $POST['product_hsn_code'];
			//print_r($sale_gst);
			if($company_state['stateid'] == $POST['cust_stateid']){
				
				$gst = $sale_gst['tax_gst']/2;
				$cgst_tax_per = $gst;
				$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
				$sgst_tax_per = $gst;
				$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
				
				$total_amount = $POST['product_amount'] + $cgst_tax_rate + $sgst_tax_rate;
				
			}else{
				$igst_tax_per = $sale_gst['tax_gst'];
				$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
				
				$total_amount = $POST['product_amount'] + $igst_tax_rate;
			}
			
			
				$info1['sale_return_invoice_no']	= $POST['invoice_number'];
				$info1['sale_return_product']		= $POST['product_id'];
				$info1['sale_return_qty']			= $POST['product_qty'];
				$info1['sale_return_rate']			= $POST['product_rate'];
				$info1['sale_return_unit']			= $POST['unit_id'];
				$info1['sale_return_hsn']			= $POST['product_hsn_code'];
				$info1['sale_return_amount']		= $POST['product_amount'];
				
				$info1['sale_return_cgst_tax_per']	= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
				$info1['sale_return_cgst_tax_amt']	= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
				$info1['sale_return_sgst_tax_per']	= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
				$info1['sale_return_sgst_tax_amt']	= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
				$info1['sale_return_igst_tax_per']		= isset($igst_tax_per) ? $igst_tax_per : 0 ;
				$info1['sale_return_igst_tax_amt']			= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;

				$info1['product_tax_cat']	= $sale_gst['tax_cat_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				$info1['sale_return_total_amount']	= $total_amount;
				
				$info1['user_id']	= $_SESSION['user_id'];
				
				$table='tbl_sale_return_transaction';$tableid='sale_return_transaction_id';
				
				if(!empty($POST['eid'])){
					$info1['sale_return_id']= $POST['eid'];
					$info1['trancation_status']	= 0;
				}
				else{
					$info1['sale_return_id']	= 0;
					$info1['trancation_status']	= 1;
				}
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon, $branch_id);

					if(!empty($POST['eid'])) {

					$sel_gen = $dbcon->query("select trn.*,p.product_type,p.ledger_id,s.sale_return_date from tbl_sale_return_transaction as trn 
        			left join product_mst as p on trn.sale_return_product=p.product_id
        			left join tbl_sale_return as s on s.sale_return_id = trn.sale_return_id
        			where trn.sale_return_transaction_id='$inserid'");

        			$r_gen=brp_mysqli_fetch_assoc($sel_gen);


        			add_general_book_entry($dbcon,"tbl_sale_return_transaction",$r_gen['sale_return_transaction_id'],2,$r_gen['ledger_id'],$POST['product_amount'],'',$r_gen['sale_return_date'],'',$curncy_trn); 
				
					}
				}
				else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
					$inserid=$POST['edit_id'];

					$sel_edit = $dbcon->query("select general_book_id from tbl_general_book where table_name='tbl_sale_return_transaction' and table_id='$POST[edit_id]'");
					$r_edit = brp_mysqli_fetch_assoc($sel_edit);

					$info_gen1['amount'] = $POST['product_amount'];

					update_record("tbl_general_book",$info_gen1," table_id=".$POST['edit_id']."  and table_name='tbl_sale_return_transaction'" ,$dbcon);	
				}
				
				/* insert to tax transaction table by Dhruv */
				if( ($cgst_tax_per != 0) && ($cgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'CGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$cgst_tax_per,$cgst_tax_rate,$inserid,"tbl_sale_return_transaction",$POST['product_id'],3,$POST['edit_id']);
				}
				if( ($sgst_tax_per != 0) && ($sgst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'SGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$sgst_tax_per,$sgst_tax_rate,$inserid,"tbl_sale_return_transaction",$POST['product_id'],3,$POST['edit_id']);
				}
				if( ($igst_tax_per != 0) && ($igst_tax_rate != 0) ){
					$cl_id = get_ledger_by_name($dbcon,'IGST');
					$insert_tax = add_tax_transaction_record($dbcon,$cl_id['l_id'],$igst_tax_per,$igst_tax_rate,$inserid,"tbl_sale_return_transaction",$POST['product_id'],3,$POST['edit_id']);
				}
				//$insert_tax=add_tax_record($dbcon,$inserid,"tbl_invoicetrn","trancation_id",$POST['formulaid'],$POST['taxable_value'], $branch_id);
				
				/*if(!empty($POST['invoice_id'])){
					$info_de['stock_status']=2;
					$updateid1=update_record("tbl_stock_trn", $info_de,"ref_name='invoice_trn' and ref_id=".$inserid ,$dbcon);
					
					$query="select i.*,sum(trn.product_amount) as gamo from tbl_invoice as i
							left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id
							where trn.trancation_status=0 and i.invoice_id=".$POST['invoice_id'];
						$result=$dbcon->query($query);
						$row=mysqli_fetch_assoc($result);
						
					minus_stock($dbcon,$info1['product_id'],$info1['unit_id'],$row['invoice_date'],"invoice_trn",$inserid,$info1['product_qty']);
					
					$general_book_id=get_general_book_id($dbcon,'tbl_invoice',$POST['invoice_id'],$row['cust_id']);
					
					add_general_book_entry($dbcon,"tbl_invoice",$POST['invoice_id'],2,$row['cust_id'],$row['gamo'],$general_book_id,$row['invoice_date']);
					general_book_tax_entry($dbcon,$POST['invoice_id']);
					general_book_sercices_entry($dbcon,$POST['invoice_id']);
				}*/
		}
		else if(strtolower($POST['mode']) == "formulavalue") 
		{
			$rate_total=0;$c_total=$POST['c_total'];
			$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$POST['eid']." order by tax_value desc";
			$row=$dbcon->query($qry);
			$j=0;
			//$dis=$POST['total']*$POST['t_dis']/100;
			$rate_total=$total=$POST['total'];
			while($tax=mysqli_fetch_assoc($row))
			{	
				if(strpos(strtolower(" ".$tax['tax_name']), "excise")==true)
				{
					$rate=$total*$tax['tax_value']/100;
					$total+=$rate;
				}
				else	
				{
					 $rate=($total)*$tax['tax_value']/100;
				}
				echo '<div class="form-group">
                                            <label class="col-md-5 control-label">'.$tax['tax_name'].'</label>
                                            <div class="col-md-5 col-xs-11">
                                            <input id="taxvalue'.$j.'" name="taxvalue'.$j.'" value= "'.$rate.'" type="text" class="form-control" readonly="readonly">
                                        </div>
					</div>
					<input id="taxname'.$j.'" name="taxname'.$j.'" value= "'.$tax['tax_name'].'" type="hidden" class="form-control">';
					$rate_total=$rate_total+$rate;
					$j++;
			}
			$g_total=$rate_total+$c_total;
			echo '<input id="rate" name="rate" value= "'.$g_total.'" type="hidden" class="form-control" >';
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			$pid=$POST['eid'];
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
			$qry="select * from product_mst where product_id=$pid";
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);
			
			$qry1="select led.stateid as lst,com.stateid as cst from tbl_ledger as led 
				left join tbl_company as com on com.company_id=led.company_id
				where l_id=".$POST['cust_id'];
			$result1=$dbcon->query($qry1);
			$row1=mysqli_fetch_assoc($result1);
			
			if($row1['lst']==$row1['cst']){
				$qry2="select * from formula_mst as led 
						where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}else{
				$qry2="select * from formula_mst as led 
						where formula_status=0 and tax_cat='INTER' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}
					
			echo json_encode( $row );
		
		}	
		else if(strtolower($POST['mode'])== "load_product_typeiwse")
		{
			echo get_product($dbcon,"",$POST['type_id']);
		}
		else if(strtolower($POST['mode'])== "get_product_amount")
		{
			//$arr=get_product_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "load_podata")
		{
				getpono($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_podate")
		{
			$qry2="select * from tbl_pono where po_id=".$POST['po_id'];
			$result2=mysqli_fetch_assoc($dbcon->query($qry2));
			echo json_encode($result2);	
		}
		else if(strtolower($POST['mode'])== "reminder")
		{
			$qry2="select * from pay_terms where terms_id=".$POST['paymentterms'];
			$result2=mysqli_fetch_assoc($dbcon->query($qry2));
			echo json_encode($result2);	
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=31 and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		
		}
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2'){
				$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
     	else if(strtolower($POST['mode']) == "load_tempoutward") {
			if($POST['eid']){
				 //$query="select mst.*,product.product_name,product.product_type,cat.unit_name,inv.invoice_no from  tbl_sale_return_transaction as mst
					//left join unit_mst as cat on cat.unitid=mst.sale_return_unit 
					//left join product_mst as product on product.product_id=mst.sale_return_product
					//left join tbl_invoice as inv on inv.invoice_id=mst.sale_return_invoice_no					
					//where sale_return_id='$POST[eid]' and trancation_status=0 and  mst.user_id=".$_SESSION['user_id'];
					$query="select mst.*,product.product_name,product.product_type,cat.unit_name,inv.invoice_no from  tbl_sale_return_transaction as mst
					left join unit_mst as cat on cat.unitid=mst.sale_return_unit 
					left join product_mst as product on product.product_id=mst.sale_return_product
					left join tbl_invoice as inv on inv.invoice_id=mst.sale_return_invoice_no					
					where sale_return_id='$POST[eid]' and trancation_status=0";
			}
			else{
				$query="select mst.*,product.product_name,product.product_type,cat.unit_name,inv.invoice_no from  tbl_sale_return_transaction as mst
					left join unit_mst as cat on cat.unitid=mst.sale_return_unit 
					left join product_mst as product on product.product_id=mst.sale_return_product
					left join tbl_invoice as inv on inv.invoice_id=mst.sale_return_invoice_no					
					where sale_return_id=0 and trancation_status=1";
			}
			/*$query="select mst.*,product.product_name,cat.unit_name,m.model_name from  tbl_invoicetrntemp as mst 
			left join unit_mst as cat on cat.unitid=mst.unit_id left join product_mst as product on product.product_id=mst.product_id left join model_mst as m on m.model_id=mst.model_id  where temp_status=0 and mst.user_id=".$_SESSION['user_id']." order by tempinvoicetrn_id Desc";*/
			$result=$dbcon->query($query);
			echo ' <div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th class="text-center" width="25%">Reference No</th>
							<th class="text-center" width="25%">Product Name</th>
							<th class="text-center" width="8%">Qty</th>
							<th class="text-center" width="8%">Rate</th>
							<th class="text-center" width="6%">Unit</th>
							<th class="text-center" width="12%">Amount</th>
						 	<th class="text-center" width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$product_type_arr = array("0", "1", "2", "3", "4", "5");
				
				$product_name=$dbcon->real_escape_string($rel['product_name']);
					
			 	echo '<tr id="fieldtr'.$id.'" >
					
					<td style="vertical-align:top;" class="text-center">
						<b>'.$rel['invoice_no'].'</b>
					</td>
					
					<td style="vertical-align:top;" class="text-center">
						<b>'.$rel['product_name'].'</b>
					</td>
					
					<td style="vertical-align:top;" class="text-center">
						'.$rel['sale_return_qty'].'
						<input type="hidden" id="trn_pro_stk'.$i.'" name="trn_pro_stk[]" value="'.$rel['sale_return_qty'].'">						
					';
					
					echo '</td>
					<td style="vertical-align:top;" class="text-center">
						'.$rel['sale_return_rate'].'
					</td>				
					<td style="vertical-align:top" class="text-center">
						'.$rel['unit_name'].'
					</td>
				
					<td style="vertical-align:top" class="text-center">
						'.($rel['sale_return_amount']).'<br>
						
					</td>
					
					<input type="hidden" name="amount[]" id="amount'.$i.'" value="'.$rel['sale_return_amount'].'"/>
					<td style="vertical-align:top">
						<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['sale_return_transaction_id'].',\' tbl_sale_return_transaction\',\'sale_return_transaction_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
						<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['sale_return_transaction_id'].',\' tbl_sale_return_transaction\',\'sale_return_transaction_id\');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
			
			echo '
			</table>			 
					</div></div>	
			';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$q = $dbcon -> query("SELECT mst.*,pro.product_name,pro.product_sale_gst,(select product_qty from tbl_invoicetrn where invoice_id=mst.sale_return_invoice_no and product_id=mst.sale_return_product and trancation_status=0) as sale_qty,(select sum(sale_return_qty) from tbl_sale_return_transaction where trancation_status !=2 and sale_return_product=mst.sale_return_product and sale_return_transaction_id!=mst.sale_return_transaction_id and sale_return_invoice_no=mst.sale_return_invoice_no) as used_qty FROM tbl_sale_return_transaction as mst left join product_mst as pro on mst.sale_return_product=pro.product_id WHERE mst.sale_return_transaction_id = '$POST[id]'");
			$r = $q->fetch_assoc();
			$r['remained_qty'] = $r['sale_qty'] - $r['used_qty'];
			/*if(strtolower($POST['table'])=='tbl_invoicetrntemp')
			{
				$row['producthtml']=getproduct($dbcon,0,'0,2');
			}
			else
			{
					$row['producthtml']=getproduct($dbcon,0,'0,2');
			}*/
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["product_type"].'');
			
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['isdelete']=1;	
			$info['trancation_status']=2;	
			
			$updateid=update_record("tbl_sale_return_transaction", $info, "sale_return_transaction_id=".$POST['eid'] , $dbcon);
			
			//update tax transaction table By Dhruv
			$info_tax['tx_status']=2;	
			$updatetax=update_record("tbl_tax_trn", $info_tax, "tx_transaction_id=".$POST['eid']." and tx_transaction_type='tbl_sale_return_transaction'" , $dbcon);
			
			// general book entry check for service and capital goods items 

			$info_general_book['genral_book_status'] = 2;

			update_record('tbl_general_book', $info_general_book,"table_name='tbl_sale_return_transaction' and table_id=".$POST['eid'] , $dbcon);


			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "last_rate")
		{
			$query="select product_rate,trancation_id,trancation_status,product_id from tbl_invoicetrn as trn left join tbl_invoice as mst on mst.invoice_id=trn.invoice_id where cust_id=".$POST["cust_id"]." and product_id=".$POST["product_id"]." and trancation_status=0 order by trancation_id DESC";
			$prel=mysqli_fetch_assoc($dbcon->query($query));
			echo $prel['product_rate'];
		}
		else if(strtolower($POST['mode'])== "load_consignee")
		{
				echo get_custmer_consignee($dbcon,$POST['cust_id']);
		}
		else if(strtolower($POST['mode'])== "load_sales_order")
		{
			echo get_sales_order($dbcon,$POST['cust_id'],$POST['branch_id']);
		}
		else if(strtolower($POST['mode'])== "load_sales_order_data")
		{
			
			//$deleteid=delete_record('tbl_invoicetrn',"trancation_status=3 and user_id=".$_SESSION['user_id'], $dbcon);
			
			$query_inv="select * from tbl_invoicetrn as trn 
					where trn.trancation_status=3 and trn.user_id=".$_SESSION['user_id']." and trn.company_id=".$_SESSION['company_id'];
				$rs_dispatch_inv=$dbcon->query($query_inv);	
			while($rel_inv=mysqli_fetch_assoc($rs_dispatch_inv))
			{	
				$info_inv['trancation_status'] = 2;
				$updateid_in=update_record('tbl_invoicetrn', $info_inv,"trancation_id=".$rel_inv['trancation_id'] ,$dbcon);
				
				$info_utax['tax_used_status'] = 2;
				$updateidutax=update_record('tbl_used_tax', $info_utax,"table_name='tbl_invoicetrn' and table_id='trancation_id' and used_transaction_id=".$rel_inv['trancation_id'] ,$dbcon);
			
			}
			
			
			
			/* $q = $dbcon -> query("SELECT * from tbl_sales_order where sales_order_id=".$POST['sales_order_id']);
			$rel = $q->fetch_assoc();
			
			$resp['transport_id'] = $rel['transport_id'];
			$resp['sales_order_no'] = $rel['sales_order_no'];
			$resp['sales_order_date'] = date("d-m-Y",strtotime($rel['sales_order_date']));
			$resp['pro_html'] = get_sales_order_data($dbcon,$POST['sales_order_id']);
			echo json_encode($resp); */
			
			$query="select * from tbl_sales_ordertrn as trn 
					where trn.sales_ordertrn_status=0 and trn.invoice_status=0 and trn.sales_order_id=".$POST['sales_order_id'];
				$rs_dispatch=$dbcon->query($query);	
				while($rel=mysqli_fetch_assoc($rs_dispatch))
				{	
					$resve_stoc=reserve_stock($dbcon,$rel['product_id'],$rel['unit_id'],"","","",$rel['sales_ordertrn_id'],$rel['branch_id']);
					
					if($resve_stoc>0){
						
						$query_used="select IFNULL(sum(product_qty),0) as used_qty from tbl_invoicetrn as trn 
								where trn.trancation_status=0 and trn.sales_ordertrn_id=".$rel['sales_ordertrn_id'];
							$rs_dispatch_used=$dbcon->query($query_used);	
						$rel_used=mysqli_fetch_assoc($rs_dispatch_used);
						
						$pending_qty=$rel['product_qty']-$rel_used['used_qty'];
						if($pending_qty>0){
							if($pending_qty>=$resve_stoc){
								$product_qty=$resve_stoc;
							}else{
								$product_qty=$pending_qty;
							}
							
							$total_value=$product_qty*$rel['product_rate'];
							if($rel['discount_per']>0){
								$discount_amount=($total_value*$rel['discount_per'])/100;
							}else{
								$discount_amount=0;
							}
							$taxablevalue=$total_value-$discount_amount;
							$product_amount=find_with_tax_amount($dbcon,$rel['formulaid'],$taxablevalue);
							
							$info1['product_id']		= $rel['product_id'];
							$info1['product_hsn_code']	= $rel['product_hsn_code'];
							$info1['product_qty']		= $product_qty;
							$info1['product_rate']		= $rel['product_rate'];
							$info1['unit_id']			= $rel['unit_id'];
							$info1['product_discount']	= $discount_amount;
							$info1['discount_per']		= $rel['discount_per'];
							$info1['formulaid']			= $rel['formulaid'];
							$info1['company_id']		= $_SESSION['company_id'];
							$info1['product_amount']	= $product_amount;
							$info1['taxable_value']		= $taxablevalue;
							$info1['sales_ordertrn_id']	= $rel['sales_ordertrn_id'];
							$info1['user_id']			= $_SESSION['user_id'];
							$info1['trancation_status']	= 3;
							$info1['branch_id']			= $rel['branch_id'];
							$info1['cdate']				= date("Y-m-d H:i:s");
							$table='tbl_invoicetrn';$tableid='trancation_id';
							$inserid=add_record($table, $info1, $dbcon);
							$insert_tax=add_tax_record($dbcon,$inserid,"tbl_invoicetrn","trancation_id",$rel['formulaid'],$info1['taxable_value']);
							
							
							//$info=get_product_tax($dbcon,$POST['taxable_value'],$POST['formulaid']);
							//$info1=array_merge($info1,$info);
							
							//$info1['bill_value']		= $POST['bill_value'];
							//$info1['bill_black_value']	= $POST['bill_black_value'];
							
							//$info1['model_id']			= $POST['model_id'];
							//$info1['ser_status']		= $POST['ser_status'];
						}else{
							$info_so['invoice_status'] = 1;
							$updateid=update_record('tbl_sales_ordertrn', $info_so,"sales_ordertrn_id=".$rel['sales_ordertrn_id'] , $dbcon);
						}
					}
				}
		}
		else if(strtolower($POST['mode'])== "load_sales_pro")
		{
			$resp['pro_html']=getproduct($dbcon,0,'0,2,3');
			echo json_encode($resp);
		}
		
		else if(strtolower($POST['mode'])== "loadsales_producttypedata")
		{
			$resp['pro_html'] 			= get_sales_order_typewise_data($dbcon,$POST['type_id'],$POST['sales_order_id']);
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode'])== "load_sale_productdata")
		{
			$product_id = $POST['product_id'];
			$invoice_number = $POST['invoice_number'];
			//echo "SELECT trn.product_id,trn.trancation_status,trn.invoice_id,trn.product_rate,(select sum(sale_return_qty) from tbl_sale_return_transaction where trancation_status!=2 and sale_return_invoice_no=trn.invoice_id) as used_qty ,trn.product_qty,p.product_desc,p.product_hsn,p.product_base_unit,h.hsn_id,h.sale_gst,h.hsn_code from tbl_invoicetrn as trn left join product_mst as p on p.product_id=trn.product_id left join mst_hsn_code as h on h.hsn_id=p.product_hsn where trn.product_id='$product_id' and trn.invoice_id='$invoice_number' and trn.trancation_status='0' ";
			
			$q = $dbcon -> query("SELECT trn.product_id,trn.trancation_status,trn.invoice_id,trn.product_rate,(select sum(sale_return_qty) from tbl_sale_return_transaction where trancation_status!=2 and sale_return_invoice_no=trn.invoice_id and sale_return_product=trn.product_id) as used_qty ,trn.product_qty,p.product_desc,p.product_hsn,p.product_base_unit,h.hsn_id,h.sale_gst,h.hsn_code from tbl_invoicetrn as trn left join product_mst as p on p.product_id=trn.product_id left join mst_hsn_code as h on h.hsn_id=p.product_hsn where trn.product_id='$product_id' and trn.invoice_id='$invoice_number' and trn.trancation_status='0' ");
			
			$resp = $q->fetch_assoc();
			$product_qty = $resp['product_qty'] - $resp['used_qty'];
			$row['product_desc'] 	= $resp['product_desc'];
			$row['product_hsn'] 	= $resp['hsn_code'];
			$row['sale_rate'] 		= $resp['product_rate'];
			$row['sale_qty'] 		= $product_qty;
			$row['sale_gst'] 		= $resp['sale_gst'];
			$row['product_base_unit'] = $resp['product_base_unit'];
			
			echo json_encode($row);
			
		}
		else if(strtolower($POST['mode'])== "load_qty")
		{
		    echo getsale_productqty($dbcon,$POST['product_id']);
		}
		else if(strtolower($POST['mode'])== "load_rate_hist")
		{
			$resp='';
			$query="select inv.*,cust.company_name,pro.product_name,trn.product_rate from tbl_invoice as inv
					inner join tbl_invoicetrn as trn on inv.invoice_id=trn.invoice_id 
					inner join tbl_customer as cust on cust.cust_id=inv.cust_id
					inner join tbl_product as pro on pro.product_id=trn.product_id
					where inv.invoice_status=0 and trn.trancation_status=0 and inv.cust_id=".$POST["cust_id"]." and trn.product_id=".$POST["product_id"]." order by trn.trancation_id DESC LIMIT 10";
				
			$rs_prel=$dbcon->query($query);
			$rs_prel_num_rows=mysqli_num_rows($rs_prel);
				
			if($rs_prel_num_rows>0){
				while($prel=mysqli_fetch_assoc($rs_prel)){
			
					$resp.='<tr>
								<td class="text-center">'.$prel['invoice_no'].'</td>
								<td class="text-center">'.date('d-m-y',strtotime($prel['invoice_date'])).'</td>
								<td class="text-center">'.$prel['product_rate'].'</td>
							</tr>';
					$row['cust_name']=$prel['company_name'];
					$row['product_name']=$prel['product_name'];		
				}
			}
			else{
				$resp.='<tr>
							<td colspan="3" class="text-center">NO DATA FOUND !!</td>
						</tr>';
					$row['cust_name']="";
					$row['product_name']="";
			}
			
			
			$row['resp']=$resp;
			
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			
			$product_type_arr = array("0", "1", "2", "3", "4", "5");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				if(!empty($POST['unit_id'])){
					$unit_id=$POST['unit_id'];
				}else{
					$unit_id=$get_pro_type_rel['product_base_unit'];
				}
				echo get_current_stock_new($dbcon,$product_id,$unit_id);
			}
			else{
				echo 9999;
			}
			
		}
		 // Dimple Panchal : Start
        else if(strtolower($POST['mode'])== "get_tax_on_total")
                        {
            $arr = get_tax_on_total($dbcon,$POST['total'],$POST['formulaid']);
            echo json_encode($arr);
        }
        else if(strtolower($POST['mode'])== "show_tcs_row")
        {
            $is_tcs_applicable = $dbcon->query("SELECT tcs_applicable FROM tbl_finance_setting WHERE company_id=".$_SESSION['company_id'])
                        ->fetch_object()->tcs_applicable;
            
            if($is_tcs_applicable) {
                $invoice_total = $dbcon->query("SELECT sum(g_total) as invoice_total FROM `tbl_invoice` where cust_id = ".$POST['cust_id']." and company_id = ".$_SESSION['company_id']." and invoice_status = 0")
                        ->fetch_object()->invoice_total;
                
                if($invoice_total >= 5000000){
                    echo "1";
                } else {
                    echo "0";
                }
            } else {
                echo "0";
            }
        }
	// Dimple Panchal : end
	//dhaval upadhyay : start 
		
	else if(strtolower($POST['mode'])== "get_invoice_by_cust"){
			
			$cust = $POST['cust'];
			$eid = $POST['eid'];
			
			
			echo get_invoice_by_cust($dbcon,$cust,$eid);
			
		}
	else if(strtolower($POST['mode'])== "get_product_from_invoice"){
			
			$invoice_no = $POST['invoice_no'];
			$product_id = $POST['product_id'];
			
			echo get_product_from_invoice($dbcon,$invoice_no,$product_id);
			
		}
	else if(strtolower($POST['mode'])== "get_tax_details_table")
	{
		$sale_return_id=$POST['eid'];
		$resp='';
		
		$query="SELECT sale_return_cgst_tax_per,sum(sale_return_cgst_tax_amt) as cgst_rate,sale_return_sgst_tax_per,sum(sale_return_sgst_tax_amt) as sgst_rate,sale_return_igst_tax_per,sum(sale_return_igst_tax_amt) as igst_rate,sale_return_amount FROM `tbl_sale_return_transaction` where sale_return_id='$sale_return_id' and trancation_status!=2 group by sale_return_cgst_tax_per,sale_return_sgst_tax_per,sale_return_igst_tax_per";

		$rs_prel=$dbcon->query($query);
		
		$rs_prel_fetch=brp_mysqli_fetch_assoc($dbcon->query("SELECT sale_return_cgst_tax_per,sum(sale_return_cgst_tax_amt) as cgst_rate,sale_return_sgst_tax_per,sum(sale_return_sgst_tax_amt) as sgst_rate,sale_return_igst_tax_per,sum(sale_return_igst_tax_amt) as igst_rate,sale_return_amount FROM `tbl_sale_return_transaction` where sale_return_id='$sale_return_id' and trancation_status!=2"));
		
		$rs_prel_num_rows=mysqli_num_rows($rs_prel);
		//print_r($rs_prel_fetch);exit;
		$resp='';
		$resp .= '<table class="table table-bordered">

		<tr>
		<th class="text-center">#</th>
		<th  class="text-center">Total Tax</th>
		<th  class="text-center">Taxable Amount</th>
		<th  class="text-center">Tax Amount</th>';
		if(($rs_prel_fetch['cgst_rate']!=0) || ($rs_prel_fetch['sgst_rate']!=0)){
		$resp .='<th  class="text-center">CGST</th>
		<th  class="text-center">SGST</th>';
		}if(($rs_prel_fetch['igst_rate']!=0)){
		$resp .= '<th  class="text-center">IGST</th>';
		}


		$resp .='</tr>';

		if($rs_prel_num_rows > 0){
		$taxRate = brp_mysqli_fetch_all($rs_prel);
		//print_r($taxRate);exit;
		$cnt=1;
		$cntloop=0;
		foreach($taxRate as $taxdetail) {
			$gst_tax_per = ($taxdetail['sale_return_cgst_tax_per'] != 0 || $taxdetail['sale_return_sgst_tax_per'] != 0) ? ($taxdetail['sale_return_cgst_tax_per']+$taxdetail['sale_return_sgst_tax_per']) : $taxdetail['sale_return_igst_tax_per'];
			$gst_tax_rate = ($taxdetail['cgst_rate'] != 0 || $taxdetail['sgst_rate'] != 0) ? ($taxdetail['cgst_rate']+$taxdetail['sgst_rate']) : $taxdetail['igst_rate'];

			if($taxdetail['sale_return_cgst_tax_per'] != 0 || $taxdetail['sale_return_sgst_tax_per'] != 0){
				$resp.='<tr>
				<th class="text-center">'.$cnt.'</th>
				<th class="text-center">'.$gst_tax_per.'%'.'</th>
				<th class="text-center">'.($taxdetail['sale_return_amount']).'</th>
				<th class="text-center">'.$gst_tax_rate.'</th>
				<th class="text-center">'.($taxdetail['sale_return_cgst_tax_per']).'%'.'</th>
				<th class="text-center">'.($taxdetail['sale_return_sgst_tax_per']).'%'.'</th>
				</tr>';

				if(!empty($POST['addontax1']) && $cntloop==0){
					foreach($POST['addontax1'] as $addtax){
						$cnt++;
						$exp_addtax = explode("-",$addtax);
						if($exp_addtax[1] != 0){
							$resp.='<tr>
								<th class="text-center">'.$cnt.'</th>
								<th class="text-center">'.$exp_addtax[1].'%'.'</th>
								<th class="text-center">'.($exp_addtax[2]).'</th>
								<th class="text-center">'.$exp_addtax[0].'</th>
								<th class="text-center">'.($exp_addtax[1]/2).'%'.'</th>
								<th class="text-center">'.($exp_addtax[1]/2).'%'.'</th>
							</tr>';
						}
					}
					$cntloop=1;
				}
			}

			if($taxdetail['sale_return_igst_tax_per'] != 0){
				$resp.='<tr>
				<th class="text-center">'.$cnt.'</th>
				<th class="text-center">'.$gst_tax_per.'%'.'</th>
				<th class="text-center">'.($taxdetail['sale_return_amount']).'</th>
				<th class="text-center">'.$gst_tax_rate.'</th>
				<th class="text-center">'.($taxdetail['sale_return_igst_tax_per']).'%'.'</th>
				</tr>';

				if(!empty($POST['addontax1']) && $cntloop==0){
					foreach($POST['addontax1'] as $addtax){
						$cnt++;
						$exp_addtax = explode("-",$addtax);
						//echo '<pre>';print_r($exp_addtax);
						if($exp_addtax[1] != 0){
							$resp.='<tr>
								<th class="text-center">'.$cnt.'</th>
								<th class="text-center">'.$exp_addtax[1].'%'.'</th>
								<th class="text-center">'.($exp_addtax[2]).'</th>
								<th class="text-center">'.$exp_addtax[0].'</th>
								<th class="text-center">'.($exp_addtax[1]).'%'.'</th>
							</tr>';
						}
					}
					$cntloop=1;
				}
			}
			$cnt++;

		}

		}

		$resp.='</table>';

		$row['resp']=$resp;

		echo json_encode($row);
	}
	
	else if(strtolower($POST['mode'])== "get_invoice_total_tax")
	{
		$sale_return_id = $POST['eid'];
		
		$resp='';
		$query="SELECT sum(sale_return_cgst_tax_amt) as cgst_rate,sum(sale_return_sgst_tax_amt) as sgst_rate,sum(sale_return_igst_tax_amt) as igst_rate FROM `tbl_sale_return_transaction` where sale_return_id='$sale_return_id' and trancation_status!=2";
			
		$rs_prel= brp_mysqli_fetch_assoc($dbcon->query($query));
		
		$row['isTcs']="0";

		$getCompanyConfig = getCompanyConfiguration($dbcon);
		$custLedgerDetails = get_cust_data_arr($dbcon,$POST['cust_id']);		
		$get_bill_sundry = get_bill_sundry_ledger($dbcon,1); 

		foreach ($get_bill_sundry as $billsundry) {
		
			if((($rs_prel['cgst_rate'] != 0) && $billsundry['l_name'] == 'CGST') || (($rs_prel['sgst_rate']!= 0) && $billsundry['l_name'] == 'SGST')){

				if(!empty($POST['addontax1'])){
					$addontax = $POST['addontax1']/2;
				}
				$gstValue = ($billsundry['l_name'] == 'CGST') ? $rs_prel['cgst_rate'] : (($billsundry['l_name'] == 'SGST') ? $rs_prel['sgst_rate'] : '');
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round($gstValue+$addontax,2).'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
				
				
			}
			if(($rs_prel['igst_rate'] != 0) && $billsundry['l_name'] == 'IGST'){
				if(!empty($POST['addontax1'])){
					$addontax = $POST['addontax1'];
				}
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round($rs_prel['igst_rate']+$addontax,2).'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
			}

			if(($billsundry['l_name'] == 'TCS') && ($getCompanyConfig['enable_tcs_reporting'] == 1) && ($custLedgerDetails['enable_tcs']==1) && ($POST['gross'] > $getCompanyConfig['gross_balance_limit'])){
				$row['isTcs']="1";
				$total_tcs_calculate = $rs_prel['product_amount']+$gstValue+$rs_prel['igst_rate'];
				$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$billsundry['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$billsundry['l_name'].'" name="bill_sundry_tax['.$billsundry['l_id'].']" type="number" class="form-control gst" title="'.$billsundry['l_name'].'"  value="'.round((($total_tcs_calculate*$billsundry['tax_value'])/100),2).'" placeholder="'.$billsundry['l_name'].'" readonly >
						<input type="hidden" name="tcs_per" id="tcs_per" value="'.$billsundry['tax_value'].'" >
					</div>
				</div>';
			}
		

		}
		
		
		$qry_add = $dbcon->query("select sum((tc.tax_per*trn.sale_return_amount)/100) as add_sum , trn.*,l.l_name,l.l_id,t.tax_cat_id from tbl_sale_return_transaction as trn left join tbl_tax_category as t on t.tax_cat_id=trn.product_tax_cat 
		left join tbl_tax_category_details as tc on tc.tax_cat=t.tax_cat_id 
		left join tbl_ledger as l on l.l_id=tc.tax_id
		where tc.tax_additional='1' and trn.sale_return_id='$invoice_id' and trn.trancation_status!=2 and tc.isdelete='0' group by tc.tax_id 
		");
		while($row1=brp_mysqli_fetch_array($qry_add))
		{
			
			//$tax_rate = ($row1['tax_per']*$row1['product_amount'])/100;
			
			
			$resp.='<div class="form-group">
					<label class="col-md-5 control-label">'.$row1['l_name'].'</label>
					<div class="col-md-5 col-xs-11">
						<input id="'.$row1['l_name'].'" name="bill_sundry_tax['.$row1['l_id'].']" type="number" class="form-control gst" title="'.$row1['l_name'].'"  value="'.number_format($row1['add_sum'],2,'.','').'" placeholder="'.$billsundry['l_name'].'" readonly >
					</div>
				</div>';
		}
		
		$row['resp']=$resp;
		
		echo json_encode($row);
	}
	
	else if(strtolower($POST['mode'])== "get_bill_sundry_details")
	{
		$invoice_id=$POST['invoice_id'];
		//echo '<pre>'; print_r($POST);exit;
		$q = $dbcon -> query("SELECT * from tbl_ledger_bill_sundry where sundry_ledger_id=".$POST['sundry_ledger_id']." and company_id = ".$_SESSION['company_id']." ");
		$resp = $q->fetch_assoc();

		$q_tax = $dbcon -> query("select tax_gst from tbl_tax_category where tax_cat_id=".$resp['sundry_gst']." ");
		$resp_tax = $q_tax->fetch_assoc();

		$basic_total = $POST['basic_amount'];
		$netamount = $POST['netamount'];
		$taxableamount = $POST['taxableamount'];
		
		$default_amount = $POST['default_amount'];

		if(($resp['apply_gst'] == 2) && (!empty($resp['sundry_gst']))){
			$taxvl = ($resp_tax['tax_gst']*$POST['default_amount'])/100;
			$taxgst=$resp_tax['tax_gst'];
		}else{
			$taxvl=0;
			$taxgst=0;
		}
		
		
		//print_r($POST['totalsundryexist']);exit;
		$totalsundryexist = $POST['totalsundryexist'];

		if($resp['sundry_type'] == 1){
			if($resp['sundry_amount_of'] == 1){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = $netamount + $default_amount;
					$pervalue =  $default_amount;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = $basic_total + $default_amount + $taxableamount + $totalsundryexist;
					$pervalue =  $default_amount;
				}else if($resp['sundry_calculate_on'] == 3){
					$finalNetAmount = $basic_total + $taxableamount + $default_amount + $totalsundryexist;
					$pervalue =  $default_amount;
				}

			}else if($resp['sundry_amount_of'] == 2){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = (($netamount * $default_amount)/100) + $netamount;
					$pervalue = -($netamount * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = (($basic_total * $default_amount)/100) + $basic_total + $taxableamount + $totalsundryexist;
					$pervalue = -($basic_total * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 3){
					$finalNetAmount = ((($basic_total + $taxableamount) * $default_amount)/100) + $basic_total + $taxableamount + $totalsundryexist;
					$pervalue = -(($basic_total + $taxableamount) * $default_amount)/100;
				}
			}
			$per_amount_show='';
		}
		else if($resp['sundry_type'] == 2){
			if($resp['sundry_amount_of'] == 1){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = $netamount - $default_amount;
					$pervalue =  $default_amount;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = ($basic_total - $default_amount) + $taxableamount + $totalsundryexist;
					$pervalue =  $default_amount;
				}else if($resp['sundry_calculate_on'] == 3){
					$finalNetAmount = (($basic_total + $taxableamount) - $default_amount) + $totalsundryexist;
					$pervalue =  $default_amount;
				}
			}else if($resp['sundry_amount_of'] == 2){
				if($resp['sundry_calculate_on'] == 1){
					$finalNetAmount = $netamount - (($netamount * $default_amount)/100);
					$pervalue = -($netamount * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 2){
					$finalNetAmount = (($basic_total + $taxableamount) - (($basic_total * $default_amount)/100)) + $totalsundryexist;
					$pervalue = -($basic_total * $default_amount)/100;
				}else if($resp['sundry_calculate_on'] == 3){
					$finalNetAmount = (($basic_total + $taxableamount) + ((($basic_total + $taxableamount) * $default_amount)/100)) + $totalsundryexist;
					$pervalue = -(($basic_total + $taxableamount) * $default_amount)/100;
				}
			}
			
			$per_amount_show = '('.$default_amount.'%'.')';
			
		}
		
		//if invoice is edit time insert data in database start - dhaval
		if($invoice_id>0)
		{
			$info_sundry_addon['sundry_ledger_id']=$POST['sundry_ledger_id'];
			$info_sundry_addon['sundry_amount']=$pervalue;
			$info_sundry_addon['sundry_voucher_id']=$invoice_id;
			$info_sundry_addon['sundry_voucher_type']=SALES_RETURN_VOUCHER;
			$info_sundry_addon['sundry_voucher_table']='tbl_sale_return';
			$info_sundry_addon['cdate']	= date("Y-m-d H:i:s");
			$info_sundry_addon['user_id']	= $_SESSION['user_id'];
			$info_sundry_addon['company_id']	= $_SESSION['company_id'];
			
			$info_sundry_addon['sundry_gst_per']	= $taxgst;
			$info_sundry_addon['sundry_gst_amount']	= $taxvl;
			//print_r(array_merge($info_sundry_addon,$curncy_trn));
			
			if(isset($POST['currency_enable'])){
            	$curncy_trn['currency_id'] = $POST['currency_id'];
            	$curncy_trn['currency_rate'] = $POST['currency_rate'];
            }else{
            	$basecurrency = getbasecurrency($dbcon);
            	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
            	$curncy_trn['currency_rate'] = 1;
            }
			
			$sundry_addon_insert=add_record('tbl_bill_sundry_transaction',array_merge($info_sundry_addon,$curncy_trn), $dbcon);

			//general book entry 
			$invoice_date = date("Y-m-d",strtotime($POST['invoice_date']));

			if($pervalue < 0){
				$ledger_entry_type = 1;
				$cust_entry_type = 2; 
			}else{
				$ledger_entry_type = 2;
				$cust_entry_type = 1;
			}
			
			$info_general_addon['ledger_id']=$POST['sundry_ledger_id'];
			$info_general_addon['amount']=abs($pervalue);
			$info_general_addon['table_id']=$sundry_addon_insert;
			$info_general_addon['entry_type']=$ledger_entry_type;
			$info_general_addon['table_name']='tbl_bill_sundry_transaction';
			$info_general_addon['ref_date']=$invoice_date;
			$info_general_addon['cdate']	= date("Y-m-d H:i:s");
			$info_general_addon['user_id']	= $_SESSION['user_id'];
			$info_general_addon['company_id']	= $_SESSION['company_id'];
			
			add_record('tbl_general_book',array_merge($info_general_addon,$curncy_trn), $dbcon);

			$info_gen2['table_name']	= 'tbl_sale_return';
			$info_gen2['table_id']		= $invoice_id;
			$info_gen2['entry_type']	= $cust_entry_type;
			$info_gen2['ref_date']		= date('Y-m-d',strtotime($invoice_date));
			$info_gen2['ledger_id']		= $POST['cust_id'];
			$info_gen2['amount']		= abs($pervalue);
			$info_gen2['user_id']		= $_SESSION['user_id'];
			$info_gen2['cdate']			= date("Y-m-d H:i:s");
			$info_gen2['company_id']	= $_SESSION['company_id'];
			$info_gen2['ref_by'] = 'tbl_addon_bill_sundry';
						
			$inserid_gen2=add_record("tbl_general_book", array_merge($info_gen2,$curncy_trn) , $dbcon);
			
			//general bbok entry 
			
			// $info_general_addon['ledger_id']=$POST['sundry_ledger_id'];
			// $info_general_addon['amount']=$pervalue;
			// $info_general_addon['table_id']=$sundry_addon_insert;
			// $info_general_addon['entry_type']=1;
			// $info_general_addon['table_name']='tbl_sale_return';
			// $info_general_addon['ref_date']=date("Y-m-d",strtotime($POST['invoice_date']));
			// $info_general_addon['cdate']	= date("Y-m-d H:i:s");
			// $info_general_addon['user_id']	= $_SESSION['user_id'];
			// $info_general_addon['company_id']	= $_SESSION['company_id'];
			
			// add_record('tbl_general_book',array_merge($info_general_addon,$curncy_trn), $dbcon);
		}
		//if invoice is edit time insert data in database end - dhaval
		
		echo json_encode($finalNetAmount.','.$pervalue.','.$per_amount_show.','.$invoice_id.','.$taxvl.','.$resp_tax['tax_gst']);
	}
	else if(strtolower($POST['mode'])== "get_bill_sundry_label")
	{
		$sundry_id = $POST['sundry_id'];
		
		$row=get_sundry_details($dbcon,$sundry_id);
		
		echo $row['sundry_amount_of'];
		
	}
	else if(strtolower($POST['mode'])== "get_ledger_details")
	{
		
		$ledger_id=$POST['ledger_id'];
		
		$row=get_ledger_details($dbcon,$ledger_id);
		
		echo json_encode($row);
	}
	else if(strtolower($POST['mode'])== "tcs_reversal_trn")
	{
		$sale_ledger_id = $POST['sale_ledger_id'];
		$ref_id = $POST['ref_id'];
		$amt_reversed = $POST['amt_reversed'];
		$tcs_collected_on = date("Y-m-d",strtotime($POST['tcs_collected_on']));
		$tcs_amt = $POST['tcs_amt'];
		$sur_amt = $POST['sur_amt'];
		$total_tax = $POST['total_tax'];
		$edit_id_tcs_reversal = $POST['edit_id_tcs_reversal'];
		$invoice_id = $POST['invoice_id'];
		
		$info1['sale_invoice_id'] = $invoice_id;
		$info1['sale_ledger_id'] = $sale_ledger_id;
		$info1['sale_ref_no'] = $ref_id;
		$info1['sale_amt_reversed'] = $amt_reversed;
		$info1['sale_amt_tcs_collected'] = $tcs_collected_on;
		$info1['sale_tcs_amt'] = $tcs_amt;
		$info1['sale_sur_amt'] = $sur_amt;
		$info1['sale_total_tax'] = $total_tax;
		$info1['user_id'] = $_SESSION['user_id'];
		$info1['company_id'] = $_SESSION['company_id'];
		$info1['usertype_id'] = $_SESSION['usertype_id'];
		
		if($edit_id_tcs_reversal=='')
		{
			$insert1 = add_record('tbl_tcs_reverse_transaction', $info1, $dbcon, '');
			
			if($insert1)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		else
		{
			$update1=update_record("tbl_tcs_reverse_transaction", $info1,"sale_return_trn_id=".$edit_id_tcs_reversal , $dbcon,'');
			
			if($update1)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		
	}
	else if(strtolower($POST['mode']) == "fetch_tcs_reversal") {
		
			
			$where.="  and s.sale_ledger_id='$POST[sale_ledger_id]' and s.sale_invoice_id='$POST[sale_return_id]'";
			$appData = array();
			$i=1;
			$aColumns = array('s.sale_return_trn_id','s.sale_ref_no','s.sale_amt_reversed','s.sale_amt_tcs_collected','s.sale_tcs_amt','s.sale_sur_amt','s.sale_total_tax','i.invoice_id','i.invoice_no');
			$sIndexColumn = "sale_return_trn_id";
			$isWhere = array("s.sale_return_trn_status = 0 and s.company_id = ".$_SESSION['company_id']." ".$where);
			$sTable = "tbl_tcs_reverse_transaction as s";			
			$isJOIN = array('inner join tbl_invoice i on i.invoice_id=s.sale_ref_no');
			$hOrder = "s.sale_return_trn_id desc";
			include($path.'include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_tcs_reversal('.$row['sale_return_trn_id'].');"><i class="fa fa-pencil"></i></button>';
				
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_tcs_reversal('.$row['sale_return_trn_id'].')"><i class="fa fa-trash-o"></i></button>';
				
				$row_data[] = $row["sr"];
				$row_data[] = $row["invoice_no"];
				$row_data[] = $row["sale_amt_reversed"];
				$row_data[] = $row["sale_amt_tcs_collected"];
				$row_data[] = $row["sale_tcs_amt"];
				$row_data[] = $row["sale_sur_amt"];
				$row_data[] = $row["sale_total_tax"];
				$row_data[] = $edit_btn.' '.$delete_btn;
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "preedit_tcs_return") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_tcs_reverse_transaction` WHERE `sale_return_trn_id` = '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "delete_sale_return_trn") {
			$info['sale_return_trn_status']='2';
			$info['isdelete']='1';
			$updateid=update_record('tbl_tcs_reverse_transaction',$info,"sale_return_trn_id=".$POST['id'] , $dbcon,'');
			
			if($updateid)
			echo "1";
			else
			echo "0";
		
		}
	else if(strtolower($POST['mode'])== "tcs_reversal_add")
	{
		$sale_ledger_id = $POST['sale_ledger_id'];
		$tcs_section = $POST['tcs_section'];
		$tcs_sub_cat_code = $POST['tcs_sub_cat_code'];
		
		$info1['sale_return_ledger_id'] = $sale_ledger_id;
		$info1['sale_return_section_code'] = $tcs_section;
		$info1['sale_return_cat_code'] = $tcs_sub_cat_code;
		
		$info1['user_id'] = $_SESSION['user_id'];
		$info1['company_id'] = $_SESSION['company_id'];
		$info1['usertype_id'] = $_SESSION['usertype_id'];
		
		$query = $dbcon->query("select sale_return_id from tbl_tcs_reverse where sale_return_ledger_id='$sale_ledger_id' and sale_return_status!='2'");
		$count = brp_mysqli_num_rows($query);
		
		
		if($count==0)
		{
			$insert1 = add_record('tbl_tcs_reverse', $info1, $dbcon, '');
			
			if($insert1)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		else
		{
			$update1=update_record("tbl_tcs_reverse", $info1,"sale_return_ledger_id=".$sale_ledger_id , $dbcon,'');
			
			if($update1)
			{
				echo "1";
			}
			else
			{
				echo "0";
			}
		}
		
	}
	else if(strtolower($POST['mode'])== "get_tcs_reverse_code"){
		
		$sale_ledger_id=$POST['sale_ledger_id'];
		$query = $dbcon->query("select * from tbl_tcs_reverse where sale_return_ledger_id='$sale_ledger_id' and sale_return_status!='2'");
		$sel = brp_mysqli_fetch_assoc($query);
		
		$row['sale_return_section_code']=$sel['sale_return_section_code'];
		$row['sale_return_cat_code']=$sel['sale_return_cat_code'];
		
		echo json_encode($row);
		
	}
	
	else if(strtolower($POST['mode'])== "get_all_bill_sundry")
	{
		$invoice_id=$POST['invoice_id'];
		
		$q=$dbcon->query("select b.*,l.sundry_ledger_id,l.sundry_type,l.sundry_nature,l.sundry_amount_of,l.sundry_calculate_on,l.sundry_default_value,le.l_name,le.l_id from tbl_bill_sundry_transaction as b left join tbl_ledger_bill_sundry as l on l.bill_sundry_id=b.sundry_ledger_id left join tbl_ledger as le on le.l_id=b.sundry_ledger_id where b.sundry_voucher_id='$invoice_id' and b.sundry_voucher_table='tbl_sale_return' and b.isdelete='0' and le.default_sundry='0'");
		
		$resp=brp_mysqli_fetch_all($q);
		
		$str="";$cnt=1;
		foreach($resp as $r)
		{
			
			if($r['sundry_type'] == 1){
				
				$per_amount_show='';
			}
			else if($r['sundry_type'] == 2){
				
				$per_amount_show = ' ('.$r['sundry_default_value'].'%'.')';
				
			}
			
			if(empty($r['sundry_gst_per'])){
				$str.='<div class="form-group">
						<label class="col-md-5 control-label">'.$r['l_name'].'</label>
						<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
						</div>
						<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
						</div>
					</div>';
			}else{
				$str.='<div class="form-group">
						<label class="col-md-5 control-label">'.$r['l_name'].'</label>
						<div class="col-md-4">
							<input id="sundry_name" name="bill_sundry_addon['.$r['l_id'].']" type="hidden" value="'.$r['sundry_amount'].'">
							<input id="default_amount" name="default_amount[]" type="text" class="form-control billsundryclass" value="'.$r['sundry_amount'].'" readonly placeholder="Amount">
							<input class="addontax" name="bill_sundry_addon_tax['.$r['l_id'].']" type="hidden" value="'.$r['sundry_gst_amount'].'-'.$r['sundry_gst_per'].'-'.$r['sundry_amount'].'" >
						</div>
						<div class="col-md-3">
							<button style="margin-top: 5px;" class="btn btn-round btn-danger btn-xs" title="remove" 
								type="button" value="'.$cnt.'" onclick="removeSundry(\'\',\''.$r['sundry_amount'].'\',this.value,\''.$r['sundry_id'].'\')"><i class="fa fa-times"></i></button>
						</div>
					</div>';
			}
			
			$cnt++;
			//$str.=$r['sundry_amount'];
		}
		
		echo $str;
		//echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "remove_sundry"){
		
		$ledger_id = $POST['ledger_id'];
		$sale_return_id = $POST['edit_id'];
		$cust_ledger_id = $POST['cust_ledger_id'];

		$info['isdelete']=1;
		
		$updateid=update_record('tbl_bill_sundry_transaction', $info,"sundry_id=".$POST['ledger_id'] , $dbcon);

		$info_general['genral_book_status'] = 2;

		$q = $dbcon -> query("SELECT amount from tbl_general_book where table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction' ");
		$resp = $q->fetch_assoc();

		$update_gen_cusid=update_record('tbl_general_book', $info_general,"table_id=".$sale_return_id." and ledger_id=".$cust_ledger_id." and amount=".$resp['amount']." and ref_by='tbl_addon_bill_sundry' and  table_name='tbl_sale_return'" , $dbcon);

		$updateid=update_record('tbl_general_book', $info_general,"table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction'" , $dbcon);
		
		//$info_general['genral_book_status'] = 2;
		
		//$updateid=update_record('tbl_general_book', $info_general,"table_id=".$POST['ledger_id']." and table_name='tbl_bill_sundry_transaction'" , $dbcon);

	}
	
	
	//dhaval upadhyay : end 	
	
function get_product_tax($dbcon,$product_amount,$formulaid)
{
	$qry="SELECT formula.*,tax.* FROM `formula_mst` as formula inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) WHERE formulaid=".$formulaid." order by tax_value desc";
	$row=$dbcon->query($qry);
	$rate_total=$total=$product_amount;
	$i=1;
	while($tax=mysqli_fetch_assoc($row))
	{	
		$info['tax_name'.$i]=$tax['tax_name'];
		$info['tax_amount'.$i]=$tax_amount=($total)*$tax['tax_value']/100;
		$rate_total+=$tax_amount;
                $tax_total_amount+=$info['tax_amount'.$i];
		$i++;
	}
	for($j=$i;$j<=3;$j++)
	{
		$info['tax_name'.$j]='';
		$info['tax_amount'.$j]='';		
	}
	$info['total']=$rate_total;
        //$info['tax_total_amount']=$tax_total_amount;
	return $info;
}
function upd_qt_done_sts($dbcon,$quotation_id,$invoice_id){
	$qt_trn_qry="select sum(product_qty) as qt_qty from tbl_quotation_trn where quot_trn_status=0 and quotation_id=".$quotation_id;
	$qt_trn_rel=mysqli_fetch_assoc($dbcon->query($qt_trn_qry));
	//Invoice Qty
	$inv_trn_qry="select sum(product_qty) as inv_qty from tbl_invoicetrn as trn
	inner join tbl_invoice as inv on inv.invoice_id=trn.invoice_id
	where trn.trancation_status=0 and inv.invoice_status=0 and inv.quotation_id=".$quotation_id;
	$inv_trn_rel=mysqli_fetch_assoc($dbcon->query($inv_trn_qry));
	
	if(floatval($inv_trn_rel['inv_qty'])>=$qt_trn_rel['qt_qty']){
		$upd_qt="update tbl_quotation set inv_done_status=1 where quotation_id=".$quotation_id;
		$upd_qt_rs=$dbcon->query($upd_qt);
	}
	
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_quotation_trn set inv_done_status=1 where quot_trn_status=0 and find_in_set(quot_trn_id,(select group_concat(ref_quot_trn_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=".$invoice_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
}
function upd_spare_inv_sts($dbcon,$complaint_id,$invoice_id){
	//Update Quotation trn rows
	$upd_qt_trn_qry="update tbl_complain_spare_part set s_inv_status=1 where s_inv_status=0 and find_in_set(s_id,(select group_concat(ref_s_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=".$invoice_id."))";
	$upd_qt_trn_qry_rs=$dbcon->query($upd_qt_trn_qry);
	
	$upd_comp_trn_qry="update tbl_complaint_trn set inv_done_status=1 where complaint_id=".$complaint_id;
	$upd_comp_trn_qry_rs=$dbcon->query($upd_comp_trn_qry);
}
function upd_inv_srl_no($dbcon,$invoice_id){
	$upd_qry="update `tbl_inv_srl_trn` set invoice_id=$invoice_id where find_in_set(trancation_id,(select group_concat(trancation_id) from tbl_invoicetrn where trancation_status=0 and invoice_id=$invoice_id));";
	$upd_qry_rs=$dbcon->query($upd_qry);
}
function copy_srl_no($dbcon,$invoice_id){
	//Invoice DATA
	$inv_qry="select cust_id,invoice_no,invoice_date from tbl_invoice where invoice_id=".$invoice_id;
	$inv_rel=mysqli_fetch_assoc($dbcon->query($inv_qry));
	
	$srl_qry="select srl.pro_srl_no,(select product_id from tbl_invoicetrn where trancation_id=srl.trancation_id) as pro_id from tbl_inv_srl_trn as srl where srl.inv_srl_trn_status=0 and srl.invoice_id=".$invoice_id;
	$srl_qry_rs=$dbcon->query($srl_qry);
	while($srl_rel=mysqli_fetch_assoc($srl_qry_rs)){
		$info1['cust_id']				= $inv_rel['cust_id'];
		$info1['sold_inv_foc_date']		= date("Y-m-d",strtotime($inv_rel['invoice_date']));
		$info1['product_id']			= $srl_rel['pro_id'];
		$info1['sold_pro_srl_no']		= $srl_rel['pro_srl_no'];
		$info1['cdate']					= date("Y-m-d H:i:s");
		$info1['user_id']				= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];
		
		$table='tbl_cust_sold_pro';$tableid='cust_sold_pro_id';
		$inserid=add_record($table, $info1, $dbcon);
		
	}
}
function general_book_tcs_entry($dbcon,$invoice_id,$branch_id){
    
        $qry = "select * from tbl_invoice as cert where invoice_status=0 and company_id = ".$_SESSION['company_id']." and invoice_id=".$invoice_id;
	$result = $dbcon->query($qry);
	$invoice = mysqli_fetch_assoc($result);
        
        $tax_qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax 
                WHERE tax_used_status=0 and used_transaction_id in (".$invoice_id.") and table_name='tbl_invoice' 
                GROUP BY ledger_id ORDER BY tax_used_id desc";
	$row=$dbcon->query($tax_qry);
        $tax = mysqli_fetch_assoc($row);
        
        
        $ledger_id = $dbcon->query("SELECT l_id FROM `tbl_ledger` where l_name like 'TCS' and l_group = '".DUTIES_AND_TAXES."' and company_id=".$_SESSION['company_id'])
            ->fetch_object()->l_id;
        
        $general_book_id = $dbcon->query("select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_invoice'")
            ->fetch_object()->general_book_id;
                
        $info['table_name']     = "tbl_invoice";
        $info['table_id']	= $invoice_id;
        $info['ref_date']	= date("Y-m-d",strtotime($invoice['invoice_date']));
        $info['entry_type']     = 1;
        $info['ledger_id']	= $ledger_id;
        $info['amount']         = $invoice['tcs_total'];
        $info['user_id']        = $_SESSION['user_id'];
        $info['cdate']		= date("Y-m-d H:i:s");
        $info['company_id']     = $_SESSION['company_id'];

        if($general_book_id){
                $updateid=update_record("tbl_general_book", $info,"general_book_id=".$general_book_id , $dbcon, $branch_id);
        }else{
                $inserid=add_record("tbl_general_book", $info, $dbcon, $branch_id);
        }
}

function general_book_tax_entry($dbcon,$invoice_id,$branch_id){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and company_id = ".$_SESSION['company_id']." and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$re["tid"].") and table_name='tbl_invoicetrn' group by ledger_id order by tax_used_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_invoice'";
                $ros=$dbcon->query($qry12);
                $re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']            = "tbl_invoice";
		$info1['table_id']		= $invoice_id;
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']            = 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['tamount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']            = $_SESSION['company_id'];
		
		if(!empty($re2['general_book_id'])){
			$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon, $branch_id);
		}else{
			$inserid=add_record("tbl_general_book", $info1, $dbcon, $branch_id);
		}
		//var_dump($re2['general_book_id']);
	}
	
}
function general_book_sercices_entry($dbcon,$invoice_id, $branch_id){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT itrn.*,promst.ledger_id FROM `tbl_invoicetrn` as itrn 
			left join product_mst as promst on promst.product_id=itrn.product_id
			WHERE itrn.trancation_status=0 and promst.product_type=8 and itrn.invoice_id=".$invoice_id." order by itrn.trancation_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$tax['trancation_id']." and table_name='tbl_invoicetrn'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_invoicetrn";
		$info1['table_id']		= $tax['trancation_id'];
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['product_amount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']	= $_SESSION['company_id'];
		
		if(!empty($re2['general_book_id'])){
			$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon, $branch_id);
		}else{
			$inserid=add_record("tbl_general_book", $info1, $dbcon, $branch_id);
		}
		//var_dump($re2['general_book_id']);
	}

}
?>