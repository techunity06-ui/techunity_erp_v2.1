<?php
session_start(); //start session
$AJAX = true;
include("../config/config.php");
//error_reporting(E_ALL);
//include("../config/session.php");
include("../include/function_database_query.php");
include("../include/common_functions.php");
include("../include/dashboard_common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$to = ['metr.umair@gmail.com'];
$subject = "Pending Work List : ".date('d-M-Y');

$company_sql = "select comp.company_id, comp.user_id, (select user_type from users where user_id=comp.user_id and active=0) as user_type from tbl_company as comp  where comp.com_status=0";
$query_res=$dbcon->query($company_sql);

if(brp_mysqli_num_rows($query_res) > 0 ){
	while($comp_data=brp_mysqli_fetch_assoc($query_res)){

			$_SESSION['company_id'] = $comp_data['company_id'];
			$_SESSION['user_id'] = $comp_data['user_id'];
			$_SESSION['user_type'] = $comp_data['user_type'];
			$body = include("cron_email_template.php");	

			send_mail($dbcon,$to, $subject, $body, $from_email = "",$ccmail=[], $attachment=[],$bccmail=['metr.umair@gmail.com']);
	}
}

?>	

