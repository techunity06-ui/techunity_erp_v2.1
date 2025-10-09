<?php
	session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);
	 $qry1="select grn.rp_id,grn.rp_pid,grn.product_version,grn.process_unit,grn.in_process_qty,grn.branch_id,ap.p_id from tbl_request_product as grn
	 		left join tbl_allocate_process as ap on ap.p_ref_id=grn.rp_id
			where grn.main_request=1 and grn.in_process_qty>0 and grn.status!=2";
	$result1=$dbcon->query($qry1);
	while($rel=brp_mysqli_fetch_assoc($result1)){
		if(empty($rel['p_id'])){
			var_dump($rel['rp_id']);
			$process=get_product_process($dbcon,$rel['rp_id'],$rel['rp_pid']);
					$process_pr=json_decode($process);
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo=get_resource_from_product_process($dbcon,$rel['rp_pid'],$process_id, $where=null);

					$info5['process_id']			= $process_id;			
					$info5['p_start_time']			= '';		
					$info5['p_end_time']			= '';		
					$info5['p_qty']					= $rel['in_process_qty'];		
					$info5['pen_qty']				= $rel['in_process_qty'];		
					$info5['process_unit']			= $rel['process_unit'];		
					$info5['p_ref_id']				= $rel['rp_id'];		
					$info5['p_ref_type']			= 'process request';		
					$info5['p_product_id']			= $rel['rp_pid'];		
					$info5['pr_process_type']		= $process_type;		
					$info5['process_priority']		= $process_priority;		
					$info5['previous_process_id']	= 0;
					$info5['product_version']		= $rel['product_version'];
					$info5['extra_stock'] 			= $extra_stock;
					$info5['ext_stock_vendor_id'] 	= $ext_stock_vendor_id;

					if($resourceinfo['process_type']=='1'){		
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}	

					
					if($company_config['batch_wise_stock'] == '1' &&  $company_config['batch_process'] == '0' && $setpro_rel['batch_wise_stock_manage'] == '1'){
						$info5['batch_process_start_time'] = 1;
					}

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon,$rel['branch_id']);

					$query_reserve="select * from tbl_request_product where status=0 and perent_id=".$rel['rp_id'];
						$rs_reserve=$dbcon->query($query_reserve);	
						while($rel_reserve=brp_mysqli_fetch_array($rs_reserve)){

							$query_resu1 = $dbcon->query("UPDATE tbl_reserve_stock SET p_id =".$inserid_alloc." WHERE p_id=0 and request_id =".$rel_reserve['rp_id']);

						}
					var_dump($inserid_alloc);
					var_dump('New Entry');
		}
		
			
	}
				

?>
