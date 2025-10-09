<?php
session_start();
$path = '../../';
$include = '../../include/';
$include1 = '../include/';
include_once($path."config/config.php");
include_once($path."config/session.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");

$form="Ledger Vouchers";

$ledger_id = $_REQUEST['ledger_id'];
$month = $_REQUEST['month'];
$type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
/* $dates = get_financial_year();
$v=extract($dates);
 $year = date('Y');
if(!in_array($month, array(1,2,3))){
    $year = $year -1;
} */

$datemo=explode("-",$month);
//print_r($datemo);
$month=$datemo['0'];
$year=$datemo['1'];
      
//$date = new DateTime($start_date);
//$start_date = $date->format($year.'-'.$month.'-d');
//$end_date = $date->format($year.'-'.$month.'-t');

$start_date=$_SESSION['balance_sheet_start_date'];
$end_date=$_SESSION['balance_sheet_end_date'];

//$start_date = date('2020-11-01');
//$end_date = date("Y-m-d");
$where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;

//echo $where_date;
$ca_entries = array();
if($ledger_id){
   $ledger_name = $dbcon->query("SELECT l_name as ledger_name FROM `tbl_ledger` WHERE `l_id` = ".$ledger_id)->fetch_object()->ledger_name;
}

$group_id = get_id_detail($dbcon,'tbl_ledger','l_id',$_REQUEST['ledger_id'],'l_group');

?>
<!DOCTYPE html>
<html lang="en">
    <head>
            <?php include_once($include.'include_css_file.php');?>
    </head>
    <body>
        <style type="text/css">
        .link_dash
	{
		border-bottom:dotted blue thin;
	}

        </style>
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
                                        
                                        <li><a class="link_dash" href=<?=ROOT.FINANCE_ROOT."group_detail_view.php?group_id=".$group_id."&type=".$type ?> >Group Detail<strong style="color:red">(<?=get_id_detail($dbcon,'tbl_group','g_id',$group_id,'g_name')?>)</strong> </a></li>

                                        <li><a class="link_dash" href=<?=ROOT.FINANCE_ROOT."ledger_monthly_view.php?ledger_id=".$ledger_id."&type=".$type ?>><?=$form?> <strong style="color:red">(<?=get_id_detail($dbcon,'tbl_ledger','l_id',$_REQUEST['ledger_id'],'l_name')?>)</strong></a></li>

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
				/*$ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,sum(debitamount) as debitamount ,
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
                    Order by l_name ASC ";*/
					
					   $ca_qry ="SELECT 
    cust.opn_balance AS cust_opening_balance,
	(SELECT SUM(IF(entry_type=2,amount, 0) - IF(entry_type=1,amount, 0) ) 
FROM tbl_general_book AS gb 
WHERE gb.genral_book_status = 0 
AND gb.company_id = ".$_SESSION['company_id']." 
AND gb.ledger_id = ".$ledger_id." 
AND YEAR(gb.ref_date) = ".$year."
AND MONTH(gb.ref_date) < ".$month.") AS opening_balance,
    cust.balance_typeid,
    sum(invoice.amount) AS debitamount,
    sum(rec.amount) AS creditamount,
    cust.l_name AS ledger_name,
    cust.l_id AS ledger_id
FROM tbl_ledger AS cust
LEFT JOIN tbl_general_book AS invoice 
    ON invoice.ledger_id = cust.l_id 
    AND invoice.genral_book_status = 0 
    AND invoice.table_name != 'tbl_ledger' 
    AND invoice.entry_type = 2 
    AND invoice.company_id = ".$_SESSION['company_id']." 
    AND YEAR(invoice.ref_date) = ".$year." 
    AND MONTH(invoice.ref_date) = ".$month." 
LEFT JOIN tbl_general_book AS rec 
    ON rec.ledger_id = cust.l_id 
    AND rec.genral_book_status = 0 
    AND rec.table_name != 'tbl_ledger' 
    AND rec.entry_type = 1 
    AND rec.company_id = ".$_SESSION['company_id']." 
   AND YEAR(invoice.ref_date) = ".$year." 
    AND MONTH(invoice.ref_date) = ".$month." 
WHERE cust.l_status = 0 
    AND cust.company_id = ".$_SESSION['company_id']." 
    AND cust.l_id IN (".$ledger_id.")
