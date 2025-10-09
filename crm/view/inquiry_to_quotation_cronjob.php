<?php
session_start(); //start session
$AJAX = true;
include('../include/urlfile.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$fetch_sql = "select task.*,inq.inquiry_type,inq.inquiry_no from `tbl_task` as task left join tbl_inquiry as inq on inq.inquiry_id=task.inquiry_id where task.`task_type_id` = 15 and task.task_status=0 and task.quotation_followup_lock=0 ";
$fetch_result=$dbcon->query($fetch_sql);
if(brp_mysqli_num_rows($fetch_result) > 0){
	while($row=mysqli_fetch_assoc($fetch_result)){
		
		$followup_start_date = $row['followup_start_date'];
		$inquiry_type = $row['inquiry_type'];
		$previous_time_validate = previous_time_validate($dbcon, $followup_start_date, $inquiry_type);
	
		if($previous_time_validate){
			$user_id = $row['user_id'];
			$show_user_ids = explode(',',$row['show_user_ids']);
			$reporting_user_id = getReportingUser($dbcon,$row['user_id']);
			if(!in_array($reporting_user_id, $show_user_ids)){
				$show_user_ids[] = $reporting_user_id;
			}
			$show_user_ids_implode = implode(',', $show_user_ids);

			// Update Followup Pending 
			//update_record('tbl_task', array('quotation_followup_lock' => '1', 'followup_start_date' => date("Y-m-d H:i:s"), 'show_user_ids' => $show_user_ids_implode), "task_id=" . $row['task_id'], $dbcon);
			
			//Maulik Kapatel User Lock 
			$user_lock = "select user_id,user_name,user_lock,user_lock_date,user_type from users where user_id=".$row['assign_user_ids'];
			$user_lock_q = $dbcon->query($user_lock);
			
			$user_lock_r = mysqli_fetch_assoc($user_lock_q);
			if($user_lock_r['user_type'] != "2"){
				update_record('users', array('user_lock' => '1', 'user_lock_date' => date("Y-m-d H:i:s")), "user_id=" . $row['assign_user_ids'], $dbcon);
				
				$user_log['locked_uname'] = $user_lock_r['user_name'];
				$user_log['user_locked_date'] = date('Y-m-d H:i:s');
				$user_log['user_locked_reason'] = $row['inquiry_no'];
				$user_log['cdate'] = date('Y-m-d H:i:s');
				add_record('tbl_userlock_log', $user_log,  $dbcon);
			}
			// Manage Pending Followup History
			$pendingHis['branch_id'] = $row['branch_id'];
			$pendingHis['company_id'] = $row['company_id'];
			$pendingHis['inquiry_id'] = $row['inquiry_id'];
			$pendingHis['task_id'] = $row['task_id'];
			$pendingHis['type'] = 'inquiry_locked';
			$pendingHis['owner_id'] = $reporting_user_id;
			$pendingHis['assign_user_id'] = $row['user_id'];
			$pendingHis['created_at'] = date('Y-m-d H:i:s');
			$pendingHis['status'] = '0';
			$insertData = add_record('tbl_inquiry_pending_history', $pendingHis,  $dbcon);

			// Send Mail
			$m_sql = "select user.user_name, user.employee_id,l.cust_email as missing_user_email from `users` as user left join tbl_ledger as l on l.l_id=user.employee_id where user.`user_id`='".$row['user_id']."'";
			$m_result=$dbcon->query($m_sql);
			$m_row=mysqli_fetch_assoc($m_result);

			$r_sql = "select user.user_name, user.employee_id,l.cust_email as reporting_user_email from `users` as user left join tbl_ledger as l on l.l_id=user.employee_id where user.`user_id`='".$reporting_user_id."'";
			$r_result=$dbcon->query($r_sql);
			$r_row=mysqli_fetch_assoc($r_result);

			if($m_row['missing_user_email']!='' && $m_row['missing_user_email']!='0'){
				$from_email_id = ($r_row['reporting_user_email']) ? $r_row['reporting_user_email'] : ADMIN_EMAIL;
			    $to_email_id = ($m_row['missing_user_email']) ? $m_row['missing_user_email'] : '';
			    $subject = 'QUOTATION CREATION MISSING ACKNOWLEGEMENT';
			    $content = 'Dear '.$m_row['user_name'].',<br><br>';
			    $content .= 'You hame missed the quotation creation. Please contact to '.$r_row['user_name'].' .<br><br>';
			    $content .= 'Thank you.';

			    //send_mail($dbcon,[$to_email_id], $subject, $content, $from_email_id,'','','','cron');
			}
		    
		}
	}
}

function previous_time_validate($dbcon, $followup_start_date, $inquiry_type){
	$followup_start_date = strtotime($followup_start_date);
	$cDate = strtotime(date('Y-m-d H:i:s'));
	// Getting the value of old date + Depend on inquiry type base
	if($inquiry_type == '0'){
// 		$oldDate = $followup_start_date + 86400; // 86400 seconds in 24 hrs (1 Days)
		$oldDate = $followup_start_date + 2592000; // 86400 seconds in 24 hrs (1 Days)
	}else if($inquiry_type == '1'){
// 		$oldDate = $followup_start_date + 172800; // 172800 seconds in 48 hrs (2 Days)
		$oldDate = $followup_start_date + 2592000; // 172800 seconds in 48 hrs (2 Days)
	}else if($inquiry_type == '2'){
// 		$oldDate = $followup_start_date + 259200; // 259200 seconds in 72 hrs (3 Days)
		$oldDate = $followup_start_date + 2592000; // 259200 seconds in 72 hrs (3 Days)
	}
	if($oldDate < $cDate)
	{
	  return true;
	}
	else
	{
	  return false;
	}
}
?>	