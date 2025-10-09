<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../include/common_functions/common_functions.php");
include("../../../config/session.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
    {
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['type']) == "fetch") {
                        //$where = 'l_status =0 and l_group = 37 ';
			$appData = array();
			$i=1;
			$aColumns = array('ven.l_id as vender_id', 'ven.l_name as vender_name','state_name', 'city_name', 'ven.cust_mobile as vender_mobile', 'ven.cust_email as vender_email', 'ven.l_status as vender_status', 'ven.cdate', 'ven.user_id');
			$sIndexColumn = "l_id";
			$sTable = "tbl_ledger as ven";
			$isWhere = array('l_status =0 and l_group = 37 '.check_user('ven'));
			$isJOIN = array("left join state_mst as state on state.stateid=ven.stateid","left join city_mst as city on city.cityid=ven.cityid");
			$sOrder = "ORDER BY l_name";
			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['vender_name'];
                                $row_data[] = $row['state_name'];
				$row_data[] = $row['city_name'];
				if(!empty($row['vender_mobile']))
					$row_data[] = '<a target="_blank" href="tel:+91'.$row['vender_mobile'].'">'.$row['vender_mobile'].'</a>';
				else
					$row_data[] = '-';
				if(!empty($row['vender_email']))
					$row_data[] = '<a target="_blank" href="mailto:'.$row['vender_email'].'">'.$row['vender_email'].'</a>';
				else
					$row_data[] = '-';
				
				// $row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_customer('.$row['vender_id'].')"><i class="fa fa-pencil"></i></button>
				//     <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_customer('.$row['vender_id'].')"><i class="fa fa-trash-o"></i></button>
				// ';
				//<button class="btn btn-xs btn-default" data-original-title="Added On '.date("d-m-Y g:i a",strtotime($row['cust_tmst'])).'" data-toggle="tooltip" data-placement="left"><i class="fa fa-clock-o"></i></button>/<a target="_blank" href="'.DOMAIN_CHEQUE.'export/payee?id='.$row['vender_id'].'"><button class="btn btn-xs btn-default" data-original-title="Print" data-toggle="tooltip" data-placement="top"><i class="fa fa-print"></i></button></a>
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['type']) == "add") {
			if($_POST['token'] == $_SESSION['token']) {
				$tr = $dbcon -> query("SELECT `vender_id`,`vender_status` FROM `tbl_vender` WHERE `vender_name` = '$POST[name]' AND `vender_mobile` = '$POST[phone]' AND `company_id` = '$_SESSION[company_id]'");
				if($tr->num_rows > 0) {
					$r = $tr -> fetch_assoc();
					if($r['cust_status'] == 0) {
						$query = $dbcon -> query("UPDATE `tbl_vender` SET `vender_status` = 1 WHERE `vender_id` = '$r[vender_id]' AND `vender_id` = '$_SESSION[user_id]'");
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
                      $query = $dbcon -> query("INSERT INTO `tbl_vender`(`vender_name`, `vender_address`,`stateid`,`cityid`, `vender_mobile`, `vender_email`,`vendor_cat`, `user_id`,`company_id`) VALUES ('$POST[name]','$POST[address]','$POST[stateid]','$POST[cityid]','$POST[phone]','$POST[mail]','2','$_SESSION[user_id]','$_SESSION[company_id]')");
					if(!empty($_POST['modaladd']) || $query)
					{
						if(!empty($_POST['modaladd']))
							echo "1@@".mysqli_insert_id($dbcon);
						else if($query)
							echo "1";
						
					}					
					else
						echo "0";
				}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_ledger` WHERE `l_id` = '$POST[id]' AND `company_id` = '$_SESSION[company_id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
			
		}
        else if(strtolower($POST['type']) == "load_city") {			
			echo getcity($dbcon,$POST['id'],0);
		}
		else if(strtolower($POST['type']) == "edit") {
            
			//if($_POST['edit_token'] == $_SESSION['token']) 
            {
				$date = date("Y-m-d H:i:s");
                $str = "UPDATE
							`tbl_vender`
						SET 
							`vender_name`= '$POST[name]',
							`vender_address`= '$POST[address]',
                            `stateid`= '$POST[stateid]',
							`cityid` = '$POST[cityid]',
							`vender_mobile`= '$POST[phone]',
							`vender_email`= '$POST[mail]',
							`vendor_cat`='2',
							`cdate`= '$date'
						WHERE
							`vender_id`= '$POST[edit_id]' AND `user_id` = '$_SESSION[user_id]'
					";
				
				if($dbcon -> query($str))
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
				$query = $dbcon -> query("UPDATE `tbl_vender` SET `vender_status` = 2 WHERE `vender_id` = '$POST[id]' AND `user_id` = '$_SESSION[user_id]'");
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
}
/*else {
    die("Error - 1");
}*/
?>