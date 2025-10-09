<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		
		
		$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$userid=$_SESSION['user_id'];
		$usertype=$_SESSION['user_type'];
		$emp_id=getEmployeeIdUser($dbcon,$userid);
		
		$appData = array();
		$i=1;
		$aColumns = array('l.l_id', 'l_name','l.l_status','l.cdate','l.user_id');
		$sIndexColumn = "l.l_id";
		$isWhere = array("l.l_status = 0 and l.company_id in (0,$_SESSION[company_id])");
		$sTable = " tbl_ledger as l";
		$isJOIN = array();
		$hOrder = "l.l_id desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['l_name'];
			
			 
			$edit_btn='';$delete_btn='';
			if($edit_btn_per){
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'employee_edit/'.$row['employee_id'].'"><i class="fa fa-pencil"></i></a>'; 
			}
			if($delete_btn_per){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_emp('.$row['employee_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "generate_daily_log_report") {
		
		$date=date('Y-m-d',strtotime($POST['rep_date']));
		$type=$POST['type'];
		$where='';
		$join='';
		$group='';
		
		if($type=='present')
		{
			$aColumns[]='l.uid';
			$where.=" l.attendance='yes' and u.active=0 and DATE(l.in_time)='$date'"; 
			$join.="left join login_history as l on l.uid=u.user_id";
			//$group.="l.uid";
			$hGroupby = array("l.uid");
		}
		else
		{
			
			$where.=" not find_in_set(u.user_id,(SELECT GROUP_CONCAT(DISTINCT l.uid) from login_history as l WHERE l.attendance='yes' and DATE(l.in_time)='$date')) and active=0 and employee_id!=0 and user_type not in(1,2)";
			$group.="";
			$join.="";
		}
		
		$appData = array();
		$i=1;
		$aColumns = array('u.user_id','u.user_name', 'utype.usertype_name','u.employee_id','u.user_type','u.active');
		$sIndexColumn = "u.user_id";
		$sTable = " users as u";
		
		$isJOIN = array($join,"left join tbl_usertype as utype on utype.usertype_id=u.user_type");
		
		$isWhere = array($where);
		
		$hOrder = "u.user_name";
		
		//if($type=='present') {  }
		
		include('../../include/pagging.php');

		$appData = array();
		$id=1;
		//print_r($sqlReturn);
		//exit();
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $id;
			$row_data[] = $row['user_name'];
			$row_data[] = $row['usertype_name'];
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add" || strtolower($POST['employee_mode']) == "add") {
		$tr = $dbcon -> query("SELECT `employee_id`,`employee_name`,`employee_status` FROM `employee_mst` WHERE `employee_name` = '$POST[employee_name]' and `emp_mobile` = '$POST[emp_mobile]' and employee_status=0 and company_id=".$_SESSION['company_id'] );
		if($tr->num_rows > 0) {
			$row['res']='-1';
		}
		else {
			$info['employee_name']	= stripcslashes($POST['employee_name']); 
			$info['employee_address']= $_POST['employee_address'];
			$info['countryid']		= $POST['countryid'];
			$info['stateid']		= $POST['stateid'];
			$info['cityid']			= $POST['cityid'];
			$info['emp_mobile']		= $POST['emp_mobile'];
			$info['emp_email']		= strtolower($POST['emp_email']); 
			$info['emp_password']	= $_POST['emp_password']; 
			$info['zone_id']		= $POST['zone_id'];
			$info['opening_balance']= $POST['opening_balance']; 
			$info['balance_typeid']	= $POST['balance_typeid']; 
			$info['e_type']	= $POST['e_type']; 
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$info['multi_company']	= $POST['multi_company'];
			if(!$POST['multi_company'])
				$info['company_id']			= $_SESSION['company_id'];
			else
				$info['company_id']			= 0;
			$inserid=add_record('employee_mst', $info, $dbcon);
	
		/*Entry in User Table Start*/	
		$infousr['user_name']		= $POST['employee_name']; 
		$infousr['user_mail']		= strtolower($POST['emp_email']); 
		$infousr['user_key']		= md5($_POST['emp_password']);
		$infousr['user_type']		= 3;//Fixed Type Employee
		$infousr['user_country']	= '100';
		$infousr['user_stat']		= $POST['stateid'];
		$infousr['user_city']		= $POST['cityid'];
		$infousr['user_phone']		= $POST['emp_mobile'];
		$infousr['user_address']	= $_POST['employee_address'];
		$infousr['user_rid']		= $_SESSION['user_id'];
		$infousr['company_id']		= $_SESSION['company_id'];
		$infousr['payment_status'] 	= 1;
		$infousr['employee_id'] 	= $inserid;//Employee ID flag check
		$inserusrid=add_record('users', $infousr, $dbcon);
		/*Entry in User Table End*/	
		
		/*Entry in mst account Table Start*/
			
			$infoacct['g_name']		= $POST['employee_name']." Cash Account"; 
			$infoacct['g_description']		= "emp_cash_account"; 
			$infoacct['g_pid']		= "29";
			$infoacct['g_open_balance']		= $POST['opening_balance'];
			$infoacct['balance_typeid']		= $POST['balance_typeid'];
			$infoacct['cdate']		= date("Y-m-d H:i:s");
			$infoacct['user_id']			= $_SESSION['user_id'];
			$infoacct['company_id']		= $_SESSION['company_id'];
			$infoacct['emp_id']		= $inserid;
			$infoacctid=add_record('tbl_group', $infoacct, $dbcon);
			
			
		/*Entry in mast account Table End*/			

			$row['res']='';
			if($inserid){
				if(strtolower($POST['employee_model'])=="employee_model"){
					$query="select * from employee_mst where employee_id=".$inserid;
					$rel=mysqli_fetch_assoc($dbcon->query($query));		
					$row = $rel;
					$row['res']="2"; 
				}
				else{
					$row['res'] ="1";
				}
			}
			else{
				$row['res'] ="0";
			}
		}
		echo json_encode($row);	
	}
	else if(strtolower($POST['mode']) == "edit") {
			$info['employee_name']	= stripcslashes($POST['employee_name']); 
			$info['employee_address']= $_POST['employee_address'];
			$info['countryid']		= $POST['countryid'];
			$info['stateid']		= $POST['stateid'];
			$info['cityid']			= $POST['cityid'];
			$info['emp_mobile']		= $POST['emp_mobile'];
			$info['emp_email']		= strtolower($POST['emp_email']);
			$info['emp_password']	= $_POST['emp_password']; 
			$info['zone_id']		= $POST['zone_id'];
			$info['opening_balance']= $POST['opening_balance']; 
			$info['balance_typeid']	= $POST['balance_typeid'];
			$info['e_type']	= $POST['e_type'];
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$info['multi_company']	= $POST['multi_company'];
			if(!$POST['multi_company'])
				$info['company_id']			= $_SESSION['company_id'];
			else
				$info['company_id']			= 0;
		
		$updateid=update_record('employee_mst', $info,"employee_id=".$POST['eid'] , $dbcon);
	 
	/*Entry in User Table Start*/	
		$infousr['user_name']		= $POST['employee_name']; 
		$infousr['user_mail']		= strtolower($POST['emp_email']); 
		$infousr['user_key']		= md5($_POST['emp_password']);
		//$infousr['user_type']		= 3;//Fixed Type Employee
		$infousr['user_country']	= '100';
		$infousr['user_stat']		= $POST['stateid'];
		$infousr['user_city']		= $POST['cityid'];
		$infousr['user_phone']		= $POST['emp_mobile'];
		$infousr['user_address']	= $_POST['employee_address']; 
		$updusrid=update_record('users', $infousr, "employee_id=".$POST['eid'], $dbcon);
	/*Entry in User Table End*/	

		$row['res']=''; 
		if($updateid){
			$row['res']='update';
		}
		else{
			$row['res']='0';
		}
		echo json_encode($row);
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['employee_status']		= 2;
		$infousr['active']		= 2;
		$updateid=update_record('employee_mst', $info,"employee_id=".$POST['eid'] , $dbcon);
		$updateusrid=update_record('users', $infousr,"employee_id=".$POST['eid'] , $dbcon);	
		
		if($updateid)
			echo "1";	
		else
			echo "0";			
	}
	else if(strtolower($POST['mode']) == "load_state") {
		$countryid=$POST['id'];				
		echo get_state($dbcon,'',$countryid);
	}
	else if(strtolower($POST['mode']) == "load_city") {
		$cityid=$POST['id'];				
		echo $str=getcity($dbcon,$cityid,'');
	}   
	
?>