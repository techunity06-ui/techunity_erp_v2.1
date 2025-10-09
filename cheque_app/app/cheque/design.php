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
			$aColumns = array('acc_id', 'acc.bankid', 'bank_name', 'branch_name','acc_name', 'acc_number', 'acc_chequeno', 'acc_status', 'acc.cdate', 'acc.user_id');
			$sIndexColumn = "acc_id";
			$sTable = "account_mst as acc";
			$isWhere = array("acc_status = 1");
			$isJOIN = array("INNER JOIN `bank_mst` as bank ON bank.bankid = acc.bankid");

			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['acc_name'];
				$row_data[] = $row['acc_number'];
				$row_data[] = $row['bank_name'];
				$row_data[] = $row['bank_branch'];
				$row_data[] = $row['acc_chequeno'];
				
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
		else if(strtolower($POST['type']) == "generate") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$tr = $dbcon -> query("SELECT `design_id`,`design_status` FROM `coro_design` WHERE `design_bank` = '$POST[bank]' AND `design_of` = '$_SESSION[user_id]'");
				if($tr->num_rows > 0) {
					/*$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						$query = $dbcon -> query("UPDATE `coro_accounts` SET `acc_status` = 1 WHERE `acc_id` = '$r[acc_id]'  AND `acc_of` = '$_SESSION[user_id]'");
						if($query)
							echo "1";
						else
							echo "0";
					}
					else {*/
						echo '-1';
					//}
				}
				else 
				{
					$bank = $POST['bank'];
					$payee = strtolower(json_encode($POST['payee']));
					$date = strtolower(json_encode($POST['date']));
					$bearer = strtolower(json_encode($POST['bearer']));
					$amounttext = strtolower(json_encode($POST['amounttext']));
					$amountnumber = strtolower(json_encode($POST['amountnumber']));
					$mark = strtolower(json_encode($POST['cmark']));
					$not_more = strtolower(json_encode($POST['not_more']));
					$preview = $dbcon->real_escape_string($_POST['cheque_preview']);
					
					$query = $dbcon -> query("INSERT INTO `coro_design`(`design_bank`, `design_payee`, `design_payeeWidth`, `design_date`, `design_dateWidth`, `design_dateLspace`, `design_amount_text`, `design_amount_textWidth`, `design_amount_textIndent`, `design_amount_textLHeight`, `design_amount_number`, `design_amount_numberWidth`, `design_bearer`, `design_bearerWidth`,`design_mark`, `design_notmore`, `design_notmoreWidth`,`design_preview`,`design_of`) VALUES ('$bank', '$payee','$POST[payee_w]','$date','$POST[date_w]','$POST[date_lspace]','$amounttext','$POST[amounttext_w]','$POST[amounttext_indent]','$POST[amounttext_lheight]','$amountnumber','$POST[amountnumber_w]','$bearer','$POST[bearer_w]','$mark', '$not_more', '$POST[not_more_w]','$preview','$_SESSION[user_id]')");
					if($query)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "update") {
			//var_dump($POST);
			$payee = strtolower(json_encode($POST['payee']));
			$date = strtolower(json_encode($POST['date']));
			$bearer = strtolower(json_encode($POST['bearer']));
			$amounttext = strtolower(json_encode($POST['amounttext']));
			$amountnumber = strtolower(json_encode($POST['amountnumber']));
			$mark = strtolower(json_encode($POST['cmark']));
			$not_more = strtolower(json_encode($POST['not_more']));
			
			$str = "UPDATE
					`coro_design`
				SET
					`design_payee`='$payee',
					`design_payeeWidth`= '$POST[payee_w]',
					`design_date`= '$date',
					`design_dateWidth`= '$POST[date_w]',
					`design_dateLspace`= '$POST[date_lspace]',
					`design_amount_text`= '$amounttext',
					`design_amount_textWidth`= '$POST[amounttext_w]',
					`design_amount_textIndent`= '$POST[amounttext_indent]',
					`design_amount_textLHeight`= '$POST[amounttext_lheight]',
					`design_amount_number`= '$amountnumber',
					`design_amount_numberWidth`= '$POST[amountnumber_w]',
					`design_bearer`= '$bearer',
					`design_bearerWidth`= '$POST[bearer_w]',
					`design_mark`= '$mark',
					`design_notmore`= '$not_more',
					`design_notmoreWidth`= '$POST[not_more_w]'
				WHERE
					`design_id`= '$POST[did]' AND `design_of`= '$_SESSION[user_id]'";
			
			$q = $dbcon->query($str);
			if($q) {
				echo "1";
			}
			else {
				echo "0";
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
						`acc_name`= '$POST[name]',
						`acc_number`= '$POST[number]',
						`acc_chequeno`= '$POST[cheque]',
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