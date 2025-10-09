<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
$incPath = $path . 'include/';
include_once($incPath . "common_send_email.php");
// Amish Soni End 30-12-2020

$POST = ($_POST != NULL) ? bulk_filter($dbcon, $_POST) : bulk_filter($dbcon, $_GET);

if (strtolower($POST['mode']) == "fetch") {
    //$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
    //$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);

    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
        TASK_SLUG_EDIT,
        TASK_SLUG_DELETE
    ]);

    $getspecialConfiguration = getspecialConfiguration($dbcon);

    $s_date = explode(' - ', $POST['date']);
    $_SESSION['start'] = $s_date[0];
    $_SESSION['end'] = $s_date[1];
    // $branch_id = $POST['branch_id'];
    $where = '';
    // if ($branch_id) {
    //     $where .= check_branch('task', $branch_id);
    // }
    
    $where .= "  and task.task_due_date between '" . date('Y-m-d', strtotime($s_date[0])) . "' 
    AND '" . date('Y-m-d', strtotime("+1 day", strtotime($s_date[1]))) . "'";
    if ($POST['task_status'] != 2) {
        $where .= ' and task.task_status=' . $POST['task_status'];
    }

    //Amish Soni Start 01-02-2021
    $is_general = (isset($POST['is_general']) && $POST['is_general']) ? $POST['is_general'] : false;
    if ($is_general) {
        $where .= " and task.task_type_id = '" . GENERAL_TASK_TYPE . "' ";

        //Amish Soni Start 18-03-2021
        //Amish Soni End 18-03-2021
        if ($_SESSION['user_type'] != '2') {
            $where.=" and FIND_IN_SET(".$_SESSION['user_id'].",task.show_user_ids)";
        }
    } else {
        $where .= " and task.task_type_id != '" . GENERAL_TASK_TYPE . "' ";
        $fis=check_crm_find_in_set($dbcon,$_SESSION['user_id'],1);
        $where.=' and task.assign_user_ids in ('.$fis.')';
        if ($_SESSION['user_type'] != '2') {
            $where .= "  and (task.user_id in ($_SESSION[user_id])) ";
        }
    }

    if(!empty($POST['assign_user'])){
        $where .= ' and task.assign_user_ids='.$POST['assign_user'];
    }

    if(!empty($POST['owner_user'])){
        $where .= ' and task.user_id='.$POST['owner_user'];
    }

    //Amish Soni End 01-02-2021

    $i = 1;
    if ($getspecialConfiguration["umaboy_permission"] == 1) {
        // Remove count read/unread tot_flip from queries for UMABOY
        $aColumns = array('task.task_type_id','task.task_remark', 'inq.inquiry_no','task.task_id', 'task.user_id', 'task.task_due_date', 'type.mcd_name',  'cust_inq.cust_name as company_name','gtm.general_task_name', 'regrd.task_rel_name', 'task.task_name', 'task.company_id', 'usr.user_name', 'prior.task_priority_name', 'task.task_status', 'per.c_con_fname', 'per.c_con_lname', 'cust.cust_name', 'task.task_rel_id', 'task.assign_user_ids', 'task.cdate', 'task.task_completion_date','task.inquiry_id','task.is_delete');
    } else {
        $aColumns = array('task.task_type_id', 'inq.inquiry_no','task.task_id', 'task.user_id', 'task.task_due_date', 'type.mcd_name',  'cust_inq.cust_name as company_name','gtm.general_task_name', 'regrd.task_rel_name', 'task.task_name', 'task.company_id', 'usr.user_name', 'prior.task_priority_name', 'task.task_status', 'per.c_con_fname', 'per.c_con_lname', 'cust.cust_name', 'task.task_rel_id', 'task.assign_user_ids', 'task.cdate', 'task.task_completion_date','task.inquiry_id','task.is_delete','(select count(flp.user_id_read) from tbl_followup as flp where flp.flp_status=0 and user_id_read=1 and flp.user_id !="'.$_SESSION['user_id'].'" and flp.task_id=task.task_id) as read_owner_flp','(select count(flp.user_id_read) from tbl_followup as flp where flp.flp_status=0 and user_id_read=0 and flp.user_id !="'.$_SESSION['user_id'].'" and flp.task_id=task.task_id) as unread_owner_flp','(select count(flp.assign_user_ids_read) from tbl_followup as flp where flp.flp_status=0 and assign_user_ids_read=1 and flp.user_id !="'.$_SESSION['user_id'].'" and flp.task_id=task.task_id) as read_assign_flp','(select count(flp.assign_user_ids_read) from tbl_followup as flp where flp.flp_status=0 and assign_user_ids_read=0 and flp.user_id !="'.$_SESSION['user_id'].'" and flp.task_id=task.task_id) as unread_assign_flp','(select count(flp.flp_id) from tbl_followup as flp where flp.flp_status=0 and flp.task_id=task.task_id and flp.user_id != "'.$_SESSION['user_id'].'") as tot_flp');
    }    

    $sIndexColumn = "task.task_id";
    $isWhere = array("task.task_status != 2 and task.entry_type=1 and task.company_id in (0,$_SESSION[company_id])".$where);
    $sTable = "tbl_task as task";
    $isJOIN = array('left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
        'left join task_rel_mst as regrd on regrd.task_rel_id=task.task_rel_id',
        'left join users as usr on usr.user_id=task.user_id',
        'left join task_priority_mst as prior on prior.task_priority_id = task.task_priority_id',
        'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id',
        'left join tbl_customer as cust on cust.cust_id=task.cust_id',
        'left join tbl_inquiry as inq on inq.inquiry_id=task.inquiry_id',
        'left join general_task_mst as gtm on gtm.gt_id=task.gt_id',
        'left join tbl_customer as cust_inq on cust_inq.cust_id = inq.cust_id');
    if ($POST['task_status'] != 2) {
        $hOrder = "task.task_id desc";
    } else {
        $hOrder = "task.task_status asc, task.task_id desc";
    }
    $having_clause="";
    include('../../../include/pagging.php');
    $appData = array();
    $id = 1;
    foreach ($sqlReturn as $row) {
        $row_data = array();
        $editHref= ($is_general) ? 'general_task_edit/' : 'task_edit/';
        $editLink = ROOT.CRM_ROOT.$editHref.$row['task_id'];
        if (in_array(TASK_SLUG_EDIT, $bulkAccessArray) && $row['task_status'] != '1' && $row['task_type_id'] != GENERAL_TASK_TYPE) {

            if ($row['task_rel_id'] == '5') {//Inquiry
                $inq_name = $row['company_name'];
            } else if ($row['task_rel_id'] == '4') {//Company
                $inq_name = $row['cust_name'];
            } else if ($row['task_rel_id'] == '3') {//Person
                $inq_name = $row['c_con_fname'] . ' ' . $row['c_con_lname'];
            } else {
                $inq_name = $row['general_task_name'];
            }

            $row_data[] ='<a class="" data-original-title="Edit '.$inq_name.'" data-toggle="tooltip" data-placement="top" href="'.$editLink.'">'.$row["mcd_name"].'</a>';
            $row_data[] = '<a class="" data-original-title="Edit '.$inq_name.'" data-toggle="tooltip" data-placement="top" href="'.$editLink.'">'.$row["task_rel_name"].' - '.$row["inquiry_no"].'</a>';
            $row_data[] = '<a class="" data-original-title="Edit '.$inq_name.'" data-toggle="tooltip" data-placement="top" href="'.$editLink.'">'.$inq_name.'</a>';

            // Remove Inquiry name for Umaboy
            if ($getspecialConfiguration["umaboy_permission"] != 1) {
                $row_data[] = '<a class="" data-original-title="Edit '.$inq_name.'" data-toggle="tooltip" data-placement="top" href="'.$editLink.'">'.$row['task_name'].'</a>';
            }
        } else {
            $row_data[] = $row['mcd_name'];
            $row_data[] = $row['task_rel_name'].' - '.$row['inquiry_no'];
            

            if ($row['task_rel_id'] == '5') {//Inquiry
                $row_data[] = $inq_name = $row['company_name'];
            } else if ($row['task_rel_id'] == '4') {//Company
                $row_data[] = $inq_name = $row['cust_name'];
            } else if ($row['task_rel_id'] == '3') {//Person
                $row_data[] = $inq_name = $row['c_con_fname'] . ' ' . $row['c_con_lname'];
            } else {
                $row_data[] = $inq_name = $row['general_task_name'];
            }

            // Remove Inquiry name for Umaboy
            if ($getspecialConfiguration["umaboy_permission"] != 1) {
                $row_data[] = $row['task_name'];
            }
        }
        $row_data[] = '<span style="white-space:nowrap;">' . date("d-M-Y h:i A", strtotime($row['task_due_date'])) . ' </span>';
        $row_data[] = $row['user_name'];
        $row_data[] = getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']);

        // Remove Task priority name for Umaboy
        if ($getspecialConfiguration["umaboy_permission"] == 1) {
            $row_data[] = $row['task_remark'];
        } else {
            $row_data[] = $row['task_priority_name'];
        }

        if ($row['task_status'] == '1') {
            $tsk_due_time = strtotime($row['task_due_date']);
            $cur_time = strtotime($row['task_completion_date']);
            $tsk_type = '';
            if ($tsk_due_time < $cur_time) {
                $tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
            }
            $row_data[] = '<button type="button" class="btn btn-sm btn-success" data-original-title="Task Completed" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i> Completed ' . $tsk_type . '</button>';
        } else {
            $tsk_due_time = strtotime($row['task_due_date']);
            $cur_time = strtotime(date('Y-m-d H:i:s'));
            $tsk_type = '';
            if ($tsk_due_time < $cur_time) {
                $tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
            }
            $row_data[] = '<button type="button" class="btn btn-sm btn-warning" data-original-title="Task Pending" data-toggle="tooltip" data-placement="top">Pending ' . $tsk_type . '</button>';
        }

        $edit = '';
        $delete = '';
        $task_btn = '';
        $add_flp_btn = '';$doc_btn = '';$read_botton='';
        if ($_SESSION['user_id'] == $row['user_id'] || $_SESSION['user_type'] == 2) {
            if ($row['task_status'] == '0' || $row['is_delete'] == '0') {
                if (in_array(TASK_SLUG_EDIT, $bulkAccessArray)) {
                    $edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="' . $editLink. '"><i class="fa fa-pencil"></i></a>';
                }
                if (in_array(TASK_SLUG_DELETE, $bulkAccessArray)) {
                    $delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task('.$row['task_id'].','.$row['inquiry_id'].')"><i class="fa fa-trash-o"></i></button>';
                }
            }
        }

        //Amish Soni Start 17-03-2021
        if ($row['task_type_id'] == GENERAL_TASK_TYPE) {
            if ($_SESSION['user_id'] == $row['user_id']) {
                if ($row['task_status'] == '0') {
                    $task_btn = '<button class="btn btn-xs btn-success" data-original-title="Click to Complete Task" data-toggle="tooltip" data-placement="top" onClick="change_task_status(' . $row['task_id'] . ',1)"><i class="fa fa-check"></i></button>';
                } else if ($row['task_status'] == '1') {
                    $task_btn = '<button class="btn btn-xs btn-warning" data-original-title="Click to Open Task" data-toggle="tooltip" data-placement="top" onClick="change_task_status(' . $row['task_id'] . ',0)"><i class="fa fa-ban"></i></button>';
                }
            }

            $add_flp_btn = '<button class="btn btn-xs btn-primary" data-original-title="Click to Add Follow Up" data-toggle="tooltip" data-placement="top" onClick="open_follow_up(' . $row['task_id'] . ',\'' . $inq_name . '\',\'' . $row['task_status'] . '\')"><i class="fa fa-eye"></i></button>';
            $doc_btn = '<button class="btn btn-xs btn-primary" data-original-title="Show Documents" data-toggle="tooltip" data-placement="top" onClick="open_doc_modal(\'' . $row['task_id'] . '\',\'' . $inq_name . '\',\'' . $row['task_status'] . '\')"><i class="fa fa-files-o"></i></button>';
        }
        //Amish Soni End 17-03-2021

        /*if($row['tot_flp']>0){
            if($row['tot_flp']==$row['read']){

            }else{

            }
        }*/
        if ($getspecialConfiguration["umaboy_permission"] != 1) {
            if($row['tot_flp']>0){
                if($_SESSION['user_id']==$row['user_id']){
                    if($row['tot_flp']==$row['read_owner_flp']){
                        $read_botton = '<button class="btn btn-sm btn-info"><i class="fa fa-eye" aria-hidden="true"></i> Read</button>';
                    }else{
                        $read_botton = '<button class="btn btn-sm btn-warning"><i class="fa fa-eye-slash" aria-hidden="true"></i> ('.$row['unread_owner_flp'].') Unread</button>';
                    }
                }else{
                    if($row['tot_flp']==$row['read_assign_flp']){
                        $read_botton = '<button class="btn btn-sm btn-info"><i class="fa fa-eye" aria-hidden="true"></i> Read</button>';
                    }else{
                        $read_botton = '<button class="btn btn-sm btn-warning"><i class="fa fa-eye-slash" aria-hidden="true"></i> ('.$row['unread_assign_flp'].') Unread</button>';
                    }
                }
            }
        }
        
        $row_data[] = $edit . ' ' . $delete . ' ' . $task_btn . ' ' . $add_flp_btn.' '.$doc_btn.' '.$read_botton;

        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode($output);
} else if (strtolower($POST['mode']) == "add") {

    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    $reason = array();
    if ($POST['opp_id'] == '13' && !empty($POST['reason_id']) && !empty($POST['lost_reason'])) {
        $reason = array_combine($POST['reason_id'], $POST['lost_reason']);
        $info['lost_reason'] = json_encode($reason);
    }
    if ($POST['task_type_id'] == GENERAL_TASK_TYPE) {
        $show_user_ids = $POST['assign_user_ids'].','.$_SESSION['user_id'];
    } else{
        $show_user_ids = show_user_ids($dbcon, $POST['assign_user_ids']);
    }

    if($POST['opp_id'] == WON){
       $info['stage_prob'] =100;
    }else{
        $info['stage_prob'] = $POST['stage_prob'];
    }
    
    $prev_task_id = get_previous_taskid($dbcon,$POST['inquiry_id']);
    if($POST['task_type_id']=='21' || $POST['task_type_id']=='20'){
        $info['quotation_id'] = $prev_task_id['quotation_id'];
    }


    $info['show_user_ids'] = $show_user_ids;
    $info['task_type_id'] = $POST['task_type_id'];

    //$info['stage_prob'] = $POST['stage_prob'];
    $info['opp_id'] = $POST['opp_id'];
    $info['sales_stage_id'] = $POST['sales_stage_id'];
    $info['task_rel_id'] = $POST['task_rel_id'];
    $info['task_name'] = $POST['task_name'];
    $info['c_con_id'] = $POST['c_con_id'];
    $info['cust_id'] = $POST['cust_id'];
    $info['inquiry_id'] = $POST['inquiry_id'];
    $info['task_remark'] = $POST['task_remark'];
    $info['assign_user_ids'] = $POST['assign_user_ids'];
    $info['task_priority_id'] = $POST['task_priority_id'];
    $info['create_date'] = date('Y-m-d H:i:s');
    $info['cdate'] = date("Y-m-d H:i:s");
    $info['task_due_date'] = date('Y-m-d H:i:s', strtotime($POST['task_due_date']));
    $info['task_alert_id'] = $POST['task_alert_id'];
    $info['gt_id'] = $POST['gt_id'];
    $info['perent_id']      = $prev_task_id['prev_taskid'];

    // if(isset($POST['objection_flag'])){
    //     $info['objection_flag'] = $POST['objection_flag'];
    // }

    if ($POST['task_alert_id'] && $POST['task_alert_id'] != '1') {//If alert is not none
        $alert_date = date("Y-m-d H:i:s", strtotime($POST['task_due_date']));
        $gap_mints = get_alert_mintes($dbcon, $POST['task_alert_id']);
        $filt_alert_date = date("Y-m-d H:i:s", strtotime($alert_date . "-" . $gap_mints . " minutes"));//Subtract Minutes
        $info['alert_date_time'] = date('Y-m-d H:i:s', strtotime($filt_alert_date));
    }

    if(isset($POST['task_in_out'])){
        $info['task_in_out'] = $POST['task_in_out'];
    }
    
    $info['entry_type'] = 1;//Fixed Task Type
    $info['user_id'] = $_SESSION['user_id'];
    $info['company_id'] = $_SESSION['company_id'];

    //Auto Complete Prev Flp Task Before Add
    if($POST['inquiry_id']) {
        $upd_qry = "update tbl_task set task_status=1,is_delete=1,task_completion_date='" . date("Y-m-d H:i:s") . "' where task_status=0 and inquiry_id=" . $POST['inquiry_id'];
        $upd_qry_rs = $dbcon->query($upd_qry);
    }

    // Amish Soni Start 19-01-2021
    $crm_auto_mail = '';
    $companySettings = getCompanySettings($dbcon);
    if ($companySettings) {
        $crm_auto_mail = $companySettings['crm_auto_mail'];
    }
    $showTemplate = ($crm_auto_mail == 'No');

    if ($showTemplate) {
        $info['email_template_id'] = (isset($POST['email_template_id']) && $POST['email_template_id'])
        ? $POST['email_template_id'] : null;
    }
    // Amish Soni End 19-01-2021

    $ins_task_id = add_record('tbl_task', $info, $dbcon, $branch_id);

    if ($POST['inquiry_id']) {
        //Auto Complete All Task Before Add
        if ($POST['opp_id'] == WON && $info['stage_prob'] == '100') {
            $infoinq['won_user_id'] = $_SESSION['user_id'];//Won User id insert for order confirm list
            $infoinq['won_date'] = date("Y-m-d H:i:s");

            $upd_qry = "update tbl_task set task_status=1,task_completion_date='" . date("Y-m-d H:i:s") . "' where task_status=0 and inquiry_id=" . $POST['inquiry_id'];
            $upd_qry_rs = $dbcon->query($upd_qry);

            $qtupd_qry = "update tbl_quotation set quot_won_user_id=" . $_SESSION['user_id'] . " where inquiry_id=" . $POST['inquiry_id'];
            $qtupd_qry_rs = $dbcon->query($qtupd_qry);
        }

        if ($POST['opp_id'] == WON && !check_has_quotation($dbcon, $POST['inquiry_id'])) {
            $inq_qry = "select * from tbl_inquiry where inquiry_id =" . $POST['inquiry_id'];
            $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
            auto_create_quotation($dbcon, $inq_data, $POST['inquiry_id']);
        }

        if ($POST['opp_id'] == LOST) {
            $infoinq['lost_by_userid'] = $_SESSION['user_id'];
            $infoinq['closed_reason'] = json_encode($reason);
            update_record('tbl_task', array('task_status' => '1', 'task_completion_date' => date("Y-m-d H:i:s")), "inquiry_id=" . $POST['inquiry_id'], $dbcon, $branch_id);
        }
        //Edit Stage In Inquiry
        //$show_user_ids			=show_user_ids($dbcon,$POST['assign_user_ids']);
        //$infoinq['show_user_ids']	= $show_user_ids;
        $infoinq['opp_id'] = $POST['opp_id'];
        $infoinq['sales_stage_id'] = $POST['sales_stage_id'];
        $infoinq['stage_prob'] = $info['stage_prob'];
        $infoinq['closing_date'] = date("Y-m-d");
        $infoinq['objection_flag'] = $POST['objection_flag'];

        $updateinqid = update_record('tbl_inquiry', $infoinq, "inquiry_id=" . $POST['inquiry_id'], $dbcon, $branch_id);
    }

    if ($ins_task_id) {


        /*
        * Jayesh : Send whatsapp message on followup
        */
        
        $getspecialConfiguration = getspecialConfiguration($dbcon);
        $company_configuration = getCompanyConfiguration($dbcon);
        if ($getspecialConfiguration["umaboy_permission"] == 1 && $company_configuration["enable_whatsapp"] == 1) {

            $get_cust_mobile_qry = "select task.task_id, task.cust_id, cust.cust_name, cust.cust_mobile, task.assign_user_ids, task.inquiry_id, task.user_id, stage.opp_stage, stage.opp_probability FROM tbl_task as task left join users as usr on usr.user_id=task.user_id left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,opp_id from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id left join tbl_customer as cust on cust.cust_id=inq.c_id left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_addr_defult=1 left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id where task.task_id=".$ins_task_id." Group by task.task_id";

            $task_cust_rel = mysqli_fetch_assoc($dbcon->query($get_cust_mobile_qry));
            if ($task_cust_rel['cust_mobile']) {
                $mobile_no = $task_cust_rel['cust_mobile'];
                
                // Send Message on Whatsapp base on stage template
                $opp_query = "SELECT `opp_id`,`opp_stage`,`opp_status`,`opp_template`,`opp_file`,`enable_whatsapp` FROM `tbl_opportunity_mst` WHERE `enable_whatsapp`=1 and `opp_id` = '".$POST['opp_id']."' and `company_id`='".$_SESSION['company_id']."'";
                $opp_data = brp_mysqli_fetch_assoc($dbcon->query($opp_query));

                if ($opp_data && $opp_data['opp_template'] && $opp_data['enable_whatsapp']==1) {
                    $attachment_path = "";
                    $quot_header_msg = "";
                    if ($opp_data['opp_file']) {
                        $attachment_path  = DOMAIN.'upload/stage_template_attach_doc/'.$opp_data['opp_file'];
                    }
                    send_whatsapp_message($dbcon,$mobile_no,$attachment_path,$opp_data['opp_template']);
                }
            }
        }

        $arr['msg'] = "1";
        //Insert LOG
        $log_entry = common_log_entry($dbcon, "task_add", 1, "tbl_task", $ins_task_id);

        $infofolloup['task_id'] = $ins_task_id;
        $infofolloup['task_flp_remark'] = $_POST['task_remark'];
        $infofolloup['flp_date'] = date("Y-m-d H:i:s");
        $infofolloup['user_id'] = $_SESSION['user_id'];
        $infofolloup['cdate'] = date("Y-m-d H:i:s");
        $infofolloup['company_id'] = $_SESSION['company_id'];

        $inserid = add_record('tbl_followup', $infofolloup, $dbcon, $branch_id);

        $upd_qry_tk = "UPDATE tbl_task_attachment SET attch_status = 0, task_id = '".$ins_task_id."' WHERE attch_status=3 AND task_id = 0 AND user_id = '" . $_SESSION['user_id']."'";
        $upd_qry_tks = $dbcon->query($upd_qry_tk);

        // Amish Soni Start 30-12-2020
        //Added Email functionality only for Inquiry (other's are remaining)
        if ($POST['inquiry_id']) {
            $inq_qry = "SELECT * FROM tbl_inquiry WHERE inquiry_id =" . $POST['inquiry_id'];
            $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));

            $module_id = 2; //CRM Module
            // Amish Soni Start 19-01-2021
            if ($showTemplate) {
                if (isset($POST['email_template_id']) && $POST['email_template_id']) {
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
            $from_email_id = ($cur_user && $cur_user['user_mail']) ? $cur_user['user_mail'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';

            if ($mail_template && $to_email_id) {
                // Amish Soni Start 18-01-2021

                $querybcc="select email_cc,email_bcc from email_sms_template where email_sms_id=".$POST['email_template_id'];
                $resultbdd=$dbcon->query($querybcc);
                $rel1=brp_mysqli_fetch_assoc($resultbdd);

                if(!empty($rel1['email_cc'])){
                    $umix=explode(",",$rel1['email_cc']);
                    $umix=array_push($umix,$cur_user_id);
                    $uid=implode(",");
                }else{
                    //var_dump($uid);
                    $uid=$cur_user_id;
                }

                $querybcc1="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (".$uid.")";



                $resultbdd1=$dbcon->query($querybcc1);
                $rel11=brp_mysqli_fetch_assoc($resultbdd1);

                $querybcc2="select GROUP_CONCAT(common_email_id SEPARATOR ";") as email_bcc from users where user_id in (".$rel1['email_bcc'].")";
                $resultbdd2=$dbcon->query($querybcc2);
                $rel12=brp_mysqli_fetch_assoc($resultbdd2);

                $subject = $mail_template['email_subject'];
                $content = $mail_template['email_content'];

                $subject = replaceMergeFields($dbcon, $subject, $cust_id, $module_id);
                $content = replaceMergeFields($dbcon, $content, $cust_id, $module_id);
                // Amish Soni End 18-01-2021
                final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content, '');
            }
        }
        // Amish Soni End 30-12-2020
    } else {
        $arr['msg'] = "0";
    }
    echo json_encode($arr);
} else if (strtolower($POST['mode']) == "edit") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    $reason = array();
    if ($POST['opp_id'] == '13' && !empty($POST['reason_id']) && !empty($POST['lost_reason'])) {
        $reason = array_combine($POST['reason_id'], $POST['lost_reason']);
        $info['lost_reason'] = json_encode($reason);
    }
    if ($POST['task_type_id'] == GENERAL_TASK_TYPE) {
        $show_user_ids = $POST['assign_user_ids'].','.$_SESSION['user_id'];
    } else{
        $show_user_ids = show_user_ids($dbcon, $POST['assign_user_ids']);
    }
    $info['show_user_ids'] = $show_user_ids;
    $info['stage_prob'] = $POST['stage_prob'];
    $info['opp_id'] = $POST['opp_id'];
    $info['sales_stage_id'] = $POST['sales_stage_id'];
    $info['task_type_id'] = $POST['task_type_id'];
    $info['task_rel_id'] = $POST['task_rel_id'];
    $info['task_name'] = $POST['task_name'];
    $info['task_remark'] = $POST['task_remark'];
    $info['assign_user_ids'] = $POST['assign_user_ids'];
    $info['task_priority_id'] = $POST['task_priority_id'];
    $info['cdate'] = date("Y-m-d H:i:s");
    $info['task_due_date'] = date('Y-m-d H:i:s', strtotime($POST['task_due_date']));
    $info['alert_date_time'] = date('Y-m-d H:i:s', strtotime($POST['alert_date_time']));
    $info['gt_id'] = $POST['gt_id'];
    // Amish Soni Start 19-01-2021
    $crm_auto_mail = '';
    $companySettings = getCompanySettings($dbcon);
    if ($companySettings) {
        $crm_auto_mail = $companySettings['crm_auto_mail'];
    }
    if(isset($POST['task_in_out'])){
        $info['task_in_out'] = $POST['task_in_out'];    
    }
    $showTemplate = ($crm_auto_mail == 'No');

    if ($showTemplate) {
        $info['email_template_id'] = (isset($POST['email_template_id']) && $POST['email_template_id'])
        ? $POST['email_template_id'] : null;
    }
    // Amish Soni End 19-01-2021

    //$info['user_id']		= $_SESSION['user_id'];
    //$info['company_id']	= $_SESSION['company_id'];
    $updateid = update_record('tbl_task', $info, "task_id=" . $POST['eid'], $dbcon, $branch_id);

    if ($updateid) {
        //Edit Stage In Inquiry
        if ($POST['inquiry_id']) {
            //Auto Complete All Task Before Add
            if ($POST['stage_prob'] == '100') {
                $infoinq['won_user_id'] = $_SESSION['user_id'];//Won User id insert for order confirm list
                $infoinq['won_date'] = date("Y-m-d H:i:s");

                $upd_qry = "update tbl_task set task_status=1,task_completion_date='" . date("Y-m-d H:i:s") . "' where task_status=0 and inquiry_id=" . $POST['inquiry_id'];
                $upd_qry_rs = $dbcon->query($upd_qry);

                $qtupd_qry = "update tbl_quotation set quot_won_user_id=" . $_SESSION['user_id'] . " where inquiry_id=" . $POST['inquiry_id'];
                $qtupd_qry_rs = $dbcon->query($qtupd_qry);

            }
            if ($POST['opp_id'] == WON && !check_has_quotation($dbcon, $ins_inquiry_id)) {
                auto_create_quotation($dbcon, $POST);
            }

            if ($POST['opp_id'] == LOST) {
                $infoinq['lost_by_userid'] = $_SESSION['user_id'];
                $infoinq['closed_reason'] = json_encode($reason);
                update_record('tbl_task', array('task_status' => '1', 'task_completion_date' => date("Y-m-d H:i:s")), "inquiry_id=" . $POST['inquiry_id'], $dbcon, $branch_id);
            }
            //Edit Stage In Inquiry
            //$show_user_ids			= show_user_ids($dbcon,$POST['assign_user_ids']);
            //$infoinq['show_user_ids']	= $show_user_ids;
            $infoinq['opp_id'] = $POST['opp_id'];
            $infoinq['sales_stage_id'] = $POST['sales_stage_id'];
            $infoinq['stage_prob'] = $POST['stage_prob'];
            $infoinq['closing_date'] = date("Y-m-d");

            $updateinqid = update_record('tbl_inquiry', $infoinq, "inquiry_id=" . $POST['inquiry_id'], $dbcon, $branch_id);
        }
        $arr['msg'] = "update";
        //Insert LOG
        $log_entry = common_log_entry($dbcon, "task_add", 2, "tbl_task", $POST['eid']);

        // Amish Soni Start 30-12-2020
        //Added Email functionality only for Inquiry (other's are remaining)
        if ($POST['inquiry_id']) {
            $inq_qry = "SELECT * FROM tbl_inquiry WHERE inquiry_id =" . $POST['inquiry_id'];
            $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));

            $module_id = 2; //CRM Module
            // Amish Soni Start 19-01-2021
            if ($showTemplate) {
                if (isset($POST['email_template_id']) && $POST['email_template_id']) {
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
            $from_email_id = ($cur_user && $cur_user['user_mail']) ? $cur_user['user_mail'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';

            if ($mail_template && $to_email_id) {
                // Amish Soni Start 18-01-2021
                $subject = $mail_template['email_subject'];
                $content = $mail_template['email_content'];

                $subject = replaceMergeFields($dbcon, $subject, $cust_id, $module_id);
                $content = replaceMergeFields($dbcon, $content, $cust_id, $module_id);
                // Amish Soni End 18-01-2021
                final_send_email($from_email_id, $to_email_id, '', '', $subject, $content,'');
            }
        }
        // Amish Soni End 30-12-2020
    } else {
        $arr['msg'] = 0;
    }
    echo json_encode($arr);
} else if (strtolower($POST['mode']) == "delete") {
    $info['task_status'] = 2;
    $info['cdate'] = date("Y-m-d H:i:s");
    $updateid = update_record('tbl_task', $info, "task_id=" . $POST['task_id'], $dbcon);

    $quotation_delete = task_on_quotation_delete($dbcon, $POST['task_id']);
    $qry=$dbcon->query("SELECT * FROM tbl_task WHERE task_id = '".$POST['task_id']."'");
    $res = mysqli_fetch_assoc($qry);

    $query_inq = "select * from tbl_task where task_id=".$res['parent_id'];
    $result_inq = $dbcon->query($query_inq);
    $row_inq = brp_mysqli_fetch_array($result_inq);

    if($row_inq['parent_id']==0){
        $info1['task_status'] = 1;
        $info1['cdate'] = date("Y-m-d H:i:s");
        $updateids = update_record('tbl_task', $info1, "task_id=" . $res['perent_id'], $dbcon); 
    }else{
        $info1['task_status'] = 0;
        $info1['cdate'] = date("Y-m-d H:i:s");
        $updateids = update_record('tbl_task', $info1, "task_id=" . $res['perent_id'], $dbcon);
    }
    

    if ($updateid)
        echo "1";
    else
        echo "0";
}
else if (strtolower($POST['mode']) == "change_task_status") {
    $info['task_status'] = $POST['task_status'];
    if ($info['task_status'] == '1') {//Update Completion Date if Task Completed
        $info['task_completion_date'] = date("Y-m-d H:i:s");
    } else {
        $info['task_completion_date'] = "0000-00-00 00:00:00";//Reset Date
    }
    $info['cdate'] = date("Y-m-d H:i:s");
    $updateid = update_record('tbl_task', $info, "task_id=" . $POST['task_id'], $dbcon);

    if ($updateid)
        echo "1";
    else
        echo "0";
} else if (strtolower($POST['mode']) == "add_flp_hist_field") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    $info1['task_id'] = $POST['task_id'];
    $info1['task_flp_remark'] = $_POST['task_flp_remark'];
    $info1['flp_date'] = date("Y-m-d H:i:s");
    $info1['user_id'] = $_SESSION['user_id'];
    $info1['cdate'] = date("Y-m-d H:i:s");
    $info1['company_id'] = $_SESSION['company_id'];

    $table = 'tbl_followup';
    $tableid = 'flp_id';

    if (empty($POST['flp_id'])) {
        $inserid = add_record($table, $info1, $dbcon, $branch_id);
    } else {
        $updateid = update_record($table, $info1, $tableid . "=" . $POST['flp_id'], $dbcon, $branch_id);
    }

    $task_query = "select user_id from tbl_task where task_id =".$POST['task_id'];
    $task_res = $dbcon->query($task_query);
    $rel = brp_mysqli_fetch_array($task_res);
    if($_SESSION['user_id']==$rel['user_id']){
        $read_status['user_id_read']   = 1;
    }else{
        $read_status['assign_user_ids_read']   = 1;
    }

    $updateid = update_record('tbl_followup', $read_status, 'task_id' . "=" . $POST['task_id']." and user_id !=".$_SESSION['user_id'], $dbcon, $branch_id);  
} else if (strtolower($POST['mode']) == "show_flp_hist") {
    if ($POST['task_id'] != "") {
        $where = "and flp.task_id =" . $POST['task_id'];
    }
    $appData = array();
    $i = 1;
    $aColumns = array('flp.flp_id', 'flp.task_flp_remark', 'flp.flp_date', 'usr.user_name', 'flp.user_id','flp.user_id_read','flp.assign_user_ids_read','ts.assign_user_ids as asign_user','ts.user_id as owner_user');
    $sIndexColumn = "flp.flp_id";
    $isWhere = array("flp.flp_status = 0 " . $where . " and flp.company_id in (0,$_SESSION[company_id])");
    $sTable = "tbl_followup as flp";
    $isJOIN = array("left join tbl_task as ts on ts.task_id=flp.task_id","left join users as usr on usr.user_id=flp.user_id");
    $hOrder = "flp.flp_id desc";
    //Amish Soni Start 18-03-2021
    include($incPath . 'pagging.php');
    //Amish Soni End 18-03-2021
    $appData = array();
    $id = 1;
    foreach ($sqlReturn as $row) {
        $row_data = array();
        $row_data[] = $row['sr'];
        $row_data[] = $row['task_flp_remark'];
        $row_data[] = date("d-M-Y h:i A", strtotime($row['flp_date']));
        $row_data[] = $row['user_name'];

        $action_btn = '';$read_button='';$read_status='';
        if($_SESSION['user_id'] == $row['user_id']){
            $action_btn = '<!--<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_reqpro(' . $row['flp_id'] . ');"><i class="fa fa-pencil"></i></button>-->
            <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task_flp(' . $row['flp_id'] . ')"><i class="fa fa-trash-o"></i></button>';
        }else{
            if($_SESSION['user_id']==$row['owner_user']){
                $usertype = 'owner_user';          
                if($row['user_id_read']==1){
                    $read_button = '<button type="button" class="btn btn-xs btn-warning" data-original-title="Unread" data-toggle="tooltip" data-placement="top" onClick="followup_read(\'' . $row['flp_id'] . '\',0,\''.$usertype.'\')"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>';

                    $read_status = '<button type="button" class="btn btn-xs btn-info">Read</button>';
                }else{
                    $read_button = '<button type="button" class="btn btn-xs btn-info" data-original-title="Unread" data-toggle="tooltip" data-placement="top" onClick="followup_read(\'' . $row['flp_id'] . '\',1,\''.$usertype.'\')"><i class="fa fa-eye" aria-hidden="true"></i></button>';

                    $read_status = '<button type="button" class="btn btn-xs btn-warning">Unread</button>';
                }
            }else{
                $usertype = 'assign_user';
                if($row['assign_user_ids_read']==1){
                    $read_button = '<button type="button" class="btn btn-xs btn-warning" data-original-title="Unread" data-toggle="tooltip" data-placement="top" onClick="followup_read(\'' . $row['flp_id'] . '\',0,\''.$usertype.'\')"><i class="fa fa-eye-slash" aria-hidden="true"></i></button>';

                    $read_status = '<button type="button" class="btn btn-xs btn-info">Read</button>';
                }else{
                    $read_button = '<button type="button" class="btn btn-xs btn-info" data-original-title="Unread" data-toggle="tooltip" data-placement="top" onClick="followup_read(\'' . $row['flp_id'] . '\',1,\''.$usertype.'\')"><i class="fa fa-eye" aria-hidden="true"></i></button>';

                    $read_status = '<button type="button" class="btn btn-xs btn-warning">Unread</button>';
                }
            }
        }
        
        

        $row_data[] = $action_btn.' '.$read_status.' '.$read_button;
        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode($output);
} else if (strtolower($POST['mode']) == "delete_task_flp") {
    $info['flp_status'] = '2';
    $info['cdate'] = date("Y-m-d H:i:s");
    $updateid = update_record('tbl_followup', $info, "flp_id=" . $POST['flp_id'], $dbcon);

    if ($updateid)
        echo "1";
    else
        echo "0";
} else if (strtolower($POST['mode']) == "preview_rel_types") {
    $str = '';
    $task_rel_id = $POST['task_rel_id'];
    $c_con_id = $POST['c_con_id'];
    $cust_id = $POST['cust_id'];
    $inquiry_id = $POST['inquiry_id'];
    if ($task_rel_id == '3' && $c_con_id) {//Person
        $str .= '<table class="display table table-bordered table-striped">
        <thead>
        <tr>
        <th>Person Name</th>
        <th>Customer Name</th>
        <th>Email</th>
        <th>Mobile</th>
        </tr>
        </thead>
        <tbody>';

        $per_qry = "select per.*,cust.cust_name from tbl_cust_contact as per 
        left join tbl_customer as cust on cust.cust_id = per.cust_id 
        where c_con_id=" . $c_con_id;
        $per_rel = mysqli_fetch_assoc($dbcon->query($per_qry));
        $str .= '<tr>
        <td>' . $per_rel['c_con_fname'] . ' ' . $per_rel['c_con_lname'] . '</td>
        <td>' . $per_rel['cust_name'] . '</td>
        <td>' . $per_rel['c_con_email'] . '</td>
        <td>' . $per_rel['c_con_mobile'] . '</td>
        </tr>';

        $str .= '</tbody>
        </table>';
    } else if ($task_rel_id == '4' && $cust_id) {//Company
        $str .= '<table class="display table table-bordered table-striped">
        <thead>
        <tr>
        <th>Customer Name</th>
        <th>Owner</th>
        </tr>
        </thead>
        <tbody>';

        $cust_qry = "select cust.*,usr.user_name FROM tbl_customer as cust 
        left join users as usr on usr.user_id=cust.user_id
        where cust.cust_id=" . $cust_id;
        $cust_rel = mysqli_fetch_assoc($dbcon->query($cust_qry));
        $str .= '<tr>
        <td>' . $cust_rel['cust_name'] . '</td>
        <td>' . $cust_rel['user_name'] . '</td>
        </tr>';

        $str .= '</tbody>
        </table>';
    } else if ($task_rel_id == '5' && $inquiry_id) {//Inquiry
        $str .= '<table class="display table table-bordered table-striped">
        <thead>
        <tr>
        <th>Inquiry Name</th>
        <th>Customer Name</th>
        <th>Customer Mobile</th>
        </tr>
        </thead>
        <tbody>';

        $inq_qry = "select inq.*,cust.cust_name,cust.cust_mobile FROM tbl_inquiry as inq 
        left join tbl_customer as cust on cust.cust_id=inq.cust_id
        where inq.inquiry_id=" . $inquiry_id;
        $inq_rel = mysqli_fetch_assoc($dbcon->query($inq_qry));
        $str .= '<tr>
        <td>' . $inq_rel['inquiry_no'] . '</td>
        <td>' . $inq_rel['cust_name'] . '</td>
        <td>' . $inq_rel['cust_mobile'] . '</td>
        </tr>';

        $str .= '</tbody>
        </table>';
    }

    $resp['html_resp'] = $str;
    echo json_encode($resp);
} else if (strtolower($POST['mode']) == "unlock_inquiry") {
    $info['closing_date '] = date("Y-m-d");

    $updateinqid = update_record('tbl_inquiry', $info, "inquiry_id=" . $POST['inquiry_id'], $dbcon);
    if($updateinqid)
        echo "1";
    else
        echo "0";
} else if (strtolower($POST['mode']) == "load_pend_task") {
   
    $whr = '';  $left_join='';
    $com_conf = getCompanyConfiguration($dbcon);
    if ($POST['fil_due_date']) {
        $whr .= " and DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='" . date('Y-m-d', strtotime($POST['fil_due_date'])) . "'";
    }

    if ($POST['fil_task_type_id']) {
        $whr .= ' and task.task_type_id=' . $POST['fil_task_type_id'];
    }

    if ($POST['fil_task_type_id']==28) {
        $whr .= ' and task.entry_type=3';
        $left_join .= 'left join tbl_customer as cust on cust.cust_id=task.cust_id';
        $left_join .= ' left join tbl_cust_address as cadd on cadd.cust_id=task.cust_id and cadd.c_addr_defult=1';
        $left_join .= ' left join tbl_opportunity_mst as stage on stage.opp_id=task.opp_id';
    }else{
        $whr .= ' and task.entry_type=1';
        $left_join .= 'left join tbl_customer as cust on cust.cust_id=inq.c_id';
        $left_join .= ' left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_addr_defult=1';   
        $left_join .= ' left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id';   
    }

    //Amish Soni Start 01-02-2021
    $is_general = (isset($POST['is_general']) && $POST['is_general']) ? $POST['is_general'] : false;
    if ($is_general) {
        $whr .= " and task.task_type_id = '" . GENERAL_TASK_TYPE . "' ";
    } else {
        $whr .= " and task.task_type_id != '" . GENERAL_TASK_TYPE . "' ";
    }
    //Amish Soni End 01-02-2021
    $month = date("m");
    if ($POST['log_user_id']) {
        $user_id = $POST['log_user_id'];
        $fis = check_crm_find_in_set_new($dbcon, $user_id, 1);
        $ftp = " and FIND_IN_SET (" . $user_id . ",task.show_user_ids)";
    } else {
        if (!empty($POST['c_user_id'])) {
            $fis = check_crm_find_in_set_new($dbcon, $POST['c_user_id'], 1);
            $ftp = " and FIND_IN_SET (" . $POST['c_user_id'] . ",task.show_user_ids)";
        } else {
            $user_id = $_SESSION['user_id'];
            $fis = check_crm_find_in_set_new($dbcon, $user_id, 1);
            $ftp = " and FIND_IN_SET (" . $user_id . ",task.show_user_ids)";
        }
    }

    $appData = array();
    $i = 1;

    $aColumns = array('task.task_id', 'task.task_rel_id', 'task.task_name','tea.t_name', 'inq.inquiry_no', 'qt_aprv.quotation_no','inq.inquiry_name', 'inq.inquiry_date', 'task.cust_id', 'cust.cust_name','cust.cust_mobile', 'per.c_con_fname', 'row.task_rel_name', 'state.state_name', 'city.city_name', 'task.task_due_date', 'task.task_remark', 'usr.user_name', 'task.task_rel_id', 'task.assign_user_ids', 'task.inquiry_id', 'task.task_type_id', 'task.task_status', 'task.entry_type', 'task.alert_date_time', 'type.mcd_name as type_name', 'task.user_id', 'task.task_priority_id', 'task_sub.mcd_name as task_sub_name','if(tr.project_wise=0,(SELECT group_concat(pro.product_name SEPARATOR ",<br>") FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as pro_name','if(tr.project_wise=0,(SELECT group_concat(pro.product_name SEPARATOR ",
    ") FROM `tbl_quotation_trn` as trn 
    left join tbl_quotation as qt on qt.quotation_id = trn.quotation_id
    left join product_mst as pro on pro.product_id=trn.product_id where trn.quot_trn_status=0 and qt.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as quot_pro_name', 'stage.opp_stage', 'stage.opp_probability', 'qt_aprv.approve_status', 'qt_aprv.quotation_id', 'type.mcd_id','mcd.mcd_name','inq.closing_date');
    $sIndexColumn = "task.task_id";
    //and alert_date_time="'.date('Y-m-d',strtotime($POST['fil_due_date'])).'" and
    $isWhere = array("task.task_status=0  and task.company_id in (0,$_SESSION[company_id])" . $whr . " " . $ftp);
    $sTable = "tbl_task as task";
    $isJOIN = array('left join users as usr on usr.user_id=task.user_id',
        'left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
        'left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id',
        'left join task_rel_mst as row on row.task_rel_id=task.task_rel_id',
        'left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,opp_id from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id
        left join (SELECT inquiry_id,approve_status,quotation_id,quotation_no FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id',
        'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
        $left_join,
        'left join state_mst as state on state.stateid=cadd.c_add_state',
        'left join city_mst as city on city.cityid=cadd.c_add_city',
        'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id',
        'left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type',
        'left join territory_mst as tea on tea.t_id=cust.t_id');
    $hOrder = "task.task_id ".$com_conf['crm_task_order'];
    $hGroupby = array("task.task_id");
    $having_clause="";
    include('../../../include/pagging.php');
    $appData = array();
    $id = 1;
    foreach ($sqlReturn as $row) {
        $com_confi = getCompanyConfiguration($dbcon);
        $add_quot_btn = '';
        $view_inq_btn = '';
        $add_apt_btn = '';
        $post_crm ="";
        $inq_limit = ($com_confi['enable_inquiry_autoclose']==1) ? $com_confi['inquiry_autoclose_limit'] : 0;
        $days = $inq_limit." days";
        $inq_dates = date("Y-m-d",strtotime($row['closing_date']));
        $inq_date = date_create($inq_dates);
        date_add($inq_date, date_interval_create_from_date_string($days));
        $next_date = date_format($inq_date, 'Y-m-d');
        if ($row['task_rel_id'] == '5') {//Fixed Type Inquiry
            if ($row['mcd_id'] == 15) {
                $add_quot_btn = '<a href="' . ROOT . CRM_ROOT . 'inq_to_quot/' . $row['inquiry_id'] . '" data-original-title="Create Quotation" data-toggle="tooltip" data-placement="top" class="btn btn-xs btn-success"><i class="fa fa-plus"></i></a>';
            }
            if ($row['mcd_id'] != 15) {
                $add_flp_btn = '<a href="' . ROOT . CRM_ROOT . 'task_add/' . $row['inquiry_id'] . '" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
            }
            $view_inq_btn = '<a href="' . ROOT . CRM_ROOT . 'inquiry_view/' . $row['inquiry_id'] . '" class="btn btn-xs btn-success" data-original-title="View Inquiry" data-toggle="tooltip" data-placement="top"><i class="fa fa-eye"></i></a>';
            $add_apt_btn = '<a href="' . ROOT . CRM_ROOT . 'appointment_add/' . $row['inquiry_id'] . '" class="btn btn-xs btn-info" data-original-title="Add Appointment" data-toggle="tooltip" data-placement="top"><i class="fa fa-users"></i></a>';

            //Quot flp allow task only if approved in quot
            if ($row['task_type_id'] == '21' && $row['approve_status'] != '1') {
                //$add_flp_btn='<a href="'.ROOT.CRM_ROOT.'task_add/'.$row['inquiry_id'].'" class="btn btn-xs btn-primary" data-original-title="Add Follow-Up" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
                $add_quot_btn = '';
                $add_flp_btn = '<a href="javascript:;" class="btn btn-xs btn-primary" data-toggle="tooltip" title="Quotation is not approved yet"><i class="fa fa-plus"></i></a>';
            }
            //if task is to Create or revise quot then dont allow for flp
            if ($row['task_type_id'] == '15' && $row['task_type_id'] == '20') {
                $add_flp_btn = '';
                $add_apt_btn = '';
            }
            if ($row['task_type_id'] == '20') {
                $add_apt_btn = '';
                $add_flp_btn = '<a class="btn btn-xs btn-info" data-original-title="Revise Quotation" data-toggle="tooltip" data-placement="top" href="' . ROOT . CRM_ROOT . 'quotation_revise/' . $row['quotation_id'] . '"><i class="fa fa-repeat"></i></a>';
            }
        }
        if($com_confi['enable_inquiry_autoclose']==1){
            if($next_date < date("Y-m-d")){
                $add_flp_btn = '<a onclick="unlock_inquiry('.$row['inquiry_id'].')" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Inquiry is locked"><i class="fa fa-lock"></i></a>';
                $add_quot_btn = '';
            }
        }

        if ($row['task_rel_id'] == '5') {//Inquiry
            $inquiry_name = '';
            if($com_confi['followup_inquiry_show']==1){
                $inquiry_name = $row['inquiry_name'];
            }
            $rel_name = $row['cust_name'] . '<br/>' . $inquiry_name . '<br/>' . $row['inquiry_no']. '<br/>' .$row['quotation_no'];
        } else if ($row['task_rel_id'] == '4') { // Company
            $rel_name = $row['cust_name'];
        } else if ($row['task_rel_id'] == '3') {//Person
            $rel_name = $row['c_con_fname'];
        } else {
            $rel_name = $row['task_name'];
        }
        $pro_name ='';
        if($row['quot_pro_name']!=''){
            $pro_name = $row['quot_pro_name'];
        }else{
            $pro_name = $row['pro_name'];
        }
        $row_data = array();
        $row_data[] = $row['sr'];
        $row_data[] = '<strong>' . $row['type_name'] . '</strong> ';
        $row_data[] = $row['task_rel_name'];
        $row_data[] = '<strong>' . $rel_name . '</strong>';
        $row_data[] = $row['t_name'];
        $row_data[] = $row['cust_mobile'];
        $row_data[] = $row['mcd_name'];
        $row_data[] = $pro_name;
        $row_data[] = '' . $row['opp_stage'] . '(' . $row['opp_probability'] . '%).';
        $row_data[] = $row['state_name'].' - '.$row['city_name'];
        $row_data[] = date("d-M-Y", strtotime($row['task_due_date'])) . '<br/>' . date("h:i A", strtotime($row['task_due_date']));
        $row_data[] = nl2br($row['task_remark']);
        $row_data[] = $row['user_name'];
        $row_data[] = getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']);

        if($row['task_type_id']==28){
            $task_btn='';$add_rmrk_btn='';$add_flp_btn='';$add_quot_btn='';$add_apt_btn='';$view_inq_btn='';
            $post_crm = "<a class='btn btn-info btn-xs' data-original-title='Add Followup' data-toggle='tooltip' data-placement='top' onclick='add_followup(".$row['cust_id'].",".$month.")'>
                        <i class='fa fa-plus'></i>
                    </a>&nbsp";
        }else{
            $post_crm ="";
        }

        $row_data[] = $post_crm .' ' . $add_flp_btn . ' ' . $add_quot_btn . ' ' . $add_apt_btn . ' ' . $view_inq_btn;

        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode($output);
} else if (strtolower($POST['mode']) == "load_inquiry_stage") {
    $query = $dbcon->query("SELECT `opp_id`,`sales_stage_id`,stage_prob FROM `tbl_inquiry` WHERE `inquiry_id` = " . $POST['inq_id']);
    $data = mysqli_fetch_array($query, MYSQLI_ASSOC);
    echo json_encode($data);
} else if (strtolower($POST['mode']) == "add_lost_reason") {
    $counter = $POST['counter'];
    $html = '';
    $html .= '<div class="col-md-12 lost_reasons" id="lost_reason_div' . $counter . '">';
    $html .= '<div class="form-group">';
    $html .= '<label class="col-md-2 control-label" style="text-align: right;">Reason</label>';
    $html .= '<div class="col-md-3">';
    $html .= '<select class="select2" id="reason_id' . $counter . '" name="reason_id[]">';
    $html .= get_lost_reasons($dbcon, $id);
    $html .= '</select>';
    $html .= '</div>';
    $html .= '<label class="col-md-2 control-label">Reason Remark</label>';
    $html .= '<div class="col-md-3">';
    $html .= '<textarea class="form-control" name="lost_reason[]" id="lost_reason' . $counter . '" style="resize:both;" placeholder="Lost Reason" rows="1"/></textarea>';
    $html .= '</div>';
    $html .= '<div class="col-md-2">';
    $html .= '<button type="button" id="reason_btn' . $counter . '" class="btn btn-primary" title="View Details" onclick="remove_reason_div(this)"><i class="add_remove_reason fa fa-minus"></i></button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    $resp['html'] = $html;
    echo json_encode($resp);
} //Amish Soni Start 04-03-2021
else if (strtolower($POST['mode']) == "mark_read") {
    $field = ($_SESSION['user_type'] == '2') ? 'is_read_by_admin' : 'is_read';
    update_record('tbl_task', array($field => 1), "task_id=" . $POST['eid'], $dbcon);
}
else if(strtolower($POST['mode'])== "add_task_attch_field") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    $info1['inquiry_id'] = $_POST['inquiry_id'];
    $info1['inq_attch_doc_name'] = $_POST['inq_attch_doc_name'];
    $info1['inq_attch_file'] = upload_attch_file($_FILES);
    $info1['user_id']    = $_SESSION['user_id'];
    $info1['company_id'] = $_SESSION['company_id'];
    // $info1['cdate'] = date("Y-m-d H:i:s");

    $table='tbl_inq_attach';$tableid='inq_attach_id';

    $inserid=add_record($table, $info1, $dbcon, $branch_id);
}
else if(strtolower($POST['mode'])== "show_task_attach_data") {
    $chkmode=$POST['modee'];
    // $delete_btn_per = in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray);
    $query="SELECT * FROM tbl_inq_attach WHERE inq_attach_status = 0 AND inquiry_id = '".$POST['task_id']."'";
    $result=$dbcon->query($query);
    echo '<table class="display table table-bordered table-striped">
    <tr>
    <th width="60%" class="text-center">Document Name</th>
    <th width="30%" class="text-center">Attached Document</th>';
    echo'<th width="10%" class="text-center">Action</th>';
    echo'</tr>
    <tbody>';
    if(brp_mysqli_num_rows($result)>0){
        while($rows=brp_mysqli_fetch_assoc($result)){
            echo '<tr> 
            <td style="vertical-align:top;">
            <strong>'.$rows['inq_attch_doc_name'].'</strong>
            </td>
            <td style="vertical-align:top;" class="text-center">
            <a href="'.ROOT.INQ_ATTACH_VWING.$rows['inq_attch_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
            </td>
            <td style="vertical-align:top"><button type="button" class="btn btn-sm btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task_attach_data('.$rows['inq_attach_id'].')">X</button></td>
            </tr>';
            $i++;
        }
    }
    else{
        echo '<tr><td colspan="3" class="text-center">NO DATA FOUND</td></tr>';
    }

    echo '</tbody>
    </table>';
}
else if(strtolower($POST['mode'])== "delete_task_attach_data") {
    $row=array();
    $del_attch_qry="select inq_attch_file from tbl_inq_attach where inq_attach_id=".$POST['task_attach_id'];
    $del_attch_rel=mysqli_fetch_assoc($dbcon->query($del_attch_qry));
    unlink('../'.INQ_ATTACH_UPING.$del_attch_rel['inq_attch_file']);

    $info['inq_attach_status']=2;  
    $whr = "inq_attach_id = '".$POST['task_attach_id']."'"; 
    $updateid=delete_record('tbl_inq_attach', $whr , $dbcon);

    if($updateid)
        $row['res']="1";
    else
        $row['res']="0";
    echo json_encode($row);
}else if(strtolower($POST['mode'])== "add_generaltask_attch_field") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    // $info1['inquiry_id'] = $_POST['inquiry_id'];
    $info1['attachment_name'] = $_POST['attachment_name'];
    $info1['attachment_file'] = upload_attch_file1($_FILES);
    $info1['user_id']    = $_SESSION['user_id'];
    $info1['company_id'] = $_SESSION['company_id'];
    // $info1['cdate'] = date("Y-m-d H:i:s");
    if(empty($_POST['task_id'])){
        $info1['attch_status']    = 3;
    }else{
        $info1['attch_status']    = 0;
        $info1['task_id'] = $_POST['task_id'];
    }

    $table='tbl_task_attachment';$tableid='task_attch_id';

    $inserid=add_record($table, $info1, $dbcon, $branch_id);
}
else if(strtolower($POST['mode'])== "show_generaltask_attach_data") {
    $chkmode=$POST['mode'];
    // $delete_btn_per = in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray);
    if(!empty($POST['task_id'])){
        $query="SELECT * FROM tbl_task_attachment WHERE attch_status = 0 AND task_id = '".$POST['task_id']."'";
    }else{
        $query="SELECT * FROM tbl_task_attachment WHERE attch_status = 3 AND user_id = '".$_SESSION['user_id']."'";
    }
    $result=$dbcon->query($query);
    echo '<table class="display table table-bordered table-striped">
    <tr>
    <th width="60%" class="text-center">Document Name</th>
    <th width="30%" class="text-center">Attached Document</th>';
    if(strtolower($chkmode)!='modalview'){
        echo'<th width="10%" class="text-center">Action</th>';
    }
    echo'</tr>
    <tbody>';
    if(brp_mysqli_num_rows($result)>0){
        while($rows=brp_mysqli_fetch_assoc($result)){
            echo '<tr> 
            <td style="vertical-align:top;">
            <strong>'.$rows['attachment_name'].'</strong>
            </td>
            <td style="vertical-align:top;" class="text-center">
            <a href="'.ROOT.INQ_ATTACH_VWING.$rows['attachment_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
            </td>';
            if(strtolower($chkmode)!='modalview'){
            echo '<td style="vertical-align:top"><button type="button" class="btn btn-sm btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task_attach_data('.$rows['task_attch_id'].')">X</button></td>';
        }
            echo '</tr>';
            $i++;
        }
    }
    else{
        echo '<tr><td colspan="3" class="text-center">NO DATA FOUND</td></tr>';
    }

    echo '</tbody>
    </table>';
}
else if(strtolower($POST['mode'])== "delete_generaltask_attach_data") {
    $row=array();
    // var_dump($POST);exit;
    $del_attch_qry="select attachment_file from tbl_task_attachment where task_attch_id=".$POST['task_attach_id'];
    $del_attch_rel=mysqli_fetch_assoc($dbcon->query($del_attch_qry));
    unlink('../'.INQ_ATTACH_UPING.$del_attch_rel['attachment_file']);

    $info['attch_status']=2;  
    $whr = "task_attch_id = ".$POST['task_attach_id']; 
    $updateid=update_record('tbl_task_attachment', $info, $whr , $dbcon);

    if($updateid)
        $row['res']="1";
    else
        $row['res']="0";
    echo json_encode($row);
}
else if(strtolower($POST['mode'])== "read_flp") {
    $row=array();
    
    if(strtolower($POST['user_type'])=='owner_user'){
        $info['user_id_read']=$POST['status'];    
    }else{
        $info['assign_user_ids_read']=$POST['status'];
    }
      
     
    $updateid=update_record('tbl_followup', $info, 'flp_id='.$POST['flp_id'] , $dbcon);

    if($updateid)
        $row['res']="1";
    else
        $row['res']="0";
    echo json_encode($row);
}
//Amish Soni End 04-03-2021
function getTaskAssignNameCommaSeparated($dbcon, $assign_user_ids = '')
{

    $strVal = '';
    $qry = 'SELECT tsk.task_id, GROUP_CONCAT(userdata.user_name) AS valuesdata FROM tbl_task tsk JOIN users AS userdata ON FIND_IN_SET(userdata.user_id, "' . $assign_user_ids . '") GROUP BY tsk.task_id';
        $qry_rel = mysqli_fetch_assoc($dbcon->query($qry));

        if ($qry_rel) {
            $strVal = $qry_rel['valuesdata'];
        }
        return $strVal;
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
    function upload_attch_file1($FILES){
        $rand=rand(0,99999999);
        if(!empty($FILES['attachment_file']['tmp_name'])) {
            $temp = explode(".", $FILES["attachment_file"]["name"]);
            $extension = strtolower(end($temp));
            $File = "general_attach_".$rand.".".$extension;
            $tmp_name = $FILES["attachment_file"]["tmp_name"];
            move_uploaded_file($tmp_name,'..//'.INQ_ATTACH_UPING.$File);

            return  $File;              
        }
    }
?>