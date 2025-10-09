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
			$s_date=explode(' - ',$POST['date']);
			$td=3;$td1=5;
			if(!empty($POST['cust_id']))
			{
				$query_cust="select company_name from tbl_customer where cust_id=".$POST['cust_id'];
				$rel_cust=mysqli_fetch_assoc($dbcon->query($query_cust));	
				$td=2;$td1=4;
			}	
			$str='';
				$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$str .='<table  width="100%" class="display table table-bordered table-striped">
					<tr id="logo" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
						</table>
						
				  
				  <table  class="display table table-bordered table-striped" id="data_list">
				 	<tr>
						<td colspan="3" style="white-space:nowrap;"><strong>Invoice Overdue by days Report</strong>
						</td>
						<td colspan="2" style="text-align:center">';
						if(!empty($POST['cust_id']))
						{
						$str .='Name: <strong>'.$rel_cust['company_name'].'</strong>';
						}
						$str .='</td><td colspan="'.$td.'" style="text-align:right">Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> From <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
					<td colspan="2"><strong></strong></td>

					</tr>
				
					<tr>
					  <th width="2%" style="text-align:center">Sr. NO.</th>
					  <th width="15%" style="text-align:center">Invoice Type</th>
					  <th width="15%" style="text-align:center">Invoice No</th>
					  <th width="15%" style="text-align:center">Invoice Date</th>';
					  if(empty($POST['cust_id']))
					  {
						$str.='<th width="25%" style="text-align:center">Company Name</th>';
					  }
					  $str .='<th width="15%" style="text-align:center">Total Amount</th>
					  <th width="15%" style="text-align:center">Paid Amount</th>	 
					  <th width="15%" style="text-align:center">Due Amount</th>
					  <th width="15%" style="text-align:center">Due On</th>
					  <th width="15%" style="text-align:center">Overdue by Days</th>
				 </tr>
				 <tbody>';
				$where ='';
				if(!empty($POST['type_id']))
				{
					$where .=" and invoice.invoicetype_id=".$POST['type_id'];
				}
				if(!empty($POST['cust_id']))
				{
					$where .=" and invoice.cust_id=".$POST['cust_id'];
				
				}
			   $qry='Select invoice_no, invoice_date, cust.company_name,todo.date,todo.ref_table,invoice.g_total,(select IFNULL (SUM(rtrn.paid_amount),0) as amuount from tbl_receipt_trn as rtrn where  rtrn.status=0 and rtrn.invoice_id=invoice.invoice_id) as paidamo , invoice.invoicetype_id,invoice_type from tbl_invoice as invoice 
			   inner join tbl_customer as cust on invoice.cust_id=cust.cust_id 
			   inner join tbl_invoicetype as type on invoice.invoicetype_id=type.invoicetype_id
			   inner join todo_mst as todo on invoice.invoice_id=todo.ref_id and  todo.ref_table="tbl_invoice"
			   where invoice_status=0 and invoice.g_total > (select IFNULL(sum(rtrn.paid_amount),0) as amuount  from tbl_receipt_trn as rtrn where  rtrn.status=0 and rtrn.invoice_id=invoice.invoice_id) and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" AND invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where.' and invoice.company_id='.$_SESSION['company_id'].' group by invoice.invoice_id';
			  
			  /* $qry='Select invoice_no, invoice_date, cust.company_name, invoice.g_total, SUM(res_trn.paid_amount) as amuount , invoice.invoicetype_id,invoice_type from tbl_invoice as invoice inner join tbl_customer as cust on invoice.cust_id=cust.cust_id inner join tbl_invoicetype as type on invoice.invoicetype_id=type.invoicetype_id
			   left join tbl_receipt_trn as res_trn on res_trn.invoice_id=invoice.invoice_id
			   where invoice_status=0 and res_trn.status=0 and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" AND invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where.' and invoice.company_id='.$_SESSION['company_id'].' group by invoice.invoice_id';*/
			
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					while($re=mysqli_fetch_assoc($result1))
					{	
						$tamount=$re['g_total'];
						$due =$tamount-$re["paidamo"];
						//$days=strtotime("+1 day", strtotime($re["date"]));
						//Our "then" date.
$then = $re["date"];
 
//Convert it into a timestamp.
$then = strtotime($then);
 
//Get the current timestamp.
$now = time();
 
//Calculate the difference.
$difference = $now - $then;
 
//Convert seconds into days.
$days = floor($difference / (60*60*24) );
 

						
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>';		  	
							$str.= '
							<td style="text-align:center">'.$re["invoice_type"].'</td>
							<td style="text-align:center">'.$re["invoice_no"].'</td>';
						$str.='
						  <td style="text-align:center">'.date('d/m/Y',strtotime($re["invoice_date"])).'</td>';
						  if(empty($POST['cust_id']))
						  {
					     	$str.='<td style="text-align:left">'.$re["company_name"].'</td>';
						  }
						  $str .='<td style="text-align:right">'.indian_number($tamount).'</td>
						  <td style="text-align:right">'.indian_number($re["paidamo"]).'</td>
						 
						  
						  <td style="text-align:right">'.indian_number($due).'</td>
						  
						  <td style="text-align:right">'.date('d/m/Y',strtotime($re["date"])).'</td>';
						  if ($days > 0){
						 $str .=' <td style="text-align:right;white-space:nowrap;">'.$days.' Days</td>';	
						  }else {
							  $str .=' <td style="text-align:right;white-space:nowrap;"> 0 Days</td>'; 
						  }
				 		$str .='</tr>';				
						$i++;
						$total=$total+$tamount;
						$total_paid=$total_paid+$re["paidamo"];
						$total_due=$total_due+($tamount-$re["paidamo"]);
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='<tr>
						 <td colspan="'.$td1.'" style="text-align:right"> <strong>Total</strong></td>
						 <td style="text-align:right">
							<label><strong>'.indian_number($total).'</strong></label>
						</td>						
						<td style="text-align:right">
							<label><strong>'.indian_number($total_paid).'</strong></label></td>
						<td style="text-align:right">
							<label><strong>'.indian_number($total_due).'</strong></label>
						</td>
						<td style="text-align:right" colspan="2">
							<label><strong></strong></label>
						</td>
				   </tr>	
				  </tbody>				 
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