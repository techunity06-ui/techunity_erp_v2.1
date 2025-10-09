<?php
session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	
	//SHOW TABLES FROM brp_erp_db_new_2
	//SHOW TABLES FROM brp_erp_db_new_2 where Tables_in_brp_erp_db_new_2="account_voucher_mst"
	//$qry3="SHOW COLUMNS FROM account_mst";
	
	
	// $qry3="SHOW COLUMNS FROM account_mst where Field='acc_type'";
		/* $result3=$dbcon->query($qry3);
		while($rel3=brp_mysqli_fetch_assoc($result3)){
			echo $rel3['Field']." --- ".$rel3['Type'];
			echo "</br>";
			
			
		}  */
		
		
		
	//start table match pathik 16-07-2021
		$find_db_name="Tables_in_".DB;
		 $qry="SHOW TABLES FROM ".DB." where ".$find_db_name."='account_voucher_mst_test'";
		$result=$dbcon->query($qry);
		$rel=brp_mysqli_fetch_assoc($result);
		echo $rel[$find_db_name];
		if($rel[$find_db_name]){
			 $qry_col="SHOW COLUMNS FROM account_voucher_mst where Field='report_status'";
			 $result_col=$dbcon->query($qry_col);
			 $rel_col=brp_mysqli_fetch_assoc($result_col);
			 if(!empty($rel_col['Field'])){
				 echo $rel_col['Field'];
				// ALTER TABLE `tbl_allocate_re_process_trn` CHANGE `pt_qty` `pt_qty` VARCHAR(255) NOT NULL;
			}else{
				 $qry_al="ALTER TABLE `account_voucher_mst` ADD `report_status` INT NOT NULL AFTER `voucher_typeid`";
				 $result_al=$dbcon->query($qry_al);
			}
		}else{
			$qry_al="CREATE TABLE IF NOT EXISTS `account_voucher_mst_test` (
			  `voucher_mstid` int(11) NOT NULL AUTO_INCREMENT,
			  `voucher_typeid` int(11) NOT NULL,
			  `report_status` int(11) NOT NULL,
			  `voucher_date` date NOT NULL,
			  `voucher_no` varchar(20) NOT NULL,
			  `remark` varchar(250) NOT NULL,
			  `g_total` decimal(10,2) NOT NULL,
			  `mst_status` int(11) NOT NULL,
			  `cdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			  `user_id` int(11) NOT NULL,
			  `company_id` int(11) NOT NULL,
			  PRIMARY KEY (`voucher_mstid`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3" ;
			
			 $result_al=$dbcon->query($qry_al);
		}
		
	//end table match pathik 16-07-2021
	
?>