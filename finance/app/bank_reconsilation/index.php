<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");
//include("../../config/image.php");
error_reporting(E_ALL);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "bank_reco_all_entries_report") 
{
	$str='';
	$s_date=explode(' - ',$POST['date']);

	if(!empty($POST['b_l_id'])){
		$where = ' and l.l_id="'.$POST['b_l_id'].'" ';
	}else{
		$where = '';
	}

	$qry='select l.l_name,payment.* from tbl_general_book as payment left join tbl_ledger as l on payment.ledger_id=l.l_id where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' and payment.ref_date >= "'.date('Y-m-d',strtotime($s_date[0])).'" and payment.ref_date <= "'.date('Y-m-d',strtotime($s_date[1])).'" and payment.table_name IN ("tbl_receipt","tbl_receipt_payment_trn","tbl_contra_trn","tbl_journal_trn") and l_form="bank_form" '.$where.'  ORDER BY payment.table_id';
//echo $qry;exit;
	$result1=$dbcon->query($qry);
	$i=1;
				
	if(mysqli_num_rows($result1)>0)
	{
		$total=0;
		while($re=mysqli_fetch_assoc($result1))
		{
			$balance='';
			$str.='<tr>
			  <td data-label="SR. NO." style="text-align:center">'.$i.'</td>
			  	<td>'.date("d-m-Y",strtotime($re['ref_date'])).'</td>';
			  	
					
			$ref_no=explode('=',load_led_no($dbcon,$re['table_name'],$re['table_id']));
			if($re['table_name']=="tbl_receipt")
			{
				$r_o=$dbcon->query("SELECT l.l_name FROM `tbl_receipt` as j left join tbl_receipt_payment_trn as jt on j.receipt_id=jt.receipt_id join tbl_ledger as l on l.l_id=jt.ledger_id and l.l_form!='bank_form' where j.receipt_id=".$ref_no[1]." ");
				$r_e=brp_mysqli_fetch_assoc($r_o);					
				if($ref_no[2] == 1){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td><td>'.$r_e['l_name'].'</td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td><td>'.$r_e['l_name'].'</td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
			}
			else if($re['table_name']=="tbl_receipt_payment_trn"){
				
				$r_o=$dbcon->query("SELECT l.l_name FROM `tbl_receipt` as j left join tbl_receipt_payment_trn as jt on j.receipt_id=jt.receipt_id join tbl_ledger as l on l.l_id=jt.ledger_id and l.l_form!='bank_form' where j.receipt_id=".$ref_no[1]." ");
				$r_e=brp_mysqli_fetch_assoc($r_o);

				if($ref_no[2] == 1){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td><td>'.$r_e['l_name'].'</td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td><td>'.$r_e['l_name'].'</td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
				
			}
			else if($re['table_name']=="tbl_journal_trn"){
				
				$r_o=$dbcon->query("SELECT l.l_name FROM `tbl_journal` as j left join tbl_journal_trn as jt on j.journal_id=jt.journal_id join tbl_ledger as l on l.l_id=jt.ledger_id and l.l_form!='bank_form' where j.journal_id=".$ref_no[1]." ");
				$r_e=brp_mysqli_fetch_assoc($r_o);

				$str .= '<td>Journal</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'journal_entry_edit/'.$ref_no[1].'">Journal No : '.$ref_no[0].'</a></td><td>'.$r_e['l_name'].'</td>';	
			}
			else if($re['table_name']=="tbl_contra_trn"){
				$r_o=$dbcon->query("SELECT l.l_name FROM `tbl_contra` as j left join tbl_contra_trn as jt on j.contra_id=jt.contra_id join tbl_ledger as l on l.l_id=jt.ledger_id and l.l_form!='bank_form' where j.contra_id=".$ref_no[1]." ");
				$r_e=brp_mysqli_fetch_assoc($r_o);

				$str .= '<td>Contra</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'contra_entry_edit/'.$ref_no[1].'">Contra No : '.$ref_no[0].'</a></td><td>-</td>';
			}

			else{
				$str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
			}

			$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$re['l_name'].'</td>';
			
			
			if($re['entry_type']==2){
			 $str.='
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.$re['amount'].'</td>
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
				$balance+=$re['amount'];
				
			}else{
				$str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
				<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.$re['amount'].'</td>';
				$balance-=$re['amount'];
			}
			
			$str.='<td style="text-align:center;">'.$re['cleared_date'].'</td>';

			if($balance<0){
			 $str.='
			 <td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.abs($balance).' CR</td>';
			}else if($balance>0){
				$str.='
				<td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.abs($balance).' DR</td>';
			}else{
				$str.='
			 <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
			}
			if($re['cleared_status'] == 0){
				$str.='<td><button class="btn btn-xs btn-warning" data-original-title="" data-toggle="tooltip" data-placement="top" onClick="clear_entry('.$re['general_book_id'].')">Clear Entry</button></td></tr>';
			}else{
				$str.='<td><button class="btn btn-xs btn-success" data-original-title="" data-toggle="tooltip" data-placement="top" >Cleared</button></td></tr>';
			}
					
			$i++;
			
		}
		
	}
	else
	{
		$str .='<tr>
				<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
				
	} 
			 
	echo $str;

}else if(strtolower($POST['mode']) == "bank_reco_cleared_entries_report") 
{
	$str='';
	$s_date=explode(' - ',$POST['date']);

	$qry='select l.l_name,payment.* from tbl_general_book as payment left join tbl_ledger as l on payment.ledger_id=l.l_id where payment.cleared_status=1 and payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' and payment.ref_date >= "'.date('Y-m-d',strtotime($s_date[0])).'" and payment.ref_date <= "'.date('Y-m-d',strtotime($s_date[1])).'" and payment.table_name IN ("tbl_receipt","tbl_receipt_payment_trn","tbl_contra_trn","tbl_journal_trn")  and l_form!="bank_form" ORDER BY payment.table_id';

	$result1=$dbcon->query($qry);
	$i=1;
				
	if(mysqli_num_rows($result1)>0)
	{
		$total=0;
		while($re=mysqli_fetch_assoc($result1))
		{
			$balance='';
			$str.='<tr>
			  <td data-label="SR. NO." style="text-align:center">'.$i.'</td>
			  	<td>'.$re['ref_date'].'</td>
			  	
			  	';
			  	
					
			$ref_no=explode('=',load_led_no($dbcon,$re['table_name'],$re['table_id']));
			if($re['table_name']=="tbl_receipt")
			{					
				if($ref_no[2] == 1){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
			}
			else if($re['table_name']=="tbl_receipt_payment_trn"){
				
				if($ref_no[2] == 1){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
				
			}
			else if($re['table_name']=="tbl_journal_trn"){
				
				$str .= '<td>Journal</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Journal No : '.$ref_no[0].'</a></td>';	
			}
			else if($re['table_name']=="tbl_contra_trn"){
				$str .= '<td>Contra</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Contra No : '.$ref_no[0].'</a></td>';
			}

			else{
				$str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
			}

			$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$re['l_name'].'</td>';
			
			
			if($re['entry_type']==2){
			 $str.='
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.$re['amount'].'</td>
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
				$balance+=$re['amount'];
				
			}else{
				$str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
				<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.$re['amount'].'</td>';
				$balance-=$re['amount'];
			}
			
			$str.='<td style="text-align:center;">'.$re['cleared_date'].'</td>';

			if($balance<0){
			 $str.='
			 <td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.abs($balance).' CR</td>';
			}else if($balance>0){
				$str.='
				<td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.abs($balance).' DR</td>';
			}else{
				$str.='
			 <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
			}

			$str.='</tr>';
					
			$i++;	
		}
		
	}
	else
	{
		
		$str .='<tr>
				<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
				
	} 
			 
	echo $str;

}
else if(strtolower($POST['mode']) == "bank_reco_uncleared_entries_report") 
{
	$str='';
	$s_date=explode(' - ',$POST['date']);

	$qry='select l.l_name,payment.* from tbl_general_book as payment left join tbl_ledger as l on payment.ledger_id=l.l_id where payment.cleared_status=0 and payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' and payment.ref_date >= "'.date('Y-m-d',strtotime($s_date[0])).'" and payment.ref_date <= "'.date('Y-m-d',strtotime($s_date[1])).'" and payment.table_name IN ("tbl_receipt","tbl_receipt_payment_trn","tbl_contra_trn","tbl_journal_trn") and l_form!="bank_form" ORDER BY payment.table_id';

	$result1=$dbcon->query($qry);
	$i=1;
				
	if(mysqli_num_rows($result1)>0)
	{
		$total=0;
		while($re=mysqli_fetch_assoc($result1))
		{
			$balance=intval('');
			$str.='<tr>
			  <td data-label="SR. NO." style="text-align:center">'.$i.'</td>
			  	<td>'.$re['ref_date'].'</td>';
			  	
					
			$ref_no=explode('=',load_led_no($dbcon,$re['table_name'],$re['table_id']));
			if($re['table_name']=="tbl_receipt")
			{					
				if($ref_no[2] == 1){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
			}
			else if($re['table_name']=="tbl_receipt_payment_trn"){
				
				if($ref_no[2] == 1){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
				
			}
			else if($re['table_name']=="tbl_journal_trn"){
				
				$str .= '<td>Journal</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Journal No : '.$ref_no[0].'</a></td>';	
			}
			else if($re['table_name']=="tbl_contra_trn"){
				$str .= '<td>Contra</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Contra No : '.$ref_no[0].'</a></td>';
			}

			else{
				$str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
			}

			$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$re['l_name'].'</td>';
			
			
			if($re['entry_type']==2){
			 $str.='
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.$re['amount'].'</td>
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
				$balance+=$re['amount'];
				
			}else{
				$str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
				<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.$re['amount'].'</td>';
				$balance-=intval($re['amount']);
			}
			
			$str.='<td style="text-align:center;">'.$re['cleared_date'].'</td>';

			if($balance<0){
			 $str.='
			 <td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.abs($balance).' CR</td>';
			}else if($balance>0){
				$str.='
				<td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.abs($balance).' DR</td>';
			}else{
				$str.='
			 <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
			}

			$str.='<td><button class="btn btn-xs btn-warning" data-original-title="" data-toggle="tooltip" data-placement="top" onClick="clear_entry('.$re['general_book_id'].')">Clear Entry</button></td></tr>';

			//$str.='</tr>';
					
			$i++;	
		}
		
	}
	else
	{
		$str .='<tr>
				<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
				
	} 
			 
	echo $str;

}
else if(strtolower($POST['mode']) == "update_clear_entry") 
{
	$clear_full_voucher = $POST['clear_full_voucher'];
	$info_gen['cleared_date'] = date("Y-m-d",strtotime($POST['clear_date']));
	$info_gen['cleared_status'] = 1;

	if($clear_full_voucher==1)
	{
		
		$updateid=clear_bank_entry($dbcon,$POST['general_book_id']);
	}
	else
	{
		$updateid=update_record('tbl_general_book',$info_gen,"general_book_id= ".$_POST['general_book_id']." " , $dbcon);	
	}
	

		
	if($updateid) {	
		echo 1;							
	}
	else{
		echo 0;
	}
}
