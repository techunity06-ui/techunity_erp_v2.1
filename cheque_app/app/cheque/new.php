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
		if(strtolower($POST['type']) == "fetch_design") {
			 $q = $dbcon->query("SELECT `acc_chequeno`,`bankid`,`acc_chequeleft` FROM `account_mst` WHERE `acc_id` = '$POST[id]' AND `user_id` in(0,$_SESSION[user_id])");
			$r = $q -> fetch_assoc();
		
			if($r['acc_chequeleft'] != 0) {
				$data = $r;
				 
				$query = $dbcon->query("SELECT * FROM `coro_design` WHERE `design_bank` = '$r[bankid]' AND `design_status` = 1 AND `design_of` in(0,$_SESSION[user_id])");
				if($query -> num_rows == 0) {
					$r['checknum'] = (int)$data['acc_chequeno'];
					$r['checkleft'] = $data['acc_chequeleft'];
					$r['response'] = "0";
					echo json_encode($r,true);
				}
				else {
					$r = $query -> fetch_assoc();
					for($i=0;$i<count($r);$i++) {
						if(json_decode(current($r)) != null) {
							$key = key($r);
							$r[$key] = json_decode(current($r));
						}
						next($r);
					}
					$r['checknum'] = (int)$data['acc_chequeno'];
					$r['checkleft'] = $data['acc_chequeleft'];
					echo json_encode($r,true);
				}
			}
			else {
				die("-1");
			}
		}
		else if(strtolower($POST['type']) == "print") {
			$q = $dbcon->query("SELECT acc.`bankid` FROM `coro_cheques` INNER JOIN `account_mst` as acc ON `cheque_acc` = `acc_id` INNER JOIN `bank_mst` as bank ON bank.`bankid` = acc.`bankid`  WHERE `cheque_id` = '$POST[id]'");
			$r = $q -> fetch_assoc();
			
			$data = $r;
			$query = $dbcon->query("SELECT * FROM `coro_design` WHERE `design_bank` = '$r[bankid]' AND `design_status` = 1 AND `design_of` in(0,$_SESSION[user_id])");
			
			$r = $query -> fetch_assoc();
			for($i=0;$i<count($r);$i++) {
				if(json_decode(current($r)) != null) {
					$key = key($r);
					$r[$key] = json_decode(current($r));
				}
				next($r);
			}
			echo json_encode($r,true);
		}
		else if(strtolower($POST['type']) == "preview") {
			$query = $dbcon->query("SELECT * FROM `coro_design` WHERE `design_bank` = '$POST[id]' AND `design_status` = 1 AND `design_of` in(0,$_SESSION[user_id])");			
			$r = $query -> fetch_assoc();
			for($i=0;$i<count($r);$i++) {
				if(json_decode(current($r)) != null) {
					$key = key($r);
					$r[$key] = json_decode(current($r));
				}
				next($r);
			}
			echo json_encode($r,true);
		}
		else if(strtolower($POST['type']) == "edit_preview") {
			$query = $dbcon->query("SELECT * FROM `coro_design` WHERE `design_id` = '$POST[id]' AND `design_status` = 1 AND `design_of` in(0,$_SESSION[user_id])");
			
			$r = $query -> fetch_assoc();
			for($i=0;$i<count($r);$i++) {
				if(json_decode(current($r)) != null) {
					$key = key($r);
					$r[$key] = json_decode(current($r));
				}
				next($r);
			}
			echo json_encode($r,true);
		}
		else if(strtolower($POST['type']) == "fetch_payee") {
		if($_POST['payee_type']=="Ven"){
                    	$q = $dbcon->query("SELECT l_name as company_name FROM `tbl_ledger` WHERE `l_id` =".$POST['id']);
			$r = $q -> fetch_assoc();
			$name=$r['company_name'];
		}else{
			$query_acc="SELECT acc_name FROM account_mst as acc where acc_id=".$POST['id'];
			$rs_acc=$dbcon->query($query_acc);
			 $rel_acc=mysqli_fetch_assoc($rs_acc);
			 if($_POST['cradit_id']=="1"){
				$name='SELF'; 
			 }else{
			 $name=$rel_acc['acc_name'];
			 }
		}
			/*if($rel_acc['acc_type']==0) 
			{
				  
                   $name=$r['company_name'];
				  //$name='SELF';
			}
			if($r['company_name']=="") 
			{
				  
                   //$name=$r['company_name'];
				  $name='SELF';
			}*/
			echo $name;
		}
		else if(strtolower($POST['type']) == "newcheque") {
			if($_POST['token'] == $_SESSION['token'])
			{
				$Amount = (float) $POST['amount'];
				$POST['date']=date('Y-m-d',strtotime($POST['date']));
				$query = $dbcon -> query("INSERT INTO `coro_cheques`(`cheque_number`, `cheque_acc`, `cheque_date`, `cheque_payee`, `cheque_amount`, `cheque_note`, `cheque_mode`,`cheque_morethen`,paytype, `cheque_of`) VALUES ('$POST[cnumtext]','$POST[bank]','$POST[date]','$POST[payee]','$Amount', '$POST[note]', '$POST[pymode]','$POST[overthan]','$POST[paytype]','$_SESSION[company_id]')");
				 
				$id = $dbcon->insert_id;
				if($query) {
					$status = 1;
					$diff=($POST['cnumtext']-$POST['cnum'])+1;
					
					$dbcon -> query("UPDATE `account_mst` SET `acc_chequeleft` = `acc_chequeleft` - $diff, `acc_chequeno` = ".$POST['cnumtext']." +1 WHERE `acc_id` = '$POST[bank]' AND `company_id` = '$_SESSION[company_id]'");
					
					$reply = array("status" => $status, "id" => $id);
					
					if($_POST['purchase_cheque']>0)//purchase cheque generated
					{
						 
						$dbcon -> query("UPDATE `tbl_payment_cheque_generate` SET `generat_status` = 1, `cheque_id`=$id  WHERE `chequegenerateid` = '$POST[purchase_cheque]' AND `company_id` = '$_SESSION[company_id]'");
						$reply['purchase']=$_POST['purchase_cheque']; 
					}
					else
					{
						
						$query = $dbcon -> query("INSERT INTO `tbl_passbookentry`(`typeid`,`acc_id`,`paymentmodeid`,`amount`,`entry_date`,`customer_id`,`trn_id`,`trn_table`,`passbook_note`,`reference_no`,`reference_date`,`company_id`) VALUES ('1','$POST[bank]','2','$Amount','$POST[date]','$POST[payee]','".$id."','coro_cheques', '$POST[note]', '$POST[cnumtext]','$POST[date]','$_SESSION[company_id]')");
					}
					echo json_encode($reply);
				}
				else {
					$id = -1;
					$status = 0;
					$reply = array("status" => $status, "id" => $id);
					echo json_encode($reply);
				}
			}
			else {
				$id = -1;
				$status = 0;
				$reply = array("status" => $status, "id" => $id);
				echo json_encode($reply);
			}
		}
		else if(strtolower($POST['type']) == "newcbook") {
			$query = $dbcon -> query("UPDATE `coro_accounts` SET `acc_chequeno` = '$POST[series]', `acc_chequeleft` = '$POST[number]' WHERE `acc_id` = '$POST[acc]' AND `acc_of` = '$_SESSION[user_id]'");
			if($query) {
				echo "1";
			}
			else {
				echo "0 ".$dbcon->error;
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

                            
                            