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
	$where.="  and task.task_due_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND task.task_due_date <= '".date('Y-m-d',strtotime($s_date[1]))."' AND task.task_type_id !=".GENERAL_TASK_TYPE;
	$i = 1;
	if(!empty($POST['user_id'])){
		$where.=" and task.user_id = ".$POST['user_id'];
	}
	$aColumns = array('task.task_id','task.task_rel_id', 'task.task_name','inq.inquiry_no', 'inq.inquiry_name', 'inq.inquiry_date', 'cust.cust_name', 'per.c_con_fname', 'state.state_name', 'city.city_name', 'task.task_due_date', 'task.task_remark', 'usr.user_name', 'task.assign_user_ids', 'task.inquiry_id', 'task.task_type_id', 'task.task_status', 'task.entry_type', 'task.task_completion_date', 'type.mcd_name as type_name', 'task.user_id', 'task.task_priority_id','if(tr.project_wise=0,(SELECT concat(group_concat( pro.product_name SEPARATOR ",<br>")," -- " ,pro.product_icode) FROM `tbl_inquiry_trn` as trn left join product_mst as pro on pro.product_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id) ,(select group_concat(proj.project_name) from tbl_inquiry_trn as trn left join tbl_project_assign as proj on proj.project_assign_id=trn.product_id where trn.inquiry_trn_status=0 and trn.inquiry_id=inq.inquiry_id)) as pro_name', 'stage.opp_stage', 'stage.opp_probability','cust.cust_mobile','stage.opp_color','task.cdate');
	$sIndexColumn = "task.task_id";
    //and alert_date_time="'.date('Y-m-d',strtotime($POST['fil_due_date'])).'" and
	$isWhere = array("task.task_status!=2 and task.entry_type=1 and task.company_id in (0,$_SESSION[company_id])" . $where);
	$sTable = "tbl_task as task";
	$isJOIN = array('left join users as usr on usr.user_id=task.user_id',
		'left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id',
		'left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id',
		'left join task_rel_mst as row on row.task_rel_id=task.task_rel_id',
		'left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date from tbl_inquiry) as inq on inq.inquiry_id=task.inquiry_id',
		'',
		'left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id',
		'left join tbl_customer as cust on cust.cust_id=inq.c_id',
		'left join state_mst as state on state.stateid=(SELECT c_add_state FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
		'left join city_mst as city on city.cityid=(SELECT c_add_city FROM `tbl_cust_address` WHERE cust_id=inq.c_id and c_add_status=0 limit 1)',
		'left join tbl_opportunity_mst as stage on stage.opp_id=task.opp_id',
		'left join tbl_cust_contact as per on per.c_con_id=task.c_con_id');
	$hOrder = "task.task_id DESC";
	include('../../../include/pagging.php');
	$appData = array();
	$id = 1;
	foreach ($sqlReturn as $row) {
		$bg_color = ($row['opp_color'])? trim($row['opp_color']) : '';
        if ($row['task_rel_id'] == '5') {//Inquiry
        	$rel_name = $row['cust_name'] . '<br/>' . $row['inquiry_name'] . '<br/>' . $row['inquiry_no'];
        } else if ($row['task_rel_id'] == '4') { // Company
        	$rel_name = $row['cust_name'];
        } else if ($row['task_rel_id'] == '3') {//Person
        	$rel_name = $row['c_con_fname'];
        } else {
        	$rel_name = $row['task_name'];
        }
        $row_data = array();
        $row_data[] = $row['sr'];
        $row_data[] = '<strong>' . $row['type_name'] . '</strong> ';
        // $row_data[] = $row['task_rel_name'];
        $row_data[] = '<strong>' . $rel_name . '</strong>';
        // $row_data[] = $row['t_name'];
        $row_data[] = $row['cust_mobile'];
        // $row_data[] = $row['mcd_name'];
        $row_data[] = $row['pro_name'];
        $row_data[] = '<span class="btn btn-sm" style="color:black;background-color: '.$bg_color.';">'. $row['opp_stage'] . '<br>(' . $row['opp_probability'] . '%)</span>';
        $row_data[] = $row['state_name'].' - '.$row['city_name'];
        $row_data[] = date("d-M-Y", strtotime($row['task_due_date'])) . '<br/>' . date("h:i A", strtotime($row['task_due_date']));
        $row_data[] = nl2br($row['task_remark']);
        $row_data[] = $row['user_name'];
        $row_data[] = getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']);
        if ($row['task_status'] == '1') {
        	$tsk_due_time = strtotime($row['task_due_date']);
        	$cur_time = strtotime($row['task_completion_date']);
        	$tsk_type = '';
        	$earlier = new DateTime($row['task_due_date']);
        	$later = new DateTime($row['task_completion_date']);

        	$abs_diff = $later->diff($earlier)->format("%a days");
        	$row_data[] = $abs_diff;
        	$row_data[] = '<button type="button" class="btn btn-sm btn-success" data-original-title="Task Completed" data-toggle="tooltip" data-placement="top"><i class="fa fa-check"></i> Completed ' . $tsk_type . '</button>';
        }else{
        	$tsk_due_time = strtotime($row['task_due_date']);
        	$cur_time = strtotime(date('Y-m-d h:i:s'));
        	$tsk_type = '';
        	if ($tsk_due_time < $cur_time) {
        		$tsk_type = "<label style='background:#d9534f;'>(Delayed)</label>";
        	}

        	$earlier = new DateTime($row['task_due_date']);
        	$later = new DateTime(date('Y-m-d h:i:s'));

        	$abs_diff = $later->diff($earlier)->format("%a days");
        	$row_data[] = $abs_diff;
        	$row_data[] = '<button type="button" class="btn btn-sm btn-warning" data-original-title="Task Pending" data-toggle="tooltip" data-placement="top">Pending ' . $tsk_type . '</button>';
        }
        $row_data[] = $row['user_name']. ' Updated on '.date('d M, Y',strtotime($row['cdate'])).' by '.date('h:i A',strtotime($row['cdate']));
        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode($output);
}
function getTaskAssignNameCommaSeparated($dbcon, $assign_user_ids)
{

	$strVal = '';
	$qry = 'SELECT tsk.task_id, GROUP_CONCAT(userdata.user_name) AS valuesdata FROM tbl_task tsk JOIN users AS userdata ON FIND_IN_SET(userdata.user_id, "' . $assign_user_ids . '") GROUP BY tsk.task_id';
		$qry_rel = mysqli_fetch_assoc($dbcon->query($qry));

		if ($qry_rel) {
			$strVal = $qry_rel['valuesdata'];
		}
		return $strVal;
	}
?>