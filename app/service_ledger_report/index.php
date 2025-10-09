<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "load_service_ledger")
	{
		$s_date=explode(' - ',$POST['date']);
		$start_date=date("Y-m-d",strtotime($s_date[0]));
		$end_date=date("Y-m-d",strtotime($s_date[1]));
		
		$cust_id=$POST['cust_id'];
		
		$whr='';
		$whr.=" ";
		
		$str='';
		$str.='<table class="display table table-bordered">
		  <thead>
			  <tr>
				  <th width="5%" style="text-align:center">Sr. NO.</th>
				  <th width="10%" style="text-align:center">Date</th>
				  <th width="50%" style="text-align:center">Description</th>
				  <th width="10%" style="text-align:center">Debit Amount</th>
				  <th width="10%" style="text-align:center">Credit Amount</th>
				  <th width="15%" style="text-align:center">Balance</th>
			  </tr>
		  </thead>
		  <tbody>';
		//Get Opening Balance Data
		$op_query="select cust.opn_balance,cust.balance_typeid,debit_inv_amt,credit_paid_amt from tbl_ledger as cust 
		left join 
		(select sum(g_total) as debit_inv_amt,invoice.cust_id from tbl_invoice as invoice where complaint_id!=0 and invoice_status=0 and invoice.company_id=".$_SESSION['company_id']." and invoice_date < '".date('Y-m-d',strtotime($start_date))."'  group by invoice.cust_id) as debitinvoice on debitinvoice.cust_id=cust.l_id
		left join 
		(select sum(amount) as credit_paid_amt,pay.partyid from payment_mst as pay where comp_id!=0 and mst_status=0 and pay.company_id=".$_SESSION['company_id']." and payment_date < '".date('Y-m-d',strtotime($start_date))."'  group by pay.partyid) as credit_pay on credit_pay.partyid=cust.l_id 
		where cust.l_id=".$cust_id;
		$op_rel=mysqli_fetch_assoc($dbcon->query($op_query));
		$op_balance=($op_rel['balance_typeid']=="1"?(-$op_rel['opn_balance']):$op_rel['opn_balance']);
		$balance=($op_balance+$op_rel['debit_inv_amt'])-$op_rel['credit_paid_amt'];
		$balancetype='';
		$str .='<tr>
				<td style="text-align:center"></td>
				<td style="text-align:center">'.date('d/m/Y',strtotime($start_date)).'</td> 
				<td style="text-align:left">Opening Balance</td>
				<td style="text-align:center"> </td>
				<td style="text-align:center"> </td>';
				if($balance>0) {
					$balancetype='DR';
				}
				else if($balance<0) {
						$balancetype='CR';
				}	
		$str .='
		  <td style="text-align:right">'.abs($balance).' '.$balancetype.'</td>
		</tr>';
		
		$query='Select * from ((Select invoice_date,invoice_no,g_total,1 as typeid from tbl_invoice as invoice where complaint_id!=0 and invoice_status=0 and invoice_date>="'.date('Y-m-d',strtotime($start_date)).'" and invoice_date<="'.date('Y-m-d',strtotime($end_date)).'" and invoice.company_id='.$_SESSION['company_id'].' and invoice.cust_id='.$cust_id.' order by invoice_date)			
		union 
		(select payment_date as invoice_date,concat(paymentno,"-",referenceno) as invoice_no,amount as g_total,2 as typeid from payment_mst as payment where payment.mst_status=0 and payment.company_id='.$_SESSION['company_id'].' and payment_date>="'.date('Y-m-d',strtotime($start_date)).'" and payment_date<="'.date('Y-m-d',strtotime($end_date)).'" and payment.partyid='.$cust_id.' )) as data order by invoice_date,typeid';
		$query_rs=($dbcon->query($query));
		$i=1;
		while($re=mysqli_fetch_assoc($query_rs))
		{
			$balancetype='';
			$str.='<tr>
			  <td style="text-align:center">'.$i.'</td>
			  <td style="text-align:center">'.date('d/m/Y',strtotime($re["invoice_date"])).'</td>';
				
			if($re['typeid']=="1") {	
				$str .='<td style="text-align:left">Bill No : '.$re["invoice_no"].'</td>';
			}
			else if($re['typeid']=="2") {	
				$str .='<td style="text-align:left">Payment No : '.$re["invoice_no"].'</td>';
			}
			
			if($re['typeid']=="1") {
				$str.='<td style="text-align:right">'.$re['g_total'].'</td>';
				$balance+=$re['g_total'];
			}
			else {
				$str.='<td style="text-align:right"></td>';
			}
			
			if($re['typeid']=="2") {
				$str.='<td style="text-align:right">'.$re['g_total'].'</td>';
				$balance-=$re['g_total'];
			}  
			else {
				$str.='<td style="text-align:right"></td>';
			}
				if($balance>0) {
					$balancetype='DR';
				}
				else if($balance<0) {
						$balancetype='CR';
				}
			$str.='
			  <td style="text-align:right">'.abs($balance).' '.$balancetype.'</td>	 
			</tr>';				
			$i++;
		}
		
		
		$str .='</tbody>				 
				  </table>';
				  
		echo $str;	
	}
?>