<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include("../../../include/payroll_common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
} else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	$appData = array();
	$i=1;
	$companyID = $_SESSION['company_id'];
	$userID =  $_SESSION['user_id'];
	$aColumns = array('payrollemp.id', 'payrollemp.category_name', 'payrollemp.max_exemption_amount', 'payrollemp.status', 'comp.company_name','payrollcat.category_name as parent_cat_name');
	$sIndexColumn = "payrollemp.id";
	$isWhere = array("payrollemp.status IN (0,1) and payrollemp.parent_id != '0' and payrollemp.company_id = $companyID".check_user('payrollemp'));
	$sTable = "payroll_emp_tax_exemption_cat_sub as payrollemp";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id=payrollemp.company_id",
					"left join payroll_emp_tax_exemption_cat_sub as payrollcat on payrollcat.id=payrollemp.parent_id");
	$hOrder = "payrollemp.category_name ASC";
	include('../../../include/pagging.php');
	$appData = array();

	$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
	$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
	$other_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'other',$dbcon);

	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['company_name'];
		$row_data[] = $row['parent_cat_name'];
		$row_data[] = $row['category_name'];
		$row_data[] = $row['max_exemption_amount'];
		if($row['status']=='0'){
			$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
		}else{
			$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
		}
		$edit_btn='';$delete_btn='';$change_status='';

		if($edit_btn_per){
			$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onclick="edit_test('.$row['id'].');"><i class="fa fa-pencil"></i></button>';
		}

		if($delete_btn_per){
			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onclick="delete_catalog('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
		}

		if($other_btn_per) {
			if($row['status'] == '0')
			{  
				$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
			} else {
				$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
			}
		}
		$row_data[] = $edit_btn.' '.$delete_btn.' '.$change_status; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	
	$row['res']='';
	$tr = $dbcon -> query("SELECT `id`,`category_name`, `max_exemption_amount`, `status` FROM `payroll_emp_tax_exemption_cat_sub` WHERE `category_name` = '$POST[category_name]'");
	if($tr->num_rows > 0) {
		$r = $tr -> fetch_assoc();
		if($r['status'] != 0) {
			$info['status']=0;
			$updateid=update_record('payroll_emp_tax_exemption_cat_sub', $info,"id=".$r['id'] , $dbcon);

			$row['res'] = ($updateid) ? '1' : '0';
		} else {
				$row['res']='-1';
		}	
	} else {
		$info['user_id'] = $_SESSION['user_id'];
		$info['company_id'] = $_SESSION['company_id'];
		$info['parent_id'] = $_POST['parent_id'];
		$info['category_name'] = $_POST['category_name'];
		$info['max_exemption_amount'] = $_POST['max_exemption_amount'];
		$info['status'] = $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");
		$inserid = add_record('payroll_emp_tax_exemption_cat_sub', $info, $dbcon);

		if($inserid) {
			if(strtolower($POST['model'])=="model") {
				$query="select * from payroll_emp_tax_exemption_cat_sub where id=".$inserid;
				$rel=mysqli_fetch_assoc($dbcon->query($query));		
				$row = $rel;
				$row['res']="2"; 
			} else {
				$row['res'] ="1";
			}
		} else {
			$row['res'] ="0";
		}	
	}

	echo json_encode($row);
	
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `payroll_emp_tax_exemption_cat_sub` WHERE `id` = '$POST[id]'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	$row = array();
	if($_POST['token'] == $_SESSION['token']) {
		$info['parent_id'] = $_POST['parent_id'];
		$info['category_name']= $_POST['category_name'];
		$info['max_exemption_amount']= $_POST['max_exemption_amount'];
		$info['status'] = $POST['status'];
		$info['updated_at']	= date("Y-m-d H:i:s");			
		$updateid = update_record('payroll_emp_tax_exemption_cat_sub', $info,"id=".$POST['eid'] , $dbcon);
		
		$row['res'] = ($updateid) ? "1" : "0";
	} else {
		$row['res'] = "0";
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "delete") {
	if($_POST['token'] == $_SESSION['token']) {
		$info['status']='2';
		$updateid=update_record('payroll_emp_tax_exemption_cat_sub', $info,"id=".$POST['eid'] , $dbcon);
		
		echo ($updateid) ? "1" : "0";
	} else {
		echo  "0";
	}
}
else if(strtolower($POST['mode']) == "change_status") {
	$p_status = $POST['p_status'];

	$info['status'] = ($p_status=='0') ? '1' : '0';
	$info['updated_at']	= date("Y-m-d H:i:s");

	$updateid = update_record('payroll_emp_tax_exemption_cat_sub', $info,"id=".$POST['eid'] , $dbcon);
	echo ($updateid) ? "1" : "0";
}