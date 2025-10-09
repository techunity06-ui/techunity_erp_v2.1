<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
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
			$user_id=$POST['crm_tree_user1'];
			if(!empty($user_id)){
				$user_where = " and (FIND_IN_SET (" . $user_id . ",task.show_user_ids) OR task.user_id=".$user_id." )";
			}else{
				$user_where ="";
			}
			
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
			$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
				$str .='
					<table  width="100%"   class="display table table-bordered table-striped">
					</table>
					<table  class="display table table-bordered table-striped" id="data_list">
						<tr id="logo" class="logo" style="">
							<td colspan="10" style="text-align:center;">
								<strong>'.$set_head['company_name'].'</strong>
							</td>
						</tr>
						<tr>
							<td colspan="3"><strong>Follow Up Reports</strong></td>
							<td colspan="3" style="text-align:center">
								<!--<strong>	Name:'.$cust_rel['company_name'].'</strong><br>
								<strong>Product Name :'.$pr_row['product_name'].'</strong>-->
							</td>
							<td colspan="4" style="text-align:right">
								Date <label>  : <strong>'.date('d/m/Y',strtotime($POST['date'])).'</strong>-<strong>'.date('d/m/Y',strtotime($POST['end_date'])).'</strong></label>
							</td>
						</tr>
						<tr>
							<th width="5%" style="text-align:center">Sr. NO.</th>
							<th width="8%" style="text-align:center;white-space:nowrap;">Work Date</th>
							<th width="8%" style="text-align:center;white-space:nowrap;">Employee Name </th>
							<th width="8%" style="text-align:center">Next Follow Up Date</th>
							<th width="8%" style="text-align:center">Next Follow Up Days</th>
							<th width="12%" style="text-align:center">Company Name</th>
							<th width="15%" style="text-align:center">Remarks</th>
							<th width="8%" style="text-align:center">Inquiry No</th>
							<th width="8%" style="text-align:center">Status</th>
							<th width="8%" style="text-align:center">View</th>
							
						</tr>
						<tbody>';
						
							/* $query="select e.cdate,e.inquiry_name as oppurtunity_name,cust.cust_name,us.user_name as lead_owner,op.opp_stage as stage,e.stage_prob as probablity,e.closing_date,mc.mcd_name as sales_stage from tbl_inquiry as e 
									left join tbl_task  as et on et.inquiry_id=e.inquiry_id
									left join tbl_customer as cust on cust.cust_id=e.cust_id
									left join users as us on us.user_id=e.user_id
									left join tbl_opportunity_mst as op on op.opp_id=e.opp_id
									left join tbl_master_category_detail as mc on mc.mcd_id=e.sales_stage_id
									where e.inquiry_status=0 and et.task_status=0 and et.entry_type=1 and e.rb_id in (".$sour.")"; */
									$sdate= $POST['date']." 00:00:00";
									$edate= $POST['end_date']." 23:59:59";
							  
						 $query="select led.l_name,cust.cust_name,task.create_date,task.task_due_date,task.task_remark,IF(task.task_status=1, 'Done', 'Pending') as tstatus,user.user_name,inq.inquiry_no,task.inquiry_id from tbl_task as task
									left join users as user on user.user_id=task.user_id
									left join tbl_ledger as led on led.l_id=user.employee_id
									left join tbl_inquiry as inq on inq.inquiry_id=task.inquiry_id
									left join tbl_customer as cust on cust.cust_id=inq.cust_id
									where task.task_status in (0,1) and task.create_date >= '".date('Y-m-d H:i:s',strtotime($sdate))."' and task.create_date <= '".date('Y-m-d H:i:s',strtotime($edate))."' and task.company_id=".$_SESSION['company_id']." ".$user_where." group by task.task_id order by task.user_id ";
				 
							$result1=$dbcon->query($query);
							$i=1;
							$cnt=mysqli_num_rows($result1);
							if($cnt>0)
							{
								$total=0;
								while($re=mysqli_fetch_assoc($result1))
								{
									$crdate=date('Y-m-d',strtotime($re['create_date']));
									$dudate=date('Y-m-d',strtotime($re['task_due_date']));
									//$query1="SELECT DATEDIFF(day, ".$crdate.", ".$dudate.") AS DateDiff";
									$query1="SELECT IFNULL((TO_DAYS( '".$dudate."' )-TO_DAYS( '".$crdate."' ) ),0) as No_of_Days";
									$result11=$dbcon->query($query1);
									$re1=mysqli_fetch_assoc($result11);
									 
									$view_inq_btn = '<a href="' . ROOT . CRM_ROOT . 'inquiry_view/' . $re['inquiry_id'] . '" target="_blank" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
               
									
									$balancetype='';
									$str.='<tr>
									  <td style="text-align:center">'.$i.'</td>
									  <td style="text-align:center">'.date('d-m-Y h:i:sa',strtotime($re['create_date'])).'</td>
									  
									  <td style="text-align:center">'.$re["user_name"].'</td>
									  <td style="text-align:center">'.date('d-m-Y h:i:sa',strtotime($re['task_due_date'])).'</td>
									  <td style="text-align:center">'.$re1['No_of_Days'].'</td>
									  <td style="text-align:center">'.$re["cust_name"].'</td>
									  <td style="text-align:center">'.$re["task_remark"].'</td>
									  <td style="text-align:center">'.$re['inquiry_no'].'</td>
									  <td style="text-align:center">'.$re['tstatus'].'</td>
									  <td style="text-align:center">'.$view_inq_btn.'</td>
									  ';
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