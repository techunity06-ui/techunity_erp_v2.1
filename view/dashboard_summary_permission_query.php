<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");


$qry = "SELECT FROM menu_master_access where  menu_name = 'DAILY EMAIL DASHBOARD SUMMARY' AND menu_description = 'DAILY EMAIL DASHBOARD SUMMARY'";
$query_res=$dbcon->query($qry);
// $to = array();

if(brp_mysqli_num_rows($query_res) == 0 ){


$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','','DAILY EMAIL DASHBOARD SUMMARY','#','DAILY EMAIL DASHBOARD SUMMARY','','','','No','2022-01-13 12:39:56','')";

		$dbcon->query($qry1);
		$insert_id=mysqli_insert_id($dbcon);


$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY PURCHASE','#','show todays purchase summary in email templete','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

 $qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-purchase-view','','2022-01-13 12:46:20')";
 	$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TILL DATE PURCHASE','#','show till date purchase summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','till-date-purchase-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY SALE','#','show todays sale summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-sale-view','','2022-01-13 12:46:20')";
	$dbcon->query($qry1);


$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TILL_DATE_SALE','#','show till date sale summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);


$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','till-date-sale-view','','2022-01-13 12:46:20')";
	$dbcon->query($qry1);


$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY INQUIRY ADD','#','show todays inquiry summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-inquiry-add-view','','2022-01-13 12:46:20')";
	$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY QUOTATION CREATE','#','show todays quotation summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-quotation-create-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY PENDING INQUIRY FOLLOWUP','#','show todays pending inquiry followup summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-pending-inquiry-followup-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);



$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY PENDING QUOTATION FOLLOWUP','#','show todays pending quotation followup summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-pending-quotation-followup-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY SO PLANNING PENDING','#','show todays so planning pending summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-so-planning-pending-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);


$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY INDENT APPROVED PENDING','#','show todays indent approved pending summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-indent-approved-pending-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY PO CREATED','#','show todays po created summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-po-created-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY PO PENDING','#','show todays po pending summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-po-pending-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY PENDING GRN','#','show todays pending GRN summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-pending-grn-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);


$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY INQUIRY WON','#','show todays inquiry won summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-inquiry-won-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

$qry1="INSERT INTO menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag,created_at,status) VALUES ('1','".$insert_id."','TODAY TOTAL AMOUNT WON INQUIRY','#','show todays total amount won inquiry summary in email template','','','','No','2022-01-13 12:46:20','')";
		$dbcon->query($qry1);
		$insert_sub_id=mysqli_insert_id($dbcon);

$qry1="INSERT INTO menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name,created_at) VALUES ('1','".$insert_sub_id."','V','today-total-amount-won-inquiry-view','','2022-01-13 12:46:20')";
$dbcon->query($qry1);

echo "menu permission created";
}else{
	echo "menu permission already created";
}
exit;
?>