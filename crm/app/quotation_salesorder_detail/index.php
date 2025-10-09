<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "quotation_mode")
{
    $s_date=explode(' - ',$POST['date']);
    $_SESSION['start']=$s_date[0];
    $_SESSION['end']=$s_date[1];
    $where='';
    $colspan='';  
    if(!empty($POST['crm_cust'])){
        $where .= " and so.cust_id=".$POST['crm_cust'];
        $colspan = 1;
    }

    if(!empty($POST['product_id'])){
        $where .= " and trn.product_id=".$POST['product_id'];
        $colspan = $colspan+1;
    }
   

    $where.="  and so.quotation_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND so.quotation_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

   $query = "select so.quotation_no, so.quotation_date, cus.cust_name, pro.product_name, trn.product_qty, trn.product_conv_qty, trn.unitid, trn.conv_unit_id, trn.rate_unit, trn.product_rate, unit.unit_name, cunit.unit_name as conv_unit from tbl_quotation as so
    left join tbl_quotation_trn as trn on trn.quotation_id = so.quotation_id
    left join product_mst as pro on pro.product_id=trn.product_id
    left join tbl_customer as cus on cus.cust_id = so.cust_id
    left join unit_mst as unit on unit.unitid = trn.unitid
    left join unit_mst as cunit on cunit.unitid = trn.conv_unit_id
    where so.quotation_status = 0 and trn.quot_trn_status=0".$where;
    
    $result = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($result);
    $product_detail = get_product_detail($dbcon,$POST['product_id']);
    $custLedgerDetails = get_party_detail($dbcon,$POST['crm_cust']);
    $str = '';
    $colspan = 7-$colspan;
    $str .= '<table class="table table-bordered table-striped " id="data_list">
            <thead> 
                <tr>
                    <th colspan="'.$colspan.'">';
                    if(!empty($POST['crm_cust'])){
                        $str.=" <strong>Customer Name : ".$custLedgerDetails['cust_name']."</strong><br><br>";
                    }

                    if(!empty($POST['product_id'])){
                        $str.=" <strong>Product Name : ".$product_detail['product_name']."</strong>";
                    }                    
                    $str.='</th>
                </tr>

                <tr>
                    <th>Sr.no.</th>
                    <th>Quotation No</th>
                    <th>Quotation Date</th>';
                    if(empty($POST['crm_cust'])){
                        $str.='<th>Party</th>';
                    }

                    if(empty($POST['product_id'])){
                        $str.='<th>Item Name</th>';
                    }

                    $str.='<th>Qty</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>';
    if($cnt>0){
        $i=1;
        while($row = brp_mysqli_fetch_array($result)){   
            
            if($row['rate_unit']==$row['unit_id']){
                $qty = $row['product_qty']." ".$row['unit_name'];
            }else{
                $qty = $row['product_conv_qty']." ".$row['conv_unit']."<br>".$row['product_qty']." ".$row['unit_name'];
            }

            $str.='<tr>
                <td>'.$i.'</td>
                <td>'.$row['quotation_no'].'</td>
                <td>'.date('d-m-Y',strtotime($row['quotation_date'])).'</td>';
                if(empty($POST['crm_cust'])){
                    $str.='<td>'.$row['cust_name'].'</td>';
                }

                if(empty($POST['product_id'])){ 
                    $str.='<td>'.$row['product_name'].'</td>';
                }
                
                $str.='<td>'.$qty.'</td>
                <td>'.$row['product_rate'].'</td>
            </tr>';
            $i++;
        }
    }
    
    $str .='</tbody>
    </table>';

    echo $str;
}
else if(strtolower($POST['mode']) == "salesorder_mode")
{
    $s_date=explode(' - ',$POST['date']);
    $_SESSION['start']=$s_date[0];
    $_SESSION['end']=$s_date[1];
    $where='';
    $colspan='';  
    if(!empty($POST['ledger_id'])){
        $where .= " and so.cust_id=".$POST['ledger_id'];
        $colspan = 1;
    }

    if(!empty($POST['product_id'])){
        $where .= " and trn.product_id=".$POST['product_id'];
        $colspan = $colspan+1;
    }
   

    $where.="  and so.sales_order_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND so.sales_order_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";

   $query = "select so.sales_order_no, so.sales_order_date, led.l_name, pro.product_name, trn.product_qty, trn.product_conv_qty, trn.unit_id, trn.conv_unit_id, trn.rate_unit, trn.product_rate, unit.unit_name, cunit.unit_name as conv_unit from tbl_sales_order as so
    left join tbl_sales_ordertrn as trn on trn.sales_order_id = so.sales_order_id
    left join product_mst as pro on pro.product_id=trn.product_id
    left join tbl_ledger as led on led.l_id = so.cust_id
    left join unit_mst as unit on unit.unitid = trn.unit_id
    left join unit_mst as cunit on cunit.unitid = trn.conv_unit_id
    where so.sales_order_status = 0 and trn.sales_ordertrn_status=0".$where;
    
    $result = $dbcon->query($query);
    $cnt = brp_mysqli_num_rows($result);
    $product_detail = get_product_detail($dbcon,$POST['product_id']);
    $custLedgerDetails = get_cust_data_arr($dbcon,$POST['ledger_id']);
    $str = '';
    $colspan = 7-$colspan;
    $str .= '<table class="table table-bordered table-striped " id="data_list">
            <thead> 
                <tr>
                    <th colspan="'.$colspan.'">';
                    if(!empty($POST['ledger_id'])){
                        $str.=" <strong>Customer Name : ".$custLedgerDetails['l_name']."</strong><br><br>";
                    }

                    if(!empty($POST['product_id'])){
                        $str.=" <strong>Product Name : ".$product_detail['product_name']."</strong>";
                    }                    
                    $str.='</th>
                </tr>

                <tr>
                    <th>Sr.no.</th>
                    <th>Sales Order No</th>
                    <th>Sales Order Date</th>';
                    if(empty($POST['ledger_id'])){
                        $str.='<th>Party</th>';
                    }

                    if(empty($POST['product_id'])){
                        $str.='<th>Item Name</th>';
                    }

                    $str.='<th>Qty</th>
                    <th>Rate</th>
                </tr>
            </thead>
            <tbody>';
    if($cnt>0){
        $i=1;
        while($row = brp_mysqli_fetch_array($result)){   
            
            if($row['rate_unit']==$row['unit_id']){
                $qty = $row['product_qty']." ".$row['unit_name'];
            }else{
                $qty = $row['product_conv_qty']." ".$row['conv_unit']."<br>".$row['product_qty']." ".$row['unit_name'];
            }

            $str.='<tr>
                <td>'.$i.'</td>
                <td>'.$row['sales_order_no'].'</td>
                <td>'.date('d-m-Y',strtotime($row['sales_order_date'])).'</td>';
                if(empty($POST['ledger_id'])){
                    $str.='<td>'.$row['l_name'].'</td>';
                }

                if(empty($POST['product_id'])){ 
                    $str.='<td>'.$row['product_name'].'</td>';
                }
                
                $str.='<td>'.$qty.'</td>
                <td>'.$row['product_rate'].'</td>
            </tr>';
            $i++;
        }
    }
    
    $str .='</tbody>
    </table>';

    echo $str;
}
?>
