<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../config/function_database_query.php");
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
            
			$aColumns = array('entryid', 'typeid','acc.acc_name','pmode.mode_name','cust.cust_name','mst.amount','mst.entry_date', 'mst.user_id','mst.status','acc.bank_branch');
			$sIndexColumn = "entryid";
			$sTable = "tbl_passbookentry as mst";
			$isWhere = array("mst.status = 0  ","mst.user_id in (0,$_SESSION[user_id])");
            
            //filter type debit/credit
            if(isset($POST['typeid']) && $POST['typeid'] != -1 && $POST['typeid'] != 0) {
		        array_push($isWhere,'mst.typeid = '.$POST['typeid']);
		        $_SESSION['ch_typeid'] = $POST['typeid'];
		    }
            //filter account of company
            if(isset($POST['account']) && $POST['account'] != -1 && $POST['account'] != 0) {
		        array_push($isWhere,"mst.acc_id = ".$POST['account']);
		        $_SESSION['ch_account'] = $POST['account'];
		    }
            //filter payment mode cheque/cash/neft/rtgs
            if(isset($POST['pmode']) && $POST['pmode'] != -1 && $POST['pmode'] != 0) {
		        array_push($isWhere,"mst.paymentmodeid = ".$POST['pmode']);
		        $_SESSION['ch_pmode'] = $POST['pmode'];
		    }
            //filter customer
            if(isset($POST['payee']) && $POST['payee'] != -9 && $POST['payee'] != 0) {
		        array_push($isWhere,"mst.customer_id = ".$POST['payee']);
		        $_SESSION['ch_payee'] = $POST['payee'];
		    }
            //filter date rage
            if(isset($POST['date']) && $POST['date'] != -9 && $POST['date'] != 0) {
                $temp = explode(",",$POST['date']);
                array_push($isWhere,"(`entry_date` >= '$temp[0]' AND `entry_date` <= '$temp[1]')");
                $_SESSION['ch_sdate'] = $temp[0];
                $_SESSION['ch_edate'] = $temp[1];
            }
            //filter amount
            if(isset($POST['amount']) && $POST['amount'] != -9 && $POST['amount'] != 0) {
                array_push($isWhere,"mst.amount = '$POST[amount]'");
                $_SESSION['ch_amnt'] = $POST['amount'];
            }
			$isJOIN = array("left join coro_accounts as acc on acc.acc_id=mst.acc_id","left join payment_mode as pmode on pmode.mode_id=mst.paymentmodeid","left join coro_customers as cust on cust.cust_id=mst.customer_id");
			include('pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = ($row['typeid']=="1"?'DEBIT':'CREDIT');
                $row_data[] = ($row['acc_name'].'-'.$row['bank_branch']);
                $row_data[] = ($row['mode_name']);
                $row_data[] = ($row['cust_name']);
                $row_data[] = ($row['amount']);
                $row_data[] = date('d-m-Y',strtotime($row['entry_date']));
				//if($row['user_id']!="0")
				{
				$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_entry('.$row['entryid'].')"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_entry('.$row['entryid'].')"><i class="fa fa-trash-o"></i></button>
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
                /*
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
				else {*/
                        $info['typeid']         = $POST['typeid'];
                        $info['acc_id']         = $POST['acc_id'];
                        $info['paymentmodeid']  = $POST['paymentmodeid'];
                        $info['amount']         = $POST['amount'];
                        $info['entry_date']     = date('Y-m-d',strtotime($POST['entry_date']));
                        $info['customer_id']    = $POST['customer_id'];
                        $info['passbook_note']  = $POST['passbook_note'];
                        $info['cdate']          = date('Y-m-d');
                        $info['user_id']        = $_SESSION['user_id'];
                       $insertid=add_record('tbl_passbookentry', $info, $dbcon);	
					if($insertid)
						echo "1";
					else
						echo "0";
				//}
			}
		}
		else if(strtolower($POST['type']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_passbookentry` WHERE `entryid` = '$POST[id]'  AND `user_id` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			$r['edate']=date('d-m-Y',strtotime($r['entry_date']));
			echo json_encode($r);
		}
		else if(strtolower($POST['type']) == "edit") {
			if($_POST['token'] == $_SESSION['token']) {
				
				$info['typeid']         = $POST['typeid'];
                $info['acc_id']         = $POST['acc_id'];
                $info['paymentmodeid']  = $POST['paymentmodeid'];
                $info['amount']         = $POST['amount'];
                $info['entry_date']     = date('Y-m-d',strtotime($POST['entry_date']));
                $info['customer_id']    = $POST['customer_id'];
                $info['passbook_note']  = $POST['passbook_note'];
                $info['cdate']          = date('Y-m-d');
                $info['user_id']        = $_SESSION['user_id'];
                $updateid=update_record('tbl_passbookentry', $info,"entryid=".$POST['edit_id'], $dbcon);
                
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
				
			}
		}
		else if(strtolower($POST['type']) == "delete") {
			if($_POST['token'] == $_SESSION['token']) {
            $info['status']='2';
				 $updateid=update_record('tbl_passbookentry', $info,"entryid=".$POST['id'], $dbcon);
                
				if($updateid)
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