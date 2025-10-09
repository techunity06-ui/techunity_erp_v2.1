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
} else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "fetch") {
	$companyID = $_SESSION['company_id'];
	$userID =  $_SESSION['user_id'];
	$branch=$_SESSION['branch_id'];
	$where='';
	$where.="hrmsatten.status IN (0,1) and hrmsatten.company_id = $companyID".check_user('hrmsatten');
	$appData = array();
	$i=1;
	$aColumns = array('hrmsatten.id','hrmsatten.attendance_date', 'shifttype.shift_type_name', 'hrmsatten.attendance_status', 'hrmsatten.late_entry_flag', 'hrmsatten.early_exit_flag', 'empusers.l_name', 'hrmsatten.series_id', 'hrmsatten.status', 'comp.company_name');
	$sIndexColumn = "hrmsatten.id";
	$isWhere = array($where);
	$sTable = "hrms_attendance as hrmsatten";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id = hrmsatten.company_id", "left join tbl_ledger as empusers on empusers.l_id=hrmsatten.employee_id", "left join tbl_invoicetype as invoicetype on invoicetype.invoicetype_id=hrmsatten.series_id","left join hrms_shift_type as shifttype on shifttype.id=hrmsatten.shift_type_id");
	$hOrder = "hrmsatten.id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;

	$edit_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per = check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['id'];
		$row_data[] = $row['series_id'];
		$row_data[] = $row['company_name'];
		$row_data[] = $row['l_name'];
		$row_data[] = $row['shift_type_name'];
		$row_data[] = $row['attendance_date'];
		$row_data[] = get_approval_status_by_id($dbcon, $row['attendance_status']);
		$row_data[] = $row['late_entry_flag'];
		$row_data[] = $row['early_exit_flag'];
		
		if($row['status']=='0'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
		}else{
			$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
		}
		
		$edit_btn='';$delete_btn='';$change_status='';
		if($row['id']!='0'){ 
			if($edit_btn_per) {
				$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . HRMS_ROOT . 'hrms_attendance_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per) {
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_attendance('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
			}
		}
		if($other_btn_per) {
			if($row['status'] == '0')
			{  
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
		$tr = $dbcon -> query("SELECT `id`,`company_id`,`employee_id`,`attendance_date`,`status` FROM `hrms_attendance` WHERE `employee_id` = '".$POST['employee_id']."' and `attendance_date` = '".date('Y-m-d', strtotime($POST['attendance_date']))."'");
			if($tr->num_rows > 0) {
				$r = $tr -> fetch_assoc();
				if($r['status'] != 0) {
					$info['status']=0;
					$updateid=update_record('hrms_attendance', $info,"id=".$r['id'] , $dbcon);
					if($updateid)
					{
							$row['msg']='1';
					}
					else
					{
							$row['msg']='0';
					}
				}else{
						$row['msg']='-1';
				}	
		} else {
			// Add New Attendance List
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['series_id']	= $POST['series_id'];
			$info['employee_id'] = $POST['employee_id'];
			$info['shift_type_id']	= $POST['shift_type_id'];
			$info['attendance_date'] = date('Y-m-d', strtotime($POST['attendance_date']));
			$info['attendance_status'] = $POST['attendance_status'];
			$info['late_entry_flag'] = ($POST['late_entry_flag'])?$POST['late_entry_flag']:'No';
			$info['early_exit_flag'] = ($POST['early_exit_flag'])?$POST['early_exit_flag']:'No';
			$info['status']	= $POST['status'];
			$info['updated_at']	= date("Y-m-d H:i:s");
			$inserattenid = add_record('hrms_attendance', $info, $dbcon);

			$query = $dbcon->query("SELECT `id` FROM `hrms_attendance`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'ATTENDANCE'" , $dbcon);
			
			if($inserattenid)
			{
				if(strtolower($POST['model'])=="model")
				{
					$query="select * from hrms_attendance where id=".$inserattenid;
					$rel=mysqli_fetch_assoc($dbcon->query($query));		
					$row = $rel;
					$row['msg']="2"; 
				}
				else
				{
					$row['msg'] ="1";
				}
			}
			else
			{
				$row['msg'] ="0";
			}
				
		}
		echo json_encode($row);
}else if(strtolower($POST['mode']) == "edit") {

	// Edit New Attendance List
	$info['employee_id'] = $POST['employee_id'];
	$info['series_id']	= $POST['series_id'];
	$info['shift_type_id']	= $POST['shift_type_id'];
	$info['attendance_date'] = date('Y-m-d', strtotime($POST['attendance_date']));
	$info['attendance_status'] = $POST['attendance_status'];
	$info['late_entry_flag'] = ($POST['late_entry_flag'])?$POST['late_entry_flag']:'No';
	$info['early_exit_flag'] = ($POST['early_exit_flag'])?$POST['early_exit_flag']:'No';
	$info['status']	= $POST['status'];
	$info['updated_at']	= date("Y-m-d H:i:s");
	$updateattenid = update_record('hrms_attendance', $info,"id=".$POST['eid'] , $dbcon);

	if($updateattenid){	
		$arr['msg']="1";
	}else{
		$arr['msg']="0";
	}
	echo json_encode($arr);	
}
else if(strtolower($POST['mode'])== "delete") {
	$row=array();
	$info['status'] = 2;
	$updateid=update_record('hrms_attendance', $info, "id=".$POST['eid'] , $dbcon);
	if($updateid)
		$row['msg']="1";
	else
		$row['msg']="0";
	
	echo json_encode($row);
}	
else if(strtolower($POST['mode']) == "check_data"){
	$row[] ='';
	if(!empty($_FILES['excel_file']['tmp_name']))
	{
		$file_name = $_FILES['excel_file']['name'];
		$err = $_FILES["excel_file"]["tmp_name"];
		$exts = array('csv'); 
		if(in_array(end(explode('.', $file_name)), $exts))
		{
			move_uploaded_file($err,ATTENDANCE_UPLOAD.$file_name);
			$handle = fopen(ATTENDANCE_UPLOAD.$file_name, "r");
			$row = check_upload_new($file_name,$dbcon);
		}
		else
		{
			$row['res'] = "-1";
		}
	} else {
		$row['res'] ='0';
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "import_data") {
	if(!empty($_FILES['excel_file']['tmp_name']))
	{
		$file_name = $_FILES['excel_file']['name'];
		$err = $_FILES["excel_file"]["tmp_name"];
		move_uploaded_file($err,ATTENDANCE_UPLOAD.$file_name);
		$handle = fopen(ATTENDANCE_UPLOAD.$file_name, "r");
		($data = fgetcsv($handle,","));//get field rows
		$i=1;$error_array=array();
		while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
		{
			$error='';
			if(!empty($data['0'])) {
				//start
				$employee_id = $attendance_date = $attendance_status = $shift_type_id = $late_entry_flag = $early_exit_flag = '';
				$yesNoArr = array('Yes', 'No');

				$employee_row = trim($data['0']);
				$attendance_row = trim($data['1']);
				$attendance_row = date('Y-m-d', strtotime($attendance_row));
				$approval_status_row = trim($data['2']);
				$shift_type_row = trim($data['3']);
				$late_entry_row = trim($data['4']);
				$early_exit_row = trim($data['5']);
				$series_id = get_series_by_type($dbcon, 'ATTENDANCE', '16');

				//Row 1
				$empQuery = "SELECT `l_id`,`l_name` FROM `tbl_ledger` WHERE l_status=0 AND `l_name` ='".$employee_row."'";
			 	$ledgerData = mysqli_fetch_array($dbcon -> query($empQuery));

				if(!empty($ledgerData)) {
					$employee_id = $ledgerData['l_id'];
				} else {
					$error = 'Employee Name Not Found';
					array_push($error_array,1);
				}

				//Row 2
				$dtQuery = "SELECT `employee_id`,`attendance_date` FROM `hrms_attendance` WHERE status = '0' AND `employee_id` ='".$employee_id."' AND attendance_date = '".$attendance_row."'";
				$dtRow = $dbcon->query($dtQuery);
				$dtCheck = $dtRow->num_rows;

				if($dtCheck <= 0) {
					$attendance_date = $attendance_row;
				} else {
					$error = 'Attendance Date already added for this employee';
					array_push($error_array,1);
				}

				//Row 3
				$aprQuery = "SELECT `id`,`status_name` FROM `hrms_approval_status_master` WHERE status = '0' AND `status_name` ='".$approval_status_row."'";
			 	$aprData = mysqli_fetch_array($dbcon -> query($aprQuery));

				if(!empty($aprData)) {
					$attendance_status = $aprData['id'];
				} else {
					$error = 'Status Not Found';
					array_push($error_array,1);
				}

				//Row 4
				$shiftQuery = "SELECT `id`,`shift_type_name` FROM `hrms_shift_type` WHERE status = '0' AND `shift_type_name` ='".$shift_type_row."'";
			 	$shiftData = mysqli_fetch_array($dbcon -> query($shiftQuery));

				if(!empty($shiftData)) {
					$shift_type_id = $shiftData['id'];
				} else {
					$error = 'Shift Type Not Found';
					array_push($error_array,1);
				}

				//Row 5
				if(in_array($late_entry_row, $yesNoArr)) {
					$late_entry_flag = $late_entry_row;
				} else {
					$error = 'Late Entry Flag Not Found';
					array_push($error_array,1);
				}

				//Row 6
				if(in_array($early_exit_row, $yesNoArr)) {
					$early_exit_flag = $early_exit_row;
				} else {
					$error = 'Late Entry Flag Not Found';
					array_push($error_array,1);
				}

				$info['series_id'] = $series_id;
				$info['employee_id'] = $employee_id;
				$info['attendance_date'] = $attendance_date;
				$info['attendance_status'] = $attendance_status;
				$info['shift_type_id'] = $shift_type_id;
				$info['late_entry_flag'] = $late_entry_flag;
				$info['early_exit_flag'] = $early_exit_flag;
				$info['user_id'] = $_SESSION['user_id'];
				$info['company_id'] = $_SESSION['company_id'];
				
				//end
			} else {
				$error = 'Blank Row';
				array_push($error_array,1);
			}

			if(empty($error)) {
				add_record('hrms_attendance', $info, $dbcon);
				updateSeries($dbcon, 'id', 'hrms_attendance', 'ATTENDANCE');
			}

			$i++;	
		}

		if(in_array(1,$error_array)) {
			$result['res']='5';
			$result['msg']= $error;
		} else {
			$result['res']='4';
		}

		fclose($handle);//close file reading
	} else {
		$result['res']='0';
	}
	
	echo  json_encode($result);
}
else if(strtolower($POST['mode']) == "change_status") {
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	
	$updateid = update_record('hrms_attendance', $info,"id=".$POST['eid'] , $dbcon);
	echo ($updateid) ? "1" : "0";
}	
function check_upload_new($filename,$dbcon)
{
	$error=array();
	$arr = explode(".", $filename);
	$fp = fopen(ATTENDANCE_UPLOAD.$filename, 'r');
	$frow = fgetcsv($fp);
	if(count($frow)==6) // Define coulmn count Here
	{
		$msg='';
		foreach($frow as $i)
		if (!in_array($i, array('Employee Name','Attendance Date','Status','Shift Type','Late Entry','Early Exit'), true)) 
		{
			$msg='error';
		}
		
		if(!empty($msg))
		{
			$error['res']="3";
		} else {
			// delete_record('cust_tempdata', 'company_id='.$_SESSION['company_id'], $dbcon);
			$error['res']="1";
		}
	} else {
		$error['res']="0";
	}

	return $error;
}
?>