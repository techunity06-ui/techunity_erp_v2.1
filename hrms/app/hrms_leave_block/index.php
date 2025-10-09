<?php
session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");

		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "fetch") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$where='';
			$appData = array();
			$i=1;
			$aColumns = array('leaveblocklist.id','leaveblocklist.leave_block_list_name','leaveblocklist.applied_to_company_flag','com.company_name','leaveblocklist.status');
			$sIndexColumn = "leaveblocklist.id";
			$isWhere = array("leaveblocklist.status IN (0,1) and leaveblocklist.company_id = $companyID".check_user('leaveblocklist'));
			$sTable = "hrms_leave_block_list as leaveblocklist";			
			$isJOIN = array('left join tbl_company as com on com.company_id=leaveblocklist.company_id');
			$hOrder = "leaveblocklist.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['leave_block_list_name'];
				$row_data[] = $row['applied_to_company_flag'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){ 
					if($edit_btn_per) {
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . ROOT . HRMS_ROOT . 'hrms_leave_block_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if($delete_btn_per) {
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_leave_block('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($row['status'] == '0')
				{  
					$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
				} else {
					$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
				}
					
				$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['leave_block_list_name']	= $POST['leave_block_list_name'];
			$info['applied_to_company_flag'] = ($POST['applied_to_company_flag'])?$POST['applied_to_company_flag']:'No';
			$info['status'] = $POST['status'];
			$inserestimateid=add_record('hrms_leave_block_list', $info, $dbcon);
					
			$info_update['status']	= 0;
			$info_update['leave_block_id']	= $inserestimateid;
			$updateblockid=update_record('hrms_leave_block_day', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
			$updateallowusersid=update_record('hrms_leave_block_allow_users', $info_update,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);
					
			if($inserestimateid){	
				$arr['msg']="1";
				$arr['eid']=$inserestimateid;
			}else{
				$arr['msg']="0";
			}
			echo json_encode($arr);
		}		
		else if(strtolower($POST['mode']) == "edit") {
			$info['leave_block_list_name']	= $POST['leave_block_list_name'];
			$info['applied_to_company_flag'] = ($POST['applied_to_company_flag'])?$POST['applied_to_company_flag']:'No';
			$info['status'] = $POST['status'];
			
			$updateid=update_record('hrms_leave_block_list', $info,"id=".$POST['eid'] , $dbcon);
			
			if($updateid){	
				$arr['msg']="update";
				$arr['eid']=$POST['eid'];
			}else{
				if($updateid == 0){
					$arr['msg']="update";
					$arr['eid']=$POST['eid'];
				}else{
					$arr['msg']=0;
				}
			}
			echo json_encode($arr);	
				
			
		}
		else if(strtolower($POST['mode']) == "delete") {
					
			$info['status']	= 2;
			$info1['status'] = 2;
			$updateblockid=update_record('hrms_leave_block_list', $info,"id=".$POST['eid'] , $dbcon);	
			$updateblockdayid=update_record('hrms_leave_block_day', $info1,"leave_block_id=".$POST['eid'] , $dbcon);
			$updateblockallowusersid=update_record('hrms_leave_block_allow_users', $info1,"leave_block_id=".$POST['eid'] , $dbcon);				
			if($updateblockallowusersid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "fieldadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['block_date']	= date('Y-m-d', strtotime($POST['block_date']));
				$info1['block_reason']	= stripslashes($POST['block_reason']);
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['leave_block_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_leave_block_day';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				}
			
		}
		else if(strtolower($POST['mode']) == "fieldblockadd") {
				$info1['user_id'] =  $_SESSION['user_id'];
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['employee_id']	= $POST['employee_id'];
				if(empty($POST['edit_id'])){
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
						$info1['leave_block_id'] = $POST['eid'];
					}
				}else{
					if(empty($POST['eid'])){
						$info1['status']	= '3';
					}else{
						$info1['status']	= '0';
					}
				}
				$table='hrms_leave_block_allow_users';
				$tableid='id';
				
				if(empty($POST['edit_id'])){
					$inserid=add_record($table, $info1, $dbcon);
				}else{
					$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
					$inserid=$POST['edit_id'];
				}
			
		}
		else if(strtolower($POST['mode'])== "load_productdata")
		{
			$pid=$POST['eid'];
			//$qry="select * from tbl_product where product_id=".$POST['eid'];
			$qry="select * from product_mst where product_id=$pid";
			$result=$dbcon->query($qry);
			$row=mysqli_fetch_assoc($result);
			
			$qry1="select led.stateid as lst,com.stateid as cst from tbl_ledger as led 
				left join tbl_company as com on com.company_id=led.company_id
				where l_id=".$POST['cust_id'];
			$result1=$dbcon->query($qry1);
			$row1=mysqli_fetch_assoc($result1);
			
			if($row1['lst']==$row1['cst']){
				$qry2="select * from formula_mst as led 
						where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}else{
				$qry2="select * from formula_mst as led 
						where formula_status=0 and tax_cat='INTER' and tax_per_id=".$row['product_sale_gst'];
				$result2=$dbcon->query($qry2);
				$row2=mysqli_fetch_assoc($result2);
				$row['fom_id']=$row2['formulaid'];
			}
					
			echo json_encode( $row );
		
		}		
		else if(strtolower($POST['mode']) == "load_tempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['lb_id'])){
				$query="select * from hrms_leave_block_day as blockday 
				left join tbl_company as comp on comp.company_id = blockday.company_id
		 		where `blockday`.`status` = 3 and `blockday`.`user_id` = $userID and `blockday`.`company_id` = $companyID";
			}else{
				 $query="select * from hrms_leave_block_day as blockday 
				left join tbl_company as comp on comp.company_id = blockday.company_id
		 		where `blockday`.`status` = 0 and `blockday`.`leave_block_id`=".$POST['lb_id']." and `blockday`.`user_id` = $userID and `blockday`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th class="text-center"width="10%">Block Date</th>
							<th class="text-center"width="25%">Block Reason</th>
						 	<th class="text-center"width="3%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Block Date" style="vertical-align:top;" class="text-center">';
								if(empty($rel['block_date'])){
									echo '-';
								}else{
									echo date('d-m-Y', strtotime($rel['block_date']));
								}
						echo'</td>
						<td data-label="Block Reason" style="vertical-align:top;" class="text-center">
							'.$rel['block_reason'].'
						</td>
						<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
						</td>	
				</tr>';
				$i++;
			}
		}else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
			echo '</table>			 
					</div>
                        </div>';
		}
		else if(strtolower($POST['mode']) == "load_blocktempoutward") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['lb_id'])){
				$query="select blockallowday.*,empusers.l_name from hrms_leave_block_allow_users as blockallowday 
				left join tbl_company as comp on comp.company_id = blockallowday.company_id
				left join tbl_ledger as empusers on empusers.l_id=blockallowday.employee_id
		 		where `blockallowday`.`status` = 3 and `blockallowday`.`user_id` = $userID and `blockallowday`.`company_id` = $companyID";
			}else{
				 $query="select blockallowday.*,empusers.l_name from hrms_leave_block_allow_users as blockallowday 
				left join tbl_company as comp on comp.company_id = blockallowday.company_id
				left join tbl_ledger as empusers on empusers.l_id=blockallowday.employee_id
		 		where `blockallowday`.`status` = 0 and `blockallowday`.`leave_block_id`=".$POST['lb_id']." and `blockallowday`.`user_id` = $userID and `blockallowday`.`company_id` = $companyID";
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
							  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th class="text-center"width="35%">Allow User</th>
						 	<th class="text-center"width="3%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
						<td data-label="Employee Name" style="vertical-align:top;" class="text-center">';
								if(empty($rel['l_name'])){
									echo '-';
								}else{
									echo $rel['l_name'];
								}
						echo'</td>
						<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_block_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_block_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
						</td>	
				</tr>';
				$i++;
			}
		}else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
			echo '</table>			 
					</div>
                        </div>';
		}
		else if(strtolower($POST['mode'])== "preedit")
		{
			$ids = $POST['id'];
			$q = $dbcon -> query("select * from hrms_leave_block_day as blockday 
				left join tbl_company as comp on comp.company_id = blockday.company_id
		 		where id = $ids");

			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "preblockedit")
		{
			$ids = $POST['id'];
			$q = $dbcon->query("select `blockallowusers`.*, `empusers`.l_name from hrms_leave_block_allow_users as blockallowusers 
				left join tbl_company as comp on comp.company_id = blockallowusers.company_id
				left join tbl_ledger as empusers on empusers.l_id = blockallowusers.employee_id
		 		where `blockallowusers`.`id` = $ids");
			
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_leave_block_day", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_block_data")
		{
			$row=array();
			$info['status'] = 2;	
			$updateid=update_record("hrms_leave_block_allow_users", $info,"id=".$POST['eid'] , $dbcon);
			if($updateid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			$updateid = update_record('hrms_leave_block_list', $info,"id=".$POST['eid'] , $dbcon);
			
			echo ($updateid) ? "1" : "0";
		}
?>