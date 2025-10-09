<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "generate_report_emp_ledger")
	{
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$user_id=$POST['user_id'];
		$usertype=$_SESSION['user_type'];
	
				$str .='
					
				  <table  class="display table table-bordered" id="data_list">
				  <thead>
					
					  <tr>
						  <th width="10%" style="text-align:center">Date</th>
						  <th width="43%" style="text-align:center">Transaction#</th>
						  <th width="10%" style="text-align:center">Debit Amount</th>
						  <th width="10%" style="text-align:center">Credit Amount</th>
						  <th width="12%" style="text-align:center">Balance</th>
					  </tr>
					  
				  </thead>
				  
				  <tbody>';
				  
					$query="select usr.user_name,exp_amt.quot_amt,pay_amt.pay_amount from users as usr
					
					left join 
						( select sum(inv.g_total) as quot_amt,quot_won_user_id,invoice_date from tbl_invoice as inv
						inner join tbl_quotation as quot on quot.quotation_id=inv.quotation_id
						where invoice_status=0 and invoice_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by quot_won_user_id ) as exp_amt on exp_amt.quot_won_user_id=usr.user_id
					
					left join 
						( select sum(paid_amt) as pay_amount,user_id,payment_date from tbl_quot_payment_trn where approve_status=1 and quot_paytrn_status=0 and payment_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by user_id ) as pay_amt on pay_amt.user_id=usr.user_id
						
						where usr.user_id='$user_id'
					";
					$rel=mysqli_fetch_assoc($dbcon->query($query));
					
					$balance=($rel['pay_amount'])-$rel['quot_amt'];
					//$balance=$rel['exp_amount'];
					$balancetype=$rel['balance_typeid'];
					 
					$str .='<tr>
					<td style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
					<td style="text-align:left">Opening Balance</td>
					<td style="text-align:center"></td>
					<td style="text-align:center"></td>';
					if($balance>0){
						$balancetype='CR';
					}
					else if($balance<0){
							$balancetype='DR';
					}
					
				$str .='
					  <td style="text-align:center">'.$balance.' '.$balancetype.'</td>
					</tr>';
			$qry='Select * from (
			(Select invoice_date as trn_date,inv.g_total as total,2 as typeid,invoice_no as trn_data from tbl_invoice as inv 
			inner join tbl_quotation as quot on quot.quotation_id=inv.quotation_id
			where inv.invoice_status=0 and quot.quot_won_user_id='.$user_id.' and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'" order by invoice_date) 
			union  
			(Select payment_date as trn_date,paid_amt as total,1 as typeid,referenceno as trn_data from tbl_quot_payment_trn as p 
			where p.quot_paytrn_status=0 and approve_status=1 and p.user_id='.$user_id.' and p.payment_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and p.payment_date<="'.date('Y-m-d',strtotime($s_date[1])).'" order by payment_date)
			
			) as data';
			$result1=$dbcon->query($qry);
			$i=1;
				
			if(mysqli_num_rows($result1)>0)
				{
					
					while($re=mysqli_fetch_assoc($result1))
					{
						if($re['typeid']=="1")
						{
							$credit=$re['total'];
							$debit='';
							$transaction="Invoice - ".$re['trn_data'];
							$op_balance+=$re['total'];
							//$back_color='style=background-color:#98FB98 !important;';
							$type="<strong style='color:green'>CR</strong>";
							
						}
						else
						{
							$debit=$re['total'];
							$credit='';
							$transaction="Payment - ".$re['trn_data'];
							$op_balance-=$re['total'];
							//$back_color='style=background-color:#FA8072 !important;';
							$type="<strong style='color:red'>DR</span>";
						}
						
						$balancetype='';
						$str.='<tr>
						  <td '.$back_color.'>'.date('d/m/Y',strtotime($re["trn_date"])).'</td>
						  <td '.$back_color.'>'.$transaction.'</td>
						  <td '.$back_color.'>'.$debit.'</td>
						  <td '.$back_color.'>'.$credit.'</td>
						  <td '.$back_color.'>'.$op_balance.' '.$type.'</td>						  
						  ';
						 
						  $str.='
						 
				 		</tr>';				
						$i++;
						
					}
					
				}
				else
				{
					$str .='<tr>
							<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>';
				  
			echo $str;
		
		
	}
	
?>