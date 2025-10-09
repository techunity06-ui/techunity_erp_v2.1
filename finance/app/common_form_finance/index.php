<?php
session_start(); //start session
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch_cost_center") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_ZONE_UPDATE,
	        ADMINISTRATOR_ZONE_DELETE
	    ]);
			
		$appData = array();
		$i=1;
		$aColumns = array('ct.costcenter_transaction_id', 'ct.costcenter_amount','ct.costcenter_id','ct.cdate','ct.costcenter_status', 'ct.user_id','c.cost_center_name','bt.balance_type_name');
		$sIndexColumn = "ct.costcenter_transaction_id";
		$isWhere = array("ct.isdelete =0 and ct.company_id in (0,$_SESSION[company_id]) and ct.cost_center_table='".$POST['cost_center_table']."' and  cost_center_table_id='".$POST['cost_center_table_id']."'");
		$sTable = "tbl_cost_center_transaction as ct";			
		$isJOIN = array("left join tbl_cost_center as c on c.cost_center_id=ct.costcenter_id","left join mst_balance_type as bt on bt.balance_typeid=ct.costcenter_entry_type");
		$hOrder = "ct.costcenter_transaction_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			
			if($row['balance_type_name']=="Debit")
			{
				$btype="<strong style='color:red'>".$row['balance_type_name']."</strong>";
			}
			else
			{
				$btype="<strong style='color:green'>".$row['balance_type_name']."</strong>";
			}
			
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cost_center_name']; 
			$row_data[] = $row['costcenter_amount']; 
			$row_data[] = $btype; 
			
			$edit_btn='';$delete_btn='';
			
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_cost_center('.$row['costcenter_transaction_id'].');"><i class="fa fa-pencil"></i></button>';
				
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_cost_center('.$row['costcenter_transaction_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}

	else if (strtolower($POST['mode']) == "bill_form_show") {

		$bill_type = $POST['bill_type'];

		if($bill_type=='PURCHASE')
		{
			$due_invoice_list=get_due_purchase_bill_by_cust($dbcon,$POST['cus_id']);
		}
		else if($bill_type=='JV')
		{
			$due_invoice_list=get_due_by_cust_jv($dbcon,$POST['cus_id']);
		}
		else
		{
			$due_invoice_list=get_due_invoices_by_cust($dbcon,$POST['cus_id']);
		}

		$row['due_invoice_list'] = $due_invoice_list;
		$row['cus_id'] = $POST['cus_id'];
		$row['cus_name'] = $POST['cus_name'];
		$row['bill_type'] = $bill_type;

		echo json_encode($row);
		//echo $due_invoice_list;
	}

	else if(strtolower($POST['mode']) == "get_tds_details") {
		
		
		$html='';
		
		$cust_details = get_ledger_details($dbcon,$POST['cus_id']);



		//echo '<pre>'; print_r($cust_details['party_pay_cat']);exit;

		if($cust_details['party_pay_cat'] == 0){
			$html.='<tr class="it_act"><th colspan="4">Data Not Available</th></tr>';
			echo $html;exit;
		}
		//echo "SELECT tc.*,tcd.* FROM `tbl_tds_tax_category` as tc left join tbl_tds_tax_category_detail as tcd on tc.tds_cat_id=tcd.tds_cat_id where tc.tds_cat_id=".$POST['tds_cat_id']." and tcd.tds_payee=".$cust_details['party_cat_id']."";exit;

		$q = $dbcon -> query("SELECT tc.*,tcd.* FROM `tbl_tds_tax_category` as tc left join tbl_tds_tax_category_detail as tcd on tc.tds_cat_id=tcd.tds_cat_id where tc.tds_cat_id=".$POST['tds_cat_id']." and tcd.tds_payee=".$cust_details['party_pay_cat']."");
		$tds_details = brp_mysqli_fetch_assoc($q);

		$effected_ledger_details = get_ledger_details($dbcon,$tds_details['effected_ledger_id']);

		if(empty($tds_details)){
			$html.='<tr class="it_act"><th colspan="4">Data Not Available</th></tr>';
			echo $html;exit;
		}

		$amt_to_paid = $POST['paid_amount'] - $POST['due_amt'];
		if(empty($cust_details['m_pan']) ){
			$pan_status = 'PAN not available';
			$tds_per = $tds_details['tds_without_pan'];
			$tds_amt = ($amt_to_paid * $tds_details['tds_without_pan'])/100;
		}else{
			$pan_status = 'PAN available';
			$tds_per = $tds_details['tds_with_pan'];
			$tds_amt = ($amt_to_paid * $tds_details['tds_with_pan'])/100;
		}

		$sur_per = $tds_details['tds_surcharge'];
		$sur_amt = ($amt_to_paid * $tds_details['tds_surcharge'])/100;

		$total_tax_amt = $tds_amt+$sur_amt;

		$bank_credit = $amt_to_paid - $tds_amt;
		

		if($amt_to_paid > $tds_details['tds_thresold_limit'] ){
			$thresoled_crossed = 'Yes';
		}else{
			$thresoled_crossed = 'No';
		}
		
		$html.='
		<tr class="it_act">
				<th colspan="4">Thresold limit for '.$cust_details['l_name'].' is Rs. '.$tds_details['tds_thresold_limit'].'</th>
			</tr>

			<tr class="it_act">
				<th colspan="4">Amount to be paid to '.$cust_details['l_name'].' is Rs. '.$amt_to_paid.'</th>
			</tr>';

		$html.='<tr class="it_act">
				<th colspan="4">Thresold limit is being crossed ? '.$thresoled_crossed.'</th>
			</tr>';

		if($amt_to_paid > $tds_details['tds_thresold_limit'] ){
		
			$html.='<th class="it_act" colspan="4" style="background-color:#337AB7;color:#FFFFFF;">TDS Calculation</th>
				<tr class="it_act">
					<td>
						<label class="form-group">Advance Amt.</label><br>
						<span>'.$amt_to_paid.'</span>
					</td>
					<td>
						<label class="form-group">TDS Amt. @ '.$tds_per.'%</label><br>
						<span>'.$tds_amt.'</span>
					</td>
					<td>
						<label class="form-group">Sur Amt. @ '.$sur_per.'%</label><br>
						<span>'.$sur_amt.'</span>
					</td>
					<td>
						<label class="form-group">Total Tax Amt.</label><br>
						<span>'.$total_tax_amt.'</span>
					</td>
					
				</tr>

				<tr class="it_act">
					<th colspan="2">TDS Rate '.$tds_per.'% ('.$pan_status.')<br>Bank account to be credited with Rs. '.$bank_credit.'</th>
					<th colspan="2">TDS Duty to be credited for Rs. '.$tds_amt.'</th>
				</tr>
				<tr class="it_act">	
					<td style="vertical-align:top; text-align: center;" colspan="4"> 
						<input type="button" name="add" id="add" 
						onClick="add_tds_details('.$effected_ledger_details['l_id'].','.$tds_amt.','.$tds_per.');"  class="btn btn-primary" value="Add"/>	
					</td>
				</tr>
			
			';
		}else{
			$html.='<tr class="it_act"><th colspan="4">Thresold is Not crossed </th></tr>';
		}
		
		echo $html;
		
	}	

	else if (strtolower($POST['mode']) == "advance_payment_tds") {

		$q = "SELECT * FROM tbl_tds_tax_category where isdelete=0";
		$select = $dbcon->query($q);

		$str='';
		$str.='<option value="0">--Select TDS Category--</option>';
		while($row=brp_mysqli_fetch_assoc($select))
		{
			$sel='';
			
			// if($row['invoice_id']==$edit_id)
			// {
			// 	$sel='selected=selected';
			// }
			
			$str.='<option value="'.$row['tds_cat_id'].'" '.$sel.' >'.$row['tds_cat_name'].'</option>';
			
		}
		echo $str;
	}


	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_cost_center_transaction` WHERE `costcenter_transaction_id` = '$POST[cost_center_id]'");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "delete_cost_center") {
		$info['isdelete']='1';
		$info['costcenter_status']='2';
		$updateid=update_record('tbl_cost_center_transaction', $info,"costcenter_transaction_id=".$POST['cost_center_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "cost_center_form_show") {
		
		
		$html='';
		
		$master_details=get_table_details_option($dbcon,'tbl_cost_center','cost_center_id','cost_center_name');
		$balance_type=getbalance_type_new($dbcon);
		
		$html.='
		<div class="col-md-12 margin_row">
		
			<div class="col-md-3">
				<div class="form-group">
					<label for="edit_zone_name">Cost Center</label>
					<select class="form-control" name="costcenter_id" id="costcenter_id">
						<option value="">--Select Cost Center--</option>
						'.$master_details.'
					</select>
				</div>	
			</div>
			
			<div class="col-md-3">
				<div class="form-group">
					<label for="edit_zone_name">Amount</label>
					<input type="text" class="form-control" name="costcenter_amount" id="costcenter_amount" />
				</div>	
			</div>

			<div class="col-md-3">
				<div class="form-group">
					<label for="edit_zone_name">Type</label>
					<select class="form-control" name="costcenter_entry_type" id="costcenter_entry_type">
						'.$balance_type.'
					</select>
				</div>
			</div>
			
		</div>
		
		<div class="col-md-12 margin_row">
			<div class="col-md-3">
				<a class="btn btn-primary" onclick="add_cost_center()">Add</a>
			</div>
		</div>
		
		';
		
		echo $html;
		
	}
	else if(strtolower($POST['mode']) == "cost_center_form_add") {

		$info['costcenter_id']	= $POST['costcenter_id'];							
		$info['costcenter_amount']	= $POST['costcenter_amount'];							
		$info['costcenter_entry_type']	= $POST['costcenter_entry_type'];							
		$info['cost_center_voucher_type']	= $POST['cost_center_voucher_type'];							
		$info['cost_center_ledger_id']	= $POST['cost_center_ledger_id'];							
		$info['cost_center_table']	= $POST['cost_center_table'];							
		$info['cost_center_table_id']	= $POST['cost_center_table_id'];							
					
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		
		
		if($POST['edit_id']!='')
		{
			$updateid=update_record('tbl_cost_center_transaction', $info,"costcenter_transaction_id=".$POST['edit_id'] , $dbcon);
			
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_cost_center_transaction', $info, $dbcon);
			
			if($inserid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
		
		
	}
	else if(strtolower($POST['mode']) == "fetch_transport_details") {
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_ZONE_UPDATE,
	        ADMINISTRATOR_ZONE_DELETE
	    ]);
			
		$appData = array();
		$i=1;
		$aColumns = array('t.transport_transaction_id', 't.transport_id','t.transport_gr_no','t.transport_gr_date','t.transport_vehicle_no','t.transport_station','t.cdate','t.transportation_status', 't.user_id','tm.transportation_name');
		$sIndexColumn = "t.transport_transaction_id";
		$isWhere = array("t.transportation_status =0 and t.company_id in (0,$_SESSION[company_id])");
		$sTable = "tbl_transport_transaction as t";			
		$isJOIN = array("left join transportation_details as tm on tm.id=t.transport_id");
		$hOrder = "t.transport_transaction_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['transportation_name']; 
			$row_data[] = $row['transport_gr_no']; 
			$row_data[] = date("d/m/Y",strtotime($row['transport_gr_date'])); 
			$row_data[] = $row['transport_vehicle_no']; 
			$row_data[] = $row['transport_station']; 
			
			$edit_btn='';$delete_btn='';
			
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_transportation('.$row['transport_transaction_id'].');"><i class="fa fa-pencil"></i></button>';
				
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_transportation('.$row['transport_transaction_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "transport_form_add") {
		
		//echo '<pre>';print_r($POST);exit;
		//echo $POST['edit_id'];exit;

		//Transport data
		$info['transport_id']		= $POST['transport_id'];							
		$info['transport_gr_no']	= $POST['transport_gr_no'];							
		$info['transport_gr_date']	= date("Y-m-d",strtotime($POST['transport_gr_date']));			
		$info['distance_km']		= $POST['distance_km'];							
		$info['transport_mode']		= $POST['transport_mode'];		
		$info['transport_vehicle_no']	= $POST['transport_vehicle_no'];							
		$info['transport_station']	= $POST['transport_station'];
		$info['transport_pincode']	= $POST['transport_pincode'];
		$info['transport_doc_no']	= $POST['transport_doc_no'];
		$info['transport_doc_date']	= date("Y-m-d",strtotime($POST['transport_doc_date']));			
		$info['transport_voucher']	= $POST['transport_voucher'];							
		$info['transport_transaction_table']	= $_POST['transport_transaction_table'];			
		$info['transport_transaction_table_id']	= $POST['transport_transaction_table_id'];
		$info['transport_eway_bill_status']	= $POST['iseway_bill'];		
		$info['trasport_einvoice_status']	= $POST['iseinvoice_bill'];				
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];

		//Eway Bill data
		if($POST['iseway_bill']==1)
		{
			
			$info1['eway_distance_km']	= $POST['distance_km'];							
			$info1['eway_transport_mode']	= $POST['transport_mode'];							
			$info1['eway_sub_type']	= $POST['eway_sub_type'];							
			$info1['eway_transaction_type']	= $POST['eway_transaction_type'];							
			$info1['eway_bill_number']	= $POST['eway_bill_number'];	
			$info1['eway_bill_date']	= date("Y-m-d",strtotime($POST['transport_gr_date']));
			$info1['eway_bill_voucher_type']	= $POST['eway_bill_voucher_type'];						
			
			$info1['eway_bill_voucher_table']	= $POST['eway_bill_voucher_table'];						
			$info1['cdate']		= date("Y-m-d H:i:s");
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];
		
		}
		
		if($POST['edit_id']!='')
		{
			$updateid=update_record('tbl_transport_transaction', $info,"transport_transaction_id=".$POST['edit_id'] , $dbcon);

			
			if($POST['iseway_bill']==1)
			{
				$update_ewayid=update_record('tbl_ewaybill_transaction', $info1,"eway_bill_transport_transaction_id=".$POST['edit_id'] , $dbcon);
			}
			
			if($updateid){
				echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_transport_transaction', $info, $dbcon);
			
			if($POST['iseway_bill']==1) 
			{
				$info1['eway_bill_transport_transaction_id']	= $inserid;
				$inser_ewayid=add_record('tbl_ewaybill_transaction', $info1, $dbcon);
			}
			
			if($inserid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
		
		
	}
	else if(strtolower($POST['mode']) == "load_eway_bill_data") {			
	
		$voucher_id = $POST['voucher_id'];
		
		$q = $dbcon -> query("SELECT * FROM `tbl_transport_transaction` WHERE `transport_voucher` = '$POST[voucher_type]' and transport_transaction_table_id='$voucher_id'");
		
		$r = brp_mysqli_fetch_assoc($q);
		
		if($r['transport_eway_bill_status']==1)
		{
			$q1 = $dbcon -> query("SELECT * FROM `tbl_ewaybill_transaction` WHERE `eway_bill_voucher_type` = '$POST[voucher_type]' and eway_bill_voucher_id='$voucher_id'");
			$r1 = brp_mysqli_fetch_assoc($q1);
			$s=array_merge($r,$r1);
			echo json_encode($s);
		}
		else
		{
			echo json_encode($r);
		}
		
		
	}
	else if(strtolower($POST['mode']) == "preedit_transport") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_transport_transaction` WHERE `transport_transaction_id` = '$POST[transport_id]'");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "delete_transport") {
		$info['transportation_status']='2';
		$updateid=update_record('tbl_transport_transaction', $info,"transport_transaction_id=".$POST['transport_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	
	//Bill By Bill Show 
	
	else if(strtolower($POST['mode']) == "get_billby_bill_due") {

		if($POST['bill_type'] == 0){
			$qry_i="SELECT g_total as rem_amount FROM tbl_invoice WHERE invoice_id=".$_POST['ref_id']." and invoice_status=0 and payment_status=0";
	 		$result=brp_mysqli_fetch_assoc($dbcon->query($qry_i));	
		}else if($POST['bill_type'] == 1){
			$qry_b="SELECT bill_amount as rem_amount FROM tbl_ledger_billbybill_opening WHERE bill_opening_id=".$_POST['ref_id']." and isdelete=0 and payment_status != 1";
	 		$result=brp_mysqli_fetch_assoc($dbcon->query($qry_b));
		}
		else if($POST['bill_type'] == 2)
		{
			$qry_p="SELECT g_total as rem_amount FROM tbl_pono WHERE po_id=".$_POST['ref_id']." and status=0 and payment_status=0";
	 		$result=brp_mysqli_fetch_assoc($dbcon->query($qry_p));
		}

		$get_billby_trn_details = get_billby_trn_details($dbcon,$_POST['ref_id']);

		$due_amt = $result['rem_amount'] - $get_billby_trn_details['amount'];

		echo $due_amt;

	}

	else if(strtolower($POST['mode']) == "bill_form_add") {

		$info['bill_ref']	= $POST['bill_ref'];
		$info['bill_method']	= $POST['bill_method'];
		$info['bill_ref_manual']	= $POST['bill_ref_manual'];
		if($POST['bill_method']==1)
		{
			$info['bill_ref_type']	= $POST['bill_type'];
		}
		else
		{
			$info['bill_ref_type']	= $POST['bill_type_original'];	
		}
		$info['bill_due_date']	= date("Y-m-d",strtotime($POST['bill_due_date']));	
		$info['bill_amount']	= $POST['bill_amt'];	
		if($POST['bill_type_original']==2)
		{
			$info['bill_entry_type']	= 2;	
		}						
		else
		{
			$info['bill_entry_type']	= 1;	
		}

		$info['bill_voucher_type']	= $POST['bill_adjust_voucher_type'];							
		$info['bill_ledger_id']	= $POST['bill_adjust_ledger_id'];							
		$info['bill_table']	= $POST['bill_adjust_table'];							
		$info['bill_table_id']	= $POST['bill_adjust_table_id'];	
		$info['bill_ledger_id'] = $POST['cust_ledger_id'];						
		//$info['edit_id']	= $POST['edit_id'];							
					
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		
		
		if($POST['edit_id']!='')
		{
			$updateid=update_record('tbl_bill_by_bill_adjustment_transaction', $info,"bill_transaction_id=".$POST['edit_id'] , $dbcon);
			
			if($updateid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_bill_by_bill_adjustment_transaction', $info, $dbcon);
			
			if($inserid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
		
	}
	
	else if(strtolower($POST['mode']) == "bill_form_add_by_trasaction") {

		$bill_transaction_id = $POST['bill_transaction_id'];
		$bill_amt = $POST['bill_amt'];
		$bill_amount = $POST['bill_amount'];
		
		for($i=0;$i<count($bill_transaction_id);$i++)
		{
			$info['bill_ref']	= 0;
			$info['bill_method']	= $POST['bill_method'];
			if($POST['bill_method']==1)
			{
				$info['bill_ref_type']	= $POST['bill_type'];
			}
			else
			{
				$info['bill_ref_type']	= $POST['bill_type_original'];	
			}
			$info['bill_amount']	= $bill_amt[$i];
			$info['bill_adjustment_id'] = $bill_transaction_id[$i];	
			if($POST['bill_type_original']==2)
			{
				$info['bill_entry_type']	= 2;	
			}						
			else
			{
				$info['bill_entry_type']	= 1;	
			}

			$info['bill_voucher_type']	= $POST['bill_adjust_voucher_type'];							
			$info['bill_table']	= $POST['bill_adjust_table'];							
			$info['bill_table_id']	= $POST['bill_adjust_table_id'];	
			$info['bill_ledger_id'] = $POST['cust_ledger_id'];						
			//$info['edit_id']	= $POST['edit_id'];							
						
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];	

			$inserid=add_record('tbl_bill_by_bill_adjustment_transaction', $info, $dbcon);
			
			if($inserid){

				echo "1";
			}
			else{
				echo "0";
			}
				
		}

	}

	else if(strtolower($POST['mode']) == "fetch_bill_by_bill_details") {
		
		
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_ZONE_UPDATE,
	        ADMINISTRATOR_ZONE_DELETE
	    ]);

	    if($POST['receiptid']!=''){
	    	$where=' AND b.bill_table_id='.$POST['receiptid'].'';
	    }else{
	    	$where=' AND b.bill_table_id=0';
	    }
		
		$appData = array();
		$ledger_id = $POST['ledger_id'];
		$i=1;
		$aColumns = array('b.bill_transaction_id', 'b.bill_method','b.bill_ref','b.bill_amount','b.bill_due_date','b.bill_entry_type','b.bill_voucher_type','b.cdate','b.bill_status', 'b.user_id','b.bill_ref_type','b.bill_method','b.bill_ref_manual','b.bill_table_id','b.bill_ledger_id');
		$sIndexColumn = "b.bill_transaction_id";
		$isWhere = array("b.bill_status=0 and b.bill_voucher_type=".$POST['bill_adjust_voucher_type']."  and bill_ledger_id='$ledger_id' and b.isdelete=0 and b.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "tbl_bill_by_bill_adjustment_transaction as b";			
		$isJOIN = array("");
		$hOrder = "b.bill_transaction_id desc";
		include($path.'include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			if($row['bill_method']==2)
			{
				$reference_no = $row['bill_ref_manual'];
			}
			else
			{
				if($row['bill_ref_type'] == 2){
					$refno = $dbcon -> query("SELECT po_no as refno FROM tbl_pono where po_id=".$row['bill_ref']." ");
					$refno_1=brp_mysqli_fetch_assoc($refno);
					$reference_no = $refno_1['refno'];
				}else if($row['bill_ref_type'] == 0){
					$refno = $dbcon -> query("SELECT invoice_no as refno FROM tbl_invoice where invoice_id=".$row['bill_ref']." ");
					$refno_1=brp_mysqli_fetch_assoc($refno);
					$reference_no = $refno_1['refno'];
				}
				else
				{
					$refno = $dbcon -> query("SELECT bill_ref_no as refno FROM tbl_ledger_billbybill_opening where bill_opening_id=".$row['bill_ref']." ");
					$refno_1=brp_mysqli_fetch_assoc($refno);
					$reference_no = $refno_1['refno'];	
				}

				
			}
			if($row['bill_entry_type']=="2")
			{
				$btype="<strong style='color:red'>Debit</strong>";
			}
			else
			{
				$btype="<strong style='color:green'>Credit</strong>";
			}
			
			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $reference_no; 
			$row_data[] = $row['bill_amount']; 
			$row_data[] = $btype; 
			$row_data[] = date("d/m/Y",strtotime($row['bill_due_date'])); 
			
			$edit_btn='';$delete_btn='';
			
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_bill_by_bill('.$row['bill_transaction_id'].');"><i class="fa fa-pencil"></i></button>';
				
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bill_by_bill('.$row['bill_transaction_id'].','.$row['bill_ledger_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
		
	}
	
	else if(strtolower($POST['mode']) == "delete_bill_by_bill") {
		$info['bill_status']='2';
		$info['isdelete']='1';
		$updateid=update_record('tbl_bill_by_bill_adjustment_transaction', $info,"bill_transaction_id=".$POST['bill_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	
	else if(strtolower($POST['mode']) == "preedit_billedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_bill_by_bill_adjustment_transaction` WHERE `bill_transaction_id` = '$POST[bill_id]'");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	
	else if(strtolower($POST['mode']) == "sales_form_show") {
		
		$salesman_voucher_type = $POST['salesman_voucher_type'];
		$salesman_voucher_id = $POST['salesman_voucher_id'];
		//$bill_amount = $POST['bill_amount'];
		
		$q = $dbcon -> query("SELECT * FROM tbl_salesman_transaction where transaction_voucher_type='$salesman_voucher_type' and transaction_table_id='$salesman_voucher_id'");
		
		$r=brp_mysqli_fetch_assoc($q);
		
		echo json_encode($r);
	
	}
	else if(strtolower($POST['mode'])=="get_salesman_detail")
	{
		$salesman_id=$POST['salesman_id'];
		
		$qry="select * from tbl_ledger_salesman where salesman_ledger_id='$salesman_id'";
		$q=$dbcon->query($qry);
		$row=brp_mysqli_fetch_assoc($q);
		echo json_encode($row);
		//echo $salesman_id;
	}
	else if(strtolower($POST['mode']) == "add_salesman_transaction") {

		$info['transaction_voucher_type']	= $POST['salesman_voucher_type'];							
		$info['transaction_table']	= $POST['salesman_voucher_table'];							
		$info['transaction_table_id']	= $POST['salesman_voucher_id'];							
		$info['salesman_id']	= $POST['salesman_id'];							
		$info['sales_bill_amt']	= $POST['sales_bill_amt'];							
		$info['sales_comm_type']	= $POST['sales_comm_type'];							
		$info['sales_commision_per']	= $POST['sales_comm_percentage'];							
		$info['sales_commision']	= $POST['sales_comm_amount'];							
		$info['sales_tot_qty']	= $POST['sales_tot_qty'];							
		$info['sales_comm_bag']	= $POST['sales_comm_bag'];							
		//$info['edit_id']	= $POST['edit_id'];							
					
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']	= $_SESSION['usertype_id'];
		
		
		if($POST['salesman_popup_id']!='')
		{
			$updateid=update_record('tbl_salesman_transaction', $info,"salesman_trans_id=".$POST['salesman_popup_id'] , $dbcon);
			
			if($updateid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_salesman_transaction', $info, $dbcon);
			
			if($inserid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
		
		
	}

	else if(strtolower($POST['mode']) == "get_adv_receipt_details") {

		if(!empty($_POST['recieptid'])){
			$where = 'and trn_voucher_id= '.$_POST['recieptid'].' ';
		}else{
			$where = 'and trn_voucher_id= 0 ';
		}

		$q = $dbcon -> query("SELECT * FROM `tbl_advacne_receipt_trn` WHERE `trn_voucher_type` = ".$POST['receipt_voucher']." and isdelete=0  and trn_table = '".$_POST['receipt_adv_pay_table']."' ".$where." ");
		$r = brp_mysqli_fetch_assoc($q);

		if(!empty($r)){
			$arr['trn_gst'] = $r['trn_gst'];
			$arr['transaction_id'] = $r['transaction_id'];
		}else{
			$arr['trn_gst'] = '';
		}
		
		$get_state_detail = get_gst_statecode_details($dbcon,$POST['party_ledger_id']);

		$get_company_details = get_company_data($dbcon,$_SESSION['company_id']);

		$arr['cust_name'] = $get_state_detail['l_name'];
		$arr['state_code']=$get_state_detail['gst_state_code'];
		$arr['state_name']=$get_state_detail['state_name'];
		if($get_state_detail['stateid'] == $get_company_details['stateid']){

			$arr['region']="Local";
			$arr['isinterstate']=0;
			$arr['gst'] = '<div class="col-md-6">
							<div class="form-group" id="comm_per_div">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate CGST *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="cgst_rate" id="cgst_rate" value="" readonly />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate SGST *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="sgst_rate" id="sgst_rate" value="" readonly />
								</div>
							</div>
						</div>';

		}else{
			$arr['region']="Inter State";
			$arr['isinterstate']=1;
			$arr['gst'] = '<div class="col-md-6">
							<div class="form-group" id="comm_per_div">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate IGST *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="igst_rate" id="igst_rate" value="" readonly />
								</div>
							</div>
						</div>';
		}
	
		echo json_encode($arr);	

	}else if(strtolower($POST['mode']) == "add_adv_payment") {

		$info['trn_voucher_type']	= $POST['receipt_voucher'];
		$info['trn_table']	= $_POST['receipt_adv_pay_table'];
		$info['trn_type']	= 1;	
		$info['trn_ref']	= $POST['trn_ref'];
		$info['cust_id']	= $POST['cust_id'];
		$info['trn_amount']	= $POST['paid_amount'];																
		$info['trn_gst']	= $POST['trn_gst'];							
		$info['trn_gst']	= $POST['trn_gst'];							
		$info['trn_gst']	= $POST['trn_gst'];							
		$info['trn_gst']	= $POST['trn_gst'];		
		
		$info['trn_cgst']	= $POST['cgst_rate'];		
		$info['trn_sgst']	= $POST['sgst_rate'];		
		$info['trn_igst']	= $POST['igst_rate'];		
		
		$info['cdate']	= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']	= $_SESSION['usertype_id'];

		//echo '<pre>';print_r($info);exit;
		if($POST['adv_payment_edit_id']!='')
		{
			$updateid=update_record('tbl_advacne_receipt_trn', $info,"transaction_id=".$POST['adv_payment_edit_id'] , $dbcon);
			
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_advacne_receipt_trn', $info, $dbcon);
			
			if($inserid){
			echo "1";
			}
			else{
				echo "0";
			}
		}
	}else if(strtolower($POST['mode']) == "get_adv_refund_payment_details") {

		$get_advance_detals=$dbcon->query("SELECT * FROM `tbl_advacne_receipt_trn` WHERE transaction_id=".$POST['transaction_id']."");
		$get_advance_detals_r = brp_mysqli_fetch_assoc($get_advance_detals);

		$get_total_refund = brp_mysqli_fetch_assoc($dbcon->query("SELECT sum(trn_amount) as total FROM `tbl_advacne_receipt_trn` where trn_ref=".$POST['transaction_id']." and trn_voucher_id != 0 and advance_receipt_type=1 and trn_voucher_type=".$POST['payment_voucher'].""));

		$remaning_refund_adv = $get_advance_detals_r['trn_amount'] - $get_total_refund['total'];

		$get_state_detail = get_gst_statecode_details($dbcon,$POST['party_ledger_id']);

		$get_company_details = get_company_data($dbcon,$_SESSION['company_id']);

		$arr['ref_details'] .='

					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Advance Amount *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control" name="trn_amount" value="'.$get_advance_detals_r['trn_amount'].'" id="trn_amount" readonly />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Remaining Refund *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control" name="trn_rem_amount" value="'.$remaning_refund_adv.'" id="trn_rem_amount" readonly />
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Refund Amount *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" onkeyup="calculate_refund_tax(this.value);digitonly(this.value);" name="trn_refund_amount" id="trn_refund_amount" value=""  />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate (%) *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control numbersOnly" value="'.$get_advance_detals_r['trn_gst'].'" readonly name="trn_gst" id="trn_gst" />
						</div>
					</div>
					
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Taxable Amount *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" name="taxable_amt" id="taxable_amt" readonly  />
						</div>
					</div>';



		if($get_state_detail['stateid'] == $get_company_details['stateid']){
			
			$arr['ref_details'] .= '<div class="col-md-6">
							<div class="form-group" id="comm_per_div">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate CGST *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="cgst_rate" id="cgst_rate" value="" readonly />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate SGST *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="sgst_rate" id="sgst_rate" value="" readonly />
								</div>
							</div>
						</div>';

		}else{			
			$arr['ref_details'] .= '<div class="col-md-6">
							<div class="form-group" id="comm_per_div">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate IGST *</label>
								<div class="col-md-12 col-xs-11">
									<input type="text" class="form-control" name="igst_rate" id="igst_rate" value="" readonly />
								</div>
							</div>
						</div>';
		}
	
		echo json_encode($arr);	

	}else if(strtolower($POST['mode']) == "get_adv_payment_ref") {

		if(!empty($POST['receiptid'])){
			$where = 'and trn_voucher_id='.$POST['receiptid'].'';
		}else{
			$where = 'and trn_voucher_id=0';
		}

		$get_advance_payment_detals=brp_mysqli_fetch_assoc($dbcon->query("SELECT * FROM `tbl_advacne_receipt_trn` where isdelete=0 and advance_receipt_type=1 ".$where." "));


		//$get_advance_detals=brp_mysqli_fetch_assoc($dbcon->query("SELECT * FROM `tbl_advacne_receipt_trn` WHERE transaction_id=".$POST['adv_refund_payment_id'].""));
		$arr['transaction_id'] = $get_advance_payment_detals['transaction_id'];
		$arr['trn_amount'] = $get_advance_payment_detals['trn_amount'];
		$arr['trn_ref'] = $get_advance_payment_detals['trn_ref'];

		$get_state_detail = get_gst_statecode_details($dbcon,$POST['party_ledger_id']);

		$get_company_details = get_company_data($dbcon,$_SESSION['company_id']);

		$arr['cust_name'] = $get_state_detail['l_name'];
		$arr['state_code']=$get_state_detail['gst_state_code'];
		$arr['state_name']=$get_state_detail['state_name'];

		if($get_state_detail['stateid'] == $get_company_details['stateid']){
			$arr['region']="Local";
			$arr['isinterstate']=0;
		}else{
			$arr['region']="Inter State";
			$arr['isinterstate']=1;
		}

		$get_advance_ref = $dbcon -> query("SELECT art.trn_ref,art.transaction_id FROM tbl_advacne_receipt_trn as art left join `tbl_receipt` as tr  
			on tr.receipt_id=art.trn_voucher_id WHERE art.`cust_id`=".$POST['party_ledger_id']." and art.advance_receipt_type=0");
		$arr['str_ref'] .= '<option value="0">--Select Ref. No.--</option>';
		while($rel=mysqli_fetch_assoc($get_advance_ref))
		{
			$sel=''; 
			if(!empty($get_advance_payment_detals)){
				if($rel['transaction_id']==$get_advance_payment_detals['trn_ref'])
				{$sel ="selected='selected'";}
			}				

			$arr['str_ref'] .= '<option '.$sel.' value="'.$rel['transaction_id'].'">'.$rel['trn_ref'].'</option>';
		}

		echo json_encode($arr);

	}else if(strtolower($POST['mode']) == "add_refund_adv_payment") {

		$info['trn_voucher_type']	= $POST['receipt_voucher'];
		$info['trn_table']	= $_POST['receipt_adv_pay_table'];
		$info['trn_type']	= 2;	
		$info['trn_ref']	= $POST['trn_ref'];
		$info['cust_id']	= $POST['cust_id'];
		$info['trn_amount']	= $POST['trn_refund_amount'];																
		$info['trn_gst']	= $POST['trn_gst'];	
		$info['advance_receipt_type'] = 1;
		$info['cdate']	= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']	= $_SESSION['usertype_id'];

		//echo '<pre>';print_r($info);exit;
		if($POST['adv_refund_payment_id']!='')
		{
			$updateid=update_record('tbl_advacne_receipt_trn', $info,"transaction_id=".$POST['adv_refund_payment_id'] , $dbcon);
			
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_advacne_receipt_trn', $info, $dbcon);
			
			if($inserid){
			echo "1".'-'.$inserid;
			}
			else{
				echo "0";
			}
		}
	}else if(strtolower($POST['mode']) == "get_register_expence_details") {
//echo '<pre>';print_r($POST);exit;
		if(!empty($POST['receiptid'])){
			$where = 'and receipt_id='.$POST['receiptid'].' and voucher_table="'.$POST['payment_voucher_table'].'" ';
		}else{
			$where = 'and receipt_id=0 and voucher_table="'.$_POST['payment_voucher_table'].'"';
		}

		$get_register_expence_detals=brp_mysqli_fetch_assoc($dbcon->query("SELECT * FROM `tbl_registered_expense` where isdelete=0 ".$where.""));


		$sel_party = '';
		$sel_specified = '';
		if(!empty($get_register_expence_detals)){
			//if($get_register_expence_detals['gst_report_basis'] == 1){
				$sel_party = 'selected';
			//}

			if($get_register_expence_detals['regd_type_of_dealer'] == 0){
				$sel_regi = 'selected';
			}else if($get_register_expence_detals['regd_type_of_dealer'] == 1){
				$sel_unregi = 'selected';
			}else if($get_register_expence_detals['regd_type_of_dealer'] == 2){
				$sel_comp = 'selected';
			}else if($get_register_expence_detals['regd_type_of_dealer'] == 3){
				$sel_gov_bod = 'selected';
			}else if($get_register_expence_detals['regd_type_of_dealer'] == 4){
				$sel_uin_hol = 'selected';
			}

		}
		if(!empty($get_register_expence_detals)){
			$arr['regd_expense_id'] = $get_register_expence_detals['regd_expense_id'];
			$arr['gst_report_basis_id'] = $get_register_expence_detals['gst_report_basis'];
		}else{
			$arr['regd_expense_id'] = '';
			$arr['gst_report_basis_id'] = '';
		}

		$get_ledger_details = get_ledger_details($dbcon,$POST['party_ledger_id']);
		
		if(!empty($get_register_expence_detals['regd_expense_id'])){
			$disable = 'disabled';
			$taxable_amt = $get_register_expence_detals['regd_taxable_amount'];
		}else{
			$disable = '';
			$taxable_amt = $POST['paid_amount'] - ($POST['paid_amount'] * $get_ledger_details['tax_value'])/(100+$get_ledger_details['tax_value']);
		}
		$gst_amt = ($POST['paid_amount'] * $get_ledger_details['tax_value'])/(100+$get_ledger_details['tax_value']);
		$get_state_detail = get_gst_statecode_details($dbcon,$get_register_expence_detals['regd_party_id']);
		$get_company_details = get_company_data($dbcon,$_SESSION['company_id']);
		if($get_state_detail['stateid'] == $get_company_details['stateid']){
			if(!empty($get_register_expence_detals['regd_expense_id'])){
				$cs_gst = $get_register_expence_detals['regd_cgst'];
				$i_gst = 0;
				$gst_amt = $get_register_expence_detals['regd_gst'];
			}else{
				$cs_gst = round($gst_amt/2,2);
				$i_gst = 0;
			}
			
		}else{
			if(!empty($get_register_expence_detals['regd_expense_id'])){
				$cs_gst = 0;
				$i_gst = $get_register_expence_detals['regd_igst'];
				$gst_amt = $get_register_expence_detals['regd_gst'];
			}else{
				$i_gst = round($gst_amt/2,2);
				$cs_gst = 0;
			}
		}

			
//echo '<pre>';print_r($gst_amt);exit;

		$arr['party_name'] = $get_ledger_details['l_name'];

		//$ledger_where = 'and l_group IN ("'.SUNDRY_CREDITORS.'","'.SUNDRY_DEBTORS.'","'.BANK_ACCOUNTS.'","'.BANK_OD_ACCCOUNTS.'","'.CASH_IN_HAND.'","'.DIRECT_EXPENSES.'")';

		$arr['party_det'] ='<div class="row" style="padding: 0px 10px 0px 10px;margin: 0px;">													
							<div class="col-md-6">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">GST BASIS</label>
									<div class="col-md-12 col-xs-11">
										<select class="regd_select2" name="gst_report_basis" id="gst_report_basis" onchange="show_hide_div(this.value);">
											<option value="1" '.$sel_party.' >As Per Party Master</option>
											
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6 party_ledger">
								<div class="form-group">
									<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Party Name</label>
									<div class="col-md-12 col-xs-11">
										<select class="regd_select2 select2" '.$disable.' name="regd_party_id" id="regd_party_id" onchange="party_wise_tax(this.value,'.$POST['party_ledger_id'].')">
											"'.get_ledger($dbcon,$get_register_expence_detals['regd_party_id'],$ledger_where).'"
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="manual_party_details" style="outline: 1px solid orange;padding: 10px;margin: 15px; display:none">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Party Name</label>
										<div class="col-md-12 col-xs-11">
											<input id="regd_party_name" name="regd_party_name" type="text"  class="form-control" value="'.$get_register_expence_detals['regd_party_name'].'" placeholder="Party Name" >
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">State</label>
										<div class="col-md-12 col-xs-11">
											<select class="regd_select2" name="regd_state" id="regd_state" onchange="party_state_wise(this.value)">
												'.get_return_state($dbcon,$get_register_expence_detals['regd_state']).'											
											</select>
										</div>
									</div>
								</div>								
								<div class="col-md-3">
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Type of dealer</label>
										<div class="col-md-12 col-xs-11">
											<select class="regd_select2" name="regd_type_of_dealer" id="regd_type_of_dealer" >
												<option value="0">--Select  Type of dealer--</option>
												<option value="0" '.$sel_regi.' >Registered</option>
												<option value="1" '.$sel_unregi.' >Unregistered</option>
												<option value="2" '.$sel_comp.' >Composition</option>
												<option value="3" '.$sel_gov_bod.' >Govt.body</option>
												<option value="4" '.$sel_uin_hol.' >UIN Holder</option>
											</select>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="col-md-12 control-label" style="text-align:left;line-height:25px">GSTIN</label>
										<div class="col-md-12 col-xs-11">
											<input id="regd_gstin" name="regd_gstin" type="text" class="form-control"  minlength="15" maxlength="15" value="'.$get_register_expence_detals['regd_gstin'].'" placeholder="GSTIN" title="Please enter Valid 15 digit GST No." >
										</div>
									</div>
								</div>
							</div>
						</div>';

		$arr['expence_other_details'] .='

				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Account Name</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_account" name="regd_account" type="text" class="form-control" value="'.$get_ledger_details['l_name'].'" placeholder="Account Name" readonly >
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Purchase Inv. No</label>
							<div class="col-md-12 col-xs-11">											
								<input id="regd_purchase_inv_no" name="regd_purchase_inv_no" type="text" class="form-control" value="'.$get_register_expence_detals['regd_purchase_inv_no'].'" placeholder="Purchase Inv. No"  >
							</div>
						</div>
					</div>								
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Purchase bill date</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_purchase_bill_date" name="regd_purchase_bill_date" type="text" class="form-control date-picker" value="'.date('d-m-Y').'" placeholder="Purchase bill date"  >
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">HSN / SAC</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_hsn" name="regd_hsn" type="text" class="form-control numbersOnly" title="Date" onkeydown="return digitonly(event);"
 value="'.$get_ledger_details['ledger_hsn'].'" maxlength="8" placeholder="HSN / SAC"  >
							</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">UNIT</label>
							<div class="col-md-12 col-xs-11">
								<select class="regd_select2" name="regd_unit" id="regd_unit" onchange="">
									'.getunit($dbcon,$get_register_expence_detals['regd_unit']).'											
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Taxable amount</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_taxable_amount" name="regd_taxable_amount" type="text" class="form-control" readonly value="'.number_format($taxable_amt,2,'.','').'" placeholder="Taxable amount" >
							</div>
						</div>
					</div>								
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">GST</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_gst" name="regd_gst" type="text" class="form-control" value="'.round($gst_amt,2).'" readonly placeholder="GST" >											
							</div>
						</div>
					</div>
					<div class="col-md-3 cgst">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">CGST</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_cgst" name="regd_cgst" type="text" class="form-control" readonly value="'.round($cs_gst,2).'" placeholder="CGST" >
							</div>
						</div>
					</div>
				
					<div class="col-md-3 cgst">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">SGST</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_sgst" name="regd_sgst" type="text" class="form-control" readonly value="'.round($cs_gst,2).'" placeholder="SGST" >
							</div>
						</div>
					</div>
					<div class="col-md-3 igst">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">IGST</label>
							<div class="col-md-12 col-xs-11">
								<input id="regd_igst" name="regd_igst" type="text" class="form-control" readonly value="'.round($i_gst,2).'" placeholder="IGST" >
							</div>
						</div>
					</div>								
					<div class="col-md-3">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">ITC Eligibility</label>
							<div class="col-md-12 col-xs-11">
								<select class="regd_select2" name="regd_itc" id="regd_itc" onchange="">
									"'.get_common_category($dbcon, 15,'ITC Eligibility',$get_register_expence_detals['regd_itc']).'"
								</select>
							</div>
						</div>
					</div>
				</div>';


		echo json_encode($arr);	

	}
	else if(strtolower($POST['mode']) == "party_wise_tax") {
		
		$get_expense_party 	= get_ledger_details($dbcon,$POST['vender_id']);
		$get_ledger_details = get_ledger_details($dbcon,$POST['party_id']);
		$company_state 		= get_company_data($dbcon,$_SESSION['company_id']);
		$sale_gst 			= get_tax_cat_val($dbcon,$get_expense_party['ledger_tax_category']);
		
		$cgst_tax_rate=0;
		$sgst_tax_rate=0;
		$igst_tax_rate=0;
		
		if(($company_state['stateid'] == $get_ledger_details['stateid']) && ($get_ledger_details['enable_sez'] == 0)){
			$gst = $sale_gst['tax_gst']/2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
			$tax_type = 1;
		}else{
			$igst_tax_per = $sale_gst['tax_gst'];
			$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
			$tax_type = 2;
		}
		$resp['cgst_tax_rate'] = $cgst_tax_rate;
		$resp['sgst_tax_rate'] = $sgst_tax_rate;
		$resp['igst_tax_rate'] = $igst_tax_rate;
		$resp['total_gst'] = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
		$resp['tax_type'] = $tax_type;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "party_state_wise") {
		$company_state 		= get_company_data($dbcon,$_SESSION['company_id']);
		$get_expense_party 	= get_ledger_details($dbcon,$POST['vender_id']);
		$sale_gst 			= get_tax_cat_val($dbcon,$get_expense_party['ledger_tax_category']);
		$cgst_tax_rate=0;
		$sgst_tax_rate=0;
		$igst_tax_rate=0;
		
		if($company_state['stateid'] == $POST['state_id']){
			$gst = $sale_gst['tax_gst']/2;
			$cgst_tax_per = $gst;
			$cgst_tax_rate = ($gst*$POST['product_amount'])/100;
			$sgst_tax_per = $gst;
			$sgst_tax_rate = ($gst*$POST['product_amount'])/100;
			$tax_type = 1;
		}else{
			$igst_tax_per = $sale_gst['tax_gst'];
			$igst_tax_rate = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
			$tax_type = 2;
		}
		$resp['cgst_tax_rate'] = $cgst_tax_rate;
		$resp['sgst_tax_rate'] = $sgst_tax_rate;
		$resp['igst_tax_rate'] = $igst_tax_rate;
		$resp['total_gst'] = ($sale_gst['tax_gst']*$POST['product_amount'])/100;
		$resp['tax_type'] = $tax_type;
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "led_tds_permis") {
		$query = "select enable_tds from tbl_ledger where l_id=".$POST['id'];
		$row   = brp_mysqli_fetch_assoc($dbcon -> query($query));
		echo json_encode($row);
	}
	else if(strtolower($POST['mode']) == "add_registered_expense") {

		//echo '<pre>';print_r($POST);exit;
		$info['voucher_type']	= $POST['voucher_type'];
		$info['voucher_table']	= $_POST['voucher_table'];	

		$info['voucher_id']	= $POST['receiptid'];
		$info['receipt_id']	= $_POST['receiptid'];

		$info['gst_report_basis']	= $POST['gst_report_basis'];
		$info['regd_party_id']	= $_POST['regd_party_id'];
		$info['regd_party_name']	= $_POST['regd_party_name'];
		$info['regd_state']	= $POST['regd_state'];
		$info['regd_type_of_dealer']	= $POST['regd_type_of_dealer'];
		$info['regd_gstin']	= $POST['regd_gstin'];																
		$info['regd_account']	= $POST['regd_account'];	
		$info['regd_purchase_inv_no'] = $POST['regd_purchase_inv_no'];
		$info['regd_purchase_bill_date']	= $POST['regd_purchase_bill_date'];
		$info['regd_hsn']	= $POST['regd_hsn'];
		$info['regd_unit']	= $POST['regd_unit'];																
		$info['regd_taxable_amount']	= $POST['regd_taxable_amount'];	
		$info['regd_gst'] = $POST['regd_gst'];
		$info['regd_cgst']	= $POST['regd_cgst'];
		$info['regd_sgst']	= $POST['regd_sgst'];
		$info['regd_igst']	= $POST['regd_igst'];																
		$info['regd_itc']	= $POST['regd_itc'];	

		$info['cdate']	= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']	= $_SESSION['usertype_id'];

		if($POST['voucher_type'] == 84){
			//Payment Entry for Expence
			$info1['entry_type']	= 1;
			$info1['ledger_id']		= $POST['regd_party_id'];
			$info1['amount']		= ($POST['regd_taxable_amount']-$POST['regd_gst']);
			$info1['user_id']		= $_SESSION['user_id'];
			$info1['cdate']			= date("Y-m-d H:i:s");
			$info1['company_id']	= $_SESSION['company_id'];
			
			$table='tbl_journal_trn';
			$tableid='journal_trn_id';

			if(!empty($POST['journal_id']))
			{
				$info1['journal_id']= $POST['journal_id'];
				$infoc1['journal_id']= $POST['journal_id'];
				$infos1['journal_id']= $POST['journal_id'];
				$infoi1['journal_id']= $POST['journal_id'];
			}
			else
			{
				$info1['journal_trn_status']	= 3;
				$infoc1['journal_trn_status']	= 3;
				$infos1['journal_trn_status']	= 3;
				$infoi1['journal_trn_status']	= 3;
			}
			if(!empty($POST['regd_cgst']) && !empty($POST['regd_sgst'])){
				$infoc1['entry_type']	= 1;
				$infoc1['ledger_id']	= 9870;
				$infoc1['amount']		= $POST['regd_cgst'];
				$infoc1['user_id']		= $_SESSION['user_id'];
				$infoc1['cdate']			= date("Y-m-d H:i:s");
				$infoc1['company_id']	= $_SESSION['company_id'];

				if($POST['registered_expense_id']=='')
				{
					$insercid=add_record($table, $infoc1, $dbcon);
				}else{
					if(empty($POST['journal_id'])){
						$updatec_id=update_record($table, $infoc1,"journal_trn_status=3 and ledger_id=9870" , $dbcon);
					}else{
						$updatec_id=update_record($table, $infoc1,"journal_id=".$POST['journal_id']." and ledger_id=9870" , $dbcon);
					}
					
				}	

				$infos1['entry_type']	= 1;
				$infos1['ledger_id']	= 9880;
				$infos1['amount']		= $POST['regd_sgst'];
				$infos1['user_id']		= $_SESSION['user_id'];
				$infos1['cdate']			= date("Y-m-d H:i:s");
				$infos1['company_id']	= $_SESSION['company_id'];

				if($POST['registered_expense_id']=='')
				{
					$insersid=add_record($table, $infos1, $dbcon);
				}else{
					if(empty($POST['journal_id'])){
						$updates_id=update_record($table, $infos1,"journal_trn_status=3 and ledger_id=9880" , $dbcon);
					}else{
						$updates_id=update_record($table, $infos1,"journal_id=".$POST['journal_id']." and ledger_id=9880" , $dbcon);
					}
					
				}

			}else if(!empty($POST['regd_igst'])){
				$infoi1['entry_type']	= 1;
				$infoi1['ledger_id']	= 9890;
				$infoi1['amount']		= $POST['regd_igst'];
				$infoi1['user_id']		= $_SESSION['user_id'];
				$infoi1['cdate']		= date("Y-m-d H:i:s");
				$infoi1['company_id']	= $_SESSION['company_id'];

				if($POST['registered_expense_id']=='')
				{
					$inseriid=add_record($table, $infoi1, $dbcon);
				}else{
					if(empty($POST['journal_id'])){
						$updatei_id=update_record($table, $infoi1,"journal_trn_status=3 and ledger_id=9890" , $dbcon);
					}else{
						$updatei_id=update_record($table, $infoi1,"journal_id=".$POST['journal_id']." and ledger_id=9890" , $dbcon);
					}
					
				}
			}

			if($POST['registered_expense_id']=='')
			{
				$inser_id=add_record($table, $info1, $dbcon);
			}else{
				if(empty($POST['journal_id'])){
					$update_id=update_record($table, $info1,"journal_trn_status=3 and ledger_id=".$POST['regd_party_id'] , $dbcon);
				}else{
					$update_id=update_record($table, $info1,"journal_id=".$POST['journal_id']." and ledger_id=".$POST['regd_party_id'] , $dbcon);
				}
				
			}
		}
		else if($POST['voucher_type'] == 82){

			$info_exe['entry_type']  = 1;
			$info_exe['ledger_id'] = $POST['regd_party_id'];
			$info_exe['amount'] = ($POST['regd_taxable_amount']-$POST['regd_gst']);
			$info_exe['payment_type'] = 1;
			$info_exe['cdate']	= date("Y-m-d H:i:s");
			$info_exe['user_id'] = $_SESSION['user_id'];
			$info_exe['company_id'] = $_SESSION['company_id'];
			$info_exe['receipt_id'] = $POST['receiptid'];

			$table='tbl_receipt_payment_trn';
			$tableid='receipt_payment_trn_id';

			if(!empty($POST['regd_cgst']) && !empty($POST['regd_sgst'])){
				$infoc1['entry_type']	= 1;
				$infoc1['ledger_id']	= 9870;
				$infoc1['amount']		= $POST['regd_cgst'];
				$infoc1['payment_type'] = 1;
				$infoc1['user_id']		= $_SESSION['user_id'];
				$infoc1['cdate']			= date("Y-m-d H:i:s");
				$infoc1['company_id']	= $_SESSION['company_id'];
				$infoc1['receipt_id'] = $POST['receiptid'];

				if($POST['registered_expense_id']=='')
				{
					$insercid=add_record($table, $infoc1, $dbcon);
				}else{
					if(empty($POST['receipt_id'])){
						$updatec_id=update_record($table, $infoc1,"isdelete=0 and ledger_id=9870" , $dbcon);
					}else{
						$updatec_id=update_record($table, $infoc1,"receipt_id=".$POST['receipt_id']." and ledger_id=9870" , $dbcon);
					}
					
				}	

				$infos1['entry_type']	= 1;
				$infos1['ledger_id']	= 9880;
				$infos1['amount']		= $POST['regd_sgst'];
				$infos1['payment_type'] = 1;
				$infos1['user_id']		= $_SESSION['user_id'];
				$infos1['cdate']			= date("Y-m-d H:i:s");
				$infos1['company_id']	= $_SESSION['company_id'];
				$infos1['receipt_id'] = $POST['receiptid'];

				if($POST['registered_expense_id']=='')
				{
					$insersid=add_record($table, $infos1, $dbcon);
				}else{
					if(empty($POST['receipt_id'])){
						$updates_id=update_record($table, $infos1,"journal_trn_status=3 and ledger_id=9880" , $dbcon);
					}else{
						$updates_id=update_record($table, $infos1,"receipt_id=".$POST['receipt_id']." and ledger_id=9880" , $dbcon);
					}
					
				}

			}else if(!empty($POST['regd_igst'])){

				$infoi1['entry_type']	= 1;
				$infoi1['ledger_id']	= 9890;
				$infoi1['amount']		= $POST['regd_igst'];
				$infoi1['payment_type'] = 1;
				$infoi1['user_id']		= $_SESSION['user_id'];
				$infoi1['cdate']		= date("Y-m-d H:i:s");
				$infoi1['company_id']	= $_SESSION['company_id'];
				$infoi1['receipt_id'] = $POST['receiptid'];

				if($POST['registered_expense_id']=='')
				{
					$inseriid=add_record($table, $infoi1, $dbcon);
				}else{
					if(empty($POST['receipt_id'])){
						$updatei_id=update_record($table, $infoi1,"isdelete=3 and ledger_id=9890" , $dbcon);
					}else{
						$updatei_id=update_record($table, $infoi1,"receipt_id=".$POST['receipt_id']." and ledger_id=9890" , $dbcon);
					}
					
				}
			}

			if($POST['registered_expense_id']=='')
			{
				$inser_id=add_record($table, $info_exe, $dbcon);
			}else{
				if(empty($POST['receipt_id'])){
					$update_id=update_record($table, $info_exe,"isdelete=0 and ledger_id=".$POST['regd_party_id'] , $dbcon);
				}else{
					$update_id=update_record($table, $info_exe,"receipt_id=".$POST['receipt_id']." and ledger_id=".$POST['regd_party_id'] , $dbcon);
				}
				
			}
		}


		if($POST['registered_expense_id']!='')
		{
			$updateid=update_record('tbl_registered_expense', $info,"regd_expense_id=".$POST['registered_expense_id'] , $dbcon);
			
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_registered_expense', $info, $dbcon);
			
			if($inserid){
			echo "1".'-'.$inserid;
			}
			else{
				echo "0";
			}
		}
	}else if(strtolower($POST['mode']) == "get_payment_gov_details") {
		if(!empty($POST['receiptid'])){
			$where = 'and pg.receipt_id='.$POST['receiptid'].'';
		}else{
			$where = 'and pg.receipt_id=0';
		}
		$get_gov_payment_trn_detals=brp_mysqli_fetch_all($dbcon->query("SELECT pg.*,pgt.* FROM `tbl_payment_to_govt` as pg left join tbl_payment_to_govt_trn as pgt on pgt.payment_trn_id=pg.payment_id where pg.isdelete=0 ".$where." "));

		//echo '<pre>';print_r($get_gov_payment_trn_detals);exit;
		$reg_sel='';
		$rcm_sel='';
		if($get_gov_payment_trn_detals[0]['gst_payment_type'] == 1)
		{
			$reg_sel = "selected";
		}else if($get_gov_payment_trn_detals[0]['gst_payment_type'] == 2){
			$rcm_sel = "selected";
		}

		//Period Ending Date
		if(!empty($get_gov_payment_trn_detals[0]['period_ending'])){
			$period_ending = date("d-m-Y",strtotime($get_gov_payment_trn_detals[0]['period_ending']));
		}else{
			$period_ending = date("d-m-Y");
		}

		//Chalan Date
		if(!empty($get_gov_payment_trn_detals[0]['chalan_date'])){
			$chalan_date = date("d-m-Y",strtotime($get_gov_payment_trn_detals[0]['chalan_date']));
		}else{
			$chalan_date = date("d-m-Y");
		}

		//Cheque Date
		if(!empty($get_gov_payment_trn_detals[0]['cheque_date'])){
			$cheque_date = date("d-m-Y",strtotime($get_gov_payment_trn_detals[0]['cheque_date']));
		}else{
			$cheque_date = date("d-m-Y");
		}

		if(!empty($get_gov_payment_trn_detals[0]['payment_id'])){
			$arr['payment_id'] = $get_gov_payment_trn_detals[0]['payment_id'];	
		}else{
			$arr['payment_id'] = '';
		}
		

		$arr['payment_gov_details'] .='

				<div class="row" style="padding: 0px 10px 0px 10px;margin: 0px;">													
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">GST Payment Type</label>
							<div class="col-md-12 col-xs-11">
								<select class="gov_select2" name="gst_payment_type" id="gst_payment_type" onchange="">
									<option value="0">--Select GST BASIS--</option>
									<option value="1" '.$reg_sel.' >Regular Payment</option>
									<option value="2" '.$rcm_sel.' >RCM Payment</option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-6 party_ledger">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Period Ending</label>
							<div class="col-md-12 col-xs-11">
								<input id="period_ending" name="period_ending" type="text" class="form-control gov_date_picker" title="Date" value="'.$period_ending.'" >
							</div>
						</div>
					</div>
				</div>

				<div class="" style="outline: 1px solid orange;padding: 10px;margin: 15px;">
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">&nbsp;&nbsp;</label>
								<div class="col-md-12 col-xs-11">
									<label class="col-md-12 control-label" style="text-align:center;line-height:25px">Tax Amount</label>
								</div>
							</div>
						</div>
						
						<div class="col-md-3">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:center;line-height:25px">CGST</label>
								<div class="col-md-12 col-xs-11">
									<input id="govt_cgst" name="govt_cgst" type="text" class="form-control CGST" title="" value="'.$get_gov_payment_trn_detals[0]['govt_cgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>								
						<div class="col-md-3">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:center;line-height:25px">SGST</label>
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control SGST" title="" value="'.$get_gov_payment_trn_detals[0]['govt_sgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:center;line-height:25px">IGST</label>
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control IGST" title="" value="'.$get_gov_payment_trn_detals[0]['govt_igst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-11">
									<label class="col-md-12 control-label" style="text-align:center;line-height:25px">Intrest</label>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control CGST" title="" value="'.$get_gov_payment_trn_detals[1]['govt_cgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>								
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control SGST" title="" value="'.$get_gov_payment_trn_detals[1]['govt_sgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control IGST" title="" value="'.$get_gov_payment_trn_detals[1]['govt_igst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-11">
									<label class="col-md-12 control-label" style="text-align:center;line-height:25px">Penalty</label>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control CGST" title="" value="'.$get_gov_payment_trn_detals[2]['govt_cgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>								
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control SGST" title="" value="'.$get_gov_payment_trn_detals[2]['govt_sgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control IGST" title="" value="'.$get_gov_payment_trn_detals[2]['govt_igst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-3">
							<div class="form-group">
								<div class="col-md-12 col-xs-11">
									<label class="col-md-12 control-label" style="text-align:center;line-height:25px">Late Fee</label>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control CGST" title="" value="'.$get_gov_payment_trn_detals[3]['govt_cgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>								
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control SGST" title="" value="'.$get_gov_payment_trn_detals[3]['govt_sgst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">											
								<div class="col-md-12 col-xs-11">
									<input id="" name="" type="text" class="form-control IGST" title="" value="'.$get_gov_payment_trn_detals[3]['govt_igst'].'" onkeydown="return digitonly(event);" >
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="" style="outline: 1px solid orange;padding: 10px;margin: 15px;">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Challan No.</label>
								<div class="col-md-12 col-xs-11">
									<input id="chalan_number" name="chalan_number" type="text" class="form-control" title="" value="'.$get_gov_payment_trn_detals[0]['chalan_number'].'" placeholder="Challan No." >
								</div>
							</div>
						</div>								
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Challan Date</label>
								<div class="col-md-12 col-xs-11">
									<input id="chalan_date" name="chalan_date" type="text" class="form-control gov_date_picker" title="Date" value="'.$chalan_date.'" placeholder="Challan Date" >
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Cheque No</label>
								<div class="col-md-12 col-xs-11">
									<input id="cheque_no" name="cheque_no" type="text" class="form-control" title="" value="'.$get_gov_payment_trn_detals[0]['cheque_no'].'" placeholder="Cheque No" >
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Cheque Date</label>
								<div class="col-md-12 col-xs-11">
									<input id="cheque_date" name="cheque_date" type="text" class="form-control gov_date_picker" title="Date" value="'.$cheque_date.'" placeholder="Cheque Date" >
								</div>
							</div>
						</div>								
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Bank Name</label>
								<div class="col-md-12 col-xs-11">
									<input id="bank_name" name="bank_name" type="text" class="form-control" title="" value="'.$get_gov_payment_trn_detals[0]['bank_name'].'" placeholder="Bank Name" >
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Bank Code</label>
								<div class="col-md-12 col-xs-11">
									<input id="bank_code" name="bank_code" type="text" class="form-control" title="" value="'.$get_gov_payment_trn_detals[0]['bank_code'].'" placeholder="Bank Code" >
								</div>
							</div>
						</div>
					</div>
				</div>';


		echo json_encode($arr);	

	}else if(strtolower($POST['mode']) == "add_payment_to_gov") {

		if(!empty($POST['receiptid'])){
			$info['voucher_id']	= $POST['receiptid'];
			$info['receipt_id']	= $POST['receiptid'];
		}

		$info['voucher_type']	= $POST['voucher_type'];
		$info['voucher_table']	= $_POST['voucher_table'];	
		$info['gst_payment_type'] = $POST['gst_payment_type'];
		$info['period_ending']	= date("Y-m-d",strtotime($POST['period_ending']));
		$info['chalan_number']	= $POST['chalan_number'];
		$info['chalan_date']	=  date("Y-m-d",strtotime($POST['chalan_date']));
		$info['cheque_no']	= $POST['cheque_no'];																
		$info['cheque_date']	=  date("Y-m-d",strtotime($POST['cheque_date']));
		$info['bank_name'] = $POST['bank_name'];
		$info['bank_code']	= $POST['bank_code'];																
		$info['cdate']	= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']	= $_SESSION['usertype_id'];

		if($POST['gov_payment_id'] != '')
		{
			$updateid=update_record('tbl_payment_to_govt', $info,"payment_id=".$POST['gov_payment_id'] , $dbcon);
		}else
		{
			$inserid = add_record('tbl_payment_to_govt', $info, $dbcon);
		}


		foreach ($POST['IGST'] as $key => $value) {
			if(!empty($POST['receiptid'])){
				$info1['receipt_id']	= $POST['receiptid'];
			}
			if($POST['gov_payment_id'] == ''){
				$info1['payment_trn_id'] = $inserid;
			}
			$info1['govt_cgst'] = $POST['CGST'][$key];
			$info1['govt_sgst']	= $POST['SGST'][$key];
			$info1['govt_igst']	= $value;
			$info1['govt_payment_type'] = $key;
			$info1['cdate']	= date("Y-m-d H:i:s");
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];
			$info1['usertype_id']	= $_SESSION['usertype_id'];

			if($POST['gov_payment_id'] == ''){
				$inserid_trn=add_record('tbl_payment_to_govt_trn', $info1, $dbcon);
			}else{
				$update_trn=update_record('tbl_payment_to_govt_trn', $info1,"payment_trn_id=".$POST['gov_payment_id']." and govt_payment_type=".$key , $dbcon);
			}

			
		}

		if($updateid){
			echo "2";
		}else if($inserid){
			echo "1".'-'.$inserid;
		}
		else{
			echo "0";
		}
		


	/*Debit-Credit Note Start- Dhruv */	

	}else if(strtolower($POST['mode']) == "get_debit_credit_note_details") { 

		if($_POST['bill_type'] == 'invoice'){
			$bill_type=1;
		}else if($_POST['bill_type'] == 'purchase'){
			$bill_type=2;
		}
		if(!empty($POST['journal_id'])){
			$where = "and voucher_id=".$POST['journal_id']." and bill_type=".$bill_type." ";
		}else{
			$where = "and voucher_id=0 and bill_type=".$bill_type."";
		}

		$get_cre_deb_detals = $dbcon->query("SELECT * FROM `tbl_cr_dr_adjustment` WHERE voucher_table='".$_POST['payment_voucher_table']."' and isdelete=0 and entry_type=".$POST['cr_dr_entry_type']." ".$where." ");
		$get_cre_deb_detals_r = brp_mysqli_fetch_assoc($get_cre_deb_detals);
		//var_dump($get_cre_deb_detals_r);
		$remaning_refund_adv = $get_advance_detals_r['trn_amount'] - $get_total_refund['total'];

		$get_state_detail = get_gst_statecode_details($dbcon,$POST['party_ledger_id']);

		$get_company_details = get_company_data($dbcon,$_SESSION['company_id']);

		$arr['party_name'] = $get_state_detail['l_name'];
		$arr['state_code']=$get_state_detail['gst_state_code'];
		$arr['state_name']=$get_state_detail['state_name'];
		$arr['adjustment_id']=$get_cre_deb_detals_r['adjustment_id'];

		if($_POST['bill_type'] == 'invoice'){
			$bill_no_detl = getInvoiceByCust($dbcon,$POST['party_ledger_id'],$get_cre_deb_detals_r['adjust_invoice']);
		}else if($_POST['bill_type'] == 'purchase'){
			$bill_no_detl = getPurchaseInvoiceByCust($dbcon,$POST['party_ledger_id'],$get_cre_deb_detals_r['adjust_invoice']);
		}

		//Invoice Date
		// if(!empty($get_gov_payment_trn_detals[0]['chalan_date'])){
		// 	$chalan_date = date("d-m-Y",strtotime($get_gov_payment_trn_detals[0]['chalan_date']));
		// }else{
		// 	$chalan_date = date("d-m-Y");
		// }

		//Adjust Date
		if(!empty($get_cre_deb_detals_r['adjust_date'])){
			$adjust_date = date("d-m-Y",strtotime($get_cre_deb_detals_r['adjust_date']));
		}else{
			$adjust_date = date("d-m-Y");
		}

		$arr['deb_cre_details'] .='
					<div class="col-md-12">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Return Reason *</label>
							<div class="col-md-12 col-xs-11">
								<select class="cre_deb_select2" name="adjust_reason" id="adjust_reason" >
									"'.get_common_category($dbcon, 36,'Return Reason',$get_cre_deb_detals_r['adjust_reason']).'"
								</select>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">'.$POST['bill_type'].' No *</label>
							<div class="col-md-12 col-xs-11">
								<select class="cre_deb_select2" name="adjust_invoice" id="adjust_invoice" onchange="getInvDate();" >
								"'.$bill_no_detl.'"
								</select>
							</div>
						</div>
					</div>
					</div>
					<div class="col-md-12">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Invoice Date *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control" name="adjust_invoice_date" value="'.date("d-m-Y",strtotime($get_cre_deb_detals_r['adjust_invoice_date'])).'" id="adjust_invoice_date" readonly />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Adjust Date *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control cre_deb_date_picker" name="adjust_date" value="'.$adjust_date.'" id="adjust_date" />
							</div>
						</div>
					</div>
					</div>
					<div class="col-md-12">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">HSN *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control" value="'.$get_cre_deb_detals_r['adjust_hsn'].'" name="adjust_hsn" id="adjust_hsn" onkeydown="return digitonly(event);" maxlength="8" />
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px">UNIT</label>
							<div class="col-md-12 col-xs-11">
								<select class="cre_deb_select2" name="adjust_unit" id="adjust_unit" onchange="">
									'.getunit($dbcon,$get_cre_deb_detals_r['adjust_unit']).'										
								</select>
							</div>
						</div>
					</div>
					</div>
					<div class="col-md-12">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Diff. Amt *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control numbersOnly" value="'.number_format($get_cre_deb_detals_r['adjsut_diff']
									,2,".","").'" name="adjsut_diff" id="adjsut_diff" onkeyup="calculate_cre_deb_tax();" onkeydown="return digitonly(event);" />
							</div>
						</div>
					</div>
					
					<div class="col-md-6">					
						<div class="form-group">
							<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> GST % *</label>
							<div class="col-md-12 col-xs-11">
								<input type="text" class="form-control numbersOnly" value="'.$get_cre_deb_detals_r['adjust_gst'].'" name="adjust_gst" id="adjust_gst" onkeyup="calculate_cre_deb_tax();" onkeydown="return digitonly(event);" />
							</div>
						</div>
					</div>
					</div>
					';



		if($get_state_detail['stateid'] == $get_company_details['stateid']){
			$arr['region']="Local";
			$arr['isinterstate']=0;
			$arr['deb_cre_details'] .= '<div class="col-md-12"><div class="col-md-6">
					<div class="form-group" id="comm_per_div">
						<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate CGST *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" name="adjust_cgst" id="adjust_cgst" value="'.number_format($get_cre_deb_detals_r['adjust_cgst'],2,".","").'" readonly />
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate SGST *</label>
						<div class="col-md-12 col-xs-11">
							<input type="text" class="form-control" name="adjust_sgst" id="adjust_sgst" value="'.number_format($get_cre_deb_detals_r['adjust_sgst'],2,".","").'" readonly />
						</div>
					</div>
				</div></div>';

		}else{	
			$arr['region']="Inter State";
			$arr['isinterstate']=1;		
			$arr['deb_cre_details'] .= '<div class="col-md-12"><div class="col-md-6">
				<div class="form-group" id="comm_per_div">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px"> Tax Rate IGST *</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" name="adjust_igst" id="adjust_igst" value="'.number_format($get_cre_deb_detals_r['adjust_igst'],2,".","").'" readonly />
					</div>
				</div>
			</div></div>';
		}

		$arr['deb_cre_details'] .= '<div class="col-md-12"> <div class="col-md-6">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Nature Of Transaction *</label>
					<div class="col-md-12 col-xs-11">
						<select class="cre_deb_select2" name="adjust_nature_transaction" id="adjust_nature_transaction" >
							"'.get_common_category($dbcon, 37,'Nature Of Transaction',$get_cre_deb_detals_r['adjust_nature_transaction']).'"
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">ITC Eligibility</label>
					<div class="col-md-12 col-xs-11">
						<select class="cre_deb_select2" name="adjust_itc" id="adjust_itc">
							"'.get_common_category($dbcon, 15,'ITC Eligibility',$get_cre_deb_detals_r['adjust_itc']).'"
						</select>
					</div>
				</div>
			</div> </div>';
	
		echo json_encode($arr);	

	}else if(strtolower($POST['mode']) == "add_cre_deb_note") {

		//echo '<pre>';print_r($POST);exit;
		$info['voucher_type']	= $POST['voucher_type'];
		$info['voucher_table']	= $_POST['voucher_table'];
		$info['bill_type'] = $POST['bill_type'];		
		$info['adjust_reason']	= $POST['adjust_reason'];
		$info['adjust_invoice']	= $_POST['adjust_invoice'];
		$info['adjust_invoice_date'] = date("Y-m-d",strtotime($_POST['adjust_invoice_date']));
		$info['adjust_date']	= date("Y-m-d",strtotime($_POST['adjust_date']));
		$info['adjust_hsn']	= $POST['adjust_hsn'];
		$info['adjust_unit']	= $POST['adjust_unit'];
		$info['adjsut_diff']	= $POST['adjsut_diff'];																
		$info['adjust_gst']	= $POST['adjust_gst'];	
		$info['adjust_cgst'] = $POST['adjust_cgst'];
		$info['adjust_sgst'] = $POST['adjust_sgst'];
		$info['adjust_igst'] = $POST['adjust_igst'];
		$info['adjust_itc']	= $POST['adjust_itc'];
		$info['adjust_nature_transaction']	= $POST['adjust_nature_transaction'];

		$info['cdate']	= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']	= $_SESSION['usertype_id'];
		$info['entry_type']	= $POST['entry_type_id'];	

		if($POST['deb_cre_adjustment_id']!='')
		{
			$updateid=update_record('tbl_cr_dr_adjustment', $info,"adjustment_id=".$POST['deb_cre_adjustment_id'] , $dbcon);
			
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}
		else
		{
			$inserid=add_record('tbl_cr_dr_adjustment', $info, $dbcon);
			
			if($inserid){
			echo "1".'-'.$inserid;
			}
			else{
				echo "0";
			}
		}
	}
	/*Debit-Credit Note End- Dhruv */
	//TCS Deduction popup start
	else if(strtolower($POST['mode']) == "get_tcs_reference_popup") {

		$ledger_details = get_ledger_details($dbcon,$POST['ledgerid']);
		$get_reference_detail =brp_mysqli_fetch_all($dbcon->query("SELECT *  FROM `tbl_general_book` WHERE `ledger_id` = ".$POST['ledgerid']." and entry_type=1 and genral_book_status=0 "));

		$html .='<div class="col-md-12">	
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">TCS Category</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" required="" minlength="2" placeholder="Default Value" name="tds_cat" disabled="disabled" id="tds_cat" value="'.$ledger_details['l_name'].'"  />
						<input type="hidden" name="payment_tds_ledger_id" id="payment_tds_ledger_id" value="'.$POST['ledgerid'].'"  />
						<input type="hidden" name="payment_type" id="payment_type" value="2"  />
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Paid Amount</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" required="" minlength="2" disabled="disabled" placeholder="Default Value" name="payamount" id="payamount" value="'.$POST['paidamount'].'"  />
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Adjust Amount</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" required="" minlength="2" placeholder="amount" name="adj_amt" id="adj_amt" value=""  />
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<table class="table table-bordered table-striped" id="billbybill-table">
			<thead>
				<tr>
					<th style="text-align:center;">#</th>
					<th>Ref. NO.</th>
					<th>Credited On</th>
					<th>TCS Amount</th>
				</tr>
			</thead>
			
			<tbody>
				<tr>';
		foreach($get_reference_detail as $ref){
			
			$get_reference_detail =brp_mysqli_fetch_all($dbcon->query("SELECT * FROM `tbl_tds_tax_deduction_reference_detail` 
				where ref_payment_id=".$ref['general_book_id']." and isdelete=0 "));
			if(empty($get_reference_detail)){
				$ref_no=explode('=',get_tds_details($dbcon,$ref['table_name'],$ref['table_id']));
				$html .= '<tr><td><input type="checkbox" class="form-control gen_checkbox" name="gen_id[]" id="gen_id" data-generalid="'.$ref['general_book_id'].'" onchange="cal_ref_amt();" value="'.$ref['amount'].'" /></td>
				<td>'.$ref_no[2].'</td>
				<td>'.$ref['ref_date'].'</td>
				<td>'.$ref['amount'].'</td></tr>';
			}			
		}

		$html .='</tr>
			</tbody>
		</table>
		</div>		
		<div class="col-md-12">
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Challan No</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" minlength="2"  name="pay_chalanno" id="pay_chalanno" value=""  />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Cheque No</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" minlength="2"  name="pay_cheque_no" id="pay_cheque_no" value=""  />
					</div>
				</div>
			</div></div>';
		
		echo $html;	

	}
	//TCS End
	else if(strtolower($POST['mode']) == "get_tds_reference_popup") {

		$ledger_details = get_ledger_details($dbcon,$POST['ledgerid']);
		$get_reference_detail =brp_mysqli_fetch_all($dbcon->query("SELECT *  FROM `tbl_general_book` WHERE `ledger_id` = ".$POST['ledgerid']." and entry_type=1 and genral_book_status=0 "));

		$html .='<div class="col-md-12">	
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">TDS Category</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" required="" minlength="2" placeholder="Default Value" name="tds_cat" disabled="disabled" id="tds_cat" value="'.$ledger_details['l_name'].'"  />
						<input type="hidden" name="payment_tds_ledger_id" id="payment_tds_ledger_id" value="'.$POST['ledgerid'].'"  />
						<input type="hidden" name="payment_type" id="payment_type" value="1"  />
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Paid Amount</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" required="" minlength="2" disabled="disabled" placeholder="Default Value" name="payamount" id="payamount" value="'.$POST['paidamount'].'"  />
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Adjust Amount</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" required="" minlength="2" placeholder="amount" name="adj_amt" id="adj_amt" value=""  />
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<table class="table table-bordered table-striped" id="billbybill-table">
			<thead>
				<tr>
					<th style="text-align:center;">#</th>
					<th>Ref. NO.</th>
					<th>Credited On</th>
					<th>TDS Amount</th>
				</tr>
			</thead>
			
			<tbody>
				<tr>';
		foreach($get_reference_detail as $ref){
			
			$get_reference_detail =brp_mysqli_fetch_all($dbcon->query("SELECT * FROM `tbl_tds_tax_deduction_reference_detail` 
				where ref_payment_id=".$ref['general_book_id']." and isdelete=0 "));
			if(empty($get_reference_detail)){
				$ref_no=explode('=',get_tds_details($dbcon,$ref['table_name'],$ref['table_id']));
				$html .= '<tr><td><input type="checkbox" class="form-control gen_checkbox" name="gen_id[]" id="gen_id" data-generalid="'.$ref['general_book_id'].'" onchange="cal_ref_amt();" value="'.$ref['amount'].'" /></td>
				<td>'.$ref_no[2].'</td>
				<td>'.$ref['ref_date'].'</td>
				<td>'.$ref['amount'].'</td></tr>';
			}			
		}

		$html .='</tr>
			</tbody>
		</table>
		</div>		
		<div class="col-md-12">
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Challan No</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" minlength="2"  name="pay_chalanno" id="pay_chalanno" value=""  />
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-md-12 control-label" style="text-align:left;line-height:25px">Cheque No</label>
					<div class="col-md-12 col-xs-11">
						<input type="text" class="form-control" minlength="2"  name="pay_cheque_no" id="pay_cheque_no" value=""  />
					</div>
				</div>
			</div></div>';
		
		echo $html;	

	}else if(strtolower($POST['mode']) == "add_tds_ref_detail") {
		//echo '<pre>';print_r($POST);exit;
		$info['payment_amount']	= $POST['adj_amt'];							
		$info['payment_tds_ledger_id']	= $POST['payment_tds_ledger_id'];	
		$info['pay_chalanno']	= $POST['pay_chalanno'];
		$info['pay_cheque_no']	= $POST['pay_cheque_no'];			
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['payment_type']	= $POST['payment_type'];

		$inserid=add_record('tbl_tds_tax_deduction_reference', $info, $dbcon);

		foreach($POST['refid'] as $ref){
			$gen_detail =brp_mysqli_fetch_assoc($dbcon->query("SELECT * 
				FROM `tbl_general_book` where general_book_id=".$ref." and genral_book_status=0 "));
			$info1['deduction_ref_id']	= $inserid;							
			$info1['ref_payment_id']	= $ref;		
			$info1['ref_pay_amount'] = $gen_detail['amount'];	
			$info1['cdate']		= date("Y-m-d H:i:s");
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];

			$inserrefid=add_record('tbl_tds_tax_deduction_reference_detail', $info1, $dbcon);

		}

		if($inserrefid){
			echo "1";
		}
		else{
			echo "0";
		}
	}
	else if(strtolower($POST['mode']) == "get_bill_adjsutment") {

		$cust_id = $POST['cust_id'];
		$page_type = $POST['page_type'];
		$where="";

		$cnt=1;

		// if($page_type==0)
		// {

		// }
		// else if($page_type==2)
		// {
		// 	//$where.=" UNION select d.debitnote_no as bill_no,d.g_total as bill_amount,d.paid_amount as paid_amount,d.debitnote_id as transaction_id from tbl_debitnote as d where d.debit_note_status='0' and d.vender_id='$cust_id'";
		// }
		
		$q = "select b.bill_ref_manual as bill_no,b.bill_amount as bill_amount,b.bill_transaction_id as transaction_id from tbl_bill_by_bill_adjustment_transaction as b where b.bill_method='2' and b.isdelete='0' and b.bill_ref_type='$page_type' and b.bill_adjustment_status=0 and b.bill_ledger_id='$cust_id'";

		//echo $q;exit;

		$sel = $dbcon->query($q);
		// $sel = $dbcon->query("select b.* from tbl_bill_by_bill_adjustment_transaction as b
		//  where b.bill_method='2' and b.isdelete='0' and b.bill_ref_type='$page_type' and b.bill_adjustment_status=0 and b.bill_ledger_id='$cust_id'");

		if(brp_mysqli_num_rows($sel)>0)
		{
			while($row=brp_mysqli_fetch_assoc($sel))
			{

				$sel1= $dbcon->query("select sum(bill_amount) as total_paid from tbl_bill_by_bill_adjustment_transaction where bill_adjustment_id='$row[bill_transaction_id]'");
				$row1 = brp_mysqli_fetch_assoc($sel1);

				$remaining = $row['bill_amount']-$row1['total_paid'];

				$str.="<tr>
					<th><input type='checkbox' name='' id='advance_check".$cnt."' value='".$cnt."' onchange='unread_payment(this.value)' /></th>
					<th>".$cnt."</th>
					<th>".$row['bill_ref_manual']."</th>
					<th>".$row['bill_amount']."</th>
					<th>".$row1['total_paid']."</th>
					<th>".$remaining."</th>
					<td>
						<input type='hidden' class='form-control' name='bill_transaction_id[]' value='".$row['bill_transaction_id']."' />
						<input type='hidden' class='form-control' name='bill_amount[]' id='bill_amount".$cnt."' value='".$row['bill_amount']."' />
						<input type='text' class='form-control' name='advance_amount[]' id='advance_amount".$cnt."' onkeyup='check_advance_amount(this.value,".$cnt.")' readonly />
					</td>
				</tr>";

				$cnt++;
			}

			$str.="<tr>
				<td colspan='8' align='center'>
					<input type='button' name='submit' value='SUBMIT' class='btn btn-primary' onclick='save_advance_payment(".$cust_id.",".$page_type.")' />
				</td>
			</tr>";

		}
		else
		{
			$str.="<tr>
				<th colspan='5' style='text-align:center !important'>
					Sorry . No Advance Payment to adjust
				</th>
			</tr>";
		}
		echo $str;

	}

	else if(strtolower($POST['mode']) == "get_bill_past_adjustment") {

			$eid = $POST['eid'];
			$cust_id = $POST['cust_id'];
			$page_type = $POST['page_type'];
			
				$str.="<table class='table table-bordered'>

				<tr>
					<th colspan='6' style='text-align:center !important'>PAST PAYMENT ADJUSTMENT</th>
				</tr>

				<tr>
					<th>#</th>
					<th>Reference No</th>
					<th>Bill Amount</th>
					<th>Adjustment Amount</th>
					<th>Remaining Amount</th>
					
				</tr>";

				$cnt1=1;
				$sel11 = $dbcon->query("select * from tbl_bill_by_bill_adjustment_transaction  where bill_table_id='$eid'");
				while($row11=brp_mysqli_fetch_assoc($sel11))
				{
					$str.="

						<tr>
							<th>".$cnt1."</th>
							<th>".get_id_detail($dbcon,'tbl_bill_by_bill_adjustment_transaction','bill_transaction_id',$row11['bill_adjustment_id'],'bill_ref_manual')."</th>
							<th>".get_id_detail($dbcon,'tbl_bill_by_bill_adjustment_transaction','bill_transaction_id',$row11['bill_adjustment_id'],'bill_amount')."</th>
							<th>
								<input type='text' readonly class='form-control' name='' id='' value='".$row11['bill_amount']."' />
							</th>
							<th>".$row11['bill_amount']."</th>

						</tr>
					";

					$cnt1++;
				}

			$str.="</table>";

			echo $str;
	}
?>
