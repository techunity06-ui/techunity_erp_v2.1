<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(isset($POST['mode']) && strtolower($POST['mode']) == "cust_so_detail_report")
{
    $s_date=explode(' - ',$POST['date']);
    $_SESSION['start']=$s_date[0];
    $_SESSION['end']=$s_date[1];
    $cust_id=$POST['cust_id'];
    $str = $where = $whr = '';
    $where.="  and DATE(so.cdate) >= '".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(so.cdate) <= '".date('Y-m-d',strtotime($s_date[1]))."'";
    $whr.="  and DATE(so.cdate) >= '".date('Y-m-d',strtotime($s_date[0]))."' AND DATE(so.cdate) <= '".date('Y-m-d',strtotime($s_date[1]))."'";
    if(!empty($cust_id)){
        $where.=" AND so.cust_id = ".$POST['cust_id'];
    }

    $query = "SELECT l_name,so.cust_id, user.user_name FROM `tbl_sales_order` as so 
                left join tbl_ledger as l on so.cust_id=l.l_id 
                left join users as user on so.user_id = so.user_id 
                WHERE so.sales_order_status = 0 AND so.short_close_status = 0 AND so.order_accept_status = 1 and so.approve_status = 3 AND so.company_id = ".$_SESSION['company_id']."".$where." group by so.cust_id ";
    $result=$dbcon->query($query);
    while($row = mysqli_fetch_assoc($result)){
        $str1 = "";
        $str1 .= '<table class="table table-bordered table-striped " id="data_list">
        <thead>
        <tr>
        <th></th>
        <th colspan="5">
        <strong>Customer : '.$row['l_name'].'</strong>
        </th>
        <th colspan="4">
        <strong>Username : '.$row['user_name'].'</strong>
        </th>
        </tr>
        <tr>
        <th style="text-align:center" width="3%"> NO </th>
        <th style="text-align:center" width="10%">SO NO </th>
        <th style="text-align:center" width="8%">SO Date</th>
        <th style="text-align:center" width="10%">PO NO </th>
        <th style="text-align:center" width="15%">Item Description</th>
        <th style="text-align:center" width="20%">Remark</th>
        <th style="text-align:center" width="8%">UOM</th>
        <th style="text-align:center" width="8%">SO Qty</th>
        <th style="text-align:center" width="8%">Desp. Qty</th>
        <th style="text-align:center" width="8%">Pend. Qty</th>
        <th style="text-align:center" width="15%">Amount <br> Pending Amount</th>
        
        </tr>
        </thead>
        <tbody>
        <tr>';
        $qry1='SELECT pm.product_name,pm.product_icode,pm.product_alias_name,sot.sales_ordertrn_id,so.sales_order_no,sot.description,sot.product_spec,so.po_no,sot.product_amount,so.sales_order_date,um.unit_name,sot.product_qty,sot.remaning_invoice_qty, l.l_name, so.invoice_status, so.remark FROM `tbl_sales_order` as so 
        left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id 
        left join product_mst as pm on pm.product_id=sot.product_id
        left JOIN unit_mst as um ON um.unitid=sot.unit_id
        left JOIN tbl_ledger as l ON l.l_id=so.cust_id
        where so.sales_order_status = 0 AND sot.short_close_status = 0 AND sot.sales_ordertrn_status = 0 AND so.short_close_status = 0 AND so.order_accept_status = 1 and so.approve_status = 3 AND so.company_id = '.$_SESSION['company_id'].' AND so.cust_id = '.$row['cust_id'].''.$whr;
        $result1 = $dbcon->query($qry1);
        $total_so_qty=0;
        $total_desp_qty=0;
        $total_pend_qty=0;
        $total_amt=0;
        $single_qty_rate=0;
        $pen_amount=0;
        $i=1;

        while($re = mysqli_fetch_assoc($result1)){

            /*if($re['invoice_status'] == 0){
                $invoice_status='Pending';
            }else{
                $invoice_status='Completed';
            }*/
            
            if($re['remaning_invoice_qty'] > 0){
                $desp_qty = $re['product_qty']-$re['remaning_invoice_qty'];
    
                $total_so_qty = $total_so_qty + $re['product_qty'];
                $total_desp_qty = $total_desp_qty + $desp_qty;
                $total_pend_qty = $total_pend_qty + $re['remaning_invoice_qty'];
                $total_amt = $total_amt + $re['product_amount'];
                $single_qty_rate=$re['product_amount']/$re['product_qty'];
                $pen_amount = $pen_amount + ($single_qty_rate * $re['remaning_invoice_qty']);
    
                $str1.='<tr>
                <td style="text-align:center">'.$i.'</td>
                <td style="text-align:center">'.$re['sales_order_no'].'</td>
                <td style="text-align:center">'.date('d-m-Y',strtotime($re['sales_order_date'])).'</td>
                <td style="text-align:center">'.$re['po_no'].'</td>
                <td style="text-align:center">'.$re['product_name'] .' -- ' . $re['product_alias_name'] . ' -- ' . $re['product_icode'] .'</td>
                <td style="text-align:center">'.$re['product_spec'].'</td>
                <td style="text-align:center">'.$re['unit_name'].'</td>
                <td style="text-align:center">'.$re['product_qty'].'</td>
                <td style="text-align:center">'.$desp_qty.'</td>
                <td style="text-align:center">'.$re['remaning_invoice_qty'].'</td>
                <td style="text-align:center">'.number_format($re['product_amount'],2).'<br>'.number_format($single_qty_rate * $re['remaning_invoice_qty'],2).'</td>
                
                </tr>';
                $i++;
            }
        }
        $str1 .='</tbody>
        <tfooter>
        <td colspan="6" style="text-align:right;"></td>
        <td  style="text-align:right;"><strong>Total : </strong></td>
        <td style="text-align:center;"><strong>'.$total_so_qty.'</strong></td>
        <td style="text-align:center;"><strong>'.$total_desp_qty.'</strong></td>
        <td style="text-align:center;"><strong>'.$total_pend_qty.'</strong></td>
        <td style="text-align:center;"><strong>'.number_format($total_amt,2).'<br> '.number_format($pen_amount,2).'</strong></td>
        </tr>
        </tfooter>
        </table>';
        if ($total_so_qty > 0) {
            $str .= $str1;
        }
    }
    echo $str;
}
?>