<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "edit") {
	
	$infousr['user_name'] =	$info['company_name']= $POST['company_name'];
	$infousr['user_address'] = $info['address']	= stripslashes($POST['address']);	
	$info['contact_no']	= $POST['contact_no'];
	$info['website']	= $POST['website'];
	$info['company_website']= $POST['company_website'];
	$info['bank_name']	= $POST['bank_name'];
	$info['ac_no']		= $POST['ac_no'];
	$info['ifcs']		= $POST['ifcs'];
	$info['branch_name']    = $POST['branch_name'];
	$info['vatno']		= strtoupper($POST['gstno']);
	$info['iec_no']		= strtoupper($POST['iec_no']);
	$info['lut_no']		= strtoupper($POST['lut_no']);
	$filter_valid_till_date 	= explode(" - ",$POST['valid_till_date']);
	$info['valid_till_date_start']= date('Y-m-d',strtotime($filter_valid_till_date[0]));
	$info['valid_till_date_end']= date('Y-m-d',strtotime($filter_valid_till_date[1]));
	$info['pan_no']		= $POST['pan_no'];
	$info['stateid']	= $POST['stateid'];
	$info['city_name']	= $POST['city_name'];
	$info['pincode'] = $POST['pincode'];
	/*$info['vat_date']	= date('Y-m-d',strtotime($POST['vat_date']));
		$info['cstno']		= $POST['cstno'];
	$info['cst_date']	= date('Y-m-d',strtotime($POST['cst_date']));*/
	$info['serno']		= $POST['serno'];
	$info['ser_date']	= date('Y-m-d',strtotime($POST['ser_date']));
	$info['pan_no']		= $POST['pan_no'];
	$info['quot_condition']		= $POST['quot_condition'];
	$info['coverlator_content']	= $POST['coverlator_content'];
	$info['quot_content']		= $POST['quot_email_content']; 

            $info['quot_validity']		= $POST['quot_validity']; // added by Dimple Panchal
            $info['inventory_management']	= $POST['inventory_management']; // added by Dimple Panchal
            $info['tcs_applicable']	= $POST['tcs_applicable']; // added by Dimple Panchal
             $info['send_email']	= $POST['send_email']; // added by Sanat Mamtora : 10-08-2021
	
	if(!empty($_FILES['logo']['tmp_name'])) {
		$q="select * from tbl_company where company_id=".$POST['eid'];
		$row=mysqli_fetch_assoc($dbcon->query($q));
		$file=$row['logo'];
		unlink(LOGO_A.$file);
		unlink(LOGO_A."thumb//".$file);
		$info['logo']	= upload_image($_FILES);
	}
	if(!empty($_FILES['f_logo']['tmp_name'])) {
		$q="select * from tbl_company where company_id=".$POST['eid'];
		$row=mysqli_fetch_assoc($dbcon->query($q));
		$file=$row['f_logo'];
		unlink(LOGO_A.$file);
		unlink(LOGO_A."thumb//".$file);
		$info['f_logo']	= upload_image1($_FILES);
	}
	if(!empty($_FILES['authorized_signature']['tmp_name'])) {
		$q="select * from tbl_company where company_id=".$POST['eid'];
		$row=mysqli_fetch_assoc($dbcon->query($q));
		$file=$row['authorized_signature'];
		unlink("../../../view/upload/signature/".$file);
		unlink("../../../view/upload/signature/thumb//".$file);
		$info['authorized_signature']	= upload_image2($_FILES);
		$infousr['authorized_signature']	= upload_image2($_FILES);
	}
	$info['perfoma_condition']		= stripslashes($_POST['export_condition']);
	$info['conditions']			= stripslashes($_POST['condition']);
	$info['challan_condition']	= stripslashes($_POST['challan_condition']);
	$info['quot_subject']	= stripslashes($_POST['quot_subject']);
	$info['po_condition']		= $_POST['po_condition'];
	$info['logo_content']		= $_POST['logo_content'];
	$info['dispatch_head_content']		= $_POST['dispatch_head_content'];
	$info['dispatch_footer_content']	= $_POST['dispatch_footer_content'];
	$info['lead_email_content']		= $_POST['lead_email_content'];
	$info['inquiry_email_content']	= $_POST['inquiry_email_content'];
	$info['installation_warranty']	= $_POST['installation_warranty'];
	$info['signature']	= $_POST['signature'];
	$info['cdate']		= date("Y-m-d H:i:s");
	$info['user_id']	= $_SESSION['user_id'];

	//Amish Soni Start 04-02-2021
	$cmp_unique_id = strtoupper($POST['cmp_unique_id']);
	$uniqueQuery = "select company_id from tbl_company where cmp_unique_id = '$cmp_unique_id'";
	$uniqueRow = mysqli_fetch_assoc($dbcon->query($uniqueQuery));
	$existingCompanyId = $uniqueRow['company_id'];
	
	if($existingCompanyId && $existingCompanyId != $POST['eid']) {
		echo "-2";
	} else {
		$updateid = update_record('tbl_company', $info, "company_id=" . $POST['eid'], $dbcon);
		//	$infousr['user_rid']  = $inserid;

		$infousr['user_company'] = $POST['company_name'];

		$updateuserid = update_record('users', $infousr, "user_type=2 and company_id='" . $POST['eid'] . "' and user_rid=" . $POST['eid'], $dbcon);

		if ($updateid)
			echo "update";
		else
			echo "0" . $dbcon->error;
	}
} else if(strtolower($POST['mode']) == "crm_settings") {
	$info['crm_auto_mail'] = $POST['crm_auto_mail'];
	$info['quotation_print_content'] = $POST['quotation_print_content'];
	$info['project_wise_manufacturing'] = $POST['project_wise_manufacturing'];
	$info['project_wise_item_rate'] = $POST['project_wise_item_rate'];
	$info['general_terms_condition'] = $_POST['general_terms_condition'];
	$info['battery_limits_and_schedule_exclusion'] = $_POST['battery_limits_and_schedule_exclusion'];
	$info['max_followup_date'] = $POST['max_followup_date'];
	$info['user_id'] = $_SESSION['user_id'];
	$info['company_id'] = $_SESSION['company_id'];
	$tableName = 'tbl_company_settings';
	if($POST['setting_id']) {
		$info['updated_at'] = date("Y-m-d H:i:s");
		$updateid = update_record($tableName, $info, "id='".$POST['setting_id']."'", $dbcon);
	} else {
		$updateid = add_record($tableName, $info, $dbcon);
	}

	$infoc['crm_pro_type'] = trim(implode(",", $_POST['crm_pro_type']),",");
	$infoc['so_pro_type'] = trim(implode(",", $_POST['so_pro_type']),",");
	$infoc['indent_po_pro_type'] = trim(implode(",", $_POST['indent_po_pro_type']),",");
	$infoc['upload_reciept'] 	= $_POST['upload_receipt'];
	$infoc['qc_upload_receipt'] = $_POST['qc_upload_receipt'];
	$infoc['user_id'] = $_SESSION['user_id'];
	$infoc['company_id'] = $_SESSION['company_id'];

	$tableName1='tbl_company_configuration';
	if($POST['company_conf_id']) {
		$infoc['cdate'] = date("Y-m-d H:i:s");
		$updateid = update_record($tableName1, $infoc, "company_conf_id='".$POST['company_conf_id']."'", $dbcon);
	} else {
		$infoc['cdate'] = date("Y-m-d H:i:s");
		$updateid = add_record($tableName1, $infoc, $dbcon);
	}
	echo ($updateid) ? 'update' : '0'.$dbcon->error;
} else if(strtolower($POST['mode']) == "company_configuration") {
	$infoc['crm_pro_type'] = trim(implode(",", $_POST['crm_pro_type']),",");
	$infoc['so_pro_type'] = trim(implode(",", $_POST['so_pro_type']),",");
	$infoc['indent_po_pro_type'] = trim(implode(",", $_POST['indent_po_pro_type']),",");
	$infoc['upload_reciept'] 	= $_POST['upload_receipt'];
	$infoc['qc_upload_receipt'] = $_POST['qc_upload_receipt'];
	$infoc['crm_pro_search']		= trim(implode(",", $_POST['crm_pro_search']),",");
	$infoc['purchase_pro_search'] 	= trim(implode(",", $_POST['purchase_pro_search']),",");
	$infoc['production_pro_search'] = trim(implode(",", $_POST['production_pro_search']),",");
	$infoc['sales_pro_search'] 		= trim(implode(",", $_POST['sales_pro_search']),",");
	$infoc['bom_pro_search'] 		= trim(implode(",", $_POST['bom_pro_search']),",");
	$infoc['sales_party_show'] 		= trim(implode(",", $_POST['sales_party_show']),",");
	$infoc['purchase_party_show'] 	= trim(implode(",", $_POST['purchase_party_show']),",");
	$infoc['generate_item_code'] = $_POST['generate_item_code'];
	$infoc['po_terms_conditions'] = stripcslashes(str_replace(array("\n", "\r", "\N"), '', $_POST['po_terms_conditions']));
	$infoc['enable_assing_user'] = $_POST['enable_assing_user'];
	$infoc['sales_time_load_pro']	= $POST['sales_time_load_pro'];
	$infoc['trading_stock']			= $POST['trading_stock'];
	$infoc['user_id'] = $_SESSION['user_id'];
	$infoc['company_id'] = $_SESSION['company_id'];

	$info['inventory_management']	= $_POST['inventory_management']; 
	$info['tcs_applicable']	= $_POST['tcs_applicable'];
	$info['send_email']	= $_POST['send_email']; 
    $info['smtp_email']	= $_POST['smtp_email']; 
    $info['smtp_password']	= $_POST['smtp_password'];
    $info['user_id'] = $_SESSION['user_id'];

    $infoc['store_approval'] = $POST['store_approval']; // added by Sanat : 20-09-2021

    $updateid = update_record('tbl_company', $info, "company_id=" . $_POST['eid'], $dbcon);

    $infoco['crm_auto_mail'] = $_POST['crm_auto_mail'];
	$infoco['quotation_print_content'] = $_POST['quotation_print_content'];
	$infoco['project_wise_manufacturing'] = $_POST['project_wise_manufacturing'];
	$infoco['project_wise_item_rate'] = $_POST['project_wise_item_rate'];
	$infoco['general_terms_condition'] = $_POST['general_terms_condition'];
	$infoco['battery_limits_and_schedule_exclusion'] = $_POST['battery_limits_and_schedule_exclusion'];
	$infoco['max_followup_date'] = $_POST['max_followup_date'];
	$infoco['user_id'] = $_SESSION['user_id'];
	$infoco['company_id'] = $_SESSION['company_id'];

	$updateid = update_record('tbl_company_settings', $infoco, "id='".$_POST['setting_id']."'", $dbcon);

	$tableName1='tbl_company_configuration';
	if($_POST['company_conf_id']) {
		$infoc['cdate'] = date("Y-m-d H:i:s");
		$updateid = update_record($tableName1, $infoc, "company_conf_id='".$POST['company_conf_id']."'", $dbcon);
	} else {
		$infoc['cdate'] = date("Y-m-d H:i:s");
		$updateid = add_record($tableName1, $infoc, $dbcon);
	}
	echo ($updateid) ? 'update' : '0'.$dbcon->error;
}

