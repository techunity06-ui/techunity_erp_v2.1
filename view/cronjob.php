<?php

session_start(); //start session
$AJAX = true;
include("../config/config.php");
//error_reporting(E_ALL);
//include("../config/session.php");
include("../include/function_database_query.php");
include("../include/common_functions/common_functions.php");
include("../include/dashboard_common_functions.php");
include_once("../include/common_send_email.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$subject = "Pending Work List : ".date('d-M-Y');

// Sanat :: Added sending email condtion  -  10-08-2021

/*$company_sql = "select comp.company_id, comp.user_id, (select user_type from users where user_id=comp.user_id and active=0) as user_type, send_email from tbl_company as comp  where comp.com_status=0";*/

$company_sql = "SELECT cron.send_email_id,c.send_email, cron.email_user_id, u.user_name, u.common_email_id, cron.director_name, cron.email,cron.company_id FROM tbl_cron_send_email as cron 
left join users as u on u.user_id = cron.email_user_id 
left join tbl_company as c on c.company_id = cron.company_id 
where  cron.status = 0";
$query_res=$dbcon->query($company_sql);
// $to = array();

if(brp_mysqli_num_rows($query_res) > 0 ){
	while($comp_data=brp_mysqli_fetch_assoc($query_res)){

			// $_SESSION['company_id'] = $comp_data['company_id'];
			// $_SESSION['user_id'] = $comp_data['email_user_id'];
			// $_SESSION['user_type'] = $comp_data['user_type'];

			$company_id = $comp_data['company_id'];
			$user_id = $comp_data['email_user_id'];

			$qry = "select sending_blue_api_key,sendinblue_mail_id from tbl_company_configuration where company_id = " . $company_id;
			$res=$dbcon->query($qry);

			$sendin_blue = brp_mysqli_fetch_assoc($res);
	// print_r($comp_data);
			if($comp_data['send_email'] == '1'){
				// print_r($sendin_blue);
			
			/*
				$qry =  "SELECT * FROM `tbl_cron_send_email` where company_id = " .$comp_data['company_id'] . " AND user_id = " . $comp_data['user_id'] . " and status = 0 ";

				$res=$dbcon->query($qry);
				if(brp_mysqli_num_rows($res) > 0 ){
					while($email_data=brp_mysqli_fetch_assoc($res)){*/

						$body = "";
						
						$body = include("cron_email_template.php");
						
						// $to = array();
						$to_email_id = $comp_data['common_email_id'];
					// 
				    	// array_push($to,$comp_data['common_email_id']);
					// }
				    	// var_dump($to);
						if($sendin_blue['sending_blue_api_key'] != ""){
							$from_email_id = $sendin_blue['sendinblue_mail_id'];
							// var_dump($from_email_id);
							$return_data = final_send_email($from_email_id, $to_email_id, array(),array(), $subject, $body);	
							if($return_data['code'] != 'success'){
								send_mail($dbcon,[$to], $subject, $body, $from_email = "",$ccmail=[], $attachment=[],$bccmail=[]);
							}
							
						}else{
							send_mail($dbcon,[$to], $subject, $body, $from_email = "",$ccmail=[], $attachment=[],$bccmail=[]);	
						}
					 
					
				// }
					
			}
			
	}
} 


?>	
