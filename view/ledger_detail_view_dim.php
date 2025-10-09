<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
$form="Ledger Vouchers";

$ledger_id = $_REQUEST['ledger_id'];
$month = $_REQUEST['month'];
$dates = get_financial_year();
extract($dates);

$year = date('Y');
if(!in_array($month, array(1,2,3))){
    $year = $year -1;
}
        
$date = new DateTime($start_date);
$start_date = $date->format($year.'-'.$month.'-d');
$end_date = $date->format($year.'-'.$month.'-t');

//$start_date = date('Y-'.$month.'-01');
//$end_date = date('Y-'.$month.'-t');

//$start_date = date('2020-11-01');
//$end_date = date("Y-m-d");
$where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;

$ca_entries = array();
if($ledger_id){
           $ledger_name = $dbcon->query("SELECT l_name as ledger_name FROM `tbl_ledger` WHERE `l_id` = ".$ledger_id)->fetch_object()->ledger_name;
        }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once('../include/include_css_file.php');?>
    </head>
    <body>
        <style type="text/css">
        .link_dash
	{
		border-bottom:dotted blue thin;
	}
        </style>
        <section id="container">
        <?php include_once('../include/include_top_menu.php');?>
        <?php include_once('../include/left_menu.php');?>
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel">
                                <header class="panel-heading"><h3><?=$mode.' '.$form?></h3></header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><?=$form?> Report</li>
                                    </ul>
                                </div>
                            </section>
                        </div>	
                    </div>
                    <div class="row">			
                        <div class="col-sm-12">
                            <section class="panel">
                                <header class="panel-heading"><?=$ledger_name?></header>	
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12"  style="margin-top:10px;">
                                            <table  class="display table table-bordered table-striped">
                                                <thead>
                                                    <tr>
							<th width="15%" style="text-align:center">Date</th>
							<th width="46%" style="text-align:center">Particulars</th>
                                                        <th width="12%" style="text-align:center">Vch Type</th>
                                                        <th width="12%" style="text-align:center">Vch No</th>
							<th width="12%" style="text-align:center">Debit</th>
							<th width="12%" style="text-align:center">Credit</th>
                                                    </tr>
                                                </thead>
            <?php
            $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,sum(debitamount) as debitamount ,
                sum(creditamount) as creditamount,l_name as ledger_name, l_id as ledger_id
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=0 and table_name!='tbl_ledger' 
                            and entry_type= 2 and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= 0 and table_name!='tbl_ledger' 
                            and entry_type= 1 and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = 0 AND company_id = ".$_SESSION['company_id']." 
                    AND cust.l_id IN (".$ledger_id.")
                    group by cust.l_id
                    Order by l_name ASC ";
        
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result); exit;
                if($ca_result){
                    foreach ($ca_result as $value) {
                        //echo '<pre>';        print_r($ca_result); //exit;
                        $balance_type = $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                        $op_balance = $op_balance + ($value['debitamount']-$value['creditamount']);
                        $balancetype='';
                        
                        $payment_qry = 'select * from tbl_general_book as payment
				where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id='.$value['ledger_id'].'
                                ORDER BY payment.ref_date
                                ';
                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        $debit_total = $credit_total = 0;
                        if($payment_result){
                            foreach ($payment_result as $payment) {
                                $balancetype='';
                                $str.='<tr>
                                    <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($payment["ref_date"])).'</td>';
                                    $ref_no = load_ledger_detail($dbcon,$payment['table_name'], $payment['table_id'], $ledger_id);
                                    if($payment['table_name']=="tbl_invoice")
                                    {
                                        $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                        $str .='<td data-label="Vch Type" style="text-align:center">Sales</td>';
                                        $str .='<td data-label="Vch No" style="text-align:center">
                                            <a style="color: inherit;" class="link_dash" href="invoiceedit/'.$ref_no["invoice_id"].'" target="_blank">
                                            '.$ref_no['invoice_no'].'</a></td>';
                                            
                                    }
                                    else if($payment['table_name']=="tbl_purchase"){
                                        
                                        $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                        $str .='<td data-label="Vch Type" style="text-align:center">Purchace</td>';
                                        $str .='<td data-label="Vch No" style="text-align:center">
                                            <a style="color: inherit;" class="link_dash" href="purchaseedit/'.$ref_no["po_id"].'" target="_blank">
                                            '.$ref_no['po_no'].'</a></td>';
                                        //$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Purchace No : '.$ref_no['po_no'].'</td>';
                                        //$str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                    }
                                    else if($payment['table_name']=="tbl_payment"){
                                            if($ref_no['payment_mode_id'] == $ledger_id){
                                                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['cust_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->l_name;
                                            } else {
                                                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['payment_mode_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->l_name;
                                            }
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ledger_name.'</td>';
                                            if($re['entry_type']=="1"){
                                                    $str .='<td data-label="Vch Type" style="text-align:center">Recipt</td>';
                                                    $str .='<td data-label="Vch No" style="text-align:center">
                                                        <a style="color: inherit;" class="link_dash" href="receipt_sales/'.$ref_no["receipt_id"].'" target="_blank">
                                                        '.$ref_no['receipt_no'].'</a></td>';
                                            }else{
                                                    $str .='<td data-label="Vch Type" style="text-align:center">Payment</td>';
                                                    $str .='<td data-label="Vch No" style="text-align:center">
                                                        <a style="color: inherit;" class="link_dash" href="receipt_purchase/'.$ref_no["receipt_id"].'" target="_blank">
                                                        '.$ref_no['receipt_no'].'</a>
                                                    </td>';
                                            }
                                            
                                    }
                                    else if($payment['table_name']=="tbl_journal_trn"){
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Journal</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="journal_entry_edit/'.$ref_no["journal_id"].'" target="_blank">
                                                '.$ref_no['journal_no'].'</a></td>';
                                    }
                                    else if($payment['table_name']=="tbl_contra_trn"){
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Contra</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="contra_entry_edit/'.$ref_no["contra_id"].'" target="_blank">
                                                '.$ref_no['contra_no'].'</td>';
                                    }
                                    else{
                                            $str .='<td data-label="Particulars" style="text-align:left">-</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">-</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">-</td>';
                                    }
                                    if($payment['entry_type']==2){
                                        $str.='
                                        <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.indian_number(abs($payment['amount']),2,".",",").'</td>
                                        <td data-label="CREDIT AMOUNT" style="text-align:center;color:red;"></td>';
                                            $debit_total += $payment['amount'];

                                    }else{
                                        $str.='<td data-label="DEBIT AMOUNT" style="text-align:center;color:green;"></td>
                                            <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.indian_number(abs($payment['amount']),2,".",",").'</td>';
                                            $credit_total += $payment['amount'];
                                    }
                                $str .= '</tr>';
                            }
                        }
                        $str .='<tr>';
                        $str .= '<td colspan="4" style="text-align: right;"><b>Opening Balance</b></td>';
                        if($op_balance>0)
                        {
                            $balancetype='DR';
                            $str .='<td data-label="DEBIT AMOUNT" style="text-align:right;"><b>'.indian_number(abs($op_balance),2,".",",").'</b></td>
                                <td data-label="CREDIT AMOUNT" style="text-align:center;"></td>';
                        }
                        else if($op_balance<0)
                        {
                            $balancetype='CR';
                            $str .='<td data-label="DEBIT AMOUNT" style="text-align:center;"></td>
                                <td data-label="CREDIT AMOUNT" style="text-align:right;"><b>'.indian_number(abs($op_balance),2,".",",").'</b></td>';
                        }
                        else{
                            $str .='<td data-label="DEBIT AMOUNT" style="text-align:center;">-</td>
                                <td data-label="CREDIT AMOUNT" style="text-align:center;">-</td>';
                        }
                        $str .='</tr>';
                        
                        $str .= '<tr>
                                    <td colspan="4" style="text-align: right;"><b>Current Total</b></td>
                                    <td style="text-align: right;"><b>'.indian_number($debit_total,2).'</b></td>
                                    <td style="text-align: right;"><b>'.indian_number($credit_total,2).'</b></td>
                                </tr>';
                        $closing_balance = $op_balance + ($debit_total - $credit_total);
                        $str .= '<tr>
                                    <td colspan="4" style="text-align: right;"><b>Closing Balance</b></td>';
                        if($closing_balance > 0)
                        {
                            $str .='<td data-label="DEBIT AMOUNT" style="text-align:right;"><b>'.indian_number(abs($closing_balance),2,".",",").'</b></td>
                                <td data-label="CREDIT AMOUNT" style="text-align:center;"></td>';
                        }
                        else if($closing_balance < 0)
                        {
                            $str .='<td data-label="DEBIT AMOUNT" style="text-align:center;"></td>
                                <td data-label="BALANCE" style="text-align:right;"><b>'.indian_number(abs($closing_balance),2,".",",").'</b></td>';
                        } else {
                            $str .='<td data-label="DEBIT AMOUNT" style="text-align:right;"><b>0.00</b></td>
                                <td data-label="BALANCE" style="text-align:right;"><b>0.00</td>';
                        }
                        $str .= '<tr>';
                    }
                }
                echo $str; ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
        <?php include_once('../include/footer.php');?>
        </section>
        <?php include_once('../include/include_js_file.php');?>  
    </body>
</html>

