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
			$set="select * from tbl_setting";
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			 $str .='<table style="font-size:12px;display:none"  id="report_head" name="report_head" width="100%">
							<tr>
							<td style="text-align:center" colspan="6">
							<h2 style="margin-top:1px;margin-bottom:2px">
							<strong>'.$set_head['title'].'</strong></h2>
							<br>
							<strong>Plot No. 510, Phase IV, GIDC,VATVA,MEHMEDABAD ROAD,AHMEDABAD -382445<BR>
							Phone: +91-79-2583 5313/2589 0362, E-mail : archita@jupitercomtex.com</strong>
							</td>
							</tr>
							
						</table>
					<br>
					<br>';
					
				if(isset($POST['custid']))
				{
				 $qry_cust_detail='Select cust.company_name,SUM(invoice.g_total) as total,SUM(invoice.paid_amount) as paid_total,invoice_status  from tbl_invoice as invoice inner join  tbl_customer as cust on invoice.cust_id=cust.cust_id where invoice.cust_id='.$POST['custid'].' AND invoice_status=0';
				
				$rel_cust_detail=mysqli_fetch_assoc($dbcon->query($qry_cust_detail));
				}
				$str .='
					<table  width="100%"><tr>
						<td>Invoice Report</td>
						 
						<td colspan="4" style="text-align:center">Customer Name
						<label>:'.$rel_cust_detail["company_name"].'</label></td>
						<td style="text-align:right">Date
					<label>:'.date('d/m/Y').'</label></td>
				
					</tr>
						</table>
						
				  
				  <table  class="display table table-bordered table-striped" id="data_list">
				  <thead>  		
				  <tr>
					  <th width="2%" style="text-align:center">Sr. NO.</th>
					  <th width="19%" style="text-align:center">Invoice No</th>
					  <th width="19%" style="text-align:center">Invoice Date</th>
					  <th width="19%" style="text-align:center">Total Amount</th>
					  <th width="19%" style="text-align:center">Paid Amount</th>	 
					  <th width="19%" style="text-align:center">Due Amount</th>	 
				 </tr>
				 
				 </thead>
				 <tbody>';
				$custid=$POST['custid'];		
			  $qry="SELECT * FROM `tbl_invoice` where invoice_status=0 AND cust_id=$custid";
			
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					while($re=mysqli_fetch_assoc($result1))
					{	
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>
					  	  <td style="text-align:center">'.$re["invoice_no"].'</td>
					  	  <td style="text-align:center">'.date('d/m/Y',strtotime($re["invoice_date"])).'</td>
					  	  <td style="text-align:center">'.$re["g_total"].'</td>
						  <td style="text-align:center">'.$re["paid_amount"].'</td>	 
						  <td style="text-align:center">'.($re["g_total"]-$re["paid_amount"]).'</td>	 
				 		</tr>';				
						$i++;
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='<tr>
						 <td colspan="3" style="text-align:right"> <strong>Total</strong></td>
						 <td style="text-align:center">
							<label><strong>'.$rel_cust_detail["total"].'</strong></label>
						</td>						
						<td style="text-align:center">
							<label><strong>'.$rel_cust_detail["paid_total"].'</strong></label></td>
						<td style="text-align:center">
							<label><strong>'.($rel_cust_detail["total"]-$rel_cust_detail["paid_total"]).'</strong></label>
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