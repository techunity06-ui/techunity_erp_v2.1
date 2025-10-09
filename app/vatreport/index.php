<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
		
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") {
    $s_date=explode(' - ',$POST['date']);
    $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));		
    $where ='';
    
    if(!empty($POST['typeid']))
    {
            $where=' and invoicetype_id='.$POST['typeid'];
    }
    $str='';
    $qry='Select invoice.invoice_id,invoice.invoice_no,invoice.invoice_date,g_total,sum(tax_amount1) as tax_amount1,sum(tax_amount2) as tax_amount2,sum(tax_amount3) as tax_amount3,invtrn.tax_name1,invtrn.tax_name2,invtrn.tax_name3,cust.l_name as company_name,cust.gst_no,sum(product_amount) as taxable_amt 
        from tbl_invoice as invoice 
        left join tbl_invoicetrn as invtrn on invtrn.invoice_id=invoice.invoice_id 
        inner join tbl_ledger as cust on invoice.cust_id=cust.l_id 
        where invoice_status=0 and invtrn.trancation_status=0 and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where.' and invoice.company_id='.$_SESSION['company_id'].' group by invoice.invoice_id order by invoice_no';
    $result=$dbcon->query($qry);
    $query="select tax_id,tax_name from tbl_tax where company_id=".$_SESSION['company_id']." and tax_status!=2 and  find_in_set (tax_id,(SELECT group_concat(tax_id) tax FROM `formula_mst` where  company_id=".$_SESSION['company_id']." and find_in_set(formulaid,(SELECT group_concat(distinct formulaid) as formula FROM `tbl_invoicetrn`))))  order by tax_value";
    $rs_tax=$dbcon->query($query);
    $tax_col=mysqli_num_rows($rs_tax);
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
                        <td colspan="'.(2+$tax_col).'" style="text-align:right">Date
                        <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
                        </td>
                </tr>
                <tr>
                        <th style="text-align:center" width="3%">Sr No </th>
                        <th style="text-align:center" width="5%"> Bill No </th>
                        <th style="text-align:center" width="5%">Bill Date</th>
                        <th style="text-align:center" width="5%">GSTIN</th>
                        <th style="text-align:center" width="15%">Customer Name</th>
                        <th style="text-align:center" width="5%">Taxable Amount</th>';
						
                        $tax_arr=array();
                        while($rel_tax=mysqli_fetch_assoc($rs_tax))
                        {
                              $str .='<th width="3%" style="text-align:center">'.$rel_tax['tax_name'].'</th>';
                              $tax_arr['name'][]=$rel_tax['tax_name'];
                        }
                        $str .='<th style="text-align:center" width="5%">Total Amount</th></tr>';
			$j=1;
                        if(brp_mysqli_num_rows($result)>0)
                        {
                                while($re = brp_mysqli_fetch_assoc($result))
                                {	

                                        if(!empty($re['gst_no'])){
                                                $gstno = $re["gst_no"];
                                        }else{
                                                $gstno = '-';
                                        }
						$str.='<tr>
						  <td data-label="Sr No" style="text-align:center">'.$j.'</td>
					  	  <td data-label="Bill No" style="text-align:center">'.$re["invoice_no"].'</td>
					  	  <td data-label="Bill Date" style="text-align:center">'.date('d-m-Y',strtotime($re["invoice_date"])).'</td>
						  <td data-label="GSTIN" style="text-align:center">'.$gstno.'</td>
					  	  <td data-label="Customer Name" style="text-align:left">'.$re['company_name'].'</td>
					  	  <td data-label="Taxable Amount" style="text-align:right">'.indian_number($re["taxable_amt"]).'</td>';
						$cnt=1;							
							
						for($i=0;$i<count($tax_arr['name']);$i++)
						{	
							  $str1="tax_amount".$cnt;
								
								//if($re["tax_name1"]==$tax_arr['name'][$i] || $re["tax_name2"]==$tax_arr['name'][$i] || $re["tax_name3"]==$tax_arr['name'][$i])
								  {
									$tax_amount=get_report_tax_amount($tax_arr['name'][$i],$re["invoice_id"],$dbcon);
									$str.='<td style="text-align:right" data-label="'.$tax_arr['name'][$i].'">'.indian_number($tax_amount).'</td>';
									$cnt++;
									$tax_arr['total'][$i]+=$tax_amount;
									$totaltax+=$tax_amount;
								  }
								/*else{
									$str.='<td style="text-align:center"></td>';
									}*/
									
						  }			
				 		$str .='
						 <td data-label="Total Amount" style="text-align:right">'.indian_number($re["g_total"]).'</td>
						</tr>';				
						$j++;
						$total=$total+$re["g_total"];
						$total_taxable=$total_taxable+$re["taxable_amt"];
					}
				}
				else
				{
					$str .='<tr>
							<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				}
			$str .='<tr>
						 <td class="tfhide" colspan="5" style="text-align:right"> <strong>Total</strong></td>
						 <td data-label="Total" style="text-align:right">
							<label><strong>'.indian_number($total_taxable).'</strong></label>
						</td>';						
				for($i=0;$i<count($tax_arr['name']);$i++)
					{
						
						$str.='<td  data-label="Total"style="text-align:right">
							 <label><strong>'.indian_number($tax_arr['total'][$i]).'</strong></label></td>';
					}		
				   			
			$str	.='<td data-label="Total" style="text-align:right">
							<label><strong>'.indian_number($total).'</strong></label>
						</td></tr>
						<tr style="font-size:20px">
						 <td class="tfhide" colspan="6" style="text-align:right"> <strong>Total Tax Payable</strong></td>
						 <td data-label="Total Tax Payable" style="text-align:right" colspan="'.(1+$tax_col).'">
							<label><strong >'.indian_number($totaltax).'</strong></label>
						</td>
					</tr>			
				  </tbody>				 
				  </table>';
		
				  
			echo $str;
		}
		
function get_report_tax_amount($tax_name,$invoiceid,$dbcon)
{
	$query="select sum(amt ) as tax_amount from ((SELECT invoice_id,sum(tax_amount1) as amt FROM `tbl_invoicetrn` where tax_name1 like '%".$tax_name."%' and trancation_status=0 and invoice_id=".$invoiceid.")  union all
	(SELECT invoice_id,sum(tax_amount2) as amt  FROM `tbl_invoicetrn`  where  tax_name2 like '%".$tax_name."%' and trancation_status=0 and invoice_id=".$invoiceid.")   union All
	(SELECT invoice_id,sum(tax_amount3) as amt  FROM `tbl_invoicetrn`  where tax_name3 like '%".$tax_name."%' and trancation_status=0 and invoice_id=".$invoiceid.") ) as a ";
	$rel= brp_mysqli_fetch_assoc($dbcon->query($query));
	return $rel['tax_amount'];
}
?>