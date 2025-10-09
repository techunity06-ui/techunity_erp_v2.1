<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../config/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
    if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {*/
	//print_r($_POST);
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	}
	else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['type']) == "fetch") {
	    //for reports & filters
	    $_SESSION['ch_sdate'] = "";
	    $_SESSION['ch_edate'] = "";
	    $_SESSION['ch_payee'] = "";
	    $_SESSION['ch_acc'] = "";
	    $_SESSION['ch_amnt'] = "";
		
		$appData = array();
		$i=1;
		$aColumns = array('cheque_id', 'l_name', 'acc_name', 'bank_name', 'cheque_number', 'cheque_acc', 'acc_number', 'cheque_date', 'cheque_payee','cheque_amount', 'cheque_note', 'cheque_mode', 'cheque_morethen','paytype', 'cheque_iscancel', 'cheque_tmst', 'cheque_of');
		$sIndexColumn = "cheque_id";
		$sTable = "coro_cheques";
		
		$isWhere = array("cheque_of = '$_SESSION[company_id]'");
		
		if(isset($POST['payee']) && $POST['payee'] != -9 && $POST['payee'] != 0) {
		    array_push($isWhere,"`cheque_payee` = '$POST[payee]'");
		    $_SESSION['ch_payee'] = $POST['payee'];
		}
		
		if(isset($POST['account']) && $POST['account'] != -9 && $POST['account'] != 0) {
		    array_push($isWhere,"`cheque_acc` = '$POST[account]'");
		    $_SESSION['ch_acc'] = $POST['account'];
		}
		
		if(isset($POST['date']) && $POST['date'] != -9 && $POST['date'] != 0) {
		    $temp = explode(",",$POST['date']);
		    array_push($isWhere,"(`cheque_date` >= '$temp[0]' AND `cheque_date` <= '$temp[1]')");
		    $_SESSION['ch_sdate'] = $temp[0];
		    $_SESSION['ch_edate'] = $temp[1];
		}
		
		if(isset($POST['amount']) && $POST['amount'] != -9 && $POST['amount'] != 0) {
		    array_push($isWhere,"`cheque_amount` = '$POST[amount]'");
		    $_SESSION['ch_amnt'] = $POST['amount'];
		}
		
		
		$isJOIN = array(
		    "INNER JOIN `account_mst` as acc ON acc.acc_id = `cheque_acc`",
		    "INNER JOIN `bank_mst` as bank ON bank.bankid= acc.bankid",
		    "Left JOIN `tbl_customer` as cust ON `cust_id` = `cheque_payee`"
		);
		
		include('pagging.php');
		$appData = array();
		$id=1;
		
		foreach($sqlReturn as $row) {
			$row_data = array();
			if($row['cheque_iscancel'] == 1) {
			    $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">'.$row['sr'].'</a></strike>';
			    $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">'.date("d-m-Y",strtotime($row['cheque_date'])).'</a></strike>';
			    $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">'.str_pad($row['cheque_number'], 6, '0', STR_PAD_LEFT).'</a></strike>';
				if($row['paytype']=="Acc"){
		$qary = $dbcon -> query("SELECT * from account_mst where acc_id=".$row['cheque_payee']);
		$ro = $qary->fetch_assoc();
		   if($row['cheque_payee']=="1"){
			    $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">SELF</a></strike>';
		   }else{
			   $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">'.$ro['acc_name'].'</a></strike>';
		   }
				}else{
					$row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">'.$row['company_name'].'</a></strike>';
				}
			    $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="'.$row['bank_name'].' ('.$row['acc_number'].')">'.$row['acc_name'].'</a></strike>';
			    $row_data[] = '<strike><a href="#" data-toggle="tooltip" data-placement="top" data-original-title="CANCELLED" style="color:#ff0000;">'.indian_number($row['cheque_amount'],2).'</a></strike>';
				/*$row_data[] = $row['sr'];
			    $row_data[] = date("d/m/Y",strtotime($row['cheque_date']));
			    $row_data[] = str_pad($row['cheque_number'], 6, '0', STR_PAD_LEFT);
			    $row_data[] = $row['vender_name'];
			    $row_data[] = '<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="'.$row['bank_name'].' ('.$row['acc_number'].')">'.$row['acc_name'].'</a>';
			    $row_data[] = '<a title="Reprint" target="_blank" href="'.DOMAIN.$_SESSION['print_page'].'?id='.$row['cheque_id'].'">'.indian_number($row['cheque_amount'],2).'</a>';*/
			    $row_data[] = "Cancelled";
				$row_data[] =$row['cheque_amount'];
			}
			else {
			    $row_data[] = $row['sr'];
			    $row_data[] = date("d/m/Y",strtotime($row['cheque_date']));
			    $row_data[] = str_pad($row['cheque_number'], 6, '0', STR_PAD_LEFT);
				if($row['paytype']=="Acc"){
		$qary = $dbcon -> query("SELECT * from account_mst where acc_id=".$row['cheque_payee']);
		$ro = $qary->fetch_assoc();
		   if($row['cheque_payee']=="1"){
			    $row_data[] = 'SELF';
		   }else{
			 
			   $row_data[] = $ro['acc_name'];
		   }
				}else{
					$row_data[] = $row['company_name'];
				}
			    
			    $row_data[] = '<a href="#" data-toggle="tooltip" data-placement="top" data-original-title="'.$row['bank_name'].' ('.$row['acc_number'].')">'.$row['acc_name'].'</a>';
			    $row_data[] = '<a title="Reprint" target="_blank" href="'.DOMAIN_CHEQUE.$_SESSION['print_page'].'?id='.$row['cheque_id'].'">'.indian_number($row['cheque_amount'],2).'</a>';
			   
			    $tool = '<a class="btn btn-xs btn-primary" title="Reprint" target="_blank" href="'.DOMAIN_CHEQUE.$_SESSION['print_page'].'?id='.$row['cheque_id'].'"><i class="fa fa-print"></i></a> <button class="btn btn-xs btn-warning" data-original-title="Cancel Cheque" data-toggle="tooltip" data-placement="top" onClick="cancel_cheque('.$row['cheque_id'].')"><i class="fa fa-ban"></i></button>';
			
			    if(trim($row['cheque_note']) != "") {
				$tool .= '<button class="btn btn-xs btn-primary" data-original-title="Note 2: '.$row['cheque_note'].'" data-toggle="tooltip" data-placement="left" ><i class="fa fa-file"></i></button>';
			    }
			    
			    $tool .= '<button class="btn btn-xs btn-default" data-original-title="Print Voucher" data-toggle="tooltip" data-placement="top" onClick="print_voucher('.$row['cheque_id'].')" ><i class="fa fa-print"></i></button>';
			    
			    $row_data[] = $tool;
				$row_data[] =$row['cheque_amount'];
			}
			
			
			//<button class="btn btn-xs btn-default" data-original-title="Added On '.date("d-m-Y g:i a",strtotime($row['cust_tmst'])).'" data-toggle="tooltip" data-placement="left"><i class="fa fa-clock-o"></i></button>/
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['type']) == "cancelcheque") {
	   $query = $dbcon->query("UPDATE `coro_cheques` SET `cheque_iscancel` = 1 WHERE `cheque_id` = '$POST[id]' AND `cheque_of` = '$_SESSION[user_id]'");
			$query="select purchase_payid,tansactionid,cheque_id,directentryid from tbl_payment_cheque_generate where  cheque_id=".$POST['id'];
			$rs_cheque=($dbcon->query($query));
			if(mysqli_num_rows($rs_cheque)>0)//for purchase Cheque Cancel
			{
				$rel_cheque=mysqli_fetch_assoc($rs_cheque);
				$info_st['generat_status']=2;
				/*Update Generate Cheque */
				update_record('tbl_payment_cheque_generate', $info_st,"cheque_id=".$rel_cheque['cheque_id'], $dbcon);
				$info['status']		= 2;
				if($rel_cheque['purchase_payid']!=0)
				{
				/*Update Purchase Receipt from Cancel*/
				$updatetrancationid=update_record('tbl_purchasereceipt', $info,"purchasereceipt_id=".$rel_cheque['purchase_payid'] , $dbcon);
				/*Update Passbook Entry from Cancel*/
				$updatetrancationid=update_record('tbl_passbookentry', $info,"trn_id=".$rel_cheque['purchase_payid']." and trn_table='tbl_purchasereceipt'" , $dbcon);	
				/*Update Purchase Amount*/
				$qry="select * from tbl_purchasereceipt where purchasereceipt_id=".$rel_cheque['purchase_payid'];
				$rel=mysqli_fetch_assoc($dbcon->query($qry));
				$purchasebillid=$rel['purchasebill_id'];
				$paidamount=$rel['paid_amount'];
				$query_from = $dbcon->query("UPDATE  tbl_pono SET paid_amount = paid_amount - ".$paidamount ." WHERE po_id = ".$purchasebillid);
				}
				else if($rel_cheque['directentryid']!=0)
				{
				/*Update Bank Transaction from Cancel*/
				update_record('tbl_direct_entry', $info,"direct_entryid=".$rel_cheque['directentryid'] , $dbcon);
				$updatetrancationid=update_record('tbl_passbookentry', $info,"trn_id=".$rel_cheque['directentryid']." and trn_table='tbl_direct_entry'" , $dbcon);	
				}
			}	
			/*$query="select entryid from tbl_passbookentry where trn_id=".$POST['id']." and trn_table='coro_cheques'";
			$rs_passbook=($dbcon->query($query));
			if(mysqli_num_rows($rs_passbook)>0)//for purchase Cheque Cancel
			{	
				$rel_cheque=mysqli_fetch_assoc($rs_passbook);
				$info['status']	= 2;
				/*Update Passbook Entry from Cancel
				$updatetrancationid=update_record('tbl_passbookentry', $info,"entryid=".$rel_cheque['entryid'], $dbcon);	
			}*/
	    if($query) {
		echo "1";
	    }
	    else {
		echo "0";
	    }
	}
   /* }
    else {
        die("Error - 2");
    }
}
else {
    die("Error - 1");
}*/
?>