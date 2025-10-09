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

if(strtolower($POST['mode']) == "fetch_cashflow_by_month") 
{
	$s_date=explode(' - ',$POST['date']);

	$qry="SELECT sum(gb.amount) as amount,DATE_FORMAT(gb.ref_date,'%M-%Y') as month,DATE_FORMAT(gb.ref_date,'%m-%Y') as month1,gb.entry_type FROM `tbl_general_book` as gb join tbl_ledger as tl on tl.l_id=gb.ledger_id and tl.l_form IN ('bank_form','cash') WHERE gb.`genral_book_status`=0 and gb.entry_type=1 GROUP BY YEAR(gb.ref_date), MONTH(gb.ref_date),gb.entry_type   ";
	//echo $qry;exit;
	$rs_state=$dbcon->query($qry);	
	
	$id=1;
	$str='';
	$str .= '<thead>
				<tr>
					<th width="100">Month-Year</th>
					<th width="100">Opening</th>
					<th width="100">InFlow</th>
					<th width="100">OutFlow</th>
					<th width="100">NetFlow</th>
					<th width="100">Balance</th>
				</tr>
			</thead>
			
			<tbody>';
	while($row=brp_mysqli_fetch_assoc($rs_state))
	{
		
		$qry_1="SELECT sum(gb.amount) as amount1,DATE_FORMAT(gb.ref_date,'%m%Y') as pmonth,gb.entry_type FROM `tbl_general_book` as gb join tbl_ledger as tl on tl.l_id=gb.ledger_id and tl.l_form IN ('bank_form','cash') WHERE gb.`genral_book_status`=0 and gb.entry_type=2 and DATE_FORMAT(gb.ref_date,'%M-%Y')='".$row['month']."' GROUP BY YEAR(gb.ref_date), MONTH(gb.ref_date),gb.entry_type  ";
		$ro_1=$dbcon->query($qry_1);
		$re_1=brp_mysqli_fetch_assoc($ro_1);


		$netflow = $row['amount']-$re_1['amount1'];
		
		$str .= '<tr>
				<td  width="450"><form method="post" action="'.ROOT.FINANCE_ROOT.'cash_flow_report_by_group/'.$re_1['pmonth'].'" class="inline">
							  <input type="hidden" name="report_type" id="report_type" value="trial_blc_sht_ledger">
							  <button type="submit" name="submit_param"  value="submit_value" class="link-button">
							    '.$row['month'].'
							  </button>
							</form></td>
				<td width="300">-</td>			
				<td width="300">'.$row['amount'].'</td>			
				<td width="300">'.$re_1['amount1'].'</td>
				<td width="300">'.$netflow.'</td>
				<td width="300">'.$netflow.'</td>
			</tr>';
		$id++;
		$monthcheck = $row['month'];
		//$tdsledgername = $row['l_name'];
	}
	//$str.='';
	$str .= '<tr>					
			<th colspan="2" style="text-align:right;"><strong>Total</strong></th>
			<th></th>
			<th></th>
		</tr></tbody>';

	echo $str;
}else if(strtolower($POST['mode']) == "load_cashinflow") 
{
	//$s_date=explode(' - ',$POST['date']);

	if(!empty($POST['monthyear'])){
		$where = " and DATE_FORMAT(gb.ref_date,'%m%Y') = ".$POST['monthyear']." ";
	}else{
		$where = "";
	}


	$query1="SELECT gb.table_name,gb.table_id FROM `tbl_general_book` as gb join tbl_ledger as tl on tl.l_id=gb.ledger_id and tl.l_form IN ('bank_form','cash') WHERE gb.`genral_book_status`=0 and gb.entry_type=1 ".$where."";

	$rel1=brp_mysqli_fetch_all($dbcon->query($query1));
	
	foreach($rel1 as $inflow){
		$in_flow_array[] = getInflowLedgerGourpTotal($dbcon,$inflow['table_name'],$inflow['table_id']);
	}
	$arr_total = array();
	
	foreach($in_flow_array as $key=>$value){
		$total_qty = 0;
		if (array_key_exists($value['g_name'],$arr_total)){
			$total_qty = $arr_total[$value['g_name']];
		}
		$total_qty = $total_qty + $value['amount'];

		$arr_total[$value['g_name']] = $total_qty;
	}

	$id=1;
	$str='';
		$str .= '<thead>	
				<tr>
					<th width="100">I N F L O W</th>
					<th width="100">Amount (Rs.)</th>
				</tr>
			</thead>
			
			<tbody>';
	foreach($arr_total as $arr=>$arrv){	
		$total_a = $total_a + $arrv;
		$str .= '<tr>	
					<td width="300">'.$arr.'</td>			
					<td width="300">'.$arrv.'</td>
				</tr>
				<tr>';
	}

	$str .= '<tr>					
			<th style="strong">Total</th>
			<th style="strong">'.$total_a.'</th>
		</tr></tbody>';

	echo $str;
}else if(strtolower($POST['mode']) == "load_cashoutflow") 
{
	//$s_date=explode(' - ',$POST['date']);
	if(!empty($POST['monthyear'])){
		$where = " and DATE_FORMAT(gb.ref_date,'%m%Y') = ".$POST['monthyear']." ";
	}else{
		$where = "";
	}

	$query1="SELECT gb.table_name,gb.table_id FROM `tbl_general_book` as gb join tbl_ledger as tl on tl.l_id=gb.ledger_id and tl.l_form IN ('bank_form','cash') WHERE gb.`genral_book_status`=0 and gb.entry_type=2  ".$where."";

	$rel1=brp_mysqli_fetch_all($dbcon->query($query1));
	
	foreach($rel1 as $inflow){
		$in_flow_array1[] = getOutflowLedgerGourpTotal($dbcon,$inflow['table_name'],$inflow['table_id']);
	}
	$arr_total1 = array();
	
//echo '<pre>'; print_r($in_flow_array1);exit;
	foreach($in_flow_array1 as $key1=>$value1){
		$total_qty1 = 0;
		if (array_key_exists($value1['g_name'],$arr_total1)){
			$total_qty1 = $arr_total1[$value1['g_name']];
		}
		$total_qty1 = $total_qty1 + $value1['amount'];

		$arr_total1[$value1['g_name']] = $total_qty1;
	}
	$id=1;
	$str1='';
		$str1 .= '<thead>	
				<tr>
					<th width="100">O U T F L O W</th>
					<th width="100">Amount (Rs.)</th>
				</tr>
			</thead>
			
			<tbody>';
	foreach($arr_total1 as $arr1=>$arrv1){	
		$total_a = $total_a + $arrv1;
		$str1 .= '<tr>	
					<td width="300">'.$arr1.'</td>			
					<td width="300">'.$arrv1.'</td>
				</tr>
				<tr>';
	}

	$str1 .= '<tr>					
			<th style="strong">Total</th>
			<th style="strong">'.$total_a.'</th>
		</tr></tbody>';

	echo $str1;
}