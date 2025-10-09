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
							<td style="text-align:center" colspan="7">
							<h2 style="margin-top:1px;margin-bottom:2px">
							<strong>'.$set_head['title'].'</strong></h2>
							<br>
							<strong>'.$set_head['address'].'</strong>
							</td>
							</tr>
							
						</table>
					<br>
					<br>';
				if(!empty($POST['typeid']))
				{
			$qry_cust_detail='Select exciseinvoice_id,exciseinvoice_no,exciseinvoice_date,invoice_type,cust.company_name,g_total,paid_amount,exciseinvoice_status,invoice.cdate,invoice.user_id,invoice.invoicetype_id,invoice.*  from tbl_exciseinvoice as invoice inner join  tbl_customer cust on invoice.cust_id=cust.cust_id inner join  tbl_invoicetype type on type.invoicetype_id=invoice.invoicetype_id where exciseinvoice_status=0 and invoice.invoicetype_id='.$POST['typeid'].' and exciseinvoice_date>="'.date('Y-m-d',strtotime($POST['s_date'])).'" AND exciseinvoice_date<="'.date('Y-m-d',strtotime($POST['e_date'])).'"';
				}						
				$result=$dbcon->query($qry_cust_detail);
				$query="select tax_id,tax_name from tbl_tax where tax_id in (SELECT tax_id FROM `formula_mst` where find_in_set(formulaid,(SELECT group_concat(distinct formulaid) as formula FROM `tbl_exciseinvoice`)))";
				$rs_tax=$dbcon->query($query);
				
				$str .='
					<table class="display table table-bordered table-striped" border="1" width="100%"><tr>
					<td colspan="2">Invoicetype  Report</td>
					<td colspan="4" style="text-align:right">Date
					<label>:'.date('d/m/Y',strtotime($POST['s_date'])).' TO ' .date('d/m/Y',strtotime($POST['e_date'])). '</label></td>
				  </tr>
					</table>
				 <table  class="display table table-bordered table-striped" id="data_list">
				  <thead>  		
				  <tr>
					  <th width="2%"style="text-align:center">SR. No</th>
					  <th width="10%"style="text-align:center">Invoice No</th>
					  <th width="10%" style="text-align:center">Invoice Date</th>
					  <th width="15%" style="text-align:center">Customer Name</th>
					  <th width="10%" style="text-align:center">Total Amount</th>
					  <th width="10%" style="text-align:center">Paid Amount</th>	 
					  <th width="10%" style="text-align:center">Due Amount</th>	';
					  $tax_arr=array();
					  while($rel_tax=mysqli_fetch_assoc($rs_tax))
					  {
						$str .='<th width="16%" style="text-align:center">'.$rel_tax['tax_name'].'</th>';
						$tax_arr['name'][]=$rel_tax['tax_name'];
					  }
				$str .='		
				 </tr>
				 
				 </thead>
				 <tbody>';
				 $i=1;$total=0;$total_paid=0;$total_due=0;
				 if(mysqli_num_rows($result)>0)
				 {
					while($re=mysqli_fetch_assoc($result))
					{	
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>
					  	  <td style="text-align:center">'.$re["exciseinvoice_no"].'</td>
					  	  <td style="text-align:center">'.date('d/m/Y',strtotime($re["exciseinvoice_date"])).'</td>
					  	  <td style="text-align:center">'.$re["company_name"].'</td>
					  	  <td style="text-align:center">'.$re["g_total"].'</td>
						  <td style="text-align:center">'.$re["paid_amount"].'</td>	 
						  <td style="text-align:center">'.($re["g_total"]-$re["paid_amount"]).'</td>';
							$cnt=1;							
							for($i=0;$i<count($tax_arr['name']);$i++)
							{	
								 $str1="taxvalue".$cnt;
								if($re["tax1_name"]==$tax_arr['name'][$i] || $re["tax2_name"]==$tax_arr['name'][$i] || $re["tax3_name"]==$tax_arr['name'][$i])
								  {
									$str.='<td style="text-align:center">'.number_format($re[$str1],0).'</td>';
									$cnt++;
									$tax_arr['total'][$i]+=$re[$str1];
								  }
								else{
									$str.='<td style="text-align:center"></td>';
									}
						  }						 
				 		$str.='</tr>';				
						$i++;
						$total=$total+$re["g_total"];						
						$total_paid=$total_paid+$re["paid_amount"];						
						$total_due=$total_due+($re["g_total"]-$re["paid_amount"]);					}
					
				}
				else
				{
					$str .='<tr>
							<td colspan="5" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='
			<tr>
					<td colspan="4"  style="text-align:right"><strong>Total</strong></td>	
					<td style="text-align:center">
						<label><strong>'.$total.'</strong></label>
					</td>						
					<td style="text-align:center">
						<label><strong>'.$total_paid.'</strong></label>
					</td>						
	
					<td style="text-align:center">
							 <label><strong>'.$total_due.'</strong></label></td>';
					for($i=0;$i<count($tax_arr['total']);$i++)
					{
						$str.='<td style="text-align:center">
							 <label><strong>'.$tax_arr['total'][$i].'</strong></label></td>';
					}
					$str.='</tr>	
						
				  </tbody>				 
				  </table>
				  ';
				  
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