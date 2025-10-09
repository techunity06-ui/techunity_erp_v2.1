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
	
	if(strtolower($POST['mode']) == "generate_report_product_service")
	{
			$s_date=explode(' - ',$POST['date']);
			
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$cust_id=$POST['cust_id'];
			$product_id=$POST['product_id'];
			
			//$source=explode(',',$POST['source_id']);
			//echo $POST['source_id'];
			$sour=implode(",",$POST['source_id']);
			
			$pr_row=get_product_detail($dbcon,$product_id);
			
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
			$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
				$str .='
					<table  width="100%"   class="display table table-bordered table-striped">
					</table>
					<table  class="display table table-bordered table-striped" id="data_list">
						<tr id="logo" class="logo" style="display:none">
							<td colspan="8" style="text-align:center;">
								<strong>'.$set_head['company_name'].'</strong>
							</td>
						</tr>
						<tr>
							<td colspan="2"><strong>Employee Account Report </strong></td>
							<td colspan="2" style="text-align:center">
								<!--<strong>	Name:'.$cust_rel['company_name'].'</strong><br>
								<strong>Product Name :'.$pr_row['product_name'].'</strong>-->
							</td>
							<td colspan="2" style="text-align:right">
								Date <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
							</td>
						</tr>
						<tr>
							<th width="5%" style="text-align:center">Sr. NO.</th>
							<th width="12%" style="text-align:center">Date</th>
							<th width="47%" style="text-align:center">Description</th>
							<th width="12%" style="text-align:center">Debit Amount</th>
							<th width="12%" style="text-align:center">Credit Amount</th>
							<th width="12%" style="text-align:center">Balance</th>
						</tr>
						<tbody>';
						
							echo $query="select e.cdate,cust.cust_name,us.user_name as lead_owner,op.opp_stage as sales_stage,et.stage_prob as probablity,e.closing_date from tbl_inquiry as e 
									left join tbl_task  as et on et.inquiry_id=e.inquiry_id
									left join tbl_customer as cust on cust.cust_id=e.cust_id
									left join users as us on us.user_id=e.user_id
									left join tbl_opportunity_mst as op on op.opp_id=et.opp_id
									where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.rb_id in (".$sour.") and e.won_user_id=0";
				 
							$result1=$dbcon->query($query);
							$i=1;
							$cnt=mysqli_num_rows($result1);
							if($cnt>0)
							{
								$total=0;
								while($re=mysqli_fetch_assoc($result1))
								{
									$balancetype='';
									$str.='<tr>
									  <td style="text-align:center">'.$i.'</td>
									  <td style="text-align:center">'.date('d-m-Y H:i:s',strtotime($re['cdate'])).'</td>
									  
									  <td style="text-align:center">'.$re["cust_name"].'</td>
									  <td style="text-align:center"></td>
									  <td style="text-align:center">'.$re["lead_owner"].'</td>
									  <td style="text-align:center">'.$re["sales_stage"].'</td>
									  <td style="text-align:center">'.$re["probablity"].'</td>
									  <td style="text-align:center">'.date('d-m-Y',strtotime($re['closing_date'])).'</td>';
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