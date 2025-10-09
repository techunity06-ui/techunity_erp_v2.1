<?php
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");

	$query="select * from wip_stock_allocate as trn where trn.status=0 and cast(trn.allocate_base_qty AS DECIMAL(10,5)) > cast(trn.allocate_base_qty_used AS DECIMAL(50,5)) and trn.stock_flag = 1 and trn.company_id=".$_SESSION['company_id'];
	$sel=$dbcon->query($query);
	$cnt = 1;
	while($row = brp_mysqli_fetch_array($sel)){
		
		$q1 = "SELECT process_id,process_priority FROM `tbl_wororder_product_process` WHERE `rp_id` = ".$row['rp_id']." order by process_priority desc limit 1";
		$row1 = brp_mysqli_fetch_array($dbcon->query($q1));

		$q2 = "select * from tbl_allocate_process where p_status !=2 and p_ref_id = " . $row['rp_id'] . " and process_id = " . $row1['process_id'];
		$res2 = $dbcon->query($q2);
		while($row2 = brp_mysqli_fetch_array($res2)){
			$q3  = "select * from tbl_job_work_sub_trn where job_work_sub_trn_status = 0 and p_id = ". $row2['p_id'] . " and rp_id = " . $row2['p_ref_id'];
		$res3 = $dbcon->query($q3);	
		while($row3 = brp_mysqli_fetch_array($res3)){

			$q4 = "select * from tbl_grn_sub_trn where job_work_sub_trn_id = " . $row3['job_work_sub_trn_id'] . " and job_work_trn_id = " . $row3['job_work_trn_id'] . " and rp_id = " . $row3['rp_id'];
			$res4 = $dbcon->query($q4);
			while($row4 = brp_mysqli_fetch_array($res4)){

			echo	$q5 = "select * from tbl_grn_trn where grn_trn_status = 0 and grn_trn_id = " . $row4['grn_trn_id']; 
					echo "</br></br>";
				$res5 = $dbcon->query($q5);
				while($row5 = brp_mysqli_fetch_array($res5)){

					if($row5['product_qc'] == '1' && $row5['store_accept'] == '1'){
						$info = array();
						$info['allocate_base_qty_used'] = $row['allocate_base_qty_used'] + $row4['product_qty'];
						$info['allocate_conv_qty_used'] = $row['allocate_conv_qty_used'] + $row4['product_conv_qty'];

						if($info['allocate_base_qty_used'] >= $row['allocate_base_qty']){
							$info['status'] = 1;
						}

						$updateid1 =update_record("wip_stock_allocate", $info, "wip_stock_allocate_id=".$row['wip_stock_allocate_id'] ." and rp_id = " . $row['rp_id'], $dbcon);

						var_dump("rp_id :". $row['rp_id'] . " - update id :  " .$updateid1);
					}
				}
			}
		}
		}

	}

	if($updateid1){
		echo "Cron Run SuccessFully.....!!!!";
	}
?>