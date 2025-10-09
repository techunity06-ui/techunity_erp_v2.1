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

//print_r($_POST);
//print_r($_FILES);
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
			
			//3.1
			$outward_invoice = get_outward_invoice($dbcon,$start_date,$end_date);
			$outward_invoice_zero = get_outward_invoice_zero($dbcon,$start_date,$end_date);
			$outward_invoice_nill = get_outward_invoice_nill($dbcon,$start_date,$end_date);
			$outward_invoice_non = get_outward_invoice_non($dbcon,$start_date,$end_date);
			//4 (A) - 1
			//$import_goods_itc = get_import_goods_itc($dbcon,$start_date,$end_date);
			//$import_service_itc = get_import_service_itc($dbcon,$start_date,$end_date);
			
			$str.="<table class='table table-bordered'>";
			
				$str.="<tr style='background-color:#F1F2F7 !important'>
							<th colspan='6' style='text-align:center'>Legal Name Of Registered Person - ".$company_row['company_name']."</th>
						</tr>";
						
				$str.="<tr>
							<th colspan='6' class='th_back'>3.1 Details of Outward Supplies and inward supplies liable to reverse charge</th>
						</tr>";
				
				$str.="
					<tr>
						<th>Nature Of Supplier</th>
						<th>Txbl. value</th>
						<th>IGST</th>
						<th>CGST</th>
						<th>SGST</th>
						<th></th>
					</tr>
					
					<tr>
						<th><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=3.1a'>(a) Outward txbl. supplies (other than zero rated , nil rated and exempted)</a></th>
						<td>".round($outward_invoice['total'],2)."</td>
						<td>".round($outward_invoice['igst_rate'],2)."</td>
						<td>".round($outward_invoice['cgst_rate'],2)."</td>
						<td>".round($outward_invoice['sgst_rate'],2)."</td>
						<td></td>
					</tr>
					
					<tr>
						<th><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=3.1b'>(b) Outward Taxable supplies Zero Rated</a></th>
						<td>".round($outward_invoice_zero['total'],2)."</td>
						<td>".round($outward_invoice_zero['igst_rate'],2)."</td>
						<td>".round($outward_invoice_zero['cgst_rate'],2)."</td>
						<td>".round($outward_invoice_zero['sgst_rate'],2)."</td>
						<td></td>
					</tr>
					
					<tr>
						<th><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=3.1c'>(c) Other Outward supp.(Nill rated , exempted)</a></th>
						<td>".round($outward_invoice_nill['total'],2)."</td>
						<td>".round($outward_invoice_nill['igst_rate'],2)."</td>
						<td>".round($outward_invoice_nill['cgst_rate'],2)."</td>
						<td>".round($outward_invoice_nill['sgst_rate'],2)."</td>
						<td></td>
					</tr>
					
					<tr>
						<th>(d) Inward supplies.(liable to reverse charges)</th>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					
					<tr>
						<th><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=3.1e'>(e) Non GST Outward Supplies</a></th>
						<td>".round($outward_invoice_non['total'],2)."</td>
						<td>".round($outward_invoice_non['igst_rate'],2)."</td>
						<td>".round($outward_invoice_non['cgst_rate'],2)."</td>
						<td>".round($outward_invoice_non['sgst_rate'],2)."</td>
						<td></td>
					</tr>
					
					<tr>
						<th style='text-align:right'>Total</th>
						<th>".($outward_invoice['total']+$outward_invoice_zero['total']+$outward_invoice_nill['total']+$outward_invoice_non['total'])."</th>
						<th>".($outward_invoice['igst_rate']+$outward_invoice_zero['igst_rate']+$outward_invoice_nill['igst_rate']+$outward_invoice_non['igst_rate'])."</th>
						<th>".($outward_invoice['cgst_rate']+$outward_invoice_zero['cgst_rate']+$outward_invoice_nill['cgst_rate']+$outward_invoice_non['cgst_rate'])."</th>
						<th>".($outward_invoice['sgst_rate']+$outward_invoice_zero['sgst_rate']+$outward_invoice_nill['sgst_rate']+$outward_invoice_non['sgst_rate'])."</th>
						<td></td>
					</tr>

				";
				
						
				$str.="<tr>
							<th colspan='6' class='th_back'>3.2 Of the suppliers shown in 3.1(a) above, details of inter-state supplies made to un-registered persons,composition taxable persons and UIN holders</th>
						</tr>";
				
				$str.="<tr>
					
					<th colspan='2'></th>
					<th>Place Of Supply (State/UT)</th>
					<th>Total Taxable Value</th>
					<th>Amount OF IGST</th>
					<th></th>
				</tr>";
				
				$str.="
					
					<tr>
						<th colspan='6'><span class='th_text'>Supplies Made to Unreg. Persons</span></th>
					</tr>";
					
					$total_unreg=0;$igst_unreg=0;
					$selx=$dbcon->query("select i.invoice_id,i.cust_id,l.stateid,s.state_name,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i1 on i1.invoice_id=trn.invoice_id left join tbl_ledger as l1 on l1.l_id=i1.cust_id where trn.trancation_status=0 and l1.stateid=l.stateid and l1.cust_gst_reg='1' ) as igst_total, (select sum(trn.product_amount*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i2 on i2.invoice_id=trn.invoice_id left join tbl_ledger as l2 on l2.l_id=i2.cust_id where trn.trancation_status=0 and l2.stateid=l.stateid and l2.cust_gst_reg='1' ) as total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$start_date' and '$end_date' and l.cust_gst_reg='1' and l.stateid!='$company_state' group by l.stateid");


					while($rowx=brp_mysqli_fetch_assoc($selx))
					{
						$total_unreg+=$rowx['total'];
						$igst_unreg+=$rowx['igst_total'];
						
						$str.="<tr>
							<td colspan='2'></td>
							<td><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=unreg_supply&&state=".$rowx['stateid']."'>".$rowx['state_name']."</a></td>
							<td>".round($rowx['total'],2)."</td>
							<td>".round($rowx['igst_total'],2)."</td>
							<th></th>
						</tr>";
					}
					
					$str.="<tr>
						<th colspan='3' style='text-align:right'>Total:</th>
						<th>".round($total_unreg,2)."</th>
						<th>".round($igst_unreg,2)."</th>
						<th></th>
					</tr>
					
					<tr>
						<th colspan='6'><span class='th_text'>Supplies Made to Composition Dealers</span></th>
					</tr> ";
					
					$total_composition=0;$igst_composition=0;
					$selx=$dbcon->query("select i.invoice_id,i.cust_id,l.stateid,s.state_name,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i1 on i1.invoice_id=trn.invoice_id left join tbl_ledger as l1 on l1.l_id=i1.cust_id where trn.trancation_status=0 and l1.stateid=l.stateid and l1.cust_gst_reg='2' ) as igst_total, (select sum(trn.product_amount*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i2 on i2.invoice_id=trn.invoice_id left join tbl_ledger as l2 on l2.l_id=i2.cust_id where trn.trancation_status=0 and l2.stateid=l.stateid and l2.cust_gst_reg='2' ) as total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$start_date' and '$end_date' and l.cust_gst_reg='2' and l.stateid!='$company_state' group by l.stateid");

					//echo "select i.invoice_id,i.cust_id,l.stateid,s.state_name,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i1 on i1.invoice_id=trn.invoice_id left join tbl_ledger as l1 on l1.l_id=i1.cust_id where trn.trancation_status=0 and l1.stateid=l.stateid ) as igst_total, (select sum(trn.product_amount*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i2 on i2.invoice_id=trn.invoice_id left join tbl_ledger as l2 on l2.l_id=i2.cust_id where trn.trancation_status=0 and l2.stateid=l.stateid ) as total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$start_date' and '$end_date' and l.cust_gst_reg='1' group by l.stateid";
					while($rowx=brp_mysqli_fetch_assoc($selx))
					{
						$total_composition+=$rowx['total'];
						$igst_composition+=$rowx['igst_total'];
						
						$str.="<tr>
							<td colspan='2'></td>
							<td><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=composition_supply&&state=".$rowx['stateid']."'>".$rowx['state_name']."</a></td>
							<td>".round($rowx['total'],2)."</td>
							<td>".round($rowx['igst_total'],2)."</td>
							<th></th>
						</tr>";
					}
					
					$str.="
					<tr>
						<th colspan='3' style='text-align:right'>Total:</th>
						<th>".round($total_composition,2)."</th>
						<th>".round($igst_composition,2)."</th>
						<th></th>
					</tr>
					
					<tr>
						<th colspan='6'><span class='th_text'>Supplies Made to UIN Holders</span></th>
					</tr>";
					
					$total_uin=0;$igst_uin=0;
					$selx=$dbcon->query("select i.invoice_id,i.cust_id,l.stateid,s.state_name,(select sum(trn.igst_tax_rate*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i1 on i1.invoice_id=trn.invoice_id left join tbl_ledger as l1 on l1.l_id=i1.cust_id where trn.trancation_status=0 and l1.stateid=l.stateid and l1.cust_gst_reg='4' ) as igst_total, (select sum(trn.product_amount*trn.currency_rate) from tbl_invoicetrn as trn left join tbl_invoice as i2 on i2.invoice_id=trn.invoice_id left join tbl_ledger as l2 on l2.l_id=i2.cust_id where trn.trancation_status=0 and l2.stateid=l.stateid and l2.cust_gst_reg='4' ) as total from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id left join state_mst as s on s.stateid=l.stateid where i.invoice_status='0' and i.invoice_date between '$start_date' and '$end_date' and l.cust_gst_reg='4' and l.stateid!='$company_state' group by l.stateid");
					while($rowx=brp_mysqli_fetch_assoc($selx))
					{
						$total_uin+=$rowx['total'];
						$igst_uin+=$rowx['igst_total'];
						
						$str.="<tr>
							<td colspan='2'></td>
							<td><a href='".ROOT.FINANCE_ROOT."gst3b_details.php?type=uin_supply&&state=".$rowx['stateid']."'>".$rowx['state_name']."</a></td>
							<td>".round($rowx['total'],2)."</td>
							<td>".round($rowx['igst_total'],2)."</td>
							<th></th>
						</tr>";
					}
					
					$str.="<tr>
						<th colspan='3' style='text-align:right'>Total:</th>
						<th>".round($total_uin,2)."</th>
						<th>".round($igst_uin,2)."</th>
						<th></th>
					</tr>
				";
				
				$str.="<tr>
						<th colspan='6' class='th_back'>4 Eligible ITC</th>
					</tr>";
				
				$str.="
					<th></th>
					<th>Details</th>
					<th>Integrated Tax</th>
					<th>Central Tax</th>
					<th>State/UT Tax</th>
					
				";
				
				$str.="<tr>
						<th colspan='6'><span class='th_text'>(A) ITC Available ( Whether in Full or Part )</span></th>
					</tr>";
					
				$str.="<tr>
						<td></td>
						<th>(1) Import Of Goods</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>
				";
				
				$str.="
					<tr>
						<td></td>
						<th>(2) Import Of Services</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>
				";
				
				$str.="
					<tr>
						<td></td>
						<th>(3) Inward supplies to reverse
							(other than 1 & 2)
						</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>
				";
				
				$str.="
					<tr>
						<td></td>
						<th>(4) Inward supplies from ISD
						</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>
				";

				$otherITC = $dbcon->query("SELECT sum(pt.cgst_tax_rate) as cgst_rate,sum(pt.sgst_tax_rate) as sgst_rate,sum(pt.igst_tax_rate) as igst_rate 
					FROM `tbl_pono` as p 
					left join tbl_potrancation as pt on pt.po_id=p.po_id
					where pt.potrancation_status=0 and p.po_date between '".$start_date."' and '".$end_date."' ");
				$otherITC_r = brp_mysqli_fetch_array($otherITC);
				
				$str.="
					<tr>
						<td></td>
						<th>(5) All other ITC
						</th>
						<td>".round($otherITC_r['igst_rate'],2)."</td>
						<td>".round($otherITC_r['cgst_rate'],2)."</td>
						<td>".round($otherITC_r['sgst_rate'],2)."</td>
						<td></td>
					</tr>
				";
				
				$str.="<tr>
						<th colspan='6'><span class='th_text'>(B) ITC Available ( Whether in Full or Part )</span></th>
					</tr>";
					
				$str.="<tr>
						<td></td>
						<th>(1) As per rules 42 & 43 of GST Rules</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>";
				
				$str.="<tr>
						<td></td>
						<th>(2) Others</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>";
					
				$str.="<tr>
						<th><span class='th_text'>(C) Net ITC Availibility (A)-(B)</span></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					<tr>
						<th colspan='6'>&nbsp;</th>
					</tr>
					";
					
				$str.="<tr>
						<th colspan='6'><span class='th_text'>(D) Ineligible ITC </span></th>
					</tr>";
					
				$str.="<tr>
						<td></td>
						<th>(1) As per Other Section 17(5)</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td></td>
					</tr>";
					
				$str.="<tr>
						<td></td>
						<th>(2) Others</th>
						<td>0</td>
						<td>0</td>
						<td>0</td>
						<td>0</td>
					</tr>";
					
				$str.="<tr>
						<th colspan='6' class='th_back'>5 Values Of Exempt, Nil rated and non GST Inward Supplies</th>
					</tr>";
					
				$str.="<tr>
						<th colspan='2'>Nature Of Supplies</th>
						<th colspan='2'>Iter State Supplies</th>
						<th colspan='2'>Intra State Supplies</th>
					</tr>
					
					<tr>
						<th colspan='2'>0</th>
						<th colspan='2'>0</th>
						<th colspan='2'>0</th>
					</tr>
					
					";
				
				/*
				
				$str.="<tr>
						<th colspan='6' class='th_back'>6.1 Payment Of Tax</th>
					</tr>";
					
				$str.="<tr>
						<th>Description</th>
						<th>Tax Payable</th>
						<th>Paid Through ITC
							<table class='table table-bordered'>
								<tr>
									<th>IGST</th>
									<th>CGST</th>
									<th>SGST</th>
								</tr>
							</table>
						</th>
						<th>Tax Paid TDS/TCS</th>
						<th>Interest</th>
						<th>Late Fee</th>
					</tr>";
				
				$str.="<tr>
						<th colspan='6'><span class='th_text'> Other Than Reverse Charge </span></th>
					</tr>";
					
				$str.="
					
					<tr>
						<th>Integrated Tax</th>
						<td colspan='5'></td>
					</tr>
					
					<tr>
						<th>Central Tax</th>
						<td colspan='5'></td>
					</tr>
					
					<tr>
						<th>State/UT Tax</th>
						<td colspan='5'></td>
					</tr>
				
				";
				
				$str.="<tr>
						<th colspan='6'><span class='th_text'> Reverse Charge </span></th>
					</tr>";
					
				$str.="
					
					<tr>
						<th>Integrated Tax</th>
						<td colspan='5'></td>
					</tr>
					
					<tr>
						<th>Central Tax</th>
						<td colspan='5'></td>
					</tr>
					
					<tr>
						<th>State/UT Tax</th>
						<td colspan='5'></td>
					</tr>
				
				";
				
				$str.="<tr>
						<th colspan='6' class='th_back'>TDS/TCS Credit</th>
					</tr>";
					
				$str.="
					
					<tr>
						<th colspan='2'>Details</th>
						<th>Integrated Tax</th>
						<th>Central Tax</th>
						<th colspan='2'>State/Ut Tax</th>
					</tr>
					
					<tr>
						<th colspan='2'>TDS</th>
						<td></td>
						<td></td>
						<td colspan='2'></td>
					</tr>
					
					<tr>
						<th colspan='2'>TCS</th>
						<td></td>
						<td></td>
						<td colspan='2'></td>
					</tr>
				"; */
				
			$str.="</table>";
			
			echo $str;
							
		}
		

?>