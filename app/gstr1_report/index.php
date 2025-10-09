<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
include("../../config/image.php");

//print_r($_POST);
//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") {
                $s_date=explode(' - ',$POST['date']);
                $_SESSION['start']=$s_date[0];
		$_SESSION['end']=$s_date[1];
		$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$where ='';
			if(!empty($POST['typeid']))
			{
				$where=' and invoicetype_id='.$POST['typeid'];
					
			}
                        $_SESSION['invoicetype_id']=$where;
			$str='';
                        $qry='Select invoice.invoice_id,invoice.invoice_no,invoice.invoice_date,invoice.g_total,invoice.cust_id,cust.gst_no,state.gst_state_code,state.state_name,sum(trn.taxable_value) as taxable_amt ,(SELECT sum(tax_value) FROM `formula_mst` as fmst left join tbl_tax as tax on find_in_set(tax.tax_id,fmst.tax_id) where formulaid=trn.formulaid AND tax.tax_name LIKE  "%GST%") as tax_rate
                            from tbl_invoice as invoice 
                            left join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id 
			inner join tbl_ledger as cust on invoice.cust_id=cust.l_id and gst_no!=""
			inner join state_mst as state on state.stateid=cust.stateid where invoice_status=0 and trn.trancation_status=0 and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where.' and invoice.company_id='.$_SESSION['company_id'].' group by invoice.invoice_id,trn.formulaid order by invoice_no';
			$result=$dbcon->query($qry);
			
			 $str .='<div id="report" class="col-md-12" name="report" width="100%">
														
						<table class="col-md-12 display table table12 table-striped" border=1 width="100%" id="b2b_table">
						<tr id="logo" class="logo hide_csv" style="display:none">
						<td colspan="11" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr class="hide_csv">
						<td colspan="2"><strong>GSTR1 B2B Report</strong></td>
						
						<td colspan="6" style="text-align:center">Date
							<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
						</td>
						<td colspan="3" style="text-align:right">
							<!--<button class="btn btn-info btn-flat" id="export_csv" onclick="return b2bcsv_export();">Export CSV</button>-->
						</td>
				
					</tr>
					<tr>
						<th width="5%" style="text-align:center">GSTIN/UIN of Recipient</th>
						<th width="5%" style="text-align:center">Invoice Number</th>
						<th width="10%" style="text-align:center">Invoice date</th>
						<th width="7%" style="text-align:center">Invoice Value </th>
						<th width="10%" style="text-align:center">Place Of Supply</th>
						<th width="5%" style="text-align:center">Reverse Charge</th>
						<th width="7%" style="text-align:center">Invoice Type</th>
						<th width="7%" style="text-align:center">E-Commerce GSTIN</th>
						<th width="4%" style="text-align:center">Rate</th>
						<th width="7%" style="text-align:center">Taxable Value</th>
						<th width="5%" style="text-align:center">Cess Amount</th>
						</tr>';
				$j=1;
				if(mysqli_num_rows($result)>0)
				{
					while($re=mysqli_fetch_assoc($result))
					{	
						$str.='<tr >
						  <td data-label="GSTIN/UIN of Recipient"  >'.$re["gst_no"].'</td>
					  	  <td data-label="Invoice Number">'.$re["invoice_no"].'</td>
						  <td data-label="Invoice date">'.date('d-M-y',strtotime($re["invoice_date"])).'</td>
						  <td data-label="Invoice Value" >'.number_format($re["g_total"],2,".",",").'</td>
					  	  <td data-label="Place Of Supply" >'.$re['gst_state_code'].'-'.$re['state_name'].'</td>
					  	  <td data-label="Reverse Charge" >'.($re['reverse_charge']=="0"?'N':'Y').'</td>
					  	  <td data-label="Invoice Type">Regular</td>
						  <td data-label="E-Commerce GSTIN">-</td>
						  <td data-label="Rate">'.floatval($re["tax_rate"]).'</td>
						  <td data-label="Taxable Value">'.number_format($re["taxable_amt"],2,".",",").'</td>
						  <td data-label="Cess Amount">0</td>';
						
				 		$str .='</tr>';				
						$j++;
						$total=$total+$re["g_total"];
						$total_taxable+=$re["taxable_amt"];
						
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="11" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			/*$str .='<tr class="hide_csv">
						 <td colspan="3" style="text-align:right"> <strong>Total</strong></td>
						 <td style="text-align:right">
							<label><strong>'.indian_number($total).'</strong></label>
						 </td>
						  <td colspan="5" style="text-align:right"></td>
						 <td style="text-align:right">
							<label><strong>'.indian_number($total_taxable).'</strong></label>
						 </td>
						 <td style="text-align:right"></td>
						 </tr>
						*/	
				 $str .='</tbody>				 
				  </table>';
				$arr['b2b_data']=$str;  
				  //gstr1 b2cs
			$qry='Select state.gst_state_code,state.state_name,sum(product_amount) as taxable_amt ,(SELECT sum(tax_value) FROM `formula_mst` as fmst left join tbl_tax as tax on find_in_set(tax.tax_id,fmst.tax_id) where formulaid=trn.formulaid AND tax.tax_name LIKE  "%GST%") as tax_rate
                            from tbl_invoice as invoice 
                            left join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id 
                            inner join tbl_ledger as cust on invoice.cust_id=cust.l_id and gst_no=""
                            inner join state_mst as state on state.stateid=cust.stateid 
                            where invoice_status=0 and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where.' and invoice.company_id='.$_SESSION['company_id'].' 
                            group by cust.stateid,trn.formulaid 
                            order by invoice_no';
			$result=$dbcon->query($qry);
			$str='<table class="col-md-12 display table table12 table-striped" border=1 width="100%" id="b2cs_table">
					<tr class="hide_csv">
						<td colspan="2"><strong>GSTR1 B2CS Report</strong></td>
						<td colspan="2" style="text-align:center">Date
							<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
						</td>
						<td colspan="2" style="text-align:right">
							<!--<button class="btn btn-info btn-flat" id="export_csv" onclick="return b2cscsv_export();">Export CSV</button>-->
						</td>
					</tr>	
					<tr>
						<th width="5%" style="text-align:center">Type</th>
						<th width="10%" style="text-align:center">Place Of Supply</th>
						<th width="4%" style="text-align:center">Rate</th>
						<th width="7%" style="text-align:center">Taxable Value</th>
						<th width="5%" style="text-align:center">Cess Amount</th>
						<th width="7%" style="text-align:center">E-Commerce GSTIN</th>
						</tr>';
				$j=1;
				if(mysqli_num_rows($result)>0)
				{
					while($re=mysqli_fetch_assoc($result))
					{	
						$str.='<tr >
						  <td data-label="Type">OE</td>
					  	  <td data-label="Place Of Supply">'.$re['gst_state_code'].'-'.$re['state_name'].'</td>
						  <td data-label="Rate">'.floatval($re["tax_rate"]).'</td>
						  <td data-label="Taxable Value">'.$re["taxable_amt"].'</td>
					  	  <td data-label="Cess Amount">-</td>
						  <td data-label="E-Commerce GSTIN">-</td>';
						$str .='</tr>';				
						$j++;						
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			
				 $str .='</tbody>				 
				  </table>';
				$arr['b2cs_data'].=$str;    
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "totalval") {
					$s_date=explode(' - ',$POST['date']);
				
			 $qry='Select invoice.*,trn.product_discount,trn.discount_per,sum(product_amount) as taxable_amt ,sum(g_total) as g_total,cust.*
			from tbl_invoice as invoice 
			left join tbl_invoicetrn as trn on trn.invoice_id=invoice.invoice_id 
			inner join tbl_customer as cust on invoice.cust_id=cust.cust_id and gst_no!="" 
			where invoice_status=0  and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'" group by invoice.invoice_id order by invoice_no';
			$result=$dbcon->query($qry);
		$j=1;
				if(mysqli_num_rows($result)>0)
				{
					while($re=mysqli_fetch_assoc($result))
					{
						$amount = $amount+$re['taxable_amt'];
						$gtotal =$gtotal+ $re['g_total'];
						
						$j++;
					}
					
				}
				else
				{
						$amount =0;
						$gtotal =0;
						
				}
				$rowc['taxable_amt']=$amount;
				$rowc['g_total']=$gtotal;
				
				echo json_encode($rowc);
			
	
					
							
		}

?>