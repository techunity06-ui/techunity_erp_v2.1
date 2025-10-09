<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");
		
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
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
			
			$where ="  and payment_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND payment_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		
			$appData = array();
			$i=1;
			$aColumns = array('receipt_id','receipt_no', 'cust.company_name','payment.payment_mode','cheque_dtl', 'receipt.paid_amount','payment_date','receipt.cdate','receipt.user_id','receipt.usertype_id','receipt.paymentmodeid');
			$sIndexColumn = "receipt_id";
			$isWhere = array("receipt.status = 0".check_user('receipt').$where);
			$sTable = "tbl_receipt as receipt";			
			$isJOIN = array('inner join  tbl_customer cust on cust.cust_id=receipt.cust_id','inner join tbl_payment_mode payment on payment.paymentmodeid=receipt.paymentmodeid');
			$hOrder = "receipt.receipt_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['receipt_no'];
			 	$row_data[] = $row['company_name'];
				$row_data[] = $row['paymentmodeid']==1 ? $row['payment_mode'] : $row['payment_mode'] .' ('.$row['cheque_dtl'].')';
				$row_data[] = $row['paid_amount'];
				$row_data[] = date('d M, Y',strtotime($row['payment_date']));
				 
				$row_data[] = '
				<a class="btn btn-xs btn-info" data-original-title="Receipt Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'receipt/'.$row['receipt_id'].'"><i class="fa fa-print"></i></a>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_payment('.$row['receipt_id'].')"><i class="fa fa-trash-o"></i></button>
				'; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
		{
			if($POST['payment_type']=="1")
			{
				$query_from = $dbcon->query("UPDATE  tbl_invoice SET paid_amount = paid_amount + ".  $POST[	'paid_amount']." WHERE invoice_id = ".$POST['invoice_id']);
				$info2['invoice_id']	=	$POST['invoice_id'];
			}
				$info2['receipt_no']	=	$POST['receiptid'];
				$infopbk['paymentmodeid']	= $info2['paymentmodeid']	=	$POST['paymentmodeid'];
				if($POST['paymentmodeid']==1)
				{	
					$qry="select acc_id,acc_type from account_mst where acc_type=1 and acc_status=0 and company_id=".$_SESSION['company_id'];
					$rel_acc=mysqli_fetch_assoc($dbcon->query($qry));	
					$acc_id=$rel_acc['acc_id'];
				}
				else
				{
					$acc_id		=	$POST['pur_acc_id'];
				}
				   $infopbk['acc_id']	= $info2['acc_id']		=	$acc_id;
							$infopbk['reference_no']    = $info2['cheque_dtl']	=	$POST['cheque_dtl'];
							$infopbk['bill_no_ref']     = $info2['invoice_id']	= 	$POST['invoice_id'];
							$infopbk['amount']          = $info2['paid_amount']	=	$POST['paid_amount'];
							$infopbk['entry_date']      = $info2['payment_date']	=	date("Y-m-d",strtotime($POST['payment_date']));
                            $infopbk['reference_date']  = $info2['ref_date']	    = date("Y-m-d",strtotime($POST['ref_date']));
							$infopbk['cdate']           = $info2['cdate']			= 	date("Y-m-d H:i:s");
							$infopbk['user_id']         = $info2['user_id']		= $_SESSION['user_id'];
							$info2['usertype_id']	= $_SESSION['user_type'];
							$info2['cust_id']		= 	$POST['cust_id'];
							$info2['company_id']	= $_SESSION['company_id'];
							$info2['bank_id']	= $POST['bankid'];
							$insertreceiptid=add_record('tbl_receipt', $info2, $dbcon);
                            //Passbook Entry
                            $rs=$dbcon->query("SELECT cust_id,company_name FROM tbl_customer as cust where cust_id=".$POST['cust_id']);
                            $rel_cust=mysqli_fetch_assoc($rs);
                            $rel=mysqli_fetch_assoc($rs);
                            $infopbk['customer_id']     =$rel['cust_id'];
                            $infopbk['typeid']          = '2';//Credit
                            $infopbk['trn_id']          = $insertreceiptid;
                            $infopbk['trn_table']       = 'tbl_receipt';
                            $infopbk['passbook_note']   = 'Sale Product to :'.$rel_cust['company_name'];
							$infopbk['company_id']	= $_SESSION['company_id'];
							 $insert=add_record('tbl_passbookentry', $infopbk, $dbcon);
							 
			/**Payment Reminder entry START***/
				if(!empty($POST['payment_reminder']) && $POST['payment_reminder']>0)
				{
					$remainder_date=addDayswithdate($info2['payment_date'],$POST['payment_reminder']);//($date,$days)
					$info_remainder['task_detail']		= $_POST['payment_reminder_desc'];
					$info_remainder['date']				= $remainder_date;
					
					$info_remainder['ref_id']			= $insertreceiptid;
					$info_remainder['ref_table']		= 'tbl_receipt';
				
					$info_remainder['user_id']			= $_SESSION['user_id'];
					$info_remainder['company_id']		= $_SESSION['company_id'];
					$inserinvoiceid=add_record('todo_mst', $info_remainder, $dbcon);
				}
			/**Payment Reminder entry END***/		
							
					if(isset($POST['save_print']))
					{
						$arr['msg']="1";
						$arr['eid']=$insertreceiptid;
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
		}
		/*else if(strtolower($POST['mode']) == "payment") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
						$query_from = $dbcon->query("UPDATE  tbl_invoice SET paid_amount = paid_amount + ".  $POST['paid_amount']." WHERE invoice_id = ".$POST['eid']);
							$info2['receipt_no']	=	'rec/'.$POST['receiptid'];
							$infopbk['paymentmodeid']	= $info2['paymentmodeid']	=	$POST['paymentmodeid'];
                            //Get Case ACCOUNT
			if($POST['paymentmodeid']==1)
			{	
				$qry="select acc_id,acc_type from account_mst where acc_type=1 and acc_status=0 and company_id=".$_SESSION['company_id'];
				$rel_acc=mysqli_fetch_assoc($dbcon->query($qry));	
				$acc_id=$rel_acc['acc_id'];
			}
			else
			{
					$acc_id		=	$POST['pur_acc_id'];
			}
							
							$infopbk['acc_id']	        = $info2['acc_id']		=	$acc_id;
							$infopbk['reference_no']    = $info2['cheque_dtl']	=	$POST['cheque_dtl'];
							$infopbk['bill_no_ref']     = $info2['invoice_id']	= 	$POST['eid'];
							$infopbk['amount']          = $info2['paid_amount']	=	$POST['paid_amount'];
							$infopbk['entry_date']      = $info2['payment_date']	=	date("Y-m-d",strtotime($POST['payment_date']));
                            $infopbk['reference_date']  = $info2['ref_date']	    = date("Y-m-d",strtotime($POST['ref_date']));
							$infopbk['cdate']           = $info2['cdate']			= 	date("Y-m-d H:i:s");
							$infopbk['user_id']         = $info2['user_id']		= $_SESSION['user_id'];
							$info2['usertype_id']	= $_SESSION['user_type'];
							$info2['company_id']	= $_SESSION['company_id'];
							$insertreceiptid=add_record('tbl_receipt', $info2, $dbcon);
                            //Passbook Entry
                            $rs=$dbcon->query("SELECT invoice.cust_id,invoice_no,company_name FROM `tbl_invoice` as invoice left join tbl_customer as cust on cust.cust_id=invoice.cust_id  where invoice_id=".$info2['invoice_id']);
                            $rel=mysqli_fetch_assoc($rs);
                            $infopbk['customer_id']     =$rel['cust_id'];
                            $infopbk['typeid']          = '2';//Credit
                            $infopbk['trn_id']          = $insertreceiptid;
                            $infopbk['trn_table']       = 'tbl_receipt';
                            $infopbk['passbook_note']   = 'Against Invoice :'.$rel['invoice_no'].'('.$rel['company_name'].')';
                            $infopbk['company_id']	= $_SESSION['company_id'];
							$insert=add_record('tbl_passbookentry', $infopbk, $dbcon);
					if(isset($POST['save_print']))
					{
						$arr['msg']="2";
						$arr['eid']=$insertreceiptid;
					}
					else
					{
						if($insertreceiptid)
						{	
							$arr['msg']="2";							
						}
						else
							$arr['msg']="0";
					}
			echo json_encode($arr);
				
			}
		}	*/	
		else if(strtolower($POST['mode']) == "delete") {
			$qry="select * from tbl_receipt where receipt_id=".$POST['eid'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));
				 $invoiceid=$rel['invoice_id'];
				$paidamount=$rel['paid_amount'];
				$query_from = $dbcon->query("UPDATE  tbl_invoice SET paid_amount = paid_amount - ".$paidamount ." WHERE invoice_id = ".$invoiceid);
			
			$info['status']		= 2;
			$updatetrancationid=update_record('tbl_receipt', $info,"receipt_id=".$POST['eid'] , $dbcon);				
			$updatepassid=update_record('tbl_passbookentry', $info,"trn_id=".$POST['eid']." and trn_table='tbl_receipt'" , $dbcon);			
			
			//Update Payment Reminder
			$informdr['status'] = 2;
			$updatermdrid=update_record('todo_mst', $informdr,"ref_id=".$POST['eid']." and ref_table='tbl_receipt'" , $dbcon);
			
			
			if($updatetrancationid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "load_data") {
			 if($POST['payment_type']==1)
			{
				getinvoiceno($dbcon,$POST['cust_id']);
			}
			else if($POST['payment_type']==2)
			{
				echo get_sales_customer_due_amount($dbcon,$POST['cust_id'],"1");
			}
			else
			{
				echo "0";
			}
		}	
		else if(strtolower($POST['mode']) == "load_totaldata") {
			
			$qry="select* from tbl_invoice where invoice_id=".$POST['invoice_id'];
			$total=mysqli_fetch_assoc($dbcon->query($qry));	
			echo json_encode($total);
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
?>