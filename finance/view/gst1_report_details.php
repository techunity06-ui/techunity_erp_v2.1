<?php
	session_start();
	$path = '../../';
	$include1 = '../include/';
	$include = '../../include/';
	include_once($path . "config/config.php");
	include_once($path . "config/session.php");
	include_once(COMMON_FUNCTION_PATH . "common_functions.php");
	include_once(COMMON_FUNCTION_PATH . "finance_common_functions.php");

	$type = isset($_GET['type']) ? $_GET['type'] : 0;
	$doc_type = isset($_GET['doc_type']) ? $_GET['doc_type'] : 0;
	$st_date = date("Y-m-d", strtotime($_SESSION['start']));
	$end_date = date("Y-m-d", strtotime($_SESSION['end']));

	$company_row = get_company_data($dbcon, $_SESSION['company_id']);
	$company_state = $company_row['stateid'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<?php include_once($include . 'include_css_file.php'); ?>
	<style>
		.gst_details {
			color: blue;
			font-size: 15px !important;
		}

		.style_underline a {
			border-bottom: dotted blue 2px !important;
		}
	</style>
</head>

<body>
	<section id="container">
		<?php include_once($include . 'include_top_menu.php'); ?>
		<!--sidebar start-->
		<?php include_once($include . 'left_menu.php'); ?>
		<!--sidebar end-->
		<!--main content start-->
		<section id="main-content">
			<section class="wrapper">
				<?php
				//				include_once('../include/quick_link.php');
				?>

				<div class="row">
					<div class="col-lg-12">
						<!--breadcrumbs start -->
						<section class="panel">
							<header class="panel-heading">
								<h3><?= $mode . ' ' . $form ?></h3>
							</header>
							<div class="">
								<ul class="breadcrumb">
									<li><a href="<?= ROOT . 'dashboard' ?>"><i class="fa fa-home"></i> Home</a></li>
									<li><a href="<?= ROOT . FINANCE_ROOT ?>finance_report_list">Finance Report</a></li>
									<li><a href="<?= ROOT . FINANCE_ROOT . 'gstr-1-report.php' ?>">GSTR 1 Report</a></li>
									<?php if ($type == 'documents_issued_details') { ?>
										<li><a href="<?= ROOT . FINANCE_ROOT . 'gst1_report_details.php?type=documents_issued' ?>">Issued Documents</a></li>
									<?php } ?>
								</ul>
							</div>
						</section>
						<!--breadcrumbs end -->
					</div>
				</div>
				<!--state overview start-->
				<div class="row">
					<div class="col-sm-12">
						<section class="panel">

							<input type="hidden" id="type_id" value="<?= $type; ?>" />
							<input type="hidden" id="start_date" value="<?= date("Y-m-d", strtotime($_SESSION['start'])); ?>" />
							<input type="hidden" id="end_date" value="<?= date("Y-m-d", strtotime($_SESSION['end'])); ?>" />

							<header class="panel-heading">
								<?php if ($type == 'gst_b2b_invoice') { ?>
									<h3>GSTR-1 Details - B2B Invoices-4A,4B,4C,6B,6C</h3>
								<?php } else if ($type == 'gst_b2c_large') { ?>
									<h3>B2C Large Invoices-5A,5B</h3>
								<?php } else if ($type == 'gst_b2c_small') { ?>
									<h3>B2C Small Details 7</h3>
								<?php } else if ($type == 'gst_creditnote_unregd') { ?>
									<h3>Credit/Debit Notes Unregistered - 9B</h3>
								<?php } else if ($type == 'gst_creditnote_unregd') { ?>
									<h3>Credit/Debit Notes Registered - 9B</h3>
								<?php } else if ($type == 'tax_liability_received') { ?>
									<h3>Tax Liability Advances Received - 11A(1),11A(2)</h3>
								<?php } else if ($type == 'hsn_summary') { ?>
									<h3>HSN Wise Summary of Outwars Supplies - 12</h3>
								<?php } else if ($type == 'documents_issued') { ?>
									<h3>Documents Issued During the tax period</h3>
								<?php } else if ($type == 'documents_issued_details') { ?>
									<h3>Documents Issued During the tax period <?= $doc_type; ?></h3>
								<?php } else if ($type == 'export_invoice') { ?>
									<h3>Export Invoices</h3>
								<?php } else if ($type == 'nill_rated') { ?>
									<h3>Nil Rated,Exempted and Non Gst (8)</h3>
								<?php } ?>
							</header>
							<div class="panel-body">

								<div class="row">

									<div class="col-md-12">

										<!-- GST B2B Invoices --->

										<?php
										if ($type == 'gst_b2b_invoice') {

											$q = "select i.invoice_id,i.cust_id,i.invoice_no,i.invoice_date,i.g_total,l.cust_gst_reg,l.l_name,i.currency_rate,
									(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as cgst_total,
									(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as sgst_total,
									(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as igst_total
									from tbl_invoice as i 
									left join tbl_ledger as l on l.l_id=i.cust_id 
									where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='0' or l.enable_sez=1";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display all taxable transactions (both local and central) made to registered dealers along with reverse charges and sale made to SEZ unit or deemed exports</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Invoice No</th>
														<th>Invoice Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['g_total'] * $row['currency_rate'];
													$total += $amount;
													$cgst += $row['cgst_total'];
													$sgst += $row['sgst_total'];
													$igst += $row['igst_total'];

												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><a href="<?= ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id']; ?>"><?= $row['l_name'] ?></a></td>
															<td><?= $row['invoice_no'] ?></td>
															<td><?= date("d/m/Y", strtotime($row['invoice_date'])) ?></td>
															<td><?= number_format($amount,2); ?></td>
															<td><?= number_format($row['cgst_total'],2); ?></td>
															<td><?= number_format($row['sgst_total'],2); ?></td>
															<td><?= number_format($row['igst_total'],2); ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>

										<!-- GST B2C Large Invoices --->


										<?php
										if ($type == 'gst_b2c_large') {

											$q = "select i.invoice_id,i.cust_id,i.invoice_no,i.invoice_date,i.g_total,l.cust_gst_reg,l.l_name,i.currency_rate,
									(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as cgst_total,
									(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as sgst_total,
									(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as igst_total
									from tbl_invoice as i 
									left join tbl_ledger as l on l.l_id=i.cust_id 
									where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and l.cust_gst_reg='1' and i.g_total*i.currency_rate > 250000";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display central taxable transactions with invoice value more than 2.5 lac made to un-registered delares</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Invoice No</th>
														<th>Invoice Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['g_total'] * $row['currency_rate'];
													$total += $amount;
													$cgst += $row['cgst_total'];
													$sgst += $row['sgst_total'];
													$igst += $row['igst_total'];

												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><a href="<?= ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id']; ?>"><?= $row['l_name'] ?></a></td>
															<td><?= $row['invoice_no'] ?></td>
															<td><?= date("d/m/Y", strtotime($row['invoice_date'])) ?></td>
															<td><?= $amount; ?></td>
															<td><?= $row['cgst_total']; ?></td>
															<td><?= $row['sgst_total']; ?></td>
															<td><?= $row['igst_total']; ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>


										<!-- GST B2C Small Invoices --->


										<?php
										if ($type == 'gst_b2c_small') {

											$q = "select i.invoice_id,i.cust_id,i.invoice_no,i.invoice_date,i.g_total,l.cust_gst_reg,l.l_name,i.currency_rate,l.stateid,
									(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as cgst_total,
									(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as sgst_total,
									(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_id = i.invoice_id group by i.invoice_id ) as igst_total
									from tbl_invoice as i 
									left join tbl_ledger as l on l.l_id=i.cust_id 
									where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and IF(l.stateid!='$company_state',i.g_total*i.currency_rate <= 250000, i.g_total*i.currency_rate >0)=1 and l.cust_gst_reg='1'";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display all taxable transactions made to un-registered dealers and central transactions of value upto 2.5 lac</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Invoice No</th>
														<th>Invoice Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['g_total'] * $row['currency_rate'];
													$total += $amount;
													$cgst += $row['cgst_total'];
													$sgst += $row['sgst_total'];
													$igst += $row['igst_total'];

												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><a href="<?= ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id']; ?>"><?= $row['l_name'] ?></a></td>
															<td><?= $row['invoice_no'] ?></td>
															<td><?= date("d/m/Y", strtotime($row['invoice_date'])) ?></td>
															<td><?= $amount; ?></td>
															<td><?= $row['cgst_total']; ?></td>
															<td><?= $row['sgst_total']; ?></td>
															<td><?= $row['igst_total']; ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>


										<!-- Credit - Debit note unregistered --->

										<?php
										if ($type == 'gst_creditnote_unregd') {

											$q = "select i.sale_return_id as voucher_id,i.sal_return_voucher_no as voucher_no,i.sale_return_date as date,
									
										(select sum(sale_return_cgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id=i.sale_return_id group by i.sale_return_id ) as cgst_total,
										
										(select sum(sale_return_sgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id=i.sale_return_id group by i.sale_return_id ) as sgst_total,
										
										(select sum(sale_return_igst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id=i.sale_return_id group by i.sale_return_id ) as igst_total,
										
										i.sale_return_gtotal*i.currency_rate as total,0 as gst_total ,'C' as type ,l.l_name as ledger_name,l.stateid as state
										
										from tbl_sale_return as i 
										
										left join tbl_ledger as l on l.l_id=i.sale_return_customer 
										
										where i.isdelete='0' and i.sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg='1'
										
										UNION
										
										select p.trn_voucher_id as voucher_id,r.receipt_no as voucher_no,r.receipt_date as date,0 as cgst_total,0 as sgst_total,0 as igst_total,p.trn_amount*p.currency_rate as total,trn_gst as gst_total,'A' as type,l.l_name as ledger_name,l.stateid as state
										from tbl_advacne_receipt_trn as p 
										left join tbl_receipt as r on r.receipt_id=p.trn_voucher_id  
										left join tbl_ledger as l on l.l_id=p.cust_id 
										where l.cust_gst_reg='0' and r.receipt_date between '$st_date' and '$end_date' and p.advance_receipt_type='1'

										UNION

										select d.debitnote_id as voucher_id,d.debitnote_no as voucher_no,d.debitnote_date as date,
										
										(select sum(purchase_return_cgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id=d.debitnote_id group by d.debitnote_id ) as cgst_total,

										(select sum(purchase_return_sgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id=d.debitnote_id group by d.debitnote_id ) as sgst_total,

										(select sum(purchase_return_igst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id=d.debitnote_id group by d.debitnote_id ) as igst_total,

										d.g_total*d.currency_rate as total,0 as gst_total ,'D' as type ,l.l_name as ledger_name,l.stateid as state
										
										from tbl_debitnote as d 
										
										left join tbl_ledger as l on l.l_id=d.vender_id 
										
										where d.debit_note_status='0' and d.debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg='1'
										";
											//echo $q;	
											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display Taxable Debit/Credit Note,Sale Return and advance amount refunded to Un-registered dealers.</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Invoice No</th>
														<th>Invoice Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['total'];
													$total += $amount;
													if ($row['type'] == 'C') {
														$cgst_total = $row['cgst_total'];
														$sgst_total = $row['sgst_total'];
														$igst_total = $row['igst_total'];

														$href = ROOT . FINANCE_ROOT . 'salereturnedit/' . $row['voucher_id'];
													} else if ($row['type'] == 'D') {

														$cgst_total = $row['cgst_total'];
														$sgst_total = $row['sgst_total'];
														$igst_total = $row['igst_total'];

														$href = ROOT . FINANCE_ROOT . 'debitnoteedit/' . $row['voucher_id'];
													} else {
														if ($row['state'] == $company_state) {
															$cgst_total = ($row['total'] * ($row['gst_total'] / 2)) / 100;
															$sgst_total = ($row['total'] * ($row['gst_total'] / 2)) / 100;
															$igst_total = 0;
														} else {
															$cgst_total = 0;
															$sgst_total = 0;
															$igst_total = ($row['total'] * $row['gst_total']) / 100;
														}

														$href = ROOT . FINANCE_ROOT . 'salereturnedit/' . $row['voucher_id'];
													}

													$cgst += $cgst_total;
													$sgst += $sgst_total;
													$igst += $igst_total;


												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><a href="<?= $href; ?>"><?= $row['ledger_name'] ?></a></td>
															<td><?= $row['voucher_no'] ?>
																<br>
																<?php
																if ($row['type'] == 'C') {
																	echo "<strong style='color:red'>Credit Note</strong>";
																} else if ($row['type'] == 'D') {
																	echo "<strong style='color:red'>Debit Note</strong>";
																} else {
																	echo "<strong style='color:red'>Advance Payment</strong>";
																}
																?>
															</td>
															<td><?= date("d/m/Y", strtotime($row['date'])) ?></td>
															<td><?= $amount; ?></td>
															<td><?= $cgst_total; ?></td>
															<td><?= $sgst_total; ?></td>
															<td><?= $igst_total; ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>


										<!-- Credit - Debit note registered --->

										<?php
										if ($type == 'gst_creditnote_regd') {

											$q = "select i.sale_return_id as voucher_id,i.sal_return_voucher_no as voucher_no,i.sale_return_date as date,
									
										(select sum(sale_return_cgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id=i.sale_return_id group by i.sale_return_id ) as cgst_total,
										
										(select sum(sale_return_sgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id=i.sale_return_id group by i.sale_return_id ) as sgst_total,
										
										(select sum(sale_return_igst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status=0 and sale_return_id=i.sale_return_id group by i.sale_return_id ) as igst_total,
										
										i.sale_return_gtotal*i.currency_rate as total,0 as gst_total ,'C' as type ,l.l_name as ledger_name,l.stateid as state
										
										from tbl_sale_return as i 
										
										left join tbl_ledger as l on l.l_id=i.sale_return_customer 
										
										where i.isdelete='0' and i.sale_return_date between '$st_date' and '$end_date' and l.cust_gst_reg='0'
										
										UNION
										
										select p.trn_voucher_id as voucher_id,r.receipt_no as voucher_no,r.receipt_date as date,0 as cgst_total,0 as sgst_total,0 as igst_total,p.trn_amount*p.currency_rate as total,trn_gst as gst_total,'A' as type,l.l_name as ledger_name,l.stateid as state
										from tbl_advacne_receipt_trn as p 
										left join tbl_receipt as r on r.receipt_id=p.trn_voucher_id  
										left join tbl_ledger as l on l.l_id=p.cust_id 
										where l.cust_gst_reg='0' and r.receipt_date between '$st_date' and '$end_date' and p.advance_receipt_type='0'


										UNION

										select d.debitnote_id as voucher_id,d.debitnote_no as voucher_no,d.debitnote_date as date,
										
										(select sum(purchase_return_cgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id=d.debitnote_id group by d.debitnote_id ) as cgst_total,

										(select sum(purchase_return_sgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id=d.debitnote_id group by d.debitnote_id ) as sgst_total,

										(select sum(purchase_return_igst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status=0 and debitnote_id=d.debitnote_id group by d.debitnote_id ) as igst_total,

										d.g_total*d.currency_rate as total,0 as gst_total ,'D' as type ,l.l_name as ledger_name,l.stateid as state
										
										from tbl_debitnote as d 
										
										left join tbl_ledger as l on l.l_id=d.vender_id 
										
										where d.debit_note_status='0' and d.debitnote_date between '$st_date' and '$end_date' and l.cust_gst_reg='0'

										";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display taxable central debit/credit note,Sale return , advance amount refunded to un-registered dealers for invoice value above 2.5 lacs and for export transaction.</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Invoice No</th>
														<th>Invoice Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['total'];
													$total += $amount;
													if ($row['type'] == 'C') {
														$cgst_total = $row['cgst_total'];
														$sgst_total = $row['sgst_total'];
														$igst_total = $row['igst_total'];

														$href = ROOT . FINANCE_ROOT . 'salereturnedit/' . $row['voucher_id'];
													} else if ($row['type'] == 'D') {

														$cgst_total = $row['cgst_total'];
														$sgst_total = $row['sgst_total'];
														$igst_total = $row['igst_total'];

														$href = ROOT . FINANCE_ROOT . 'debitnoteedit/' . $row['voucher_id'];
													} else {
														if ($row['state'] == $company_state) {
															$cgst_total = ($row['total'] * ($row['gst_total'] / 2)) / 100;
															$sgst_total = ($row['total'] * ($row['gst_total'] / 2)) / 100;
															$igst_total = 0;
														} else {
															$cgst_total = 0;
															$sgst_total = 0;
															$igst_total = ($row['total'] * $row['gst_total']) / 100;
														}

														$href = ROOT . FINANCE_ROOT . 'salereturnedit/' . $row['voucher_id'];
													}

													$cgst += $cgst_total;
													$sgst += $sgst_total;
													$igst += $igst_total;


												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><a href="<?= $href; ?>"><?= $row['ledger_name'] ?></a></td>
															<td><?= $row['voucher_no'] ?>
																<br>
																<?php
																if ($row['type'] == 'C') {
																	echo "<strong style='color:red'>Credit Note</strong>";
																} else if ($row['type'] == 'D') {
																	echo "<strong style='color:red'>Debit Note</strong>";
																} else {
																	echo "<strong style='color:red'>Advance Payment</strong>";
																}
																?>
															</td>
															<td><?= date("d/m/Y", strtotime($row['date'])) ?></td>
															<td><?= $amount; ?></td>
															<td><?= $cgst_total; ?></td>
															<td><?= $sgst_total; ?></td>
															<td><?= $igst_total; ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>

										<!-- Tax Liability Received --->


										<?php
										if ($type == 'tax_liability_received') {

											$q = "select p.trn_amount*p.currency_rate as total,trn_gst as gst_total,p.trn_voucher_id,r.receipt_date,l.l_name,l.stateid,r.receipt_no
									from tbl_advacne_receipt_trn as p 
									left join tbl_receipt as r on r.receipt_id=p.trn_voucher_id  
									left join tbl_ledger as l on l.l_id=p.cust_id 
									where r.receipt_date between '$st_date' and '$end_date' and p.advance_receipt_type='0'";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display central taxable transactions with invoice value more than 2.5 lac made to un-registered delares</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Voucher No</th>
														<th>Voucher Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['total'];
													$total += $amount;
													if ($row['stateid'] == $company_state) {
														$cgst_total = ($row['total'] * ($row['gst_total'] / 2)) / 100;
														$sgst_total = ($row['total'] * ($row['gst_total'] / 2)) / 100;
														$igst_total = 0;
													} else {
														$cgst_total = 0;
														$sgst_total = 0;
														$igst_total = ($row['total'] * $row['gst_total']) / 100;
													}

													$cgst += $cgst_total;
													$sgst += $sgst_total;
													$igst += $igst_total;
												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><a href="<?= ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['trn_voucher_id']; ?>"><?= $row['l_name'] ?></a></td>
															<td><?= $row['receipt_no'] ?></td>
															<td><?= date("d/m/Y", strtotime($row['receipt_date'])) ?></td>
															<td><?= $amount; ?></td>
															<td><?= $cgst_total; ?></td>
															<td><?= $sgst_total; ?></td>
															<td><?= $igst_total; ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>

										<!-- HSN Wise Summary Details --->

										<?php
										if ($type == 'nill_rated') {

											$q = "select trn.product_amount*trn.currency_rate as total_amount,i.invoice_id,l.l_name,i.invoice_no,i.invoice_date,i.g_total
									 from tbl_invoice as i
									left join tbl_invoicetrn as trn on trn.invoice_id=i.invoice_id 
									left join tbl_ledger as l on l.l_id=i.cust_id 
									where  i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and trn.product_tax_cat='22' OR trn.product_tax_cat='23' OR trn.product_tax_cat='24'";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display All Transactions Made for Exempt,Nill Rated & Non-GST Supplies</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>Receiver Name</th>
														<th>Voucher No</th>
														<th>Voucher Date</th>
														<th>Amount</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['g_total'];
													$total += $amount;

												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><?= $row['l_name'] ?></td>
															<td><a href="<?= ROOT . FINANCE_ROOT . 'invoiceedit/' . $row['invoice_id']; ?>" target="_blank"><?= $row['invoice_no'] ?></a></td>
															<td><?= date("d/m/Y", strtotime($row['invoice_date'])) ?></td>
															<td><?= $amount; ?></td>
															<td>0</td>
															<td>0</td>
															<td>0</td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="4" class="text-right">Total:</th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>


										<?php
										if ($type == 'hsn_summary') {

											$q = "select i.invoice_id,invtrn.product_hsn_code,
	
									(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and product_hsn_code=invtrn.product_hsn_code group by invtrn.product_hsn_code ) as cgst_total,
									
									(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and product_hsn_code=invtrn.product_hsn_code group by invtrn.product_hsn_code ) as sgst_total,
									
									(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and product_hsn_code=invtrn.product_hsn_code group by invtrn.product_hsn_code ) as igst_total,
									
									(select sum(product_qty) from tbl_invoicetrn where trancation_status=0 and product_hsn_code=invtrn.product_hsn_code group by invtrn.product_hsn_code) as total_qty,
									
									(select sum(product_amount) from tbl_invoicetrn where trancation_status=0 and product_hsn_code=invtrn.product_hsn_code group by invtrn.product_hsn_code) as total,

									l.cust_gst_reg from tbl_invoice as i 
									left join tbl_ledger as l on l.l_id=i.cust_id
									left join tbl_invoicetrn as invtrn on invtrn.invoice_id=i.invoice_id
									where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' group by invtrn.product_hsn_code";

											$query = $dbcon->query($q);

										?>
											<strong class="gst_details">* Display HSN - Wise Consolidated transaction summary i.e for each HSN code , all the transaction made will be displayed collectively</strong>
											<table class="table table-bordered table-hover" id="">
												<thead>
													<tr>
														<th>#</th>
														<th>HSN Code</th>
														<th>Total Qty</th>
														<th>Total Value</th>
														<th>CGST</th>
														<th>SGST</th>
														<th>IGST</th>
													</tr>
												</thead>
												<?php

												$cnt = 1;
												$total = 0;
												$cgst = 0;
												$sgst = 0;
												$igst = 0;
												$total_qty = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {
													$amount = $row['total'];
													$total += $amount;
													$cgst += $row['cgst_total'];
													$sgst += $row['sgst_total'];
													$igst += $row['igst_total'];
													$total_qty += $row['total_qty'];

													$q_1 = get_tax_cat_by_hsn($dbcon, $row['product_hsn_code']);
												?>
													<tbody>
														<tr>
															<td><?= $cnt; ?></td>
															<td><?= $row['product_hsn_code'] . '(' . $q_1['tax_gst'] . '% )' ?></td>
															<td><?= $row['total_qty']; ?></td>
															<td><?= $row['total']; ?></td>
															<td><?= $row['cgst_total']; ?></td>
															<td><?= $row['sgst_total']; ?></td>
															<td><?= $row['igst_total']; ?></td>
														</tr>
													</tbody>
												<?php $cnt++;
												} ?>

												<tr>
													<th colspan="2" class="text-right">Total:</th>
													<th><?= $total_qty; ?></th>
													<th><?= $total; ?></th>
													<th><?= $cgst; ?></th>
													<th><?= $sgst; ?></th>
													<th><?= $igst; ?></th>
												</tr>
											</table>

										<?php } ?>

										<?php
										if ($type == 'documents_issued') {
										?>
											<strong class="gst_details">* Summary Of Documents Issued During the tax period (13)</strong>
											<table class="table table-bordered table-hover" id="">
												<tr>
													<th>#</th>
													<th>Nature Of Documents</th>
													<th>Total</th>
												</tr>
												<tr>
													<th>1</th>
													<td class="style_underline"><a href="<?= ROOT . FINANCE_ROOT ?>gst1_report_details.php?type=documents_issued_details&&doc_type=invoice">Invoice</a></td>
													<td>
														<?php
														$inv_tot = get_gst_document_by_type($dbcon, $st_date, $end_date, 'invoice');
														echo $inv_tot;
														?>

													</td>
												</tr>
												<tr>
													<th>2</th>
													<td class="style_underline"><a href="<?= ROOT . FINANCE_ROOT ?>gst1_report_details.php?type=documents_issued_details&&doc_type=cr_note">Credit Note</a></td>
													<td>
														<?php
														$cr_note_total = get_gst_document_by_type($dbcon, $st_date, $end_date, 'cr_note');
														echo $cr_note_total;
														?></td>
												</tr>
												<tr>
													<th>3</th>
													<td class="style_underline"><a href="<?= ROOT . FINANCE_ROOT ?>gst1_report_details.php?type=documents_issued_details&&doc_type=jv">Journal Voucher</a></td>
													<td><?php
														$jv_total = get_gst_document_by_type($dbcon, $st_date, $end_date, 'jv');
														echo $jv_total;
														?></td>
												</tr>

												<tr>
													<th colspan="2" style="text-align: right;">Total:</th>
													<th><?= $inv_tot + $cr_note_total + $jv_total; ?></th>
												</tr>
											</table>
										<?php
										}
										?>

										<?php
										if ($type == 'export_invoice') {
										?>
											<strong class="gst_details">* Export Invoices - 6A : Display all normal exports apart from SEZ unit and deemed exports which are covered under B2B section</strong>
											<table class="table table-bordered table-hover" id="">
												<tr>
													<th>#</th>
													<th>Account Name</th>
													<th>Invoice No</th>
													<th>Date</th>
													<th>Amount</th>
													<th>CGST</th>
													<th>SGST</th>
													<th>IGST</th>
												</tr>

												<?php

												$q = "select i.invoice_id,i.invoice_date,l.l_name,(i.g_total*i.currency_rate) as total,i.invoice_no,
									(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1'  and invoice_id = i.invoice_id group by i.invoice_id ) as cgst_total,
									(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1' and invoice_id = i.invoice_id group by i.invoice_id ) as sgst_total,
									(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status=0 and invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1' and invoice_id = i.invoice_id group by i.invoice_id ) as igst_total

									 from tbl_invoice as i 
									left join tbl_ledger as l on l.l_id=i.cust_id 
									where i.invoice_status='0' and i.invoice_date between '$st_date' and '$end_date' and i.currency_enable='1' and i.currency_id!='1'";

												$query = $dbcon->query($q);
												$cnt = 1;
												$total = 0;
												$cgst_total = 0;
												$sgst_total = 0;
												$igst_total = 0;
												while ($row = brp_mysqli_fetch_assoc($query)) {

													$total += $row['total'];
													$cgst_total += $row['cgst_total'];
													$sgst_total += $row['sgst_total'];
													$igst_total += $row['igst_total'];

													echo "<tr>

											<th>" . $cnt . "</th>
											<th>" . $row['l_name'] . "</th>
											<th class='style_underline'><a href='" . ROOT . FINANCE_ROOT . "invoiceedit/" . $row['invoice_id'] . "' target='_blank'>" . $row['invoice_no'] . "</a></th>
											<th>" . date("d/m/Y", strtotime($row['invoice_date'])) . "</th>
											<th>" . round($row['total'], 2) . "</th>
											<th>" . round($row['cgst_total'], 2) . "</th>
											<th>" . round($row['sgst_total'], 2) . "</th>
											<th>" . round($row['igst_total'], 2) . "</th>

										</tr>";

													$cnt++;
												}

												?>
												<tr>
													<th colspan="4" align="right">Total:</th>
													<th><?= round($total, 2); ?></th>
													<th><?= round($cgst_total, 2); ?></th>
													<th><?= round($sgst_total, 2); ?></th>
													<th><?= round($igst_total, 2); ?></th>
												</tr>
											</table>
										<?php
										}
										?>

										<?php
										if ($type == 'documents_issued_details') {
										?>

											<table class="table table-bordered table-hover" id="">
												<tr>
													<th>#</th>
													<th>Date</th>
													<th>Voucher / Bill No</th>
													<th>Account Name</th>
													<th>Amount</th>
												</tr>

												<?php

												if ($doc_type == 'invoice') {
													$q = "select i.invoice_id,i.invoice_date,i.invoice_no,i.cust_id,i.g_total,l.l_name from tbl_invoice as i inner join tbl_ledger as l on l.l_id=i.cust_id where i.invoice_status=0 and i.invoice_date between '$st_date' and '$end_date' and i.company_id='$_SESSION[company_id]' ";


													$query = $dbcon->query($q);
													$cnt = 1;
													$total = 0;
													while ($row = brp_mysqli_fetch_assoc($query)) {

														echo "<tr>

													<th>" . $cnt . "</th>
													<td>" . date("d/m/Y", strtotime($row['invoice_date'])) . "</td>
													<td  class='style_underline'><a href='" . ROOT . FINANCE_ROOT . "invoiceedit/" . $row['invoice_id'] . "' target='_blank'>" . $row['invoice_no'] . "</a></td>
													<td>" . $row['l_name'] . "</td>
													<td>" . $row['g_total'] . "</td>
												</tr>";

														$total += $row['g_total'];

														$cnt++;
													}

													echo "<tr>
												<th colspan='4' style='text-align:right'>Total:</th>
												<th>" . $total . "</th>
											</tr>";
												}


												if ($doc_type == 'cr_note') {
													$q = "select s.sale_return_id,s.sale_return_date,s.sal_return_voucher_no,s.sale_return_customer,s.sale_return_gtotal,l.l_name from tbl_sale_return as s inner join tbl_ledger as l on l.l_id=s.sale_return_customer where s.isdelete=0 and s.is_without_item='0' and s.sale_return_date between '$st_date' and '$end_date' and s.company_id='$_SESSION[company_id]' ";


													$query = $dbcon->query($q);
													$cnt = 1;
													$total = 0;
													while ($row = brp_mysqli_fetch_assoc($query)) {

														echo "<tr>

													<th>" . $cnt . "</th>
													<td>" . date("d/m/Y", strtotime($row['sale_return_date'])) . "</td>
													<td  class='style_underline'><a href='" . ROOT . FINANCE_ROOT . "salereturnedit/" . $row['sale_return_id'] . "' target='_blank'>" . $row['sal_return_voucher_no'] . "</td>
													<td>" . $row['l_name'] . "</a></td>
													<td>" . $row['sale_return_gtotal'] . "</td>
												</tr>";

														$total += $row['sale_return_gtotal'];

														$cnt++;
													}

													echo "<tr>
												<th colspan='4' style='text-align:right'>Total:</th>
												<th>" . $total . "</th>
											</tr>";
												}


												if ($doc_type == 'jv') {
													$q = "select j.journal_no,j.journal_id,j.journal_date,j.journal_status,jt.amount from tbl_journal as j left join tbl_journal_trn as jt on jt.journal_id=j.journal_id where j.journal_status='0' and j.company_id='$_SESSION[company_id]' and j.gst_nature='97' and j.journal_date between '$st_date' and '$end_date'";


													$query = $dbcon->query($q);
													$cnt = 1;
													$total = 0;
													while ($row = brp_mysqli_fetch_assoc($query)) {

														echo "<tr>

													<th>" . $cnt . "</th>
													<td>" . date("d/m/Y", strtotime($row['journal_date'])) . "</td>
													<td class='style_underline'><a href='" . ROOT . FINANCE_ROOT . "journal_entry_edit/" . $row['journal_id'] . "' target='_blank'>" . $row['journal_no'] . "</a></td>
													<td>--</td>
													<td>" . $row['amount'] . "</td>
												</tr>";

														$total += $row['amount'];

														$cnt++;
													}

													echo "<tr>
												<th colspan='4' style='text-align:right'>Total:</th>
												<th>" . $total . "</th>
											</tr>";
												}
												?>

											</table>
										<?php
										}
										?>

									</div>
								</div>

							</div>

						</section>
					</div>
				</div>
				<!--state overview end-->

			</section>

		</section>
		<!--main content end-->
		<!--footer start-->
		<?php include_once($include . 'footer.php'); ?>
		<!--footer end-->
	</section>

	<!-- js placed at the end of the document so the pages load faster -->
	<?php include_once($include . 'include_js_file.php'); ?>
	<!--<script src="js/count.js"></script>-->
	<script>
		$(document).ready(function() {
			$('#example').DataTable();
		});

		$(".select2").select2({
			width: '100%'
		});
		$('.default-date-picker').datepicker({
			format: 'dd-mm-yyyy',
			autoclose: true
		});

		function cb(start, end) {
			$('.datepikerdemo span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
		}
		cb(moment().subtract(29, 'days'), moment());


		$('.datepikerdemo').daterangepicker({
			locale: {
				format: 'DD-MM-YYYY'
			},
			"autoApply": true,
			"startDate": $('#from_date').val(),
			"endDate": $('#to_date').val(),
			ranges: {
				//'Today': [moment(), moment()],
				//'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
				//'Last 7 Days': [moment().subtract(6, 'days'), moment()],
				'Last 30 Days': [moment().subtract(29, 'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
				'Last 3 Month': [moment().subtract(3, 'months'), moment().endOf('month')],
				'Last 6 Month': [moment().subtract(6, 'months'), moment().endOf('month')],
				'Last 1 Year': [moment().subtract(12, 'months'), moment().endOf('month')]
			}
		}, cb);
		$('.date-set').click(function() {
			$('.datepikerdemo').trigger('click')
		});

		function paymentmode(id) {
			if (id == "2") {
				$('#cheque_dtl').val('');
				$('#cheque_data').show();
			} else
				$('#cheque_data').hide();
		}
	</script>
	<script>
		var tableToExcel = (function() {
			var uri = 'data:application/vnd.ms-excel;base64,',
				template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
				base64 = function(s) {
					return window.btoa(unescape(encodeURIComponent(s)))
				},
				format = function(s, c) {
					return s.replace(/{(\w+)}/g, function(m, p) {
						return c[p];
					})
				}
			return function(table, name) {
				if (!table.nodeType) table = document.getElementById(table)
				var ctx = {
					worksheet: name || 'Worksheet',
					table: table.innerHTML
				}
				window.location.href = uri + base64(format(template, ctx))
			}
		})()

		function PrintMe(DivID) {
			$('#logo').css('display', '');
			var disp_setting = "toolbar=yes,location=no,";
			var content_vlue = $('#report_head').show();
			disp_setting += "directories=yes,menubar=yes,";
			disp_setting += "scrollbars=yes,width=800, height=600, left=100, top=25";

			content_vlue = document.getElementById(DivID).innerHTML;
			var docprint = window.open("", "", disp_setting);
			docprint.document.open();
			docprint.document.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"');
			docprint.document.write('"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
			docprint.document.write('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">');
			docprint.document.write('<head><title><?= TITLE ?></title>');
			docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT; ?>css/style.css" media="all"/>');
			docprint.document.write('<link rel="stylesheet" href="<?php echo ROOT; ?>css/bootstrap.min.css" media="all"/>');

			docprint.document.write('<style type="text/css">body { margin:20px 10px 10px 35px;');
			docprint.document.write('font-family:Tahoma;color:#000;');
			docprint.document.write('font-family:Tahoma,Verdana; font-size:10px;} .dataTables_length, .dataTables_filter , .dataTables_paginate { display:none; }');
			docprint.document.write('#mainpart table,#mainpart tr,#mainpart td,#mainpart th {border:1px #eee solid;padding:2px 5px 2px 5px;text-align:center;}');
			docprint.document.write('a{color:#000;text-decoration:none;} h1 {font-size:25px; line-height:5px;} b { font-weight:normal; } div.page { page-break-after: always; page-break-inside: avoid; } </style>');
			docprint.document.write('</head><body onLoad="self.print()"><center>');
			docprint.document.write(content_vlue);
			docprint.document.write('</center></body></html>');
			docprint.document.close();
			$('#report_head').hide()
			docprint.focus();

			$('#logo').css('display', 'none');
		}
	</script>
</body>

</html>