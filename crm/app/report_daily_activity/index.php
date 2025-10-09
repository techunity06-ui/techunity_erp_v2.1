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
					<table  class="display table " id="data_list">
						<tr id="logo" class="logo" style="">
							<td colspan="6" style="text-align:center;">
								<strong>'.$set_head['company_name'].'</strong>
							</td>
						</tr>
						<tr>
							<!--<td colspan="3"><strong>Follow Up Reports</strong></td>
							<td colspan="3" style="text-align:center">
								
							</td>-->
							<td colspan="6" style="text-align:center;">
								<strong>Daily Activity Report From </strong> <label>  : <strong>'.date('d/m/Y',strtotime($POST['date'])).'</strong>-<strong>'.date('d/m/Y',strtotime($POST['end_date'])).'</strong></label>
							</td>
						</tr>
						<tr>
							<th width="5%" style="text-align:center">User Name</th>
							<th width="5%" style="text-align:center"></th>
							<th width="5%" style="text-align:center">Sr. NO.</th>
							<th width="8%" style="text-align:center;white-space:nowrap;">Activity Date</th>
							<th width="8%" style="text-align:center;white-space:nowrap;">Branch Name </th>
							<th width="8%" style="text-align:center">Activity Details</th>
							<!--<th width="8%" style="text-align:center">Next Follow Up Days</th>
							<th width="12%" style="text-align:center">Company Name</th>
							<th width="15%" style="text-align:center">Remarks</th>
							<th width="8%" style="text-align:center">Inquiry No</th>
							<th width="8%" style="text-align:center">Status</th>
							<th width="8%" style="text-align:center">View</th>-->
							
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
						
						//task activity start
							  
						 $query="select led.l_name,cust.cust_name,task.create_date,task.task_due_date,task.task_remark,IF(task.task_status=1, 'Done', 'Pending') as tstatus,user.user_name,inq.inquiry_no,task.inquiry_id,bmst.branch_name,tas.mcd_name as task_name,opmst.opp_stage,salesst.mcd_name as sales_stage,task.user_id from tbl_task as task
									left join users as user on user.user_id=task.user_id
									left join tbl_ledger as led on led.l_id=user.employee_id
									left join tbl_inquiry as inq on inq.inquiry_id=task.inquiry_id
									left join tbl_customer as cust on cust.cust_id=inq.cust_id
									left join branch_mst as bmst on bmst.branch_id=task.branch_id
									left join tbl_master_category_detail as tas on tas.mcd_id=task.task_type_id
									left join tbl_opportunity_mst as opmst on opmst.opp_id=task.opp_id
									left join tbl_master_category_detail as salesst on salesst.mcd_id=task.sales_stage_id
									where task.task_status in (0,1) and task.create_date >= '".date('Y-m-d H:i:s',strtotime($sdate))."' and task.create_date <= '".date('Y-m-d H:i:s',strtotime($edate))."' and task.company_id=".$_SESSION['company_id']." and task.user_id=".$POST['crm_tree_user1']." group by task.task_id order by task.user_id,tas.mcd_name ";
				 
							$result1=$dbcon->query($query);
							$i=1;$total=0;$user_name="";$task_name="";
							$k=1;
							$cnt=mysqli_num_rows($result1);
							if($cnt>0)
							{
								
								while($re=mysqli_fetch_assoc($result1))
								{
									$crdate=date('Y-m-d',strtotime($re['create_date']));
									$dudate=date('Y-m-d',strtotime($re['task_due_date']));
									//$query1="SELECT DATEDIFF(day, ".$crdate.", ".$dudate.") AS DateDiff";
									$query1="SELECT IFNULL((TO_DAYS( '".$dudate."' )-TO_DAYS( '".$crdate."' ) ),0) as No_of_Days";
									$result11=$dbcon->query($query1);
									$re1=mysqli_fetch_assoc($result11);
									 
									$view_inq_btn = '<a href="' . ROOT . CRM_ROOT . 'inquiry_view/' . $re['inquiry_id'] . '" target="_blank" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
									
									if($user_name!=$re["user_name"]){
										$str.='<tr style="font-size: 18px;color: #cc0b0b;">
											<td colspan="6"><strong>'.$re["user_name"].'</strong></td>
										</tr>
										';
										$k=1;
									}
									if($task_name!=$re["task_name"]){
										$str.='<tr style="font-size: 15px;color: #042a63;">
											<td ><strong></strong></td>
											<td colspan="5"><strong>'.$re["task_name"].'</strong></td>
										</tr>
										';
										$i=1;
									}
									
									$balancetype='';
									$str.='<tr style="border: none;">
										<td colspan="2"></td>
									  <td style="text-align:left">'.$i.'</td>
									  <td style="text-align:left">'.date('d-m-Y h:i:sa',strtotime($re['create_date'])).'</td>
									  
									  <td style="text-align:left">'.$re["branch_name"].'</td>
									  <!--<td style="text-align:center">'.$re["user_name"].'</td>-->
									  <td style="text-align:left">
										<strong>Inquiry No : </strong>'.$re['inquiry_no'].' </br>
										<strong>Customer Name : </strong>'.$re["cust_name"].' </br>
										<strong>Stage : </strong>'.$re["opp_stage"].' </br>
										<strong>Sales Stage : </strong>'.$re["sales_stage"].' </br>
										<strong>Next Followup Date :</strong> '.date('d-m-Y h:i:sa',strtotime($re['task_due_date'])).' </br>
										<strong>Next Followup Days : </strong>'.$re1['No_of_Days'].' </br>
										<strong>Remarks : </strong>'.$re["task_remark"].'
										
										</td>
									  <!--<td style="text-align:center">'.date('d-m-Y h:i:sa',strtotime($re['task_due_date'])).'</td>
									  <td style="text-align:center">'.$re1['No_of_Days'].'</td>
									  <td style="text-align:center">'.$re["cust_name"].'</td>
									  <td style="text-align:center">'.$re["task_remark"].'</td>
									  <td style="text-align:center">'.$re['inquiry_no'].'</td>
									  <td style="text-align:center">'.$re['tstatus'].'</td>
									  <td style="text-align:center">'.$view_inq_btn.'</td>-->
									  ';
									  
									  $user_name=$re["user_name"];
									  $task_name=$re["task_name"];
									$i++;
									$k++;
								}
								
								$str.='<!--<tr style="border: none;font-size: 18px;color: #cc0b0b;">
									<td colspan="6">Total For '.$user_name.' ( '.$k.' )</td>
								</tr>-->';
							}
							
						//task activity close
						
						//Sales Order Create Activity Start
						
							$query1="select so.sales_order_no,so.sales_order_date,so.remark,so.po_no,so.po_date,led.l_name,so.approve_status,so.cdate,bmst.branch_name from tbl_sales_order as so
									left join tbl_ledger as led on led.l_id=so.cust_id
									left join branch_mst as bmst on bmst.branch_id=so.branch_id
									where so.sales_order_status=0 and so.cdate >= '".date('Y-m-d H:i:s',strtotime($sdate))."' and so.cdate <= '".date('Y-m-d H:i:s',strtotime($edate))."' and so.company_id=".$_SESSION['company_id']." and so.user_id=".$POST['crm_tree_user1']." group by so.sales_order_id order by so.sales_order_id ";
				 
							$result11=$dbcon->query($query1);
							$i1=1;
							$cnt1=mysqli_num_rows($result11);
							if($cnt1>0)
							{
								$str.='<tr style="font-size: 15px;color: #042a63;">
											<td ><strong></strong></td>
											<td colspan="5"><strong>Sales Order Create</strong></td>
										</tr>
										';
								while($re1=mysqli_fetch_assoc($result11))
								{
									if($re1['approve_status']=="0"){
										$so_approved_status="Pending";
									}else{
										$so_approved_status="Approved";
									}
									$str.='<tr style="border: none;">
										<td colspan="2"></td>
									  <td style="text-align:left">'.$i1.'</td>
									  <td style="text-align:left">'.date('d-m-Y h:i:sa',strtotime($re1['cdate'])).'</td>
									  
									  <td style="text-align:left">'.$re1["branch_name"].'</td>
									  <td style="text-align:left">
										<strong>sales Order No : </strong>'.$re1['sales_order_no'].' </br>
										<strong>sales Order Date : </strong>'.date('d-m-Y',strtotime($re1['sales_order_date'])).' </br>
										<strong>Customer Name : </strong>'.$re1["l_name"].' </br>
										<strong>PO No : </strong>'.$re1["po_no"].' </br>
										<strong>Po Date : </strong>'.date('d-m-Y',strtotime($re1['po_date'])).' </br>
										<strong>Approved Status : </strong>'.$so_approved_status.' </br>
										<strong>Remarks : </strong>'.$re1["remark"].'
										
										</td>
									  ';
									$i1++;
								}
							}
						
						
						//Sales Order Create Activity End
						
						//Sales Order Edit Activity Start
						/*
							$query2="select so.sales_order_no,so.sales_order_date,so.remark,so.po_no,so.po_date,led.l_name,so.approve_status,so.mdate,bmst.branch_name from tbl_sales_order as so
									left join tbl_ledger as led on led.l_id=so.cust_id
									left join branch_mst as bmst on bmst.branch_id=so.branch_id
									where so.sales_order_status=0 and so.mdate >= '".date('Y-m-d H:i:s',strtotime($sdate))."' and so.mdate <= '".date('Y-m-d H:i:s',strtotime($edate))."' and so.company_id=".$_SESSION['company_id']." and so.user_id=".$POST['crm_tree_user1']." group by so.sales_order_id order by so.sales_order_id ";
				 
							$result12=$dbcon->query($query2);
							$i2=1;
							$cnt2=mysqli_num_rows($result12);
							if($cnt2>0)
							{
								$str.='<tr style="font-size: 15px;color: #042a63;">
											<td ><strong></strong></td>
											<td colspan="5"><strong>Sales Order Edit</strong></td>
										</tr>
										';
								while($re2=mysqli_fetch_assoc($result12))
								{
									if($re2['approve_status']=="0"){
										$so_approved_status="Pending";
									}else{
										$so_approved_status="Approved";
									}
									$str.='<tr style="border: none;">
										<td colspan="2"></td>
									  <td style="text-align:left">'.$i2.'</td>
									  <td style="text-align:left">'.date('d-m-Y h:i:sa',strtotime($re2['mdate'])).'</td>
									  
									  <td style="text-align:left">'.$re2["branch_name"].'</td>
									  <td style="text-align:left">
										<strong>sales Order No : </strong>'.$re2['sales_order_no'].' </br>
										<strong>sales Order Date : </strong>'.date('d-m-Y',strtotime($re2['sales_order_date'])).' </br>
										<strong>Customer Name : </strong>'.$re2["l_name"].' </br>
										<strong>PO No : </strong>'.$re2["po_no"].' </br>
										<strong>Po Date : </strong>'.date('d-m-Y',strtotime($re2['po_date'])).' </br>
										<strong>Approved Status : </strong>'.$so_approved_status.' </br>
										<strong>Remarks : </strong>'.$re2["remark"].'
										
										</td>
									  ';
									$i2++;
								}
							}
							*/
						//Sales Order Edit Activity End
						
						//sales order Approved activity start
						
						$query3="select so.sales_order_no,so.sales_order_date,soap.approve_remark,so.po_no,so.po_date,led.l_name,soap.approve_status,soap.cdate,bmst.branch_name from tbl_quot_po_aprv_log as soap
									left join tbl_sales_order as so on so.sales_order_id=soap.sales_order_id
									left join tbl_ledger as led on led.l_id=so.cust_id
									left join branch_mst as bmst on bmst.branch_id=soap.branch_id
									where soap.quot_aprv_log_status=0 and soap.cdate >= '".date('Y-m-d H:i:s',strtotime($sdate))."' and soap.cdate <= '".date('Y-m-d H:i:s',strtotime($edate))."' and so.company_id=".$_SESSION['company_id']." and soap.user_id=".$POST['crm_tree_user1']." group by soap.quot_aprv_log_id order by soap.quot_aprv_log_id ";
				 
							$result13=$dbcon->query($query3);
							$i3=1;
							$cnt3=mysqli_num_rows($result13);
							if($cnt3>0)
							{
								$str.='<tr style="font-size: 15px;color: #042a63;">
											<td ><strong></strong></td>
											<td colspan="5"><strong>Sales Order Approved</strong></td>
										</tr>
										';
								while($re3=mysqli_fetch_assoc($result13))
								{
									if($re3['approve_status']=="0"){
										$so_approved_status="Pending";
									}else{
										$so_approved_status="Approved";
									}
									$str.='<tr style="border: none;">
										<td colspan="2"></td>
									  <td style="text-align:left">'.$i3.'</td>
									  <td style="text-align:left">'.date('d-m-Y h:i:sa',strtotime($re3['cdate'])).'</td>
									  <td style="text-align:left">'.$re3["branch_name"].'</td>
									  <td style="text-align:left">
										<strong>sales Order No : </strong>'.$re3['sales_order_no'].' </br>
										<strong>sales Order Date : </strong>'.date('d-m-Y',strtotime($re3['sales_order_date'])).' </br>
										<strong>Customer Name : </strong>'.$re3["l_name"].' </br>
										<strong>PO No : </strong>'.$re3["po_no"].' </br>
										<strong>Po Date : </strong>'.date('d-m-Y',strtotime($re3['po_date'])).' </br>
										<strong>Approved Status : </strong>'.$so_approved_status.' </br>
										<strong>Approved Remarks : </strong>'.$re3["approve_remark"].'
										
										</td>
									  ';
									$i3++;
								}
							}
							
						
						//sales order Approved activity End

						//Pending Indent Approved activity start
						$query4="select ai.approve_indent_id, ai.approve_no, ai.approve_date, ai.rp_id, ai.approve_qty, ai.approve_indent_status, ai.cdate,ai.quotation_requirement, pm.product_name,bmst.branch_name,trp.rp_pid,trp.indent_no,trp.indent_date from approve_indent as ai 
							left join tbl_request_product as trp on trp.rp_id = ai.rp_id
							left join product_mst as pm on trp.rp_pid = pm.product_id
							left join branch_mst as bmst on bmst.branch_id=ai.branch_id 
							where ai.company_id=".$_SESSION['company_id']." and ai.user_id=".$POST['crm_tree_user1']." and ai.cdate >= CURDATE() order by  ai.approve_indent_id desc";

						$result14=$dbcon->query($query4);
							$i4=1;
							$cnt4=mysqli_num_rows($result14);
							if($cnt4>0)
							{
								$str.='<tr style="font-size: 15px;color: #042a63;">
											<td ><strong></strong></td>
											<td colspan="5"><strong>Indent Approved</strong></td>
										</tr>
										';
								while($re4=mysqli_fetch_assoc($result14))
								{
									if($re4['approve_indent_status']=="0"){
										$indent_approved_status="Approved";
									}else{
										$indent_approved_status="Pending";
									}
									$str.='<tr style="border: none;">
										<td colspan="2"></td>
									  <td style="text-align:left">'.$i4.'</td>
									  <td style="text-align:left">'.date('d-m-Y h:i:sa',strtotime($re4['cdate'])).'</td>
									  <td style="text-align:left">'.$re4["branch_name"].'</td>
									  <td style="text-align:left">
										<strong>Indent No : </strong>'.$re4['indent_no'].' </br>
										<strong>Approve No : </strong>'.$re4['approve_no'].' </br>
										<strong>Indent Date : </strong>'.date('d-m-Y',strtotime($re4['indent_date'])).' </br>
										<strong>Product Name : </strong>'.$re4["product_name"].' </br>
										<strong>Approve Qty : </strong>'.$re4["approve_qty"].' </br>
										<strong>Approved Status : </strong>'.$indent_approved_status.' </br>
										<strong>Approved Remarks : </strong>
										
										</td>
									  ';
									$i4++;
								}
							}	
						//Pending Indent Approved activity end	

						//Purchase Order Created activity Start
						$query5="select tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.cdate,tp.remark,tp.g_total,led.l_name,tp.po_approval_status from tbl_purchaseorder as tp 
							left join tbl_ledger as led on led.l_id=tp.vender_id
							left join branch_mst as bmst on bmst.branch_id=tp.branch_id
							where tp.company_id=".$_SESSION['company_id']." and tp.userid=".$POST['crm_tree_user1']." and tp.cdate >= CURDATE() order by tp.purchaseorder_id desc";

						$result15=$dbcon->query($query5);
							$i5=1;
							$cnt5=mysqli_num_rows($result15);
							if($cnt5>0)
							{
								$str.='<tr style="font-size: 15px;color: #042a63;">
											<td ><strong></strong></td>
											<td colspan="5"><strong>Purchase Order Created</strong></td>
										</tr>
										';
								while($re5=mysqli_fetch_assoc($result15))
								{
									if($re5['po_approval_status']=="1"){
										$po_approval_status="Approved";
									}else{
										$po_approval_status="Approval Pending";
									}
									$str.='<tr style="border: none;">
										<td colspan="2"></td>
									  <td style="text-align:left">'.$i5.'</td>
									  <td style="text-align:left">'.date('d-m-Y h:i:sa',strtotime($re5['cdate'])).'</td>
									  <td style="text-align:left">'.$re5["branch_name"].'</td>
									  <td style="text-align:left">
										<strong>PO No : </strong>'.$re5['purchaseorder_no'].' </br>
										<strong>PO Date : </strong>'.date('d-m-Y',strtotime($re5['purchaseorder_date'])).' </br>
										<strong>Vendor Name : </strong>'.$re5["l_name"].' </br>
										<strong>Grand Total : </strong>'.$re5["g_total"].' </br>
										<strong>Approved Status : </strong>'.$po_approval_status.' </br>
										<strong>Approved Remarks : </strong>'.$re5["remark"].'
										
										</td>
									  ';
									$i5++;
								}
							}
						//Purchase Order Created activity End
							
						
							
						$str .='</tbody>				 
					</table>';
				echo $str;
	}
	
	
?>