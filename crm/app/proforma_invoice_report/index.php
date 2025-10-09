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
    $where.="  and so.invoice_date >= '".date('Y-m-d',strtotime($s_date[0]))."' AND so.invoice_date <= '".date('Y-m-d',strtotime($s_date[1]))."'";
    
    $where .= ' and so.performa_invoice_type='.$POST['proforma_type'];
    if($POST['proforma_type']==1){
        $left  = "left join tbl_quotation as quot on quot.quotation_id = so.quotation_id
        left join tbl_inquiry as inq on inq.inquiry_id = quot.inquiry_id
        left join tbl_customer as cus on cus.cust_id = so.cust_id
        left join tbl_cust_address as cadd on cadd.cust_id = cus.cust_id and c_addr_defult=1
        left join city_mst as city on city.cityid  = cadd.c_add_city
        left join state_mst as state on state.stateid = cadd.c_add_state";
        $field = ", inq.inquiry_no, cus.cust_name, city.city_name, state.state_name, cadd.c_add_address,so.cust_id";
    }else{
        $left  = "left join tbl_ledger as led on led.l_id=so.cust_id
        left join tbl_customer as cus on cus.cust_id = led.cust_id
        left join city_mst as city on city.cityid  = led.cityid
        left join state_mst as state on state.stateid = led.stateid";
        $field = ", led.l_name, led.cust_cont_name, led.cust_mobile, led.common_email_id, city.city_name, state.state_name, led.m_address";
    }

    $query =" SELECT so.invoice_no,so.invoice_date,so.cdate, user.user_name as owner,mcd.mcd_name as customer_type, pro.product_name, trn.product_amount ".$field." FROM `tbl_proforma_invoice` as so 
    left join tbl_proforma_trn as trn on trn.invoice_id=so.invoice_id
    left join product_mst as pro on pro.product_id = trn.product_id
    ".$left."
    left join users as user on user.user_id = so.user_id 
    left join tbl_master_category_detail as mcd on mcd.mcd_id = cus.cust_type
    WHERE so.invoice_status = 0 AND so.company_id = ".$_SESSION['company_id']." ".$where;
    $result=$dbcon->query($query);
    
    $str .= '<table class="table table-bordered table-striped " id="data_list">
        <thead> 
            <tr>
                <th style="text-align:center;white-space:nowrap" width="3%">Sr. No </th>
                <th style="text-align:center;white-space:nowrap" width="11%">Customer Type</th>
                <th style="text-align:center;white-space:nowrap" width="9%">Created Date</th>
                <th style="text-align:center;white-space:nowrap" width="20%">Owner</th>
                <th style="text-align:center;white-space:nowrap" width="8%">PI No</th>
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
                if($POST['proforma_type']==1){
                    $cust_name  = $row['cust_name'];
                    $cust_add   = $row['c_add_address']; 

                    $contac = "select c_con_fname, c_con_lname, c_con_email, c_con_mobile from tbl_cust_contact as con where c_con_status=0 and cust_id=".$row['cust_id']." limit 1";
                    $result_con = $dbcon->query($contac);
                    $row_con = brp_mysqli_fetch_array($result_con);

                    $con_name   = $row_con['c_con_fname'].' '.$row_con['c_con_lname'];
                    $con_phn    = $row_con['c_con_mobile'];
                    $con_mail   = strtolower($row_con['c_con_email']);
                }else{
                    $cust_name  = $row['l_name'];
                    $cust_add   = $row['m_address'];
                    $con_name   = $row['cust_cont_name'];
                    $con_phn    = $row['cust_mobile'];
                    $con_mail   = strtolower($row['common_email_id']);
                }
                $str .= '<tr>
                    <td>'.$i.'</td>
                    <td>'.$row['customer_type'].'</td>
                    <td>'.date('d-m-Y',strtotime($row['cdate'])).'</td>
                    <td>'.$row['owner'].'</td>
                    <td>'.$row['invoice_no'].'</td>
                    <td>'.$cust_name.'</td>
                    <td>'.$cust_add.'</td>
                    <td>'.$row['city_name'].'</td>
                    <td>'.$row['state_name'].'</td>
                    <td>'.$con_name.'</td>
                    <td>'.$con_phn.'</td>
                    <td>'.$con_mail.'</td>
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