<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function get_company_name($dbcon){
    $companyName = $dbcon->query("SELECT company_name FROM tbl_company as comp WHERE company_id=".$_SESSION['company_id'])
            ->fetch_object()->company_name;
    return $companyName;
}
/*
 *  Assets Function - to fetch values 
 */
function get_fixed_assets($dbcon, $where_date){
    $fa_query = "SELECT gb.ledger_id,led.l_name,led.l_id,sum(gb.amount) as amount
        FROM `tbl_general_book` gb 
        LEFT join tbl_ledger as led ON led.l_id= gb.ledger_id 
        LEFT join tbl_group as gro ON gro.g_id=led.l_group 
        WHERE led.l_status = ".ACTIVE."
            AND gb.entry_type = ".DEBIT."
            AND gb.genral_book_status = ".ACTIVE."
            AND gb.ref_date ".$where_date."
            AND led.`l_group` = ".FIXED_ASSETS."
            AND led.company_id = ".$_SESSION['company_id']."
        GROUP BY gb.ledger_id";
    $fa_entries_res = $dbcon->query($fa_query);
    
    $fixed_assets_value = 0;
    $str = '';
    if($fa_entries_res){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;width:80%" cellpadding="0" cellspacing="0" >';
        while($fa_entry = mysqli_fetch_assoc($fa_entries_res)){
                $amount = number_format((float)$fa_entry["amount"], 2, '.', '');
                if($amount > 0){
                    $str.='
                        <tr>
                            <td><a style="color: inherit;" href="ledger_detail_view.php?ledger_id='.$fa_entry["l_id"].'" target="_blank">'.$fa_entry["l_name"].'</a></td>
                            <td style="text-align: right;">'.indian_number($amount,2).'</td>
                        </tr>
                    ';
                    $fixed_assets_value = $fixed_assets_value + $amount;
                }

        }
        $str .= "</table>";
    }
    $fixed_assets['entries'] = $str;
    $fixed_assets['value'] = $fixed_assets_value;
    return $fixed_assets;
}

