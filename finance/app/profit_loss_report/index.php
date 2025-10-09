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

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET); 
		
if(strtolower($POST['mode']) == "load_profit_loss") {
    $companyName = get_company_name($dbcon);
			
    $start_date = date('Y-m-d',strtotime($POST['start_date']));
    $end_date = date('Y-m-d',strtotime($POST['end_date']));

    $_SESSION['balance_sheet_start_date']= $start_date;
    $_SESSION['balance_sheet_end_date'] = $end_date;

    $where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;
		
    $indirect_expences = get_indirect_expenses($dbcon,$start_date,$end_date);
    $dierct_expences = get_direct_expences($dbcon, $where_date);
    //echo '<pre>';    print_r($dierct_expences); 
    $purchase_ac_value = purchase_ac_value($dbcon,$where_date);
    $sales_ac_value = sales_ac_value($dbcon,$where_date);
    $opening_stock_value = opening_stock_value($dbcon,$start_date);
    $direct_expance_value = direct_expance_value($dbcon,$where_date);
    $direct_income_value = direct_income_value($dbcon,$where_date);
    $indirect_expances_value = indirect_expances_value($dbcon,$where_date);
    $indirect_income_value = indirect_income_value($dbcon,$where_date);
	
	
    
	/*$inventory_management = $dbcon->query("SELECT inventory_management FROM tbl_finance_setting as comp WHERE company_id=".$_SESSION['company_id'])
                ->fetch_object()->inventory_management; */
    
	//echo $where_date;
	
   
    $closing_balance = (float)($purchase_ac_value - $sales_ac_value);
    
    $closing_stock = 0;
    $exp = $opening_stock_value + $purchase_ac_value + $dierct_expences['value'];
    $incom = $closing_stock + $sales_ac_value + $direct_income_value;
    $net_incom = $net_exp = $gross_profit = $gross_loss = $net_profit = $net_loss = 0;
    
    if($exp > $incom){
            $gross_loss = $exp - $incom;
            $total_exp = $exp;
            $total_income = $gross_loss + $incom;
            $net_exp = ($gross_loss + $indirect_expences['value']) - $indirect_income_value;
    } else {
            $gross_profit = $incom - $exp;
            $total_exp = $gross_profit + $exp;
            $total_income = $incom;
            $net_incom = ($gross_profit + $indirect_income_value) - $indirect_expences['value'];
    }
				
    if($net_exp>$net_incom){
            $net_loss = $net_exp - $net_incom;
            $gtotal_exp = $gross_loss + $indirect_expences['value'];
            $gtotal_profit = $gross_profit + $indirect_income_value + $net_loss;
    }else{
            $net_profit = $net_incom - $net_exp;
            $gtotal_exp = $gross_loss + $indirect_expences['value'] + $net_profit;
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
			
    $indirect_income_value_details = $indirect_expense_value_details = $direct_expance_value_details =
    $direct_income_value_details = $purchase_ac_value_details = $sales_ac_value_details = 
    $opening_stock_value_details = $closing_stock_value_details = '';
    
    if(strtolower($POST['show_details']) == "true") {
        $indirect_income_value_details = indirect_income_value_details($dbcon,$where_date);
        //$indirect_expense_value_details = get_indirect_expenses($dbcon,$start_date,$end_date); iclude thesae 3 fields in live time currently having errors 
        //$direct_expance_value_details = direct_expance_value_details($dbcon,$where_date);
        //$direct_income_value_details = direct_income_value_details($dbcon,$where_date);
        $purchase_ac_value_details = purchase_ac_value_details($dbcon,$where_date);
        $sales_ac_value_details = sales_ac_value_details($dbcon,$where_date);
        $opening_stock_value_details = opening_stock_value_details($dbcon,$start_date);
        $closing_stock_value_details = closing_stock_value_details($closing_stock);
    }
	//echo $where_date;			
    $str = '';
    $str .= '<strong>Note : Press Shift + v to see detail view</strong><br/>';
    $str.='<table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
            <tr>
                <td colspan="4" style="font-size:20px;border-top:1px #101010 solid;border-left:1px #101010 solid;border-right:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                    <center>
                            <strong>Profit And Loss</strong>
                    </center>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;">
                        <center><strong>'.$companyName.'</strong></center>
                </td>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;">
                        <center><strong>'.$companyName.'</strong></center>
                </td>
            </tr>
            <tr>
                <td style="font-size:15px;border-bottom:1px #101010 solid;border-left:1px #101010 solid;color:#251919;">
                        <strong>Particulars</strong>
                </td>
                <td style="font-size:15px;border-right:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                        <center>'.$companyName.'</center>
                </td>
                <td style="font-size:15px;border-left:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                        <strong>Particulars</strong>
                </td>
                <td style="font-size:15px;border-right:1px #101010 solid;border-bottom:1px #101010 solid;color:#251919;">
                        <center>'.$companyName.'</center>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;vertical-align: top;">
                    <table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
                        <tr>
                            <td><strong>Opening Stock</strong></td>
                            <td style="text-align: right;">'.number_format((float)$opening_stock_value, 2, '.', '').'</td>
                        </tr>
                        <tr class="descripc">
                            <td colspan="2">'.$opening_stock_value_details.'</td>
                        </tr>
                        <tr>
                            <td><strong>Purchase A/C.</strong></td>
                            <td style="text-align: right;">'.number_format((float)$purchase_ac_value, 2, '.', '').'</td>
                        </tr>
                        <tr class="descripc">
                            <td colspan="2">'.$purchase_ac_value_details.'</td>
                        </tr>
                        <tr>
                            <td><strong>Direct Expense</strong></td>
                            <td style="text-align: right;">'.number_format((float)$dierct_expences['value'], 2, '.', '').'</td>
                        </tr>
                        <tr class="descripc">
                            <td colspan="2">
                                    '.$dierct_expences['entries'].'
                            </td>
                        </tr>';
                        if(!empty($gross_profit) && $gross_profit!="0.00"){
                                $str.='<tr>
                                                <td ><strong>Gross Profit<strong></td>
                                                <td style="text-align: right;">'.$gross_profit.'</td>
                                        </tr>';
                        }else{
                                $str.='<tr height="20px">
                                                <td></td>
                                                <td style="text-align: right;"></td>
                                        </tr>';
                        }
                $str.='</table>
                </td>
                <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;vertical-align: top;">
                    <table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
                        <tr>
                                <td><strong>Sales A/C<strong></td>
                                <td style="text-align: right;">'.number_format((float)$sales_ac_value, 2, '.', '').'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">'.$sales_ac_value_details.'</td>
                        </tr>
                        <tr>
                                <td><strong>Direct Income<strong></td>
                                <td style="text-align: right;">'.number_format((float)$direct_income_value, 2, '.', '').'</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2" >
                                        '.$direct_income_value_details.'
                                </td>
                        </tr>
                        <tr>
                                <td><strong>Closing Stock<strong></td>
                                <td style="text-align: right;">0</td>
                        </tr>
                        <tr class="descripc">
                                <td colspan="2">
                                        0 
                                </td>
                        </tr>';
                        if(!empty($gross_loss) && $gross_loss!="0.00"){
                                $str.='<tr>
                                            <td><strong>Gross Loss<strong></td>
                                            <td style="text-align: right;">'.$gross_loss.'</td>
                                        </tr>';
                        }else{
                                $str.='<tr height="20px">
                                            <td></td>
                                            <td style="text-align: right;"></td>
                                        </tr>';
                        }
                $str.='</table>
                </td>
            </tr>';
            $str .= '<tr>
                        <td style="border-left: 1px #101010 solid;"></td>
                        <td style="text-align: right;border-bottom: 2px  #101010 solid;border-top: 2px #101010 solid;color: #101010;">'.indian_number((float)$total_exp, 2).'</td>
                        <td style="border-left: 1px #101010 solid;border-left: 1px #101010 solid;"></td>
                        <td style="text-align: right;border-bottom: 2px  #101010 solid;border-top: 2px #101010 solid;border-right: 1px  #101010 solid;color: #101010;">'.indian_number((float)$total_income, 2).'</td>
                    </tr>';
            $str.='<tr>	
                    <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;vertical-align: top;">
                        <table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
                            if(!empty($gross_loss) && $gross_loss!="0.00"){
                                $str.='<tr>
                                            <td><strong>Gross Loss<strong></td>
                                            <td style="text-align: right;">'.indian_number((float)$gross_loss, 2).'</td>
                                        </tr>';
                            }
                            $str.='<tr>
                                        <td><strong>Indirect Expense<strong></td>
                                        <td style="text-align: right;">'.indian_number((float)$indirect_expences['value'], 2).'</td>
                                    </tr>
                                    <tr class="descripc">
                                            <td colspan="2">'.$indirect_expences['entries'].'</td>
                                    </tr>';
								
                            if(!empty($net_profit) && $net_profit!="0.00"){
                                $str.='
                                <tr height="20px">
                                        <td></td>
                                        <td style="text-align: right;"></td>
                                </tr>
                                <tr>
                                        <td><strong>Net Profit<strong></td>
                                        <td style="text-align: right;">'.indian_number((float)$net_profit, 2).'</td>
                                </tr>';

                            } else {
                            $str.=' <tr height="20px">
                                        <td></td>
                                        <td style="text-align: right;"></td>
                                    </tr>
                                    <tr height="20px">
                                        <td></td>
                                        <td style="text-align: right;"></td>
                                    </tr>';
                            }
                    $str.=' </table>
                    </td>
                    <td colspan="2" style="font-size:15px;border-left:1px #101010 solid;border-right:1px #101010 solid;color:#251919;vertical-align: top;">
                        <table style="font-size:15px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >';
                            if(!empty($gross_profit) && $gross_profit!="0.00"){
                                $str.='<tr>
                                            <td><strong>Gross Profit<strong></td>
                                            <td style="text-align: right;">'.indian_number((float)$gross_profit, 2).'</td>
                                        </tr>';
                            }else{
                                $str.='<tr height="20px">
                                            <td></td>
                                            <td style="text-align: right;"></td>
                                        </tr>';
                            }
                            $str.='<tr>
                                    <td><strong>Indirect Income<strong></td>
                                    <td style="text-align: right;">'.indian_number((float)$indirect_income_value, 2).'</td>
                            </tr>
                            <tr class="descripc">
                                    <td colspan="2">'.$indirect_income_value_details.'</td>
                            </tr>';
                            if(!empty($net_loss) && $net_loss!="0.00"){
                                $str.='<tr height="20px">
                                            <td></td>
                                            <td style="text-align: right;"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Net Loss<strong></td>
                                            <td style="text-align: right;">'.indian_number((float)$net_loss, 2).'</td>
                                        </tr>';
                            }else{
                                $str.='<tr height="20px">
                                            <td></td>
                                            <td style="text-align: right;"></td>
                                        </tr>
                                        <tr height="20px">
                                                <td></td>
                                                <td style="text-align: right;"></td>
                                        </tr>';
                            }
                        $str.='</table>
                    </td>
                </tr>';
            $str.='<tr height="20px">
                        <td style="border-top: 1px #101010 solid;border-bottom: 1px  #101010 solid;border-left: 1px #101010 solid;color: #101010;">Total</td>
                        <td style="text-align: right;border-top: 1px #101010 solid;border-bottom: 1px #101010 solid;border-right: 1px #101010 solid;color: #101010;">'.indian_number((float)$gtotal_exp, 2).'</td>
                        <td style="border-top: 1px #101010 solid;border-bottom: 1px #101010 solid;border-left: 1px #101010 solid;color: #101010;">Total</td>
                        <td style="text-align: right;border-top: 1px #101010 solid;border-bottom: 1px #101010 solid;border-right: 1px #101010 solid;color: #101010;">'.indian_number((float)$gtotal_profit, 2).'</td>
                    </tr>';
        $str.='</table>';
    echo $str;
}
?>