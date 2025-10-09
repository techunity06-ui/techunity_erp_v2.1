<?
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "SELECT * FROM tbl_sales_ordertrn WHERE sales_ordertrn_status = 0 AND invoice_status = 1 AND remaning_invoice_qty = 0";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
echo	$wo_qry = "SELECT sp_id FROM tbl_set_main_process WHERE so_cron = 0 AND finish_status = 0 AND sales_order_trn_id = " . $row['sales_ordertrn_id'];	

	$wo_result = $dbcon->query($wo_qry);
	$wo_cnt = brp_mysqli_num_rows($wo_result);

	if($wo_cnt > 0){
		while($wo_row = brp_mysqli_fetch_array($wo_result)){
		echo	$req_qry = "SELECT rp_id FROM tbl_request_product WHERE  so_cron = 0 AND  finish_status = 0 AND sp_id = " . $wo_row['sp_id'];	

			$req_result = $dbcon->query($req_qry);
			$req_cnt = brp_mysqli_num_rows($req_result);

			if($req_cnt > 0){
				while($req_row = brp_mysqli_fetch_array($req_result)){
				echo	$ap_qry = "SELECT p_id FROM tbl_allocate_process WHERE p_status in (0,1) AND p_ref_id = " . $req_row['rp_id'];

					$ap_result = $dbcon->query($ap_qry);
					$ap_cnt = brp_mysqli_num_rows($ap_result);

					if($ap_cnt > 0){
						while($ap_row = brp_mysqli_fetch_array($ap_result)){
							$dbcon->query("update tbl_allocate_process set p_status = 3 where p_id = " . $ap_row['p_id']);
							
							
							$sr_ap_qry = "SELECT * FROM tbl_store_request WHERE store_request_status = 0 AND p_id = ".$ap_row['p_id'];

							$sr_ap_result = $dbcon->query($sr_ap_qry);
							$sr_ap_cnt = brp_mysqli_num_rows($sr_ap_result);

							if($sr_ap_cnt > 0){
								while($sr_ap_row = brp_mysqli_fetch_array($sr_ap_result)){
									$dbcon->query("update tbl_store_request set store_request_status = 1,release_qty=".$sr_ap_row['base_qty']."  where store_request_id = " . $sr_ap_row['store_request_id']);
								}
							}

							echo "p_id : " . $ap_row['p_id'];
						}
					}
					$dbcon->query("update tbl_request_product set finish_status = 1,so_cron = 1 where rp_id = " . $req_row['rp_id']);
					echo " ::  rp_id : " . $req_row['rp_id'];
				}
			}
			$dbcon->query("update tbl_set_main_process set finish_status = 1,so_cron = 1 where sp_id = " . $wo_row['sp_id']);
			echo " ::  sp_id : " . $wo_row['sp_id'];
		}
	}
	echo " ::  salesorder : " . $row['sales_ordertrn_id'];

	echo "</br></br>";

} 


?>
