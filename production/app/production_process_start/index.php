<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
		
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		$company_config = getCompanyConfiguration($dbcon);	
		if(strtolower($POST['mode']) == "add_start_process") {
			
			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];
			
			$info_jobwork['job_work_type']		= "1";
			$info_jobwork['job_work_no']		= load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
			$info_jobwork['job_work_date']		= date("Y-m-d");
			$info_jobwork['vender_id']			= "-1";
			$info_jobwork['vehicle_no']			= "";
			$info_jobwork['remark']				= $POST['remark'];
			
			$info_jobwork['cdate']				= date("Y-m-d H:i:s");
			$info_jobwork['user_id']			= $_SESSION['user_id'];
			$info_jobwork['company_id']			= $_SESSION['company_id'];
			
			$job_work_id=add_record('tbl_job_work',$info_jobwork, $dbcon,$branch_id);
			if($job_work_id){
				update_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
			}
			$info_jobwork_trn['job_work_id']			= $job_work_id;
			$info_jobwork_trn['process_id']				= $POST['process_id'];
			$info_jobwork_trn['product_id']				= $POST['product_id'];
			$info_jobwork_trn['product_base_qty']		= $POST['start_qty'];
			$info_jobwork_trn['product_base_unit']		= $POST['product_base_unit'];
			$info_jobwork_trn['product_con_qty']		= $POST['start_qty'];
			$info_jobwork_trn['product_con_unit']		= $POST['product_base_unit'];
			$info_jobwork_trn['remark']					= $POST['remark'];
			$info_jobwork_trn['product_version']		= $POST['product_version'];
			
			$info_jobwork_trn['cdate']						= date("Y-m-d H:i:s");
			$info_jobwork_trn['user_id']					= $_SESSION['user_id'];
			$info_jobwork_trn['company_id']					= $_SESSION['company_id'];
			
			$job_work_trn_id=add_record('tbl_job_work_trn',$info_jobwork_trn, $dbcon,$branch_id);
			
			$query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_process where p_id in (".$POST['p_id'].")";
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);
			if($cnt){
				$allocate_process_qty=0;
				while($row=brp_mysqli_fetch_assoc($result)){
					$allocate_process_qty=($row['p_qty']-$row['start_qty']);
					$working_qty=production_start_count_using_p_id($dbcon,$row['p_id']);
					if($start_qty<$working_qty){
						$working_qty=$start_qty;
					}
					if($working_qty!="0" && $allocate_process_qty!="0"){
						if($working_qty>=$allocate_process_qty){
							//use $allocate_process_qty
							$used_qty=$allocate_process_qty;
						}else{
							//use $working_qty 
							$used_qty=$working_qty;
						}
						if($used_qty>0){
							$allocate_process_start_qty=$row['start_qty']+$used_qty;
							$info_allocate['start_qty']		= $allocate_process_start_qty;
							$info_allocate['p_status']		= 1;
							$info_allocate['task_status']	= 1;
							$updatetrnid=update_record('tbl_allocate_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
							
							//location common_functions 
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$info_jobwork_trn['product_id'],$info_jobwork_trn['process_id'],$used_qty,"0");
							
								$info_jobwork_sub_trn['job_work_trn_id']	= $job_work_trn_id;
								$info_jobwork_sub_trn['product_id']			= $info_jobwork_trn['product_id'];
								$info_jobwork_sub_trn['product_base_qty']	= $used_qty;
								$info_jobwork_sub_trn['product_base_unit']	= $info_jobwork_trn['product_base_unit'];
								$info_jobwork_sub_trn['product_con_qty']	= $used_qty;
								$info_jobwork_sub_trn['product_con_unit']	= $info_jobwork_trn['product_con_unit'];
								$info_jobwork_sub_trn['p_id']				= $row['p_id'];
								$info_jobwork_sub_trn['rp_id']				= $row['p_ref_id'];
								
								$info_jobwork_sub_trn['cdate']				= date("Y-m-d H:i:s");
								$info_jobwork_sub_trn['user_id']			= $_SESSION['user_id'];
								$info_jobwork_sub_trn['company_id']			= $_SESSION['company_id'];
								
								$job_work_sub_trn_id=add_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn, $dbcon,$branch_id);



										/*START JAYESH 
										PURPOSE : Process start with batch wise 
										*/

										/*$qry_1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					where ap.p_id in (".$row['p_id'].")";

							$result_1=$dbcon->query($qry_1);
				
							$row_1=brp_mysqli_fetch_array($result_1);


									if(isset($POST['batch_no'])){	
										$product_id =  $POST['product_id'];
										$mfgdate = date("Y-m-d");
										$get_dt_qry="select * from product_mst where product_id = '$product_id'";
										$getproduct_res=$dbcon->query($get_dt_qry);
										$getproduct_row=mysqli_fetch_assoc($getproduct_res);
										if($getproduct_row['self_life_days'] != '')
										{
										$exp_days = $getproduct_row['self_life_days'];
										$mfg_date = date("Y-m-d",strtotime($mfgdate));			
										$exp_date = date("Y-m-d",strtotime("+".$exp_days." days", strtotime($mfg_date)));

										}
										else{
										$exp_date = 0;
										}
										
										$company_data = getCompanyConfiguration($dbcon, $id = false);
										$batch_data['grn_id'] = $row_1["work_order_id"];
										$batch_data['order_no'] = $row_1["work_order_no"];
										$batch_data['batch_no'] = $POST['batch_no'];
										$batch_data['batch_qty'] = $used_qty;
										$batch_data['mfg_date'] = $mfgdate;
										$batch_data['exp_date'] = $exp_date;
										$batch_data['grn_date'] = date("Y-m-d");
										$batch_data['batch_type'] = $company_data['batch_type'];
										$batch_data['production_type'] = '2';
										$batch_data['status'] = '3';
										$batch_data['cdate'] =  date("Y-m-d H:i:s");
										$batch_data['user_id'] = $_SESSION['user_id'];
										$batch_data['company_id'] = $_SESSION['company_id'];
										$insert_batch_data=add_record('tbl_batch_data',$batch_data, $dbcon,$branch_id);
									}*/
										/*END JAYESH 
										PURPOSE : Process start with batch wise 
										*/		
							
							$start_qty=$start_qty-$used_qty;
						}
						
					}
				}
			}
			echo "1";
			
		}
		if(strtolower($POST['mode']) == "add_start_process_using_model") {
			
			$start_stop_user_id = $POST['start_stop_user_id'];


			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];
			$process_rate = $POST['process_rate'];
			
			$info_jobwork['job_work_type']		= "1";
			$info_jobwork['job_work_no']		= load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
			$info_jobwork['job_work_date']		= date("Y-m-d");
			$info_jobwork['vender_id']			= "-1";
			$info_jobwork['vehicle_no']			= "";
			$info_jobwork['remark']				= $POST['remark'];
			$info_jobwork['resource_id']		= $POST['machine'];
			
			$info_jobwork['cdate']				= date("Y-m-d H:i:s");
			$info_jobwork['user_id']			= $_SESSION['user_id'];
			$info_jobwork['company_id']			= $_SESSION['company_id'];
			
			$job_work_id=add_record('tbl_job_work',$info_jobwork, $dbcon,$branch_id);
			if($job_work_id){
				update_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
			}
			$info_jobwork_trn['job_work_id']			= $job_work_id;
			$info_jobwork_trn['process_id']				= $POST['process_id'];
			$info_jobwork_trn['product_id']				= $POST['product_id'];
			$info_jobwork_trn['product_base_qty']		= $POST['start_qty'];
			$info_jobwork_trn['product_base_unit']		= $POST['product_base_unit'];
			$type="conv_unit";
			$start_conv_qty = convert_stock($dbcon,$POST['start_qty'],$POST['product_id'],$type);
			
			$info_jobwork_trn['product_con_qty']		= $start_conv_qty;
			$info_jobwork_trn['product_con_unit']		= $POST['product_conv_unit'];
			$info_jobwork_trn['remark']					= $POST['remark'];
			$info_jobwork_trn['product_version']		= $POST['product_version'];
			$info_jobwork_trn['pr_rate']		= $process_rate;
			$info_jobwork_trn['rate_unit']		= $POST['product_base_unit'];
			
			$info_jobwork_trn['cdate']						= date("Y-m-d H:i:s");
			$info_jobwork_trn['user_id']					= $_SESSION['user_id'];
			$info_jobwork_trn['company_id']					= $_SESSION['company_id'];
			
			$info_jobwork_trn['company_id']					= $_SESSION['company_id'];

			
			
			$job_work_trn_id=add_record('tbl_job_work_trn',$info_jobwork_trn, $dbcon,$branch_id);
			
			$pid=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];
			if(isset($POST['batch_no'])){
				$batch_no = $POST['batch_no'];
			}
			$work_order_no  = $POST['work_order_no'];
			$work_order_id = $POST['work_order_id'];
		
			for($i=0;$i<count($pid);$i++)

			{	
				if($company_config['resource_wise_production'] == '1'){
					$upd_machine['resource_id'] = $POST['machine'];
					$updatetrnid=update_record('tbl_allocate_process',$upd_machine,"p_id=".$pid[$i] , $dbcon);
				}

			
				/* Sanat Start :: 22-06-22 :: UPDATE tbl_start_stop_production start_qty */

				$ss_qry = "select * from tbl_start_stop_production where complete_status = 0 and p_id = " . $pid[$i];
				$ss_res = $dbcon->query($ss_qry);
				$ss_start_qty = $pid_wise_start_qty[$i];
				while($ss_row = brp_mysqli_fetch_assoc($ss_res)){
					$ss_pen_qty = $ss_row['pending_qty'];
					$start_qty_ss = $ss_row['start_qty'];
					if($ss_start_qty > 0){
						if($ss_pen_qty>=$ss_start_qty){
							$s_used_qty=$ss_start_qty;
						}else{
							//use $working_qty 
							$s_used_qty=$ss_pen_qty;
						}	
							$ss_start_qty=$ss_start_qty-$s_used_qty;
						$ss_info = array();	
						$ss_info['pending_qty'] = $ss_pen_qty - $s_used_qty;
						$ss_info['start_qty'] = $start_qty_ss + $s_used_qty;

						if(($ss_pen_qty - $s_used_qty) <= 0){
							$ss_info['complete_status'] = 1;						
						}

						$updatetrnid=update_record('tbl_start_stop_production',$ss_info,"start_stop_id=".$ss_row['start_stop_id']." and p_id = ".$pid[$i], $dbcon);
						
					}
				}

				/* Sanat End :: 22-06-22 :: UPDATE tbl_start_stop_production start_qty */

				if($company_config['store_approval'] == '0'){
					 $qry = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $pid[$i];
				$res = $dbcon->query($qry);
				$cnt=brp_mysqli_num_rows($res);
				$result= brp_mysqli_fetch_assoc($res);
				$extra_stock = $result['extra_stock'];


					$store_info['rp_id']		= $result['p_ref_id'];
					$store_info['product_id']		= $POST['product_id'];
					$store_info['process_id']		= $POST['process_id'];
					$store_info['remark']		= "store off auto entry";
					$store_info['cdate']		= date("Y-m-d H:i:s");
					$store_info['user_id']	= $_SESSION['user_id'];
					$store_info['company_id']	= $_SESSION['company_id'];
					$store_info['branch_id']	= $branch_id;
					$store_info['base_unit'] = $POST['product_base_unit'];
					$store_info['conv_unit'] = $POST['product_conv_unit'];


					$conv_stock=convert_stock_new($dbcon,$pid_wise_start_qty[$i],$POST['product_id'],"conv_unit");
					$store_info['p_id']		= $pid[$i];
					$store_info['base_qty']	= $pid_wise_start_qty[$i];
					$store_info['conv_qty']	= $conv_stock;
					$store_info['store_request_status']	=1;


					$req_id = add_record('tbl_store_request',$store_info, $dbcon,$branch_id);


					$qry_1 = "select * from tbl_store_request  where store_request_id = ". $req_id;
					$result_1 =$dbcon->query($qry_1);
					$row = brp_mysqli_fetch_assoc($result_1);
					$remaining_qty = $row['base_qty'];


				
				$info['rp_id']		= $POST['product_id'];
				$info['process_id']	= $POST['process_id'];
				$info['p_id']	= $pid[$i];
				$info['remark']		= "store off auto entry";
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['branch_id']	= $branch_id;

				$info['release_unit'] = $POST['product_base_unit'];
				$info['release_conv_unit'] = $POST['product_conv_unit'];

				$info['store_request_id'] = $row['store_request_id'];
				$info['to_user_id'] = $_SESSION['user_id'];
				$info['issue_no'] = get_issue_no($dbcon);
				$info['release_status'] = 1;
				$info['issue_date']	= date('Y-m-d');
				update_issue_no($dbcon);

				$info['release_qty']	= $row['base_qty'];
				$info['release_conv_qty']	= $row['conv_qty'];

				$release_id = add_record('tbl_store_release',$info, $dbcon,$branch_id);

				store_release_logs($dbcon,$row['store_request_id'],$release_id,$row['base_qty'],$pid[$i],$POST['product_id'],$POST['process_id'],"store off auto entry",$_SESSION['user_id'],$branch_id);

				if($result['previous_process_id'] == '0'){
					$query2 = "select trp.* from tbl_request_product as trp 
				where  trp.status !=2 and trp.perent_id = " . $result['p_ref_id'] . " group by rp_id";

				$req_qty = $pid_wise_start_qty[$i];
				$result2=$dbcon->query($query2);
				
				while($row2=brp_mysqli_fetch_array($result2)){
						$o_qty=convert_stock($dbcon,$row2["req_qty_one"],$row2['rp_pid'],"base_unit");
						$o_qty=round($o_qty,6);
						
						$used_qty=$req_qty*$row2["req_qty_one"];
						$raw_rel_qty=round($used_qty,5);

						$c_used_qty=convert_stock($dbcon,$used_qty,$row2['rp_pid'],'conv_unit');
						$raw_rel_qty_con = round_up($c_used_qty,5);

						$info_material['release_id'] = $release_id;
						$info_material['p_id'] = $pid[$i];
						$info_material['product_id'] = $row2['rp_pid'];
						$info_material['process_id'] = $POST['process_id'];
						$info_material['request_qty'] = $raw_rel_qty;
						$info_material['release_qty'] = $raw_rel_qty;
						$info_material['release_unit'] = $row2['process_unit'];
						$info_material['release_conv_qty'] =  $raw_rel_qty_con;
						$info_material['release_conv_unit'] = $row2['purchase_unit'];
						$info_material['cdate']		= date("Y-m-d H:i:s");
						$info_material['user_id']	= $_SESSION['user_id'];
						$info_material['company_id']	= $_SESSION['company_id'];
						$info_material['branch_id']	= $branch_id;

						$m_req_id = add_record('tbl_store_release_material_trn',$info_material, $dbcon);
						if($extra_stock	== 0){
							release_stock_action_modal($dbcon,$pid[$i],$info_material['release_qty'],$info_material['release_conv_qty'],0,$row2['rp_pid'],$product_id,$row2['rp_id']);
						}
					}
				}else{
						$info_material['release_id'] = $release_id;
						$info_material['p_id'] = $pid[$i];
						$info_material['product_id'] = $POST['product_id'];
						$info_material['process_id'] = $POST['process_id'];
						$info_material['request_qty'] = $row['base_qty'];
						$info_material['release_qty'] = $row['base_qty'];
						$info_material['release_unit'] = $POST['product_base_unit'];
						$info_material['release_conv_qty'] =  $row['conv_qty'];
						$info_material['release_conv_unit'] = $POST['product_conv_unit'];
						$info_material['cdate']		= date("Y-m-d H:i:s");
						$info_material['user_id']	= $_SESSION['user_id'];
						$info_material['company_id']	= $_SESSION['company_id'];
						$info_material['branch_id']	= $branch_id;

						$m_req_id = add_record('tbl_store_release_material_trn',$info_material, $dbcon);
						if($extra_stock	== 0){
							release_stock_action_modal($dbcon,$pid[$i],$info_material['release_qty'],$info_material['release_conv_qty'],1,$POST['product_id'],$POST['product_id'],$result['p_ref_id']);
						}
				}

				}




					$query="select ap.p_id,ap.p_qty,ap.start_qty,ap.p_ref_id,rp.customer_id from tbl_allocate_process as ap left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id where p_id in (".$pid[$i].")";
					$result=$dbcon->query($query);
					$cnt=brp_mysqli_num_rows($result);
					if($cnt){
						$allocate_process_qty=0;
						while($row=brp_mysqli_fetch_assoc($result)){
							$allocate_process_qty=($row['p_qty']-$row['start_qty']);
							//$working_qty=production_start_count_using_p_id($dbcon,$row['p_id']);
							$working_qty=$pid_wise_start_qty[$i];
							if($start_qty<$working_qty){
								$working_qty=$start_qty;
							}
							if($working_qty!="0" && $allocate_process_qty!="0"){
								if($working_qty>=$allocate_process_qty){
									//use $allocate_process_qty
									$used_qty=$allocate_process_qty;
								}else{
									//use $working_qty 
									$used_qty=$working_qty;
								}
								if($used_qty>0){
									$allocate_process_start_qty=$row['start_qty']+$used_qty;
									$info_allocate['start_qty']		= $allocate_process_start_qty;
									$info_allocate['p_status']		= 1;
									$info_allocate['task_status']	= 1;
									$updatetrnid=update_record('tbl_allocate_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
									
									//location common_functions 
									// var_dump($start_stop_user_id);
									add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$info_jobwork_trn['product_id'],$info_jobwork_trn['process_id'],$used_qty,"0","","",$start_stop_user_id);
									
										$info_jobwork_sub_trn['job_work_trn_id']	= $job_work_trn_id;
										$info_jobwork_sub_trn['product_id']			= $info_jobwork_trn['product_id'];
										$info_jobwork_sub_trn['product_base_qty']	= $used_qty;
										$info_jobwork_sub_trn['product_base_unit']	= $info_jobwork_trn['product_base_unit'];
										
										
										$used_conv_qty = convert_stock($dbcon,$POST['start_qty'],$POST['product_id'],"conv_unit");	
										
										$info_jobwork_sub_trn['product_con_qty']	= $used_conv_qty;
										$info_jobwork_sub_trn['product_con_unit']	= $info_jobwork_trn['product_con_unit'];
										$info_jobwork_sub_trn['p_id']				= $row['p_id'];
										$info_jobwork_sub_trn['rp_id']				= $row['p_ref_id'];
										$info_jobwork_sub_trn['pr_rate']		= $process_rate;
										
										$info_jobwork_sub_trn['cdate']				= date("Y-m-d H:i:s");
										$info_jobwork_sub_trn['user_id']			= $_SESSION['user_id'];
										$info_jobwork_sub_trn['company_id']			= $_SESSION['company_id'];
										$info_jobwork_sub_trn['customer_id']		= $row['customer_id'];
										
										$job_work_sub_trn_id=add_record('tbl_job_work_sub_trn',$info_jobwork_sub_trn, $dbcon,$branch_id);
										
										/*START JAYESH 
										PURPOSE : Process start with batch wise 
										*/
									/*
									if(isset($POST['batch_no'])){		
										$product_id = $pid[$i];
										$mfgdate = date("Y-m-d");
										$get_dt_qry="select * from product_mst where product_id = '$product_id'";
										$getproduct_res=$dbcon->query($get_dt_qry);
										$getproduct_row=mysqli_fetch_assoc($getproduct_res);
										if($getproduct_row['self_life_days'] != '')
										{
										$exp_days = $getproduct_row['self_life_days'];
										$mfg_date = date("Y-m-d",strtotime($mfgdate));			
										$exp_date = date("Y-m-d",strtotime("+".$exp_days." days", strtotime($mfg_date)));

										}
										else{
										$exp_date = 0;
										}
										
										$company_data = getCompanyConfiguration($dbcon, $id = false);
										$batch_data['grn_id'] = $work_order_id[$i];
										$batch_data['order_no'] = $work_order_no[$i];
										$batch_data['batch_no'] = $batch_no[$i];
										$batch_data['batch_qty'] = $pid_wise_start_qty[$i];
										$batch_data['mfg_date'] = $mfgdate;
										$batch_data['exp_date'] = $exp_date;
										$batch_data['grn_date'] = date("Y-m-d");
										$batch_data['batch_type'] = $company_data['batch_type'];
										$batch_data['production_type'] = '2';
										$batch_data['status'] = '3';
										$batch_data['cdate'] =  date("Y-m-d H:i:s");
										$batch_data['user_id'] = $_SESSION['user_id'];
										$batch_data['company_id'] = $_SESSION['company_id'];
										$insert_batch_data=add_record('tbl_batch_data',$batch_data, $dbcon,$branch_id);
																
										}*/	

										/*END JAYESH 
										PURPOSE : Process start with batch wise 
										*/		
									$start_qty=$start_qty-$used_qty;
								}
								
							}
						}
					}
				
			}
			echo "1";
		}
		else if(strtolower($POST['mode']) == "add_start_process_old") {

			$process_id=$POST['process_id_hid'];
			
			$eid=$POST['eid'];
			$date=date("Y-m-d h:i:sa");
			$qty=$POST['machine_no'];
			$branch_id=$POST['branch_id'];
			
			$info1['jobwork_no']		=load_job_no($dbcon,$POST['invoicetype_id']);
			$info1['jobwork_date']		=$date;
			$info1['j_product_id']		=$POST['product_id_hid'];
			$info1['j_pr_process_id']	=$POST['process_id_hid'];
			$info1['j_process_type']	=$POST['process_type_hid'];
			$info1['j_pr_process_no']	=$POST['pr_process_no'];
			$info1['j_vendor']			=$POST['pr_vender_id'];
			$info1['j_chalan_no']		=$POST['pr_chalan_no'];
			$info1['j_qty']				=$qty;
			//$info1['j_ref_id']			=$row['p_ref_id'];
			$info1['j_alloc_process_id']	=$eid;
			//$info1['pr_jobwork_no']		=$POST['pr_jobwork_no'];
			$info1['process_unit']		=$POST['process_unit'];
			$info1['pr_rate']			=$POST['pr_rate'];
			
			$info1['cdate']				= date("Y-m-d H:i:s");
			$info1['userid']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			
			$job_id=add_record('tbl_jobwork', $info1, $dbcon,$branch_id);
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '7' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
			
			$info3['process_id']			=$POST['process_id_hid'];
			$info3['p_start_time']			=$date;
			$info3['p_end_time']			='';
			$info3['p_qty']					=$qty;
			$info3['pen_qty']				='';
			$info3['p_status']				='1';
			//$info3['p_ref_id']				=$row['p_ref_id'];
			//$info3['p_ref_type']			=$POST['pr_chalan_no'];
			$info3['p_product_id']			=$POST['product_id_hid'];
			$info3['pr_process_type']		=$POST['process_type_hid'];
			//$info3['j_alloc_process_id']	=$row['p_id'];
			
			$info3['cdate']					= date("Y-m-d H:i:s");
			$info3['user_id']				= $_SESSION['user_id'];
			$info3['company_id']			= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon,$branch_id);
			
			$query="select * from tbl_allocate_process where p_id in (".$eid.")";
			//var_dump($query);
			$result=$dbcon->query($query);
			while($row=mysqli_fetch_assoc($result)){
				
				$sub_qty=($row['p_qty']-$row['start_qty']);
				//pathik start 
					//only  allocate qty use 
					$aaac_qty=start_qty_avalable($dbcon,$row['process_id'],$row['pr_process_type'],$row['p_product_id'],$row['p_id'],$branch_id);
					
					if($aaac_qty<=$sub_qty){
						$sub_qty=$aaac_qty;
					}
				//pathik end
				if($qty!=""){
					if($qty>0){
						if($sub_qty>=$qty){
							/* var_dump("pid=");
							var_dump($row['p_id']);
							echo "</br>";
							var_dump("qty");
							var_dump($qty);
							echo "</br>";
							 */
							$dbcon->query("update tbl_allocate_process set start_qty=start_qty+".$qty.",p_start_time='$date',p_status='1',task_status='1' where  p_id=".$row['p_id']."");
							
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$qty,"0");
							
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							//add_process_stock_new($dbcon,$row['p_id'],$qty);
							
							$infog['jobwork_id']		= $job_id;
							$infog['p_id']				= $row['p_id'];
							$infog['qty']				= $qty;
							$infog['cdate']				= date("Y-m-d H:i:s");
							$infog['userid']			= $_SESSION['user_id'];
							$infog['company_id']		= $_SESSION['company_id'];
							
							$job_p=add_record('tbl_jobwork_process', $infog, $dbcon,$branch_id);
			
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							/*if($row['previous_process_id']=="0"){
								$grn_qty=$POST['row_product_id'];
								for($k=0;$k<count($grn_qty);$k++)
								{
									$uqty=$POST['row_req_qty_one'][$k]*$qty;
									$uqty=round($uqty,4);
									$info2['allocate_process_id']	=$eid;
									$info2['product_id']			=$POST['row_product_id'][$k];
									$info2['unit_id']				=$POST['row_unit_id'][$k];
									$info2['used_qty']				=$uqty;
									$info2['cdate']					= date("Y-m-d H:i:s");
									$info2['user_id']				= $_SESSION['user_id'];
									$info2['company_id']			= $_SESSION['company_id'];
									
									$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon);
									
									$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty']);
									
									//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
									$request_id=find_request_id($dbcon,$row['p_ref_id'],$info2['product_id']);
									
									//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
									deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
								}
							}*/
							$qty=$qty-$qty;
							
							
						}else{
							/* var_dump("pid=");
							var_dump($row['p_id']);
							echo "</br>";
							var_dump("sub_qty");
							var_dump($sub_qty);
							echo "</br>";
							 */
							$dbcon->query("update tbl_allocate_process set start_qty=start_qty+".$sub_qty.",p_start_time='$date',p_status='1',task_status='1' where p_id=".$row['p_id']."");
							
							add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$sub_qty,"0");
							
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							//add_process_stock_new($dbcon,$row['p_id'],$sub_qty);
							
							/* $info1['jobwork_no']		=load_job_no($dbcon,$POST['invoicetype_id']);
							$info1['jobwork_date']		=$date;
							$info1['j_product_id']		=$row['p_product_id'];
							$info1['j_pr_process_id']	=$row['process_id'];
							$info1['j_process_type']	=$row['pr_process_type'];
							$info1['j_pr_process_no']	=$POST['pr_process_no'];
							$info1['j_vendor']			=$POST['pr_vender_id'];
							$info1['j_chalan_no']		=$POST['pr_chalan_no'];
							$info1['j_qty']				=$sub_qty;
							$info1['j_ref_id']			=$row['p_ref_id'];
							$info1['j_alloc_process_id']=$row['p_id'];
							$info1['process_unit']		=$row['process_unit'];
							$info1['pr_rate']			=$POST['pr_rate'];
							
							$info1['cdate']				= date("Y-m-d H:i:s");
							$info1['userid']			= $_SESSION['user_id'];
							$info1['company_id']		= $_SESSION['company_id'];
							
							$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
							
							$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
							
							$info3['process_id']			=$row['process_id'];
							$info3['p_start_time']			=$date;
							$info3['p_end_time']			='';
							$info3['p_qty']					=$sub_qty;
							$info3['pen_qty']				='';
							$info3['p_status']				='1';
							$info3['p_ref_id']				=$row['p_ref_id'];
							//$info3['p_ref_type']			=$POST['pr_chalan_no'];
							$info3['p_product_id']			=$row['p_product_id'];
							$info3['pr_process_type']		=$row['pr_process_type'];
							$info3['j_alloc_process_id']	=$row['p_id'];
							
							$info3['cdate']					= date("Y-m-d H:i:s");
							$info3['user_id']				= $_SESSION['user_id'];
							$info3['company_id']			= $_SESSION['company_id'];
							
							$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon); */
							
							$infog['jobwork_id']		= $job_id;
							$infog['p_id']				= $row['p_id'];
							$infog['qty']				= $sub_qty;
							$infog['cdate']				= date("Y-m-d H:i:s");
							$infog['userid']			= $_SESSION['user_id'];
							$infog['company_id']		= $_SESSION['company_id'];
							
							$job_p=add_record('tbl_jobwork_process', $infog, $dbcon,$branch_id);
							
							/*
							Code By Umair: 19/11/2020
							Commnet: Below code is commented coz of this is used in the end process function
							*/
							 /*if($row['previous_process_id']=="0"){
								$grn_qty=$POST['row_product_id'];
								for($k=0;$k<count($grn_qty);$k++)
								{
									$uqty=$POST['row_req_qty_one'][$k]*$sub_qty;
									
									$request_id=find_request_id($dbcon,$row['p_ref_id'],$info2['product_id']);
									
									$info2['allocate_process_id']	=$eid;
									$info2['product_id']			=$POST['row_product_id'][$k];
									$info2['unit_id']				=$POST['row_unit_id'][$k];
									$info2['used_qty']				=$uqty;
									$info2['cdate']					= date("Y-m-d H:i:s");
									$info2['user_id']				= $_SESSION['user_id'];
									$info2['company_id']			= $_SESSION['company_id'];
									
									$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon);
									
									$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty']);
									
									//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
									
									deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
								}
							}  */
							$qty=$qty-$sub_qty;
							
						}
					}
				}
			}
			
			
			
			
			
			
			/* 
			$sel1=$dbcon->query("select jobwork_id from tbl_jobwork where j_alloc_process_id='$eid'");
			$count=mysqli_num_rows($sel1);
			
			if($count==0)
			{
				//pr_process_no
				$info1['jobwork_no']		=$POST['pr_job_no'];
				$info1['jobwork_date']		=$date;
				$info1['j_product_id']		=$POST['product_id_hid'];
				$info1['j_pr_process_id']	=$POST['process_id_hid'];
				$info1['j_process_type']	=$POST['process_type_hid'];
				$info1['j_pr_process_no']	=$POST['pr_process_no'];
				$info1['j_vendor']			=$POST['pr_vender_id'];
				$info1['j_chalan_no']		=$POST['pr_chalan_no'];
				$info1['j_qty']				=$POST['machine_no'];
				$info1['j_ref_id']			=$POST['request_no'];
				$info1['j_alloc_process_id']=$eid;
				$info1['pr_jobwork_no']		=$POST['pr_jobwork_no'];
				$info1['process_unit']		=$POST['process_unit'];
				$info1['pr_rate']			=$POST['pr_rate'];
				
				$info1['cdate']				= date("Y-m-d H:i:s");
				$info1['userid']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_jobwork', $info1, $dbcon);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '11' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
				
				$info3['process_id']			=$POST['process_id_hid'];
				$info3['p_start_time']			=$date;
				$info3['p_end_time']			='';
				$info3['p_qty']					=$POST['machine_no'];
				$info3['pen_qty']				='';
				$info3['p_status']				='1';
				$info3['p_ref_id']				=$POST['request_no'];
				//$info3['p_ref_type']			=$POST['pr_chalan_no'];
				$info3['p_product_id']			=$POST['product_id_hid'];
				$info3['pr_process_type']		=$POST['process_type_hid'];
				$info3['j_alloc_process_id']	=$eid;
				
				$info3['cdate']					= date("Y-m-d H:i:s");
				$info3['user_id']				= $_SESSION['user_id'];
				$info3['company_id']			= $_SESSION['company_id'];
				
				$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon);
				
			}
			
			$info3['process_id']			=$POST['process_id_hid'];
			$info3['p_start_time']			=$date;
			$info3['p_end_time']			='';
			$info3['p_qty']					=$POST['machine_no'];
			$info3['pen_qty']				='';
			$info3['p_status']				='1';
			$info3['p_ref_id']				=$POST['request_no'];
			//$info3['p_ref_type']			=$POST['pr_chalan_no'];
			$info3['p_product_id']			=$POST['product_id_hid'];
			$info3['pr_process_type']		=$POST['process_type_hid'];
			$info3['j_alloc_process_id']	=$eid;
			
			$info3['cdate']					= date("Y-m-d H:i:s");
			$info3['user_id']				= $_SESSION['user_id'];
			$info3['company_id']			= $_SESSION['company_id'];
			
			$inserusrid1=add_record('tbl_jobwork_history', $info3, $dbcon);
			
			if($POST['previous_process_id']=="0"){
				$grn_qty=$POST['j_pr_job_id'];
			//var_dump(count($grn_qty));
					//var_dump("123");
				for($k=0;$k<count($grn_qty);$k++)
				{
					
					$info2['allocate_process_id']	=$eid;
					$info2['product_id']			=$POST['j_pr_job_id'][$k];
					//$info2['qty_need_for_single']	=$inserpoid;
					//$info2['total_req_qty']			=$POST['grn_qty'][$k];
					$info2['unit_id']				=$POST['j_unit_id'][$k];
					$info2['used_qty']				=$POST['j_usable'][$k];
					$info2['cdate']					= date("Y-m-d H:i:s");
					$info2['user_id']				= $_SESSION['user_id'];
					$info2['company_id']			= $_SESSION['company_id'];
					
					$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon);
					//var_dump($info2['used_qty']);
					$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty']);
					
					$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
					
					deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
					
					
				}
				
				
			} */
			//var_dump("fdsa");

			/*
				Code By Umair : 15/12/2020
				Comment: Update  actual_start_date in tbl_resource_schedule
			*/
			$resource_sch_where = '';	
			if(isset($_SESSION['resource_id']) && $_SESSION['resource_id']!=""){
				$resource_sch_where = ' and resource_id = "'.$_SESSION['resource_id'].'" ';
			}	
			$resource_schedule_sql = 'select * from tbl_resource_schedule where process_id="'.$POST['process_id_hid'].'" and p_product_id="'.$POST['product_id_hid'].'" and work_status="0" and company_id="'.$_SESSION['company_id'].'" '.$resource_sch_where.' ';

			$resource_schedule_exec=$dbcon->query($resource_schedule_sql);

			$entered_qty = (float)$POST['machine_no'];
			while($resource_schedule_data=mysqli_fetch_assoc($resource_schedule_exec)){
				$resource_schedule_id = $resource_schedule_data['resource_schedule_id'];

				$p_qty = $resource_schedule_data['p_qty']; 
				$pen_qty = $resource_schedule_data['pen_qty']; 
				$start_qty = $resource_schedule_data['start_qty']; 

				$sub_qty_val=($resource_schedule_data['p_qty']-$resource_schedule_data['start_qty']);
				$sub_qty_val= (float)$sub_qty_val;
				if($entered_qty!="" && $entered_qty>0){

					if($start_qty==0 || $start_qty==''){
						$actual_start_date = " ,actual_start_date='".date('Y-m-d H:i:s')."'";
					}
					
					if($sub_qty_val >= $entered_qty){
						
						$update_sql = "UPDATE tbl_resource_schedule SET start_qty = start_qty+'".$entered_qty."' , process_qty = '".$entered_qty."', work_status='1' $actual_start_date WHERE resource_schedule_id = '".$resource_schedule_id."' ";
						$dbcon->query($update_sql);

						$entered_qty=$entered_qty-$entered_qty;
					}else{
						$update_sql = "UPDATE tbl_resource_schedule SET start_qty = start_qty+'".$sub_qty_val."' , process_qty = '".$sub_qty_val."', work_status='1' $actual_start_date WHERE resource_schedule_id = '".$resource_schedule_id."' ";
						$dbcon->query($update_sql);
						
						$entered_qty=$entered_qty-$sub_qty_val;
					}
				}		

				/*if($entered_qty==$pen_qty){
					$start_qty = $entered_qty;
				}elseif($entered_qty > $pen_qty){
					$entered_qty = $entered_qty - $pen_qty;
					$start_qty = $pen_qty;
				}*/

				/*$start_qty = $start_qty + $entered_qty;

				if($start_qty==0 || $start_qty==''){
					$update_sql = "UPDATE tbl_resource_schedule SET start_qty = '".$start_qty."' , process_qty = '".$entered_qty."', actual_start_date='".date('Y-m-d H:i:s')."' , work_status='1' WHERE resource_schedule_id = '".$resource_schedule_id."' ";
					$dbcon->query($update_sql);
				}else{
					$update_sql = "UPDATE tbl_resource_schedule SET start_qty = '".$start_qty."' , process_qty = '".$entered_qty."', work_status='1' WHERE resource_schedule_id = '".$resource_schedule_id."' ";
					$dbcon->query($update_sql);
				}
				$dbcon->query($update_sql);
				$entered_qty = $entered_qty - $pen_qty; */

			}
			
			
			echo "1";
		}
		else if(strtolower($POST['mode']) == "show_material_list_new") {
			
			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['p_id'].")";
			$resul=$dbcon->query($bom);
			$rel1=mysqli_fetch_assoc($resul);
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			
		$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
			$result=$dbcon->query($bom1);
			$i=1;
			while($rel=mysqli_fetch_assoc($result)){
				$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
				$rel["req_qty_one"]=round($rel["req_qty_one"],6);
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
				
				$cur_stock=round($cur_stock,4);
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';

			$chkMaterial = $dbcon->query("SELECT bmt.*, mp.material_parameter_name,bom.product_kg FROM tbl_bom_material_trn as bmt LEFT JOIN tbl_material_parameter as mp ON mp.material_parameter_id = bmt.material_parameter_id LEFT JOIN tbl_bomtrn as bom ON bom.bom_trn_id = bmt.bom_trn_id WHERE bmt.bom_material_trn_status = 0 AND bmt.bom_id='".$rel['perent_id']."'");

			$Calculation = "";
			$str = "";
			while($getMaterial=brp_mysqli_fetch_assoc($chkMaterial)){
			    $str .= $getMaterial['material_parameter_name'].' - '.$getMaterial['material_parameter_value'].',';
			    $Calculation = 'Calculation : '. $getMaterial['product_kg'];
			}
			$str .= '<br>'. $Calculation;
				//$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
				echo '<tr>
						<td>'.$rel["product_name"].'
							<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" /> <br> '.$str.'
						</td>
						<td>'.$cat_name.'</td>
						<td>'.$o_qty.'
							<input type="hidden" class="" name="row_req_qty_one[]" id="row_req_qty_one'.$i.'" value="'.$o_qty.'" />
						</td>
						<td>'.$total_req_qty.'</td>
						<td>'.$cur_stock.'</td>
						<td>'.$used_qty.'</td>
						<td>'.$rel["base_unit_name"].'
							<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id'.$i.'" value="'.$rel['process_unit'].'" />
						</td>
				</tr>
				';
				$i++;
			}
		}
		else if(strtolower($POST['mode'])== "get_series_no"){
			$query="select * from tbl_invoicetype where status=0 and type_id=11 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(strtolower($POST['mode'])== "load_invoiceno"){
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
			//$end = $start+1;
			if($rows['invoice_format']=='2')
			{
				$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
			}
			else if($rows['invoice_format']=='1')
			{
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
			}
			else if($rows['invoice_format']=='3'){
				$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
			}
			else{
				$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			}
			$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
			echo json_encode($row);
		}
		
		
	

?>