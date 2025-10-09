<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "add") {

			$company_config = getCompanyConfiguration($dbcon);
			$qc_unit_on = $company_config['qc_unit'];

			
			$arr_batch_id = $POST['batch_id'];
			$arr_accept = $POST['qty_accept'];
			$arr_reprocess = $POST['qty_reprocess'];
			$arr_reject = $POST['qty_reject'];
			$arr_qc_total_qty = $POST['total_pending_qty'];
			$arr_grn_pqty = $POST['grn_pqty'];
			$arr_branch_id =  $POST['branch_id'];
			$arr_grn_id =  $POST['grn_id'];
			$arr_grn_trn_id =  $POST['grn_trn_id'];
			$arr_po_ref_id = $POST['po_ref_id'];
			$arr_accept_godown_id = $POST['qc_godown'];
			$arr_reject_godown_id = $POST['qc_reject_godown'];
			$arr_reprocess_godown_id = $POST['qc_reporcess_godown'];
			$arr_grn_product = $POST['grn_product'];
			$arr_process_id = $POST['current_process_id'];
			$arr_new_product_id = $POST['new_product_id'];
			$arr_new_product_unit = $POST['new_product_unit'];
			$arr_new_process = $POST['new_process'];
			$arr_grn_type = $POST['grn_type'];
			$arr_grn_no = $POST['grn_no'];

			$arr_unit_id =$POST['qc_unit_id'];
			$arr_base_unit = $POST['qc_base_unit_id'];
			$arr_conv_unit_id =$POST['qc_conv_unit_id'];
			$arr_allocate_process_ids =$POST['allocate_process_ids'];

			$_SESSION['qc_work_type'] = '';
			unset($_SESSION['qc_work_type']);


			for($i=0;$i<count($arr_batch_id);$i++){
				$batch_id = $arr_batch_id[$i];
				$qc_accept_qty=$arr_accept[$i];
				$qc_reprocess_qty=$arr_reprocess[$i];
				$qc_reject_qty=$arr_reject[$i];
				$qc_total_qty = $arr_qc_total_qty[$i];
				$grn_id = $arr_grn_id[$i];
				$grn_trn_id = $arr_grn_trn_id[$i];
				$po_ref_id = $arr_po_ref_id[$i];
				$grn_product = $arr_grn_product[$i];
				$product_id = $arr_grn_product[$i];
				$new_product_id = $arr_new_product_id[$i];
				$grn_no = $arr_grn_no[$i]; 

				$accept = $arr_accept[$i];
				$reprocess = $arr_reprocess[$i];
				$reject = $arr_reject[$i];

				$branch_id = ($_SESSION['user_type'] == '2' && isset($arr_branch_id[$i]) && $arr_branch_id[$i]) ? $arr_branch_id[$i] : $_SESSION['branch_id'];
				$qc_no=load_common_no($dbcon,QC_NO);

				$info = array();
				$update_batch_info = array();

				$info['qc_no']			= $qc_no;			
				$info['qc_date']		= date("Y-m-d");
				// $info['check_tc_no']	= $POST['check_tc_no'];
				$info['qc_remark']		= "QC ALL";			
				$info['grn_id']			= $grn_id;			
				$info['grn_trn_id']		= $grn_trn_id;			
				$info['po_ref_id']		= $po_ref_id;			
				$info['cdate']			= date("Y-m-d H:i:s");
				$info['user_id']		= $_SESSION['user_id'];
				$info['company_id']		= $_SESSION['company_id'];
				//$info['purchase_id']	= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;	
				$info['qc_godown']		= $arr_accept_godown_id[$i];	
				//$info['grn_type']		= $POST['grn_type'];
				$info['batch_id']		= $batch_id;	
				$info['product_id']		= $grn_product;	
				$info['process_id']		= $arr_process_id[$i];	
				$info['rejected_conv_new_product_id']		= $arr_new_product_id[$i];	

				$unit_id = $arr_unit_id[$i];

				$base_unit = $arr_base_unit[$i];
				$conv_unit_id = $arr_conv_unit_id[$i];

				if($qc_unit_on == '1'){
					$unit_id = $base_unit;
				}else{
					$unit_id = $conv_unit_id;
				}

				$accept_conv=0;
				$reprocess_conv=0;
				$reject_conv=0;

			if($base_unit==$conv_unit_id){
				$accept_conv=$accept;
				$reprocess_conv=$reprocess;
				$reject_conv=$reject;
					
				$info['accepted_base_qty']= $accept;
				$info['rejected_base_qty']= $reject;
				$info['reprocess_base_qty']= $reprocess;
				
				$info['accepted_conv_qty']= $accept;
				$info['rejected_conv_qty']= $reject;
				$info['reprocess_conv_qty']= $reprocess;	
			}else{

				$qry12="select base_qty,conv_qty,IFNULL(grn_accept_qty,0) as grn_accept_qty,IFNULL(grn_reject_qty,0) as grn_reject_qty,IFNULL(grn_reprocess_qty,0) as grn_reprocess_qty from tbl_batch_data where batch_id = " . $batch_id;
				$res12=mysqli_fetch_assoc($dbcon->query($qry12));
				
				$batch_qty=$res12['base_qty'];
				$batch_conv_qty=$res12['conv_qty'];
				

				if($unit_id == $conv_unit_id){
					
					$info['accepted_base_qty']= ($accept/$batch_conv_qty) * $batch_qty;
					$info['rejected_base_qty']= ($reject/$batch_conv_qty) * $batch_qty;
					$info['reprocess_base_qty']= ($reprocess/$batch_conv_qty) * $batch_qty;
					
					$info['accepted_conv_qty']= $accept;
					$info['rejected_conv_qty']= $reject;
					$info['reprocess_conv_qty']= $reprocess;
				}else{
					$info['accepted_base_qty']= $accept;
					$info['rejected_base_qty']= $reject;
					$info['reprocess_base_qty']= $reprocess;
					
					$info['accepted_conv_qty']= ($accept/$batch_qty) * $batch_conv_qty;
					$info['rejected_conv_qty']= ($reject/$batch_qty) * $batch_conv_qty;
					$info['reprocess_conv_qty']= ($reprocess/$batch_qty) * $batch_conv_qty;
				}

			}

			$info['qc_qty']= $accept;
			$info['qc_unit']= $unit_id;
			
			$info['accepted_base_unit']= $base_unit;
			$info['accepted_conv_unit']= $conv_unit_id;
			$info['accepted_godown']= $arr_accept_godown_id[$i];
			
			$info['rejected_base_unit']= $base_unit;
			$info['rejected_conv_unit']= $conv_unit_id;
			$info['rejected_godown']= $arr_reject_godown_id[$i];
			
			$info['reprocess_base_unit']= $base_unit;
			$info['reprocess_conv_unit']= $conv_unit_id;
			$info['reprocess_godown']= $arr_reprocess_godown_id[$i];

			$inserid=add_record('tbl_qc', $info, $dbcon,$branch_id);

			if($inserid){
				update_common_no($dbcon,QC_NO);
			}

			if($arr_reject[$i] > 0){
				// add_new_product_batch($dbcon,$inserid,$POST);
				add_new_product_batch($dbcon,$inserid,$batch_id,$qc_no,$new_product_id,$unit_id,$arr_reject[$i],$branch_id,$arr_reject_godown_id[$i]);
			}
			
			
			$update_batch_info['accept_qty'] = $accept;
			$update_batch_info['grn_accept_qty'] = $res12['grn_accept_qty'] + $accept;
			$update_batch_info['reject_qty'] = $reject;
			$update_batch_info['grn_reject_qty'] = $res12['grn_reject_qty'] + $reject;
			$update_batch_info['reprocess_qty'] = $reprocess;
			$update_batch_info['grn_reprocess_qty'] = $res12['grn_reprocess_qty'] + $reprocess;
			$update_batch_info['qc_qty'] = $arr_grn_pqty[$i];
			$update_batch_info['qc_status'] = 1;
			$update_batch_info['qc_sample_qty'] = 1;
			

			$u_id=update_record('tbl_batch_data', $update_batch_info,"batch_id=".$batch_id, $dbcon);

			$process_ids=explode(",",$arr_allocate_process_ids[$i]);
			$qry_11="select ap.*,rp.reject_status from tbl_allocate_process as ap
					left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
					where p_id=".$process_ids[0];

			$set_row_11=mysqli_fetch_assoc($dbcon->query($qry_11));

			$rp_id = $set_row_11['p_ref_id'];


			if($reprocess>0){
				$qry_12="select * from tbl_wororder_product_process where rp_id=". $rp_id ." and process_priority >= (select process_priority from tbl_wororder_product_process where rp_id=". $rp_id ." and process_id = ".$arr_new_process[$i]." and product_id = ". $grn_product.") and process_priority <= (select process_priority from tbl_wororder_product_process where rp_id=". $rp_id ." and process_id = ". $arr_process_id[$i]." and product_id = ". $grn_product.") and product_id = ". $grn_product." order by process_priority asc";
				$res_12 = $dbcon->query($qry_12);
				while($set_row_12=mysqli_fetch_assoc($res_12)){
					$reprocess_info['product_id'] = $set_row_12['product_id'];
					$reprocess_info['qc_id'] = $inserid;
					$reprocess_info['rp_id'] = $set_row_12['rp_id'];
					$reprocess_info['process_priority'] = $set_row_12['process_priority'];
					$reprocess_info['process_time'] = $set_row_12['process_time'];
					$reprocess_info['process_type'] = $set_row_12['process_type'];
					$reprocess_info['process_opening'] = $set_row_12['process_opening'];
					$reprocess_info['process_id'] = $set_row_12['process_id'];
					$reprocess_info['cdate']		= date("Y-m-d");
					$reprocess_info['user_id']		= $_SESSION['user_id'];
					$reprocess_info['company_id']	= $_SESSION['company_id'];	
					$reprocess_info['branch_id']	= $set_row_12['branch_id'];	
					
					add_record('tbl_wororder_product_reprocess', $reprocess_info, $dbcon);
				}
			}
			//var_dump($reject);
			if($arr_grn_type[$i]!="2"){
				$process_ids=explode(",",$arr_allocate_process_ids[$i]);
				$cou=count($process_ids);
				//var_dump($cou);
				for($kp=0;$kp<$cou;$kp++)
				{
					$accept_qty=0;
					$reprocess_qty=0;
					$reject_qty=0;
					//var_dump($kp);
					$set11="select ap.*,rp.reject_status from tbl_allocate_process as ap
					left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
					where p_id=".$process_ids[$kp];
					//var_dump($set11);
					$set_row=mysqli_fetch_assoc($dbcon->query($set11));
					
					
					$p_ref_id1=$set_row['p_ref_id'];	
					$process_id=$set_row['process_id'];
					$process=get_next_process($dbcon,$process_id,$grn_product,$set_row['p_ref_id'],$set_row['process_priority']);
					$process_pr=json_decode($process);
					
					$process_id_new=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;
					
					$sel="select sum(accept_qty+reject_qty) as used_qty from tbl_qc_process_trn where qc_process_status=0 and p_id=".$set_row['p_id'];
					$sel_row=mysqli_fetch_assoc($dbcon->query($sel));
					
					$pending_qty=$set_row['p_qty']-$sel_row['used_qty'];
					
					$sel_job="select IFNULL(sum(IFNULL(gtrn.product_qty,0)),0) as total_qty from tbl_job_work_sub_trn as jtrn 
						left join tbl_grn_sub_trn as gtrn on gtrn.job_work_sub_trn_id=jtrn.job_work_sub_trn_id
						where jtrn.job_work_sub_trn_status=0 and jtrn.p_id=".$set_row['p_id'];
					$row_job=mysqli_fetch_assoc($dbcon->query($sel_job));
					
					$sel_qc="select IFNULL(sum(IFNULL(total_qty,0)),0) as used_qty from tbl_qc_process_trn where qc_process_status=0 and p_id=".$set_row['p_id'];
					$row_qc=mysqli_fetch_assoc($dbcon->query($sel_qc));
					
					$pending_qty=$row_job['total_qty']-$row_qc['used_qty'];
					
					if($accept>"0"){
						if($accept>=$pending_qty){
							if($process_id_new!=0)
							{
								
							}
							else
							{
								$stock_status='1';
								
							}
							$accept_qty=$pending_qty;
							$accept=$accept-$pending_qty;
							
						}else{
							if($process_id_new!=0)
							{
							}
							else
							{
								$stock_status='1';
							}
							$accept_qty=$accept;
							$accept=$accept-$accept;
							if($reprocess>0){
								$pending_qty=$pending_qty-$accept_qty;
								if($reprocess>=$pending_qty){
									$reprocess_qty=$pending_qty;
									$reprocess=$reprocess-$pending_qty;
									$ch_reject=0;
								}else{
									$reprocess_qty=$reprocess;
									$reprocess=$reprocess-$reprocess;
									$ch_reject=1;
								}

							 	$query__1 = "select job_sub_trn.rp_id,job_sub_trn.job_work_sub_trn_id,job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.product_con_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id,job_trn.rate_unit,ap.p_ref_id,job_sub_trn.pr_rate from tbl_job_work_sub_trn as job_sub_trn
											left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
											left join tbl_allocate_process as ap on ap.p_id=job_sub_trn.p_id
											where job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id =".$set_row['p_id'];
								$result__1=$dbcon->query($query__1);
								$row__1=brp_mysqli_fetch_array($result__1);
								
								 $query__2 = "select * from tbl_grn_sub_trn
											where status = 0 and job_work_sub_trn_id = " . $row__1['job_work_sub_trn_id'] . " and rp_id = " . $row__1['rp_id'];
								$result__2=$dbcon->query($query__2);
								$row__2=brp_mysqli_fetch_array($result__2);
								
								
								// $info7['process_id']		= $process_id;
								$info7['process_id']		= $arr_new_process[$i];
								$info7['pt_alloc_id']		= $set_row['p_id'];
								$info7['p_start_time']		= '';		
								$info7['p_end_time']		= '';		
								$info7['p_qty']				= $reprocess_qty;		
								$info7['pen_qty']			= $reprocess_qty;		
								$info7['p_ref_id']			= $p_ref_id1;		
								$info7['p_ref_type']		= 'process_request';		
								$info7['p_product_id']		= $grn_product;		
								$info7['pr_process_type']	= $set_row['pr_process_type'];		
								$info7['pr_process_id']		= $set_row['p_id'];		
								$info7['process_priority']		= 1;
								$info7['process_unit']		= $unit_id;		
								$info7['qc_id']		= $inserid;		
								$info7['batch_id']		=$batch_id;	
								
								$info7['cdate']				= date("Y-m-d H:i:s");
								$info7['user_id']			= $_SESSION['user_id'];
								$info7['company_id']		= $_SESSION['company_id'];	

								/* 
									Sanat Start code :: 04/05/22  for costing report for reprocess
								*/
								
								$info7['product_process_rate'] = $row__1['pr_rate'];
								$info7['product_process_unit'] = $row__1['product_base_unit'];
								$info7['total_process_rate'] = $reprocess_qty * $row__1['pr_rate'];
								$pro_rate = convert_rate($dbcon,$row__1['pr_rate'],$grn_product,"conv_unit");
								$conv_reprocess_qty = convert_stock($dbcon,$reprocess_qty,$grn_product,"conv_unit");
								$info7['total_process_conv_rate']	= $conv_reprocess_qty * $pro_rate;

								$mat_rate_for_one =  $row__2['process_pus_material_rate'] / $row__2['product_qty'];
								$mat_conv_rate_for_one =  $row__2['process_pus_material_conv_rate'] / $row__2['product_conv_qty'];

								$info7['material_rate'] = (float)$mat_rate_for_one * (float)$reprocess_qty;
								$info7['material_conv_rate'] = (float)$mat_conv_rate_for_one * (float)$conv_reprocess_qty;
								
								$info7['process_pus_material_rate'] = $info7['material_rate'] + $info7['total_process_rate'];
								$info7['process_pus_material_conv_rate'] = $info7['material_rate'] + $info7['total_process_conv_rate'];
								
								/* 
									Sanat End code :: 04/05/22  for costing report for reprocess
								*/
								
								$inserid_alloc=add_record('tbl_allocate_re_process', $info7, $dbcon,$branch_id);
								
								$info8['pt_alloc_id']	= $set_row['p_id'];			
								$info8['pt_ref_id']		= $p_ref_id1;			
								$info8['pt_product_id']	= $grn_product;			
								// $info8['pt_process_id']	= $process_id;			
								$info8['pt_process_id']	= $arr_new_process[$i];	
								$info8['pt_qty']		= $reprocess_qty;			
								$info8['cdate']			= date("Y-m-d H:i:s");
								$info8['user_id']		= $_SESSION['user_id'];
								$info8['company_id']	= $_SESSION['company_id'];	
								
								add_record('tbl_allocate_re_process_trn', $info8, $dbcon,$branch_id);
								
								if($ch_reject==1){
									if($reject>0){
										$pending_qty=$pending_qty-($accept_qty+$reprocess_qty);
										if($reject>=$pending_qty){
											$reject_qty=$pending_qty;
											$reject=$reject-$pending_qty;
										}else{
											$reject_qty=$reject;
											$reject=$reject-$reject;
										}
										
										$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$grn_no'");
										$c_mrn=mysqli_num_rows($sel_m);
										
										if($c_mrn==0)
										{
											$info2['mrn_no']			= "1";			
											$info2['mrn_date']			= date("Y-m-d");			
											$info2['grn_no']			= $grn_no;			
											$info2['qc_no']				= $inserid;	
											$info2['purchaseorder_id']	= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;	
											
											$info2['cdate']				= date("Y-m-d H:i:s");
											$info2['user_id']			= $_SESSION['user_id'];
											$info2['company_id']		= $_SESSION['company_id'];
											
											$inserid_mrn=add_record('tbl_mrn', $info2, $dbcon,$branch_id);
										}
										else
										{
											$r_m=mysqli_fetch_assoc($sel_m);
											$inserid_mrn=$r_m['mrn_id'];
										}
										
										$info3['mrn_no']		= $inserid_mrn;			
										$info3['product_id']	= $grn_product;			
										$info3['rejected_qty']	= $reject_qty;		
										
										$info3['cdate']			= date("Y-m-d H:i:s");
										$info3['user_id']		= $_SESSION['user_id'];
										$info3['company_id']	= $_SESSION['company_id'];	
										
										$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
										
										// $grn_ref=$POST['grn_ref'];
										
										// $dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
									}
								}
							}else if($reject>0){
								$pending_qty=$pending_qty-($accept_qty+$reprocess_qty);
								if($reject>=$pending_qty){
									$reject_qty=$pending_qty;
									$reject=$reject-$pending_qty;
								}else{
									$reject_qty=$reject;
									$reject=$reject-$reject;
								}
								
								$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$grn_no'");
								$c_mrn=mysqli_num_rows($sel_m);
								
								if($c_mrn==0)
								{
									$info2['mrn_no']			= "1";			
									$info2['mrn_date']			= date("Y-m-d");			
									$info2['grn_no']			= $grn_no;			
									$info2['qc_no']				= $inserid;	
									$info2['purchaseorder_id']	= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;		
									
									$info2['cdate']				= date("Y-m-d H:i:s");
									$info2['user_id']			= $_SESSION['user_id'];
									$info2['company_id']		= $_SESSION['company_id'];
									
									$inserid_mrn=add_record('tbl_mrn', $info2, $dbcon,$branch_id);
								}
								else
								{
									$r_m=mysqli_fetch_assoc($sel_m);
									$inserid_mrn=$r_m['mrn_id'];
								}
								
								$info3['mrn_no']		= $inserid_mrn;			
								$info3['product_id']	= $grn_product;			
								$info3['rejected_qty']	= $reject_qty;		
								
								$info3['cdate']			= date("Y-m-d H:i:s");
								$info3['user_id']		= $_SESSION['user_id'];
								$info3['company_id']	= $_SESSION['company_id'];	
								
								$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
								
								// $grn_ref=$POST['grn_ref'];
								
								// $dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
							}
						}
					}else if($reprocess>0){
						$pending_qty=$pending_qty-$accept_qty;
						if($reprocess>=$pending_qty){
							$reprocess_qty=$pending_qty;
							$reprocess=$reprocess-$pending_qty;
						}else{
							$reprocess_qty=$reprocess;
							$reprocess=$reprocess-$reprocess;
						}

						
						$query__1 = "select job_sub_trn.rp_id,job_sub_trn.job_work_sub_trn_id,job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.product_con_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id,job_trn.rate_unit,ap.p_ref_id,job_sub_trn.pr_rate from tbl_job_work_sub_trn as job_sub_trn
						left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
						left join tbl_allocate_process as ap on ap.p_id=job_sub_trn.p_id
						where job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id =".$set_row['p_id'];
			$result__1=$dbcon->query($query__1);
			$row__1=brp_mysqli_fetch_array($result__1);

			$query__2 = "select * from tbl_grn_sub_trn
						where status = 0 and job_work_sub_trn_id = " . $row__1['job_work_sub_trn_id'] . " and rp_id = " . $row__1['rp_id'];
			$result__2=$dbcon->query($query__2);
			$row__2=brp_mysqli_fetch_array($result__2);
						// $info7['process_id']		= $process_id;
						$info7['process_id']		=$arr_new_process[$i];
						$info7['pt_alloc_id']		= $set_row['p_id'];
						$info7['p_start_time']		= '';		
						$info7['p_end_time']		= '';		
						$info7['p_qty']				= $reprocess_qty;		
						$info7['pen_qty']			= $reprocess_qty;		
						$info7['p_ref_id']			= $p_ref_id1;		
						$info7['p_ref_type']		= 'process_request';		
						$info7['p_product_id']		= $grn_product;		
						$info7['pr_process_type']	= $set_row['pr_process_type'];		
						$info7['pr_process_id']		= $set_row['p_id'];	
						// Umair Start 05-03-2021
						$info7['process_unit']		= $unit_id;		
						$info7['qc_reporcess_godown']	= $arr_reprocess_godown_id[$i];	
						$info7['qc_id']	= $inserid;
						$info7['batch_id']		=$batch_id;		
						// Umair End 05-03-2021	
						$info7['process_priority']		= 1;
						$info7['cdate']				= date("Y-m-d H:i:s");
						$info7['user_id']			= $_SESSION['user_id'];
						$info7['company_id']		= $_SESSION['company_id'];

							/* 
									Sanat Start code :: 04/05/22  for costing report for reprocess
								*/
								
								$info7['product_process_rate'] = $row__1['pr_rate'];
								$info7['product_process_unit'] = $row__1['product_base_unit'];
								$info7['total_process_rate']			= $reprocess_qty * $row__1['pr_rate'];
								$pro_rate = convert_rate($dbcon,$row__1['pr_rate'],$grn_product,"conv_unit");
								$conv_reprocess_qty = convert_stock($dbcon,$reprocess_qty,$grn_product,"conv_unit");
								$info7['total_process_conv_rate']	= $conv_reprocess_qty * $pro_rate;

								$mat_rate_for_one =  $row__2['process_pus_material_rate'] / $row__2['product_qty'];
								$mat_conv_rate_for_one =  $row__2['process_pus_material_conv_rate'] / $row__2['product_conv_qty'];

								$info7['material_rate'] = (float)$mat_rate_for_one * (float)$reprocess_qty;
								$info7['material_conv_rate'] = (float)$mat_conv_rate_for_one * (float)$conv_reprocess_qty;
								
								$info7['process_pus_material_rate'] = $info7['material_rate'] + $info7['total_process_rate'];
								$info7['process_pus_material_conv_rate'] = $info7['material_rate'] + $info7['total_process_conv_rate'];
								
								/* 
									Sanat End code :: 04/05/22  for costing report for reprocess
								*/

								
						$inserid_alloc=add_record('tbl_allocate_re_process', $info7, $dbcon,$branch_id);
						
						$info8['pt_alloc_id']	= $set_row['p_id'];			
						$info8['pt_ref_id']		= $p_ref_id1;			
						$info8['pt_product_id']	= $grn_product;			
						// $info8['pt_process_id']	= $process_id;			
						$info8['pt_process_id']	= $arr_new_process[$i];
						$info8['pt_qty']		= $reprocess_qty;		
						// Umair Start 05-03-2021
						$info8['qc_reporcess_godown']	= $arr_reprocess_godown_id[$i];	
						// Umair End 05-03-2021		
						$info8['cdate']			= date("Y-m-d H:i:s");
						$info8['user_id']		= $_SESSION['user_id'];
						$info8['company_id']	= $_SESSION['company_id'];	
						
						add_record('tbl_allocate_re_process_trn', $info8, $dbcon,$branch_id);
						if($ch_reject==1){
							if($reject>0){
								$pending_qty=$pending_qty-($accept_qty+$reprocess_qty);
								if($reject>=$pending_qty){
									$reject_qty=$pending_qty;
									$reject=$reject-$pending_qty;
								}else{
									$reject_qty=$reject;
									$reject=$reject-$reject;
								}
								
								$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$grn_no'");
								$c_mrn=mysqli_num_rows($sel_m);
								
								if($c_mrn==0)
								{
									$info2['mrn_no']			= "1";			
									$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
									$info2['grn_no']			= $grn_no;			
									$info2['qc_no']				= $inserid;	
									$info2['purchaseorder_id']	= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;
									// Umair Start 05-03-2021
									$info2['qc_reject_godown']	= $arr_reject_godown_id[$i];	
									// Umair End 05-03-2021	
									
									$info2['cdate']				= date("Y-m-d H:i:s");
									$info2['user_id']			= $_SESSION['user_id'];
									$info2['company_id']		= $_SESSION['company_id'];
									
									$inserid_mrn=add_record('tbl_mrn', $info2, $dbcon,$branch_id);
								}
								else
								{
									$r_m=mysqli_fetch_assoc($sel_m);
									$inserid_mrn=$r_m['mrn_id'];
								}
								
								$info3['mrn_no']		= $inserid_mrn;			
								$info3['product_id']	= $grn_product;			
								$info3['rejected_qty']	= $reject_qty;
								// Umair Start 05-03-2021
								$info3['qc_reject_godown']	= $arr_reject_godown_id[$i];
								// Umair End 05-03-2021			
								
								$info3['cdate']			= date("Y-m-d H:i:s");
								$info3['user_id']		= $_SESSION['user_id'];
								$info3['company_id']	= $_SESSION['company_id'];	
								
								$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
								
								// $grn_ref=$POST['grn_ref'];
								
								// $dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
							}
						}
					}else if($reject>0){
						$pending_qty=$pending_qty-($accept_qty+$reprocess_qty);
						if($reject>=$pending_qty){
							$reject_qty=$pending_qty;
							$reject=$reject-$pending_qty;
						}else{
							$reject_qty=$reject;
							$reject=$reject-$reject;
						}
						
						$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$grn_no'");
						$c_mrn=mysqli_num_rows($sel_m);
						
						if($c_mrn==0)
						{
							$info2['mrn_no']			= "1";			
							$info2['mrn_date']			= date("Y-m-d");			
							$info2['grn_no']			= $grn_no;			
							$info2['qc_no']				= $inserid;	
							$info2['purchaseorder_id']	= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;
							// Umair Start 05-03-2021
							$info2['qc_reject_godown']	= $arr_reject_godown_id[$i];	
							// Umair End 05-03-2021		
							
							$info2['cdate']				= date("Y-m-d H:i:s");
							$info2['user_id']			= $_SESSION['user_id'];
							$info2['company_id']		= $_SESSION['company_id'];
							
							$inserid_mrn=add_record('tbl_mrn', $info2, $dbcon,$branch_id);
						}
						else
						{
							$r_m=mysqli_fetch_assoc($sel_m);
							$inserid_mrn=$r_m['mrn_id'];
						}
						
						$info3['mrn_no']		= $inserid_mrn;			
						$info3['product_id']	= $grn_product;			
						$info3['rejected_qty']	= $reject_qty;	
						// Umair Start 05-03-2021
						$info3['qc_reject_godown']	= $arr_reject_godown_id[$i];	
						// Umair End 05-03-2021		
						
						$info3['cdate']			= date("Y-m-d H:i:s");
						$info3['user_id']		= $_SESSION['user_id'];
						$info3['company_id']	= $_SESSION['company_id'];	
						
						$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
						
						// $grn_ref=$POST['grn_ref'];
						
						// $dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
					
					}
					
					//add_process_stock($dbcon,$set_row['p_id'],$accept_qty,$rejected_qty,$process_id_new);
					
					deduct_process_stock($dbcon,$set_row['p_id'],$accept_qty,$reject_qty,$process_id_new);
					//var_dump($set_row['p_id']);
					//var_dump($accept_qty);
					//var_dump($reject_qty);
					//var_dump($process_id_new);
					$total_qty=$accept_qty+$reject_qty+$reprocess_qty;
					$info_su['p_id']			= $set_row['p_id'];
					$info_su['qc_id']			= $inserid;
					$info_su['product_id']		= $grn_product;
					$info_su['p_ref_id']		= $p_ref_id1;
					
					$info_su['company_id']		= $_SESSION['company_id'];
					// Umair Start 05-03-2021
					$info_su['qc_reject_godown']	= $arr_reject_godown_id[$i];	
					$info_su['grn_trn_id']	= $grn_trn_id;	
					$info_su['qc_unit'] =$unit_id;	
					// Umair End 05-03-2021	
					//var_dump($info_su);

					$grn_qry = "select * from tbl_grn_sub_trn where status = 0 and grn_trn_id = " . $grn_trn_id . " and rp_id = " . $p_ref_id1;

					$grn_res = $dbcon->query($grn_qry);
					if(brp_mysqli_num_rows($grn_res) > 0){
					while($grn_row=mysqli_fetch_assoc($grn_res)){
						$info_su['grn_sub_trn_id']	= $grn_row['grn_trn_sub_id'];
						$info_su['accept_qty']		= 0;
						$info_su['reject_qty']		= 0;
						$info_su['reprocess_qty']	= 0;
							
						$g_qty = 0;
						$g_total_qty = 0;
						if($unit_id == $grn_row['product_conv_unit']){
							$g_qty = $grn_row['product_conv_qty'];
						}else{
							$g_qty = $grn_row['product_qty'];
						}

					
						
						if($qc_accept_qty !="" && $qc_accept_qty > 0 && $g_qty > 0 && $g_qty <= $qc_accept_qty){
						
							$info_su['accept_qty']		= $g_qty;
							
							$g_total_qty = $g_total_qty + $g_qty;
							$qc_total_qty = $qc_total_qty - $g_qty;
							$qc_accept_qty = $qc_accept_qty - $g_qty;
							$g_qty = $g_qty - $g_qty;
						
							
						}else if($qc_accept_qty !="" && $qc_accept_qty > 0 && $g_qty > 0 && $g_qty > $qc_accept_qty){
							
							$info_su['accept_qty']		= $qc_accept_qty;
							$g_total_qty = $g_total_qty + $qc_accept_qty;
							$qc_total_qty = $qc_total_qty - $qc_accept_qty;
							$qc_accept_qty = $qc_accept_qty - $qc_accept_qty;
							$g_qty = $g_qty - $qc_accept_qty;
							
						}
						
						if($qc_reject_qty !="" && $qc_reject_qty > 0 && $g_qty > 0 && $g_qty <= $qc_reject_qty){
							
							$info_su['reject_qty']		= $g_qty;
							$g_total_qty = $g_total_qty + $g_qty;
							$qc_total_qty = $qc_total_qty - $g_qty;
							$qc_reject_qty = $qc_reject_qty - $g_qty;
							$g_qty = $g_qty - $g_qty;
							
							
						}else if($qc_reject_qty !="" && $qc_reject_qty > 0 && $g_qty > 0 && $g_qty > $qc_reject_qty){
							
							$info_su['reject_qty']		= $qc_reject_qty;
							$g_total_qty = $g_total_qty + $qc_reject_qty;
							$qc_total_qty = $qc_total_qty - $qc_reject_qty;
							$qc_reject_qty = $qc_reject_qty - $qc_reject_qty;
							$g_qty = $g_qty - $qc_reject_qty;
							
						}

						if($qc_reprocess_qty !="" && $qc_reprocess_qty > 0 && $g_qty > 0 && $g_qty <= $qc_reprocess_qty){

							$info_su['reprocess_qty']	= $g_qty;
							$g_total_qty = $g_total_qty + $g_qty;
							$qc_total_qty = $qc_total_qty - $g_qty;
							$qc_reprocess_qty = $qc_reprocess_qty - $g_qty;
							$g_qty = $g_qty - $g_qty;
							
						}else if($qc_reprocess_qty !="" && $qc_reprocess_qty > 0 && $g_qty > 0 && $g_qty > $qc_reprocess_qty){
							
							$info_su['reprocess_qty']		= $qc_reprocess_qty;
							$g_total_qty = $g_total_qty + $qc_reprocess_qty;
							$qc_total_qty = $qc_total_qty - $qc_reprocess_qty;
							$qc_reprocess_qty = $qc_reprocess_qty - $qc_reprocess_qty;
							$g_qty = $g_qty - $qc_reprocess_qty;
							
						}

						$info_su['total_qty']	= $g_total_qty;
						
						if($g_total_qty > 0 && !empty($g_total_qty)){
							$inserid_sub=add_record('tbl_qc_process_trn',$info_su, $dbcon,$branch_id);
						}
					}
				} 		
				else if ($qc_total_qty > 0 && $qc_total_qty != ""){
					$info_su['accept_qty']		= $qc_accept_qty;
					$info_su['reject_qty']		= $qc_reject_qty;
					$info_su['reprocess_qty']	= $qc_reprocess_qty;
					$info_su['total_qty']		= $qc_total_qty;
					
					$inserid_sub=add_record('tbl_qc_process_trn',$info_su, $dbcon,$branch_id);
				}
					
							
					
					/* $set_job_qty="select * from tbl_jobwork_process where p_id=".$set_row['p_id'];
					$ser_job_qty=$dbcon->query($set_job_qty);
						while($row_job_qty=mysqli_fetch_assoc($ser_job_qty)){
							$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
						} */
				}
			}else{
				$stock_status='1';
				//var_dump($accept);
				$accept_qty_new=$accept;
				$reject_qty_new=$reject;
				
				if($reject>0){
						$reject_qty=$reject;
						$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$grn_no'");
						$c_mrn=mysqli_num_rows($sel_m);
						
						if($c_mrn==0)
						{
							$info2['mrn_no']			= "1";			
							$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
							$info2['grn_no']			= $grn_no;			
							$info2['qc_no']				= $inserid;	
							$info2['purchaseorder_id']	= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;	
							// Umair Start 05-03-2021
							$info2['qc_reject_godown']	= $arr_reject_godown_id[$i];	
							// Umair End 05-03-2021	

							$info2['cdate']				= date("Y-m-d H:i:s");
							$info2['user_id']			= $_SESSION['user_id'];
							$info2['company_id']		= $_SESSION['company_id'];
							
							$inserid_mrn=add_record('tbl_mrn', $info2, $dbcon,$branch_id);
						}
						else
						{
							$r_m=mysqli_fetch_assoc($sel_m);
							$inserid_mrn=$r_m['mrn_id'];
						}
						
						$info3['mrn_no']		= $inserid_mrn;			
						$info3['product_id']	= $grn_product;			
						$info3['rejected_qty']	= $reject_qty;	
						// Umair Start 05-03-2021
						$info3['qc_reject_godown']	=  $arr_reject_godown_id[$i];	
						// Umair End 05-03-2021	
						
						$info3['cdate']			= date("Y-m-d H:i:s");
						$info3['user_id']		= $_SESSION['user_id'];
						$info3['company_id']	= $_SESSION['company_id'];	
						
						$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
						
						// $grn_ref=$POST['grn_ref'];
						
						// $dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
					}
				}
			
				$info1['qc_id']				= $inserid;			
				$info1['qc_product']		= $grn_product;			
				$info1['qc_product_qty']	= $arr_grn_pqty[$i];		
				$info1['qc_accepted'] 		= $arr_accept[$i];	
				$info1['qc_rejected']		= $arr_reject[$i];	
				$info1['qty_reprocess']		= $arr_reprocess[$i];	
				$info1['stock_status']		= $stock_status;	
				$info1['po_id']				= (isset($POST['po_id'])) ? $POST['po_id'][$i] : 0;		
				$info1['qc_unit_id']		= $unit_id;
				// Umair Start 05-03-2021	
				$info1['qc_reject_godown']		= $arr_reject_godown_id[$i];	
				$info1['qc_reporcess_godown']	= $arr_reprocess_godown_id[$i];		
				// Umair Start 05-03-2021

				$info1['cdate']				= date("Y-m-d H:i:s");
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];	

				$insertid_qc=add_record('tbl_qc_trn', $info1, $dbcon,$branch_id);
				
				$set11="select * from tbl_request_product where rp_id in (".$arr_po_ref_id[$i].")";
				$ser=$dbcon->query($set11);
				$accept_qty_a=0;
				$reject_qty_a=0;
				while($set_row=mysqli_fetch_assoc($ser)){
					
					$query1w="select IFNULL(sum(total_qty),0) as used_qc from tbl_qc_process_trn as p
						where p.p_id=0 and qc_process_status=0 and p.p_ref_id=".$set_row['rp_id']." and company_id=".$_SESSION['company_id'];
					 
					 $rs_dispatchw=$dbcon->query($query1w);
					 $rel1w=mysqli_fetch_assoc($rs_dispatchw);
					 
					 $request_rp_po_qty=convert_stock($dbcon,$set_row['rp_po_qty'],$grn_product,"base_unit");
						$reserve_pending_qty=$request_rp_po_qty-$rel1w['used_qc'];
					//var_dump($request_rp_po_qty);
					 //var_dump($reserve_pending_qty);
						//var_dump($reserve_pending_qty);
						//var_dump($accept_qty_new);
					if($accept_qty_new>0){
						if($accept_qty_new>=$reserve_pending_qty){
							$accept_qty_a=$reserve_pending_qty;
							//var_dump($accept_qty_a);
							$accept_qty_new=$accept_qty_new-$reserve_pending_qty;
						}else{
							$accept_qty_a=$accept_qty_new;
							//var_dump($accept_qty_a);
							$reserve_pending_qty=$reserve_pending_qty-$accept_qty_new;
							if($reject_qty_new>0){
								if($reject_qty_new>=$reserve_pending_qty){
									$reject_qty_a=$reserve_pending_qty;
									$reject_qty_new=$reject_qty_new-$reserve_pending_qty;
								}else{
									$reject_qty_a=$reject_qty_new;
									$reject_qty_new=$reject_qty_new-$reject_qty_new;
								}
							}
							$accept_qty_new=$accept_qty_new-$accept_qty_new;
						}
					}else{
						if($reject_qty_new>0){
							if($reject_qty_new>=$reserve_pending_qty){
								$reject_qty_a=$reserve_pending_qty;
								$reject_qty_new=$reject_qty_new-$reserve_pending_qty;
							}else{
								$reject_qty_a=$reject_qty_new;
								$reject_qty_new=$reject_qty_new-$reject_qty_new;
							}
						}
					}
					
					$total_qty=$accept_qty_a+$reject_qty_a;
					//$info_su['p_id']			= $set_row['p_id'];
					$info_su['qc_id']			= $inserid;
					$info_su['product_id']		= $grn_product;
					$info_su['p_ref_id']		= $set_row['rp_id'];
					$info_su['accept_qty']		= $accept_qty_a;
					$info_su['reject_qty']		= $reject_qty_a;
					// Umair Start 05-03-2021	
					$info_su['qc_reject_godown']		= $arr_reject_godown_id[$i];	
					// Umair Start 05-03-2021
					//$info_su['reprocess_qty']	= $reprocess_qty;
					$info_su['total_qty']		= $total_qty;
					$info_su['company_id']		= $_SESSION['company_id'];
					$info_su['grn_trn_id']	= $grn_trn_id;	
					$info_su['qc_unit'] =$unit_id;	

					
					$grn_qry = "select * from tbl_grn_sub_trn where status = 0 and grn_trn_id = " . $grn_trn_id . " and rp_id = " . $set_row['rp_id'];
					$grn_res = $dbcon->query($grn_qry);
					if(brp_mysqli_num_rows($grn_res) > 0){
					while($grn_row=mysqli_fetch_assoc($grn_res)){
						$info_su['grn_sub_trn_id']	= $grn_row['grn_trn_sub_id'];
						$info_su['accept_qty']		= 0;
						$info_su['reject_qty']		= 0;
						$info_su['reprocess_qty']	= 0;
						$g_qty = 0;
						$g_total_qty = 0;
						if($unit_id == $grn_row['product_conv_unit']){
							$g_qty = $grn_row['product_conv_qty'];
						}else{
							$g_qty = $grn_row['product_qty'];
						}

					
						
						if($qc_accept_qty !="" && $qc_accept_qty > 0 && $g_qty > 0 && $g_qty <= $qc_accept_qty){
						
							$info_su['accept_qty']		= $g_qty;
							
							$g_total_qty = $g_total_qty + $g_qty;
							$qc_total_qty = $qc_total_qty - $g_qty;
							$qc_accept_qty = $qc_accept_qty - $g_qty;
							$g_qty = $g_qty - $g_qty;
						
							
						}else if($qc_accept_qty !="" && $qc_accept_qty > 0 && $g_qty > 0 && $g_qty > $qc_accept_qty){
							
							$info_su['accept_qty']		= $qc_accept_qty;
							$g_total_qty = $g_total_qty + $qc_accept_qty;
							$qc_total_qty = $qc_total_qty - $qc_accept_qty;
							$qc_accept_qty = $qc_accept_qty - $qc_accept_qty;
							$g_qty = $g_qty - $qc_accept_qty;
							
						}
						
						if($qc_reject_qty !="" && $qc_reject_qty > 0 && $g_qty > 0 && $g_qty <= $qc_reject_qty){
							
							$info_su['reject_qty']		= $g_qty;
							$g_total_qty = $g_total_qty + $g_qty;
							$qc_total_qty = $qc_total_qty - $g_qty;
							$qc_reject_qty = $qc_reject_qty - $g_qty;
							$g_qty = $g_qty - $g_qty;
							
						}else if($qc_reject_qty !="" && $qc_reject_qty > 0 && $g_qty > 0 && $g_qty > $qc_reject_qty){
							
							$info_su['reject_qty']		= $qc_reject_qty;
							$g_total_qty = $g_total_qty + $qc_reject_qty;
							$qc_total_qty = $qc_total_qty - $qc_reject_qty;
							$qc_reject_qty = $qc_reject_qty - $qc_reject_qty;
							$g_qty = $g_qty - $qc_reject_qty;
							
						}

						if($qc_reprocess_qty !="" && $qc_reprocess_qty > 0 && $g_qty > 0 && $g_qty <= $qc_reprocess_qty){

							$info_su['reprocess_qty']	= $g_qty;
							$g_total_qty = $g_total_qty + $g_qty;
							$qc_total_qty = $qc_total_qty - $g_qty;
							$qc_reprocess_qty = $qc_reprocess_qty - $g_qty;
							$g_qty = $g_qty - $g_qty;
							
						}else if($qc_reprocess_qty !="" && $qc_reprocess_qty > 0 && $g_qty > 0 && $g_qty > $qc_reprocess_qty){
							
							$info_su['reprocess_qty']		= $qc_reprocess_qty;
							$g_total_qty = $g_total_qty + $qc_reprocess_qty;
							$qc_total_qty = $qc_total_qty - $qc_reprocess_qty;
							$qc_reprocess_qty = $qc_reprocess_qty - $qc_reprocess_qty;
							$g_qty = $g_qty - $qc_reprocess_qty;
							
						}

						$info_su['total_qty']	= $g_total_qty;
						
						if($g_total_qty > 0 && !empty($g_total_qty)){
							$inserid_sub=add_record('tbl_qc_process_trn',$info_su, $dbcon,$branch_id);
						}
					}
				} 		
				else if ($qc_total_qty > 0 && $qc_total_qty != ""){
					$info_su['accept_qty']		= $qc_accept_qty;
					$info_su['reject_qty']		= $qc_reject_qty;
					$info_su['reprocess_qty']	= $qc_reprocess_qty;
					$info_su['total_qty']		= $qc_total_qty;
					
					$inserid_sub=add_record('tbl_qc_process_trn',$info_su, $dbcon,$branch_id);
				}

					//var_dump($info_su);
					// $inserid_sub=add_record('tbl_qc_process_trn',$info_su, $dbcon,$branch_id);
				}
			
			//$dbcon->query("update tbl_grn set qc_status='1' where grn_id='$POST[grn_no]'");

			$query_11="select count(batch_id) as total_qc from tbl_batch_data where grn_trn_id=".$grn_trn_id." and qc_status = 0 and status = 0";
			$rel_11=brp_mysqli_fetch_assoc($dbcon->query($query_11));

			if($rel_11['total_qc']=="0"){
				// echo "update : " . $POST['grn_trn_id'];
				$dbcon->query("update tbl_grn_trn set product_qc='1' where grn_trn_id='$grn_trn_id'");
			}
			
			if($inserid)
			{
				$resp['msg'] = "1";
			}
			else
			{
				$resp['msg'] = "0";
			}
			}

			
			$resp['back'] = strtolower($POST['back']);
			echo json_encode($resp); 
		
		}else if(strtolower($POST['mode']) == "get_product_unit") {
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['product_id'];
			$rs_type1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($rs_type1);

			if($row1['product_base_unit']!=$row1['product_conv_unit']){
				$row1['unit_status']="1";
				$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
        			$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
			}else{
				$row1['unit_status']="0";
				$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
			}
				//$row1['qye']=$query1;
				/*$row1['unit_option']=$opt;		
			echo json_encode($row1);*/
			echo $opt;
		}
	}
}
	

