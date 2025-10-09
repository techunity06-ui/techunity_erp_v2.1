<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
include("../../config/image.php");
$image = new SimpleImage();
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
		
		if(strtolower($POST['mode']) == "generate_report") {
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
			$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
				$str .='
					<table  width="100%"   class="display table table-bordered table-striped">
					</table>
				  <table  class="display table table-bordered" id="data_list">
				  <thead>
					<tr id="logo" class="logo noborder" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr>
						<td colspan="2" class="noborder"><strong>Party Ledger </strong></td>
						<td colspan="2" class="noborder" style="text-align:center"><strong>	Name:'.$cust_rel['company_name'].'
						</strong></td>
						<td colspan="3" class="noborder" style="text-align:right">Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
				
					</tr>
					
				  <tr>
					  <th width="5%" style="text-align:center">Sr. NO.</th>
					  <th width="10%" style="text-align:center">Date</th>
					  <th width="10%" style="text-align:center">Type</th>
					  <th width="43%" style="text-align:center">Transaction#</th>
					   <th width="10%" style="text-align:center">Debit Amount</th>
					   <th width="10%" style="text-align:center">Credit Amount</th>
					   <th width="12%" style="text-align:center">Balance</th>
					 
				</tr>
				</thead>
				 <tbody>';
		  $query="select opening_balance,balance_typeid,debitinvoice_amt,debitincome_amt from tbl_customer as cust 
		left join 
		( select sum(g_total) as debitinvoice_amt,invoice.cust_id from tbl_invoice as invoice where invoice_status=0 and invoice.company_id=".$_SESSION['company_id']." and invoice_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by invoice.cust_id ) as debitinvoice on debitinvoice.cust_id=cust.cust_id

		
		left join (select sum(g_total) as debitincome_amt,income.customerid from income_mst as income where mst_status=0 and income.company_id=".$_SESSION['company_id']." and income_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by income.customerid ) as debitincome on debitincome.customerid=cust.cust_id
		
		left join (
		SELECT sum(v_trn.amount) as debit_voucher_amt,v_trn.partyid FROM `account_voucher_trn` as v_trn inner join account_voucher_mst as v_mst on v_trn.voucher_mstid=v_mst.voucher_mstid where v_trn.type=1 and  v_trn.trn_status=0  and v_mst.company_id=".$_SESSION['company_id']." group by v_trn.partyid
		)as debitvoucher on debitvoucher.partyid=cust.cust_id

		where cust.cust_id=".$POST['cust_id'];
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		
		$op_balance=($rel['balance_typeid']=="1"?(-$rel['opening_balance']):$rel['opening_balance']);//1credit,2debit
		
		 $balance=($op_balance+$rel['debitinvoice_amt']+$rel['debitincome_amt']+$rel['debit_voucher_amt']);
		 $balancetype='';
		$str .='<tr>
					<td style="text-align:center"></td>
					<td style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
					<td style="text-align:left" colspan="2">Opening Balance</td>
					<td style="text-align:center"> </td>
					<td style="text-align:center"> </td>';
					if($balance>0)
					{
						$balancetype='DR';
					}
					else if($balance<0)
					{
							$balancetype='CR';
					}
					
					$str .='
					  <td style="text-align:center">'.abs($balance).' '.$balancetype.'</td>
					</tr>';
			$qry='Select * from (
			(Select invoice_date,invoice_no,g_total,1 as typeid,"invoice" as type_name,invoice_id from tbl_invoice as invoice where invoice_status=0 and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and invoice.company_id='.$_SESSION['company_id'].' and invoice.cust_id='.$POST['cust_id'].' order by invoice_date) 
			
			) as data order by invoice_date,typeid';
			$result1=$dbcon->query($qry);
			$i=1;
				
			if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($re=mysqli_fetch_assoc($result1))
					{
						$balancetype='';
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>
						  <td style="text-align:center">'.date('d/m/Y',strtotime($re["invoice_date"])).'</td>
						   <td style="text-align:center">'.ucwords($re["type_name"]).'</td>
						  ';	
						//Transaction# col						  
						if($re['typeid']=="1" || $re['typeid']=="2" || $re['typeid']=="5" || $re['typeid']=="6") //invoice,income,purchase,expense
						{	
							$str .='<td style="text-align:left">Bill No : '.$re["invoice_no"].'</td>';
						}
						else if($re['typeid']=="3" || $re['typeid']=="4")//payment receipt
						{	
							$str .='<td style="text-align:left">'.ucwords($re["invoice_id"]).' : '.$re["invoice_no"].' ('.$re["payment_mode"].') </td>';
							
						}
						else if($re['typeid']=="7")//voucher
						{	
							$str .='<td style="text-align:left">No. : '.$re["invoice_no"].' ('.$re["payment_mode"].') </td>';
							
						}
						//debitamount col
						if($re['typeid']=="1" || $re['typeid']=="2" || $re['typeid']=="3" || ($re['typeid']=="7" && $re['invoice_id']==1 ))
						 {
						 $str.='
						  <td style="text-align:center">'.$re['g_total'].'</td>';
							$balance+=$re['g_total'];							
						 } 
						
						 else
						 {
							$str.='
						  <td style="text-align:center"></td>';
						
						 }
						 //creditamount col
						 if($re['typeid']=="4" || $re['typeid']=="5" || $re['typeid']=="6" || ($re['typeid']=="7" && $re['invoice_id']==2 ))
						 {
						  $str.='<td style="text-align:center">'.$re['g_total'].'</td>';
							$balance-=$re['g_total'];
							 
						 }  
						else
						{
								$str.='
						  <td style="text-align:center"></td>';
						
						}
					if($balance>0)
					{
						$balancetype='DR';
					}
					else if($balance<0)
					{
							$balancetype='CR';
					}
						  $str.='
						  <td style="text-align:right">'.indian_number(abs($balance),2).' '.$balancetype.'</td>	 
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