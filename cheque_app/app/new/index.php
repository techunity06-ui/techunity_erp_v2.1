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
		if(strtolower($POST['type']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('cust_id', 'cust_name', 'cust_city', 'cust_phone', 'cust_mail', 'cust_pan','cust_panimg', 'cust_status', 'cust_tmst', 'cust_of');
			$sIndexColumn = "cust_id";
			$sTable = "coro_customers";
			$isWhere = array("cust_status = 1");
			$isJOIN = array();

			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['cust_name'];
				$row_data[] = $row['cust_city'];
				$row_data[] = '<a target="_blank" href="tel:+91'.$row['cust_phone'].'">'.$row['cust_phone'].'</a>';
				$row_data[] = '<a target="_blank" href="mailto:'.$row['cust_mail'].'">'.$row['cust_mail'].'</a>';
				if($row['cust_panimg'] != NULL && $row['cust_panimg'] != "")
					$row_data[] = '<a target="_blank" href="'.DOMAIN.'upload/pan/'.$row['cust_panimg'].'">'.$row['cust_pan'].'</a>';
				else
					$row_data[] = $row['cust_pan'];
				$row_data[] = '
					<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_customer('.$row['cust_id'].')"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_customer('.$row['cust_id'].')"><i class="fa fa-trash-o"></i></button>
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
				$tr = $dbcon -> query("SELECT `cust_id`,`cust_status` FROM `coro_customers` WHERE `cust_name` = '$POST[name]' AND `cust_phone` = '$POST[phone]' AND `cust_of` = '$_SESSION[user_id]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						$query = $dbcon -> query("UPDATE `coro_customers` SET `cust_status` = 1 WHERE `cust_id` = '$r[cust_id]' AND `cust_of` = '$_SESSION[user_id]'");
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
					$query = $dbcon -> query("INSERT INTO `coro_customers`(`cust_name`, `cust_city`, `cust_phone`, `cust_mail`, `cust_pan`, `cust_panimg`, `cust_of`) VALUES ('$POST[name]','$POST[city]','$POST[phone]','$POST[mail]','$POST[pannumber]','$POST[panphotoid]','$_SESSION[user_id]')");
					if($query)
						echo "1";
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `coro_customers` WHERE `cust_id` = '$POST[id]' AND `cust_of` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$date = date("Y-m-d H:i:s");
				$str = "
					UPDATE
						`coro_customers`
					SET 
						`cust_name`= '$POST[name]',
						`cust_city`= '$POST[city]',
						`cust_phone`= '$POST[phone]',
						`cust_mail`= '$POST[mail]',
						`cust_pan`= '$POST[pannumber]',
						`cust_tmst`= '$date'
					WHERE
						`cust_id`= '$POST[id]' AND `cust_of` = '$_SESSION[user_id]'
				";
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$query = $dbcon -> query("UPDATE `coro_customers` SET `cust_status` = 0 WHERE `cust_id` = '$POST[id]' AND `cust_of` = '$_SESSION[user_id]'");
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