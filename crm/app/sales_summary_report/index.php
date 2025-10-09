<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php'); { {

		if ($_POST != NULL) {
			$POST = bulk_filter($dbcon, $_POST);
		} else {
			$POST = bulk_filter($dbcon, $_GET);
		}

		if (strtolower($POST['mode']) == "generate_report") {
			$s_date = explode(' - ', $POST['date']);
			$str = '';

			$s_date = explode(' - ', $POST['date']);
			$start_date = date("Y-m-d", strtotime($s_date[0]));
			$end_date = date("Y-m-d", strtotime($s_date[1]));

			$str = "<table class='table table-bordered table-hover dataTable display'>";
			$str .= "<tr><h4><strong>Sales Summary Report</strong></h4></tr>";
			$str .= "<tr>
				<th>Month</th>
				<th>Orange</th>
				<th>Mfg.</th>
				<th>Mfg. Other</th>
				<th>Trading</th>
				<th>Repairing</th>
				<th>Total</th>
			</tr>";
			$cnt = 1;

			$q = "select MONTHNAME(inv.invoice_date) AS monthname,MONTH(inv.invoice_date) as month, trn.product_qty,
               IFNULL(SUM(trn.product_qty * trn.orange),0) AS totalorange, IFNULL(SUM(trn.product_qty * trn.mfg),0) AS totalmfg, IFNULL(SUM(trn.product_qty * trn.trading),0) AS totaltrading, IFNULL(SUM(trn.product_qty * trn.repairing),0) as totalrepairing, IFNULL(SUM(trn.product_qty * trn.other),0) as  totalother from  tbl_invoicetrn as trn left join tbl_invoice as inv on inv.invoice_id = trn.invoice_id where trn.trancation_status = 0 and inv.invoice_status = 0 and date(inv.invoice_date) >= '" . $start_date . "' and date(inv.invoice_date) <= '" . $end_date . "' GROUP BY month order by month";

			$cnt = 1;
			$query = $dbcon->query($q);
			$totalorange = 0;
			$totalmfg = 0;
			$totaltrading = 0;
			$totalrepairing = 0;
			$totalother = 0;
			$final_total = 0;
			while ($row = brp_mysqli_fetch_assoc($query)) {
				$total = 0;
				$totalorange = $totalorange + $row['totalorange'];
				$totalmfg = $totalmfg + $row['totalmfg'];
				$totaltrading = $totaltrading + $row['totaltrading'];
				$totalrepairing = $totalrepairing + $row['totalrepairing'];
				$totalother = $totalother + $row['totalother'];

				$total = $row['totalorange'] + $row['totalmfg'] + $row['totaltrading'] + $row['totalrepairing']  + $row['totalother'];

				$final_total = $final_total + $total;

				$str .= "
					<tr>
						<td onClick='generate_detail_report(" . $row['month'] . ");'><a style='cursor:pointer;'>" . $row['monthname'] . "</a></td>
						<td>" . $row['totalorange'] . "</td>
						<td>" . $row['totalmfg'] . "</td>
						<td>" . $row['totalother'] . "</td>
						<td>" . $row['totaltrading'] . "</td>
						<td>" . $row['totalrepairing'] . "</td>
						<td>" . $total . "</td>	
					</tr>
				";
				$cnt++;
			}

			$str .= "
					<tr>
						<th>Total</th>
						<th>" . $totalorange . "</th>
						<th>" . $totalmfg . "</th>
						<th>" . $totalother . "</th>
						<th>" . $totaltrading . "</th>
						<th>" . $totalrepairing . "</th>
						<th>" . $final_total . "</th>
					</tr>
				";

			echo $str;
		} else if (strtolower($POST['mode']) == "generate_detail_report") {
			$s_date = explode(' - ', $POST['date']);
			$str = '';
			$s_date = explode(' - ', $POST['date']);
			$start_date = date("Y-m-d", strtotime($s_date[0]));
			$end_date = date("Y-m-d", strtotime($s_date[1]));

			$month = $POST['month'];

			$str = "<table class='table table-bordered table-hover dataTable display'>";
			$str .= "<tr><h4><strong>Sales Summary Month Wise Details Report</strong></h4></tr>";
			$str .= "<tr>
				<th>Invoice Date</th>
				<th>Invoice No</th>
				<th>Client</th>
				<th>Orange</th>
				<th>Mfg.</th>
				<th>Mfg. Other</th>
				<th>Trading</th>
				<th>Repairing</th>
				<th>Total</th>
			</tr>";
			$cnt = 1;


			$q = "select inv.invoice_date,inv.invoice_no, MONTHNAME(inv.invoice_date) AS monthname,MONTH(inv.invoice_date) as month,trn.product_qty, IFNULL(SUM(trn.product_qty * trn.orange),0) AS totalorange, IFNULL(SUM(trn.product_qty * trn.mfg),0) AS totalmfg, IFNULL(SUM(trn.product_qty * trn.trading),0) AS totaltrading, IFNULL(SUM(trn.product_qty * trn.repairing),0) as totalrepairing, IFNULL(SUM(trn.product_qty * trn.other),0) as totalother, cust.l_name from tbl_invoicetrn as trn left join tbl_invoice as inv on inv.invoice_id = trn.invoice_id left join tbl_ledger cust on inv.cust_id=cust.l_id where trn.trancation_status = 0 and inv.invoice_status = 0 and date(inv.invoice_date) >= '" . $start_date . "' and date(inv.invoice_date) <= '" . $end_date . "' and MONTH(inv.invoice_date)=$month GROUP BY inv.invoice_no order by inv.invoice_no desc";

			$cnt = 1;
			$query = $dbcon->query($q);
			$totalorange = 0;
			$totalmfg = 0;
			$totaltrading = 0;
			$totalrepairing = 0;
			$totalother = 0;
			$final_total = 0;
			while ($row = brp_mysqli_fetch_assoc($query)) {
				$total = 0;
				$totalorange = $totalorange + $row['totalorange'];
				$totalmfg = $totalmfg + $row['totalmfg'];
				$totaltrading = $totaltrading + $row['totaltrading'];
				$totalrepairing = $totalrepairing + $row['totalrepairing'];
				$totalother = $totalother + $row['totalother'];

				$total = $row['totalorange'] + $row['totalmfg'] + $row['totaltrading'] + $row['totalrepairing']  + $row['totalother'];

				$final_total = $final_total + $total;

				$str .= "
					<tr>
						<td>" . date("d-m-Y",strtotime($row['invoice_date'])) . "</td>
						<td>" . $row['invoice_no'] . "</td>
						<td>" . $row['l_name'] . "</td>
						<td>" . $row['totalorange'] . "</td>
						<td>" . $row['totalmfg'] . "</td>
						<td>" . $row['totalother'] . "</td>
						<td>" . $row['totaltrading'] . "</td>
						<td>" . $row['totalrepairing'] . "</td>
						<td>" . $total . "</td>						
					</tr>
				";
				$cnt++;
			}

			$str .= "
					<tr>
						<th colspan='3'>Total</th>
						<th>" . $totalorange . "</th>
						<th>" . $totalmfg . "</th>
						<th>" . $totalother . "</th>
						<th>" . $totaltrading . "</th>
						<th>" . $totalrepairing . "</th>
						<th>" . $final_total . "</th>						
					</tr>
				";
			echo $str;
		}
	}
}
