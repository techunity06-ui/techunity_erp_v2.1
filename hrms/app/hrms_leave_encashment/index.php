<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
{ 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		} else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$aColumns = array('leaveencash.id', 'leaveencash.encashment_date', 'leaveencash.status', 'leavetype.leave_type_name', 'comp.company_name', 'empusers.l_name', 'leaveperiod.id');
			$sIndexColumn = "leaveencash.id";
			$isWhere = array("leaveencash.status IN (0,1) and leaveencash.company_id = $companyID".check_user('leaveencash'));
			$sTable = "hrms_leave_encashment as leaveencash";			
			$isJOIN = array("left join tbl_company as comp on comp.company_id=leaveencash.company_id","left join hrms_leave_type as leavetype on leavetype.id=leaveencash.leave_type_id","left join tbl_ledger as empusers on empusers.l_id=leaveencash.employee_id","left join hrms_leave_period as leaveperiod on leaveperiod.id=leaveencash.leave_period_id");
			$hOrder = "leaveencash.id ASC";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;

			$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
			$other_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['l_name'];
				$row_data[] = $row['id'].' - HRMS-LP';
				$row_data[] = $row['leave_type_name'];
				$row_data[] = $row['encashment_date'];

				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}

				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){  
					if($edit_btn_per) {
						$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_leave_encashment('.$row['id'].');"><i class="fa fa-pencil"></i></button>'; 
					}
					if($delete_btn_per) {
						$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_leave_encashment('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
					}
				}
				if($other_btn_per) {
					if($row['status'] == '0'){  
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					} else {
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
				$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			
				$row['res']='';
				$tr = $dbcon -> query("SELECT `id`, `company_id`, `employee_id`, `leave_period_id`, `leave_type_id`, `encashment_date`, `status` FROM `hrms_leave_encashment` WHERE `leave_period_id` = '$POST[leave_period_id]'");
						if($tr->num_rows > 0) {
							$r = $tr -> fetch_assoc();
							if($r['status'] != 0) {
								$info['status']=0;
								$updateid=update_record('hrms_leave_encashment', $info,"id=".$r['id'] , $dbcon);
									if($updateid)
									{
											$row['res']='1';
									}
									else
									{
											$row['res']='0';
									}
							}
							else 
							{
									$row['res']='-1';
							}	
					} else {
							$info['user_id']	= $_SESSION['user_id'];
							$info['company_id']	= $_SESSION['company_id'];
							$info['employee_id']	= $POST['employee_id'];
							$info['leave_period_id']= $POST['leave_period_id'];
							$info['leave_type_id']= $POST['leave_type_id'];
							$info['encashment_date'] = date('Y-m-d', strtotime($POST['encashment_date']));
							$info['updated_at']	= date("Y-m-d H:i:s");
							$info['status']	= $POST['status'];
							$inserid = add_record('hrms_leave_encashment', $info, $dbcon);
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from hrms_leave_encashment where id=".$inserid;
							$rel=mysqli_fetch_assoc($dbcon->query($query));		
							$row = $rel;
							$row['res']="2"; 
						}
						else
						{
							$row['res'] ="1";
						}
					}
					else
					{
						$row['res'] ="0";
					}
					
				}
				echo json_encode($row);
			
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `hrms_leave_encashment` WHERE `id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['employee_id']= $POST['employee_id'];
				$info['leave_period_id']= $POST['leave_period_id'];
				$info['leave_type_id']= $POST['leave_type_id'];
				$info['encashment_date'] = date('Y-m-d', strtotime($POST['encashment_date']));
				$info['updated_at']	= date("Y-m-d H:i:s");
				$info['status']	= $POST['status'];				
				$updateid=update_record('hrms_leave_encashment', $info,"id=".$POST['eid'] , $dbcon);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$info['status']='2';
				$updateid=update_record('hrms_leave_encashment', $info,"id=".$POST['eid'] , $dbcon);
				if($updateid)
					echo "1";
				else
					echo "0";
			}
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('hrms_leave_encashment', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
    }
}
