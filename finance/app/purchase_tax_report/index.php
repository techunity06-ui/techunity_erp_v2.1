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
include_once(COMMON_FUNCTION_INNER_PATH."common_sub_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
			if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") {
            $s_date=explode(' - ',$POST['date']);
		    $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$where ='';
			if($POST['cust_id']){
				$where = 'and inv.cust_id='.$POST['cust_id'];
			}
            
			$str='';
			$qry='select inv.po_id,inv.po_no,inv.po_date,led.l_name,led.gst_no,sum(invtrn.product_amount_conv) as taxable,inv.round_off,inv.g_total from tbl_pono as inv 
			left join tbl_potrancation as invtrn on invtrn.po_id = inv.po_id  
			left join tbl_ledger as led on led.l_id = inv.vender_id
			where inv.status=0 and invtrn.potrancation_status=0 and inv.po_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and inv.po_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and inv.company_id='.$_SESSION['company_id'].' '.$where.' group by inv.po_id order by po_no';
			$result=$dbcon->query($qry);

			$query = "SELECT led.l_name,gen.ledger_id FROM `tbl_general_book` as gen left join tbl_ledger as led on led.l_id = gen.ledger_id WHERE gen.module_name='Purchase' and led.l_group=31 and gen.company_id = ".$_SESSION['company_id']." group by gen.ledger_id";
			$rs_tax=$dbcon->query($query);
            $tax_col=mysqli_num_rows($rs_tax);
			$tax_row = brp_mysqli_fetch_all($rs_tax);
            
			$query_sun = "SELECT led.l_name,gen.ledger_id FROM `tbl_general_book` as gen left join tbl_ledger as led on led.l_id = gen.ledger_id WHERE gen.module_name='Purchase' and led.l_group in (16,19) and gen.company_id = ".$_SESSION['company_id']." group by gen.ledger_id";
			$rs_sun=$dbcon->query($query_sun);
            $sun_col=mysqli_num_rows($rs_sun);
			$sun_row = brp_mysqli_fetch_all($rs_sun);
			
            $str .='<div id="report" class="col-md-12" name="report" width="100%">
						 <br><br>								
						<table class="col-md-12 display table table12 table-bordered table-striped" border=1 width="100%">
						<tr id="logo" class="logo" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr>
						<td colspan="5"><strong>Sale Tax Report</strong></td>
						
						<td colspan="'.(3+$tax_col+$sun_col).'" style="text-align:right">Date
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
					</td>
				
					</tr>
					<tr>
						<th style="text-align:center" width="3%">Sr No </th>
						<th style="text-align:center" width="10%"> Bill No </th>
						<th style="text-align:center" width="8%">Bill Date</th>
						<th style="text-align:center" width="5%">GSTIN</th>
						<th style="text-align:center" width="15%">Customer Name</th>
						<th style="text-align:center" width="5%">Taxable Amount</th>';
						foreach($sun_row as $row1){
							$str.='<th style="text-align:center" width="5%">'.$row1['l_name'].'</th>';
						}
						$tax_arr=array();
						foreach($tax_row as $row){
							$str.='<th style="text-align:center" width="5%">'.$row['l_name'].'</th>';
						}
					$str .='<th style="text-align:center" width="5%">Round off</th>';
					$str .='<th style="text-align:center" width="5%">Total Amount</th></tr>';
							$j=1;
				if(mysqli_num_rows($result)>0)
				{
					$total = 0;
					while($re=mysqli_fetch_assoc($result))
					{	
				
						if(!empty($re['gst_no'])){
							$gstno = $re["gst_no"];
						}else{
							$gstno = '-';
						}
						$str.='<tr>
						  <td data-label="Sr No" style="text-align:center">'.$j.'</td>
					  	  <td data-label="Bill No" style="text-align:center">'.$re["po_no"].'</td>
					  	  <td data-label="Bill Date" style="text-align:center">'.date('d-m-Y',strtotime($re["po_date"])).'</td>
						  <td data-label="GSTIN" style="text-align:center">'.$gstno.'</td>
					  	  <td data-label="Customer Name" style="text-align:left">'.$re['l_name'].'</td>
					  	  <td data-label="Taxable Amount" style="text-align:right">'.indian_number($re["taxable"]).'</td>';
						$k=0;
						foreach($sun_row as $row1){
							$query_sundry_value = "SELECT gen.ledger_id,gen.amount FROM `tbl_general_book` as gen left join tbl_ledger as led on led.l_id = gen.ledger_id WHERE gen.module_name='Purchase' and led.l_group in (16,19) and gen.company_id = ".$_SESSION['company_id']." and gen.module_id=".$re['po_id']." and ledger_id=".$row1['ledger_id'];
							$result_sundry_value = $dbcon->query($query_sundry_value);
							$row_sundry_value = brp_mysqli_fetch_array($result_sundry_value);
							
							if(empty($row_sundry_value['amount'])){
								$row_sundry_value['amount']='0';
							}
							$str.='<td data-label="Tax Amount" style="text-align:right">'.$row_sundry_value['amount'].'</td>';
							$tax_arr['sundry'][$k]=$tax_arr['sundry'][$k]+$row_sundry_value['amount'];
							$k++;
						}

						$i=0;
						foreach($tax_row as $row){
							$query_tax_value = "SELECT gen.ledger_id,gen.amount FROM `tbl_general_book` as gen left join tbl_ledger as led on led.l_id = gen.ledger_id WHERE gen.module_name='Purchase' and led.l_group=31 and gen.company_id = ".$_SESSION['company_id']." and gen.module_id=".$re['po_id']." and ledger_id=".$row['ledger_id'];
							$result_tax_value = $dbcon->query($query_tax_value);
							$row_tax_value = brp_mysqli_fetch_array($result_tax_value);
							
							if(empty($row_tax_value['amount'])){
								$row_tax_value['amount']='0';
							}
							$str.='<td data-label="Tax Amount" style="text-align:right">'.$row_tax_value['amount'].'</td>';
							$tax_arr['total'][$i]=$tax_arr['total'][$i]+$row_tax_value['amount'];
							$totaltax = $totaltax + $row_tax_value['amount'];
							$i++;
						}

						

				 		$str .='<td data-label="Total Amount" style="text-align:right">'.indian_number($re["round_of"]).'</td>
						 <td data-label="Total Amount" style="text-align:right">'.indian_number($re["g_total"]).'</td>
						</tr>';				
						$j++;
						$total=$total+$re["g_total"];
						$roundof = $roundof + $re['round_of'];
						$total_taxable=$total_taxable+$re["taxable"];
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="'.(8+$tax_col+$sun_col).'" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='<tr>
						 <td class="tfhide" colspan="5" style="text-align:right"> <strong>Total</strong></td>
						 <td data-label="Total" style="text-align:right">
							<label><strong>'.indian_number($total_taxable).'</strong></label>
						</td>';		
						$k=0;			
						foreach($sun_row as $row1){
							$str.='<td style="text-align:right">'.$tax_arr['sundry'][$k].'</td>';
							$k++;
						}

						$i=0;	
						foreach($tax_row as $row){
							$str.='<td style="text-align:right">'.indian_number($tax_arr['total'][$i]).'</td>';
							$i++;
						}

						
				   			
				$str .='<td data-label="Total" style="text-align:right">
							'.indian_number($roundof).'
						</td>
						<td data-label="Total" style="text-align:right">
							<label><strong>'.indian_number($total).'</strong></label>
						</td>
					</tr>
					<tr style="font-size:20px">
						<td class="tfhide" colspan="6" style="text-align:right"> <strong>Total Tax Payable</strong></td>
						<td data-label="Total Tax Payable" style="text-align:right" colspan="'.(2+$tax_col+$sun_col).'">
							<label><strong >'.indian_number($totaltax).'</strong></label>
						</td>
					</tr>			
				  </tbody>				 
				  </table>';
		
			echo $str;
		}
	}
}

