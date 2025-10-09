<?php
session_start(); //start session
error_reporting(0); 
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



if(strtolower($POST['mode']) == "generate_aging_payable") 
{
	$ledgers = get_ledger_by_group_new($dbcon,37,38);
	$bill_status_on = date("Y-m-d",strtotime($POST['bill_status_on']));
	//echo $bill_status_on;exit;

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	
	$start_date = date("Y-m-d",strtotime($s_date[0]));
	$end_date = date("Y-m-d",strtotime($s_date[1]));

	$ledgers_arr = explode(",",$ledgers);

	$check_qry='';$aging_check='';$pay_qry='';$check_final_total_qry='';
	$qsel=$dbcon->query("select * from tbl_aging_slab where isdelete='0' and company_id='$_SESSION[company_id]'");
	while($rsel = brp_mysqli_fetch_assoc($qsel))
	{
		$check_str = $rsel['slab_start_day']."".$rsel['slab_end_day'];
		$check_qry.="SUM(IF(DATEDIFF(CURDATE(), p.po_date ) BETWEEN '$rsel[slab_start_day]' AND '$rsel[slab_end_day]', g_total, 0)) AS Age$check_str,";
		$pay_qry.="(select SUM(IF(DATEDIFF(CURDATE(), b.cdate ) BETWEEN '$rsel[slab_start_day]' AND '$rsel[slab_end_day]', b.bill_amount, 0)) from tbl_bill_by_bill_adjustment_transaction as b where b.bill_ref_type='2' and  b.bill_ledger_id=p.vender_id  and b.isdelete='0') AS Pay$check_str,";
		//$check_final_total_qry.="Age$check_str-Pay$check_str as final$check_str,";
		

		$aging_check.= "<th>".$rsel['slab_start_day']."-".$rsel['slab_end_day']."</th>";		
	}

	
	$q1 = "SELECT l.l_name,p.vender_id,".$check_qry.$pay_qry."
   
    SUM(p.g_total) AS totalBalance
	FROM tbl_pono as p
	left join tbl_ledger as l on l.l_id=p.vender_id
	WHERE
		p.g_total  > 0 and p.po_date between '$start_date' and '$end_date'

	GROUP BY l.l_name  
	ORDER BY totalBalance DESC";

	$str="";

	$str.="<table class='table table-bordered'>";

	$str.="<tr style='background-color:#3E4C85;color:#FFFFFF'>

		<th>#</th>
		<th>Customer</th>
		".$aging_check."
		<th>Total</th>
	</tr>";

	$sel1= $dbcon->query($q1);
	$cnt=1;

	//echo "<pre>";print_r(brp_mysqli_fetch_array($sel1));exit;

	while($row1=brp_mysqli_fetch_array($sel1))
	{

		//$total = $row1[1]+$row1[2]+$row1[3]+$row1[4]+$row1[5];

		$str.="<tr>

			<th>".$cnt."</th>
			<th>".$row1[0]."</th>";
		?>

		<?php
			$qsel=$dbcon->query("select * from tbl_aging_slab where isdelete='0' and company_id='$_SESSION[company_id]'");
			$final_aging=0;
			while($rsel1 = brp_mysqli_fetch_assoc($qsel))
			{
				$checkstr = $rsel1['slab_start_day'].$rsel1['slab_end_day'];

				$total_aging = $row1['Age'.$checkstr]-$row1['Pay'.$checkstr];
				$final_aging+=$total_aging;
				$str.="<th>".$total_aging."</th>";
			}
			
			$str.="<th>".$final_aging."</th>

		</tr>";

		$cnt++;
	}
	
	echo $str;
	
}
else if(strtolower($POST['mode']) == "generate_aging_receivable") 
{
	$ledgers = get_ledger_by_group_new($dbcon,37,38);
	//echo $bill_status_on;exit;

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	
	$start_date = date("Y-m-d",strtotime($s_date[0]));
	$end_date = date("Y-m-d",strtotime($s_date[1]));

	$ledgers_arr = explode(",",$ledgers);

	$check_qry='';$aging_check='';
	$qsel=$dbcon->query("select * from tbl_aging_slab where isdelete='0' and company_id='$_SESSION[company_id]'");
	while($rsel = brp_mysqli_fetch_assoc($qsel))
	{
		$check_str = $rsel['slab_start_day']."".$rsel['slab_end_day'];
		$check_qry.="SUM(IF(DATEDIFF(CURDATE(), i.invoice_date ) BETWEEN '$rsel[slab_start_day]' AND '$rsel[slab_end_day]', i.g_total, 0)) AS Age$check_str,";
		$pay_qry.="(select SUM(IF(DATEDIFF(CURDATE(), b.cdate ) BETWEEN '$rsel[slab_start_day]' AND '$rsel[slab_end_day]', b.bill_amount, 0)) from tbl_bill_by_bill_adjustment_transaction as b where b.bill_ref_type='0' and  b.bill_ledger_id=i.cust_id and b.isdelete='0') AS Pay$check_str,";

		$aging_check.= "<th>".$rsel['slab_start_day']."-".$rsel['slab_end_day']."</th>";		
	}

	
	$q1 = "SELECT l.l_name,i.cust_id,".$check_qry.$pay_qry."
   
    SUM(i.g_total) AS totalBalance
	FROM tbl_invoice as i 
	left join tbl_ledger as l on l.l_id=i.cust_id
	WHERE
		i.g_total  > 0 and i.invoice_date between '$start_date' and '$end_date'

	GROUP BY l_name  
	ORDER BY totalBalance DESC";

	$str="";

	$str.="<table class='table table-bordered'>";

	$str.="<tr style='background-color:#3E4C85;color:#FFFFFF'>

		<th>#</th>
		<th>Customer</th>
		".$aging_check."
		<th>Total</th>
	</tr>";

	$sel1= $dbcon->query($q1);
	$cnt=1;
	$row_count = brp_mysqli_num_rows($sel1);

	while($row1=brp_mysqli_fetch_array($sel1))
	{

		$str.="<tr>

			<th>".$cnt."</th>
			<th><a>".$row1[0]."</a></th>";
		?>

		<?php
			$qsel=$dbcon->query("select * from tbl_aging_slab where isdelete='0' and company_id='$_SESSION[company_id]'");
			$final_aging=0;
			while($rsel1 = brp_mysqli_fetch_assoc($qsel))
			{
				$checkstr = $rsel1['slab_start_day'].$rsel1['slab_end_day'];

				$total_aging = $row1['Age'.$checkstr]-$row1['Pay'.$checkstr];
				$final_aging+=$total_aging;
				$str.="<th>".$total_aging."</th>";
			}
			
			$str.="<th>".$final_aging."</th>

		</tr>";

		$cnt++;
	}
	
	echo $str;
	
}
		

?>