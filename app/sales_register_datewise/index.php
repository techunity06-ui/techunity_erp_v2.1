<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") {
			$where = '';
                        $s_date=explode(' - ',$POST['date']);
			$start_date = $s_date[0];
			$end_date = $s_date[1];
			$str = '';
			$set = "SELECT * FROM `tbl_company` where company_id=".$_SESSION['company_id'];
			$set_head = mysqli_fetch_assoc($dbcon->query($set));	
			if($POST['cust_id']){
				$query1="select company_name from tbl_customer  where  cust_id=".$POST['cust_id'];
				$rel1=mysqli_fetch_assoc($dbcon->query($query1));
				$where=' and inv.cust_id='.$POST["cust_id"];
			}	
		 	 
			 
				$str .='<div id="payment_detail">
				<table class="display table table-striped table-bordered" id="data_list">
				  <thead class="resdisplay"> 
					<tr>
						<td class="noborder" colspan="9" style="border:none;text-align: center;">
							<span id="head_logo"><strong style="">'.$set_head['company_name'].'</strong></span>
						</td>
					</tr>
					<tr>
						<td class="noborder" colspan="2" style="border:none"><strong>Sales Register (Datewise)</strong></td>
						<td class="noborder" style="border:none"><!--Customer Name: <strong>'.$rel1['company_name'].'</strong>--></td>
						<td class="noborder" colspan="4" style="text-align:right;border-top:none; border-left:none;border-bottom:none;"> 
						Date <label> : <strong>'.$start_date.'</strong> To <strong>'.$end_date.'</strong></label>
					</td>
				
					</tr>
					
					<tr>
						<th width="15%"  style="text-align:center">Date</th>
						<th width="10%" style="text-align:left">Bill NO.</th>
						<th width="30%" style="text-align:left">A/C Name</th>
						<th width="15%" style="text-align:center">GSTIN No.</th>
						<th width="10%" style="text-align:right;">Values of Goods</th>
                                                <th width="10%" style="text-align:right;">Tax Amount</th>
                                                <th width="10%" style="text-align:right;">Bill Amt.</th>
					</tr>
				 
				 </thead>
				 <tbody>';
				$qry = 'SELECT inv.invoice_id,invoice_no,invoice_date, company_name,gst_no,trn.product_amount as subtotal,(trn.tax_amount1 + trn.tax_amount2 + trn.tax_amount3) as tax_amount, trn.total as g_total,MONTHNAME(inv.invoice_date) as month  
                                    FROM tbl_invoice as inv 
                                    inner join tbl_customer as cust on cust.cust_id=inv.cust_id
                                    left join tbl_invoicetrn as trn on trn.invoice_id = inv.invoice_id
                                where inv.invoice_status!=2 AND inv.invoice_date between "'.date('Y-m-d', strtotime($start_date)).'" and "'.date('Y-m-d', strtotime($end_date)).'" and inv.company_id='.$_SESSION['company_id'].' '.$where.' 
                                order by invoice_date';
                                $result = mysqli_query($dbcon,$qry);
                                $invoices = mysqli_fetch_all($result,MYSQLI_ASSOC);
                                
                                if($invoices)
				{
                                        $grand_sub_total = $grand_tax_total = $grand_total = 0; 
                                        $monthly_sub_total  = $monthly_tax_total = $monthly_total = 0; 
                                        $arr=array();
					foreach ($invoices as $i => $re) {
                                                $pre_month = $invoices[$i-1]['month'];
                                                $next_month = $invoices[$i+1]['month'];
                                                $month_name = $re["month"];
                                                if($month_name !== $pre_month){
                                                    $str.='<tr>
                                                        <td class="noborder" colspan="7" style="border:none"><strong>'.$month_name.'</strong></td>
                                                    </tr>';
                                                }
						if(!empty($re["gst_no"])){
							$gstno = $re["gst_no"];
						}else{
							$gstno = '-';
						}
						$str.='<tr>
                                                        <td data-label="Date" style="text-align:left" class="noborder">'.date("d/m/y",strtotime($re["invoice_date"])).'</td>
                                                        <td data-label="Bill NO." style="text-align:left" class="noborder">'.$re["invoice_no"].'</td>
                                                        <td data-label="Customer Name" style="text-align:" class="noborder">'.$re["company_name"].'</td>
                                                        <td data-label="GST No. " style="text-align:center" class="noborder">'.$gstno.'</td>
                                                        <td data-label="Total" style="text-align:right" class="noborder">'.$re["subtotal"].'</td>
                                                        <td data-label="Total" style="text-align:right" class="noborder">'.$re["tax_amount"].'</td>
                                                        <td data-label="Total" style="text-align:right" class="noborder">'.indian_number($re["g_total"],2).'</td>
                                                    </tr>';
                                                
                                                    $monthly_sub_total = $monthly_sub_total + $re['subtotal'];
                                                    $monthly_tax_total = $monthly_tax_total + $re['tax_amount'];
                                                    $monthly_total = $monthly_total + $re['g_total'];
                                                    
                                                    if($month_name !== $next_month){
                                                        $str.='<tr>
                                                            <td style="text-align:right" colspan="4"><b>Total</b></td>
                                                            <td style="text-align:right"><strong>'.indian_number($monthly_sub_total,2).'</strong></td>
                                                            <td style="text-align:right"><strong>'.indian_number($monthly_tax_total,2).'</strong></td>
                                                            <td style="text-align:right"><strong>'.indian_number($monthly_total,2).'</strong></td>
                                                        </tr>';
                                                        $monthly_sub_total = 0; $monthly_tax_total = 0; $monthly_total = 0;
                                                    }
                                                    
                                                    $grand_sub_total = $grand_sub_total + $re['subtotal'];
                                                    $grand_tax_total = $grand_tax_total + $re['tax_amount'];
                                                    $grand_total = $grand_total + $re['g_total'];
					}
					$str.='<tfoot><tr>
                                                    <td style="text-align:right" colspan="4"><b>Grand Total</b></td>
                                                    <td style="text-align:right"><strong>'.indian_number($grand_sub_total,2).'</strong></td>
                                                    <td style="text-align:right"><strong>'.indian_number($grand_tax_total,2).'</strong></td>
                                                    <td style="text-align:right"><strong>'.indian_number($grand_total,2).'</strong></td>
                                                </tr></tfoot>';
				}
				else
				{
					$str .='<tr>
							<td colspan="9" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='</tbody>				 
				  </table>
				  </div>';
				  
                        $str .= '<br/><br/>';
                        
                        
                        // Sales tax summary start
                        $str .='<div id="payment_detail">
				<table class="display table table-striped table-bordered" id="data_list">
				  <thead class="resdisplay"> 
					<tr>
						<td class="noborder" colspan="9" style="border:none;text-align: center;">
                                                    <span id="head_logo"><strong style="">Sale Tax Summary</strong></span>
						</td>
					</tr>
					<tr>
						<th width="15%"  style="text-align:center"></th>
						<th width="10%" style="text-align:left">Basic Amount</th>
						<th width="30%" style="text-align:left">Taxable</th>
						<th width="15%" style="text-align:center">Non-Taxable</th>
						<th width="10%" style="text-align:right;">Total Tax</th>
                                                <th width="10%" style="text-align:right;">Add. Tax</th>
                                                <th width="10%" style="text-align:right;">Bill Amt.</th>
					</tr>
				 
				 </thead>
				 <tbody>';
                        $tax_summary_qry = 'SELECT formula.tax_id,tax.tax_value,concat(tax_name1 ,tax_name2, tax_name3) as tax_name, MONTHNAME(inv.invoice_date) as month 
                                    FROM tbl_invoice as inv 
                                left join tbl_invoicetrn as trn on trn.invoice_id = inv.invoice_id 
                                left join formula_mst as formula on formula.formulaid = trn.formulaid 
                                inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id)
                                where inv.invoice_status!=2 and inv.company_id='.$_SESSION['company_id'].'
                                        AND inv.invoice_date between "'.date('Y-m-d', strtotime($start_date)).'" and "'.date('Y-m-d', strtotime($end_date)).'" 
                                group by tax_id order by tax_name'; 
                        $result = mysqli_query($dbcon,$tax_summary_qry);
                        $tax_summary = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        foreach($tax_summary as $summary){
                            
                                $str.='<tr>
                                            <td class="noborder" colspan="7" style="border:none"><strong>'.$summary['tax_value'].'% GST</strong></td>
                                        </tr>';
                                $tax_qry = 'SELECT formula.tax_id,sum(trn.product_amount) as subtotal,(sum(trn.tax_amount1) + sum(trn.tax_amount2) + sum(trn.tax_amount3)) as tax_amount, sum(trn.total) as g_total,MONTHNAME(inv.invoice_date) as month,concat(tax_name1 ,tax_name2, tax_name3) as tax_name
                                    FROM tbl_invoice as inv 
                                    left join tbl_invoicetrn as trn on trn.invoice_id = inv.invoice_id
                                    left join formula_mst as formula on formula.formulaid = trn.formulaid
                                    inner join tbl_tax as tax on find_in_set(tax.tax_id,formula.tax_id) 
                                    where inv.invoice_status!=2 and tax.tax_id in ('.$summary['tax_id'].') and inv.company_id='.$_SESSION['company_id'].'
                                        AND inv.invoice_date between "'.date('Y-m-d', strtotime($start_date)).'" and "'.date('Y-m-d', strtotime($end_date)).'" '.$where.' 
                                    group by month 
                                    order by tax_name';
                                $result = mysqli_query($dbcon,$tax_qry);
                                $tax_invoices = mysqli_fetch_all($result,MYSQLI_ASSOC);
                                
                                if($tax_invoices)
				{
                                        $grand_basic = $grand_taxable = $grand_bill = 0; 
                                        $basic_total  = $taxable_total = $tax_total = 0; 
                                        $arr=array();
					foreach ($tax_invoices as $i => $tax) {
                                            //$pre_tax = $tax_invoices[$i-1]['tax_name'];
                                            //$tax_name = $tax['tax_name'];
                                            //$next_tax = $tax_invoices[$i+1]['tax_name'];
                                            
                                            
                                                
                                            $str.='<tr>
                                                    <td data-label="Month" style="text-align:left" class="noborder">'.$tax['month'].'</td>
                                                    <td data-label="Basic Amount" style="text-align:right" class="noborder">'.indian_number($tax['subtotal'],2).'</td>
                                                    <td data-label="Taxable" style="text-align:right" class="noborder">'.indian_number($tax['subtotal'],2).'</td>
                                                    <td data-label="Non taxable" style="text-align:right" class="noborder">0.00</td>
                                                    <td data-label="Total Tax" style="text-align:right" class="noborder">'.indian_number($tax['tax_amount'],2).'</td>
                                                    <td data-label="Add. Tax" style="text-align:right" class="noborder">-</td>
                                                    <td data-label="Bill Amt." style="text-align:right" class="noborder">'.indian_number($tax["g_total"],2).'</td>
                                                </tr>';
                                            
                                                    $basic_total = $basic_total + $tax['subtotal'];
                                                    $taxable_total = $taxable_total + $tax['tax_amount'];
                                                    $bill_total = $bill_total + $tax['g_total'];
                                                    
                                                    $grand_basic += $tax['subtotal'];
                                                    $grand_taxable += $tax['tax_amount'];
                                                    $grand_bill += $tax['g_total'];
                                                    }
                                                    
                                                        $str.='<tr>
                                                                <td data-label="Month" style="text-align:right" class="noborder"><b>Total</b></td>
                                                                <td data-label="Basic Amount" style="text-align:right" class="noborder"><b>'.indian_number($basic_total,2).'</b></td>
                                                                <td data-label="Taxable" style="text-align:right" class="noborder"><b>'.indian_number($basic_total,2).'</b></td>
                                                                <td data-label="Non taxable" style="text-align:center" class="noborder"></td>
                                                                <td data-label="Total Tax" style="text-align:right" class="noborder"><b>'.indian_number($taxable_total,2).'</b></td>
                                                                <td data-label="Add. Tax" style="text-align:right" class="noborder"></td>
                                                                <td data-label="Bill Amt." style="text-align:right" class="noborder"><b>'.indian_number($bill_total,2).'</b></td>
                                                            </tr>';
                                                        $basic_total = 0; $taxable_total = 0; $bill_total = 0;
                                                    
                                        
                                }
                        }
                                
                                
                                        $str .= '</table></div>';
                                        // Sales tax summary end
                                        
                                        // Grand Total summary start
                                        $str .= '<table style="width:50% !important">
                                                <tr>
                                                    <td colspan="2"><strong>Grand Total</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align:right"><strong>BASIC :</strong></td>
                                                    <td style="text-align:left"><strong>'.indian_number($grand_basic,2).'</strong></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td style="text-align:right"><strong>TAXABLE  :</strong></td>
                                                    <td style="text-align:left"><strong>'.indian_number($grand_taxable,2).'</strong></td>
                                                </tr>';
                                                
                                        $tax_qry = 'SELECT distinct(tax_name) FROM `tbl_tax` as tax 
                                                inner join formula_mst as formula on find_in_set(tax.tax_id,formula.tax_id)
                                                WHERE tax_status = 0 and formula.tax_type = 0 and tax.company_id='.$_SESSION['company_id'];
                                        $result = mysqli_query($dbcon,$tax_qry);
                                        $taxes = mysqli_fetch_all($result,MYSQLI_ASSOC);
                                        
                                        foreach($taxes as $tax){
                                            $tax_value_qry = "SELECT 
                                                sum(case when tax_name1 like '".$tax['tax_name']."' then tax_amount1 else 0 end) 'total_tax1', 
                                                sum(case when tax_name2 like '".$tax['tax_name']."' then tax_amount2 else 0 end) 'total_tax2', 
                                                sum(case when tax_name3 like '".$tax['tax_name']."' then tax_amount3 else 0 end) 'total_tax3'
                                                FROM tbl_invoice as inv 
                                                left join tbl_invoicetrn as trn on trn.invoice_id = inv.invoice_id
                                                where inv.invoice_status!=2 and inv.company_id=".$_SESSION['company_id']."
                                                    AND inv.invoice_date between '".date("Y-m-d", strtotime($start_date))."' and '".date("Y-m-d", strtotime($end_date))."'
                                                order by inv.invoice_id"; 
                                            $result = mysqli_query($dbcon,$tax_value_qry);
                                            $tax_values = mysqli_fetch_array($result,MYSQLI_ASSOC);
                                            extract($tax_values);
                                            $total = $total_tax1 + $total_tax2 + $total_tax3;
                                            $str .= '<tr>
                                                    <td style="text-align:right"><strong>'.$tax['tax_name'].': </strong></td>
                                                    <td style="text-align:left"><strong>'.indian_number($total,2).'</strong></td>
                                                </tr>';
                                            
                                        }
                                        $str .= '<tr>
                                                    <td style="text-align:right"><strong>Bill Amount: </strong></td>
                                                    <td style="text-align:left"><strong>'.indian_number($grand_bill,2).'</strong></td>
                                                </tr>';
                                        $str .= '</table>';
                                        // Grand Total summary end
                        echo $str;
                        
                        
		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
?>