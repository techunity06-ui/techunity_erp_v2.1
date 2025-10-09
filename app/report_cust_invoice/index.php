
<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");
//include("../../config/image.php");

if($_POST != NULL) {
        $POST = bulk_filter($dbcon,$_POST);
}
else {
        $POST = bulk_filter($dbcon,$_GET);
}
		
if(strtolower($POST['mode']) == "generate_report") 
{
        $s_date = explode(' - ',$POST['date']);
        $_SESSION['start'] = $s_date[0];
        $_SESSION['end'] = $s_date[1];
        $set = "select * from tbl_company where company_id=".$_SESSION['company_id'];
        $set_head = brp_mysqli_fetch_assoc($dbcon->query($set));		
        $qrycust = "select * from tbl_ledger where l_id=".$POST['cust_id'];
        $cust_rel = brp_mysqli_fetch_assoc($dbcon->query($qrycust));	

        if(strtolower($POST['bill_type']) == "bill_to_bill") {
                $str .='<table  class="display table table-bordered table-striped" id="data_list">
                                <tr id="logo" class="logo" >
                                        <td colspan="8" style="text-align:center;">
                                                <strong>'.$set_head['company_name'].'</strong>
                                        </td>
                                </tr>
                                <tr>
                                        <td colspan="3" style="text-align:center"><strong>	Name:'.$cust_rel['l_name'].'
                                        </strong></td>
                                        <td colspan="2" style="text-align:right">Date
                                        <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
                                </tr>';

                            $str .='<tr>
                                        <th width="15%" style="text-align:center">Date</th>
                                        <th width="40%" style="text-align:center">Description</th>
                                        <th width="15%" style="text-align:center">Debit Amount</th>
                                        <th width="15%" style="text-align:center">Credit Amount</th>
                                        <th width="15%" style="text-align:center">Balance</th>
                                </tr>
                                <tbody>';

                                $query="select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
                               left join 
                               (select sum(amount) as debitamount,invoice.ledger_id 
                               from tbl_general_book as invoice 
                               where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 

                               left join 
                               (select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
                                       where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 

                               where cust.l_id=".$POST['cust_id'];


                    $rel=mysqli_fetch_assoc($dbcon->query($query));
                    $op_balance=($rel['balance_typeid']=="2"?($rel['opening_balance']):-$rel['opening_balance']);
                    $balance=$op_balance+$rel['debitamount']-($rel['creditamount']);
                    $balancetype='';
                    $str .='<tr>
                        <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
                        <td data-label="DESCRIPTION" style="text-align:center">Opening Balance</td>
                        <td data-label="DEBIT AMOUNT" style="text-align:center">- </td>
                        <td data-label="CREDIT AMOUNT" style="text-align:center"> -</td>';
                        if($balance>0)
                        {
                                $balancetype='DR';
                                $str .='
                          <!--<td data-label="BALANCE" style="text-align:right;color:red;">'.abs($balance).' '.$balancetype.'</td>-->
                          <td data-label="BALANCE" style="text-align:right;color:red;">'.number_format(abs($balance),2,".",",").' '.$balancetype.'</td>';
                        }
                        else if($balance<0)
                        {
                                        $balancetype='CR';
                                        $str .='
                          <td data-label="BALANCE" style="text-align:right;color:green;">'.number_format(abs($balance),2,".",",").' '.$balancetype.'</td>';
                        }else{
                                $str .='
                          <td data-label="BALANCE" style="text-align:center;color:green;">-</td>';
                        }

                        $str .='
                        </tr>';

        $qry='select payment.*,total_amount from tbl_general_book as payment 
            LEFT JOIN tbl_receipt_trn as trn ON trn.receipt_id = payment.table_id
                where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                    and ref_date>="'.date('Y-m-d',strtotime($s_date[0])).'" 
                    and ref_date<="'.date('Y-m-d',strtotime($s_date[1])).'" 
                    and table_name!="tbl_ledger" and (table_name="tbl_invoice" or table_name="tbl_payment") and payment.ledger_id='.$POST['cust_id'].' 
                ORDER BY payment.cdate';
        $result1=$dbcon->query($qry);
        $i=1;

        if(brp_mysqli_num_rows($result1)>0)
                {
                        $total=0;
                        while($re = brp_mysqli_fetch_assoc($result1))
                        {
                                $balancetype='';
                                $str.='<tr>
                                        <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($re["ref_date"])).'</td>';
                                                $ref_no = load_ledger_detail($dbcon,$re['table_name'],$re['table_id']);
                                                if($re['table_name']=="tbl_invoice")
                                                {
                                                        $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.' Invoice No : 
                                                            <a style="color: inherit;" class="link_dash" href="invoiceedit/'.$ref_no["invoice_id"].'" target="_blank">
                                                            '.$ref_no['invoice_no'].'</a></td>';
                                                        //$str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                                }
                                                else if($re['table_name']=="tbl_purchase"){
                                                        $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Purchace No : '.$ref_no['po_no'].'</td>';
                                                        $str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                                }
                                                else if($re['table_name']=="tbl_payment"){
                                                        if($re['entry_type']=="1"){
                                                                $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Payment No : 
                                                                    <a style="color: inherit;" class="link_dash" href="receipt_sales/'.$ref_no["receipt_id"].'" target="_blank">
                                                                    '.$ref_no['receipt_no'].'</a></td>';
                                                        }else{
                                                                $str .='<td data-label="DESCRIPTION" style="text-align:center">
                                                                    <a style="color: inherit;" class="link_dash" href="receipt_sales/'.$ref_no["receipt_id"].'" target="_blank">
                                                                    '.$demo.'Recipt No : '.$ref_no['receipt_no'].'</td>';
                                                        }
                                                        //$str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                                }
                                                else if($re['table_name']=="tbl_journal_trn"){
                                                        $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Journal No : '.$ref_no['journal_no'].'</td>';
                                                        $str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                                }
                                                else if($re['table_name']=="tbl_contra_trn"){
                                                        $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Contra No : '.$ref_no['contra_no'].'</td>';
                                                        $str .='<td data-label="LEDGER" style="text-align:center">'.$ref_no['ledger_name'].'</td>';
                                                }
                                                else{
                                                        $str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
                                                        $str .='<td data-label="LEDGER" style="text-align:center">-</td>';
                                                }

                                if($re['entry_type']==2){
                                 $str.='
                                  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.number_format(abs($re['amount']),2,".",",").'</td>
                                  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';

                                        $balance+=$re['amount'];

                                }else{
                                        $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
                                        <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.number_format(abs($re['total_amount']),2,".",",").'</td>';
                                        $balance-=$re['total_amount'];
                                }
//                                if($balance<0){
//                                $str.='
//                                 <td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.number_format(abs($balance),2,".",",").' CR</td>';
//                                }else if($balance>0){
//                                        $str.='
//                                        <td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.number_format(abs($balance),2,".",",").' DR</td>';
//                                }else{
//                                        $str.='
//                                 <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
//                                }
                                $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>
                                    </tr>';		
                                $i++;

                        }
                        $str.='<tr>';
                        if($balance<0){
                            $str.='<td colspan="5" data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.number_format(abs($balance),2,".",",").' CR</td>';
                        }else if($balance>0){
                            $str.='<td colspan="5" data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.number_format(abs($balance),2,".",",").' DR</td>';
                        }else{
                            $str.='<td colspan="5" data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
                        }
                        $str.='</tr>';

                }
                else
                {
                        $str .='<tr>
                                        <td colspan="10" style="text-align:center">NO DATA FOUND  </td>
                                        </tr>';

                }
                $str .='</tbody>				 
                  </table>'; 
        }

        if(strtolower($POST['bill_type']) == "on_account"){
            $str .='<table  class="display table table-bordered table-striped" id="data_list">
                        <tr id="logo" class="logo" >
                                <td colspan="8" style="text-align:center;">
                                        <strong>'.$set_head['company_name'].'</strong>
                                </td>
                        </tr>
                        <tr>
                                <td colspan="4" style="text-align:center"><strong>	Name:'.$cust_rel['l_name'].'
                                </strong></td>
                                <td colspan="2" style="text-align:right">Date
                                <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
                        </tr>';

                $str .='<tr>
                            <th width="15%" style="text-align:center">Date</th>
                            <th width="25%" style="text-align:center">Description</th>
                            <th width="15%" style="text-align:center">Type</th>
                            <th width="15%" style="text-align:center">Debit Amount</th>
                            <th width="15%" style="text-align:center">Credit Amount</th>
                            <th width="15%" style="text-align:center">Balance</th>
                        </tr>
                        <tbody>';

                $query="select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
                        left join 
                        (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                        left join 
                        (select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
                        where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($s_date[0]))."' group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 
                        where cust.l_id=".$POST['cust_id'];
                $rel = brp_mysqli_fetch_assoc($dbcon->query($query));
                $op_balance = ($rel['balance_typeid']=="2"?($rel['opening_balance']):-$rel['opening_balance']);
                $balance = $op_balance+$rel['debitamount']-($rel['creditamount']);
                $balancetype = '';
                $str .= '<tr>
                        <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
                        <td data-label="DESCRIPTION" style="text-align:center">Opening Balance</td>
                        <td data-label="LEDGER" style="text-align:center">- </td>
                        <td data-label="DEBIT AMOUNT" style="text-align:center">- </td>
                        <td data-label="CREDIT AMOUNT" style="text-align:center"> -</td>';
                        if($balance>0)
                        {
                            $balancetype='DR';
                            $str .='<td data-label="BALANCE" style="text-align:right;color:red;">'.number_format(abs($balance),2,".",",").' '.$balancetype.'</td>';
                        }
                        else if($balance<0)
                        {
                            $balancetype='CR';
                            $str .='<td data-label="BALANCE" style="text-align:right;color:green;">'.number_format(abs($balance),2,".",",").' '.$balancetype.'</td>';
                        }
                        else{
                            $str .='<td data-label="BALANCE" style="text-align:center;color:green;">-</td>';
                        }

                        $str .='</tr>';

        $qry='select * from tbl_general_book as payment
                where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                    and ref_date>="'.date('Y-m-d',strtotime($s_date[0])).'" 
                    and ref_date<="'.date('Y-m-d',strtotime($s_date[1])).'" 
                    and table_name!="tbl_ledger" and (table_name="tbl_invoice" or table_name="tbl_payment") and payment.ledger_id='.$POST['cust_id'].' 
                ORDER BY payment.cdate';
        $result1=$dbcon->query($qry);
        $i=1;

        if(brp_mysqli_num_rows($result1)>0){
            $total=0;
            while($re=brp_mysqli_fetch_assoc($result1))
            {
                $balancetype='';
                $str.='<tr>
                        <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($re["ref_date"])).'</td>';
                        $ref_no = load_ledger_detail($dbcon,$re['table_name'],$re['table_id']);
                        if($re['table_name']=="tbl_invoice")
                        {
                            $str .='<td data-label="DESCRIPTION" style="text-align:center">
                                <a style="color: inherit;" class="link_dash" href="invoiceedit/'.$ref_no["invoice_id"].'" target="_blank">'.$ref_no['invoice_no'].'</a></td>';
                            $str .='<td data-label="LEDGER" style="text-align:center">Sales</td>';
                            if($re['entry_type']=="1"){
                                $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
                                    <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.number_format(abs($re['amount']),2,".",",").'</td>';
                                    $balance -= $re['amount'];
                            } else {
                                $str.='<td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.number_format(abs($re['amount']),2,".",",").'</td>
                                    <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
                                    $balance += $re['amount'];
                            }
                                $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
                        }
                        else if($re['table_name']=="tbl_payment"){
                            if($re['entry_type']=="1"){
                                    $str .='<td data-label="DESCRIPTION" style="text-align:center">
                                        <a style="color: inherit;" class="link_dash" href="receipt_sales/'.$ref_no["receipt_id"].'" target="_blank">'.$ref_no['receipt_no'].'</a></td>';
                                    $str .='<td data-label="LEDGER" style="text-align:center">Receipt</td>';
                                    $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
                                    <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.number_format(abs($re['amount']),2,".",",").'</td>';
                                    $balance -= $re['amount'];
                            }else{
                                    $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$ref_no['receipt_no'].'</td>';
                                    $str .='<td data-label="LEDGER" style="text-align:center">Receipt</td>';
                                    $str.='<td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.number_format(abs($re['amount']),2,".",",").'</td>
                                    <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
                                    $balance += $re['amount'];
                            }
                            if($balance<0){
                                $str.='<td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.number_format(abs($balance),2,".",",").' CR</td>';
                            }else if($balance>0){
                                $str.='<td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.number_format(abs($balance),2,".",",").' DR</td>';
                            }else{
                                $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
                            }
                        }
                        else{
                            $str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
                            $str .='<td data-label="LEDGER" style="text-align:center">-</td>';
                        }
                    $str .= '</tr>';		
                    $i++;
            }
            $str.='<tr>';
            if($balance<0){
                $str.='<td colspan="6" data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.number_format(abs($balance),2,".",",").' CR</td>';
            }else if($balance>0){
                $str.='<td colspan="6" data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.number_format(abs($balance),2,".",",").' DR</td>';
            }else{
                $str.='<td colspan="6" data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
            }
            $str.='</tr>';

        }
        else
        {
                $str .='<tr>
                            <td colspan="10" style="text-align:center">NO DATA FOUND  </td>
                        </tr>';

        }
        $str .='</tbody>				 
                </table>'; 
    }

        if(strtolower($POST['bill_type']) == "bill_due_wise"){
            $str .='<table  class="display table table-bordered table-striped" id="data_list">
                        <tr id="logo" class="logo" >
                                <td colspan="5" style="text-align:center;">
                                        <strong>'.$set_head['company_name'].'</strong>
                                </td>
                        </tr>
                        <tr>
                                <td colspan="3" style="text-align:center"><strong>	Name:'.$cust_rel['l_name'].'
                                </strong></td>
                                <td colspan="2" style="text-align:right">Date
                                <label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
                        </tr>';
            $str .='<tr>
                        <th style="text-align:center">Date</th>
                        <th style="text-align:center">Invoice No</th>
                        <th style="text-align:center">Amount</th>
                        <th style="text-align:center">Due Date</th>
                        <th style="text-align:center">Due Days</th>
                    </tr>
                    <tbody>';
            
            $invoice_qry = 'Select * from ( 
                (select "Invoice" as type,1 as ref_type,invoice_date as ref_date,invoice_no as ref_no,invoice_id as ref_id,g_total as ref_amount, 
                (select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and inv.invoice_id=trn.invoice_id) as pay_amount, 
                inv.cdate from tbl_invoice as inv 
                where inv.invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" 
                    and inv.invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'" 
                    and invoice_status=0 AND cust_id='.$POST['cust_id'].' 
                and inv.g_total>(select IFNULL(sum(total_amount),0) as qty from tbl_receipt_trn as trn where status=0 and inv.invoice_id=trn.invoice_id)) ) as data 
                order by ref_date,ref_type DESC';
            $result = brp_mysqli_query($dbcon,$invoice_qry);
            $invoice_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
            //p($cust_rel);
            
            $payment_terms = $cust_rel['pay_terms'];
            
            if($invoice_data){
                foreach ($invoice_data as $i => $invoice) {
                    $invoice_date = date('d-m-Y', strtotime($invoice['ref_date']));
                    $today = date('d-m-Y');
                    if($payment_terms){
                        $due_date = date('d-m-Y', strtotime($invoice_date. ' + '.$payment_terms.' days'));
                        $due_days = date_diff(date_create($due_date), date_create($today))->format("%R%a");
                    } else {
                        $due_date = $invoice_date;
                        $due_days = date_diff(date_create($invoice_date), date_create($today))->format("%R%a");
                    }
                    $str .='<tr>
                            <td style="text-align:center">'.$invoice_date.'</td>
                            <td style="text-align:center">
                            <a style="color: inherit;" class="link_dash" href="invoiceedit/'.$invoice["ref_id"].'" target="_blank">
                            '.$invoice['ref_no'].'</a></td>
                            <td style="text-align:center">'.indian_number($invoice['ref_amount'],2).'</td>
                            <td style="text-align:center">'.$due_date.'</td>
                            <td style="text-align:center">'.(($due_days > 0) ? abs($due_days) : '0').'</td>
                        </tr>';
                }
            } else {
                $str .= '<tr>
                        <td colspan="5" style="text-align:center">No Due Bills.. </td>
                    </tr>';
            }
        }
        echo $str;
}
if(strtolower($POST['mode']) == "load_customer"){
    $cust_type = $POST['cust_type'];
    $where = '';
    if($cust_type == '1'){
        $where = ' and l_group IN ('.SUNDRY_CREDITORS.')';
    } 
    if($cust_type == '2'){
        $where = ' and l_group IN ('.SUNDRY_DEBTORS.')';
    }
    echo get_ledger($dbcon,'',$where);
}
   
?>