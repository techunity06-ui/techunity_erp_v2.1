<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	if(strtolower($POST['mode']) == "fetch") {
		$appData = array();
		$i=1;
		$aColumns = array('user.user_id','usertype_name', 'user_name','user_mail','state.state_name','city.city_name','user_phone','user.company_id','user.user_rid','user.user_type');
		$sIndexColumn = "user.user_id";
		$isWhere = array("user.active=0 and user.company_id=".$_SESSION['company_id']);
		$sTable = "users as user";
		$isJOIN = array('left join state_mst state on user.user_stat=state.stateid', 'left join city_mst city on user.user_city=city.cityid', 'left join tbl_usertype type on user.user_type=type.usertype_id');
		$hOrder = "user.user_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['usertype_name'];
			$row_data[] = $row['user_name'];
			$row_data[] = $row['user_mail'];
			$row_data[] = $row['city_name'];
			$row_data[] = $row['state_name'];
			$row_data[] = $row['user_phone'];
			
			$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'useredit/'.$row['user_id'].'"><i class="fa fa-pencil"></i></a>';
			
			if($row['user_type']=='2') {
				$del_btn='';
			}
			else{
				$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_user('.$row['user_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			if($_SESSION['user_type']=='2') {
				$row_data[] = $edit_btn.' '.$del_btn;
			}
			else {
				$row_data[]='';
			}
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT `user_id`,`user_email` FROM `tbl_user` WHERE `user_email` = '$POST[user_email]'");
		if($tr->num_rows > 0) {
			$row['res']='-1';
			echo json_encode($row);
		}
		else {
			$info['user_name']		= $POST['user_name']; 
			$info['user_mail']		= strtolower($POST['user_email']);
			$info['user_key']		= md5($_POST['password']);
			$info['user_type']		= $POST['usertype_id'];
			$info['user_stat']		= $POST['stateid'];
			$info['user_city']		= $POST['cityid'];
			$info['user_phone']		= $POST['user_mobile'];
			$info['user_country']	= '100';
			$info['user_address']	= $POST['user_address'];
			$info['user_rid']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			$info['payment_status'] = 1;
			$inserid=add_record('users', $info, $dbcon);
			$row['res']='';
			if($inserid) {
				$row['res'] ="1";
			}
			else {
				$row['res'] ="0";
			}
			echo json_encode($row);	
		}
		
	}		
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['user_name']		= $_POST['user_name'];
		$info['user_mail']		= strtolower($POST['user_email']);
		if(!empty($POST['password'])) {
			$info['user_key']		= md5($_POST['password']);
		}
		$info['user_type']		= $POST['usertype_id'];
		$info['user_stat']		= $POST['stateid'];
		$info['user_city']		= $POST['cityid'];
		$info['user_phone']		= $POST['user_mobile'];
		$info['user_country']	= '100';
		$info['user_address']	= $POST['user_address'];
		$info['user_rid']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];
		$updateid=update_record('users', $info,"user_id=".$POST['eid'] , $dbcon);
		$row['res']='';
		/*if($updateid) {
			$row['res']='update';
		}
		else {
			$row['res']='0';
		}*/
		$row['res']='update';
		echo json_encode($row);
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['active']		= 2;
		$updateid=update_record('users', $info,"user_id=".$POST['eid'] , $dbcon);				
		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "load_state") {
		echo getstate($dbcon,0);	
	}
	else if(strtolower($POST['mode']) == "load_city") {
		$cityid=$POST['id'];				
		echo $str=getcity($dbcon,$cityid,0);
	}
	
	
?>