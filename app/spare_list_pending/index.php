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
		
		if($POST['sp_sent_status']){
			$where.=" and pr.sp_sent_status='".$POST['sp_sent_status']."'";
		}
		
		if($POST['s_inv_status']!=''){
			$where.=" and pr.s_inv_status='".$POST['s_inv_status']."'";
		}
		
		
		$appData = array();
		$i=1;
		$aColumns = array('pr.s_id', 'cust.l_name','cit.city_name', 'c.complaint_no', 'pm.product_name', 'pr.s_qty', 'pr.s_rate', 'pr.s_amount', 'pr.s_date' ,'pr.c_type', 'pr.s_inv_status', 'pr.sp_sent_status', 'pr.s_comp_id','s_cust_id','pr.s_user_id','pr.s_product','pr.s_courier_name','pr.s_courier_no','pr.s_courier_del_date','pr.s_status','pr.s_emp_id','pr.c_remark','l.l_name as emp_name');
		$sIndexColumn = "pr.s_id";
		$isWhere = array("pr.company_id=".$_SESSION['company_id']." and c.complaint_status = 0 ".$where);
		$sTable = "tbl_complain_spare_part as pr";
		$isJOIN = array('left join product_mst as pm on pm.product_id=pr.s_product', 'left join tbl_complaint as c on c.complaint_id=pr.s_comp_id','left join tbl_ledger as cust on cust.l_id=c.cust_id','left join tbl_ledger as l on l.l_id=pr.s_emp_id','left join city_mst as cit on cit.cityid=cust.cityid');
		$hOrder = "pr.s_date desc";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = "<strong>".$row['l_name']."</strong> - ".$row['city_name'];
			$row_data[] = $row['complaint_no'];
			$row_data[] = $row['product_name'];
			$row_data[] = $row['s_qty'];
			$row_data[] = $row['s_rate'];
			$row_data[] = $row['s_amount'];
			$row_data[] = date('d M, Y',strtotime($row['s_date']));
			
			//Courier Details
			$cour_det='';
			if($row['c_type']==2) {
				$date='';
				if($row['s_courier_del_date']!='1970-01-01' || $row['s_courier_del_date']!='0000-00-00'){
					$date=date("d-M-Y",strtotime($row['s_courier_del_date']));
				}
				$cour_det='<b>Courier Name: </b>'.$row['s_courier_name'].'</br>
							<b>Courier No: </b>'.$row['s_courier_no'].'<br>
							<b>Courier Date: </b>'.$date.'<br>
							<b>'.$row['c_remark'].'</b>';
			} 
			else if($row['c_type']==1) {
				$cour_det='<b>By Hand<b><br/><b>'.$row['c_remark'].'</b>';
			}
			$row_data[] = $cour_det;

			$row_data[] = $row['emp_name'];
			
			//Invoice Status
			if($row['s_inv_status']=='1'){
				$row_data[] = '<a class="btn btn-success">Done</a>';
			}
			else{
				$row_data[] = '<a class="btn btn-warning">Pending</a>';
			}
			
			//Spare Parts Button
			//Amish Soni 9-12-2020
			$int_cln_qry = "SELECT * FROM tbl_internal_chalan 
				WHERE complaint_id = '".$row['s_comp_id']."' AND sp_id = '".$row['s_id']."'";
			
			$rs_type = $dbcon->query($int_cln_qry);
			$totalRecords = brp_mysqli_num_rows($rs_type);

			$int_chalan_btn = $print_int_chalan_btn = $report_int_chalan_btn = '';
			if($row['sp_sent_status']=='no') {
				if($_SESSION['user_type']!=3) {
					$btn_request="<a class='btn btn-warning btn-xs' href=".ROOT."send_spare_part/".$row['s_id']." >Send Spares <i class='fa fa-paper-plane'></i></a>";
				}
				else{
					$btn_request="<button type='button' class='btn btn-warning btn-xs'>Pending</button>";
				}

				//Amish Soni 9-12-2020
				if($totalRecords > 0) {
					$int_chalan_btn = '<a href='.ROOT.'edit_internal_chalan/'.$row['s_comp_id'].' class="btn btn-xs btn-primary" data-original-title="Edit Internal Chalan" data-toggle="tooltip" data-placement="top"><i class="fa fa-pencil"></i></a>';
					$chalan = brp_mysqli_fetch_assoc($rs_type);
					$print_int_chalan_btn = '<a href='.ROOT.'print_internal_chalan/'.$row['s_comp_id'].' class="btn btn-xs btn-info" data-original-title="Print Internal Chalan" data-toggle="tooltip" data-placement="top"><i class="fa fa-print"></i></a>';
					if($chalan && $chalan['status'] == 'receive') {		
						$report_int_chalan_btn = '<a href='.ROOT.'report_internal_chalan/'.$row['s_comp_id'].' class="btn btn-xs btn-primary" data-original-title="Report Internal Chalan" data-toggle="tooltip" data-placement="top"><i class="fa fa-print"></i></a>';
					}
				} else {
					$int_chalan_btn = '<a href='.ROOT.'create_internal_chalan/'.$row['s_comp_id'].' class="btn btn-xs btn-primary" data-original-title="Create Internal Chalan" data-toggle="tooltip" data-placement="top"><i class="fa fa-plus"></i></a>';
				}
			}
			else {
				$btn_request='<button type="button" class="btn btn-success">Spare Sent <i class="fa fa-check"></i></button>';
			}
			
			//Amish Soni 9-12-2020
			$row_data[] = $btn_request .' '. $int_chalan_btn. ' '. $print_int_chalan_btn. ' '. $report_int_chalan_btn;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	
?>