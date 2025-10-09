<?php
/**
p() - echo exist function
get_all_departments() - Get All Department Data
get_leave_policy() - Get Leave Policy Data
get_salary_structure() - Get Salary Structure Data
get_departments() - Get Specific Department Data
getStatusOptions() - Get Status Data
get_approval_status() - Get Approval Status Data
get_approval_status_by_id() - Get Approval Status By ID Data
get_department_name_by_id() - Get Department Name By ID Data
get_branch_name_by_id() - Get Branch Name By ID Data
get_zone_name_by_id() - Get Zone Name By ID Data
get_series_by_type() - Get Series By Type Data
updateSeries() - Update Series Data
get_shift_type_by_id() - Get Shift Type By ID Data
getUserFromEmployee() - Get User From Employee Data
getEmailTemplate() - Get Email Template Data
getleaveType() - Get Leave Type Data
getEmployeeSync() - Get Employee Sync Data
**/

//ankit function start
function p($val, $isexit = true) {
    echo '<pre>';
    print_r($val);
    echo '</pre>';
    if($isexit) {
        die();
    }
}

function get_all_departments($dbcon,$id,$where='',$showPrimary = false)
{
	$str='';
	$query="SELECT id, department_name FROM hrms_department where status IN ('0','1') AND is_group = 'Yes' ".$where;
	$rs_type=$dbcon->query($query);
    if($id=='0'){ $psel='selected="selected"';}
	$str ='<option value="" >--Choose Department--</option>';
	if($showPrimary) {		
		$str.='<option value="0" '.$psel.' >PRIMARY</option>';
	}
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['id'].'">'.$row['department_name'].'</option>';
	}
	return $str;
}


function get_leave_policy($dbcon,$id){
    $query="select id from hrms_leave_policy where user_id = '".$_SESSION['user_id']."' AND company_id = '".$_SESSION['company_id']."' AND status IN ('0','1') ";
	$rs_dispatch=$dbcon->query($query);	
	$str = '<option value="">Please Select</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['id']==$id)
		{$sel='selected="selected"';}

		$str.= '<option '.$sel.' value="'.$rel['id'].'">'.$rel['id'].'</option>';
	}
	return $str;
}

function get_salary_structure($dbcon,$id){
    $query="select id, salary_structure_name from hrms_salary_structure where user_id = '".$_SESSION['user_id']."' AND company_id = '".$_SESSION['company_id']."' AND status IN ('0','1') ";
	$rs_dispatch=$dbcon->query($query);
	$str = '<option value="">Please Select</option>';
	while($rel=mysqli_fetch_assoc($rs_dispatch))
	{
		$sel='';
		if($rel['id']==$id)
		{$sel='selected="selected"';}

		$str.= '<option '.$sel.' value="'.$rel['id'].'">'.$rel['salary_structure_name'].'</option>';
	}
	return $str;
}

function get_departments($dbcon,$id,$where='',$showPrimary = false)
{
	$str='';
	$query="SELECT id, department_name FROM hrms_department where status IN ('0','1') ".$where;
	$rs_type=$dbcon->query($query);
    if($id=='0'){ $psel='selected="selected"';}
	$str ='<option value="">Please Select</option>';
	if($showPrimary) {		
		$str.='<option value="0" '.$psel.' >PRIMARY</option>';
	}
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['id'].'">'.$row['department_name'].'</option>';
	}
	return $str;
}

function getStatusOptions($id)
{
	$option = '<option value="0" '.($id == '0' ? 'selected="selected"' : '').'>Active</option>';
	$option .= '<option value="1" '.($id == '1' ? 'selected="selected"' : '').'>Inactive</option>';

	return $option;
}


function get_approval_status($dbcon,$id,$where='')
{
	$str='';
	$query="SELECT id, status_name FROM hrms_approval_status_master WHERE status IN ('0','1') ".$where;
	$rs_type=$dbcon->query($query);
    
	$str ='<option value="">SELECT ATTENDANCE STATUS</option>';
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$sel='';
		if($row['id']==$id)
		{$sel='selected="selected"';}
		
		$str .= '<option '.$sel.' value="'.$row['id'].'">'.$row['status_name'].'</option>';
	}
	return $str;
}

function get_approval_status_by_id($dbcon, $id)
{
	$query = "SELECT id, status_name FROM hrms_approval_status_master WHERE status IN ('0','1') AND id = $id";
	// return $query;
	$row = $dbcon->query($query);
	$rel = mysqli_fetch_array($row);
  
	return ($rel && $rel['status_name']) ? $rel['status_name'] : '-';
}

function get_department_name_by_id($dbcon, $id)
{
	$query = "SELECT id, department_name FROM hrms_department WHERE status IN ('0','1') AND id = '$id'";
	$row = $dbcon->query($query);
	$rel = mysqli_fetch_array($row);
  
	return ($rel && $rel['department_name']) ? $rel['department_name'] : '-';
}

function get_branch_name_by_id($dbcon, $id)
{
	$query = "SELECT branch_id, branch_name FROM branch_mst WHERE branch_status IN ('0','1') AND branch_id = '$id'";
	$row = $dbcon->query($query);
	$rel = mysqli_fetch_array($row);
  
	return ($rel && $rel['branch_name']) ? $rel['branch_name'] : '-';
}

