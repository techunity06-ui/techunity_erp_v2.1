<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
//include("../../config/image.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "cost_center_report") 
{
	$s_date=explode(' - ',$POST['date']);

	// $qry='select l.l_name,payment.* from tbl_general_book as payment left join tbl_ledger as l on payment.ledger_id=l.l_id where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' and payment.ref_date >= "'.date('Y-m-d',strtotime($s_date[0])).'" and payment.ref_date <= "'.date('Y-m-d',strtotime($s_date[1])).'" and payment.table_name IN ("tbl_receipt","tbl_receipt_payment_trn","tbl_contra_trn","tbl_journal_trn") ORDER BY payment.ref_date';
	if($_POST['costid']){
		$where = 'and cct.costcenter_id='.$_POST['costid'].' ';
	}else{
		$where = '';
	}

	$qry='SELECT cc.cost_center_name, cct.* FROM `tbl_cost_center_transaction` as cct left join tbl_cost_center as cc on cct.costcenter_id=cc.cost_center_id where cct.costcenter_status=0 and cct.isdelete=0 and cct.company_id='.$_SESSION['company_id'].' and cct.cdate >= "'.date('Y-m-d',strtotime($s_date[0])).'" and cct.cdate <= "'.date('Y-m-d',strtotime($s_date[1])).' " '.$where.'
';

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
			  	<td>'.date('d-m-Y',strtotime($re['cdate'])).'</td>
			  	
			  	';
			$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$re['cost_center_name'].'</td>';  	
			//$str .= '<td>-</td>';		
			$ref_no=explode('=',load_led_no($dbcon,$re['cost_center_table'],$re['cost_center_table_id']));
			if($re['cost_center_table']=="tbl_receipt")
			{					
				if($ref_no[2] == 1){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
			}
			else if($re['cost_center_table']=="tbl_receipt_payment_trn"){
				
				if($ref_no[2] == 1){
					$str .= '<td>Reciept</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
				}else if($ref_no[2] == 2){
					$str .= '<td>Payment</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
				}else{
					$str .= '<td>-</td><td>-</td>';
				}
				
			}
			else if($re['cost_center_table']=="tbl_invoice"){
				
				$str .= '<td>Invoice</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Invoice No : '.$ref_no[0].'</a></td>';	
			}
			else if($re['cost_center_table']=="tbl_sale_return"){
				
				$str .= '<td>Credit Note</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'salereturnedit/'.$ref_no[1].'">Credit Note No : '.$ref_no[0].'</a></td>';	
			}
			else if($re['cost_center_table']=="tbl_debitnote"){
				
				$str .= '<td>Debit Note</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'debitnoteedit/'.$ref_no[1].'">Debit Note No : '.$ref_no[0].'</a></td>';	
			}
			else if($re['cost_center_table']=="tbl_pono"){
				
				$str .= '<td>Purchase</td><td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$ref_no[1].'">Purchase No : '.$ref_no[0].'</a></td>';	
			}
			else{
				$str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
			}

			
			
			
			if($re['costcenter_entry_type']==2){
			 $str.='
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.$re['costcenter_amount'].'</td>
			  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
				$balance+=$re['costcenter_amount'];
				
			}else{
				$str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
				<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.$re['costcenter_amount'].'</td>';
				$balance-=$re['costcenter_amount'];
			}
			
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
