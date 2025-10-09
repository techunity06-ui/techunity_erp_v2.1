<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");
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
			$str='';
				$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$query1="select company_name,(Select sum(g_total) as total from tbl_invoice where cust_id=cust.cust_id and invoice_status=0) as total,(Select sum(paid_amount) as receive from  tbl_receipt where cust_id=cust.cust_id and status=0) as receive from tbl_customer as cust where  cust.cust_id=".$POST['comapny_id']." and cust.company_id=".$_SESSION['company_id'];
			 $rel1=mysqli_fetch_assoc($dbcon->query($query1));
				$str .='<div id="payment_detail">
					<center> 
					<span id="head_logo"><strong>'.$set_head['title'].'</strong></span></td>
				</center>
					<table class="display table table-bordered table-striped" width="100%" >
					<tr>
						<td><strong>Customer Statement</strong></td>
						<td>Commpany Name: <strong>'.$rel1['company_name'].'</strong></td>
						<td style="text-align:right"></td>
				
					</tr>
					<tr>
						<td>Total Payment   : <strong>'.indian_number($rel1['total']).'</strong></td>
						<td>Recived Paymnet :<strong>'.indian_number($rel1['receive']).'</strong></td>
						<td>Over Due Paymnet:<strong>'.indian_number($rel1['total']-$rel1['receive']).'</strong></label></td>
				
					</tr>
					<tr>
						<td colspan="3" style="text-align:center"><strong>Received Payment History</strong></td>
					
					</tr>
					
					</table>
				  <table  class="display table table-bordered table-striped" id="data_list">
				  <thead>  		
				  <tr>
					  <th width="2%" style="text-align:center">Sr. NO.</th>
					  <th width="15%" style="text-align:center">Payment Date</th>
					  <th width="15%" style="text-align:center">Payment Type</th>
					  <th width="25%" style="text-align:center">Bank Name</th>
					  <th width="15%" style="text-align:center">Cheque No</th>
					  <th width="15%" style="text-align:center">Amount</th>	 
				</tr>
				 
				 </thead>
				 <tbody>';
				
			   $qry='Select payment_date,paymentmode.payment_mode,bank.bank_name,acc.acc_name,acc.branch_name,receipt.paid_amount as receive,cheque_dtl from tbl_receipt as receipt  left join account_mst as acc on acc.acc_id=receipt.acc_id
left join bank_mst as bank on bank.bankid=acc.bankid left join tbl_payment_mode as paymentmode on paymentmode.paymentmodeid=receipt.paymentmodeid  left join tbl_customer as cust on cust.cust_id=receipt.cust_id where cust.cust_id='.$POST['comapny_id'].' and receipt.status=0 and   receipt.company_id='.$_SESSION['company_id'].'  order by payment_date ASC';
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($re=mysqli_fetch_assoc($result1))
					{	
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>
						  <td style="text-align:center">'.date('d/m/Y',strtotime($re["payment_date"])).'</td>
					  	  <td style="text-align:center">'.$re["payment_mode"].'</td>';
						  if($re['bankid']!="0")
						  {
						  $str.='
						  <td style="text-align:center">'.$re['bank_name'].' - '.$re["acc_name"].' </td>';
							}  
							else{
						 			$str.='<td style="text-align:center"> - </td>';
						  }
						  if(!empty($re['cheque_dtl']))
						  {
							$str .='<td style="text-align:center">'.$re['cheque_dtl'].'</td>';
						  }
						  else
						  {
						  $str .='<td style="text-align:center"> - </td>';
						  
						  }
						  $str .='<td style="text-align:center">'.indian_number($re['receive']).'</td>	 
				 		</tr>';				
						$i++;
						$total=$total+$re['receive'];
					}
					$str .="<tr>
							<td></td><td></td><td></td><td></td><td style='text-align:right;'>Total</td><td style='text-align:center'>".indian_number($total)."</td></tr>";
				}
				else
				{
					$str .='<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>
				  </div>';
				  
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