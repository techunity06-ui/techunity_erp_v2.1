<?php 
session_start();
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
include("../include/function_database_query.php");

$query = "SELECT p_id,release_qty,material_id FROM tbl_material_release WHERE status  = 0 AND product_id = 0";
$result = $dbcon->query($query);
$cnt = brp_mysqli_num_rows($result);
while($row = brp_mysqli_fetch_array($result)){
	$q2 = "SELECT material_trn_id FROM tbl_material_release_trn WHERE status = 0 and release_status = 1 and p_id = " . $row['p_id'];

	$res3 = $dbcon->query($q2);
	$ap_cnt = brp_mysqli_num_rows($res3);
	$release_qty = $row['release_qty'];
	if($ap_cnt == 0){
		$ap_qry = "SELECT p_product_id,p_ref_id FROM tbl_allocate_process WHERE p_id = " . $row['p_id'];
		$ap_result = $dbcon->query($ap_qry);
		$ap_row = brp_mysqli_fetch_assoc($ap_result);

		$query_dstock = "select i.*,(cast(base_stock AS DECIMAL(10,5)) - IFNULL((select sum(base_stock) from tbl_process_reserve_stock where stock_status = 0  and   p_id=". $row['p_id'] ." and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_base_stock,(cast(conv_stock AS DECIMAL(10,5)) - IFNULL((select sum(conv_stock) from tbl_process_reserve_stock where stock_status = 0 and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_conv_stock from tbl_process_reserve_stock as i where stock_status=0 and stock_flage=1 and i.product_id=".$ap_row['p_product_id']." and p_id = " . $row['p_id'];

			$result_dstock=$dbcon->query($query_dstock);
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
						// var_dump('--->'.$release_qty);
				// $trn_info['batch_no']		= $row_dstock['batch_no'];	
				$trn_info['godown_id']		= $row_dstock['godown_id'];	

				$trn_info['product_id']		= $ap_row['p_product_id'];
				
				$trn_info['to_godown_id']	= $row_dstock['godown_id'];	
				
				$trn_info['release_status'] = 1;
				$trn_info['batch_no'] = 1;
				$trn_info['rp_id']			= $ap_row['p_ref_id'];
				$trn_info['parent_rp_id']	= $ap_row['p_ref_id'];
				$trn_info['p_id']			=  $row['p_id'];
			
				$trn_info['cdate']				= date('Y-m-d H:i:s');
				$trn_info['user_id']			= $_SESSION['user_id'];	
				$trn_info['company_id']		= $_SESSION['company_id'];	
				$trn_info['base_unit']		= $row_dstock['base_unit'];
				$trn_info['conv_unit']		= $row_dstock['conv_unit'];
			
				$trn_info['status'] = 0;
				$trn_info['material_id'] = $row['material_id'];
				// $trn_info['batch_no'] = $batch_row['batch_no'];
				
				$trn_info['to_user_id'] = $_SESSION['user_id'];;
				// $trn_info['start_stop_id'] = $start_stop_id;
				
				$pending_stock=$row_dstock['pending_base_stock'];	
				
				if($release_qty>0){
					if($pending_stock>=$release_qty){
						$rqty=$release_qty;
						$release_qty=$release_qty-$release_qty;
					}else{
						$rqty=$pending_stock;
						$release_qty=$release_qty-$pending_stock;
					}
			
					$type="conv_unit";
					$base_stock=$rqty;
					$con_stock=convert_stock_new($dbcon,$rqty,$row['p_product_id'],$type);
					
					$trn_info['base_qty']		= $base_stock;
					$trn_info['conv_qty']		= $con_stock;
					$trn_info['stock_id']		= $row_dstock['process_stock_id'];
					
					$inserpoid=add_record('tbl_material_release_trn',$trn_info, $dbcon);

					echo "p_id :: ". $row['p_id'];
					echo "</br>";
				}
			}
	}

} 


?>
