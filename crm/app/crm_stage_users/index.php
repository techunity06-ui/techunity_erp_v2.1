<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);


if(strtolower($POST['mode']) == "stage_user_sum") {

    $_SESSION['summary_start_date'] = $POST['stage_summ_start_date'];
    $_SESSION['summary_end_date'] = $POST['stage_summ_end_date'];

    $opp_id = isset($POST['opp_id']) ?  $POST['opp_id'] : '';
    
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $crm_user_type = $companyConfiguration['crm_user_type'];
    
    $str="";
    $str.='<table class="table" style="margin-top:50px;overflow-x:scroll;">
    <tr> 
    <th width="10%" style="white-space:nowrap;">Sr. No.</th>
    <th width="30%" >Employee Name</th>';


    $_SESSION['start_date'] = $post_data['start_date'];
	$_SESSION['end_date'] = $post_data['end_date'];

	$where = "";
	$post_user_id = 0;
	if(isset($post_data['user_id']) && !empty($post_data['user_id'])) {
		$post_user_id = $post_data['user_id'];
		$where .= " AND inq.user_id = '".$post_data['user_id']."'";
	}

	$start_date = $POST['stage_summ_start_date'];
	$end_date = $POST['stage_summ_end_date'];
	if(!empty($start_date) && !empty($end_date)){
        $where .="  AND DATE(inq.inquiry_date) >= '".date('Y-m-d',strtotime($start_date))."' AND  DATE(inq.inquiry_date) <= '".date('Y-m-d',strtotime($end_date))."'";
	}

	if($_SESSION['user_type']!=2){ 
        if ($opp_id !=12 && $opp_id !=13) {
		    $where.=" and FIND_IN_SET($_SESSION[user_id],task.show_user_ids)";
        }
        $user_funnel_id = check_user_chein($dbcon,$_SESSION['user_id'],1);
        $where .= " and us.user_id IN (".$user_funnel_id.")";
    }

    $where .= " And rf.opp_id = $opp_id";


    if ($opp_id ==12 || $opp_id ==13) {
	    $query = "select COUNT(inq.inquiry_id) AS led, us.user_id, us.user_name, inq.opp_id, rf.opp_stage from users as us left join tbl_inquiry as inq on inq.user_id=us.user_id left join tbl_opportunity_mst as rf on rf.opp_id=inq.opp_id where inq.inquiry_status=0 and rf.opp_status=0 $where AND inq.company_id IN (0,".$_SESSION['company_id'].") group by us.user_id";
    } else {
        $query = "select COUNT(inq.inquiry_id) AS led, us.user_id, us.user_name, inq.opp_id, rf.opp_stage from users as us left join tbl_inquiry as inq on inq.user_id=us.user_id left join tbl_opportunity_mst as rf on rf.opp_id=inq.opp_id left join tbl_task as task on task.inquiry_id=inq.inquiry_id where inq.inquiry_status=0 and inq.opp_id != 0 and rf.opp_status=0 $where AND inq.company_id IN (0,".$_SESSION['company_id'].") and task.task_status = 0 group by us.user_id";
    }

	$result=$dbcon->query($query);
    
    $where = "";    
    if ($opp_id) {
        $where = " And opp_id=$opp_id";
    }
    $qry="select opp_id,opp_stage from tbl_opportunity_mst where opp_status=0 $where";
    
    $rs_state=$dbcon->query($qry);	
    
    $opp=array();
    $ca="";
    $caa="";

    $row = $rs_state->fetch_assoc();    
    $str.='<th style="white-space:nowrap;">'.$row["opp_stage"].'</th>';
    array_push($opp,$row["opp_id"]);
    $ca.="sum(case when ".$opp_id." = inq.opp_id then 1 else 0 end) '".$opp_id."',";
    $caa.="sum(".$opp_id.") as ".$opp_id.",";
    $str.='</tr>';
    
    $i=1;
    $total=0;
    $opp_total = array();
    $total_funnel = 0;
    while($row_p=mysqli_fetch_assoc($result))
    { 
        $str.='<tr> 
        <td >'.$i.'</td>
        <td>'.$row_p['user_name'].'</td>';
        $total_funnel += $row_p['led'];
        
        $link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list_usr_stage/'.$row_p['user_id'].'/'.$opp_id.'" target="_blank" class="link_dash">'.$row_p['led'].'</a>';
        
        $str.='<td style="">'.$link.'
        </td>';
        $str.='</tr>';
        $i++;
    }
    $str.='<tr style="font-size:16px;"> 
    <td colspan="2" style=""><strong>Total</strong></td>';
    
    $str.='<td style=""><strong><a href="'.ROOT.CRM_ROOT.'inquiry_list" target="_blank" class="link_dash">'.$total_funnel.'</a></strong></td>';
    $str.='</tr>
    </table>';    
    echo $str;
}
?>