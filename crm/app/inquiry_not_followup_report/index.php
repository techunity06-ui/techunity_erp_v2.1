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

if(strtolower($POST['mode']) == "inqiuiry_not_followup_detail_report")
{
    $date  = $POST['date'];
    $where='';
    
    $where.=" and DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='" . date('Y-m-d', strtotime($date)) . "'";

    if(!empty($_POST['cust_id'])){
        $where.=' and inq.c_id='.$_POST['cust_id'];
    }

    $count_que = 'select count(task.task_id) as cnt_task from tbl_task as task
    left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,c_con_id from tbl_inquiry
        where inquiry_status=0
    ) as inq on inq.inquiry_id=task.inquiry_id
    where task.task_status=0 and task.entry_type=1 and task.company_id in (0,'.$_SESSION['company_id']. ') and task.task_type_id=16 and task.task_type_id !="'.GENERAL_TASK_TYPE.'"'.$where;
    $result_cnt = $dbcon->query($count_que);
    $row_cnt = brp_mysqli_fetch_array($result_cnt);
    $rowperpage = 30;
    $query = 'SELECT task.task_id, task.task_rel_id,task.create_date,task.cdate, task.task_name, tea.t_name, inq.inquiry_no, qt_aprv.quotation_no, inq.inquiry_name, inq.inquiry_date, cust.cust_name, cust.cust_mobile, per.c_con_fname, row.task_rel_name, state.state_name, city.city_name, task.task_due_date, task.task_remark, usr.user_name, task.task_rel_id, task.assign_user_ids, task.inquiry_id, task.task_type_id, task.task_status, task.entry_type, task.alert_date_time, type.mcd_name as type_name, task.user_id, task.task_priority_id, task_sub.mcd_name as task_sub_name, stage.opp_stage, stage.opp_probability, qt_aprv.approve_status, qt_aprv.quotation_id, type.mcd_id, mcd.mcd_name, inq.closing_date, cadd.c_add_address,inq.c_con_id,cu_con.c_con_fname,cu_con.c_con_lname,cu_con.c_con_mobile,cu_con.c_con_email,rf.rb_name as source

    FROM tbl_task as task 
    left join users as usr on usr.user_id=task.user_id 
    left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id 
    left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id 
    left join task_rel_mst as row on row.task_rel_id=task.task_rel_id 
    left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,c_con_id from tbl_inquiry
        where inquiry_status=0
    ) as inq on inq.inquiry_id=task.inquiry_id 
    left join (SELECT inquiry_id,approve_status,quotation_id,quotation_no FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id 
    left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id 
    left join tbl_customer as cust on cust.cust_id=inq.c_id
    left join tbl_cust_contact as cu_con on cu_con.c_con_id = inq.c_con_id 
    left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_addr_defult=1
    left join state_mst as state on state.stateid=cadd.c_add_state
    left join city_mst as city on city.cityid=cadd.c_add_city
    left join tbl_opportunity_mst as stage on stage.opp_id=(SELECT opp_id from tbl_inquiry where inquiry_id=task.inquiry_id) 
    left join tbl_cust_contact as per on per.c_con_id=task.c_con_id 
    left join tbl_refer_by as rf on rf.rb_id=cust.cust_source
    left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type 
    left join territory_mst as tea on tea.t_id=cust.t_id

    where task.task_status=0 and task.entry_type=1 and task.company_id in (0,'.$_SESSION['company_id']. ') and task.task_type_id=16 and task.task_type_id !="'.GENERAL_TASK_TYPE.'" '.$where.' 
    
    
    Group by task.task_id ORDER BY task.task_id asc  limit 0,'.$rowperpage;
    
    $result = $dbcon->query($query);
    $str = '';
    
    $str .= '<table class="table table-bordered table-striped " id="data_list">
            <thead> 
                <tr>
                    <th style="text-align:center" colspan="19"> Inquiry Not Follow Up Report </th>
                </tr>
                <tr>
                    <th>Sr.no.</th>
                    <th>Create Date</th>
                    <th>Modify Date</th>
                    <th>Owner</th>
                    <th>Assign Users</th>
                    <th>Inquiry No</th>
                    <th>Company Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Contact Person Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Customer Type</th>
                    <th>Source</th>
                    <th>Sales Stage</th>
                    <th>Last Follow Up Report</th>
                    <th>Desc</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>';
    $i=1;
    $cnt = brp_mysqli_num_rows($result);
    if($cnt>0){
        while($row = $result->fetch_assoc()){
            //var_dump($row);
            $str.='<tr class="post" id="post_'.$row['task_id'].'">
                <td>'.$i.'</td>
                <td>'.date('d-m-Y',strtotime($row['create_date'])).'</td>
                <td>'.date('d-m-Y',strtotime($row['cdate'])).'</td>
                <td>'.$row['user_name'].'</td>
                <td>'.getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']).'</td>
                <td>'.$row['inquiry_no'].'</td>
                <td>'.$row['cust_name'].'</td>
                <td>'.$row['c_add_address'].'</td>
                <td>'.$row['city_name'].'</td>
                <td>'.$row['state_name'].'</td>
                <td>'.$row['c_con_fname'].' '.$row['c_con_lname'].'</td>
                <td>'.$row['c_con_mobile'].'</td>
                <td>'.strtolower($row['c_con_email']).'</td>
                <td></td>
                <td>'.$row['source'].'</td>
                <td>'.$row['opp_stage'].' ('.$row['opp_probability'].' %)</td>
                <td></td>
                <td></td>
                <td>'.nl2br($row['task_remark']).'</td>
            </tr>';
            $i++;
        }
    }else{
        $str.='<tr>
            <td colspan="19" style="text-align:center"><strong>No Data Found......!!!</strong></td>
        </tr>';
    } 
    $str .='<td colspan="19" style="text-align:left"><button class="btn btn-sm btn-info load-more" onClick="load_more()">Load More</button>
            <input type="hidden" id="row" value="0">
            <input type="hidden" id="all" value="'.$row_cnt['cnt_task'].'"></td>';
    $str .='</tbody>
    </table>';

    echo $str;
}else if(strtolower($POST['mode'])=="load_more_inqiuiry_not_followup_detail_report"){
    $date  = $POST['date'];
    $where='';
    $row_show = $POST['row'];

    $where.=" and DATE_FORMAT(task.task_due_date,'%Y-%m-%d')<='" . date('Y-m-d', strtotime($date)) . "'";

    if(!empty($_POST['cust_id'])){
        $where.=' and inq.c_id='.$_POST['cust_id'];
    }
    $rowperpage = 30;
    $query = 'SELECT task.task_id, task.task_rel_id,task.create_date,task.cdate, task.task_name, tea.t_name, inq.inquiry_no, qt_aprv.quotation_no, inq.inquiry_name, inq.inquiry_date, cust.cust_name, cust.cust_mobile, per.c_con_fname, row.task_rel_name, state.state_name, city.city_name, task.task_due_date, task.task_remark, usr.user_name, task.task_rel_id, task.assign_user_ids, task.inquiry_id, task.task_type_id, task.task_status, task.entry_type, task.alert_date_time, type.mcd_name as type_name, task.user_id, task.task_priority_id, task_sub.mcd_name as task_sub_name, stage.opp_stage, stage.opp_probability, qt_aprv.approve_status, qt_aprv.quotation_id, type.mcd_id, mcd.mcd_name, inq.closing_date, cadd.c_add_address,inq.c_con_id,cu_con.c_con_fname,cu_con.c_con_lname,cu_con.c_con_mobile,cu_con.c_con_email,rf.rb_name as source

    FROM tbl_task as task 
    left join users as usr on usr.user_id=task.user_id 
    left join tbl_master_category_detail as type on type.mcd_id=task.task_type_id 
    left join tbl_master_category_detail as task_sub on task_sub.mcd_id=task.task_type_id 
    left join task_rel_mst as row on row.task_rel_id=task.task_rel_id 
    left join (SELECT cust_id as c_id,inquiry_id,inquiry_no,inquiry_name, inquiry_date, closing_date,c_con_id from tbl_inquiry
        where inquiry_status=0
    ) as inq on inq.inquiry_id=task.inquiry_id 
    left join (SELECT inquiry_id,approve_status,quotation_id,quotation_no FROM `tbl_quotation` WHERE quotation_status=0 and revise_status=0) as qt_aprv on qt_aprv.inquiry_id=task.inquiry_id 
    left join tbl_inquiry_trn as tr on tr.inquiry_id=inq.inquiry_id 
    left join tbl_customer as cust on cust.cust_id=inq.c_id
    left join tbl_cust_contact as cu_con on cu_con.c_con_id = inq.c_con_id 
    left join tbl_cust_address as cadd on cadd.cust_id=inq.c_id and cadd.c_addr_defult=1
    left join state_mst as state on state.stateid=cadd.c_add_state
    left join city_mst as city on city.cityid=cadd.c_add_city
    left join tbl_opportunity_mst as stage on stage.opp_id=(SELECT opp_id from tbl_inquiry where inquiry_id=task.inquiry_id) 
    left join tbl_cust_contact as per on per.c_con_id=task.c_con_id 
    left join tbl_refer_by as rf on rf.rb_id=cust.cust_source
    left join tbl_master_category_detail as mcd on mcd.mcd_id=cust.cust_type 
    left join territory_mst as tea on tea.t_id=cust.t_id

    where task.task_status=0 and task.entry_type=1 and task.company_id in (0,'.$_SESSION['company_id']. ') and task.task_type_id=16 and task.task_type_id !="'.GENERAL_TASK_TYPE.'" '.$where.' 
    
    
    Group by task.task_id ORDER BY task.task_id asc  limit '.$row_show.','.$rowperpage;
    $i=$row_show+1;
    $result = $dbcon->query($query);
    while($row = $result->fetch_assoc()){
        //var_dump($row);
        $str.='<tr class="post" id="post_'.$row['task_id'].'">
            <td>'.$i.'</td>
            <td>'.date('d-m-Y',strtotime($row['create_date'])).'</td>
            <td>'.date('d-m-Y',strtotime($row['cdate'])).'</td>
            <td>'.$row['user_name'].'</td>
            <td>'.getTaskAssignNameCommaSeparated($dbcon, $row['assign_user_ids']).'</td>
            <td>'.$row['inquiry_no'].'</td>
            <td>'.$row['cust_name'].'</td>
            <td>'.$row['c_add_address'].'</td>
            <td>'.$row['city_name'].'</td>
            <td>'.$row['state_name'].'</td>
            <td>'.$row['c_con_fname'].' '.$row['c_con_lname'].'</td>
            <td>'.$row['c_con_mobile'].'</td>
            <td>'.strtolower($row['c_con_email']).'</td>
            <td></td>
            <td>'.$row['source'].'</td>
            <td>'.$row['opp_stage'].' ('.$row['opp_probability'].' %)</td>
            <td></td>
            <td></td>
            <td>'.nl2br($row['task_remark']).'</td>
        </tr>';
        $i++;
    }
    echo $str;
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
