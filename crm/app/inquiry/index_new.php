<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
// include_once(COMMON_FUNCTION_INNER_PATH."crm_common_functions.php");
include_once($incPath."common_send_email.php");
// Amish Soni End 30-12-2020
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INQUIRY_SLUG_EDIT,
    INQUIRY_SLUG_DELETE
]);


$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {

    $show_owner = TRUE; //please chenge in inquiry list also
    if($POST['start_date'] && $POST['end_date']){
        $_SESSION['start'] = $start_date = $POST['start_date'];
        $_SESSION['end'] = $end_date = $POST['end_date'];
    } 
    else if(isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) 
        && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])){
        $start_date = $_SESSION['summary_start_date'];
    $end_date = $_SESSION['summary_end_date'];
} else {
    $start_date = date('1-m-Y');
    $end_date = date("d-m-Y");
} 

$branch_id = $POST['branch_id'];

$where='';
if($branch_id){
    $where .= check_branch('inq',$branch_id);
}


$stage_where = '';
$stage_flag = TRUE;
if(isset($POST['stage_id']) && !empty($POST['stage_id'])){
    $where .= " AND inq.opp_id =".$POST['stage_id'];
    $stage_flag = FALSE;
}

if(isset($POST['sales_stage_id']) && !empty($POST['sales_stage_id'])){
    $where .= " AND inq.sales_stage_id IN(".$POST['sales_stage_id'].") ";
    $stage_flag = FALSE;
}

if(isset($POST['source_id']) && !empty($POST['source_id'])){
    $where .= " AND inq.rb_id IN(".$POST['source_id'].") ";
    $stage_flag = FALSE;
}

if(isset($POST['assign_user_id']) && !empty($POST['assign_user_id'])){
    $where .= " AND task.assign_user_ids = '".$POST['assign_user_id']."'";
    $stage_flag = FALSE;
}

if(isset($POST['user_id']) && !empty($POST['user_id'])){
    $where .= " AND usr.user_id IN(".$POST['user_id'].") ";
    $stage_flag = FALSE;
}

if(!empty($start_date) && !empty($end_date)){
    $where.="  AND inquiry_date >= '".date('Y-m-d',strtotime($start_date))."' AND inquiry_date <= '".date('Y-m-d',strtotime($end_date))."'";
}
if($stage_flag){
    $stage_where = " AND inq.opp_id NOT IN(12,13)";
}
$appData = array();
$i=1;
$aColumns = array('inq.inquiry_id','inq.owner_user_id','usr.user_name','owner_usr.user_name as owner', 'inq.inquiry_no', 'inq.inquiry_date', 'city.city_name', 'inq.inquiry_name', 'cust.cust_name','cust.cust_mobile', 'per.c_con_fname','per.c_con_mobile', 'stage.opp_stage','stage.opp_color','inq.stage_prob', 'inq.inquiry_status','inq.cdate','inq.mdate','inq.cust_id','inq.g_total','inq.company_id','updated_user.user_name as updated_by','tr.project_wise',
    'if(tr.project_wise=0,(SELECT group_concat(pro.product_name) FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as pro_name','(SELECT COUNT(task_id) FROM tbl_task WHERE inquiry_id = inq.inquiry_id) as task_count');
$sIndexColumn = "inq.inquiry_id";
$isWhere = array("inq.inquiry_status = 0  and inq.company_id in (0,$_SESSION[company_id]) ".$stage_where.$where);
$sTable = "tbl_inquiry as inq";
$isJOIN = array(
    'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
    'left join tbl_task as task on task.inquiry_id=inq.inquiry_id',
    'left join tbl_customer as cust on cust.cust_id=inq.cust_id',
    'left join tbl_cust_contact as per on per.c_con_id=inq.c_con_id', 
    'left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id', 
    'left join users as usr on usr.user_id=inq.user_id',
    'left join users as owner_usr on owner_usr.user_id=inq.owner_user_id',
    'left join users as updated_user on updated_user.user_id=inq.updated_by_userid',
    'left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.cust_id and c_add_status=0 limit 1)');
$hOrder = "inq.cdate desc";
$hGroupby = array("inq.inquiry_id");
include($incPath.'pagging.php');
    //$appData = array();
$id=1;

