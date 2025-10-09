<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions/common_functions.php");
	include("../include/function_database_query.php");

	//select user id 

	$sel1 = $dbcon->query("select user_id from users where user_type='2'");
	$row1 = brp_mysqli_fetch_array($sel1);
	$user_id = $row1['user_id'];

	$query = "select * from tbl_ledger where cust_id='0' and l_status!='2' and default_ledger='0' and default_sundry='0'";
	$rs_que = $dbcon->query($query);
	$cnt=1;
	while($row = mysqli_fetch_array($rs_que))
	{
		$sel2 = $dbcon->query("select ledger_id from tbl_customer where ledger_id='$row[l_id]'");
		if(brp_mysqli_num_rows($sel2)==0)
		{

			$info_crm['cust_name']		= $row['l_name'];
			$info_crm['cust_creator']	= $user_id;
			$info_crm['cust_code']		= get_customer_code($dbcon);//Generate New Code
			$info_crm['cust_code_series']= get_customer_code_series($dbcon);//Generate New Code
			$info_crm['cust_gst']		= $row['gst_no'];
			$info_crm['cust_mobile']	= $row['cust_mobile'];
			$info_crm['cust_email']		= $row['cust_email'];
			$info_crm['account_terms']		= $row['pay_terms'];
			$info_crm['account_credit_limit']		= $row['credit_limit'];
			$info_crm['account_credit_days']		= $row['credit_days'];
			$info_crm['ledger_id'] = $row['l_id'];
			
			$info_crm['cdate']			= date("Y-m-d H:i:s");
			$info_crm['user_id']		= $_SESSION['user_id'];
			$info_crm['company_id']			= $_SESSION['company_id'];

			echo $cnt."--".$row['l_name']."<br>";

			$inserid_crm=add_record('tbl_customer', $info_crm, $dbcon,$row['branch_id']);	

			$info_ledger['cust_id'] = $inserid_crm;

			update_record('tbl_ledger',$info_ledger,"l_id=".$row['l_id'],$dbcon,'');

			$cnt++;
		}
	}
	
?>