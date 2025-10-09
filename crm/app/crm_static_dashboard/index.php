<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "new_inq_add_load") {
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $crm_user_type = $companyConfiguration['crm_user_type'];
    $_SESSION['start_date'] = $POST['new_inq_start_date'];
    $_SESSION['end_date'] = $POST['new_inq_end_date'];
    $str="";
    $str.='<table class="table">
    <tr> 
    <th width="10%" style="white-space:nowrap;" >Sr. No.</th>
    <th width="70%">Employee Name</th>
    <th width="20%">Nos.</th>
    </tr>';
    $query="select us.user_id,us.user_name,IFNULL(cou,0) as cou from users as us
    left join 
    (select IFNULL(count(inquiry_id),0) as cou,inq.user_id from tbl_inquiry as inq where inquiry_status=0 and inq.company_id=".$_SESSION['company_id']." and DATE(create_date) >= '".date('Y-m-d 00:00:00',strtotime($POST['new_inq_start_date']))."' and DATE(create_date) <= '".date('Y-m-d 23:59:59',strtotime($POST['new_inq_end_date']))."' AND inq.opp_id NOT IN(12,13) group by inq.user_id) as inquiry on inquiry.user_id=us.user_id 
    where us.active=0 and us.user_type in (".$crm_user_type.") and us.company_id='$_SESSION[company_id]'";
    $query_rs=$dbcon->query($query);
    $i=1;
    $total=0;
    while($row_p=mysqli_fetch_assoc($query_rs))
    { 
        if($row_p["cou"] <= 0){
            $total_link = $row_p["cou"];

        } else {
            $total_link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list/'.$row_p['user_id'].'" target="_blank" class="link_dash">'.$row_p['cou'].'</a>';
        }
        $str.='<tr> 
        <td>'.$i.'</td>
        <td>'.$row_p['user_name'].'</td>
        <td>'.$total_link.'</td>
        </tr>';

        $total=$total+$row_p['cou'];
        $i++;
    }
    
    $str.='<tr style="font-size:16px;"> 
    <td colspan="2" style="text-align:center"><strong>Total</strong></td>
    <td><strong>'.$total.'</strong></td>
    </tr>
    </table>';
    
    echo $str;
}
else if(strtolower($POST['mode']) == "sales_stage_repo") {
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $crm_user_type = $companyConfiguration['crm_user_type'];
    $_SESSION['start_date'] = $POST['sales_stage_start_date'];
    $_SESSION['end_date'] = $POST['sales_stage_end_date'];
    $str="";
    $str.='<table class="table">
    <tr> 
    <th width="10%" style="white-space:nowrap;">Sr. No.</th>
    <th width="70%">Employee Name</th>
    <th width="20%">Nos.</th>
    </tr>';
    $query="select us.user_id,us.user_name,IFNULL(cou,0) as cou from users as us
    left join 
    (select IFNULL(count(inquiry_id),0) as cou,invoice.user_id from tbl_inquiry as invoice where inquiry_status=0 and sales_stage_id=".$POST['sales_stage_id']." and invoice.company_id=".$_SESSION['company_id']." and mdate >= '".date('Y-m-d 00:00:00',strtotime($POST['sales_stage_start_date']))."' and mdate <= '".date('Y-m-d 23:59:59',strtotime($POST['sales_stage_end_date']))."' AND invoice.opp_id NOT IN(12,13) group by invoice.user_id) as debitinvoice on debitinvoice.user_id=us.user_id 

    where us.active=0 and us.user_type in (".$crm_user_type.") and us.company_id='$_SESSION[company_id]'";
    $query_rs=$dbcon->query($query);
    $i=1;
    $total=0;
    while($row_p=mysqli_fetch_assoc($query_rs))
    { 
        if($row_p["cou"] <= 0){
            $total_link = $row_p["cou"];

        } else {
            $total_link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list_sales_stage/'.$row_p['user_id'].'/'.$POST['sales_stage_id'].'" target="_blank" class="link_dash">'.$row_p['cou'].'</a>';
        }
        $str.='<tr> 
        <td>'.$i.'</td>
        <td>'.$row_p['user_name'].'</td>
        <td>'.$total_link.'</td>
        </tr>';

        $total = $total+$row_p['cou'];
        $i++;
    }

    $str.='<tr style="font-size:16px;"> 
    <td colspan="2" style="text-align:center"><strong>Total</strong></td>
    <td><strong>'.$total.'</strong></td>
    </tr>
    </table>';

    echo $str;
}
else if(strtolower($POST['mode']) == "stage_repo") {
    $_SESSION['start_date'] = $POST['source_start_date'];
    $_SESSION['end_date'] = $POST['source_end_date'];
    $str="";
    $str.='<table class="table">
    <tr> 
    <th width="10%" style="white-space:nowrap;">Sr. No.</th>
    <th width="70%">Stage Name</th>
    <th width="20%">Nos.</th>
    </tr>';
    $query="select ope.opp_id,ope.opp_stage,IFNULL(cou,0) as cou from tbl_opportunity_mst as ope
    left join 
    (select IFNULL(count(inquiry_id),0) as cou,invoice.opp_id from tbl_inquiry as invoice where inquiry_status=0 and user_id=".$POST['source_user_id']." and invoice.company_id=".$_SESSION['company_id']." and DATE(mdate) >= '".date('Y-m-d 00:00:00',strtotime($POST['source_start_date']))."' and DATE(mdate) <= '".date('Y-m-d 23:59:59',strtotime($POST['source_end_date']))."'  group by invoice.opp_id) as debitinvoice on debitinvoice.opp_id=ope.opp_id
    where ope.opp_status=0 and ope.company_id='$_SESSION[company_id]' group by ope.opp_id";
    $query_rs=$dbcon->query($query);
    $i=1;
    $total=0;
    while($row_p=mysqli_fetch_assoc($query_rs))
    { 
        if($row_p["cou"] <= 0) 
        {
            $total_link = $row_p["cou"];

        } else {
            $total_link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list/'.$POST['source_user_id'].'/'.$row_p['opp_id'].'" target="_blank" class="link_dash">'.$row_p['cou'].'</a>';
        }
        $str.='<tr> 
        <td>'.$i.'</td>
        <td>'.$row_p['opp_stage'].'</td>
        <td>'.$total_link.'</td>
        </tr>';

        $total=$total+$row_p['cou'];
        $i++;
    }

    $str.='<tr style="font-size:16px;"> 
    <td colspan="2" style="text-align:center"><strong>Total</strong></td>
    <td><strong>'.$total.'</strong></td>
    </tr>
    </table>';

    echo $str;
}
else if(strtolower($POST['mode']) == "source_repo1") {
    $_SESSION['start_date'] = $POST['source_start_date'];
    $_SESSION['end_date'] = $POST['source_end_date'];
    $str="";
    $str.='<table class="table">
    <tr> 
    <th width="10%" style="white-space:nowrap;">Sr. No.</th>
    <th width="70%" >Source Name</th>
    <th width="20%" >Nos.</th>
    </tr>';
    $query="select ope.rb_id,ope.rb_name,IFNULL(cou,0) as cou from tbl_refer_by as ope
    left join 
    (select IFNULL(count(inquiry_id),0) as cou,invoice.rb_id from tbl_inquiry as invoice where invoice.inquiry_status=0 and invoice.user_id=".$POST['source_user_id']." and invoice.company_id=".$_SESSION['company_id']." and invoice.mdate >= '".date('Y-m-d 00:00:00',strtotime($POST['source_start_date']))."' and invoice.mdate <= '".date('Y-m-d 23:59:59',strtotime($POST['source_end_date']))."' AND invoice.opp_id NOT IN(12,13) group by invoice.rb_id) as debitinvoice on debitinvoice.rb_id=ope.rb_id
    where ope.rb_status=0 group by ope.rb_id";
    $query_rs=$dbcon->query($query);
    $i=1;
    $total=0;
    while($row_p=mysqli_fetch_assoc($query_rs))
    { 
        if($row_p["cou"] <= 0) 
        {
            $total_link = $row_p["cou"];

        } else {
            $total_link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list_source/'.$POST['source_user_id'].'/'.$row_p['rb_id'].'" target="_blank" class="link_dash">'.$row_p['cou'].'</a>';
        }
        $str.='<tr> 
        <td >'.$i.'</td>
        <td>'.$row_p['rb_name'].'</td>
        <td>'.$total_link.'</td>
        </tr>';

        $total=$total+$row_p['cou'];
        $i++;
    }

    $str.='<tr style="font-size:16px;"> 
    <td colspan="2" style="text-align:center"><strong>Total</strong></td>
    <td><strong>'.$total.'</strong></td>
    </tr>
    </table>';

    echo $str;
}
else if(strtolower($POST['mode']) == "stage_summ") {
    $_SESSION['summary_start_date'] = $POST['stage_summ_start_date'];
    $_SESSION['summary_end_date'] = $POST['stage_summ_end_date'];
    $companyConfiguration=getCompanyConfiguration($dbcon);
    $crm_user_type = $companyConfiguration['crm_user_type'];
    $str="";
    $str.='<table class="table" style="margin-top:50px;overflow-x:scroll;">
    <tr> 
    <th width="10%" style="white-space:nowrap;">Sr. No.</th>
    <th width="30%" >Employee Name</th>';
    $qry="select opp_id,opp_stage from tbl_opportunity_mst where opp_status=0";
    $rs_state=$dbcon->query($qry);	
    $opp=array();
    $ca="";
    $caa="";
    while($row=mysqli_fetch_assoc($rs_state))
    {	
        $str.='<th style="white-space:nowrap;">'.$row["opp_stage"].'</th>';
        array_push($opp,$row["opp_id"]);
        $ca.="sum(case when ".$row["opp_id"]." = inq.opp_id then 1 else 0 end) '".$row["opp_id"]."',";
        $caa.="sum(".$row['opp_id'].") as ".$row['opp_id'].",";
    }
    $str.='<th>Total</th>
    </tr>';
    
    $query="select ".$ca." us.user_id,us.user_name,IFNULL(count(inq.inquiry_id),0) as cou from users as us
    left join tbl_inquiry as inq on inq.user_id=us.user_id
    where us.active=0 and inq.inquiry_status=0 and DATE(inq.cdate) >= '".date('Y-m-d',strtotime($POST['stage_summ_start_date']))."' and DATE(inq.cdate) <= '".date('Y-m-d',strtotime($POST['stage_summ_end_date']))."' AND us.company_id = ".$_SESSION['company_id']." AND us.user_type IN (".$crm_user_type.") group by inq.user_id";
    
    $query_rs=$dbcon->query($query);
    $i=1;
    $total=0;
    $opp_total = array();
    while($row_p=mysqli_fetch_assoc($query_rs))
    { 
        $str.='<tr> 
        <td >'.$i.'</td>
        <td>'.$row_p['user_name'].'</td>';
        for($x=0;$x<count($opp);$x++){
            $kp=$opp[$x];
            $opp_total[$opp[$x]] += $row_p[$kp];
            if($row_p[$kp] <= 0) 
            {
                $link = $row_p[$kp];

            } else {
                $link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list/'.$row_p['user_id'].'/'.$kp.'" target="_blank" class="link_dash">'.$row_p[$kp].'</a>';
            }
            $str.='<td style="text-align:center;">'.$link.'
            </td>';
        }
        
        if($row_p["cou"] <= 0) 
        {
            $total_link = $row_p["cou"];

        } else {
            $total_link = '<a href="'.ROOT.CRM_ROOT.'inquiry_list/'.$row_p['user_id'].'" target="_blank" class="link_dash">'.$row_p['cou'].'</a>';
        }
        $str.='<td style="text-align:center;"><strong>'.$total_link.'</strong></td>';
        $str.='</tr>';
        $i++;
    }
    $str.='<tr style="font-size:16px;"> 
    <td colspan="2" style="text-align:center"><strong>Total</strong></td>';
    for($x=0;$x<count($opp);$x++){
        $kp=$opp_total[$opp[$x]];
        $total = $total + $kp;
        $str.='<td style="text-align:center;"><strong>
        <a href="'.ROOT.CRM_ROOT.'inquiry_list/0/'.$opp[$x].'" target="_blank" class="link_dash">'.$kp.'</a></strong></td>';
    }
    $str.='<td style="text-align:center;"><strong><a href="'.ROOT.CRM_ROOT.'inquiry_list" target="_blank" class="link_dash">'.$total.'</a></strong></td>';
    $str.='</tr>
    </table>';
    
    echo $str;
} else if(strtolower($POST['mode']) == "inquiry_monthly_report") {
    $start_date = $POST['start_date'];
    $end_date = $POST['end_date'];
    $user_id = $POST['user_id'];

    $where = "";
    if (!empty($start_date) && !empty($end_date)) {
        $where = " and DATE_FORMAT(u.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE)";
    }
    
    $str="";
    $str.='<table class="table" border="1">
    <tr>
        <th rowspan="2">Finish Product Category</th>
        <th colspan="3">JAN</th>
        <th colspan="3">FEB</th>
        <th colspan="3">MAR</th>
        <th colspan="3">APR</th>
        <th colspan="3">MAY</th>
        <th colspan="3">JUN</th>
        <th colspan="3">JUL</th>
        <th colspan="3">AUG</th>
        <th colspan="3">SEP</th>
        <th colspan="3">OCT</th>
        <th colspan="3">NOV</th>
        <th colspan="3">DEC</th>
    </tr>
    <tr>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending Dispatch</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
        <th>Total</th>
        <th>SO Pending</th>
        <th>SO Dispatch Pending</th>
    </tr>';
    
  $forecast_query = "SELECT m.month, 
                        COALESCE(SUM(pro_intrn.ter_target_amt), 0) AS user_target_amt, COALESCE(SUM(pro_intrn.     ter_target_qty), 0) AS user_target_qty, forc_pro.f_period_id, pro_intrn.product_id AS category_id, cat.cat_name as category_name  
                    FROM ( SELECT 'Apr' AS MONTH, 4 AS month_number UNION SELECT 'May', 5 UNION SELECT 'Jun', 6 UNION SELECT 'Jul', 7 UNION SELECT 'Aug', 8 UNION SELECT 'Sep', 9 UNION SELECT 'Oct', 10 UNION SELECT 'Nov', 11 UNION SELECT 'Dec', 12 UNION SELECT 'Jan', 1 UNION SELECT 'Feb', 2 UNION SELECT 'Mar', 3 ) AS m 
                    LEFT JOIN tbl_forecast_byuser_pro AS forc_pro ON m.month_number = forc_pro.f_period_id 
                    LEFT JOIN tbl_f_byuser_pro_inrtrn AS pro_intrn ON forc_pro.forecast_id = pro_intrn.forecast_id 
                    LEFT JOIN tbl_category AS cat ON cat.cat_id = pro_intrn.product_id 
                    GROUP BY m.month, pro_intrn.product_id, forc_pro.forecast_id, forc_pro.f_period_id 
                    ORDER BY m.month_number";

    $result = $dbcon->query($forecast_query);
    $forecast_res = $result->fetch_all(MYSQLI_ASSOC);

    $so_query = "SELECT
                    m.month,
                    COALESCE(SUM(sotrn.product_qty), 0) AS product_qty,
                    COALESCE(SUM(sotrn.remaning_invoice_qty), 0) AS remaning_invoice_qty,
                    pm.product_category as category_id,
                    cat.cat_name as category_name
                FROM
                    (SELECT 'Apr' AS MONTH UNION SELECT 'May' AS MONTH UNION SELECT 'Jun' AS MONTH UNION SELECT 'Jul' AS MONTH UNION SELECT 'Aug' AS MONTH UNION SELECT 'Sep' AS MONTH UNION SELECT 'Oct' AS MONTH UNION SELECT 'Nov' AS MONTH UNION SELECT 'Dec' AS MONTH UNION SELECT 'Jan' AS MONTH UNION SELECT 'Feb' AS MONTH UNION SELECT 'Mar' AS MONTH) AS m
                    LEFT JOIN tbl_sales_order AS so ON MONTH(STR_TO_DATE(m.month, '%M')) = MONTH(so.sales_order_date)
                    LEFT JOIN tbl_sales_ordertrn AS sotrn ON so.sales_order_id = sotrn.sales_order_id
                    LEFT JOIN product_mst AS pm ON sotrn.product_id = pm.product_id
                    LEFT JOIN tbl_category AS cat ON cat.cat_id = pm.product_category
                    WHERE
                        sotrn.sales_order_id != 0
                    GROUP BY
                        m.month,
                        pm.product_category";

    $so_result = $dbcon->query($so_query);
    $so_res = $so_result->fetch_all(MYSQLI_ASSOC);

    $inq_arr = [];
    $cat_arr = [];
    foreach($forecast_res as $key => $value) {
        if (isset($value['category_id']) && !empty($value['category_id'])) {
            $month = $value['month'];
            $category_id = $value['category_id'];
            $forcast_qty = $value['user_target_qty'];
            
            $cat_arr[$category_id] =  $value['category_name'];

            $inq_arr[$category_id][$month]["total"] = $value['user_target_qty'];
            $inq_arr[$category_id][$month]["category_name"] = $value['category_name'];

            $matchingArrays = array_filter($so_res, function ($element) use ($month, $category_id) {
                return $element['month'] === $month && $element['category_id'] === $category_id;
            });

            if ($matchingArrays) {
                foreach($matchingArrays as $val) {
                    $product_qty = $val['product_qty'];
                    $remaning_invoice_qty = $val['remaning_invoice_qty'];
                    $invoice_qty = $val['product_qty'] - $val['remaning_invoice_qty'];
                    $pending_qty = $forcast_qty - $product_qty;

                    $inq_arr[$category_id][$month]["dis_invoice_qty"] = $remaning_invoice_qty;
                    $inq_arr[$category_id][$month]["pen_so_qty"] = $pending_qty;
                }
            } else {
                $inq_arr[$category_id][$month]["dis_invoice_qty"] = 0;
                $inq_arr[$category_id][$month]["pen_so_qty"] = $forcast_qty;
            }
        }
    }

    $months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    foreach ($inq_arr as $key => $inq) {
        $category_id = $key;
        $category_name = "";
        $str.='<tr style="text-align:center">';
        $str .= '<td><strong>'.$cat_arr[$key].'</strong></td>';
        foreach($months as $month) {
            if (isset($inq[$month])) {
                $str .= '<td>'.$inq[$month]['total'].'</td>';
                $str .= '<td>'.$inq[$month]['pen_so_qty'].'</td>';
                if ($inq[$month]['dis_invoice_qty'] == 0) {
                    $str .= '<td>'.$inq[$month]['dis_invoice_qty'].'</td>';
                } else {
                    $str .= '<td><a href="javascript:void(0);" onClick="pending_dispatch_so('.$category_id.',\''.$month.'\');"  class="link_dash">'.$inq[$month]['dis_invoice_qty'].'</a></td>';
                }
            } else {
                $str .= '<td>0</td>';
                $str .= '<td>0</td>';
                $str .= '<td>0</td>';
            }
        }
        $str.='</tr>';    
    }

    $str .= '</table>';

    echo $str;

} else if(strtolower($POST['mode']) == "pending_dispatch_so") {
    $month = $POST['month'];
    $category_id = $POST['category_id'];

    $so_query = "SELECT so.sales_order_id, sotrn.product_qty,
                    sotrn.remaning_invoice_qty,
                    pm.product_id,pm.product_name,
                    pm.product_category as category_id,
                    cat.cat_name as category_name,
                    so.sales_order_date,
                    so.sales_order_no
                FROM tbl_sales_order as so 
                    LEFT JOIN tbl_sales_ordertrn AS sotrn ON so.sales_order_id = sotrn.sales_order_id
                    LEFT JOIN product_mst AS pm ON sotrn.product_id = pm.product_id
                    LEFT JOIN tbl_category AS cat ON cat.cat_id = pm.product_category
                WHERE
                    sotrn.sales_order_id != 0 and MONTH(so.sales_order_date) = MONTH(STR_TO_DATE('".$month."', '%M')) and pm.product_category = $category_id and sotrn.remaning_invoice_qty != 0";
    
    $so_result = $dbcon->query($so_query);
    $pend_dis_res = $so_result->fetch_all(MYSQLI_ASSOC);

    $str="";
    $str.='<table class="table" border="1">
        <tr>
            <th>Sr. No</th>
            <th>Order No</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Dayss</th>
        </tr>';
    $i = 1;
    foreach($pend_dis_res as $key => $val) {
        $str .= '<tr>
                    <td>'.$i.'</td>    
                    <td>'.$val["sales_order_no"].'</td>    
                    <td>'.$val["product_name"].'</td>    
                    <td>'.$val["remaning_invoice_qty"].'</td>    
                    <td>'.$val["sales_order_date"].'</td>    
                </tr>';
        $i++;
    }
    $str .= '</table>';

    echo $str;
}
?>