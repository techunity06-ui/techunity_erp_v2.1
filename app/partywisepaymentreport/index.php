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
				
			$set="select * from tbl_setting";
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			
				$str .='<table  class="display table table-bordered table-striped" id="data_list">
				  <tr id="logo" style="display:none">
						<td colspan="6" style="text-align:center;">
						<strong>'.$set_head['title'].'</strong></td>
					</tr>
					<tr>
						<td colspan="6"><strong>Party Wise Payment Report</strong></td>
						
					</tr>
				  <tr>
					  <th width="2%" style="text-align:center">Sr. NO.</th>
					  <th width="15%" style="text-align:center">Party Name</th>
					  <th width="15%" style="text-align:center">Total Amount</th>
					  <th width="25%" style="text-align:center">Total Recived</th>
					  <th width="15%" style="text-align:center">Total Due</th>
					  <th width="15%" style="text-align:center">Action</th>	 
				</tr>
				 <tbody>';
			$where ="";
			if(!empty($POST['comapny_id']))
			{
				$companyid = implode(',',$POST['comapny_id']);
				$where .= "and cust.cust_id in (".$companyid.")";
			}
			$qry='Select cust.company_name,(Select sum(g_total) from tbl_invoice as invoice where invoice_status=0 and invoice.cust_id=cust.cust_id) as total ,(Select sum(invoice.paid_amount) from tbl_invoice as invoice where invoice_status=0 and invoice.cust_id=cust.cust_id) as receive,cust.cust_id from tbl_invoice as invoice left join tbl_customer as cust on invoice.cust_id=cust.cust_id where invoice_status=0 '.$where.' and cust.company_id='.$_SESSION['company_id'].' group by cust_id';
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($re=mysqli_fetch_assoc($result1))
					{	
						$due=$re["total"]-$re['receive'];
						if($POST['status']=='1')
						{
								if($due>0)
								{
								$str.='<tr>
								<td style="text-align:center">'.$i.'</td>
								<td style="text-align:center">'.$re["company_name"].'</td>
								<td style="text-align:center">'.indian_number($re["total"]).'</td>
								<td style="text-align:center">'.indian_number($re['receive']).'</td>
								<td style="text-align:center">'.indian_number($due).'</td>	 
								<td style="text-align:center">
								 <a class="btn btn-xs btn-info" data-original-title="View Payment History" data-toggle="tooltip" data-placement="top" href="javascript:;" Onclick="view_history('.$re['cust_id'].')"><i class="fa fa-eye"></i></a></td>	 
								</tr>';
							$total=$total+$re["total"];
							$total_paid=$total_paid+$re['receive'];
							$total_due=$total_due+($re["total"]-$re['receive']);
				
							}
						}
						else if($POST['status']=='2')
						{
							if($due==0)
							{
							$str.='<tr>
							<td style="text-align:center">'.$i.'</td>
							<td style="text-align:center">'.$re["company_name"].'</td>
							<td style="text-align:center">'.indian_number($re["total"]).'</td>
							<td style="text-align:center">'.indian_number($re['receive']).'</td>
							<td style="text-align:center">'.indian_number($re["total"]-$re['receive']).'</td>	 
							<td style="text-align:center">
							<a class="btn btn-xs btn-info" data-original-title="View Payment History" data-toggle="tooltip" data-placement="top" href="javascript:;" Onclick="view_history('.$re['cust_id'].')"><i class="fa fa-eye"></i></a></td>	 
				 		</tr>';
							$total=$total+$re["total"];
							$total_paid=$total_paid+$re['receive'];
							$total_due=$total_due+($re["total"]-$re['receive']);
			
							}
						}
						else{
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>
						  <td style="text-align:center">'.$re["company_name"].'</td>
					  	  <td style="text-align:center">'.indian_number($re["total"]).'</td>
					  	  <td style="text-align:center">'.indian_number($re['receive']).'</td>
						  <td style="text-align:center">'.indian_number($re["total"]-$re['receive']).'</td>	 
						  <td style="text-align:center">
						  <a class="btn btn-xs btn-info" data-original-title="View Payment History" data-toggle="tooltip" data-placement="top" href="javascript:;" Onclick="view_history('.$re['cust_id'].')"><i class="fa fa-eye"></i></a></td>	 
				 		</tr>';
							$total=$total+$re["total"];
							$total_paid=$total_paid+$re['receive'];
							$total_due=$total_due+($re["total"]-$re['receive']);
				
						}
						$i++;
					}
					$str .="<tr>
							<td></td>
							<td style='text-align:right'>Total</td>
							<td style='text-align:center'><strong>".indian_number($total)."</strong></td>
							<td style='text-align:center'><strong>".indian_number($total_paid)."</strong></td>
							<td style='text-align:center'> <strong>".indian_number($total_due)."</strong></td>
							<td style='text-align:center'></td></tr>";
				}
				else
				{
					$str .='<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>';
				  
			echo $str;
		}
		else if(strtolower($POST['mode']) == "view_history") {
			$str ='';
			
			$str .='<table  class="display table table-bordered table-striped" id="data_list">
				  <thead>  		
				  <tr>
					  <th width="20%" style="text-align:center">Payment Date</th>
					  <th width="15%" style="text-align:center">Payment Type</th>
					  <th width="25%" style="text-align:center">Bank Name</th>
					  <th width="15%" style="text-align:center">Cheque No</th>
					  <th width="15%" style="text-align:center">Amount</th>	 
				</tr>
				 
				 </thead>
				 <tbody>';
				
			 $qry='Select payment_date,paymentmode.payment_mode,bank.bank_name,acc.acc_name,acc.branch_name,receipt.paid_amount as receive,cheque_dtl from tbl_receipt as receipt  left join account_mst as acc on acc.acc_id=receipt.acc_id
left join bank_mst as bank on bank.bankid=acc.bankid left join tbl_payment_mode as paymentmode on paymentmode.paymentmodeid=receipt.paymentmodeid  left join tbl_invoice as invoice on invoice.invoice_id=receipt.invoice_id left join tbl_customer as cust on invoice.cust_id=cust.cust_id where invoice.cust_id='.$POST['custid'].' and receipt.status=0 and  invoice_status=0  order by payment_date ASC';
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($re=mysqli_fetch_assoc($result1))
					{	
						$str.='<tr>
						  <td style="text-align:center">'.date('d/m/Y',strtotime($re["payment_date"])).'</td>
					  	  <td style="text-align:center">'.$re["payment_mode"].'</td>';
					  	 
						if($re['bankid']!="0")
						  {
						  $str.='
						  <td style="text-align:center">'.$re['bank_name'].' - '.$re["acc_name"].'</td>';
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
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='<input  name="company_id" type="hidden" id="company_id" value="'.$POST['custid'].'"/>
					<input  name="to_date" type="hidden" id="to_date" value="'.date('d-m-Y').'"/>
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