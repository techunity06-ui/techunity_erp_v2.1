<?php
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");

$users = '';
$companyConfiguration=getCompanyConfiguration($dbcon);
$crm_user_type = $companyConfiguration['crm_user_type'];
$qry = $dbcon->query("SELECT * FROM tbl_usertype WHERE status = 0 AND company_id IN (0,".$_SESSION['company_id'].") AND usertype_id IN (".$crm_user_type.")");
	while($row = mysqli_fetch_assoc($qry)){
		if($row['usertype_name']!='Admin'){
			$infousern['field_name']=$row['usertype_name'].' - USER_NAME';
			$infousern['table_name']='users';
			$infousern['replace_with']='user_name';
			$infousern['primary_id']='user_id';
			$infousern['module_id']='2';
			$infousern['cdate']	= date("Y-m-d H:i:s");
			$infousern['user_id'] = $_SESSION['user_id'];
			$infousern['company_id'] = $_SESSION['company_id'];
			$infousernid=add_record('email_merge_fields', $infousern, $dbcon);

			$infousere['field_name']=$row['usertype_name'].' - USER_EMAIL';
			$infousere['table_name']='users';
			$infousere['replace_with']='common_email_id';
			$infousere['primary_id']='user_id';
			$infousere['module_id']='2';
			$infousere['cdate']	= date("Y-m-d H:i:s");
			$infousere['user_id'] = $_SESSION['user_id'];
			$infousere['company_id'] = $_SESSION['company_id'];
			$infousereid=add_record('email_merge_fields', $infousere, $dbcon);

			$infouserm['field_name']=$row['usertype_name'].' - USER_MOBILE';
			$infouserm['table_name']='users';
			$infouserm['replace_with']='user_phone';
			$infouserm['primary_id']='user_id';
			$infouserm['module_id']='2';
			$infouserm['cdate']	= date("Y-m-d H:i:s");
			$infouserm['user_id'] = $_SESSION['user_id'];
			$infouserm['company_id'] = $_SESSION['company_id'];
			$infousermid=add_record('email_merge_fields', $infouserm, $dbcon);

			$users.=$row['usertype_name'].'<br>';
		}
	}
	echo $users;
?>