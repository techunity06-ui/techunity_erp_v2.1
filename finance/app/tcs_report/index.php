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

if(strtolower($POST['mode']) == "fetch_all_tcs_entry") 
{
	$s_date=explode(' - ',$POST['date']);

	$qry="SELECT tl.l_id,tl.l_name,gb.ref_date,gb.amount,gb.entry_type,gb.table_name,gb.table_id,gb.general_book_id FROM `tbl_general_book` as gb join tbl_ledger as tl on gb.ledger_id=tl.l_id and tl.ledger_Tax_type='9892' where gb.genral_book_status=0 and gb.entry_type=1 order by  tl.l_name desc ";
	//echo $qry;exit;
	$rs_state=$dbcon->query($qry);	
	
	$id=1;
	$str='';
	$str .= '<thead>
				<tr>
					<th width="100">#</th> 
					<th width="100">TCS Category</th>
					<th width="100">Ref. No.</th>
					<th width="100">Deductee Name</th>
					<th width="100">Payment Amt</th>
					<th width="100">TCS %</th>
					<th width="100">TCS Amount</th>
					<th width="100">Payment Date</th>
					<th width="100">Tax Deposited</th>
					<th width="100">Tax Deposited Date</th>
					<th width="100">Bank Name</th>
				</tr>
			</thead>
			
			<tbody>';
	while($row=brp_mysqli_fetch_assoc($rs_state))
	{

		$ref_no=explode('=',get_tds_details($dbcon,$row['table_name'],$row['table_id']));
		
		$qry_1="SELECT tdr.ref_pay_amount,td.payment_date,tl.l_name,tr.receipt_id as baseid FROM `tbl_tds_tax_deduction_reference_detail` as tdr left join tbl_tds_tax_deduction_reference as td on td.deduction_ref_id=tdr.deduction_ref_id left join tbl_receipt as tr on td.payment_id=tr.receipt_id left join tbl_ledger as tl on tl.l_id=tr.cust_id where tdr.ref_payment_id=".$row['general_book_id']." and tdr.isdelete=0 and td.isdelete=0  ";
		$ro_1=$dbcon->query($qry_1);
		$re_1=brp_mysqli_fetch_assoc($ro_1);

		$qry_2="SELECT tdr.ref_pay_amount,td.payment_date,tl.l_name,tr.journal_trn_id as baseid FROM `tbl_tds_tax_deduction_reference_detail` as tdr left join tbl_tds_tax_deduction_reference as td on td.deduction_ref_id=tdr.deduction_ref_id left join tbl_journal_trn as tr on td.payment_id=tr.journal_id and tr.entry_type=1 left join tbl_ledger as tl on tl.l_id=tr.ledger_id where tdr.isdelete=0 and tdr.ref_payment_id=".$row['general_book_id']."  ";
		$ro_2=$dbcon->query($qry_2);
		$re_2=brp_mysqli_fetch_assoc($ro_2);
		
		if($re_1['baseid'] != 'null'){
			$ref_pay_amount = $re_1['ref_pay_amount'];	
			$payment_date = $re_1['payment_date'];
			$l_name = $re_1['l_name'];
		}else if($re_2['baseid'] != 'null'){
		 	$ref_pay_amount = $re_2['ref_pay_amount'];
		 	$payment_date = $re_2['payment_date'];
		 	$l_name = $re_2['l_name'];
		}else{
			$ref_pay_amount = '-';
			$payment_date = '-';
			$l_name = '-';
		}

		$total_pay_amt = $total_pay_amt + $ref_no[1];
		$total_tcs_amt = $total_tcs_amt + $row['amount'];
		$total_tcs_dep_amt = $total_tcs_dep_amt + $ref_pay_amount;
		$str .= '<tr>
				<td width="100">'.$id.'</td>
				<td  width="250"><form method="post" action="'.ROOT.FINANCE_ROOT.'ledger_form/'.$row['l_id'].'" class="inline">
							  <input type="hidden" name="report_type" id="report_type" value="trial_blc_sht_ledger">
							  <button type="submit" name="submit_param"  value="submit_value" class="link-button">
							    '.$row['l_name'].'
							  </button>
							</form></td>
				<td width="500">'.$ref_no[2].'</td>			
				<td width="300">'.$ref_no[3].'</td>			
				<td width="300">'.$ref_no[1].'</td>
				<td width="300">'.$ref_no[0].' %</td>
				<td width="300">'.$row['amount'].'</td>
				<td width="300">'.date('d M, Y',strtotime($row['ref_date'])).'</td>
				<td width="300">'.$ref_pay_amount.'</td>
				<td width="300">'.$payment_date.'</td>
				<td width="300">'.$l_name.'</td>
			</tr>';
		$id++;
		//$tdsledgername = $row['l_name'];
	}
	//$str.='';
	$str .= '<tr>					
			<th colspan="4" style="text-align:right;"><strong>Total</strong></th>
			<th>'.$total_pay_amt.'</th>
			<th></th>
			<th>'.$total_tcs_amt.'</th>
			<th></th>
			<th>'.$total_tcs_dep_amt.'</th>
			<th></th>
		</tr></tbody>';

	echo $str;
}