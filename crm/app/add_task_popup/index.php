<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

// Amish Soni Start 30-12-2020
include_once($include."common_send_email.php");
// Amish Soni End 30-12-2020

//print_r($_POST);
//print_r($_FILES);

$POST = ($_POST != NULL) ? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "add_task") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    if ($POST['task_type_id'] == GENERAL_TASK_TYPE) {
        $show_user_ids = $POST['assign_user_ids'].','.$_SESSION['user_id'];
    } else{
        $show_user_ids = show_user_ids($dbcon, $POST['assign_user_ids']);
    }

    $prev_task_id = get_previous_taskid($dbcon,$POST['inquiry_id']);
    //echo $prev_task_id['prev_taskid'];
    $info['task_type_id']		= $POST['task_type_id'];
    $info['task_rel_id']		= 5; //fixed related Id
    $info['task_name']			= $POST['task_name'];
    $info['c_con_id']			= $POST['c_con_id'];
    $info['cust_id']			= $POST['cust_id'];
    $info['inquiry_id']			= $POST['inquiry_id'];
    $info['opp_id']			    = $POST['opp_id'];
    $info['sales_stage_id']     = $POST['sales_stage_id'];
    if($POST['opp_id'] == WON){
        $info['stage_prob'] =100;
    }else{
        $info['stage_prob'] = $POST['stage_prob'];
    }
    $info['task_remark']		= $POST['task_remark'];
    $info['assign_user_ids']    = $POST['assign_user_ids'];
    $info['task_priority_id']   = $POST['task_priority_id'];
    $info['task_alert_id']		= $POST['task_alert_id'];
    $info['show_user_ids']      = $show_user_ids;

    if($POST['task_alert_id'] && $POST['task_alert_id']!='1'){//If alert is not none
        $alert_date = date("Y-m-d H:i:s", strtotime($POST['task_due_date']));
        $gap_mints = get_alert_mintes($dbcon,$POST['task_alert_id']);
        $filt_alert_date = date("Y-m-d H:i:s", strtotime($alert_date . "-".$gap_mints." minutes"));//Subtract Minutes
        $info['alert_date_time']	= date('Y-m-d H:i:s',strtotime($filt_alert_date));
    }
	
    $info['perent_id']	    = $prev_task_id['prev_taskid'];
    $info['create_date']	= date('Y-m-d H:i:s');
    $info['task_due_date']	= date('Y-m-d H:i:s',strtotime($POST['task_due_date']));
    $info['entry_type']		= 1;//Fixed Task Type
    $info['cdate']          = date("Y-m-d H:i:s");
    $info['user_id']		= $_SESSION['user_id'];
    $info['company_id']		= $_SESSION['company_id'];

    // Amish Soni Start 19-01-2021
    $crm_auto_mail = '';
    $companySettings = getCompanySettings($dbcon);
    if($companySettings) {
        $crm_auto_mail = $companySettings['crm_auto_mail'];
    }
    $showTemplate = ($crm_auto_mail == 'No');

    if($showTemplate) {
        $info['email_template_id'] = (isset($POST['email_template_id']) && $POST['email_template_id'])
            ? $POST['email_template_id'] : null;
    }
    // Amish Soni End 19-01-2021

    $ins_task_id = add_record('tbl_task', $info, $dbcon, $branch_id);
    
    if($ins_task_id){
        $inq_data = array();
        //$inq_data['show_user_ids']      = $show_user_ids;
        $inq_data['opp_id']		= $POST['opp_id'];
        $inq_data['sales_stage_id']     = $POST['sales_stage_id'];
        $inq_data['stage_prob']         = $POST['stage_prob'];
        
        if($POST['stage_prob']=='100' && $POST['opp_id'] == WON){
            $upd_qry="update tbl_task set task_status=1,task_completion_date='".date("Y-m-d H:i:s")."' where task_status=0 and inquiry_id=".$POST['inquiry_id'];
            $upd_qry_rs=$dbcon->query($upd_qry);
                                
            $qtupd_qry="update tbl_quotation set quot_won_user_id=".$_SESSION['user_id']." where inquiry_id=".$POST['inquiry_id'];
            $qtupd_qry_rs=$dbcon->query($qtupd_qry);
            $inq_data['won_user_id']	= $_SESSION['user_id'];
            $inq_data['won_by_userid']	= $_SESSION['user_id'];
            $inq_data['won_date']       = date("Y-m-d H:i:s");
        }
        
        if($POST['stage_prob']=='0' && $POST['opp_id'] == LOST){
            $upd_qry="update tbl_task set task_status=1,task_completion_date='".date("Y-m-d H:i:s")."' where task_status=0 and inquiry_id=".$POST['inquiry_id'];
            $upd_qry_rs=$dbcon->query($upd_qry);
                                
            $qtupd_qry="update tbl_quotation set quotation_status=".DELETED." where inquiry_id=".$POST['inquiry_id'];
            $qtupd_qry_rs=$dbcon->query($qtupd_qry);
            
            $inq_data['lost_by_userid']      = $_SESSION['user_id'];
        }
        
        $inq_id = update_record('tbl_inquiry', $inq_data,"inquiry_id=".$POST['inquiry_id'], $dbcon, $branch_id);
        
        $upd_qry = "update tbl_task set task_status=1,is_delete=1,task_completion_date='".date("Y-m-d H:i:s")."' where task_id NOT IN (".$ins_task_id.") AND inquiry_id=".$POST['inquiry_id'];
        $upd_qry_rs = $dbcon->query($upd_qry);
        
        //if Inquiry won without Quotation, auto create Quotation 
        if($POST['opp_id'] == WON && !check_has_quotation($dbcon,$POST['inquiry_id'])){
            $inq_qry = "select * from tbl_inquiry where inquiry_id =".$POST['inquiry_id'];
            $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
            auto_create_quotation($dbcon,$inq_data,$POST['inquiry_id']);
        }

        // Amish Soni Start 30-12-2020
        //Added Email functionality only for Inquiry (other's are remaining)
        if($POST['inquiry_id']) {
            $inq_qry = "SELECT * FROM tbl_inquiry WHERE inquiry_id =".$POST['inquiry_id'];
            $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));

            $module_id = 2; //CRM Module
            // Amish Soni Start 19-01-2021
            if($showTemplate) {
                if(isset($POST['email_template_id']) && $POST['email_template_id']){
                    $mail_template = getEmailSMSTemplateById($dbcon, $POST['email_template_id']);
                }
            } else {
                $mail_template = getEmailSMSTemplate($dbcon, $module_id, $POST['task_type_id'], $POST['opp_id']);
            }
            // Amish Soni End 19-01-2021

            $cur_user_id = $_SESSION['user_id'];
            $cust_id = $inq_data['cust_id'];
            $cur_user = getUserDetailById($dbcon, $cur_user_id);
            $customer = getCustDetailById($dbcon, $cust_id);
            $from_email_id = ($cur_user && $cur_user['common_email_id']) ? $cur_user['common_email_id'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';

            if($mail_template && $to_email_id) {
                // Amish Soni Start 18-01-2021
                $subject = $mail_template['email_subject'];
                $content = $mail_template['email_content'];

                $subject = replaceMergeFields($dbcon, $subject, $cust_id, $module_id);
                $content = replaceMergeFields($dbcon, $content, $cust_id, $module_id);
                // Amish Soni End 18-01-2021
                final_send_email($from_email_id, $to_email_id, '', '', $subject, $content);
            }
        }
        // Amish Soni End 30-12-2020
    }
    		
    if($ins_task_id){	
            $arr['msg']="1";							
    }
    else{
            $arr['msg']="0";
    }
    echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "add_appointment") {
        $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
        $show_user_ids			= show_user_ids($dbcon,$POST['assign_user_ids']);
        $info['task_location']		= $_POST['task_location'];
        $info['full_day_event']		= $_POST['full_day_event'];
        $info['appointment_start_time']	= date('Y-m-d H:i:s',strtotime($POST['appointment_start_time']));
        $info['appointment_end_time']	= date('Y-m-d H:i:s',strtotime($POST['appointment_end_time']));
        $info['appointment_subject']    = $_POST['appointment_subject'];
        $info['task_remark']		= $_POST['task_remark'];
        $info['assign_user_ids']	= implode(",",array_filter($POST['assign_user_ids']));
        $info['task_rel_id']		= 5;
        $info['task_name']              = $POST['task_name'];
        $info['c_con_id']		= $POST['c_con_id'];
        $info['cust_id']		= $POST['cust_id'];
        $info['inquiry_id']		= $POST['inquiry_id'];

        if($POST['task_alert_id'] && $POST['task_alert_id']!='1'){//If alert is not none
            $alert_date = date("Y-m-d H:i:s", strtotime($POST['appointment_start_time']));
            $gap_mints = get_alert_mintes($dbcon,$POST['task_alert_id']);
            $filt_alert_date = date("Y-m-d H:i:s", strtotime($alert_date . "-".$gap_mints." minutes"));//Subtract Minutes
            $info['alert_date_time']	= date('Y-m-d H:i:s',strtotime($filt_alert_date));
        }
        $info['task_alert_id']		= $POST['task_alert_id'];
        $info['create_date']		= date('Y-m-d H:i:s');
        $info['entry_type']		= 2;//Fixed Appointment Type
        $info['cdate']			= date("Y-m-d H:i:s");
        $info['user_id']		= $_SESSION['user_id'];
        $info['company_id']		= $_SESSION['company_id'];
        $info['show_user_ids']              = $show_user_ids;
        //echo '<pre>';print_r($info);
        $ins_task_id = add_record('tbl_task', $info, $dbcon, $branch_id);

        if($ins_task_id){
                $arr['msg']="1";							
        }
        else{
                $arr['msg']="0";
        }
        echo json_encode($arr);
}