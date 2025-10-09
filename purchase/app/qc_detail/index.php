<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');
		//print_r($_POST);

$companyConfiguration=getCompanyConfiguration($dbcon);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
			$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
			
			//$where=" and g.qc_status=0 and FIND_IN_SET('product_qc',p.product_setting_check)";
			//grn.grn_status=0 and trn.grn_trn_status=0 and grn.qc_status=0 and trn.product_qc=0 and grn.ref_type='1'
			$where=" and g.qc_status=0 ";
			//$where=" ";
			$appData = array();
			$i=1;
			$aColumns = array('gt.product_id', 'p.product_type','p.product_name','g.ref_type','g.grn_no','gt.product_qty','g.grn_date','g.qc_status','g.grn_status','g.ref_type','g.product_qc','gt.grn_trn_id','p.product_setting_check');
			$sIndexColumn = "gt.grn_trn_id";
			$isWhere = array("gt.grn_trn_status = 0 and g.qc_status=0 and gt.product_qc=0".$where);
			$sTable = "tbl_grn_trn as gt";			
			$isJOIN = array('left join tbl_grn as g on g.grn_id=gt.grn_id','left join product_mst as p on p.product_id=gt.product_id');
			$hOrder = "grn_trn_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				
				if($row['ref_type']==1)
				{
					$ref_type='jobcard';
				}
				else
				{
					$ref_type='PO';
				}
				
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['product_name'];
				$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
				$row_data[] = $ref_type;
				$row_data[] = $row['grn_no'];
				$row_data[] = $row['product_qty'];
				$row_data[] = date("d-M-Y",strtotime($row['grn_date']));
				
				$row_data[] = '<a class="btn btn-xs btn-success" data-original-title="Add Qc" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'qc_add/'.$row['grn_trn_id'].'" ><i class="fa fa-plus"></i></a>';
				//$row_data[] = $edit_btn.' '.$delete_btn.' '.$mrn_btn.' '.$allocate_btn; 
				$row_data[] = ''; 
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode($output);
		}
		else if(strtolower($POST['mode']) == "add") {
			// echo "<pre>";print_r($POST);die;
			$batch_id = $POST['batch_id'];
			$auto_store_relese = $POST['auto_store_relese'];

			$qc_sample_qty = $POST['qc_qty'];

			$accept=$POST['qty_accept'];
			$reprocess=$POST['qty_reprocess'];
			$reject=$POST['qty_reject'];


			$qc_accept_qty=$POST['qty_accept'];
			$qc_reprocess_qty=$POST['qty_reprocess'];
			$qc_reject_qty=$POST['qty_reject'];
			$qc_total_qty = $POST['total_pending_qty'];

			$_SESSION['qc_work_type'] = '';
			unset($_SESSION['qc_work_type']);
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$info['qc_no']			= $POST['qc_no'];			
			$info['qc_date']		= date("Y-m-d",strtotime($POST['qc_date']));
			$info['check_tc_no']	= $POST['check_tc_no'];
			$info['qc_remark']		= $POST['qc_remark'];			
			$info['grn_id']			= $POST['grn_id'];			
			$info['grn_trn_id']		= $POST['grn_trn_id'];			
			$info['po_ref_id']		= $POST['po_ref_id'];			
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']		= $_SESSION['company_id'];
			//$info['purchase_id']	= $POST['po_id'];	
			$info['qc_godown']		= $POST['qc_godown'];	
			//$info['grn_type']		= $POST['grn_type'];
			$info['batch_id']		= $POST['batch_id'];	
			$info['product_id']		= $POST['grn_product'];	
			$info['process_id']		= $POST['current_process_id'];	
			$info['rejected_conv_new_product_id']		= $POST['new_product'];	

			$unit_id =$POST['qc_unit_id'];
			$base_unit = $POST['qc_base_unit_id'];
			$conv_unit_id =$POST['qc_conv_unit_id'];

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

				$qry12="select base_qty,conv_qty,batch_qty,IFNULL(grn_accept_qty,0) as grn_accept_qty,IFNULL(grn_reject_qty,0) as grn_reject_qty,IFNULL(grn_reprocess_qty,0) as grn_reprocess_qty from tbl_batch_data where batch_id = " . $POST['batch_id'];
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
			$info['accepted_godown']= $POST['qc_godown'];
			
			$info['rejected_base_unit']= $base_unit;
			$info['rejected_conv_unit']= $conv_unit_id;
			$info['rejected_godown']= $POST['qc_reject_godown'];
			
			$info['reprocess_base_unit']= $base_unit;
			$info['reprocess_conv_unit']= $conv_unit_id;
			$info['reprocess_godown']= $POST['qc_reporcess_godown'];

			$inserid=add_record('tbl_qc', $info, $dbcon,$branch_id);

			if($POST['qty_reject'] > 0){
				$upd_new['qc_id'] = $inserid;
				$upd_new['status'] = 0;
				$un_id=update_record('tbl_qc_reject_new_product', $upd_new,"status = 3 and batch_id=".$POST['batch_id'] , $dbcon);

				add_new_product_batch($dbcon,$inserid,$POST);
			}
			
			if(!empty($_FILES['qc_file']['tmp_name'][0])) {
				$imgresp = upload_qc_receipt($_FILES,$dbcon,$inserid);
			}
			
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id=14 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
			
		
			$update_batch_info['accept_qty'] = $accept;
			$update_batch_info['reject_qty'] = $reject;
			$update_batch_info['reprocess_qty'] = $reprocess;
			if(($res12['grn_accept_qty'] + $res12['grn_reject_qty'] + $res12['grn_reprocess_qty']) != $res12['batch_qty']){
				$update_batch_info['grn_accept_qty'] = $res12['grn_accept_qty'] + $accept;
				$update_batch_info['grn_reject_qty'] = $res12['grn_reject_qty'] + $reject;
				$update_batch_info['grn_reprocess_qty'] = $res12['grn_reprocess_qty'] + $reprocess;	
			}
			
			$update_batch_info['qc_qty'] = $POST['grn_pqty'];
			$update_batch_info['qc_status'] = 1;
			$update_batch_info['qc_sample_qty'] = $qc_sample_qty;
			

			$u_id=update_record('tbl_batch_data', $update_batch_info,"batch_id=".$POST['batch_id'] , $dbcon);

			$process_ids=explode(",",$POST['allocate_process_ids']);
			$qry_11="select ap.*,rp.reject_status from tbl_allocate_process as ap
					left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
					where p_id=".$process_ids[0];

			$set_row_11=mysqli_fetch_assoc($dbcon->query($qry_11));

			$rp_id = $set_row_11['p_ref_id'];


			if($reprocess>0){
				$qry_12="select * from tbl_wororder_product_process where rp_id=". $rp_id ." and process_priority >= (select process_priority from tbl_wororder_product_process where rp_id=". $rp_id ." and process_id = ".$POST['new_process']." and product_id = ". $POST['grn_product'].") and process_priority <= (select process_priority from tbl_wororder_product_process where rp_id=". $rp_id ." and process_id = ". $POST['current_process_id'] ." and product_id = ". $POST['grn_product'].") and product_id = ". $POST['grn_product']." order by process_priority asc";
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
			if($POST['grn_type']!="2"){
				$process_ids=explode(",",$POST['allocate_process_ids']);
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
					$pt_alloca_id=get_alloc_id($dbcon,$POST['grn_ref'],$process_id);
			
					$process=get_next_process($dbcon,$process_id,$POST['grn_product'],$set_row['p_ref_id'],$set_row['process_priority']);
					$process_pr=json_decode($process);
					
					$process_id_new=$process_pr->process_id;
					$process_type=$process_pr->process_type;
					$process_priority=$process_pr->process_priority;
					//var_dump($process_id_new);
					//die();
					//var_dump("aa");
					/* $sel="select sum(accept_qty+reject_qty) as used_qty from tbl_qc_process_trn where qc_process_status=0 and p_id=".$process_id; */
					
					$sel="select sum(accept_qty+reject_qty) as used_qty from tbl_qc_process_trn where qc_process_status=0 and p_id=".$set_row['p_id'];
					$sel_row=mysqli_fetch_assoc($dbcon->query($sel));
					//var_dump($accept);
					//var_dump($set_row['p_qty']);
					//var_dump($sel_row['used_qty']);
					$pending_qty=$set_row['p_qty']-$sel_row['used_qty'];
					
					/* $sel_job="select IFNULL(sum(IFNULL(used_qty,0)),0) as total_qty from tbl_jobwork_process where status=0 and p_id=".$set_row['p_id'];
					$row_job=mysqli_fetch_assoc($dbcon->query($sel_job)); */
					
					$sel_job="select IFNULL(sum(IFNULL(gtrn.product_qty,0)),0) as total_qty from tbl_job_work_sub_trn as jtrn 
						left join tbl_grn_sub_trn as gtrn on gtrn.job_work_sub_trn_id=jtrn.job_work_sub_trn_id
						where jtrn.job_work_sub_trn_status=0 and jtrn.p_id=".$set_row['p_id'];
					$row_job=mysqli_fetch_assoc($dbcon->query($sel_job));
					
					$sel_qc="select IFNULL(sum(IFNULL(total_qty,0)),0) as used_qty from tbl_qc_process_trn where qc_process_status=0 and p_id=".$set_row['p_id'];
					$row_qc=mysqli_fetch_assoc($dbcon->query($sel_qc));
					/* echo $sel_job;
					var_dump($row_job['total_qty']);
					echo $sel_qc;
					var_dump($row_qc['used_qty']); */
					$pending_qty=$row_job['total_qty']-$row_qc['used_qty'];
					//var_dump($accept);
					//var_dump($pending_qty);
					if($accept>"0"){
						if($accept>=$pending_qty){
							if($process_id_new!=0)
							{
								if(!empty($process_id_new)){
									//process stock add start
									if(1==2){
										$process_stock_id=production_add_process_stock($dbcon,$pending_qty,$POST['qc_unit_id'],$set_row['p_id'],$POST['qc_date'],$POST['qc_godown'],"tbl_qc",$inserid);
									
									//process stock add end
									
									$next_pid=process_allocate($dbcon,$set_row['p_id'],$process_id_new,$pending_qty,$p_ref_id1,"tbl_qc",$POST['grn_product'],$process_type,$POST['qc_unit_id'],$process_priority,$POST['grn_type'],$branch_id);
									
									//reserve process stock start
										$process_reserve_id=production_reserve_add_process_stock($dbcon,$pending_qty,$POST['qc_unit_id'],$next_pid,$process_stock_id,$POST['qc_date'],"tbl_qc",$inserid);
									//reserve process stock end
									}
								}
							}
							else
							{
								if(1==2){
								$stock_id=add_stock($dbcon,$POST['grn_product'],$POST['qc_unit_id'],$POST['qc_date'],"tbl_qc",$inserid,$POST['qc_godown'],$pending_qty,"1",$branch_id);
								
								//add_request_reserve_stock($dbcon,$p_ref_id1,$pending_qty,$POST['qc_unit_id']);
								add_request_reserve_stock_qc($dbcon,$p_ref_id1,$pending_qty,$POST['qc_unit_id'],$POST['grn_product'],$set_row['reject_status'],$branch_id,$stock_id);
								}
								$stock_status='1';
								
							}
							$accept_qty=$pending_qty;
							$accept=$accept-$pending_qty;
							//var_dump($accept);
						}else{
							if($process_id_new!=0)
							{
								if(!empty($process_id_new)){
									if(1==2){
									//process stock add start
										$process_stock_id=production_add_process_stock($dbcon,$accept,$POST['qc_unit_id'],$set_row['p_id'],$POST['qc_date'],$POST['qc_godown'],"tbl_qc",$inserid);
									//process stock add end
									
									$next_pid=process_allocate($dbcon,$set_row['p_id'],$process_id_new,$accept,$p_ref_id1,"tbl_qc",$POST['grn_product'],$process_type,$POST['qc_unit_id'],$process_priority,$POST['grn_type'],$branch_id);
									
									
									//reserve process stock start
										$process_reserve_id=production_reserve_add_process_stock($dbcon,$accept,$POST['qc_unit_id'],$next_pid,$process_stock_id,$POST['qc_date'],"tbl_qc",$inserid);
									//reserve process stock end
									}
									
								}
							}
							else
							{
								if(1==2){
								$stock_id=add_stock($dbcon,$POST['grn_product'],$POST['qc_unit_id'],$POST['qc_date'],"tbl_qc",$inserid,$POST['qc_godown'],$accept,"1",$branch_id);
								//add_request_reserve_stock($dbcon,$p_ref_id1,$accept,$POST['qc_unit_id']);
								add_request_reserve_stock_qc($dbcon,$p_ref_id1,$accept,$POST['qc_unit_id'],$POST['grn_product'],$set_row['reject_status'],$branch_id,$stock_id);
								}
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
								

								$re_pro_wr_qry = "SELECT process_type FROM tbl_wororder_product_reprocess where process_priority = 1 and qc_id = " . $inserid . " and rp_id = " . $p_ref_id1;
								$row_re_pro_wr = brp_mysqli_fetch_assoc($dbcon->query($re_pro_wr_qry));
								
								// $info7['process_id']		= $process_id;
								$info7['process_id']		= $POST['new_process'];
								$info7['pt_alloc_id']		= $set_row['p_id'];
								$info7['p_start_time']		= '';		
								$info7['p_end_time']		= '';		
								$info7['p_qty']				= $reprocess_qty;		
								$info7['pen_qty']			= $reprocess_qty;		
								$info7['p_ref_id']			= $p_ref_id1;		
								$info7['p_ref_type']		= 'process_request';		
								$info7['p_product_id']		= $POST['grn_product'];		
								$info7['pr_process_type']	= $row_re_pro_wr['process_type'];		
								$info7['pr_process_id']		= $set_row['p_id'];		
								$info7['process_priority']		= 1;
								$info7['process_unit']		= $unit_id;		
								$info7['qc_id']		= $inserid;		
								$info7['batch_id']		=$POST['batch_id'];	
								
								$info7['cdate']				= date("Y-m-d H:i:s");
								$info7['user_id']			= $_SESSION['user_id'];
								$info7['company_id']		= $_SESSION['company_id'];	

								/* 
									Sanat Start code :: 04/05/22  for costing report for reprocess
								*/
								
								$info7['product_process_rate'] = $row__1['pr_rate'];
								$info7['product_process_unit'] = $row__1['product_base_unit'];
								$info7['total_process_rate'] = $reprocess_qty * $row__1['pr_rate'];
								$pro_rate = convert_rate($dbcon,$row__1['pr_rate'],$POST['grn_product'],"conv_unit");
								$conv_reprocess_qty = convert_stock($dbcon,$reprocess_qty,$POST['grn_product'],"conv_unit");
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
								$info8['pt_product_id']	= $POST['grn_product'];			
								// $info8['pt_process_id']	= $process_id;			
								$info8['pt_process_id']	= $POST['new_process'];	
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
										
										$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$POST[grn_no]'");
										$c_mrn=mysqli_num_rows($sel_m);
										
										if($c_mrn==0)
										{
											$info2['mrn_no']			= "1";			
											$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
											$info2['grn_no']			= $POST['grn_no'];			
											$info2['qc_no']				= $inserid;	
											$info2['purchaseorder_id']	= $POST['po_id'];	
											
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
										$info3['product_id']	= $POST['grn_product'];			
										$info3['rejected_qty']	= $reject_qty;		
										
										$info3['cdate']			= date("Y-m-d H:i:s");
										$info3['user_id']		= $_SESSION['user_id'];
										$info3['company_id']	= $_SESSION['company_id'];	
										
										$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
										
										$grn_ref=$POST['grn_ref'];
										
										$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
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
								
								$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$POST[grn_no]'");
								$c_mrn=mysqli_num_rows($sel_m);
								
								if($c_mrn==0)
								{
									$info2['mrn_no']			= "1";			
									$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
									$info2['grn_no']			= $POST['grn_no'];			
									$info2['qc_no']				= $inserid;	
									$info2['purchaseorder_id']	= $POST['po_id'];	
									
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
								$info3['product_id']	= $POST['grn_product'];			
								$info3['rejected_qty']	= $reject_qty;		
								
								$info3['cdate']			= date("Y-m-d H:i:s");
								$info3['user_id']		= $_SESSION['user_id'];
								$info3['company_id']	= $_SESSION['company_id'];	
								
								$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
								
								$grn_ref=$POST['grn_ref'];
								
								$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
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


			$re_pro_wr_qry = "SELECT process_type FROM tbl_wororder_product_reprocess where process_priority = 1 and qc_id = " . $inserid . " and rp_id = " . $p_ref_id1;
								$row_re_pro_wr = brp_mysqli_fetch_assoc($dbcon->query($re_pro_wr_qry));


								
						// $info7['process_id']		= $process_id;
						$info7['process_id']		=$POST['new_process'];
						$info7['pt_alloc_id']		= $set_row['p_id'];
						$info7['p_start_time']		= '';		
						$info7['p_end_time']		= '';		
						$info7['p_qty']				= $reprocess_qty;		
						$info7['pen_qty']			= $reprocess_qty;		
						$info7['p_ref_id']			= $p_ref_id1;		
						$info7['p_ref_type']		= 'process_request';		
						$info7['p_product_id']		= $POST['grn_product'];		
						$info7['pr_process_type']	= $row_re_pro_wr['process_type'];		
						$info7['pr_process_id']		= $set_row['p_id'];	
						// Umair Start 05-03-2021
						$info7['process_unit']		= $unit_id;		
						$info7['qc_reporcess_godown']	= $POST['qc_reporcess_godown'];	
						$info7['qc_id']	= $inserid;
						$info7['batch_id']		=$POST['batch_id'];		
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
								$pro_rate = convert_rate($dbcon,$row__1['pr_rate'],$POST['grn_product'],"conv_unit");
								$conv_reprocess_qty = convert_stock($dbcon,$reprocess_qty,$POST['grn_product'],"conv_unit");
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
						$info8['pt_product_id']	= $POST['grn_product'];			
						// $info8['pt_process_id']	= $process_id;			
						$info8['pt_process_id']	= $POST['new_process'];
						$info8['pt_qty']		= $reprocess_qty;		
						// Umair Start 05-03-2021
						$info7['qc_reporcess_godown']	= $POST['qc_reporcess_godown'];	
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
								
								$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$POST[grn_no]'");
								$c_mrn=mysqli_num_rows($sel_m);
								
								if($c_mrn==0)
								{
									$info2['mrn_no']			= "1";			
									$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
									$info2['grn_no']			= $POST['grn_no'];			
									$info2['qc_no']				= $inserid;	
									$info2['purchaseorder_id']	= $POST['po_id'];
									// Umair Start 05-03-2021
									$info2['qc_reject_godown']	= $POST['qc_reject_godown'];	
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
								$info3['product_id']	= $POST['grn_product'];			
								$info3['rejected_qty']	= $reject_qty;
								// Umair Start 05-03-2021
								$info3['qc_reject_godown']	= $POST['qc_reject_godown'];	
								// Umair End 05-03-2021			
								
								$info3['cdate']			= date("Y-m-d H:i:s");
								$info3['user_id']		= $_SESSION['user_id'];
								$info3['company_id']	= $_SESSION['company_id'];	
								
								$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
								
								$grn_ref=$POST['grn_ref'];
								
								$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
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
						
						$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$POST[grn_no]'");
						$c_mrn=mysqli_num_rows($sel_m);
						
						if($c_mrn==0)
						{
							$info2['mrn_no']			= "1";			
							$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
							$info2['grn_no']			= $POST['grn_no'];			
							$info2['qc_no']				= $inserid;	
							$info2['purchaseorder_id']	= $POST['po_id'];
							// Umair Start 05-03-2021
							$info2['qc_reject_godown']	= $POST['qc_reject_godown'];	
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
						$info3['product_id']	= $POST['grn_product'];			
						$info3['rejected_qty']	= $reject_qty;	
						// Umair Start 05-03-2021
						$info3['qc_reject_godown']	= $POST['qc_reject_godown'];	
						// Umair End 05-03-2021		
						
						$info3['cdate']			= date("Y-m-d H:i:s");
						$info3['user_id']		= $_SESSION['user_id'];
						$info3['company_id']	= $_SESSION['company_id'];	
						
						$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
						
						$grn_ref=$POST['grn_ref'];
						
						$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
					
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
					$info_su['product_id']		= $POST['grn_product'];
					$info_su['p_ref_id']		= $p_ref_id1;
					
					$info_su['company_id']		= $_SESSION['company_id'];
					// Umair Start 05-03-2021
					$info_su['qc_reject_godown']	= $POST['qc_reject_godown'];	
					$info_su['grn_trn_id']	= $POST['grn_trn_id'];	
					$info_su['qc_unit'] =$POST['qc_unit_id'];	
					// Umair End 05-03-2021	
					//var_dump($info_su);

					$grn_qry = "select * from tbl_grn_sub_trn where status = 0 and grn_trn_id = " . $POST['grn_trn_id'] . " and rp_id = " . $p_ref_id1;

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
				
				if(1==2){
					if(!empty($accept)){
						$stock_id=add_stock($dbcon,$POST['grn_product'],$POST['qc_unit_id'],$POST['qc_date'],"tbl_qc",$insertid,$POST['qc_godown'],$accept,"1",$branch_id);
					}
				}
				if($reject>0){
						$reject_qty=$reject;
						$sel_m=$dbcon->query("select mrn_id,grn_no from tbl_mrn where grn_no='$POST[grn_no]'");
						$c_mrn=mysqli_num_rows($sel_m);
						
						if($c_mrn==0)
						{
							$info2['mrn_no']			= "1";			
							$info2['mrn_date']			= date("Y-m-d",strtotime($POST['qc_date']));			
							$info2['grn_no']			= $POST['grn_no'];			
							$info2['qc_no']				= $inserid;	
							$info2['purchaseorder_id']	= $POST['po_id'];	
							// Umair Start 05-03-2021
							$info2['qc_reject_godown']	= $POST['qc_reject_godown'];	
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
						$info3['product_id']	= $POST['grn_product'];			
						$info3['rejected_qty']	= $reject_qty;	
						// Umair Start 05-03-2021
						$info3['qc_reject_godown']	= $POST['qc_reject_godown'];	
						// Umair End 05-03-2021	
						
						$info3['cdate']			= date("Y-m-d H:i:s");
						$info3['user_id']		= $_SESSION['user_id'];
						$info3['company_id']	= $_SESSION['company_id'];	
						
						$inserid_mrn=add_record('tbl_mrn_trn', $info3, $dbcon,$branch_id);
						
						$grn_ref=$POST['grn_ref'];
						
						$dbcon->query("update tbl_request_product set status='1' where rp_id='$grn_ref'");
					}
				}
			
				$info1['qc_id']				= $inserid;			
				$info1['qc_product']		= $POST['grn_product'];			
				$info1['qc_product_qty']	= $POST['grn_pqty'];		
				$info1['qc_accepted'] 		= $POST['qty_accept'];	
				$info1['qc_rejected']		= $POST['qty_reject'];	
				$info1['qty_reprocess']		= $POST['qty_reprocess'];	
				$info1['stock_status']		= $stock_status;	
				$info1['po_id']				= $POST['po_id'];		
				$info1['qc_unit_id']		= $POST['qc_unit_id'];
				// Umair Start 05-03-2021	
				$info1['qc_reject_godown']		= $POST['qc_reject_godown'];	
				$info1['qc_reporcess_godown']	= $POST['qc_reporcess_godown'];		
				// Umair Start 05-03-2021

				$info1['cdate']				= date("Y-m-d H:i:s");
				$info1['user_id']			= $_SESSION['user_id'];
				$info1['company_id']		= $_SESSION['company_id'];	

				$insertid_qc=add_record('tbl_qc_trn', $info1, $dbcon,$branch_id);
				
				$set11="select * from tbl_request_product where rp_id in (".$POST['po_ref_id'].")";
				$ser=$dbcon->query($set11);
				$accept_qty_a=0;
				$reject_qty_a=0;
				while($set_row=mysqli_fetch_assoc($ser)){
					
					$query1w="select IFNULL(sum(total_qty),0) as used_qc from tbl_qc_process_trn as p
						where p.p_id=0 and qc_process_status=0 and p.p_ref_id=".$set_row['rp_id']." and company_id=".$_SESSION['company_id'];
					 
					 $rs_dispatchw=$dbcon->query($query1w);
					 $rel1w=mysqli_fetch_assoc($rs_dispatchw);
					 
					 $request_rp_po_qty=convert_stock($dbcon,$set_row['rp_po_qty'],$POST['grn_product'],"base_unit");
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
					if($accept_qty_a>"0"){
						/*var_dump($accept_qty_a);
						var_dump("</br>");
						var_dump($POST['qc_unit_id']);
						var_dump("</br>");
						var_dump($set_row['reject_status']);*/
						if(1==2){
						add_request_reserve_stock_qc($dbcon,$set_row['rp_id'],$accept_qty_a,$POST['qc_unit_id'],$POST['grn_product'],$set_row['reject_status'],$branch_id,$stock_id);
						}
						
					}
					$total_qty=$accept_qty_a+$reject_qty_a;
					//$info_su['p_id']			= $set_row['p_id'];
					$info_su['qc_id']			= $inserid;
					$info_su['product_id']		= $POST['grn_product'];
					$info_su['p_ref_id']		= $set_row['rp_id'];
					$info_su['accept_qty']		= $accept_qty_a;
					$info_su['reject_qty']		= $reject_qty_a;
					// Umair Start 05-03-2021	
					$info_su['qc_reject_godown']		= $POST['qc_reject_godown'];	
					// Umair Start 05-03-2021
					//$info_su['reprocess_qty']	= $reprocess_qty;
					$info_su['total_qty']		= $total_qty;
					$info_su['company_id']		= $_SESSION['company_id'];
					$info_su['grn_trn_id']	= $POST['grn_trn_id'];	
					$info_su['qc_unit'] =$POST['qc_unit_id'];	

					
					$grn_qry = "select * from tbl_grn_sub_trn where status = 0 and grn_trn_id = " . $POST['grn_trn_id'] . " and rp_id = " . $set_row['rp_id'];
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

			$query_11="select count(batch_id) as total_qc from tbl_batch_data where grn_trn_id=".$POST['grn_trn_id']." and qc_status = 0 and status = 0";
			$rel_11=brp_mysqli_fetch_assoc($dbcon->query($query_11));

			if($rel_11['total_qc']=="0"){
				// echo "update : " . $POST['grn_trn_id'];
				$dbcon->query("update tbl_grn_trn set product_qc='1' where grn_trn_id='$POST[grn_trn_id]'");
			}
			
			if($inserid)
			{
				if($auto_store_relese == '1'){
					$bt_upd_info['stock_approval_status'] = '1';
					$bt_upd_info['auto_store_relese'] = '1';
					$updateid=update_record('tbl_batch_data', $bt_upd_info,"batch_id=".$batch_id, $dbcon);
					auto_store_approval_entry($dbcon,$batch_id);
				}
				$resp['msg'] = "1";
			}
			else
			{
				$resp['msg'] = "0";
			}
			$resp['back'] = $POST['back'];
			echo json_encode($resp); 
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_qc_param` WHERE `p_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			
				$info['qc_no']	= $POST['qc_no'];			
				$info['qc_date']	= date("Y-m-d",strtotime($POST['qc_date']));			
				$info['qc_grn']	= $POST['grn_no'];			
				$info['qc_remark']	= $POST['qc_remark'];			
				
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('tbl_qc', $info,"qc_id=".$POST['eid'] , $dbcon);
				
				$dbcon->query("delete from tbl_qc_trn where qc_id='$POST[eid]'");
				
				foreach($POST['grn_product'] as $row=>$name)
				{
					$info1['qc_id']	= $POST['eid'];			
					$info1['qc_product']	= $POST['grn_product'];			
					$info1['qc_product_qty']	= $POST['grn_pqty'];		
					$info1['qc_accepted'] = $POST['qty_accept'];	
					$info1['qc_rejected']	=$POST['qty_reject'];	
					$info1['qty_reprocess']	=$POST['qty_reprocess'];	

					$info1['cdate']		= date("Y-m-d H:i:s");
					$info1['user_id']	= $_SESSION['user_id'];
					$info1['company_id']	= $_SESSION['company_id'];	

					add_record('tbl_qc_trn', $info1, $dbcon);					
				}
				
				if($updateid)
					$resp['msg'] = "2";
				else
					$resp['msg'] = "0";
				
				echo json_encode($resp);
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				
				$info['qc_status']  = 2;
				$update=update_record('tbl_qc', $info,"qc_id=".$POST['eid'], $dbcon);	
							
				if($update)
					echo "1";	
				else
					echo "0";		
			
		}
		else if(strtolower($POST['mode']) == "get_grn_product") {
			
			//$grn_id=$POST['grn_id'];
			$eid=$POST['eid'];
			
			$str="";
			
			$str="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th style='width:5%;' >#</th>
				<th style='width:27%;'>Product Name</th>
				<th style='width:8%;'>Unit</th>
				<th style='width:15%;'>Total Qty</th>
				<th style='width:15%;'>Accepted Qty</th>
				<th style='width:15%;'>Rejected Qty</th>
				<th style='width:15%;'>Reprocess Qty</th>
				
			</tr>";
			
			$cnt=1;
			$sel=$dbcon->query("select gt.product_id, gt.purchaseorder_id,p.product_type,p.product_name,g.ref_type,g.ref_no,g.grn_no,gt.product_qty,g.grn_date,g.qc_status,g.grn_status,g.ref_type,g.product_qc,gt.grn_trn_id,gt.grn_id,gt.unit_id,umst.unit_name,gt.po_ref_id from tbl_grn_trn as gt 
			left join unit_mst as umst on umst.unitid=gt.unit_id
			left join tbl_grn as g on g.grn_id=gt.grn_id 
			left join product_mst as p on p.product_id=gt.product_id 
			where gt.grn_trn_id='$eid'");
			$row=mysqli_fetch_assoc($sel);
			
			
				$str.="<tr>
					
					<th>".$cnt."</th>
					<th>".$row['product_name']."
						<input type='hidden' class='form-control' name='grn_product' id='grn_product' value='".$row['product_id']."' />
						<input type='hidden' class='form-control' name='grn_type' id='grn_type' value='".$row['ref_type']."' />
						<input type='hidden' class='form-control' name='grn_ref' id='grn_ref' value='".$row['ref_no']."' />
						<input type='hidden' class='form-control' name='j_reprocess' id='j_reprocess' value='".$row['j_reprocess']."' />
						
					</th>
					<th>".$row['unit_name']."
						<input type='hidden' class='form-control' name='qc_unit_id' id='qc_unit_id' value='".$row['unit_id']."' />
					</th>
					<th>".$row['product_qty']."
						<input type='hidden' class='form-control' name='grn_pqty' id='grn_pqty' value='".$row['product_qty']."' />
					</th>
					<th>
						<input type='text' class='form-control' name='qty_accept' id='qty_accept' value='".$accept."' onkeyup='sub_accept_value()' />
						<input type='hidden' class='form-control' name='' id='qty_accept_hid' value='".$accept."' />
						<strong id='qty_error' style='color:red'></strong>
					</th>
					<th>
						<input type='text' class='form-control' name='qty_reject' id='qty_reject' value='".$reject."' onkeyup='sub_accept_value()'  />
						<input type='hidden' class='form-control' name='' id='qty_reject_hid' value='' />
						<strong id='qty_error_reject' style='color:red'></strong>
					</th>
					<th>
						<input type='text' class='form-control' name='qty_reprocess' id='qty_reprocess' value='".$reprocess."' onkeyup='sub_accept_value()'  />
						<input type='hidden' class='form-control' name='' id='qty_reprocess_hid' value='' />
						<strong id='qty_error_reprocess' style='color:red'></strong>
						
						<input type='text' name='po_id' id='po_id' value='".$row['purchaseorder_id']."' />
						<input type='hidden'  name='grn_no' id='grn_no' value='".$row['grn_id']."' />
						<input type='hidden'  name='po_ref_id' id='po_ref_id' value='".$row['po_ref_id']."' />
					</th>
					
					
						
				</tr>";
				
				
			echo $str;
		}
		
		else if(strtolower($POST['mode']) == "get_po_no") {
			
			$grn_id=$POST['grn_id'];
			$sel=$dbcon->query("select purchaseorder_id from tbl_grn where grn_id='$grn_id'");
			$row=mysqli_fetch_assoc($sel);
			
			echo $row['purchaseorder_id'];
		}
		else if(strtolower($POST['mode']) == "show_qc_param_details") {
			
			
			$eid=$POST['eid'];
			
			$s1=$dbcon->query("select product_id from tbl_grn_trn where grn_trn_id='$eid'");
			$r1=mysqli_fetch_array($s1);
			
			$pid=$r1['product_id'];
			
			$str="";
			
			$str.="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th>#</th>
				<th>Parameter Name</th>
				<th>Actual Value</th>
				<th>Testing Value</th>
			
			</tr>";
			
			$cnt=1;
			$sel=$dbcon->query("select mst.*,p.p_name from tbl_product_parameter as mst left join tbl_qc_param as p on p.p_id=mst.param_id where mst.product_id='$pid'");
			while($row=mysqli_fetch_assoc($sel))
			{
				
				$str.="<tr>
					
					<th>".$cnt."</th>
					
					<th>".$row['p_name']."
						<input type='hidden' class='form-control qc_pname' name='qc_pname[]' id='tested_value".$cnt."' value='".$row['param_id']."' />
					</th>
					
					<th>".$row['param_value']."
						<input type='hidden' class='form-control qc_param_value' name='qc_param_value[]' id='tested_value".$cnt."' value='".$row['param_value']."' />
					</th>
					
					<th><input type='text' class='form-control tested_value' name='tested_value[]' id='tested_value".$cnt."'  /></th>
				
				</tr>";
				
				$cnt++;
			}
			
			$total_param=$cnt-1;
			$str.="<tr>
				
				<td colspan='4' align='center'>
				
				<input type='hidden' name='total_param' value='".$total_param."' />
				
				</td>
			
			</tr>";
			
			
			echo $str;
			
		}
		else if(strtolower($POST['mode']) == "add_param") {
			
			$resp['msg'] = "1";
			
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "add_qc_param_data") {
			
			$qc_pname=$POST['qc_pname'];
			$qc_param_value=$POST['qc_param_value'];
			$tested_value=$POST['tested_value'];
			$form_mode=$POST['form_mode'];
			$pid=$POST['pid'];
			$eid=$POST['eid'];
			$grn_no=$POST['grn_no'];
			
			for($i=0;$i<count($qc_pname);$i++)
			{
				$q=$dbcon->query("select qc_pr_tested from tbl_qc_param_trn where qcpt_grn_id='$grn_no' and qc_param='$qc_pname[$i]' and qc_product='$pid' and qcpt_qc_id='$eid'");
				$count=mysqli_num_rows($q);
				
				$info['qc_param']=$qc_pname[$i];
				$info['qc_pr_actual']=$qc_param_value[$i];
				$info['qc_pr_tested']=$tested_value[$i];
				$info['qc_product']=$pid;
				$info['qcpt_qc_id']=$eid;
				$info['qcpt_grn_id']=$grn_no;
				
				$info['user_id']=$_SESSION['user_id'];
				$info['cdate']=date("Y-m-d h:i:s");
				$info['company_id']=$_SESSION['company_id'];
				
				$table='tbl_qc_param_trn';$tableid='qcpt_id';
				
				if($count>0)
				{
					$updateid=update_record($table, $info,"qcpt_grn_id='$grn_no' and qc_param='$qc_pname[$i]' and qc_product='$pid' and qcpt_qc_id='$eid'", $dbcon);	
				}else{
					
					$inserid=add_record($table, $info, $dbcon);
				}
			}
			//print_r($bid);
			
		}
		else if(strtolower($POST['mode']) == "show_mrn_details") {
			
			$qid=$POST['qid'];
			
			$str="";
			
			$sel_m=$dbcon->query("select * from tbl_mrn where qc_no='$qid'");
			$r_m=mysqli_fetch_assoc($sel_m);
			
			
			$str.="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th>MRN No</th>
				<td>".$r_m['mrn_no']."</td>
				<th>Date</th>
				<td>".date("d/m/Y",strtotime($r_m['mrn_date']))."</td>
			</tr>";
			
			$str.="</table>";
			
			$str.="<table class='table table-bordered table_stripped'>
			
			<tr>
			
				<th>#</th>
				<th>Product </th>
				<td>Qty</td>
	
			</tr>";
			
			$cnt=1;
			$sel=$dbcon->query("select mst.*,p.product_name from tbl_mrn_trn as mst inner join product_mst as p on p.product_id=mst.product_id where mst.mrn_no='$r_m[mrn_id]'");
			while($row=mysqli_fetch_assoc($sel))
			{
				
				$str.="<tr>
					
					<th>".$cnt."</th>
					<th>".$row['product_name']."</th>
					<th>".$row['rejected_qty']."</th>
					
				</tr>";
				
				$cnt++;
			}
			
			echo $str;
		}
		else if(strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=14 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		else if(strtolower($POST['mode'])== "load_invoiceno")
		{
			$row=array();
			$query1="select * from  tbl_invoicetype where invoicetype_id=".$_POST['typeid'];
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
		else if(strtolower($POST['mode'])== "get_each_qty_qc_param")
		{
			$qc_work_type = $_POST['qc_work_type'];
			$total_pending_qty = $POST['total_pending_qty'];
			$grn_product = $POST['grn_product'];
			$grn_trn_id = $POST['grn_trn_id']; 
			$process_show = $POST['process_show']; 
			$dper = ''; $isdisabled = '';
			if($process_show!="1") { 
			 	//$dper="display:none";
			 	$dper="hide";
			 	$isdisabled = "disabled";
			}

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];


			$_SESSION['qc_work_type'] = $qc_work_type;

			if($qc_work_type=='1'){
				$item_count = (int)$total_pending_qty;
			}else{
				$item_count = 1;
			}
			
			$query="select pp.*,qp.p_name,pm.product_name,pm.image_name from tbl_product_parameter as pp 
					left join tbl_qc_param as qp on qp.p_id=pp.param_id 
					left join product_mst as pm on pm.product_id=pp.product_id 
					where pp.product_id=".$grn_product." and process_id=".$POST['current_process_id']." and pp.company_id=".$_SESSION['company_id'];
					
			$result=$dbcon->query($query);
			$count_data = brp_mysqli_num_rows($result);

			$str = '';

			if($count_data > 0){
				$calculate_qty_status = calculate_qty_status_wise($dbcon, $grn_product, $grn_trn_id, $qc_work_type);
				$pro_exec=$dbcon->query($query);
				$row_info=brp_mysqli_fetch_assoc($pro_exec);

				$str .= '<!--<div class="col-md-12 margin_row">
							<div class="col-md-1 product_label_name"><b>Product Name :</b></div>
							<div class="col-md-11 product_label_name">'.$row_info['product_name'].' 
								<a href="javascript:void(0)" class="view_toggle_image" style="margin-left:40px">View Product Image</a>
							</div>
						</div>
						<div class="col-md-12" style="margin-bottom:30px;">
						<div class="image_div" style="display:none;">
							  <a href="../view/upload/product_images/'.$row_info['image_name'].'" target="_blank"><img src="../view/upload/product_images/'.$row_info['image_name'].'" class="img-thumbnail" width="280" height="280"></a>
							</div>
						</div>-->

						 <div class="col-md-12 margin_row" >

							<!-- <input type="hidden" name="mode" value="qc_item_record" />	-->

							<!-- <input type="hidden" name="mode" value="qc_item_record" />	 -->

							   <table class="table table-bordered">
							      <tr>
							         <th>Accepted Qty</th>
							         <th>Godown</th>
							         <th>Rejected Qty</th>
							         <th>Godown</th>
							         <th class="'.$dper.'">Reprocess Qty</th>
							         <th class="'.$dper.'">Godown</th>
							      </tr>
							      <tr>
							         <td>
							            <input type="text" class="form-control" name="item_accepted_qty" value="'.$calculate_qty_status['accepted'].'" id="item_accepted_qty" readonly/>
							         </td>
							         <td>
							            <select class="form-control" name="item_qc_accepted_godown" id="item_qc_accepted_godown" required >
											'.get_all_godown($dbcon,'').'
										</select>
							         </td>
							         <td>
							            <input type="text" class="form-control" name="item_rejected_qty" value="'.$calculate_qty_status['rejected'].'" id="item_rejected_qty" readonly/>
							         </td>
							         <td>
							            <select class="form-control" name="item_qc_rejected_godown" id="item_qc_rejected_godown" required >
											'.get_all_godown($dbcon,'').'
										</select>
							         </td>
							         <td class="'.$dper.'">
							            <input type="text" class="form-control '.$dper.'" name="item_reprocess_qty" value="'.$calculate_qty_status['reprocessed'].'" id="item_reprocess_qty" readonly/>
							         </td>
							        <td class="'.$dper.'">
							            <select class="form-control '.$dper.'" name="item_qc_reprocess_godown" id="item_qc_reprocess_godown" required >
											'.get_all_godown($dbcon,'').'
										</select>
							         </td>
							         
							      </tr>
							   </table>
							   <div id="table_product_parameter"></div>
							</div>';
							if($count_data<"4"){
								$colspan=$count_data;
							}else{
								$colspan="4";
							}
				$str .= '<div class="col-md-12 margin_row">
						<table style="border-spacing:10px;" class="display table table-bordered table-striped" cellspacing="10">
						   <tbody>
						   <tr id="field">
							<th class="text-center" width="5%">Item No</th>
							<th class="text-center" width="75%">QC Parameter</th>
							<th class="text-center" width="10%">Status</th>
							<th class="text-center" width="10%"></th>
						</tr>';
				for($k=1; $k<=$item_count;$k++){
					$str .= '<tr id="item_'.$k.'"><td class="text-center" width="5%">'.$k.'</td>
					<td class="text-center" width="75%">
									<table style="border-spacing:10px;" class="display table table-bordered table-striped" cellspacing="10">';
					$result_row=$dbcon->query($query);

					$m=1;
					while($row1=brp_mysqli_fetch_assoc($result_row)){
						$param_name = generateClassName($row1['p_name']);
						$class_label = generateClassName($row1['p_name']);
						$pr_param_id = $row1['pr_param_id'];
						$product_id = $row1['product_id'];
						$param_val = $row1['param_value'];
						
						$min_attr = '';
						if(is_numeric($param_val)){
							$input_type = 'number';
							$min_attr = 'min=0';
						}else{
							$input_type = 'text';
						}
						// Get Particular Parameter's Entered Data
						$item_value = get_qc_item_info($dbcon, $grn_product, $grn_trn_id, $pr_param_id, $qc_work_type, $branch_id, $k);
						$field_value = $item_value['field_value'];
						$item_qc_id = $item_value['item_qc_id'];
						if($m=="1"){
							$str .= '<tr>';
						}
						$str .= '
								<td class="text-center" width="15%">
									<strong>'.$row1['p_name'].' ('.$row1["param_value"].')</strong> </br>
									<input type="'.$input_type.'" '.$min_attr.' class="form-control  claculate_status '.$class_label.'" data-product_id="'.$product_id.'" data-pr_param_id="'.$pr_param_id.'" data-item_qc_id="'.$item_qc_id.'" data-field_key="'.$param_name.'" data-item_id="'.$k.'" data-param_value = "'.$row1['param_value'].'" name="'.$param_name.'[]" value="'.$field_value.'" required />
								</td>';
						
						if($m=="4"){		
							$str .= '</tr>';
							$m=0;
						}
						$m++;	
					}

						//if($m==$count_data){
							$check_item_status_info = get_qc_item_status($dbcon, $grn_product, $grn_trn_id, $k, $qc_work_type, $branch_id);

							$status=''; $sel=''; $sel1=''; $hide = 'hide'; $item_status_id='';
							if(!empty($check_item_status_info)){
								$item_status_id = $check_item_status_info['item_status_id'];
								if($check_item_status_info['status']=='1'){
									$status='<span class="label label-success">QC Pass</span>';
								}else{
									$hide = '';
									$status='<span class="label label-danger">QC Fail</span>';
									if($check_item_status_info['status']=='2'){
										$sel='selected="selected"';
									}if($check_item_status_info['status']=='3'){
										$sel1='selected="selected"';
									}
								}
							}

							$str .= '</table>
							</td>
							<td class="text-center" width="10%" id="item_status_'.$k.'">'.$status.'</td>
							<td class="text-center" width="10%" id="item_recheck_'.$k.'">
									<select class="form-control reject_reprocess_qty '.$hide.' item_status_'.$k.'" name="status" data-status_id="'.$item_status_id.'" id="status_'.$k.'" required onChange="calculate_reject_reprocess('.$k.')">
										<option value="2" '.$sel.'> Reject </option>
										<option value="3" '.$sel1.' '.$isdisabled.'> Reprocess </option>
									</select>
							</td>
							</tr>';
						//}
				}						  	 
				$str .= '</tbody></table>
						</div>
						<!--<div class="col-md-12 calc_row" style="display:none;">
								<center>
								<button type="submit" class="btn btn-success" id="caluclate_qty" name="caluclate_qty">Calculate Qty</button>
								<a href="" type="button" class="btn btn-danger">Cancel</a>
								</center>
							</div>-->';	
			}					
			echo $str;			   
		}

		else if(brp_strtolower($POST['mode'])== "insert_item_data")
		{
			$item_qc_id = $POST['item_qc_id'];
			
			$info['product_id'] = $POST['product_id']; 
			$info['grn_trn_id'] = $POST['grn_trn_id']; 
			$info['item_number'] = $POST['item_id']; 
			$info['qc_work_type'] = $POST['qc_work_type']; 
			$info['field_key'] = $POST['field_key']; 
			$info['field_value'] = trim(strtolower($POST['entered_value'])); 
			$info['pr_param_id'] = $POST['pr_param_id']; 
			$info['user_id'] = $_SESSION['user_id']; 
			$info['cdate'] = date('Y-m-d H:i:s'); 
			$info['company_id'] = $_SESSION['company_id']; 
			$info['branch_id'] = $POST['branch_id']; 
			$info['batch_id'] = $POST['batch_id']; 
			$info['sr_no'] = $POST['sr_no']; 

			if($item_qc_id==''){
				$insertid=add_record('tbl_item_wise_qc', $info, $dbcon);
				$return_id = $insertid;
			}else{
				update_record('tbl_item_wise_qc', $info, "item_qc_id='$item_qc_id'", $dbcon);
				$return_id = $item_qc_id;	
			}
			echo $return_id;
		}
		else if(brp_strtolower($POST['mode'])== "get_item_status")
		{
			$product_id = $POST['product_id'];
			$pr_param_id = $POST['pr_param_id'];
			$grn_trn_id = $POST['grn_trn_id'];
			$item_id = $POST['item_id'];
			$item_qc_id = $POST['item_qc_id'];
			$qc_work_type = $POST['qc_work_type'];
			$branch_id = $POST['branch_id'];
			$main_qty = $POST['grn_pqty'];

			$product_para_sql="select pp.*,qp.p_name from tbl_product_parameter as pp 
						left join tbl_qc_param as qp on qp.p_id=pp.param_id 
						where pp.product_id=".$product_id." and pp.process_id=".$POST['current_process_id']." and pp.company_id=".$_SESSION['company_id'];
			$product_result= $dbcon->query($product_para_sql);
			$product_count = brp_mysqli_num_rows($product_result);

			$qc_item_sq = "select *  from `tbl_item_wise_qc` WHERE `product_id`= ".$product_id." and item_number=".$item_id." and qc_work_type=".$qc_work_type." and grn_trn_id=".$grn_trn_id." and company_id=".$_SESSION['company_id'];
			$qc_result=$dbcon->query($qc_item_sq);
			$qc_count = brp_mysqli_num_rows($qc_result);

			if($product_count==$qc_count){
				$status_flag = '1';

				while($row=brp_mysqli_fetch_assoc($product_result)){
					$item_param_value = trim($row['param_value']);

					$qc_item_info = get_qc_item_info($dbcon, $product_id, $grn_trn_id, $row['pr_param_id'], $qc_work_type, $branch_id, $item_id);

					if(is_numeric($item_param_value)){
						$item_plus_tole = trim($row['tolerance_plus']);
						$item_minus_tole = trim($row['tolerance_minus']);
						
						$max_tole = ($item_param_value*$item_plus_tole)/100;
						$max_value = (double)($item_param_value + $max_tole);
						//var_dump($max_value);

						$min_tole = ($item_param_value*$item_minus_tole)/100;
						$min_value = (double)($item_param_value - $max_tole);
						
						//var_dump($min_value);
						$item_qc_value = (double)(trim($qc_item_info['field_value']));
						
						//var_dump($item_qc_value);
						if($item_qc_value < $min_value || $item_qc_value > $max_value){
							$status_flag = '0';
							break;
						}

					}else{
						$item_param_value = brp_strtolower($item_param_value);
						$item_qc_value = trim_lowecase($qc_item_info['field_value']);
						if($item_param_value!=$item_qc_value){
							$status_flag = '0';
							break;
						}
					}
				}

				$delete_sql = "delete from tbl_item_wise_status where product_id=$product_id and grn_trn_id=$grn_trn_id and item_number=$item_id and qc_work_type=$qc_work_type and company_id=".$_SESSION['company_id'];
				$dbcon->query($delete_sql);

				$statusinfo['product_id'] = $product_id;
				$statusinfo['grn_trn_id'] = $grn_trn_id;
				$statusinfo['item_number'] = $item_id;
				$statusinfo['qc_work_type'] = $qc_work_type;
				if($status_flag=='1'){
					$statusinfo['status'] = 1;
					if($statusinfo['qc_work_type']=='1'){
						$statusinfo['accepted_qty'] = 1;
					}else{
						$statusinfo['accepted_qty'] = $main_qty;
					}
					
					$statusinfo['rejected_qty'] = 0;
					$statusinfo['reprocess_qty'] = 0;
				}else{
					$statusinfo['status'] = 0;
					$statusinfo['accepted_qty'] = 0;
					$statusinfo['rejected_qty'] = 0;
					$statusinfo['reprocess_qty'] = 0;				
				}
				
				$statusinfo['user_id'] = $_SESSION['user_id'];
				$statusinfo['cdate'] = date('Y-m-d H:i:s');
				$statusinfo['company_id'] = $_SESSION['company_id'];
				$statusinfo['branch_id'] = $branch_id;

				$item_status_id=add_record('tbl_item_wise_status', $statusinfo, $dbcon);

				$calculate_qty_status = calculate_qty_status_wise($dbcon, $product_id, $grn_trn_id, $qc_work_type);

				$arr = array('msg' => 1,'status' => $status_flag, 'item_status_id' => $item_status_id );
				$final_array = array_merge($arr, $calculate_qty_status);
				
			}else{
				$final_array = array('msg' => 0);
			}

			echo json_encode($final_array);
		}

		else if(brp_strtolower($POST['mode'])== "update_item_status")
		{
			$qc_work_type = $POST['qc_work_type'];
			$main_qty = $POST['grn_pqty'];
			$product_id = $POST['grn_product'];
			$grn_trn_id = $POST['grn_trn_id'];


			$info['status'] = $POST['status_type'];

			if($info['status']=='2'){
				if($qc_work_type=='1'){
					$info['rejected_qty'] = 1;
				}else{
					$info['rejected_qty'] = $main_qty;
				}
				$info['reprocess_qty'] = 0;
			}else{
				$info['rejected_qty'] = 0;

				if($qc_work_type=='1'){
					$info['reprocess_qty'] = 1;
				}else{
					$info['reprocess_qty'] = $main_qty;
				}
			}
			$status_id = $POST['status_id'];

			$updateid=update_record('tbl_item_wise_status', $info,"item_status_id='$status_id' ", $dbcon);

			$calculate_qty_status = calculate_qty_status_wise($dbcon, $product_id, $grn_trn_id, $qc_work_type);
			echo json_encode($calculate_qty_status);
		}

		else if(brp_strtolower($POST['mode'])== "qc_item_record"){

			// echo "<pre>";print_r($POST);die();

			//echo "<pre>";print_r($POST);die();

		}

		else if(strtolower($POST['mode']) == "load_qc_perameter") {

				$qc_item_sq = "select pra.*,qpra.p_name  from `tbl_product_parameter` as pra
					left join tbl_qc_param as qpra on qpra.p_id=pra.param_id
				 WHERE pra.process_id=".$POST['process_id']." and pra.product_id=".$POST['product_id']." and pra.company_id=".$_SESSION['company_id'];
				$qc_result=$dbcon->query($qc_item_sq);
				$qc_count = brp_mysqli_num_rows($qc_result);
$html="";
				if($qc_count>0){
						$html .='<table style="border-spacing:10px;" class="display table table-bordered table-striped" cellspacing="10">
						 <tr id="field">
								<th class="text-center" width="5%">Item No</th>
								<th class="text-center" width="75%">QC Parameter</th>
									<th class="text-center" width="75%">Status</th>
							</tr>';
							$i=1;
						while($row=brp_mysqli_fetch_assoc($qc_result)){
								$html .='
								 <tr id="field">
										<th class="text-center" width="5%">'.$i.'
										</th>
										<th class="text-center" width="75%">'.$row["p_name"].'</th>
											<th class="text-center" width="75%">Status</th>
									</tr>';
									$i++;
						}
						$html .='</table>';
				}
				//echo $html;
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

else if(strtolower($POST['mode'])== "get_each_qty_qc_param_new")
		{
			$qc_work_type = $_POST['qc_work_type'];
			$total_pending_qty = $POST['total_pending_qty'];
			$grn_product = $POST['grn_product'];
			$grn_trn_id = $POST['grn_trn_id']; 
			$process_show = $POST['process_show']; 
			$dper = ''; $isdisabled = '';

			$qc_qty = $POST['qc_qty'];
			if($process_show!="1") { 
			 	//$dper="display:none";
			 	$dper="hide";
			 	$isdisabled = "disabled";
			}

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];


			$_SESSION['qc_work_type'] = $qc_work_type;

			if($qc_work_type=='1'){
				$item_count = (int)$total_pending_qty;
			}else{
				$item_count = 1;
			}
			
			$query="select pp.*,qp.p_name,pm.product_name,pm.image_name from tbl_product_parameter as pp 
					left join tbl_qc_param as qp on qp.p_id=pp.param_id 
					left join product_mst as pm on pm.product_id=pp.product_id 
					where pp.product_id=".$grn_product." and process_id=".$POST['current_process_id']." and pp.company_id=".$_SESSION['company_id'];
					
			$result=$dbcon->query($query);
			$count_data = brp_mysqli_num_rows($result);
			$row_param=brp_mysqli_fetch_assoc($result);

			$str = '';

			if($count_data > 0){
				$calculate_qty_status = calculate_qty_status_wise($dbcon, $grn_product, $grn_trn_id, $qc_work_type);
				$pro_exec=$dbcon->query($query);
				$row_info=brp_mysqli_fetch_assoc($pro_exec);

				$str .= '<!--<div class="col-md-12 margin_row">
							<div class="col-md-1 product_label_name"><b>Product Name :</b></div>
							<div class="col-md-11 product_label_name">'.$row_info['product_name'].' 
								<a href="javascript:void(0)" class="view_toggle_image" style="margin-left:40px">View Product Image</a>
							</div>
						</div>
						<div class="col-md-12" style="margin-bottom:30px;">
						<div class="image_div" style="display:none;">
							  <a href="../view/upload/product_images/'.$row_info['image_name'].'" target="_blank"><img src="../view/upload/product_images/'.$row_info['image_name'].'" class="img-thumbnail" width="280" height="280"></a>
							</div>
						</div>-->

						 <div class="col-md-12 margin_row" >
							<!-- <input type="hidden" name="mode" value="qc_item_record" />	-->
							   <table class="table table-bordered">
							      <tr>
							         <th>Accepted Qty</th>
							         <th>Godown</th>
							         <th>Rejected Qty</th>
							         <th>Godown</th>
							         <th class="'.$dper.'">Reprocess Qty</th>
							         <th class="'.$dper.'">Godown</th>
							      </tr>
							      <tr>
							         <td>
							            <input type="text" class="form-control" name="item_accepted_qty" value="'.$calculate_qty_status['accepted'].'" id="item_accepted_qty" readonly/>
							         </td>
							         <td>
							            <select class="form-control" name="item_qc_accepted_godown" id="item_qc_accepted_godown">
											'.get_all_godown($dbcon,'').'
										</select>
							         </td>
							         <td>
							            <input type="text" class="form-control" name="item_rejected_qty" value="'.$calculate_qty_status['rejected'].'" id="item_rejected_qty" readonly/>
							         </td>
							         <td>
							            <select class="form-control" name="item_qc_rejected_godown" id="item_qc_rejected_godown">
											'.get_all_godown($dbcon,'').'
										</select>
							         </td>
							         <td class="'.$dper.'">
							            <input type="text" class="form-control '.$dper.'" name="item_reprocess_qty" value="'.$calculate_qty_status['reprocessed'].'" id="item_reprocess_qty" readonly/>
							         </td>
							        <td class="'.$dper.'">
							            <select class="form-control '.$dper.'" name="item_qc_reprocess_godown" id="item_qc_reprocess_godown">
											'.get_all_godown($dbcon,'').'
										</select>
							         </td>
							         
							      </tr>
							   </table>
							   <div id="table_product_parameter"></div>
							</div>';
							if($count_data<"4"){
								$colspan=$count_data;
							}else{
								$colspan="4";
							}
				$str .= '<div class="col-md-12 margin_row">
						<table style="border-spacing:10px;" class="display table table-bordered table-striped" cellspacing="10">
						   <tbody>
						   <tr id="field">
							<th class="text-center" width="5%">Item No</th>
							<th class="text-center" width="65%">QC Parameter</th>
						<th class="text-center" width="10%">Status</th>
							<th class="text-center" width="10%"></th>
						</tr>';

				for($x=1; $x<=$qc_qty;$x++){
				for($k=1; $k<=$item_count;$k++){
					$str .= '<tr id="item_'.$x.'"><td class="text-center" width="5%">'.$x.'</td>
					<td class="text-center" width="65%">
									<table style="border-spacing:10px;" class="display table table-bordered table-striped" cellspacing="10">';
					$result_row=$dbcon->query($query);

					$m=1;
					while($row1=brp_mysqli_fetch_assoc($result_row)){
						
						$param_name = generateClassName($row1['p_name']);
						$class_label = generateClassName($row1['p_name']);
						$pr_param_id = $row1['pr_param_id'];
						$product_id = $row1['product_id'];
						$param_val = $row1['param_value'];
						
						$min_attr = '';
						if(is_numeric($param_val)){
							$input_type = 'number';
							$min_attr = 'min=0';
						}else{
							$input_type = 'text';
						}
						// Get Particular Parameter's Entered Data
						$item_value = get_qc_item_info($dbcon, $grn_product, $grn_trn_id, $pr_param_id, $qc_work_type, $branch_id, $k);
						$field_value = $item_value['field_value'];
						$item_qc_id = $item_value['item_qc_id'];
						if($m=="1"){
							$str .= '<tr>';
						}
						$str .= '
								<td class="text-center" width="15%">
									<strong>'.$row1['p_name'].' ('.$row1["param_value"].') | Tolerance Plus ('.$row1['tolerance_plus'].') | Tolerance Minus ('.$row1['tolerance_minus'].')</strong> </br>
									<input type="'.$input_type.'" '.$min_attr.' class="form-control calculate_qc_status claculate_status_'.$x.' '.$class_label.'" data-product_id="'.$product_id.'" data-pr_param_id="'.$pr_param_id.'" data-item_qc_id="'.$item_qc_id.'" data-field_key="'.$param_name.'" data-item_id="'.$x.'" data-status_id="'.$x.'" data-param_value = "'.$row1['param_value'].'" data-tolerance_minus = "'.$row1['tolerance_minus'].'" data-tolerance_plus = "'.$row1['tolerance_plus'].'" name="'.$param_name.'[]" value="" required />
								</td>';
						
						if($m=="4"){		
							$str .= '</tr>';
							$m=0;
						}
						$m++;	
					}

						//if($m==$count_data){
							// $check_item_status_info = get_qc_item_status($dbcon, $grn_product, $grn_trn_id, $k, $qc_work_type, $branch_id);
					$check_item_status_info = "";

							$status=''; $sel=''; $sel1=''; $hide = 'hide'; $item_status_id='';
							if(!empty($check_item_status_info)){
								$item_status_id = $check_item_status_info['item_status_id'];
								if($check_item_status_info['status']=='1'){
									$status='<span class="label label-success">QC Pass</span>';
								}else{
									$hide = '';
									$status='<span class="label label-danger">QC Fail</span>';
									if($check_item_status_info['status']=='2'){
										$sel='selected="selected"';
									}if($check_item_status_info['status']=='3'){
										$sel1='selected="selected"';
									}
								}
							}

							$str .= '</table>
							</td>
							<td class="text-center" width="10%" id="item_status_'.$x.'">'.$status.'</td>
							<td class="text-center" width="10%" id="item_recheck_'.$k.'">
									<select class="form-control reject_reprocess_qty '.$hide.'" name="status" data-status_id="'.$x.'" id="status_'.$x.'" onChange="calculate_reject_reprocess('.$x.')" required>
										
										<option value="2" '.$sel.'> Reject </option>
										<option value="3" '.$sel1.' '.$isdisabled.'> Reprocess </option>
									</select>
							</td>
							</tr>';
						//}
				}			
				}			  	 
				$str .= '</tbody></table>
						</div>
						<!--<div class="col-md-12 calc_row" style="display:none;">
								<center>
								<button type="submit" class="btn btn-success" id="caluclate_qty" name="caluclate_qty">Calculate Qty</button>
								<a href="" type="button" class="btn btn-danger">Cancel</a>
								</center>
							</div>-->';	
			}					
			echo $str;			   
		}else if(strtolower($POST['mode']) == "load_new_product_tempoutward") {
				
			$query="select trn.*,pro.product_name,uns.unit_name from tbl_qc_reject_new_product as trn
					left join product_mst as pro on pro.product_id=trn.product_id
					left join unit_mst as uns on uns.unitid=trn.unit_id
					where trn.status=3";
				
			//echo $query;
			$result=$dbcon->query($query);
			echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
				<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
					<tr id="field">
						<th class="text-center" width="10%">Product Name</th>
						<th class="text-center"width="15%">Qty</th>
						<th class="text-center"width="15%">Unit</th>
						<th class="text-center"width="10%">Action</th>
					</tr>';

			//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$i=1;$total=0;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					
					echo '<tr id="fieldtr'.$i.'">
					<td style="vertical-align:top;" class="text-left">
					'.$rel['product_name'].'
					</td>';				
					
					echo '<td style="vertical-align:top;" class="text-left">
					'.$rel['qty'].'
					</td>';
					
					echo '<td style="vertical-align:top;" class="text-center">
					 '.$rel['unit_name'].'
					</td>					
					
					<td style="vertical-align:top">

					<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_new_product_data('.$rel['qc_rej_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
					</td>	
					</tr>';
					$total=$total+$rel['qty'];
					$i++;
				}
			}

			else{
				echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> 
					<input type="hidden" name="total_new_qty" id="total_new_qty" value="'.$total.'" />
				</div>
			</div>';
		}else if(strtolower($POST['mode']) == "add_new_product") {
			$info['product_id']	= $POST['product_id'];
			$info['qty']		= $POST['qty'];	
			$info['unit_id']	= $POST['unit_id'];
			$info['status']		= 3;
			$info['batch_id']	= $POST['batch_id'];
			$info['cdate']		= date("Y-m-d H:i:s"); 
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id']; 

			$inserid=add_record('tbl_qc_reject_new_product', $info, $dbcon);

			if($inserid){
				echo "1";
			}else{
				echo "0";
			}
		}else if(strtolower($POST['mode']) == "delete_new_product_data") {
			$info['status']  = 2;
			$update=update_record('tbl_qc_reject_new_product', $info,"qc_rej_id=".$POST['id'], $dbcon);	
						
			if($update)
				echo "1";	
			else
				echo "0";		
		}
  
function upload_qc_receipt($FILES,$dbcon,$qc_id){
	$cnt=count($_FILES['qc_file']['name']);
	for( $i=0 ; $i < $cnt ; $i++ ) {
		if(!empty($_FILES['qc_file']['tmp_name'][$i])) {
			$rand=rand(0,999999);
			$temp = explode(".", $_FILES["qc_file"]["name"][$i]);
			$extension = strtolower(end($temp));
			$file_name = $_FILES['qc_file']['name'][$i];
			$err = $_FILES["qc_file"]["tmp_name"][$i];
			$file_name = "qc_rec_".$rand.'.'.$extension;
			move_uploaded_file($err,QC_FILE_UPING.$file_name);
			
			$attch['qc_id']			= $qc_id;
			$attch['qc_file']		= $file_name;
			$attch['cdate']			= date("Y-m-d H:i:s"); 
			$attch['user_id']		= $_SESSION['user_id'];
			$attch['company_id']	= $_SESSION['company_id']; 
			$inserid=add_record('tbl_qc_attch', $attch, $dbcon);
			//return 	$file_name;
		}
	}
}
function get_qc_item_info($dbcon, $product_id, $grn_trn_id, $pr_param_id, $qc_work_type, $branch_id, $item_number){

	$query="select * from tbl_item_wise_qc where product_id=".$product_id." and grn_trn_id=".$grn_trn_id." and pr_param_id=".$pr_param_id." and qc_work_type=".$qc_work_type." and item_number=".$item_number." and branch_id=".$branch_id." and company_id=".$_SESSION['company_id'];

	$result=$dbcon->query($query);
	
	$data = brp_mysqli_fetch_assoc($result);
	return $data;
}

function get_qc_item_status($dbcon, $product_id, $grn_trn_id, $item_number, $qc_work_type, $branch_id){
	$query="select * from tbl_item_wise_status where product_id=".$product_id." and grn_trn_id=".$grn_trn_id." and qc_work_type=".$qc_work_type." and item_number=".$item_number." and branch_id=".$branch_id." and company_id=".$_SESSION['company_id'];

	$result=$dbcon->query($query);
	
	if(brp_mysqli_num_rows($result) > 0){
		$data = brp_mysqli_fetch_assoc($result);
	}else{
		$data = '';
	}
	
	return $data;
}

function calculate_qty_status_wise($dbcon, $product_id, $grn_trn_id, $qc_work_type){
	$pass_sql = "select IFNULL(sum(accepted_qty), 0) as accepted from tbl_item_wise_status where product_id = '".$product_id."' and grn_trn_id = '".$grn_trn_id."' and qc_work_type = '".$qc_work_type."' and status='1' and company_id='".$_SESSION['company_id']."' ";
	$pass_result=$dbcon->query($pass_sql);
	$pass_assoc = brp_mysqli_fetch_assoc($pass_result);
	$accepted = $pass_assoc['accepted'];
	
	$reject_sql = "select IFNULL(sum(rejected_qty), 0) as rejected from tbl_item_wise_status where product_id = '".$product_id."' and grn_trn_id = '".$grn_trn_id."' and qc_work_type = '".$qc_work_type."' and status='2' and company_id='".$_SESSION['company_id']."' ";
	$reject_result=$dbcon->query($reject_sql);
	$reject_assoc = brp_mysqli_fetch_assoc($reject_result);
	$rejected = $reject_assoc['rejected'];
	
	$reprocess_sql = "select IFNULL(sum(reprocess_qty), 0) as reprocessed from tbl_item_wise_status where product_id = '".$product_id."' and grn_trn_id = '".$grn_trn_id."' and qc_work_type = '".$qc_work_type."' and status='3' and company_id='".$_SESSION['company_id']."' ";
	$reprocess_result=$dbcon->query($reprocess_sql);
	$reprocess_assoc = brp_mysqli_fetch_assoc($reprocess_result);
	$reprocessed = $reprocess_assoc['reprocessed'];

	$arr = array('accepted' => $accepted, 'rejected' => $rejected, 'reprocessed' => $reprocessed);

	return $arr;
}


function add_new_product_batch($dbcon,$qc_id,$POST){
	$qry = "select * from tbl_qc_reject_new_product where status = 0 and qc_id = " . $qc_id . " and batch_id = " .$POST['batch_id'];
	$result=$dbcon->query($qry);
	while($row = brp_mysqli_fetch_assoc($result)){
	 	$pro_qry = "select * from product_mst where product_id = " .$row['product_id'];
		$pro_result=$dbcon->query($pro_qry);
		$pr_row = brp_mysqli_fetch_assoc($pro_result);

		$rate_unit = $row['unit_id'];
		$remaining_qty = $row['qty'];

		if($row['unit_id']==$pr_row['product_conv_unit']){
			$type="base_unit";
			$conv_qty=$remaining_qty;
			$base_qty = ($conv_qty/$pr_row['product_conv_qty']) * $pr_row['product_base_qty'];
		}else{
			$type="conv_unit";
			$base_qty=$remaining_qty;
			$conv_qty = ($base_qty/$pr_row['product_base_qty']) *$pr_row['product_conv_qty'];
		}

		$batch_qty=$base_qty;
		$batch_conv_qty=$conv_qty;
			
		// $batch_info['grn_id']			= $grn_id;	
		// $batch_info['grn_trn_id']		= $tbl_grn_trn_id;	
		// $batch_info['batch_no']			= $batch_no;
		$batch_info['batch_qty']		= $remaining_qty;
		$batch_info['order_no']			= $POST['qc_no'];
		$batch_info['product_id']		= $row['product_id'];
		$batch_info['grn_date']			= date('Y-m-d',strtotime($POST['qc_date']));
		$batch_info['batch_type']		= $companyConfiguration['batch_type'];
		$batch_info['production_type']	= '1';			
		$batch_info['status']			= '0';			
		
		$batch_info['qc_status']		= 1;
		$batch_info['accept_qty']		= $remaining_qty;
		$batch_info['qc_qty']			= $remaining_qty;
		
		$batch_info['cdate']			= date("Y-m-d H:i:s"); 
		$batch_info['user_id']			= $_SESSION['user_id'];
		$batch_info['company_id']		= $_SESSION['company_id'];	
		$batch_info['branch_id']		= $POST['branch_id'];
		$batch_info['batch_unit']		= $rate_unit;
		$batch_info['base_qty']			= $batch_qty;
		$batch_info['base_unit']		= $pr_row['product_base_unit'];
		$batch_info['conv_qty']			= $batch_conv_qty;
		$batch_info['conv_unit']		= $pr_row['product_conv_unit'];
		$batch_info['qc_id']			= $qc_id;

		$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	

	 }
}
?>