foreach($sqlReturn as $row) {
    $row_data = array();

    $query_i="select GROUP_CONCAT(DISTINCT mst.user_name SEPARATOR ',<br/>') as asinguser from users as mst
    where mst.user_id in (".$row['assign_user_ids'].")";
    $result_i=$dbcon->query($query_i);
    $rel_i=mysqli_fetch_assoc($result_i);

    $bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';

    $row_data[] = date('d M, Y',strtotime($row['inquiry_date']));
    if(in_array(INQUIRY_SLUG_EDIT,$bulkAccessArray) && $POST['stage_id'] != 12){
       $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["inquiry_no"].'</a>';
			//$row_data[] = $row['inquiry_name'];
       $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["cust_name"].'</a><br/>'.$row['inquiry_name'];
       $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["cust_mobile"].'</a>';
			//$row_data[] = $row['state_name'];
       $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["city_name"].'</a>';

       $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["pro_name"].'</a>';
   }else{
       $row_data[] = $row['inquiry_no'].' '.$row['COUNT(task.task_id)'];
			//$row_data[] = $row['inquiry_name'];
       $row_data[] = $row['cust_name'].'<br/>'.$row['inquiry_name'];
       $row_data[] = $row['cust_mobile'];
			//$row_data[] = $row['state_name'];
       $row_data[] = $row['city_name'];
       $row_data[] = $row['pro_name'];
   }
   $row_data[] = '<span class="btn btn-sm" style="color:black;background-color: '.$bg_color.';">'.$row['opp_stage'].'('.$row['stage_prob'].'%)<span>';

   if($show_owner){
    $row_data[] = $row['owner'];
}
$row_data[] = $row['user_name'];

$row_data[] = $row['updated_by'].' updated on '.date('d M, Y',strtotime($row['cdate'])).' by '.date('h:i A',strtotime($row['cdate']));

        //$row_data[] = $rel_i['asinguser'];
$edit='';$delete='';$view_hist_btn='';$send_email='';
if($row['COUNT(task.task_id)']==1){
if(in_array(INQUIRY_SLUG_EDIT,$bulkAccessArray) && $POST['stage_id'] != 12) {
    $edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'"><i class="fa fa-pencil"></i></a>';
}
if(in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray) && $POST['stage_id'] != 12) {
    $inquiry_no = $dbcon->real_escape_string($row['inquiry_no']);
    $delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inquiry('.$row['inquiry_id'].',\''.$inquiry_no.'\')"><i class="fa fa-trash-o"></i></button>';
}
}

$add_task_btn = $add_appointment_btn = '';
$view_hist_btn = '<button class="btn btn-xs btn-info" data-original-title="View History" data-toggle="tooltip" data-placement="top" onClick="view_followup_hist('.$row['inquiry_id'].')"><i class="fa fa-history"></i></button>';

$send_email = '<button class="btn btn-xs btn-primary" data-original-title="Send Email" data-toggle="tooltip" data-placement="top" onClick="open_inq_email('.$row['inquiry_id'].','.$row['cust_id'].')"><i class="fa fa-envelope"></i></button>'; 

if($POST['stage_id'] != 12){
    $add_task_btn = '<button class="btn btn-xs btn-primary" data-original-title="Add Task" data-toggle="tooltip" data-placement="top" onClick="open_add_task_popup('.$row['inquiry_id'].',1)"><i class="fa fa-list-alt"></i></button>';
}

if($POST['stage_id'] != 12){
    $add_appointment_btn = '<button class="btn btn-xs btn-primary" data-original-title="Add Appointment" data-toggle="tooltip" data-placement="top" onClick="open_add_task_popup('.$row['inquiry_id'].',2)"><i class="fa fa-clock-o"></i></button>';
}
$com_confi = getCompanyConfiguration($dbcon);
$inq_limit = ($com_confi['enable_inquiry_autoclose']==1) ? $com_confi['inquiry_autoclose_limit'] : 0;
        $days = $inq_limit." days";
        $inq_dates = date("Y-m-d",strtotime($row['inquiry_date']));
        $inq_date = date_create($inq_dates);
        date_add($inq_date, date_interval_create_from_date_string($days));
        $next_date = date_format($inq_date, 'Y-m-d');
        if($next_date < date("Y-m-d")){
            $add_task_btn = '';
            $add_appointment_btn = '';
        }
$row_data[] = $edit.' '.$delete.' '.$view_hist_btn.' '.$send_email.' '.$add_task_btn.' '.$add_appointment_btn;

$appData[] = $row_data;
$id++;
}
$output['aaData'] = $appData;
echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
   $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
   $products = get_inquiry_products($dbcon,'');
   if(empty($products)){
    $arr['msg'] = "2";
} else 
{

 $show_user_ids			= show_user_ids($dbcon,$POST['assign_user_inq_ids']);
 $info['inquiry_no']		= load_inquiry_no($dbcon);
        //Update Start series of No
        $query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE status=0 and type_id=2 and company_id=".$_SESSION['company_id']);
        
        $info['inquiry_type']        = $POST['inquiry_type'];
        $info['inquiry_date']           = date('Y-m-d',strtotime($POST['inquiry_date']));
        $info['cust_id']		= $POST['cust_id'];
        $info['c_con_id']		= $POST['c_con_id'];
        $info['assign_user_inq_ids']    = $POST['assign_user_inq_ids'];
        $info['inquiry_name']           = $POST['inquiry_name'];
        $info['closing_date']           = date('Y-m-d',strtotime($POST['closing_date']));
        $info['closed_reason']		= $POST['closed_reason'];
        $info['t_id']			= $POST['t_id'];
        $info['opp_id']			= $POST['opp_id'];
        $info['stage_prob']		= $POST['stage_prob'];
        $info['sales_stage_id']         = $POST['sales_stage_id'];
        $info['inquiry_type_id']        = $POST['inquiry_type_id'];
        $info['rb_id']			= $POST['rb_id'];
        $info['inquiry_cat_id']         = $POST['inquiry_cat_id'];
        $info['currency_id']            = $POST['currency_id'];
        $info['g_total']		= $POST['g_total'];
        $info['inq_desc']		= $POST['inq_desc'];
        $info['inq_comp_desc']          = $POST['inq_comp_desc'];
		$info['project_name']       = $POST['project_name'];
        $info['create_date']            = date('Y-m-d H:i:s');
        $info['cdate']			= date("Y-m-d H:i:s");
        $info['owner_user_id']          = $_SESSION['user_id'];
        $info['user_id']		= $POST['assign_user_inq_ids']; //$_SESSION['user_id'];
        $info['show_user_ids']          = $show_user_ids;
        $info['updated_by_userid']      = $_SESSION['user_id'];
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

        $ins_inquiry_id=add_record('tbl_inquiry', $info, $dbcon, $branch_id);
        
        /*Insert in task table, when new inquiry add */
        $task_info['show_user_ids']	= $show_user_ids;
        $task_info['task_type_id']	= $POST['task_type_id'];
        $task_info['task_rel_id']	= 5;
        $task_info['task_name']		= $POST['inquiry_name'];
        $task_info['c_con_id']		= $POST['c_con_id'];
        $task_info['cust_id']		= $POST['cust_id'];
        $task_info['inquiry_id']	= $ins_inquiry_id;
        $task_info['opp_id']		= $POST['opp_id'];
        $task_info['sales_stage_id']    = $POST['sales_stage_id'];
        $task_info['stage_prob']	= $POST['stage_prob'];
        $task_info['task_remark']	= 'New Inquiry Added';
        $task_info['assign_user_ids']   = $POST['assign_user_inq_ids'];
        $task_info['task_priority_id']  = $POST['task_priority_id'];
        $task_info['cdate']             = date("Y-m-d H:i:s");
        $task_info['create_date']       = date('Y-m-d H:i:s');
        $task_info['task_due_date']     = date("Y-m-d H:i:s",strtotime($POST['task_due_date']));
        $task_info['entry_type']	= 1;//Fixed Task Type
        $task_info['user_id']		= $POST['assign_user_inq_ids'];
        $task_info['company_id']	= $_SESSION['company_id'];
        $task_info['is_delete']    = 1;
        
        $ins_task_id=add_record('tbl_task', $task_info, $dbcon, $branch_id);
        //echo '<pre>';        print_r($task_info);
        //exit;
        
        //if Inquiry won without Quotation, auto create Quotation 
        if($POST['opp_id'] == WON && !check_has_quotation($dbcon,$ins_inquiry_id)){
            auto_create_quotation($dbcon,$POST,$ins_inquiry_id);
            $info['won_by_userid']      = $_SESSION['user_id'];
            $info['won_user_id']	= $_SESSION['user_id'];
            $info['won_date']           = date("Y-m-d H:i:s");
            update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s")), "inquiry_id=".$ins_inquiry_id, $dbcon, $branch_id);
            $updateid = update_record('tbl_inquiry', $info, "inquiry_id=".$ins_inquiry_id, $dbcon);
        }
        
        /*Update Trn Table Start*/
        if($ins_inquiry_id){
            $infotrn['inquiry_id']			= $ins_inquiry_id;
            $infotrn['inquiry_trn_status']	= 0;

            $updatetrnid=update_record('tbl_inquiry_trn', $infotrn,"inquiry_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
        }
        /*Update Trn Table End*/

        /*Update Note Trn Table Start*/
        if($ins_inquiry_id){
            $infonote['inquiry_id']			= $ins_inquiry_id;
            $infonote['inq_note_status']	= 0;

            $updatetrnid=update_record('tbl_inq_notes', $infonote,"inq_note_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
        }
        /*Update Note Trn Table End*/

        /*Update Attach Trn Table Start*/
        if($ins_inquiry_id){
            $infoattch['inquiry_id']			= $ins_inquiry_id;
            $infoattch['inq_attach_status']	= 0;

            $updatetrnid=update_record('tbl_inq_attach', $infoattch,"inq_attach_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
        }
        /*Update Attach Trn Table End*/
        /*Code By Umair : 23-06-2021
          Comment: Update tbl_inquiry_project_trn Table Start
          START
          */

          if($ins_inquiry_id && $POST['inquiry_type']!='1'){
            $infoproject['inquiry_id']            = $ins_inquiry_id;
            $infoproject['inquiry_projecttrn_status'] = 0;

            $updatetrnid=update_record('tbl_inquiry_project_trn', $infoproject,"inquiry_projecttrn_status=4 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
        }
        /* END*/

        if($ins_inquiry_id){	
            $arr['msg']="1";

            // Amish Soni Start 30-12-2020
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
            $cur_user = getUserDetailById($dbcon, $cur_user_id);
            $customer = getCustDetailById($dbcon, $POST['cust_id']);
            $from_email_id = ($cur_user && $cur_user['user_email']) ? $cur_user['user_email'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';
//      	$cust_name = ($customer && $customer['cust_name']) ? $customer['cust_name'] : '';

            if($mail_template && $to_email_id) {
                $subject = $mail_template['email_subject'];
                // Amish Soni Start 18-01-2021
//                $subject = str_replace('{{CUSTOMER NAME}}', $cust_name, $subject);
//                $subject = str_replace('{{CUSTOMER EMAIL}}', $to_email_id, $subject);

                $content = $mail_template['email_content'];
//                $content = str_replace('{{CUSTOMER NAME}}', $cust_name, $content);
//                $content = str_replace('{{CUSTOMER EMAIL}}', $to_email_id, $content);

                $subject = replaceMergeFields($dbcon, $subject, $POST['cust_id'], $module_id);
                $content = replaceMergeFields($dbcon, $content, $POST['cust_id'], $module_id);
                // Amish Soni End 18-01-2021

                final_send_email($from_email_id, $to_email_id, '', '', $subject, $content);
            }
            // Amish Soni End 30-12-2020
        }
        else{
           $arr['msg']="0";
       }
   }
   echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "edit") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

    $old_users = $dbcon->query("SELECT assign_user_inq_ids FROM tbl_inquiry WHERE inquiry_id = ".$POST['eid'])
    ->fetch_object()->assign_user_inq_ids;
    if($old_users){
        $assigned_users_arr = explode(',', $old_users);
        if(!in_array($POST['assign_user_inq_ids'], $assigned_users_arr)){
            $assigned_users = $old_users.','.$POST['assign_user_inq_ids'];
        } else {
            $assigned_users = $old_users;
        }
    }
    
        $show_user_ids			= show_user_ids($dbcon,$POST['assign_user_inq_ids']);
        $info['show_user_ids']          = $show_user_ids;
        $info['inquiry_date']           = date('Y-m-d',strtotime($POST['inquiry_date']));
        $info['cust_id']		= $POST['cust_id'];
        $info['c_con_id']		= $POST['c_con_id'];
        $info['assign_user_inq_ids']    = $assigned_users;
        $info['inquiry_name']           = $POST['inquiry_name'];
        $info['closing_date']           = date('Y-m-d',strtotime($POST['closing_date']));
        $info['closed_reason']		= $POST['closed_reason'];
        $info['t_id']			= $POST['t_id'];
        $info['opp_id']			= $POST['opp_id'];
        $info['stage_prob']		= $POST['stage_prob'];
        $info['sales_stage_id']         = $POST['sales_stage_id'];
        $info['inquiry_type_id']        = $POST['inquiry_type_id'];
        $info['rb_id']			= $POST['rb_id'];
        $info['inquiry_cat_id']         = $POST['inquiry_cat_id'];
        $info['currency_id']            = $POST['currency_id'];
        $info['g_total']		= $POST['g_total'];
        $info['inq_desc']		= $POST['inq_desc'];
        $info['inq_comp_desc']          = $POST['inq_comp_desc'];
        $info['cdate']			= date("Y-m-d H:i:s");
        $info['updated_by_userid']      = $_SESSION['user_id'];
        $info['user_id']		= $POST['assign_user_inq_ids'];
        $info['project_name']       = $POST['project_name'];
        //$info['company_id']		= $_SESSION['company_id'];
    if($POST['opp_id'] == WON){
        $info['won_by_userid']      = $_SESSION['user_id'];
        $info['won_user_id']	= $_SESSION['user_id'];
        $info['won_date']           = date("Y-m-d H:i:s");
        update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s")), "inquiry_id=".$POST['eid'], $dbcon, $branch_id);
    }
    if($POST['opp_id'] == LOST){
        $reason = array();
        if(!empty($POST['reason_id']) && !empty($POST['lost_reason'])){
            $reason = array_combine($POST['reason_id'],$POST['lost_reason']);
        }
        $info['lost_by_userid']      = $_SESSION['user_id'];
        $info['closed_reason']       = json_encode($reason);

        update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s")), "inquiry_id=".$POST['eid'], $dbcon, $branch_id);
    }

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

        //echo '<pre>';print_r($info);exit;
    $updateid = update_record('tbl_inquiry', $info, "inquiry_id=".$POST['eid'], $dbcon);
    if($updateid){
            // if Inquiry won without Quotation, auto create Quotation 
        if($POST['opp_id'] == WON && !check_has_quotation($dbcon,$POST['eid'])){
            auto_create_quotation($dbcon,$POST);
        }
        $arr['msg']="update";

            // Amish Soni Start 30-12-2020
            $module_id = 2; //CRM Module
            // Amish Soni Start 19-01-2021
            if($showTemplate) {
                if(isset($POST['email_template_id']) && $POST['email_template_id']){
                    $mail_template = getEmailSMSTemplateById($dbcon, $POST['email_template_id']);
                }
            } else {
                $mail_template = getEmailSMSTemplate($dbcon, $module_id, 16, $POST['opp_id']);
            }
            // Amish Soni End 19-01-2021

            $cur_user_id = $_SESSION['user_id'];
            $cur_user = getUserDetailById($dbcon, $cur_user_id);
            $customer = getCustDetailById($dbcon, $POST['cust_id']);
            $from_email_id = ($cur_user && $cur_user['user_email']) ? $cur_user['user_email'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';
//            $cust_name = ($customer && $customer['cust_name']) ? $customer['cust_name'] : '';
            
            if($mail_template && $to_email_id) {
                // Amish Soni Start 18-01-2021
                $subject = $mail_template['email_subject'];
                $content = $mail_template['email_content'];

                $subject = replaceMergeFields($dbcon, $subject, $POST['cust_id'], $module_id);
                $content = replaceMergeFields($dbcon, $content, $POST['cust_id'], $module_id);
                // Amish Soni End 18-01-2021
                final_send_email($from_email_id, $to_email_id, '', '', $subject, $content);
            }
            // Amish Soni End 30-12-2020
        }
        else{
            $arr['msg']=0;
        }
        echo json_encode($arr);
    }
    else if(strtolower($POST['mode']) == "delete") {
        $info['inquiry_status']	= 2;
        $infotrn['inquiry_trn_status']	= 2;
        $infoprojecttrn['inquiry_projecttrn_status']  = 2;
        $updateid = update_record('tbl_inquiry', $info, "inquiry_id=".$POST['inquiry_id'], $dbcon);
        $updatetrnid = update_record('tbl_inquiry_trn', $infotrn, "inquiry_id=".$POST['inquiry_id'], $dbcon);
        $updateprojecttrnid = update_record('tbl_inquiry_project_trn', $infoprojecttrn, "inquiry_id=".$POST['inquiry_id'], $dbcon);

        if($updateid)
            echo "1";	
        else
            echo "0";			
    }
    else if(strtolower($POST['mode']) == "add_field") {

        $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
        $info1['inquiry_type']    = $POST['inquiry_type'];
        $info1['product_id']	= $POST['product_id'];
        $info1['cat_id']	= $POST['cat_id'];
        $info1['pg_id']		= $POST['pg_id'];
        $info1['level_id']	= $POST['level_id'];
        $info1['unitid']	= $POST['unitid'];
        $info1['product_qty']	= $POST['product_qty'];
        $info1['product_rate']	= $POST['product_rate'];
        $info1['product_amount']= $POST['product_amount'];
        $info1['product_desc']	= text_rnremove($_POST['product_desc']);
        $info1['product_spec']	= text_rnremove($_POST['product_spec']);
        $info1['user_id']		= $_SESSION['user_id'];
        $info1['company_id']	= $_SESSION['company_id'];

        $table='tbl_inquiry_trn';$tableid='inquiry_trn_id';
        if(!empty($POST['inquiry_id'])) {
            $info1['inquiry_id']= $POST['inquiry_id'];
        }
        else{
            $info1['inquiry_trn_status']= 3;
        }
        
        if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
            $info1['project_wise']= 1;
        }

        if(empty($POST['edit_id'])) {
            $inserid = add_record($table, $info1, $dbcon, $branch_id);
            $updateinfo['inquiry_trn_id'] = $inserid; 
        }
        else {
            $updateid = update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
            $updateinfo['inquiry_trn_id'] = $updateid; 

            if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise' && $POST['old_product_id']!=$POST['product_id']){
                $updatein['inquiry_projecttrn_status'] = 2; 
                update_record('tbl_inquiry_project_trn', $updatein, "inquiry_trn_id=".$POST['edit_id']." and project_assign_id=".$POST['old_product_id'] , $dbcon, $branch_id);
            }
        }

        /*
            Code By Umair: 23-06-2021
            Comment : Update tbl_inquiry_project_trn inquiry_projecttrn_status status
            START
        */
            if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
                $updateinfo['inquiry_projecttrn_status'] = 4; 
                update_record('tbl_inquiry_project_trn', $updateinfo, "project_assign_id=".$POST['product_id']." and inquiry_projecttrn_status=3" , $dbcon, $branch_id);
            }

            /* END */   
        }
        else if(strtolower($POST['mode'])=="show_data") {
            $str='';
            $delete_btn_per=check_permission('crm/inquiry_list',$_SESSION['user_id'],'delete',$dbcon);
            $chkmode=$POST['modee'];
            $products = get_inquiry_products($dbcon, $POST['inquiry_id']);
			//print_r($products);
            $str.='<table class="display table table-bordered table-striped" style="width:110%;">
            <tr>';
            if($chkmode!='VIEW'){ 
                $str.='<th width="5%" class="text-center">Action</th>';
            }
            $str.='<th width="20%" class="text-center">Product Name</th>
            <!--<th width="10%" class="text-center">Product Category</th>-->
            <!--<th width="10%" class="text-center">Product Group</th>-->
            <!--<th width="2%" class="text-center">Level</th>-->
            <th width="5%" class="text-center">Quantity</th>
            <th width="3%" class="text-center">Unit</th>
            <th width="8%" class="text-center">Rate</th>
            <th width="12%" class="text-center">Amount</th>				  
            <th width="10%" class="text-center">Specification</th>				  
            </tr>
            <tbody>';
            if($products){
                $i=1;
                foreach ($products as $rel) {

                    $str.='<tr> ';
                        //echo $chkmode;
                    if($chkmode!='VIEW'){ 
                        $str.='<td style="vertical-align:middle"> 
                        <button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data('.$rel['inquiry_trn_id'].','.$rel['project_wise'].')"><i class="fa fa-pencil"></i></button>';
                        if($delete_btn_per){
                            $str .= '&nbsp;<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_trn_data('.$rel['inquiry_trn_id'].','.$rel['project_wise'].')">X</button>';
                        }
                        $str .= '</td>';
                    } 
                    $str.='<td style="vertical-align:top;">
                    <strong>'.$rel['product_name'].'</strong><br/>
                    <strong>Desc:</strong> '.($rel['product_desc'] ? (nl2br($rel['product_desc'])) : (nl2br($rel['description']))).'
                    </td>
                    <!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['cat_name'].'
                    </td>-->
                    <!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['pg_name'].'
                    </td>-->
                    <!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['level_id'].'
                    </td>-->
                    <td style="vertical-align:top;" class="text-center">
                    '.$rel['product_qty'].'
                    </td>
                    <td style="vertical-align:top;" class="text-center">
                    '.$rel['unit_name'].'
                    </td>
                    <td style="vertical-align:top;" class="text-right">
                    '.$rel['product_rate'].'
                    </td>
                    <td style="vertical-align:top;" class="text-right">
                    <input type="hidden" name="amount[]" value="'.$rel['product_amount'].'">
                    '.$rel['product_amount'].'
                    </td>
                    <td style="vertical-align:top;">
                    '.$rel['product_spec'].'
                    </td>	
                    </tr>';
                    $i++;
                }
            } else{
                $str.= '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
            }

            $str.= '</tbody>
            </table>';
            echo $str;
        }
        else if(strtolower($POST['mode'])== "edit_trn_data") {
            $q = $dbcon -> query("SELECT trn.*,pmst.product_name FROM tbl_inquiry_trn as trn left join product_mst as pmst on pmst.product_id=trn.product_id WHERE inquiry_trn_id = '$POST[inquiry_trn_id]'");
            $r = $q->fetch_assoc();
            echo json_encode($r);
        }
        else if(strtolower($POST['mode'])== "delete_trn_data") {
          $row=array();
          $info['inquiry_trn_status']=2;	
          $info1['inquiry_projecttrn_status']=2;  
          $updateid=update_record('tbl_inquiry_trn', $info, "inquiry_trn_id=".$POST['inquiry_trn_id'] , $dbcon);
          $updatprojecteid=update_record('tbl_inquiry_project_trn', $info1, "inquiry_trn_id=".$POST['inquiry_trn_id'] , $dbcon);

          if($updateid)
           $row['res']="1";
       else
           $row['res']="0";
       echo json_encode($row);
   }
   else if(strtolower($POST['mode']) == "add_inq_note_field") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

    $info1['inq_note_title']	= $POST['inq_note_title'];
    $info1['inq_note_desc']		= $_POST['inq_note_desc'];
    $info1['user_id']			= $_SESSION['user_id'];
    $info1['company_id']		= $_SESSION['company_id'];

    $table='tbl_inq_notes';$tableid='inq_note_id';
    if(!empty($POST['inquiry_id'])) {
       $info1['inquiry_id']= $POST['inquiry_id'];
   }
   else{
       $info1['inq_note_status']= 3;
   }

   if(empty($POST['edit_inq_noteid'])) {
       $inserid=add_record($table, $info1, $dbcon, $branch_id);
   } else {
       $updateid=update_record($table, $info1,$tableid."=".$POST['edit_inq_noteid'] , $dbcon, $branch_id);	
   }
}
else if(strtolower($POST['mode'])== "show_inq_note_data") {
    $delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_type'],'delete',$dbcon);
    $chkmode=$POST['chkmode'];
    if($POST['inquiry_id']){
        $query="select mst.* from tbl_inq_notes as mst 
        where inq_note_status=0 and mst.inquiry_id=".$POST['inquiry_id'];
    }
    else{
        $query="select mst.* from tbl_inq_notes as mst 
        where inq_note_status=3 and mst.user_id=".$_SESSION['user_id'];
    }
    $result=$dbcon->query($query);
    echo '<table class="display table table-bordered table-striped">

    <tr>
    <th width="30%" class="text-center">Title</th>
    <th width="60%" class="text-center">Description</th>';
    if($chkmode!='VIEW')
    {
        echo'<th width="10%" class="text-center">Action</th>';
    }
    echo'</tr>
    <tbody>';
    if(mysqli_num_rows($result)>0)
    {
        $i=1;
        while($rel=mysqli_fetch_assoc($result))
        {
            echo '<tr> 
            <td style="vertical-align:top;">
            <strong>'.$rel['inq_note_title'].'</strong>
            </td>
            <td style="vertical-align:top;" class="text-center">
            '.$rel['inq_note_desc'].'
            </td>';
            if($chkmode!='VIEW')
            {
                echo '<td style="vertical-align:top"> 
                <button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_inq_note_data('.$rel['inq_note_id'].')"><i class="fa fa-pencil"></i></button>';
                if($delete_btn_per){
                    echo ' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inq_note_data('.$rel['inq_note_id'].')">X</button>';
                }
                echo '</td>';
            }
            echo'</tr>';
            $i++;
        }
    }
    else{
        echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
    }

    echo '</tbody>
    </table>';
}
else if(strtolower($POST['mode'])== "edit_inq_note_data") {
    $q = $dbcon -> query("SELECT mst.* FROM tbl_inq_notes as mst WHERE inq_note_id = '$POST[inq_note_id]'");
    $r = $q->fetch_assoc();
    echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_inq_note_data") {
    $row=array();
    $info['inq_note_status']=2;	
    $updateid=update_record('tbl_inq_notes', $info, "inq_note_id=".$POST['inq_note_id'] , $dbcon);

    if($updateid)
        $row['res']="1";
    else
        $row['res']="0";
    echo json_encode($row);
}
else if(strtolower($POST['mode'])== "add_inq_attch_field") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    $info1['inq_attch_doc_name']    = $POST['inq_attch_doc_name'];
    $info1['inq_attch_file']	= upload_attch_file($_FILES);
    $info1['user_id']		= $_SESSION['user_id'];
    $info1['company_id']		= $_SESSION['company_id'];

    $table='tbl_inq_attach';$tableid='inq_attach_id';
    if(!empty($POST['inquiry_id'])) {
        $info1['inquiry_id']= $POST['inquiry_id'];
    }
    else{
        $info1['inq_attach_status']= 3;
    }

    $inserid=add_record($table, $info1, $dbcon, $branch_id);
}
else if(strtolower($POST['mode'])== "show_inq_attach_data") {
    $chkmode=$POST['modee'];
    $delete_btn_per = in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray);
    if($POST['inquiry_id']){
        $query="select mst.* from tbl_inq_attach as mst 
        where inq_attach_status=0 and mst.inquiry_id=".$POST['inquiry_id'];
    }
    else{
        $query="select mst.* from tbl_inq_attach as mst 
        where inq_attach_status=3 and mst.user_id=".$_SESSION['user_id'];
    }
    $result=$dbcon->query($query);
    echo '<table class="display table table-bordered table-striped">
    <tr>
    <th width="60%" class="text-center">Document Name</th>
    <th width="30%" class="text-center">Attached Document</th>';
    if($chkmode!='VIEW' && $delete_btn_per)
    {
        echo'<th width="10%" class="text-center">Action</th>';
    }
    echo'</tr>
    <tbody>';
    if(mysqli_num_rows($result)>0)
    {
        $i=1;
        while($rel=mysqli_fetch_assoc($result))
        {
            echo '<tr> 
            <td style="vertical-align:top;">
            <strong>'.$rel['inq_attch_doc_name'].'</strong>
            </td>
            <td style="vertical-align:top;" class="text-center">
            <a href="'.ROOT.INQ_ATTACH_VWING.$rel['inq_attch_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
            </td>';
            if($chkmode!='VIEW')
            {
                if($delete_btn_per){
                    echo '<td style="vertical-align:top"> 
                    <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inq_attach_data('.$rel['inq_attach_id'].')">X</button>
                    </td>';
                }
            }
            echo '</tr>';
            $i++;
        }
    }
    else{
        echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
    }

    echo '</tbody>
    </table>';
}
else if(strtolower($POST['mode'])== "delete_inq_attach_data") {
    $row=array();
    $del_attch_qry="select inq_attch_file from tbl_inq_attach where inq_attach_id=".$POST['inq_attach_id'];
    $del_attch_rel=mysqli_fetch_assoc($dbcon->query($del_attch_qry));
    unlink('../'.INQ_ATTACH_UPING.$del_attch_rel['inq_attch_file']);

    $info['inq_attach_status']=2;	
    $updateid=update_record('tbl_inq_attach', $info, "inq_attach_id=".$POST['inq_attach_id'] , $dbcon);

    if($updateid)
        $row['res']="1";
    else
        $row['res']="0";
    echo json_encode($row);
}
else if(strtolower($POST['mode'])== "load_product_dtls") {
    $pro_qry="select * from product_mst where product_id=".$POST['product_id'];
    $pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
    $pro_rel['current_stock']=get_current_stock_new($dbcon, $POST['product_id'], $pro_rel['product_base_unit']);
    echo json_encode($pro_rel);
}
else if(strtolower($POST['mode'])== "view_followup_hist") {
    $inquiry_id=$POST['inquiry_id'];$str='';
    $inq_qry="select inq.inquiry_id,inq.inquiry_date,inq.inq_desc,inq.inq_comp_desc,inq.inquiry_name,inq.opp_id,cust.cust_name,
    usr.user_name, inq.inquiry_no, inq.inquiry_date, inq.inquiry_name, cust.cust_name,
    cust.cust_mobile,per.c_con_email, per.c_con_fname, per.c_con_lname,mcd.mcd_name,rb.rb_name,
    stage.opp_stage, stage.opp_color, inq.inquiry_status,inq.cdate,inq.cust_id,inq.g_total,inq.cdate
    from tbl_inquiry as inq
    left join tbl_customer as cust on cust.cust_id=inq.cust_id
    left join tbl_cust_contact as per on per.c_con_id=inq.c_con_id
    left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id
    left join users as usr on usr.user_id=inq.user_id
    left join tbl_master_category_detail as mcd on mcd.mcd_id = inq.sales_stage_id
    left join tbl_refer_by as rb on rb.rb_id = inq.rb_id
    where inq.inquiry_id=".$inquiry_id;
    $inq_rel=mysqli_fetch_assoc($dbcon->query($inq_qry));
    $str.='<div class="col-md-12">
    <div class="col-md-6"><strong>Inquiry Number : </strong>'.$inq_rel['inquiry_no'].'</div>
    <div class="col-md-6"><strong>Inquiry Date : </strong>'.date('d-M-Y', strtotime($inq_rel['inquiry_date'])).'</div>
    </div>';
    $str.='<div class="col-md-12">
    <div class="col-md-6"><strong>Company Name : </strong>'.$inq_rel['cust_name'].'</div>
    <div class="col-md-6"><strong>Contact Person : </strong>'.$inq_rel['c_con_fname'].' '.$inq_rel['c_con_lname'].'</div>
    </div>';
    $str.='<div class="col-md-12">
    <div class="col-md-6"><strong>Mobile : </strong>'.$inq_rel['cust_mobile'].'</div>
    <div class="col-md-6"><strong>Email : </strong>'.$inq_rel['c_con_email'].'</div>
    </div>';
    $str.='<div class="col-md-12">
    <div class="col-md-3"><strong>Sales stage : </strong>'.$inq_rel['mcd_name'].'</div>
    <div class="col-md-3"><strong>Stage : </strong>'.$inq_rel['opp_stage'].'</div>
    <div class="col-md-3"><strong>Source : </strong>'.$inq_rel['rb_name'].'</div>
    <div class="col-md-3"><strong>Total : </strong>'.$inq_rel['g_total'].'</div>
    </div>';
    $str.='<br/><div class="col-md-12 text-left"><h4>Remarks</h4></div>';
    $str .= '<table class="display table table-bordered table-striped">
    <tr>
    <td width="25%" class="text-left">
    <strong>Description: </strong>'.$inq_rel['inq_desc'].'
    </td>
    <td width="25%" class="text-left">
    <strong>Competition Status: </strong>'.$inq_rel['inq_comp_desc'].'
    </td>
    </tr>
    </table>';
    $str .= '<div class="col-md-12 text-left"><h4>Task</h4></div>';

    $str.='<table class="display table table-bordered table-striped">
    <tr>
    <th class="text-center">Created</th>
    <th class="text-center">Due Date</th>
    <th class="text-center">Type</th>
    <th class="text-center">Owner</th>
    <th class="text-center">Priority</th>
    <th class="text-center">Status</th>
    <th class="text-center">Remarks</th>
    </tr>';

    $flp_qry="select task.*,type.mcd_name,usr.user_name,prior.task_priority_name from tbl_task as task
    left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id
    left join users as usr on usr.user_id=task.user_id
    left join task_priority_mst as prior on prior.task_priority_id=task.task_priority_id
    where task.task_status!=2 and task.entry_type=1 and task.inquiry_id=".$inquiry_id." order by create_date DESC";
    $flp_qry_rs=$dbcon->query($flp_qry);
    if(mysqli_num_rows($flp_qry_rs)){
        while($flp_rel=mysqli_fetch_assoc($flp_qry_rs)){
            $str.='<tr>
            <td class="text-left">'.(date("d-M-Y h:i A",strtotime($flp_rel['create_date']))).'</td>
            <td class="text-left">'.(date("d-M-Y h:i A",strtotime($flp_rel['task_due_date']))).'</td>
            <td class="text-left">'.$flp_rel['mcd_name'].'</td>
            <td class="text-left">'.$flp_rel['user_name'].'</td>
            <td class="text-center">'.$flp_rel['task_priority_name'].'</td>';

            $tsk_type="";
            $tsk_due_time=strtotime($flp_rel['task_due_date']);

            if($flp_rel['task_status']=='1'){ 
                $cur_time=strtotime($flp_rel['task_completion_date']);
                if($tsk_due_time<$cur_time){
                    $tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
                }
                $str .= '<td class="text-center btn-success">Completed'.$tsk_type.'</td>';
            } else {
                $cur_time = strtotime(date('Y-m-d H:i:s'));
                if($tsk_due_time<$cur_time){
                    $tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
                }
                $str .= '<td class="text-center btn-warning">Pending'.$tsk_type.'</td>';
            }
            $str.='<td class="text-center">'.(nl2br($flp_rel['task_remark'])).'</td>';
            $str.='</tr>';
        }
    }
    else{
        $str.='<tr><td colspan="7" class="text-center">NO DATA FOUND!!!</td></tr>';
    }
    $str.='</table>';

    $str.='<div class="col-md-12 text-left"><h4>Appointment</h4></div>';

    $str.='<table class="display table table-bordered table-striped">
    <tr>
    <th class="text-center">Location</th>
    <th class="text-center">Subject</th>
    <th class="text-center">Start Time</th>
    <th class="text-center">End Time</th>
    <th class="text-center">Status</th>
    <th class="text-center">Remarks</th>
    </tr>';

    $task_qry="select task.* from tbl_task as task
    where task.task_status!=2 and task.entry_type=2 and task.inquiry_id=".$inquiry_id." order by create_date DESC";
    $task_qry_rs=$dbcon->query($task_qry);
    if(mysqli_num_rows($task_qry_rs)){
        while($apt_rel=mysqli_fetch_assoc($task_qry_rs)){
            $str.='<tr>
            <td class="text-left">'.$apt_rel['task_location'].'</td>
            <td class="text-left">'.$apt_rel['appointment_subject'].'</td>
            <td class="text-left">'.(date("d-M-Y H:i: A",strtotime($apt_rel['appointment_start_time']))).'</td>
            <td class="text-left">'.(date("d-M-Y H:i: A",strtotime($apt_rel['appointment_end_time']))).'</td>';

            $tsk_type="";
            $tsk_due_time=strtotime($apt_rel['appointment_end_time']);

            if($apt_rel['task_status']=='1'){ 
                $cur_time=strtotime($apt_rel['task_completion_date']);
                if($tsk_due_time<$cur_time){
                    $tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
                }
                $str .= '<td class="text-center btn-success">Completed'.$tsk_type.'</td>';
            } else {
                $cur_time = strtotime(date('Y-m-d H:i:s'));
                if($tsk_due_time<$cur_time){
                    $tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
                }
                $str .= '<td class="text-center btn-warning">Pending'.$tsk_type.'</td>';
            }
            $str.='<td class="text-center">'.(nl2br($apt_rel['task_remark'])).'</td>';
            $str.='</tr>';
        }
    }
    else{
        $str.='<tr><td colspan="7" class="text-center">NO DATA FOUND!!!</td></tr>';
    }
    $str .='</table>';

    if($inq_rel['opp_id'] != WON) {
        $str .= '<div class="col-md-1">
        <a onclick="open_add_task_popup('.$inq_rel['inquiry_id'].',1);"  type="button" class="btn btn-primary" ><i class="fa fa-plus"></i> Task</a>
        </div>
        <div class="col-md-1">
        <a onclick="open_add_task_popup('.$inq_rel['inquiry_id'].',2);" type="button" class="btn btn-info"><i class="fa fa-plus"></i> Appointment</a>
        </div>';
    }

    $resp['inq_name'] = $inq_rel['inquiry_name'];
    $resp['html_resp']=$str;
    echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "open_inq_email") {
    $set="select inq_email_content from tbl_company where company_id=".$_SESSION['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));
    $email_content = $set_head['inq_email_content'];
    $resp['email_content']	= $email_content;

        //Get Customer Detail
    $custqry="select cust_email from tbl_customer where cust_id=".$POST['cust_id'];
    $cust_rel=mysqli_fetch_assoc($dbcon->query($custqry));
    $resp['to_email_id']	= strtolower($cust_rel['cust_email']);

    echo json_encode($resp);
}
else if(strtolower($POST['mode'])== "send_mail") {
    $inquiry_id=strtolower($POST['email_ref_id']);
    $to_email_id=strtolower($POST['to_email_id']);
    $ccemail_id=strtolower($POST['ccemail_id']);
    $bccemail_id=strtolower($POST['bccemail_id']);
    $email_subject=$_POST['email_subject'];
    $email_content=$_POST['email_content'];
    if(!empty($_FILES['email_attach']['tmp_name'])) {
        $file = upload_mail_attch_file($_FILES,$dbcon);
    }

    $files=array();
    array_push($files,$file);
    $resp=final_send_email($to_email_id,$ccemail_id,$bccemail_id,$email_subject,$email_content,$files);
    unlink(MAIL_ATTACH_UPING.$file);

    $arr['msg']=array();
    if($resp['code']=='success'){
        $arr['msg']='1';
    }
    else{
        $arr['msg']='0';
    }
    echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "has_quotation") {
    if($POST['inquiry_id']){
        $quotation_id = check_has_quotation($dbcon,$POST['inquiry_id']);
    }
    echo ($quotation_id) ? $quotation_id : 0;
}
else if(strtolower($POST['mode'])== "has_product") {
	$products = get_inquiry_products($dbcon,$POST['inquiry_id']);
	echo ($products) ? json_encode($products) : 0;
}
else if(strtolower($POST['mode'])== "load_inquiry_data"){
    $inq_qry = "select * from tbl_inquiry where inquiry_id =".$POST['inquiry_id'];
    $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
    echo json_encode($inq_data);
}
else if(strtolower($POST['mode'])== "load_inquiry_type"){
    $inq_qry = "select inquiry_id from tbl_inquiry where cust_id =".$POST['cust_id'];
    $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
    echo ($inq_data) ? true : false;
    //echo json_encode($inq_data);
} else if(strtolower($POST['mode']) == "add_lost_reason"){
    $counter = $POST['counter'];
    $html = '';
    $html .= '<div class="col-md-8 lost_reasons" id="lost_reason_div'.$counter.'" style="float: right;display: none;">';
    $html .= '<div class="form-group">';
    $html .= '<label class="col-md-2 control-label" style="text-align: right;">Reason</label>';
    $html .= '<div class="col-md-3">';
    $html .= '<select class="select2" id="reason_id'.$counter.'" name="reason_id[]">';
    $html .= get_lost_reasons($dbcon,$id);
    $html .= '</select>';
    $html .= '</div>';
    $html .= '<label class="col-md-2 control-label">Reason Remark</label>';
    $html .= '<div class="col-md-3">'; 
    $html .= '<textarea class="form-control" name="lost_reason[]" id="lost_reason'.$counter.'" style="resize:both;" placeholder="Lost Reason" rows="1"/></textarea>';
    $html .= '</div>';	
    $html .= '<div class="col-md-2">'; 
    $html .= '<button type="button" id="reason_btn'.$counter.'" class="btn btn-primary" title="View Details" onclick="remove_reason_div(this)"><i class="add_remove_reason fa fa-minus"></i></button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    $resp['html'] = $html;
    echo json_encode($resp);
}
/*
Code By Umair : 23-06-2021
Comment: Load Product Based On the Inquiry Type
START
*/
else if(strtolower($POST['mode'])== "load_inquiry_type_product"){
    $inquiry_type = $POST['inquiry_type'];

    if($inquiry_type=='1'){
        $arr['product_list'] = getproduct_typewise($dbcon,"",$_POST['pro_type'],$_POST['pro_search']);
    }elseif($inquiry_type=='2'){
        $getProjectList ='<option value="" >Choose Product</option>';
        $getProjectList .= getProjectList($dbcon,"");
        $arr['product_list'] = $getProjectList;
        
    }
    elseif($inquiry_type=='3'){
        $product_list = getproduct_typewise($dbcon,"",$_POST['pro_type'],$_POST['pro_search']);
        $product_list .= getProjectList($dbcon,"");
        $arr['product_list'] = $product_list;
    }

    echo json_encode($arr);
}
else if(strtolower($POST['mode'])== "add_project_data"){
    $inquiry_id = $POST['eid'];
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    //if($inquiry_id==''){
    $project_assign_id = $POST['project_assign_id'];
    $inquiry_type = $POST['inquiry_type'];

    $update['inquiry_projecttrn_status'] = 2;
    update_record('tbl_inquiry_project_trn', $update, "project_assign_id=".$project_assign_id. " and inquiry_projecttrn_status = 3", $dbcon);

    $project_qry = "select * from `tbl_project_assigntrn` where `project_assign_id` = ".$project_assign_id." AND `project_assigntrn_status` = 0 and company_id='".$_SESSION['company_id']."' ";
    $proj_result=$dbcon->query($project_qry);
    if(brp_mysqli_num_rows($proj_result)>0)
    {
        while($rel=brp_mysqli_fetch_assoc($proj_result))
        {
            $info['inquiry_type'] = $inquiry_type; 
            $info['inquiry_id'] = $inquiry_id; 
            $info['project_assign_id'] = $project_assign_id;
            $info['product_category_id'] = $rel['product_category_id'];
            $info['product_id'] = $rel['product_id'];
            $info['description'] = $rel['description'];
            $info['product_hsn_code'] = $rel['product_hsn_code'];

            $info['product_qty'] = $rel['product_qty'];
            $info['product_rate'] = $rel['product_rate'];
            $info['product_amount']    = $rel['product_amount'];
            $info['formulaid']         = $rel['formulaid'];

            $info1=get_product_common_tax($dbcon,$rel['product_amount'],$rel['formulaid']);
            $info=array_merge($info,$info1);

            $info['user_id'] = $_SESSION['user_id'];
            $info['company_id'] = $_SESSION['company_id'];
            $info['product_disc'] = $rel['product_disc'];
            $info['product_spec'] = $rel['product_spec'];

            if($inquiry_id){
                $info['inquiry_projecttrn_status'] = 0;
            }else{
                $info['inquiry_projecttrn_status'] = 3;
            }
            add_record('tbl_inquiry_project_trn', $info, $dbcon, $branch_id);
        }
    }    
    //}
}
else if(brp_strtolower($POST['mode']) == "load_tempoutward") {

    if(empty($POST['eid'])){
        $query="select inquiry_projecttrn_id,product.product_name,mst.description,product_qty,product_rate,mst.* from tbl_inquiry_project_trn as mst 
        left join product_mst as product on product.product_id=mst.product_id  
        where inquiry_projecttrn_status in (3,4) and project_assign_id=".$POST['project_assign_id']." and mst.user_id=".$_SESSION['user_id'];
    }else{
        $query="select inquiry_projecttrn_id,product.product_name,mst.description,product_qty,product_rate,mst.* from tbl_inquiry_project_trn as mst 
        left join product_mst as product on product.product_id=mst.product_id  
        where inquiry_projecttrn_status=0 and inquiry_id=".$POST['eid']." and project_assign_id=".$POST['project_assign_id'];
    }

    $result=$dbcon->query($query);
    $companySettings = getCompanySettings($dbcon);
    $project_wise_item_rate = '';
    if($companySettings) {
        $project_wise_item_rate = $companySettings['project_wise_item_rate'];
    }
    echo ' <div class="form-group">
    <div class="col-md-12 col-xs-12"  style="overflow-y: scroll;height: 350px;">
    <input type="text" class="form-control" id="projectProductTrn" placeholder="Search Product Only.." title="Product Only"><br>     
    <table id="project-product-table" cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
    <tr id="field">
    <th class="text-center"width="25%">Product Name</th>
    <th class="text-center"width="8%">HSN Code</th>
    <th class="text-center"width="8%">Qty</th>';
    if($project_wise_item_rate=='Yes'){ 
        echo  '<th class="text-center"width="10%">Rate</th>';
        echo  '<th class="text-center"width="10%">Taxable Value</th>';
        echo  '<th class="text-center"width="10%">Tax</th>';
        echo  '<th class="text-center"width="10%">Total Amount</th>';
    }
    echo '<th class="text-center"width="10%">Action</th>
    </tr>';
    if(brp_mysqli_num_rows($result)>0)
    {
        $i=1;
        while($rel=brp_mysqli_fetch_assoc($result))
        {
            echo '<tr id="fieldtr'.$id.'" >
            <td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
            '.$rel['product_name'].'
            '.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
            </td>

            <td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
            if(empty($rel['product_hsn_code'])){
                echo '-';
            }else{
                echo $rel['product_hsn_code'];
            }
            echo'</td>
            <td data-label="QTY" style="vertical-align:top;" class="text-center">
            '.$rel['product_qty'].'
            </td>';

            if($project_wise_item_rate=='Yes'){ 
                echo '<td  data-label="RATE" style="vertical-align:top;" class="text-center">
                '.$rel['product_rate'].'
                </td>' ;              

                echo'<td  data-label="TAXABLE AMOUNT" style="vertical-align:top;" class="text-center">
                '.$rel['product_amount'].'
                </td>
                <td  data-label="TAX" style="vertical-align:top;" class="text-center">';
                if(empty($rel['formulaid'])){
                    echo '-';
                }else{
                    echo (empty($rel['tax_name1']) ? " " : $rel['tax_name1'] .' : '. $rel['tax_amount1']).'<br/>';
                    echo (empty($rel['tax_name2']) ? " " : $rel['tax_name2'] .' : '. $rel['tax_amount2']).'<br/>';
                    echo (empty($rel['tax_name3']) ? " " : $rel['tax_name3'] .' : '. $rel['tax_amount3']).'<br/>';
                }
                echo '</td>
                <td  data-label="TOTAL AMOUNT" style="vertical-align:top;" class="text-center">
                '.$rel['product_total'].'
                </td>'; 
            }  
            echo '<td data-label="ACTION" style="vertical-align:top">
            <button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_project_data('.$rel['inquiry_projecttrn_id'].');" ><i class="fa fa-pencil"></i></button>
            <button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_project_data('.$rel['inquiry_projecttrn_id'].');" id="fieldremove'.$i.'">X</button>
            </td>   
            </tr>';
            $i++;
        }
    }
    else{
        echo '<tr><td colspan="8" class="text-center">NO DATA FOUND</td></tr>';
    }
    echo '</table></div></div>';
}
else if(brp_strtolower($POST['mode']) == "add_project_field") {

    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

    $info1['inquiry_id']        = $POST['inquiry_id'];
    $info1['inquiry_type']        = $POST['inquiry_type'];
    $info1['product_id']        = $POST['product_id'];
    $info1['inquiry_trn_id']= $POST['inquiry_trn_id'];
    $info1['project_assign_id']= $POST['project_assign_id'];
    $info1['description']       = stripslashes($POST['product_des']);
    $info1['product_disc']      = stripslashes($POST['product_des']);
    $info1['product_spec']      = stripslashes($POST['product_spec']);
    $info1['product_hsn_code']  = $POST['product_hsn_code'];
    $info1['product_qty']       = $POST['product_qty'];
    $info1['product_rate']      = $POST['product_rate'];
    $info1['product_amount']    = $POST['product_qty']*$POST['product_rate'];
    $info1['formulaid']         = $POST['formulaid'];

    $info1['user_id']   = $_SESSION['user_id'];
    $info1['company_id']        = $_SESSION['company_id'];

    $info=get_product_common_tax($dbcon,$info1['product_amount'],$POST['formulaid']);
    $info1=array_merge($info1,$info);

    if($POST['inquiry_id']!=''){
        $info1['inquiry_projecttrn_status']= 0;
    }
    elseif($info1['inquiry_trn_id']!='' && $POST['edit_id']==''){
        $info1['inquiry_projecttrn_status']= 4;
    }else{
        $info1['inquiry_projecttrn_status']= 3;
    }

    $table='tbl_inquiry_project_trn';$tableid='inquiry_projecttrn_id';

    if(empty($POST['edit_id']))
    {
        $inserid=add_record($table, $info1, $dbcon,$branch_id);
    }
    else
    {
        $updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id); 
    }
} 
else if(brp_strtolower($POST['mode'])== "load_productdata"){
    $pro_qry="select * from product_mst where product_id=".$POST['eid'];
    $pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));

    $qry1="select c_add_state as lst,com.stateid as cst from tbl_customer as led 
    left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
    left join tbl_company as com on com.company_id=led.company_id
    where led.cust_id =".$POST['cust_id'];
    $result1=$dbcon->query($qry1);
    $row1=mysqli_fetch_assoc($result1);

    if($row1['lst']==$row1['cst']){
        $qry2="select * from formula_mst as led 
        where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$pro_rel['product_sale_gst'];
        $result2=$dbcon->query($qry2);
        $row2=mysqli_fetch_assoc($result2);
        $pro_rel['formula_id']=$row2['formulaid'];
    }else{
        $qry2="select * from formula_mst as led 
        where formula_status=0 and tax_cat='INTER' and tax_per_id=".$pro_rel['product_sale_gst'];
        $result2=$dbcon->query($qry2);
        $row2=mysqli_fetch_assoc($result2);
        $pro_rel['formula_id']=$row2['formulaid'];
    }
    echo json_encode($pro_rel);

}
else if(brp_strtolower($POST['mode'])== "edit_project_data"){
    $q = $dbcon -> query("select mst.*,pro.product_name from tbl_inquiry_project_trn as mst left join tbl_product as pro on mst.product_id=pro.product_id where inquiry_projecttrn_id = '$POST[id]'");
    $r = $q->fetch_assoc();

    echo brp_json_encode($r);
}
else if(brp_strtolower($POST['mode'])== "delete_project_data"){
    $row=array();
    $info['inquiry_projecttrn_status']=2;    
    $updateid=update_record("tbl_inquiry_project_trn", $info,"inquiry_projecttrn_id=".$POST['eid'] , $dbcon);
    if($updateid)
        $row['res']="1";
    else
        $row['res']="0";
    echo brp_json_encode($row);
}   
else if(strtolower($POST['mode'])== "get_project_amount")
{
    $arr=get_product_common_tax($dbcon,$POST['product_amount'],$POST['formulaid']);
    echo json_encode($arr);
}    
else if(strtolower($POST['mode'])== "get_cust_territory"){
    $row=array();
    $query="select * from tbl_customer  where cust_id=".$_POST['cust_id'];
    $rs_cust=$dbcon->query($query);
    $getQry=mysqli_fetch_assoc($rs_cust);
    $row['t_id']=$getQry['t_id'];
    echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "load_product_history") {
    $row=get_product_history($dbcon, $_POST['cust_id'], $_POST['product_id'], 2);
    echo $row;
}
/* END */
else if(strtolower($POST['mode']) == "product_load") {
			
	$inquiry_type = $POST['inquiry_type'];
    	if($inquiry_type!='1'){
			 $query = "SELECT product_id,product_name FROM product_mst WHERE product_status = 0 AND product_type = '-1'";
		}else{
			//$query="SELECT product_id,product_name FROM product_mst";
			$query="select pro.product_id, pro.product_name, pro.product_icode, dr.drawing_number from product_mst as pro
					left join tbl_drawing as dr on dr.drawing_id = pro.drawing_id
					where product_status=0 ".$whr." order by product_name";
		} 
			
			$result=$dbcon->query($query);
			$i=0;
			while($row=mysqli_fetch_array($result)){
				$row1[0][]=$row['product_id'];
				$row1[1][]=$row['product_name'];
			}
			//$row=mysqli_fetch_array($result);		
			
			echo json_encode($row1); 
		}



/* Inquiry Related Functions */
function load_inquiry_no($dbcon){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=2 and company_id=".$_SESSION['company_id'];
	$rows=mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
	if($rows['invoice_format']=='2'){
		$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
	}
	return $row['invoiceno'];
}
function upload_attch_file($FILES){
    $rand=rand(0,99999999);
    if(!empty($FILES['inq_attch_file']['tmp_name'])) {
        $temp = explode(".", $FILES["inq_attch_file"]["name"]);
        $extension = strtolower(end($temp));
        $File = "inq_attch_".$rand.".".$extension;
        $tmp_name = $FILES["inq_attch_file"]["tmp_name"];
        move_uploaded_file($tmp_name,'..//'.INQ_ATTACH_UPING.$File);

        return  $File;				
    }
}
?>
