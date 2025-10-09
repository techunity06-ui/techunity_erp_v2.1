<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
// error_reporting(E_ALL);
// include_once(COMMON_FUNCTION_INNER_PATH."crm_common_functions.php");
include_once($incPath."common_send_email.php");
// Amish Soni End 30-12-2020
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    INQUIRY_SLUG_EDIT,
    INQUIRY_SLUG_DELETE
]);
//error_reporting(E_ALL);

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {
    
    $show_owner = TRUE; //please chenge in inquiry list also
    if($POST['start_date'] && $POST['end_date']){
        $_SESSION['start'] = $start_date = $POST['start_date'];
        $_SESSION['end'] = $end_date = $POST['end_date'];
    } else if(isset($_SESSION['summary_start_date']) && !empty($_SESSION['summary_start_date']) 
        && isset($_SESSION['summary_end_date']) && !empty($_SESSION['summary_end_date'])){
        $start_date = $_SESSION['summary_start_date'];
        $end_date = $_SESSION['summary_end_date'];
    } else {
        $start_date = date('1-m-Y');
        $end_date = date("d-m-Y");
    } 

$branch_id = $POST['branch_id'];
$getspecialConfiguration=getspecialConfiguration($dbcon);
$where='';
$stage_where = ' and task.task_status = 0';
$stage_flag = TRUE;
if(isset($POST['stage_id']) && !empty($POST['stage_id'])){
    $where .= " AND inq.opp_id =".$POST['stage_id'];
    $stage_flag = FALSE;
    if($POST['stage_id']=='12' || $POST['stage_id']=='13'){
        $stage_where = '';
    }
}

if(isset($POST['sales_stage_id']) && !empty($POST['sales_stage_id'])){
    $where .= " AND inq.sales_stage_id IN(".$POST['sales_stage_id'].") ";
    $stage_flag = FALSE;
}

if(isset($POST['sales_stage_cat_id']) && !empty($POST['sales_stage_cat_id'])){
    $where .= " AND inq.inquiry_cat_id IN(".$POST['sales_stage_cat_id'].") ";
    $stage_flag = FALSE;
}

if(isset($POST['source_id']) && !empty($POST['source_id'])){
    $where .= " AND cust.cust_source IN(".$POST['source_id'].") ";
    $stage_flag = FALSE;
}

if(isset($POST['assign_user_id']) && !empty($POST['assign_user_id'])){
    $where .= " AND inq.user_id = '".$POST['assign_user_id']."'";
    $stage_flag = FALSE;
}

if(isset($POST['user_id']) && !empty($POST['user_id'])){
    $where .= " AND inq.owner_user_id = ".$POST['user_id'];
    $stage_flag = FALSE;
}

if(!empty($start_date) && !empty($end_date)){
    $where.="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($end_date))."'";
}

if(isset($POST['country_id']) && !empty($POST['country_id'])){
    $where .= " AND cadd.c_add_country = ".$POST['country_id'];
    $stage_flag = FALSE;
}
if(isset($POST['state_id']) && !empty($POST['state_id'])){
    $where .= " AND cadd.c_add_state = ".$POST['state_id'];
    $stage_flag = FALSE;
}

if(isset($POST['city_id']) && !empty($POST['city_id'])){
    $where .= " AND cadd.c_add_city = ".$POST['city_id'];
    $stage_flag = FALSE;
}

if($stage_flag){
    $stage_where = " AND inq.opp_id NOT IN(12,13)";
}
if($_SESSION['user_type']!=2){ 
    $where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
}

if (!empty($_SESSION['objection_month'])) {
    $where .= " AND objection_flag=1 AND MONTH(inq.inquiry_date) = MONTH(STR_TO_DATE('".$_SESSION['objection_month']."','%M'))"; 
}

if(isset($POST['product_id']) && !empty($POST['product_id'])){
    $where .= " AND pro.product_id = ".$POST['product_id'];
}

if(isset($POST['category_id']) && (!empty($POST['category_id']) || $POST['category_id'] == '0')){
    $where .= " AND pro.product_category = ".$POST['category_id'];
}


$appData = array();
$i=1;
$aColumns = array('inq.inquiry_id','inq.owner_user_id','usr.user_name','owner_usr.user_name as owner', 'inq.inquiry_no', 'inq.inquiry_date', 'city.city_name', 'inq.inquiry_name', 'cust.cust_name','cust.cust_mobile', 'per.c_con_fname','per.c_con_mobile', 'stage.opp_stage','stage.opp_color','inq.stage_prob', 'inq.inquiry_status','task.cdate','inq.mdate','inq.cust_id','inq.g_total','inq.company_id','updated_user.user_name as updated_by','tr.project_wise','mcd.mcd_name','state.state_name','country.country_name','cadd.c_add_state','cadd.c_add_country','cadd.c_add_address','inq.won_user_id','source.rb_name','cust.cust_source');
$sIndexColumn = "inq.inquiry_id";
$isWhere = array("inq.inquiry_status = 0  and inq.company_id in (0,$_SESSION[company_id]) ".$stage_where.$where);
$sTable = "tbl_inquiry as inq";
$isJOIN = array(
    'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
    'left join product_mst as pro on pro.product_id=tr.product_id',
    'left join tbl_task as task on task.inquiry_id=inq.inquiry_id',
    'left join tbl_customer as cust on cust.cust_id=inq.cust_id',
    'left join tbl_cust_contact as per on per.c_con_id=inq.c_con_id', 
    'left join tbl_opportunity_mst as stage on stage.opp_id=inq.opp_id', 
    'left join users as usr on usr.user_id=inq.user_id',
    'left join users as owner_usr on owner_usr.user_id=inq.owner_user_id',
    'left join users as updated_user on updated_user.user_id=inq.updated_by_userid',
    'left join tbl_cust_address as cadd on cadd.cust_id=inq.cust_id and cadd.c_add_status=0 and c_addr_defult=1',
    'left join tbl_refer_by as source on source.rb_id=cust.cust_source',
    'left join city_mst as city on city.cityid=cadd.c_add_city',
    'left join state_mst as state on state.stateid=cadd.c_add_state',
    'left join country_mst as country on country.countryid=cadd.c_add_country',
    'left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type');
$hOrder = "inq.cdate desc";
$hGroupby = array("inq.inquiry_id");
include($incPath.'pagging.php');
    //$appData = array();
$id=1;
// print_r($sqlReturn);exit;
foreach($sqlReturn as $row) {
    $row_data = array();

    if($row['project_wise'] == 0){
        $query_pro = 'select group_concat(pro.product_name SEPARATOR ",<br>") as pro_name FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id='.$row['inquiry_id'];
     }else{
        $query_pro = 'select group_concat(proj.project_name) as pro_name from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id='.$row['inquiry_id'];
     }
   
    $rel = brp_mysqli_fetch_array($dbcon->query($query_pro));

    $query_task = 'select COUNT(task_id) as task_count FROM tbl_task WHERE inquiry_id ='.$row['inquiry_id'];

    $rel_tas = brp_mysqli_fetch_array($dbcon->query($query_task));
    
    // $query_i="select GROUP_CONCAT(DISTINCT mst.user_name SEPARATOR ',<br/>') as asinguser from users as mst
    // where mst.user_id in (".$row['assign_user_ids'].")";
    // $result_i=$dbcon->query($query_i);
    // $rel_i=mysqli_fetch_assoc($result_i);

    $bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';

    $flp_qry="select task.task_remark from tbl_task as task where task.task_status!=2 and task.entry_type=1 and task.inquiry_id=".$row['inquiry_id']." order by create_date DESC limit 1";
    $flp_result = $dbcon->query($flp_qry);
    $flp_row=mysqli_fetch_assoc($flp_result);

    $row_data[] = date('d M, Y',strtotime($row['inquiry_date']));
    if(in_array(INQUIRY_SLUG_EDIT,$bulkAccessArray) && $POST['stage_id'] != 12 && $rel_tas['task_count']==1){
        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["inquiry_no"].'</a>';
		// $row_data[] = $row['inquiry_name'];
        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["cust_name"].'</a><br/>'.$row['inquiry_name'];
        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["cust_mobile"].'</a>';
        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["mcd_name"].'</a>';
        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["rb_name"].'</a>';
        $row_data[] = $row['c_add_address'];
        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$row["city_name"].'<br>'.$row['state_name'].'<br>'.$row['country_name'].'</a>';

        $row_data[] = '<a class="" data-original-title="Edit '.$row['inquiry_no'].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'">'.$rel["pro_name"].'</a>';
    }else{
       $row_data[] = $row['inquiry_no'].' '.$row['COUNT(task.task_id)'];
			//$row_data[] = $row['inquiry_name'];
       $row_data[] = $row['cust_name'].'<br/>'.$row['inquiry_name'];
       $row_data[] = $row['cust_mobile'];
       $row_data[] = $row['mcd_name'];
       $row_data[] = $row['rb_name'];
       $row_data[] = $row['c_add_address'];
       $row_data[] = $row['city_name'].'<br>'.$row['state_name'].'<br>'.$row['country_name'];
       $row_data[] = $rel['pro_name'];
   }
   $row_data[] = '<span class="btn btn-sm" style="color:black;background-color: '.$bg_color.';">'.$row['opp_stage'].'<span>';
   $row_data[] = $flp_row['task_remark'];

   if($show_owner){
        $row_data[] = $row['owner'];
    }

$row_data[] = $row['user_name'];

$row_data[] = $row['updated_by'].' updated on '.date('d M, Y',strtotime($row['cdate'])).' by '.date('h:i A',strtotime($row['cdate']));

        //$row_data[] = $rel_i['asinguser'];
$edit='';$delete='';$view_hist_btn='';$send_email='';$print='';$inquiry_review_print='';
if($rel_tas['task_count']==1){
    if(in_array(INQUIRY_SLUG_EDIT,$bulkAccessArray) && $POST['stage_id'] != 12) {
        $edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'"><i class="fa fa-pencil"></i></a>';
    }
    
    if(in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray) && $POST['stage_id'] != 12) {
        $inquiry_no = $dbcon->real_escape_string($row['inquiry_no']);
        $delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inquiry('.$row['inquiry_id'].',\''.$inquiry_no.'\')"><i class="fa fa-trash-o"></i></button>';
    }
}

if (in_array(INQUIRY_SLUG_EDIT,$bulkAccessArray) && $getspecialConfiguration["umaboy_permission"] == 1) {
    $edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_edit/'.$row['inquiry_id'].'"><i class="fa fa-pencil"></i></a>';
}

$add_task_btn = $add_appointment_btn = '';$add_acnowledge='';
$view_hist_btn = '<button class="btn btn-xs btn-info" data-original-title="View History" data-toggle="tooltip" data-placement="top" onClick="view_followup_hist('.$row['inquiry_id'].')"><i class="fa fa-history"></i></button>';

$view_attach_doc = '<button class="btn btn-xs btn-info" data-original-title="View Attached Document" data-toggle="tooltip" data-placement="top" onClick="view_attach_document('.$row['inquiry_id'].',\''.$row['inquiry_no'].'\')"><i class="fa fa-eye"></i></button>';
if($getspecialConfiguration['filter_concept_permission']==1){
$add_acnowledge = '<button class="btn btn-xs btn-success" data-original-title="View Acknowledgement" data-toggle="tooltip" data-placement="top" onClick="view_acknowledgement('.$row['inquiry_id'].')"><i class="fa fa-sign-language" aria-hidden="true"></i></button>';
}
$send_email = '<button class="btn btn-xs btn-primary" data-original-title="Send Email" data-toggle="tooltip" data-placement="top" onClick="open_inq_email('.$row['inquiry_id'].','.$row['cust_id'].')"><i class="fa fa-envelope"></i></button>'; 

if($POST['stage_id'] != 12){
    $add_task_btn = '<button class="btn btn-xs btn-primary" data-original-title="Add Task" data-toggle="tooltip" data-placement="top" onClick="open_add_task_popup('.$row['inquiry_id'].',1)"><i class="fa fa-list-alt"></i></button>';
}

if($POST['stage_id'] != 12){
    $add_appointment_btn = '<button class="btn btn-xs btn-primary" data-original-title="Add Appointment" data-toggle="tooltip" data-placement="top" onClick="open_add_task_popup('.$row['inquiry_id'].',2)"><i class="fa fa-clock-o"></i></button>';
}
$com_confi = getCompanyConfiguration($dbcon);
if($com_confi['enable_inquiry_autoclose']==1){
    $inq_limit = $com_confi['inquiry_autoclose_limit'];
    $days = $inq_limit." days";
    $inq_dates = date("Y-m-d",strtotime($row['inquiry_date']));
    $inq_date = date_create($inq_dates);
    date_add($inq_date, date_interval_create_from_date_string($days));
    $next_date = date_format($inq_date, 'Y-m-d');
    if($next_date < date("Y-m-d")){
        $add_task_btn = '';
        $add_appointment_btn = '';
    }
}
if($getspecialConfiguration['aeon_permission']==1){
$print = '<a class="btn btn-xs btn-danger" data-original-title="Export Xlsx File" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRINT_ROOT.'html_print/'.$row['inquiry_id'].'"><i class="fa fa-print"></i></a>';
}
 $harddelete = "";
if($_SESSION['user_type']==2){
    if($row['won_user_id']==0){
        $harddelete = '<button class="btn btn-xs btn-danger" data-original-title="Hard Delete" data-toggle="tooltip" data-placement="top" onClick="hard_delete_inquiry('.$row['inquiry_id'].',\''.$inquiry_no.'\')">Hard Delete <i class="fa fa-trash-o"></i></button>';
    }
}

if($getspecialConfiguration['libra_engineering_permission']==1){
    $inquiry_review = '<a class="btn btn-xs btn-primary" data-original-title="Inquiry Review" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'inquiry_review/'.$row['inquiry_id'].'"><i class="fa fa-plus"></i></a>';

    $inquiry_review_print = '<a class="btn btn-xs btn-danger" data-original-title="Inquiry Review" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRINT_ROOT.'inquiry_review_print/'.$row['inquiry_id'].'"><i class="fa fa-print"></i></a>';
}

$row_data[] = $edit.' '.$delete.' '.$view_hist_btn.' '.$send_email.' '.$add_task_btn.' '.$add_appointment_btn.' '.$add_acnowledge.' '.$view_attach_doc.' '.$print.' '.$harddelete.' '.$inquiry_review.' '.$inquiry_review_print;

$appData[] = $row_data;
$id++;
}
$output['aaData'] = $appData;
echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
    
   $company_state = get_company_data($dbcon,$_SESSION['company_id']);
   $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
   $products = ($POST['inq_product_required']==1) ? get_inquiry_products($dbcon,'') : '123';

   $cust_id = $POST['cust_id'];
   $cust_rb_id = $dbcon->query("SELECT cust_source as rb_id FROM `tbl_customer` WHERE cust_id=$cust_id")->fetch_object();
    
    if(empty($products)){
        $arr['msg'] = "2";
    } 
    else 
    {
        $show_user_ids			          = show_user_ids($dbcon,$POST['assign_user_inq_ids']);
         $info['inquiry_no']		      = load_common_no($dbcon,INQUIRY_SERIES);
        //Update Start series of No
         update_common_no($dbcon,INQUIRY_SERIES);

         $info['inquiry_type']              = $POST['inquiry_type'];
         $info['inquiry_date']              = date('Y-m-d',strtotime($POST['inquiry_date']));
         $info['cust_id']		            = $POST['cust_id'];
         $info['c_con_id']		            = $POST['c_con_id'];
         $info['assign_user_inq_ids']       = $POST['assign_user_inq_ids'];
         $info['inquiry_name']              = $POST['inquiry_name'];
         $info['closing_date']              = date('Y-m-d',strtotime($POST['closing_date']));
         $info['closed_reason']		        = $POST['closed_reason'];
         $info['t_id']			            = $POST['t_id'];
         $info['opp_id']			        = $POST['opp_id'];
         $info['stage_prob']		        = $POST['stage_prob'];
         $info['sales_stage_id']            = $POST['sales_stage_id'];
         $info['inquiry_type_id']           = $POST['inquiry_type_id'];
         $info['rb_id']			            = $cust_rb_id ? $cust_rb_id->rb_id : $POST['rb_id'];
         $info['inquiry_cat_id']            = $POST['inquiry_cat_id'];
         $info['task_priority_id']          = $POST['task_priority_id'];
         $info['currency_id']               = $POST['currency_id'];
         $info['currency_rate']             = $POST['currency_rate'];
         $info['gst_type']				    = $POST['gst_type'];
         if($POST['currency_id']==$company_state['currency_id']){
            $info['g_total']                = $POST['g_total'];
            $info['g_total_conv']           = $POST['g_total']*$POST['currency_rate'];
         }else{
            $info['g_total']                = $POST['g_total']*$POST['currency_rate'];
            $info['g_total_conv']           = $POST['g_total'];
         }
         
         $info['inq_desc']		            = $POST['inq_desc'];
         $info['inq_comp_desc']             = $POST['inq_comp_desc'];
         $info['project_name']              = $_POST['project_name'];
         $info['inquiry_project_name']      = $_POST['project_name'];
         $info['create_date']               = date('Y-m-d H:i:s');
         $info['cdate']			            = date("Y-m-d H:i:s");
         $info['owner_user_id']             = $_SESSION['user_id'];
         $info['user_id']		            = $POST['assign_user_inq_ids']; //$_SESSION['user_id'];
         $info['show_user_ids']             = $show_user_ids;
         $info['updated_by_userid']         = $_SESSION['user_id'];
         $info['company_id']		        = $_SESSION['company_id'];

         if (isset($_POST['objection_flag'])) {
            $info['objection_flag'] = $_POST['objection_flag'];
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
        /*Update Trn Table Start*/
        if($ins_inquiry_id){
            $infotrn['inquiry_id']			= $ins_inquiry_id;
            $infotrn['inquiry_trn_status']	= 0;

            $updatetrnid=update_record('tbl_inquiry_trn', $infotrn,"inquiry_trn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
        }
        /*Update Trn Table End*/

        //if Inquiry won without Quotation, auto create Quotation 
        if($POST['opp_id'] == WON && !check_has_quotation($dbcon,$ins_inquiry_id)){
            auto_create_quotation($dbcon,$POST,$ins_inquiry_id);
            $info['won_by_userid']      = $_SESSION['user_id'];
            $info['won_user_id']	= $_SESSION['user_id'];
            $info['won_date']           = date("Y-m-d H:i:s");
            update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s")), "inquiry_id=".$ins_inquiry_id, $dbcon, $branch_id);
            $updateid = update_record('tbl_inquiry', $info, "inquiry_id=".$ins_inquiry_id, $dbcon);
        }
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
        if($POST['opp_id'] == LOST){
            $reason = array();
            if(!empty($POST['reason_id']) && !empty($POST['lost_reason'])){
                $reason = array_combine($POST['reason_id'],$POST['lost_reason']);
            }
            $infores['lost_by_userid']      = $_SESSION['user_id'];
            $infores['closed_reason']       = json_encode($reason);
            $infores['closing_date']       = date("Y-m-d");
           
            update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s"),'lost_reason'=>json_encode($reason)), "inquiry_id=".$ins_inquiry_id, $dbcon, $branch_id);

            $updateid = update_record('tbl_inquiry', $infores, "inquiry_id=".$ins_inquiry_id, $dbcon);
        }


          if($ins_inquiry_id && $POST['inquiry_type']!='1'){
            $infoproject['inquiry_id']            = $ins_inquiry_id;
            $infoproject['inquiry_projecttrn_status'] = 0;

            $updatetrnid=update_record('tbl_inquiry_project_trn', $infoproject,"inquiry_projecttrn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);
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
            $from_email_id = ($cur_user && $cur_user['user_mail']) ? $cur_user['user_mail'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';
//      	$cust_name = ($customer && $customer['cust_name']) ? $customer['cust_name'] : '';

            if($mail_template && $to_email_id) {

                $querybcc="select email_cc,email_bcc from email_sms_template where email_sms_id=".$POST['email_template_id'];
                $resultbdd=$dbcon->query($querybcc);
                $rel1=brp_mysqli_fetch_assoc($resultbdd);

                if(!empty($rel1['email_cc'])){
                    $umix=explode(",",$rel1['email_cc']);
                    $umix=array_push($umix,$info['owner_user_id']);
                    $uid=implode(",",$umix);
                }else{
                    //var_dump($uid);
                    $uid=$info['owner_user_id'];
                }

                $querybcc1="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (".$uid.")";
                $resultbdd1=$dbcon->query($querybcc1);
                $rel11=brp_mysqli_fetch_assoc($resultbdd1);

                $querybcc2="select GROUP_CONCAT(common_email_id SEPARATOR ";") as email_bcc from users where user_id in (".$rel1['email_bcc'].")";
                $resultbdd2=$dbcon->query($querybcc2);
                $rel12=brp_mysqli_fetch_assoc($resultbdd2);

                // Amish Soni End 18-01-2021
                
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

                final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content);
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

    $cust_id = $POST['cust_id'];
    $cust_rb_id = $dbcon->query("SELECT cust_source as rb_id FROM `tbl_customer` WHERE cust_id=$cust_id")->fetch_object();
    
    $show_user_ids			           = show_user_ids($dbcon,$POST['assign_user_inq_ids']);
    $info['show_user_ids']             = $show_user_ids;
    $info['inquiry_date']              = date('Y-m-d',strtotime($POST['inquiry_date']));
    $info['cust_id']		           = $POST['cust_id'];
    $info['c_con_id']		           = $POST['c_con_id'];
    $info['assign_user_inq_ids']       = $assigned_users;
    $info['inquiry_name']              = $POST['inquiry_name'];
    $info['closing_date']              = date('Y-m-d',strtotime($POST['closing_date']));
    $info['closed_reason']		       = $POST['closed_reason'];
    $info['t_id']			           = $POST['t_id'];
    $info['opp_id']			           = $POST['opp_id'];
    $info['stage_prob']		           = $POST['stage_prob'];
    $info['sales_stage_id']            = $POST['sales_stage_id'];
    $info['inquiry_type_id']           = $POST['inquiry_type_id'];
    $info['rb_id']			           = $cust_rb_id ? $cust_rb_id->rb_id : $POST['rb_id'];
    $info['inquiry_cat_id']            = $POST['inquiry_cat_id'];
    $info['currency_id']               = $POST['currency_id'];
    $info['currency_rate']             = $POST['currency_rate'];
    $info['gst_type']				   = $POST['gst_type'];
    $info['task_priority_id']          = $POST['task_priority_id'];
    if($POST['currency_id']==$company_state['currency_id']){
        $info['g_total']		        = $POST['g_total'];
        $info['g_total_conv']           = $POST['g_total']*$POST['currency_rate'];
    }else{
        $info['g_total']                = $POST['g_total']*$POST['currency_rate'];
        $info['g_total_conv']           = $POST['g_total'];
    }

    $info['inq_desc']		           = $POST['inq_desc'];
    $info['inq_comp_desc']             = $POST['inq_comp_desc'];
    $info['cdate']			           = date("Y-m-d H:i:s");
    $info['updated_by_userid']         = $_SESSION['user_id'];
    $info['user_id']		           = $POST['assign_user_inq_ids'];
    $info['project_name']              = $_POST['project_name'];
    $info['inquiry_project_name']      = $_POST['project_name'];

    //Maulik Code add
    $task_update['task_priority_id']   = $POST['task_priority_id'];
    $task_update['assign_user_ids']   = $POST['assign_user_inq_ids'];

    $old_task_show_users = $dbcon->query("SELECT show_user_ids FROM tbl_task WHERE inquiry_id = ".$POST['eid'])
    ->fetch_object()->show_user_ids;
    if($old_task_show_users) {
        $assigned_users_arr = explode(',', $old_task_show_users);
        if(!in_array($POST['assign_user_inq_ids'], $assigned_users_arr)){
            $task_assigned_users = $old_task_show_users.','.$POST['assign_user_inq_ids'];
            $task_update['show_user_ids']   = $task_assigned_users;
        }
    }
    update_record('tbl_task', $task_update, "inquiry_id=".$POST['eid'], $dbcon, $branch_id);
    
    if($POST['opp_id'] == WON){
        $info['won_by_userid']         = $_SESSION['user_id'];
        $info['won_user_id']	       = $_SESSION['user_id'];
        $info['won_date']              = date("Y-m-d H:i:s");
        update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s")), "inquiry_id=".$POST['eid'], $dbcon, $branch_id);
    }

    if($POST['opp_id'] == LOST){
        $reason = array();
        if(!empty($POST['reason_id']) && !empty($POST['lost_reason'])){
            $reason = array_combine($POST['reason_id'],$POST['lost_reason']);
        }
        $info['lost_by_userid']      = $_SESSION['user_id'];
        $info['closed_reason']       = json_encode($reason);

        update_record('tbl_task', array('task_status'=>'1','task_completion_date'=>date("Y-m-d H:i:s"),'lost_reason'=>json_encode($reason)), "inquiry_id=".$POST['eid'], $dbcon, $branch_id);
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

    if (isset($_POST['objection_flag'])) {
        $info['objection_flag'] = $_POST['objection_flag'];
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
            $from_email_id = ($cur_user && $cur_user['user_mail']) ? $cur_user['user_mail'] : ADMIN_EMAIL;
            $to_email_id = ($customer && $customer['cust_email']) ? $customer['cust_email'] : '';
//            $cust_name = ($customer && $customer['cust_name']) ? $customer['cust_name'] : '';
            
            if($mail_template && $to_email_id) {

                $querybcc="select email_cc,email_bcc from email_sms_template where email_sms_id=".$POST['email_template_id'];
                $resultbdd=$dbcon->query($querybcc);
                $rel1=brp_mysqli_fetch_assoc($resultbdd);
                //var_dump($info['updated_by_userid']);
                if(!empty($rel1['email_cc'])){
                    $umix=explode(",",$rel1['email_cc']);
                    $umix=array_push($umix,$info['updated_by_userid']);
                    $uid=implode(",",$umix);
                }else{
                    //var_dump($uid);
                    $uid=$info['updated_by_userid'];
                }
                
                
                $querybcc1="select GROUP_CONCAT(common_email_id SEPARATOR ';') as email_cc from users where user_id in (".$uid.")";
                $resultbdd1=$dbcon->query($querybcc1);
                $rel11=brp_mysqli_fetch_assoc($resultbdd1);

                $querybcc2="select GROUP_CONCAT(common_email_id SEPARATOR ";") as email_bcc from users where user_id in (".$rel1['email_bcc'].")";
                $resultbdd2=$dbcon->query($querybcc2);
                $rel12=brp_mysqli_fetch_assoc($resultbdd2);
                // Amish Soni Start 18-01-2021
                $subject = $mail_template['email_subject'];
                $content = $mail_template['email_content'];

                $subject = replaceMergeFields($dbcon, $subject, $POST['cust_id'], $module_id);
                $content = replaceMergeFields($dbcon, $content, $POST['cust_id'], $module_id);
                // Amish Soni End 18-01-2021
                final_send_email($from_email_id, $to_email_id, $rel11['email_cc'],$rel12['email_bcc'], $subject, $content);
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
        $infotask['task_status']  = 2;
        $updateid = update_record('tbl_inquiry', $info, "inquiry_id=".$POST['inquiry_id'], $dbcon);
        $updatetrnid = update_record('tbl_inquiry_trn', $infotrn, "inquiry_id=".$POST['inquiry_id'], $dbcon);
        $updateprojecttrnid = update_record('tbl_inquiry_project_trn', $infoprojecttrn, "inquiry_id=".$POST['inquiry_id'], $dbcon);

        $updatetask = update_record('tbl_task', $infotask, "inquiry_id=".$POST['inquiry_id'], $dbcon);

        if($updateid)
            echo "1";	
        else
            echo "0";			
    }
	////////////////////////////////////////////////////hrshil - 20-9-2022//////////////////////////////////////
	 else if(strtolower($POST['mode']) == "add_field_list") 
	 {
        $pid= $POST['pid']; 
            
        $inq_qry="select * from tbl_inquiry_trn  where  inquiry_trn_id=".$pid;
        
        $inq_qry_rs=$dbcon->query($inq_qry);

        $inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs);
        
        $inq_unit="select product_base_unit,product_spec,product_spec_id from product_mst  where  product_id=".$POST['product_id'];
        
        $inq_unit_rs=$dbcon->query($inq_unit);

        $inq_rel_unit=brp_mysqli_fetch_assoc($inq_unit_rs);
					
        $company_state = get_company_data($dbcon,$_SESSION['company_id']);

        $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
        $info1['inquiry_type']               = $inq_rel['inquiry_type'];
        $info1['pid']	             			= $POST['pid'];
        $info1['product_id']	             = $POST['product_id'];
		$info1['unitid']	                 = $inq_rel_unit['product_base_unit'];
        $info1['product_qty']	             = $POST['product_qty'];


        $info1['currency_id']                = $inq_rel['currency_id'];
        $info1['currency_rate']              = $inq_rel['currency_rate'];
        if($inq_rel['currency_rate']==$company_state['currency_id']){
            $info1['product_rate']          = $POST['product_rate'];
            $info1['product_amount']        = $POST['product_amount'];
            $info1['product_rate_conv']     = $POST['product_rate']*$inq_rel['currency_rate'];
            $info1['product_amount_conv']   = $POST['product_amount']*$inq_rel['currency_rate'];
        }else{
            $info1['product_rate']          = $POST['product_rate']*$inq_rel['currency_rate'];
            $info1['product_amount']        = $POST['product_amount']*$inq_rel['currency_rate'];
            $info1['product_rate_conv']     = $POST['product_rate'];
            $info1['product_amount_conv']   = $POST['product_amount'];
        }
        
        $info1['product_desc']	= text_rnremove($_POST['product_desc']);
        $info1['product_spec']	= text_rnremove($inq_rel_unit['product_spec']);
		$info1['product_spec_id']=  $inq_rel_unit['product_spec_id'];
       
        if(!empty($POST['edit_id'])){
            $info1['inquiry_trn_status'] = 0;
            $info1['inquiry_id']         = $POST['edit_id'];
        }else{
            $info1['inquiry_trn_status'] = 3;
        }
		
        $info1['user_id']		= $_SESSION['user_id'];
        $info1['company_id']	= $_SESSION['company_id'];
		
		$table='tbl_inquiry_trn';$tableid='inquiry_trn_id';		 
		$inserid = add_record($table, $info1, $dbcon, $branch_id);
	 }
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
    else if(strtolower($POST['mode']) == "add_field") {
        $company_state = get_company_data($dbcon,$_SESSION['company_id']);

        $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
        $info1['inquiry_type']               = $POST['inquiry_type'];
        $info1['product_id']	             = $POST['product_id'];
		//$info1['product_category']			 = $POST['product_category'];
        $info1['cat_id']	                 = $POST['cat_id'];
        $info1['rcat_id']                    = $POST['rcat_id'];
        $info1['pg_id']		                 = $POST['pg_id'];
        $info1['level_id']	                 = $POST['level_id'];
        
        $info1['unitid']	                 = $POST['unitid'];
        $info1['product_qty']	             = $POST['product_qty'];
       
        $info1['product_conv_qty']           = $POST['product_conv_qty'];
        $info1['conv_unit_id']               = $POST['conv_unit_id'];
        $info1['rate_unit']                  = $POST['rate_unit'];

        $info1['currency_id']                = $POST['currency_id'];
        $info1['currency_rate']              = $POST['currency_rate'];
        if($POST['currency_id']==$company_state['currency_id']){
            $info1['product_rate']          = $POST['product_rate'];
            $info1['product_amount']        = $POST['product_amount'];
            $info1['product_rate_conv']     = $POST['product_rate']*$POST['currency_rate'];
            $info1['product_amount_conv']   = $POST['product_amount']*$POST['currency_rate'];
        }else{
            $info1['product_rate']          = $POST['product_rate']*$POST['currency_rate'];
            $info1['product_amount']        = $POST['product_amount']*$POST['currency_rate'];
            $info1['product_rate_conv']     = $POST['product_rate'];
            $info1['product_amount_conv']   = $POST['product_amount'];
        }
        
        $info1['product_desc']	= text_rnremove($_POST['product_desc']);
        $info1['product_spec']	= text_rnremove($_POST['product_spec']);
        if(is_array($_POST['specification'])){
		    $info1['product_spec_id']=  implode(",",$_POST['specification']);
        }else{
            $info1['product_spec_id'] = "";
        }
        $info1['user_id']		= $_SESSION['user_id'];
        $info1['company_id']	= $_SESSION['company_id'];
		
		/*echo "<pre>"; print_r($info1);
			exit;*/

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
			
			/////////////////////////////////////////////////////////////Harshil  - 20-9-2022 - Accessories Product add///////////////////////////////
			
		    if($POST['currency_id']==$company_state['currency_id'])
    		{
    				 
    				$copy_qry="Insert into tbl_inquiry_trn (inquiry_type,product_id,pid,product_desc,product_qty,unitid,product_rate,product_amount,product_rate_conv,product_amount_conv,currency_id,currency_rate,inquiry_trn_status,company_id,user_id) 
    			select ".$POST['inquiry_type'].",tiat.product_id,".$inserid.",tiat.product_desc,tiat.qty,pm.product_base_unit,tiat.acce_rate,tiat.acc_amount,(tiat.acce_rate * ".$POST['currency_rate'].") as product_rate_conv ,(tiat.acc_amount * ".$POST['currency_rate'].") as product_amount_conv ,".$POST['currency_id'].",".$POST['currency_rate'].",3,".$_SESSION['company_id'].",".$_SESSION['user_id']." from tbl_inq_access_trn as tiat left join product_mst as pm on pm.product_id=tiat.product_id where tiat.inq_access_status=3 and tiat.pid=".$POST['product_id']." and tiat.company_id=".$_SESSION['company_id']." and tiat.user_id=".$_SESSION['user_id']."";
    			$copy_qry_rs=$dbcon->query($copy_qry);
    			$deleteid=delete_record('tbl_inq_access_trn', "pid=".$POST['product_id']. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
    		
    		}
    		else
    		{
    				$copy_qry="Insert into tbl_inquiry_trn (inquiry_type,product_id,pid,product_desc,product_qty,unitid,product_rate,product_amount,product_rate_conv,product_amount_conv,currency_id,currency_rate,inquiry_trn_status,company_id,user_id) 
    			select ".$POST['inquiry_type'].",tiat.product_id,".$inserid.",tiat.product_desc,tiat.qty,pm.product_base_unit,(tiat.acce_rate * ".$POST['currency_rate'].") as rate ,(tiat.acc_amount * ".$POST['currency_rate'].") as amount,(tiat.acce_rate) as product_rate_conv ,(tiat.acc_amount) as product_amount_conv ,".$POST['currency_id'].",".$POST['currency_rate'].",3,".$_SESSION['company_id'].",".$_SESSION['user_id']." from tbl_inq_access_trn tiat left join product_mst as pm on pm.product_id=tiat.product_id where tiat.inq_access_status=3 and tiat.pid=".$POST['product_id']." and tiat.company_id=".$_SESSION['company_id']." and tiat.user_id=".$_SESSION['user_id']."";
    			$copy_qry_rs=$dbcon->query($copy_qry);
    			
    			$deleteid=delete_record('tbl_inq_access_trn', "pid=".$POST['product_id']. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
    		
    		}
			
			
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			
			
			
			////////////////////////////////////////////////////////////////////////Harshil - 8-7-2022 ///////////////////////////////////////////////////
			
				   $inq_qry="select * from tbl_project_assigntrn where project_assigntrn_status=0 and project_assign_id=".$POST['product_id'];
					
					$inq_qry_rs=$dbcon->query($inq_qry);

					while($inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs))
					{
						
						
						$t_Qty=($inq_rel['product_qty'] * $POST['product_qty']);
						$t_amount = ($t_Qty * $inq_rel['product_rate']);
						
						$company_state = get_company_data($dbcon,$_SESSION['company_id']);
						
						 $sale_gst = get_tax_cat_by_hsn_id($dbcon,$inq_rel['product_hsn_code']);
						
						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;
						if(($company_state['stateid'] == $POST['cust_stateid']))
						{
							$gst = $sale_gst['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$t_amount)/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$t_amount)/100;
							$t_g_amount=($t_amount+$cgst_tax_rate+$sgst_tax_rate);
						}else
						{
							$igst_tax_per = $sale_gst['tax_gst'];
							$igst_tax_rate = ($sale_gst['tax_gst']*$t_amount)/100;
							$t_g_amount=($t_amount+$igst_tax_rate);
						}
						
						
						
						
						$info12['inquiry_id']			= $POST['inquiry_id'];
						
						if(!empty($POST['inquiry_id'])) 
						{
							$info12['inquiry_id']= $POST['inquiry_id'];
						}
						else
						{
							$info12['inquiry_projecttrn_status']= 3;
						}
						
						
						$info12['inquiry_trn_id']		= $inserid;
						$info12['inquiry_type']			= $POST['inquiry_type'];
						$info12['project_assign_id']		= $POST['product_id'];
						$info12['product_category_id']	= 0;
						$info12['product_id']			= $inq_rel['product_id'];
						$info12['description']			= $inq_rel['description'];
						$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
						$info12['product_qty']			= $t_Qty ;
						$info12['product_rate']			= $inq_rel['product_rate'];
						$info12['product_amount']    	= $t_amount;
						$info12['formulaid']         	= $inq_rel['formulaid'];
						$info12['product_disc']			= $inq_rel['product_disc'];
						$info12['product_spec']			= $inq_rel['product_spec'];
						//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
						//$info12=array_merge($info12,$info);

						$info12['user_id']				= $_SESSION['user_id'];
						$info12['company_id']			= $_SESSION['company_id'];
						$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
						$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
						$info12['product_total']		= $t_g_amount;
						$inserid_sub=add_record("tbl_inquiry_project_trn", $info12, $dbcon, $branch_id);

			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			
			
					}
		}
        else 
		{
            $updateid = update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
            $updateinfo['inquiry_trn_id'] = $updateid; 
			
			$deletpro=delete_record('tbl_inquiry_project_trn',"inquiry_trn_id='".$POST['edit_id']."' and user_id=".$_SESSION['user_id'], $dbcon);

			///////////////////////////////////////////Harshil 4-7-2022////////////////////////////////////////////////////////////////
			
					  $inq_qry="select * from tbl_project_assigntrn where project_assigntrn_status=0 and project_assign_id=".$POST['product_id'];
					
					$inq_qry_rs=$dbcon->query($inq_qry);

					while($inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs))
					{
						
						
						$t_Qty=($inq_rel['product_qty'] * $POST['product_qty']);
						$t_amount = ($t_Qty * $inq_rel['product_rate']);
						
						$company_state = get_company_data($dbcon,$_SESSION['company_id']);
						
						 $sale_gst = get_tax_cat_by_hsn_id($dbcon,$inq_rel['product_hsn_code']);
						
						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;
						if(($company_state['stateid'] == $POST['cust_stateid']))
						{
							$gst = $sale_gst['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$t_amount)/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$t_amount)/100;
							$t_g_amount=($t_amount+$cgst_tax_rate+$sgst_tax_rate);
						}else
						{
							$igst_tax_per = $sale_gst['tax_gst'];
							$igst_tax_rate = ($sale_gst['tax_gst']*$t_amount)/100;
							$t_g_amount=($t_amount+$igst_tax_rate);
						}
						
						
						
						
						$info12['inquiry_id']			= $POST['inquiry_id'];
						
						if(!empty($POST['inquiry_id'])) 
						{
							$info12['inquiry_id']= $POST['inquiry_id'];
						}
						else
						{
							$info12['inquiry_projecttrn_status']= 3;
						}
						
						
						$info12['inquiry_trn_id']		= $POST['edit_id'];
						$info12['inquiry_type']			= $POST['inquiry_type'];
						$info12['project_assign_id']	= $POST['product_id'];
						$info12['product_category_id']	= 0;
						$info12['product_id']			= $inq_rel['product_id'];
						$info12['description']			= $inq_rel['description'];
						$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
						$info12['product_qty']			= $t_Qty ;
						$info12['product_rate']			= $inq_rel['product_rate'];
						$info12['product_amount']    	= $t_amount;
						$info12['formulaid']         	= $inq_rel['formulaid'];
						$info12['product_disc']			= $inq_rel['product_disc'];
						$info12['product_spec']			= $inq_rel['product_spec'];
						//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
						//$info12=array_merge($info12,$info);

						$info12['user_id']				= $_SESSION['user_id'];
						$info12['company_id']			= $_SESSION['company_id'];
						$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
						$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
						$info12['product_total']		= $t_g_amount;
						$inserid_sub=add_record("tbl_inquiry_project_trn", $info12, $dbcon, $branch_id);
			
			
			

            /* if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise' && $POST['old_product_id']!=$POST['product_id']){
                $updatein['inquiry_projecttrn_status'] = 2; 
                update_record('tbl_inquiry_project_trn', $updatein, "inquiry_trn_id=".$POST['edit_id']." and project_assign_id=".$POST['old_product_id'] , $dbcon, $branch_id);
            } */
					}

        /*
            Code By Umair: 23-06-2021
            Comment : Update tbl_inquiry_project_trn inquiry_projecttrn_status status
            START
        */
            /* if(isset($POST['product_attr']) && strtolower($POST['product_attr'])=='projectwise'){
                $updateinfo['inquiry_projecttrn_status'] = 4; 
                update_record('tbl_inquiry_project_trn', $updateinfo, "project_assign_id=".$POST['product_id']." and inquiry_projecttrn_status=3" , $dbcon, $branch_id);
            } */

            /* END */   
		}
	}
        else if(strtolower($POST['mode'])=="show_data") 
		{
            $str='';
            $delete_btn_per=check_permission('crm/inquiry_list',$_SESSION['user_id'],'delete',$dbcon);
            $chkmode=$POST['modee'];
            $products = get_inquiry_products($dbcon, $POST['inquiry_id']);
			// print_r($products);
            $str.='<table class="display table table-bordered table-striped" style="width:110%;">
            <tr>';
            if($chkmode!='VIEW'){ 
                $str.='<th width="5%" class="text-center">Action</th>';
            }
			$getspecialConfiguration=getspecialConfiguration($dbcon);
            $companyConfiguration=getCompanyConfiguration($dbcon);
			if($companyConfiguration['category_selection_active'] ==1){
                $str.='<th width="10%" class="text-center">Product Category</th>';
			}
			 $str.='<th width="15%" class="text-center">Product Name</th>';
           
			if($getspecialConfiguration['reciclar']==1){
                $str.='<th width="10%" class="text-center">Reciclar Category</th>';
            }
            $str.='<!--<th width="10%" class="text-center">Product Group</th>-->
            <!--<th width="2%" class="text-center">Level</th>-->
            <th width="8%" class="text-center">Quantity</th>
            <!--<th width="3%" class="text-center">Unit</th>-->
            <th width="8%" class="text-center">Rate <span class="currency_icon"> </span></th>
            <th width="12%" class="text-center">Amount <span class="currency_icon"> </span></th>				  
            <th width="10%" class="text-center">Specification</th>				  
            </tr>
            <tbody>';
            if($products){
                $i=1;
                foreach ($products as $rel) {

                    if($rel['unitid']===$rel['rate_unit']){
                        $sqty=$rel['product_qty'];
                    }else{
                        $sqty=$rel['product_conv_qty'];
                    }

                    if($rel['unitid'] != $rel['conv_unit_id']){
                        $qty_lb = '<strong style="color:green;">Base Qty</strong> :'.number_format($rel['product_qty'],4,'.','').' '.$rel['base_unit'].'<br><strong style="color:green;">Conv. Qty</strong> :'.number_format($rel['product_conv_qty'],4,'.','').' '.$rel['conv_unit']; 
                    }else{
                        $qty_lb = '<strong style="color:green;">Base Qty</strong> :'.number_format($rel['product_qty'],4,'.','').' '.$rel['base_unit'];
                    }

                    $str.='<tr> ';
                        //echo $chkmode;
                    if($chkmode!='VIEW'){ 
                        $str.='<td style="vertical-align:middle"> 
                        <button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data('.$rel['inquiry_trn_id'].','.$rel['project_wise'].')"><i class="fa fa-pencil"></i></button>';
                        //if($delete_btn_per){
                        $str .= '&nbsp;<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_trn_data('.$rel['inquiry_trn_id'].','.$rel['project_wise'].')">X</button>';
                        //}
						
                        $str .= '</td>';
                    } 

                    if($rel['currency_id']==$company_state['currency_id']){
                        $rel['product_rate']    = $rel['product_rate'];
                        $rel['product_amount']  = $rel['product_amount']; 
                    }else{
                        $rel['product_rate']    = $rel['product_rate_conv'];
                        $rel['product_amount']  = $rel['product_amount_conv'];
                    }


					if($companyConfiguration['category_selection_active'] ==1){
					   $str.='<td style="vertical-align:top;"><strong>'.$rel['cat_name'].'</strong></td>';
					}
                    $str.='<td style="vertical-align:top;">
                    <strong>'.$rel['product_name'].'</strong><br/>
                    <strong>Desc:</strong> '.($rel['product_desc'] ? (nl2br($rel['product_desc'])) : (nl2br($rel['description']))).'
                    </td>';
                    if($getspecialConfiguration['reciclar']==1){
                        $str.='<td style="vertical-align:top;"><strong>'.$rel['pcat_name'].'</strong></td>';
                    }
                    $str.='<!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['cat_name'].'
                    </td>-->
                    <!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['pg_name'].'
                    </td>-->
                    <!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['level_id'].'
                    </td>-->
                    <td style="vertical-align:top;" class="text-left">
                    <strong style="color:green">Rate Qty</strong> :'.number_format($sqty,4,'.','').' '.$rel['rat_unit'].'<br>'.$qty_lb.'
                    </td>
                    <!--<td style="vertical-align:top;" class="text-center">
                    '.$rel['unit_name'].'
                    </td>-->
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
        else if(strtolower($POST['mode'])=="show_data_durva") 
        {
            $str='';
            $delete_btn_per=check_permission('crm/inquiry_list',$_SESSION['user_id'],'delete',$dbcon);
            $chkmode=$POST['modee'];
            $products = get_inquiry_products($dbcon, $POST['inquiry_id']);
            // print_r($products);
            $str.='<table class="display table table-bordered table-striped" style="width:110%;">
            <tr>';
            if($chkmode!='VIEW'){ 
                $str.='<th width="5%" class="text-center">Action</th>';
            }
            $getspecialConfiguration=getspecialConfiguration($dbcon);
            
            $str.='<th width="5%" class="text-center">Sr.no</th>';
            if($getspecialConfiguration['aeon_permission'] ==1){
            $str.='
             <th width="10%" class="text-center">Product Category</th>';
            }
            $str.=' <th width="20%" class="text-center">Product Name</th>
           
            <!--<th width="10%" class="text-center">Product Group</th>-->
            <!--<th width="2%" class="text-center">Level</th>-->
            <th width="5%" class="text-center">Quantity</th>
            <th width="3%" class="text-center">Unit</th>
            <th width="8%" class="text-center">Rate <span class="currency_icon"> </span></th>
            <th width="12%" class="text-center">Amount <span class="currency_icon"> </span></th>                  
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
                        //if($delete_btn_per){
                        $str .= '&nbsp;<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_trn_data('.$rel['inquiry_trn_id'].','.$rel['project_wise'].')">X</button>';
                        //}
						if($getspecialConfiguration['durva_permission']==1){
							if($rel['pid']==0)
							{
						$str .= '&nbsp;<button type="button" class="btn btn-xs btn-primary" data-original-title="Add Accessories" data-toggle="tooltip" data-placement="top" onClick="open_accesorice_wise_product_list('.$rel['inquiry_trn_id'].')">+</button>';
							}
						}
                        $str .= '</td>';
                    } 
                    $str.='<td style="vertical-align:top;">
                        '.$i.'
                    </td>';
                    if($rel['currency_id']==$company_state['currency_id']){
                        $rel['product_rate']    = $rel['product_rate'];
                        $rel['product_amount']  = $rel['product_amount']; 
                    }else{
                        $rel['product_rate']    = $rel['product_rate_conv'];
                        $rel['product_amount']  = $rel['product_amount_conv'];
                    }

                    if($getspecialConfiguration['aeon_permission'] ==1){
                    
                    $str.='<td style="vertical-align:top;"><strong>'.$rel['cat_name'].'</strong></td>';
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

                    if($POST['inquiry_id']){
                        $sub_pro="select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,trn.product_desc as description,trn.product_spec as spec,cat.cat_name,unit.unit_name , hsn.hsn_code as product_hsn_code from tbl_inquiry_trn as trn 
                        left join product_mst as pro on pro.product_id  = trn.product_id 
                        left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
                        left join tbl_category as cat on cat.cat_id=trn.cat_id
                        left join unit_mst as unit on unit.unitid=trn.unitid
                        where trn.inquiry_trn_status=0 and trn.pid=".$rel['inquiry_trn_id']."  and trn.inquiry_id=".$POST['inquiry_id'];
                        /* END */ 
                    }
                    else{
                        $sub_pro="select trn.*,if(trn.project_wise=0,(select product_name from product_mst as pro where pro.product_id=trn.product_id) ,(select product_name from product_mst as pro where pro.product_id=trn.product_id)) as product_name,trn.product_desc as description,trn.product_spec as spec,cat.cat_name,unit.unit_name , hsn.hsn_code as product_hsn_code from tbl_inquiry_trn as trn 
                        left join product_mst as pro on pro.product_id  = trn.product_id 
                        left join mst_hsn_code as hsn on hsn.hsn_id = pro.product_hsn
                        left join tbl_category as cat on cat.cat_id=trn.cat_id
                        left join unit_mst as unit on unit.unitid=trn.unitid
                        where trn.inquiry_trn_status=3 and trn.pid=".$rel['inquiry_trn_id']." and trn.user_id=".$_SESSION['user_id'];
                        /* END */ 
                    }

                    $result_prod = $dbcon->query($sub_pro);
                    $j=1;
                    while($row = brp_mysqli_fetch_array($result_prod)){
                        $str.='<tr> ';
                        //echo $chkmode;
                        if($chkmode!='VIEW'){ 
                            $str.='<td style="vertical-align:middle"> 
                            <button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_trn_data('.$row['inquiry_trn_id'].','.$row['project_wise'].')"><i class="fa fa-pencil"></i></button>';
                            //if($delete_btn_per){
                            $str .= '&nbsp;<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_trn_data('.$row['inquiry_trn_id'].','.$row['project_wise'].')">X</button>';
                            //}
                            $str .= '</td>';
                        } 

                        if($row['currency_id']==$company_state['currency_id']){
                            $row['product_rate']    = $row['product_rate'];
                            $row['product_amount']  = $row['product_amount']; 
                        }else{
                            $row['product_rate']    = $row['product_rate_conv'];
                            $row['product_amount']  = $row['product_amount_conv'];
                        }
                        $str.='<td style="vertical-align:top;">
                            '.$i.'.'.$j.'
                        </td>';
                        if($getspecialConfiguration['aeon_permission'] ==1){
                        
                        $str.='<td style="vertical-align:top;"><strong>'.$row['cat_name'].'</strong></td>';
                        }
                        $str.='
                        <td style="vertical-align:top;">
                        <strong>'.$row['product_name'].'</strong><br/>
                        <strong>Desc:</strong> '.($row['product_desc'] ? (nl2br($row['product_desc'])) : (nl2br($row['description']))).'
                        </td>
                        <!--<td style="vertical-align:top;" class="text-center">
                        '.$row['cat_name'].'
                        </td>-->
                        <!--<td style="vertical-align:top;" class="text-center">
                        '.$row['pg_name'].'
                        </td>-->
                        <!--<td style="vertical-align:top;" class="text-center">
                        '.$row['level_id'].'
                        </td>-->
                        <td style="vertical-align:top;" class="text-center">
                        '.$row['product_qty'].'
                        </td>
                        <td style="vertical-align:top;" class="text-center">
                        '.$row['unit_name'].'
                        </td>
                        <td style="vertical-align:top;" class="text-right">
                        '.$row['product_rate'].'
                        </td>
                        <td style="vertical-align:top;" class="text-right">
                        <input type="hidden" name="amount[]" value="'.$row['product_amount'].'">
                        '.$row['product_amount'].'
                        </td>
                        <td style="vertical-align:top;">
                        '.$row['product_spec'].'
                        </td>   
                        </tr>';
                        $j++;
                    }

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
            $q = $dbcon -> query("SELECT trn.*, pmst.product_name, pmst.parent_category FROM tbl_inquiry_trn as trn left join product_mst as pmst on pmst.product_id=trn.product_id WHERE inquiry_trn_id = '$POST[inquiry_trn_id]'");
            $r = $q->fetch_assoc();

            if(!empty($r['product_spec_id'])){
                $a = explode(",",$r['product_spec_id']);
                $specification= implode(',', array_map('quote', $a));
                $spq= $dbcon -> query("select group_concat(specification_id ORDER BY FIND_IN_SET(specification_name,'".$r['product_spec_id']."')) as spec_id from tbl_specification where specification_name in(".$specification.")");
                $spr = $spq->fetch_assoc();
                //$d = explode(",",$spr['spec_id']);
                //$spr['spec_id'] = implode(',', array_map('quote', $d));
                $r['product_spec_id_id']=$spr['spec_id'];
            }	
           echo json_encode($r);
        }
        else if(strtolower($POST['mode'])== "dataget") {
			$r['res']=get_specification_types($dbcon, $POST['product_spec_id']);
			echo json_encode($r);
	    }
        else if(strtolower($POST['mode'])== "delete_trn_data") {
			$row=array();
			 $flp_qry="select * from tbl_inquiry_trn where pid =".$POST['inquiry_trn_id']." and inquiry_trn_status !=2   ";
				$flp_qry_rs=$dbcon->query($flp_qry);
				if(mysqli_num_rows($flp_qry_rs))
				{
					 $row['res']="2"; 
				}
				else
				{
					
					  $info['inquiry_trn_status']=2;	
					  $info1['inquiry_projecttrn_status']=2;  
					  $updateid=update_record('tbl_inquiry_trn', $info, "inquiry_trn_id=".$POST['inquiry_trn_id'] , $dbcon);
					  $updatprojecteid=update_record('tbl_inquiry_project_trn', $info1, "inquiry_trn_id=".$POST['inquiry_trn_id'] , $dbcon);
					  

						  if($updateid)
						   $row['res']="1";
					   else
						   $row['res']="0";
				}
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
    /*var_dump($_POST);
    var_dump($_FILES);*/
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

    if($inserid){
        echo "1";
    }else{
        echo "0";
    }
}
else if(strtolower($POST['mode'])== "show_inq_attach_data") {
    $chkmode=$POST['modee'];
    $delete_btn_per = in_array(INQUIRY_SLUG_DELETE,$bulkAccessArray);
    if($POST['inquiry_id']){
        $query="select mst.* from tbl_inq_attach as mst 
        where mst.inq_attach_status=0 and mst.inquiry_id=".$POST['inquiry_id'];
    }
    else{
        $query="select mst.* from tbl_inq_attach as mst 
        where mst.inq_attach_status=3 and mst.user_id=".$_SESSION['user_id'];
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
                // if($delete_btn_per){
                    echo '<td style="vertical-align:top"> 
                    <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_inq_attach_data('.$rel['inq_attach_id'].')">X</button>
                    </td>';
                // }
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
	
    $pro_qry="select pm.*,um.unit_name from product_mst as pm left join unit_mst as um on um.unitid=pm.product_base_unit  where pm.product_id=".$POST['product_id'];
    $pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
    $pro_rel['current_stock']=get_current_stock_new($dbcon, $POST['product_id'], $pro_rel['product_base_unit']);

	if($POST['inquiry_type'] ==2)
	{
        $pro_qry_total="SELECT sum(`product_amount`) as total FROM tbl_project_assigntrn WHERE project_assigntrn_status=0 and  project_assign_id=".$POST['product_id'];
		$pro_rel_total=mysqli_fetch_assoc($dbcon->query($pro_qry_total));
		
			$pro_rel['product_sale_rate']=$pro_rel_total['total'];
	}
	else
	{	
        $rate = get_product_rate_sales_time($dbcon, $POST['product_id'], $pro_rel['product_base_unit'],$POST['cust_id']);
         $pro_rel['product_sale_rate']=@$rate['pr_rate'];
	}
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
    left join tbl_refer_by as rb on rb.rb_id = cust.cust_source
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
    <th class="text-center">Assign User</th>
    <th class="text-center">Priority</th>
    <th class="text-center">Status</th>
    <th class="text-center">Remarks</th>
    </tr>';

    $flp_qry="select task.*,type.mcd_name,usr.user_name,prior.task_priority_name,user.user_name as assign_name from tbl_task as task
    left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id
    left join users as usr on usr.user_id=task.user_id
    left join users as user on user.user_id=task.assign_user_ids
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
            <td class="text-left">'.$flp_rel['assign_name'].'</td>
            <td class="text-center">'.$flp_rel['task_priority_name'].'</td>';

            $tsk_type="";
            $tsk_due_time=strtotime($flp_rel['task_due_date']);

            if($flp_rel['task_status']=='1'){ 
                $cur_time=strtotime($flp_rel['task_completion_date']);
                if($tsk_due_time<$cur_time){
                    $tsk_type="<label style='background:#d9534f;'>Completed (Delayed)</label>";
                }
                $str .= '<td class="text-center btn-success">Completed'.$tsk_type.'</td>';
            } else {
                $cur_time = strtotime(date('Y-m-d H:i:s'));
                if($tsk_due_time<$cur_time){
                    $tsk_type="<label style='background:#d9534f;'>(Delayed)</label>";
                }
                $str .= '<td class="text-center btn-warning">Pending'.$tsk_type.'</td>';
            }
            $str.='<td class="text-center">'.cleanString(nl2br($flp_rel['task_remark'])).'</td>';
            $str.='</tr>';
        }
    }
    else{
        $str.='<tr><td colspan="8" class="text-center">NO DATA FOUND!!!</td></tr>';
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
            $str.='<td class="text-center">
                <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_task_flp(' . $row['flp_id'] . ')"><i class="fa fa-trash-o"></i></button>
                </td>';
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
} else if(strtolower($POST['mode'])== "no_of_inquiry"){
    $inq_qry = "select count(*) as no_of_inquiry from tbl_inquiry where inquiry_status = 0 AND company_id = '".$_SESSION['company_id']."' AND stage_prob NOT IN (0,100) and user_id=".$POST['user_id'];
    $inq_count_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
    echo ($inq_count_data) ? $inq_count_data['no_of_inquiry'] : 0;
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
	
	////////////////////////////////////////////////////////////////////////Harshil - 8-7-2022 ///////////////////////////////////////////////////
			
					   $inq_qry="select * from tbl_project_assigntrn where project_assigntrn_status=0 and project_assign_id=".$project_assign_id." and company_id='".$_SESSION['company_id']."'";
					
					$inq_qry_rs=$dbcon->query($inq_qry);
					if(brp_mysqli_num_rows($inq_qry_rs)>0)
				{

					while($inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs))
					{
						
						
						$t_Qty=($inq_rel['product_qty'] * $POST['product_qty']);
						$t_amount = ($t_Qty * $inq_rel['product_rate']);
						
						$company_state = get_company_data($dbcon,$_SESSION['company_id']);
						
						 $sale_gst = get_tax_cat_by_hsn_id($dbcon,$inq_rel['product_hsn_code']);
						
						$cgst_tax_rate=0;
						$sgst_tax_rate=0;
						$igst_tax_rate=0;
						if(($company_state['stateid'] == $POST['cust_stateid']))
						{
							$gst = $sale_gst['tax_gst']/2;
							$cgst_tax_per = $gst;
							$cgst_tax_rate = ($gst*$t_amount)/100;
							$sgst_tax_per = $gst;
							$sgst_tax_rate = ($gst*$t_amount)/100;
							$t_g_amount=($t_amount+$cgst_tax_rate+$sgst_tax_rate);
						}else
						{
							$igst_tax_per = $sale_gst['tax_gst'];
							$igst_tax_rate = ($sale_gst['tax_gst']*$t_amount)/100;
							$t_g_amount=($t_amount+$igst_tax_rate);
						}
						
						
						
						
						$info12['inquiry_id']			= $POST['inquiry_id'];
						
						if(!empty($POST['inquiry_id'])) 
						{
							$info12['inquiry_id']= $POST['inquiry_id'];
						}
						else
						{
							$info12['inquiry_projecttrn_status']= 3;
						}
						
						
						$info12['inquiry_trn_id']		= $inserid;
						$info12['inquiry_type']			= $POST['inquiry_type'];
						$info12['project_assign_id']		= $POST['product_id'];
						$info12['product_category_id']	= 0;
						$info12['product_id']			= $inq_rel['product_id'];
						$info12['description']			= $inq_rel['description'];
						$info12['product_hsn_code']		= $inq_rel['product_hsn_code'];
						$info12['product_qty']			= $inq_rel['product_qty'] ;
						$info12['product_rate']			= $inq_rel['product_rate'];
						$info12['product_amount']    	= $t_amount;
						$info12['formulaid']         	= $inq_rel['formulaid'];
						$info12['product_disc']			= $inq_rel['product_disc'];
						$info12['product_spec']			= $inq_rel['product_spec'];
						//$info=get_product_common_tax($dbcon,$t_amount,$info12['formulaid']);
						//$info12=array_merge($info12,$info);

						$info12['user_id']				= $_SESSION['user_id'];
						$info12['company_id']			= $_SESSION['company_id'];
						$info12['cgst_tax_per']			= isset($cgst_tax_per) ? $cgst_tax_per : 0 ;
						$info12['cgst_tax_rate']		= isset($cgst_tax_rate) ? $cgst_tax_rate : 0 ;
						$info12['sgst_tax_per']			= isset($sgst_tax_per) ? $sgst_tax_per : 0 ;
						$info12['sgst_tax_rate']		= isset($sgst_tax_rate) ? $sgst_tax_rate : 0 ;
						$info12['igst_tax_per']			= isset($igst_tax_per) ? $igst_tax_per : 0 ;
						$info12['igst_tax_rate']		= isset($igst_tax_rate) ? $igst_tax_rate : 0 ;
						$info12['product_total']		= $t_g_amount;
						$inserid_sub=add_record("tbl_inquiry_project_trn", $info12, $dbcon, $branch_id);
					}
				}
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
}
///////////////////////////////////////////////////////////////////////////////////harshil - 19-9-2022///////////////////////////////////////////////////
else if(strtolower($POST['mode'])== "add_accessories_data"){
   // $inquiry_id = $POST['eid'];
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    //if($inquiry_id==''){
    $product_id = $POST['product_id'];
    $inquiry_type = $POST['inquiry_type'];

    //$update['inq_access_status'] = 2;
    //update_record('tbl_inq_access_trn', $update, "pid=".$product_id. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
	
	$deleteid=delete_record('tbl_inq_access_trn', "pid=".$product_id. " and inq_access_status = 3 and user_id='".$_SESSION['user_id']."' and company_id='".$_SESSION['company_id']."'", $dbcon);
			
    $inq_qry="select tpap.*,pm.product_sale_rate from tbl_product_acc_product tpap left join product_mst as pm on  pm.product_id = tpap.acc_product_id  where tpap.product_id=".$product_id." and tpap.company_id='".$_SESSION['company_id']."'";
					
	$inq_qry_rs=$dbcon->query($inq_qry);
	if(brp_mysqli_num_rows($inq_qry_rs)>0)
	{
        while($inq_rel=brp_mysqli_fetch_assoc($inq_qry_rs))
		{
			$info12['product_id']		= $inq_rel['acc_product_id'];
    		$info12['pid']				= $inq_rel['product_id'];
    		$info12['qty']				= $inq_rel['acc_product_qty'];
    		$info12['acce_rate']		= $inq_rel['product_sale_rate'];
    		
    		if(!empty($inq_rel['product_sale_rate']))
    		{
    			$info12['acc_amount']		= $inq_rel['product_sale_rate'] * $inq_rel['acc_product_qty'] ;
    		}
    		else
    		{
    			$info12['acc_amount']		= 0;
    		}	
    		$info12['product_desc']		= $inq_rel['acc_product_desc'];
    		$info12['inq_access_status']= 3;
    		$info12['company_id']		= $_SESSION['company_id'];
    		$info12['user_id']			= $_SESSION['user_id'];
    		//var_dump($info12);
    		$inserid_sub=add_record(" tbl_inq_access_trn", $info12, $dbcon, $branch_id);
    	}
    }
}
////////////////////////////////////////////////////////////////////Harshil - 19-9-2022////////////////////////////////////////////////////////////
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
    echo '</tr>';
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
            
            $i++;
        }
    }
    else{
        echo '<tr><td colspan="8" class="text-center">NO DATA FOUND</td></tr>';
    }
    echo '</table></div></div>';
}
else if(brp_strtolower($POST['mode']) == "load_accessories_tempoutward") {

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
    echo '</tr>';
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
	//echo "select mst.*,pro.product_name from tbl_inquiry_project_trn as mst left join tbl_product as pro on mst.product_id=pro.product_id where inquiry_projecttrn_id = '$POST[id]'";
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
    $row=get_product_history($dbcon, $_POST['cust_id'], $_POST['product_id'],"", 3);
    echo $row;
}
/* END */
else if(strtolower($POST['mode']) == "getrate") {
    $qry = $dbcon->query("SELECT trn.* FROM tbl_salescardtrn as trn LEFT JOIN tbl_product_party_sales as sales ON sales.party_sales_id = trn.party_sales_id WHERE trn.salescardtrn_status= 0 AND sales.is_aproove = 1 AND sales.is_active = 0 AND trn.product_id = ".$POST['product_id']." AND trn.affected_date<='".date("Y-m-d")."' AND trn.valid_date>='".date("Y-m-d")."' AND trn.company_id = ".$_SESSION['company_id']);
    $re_po = brp_mysqli_fetch_assoc($qry);

    $pro_de = get_product_detail($dbcon,$POST['product_id']);

    $chksales = $dbcon->query("SELECT * FROM tbl_salescardelcontrn WHERE salescardelcontrn_id = ".$re_po['salescardelcontrn_id']);
    $getsales = brp_mysqli_fetch_assoc($chksales);

    $pr_rate = 0;$disc=0;
    if($pro_de['product_base_unit']==$POST['unit_id']){
        if($pro_de['product_base_unit']==$re_po['unit_id']){
                if(!empty($re_po['discount_percentage'])){
                    $disc = $re_po['price']*$re_po['discount_percentage']/100;
                    $pr_rate=($re_po['price'] - $disc);
                }else{  
                    $pr_rate=$re_po['price'];
                }
            }else if($pro_de['product_conv_unit']==$re_po['unit_id']){
                $prc = $re_po['price'] - $getsales['rate1'] - $getsales['rate2'] - $getsales['rate3'];
                $prcs = ($prc * $pro_de['base_weight'] / $pro_de['conv_weight']) + $getsales['rate1'] + $getsales['rate2'] + $getsales['rate3'];
                if(!empty($re_po['discount_percentage'])){
                    $disc = $prcs*$re_po['discount_percentage']/100;
                    $pr_rate=($prcs - $disc);
                }else{  
                    $pr_rate=$prcs;
                }
            } else{
                $pr_rate=$pro_de['product_sale_rate'];
            }
    }else if($pro_de['product_conv_unit']==$POST['unit_id']){
        if($pro_de['product_conv_unit']==$re_po['unit_id']){
                if(!empty($re_po['discount_percentage'])){
                    $disc = $re_po['price']*$re_po['discount_percentage']/100;
                    $pr_rate=($re_po['price'] - $disc);
                }else{  
                    $pr_rate=$re_po['price'];
                }
            }else if($pro_de['product_base_unit']==$re_po['unit_id']){
                $prc = $re_po['price'] - $getsales['rate1'] - $getsales['rate2'] - $getsales['rate3'];
                $prcs = ($prc * $pro_de['conv_weight'] / $pro_de['base_weight']) + $getsales['rate1'] + $getsales['rate2'] + $getsales['rate3'];
                if(!empty($re_po['discount_percentage'])){
                    $disc = $prcs*$re_po['discount_percentage']/100;
                    $pr_rate=($prcs - $disc);
                }else{  
                    $pr_rate=$prcs;
                }
            } else{
                $pr_rate=$pro_de['product_sale_rate'];
            }
    } else{
        $pr_rate=$pro_de['product_sale_rate'];
    }
    $row['price'] = $pr_rate;
    echo json_encode($row);
}
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

else if(strtolower($POST['mode']) == "ac_edit") {
    $info['type_of_inquiry']    = $POST['type_of_inquiry'];
    $info['project_name']   = $_POST['inquiry_project_name'];
    $info['inquiry_project_name']   = $_POST['inquiry_project_name'];
    $info['end_user_details']   = $POST['end_user_details'];
    $info['scope_of_work']  = $POST['scope_of_work'];
    $info['payment_terms']  = $POST['payment_terms'];
    $info['delivery_time']  = date('Y-m-d H:i:s',strtotime($POST['delivery_time']));
    $info['estimated_timeline_for_closing'] = $POST['estimated_timeline_for_closing'];
    $info['quotation_required_date']    = date('Y-m-d H:i:s',strtotime($POST['quotation_required_date']));
    $info['cdate']  = date("Y-m-d H:i:s");

    $updateid=update_record("tbl_inquiry", $info,"inquiry_id"."=".$POST['ac_inq_id'] , $dbcon);

    if($updateid){
        $arr['msg'] = 1;
    }else{
        $arr['msg'] = 0;
    }
    echo json_encode($arr);
}

else if(strtolower($POST['mode']) == "acknowledge_detail") {
    $q = "select * from tbl_inquiry where inquiry_id=".$POST['inquiry_id'];
    $result=$dbcon->query($q);
    $row = brp_mysqli_fetch_array($result);

    $row['delivery_time']           = date('d-m-Y H:i A',strtotime($row['delivery_time']));
    $row['quotation_required_date'] = date('d-m-Y H:i A',strtotime($row['quotation_required_date']));
    
    if($row['delivery_time'] == '01-01-1970 05:30 AM'){
        $row['delivery_time']       = date('d-m-Y H:i A'); 
    }

    if($row['quotation_required_date'] == '01-01-1970 05:30 AM'){
        $row['quotation_required_date'] = date('d-m-Y H:i A');
    }
    echo json_encode($row);
}

else if(strtolower($POST['mode']) == "load_attach_document") {
    $appData = array();
    $i=1;
    $where='';
    if($POST['inquiry_id']){
        $where = ' and attach.inquiry_id='.$POST['inquiry_id'];
    }
    // if($branch_id){
    //     $where .= check_branch('opportun',$branch_id);
    // }
    $aColumns = array('attach.inq_attach_id', 'attach.inquiry_id','attach.inq_attch_doc_name','attach.inq_attch_file');
    $sIndexColumn = "attach.inq_attach_id";
    $isWhere = array("attach.inq_attach_status=0 and attach.company_id in (0,$_SESSION[company_id])".$where);
    $sTable = "tbl_inq_attach as attach";            
    $isJOIN = array('');
    $hOrder = "attach.inq_attach_id desc";
    include('../../../include/pagging.php');
    $appData = array();
    $id=1;
    foreach($sqlReturn as $row) {
        $row_data = array();
        $row_data[] = $row['sr'];
        $row_data[] = $row['inq_attch_doc_name']; 
        $row_data[] = '<a href="'.ROOT.INQ_ATTACH_VWING.$row['inq_attch_file'].'" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>'; 
    
        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode( $output );
}

else if(strtolower($POST['mode']) == "check_product_entry") {
    if($POST['inquiry_id']){
        $query = "select * from tbl_inquiry_trn where pid=0 and inquiry_trn_status=0 and inquiry_id=".$POST['inquiry_id'];
        
        $query1 = "select * from tbl_inquiry_trn where pid=0 and inquiry_trn_status=0 and inquiry_id=".$POST['inquiry_id']." ORDER BY inquiry_trn_id desc limit 1";
    }else{
        $query = "select * from tbl_inquiry_trn where pid=0 and inquiry_trn_status=3 and user_id=".$_SESSION['user_id'];

        $query1 = "select * from tbl_inquiry_trn where pid=0 and inquiry_trn_status=3 and user_id=".$_SESSION['user_id']." ORDER BY inquiry_trn_id desc limit 1";
    } 
    $result = $dbcon->query($query);
    $result1 = $dbcon->query($query1);
    $cnt = brp_mysqli_num_rows($result);
    $row = brp_mysqli_fetch_array($result1);

    $row['cnt'] = $cnt;

    echo json_encode($row);

}
/////////////////////////////////////////////////////////////////////Harshil - 19-9-2022//////////////////////////////////////////////////////////////
else if(strtolower($POST['mode'])== "accessories_model_open")
{

		

			$html = '
			 <input type="hidden" id="pid" value='.$POST['product_id'].' />
			<div class="row">
                <div class="col-md-12 margin_row">
                    <table class="table table-bordered">
                        <tr>
                            <th>Accessories Product Name</th>
							<th>Qty</th>
							<th>Rate</th>
							<th>Total</th>
							<td>Action</td>
                        </tr>
                        <tr>
                            <td>
                                <input id="acc_product_id" name="acc_product_id" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls_pop(this.value);get_hsn_pop(this.value);" />
								<br><label id="current_stock_pop" style="display: none;"></label><strong class="hsncode_pop" style="display:none;color:blue">HSN Code : <span id="hsncode_pop"></span></strong><br>
                            </td>
							 <td>
                                <input type="text" class="form-control" name="acc_product_qty" onkeyup="get_amount_pop();" id="acc_product_qty" placeholder="QTY" />
								<strong class="unit_pop" style="display:none;color:blue"><span id="unit_pop"></span></strong>
								
                            </td>
							 <td>
                                <input type="text" class="form-control" name="acce_rate" onkeyup="get_amount_pop();" id="acce_rate" placeholder="Rate" />
                            </td>
							<td>
                                <input type="text" class="form-control" name="acc_amount" id="acc_amount" placeholder="Total" />
                            </td>
							<td rowspan="2"><input type="button" class="btn btn-primary" value="ADD" onclick="add_accessories_product_pop()" id="add_alternative_btn" /></td>
                            <input type="hidden" id="edit_id_accessories" value="" />
                            <input type="hidden" id="eid_accessories" value="" />
							</tr>
							<tr>
							<td colspan="4">
							 <div class="form-group">
								<label for="Product Description" class="col-md-4 control-label">Description</label>
								<div class="col-md-12 col-xs-11">
								<textarea class="form-control" id="acc_product_desc" name="acc_product_desc" placeholder="Enter Product Description"></textarea>
								</div>
							</div>
							</td>
							</tr>
							
                    </table>
                </div>
            </div>';
			$row['html_data'] = $html;
			echo json_encode($row);
		}
		
		else if(strtolower($POST['mode'])== "open_accesorice_wise_product_list")
{
$html = '
			 <input type="hidden" id="pid_l" value='.$POST['product_id'].' />
			<div class="row">
                <div class="col-md-12 margin_row">
                    <table class="table table-bordered">
                        <tr>
                            <th>Accessories Product Nameeee</th>
							<th>Qty</th>
							<th>Rate</th>
							<th>Total</th>
							
                        </tr>
                        <tr>
                            <td>
                                <input id="acc_product_id_l" name="acc_product_id_l" style="width:100%;" placeholder="Select Product" onchange="load_product_dtls_pop_list(this.value);get_hsn_pop_list(this.value);" />
								<br><label id="current_stock_pop_l" style="display: none;"></label><strong class="hsncode_pop_l" style="display:none;color:blue">HSN Code : <span id="hsncode_pop_l"></span></strong><br>
                            </td>
							 <td>
                                <input type="text" class="form-control" name="acc_product_qty_l" id="acc_product_qty_l" onkeyup="get_amount_pop_list();" placeholder="QTY" />
								<strong class="unit_pop_l" style="display:none;color:blue"><span id="unit_pop_l"></span></strong>
                            </td>
							 <td>
                                <input type="text" class="form-control" name="acce_rate_l" id="acce_rate_l" onkeyup="get_amount_pop_list();" placeholder="Rate" />
                            </td>
							<td>
                                <input type="text" class="form-control" name="acc_amount_l" id="acc_amount_l" placeholder="Total" />
                            </td>
							
							</tr>
							<tr>
							<td colspan="4">
							 <div class="form-group">
								<label for="Product Description" class="col-md-4 control-label">Description</label>
								<div class="col-md-12 col-xs-11">
								<textarea class="form-control" id="acc_product_desc_l" name="acc_product_desc_l" placeholder="Enter Product Description"></textarea>
								</div>
							</div>
							</td>
							</tr>
							
                    </table>
                </div>
            </div>';
			$row['html_data'] = $html;
			echo json_encode($row);
}
		
		else if(strtolower($POST['mode'])== "fetch_accessories_qty")
		{
			
			
			$appData = array();
			$i=1;
			$aColumns = array('tpm.product_name','tiat.inq_acc_id','tiat.product_id','tiat.pid','tiat.qty','tiat.acce_rate','tiat.acc_amount','tiat.product_desc');
			$sTable = "tbl_inq_access_trn as tiat";			
			$isJOIN = array('left join product_mst as tpm on tpm.product_id=tiat.product_id');
			$sIndexColumn = "tiat.inq_acc_id";
			$where = "  tiat.pid='".$POST['product_id']."' and tiat.inq_access_status=3 ";
			$isWhere = array($where);
			$hOrder = "tiat.inq_acc_id desc";
			include($path.'include/pagging.php');
			$id=1;
			$edit = $delete = '';
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['product_name'];
				$row_data[] = $row['qty'];
				$row_data[] = $row['acce_rate'];
				$row_data[] = $row['acc_amount'];
				$row_data[] = $row['product_desc'];
				
				
					$edit='<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_accessories_product_pop('.$row['inq_acc_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>';	
					$delete='<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_accessories_product_pop('.$row['inq_acc_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>';	
				
				
				$row_data[] = $edit.' '.$delete;
				

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		
	else if(strtolower($POST['mode']) == "add_accessories_product_pop") {
			
			$info1['product_id']		= $POST['acc_product_id'];
			$info1['pid']				= $POST['pid'];		
			$info1['qty']				= $POST['acc_product_qty'];
			$info1['acce_rate']			= $POST['acce_rate'];
			$info1['acc_amount']		= $POST['acc_amount'];					
			$info1['product_desc']		= text_rnremove($_POST['acc_product_desc']);
			$info1['inq_access_status']	= 3;
			$info1['cdate'] 				= date("Y-m-d H:i:s");
			$info1['user_id']				= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']				= $POST['branchid'];
			//var_dump($info1);
			$table='tbl_inq_access_trn';$tableid='inq_acc_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}


else if(strtolower($POST['mode'])== "preedit_accessories_product")
		{
			$q = $dbcon -> query("SELECT tpap.*,pm.product_name FROM tbl_inq_access_trn as tpap left join product_mst as pm on pm.product_id=tpap.product_id WHERE inq_acc_id= '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}

else if(strtolower($POST['mode'])== "delete_data_alternative_product_pop")
		{
			 
			
			$deleteid=delete_record('tbl_inq_access_trn', "inq_acc_id=".$POST['eid'], $dbcon);
			

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
    else if(strtolower($POST['mode']) == "hard_delete") {
        
        
        $info['inquiry_status'] = 2;
        $infotrn['inquiry_trn_status']  = 2;
        $infoprojecttrn['inquiry_projecttrn_status']  = 2;
        $infotask['task_status']  = 2;
        $updateid = update_record('tbl_inquiry', $info, "inquiry_id=".$POST['inquiry_id'], $dbcon);
        $updatetrnid = update_record('tbl_inquiry_trn', $infotrn, "inquiry_id=".$POST['inquiry_id'], $dbcon);
        $updateprojecttrnid = update_record('tbl_inquiry_project_trn', $infoprojecttrn, "inquiry_id=".$POST['inquiry_id'], $dbcon);

        $updatetask = update_record('tbl_task', $infotask, "inquiry_id=".$POST['inquiry_id'], $dbcon);

        $query1="select quotation_id from tbl_quotation where quotation_status=0 and inquiry_id=".$POST['inquiry_id'];
        $result2=$dbcon->query($query1);
        while($rows=mysqli_fetch_assoc($result2)){
            $infoqut['quotation_status'] = 2;
            $infoquttrn['quot_trn_status'] = 2;
            $updateidquot = update_record('tbl_quotation', $infoqut, "quotation_id=".$rows['quotation_id'], $dbcon);
            $updateidquottrn = update_record('tbl_quotation_trn', $infoquttrn, "quotation_id=".$rows['quotation_id'], $dbcon);
        }

        if($updateid)
            echo "1";   
        else
            echo "0";           
    }		
//Maulik Code Start
    else if(strtolower($POST['mode'])== "load_product_unit")
    {
        $query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
            left join unit_mst as umst on umst.unitid=promst.product_base_unit
            left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
            WHERE product_id=".$POST['product_id'];
        //var_dump($POST);
        $rs_type1=$dbcon->query($query1);
        $row1=brp_mysqli_fetch_assoc($rs_type1);
            $rate_unit = "";
            if($POST['rate_unit']){
                $rate_unit = $POST['rate_unit'];
            }
            if($row1['product_base_unit']!=$row1['product_conv_unit']){
                $row1['unit_status']="1";
                $base_sel = "";$conv_sel="";
                if(empty($POST['edit_id'])){
                    if($row1['product_base_unit']==$POST['rate_unit']){
                        $base_sel="selected=='selected'";
                    }
                    if($row1['product_conv_unit']==$POST['rate_unit']){
                        $conv_sel="selected=='selected'";
                    }
                }else{
                    $query_de = "select * from tbl_purchaseordertrn where purchaseordertrn_id=".$POST['edit_id'];
                    $exe = $dbcon->query($query_de);
                    $del_ro = brp_mysqli_fetch_array($exe);

                    if($row1['product_base_unit']==$del_ro['unit_wise']){
                        $base_sel="selected=='selected'";
                    }

                    if($row1['product_conv_unit']==$del_ro['unit_wise']){
                        $conv_sel="selected=='selected'";
                    }
                }
                

                $opt='<option '.$base_sel.' value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
                $opt .='<option '.$conv_sel.' value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
            }else{
                $row1['unit_status']="0";
                $opt.='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
            }
            //echo $opt;
            $row1['unit_option']=$opt;
            //$row1['qye']=$query1;
        //var_dump($row1);
        echo json_encode($row1);
    }
    else if(strtolower($POST['mode'])== "convert_qty")
    {
        //var_dump($POST);
        $row=array();
        if($POST["type"]=="1"){
            $type="base_unit";
            $ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
        }else if($POST["type"]=="2"){
            $type="conv_unit";
            $ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
        }else{
            $ret_qty="0";
        }
            //var_dump($ret_qty);
        $ret_qty_new=number_format($ret_qty, 4, ".", "");
                //$ret_qty=$ret_qty;
            //  echo $ret_qty;
        $row['show_qty']=$ret_qty_new;
        $row['hide_qty']=$ret_qty;
        echo json_encode($row);
    }
    else if(brp_strtolower($POST['mode'])=='load_parent_cat'){
        $html='';
        $query = "select * from tbl_category where cat_status=0 and cat_pid=".$POST['parent_id'];
        $result = $dbcon->query($query);
        $html.='<option value="">Choose Category</option>';
        while($row = brp_mysqli_fetch_array($result)){
            $html .= '<option value="'.$row['cat_id'].'">'.$row['cat_name'].'</option>';
        }
        echo $html;
    } else if(strtolower($POST['mode']) == "check_data") {
		// JS : File import for inquiry product data
        $row[] ='';
        if(!empty($_FILES['excel_file']['tmp_name']))
        {
            $file_name = $_FILES['excel_file']['name'];
            $err = $_FILES["excel_file"]["tmp_name"];
            $exts = array('csv'); 
            if(in_array(end(explode('.', $file_name)), $exts))
            {
                move_uploaded_file($err,INQUIRY_PRODUCT_FILE_UPING.$file_name);
                $handle = fopen(INQUIRY_PRODUCT_FILE_UPING.$file_name, "r");
                $row = check_data($file_name,$dbcon);
                if ($row['res'] == 1) {
                    $row = import_data($file_name,$dbcon,$POST);
                }
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

    // JS : Check File import for inquiry product data
    function check_data($filename,$dbcon)
    {	
        $error=array();
        $arr = explode(".", $filename);
        $fp = fopen(INQUIRY_PRODUCT_FILE_UPING.$filename, 'r');
        $frow = fgetcsv($fp);
        $fikecount=count($frow);
        
        if($fikecount>=4) // Define coulmn count Here
        { 
            $hname=array('Product Name','Unit','Qty','Rate');
            $fikecount=$fikecount-4;
            if($fikecount>0){
                for ($x = 1; $x <= $fikecount; $x++) {
                array_push($hname,"");
                }
            }
            $msg='';
            foreach($frow as $i)  {
                
                if ( !in_array($i,$hname, true ) ) 
                {
                    $msg='error';
                }
            }
            if(!empty($msg))
            {
                $error['res']="3";
            }
            else
            {
                $error['res']="1";
            }
        } else {
            $error['res']="0";
        }
        return $error;
    }

    // JS : Get File import for inquiry product data
    function import_data($file_name,$dbcon,$post) {

        if(!empty($file_name))
        {
            $handle = fopen(INQUIRY_PRODUCT_FILE_UPING.$file_name, "r");
            ($data = fgetcsv($handle,","));//get field rows
            $i=1;
            $error_array=array();
            $perent_id=0;$main_id=0;
            $error_products = [];
            $currect_products = [];
            $batch_data_arr = [];
            $is_exist_prod_arr = [];
            $error_products_flag = false;
            $str = "<table class='table'>";
            $str .= "<tr><th>Product Name</th><th>Unit</th><th>Qty</th><th>Rate</th></tr>";
            while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
            {
                $error='';
                $fikecount1=count($data);
                //$fikecount1=$fikecount1-4;
                
                if(!empty($data['0']))
                {
                    $csv_product_name	= $data['0'];
                    $csv_unit		= $data['1'];
                    $csv_qty		= $data['2'];
                    $csv_rate		= $data['3'];

                    $products_details = array('product_name' => $csv_product_name,"unit" => $csv_unit, "qty" => $csv_qty, "rate" => $csv_rate);

                    $errors = [];					
                    $error_flag = false;
                    if(!empty($csv_product_name)){
                       
                        $qstate="SELECT product_id,product_name FROM product_mst WHERE product_status=0 and trim(product_name) ='".$csv_product_name."'";
                        $tr_state = brp_mysqli_fetch_array($dbcon -> query($qstate));
                        if(!empty($tr_state))
                        {

                            $csv_product_id = $tr_state['product_id'];
                            
                            if (empty($csv_qty) || $csv_qty == 0) {
                                $error_flag = true;
                                $errors["qty"] = "Qty is not zero or blank"; 
                                $errors["qty_color"] = "color:red;";
                            }

                            if (empty($csv_rate) || $csv_rate == 0) {
                                $error_flag = true;
                                $errors["rate"] = "Rate is not zero or blank"; 
                                $errors["rate_color"] = "color:red;";
                            }

                            // Get product units
                            $product_units = get_import_product_unit($dbcon, $csv_product_id);
                            $unitid = $product_base_unit = $product_units["product_base_unit"];
                            $conv_unit_id = $product_conv_unit = $product_units["product_conv_unit"];
                            $product_rate_conv = $product_rate = $csv_rate;
                            $rate_unit = $product_base_unit;

                            // Product Qty, convert qty, product amount and product conv amount
                            if ($product_units["base_unit_name"] == $csv_unit) {   
                                $product_conv_qty = get_convert_qty($dbcon, $csv_product_id, $product_base_unit, $csv_qty);
                                $product_qty = $csv_qty;
                                $product_amount_conv = $product_amount = ($csv_rate * $product_qty);
                            } else if ($product_units["convert_unit_name"] == $csv_unit) {
                                // $rate_unit = $product_conv_unit;
                                $product_conv_qty = $csv_qty;
                                $product_qty = get_convert_qty($dbcon, $csv_product_id, $product_conv_unit, $csv_qty);
                                $product_amount_conv = $product_amount = ($csv_rate * $product_qty);
                            } else {
                                $error_flag = true;
                                $errors["unit"] = "Unit not valid"; 
                                $errors["unit_color"] = "color:red;";
                                $products_details['unit'] = $csv_unit ? $csv_unit : "N/A";
                            }                      

                            if (!$is_exist_prod_arr[$csv_product_id]) {
                                $is_exist_prod_arr[$csv_product_id] = true;

                                if (!$error_flag) {
                                    $products_details['product_id']	 = $csv_product_id;
                                    $products_details['product_qty']	 = $product_qty;
                                    $products_details['unitid']	 = $unitid; 

                                    $products_details['product_conv_qty'] = $product_conv_qty;
                                    $products_details['conv_unit_id'] = $conv_unit_id; 
                                    $products_details['rate_unit']	  = $rate_unit; 
                                    $products_details['product_rate'] = $product_rate; 

                                    $products_details['product_amount']		= $product_amount;

                                    $products_details['inquiry_trn_status']	= 3;
                                    $products_details['inquiry_id']	= 0;
                                    if ($post['inquiry_id']) {
                                        $products_details['inquiry_trn_status']	= 0;
                                        $products_details['inquiry_id']	= $post['inquiry_id'];
                                    }
                                    
                                    
                                    $products_details['product_rate_conv']	 = $product_rate_conv;                                     
                                    $products_details['product_amount_conv'] = $product_amount_conv;

                                    $products_details['currency_id'] = '68';
                                    $products_details['currency_rate'] = '1.00';
                                    
                                    $currect_products[] = $products_details;
                                }
                            }
                        } else {
                            $error_flag = true;
                            $errors["product"] = "Product Not Found";
                            $errors["product_color"] = "color:red;";
                        }
                    } else {
                        $errors["product"] = 'Product Name Not Add In Excel File';
                        $errors["product_color"] = "color:red;";
                        $products_details['product_name'] = "NA";
                        $error_flag = true;
                    }

                    if ($error_flag) {
                        $str .= "<tr><td style='".$errors["product_color"]."'>".$products_details['product_name']."</td><td style='".$errors["unit_color"]."'>".$products_details['unit']."</td><td style='".$errors["qty_color"]."'>".$products_details['qty']."</td><td style='".$errors["rate_color"]."'>".$products_details['rate']."</td></tr>";
                        $error_products_flag = true;
                    }
                }
                $i++;	
            }
            $str .= "</table>";
            if ($currect_products) {
                $result["response"] = inquiry_product_data_save($currect_products,$dbcon);
            }
            $result['res']='1';
            $result['error_flag']=$error_products_flag;
            $result['error_list_data']=$str;

            fclose($handle);//close file reading
        } else {
            $result['res']='0';
            $result['error_flag']=false;
        }
        return $result;
    }

    // JS : Save File import for inquiry product data
    function inquiry_product_data_save($inquiry_product_data, $dbcon) {

        $inquiry_type = 6;
        $inquiry_trn_status = 3;
        $currency_id = 68;
        $currency_rate = '1.0';

        
        /* Add User TRN Data Start */
        $inq_product_data = [];
        foreach ($inquiry_product_data as $key => $inq_prod) 
        {
            $inquiry_id = $inq_prod['inquiry_id'];
            $product_id = $inq_prod['product_id'];
            $product_qty = $inq_prod['product_qty'];
            $unitid = $inq_prod['unitid'];
            $product_conv_qty = $inq_prod['product_conv_qty'];
            $conv_unit_id = $inq_prod['conv_unit_id'];
            $rate_unit = $inq_prod['rate_unit'];
            $product_rate = $inq_prod['product_rate'];
            $product_amount = $inq_prod['product_amount'];
            $inquiry_trn_status = $inq_prod['inquiry_trn_status'];
            $product_rate_conv = $inq_prod['product_rate_conv'];
            $product_amount_conv = $inq_prod['product_amount_conv'];
            $currency_id = $inq_prod['currency_id'];
            $currency_rate = $inq_prod['currency_rate'];
            $branch_id = $_SESSION['branch_id'];
            $user_id = $_SESSION['user_id'];
            $company_id = $_SESSION['company_id'];
            
            $inq_product_data[] = "('" . $inquiry_id . "','" . $inquiry_type . "','" . $product_id . "', '" . $product_qty . "', '" . $unitid . "', '" . $product_conv_qty . "', '" . $conv_unit_id . "', '" . $rate_unit . "', '" . $product_rate . "', '" . $product_amount . "', '" . $inquiry_trn_status . "', '" . $product_rate_conv . "', '" . $product_amount_conv . "', '" . $currency_id . "', '" . $currency_rate . "', '" . $branch_id . "', '" . $user_id . "', '" . $company_id . "')";
        }
        
        $usrtrn_columns = "inquiry_id, inquiry_type, product_id, product_qty, unitid, product_conv_qty, conv_unit_id, rate_unit, product_rate, product_amount, inquiry_trn_status, product_rate_conv, product_amount_conv, currency_id, currency_rate, branch_id, user_id, company_id";
        
        $inserid = bulk_add_record('tbl_inquiry_trn',$inq_product_data, $usrtrn_columns, $dbcon);
    
        if($inserid){
            return 1;
        }else{
            return 0;
        }
    }


    function get_product_details($product_name, $dbcon) {
        $pro_qry = "select pm.*,um.unit_name from product_mst as pm left join unit_mst as um on um.unitid=pm.product_base_unit  where pm.product_name='".$product_name."'";

        $pro_rel = mysqli_fetch_assoc($dbcon->query($pro_qry));
        if(!empty($pro_rel))
        {
            $product_id=$pro_rel['product_id'];
            

            // $qstate="SELECT `product_id`,`product_name` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$csv_product_name."'";

            $pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
            $pro_rel['current_stock']=get_current_stock_new($dbcon, $product_id, $pro_rel['product_base_unit']);

            $inquiry_type = 6;

            if($inquiry_type ==2)
            {
                $pro_qry_total="SELECT sum(`product_amount`) as total FROM tbl_project_assigntrn WHERE project_assigntrn_status=0 and  project_assign_id=".$product_id;
                $pro_rel_total=mysqli_fetch_assoc($dbcon->query($pro_qry_total));
                
                $pro_rel['product_sale_rate']=$pro_rel_total['total'];
            }
            else
            {	
                $rate = get_product_rate_sales_time($dbcon, $product_id, $pro_rel['product_base_unit'],'');
                $pro_rel['product_sale_rate']=$rate['pr_rate'];
            }
        }

        return $pro_rel;
    }

    function get_convert_qty($dbcon, $product_id, $ptype, $qty) {
        $row=array();
        if($ptype=="1"){
            $type="base_unit";
            $ret_qty=convert_stock($dbcon,$qty,$product_id,$type);
        }else if($ptype=="2"){
            $type="conv_unit";
            $ret_qty=convert_stock($dbcon,$qty,$product_id,$type);
        }else{
            $ret_qty="0";
        }

        $ret_qty_new=number_format($ret_qty, 4, ".", "");
        return $ret_qty_new;
    }


    function get_import_product_unit($dbcon,$product_id) {
        $query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst left join unit_mst as umst on umst.unitid=promst.product_base_unit left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit WHERE product_id=".$product_id;
        
        $rs_type1=$dbcon->query($query1);
        $row1=brp_mysqli_fetch_assoc($rs_type1);
        return $row1;
    }


/////////////////////////////////////////////////////////////////////////////////////////////////////////////Harshil - 19-9-2022/////////////////////////////////////////////////

/* Inquiry Related Functions */
function load_inquiry_no($dbcon){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=2 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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

function cleanString($input) {

    $search_flag = false;
    removeString:
    // Remove HTML tags
    $cleanedString = strip_tags($input);

    // Trim leading and trailing whitespaces
    $cleanedString = trim($cleanedString);

    // Remove \r and \n
    $cleanedString = str_replace(['\r\n', '\n\r', '\r', '\n'], ' ', $cleanedString);


    $cleanedString = preg_replace('/[^\x20-\x7E]/', ' ', $cleanedString);

    
    $wordsToFind = array("Â", "â", "€", "™");


    foreach ($wordsToFind as $word) {
        if (strpos($cleanedString, $word) !== false) {
            goto removeString;
        } else {
            break;
        }
    }    

    return $cleanedString;
}
?>
