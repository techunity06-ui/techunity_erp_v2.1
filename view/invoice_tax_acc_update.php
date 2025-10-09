<?php
session_start();
$AJAX = true;
include("../config/config.php");
//error_reporting(E_ALL);
include("../config/session.php");
include("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
					
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

		/* $query="select * from tbl_invoice where invoice_status=0";
			$result=$dbcon->query($query);
			while($rel=mysqli_fetch_assoc($result)){
				
				 $query1="select * from tbl_invoicetrn where trancation_status=0 and invoice_id=".$rel['invoice_id'];
				$result1=$dbcon->query($query1);
				while($rel1=mysqli_fetch_assoc($result1)){
					$insert_tax=add_tax_record($dbcon,$rel1['trancation_id'],"tbl_invoicetrn","trancation_id",$rel1['formulaid'],$rel1['taxable_value']);
				}
				 
				add_general_book_entry($dbcon,"tbl_invoice",$rel['invoice_id'],2,$rel['cust_id'],$rel['g_total'],$general_book_id,$rel['invoice_date']);
				
				general_book_tax_entry($dbcon,$rel['invoice_id']);
				general_book_sercices_entry($dbcon,$rel['invoice_id']);
		
			} */
		
		$query_exp="select * from tbl_expense_detail where expense_status=0 and expense_approve_status=1";
			$result_exp=$dbcon->query($query_exp);
			while($rel_exp=mysqli_fetch_assoc($result_exp)){
				//add_general_book_entry($dbcon,"tbl_expense_detail",$rel_exp['ex_id'],1,$rel_exp['vendorid'],$rel_exp['paid_amount'],$general_book_id,$rel_exp['expense_date']);
				
				add_general_book_entry($dbcon,"tbl_expense_detail",$rel_exp['ex_id'],1,$rel_exp['emp_id'],$rel_exp['paid_amount'],$general_book_id,$rel_exp['expense_date']);
			
				add_general_book_entry($dbcon,"tbl_expense_detail_account",$rel_exp['ex_id'],1,$rel_exp['exp_accountid'],$rel_exp['paid_amount'],$general_book_id1,$rel_exp['expense_date']);
			}
			
		/* $query_rec="select * from tbl_receipt where status=0 and payment_type=2";
			$result_rec=$dbcon->query($query_rec);
			while($rel_rec=mysqli_fetch_assoc($result_rec)){
				
				$info1['ref_date']		= date("Y-m-d",strtotime($rel_rec['receipt_date']));
				$info1['table_name']	= "tbl_payment";
				$info1['table_id']		= $rel_rec['receipt_id'];
				$info1['entry_type']	= 2;
				$info1['ledger_id']		= $rel_rec['cust_id'];
				$info1['amount']		= $rel_rec['total_paid_amount'];
				$info1['user_id']		= $rel_rec['user_id'];
				$info1['cdate']			= date("Y-m-d H:i:s");
				$info1['company_id']	= $rel_rec['company_id'];
				$inserid11=add_record("tbl_general_book", $info1, $dbcon);
				
				$info21['ref_date']	= date("Y-m-d",strtotime($rel_rec['receipt_date']));
				$info21['table_name']	= "tbl_payment";
				$info21['table_id']		= $rel_rec['receipt_id'];
				$info21['entry_type']	= 1;
				$info21['ledger_id']	= $rel_rec['payment_mode_id'];
				$info21['amount']		= $rel_rec['total_paid_amount'];
				$info21['user_id']		= $rel_rec['user_id'];
				$info21['cdate']		= date("Y-m-d H:i:s");
				$info21['company_id']	= $rel_rec['company_id'];
				$inserid11=add_record("tbl_general_book", $info21, $dbcon);
			} */
		
		

function general_book_tax_entry($dbcon,$invoice_id,$ref_date){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT utax.*,sum(tax_amount) as tamount FROM `tbl_used_tax` as utax WHERE tax_used_status=0 and used_transaction_id in (".$re["tid"].") and table_name='tbl_invoicetrn' group by ledger_id order by tax_used_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$invoice_id." and table_name='tbl_invoice'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_invoice";
		$info1['table_id']		= $invoice_id;
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['tamount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']	= $_SESSION['company_id'];
		
		if(!empty($re2['general_book_id'])){
			$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon);
		}else{
			$inserid=add_record("tbl_general_book", $info1, $dbcon);
		}
		//var_dump($re2['general_book_id']);
	}
	
}
function general_book_sercices_entry($dbcon,$invoice_id){
	$qry1="select group_concat(trancation_id) as tid from tbl_invoicetrn as cert where trancation_status=0 and invoice_id=".$invoice_id;
	$ro=$dbcon->query($qry1);
	$re=mysqli_fetch_assoc($ro);
	
	$qry122="select * from tbl_invoice as cert where invoice_status=0 and invoice_id=".$invoice_id;
	$ro12=$dbcon->query($qry122);
	$rea=mysqli_fetch_assoc($ro12);
	
	$qry="SELECT itrn.*,promst.ledger_id FROM `tbl_invoicetrn` as itrn 
			left join product_mst as promst on promst.product_id=itrn.product_id
			WHERE itrn.trancation_status=0 and promst.product_type=8 and itrn.invoice_id=".$invoice_id." order by itrn.trancation_id desc";
	$row=$dbcon->query($qry);
	while($tax=mysqli_fetch_assoc($row))
	{
		$qry12="select general_book_id from tbl_general_book as cert where genral_book_status=0 and ledger_id=".$tax['ledger_id']." and table_id=".$tax['trancation_id']." and table_name='tbl_invoicetrn'";
			$ros=$dbcon->query($qry12);
			$re2=mysqli_fetch_assoc($ros);
		
	
		$info1['table_name']	= "tbl_invoicetrn";
		$info1['table_id']		= $tax['trancation_id'];
		$info1['ref_date']		= date("Y-m-d",strtotime($rea['invoice_date']));
		$info1['entry_type']	= 1;
		$info1['ledger_id']		= $tax['ledger_id'];
		$info1['amount']		= $tax['product_amount'];
		$info1['user_id']		= $_SESSION['user_id'];
		$info1['cdate']			= date("Y-m-d H:i:s");
		$info1['company_id']	= $_SESSION['company_id'];
		
		if(!empty($re2['general_book_id'])){
			$updateid=update_record("tbl_general_book", $info1,"general_book_id=".$re2['general_book_id'] , $dbcon);
		}else{
			$inserid=add_record("tbl_general_book", $info1, $dbcon);
		}
		//var_dump($re2['general_book_id']);
	}
	
}

?>