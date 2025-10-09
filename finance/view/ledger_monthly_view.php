<?php
session_start();
$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Ledger Detail By Month ";

$ledger_id = $_REQUEST['ledger_id'];
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
//echo $type;
$group_id = get_id_detail($dbcon,'tbl_ledger','l_id',$_REQUEST['ledger_id'],'l_group');

$start_date=$_SESSION['balance_sheet_start_date'];
$end_date=$_SESSION['balance_sheet_end_date'];

// $dates = get_financial_year();
// extract($dates);
$where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;

//echo $where_date;


 //$start_date = date('Y-m-d',strtotime($start_date));
 //$end_date = date('Y-m-d',strtotime($_POST['end_date']));
$ca_entries = array();


if($ledger_id){
        $ledger_name = $dbcon->query("SELECT l_name as ledger_name FROM `tbl_ledger` WHERE `l_id` = ".$ledger_id)->fetch_object()->ledger_name;
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
           // echo $ca_qry;
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result); exit;
                if($ca_result){
                    foreach ($ca_result as $value) {
                        $balance_type = $value['balance_typeid'];
                        //$balance_type = ($sub_group_id == SUNDRY_DEBTORS) ? '2' : $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount']-$value['creditamount']);
                        
						$payment_qry ='SELECT entry_type,sum(amount) as amount,MONTHNAME(payment.ref_date) month_name, MONTH(payment.ref_date) as month,YEAR(payment.ref_date) as year
                                FROM tbl_general_book as payment
                                WHERE payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.$start_date.'" 
                                    and ref_date<="'.$end_date.'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id='.$value['ledger_id'].'
                                GROUP BY month,entry_type
                                ORDER BY payment.ref_date
                                ';

                           //echo $payment_qry;

                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        if($payment_result){
                            foreach ($payment_result as $payment) {
                                $ca_entries[$payment['month_name']]['month'] = $payment['month'];
                                $ca_entries[$payment['month_name']]['year'] = $payment['year'];
                                if($payment['entry_type']==2){
                                    $ca_entries[$payment['month_name']]['debit'] = $payment['amount'];

                                }else{
                                    $ca_entries[$payment['month_name']]['credit'] = $payment['amount'];
                                }
                            }
                        }
                    }
                }
                //echo '<pre>'; print_r($ca_entries);
        }

?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once($include.'include_css_file.php');?>
            <style type="text/css">
                .border_down
                {
                    border-bottom: blue thin dotted;
                }
            </style>
    </head>
    <body>
        <section id="container">
        <?php include_once($include.'include_top_menu.php');?>
        <?php include_once($include.'left_menu.php');?>
            <section id="main-content">
                <section class="wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel">
                                <header class="panel-heading"><h3><?=$mode.' '.$form?></h3></header>	
                                <div class="">
                                    <ul class="breadcrumb">
                                        <li><a href="<?=ROOT.'dashboard'?>"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="<?=ROOT.FINANCE_ROOT ?>finance_report_list">Finance Report List</a></li>
                                        <?php if($type!='pl') { ?>
										  <li><a href="<?=ROOT.FINANCE_ROOT.'balance_sheet'?>"> Balance Sheet</a>  </li>
                                        <?php } else { ?>
                                            <li><a href="<?=ROOT.FINANCE_ROOT.'profit_loss_report'?>"> Profit & Loss </a>  </li>
                                        <?php } ?>
										


                                        <li><a class="border_down" href=<?=ROOT.FINANCE_ROOT."group_detail_view.php?group_id=".$group_id."&&type=".$type ?> >Group Detail<strong style="color:red">(<?=get_id_detail($dbcon,'tbl_group','g_id',$group_id,'g_name')?>)</strong> </a></li>

										<li><?=$form?> <strong style="color:red">(<?=get_id_detail($dbcon,'tbl_ledger','l_id',$_REQUEST['ledger_id'],'l_name')?>)</strong></li>
                                      
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
<!--                                                    <tr>
                                                        <td colspan="3" style="text-align: right;"><b>Opening Balance</b></td>
                                                        <td><?= $balance; ?></td>
                                                    </tr>-->
                                                    <tr>
							<th width="55%" style="text-align:center">Month</th>
                                                        <th width="15%" style="text-align:center">Debit</th>
							<th width="15%" style="text-align:center">Credit</th>
                                                        <th width="15%" style="text-align:center">Closing Balance</th>
                                                    </tr>
                                                </thead>
                                            <?php
                                            $ca_value = 0; $opening_balance = 0; $closing_bal = 0;$i=1;
                                            if($ca_entries && !empty($ca_entries)){ 
                                                //echo "<pre>"; print_r($ca_entries); echo "</pre>";
                                                foreach ($ca_entries as $month => $amount) {
                                                    if($opening_balance == 0){
                                                        $opening_balance = $balance;
                                                    }
                                                    $closing_bal = $opening_balance + ($amount['debit'] - $amount['credit']);
                                                    ?>
                                                    <tr>
                                                        <td tabindex="<?= $i ?>"><a style="color: inherit;" href="ledger_detail_view.php?ledger_id=<?= $ledger_id?>&month=<?= $amount['month']?>-<?=$amount['year'] ?>&type=<?=$type;?>"><?= $month ?></a></td>
                                                       <!-- <td><a style="color: inherit;" href="ledger_form/<?=$ledger_id?>&month=<?= $amount['month']?>" target="_blank"><?= $month ?></a></td>-->
                                                        <td style="text-align: right;"> <?= indian_number($amount['debit'],2) ?></td>
                                                        <td style="text-align: right;"> <?= indian_number($amount['credit'],2) ?></td>
                                                        <td style="text-align: right;"> <?= indian_number(abs($closing_bal),2) ?></td>
                                                    </tr>
                                                <?php
                                                    $opening_balance = $closing_bal;
                                                    $debit_total += $amount['debit'];
                                                    $credit_total += $amount['credit'];
                                                  
                                                }
                                            } ?>
                                                <tr>
                                                    <td style="text-align: left;"><strong>Grand Total</strong></td>
                                                    <td style="text-align: right;"><strong><?= indian_number($debit_total,2) ?></strong></td>
                                                    <td style="text-align: right;"><strong><?= indian_number($credit_total,2) ?></strong></td>
                                                    <td style="text-align: right;"><strong><?= indian_number(abs($closing_bal),2) ?></strong></td>
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
        <?php include_once($include.'footer.php');?>
        </section>
        <?php include_once($include.'include_js_file.php');?> 
        
        <script>
        	  /* Added By Jayesh 30-07-2021 For tab and enter key */   
		window.onkeyup = function(e){
		var event = e.which || e.keyCode || 0; // .which with fallback
			var current = $(':focus');
			var id = current.attr("tabIndex");
			var current_link = current.find('a:first').attr('href');
			if(event== 13)
			{
				if(typeof current_link == "undefined"){
					return false;
				}
				else{
					window.location=root_domain+finance_root_domain+current_link; // Navigate to URL	
					return false;					
				}
			}
			if(event== 27)
			{
				history.back();	
				return false;
			}			   	
		}
        /* Added By Jayesh 30-07-2021 For tab and enter key */   
        </script> 
    </body>
</html>

