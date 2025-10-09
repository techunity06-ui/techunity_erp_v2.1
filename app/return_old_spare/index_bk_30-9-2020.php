<?php

session_start(); //start session
$AJAX = true;
include("../../config/config.php");
///error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
//print_r($_FILES);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

	if(strtolower($POST['mode']) == "fetch") {
	
		$where='';
		if($_SESSION['user_type']=='3'){
			$emp_id=getEmployeeIdUser($dbcon,$_SESSION['user_id']);
			$where.=" and s_emp_id='$emp_id'";
		}
		
		if($POST['s_return_status']!=''){
			$where.=" and pr.s_return_status='".$POST['s_return_status']."'";
		}
		
		
		$appData = array();
		$i=1;
		$aColumns = array('pr.s_id', 'cust.l_name','cit.city_name', 'c.complaint_no', 'pm.product_name', 'pr.sc_qty', 'pr.sc_rate', 'pr.sc_amount', 'c.complaint_date' ,'pr.c_type', 'pr.s_return_status', 'pr.sc_comp_id','pr.sc_product','pr.courier_name','pr.courier_no','pr.courier_del_date','pr.s_emp_id','pr.sc_remark','l.l_name as emp_name');
		$sIndexColumn = "pr.s_id";
		$isWhere = array("c.complaint_status = 0 ".$where);
		$sTable = "tbl_complain_close_spare_part as pr";
		$isJOIN = array('inner join product_mst as pm on pm.product_id=pr.sc_product', 'left join tbl_complaint as c on c.complaint_id=pr.sc_comp_id','left join tbl_ledger as cust on cust.l_id=c.cust_id','left join tbl_ledger as l on l.l_id=pr.s_emp_id','left join city_mst as cit on cit.cityid=cust.cityid');
		$hOrder = "c.complaint_date desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = "<strong>".$row['l_name']."</strong> - ".$row['city_name'];
			$row_data[] = $row['complaint_no'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['sc_qty'];
			$row_data[] = $row['sc_rate'];
			$row_data[] = $row['sc_amount'];
			
			//Courier Details
			$cour_det='';
			if($row['c_type']==2) {
				$date='';
				if($row['courier_del_date']!='1970-01-01' || $row['courier_del_date']!='0000-00-00'){
					$date=date("d-M-Y",strtotime($row['courier_del_date']));
				}
				$cour_det='<b>Courier Name: </b>'.$row['courier_name'].'</br>
							<b>Courier No: </b>'.$row['courier_no'].'<br>
							<b>Courier Date: </b>'.$date.'<br>
							<b>'.$row['sc_remark'].'</b>';
			} 
			else if($row['c_type']==1) {
				$cour_det='<b>By Hand<b><br/><b>'.$row['sc_remark'].'</b>';
			}
			$row_data[] = $cour_det;

			$row_data[] = $row['emp_name'];
		
			//Spare Parts Button
			if($row['s_return_status']=='0') {
				if($_SESSION['user_type']!=3) {
					$btn_request="<a class='btn btn-warning' href=".ROOT."return_part/".$row['s_id']." >Receive Spares <i class='fa fa-arrow-circle-down'></i></a>";
				}
				else{
					$btn_request="<button type='button' class='btn btn-warning'>Pending</button>";
				}
			}
			else {
				$btn_request='<button type="button" class="btn btn-success">Spare Received <i class="fa fa-check"></i></button>';
			}
			
			
			$row_data[] = $btn_request;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>