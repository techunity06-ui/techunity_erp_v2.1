<?php
session_start(); //start session
error_reporting(E_ALL); 
ini_set('display_errors', '1');
$AJAX = true;
$path = '../../../';
$include = '../../../include/';
$include1 = '../../include/';
include($path."config/config.php");
include($path."config/session.php");
include($include."function_database_query.php");
include_once(COMMON_FUNCTION_INNER_PATH."common_functions.php");
include_once(COMMON_FUNCTION_INNER_PATH."finance_common_functions.php");

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    //ADMINISTRATOR_LEDGER_DELETE,
]);

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);



if(strtolower($POST['mode']) == "generate_report_bill_sale") 
{
	$str="";$where="";
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	
	$start_date = date("Y-m-d",strtotime($s_date[0]));
	$end_date = date("Y-m-d",strtotime($s_date[1]));
	$cust_id = $POST['cust_id'];

	if($cust_id!='')
	{
		$where.=" and i.cust_id='$cust_id'";
	}

	$q = "select i.invoice_id,l.l_name,i.g_total,i.invoice_no,(select IFNULL(sum(b.bill_amount),0) from tbl_bill_by_bill_adjustment_transaction as b where b.bill_ref_type='0' and b.bill_ref=i.invoice_id) as paid_amount from tbl_invoice as i left join tbl_ledger as l on l.l_id=i.cust_id where i.invoice_status='0' and i.company_id='$_SESSION[company_id]' and i.invoice_date between '$start_date' and '$end_date'".$where;

	$sel = $dbcon->query($q);
	
	$str.="

		<table class='table table-bordered table-hover table-stripped'>

			<tr style='background-color:#337AB7;color:#FFFFFF'>
				<th>#</th>
				<th>Name</th>
				<th>Bill No</th>
				<th>Bill Amount</th>
				<th>Received Amount</th>
				<th>Due Amount</th>
			</tr>

	";

	if(brp_mysqli_num_rows($sel)>0)
	{
		$cnt=1;$total=0;$total_paid=0;$total_due=0;
		while($row=brp_mysqli_fetch_assoc($sel))
		{

			$due_amt = $row['g_total']-$row['paid_amount'];

			$total+=$row['g_total'];
			$total_paid+=$row['paid_amount'];
			$total_due+=$due_amt;

			$str.="

				<tr>
					<th>".$cnt."</th>
					<th>".$row['l_name']."</th>
					<th>".$row['invoice_no']."</th>
					<th>".$row['g_total']."</th>
					<th>".$row['paid_amount']."</th>
					<th>".$due_amt."</th>
				</tr>

			";

			$cnt++;
		}

		$str.="

			<tr style='background-color:#337AB7;color:#FFFFFF'>
				<th colspan='3' style='text-align:right'>Total : </th>
				<th>".$total."</th>
				<th>".$total_paid."</th>
				<th>".$total_due."</th>
			</tr>

		";
	}
	else
	{
		$str.="<tr>

			<th colspan='6' style='text-align:center'>Sorry . No Date Found</th>
		</tr>";
	}

	echo $str;
	
}
else if(strtolower($POST['mode']) == "generate_report_bill_purchase") 
{
	$str="";$where="";
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	
	$start_date = date("Y-m-d",strtotime($s_date[0]));
	$end_date = date("Y-m-d",strtotime($s_date[1]));
	$cust_id = $POST['cust_id'];

	if($cust_id!='')
	{
		$where.=" and p.vender_id='$cust_id'";
	}

	$q = "select p.po_id,l.l_name,p.g_total,p.po_no,(select IFNULL(sum(b.bill_amount),0) from tbl_bill_by_bill_adjustment_transaction as b where b.bill_ref_type='2' and b.bill_ref=p.po_id) as paid_amount from tbl_pono as p left join tbl_ledger as l on l.l_id=p.vender_id where p.status='0' and p.company_id='$_SESSION[company_id]' and p.po_date between '$start_date' and '$end_date'".$where;

	$sel = $dbcon->query($q);
	
	$str.="

		<table class='table table-bordered table-hover table-stripped'>

			<tr style='background-color:#337AB7;color:#FFFFFF'>
				<th>#</th>
				<th>Name</th>
				<th>Bill No</th>
				<th>Bill Amount</th>
				<th>Paid Amount</th>
				<th>Due Amount</th>
			</tr>

	";

	if(brp_mysqli_num_rows($sel)>0)
	{
		$cnt=1;$total=0;$total_paid=0;$total_due=0;
		while($row=brp_mysqli_fetch_assoc($sel))
		{

			$due_amt = $row['g_total']-$row['paid_amount'];

			$total+=$row['g_total'];
			$total_paid+=$row['paid_amount'];
			$total_due+=$due_amt;

			$str.="

				<tr>
					<th>".$cnt."</th>
					<th>".$row['l_name']."</th>
					<th>".$row['po_no']."</th>
					<th>".$row['g_total']."</th>
					<th>".$row['paid_amount']."</th>
					<th>".$due_amt."</th>
				</tr>

			";

			$cnt++;
		}

		$str.="

			<tr style='background-color:#337AB7;color:#FFFFFF'>
				<th colspan='3' style='text-align:right'>Total : </th>
				<th>".$total."</th>
				<th>".$total_paid."</th>
				<th>".$total_due."</th>
			</tr>

		";
	}
	else
	{
		$str.="<tr>

			<th colspan='6' style='text-align:center'>Sorry . No Date Found</th>
		</tr>";
	}

	echo $str;
	
}
		

?>