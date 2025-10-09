<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
//include("../../config/image.php");

		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") 
		{
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['ledger_start_date']=$s_date[0];
			$_SESSION['ledger_end_date']=$s_date[1];
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select * from tbl_ledger where l_id=".$POST['cust_id'];

			$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
				$str .='
					<table  class="display table table-bordered table-striped" id="data_list">
						<tr id="logo" class="logo" style="display:none">
							<td colspan="8" style="text-align:center;">
								<strong>'.$set_head['company_name'].'</strong>
							</td>
						</tr>
						<tr>
							<td colspan="2"><strong>Customer Ledger </strong></td>
							<td colspan="2" style="text-align:center"><strong>	Name:'.$cust_rel['l_name'].'<br>
							GSTIN NO : '.$cust_rel['gst_no'].'<br>
							ADDRESS : '.$cust_rel['m_address'].'
							</strong></td>
							<td colspan="2" style="text-align:right">Date
							<label>  : <strong>'.date('d/m/Y',strtotime($s_date[0])).'</strong> To <strong>'.date('d/m/Y',strtotime($s_date[1])).'</strong></label></td>
						</tr>
						<tr>
							<th width="5%" style="text-align:center">Sr. NO.</th>
							<th width="12%" style="text-align:center">Date</th>
							<th width="47%" style="text-align:center">Description</th>
							<th width="12%" style="text-align:center">Debit Amount</th>
							<th width="12%" style="text-align:center">Credit Amount</th>
							<th width="12%" style="text-align:center">Balance</th>
						</tr>
						<tbody>';
		
		 $query="select opn_balance as opening_balance,balance_typeid,debitamount,creditamount from tbl_ledger as cust 
		left join 
		(select sum(amount) as debitamount,invoice.ledger_id from tbl_general_book as invoice where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=2 and invoice.company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by invoice.ledger_id) as debitinvoice on debitinvoice.ledger_id=cust.l_id 
		
		left join 
		(select sum(amount) as creditamount,rec.ledger_id from tbl_general_book as rec 
			where genral_book_status=0 and table_name!='tbl_ledger' and entry_type=1 and company_id=".$_SESSION['company_id']." and ref_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by rec.ledger_id) as creditcust on creditcust.ledger_id=cust.l_id 
		
		where cust.l_id=".$POST['cust_id'] ;
		
		
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		//$op_balance=($rel['balance_typeid']=="2"?(-$rel['opening_balance']):$rel['opening_balance']);
		// $balance=$op_balance+$rel['creditamount']-($rel['debitamount']);
		
		$op_balance=($rel['balance_typeid']=="2"?($rel['opening_balance']):-$rel['opening_balance']);
		 $balance=$op_balance+$rel['debitamount']-($rel['creditamount']);
		 
		 $balancetype='';
		$str .='<tr>
					<td data-label="" style="text-align:center"></td>
					<td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
					<td data-label="DESCRIPTION" style="text-align:center">Opening Balance</td>
					<td data-label="DEBIT AMOUNT" style="text-align:center">- </td>
					<td data-label="CREDIT AMOUNT" style="text-align:center"> -</td>';
					/* if($balance<0)
					{
						$balancetype='DR';
						$str .='
					  <td data-label="BALANCE" style="text-align:right;color:red;">'.abs($balance).' '.$balancetype.'</td>';
					}
					else if($balance>0)
					{
							$balancetype='CR';
							$str .='
					  <td data-label="BALANCE" style="text-align:right;color:green;">'.abs($balance).' '.$balancetype.'</td>';
					}else{
						$str .='
					  <td data-label="BALANCE" style="text-align:center;color:green;">-</td>';
					} */
					
					if($balance>0)
					{
						$balancetype='DR';
						$str .='
					  <td data-label="BALANCE" style="text-align:right;color:red;">'.abs($balance).' '.$balancetype.'</td>';
					}
					else if($balance<0)
					{
							$balancetype='CR';
							$str .='
					  <td data-label="BALANCE" style="text-align:right;color:green;">'.abs($balance).' '.$balancetype.'</td>';
					}else{
						$str .='
					  <td data-label="BALANCE" style="text-align:center;color:green;">-</td>';
					}
					
					$str .='
					</tr>';
		
			/* $qry='select * from tbl_general_book as payment
				where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' and ref_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and ref_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and payment.ledger_id='.$POST['cust_id'].' ORDER BY payment.ref_date'; */
				
			  $qry='select * from tbl_general_book as payment
				where payment.genral_book_status=0 and payment.company_id='.$_SESSION['company_id'].' and ref_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and ref_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and table_name!="tbl_ledger" and payment.ledger_id='.$POST['cust_id'].' ORDER BY payment.ref_date';

			//echo $qry;exit;

			$result1=$dbcon->query($qry);
			$i=1;
				
			if(mysqli_num_rows($result1)>0)
				{
					$total=0;
					while($re=mysqli_fetch_assoc($result1))
					{
						$balancetype='';
						$str.='<tr>
						  <td data-label="SR. NO." style="text-align:center">'.$i.'</td>
							<td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($re["ref_date"])).'</td>';
								$ref_no=explode('=',load_led_no($dbcon,$re['table_name'],$re['table_id']));
								//print_r($ref_no);
								

								if($re['table_name']=="tbl_invoice")
								{
									
									// $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.' Invoice No : '.$ref_no.'</td>';

									$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Invoice No : '.$ref_no[0].'</a></td>';
								}
								else if($re['table_name']=="tbl_pono"){
									if($re['ledger_id_ref'] == 24453)
									{
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$ref_no[1].'">TDS for this Bill : '.$ref_no[0].'</a></td>';
									}
									else
									{
									$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$ref_no[1].'">Purchace No : '.$ref_no[0].'</a></td>';
									// $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Purchace No : '.$ref_no.'</td>';
									}
								}
								else if($re['table_name']=="tbl_payment"){
									if($re['entry_type']=="1"){
										$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Recipt No : '.$ref_no.'</td>';
									}else{
										$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Payment No : '.$ref_no.'</td>';
									}
								}

								else if($re['table_name']=="tbl_receipt"){
									
									if($ref_no[2] == 1){
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
									}else if($ref_no[2] == 2){
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
									}
									

									// if($re['entry_type']=="1"){
									// 	$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Recipt No : '.$ref_no.'</td>';
									// }else{
									// 	$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Payment No : '.$ref_no.'</td>';
									// }
								}

								else if($re['table_name']=="tbl_receipt_payment_trn"){
									if($ref_no[2] == 1){
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'payment_edit/'.$ref_no[1].'">Make Payment No : '.$ref_no[0].'</a></td>';
									}else if($ref_no[2] == 2){
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'recipt_edit/'.$ref_no[1].'">Recieved No : '.$ref_no[0].'</a></td>';
									}

								}

								else if($re['table_name']=="tbl_journal_trn"){
									// $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Journal No : '.$ref_no.'</td>';
									$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'journal_entry_edit/'.$ref_no[1].'">Journal No : '.$ref_no[0].'</a></td>';
								}
								else if($re['table_name']=="tbl_contra_trn"){

									$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'contra_entry_edit/'.$ref_no[1].'">Contra No : '.$ref_no[0].'</a></td>';
								}
								else if($re['table_name']=="tbl_debitnote"){
									// $str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Journal No : '.$ref_no.'</td>';
									$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'debitnoteedit/'.$ref_no[2].'">DebitNote No : '.$ref_no[0].'-'.$ref_no[1].'</a></td>';
								}

								else if($re['table_name']=="tbl_bill_sundry_transaction"){
									if($ref_no[2] == 'tbl_invoice'){

										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'invoiceedit/'.$ref_no[1].'">Invoice No : '.$ref_no[0].' & '.$ref_no[3].'</a></td>';

									}else if($ref_no[2] == 'tbl_pono'){

										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PURCHASE_ROOT.'purchaseedit/'.$ref_no[1].'">Purchace No : '.$ref_no[0].'</a></td>';

									}else if($ref_no[2] == 'tbl_debitnote'){
										
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'debitnoteedit/'.$ref_no[1].'">DebitNote No : '.$ref_no[0].'</a></td>';

									}else if($ref_no[2] == 'tbl_sale_return'){
										
										$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'salereturnedit/'.$ref_no[1].'">Sale Return No : '.$ref_no[0].'</a></td>';

									}else{
										$str .= '<td>-----</td>';
									}

								}

								else if($re['table_name']=="tbl_sale_return"){
									
									$str .= '<td data-label="DESCRIPTION" style="text-align:center"><a  data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.FINANCE_ROOT.'salereturnedit/'.$ref_no[2].'">Credit Note No : '.$ref_no[0].'-'.$ref_no[1].'</a></td>';
								}

								else if($re['table_name']=="tbl_expense_detail"){
									$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Expense : '.$ref_no.'</td>';
								}
								else if($re['table_name']=="tbl_expense_detail_account"){
									$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Expense : '.$ref_no.'</td>';
								}
								else{
									$str .='<td data-label="DESCRIPTION" style="text-align:center">-</td>';
								}
						
						if($re['entry_type']==2){
						 $str.='
						  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.$re['amount'].'</td>
						  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;"></td>';
							//$balance-=$re['amount'];
							$balance+=$re['amount'];
							
						}else{
							$str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;"></td>
							<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.$re['amount'].'</td>';
							//$balance+=$re['amount'];
							$balance-=$re['amount'];
						}
						/* if($balance>0){
						 $str.='
						 <td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.abs($balance).' CR</td>';
						}else if($balance<0){
							$str.='
							<td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.abs($balance).' DR</td>';
						}else{
							$str.='
						 <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
						} */
						if($balance<0){
						 $str.='
						 <td data-label="CREDIT AMOUNT" style="text-align:right;color:green;">'.abs($balance).' CR</td>';
						}else if($balance>0){
							$str.='
							<td data-label="CREDIT AMOUNT" style="text-align:right;color:red;">'.abs($balance).' DR</td>';
						}else{
							$str.='
						 <td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">-</td>';
						}
						$str.='</tr>';		
						$i++;
						
					}
					
				}
				else
				{
					$str .='<tr>
							<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
							</tr>';
							
				} 
			$str .='</tbody>				 
				  </table>'; 
				
			 
			echo $str;
		}
		
   
?>