// Amish Soni End 12-01-2021
function upload_image($FILES)
{
	$rand=rand(0,9999);
	if(!empty($FILES['logo']['tmp_name'])) {
		list($width, $height, $type, $attr) = getimagesize($FILES['logo']['tmp_name']);
		if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $FILES["logo"]["name"]);
			$extension = strtolower(end($temp));
			if (in_array($extension, $allowedExts)) {
				$File = "header".$rand.".jpg";
				$tmp_name = $FILES["logo"]["tmp_name"];
				move_uploaded_file($tmp_name,LOGO_A.$File);
				smart_resize_image(LOGO_A.$File,792,100);
			}
		}
		return  $File;				
	}

}
function upload_image1($FILES)
{
	$rand=rand(0,9999);
	if(!empty($FILES['f_logo']['tmp_name'])) {
		list($width, $height, $type, $attr) = getimagesize($FILES['f_logo']['tmp_name']);
		if (isset($type) && in_array($type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$temp = explode(".", $FILES["f_logo"]["name"]);
			$extension = strtolower(end($temp));
			if (in_array($extension, $allowedExts)) {
				$File = "footer".$rand.".jpg";
				$tmp_name = $FILES["f_logo"]["tmp_name"];
				move_uploaded_file($tmp_name,LOGO_A.$File);
				smart_resize_image(LOGO_A.$File,792,80);
			}
		}
		return  $File;				
	}	
}
?>
