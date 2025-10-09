<?php
session_start(); //start session
$AJAX = true;
include('../include/urlfile.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
	
$fetch_sql = "select inq.* from tbl_inquiry as inq left join users as u on u.user_id=inq.user_id where inq.acknowlegement_allow='1' and inq.acknowlegement_sent='0' and u.user_type NOT IN ('25','26') ";
$fetch_result=$dbcon->query($fetch_sql);
if(brp_mysqli_num_rows($fetch_result) > 0){

	while($row=mysqli_fetch_assoc($fetch_result)){
		$previous_time = $row['acknowlegement_assign_time'];
		$inquiry_type = $row['inquiry_type'];
		$previous_time_validate = previous_time_validate($dbcon, $previous_time, $inquiry_type);
		
		if($previous_time_validate){

			$info['acknowlegement_sent']=2;
			$info['acknowlegement_allow']=0;

			// Manage Pending Inquiry History
			$q = $dbcon->query("SELECT inq.* FROM tbl_inquiry as inq WHERE inquiry_id = '".$row['inquiry_id']."' and user_id = '".$row['user_id']."' and owner_user_id = '".$row['owner_user_id']."'");
    		$requestData = $q->fetch_assoc();
			// Update Inquiry Pending History
			$pendingHis['branch_id'] = $row['branch_id'];
			$pendingHis['company_id'] = $row['company_id'];
			$pendingHis['inquiry_id'] = $row['inquiry_id'];
			$pendingHis['type'] = 'inquiry_followup';
			$pendingHis['owner_id'] = $row['owner_user_id'];
			$pendingHis['assign_user_id'] = $row['user_id'];
			$pendingHis['created_at'] = date('Y-m-d H:i:s');
			$pendingHis['status'] = '0';
			$insertData = add_record('tbl_inquiry_pending_history', $pendingHis,  $dbcon);
    		 	
			$updateid=update_record('tbl_inquiry', $info, "inquiry_id=".$row['inquiry_id'] , $dbcon);
		}

	}
}

function previous_time_validate($dbcon, $previous_time, $inquiry_type){
	$previous_time = strtotime($previous_time);
	$cDate = strtotime(date('Y-m-d H:i:s'));
	// Getting the value of old date + Depend on inquiry type base
	if($inquiry_type == '0'){
		$oldDate = $previous_time + 86400; // 86400 seconds in 24 hrs (1 Days)
	}else if($inquiry_type == '1'){
		$oldDate = $previous_time + 172800; // 172800 seconds in 48 hrs (2 Days)
	}else if($inquiry_type == '2'){
		$oldDate = $previous_time + 259200; // 259200 seconds in 72 hrs (3 Days)
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