function get_zone_name_by_id($dbcon, $id)
{
	$query = "SELECT zone_id, zone_name FROM zone_mst WHERE zone_status IN ('0','1') AND zone_id = '$id'";
	$row = $dbcon->query($query);
	$rel = mysqli_fetch_array($row);
  
	return ($rel && $rel['zone_name']) ? $rel['zone_name'] : '-';
}

function get_series_by_type($dbcon, $invoice_type, $type_id)
{
	$series_id = '';
	$query = "SELECT `invoicetype_id`,`taxinvoice_start`,`format_value`,`end_format_value` FROM `tbl_invoicetype` WHERE `status` = 0 AND `invoice_type` = '$invoice_type' AND company_id = '".$_SESSION['company_id']."' AND `type_id` = '$type_id' ORDER BY invoicetype_id ";
	$data = $dbcon->query($query);
	$r = $data->fetch_assoc();
	$series_id = $r['format_value'] . $r['taxinvoice_start'] . $r['end_format_value'];

	return $series_id;
}

function updateSeries($dbcon, $field, $table, $invoice_type)
{
	// Series Number Update Code
	$qry = "SELECT $field FROM $table";
	$query = $dbcon->query($qry);
	$total_records = $query->num_rows;
	$updateInfo['taxinvoice_start'] = $total_records;
	$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = '$invoice_type'" , $dbcon);
}

function get_shift_type_by_id($dbcon, $id)
{
	$query = "SELECT id, shift_type_name FROM hrms_shift_type WHERE status = '0' AND id = '$id'";
	$row = $dbcon->query($query);
	$rel = mysqli_fetch_array($row);
  
	return ($rel && $rel['shift_type_name']) ? $rel['shift_type_name'] : '-';
}

function getUserFromEmployee($dbcon,$id)
{
	$query="select * from users where employee_id=$id";
	$rs_cust=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_cust);
	return $rel;
}
function getEmailTemplate($dbcon,$emailType)
{
	$query="select * from hrms_email_template where email_template_name='$emailType'";
	$rs_email=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_email);
	return $rel;
}
function getleaveType($dbcon,$leaveType)
{
	$query="select * from hrms_leave_type where id='$leaveType'";
	$rs_leave_type=$dbcon->query($query);	
	$rel=mysqli_fetch_array($rs_leave_type);
	return $rel;
}
function getEmployeeSync($dbcon){
	$query="SELECT l.*, u.user_mail, u.user_key, u.user_phone, u.user_type FROM `tbl_ledger` as l JOIN users as u ON u.employee_id = l.l_id WHERE l.`l_group` = '58'";
	$rs_type=$dbcon->query($query);
	while($row=mysqli_fetch_assoc($rs_type))
	{	
		$series_id = get_series_by_type($dbcon, 'EMPLOYEE', '16');
		$birth_date = '1990-10-30';
		$joining_date = '2015-10-30';

		// Insert Code HRMS Employee Table
		$info['user_id'] = $row['user_id'];
		$info['company_id'] = $row['company_id'];
		$info['series_id'] = $series_id;
		$info['employee_name'] = $row['l_name'];
		$info['birth_date'] = date('Y-m-d',strtotime($birth_date));
		$info['joining_date'] = date('Y-m-d',strtotime($joining_date));
		$info['gender'] = 'MALE';
		$info['country_id']	= $row['countryid'];
		$info['state_id'] = $row['stateid'];
		$info['city_id'] = $row['cityid'];
		$info['cust_pincode'] = $row['cust_pincode'];
		$info['m_pan'] = $row['m_pan'];
		$info['emp_email'] = strtolower($row['user_mail']);
		$info['emp_password'] = $row['user_key'];
		$info['emp_mobile']	= $row['user_phone'];
		$info['emp_zone_id']	= $row['emp_zone_id'];
		$info['emp_branch_id']	= $row['branch_id_employee'];
		$info['emp_user_type']	= $row['user_type'];
		$info['alloc_state_id']	= implode(",",$row['alloc_stateid']);
		$info['alloc_city_id']	= implode(",",$row['alloc_cityid']);
		$info['report_to_user_type']	= $row['report_to_user_type'];
		$info['report_to_user_id']	= $row['report_to_user_id'];
		$info['open_balance']= $row['opn_balance'];
		$info['balance_typeid']	= $row['balance_typeid'];
		
		$insertid = add_record('hrms_employee', $info, $dbcon);

		if($insertid) {
			// Series Number Update Code
			$query = $dbcon->query("SELECT `id` FROM `hrms_employee`");
			$total_records = $query->num_rows;
			$updateInfo['taxinvoice_start'] = $total_records;
			$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'EMPLOYEE'" , $dbcon);

			//update to ledger
			$updateInfo1['employee_id'] = $insertid;
			$updateinvoiceid = update_record('tbl_ledger', $updateInfo1,"l_id = ".$row['l_id'] , $dbcon);
		}
	}
	return 1;
}
// ankit function end
?>