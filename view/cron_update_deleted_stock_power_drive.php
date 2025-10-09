<?php


session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH . "common_functions.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");
$company_config = getCompanyConfiguration($dbcon);

$qry1 = "SELECT * FROM `tbl_general_stock_trn` WHERE status=2";
$result1 = $dbcon->query($qry1);
if (brp_mysqli_num_rows($result1) > 0) {
	while ($rel = brp_mysqli_fetch_assoc($result1)) {
		$qry2 = "SELECT * FROM `tbl_reserve_stock` WHERE stock_status !=2 and ref_name='production_bypass' and ref_id= " . $rel['general_stock_trn_id'];
		
		$result2 = $dbcon->query($qry2);

		if (brp_mysqli_num_rows($result2) > 0) {

			while ($rel2 = brp_mysqli_fetch_assoc($result2)) {

				$info = array();
				$info['stock_status'] = 2;
				$updateid = update_record("tbl_reserve_stock", $info, "reserve_id=" . $rel2['reserve_id'], $dbcon);

				if ($updateid) {
					$qry3 = "select * from tbl_stock_trn where stock_id =" . $rel2['stock_id'];
					
					$result3 = $dbcon->query($qry3);

					if (brp_mysqli_num_rows($result3) > 0) {
						$rel3 = brp_mysqli_fetch_assoc($result3);

						$info1 = array();
						$info1['used_base_stock'] = $rel3['used_base_stock'] - $rel2['base_stock'];
						$info1['used_convert_stock'] = $rel3['used_convert_stock'] - $rel2['convert_stock'];
						$updateid1 = update_record("tbl_stock_trn", $info1, "stock_id=" . $rel3['stock_id'], $dbcon);
					}
				}
			}
		}
	}
	echo "Successfully updated!";
} else {
	echo "no records found ::";
}
