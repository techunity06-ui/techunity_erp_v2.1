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
		else if(strtolower($POST['mode']) == "add") {
				
			$info['bill_id']=$POST['comp_id'];
			$info['bill_type']='complaint';
			$info['paid_amount']=$POST['paid_amount'];
			$info['emp_id']=$POST['emp_id'];
			$info['pay_date']=date("Y-m-d");
			$info['pay_mode']=$POST['paymentmodeid'];
			$insertidptrn=add_record("complain_payment_trn",$info,$dbcon);
			
			$paid_payment=get_complain_payment_pending($dbcon,$POST['comp_id']);
				
			if($paid_payment<=0)
			{
			
				$q = $dbcon -> query("update payment_mst set mst_status='0' where comp_id='".$POST['comp_id']."'");
				$r = $dbcon -> query("update payment_trn set pay_status='0' where bill_id='".$POST['comp_id']."'");
				$s = $dbcon -> query("update tbl_complaint set pay_status='1' where complaint_id='".$POST['comp_id']."'");
			}
				
			$arr['msg']="1";
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
		else if(strtolower($POST['mode']) == "get_complain") {
			
			$customer=$POST['customer'];
			
			$complain_id=$POST['complain_id'];
			
			echo get_customer_complain($dbcon,$customer,$complain_id);
			//echo get_customer_complain_closed($dbcon,$customer,$complain_id);
		}
		else if(strtolower($POST['mode']) == "get_complain_pending_payment") {
			
			$complaint=$POST['complaint'];
			echo get_complain_payment_pending($dbcon,$complaint);
			//echo $complaint;
			
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