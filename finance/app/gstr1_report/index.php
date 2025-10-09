<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");


		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") 
		{
			
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$start_date = date("Y-m-d",strtotime($s_date[0]));
			$end_date = date("Y-m-d",strtotime($s_date[1]));
			
			$company_row = get_company_data($dbcon,$_SESSION['company_id']);
			$company_state=$company_row['stateid'];
			
			$where ='';
			$str ='';
			
			//b2b invoice
			$b2b = get_b2b_invoice($dbcon,$start_date,$end_date);
			$b2b_tax_total=$b2b['cgst_total']+$b2b['sgst_total']+$b2b['igst_total']+$b2b['tcs_total'];
			$b2b_taxable_amt = $b2b['total']-$b2b_tax_total;
			
			//b2c large invoice
			$b2c = get_b2c_large_invoice($dbcon,$start_date,$end_date,$company_state);
			$b2c_tax_total=$b2c['cgst_total']+$b2c['sgst_total']+$b2c['igst_total'];
			$b2c_taxable_amt = $b2c['total']-$b2c_tax_total;
			
			//b2c small invoice
			$b2c_small = get_b2c_small_invoice($dbcon,$start_date,$end_date,$company_state);
			$b2c_small_tax_total=$b2c_small['cgst_total']+$b2c_small['sgst_total']+$b2c_small['igst_total'];
			$b2c_small_taxable_amt = $b2c_small['total']-$b2c_small_tax_total;
			
			//Credit - Debit note registered
			$crdr_regd = get_crdr_note_registered($dbcon,$start_date,$end_date,$company_state);
			$crdr_regd_tax_total=$crdr_regd['cgst_total']+$crdr_regd['sgst_total']+$crdr_regd['igst_total'];
			$crdr_regd_taxable_amt = $crdr_regd['total']-$crdr_regd_tax_total;
			
			//Credit - Debit note Unregistered
			$crdr_notregd = get_crdr_note_unregistered($dbcon,$start_date,$end_date,$company_state);
			$crdr_notregd_tax_total=$crdr_notregd['cgst_total']+$crdr_notregd['sgst_total']+$crdr_notregd['igst_total'];
			$crdr_notregd_taxable_amt = $crdr_notregd['total']-$crdr_notregd_tax_total;
			
			//Export Invoices - 6A
			$export_invoice = get_export_invoice_gst($dbcon,$start_date,$end_date,$company_state);
			$export_invoice_tax_total=$export_invoice['cgst_total']+$export_invoice['sgst_total']+$export_invoice['igst_total'];
			$export_invoice_taxable_amt = $export_invoice['total']-$export_invoice_tax_total;
			
			//Tax Liability(Advance Received) 
			$tax_liability = get_tax_liability($dbcon,$start_date,$end_date,$company_state);
			$tax_liability_tax_total=$tax_liability['cgst_total']+$tax_liability['sgst_total']+$tax_liability['igst_total'];
			$tax_liability_taxable_amt = $tax_liability['total']-$tax_liability_tax_total;
			
			//HSN Wise Summary 
			$hsn_summary = get_hsn_summary($dbcon,$start_date,$end_date,$company_state);
			$hsn_summary_tax_total=$hsn_summary['cgst_total']+$hsn_summary['sgst_total']+$hsn_summary['igst_total'];
			$hsn_summary_taxable_amt = $hsn_summary['total']-$hsn_summary_tax_total;

			//GST Nill Rated 
			$gstnill = get_gst_nill_invoice($dbcon,$start_date,$end_date,$company_state);

			$total_document = get_gst_document($dbcon,$start_date,$end_date);
			
			$str.="<table class='table table-bordered table-hover'>
				
				<tr style='background-color:#F1F2F7 !important'>
					<th>Section Name</th>
					<th>No.Of Records</th>
					<th>Total Invoice Amt.</th>
					<th>Total Taxable Amt.</th>
					<th>Total Tax Liability</th>
					<th>Total CGST Amt.</th>
					<th>Total SGST Amt.</th>
					<th>Total IGST Amt.</th>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=gst_b2b_invoice'>B2B Invoices-4A,4B,4C,6B,6C</a></th>
					<td>".$b2b['total_count']."</td>
					<td>".round($b2b['total'],2)."</td>
					<td>".round($b2b_taxable_amt,2)."</td>
					<td>".round($b2b_tax_total,2)."</td>
					<td>".round($b2b['cgst_total'],2)."</td>
					<td>".round($b2b['sgst_total'],2)."</td>
					<td>".round($b2b['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=gst_b2c_large'>B2C Large Invoices-5A,5B</a></th>
					<td>".$b2c['total_count']."</td>
					<td>".round($b2c['total'],2)."</td>
					<td>".round($b2c_taxable_amt,2)."</td>
					<td>".round($b2c_tax_total,2)."</td>
					<td>".round($b2c['cgst_total'],2)."</td>
					<td>".round($b2c['sgst_total'],2)."</td>
					<td>".round($b2c['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=gst_b2c_small'>B2C Small Details 7</a></th>
					<td>".$b2c_small['total_count']."</td>
					<td>".round($b2c_small['total'],2)."</td>
					<td>".round($b2c_small_taxable_amt,2)."</td>
					<td>".round($b2c_small_tax_total,2)."</td>
					<td>".round($b2c_small['cgst_total'],2)."</td>
					<td>".round($b2c_small['sgst_total'],2)."</td>
					<td>".round($b2c_small['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=gst_creditnote_unregd'>Credit/Debit Notes Unregistered - 9B</a></th>
					<td>".$crdr_notregd['total_count']."</td>
					<td>".round($crdr_notregd['total'],2)."</td>
					<td>".round($crdr_notregd_tax_total,2)."</td>
					<td>".round($crdr_notregd_taxable_amt,2)."</td>
					<td>".round($crdr_notregd['cgst_total'],2)."</td>
					<td>".round($crdr_notregd['sgst_total'],2)."</td>
					<td>".round($crdr_notregd['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=gst_creditnote_regd'>Credit/Debit Notes Registered - 9B</a></th>
					<td>".$crdr_regd['total_count']."</td>
					<td>".round($crdr_regd['total'],2)."</td>
					<td>".round($crdr_regd_tax_total,2)."</td>
					<td>".round($crdr_regd_taxable_amt,2)."</td>
					<td>".round($crdr_regd['cgst_total'],2)."</td>
					<td>".round($crdr_regd['sgst_total'],2)."</td>
					<td>".round($crdr_regd['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a  href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=export_invoice'>Export Invoices - 6A</a></th>
					<td>".$export_invoice['total_count']."</td>
					<td>".round($export_invoice['total'],2)."</td>
					<td>".round($export_invoice_tax_total,2)."</td>
					<td>".round($export_invoice_taxable_amt,2)."</td>
					<td>".round($export_invoice['cgst_total'],2)."</td>
					<td>".round($export_invoice['sgst_total'],2)."</td>
					<td>".round($export_invoice['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=tax_liability_received'>Tax Liability Advances Received - 11A(1),11A(2)</a></th>
					<td>".$tax_liability['total_count']."</td>
					<td>".round($tax_liability['total'],2)."</td>
					<td>".round($tax_liability_tax_total,2)."</td>
					<td>".round($tax_liability_taxable_amt,2)."</td>
					<td>".round($tax_liability['cgst_total'],2)."</td>
					<td>".round($tax_liability['sgst_total'],2)."</td>
					<td>".round($tax_liability['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th>Adjustment Of Advance - 11B(1),11B(2)</th>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=nill_rated'>Nil Rated,Exempted and Non Gst (8) </a></th>
					<td>".round($gstnill['total_count'],2)."</td>
					<td>".round($gstnill['total_amount'],2)."</td>
					<td>".round($gstnill['total_amount'],2)."</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
					<td>0</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=hsn_summary'>HSN Wise Summary of Outwars Supplies - 12</a></th>
					<td>".$hsn_summary['total_count']."</td>
					<td>".round($hsn_summary['total'],2)."</td>
					<td>".round($hsn_summary_tax_total,2)."</td>
					<td>".round($hsn_summary_taxable_amt,2)."</td>
					<td>".round($hsn_summary['cgst_total'],2)."</td>
					<td>".round($hsn_summary['sgst_total'],2)."</td>
					<td>".round($hsn_summary['igst_total'],2)."</td>
				</tr>
				
				<tr>
					<th><a href='".ROOT.FINANCE_ROOT."gst1_report_details.php?type=documents_issued'>Summary Of Documents Issued During the tax period (13)</a></th>
					<td colspan='7'>".$total_document."</td>
				</tr>
				
			</table>";
			
			echo $str;
							
		}
		

?>