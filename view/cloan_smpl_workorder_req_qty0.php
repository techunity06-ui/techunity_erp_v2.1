<?php
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../include/function_database_query.php");
	$company_config = getCompanyConfiguration($dbcon);

	/*main_request==1
	rp_po_qty==0
	rp_po_base_qty==0
	in_process_qty == rp_po_qty
	in_process_conv_qty== rp_po_qty
	indent_no==0""
	indent_status=0

	set main process
	rp_po_qty =0
	in_process_qty_main = rp_po_qty*/
	$qryse='SELECT rp_req_qty,sp_id FROM `tbl_set_main_process` WHERE sp_id in (8971,8935,8855,8849,8506,8505,8503,8502,8498,8497,8495,8492,8491,8490,8489,8488,8450,8410,8407,8404,8148,8090,4279)';
	$resultse=$dbcon->query($qryse);
	while($rowse=mysqli_fetch_assoc($resultse))
	{

		//$info_sub['rp_po_qty'] = 0;	
		$info_sub['in_process_qty_main'] = $rowse['rp_req_qty'];	
		$updateid=update_record('tbl_set_main_process', $info_sub,"sp_id=".$rowse['sp_id'], $dbcon);

		$qry3='SELECT * FROM `tbl_request_product` as apro 
		        WHERE main_request=1 and `sp_id`='.$rowse['sp_id'];
		$result3=$dbcon->query($qry3);
		while($row=mysqli_fetch_assoc($result3))
		{
			$info_sub1['in_process_qty'] 		= $row['rp_req_qty'];	
			$info_sub1['in_process_conv_qty'] 	= $row['rp_req_qty'];	
			$indent_no=load_common_no($dbcon,JOBCARD);
			update_common_no($dbcon,JOBCARD);
			$info_sub1['job_card_status']		= 1;
			$info_sub1['job_card_no']			= $indent_no;
			$info_sub1['job_card_date']			= date('Y-m-d');
			$updateid=update_record('tbl_request_product', $info_sub1,"rp_id=".$row['rp_id'], $dbcon);

					$process=get_product_process($dbcon,$row['rp_id'],$row['rp_pid']);
					$process_pr=json_decode($process);
					
					$process_id=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo=get_resource_from_product_process($dbcon,$row['rp_pid'],$process_id, $where=null);

					$info5['process_id']		= $process_id;			
					$info5['p_start_time']		= '';		
					$info5['p_end_time']		= '';		
					$info5['p_qty']				= $info_sub1['in_process_qty'];		
					$info5['pen_qty']			= $info_sub1['in_process_qty'];		
					$info5['process_unit']		= $row['process_unit'];		
					$info5['p_ref_id']			= $row['rp_id'];		
					$info5['p_ref_type']		= 'process request';		
					$info5['p_product_id']		= $row['rp_pid'];		
					$info5['pr_process_type']	= $process_type;		
					$info5['process_priority']	= $process_priority;		
					$info5['previous_process_id']= 0;
					$info5['product_version']	= $row['product_version'];
					$info5['extra_stock'] 			= $row['extra_stock'];
					$info5['ext_stock_vendor_id'] = $row['ext_stock_vendor_id'];

					if($resourceinfo['process_type']=='1'){		
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}	

					
					if($company_config['batch_wise_stock'] == '1' &&  $company_config['batch_process'] == '0' && $setpro_rel['batch_wise_stock_manage'] == '1'){
						$info5['batch_process_start_time'] = 1;
					}

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					
					$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon,$row['branch_id']);
					echo $row['rp_id']; 
					echo "</br>";
					echo $inserid_alloc;
					echo "--------------------------------------------------------";
		}

	}
?>
