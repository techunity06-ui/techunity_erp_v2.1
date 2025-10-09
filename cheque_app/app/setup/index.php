<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
//include("../../../config/session.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ { 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */{
		//print_r($_POST);
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}		
		if(strtolower($POST['type']) == "add") {
			if($_POST['token'] == $_SESSION['token']) {
				//exec('wmic DISKDRIVE GET SerialNumber 2>&1',$m);				
				 $str = "
					UPDATE
						`coro_users`
					SET 
						`user_company`= '".encrypt($POST['company_name'])."',
						`user_address`= '".encrypt($POST['address'])."',
						`user_phone`= '".encrypt($POST['contact_no'])."',
						`user_mail`= '".encrypt($_POST['email'])."',
						`user_key`= '".md5($_POST['password'])."',
						
						`user_stat`= '1',
						`setup`= '0',
						`user_tmst`= '".encrypt(date('Y-m-d 00:00:00'))."'
					WHERE
						`user_id`= 0 "; //`user_date`= '".($m[1].'2015')."',
				$query = $dbcon -> query($str);					
					if($query)
						echo "1";
					else
						echo "0";
				}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `bank_mst` WHERE `bankid` = '$POST[id]'  AND `user_id` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			$r['bank_name']=($r['bank_name']);
			echo json_encode($r);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$date = date("Y-m-d H:i:s");
				$str = "
					UPDATE
						`bank_mst`
					SET 
						`bank_name`= '".($POST['name'])."',						
						`cdate`= '$date'
					WHERE
						`bankid`= '$POST[id]' AND `user_id` = '$_SESSION[user_id]'
				";
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$query = $dbcon -> query("UPDATE `bank_mst` SET `bank_status` = 0 WHERE `bankid` = '$POST[id]' AND `user_id` = '$_SESSION[user_id]'");
				if($query)
					echo "1";
				else
					echo "0";
			}
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