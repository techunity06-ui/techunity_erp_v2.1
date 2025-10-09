<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
//include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
if($_POST != NULL) {
    $POST = bulk_filter($dbcon,$_POST);
}
else {
    $POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "load_sales") {
                    //var_dump($_REQUEST);
    $year = get_current_financial_year();
    extract($year);
    if($_POST['amount_filter']==1){
        if($POST['sales_filter']=='2'){
            $query="SELECT m.month,(select sum(g_total) from tbl_invoice u 
                where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.invoice_date) and invoice_status=0 
                and company_id=".$_SESSION['company_id']." AND u.approve_status = 1
                and u.invoice_date between '".date('Y-m-d',strtotime($start_date))."' 
                and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                FROM (
                SELECT 'Apr' AS MONTH
                UNION SELECT 'May' AS MONTH
                UNION SELECT 'Jun' AS MONTH
                UNION SELECT 'Jul' AS MONTH
                UNION SELECT 'Aug' AS MONTH
                UNION SELECT 'Sep' AS MONTH
                UNION SELECT 'Oct' AS MONTH
                UNION SELECT 'Nov' AS MONTH
                UNION SELECT 'Dec' AS MONTH
                UNION SELECT 'Jan' AS MONTH
                UNION SELECT 'Feb' AS MONTH
                UNION SELECT 'Mar' AS MONTH
                ) AS m
                GROUP BY m.month
                ORDER BY 1+1";
            } else if($POST['sales_filter']=='1'){
                $query="SELECT m.month,(select sum(g_total) from tbl_invoice u 
                    where YEAR(STR_TO_DATE(m.month,'%Y')) = YEAR(u.invoice_date) and invoice_status=0 
                    and company_id=".$_SESSION['company_id']." AND u.approve_status = 1
                    and u.invoice_date between '".date('Y-m-d',strtotime($start_date))."' 
                    and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                    FROM (
                    SELECT '".date('Y',strtotime($start_date))."' AS MONTH
                    UNION SELECT '".date('Y',strtotime($end_date))."' AS MONTH
                    ) AS m
                    GROUP BY m.month
                    ORDER BY 1+1";
                } else{
                    $dates = '';
                    for($i=1; $i<=date('t'); $i++){
                        $ty = date("Y-m-");
                        if($i<date('t')){
                            $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH UNION ";
                        } else{
                            $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH"; 
                        }
                            // echo $i;
                    }
                    $query="SELECT m.month,(select sum(g_total) from tbl_invoice u 
                        where DAY(STR_TO_DATE(m.month,'%Y-%m-%d')) = DAY(u.invoice_date) and invoice_status=0 
                        and company_id=".$_SESSION['company_id']." AND u.approve_status = 1
                        and u.invoice_date between '".date('Y-m-d',strtotime($start_date))."' 
                        and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                        FROM (".
                        $dates
                        .") AS m
                        GROUP BY m.month
                        ORDER BY 1+1";
                    }
                } else{
                    if($POST['sales_filter']=='2'){
                        $query="SELECT m.month,(select sum(trn.product_qty) from tbl_invoicetrn as trn LEFT JOIN tbl_invoice As u on u.invoice_id = trn.invoice_id  where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.invoice_date) and invoice_status=0  and u.company_id=".$_SESSION['company_id']." AND u.approve_status = 1 and u.invoice_date between '".date('Y-m-d',strtotime($start_date))."' and '".date('Y-m-d',strtotime($end_date))."' and trn.trancation_status = 0) as invoice FROM (
                            SELECT 'Apr' AS MONTH
                            UNION SELECT 'May' AS MONTH
                            UNION SELECT 'Jun' AS MONTH
                            UNION SELECT 'Jul' AS MONTH
                            UNION SELECT 'Aug' AS MONTH
                            UNION SELECT 'Sep' AS MONTH
                            UNION SELECT 'Oct' AS MONTH
                            UNION SELECT 'Nov' AS MONTH
                            UNION SELECT 'Dec' AS MONTH
                            UNION SELECT 'Jan' AS MONTH
                            UNION SELECT 'Feb' AS MONTH
                            UNION SELECT 'Mar' AS MONTH
                            ) AS m
                            GROUP BY m.month
                            ORDER BY 1+1";
                        }else if($POST['sales_filter']=='1'){
                            $query="SELECT m.month,(select sum(trn.product_qty) from tbl_invoicetrn as trn LEFT JOIN tbl_invoice As u on u.invoice_id = trn.invoice_id 
                                where YEAR(STR_TO_DATE(m.month,'%Y')) = YEAR(u.invoice_date) and invoice_status=0 
                                and u.company_id=".$_SESSION['company_id']." AND u.approve_status = 1
                                and u.invoice_date between '".date('Y-m-d',strtotime($start_date))."' 
                                and '".date('Y-m-d',strtotime($end_date))."' and trn.trancation_status = 0) as invoice
                                FROM (
                                SELECT '".date('Y',strtotime($start_date))."' AS MONTH
                                UNION SELECT '".date('Y',strtotime($end_date))."' AS MONTH
                                ) AS m
                                GROUP BY m.month
                                ORDER BY 1+1";
                            }else{
                                $dates = '';
                                for($i=1; $i<=date('t'); $i++){
                                    $ty = date("Y-m-");
                                    if($i<date('t')){
                                        $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH UNION ";
                                    } else{
                                       $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH"; 
                                   }
                            // echo $i;
                               }
                               $query="SELECT m.month,(select sum(trn.product_qty) from tbl_invoicetrn as trn LEFT JOIN tbl_invoice As u on u.invoice_id = trn.invoice_id 
                                where DAY(STR_TO_DATE(m.month,'%Y-%m-%d')) = DAY(u.invoice_date) and invoice_status=0 
                                and u.company_id=".$_SESSION['company_id']." AND u.approve_status = 1
                                and u.invoice_date between '".date('Y-m-d',strtotime($start_date))."' 
                                and '".date('Y-m-d',strtotime($end_date))."' and trn.trancation_status = 0) as invoice
                                FROM (".
                                $dates
                                .") AS m
                                GROUP BY m.month
                                ORDER BY 1+1";
                            }
                        }
                    // echo $query;
                        $result = mysqli_query($dbcon,$query);
                        $invoice_counter = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        $row = array();
                        foreach($invoice_counter as $i => $chart){	
                            $row1[$i]['label']=$chart['month'];
                            $row1[$i]['y']=intval($chart['invoice']);	
                        }		
                        echo json_encode($row1);
                    }
                    else if(strtolower($POST['mode']) == "getcust") {
                        $year = get_current_financial_year();
                        extract($year);
                        $table1='';
                        if($_POST['cust_filter'] ==1){
                            $qry="SELECT SUM(invoice.g_total) AS total,cust.l_name as name from tbl_invoice as invoice 
                            inner join  tbl_ledger as cust on invoice.cust_id=cust.l_id  
                            where invoice_date>='".date('Y-m-d',strtotime($start_date))."' AND invoice_date<='".date('Y-m-d', strtotime($end_date))."' and cust.l_status = 0 and invoice_status=0 GROUP BY cust.l_id ORDER BY total desc limit 0,5";
                        }else{
                            $qry="SELECT trn.invoice_id, SUM(trn.product_qty) AS total,cust.l_name as name from tbl_invoicetrn as trn 
                            left join  tbl_invoice as invoice on invoice.invoice_id=trn.invoice_id
                            inner join  tbl_ledger as cust on invoice.cust_id=cust.l_id  
                            where invoice.invoice_date>='".date('Y-m-d',strtotime($start_date))."' AND invoice.invoice_date<='".date('Y-m-d', strtotime($end_date))."' and cust.l_status = 0 and invoice.invoice_status=0 and trn.trancation_status = 0 GROUP BY cust.l_id ORDER BY total desc limit 0,5";
                        }
                        $cat=$dbcon->query($qry);
                        $i=1;
                        $html = '';
                        while($rel=mysqli_fetch_assoc($cat))
                        {
                            $html .= '<tr>
                            <td>'.$rel['name'].'</td>
                            <td style="text-align: right">'.indian_number($rel['total']).'</td>
                            </tr>';
                            $i++;
                        }
                        echo $html;
                    }
                    else if(strtolower($POST['mode']) == "getvendors") {
                        $year = get_current_financial_year();
                        extract($year);
                        $table1='';
                        if($_POST['vendor_filter'] ==1){
                            $qry="SELECT SUM(purchase.g_total) AS total,cust.l_name as name from tbl_pono as purchase 
                            inner join tbl_ledger as cust on purchase.vender_id=cust.l_id 
                            where cust.l_status = 0 and purchase.status = 0 and po_date >= '".date('Y-m-d',strtotime($start_date))."' AND po_date <= '".date('Y-m-d', strtotime($end_date))."' GROUP BY cust.l_id ORDER BY total desc limit 0,5";
                        }else{
                            $qry="SELECT purchase.po_id, SUM(trn.product_qty) AS total,cust.l_name as name from tbl_potrancation as trn LEFT JOIN tbl_pono as purchase on purchase.po_id = trn.po_id
                            inner join tbl_ledger as cust on purchase.vender_id=cust.l_id 
                            where cust.l_status = 0 and purchase.status = 0 and trn.potrancation_status = 0 and purchase.po_date >= '".date('Y-m-d',strtotime($start_date))."' AND purchase.po_date <= '".date('Y-m-d', strtotime($end_date))."' GROUP BY cust.l_id ORDER BY total desc limit 0,5"; 
                        }
                        $cat=$dbcon->query($qry);
                        $i=1;
                        $html = '';
                        while($rel=mysqli_fetch_assoc($cat))
                        {
                            $html .= '<tr>
                            <td>'.$rel['name'].'</td>
                            <td style="text-align: right">'.indian_number($rel['total']).'</td>
                            </tr>';
                            $i++;
                        }
                        echo $html;
                    }
                    else if(strtolower($POST['mode']) == "getsold_products") {
                        $year = get_current_financial_year();
                        extract($year);
                        $whr='';
                        if(!empty($_POST['product_type'])){
                            $whr.=' AND product.product_type in ('.$_POST['product_type'].')';
                        }
                        if($_POST['product_filter']==0){
                            $topproductqry="SELECT trn.product_id ,product_name, sum(trn.product_qty) as product_qty 
                            FROM `tbl_invoicetrn` as trn 
                            left join product_mst as product on product.product_id=trn.product_id 
                            inner join  tbl_invoice as invoice on trn.invoice_id=invoice.invoice_id  
                            where trancation_status=0 and product_status=0 and invoice_date>='".date('Y-m-d',strtotime($start_date))."' 
                            AND invoice_date<='".date('Y-m-d',strtotime($end_date))."'".$whr."and invoice.company_id in (0,".$_SESSION['company_id'].") 
                            group by trn.product_id order by product_qty desc limit 0,5";
                        } else{
                            $topproductqry="SELECT trn.product_id ,product_name, sum(invoice.g_total) as product_qty 
                            FROM `tbl_invoicetrn` as trn 
                            left join product_mst as product on product.product_id=trn.product_id 
                            inner join  tbl_invoice as invoice on trn.invoice_id=invoice.invoice_id  
                            where trancation_status=0 and product_status=0 and invoice_date>='".date('Y-m-d',strtotime($start_date))."' 
                            AND invoice_date<='".date('Y-m-d',strtotime($end_date))."'".$whr."and invoice.company_id in (0,".$_SESSION['company_id'].") 
                            group by trn.product_id order by product_qty desc limit 0,5";
                        }
                        $cat = $dbcon->query($topproductqry);
                        $i=1;
                        $html = '';
                        while($rel=mysqli_fetch_assoc($cat))
                        {
                            $html .= '<tr>
                            <td>'.$rel['product_name'].'</td>
                            <td style="text-align: right">'.indian_number($rel['product_qty']).'</td>
                            </tr>';
                            $i++;
                        }
                        echo $html;
                    }
                    else if(strtolower($POST['mode']) == "getpurchase_products") {
                        $year = get_current_financial_year();
                        extract($year);
                        $whr='';
                        if(!empty($_POST['product_type'])){
                            $whr.=' AND product.product_type in ('.$_POST['product_type'].')';
                        }
                        if($_POST['product_filter']==0){
                            $qry = "SELECT trn.product_id, product_name, sum(trn.product_qty) as product_qty 
                            FROM `tbl_potrancation` as trn 
                            left join product_mst as product on product.product_id=trn.product_id 
                            left join tbl_pono as invoice on trn.po_id=invoice.po_id 
                            where trn.potrancation_status = 0 and product.product_status=0 AND invoice.po_date >= '".date('Y-m-d',strtotime($start_date))."' AND invoice.po_date <= '".date('Y-m-d',strtotime($end_date))."'".$whr." and invoice.company_id in (0,".$_SESSION['company_id'].") group by trn.product_id order by product_qty desc limit 0,5";
                        }else{
                            $qry = "SELECT trn.product_id, product_name, sum(invoice.g_total) as product_qty 
                            FROM `tbl_potrancation` as trn 
                            left join product_mst as product on product.product_id=trn.product_id 
                            left join tbl_pono as invoice on trn.po_id=invoice.po_id 
                            where potrancation_status = 0 and product_status=0 
                            AND po_date >= '".date('Y-m-d',strtotime($start_date))."' 
                            AND po_date <= '".date('Y-m-d',strtotime($end_date))."' 
                            ".$whr." and invoice.company_id in (0,".$_SESSION['company_id'].")  
                            group by trn.product_id 
                            order by product_qty desc 
                            limit 0,5";
                        }
                        $result = $dbcon->query($qry);
                        $i=1;
                        $html = '';
                        while($rel=mysqli_fetch_assoc($result))
                        {
                            $html .= '<tr>
                            <td>'.$rel['product_name'].'</td>
                            <td style="text-align: right">'.indian_number($rel['product_qty']).'</td>
                            </tr>';
                            $i++;
                        }
                        echo $html;
                    }
                    else if(strtolower($POST['mode']) == "load_purchase"){
                       $year = get_current_financial_year();
                       extract($year);
                       if($_POST['amount_filter']==0){
                        if($POST['purchase_filter']=='2'){
                            $query="SELECT m.month,(select sum(trn.product_qty) from tbl_potrancation as trn LEFT JOIN tbl_pono as u on u.po_id = trn.po_id
                                where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.order_date) and u.status=0 
                                and u.company_id = ".$_SESSION['company_id']." and trn.potrancation_status = 0
                                and u.order_date between '".date('Y-m-d',strtotime($start_date))."' 
                                and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                                FROM (
                                SELECT 'Apr' AS MONTH
                                UNION SELECT 'May' AS MONTH
                                UNION SELECT 'Jun' AS MONTH
                                UNION SELECT 'Jul' AS MONTH
                                UNION SELECT 'Aug' AS MONTH
                                UNION SELECT 'Sep' AS MONTH
                                UNION SELECT 'Oct' AS MONTH
                                UNION SELECT 'Nov' AS MONTH
                                UNION SELECT 'Dec' AS MONTH
                                UNION SELECT 'Jan' AS MONTH
                                UNION SELECT 'Feb' AS MONTH
                                UNION SELECT 'Mar' AS MONTH
                                ) AS m
                                GROUP BY m.month
                                ORDER BY 1+1";
                            } else if($POST['purchase_filter']=='1'){
                                $query="SELECT m.month,(select sum(trn.product_qty) from tbl_potrancation as trn LEFT JOIN tbl_pono as u on u.po_id = trn.po_id
                                    where YEAR(STR_TO_DATE(m.month,'%Y')) = YEAR(u.order_date) and u.status=0 
                                    and u.company_id = ".$_SESSION['company_id']." and trn.potrancation_status = 0
                                    and u.order_date between '".date('Y-m-d',strtotime($start_date))."' 
                                    and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                                    FROM (
                                    SELECT '".date('Y',strtotime($start_date))."' AS MONTH
                                    UNION SELECT '".date('Y',strtotime($end_date))."' AS MONTH
                                    ) AS m
                                    GROUP BY m.month
                                    ORDER BY 1+1";
                                } else{
                                    $dates = '';
                                    for($i=1; $i<=date('t'); $i++){
                                        $ty = date("Y-m-");
                                        if($i<date('t')){
                                            $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH UNION ";
                                        } else{
                                            $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH"; 
                                        }
                                    }
                                    $query="SELECT m.month,(select sum(trn.product_qty) from tbl_potrancation as trn LEFT JOIN tbl_pono as u on u.po_id = trn.po_id
                                        where DAY(STR_TO_DATE(m.month,'%Y-%m-%d')) = DAY(u.order_date) and u.status=0 
                                        and u.company_id = ".$_SESSION['company_id']." and trn.potrancation_status = 0
                                        and u.order_date between '".date('Y-m-d',strtotime($start_date))."' 
                                        and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                                        FROM (".
                                        $dates
                                        .") AS m
                                        GROUP BY m.month
                                        ORDER BY 1+1";
                                    }
                                }else{
                                    if($POST['purchase_filter']=='2'){
                                        $query="SELECT m.month,(select sum(g_total) from tbl_pono u 
                                            where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.order_date) and status=0 
                                            and company_id = ".$_SESSION['company_id']." 
                                            and u.order_date between '".date('Y-m-d',strtotime($start_date))."' 
                                            and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                                            FROM (
                                            SELECT 'Apr' AS MONTH
                                            UNION SELECT 'May' AS MONTH
                                            UNION SELECT 'Jun' AS MONTH
                                            UNION SELECT 'Jul' AS MONTH
                                            UNION SELECT 'Aug' AS MONTH
                                            UNION SELECT 'Sep' AS MONTH
                                            UNION SELECT 'Oct' AS MONTH
                                            UNION SELECT 'Nov' AS MONTH
                                            UNION SELECT 'Dec' AS MONTH
                                            UNION SELECT 'Jan' AS MONTH
                                            UNION SELECT 'Feb' AS MONTH
                                            UNION SELECT 'Mar' AS MONTH
                                            ) AS m
                                            GROUP BY m.month
                                            ORDER BY 1+1";
                                        } else if($POST['purchase_filter']=='1'){
                                            $query="SELECT m.month,(select sum(g_total) from tbl_pono u 
                                                where YEAR(STR_TO_DATE(m.month,'%Y')) = YEAR(u.order_date) and status=0 
                                                and company_id = ".$_SESSION['company_id']." 
                                                and u.order_date between '".date('Y-m-d',strtotime($start_date))."' 
                                                and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                                                FROM (
                                                SELECT '".date('Y',strtotime($start_date))."' AS MONTH
                                                UNION SELECT '".date('Y',strtotime($end_date))."' AS MONTH
                                                ) AS m
                                                GROUP BY m.month
                                                ORDER BY 1+1";
                                            } else {
                                                $dates = '';
                                                for($i=1; $i<=date('t'); $i++){
                                                    $ty = date("Y-m-");
                                                    if($i<date('t')){
                                                        $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH UNION ";
                                                    } else{
                                                        $dates.="SELECT '".$ty."".str_pad($i, 2, '0', STR_PAD_LEFT)."' AS MONTH"; 
                                                    }
                                                }
                                                $query="SELECT m.month,(select sum(g_total) from tbl_pono u 
                                                    where DAY(STR_TO_DATE(m.month,'%Y-%m-%d')) = DAY(u.order_date) and status=0 
                                                    and company_id = ".$_SESSION['company_id']." 
                                                    and u.order_date between '".date('Y-m-d',strtotime($start_date))."' 
                                                    and '".date('Y-m-d',strtotime($end_date))."' ) as invoice
                                                    FROM (".
                                                    $dates
                                                    .") AS m
                                                    GROUP BY m.month
                                                    ORDER BY 1+1";
                                                }
                                            }
                                            $result = mysqli_query($dbcon,$query);
                                            $purchase_counter = mysqli_fetch_all($result,MYSQLI_ASSOC);
                                            $row = array();
                                            foreach($purchase_counter as $i => $chart){ 
                                                $row[$i]['label']=$chart['month'];
                                                $row[$i]['y']=intval($chart['invoice']);    
                                            }       
                                            echo json_encode($row);
                                        }
                                        else if(strtolower($POST['mode']) == "load_counts") {
                                            $start_date = date('Y-m-01');
                                            $end_date = date('Y-m-d');

                                            $today = date('Y-m-d');
                                            $yesterday = date('Y-m-d', strtotime("-1 days"));

                                            $last_month_start_date = date('Y-m-d', strtotime('first day of last month'));
                                            $last_month_end_date = date('Y-m-d', strtotime('last day of previous month'));
                                            $outgoing_bills = $incoming_bills = $incoming_payment = $outgoing_payment = 0;

        //Sales of current and Last Month
                                            $today_sales = $dbcon->query("Select SUM(total) as today_sales from tbl_invoice as invoice
                                                left join tbl_invoicetrn as invtrn on invtrn.invoice_id=invoice.invoice_id
                                                where  invoice_date = '".$today."' 
                                                AND invoice_status=0 and invtrn.trancation_status=0 
                                                and invoice.company_id=".$_SESSION['company_id'])->fetch_object()->today_sales;

                                            $yesterday_sales = $dbcon->query("Select SUM(total) as yesterday_sales from tbl_invoice as invoice
                                                left join tbl_invoicetrn as invtrn on invtrn.invoice_id=invoice.invoice_id
                                                where  invoice_date = '".date('Y-m-d',$yesterday)."' 
                                                AND invoice_status=0 and invtrn.trancation_status=0 
                                                and invoice.company_id=".$_SESSION['company_id'])->fetch_object()->yesterday_sales;

                                            $sales_diff = abs($yesterday_sales - $today_sales);
                                            $sales_percentage = abs(round(($sales_diff * 100) / $yesterday_sales));

        //Purchase of today and Yesterday
                                            $today_purchase = $dbcon->query("Select sum(total) as today_purchase from tbl_pono as po 
                                                left join tbl_potrancation as potrn on potrn.po_id=po.po_id 
                                                where po_date ='".$today."' AND po.status=0 and potrn.potrancation_status=0 
                                                and po.company_id = ".$_SESSION['company_id'])->fetch_object()->today_purchase;

                                            $yesterday_purchase = $dbcon->query("Select SUM(total) as yesterday_purchase
                                                from tbl_pono as po 
                                                left join tbl_potrancation as potrn on potrn.po_id=po.po_id 
                                                where po_date = '".$yesterday."' 
                                                AND po.status=0 and potrn.potrancation_status=0 
                                                and po.company_id=".$_SESSION['company_id'])->fetch_object()->yesterday_purchase;

                                            $purchase_diff = abs($yesterday_purchase - $today_purchase);
                                            $purchase_percentage = abs(round(($purchase_diff * 100) / $yesterday_purchase));

        //Payable today and yesterday
                                            $outgoing_bills = $dbcon->query("SELECT sum(tgb.amount) as outgoing_bills
                                                FROM tbl_general_book as tgb 
                                                LEFT JOIN tbl_pono as po ON po.vender_id = tgb.ledger_id AND po.po_id = tgb.table_id
                                                LEFT JOIN tbl_potrancation as pot ON pot.po_id = po.po_id
                                                LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id 
                                                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                                                WHERE genral_book_status = ".ACTIVE." AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                                                AND po.po_date = '".$today."' 
                                                AND pot.product_type != ".CHARGES)->fetch_object()->outgoing_bills;

                                            $yesterday_outgoing_bills = $dbcon->query("SELECT sum(tgb.amount) as outgoing_bills
                                                FROM tbl_general_book as tgb 
                                                LEFT JOIN tbl_pono as po ON po.vender_id = tgb.ledger_id AND po.po_id = tgb.table_id
                                                LEFT JOIN tbl_potrancation as pot ON pot.po_id = po.po_id
                                                LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id 
                                                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                                                WHERE genral_book_status = ".ACTIVE." AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                                                AND po.po_date = '".$yesterday."'
                                                AND pot.product_type != ".CHARGES)->fetch_object()->outgoing_bills;

                                            $outgoing_bills_diff = abs($yesterday_outgoing_bills - $outgoing_bills);
                                            $outgoing_bills_percentage = abs(round(($outgoing_bills_diff * 100) / $yesterday_outgoing_bills));

        // Receivable Today and Yesterday
                                            $incoming_bills = $dbcon->query("SELECT SUM(cert.product_amount) as incoming_bills 
                                                FROM tbl_invoicetrn as cert 
                                                LEFT JOIN product_mst as pro on pro.product_id=cert.product_id
                                                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                                                LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
                                                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                                                WHERE trancation_status = ".ACTIVE." 
                                                AND gro.g_id = ".SALES_ACCOUNTS." 
                                                AND po.invoice_date between '".$start_date."' and '".$end_date."' 
                                                AND pro.product_type != ".CHARGES)->fetch_object()->incoming_bills;

                                            $last_month_incoming_bills = $dbcon->query("SELECT SUM(cert.product_amount) as incoming_bills 
                                                FROM tbl_invoicetrn as cert 
                                                LEFT JOIN product_mst as pro on pro.product_id=cert.product_id
                                                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                                                LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
                                                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                                                WHERE trancation_status = ".ACTIVE." 
                                                AND gro.g_id = ".SALES_ACCOUNTS." 
                                                AND po.invoice_date between '".$last_month_start_date."' and '".$last_month_end_date."' 
                                                AND pro.product_type != ".CHARGES)->fetch_object()->incoming_bills;

        //$last_month_incoming_bills = 100000;
                                            $incoming_bills_diff = abs($last_month_incoming_bills - $incoming_bills);
                                            $incoming_bills_percentage = abs(round(($incoming_bills_diff * 100) / $last_month_outgoing_bills));

                                            $count['sales'] = indian_number(floatval($today_sales),2);
                                            $count['sales_pecentage'] = floatval($sales_percentage);
                                            $count['purchase'] = indian_number(floatval($today_purchase),2);
                                            $count['purchase_pecentage'] = floatval($purchase_percentage);
                                            $count['outgoing_bills'] = indian_number(floatval($outgoing_bills),2);
                                            $count['outgoing_bills_percentage'] = floatval($outgoing_bills_percentage);
                                            $count['incoming_bills'] = indian_number(floatval($incoming_bills),2);
                                            $count['incoming_bills_percentage'] = floatval($incoming_bills_percentage);
                                            echo json_encode($count);
                                        }
                                        else if(strtolower($POST['mode']) == "load_profit_loss"){
                                        }