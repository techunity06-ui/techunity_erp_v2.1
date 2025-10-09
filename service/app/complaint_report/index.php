<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');
$incPath = $path.'include/';

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
if(brp_strtolower($POST['mode']) == "get_counts") {
	$userid=$_SESSION['user_id'];
	$usertype=$_SESSION['user_type'];
	$cur_date=date("Y-m-d");

	$where="";

	if($usertype=='3'){
		$emp_id=getEmployeeIdUser($dbcon,$userid);
		$where.="  and emp_id='$emp_id'";
	}

	$livec_query="Select count(complaint_id) as total from tbl_complaint where complaint_status=0 and followup_status!='4' ".$where." ";
	$live_complain=brp_mysqli_fetch_assoc($dbcon->query($livec_query));
	$row['live_complain'] = $live_complain;

	$inst_done_query="Select count(complaint_id) as total from tbl_complaint where complaint_status=0 and followup_status='4' and complaint_type_id ='1' ".$where." ";
	$inst_done=brp_mysqli_fetch_assoc($dbcon->query($inst_done_query));
	$row['inst_done'] = $inst_done;

	$inst_pending_query="Select count(complaint_id) as total from tbl_complaint where complaint_status=0 and followup_status!='4' and complaint_type_id ='1' ".$where." ";
	$inst_pending=brp_mysqli_fetch_assoc($dbcon->query($inst_pending_query));
	$row['inst_pending'] = $inst_pending;

	echo brp_json_encode($row);

} 
else if(brp_strtolower($POST['mode']) == "today_complain_chart") {
	$query = "SELECT COUNT(tc.complaint_id) complaints,sm.state_name FROM `tbl_complaint` tc JOIN `tbl_ledger` tl ON tc.cust_id = tl.l_id JOIN `state_mst` sm ON tl.stateid = sm.stateid WHERE tc.followup_status = '1' AND tc.complaint_date <= curdate() GROUP BY sm.state_name ORDER BY sm.state_name";

	$chart_data = $dbcon->query($query);
	// echo $query;
	$row = array();
	$i = 0;
	while($chart=brp_mysqli_fetch_assoc($chart_data))
	{	
		$row[$i]['state']= $chart['state_name'];
		$row[$i]['total_complaints']= $chart['complaints'];
		$i++;
	}
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "total_complains_chart") {
	$query = "SELECT COUNT(tc.complaint_id) complaints,sm.state_name FROM `tbl_complaint` tc JOIN `tbl_ledger` tl ON tc.cust_id = tl.l_id JOIN `state_mst` sm ON tl.stateid = sm.stateid WHERE tc.followup_status NOT IN('4', '9') GROUP BY sm.state_name ORDER BY sm.state_name";

	$chart_data = $dbcon->query($query);
	// echo $query;
	$row = array();
	$i = 0;
	while($chart=brp_mysqli_fetch_assoc($chart_data))
	{	
		$row[$i]['state']= $chart['state_name'];
		$row[$i]['total_complaints']= $chart['complaints'];
		$i++;
	}
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "category_complaints_chart") {
	$cat_id = $POST['cat_id'] ? $POST['cat_id'] : '';
	$start_date = $POST['start_date'] ? date('Y-m-d',strtotime($POST['start_date'])) : '';
	$end_date = $POST['end_date'] ? date('Y-m-d',strtotime($POST['end_date'])) : '';
	$columns = 'tc.cat_name';
	$groupBy = '';
	$orderBy = '';
	$where = '';
	if($cat_id) {
		$columns = 'pm.product_name';
		$groupBy = ', pm.product_id';
		$orderBy = ', pm.product_id';
		$where .= ' AND tc.cat_id = '.$cat_id;
	}

	if($start_date) {
		$where .= ' AND tcmp.complaint_date >= "'.$start_date.'"';
	}

	if($end_date) {
		$where .= ' AND tcmp.complaint_date <= "'.$end_date.'"';
	}

	$query = "SELECT $columns AS cat_prd, COUNT(tcmp.complaint_id) complaints FROM tbl_category tc JOIN product_mst pm ON tc.cat_id = pm.product_category JOIN tbl_complaint_trn tcr ON pm.product_id = tcr.product_id JOIN tbl_complaint tcmp ON tcmp.complaint_id = tcr.complaint_id WHERE tc.cat_status = 0 AND pm.product_status = 0 AND tcmp.complaint_status = 0 $where GROUP BY tc.cat_id$groupBy ORDER BY tc.cat_id$orderBy";

	$chart_data = $dbcon->query($query);
	// echo $query; die;
	$row = array();
	$i = 0;
	while($chart=brp_mysqli_fetch_assoc($chart_data))
	{	
		$row[$i]['cat_prd']= $chart['cat_prd'];
		$row[$i]['total_complaints']= $chart['complaints'];
		$i++;
	}
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "employee_chart") {
	//Amish Soni 17-09-2020
	$userid = $POST['emp_id'];
	$emp_id=getEmployeeIdUser($dbcon,$userid);
	$where = " and complaint_status=0 and emp_id=$emp_id and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(complaint_date)";
	$query="SELECT m.month,(Select count(complaint_id) as total from tbl_complaint where followup_status IN ('4','9') $where) as closed, (Select count(complaint_id) as total from tbl_complaint where followup_status='5' $where) as not_done, (Select count(complaint_id) as total from tbl_complaint where followup_status='7' $where) as started
		 FROM (
		 		SELECT 'Jan' AS MONTH
				UNION SELECT 'Feb' AS MONTH
				UNION SELECT 'Mar' AS MONTH
				UNION SELECT 'Apr' AS MONTH
				UNION SELECT 'May' AS MONTH
				UNION SELECT 'Jun' AS MONTH
				UNION SELECT 'Jul' AS MONTH
				UNION SELECT 'Aug' AS MONTH
				UNION SELECT 'Sep' AS MONTH
				UNION SELECT 'Oct' AS MONTH
				UNION SELECT 'Nov' AS MONTH
				UNION SELECT 'Dec' AS MONTH
				) AS m
		GROUP BY m.month
		ORDER BY 1+1";

	$data = $dbcon->query($query);
	// echo $query;
	$row = array();
	$i = 0;
	while($chart = brp_mysqli_fetch_assoc($data))
	{	
		$row[$i]['month']= $chart['month'];
		$row[$i]['started']= $chart['started'];
		$row[$i]['completed']= $chart['closed'];
		$row[$i]['not_done']= $chart['not_done'];
		$i++;
	}
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "weekend_complaints_chart") {
	$end_date = $POST['date_val'] ? $POST['date_val'] : date('d-m-Y',strtotime('this Sunday'));
	$product_val = $POST['product_val'] ? $POST['product_val'] : '';
	$start_date =  date('Y-m-d', strtotime('-6 days', strtotime($end_date))); 
	$end_date = date('Y-m-d',strtotime($end_date));
	$start_date = date('Y-m-d',strtotime($start_date));

	$where = ($product_val) ? " AND tcr.product_id = $product_val " : "";
	
	$query = "SELECT DATE_FORMAT(complaint_date,'%d-%m') complaint_date, COUNT(tc.complaint_id) complaints FROM `tbl_complaint` tc JOIN tbl_complaint_trn tcr ON tc.complaint_id = tcr.complaint_id  WHERE complaint_date BETWEEN '$start_date' AND '$end_date' AND followup_status IN ('4','9') $where GROUP BY complaint_date ORDER BY complaint_date";

	// echo $query; die;

	$complaintsData = $dbcon->query($query);
	$totalData = array();
	while($chart=brp_mysqli_fetch_assoc($complaintsData))
	{
		// var_dump($chart);
		$totalData[$chart['complaint_date']] = $chart['complaints'];
	}
	// var_dump($totalData);

	$setQuery = "set @i = -1;";
	$dbcon->query($setQuery);
	$dateQuery = "SELECT DATE_FORMAT(DATE(ADDDATE('$start_date', INTERVAL @i:=@i+1 DAY)),'%d-%m') AS date FROM `tbl_complaint`
		HAVING 
		@i < DATEDIFF('$end_date', '$start_date')";

	$chart_data = $dbcon->query($dateQuery);
	$row = array();
	$i = 0;
	while($dateData=brp_mysqli_fetch_assoc($chart_data))
	{
		$set_date = $dateData['date'];
		$count = (isset($totalData[$set_date]) && $totalData[$set_date]) ? $totalData[$set_date] : 0;
		$row[$i]['complaint_date']= $set_date;
		$row[$i]['total_complaints']= $count;
		$i++;
	}
	echo brp_json_encode($row);
}
else if(brp_strtolower($POST['mode']) == "profit_loss_chart") {
	$janMonth = getMonthlyTotal('01', $dbcon);
	$febMonth = getMonthlyTotal('02', $dbcon);
	$marMonth = getMonthlyTotal('03', $dbcon);
	$aprMonth = getMonthlyTotal('04', $dbcon);
	$mayMonth = getMonthlyTotal('05', $dbcon);
	$junMonth = getMonthlyTotal('06', $dbcon);
	$julMonth = getMonthlyTotal('07', $dbcon);
	$augMonth = getMonthlyTotal('08', $dbcon);
	$sepMonth = getMonthlyTotal('09', $dbcon);
	$octMonth = getMonthlyTotal('10', $dbcon);
	$novMonth = getMonthlyTotal('11', $dbcon);
	$decMonth = getMonthlyTotal('12', $dbcon);
	
	$row['January'] = $janMonth;
	$row['February'] = $febMonth;
	$row['March'] = $marMonth;
	$row['April'] = $aprMonth;
	$row['May'] = $mayMonth;
	$row['June'] = $junMonth;
	$row['July'] = $julMonth;
	$row['August'] = $augMonth;
	$row['September'] = $sepMonth;
	$row['October'] = $octMonth;
	$row['November'] = $novMonth;
	$row['December'] = $decMonth;

	echo brp_json_encode($row);
}

function getMonthlyTotal($m, $dbcon) {
	$net_amount = 0;
	$start_date = date('Y').'-'.$m.'-01';
	$end_date = date("Y-m-t", strtotime($start_date));

	$total_direct_income = get_p_and_l_direct_income($start_date,$end_date,$dbcon);
	$total_direct_income_spare = get_p_and_l_direct_income_spare($start_date,$end_date,$dbcon);

	$total_direct_expense = get_p_and_l_direct_expense_spare($start_date,$end_date,$dbcon);
	$total_indirect_expense = get_p_and_l_total_indirect_expense($start_date,$end_date,$dbcon);

	$net_amount = ($total_direct_income+$total_direct_income_spare) - ($total_direct_expense+$total_indirect_expense);

	return $net_amount;
}
?>