function add_new_product_batch($dbcon,$qc_id,$batch_id,$qc_no,$product_id,$unit_id,$reject_qty,$branch_id,$godown_id){
		$companyConfiguration=getCompanyConfiguration($dbcon);
	
	 	$pro_qry = "select * from product_mst where product_id = " .$product_id;
		$pro_result=$dbcon->query($pro_qry);
		$pr_row = brp_mysqli_fetch_assoc($pro_result);

		if($unit_id==$pr_row['product_conv_unit']){
			$type="base_unit";
			$conv_qty=$reject_qty;
			$base_qty = ($conv_qty/$pr_row['product_conv_qty']) * $pr_row['product_base_qty'];
		}else{
			$type="conv_unit";
			$base_qty=$reject_qty;
			$conv_qty = ($base_qty/$pr_row['product_base_qty']) *$pr_row['product_conv_qty'];
		}

		$batch_qty=$base_qty;
		$batch_conv_qty=$conv_qty;
			
		// $batch_info['grn_id']			= $grn_id;	
		// $batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
		// $batch_info['batch_no']			= $batch_no;
		$batch_info['batch_qty']		= $reject_qty;
		$batch_info['order_no']			= $qc_no;
		$batch_info['product_id']		= $product_id;
		$batch_info['grn_date']			= date('Y-m-d');
		$batch_info['batch_type']		= $companyConfiguration['batch_type'];
		$batch_info['production_type']	= '1';			
		$batch_info['status']			= '0';			
		$batch_info['grn_godown']			= $godown_id;			
		
		$batch_info['qc_status']		= 1;
		$batch_info['accept_qty']		= $reject_qty;
		$batch_info['qc_qty']			= $reject_qty;
		
		$batch_info['cdate']			= date("Y-m-d H:i:s"); 
		$batch_info['user_id']			= $_SESSION['user_id'];
		$batch_info['company_id']		= $_SESSION['company_id'];	
		$batch_info['branch_id']		= $branch_id;
		$batch_info['batch_unit']		= $unit_id;
		$batch_info['base_qty']			= $batch_qty;
		$batch_info['base_unit']		= $pr_row['product_base_unit'];
		$batch_info['conv_qty']			= $batch_conv_qty;
		$batch_info['conv_unit']		= $pr_row['product_conv_unit'];
		$batch_info['qc_id']			= $qc_id;

		$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
	 
}
?>