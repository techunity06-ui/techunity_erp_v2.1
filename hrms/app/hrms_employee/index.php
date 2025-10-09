<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include("../../../include/hrms_common_functions.php");

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
	$aColumns = array('id', 'series_id', 'emp_profile_img', 'employee_name', 'emp_email', 'created_at', 'status');
	$sIndexColumn = "id";
	$isWhere = array("status IN (0,1) and hrmsemp.company_id = $companyID".check_user('hrmsemp'));
	$sTable = "hrms_employee as hrmsemp";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id=hrmsemp.company_id");
	$hOrder = "hrmsemp.id ASC";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	
	$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);
	$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		if(isset($row['emp_profile_img']) && !empty($row['emp_profile_img'])){
			$imagePath = ROOT . HRMS_ROOT .'upload/emp_profile_image/'.$row['emp_profile_img'];
			$row_data[] = "<img class='text-center' src='$imagePath' alt='Member' width='50px' height='50px'>";
		}else{
			$imagePath = ROOT . HRMS_ROOT .'upload/emp_profile_image/no-profile.png';
			$row_data[] = "<img class='text-center' src='$imagePath' alt='Member' width='50px' height='50px'>";
		}
		$row_data[] = $row['series_id'];
		$row_data[] = $row['employee_name'];
		$row_data[] = $row['emp_email'];
		$row_data[] = 'SALARY ACCOUNT';
		$row_data[] = $row['created_at'];
		if($row['status']=='0'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
		}else{
			$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
		}

		$edit_btn='';$delete_btn='';$change_status='';
		if($row['id']!='0'){
			if($edit_btn_per){
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT . HRMS_ROOT . 'hrms_employee_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if($delete_btn_per){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_record('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
		$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	
	$row['res']='';
	// Insert Code HRMS Employee Table
	if(isset($_FILES["emp_profile_img"]["name"]) && !empty($_FILES["emp_profile_img"]["name"])){
        $file = $_FILES['emp_profile_img']['tmp_name']; 
        $sourceProperties = getimagesize($file);
        $fileNewName = time();
        $folderPath = "../../view/upload/emp_profile_image/";
        $ext = pathinfo($_FILES['emp_profile_img']['name'], PATHINFO_EXTENSION);
        $imageType = $sourceProperties[2];
        switch ($imageType) {
            case IMAGETYPE_PNG:
                $imageResourceId = imagecreatefrompng($file); 
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                imagepng($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                break;
            case IMAGETYPE_GIF:
                $imageResourceId = imagecreatefromgif($file); 
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                imagegif($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                break;
            case IMAGETYPE_JPEG:
                $imageResourceId = imagecreatefromjpeg($file); 
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                imagejpeg($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                break;
            default:
                echo "Invalid Image type.";
                exit;
                break;
        }
        $info['emp_profile_img'] = $fileNewName. "_thump.". $ext;
        $infoledger['emp_profile_img'] = $fileNewName. "_thump.". $ext;
	}
	$info['user_id'] = $_SESSION['user_id'];
	$info['company_id'] = $_SESSION['company_id'];
	$info['series_id'] = $POST['series_id'];
	$info['employee_name'] = $POST['employee_name'];
	$info['birth_date'] = date('Y-m-d',strtotime($POST['birth_date']));
	$info['joining_date'] = date('Y-m-d',strtotime($POST['joining_date']));
	$info['gender'] = $POST['gender'];
	$info['country_id']	= $POST['country_id'];
	$info['state_id'] = $POST['state_id'];
	$info['city_id'] = $POST['city_id'];
	$info['cust_pincode'] = $POST['cust_pincode'];
	$info['m_pan'] = $POST['m_pan'];
	$info['emp_email'] = strtolower($POST['emp_email']);
	$info['emp_password'] = $POST['emp_password'];
	$info['emp_mobile']	= $POST['emp_mobile'];
	$info['emp_zone_id']	= $POST['emp_zone_id'];
	$info['emp_branch_id']	= $POST['branch_id_emp'];
	$info['emp_user_type']	= $POST['emp_user_type'];
	$info['alloc_state_id']	= implode(",",$POST['alloc_stateid']);
	$info['alloc_city_id']	= implode(",",$POST['alloc_cityid']);
	$info['report_to_user_type']	= $POST['report_to_user_type'];
	$info['report_to_user_id']	= $POST['report_to_user_id'];
	$info['open_balance']= $POST['open_balance'];
	$info['balance_typeid']	= $POST['balance_typeid'];
	$info['emergenecy_contact_name'] = $POST['emergenecy_contact_name'];
	$info['emergenecy_contact_number'] = $POST['emergenecy_contact_number'];
	$info['per_day_salary'] = $POST['per_day_salary'];
	$info['relation'] = (isset($POST['relation']))?$POST['relation']:'';
	$info['status'] = $POST['status'];
	$info['updated_at']	= date("Y-m-d H:i:s");

	$tr = $dbcon -> query("SELECT `id`,`employee_name`,`status` FROM `hrms_employee` WHERE status != 2 and `employee_name` ='".$POST['employee_name']."' ");
	if($tr->num_rows > 0) {
		$row['res'] ="-1";
	}else{
		$insertid = add_record('hrms_employee', $info, $dbcon);

		// Series Number Update Code
		$query = $dbcon->query("SELECT `id` FROM `hrms_employee`");
		$total_records = $query->num_rows;
		$updateInfo['taxinvoice_start'] = $total_records;
		$updateinvoiceid = update_record('tbl_invoicetype', $updateInfo,"invoice_type = 'EMPLOYEE'" , $dbcon);

		// Insert Code Joing Table
		$joingtable['user_id'] = $_SESSION['user_id'];
		$joingtable['company_id'] = $_SESSION['company_id'];
		$joingtable['employee_id'] = $insertid;
		$joingtable['job_applicant_id'] = (isset($POST['job_applicant_id']))?$POST['job_applicant_id']:'';
		$joingtable['contract_end_date'] = (isset($POST['contract_end_date']))?date('Y-m-d',strtotime($POST['contract_end_date'])):'';
		$joingtable['offer_date'] = (isset($POST['offer_date']))?date('Y-m-d',strtotime($POST['offer_date'])):'';
		$joingtable['notice'] = (isset($POST['notice']))?$POST['notice']:'';
		$joingtable['confirmation_date'] = (isset($POST['confirmation_date']))?date('Y-m-d',strtotime($POST['confirmation_date'])):'';
		$joingtable['date_of_retirement'] = (isset($POST['date_of_retirement']))?date('Y-m-d',strtotime($POST['date_of_retirement'])):'';
		$joingtable['updated_at']	= date("Y-m-d H:i:s");
		$joingtable['status'] = 0;
		$joinginsertid = add_record('hrms_employee_joining_details', $joingtable, $dbcon);

		// Insert Code Department & Grade Table
		$departmenttable['user_id'] = $_SESSION['user_id'];
		$departmenttable['company_id'] = $_SESSION['company_id'];
		$departmenttable['employee_id'] = $insertid;
		$departmenttable['department_id'] = (isset($POST['department_id']))?$POST['department_id']:'';
		$departmenttable['grade_id'] = (isset($POST['grade_id']))?$POST['grade_id']:'';
		$departmenttable['designation_id'] = (isset($POST['designation_id']))?$POST['designation_id']:'';
		$departmenttable['branch_id'] = (isset($POST['branch_id']))?$POST['branch_id']:'';
		$departmenttable['report_to_id'] = (isset($POST['report_to_id']))?$POST['report_to_id']:'';
		$departmenttable['updated_at']	= date("Y-m-d H:i:s");
		$departmenttable['status'] = 0;
		$departmentinsertid = add_record('hrms_employee_department_grade_details', $departmenttable, $dbcon);

		// Insert Code Attendance & Leave Detail Table
		$attenleavetable['user_id'] = $_SESSION['user_id'];
		$attenleavetable['company_id'] = $_SESSION['company_id'];
		$attenleavetable['employee_id'] = $insertid;
		$attenleavetable['leave_policy_id'] = (isset($POST['leave_policy_id']))?$POST['leave_policy_id']:'';
		$attenleavetable['holiday_list_id'] = (isset($POST['holiday_list_id']))?$POST['holiday_list_id']:'';
		$attenleavetable['attendance_device_id'] = (isset($POST['attendance_device_id']))?$POST['attendance_device_id']:'';
		$attenleavetable['shift_type_id'] = (isset($POST['shift_type_id']))?$POST['shift_type_id']:'';
		$attenleavetable['leave_approver_id'] = (isset($POST['leave_approver_id']))?$POST['leave_approver_id']:'';
		$attenleavetable['updated_at']	= date("Y-m-d H:i:s");
		$attenleavetable['status'] = 0;
		$attenleaveinsertid = add_record('hrms_employee_attendance_leave_details', $attenleavetable, $dbcon);

		// Insert Code Salary Detail Table
		$salarydetailtable['user_id'] = $_SESSION['user_id'];
		$salarydetailtable['company_id'] = $_SESSION['company_id'];
		$salarydetailtable['employee_id'] = $insertid;
		$salarydetailtable['salary_mode'] = (isset($POST['salary_mode']))?$POST['salary_mode']:'';
		$salarydetailtable['expense_approver_id'] = (isset($POST['expense_approver_id']))?$POST['expense_approver_id']:'';
		$salarydetailtable['payroll_cost_center_id'] = (isset($POST['payroll_cost_center_id']))?$POST['payroll_cost_center_id']:'';
		$salarydetailtable['pan_number'] = (isset($POST['pan_number']))?$POST['pan_number']:'';
		$salarydetailtable['provident_fund_account'] = (isset($POST['provident_fund_account']))?$POST['provident_fund_account']:'';
		$salarydetailtable['health_insurance_id'] = (isset($POST['health_insurance_id']))?$POST['health_insurance_id']:'';
		$salarydetailtable['updated_at']	= date("Y-m-d H:i:s");
		$salarydetailtable['status'] = 0;
		$salarydetailinsertid = add_record('hrms_employee_salary_details', $salarydetailtable, $dbcon);

		// Insert Code Contact Detail Table
		$contactdetailtable['user_id'] = $_SESSION['user_id'];
		$contactdetailtable['company_id'] = $_SESSION['company_id'];
		$contactdetailtable['employee_id'] = $insertid;
		$contactdetailtable['employee_mobile'] = (isset($POST['employee_mobile']))?$POST['employee_mobile']:'';
		$contactdetailtable['employee_personal_email'] = (isset($POST['employee_personal_email']))?$POST['employee_personal_email']:'';
		$contactdetailtable['employee_unsubscribed_flag'] = (isset($POST['employee_unsubscribed_flag']))?$POST['employee_unsubscribed_flag']:'';
		$contactdetailtable['permenent_address_type'] = (isset($POST['permenent_address_type']))?$POST['permenent_address_type']:'';
		$contactdetailtable['permenent_address'] = (isset($POST['permenent_address']))?$POST['permenent_address']:'';
		$contactdetailtable['prefered_contact_email_type'] = (isset($POST['prefered_contact_email_type']))?$POST['prefered_contact_email_type']:'';
		$contactdetailtable['company_email'] = (isset($POST['company_email']))?$POST['company_email']:'';
		$contactdetailtable['current_address_type'] = (isset($POST['current_address_type']))?$POST['current_address_type']:'';
		$contactdetailtable['current_address'] = (isset($POST['current_address']))?$POST['current_address']:'';
		$contactdetailtable['updated_at']	= date("Y-m-d H:i:s");
		$contactdetailtable['status'] = 0;
		$contactdetailinsertid = add_record('hrms_employee_contact_details', $contactdetailtable, $dbcon);

		// Insert Code Personal Detail Table
		$personaldetailtable['user_id'] = $_SESSION['user_id'];
		$personaldetailtable['company_id'] = $_SESSION['company_id'];
		$personaldetailtable['employee_id'] = $insertid;
		$personaldetailtable['personal_bio_description'] = (isset($POST['personal_bio_description']))?$POST['personal_bio_description']:'';
		$personaldetailtable['passport_number'] = (isset($POST['passport_number']))?$POST['passport_number']:'';
		$personaldetailtable['date_of_issue'] = (isset($POST['date_of_issue']))?date('Y-m-d',strtotime($POST['date_of_issue'])):'';
		$personaldetailtable['valid_up_to'] = (isset($POST['valid_up_to']))?date('Y-m-d',strtotime($POST['valid_up_to'])):'';
		$personaldetailtable['place_of_issue'] = (isset($POST['place_of_issue']))?$POST['place_of_issue']:'';
		$personaldetailtable['matrial_status'] = (isset($POST['matrial_status']))?$POST['matrial_status']:'';
		$personaldetailtable['matrial_status'] = (isset($POST['matrial_status']))?$POST['matrial_status']:'';
		$personaldetailtable['blood_group'] = (isset($POST['blood_group']))?$POST['blood_group']:'';
		$personaldetailtable['family_background'] = (isset($POST['family_background']))?$POST['family_background']:'';
		$personaldetailtable['health_detail'] = (isset($POST['health_detail']))?$POST['health_detail']:'';
		$personaldetailtable['updated_at']	= date("Y-m-d H:i:s");
		$personaldetailtable['status'] = 0;
		$personaldetailinsertid = add_record('hrms_employee_personal_details', $personaldetailtable, $dbcon);

		// Insert Code Education Qulification Table
		$qulificationupdate['status']	= 0;
		$qulificationupdate['employee_id']	= $insertid;
		$updatequlificationid = update_record('hrms_employee_education_details', $qulificationupdate,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);

		// Insert Code Previous Work Experience Table
		$previousworkupdate['status']	= 0;
		$previousworkupdate['employee_id']	= $insertid;
		$previousworkid = update_record('hrms_employee_previous_work_experience_details', $previousworkupdate,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);

		// Insert Code History In Company Table
		$historyinworkupdate['status']	= 0;
		$historyinworkupdate['employee_id']	= $insertid;
		$historyinworkid = update_record('hrms_employee_history_company_details', $historyinworkupdate,"status=3 and user_id=".$_SESSION['user_id'] , $dbcon);

		// Insert Code Exit Details Table
		$exitdetailtable['user_id'] = $_SESSION['user_id'];
		$exitdetailtable['company_id'] = $_SESSION['company_id'];
		$exitdetailtable['employee_id'] = $insertid;
		$exitdetailtable['resignation_letter_date'] = (isset($POST['resignation_letter_date']))?date('Y-m-d',strtotime($POST['resignation_letter_date'])):'';
		$exitdetailtable['held_on_date'] = (isset($POST['held_on_date']))?date('Y-m-d',strtotime($POST['held_on_date'])):'';
		$exitdetailtable['reason_for_leaving'] = (isset($POST['reason_for_leaving']))?$POST['reason_for_leaving']:'';
		$exitdetailtable['reason_for_resignation'] = (isset($POST['reason_for_resignation']))?$POST['reason_for_resignation']:'';
		$exitdetailtable['leave_encashed_flag'] = (isset($POST['leave_encashed_flag']))?$POST['leave_encashed_flag']:'';
		$exitdetailtable['new_workplace'] = (isset($POST['new_workplace']))?$POST['new_workplace']:'';
		$exitdetailtable['encashment_date'] = (isset($POST['encashment_date']))?date('Y-m-d',strtotime($POST['encashment_date'])):'';
		$exitdetailtable['exit_feedback'] = (isset($POST['exit_feedback']))?$POST['exit_feedback']:'';
		$exitdetailtable['updated_at']	= date("Y-m-d H:i:s");
		$exitdetailtable['status'] = 0;
		$exitdetailinsertid = add_record('hrms_employee_exit_details', $exitdetailtable, $dbcon);

		// Insert Code Ledger Table
		$infoledger['employee_id'] = $insertid;
		$infoledger['l_name'] = $POST['employee_name'];
		$infoledger['l_group'] = '58';
		$infoledger['l_form'] = 'l_form';
		$infoledger['countryid'] = $POST['country_id'];
		$infoledger['stateid'] = $POST['state_id'];
		$infoledger['cityid'] = $POST['city_id'];
		$infoledger['cust_pincode']	= $POST['cust_pincode'];
		$infoledger['m_pan'] = $POST['m_pan'];
		$infoledger['emp_email'] = strtolower($POST['emp_email']);
		$infoledger['emp_password']	= $POST['emp_password'];
		$infoledger['emp_mobile']	= $POST['emp_mobile'];
		$infoledger['emp_zone_id']	= $POST['emp_zone_id'];
		$infoledger['branch_id_employee'] = $POST['branch_id_emp'];
		$infoledger['emp_user_type'] = $POST['emp_user_type'];
		$infoledger['alloc_stateid'] = implode(",",$POST['alloc_stateid']);
		$infoledger['alloc_cityid'] = implode(",",$POST['alloc_cityid']);
		$infoledger['report_to_user_type'] = $POST['report_to_user_type'];
		$infoledger['report_to_user_id'] = $POST['report_to_user_id'];
		$infoledger['opn_balance']	= $POST['open_balance'];
		$infoledger['balance_typeid']	= $POST['balance_typeid'];
		$infoledger['cdate'] = date("Y-m-d H:i:s");
		$infoledger['user_id'] = $_SESSION['user_id'];
		$infoledger['company_id'] = $_SESSION['company_id'];

		$insertledgerid = add_record('tbl_ledger', $infoledger, $dbcon);
		if($insertledgerid){
			$ref_date=date("Y-m-d");
			add_general_book_entry($dbcon,"tbl_ledger",$insertledgerid,$POST['balance_typeid'],$insertledgerid,$POST['open_balance'],$general_book_id,$ref_date);
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"ledger_add",1,"tbl_ledger",$inserid);

			/*Insert Code User Table*/
			$infousr['user_name']		= $POST['employee_name']; 
			$infousr['user_mail']		= strtolower($POST['emp_email']); 
			$infousr['user_key']		= md5($_POST['emp_password']);
			$infousr['user_type']		= $POST['emp_user_type'];//Fixed Type Employee
			$infousr['user_country']	= $POST['country_id'];
			$infousr['user_stat']		= $POST['state_id'];
			$infousr['user_city']		= $POST['city_id'];
			$infousr['user_phone']		= $POST['emp_mobile'];
			$infousr['usertype_terr']	= (isset($POST['usertype_terr']))?implode(",",$POST['usertype_terr']):'';
			$infousr['alloc_stateid']	= implode(",",$POST['alloc_stateid']);
			$infousr['alloc_cityid']	= implode(",",$POST['alloc_cityid']);
			$infousr['report_to_user_type']	= $POST['report_to_user_type'];
			$infousr['report_to_user_id'] = $POST['report_to_user_id'];
			$infousr['question_id']		= (isset($_POST['forgotquestion_id'])) ? md5($_POST['forgotquestion_id']) : '';
			$infousr['answer']			= (isset($_POST['forgotgive_answer'])) ? md5($_POST['forgotgive_answer']) : '';
			$infousr['user_address']	= (isset($_POST['m_address']))?$_POST['m_address']:'';
			$infousr['user_rid']		= $_SESSION['user_id'];
			$infousr['company_id']		= $_SESSION['company_id'];
			$infousr['payment_status'] 	= 1;
			$infousr['employee_id'] 	= $insertledgerid;//Employee ID flag check
			if(isset($_POST['template_id']) && $_POST['template_id'] != ''){
				$temp = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '".$_POST['template_id']."'");
				$temprecord = $temp->fetch_assoc();
				$infousr['template_access_perm_id'] = $temprecord['id'];
				$infousr['user_access_permission'] = $temprecord['template_access_permission'];
				$infousr['menu_show_permission'] = $temprecord['template_menu_show_permission'];
			}
			$inserusrid=add_record('users', $infousr, $dbcon);	

			$row['res'] = ($inserusrid) ? "1" : "0";
		}else{
			$row['res'] = ($insertledgerid) ? "1" : "0";
		}
	}
	echo json_encode($row);
	
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `hrms_employee` WHERE `id` = '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	if($_POST['token'] == $_SESSION['token']) {
		if(isset($_FILES["emp_profile_img"]["name"]) && !empty($_FILES["emp_profile_img"]["name"])){
	        $file = $_FILES['emp_profile_img']['tmp_name']; 
	        $sourceProperties = getimagesize($file);
	        $fileNewName = time();
	        $folderPath = "../../view/upload/emp_profile_image/";
	        $ext = pathinfo($_FILES['emp_profile_img']['name'], PATHINFO_EXTENSION);
	        $imageType = $sourceProperties[2];
	        switch ($imageType) {
	            case IMAGETYPE_PNG:
	                $imageResourceId = imagecreatefrompng($file); 
	                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
	                imagepng($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
	                break;
	            case IMAGETYPE_GIF:
	                $imageResourceId = imagecreatefromgif($file); 
	                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
	                imagegif($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
	                break;
	            case IMAGETYPE_JPEG:
	                $imageResourceId = imagecreatefromjpeg($file); 
	                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
	                imagejpeg($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
	                break;
	            default:
	                echo "Invalid Image type.";
	                exit;
	                break;
	        }
	        $info['emp_profile_img'] = $fileNewName. "_thump.". $ext;
	        $infoledger['emp_profile_img'] = $fileNewName. "_thump.". $ext;
    	}

		$info['series_id'] = $POST['series_id'];
		$info['employee_name'] = $POST['employee_name'];
		$info['birth_date'] = date('Y-m-d',strtotime($POST['birth_date']));
		$info['joining_date'] = date('Y-m-d',strtotime($POST['joining_date']));
		$info['gender'] = $POST['gender'];
		$info['country_id']	= $POST['country_id'];
		$info['state_id'] = $POST['state_id'];
		$info['city_id'] = $POST['city_id'];
		$info['cust_pincode'] = $POST['cust_pincode'];
		$info['m_pan'] = $POST['m_pan'];
		$info['emp_email'] = strtolower($POST['emp_email']);
		if($POST['emp_password']){
			$info['emp_password'] = $POST['emp_password'];
		}
		$info['emp_mobile']	= $POST['emp_mobile'];
		$info['emp_zone_id']	= $POST['emp_zone_id'];
		$info['emp_branch_id']	= $POST['branch_id_emp'];
		$info['emp_user_type']	= $POST['emp_user_type'];
		$info['alloc_state_id']	= implode(",",$POST['alloc_stateid']);
		$info['alloc_city_id']	= implode(",",$POST['alloc_cityid']);
		$info['report_to_user_type']	= $POST['report_to_user_type'];
		$info['report_to_user_id']	= $POST['report_to_user_id'];
		$info['open_balance']= $POST['open_balance'];
		$info['balance_typeid']	= $POST['balance_typeid'];
		$info['emergenecy_contact_name'] = $POST['emergenecy_contact_name'];
		$info['emergenecy_contact_number'] = $POST['emergenecy_contact_number'];
		$info['per_day_salary'] = $POST['per_day_salary'];
		$info['relation'] = (isset($POST['relation']))?$POST['relation']:'';
		$info['status'] = $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");	
		
		
		$updateid = update_record('hrms_employee', $info,"id=".$POST['eid'] , $dbcon);

		// Update Code Joing Table
		$joingtable['user_id'] = $_SESSION['user_id'];
		$joingtable['company_id'] = $_SESSION['company_id'];
		$joingtable['employee_id'] = $POST['eid'];
		$joingtable['job_applicant_id'] = (isset($POST['job_applicant_id']))?$POST['job_applicant_id']:'';
		$joingtable['contract_end_date'] = (isset($POST['contract_end_date']))?date('Y-m-d',strtotime($POST['contract_end_date'])):'';
		$joingtable['offer_date'] = (isset($POST['offer_date']))?date('Y-m-d',strtotime($POST['offer_date'])):'';
		$joingtable['notice'] = (isset($POST['notice']))?$POST['notice']:'';
		$joingtable['confirmation_date'] = (isset($POST['confirmation_date']))?date('Y-m-d',strtotime($POST['confirmation_date'])):'';
		$joingtable['date_of_retirement'] = (isset($POST['date_of_retirement']))?date('Y-m-d',strtotime($POST['date_of_retirement'])):'';
		$joingtable['updated_at']	= date("Y-m-d H:i:s");
		$joingtable['status'] = 0;
		$joinginsertid = update_record('hrms_employee_joining_details', $joingtable, "employee_id=".$POST['eid'], $dbcon);

		// Update Code Department & Grade Table
		$departmenttable['user_id'] = $_SESSION['user_id'];
		$departmenttable['company_id'] = $_SESSION['company_id'];
		$departmenttable['employee_id'] = $POST['eid'];
		$departmenttable['department_id'] = (isset($POST['department_id']))?$POST['department_id']:'';
		$departmenttable['grade_id'] = (isset($POST['grade_id']))?$POST['grade_id']:'';
		$departmenttable['designation_id'] = (isset($POST['designation_id']))?$POST['designation_id']:'';
		$departmenttable['branch_id'] = (isset($POST['branch_id']))?$POST['branch_id']:'';
		$departmenttable['report_to_id'] = (isset($POST['report_to_id']))?$POST['report_to_id']:'';
		$departmenttable['updated_at']	= date("Y-m-d H:i:s");
		$departmenttable['status'] = 0;
		$departmentinsertid = update_record('hrms_employee_department_grade_details', $departmenttable, "employee_id=".$POST['eid'] , $dbcon);

		// Update Code Attendance & Leave Detail Table
		$attenleavetable['user_id'] = $_SESSION['user_id'];
		$attenleavetable['company_id'] = $_SESSION['company_id'];
		$attenleavetable['employee_id'] = $POST['eid'];
		$attenleavetable['leave_policy_id'] = (isset($POST['leave_policy_id']))?$POST['leave_policy_id']:'';
		$attenleavetable['holiday_list_id'] = (isset($POST['holiday_list_id']))?$POST['holiday_list_id']:'';
		$attenleavetable['attendance_device_id'] = (isset($POST['attendance_device_id']))?$POST['attendance_device_id']:'';
		$attenleavetable['shift_type_id'] = (isset($POST['shift_type_id']))?$POST['shift_type_id']:'';
		$attenleavetable['leave_approver_id'] = (isset($POST['leave_approver_id']))?$POST['leave_approver_id']:'';
		$attenleavetable['updated_at']	= date("Y-m-d H:i:s");
		$attenleavetable['status'] = 0;
		$attenleaveinsertid = update_record('hrms_employee_attendance_leave_details', $attenleavetable, "employee_id=".$POST['eid'], $dbcon);

		// Update Code Salary Detail Table
		$salarydetailtable['user_id'] = $_SESSION['user_id'];
		$salarydetailtable['company_id'] = $_SESSION['company_id'];
		$salarydetailtable['employee_id'] = $POST['eid'];
		$salarydetailtable['salary_mode'] = (isset($POST['salary_mode']))?$POST['salary_mode']:'';
		$salarydetailtable['expense_approver_id'] = (isset($POST['expense_approver_id']))?$POST['expense_approver_id']:'';
		$salarydetailtable['payroll_cost_center_id'] = (isset($POST['payroll_cost_center_id']))?$POST['payroll_cost_center_id']:'';
		$salarydetailtable['pan_number'] = (isset($POST['pan_number']))?$POST['pan_number']:'';
		$salarydetailtable['provident_fund_account'] = (isset($POST['provident_fund_account']))?$POST['provident_fund_account']:'';
		$salarydetailtable['health_insurance_id'] = (isset($POST['health_insurance_id']))?$POST['health_insurance_id']:'';
		$salarydetailtable['updated_at']	= date("Y-m-d H:i:s");
		$salarydetailtable['status'] = 0;
		$salarydetailinsertid = update_record('hrms_employee_salary_details', $salarydetailtable, "employee_id=".$POST['eid'], $dbcon);

		// Update Code Contact Detail Table
		$contactdetailtable['user_id'] = $_SESSION['user_id'];
		$contactdetailtable['company_id'] = $_SESSION['company_id'];
		$contactdetailtable['employee_id'] = $POST['eid'];
		$contactdetailtable['employee_mobile'] = (isset($POST['employee_mobile']))?$POST['employee_mobile']:'';
		$contactdetailtable['employee_personal_email'] = (isset($POST['employee_personal_email']))?$POST['employee_personal_email']:'';
		$contactdetailtable['employee_unsubscribed_flag'] = (isset($POST['employee_unsubscribed_flag']))?$POST['employee_unsubscribed_flag']:'';
		$contactdetailtable['permenent_address_type'] = (isset($POST['permenent_address_type']))?$POST['permenent_address_type']:'';
		$contactdetailtable['permenent_address'] = (isset($POST['permenent_address']))?$POST['permenent_address']:'';
		$contactdetailtable['prefered_contact_email_type'] = (isset($POST['prefered_contact_email_type']))?$POST['prefered_contact_email_type']:'';
		$contactdetailtable['company_email'] = (isset($POST['company_email']))?$POST['company_email']:'';
		$contactdetailtable['current_address_type'] = (isset($POST['current_address_type']))?$POST['current_address_type']:'';
		$contactdetailtable['current_address'] = (isset($POST['current_address']))?$POST['current_address']:'';
		$contactdetailtable['updated_at']	= date("Y-m-d H:i:s");
		$contactdetailtable['status'] = 0;
		$contactdetailinsertid = update_record('hrms_employee_contact_details', $contactdetailtable, "employee_id=".$POST['eid'], $dbcon);

		// Update Code Personal Detail Table
		$personaldetailtable['user_id'] = $_SESSION['user_id'];
		$personaldetailtable['company_id'] = $_SESSION['company_id'];
		$personaldetailtable['employee_id'] = $POST['eid'];
		$personaldetailtable['personal_bio_description'] = (isset($POST['personal_bio_description']))?$POST['personal_bio_description']:'';
		$personaldetailtable['passport_number'] = (isset($POST['passport_number']))?$POST['passport_number']:'';
		$personaldetailtable['date_of_issue'] = (isset($POST['date_of_issue']))?date('Y-m-d',strtotime($POST['date_of_issue'])):'';
		$personaldetailtable['valid_up_to'] = (isset($POST['valid_up_to']))?date('Y-m-d',strtotime($POST['valid_up_to'])):'';
		$personaldetailtable['place_of_issue'] = (isset($POST['place_of_issue']))?$POST['place_of_issue']:'';
		$personaldetailtable['matrial_status'] = (isset($POST['matrial_status']))?$POST['matrial_status']:'';
		$personaldetailtable['matrial_status'] = (isset($POST['matrial_status']))?$POST['matrial_status']:'';
		$personaldetailtable['blood_group'] = (isset($POST['blood_group']))?$POST['blood_group']:'';
		$personaldetailtable['family_background'] = (isset($POST['family_background']))?$POST['family_background']:'';
		$personaldetailtable['health_detail'] = (isset($POST['health_detail']))?$POST['health_detail']:'';
		$personaldetailtable['updated_at']	= date("Y-m-d H:i:s");
		$personaldetailtable['status'] = 0;
		$personaldetailinsertid = update_record('hrms_employee_personal_details', $personaldetailtable, "employee_id=".$POST['eid'], $dbcon);

		// Insert Code Exit Details Table
		$exitdetailtable['user_id'] = $_SESSION['user_id'];
		$exitdetailtable['company_id'] = $_SESSION['company_id'];
		$exitdetailtable['employee_id'] = $POST['eid'];
		$exitdetailtable['resignation_letter_date'] = (isset($POST['resignation_letter_date']))?date('Y-m-d',strtotime($POST['resignation_letter_date'])):'';
		$exitdetailtable['held_on_date'] = (isset($POST['held_on_date']))?date('Y-m-d',strtotime($POST['held_on_date'])):'';
		$exitdetailtable['reason_for_leaving'] = (isset($POST['reason_for_leaving']))?$POST['reason_for_leaving']:'';
		$exitdetailtable['reason_for_resignation'] = (isset($POST['reason_for_resignation']))?$POST['reason_for_resignation']:'';
		$exitdetailtable['leave_encashed_flag'] = (isset($POST['leave_encashed_flag']))?$POST['leave_encashed_flag']:'';
		$exitdetailtable['new_workplace'] = (isset($POST['new_workplace']))?$POST['new_workplace']:'';
		$exitdetailtable['encashment_date'] = (isset($POST['encashment_date']))?date('Y-m-d',strtotime($POST['encashment_date'])):'';
		$exitdetailtable['exit_feedback'] = (isset($POST['exit_feedback']))?$POST['exit_feedback']:'';
		$exitdetailtable['updated_at']	= date("Y-m-d H:i:s");
		$exitdetailtable['status'] = 0;
		$exitdetailinsertid = update_record('hrms_employee_exit_details', $exitdetailtable, "employee_id=".$POST['eid'], $dbcon);

		// Update Code Ledger Table
		$infoledger['employee_id'] = $POST['eid'];
		$infoledger['l_name'] = $POST['employee_name'];
		$infoledger['l_group'] = '58';
		$infoledger['countryid'] = $POST['country_id'];
		$infoledger['stateid'] = $POST['state_id'];
		$infoledger['cityid'] = $POST['city_id'];
		$infoledger['cust_pincode']	= $POST['cust_pincode'];
		$infoledger['m_pan'] = $POST['m_pan'];
		$infoledger['emp_email'] = strtolower($POST['emp_email']);
		if($POST['emp_password']){
			$infoledger['emp_password']	= $POST['emp_password'];
		}
		$infoledger['emp_mobile']	= $POST['emp_mobile'];
		$infoledger['emp_zone_id']	= $POST['emp_zone_id'];
		$infoledger['branch_id_employee'] = $POST['branch_id_emp'];
		$infoledger['emp_user_type'] = $POST['emp_user_type'];
		$infoledger['alloc_stateid'] = implode(",",$POST['alloc_stateid']);
		$infoledger['alloc_cityid'] = implode(",",$POST['alloc_cityid']);
		$infoledger['report_to_user_type'] = $POST['report_to_user_type'];
		$infoledger['report_to_user_id'] = $POST['report_to_user_id'];
		$infoledger['opn_balance']	= $POST['open_balance'];
		$infoledger['balance_typeid']	= $POST['balance_typeid'];
		$infoledger['cdate'] = date("Y-m-d H:i:s");

		$updateledgerid = update_record('tbl_ledger', $infoledger, "employee_id=".$POST['eid'] , $dbcon);

		$tr = $dbcon->query("SELECT `l_id`,`employee_id` FROM `tbl_ledger` WHERE l_status != 2 and `employee_id` ='".$POST['eid']."' ");
		$updateRecord = $tr->fetch_assoc();

		// Update Code User Table
		$infousr['user_name']		= $POST['employee_name']; 
		$infousr['user_mail']		= strtolower($POST['emp_email']); 
		if($POST['emp_password']){
			$infousr['user_key']		= md5($_POST['emp_password']);
		}
		$infousr['user_type']		= $POST['emp_user_type'];//Fixed Type Employee
		$infousr['user_country']	= $POST['country_id'];
		$infousr['user_stat']		= $POST['state_id'];
		$infousr['user_city']		= $POST['city_id'];
		$infousr['user_phone']		= $POST['emp_mobile'];
		$infousr['usertype_terr']	= (isset($POST['usertype_terr']))?implode(",",$POST['usertype_terr']):'';
		$infousr['alloc_stateid']	= implode(",",$POST['alloc_stateid']);
		$infousr['alloc_cityid']	= implode(",",$POST['alloc_cityid']);
		$infousr['report_to_user_type']	= $POST['report_to_user_type'];
		$infousr['report_to_user_id'] = $POST['report_to_user_id'];
		$infousr['payment_status'] 	= 1;
		$infousr['employee_id'] 	= $updateRecord['l_id'];//Employee ID flag check
		if(isset($_POST['template_id']) && $_POST['template_id'] != ''){
			$temp = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '".$_POST['template_id']."'");
			$temprecord = $temp->fetch_assoc();
			$updateinfoper['user_access_permission'] = '';
			$updateinfoper['menu_show_permission'] = '';
			$infopermission['template_access_perm_id'] = $temprecord['id'];
			$infopermission['user_access_permission'] = $temprecord['template_access_permission'];
			$infopermission['menu_show_permission'] = $temprecord['template_menu_show_permission'];
			$updaterecord=update_record('users', $updateinfoper,"employee_id=".$updateRecord['l_id'] , $dbcon);
			$updateid=update_record('users', $infopermission,"employee_id=".$updateRecord['l_id'] , $dbcon);
		}
		$updateuserid = update_record('users', $infousr, "employee_id=".$updateRecord['l_id'] , $dbcon);

		$row['res'] = ($updateid) ? "1" : "0".$dbcon->error;
		
	} else {
		$row['res'] = "0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "delete") {
	$info['status'] = '2';
	$info['updated_at']	= date("Y-m-d H:i:s");
	$updateid = update_record('hrms_employee', $info,"id=".$POST['eid'] , $dbcon);
	$joinginsertid = update_record('hrms_employee_joining_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$departmentinsertid = update_record('hrms_employee_department_grade_details', $info, "employee_id=".$POST['eid'] , $dbcon);
	$attenleaveinsertid = update_record('hrms_employee_attendance_leave_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$salarydetailinsertid = update_record('hrms_employee_salary_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$contactdetailinsertid = update_record('hrms_employee_contact_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$personaldetailinsertid = update_record('hrms_employee_personal_details', $info, "employee_id=".$POST['eid'], $dbcon);
	
	echo ($updateid) ? "1" : "0";
}
else if(strtolower($POST['mode']) == "change_status") {
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	$info['updated_at']	= date("Y-m-d H:i:s");

	$updateid = update_record('hrms_employee', $info,"id=".$POST['eid'] , $dbcon);
	$joinginsertid = update_record('hrms_employee_joining_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$departmentinsertid = update_record('hrms_employee_department_grade_details', $info, "employee_id=".$POST['eid'] , $dbcon);
	$attenleaveinsertid = update_record('hrms_employee_attendance_leave_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$salarydetailinsertid = update_record('hrms_employee_salary_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$contactdetailinsertid = update_record('hrms_employee_contact_details', $info, "employee_id=".$POST['eid'], $dbcon);
	$personaldetailinsertid = update_record('hrms_employee_personal_details', $info, "employee_id=".$POST['eid'], $dbcon);
	
	echo ($updateid) ? "1" : "0";
}
else if(strtolower($POST['mode']) == "load_branch") {
	$val=$POST['val'];
	$where = ' AND zoneid = '.$val;		
	echo get_branch($dbcon, '', $where);
}
else if(strtolower($POST['mode']) == "load_emp") {
	$val=$POST['val'];
	$where = ' AND branch_id_employee = '.$val;		
	echo getAllEmployee($dbcon, '', $where);
}
else if(strtolower($POST['mode']) == "load_history_company") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['he_id'])){
				$query="select historycomday.id as histids ,historycomday.*,branchmst.branch_name,departm.department_name,designm.designation_name from hrms_employee_history_company_details as historycomday 
				left join hrms_employee as hrmsemp on hrmsemp.id = historycomday.employee_id
				left join branch_mst as branchmst on branchmst.branch_id = historycomday.history_branch_id
				left join hrms_department as departm on departm.id = historycomday.history_department_id
				left join hrms_designation as designm on designm.id = historycomday.history_designation_id
		 		where `historycomday`.`status` = 3 and `historycomday`.`user_id` = $userID and `historycomday`.`company_id` = $companyID";
			}else{
				 $query="select historycomday.id as histids, historycomday.*,branchmst.branch_name,departm.department_name,designm.designation_name from hrms_employee_history_company_details as historycomday 
				left join hrms_employee as hrmsemp on hrmsemp.id = historycomday.employee_id
				left join branch_mst as branchmst on branchmst.branch_id = historycomday.history_branch_id
				left join hrms_department as departm on departm.id = historycomday.history_department_id
				left join hrms_designation as designm on designm.id = historycomday.history_designation_id
		 		where `historycomday`.`status` = 0 and `historycomday`.`employee_id`=".$POST['he_id']." and `historycomday`.`user_id` = $userID and `historycomday`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th class="text-center"width="15%">Branch</th>
							<th class="text-center"width="15%">Department</th>
							<th class="text-center"width="15%">Designation</th>
							<th class="text-center"width="15%">From Date</th>
							<th class="text-center"width="15%">To Date</th>
						 	<th class="text-center"width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Branch" style="vertical-align:top;" class="text-center">';
								if(empty($rel['branch_name'])){
									echo '-';
								}else{
									echo $rel['branch_name'];
								}
						echo'</td><td data-label="Department" style="vertical-align:top;" class="text-center">';
								if(empty($rel['department_name'])){
									echo '-';
								}else{
									echo $rel['department_name'];
								}
						echo'</td><td data-label="Designation" style="vertical-align:top;" class="text-center">';
								if(empty($rel['designation_name'])){
									echo '-';
								}else{
									echo $rel['designation_name'];
								}
						echo'</td><td data-label="From Date" style="vertical-align:top;" class="text-center">';
								if(empty($rel['history_from_date'])){
									echo '-';
								}else{
									echo date('d-m-Y', strtotime($rel['history_from_date']));
								}
						echo'</td><td data-label="To Date" style="vertical-align:top;" class="text-center">';
								if(empty($rel['history_to_date'])){
									echo '-';
								}else{
									echo date('d-m-Y', strtotime($rel['history_to_date']));
								}
						echo'</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_history_data('.$rel['histids'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_history_data('.$rel['histids'].');" id="fieldremove'.$i.'">X</button>
							</td></tr>';
				$i++;
			}
		}else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</table></div></div>';
}
else if(strtolower($POST['mode']) == "fieldhistoryadd") {
		$info1['user_id'] =  $_SESSION['user_id'];
		$info1['company_id'] = $_SESSION['company_id'];
		$info1['history_branch_id'] = $POST['history_branch_id'];
		$info1['history_department_id'] = $POST['history_department_id'];
		$info1['history_designation_id'] = $POST['history_designation_id'];
		$info1['history_from_date']	= date('Y-m-d', strtotime($POST['history_from_date']));
		$info1['history_to_date']	= date('Y-m-d', strtotime($POST['history_to_date']));
		if(empty($POST['edit_id'])){
			if(empty($POST['eid'])){
				$info1['status']	= '3';
			}else{
				$info1['status']	= '0';
				$info1['employee_id'] = $POST['eid'];
			}
		}else{
			$info1['status']	= '0';
		}
		$table='hrms_employee_history_company_details';
		$tableid='id';
		
		if(empty($POST['edit_id'])){
			$inserid=add_record($table, $info1, $dbcon);
		}else{
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
		}		
}
else if(strtolower($POST['mode'])== "prehistoryedit")
{
		$ids = $POST['id'];
		$q = $dbcon -> query("select historycomday.*,branchmst.branch_name,departm.department_name,designm.designation_name from hrms_employee_history_company_details as historycomday 
				left join hrms_employee as hrmsemp on hrmsemp.id = historycomday.employee_id
				left join branch_mst as branchmst on branchmst.branch_id = historycomday.history_branch_id
				left join hrms_department as departm on departm.id = historycomday.history_department_id
				left join hrms_designation as designm on designm.id = historycomday.history_designation_id
		 		where `historycomday`.`id` = $ids");
		$r = $q->fetch_assoc();
		echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_history_data")
{
		$row=array();
		$info['status'] = 2;	
		$updateid=update_record("hrms_employee_history_company_details", $info,"id=".$POST['eid'] , $dbcon);
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
}
else if(strtolower($POST['mode']) == "load_previous_company") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['pre_id'])){
				$query="select previouswork.* from hrms_employee_previous_work_experience_details as previouswork 
				where `previouswork`.`status` = 3 and `previouswork`.`user_id` = $userID and `previouswork`.`company_id` = $companyID";
			}else{
				 $query="select previouswork.* from hrms_employee_previous_work_experience_details as previouswork 
		 			where `previouswork`.`status` = 0 and `previouswork`.`employee_id`=".$POST['pre_id']." and `previouswork`.`user_id` = $userID and `previouswork`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th class="text-center"width="15%">Company Name</th>
							<th class="text-center"width="15%">Designation</th>
							<th class="text-center"width="15%">Salary</th>
							<th class="text-center"width="15%">Address</th>
							<th class="text-center"width="15%">Contact</th>
							<th class="text-center"width="15%">Total Experience</th>
						 	<th class="text-center"width="10%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="Company Name" style="vertical-align:top;" class="text-center">';
								if(empty($rel['company_name'])){
									echo '-';
								}else{
									echo $rel['company_name'];
								}
						echo'</td><td data-label="Designation" style="vertical-align:top;" class="text-center">';
								if(empty($rel['designation'])){
									echo '-';
								}else{
									echo $rel['designation'];
								}
						echo'</td><td data-label="Salary" style="vertical-align:top;" class="text-center">';
								if(empty($rel['salary_amount'])){
									echo '-';
								}else{
									echo $rel['salary_amount'];
								}
						echo'</td><td data-label="Address" style="vertical-align:top;" class="text-center">';
								if(empty($rel['address'])){
									echo '-';
								}else{
									echo $rel['address'];
								}
						echo'</td><td data-label="Contact" style="vertical-align:top;" class="text-center">';
								if(empty($rel['contact'])){
									echo '-';
								}else{
									echo $rel['contact'];
								}
						echo'</td><td data-label="Total Experience" style="vertical-align:top;" class="text-center">';
								if(empty($rel['total_experience'])){
									echo '-';
								}else{
									echo $rel['total_experience'];
								}		
						echo'</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_previous_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_previous_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td></tr>';
				$i++;
			}
		}else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</table></div></div>';
}
else if(strtolower($POST['mode']) == "fieldpreviousadd") {
		$info1['user_id'] =  $_SESSION['user_id'];
		$info1['company_id'] = $_SESSION['company_id'];
		$info1['company_name'] = $POST['company_name'];
		$info1['designation'] = $POST['designation'];
		$info1['salary_amount'] = $POST['salary_amount'];
		$info1['address'] = $POST['address'];
		$info1['contact'] = $POST['contact'];
		$info1['total_experience'] = $POST['total_experience'];
		if(empty($POST['edit_previous_id'])){
			if(empty($POST['eid'])){
				$info1['status']	= '3';
			}else{
				$info1['status']	= '0';
				$info1['employee_id'] = $POST['eid'];
			}
		}else{
			$info1['status']	= '0';
		}
		$table='hrms_employee_previous_work_experience_details';
		$tableid='id';
		
		if(empty($POST['edit_previous_id'])){
			$inserid = add_record($table, $info1, $dbcon);
		}else{
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_previous_id'] , $dbcon);	
		}		
}
else if(strtolower($POST['mode'])== "prepreviousedit")
{
		$ids = $POST['id'];
		$q = $dbcon -> query("select previouswork.* from hrms_employee_previous_work_experience_details as previouswork 
		 		where `previouswork`.`id` = $ids");
		$r = $q->fetch_assoc();
		echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_previous_data")
{
		$row=array();
		$info['status'] = 2;	
		$updateid=update_record("hrms_employee_previous_work_experience_details", $info,"id=".$POST['eid'] , $dbcon);
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
}
else if(strtolower($POST['mode']) == "load_educational_company") {
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			if(empty($POST['edu_id'])){
				$query="select educationalwork.* from hrms_employee_education_details as educationalwork 
				where `educationalwork`.`status` = 3 and `educationalwork`.`user_id` = $userID and `educationalwork`.`company_id` = $companyID";
			}else{
				 $query="select educationalwork.* from hrms_employee_education_details as educationalwork 
		 			where `educationalwork`.`status` = 0 and `educationalwork`.`employee_id`=".$POST['edu_id']." and `educationalwork`.`user_id` = $userID and `educationalwork`.`company_id` = ".$companyID;
			}
		    
			$result=$dbcon->query($query);
			
			echo ' <div class="form-group">
					  <div class="col-md-12 col-xs-12">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
						<tr id="field">
							<th class="text-center"width="12%">School/University</th>
							<th class="text-center"width="11%">Qualification</th>
							<th class="text-center"width="12%">Level</th>
							<th class="text-center"width="11%">Year of Passing</th>
							<th class="text-center"width="12%">Class/Percentage</th>
							<th class="text-center"width="20%">Major/Optional Subjects</th>
						 	<th class="text-center"width="8%">Action</th>
						</tr>';
		if(mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=mysqli_fetch_assoc($result))
			{
				 echo '<tr id="fieldtr'.$rel['id'].'" >
							<td data-label="School/University" style="vertical-align:top;" class="text-center">';
								if(empty($rel['education_school_university'])){
									echo '-';
								}else{
									echo $rel['education_school_university'];
								}
						echo'</td><td data-label="Qualification" style="vertical-align:top;" class="text-center">';
								if(empty($rel['education_qualification'])){
									echo '-';
								}else{
									echo $rel['education_qualification'];
								}
						echo'</td><td data-label="Level" style="vertical-align:top;" class="text-center">';
								if(empty($rel['education_level'])){
									echo '-';
								}else{
									echo $rel['education_level'];
								}
						echo'</td><td data-label="Year of Passing" style="vertical-align:top;" class="text-center">';
								if(empty($rel['year_of_passing'])){
									echo '-';
								}else{
									echo $rel['year_of_passing'];
								}
						echo'</td><td data-label="Class/Percentage" style="vertical-align:top;" class="text-center">';
								if(empty($rel['class_percentage'])){
									echo '-';
								}else{
									echo $rel['class_percentage'];
								}
						echo'</td><td data-label="Major/Optional Subjects" style="vertical-align:top;" class="text-center">';
								if(empty($rel['optional_subjects'])){
									echo '-';
								}else{
									echo $rel['optional_subjects'];
								}		
						echo'</td>
							<td data-label="ACTION" style="vertical-align:top">
								<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_educational_data('.$rel['id'].');" ><i class="fa fa-pencil"></i></button>
								<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_educational_data('.$rel['id'].');" id="fieldremove'.$i.'">X</button>
							</td></tr>';
				$i++;
			}
		}else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</table></div></div>';
}
else if(strtolower($POST['mode']) == "fieldeducationaladd") {
		$info1['user_id'] =  $_SESSION['user_id'];
		$info1['company_id'] = $_SESSION['company_id'];
		$info1['education_school_university'] = $POST['education_school_university'];
		$info1['education_qualification'] = $POST['education_qualification'];
		$info1['education_level'] = $POST['education_level'];
		$info1['year_of_passing'] = $POST['year_of_passing'];
		$info1['class_percentage'] = $POST['class_percentage'];
		$info1['optional_subjects'] = $POST['optional_subjects'];
		if(empty($POST['edit_educational_id'])){
			if(empty($POST['eid'])){
				$info1['status']	= '3';
			}else{
				$info1['status']	= '0';
				$info1['employee_id'] = $POST['eid'];
			}
		}else{
			$info1['status']	= '0';
		}
		$table='hrms_employee_education_details';
		$tableid='id';
		
		if(empty($POST['edit_educational_id'])){
			$inserid = add_record($table, $info1, $dbcon);
		}else{
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_educational_id'] , $dbcon);	
		}		
}
else if(strtolower($POST['mode'])== "preeducationaledit")
{
		$ids = $POST['id'];
		$q = $dbcon -> query("select educationalwork.* from hrms_employee_education_details as educationalwork 
		 		where `educationalwork`.`id` = $ids");
		$r = $q->fetch_assoc();
		echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_educational_data")
{
		$row=array();
		$info['status'] = 2;	
		$updateid=update_record("hrms_employee_education_details", $info,"id=".$POST['eid'] , $dbcon);
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
}
function imageResize($imageResourceId,$width,$height) {
    $targetWidth =100;
    $targetHeight =100;
    $targetLayer=imagecreatetruecolor($targetWidth,$targetHeight);
    imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$targetWidth,$targetHeight, $width,$height);
    return $targetLayer;
}