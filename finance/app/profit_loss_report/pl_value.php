<?php

session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."profit_loss_functions.php");

	
if(strtolower($_POST['mode']) == "load_profit_loss") {

    $companyName = get_company_name($dbcon);
			
    //$s_date = explode(' - ',$_POST['date']);
    //$start_date = date('Y-m-d',strtotime($s_date[0]));
//    $end_date = date('Y-m-d',strtotime($s_date[1]));
//    $year = date('Y', strtotime($end_date));
//    $start_date = date($year.'-04-01',strtotime($end_date));
    
    $start_date = date('Y-m-d',strtotime($_POST['start_date']));
    $end_date = date('Y-m-d',strtotime($_POST['end_date']));
    $where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;
	
    $purchase_ac_value = purchase_ac_value($dbcon,$where_date);
    $sales_ac_value = sales_ac_value($dbcon,$where_date);
    $opening_stock_value = opening_stock_value($dbcon,$start_date);
    $direct_expance_value = direct_expance_value($dbcon,$where_date);
    $direct_income_value = direct_income_value($dbcon,$where_date);
    $indirect_expances = get_indirect_expenses($dbcon,$start_date,$end_date);
    $indirect_income_value = indirect_income_value($dbcon,$start_date,$end_date);
    $dierct_expences = get_direct_expences($dbcon,$start_date,$end_date);

    $closing_balance = (float)($purchase_ac_value - $sales_ac_value);
    
    //$closing_stock = number_format($closing_balance, 2, '.', '');
    $closing_stock = 0;
    $exp = $opening_stock_value + $purchase_ac_value + $direct_expance_value;
    $incom = $closing_stock + $sales_ac_value + $direct_income_value;
    $net_incom = $net_exp = $gross_profit = $gross_loss = $net_profit = $net_loss = 0;
    
    if($exp > $incom){
            $gross_loss = $exp - $incom;
            $total_exp = $exp;
            $total_income = $gross_loss + $incom;
            $net_exp = ($gross_loss + $indirect_expances['value']) - $indirect_income_value;
    } else {
            $gross_profit = $incom - $exp;
            $total_exp = $gross_profit + $exp;
            $total_income = $incom;
            $net_incom = ($gross_profit + $indirect_income_value) - $indirect_expances['value'];
    }
				
    if($net_exp>$net_incom){
            $net_loss = $net_exp - $net_incom;
            $gtotal_exp = $gross_loss + $indirect_expances['value'];
            $gtotal_profit = $gross_profit + $indirect_income_value + $net_loss;
    }else{
            $net_profit = $net_incom - $net_exp;
            $gtotal_exp = $gross_loss + $indirect_expances['value'] + $net_profit;
            $gtotal_profit = $gross_profit + $indirect_income_value;
    }
				
    $gross_profit = number_format((float)$gross_profit, 2, '.', '');
    $gross_loss = number_format((float)$gross_loss, 2, '.', '');
    $total_exp = number_format((float)$total_exp, 2, '.', '');
    $total_income = number_format((float)$total_income, 2, '.', '');
    $net_profit = number_format((float)$net_profit, 2, '.', '');
    $net_loss = number_format((float)$net_loss, 2, '.', '');
    $gtotal_exp = number_format((float)$gtotal_exp, 2, '.', '');
    $gtotal_profit = number_format((float)$gtotal_profit, 2, '.', '');
}
$data = array();
$data['net_profit'] = $net_profit;
$data['net_loss'] = $net_loss;

echo json_encode($data);
