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
			
			//Input GST
			
			$str.="<table class='table table-bordered'>";
					
				$str.="<tr>
					<th colspan='6' class='th_back'>Input GST</th>
				</tr>";	
				
				$str.="
					<tr class='th_back_second'>
						<th>#</th>
						<th>Category</th>
						<th>Taxable Amount</th>
						<th>IGST</th>
						<th>CGST</th>
						<th>SGST</th>
					</tr>
				";
				
				$cnt=1;$total_taxable_input=0;$total_cgst_input=0;$total_sgst_input=0;$total_igst_input=0;
				$sel=$dbcon->query("select tc.*,
				(select sum(product_amount*currency_rate) from tbl_potrancation where potrancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as total,
				(select sum(igst_tax_rate*currency_rate) from tbl_potrancation where potrancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as igst_total,
				(select sum(cgst_tax_rate*currency_rate) from tbl_potrancation where potrancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as cgst_total,
				(select sum(sgst_tax_rate*currency_rate) from tbl_potrancation where potrancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as sgst_total 
				from tbl_tax_category as tc 
				where tc.isdelete='0' and tc.company_id='".$_SESSION['company_id']."' group by tc.tax_cat_id having total > 0");
				while($row=brp_mysqli_fetch_assoc($sel))
				{
					$taxable_amount=$row['total']==''?'0':$row['total'];
					$cgst_amount=$row['cgst_total']==''?'0':$row['cgst_total'];
					$sgst_amount=$row['sgst_total']==''?'0':$row['sgst_total'];
					$igst_amount=$row['igst_total']==''?'0':$row['igst_total'];
					
					$total_taxable_input+=$taxable_amount;
					$total_cgst_input+=$cgst_amount;
					$total_sgst_input+=$sgst_amount;
					$total_igst_input+=$igst_amount;
					
					$str.="<tr>
						
						<th>".$cnt."</th>
						<th>".$row['tax_cat_name']."</th>
						<th>".$taxable_amount."</th>
						<th>".$igst_amount."</th>
						<th>".$cgst_amount."</th>
						<th>".$sgst_amount."</th>
						
					
					</tr>";
					
					$cnt++;
				}
				
				$str.="
					
					<tr class='th_back_second'>
						<th colspan='2' style='text-align:right'>Total:</th>
						<th>".$total_taxable_input."</th>
						<th>".$total_igst_input."</th>
						<th>".$total_cgst_input."</th>
						<th>".$total_sgst_input."</th>
					</tr>
				
				";
				
			$str.="</table>";
			
			//Debit Note (Input GST)
			
			$str.="<table class='table table-bordered'>";
					
				$str.="<tr>
					<th colspan='6' class='th_back'>Input GST (Debit Note)</th>
				</tr>";	
				
				$str.="
					<tr class='th_back_second'>
						<th>#</th>
						<th>Category</th>
						<th>Taxable Amount</th>
						<th>IGST</th>
						<th>CGST</th>
						<th>SGST</th>
					</tr>
				";
				
				$cnt=1;$total_taxable_input_cr=0;$total_cgst_input_cr=0;$total_sgst_input_cr=0;$total_igst_input_cr=0;
				$sel=$dbcon->query("select tc.*,
				(select sum(product_amount*currency_rate) from tbl_debitnote_trn where debitnote_trn_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as total,
				(select sum(purchase_return_igst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as igst_total,
				(select sum(purchase_return_cgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as cgst_total,
				(select sum(purchase_return_sgst_tax_amt*currency_rate) from tbl_debitnote_trn where debitnote_trn_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as sgst_total 
				from tbl_tax_category as tc 
				where tc.isdelete='0' and tc.company_id='".$_SESSION['company_id']."' group by tc.tax_cat_id having total > 0");
				while($row=brp_mysqli_fetch_assoc($sel))
				{
					$taxable_amount=$row['total']==''?'0':$row['total'];
					$cgst_amount=$row['cgst_total']==''?'0':$row['cgst_total'];
					$sgst_amount=$row['sgst_total']==''?'0':$row['sgst_total'];
					$igst_amount=$row['igst_total']==''?'0':$row['igst_total'];
					
					$total_taxable_input_cr+=$taxable_amount;
					$total_cgst_input_cr+=$cgst_amount;
					$total_sgst_input_cr+=$sgst_amount;
					$total_igst_input_cr+=$igst_amount;
					
					$str.="<tr>
						
						<th>".$cnt."</th>
						<th>".$row['tax_cat_name']."</th>
						<th>".$taxable_amount."</th>
						<th>".$igst_amount."</th>
						<th>".$cgst_amount."</th>
						<th>".$sgst_amount."</th>
						
					
					</tr>";
					
					$cnt++;
				}
				
				$str.="
					
					<tr class='th_back_second'>
						<th colspan='2' style='text-align:right'>Total:</th>
						<th>".$total_taxable_input_cr."</th>
						<th>".$total_igst_input_cr."</th>
						<th>".$total_cgst_input_cr."</th>
						<th>".$total_sgst_input_cr."</th>
					</tr>
				
				";
				
			$str.="</table>";
			
			
			//Output GST
			
			$str.="<table class='table table-bordered'>";
					
				$str.="<tr>
					<th colspan='6' class='th_back'>Output GST</th>
				</tr>";	
				
				$str.="
					<tr class='th_back_second'>
						<th>#</th>
						<th>Category</th>
						<th>Taxable Amount</th>
						<th>IGST</th>
						<th>CGST</th>
						<th>SGST</th>
					</tr>
				";
				
				$cnt=1;$total_taxable_output=0;$total_cgst_output=0;$total_sgst_output=0;$total_igst_output=0;
				$sel=$dbcon->query("select tc.*,
				(select sum(product_amount*currency_rate) from tbl_invoicetrn where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as total,
				(select sum(igst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as igst_total,
				(select sum(cgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as cgst_total,
				(select sum(sgst_tax_rate*currency_rate) from tbl_invoicetrn where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as sgst_total 
				from tbl_tax_category as tc 
				where tc.isdelete='0' and tc.company_id='".$_SESSION['company_id']."' group by tc.tax_cat_id having total > 0");
				while($row=brp_mysqli_fetch_assoc($sel))
				{
					$taxable_amount=$row['total']==''?'0':$row['total'];
					$cgst_amount=$row['cgst_total']==''?'0':$row['cgst_total'];
					$sgst_amount=$row['sgst_total']==''?'0':$row['sgst_total'];
					$igst_amount=$row['igst_total']==''?'0':$row['igst_total'];
					
					$total_taxable_output+=$taxable_amount;
					$total_cgst_output+=$cgst_amount;
					$total_sgst_output+=$sgst_amount;
					$total_igst_output+=$igst_amount;
					
					$str.="<tr>
						
						<th>".$cnt."</th>
						<th>".$row['tax_cat_name']."</th>
						<th>".$taxable_amount."</th>
						<th>".$igst_amount."</th>
						<th>".$cgst_amount."</th>
						<th>".$sgst_amount."</th>
						
					
					</tr>";
					
					$cnt++;
				}
				
				$str.="
					
					<tr class='th_back_second'>
						<th colspan='2' style='text-align:right'>Total:</th>
						<th>".$total_taxable_output."</th>
						<th>".$total_igst_output."</th>
						<th>".$total_cgst_output."</th>
						<th>".$total_sgst_output."</th>
					</tr>
				
				";
				
			$str.="</table>";
			
			
			//Credit Note (Output GST)
			
			$str.="<table class='table table-bordered'>";
					
				$str.="<tr>
					<th colspan='6' class='th_back'>Output GST (Credit Note)</th>
				</tr>";	
				
				$str.="
					<tr class='th_back_second'>
						<th>#</th>
						<th>Category</th>
						<th>Taxable Amount</th>
						<th>IGST</th>
						<th>CGST</th>
						<th>SGST</th>
					</tr>
				";
				
				$cnt=1;$total_taxable_output_cr=0;$total_cgst_output_cr=0;$total_sgst_output_cr=0;$total_igst_output_cr=0;
				$sel=$dbcon->query("select tc.*,
				(select sum(sale_return_amount*currency_rate) from tbl_sale_return_transaction where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as total,
				(select sum(sale_return_igst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as igst_total,
				(select sum(sale_return_cgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as cgst_total,
				(select sum(sale_return_sgst_tax_amt*currency_rate) from tbl_sale_return_transaction where trancation_status='0' and product_tax_cat=tc.tax_cat_id group by tc.tax_cat_id) as sgst_total 
				from tbl_tax_category as tc 
				where tc.isdelete='0' and tc.company_id='".$_SESSION['company_id']."' group by tc.tax_cat_id having total > 0");
				while($row=brp_mysqli_fetch_assoc($sel))
				{
					$taxable_amount=$row['total']==''?'0':$row['total'];
					$cgst_amount=$row['cgst_total']==''?'0':$row['cgst_total'];
					$sgst_amount=$row['sgst_total']==''?'0':$row['sgst_total'];
					$igst_amount=$row['igst_total']==''?'0':$row['igst_total'];
					
					$total_taxable_output_cr+=$taxable_amount;
					$total_cgst_output_cr+=$cgst_amount;
					$total_sgst_output_cr+=$sgst_amount;
					$total_igst_output_cr+=$igst_amount;
					
					$str.="<tr>
						
						<th>".$cnt."</th>
						<th>".$row['tax_cat_name']."</th>
						<th>".$taxable_amount."</th>
						<th>".$igst_amount."</th>
						<th>".$cgst_amount."</th>
						<th>".$sgst_amount."</th>
						
					
					</tr>";
					
					$cnt++;
				}
				
				$str.="
					
					<tr class='th_back_second'>
						<th colspan='2' style='text-align:right'>Total:</th>
						<th>".$total_taxable_output_cr."</th>
						<th>".$total_igst_output_cr."</th>
						<th>".$total_cgst_output_cr."</th>
						<th>".$total_sgst_output_cr."</th>
					</tr>
				
				";
				
			$str.="</table>";
			
			$gst_payable = ($total_taxable_input+$total_taxable_input_cr)-($total_taxable_output+$total_taxable_output_cr);
			
			$str.="<table class='table table-bordered'>";
				
				$str.="<tr class='th_back'>
					
					<th>Total Input GST</th>
					<th>Total Output GST</th>
					<th>GST Payable</th>
					
				</tr>
				
				<tr  class='th_back_second'>
					<th>".($total_taxable_input+$total_taxable_input_cr)."</th>
					<th>".($total_taxable_output+$total_taxable_output_cr)."</th>
					<th>".$gst_payable."</th>
				</tr>
				
				";
				
			$str.="</table>";
			
			echo $str;
							
		}
		

?>