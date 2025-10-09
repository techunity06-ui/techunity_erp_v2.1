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

if(brp_strtolower($POST['mode']) == "generate_report_ledger") {
	
	$con = "";
	if(!empty($POST['cust_id'])){ 
		$con = " AND i.cust_id = ".$POST['cust_id'];
	}

	// $query = "SELECT tc.cust_id, SUM(IF(tc.pay_status = 1, (IF(tct.inv_done_status = '1', 0, tct.comp_amount)), 0)) credit, SUM(IF(tc.pay_status = 0, (IF(tct.inv_done_status = '1', 0, tct.comp_amount)), 0)) debit,  GROUP_CONCAT(tc.complaint_id) total_complaint, GROUP_CONCAT(tct.inv_done_status) total_inv_done_status FROM `tbl_complaint` tc 
	// 	JOIN tbl_complaint_trn tct ON tc.complaint_id = tct.complaint_id 
	// 	WHERE tc.followup_status IN (4, 9) AND tc.invoice_id = 0 GROUP BY tc.cust_id";
	
	// $query = "SELECT i.cust_id, c.complaint_no, i.invoice_no, IF(i.g_total, i.g_total, 0), i.invoice_id, 
	// (IF(i.g_total, i.g_total, 0) - IF(rt.total_amount, rt.total_amount, 0)) as balance_amount, 
	// IF(rt.total_amount, rt.total_amount, 0), l.l_name FROM tbl_invoice i 
	// LEFT JOIN tbl_receipt_trn rt ON rt.invoice_id = i.invoice_id 
	// LEFT JOIN tbl_complaint c ON i.complaint_id = c.complaint_id 
	// LEFT JOIN tbl_ledger l ON l.l_id = i.cust_id
	// WHERE i.install_type = 'no' AND c.followup_status IN (4, 9) AND l.l_status = '0' $con GROUP BY i.cust_id";
	
	
	$query = "SELECT i.cust_id, c.complaint_no, i.invoice_no, i.invoice_id, l.l_name, 
		SUM((
			SELECT IF(invoice_id = i.invoice_id, IF(i.g_total, i.g_total, 0) - SUM(IF(total_amount, total_amount, 0)), IF(i.g_total, i.g_total, 0)) total_amount FROM tbl_receipt_trn 
			WHERE invoice_id = i.invoice_id
		)) balance_amount FROM tbl_invoice i 
		LEFT JOIN tbl_complaint c ON i.complaint_id = c.complaint_id 
		LEFT JOIN tbl_ledger l ON l.l_id = i.cust_id
		WHERE i.install_type = 'no' AND c.followup_status IN (4, 9) AND l.l_status = '0' $con GROUP BY i.cust_id";
	// p($query);
	$qry = $dbcon->query($query);
	
	$cnt = 1; $str = ''; $totaldebit_amount = 0; $totalcradit_amo = 0;
	while($row = brp_mysqli_fetch_assoc($qry))
	{
		$cityQuery="select l.l_id,l.l_name,c.city_name from tbl_ledger as l left join city_mst as c on c.cityid=l.cityid where l.l_id=".$row['cust_id'];
		$cityRow = brp_mysqli_fetch_assoc($dbcon->query($cityQuery));

		$total_complaint = $row['total_complaint'];
		$balance_amount = $row['balance_amount'];

		$credit = $balance_amount < 0 ? ($balance_amount) : 0;
		$debit = $balance_amount > 0 ? ($balance_amount) : 0;
		
		$str.='<tr>
			<th>'.$cnt.'</th>
			<th><a href="'.ROOT.SERVICE_ROOT.'service_ledger_detail/'.$row['cust_id'].'">'.$row['l_name'].' ('.$cityRow['city_name'].')</a></th>
			<th>'.number_format(abs($credit), 2).'</th>
			<th>'.number_format(abs($debit), 2).'</th>
		</tr>';
		
		$totaldebit_amount += $debit;
		$totalcradit_amo += $credit;
		$cnt++;
	}
	
	$str.='<tr>			
		<th colspan="2" style="text-align:right;"><strong>Total</strong></th>
		<th>'.number_format(abs($totalcradit_amo), 2).'</th>
		<th>'.number_format(abs($totaldebit_amount), 2).'</th>
	</tr>';

	$grand_total = $totalcradit_amo - $totaldebit_amount;
	$desc = $grand_total > 0 ? 'CR' : 'DR';
	$desc_color = $grand_total > 0 ? 'green' : 'red';

	$str.='<tr style="font-size: 20px;">			
		<th colspan="2" style="text-align:right;"><strong>Grand Total</strong></th>
		<th colspan="2" style="color: '.$desc_color.'; text-align: right;">'.number_format(abs($grand_total), 2).' '.$desc.'</th>
	</tr>';
	
	echo $str;
}
else if(brp_strtolower($POST['mode']) == "report_ledger_detail") {
	$con = $con1 = $con2 = $con3 = "";
	if(!empty($POST['l_id'])){ 
		$con = " AND i.cust_id = ".$POST['l_id'];
	}
	
	if(!empty($POST['start_date'])){
		$start_date_str = date('Y-m-d', strtotime($POST['start_date']));
		$con1 .= " AND i.invoice_date >= '".$start_date_str."'";
		$con2 .= " AND r.receipt_date >= '".$start_date_str."'";
		$con3 .= " AND i.invoice_date < '".$start_date_str."'";
		$con4 .= " AND r.receipt_date < '".$start_date_str."'";
	}
	
	if(!empty($POST['end_date'])){
		$end_date = $POST['end_date'].'23:59:59';
		$end_date_str = date('Y-m-d', strtotime($end_date));
		$con1 .= " AND i.invoice_date <= '".$end_date_str."'";
		$con2 .= " AND r.receipt_date <= '".$end_date_str."'";
	}
	
	// $query = "SELECT IF(pay_status = 1, SUM(tct.comp_amount), 0) credit, IF(pay_status = 0, SUM(tct.comp_amount), 0) debit, tct.inv_done_status, tc.* FROM `tbl_complaint` tc 
	// 	JOIN tbl_complaint_trn tct ON tc.complaint_id = tct.complaint_id 
	// 	WHERE tc.followup_status IN (4, 9) AND tc.invoice_id = 0 $con GROUP BY tc.complaint_id";

	$obQuery = "SELECT i.cust_id, 
		SUM((
			SELECT IF(tr.invoice_id = i.invoice_id, IF(i.g_total, i.g_total, 0) - SUM(IF(tr.total_amount, tr.total_amount, 0)), IF(i.g_total, i.g_total, 0)) total_amount FROM tbl_receipt_trn tr
			JOIN tbl_receipt r ON r.receipt_id = tr.receipt_id
			WHERE tr.invoice_id = i.invoice_id $con4
		)) opening_balance FROM tbl_invoice i 
		LEFT JOIN tbl_complaint c ON i.complaint_id = c.complaint_id 
		LEFT JOIN tbl_ledger l ON l.l_id = i.cust_id
		WHERE i.install_type = 'no' AND c.followup_status IN (4, 9) AND l.l_status = '0' $con $con3 GROUP BY i.cust_id";
	// p($obQuery);
	$qry = $dbcon->query($obQuery);
	$balanceRow = brp_mysqli_fetch_assoc($dbcon->query($obQuery));
	// p($balanceRow);

	$opening_balance = 0;
	$obnote = 'DR';
	$ob_note_color = 'red';
	$str = '';
	if($balanceRow) {
		$opening_balance = $balanceRow['opening_balance'] ? $balanceRow['opening_balance'] : $opening_balance;
		$obnote = $opening_balance < 0 ? 'CR' : $obnote;
		$ob_note_color = $opening_balance < 0 ? 'green' : $ob_note_color;
		$opening_balance = $opening_balance < 0 ? $opening_balance : -$opening_balance;
	}

	$str.='<tr>
			<th>1</th>
			<th>'.date('d-m-Y', strtotime($POST['start_date'])).'</th>
			<th>Opening Balance</th>
			<th>-</th>
			<th>-</th>
			<th style="color: '.$ob_note_color.';">'.number_format(abs($opening_balance), 2).' '.$obnote.'</th>
		</tr>';
	
	$query = "SELECT *
	FROM (SELECT c.complaint_id, c.complaint_no, i.invoice_id AS rec_id, i.invoice_no AS ref_no, i.g_total AS total_amount, 'debit' AS row_type, i.invoice_date AS cdate FROM tbl_invoice i 
	LEFT JOIN tbl_complaint c ON i.complaint_id = c.complaint_id 
	LEFT JOIN tbl_ledger l ON l.l_id = i.cust_id
	WHERE i.install_type = 'no' AND c.followup_status IN (4, 9) AND l.l_status = '0' $con $con1
	UNION
	SELECT c.complaint_id, c.complaint_no, r.receipt_id AS rec_id, r.receipt_no AS ref_no, rt.total_amount AS total_amount, 'credit' AS row_type, r.receipt_date AS cdate FROM tbl_receipt_trn rt 
	LEFT JOIN tbl_invoice i ON rt.invoice_id = i.invoice_id 
	LEFT JOIN tbl_receipt r ON rt.receipt_id = r.receipt_id
	LEFT JOIN tbl_complaint c ON i.complaint_id = c.complaint_id
	LEFT JOIN tbl_ledger l ON l.l_id = i.cust_id
	WHERE i.install_type = 'no' AND c.followup_status IN (4, 9) AND l.l_status = '0' $con $con2) all_rec
	ORDER BY all_rec.cdate";
	// p($query);
	$qry = $dbcon->query($query);
	
	$cnt = 2; $totaldebit_amount = 0; $totalcradit_amo = 0; $balance_amt = 0;
	while($row = brp_mysqli_fetch_assoc($qry))
	{
		$complaint_no = $row['complaint_no'];
		$complaint_id = $row['complaint_id'];
		$complaint_no = '<a target="_blank" href="'.ROOT.SERVICE_ROOT.'complaint_history/'.$complaint_id.'">'.$row['complaint_no'].'</a>';;
		$ref_no = $row['ref_no'];
		$rec_id = $row['rec_id'];
		if($row['row_type'] == 'credit') {
			$credit = $row['total_amount'];
			$debit = 0;
			$note = 'Ref No:';
			$ref_desc = '<a target="_blank" href="'.ROOT.'receipt_sales/'.$rec_id.'">'.$ref_no.'</a>';
			$description = "Payment received for: $complaint_no ($note $ref_desc)";
		} else {
			$credit = 0;
			$debit = $row['total_amount'];
			$note = 'Invoice No:';
			$ref_desc = '<a target="_blank" href="'.ROOT.'invoicereceipt/'.$rec_id.'">'.$ref_no.'</a>';
			$description = "Complaint No: $complaint_no ($note $ref_desc)";
		}

		$grand_total_amt = $credit - $debit;
		$balance_amount += $grand_total_amt;
		if($cnt == 2) {
		$balance_amount += $opening_balance;
		}
		$desc_note = $balance_amount > 0 ? 'CR' : 'DR';
		$desc_note_color = $balance_amount > 0 ? 'green' : 'red';
		
		$totaldebit_amount += $debit;
		$totalcradit_amo += $credit;
			
		$str.='<tr>
			<th>'.$cnt.'</th>
			<th>'.date('d-m-Y', strtotime($row['cdate'])).'</th>
			<th>'.$description.'</th>
			<th>'.number_format(abs($credit), 2).'</th>
			<th>'.number_format(abs($debit), 2).'</th>
			<th style="color: '.$desc_note_color.';">'.number_format(abs($balance_amount), 2).' '.$desc_note.'</th>
		</tr>';
		
		$cnt++;
	}

	// $str.='<tr>			
	// 	<th colspan="3" style="text-align:right;"><strong>Total</strong></th>
	// 	<th>'.number_format($totalcradit_amo, 2).'</th>
	// 	<th>'.number_format($totaldebit_amount, 2).'</th>
	// 	<th style="color: '.$desc_note_color.';">'.number_format(abs($balance_amount), 2).' '.$desc_note.'</th>
	// </tr>';
	
	echo $str;
}
?>