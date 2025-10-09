<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(strtolower($POST['mode']) == "generate_report") {
	$where = '';
	$s_date=explode(' - ',$POST['rep_date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	if ($POST['fil_task_type_id']) {
		$where.= ' and task.task_type_id=' . $POST['fil_task_type_id'];
	}
	if ($POST['t_id']) {
		$where.= ' and cust.t_id=' . $POST['t_id'];
	}
	if ($POST['cust_id']) {
		$where.= ' and inq.c_id=' . $POST['cust_id'];
	}
	if ($POST['state_id']) {
		$where.= ' and cadd.c_add_state=' . $POST['state_id'];
	}
	if ($POST['city_id']) {
		$where.= ' and cadd.c_add_city=' . $POST['city_id'];
	}
	if ($POST['user_id']) {
		$where.= ' and task.user_id=' . $POST['user_id'];
	}
	if ($POST['stage_id']) {
		$where.= ' and task.opp_id=' . $POST['stage_id'];
	}
	$where.="  and task.task_due_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND task.task_due_date <= '".date('Y-m-d',strtotime($s_date[1]))."' AND task.task_type_id !=".GENERAL_TASK_TYPE;
	$i = 1;
	$aColumns = array('task.task_id', 'task.task_rel_id', 'task.task_name','inq.inquiry_no', 'inq.inquiry_name', 'inq.inquiry_date', 'cust.cust_name', 'per.c_con_fname', 'state.state_name', 'city.city_name', 'task.task_due_date', 'task.task_remark', 'usr.user_name', 'task.assign_user_ids', 'task.inquiry_id', 'task.task_type_id', 'task.task_status', 'task.entry_type', 'task.task_completion_date', 'type.mcd_name as type_name', 'task.user_id', 'task.task_priority_id','tea.t_name', 'stage.opp_stage', 'stage.opp_probability','stage.opp_color','task.cdate','inq.c_id','cadd.c_add_state','cadd.c_add_city');
	$sIndexColumn = "task.task_id";
    //and alert_date_time="'.date('Y-m-d',strtotime($POST['fil_due_date'])).'" and
	$isWhere = array("task.task_status = 0 and task.entry_type=1 and task.company_id = $_SESSION[company_id]" . $where);
	$sTable = "tbl_task as task";
	$isJOIN = array('left join users as usr on usr.user_id=task.user_id',
		'left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
		'left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id',
		'left join task_rel_mst as row on row.task_rel_id=task.task_rel_id',
		'left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id',
		'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
		'left join tbl_customer as cust on cust.cust_id=inq.c_id',
		'left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
		'left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
		'left join tbl_opportunity_mst as stage on stage.opp_id=task.opp_id',
		'left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_add_status=0',
		'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id',
		'left join territory_mst as tea on tea.t_id=cust.t_id');
	$hOrder = "task.task_id DESC";
	$hGroupby = array("task.task_id");
	include('../../../include/pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';
        if ($row['task_rel_id'] == '5') {//Inquiry
        	$rel_name = $row['cust_name'];
        } else if ($row['task_rel_id'] == '4') { // Company
        	$rel_name = $row['cust_name'];
        } else if ($row['task_rel_id'] == '3') {//Person
        	$rel_name = $row['c_con_fname'];
        } else {
        	$rel_name = $row['task_name'];
        }
        $row_data = array();
        $row_data[] = $row['sr'];
        $row_data[] = $row['type_name'];
        $row_data[] = $row['inquiry_no'];
        $row_data[] = date("d-M-Y", strtotime($row['inquiry_date']));
        $row_data[] = $rel_name;
        $row_data[] = '<span class="btn btn-sm" style="color:black;background-color: '.$bg_color.';">'. $row['opp_stage'] . '<br>(' . $row['opp_probability'] . '%)</span>';
        $row_data[] = $row['state_name'].' - '.$row['city_name'];
        $row_data[] = $row['t_name'];
        $row_data[] = date("d-M-Y", strtotime($row['cdate'])) . '<br/>' . date("h:i A", strtotime($row['cdate']));
        $row_data[] = date("d-M-Y", strtotime($row['task_due_date'])) . '<br/>' . date("h:i A", strtotime($row['task_due_date']));
        $row_data[] = nl2br($row['task_remark']);
        $row_data[] = $row['user_name'];
        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode($output);
}
?>