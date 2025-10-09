<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
		
if(strtolower($POST['mode']) == "generate_report") {
        $where = '';
        $s_date = explode(' - ',$POST['date']);
        $str = '';
        $companyName = $dbcon->query("SELECT company_name FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
            ->fetch_object()->company_name;
        
        if($POST['cust_id']){
                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$POST['cust_id'])
                    ->fetch_object()->l_name;
                $where=' and inv.cust_id='.$POST["cust_id"];
        }	
			 
	$str .='<div id="payment_detail">
                    <table class="display table table-striped table-bordered" id="data_list">
                        <thead class="resdisplay"> 
                            <tr>
                                    <td class="noborder" colspan="9" style="border:none;text-align: center;">
                                            <span id="head_logo"><strong style="">'.$companyName.'</strong></span>
                                    </td>
                            </tr>
                            <tr>
                                    <td class="noborder" colspan="2" style="border:none"><strong>Sales Register</strong></td>
                                    <td class="noborder" style="border:none"><!--Customer Name: <strong>'.$ledger_name.'</strong>--></td>
                                    <td class="noborder" colspan="2" style="text-align:right;border-top:none; border-left:none;border-bottom:none;"> 
                                    Date
                                    <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label>
                                    </td>

                            </tr>
                            <tr>
                                    <th width="15%"  style="text-align:center">Bill NO.</th>
                                    <th width="10%" style="text-align:left">Date</th>
                                    <th width="50%" style="text-align:left">Customer Name</th>
                                    <th width="15%" style="text-align:center">GST No. </th>
                                    <th width="10%" style="text-align:right;">Total </th>
                            </tr>
                        </thead>
                        <tbody>';
			
        $qry='SELECT inv.invoice_id,invoice_no,invoice_date,l_name as company_name,gst_no, g_total 
            FROM tbl_invoice as inv 
            inner join tbl_ledger as cust on cust.l_id = inv.cust_id 
            where inv.invoice_status!=2 AND inv.invoice_date between "'.date('Y-m-d',strtotime($s_date[0])).'" and "'.date('Y-m-d',strtotime($s_date[1])).'" and inv.company_id='.$_SESSION['company_id'].' '.$where.' 
            order by invoice_date DESC';
        $result1=$dbcon->query($qry);
        $i=1;
        if(mysqli_num_rows($result1)>0){
            $total=0;$arr=array();
            while($re=mysqli_fetch_assoc($result1)){
                
                $gstno = (!empty($re["gst_no"])) ? $re["gst_no"] : '-';
                $str.='<tr>
                        <td data-label="Bill NO." style="text-align:left" class="noborder">'.$re["invoice_no"].'</td>
                        <td data-label="Date" style="text-align:left" class="noborder">'.date("d/m/y",strtotime($re["invoice_date"])).'</td>
                        <td data-label="Customer Name" style="text-align:" class="noborder">'.$re["company_name"].'</td>
                        <td data-label="GST No. " style="text-align:center" class="noborder">'.$gstno.'</td>
                        <td data-label="Total" style="text-align:right" class="noborder">'.$re["g_total"].'</td>
                    </tr>';
						
                $str.='<tr>
                        <td colspan="5" style="text-align:left" class="noborder">';
                $str.='<table style="width:100%;border-collapse:collapse;" cellpadding="0" cellspacing="0" class="table-bordered">
                        <thead>
                            <tr>
                                <th style="text-align:center;width:5%;">Sr.</th>
                                <th style="text-align:center;width:30%;">Product Details</th>
                                <th style="text-align:center;width:10%;">HSN Code</th>
                                <th style="text-align:center;width:10%;">Quantity</th>
                                <!--<th style="text-align:center;width:10%;">Sqr/Ft</th>-->
                                <th style="text-align:center;width:10%;">Rate</th>
                                <th style="text-align:center;width:20%;">Tax</th>
                                <th style="text-align:center;width:15%;">Amount</th>
                            </tr>
                        </thead>';
                $trn_qry = "select trn.*,pro.product_name,unit_name 
                                    from tbl_invoicetrn as trn 
                                inner join product_mst as pro on pro.product_id=trn.product_id
                                left join unit_mst as unit on unit.unitid=trn.unit_id
                                where trancation_status=0 and trn.invoice_id=".$re['invoice_id'];
                $trn_quy_rs = $dbcon->query($trn_qry);
                $j=1;
                while($trn_re = brp_mysqli_fetch_assoc($trn_quy_rs))
                {
                    $hsncode = (!empty($trn_re["product_hsn_code"])) ? $trn_re["product_hsn_code"] : '-';
                    
                    $str.='<tr>
                                <td data-label="Sr.No." style="text-align:center;vertical-align:top;">'.$j.'</td>
                                <td  data-label="Product Details" style="text-align:left;vertical-align:top;">'.$trn_re['product_name'].' '.(($trn_re['description'])?'<br/>'.nl2br($trn_re['description']):' ').'</td>
                                <td  data-label="HSN Code" style="text-align:center;vertical-align:top;">'.$hsncode.'</td>
                                <td  data-label="Quantity" style="text-align:center;vertical-align:top;">'.$trn_re['product_qty'].' '.$trn_re['unit_name'].'</td>
                                <!--<td style="text-align:center;vertical-align:top;">'.$trn_re['sqr_ft'].'</td>-->
                                <td  data-label="Rate" style="text-align:center;vertical-align:top;">'.$trn_re['product_rate'].'</td>
                                <td  data-label="Tax" style="text-align:left;vertical-align:top;">';
                    
                    if($trn_re['tax_name1']){
                            $str.= $trn_re['tax_name1'].' : '.$trn_re['tax_amount1'];
                    }else{
                            $str.='-';
                    }
                    if($trn_re['tax_name2']){
                            $str.= '<br/>'.$trn_re['tax_name2'].' : '.$trn_re['tax_amount2'];
                    }else{
                            $str.='-';
                    }
                    $str.='</td>
                            <td  data-label="Amount" style="text-align:center;vertical-align:top;">'.$trn_re['total'].'</td>
                    </tr>';
                    $j++;
                }
                $str.='</table>';
                $str.='</td></tr>';
                $total=$total+$re['g_total']; 
                $i++;
            }
                $str.='<tfoot><tr>
                          <td style="text-align:right" colspan="4"><b>Total</b></td>
                          <td style="text-align:right">'.indian_number($total,2).'</td></tr></tfoot>';
        } else {
                $str .='<tr>
                            <td colspan="9" style="text-align:center">NO DATA FOUND  </td>
                        </tr>';
							
        }
        $str .='</tbody>				 
            </table>
        </div>';
				  
    echo $str;
}
?>