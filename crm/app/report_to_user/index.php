<?php
session_start();
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
		//check paermission for customer add
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		REPORT_TO_USER_APPROVE
	]);

	$branch_id = $POST['branch_id'];
	
	$appData = array();
	$i=1;
	$aColumns = array('user.user_id', 'user.user_name', 'utype.usertype_name', 'user.user_type', 'user.user_mail', 'user.user_phone', 'user.report_to_user_id', 'user.company_id', 'user.user_lock', 'user.user_lock_date');
	$sIndexColumn = "user.user_id";
	if($_SESSION['user_type'] == '2'){
		$isWhere = array("user.company_id in ($_SESSION[company_id]) and active=0");
	}else{
		$isWhere = array("user.report_to_user_id in ($_SESSION[user_id]) and active=0");
	}
	$sTable = " users as user";			
	$isJOIN = array('left join tbl_usertype as utype on utype.usertype_id = user.user_type');
	$hOrder = "user.user_id desc";
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['usertype_name'];
		$row_data[] = $row['user_name'];
		$row_data[] = $row['user_mail'];
		$row_data[] = $row['user_phone'];
		
		if($row['user_lock'] == '1'){
			$row_data[] = '<label class="label label-danger">Locked</label>';
		}else{
			$row_data[] = '<label class="label label-success">Unlocked</label>';
		}
		
		$user_unlock=''; 
		if($row['user_lock'] == '1'){
			if(in_array(REPORT_TO_USER_APPROVE,$bulkAccessArray)){
				$user_unlock=' <button class="btn btn-xs btn-success" data-original-title="User Unlock" data-toggle="tooltip" data-placement="top" onClick="edit_user_unlock('.$row['user_id'].')"><i class="fa fa-unlock" aria-hidden="true"></i></button>'; 
			}
		}
		$row_data[] = $user_unlock;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
} else if(strtolower($POST['mode']) == "unlock") {
	$info['user_lock']		= 0;
	$updateid=update_record('users', $info,"user_id=".$POST['eid'] , $dbcon);
	
		//Insert Log Table 
	$que = "select user_name,user_lock_date from users where user_id =".$POST['eid'];
	$row=mysqli_fetch_assoc($dbcon->query($que));
	
	
	$info1['locked_uname'] 		= $row['user_name'];
	$info1['user_locked_date'] 	= $row['user_lock_date'];
	$info1['user_locked_reason'] = $_POST['user_locked_reason'];
	$info1['user_unlock_date'] 	= date("Y-m-d H:i:s");
	$info1['unlocked_by'] 	 	= $_SESSION['user_name'];
	$info1['cdate'] 			= date("Y-m-d H:i:s");
	
	$insertid = add_record('tbl_userlock_log', $info1, $dbcon, $branch_id);
	
	if($updateid)
		echo "1";	
	else
		echo "0";	
} else if(strtolower($POST['mode']) == "preedit") {
	$inq_qry = "select user.*, usertype.usertype_name from users as user LEFT JOIN tbl_usertype as usertype on usertype.usertype_id = user.user_type where user.user_id =".$POST['eid'];
    $inq_data = brp_mysqli_fetch_assoc($dbcon->query($inq_qry));
    echo json_encode($inq_data);
}
?>