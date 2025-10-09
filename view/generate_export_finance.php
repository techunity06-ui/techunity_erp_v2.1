<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../config/security.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

	$delimiter = ",";
	$f = fopen('php://memory', 'w');

	if ($_REQUEST['mode'] == "invoice_list") {

		$filename = "invoice_list_".date('d-M-Y').".csv";
		
		//set column headers
		$fields = array('SR No.','Invoice No','Invoice Date','Customer Name', 'Grand Total', 'Basic Total', 'Username');

		fputcsv($f, $fields, $delimiter);

		$s_date=explode(' - ',$_REQUEST['rep_date']);
		$_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];


		$branch_id = $_SESSION['branch_id'];
		$where_db = check_branch('invoice', $branch_id);
		$where .= " $where_db";
		$where_company = check_company('invoice');
		$where .= " $where_company";
		$where_user = check_user('invoice');

		$companyConfiguration = getCompanyConfiguration($dbcon);
		
		$where .= "  and invoice.invoice_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND invoice.invoice_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";
		$i = 1;

		$aColumns = array('invoice.invoice_id', 'trn.product_spec', 'trn.description', 'invoice.invoice_no', 'cust.l_name', 'invoice.invoice_date', 'invoice.g_total', 'invoice.order_date', 'invoice.order_no', 'users.user_name', 'invoicetype.invoice_type', 'invoice.paid_amount', 'invoice.invoice_status', 'invoice.cdate', 'invoice.user_id', 'invoice.usertype_id', 'invoice.invoicetype_id', 'invoice.gst_flag', 'invoice.approve_status', 'cust.cust_mobile', 'invoice.eway_bill_no', 'invoice.einv_Irn', 'invoice.basic_total');

		$sIndexColumn = "invoice.invoice_id";
		$isWhere = array("invoice_status = 0 " . $where);
		$sTable = "tbl_invoice as invoice";
		$isJOIN = array(
			'left join tbl_ledger cust on invoice.cust_id=cust.l_id',
			'left join tbl_invoicetype invoicetype on invoice.invoicetype_id=invoicetype.invoicetype_id',
			'left join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id',
			'left join users as users on users.user_id=invoice.user_id'
		);
		$hGroupby = array("invoice.invoice_id");
		$hOrder = "invoice.invoice_id desc";
		include('../include/pagging.php');

		$appData = array();
		$id = 1;
		$grand_total = 0;
		$basic_total = 0;
		foreach ($sqlReturn as $row) {
			$row_data = array();

			$row_data[] = $id;
			$row_data[] = $row["invoice_no"];
			$row_data[] = date('d M, Y', strtotime($row["invoice_date"]));
			if ($getspecialConfiguration['power_drive'] == 1) {
				$row_data[] = $row["product_spec"];
				$row_data[] = $row["description"];
			}
			$row_data[] = $row["l_name"];
			$grand_total = $grand_total + $row["g_total"];
			$row_data[] = $row["g_total"];
			$basic_total = $basic_total + $row["basic_total"];
			$row_data[] = $row["basic_total"];
			$row_data[] = $row["user_name"];

			$lineData = $row_data;
			fputcsv($f, $lineData, $delimiter);	
			$id++;
		}

		$row_data = array('','','','',$grand_total,$basic_total,'');
		$lineData = $row_data;
		fputcsv($f, $lineData, $delimiter);	

	}
	
	//move back to beginning of file
	fseek($f, 0);
	
	//set headers to download file rather than displayed
	// header('Content-Type: text/csv');
	// header('Content-Disposition: attachment; filename="' . $filename . '";');
	$now = gmdate("D, d M Y H:i:s");
	header("Expires: ".date('D M d Y H:i:s O'));
	header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
	header("Last-Modified: ".$now." GMT");
	
	// force download  
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	
	// disposition / encoding on response body
	header("Content-Disposition: attachment;filename=".$filename."");
	header("Content-Transfer-Encoding: binary");
	
	//output all remaining data on a file pointer
	fpassthru($f);
	exit;	

function getTaskAssignNameCommaSeparated($dbcon, $assign_user_ids)
{

	$strVal = '';
	$qry = 'SELECT tsk.task_id, GROUP_CONCAT(userdata.user_name) AS valuesdata FROM tbl_task tsk JOIN users AS userdata ON FIND_IN_SET(userdata.user_id, "' . $assign_user_ids . '") GROUP BY tsk.task_id';
		$qry_rel = mysqli_fetch_assoc($dbcon->query($qry));

		if ($qry_rel) {
			$strVal = $qry_rel['valuesdata'];
		}
		return $strVal;
}

function remove_special_char($originalString) {

	// Convert encoding to UTF-8
	$htmlContent = html_entity_decode($originalString);
	
	// Remove special characters from HTML content
	$cleanedContent = preg_replace('/[^\x20-\x7E]/u', '', $htmlContent);
	
	return $cleanedContent;
}
?>