<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
//    if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['type']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('acc_id', 'acc_bank', 'bank_name', 'bank_branch','bank_city','acc_name', 'acc_number', 'acc_chequeno', 'acc_chequeleft','acc_status', 'acc_tmst', 'acc_of');
			$sIndexColumn = "acc_id";
			$sTable = "coro_accounts";
			$isWhere = array("acc_status = 1","acc_of=$_SESSION[user_id]");
			$isJOIN = array("INNER JOIN `coro_banks` ON `acc_bank` = `bank_id`");

			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = ($row['acc_name']);
				$row_data[] = ($row['acc_number']);
				$row_data[] = ($row['bank_name']);
				$row_data[] = ($row['bank_branch']);
				$row_data[] = ($row['bank_city']);
				$row_data[] = ($row['acc_chequeleft']);
				
				$row_data[] = '
					<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_account('.$row['acc_id'].')"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_account('.$row['acc_id'].')"><i class="fa fa-trash-o"></i></button>
				';
				//<button class="btn btn-xs btn-default" data-original-title="Added On '.date("d-m-Y g:i a",strtotime($row['cust_tmst'])).'" data-toggle="tooltip" data-placement="left"><i class="fa fa-clock-o"></i></button>/
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['type']) == "add") {
			if($_POST['token'] == $_SESSION['token']) {
				$tr = $dbcon -> query("SELECT `acc_id`,`acc_status` FROM `coro_accounts` WHERE `acc_bank` = '$POST[bank]' AND `bank_branch` = '$POST[branch]' AND `bank_city` = '$POST[city]' AND `acc_number` = '$POST[number]' AND `acc_of` = '$_SESSION[user_id]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						$query = $dbcon -> query("UPDATE `coro_accounts` SET `acc_status` = 1 WHERE `acc_id` = '$r[acc_id]'  AND `acc_of` = '$_SESSION[user_id]'");
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
					
					$query = $dbcon -> query("INSERT INTO `coro_accounts`(`acc_bank`,`bank_branch`,`bank_city`, `acc_name`, `acc_number`, `acc_chequeno`, `acc_chequeleft`,`acc_of`) VALUES ('".$POST['bank']."','".($POST['branch'])."','".($POST['city'])."','".($POST['name'])."','".($POST['number'])."','".($POST['cheque'])."','".($POST['cheque_num'])."','$_SESSION[user_id]')");					
					if($query)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `coro_accounts` WHERE `acc_id` = '$POST[id]'  AND `acc_of` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$date = date("Y-m-d H:i:s");
				$str = "
					UPDATE
						`coro_accounts`
					SET
						`acc_bank` = '$POST[bank]',
						`bank_branch` = '$POST[branch]',
						`bank_city` = '$POST[city]',
						`acc_name`= '$POST[name]',
						`acc_number`= '$POST[number]',
						`acc_chequeno`= '$POST[cheque]',
						`acc_chequeleft` = '$POST[cheque_num]',
						`acc_tmst`= '$date'
					WHERE
						`acc_id`= '$POST[id]' AND `acc_of` = '$_SESSION[user_id]'
				";
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$query = $dbcon -> query("UPDATE `coro_accounts` SET `acc_status` = 0 WHERE `acc_id` = '$POST[id]' AND `acc_of` = '$_SESSION[user_id]'");
				if($query)
					echo "1";
				else
					echo "0";
			}
		}
    /*}
    else {
        die("Error - 2");
    }
}
else {
    die("Error - 1");
}*/
?>
