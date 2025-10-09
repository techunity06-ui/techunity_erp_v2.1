<?php
session_start(); //start session
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch_all_ledger") 
{
	$s_date=explode(' - ',$POST['date']);

	$qry="SELECT l.l_id, l.l_name,l.opn_balance FROM tbl_ledger as l where ( 1 AND l.l_status=0 and l.company_id in (0,1)) ";
	//echo $qry;exit;
	$rs_state=$dbcon->query($qry);	
	
	$id=1;
	$str='';
	$str .= '<thead>
				<tr>
					<th width="5%">#</th> 
					<th width="15%">Name</th> 
					<th width="25%">Opening Balance</th>
					<th width="25%">Credit</th>
					<th width="25%">Debit</th>
					<th width="5%">Closing Balance</th>
				</tr>
			</thead>
			
			<tbody>
			<tr>
            <td colspan="6">
        <div class="scrollit">
            <table class="table table-stripped" border="1">';
	while($row=brp_mysqli_fetch_assoc($rs_state))
	{
		$balance=get_ledger_dr_cr_amount($dbcon,$row['l_id'],date('Y-m-d',strtotime($s_date[0])),date('Y-m-d',strtotime($s_date[1])));
		$cr_balance = isset($balance['creditamount']) ? $balance['creditamount']:'0.00';
		$dr_balance = isset($balance['debitamount']) ? $balance['debitamount']:'0.00';
		$op_balance = ($balance['openingbalance']!=0) ? $balance['openingbalance']:'0.00';
		$cl_balance = $balance['openingbalance']+$balance['creditamount']-$balance['debitamount'];
		$total_cr=$total_cr+$balance['creditamount'];
		$total_dr=$total_dr+$balance['debitamount'];
		$total_op=$total_op+$balance['openingbalance'];
		$total_cl=$total_cl+$cl_balance;
		
		// if($balance<0){
		// 	$cradit_amo=abs($balance);
		// 	$debit_amount="0.00";
			
		// }else if($balance>0){
		// 	$cradit_amo="0.00";
		// 	$debit_amount=abs($balance);
		// 	$total_dr=$total_dr+$debit_amount;
		// }else{
		// 	$cradit_amo="0";
		// 	$debit_amount="0";
		// }
		//$total_op = $total_op + $row['opn_balance'];
		if(($balance['creditamount'] !=0) || ($balance['debitamount'] !=0) || ($balance['openingbalance'] !=0)){
			$str .= '<tr>
					<td width="5%">'.$id.'</td>
					<td  width="15%"><form method="post" action="'.ROOT.FINANCE_ROOT.'ledger_form/'.$row['l_id'].'" class="inline">
								  <input type="hidden" name="report_type" id="report_type" value="trial_blc_sht_ledger">
								  <button type="submit" name="submit_param"  value="submit_value" class="link-button">
								    '.$row['l_name'].'
								  </button>
								</form></td>
					<td width="25%">'.$op_balance.'</td>
					<td width="25%">'.$cr_balance.'</td>
					<td width="25%">'.$dr_balance.'</td>
					<td width="5%">'.$cl_balance.'</td>
				</tr>';
			$id++;
		}
		
	}
	//$str.='';
	$str .= '            </table>
        </div>
                </td>
        </tr><tr>					
			<th colspan="2" style="text-align:right;"><strong>Total</strong></th>
			<th>'.$total_op.'</th>
			<th>'.$total_cr.'</th>
			<th>'.$total_dr.'</th>
			<th>'.$total_cl.'</th>
		</tr></tbody>';

	echo $str;
}
else if(strtolower($POST['mode']) == "fetch_all_ledger_group")
{
	$s_date=explode(' - ',$POST['date']);
	
	$start_date = date("Y-m-d",strtotime($s_date[0]));
	$end_date = date("Y-m-d",strtotime($s_date[1]));
	
	$qry="SELECT * FROM tbl_group where g_status='0'";
	//echo $qry;exit;
	$rs_group=$dbcon->query($qry);	
		
	$where_date = (isset($end_date) && !empty($end_date)) ? " between '".$start_date."' and '".$end_date."'" : " < '".$start_date."'" ;

	$id=1;
	$str='';
	$str .= '<thead>
				<tr>
					<th width="5%">#</th> 
					<th width="15%">Name</th> 
					<th width="25%">Credit</th>
					<th width="25%">Debit</th>
				</tr>
			</thead>
			
			<tbody>
			<tr>
            <td colspan="6">
        <div class="scrollit">
            <table class="table table-stripped" border="1">';
            $cnt=1;$cr_amount = 0;$db_amount=0;
	while($row=brp_mysqli_fetch_assoc($rs_group))
	{
		$fa_query = "SELECT  
		(select IFNULL(sum(amount),0) from tbl_general_book as ig left join tbl_ledger as il on il.l_id=ig.ledger_id where entry_type=1 and il.`l_group` = '$row[g_id]'  and genral_book_status=0) as cr_amount,
		(select IFNULL(sum(amount),0) from tbl_general_book as ig left join tbl_ledger as il on il.l_id=ig.ledger_id where entry_type=2 and genral_book_status=0 and il.`l_group` = '$row[g_id]' ) as db_amount
        FROM `tbl_general_book` gb 
        LEFT join tbl_ledger as led ON led.l_id= gb.ledger_id 
        LEFT join tbl_group as gro ON gro.g_id=led.l_group 
        WHERE led.l_status = ".ACTIVE."
            AND gb.genral_book_status = ".ACTIVE."
    		AND gb.ref_date between '$start_date' and '$end_date'     
            AND led.`l_group` = ".$row['g_id']."
            AND led.`l_group` != ".PROFIT_LOSS."
            AND led.company_id = ".$_SESSION['company_id']."
        GROUP BY gb.ledger_id order by gro.group_priority";

        $query1 = $dbcon->query($fa_query);
        
        $ra_query = brp_mysqli_fetch_assoc($query1);
		
		if($ra_query['cr_amount']!='' && $ra_query['db_amount']!='' )
		{
			$str .= '<tr>
					<th width="5%">'.$cnt.'</th>
					<th  width="15%">'.$row['g_name'].'</th>
					<th width="25%">'.$ra_query['cr_amount'].'</th>
					<th width="25%">'.$ra_query['db_amount'].'</th>
				</tr>';

			$cr_amount+=$ra_query['cr_amount'];
			$db_amount+=$ra_query['db_amount'];

			$cnt++;
		}


		//show ledger details 
		$la_query = "SELECT gb.ledger_id,led.l_name,led.l_id,(select sum(amount) from tbl_general_book where entry_type=1 and ledger_id=gb.ledger_id and genral_book_status=0) as cr_l_amount,(select sum(amount) from tbl_general_book where entry_type=2 and ledger_id=gb.ledger_id  and genral_book_status=0 ) as db_l_amount
        FROM `tbl_general_book` gb 
        LEFT join tbl_ledger as led ON led.l_id= gb.ledger_id 
        LEFT join tbl_group as gro ON gro.g_id=led.l_group 
        WHERE led.l_status = ".ACTIVE."
            AND gb.genral_book_status = ".ACTIVE."
            AND gb.ref_date between '$start_date' and '$end_date' 
            AND led.`l_group` = ".$row['g_id']."
            AND led.`l_group` != ".PROFIT_LOSS."
            AND led.company_id = ".$_SESSION['company_id']."
        GROUP BY gb.ledger_id";

        $query2 = $dbcon->query($la_query);
        
        while($row_ledger = brp_mysqli_fetch_assoc($query2))
        {
        	$str .= '<tr class="descripc">
					<td width="5%">--</td>
					<td  width="15%">'.$row_ledger['l_name'].'</td>
					<td width="25%">'.$row_ledger['cr_l_amount'].'</td>
					<td width="25%">'.$row_ledger['db_l_amount'].'</td>
				</tr>';
        }

	}

	$financial_year=get_financial_year_new($dbcon);
	$f_start_date = $financial_year['financial_start_date'];
	$f_end_date = $financial_year['financial_end_date'];

	$profit_loss=json_decode(get_unsettled_profit_loss($dbcon,$f_start_date,$f_end_date));
	//print_r($profit_loss[0]);exit;
	if($profit_loss->opening >= 0)
	{
		$cr_amount+=($profit_loss->credit);
	}
	else
	{
		$db_amount+=($profit_loss->debit);	
	}

	$profit_loss_settle=json_decode(get_settled_profit_loss($dbcon,$start_date,$end_date));
	//print_r($profit_loss[0]);exit;
	$cr_amount+=$profit_loss_settle->credit;
	$db_amount+=$profit_loss_settle->debit;

	$total_pl_credit = $profit_loss_settle->credit + $profit_loss->credit+$profit_loss->opening;
	$total_pl_debit = $profit_loss_settle->debit + $profit_loss->debit+$profit_loss->opening;

	if($total_pl_credit!='0' || $total_pl_debit!='0')
	{
		$str .= '<tr>
			<th width="5%">'.$cnt.'</th>
			<th  width="15%">PROFIT & LOSS</th>
			<th width="25%">'.$total_pl_credit.'</th>
			<th width="25%">'.$total_pl_debit.'</th>
		</tr>';
	}
	//$str.='';
	$str .= '</table>
        </div>
                </td>
        </tr><tr>					
			<th colspan="2" style="text-align:right;"><strong>Total</strong></th>
			<th>'.$cr_amount.'</th>
			<th>'.$db_amount.'</th>
		</tr></tbody>';

	echo $str;
}