function get_current_assets($dbcon, $start_date, $end_date){
    //get all groups under current assets
    $ca_sub_qry = "SELECT g_id AS ca_sub_group FROM `tbl_group` WHERE g_status = 0 And `g_pid`= ".CURRENT_ASSETS;
    $result = mysqli_query($dbcon,$ca_sub_qry);
    $ca_sub_groups = mysqli_fetch_all($result,MYSQLI_ASSOC);
    
    //$ca_sub_groups = get_sub_group($dbcon, CURRENT_ASSETS);
    $ca_entries = array();
    foreach ($ca_sub_groups as $ca_sub_group) {
        $sub_group_id = $ca_sub_group['ca_sub_group'];
        if($sub_group_id){
            $sub_groups = implode(',',get_sub_group($dbcon, $sub_group_id));
            $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE l_status = ".ACTIVE." AND l_group IN (".$sub_groups.")";
            $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
//            
            $ca_qry = "select sum(opn_balance) as opening_balance,balance_typeid,
                sum(debitamount) as debitamount ,sum(creditamount) as creditamount,
                (SELECT g_name FROM `tbl_group` WHERE `g_id` = ".$sub_group_id.") as group_name
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".DEBIT." and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= ".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".CREDIT." and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = ".ACTIVE." AND company_id = ".$_SESSION['company_id']." 
                    AND cust.l_id IN (".$sub_ledger.")
                ";
        
                $result = mysqli_query($dbcon, $ca_qry);
                $ca_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>';        print_r($ca_result);
                if($ca_result){
                    foreach ($ca_result as $value) {
                        //$balance_type = $value['balance_typeid'];
                        $balance_type = ($sub_group_id == SUNDRY_DEBTORS) ? '2' : $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount']-$value['creditamount']);
                        
                        $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
				where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                    and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                                    and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                                    and table_name!="tbl_ledger" and payment.ledger_id IN ('.$sub_ledger.') 
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
    }
    $ca_value = 0;
    $str = '';
    if($ca_entries && !empty($ca_entries)){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;width:80%" cellpadding="0" cellspacing="0">';
            foreach ($ca_entries as $ca_entry) {
                
                $amount = number_format((float)$ca_entry["ca_value"], 2, '.', '');
                $style = ($amount < 0) ? 'style="color: red;"' : '';
                if($amount > 0){
                    $str.='
                        <tr>
                            <td><a style="color: inherit;" href="group_detail_view.php?group_id='.$ca_entry["group_id"].'" target="_blank">'.$ca_entry["group_name"].'</a></td>
                            <td style="text-align: right;" '.$style.'>'.indian_number($amount,2).'</td>
                        </tr>
                    ';
                    $ca_value = $ca_value + $amount;
                }
            }
        $str .= "</table>";
    }
    $current_assets['entries'] = $str;
    $current_assets['value'] = $ca_value;
    return $current_assets;
}
function get_current_assets_value($dbcon, $where_date){
    $current_assets_value = 0.00;
    $ca_group_entry = get_current_assets($dbcon, $where_date);
    
    if($ca_group_entry && !empty($ca_group_entry)){
        foreach ($ca_group_entry as $ca_entry) {
            $current_assets_value += $ca_entry["ca_value"];
        }
    }

    return number_format((float)$current_assets_value, 2, '.', '');
}

function get_investments_value($dbcon, $where_date){
    $investments_value = 0.00;
    return number_format((float)$investments_value, 2, '.', '');
}

function get_misc_expense_value($dbcon, $where_date){
    $misc_expense_qry = "SELECT SUM(cgen.amount) as misc_expense_value
        FROM tbl_general_book as cgen 
        LEFT JOIN tbl_journal_trn as jrt ON jrt.journal_trn_id=cgen.table_id 
        LEFT JOIN tbl_journal as jou ON jou.journal_id=jrt.journal_id 
        LEFT JOIN tbl_ledger as led ON led.l_id=cgen.ledger_id 
        LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
        WHERE journal_trn_status= ".ACTIVE."
            AND gro.g_id = ".MISC_EXPENSES_ASSET."
            AND jrt.entry_type = ".DEBIT." 
            AND jou.journal_date".$where_date;
    $misc_expense_value = $dbcon->query($misc_expense_qry)->fetch_object()->misc_expense_value;
    
    if(!$misc_expense_value)
        $misc_expense_value = 0.00;
    
    return number_format((float)$misc_expense_value, 2, '.', '');
}

/*
 *  Assets Function - to fetch entries 
 */

function get_fixed_assets_entries($dbcon, $where_date){
    /*$query = "SELECT GROUP_CONCAT(ledgerids.l_id) as leger_id 
                FROM (SELECT led.l_id FROM tbl_potrancation as pot 
                LEFT join tbl_product as pro on pro.product_id = pot.product_id 
                LEFT join tbl_pono as po on po.po_id = pot.po_id 
                LEFT join tbl_ledger as led on led.l_id= po.purchase_ledger_id 
                LEFT join tbl_group as gro on gro.g_id=led.l_group 
                WHERE potrancation_status=0 
                        AND gro.g_id=18 
                        AND po.po_date between '2019-07-22' and '2020-07-31' 
                        AND pro.product_type!=3 
                group by led.l_id 
                order by led.l_id) as ledgerids";
    $fa_ledgerids = $dbcon->query($query)->fetch_object()->leger_id;*/
    
    $fa_query = "SELECT SUM(pot.product_amount) as total_amount,l_name 
                FROM tbl_potrancation as pot 
                LEFT join tbl_product as pro ON pro.product_id = pot.product_id 
                LEFT join tbl_pono as po ON po.po_id = pot.po_id 
                LEFT join tbl_ledger as led ON led.l_id= po.purchase_ledger_id 
                LEFT join tbl_group as gro ON gro.g_id=led.l_group 
                WHERE potrancation_status= ".ACTIVE." 
                    AND gro.g_id = ".FIXED_ASSETS." 
                    AND po.po_date ".$where_date." 
                    AND pro.product_type != ".CHARGES." 
                GROUP BY led.l_id 
                ORDER BY led.l_id";
    //echo $fa_query;
    $fa_entries_res = $dbcon->query($fa_query);
    
    $str = '';
    if($fa_entries_res){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($fa_entry = mysqli_fetch_assoc($fa_entries_res)){
                $amount = $fa_entry["total_amount"];
                $str.='
                    <tr>
                        <td>'.$fa_entry["l_name"].'</td>
                        <td>'.number_format((float)$amount, 2, '.', '').'</td>
                    </tr>
                ';

        }
        $str .= "</table>";
    }
    return $str;
}


function get_current_assets_entries($dbcon, $where_date){
    $ca_entries = get_current_assets($dbcon, $where_date);
    
    $str = '';
    if($ca_entries && !empty($ca_entries)){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
            foreach ($ca_entries as $ca_entry) {
                
                $amount = $ca_entry["ca_value"];
                $style = ($amount < 0) ? 'style="color: red;"' : '';
                if($amount){
                    $str.='
                        <tr>
                            <td>'.$ca_entry["group_name"].'</td>
                            <td '.$style.'>'.number_format((float)$amount, 2, '.', '').'</td>
                        </tr>
                    ';
                }
            }
        $str .= "</table>";
    }
    return $str;
}

function get_investments_entries($dbcon, $where_date){
    return '';
}

function get_misc_expense_entries($dbcon, $where_date){
    $misc_expense_qry = "SELECT SUM(cgen.amount) as misc_expense_value, l_name
        FROM tbl_general_book as cgen 
        LEFT JOIN tbl_journal_trn as jrt ON jrt.journal_trn_id=cgen.table_id 
        LEFT JOIN tbl_journal as jou ON jou.journal_id=jrt.journal_id 
        LEFT JOIN tbl_ledger as led ON led.l_id=cgen.ledger_id 
        LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
        WHERE journal_trn_status= ".ACTIVE."
            AND gro.g_id = ".MISC_EXPENSES_ASSET."
            AND jrt.entry_type = ".DEBIT." 
            AND jou.journal_date".$where_date."
        GROUP BY led.l_id 
        ORDER BY led.l_id";
    $misc_expense_res = $dbcon->query($misc_expense_qry);
    
    $str = '';
    if($misc_expense_res){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($misc_expense_entry = mysqli_fetch_assoc($misc_expense_res)){
                $amount = $misc_expense_entry["misc_expense_value"];
                if($amount > 0){
                    $str.='
                        <tr>
                            <td>'.$misc_expense_entry["l_name"].'</td>
                            <td>'.number_format((float)$amount, 2, '.', '').'</td>
                        </tr>
                    ';
                }

        }
        $str .= "</table>";
    }
    return $str;
}

/*
 *  Liabilities Function - to fetch values
 */

function get_capital_account($dbcon, $start_date){
    $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE `l_group` IN (".CAPITAL_ACCOUNT.")";
    $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
    
    $capital_ac_qry = "select sum(opn_balance) as opening_balance,balance_typeid,
                sum(debitamount) as debitamount ,sum(creditamount) as creditamount,
                l_name as ledger_name
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".DEBIT." and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= ".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".CREDIT." and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = ".ACTIVE." AND company_id = ".$_SESSION['company_id']." 
                    AND l_group IN (".CAPITAL_ACCOUNT.")
                    AND cust.l_id IN (".$sub_ledger.")";
    //echo $capital_ac_qry;
    $capital_account_res = mysqli_query($dbcon,$capital_ac_qry);
    $ca_result = mysqli_fetch_all($capital_account_res,MYSQLI_ASSOC);
    
    $ca_entries = array();
    if($ca_result){
        foreach ($ca_result as $value) {
            $balance_type = $value['balance_typeid'];
            $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
            $balance = $op_balance + ($value['debitamount']-$value['creditamount']);

            $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
                    where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                        and ref_date>="'.date('Y-m-d',strtotime($start_date)).'" 
                        and ref_date<="'.date('Y-m-d',strtotime($end_date)).'" 
                        and table_name!="tbl_ledger" and payment.ledger_id IN ('.$sub_ledger.') 
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
            //$ca_value['group_id'] = $sub_group_id;
            $ca_value['ledger_name'] = $value['ledger_name'];
            $ca_value['ca_value'] = abs($balance);
            array_push($ca_entries, $ca_value);
        }
    }
    $capital_account_value = 0;
    $str = '';
    if($ca_entries && !empty($ca_entries)){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;width:80%" cellpadding="0" cellspacing="0">';
            foreach ($ca_entries as $ca_entry) {
                
                $amount = number_format((float)$ca_entry["ca_value"], 2, '.', '');
                $style = ($amount < 0) ? 'style="color: red;"' : '';
                if($amount > 0){
                    $str.='
                        <tr>
                            <td>'.$ca_entry["ledger_name"].'</td>
                            <td style="text-align: right;" '.$style.'>'.number_format($amount,2).'</td>
                        </tr>
                    ';
                    $capital_account_value = $capital_account_value + $amount;
                }
            }
        $str .= "</table>";
    }
    $capital_account['entries'] = $str;
    $capital_account['value'] = $capital_account_value;
    return $capital_account;
}

function get_loans($dbcon, $where_date){
    $loan_sub_qry = "SELECT g_id AS ca_sub_group FROM `tbl_group` WHERE `g_pid`= ".LOANS_LIABILITY." OR g_id=".LOANS_LIABILITY;
    $result = mysqli_query($dbcon,$loan_sub_qry);
    $loan_sub_groups = mysqli_fetch_all($result,MYSQLI_ASSOC);
    
    $loans_entries = array();
    foreach ($loan_sub_groups as $loan_sub_group) {
        $sub_group_id = $loan_sub_group['ca_sub_group'];
        if($sub_group_id){
            $ledger = $dbcon->query("SELECT group_concat(l_id) as ledger FROM `tbl_ledger` WHERE `l_group` = ".$sub_group_id)
                        ->fetch_object()->ledger;
                $loan_qry = "select sum(opn_balance) as opening_balance,balance_typeid,
                sum(debitamount) as debitamount ,sum(creditamount) as creditamount,
                l_name as ledger_name,
                l_id as ledger_id
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".DEBIT." and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date ".$where_date." 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= ".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".CREDIT." and company_id=".$_SESSION['company_id']."
                            and ref_date ".$where_date." 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = ".ACTIVE." AND company_id = ".$_SESSION['company_id']." 
                    AND l_group IN (".$sub_group_id.")
                    AND cust.l_id IN (".$ledger.")
                group by cust.l_id ";
                
                $result = mysqli_query($dbcon, $loan_qry);
                $loan_result = mysqli_fetch_all($result,MYSQLI_ASSOC);

                //echo '<pre>'; print_r($loan_result); exit;
                if($loan_result){
                    foreach ($loan_result as $value) {
                        $balance_type = ($sub_group_id == SUNDRY_DEBTORS) ? '2' : $value['balance_typeid'];
                        $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                        $balance = $op_balance + ($value['debitamount']-$value['creditamount']);

                        $loan['ledger_id'] =  $value['ledger_id'];
                        $loan['ledger_name'] = $value['ledger_name'];
                        $loan['loan_value'] = abs($balance);
                        array_push($loans_entries, $loan);
                    }
                }
        }
    }
    $loan_value = 0;
    $str = '';
    if($loans_entries && !empty($loans_entries)){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;width:80%" cellpadding="0" cellspacing="0">';
            foreach ($loans_entries as $loans_entry) {
                
                $amount = number_format((float)$loans_entry["loan_value"], 2, '.', '');
                $style = ($amount < 0) ? 'style="color: red;"' : '';
                if($amount > 0){
                    $str.='
                        <tr>
                            <td><a style="color: inherit;" href="ledger_monthly_view.php?ledger_id='.$loans_entry["ledger_id"].'" target="_blank">'.$loans_entry["ledger_name"].'</a></td>
                            <td style="text-align: right;" '.$style.'>'. indian_number($amount,2).'</td>
                        </tr>
                    ';
                    $loan_value = $loan_value + $amount;
                }
            }
        $str .= "</table>";
    }
    $loans_liability['entries'] = $str;
    $loans_liability['value'] = $loan_value;
    return $loans_liability;
}

function get_loans_value($dbcon, $where_date){
    $loan_qry = "SELECT SUM(cgen.amount) as loan_value
        FROM tbl_general_book as cgen 
        LEFT join tbl_journal_trn as jrt ON jrt.journal_trn_id=cgen.table_id 
        LEFT join tbl_journal as jou ON jou.journal_id=jrt.journal_id 
        LEFT join tbl_ledger as led ON led.l_id=cgen.ledger_id 
        LEFT join tbl_group as gro ON gro.g_id=led.l_group 
        WHERE journal_trn_status= ".ACTIVE."
            AND gro.g_id = ".LOANS_LIABILITY."
            AND jrt.entry_type = ".DEBIT." 
            AND jou.journal_date".$where_date;
    $loans_value = $dbcon->query($loan_qry)->fetch_object()->loan_value;
    
    if(!$loans_value){
        $loans_value = 0.00;
    }
    return number_format((float)$loans_value, 2, '.', '');
}

function get_current_liabilities_value($dbcon, $where_date){
    $current_liabilities_value = 0.00;
    $cl_group_entry = get_current_liabilities($dbcon, $where_date);
    
    if($cl_group_entry && !empty($cl_group_entry)){
        foreach ($cl_group_entry as $cl_entry) {
            $current_liabilities_value += $cl_entry["cl_value"];
        }
    }
    return number_format((float)$current_liabilities_value, 2, '.', '');
}

function get_suspence_account_value($dbcon, $where_date){
    $suspence_account_value = 0.00;
    $suspence_ac_qry = "SELECT SUM(cgen.amount) as suspence_value
        FROM tbl_general_book as cgen 
        LEFT JOIN tbl_journal_trn as jrt ON jrt.journal_trn_id=cgen.table_id 
        LEFT JOIN tbl_journal as jou ON jou.journal_id=jrt.journal_id 
        LEFT JOIN tbl_ledger as led ON led.l_id=cgen.ledger_id 
        LEFT JOIN tbl_group as gro ON gro.g_id=led.l_group 
        WHERE journal_trn_status= ".ACTIVE."
            AND gro.g_id = ".SUSPENSE_ACCOUNTS."
            AND jrt.entry_type = ".CREDIT." 
            AND jou.journal_date".$where_date;
    $suspence_account_res = $dbcon->query($suspence_ac_qry);
    while($suspence_account_entry = mysqli_fetch_assoc($suspence_account_res)){
        $suspence_account_value = $suspence_account_entry["suspence_value"];
    }
    
    return number_format((float)$suspence_account_value, 2, '.', '');;
}

function get_pl_account_value($dbcon, $where_date){
    $pl_value = 0.00;
    return number_format((float)$pl_value, 2, '.', '');
}

/*
 *  Liabilities Function - to fetch entries
 */

function get_capital_account_entries($dbcon, $start_date){
    $capital_ac_qry = "SELECT SUM(cgen.amount)as capital_ac_value ,led.l_name 
	FROM tbl_general_book as cgen 
	LEFT JOIN tbl_journal_trn as jrt on jrt.journal_trn_id=cgen.table_id 
	LEFT JOIN tbl_journal as jou on jou.journal_id=jrt.journal_id 
	LEFT JOIN tbl_ledger as led on led.l_id=cgen.ledger_id 
	LEFT JOIN tbl_group as gro on gro.g_id=led.l_group 
	WHERE journal_trn_status = ".ACTIVE." 
            AND gro.g_id = ".CAPITAL_ACCOUNT." 
            AND jrt.entry_type = ".CREDIT." 
            AND jou.journal_date <= '".$start_date."'
	GROUP BY led.l_id 
	ORDER BY led.l_id";
    //echo '<br/>'.$capital_ac_qry;
    $capital_ac_res = $dbcon->query($capital_ac_qry);
    
    $str = '';
    $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
    while($capital_ac_entry = mysqli_fetch_assoc($capital_ac_res)){
        $amount = $capital_ac_entry["capital_ac_value"];
        $str.='
            <tr>
                <td>'.$capital_ac_entry["l_name"].'</td>
                <td>'.number_format((float)$amount, 2, '.', '').'</td>
            </tr>
        ';

    }
    $str .= "</table>";
    return $str;
}

function get_loans_entries($dbcon, $where_date){
    $loan_qry = "SELECT SUM(cgen.amount) as misc_expense_value, l_name
        FROM tbl_general_book as cgen 
        LEFT JOIN tbl_journal_trn as jrt on jrt.journal_trn_id=cgen.table_id 
        LEFT JOIN tbl_journal as jou on jou.journal_id=jrt.journal_id 
        LEFT JOIN tbl_ledger as led on led.l_id=cgen.ledger_id 
        LEFT JOIN tbl_group as gro on gro.g_id=led.l_group 
        WHERE journal_trn_status= ".ACTIVE."
            AND gro.g_id = ".LOANS_LIABILITY."
            AND jrt.entry_type = ".DEBIT." 
            AND jou.journal_date".$where_date."
        GROUP BY led.l_id 
        ORDER BY led.l_id";
    $loan_res = $dbcon->query($loan_qry);
    
    $str = '';
    if($loan_res){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($loan_entry = mysqli_fetch_assoc($loan_res)){
                $amount = $loan_entry["misc_expense_value"];
                $str.='
                    <tr>
                        <td>'.$loan_entry["l_name"].'</td>
                        <td>'.number_format((float)$amount, 2, '.', '').'</td>
                    </tr>
                ';

        }
        $str .= "</table>";
    }
    return $str;
}

function get_current_liabilities($dbcon, $start_date, $end_date){
    //get all groups under current liablities
    $cl_sub_qry = "SELECT g_id AS cl_sub_group FROM `tbl_group` WHERE g_status = 0 And `g_pid`= ".CURRENT_LIABILITIES;
    $result = mysqli_query($dbcon,$cl_sub_qry);
    $cl_sub_groups = mysqli_fetch_all($result,MYSQLI_ASSOC);
    
    $cl_entries = array();
    foreach ($cl_sub_groups as $cl_sub_group) {
        $sub_group_id = $cl_sub_group['cl_sub_group'];
        if($sub_group_id) {
            $sub_ledger_qry = "SELECT group_concat(l_id) as sub_ledger FROM `tbl_ledger` WHERE `l_group` IN (".$sub_group_id.")";
            $sub_ledger = $dbcon->query($sub_ledger_qry)->fetch_object()->sub_ledger;
            
            $cl_qry = "select sum(opn_balance) as opening_balance,balance_typeid,
                sum(debitamount) as debitamount ,sum(creditamount) as creditamount,
                (SELECT g_name FROM `tbl_group` WHERE `g_id` = ".$sub_group_id.") as group_name
                from tbl_ledger as cust 
                left join (select sum(amount) as debitamount,invoice.ledger_id 
                        from tbl_general_book as invoice 
                        where genral_book_status=".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".DEBIT." and invoice.company_id=".$_SESSION['company_id']." 
                            and ref_date < '".$start_date."' 
                        group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
                left join (select sum(amount) as creditamount,rec.ledger_id 
                        from tbl_general_book as rec 
                        where genral_book_status= ".ACTIVE." and table_name!='tbl_ledger' 
                            and entry_type= ".CREDIT." and company_id=".$_SESSION['company_id']."
                            and ref_date < '".$start_date."' 
                        group by rec.ledger_id) as creditcust on creditcust.ledger_id = cust.l_id 
                where l_status = ".ACTIVE." AND company_id = ".$_SESSION['company_id']." 
                    AND l_group IN (".$sub_group_id.")
                    AND cust.l_id IN (".$sub_ledger.")
                ";
            $cl_res = mysqli_query($dbcon,$cl_qry);
            $cl_result = mysqli_fetch_all($cl_res,MYSQLI_ASSOC);

            if($cl_result){
                foreach ($cl_result as $value) {
                    $balance_type = $value['balance_typeid'];
                    $op_balance = ($balance_type=="2" ? ($value['opening_balance']) :-$value['opening_balance']);
                    $balance = $op_balance + ($value['debitamount']-$value['creditamount']);

                    $payment_qry = 'select sum(amount) as amount, entry_type from tbl_general_book as payment
                            where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' 
                                and ref_date >= "'.date('Y-m-d',strtotime($start_date)).'" 
                                and ref_date <= "'.date('Y-m-d',strtotime($end_date)).'" 
                                and table_name!="tbl_ledger" and payment.ledger_id IN ('.$sub_ledger.') 
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
                    
                    $cl_value['group_id'] = $sub_group_id;
                    $cl_value['group_name'] = $value['group_name'];
                    $cl_value['cl_value'] = abs($balance);
                    array_push($cl_entries, $cl_value);
                }
            }
        }
    }
    $cl_value = 0;
    $str = '';
    if($cl_entries && !empty($cl_entries)){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;width:80%" cellpadding="0" cellspacing="0">';
            foreach ($cl_entries as $cl_entry) {
                
                $amount = number_format((float)$cl_entry["cl_value"], 2, '.', '');
                $style = ($amount < 0) ? 'style="color: red;"' : '';
                if($amount > 0){
                    $str.='
                        <tr>
                            <td><a style="color: inherit;" href="group_detail_view.php?group_id='.$cl_entry["group_id"].'" target="_blank">'.$cl_entry["group_name"].'</a></td>
                            <td style="text-align: right;" '.$style.'>'. number_format($amount,2).'</td>
                        </tr>
                    ';
                    $cl_value = $cl_value + $amount;
                }
            }
        $str .= "</table>";
    }
    $current_liabilities['entries'] = $str;
    $current_liabilities['value'] = $cl_value;
    return $current_liabilities;
}

function get_current_liabilities_entries($dbcon, $where_date){
    $cl_group_entry = get_current_liabilities($dbcon, $where_date);
    
    $str = '';
    if($cl_group_entry && !empty($cl_group_entry)){
        
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        foreach ($cl_group_entry as $cl_entry) {
            
            $amount = $cl_entry["cl_value"];
            $style = ($amount < 0) ? 'style="color: red;"' : '';
            if($amount){
                $str.='
                    <tr>
                        <td>'.$cl_entry["group_name"].'</td>
                        <td '.$style.'>'.number_format((float)$amount, 2, '.', '').'</td>
                    </tr>
                ';
            }
        }
        $str .= "</table>";
    }
    return $str;
}

function get_suspence_account_entries($dbcon, $where_date){
    $suspence_ac_qry = "SELECT SUM(cgen.amount) as suspence_value,led.l_name
        FROM tbl_general_book as cgen 
        LEFT JOIN tbl_journal_trn as jrt on jrt.journal_trn_id=cgen.table_id 
        LEFT JOIN tbl_journal as jou on jou.journal_id=jrt.journal_id 
        LEFT JOIN tbl_ledger as led on led.l_id=cgen.ledger_id 
        LEFT JOIN tbl_group as gro on gro.g_id=led.l_group 
        WHERE journal_trn_status= ".ACTIVE."
            AND gro.g_id = ".SUSPENSE_ACCOUNTS."
            AND jrt.entry_type = ".CREDIT." 
            AND jou.journal_date ".$where_date;
    $suspence_ac_res = $dbcon->query($suspence_ac_qry);
    
    $str = '';
    if(!empty($suspence_ac_res)){
        $str.= '<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
        while($suspence_ac_entry = mysqli_fetch_assoc($suspence_ac_res)){
            $amount = $suspence_ac_entry["suspence_value"];
            if($amount && ($amount>0)){
                $str.='
                    <tr>
                        <td>'.$suspence_ac_entry["l_name"].'</td>
                        <td>'.number_format((float)$amount, 2, '.', '').'</td>
                    </tr>
                ';
            }

        }
        $str .= "</table>";
    }
    return $str;
}

