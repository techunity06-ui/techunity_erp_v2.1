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
			$td=8;$td1=4;
			$width="";
					  
			if(!empty($POST['vender_id']))
			{
				$query_cust="select vender_name from tbl_vender where vender_id=".$POST['vender_id'];
				$rel_cust=mysqli_fetch_assoc($dbcon->query($query_cust));	
				$td=6;$td1=3;
			}	
			$str='';
				$set="select * from tbl_setting";
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$str .='<table  width="100%" class="display table table-bordered table-striped">
					<tr id="logo" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['title'].'</strong>
						</td>
					</tr>
						</table>
						
				  
				  <table  class="display table table-bordered table-striped" id="data_list">
				 	<tr>
						<td colspan="3"><strong>Purchase Detail Report</strong>
						</td>
						<td colspan="6" style="text-align:center">';
						if(!empty($POST['vender_id']))
						{
						$str .='Name: <strong>'.$rel_cust['vender_name'].'</strong>';
						}
						$str .='</td><td colspan="'.$td.'" style="text-align:right">Date
						<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> From <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
					
					</tr>
				
					<tr>
					  <th width="5%" style="text-align:center" rowspan="2">Sr. NO.</th>
					  <th width="10%" style="text-align:center" rowspan="2">Purchase Bill No</th>
					  <th width="10%" style="text-align:center" rowspan="2">Purchase Date</th>';
					  if(empty($POST['vender_id']))
					  {
						$str.='<th width="16%" style="text-align:center" rowspan="2">Vendor Name</th>';
					  }
					  $str .='<th width="7%" style="text-align:center" rowspan="2">Total Amount</th>
					  <th width="7%" style="text-align:center" rowspan="2">Paid Amount</th>	 
					  <th width="7%" style="text-align:center" rowspan="2">Due Amount</th>	 
					  <th width="40%" style="text-align:center" colspan="6">Payment History</th>
				  
				 </tr>
				  <tr>
					  <th width="5%" style="text-align:center">Rec. NO.</th>
					  <th width="5%" style="text-align:center">Mode</th>
					  <th width="5%" style="text-align:center"> Amount</th>
					  <th width="20%" style="text-align:center">Date</th>
					  <th width="10%" style="text-align:center">Cheque No</th>					  
					  <th width="55%" style="text-align:center">Bank name</th>	 
				</tr>
				 <tbody>';
				$where ='';
				if(!empty($POST['vender_id']))
				{
					$where .=" and po.vender_id=".$POST['vender_id'];
				
				}
				 $qry='Select po.po_id, po_no, po_date, ven.vender_name, po.g_total, po.paid_amount from tbl_pono as po inner join tbl_vender as ven on po.vender_id=ven.vender_id  where status=0 and po.company_id='.$_SESSION['company_id'].' and po_date>="'.date('Y-m-d',strtotime($s_date[0])).'" AND po_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where;
			
			  $result1=$dbcon->query($qry);
				$i=1;
				if(mysqli_num_rows($result1)>0)
				{
					while($re=mysqli_fetch_assoc($result1))
					{	
						$tamount=$re['g_total'];
						$due =$tamount-$re["paid_amount"];
						 $query_inv="SELECT po.po_id,purchasereceipt_no,payment_mode,receipt.paid_amount,payment_date,cheque_dtl,cust_bank.bank_name as cust_bname 
						FROM `tbl_purchasereceipt` as receipt 
						left join  tbl_pono po on po.po_id=receipt.purchasebill_id 
						left join tbl_vender ven on ven.vender_id=po.vender_id 
						left join tbl_payment_mode payment on payment.paymentmodeid=receipt.paymentmodeid 
						left join account_mst as acc on acc.acc_id=receipt.acc_id
						left join bank_mst as cust_bank on cust_bank.bankid=acc.bankid
						where receipt.status=0 and receipt.company_id=".$_SESSION['company_id']." and po.po_id=".$re["po_id"];
						$rs_inv=$dbcon->query($query_inv);
						$count=mysqli_num_rows($rs_inv);
						
						
						$str.='<tr>
						  <td style="text-align:center" rowspan="'.$count.'">'.$i.'</td>';		  	
							$str.= '
							<td style="text-align:center"  rowspan="'.$count.'">'.$re["po_no"].'</td>';
						$str.='
						  <td style="text-align:center"  rowspan="'.$count.'">'.date('d/m/Y',strtotime($re["po_date"])).'</td>';
						  if(empty($POST['vender_id']))
						  {
					     	$str.='<td style="text-align:center"  rowspan="'.$count.'">'.$re["vender_name"].'</td>';
						  }
						  $str .='<td style="text-align:center"  rowspan="'.$count.'">'.indian_number($tamount).'</td>
						  <td style="text-align:center"  rowspan="'.$count.'">'.indian_number($re["paid_amount"]).'</td>	 
						  <td style="text-align:center"  rowspan="'.$count.'">'.indian_number($due).'</td>';	 
							  $j=$count;
						
						if(mysqli_num_rows($rs_inv)>0)
						 {
						  while($rel_pay=mysqli_fetch_assoc($rs_inv))
						  {
							if($j<$count )
							{
								$str.='<tr>';
							}
							$str.='
						  <td style="text-align:center" >'.$rel_pay["purchasereceipt_no"].'</td>
					  	  <td style="text-align:center" rowspan="">'.$rel_pay["payment_mode"].'</td>
					  	  <td style="text-align:right" rowspan="">'.indian_number($rel_pay["paid_amount"]).'</td>
						  <td style="text-align:center" rowspan="">'.date('d/m/Y',strtotime($rel_pay["payment_date"])).'</td>	 
						  <td style="text-align:center" rowspan="">'.$rel_pay["cheque_dtl"].'</td>
						  <td style="text-align:center" rowspan="">'.$rel_pay["cust_bname"].'</td>';
							if($j<=$count && $j!=1)
							{
								$str.='</tr>';
							}
							$j--;
						  }
						 }
						 else
						{
							$str.='<td style="text-align:center" colspan="6">No Payment Data Found</td>';
						}
						$str .='</tr>';				
						$i++;
						$total=$total+$tamount;
						$total_paid=$total_paid+$re["paid_amount"];
						$total_due=$total_due+($tamount-$re["paid_amount"]);
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='<tr>
						 <td colspan="'.$td1.'" style="text-align:right"> <strong>Total</strong></td>
						 <td style="text-align:center">
							<label><strong>'.indian_number($total).'</strong></label>
						</td>						
						<td style="text-align:center">
							<label><strong>'.indian_number($total_paid).'</strong></label></td>
						<td style="text-align:center">
							<label><strong>'.indian_number($total_due).'</strong></label>
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