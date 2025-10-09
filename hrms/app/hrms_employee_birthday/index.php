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
			$qrycust="select hrmslble.l_id,hrmslble.employee_id,comp.company_name,hrmsemplo.* from tbl_ledger as hrmslble 
					 left join hrms_employee as hrmsemplo on hrmsemplo.id = hrmslble.employee_id
					 left join tbl_company as comp on comp.company_id = hrmsemplo.company_id
					 where hrmsemplo.birth_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND hrmsemplo.birth_date<='".date('Y-m-d',strtotime($s_date[1]))."' and hrmsemplo.company_id = $companyID".check_user('hrmsemplo');
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
						<th width="12%" style="text-align:center">Date Of Birth</th>
						<th width="27%" style="text-align:center">Name</th>
						<th width="27%" style="text-align:center">Email</th>
						<th width="12%" style="text-align:center">Gender</th>
						<th width="12%" style="text-align:center">Status</th>
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
						<td data-label="Date Of Birth" style="text-align:center">'.date('d/m/Y',strtotime($re["birth_date"])).'</td>
						<td data-label="Employee Name" style="text-align:center">'.$re["employee_name"].'</td>
						<td data-label="Employee Name" style="text-align:center">'.$re["emp_email"].'</td>';
					if($re['gender']=='MALE'){
						$str.='<td data-label="GENDER" style="text-align:center;">Male</td>';
					}else if($re['gender']=='FEMALE'){
						$str.='<td data-label="GENDER" style="text-align:center;">FEMALE</td>';
					}else{
						$str.='<td data-label="GENDER" style="text-align:center;">OTHER</td>';
					}
					if($re['status']=='0'){
						$str.='<td data-label="STATUS" style="text-align:center;">Active</td>';
					}else if($re['status']=='1'){
						$str.='<td data-label="STATUS" style="text-align:center;">In Active</td>';
					}		
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