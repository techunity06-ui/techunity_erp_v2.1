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
error_reporting(E_ALL);
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		FINANCE_MAKE_PAYMENT_EDIT,
		FINANCE_MAKE_PAYMENT_DELETE,
		FINANCE_MAKE_PAYMENT_PRINT
	]);

        $POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);	
	if(strtolower($POST['mode']) == "fetch") {
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		 //branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('receipt', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('receipt');

		$where.=" $where_company";

		$where_user=check_user('receipt');

		//$where.=" $where_user";

		// branch , comapny , user check end - dhaval  
                
		$where1 = " and receipt.payment_type=1";
		
		$where .= " and receipt_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND receipt_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('receipt.receipt_id','receipt_no', 'vender.l_name','receipt.total_paid_amount','receipt_date','receipt.payment_type','receipt.cdate','receipt.user_id','pay.chequegenerateid','pay.generat_status','cheque_dtl','receipt.payment_mode_id','c.common_mst_name','payment.l_name as payment_mode','payment.cust_mobile','payment.company_name');
		$sIndexColumn = "receipt.receipt_id";
		$isWhere = array("receipt.status = 0  ".$where.$where1);
		$sTable = "tbl_receipt as receipt";			
		$isJOIN = array('left join tbl_ledger vender on vender.l_id=receipt.cust_id', 'left join tbl_ledger payment on payment.l_id=receipt.payment_mode_id','left join tbl_payment_cheque_generate as pay on pay.purchase_payid=receipt.receipt_id and generat_status=0','left join tbl_common_mst as c on c.common_mst_id=receipt.payment_mode_id');
		$hOrder = "receipt.receipt_id desc";
		//$hGroupby = "rtrn.receipt_id";
		include($path.'include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$reciept_deta = $dbcon->query("SELECT l.l_name,l.cust_mobile,l.company_name FROM `tbl_receipt_payment_trn` as r left join tbl_ledger as l on l.l_id=r.ledger_id where r.payment_type=1 and r.receipt_id=".$row['receipt_id']."  and r.isdelete=0 limit 1 ");
            $reciept_deta_1 = brp_mysqli_fetch_assoc($reciept_deta);

			if ($row['payment_type']==1){
				$col='style="color:red"';
			}
			else{
				$col='style="color:green"';
			}
			$row_data[] = "<span ".$col.">".$row['sr']."</span>";
			$row_data[] = "<span ".$col.">".$row['receipt_no']."</span>";
			//$row_data[] = "<span ".$col.">".$row['inv']."</span>";
			$row_data[] = "<span ".$col.">".$reciept_deta_1['l_name']."</span>";
			$chedel='';
			if($row['cheque_dtl']!=""){
				$chedel="<span ".$col.">(".$row['cheque_dtl'].")</span>";
			}
			$row_data[] =  "<span ".$col.">".$row['common_mst_name'].' '.$chedel.'</span>';
			
			$row_data[] = "<span ".$col.">".$row['total_paid_amount']."</span>";
			if ($row['payment_type']==1){
				$row_data[] = "<span ".$col.">CR</span>";
			}else{
				$row_data[] ="<span ".$col.">DR</span>";
			}
			$row_data[] = "<span ".$col.">".date('d M, Y',strtotime($row['receipt_date']))."</span>";
			$cheq_btn='';
			if($row['generat_status']=="0" && $row['payment_type']=="2") {
				$cheq_btn=' <a class="btn btn-xs btn-info" data-original-title="Print Cheque" data-toggle="tooltip" data-placement="top" href="'.DOMAIN_CHEQUE.'generate-cheque/'.$row['chequegenerateid'].'" target="_blank"><i class="fa fa-money"></i></a>';
			}
			$edit_btn='';$print_btn='';$del_btn='';$whatsapp='';
			if(in_array(FINANCE_MAKE_PAYMENT_EDIT,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$row['receipt_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(FINANCE_MAKE_PAYMENT_DELETE,$bulkAccessArray)){
                $del_btn = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment('.$row['receipt_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
                        
            if(in_array(FINANCE_MAKE_PAYMENT_PRINT,$bulkAccessArray)){
                $print_btn = '<a class="btn btn-xs btn-info" data-original-title="Receipt Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'receipt_purchase/'.$row['receipt_id'].'"><i class="fa fa-print"></i></a>';
			}

			
            $key = "encryptionkey";
			$text=$row['receipt_id'];
			$encrypted = bin2hex(openssl_encrypt($text,'AES-128-CBC', $key));
            $whatsapp.='<a title="Send to Whatsapp" type="button" class="btn btn-xs btn-success" href="https://web.whatsapp.com/send?phone=+91'.$reciept_deta_1['cust_mobile'].'&text='.$reciept_deta_1['company_name'].'%0aThank you for your payment.%0aReceipt No:-'.$row['receipt_no'].'%0aDate:- '.date('d-m-Y',strtotime($row['receipt_date'])).'%0aAmount:- '.$row['total_paid_amount'].'%0aBest Regards%0a '.DOMAIN.FINANCE_ROOT.'linkreceipt_purchase/'.$encrypted.'" target="_blank"> <i class="fa fa-whatsapp"></i></a>&nbsp;';
			
			$row_data[] = $edit_btn.' '.$print_btn.' '.$del_btn.' '.$cheq_btn.' '.$whatsapp; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
//echo '<pre>';print_r($POST);exit;
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=4 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

		//Currency converter
		if(isset($POST['currency_enable'])){
	    	$curncy_trn['currency_id'] = $POST['currency_id'];
	    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
	    }else{
	    	$basecurrency = getbasecurrency($dbcon);
	    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
	    	$curncy_trn['currency_rate'] = 1;
	    }

        $info2['payment_type'] 	 = 1;
		$info2['receipt_no']  	= $POST['receipt_no'];
		$info2['receipt_date']	 = date("Y-m-d",strtotime($POST['payment_date']));		
		$info2['payment_mode_id']  	= $POST['payment_mode_id'];		
		$info2['cust_id']		 = $POST['paymentmodeid'];

		$info2['enable_billby_bill_show'] = (isset($_POST['enable_billby_bill_show']) && ($_POST['enable_billby_bill_show']=='yes')) ? 1 : 0; 

		$info2['enable_cost_center']     = (isset($_POST['enable_cost_center']) && ($_POST['enable_cost_center']=='yes')) ? 1 : 0; 
		
		$info2['ref_date']     			= date("Y-m-d",strtotime($POST['ref_date']));
		$info2['total_paid_amount']	   	= $POST['paid_amount'];
		$info2['payment_remark']	   	= ($_POST['payment_desc']);
		$info2['gst_nature']	   	 	= ($_POST['gst_nature']);
		$info2['balance_typeid']		= 1;
		$info2['cdate']		   	 	 	= date("Y-m-d H:i:s");
		$info2['user_id']		  		= $_SESSION['user_id'];
		$info2['company_id']     		= $_SESSION['company_id'];
		$info2['currency_enable']		= $_POST['currency_enable'];
		$info2['is_pdc']				= $POST['is_pdc'];
		$info2['pdc_date']				=  date("Y-m-d",strtotime($POST['pdc_date']));
		$info2['branch_id']				= $POST['branch_id'];
		$info2['financial_year_id']		= $POST['financial_year'];
		$info2['cheque_dtl']			= $POST['cheque_dtl'];

		//print_r($POST);exit;
		$insertreceiptid=add_record('tbl_receipt',array_merge($info2,$curncy_trn), $dbcon,'');

		// Update payment id in TDS/TCS deduction table
		if($POST['ledger_Tax_type'] == '9891'){
			$tds_ded['payment_date']	= date("Y-m-d",strtotime($POST['payment_date']));
			$tds_ded['payment_id'] = $insertreceiptid;
			$update_tds_ded_id=update_record('tbl_tds_tax_deduction_reference',$tds_ded,"isdelete=0 and payment_id=0 and payment_type=1 and user_id=".$_SESSION['user_id'] , $dbcon);
		}else if($POST['ledger_Tax_type'] == '9892'){
			$tcs_ded['payment_date']	= date("Y-m-d",strtotime($POST['payment_date']));
			$tcs_ded['payment_id'] = $insertreceiptid;
			$update_tds_ded_id=update_record('tbl_tds_tax_deduction_reference',$tcs_ded,"isdelete=0 and payment_id=0 and payment_type=2 and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		/*Update Cost center Trn Table Start by Dhruv*/
        if($insertreceiptid && $_POST['enable_cost_center']=='yes'){
			$cost_trn['cost_center_ledger_id']	= $POST['vender_id'];
			$cost_trn['cost_center_table_id'] = $insertreceiptid;
			$updatecosttrnid=update_record('tbl_cost_center_transaction',array_merge($cost_trn,$curncy_trn),"isdelete=0 and cost_center_table='tbl_receipt' and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		/*Update Bill by bill Trn Table Start by Dhruv*/
        if($insertreceiptid){
			$billby_bill['bill_table_id']	= $insertreceiptid;
			//$billby_bill['bill_ledger_id'] =  $POST['vender_id'];
			$updatebillbytrnid=update_record('tbl_bill_by_bill_adjustment_transaction',array_merge($billby_bill,$curncy_trn),"bill_voucher_type=".$POST['bill_adjust_voucher_type']." and isdelete=0 and bill_table='tbl_receipt' and bill_table_id=0 and user_id=".$_SESSION['user_id'] , $dbcon);
		}


		/*Update refund entry in advance payment Trn Table Start by Dhruv*/
		if($insertreceiptid && $_POST['gst_nature'] == 72){
			$refund_payment['trn_voucher_id']	= $insertreceiptid;
			$updaterefundtrnid=update_record('tbl_advacne_receipt_trn',array_merge($refund_payment,$curncy_trn),"trn_type=2 and cust_id=".$POST['vender_id']." and advance_receipt_type=1 and trn_voucher_id=0  and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		/*Update register expence in tbl_registered_expense Table Start by Dhruv*/
		if($insertreceiptid && $_POST['gst_nature'] == 70){
			$reg_exp['receipt_id']	= $insertreceiptid;
			$reg_exp['voucher_id']	= $insertreceiptid;
			$updateregexpid=update_record('tbl_registered_expense',array_merge($reg_exp,$curncy_trn),"receipt_id=0 and isdelete=0 and voucher_id=0 and voucher_table='tbl_receipt'  and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		/*Update payment to gov in tbl_payment_to_govt Table Start by Dhruv*/
		if($insertreceiptid && $_POST['gst_nature'] == 73){
			$pay_gov['receipt_id']	= $insertreceiptid;
			$pay_gov['voucher_id']	= $insertreceiptid;
			$updatepaygovid=update_record('tbl_payment_to_govt',array_merge($pay_gov,$curncy_trn),"receipt_id=0 and isdelete=0 and voucher_id=0 and voucher_table='tbl_receipt'  and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		
		 $info1['ref_date']	= date("Y-m-d",strtotime($POST['payment_date']));
		 $info1['table_name']	= "tbl_receipt";
		 $info1['table_id']		= $insertreceiptid;
		 $info1['entry_type']	= 1;
		 $info1['ledger_id']		= $POST['paymentmodeid'];
		 $info1['amount']		= $POST['paid_amount'];
		 $info1['user_id']		= $_SESSION['user_id'];
		 $info1['cdate']		= date("Y-m-d H:i:s");
		 $info1['company_id']	= $_SESSION['company_id'];
		 $inserid11=add_record("tbl_general_book",array_merge($info1,$curncy_trn), $dbcon,$branch_id);

	 	$receipt_payment_trn_detail = get_receipt_payment_trn_detail($dbcon,1,0);
		foreach ($receipt_payment_trn_detail as $trn_detail) {
			add_general_book_entry($dbcon,"tbl_receipt_payment_trn",$trn_detail['receipt_payment_trn_id'],$trn_detail['entry_type'],$trn_detail['ledger_id'],$trn_detail['amount'],'',date("Y-m-d",strtotime($POST['payment_date'])),'',$curncy_trn);
		}

		/*Update receipt payment Trn Table Start by Dhruv*/
		if($POST['payment_mode_id']==11)//if cheque select then
        {
                //$query_from = $dbcon->query("UPDATE account_mst SET acc_chequeno = acc_chequeno + 1 WHERE acc_id = ".$POST['paymentmodeid']);
               // $query_from = $dbcon->query("UPDATE account_mst SET acc_chequeleft = acc_chequeleft - 1 WHERE acc_id = ".$POST['paymentmodeid']);

                $info_gen['acc_id']			= $POST['paymentmodeid'];
                $info_gen['amount']			= $POST['paid_amount'];
                $info_gen['cheque_date']	= date("Y-m-d",strtotime($POST['ref_date']));
                $info_gen['cheque_num']		= $POST['cheque_dtl'];
                $info_gen['vender_id']		= $POST['vender_id'];
                $info_gen['purchase_payid'] = $insertreceiptid;
                $info_gen['generat_status'] = 0;// for cheque generate
                $info_gen['company_id']		= $_SESSION['company_id'];
                $insert_cheque=add_record('tbl_payment_cheque_generate', $info_gen, $dbcon);
        }
		if($insertreceiptid){
			$receipt_payment['receipt_id']	= $insertreceiptid;
			$updaterectrntrnid=update_record('tbl_receipt_payment_trn',array_merge($receipt_payment,$curncy_trn),"isdelete=0 and payment_type=1 and receipt_id=0  and user_id=".$_SESSION['user_id'] , $dbcon);
		}
		
		if($POST['save_cheque']=="1") {
			$arr['msg']="1";
			$arr['eid']=$insertreceiptid;
			$arr['cheque_genid']=$insert_cheque;
		}
		if($insertreceiptid) {	
			$arr['msg']="1";							
		}
		else{
			$arr['msg']="0";
		}
	
		echo json_encode($arr);
	}
    else if(strtolower($POST['mode']) == "edit") {

        //Currency converter
		if(isset($POST['currency_enable'])){
	    	$curncy_trn['currency_id'] = $POST['currency_id'];
	    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
	    }else{
	    	$basecurrency = getbasecurrency($dbcon);
	    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
	    	$curncy_trn['currency_rate'] = 1;
	    }

        $info2['payment_type'] 	 = 1;
		$info2['receipt_no']  = $POST['receipt_no'];
		$info2['receipt_date']	 = date("Y-m-d",strtotime($POST['payment_date']));				
		//$info2['cust_id']		 = $POST['vender_id'];

		$info2['enable_billby_bill_show'] = (isset($_POST['enable_billby_bill_show']) && ($_POST['enable_billby_bill_show']=='yes')) ? 1 : 0; 

		$info2['enable_cost_center']     = (isset($_POST['enable_cost_center']) && ($_POST['enable_cost_center']=='yes')) ? 1 : 0; 
		
		$info2['ref_date']     		 = date("Y-m-d",strtotime($POST['ref_date']));
		$info2['total_paid_amount']	   	 = $POST['paid_amount'];
		$info2['payment_remark']	   	 = ($_POST['payment_desc']);
		$info2['gst_nature']	   	 = ($_POST['gst_nature']);
		$info2['balance_typeid']	= 2;
		$info2['cdate']		   	 	 	 = date("Y-m-d H:i:s");
		$info2['user_id']		  		 = $_SESSION['user_id'];
		$info2['company_id']     		 = $_SESSION['company_id'];
		$info2['currency_enable']	= $_POST['currency_enable'];
		$info2['payment_mode_id']		= $POST['payment_mode_id'];
		$info2['is_pdc']			= $POST['is_pdc'];
		$info2['pdc_date']			=  date("Y-m-d",strtotime($POST['pdc_date']));
		$info2['branch_id']			= $POST['branch_id'];
		$info2['cheque_dtl']			= $POST['cheque_dtl'];
		$info2['cust_id']		 = $POST['paymentmodeid'];

		$updatetreceiptid = update_record('tbl_receipt',array_merge($info2,$curncy_trn),"receipt_id=".$POST['receiptid'], $dbcon);

		$info_gen['ref_date']	= date("Y-m-d",strtotime($POST['payment_date']));
		$info_gen['amount']	= $POST['paid_amount'];
		$updatetrancationid5=update_record('tbl_general_book',array_merge($info_gen,$curncy_trn),"table_name='tbl_receipt' and table_id=".$POST['receiptid'] , $dbcon);

		
		
		if($updatetreceiptid) {	
			$arr['msg']="1";							
		}
		else{
			$arr['msg']="0";
		}
		
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "delete") {
		
		$info['status']		= 2;
		$updatetrancationid1=update_record('tbl_receipt', $info,"receipt_id=".$POST['eid'] , $dbcon);	
		$updatetrancationid2=update_record('tbl_receipt_trn', $info,"receipt_id=".$POST['eid'] , $dbcon);
		$updatetrancationid2=update_record('tbl_receipt_payment_trn', $info,"receipt_id=".$POST['eid'] , $dbcon);
		$updatetrancationid3=update_record('tbl_excess', $info,"receipt_id=".$POST['eid'] , $dbcon);

		$info1['isdelete']	= 1;
		$updatetrancationid4=update_record('tbl_advacne_receipt_trn', $info1,"advance_receipt_type=1 and trn_voucher_id=".$POST['eid'] , $dbcon);	
		$updatetrancationid5=update_record('tbl_payment_to_govt', $info1,"receipt_id=".$POST['eid'] , $dbcon);
		$updatetrancationid6=update_record('tbl_registered_expense', $info1,"receipt_id=".$POST['eid'] , $dbcon);

		$infogb['genral_book_status']		= 2;
		$updatepb=update_record('tbl_general_book', $infogb, "table_name='tbl_receipt' and table_id=".$POST['eid'], $dbcon);

		$query = "SELECT * FROM tbl_receipt_payment_trn WHERE receipt_id=".$POST['eid']."";
		$rs_type=$dbcon->query($query);
		while($row=mysqli_fetch_assoc($rs_type)){
			$updatetrancationid6=update_record('tbl_general_book', $infogb,"table_name='tbl_receipt_payment_trn' and table_id=".$row['receipt_payment_trn_id'] , $dbcon);
		}
		
		/*$info_st['generat_status']=2;
		$updatetrnid=update_record('tbl_payment_cheque_generate', $info_st,"purchase_payid=".$POST['eid'], $dbcon);
		$query="select cheque_id from tbl_payment_cheque_generate where purchase_payid=".$POST['eid'];
		$rs_cheque=($dbcon->query($query));
		if(mysqli_num_rows($rs_cheque)>0) {
			$rel_cheque=mysqli_fetch_assoc($rs_cheque);
			$info_cheque['cheque_iscancel']=1;
			update_record('coro_cheques', $info_cheque,"cheque_id=".$rel_cheque['cheque_id'], $dbcon);
		}*/
		if($updatetrancationid1)
			echo "1";	
		else
			echo "0";			
	}

	/** Start code Added by Dhruv **/
	else if(strtolower($POST['mode'])== "get_ledger_details")
	{
		
		$ledger_id=$POST['ledger_id'];
		
		$row=get_ledger_details($dbcon,$ledger_id);
		
		echo json_encode($row);
	}

	else if (strtolower($POST['mode'])== "add_paymnt_entry_field") {
		//echo '<pre>';print_r($POST['edit_payment_entry_id']);exit;
		if(isset($POST['currency_enable'])){
	    	$curncy_trn['currency_id'] = $POST['currency_id'];
	    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
	    }else{
	    	$basecurrency = getbasecurrency($dbcon);
	    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
	    	$curncy_trn['currency_rate'] = 1;
	    }

		$info_exe['entry_type']  = $POST['entry_type'];
		$info_exe['ledger_id'] = $POST['ledger_id'];
		$info_exe['amount'] = $POST['entry_amount'];
		$info_exe['payment_type'] = $POST['payment_type'];
		$info_exe['cdate']	= date("Y-m-d H:i:s");
		$info_exe['user_id'] = $_SESSION['user_id'];
		$info_exe['company_id'] = $_SESSION['company_id'];
		$info_exe['receipt_id'] = $POST['receiptid'];
		$info_exe['tds_per'] = $POST['tds_per'];

		if($POST['enable_billby_bill_show']=='yes'){
			$info_billby['bill_ledger_id'] = $POST['ledger_id'];
			$updatebillbyid=update_record('tbl_bill_by_bill_adjustment_transaction', $info_billby,"bill_table_id=0 and bill_ref_type=1 and bill_voucher_type=".$POST['bill_adjust_voucher_type']." " , $dbcon);
		}
		

		if($POST['edit_payment_entry_id']!='')
		{
			$updateid=update_record('tbl_receipt_payment_trn', $info_exe,"receipt_payment_trn_id=".$POST['edit_payment_entry_id'] , $dbcon);

			if(!empty($POST['receiptid'])){
				$info1['ledger_id'] = $POST['ledger_id'];
				$info1['amount'] = $POST['entry_amount'];
				$updategeneid=update_record('tbl_general_book', $info1,"table_id=".$POST['edit_payment_entry_id']." and table_name='tbl_receipt_payment_trn' " , $dbcon);
			}
		
			if($updateid){
			echo "2";
			}
			else{
				echo "0";
			}
		}else
		{
			//echo '<pre>';print_r($info_exe);exit;
			$insert_exc=add_record('tbl_receipt_payment_trn', $info_exe, $dbcon);
			if($POST['istds'] == 'yes'){
				$info_tds['ref_id']=$insert_exc+1;
				update_record('tbl_receipt_payment_trn', $info_tds,"receipt_payment_trn_id=".$insert_exc , $dbcon);
			}
			if(!empty($POST['receiptid'])){
				add_general_book_entry($dbcon,"tbl_receipt_payment_trn",$insert_exc,2,$POST['ledger_id'],$POST['entry_amount'],'',date("Y-m-d",strtotime($POST['payment_date'])),'',$curncy_trn);
			}
			if($insert_exc)
				echo "1";	
			else
				echo "0";
		}
		
		

	}

	else if(strtolower($POST['mode']) == "fetch_payment_entry") {
		
		if(!empty($POST['receiptid'])){
			$where = 'and rpt.receipt_id='.$POST['receiptid'].'';
		}else{
			$where = 'and rpt.receipt_id=0';
		}			
		$appData = array();
		$i=1;
		$aColumns = array('rpt.receipt_payment_trn_id,bt.balance_type_name,tl.l_name,rpt.amount,tl.l_id,rpt.ref_id');
		$sIndexColumn = "rpt.receipt_payment_trn_id";
		$isWhere = array("rpt.payment_type=1 and rpt.isdelete=0 and rpt.company_id in (0,$_SESSION[company_id]) ".$where." ");
		$sTable = "tbl_receipt_payment_trn as rpt";			
		$isJOIN = array(" left join mst_balance_type as bt on rpt.entry_type=bt.balance_typeid "," left join tbl_ledger as tl on rpt.ledger_id=tl.l_id ");
		$hOrder = "rpt.receipt_payment_trn_id";
		$having_clause ='';
		include($path.'include/pagging.php');
		$appData = array();
		$yes='yes';
		$id=1;

		foreach($sqlReturn as $row) {
			$refid=$row['ref_id'];
			if($refid!=0){
				$refid1 = $refid;
			}
			$row1=get_ledger_details($dbcon,$row['l_id']);

			if( ($POST['company_bill_balance'] == 1) && ($row1['enable_billbybill_opening']==1) ){
				$show_bill = '<a href="#" id="billby_bill_link" onClick="get_bill_show(\'yes\',\'purchase\',\'entry_amount\',\'vender_id\',\''.$row['l_id'].'\',\''.$row['l_name'].'\');"> Check Bill By Bill Adjustment </a>';
			}else{
				$show_bill='';
			}

			if($POST['gst_nature'] == 72){

				$gst_nature_link='<a href="#" onclick="get_adv_payment_ref(\'72\',\'field_ledger_id\')" id="checkAdvPayLink" >Check Advance Payment Refund</a>';
			
			}else if($POST['gst_nature'] == 73){
				
				$gst_nature_link = '<a href="#" onclick="get_payment_gov_popup(\'73\')" id="checkGovPayLink" >Check Payment To Gov.</a>';
			
			}else{
				$gst_nature_link = '';
			}
			
			// if($row['balance_type_name']=="Debit")
			// {
			// 	$btype="<strong style='color:red'>".$row['balance_type_name']."</strong>";
			// }
			// else
			// {
			// 	$btype="<strong style='color:green'>".$row['balance_type_name']."</strong>";
			// }
			
			

			$row_data = array();
			//$row_data[] = $id;
			$row_data[] = $row['balance_type_name']; 
			$row_data[] = "<input type='hidden' id='field_ledger_id' value=".$row['l_id']."  /><b>".$row['l_name']."</b>"."<br>".$show_bill."<br>".$gst_nature_link; 
			$row_data[] = "<input type='hidden' id='field_entry_amount' value=".number_format($row['amount'],2,'.','')."  />".number_format($row['amount'],2,'.',''); 
			
			$count_btn='';$edit_btn='';$delete_btn='';
				
				$count_btn='<input type="hidden" class="fieldcount" />';
				if(!empty($refid) || $refid == $row['receipt_payment_trn_id'] || $POST['gst_nature'] == 70){
					$edit_btn='';
				}else{

					$edit_btn='<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_payment_entry('.$row['receipt_payment_trn_id'].');"><i class="fa fa-pencil"></i></button>';
				}
				$delete_btn='<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment_entry('.$row['receipt_payment_trn_id'].','.$row['l_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$count_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}

	else if(strtolower($POST['mode']) == "delete_payment_entry") {
		$info['isdelete']='1';
		$q = $dbcon -> query("SELECT * FROM tbl_receipt_payment_trn WHERE ref_id = ".$POST['eid']." and isdelete=0 ");
		$r = brp_mysqli_fetch_assoc($q);
		if(!empty($r['ref_id'])){
			$updaterefid=update_record('tbl_receipt_payment_trn', $info,"receipt_payment_trn_id=".$r['receipt_payment_trn_id'] , $dbcon);
		}
		$updateid=update_record('tbl_receipt_payment_trn', $info,"receipt_payment_trn_id=".$POST['eid'] , $dbcon);
		if(!empty($POST['receiptid'])){
			$receiptid = $POST['receiptid'];
		}else{
			$receiptid = 0;
		}
		$updatebillbyid=update_record('tbl_bill_by_bill_adjustment_transaction', $info,"bill_table_id=".$receiptid." and bill_ledger_id=".$POST['ledger_id']." and bill_voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
		$updateexpenceid=update_record('tbl_registered_expense', $info,"voucher_id=".$receiptid." and receipt_id=".$receiptid." and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
		$updategovpayid=update_record('tbl_payment_to_govt', $info,"voucher_id=".$receiptid." and receipt_id=".$receiptid." and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
		
		if(!empty($POST['receiptid'])){
			$info1['genral_book_status']='2';
			$updategeneid=update_record('tbl_general_book', $info1,"table_id=".$POST['eid']." and table_name='tbl_receipt_payment_trn' " , $dbcon);
		}
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}else if(strtolower($POST['mode']) == "delete_all_payment_entry") {

		$info['isdelete']='1';
		
		if(empty($POST['receiptid'])){
			$updateid=update_record('tbl_receipt_payment_trn', $info,"receipt_id=0 and payment_type=1", $dbcon);		
			$updatebillbyid=update_record('tbl_bill_by_bill_adjustment_transaction', $info,"bill_table_id=0 and bill_table='tbl_receipt' and bill_voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
			$updateexpenceid=update_record('tbl_registered_expense', $info,"voucher_id=0 and receipt_id=0 and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
			$updateadvid=update_record('tbl_advacne_receipt_trn', $info,"trn_voucher_id=0 and advance_receipt_type=1 and trn_voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
			$updategovpayid=update_record('tbl_payment_to_govt', $info,"voucher_id=0 and receipt_id=0 and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);

		}
		
	}else if(strtolower($POST['mode']) == "delete_all_register_expence_entry") {
		$info['isdelete']='1';

		$updateid=update_record('tbl_receipt_payment_trn', $info,"receipt_id=".$POST['receiptid']."", $dbcon);
		$updateexpenceid=update_record('tbl_registered_expense', $info,"receipt_id=".$POST['receiptid']." and voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
	}

	else if(strtolower($POST['mode']) == "preedit_payment_entry") {			
		$q = $dbcon -> query("SELECT * FROM tbl_receipt_payment_trn WHERE receipt_payment_trn_id = ".$POST['edit_payment_entry_id']." ");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}

	else if(strtolower($POST['mode'])== "get_total_receipt_payment")
	{
		if(!empty($POST['receiptid'])){
			$where="and receipt_id= ".$POST['receiptid']." ";
		}else{
			$where="and receipt_id= 0";
		}
		$q = $dbcon -> query("SELECT sum(amount - (select IFNULL(sum(amount),0) as paid_amt from tbl_receipt_payment_trn where isdelete=0 and payment_type=1  ".$where."  and entry_type=1)) as t_amount FROM tbl_receipt_payment_trn WHERE payment_type=1 and isdelete=0 and company_id in (0,".$_SESSION['company_id'].") ".$where." ");
		$r = brp_mysqli_fetch_assoc($q);
		$r_amount = ($POST['paid_amount'] - $r['t_amount']);
		echo $r_amount;
	}


	/** End code by Dhruv **/

	else if(strtolower($POST['mode']) == "get_opn_bal") {
		$acc_id=$POST['acc_id'];
		if($acc_id==0)
		{	
			$qry="select acc_id,acc_type from account_mst where acc_type=1 and acc_status=0 and company_id=".$_SESSION['company_id'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));	
			$acc_id=$rel['acc_id'];
		}
		else
		{
			$qry="select acc_id,acc_type from account_mst where  acc_status=0 and company_id=".$_SESSION['company_id']." and acc_id=".$acc_id;
			$rel=mysqli_fetch_assoc($dbcon->query($qry));	
			$acc_id=$rel['acc_id'];
			
		}
		echo get_opening_balance($acc_id,$dbcon,$rel['acc_type']);
	}
	else if(strtolower($POST['mode'])== "load_invoiceno")
	{
		$row=array();
		$query1="select * from tbl_invoicetype where type_id=4 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
		$rows=mysqli_fetch_assoc($dbcon->query($query1));
		$id=$rows['taxinvoice_start'];
		$id=$id+1;
		//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
		//$end = $start+1;
		if($rows['invoice_format']=='2')
		{
			$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
		}
		else if($rows['invoice_format']=='1')
		{
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
	else if(strtolower($POST['mode']) == "load_data") {
		/*$query="select sum(amount) as cr_amount from tbl_general_book as cust where entry_type=1 and cust.ledger_id=".$POST['vender_id'];
			$rel=mysqli_fetch_assoc($dbcon->query($query));
			
			$query2="select sum(amount) as dr_amount from tbl_general_book as cust where entry_type=2 and cust.ledger_id=".$POST['vender_id'];
		$rel1=mysqli_fetch_assoc($dbcon->query($query2));*/
		
		$query="select cust.opn_balance,cust.balance_typeid,
		(SELECT sum(g_total) FROM `tbl_invoice` as inv where inv.cust_id=cust.l_id and inv.invoice_status=0) as invoice_amount,
		(SELECT sum(excess_amount) FROM `tbl_excess` as cr_exc where cr_exc.cust_id=cust.l_id and cr_exc.status!=2 and cr_exc.excess_type=1) as cr_excess_amount,
		(SELECT sum(excess_amount) FROM `tbl_excess` as dr_exc where dr_exc.cust_id=cust.l_id and dr_exc.status!=2 and dr_exc.excess_type=2) as dr_excess_amount,
		(SELECT sum(g_total) FROM `tbl_pono` as po where po.vender_id=cust.l_id and po.status=0 and po.approve_status=1) as po_amount,
		(SELECT sum(paid_amount) FROM `tbl_expense_detail` as exp where exp.emp_id=cust.l_id and exp.expense_status=0 and exp.expense_approve_status=1) as exp_amount,
		(SELECT sum(rec_trn.total_amount) FROM `tbl_receipt` as rec
		left join tbl_receipt_trn as rec_trn on rec_trn.receipt_id=rec.receipt_id
		where rec.cust_id=cust.l_id and rec.status!=2 and rec_trn.status=0 and rec_trn.payment_type=1) as paid_amount,
		(SELECT sum(rec_trn.total_amount) FROM `tbl_receipt` as rec
		left join tbl_receipt_trn as rec_trn on rec_trn.receipt_id=rec.receipt_id
		where rec.cust_id=cust.l_id and rec.status!=2 and rec_trn.status=0 and rec_trn.payment_type=2) as purchasepaid_amount
		
		from tbl_ledger as cust where cust.l_id=".$POST['vender_id'];
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$op_balance=0;
		
		if(!empty($rel['opn_balance']))
		{
			$op_balance=($rel['balance_typeid']=="2"?-($rel['opn_balance']):$rel['opn_balance']);
		}
		$amount=($op_balance+$rel['paid_amount']+$rel['po_amount']+$rel['exp_amount']+$rel['cr_excess_amount']+$rel['credit_amount'])-($rel['invoice_amount']+$rel['proinvoice_amount']+$rel['dr_excess_amount']+$rel['purchasepaid_amount']+$rel['debit_amount']);
		
		if($amount<0){
			$type="DR";
			}else{
			$type="CR";
			
		}
		
		$r['dueamo']=abs($amount);
		$r['type']=$type;
		
		echo json_encode($r);
	}		
	else if(strtolower($POST['mode']) == "load_totaldata") {
		$qry="select* from tbl_pono where po_id=".$POST['purchasebill_id'];
		$total=mysqli_fetch_assoc($dbcon->query($qry));	
		echo json_encode($total);
	} 
	else if(strtolower($POST['mode']) == "get_chequeno") {
		
		echo $cno=get_chequeno($POST['acc_id'],$dbcon);
		//var_dump($cno);
		
	}
	else if(strtolower($POST['mode']) == "load_tempoutward") {
                $where = '';
                $where .= " WHERE ref_date >= '".date('Y-m-d', strtotime($_POST['start_date']))."' AND ref_date <= '".date('Y-m-d', strtotime($_POST['end_date']))."'";
		//invoice payment data
		// 1 .cr   2 dr
		$query='Select * from (
		
		(select "Purchase" as type,2 as ref_type,po_date as ref_date,po_no as ref_no,po_id as ref_id,g_total as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and po.po_id=trn.purchase_id) as pay_amount,po.cdate from  tbl_pono as po where status=0 and po.approve_status=1 AND vender_id='.$POST['vender_id'].' and po.g_total>(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and po.po_id=trn.purchase_id)) 
		
		union (select "Expense" as type,2 as ref_type,expense_date as ref_date,remark as ref_no,ex_id as ref_id,paid_amount as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and exp.ex_id=trn.ex_id) as pay_amount,exp.cdate from tbl_expense_detail as exp where expense_status=0 and exp.expense_approve_status=1 AND exp.emp_id='.$POST['vender_id'].' and exp.paid_amount>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and exp.ex_id=trn.ex_id)) 
		
		union (select "Invoice" as type,1 as ref_type,invoice_date as ref_date,invoice_no as ref_no,invoice_id as ref_id,g_total as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and inv.invoice_id=trn.invoice_id) as pay_amount,inv.cdate  from tbl_invoice as inv where invoice_status=0 AND cust_id='.$POST['vender_id'].' and inv.g_total>(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and inv.invoice_id=trn.invoice_id))
		
		union (select "excess" as type,2 as ref_type,rep.receipt_date as ref_date,rep.receipt_no as ref_no,excess_id as ref_id,excess_amount as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and payment_type=2 and inv.excess_id=trn.excess_id) as pay_amount,inv.cdate  from tbl_excess as inv 
		left join tbl_receipt as rep on rep.receipt_id=inv.receipt_id
		where inv.status=0 and excess_type=1 AND inv.cust_id='.$POST['vender_id'].' and inv.excess_amount>(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and payment_type=2 and inv.excess_id=trn.excess_id))
		
		union (select "excess" as type,1 as ref_type,rep.receipt_date as ref_date,rep.receipt_no as ref_no,excess_id as ref_id,excess_amount as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and payment_type=1 and inv.excess_id=trn.excess_id) as pay_amount,inv.cdate  from tbl_excess as inv 
		left join tbl_receipt as rep on rep.receipt_id=inv.receipt_id
		where inv.status=0 and excess_type=2 AND inv.cust_id='.$POST['vender_id'].' and inv.excess_amount>(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and payment_type=1 and inv.excess_id=trn.excess_id))
		
		) as data '.$where.' order by ref_date,ref_type DESC';
		
		//$query="select * from  tbl_pono where status=0 AND g_total>paid_amount AND vender_id=".$POST['vender_id'];
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field" style="background-color:#0088cc;color: white;font-size:15px ;" >
		<th class="text-center" width="10%">Type</th>
		<th class="text-center" width="6%">Ref No</th>
		<th class="text-center" width="10%">Date</th>
		<th class="text-center" width="7%">Amount</th>
		<th class="text-center" width="7%">Due Amount</th>
		<th colspan="2" style="display:none;" class="text-center tdskasar" width="10%">TDS</th>
		<th style="display:none;" class="text-center tdskasar" width="6%">Kasar</th>
		<th class="text-center" width="8%">Pay Amount</th>
		<th class="text-center" width="5%"></th>
		</tr>
		';
		$query1="select cust.opn_balance,cust.balance_typeid,
		(SELECT sum(inv.paid_amount) FROM `tbl_receipt_trn` as inv 
		left join tbl_receipt as res on res.receipt_id=inv.receipt_id
		where res.cust_id=cust.l_id and inv.status!=2 and res.status!=2 and inv.invoice_id=0 and inv.purchase_id=0 and inv.cradit_note_id=0 and inv.debit_note_id=0 and inv.ex_id=0 and inv.performa_id=0 and inv.excess_id=0) as paid_amount
		from tbl_ledger as cust where cust.l_id=".$POST['vender_id'];
		$result1=$dbcon->query($query1);
		$rel1=mysqli_fetch_assoc($result1);
		$due1=$rel1['opn_balance']-$rel1['paid_amount'];
		
		if($due1>0){
			
			if($rel1['balance_typeid']=="1"){
				$baltype="2";
				$colr="color:green";
			}
			if($rel1['balance_typeid']=="2"){
				$baltype="1";
				$colr="color:#d43f3a";
			}
			echo '<tr id="fieldtr'.$i.'" >
				<td colspan="3" style="vertical-align:center;'.$colr.'" class="text-center">
					<strong>Opening Balance</strong>
					<input type="hidden" name="o_ref_source[]" id="o_ref_source0" value="" />
					<input type="hidden" name="o_ref_type[]" id="o_ref_type0" value="'.$baltype.'" />
					<input type="hidden" name="o_ref_id[]" id="o_ref_id0" value="0" />
				</td>
				<td style="vertical-align:center;'.$colr.'" class="text-center">
					'.$rel1['opn_balance'].'
					<input type="hidden" name="o_ref_amount[]" id="o_ref_amount0" value="'.$rel1['opn_balance'].'" />
				</td>
				<td style="vertical-align:center;'.$colr.'" class="text-center">
					'.$due1.'
					<input type="hidden" name="o_ref_due[]" id="o_ref_due0" value="'.$due1.'" />
				</td>
				<td style="vertical-align:center;display:none;" width="4%" class="text-center tdskasar">
					<input type="hidden" name="o_tds_per[]" id="o_tds_per0" value="0" />
				</td>
				<td style="vertical-align:center;display:none;" width="6%" class="text-center tdskasar">
					<input type="hidden" name="o_tds[]" id="o_tds0" value="0" />
				</td>
				<td style="vertical-align:center;display:none;" class="text-center tdskasar">
					<input type="number"  title="Enter amount" min="0" onkeyup="get_kasar(0);"  id="o_kasar0" name="o_kasar[]"  class="form-control" />
				</td>
				<td style="vertical-align:center" class="text-center">
					<input type="number"  title="Enter amount" min="0" onkeyup="paid_total();" max="'.$due.'" id="o_amount0" name="o_amount[]"  class="form-control" />
				</td>
				<td style="vertical-align:center;" class="text-center">
					<center> <input type="checkbox" class="form-control" style="width: 26px;" id="chk_cust0" name="chk_cust0" onclick="use_amount(0);"> </center>
				</td>
			</tr>';
		}
		
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$due=$rel['ref_amount']-$rel['pay_amount'];
				//$due=$rel['pay_amount'];
				if($rel['ref_type']=="1"){
					$colr="color:#d43f3a";
					}else{
					$colr="color:green";
				}
				echo '<tr id="fieldtr'.$i.'" >
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$rel['type'].'
						<input type="hidden" name="o_ref_source[]" id="o_ref_source'.$i.'" value="'.$rel['type'].'" />
						<input type="hidden" name="o_ref_type[]" id="o_ref_type'.$i.'" value="'.$rel['ref_type'].'" />
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$rel['ref_no'].'
						<input type="hidden" name="o_ref_id[]" id="o_ref_id'.$i.'" value="'.$rel['ref_id'].'" />
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.date('d-M-Y',strtotime($rel['ref_date'])).'
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$rel['ref_amount'].'
						<input type="hidden" name="o_ref_amount[]" id="o_ref_amount'.$i.'" value="'.$rel['ref_amount'].'" />
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$due.'
						<input type="hidden" name="o_ref_due[]" id="o_ref_due'.$i.'" value="'.$due.'" />
					</td>
					<td style="vertical-align:center;display:none;" width="4%" class="text-center tdskasar">
						<input type="number" placeholder="%"  title="Enter amount" min="0" onkeyup="get_tds(1,'.$i.');" id="o_tds_per'.$i.'" name="o_tds_per[]"  class="form-control" />
					</td>
					<td style="vertical-align:center;display:none;" width="6%" class="text-center tdskasar">
						<input type="number"  placeholder="Amount" title="Enter amount" min="0" onkeyup="get_tds(2,'.$i.');"  id="o_tds'.$i.'" name="o_tds[]"  class="form-control" />
					</td>
					<td style="vertical-align:center;display:none;" class="text-center tdskasar">
						<input type="number" placeholder="Amount" title="Enter amount" min="0" onkeyup="get_kasar('.$i.');"  id="o_kasar'.$i.'" name="o_kasar[]"  class="form-control" />
					</td>
					<td style="vertical-align:center" class="text-center">
						<input type="number"  placeholder="Amount" title="Enter amount" min="0" onkeyup="paid_total();" max="'.$due.'" id="o_amount'.$i.'" name="o_amount[]"  class="form-control" />
					</td>
					<td style="vertical-align:center;" class="text-center">
						<center> <input type="checkbox" class="form-control" style="width: 26px;" id="chk_cust'.$i.'" name="chk_cust'.$i.'" onclick="use_amount('.$i.');"> </center>
					</td>
				</tr>';
				$i++;
			}
			echo '<input type="hidden" name="cou" id="cou" value="'.$i.'" />';
		}
		else {
			echo '<tr><td colspan="10" class="text-center">Due Payment Not Found </td></tr>';
		}
		echo '
		<tr>
			<td colspan="3" class="text-center"></td>
			<td colspan="2" style="display:none;" class="tdskasar"></td>
			<td colspan="2" class="tdskasar1"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;"><strong style="vertical-align:center;">	Amount Paid</strong></td>
			<td colspan="3" class="tdskasar"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;display:none;"><strong style="vertical-align:center;">Amount Paid</strong></td>
			<td class="text-center" style="background-color:#100648;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly"   style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Paid amount"  id="amount_paid" name="amount_paid"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly"   style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_paid_type" name="amount_paid_type"  class="form-control" />
			</td>
		</tr>
		<tr>
			<td colspan="3" class="text-center"></td>
			<td colspan="2" style="display:none;" class="tdskasar"></td>
			<td colspan="2" class="tdskasar1"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;"><strong style="vertical-align:center;">Amount Used For Payments</strong></td>
			<td colspan="3" class="tdskasar"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;display:none;"><strong style="vertical-align:center;">Amount Used For Payments</strong></td>
			<td class="text-center" style="background-color:#100648;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Amount Used For Payments"  id="amount_used_payment" name="amount_used_payment"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_used_payment_type" name="amount_used_payment_type"  class="form-control" />
			</td>
		</tr>
		<tr>
			<td colspan="3" class="text-center"></td>
			<td colspan="2" style="display:none;" class="tdskasar"></td>
			<td colspan="2" class="tdskasar1"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;"><strong style="vertical-align:center;">Amount In Excess</strong></td>
			<td colspan="3" class="tdskasar"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;display:none;"><strong style="vertical-align:center;">Amount In Excess</strong></td>
			<td class="text-center" style="background-color:#100648;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Amount In Excess"  id="amount_in_excess" name="amount_in_excess"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_in_excess_type" name="amount_in_excess_type"  class="form-control" />
			</td>
		</tr>
		<input type="hidden" name="full_paid" id="full_paid" value="" />
		<input type="hidden" name="full_paid_type" id="full_paid_type" value="" />
		</table>			 
		</div>
		
		</div>	';
	}
        else if(strtolower($POST['mode']) == "load_purchase_data") {
                $where = '';
                $where .= " WHERE ref_date >= '".date('Y-m-d', strtotime($_POST['start_date']))."' AND ref_date <= '".date('Y-m-d', strtotime($_POST['end_date']))."'";
		//invoice payment data
		// 1 .cr   2 dr
		$query='Select * from (
		
		(select "Purchase" as type,2 as ref_type,po_date as ref_date,po_no as ref_no,po_id as ref_id,g_total as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and po.po_id=trn.purchase_id) as pay_amount,po.cdate from  tbl_pono as po where status=0 and po.approve_status=1 AND vender_id='.$POST['vender_id'].' and po.g_total>(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and po.po_id=trn.purchase_id)) 
		
		) as data '.$where.' order by ref_date,ref_type DESC';
		
		//$query="select * from  tbl_pono where status=0 AND g_total>paid_amount AND vender_id=".$POST['vender_id'];
		$result=$dbcon->query($query);
		echo ' <div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field" style="background-color:#0088cc;color: white;font-size:15px ;" >
		<th class="text-center" width="10%">Type</th>
		<th class="text-center" width="6%">Ref No</th>
		<th class="text-center" width="10%">Date</th>
		<th class="text-center" width="7%">Amount</th>
		<th class="text-center" width="7%">Due Amount</th>
		<th colspan="2" style="display:none;" class="text-center tdskasar" width="10%">TDS</th>
		<th style="display:none;" class="text-center tdskasar" width="6%">Kasar</th>
		<th class="text-center" width="8%">Pay Amount</th>
		<th class="text-center" width="5%"></th>
		</tr>
		';
		$query1="select cust.opn_balance,cust.balance_typeid,
		(SELECT sum(inv.paid_amount) FROM `tbl_receipt_trn` as inv 
		left join tbl_receipt as res on res.receipt_id=inv.receipt_id
		where res.cust_id=cust.l_id and inv.status!=2 and res.status!=2 and inv.invoice_id=0 and inv.purchase_id=0 and inv.cradit_note_id=0 and inv.debit_note_id=0 and inv.ex_id=0 and inv.performa_id=0 and inv.excess_id=0) as paid_amount
		from tbl_ledger as cust where cust.l_id=".$POST['vender_id'];
		$result1=$dbcon->query($query1);
		$rel1=mysqli_fetch_assoc($result1);
		$due1=$rel1['opn_balance']-$rel1['paid_amount'];
		
		if($due1>0){
			
			if($rel1['balance_typeid']=="1"){
				$baltype="2";
				$colr="color:green";
			}
			if($rel1['balance_typeid']=="2"){
				$baltype="1";
				$colr="color:#d43f3a";
			}
			echo '<tr id="fieldtr'.$i.'" >
				<td colspan="3" style="vertical-align:center;'.$colr.'" class="text-center">
					<strong>Opening Balance</strong>
					<input type="hidden" name="o_ref_source[]" id="o_ref_source0" value="" />
					<input type="hidden" name="o_ref_type[]" id="o_ref_type0" value="'.$baltype.'" />
					<input type="hidden" name="o_ref_id[]" id="o_ref_id0" value="0" />
				</td>
				<td style="vertical-align:center;'.$colr.'" class="text-center">
					'.$rel1['opn_balance'].'
					<input type="hidden" name="o_ref_amount[]" id="o_ref_amount0" value="'.$rel1['opn_balance'].'" />
				</td>
				<td style="vertical-align:center;'.$colr.'" class="text-center">
					'.$due1.'
					<input type="hidden" name="o_ref_due[]" id="o_ref_due0" value="'.$due1.'" />
				</td>
				<td style="vertical-align:center;display:none;" width="4%" class="text-center tdskasar">
					<input type="hidden" name="o_tds_per[]" id="o_tds_per0" value="0" />
				</td>
				<td style="vertical-align:center;display:none;" width="6%" class="text-center tdskasar">
					<input type="hidden" name="o_tds[]" id="o_tds0" value="0" />
				</td>
				<td style="vertical-align:center;display:none;" class="text-center tdskasar">
					<input type="number"  title="Enter amount" min="0" onkeyup="get_kasar(0);"  id="o_kasar0" name="o_kasar[]"  class="form-control" />
				</td>
				<td style="vertical-align:center" class="text-center">
					<input type="number"  title="Enter amount" min="0" onkeyup="paid_total();" max="'.$due.'" id="o_amount0" name="o_amount[]"  class="form-control" />
				</td>
				<td style="vertical-align:center;" class="text-center">
					<center> <input type="checkbox" class="form-control" style="width: 26px;" id="chk_cust0" name="chk_cust0" onclick="use_amount(0);"> </center>
				</td>
			</tr>';
		}
		
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				$due=$rel['ref_amount']-$rel['pay_amount'];
				//$due=$rel['pay_amount'];
				if($rel['ref_type']=="1"){
					$colr="color:#d43f3a";
					}else{
					$colr="color:green";
				}
				echo '<tr id="fieldtr'.$i.'" >
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$rel['type'].'
						<input type="hidden" name="o_ref_source[]" id="o_ref_source'.$i.'" value="'.$rel['type'].'" />
						<input type="hidden" name="o_ref_type[]" id="o_ref_type'.$i.'" value="'.$rel['ref_type'].'" />
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$rel['ref_no'].'
						<input type="hidden" name="o_ref_id[]" id="o_ref_id'.$i.'" value="'.$rel['ref_id'].'" />
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.date('d-M-Y',strtotime($rel['ref_date'])).'
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$rel['ref_amount'].'
						<input type="hidden" name="o_ref_amount[]" id="o_ref_amount'.$i.'" value="'.$rel['ref_amount'].'" />
					</td>
					<td style="vertical-align:center;'.$colr.'" class="text-center">
						'.$due.'
						<input type="hidden" name="o_ref_due[]" id="o_ref_due'.$i.'" value="'.$due.'" />
					</td>
					<td style="vertical-align:center;display:none;" width="4%" class="text-center tdskasar">
						<input type="number" placeholder="%"  title="Enter amount" min="0" onkeyup="get_tds(1,'.$i.');" id="o_tds_per'.$i.'" name="o_tds_per[]"  class="form-control" />
					</td>
					<td style="vertical-align:center;display:none;" width="6%" class="text-center tdskasar">
						<input type="number"  placeholder="Amount" title="Enter amount" min="0" onkeyup="get_tds(2,'.$i.');"  id="o_tds'.$i.'" name="o_tds[]"  class="form-control" />
					</td>
					<td style="vertical-align:center;display:none;" class="text-center tdskasar">
						<input type="number" placeholder="Amount" title="Enter amount" min="0" onkeyup="get_kasar('.$i.');"  id="o_kasar'.$i.'" name="o_kasar[]"  class="form-control" />
					</td>
					<td style="vertical-align:center" class="text-center">
						<input type="number"  placeholder="Amount" title="Enter amount" min="0" onkeyup="paid_total();" max="'.$due.'" id="o_amount'.$i.'" name="o_amount[]"  class="form-control" />
					</td>
					<td style="vertical-align:center;" class="text-center">
						<center> <input type="checkbox" class="form-control" style="width: 26px;" id="chk_cust'.$i.'" name="chk_cust'.$i.'" onclick="use_amount('.$i.');"> </center>
					</td>
				</tr>';
				$i++;
			}
			echo '<input type="hidden" name="cou" id="cou" value="'.$i.'" />';
		}
		else {
			echo '<tr><td colspan="10" class="text-center">Due Payment Not Found </td></tr>';
		}
		echo '
		<tr>
			<td colspan="3" class="text-center"></td>
			<td colspan="2" style="display:none;" class="tdskasar"></td>
			<td colspan="2" class="tdskasar1"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;"><strong style="vertical-align:center;">	Amount Paid</strong></td>
			<td colspan="3" class="tdskasar"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;display:none;"><strong style="vertical-align:center;">Amount Paid</strong></td>
			<td class="text-center" style="background-color:#100648;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly"   style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Paid amount"  id="amount_paid" name="amount_paid"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly"   style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_paid_type" name="amount_paid_type"  class="form-control" />
			</td>
		</tr>
		<tr>
			<td colspan="3" class="text-center"></td>
			<td colspan="2" style="display:none;" class="tdskasar"></td>
			<td colspan="2" class="tdskasar1"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;"><strong style="vertical-align:center;">Amount Used For Payments</strong></td>
			<td colspan="3" class="tdskasar"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;display:none;"><strong style="vertical-align:center;">Amount Used For Payments</strong></td>
			<td class="text-center" style="background-color:#100648;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Amount Used For Payments"  id="amount_used_payment" name="amount_used_payment"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_used_payment_type" name="amount_used_payment_type"  class="form-control" />
			</td>
		</tr>
		<tr>
			<td colspan="3" class="text-center"></td>
			<td colspan="2" style="display:none;" class="tdskasar"></td>
			<td colspan="2" class="tdskasar1"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;"><strong style="vertical-align:center;">Amount In Excess</strong></td>
			<td colspan="3" class="tdskasar"  style="background-color:#cca900;color: white;font-size:15px;text-align:center;display:none;"><strong style="vertical-align:center;">Amount In Excess</strong></td>
			<td class="text-center" style="background-color:#100648;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Amount In Excess"  id="amount_in_excess" name="amount_in_excess"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly" style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_in_excess_type" name="amount_in_excess_type"  class="form-control" />
			</td>
		</tr>
		<input type="hidden" name="full_paid" id="full_paid" value="" />
		<input type="hidden" name="full_paid_type" id="full_paid_type" value="" />
		</table>			 
		</div>
		
		</div>	';
	}
	else if(strtolower($POST['mode'])== "cradit_max")
	{
		$q ="SELECT cra.g_total,
		(select sum(cradit_amount) as payment from tbl_purchasereceipt_trn as pur_trn where cra.credit_note_id=pur_trn.credit_note_id and status=0) as pur_cra_used,
		(select sum(paid_amount) as payment from tbl_purchasereceipt as pur_trn1 where cra.credit_note_id=pur_trn1.credit_note_id and status=0) as pur_cra_used1
		from tbl_credit_note as cra where credit_note_id=".$POST['cradit_id']." and credit_note_status=0";
		$result=$dbcon->query($q);
		$row=mysqli_fetch_assoc($result);
		$resp['pending_cra']=$row['g_total']-($row['pur_cra_used']+$row['pur_cra_used1']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "dabit_max")
	{
		$q ="SELECT dabit.g_total,
		(select sum(cradit_amount) as payment from tbl_purchasereceipt_trn as pur_trn where cra.credit_note_id=pur_trn.credit_note_id and status=0) as pur_cra_used,
		(select sum(paid_amount) as payment from tbl_purchasereceipt as pur_trn1 where cra.credit_note_id=pur_trn1.credit_note_id and status=0) as pur_cra_used1
		from tbl_debitnote as dabit where credit_note_id=".$POST['cradit_id']." and credit_note_status=0";
		$result=$dbcon->query($q);
		$row=mysqli_fetch_assoc($result);
		$resp['pending_cra']=$row['g_total']-($row['pur_cra_used']+$row['pur_cra_used1']);
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode'])== "bank_type1")
	{
		$q ="SELECT l_form from tbl_ledger as dabit where l_id=".$POST['id'];
		$result=$dbcon->query($q);
		$row=mysqli_fetch_assoc($result);
		$resp[]="";
		$resp['type']=$row['l_form'];
		echo json_encode($resp); 
	}
	else if(strtolower($POST['mode']) == "paid_amt") {
		if($POST['receipt_id']){
			$query = "select (sum(amount) - (select IFNULL(sum(amount),0) as paid_amt from tbl_receipt_payment_trn where isdelete=0 and payment_type=1 and receipt_id=".$POST['receipt_id']." and entry_type=1)) as paid_amt from tbl_receipt_payment_trn where  isdelete=0 and payment_type=1 and receipt_id=".$POST['receipt_id'];
		}else{
			$query = "select (sum(amount) - (select IFNULL(sum(amount),0) as paid_amt from tbl_receipt_payment_trn where isdelete=0 and payment_type=1 and receipt_id=0 and entry_type=1)) as paid_amt from tbl_receipt_payment_trn where isdelete=0 and payment_type=1 and receipt_id=0";
		}
		$result=$dbcon->query($query);
		$row=mysqli_fetch_assoc($result);
		echo abs($row['paid_amt']);
	}
	else if(strtolower($POST['mode']) == "get_data_description") {

		$nature_id = $POST['nature_id'];

		$sel = $dbcon->query("select common_mst_desc from tbl_common_mst where common_mst_id='$nature_id'");
		$row = brp_mysqli_fetch_assoc($sel);

		echo $row['common_mst_desc'];
		//echo $nature_id;
	}
	
?>
