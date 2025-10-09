<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");
$godown_id = 23;
$query = "SELECT p_id FROM tbl_allocate_process WHERE p_status = 1";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){

	$ap_qry = "SELECT p_product_id FROM tbl_allocate_process WHERE p_id = " . $row['p_id'];
	$ap_result = $dbcon->query($ap_qry);
	$ap_row = brp_mysqli_fetch_assoc($ap_result);
	
	$info1['release_no']		= load_common_no($dbcon,RELEASE_MATERIAL);
	$info1['release_date']		= date('d-m-Y',strtotime($POST['release_date']));
	$info1['to_godown_id']		= $godown_id ;
	$info1['to_user_id']		= $row['user_id'];	
	$info1['rp_id']				= $row['rp_id'];
	$info1['product_id']		= $ap_row['p_product_id'];
	$info1['release_qty']		= $row['release_qty'];
	$info1['release_unit']		= $row['release_unit'];
	$info1['process_id']		= $row['process_id'];	
	$info1['p_id']				= $row['p_id'];	

	$info1['cdate']				= date('Y-m-d H:i:s');
	$info1['user_id']			= $row['user_id'];	
	$info1['company_id']		= $row['company_id'];	
	
	$material_id = add_record('tbl_material_release',$info1, $dbcon);

	if($material_id){
		update_common_no($dbcon,RELEASE_MATERIAL);

		$query1 = "SELECT * FROM tbl_store_release_material_trn where release_id = " .$row['release_id'];
		$result1 = $dbcon->query($query1);
		while($row1 = brp_mysqli_fetch_assoc($result1)){

			$query2 = "SELECT p_ref_id,previous_process_id FROM tbl_allocate_process WHERE p_id = " . $row1['p_id'];
			$result2 = $dbcon->query($query2);
			$p_rw = brp_mysqli_fetch_assoc($result2);

			if($p_rw['previous_process_id'] == '0'){

			$rp_qry = "SELECT rp_id from tbl_request_product WHERE rp_pid = " . $row1['product_id'] . " and perent_id = " . $p_rw['p_ref_id'];
				$rp_rw = brp_mysqli_fetch_assoc($dbcon->query($rp_qry));
				$info2['rp_id']			= $rp_rw['rp_id'];	
				$info2['parent_rp_id']	= $p_rw['p_ref_id'];
			}else{
				
				$info2['rp_id']			=$p_rw['p_ref_id'];
				$info2['parent_rp_id']	= $p_rw['p_ref_id'];
			}
			

			$info2['material_id'] = $material_id;
			$info2['to_godown_id'] = $godown_id;
			$info2['to_user_id'] = $row1['user_id'];
			$info2['start_stop_id'] = $start_stop_id;

			$info2['product_id']	= $row1['product_id'];
			$info2['p_id']			= $row1['p_id'] ;
			$info2['godown_id']		= $godown_id;
			$info2['cdate']			= date('Y-m-d H:i:s');
			$info2['user_id']		= $row1['user_id'];	
			$info2['company_id']	= $row1['company_id'];	
			$info2['base_unit']		= $row['release_unit'];
			$info2['conv_unit']		= $row['release_conv_unit'];
			$info2['status']		= 0;
			$info2['release_status']		= 1;
			// $info2['batch_no']		= $batch_no;
			$info2['base_qty']		= $row1['release_qty'];
			$info2['conv_qty']		= $row1['release_conv_qty'];
			// $info2['stock_id']		= $row_dstock['process_stock_id'];
			// $info2['stock_id']		= $row_dstock['stock_id'];
			
			$inserpoid=add_record('tbl_material_release_trn',$info2, $dbcon);
		}
		
	}
	$dbcon->query("update tbl_store_release set cron_status = 1 where release_id = " . $row['release_id']);
} 


?>