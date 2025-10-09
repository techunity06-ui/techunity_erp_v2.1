<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
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
		
	
		if(strtolower($POST['mode']) == "end_process") {
			
			$process_id=$POST['process_id_hid'];
			$process_type_hid=$POST['process_type_hid'];
			$resource_id = $POST['resource_id'];
			$request_no = $POST['request_no'];
			$eid=$POST['eid'];
			$date=date("Y-m-d h:i:sa");
			$qty=$POST['machine_no1'];
			$productID = $POST['product_id_hid'];
			$branch_id = $POST['branch_id'];

			
			/*
			Code By Umair
			Comment: Manage the quantity based on the end
			*/
			$job_remain_qty = $POST['pr_p_qty1']-$POST['machine_no1'];
			$job_his_remaining =$job_remain_qty;
			if($job_remain_qty>0)
			{
				$job_id=find_jobwork_id_in_des($dbcon,$POST['product_id_hid'],$process_type_hid,$process_id,$branch_id);
				
				$job_loop = "SELECT * FROM `tbl_jobwork` WHERE jobwork_id in (".$job_id.") ORDER BY jobwork_id DESC";
				$job_result=$dbcon->query($job_loop);
				while($job_row=mysqli_fetch_assoc($job_result))
				{
					$job_pending_qty=$job_row['j_qty']-$job_row['used_qty'];
					if($job_remain_qty!="0"){
						if($job_remain_qty!=""){
							if($job_remain_qty>=$job_pending_qty){
								$jobwork_update = $dbcon->query("UPDATE tbl_jobwork SET j_qty = j_qty-".$job_pending_qty." WHERE jobwork_id=".$job_row['jobwork_id']);
								
								
								
								
								$job_pending_allo_qty=$job_pending_qty;
								
								$job_loop_allo = "SELECT * FROM `tbl_jobwork_process` WHERE jobwork_id=".$job_row['jobwork_id']." and qty>used_qty ORDER BY p_id DESC";
								$job_result_allo=$dbcon->query($job_loop_allo);
								while($job_row_allo=mysqli_fetch_assoc($job_result_allo))
								{
									$allopen=$job_row_allo['qty']-$job_row_allo['used_qty'];
									if($job_pending_allo_qty!="0"){
										if($job_pending_allo_qty!=""){
											if($job_pending_allo_qty>=$allopen){
												
												$dbcon->query("update tbl_allocate_process set start_qty=start_qty-".$allopen." where p_id=".$job_row_allo['p_id']."");
												
												$dbcon->query("update tbl_jobwork_process set qty=qty-".$allopen." where jobwork_process_id=".$job_row_allo['jobwork_process_id']."");
												
												$jtrn=$allopen;
												
												$job_loop_allo_trn = "SELECT * FROM `tbl_allocate_process_trn` WHERE pt_alloc_id=".$job_row_allo['p_id']." ORDER BY pt_id DESC";
												
												$job_result_allo_trn=$dbcon->query($job_loop_allo_trn);
												while($job_row_allo_trn=mysqli_fetch_assoc($job_result_allo_trn)){
													
													$job_loop_allo_trnsm = "SELECT IFNULL(sum(pt_qty),0) as used_qty FROM `tbl_allocate_process_trn` WHERE p_status=1 and parent_pt_id=".$job_row_allo['pt_id']." ORDER BY pt_id DESC";
													$job_result_allo_trnsm=$dbcon->query($job_loop_allo_trnsm);
													$job_row_allo_trnsm=mysqli_fetch_assoc($job_result_allo_trnsm);
													if($jtrn!="0" && $jtrn!="")
													{
														$penqty=$job_row_allo_trn['pt_qty']-$job_row_allo_trnsm['used_qty'];
														if($jtrn>=$penqty){
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$penqty." where pt_id=".$job_row_allo_trn['pt_id']."");
															
															
															$jtrn=$jtrn-$penqty;
														}else{
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$jtrn." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$jtrn;
														}
													}
												}
												$job_pending_allo_qty=$job_pending_allo_qty-$allopen;
													
											}else{
												$dbcon->query("update tbl_allocate_process set start_qty=start_qty-".$job_pending_allo_qty." where p_id=".$job_row_allo['p_id']."");
												
												$dbcon->query("update tbl_jobwork_process set qty=qty-".$job_pending_allo_qty." where jobwork_process_id=".$job_row_allo['jobwork_process_id']."");
												
												$jtrn=$job_pending_allo_qty;
												
												$job_loop_allo_trn = "SELECT * FROM `tbl_allocate_process_trn` WHERE pt_alloc_id=".$job_row_allo['p_id']." ORDER BY pt_id DESC";
												
												$job_result_allo_trn=$dbcon->query($job_loop_allo_trn);
												while($job_row_allo_trn=mysqli_fetch_assoc($job_result_allo_trn))
												{
													
													$job_loop_allo_trnsm = "SELECT IFNULL(sum(pt_qty),0) as used_qty FROM `tbl_allocate_process_trn` WHERE p_status=1 and parent_pt_id=".$job_row_allo['pt_id']." ORDER BY pt_id DESC";
													$job_result_allo_trnsm=$dbcon->query($job_loop_allo_trnsm);
													$job_row_allo_trnsm=mysqli_fetch_assoc($job_result_allo_trnsm);
													if($jtrn!="0" && $jtrn!="")
													{
														$penqty=$job_row_allo_trn['pt_qty']-$job_row_allo_trnsm['used_qty'];
														if($jtrn>=$penqty){
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$penqty." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$penqty;
														}else{
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$jtrn." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$jtrn;
														}//3 WHILE 2 IF CLOSE
													}//3 WHILE 1 IF CLOSE
												} //3 WHILE CLOSE	
												$job_pending_allo_qty=$job_pending_allo_qty-$job_pending_allo_qty;
											}//2 WHILW  IF3 CLOSE
										}//2 WHILW 2 IF CLOSE
									}//2 WHILW 1 IF CLOSE
								}//WHILE CLOSE INNER WHILE

								$job_remain_qty=$job_remain_qty-$job_pending_qty;
							}else{
								$jobwork_update = $dbcon->query("UPDATE tbl_jobwork SET j_qty = j_qty-".$job_remain_qty." WHERE jobwork_id=".$job_row['jobwork_id']);
								
								
								
								$job_pending_allo_qty=$job_remain_qty;
								
								$job_loop_allo = "SELECT * FROM `tbl_jobwork_process` WHERE jobwork_id=".$job_row['jobwork_id']." and qty>used_qty ORDER BY p_id DESC";
								$job_result_allo=$dbcon->query($job_loop_allo);
								while($job_row_allo=mysqli_fetch_assoc($job_result_allo))
								{
									$allopen=$job_row_allo['qty']-$job_row_allo['used_qty'];
									if($job_pending_allo_qty!="0"){
										if($job_pending_allo_qty!=""){
											if($job_pending_allo_qty>=$allopen){
												$dbcon->query("update tbl_allocate_process set start_qty=start_qty-".$allopen." where p_id=".$job_row_allo['p_id']."");
												
												$dbcon->query("update tbl_jobwork_process set qty=qty-".$allopen." where jobwork_process_id=".$job_row_allo['jobwork_process_id']."");
												
												$jtrn=$allopen;
												
												$job_loop_allo_trn = "SELECT * FROM `tbl_allocate_process_trn` WHERE pt_alloc_id=".$job_row_allo['p_id']." ORDER BY pt_id DESC";
												
												$job_result_allo_trn=$dbcon->query($job_loop_allo_trn);
												while($job_row_allo_trn=mysqli_fetch_assoc($job_result_allo_trn)){
													
													$job_loop_allo_trnsm = "SELECT IFNULL(sum(pt_qty),0) as used_qty FROM `tbl_allocate_process_trn` WHERE p_status=1 and parent_pt_id=".$job_row_allo['pt_id']." ORDER BY pt_id DESC";
													$job_result_allo_trnsm=$dbcon->query($job_loop_allo_trnsm);
													$job_row_allo_trnsm=mysqli_fetch_assoc($job_result_allo_trnsm);
													if($jtrn!="0" && $jtrn!="")
													{
														$penqty=$job_row_allo_trn['pt_qty']-$job_row_allo_trnsm['used_qty'];
														if($jtrn>=$penqty){
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$penqty." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$penqty;
														}else{
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$jtrn." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$jtrn;
														}
													}
												}

												$job_pending_allo_qty=$job_pending_allo_qty-$allopen;
											}else{
												$dbcon->query("update tbl_allocate_process set start_qty=start_qty-".$job_pending_allo_qty." where p_id=".$job_row_allo['p_id']."");
												
												$dbcon->query("update tbl_jobwork_process set qty=qty-".$job_pending_allo_qty." where jobwork_process_id=".$job_row_allo['jobwork_process_id']."");
												
												$jtrn=$job_pending_allo_qty;
												
												$job_loop_allo_trn = "SELECT * FROM `tbl_allocate_process_trn` WHERE pt_alloc_id=".$job_row_allo['p_id']." ORDER BY pt_id DESC";
												
												$job_result_allo_trn=$dbcon->query($job_loop_allo_trn);
												while($job_row_allo_trn=mysqli_fetch_assoc($job_result_allo_trn)){
													
													$job_loop_allo_trnsm = "SELECT IFNULL(sum(pt_qty),0) as used_qty FROM `tbl_allocate_process_trn` WHERE p_status=1 and parent_pt_id=".$job_row_allo['pt_id']." ORDER BY pt_id DESC";
													$job_result_allo_trnsm=$dbcon->query($job_loop_allo_trnsm);
													$job_row_allo_trnsm=mysqli_fetch_assoc($job_result_allo_trnsm);
													if($jtrn!="0" && $jtrn!="")
													{
														$penqty=$job_row_allo_trn['pt_qty']-$job_row_allo_trnsm['used_qty'];
														if($jtrn>=$penqty){
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$penqty." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$penqty;
														}else{
															$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$jtrn." where pt_id=".$job_row_allo_trn['pt_id']."");
															$jtrn=$jtrn-$jtrn;
														}
													}
												}	
													
												$job_pending_allo_qty=$job_pending_allo_qty-$job_pending_allo_qty;
											}
										}
									}
								}
								
								$job_remain_qty=$job_remain_qty-$job_remain_qty;
							}//WHILE INNER 3 IF CLOSE
						}//WHILE INNER 2 IF CLOSE
					}//WHILE INNER 1 IF CLOSE
			}		
			if(!empty($branch_id)){
				$jo_branch_where=" and branch_id=".$branch_id;
			}	
				
			$job_his = "SELECT * FROM `tbl_jobwork_history` WHERE `process_id` = '".$process_id."' AND `p_status` = '1' AND `p_product_id` = '".$productID."' AND `pr_process_type`='1' AND company_id='".$_SESSION['company_id']."' ".$jo_branch_where." ORDER BY p_id DESC";
			$job_his_res=$dbcon->query($job_his);
			while($job_his_raw=mysqli_fetch_assoc($job_his_res)){
				if($job_his_remaining!="0"){
					if($job_his_remaining!=""){
						if($job_his_raw['p_qty']>=$job_his_remaining){
							$jobwork_history_update = $dbcon->query("UPDATE tbl_jobwork_history SET p_qty = p_qty - ".$job_his_remaining." WHERE p_id=".$job_his_raw['p_id']." and company_id=".$_SESSION['company_id']);
							$job_his_remaining=$job_his_remaining-$job_his_remaining;
						}else{
							$jobwork_history_update = $dbcon->query("UPDATE tbl_jobwork_history SET p_qty = p_qty -".$job_his_raw['p_qty']." WHERE p_id=".$job_his_raw['p_id']." and company_id=".$_SESSION['company_id']);
							$job_his_remaining=$job_his_remaining-$job_his_raw['p_qty'];
						}
					}
				}
			}
			//}//WHILE LOOP CLOSE		
				//die();
				
				
				
				$j_pr_process_id = $POST['process_id_hid'];
				$j_alloc_process_id = $POST['alloc_id'];

				//$remain_qty = $POST['pr_p_qty1']-$POST['machine_no1'];
				$productID = $POST['product_id_hid'];

				// Update proceess start for the tbl_jobwork 
				/* $sql = "SELECT * FROM `tbl_jobwork` WHERE `j_pr_process_id` = '".$j_pr_process_id."' AND `j_alloc_process_id` = '".$j_alloc_process_id."' AND `j_product_id` = '".$productID."' AND company_id='".$_SESSION['company_id']."' ORDER BY jobwork_id DESC LIMIT 1";
				$exec=$dbcon->query($sql);
				$jobwork_res=mysqli_fetch_assoc($exec);
				$jobwork_id = $jobwork_res['jobwork_id'];
				$j_product_id = $jobwork_res['j_product_id'];
				
				// Update the j_qty in tbl_jobwork table
				$jobwork_update = $dbcon->query("UPDATE tbl_jobwork SET j_qty = j_qty - '$remain_qty' WHERE jobwork_id='".$jobwork_id."' and company_id=".$_SESSION['company_id']);

				// End tbl_jobwork 


				// Update proceess start for the tbl_jobwork_history 
				$sql1 = "SELECT * FROM `tbl_jobwork_history` WHERE `process_id` = '".$j_pr_process_id."' AND `p_status` = '1' AND `p_product_id` = '".$j_product_id."' AND `pr_process_type`='1' AND company_id='".$_SESSION['company_id']."' ORDER BY p_id DESC LIMIT 1";
				$exec1=$dbcon->query($sql1);
				$jobworkhistory_res=mysqli_fetch_assoc($exec1);
				$jobwork_his_id = $jobworkhistory_res['p_id'];


				$jobwork_history_update = $dbcon->query("UPDATE tbl_jobwork_history SET p_qty = p_qty - '$remain_qty' WHERE p_id='".$jobwork_his_id."' and company_id=".$_SESSION['company_id']);
				// End tbl_jobwork_history 

				// Update proceess start for the tbl_allocate_process 
				$query="select * from tbl_allocate_process where p_id in (".$POST['alloc_id'].")";
				//var_dump($query);
				$result_exe=$dbcon->query($query);
				while($data=mysqli_fetch_assoc($result_exe)){
					
					$dbcon->query("update tbl_allocate_process set start_qty=start_qty-".$remain_qty." where p_id=".$POST['alloc_id']."");


					// Update proceess start for the tbl_allocate_process_trn 	
					$allocate_process_trn_sql = "SELECT * FROM `tbl_allocate_process_trn` WHERE `parent_pt_id` = 0 AND `pt_alloc_id` = '".$j_alloc_process_id."' AND `pt_process_id` = '".$j_pr_process_id."' AND `pt_product_id` = '".$productID."' ORDER BY pt_id DESC limit 1";
					$exec2=$dbcon->query($allocate_process_trn_sql);
					$allocate_process_trn_res=mysqli_fetch_assoc($exec2);
					$allocate_process_trn_id = $allocate_process_trn_res['pt_id'];

					$dbcon->query("update tbl_allocate_process_trn set pt_qty=pt_qty-".$remain_qty." where pt_id=".$allocate_process_trn_id."");
					// Update proceess start for the tbl_allocate_process_trn 


					// Update proceess start for the tbl_jobwork_process 
					$dbcon->query("update tbl_jobwork_process set qty=qty-".$remain_qty." where jobwork_id=".$jobwork_id."");
					// Update proceess start for the tbl_jobwork_process 
				} */
				// End tbl_allocate_process 
			}//MAIN IF CLOSE
			/*End */
			//die();
			
			//add_process_trn($dbcon,$eid,$POST['request_no'],$POST['product_id_hid'],$POST['process_id_hid'],$POST['machine_no1'],"1");
			if($process_type_hid==1)
			{
				//$grn_status='1';
				$grn_status='0';
			}
			else
			{
				$grn_status='0';
			}
			
			$job_id=find_jobwork_id($dbcon,$POST['product_id_hid'],$process_type_hid,$process_id,$branch_id);
			
			if($POST['machine_no1']!=0)
			{
				if($POST['process_type_hid']=="1"){
					$info['grn_no']				= $POST['grn_no'];
					$info['grn_date']			= date('Y-m-d');
					//$info['vender_id']		= $POST['vender_id'];
					$info['invoice_no']			= $POST['process_no'];
					$info['challan_no']			= $POST['process_no'];
					$info['ref_type']			= '1';
					$info['purchaseorder_id']	= $job_id;
					$info['remark']				= 'inhouse process';
					//$info['ref_no']				= $_POST['request_no'];
					$info['grn_status']			= $grn_status;
					//$info['product_qc']		= $POST['product_qc'];
					
					$info['cdate']				= date("Y-m-d H:i:s"); 
					$info['user_id']			= $_SESSION['user_id'];
					$info['company_id']			= $_SESSION['company_id'];
					
					$inserpoid=add_record('tbl_grn', $info, $dbcon,$branch_id);
					
					if(strtolower($POST['product_qc'])=="no"){
						$godown_id=$POST['grn_godown'];
						$product_qc=1;
					}else{
						$godown_id="";
						$product_qc=0;
					}
					
					//$info2['purchaseorder_id']	=$POST['jobwork_id'];
					$info2['product_id']		= $POST['product_id_hid'];
					$info2['grn_id']			= $inserpoid;
					$info2['product_qty']		= $POST['machine_no1'];
					$info2['unit_id']			= $POST['process_unit'];
					$info2['grn_godown']		= $godown_id;
					$info2['product_qc']		= $product_qc;
					$info2['po_ref_id']			= $_POST['request_no'];
					
					$info2['cdate']				= date("Y-m-d H:i:s");
					$info2['user_id']			= $_SESSION['user_id'];
					$info2['company_id']		= $_SESSION['company_id'];
					//pathik add 26-02-2021 3:25 start
					
					$info2['process_id']		= $process_id;
					
					//pathik add 26-02-2021 3:25 end
					
					$tbl_grn_trn_id=add_record('tbl_grn_trn', $info2, $dbcon,$branch_id);
					
					$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=14 and company_id=".$_SESSION['company_id']);

					/*
					Code By Umair: 11/11/2020
					Comment: Update the completed quantity and time entry in tbl_work_order_resource_allocate database 
					*/
					$com_qty = $POST['machine_no1'];
					update_completed_process_time_and_qty($dbcon, $process_id, $resource_id, $request_no, $com_qty);
				}

			}

			if($POST['process_type_hid']=="1"){
				$query2="select * from tbl_jobwork where jobwork_id in (".$job_id.")";
				$result2=$dbcon->query($query2);
				$jpqty=$qty;
				//var_dump($jpqty);
				//var_dump($query2);
				while($row2=mysqli_fetch_assoc($result2)){
					$jqty=$row2['j_qty']-$row2['used_qty'];
					
					//var_dump($jqty);
					if($jpqty>0){
						if($jpqty>=$jqty){
							
							$infogtrn['product_id']			= $row['p_product_id'];
							$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;
							$infogtrn['jobwork_id']			= $row2['jobwork_id'];
							$infogtrn['product_qty']		= $jqty;
							$infogtrn['cdate']				= date("Y-m-d H:i:s");
							$infogtrn['user_id']			= $_SESSION['user_id'];
							$infogtrn['company_id']			= $_SESSION['company_id'];
							$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon,$branch_id);
							
							$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$jqty." where jobwork_id=".$row2['jobwork_id']."");
							
							$query211="select * from tbl_jobwork_process where jobwork_id =".$row2['jobwork_id'];
							//var_dump($query211);
							$result211=$dbcon->query($query211);
							$jq=$jqty;
							//var_dump($jq);
							while($row211=mysqli_fetch_assoc($result211))
							{
								if($jq>0){
									if($jq>=$row211['qty']){
										$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$row211['qty']." where jobwork_id=".$row2['jobwork_id']." and p_id=".$row211['p_id']."");
										$jq=$jq-$row211['qty'];
									}else{
										$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$jq." where jobwork_id=".$row2['jobwork_id']." and p_id=".$row211['p_id']."");
										$jq=$jq-$jq;
									}
								}
							}
							
							$jpqty=$jpqty-$jqty;
							//var_dump("123");
						}else{
							$infogtrn['product_id']			= $row['p_product_id'];
							$infogtrn['grn_trn_id']			= $tbl_grn_trn_id;
							$infogtrn['jobwork_id']			= $row2['jobwork_id'];
							$infogtrn['product_qty']		= $jpqty;
							$infogtrn['cdate']				= date("Y-m-d H:i:s");
							$infogtrn['user_id']			= $_SESSION['user_id'];
							$infogtrn['company_id']			= $_SESSION['company_id'];
							$tbl_grn_trn_id1=add_record('tbl_grn_sub_trn', $infogtrn, $dbcon,$branch_id);
							
							$dbcon->query("update tbl_jobwork set used_qty=used_qty+".$jpqty." where jobwork_id=".$row2['jobwork_id']."");
							
							$query211="select * from tbl_jobwork_process where jobwork_id =".$row2['jobwork_id'];
							//var_dump($query211);
							$result211=$dbcon->query($query211);
							$jq=$jpqty;
							//var_dump($jq);
							while($row211=mysqli_fetch_assoc($result211))
							{
								if($jq>0){
									if($jq>=$row211['qty']){
										$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$row211['qty']." where jobwork_id=".$row2['jobwork_id']." and p_id=".$row211['p_id']."");
										$jq=$jq-$row211['qty'];
									}else{
										$dbcon->query("update tbl_jobwork_process set used_qty=used_qty+".$jq." where jobwork_id=".$row2['jobwork_id']." and p_id=".$row211['p_id']."");
										$jq=$jq-$jq;
									}
								}
							}
							
							$jpqty=$jpqty-$jpqty;
							//var_dump("345");
						}
						
					}
				}
			}
			

			/* Code By Umair
			   Comment: Update tbl_resource_schedule table
			   Start
			*/

			if($POST['machine_no1']!=0){
				$resource_sch_where = '';	
				if(isset($_SESSION['resource_id']) && $_SESSION['resource_id']!=""){
					$resource_sch_where = ' and resource_id = "'.$_SESSION['resource_id'].'" ';
				}	
				$resource_schedule_sql = 'select * from tbl_resource_schedule where process_id="'.$POST['process_id_hid'].'" and p_product_id="'.$POST['product_id_hid'].'" and work_status="1" and company_id="'.$_SESSION['company_id'].'" '.$resource_sch_where.' ';	
				$resource_schedule_exec=$dbcon->query($resource_schedule_sql);

				$entered_qty = (float)$POST['machine_no1'];
				$sqty=0;$jpqty=0;$pending_qty=0;

				while($resource_schedule_data=mysqli_fetch_assoc($resource_schedule_exec)){
					$resource_schedule_id = $resource_schedule_data['resource_schedule_id'];
				
					$query11="select sum(pt_qty) as start_qty from tbl_allocate_process_trn as trn where p_status=0 and pt_ref_id=".$resource_schedule_data['rp_id'];
					$rel1=mysqli_fetch_assoc($dbcon->query($query11));
					
					$query12="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_ref_id=".$resource_schedule_data['rp_id'];
					$rel2=mysqli_fetch_assoc($dbcon->query($query12));
					
					$pending_qty=$rel1['start_qty']-$rel2['end_qty'];
					$pending_qty= (float)$pending_qty;

					
					if($pending_qty>=$entered_qty){
						$sqty=$entered_qty;
						$jpqty=$sqty;
						//echo "if";die;
						/*$query13="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_ref_id=".$resource_schedule_data['rp_id'];
						$rel3=mysqli_fetch_assoc($dbcon->query($query13));*/
						
						$up_sql = "update tbl_resource_schedule set pen_qty=pen_qty-".$sqty.", process_qty = '0' where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
						$dbcon->query($up_sql);

						$query13="select start_qty from tbl_resource_schedule where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
						$rel3=mysqli_fetch_assoc($dbcon->query($query13));
						
						$rel1_start_qty = (float)$rel1['start_qty'];
						$rel3_end_qty = (float)$rel3['start_qty'];
						if($rel1_start_qty<=$rel3_end_qty){
							
							$update_status="update tbl_resource_schedule set work_status=0 where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
							$dbcon->query($update_status);
						}
						
						if($resource_schedule_data['p_qty']==$rel3['start_qty']){
							$update_end_date = "update tbl_resource_schedule set actual_end_date='".date('Y-m-d H:i:s')."', work_status=2 where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
							$dbcon->query($update_end_date);
						}

					}else{
						$sqty=$pending_qty;
						$jpqty=$sqty;
						
						$up_sql = "update tbl_resource_schedule set pen_qty=pen_qty-".$sqty.", process_qty = '0' where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
						$dbcon->query($up_sql);

						$query13="select start_qty from tbl_resource_schedule where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
						$rel3=mysqli_fetch_assoc($dbcon->query($query13));
						
						$rel1_start_qty = (float)$rel1['start_qty'];
						$rel3_end_qty = (float)$rel3['start_qty'];
						if($rel1_start_qty<=$rel3_end_qty){
							
							$update_status="update tbl_resource_schedule set work_status=0 where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
							$dbcon->query($update_status);
						}
						
						if($resource_schedule_data['p_qty']==$rel3['start_qty']){
							$update_end_date = "update tbl_resource_schedule set actual_end_date='".date('Y-m-d H:i:s')."',work_status=2 where resource_schedule_id=".$resource_schedule_data['resource_schedule_id'];
							$dbcon->query($update_end_date);
						}

					}	
				}
			}  
			
			/* Code By Umair
			   Comment: Update tbl_resource_schedule table
			   End
			*/

			$sqty=0;$jpqty=0;$pending_qty=0;$pextra_used=0;$last_extra=0;
			
			
			
			$query="select * from tbl_allocate_process where p_id in (".$eid.")";
			//var_dump($query);
			$result=$dbcon->query($query);
			$pp=1;
			$cnt1=mysqli_num_rows($result);
			while($row=mysqli_fetch_assoc($result)){
				
				$query11="select sum(pt_qty) as start_qty from tbl_allocate_process_trn as trn where p_status=0 and pt_alloc_id=".$row['p_id'];
				$rel1=mysqli_fetch_assoc($dbcon->query($query11));
				
				$query12="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_alloc_id=".$row['p_id'];
				$rel2=mysqli_fetch_assoc($dbcon->query($query12));
				
				//var_dump($query12);
				//var_dump($rel2['end_qty']);
				
				$pending_qty=$rel1['start_qty']-$rel2['end_qty'];
				
				
				if($pending_qty>=$qty){
					$sqty=$qty;
					$jpqty=$sqty;
					
					add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$sqty,"1","");
					
					$query13="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_alloc_id=".$row['p_id'];
					$rel3=mysqli_fetch_assoc($dbcon->query($query13));
					if(strtolower($POST['product_qc'])=="no"){
					
						$process=get_next_process($dbcon,$row['process_id'],$info2['product_id'],$row['p_ref_id'],$row['process_priority']);
						$process_pr=json_decode($process);
			
						$process_id_new=$process_pr->process_id;
						$process_type=$process_pr->process_type;
						$process_priority=$process_pr->process_priority;
						//var_dump($process_id_new);
							if($process_id_new==0){
								if($godown_id!=""){
									add_stock($dbcon,$info2['product_id'],$info2['unit_id'],$info['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$sqty,"1",$branch_id);
									
									add_request_reserve_stock($dbcon,$inserid,$sqty,$info2['unit_id'],$branch_id);
									
								}
							}else{
								process_allocate($dbcon,$row['p_id'],$process_id_new,$sqty,$row['p_ref_id'],"tbl_grn_trn",$info2['product_id'],$process_type,$info2['unit_id'],$process_priority,"",$branch_id);
							}
						add_process_stock($dbcon,$row['p_id'],$sqty,0,$process_id_new);
					}
					
					$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$sqty." where p_id=".$row['p_id']);
					
					if($rel1['start_qty']<=$rel3['end_qty']){
						$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$row['p_id'];
						$dbcon->query($bb);
					}
					
					if($row['p_qty']<=$rel3['end_qty']){
						$date=date("Y-m-d h:i:sa");
						$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$row['p_id']);
					}else{
						//var_dump($row['p_qty']);
						//var_dump($rel2['end_qty']);
					}					
					$info6['process_id']		=$row['process_id'];
					$info6['p_start_time']		='';
					$info6['p_end_time']		=$date;
					$info6['p_qty']				=$sqty;
					$info6['pen_qty']			='';
					$info6['p_status']			='2';
					$info6['p_ref_id']			=$row['p_ref_id'];
					$info6['p_product_id']		=$row['p_product_id'];
					$info6['j_alloc_process_id']=$row['p_id'];
					
					$info6['cdate']				= date("Y-m-d H:i:s");
					$info6['user_id']			= $_SESSION['user_id'];
					$info6['company_id']		= $_SESSION['company_id'];
					
					$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon,$branch_id);
					
					//code by pathik date: 1-12-2020 4:51 am
					//var_dump("22222");
					if($row['previous_process_id']!="0"){
						if($pp=="1"){
							$extraused=$POST['row_actual_qty'][0]-$POST['row_estimate_qty'][0];
							//$extraused=$POST['row_actual_qty']/$qty;
							if($extraused>0){
								$extr=$extraused/$cnt1;
								$dic=explode(".",$extr);
								$pextra_used=$dic['0'];
								if($dic['1']>0){
									//$last_extra=$cnt1*$dic['1'];
									$last_extra=1;
								}
							}
						}
						$used_p_qty=$sqty+$pextra_used;
						if($cnt1==$pp){
							$used_p_qty=$used_p_qty+$last_extra;
						}
					}
					
					//var_dump($used_p_qty);
						add_process_stock_new($dbcon,$row['p_id'],$sqty,$used_p_qty);

						if($row['previous_process_id']=="0"){
							$grn_qty=$POST['row_product_id'];
							for($k=0;$k<count($grn_qty);$k++)
							{
								$uqty=$POST['row_req_qty_one'][$k]*$sqty;
								
								$extraused1=$POST['row_actual_qty'][$k]-$uqty;
									//$extraused=$POST['row_actual_qty']/$qty;
									//var_dump($extraused1);
									if($extraused1>0){
										$extr1=$extraused1/$cnt1;
										//var_dump($extr1);
										$dic1=explode(".",$extr1);
										//var_dump($dic1['0']);
										$pextra_used1=$dic1['0'];
										if($dic1['1']>0){
											//$last_extra=$cnt1*$dic['1'];
											$last_extra1=1;
										}
									}
									$used_p_qty1=$uqty+$pextra_used1;
									if($cnt1==$pp){
										$used_p_qty1=$used_p_qty1+$last_extra1;
									}
								
								//var_dump($used_p_qty1);
								$used_p_qty1=round($used_p_qty1,4);
								$info2['allocate_process_id']	=$eid;
								$info2['product_id']			=$POST['row_product_id'][$k];
								$info2['unit_id']				=$POST['row_unit_id'][$k];
								$info2['used_qty']				=$used_p_qty1;
								$info2['cdate']					= date("Y-m-d H:i:s");
								$info2['user_id']				= $_SESSION['user_id'];
								$info2['company_id']			= $_SESSION['company_id'];
								
								$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon,$branch_id);
								
								$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty'],$branch_id);
								
								//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
								$request_id=find_request_id($dbcon,$row['p_ref_id'],$info2['product_id']);
								
								//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
								deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
							}
						}
					
					//code end pathik
					
				}else{
					$sqty=$pending_qty;
					$jpqty=$sqty;
					
					add_process_trn($dbcon,$row['p_id'],$row['p_ref_id'],$row['p_product_id'],$row['process_id'],$sqty,"1");
					
					$query13="select sum(pt_qty) as end_qty from tbl_allocate_process_trn as trn where p_status=1 and pt_alloc_id=".$row['p_id'];
					$rel3=mysqli_fetch_assoc($dbcon->query($query13));
					
					if(strtolower($POST['product_qc'])=="no"){
					
						$process=get_next_process($dbcon,$row['process_id'],$info2['product_id'],$row['p_ref_id']);
						$process_pr=json_decode($process);
			
						$process_id_new=$process_pr->process_id;
						$process_type=$process_pr->process_type;
						$process_priority=$process_pr->process_priority;
						//var_dump($process_id_new);
							if($process_id_new==0){
								if($godown_id!=""){
									add_stock($dbcon,$info2['product_id'],$info2['unit_id'],$info['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$sqty,"1",$branch_id);
									
									add_request_reserve_stock($dbcon,$inserid,$sqty,$info2['unit_id'],$branch_id);
									
								}
							}else{
								process_allocate($dbcon,$row['p_id'],$process_id_new,$sqty,$row['p_ref_id'],"tbl_grn_trn",$info2['product_id'],$process_type,$info2['unit_id'],$process_priority,"",$branch_id);
								
							}
						add_process_stock($dbcon,$row['p_id'],$sqty,0,$process_id_new);
					}
					
					
					$dbcon->query("update tbl_allocate_process set pen_qty=pen_qty-".$sqty." where p_id=".$row['p_id']);
					
					if($rel1['start_qty']<=$rel3['end_qty']){
						$bb="update tbl_allocate_process set p_status=0,task_status=0 where p_id=".$row['p_id'];
						$dbcon->query($bb);
					}
					
					if($row['p_qty']==$rel3['end_qty']){
						$date=date("Y-m-d h:i:sa");
						$dbcon->query("update tbl_allocate_process set task_status='2',p_end_time='".$date."',p_status=3 where p_id=".$row['p_id']);
					}
					
					$info6['process_id']		=$row['process_id'];
					$info6['p_start_time']		='';
					$info6['p_end_time']		=$date;
					$info6['p_qty']				=$sqty;
					$info6['pen_qty']			='';
					$info6['p_status']			='2';
					$info6['p_ref_id']			=$row['p_ref_id'];
					$info6['p_product_id']		=$row['p_product_id'];
					$info6['j_alloc_process_id']=$row['p_id'];
					
					$info6['cdate']				= date("Y-m-d H:i:s");
					$info6['user_id']			= $_SESSION['user_id'];
					$info6['company_id']		= $_SESSION['company_id'];
					
					$inserusrid1=add_record('tbl_jobwork_history', $info6, $dbcon,$branch_id);
					
					
					
					//code by pathik date: 1-12-2020 4:51 am
					
						if($row['previous_process_id']!="0"){
							if($pp=="1"){
								
								$extraused=$POST['row_actual_qty'][0]-$POST['row_estimate_qty'][0];
								//$extraused=$POST['row_actual_qty']/$qty;
							
								if($extraused>0){
									$extr=$extraused/$cnt1;
									$dic=explode(".",$extr);
									$pextra_used=$dic['0'];
									if($dic['1']>0){
										//$last_extra=$cnt1*$dic['1'];
										$last_extra=1;
									}
								}
							}
							$used_p_qty=$sqty+$pextra_used;
							if($cnt1==$pp){
								$used_p_qty=$used_p_qty+$last_extra;
							}
						}
						//var_dump("cds");
						//var_dump($used_p_qty);
						add_process_stock_new($dbcon,$row['p_id'],$sqty,$used_p_qty);

						if($row['previous_process_id']=="0"){
							$grn_qty=$POST['row_product_id'];
							for($k=0;$k<count($grn_qty);$k++)
							{
								$uqty=$POST['row_req_qty_one'][$k]*$sqty;
								
								
								$extraused1=$POST['row_actual_qty'][$k]-$uqty;
									//$extraused=$POST['row_actual_qty']/$qty;
									//var_dump($extraused1);
									if($extraused1>0){
										$extr1=$extraused1/$cnt1;
										//var_dump($extr1);
										$dic1=explode(".",$extr1);
										//var_dump($dic1['0']);
										$pextra_used1=$dic1['0'];
										if($dic1['1']>0){
											//$last_extra=$cnt1*$dic['1'];
											$last_extra1=1;
										}
									}
									$used_p_qty1=$uqty+$pextra_used1;
									if($cnt1==$pp){
										$used_p_qty1=$used_p_qty1+$last_extra1;
									}
								
								//var_dump($used_p_qty1);
								
								$used_p_qty1=round($used_p_qty1,4);
								$info2['allocate_process_id']	=$eid;
								$info2['product_id']			=$POST['row_product_id'][$k];
								$info2['unit_id']				=$POST['row_unit_id'][$k];
								$info2['used_qty']				=$used_p_qty1;
								$info2['cdate']					= date("Y-m-d H:i:s");
								$info2['user_id']				= $_SESSION['user_id'];
								$info2['company_id']			= $_SESSION['company_id'];
								
								$tbl_grn_trn_id=add_record('tbl_allocate_process_material',$info2, $dbcon,$branch_id);
								
								$hhd=minus_stock($dbcon,$info2['product_id'],$info2['unit_id'],date("Y-m-d"),"tbl_allocate_process_material",$tbl_grn_trn_id,$info2['used_qty'],$branch_id);
								
								//$request_id=find_request_id($dbcon,$info1['j_ref_id'],$info2['product_id']);
								$request_id=find_request_id($dbcon,$row['p_ref_id'],$info2['product_id']);
								
								//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
								deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
							}
						}
					
					//code end pathik
					
				}
				$pp++;
			}

			/*
				Code By Umair: 19/11/2020
				Comment: Change the end process logic
			*/

			/* $allocate_query="select * from tbl_allocate_process where p_id in (".$eid.")";
			$allocate_result=$dbcon->query($allocate_query);
			while($allocate_row=mysqli_fetch_assoc($allocate_result)){
				
				add_process_stock_new($dbcon,$allocate_row['p_id'],$POST['machine_no1']);

				if($allocate_row['previous_process_id']=="0"){
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
						$request_id=find_request_id($dbcon,$allocate_row['p_ref_id'],$info2['product_id']);
						
						//deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
						deduct_remove_stock($dbcon,$request_id,$info2['used_qty'],$info2['unit_id']);
					}
				}
			} */	


			/*
				Code By Umair
				Comment: Update  actual_start_date in tbl_resource_schedule
			*/
			
			
			echo "1";
		}
		else if(strtolower($POST['mode']) == "show_material_list_new") {

			$branch_id=$POST['branch_id'];
			
			if($POST['pre_alloc_id']=="0"){
				$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
					WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['eid'].")";
				$resul=$dbcon->query($bom);
				$rel1=mysqli_fetch_assoc($resul);
				
				$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
				left join product_mst as pro on pro.product_id=rpro.rp_pid
				left join tbl_category as tc on pro.product_category=tc.cat_id
				left join unit_mst as bunit on bunit.unitid=rpro.process_unit
				left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
				WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
				$result=$dbcon->query($bom1);
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					
					//$rel["req_qty_one"]=round($rel["req_qty_one"],6);
					$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
					$rel["req_qty_one"]=round($rel["req_qty_one"],6);
					$o_qty=round($o_qty,6);
					
					$total_req_qty=$POST['pending_qty']*$o_qty;
					$total_req_qty=round($total_req_qty,4);
					$used_qty=$POST['max_start_qty']*$o_qty;
					$used_qty=round($used_qty,4);
					
					$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],"","","","",$branch_id);
					$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["process_unit"]);
					
					$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],"","","","",$branch_id);
					
					
					//var_dump($cstock);
					//var_dump($rstock);
					$actualstock=$cur_stock+($cstock-$rstock);
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
					//$used_qty=round($cur_stock,4);
					echo '<table class="display table table-bordered table-striped" id="material_details">
							<thead>
							  <tr>
								<th>Product Name</th>
								<th>Product Category</th>
								<th>Qty Needed For Single Piece</th>
								<th>Total Required Qty</th>
								<th>Total Available Qty </th>
								<th>Estimate Usable Qty</th>
								<th>Actual Usable Qty</th>
								<th>Unit</th>
							  </tr>
							</thead>
						<tbody>								
						
						<tr>
							<td>'.$rel["product_name"].'
								<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" />
							</td>
							<td>'.$cat_name.'</td>
							<td>'.$o_qty.'
								<input type="hidden" class="" name="row_req_qty_one[]" id="row_req_qty_one'.$i.'" value="'.$o_qty.'" />
							</td>
							<td>'.$total_req_qty.'</td>
							<td>'.$cur_stock.'</td>
							<td>'.$used_qty.'
								<input type="hidden" class="" name="row_estimate_qty[]" id="row_estimate_qty'.$i.'" value="'.$used_qty.'" />
							</td>
							<td>
								<input type="number" class="form-control" name="row_actual_qty[]" id="row_actual_qty'.$i.'" value="'.$used_qty.'"  max="'.$actualstock.'" />
							</td>
							<td>'.$rel["base_unit_name"].'
								<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id'.$i.'" value="'.$rel['process_unit'].'" />
							</td>
					</tr>
					</tbody>
					</table>
					';
					$i++;
				}
			 }else{
				$pid=$POST['pid'];
				
				$bom1="SELECT group_concat(previous_process_id) as pre_id FROM `tbl_allocate_process` as rpro WHERE rpro.p_id in (".$pid.")" ;
				$result=$dbcon->query($bom1);
				$rel=mysqli_fetch_assoc($result);
				
				$bom22="SELECT sum(rpro.process_stock-rpro.process_used_stock) as tprocess_stock,pmst.product_name,tc.cat_name,uni.unit_name,promst.process_name,rpro.process_unit FROM `tbl_allocate_process` as rpro 
				left join product_mst as pmst on pmst.product_id=rpro.p_product_id
				left join tbl_category as tc on pmst.product_category=tc.cat_id
				left join unit_mst as uni on uni.unitid=rpro.process_unit
				left join process_mst as promst on promst.process_id=rpro.process_id
				WHERE rpro.p_id in (".$rel['pre_id'].")";
				$result22=$dbcon->query($bom22);
				$rel2=mysqli_fetch_assoc($result22);
				$i=1;
				$cat_name = ($rel2['cat_name']!=null) ? $rel2['cat_name'] : 'PRIMARY';
				echo '<table class="display table table-bordered table-striped" id="material_details">
							<thead>
							  <tr>
								<th>Product Name</th>
								<th>Product Category</th>
								<th>Total Required Qty</th>
								<th>Total Available Qty </th>
								<th>Estimate Usable Qty</th>
								<th>Actual Usable Qty</th>
								<th>Unit</th>
							  </tr>
							</thead>
						<tbody>	
						<tr>
							<td>'.$rel2["product_name"].' ('.$rel2["process_name"].')</td>
							<td>'.$cat_name.'</td>
							<td>'.$POST["pending_qty"].'</td>
							<td>'.$rel2["tprocess_stock"].' </td>
							<td>'.$POST["max_start_qty"].'
								<input type="hidden" class="" name="row_estimate_qty[]" id="row_estimate_qty" value="'.$POST["max_start_qty"].'" />
							</td>
							<td>
								<input type="number" class="form-control" name="row_actual_qty[]" id="row_actual_qty" value="'.$POST["max_start_qty"].'"  max="'.$POST["tprocess_stock"].'" />
							</td>
							<td>'.$rel2["unit_name"].'
								<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id" value="'.$rel2['process_unit'].'" />
							</td> 
						</tr>'; 
			} 

		}else if(strtolower($POST['mode']) == "open_scrap_entry") {
			
			$query_pro="select product_scrap_id,scrap_qty,material_issue_weight from product_mst as trn where product_id=".$POST['product_id'];
			$rel_pro=brp_mysqli_fetch_assoc($dbcon->query($query_pro));
			
			$query11="select product_sale_rate from product_mst as trn where product_id=".$rel_pro['product_scrap_id'];
			$rel1=brp_mysqli_fetch_assoc($dbcon->query($query11));
			
			
			$query_pl="select process_scrap_tolerance_plus,process_scrap_tolerance_minus from tbl_product_process as trn where status=0 and product_id=".$POST['product_id']." and process_id=".$POST['process_id'];
			$rel_pl=brp_mysqli_fetch_assoc($dbcon->query($query_pl));
			
			$id=$rel_pro['product_scrap_id'];
			$expected_scrap=$POST['qty']*$rel_pro['scrap_qty'];
			
			if($expected_scrap!=0){
				if($rel_pl['process_scrap_tolerance_plus']!="0"){
					$max_tol=(($expected_scrap*$rel_pl['process_scrap_tolerance_plus'])/100)+$expected_scrap;
				}else{
					$max_tol=$expected_scrap;
				}
			}else{
				$max_tol=0;
			}
		
			if($expected_scrap!=0){
				if($rel_pl['process_scrap_tolerance_plus']!="0"){
					$min_tol=(($expected_scrap*$rel_pl['process_scrap_tolerance_minus'])/100)-$expected_scrap;
				}else{
					$min_tol=$expected_scrap;
				}
			}else{
				$min_tol=0;
			}
			if($min_tol<0){
				$min_tol=0;
			}
			
			
			$str="";
			$str.="
					<div class='col-md-12' >
						<div class='col-md-6' >
							<div class='col-md-4'> Expected Scrap </div>
							<div class='col-md-8'>
								<input type='number' class='form-control' name='scrap_expected_qty' id='scrap_expected_qty' value='".$expected_scrap."' readonly />
							</div>
						</div>
						<div class='col-md-6' >
							<div class='col-md-4'>Rate</div>
							<div class='col-md-8'>
								<input type='number' class='form-control' name='scrap_rate' id='scrap_rate' value='".$rel1['product_sale_rate']."'  />
							</div>
						</div>
					</div>
					<div class='col-md-12' style='margin-top: 15px;'>
						<div class='col-md-6' >
							<div class='col-md-4'>Scrap Received</div>
							<div class='col-md-8'>
								<input type='number' class='form-control' name='scrap_received_qty' id='scrap_received_qty' value='' min='".$min_tol."' max='".$max_tol."'  />
							</div>
						</div>
						<div class='col-md-6' >
							<div class='col-md-4'>Scrap Code</div>
							<div class='col-md-8'>
								<select class='form-control' name='product_scrap_id' id='product_scrap_id' onchange='scrap_rate_change();' >
                                  ".getScrapCode($dbcon,$id)."
                                 </select>
							</div>
						</div>
					</div>
				<div class='col-md-12' style='margin-top: 15px;' >
					<center>
						<input type='button' id='scrap_save' name='scrap_save' class='btn btn-success' value='Save' onclick='scrap_save1();' />
					</center>
					<input type='hidden' name='sproduct' id='sproduct' value='".$POST['product_id']."' >
					<input type='hidden' name='sprocess' id='sprocess' value='".$POST['process_id']."' >
					<input type='hidden' name='sallo_id' id='sallo_id' value='".$POST['allo_id']."' >
					<input type='hidden' name='sbranch_id' id='sbranch_id' value='".$POST['branch_id']."' >
				</div>
			";
			echo $str;
		}else if(strtolower($POST['mode']) == "scrap_save") {
			$branch_id=$POST['sbranch_id'];
			$info2['alloc_id']				=$POST['sallo_id'];
			$info2['product_id']			=$POST['sproduct'];
			$info2['process_id']			=$POST['sprocess'];
			$info2['scrap_product_id']		=$POST['product_scrap_id'];
			$info2['qty']					=$POST['scrap_received_qty'];
			$info2['cdate']					= date("Y-m-d H:i:s");
			$info2['user_id']				= $_SESSION['user_id'];
			$info2['company_id']			= $_SESSION['company_id'];
			
			$tbl_grn_trn_id=add_record('tbl_scrap_add',$info2, $dbcon,$branch_id);
			
			$query11="select product_base_unit from product_mst as trn where product_id=".$info2['scrap_product_id'];
			$rel1=brp_mysqli_fetch_assoc($dbcon->query($query11));
			
			add_stock($dbcon,$info2['scrap_product_id'],$rel1['product_base_unit'],date("Y-m-d"),"process_end",$tbl_grn_trn_id,1,$info2['qty'],"1",$branch_id);
								
		}
		else if(strtolower($POST['mode'])== "scrap_rate_change") {
			//$row=array();
			$query1="select product_sale_rate from product_mst where product_id=".$POST['product_scrap_id'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
			
			echo json_encode($rows);
		}
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>