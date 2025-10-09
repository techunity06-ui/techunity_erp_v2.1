<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "generate_report_emp_leave_balance")
	{
		$s_date=explode(' - ',$POST['date']);
		$start_date=date("Y-m-d",strtotime($s_date[0]));
		$end_date=date("Y-m-d",strtotime($s_date[1]));
		$user_id=$POST['user_id'];
		$companyID = $_SESSION['company_id'];
		$userID =  $_SESSION['user_id'];
		
		$whr='';
		$whr.=" and DATE_FORMAT(hrmsleavebalance.leave_balance_date,'%Y-%m-%d')>='".$start_date."' and DATE_FORMAT(hrmsleavebalance.leave_balance_date,'%Y-%m-%d')<='".$end_date."'".check_user('hrmsleavebalance');
		
		
		$str.='<table class="display table table-bordered" id="data_list">
		  <thead>
			  <tr>
				  <th width="5%" style="text-align:center">Sr. NO.</th>
				  <th width="10%" style="text-align:center">Leave Type</th>
				  <th width="10%" style="text-align:center">Employee</th>
				  <th width="10%" style="text-align:center">Employee Name</th>
				  <th width="10%" style="text-align:center">Opening Balance</th>
				  <th width="10%" style="text-align:center">Leave Allocated</th>
				  <th width="10%" style="text-align:center">Leave Taken</th>
				  <th width="10%" style="text-align:center">Leave Expired</th>
				  <th width="10%" style="text-align:center">Closing Balance</th>
			  </tr>
		  </thead>
		  <tbody>';
				  
		$query="SELECT hrmsleavebalance.*,comp.company_name,empusers.l_name,leavetype.leave_type_name 
				FROM hrms_employee_leave_balance as hrmsleavebalance
				left join tbl_company as comp on comp.company_id=hrmsleavebalance.company_id
				left join tbl_ledger as empusers on empusers.l_id=hrmsleavebalance.employee_id
				left join hrms_leave_type as leavetype on leavetype.id=hrmsleavebalance.leave_type_id
				WHERE hrmsleavebalance.company_id = $companyID and hrmsleavebalance.employee_id=$user_id ".$whr." GROUP BY employee_id";
		$query_rs=($dbcon->query($query));
		if(mysqli_num_rows($query_rs)>0){
			$i=1;
			while($rel=mysqli_fetch_assoc($query_rs))
			{
				$str .='
					<tr>
						<td>'.$i.'</td>
						<td>'.$rel['leave_type_name'].'</td>
						<td>'.$rel['l_name'].'</td>
						<td>'.$rel['l_name'].'</td>
						<td>'.$rel['opening_balance'].'</td>
						<td>'.$rel['leave_allocated'].'</td>
						<td>'.$rel['leave_taken'].'</td>
						<td>'.$rel['leave_expired'].'</td>
						<td>'.$rel['closing_balance'].'</td>';
				$str .='</tr>';
				$i++;
			}
		} else {
			$str .='<tr>
				<td colspan="11" style="text-align:center">NO DATA FOUND</td>
			</tr>';
		}
		$str .='</tbody>				 
				</table>';	  
		echo $str;	
	}
?>