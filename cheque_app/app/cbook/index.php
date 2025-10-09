<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
    if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {
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
			$aColumns = array('cbook_id', 'cbook_acc', 'cbook_total', 'cbook_used', 'cbook_active', 'cbook_tmst', 'cbook_of', 'bank_name', 'acc_name');
			$sIndexColumn = "cbook_id";
			$sTable = "coro_chequebooks";
			$isWhere = array("`cbook_of` = '$_SESSION[user_id]'");
			$isJOIN = array(
				"INNER JOIN `account_mst` as acc ON `cbook_acc` = acc.acc_name",
				"INNER JOIN `bank_mst` as bank ON bank.bankid = acc.bankid"
			);

			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['acc_name'];
				$row_data[] = $row['bank_name'];
				$row_data[] = $row['cbook_total'];
				
				$row_data[] = '
					<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_bank('.$row['bank_id'].')"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bank('.$row['bank_id'].')"><i class="fa fa-trash-o"></i></button>
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
				$tr = $dbcon -> query("SELECT `bankid`,`bank_status` FROM `bank_mst` WHERE `bank_name` = '$POST[name]' AND `bank_branch` = '$POST[branch]' AND `bank_city` = '$POST[city]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						$query = $dbcon -> query("UPDATE `bank_mst` SET `bank_status` = 1 WHERE `bankid` = '$r[bank_id]'  AND `userid` = '$_SESSION[user_id]'");
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
					$query = $dbcon -> query("INSERT INTO `bank_mst`(`bank_name`, `bank_branch`, `bank_city`, `bank_of`) VALUES ('$POST[name]','$POST[branch]','$POST[city]','$_SESSION[user_id]')");
					if($query)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `coro_banks` WHERE `bank_id` = '$POST[id]'  AND `bank_of` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$date = date("Y-m-d H:i:s");
				$str = "
					UPDATE
						`coro_banks`
					SET 
						`bank_name`= '$POST[name]',
						`bank_city`= '$POST[city]',
						`bank_branch`= '$POST[branch]',
						`bank_tmst`= '$date'
					WHERE
						`bank_id`= '$POST[id]' AND `bank_of` = '$_SESSION[user_id]'
				";
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$query = $dbcon -> query("UPDATE `coro_banks` SET `bank_status` = 0 WHERE `bank_id` = '$POST[id]' AND `bank_of` = '$_SESSION[user_id]'");
				if($query)
					echo "1";
				else
					echo "0";
			}
		}
    }
    else {
        die("Error - 2");
    }
}
else {
    die("Error - 1");
}
?>
