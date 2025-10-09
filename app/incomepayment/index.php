<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$delete_btn=true;
			$aColumns = array('receipt_id','receipt_no', 'ledger.l_name','inc.invoice_no','payment.payment_mode','cheque_dtl', 'receipt.paid_amount','receipt.paymentmodeid','payment_date','receipt.cdate','receipt.user_id');
			$sIndexColumn = "receipt_id";
			$isWhere = array("receipt.status = 0 and receipt.receipt_flag='income' ".check_user('receipt'));
			$sTable = "tbl_receipt as receipt";			
			$isJOIN = array('left join tbl_ledger ledger on ledger.l_id=receipt.cust_id','left join tbl_payment_mode payment on payment.paymentmodeid=receipt.paymentmodeid','left join tbl_payment_cheque_generate as pay on pay.purchase_payid=receipt_id and generat_status=0','left join income_mst as inc on receipt.invoice_id=inc.incomeid');
			$hOrder = "receipt.receipt_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['receipt_no'];
				 $row_data[] = $row['l_name'];
				 $row_data[] = $row['invoice_no'];
				$row_data[] = $row['paymentmodeid']==1 ? $row['payment_mode'] : $row['payment_mode'] .' ('.$row['cheque_dtl'].')';;
				$row_data[] = floatval($row['paid_amount']);
				$row_data[] = date('d M, Y',strtotime($row['payment_date']));
                $btn='';$delete='';
				
				if($delete_btn)//$pagename,$usetype,$permission,$dbcon
				{
					$delete=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment('.$row['receipt_id'].')"><i class="fa fa-trash-o"></i></button>';
				} 
				$row_data[] = $delete; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "payment") {
				$paid_amount=$POST['paid_amount'];
				if($POST['payment_type']=="1")//Bill to Bill payment update in Purchase Table
				{
					$query_from = $dbcon->query("UPDATE income_mst SET paid_amount = paid_amount + ".$paid_amount." WHERE incomeid = ".$POST['invoice_id']);
				}
				$acc_id		=	$POST['pur_acc_id'];
				if($POST['paymentmodeid']==1)//cash payment mode
				{	
					$acc_id	= get_company_cash_accounts($dbcon);
				}
				$info2['receipt_no']= $POST['receipt_no'];
				$infopbk['paymentmodeid']	= $info2['paymentmodeid']	= $POST['paymentmodeid'];					
				$infopbk['acc_id']	        = $info2['acc_id']			= $acc_id;
				$infopbk['reference_no']    = $info2['cheque_dtl']	 	= $POST['cheque_dtl'];
				$infopbk['bill_no_ref']     = $info2['invoice_id']		= $POST['invoice_id'];
				$infopbk['amount']          = $info2['paid_amount']	 	= $paid_amount;
				$infopbk['entry_date']      = $info2['payment_date'] 	= date("Y-m-d",strtotime($POST['payment_date']));
                $infopbk['reference_date']  = $info2['ref_date']	 	= date("Y-m-d",strtotime($POST['ref_date']));
				$infopbk['cdate']           = $info2['cdate']			= date("Y-m-d H:i:s");
				$infopbk['user_id']         = $info2['user_id']			= $_SESSION['user_id'];
				$infopbk['company_id'] 		= $info2['company_id']		= $_SESSION['company_id'];
				$info2['cust_id']			= $POST['cust_id'];
				$info2['receipt_flag']		= 'income';			
				$insertreceiptid=add_record('tbl_receipt', $info2, $dbcon);
				//passbook code
				$rs=$dbcon->query("SELECT cust_id,company_name FROM tbl_customer  where cust_id=".$POST['cust_id']);
				$rel=mysqli_fetch_assoc($rs);
				$infopbk['customer_id']     =$rel['cust_id'];
				$infopbk['typeid']          = '2';	//debit
				$infopbk['trn_id']          = $insertreceiptid;
				$infopbk['trn_table']       = 'tbl_receipt';
				$infopbk['passbook_note']   = 'Income Payment :'.$rel['company_name'];
				$insert=add_record('tbl_passbookentry', $infopbk, $dbcon);
				if($info2['paymentmodeid']==2 && $info2['ref_date']>date("Y-m-d") )//if cheque select then
				{
					/**Payment Remainder entry START***/
					$info_remainder['task_detail']		= ' Income Cheque of  ( '.get_bank_name($dbcon,$POST['bankid']).' - '.$POST['cheque_dtl'].' ) ';
					$info_remainder['date']				= $info2['ref_date'];
					$info_remainder['company_id']		= $_SESSION['company_id'];
					$inserinvoiceid=add_record('todo_mst', $info_remainder, $dbcon);
					/**Payment Remainder entry END***/
				}
				if($insertreceiptid)
				{	
					(strtolower($POST['mode'])== "payment"?$arr['msg']="2":$arr['msg']="1");							
				}
				else
					$arr['msg']="0";
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "delete") {
			$qry="select * from tbl_receipt where receipt_id=".$POST['eid'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));
				 $invoiceid=$rel['invoice_id'];
				$paidamount=$rel['paid_amount'];
			if(!empty($invoiceid))
				$query_from = $dbcon->query("UPDATE  income_mst SET paid_amount = paid_amount - ".$paidamount ." WHERE incomeid = ".$invoiceid." and paid_amount >=".$paidamount);
			
			$info['status']		= 2;
			$updatetrancationid=update_record('tbl_receipt', $info,"receipt_id=".$POST['eid'] , $dbcon);			
            $updatetrancationid=update_record('tbl_passbookentry', $info,"trn_id=".$POST['eid']." and trn_table='tbl_receipt'" , $dbcon);	
			if($updatetrancationid)
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
		else if(strtolower($POST['mode']) == "load_data") {
			if($POST['payment_type']==1)
			{
				echo getincome_billno($dbcon,$POST['cust_id'],'');
			}
			else if($POST['payment_type']==2)
			{
				echo get_income_customer_due_amount($POST['cust_id'],$dbcon);
			}
			else
			{
				echo "0";
			}
				
		}		
		else if(strtolower($POST['mode']) == "load_totaldata") {
			$qry="select* from income_mst where incomeid=".$POST['invoice_id'];
			$total=mysqli_fetch_assoc($dbcon->query($qry));	
			echo json_encode($total);
		}
		else if(strtolower($POST['mode']) == "get_chequeno") {
			
			echo get_chequeno($POST['acc_id'],$dbcon);
		}		
		else if(strtolower($POST['mode']) == "load_debit_note_amount") {
			$query="select sum(note.debit_amount) as debit_amount,sum(note.debit_paidamount) as debit_paidamount from  tbl_purchasedebitnote note where debitnote_status=0 and purchasedebitnote_id in (".implode(",",$POST['purchasedebit_note']).") and company_id=".$_SESSION['company_id'];
			$rel=mysqli_fetch_assoc($dbcon->query($query));		
			echo $note_amount=$rel['debit_amount']-$rel['debit_paidamount'];
		}
		else if(strtolower($POST['mode']) == "load_debit_note") {
				echo get_purcahsedebitnote($dbcon,$POST['vendor_id']);
		}
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
function passbook_debit_entry()
{
    
}
?>