GROUP BY cust.l_id
ORDER BY cust.l_name ASC";


                   // echo $ca_qry;exit;
        
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result); exit;
                if($ca_result){
                    foreach ($ca_result as $value) {
                        //echo '<pre>';        print_r($ca_result); //exit;
                        $balance_type = $value['balance_typeid'];
                       echo  $op_balance_cust = ($balance_type=="2" ? ($value['cust_opening_balance']) :-$value['cust_opening_balance']);
						
						if($value['opening_balance']==0)
						{
							
							$op_balance = $op_balance_cust + ($value['debitamount']-$value['creditamount']);
						}
						else
						{
							$op_balance = $value['opening_balance'];
						}
						
						
                       
                        $balancetype='';
                        
                       /* $payment_qry = 'select * from tbl_general_book as payment
								where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id='.$ledger_id.'
                                ORDER BY payment.ref_date
                                ';*/
								
								/*echo $payment_qry = 'select * from tbl_general_book as payment
								where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and  YEAR(ref_date) ="'.$year.'" 
                                    and MONTH(ref_date) ="'.$month.'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id='.$ledger_id.'
                                ORDER BY payment.ref_date
                                ';*/
								
								  $payment_qry = 'SELECT *, 
(SELECT SUM(IF(entry_type=2,amount, 0) - IF(entry_type=1,amount, 0) ) 
FROM tbl_general_book AS gb 
WHERE gb.genral_book_status = 0 
AND gb.company_id = '.$_SESSION['company_id'].' 
AND gb.ledger_id = '.$ledger_id.' 
AND YEAR(gb.ref_date) = "'.$year.'"
AND MONTH(gb.ref_date) < "'.$month.'") AS opening_balance
FROM tbl_general_book AS payment 
WHERE payment.genral_book_status = 0 
AND payment.company_id = '.$_SESSION['company_id'].' 
AND YEAR(payment.ref_date) = "'.$year.'" 
AND MONTH(payment.ref_date) ="'.$month.'" 
AND payment.table_name != "tbl_ledger" 
AND payment.ledger_id = '.$ledger_id.' 
ORDER BY payment.ref_date';



								
                            //echo $payment_qry."<br>";
                        $result = mysqli_query($dbcon, $payment_qry);
                        $payment_result = mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        $debit_total = $credit_total = 0;
                        if($payment_result){
                            foreach ($payment_result as $payment) {
                                $balancetype='';
                                $str.='<tr>
                                    <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($payment["ref_date"])).'</td>';
                                    $ref_no = load_ledger_detail($dbcon,$payment['table_name'], $payment['table_id'], $ledger_id);
                                   //print_r($ref_no);
                                    if($payment['table_name']=="tbl_invoice")
                                    {
                                        $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                        $str .='<td data-label="Vch Type" style="text-align:center">Sales</td>';
                                        $str .='<td data-label="Vch No" style="text-align:center">
                                            <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no["invoice_id"].'" >
                                            '.$ref_no['invoice_no'].'</a></td>';
                                            
                                    }
                                    else if($payment['table_name']=="tbl_pono"){
                                        $ref_no = load_ledger_detail($dbcon,$payment['table_name'], $payment['table_id'], $ledger_id);
                                        $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                        $str .='<td data-label="Vch Type" style="text-align:center">Purchace</td>';
                                        $str .='<td data-label="Vch No" style="text-align:center">
                                            <a style="color: inherit;" class="link_dash" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$ref_no["po_id"].'" >
                                            '.$ref_no['po_no'].'</a></td>';
                                        //$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Purchace No : '.$ref_no['po_no'].'</td>';
                                        //$str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                    }
                                    else if($payment['table_name']=="tbl_receipt"){
                                            if($ref_no['cust_id'] == $ledger_id){
                                                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['cust_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->l_name;
                                            } else {
                                                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['cust_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->l_name;
                                            }
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ledger_name.'</td>';
                                            if($ref_no['payment_type']=="2"){
                                                    $str .='<td data-label="Vch Type" style="text-align:center">Recipt</td>';
                                                    $str .='<td data-label="Vch No" style="text-align:center">
                                                        <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'receipt_sales/'.$ref_no["receipt_id"].'" >
                                                        '.$ref_no['receipt_no'].'</a></td>';
                                            }else{
                                                    $str .='<td data-label="Vch Type" style="text-align:center">Payment</td>';
                                                    $str .='<td tabindex="1" data-label="Vch No" style="text-align:center">
                                                        <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'receipt_purchase/'.$ref_no["receipt_id"].'" >
                                                        '.$ref_no['receipt_no'].'</a>
                                                    </td>';
                                            }
                                            
                                    }
                                    else if($payment['table_name']=="tbl_receipt_payment_trn"){
                                               // print_r($ref_no);
                                            if($ref_no['ledger_id'] == $ledger_id){
                                                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['ledger_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->l_name;
                                            } else {
                                                $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['ledger_id']." and company_id=".$_SESSION['company_id'])
                                                    ->fetch_object()->l_name;
                                            }
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ledger_name.'</td>';
                                            if($ref_no['payment_type']=="2"){
                                                    $str .='<td data-label="Vch Type" style="text-align:center">Recipt</td>';
                                                    $str .='<td data-label="Vch No" style="text-align:center">
                                                        <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'receipt_sales/'.$ref_no["receipt_id"].'" >
                                                        '.$ref_no['receipt_no'].'</a></td>';
                                            }else{
                                                    $str .='<td data-label="Vch Type" style="text-align:center">Payment</td>';
                                                    $str .='<td tabindex="1" data-label="Vch No" style="text-align:center">
                                                        <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'receipt_purchase/'.$ref_no["receipt_id"].'" >
                                                        '.$ref_no['receipt_no'].'</a>
                                                    </td>';
                                            }
                                            
                                    }
                                    else if($payment['table_name']=="tbl_journal_trn"){
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Journal</td>';
                                            $str .='<td tabindex="1" data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="journal_entry_edit/'.$ref_no["journal_id"].'" >
                                                '.$ref_no['journal_no'].'</a></td>';
                                    }
                                    else if($payment['table_name']=="tbl_contra_trn"){
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ref_no['ledger_name'].'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Contra</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="contra_entry_edit/'.$ref_no["contra_id"].'" >
                                                '.$ref_no['contra_no'].'</td>';
                                    }
                                    else if($payment['table_name']=="tbl_bill_sundry_transaction"){

                                        if($ref_no['table_name'] == 'tbl_invoice'){
                                            
                                            $ledger_name = $dbcon->query("select l_name from tbl_ledger where l_id=".$ref_no['cust_id']." and company_id=".$_SESSION['company_id'])->fetch_object()->l_name;

                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ledger_name.'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Sales</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no["invoice_id"].'" >
                                                '.$ref_no['invoice_no'].'</a></td>';

                                        }else if($ref_no['table_name'] == 'tbl_pono'){
                                            if(!empty($ref_no['vender_id'])){
                                                $where = ' and l_id='.$ref_no['vender_id'].'';
                                            }
                                            $ledger_name = $dbcon->query("select l_name from tbl_ledger where company_id=".$_SESSION['company_id'].$where)->fetch_object()->l_name;
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ledger_name.'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Purchace</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$ref_no["po_id"].'" >
                                                '.$ref_no['po_no'].'</a></td>';

                                        }else if($ref_no['table_name'] == 'tbl_debitnote'){
                                            
                                            //$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'debitnoteedit/'.$ref_no[2].'">DebitNote No : '.$ref_no[0].'-'.$ref_no[1].'</a></td>';

                                            if(!empty($ref_no['vender_id'])){
                                                $where = ' and l_id='.$ref_no['vender_id'].'';
                                            }
                                            $ledger_name = $dbcon->query("select l_name from tbl_ledger where company_id=".$_SESSION['company_id'].$where)->fetch_object()->l_name;
                                            $str .='<td data-label="Particulars" style="text-align:left">'.$ledger_name.'</td>';
                                            $str .='<td data-label="Vch Type" style="text-align:center">Debit Note</td>';
                                            $str .='<td data-label="Vch No" style="text-align:center">
                                                <a style="color: inherit;" class="link_dash" href="'.ROOT.FINANCE_ROOT.'debitnoteedit/'.$ref_no["debitnote_id"].'" >
                                                '.$ref_no['debitnote_no'].'</a></td>';

                                        }else if($ref_no['table_name'] == 'tbl_sale_return'){
                                            
                                            $str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'salereturnedit/'.$ref_no[1].'">Sale Return No : '.$ref_no[0].'</a></td>';

                                        }else{
                                            $str .= '<td>-----</td>';
                                        }
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

