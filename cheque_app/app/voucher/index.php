<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
    if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	}
	else {
		$POST = bulk_filter($dbcon,$_GET);
	}	
	if(strtolower($POST['type']) == "init_voucher") {
	    $q = $dbcon -> query("SELECT * FROM `coro_vouchers` WHERE `v_cheque` = '$POST[id]' AND `v_of` = '$_SESSION[user_id]'");
	    if($q -> num_rows == 0) {
		$query = $dbcon -> query("SELECT vender_name, acc_name, bank_name, cheque_number, acc_number, cheque_date, cheque_payee, ROUND(cheque_amount,2) as cheque_amount FROM coro_cheques INNER JOIN `account_mst` as acc ON `acc_id` = `cheque_acc` INNER JOIN `bank_mst` as bank ON bank.`bankid` = acc.`bankid` INNER JOIN `tbl_vender` ON `vender_id` = `cheque_payee` WHERE `cheque_id` ='$POST[id]' AND `cheque_of` = '$_SESSION[user_id]'");
		$r = $query->fetch_assoc();
		$r['cheque_date'] = date("d/m/Y",strtotime($r['cheque_date']));
		$r['cheque_number'] = str_pad($r['cheque_number'], 6, '0', STR_PAD_LEFT);
		$r['mode'] = "1";
		echo json_encode($r);
	    }
	    else {
		$row = $q->fetch_assoc();
		$query = $dbcon -> query("SELECT vender_name, acc_name, bank_name, cheque_number, acc_number, cheque_date, cheque_payee, ROUND(cheque_amount,2) as cheque_amount FROM coro_cheques INNER JOIN `account_mst` as acc ON `acc_id` = `cheque_acc` INNER JOIN `bank_mst` as bank ON bank.`bankid` = acc.`bankid` INNER JOIN `tbl_vender` ON `vender_id` = `cheque_payee` WHERE `cheque_id` = '$POST[id]' AND `cheque_of` = '$_SESSION[user_id]'");
		$r = $query->fetch_assoc();
		$r['cheque_date'] = date("d/m/Y",strtotime($r['cheque_date']));
		$r['cheque_number'] = str_pad($r['cheque_number'], 6, '0', STR_PAD_LEFT);
		$r['v_tds'] = $row['v_tds'];
		$r['v_id'] = $row['v_id'];
		$r['v_rec_name'] = $row['v_rec_name'];
		$r['v_rec_mobno'] = $row['v_rec_mobno'];
		$r['mode'] = "0";
		echo json_encode($r);
	    }
	}
	else if(strtolower($POST['type']) == "generate") {	    
	    $q = $dbcon -> query("SELECT `v_id` FROM `coro_vouchers` WHERE `v_cheque` = '$POST[id]' AND `v_of` = '$_SESSION[user_id]'");
	    if($q -> num_rows == 0) {
		$query = $dbcon -> query("INSERT INTO `coro_vouchers`(`v_cheque`, `v_tds`,`v_rec_name`,`v_rec_mobno`, `v_of`) VALUES ('$POST[id]','$POST[tds]','".strtoupper($POST['rec_name'])."','$POST[rec_mobno]','$_SESSION[user_id]')");
		if($query) {
		    $reply = array("response" => 1, "id" => $dbcon->insert_id);
		    echo json_encode($reply);
		}
		else {
		    echo $dbcon->error;
		    $reply = array("response" => 0, "id" => 0);
		    echo json_encode($reply);
		}
	    }
	    else {			
		$r = $q->fetch_assoc();
		$reply = array("response" => 1, "id" => $r['v_id']);
		echo json_encode($reply);
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