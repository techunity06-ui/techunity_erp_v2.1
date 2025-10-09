
<?php
session_start();
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/coman_function.php");
include("../../config/image.php");
$image = new SimpleImage();
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
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
		$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
			$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
				$str .='
					<table  width="100%"   class="display table  table-striped">
					</table>
				  <table  class="display table table-bordered table-striped" id="data_list">
				  <tr id="logo" class="logo" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr>
						<td colspan="2"><strong>Customer Ledger </strong></td>
						<td colspan="2" style="text-align:center"><strong>	Name:'.$cust_rel['company_name'].'
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
	 $query="select opening_balance,balance_typeid,debitamount,creditamount,pocraditamount,podebitamount,craditnoteamount,debitnoteamount,excessdebitamount,excesscraditamount  from tbl_customer as cust 
		left join 
		(select sum(g_total) as debitamount,invoice.cust_id from tbl_invoice as invoice where invoice_status=0 and invoice.company_id=".$_SESSION['company_id']." and invoice_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by invoice.cust_id) as debitinvoice on debitinvoice.cust_id=cust.cust_id 
		
		left join 
		(select sum(rec.total_amount) as creditamount,res.cust_id from tbl_receipt_trn as rec 
			left join tbl_receipt as res on res.receipt_id=rec.receipt_id
			where rec.status=0 and res.status=0 and rec.payment_type=1 and res.company_id=".$_SESSION['company_id']." and res.receipt_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by res.cust_id) as creditcust on creditcust.cust_id=cust.cust_id 
		
		left join 
		(select sum(g_total) as pocraditamount,pono.vender_id from tbl_pono as pono where pono.status=0 and pono.company_id=".$_SESSION['company_id']." and pono.po_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by pono.vender_id) as craditpo on craditpo.vender_id=cust.cust_id
		
		left join 
		(select sum(rec.total_amount) as podebitamount,res.cust_id from tbl_receipt_trn as rec 
			left join tbl_receipt as res on res.receipt_id=rec.receipt_id
			where rec.status=0 and res.status=0 and rec.payment_type=2 and res.company_id=".$_SESSION['company_id']." and res.receipt_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by res.cust_id) as debitven on debitven.cust_id=cust.cust_id 
		
		left join 
		(select sum(g_total) as craditnoteamount,creditnote.cust_id from tbl_credit_note as creditnote where creditnote.credit_note_status=0 and creditnote.company_id=".$_SESSION['company_id']." and creditnote.credit_note_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by creditnote.cust_id) as craditcust on craditcust.cust_id=cust.cust_id
		
		left join 
		(select sum(g_total) as debitnoteamount,debitnote.vender_id from tbl_debitnote as debitnote where debitnote.debit_note_status=0 and debitnote.company_id=".$_SESSION['company_id']." and debitnote.debitnote_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by debitnote.vender_id) as debitvender on debitvender.vender_id=cust.cust_id
		
		left join 
		(select sum(excess_amount) as excessdebitamount,excessdebit.cust_id from tbl_excess as excessdebit
		left join tbl_receipt as rec on rec.receipt_id=excessdebit.receipt_id
		where excessdebit.status=0 and excessdebit.excess_type=2 and excessdebit.company_id=".$_SESSION['company_id']." and rec.receipt_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by excessdebit.cust_id) as debitexcesscust on debitexcesscust.cust_id=cust.cust_id
		
		left join 
		(select sum(excess_amount) as excesscraditamount,excesscradit.cust_id from tbl_excess as excesscradit
		left join tbl_receipt as rec on rec.receipt_id=excesscradit.receipt_id
		where excesscradit.status=0 and excesscradit.excess_type=1 and excesscradit.company_id=".$_SESSION['company_id']." and rec.receipt_date < '".date('Y-m-d',strtotime($s_date[0]))."'  group by excesscradit.cust_id) as craditexcesscust on craditexcesscust.cust_id=cust.cust_id
		
		where cust.cust_id=".$POST['cust_id'];
		$rel=mysqli_fetch_assoc($dbcon->query($query));
		$op_balance=($rel['balance_typeid']=="1"?(-$rel['opening_balance']):$rel['opening_balance']);
		 $balance=$op_balance+$rel['debitamount']+$rel['podebitamount']+$rel['debitnoteamount']+$rel['excessdebitamount']-($rel['creditamount']+$rel['pocraditamount']+$rel['craditnoteamount']+$rel['excesscraditamount']);
		 $balancetype='';
		$str .='<tr>
					<td data-label="" style="text-align:center"></td>
					<td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($s_date[0])).'</td> 
					<td data-label="DESCRIPTION" style="text-align:center">Opening Balance</td>
					<td data-label="DEBIT AMOUNT" style="text-align:center">- </td>
					<td data-label="CREDIT AMOUNT" style="text-align:center"> -</td>';
					if($balance>0)
					{
						$balancetype='DR';
						$str .='
					  <td data-label="BALANCE" style="text-align:center;color:red;">'.abs($balance).' '.$balancetype.'</td>';
					}
					else if($balance<0)
					{
							$balancetype='CR';
							$str .='
					  <td data-label="BALANCE" style="text-align:center;color:green;">'.abs($balance).' '.$balancetype.'</td>';
					}else{
						$str .='
					  <td data-label="BALANCE" style="text-align:center;color:green;">-</td>';
					}
					
					$str .='
					  <!--<td style="text-align:center">'.abs($balance).' '.$balancetype.'</td>-->
					</tr>';
			$qry='Select * from (
			
			(Select invoice_date,invoice_no,g_total,NULL as paymentmodeid,NULL as payment_mode,1 as typeid,invoice_id from tbl_invoice as invoice where invoice_status=0 and invoice_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and invoice_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and invoice.company_id='.$_SESSION['company_id'].' and invoice.cust_id='.$POST['cust_id'].' order by invoice_date) 
			
			union (select receipt_date as invoice_date,concat(bank_name,"-",cheque_dtl) as invoice_no,restrn.paid_amount as g_total,payment.payment_mode_id,payment_mode,2 as typeid,restrn.receipt_trn_id as invoice_id from tbl_receipt as payment
			left join tbl_receipt_trn as restrn on restrn.receipt_id=payment.receipt_id
			left join bank_mst as bank on bank.bankid=payment.bank_id 
			left join tbl_payment_mode mode on payment.payment_mode_id=mode.paymentmodeid 
			where payment.status=0 and restrn.payment_type=1 and payment.company_id='.$_SESSION['company_id'].' and receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and payment.cust_id='.$POST['cust_id'].' )
			
			union (select restrn.receipt_date as invoice_date,restrn.receipt_no as invoice_no,payment.excess_amount as g_total,restrn.payment_mode_id,payment_mode,11 as typeid,restrn.receipt_id as invoice_id from tbl_excess as payment
			left join tbl_receipt as restrn on restrn.receipt_id=payment.receipt_id
			left join bank_mst as bank on bank.bankid=restrn.bank_id 
			left join tbl_payment_mode mode on restrn.payment_mode_id=mode.paymentmodeid
			where payment.status=0 and payment.excess_type=1 and payment.company_id='.$_SESSION['company_id'].' and restrn.receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and restrn.receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and payment.cust_id='.$POST['cust_id'].' )
			
			union (select restrn.receipt_date as invoice_date,restrn.receipt_no as invoice_no,payment.excess_amount as g_total,restrn.payment_mode_id,payment_mode,12 as typeid,restrn.receipt_id as invoice_id from tbl_excess as payment
			left join tbl_receipt as restrn on restrn.receipt_id=payment.receipt_id
			left join bank_mst as bank on bank.bankid=restrn.bank_id 
			left join tbl_payment_mode mode on restrn.payment_mode_id=mode.paymentmodeid
			where payment.status=0 and payment.excess_type=2 and payment.company_id='.$_SESSION['company_id'].' and restrn.receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and restrn.receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and payment.cust_id='.$POST['cust_id'].' )
			
			union (select receipt_date as invoice_date,TDS as invoice_no,restrn.kasar as g_total,payment.payment_mode_id,payment_mode,7 as typeid,restrn.receipt_trn_id as invoice_id from tbl_receipt as payment
			left join tbl_receipt_trn as restrn on restrn.receipt_id=payment.receipt_id
			left join bank_mst as bank on bank.bankid=payment.bank_id 
			left join tbl_payment_mode mode on payment.payment_mode_id=mode.paymentmodeid 
			where payment.status=0 and restrn.kasar!="0.00" and restrn.payment_type=1 and payment.company_id='.$_SESSION['company_id'].' and receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and payment.cust_id='.$POST['cust_id'].' )
			
			union (select receipt_date as invoice_date,TDS as invoice_no,restrn.tds as g_total,payment.payment_mode_id,payment_mode,9 as typeid,restrn.receipt_trn_id as invoice_id from tbl_receipt as payment
			left join tbl_receipt_trn as restrn on restrn.receipt_id=payment.receipt_id
			left join bank_mst as bank on bank.bankid=payment.bank_id 
			left join tbl_payment_mode mode on payment.payment_mode_id=mode.paymentmodeid 
			where payment.status=0 and restrn.tds!="0.00" and restrn.payment_type=1 and payment.company_id='.$_SESSION['company_id'].' and receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and payment.cust_id='.$POST['cust_id'].' )
			
			union (Select credit_note_date as invoice_date,credit_note_no as invoice_no,g_total,NULL as paymentmodeid,NULL as payment_mode,6 as typeid,credit_note_id as invoice_id from tbl_credit_note as credit_note where credit_note_status=0 and credit_note_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and credit_note_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and credit_note.company_id='.$_SESSION['company_id'].' and credit_note.cust_id='.$POST['cust_id'].' order by credit_note_date) 
			
			union (Select debitnote_date as invoice_date,debitnote_no as invoice_no,g_total,NULL as paymentmodeid,NULL as payment_mode,5 as typeid,debitnote_id as invoice_id from tbl_debitnote as debit_note where debit_note_status=0 and debitnote_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and debitnote_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and debit_note.company_id='.$_SESSION['company_id'].' and debit_note.vender_id='.$POST['cust_id'].' order by debitnote_date) 
			
			union(Select po_date as invoice_date,po_no as invoice_no,g_total,NULL as paymentmodeid,NULL as payment_mode,3 as typeid,po_id as invoice_id from tbl_pono as pono where pono.status=0 and po_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and po_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and pono.company_id='.$_SESSION['company_id'].' and pono.vender_id='.$POST['cust_id'].' order by po_date)
			
			union (select purpayment.receipt_date as invoice_date,concat(acc_name,"-",cheque_dtl) as invoice_no,restrn.paid_amount as g_total,purpayment.payment_mode_id,payment_mode,4 as typeid,restrn.receipt_trn_id as invoice_id from tbl_receipt as purpayment 
			left join tbl_receipt_trn as restrn on restrn.receipt_id=purpayment.receipt_id
			left join account_mst as acc on acc.acc_id=purpayment.acc_id 
			left join tbl_payment_mode mode on purpayment.payment_mode_id=mode.paymentmodeid 
			where purpayment.status=0 and restrn.payment_type=2 and restrn.status=0  and purpayment.company_id='.$_SESSION['company_id'].' and purpayment.receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and purpayment.receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and purpayment.cust_id='.$POST['cust_id'].' )
			
			union (select purpayment.receipt_date as invoice_date,"kasar" as invoice_no,restrn.kasar as g_total,purpayment.payment_mode_id,payment_mode,8 as typeid,restrn.receipt_trn_id as invoice_id from tbl_receipt as purpayment 
			left join tbl_receipt_trn as restrn on restrn.receipt_id=purpayment.receipt_id
			left join account_mst as acc on acc.acc_id=purpayment.acc_id 
			left join tbl_payment_mode mode on purpayment.payment_mode_id=mode.paymentmodeid 
			where purpayment.status=0 and restrn.kasar!="0.00" and restrn.payment_type=2 and restrn.status=0  and purpayment.company_id='.$_SESSION['company_id'].' and purpayment.receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and purpayment.receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and purpayment.cust_id='.$POST['cust_id'].' )
			
			union (select purpayment.receipt_date as invoice_date,"TDS" as invoice_no,restrn.tds as g_total,purpayment.payment_mode_id,payment_mode,10 as typeid,restrn.receipt_trn_id as invoice_id from tbl_receipt as purpayment 
			left join tbl_receipt_trn as restrn on restrn.receipt_id=purpayment.receipt_id
			left join account_mst as acc on acc.acc_id=purpayment.acc_id 
			left join tbl_payment_mode mode on purpayment.payment_mode_id=mode.paymentmodeid 
			where purpayment.status=0 and restrn.tds!="0.00" and restrn.payment_type=2 and restrn.status=0  and purpayment.company_id='.$_SESSION['company_id'].' and purpayment.receipt_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and purpayment.receipt_date<="'.date('Y-m-d',strtotime($s_date[1])).'" and purpayment.cust_id='.$POST['cust_id'].' )
			
			) as data order by invoice_date,typeid';
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
						  <td data-label="DATE" style="text-align:center">'.date('d/m/Y',strtotime($re["invoice_date"])).'</td>';
							$mode1=$re['paymentmodeid']==1 ? $re['payment_mode'] : 'Payment: '.$re['payment_mode'] .' ('.$re['invoice_no'].')';
							$mode2=$re['paymentmodeid']==1 ?'Payment: '. $re['payment_mode']: 'Payment: '.$re['payment_mode'] .' ('.$re['invoice_no'].')';
							
							if($re['typeid']=="2" || $re['typeid']=="7" || $re['typeid']=="9" || $re['typeid']=="4" || $re['typeid']=="8" || $re['typeid']=="10" || $re['typeid']=="11"){
								
								$qry_sub="select inv.invoice_no,po.po_no,cra.credit_note_no,deb.debitnote_no FROM `tbl_receipt_trn` as trn 
									left join tbl_invoice as inv on inv.invoice_id=trn.invoice_id
									left join tbl_pono as po on po.po_id=trn.purchase_id
									left join tbl_credit_note as cra on cra.credit_note_id=trn.cradit_note_id
									left join tbl_debitnote as deb on deb.debitnote_id=trn.debit_note_id
								where trn.status=0 and trn.receipt_trn_id=".$re['invoice_id']."";
								$result_sub=$dbcon->query($qry_sub);		
								$row_sub=mysqli_fetch_assoc($result_sub);
								
								if($row_sub['invoice_no']!=""){
									$demo="Invoice Payment- (Ref No:".$row_sub['invoice_no'].")-";
								}else if($row_sub['po_no']!=""){
									$demo="Purchace Payment- (Ref No :".$row_sub['po_no'].")-";
								}else if($row_sub['credit_note_no']!=""){
									$demo="Cradit Note Payment- (Ref No :".$row_sub['credit_note_no'].")-";
								}else if($row_sub['debitnote_no']!=""){
									$demo="Debit Note Payment- (Ref No :".$row_sub['debitnote_no'].")-";
								}else{
									$demo=" ";
								}
									
								
							}else{
								$demo=" ";
							}
						
						if($re['typeid']=="1")
						{
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.' Invoice No : '.$re["invoice_no"].'</td>';
						}
						else if($re['typeid']=="2"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.''.$mode1.'</td>';
						}else if($re['typeid']=="3"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Purchace No : '.$re["invoice_no"].'</td>';
						}else if($re['typeid']=="5"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Debit No : '.$re["invoice_no"].'</td>';
						}else if($re['typeid']=="6"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Credit No : '.$re["invoice_no"].'</td>';
						}
						else if($re['typeid']=="7"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Kasar</td>';
						}
						else if($re['typeid']=="8"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Kasar</td>';
						}
						else if($re['typeid']=="9"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'TDS</td>';
						}
						else if($re['typeid']=="10"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'TDS</td>';
						}
						else if($re['typeid']=="11" || $re['typeid']=="12"){
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.'Excess</td>';
						}
						else{
							$str .='<td data-label="DESCRIPTION" style="text-align:center">'.$demo.''.$mode2.'</td>';
						}
						
						if($re['typeid']=="1" || $re['typeid']=="5" || $re['typeid']=="4" || $re['typeid']=="8" || $re['typeid']=="10" || $re['typeid']=="12")
						{
						 $str.='
						  <td data-label="DEBIT AMOUNT" style="text-align:center;color:red;">'.$re['g_total'].'</td>';
							$balance+=$re['g_total'];
							
						}  
						else 
						{
							$str.='
						  <td data-label="DEBIT AMOUNT" style="text-align:center">-</td>';
						
						}
						
						if($re['typeid']=="2" || $re['typeid']=="3" || $re['typeid']=="6" || $re['typeid']=="7" || $re['typeid']=="9" || $re['typeid']=="11")
						{
						  $str.='<td data-label="CREDIT AMOUNT" style="text-align:center;color:green;">'.$re['g_total'].'</td>';
							$balance-=$re['g_total'];
							 
						}
						 
						else
						{
								$str.='
						  <td data-label="CREDIT AMOUNT" style="text-align:center">-</td>';
						
						}
					if($balance>0)
					{
						$balancetype='DR';
						$str.='
						  <td data-label="BALANCE" style="text-align:center;color:red;">'.abs($balance).' '.$balancetype.'</td>';	
						 
					}
					else if($balance<0)
					{
							$balancetype='CR';
							$str.='
						  <td data-label="BALANCE" style="text-align:center;color:green;">'.abs($balance).' '.$balancetype.'</td>	';
						 
					}else{
						$str.='
						  <td data-label="BALANCE" style="text-align:center;color:green;"></td>	';
					}
						 $str.='
						  <!--<td style="text-align:center">'.abs($balance).' '.$balancetype.'</td>	--> 
				 		</tr>';		
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
		
    }
    /*else {
        die("Error - 2");
    }*/
}
/*
else {
    die("Error - 1");
}*/
?>