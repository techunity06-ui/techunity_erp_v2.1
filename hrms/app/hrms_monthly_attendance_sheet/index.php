<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include("../../../include/common_functions.php");
include("../../../include/hrms_common_functions.php");

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
	$date = isset($POST['date']) && $POST['date'] ? $POST['date']  : date('m').'-'.date('Y');
	$start_date = "01-".$date;
	$start_date_new = date("Y-m-d", strtotime($start_date));
	$end_date = date("Y-m-t", strtotime($start_date));

	$start_time = strtotime($start_date);
	$end_time = strtotime("+1 month", $start_time);

	$j=1;
	for($i=$start_time; $i<$end_time; $i+=86400) {
	   $monthDays = date('d D', $i);
	   $days[] = "GROUP_CONCAT(if(DAY(`attendance_date`) = $j, IF(`attendance_status` = '1', 'P', IF(`attendance_status` = '2', 'A', NULL)), NULL)) AS `$monthDays`";
	   $j++;
	}
	$add_days = implode(', ', $days);
	$totalCount = "COUNT(if(`attendance_status`='1', `attendance_status`, NULL)) AS 'totalP'";
	// p($add_days);
	$aColumns = array('id', 'series_id', 'employee_id', 'attendance_date', 'attendance_status', 'status', $add_days,$totalCount);
	$sIndexColumn = "id";
	$isWhere = array("status ='0' and depart.company_id = $companyID AND depart.attendance_date BETWEEN '$start_date_new' AND '$end_date'".check_user('depart'));
	$sTable = "hrms_attendance as depart";			
	$isJOIN = array("left join tbl_company as comp on comp.company_id=depart.company_id");
	$hOrder = "depart.id ASC";
	$hGroupby = array('depart.employee_id'); 
	include('../../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$month_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['employee_id'] ? get_ledger_expense_by_id($dbcon, $row['employee_id']) : '-';
		$month_data = getDatesStatus($start_date, $row['employee_id'], $row['attendance_status'], $row['attendance_date']);
		$i=1;
		for($i=0; $i<count($month_data); $i++) {
			$row_data[] = $row[$month_data[$i]] ? $row[$month_data[$i]] : 'A';
		}
		$row_data[] = $row['totalP'];
		$row_data[] = (($j - 1) - $row['totalP']);
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "load_summary_report") {
	$monthDays = array();
	$companyID = $_SESSION['company_id'];
	$userID =  $_SESSION['user_id'];
	$date = isset($POST['date']) && $POST['date'] ? $POST['date']  : date('m').'-'.date('Y');

	$start_date = "01-".$date;
	$end_date = date("Y-m-t", strtotime($start_date));
	$start_time = strtotime($start_date);
	$end_time = strtotime("+1 month", $start_time);

	for($i=$start_time; $i<$end_time; $i+=86400) {
	   // $monthDays[] = date('Y-m-d D', $i);
	   $monthDays[] = date('d D', $i);
	}

	$dateQuery = "SELECT ha.attendance_date, IF(present, present, 0) present, IF(absent, absent , 0) absent, IF(leaves, leaves, 0) leaves FROM hrms_attendance ha 
	LEFT JOIN (SELECT count(id) as present, attendance_date  FROM hrms_attendance ha1 WHERE attendance_status= '1' GROUP BY attendance_date) as p ON ha.attendance_date = p.attendance_date 
	LEFT JOIN (SELECT count(id) as absent, attendance_date FROM hrms_attendance ha2 WHERE attendance_status= '2' GROUP BY attendance_date) as a ON ha.attendance_date = a.attendance_date
	LEFT JOIN (SELECT count(id) as leaves, attendance_date FROM hrms_attendance ha3 WHERE attendance_status= '3' GROUP BY attendance_date) as l ON ha.attendance_date = l.attendance_date
	WHERE ha.company_id = $companyID and ha.user_id = $userID and ha.status ='0' AND ha.attendance_date BETWEEN '$start_date' AND '$end_date' GROUP BY ha.attendance_date";

	$chart_data = $dbcon->query($dateQuery);
	$rowData = array();
	$i = 0;
	while($dateData=mysqli_fetch_assoc($chart_data))
	{
		$set_date = $dateData['attendance_date'];
		$rowData[$set_date]['present'] = ($dateData['present']);
		$rowData[$set_date]['absent'] = ($dateData['absent']);
		$rowData[$set_date]['leaves'] = ($dateData['leaves']);
		$i++;
	}

	$j = 0;
	for($i=$start_time; $i<$end_time; $i+=86400) {
		$checkDate = date('Y-m-d', $i);
		$date = date('d D', $i);
		$present = (array_key_exists($checkDate,$rowData)) ? $rowData[$checkDate]['present'] : 0;
		$absent = (array_key_exists($checkDate,$rowData)) ? $rowData[$checkDate]['absent'] : 0;
		$leaves = (array_key_exists($checkDate,$rowData)) ? $rowData[$checkDate]['leaves'] : 0;
		$row[$j]['month_date'] = $date;
		$row[$j]['present'] = $present;
		$row[$j]['absent'] = $absent;
		$row[$j]['leaves'] = $leaves;
		$j++;
	}
	echo json_encode($row);
}

function getDatesStatus($start_date, $employee_id, $attendance_status, $attendance_date)
{
	$data = array();
	$start_time = strtotime($start_date);
	$end_time = strtotime("+1 month", $start_time);
	for($i=$start_time; $i<$end_time; $i+=86400) {
		$checkDate = date('d D', $i);
		$date = date('d', $i);
		$data[] = $checkDate;
		// $data[] = $date;
	}

	return $data;
}