<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
//error_reporting(E_ALL);
include_once("../../include/common_functions.php");
include("../../include/function_database_query.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "fetch") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		
		/* if ($POST['pay']!=""){
			$where1=" and receipt.payment_type=".$POST['pay'];
		} */
		$where1=" and receipt.payment_type=1";
		$where ="  and receipt_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND receipt_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
		$appData = array();
		$i=1;
		$aColumns = array('receipt.receipt_id','receipt_no', 'vender.l_name','payment.l_name as payment_mode','cheque_dtl', 'receipt.total_paid_amount','receipt_date','receipt.payment_type','receipt.cdate','receipt.user_id');
		$sIndexColumn = "receipt.receipt_id";
		$isWhere = array("receipt.status = 0".check_user('receipt').$where.$where1);
		$sTable = "tbl_receipt as receipt";			
		$isJOIN = array('inner join tbl_ledger vender on vender.l_id=receipt.cust_id','left join tbl_ledger payment on payment.l_id=receipt.payment_mode_id');
		$hOrder = "receipt.receipt_id desc";
		//$hGroupby = "rtrn.receipt_id";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			if ($row['payment_type']==1){
				$col='style="color:green"';
			}
			else{
				$col='style="color:red"';
			}
			$row_data[] = "<span ".$col.">".$row['sr']."</span>";
			$row_data[] = "<span ".$col.">".$row['receipt_no']."</span>";
			//$row_data[] = "<span ".$col.">".$row['inv']."</span>";
			$row_data[] = "<span ".$col.">".$row['l_name']."</span>";
			$chedel='';
			if($row['cheque_dtl']!=""){
				$chedel="<span ".$col.">(".$row['cheque_dtl'].")</span>";
			}
			$row_data[] =  "<span ".$col.">".$row['payment_mode'].' '.$chedel.'</span>';
			
			
			
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
			
			$print_btn='';$del_btn='';
			if($delete_btn_per){
				$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment('.$row['receipt_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			$print_btn='<a class="btn btn-xs btn-info" data-original-title="Receipt Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'receipt_sales/'.$row['receipt_id'].'"><i class="fa fa-print"></i></a>';
			
			$row_data[] = $print_btn.' '.$del_btn.' '.$btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=4 and company_id=".$_SESSION['company_id']);
		
		$info2['receipt_no'] 			 = $POST['receipt_no'];
		$info2['receipt_date']			 = date("Y-m-d",strtotime($POST['payment_date']));
		$info2['cust_id']		    	 = $POST['vender_id'];
		$info2['bank_id']	    		 = $POST['bankid'];
		$info2['payment_mode_id']	  	 = $POST['paymentmodeid'];
		$info2['cheque_dtl']     	 	 = $POST['cheque_dtl'];
		$info2['ref_date']     			 = date("Y-m-d",strtotime($POST['ref_date']));
		if($POST['full_paid_type']=="DR"){
			$full_paid_type1="2";
			
			}else{
			$full_paid_type1="1";
			
		}
		$info2['payment_type']	   		 = $full_paid_type1;
		$info2['total_paid_amount']	   	 = $POST['paid_amount'];
		$info2['payment_remark']	   	 = ($_POST['payment_desc']);
		$info2['cdate']		   	 	 	 = date("Y-m-d H:i:s");
		$info2['user_id']		  		 = $_SESSION['user_id'];
		$info2['company_id']     		 = $_SESSION['company_id'];
		$insertreceiptid=add_record('tbl_receipt', $info2, $dbcon);
		
		$info1['ref_date']	= date("Y-m-d",strtotime($POST['payment_date']));
		$info1['table_name']	= "tbl_payment";
		$info1['table_id']		= $insertreceiptid;
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $POST['vender_id'];
		$info1['amount']		= $POST['paid_amount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']	= $_SESSION['company_id'];
		
		$inserid11=add_record("tbl_general_book", $info1, $dbcon);
		
		$info21['ref_date']	= date("Y-m-d",strtotime($POST['payment_date']));
		$info21['table_name']	= "tbl_payment";
		$info21['table_id']		= $insertreceiptid;
		$info21['entry_type']	= 2;
		$info21['ledger_id']		= $POST['paymentmodeid'];
		$info21['amount']		= $POST['paid_amount'];
		$info21['user_id']		= $_SESSION['user_id'];
		$info21['cdate']			= date("Y-m-d H:i:s");
		$info21['company_id']	= $_SESSION['company_id'];
		
		$inserid121=add_record("tbl_general_book", $info21, $dbcon);
		
		foreach ($POST['o_ref_id'] as $key => $name) 
		{	
			$o_ref_id=$POST['o_ref_id'][$key];
			$amo=$POST['o_amount'][$key];
			$source=$_POST['o_ref_source'][$key];
			$totalamo=($POST['o_tds'][$key])+($POST['o_kasar'][$key])+($POST['o_amount'][$key]);
			if($amo!=""){
				$infotrn['receipt_id']     =$insertreceiptid;
				if($source=="Purchase"){
					$infotrn['purchase_id']       =$o_ref_id;
					$infotrn['invoice_id']        = 0;
					$infotrn['cradit_note_id']    = 0;
					$infotrn['debit_note_id']     = 0;
					$infotrn['excess_id']    	  = 0;
					$infotrn['ex_id']    	  	  = 0;
				}
				else if($source=="Expense"){
					$infotrn['ex_id']      		 = $o_ref_id;
					$infotrn['purchase_id']       = 0;
					$infotrn['invoice_id']        = 0;
					$infotrn['cradit_note_id']    = 0;
					$infotrn['debit_note_id']     = 0;
					$infotrn['excess_id']    	  = 0;
				}else if($source=="Invoice"){
					$infotrn['invoice_id']        = $o_ref_id;
					$infotrn['purchase_id']       = 0;
					$infotrn['cradit_note_id']    = 0;
					$infotrn['debit_note_id']     = 0;
					$infotrn['excess_id']    	  = 0;
					$infotrn['ex_id']    	  	  = 0;
				}else if($source=="Credit Note"){
					$infotrn['cradit_note_id']    = $o_ref_id;
					$infotrn['purchase_id']       = 0;
					$infotrn['invoice_id']        = 0;
					$infotrn['debit_note_id']     = 0;
					$infotrn['excess_id']    	  = 0;
					$infotrn['ex_id']    	  	  = 0;
				}else if($source=="Debit Note"){
					$infotrn['debit_note_id']     = $o_ref_id;
					$infotrn['purchase_id']       = 0;
					$infotrn['invoice_id']        = 0;
					$infotrn['cradit_note_id']    = 0;
					$infotrn['excess_id']    	  = 0;
					$infotrn['ex_id']    	  	  = 0;
				}else if($source=="excess"){
					$infotrn['debit_note_id']     = 0;
					$infotrn['purchase_id']       = 0;
					$infotrn['invoice_id']        = 0;
					$infotrn['cradit_note_id']    = 0;
					$infotrn['excess_id']    	  = $o_ref_id;
					$infotrn['ex_id']    	  	  = 0;
				}
				else{
					$infotrn['purchase_id']       = 0;
					$infotrn['invoice_id']        = 0;
					$infotrn['cradit_note_id']    = 0;
					$infotrn['debit_note_id']     = 0;
					$infotrn['excess_id']    	  = 0;
					$infotrn['ex_id']    	  	  = 0;
				}
				
				$infotrn['payment_source']      =$source;
				$infotrn['tds_per']      		=$POST['o_tds_per'][$key];
				$infotrn['tds']      			=$POST['o_tds'][$key];
				$infotrn['kasar']      			=$POST['o_kasar'][$key];
				$infotrn['paid_amount']      	=$POST['o_amount'][$key];
				$infotrn['total_amount']        =$totalamo;
				$infotrn['payment_type']        =$POST['o_ref_type'][$key];
				
				$infotrn['user_id']    		 =$_SESSION['user_id'];
				$infotrn['company_id']    	 =$_SESSION['company_id'];
				$infotrn['usertype_id']   	 =$_SESSION['user_type'];
				//var_dump($infotrn);
				$inserttrn=add_record('tbl_receipt_trn', $infotrn, $dbcon);
			}
			
			
		}
		if($POST['amount_in_excess']!="0"){
			
			if($POST['amount_in_excess_type']=="DR"){
				$amount_in_excess_type="2";
			}else{
				$amount_in_excess_type="1";
			}
			$info_exe['receipt_id'] 			 = $insertreceiptid;
			$info_exe['cust_id'] 			 	 = $POST['vender_id'];
			$info_exe['excess_type'] 			 = $amount_in_excess_type;
			$info_exe['excess_amount'] 			 = $POST['amount_in_excess'];
			$info_exe['user_id']    	 		 = $_SESSION['user_id'];
			$info_exe['company_id']    	 	 	 = $_SESSION['company_id'];
			$info_exe['usertype_id']   	 	 	 = $_SESSION['user_type'];
			
			$insert_exc=add_record('tbl_excess', $info_exe, $dbcon);
		}
		
		if($POST['save_cheque']=="1")
		{
			$arr['msg']="1";
			$arr['eid']=$insertreceiptid;
			$arr['cheque_genid']=$insert_cheque;
		}
		else
		{
			if($insertreceiptid)
			{	
				$arr['msg']="1";							
			}
			else
			$arr['msg']="0";
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "delete") {
		/*$qry="select * from tbl_purchasereceipt where purchasereceipt_id=".$POST['eid'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));
			$purchasebillid=$rel['purchasebill_id'];
			$paidamount=$rel['paid_amount'];
			$query_from = $dbcon->query("UPDATE  tbl_pono SET paid_amount = paid_amount - ".$paidamount ." WHERE po_id = ".$purchasebillid);
			
			
			if($purchasebillid=="0"){
			$qry1="select * from tbl_purchasereceipt_trn where purchasereceipt_id=".$POST['eid'];
			$result1=$dbcon->query($qry1);
			while($row1=mysqli_fetch_assoc($result1))
			{
			
			$query_from = $dbcon->query("UPDATE tbl_pono SET paid_amount = paid_amount - ". $row1['paid_amount']." WHERE po_id = ".$row1['purchasebill_id']);	
			
			
			}
			}
		*/
		$info['status']		= 2;
		$updatetrancationid1=update_record('tbl_receipt', $info,"receipt_id=".$POST['eid'], $dbcon);	
		$updatetrancationid2=update_record('tbl_receipt_trn', $info,"receipt_id=".$POST['eid'], $dbcon);
		$updatetrancationid3=update_record('tbl_excess', $info,"receipt_id=".$POST['eid'], $dbcon);
		$infogb['genral_book_status']		= 2;
		$updatepb=update_record('tbl_general_book', $infogb, "table_name='tbl_payment' and table_id=".$POST['eid'], $dbcon);
			
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
		$query1="select * from tbl_invoicetype where type_id=4 and company_id=".$_SESSION['company_id'];
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