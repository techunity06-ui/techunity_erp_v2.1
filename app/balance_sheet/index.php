<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/balance_sheet_functions.php");
include_once("../../include/common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET) ;
		
if(strtolower($POST['mode']) == "load_balance_sheet") {
    $grand_total = 0.00;
    $start_date = date('Y-m-d',strtotime($_POST['start_date']));
    $end_date = date('Y-m-d',strtotime($_POST['end_date']));
//    echo '<pre>'; print_r($POST); exit;
//    $year = date('Y', strtotime($end_date));
//    $start_date = date($year.'-04-01',strtotime($end_date));
    $where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;
    $companyName = get_company_name($dbcon);
    
    //Assets variables
    $fixed_assets = get_fixed_assets($dbcon, $where_date);
    $current_assets = get_current_assets($dbcon, $start_date, $end_date);
    $investments_value = get_investments_value($dbcon, $where_date);
    $misc_expense_value = get_misc_expense_value($dbcon, $where_date);
    $suspence_account = get_suspence_account($dbcon, $where_date);
    //echo '<pre>';    print_r($current_assets); exit;
    
    // Liabilities Variables
    $capital_account = get_capital_account($dbcon, $start_date,$end_date);
    $loans_liability = get_loans($dbcon, $where_date);
    $current_liabilities = get_current_liabilities($dbcon, $start_date, $end_date);
    $suspence_account_value = $suspence_account['value'];
    $suspence_account_entries = $suspence_account['entries'];
    
    $capital_account_entries = $loans_entries = $current_liabilities_entries = '';
    
    $total_assets = $fixed_assets['value'] + $current_assets['value'] + $investments_value + $misc_expense_value;
    $total_liability = $capital_account['value'] + $loans_liability['value'] + $current_liabilities['value'] + $suspence_account_value;
    
    $total_assets = number_format((float)$total_assets, 2, '.', '');
    $total_liability = number_format((float)$total_liability, 2, '.', '');
    
    /*if(strtolower($POST['show_details']) == "true") {
    
        // Assets Entries 
//        $fixed_assets_entries = get_fixed_assets_entries($dbcon, $where_date);
//        $current_assets_entries = get_current_assets_entries($dbcon, $where_date);
//        $investments_entries = get_investments_entries($dbcon, $where_date);
//        $misc_expense_entries = get_misc_expense_entries($dbcon, $where_date);
//    
//        // Liability Entries
//        $capital_account_entries = get_capital_account_entries($dbcon, $start_date);
//        $loans_entries = get_loans_entries($dbcon, $where_date);
//        $current_liabilities_entries = get_current_liabilities_entries($dbcon, $where_date);
//        $suspence_account_entries = get_suspence_account_entries($dbcon, $where_date);
    }*/
    
    $str='';
    $str .= '<strong>Note : Press Shift + v to see detail view</strong><br/>';
    $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
                <td colspan="4" style="font-size:20px;border-top:1px #101010 solid;border-left:1px #101010 solid;border-right:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                        <center>
                                <strong>'.$companyName.'</strong>
                        </center>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:20px;border-top:1px #101010 solid;border-left:1px #101010 solid;border-right:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                        <center>
                                <strong>Balance Sheet</strong>
                        </center>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;border-top:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                    <strong>Liabilities(cr)
                    <div style="float: right;">as at : '. date('d-M-y', strtotime($end_date)).'</div>
                    </strong>
                </td>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;border-top:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                    <strong>Assets(dr)
                    <div style="float: right;">as at : '. date('d-M-y', strtotime($end_date)).'</div>
                    </strong>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;vertical-align: top;">
                    <table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
                        <tr>
                                <td><strong>Capital Account</strong></td>
                                <td style="text-align: right;">'. indian_number($capital_account['value'],2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">'.$capital_account['entries'].'</td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>
                        <tr>
                                <td><strong>Loans (Liabilities)</strong></td>
                                <td style="text-align: right;">'. indian_number($loans_liability['value'],2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">'.$loans_liability['entries'].'</td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>';
                        $style = ($current_liabilities['value'] < 0) ? 'color: red;' : '';
                        $str .= '<tr>
                                <td><strong>Current Liabilities</strong></td>
                                <td style="text-align: right;'.$style.'">'. indian_number($current_liabilities['value'], 2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">
                                    '.$current_liabilities['entries'].'
                                </td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>
                        <tr>
                                <td><strong>Suspence Accounts</strong></td>
                                <td style="text-align: right;">'.indian_number($suspence_account_value,2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">
                                    '.$suspence_account_entries.'
                                </td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>';
                        
                    /*if($total_assets > $total_liability){
                        $grand_total = $total_assets;
                        $pl_value = $total_assets - $total_liability;
                        $str .= '<tr>
                                <td ><strong>Profit & Loss A/C<strong></td>
                                <td style="text-align: right;">'.number_format((float)$pl_value, 2, '.', '').'</td>
                        </tr>
                        ';
                    }*/
                $str .= '<tr style="display:none;" class="net_profit"></tr>
                        <tr height="20px">
                                <td></td>
                                <td style="text-align: right;"></td>
                        </tr>
                        ';
            $str.='</table>
                </td>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;vertical-align: top;">
                    <table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
                        <tr>
                                <td><strong>Fixed Assets<strong></td>
                                <td style="text-align: right;">'. indian_number($fixed_assets['value'], 2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">'.$fixed_assets['entries'].'</td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>
                        <tr>
                                <td><strong>Investments<strong></td>
                                <td style="text-align: right;">'.indian_number($investments_value,2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2" >
                                        '.$investments_entries.'
                                </td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>
                        <tr>
                                <td><strong>Current Assets<strong></td>
                                <td style="text-align: right;">'. indian_number($current_assets['value'], 2).'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">
                                        '.$current_assets['entries'].'
                                </td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>';
                    /*$str .= '<tr>
                                <td><strong>Miscellenous Expense<strong></td>
                                <td style="text-align: right;">'.$misc_expense_value.'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">
                                        '.$misc_expense_entries.'
                                </td>
                        </tr>
                        <tr style="height: 10px;"><td colspan="2"></td></tr>
                        ';*/
                    /*if($total_liability > $total_assets ){
                        $grand_total = $total_liability;
                        $pl_value = $total_liability - $total_assets;
                        $str .= '
                            <tr>
                                    <td ><strong>Profit & Loss A/C<strong></td>
                                    <td style="text-align: right;">'.number_format((float)$pl_value, 2, '.', '').'</td>
                            </tr>
                            ';
                    }*/
                    $str .= '<tr style="display:none;" class="net_loss"></tr>
                            <tr height="20px">
                                    <td></td>
                                    <td style="text-align: right;"></td>
                            </tr>
                            ';
                    $str .= '</table>
                </td>
            </tr>';									
$str.='<tr height="20px">
            <td style="border-top: 1px #101010 solid;border-bottom: 1px  #101010 solid;border-left: 1px #101010 solid;color: #101010;"><strong>Total</strong></td>
            <td class="grand_total" style="text-align: right;border-top: 1px #101010 solid;border-bottom: 1px #101010 solid;border-right: 1px #101010 solid;color: #101010;">'.$total_liability.'</td>
            <td style="border-top: 1px #101010 solid;border-bottom: 1px #101010 solid;border-left: 1px #101010 solid;color: #101010;"><strong>Total</strong></td>
            <td class="grand_total" style="text-align: right;border-top: 1px #101010 solid;border-bottom: 1px #101010 solid;border-right: 1px #101010 solid;color: #101010;">'.$total_assets.'</td>
        </tr>';
						
$str.='</table>';
$str .= '<input type="hidden" value="'.$total_liability.'" id="total_liability" />';
$str .= '<input type="hidden" value="'.$total_assets.'" id="total_assets" />';				
echo $str;
}