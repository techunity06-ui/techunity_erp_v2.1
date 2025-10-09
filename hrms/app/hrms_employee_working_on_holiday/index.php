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
		}else{
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report") 
		{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select hrmsholiday.id,hrmsholiday.holiday_name,comp.company_name,hholida.holiday_date,hrmsatten.attendance_date,hrmsatten.employee_id,hrmsledger.employee_id,hrmsemplo.employee_name,hrmsemplo.emp_email,hrmsemplo.series_id,hrmsatten.attendance_status from hrms_holiday_list as hrmsholiday
					 left join hrms_holiday as hholida on hholida.holiday_id = hrmsholiday.id 
					 left join tbl_company as comp on comp.company_id = hrmsholiday.company_id
					 left join hrms_attendance as hrmsatten on hrmsatten.attendance_date = hholida.holiday_date
					 left join tbl_ledger as hrmsledger on hrmsledger.l_id = hrmsatten.employee_id
					 left join hrms_employee as hrmsemplo on hrmsemplo.id = hrmsledger.employee_id
					 where hrmsatten.attendance_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND hrmsatten.attendance_date<='".date('Y-m-d',strtotime($s_date[1]))."' and hrmsholiday.company_id = $companyID".check_user('hrmsholiday');
			$employee_rel = $dbcon->query($qrycust);		
			$str .='<table  class="display table table-bordered table-striped" id="data_list">
					<tr id="logo" class="logo" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr>
						<th width="5%" style="text-align:center">Sr. NO.</th>
						<th width="12%" style="text-align:center">Series ID</th>
						<th width="12%" style="text-align:center">Company</th>
						<th width="12%" style="text-align:center">Attendance Date</th>
						<th width="27%" style="text-align:center">Employee Name</th>
						<th width="27%" style="text-align:center">Employee Email</th>
						<th width="12%" style="text-align:center">Attendance Status</th>
						<th width="12%" style="text-align:center">Holiday Name</th>
					</tr>
					<tbody>';
			$i=1;
			if(mysqli_num_rows($employee_rel)>0)
			{
				while($re=mysqli_fetch_assoc($employee_rel))
				{
					$str.='<tr>
					  	<td data-label="SR. NO." style="text-align:center">'.$i.'</td>
					  	<td data-label="Series ID." style="text-align:center">'.$re['series_id'].'</td>
					  	<td data-label="Company Name" style="text-align:center">'.$re['company_name'].'</td>
						<td data-label="Date Of Birth" style="text-align:center">'.date('d/m/Y',strtotime($re["attendance_date"])).'</td>
						<td data-label="Employee Name" style="text-align:center">'.$re["employee_name"].'</td>
						<td data-label="Employee Name" style="text-align:center">'.$re["emp_email"].'</td>';
					if($re['attendance_status']=='1'){
						$str.='<td data-label="ATTENDANCE STATUS" style="text-align:center;">Present</td>';
					}else if($re['attendance_status']=='2'){
						$str.='<td data-label="ATTENDANCE STATUS" style="text-align:center;">Absent</td>';
					}else if($re['attendance_status']=='3'){
						$str.='<td data-label="ATTENDANCE STATUS" style="text-align:center;">On Leave</td>';
					}else if($re['attendance_status']=='4'){
						$str.='<td data-label="ATTENDANCE STATUS" style="text-align:center;">Half Day</td>';
					}else{
						$str.='<td data-label="ATTENDANCE STATUS" style="text-align:center;">Work From Home</td>';
					}
					$str.='<td data-label="HOLIDAY NAME" style="text-align:center;">'.$re["holiday_name"].'</td>';
					$str.='</tr>';		
					$i++;
				}
			} else {
				$str .='<tr><td colspan="10" style="text-align:center">NO DATA FOUND</td></tr>';
			} 
			$str .='</tbody></table>'; 
			echo $str;
		}
		
   
?>