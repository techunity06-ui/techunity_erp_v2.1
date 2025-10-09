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

if(strtolower($POST['mode']) == "item_so_detail_report")
{
     $s_date=explode(' - ',$POST['date']);
     $_SESSION['start']=$s_date[0];
     $_SESSION['end']=$s_date[1];
    // $cust_id=$POST['cust_id'];
    // $product_id=$POST['product_id'];

    // $pr_row=get_product_detail($dbcon,$product_id);
    
    $html = "";

    $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
    $set_head=mysqli_fetch_assoc($dbcon->query($set));      
    if($set_head){
        $html .= '
        <table  width="100%"   class="display">
        </table>
        <table class="" id="data_list">
        <tbody><tr id="logo" class="logo">
        <td colspan="8" style="text-align:center;">
        <strong>'.$set_head['company_name'].'</strong>
        </td>
        </tr>

        <tr style="border-bottom:0.5px #000 solid;">
        <td colspan="7">
        <strong>[ Item Wise Sales Order Detail ]</strong>
        </td>
        </tr>';
        $whr='';$where='';
    if($POST['product_id']){
        $whr .= ' and sot.product_id='.$POST['product_id'];
    }

    if($POST['country_id']){
        $where .=' and l.countryid='.$POST['country_id'];
    }

    if($POST['state_id']){
        $where .=' and l.stateid='.$POST['state_id'];
    }

    if($POST['city_id']){
        $where .=' and l.cityid='.$POST['city_id'];
    }
    
    $where.="  and so.sales_order_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND so.sales_order_date<='".date('Y-m-d',strtotime($s_date[1]))."'";

    $query =" SELECT pm.product_name,pm.product_alias_name,pm.product_icode,pm.product_id,sot.sales_ordertrn_id FROM `tbl_sales_ordertrn` as sot left join product_mst as pm on pm.product_id=sot.product_id 
        left join tbl_sales_order as so on so.sales_order_id = sot.sales_order_id
        left join tbl_ledger as l on l.l_id = so.cust_id
    where sot.sales_ordertrn_status=0 and so.order_accept_status = 1 and approve_status = 3 ".$whr." ".$where." group by sot.product_id ";

    //echo $query;
    $result=$dbcon->query($query);

    if(mysqli_num_rows($result)>0)
    {
        while($product_list=mysqli_fetch_assoc($result))
        {
            $str = "";
            $str .= '
            <tr></tr>
            <tr style="border-top: solid white 30px;">
           
            <td colspan="11" style="padding-bottom:10px;">
            <strong>Item : '.$product_list['product_name'].'</strong>
            </td>
            
            </tr><tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
            
            <th style="text-align:center" width="3%"> NO </th>
            <th style="text-align:center" width="11%">SO NO </th>
            <th style="text-align:center" width="9%">SO Date</th>
            <th style="text-align:center" width="15%">Company Name</th>
            <th style="text-align:center" width="15%">Item Code</th>
            <th style="text-align:center" width="15%">Item Alias</th>
            <th style="text-align:center" width="8%">UOM</th>
            <th style="text-align:center" width="8%">SO Qty</th>
            <th style="text-align:center" width="8%">Desp. Qty</th>
            <th style="text-align:center" width="15%">Pend. Qty</th>
            <th style="text-align:center" width="15%">Amount</th>
            <!-- <th style="text-align:center" width="15%">Status</th> -->
            <th style="text-align:center" width="15%">Remark</th>
            </tr><tr>';

            $qry1='SELECT l.l_name,sot.sales_ordertrn_id,so.sales_order_no,sot.product_amount,so.sales_order_date,um.unit_name,sot.product_qty,sot.remaning_invoice_qty,so.remark  
                    FROM `tbl_sales_order` as so 
                    left join tbl_sales_ordertrn as sot on so.sales_order_id=sot.sales_order_id 
                    left join tbl_ledger as l on l.l_id=so.cust_id 
                    left JOIN unit_mst as um ON um.unitid=sot.unit_id 
                    where sot.product_id='.$product_list['product_id'].' and sot.sales_ordertrn_status=0 and so.revise_status=0 and sot.short_close_status = 0 AND so.order_accept_status = 1 and so.approve_status = 3 '.$where;
            $result1 = mysqli_query($dbcon, $qry1);
            $rel1 = mysqli_fetch_all($result1,MYSQLI_ASSOC);
            $total_so_qty=0;
            $total_desp_qty=0;
            $total_pend_qty=0;
            $total_amt=0;
            $single_qty_rate=0;
            $pen_amount=0;
            $i=0;
            
            foreach ($rel1 as $re) {
                if($re['remaning_invoice_qty']>0){
                
                $i++;
                if($re['invoice_status'] == 0){
                    $invoice_status='Pending';
                }else{
                    $invoice_status='Completed';
                }
                $remark = $re['remark'];
                $desp_qty = $re['product_qty']-$re['remaning_invoice_qty'];
                
                $total_so_qty = $total_so_qty + $re['product_qty'];
                $total_desp_qty = $total_desp_qty + $desp_qty;
                $total_pend_qty = $total_pend_qty + $re['remaning_invoice_qty'];
                $total_amt = $total_amt + $re['product_amount'];
                $single_qty_rate=$re['product_amount']/$re['product_qty'];
                $pen_amount = $pen_amount + ($single_qty_rate * $re['remaning_invoice_qty']);

                $str.='<tr style="  border: 1px dashed #cccccc;">
                <td style="text-align:center">'.$i.'</td>
                <td style="text-align:center">'.$re['sales_order_no'].'</td>
                <td style="text-align:center">'.date('d-m-Y',strtotime($re['sales_order_date'])).'</td>
                <td style="text-align:center">'.$re['l_name'].'</td>
                <td style="text-align:center">'.$product_list['product_icode'].'</td>
                <td style="text-align:center">'.$product_list['product_alias_name'].'</td>
                <td style="text-align:center">'.$re['unit_name'].'</td>
                <td style="text-align:center">'.$re['product_qty'].'</td>
                <td style="text-align:center">'.$desp_qty.'</td>
                <td style="text-align:center">'.$re['remaning_invoice_qty'].'</td>
                <td style="text-align:center">'.number_format($re['product_amount'],2).'</td>
                <td style="text-align:center">'.$remark.'</td>
                <!-- <td style="text-align:center">'.$invoice_status.'</td> -->
                  
                </tr>';
                }
            }
            $str .='<tr>
                <td colspan="6" style="text-align:center"></td>
                <td  style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:right;"><strong>Total : </strong></td>
                <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.$total_so_qty.'</td>
                <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.$total_desp_qty.'</td>
                <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.$total_pend_qty.'</td>
                <td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">'.number_format($total_amt,2).'</td>
                </tr>';
                
                if($total_pend_qty > 0){
                    $html .= $str;
                }
        }

    }

    $html .= '</tbody>
        </table>';
         
    }
    // else
    // {
    //     $str .='<tr>
    //     <td colspan="7" style="text-align:center">NO DATA FOUND  </td>
    //     </tr>';

    // }
    
    echo $html;
}
?>