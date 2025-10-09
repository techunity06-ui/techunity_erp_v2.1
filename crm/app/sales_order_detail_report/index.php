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

if(strtolower($POST['mode']) == "cust_so_detail_report")
{
    $s_date=explode(' - ',$POST['date']);
    $_SESSION['start']=$s_date[0];
    $_SESSION['end']=$s_date[1];
    $cust_id=$POST['cust_id'];
    $str = $where = $whr = '';
    $where.="  and so.sales_order_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND so.sales_order_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
    
    if(!empty($cust_id)){
        $where.=" AND so.cust_id = ".$POST['cust_id'];
    }

    $query =" SELECT l.l_name,so.sales_order_no,so.sales_order_date,so.cdate,inq.inquiry_no, user.user_name as owner,mcd.mcd_name as customer_type,l.m_address, city.city_name, state.state_name, l.cust_cont_name, l.cust_mobile, l.common_email_id, pro.product_name, trn.product_amount FROM `tbl_sales_order` as so 
    left join tbl_sales_ordertrn as trn on trn.sales_order_id=so.sales_order_id
    left join product_mst as pro on pro.product_id = trn.product_id
    left join tbl_quotation_trn as qtr on qtr.quot_trn_id = trn.quot_trn_id
    left join tbl_quotation as quot on quot.quotation_id = qtr.quotation_id
    left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id
    left join tbl_ledger as l on so.cust_id=l.l_id 
    left join tbl_customer as cus on cus.cust_id = l.cust_id 
    left join city_mst as city on city.cityid  = l.cityid
    left join state_mst as state on state.stateid = l.stateid
    left join users as user on so.user_id = so.user_id 
    left join tbl_master_category_detail as mcd on mcd.mcd_id = cus.cust_type
    WHERE so.sales_order_status = 0 and trn.sales_ordertrn_status=0  and so.company_id = ".$_SESSION['company_id']." ".$where;
    $result=$dbcon->query($query);
    
    $str .= '<table class="table table-bordered table-striped " id="data_list">
        <thead> 
            <tr>
                <th style="text-align:center;white-space:nowrap" width="3%">Sr. No </th>
                <th style="text-align:center;white-space:nowrap" width="11%">Customer Type</th>
                <th style="text-align:center;white-space:nowrap" width="9%">Created Date</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Owner</th>
                <th style="text-align:center;white-space:nowrap" width="8%">Inquiry No</th>
                <th style="text-align:center;white-space:nowrap" width="8%">SO No</th>
                <th style="text-align:center;white-space:nowrap" width="8%">Customer Name</th>
                <th style="text-align:center;white-space:nowrap" width="8%">Address</th>
                <th style="text-align:center;white-space:nowrap" width="15%">City</th>
                <th style="text-align:center;white-space:nowrap" width="20%">State</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Contact Person</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Contact No</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Email Id</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Product (Final)</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Value (Basic)</th>
            </tr>
        </thead>
        <tbody>';
        $cnt = brp_mysqli_num_rows($result);
        if($cnt>0){
            $i = 1;
            while($row = mysqli_fetch_assoc($result)){
                $str .= '<tr>
                    <td>'.$i.'</td>
                    <td>'.$row['customer_type'].'</td>
                    <td>'.date('d-m-Y',strtotime($row['cdate'])).'</td>
                    <td>'.$row['owner'].'</td>
                    <td>'.$row['inquiry_no'].'</td>
                    <td>'.$row['sales_order_no'].'</td>
                    <td>'.$row['l_name'].'</td>
                    <td>'.$row['m_address'].'</td>
                    <td>'.$row['city_name'].'</td>
                    <td>'.$row['state_name'].'</td>
                    <td>'.$row['cust_cont_name'].'</td>
                    <td>'.$row['cust_mobile'].'</td>
                    <td>'.strtolower($row['common_email_id']).'</td>
                    <td>'.$row['product_name'].'</td>
                    <td>'.$row['product_amount'].'</td>
                </tr>';
                $i++;
            }
        }else{
            $str .= '<tr>
                <td colspan="17" style="text-align:center">No Data Found...!!!!</td>
            </tr>';
        }

    $str .='</tbody>
    </table>';
    
    echo $str;
}
?>