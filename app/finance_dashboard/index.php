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
		
if(strtolower($POST['mode']) == "dynamic_chart") {
                //var_dump($_REQUEST);
        $date=get_sdate($POST['c_year']);	
        $whr='';
        if($_SESSION['user_type']!='2'){
                $whr.=' and u.user_id='.$_SESSION['user_id'];
        }

        $query="SELECT m.month,(select count(inquiry_id) from tbl_inquiry u 
where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(u.inquiry_date) and inquiry_status=0 and company_id=".$_SESSION['company_id']." and u.inquiry_date between '".date('Y-m-d',strtotime($date['start_date']))."' and '".date('Y-m-d',strtotime($date['end_date']))."' ".$whr.") as invoice
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
                $invoice_counter=$dbcon->query($query);
        //	echo $query;
                $row	= array();
                $i=0;
                while($chart=mysqli_fetch_assoc($invoice_counter))
                {	
                        $row[$chart['month']][]=intval($chart['invoice']);
                        $row[]= $chart['month'];
                        $row1[$i]['device']=$chart['month'];
                        $row1[$i]['geekbench']=$chart['invoice'];
                        $i++;
                }		
                //var_dump($row);	
                echo json_encode($row1);
}
		else if(strtolower($POST['mode']) == "lead_circle") {
			//$date=get_sdate($POST['c_year']);
			$user_ids=check_user_chein($dbcon,$POST['user_id'],1);
                        $query="select count(inquiry_id) as led,rf.rb_name from tbl_inquiry as e 
                                left join tbl_refer_by as rf on rf.rb_id=e.rb_id
                                where e.inquiry_status=0 and e.user_id in (".$user_ids.") 
                                    and DATE_FORMAT(e.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) group by e.rb_id";
			
				$invoice_turnover=$dbcon->query($query);
				$row1 = array();
				$i=0;
				while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
				{	
					$row1[$i]['label']=intval($invoice_circle['rb_name']);
					$row1[$i]['symbol']=$invoice_circle['rb_name'];
					$row1[$i]['y']=$invoice_circle['led'];			
					$i++;
				}	
				//$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
				echo json_encode($row1);
		}
    else if(strtolower($POST['mode']) == "load_payable_ageing"){
        $qry = "SELECT sum(tgb.amount) as outgoing_bills, terms.payment_days as days
                    FROM tbl_general_book as tgb 
                    LEFT JOIN tbl_purchaseorder as po ON po.vender_id = tgb.ledger_id AND po.purchaseorder_id = tgb.table_id 
                    LEFT JOIN tbl_purchaseordertrn as pot ON pot.purchaseordertrn_id = po.purchaseorder_id 
                    LEFT JOIN pay_terms as terms ON terms.terms_id = po.payment_terms
                    LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id 
                    LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group
                    where genral_book_status = ".ACTIVE." 
                        AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                        AND po.purchaseorder_date >= '".date('Y-m-d')."' 
                        AND pot.product_type != ".CHARGES."
                    group by po.payment_terms";
        $result = mysqli_query($dbcon,$qry);
        $payable_ageing = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $i=0;
        foreach ($payable_ageing as $chart) {
                $row[$i]['label']=$chart['days'];
                $row[$i]['y']=intval($chart['outgoing_bills']);	
                $i++;
        }
        //echo '<pre>';        print_r($payable_ageing);
        echo json_encode($row);
    }
    else if(strtolower($POST['mode']) == "load_receivable_ageing"){
        $qry = "SELECT SUM(cert.product_amount) as incoming_bills, terms.payment_days as days
                FROM tbl_invoicetrn as cert 
                LEFT JOIN product_mst as pro on pro.product_id=cert.product_id
                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id
                LEFT JOIN pay_terms as terms ON terms.terms_id = po.payment_terms
                LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id
                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group
                WHERE trancation_status = ".ACTIVE."
                    AND gro.g_id = ".SALES_ACCOUNTS." 
                    AND po.invoice_date >= '".date('Y-m-d')."'
                    AND pro.product_type != ".CHARGES."
                group by po.payment_terms";
        $result = mysqli_query($dbcon,$qry);
        $receivable_ageing = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $i=0;
        foreach ($receivable_ageing as $chart) {
               // $row[$i]['label']=$chart['days'];
                //$row[$i]['label']=100;
               // $row[$i]['y']=intval($chart['incoming_bills']);	
                //$row[$i]['y']=324;	
                $i++;
        }
		

		
        //echo '<pre>';        print_r($payable_ageing);
        echo json_encode($row);
    }
    else if(strtolower($POST['mode']) == "load_lead_by_product") {
            //$date=get_sdate($POST['c_year']);
            $user_ids=check_user_chein($dbcon,$POST['user_id'],1);
                    $query="select count(inq.inquiry_id) as led,pro.product_name as pg_name from product_mst as pro
                            left join tbl_inquiry_trn as itrn on itrn.product_id=pro.product_id
                            left join tbl_inquiry as inq on inq.inquiry_id=itrn.inquiry_id
                    where inq.inquiry_status=0 and itrn.inquiry_trn_status=0 and pro.product_status	=0 
                        and inq.user_id in (".$user_ids.") 
                        and DATE_FORMAT(inq.inquiry_date,'%Y-%m-%d') BETWEEN CAST('".date('Y-m-d',strtotime($POST['start_date']))."'AS DATE) and CAST('".date('Y-m-d',strtotime($POST['end_date']))."'AS DATE) group by pro.product_id";

                    $invoice_turnover=$dbcon->query($query);
                    $row1 = array();
                    $i=0;
                    while($invoice_circle=mysqli_fetch_assoc($invoice_turnover))
                    {	
                            $row1[$i]['label']=$invoice_circle['pg_name'];
                            $row1[$i]['y']=intval($invoice_circle['led']);			
                            $i++;
                    }	
                    //$row1='{y: 8000, legendText:Jan, indexLabel: Jan }';	
                    echo json_encode($row1);
    }
    else if(strtolower($POST['mode']) == "load_bank_balance") {
        $deposit_qry = "SELECT cert.dr_amount as deposit, QUARTER(cgen.ref_date) as quarter
                        FROM tbl_general_book as cgen
                        LEFT JOIN account_voucher_trn as cert ON cert.voucher_trnid = cgen.table_id 
                        LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id 
                        LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                        where trn_status = ".ACTIVE." 
                                AND gro.g_id = ".BANK_ACCOUNTS." 
                                AND cert.type_id = ".DEBIT." 
                                AND cgen.entry_type = ".DEBIT."
                                AND cgen.ref_date between <= '".date('Y-m-d')."'
                        group by quarter
                        order by quarter DESC";
        $deposit_result = mysqli_query($dbcon,$deposit_qry);
        $deposit = mysqli_fetch_all($deposit_result,MYSQLI_ASSOC);
        
        foreach ($deposit as $value) {
            $deposits[$value['quarter']] = $value['deposit'];
        }
        
        $withdraw_qry = "SELECT sum(cert.cr_amount) as withdraw,QUARTER(cgen.ref_date) as quarter
                            FROM tbl_general_book as cgen
                            LEFT JOIN account_voucher_trn as cert ON cert.voucher_trnid = cgen.table_id 
                            LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id 
                            LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                            where trn_status = ".ACTIVE." 
                                AND gro.g_id = ".BANK_ACCOUNTS." 
                                AND cert.type_id = ".CREDIT." 
                                AND cgen.entry_type = ".CREDIT."
                                AND cgen.ref_date between <= '".date('Y-m-d')."'
                            group by quarter
                            order by quarter DESC";
        $result = mysqli_query($dbcon,$withdraw_qry);
        $withdraw = mysqli_fetch_all($result,MYSQLI_ASSOC);
        
        foreach ($withdraw as $value) {
            $withdrawals[$value['quarter']] = $value['withdraw'];
        }
        
        //echo '<pre>';        print_r($deposits);
        //echo '<pre>';        print_r($withdrawals);
        $bank_balance = array();
        for($i=0; $i<=4; $i++){
            $bank_balance[$i]['label'] = $i;
            $bank_balance[$i]['y'] = floatval($deposits[$i] - $withdrawals[$i]);	
        }
        echo json_encode($bank_balance);
    }
    else if(strtolower($POST['mode']) == "load_outgoing_bills") {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        
        $incoming_bills_qry = "SELECT m.month,
            (select SUM(cert.product_amount) 
                FROM tbl_invoicetrn as cert 
                LEFT JOIN product_mst as pro on pro.product_id=cert.product_id 
                LEFT JOIN tbl_invoice as po on po.invoice_id=cert.invoice_id 
                LEFT JOIN tbl_ledger as led on led.l_id=po.sales_ledger_id 
                LEFT JOIN tbl_group as gro on gro.g_id=led.l_group 
                where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(invoice_date) 
                AND trancation_status = ".ACTIVE." AND gro.g_id = ".SALES_ACCOUNTS." 
                AND po.invoice_date between '2019-09-30' and '".date('Y-m-d')."' AND pro.product_type != ".CHARGES.") as invoice
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
        $result = mysqli_query($dbcon,$incoming_bills_qry);
        $incoming_bills = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $i=0;
        foreach ($incoming_bills as $chart) {
                $row[$i]['label']=$chart['month'];
                $row[$i]['y']=intval($chart['invoice']);	
                $i++;
        }
        //echo '<pre>';        print_r($incoming_bills);
        echo json_encode($row);
    }
    else if(strtolower($POST['mode']) == "load_incoming_bills") {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        
        $outgoing_bill_qry = "SELECT m.month,(SELECT sum(po.g_total) FROM tbl_purchaseorder as po
                LEFT JOIN tbl_purchaseordertrn as pot ON pot.purchaseordertrn_id = po.purchaseorder_id 
                LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id 
                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group
                where MONTH(STR_TO_DATE(m.month,'%M')) = MONTH(po.purchaseorder_date) 
                AND po.status = ".ACTIVE." AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                AND po.purchaseorder_date between '2020-09-01' and '".date('Y-m-d')."' 
                AND pot.product_type != ".CHARGES.") as purchase
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
        $result = mysqli_query($dbcon,$outgoing_bill_qry);
        $outgoing_bills = mysqli_fetch_all($result,MYSQLI_ASSOC);
        $i=0;
        foreach ($outgoing_bills as $chart) {
                $row[$i]['label']=$chart['month'];
                $row[$i]['y']=intval($chart['purchase']);	
                $i++;
        }
        //echo '<pre>';        print_r($incoming_bills);
        echo json_encode($row);
    }
    else if(strtolower($POST['mode']) == "load_counts") {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-d');
        
        $last_month_start_date = date('Y-m-d', strtotime('first day of last month'));
        $last_month_end_date = date('Y-m-d', strtotime('last day of previous month'));
        $outgoing_bills = $incoming_bills = $incoming_payment = $outgoing_payment = 0;
        
        $outgoing_bills = $dbcon->query("SELECT sum(po.g_total) as outgoing_bills 
                From tbl_purchaseorder as po 
                LEFT JOIN tbl_purchaseordertrn as pot ON pot.purchaseordertrn_id = po.purchaseorder_id 
                LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                WHERE po.status = ".ACTIVE." AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                        AND po.purchaseorder_date between '".$start_date."' and '".$end_date."'
                        AND pot.product_type != ".CHARGES)->fetch_object()->outgoing_bills;
        
        $last_month_outgoing_bills = $dbcon->query("SELECT sum(po.g_total) as outgoing_bills 
                From tbl_purchaseorder as po 
                LEFT JOIN tbl_purchaseordertrn as pot ON pot.purchaseordertrn_id = po.purchaseorder_id 
                LEFT JOIN tbl_ledger as led ON led.l_id = po.purchase_ledger_id LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                WHERE po.status = ".ACTIVE." AND gro.g_id = ".PURCHASE_ACCOUNTS." 
                        AND po.purchaseorder_date between '".$last_month_start_date."' and '".$last_month_end_date."'
                        AND pot.product_type != ".CHARGES)->fetch_object()->outgoing_bills;

        //$last_month_outgoing_bills = 100000;
        $outgoing_bills_diff = abs($last_month_outgoing_bills - $outgoing_bills);
        $outgoing_bills_percentage = abs(round(($outgoing_bills_diff * 100) / $last_month_outgoing_bills));

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

        //$last_month_incoming_bills1 = 100000;
        $incoming_bills_diff = abs($last_month_incoming_bills - $incoming_bills);
        $incoming_bills_percentage = abs(round(($incoming_bills_diff * 100) / $last_month_outgoing_bills));
        			
        $incoming_payment = $dbcon->query("
                SELECT sum(cert.total_paid_amount) as incoming_payment
                FROM tbl_general_book as cgen
                LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id 
                LEFT JOIN tbl_receipt_trn as trn ON trn.receipt_id = cert.receipt_id
                LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id 
                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                where trn.status = ".ACTIVE." 
                    AND trn.payment_type = ".CREDIT." 
                    AND cgen.entry_type = ".CREDIT."
                    AND cgen.ref_date between '".$start_date."' and '".$end_date."'
                ")->fetch_object()->incoming_payment;
        
        $last_month_incoming_payment = $dbcon->query("
                SELECT sum(cert.total_paid_amount) as incoming_payment
                FROM tbl_general_book as cgen
                LEFT JOIN tbl_receipt as cert ON cert.receipt_id = cgen.table_id 
                LEFT JOIN tbl_receipt_trn as trn ON trn.receipt_id = cert.receipt_id
                LEFT JOIN tbl_ledger as led ON led.l_id = cgen.ledger_id 
                LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
                where trn.status = ".ACTIVE." 
                    AND trn.payment_type = ".CREDIT." 
                    AND cgen.entry_type = ".CREDIT."
                    AND cgen.ref_date between '".$last_month_start_date."' and '".$last_month_end_date."'
                ")->fetch_object()->incoming_payment;

        $outgoing_payment = $dbcon->query("
                SELECT sum(receipt.total_paid_amount) as outgoing_payment
                    FROM tbl_receipt as receipt 
                    LEFT JOIN tbl_receipt_trn as trn ON trn.receipt_id = receipt.receipt_id
                    inner join tbl_ledger vender on vender.l_id=receipt.cust_id 
                    left join tbl_ledger payment on payment.l_id=receipt.payment_mode_id 
                    where receipt.status = ".ACTIVE." 
                        AND receipt.receipt_date between '".$start_date."' and '".$end_date."'
                        AND trn.payment_type = ".DEBIT."
                ")->fetch_object()->outgoing_payment;
        
        $last_month_outgoing_payment = $dbcon->query("
                SELECT sum(receipt.total_paid_amount) as outgoing_payment
                    FROM tbl_receipt as receipt 
                    LEFT JOIN tbl_receipt_trn as trn ON trn.receipt_id = receipt.receipt_id
                    inner join tbl_ledger vender on vender.l_id=receipt.cust_id 
                    left join tbl_ledger payment on payment.l_id=receipt.payment_mode_id 
                    where receipt.status = ".ACTIVE." 
                        AND receipt.receipt_date between '".$last_month_start_date."' and '".$last_month_end_date."'
                        AND trn.payment_type = ".DEBIT."
                ")->fetch_object()->outgoing_payment;

            $count['outgoing_bills'] = floatval($incoming_bills);
            $count['outgoing_bills_percentage'] = floatval($incoming_bills_percentage);
            $count['incoming_bills'] = floatval($outgoing_bills);
            $count['incoming_bills_percentage'] = floatval($outgoing_bills_percentage);
            $count['incoming_payment'] = floatval($incoming_payment);
            $count['last_month_incoming_payment'] = floatval($last_month_incoming_payment);
            $count['outgoing_payment'] = floatval($outgoing_payment);
            $count['last_month_outgoing_payment'] = floatval($last_month_outgoing_payment);
            
            echo json_encode($count);
    }
	
function get_sdate($date){
	$sdate['start_date']=date('01-04-'.$date);
	$sdate['end_date']=date('31-03-'.($date+1));
	return $sdate;	
}