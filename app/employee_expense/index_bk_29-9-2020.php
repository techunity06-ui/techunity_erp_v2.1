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
	
	if(strtolower($POST['mode']) == "fetch") {
		
		$emp_name=$POST['emp_name'];
		$emp_status=$POST['emp_status'];
		
		$where='';
		$where.=' and exp.emp_id!=0';
		if($emp_name!='')
		{
			$where.=" and exp.user_id=".$POST['emp_name'];
		}
		if($emp_status!=''){
			$where.=" and exp.expense_approve_status=".$POST['emp_status'];
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('exp.ex_id','exp.remark', 'exp.expense_date','exp.expense_complain', 'exp.paid_amount','exp.expense_status', 'exp.user_id','exp.expense_approve_status','comp.complaint_no','u.user_name','comp.cust_id','l.l_name as exp_name','l1.l_name as customer_name');
		$sIndexColumn = "ex_id";
		$isWhere = array("exp.expense_status=0 ".$where);
		$sTable = "tbl_expense_detail as exp";			
		$isJOIN = array('left join tbl_complaint as comp on  comp.complaint_id=exp.expense_complain','left join users as u on u.user_id=exp.user_id','left join tbl_ledger as l on l.l_id=exp.exp_accountid','left join tbl_ledger as l1 on l1.l_id=exp.vendorid');
		$hOrder = "ex_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = date("d/m/Y",strtotime($row['expense_date'])); 
			$row_data[] = $row['exp_name']; 
			$row_data[] = $row['user_name']; 
			$row_data[] = $row['complaint_no']; 
			$row_data[] = $row['customer_name']; 
			$row_data[] = $row['paid_amount']; 
			$row_data[] = nl2br($row['remark']); 
			//$row_data[] = get_last_remark($dbcon,$row['ex_id']); 
			if($row['expense_approve_status']=='0')
			{
				$row_data[] = '<a class="btn btn-warning btn-xs">Pending</a>'; 
			}
			else if($row['expense_approve_status']=='2')
			{
				$row_data[] = '<a class="btn btn-danger btn-xs">Rejected</a>';  
			}
			else
			{
				$row_data[] = '<a class="btn btn-success btn-xs">Approved</a>';  
			}
			
			/*if($row['expense_approve_status']=='0')
			{
				$row_data[]='<a onclick="approveData('.$row['ex_id'].')" class="btn btn-primary btn-xs">Change Status</a>';
			}
			else
			{
				$row_data[]='<a onclick="DisApproveData('.$row['ex_id'].')" class="btn btn-primary btn-xs">Change Status</a>';
			}*/
			
			$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_expense('.$row['ex_id'].')"><i class="fa fa-trash-o"></i></button>';
			$row_data[]='<a href="'.ROOT.'expense_status/'.$row['ex_id'].'" class="btn btn-primary btn-xs">Change Status</a> '.$del_btn;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$info['expense_name']	= $POST['expense_name'];							
		$info['expense_complain']	= $POST['expense_complain'];							
		$info['expense_amount']	= $POST['expense_amount'];							
		$info['expense_date']		= date("Y-m-d",strtotime($POST['expense_date']));
		$info['user_id']	= $_SESSION['user_id'];
		
		$inserid=add_record('tbl_expense_detail', $info, $dbcon);
		
		if($inserid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_expense_detail` WHERE `ex_id` = '$POST[expense_id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "change_status") {	
		
		$emp_id=$POST['emp_id'];
		$mode=$POST['mode'];
		$status=$POST['emp_status'];
		$remark_emp=$POST['remark_emp'];
		$ex_id=$POST['ex_id'];
		$amount=$POST['amount'];
	
		$update = $dbcon -> query("update tbl_expense_detail set expense_approve_status='$status',change_remark='$remark' where ex_id='$ex_id'");
		
		$info['eh_ex_id']	= $ex_id;							
		$info['eh_emp_id']	= $emp_id;							
		$info['eh_date']	= date("Y-m-d H:i:s");						
		$info['eh_status']	= $status;
		$info['eh_remark']	= $remark_emp;
		$info['eh_amount']	= $amount;
		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['usertype_id']= $_SESSION['user_type'];
		
		$inserid=add_record('tbl_expense_status_history', $info, $dbcon);
		
		$query="select * from tbl_expense_detail where ex_id=".$ex_id;
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		
		$general_book_id=get_general_book_id($dbcon,'tbl_expense_detail',$ex_id,$info['eh_emp_id']);
		$general_book_id1=get_general_book_id($dbcon,'tbl_expense_detail_account',$ex_id,$rel['exp_accountid']);
			
		if($status=="1"){
			add_general_book_entry($dbcon,"tbl_expense_detail",$ex_id,1,$info['eh_emp_id'],$rel['paid_amount'],$general_book_id,$rel['expense_date']);
			
			add_general_book_entry($dbcon,"tbl_expense_detail_account",$ex_id,1,$rel['exp_accountid'],$rel['paid_amount'],$general_book_id1,$rel['expense_date']);
		}else{
			$info_exp['genral_book_status']=2;
			$updateid=update_record('tbl_general_book', $info_exp,"general_book_id=".$general_book_id , $dbcon);
			$updateid=update_record('tbl_general_book', $info_exp,"general_book_id=".$general_book_id1 , $dbcon);
		}
		
		if($update)
		{
			$arr['msg']="1";
		}
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['expense_name']	= $POST['expense_name'];							
		$info['expense_complain']	= $POST['expense_complain'];							
		$info['expense_amount']	= $POST['expense_amount'];							
		$info['expense_date']		= date("Y-m-d",strtotime($POST['expense_date']));
		$info['user_id']	= $_SESSION['user_id'];
		
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['edit_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['expense_del_status']='2';
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['eid'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "approve") {
		$info['expense_approve_status']='1';
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['expense_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "dis_approve") {
		$info['expense_approve_status']='0';
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['expense_id'] , $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	else if(strtolower($POST['mode']) == "generate_report")
	{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			
			
			
			
	}
	else if(strtolower($POST['mode']) == "generate_report_complain")
	{
			
			$comp_id=$POST['comp_id'];
			$where='';
			if($comp_id!='')
			{
				$where.=" and exp.expense_complain='$comp_id' ";
			}
			
			$str='';
			$str .='
				
			  <table  class="display table table-bordered table-striped" id="data_list">
			  
			  <tr>
				  <th width="5%" style="text-align:center">Sr. NO.</th>
				  <th width="12%" style="text-align:center">Date</th>
				  <th width="47%" style="text-align:center">Description</th>
				  <th width="12%" style="text-align:center">Amount</th>
			  </tr>
			 <tbody>';
			 $query="select exp.*,l.l_name from tbl_expense_detail as exp left join tbl_ledger as l on l.l_id=exp.exp_accountid where exp.expense_status=0".$where;
			 $row=$dbcon->query($query);
			 if(mysqli_num_rows($row))
			 {
				$cnt=1;
				while($rel=mysqli_fetch_assoc($row))
				{
					
					$str .='<tr>
						<td style="text-align:center">'.$cnt.'</td>
						<td style="text-align:center">'.date('d/m/Y',strtotime($rel['expense_date'])).'</td> 
						<td style="text-align:center">'.$rel['l_name'].'</td>
						<td style="text-align:center">'.$rel['g_total'].'</td>
					</tr>';
					
					$cnt++;
				}
			 }
			 else
			 {
				 $str .='<tr>
						<td style="text-align:center;color:red" colspan="4">No Data Found</td>
					</tr>';
			 }
			$str .='</tbody>				 
			  </table>';
			 
			 echo $str;
	}
	else if(strtolower($POST['mode']) == "generate_report_product_service")
	{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$cust_id=$POST['cust_id'];
			$product_id=$POST['product_id'];
			
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
						<td colspan="2" style="text-align:center"><strong>	Name:'.$cust_rel['company_name'].'
						</strong><br>
						<strong>Product Name :'.$pr_row['product_name'].'
						</strong>
						</td>
						<td colspan="2" style="text-align:right">Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
				
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
				 $query="select e.ex_id,e.vendorid,e.expense_date,et.expense_mstid,et.account_mst_id,sum(et.expense_amount) as eamount from tbl_expense_detail as e left join expense_trn  as et on et.expense_mstid=e.ex_id ";
				 
				 $result1=$dbcon->query($query);
				 $i=1;
				
			if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($re=mysqli_fetch_assoc($result1))
					{
						$balancetype='';
						$str.='<tr>
						  <td style="text-align:center">'.$i.'</td>
						  <td style="text-align:center">'.date('d/m/Y',strtotime($re["expense_date"])).'</td>
						  <td style="text-align:center">'.$re["account_mst_id"].'</td>
						  <td style="text-align:center"></td>
						  <td style="text-align:center"></td>
						  <td style="text-align:center">'.$re["eamount"].'</td>';
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
	else if(strtolower($POST['mode']) == "generate_report_emp_per")
	{
			$eid=$POST['emp_id'];
			
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));			
			
			$qrycust="select l_id,l_name from tbl_ledger where l_id=".$eid;
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
						<td colspan="2"><strong>Employee Per Report </strong></td>
						<td style="text-align:center" colspan="2"><strong> Employee Name :'.$cust_rel['l_name'].'</strong></td>
				
					</tr>
				  
				  <tr>
					  <th width="5%" style="text-align:center">Sr. NO.</th>
					  <th width="12%" style="text-align:center">Product</th>
					  <th width="27%" style="text-align:center">Complaint</th>
					  <th width="27%" style="text-align:center">Installation</th>
				  </tr>
				  <tr>
					<th></th>
					<th></th>
					<th>
						<table class="table table-bordered">
							<tr>
								<th>Assign</th>
								<th>Done</th>
								<th>Note Done</th>
								<th>30 Days</th>
							</tr>
						</table>
					</th>
					<th>
						<table  class="table  table-bordered">
							<tr>
								<th>Assign</th>
								<th>Done</th>
								<th>Note Done</th>
								<th>30 Days</th>
							</tr>
						</table>
					</th>
				  </tr>
				 <tbody>';
				 $query="select fl.*,ct.product_id,p.product_name from tbl_follow as fl left join tbl_complaint_trn as ct on ct.complaint_id=fl.fl_cid left join product_mst as p on p.product_id=ct.product_id where fl.fl_e_id='$eid' group by ct.product_id ";
				 $row=$dbcon->query($query);
				 if(mysqli_num_rows($row))
				 {
					$cnt=1;
					while($rel=mysqli_fetch_assoc($row))
					{
						
						$str .='<tr>
							<td style="text-align:center">'.$cnt.'</td>
							<td style="text-align:center">'.$rel['product_name'].'</td> 
							<td style="text-align:center">
								<table class="table table-bordered">
									<tr>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'1','2',$eid,'').'</th>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'1','4',$eid,'').'</th>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'1','5',$eid,'').'</th>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'1','4',$eid,30).'</th>
									</tr>
								</table>
							</td>
							<td style="text-align:center">
								<table class="table table-bordered">
									<tr>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'2','2',$eid,'').'</th>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'2','4',$eid,'').'</th>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'2','5',$eid,'').'</th>
										<th>'.get_qty_report($dbcon,$rel['product_id'],'2','4',$eid,30).'</th>
									</tr>
								</table>
							</td>
						</tr>';
						
						$cnt++;
					}
				 }
				 else
				 {
					 $str .='<tr>
							<td style="text-align:center;color:red" colspan="4">No Data Found</td>
						</tr>';
				 }
				$str .='</tbody>				 
				  </table>';
				 
				 echo $str;
	}
	else if(strtolower($POST['mode']) == "generate_report_frequent")
	{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));			
			
			
				$str .='
					<table  width="100%"   class="display table table-bordered table-striped">
					</table>
				  <table  class="display table table-bordered table-striped" id="data_list">
				  <tr id="logo" class="logo" style="display:none">
						<td colspan="6" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr>
						<td colspan="3"><strong>Frequent Complaint Report </strong></td>
						<td colspan="3" style="text-align:right">Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
				
					</tr>
				  
				  <tr>
					  <th width="5%" style="text-align:center">Sr. NO.</th>
					  <th width="12%" style="text-align:center">Customer Name</th>
					  <th width="27%" style="text-align:center">Pro Name</th>
					  <th width="27%" style="text-align:center">Complain No</th>
					  <th width="27%" style="text-align:center">Emp Details</th>
					  <th width="27%" style="text-align:center">Spare Details</th>
				  </tr>
				  <tr>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>

				  </tr>
				 <tbody>';
				 $query="select f.fl_cid,f.fl_f_status,f.fl_date,f.fl_e_id,count(f.fl_cid) as cid,c.cust_id,c.complaint_no ,l.l_name,e.employee_name from tbl_follow as f left join tbl_complaint as c on c.complaint_id=f.fl_cid left join tbl_ledger as l on l.l_id=c.cust_id left join employee_mst as e on e.employee_id=f.fl_e_id group by fl_cid order by count(fl_cid) desc";
				 $row=$dbcon->query($query);
				 if(mysqli_num_rows($row))
				 {
					$cnt=1;
					while($rel=mysqli_fetch_assoc($row))
					{
						
						$str .='<tr>
							<td style="text-align:center">'.$cnt.'</td>
							<td style="text-align:center">'.$rel['l_name'].'</td> 
							<td style="text-align:center">'.get_product_complain($dbcon,$rel['fl_cid']).'</td>
							<td style="text-align:center">'.$rel['complaint_no'].'</td>
							<td style="text-align:center">'.$rel['employee_name'].'</td>
							<td style="text-align:center"></td>
						</tr>';
						
						$cnt++;
					}
				 }
				 else
				 {
					 $str .='<tr>
							<td style="text-align:center;color:red" colspan="6">No Data Found</td>
						</tr>';
				 }
				$str .='</tbody>				 
				  </table>';
				 
				 echo $str;
	}
	else if(strtolower($POST['mode']) == "generate_report_comp_history")
	{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));			
			
			$str='';
			//$today=date('Y-m-d');
			
			if($s_date[0]==$s_date[1])
			{
				$query_compl="select * from tbl_complaint where complaint_date='".date("Y-m-d")."'  order by complaint_date desc";
			}
			else
			{
				$query_compl="select * from tbl_complaint where complaint_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND complaint_date<='".date('Y-m-d',strtotime($s_date[1]))."' order by complaint_date desc";
			}
			 $row=$dbcon->query($query_compl);
			 if(mysqli_num_rows($row))
			 {
				
				while($rel=mysqli_fetch_assoc($row))
				{
					$id=$rel['complaint_id'];
					$str .='<div class="col-md-12">
					
						<center>
							
							<div class="col-md-12">
								<div class="panel-group" id="accordion4" role="tablist" aria-multiselectable="true">
									<div class="panel panel-default">
										<div class="panel-heading" role="tab" id="headingOne'.$rel['complaint_id'].'">
											<h4 class="panel-title">
												<a role="button" data-toggle="collapse" data-parent="#accordion4" href="#collapseOne'.$rel['complaint_id'].'" aria-expanded="true" aria-controls="collapseOne4">
													<i class="icon fa fa-globe"></i>
													'.$rel['complaint_no'].'
													
												</a>
											</h4>
										</div>
										<div id="collapseOne'.$rel['complaint_id'].'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne'.$rel['complaint_id'].'">
											<div class="panel-body">
												<table class="table table-bordered table-hover table-striped ">
				
													<tr>
														<th colspan="4" class="redc">Product Detail</th>
													</tr>
													
													<tr>
														<th>#</th>
														<th>Product</th>
														<th>Model</th>
														<th>Service Status</th>
													</tr>';
													$query1="select comp.*,p.product_name,m.model_name from tbl_complaint_trn as comp inner join product_mst as p on p.product_id=comp.product_id inner join model_mst as m on m.model_id=comp.model_id where comp.complaint_id=$id";
													$rs_comp1=$dbcon->query($query1);
													while($row1=mysqli_fetch_array($rs_comp1))
													{
														if($row1['comp_pro_sts']=='2'){
															$status="Paid"; }
															else { $status="Free"; }
															
														$str.='
														<tr>
															<th>'.$cnt.'</th>
															<th>'.$row1['product_name'].'</th>
															<th>'.$row1['model_name'].'</th>
															<th>'.$status.'</th>
														</tr>';
													}
													$str.='
												</table>
												
												<table  class="table table-bordered table-hover table-striped ">
					
													<tr>
														<th colspan="5"  class="redc">Followup History</th>
													</tr>
													
													<tr>
														<th>#</th>
														<th>Date</th>
														<th>Status</th>
														<th>Assigned Emploee</th>
														<th>Remark</th>
														
													</tr>';
													
													$q="select f.fl_id,f.fl_cid,f.fl_f_status,f.fl_date,f.f_remark,f.fl_e_id,e.employee_name,fl.f_status_name from tbl_follow as f left join employee_mst as e on f.fl_e_id=e.employee_id left join tbl_followup_status as fl on f.fl_f_status=fl.f_id where f.fl_cid='$id' order by f.fl_id";
														
													$result=$dbcon->query($q);
														
													if(mysqli_num_rows($result)>0)
													{
														$cnt=1;
														while($relf=mysqli_fetch_assoc($result))
														{
													$str.='
													<tr>
														<td>'.$cnt.'</td>
														<td>'.date('d M, Y',strtotime($relf['fl_date'])).'</td>
														<td>'.$relf['f_status_name'].'</td>
														<td>'.$relf['employee_name'].'</td>
														<td>'.$relf['f_remark'].'</td>
													</tr>';
													
													 $cnt++; } } else { 
													
													$str.='
													<tr>
														<th colspan="5" class="redc">No Data Found</th>
													</tr>';
													
													}
													
												 $str.=
												'</table>
												
												<table  class="table table-bordered table-hover table-striped">
					
													<tr>
														<th colspan="8" class="redc">Spare Part History</th>
													</tr>
													
													<tr>
														<th>#</th>
														<th width="30%" class="text-center">Product</th>
														<th width="5%" class="text-center">Qty</th>
														<th width="5%" class="text-center">Rate</th>
														<th width="5%" class="text-center">Amount</th>
														<th width="20%" class="text-center">Courier Name</th>
														<th width="15%" class="text-center">Courier No</th>
														<th width="20%" class="text-center">Expected Delivery Date</th>
													</tr>';
													
													$qs="select pr.s_id,pr.s_comp_id,pr.s_cust_id,pr.s_user_id,pr.s_date,pr.s_product,pr.s_qty,pr.s_rate,pr.s_amount,pr.s_courier_name,pr.s_courier_no,pr.s_courier_del_date,pr.s_status,pm.product_name from tbl_complain_spare_part as pr inner join product_mst as pm on pr.s_product=pm.product_id where pr.s_comp_id=$id";
						
													$result1=$dbcon->query($qs);
													
													if(mysqli_num_rows($result1)>0)
													{
														$cnt=1;
														while($relf1=mysqli_fetch_assoc($result1))
														{
															if($relf1['s_courier_del_date']=='0000-00-00')
															{
																$date="";
															}
															else
															{
																$date=date("d/m/Y",strtotime($relf1['s_courier_del_date']));
															}
															
															if($relf1['s_status']=='2')
															{
																$btn_request='  <button type="button" class="btn btn-round btn-success btn-xs" onclick="request_data_complain('.$relf1['s_id'].');" id="filerequest'.$cnt.'"><i class="fa fa-check-circle"></i></button>';
															}
															else
															{
																$btn_request='';
															}
									
													$str.='
													<tr>
														<td>'.$cnt.'</td>
														<td>'.$relf1['product_name'].'</td>
														<td>'.$relf1['s_qty'].'</td>
														<td>'.$relf1['s_rate'].'</td>
														<td>'.$relf1['s_amount'].'</td>
														<td>'.$relf1['s_courier_name'].'</td>
														<td>'.$relf1['s_courier_no'].'</td>
														<td>'.$date.'</td>
													</tr>';
												
												
													$cnt++; } } else { 
												
												$str.='
												<tr>
													<th colspan="8" class="redc">No Data Found</th>
												</tr>';
												
												}
												
												$str.='
												</table>
												
												<table  class="table table-bordered table-hover table-striped">
					
													<tr>
														<th colspan="7"  class="redc">Old Spare Part History</th>
													</tr>
													
													<tr>
														<th>#</th>
														<th width="10%" class="text-center">Product</th>
														<th width="10%" class="text-center">Quantity</th>
														<th width="10%" class="text-center">Rate</th>
														<th width="10%" class="text-center">Amount</th>
														<th width="30%" class="text-center">Courier Details</th>
														<th width="30%" class="text-center">Remark</th>
													</tr>';
													
													
													$qo="select pr.s_id,pr.sc_comp_id,pr.sc_cust_id,pr.courier_name,pr.courier_no,pr.courier_del_date,pr.sc_user_id,pr.sc_date,pr.sc_product,pr.sc_qty,pr.sc_rate,pr.sc_amount,pr.sc_remark,pm.product_name from tbl_complain_close_spare_part as pr inner join product_mst as pm on pr.sc_product=pm.product_id where pr.sc_comp_id=$id";
														
													$result2=$dbcon->query($qo);
														
													if(mysqli_num_rows($result2)>0)
													{
														$cnt=1;
														while($relf2=mysqli_fetch_assoc($result2))
														{
														
														$str.='
														<tr>
															<td>'.$cnt.'</td>
															<td>'.$relf2['product_name'].'</td>
															<td>'.$relf2['sc_qty'].'</td>
															<td>'.$relf2['sc_rate'].'</td>
															<td>'.$relf2['sc_amount'].'</td>
															<td>
																Courier Name : '.$relf2['courier_name'].'<br>
																Courier No:'.$relf2['courier_no'].'<br>
																Courier Date :'.date("d/m/Y",strtotime($relf2['courier_del_date'])).'<br>
															</td>
															<td>'.$relf2['sc_remark'].'></td>
															
														</tr>';
													
													
													$cnt++; } } else {
													
													$str.='
													<tr>
														<th colspan="6" class="redc">No Data Found</th>
													</tr>';
													 
													 } 
													
													$str.='
												</table>
												
											</div>
										</div>
									</div>
								</div>
							</div>
							
						</center>
					
					</div>';
					
				}
			 }
			 
			 else
			 {
				 $str.="<h3 style='color:red;text-align:center'>No Data Found</h3>";
			 }
			 
			 echo $str;
	}
	else if(strtolower($POST['mode']) == "generate_report_expense")
	{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$emp_id=$POST['emp_id'];
			$exp_id=$POST['exp_id'];
			$comp_id=$POST['comp_id'];
		
			$where='';
			if($s_date[0]==$s_date[1])
			{
				$where.=" and expense_date='".date("Y-m-d")."'";
			}
			else
			{
				$where.=" and expense_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND expense_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
			}
			
			if($emp_id!='')
			{
				$where.=" and exp.emp_id='$emp_id'";
			}
			
			if($exp_id!='')
			{
				$where.=" and exp.exp_accountid='$exp_id'";
			}
			
			if($comp_id!='')
			{
				$where.=" and exp.expense_complain='$comp_id'";
			}
			
			
			$str='';
			$str .='
				
			  <table  class="display table table-bordered table-striped" id="data_list">
			  
			  <tr>
				  <th width="5%" style="text-align:center">Sr. NO.</th>
				  <th width="12%" style="text-align:center">Date</th>
				  <th width="47%" style="text-align:center">Description</th>
				  <th width="47%" style="text-align:center">Employee</th>
				  <th width="12%" style="text-align:center">Amount</th>
			  </tr>
			 <tbody>';
			 $query="select exp.*,l.l_name as exp_name,e.l_name as emp_name from tbl_expense_detail as exp left join tbl_ledger as l on l.l_id=exp.exp_accountid  left join tbl_ledger as e on e.l_id=exp.emp_id where exp.expense_status=0".$where."  order by expense_date desc";
			 $row=$dbcon->query($query);
			 if(mysqli_num_rows($row))
			 {
				$cnt=1;$total=0;
				while($rel=mysqli_fetch_assoc($row))
				{
					
					$str .='<tr>
						<td style="text-align:center">'.$cnt.'</td>
						<td style="text-align:center">'.date('d/m/Y',strtotime($rel['expense_date'])).'</td> 
						<td style="text-align:center">'.$rel['exp_name'].'</td>
						<td style="text-align:center">'.$rel['emp_name'].'</td>
						<td style="text-align:center">'.$rel['paid_amount'].'</td>
					</tr>';
					
					$cnt++;
					$total+=$rel['paid_amount'];
				}
				
				 $str .='<tr>
						<th style="text-align:right;" colspan="4">Total</th>
						<td>'.$total.'</td>
					</tr>';
			 }
			 else
			 {
				 $str .='<tr>
						<td style="text-align:center;color:red" colspan="4">No Data Found</td>
					</tr>';
			 }
			$str .='</tbody>				 
			  </table>';
			 
			 echo $str;
	}
	else if(strtolower($POST['mode']) == "generate_report_emp_ledger")
	{
		//pathik edit
		$s_date=explode(' - ',$POST['date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$emp_id=$POST['emp_id'];
		$usertype=$_SESSION['user_type'];
	
				$str .='
					
				  <table  class="display table table-bordered" id="data_list">
				  <thead>
					
					  <tr>
						  <th width="5%" style="text-align:center">Sr. NO.</th>
						  <th width="10%" style="text-align:center">Date</th>
						  <th width="10%" style="text-align:center">Type</th>
						  <th width="43%" style="text-align:center">Transaction#</th>
						  <th width="10%" style="text-align:center">Debit Amount</th>
						  <th width="10%" style="text-align:center;white-space:nowrap;">Credit Amount</th>
						  <th width="12%" style="text-align:center">Balance</th>
						 
					  </tr>
					  
				  </thead>
				  
				  <tbody>';
				  
					$query="select l.opn_balance,l.balance_typeid,exp_amt.exp_amount,pay_amt.pay_amount from tbl_ledger as l
					
					left join 
						( select sum(paid_amount) as exp_amount,emp_id,expense_date from tbl_expense_detail where expense_approve_status!=2 and expense_status=0 and expense_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by emp_id ) as exp_amt on exp_amt.emp_id=l.l_id
					
					left join 
						( select sum(total_paid_amount) as pay_amount,cust_id,receipt_date from tbl_receipt where status=0 and  receipt_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by cust_id ) as pay_amt on pay_amt.cust_id=l.l_id
						
						where l.l_id='$emp_id'
					
					";
					$rel=mysqli_fetch_assoc($dbcon->query($query));
					
					$op_balance=($rel['balance_typeid']=="2"?(-$rel['opn_balance']):$rel['opn_balance']);//1credit,2debit
					
					$balance=($op_balance+$rel['exp_amount'])-$rel['pay_amount'];
					//$balance=$rel['pay_amount'];
					//$balancetype=$rel['balance_typeid'];
					 
					$str .='<tr>
					<td style="text-align:center"></td>
					<td style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
					<td style="text-align:left" colspan="2">Opening Balance</td>
					<td style="text-align:center"> </td>
					<td style="text-align:center"> </td>';
					if($balance>0)
					{
						$balancetype='CR';
						$clor="color:green";
					}
					else if($balance<0)
					{
							$balancetype='DR';
							$clor="color:red";
					}else{
						$balancetype='';
						$balance='';
						$clor="";
					}
					
					$str .='
					  <td style="text-align:right;'.$clor.'">'.abs($balance).' '.$balancetype.'</td>
					</tr>';
			 $qry='Select * from (
			(Select expense_date as trn_date,paid_amount as total,1 as typeid,"expense" as type_name,ex_id as ex_id,l.l_name as trn_data,lv.l_name as cust_name,null as cheque_dtl from tbl_expense_detail as exp 
			left join tbl_ledger as l on l.l_id=exp.exp_accountid 
			left join tbl_ledger as lv on lv.l_id=exp.vendorid 
			where exp.expense_approve_status!=2 and exp.expense_status=0 and exp.emp_id='.$POST['emp_id'].' and expense_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and expense_date<="'.date('Y-m-d',strtotime($s_date[1])).'" order by expense_date) 
			union  
			(Select receipt_date as trn_date,total_paid_amount as total,2 as typeid,"payment" as type_name,receipt_id as ex_id,p.receipt_no as trn_data ,l.l_name as cust_name,cheque_dtl from tbl_receipt as p 
			left join tbl_ledger as l on l.l_id=p.payment_mode_id
			where p.status=0  and p.cust_id='.$POST['emp_id'].' and p.receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and p.receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" order by receipt_date)
			
			) as data';
			$result1=$dbcon->query($qry);
			$i=1;
				
			if(mysqli_num_rows($result1)>0)
				{
					
					while($re=mysqli_fetch_assoc($result1))
					{
						if($re['typeid']=="1")
						{
							$debit='';
							$credit=$re['total'];
							$transaction=$re['trn_data'].', Customer Name : -'.$re['cust_name'];
							$balance+=$re['total'];
							//$back_color='style=background-color:#FA8072 !important;';
							
						}
						else
						{
							$credit='';
							$debit=$re['total'];
							if(!empty($re['cheque_dtl'])){
								$transaction=$re['trn_data'].' , Payment Account : -'.$re['cust_name'].' ('.$re['cheque_dtl'].')';
							}else{
								$transaction=$re['trn_data'].' , Payment Account : -'.$re['cust_name'];
							}
							$balance-=$re['total'];
							//$back_color='style=background-color:#98FB98 !important;';
						}
							
							if($balance>0){
								$type="CR";
							}else{
								$type="DR";
							}
							
							
							
						$balancetype='';
						$str.='<tr>
						  <td '.$back_color.'>'.$i.'</td>
						  <td '.$back_color.'>'.date('d/m/Y',strtotime($re["trn_date"])).'</td>
						  <td '.$back_color.'>'.$re["type_name"].'</td>
						  <td '.$back_color.'>'.$transaction.'</td>
						  <td '.$back_color.'>'.$debit.'</td>
						  <td '.$back_color.'>'.$credit.'</td>';
						  if($balance>0){
							   $str.='<td style="text-align:right;color:green;">'.abs($balance).' '.$type.'</td>';
						  }else{
							  $str.='<td style="text-align:right;color:red;">'.abs($balance).' '.$type.'</td>'; 
						  }
						 
						 
						  $str.='
						 
				 		</tr>';				
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
	else if(strtolower($POST['mode']) == "delete_expense"){
	    $info['expense_status']		= 2;
		$updateid=update_record('tbl_expense_detail', $info,"ex_id=".$POST['ex_id'] , $dbcon);					
		
			if($updateid)
				echo "1";	
			else
				echo "0";
	}
	else if(strtolower($POST['mode']) == "search_complain_no")
	{
		
		if(!empty($_POST["keyword"])) 
		{
			$query ="SELECT complaint_no FROM tbl_complaint WHERE complaint_status='0' and complaint_no like '" . $_POST["keyword"] . "%' ORDER BY complaint_no";
			$result = $dbcon->query($query);
			$str='';
			if(!empty($result)) 
			{
				$str.='<ul id="country-list">';
					
				foreach($result as $country) 
				{
					$str.='<li onClick="select_data_search(\''.$country['complaint_no'].'\');generate_report_expense()">'.$country["complaint_no"].'</li>';
				}
				
				$str.='</ul>';
			}
			
			echo $str;
			
		}
		
	}
	
?>