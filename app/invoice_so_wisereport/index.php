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
    $td=5;$td1=10;
    if(!empty($POST['cust_id']))
    {
            $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$POST['cust_id'])
                    ->fetch_object()->l_name;
            $td=4;$td1=10;
    }	
    $str='';
    $set="select * from tbl_setting";
    $set_head= brp_mysqli_fetch_assoc($dbcon->query($set));		
    $str .='<table  width="100%" class="display table table-bordered table-striped">
                    <tr id="logo" style="display:none">
                            <td colspan="8" style="text-align:center;">
                                    <strong>'.$set_head['title'].'</strong>
                            </td>
                    </tr>
            </table>

            <table  class="display table table-bordered table-striped" id="data_list">
                <tr>
                        <td colspan="3"><strong>Invoice Report</strong>
                        </td>
                        <td colspan="5" style="text-align:center">';
                        if(!empty($POST['cust_id']))
                        {
                        $str .='Name: <strong>'.$rel_cust['company_name'].'</strong>';
                        }
                        $str .='</td><td colspan="'.$td.'" style="text-align:right">Date
                        <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> From <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
                </tr>
                <tr>
                          <th width="2%" style="text-align:center">Sr. NO.</th>
                          <th width="15%" style="text-align:center">Invoice Type</th>
                          <th width="15%" style="text-align:center">Invoice No</th>
                          <th width="15%" style="text-align:center">Invoice Date</th>
                          <th width="15%" style="text-align:center">Sales Order No</th>
                          <th width="15%" style="text-align:center">Sales Order Date</th>
                          <th width="15%" style="text-align:center">PO No</th>
                          <th width="15%" style="text-align:center">PO Date</th>
                          <th width="15%" style="text-align:center">Payment Terms</th>

                          ';
                          if(empty($POST['cust_id']))
                          {
                                $str.='<th width="25%" style="text-align:center">Company Name</th>';
                          }
                          $str .='<th width="15%" style="text-align:center">Total Amount</th>
                          <th width="15%" style="text-align:center">Paid Amount</th>	 
                          <th width="15%" style="text-align:center">Due Amount</th>	 
                 </tr>
            <tbody>';
            $where ='';
            if(!empty($POST['type_id']))
            {
                    $where .=" and invoice.invoicetype_id=".$POST['type_id'];
            }
            if(!empty($POST['cust_id']))
            {
                    $where .=" and invoice.cust_id=".$POST['cust_id'];

            }
            
            $qry='Select invoice_no, invoice_date, cust.l_name as company_name, invoice.g_total,(select SUM(rtrn.paid_amount) as amuount from tbl_receipt_trn as rtrn where  rtrn.status=0 and rtrn.invoice_id=invoice.invoice_id) as paidamo , invoice.invoicetype_id,invoice_type,GROUP_CONCAT(itrn.sales_ordertrn_id) as so_trn
                from tbl_invoice as invoice 
                inner join tbl_ledger as cust on invoice.cust_id=cust.l_id 
                inner join tbl_invoicetype as type on invoice.invoicetype_id=type.invoicetype_id
                left join tbl_invoicetrn as itrn on itrn.invoice_id=invoice.invoice_id
                where invoice_status=0  and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" AND invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'"'.$where.' and invoice.company_id='.$_SESSION['company_id'].' group by invoice.invoice_id';

            $result1=$dbcon->query($qry);
            $i=1;
            if(brp_mysqli_num_rows($result1)>0){
                while($re = brp_mysqli_fetch_assoc($result1)){	

                     $qryso='Select GROUP_CONCAT(so.sales_order_no) as sono,GROUP_CONCAT(sales_order_date) as sodate,GROUP_CONCAT(po_no) as pono,GROUP_CONCAT(po_date) as po_date,pterms.payment_terms from tbl_sales_ordertrn as strn 
                left join tbl_sales_order as so on so.sales_order_id=strn.sales_order_id 
                left join pay_terms as pterms on pterms.terms_id=so.payment_terms
                where sales_ordertrn_id in ('.$re["so_trn"].')';

            $result_so=$dbcon->query($qryso);
            $re_so = brp_mysqli_fetch_assoc($result_so);


                    $tamount=$re['g_total'];
                    $due =$tamount-$re["paidamo"];
                    $str.='<tr>
                            <td style="text-align:center">'.$i.'</td>';		  	
                    $str.= '<td style="text-align:center">'.$re["invoice_type"].'</td>
                            <td style="text-align:center">'.$re["invoice_no"].'</td>';
                    $str.='<td style="text-align:center">'.date('d/m/Y',strtotime($re["invoice_date"])).'</td>
                    <td style="text-align:center">'.$re_so["sono"].' </td>
                    <td style="text-align:center">'.$re_so["sodate"].'</td>
                    <td style="text-align:center">'.$re_so["pono"].'</td>
                    <td style="text-align:center">'.$re_so["po_date"].'</td>
                    <td style="text-align:center">'.$re_so["payment_terms"].'</td>


                    ';
                    
                    if(empty($POST['cust_id'])){
                            $str.='<td style="text-align:left">'.$re["company_name"].'</td>';
                    }
                    $str .='<td style="text-align:right">'.indian_number($tamount).'</td>
                        <td style="text-align:right">'.indian_number($re["paidamo"]).'</td>	 
                        <td style="text-align:right">'.indian_number($due).'</td>	 
                      </tr>';				
                    $i++;
                    $total=$total+$tamount;
                    $total_paid=$total_paid+$re["paidamo"];
                    $total_due=$total_due+($tamount-$re["paidamo"]);
                }
            } else {
                $str .='<tr>
                        <td colspan="8" style="text-align:center">NO DATA FOUND  </td>
                    </tr>';
							
            }
            $str .='<tr>
                        <td colspan="'.$td1.'" style="text-align:right"> <strong>Total</strong></td>
                        <td style="text-align:right">
                               <label><strong>'.indian_number($total).'</strong></label>
                        </td>						
                        <td style="text-align:right">
                               <label><strong>'.indian_number($total_paid).'</strong></label></td>
                        <td style="text-align:right">
                               <label><strong>'.indian_number($total_due).'</strong></label>
                        </td>	
                    </tr>	
                </tbody>				 
            </table>';
				  
    echo $str;
}
		
    
?>