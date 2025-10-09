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
			$where='';
			$s_date=explode(' - ',$POST['date']);
			if($POST['search_serial_no']){
				$where.=' and serial.serial_no in('.$POST['search_serial_no'].') ';
			}
			if($POST['product_id']){
				$where.=' and trn.product_id ='.$POST['product_id'];
			}
			
			$str='';
			$set="SELECT * FROM `tbl_company` where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
		 	$query1="select  company_name  from tbl_customer  where  cust_id=".$POST['cust_id'];
			 $rel1=mysqli_fetch_assoc($dbcon->query($query1));
				$str .='<div id="payment_detail">
				<table  class="display table table-bordered table-striped" id="data_list">
				  <thead> 
					<tr>
						<td class="noborder" colspan="7" style="border:none;text-align: center;">
						<span id="head_logo"><strong style="">'.$set_head['company_name'].'</strong></span></td>
					</tr>
					
					
					<tr>
					  <th width="8%" style="text-align:center">Bill NO.</th>
					  <th width="15%" style="text-align:left">Party Name</th>
					  <th width="20%" colspan="2" style="text-align:left">Item Description</th>
					  <th width="10%" style="text-align:center">Serial No.</th>
					  <th width="8%" style="text-align:center">Supply Date</th>
					  <th width="8%" style="text-align:right;">Sales Rate</th>
					</tr>
				 
				 </thead>
				 <tbody>';
			
			  $qry='select cust.company_name,pro.product_name,trn.description,invoice.invoice_no,invoice.dispatch_date,trn.product_rate,serial.serial_no,trn.start_serial1,trn.end_serial1,trn.start_serial2,trn.end_serial2,trn.start_serial3,trn.end_serial3 from tbl_invoice as invoice
				inner join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id
				inner join tbl_product as pro on pro.product_id=trn.product_id
				inner join tbl_customer as cust on cust.cust_id=invoice.cust_id
				left join tbl_serialtrn as serial on serial.invoicetrn_id=trn.trancation_id
				where invoice.invoice_status=0 and trn.trancation_status=0 and invoice.company_id='.$_SESSION['company_id'].' '.$where.' and invoice.dispatch_date between "'.date('Y-m-d',strtotime($s_date[0])).'" and "'.date('Y-m-d',strtotime($s_date[1])).'" group by trancation_id';
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;$arr=array();
					while($re=mysqli_fetch_assoc($result1))
					{	
						
						$str.='<tr>
						  <td style="text-align:center" class="noborder">'.$re["invoice_no"].'</td>
						  <td style="text-align:left" class="noborder">'.$re["company_name"].'</td>
						  <td style="text-align:left" colspan="2" class="noborder">'.$re["product_name"].'<br/>'.nl2br(stripcslashes($re['description'])).'</td>
						  <td style="text-align:center" class="noborder">';
						  
							if($re["start_serial1"] && $re["end_serial1"]){
								$str.= $re["start_serial1"].'-'.$re["end_serial1"];
							} 
							if($re["start_serial2"] && $re["end_serial2"]){
								$str.= ', '.$re["start_serial2"].'-'.$re["end_serial2"];
							}
							if($re["start_serial3"] && $re["end_serial3"]){
								$str.= ', '.$re["start_serial3"].'-'.$re["end_serial3"];
							} 
						$str.='</td>
						  <td style="text-align:center" class="noborder">'.date("d/m/y",strtotime($re["dispatch_date"])).'</td>
					  	  <td style="text-align:right" class="noborder">'.$re["product_rate"].'</td>';
						$i++;
					}
					
				}
				else
				{
					$str .='<tr>
							<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
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