/* $query1 = "select led.l_name, btrn.sundry_gst_per as tax , tax.tx_tax_value as tax_value  from tbl_invoice as inv 
	left join tbl_invoicetrn as itrn on itrn.invoice_id=inv.invoice_id
	left join tbl_tax_trn as tax on tax.tx_transaction_id = itrn.trancation_id	and tax.tx_transaction_type = 'tbl_invoicetrn' and tax.tx_status=0
	left join tbl_bill_sundry_transaction as btrn on btrn.sundry_voucher_id=inv.invoice_id and btrn.sundry_voucher_table='tbl_invoice' and btrn.isdelete=0 and btrn.sundry_gst_per!='0.00'
	left join tbl_bill_sundry_transaction as btax on btax.sundry_voucher_id=inv.invoice_id and btax.sundry_voucher_table='tbl_invoice' and btax.isdelete=0 and btax.sundry_gst_per='0.00'
	left join tbl_ledger as led on led.l_id = btax.sundry_ledger_id
	where inv.invoice_status=0 and itrn.trancation_status=0 group by btax.sundry_ledger_id,tax_value ";
	
	
	$query =  'select tax.tx_tax_value,led.l_name from tbl_tax_trn as tax 
	left join tbl_ledger as led on led.l_id = tax.tx_tax_id
	where tx_transaction_type="tbl_invoicetrn" and tx_status!=2 group by tx_tax_id,tx_tax_value order by tx_tax_value';
	
	$query2 = 'SELECT led.l_name FROM `tbl_general_book` as gen left join tbl_ledger as led on led.l_id = gen.ledger_id where gen.table_name="tbl_invoice" and gen.genral_book_status=0 and led.l_group=31 and gen.company_id=4'; */
?>