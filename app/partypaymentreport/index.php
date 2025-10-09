<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/finance_common_functions.php");
include("../../config/image.php");

$date = get_current_financial_year();
extract($date);
$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
$image = new SimpleImage();
if(strtolower($POST['mode']) == "generate_report") {
        if(!empty($POST['ledger_id'])){
            foreach ($POST['ledger_id'] as $value) {
                $sub_ledger[]['ledger_id'] = $value;
            }
        } else {
            $sub_groups = implode(',',get_sub_group($dbcon, SUNDRY_DEBTORS));
            $sub_ledger_qry = "SELECT l_id as ledger_id FROM `tbl_ledger` WHERE l_status = 0 AND l_group IN (".$sub_groups.")";
            $result = brp_mysqli_query($dbcon, $sub_ledger_qry);
            $sub_ledger = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
        }
        $ca_entries = array();
        foreach ($sub_ledger as $ledger) {
                        
                    $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,sum(debitamount) as debitamount ,
                        sum(creditamount) as creditamount,l_name as ledger_name, l_id as ledger_id, cust_mobile as phon_no
                        from tbl_ledger as cust 
                        left join (select sum(amount) as debitamount,invoice.ledger_id 
                                from tbl_general_book as invoice 
                                where genral_book_status=0 and table_name!='tbl_ledger' 
                                    and entry_type= 2 and invoice.company_id=".$_SESSION['company_id']." 
                                    and ref_date < '".date('Y-m-d', strtotime($start_date))."' 
                                group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                        left join (select sum(amount) as creditamount,rec.ledger_id 
                                from tbl_general_book as rec 
                                where genral_book_status= 0 and table_name!='tbl_ledger' 
                                    and entry_type= 1 and company_id=".$_SESSION['company_id']."
                                    and ref_date < '".date('Y-m-d', strtotime($start_date))."' 
                                group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                        where l_status = 0 AND company_id = ".$_SESSION['company_id']." 
                            AND cust.l_id IN (".$ledger['ledger_id'].")
                            group by cust.l_id
                            Order by l_name ASC ";
        
                        $result = brp_mysqli_query($dbcon, $ca_qry);
                        $ca_result = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

                if($ca_result){
                    foreach ($ca_result as $value) {
                        $balance_type = $value['balance_typeid'];
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
                        $result = brp_mysqli_query($dbcon, $payment_qry);
                        $payment_result = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);
                        
                        if($payment_result){
                            foreach ($payment_result as $payment) {
                                $debit = $credit = 0;
                                if($payment['entry_type']==2){
                                    $balance += $payment['amount'];
                                    $ca_entries[$value['ledger_id']]['debit'] = $payment['amount'];

                                }else{
                                    $balance -= $payment['amount'];
                                    $ca_entries[$value['ledger_id']]['credit'] = $payment['amount'];
                                }
                        }
                        $ca_entries[$value['ledger_id']]['ledger_name'] = $value['ledger_name'];
                        $ca_entries[$value['ledger_id']]['phon_no'] = $value['phon_no'];
                        }
                    }
                }
            }
            $set="select * from tbl_company where company_id=".$_SESSION['company_id'];
            $set_head= brp_mysqli_fetch_assoc($dbcon->query($set));		
			
            $str .='<table  class="display table table-bordered table-striped" id="">
                    <thead class="resdisplay">
                        <tr id="logo">
                                <td class="noborder" colspan="6" style="text-align:center;">
                                <strong>'.$set_head['company_name'].'</strong></td>
                        </tr>
                        <tr>
                                <td colspan="4" class="noborder"><strong>Customer wise Outstanding Statement</strong></td>
                                <td class="noborder">Date :'.date('d/m/Y').'</td>
                        </tr>
                        <tr>
                          <th  rowspan="2" width="2%" style="text-align:center;vertical-align:top;">Sr. NO.</th>
                          <th rowspan="2" width="40%" style="text-align:center;vertical-align:top;">Party Name</th>
                          <th rowspan="2" width="10%" style="text-align:center;vertical-align:top;">Phone No.</th>
                          <th colspan="2" width="10%" style="text-align:center">Closing Balance</th>
                        </tr>
                        <tr>
                         <th width="10%" style="text-align:center">Debit</th>
                          <th width="10%" style="text-align:center">Credit</th>
                        </tr>
                    </thead>
                    <tbody>';
		if($ca_entries)	
                {
                        $total=0; $i = 1;
                        foreach ($ca_entries as $id => $value) {
                            $str.='<tr>
                                        <td data-label="SR. NO." style="text-align:center">'.$i.'</td>
                                        <td data-label="PARTY NAME" style="text-align:left">'.$value['ledger_name'].'</td>
                                        <td data-label="PHONE NO." style="text-align:left">'.$value['phon_no'].'</td>';
                            $str.='<td data-label="DEBIT" style="text-align:right">'. indian_number(abs($value['debit']),2).'</td>';
                            $debittotal+=abs($value['debit']);
                            $str.='<td style="text-align:right">'.indian_number(abs($value['credit']),2).'</td>';
                            $credittotal+=abs($value['credit']);
                            $str.='<tr>';
                            $i++;
                        }
                        $str.='<tr>
                                    <td style="text-align:right" colspan="3"><b>Total</b></td>
                                    <td style="text-align:right">'.indian_number($debittotal,2).'</td>
                                    <td data-label="credit" style="text-align:right">'.indian_number($credittotal,2).'</td>
                                </tr>';
                }
                else
                {
                        $str .='<tr>
                                <td colspan="6" style="text-align:center">NO DATA FOUND  </td>
                                </tr>';
							
                }
        $str .='</tbody>				 
            </table>';
				  
    echo $str;
}
?>