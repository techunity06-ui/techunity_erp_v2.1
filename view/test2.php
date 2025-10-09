<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

// $qry2="select * from tbl_grn_sub_trn as grn where grn.status=0 ";
// $result2=$dbcon->query($qry2);
// while($rel1=brp_mysqli_fetch_assoc($result2)){
// 	update_grn_sub_trn_to_purchase_status($dbcon,$rel1['grn_trn_sub_id']);R	
// }
function getMonthsInRange($startDate, $endDate, $smonth, $emonth,$ftype)
{
	$months = array();
	$data = array();

	while (strtotime($startDate) <= strtotime($endDate)) {
		$months[] = array(
			's_date' => date('Y-m-01', strtotime($startDate)),
			'e_date' => date('Y-m-t', strtotime($startDate)),
			'month' => date('m', strtotime($startDate)),
		);
		$startDate = date('01 M Y', strtotime($startDate . '+ 1 month'));
        // Set date to 1 so that new month is returned as the month changes.
	}
	if($ftype=='2'){
		if($smonth=='01'){
			array_push($data,$months['9']);
		}else if($smonth=='02'){
			array_push($data,$months['10']);
		}else if($smonth=='03'){
			array_push($data,$months['11']);
		}else if($smonth=='04'){
			array_push($data,$months['0']);
		}else if($smonth=='05'){
			array_push($data,$months['1']);
		}else if($smonth=='06'){
			array_push($data,$months['2']);
		}else if($smonth=='07'){
			array_push($data,$months['3']);
		}else if($smonth=='08'){
			array_push($data,$months['4']);
		}else if($smonth=='09'){
			array_push($data,$months['5']);
		}else if($smonth=='10'){
			array_push($data,$months['6']);
		}else if($smonth=='11'){
			array_push($data,$months['7']);
		}else if($smonth=='12'){
			array_push($data,$months['8']);
		}
		if($emonth=='01'){
			array_push($data,$months['9']);
		}else if($emonth=='02'){
			array_push($data,$months['10']);
		}else if($emonth=='03'){
			array_push($data,$months['11']);
		}else if($emonth=='04'){
			array_push($data,$months['0']);
		}else if($emonth=='05'){
			array_push($data,$months['1']);
		}else if($emonth=='06'){
			array_push($data,$months['2']);
		}else if($emonth=='07'){
			array_push($data,$months['3']);
		}else if($emonth=='08'){
			array_push($data,$months['4']);
		}else if($emonth=='09'){
			array_push($data,$months['5']);
		}else if($emonth=='10'){
			array_push($data,$months['6']);
		}else if($emonth=='11'){
			array_push($data,$months['7']);
		}else if($emonth=='12'){
			array_push($data,$months['8']);
		}
	}else{
		if($smonth=='01'){
			array_push($data,$months['0']);
		}else if($smonth=='02'){
			array_push($data,$months['1']);
		}else if($smonth=='03'){
			array_push($data,$months['2']);
		}else if($smonth=='04'){
			array_push($data,$months['3']);
		}else if($smonth=='05'){
			array_push($data,$months['4']);
		}else if($smonth=='06'){
			array_push($data,$months['5']);
		}else if($smonth=='07'){
			array_push($data,$months['6']);
		}else if($smonth=='08'){
			array_push($data,$months['7']);
		}else if($smonth=='09'){
			array_push($data,$months['8']);
		}else if($smonth=='10'){
			array_push($data,$months['9']);
		}else if($smonth=='11'){
			array_push($data,$months['10']);
		}else if($smonth=='12'){
			array_push($data,$months['11']);
		}
		if($emonth=='01'){
			array_push($data,$months['0']);
		}else if($emonth=='02'){
			array_push($data,$months['1']);
		}else if($emonth=='03'){
			array_push($data,$months['2']);
		}else if($emonth=='04'){
			array_push($data,$months['3']);
		}else if($emonth=='05'){
			array_push($data,$months['4']);
		}else if($emonth=='06'){
			array_push($data,$months['5']);
		}else if($emonth=='07'){
			array_push($data,$months['6']);
		}else if($emonth=='08'){
			array_push($data,$months['7']);
		}else if($emonth=='09'){
			array_push($data,$months['8']);
		}else if($emonth=='10'){
			array_push($data,$months['9']);
		}else if($emonth=='11'){
			array_push($data,$months['10']);
		}else if($emonth=='12'){
			array_push($data,$months['11']);
		}
	}

	return $data;
}
echo "<pre>";
print_r(getMonthsInRange('2022-04-01','2023-03-31','12','01','2'));

?>
