<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
error_reporting(E_ALL);
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "load_month_wise_won_amount"){
	$companyConfiguration=getCompanyConfiguration($dbcon);

	$query_update = "";
	$whr = "";
	if (!empty($POST['user_id'])) {
		$user_ids=check_user_chein($dbcon,$POST['user_id'],1);			
		/*$whr='';*/
		// $whr.=' and so.user_id in ('.$user_ids.')';
		$whr .= ' and so.user_id = '.$POST['user_id'];
	}

	if($companyConfiguration['forecast_calculation']==1){
		
		if ($POST['start_date'] && $POST['end_date']) {
			$whr .= " AND DATE(so.quotation_date) >= '".date('Y-m-d',strtotime($POST['start_date']))."' AND  DATE(so.quotation_date) <= '".date('Y-m-d',strtotime($POST['end_date']))."'";
		}

		$res = "select sum(u.product_amount) as led from tbl_quotation_trn u LEFT JOIN tbl_quotation AS so ON so.quotation_id = u.quotation_id where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(so.quotation_date) and so.quotation_status=0 and so.approve_status=1 AND u.quot_trn_status = 0 AND so.company_id=".$_SESSION['company_id'];

	}else if($companyConfiguration['forecast_calculation']==2){

		if ($POST['start_date'] && $POST['end_date']) {
			$whr .= " AND DATE(so.sales_order_date) >= '".date('Y-m-d',strtotime($POST['start_date']))."' AND  DATE(so.sales_order_date) <= '".date('Y-m-d',strtotime($POST['end_date']))."'";
		}

		$res = "select sum(u.product_amount) as led from tbl_sales_ordertrn u LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = u.sales_order_id where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(so.sales_order_date) and so.sales_order_status=0 and so.order_accept_status=1 AND u.sales_ordertrn_status = 0 AND so.company_id=".$_SESSION['company_id'];

	}else if($companyConfiguration['forecast_calculation']==3){
		if($companyConfiguration['crm_sales_order_user_selecation']==1 && !empty($POST['user_id'])) {
			$whr = ' and so.order_user_id = '.$POST['user_id'];
		}
		$res = "select sum(u.product_amount) as led from tbl_invoicetrn u LEFT JOIN tbl_invoice AS so ON so.invoice_id = u.invoice_id where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(so.invoice_date) and so.invoice_status=0 and u.trancation_status = 0 AND so.company_id=".$_SESSION['company_id']." and DATE_FORMAT(so.invoice_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE)";

		// $query_update = "SELECT m.month, SUM(u.product_amount) AS invoice
		// 					FROM (
		// 						SELECT 'Apr' AS MONTH 
		// 						UNION SELECT 'May' 
		// 						UNION SELECT 'Jun' 
		// 						UNION SELECT 'Jul' 
		// 						UNION SELECT 'Aug' 
		// 						UNION SELECT 'Sep' 
		// 						UNION SELECT 'Oct' 
		// 						UNION SELECT 'Nov' 
		// 						UNION SELECT 'Dec' 
		// 						UNION SELECT 'Jan' 
		// 						UNION SELECT 'Feb' 
		// 						UNION SELECT 'Mar'
		// 					) AS m
		// 					LEFT JOIN tbl_invoice AS so ON MONTH(STR_TO_DATE(m.month, '%M')) = MONTH(so.invoice_date)
		// 					LEFT JOIN tbl_invoicetrn AS u ON so.invoice_id = u.invoice_id
		// 					WHERE so.invoice_status = 0 
		// 						AND u.trancation_status = 0 
		// 						AND so.company_id = ".$_SESSION['company_id']." 
		// 						AND DATE(so.invoice_date) >= '".date('Y-m-d',strtotime($POST['start_date']))."' AND  DATE(so.invoice_date) <= '".date('Y-m-d',strtotime($POST['end_date']))."' $whr
		// 					GROUP BY m.month
		// 					ORDER BY MONTH(STR_TO_DATE(m.month, '%M'));";
	}

	$query="SELECT m.month,( ".$res."".$whr.") as invoice
	FROM (
	SELECT 'Apr' AS MONTH
	UNION SELECT 'May' AS MONTH
	UNION SELECT 'Jun' AS MONTH
	UNION SELECT 'Jul' AS MONTH
	UNION SELECT 'Aug' AS MONTH
	UNION SELECT 'Sep' AS MONTH
	UNION SELECT 'Oct' AS MONTH
	UNION SELECT 'Nov' AS MONTH
	UNION SELECT 'Dec' AS MONTH
	UNION SELECT 'Jan' AS MONTH
	UNION SELECT 'Feb' AS MONTH
	UNION SELECT 'Mar' AS MONTH
	) AS m
	GROUP BY m.month
	ORDER BY 1+1";

	if ($query_update) {
		$invoice_counter=$dbcon->query($query_update);
	} else {
		$invoice_counter=$dbcon->query($query);
	}
	
				
	$row	= array();
	$i=0;
	while($chart=mysqli_fetch_assoc($invoice_counter))
	{	
		$row1[$i]['label']=$chart['month'];
		$row1[$i]['y']=intval($chart['invoice']);	
		$i++;
	}		
				//var_dump($row);	
	echo json_encode($row1);
}else if(strtolower($POST['mode']) == "load_month_wise_won_qty"){
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$user_ids=check_user_chein($dbcon,$POST['user_id'],1);			
	$whr='';
	// $whr.=' and so.user_id in ('.$user_ids.')';
	if ($POST['user_id']) {
		$whr =' and so.user_id = '.$POST['user_id'];
	}

	if($companyConfiguration['forecast_calculation']==1){
		$res = "select sum(u.product_qty) as led from tbl_quotation_trn u LEFT JOIN tbl_quotation AS so ON so.quotation_id = u.quotation_id where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(so.quotation_date) and so.quotation_status=0 and so.approve_status=1 AND u.quot_trn_status = 0 AND so.company_id=".$_SESSION['company_id']." and DATE_FORMAT(so.quotation_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."' AS DATE)";
	}else if($companyConfiguration['forecast_calculation']==2){
		$res = "select sum(u.product_qty) as led from tbl_sales_ordertrn u LEFT JOIN tbl_sales_order AS so ON so.sales_order_id = u.sales_order_id where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(so.sales_order_date) and so.sales_order_status=0 and so.order_accept_status=1 AND u.sales_ordertrn_status = 0 AND so.company_id=".$_SESSION['company_id']." and DATE_FORMAT(so.sales_order_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."' AS DATE)";
	}else if($companyConfiguration['forecast_calculation']==3){
		if($companyConfiguration['crm_sales_order_user_selecation']==1 && !empty($POST['user_id'])) {
			$whr = ' and so.order_user_id = '.$POST['user_id'];
		}
		$res = "select sum(u.product_qty) as led from tbl_invoicetrn u LEFT JOIN tbl_invoice AS so ON so.invoice_id = u.invoice_id where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(so.invoice_date) and so.invoice_status=0 and u.trancation_status = 0 AND so.company_id=".$_SESSION['company_id']." and DATE_FORMAT(so.invoice_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."' AS DATE)";
	}

	$query="SELECT m.month,( ".$res."".$whr.") as invoice
	FROM (
	SELECT 'Apr' AS MONTH
	UNION SELECT 'May' AS MONTH
	UNION SELECT 'Jun' AS MONTH
	UNION SELECT 'Jul' AS MONTH
	UNION SELECT 'Aug' AS MONTH
	UNION SELECT 'Sep' AS MONTH
	UNION SELECT 'Oct' AS MONTH
	UNION SELECT 'Nov' AS MONTH
	UNION SELECT 'Dec' AS MONTH
	UNION SELECT 'Jan' AS MONTH
	UNION SELECT 'Feb' AS MONTH
	UNION SELECT 'Mar' AS MONTH
	) AS m
	GROUP BY m.month
	ORDER BY 1+1";
	$invoice_counter=$dbcon->query($query);
			//	echo $query;
	$row	= array();
	$i=0;
	while($chart=mysqli_fetch_assoc($invoice_counter))
	{	
		$row1[$i]['label']=$chart['month'];
		$row1[$i]['y']=intval($chart['invoice']);	
		$i++;
	}		
				//var_dump($row);	
	echo json_encode($row1);
}
else if(strtolower($POST['mode']) == "load_target_chart") {
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$target_start_date=$POST['target_start_date'];	
	$target_end_date=$POST['target_end_date'];	
	$target_user_id=$POST['target_user_id'];
    $log_user_id=$_SESSION['user_id'];//53
    $t_pro_wise=$POST['t_pro_wise'];
    $whr='';
	// $whr.=' and so.user_id in ('.$user_ids.')';
	if ($target_user_id) {
		$whr =' and so.user_id = '.$target_user_id;
	}
	
    if($POST['t_pro_wise']=='1'){
    	//Qty Wise Load Target data
    	if($companyConfiguration['forecast_calculation']==1){

			$whr_inn = " ";
			if ($target_user_id) {
				$whr_inn = " AND qt.user_id='".$target_user_id."'";
			}
			if ($target_start_date && $target_end_date) {
				$whr_inn .= " AND DATE(qt.quotation_date) >= '".date('Y-m-d',strtotime($target_start_date))."' AND  DATE(qt.quotation_date) <= '".date('Y-m-d',strtotime($target_end_date))."'";
			}

    		$res = "SELECT sum(qtrn.product_qty) FROM `tbl_quotation` as qt left join tbl_quotation_trn as qtrn on qtrn.quotation_id=qt.quotation_id where qtrn.quot_trn_status=0 and qt.quotation_status=0 and qt.approve_status=1 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.quotation_date) $whr_inn ";

    	}else if($companyConfiguration['forecast_calculation']==2){

			$whr_inn = " ";
			if ($target_user_id) {
				$whr_inn = " AND qt.user_id='".$target_user_id."'";
			}
			if ($target_start_date && $target_end_date) {
				$whr_inn .= " AND DATE(qt.sales_order_date) >= '".date('Y-m-d',strtotime($target_start_date))."' AND  DATE(qt.sales_order_date) <= '".date('Y-m-d',strtotime($target_end_date))."'";
			}

    		$res = "SELECT sum(qtrn.product_qty) FROM `tbl_sales_order` as qt left join tbl_sales_ordertrn as qtrn on qtrn.sales_order_id=qt.sales_order_id where qtrn.sales_ordertrn_status=0 and qt.sales_order_status=0 and qt.order_accept_status=1 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.sales_order_date) $whr_inn";

    	}else if($companyConfiguration['forecast_calculation']==3){

    		if($companyConfiguration['crm_sales_order_user_selecation']==1 && !empty($POST['user_id'])){
				$whr = ' and so.order_user_id = '.$POST['user_id'];
			}

			$whr_inn = " ";
			if ($target_user_id) {
				$whr_inn = " AND qt.user_id='".$target_user_id."'";
			}
			if ($target_start_date && $target_end_date) {
				$whr_inn .= " AND DATE(qt.invoice_date) >= '".date('Y-m-d',strtotime($target_start_date))."' AND  DATE(qt.invoice_date) <= '".date('Y-m-d',strtotime($target_end_date))."'";
			}

    		$res = "SELECT sum(qtrn.product_qty) FROM `tbl_invoice` as qt left join tbl_invoicetrn as qtrn on qtrn.invoice_id=qt.invoice_id where qtrn.trancation_status=0 and qt.invoice_status=0 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.invoice_date) $whr_inn";
    	}

		$whr_mst = " ";
		if ($target_user_id) {
			$whr_mst = " AND mst.f_user_id='".$target_user_id."'";
		}

    	$query = "SELECT m.month,(SELECT sum(ptrn.target_qty) FROM `tbl_forecast_user_trn` as ptrn
    	left join tbl_forecast_user as mst on mst.forecast_user_id=ptrn.forecast_usertable_id
    	where ptrn.status=0 and mst.forecast_status=0 $whr_mst AND mst.company_id = ".$_SESSION['company_id']." and (MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(ptrn.forecast_start_date) AND MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(ptrn.forecast_end_date))) as total ,
    	( ".$res." ) as total_paid FROM 
    	( SELECT 'Jan' AS MONTH 
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
    	UNION SELECT 'Dec' AS MONTH  ) AS m GROUP BY m.month ORDER BY 1+1";

    } else{
    	if($companyConfiguration['forecast_calculation']==1){

			$whr1 = " ";
			if ($target_user_id) {
				$whr1 = " and qt.user_id='".$target_user_id."' ";
			}

    		$res = "SELECT sum(qtrn.product_amount) FROM `tbl_quotation` as qt left join tbl_quotation_trn as qtrn on qtrn.quotation_id=qt.quotation_id where qtrn.quot_trn_status=0 and qt.quotation_status=0 and qt.approve_status=1 $whr1 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.quotation_date) and DATE_FORMAT(qt.quotation_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($target_start_date))."' AS DATE) and CAST('".date('Y-m-d',strtotime($target_end_date))."' AS DATE)";

    	}else if($companyConfiguration['forecast_calculation']==2){

			$whr1 = " ";
			if ($target_user_id) {
				$whr1 = " and qt.user_id='".$target_user_id."' ";
			}

    		$res = "SELECT sum(qtrn.product_amount) FROM `tbl_sales_order` as qt left join tbl_sales_ordertrn as qtrn on qtrn.sales_order_id=qt.sales_order_id where qtrn.sales_ordertrn_status=0 and qt.sales_order_status=0 and qt.order_accept_status=1 $whr1 and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.sales_order_date) and DATE_FORMAT(qt.sales_order_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($target_start_date))."' AS DATE) and CAST('".date('Y-m-d',strtotime($target_end_date))."' AS DATE)";

    	}else if($companyConfiguration['forecast_calculation']==3){

			if ($target_user_id) {
				if($companyConfiguration['crm_sales_order_user_selecation']==1){
					$whr = ' and qt.order_user_id = '.$target_user_id;
				}else{
					$whr = ' and qt.user_id='.$target_user_id;
				}
			}
    		$res = "SELECT sum(qtrn.product_amount) FROM `tbl_invoice` as qt left join tbl_invoicetrn as qtrn on qtrn.invoice_id=qt.invoice_id where qtrn.trancation_status=0 and qt.invoice_status=0 ".$whr." and MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(qt.invoice_date) and DATE_FORMAT(qt.invoice_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($target_start_date))."' AS DATE) and CAST('".date('Y-m-d',strtotime($target_end_date))."' AS DATE)";
    	}

		$whr_mst = " ";
		if ($target_user_id) {
			$whr_mst = " AND mst.f_user_id='".$target_user_id."'";
		}
    	$query = "SELECT m.month,(SELECT sum(ptrn.target_amount) FROM `tbl_forecast_user_trn` as ptrn left join tbl_forecast_user as mst on mst.forecast_user_id=ptrn.forecast_usertable_id where ptrn.status=0 and mst.forecast_status=0 $whr_mst AND mst.company_id = ".$_SESSION['company_id']." and (MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(ptrn.forecast_start_date) AND MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(ptrn.forecast_end_date))) as total ,
    	( ".$res." ) as total_paid FROM 
    	( SELECT 'Jan' AS MONTH 
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
    	UNION SELECT 'Dec' AS MONTH  ) AS m GROUP BY m.month ORDER BY 1+1";
    }
    $tar_counter = $dbcon->query($query);
    $row = array();
    $i=0;
    while($chart= mysqli_fetch_assoc($tar_counter))
    {	
    	$row[$chart['month']][]=intval($chart['total']);
    	$row[$chart['month']][]=intval($chart['total_paid']);
    	$row[]= $chart['month'];
    }
    // print_r($query);
    echo json_encode($row); 
}

function get_sdate($date)
{
	$sdate['start_date']=date('01-04-'.$date);
	$sdate['end_date']=date('31-03-'.($date+1));
	return $sdate;	
}

function get_calender_sdate($date)
{
	$sdate['start_date']=date($date.'-01-01');
	$sdate['end_date']=date(($date).'-12-31');
	return $sdate;	
}

?>