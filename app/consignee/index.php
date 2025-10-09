<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include("../../include/coman_function.php");
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('cust_id', 'company_name','cust_mobile','state.state_name','city.city_name','gst_no','cust_email','cust_status','cust.cdate','cust.user_id');
			$sIndexColumn = "cust_id";
			$isWhere = array("cust_status = 0 and cust.company_id in (0,$_SESSION[company_id])","cust_ref_id=".$POST['customerid']);
			$sTable = "tbl_custmer_consignee as cust";			
			$isJOIN = array('left join state_mst state on cust.stateid=state.stateid','left join city_mst city on cust.cityid=city.cityid');
			$hOrder = "cust.cust_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['city_name'];
				$row_data[] = $row['state_name'];
				$row_data[] = $row['cust_mobile'];
				$row_data[] = $row['gst_no'];
				
				$delete=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_consignee('.$row['cust_id'].')"><i class="fa fa-trash-o"></i></button> ';
				
				$row_data[] = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="javascript:;" onClick="edit_consignee('.$row['cust_id'].')"><i class="fa fa-pencil"></i></a> '.$delete; 
				//<a class="btn btn-xs btn-primary" data-original-title="Label Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.'labourprint/customer/'.$row['cust_id'].'"><i class="fa fa-print"></i></a>
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			 	
					$info['cust_name']		= stripcslashes($_POST['cust_name']);
					$info['cust_ref_id']	= $_POST['consignee_custmerid'];
					$info['company_name']	= stripcslashes($_POST['company_name']);
					//$info['cust_name']		= $POST['cust_name'];
					$info['cust_address']	= text_rnremove($_POST['cust_address']);
					$info['countryid']		= $POST['countryid'];
					$info['stateid']		= $POST['stateid'];
					$info['cityid']			= $POST['cityid'];
					$info['opening_balance']= $POST['opening_balance'];
					$info['balance_typeid']	= $POST['balance_typeid'];
					$info['cust_mobile']	= $POST['cust_mobile'];
					$info['cust_email']		= $POST['cust_email'];
					$info['cust_pincode']	= $POST['cust_pincode'];
					$info['gst_no']			= $POST['gst_no'];
					$info['pan_no']			= $POST['pan_no'];
					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['usertype_id']	= $_SESSION['user_type'];
					$info['company_id']		= $_SESSION['company_id'];
					$inserid=add_record('tbl_custmer_consignee', $info, $dbcon);
					$row['res']='';$row['hide_modal']="";
							
					if($inserid)
					{
						if(strtolower($POST['model'])=="model")
						{
							$query="select * from tbl_custmer_consignee where cust_id=".$inserid;
							$rel=mysqli_fetch_assoc($dbcon->query($query));		
							$row = $rel;
							$row['res']="2"; 
							if($POST['entry_type']=="1"){
								$row['hide_modal']="1"; 
							}
						}
						else
						{
							$row['res'] ="1";
						}
					}
					else
					{
						$row['res'] ="0";
					}
					
			echo json_encode($row);	
		}
		else if(strtolower($POST['mode']) == "edit") {
			
						$info['cust_name']		= $_POST['cust_name'];
						$info['cust_ref_id']	= $_POST['consignee_custmerid'];
						$info['company_name']	= $_POST['company_name'];
						$info['cust_name']		= $POST['cust_name'];
						$info['cust_address']	= text_rnremove($_POST['cust_address']);
						$info['countryid']		= $POST['countryid'];
						$info['stateid']		= $POST['stateid'];
						$info['cityid']			= $POST['cityid'];
						$info['opening_balance']= $POST['opening_balance'];
						$info['balance_typeid']	= $POST['balance_typeid'];
						$info['cust_mobile']	= $POST['cust_mobile'];
						$info['cust_email']		= $POST['cust_email'];
						$info['cust_pincode']	= $POST['cust_pincode'];
						$info['gst_no']			= $POST['gst_no'];
						$info['pan_no']			= $POST['pan_no'];
						$info['cdate']			= date("Y-m-d H:i:s");
						$info['user_id']		= $_SESSION['user_id'];
						$info['usertype_id']	= $_SESSION['user_type'];
						$info['company_id']		= $_SESSION['company_id'];
						
				$updateid=update_record('tbl_custmer_consignee', $info,"cust_id=".$POST['consignee_eid'] , $dbcon);
					$row['res']='';
					$row['cust_id']=$_POST['consignee_custmerid'];
				if($updateid)
				{
					$row['res']='update';
				}
				else
				{
					$row['res']='0';
				}
				echo json_encode($row);
			 
		}
		else if(strtolower($POST['mode']) == "preedit") {		
			$q = $dbcon -> query("SELECT * FROM `tbl_custmer_consignee` WHERE `cust_id` = '$POST[eid]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "delete") {
			
			$info['cust_status']		= 2;
			$updateid=update_record('tbl_custmer_consignee', $info,"cust_id=".$POST['eid'] , $dbcon);				
			if($updateid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "load_state") {
			 	$stateid=$POST['id'];				
				echo $str=getstate($dbcon,$stateid,0);
		}
		else if(strtolower($POST['mode']) == "load_city") {
			 	$cityid=$POST['id'];
				echo $str=getcity($dbcon,$cityid,0);
		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/

?>