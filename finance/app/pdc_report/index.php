<?php
session_start();
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

//print_r($_POST);
//print_r($_FILES);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report") 
		{
			
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$start_date = date("Y-m-d",strtotime($s_date[0]));
			$end_date = date("Y-m-d",strtotime($s_date[1]));
			
			$company_row = get_company_data($dbcon,$_SESSION['company_id']);
			$company_state=$company_row['stateid'];
			
			$where ='';
			$str ='';
			
			$str.="
				<table class='table table-bordered'>";
			
			$str.="<tr style='background-color:#F1F2F7 !important'>
				
				<th>
					<input type='checkbox' class='form-control'  id='checkAll' onchange='check_all_change()' />
				</th>
				<th>#</th>
				<th>Bank Name</th>
				<th>PDC Date</th>
				<th>Type</th>
				<th>Voucher No</th>
				<th>Account</th>
				<th>Amount</th>
			
			</tr>";
			
			$cnt=1;$total=0;
			$sel=$dbcon->query("select r.*,trn.ledger_id,trn.receipt_id,GROUP_CONCAT(l.l_name) as acct_name,l1.l_name as bank_name,r.payment_type from tbl_receipt as r  left join tbl_receipt_payment_trn as trn on trn.receipt_id=r.receipt_id left join tbl_ledger as l on l.l_id=trn.ledger_id left join tbl_ledger as l1 on l1.l_id=r.cust_id where r.is_pdc='1' and r.is_regularize=0 and r.receipt_date between '$start_date' and '$end_date' and r.status=0 group by trn.receipt_id ");
			while($row=brp_mysqli_fetch_array($sel))
			{
				if($row['is_regularize']==0){
					$checkbox = "<input type='checkbox' class='form-control pdc_checkbox' name='ch_id[]' id='ch_id' value=".$row['receipt_id']." />";
				}else{
					$checkbox='';
				}
				$total+=$row['total_paid_amount'];

				if($row['payment_type'] == 1){
					$type='Payment';
				}else if($row['payment_type'] == 2){
					$type='Receipt';
				}
			
				$str.="
				
					<tr>
						<th>
							".$checkbox."
						</th>
						<th>".$cnt."</th>
						<th>".$row['bank_name']."</th>
						<th>".date('d-M-Y',strtotime($row['pdc_date']))."</th>
						<th>".$type."</th>
						<th>".$row['receipt_no']."</th>
						<th>".$row['acct_name']."</th>
						<th>".$row['total_paid_amount']."</th>
					</tr>
				
				";
			
				$cnt++;
			}
			
			$str.="
				
				<tr>
					<th colspan='7' style='text-align:right'>Total:</th>
					<th>".$total."</th>
				</tr>
				
			";
			
			$str.="
				
				<tr>
					<th  colspan='8'>
						
						<button class='btn btn-primary' data-original-title='Delete' data-toggle='tooltip' data-placement='top' onClick='regularize_pdc()'>Regularize</button>
					</th>
				</tr>
				
			";
			
			$str.="</table>";
			
			echo $str;
							
		}
		if(strtolower($POST['mode']) == "pdc_regularized_report") 
		{
			
			$s_date=explode(' - ',$POST['date']);
			$_SESSION['start']=$s_date[0];
			$_SESSION['end']=$s_date[1];
			
			$start_date = date("Y-m-d",strtotime($s_date[0]));
			$end_date = date("Y-m-d",strtotime($s_date[1]));
			
			$company_row = get_company_data($dbcon,$_SESSION['company_id']);
			$company_state=$company_row['stateid'];
			
			$where ='';
			$str ='';
			
			$str.="
				<table class='table table-bordered'>";
			
			$str.="<tr style='background-color:#F1F2F7 !important'>
				
				<th>#</th>
				<th>Bank Name</th>
				<th>PDC Date</th>
				<th>Type</th>
				<th>Voucher No</th>
				<th>Account</th>
				<th>Amount</th>
			
			</tr>";
			
			$cnt=1;$total=0;
			$sel=$dbcon->query("select r.*,trn.ledger_id,trn.receipt_id,GROUP_CONCAT(l.l_name) as acct_name,l1.l_name as bank_name,r.payment_type from tbl_receipt as r  left join tbl_receipt_payment_trn as trn on trn.receipt_id=r.receipt_id left join tbl_ledger as l on l.l_id=trn.ledger_id left join tbl_ledger as l1 on l1.l_id=r.cust_id where r.is_pdc='1' and r.is_regularize=1 and r.receipt_date between '$start_date' and '$end_date' and r.status=0 group by trn.receipt_id ");
			while($row=brp_mysqli_fetch_array($sel))
			{
				
				$total+=$row['total_paid_amount'];

				if($row['payment_type'] == 1){
					$type='Payment';
				}else if($row['payment_type'] == 2){
					$type='Receipt';
				}
			
				$str.="
				
					<tr>
						
						<th>".$cnt."</th>
						<th>".$row['bank_name']."</th>
						<th>".date('d-M-Y',strtotime($row['pdc_date']))."</th>
						<th>".$type."</th>
						<th>".$row['receipt_no']."</th>
						<th>".$row['acct_name']."</th>
						<th>".$row['total_paid_amount']."</th>
					</tr>
				
				";
			
				$cnt++;
			}
			
			$str.="
				
				<tr>
					<th colspan='6' style='text-align:right'>Total:</th>
					<th>".$total."</th>
				</tr>
				
			";
			
			$str.="</table>";
			
			echo $str;
							
		}

		if(strtolower($POST['mode']) == "entry_regularize") 
		{
			foreach ($POST['receipt_id'] as $id) {
				
				$info['is_regularize']=1;
				$update_reciept=update_record('tbl_receipt',$info ,"receipt_id=0".$id , $dbcon);
			}
			
			if($update_reciept)
			{	
				echo "1";							
			}
			else{
				echo "0";
			}

		}
		

?>