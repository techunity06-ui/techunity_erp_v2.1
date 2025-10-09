<?php

$AJAX = true;
include("../../config/config.php");
include('../key_api.php');
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		//var_dump($POST);
		if(strtolower($POST['mode']) == "add") {
		
		
			$key_check=get_key_api_data($_POST['cust_code'],$_POST['cust_key']);
			$key_val=json_decode($key_check, true);
				
				
			if($key_val['msg']=="fail"){
				$arr['msg']='licence';
				$arr['cid']=$POST['company_id'];
				$arr['back']="licence/".$POST['company_id'];
			}else{
				//echo "UPDATE users SET user_tmst=".$key_val['date']." WHERE company_id= ".$POST['company_id'];
				$query_invoicetype = $dbcon->query("UPDATE users SET user_tmst='".$key_val['date']."' WHERE company_id= ".$POST['company_id']);
				
				$query_invoicetype1 = $dbcon->query("UPDATE tbl_company SET cust_key='".$_POST['cust_key']."' WHERE company_id= ".$POST['company_id']);
				
				$arr['msg']='valid';
				$arr['back']="login";
			}
			echo json_encode($arr);					
		}		
		
?>