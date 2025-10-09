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

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    FINANCE_RECEIPT_CREATE,
    FINANCE_RECEIPT_EDIT,
    FINANCE_RECEIPT_DELETE,
    FINANCE_RECEIPT_PRINT
]);
if(strtolower($POST['mode']) == "fetch") {
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
        $branch_id = $POST['branch_id'];
		
         //branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		$where_db = check_branch('receipt', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('receipt');

		$where.=" $where_company";

		$where_user=check_user('receipt');

		//$where.=" $where_user";

		// branch , comapny , user check end - dhaval       
		
		/* if ($POST['pay']!=""){
			$where1=" and receipt.payment_type=".$POST['pay'];
		} */
		$where1 = " and receipt.payment_type=2";
                $where .= " and receipt_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND receipt_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('receipt.receipt_id','receipt_no','vender.l_name','cheque_dtl', 'receipt.total_paid_amount','receipt_date','receipt.payment_type','receipt.cdate','receipt.user_id','payment.l_name as payment_mode','c.common_mst_name');
		$sIndexColumn = "receipt.receipt_id";
		$isWhere = array("receipt.status = 0  ".$where.$where1);
		$sTable = "tbl_receipt as receipt";			
		$isJOIN = array('left join tbl_ledger vender on vender.l_id=receipt.cust_id','left join tbl_ledger payment on payment.l_id=receipt.payment_mode_id','left join tbl_common_mst as c on c.common_mst_id=receipt.payment_mode_id');
		$hOrder = "receipt.receipt_id desc";
		//$hGroupby = "rtrn.receipt_id";
		include($path.'include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();

			$reciept_deta = $dbcon->query("SELECT l.l_name,l.cust_mobile,l.company_name FROM `tbl_receipt_payment_trn` as r left join tbl_ledger as l on l.l_id=r.ledger_id where r.payment_type=2 and r.receipt_id=".$row['receipt_id']."  and r.isdelete=0 limit 1 ");
            $reciept_deta_1 = brp_mysqli_fetch_assoc($reciept_deta);
            
			if ($row['payment_type']==1){
				$col='style="color:green"';
			}
			else{
				$col='style="color:red"';
			}
			$row_data[] = "<span ".$col.">".$row['sr']."</span>";
			$row_data[] = "<span ".$col.">".$row['receipt_no']."</span>";
			$row_data[] = "<span ".$col.">".$reciept_deta_1['company_name']."</span>";
			$chedel='';
			if($row['cheque_dtl']){
				$chedel="<span ".$col.">(".$row['cheque_dtl'].")</span>";
			}
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
			$btn='';
			/*if($row['generat_status']=="0" && $row['payment_type']=="2")
				{
				$btn=' <a class="btn btn-xs btn-info" data-original-title="Print Cheque" data-toggle="tooltip" data-placement="top" href="'.DOMAIN_CHEQUE.'generage-cheque/'.$row['chequegenerateid'].'" target="_blank"><i class="fa fa-money"></i></a>';
			}*/
			
			$edit_btn='';$print_btn='';$del_btn='';$whatsapp='';
			if(in_array(FINANCE_RECEIPT_EDIT,$bulkAccessArray)){
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$row['receipt_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(FINANCE_RECEIPT_DELETE,$bulkAccessArray)){
				$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment('.$row['receipt_id'].')"><i class="fa fa-trash-o"></i></button>';
			}

            if(in_array(FINANCE_RECEIPT_PRINT,$bulkAccessArray)){
                $print_btn='<a class="btn btn-xs btn-info" data-original-title="Receipt Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'receipt_sales/'.$row['receipt_id'].'"><i class="fa fa-print"></i></a>';
            }

            
            $key = "encryptionkey";
			$text=$row['receipt_id'];
			$encrypted = bin2hex(openssl_encrypt($text,'AES-128-CBC', $key));
            $whatsapp.='<a title="Send to Whatsapp" type="button" class="btn btn-xs btn-success" href="https://web.whatsapp.com/send?phone=+91'.$reciept_deta_1['cust_mobile'].'&text='.$reciept_deta_1['company_name'].'%0aThank you for your payment.%0aReceipt No:-'.$row['receipt_no'].'%0aDate:- '.date('d-m-Y',strtotime($row['receipt_date'])).'%0aAmount:- '.$row['total_paid_amount'].'%0aBest Regards%0a '.DOMAIN.FINANCE_ROOT.'linkreceipt_sales/'.$encrypted.'" target="_blank"> <i class="fa fa-whatsapp"></i></a>&nbsp;';
                        
			$row_data[] = $edit_btn.' '.$print_btn.' '.$del_btn.' '.$btn.' '.$whatsapp; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		//echo '<pre>';print_r($_POST);exit; 

		$t_entry_payment = get_total_receipt_payment($dbcon,2);

		if($POST['paid_amount'] != $t_entry_payment){
			$arr['msg']="3";		
			echo json_encode($arr);exit;
		}

		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=35 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);

		if(isset($POST['currency_enable'])){
	    	$curncy_trn['currency_id'] = $POST['currency_id'];
	    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
	    }else{
	    	$basecurrency = getbasecurrency($dbcon);
	    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
	    	$curncy_trn['currency_rate'] = 1;
	    }

        $info2['payment_type'] 	 = 2;
		$info2['receipt_no']  = $POST['receipt_no'];
		$info2['receipt_date']	 = date("Y-m-d",strtotime($POST['payment_date']));				
		
		$info2['cust_id']	= $POST['vender_id'];
		
		$info2['enable_billby_bill_show'] = (isset($_POST['enable_billby_bill_show']) && ($_POST['enable_billby_bill_show']=='yes')) ? 1 : 0; 
		$info2['ref_date']     = date("Y-m-d",strtotime($POST['ref_date']));
		$info2['total_paid_amount']	 = $POST['paid_amount'];
		$info2['payment_remark']	= ($_POST['payment_desc']);
		$info2['gst_nature']	= $_POST['gst_nature'];
		$info2['balance_typeid']	= 2;

		$info2['currency_enable']	= $_POST['currency_enable'];

		$info2['is_pdc'] = $POST['is_pdc'];
		$info2['pdc_date'] = date("Y-m-d",strtotime($POST['pdc_date']));
		$info2['payment_mode_id'] = $POST['payment_mode_id'];
		$info2['cheque_dtl'] = $POST['cheque_dtl'];
		$info2['currency_id']	= $_POST['currency_id'];
		$info2['currency_rate']	= $_POST['currency_rate'];

		$info2['cdate']		  = date("Y-m-d H:i:s");
		$info2['user_id']		= $_SESSION['user_id'];
		$info2['company_id']   = $_SESSION['company_id'];
		$info2['financial_year_id']		= $POST['financial_year'];

		$insertreceiptid=add_record('tbl_receipt', array_merge($info2,$curncy_trn), $dbcon, $POST['branch_id'] );

		/*Update Bill by bill Trn Table Start by Dhruv*/
        if($insertreceiptid){
			$billby_bill['bill_table_id']	= $insertreceiptid;
			//$billby_bill['bill_ledger_id'] =  $POST['vender_id'];
			$updatebillbytrnid=update_record('tbl_bill_by_bill_adjustment_transaction',array_merge($billby_bill,$curncy_trn),"bill_voucher_type=".$POST['bill_adjust_voucher_type']." and isdelete=0 and bill_table='tbl_receipt' and bill_table_id=0 and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		/*Update receipt Advance Payment Table Start by Dhruv*/
		if($insertreceiptid && $_POST['gst_nature'] == 99){
			$receipt_adv_payment['trn_voucher_id']	= $insertreceiptid;
			$updateAdvPaytrnid=update_record('tbl_advacne_receipt_trn',array_merge($receipt_adv_payment,$curncy_trn),"isdelete=0 and trn_voucher_type=".$_POST['bill_adjust_voucher_type']." and trn_table='tbl_receipt' and trn_voucher_id=0  and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		
		if($insertreceiptid){
			add_general_book_entry($dbcon,"tbl_receipt",$insertreceiptid,2,$POST['vender_id'],$POST['paid_amount'],'',date("Y-m-d",strtotime($POST['payment_date'])),'',$curncy_trn);

			$receipt_payment_trn_detail = get_receipt_payment_trn_detail($dbcon,2,0);
			foreach ($receipt_payment_trn_detail as $trn_detail) {
				add_general_book_entry($dbcon,"tbl_receipt_payment_trn",$trn_detail['receipt_payment_trn_id'],1,$trn_detail['ledger_id'],$trn_detail['amount'],'',date("Y-m-d",strtotime($POST['payment_date'])),'',$curncy_trn);
			}
		}

		/*Update receipt payment Trn Table Start by Dhruv*/
		if($insertreceiptid){
			$receipt_payment['receipt_id']	= $insertreceiptid;
			$updaterectrntrnid=update_record('tbl_receipt_payment_trn',array_merge($receipt_payment,$curncy_trn) ,"isdelete=0 and payment_type=2 and receipt_id=0  and user_id=".$_SESSION['user_id'] , $dbcon);
		}

		if($insertreceiptid)
		{	
			$arr['msg']="1";							
		}
		else
		$arr['msg']="0";
		
		echo json_encode($arr);
	}
    else if(strtolower($POST['mode']) == "edit") {
		
		if(!empty($POST['receiptid'])){
			$t_entry_payment = get_total_receipt_payment($dbcon,2,$POST['receiptid']);
		}else{
			$t_entry_payment = get_total_receipt_payment($dbcon,2,'');
		}
		
		if($POST['paid_amount'] != $t_entry_payment){
			$arr['msg']="3";		
			echo json_encode($arr);exit;
		}

		if(isset($POST['currency_enable'])){
	    	$curncy_trn['currency_id'] = $POST['currency_id'];
	    	$curncy_trn['currency_rate'] = $POST['currency_rate'];
	    }else{
	    	$basecurrency = getbasecurrency($dbcon);
	    	$curncy_trn['currency_id'] = $basecurrency['currencyid'];
	    	$curncy_trn['currency_rate'] = 1;
	    }

        $info2['payment_type'] 	 = 2;
		$info2['receipt_no']  = $POST['receipt_no'];
		$info2['receipt_date']	 = date("Y-m-d",strtotime($POST['payment_date']));				
		$info2['cust_id']	= $POST['vender_id'];
		$info2['enable_billby_bill_show'] = (isset($_POST['enable_billby_bill_show']) && ($_POST['enable_billby_bill_show']=='yes')) ? 1 : 0; 
		$info2['ref_date']     = date("Y-m-d",strtotime($POST['ref_date']));
		$info2['total_paid_amount']	 = $POST['paid_amount'];
		$info2['payment_remark']	= ($_POST['payment_desc']);
		$info2['gst_nature']	= $_POST['gst_nature'];
		$info2['balance_typeid']	= 2;
		$info2['currency_enable']	= $_POST['currency_enable'];
		$info2['cdate']		  = date("Y-m-d H:i:s");
		$info2['user_id']		= $_SESSION['user_id'];
		$info2['company_id']   = $_SESSION['company_id'];

		$info2['is_pdc'] = $POST['is_pdc'];
		$info2['pdc_date'] = date("Y-m-d",strtotime($POST['pdc_date']));
		$info2['payment_mode_id'] = $POST['payment_mode_id'];
		$info2['cheque_dtl'] = $POST['cheque_dtl'];
		$info2['currency_id']	= $_POST['currency_id'];
		$info2['currency_rate']	= $_POST['currency_rate'];

		$updatetreceiptid = update_record('tbl_receipt',array_merge($info2,$curncy_trn),"receipt_id=".$POST['receiptid'], $dbcon);

		$info_gen['ref_date']	= date("Y-m-d",strtotime($POST['payment_date']));
		$info_gen['amount']	= $POST['paid_amount'];
		$updatetrancationid5=update_record('tbl_general_book',array_merge($info_gen,$curncy_trn),"table_name='tbl_receipt' and table_id=".$POST['receiptid'] , $dbcon);

		if($updatetreceiptid)
		{	
			$arr['msg']="update";							
		}
		else{
			$arr['msg']="0";
		}		

		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "delete") {
	
		$info['status']		= 2;
		$updatetrancationid1=update_record('tbl_receipt', $info,"receipt_id=".$POST['eid'], $dbcon);	
		
		$info_del['isdelete'] = 1;
		$updatetrancationid2=update_record('tbl_receipt_payment_trn', $info_del,"receipt_id=".$POST['eid'], $dbcon);
		$updatetrancationid3=update_record('tbl_bill_by_bill_adjustment_transaction', $info_del,"bill_table_id=".$POST['eid'], $dbcon);
		$updatetrancationid4=update_record('tbl_advacne_receipt_trn', $info_del,"trn_voucher_id=".$POST['eid'], $dbcon);

		$info_gen['genral_book_status']	= 2;
		$updatetrancationid5=update_record('tbl_general_book', $info_gen,"table_name='tbl_receipt' and table_id=".$POST['eid'] , $dbcon);

		$query = "SELECT * FROM tbl_receipt_payment_trn WHERE receipt_id=".$POST['eid']."";
		$rs_type=$dbcon->query($query);
		while($row=mysqli_fetch_assoc($rs_type)){
			$updatetrancationid6=update_record('tbl_general_book', $info_gen,"table_name='tbl_receipt_payment_trn' and table_id=".$row['receipt_payment_trn_id'] , $dbcon);
		}

		if($updatetrancationid1)
			echo "1";	
		else
			echo "0";			
	}
	
	/** Start code by Dhruv **/

	else if (strtolower($POST['mode'])== "add_paymnt_entry_field") {

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
			$insert_exc=add_record('tbl_receipt_payment_trn', $info_exe, $dbcon);

			if(!empty($POST['receiptid'])){
				add_general_book_entry($dbcon,"tbl_receipt_payment_trn",$insert_exc,1,$POST['ledger_id'],$POST['entry_amount'],'',date("Y-m-d",strtotime($POST['payment_date'])),'',$curncy_trn);
			}

			if($insert_exc)
				echo "1";	
			else
				echo "0";
		}
		
	}

	else if(strtolower($POST['mode']) == "fetch_payment_entry") {
					
		$appData = array();
		$i=1;
		$where='';
		if(!empty($POST['receiptid'])){
			$where="and rpt.receipt_id= ".$POST['receiptid']." ";
		}else{
			$where="and rpt.receipt_id= 0 ";
		}
		$aColumns = array('rpt.receipt_payment_trn_id,bt.balance_type_name,tl.l_name,rpt.amount,tl.l_id');
		$sIndexColumn = "rpt.receipt_payment_trn_id";
		$isWhere = array("rpt.payment_type=2 and rpt.isdelete=0 and rpt.company_id in (0,$_SESSION[company_id]) ".$where);
		$sTable = "tbl_receipt_payment_trn as rpt";			
		$isJOIN = array(" left join mst_balance_type as bt on rpt.entry_type=bt.balance_typeid "," left join tbl_ledger as tl on rpt.ledger_id=tl.l_id ");
		$hOrder = "rpt.receipt_payment_trn_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {

			$row1=get_ledger_details($dbcon,$row['l_id']);

			if( ($POST['company_bill_balance'] == 1) && ($row1['enable_billbybill_opening']==1) ){
				$show_bill = '<a href="#" id="billby_bill_link" onClick="get_bill_show(\'yes\',\'invoice\',\'entry_amount\',\'receiver_ledger\',\''.$row['l_id'].'\',\''.$row['l_name'].'\');"> Check Bill By Bill Adjustment </a>';
			}else{
				$show_bill='';
			}

			if($POST['gst_nature'] == 99){
				
				$gst_nature_link = '<a href="#" onclick="get_adv_receipt_popup(\'99\',\'field_ledger_id\')" id="checkAdvPayLink" >Check Advance Payment Refund</a>';
			
			}else{
				$gst_nature_link = '';
			}
			
			$row_data = array();
			//$row_data[] = $id;
			$row_data[] = $row['balance_type_name']; 
			$row_data[] = "<input type='hidden' id='field_ledger_id' value=".$row['l_id']."  /><b>".$row['l_name']."</b>"."<br>".$show_bill."<br>".$gst_nature_link; 
			$row_data[] = number_format($row['amount'],2,'.',''); 
			//$row_data[] = $btype; 
			
			$count_btn='';$edit_btn='';$delete_btn='';
				
				$count_btn='<input type="hidden" class="fieldcount" />';

				$edit_btn='<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_payment_entry('.$row['receipt_payment_trn_id'].');"><i class="fa fa-pencil"></i></button>';
				
				$delete_btn='<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment_entry('.$row['receipt_payment_trn_id'].')"><i class="fa fa-trash-o"></i></button>';
			
			$row_data[] = $edit_btn.' '.$delete_btn.' '.$count_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}

	else if(strtolower($POST['mode']) == "delete_payment_entry") {
		$info['isdelete']='1';
		$updateid=update_record('tbl_receipt_payment_trn', $info,"receipt_payment_trn_id=".$POST['eid'] , $dbcon);
		if(!empty($POST['receiptid'])){
			$info1['genral_book_status']='2';
			$updategeneid=update_record('tbl_general_book', $info1,"table_id=".$POST['eid']." and table_name='tbl_receipt_payment_trn' " , $dbcon);
		}
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}else if(strtolower($POST['mode']) == "delete_all_receipt_entry") {

		$info['isdelete']='1';
		
		if(empty($POST['receiptid'])){
			$updateid=update_record('tbl_receipt_payment_trn', $info,"receipt_id=0 and payment_type=2", $dbcon);		
			$updatebillbyid=update_record('tbl_bill_by_bill_adjustment_transaction', $info,"bill_table_id=0 and bill_table='tbl_receipt' and bill_voucher_type=".$POST['bill_voucher_type']." " , $dbcon);
			$updateadvid=update_record('tbl_advacne_receipt_trn', $info,"trn_voucher_id=0 and advance_receipt_type=0 and trn_voucher_type=".$POST['bill_voucher_type']." " , $dbcon);

		}
		
	}

	else if(strtolower($POST['mode']) == "preedit_payment_entry") {			
		$q = $dbcon -> query("SELECT * FROM tbl_receipt_payment_trn WHERE receipt_payment_trn_id = ".$POST['edit_payment_entry_id']." ");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}

	else if(strtolower($POST['mode'])== "get_ledger_details")
	{
		$ledger_id=$POST['ledger_id'];
		$row=get_ledger_details($dbcon,$ledger_id);
		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "get_total_receipt_payment")
	{
		$get_total = get_total_receipt_payment($dbcon,2,$POST['receiptid']);
		echo $get_total;
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
		$query1="select * from tbl_invoicetype where type_id=35 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
		//print_r($row);exit;
		echo json_encode($row);
	}
	else if(strtolower($POST['mode']) == "load_data") {
		//echo get_sales_customer_due_amount($dbcon,$POST['vender_id'],"2");
		
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
		
		echo get_chequeno($POST['acc_id'],$dbcon);
	}
	else if(strtolower($POST['mode']) == "load_tempoutward") {
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
		
		) as data order by ref_date,ref_type DESC';
		
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
			</tr>';
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
			'.$rel1['opening_balance'].'
			<input type="hidden" name="o_ref_amount[]" id="o_ref_amount0" value="'.$rel1['opening_balance'].'" />
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
		else
		{
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
			<input type="text" readonly="readonly"   style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Amount Used For Payments"  id="amount_used_payment" name="amount_used_payment"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly"   style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_used_payment_type" name="amount_used_payment_type"  class="form-control" />
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
			<input type="text" readonly="readonly"   style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_in_excess_type" name="amount_in_excess_type"  class="form-control" />
			</td>
		</tr>
			<input type="hidden" name="full_paid" id="full_paid" value="" />
			<input type="hidden" name="full_paid_type" id="full_paid_type" value="" />
		</table>			 
		</div>
		</div>	';
	}
        else if(strtolower($POST['mode']) == "load_invoice_data") {
		//invoice payment data
		// 1 .cr   2 dr
		$query='Select * from (
		
		(select "Invoice" as type,1 as ref_type,invoice_date as ref_date,invoice_no as ref_no,invoice_id as ref_id,g_total as ref_amount,(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and inv.invoice_id=trn.invoice_id) as pay_amount,inv.cdate  from tbl_invoice as inv where invoice_status=0 AND cust_id='.$POST['vender_id'].' and inv.g_total>(select IFNULL(sum(total_amount),0) as qty from  tbl_receipt_trn as trn where status=0 and inv.invoice_id=trn.invoice_id))
		) as data order by ref_date,ref_type DESC';
		
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
			</tr>';
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
			'.$rel1['opening_balance'].'
			<input type="hidden" name="o_ref_amount[]" id="o_ref_amount0" value="'.$rel1['opening_balance'].'" />
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
		else
		{
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
			<input type="text" readonly="readonly"   style="background-color: #100648;color: #f5f5f5;border:3px solid #100648;" title="Amount Used For Payments"  id="amount_used_payment" name="amount_used_payment"  class="form-control" />
			</td>
			<td class="text-center" style="background-color:#5cb85c;color: white;font-size:15px ;">
			<span ></span>
			<lable id=""></lable>
			<input type="text" readonly="readonly"   style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_used_payment_type" name="amount_used_payment_type"  class="form-control" />
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
			<input type="text" readonly="readonly"   style="background-color: #5cb85c;color: #f5f5f5;border:3px solid #5cb85c;" title="Type"  id="amount_in_excess_type" name="amount_in_excess_type"  class="form-control" />
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
	
?>