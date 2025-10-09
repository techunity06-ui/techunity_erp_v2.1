<?php
/*
*  SO delivery date - 7 days before balance payment reminder. mail
*/


session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
//include("../config/session.php");
include("../../include/function_database_query.php");
include("../../include/common_functions/common_functions.php");
include("../../include/dashboard_common_functions.php");

$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

$subject = "Payment reminder for Dispatch schedule";



$so_sql = "select so.*,led.cust_email as to_email from tbl_sales_order as so
			left join tbl_ledger as led on led.l_id = so.cust_id
			where sales_order_status != 2 AND approve_status = 3";

$so_res=$dbcon->query($so_sql);

if(brp_mysqli_num_rows($so_res) > 0 ){
	while($so=brp_mysqli_fetch_assoc($so_res)){
		
		$qry = "select * from tbl_sales_ordertrn where 	sales_order_id = " . $so['sales_order_id'];

		$res=$dbcon->query($qry);

		while($so_trn=brp_mysqli_fetch_assoc($res)){

			$qry1 = "select * from tbl_salesorder_delivery_date where sales_ordertrn_id = " . $so_trn['sales_ordertrn_id'];
	
			$res1=$dbcon->query($qry1);

			while($so_date=brp_mysqli_fetch_assoc($res1)){

				$now = time(); // or your date as well

				$delivery_date = strtotime($so_date['delivery_date']);
				$datediff = $delivery_date - $now;
				$day =  round($datediff / (60 * 60 * 24));

				$date=date_create($so_date['delivery_date']);
				
				if($day == 7){

					$message = "<p>Dear Sir, </p>";
					$message.= "<p>Reference to your sales order no ". $so['sales_order_no'] ." </p>";

					$message .= "<p>Your dispatch schedule as indicated will be on ". date_format($date,"d/m/Y")  .".</p>";
					
					
					if(!empty($so['to_email'])){
						$to = array($so['to_email']);
						
						if (filter_var($so['to_email'], FILTER_VALIDATE_EMAIL)) {
						      $is_sent = send_mail($dbcon,$to, $subject, $message, $from_email = "",$ccmail=[], $attachment=[],$bccmail=[],2);
						    }
						

						if($is_sent){
							$info['cdate']	= date("Y-m-d H:i:s");
							$info['details'] = "so_delivery_date";
							add_record('tbl_cron_details', $info, $dbcon);
						}
					}
				}					
			}
		}
	}
} 


?>	