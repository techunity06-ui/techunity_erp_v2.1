<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include("../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
$form="Balance Sheet Details";

$sub_group_id = $_REQUEST['group_id'];
$start_date = date('Y-04-01');
$end_date = date("Y-m-d");
$where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;

$ca_entries = array();
if($sub_group_id){
        $group_name = $dbcon->query("SELECT g_name as ledger_name FROM `tbl_group` WHERE `g_id` = ".$sub_group_id)->fetch_object()->ledger_name;
        
        $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE `l_group` IN (".$sub_group_id.")";
        $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
//            
            $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,sum(debitamount) as debitamount ,
                sum(creditamount) as creditamount,l_name as group_name, l_id as ledger_id
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
                    AND l_group IN (".$sub_group_id.")
                    AND cust.l_id IN (".$sub_ledger.")
                    group by cust.l_id";
        
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result); exit;
                if($ca_result){
                    foreach ($ca_result as $value) {
                        $balance_type = $value['balance_typeid'];
                        //$balance_type = ($sub_group_id == SUNDRY_DEBTORS) ? '2' : $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount']-$value['creditamount']);
                        
                        $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
				where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id='.$value['ledger_id'].' 
                                GROUP BY payment.entry_type
                                ORDER BY payment.ref_date
                                ';
                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        if($payment_result){
                            foreach ($payment_result as $payment) {
                                if($payment['entry_type']==2){
                                    $balance += $payment['amount'];

                                }else{
                                    $balance -= $payment['amount'];
                                }
                            }
                        }
                        $ca_value['group_id'] = $sub_group_id;
                        $ca_value['group_name'] = $value['group_name'];
                        $ca_value['ca_value'] = abs($balance);
                        array_push($ca_entries, $ca_value);
                    }
                }
        }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once('../include/include_css_file.php');?>
    </head>
    <body>
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
                                <header class="panel-heading"><?=$group_name?> REPORT</header>	
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-12"  style="margin-top:10px;">
                                            <table  class="display table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Ledger Name</th>
                                                        <th style="text-align: right;">Balance</th>
                                                    </tr>
                                                </thead>
                                            <?php
                                            $ca_value = 0;
                                            if($ca_entries && !empty($ca_entries)){ ?>
                                                    <?php foreach ($ca_entries as $ca_entry) { 

                                                        $amount = number_format((float)$ca_entry["ca_value"], 2, '.', '');
                                                        $style = ($amount < 0) ? 'style="color: red;"' : ''; 
                                                        if($amount > 0){ ?>
                                                                <tr>
                                                                    <td><?= $ca_entry["group_name"] ?></td>
                                                                    <td style="text-align: right;" <?= $style ?>> <?= number_format($amount,2) ?></td>
                                                                </tr>
                                                    <?php  
                                                        }
                                                    $ca_value = $ca_value + $amount;
                                                    }
                                             } ?>
                                                <tr>
                                                    <td style="text-align: right;">Total</td>
                                                    <td style="text-align: right;"><b><?= number_format($ca_value,2) ?></b></td>
                                                </tr>
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
        <!--<script src="<?=ROOT?>js/app/balance_sheet.js"></script>-->
    </body>
</html>

