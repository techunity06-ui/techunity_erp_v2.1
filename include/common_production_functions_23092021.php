<?php
	
	function production_start_count_using_p_id($dbcon,$pid,$is_store_approval = 0){
		$query="select p_status,p_id,p_product_id as product_id,p_qty as actual_qty,previous_process_id,process_unit from tbl_allocate_process 
				where p_id IN (".$pid.")";
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		$total_working_qty=0;
		if($cnt>0){
			while($row=brp_mysqli_fetch_assoc($result)){
				$working_qty=0;
				
				if($row['p_status']=="1"){
					//check working qty if process running. (its use process stop time)
					$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
					$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
					$start_qty=$total_start_qty-$total_end_qty;
					
					if($row['previous_process_id']=="0"){
						$matirial_available_qty=check_row_material_availability($dbcon,$row['p_id'],$is_store_approval);
						if($matirial_available_qty>$start_qty){
							$working_qty=$matirial_available_qty-$start_qty;
						}
					}else{
						//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
						$process_start_pending_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id);
						if($process_start_pending_qty>$start_qty){
							$working_qty=$process_start_pending_qty-$start_qty;
						}
					}
					
				}else if($row['previous_process_id']=="0"){
					//check material availability when this is first process 
					$working_qty=check_row_material_availability($dbcon,$row['p_id'],$is_store_approval);
				}else{
					//$working_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
					$working_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id);
					
				}
				$total_working_qty=$total_working_qty+$working_qty;
			}
		} 
		return $total_working_qty;
	}
	
	function production_end_count_using_p_id($dbcon,$pid){
		$query="select p_id from tbl_allocate_process 
				where p_status=1 and p_id IN (".$pid.")";
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		$total_working_qty=0;
		if($cnt>0){
			while($row=brp_mysqli_fetch_assoc($result)){
				$working_qty=0;
					//check working qty if process running.
					$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
					$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
					$working_qty=$total_start_qty-$total_end_qty;
					$total_working_qty=$total_working_qty+$working_qty;
			}
		} 
		return $total_working_qty;
	}
	
	function total_process_transaction_qty($dbcon,$type,$pid){
		$query="select sum(pt_qty) as return_qty from tbl_allocate_process_trn 
				where pt_alloc_id in (".$pid.") and p_status=".$type;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);
		return $row['return_qty'];
	}
	
	function check_row_material_availability($dbcon,$p_id,$is_store_approval){
		//p_ref_id = tbl_request_product  main id rp_id
		
		$availability_material=0;
		$query="select p_id,p_product_id as product_id,p_qty as actual_qty,p_ref_id from tbl_allocate_process 
				where p_id=".$p_id;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt){
			while($row=brp_mysqli_fetch_assoc($result)){
				
				$query_2="select rp_pid from tbl_request_product 
				where status=3 and perent_id=".$row['p_ref_id'];
				$result_2=$dbcon->query($query_2);
				$cnt_2=brp_mysqli_num_rows($result_2);
				
				if(empty($cnt_2)){
					//req_qty_one = use for one product qty
					//rp_pid = product_id
					//purchase_unit = unit id
					//rp_id = tbl_request_product  main id rp_id
					
					$query_1="select req_qty_one,rp_pid as product_id,purchase_unit as unit_id,rp_id,branch_id from tbl_request_product 
					where status=0 and perent_id=".$row['p_ref_id'];
					$result_1=$dbcon->query($query_1);
					$cnt_1=brp_mysqli_num_rows($result_1);
					if($cnt_1){
						$availability_material_array=array();
						while($row_1=brp_mysqli_fetch_assoc($result_1)){
							$use_one_product_qty=$row_1['req_qty_one'];
							$total_required_qty=$row['actual_qty']*$use_one_product_qty;
							
							$allocate_stock=reserve_stock($dbcon,$row_1['product_id'],$row_1['unit_id'],"",$row_1['rp_id'],"","",$row_1['branch_id'],$is_store_approval);
							if($allocate_stock>="0"){
								$availability_material_array[]=($allocate_stock/$use_one_product_qty);
							}else{
								$availability_material_array[]=0;
							}
						}
						$availability_material=min($availability_material_array);
					}
				
				}
			}
		}
		return $availability_material;
	}
	
	function check_process_stock_using_p_id($dbcon,$p_id){
		$availability_process_stock=0;
		$query="select previous_process_id from tbl_allocate_process 
				where p_id in (".$p_id.")";
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			while($row=brp_mysqli_fetch_assoc($result)){
				$query_1="select IFNULL((IFNULL(process_stock,0)-IFNULL(process_used_stock,0)),0) as current_process_stock from tbl_allocate_process 
					where p_id=".$row['previous_process_id'];
				$result_1=$dbcon->query($query_1);
				$cnt_1=brp_mysqli_num_rows($result_1);
				if($cnt_1>0){
					while($row_1=brp_mysqli_fetch_assoc($result_1)){
						$availability_process_stock=$availability_process_stock+$row_1['current_process_stock'];
					}
				}
			}
		}
		return $availability_process_stock;
	}
	
	function total_production_pending_qty($dbcon,$p_id){
		$query="select sum(p_qty-start_qty) as total_pending_qty from tbl_allocate_process 
				where p_id in (".$p_id.")";
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);
		return $row['total_pending_qty'];
	} 
	
	function load_series_no_using_type_id($dbcon,$type_id,$company_id,$branch_id){
		$row=array();
		$where_branch="";
			if($branch_id){
				$where_branch=" and branch_id=".$branch_id;
			}
			
			$query="select taxinvoice_start,invoice_format,format_value,end_format_value from tbl_invoicetype where type_id=".$type_id." and company_id=".$company_id."".$where_branch;
			$result=$dbcon->query($query);
			$rows=brp_mysqli_fetch_assoc($result);
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
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
		return $row['invoiceno'];

	}
	
	function update_series_no_using_type_id($dbcon,$type_id,$company_id,$branch_id){
		$where_branch="";
			if($branch_id){
				$where_branch=" and branch_id=".$branch_id;
			}
			
			$query="select taxinvoice_start,invoicetype_id from tbl_invoicetype where type_id=".$type_id." and company_id=".$company_id."".$where_branch;
			$result=$dbcon->query($query);
			$rows=brp_mysqli_fetch_assoc($result);
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			
			$info['taxinvoice_start']	= $id;
			$updatetrnid=update_record('tbl_invoicetype',$info,"invoicetype_id=".$rows['invoicetype_id'] , $dbcon);
	}
	
	function process_wise_production_count($dbcon,$process_id,$process_type,$type,$is_store_approval){
		
		if($_SESSION['user_type']!='2'){
			$check_branch = check_branch('ap',$_SESSION['branch_id']);
		}
		$s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status from tbl_allocate_process as ap
			
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join tbl_category as tc on p.product_category=tc.cat_id
		left join branch_mst as branch on branch.branch_id=ap.branch_id
		where ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
		$q=$dbcon->query($s_ql);
		$total_working=0;
		while($rel=brp_mysqli_fetch_array($q))
		{
			if($type=="1"){
				$working_qty=production_start_count_using_p_id($dbcon,$rel['allocate_id'],$is_store_approval);
			}else{
				$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
			}
			$total_working=$total_working+$working_qty;
			
		}
		return $total_working;
	}

	function store_approve_process_wise_production_count($dbcon,$process_id,$process_type,$type){
		
		if($_SESSION['user_type']!='2'){
			$check_branch = check_branch('ap',$_SESSION['branch_id']);
		}
		$s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status from tbl_allocate_process as ap
			
		left join product_mst as p on p.product_id=ap.p_product_id 
		left join tbl_category as tc on p.product_category=tc.cat_id
		left join branch_mst as branch on branch.branch_id=ap.branch_id
		where ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
		$q=$dbcon->query($s_ql);
		$total_working=0;
		while($rel=brp_mysqli_fetch_array($q))
		{
			if($type=="1"){
				$working_qty=production_start_count_using_p_id($dbcon,$rel['allocate_id'],1);
			}else{
				$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
			}
			$total_working=$total_working+$working_qty;
			
		}
		return $total_working;
	}
	
	function job_work_sub_trn_grn_pending_qty($dbcon,$job_work_sub_trn_id){
		
		$query="select (job_sub_trn.product_base_qty-IFNULL(grn_used_qty,0)) as grn_pending_qty from tbl_job_work_sub_trn as job_sub_trn 
				left join 
					(select IFNULL(sum(grn_sub_trn.product_qty),0) as grn_used_qty,grn_sub_trn.job_work_sub_trn_id from tbl_grn_sub_trn as grn_sub_trn 
						where grn_sub_trn.status=0 and grn_sub_trn.company_id=".$_SESSION['company_id']."  group by grn_sub_trn.job_work_sub_trn_id) as craditpo on craditpo.job_work_sub_trn_id=job_sub_trn.job_work_sub_trn_id
				where job_sub_trn.job_work_sub_trn_id=".$job_work_sub_trn_id." group by job_sub_trn.job_work_sub_trn_id";

		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_assoc($result);
		}else{
			$row['grn_pending_qty']="0";
		}
		return $row['grn_pending_qty'];
	}
	
	function grn_status_update_in_tbl_job_work_sub_trn($dbcon,$job_work_sub_trn_id){
		$query="select job_work_trn_id from tbl_job_work_sub_trn as job_sub_trn
				where job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.job_work_sub_trn_id=".$job_work_sub_trn_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_assoc($result);
			//update status start
			$job_work_sub_trn_grn_pending_qty=job_work_sub_trn_grn_pending_qty($dbcon,$job_work_sub_trn_id);
			if($job_work_sub_trn_grn_pending_qty>0){
				//update status not complete
				$info['grn_complete_status']	= "0";
			}else{
				//update status complete
				$info['grn_complete_status']	= "1";
			}
			
			$updatetrnid=update_record('tbl_job_work_sub_trn',$info,"job_work_sub_trn_id=".$job_work_sub_trn_id , $dbcon);
			//update status end
			
			//jobwork trn grn status update
			grn_status_update_in_tbl_job_work_trn($dbcon,$row['job_work_trn_id']);
		}
	}
	
	function grn_status_update_in_tbl_job_work_trn($dbcon,$job_work_trn_id){
		$query="select job_work_id from tbl_job_work_trn as job_trn
				where job_trn.job_work_trn_status=0 and job_trn.job_work_trn_id=".$job_work_trn_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_assoc($result);
			//update status start
				
				$query1="select job_work_trn_id from tbl_job_work_sub_trn as job_sub_trn
				where job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.grn_complete_status=0 and job_sub_trn.job_work_trn_id=".$job_work_trn_id ;
				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);
				
				if($cnt1>"0"){
					//update status not complete
					$info['grn_complete_status']	= "0";
				}else{
					//update status complete
					$info['grn_complete_status']	= "1";
				}
				
				$updatetrnid=update_record('tbl_job_work_trn',$info,"job_work_trn_id=".$job_work_trn_id , $dbcon);
				
			//update status end
			grn_status_update_in_tbl_job_work($dbcon,$row['job_work_id']);
		}
	}
	
	function grn_status_update_in_tbl_job_work($dbcon,$job_work_id){
		//update status start
				
			$query="select job_work_id from tbl_job_work_trn as job_work_trn
			where job_work_trn.job_work_trn_status=0 and job_work_trn.grn_complete_status=0 and job_work_trn.job_work_id=".$job_work_id ;
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);
			
			if($cnt>"0"){
				//update status not complete
				$info['grn_complete_status']	= "0";
			}else{
				//update status complete
				$info['grn_complete_status']	= "1";
			}
			
			$updatetrnid=update_record('tbl_job_work',$info,"job_work_id=".$job_work_id , $dbcon);
			
		//update status end
	}
	
	function allocate_process_trn_stop_entry_start_entry_wise($dbcon,$grn_sub_trn_qty,$p_id){
		$query1 = "select allocate_trn.pt_id,(allocate_trn.pt_qty-allocate_trn.pt_used_qty) pending_qty,allocate_process.p_ref_id,allocate_process.process_id,allocate_trn.pt_used_qty,allocate_process.pen_qty,allocate_process.p_product_id from tbl_allocate_process_trn as allocate_trn
					left join tbl_allocate_process as allocate_process on allocate_process.p_id=allocate_trn.pt_alloc_id
					where allocate_trn.p_status=0 and allocate_trn.pt_alloc_id=".$p_id ;
		$result1=$dbcon->query($query1);
		$cnt1=brp_mysqli_num_rows($result1);
		
		$return_qty=0;
		
		if($cnt1>0){
			
			while($row1=brp_mysqli_fetch_array($result1))
			{
				if($row1['pending_qty']>"0" && $grn_sub_trn_qty>"0"){
					if($row1['pending_qty']<=$grn_sub_trn_qty){
						//use $row1['pending_qty']
						$allocate_trn_update_qty=$row1['pending_qty'];
					}else{
						//use $grn_sub_trn_qty
						$allocate_trn_update_qty=$grn_sub_trn_qty;
					}
					
					$grn_sub_trn_qty=$grn_sub_trn_qty-$allocate_trn_update_qty;
					
					add_process_trn($dbcon,$p_id,$row1['p_ref_id'],$row1['p_product_id'],$row1['process_id'],$allocate_trn_update_qty,"1","0",$row1['pt_id']);
					
					$allocate_trn_used_update_qty=$row1['pt_used_qty']+$allocate_trn_update_qty;
					
					$info_allocate_trn_used_update['pt_used_qty']	= $allocate_trn_used_update_qty;
			
					$updatetrnid=update_record('tbl_allocate_process_trn',$info_allocate_trn_used_update,"pt_id=".$row1['pt_id'] , $dbcon);
					
					$return_qty=$return_qty+$allocate_trn_update_qty;
				}
			}
		}
		
		return $return_qty;
	}
	
	function tbl_allocate_process_update_pen_qty($dbcon,$p_id,$qty){
		$query = "select pen_qty from tbl_allocate_process as allocate_process
					where allocate_process.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
			
			$allocate_pen_qty_update=$row['pen_qty']-$qty;
		
			$info_allocate_pen_qty_update['pen_qty']	= $allocate_pen_qty_update;

			$updatetrnid1=update_record('tbl_allocate_process',$info_allocate_pen_qty_update,"p_id=".$p_id , $dbcon);
		}
	}
	
	function tbl_allocate_process_update_p_status($dbcon,$p_id){
		
		$stop_pending_qty=production_end_count_using_p_id($dbcon,$p_id);
		
		$query = "select pen_qty from tbl_allocate_process as allocate_process
					where allocate_process.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
		}
		
		if($stop_pending_qty>"0"){
			
			$info['p_status']= "1";
		
		} else if($row['pen_qty']>"0"){
			
			$info['p_status']= "0";
		
		} else{
			
			$info['p_status']= "3";
		
		}
		
		$updatetrnid1=update_record('tbl_allocate_process',$info,"p_id=".$p_id , $dbcon);
	}
	
	function grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_permission){
		$query = "select grn_trn_sub_id,product_id,purchaseordertrn_id,job_work_sub_trn_id,product_qty,product_base_unit from tbl_grn_sub_trn as grn_sub_trn
					where grn_sub_trn.grn_trn_sub_id=".$grn_trn_sub_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			
			$row=brp_mysqli_fetch_array($result);
			
			if(!empty($row['job_work_sub_trn_id'])){
				
				$query1= "select p_id,rp_id from tbl_job_work_sub_trn as job_sub_trn
				where job_sub_trn.job_work_sub_trn_id=".$row['job_work_sub_trn_id'] ;
				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);
				
				if($cnt1>0){
					$row1=brp_mysqli_fetch_array($result1);
					
				$query2 = "select grn_godown,branch_id from tbl_grn_trn as grn_trn
						where grn_trn.grn_trn_id=".$row['grn_trn_id'] ;
						$result2=$dbcon->query($query1);
						$row2=brp_mysqli_fetch_array($result2);
					
					$stock_date=date("Y-m-d");
					
					$process=p_id_wise_find_previous_and_next_process($dbcon,$row1['p_id']);
					$process_pr=json_decode($process);
			
					$next_process_id=$process_pr->next_process_id;
					$next_process_type=$process_pr->next_process_type;
					$next_process_priority=$process_pr->next_process_priority;
					
					$previous_process_pid=$process_pr->previous_process_pid;
					
					if($previous_process_pid=="0" && $next_process_id=="0"){
						//only one product
							
							//raw matirial stock deduct start
								p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$row1['p_id'],$row1['rp_id'],$row['product_qty'],$row['product_base_unit']);
							//raw matirial stock deduct end
						
							if($qc_permission=="0"){
								
								//product stock add start 
									$stock_id=add_stock($dbcon,$row['product_id'],$row['product_base_unit'],$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$row2['grn_godown'],$row['product_qty'],1,$row2['branch_id']);
								//product stock add end
								
								//product reserve stock start
									grn_sub_trn_wise_reserv_stock_add($dbcon,$row['product_qty'],$row['product_base_unit'],$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id);
								//product reserve stock end
							
							}
							
						
						
					}else if($previous_process_pid=="0"){
						//first process
						
							//raw matirial stock deduct start
								p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$row1['p_id'],$row1['rp_id'],$row['product_qty'],$row['product_base_unit']);
							//raw matirial stock deduct end
					
						//production_add_process_stock($dbcon,$row['product_qty'],$row1['p_id'],"1");
						
						if($qc_permission=="0"){
							
							//process stock add start
								$process_stock_id=production_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$stock_date,$row2['grn_godown'],"Grn_sub_trn",$row['grn_trn_sub_id']);
							//process stock add end
							
							//next process entry start
								$next_pid=next_process_entry($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority);
							//next process entry end
							
							//reserve process stock start
								$process_reserve_id=production_reserve_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id']);
							//reserve process stock end
						}
						
						
					}else if($next_process_id=="0"){
						//last process
						
						//process stock deduct start
							//production_add_process_stock($dbcon,$row['product_qty'],$previous_process_pid,"2");
							production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$row['grn_trn_sub_id'],"Grn_sub_trn",$stock_date);
						//process stock deduct end
						
						if($qc_permission=="0"){
							//product stock add start 
								$stock_id=add_stock($dbcon,$row['product_id'],$row['product_base_unit'],$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$row2['grn_godown'],$row['product_qty'],1,$row2['branch_id']);
							//product stock add end
							
							//reserve stock add start
								grn_sub_trn_wise_reserv_stock_add($dbcon,$row['product_qty'],$row['product_base_unit'],$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id);
							//reserve stock add end
						
						}
					}else{
						//middel process
						
						//process stock deduct start
							//production_add_process_stock($dbcon,$row['product_qty'],$previous_process_pid,"2");
							//production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$previous_process_pid,"2");
							production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$row['grn_trn_sub_id'],"Grn_sub_trn",$stock_date);
						//process stock deduct end
						
						if($qc_permission=="0"){
							//production_add_process_stock($dbcon,$row['product_qty'],$row1['p_id'],"1");
							//process stock add start
								
								$process_stock_id=production_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$stock_date,$row2['grn_godown'],"Grn_sub_trn",$row['grn_trn_sub_id']);
							//process stock add end
							
							//next process entry start
								$next_pid=next_process_entry($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority);
							//next process entry stop
							
							//reserve process stock start
								$process_reserve_id=production_reserve_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id']);
							//reserve process stock end
						}
					}
				}
			}
			
			/* if(!empty($row['purchaseordertrn_id'])){
				
			} */
		}
	}
	
	function p_id_wise_find_previous_and_next_process($dbcon,$p_id){
		$query= "select p_id,process_priority,previous_process_id,p_ref_id from tbl_allocate_process as allo_process
					where allo_process.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		
		$process_priority=0;
		$process_type=0;
		$process_id=0;
		$previous_process_pid=0;
		
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
			
			$query1= "select process_priority,process_type,process_id from tbl_wororder_product_process as wor_pro_process
				where wor_pro_process.rp_id=".$row['p_ref_id']." and wor_pro_process.process_priority>".$row['process_priority']." limit 1" ;
			$result1=$dbcon->query($query1);
			$cnt1=brp_mysqli_num_rows($result1);
			
			if($cnt1>0){
				$row1=brp_mysqli_fetch_array($result1);
				$process_priority=$row1['process_priority'];
				$process_type=$row1['process_type'];
				$process_id=$row1['process_id'];
			}
			$previous_process_pid=$row['previous_process_id'];
		}
		
		$return_row['next_process_id']			=$process_id;
		$return_row['next_process_type']		=$process_type;
		$return_row['next_process_priority']	=$process_priority;
		
		$return_row['previous_process_pid']		=$previous_process_pid;
	
		return json_encode($return_row);
	}
	
	function p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$p_id,$rp_id,$product_qty,$unit_id){
		$query = "select rp_id,perent_id,req_qty_one,rp_pid as product_id,branch_id,purchase_unit from tbl_request_product as req_product
					where req_product.perent_id=".$rp_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			
			while($row=brp_mysqli_fetch_array($result)){
				$total_required_qty=$row['req_qty_one']*$product_qty;
				
				$info['allocate_process_id']	= $p_id;
				$info['product_id']				= $row['product_id'];
				$info['qty_need_for_single']	= $row['req_qty_one'];
				$info['total_req_qty']			= $total_required_qty;
				//$info['unit_id']				= $unit_id;
				$info['unit_id']				= $row['purchase_unit'];
				$info['grn_trn_sub_id']			= $grn_trn_sub_id;
				
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];
				
				$process_material_id=add_record('tbl_allocate_process_material',$info, $dbcon,$row['branch_id']);
				
				tbl_allocate_process_material_wise_reserve_stock_minus($dbcon,$process_material_id);
				
			}
			
		}
	}
	
	function production_reserve_stock_p_id_wise($dbcon,$product_id,$unit_id,$p_id,$reserve_id,$totaladd){
		
		if(!empty($p_id)){
			$p_id_where=" and p_id=".$p_id;
		}
		if(!empty($reserve_id)){
		$rwhser=" and reserve_id=".$reserve_id;
		$rwhser22=" and ref_id=".$reserve_id;
		}
		if(!empty($request_id)){
			$rwhser1=" and request_id=".$request_id;
		}
		if(!empty($complaint_id)){
			$rwhser2=" and complaint_id=".$complaint_id;
		}
		if(!empty($sales_order_trn_id)){
			$rwhser23=" and sales_order_trn_id=".$sales_order_trn_id;
		}
		if(!empty($branch_id)){
			$where_branch=" and branch_id=".$branch_id;	
		}
		
		$query1="select sum(base_stock) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($result1);
		
		$query2="select sum(convert_stock) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and convert_unit=".$unit_id." and product_id=".$product_id;
		$result2=$dbcon->query($query2);
		$row2=brp_mysqli_fetch_assoc($result2);
		if(empty($totaladd)){
			$query3="select sum(base_stock) as base_usedqty from tbl_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
			$result3=$dbcon->query($query3);
			$row3=brp_mysqli_fetch_assoc($result3);
			
			$query4="select sum(convert_stock) as conv_usedqty from tbl_reserve_stock where stock_status=0 and base_unit!=convert_unit ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and stock_flage=2 and convert_unit=".$unit_id." and product_id=".$product_id;
			$result4=$dbcon->query($query4);
			$row4=brp_mysqli_fetch_assoc($result4);
		}
		if(empty($totaladd)){
			$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
		}else{
			$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
		}
		return $res_qty;
		
	}
	
	function tbl_allocate_process_material_wise_reserve_stock_minus($dbcon,$process_material_id){
		
		$query = "select product_id,allocate_process_id as p_id,total_req_qty,unit_id,grn_trn_sub_id,remark,used_qty from tbl_allocate_process_material as allo_mat
					where allo_mat.process_material_id=".$process_material_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			
			$row=brp_mysqli_fetch_array($result);
			
			$reserve_stock=production_reserve_stock_p_id_wise($dbcon,$row['product_id'],$row['unit_id'],$row['p_id']);
			
			if($row['total_req_qty']<=$reserve_stock){
				$used_qty=$row['total_req_qty'];
			}else{
				$used_qty=$reserve_stock;
				$error_stock_qty=$row['total_req_qty']-$reserve_stock;
				$remark=$row['remark']." -- ".$error_stock_qty." stock not fetch.";
				$info['remark']	= $remark;
				
			}
				$apupdate=$row['used_qty']+$used_qty;
				$info['used_qty']	= $apupdate;
			$updatetrnid=update_record('tbl_allocate_process_material',$info,"process_material_id=".$process_material_id , $dbcon);
			
			 $query1 = "select * from tbl_reserve_stock as res_stock
					where res_stock.used_status=0 and res_stock.stock_flage=1 and res_stock.p_id=".$row['p_id'] ;
				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);
				if($cnt1>0){
					while($row1=brp_mysqli_fetch_array($result1)){
						$reservestock=production_reserve_stock_p_id_wise($dbcon,$row['product_id'],$row['unit_id'],"",$row1['reserve_id']);
						if($used_qty!="0" && $reservestock!="0"){
							if($used_qty>=$reservestock){
								//used $reservestock
								$reserve_minus_stock_qty=$reservestock;
							}else{
								//used $used_qty
								$reserve_minus_stock_qty=$used_qty;
							}
							
							$que="select * from product_mst as ta where product_id=".$row['product_id'];
							$rs_di=$dbcon->query($que);
							$re=brp_mysqli_fetch_assoc($rs_di);
							
							if($re['product_conv_unit']==$row['unit_id']){
								$type="base_unit";
								$con_stock=$reserve_minus_stock_qty;
								$base_stock=convert_stock_new($dbcon,$reserve_minus_stock_qty,$row['product_id'],$type);
							}else{
								$type="conv_unit";
								$base_stock=$reserve_minus_stock_qty;
								$con_stock=convert_stock_new($dbcon,$reserve_minus_stock_qty,$row['product_id'],$type);
							}
							
							$info_rese['reserve_date']		= date("Y-m-d");
							$info_rese['product_id']		= $row1['product_id'];
							$info_rese['godown_id']			= $row1['godown_id'];
							$info_rese['base_unit']			= $re['product_base_unit'];
							$info_rese['base_stock']		= $base_stock;
							$info_rese['convert_unit']		= $re['product_conv_unit'];
							$info_rese['convert_stock']		= $con_stock;
							$info_rese['stock_flage']		= "2";
							$info_rese['request_id']		= $row1['request_id'];
							$info_rese['ref_name']			= "grn";
							$info_rese['ref_id']			= $row1['reserve_id'];
							$info_rese['p_id']				= $row['p_id'];
							$info_rese['stock_id']			= $row1['stock_id'];
							
							$info_rese['cdate']					= date("Y-m-d H:i:s");
							$info_rese['user_id']				= $_SESSION['user_id'];
							$info_rese['company_id']			= $_SESSION['company_id'];
							
							$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row1['branch_id']);
							
							add_stock($dbcon,$row1['product_id'],$re['product_base_unit'],$info_rese['reserve_date'],"Grn",$row['grn_trn_sub_id'],$row1['godown_id'],$base_stock,2,$row1['branch_id'],$row1['stock_id'],$reserve_id_id);
						}
					}
					
				} 
				
			
		}
	}
	
	function production_add_process_stock($dbcon,$product_qty,$unit_id,$p_id,$stock_date,$godown_id,$ref_name,$ref_id){
		$query = "select p_product_id as product_id,process_id,process_stock,branch_id from tbl_allocate_process as allo_mat
					where allo_mat.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
		
		$que="select * from product_mst as ta where product_id=".$row['product_id'];
			$rs_di=$dbcon->query($que);
			$re=brp_mysqli_fetch_assoc($rs_di);
			
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$product_qty;
				$base_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
			}else{
				$type="conv_unit";
				$base_stock=$product_qty;
				$con_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
			}
			
			$info_stockadd['process_stock_date']		= date("Y-m-d",strtotime($stock_date));
			$info_stockadd['product_id']				= $row['product_id'];
			$info_stockadd['process_id']				= $row['process_id'];
			$info_stockadd['base_stock']				= $base_stock;
			$info_stockadd['base_unit']					= $re['product_base_unit'];
			$info_stockadd['conv_stock']				= $con_stock;
			$info_stockadd['conv_unit']					= $re['product_conv_unit'];
			$info_stockadd['stock_flage']				= "1";
			$info_stockadd['godown_id']					= $godown_id;
			$info_stockadd['ref_name']					= $ref_name;
			$info_stockadd['ref_id']					= $ref_id;
			$info_stockadd['stock_status']				= "0";
			$info_stockadd['cdate']						= date("Y-m-d H:i:s");
			$info_stockadd['user_id']					= $_SESSION['user_id'];
			$info_stockadd['company_id']				= $_SESSION['company_id'];
			
			$process_stock_id=add_record('tbl_process_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);
			
			$update_qty=$row['process_stock']+$product_qty;
			
			$info['process_stock']	= $update_qty;
			$updatetrnid=update_record('tbl_allocate_process',$info,"p_id=".$p_id , $dbcon);
		}
		
		return $process_stock_id;
	}
	
	function production_deduct_process_stock($dbcon,$product_qty,$unit_id,$p_id,$stock_date,$godown_id,$ref_name,$ref_id,$perent_id,$reserve_id){
		$query = "select p_product_id as product_id,process_id,process_used_stock,branch_id from tbl_allocate_process as allo_mat
					where allo_mat.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
		
		$que="select * from product_mst as ta where product_id=".$row['product_id'];
			$rs_di=$dbcon->query($que);
			$re=brp_mysqli_fetch_assoc($rs_di);
			
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$product_qty;
				$base_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
			}else{
				$type="conv_unit";
				$base_stock=$product_qty;
				$con_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
			}
			
			$info_stockadd['process_stock_date']		= date("Y-m-d",strtotime($stock_date));
			$info_stockadd['product_id']				= $row['product_id'];
			$info_stockadd['process_id']				= $row['process_id'];
			$info_stockadd['base_stock']				= $base_stock;
			$info_stockadd['base_unit']					= $re['product_base_unit'];
			$info_stockadd['conv_stock']				= $con_stock;
			$info_stockadd['conv_unit']					= $re['product_conv_unit'];
			$info_stockadd['stock_flage']				= "2";
			$info_stockadd['godown_id']					= $godown_id;
			$info_stockadd['ref_name']					= $ref_name;
			$info_stockadd['ref_id']					= $ref_id;
			$info_stockadd['perent_id']					= $perent_id;
			$info_stockadd['reserve_id']				= $reserve_id;
			$info_stockadd['stock_status']				= "0";
			$info_stockadd['cdate']						= date("Y-m-d H:i:s");
			$info_stockadd['user_id']					= $_SESSION['user_id'];
			$info_stockadd['company_id']				= $_SESSION['company_id'];
			
			$process_stock_id=add_record('tbl_process_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);
			
			$update_qty=$row['process_used_stock']+$product_qty;
			
			$info['process_used_stock']	= $update_qty;
			$updatetrnid=update_record('tbl_allocate_process',$info,"p_id=".$p_id , $dbcon);
		}
		
		return $process_stock_id;
	}
	
	function total_allocate_process_qty($dbcon,$p_id){
		$query = "select p_qty from tbl_allocate_process as req_pro
					where req_pro.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		$row=brp_mysqli_fetch_array($result);
		
		return $row['p_qty'];
	}
	
	function grn_sub_trn_wise_reserv_stock_add($dbcon,$product_qty,$product_base_unit,$stock_date,$p_id,$rp_id,$stock_id){
		// check perent id
		// check perent id ma allocate process ma entry ketli 6e first process ni
		// allocate process ni qty jetlu reseve stock add thayo k nai baki hoy to pending qty lavvi
		// je qty pending ave ae allocate kari deva ni
		
		$query = "select perent_id,rp_pid as product_id,rp_req_qty,purchase_unit from tbl_request_product as req_pro
					where req_pro.rp_id=".$rp_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
			if($row['perent_id']!="0"){
				
				$query1 = "select p_id,p_qty,process_unit,p_product_id as product_id,branch_id from tbl_allocate_process as allo_pro
				where allo_pro.previous_process_id=0 and allo_pro.p_ref_id=".$row['perent_id'] ;
				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);
				if($cnt1>0){
					while($row1=brp_mysqli_fetch_array($result1)){
						$total_reserve_stock=production_reserve_stock_p_id_wise($dbcon,$row['product_id'],$row['purchase_unit'],$row1['p_id'],"","totaladd");
						//$total_allocate_process_qty=total_allocate_process_qty($dbcon,$p_id);
						if($row1['p_qty']>$total_reserve_stock){
							//$allocate_process_reserv_pending_qty=$row1['p_qty']-$total_reserve_stock;
							$allocate_process_reserv_pending_qty=$row['rp_req_qty']-$total_reserve_stock;
							//$allocate_process_reserv_pending_qty=$total_allocate_process_qty-$total_reserve_stock;
							
							if($product_qty>=$allocate_process_reserv_pending_qty){
								//used $allocate_process_reserv_pending_qty
								$used_qty=$allocate_process_reserv_pending_qty;
							}else{
								// used $product_qty
								$used_qty=$product_qty;
							}
							$que="select * from product_mst as ta where product_id=".$row['product_id'];
								$rs_di=$dbcon->query($que);
								$re=brp_mysqli_fetch_assoc($rs_di);
								
								if($re['product_conv_unit']==$row['purchase_unit']){
									$type="base_unit";
									$con_stock=$used_qty;
									$base_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
								}else{
									$type="conv_unit";
									$base_stock=$used_qty;
									$con_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
								}
								
							$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id from tbl_stock_trn as ta where stock_id=".$stock_id;
							$rs_di1=$dbcon->query($que1);
							$re1=brp_mysqli_fetch_assoc($rs_di1);
						
							$info_rese['reserve_date']		= date('Y-m-d',strtotime($stock_date));
							$info_rese['product_id']		= $row['product_id'];
							$info_rese['godown_id']			= $re1['godown_id'];
							$info_rese['base_unit']			= $re['product_base_unit'];
							$info_rese['base_stock']		= $base_stock;
							$info_rese['convert_unit']		= $re['product_conv_unit'];
							$info_rese['convert_stock']		= $con_stock;
							$info_rese['stock_flage']		= "1";
							$info_rese['request_id']		= $rp_id;
							$info_rese['ref_name']			= "grn";
							$info_rese['ref_id']			= "0";
							$info_rese['p_id']				= $row1['p_id'];
							$info_rese['stock_id']			= $stock_id;
							
							$info_rese['cdate']					= date("Y-m-d H:i:s");
							$info_rese['user_id']				= $_SESSION['user_id'];
							$info_rese['company_id']			= $_SESSION['company_id'];
							
							$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row1['branch_id']);
							
							
							
							if($re1['base_unit']==$re['product_base_unit']){
								$used_base_stock=$re1['base_stock']+$base_stock;
								$used_convert_stock=$re1['convert_unit']+$con_stock;
							}else{
								$used_base_stock=$re1['base_stock']+$con_stock;
								$used_convert_stock=$re1['convert_unit']+$base_stock;
							}
							
							$info_stock['used_base_stock']		= $used_base_stock;
							$info_stock['used_convert_stock']	= $used_convert_stock;
							
							$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$stock_id , $dbcon);
							
						}
					}
				}
			}
		}
	}
	
	function grn_trn_and_sub_trn_entry($dbcon,$product_id,$grn_id,$stop_qty,$product_base_unit,$process_id,$grn_godown,$p_id,$branch_id,$pid_array,$end_qty_array){
		
		$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id);
		
		
		
		$info_grn_trn['product_id']				= $product_id;
		$info_grn_trn['description']			= "";
		$info_grn_trn['grn_id']					= $grn_id;
		$info_grn_trn['product_qty']			= $stop_qty;
		$info_grn_trn['unit_id']				= $product_base_unit;
		$info_grn_trn['product_conv_qty']		= $stop_qty;
		$info_grn_trn['product_conv_unit']		= $product_base_unit;
		$info_grn_trn['process_id']				= $process_id;
		$info_grn_trn['grn_godown']				= $grn_godown;
		
		if($qc_paramter_info=="1"){
			$info_grn_trn['product_qc']				= "0";
		}
		else{
			$info_grn_trn['product_qc']				= "1";
		}
		
		$info_grn_trn['cdate']					= date("Y-m-d H:i:s");
		$info_grn_trn['user_id']				= $_SESSION['user_id'];
		$info_grn_trn['company_id']				= $_SESSION['company_id'];
		
		$grn_trn_id=add_record('tbl_grn_trn',$info_grn_trn,$dbcon,$branch_id);
		
		$acount=count($pid_array);
		
		if($acount>0){
			for($i=0;$i<count($pid_array);$i++)
			{
				$end_pid_qty=$end_qty_array[$i];
				$query = "select job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id from tbl_job_work_sub_trn as job_sub_trn
				left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
				where job_sub_trn.grn_complete_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id in (".$pid_array[$i].")" ;
				$result=$dbcon->query($query);
				$cnt=brp_mysqli_num_rows($result);
				if($cnt>0){
					while($row=brp_mysqli_fetch_array($result))
					{
						$job_work_sub_trn_grn_pending_qty_raw=job_work_sub_trn_grn_pending_qty($dbcon,$row['job_work_sub_trn_id']);
						if($end_pid_qty>=$job_work_sub_trn_grn_pending_qty_raw){
							$job_work_sub_trn_grn_pending_qty=$job_work_sub_trn_grn_pending_qty_raw;
							$end_pid_qty=$end_pid_qty-$job_work_sub_trn_grn_pending_qty_raw;
						}else{
							$job_work_sub_trn_grn_pending_qty=$end_pid_qty;
							$end_pid_qty=$end_pid_qty-$end_pid_qty;
						}
						
						if($job_work_sub_trn_grn_pending_qty!="" && $job_work_sub_trn_grn_pending_qty!="0" && $stop_qty!="0"){
							
							if($job_work_sub_trn_grn_pending_qty<=$stop_qty){
								//use $job_work_sub_trn_grn_pending_qty
								$used_qty=$job_work_sub_trn_grn_pending_qty;
							}else{
								//use $stop_qty
								$used_qty=$stop_qty;
							}
							if($used_qty>"0"){
								$stop_qty=$stop_qty-$used_qty;
								
								//tbl_grn_sub_trn entry start
									$info_grn_sub_trn['product_id']				= $row['product_id'];
									$info_grn_sub_trn['grn_trn_id']				= $grn_trn_id;
									$info_grn_sub_trn['jobwork_id']				= $row['job_work_id'];
									$info_grn_sub_trn['job_work_trn_id']		= $row['job_work_trn_id'];
									$info_grn_sub_trn['job_work_sub_trn_id']	= $row['job_work_sub_trn_id'];
									$info_grn_sub_trn['product_qty']			= $used_qty;
									$info_grn_sub_trn['product_base_unit']		= $row['product_base_unit'];
									$info_grn_sub_trn['product_conv_qty']		= $used_qty;
									$info_grn_sub_trn['product_conv_unit']		= $row['product_base_unit'];
									
									$info_grn_sub_trn['cdate']					= date("Y-m-d H:i:s");
									$info_grn_sub_trn['user_id']				= $_SESSION['user_id'];
									$info_grn_sub_trn['company_id']				= $_SESSION['company_id'];
									
									$grn_trn_sub_id=add_record('tbl_grn_sub_trn',$info_grn_sub_trn,$dbcon,$branch_id);
								//tbl_grn_sub_trn entry end
								
								//update job work grn complite/not complite start
									grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id']);
								//update job work grn complite/not complite end
								
								//allocate process trn entry start
								
									$allocate_trn_stop_qty=allocate_process_trn_stop_entry_start_entry_wise($dbcon,$used_qty,$row['p_id']);
								
								//allocate process trn entry end
								
								
								//allocate process table pen_qty update start 
									
									tbl_allocate_process_update_pen_qty($dbcon,$row['p_id'],$allocate_trn_stop_qty);
									
								//allocate process table pen_qty update end
								
								//allocate process pstatus update start
								
									tbl_allocate_process_update_p_status($dbcon,$row['p_id']);
									
								//allocate process pstatus update start
									
								//stock deduct start
									grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info);
								//stock deduct end 
							}
							
						}
					}
				}

			}
		}else{
			$query = "select job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id from tbl_job_work_sub_trn as job_sub_trn
					left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
					where job_sub_trn.grn_complete_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id in (".$p_id.")" ;
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);
			if($cnt>0){
				while($row=brp_mysqli_fetch_array($result))
				{
					$job_work_sub_trn_grn_pending_qty=job_work_sub_trn_grn_pending_qty($dbcon,$row['job_work_sub_trn_id']);
					
					if($job_work_sub_trn_grn_pending_qty!="" && $job_work_sub_trn_grn_pending_qty!="0" && $stop_qty!="0"){
						
						if($job_work_sub_trn_grn_pending_qty<=$stop_qty){
							//use $job_work_sub_trn_grn_pending_qty
							$used_qty=$job_work_sub_trn_grn_pending_qty;
						}else{
							//use $stop_qty
							$used_qty=$stop_qty;
						}
						if($used_qty>"0"){
							$stop_qty=$stop_qty-$used_qty;
							
							//tbl_grn_sub_trn entry start
								$info_grn_sub_trn['product_id']				= $row['product_id'];
								$info_grn_sub_trn['grn_trn_id']				= $grn_trn_id;
								$info_grn_sub_trn['jobwork_id']				= $row['job_work_id'];
								$info_grn_sub_trn['job_work_trn_id']		= $row['job_work_trn_id'];
								$info_grn_sub_trn['job_work_sub_trn_id']	= $row['job_work_sub_trn_id'];
								$info_grn_sub_trn['product_qty']			= $used_qty;
								$info_grn_sub_trn['product_base_unit']		= $row['product_base_unit'];
								$info_grn_sub_trn['product_conv_qty']		= $used_qty;
								$info_grn_sub_trn['product_conv_unit']		= $row['product_base_unit'];
								
								$info_grn_sub_trn['cdate']					= date("Y-m-d H:i:s");
								$info_grn_sub_trn['user_id']				= $_SESSION['user_id'];
								$info_grn_sub_trn['company_id']				= $_SESSION['company_id'];
								
								$grn_trn_sub_id=add_record('tbl_grn_sub_trn',$info_grn_sub_trn,$dbcon,$branch_id);
							//tbl_grn_sub_trn entry end
							
							//update job work grn complite/not complite start
								grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id']);
							//update job work grn complite/not complite end
							
							//allocate process trn entry start
							
								$allocate_trn_stop_qty=allocate_process_trn_stop_entry_start_entry_wise($dbcon,$used_qty,$row['p_id']);
							
							//allocate process trn entry end
							
							
							//allocate process table pen_qty update start 
								
								tbl_allocate_process_update_pen_qty($dbcon,$row['p_id'],$allocate_trn_stop_qty);
								
							//allocate process table pen_qty update end
							
							//allocate process pstatus update start
							
								tbl_allocate_process_update_p_status($dbcon,$row['p_id']);
								
							//allocate process pstatus update start
								
							//stock deduct start
								grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info);
							//stock deduct end 
						}
						
					}
				}
			}
		}
	}
	
	 function job_work_product_for_pending_grn($dbcon,$vender_id,$jobwork_trn_id){
		$str = '';
		if(!empty($jobwork_trn_id)){
			$job_where=" and job_trn.job_work_trn_id in (".$jobwork_trn_id.")";
		}
		$query = "select sum(job_sub_trn.product_base_qty) as job_qty,GROUP_CONCAT(job_sub_trn.p_id) as p_id,GROUP_CONCAT(job_sub_trn.job_work_sub_trn_id) as job_sub_trn_id,job_trn.product_id,job_trn.process_id,job_trn.product_base_unit,promst.product_type,promst.product_name,process_ms.process_name,job_trn.qc_id from tbl_job_work as job
					left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
					left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id
					left join product_mst as promst on promst.product_id=job_trn.product_id
					left join process_mst as process_ms on process_ms.process_id=job_trn.process_id
					where job.grn_complete_status=0 and job.job_work_type in (2,4) and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.grn_complete_status=0 and job_trn.grn_complete_status=0 and job.vender_id=".$vender_id." ".$job_where." group by job_trn.process_id,job_trn.product_id,job.job_work_type,job_trn.qc_id" ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$i=1;
			while($row=brp_mysqli_fetch_array($result))
			{
				$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id']);
				if($qc_paramter_info=='1')
				{
					$qc_st="yes";
					$sty="display:none;";
				}else{
					$qc_st="no";
					$sty="";
				}
				if(!empty($row['qc_id'])){
					$reprocess=" -Re Process";
				}
				$used_qty=jobwork_used_qty_using($dbcon,$row['job_sub_trn_id'],$job_work_trn_id,$job_work_id);
				$pending_qty=$row['job_qty']-$used_qty;
				
				if($pending_qty>0){
					 $str .="<tr>";
						$str .="<td>".$cnt."</td>
								
								<td>".$row['product_name']." (".$row['process_name']." ".$reprocess.")</td>
								<td>".$row['product_name']." </td>
								<td>".$row['job_qty']." </td>
								<td>".$pending_qty." </td>
								<td>
									<input type='text' class='form-control' max='".$pending_qty."' name='grn_qty[]' id='grn_qty$i' value='".$pending_qty."' /> 
								</td>
								<td>
									<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$i' required >";
									$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
								$str.="</select>
								</td>
								<td></td>
								<input type='hidden' name='product_id[]' id='product_id$i' value='".$row['product_id']."' />
								<input type='hidden' name='process_id[]' id='process_id$i' value='".$row['process_id']."' />
								<input type='hidden' name='product_base_unit[]' id='product_base_unit$i' value='".$row['product_base_unit']."' />
								<input type='hidden' name='p_id[]' id='p_id$i' value='".$row['p_id']."' />
								<input type='text' name='qc_id[]' id='qc_id$i' value='".$row['qc_id']."' />
								
						";
					$str .="</tr>"; 
						
					$i++;
				}
						
			}
		}
		return $str;
	} 

	
	function jobwork_used_qty_using($dbcon,$job_sub_trn_id,$job_work_trn_id,$job_work_id){
			$where="";
			
			if(!empty($job_sub_trn_id)){
				$where.=" and grn_sub_trn.job_work_sub_trn_id in (".$job_sub_trn_id.")";
			}
			if(!empty($job_work_trn_id)){
				$where.=" and grn_sub_trn.job_work_trn_id in (".$job_work_trn_id.")";
			}
			if(!empty($job_work_id)){
				$where.=" and grn_sub_trn.jobwork_id in (".$job_work_id.")";
			}
			
			
		 $query1= "select sum(product_qty) as used_qty from tbl_grn_sub_trn as grn_sub_trn
				where grn_sub_trn.status=0 ".$where ;
			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_array($result1);
			
		return $row1['used_qty'];
		//return $query1;
		
	}
	
	function next_process_entry($dbcon,$product_qty,$unit_id,$previous_process_id,$next_process_id,$next_process_type,$next_process_priority){
		
		$query = "select p_product_id,p_ref_id,branch_id,product_version from tbl_allocate_process as allo
					where allo.p_id=".$previous_process_id ;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);
		
		$info['process_id']				= $next_process_id;
		$info['p_qty']					= $product_qty;
		$info['pen_qty']				= $product_qty;
		$info['p_status']				= "0";
		$info['task_status']			= "0";
		$info['p_ref_id']				= $row['p_ref_id'];
		$info['p_ref_type']				= "GRN";
		$info['p_product_id']			= $row['p_product_id'];
		$info['pr_process_type']		= $next_process_type;
		$info['process_priority']		= $next_process_priority;
		$info['previous_process_id']	= $previous_process_id;
		$info['process_stock']			= "0";
		$info['process_used_stock']		= "0";
		$info['process_unit']			= $unit_id;
		$info['process_type_data']		= "0";
		$info['product_version']		= $row['product_version'];
				
		$info['cdate']					= date("Y-m-d H:i:s");
		$info['user_id']				= $_SESSION['user_id'];
		$info['company_id']				= $_SESSION['company_id'];
		
		$p_id=add_record('tbl_allocate_process',$info, $dbcon,$row['branch_id']);
		
	return $p_id;
		
	}
	
	function production_reserve_add_process_stock($dbcon,$product_qty,$unit_id,$p_id,$process_stock_id,$stock_date,$ref_name,$ref_id){
		
		$query = "select product_id,process_id,godown_id,branch_id from tbl_process_stock_trn as allo_mat
					where allo_mat.process_stock_id=".$process_stock_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
		
			$que="select * from product_mst as ta where product_id=".$row['product_id'];
			$rs_di=$dbcon->query($que);
			$re=brp_mysqli_fetch_assoc($rs_di);
			
			if($re['product_conv_unit']==$unit_id){
				$type="base_unit";
				$con_stock=$product_qty;
				$base_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
			}else{
				$type="conv_unit";
				$base_stock=$product_qty;
				$con_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
			}
			
			$info_stockadd['process_reserve_date']		= date("Y-m-d",strtotime($stock_date));
			$info_stockadd['product_id']				= $row['product_id'];
			$info_stockadd['process_id']				= $row['process_id'];
			$info_stockadd['base_stock']				= $base_stock;
			$info_stockadd['base_unit']					= $re['product_base_unit'];
			$info_stockadd['conv_stock']				= $con_stock;
			$info_stockadd['conv_unit']					= $re['product_conv_unit'];
			$info_stockadd['stock_flage']				= "1";
			$info_stockadd['godown_id']					= $row['godown_id'];
			$info_stockadd['ref_name']					= $ref_name;
			$info_stockadd['ref_id']					= $ref_id;
			$info_stockadd['process_stock_id']			= $process_stock_id;
			$info_stockadd['p_id']						= $p_id;
			$info_stockadd['stock_status']				= "0";
			$info_stockadd['cdate']						= date("Y-m-d H:i:s");
			$info_stockadd['user_id']					= $_SESSION['user_id'];
			$info_stockadd['company_id']				= $_SESSION['company_id'];
			
			$process_reserve_id=add_record('tbl_process_reserve_stock',$info_stockadd, $dbcon,$row['branch_id']);
			
		}
		
		return $process_reserve_id;
	}
	
	function production_deduct_process_reserve_stock($dbcon,$product_qty,$unit_id,$p_id,$ref_id,$ref_name,$stock_date){
		
		$query = "select process_reserve_id,product_id,process_id,branch_id,process_stock_id,godown_id from tbl_process_reserve_stock as allo_mat
				where allo_mat.stock_status=0 and stock_flage=1 and allo_mat.p_id=".$p_id;
		$result=$dbcon->query($query);
		while($row=brp_mysqli_fetch_array($result)){
			if($product_qty>"0"){
				$reserve_stock=production_process_reseve_stock($dbcon,$unit_id,$row['branch_id'],$p_id,$row['product_id'],$row['process_id'],$row['process_reserve_id'],$row['process_stock_id']);
				if($product_qty>=$reserve_stock){
					$used_qty=$reserve_stock;
				}else{
					$used_qty=$product_qty;
				}
				
				$product_qty=$product_qty-$used_qty;
				
				if($used_qty>"0"){
					$que="select * from product_mst as ta where product_id=".$row['product_id'];
					$rs_di=$dbcon->query($que);
					$re=brp_mysqli_fetch_assoc($rs_di);
					
					if($re['product_conv_unit']==$unit_id){
						$type="base_unit";
						$con_stock=$used_qty;
						$base_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
					}else{
						$type="conv_unit";
						$base_stock=$product_qty;
						$con_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
					}
				
					
					$info_stockadd['process_reserve_date']		= date("Y-m-d",strtotime($stock_date));
					$info_stockadd['product_id']				= $row['product_id'];
					$info_stockadd['process_id']				= $row['process_id'];
					$info_stockadd['base_stock']				= $base_stock;
					$info_stockadd['base_unit']					= $re['product_base_unit'];
					$info_stockadd['conv_stock']				= $con_stock;
					$info_stockadd['conv_unit']					= $re['product_conv_unit'];
					$info_stockadd['stock_flage']				= "2";
					$info_stockadd['godown_id']					= $row['godown_id'];
					$info_stockadd['ref_name']					= $ref_name;
					$info_stockadd['ref_id']					= $ref_id;
					$info_stockadd['process_stock_id']			= $row['process_stock_id'];
					$info_stockadd['p_id']						= $p_id;
					$info_stockadd['stock_status']				= "0";
					$info_stockadd['cdate']						= date("Y-m-d H:i:s");
					$info_stockadd['user_id']					= $_SESSION['user_id'];
					$info_stockadd['company_id']				= $_SESSION['company_id'];
					
					$process_reserve_id=add_record('tbl_process_reserve_stock',$info_stockadd, $dbcon,$row['branch_id']);
					
					production_deduct_process_stock($dbcon,$info_stockadd['base_stock'],$info_stockadd['base_unit'],$info_stockadd['p_id'],$info_stockadd['process_reserve_date'],$info_stockadd['godown_id'],$info_stockadd['ref_name'],$info_stockadd['ref_id'],$info_stockadd['process_stock_id'],$process_reserve_id);
				}
			}
		}
	}
	
	function production_process_reseve_stock($dbcon,$unit_id,$branch_id,$p_id,$product_id,$process_id,$process_reserve_id,$process_stock_id){
		
		/* if(!empty($reserve_id)){
			$rwhser=" and reserve_id=".$reserve_id;
			$rwhser22=" and ref_id=".$reserve_id;
		} */
		
		if(!empty($p_id)){
			$where_p_id=" and p_id=".$p_id;	
		}
		
		if(!empty($process_reserve_id)){
			$where_process_reserve_id_add=" and process_reserve_id=".$process_reserve_id;	
			$where_process_reserve_id_deduct=" and perent_id=".$process_reserve_id;	
		}
		
		if(!empty($branch_id)){
			$where_branch=" and branch_id=".$branch_id;	
		}
		
		if(!empty($process_stock_id)){
			$where_process_stock_id=" and process_stock_id=".$process_stock_id;
		}
		
		$query1="select sum(base_stock) as base_addqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=1 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id." and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($result1);
		
		$query2="select sum(convert_stock) as conv_addqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=convert_unit and stock_flage=1 ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id." and company_id=".$_SESSION['company_id']." and convert_unit=".$unit_id." and product_id=".$product_id;
		$result2=$dbcon->query($query2);
		$row2=brp_mysqli_fetch_assoc($result2);
		
		$query3="select sum(base_stock) as base_usedqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_deduct." ".$where_process_stock_id." and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result3=$dbcon->query($query3);
		$row3=brp_mysqli_fetch_assoc($result3);
		
		$query4="select sum(convert_stock) as conv_usedqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=convert_unit ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_deduct." ".$where_process_stock_id." and company_id=".$_SESSION['company_id']." and stock_flage=2 and convert_unit=".$unit_id." and product_id=".$product_id;
		$result4=$dbcon->query($query4);
		$row4=brp_mysqli_fetch_assoc($result4);
		
		$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
		
		return $res_qty;
		
	}
	
	function purchase_order_product_for_pending_grn($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
	{
		$str='';
		if(!empty($eid)){
			$grn_ids=" and grn_id!=".$eid;
		}
		if(!empty($vender_id)){
			$ven=" and op.vender_id=".$vender_id;
		}
		if(!empty($id)){
			$po=" and po.purchaseorder_id=".$id;
		}
		$branch_where=" and po.branch_id=".$branch_id;
		//$branch_where=" and branch_id=".$branch_id;
		$query="select po.*,sum(po.product_qty)as produ_qty,sum(po.product_conv_qty)as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as trn_id,group_concat(po.po_ref_id ORDER BY po.po_ref_id DESC) as ref_id,con_unit.unit_name as conv_unit_name from tbl_purchaseordertrn as po 
		left join product_mst as p on p.product_id=po.product_id
		left join tbl_category as tc on p.product_category=tc.cat_id 
		left join unit_mst as unit on unit.unitid=po.unit_id
		left join unit_mst as con_unit on con_unit.unitid=po.conv_unit_id
		left join tbl_purchaseorder as op on op.purchaseorder_id=po.purchaseorder_id
		where op.po_approval_status=1 and po.used_status=0 and purchaseordertrn_status=0 ".$branch_where." ".$ven." ".$po." group by po.product_id,po.unit_id,po.conv_unit_id";
		$rs_product=$dbcon->query($query);
		$cnt=1;
		while($row=brp_mysqli_fetch_array($rs_product))
		{
			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$query1="select sum(product_qty) as done_qty,sum(product_conv_qty) as conv_done_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id in (".$row['trn_id'].")";
			$rs_product1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_array($rs_product1);
			
			$pending_qty=$row['produ_qty']-$row1['done_qty'];
			$pending_conv_qty=$row['produ_con_qty']-$row1['conv_done_qty'];

			/*
				Code By Umair
				Comment: Below code is commented and updating new code to check qc parameter added or not according to pathik
				Date: 27/03/2021
			*/

			/*$pr_setting=get_pro_field($dbcon,$row['product_id'],'product_setting_check');
			$pr_setting_arr=explode(",",$pr_setting);
			if(in_array("product_qc",$pr_setting_arr))
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}*/
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id']);
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}

			if(!empty($eid)){
				$query11="select * from tbl_grn_trn as mst
				where mst.grn_id=".$eid." and product_id=".$row['product_id']." and purchaseorder_id=".$row['purchaseorder_id'];
				$rol=mysqli_fetch_assoc($dbcon->query($query11));
				
				if($rol['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}
			}
			$tolerance=get_pro_field($dbcon,$row['product_id'],'tolerance');
			$maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
			$minimum_tolerance=get_pro_field($dbcon,$row['product_id'],'minimum_tolerance');
			if($tolerance=="1"){
				// $maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
				$pending_qty1=$pending_qty;
			}else{
				$pending_qty1=$pending_qty;
			}
			/* Code By Umair: 29/10/2020 
			   Comment: I have removed the max value from the input tag for tolerance functionality for grn module.	
			   ".$pending_qty1."
			*/
			
			
			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
						<td>".$row['product_name']."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
						<td>".$cat_name."</td>
						<td>
							<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".number_format($row['produ_con_qty'],4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";
							 if($row["unit_id"]!=$row["conv_unit_id"]){ 
							
								$str.="</br>
									<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
								".number_format($row['produ_qty'],4,".","")." </br> <span>".$row['unit_name']."</span> 
								</div>";
							 } 
						$str.="</td>
						<td>
							
							<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".number_format($pending_conv_qty,4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";
							
							if($row["unit_id"]!=$row["conv_unit_id"]){
							
								$str.="</br>
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
									".number_format($pending_qty,4,".","")." </br> ".$row['unit_name']." 
								</div>";
							}
						$str.="<td>
							
							<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".$pending_qty1."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['conv_unit_name']."
							
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
						
						if($row["unit_id"]!=$row["conv_unit_id"]){
							$str.="<br/>
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input type='number' class='form-control'  name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['unit_name']."
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						}else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";*/
							
							$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
						}
							
						$str.="</td>
						<!--<td>
							<input type='number' min='0' max='' data-pendingqty='".$pending_qty1."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td>
							<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
							$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
							$str.="</select>
							<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
							<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
							<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
							<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
							
						</td>
						<td>
							<button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button>
						</td>
					</tr>";
			
			$cnt++;	
		}
		
		return $str;
	}

	
	// Maulik
	function purchaseorder_delivery_datewise_used_qty_update($dbcon,$purchaseordertrn_id,$base_stock,$product_base_unit){
		$query = "select po_delivery_date_id,(product_qty-used_qty) as pending_qty, used_qty,product_qty from tbl_purchaseorder_delivery_date where po_delivery_date_status=0 and grn_status=0 and  purchaseordertrn_id=".$purchaseordertrn_id;
		$result=$dbcon->query($query);
		while($row=brp_mysqli_fetch_assoc($result)){
			$used_qty=0;
			if($base_stock>0){
				if($base_stock>=$row['pending_qty']){
					//used $row['pending_qty']
					$used_qty=$row['pending_qty'];
					
				}else{
					//$base_stock used
					$used_qty=$base_stock;
				}
				//return $used_qty;
				$base_stock=$base_stock-$used_qty;
				$info2['used_qty'] = $row['used_qty']+$used_qty;

				if($info2['used_qty'] == $row['product_qty']){
					$info2['grn_status'] = 1;
				}
				update_record('tbl_purchaseorder_delivery_date', $info2,"po_delivery_date_id=".$row['po_delivery_date_id'] , $dbcon);
			}
			
		}
	}
?>
