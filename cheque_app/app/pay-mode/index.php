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
			$aColumns = array('mode_id', 'mode_name', 'mode_status', 'cdate', 'user_id');
			$sIndexColumn = "mode_id";
			$sTable = "payment_mode";
			$isWhere = array("mode_status = 1","user_id in (0,$_SESSION[user_id])");
			$isJOIN = array();
			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = ($row['mode_name']);

				//if($row['user_id']!="0")
				{
				$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_bank('.$row['mode_id'].')"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bank('.$row['mode_id'].')"><i class="fa fa-trash-o"></i></button>
				';}
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
				$tr = $dbcon -> query("SELECT `mode_id`,`mode_status` FROM `payment_mode` WHERE `mode_name` = '$POST[name]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						$query = $dbcon -> query("UPDATE `payment_mode` SET `mode_status` = 1 WHERE `bankt_id` = '$r[mode_id]'  AND `user_id` = '$_SESSION[user_id]'");
						if($query)
							echo "1";
						else
							echo "0";
					}
					else {
						echo '-1';
					}
				}
				else {
					$query = $dbcon -> query("INSERT INTO `payment_mode`(`mode_name`, `user_id`) VALUES ('".($POST['name'])."','$_SESSION[user_id]')");					
					if($query)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `payment_mode` WHERE `mode_id` = '$POST[id]'  AND `user_id` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			$r['mode_name']=($r['mode_name']);
			echo json_encode($r);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$date = date("Y-m-d H:i:s");
				$str = "
					UPDATE
						`payment_mode`
					SET 
						`mode_name`= '".($POST['name'])."',						
						`cdate`= '$date'
					WHERE
						`mode_id`= '$POST[id]' AND `user_id` = '$_SESSION[user_id]'
				";
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$query = $dbcon -> query("UPDATE `payment_mode` SET `mode_status` = 0 WHERE `mode_id` = '$POST[id]' AND `user_id` = '$_SESSION[user_id]'");
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