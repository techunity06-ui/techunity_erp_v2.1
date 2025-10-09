<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ { 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */{
		//print_r($_POST);
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($_REQUEST['type']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('user_id', 'user_company', 'user_mail', 'user_phone', 'user_stat');
			$sIndexColumn = "user_id";
			$sTable = "coro_users";
			$isWhere = array("user_stat != 2","user_id>0");
			$isJOIN = array();
			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = decrypt($row['user_company']);
				$row_data[] = decrypt($row['user_mail']);
				$row_data[] = decrypt($row['user_phone']);
				//if($row['bank_of']!="0")
				{
					if($row['user_stat']==0)	
					{
						$btn='<button class="btn btn-xs btn-warning" data-original-title="Inactive" data-toggle="tooltip" data-placement="top" onClick="change_status('.$row['user_id'].',1)"><i class="fa fa-times-circle-o"></i></button>';
					}
					else if($row['user_stat']==1)
					{
						$btn='<button class="btn btn-xs btn-success" data-original-title="Active" data-toggle="tooltip" data-placement="top" onClick="change_status('.$row['user_id'].',0)"><i class="fa fa-check"></i></button>';
						
					}
				$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_user('.$row['user_id'].')"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_user('.$row['user_id'].')"><i class="fa fa-trash-o"></i></button> '.$btn;
				}
				/*else
				{
					$row_data[]='';
				}*/
				//<button class="btn btn-xs btn-default" data-original-title="Added On '.date("d-m-Y g:i a",strtotime($row['cust_tmst'])).'" data-toggle="tooltip" data-placement="left"><i class="fa fa-clock-o"></i></button>/
				$appData[] = $row_data;
				$id++;				
			}
			$output['aaData'] = $appData;
			
			echo json_encode( $output );
		}
		else if(strtolower($POST['type']) == "add") {
			if($_POST['token'] == $_SESSION['token']) {	
				
				$tr = $dbcon -> query("SELECT `user_id`,`user_stat` FROM `coro_users` WHERE `user_mail` = '".encrypt(strtolower($_POST['email']))."'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						echo '-1';
					}
					else {
						echo '-1';
					}
				}
				else {					
					$str = "INSERT INTO `coro_users`(`user_name`,`user_address`,`user_phone`, `user_mail`, `user_key`,  `user_company`,  `user_stat`,  `user_tmst`)
					VALUES('".encrypt($POST['company_name'])."',
						 '".encrypt($POST['address'])."','".encrypt($POST['contact_no'])."','".encrypt($_POST['email'])."','".md5($_POST['password'])."','".encrypt($POST['company_name'])."','0','".encrypt(date('Y-m-d 00:00:00'))."')";
						
				$query = $dbcon -> query($str);						
					if($query)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `coro_users` WHERE `user_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			$row_data['company_name'] = decrypt($r['user_company']);
			$row_data['user_mail'] = decrypt($r['user_mail']);
			$row_data['user_phone'] = decrypt($r['user_phone']);
			$row_data['user_address']=decrypt($r['user_address']);
			$row_data['user_id']=$r['user_id'];
			echo json_encode($row_data);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$date = date("Y-m-d H:i:s");
				$pass_query='';
				if($_POST['password']!="" || !empty($_POST['password']))
				{
					$pass_query="`user_key`= '".md5($_POST['password'])."',";					
				}
				$str = "
					UPDATE
						`coro_users`
					SET 
						`user_company`= '".encrypt($POST['company_name'])."',
						`user_address`= '".encrypt($POST['address'])."',
						`user_phone`= '".encrypt($POST['contact_no'])."',
						`user_mail`= '".encrypt($_POST['email'])."',
						".$pass_query."
						`setup`= '0'
					WHERE
						`user_id`= ".$POST['id'];
				$query = $dbcon -> query($str);	
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			//if($_POST['token'] == $_SESSION['token']) 
			{
				$query = $dbcon -> query("UPDATE `coro_users` SET `user_stat` = 2 WHERE `user_id` = '$POST[id]'");
				if($query)
					echo "1";
				else
					echo "0";
			}
		}
		else if(strtolower($POST['type']) == "status_update") {
		
		$query = $dbcon -> query("UPDATE `coro_users` SET `user_stat` = ".$POST['status']." WHERE `user_id` = '$POST[id]'");
		if($query)
			echo "1";
		else
			echo "0";

		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}/*
else {
    die("Error - 1");
}*/	
?>