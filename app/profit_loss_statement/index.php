<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") {
				$s_date=explode(' - ',$POST['date']);
				$date_paramerter='&from_date='.$s_date[0].'&to_date='.$s_date[1];
				$s_date[0]=date('Y-m-d',strtotime($s_date[0]));
				$s_date[1]=date('Y-m-d',strtotime($s_date[1]));
				$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
				$set_head=mysqli_fetch_assoc($dbcon->query($set));	
				
				$total_direct_income=get_p_and_l_direct_income($s_date[0],$s_date[1],$dbcon);
				$total_direct_income_spare=get_p_and_l_direct_income_spare($s_date[0],$s_date[1],$dbcon);
				$total_direct_expense=get_p_and_l_direct_expense_spare($s_date[0],$s_date[1],$dbcon);
				
				
				$total_indirect_income=get_p_and_l_total_indirect_income($s_date[0],$s_date[1],$dbcon);
				$total_indirect_expense=get_p_and_l_total_indirect_expense($s_date[0],$s_date[1],$dbcon);
				$net_profit=($gross_profit+$total_indirect_income)-$total_indirect_expense;
				$net_profit1=($total_direct_income+$total_direct_income_spare)-($total_direct_expense+$total_indirect_expense);
				$net_profit2=($total_direct_expense+$total_indirect_expense);
				$str .='
					<table  class="display table table-bordered table-striped" id="data_list">
				  <tr id="logo" class="logo" style="">
						<td colspan="4" style="text-align:center;">
							<strong><span class="english">'.$set_head['company_name'].'</span></strong>
						</td>
					</tr>
					<tr>
						<td colspan="2"><strong><span class="english">Profit and Loss Statement</span></strong></td>
						<td colspan="2" style="text-align:right"><span class="english">Date</span>
					<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
				
					</tr>
					
				  <tr>
					  <th width="50%" colspan="2" style="text-align:center"><span class="english">Debit</span></th>
					  <th width="50%" colspan="2" style="text-align:center"><span class="english">Credit</span></th>
				  </tr>
				  <tr>
					  <th width="40%" style="text-align:center"><span class="english">Account</span></th>
					  <th width="10%" style="text-align:center"><span class="english">Amount</span></th>
					  <th width="40%" style="text-align:center"><span class="english">Account</span></th>
					  <th width="10%"style="text-align:center"><span class="english">Amount</span></th>
				  </tr>
				 <tbody>
					
				  <tr>
					   <td style="text-align:left;padding:0px;" colspan="2">
					  '.display_indirect_expense($s_date[0],$s_date[1],$dbcon).'
					  </td>
					  <td style="text-align:left"><span class="english">Ser. Income</span></td>
					  <td style="text-align:center">'.indian_number($total_direct_income,2).'</td>
				  </tr>
				  <tr>
					<td style="text-align:left" title="debit total"><strong><span class="english">Total</span></strong></td>
					<td style="text-align:center"><strong>'.indian_number($total_indirect_expense,2).'</strong></td>
				  </tr>
				   <tr>
					  <td style="text-align:left"><span class="english">Spare. Expense</span></td>
					  <td style="text-align:center">'.indian_number($total_direct_expense,2).'</td>
					  <td style="text-align:left"><span class="english">Spare. Income</span></td>
					  <td style="text-align:center">'.indian_number($total_direct_income_spare,2).'</td>
				  </tr>
				  
				  <tr>
					  <td style="text-align:left;padding:0px;" colspan="2">
					  
					  </td>
					  <td style="text-align:left;padding:0px;" colspan="2">
					 
					  </td>
				  </tr>
				  
				  <tr>
					  <td style="text-align:left" title="debit total"><strong><span class="english">Total</span></strong></td>
					  <td style="text-align:center"><strong>'.indian_number($net_profit2,2).'</strong></td>
					  <td style="text-align:left" title="credit total"><strong><span class="english">Total</span></strong></td>
					  <td style="text-align:center"><strong>'.indian_number($total_direct_income+$total_direct_income_spare,2).'</strong></td>
				  </tr>
				  <tr>
					  <td style="text-align:left;color:red;" title="debit total">'.($net_profit1<0?'<strong>Net Loss</strong>':'').'</td>
					  <td style="text-align:right;color:red;"><strong>'.($net_profit1<0?indian_number($net_profit1,2):'').'</strong></td>
					  <td style="text-align:left;color:green;" title="credit total">'.($net_profit1>0?'<strong>Net Profit</strong>':'').'</td>
					  <td style="text-align:right;color:green;"><strong >'.($net_profit1>0?indian_number($net_profit1,2):'').'</strong></td>
				  </tr>
				  
				  <tr>
					  <td style="text-align:left;color:red;" title="debit total">'.($gross_profit<0?'<strong><span class="english">Gross Loss</span></strong>':'').'</td>
					  <td style="text-align:right;color:red;"><strong>'.($gross_profit<0?indian_number($gross_profit,2):'').'</strong></td>
					  <td style="text-align:left;color:green;" title="credit total">'.($gross_profit>0?'<strong><span class="english">Gross Profit</span></strong>':'').'</td>
					  <td style="text-align:right;color:green;"><strong >'.($gross_profit>0?indian_number($gross_profit,2):'').'</strong></td>
				  </tr>
				  <tr>
					  <td colspan="4" ></td>
				  </tr>
				  
				  
				 </tbody>				 
				  </table>';
			echo $str;
		}
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
function display_indirect_expense($startdate,$enddate,$dbcon)
{
	$rs_exp=get_p_and_l_indirect_expense($startdate,$enddate,$dbcon);
	$str.='<table  class="display table table-bordered table-striped" id="data_list">';
	while($rel_exp=mysqli_fetch_assoc($rs_exp))
	{
		$str.='<tr id="logo" class="logo" style="">
				<td style="text-align:left;" width="80%">
					'.get_expense_by_id($dbcon,$rel_exp['account_mst_id']).'
				</td>
				<td style="text-align:right;">
					'.indian_number($rel_exp['eamount'],2).'
				</td>
			</tr>';
			$totalexp+=$rel_exp['expense_amount'];
	}	
	$str.='</table>';
	return $str;
}
function display_indirect_income($startdate,$enddate,$dbcon)
{
	$rs_exp=get_p_and_l_indirect_income($startdate,$enddate,$dbcon);
	$str.='<table  class="display table table-bordered table-striped" id="data_list">';
	while($rel_exp=mysqli_fetch_assoc($rs_exp))
	{
		$str.='<tr id="logo" class="logo" style="">
				<td style="text-align:left;" width="80%">
					'.$rel_exp['account_name'].'
				</td>
				<td style="text-align:right;">
					'.indian_number($rel_exp['income_amount'],2).'
				</td>
			</tr>';
			$total+=$rel_exp['income_amount'];
	}
	
	$str.='</table>';
	return $str;
}
function get_p_and_l_indirect_income($startdate,$enddate,$dbcon)
{
	if(isset($enddate) && !empty($enddate))
	{
		$where_date=" between '".$startdate."' and '".$enddate."'";
	}
	else{
		$where_date=" < '".$startdate."'";
	}
	$query="SELECT inctrn.account_mst_id,account_name,sum(income_amount) as income_amount FROM income_mst as incmst inner join income_trn as inctrn on inctrn.income_mstid=incmst.incomeid 
	left join mst_accounts as mstacc on inctrn.account_mst_id=mstacc.accountid
	where incmst.income_date ".$where_date." and incmst.mst_status=0 and incmst.company_id=".$_SESSION['company_id']." group by account_mst_id ";
	$rs=($dbcon->query($query));
	return $rs;
}
?>
