<?php


function store_production_start_count_using_p_id($dbcon,$pid,$store=0,$from=""){
	$company_config = getCompanyConfiguration($dbcon);
	$query="select p_status,p_id,p_product_id as product_id,p_qty as actual_qty,previous_process_id,process_unit,start_qty,process_id,extra_stock_material_reserve,extra_stock,p_qty from tbl_allocate_process 
	where p_id IN (".$pid.")";

	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	$total_working_qty=0;
	$total_request_qty=0;
	$total_release_qty=0;
	
	if($cnt>0){
		while($row=brp_mysqli_fetch_assoc($result)){

			$s_ql = "select IFNULL(sum(base_qty),0) as total_qty,IFNULL(sum(release_qty),0) as total_release_qty from tbl_store_request 			
			where company_id=".$_SESSION['company_id']." and store_request_status != 2 and  p_id IN (".$row['p_id'].")" ;
		// echo $s_ql;
			$q=$dbcon->query($s_ql);
			$rel=brp_mysqli_fetch_array($q);
			$total_request_qty=$total_request_qty+$rel['total_qty'];
			$total_release_qty=$total_release_qty + $rel['total_release_qty'];
			$working_qty=0;
			$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
			
			if($company_config['extra_stock'] == '1' && $row['extra_stock'] == '1'  && $row['extra_stock_material_reserve'] == '1'){
				// if($from == ""){
					$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
					$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
					$start_qty=$total_start_qty-$total_end_qty;
					$working_qty = $row['p_qty'];
					$working_qty = $working_qty - $start_qty;
					$total_working_qty = $total_working_qty + $working_qty;
				// }
			}else{
				if($row['p_status']=="1"){
						//check working qty if process running. (its use process stop time)
					$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
					$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
					$start_qty=$total_start_qty-$total_end_qty;

					if($row['previous_process_id']=="0"){
						$matirial_available_qty=check_row_material_availability($dbcon,$row['p_id'],0);
							// if($matirial_available_qty>$start_qty){
						$working_qty=$matirial_available_qty;
								// echo $working_qty . "<";
							// }
					}else{
							//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
						$process_start_pending_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id,0);


						if($process_start_pending_qty>$start_qty){
							$working_qty=$process_start_pending_qty-$start_qty;
						}
					}
				}else if($row['previous_process_id']=="0"){
						//check material availability when this is first process 
					$working_qty=check_row_material_availability($dbcon,$row['p_id'],0);
				}else{
						//$working_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
					$working_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id,0);
				}
		
				if($row['p_status']=="1" && $row['previous_process_id'] != '0'){
					$total_working_qty=$total_working_qty+$working_qty+$total_end_qty+$total_release_qty;
				}else{
					$total_working_qty=$total_working_qty+$working_qty;
						//echo $working_qty;echo "</br>";
					if($row['p_status']!="1"){
						$total_request_qty=$total_request_qty-$row['start_qty'];	
					}else{
						$total_working_qty=$total_working_qty+$total_end_qty;	
					}
				}
				// echo " p_id :: " . $row['p_id'] ." :: " .$total_working_qty . " :: " .$total_request_qty. ">></br>";
				}
				// $total_working_qty=$total_working_qty+$working_qty+$total_end_qty;
				// echo $total_working_qty . ">>";
				// $total_working_qty=$total_working_qty+$working_qty+$total_end_qty;

		}
	} 
		// echo $total_request_qty ."<".$total_working_qty;
		// $value = $total_working_qty-$total_request_qty;

	$value = $total_working_qty-$total_request_qty;
	$rounded_value = round($value, 3); 
	return ($rounded_value);
}

function production_start_count_using_p_id($dbcon,$pid,$is_store_approval,$batch=0){

	// $query="select p_status,p_id,p_product_id as product_id,p_qty as actual_qty,previous_process_id,process_unit from tbl_allocate_process 
	// where p_id IN (".$pid.")";

	$company_config = getCompanyConfiguration($dbcon);
	$query="select extra_stock,p_status,p_id,p_product_id as product_id,p_qty as actual_qty,previous_process_id,process_unit,start_qty,process_id,extra_stock_material_reserve,extra_stock,p_qty,batch_no from tbl_allocate_process 
	where p_id IN (".$pid.")";

	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	$total_working_qty=0;
	if($cnt>0){
		while($row=brp_mysqli_fetch_assoc($result)){
			$working_qty=0;
			$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
			
			if($company_config['extra_stock'] == '1' && $row['extra_stock'] == '1'  && $row['extra_stock_material_reserve'] == '1'){
				
				if(($batch == '1' && empty($row['batch_no'])) || !empty($row['batch_no'])){
					$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
					$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
					$start_qty=$total_start_qty-$total_end_qty;
					$working_qty = $row['actual_qty'];
					$working_qty = $working_qty - $start_qty;
					$total_working_qty = $total_working_qty + $working_qty;
					// var_dump($total_working_qty);
				}
			}else{
			if($row['p_status']=="1"){
					//check working qty if process running. (its use process stop time)
				$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
				$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
				$start_qty=$total_start_qty-$total_end_qty;

				if($row['previous_process_id']=="0"){
					$matirial_available_qty=check_row_material_availability($dbcon,$row['p_id'],$is_store_approval);
					if($matirial_available_qty>$start_qty){
						$working_qty=$matirial_available_qty-$start_qty;
					} else{
                          $working_qty=$matirial_available_qty;
                    }
				}else{
						//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
					$process_start_pending_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id,$is_store_approval);
					if($process_start_pending_qty>$start_qty){
						$working_qty=$process_start_pending_qty-$start_qty;
					}
				}

			}else if($row['previous_process_id']=="0"){
					//check material availability when this is first process 
				$working_qty=check_row_material_availability($dbcon,$row['p_id'],$is_store_approval);
			}else{
					//$working_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
				$working_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id,$is_store_approval);

			}

			if($is_store_approval){
				$total_working_qty=$total_working_qty+$working_qty - $total_end_qty;
			}else{
				$total_working_qty=$total_working_qty+$working_qty;	
			}
		}

		}
	} 

	$value = $total_working_qty;
	$rounded_value = round($value, 3); 

	$company_config = getCompanyConfiguration($dbcon);
	if($company_config['round_up_qty'] == '1'){
	 	return round($rounded_value);
	 }else{
		return $rounded_value;	
	 }
	
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

	$company_config = getCompanyConfiguration($dbcon);
	if($company_config['round_up_qty'] == '1'){
	 	return round($total_working_qty);
	 }else{
		return $total_working_qty;	
	 }
	// return $total_working_qty;
}


function total_process_transaction_qty($dbcon,$type,$pid){

	$query="select IFNULL(sum(pt_qty),0) as return_qty,IFNULL(sum(variation_qty_plus),0) as variation_qty_plus, IFNULL(sum(variation_qty_minus),0) as variation_qty_minus from tbl_allocate_process_trn 
	where pt_alloc_id in (".$pid.") and p_status=".$type;
	$result=$dbcon->query($query);
	$row=brp_mysqli_fetch_assoc($result);

	if($type == 1){
		return ($row['return_qty'] + $row['variation_qty_minus']);
	}else{
		return $row['return_qty'];
	}
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

				$query_1="select req_qty_one,rp_pid as product_id,process_unit as unit_id,rp_id,branch_id,customer_id from tbl_request_product 
				where status=0 and perent_id=".$row['p_ref_id'];
				$result_1=$dbcon->query($query_1);
				$cnt_1=brp_mysqli_num_rows($result_1);

				if($cnt_1){
					$availability_material_array=array();
					while($row_1=brp_mysqli_fetch_assoc($result_1)){
						$use_one_product_qty=$row_1['req_qty_one'];
						$total_required_qty=$row['actual_qty']*$use_one_product_qty;

						$allocate_stock=reserve_stock($dbcon,$row_1['product_id'],$row_1['unit_id'],"",$row_1['rp_id'],"","",$row_1['branch_id'],$is_store_approval,$row['p_id'],"", "",$row_1['customer_id']);
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
	/*$query="select IFNULL((sum(p_qty)-sum(start_qty)),0) as total_pending_qty from tbl_allocate_process 
	where p_id in (".$p_id.")";*/
	$query="select IFNULL(sum(pen_qty),0) as total_pending_qty from tbl_allocate_process 
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

	$query="select taxinvoice_start,invoice_format,format_value,end_format_value from tbl_invoicetype where type_id=".$type_id." and company_id= ".$company_id." AND financial_year_id = ".$_SESSION['financial_year_id']."".$where_branch;
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

	$query="select taxinvoice_start,invoicetype_id from tbl_invoicetype where type_id=".$type_id." and company_id= ".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']."".$where_branch;
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

	 $company_config = getCompanyConfiguration($dbcon);	
	 $where = "";
	 if($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0){
	 	$where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
	 }
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id from tbl_allocate_process as ap
	where ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id'].$where." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
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

function store_process_wise_production_count($dbcon,$process_id,$process_type,$type){

	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}


	 $company_config = getCompanyConfiguration($dbcon);	
	 $where = "";
	 if($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0){
	 	$where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
	 }
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id from tbl_allocate_process as ap
	where  ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id'].$where." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		if($type=="1"){
			$working_qty=store_production_start_count_using_p_id($dbcon,$rel['allocate_id'],0,'store_request');
		}else{
			$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
		}
		$total_working=$total_working+$working_qty;

	}
	 
	 if($company_config['round_up_qty'] == '1'){
	 	return round($total_working);
	 }else{
		return $total_working; 	
	 }
	// return $total_working;
}


function store_process_batch_wise_production_count($dbcon,$process_id,$process_type,$type){
$company_config = getCompanyConfiguration($dbcon);	
	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id from tbl_allocate_process as ap
	where ap.batch_process_start_time = 1 and ap.batch_no ='' and ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;

	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		if($type=="1"){
			$working_qty=store_release_material_count_store_wise($dbcon,$rel['allocate_id']);
		}else{
			$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
		}
		$total_working=$total_working+$working_qty;

	}
	 
	 if($company_config['round_up_qty'] == '1'){
	 	return round($total_working);
	 }else{
		return $total_working; 	
	 }
}

function store_approve_process_wise_production_count($dbcon,$process_id,$process_type,$type,$batch=0){
	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id,IFNULL(sum(p_qty),0) as total_qty,IFNULL(sum(pen_qty),0) as total_pending,IFNULL(sum(start_qty),0) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status from tbl_allocate_process as ap

	left join product_mst as p on p.product_id=ap.p_product_id 
	left join tbl_category as tc on p.product_category=tc.cat_id
	left join branch_mst as branch on branch.branch_id=ap.branch_id
	where ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		if($type=="1"){
			$working_qty=production_start_count_using_p_id($dbcon,$rel['allocate_id'],1,$batch);
		}else{
			$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
		}
		$total_working=$total_working+$working_qty;

	}
	return $total_working;
}

function store_approve_p_id_wise_production_count($dbcon,$p_id,$process_type,$type){

	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id,IFNULL(sum(p_qty),0) as total_qty,IFNULL(sum(pen_qty),0) as total_pending,IFNULL(sum(start_qty),0) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status from tbl_allocate_process as ap

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

function job_work_sub_trn_grn_pending_qty($dbcon,$job_work_sub_trn_id,$unit_id){

	$qry = "select p.product_conv_unit from tbl_job_work_sub_trn as j left join product_mst as p on p.product_id = j.product_id  where job_work_sub_trn_id = " . $job_work_sub_trn_id;

	$p_res=$dbcon->query($qry);
	$p_row = brp_mysqli_fetch_assoc($p_res);

	if($p_row['product_conv_unit'] == $unit_id){
		$query="select IFNULL(job_sub_trn.product_con_qty,0) as jobwork_qty,IFNULL(grn_used_qty,0) as grn_used_qty, IFNULL(qty_plus,0) as qty_plus, IFNULL(qty_minus,0) as qty_minus from tbl_job_work_sub_trn as job_sub_trn 
		left join 
		(select IFNULL(sum(grn_sub_trn.product_conv_qty),0) as grn_used_qty,IFNULL(sum(grn_sub_trn.variation_qty_plus),0) as qty_plus,IFNULL(sum(grn_sub_trn.variation_qty_minus),0) as qty_minus,grn_sub_trn.job_work_sub_trn_id from tbl_grn_sub_trn as grn_sub_trn 
		where grn_sub_trn.status=0 and grn_sub_trn.company_id=".$_SESSION['company_id']."  group by grn_sub_trn.job_work_sub_trn_id) as craditpo on craditpo.job_work_sub_trn_id=job_sub_trn.job_work_sub_trn_id
		where job_sub_trn.job_work_sub_trn_id=".$job_work_sub_trn_id." group by job_sub_trn.job_work_sub_trn_id";

	}else{
		$query="select IFNULL(job_sub_trn.product_base_qty,0) as jobwork_qty,IFNULL(grn_used_qty,0) as grn_used_qty, IFNULL(qty_plus,0) as qty_plus, IFNULL(qty_minus,0) as qty_minus from tbl_job_work_sub_trn as job_sub_trn 
		left join 
		(select IFNULL(sum(grn_sub_trn.product_qty),0) as grn_used_qty,IFNULL(sum(grn_sub_trn.variation_qty_plus),0) as qty_plus,IFNULL(sum(grn_sub_trn.variation_qty_minus),0) as qty_minus,grn_sub_trn.job_work_sub_trn_id from tbl_grn_sub_trn as grn_sub_trn 
		where grn_sub_trn.status=0 and grn_sub_trn.company_id=".$_SESSION['company_id']."  group by grn_sub_trn.job_work_sub_trn_id) as craditpo on craditpo.job_work_sub_trn_id=job_sub_trn.job_work_sub_trn_id
		where job_sub_trn.job_work_sub_trn_id=".$job_work_sub_trn_id." group by job_sub_trn.job_work_sub_trn_id";

	}

	
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	$pending_qty = 0;
	if($cnt>0){
		$row=brp_mysqli_fetch_assoc($result);
		$pending_qty = $row['jobwork_qty'] - (($row['grn_used_qty'] + $row['qty_minus']) - $row['qty_plus']);
	}else{
		$pending_qty = 0;
	}
	
	return $pending_qty;
}

function grn_status_update_in_tbl_job_work_sub_trn($dbcon,$job_work_sub_trn_id,$qty_variation="",$jobcard_close="",$diff_qty_plus=0,$diff_qty_minus=0){
	$query="select job_work_trn_id,variation_qty_plus,variation_qty_minus from tbl_job_work_sub_trn as job_sub_trn
	where job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.job_work_sub_trn_id=".$job_work_sub_trn_id ;
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	if($cnt>0){
		$row=brp_mysqli_fetch_assoc($result);
			//update status start
		$job_work_sub_trn_grn_pending_qty=job_work_sub_trn_grn_pending_qty($dbcon,$job_work_sub_trn_id);
		// var_dump(' job_work_sub_trn_grn_pending_qty :: ' .$job_work_sub_trn_grn_pending_qty);
		if($job_work_sub_trn_grn_pending_qty>0){
				//update status not complete
			$info['grn_complete_status']	= "0";
		}else{
				//update status complete
			$info['grn_complete_status']	= "1";
		}
		$info['is_qty_variation']	= $qty_variation;
		$info['variation_qty_plus']	= $row['variation_qty_plus'] + $diff_qty_plus;
		$info['variation_qty_minus']	= $row['variation_qty_minus'] + $diff_qty_minus;
/*
		if(!empty($qty_variation) && !empty($qty_variation) && $qty_variation == '1' && $jobcard_close == '1'){
			$info['grn_complete_status']	= "1";
		}*/

		$updatetrnid=update_record('tbl_job_work_sub_trn',$info,"job_work_sub_trn_id=".$job_work_sub_trn_id , $dbcon);
			//update status end

			//jobwork trn grn status update
		grn_status_update_in_tbl_job_work_trn($dbcon,$row['job_work_trn_id'],$qty_variation,$jobcard_close);
	}
}

function grn_status_update_in_tbl_job_work_trn($dbcon,$job_work_trn_id,$qty_variation="",$jobcard_close=""){
	$query="select job_work_id from tbl_job_work_trn as job_trn
	where job_trn.job_work_trn_status != 2 and job_trn.job_work_trn_id=".$job_work_trn_id ;
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
		if(!empty($qty_variation) && !empty($qty_variation) && $qty_variation == '1' && $jobcard_close == '1'){
			$info['grn_complete_status']	= "1";
		}

		$updatetrnid=update_record('tbl_job_work_trn',$info,"job_work_trn_id=".$job_work_trn_id , $dbcon);

			//update status end
		grn_status_update_in_tbl_job_work($dbcon,$row['job_work_id'],$qty_variation,$jobcard_close);
	}
}

	function grn_status_update_in_tbl_job_work($dbcon,$job_work_id,$qty_variation="",$jobcard_close=""){
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
			if(!empty($qty_variation) && !empty($qty_variation) && $qty_variation == '1' && $jobcard_close == '1'){
				$info['grn_complete_status']	= "1";
			}
			
			$updatetrnid=update_record('tbl_job_work',$info,"job_work_id=".$job_work_id , $dbcon);
			
		//update status end
	}
	// addded by sanat :: 17-12-21 for reprocess
function allocate_reprocess_trn_stop_entry_start_entry_wise($dbcon,$qty,$p_id){
	$query1 = "select allocate_trn.pt_id,(allocate_trn.pt_qty-allocate_trn.pt_used_qty) pending_qty,allocate_process.p_ref_id,allocate_process.process_id,allocate_trn.pt_used_qty,allocate_process.pen_qty,allocate_process.p_product_id from tbl_allocate_re_process_trn as allocate_trn
	left join tbl_allocate_re_process as allocate_process on allocate_process.p_id=allocate_trn.pt_alloc_id
	where allocate_trn.p_status=0 and allocate_trn.pt_alloc_id=".$p_id ;
	$result1=$dbcon->query($query1);
	$cnt1=brp_mysqli_num_rows($result1);

	$return_qty=0;

	if($cnt1>0){

		while($row1=brp_mysqli_fetch_array($result1))
		{
			if($row1['pending_qty']>"0" && $qty>"0"){
				if($row1['pending_qty']<=$qty){
						//use $row1['pending_qty']
					$allocate_trn_update_qty=$row1['pending_qty'];
				}else{
						//use $qty
					$allocate_trn_update_qty=$qty;
				}

				$qty=$qty-$allocate_trn_update_qty;

				add_reprocess_trn($dbcon,$p_id,$row1['p_ref_id'],$row1['p_product_id'],$row1['process_id'],$allocate_trn_update_qty,"1","0",$row1['pt_id']);

				$allocate_trn_used_update_qty=$row1['pt_used_qty']+$allocate_trn_update_qty;

				$info_allocate_trn_used_update['pt_used_qty']	= $allocate_trn_used_update_qty;

				$updatetrnid=update_record('tbl_allocate_re_process_trn',$info_allocate_trn_used_update,"pt_id=".$row1['pt_id'] , $dbcon);

				$return_qty=$return_qty+$allocate_trn_update_qty;
			}
		}
	}

	return $return_qty;
}

function tbl_allocate_process_update_pen_qty($dbcon,$p_id,$qty,$diff_qty_plus=0,$diff_qty_minus=0){
	$query = "select pen_qty,p_ref_id,is_qty_variation,variation_qty_plus,variation_qty_minus from tbl_allocate_process as allocate_process
	where allocate_process.p_id=".$p_id ;
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	if($cnt>0){
		$row=brp_mysqli_fetch_array($result);
		$info_allocate_pen_qty_update  = array();
		$allocate_pen_qty_update=$row['pen_qty']-($qty + $diff_qty_minus);
		if($diff_qty_plus > 0 || $diff_qty_minus > 0){
			$info_allocate_pen_qty_update['is_qty_variation']	= 1;
			$info_allocate_pen_qty_update['variation_qty_plus']	= $row['variation_qty_plus']+ $diff_qty_plus;
			$info_allocate_pen_qty_update['variation_qty_minus']	= $row['variation_qty_minus']+ $diff_qty_minus;


			$qry = "select is_qty_variation,variation_qty_plus,variation_qty_minus FROM tbl_request_product where rp_id = " . $row['p_ref_id'];

			$rel = brp_mysqli_fetch_assoc($dbcon->query($qry));

			$rp_info['is_qty_variation']	= 1;
			$rp_info['variation_qty_plus']	= $rel['variation_qty_plus']+ $diff_qty_plus;
			$rp_info['variation_qty_minus']	= $rel['variation_qty_minus']+ $diff_qty_minus;

			$updatetrnid1=update_record('tbl_request_product',$rp_info,"rp_id=".$row['p_ref_id'], $dbcon);
		}
		
		$info_allocate_pen_qty_update['pen_qty']	= $allocate_pen_qty_update;

		$updatetrnid1=update_record('tbl_allocate_process',$info_allocate_pen_qty_update,"p_id=".$p_id , $dbcon);
	}
}
	// added by sanat : 17-12-21 for reprocess
function tbl_allocate_reprocess_update_pen_qty($dbcon,$p_id,$qty){
	$query = "select pen_qty,end_qty,start_qty from tbl_allocate_re_process as allocate_process
	where allocate_process.p_id=".$p_id ;
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	if($cnt>0){
		$row=brp_mysqli_fetch_array($result);
		$start_qty = $row['start_qty'];
		$start_qty = $start_qty - $qty;
		$end_qty = $row['end_qty'];
		$end_qty = $end_qty + $qty;
		$allocate_pen_qty_update=$row['pen_qty']-$qty;
		
		$info_allocate_pen_qty_update['pen_qty']	= $allocate_pen_qty_update;
		$info_allocate_pen_qty_update['start_qty']	= $start_qty;
		$info_allocate_pen_qty_update['end_qty']	= $end_qty;

		$updatetrnid1=update_record('tbl_allocate_re_process',$info_allocate_pen_qty_update,"p_id=".$p_id , $dbcon);
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

	// added by sanat :: 17-12-21 for reprocess

function tbl_allocate_reprocess_update_p_status($dbcon,$p_id){

	$stop_pending_qty=reprocess_end_count_using_p_id($dbcon,$p_id);

	$query = "select pen_qty from tbl_allocate_re_process as allocate_process
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

	$updatetrnid1=update_record('tbl_allocate_re_process',$info,"p_id=".$p_id , $dbcon);
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
				$workorder_process_id=$process_pr->workorder_process_id;

				if($previous_process_pid=="0" && $next_process_id=="0"){
						//echo "tets";die;
						//only one product

							//raw matirial stock deduct start
					p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$row1['p_id'],$row1['rp_id'],$row['product_qty'],$row['product_base_unit']);
							//raw matirial stock deduct end
					if(1==2){
						if($qc_permission=="0"){

								//product stock add start 
							$stock_id=add_stock($dbcon,$row['product_id'],$row['product_base_unit'],$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$row2['grn_godown'],$row['product_qty'],1,$row2['branch_id'],0,0);
								//product stock add end

								//product reserve stock start

							grn_sub_trn_wise_reserv_stock_add($dbcon,$row['product_qty'],$row['product_base_unit'],$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id);
								//product reserve stock end
							
						}

					}

				}
				else if($previous_process_pid=="0"){

						//echo "2"; die;//first process

							//raw matirial stock deduct start
					p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$row1['p_id'],$row1['rp_id'],$row['product_qty'],$row['product_base_unit']);
							//raw matirial stock deduct end
					
						//production_add_process_stock($dbcon,$row['product_qty'],$row1['p_id'],"1");
					if(1==2){
						if($qc_permission=="0"){
							
							//process stock add start
							$process_stock_id=production_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$stock_date,$row2['grn_godown'],"Grn_sub_trn",$row['grn_trn_sub_id']);
							//process stock add end
							
							//next process entry start
							$next_pid=next_process_entry($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id);
							//next process entry end
							
							//reserve process stock start
							$process_reserve_id=production_reserve_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id']);
							//reserve process stock end
						}
					}


				}else if($next_process_id=="0"){
	//echo "estt";die;
						//last process

						//process stock deduct start
							//production_add_process_stock($dbcon,$row['product_qty'],$previous_process_pid,"2");
					production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$row['grn_trn_sub_id'],"Grn_sub_trn",$stock_date);
						//process stock deduct end
					if(1==2){	
						if($qc_permission=="0"){
							//product stock add start 
							$stock_id=add_stock($dbcon,$row['product_id'],$row['product_base_unit'],$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id'],$row2['grn_godown'],$row['product_qty'],1,$row2['branch_id'],0,0);
							//product stock add end
							
							//reserve stock add start
							grn_sub_trn_wise_reserv_stock_add($dbcon,$row['product_qty'],$row['product_base_unit'],$stock_date,$row1['p_id'],$row1['rp_id'],$stock_id);
							//reserve stock add end

						}
					}
				}else{
						//middel process

						//process stock deduct start
							//production_add_process_stock($dbcon,$row['product_qty'],$previous_process_pid,"2");
							//production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$previous_process_pid,"2");
					production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$row['grn_trn_sub_id'],"Grn_sub_trn",$stock_date);
						//process stock deduct end
					if(1==2){
						if($qc_permission=="0"){
							//production_add_process_stock($dbcon,$row['product_qty'],$row1['p_id'],"1");
							//process stock add start

							$process_stock_id=production_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$stock_date,$row2['grn_godown'],"Grn_sub_trn",$row['grn_trn_sub_id']);
							//process stock add end
							
							//next process entry start
							$next_pid=next_process_entry($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id);
							//next process entry stop
							
							//reserve process stock start
							$process_reserve_id=production_reserve_add_process_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$next_pid,$process_stock_id,$stock_date,"Grn_sub_trn",$row['grn_trn_sub_id']);
							//reserve process stock end
						}
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
		where allo_process.p_id in(".$p_id.")" ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		
		$process_priority=0;
		$process_type=0;
		$process_id=0;
		$previous_process_pid=0;
		$workorder_process_id = 0;
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
			
			$query1= "select pr_process_id,process_priority,process_type,process_id from tbl_wororder_product_process as wor_pro_process
			where wor_pro_process.rp_id=".$row['p_ref_id']." and wor_pro_process.process_priority>".$row['process_priority']." ORDER BY process_priority  limit 1" ;
			$result1=$dbcon->query($query1);
			$cnt1=brp_mysqli_num_rows($result1);
			
			if($cnt1>0){
				$row1=brp_mysqli_fetch_array($result1);
				$process_priority=$row1['process_priority'];
				$process_type=$row1['process_type'];
				$process_id=$row1['process_id'];
				$workorder_process_id = $row1['pr_process_id'];
			}
			$previous_process_pid=$row['previous_process_id'];
		}
		
		$return_row['next_process_id']			=$process_id;
		$return_row['next_process_type']		=$process_type;
		$return_row['next_process_priority']	=$process_priority;
		
		$return_row['previous_process_pid']		=$previous_process_pid;
		$return_row['workorder_process_id']		=$workorder_process_id;

		return json_encode($return_row);
	}

	// added by sanat :: 17-12-21 for reprocess
	function p_id_wise_find_previous_and_next_reprocess($dbcon,$p_id,$qc_id){
		$query= "select p_id,process_priority,previous_process_id,p_ref_id from tbl_allocate_re_process as allo_process
		where qc_id = ".$qc_id." and allo_process.p_id=".$p_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		
		$process_priority=0;
		$process_type=0;
		$process_id=0;
		$previous_process_pid=0;
		
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);
			
			$query1= "select process_priority,process_type,process_id from tbl_wororder_product_reprocess as wor_pro_process
			where wor_pro_process.qc_id=".$qc_id." and wor_pro_process.process_priority>".$row['process_priority']." ORDER BY process_priority limit 1" ;
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
		$query = "select rp_id,perent_id,req_qty_one,rp_pid as product_id,branch_id,purchase_unit,process_unit,customer_id from tbl_request_product as req_product
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
				$info['unit_id']				= $row['process_unit'];
				$info['grn_trn_sub_id']			= $grn_trn_sub_id;

				$qry = "select * from tbl_reserve_stock where stock_status = 0 and stock_flage = 1 and p_id=".$p_id." and product_id = " . $row['product_id'] . " and request_id = " . $row['rp_id'];
				$result1=$dbcon->query($qry);
				$row1=brp_mysqli_fetch_array($result1);
			
				$qry1 = "select * from tbl_stock_trn where stock_status = 0 and stock_id=".$row1['stock_id'];
				$result2=$dbcon->query($qry1);
				$row2=brp_mysqli_fetch_array($result2);
				
		
				$qry2 = "select * from tbl_grn_sub_trn where grn_trn_sub_id=".$grn_trn_sub_id;
				$result3=$dbcon->query($qry2);
				$row3=brp_mysqli_fetch_array($result3);

				$rate = $row2['base_rate'];
				$conv_rate = $row2['conv_rate']; 
				$info['rate']			= $rate;
				$info['total_rate']		= $rate * $total_required_qty;

				$total_required_conv_qty = convert_stock($dbcon,$total_required_qty, $row['product_id'],"conv_unit");
				$info['conv_rate']			= $conv_rate;
				$info['total_conv_rate']		= $conv_rate * $total_required_conv_qty;
			
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];

				$process_material_id=add_record('tbl_allocate_process_material',$info, $dbcon,$row['branch_id']);


				$qry_2 = "select * from tbl_allocate_process where p_id=".$p_id;
				$result_3=$dbcon->query($qry_2);
				$row_3=brp_mysqli_fetch_array($result_3);


				$q_111 = "select trn.*,pro.product_name from tbl_workorder_direct_material_issue_trn as trn
							left join tbl_workorder_direct_material_issue as mst on mst.material_issue_id = trn.material_issue_id
						left join product_mst as pro on pro.product_id = trn.product_id
						where mst.status = 1 and trn.status = 0 and trn.flag = 0 and mst.rp_id = " . $rp_id . " and mst.process_id = " . $row_3['process_id'];
				$rel_111=$dbcon->query($q_111);
				$total_extra_rate = 0;
				if(brp_mysqli_num_rows($rel_111)>0){
					while($row_111 = brp_mysqli_fetch_assoc($rel_111)){

					$queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
					where ref_name ='workorder_direct_material_issue' and stock.ref_id in(".$row_111['material_issue_trn_id'].") and stock.stock_status = 0 and stock.stock_flage = 2 and product_id = " . $row_111['product_id'];
					$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
					
					$total_extra_rate = $relp_2['base_rate'] * $row_111['base_qty'];
					$total_extra_conv_rate = $relp_2['conv_rate'] * $row_111['conv_qty'];
					
					$upd_wo_mat['flag'] = 1;
					$updatetrnid=update_record('tbl_workorder_direct_material_issue_trn',$upd_wo_mat,"material_issue_trn_id=".$row_111['material_issue_trn_id'], $dbcon);
					}

				}

				$update_grn['material_rate'] = $row3['material_rate'] + $info['total_rate'] + $total_extra_rate;
				$update_grn['material_conv_rate'] = $row3['material_rate'] + $info['total_conv_rate']+$total_extra_conv_rate;
				$update_grn['process_pus_material_rate'] = $update_grn['material_rate'] + $row3['total_process_rate'];
				$update_grn['process_pus_material_conv_rate'] = $update_grn['material_conv_rate']+ $row3['total_process_conv_rate'];
				
				$updatetrnid=update_record('tbl_grn_sub_trn',$update_grn,"grn_trn_sub_id=".$grn_trn_sub_id , $dbcon);
			
				tbl_allocate_process_material_wise_reserve_stock_minus($dbcon,$process_material_id,$grn_trn_sub_id,"",$row['rp_id']);
				
			}
			
		}
	}
	
	function production_reserve_stock_p_id_wise($dbcon,$product_id,$unit_id,$p_id,$reserve_id,$totaladd,$godown_id="",$request_id=0,$on_floor_cond =""){

		$que="select * from product_mst as ta where product_id=".$product_id;
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);
		
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
		if(!empty($godown_id)){
			$where_godown=" and godown_id=".$godown_id;	
		}
		$whr_on_flr  = "";

		if(!empty($on_floor_cond)){
			$whr_on_flr = " AND ref_name = 'store_release' and godown_id = '".ON_FLOOR_GODOWN."' ";
		}

		if($re['product_conv_unit'] == $unit_id){
			$query1="select IFNULL(sum(convert_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and convert_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1.$whr_on_flr;

			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($result1);
			
			$query2="select IFNULL(sum(base_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and base_unit=".$unit_id." and product_id=".$product_id.$rwhser1.$whr_on_flr;
			$result2=$dbcon->query($query2);
			$row2=brp_mysqli_fetch_assoc($result2);
			if(empty($totaladd)){
				$query3="select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status=0 and stock_flage=2 and convert_unit=".$unit_id." ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1.$whr_on_flr;
				$result3=$dbcon->query($query3);
				$row3=brp_mysqli_fetch_assoc($result3);
				

				$query4="select IFNULL(sum(base_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status=0 and base_unit!=convert_unit ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and stock_flage=2 and base_unit=".$unit_id." and product_id=".$product_id.$rwhser1.$whr_on_flr;

				$result4=$dbcon->query($query4);
				$row4=brp_mysqli_fetch_assoc($result4);
			
			}
			if(empty($totaladd)){
				$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
			}else{
				$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
			}
		}else{

			$query1="select IFNULL(sum(base_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1.$whr_on_flr;

			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($result1);

			$query2="select IFNULL(sum(convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and convert_unit=".$unit_id." and product_id=".$product_id.$rwhser1.$whr_on_flr;
			$result2=$dbcon->query($query2);
			$row2=brp_mysqli_fetch_assoc($result2);
			if(empty($totaladd)){
				$query3="select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1.$whr_on_flr;
				$result3=$dbcon->query($query3);
				$row3=brp_mysqli_fetch_assoc($result3);


				$query4="select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status=0 and base_unit!=convert_unit ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and stock_flage=2 and convert_unit=".$unit_id." and product_id=".$product_id.$rwhser1.$whr_on_flr;

				$result4=$dbcon->query($query4);
				$row4=brp_mysqli_fetch_assoc($result4);
			}
			if(empty($totaladd)){
				$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
			}else{
				$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
			}
		}
		
		
		return $res_qty;
		
	}
	

	function tbl_allocate_process_material_wise_reserve_stock_minus($dbcon,$process_material_id,$grn_trn_sub_id="",$godown_id="",$rp_id="",$ref_id="",$ref_name=""){

		 $query = "select product_id,allocate_process_id as p_id,total_req_qty,unit_id,grn_trn_sub_id,remark,used_qty from tbl_allocate_process_material as allo_mat
					where allo_mat.process_material_id=".$process_material_id ;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			
			$row=brp_mysqli_fetch_array($result);
						  
			$reserve_stock=production_reserve_stock_p_id_wise($dbcon,$row['product_id'],$row['unit_id'],$row['p_id'],"","",$godown_id,$rp_id);
			
			// $reserve_stock=production_reserve_stock_p_id_wise($dbcon,$row['product_id'],$row['unit_id'],$row['p_id'],"","",$rp_id);
			
			
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
			$updatetrnid=update_record('tbl_allocate_process_material',$info,"process_material_id=".$process_material_id, $dbcon);
			$gd_whr = "";
			if(!empty($godown_id)){
				$gd_whr = " and godown_id = " . $godown_id;
			}
			 $query1 = "select * from tbl_reserve_stock as res_stock
					where stock_status != 2 and res_stock.used_status=0 and res_stock.stock_flage=1 and res_stock.p_id=".$row['p_id'] . $gd_whr . "  and request_id = " .$rp_id . " ORDER BY reserve_id";

				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);
				if($cnt1>0){
					while($row1=brp_mysqli_fetch_array($result1)){
						
						if($used_qty > 0){

						$reservestock=production_reserve_stock_p_id_wise($dbcon,$row['product_id'],$row['unit_id'],"",$row1['reserve_id'],"",$row1['godown_id'],$row1['request_id']);

					
						if($used_qty!="0" &&  $used_qty > 0 && $reservestock!="0"){

							if($used_qty>=$reservestock){
								//used $reservestock
								$reserve_minus_stock_qty=$reservestock;
							}else{
								//used $used_qty
								$reserve_minus_stock_qty=$used_qty;
							}

							
							$used_qty = $used_qty -  $reserve_minus_stock_qty;

							
							$que="select * from product_mst as ta where product_id=".$row['product_id'];
							$rs_di=$dbcon->query($que);
							$re=brp_mysqli_fetch_assoc($rs_di);
							
							if($re['product_conv_unit']==$row['unit_id']){
								$type="base_unit";
								$con_stock=$reserve_minus_stock_qty;
								/*$base_stock=convert_stock_new($dbcon,$reserve_minus_stock_qty,$row['product_id'],$type);*/

								$base_stock = ($con_stock/$row1['convert_stock']) * $row1['base_stock'];
							}else{
								$type="conv_unit";
								$base_stock=$reserve_minus_stock_qty;
								/*$con_stock=convert_stock_new($dbcon,$reserve_minus_stock_qty,$row['product_id'],$type);*/

								$con_stock = ($base_stock/$row1['base_stock']) * $row1['convert_stock'];
							}
							
							$company_data = getCompanyConfiguration($dbcon);
							
							// if($company_data['store_approval'] == '0')
							// {
							$info_rese['reserve_date']		= date("Y-m-d");
							$info_rese['product_id']		= $row1['product_id'];
							$info_rese['godown_id']			= $row1['godown_id'];
							$info_rese['base_unit']			= $re['product_base_unit'];
							$info_rese['base_stock']		= $base_stock;
							$info_rese['convert_unit']		= $re['product_conv_unit'];
							$info_rese['convert_stock']		= $con_stock;
							$info_rese['stock_flage']		= "2";
							$info_rese['request_id']		= $row1['request_id'];

							if($ref_id == "" && $ref_name == ""){
								$info_rese['ref_name']			= "grn";
								$info_rese['ref_id']			= $row1['reserve_id'];	
							}else{
								$info_rese['ref_name']			= $ref_name;
								$info_rese['ref_id']			= $ref_id;	
							}
							
							$info_rese['perent_id']			= $row1['reserve_id'];

							$info_rese['p_id']				= $row['p_id'];
							$info_rese['stock_id']			= $row1['stock_id'];							
							$info_rese['grn_trn_sub_id']			= $row['grn_trn_sub_id'];							
							$info_rese['cdate']					= date("Y-m-d H:i:s");
							$info_rese['user_id']				= $_SESSION['user_id'];
							$info_rese['company_id']			= $_SESSION['company_id'];
							$info_rese['customer_id']			= $row1['customer_id'];
							$info_rese['perent_id']			= $row1['reserve_id'];

							$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row1['branch_id']);

							$used_res_info['used_base_stock'] =  $row1['used_base_stock'] + $base_stock;
							$used_res_info['used_convert_stock'] = $row1['used_convert_stock'] +$con_stock;

							update_record('tbl_reserve_stock',$used_res_info,"reserve_id=".$row1['reserve_id'], $dbcon);
							
							$res_used_stock = 0;
							$res_used_conv_stock = 0;

							if(!empty($row1['used_base_stock'])){
								$res_used_stock = $row1['used_base_stock'];
							}
							if(!empty($row1['used_convert_stock'])){
								$res_used_conv_stock = $row1['used_convert_stock'];
							}

							$upd_res_info['used_base_stock'] = $res_used_stock + $base_stock;
							$upd_res_info['used_convert_stock'] = $res_used_conv_stock + $con_stock;
							
							$updatetrnid=update_record('tbl_reserve_stock',$upd_res_info,"reserve_id=".$row1['reserve_id'], $dbcon);

							if($ref_id == "" && $ref_name == ""){
								add_stock($dbcon,$row1['product_id'],$re['product_base_unit'],$info_rese['reserve_date'],"Grn_sub_trn",$row['grn_trn_sub_id'],$row1['godown_id'],$base_stock,2,$row1['branch_id'],$row1['stock_id'],$reserve_id_id,$row1['customer_id']);	
							}else{
								add_stock($dbcon,$row1['product_id'],$re['product_base_unit'],$info_rese['reserve_date'],$ref_name,$ref_id,$row1['godown_id'],$base_stock,2,$row1['branch_id'],$row1['stock_id'],$reserve_id_id,$row1['customer_id']);	
							}
							
							

							/*}
							else
							{
								$date = date("Y-m-d");
								$store_receive_id = add_store_receive($dbcon,$row1['product_id'],$row1['unit_id'],$date,"Grn",0,$info_rese['godown_id'],$used_qty,"2",$branch_id);
							}*/
							/* jayesh for setting store approve or not */
							
						}
					}
					}
				} 
		}
	}

	
		function production_add_process_stock($dbcon,$product_qty,$unit_id,$p_id,$stock_date,$godown_id,$ref_name,$ref_id,$process_type="",$reprocess_p_id=""){
			$query = "select p_product_id as product_id,process_id,process_stock,branch_id,batch_no from tbl_allocate_process as allo_mat
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
				$info_stockadd['batch_no']					= $row['batch_no'];
				$info_stockadd['cdate']						= date("Y-m-d H:i:s");
				$info_stockadd['user_id']					= $_SESSION['user_id'];
				$info_stockadd['company_id']				= $_SESSION['company_id'];

				if($process_type == "reprocess"){
					$qry = "select * from tbl_allocate_re_process where p_id=".$reprocess_p_id;
					$res=$dbcon->query($qry);
					$row1=brp_mysqli_fetch_array($res);

					$process_base_rate = (float)$row1['process_pus_material_rate'] / (float)$row1['end_qty'];

					$end_conv_qty =  convert_stock($dbcon,$row1['end_qty'],$row['product_id'],"conv_unit");
					$process_conv_rate = (float)$row1['process_pus_material_conv_rate'] / (float)$end_conv_qty;
					$info_stockadd['process_base_rate'] = round_up($process_base_rate,5); 
					$info_stockadd['process_conv_rate'] = round_up($process_conv_rate,5);
					$info_stockadd['process_stock_base_rate'] = round_up($row1['process_pus_material_rate'],5); 
					$info_stockadd['process_stock_conv_rate'] = round_up($row1['process_pus_material_conv_rate'],5);
				}else{
					
					$qry = "select * from tbl_grn_sub_trn where grn_trn_sub_id =".$ref_id;
					$res=$dbcon->query($qry);
					$row1=brp_mysqli_fetch_array($res);
					
					$process_base_rate = (float)$row1['process_pus_material_rate'] / (float)$row1['product_qty'];
					$process_conv_rate = (float)$row1['process_pus_material_conv_rate'] / (float)$row1['product_conv_qty'];
					$info_stockadd['process_base_rate'] = round_up($process_base_rate,5); 
					$info_stockadd['process_conv_rate'] = round_up($process_conv_rate,5);
					$info_stockadd['process_stock_base_rate'] = round_up($row1['process_pus_material_rate'],5); 
					$info_stockadd['process_stock_conv_rate'] = round_up($row1['process_pus_material_conv_rate'],5);
				}

				$mfg_date = date("Y-m-d");
				$dt = get_exp_date_by_product($dbcon,$row['product_id'],date("d-m-Y"));
				$exp_date = date('Y-m-d',strtotime($dt));

				$info_stockadd['mfg_date'] = $mfg_date;
				$info_stockadd['exp_date'] = $exp_date; 

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


				$query_11 = "select process_id from tbl_process_stock_trn as allo_mat
			where allo_mat.process_stock_id=".$perent_id ;
			$result_11=$dbcon->query($query_11);
			$row_11 = brp_mysqli_fetch_array($result_11);
				

				$info_stockadd['process_stock_date']		= date("Y-m-d",strtotime($stock_date));
				$info_stockadd['product_id']				= $row['product_id'];
				$info_stockadd['process_id']				= $row_11['process_id'];
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

				$mfg_date = date("Y-m-d");
				$dt = get_exp_date_by_product($dbcon,$row['product_id'],date("d-m-Y"));
				$exp_date = date('Y-m-d',strtotime($dt));

				$info_stockadd['mfg_date'] = $mfg_date;
				$info_stockadd['exp_date'] = $exp_date; 

				$info_stockadd['process_base_rate']			= $row_11['process_base_rate'];
				$info_stockadd['process_conv_rate']			= $row_11['process_conv_rate'];
				$info_stockadd['process_stock_base_rate']	= (float)$base_stock * (float)$row_11['process_base_rate'];
				$info_stockadd['process_stock_conv_rate']	= (float)$con_stock * (float)$row_11['process_conv_rate'];

				$process_stock_id=add_record('tbl_process_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);
				
				$pr_used_stock =  0;
				$pr_used_conv_stock = 0;

				if(!empty($row_11['used_base_stock'])){
					$pr_used_stock = $row_11['used_base_stock'];
				}
				if(!empty($row_11['used_convert_stock'])){
					$pr_used_conv_stock = $row_11['used_convert_stock'];
				}

				$uppd_pr_stock['used_base_stock'] = $pr_used_stock + $base_stock;
				$uppd_pr_stock['used_convert_stock'] = $pr_used_conv_stock +  $con_stock;


				$updatetrnid=update_record('tbl_process_stock_trn',$uppd_pr_stock,"process_stock_id=".$perent_id, $dbcon);

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

		function grn_sub_trn_wise_reserv_stock_add($dbcon,$product_qty,$product_base_unit,$stock_date,$p_id,$rp_id,$stock_id,$customer_id=""){
			//var_dump("qq");
		/*var_dump($product_qty); 
		var_dump($product_base_unit); */
		// check perent id
		// check perent id ma allocate process ma entry ketli 6e first process ni
		// allocate process ni qty jetlu reseve stock add thayo k nai baki hoy to pending qty lavvi
		// je qty pending ave ae allocate kari deva ni
// echo "unit : " .$product_base_unit;
			$query = "select perent_id,rp_pid as product_id, rp_req_qty, purchase_unit, process_unit, customer_id, req_qty_one, sales_order_trn_id,reject_status,branch_id from tbl_request_product as req_pro

			where req_pro.rp_id=".$rp_id ;
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);

			$process_qty = 0;

		//var_dump($cnt);
		//var_dump("232");
			if($cnt>0){
				$row=brp_mysqli_fetch_array($result);

			$query_pe = "select rp_req_qty from tbl_request_product as req_pro

			where req_pro.rp_id=".$row['perent_id'] ;

			$result_pe=$dbcon->query($query_pe);
			$row_pe=brp_mysqli_fetch_array($result_pe);
				$req_used_qty=$row_pe['rp_req_qty']*$row['req_qty_one'];
				if($row['process_unit'] == $product_base_unit){
					//$process_qty = $row['rp_req_qty'];
					$process_qty = $req_used_qty;
				}else{
					$type="conv_unit";

					//$base_stock=convert_stock_new($dbcon,$row['rp_req_qty'],$row['product_id'],$type);
					$base_stock=convert_stock_new($dbcon,$req_used_qty,$row['product_id'],$type);
					$process_qty = $base_stock;
				}
				
				if($row['perent_id']=="0" && $row['sales_order_trn_id']=='0' && $row['reject_status']=='1')
				{
					
					add_request_reserve_stock_qc($dbcon,$rp_id,$product_qty,$row['process_unit'],$row['product_id'],$row['reject_status'],$row['branch_id'],$stock_id);
				}

				if($row['perent_id']!="0")
				{
					
				$query1 = "select p_id,p_qty,process_unit,p_product_id as product_id,branch_id from tbl_allocate_process as allo_pro
					where allo_pro.previous_process_id=0 and allo_pro.p_ref_id=".$row['perent_id'] ;
					$result1=$dbcon->query($query1);
					$cnt1=brp_mysqli_num_rows($result1);
					//var_dump($query1);
					if($cnt1>0){
						while($row1=brp_mysqli_fetch_array($result1)){

							$total_reserve_stock1=production_reserve_stock_p_id_wise_new($dbcon,$row['product_id'],$row['process_unit'],$row1['p_id'],"","","",$rp_id);
							$total_reserve_stock2=production_material_release_stock_p_id_wise($dbcon,$row['product_id'],$row['process_unit'],$row1['p_id'],$rp_id);
							// var_dump($total_reserve_stock1);
							// var_dump($total_reserve_stock2);
							$total_reserve_stock = $total_reserve_stock1 + $total_reserve_stock2;
							// var_dump($total_reserve_stock);
						// var_dump('total reseve stock :'.$total_reserve_stock);
						// var_dump('process qty :'.$process_qty );
						// var_dump('product qty :'.$product_qty);
						//$total_allocate_process_qty=total_allocate_process_qty($dbcon,$p_id);
							if($process_qty>$total_reserve_stock){
							//$allocate_process_reserv_pending_qty=$row1['p_qty']-$total_reserve_stock;
								$allocate_process_reserv_pending_qty=$process_qty-$total_reserve_stock;
							//$allocate_process_reserv_pending_qty=$total_allocate_process_qty-$total_reserve_stock;

								if($product_qty>=$allocate_process_reserv_pending_qty){
								//used $allocate_process_reserv_pending_qty
									$used_qty=$allocate_process_reserv_pending_qty;
								}else{
								// used $product_qty
									$used_qty=$product_qty;
								}

								$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,customer_id,ref_id,ref_name from tbl_stock_trn as ta where stock_id=".$stock_id;
								$rs_di1=$dbcon->query($que1);
								$re1=brp_mysqli_fetch_assoc($rs_di1);

							// var_dump('used_qty :: '.$used_qty);
								$que="select * from product_mst as ta where product_id=".$row['product_id'];
								$rs_di=$dbcon->query($que);
								$re=brp_mysqli_fetch_assoc($rs_di);
								
								if($re['product_conv_unit']==$re['product_base_unit']){
									$con_stock=$used_qty;
									$base_stock=$used_qty;
								}
								else if($re['product_conv_unit']==$product_base_unit){

									// echo "== 1 ==";
									$type="base_unit";
									$con_stock=$used_qty;
									// $base_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
									$base_stock=($used_qty/$re1['convert_stock'])*$re1['base_stock'];
								}else{
									// echo "== 2 ==";
									$type="conv_unit";
									$base_stock=$used_qty;
									// $con_stock=convert_stock_new($dbcon,$used_qty,$re['product_id'],$type);
									$con_stock=($used_qty/$re1['base_stock'])*$re1['convert_stock'];
								}

							/*$company_data = getCompanyConfiguration($dbcon, $id = false);
							if($company_data['store_approval'] == '0')
							{*/
								//var_dump("112");
								/*$stock_id=add_stock($dbcon,$info2s['product_id'],$info2s['unit_id'],$POST['grn_date'],"tbl_grn_trn",$tbl_grn_trn_id,$godown_id,$info2s['product_qty'],"1",$branch_id);*/
								
								$info_rese['reserve_date']		= date('Y-m-d',strtotime($stock_date));
								$info_rese['product_id']		= $row['product_id'];
								$info_rese['godown_id']			= $re1['godown_id'];
								$info_rese['base_unit']			= $re['product_base_unit'];
								$info_rese['base_stock']		= $base_stock;
								$info_rese['convert_unit']		= $re['product_conv_unit'];
								$info_rese['convert_stock']		= $con_stock;
								$info_rese['stock_flage']		= "1";
								$info_rese['request_id']		= $rp_id;
								$info_rese['ref_name']			= $re1['ref_name'];
								$info_rese['ref_id']			= $re1['ref_id'];
								$info_rese['p_id']				= $row1['p_id'];
								$info_rese['stock_id']			= $stock_id;
								$info_rese['customer_id']		= $customer_id;

								$info_rese['cdate']				= date("Y-m-d H:i:s");
								$info_rese['user_id']			= $_SESSION['user_id'];
								$info_rese['company_id']		= $_SESSION['company_id'];		
							//var_dump($info_rese);					
								$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row1['branch_id']);
							/*}
							else
							{
								$store_receive_id =  add_store_receive($dbcon,$row['product_id'],$row['process_unit'],$stock_date,"tbl_reserve_stock",0,$re1['godown_id'],$used_qty,"1",$branch_id,'','','',$row1['p_id'],$rp_id);
								
							}*/
							/* jayesh for setting store approve or not */
							
							if($re1['base_unit']==$re['product_base_unit']){
								$used_base_stock=$re1['used_base_stock']+$base_stock;
								$used_convert_stock=$re1['used_convert_stock']+$con_stock;
							}else{
								$used_base_stock=$re1['used_convert_stock']+$con_stock;
								$used_convert_stock=$re1['used_base_stock']+$base_stock;
							}
							
							$info_stock['used_base_stock']		= $used_base_stock;
							$info_stock['used_convert_stock']	= $used_convert_stock;
							
							$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$stock_id , $dbcon);
							
						}
					}
				}

			}else if($row['sales_order_trn_id']!='0'){
				$so_qry = "SELECT so.short_close_status from tbl_sales_ordertrn as so_trn
							LEFT JOIN tbl_sales_order as so ON so.sales_order_id = so_trn.sales_order_id
							where so_trn.sales_ordertrn_id = " . $row['sales_order_trn_id'];
				$so_row = brp_mysqli_fetch_assoc($dbcon->query($so_qry));

							 $que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,ta.product_id,ta.branch_id,customer_id from tbl_stock_trn as ta where stock_id=".$stock_id;
							$rs_di1=$dbcon->query($que1);
							$re1=brp_mysqli_fetch_assoc($rs_di1);

							$que="select * from product_mst as ta where product_id=".$re1['product_id'];
							$rs_di=$dbcon->query($que);
							$re=brp_mysqli_fetch_assoc($rs_di);

							if($re['product_conv_unit']==$product_base_unit){
								$type="base_unit";
								$con_stock=$product_qty;
								$base_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
							}else{
								$type="conv_unit";
								$base_stock=$product_qty;
								$con_stock=convert_stock_new($dbcon,$product_qty,$re['product_id'],$type);
							}
							
							$info_rese['reserve_date']		= date('Y-m-d',strtotime($stock_date));
							$info_rese['product_id']		= $re['product_id'];
							$info_rese['godown_id']			= $re1['godown_id'];
							$info_rese['base_unit']			= $re['product_base_unit'];
							$info_rese['base_stock']		= $base_stock;
							$info_rese['convert_unit']		= $re['product_conv_unit'];
							$info_rese['convert_stock']		= $con_stock;
							$info_rese['stock_flage']		= "1";
							$info_rese['request_id']		= $rp_id;
							$info_rese['ref_name']			= "soallocate_stock_accept";
							$info_rese['ref_id']			= "0";
							$info_rese['sales_order_trn_id']= $row['sales_order_trn_id'];
							$info_rese['stock_id']			= $stock_id;
							$info_rese['customer_id']		= $re1['customer_id'];

							$info_rese['cdate']					= date("Y-m-d H:i:s");
							$info_rese['user_id']				= $_SESSION['user_id'];
							$info_rese['company_id']			= $_SESSION['company_id'];		
								//var_dump($info_rese);
						if($so_row['short_close_status'] == '0'){
							$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$re1['branch_id']);	
						}					
			}else{
				//var_dump("fsas");
				//die();
				
							
				 $qry12="select trn.*,IFNULL(sum(trn.allocate_base_qty-allocate_base_qty_used),0) as pendingqty from wip_stock_allocate as trn where status = 0 and stock_flag=2 and rp_id=".$rp_id;
				$result=$dbcon->query($qry12);
				while($res12=brp_mysqli_fetch_assoc($result)){
					if($product_qty>0){
						if($res12['pendingqty']>=$product_qty){
							$wstock=$product_qty;
							$product_qty=$product_qty-$product_qty;
						}else{
							$wstock=$res12['pendingqty'];
							$product_qty=$product_qty-$res12['pendingqty'];
						}
						if($res12['sales_order_trn_id']==0){
								//var_dump("22");
							grn_sub_trn_wise_reserv_stock_add($dbcon,$wstock,$product_base_unit,$stock_date,$p_id,$res12['allocate_for_rp_id'],$stock_id);
							$query_invoicetype = $dbcon->query("UPDATE wip_stock_allocate SET allocate_base_qty_used = allocate_base_qty_used +".$wstock." WHERE wip_stock_allocate_id = ".$res12['wip_stock_allocate_id']);
						}else{


							$que1="select base_unit,base_stock,used_base_stock,convert_unit,convert_stock,used_convert_stock,godown_id,ta.product_id,ta.branch_id,customer_id from tbl_stock_trn as ta where stock_id=".$stock_id;
							$rs_di1=$dbcon->query($que1);
							$re1=brp_mysqli_fetch_assoc($rs_di1);

							$que="select * from product_mst as ta where product_id=".$re1['product_id'];
							$rs_di=$dbcon->query($que);
							$re=brp_mysqli_fetch_assoc($rs_di);

							if($re['product_conv_unit']==$product_base_unit){
								$type="base_unit";
								$con_stock=$wstock;
								$base_stock=convert_stock_new($dbcon,$wstock,$re['product_id'],$type);
							}else{
								$type="conv_unit";
								$base_stock=$wstock;
								$con_stock=convert_stock_new($dbcon,$wstock,$re['product_id'],$type);
							}
							
							$info_rese['reserve_date']		= date('Y-m-d',strtotime($stock_date));
							$info_rese['product_id']		= $re['product_id'];
							$info_rese['godown_id']			= $re1['godown_id'];
							$info_rese['base_unit']			= $re['product_base_unit'];
							$info_rese['base_stock']		= $base_stock;
							$info_rese['convert_unit']		= $re['product_conv_unit'];
							$info_rese['convert_stock']		= $con_stock;
							$info_rese['stock_flage']		= "1";
							$info_rese['request_id']		= $rp_id;
							$info_rese['ref_name']			= "soallocate_stock_accept";
							$info_rese['ref_id']			= "0";
							$info_rese['sales_order_trn_id']= $res12['sales_order_trn_id'];
							$info_rese['stock_id']			= $stock_id;
							$info_rese['customer_id']		= $re1['customer_id'];

							$info_rese['cdate']					= date("Y-m-d H:i:s");
							$info_rese['user_id']				= $_SESSION['user_id'];
							$info_rese['company_id']			= $_SESSION['company_id'];		
								//var_dump($info_rese);					
							$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$re1['branch_id']);
							
							$query_invoicetype = $dbcon->query("UPDATE wip_stock_allocate SET allocate_base_qty_used = allocate_base_qty_used +".$product_qty." WHERE wip_stock_allocate_id = ".$res12['wip_stock_allocate_id']);
						}

					}
				}
			}
			/*else if($row['sales_order_trn_id']!="0"){
				
				add_so_reserve_stock_production($dbcon,$rp_id,$product_qty,$unit_id,$branch_id,$stock_id);
			}*/
		}
	}
	

	function grn_trn_and_sub_trn_entry($dbcon,$product_id,$grn_id,$stop_qty,$product_base_unit,$process_id,$grn_godown,$p_id,$branch_id,$pid_array,$arr_end_qty,$job_work_po_trn_id=0,$stop_conv_qty=0,$product_conv_unit=0,$grn_no=0,$batch_no="",$batch_man_no="",$start_stop_user_id="",$ref_type="",$rate_unit="",$product_scrap_id="",$scrap_unit="",$scrap_qty="",$auto_store_relese = 0,$process_end_time_qc=0,$material_product_id="",$material_pid="",$material_used_qty="",$material_godown_action="",$material_godown_id="",$qty_variation="",$jobcard_close="",$end_qty_array="",$arr_process_stock="",$arr_rp_id="",$arr_batch_wise_stock_manage="",$vender_id=""){

		$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id,$process_id);
		
		$companyConfiguration = getCompanyConfiguration($dbcon, $id = false);

		$info_grn_trn['product_id']				= $product_id;
		$info_grn_trn['description']			= "";
		$info_grn_trn['grn_id']					= $grn_id;
		$info_grn_trn['product_qty']			= $stop_qty;
		$info_grn_trn['unit_id']				= $product_base_unit;
		$info_grn_trn['product_conv_qty']		= $stop_conv_qty;
		$info_grn_trn['product_conv_unit']		= $product_conv_unit;
		$info_grn_trn['process_id']				= $process_id;
		$info_grn_trn['grn_godown']				= $grn_godown;
		$info_grn_trn['job_work_po_trn_id']		= $job_work_po_trn_id;
		$info_grn_trn['ref_type']				= $ref_type;
		$info_grn_trn['rate_unit']				= $rate_unit;
		$info_grn_trn['product_scrap_id']		= $product_scrap_id;
		$info_grn_trn['scrap_unit']				= $scrap_unit;
		$info_grn_trn['scrap_qty']				= $scrap_qty;
		
		if($qc_paramter_info=="1"){
			$info_grn_trn['product_qc']			= "0"; // QC pending 
		}
		else{
			$info_grn_trn['product_qc']			= "1"; // QC Done
		}
		
		$info_grn_trn['cdate']					= date("Y-m-d H:i:s");
		$info_grn_trn['user_id']				= $_SESSION['user_id'];
		$info_grn_trn['company_id']				= $_SESSION['company_id'];
		
		$grn_trn_id=add_record('tbl_grn_trn',$info_grn_trn,$dbcon,$branch_id);

		if($grn_trn_id){
			if($process_end_time_qc == '1'){
				$tmp_qc_p_id =  implode(",",$pid_array);
				
				$tmp_qc['status'] = 0;
				$tmp_qc['grn_trn_id'] = $grn_trn_id;

				update_record('tbl_temp_qc', $tmp_qc,"status = 3 and process_id = " . $process_id . " and p_id in(".$tmp_qc_p_id.")", $dbcon);
			}

			if($material_pid){
				$bt_pid = implode(",",$material_pid);
				$bt_product_id = implode(",",$material_product_id);
				$batch_info_temp = array();
				$batch_info_temp['mt_id'] = $grn_trn_id;
				$batch_info_temp['status'] = 0;
				$bt_upd_id = update_record('tbl_batch_temp_material_start_time_deduct', $batch_info_temp,"status = 3 and type = 2 and p_id in(" . $bt_pid . ") and product_id in(" . $bt_product_id .")",$dbcon);
			}
		}
		/*Added by Sanat :: START  :: 19-11-21 */

		$rate_unit = $product_base_unit;

		$grn_base_qty = $stop_qty;
		$grn_base_unit = $product_base_unit;

		$grn_conv_qty = $stop_conv_qty;
		$grn_conv_unit = $product_conv_unit;

		upadte_batch_data_status($dbcon,$grn_id,$grn_trn_id,$grn_no,$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc,"","",$grn_godown); // for update batch no tempory status and add grn_id for multiple batch  
		$qry12="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and product_id=".$product_id." and order_no ='" . $grn_no."' and grn_id = " . $grn_id." and grn_trn_id=".$grn_trn_id;
		$res12=mysqli_fetch_assoc($dbcon->query($qry12));
	
		$batch_qty = $res12['qty'];
		// echo $grn_conv_qty . "ok" .  $batch_qty;
		// var_dump($grn_conv_unit);
		// var_dump($rate_unit);

		if($grn_conv_unit==$rate_unit){
			$remaining_qty = $grn_conv_qty - $batch_qty;
		}else{
			$remaining_qty = $grn_base_qty - $batch_qty;
		}

		// $batch_no = "";
		if($grn_conv_unit==$rate_unit){
			$type="base_unit";
			$conv_qty=$remaining_qty;
			$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
		}else{
			$type="conv_unit";
			$base_qty=$remaining_qty;
			$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
		}

		$batch_qty = $base_qty;
		$batch_conv_qty = $conv_qty;

		$process=p_id_wise_find_previous_and_next_process($dbcon,$p_id);
		$process_pr=json_decode($process);

		$next_process_id=$process_pr->next_process_id;

		
		// check product batch stock setting 

		$pro_qry= "select * from product_mst where product_id = " . $product_id;
		$pro_result=$dbcon->query($pro_qry);
		$pro_res=brp_mysqli_fetch_assoc($pro_result);

		if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' && $companyConfiguration['batch_process'] == '0') {
			// var_dump(1);
			if($batch_no == ""){
				$batch_no = get_batch_no($dbcon,$product_id);	
			}

		}else if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '0' && $companyConfiguration['batch_process'] == '0') {
			// var_dump(2);
			$batch_no = $batch_man_no;
		}
		else if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' && $companyConfiguration['batch_process'] == '1') {
			if($batch_no == ""){
				$batch_no = get_batch_no($dbcon,$product_id);	
			}
			// var_dump(3);
		}
		else if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '0' && $companyConfiguration['batch_process'] == '1') {
			// var_dump(4);
			$batch_no = $batch_man_no;
		}else{
			// var_dump(5);
			$batch_no = "";
		}

		if ($pro_res['batch_wise_stock_manage'] == '0') {
			$batch_no = "";	
		}

		/*if($pro_res['batch_wise_stock_manage'] == '1' && $companyConfiguration['batch_process'] == '1' && $next_process_id == 0){
			// echo " 1 <===>";
			$batch_no = $batch_no;
		}else if($pro_res['batch_wise_stock_manage'] == '1' && $companyConfiguration['batch_process'] == '0'){
			$batch_no = $batch_man_no;
			// echo " 2 <===> " . $batch_man_no . " -- ";
		}else if($pro_res['batch_wise_stock_manage'] == '0'){
			$batch_no = "";
			// echo " 3<===>";
		}else{
			$batch_no = "";
			// echo " 4 <===>";
		}*/

		// echo $batch_no. "  <===>";
		// var_dump($batch_no);

		
		$batch_info['grn_id']			= $grn_id;	
		$batch_info['grn_trn_id']		= $grn_trn_id;	
		$batch_info['batch_no']			= $batch_no;
		$batch_info['batch_qty']		= $remaining_qty;
		$batch_info['order_no']			= $grn_no;
		$batch_info['product_id']		= $product_id;
		$batch_info['grn_date']			= date('Y-m-d');
		$batch_info['batch_type']		= $companyConfiguration['batch_type'];
		$batch_info['production_type']	= '1';			
		$batch_info['status']			= '0';			
		
		$batch_info['qc_status']		= $info_grn_trn['product_qc'];
		$batch_info['grn_accept_qty']	= $remaining_qty;

		


		if($qc_paramter_info=='0'){
			$batch_info['accept_qty']	= $remaining_qty;
			$batch_info['qc_qty']		= $remaining_qty;
		}

		if($process_end_time_qc  == '1'){
			$tmp_qc_p_id =  implode(",",$pid_array);
			$qc_qry = "SELECT (SELECT IFNULL(SUM(qty),0) FROM tbl_temp_qc where status = 0 and p_id in(".$tmp_qc_p_id.") and grn_trn_id = ".$grn_trn_id." and type = 1) as accept_qty,(SELECT IFNULL(SUM(qty),0) FROM tbl_temp_qc where status = 0 and p_id in(".$tmp_qc_p_id.") and grn_trn_id = ".$grn_trn_id." and type = 2) as reject_qty,(SELECT IFNULL(SUM(qty),0) FROM tbl_temp_qc where status = 0 and p_id in(".$tmp_qc_p_id.") and grn_trn_id = ".$grn_trn_id." and type = 3) as reprocess_qty";
			$qc_result = $dbcon->query($qc_qry);
			$qc_row = brp_mysqli_fetch_assoc($qc_result);

			$batch_info['grn_reject_qty']	 = $qc_row['reject_qty'];
			$batch_info['grn_reprocess_qty'] = $qc_row['reprocess_qty'];

			$tmp_total_qc_qty = $qc_row['accept_qty'] + $qc_row['reject_qty'] + $qc_row['reprocess_qty'];
			$total_accept_qc_qty = $remaining_qty - $qc_row['reject_qty'] - $qc_row['reprocess_qty'];
			$batch_info['grn_accept_qty']	 = $total_accept_qc_qty;
			if($qc_paramter_info==0){
				$batch_info['accept_qty']	 =  $total_accept_qc_qty;
				$batch_info['reject_qty']	 = $qc_row['reject_qty'];
				$batch_info['reprocess_qty'] = $qc_row['reprocess_qty'];
				$batch_info['qc_qty']		= $remaining_qty;
			}

			if($qc_row['accept_qty'] == 0){
				$batch_info['qc_status']	= 1;
			}
		}
		$mfg_date = date("Y-m-d");
		$dt = get_exp_date_by_product($dbcon,$product_id,date("d-m-Y"));
		$exp_date = date('Y-m-d',strtotime($dt));
		
		$batch_info['mfg_date']			= $mfg_date;
		$batch_info['exp_date']			= $exp_date;
		$batch_info['cdate']			= date("Y-m-d H:i:s"); 
		$batch_info['user_id']			= $_SESSION['user_id'];
		$batch_info['company_id']		= $_SESSION['company_id'];	
		$batch_info['branch_id']		= $branch_id;
		$batch_info['batch_unit']		= $rate_unit;
		$batch_info['base_qty']			= $batch_qty;
		$batch_info['base_unit']		= $grn_base_unit;
		$batch_info['conv_qty']			= $batch_conv_qty;
		$batch_info['conv_unit']		= $grn_conv_unit;
		$batch_info['process_id']		= $process_id;
		$batch_info['grn_godown']		= $grn_godown;
		$batch_info['auto_store_relese']= $auto_store_relese;

		if($auto_store_relese == '1'){
			$batch_info['stock_approval_status']= 1;
		}else{
			$batch_info['stock_approval_status']= 0;
		}

		if($remaining_qty >  0){
			$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
			if($batch_gen_id){
				if($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_stock'] == '1' ) {
					update_batch_no($dbcon,$product_id);
				}
			}						
		}

		if($product_scrap_id != "" && $scrap_unit !="" && $scrap_qty > 0){

			$scrap_pro_qry= "select * from product_mst where product_id = " . $product_id;
			$scrap_pro_result=$dbcon->query($scrap_pro_qry);
			$scrap_pro_res=brp_mysqli_fetch_assoc($scrap_pro_result);

			$scrap_info['grn_id']			= $grn_id;	
			$scrap_info['grn_trn_id']		= $grn_trn_id;	
			$scrap_info['batch_no']			= $batch_no;
			$scrap_info['batch_qty']		= $scrap_qty;
			$scrap_info['order_no']			= $grn_no;
			$scrap_info['product_id']		= $product_scrap_id;
			// $scrap_info['grn_date']			= date('Y-m-d',strtotime($POST['grn_date']));
			$scrap_info['batch_type']		= $companyConfiguration['batch_type'];
			$scrap_info['production_type']	= '1';			
			$scrap_info['status']			= '0';			
			
			$scrap_info['qc_status']		= 1;
			
			$scrap_info['accept_qty']	= $scrap_qty;
			$scrap_info['qc_qty']		= $scrap_qty;

			$scrap_info['cdate']			= date("Y-m-d H:i:s"); 
			$scrap_info['user_id']			= $_SESSION['user_id'];
			$scrap_info['company_id']		= $_SESSION['company_id'];	

			$scrap_conv_qty = 0;
			$scrap_base_qty = 0;
			
			if($scrap_pro_res['product_conv_unit']==$scrap_unit){
				$type="base_unit";
				$scrap_conv_qty=$scrap_qty;
				$scrap_base_qty=convert_stock($dbcon,$scrap_conv_qty,$product_scrap_id,$type);
				// $scrap_base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;
					
			}else{
				$type="conv_unit";
				$scrap_base_qty=$scrap_qty;
				$scrap_conv_qty=convert_stock($dbcon,$scrap_base_qty,$product_scrap_id,$type);
				// $scrap_conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
			}

			$mfg_date = date("Y-m-d");
			
			$dt = get_exp_date_by_product($dbcon,$product_scrap_id,date("d-m-Y"));
			$exp_date = date('Y-m-d',strtotime($dt));

			$scrap_info['mfg_date']			= $mfg_date;
			$scrap_info['exp_date']			= $exp_date;

			$scrap_info['branch_id']		= $branch_id;
			$scrap_info['batch_unit']		= $scrap_unit;
			$scrap_info['base_qty']			= $scrap_base_qty;
			$scrap_info['base_unit']		= $scrap_pro_res['product_base_unit'];
			$scrap_info['conv_qty']			= $scrap_conv_qty;
			$scrap_info['conv_unit']		= $scrap_pro_res['product_conv_unit'];
			$scrap_info['is_scrap']			= 1;

			$batch_gen_id = add_record('tbl_batch_data', $scrap_info, $dbcon);	
	}

	/*Added by Sanat :: End :: 19-11-21 */
		$acount=count($pid_array);
		
		if($acount>0){
			for($i=0;$i<count($pid_array);$i++)
			{

				/* Sanat Start :: 22-06-22 :: UPDATE tbl_start_stop_production start_qty */

				if($process_end_time_qc == '1'){
					$qc_qry = "SELECT (SELECT IFNULL(SUM(qty),0) FROM tbl_temp_qc where status = 0 and p_id in(".$tmp_qc_p_id.") and grn_trn_id = ".$grn_trn_id." and type = 1) as accept_qty,(SELECT IFNULL(SUM(qty),0) FROM tbl_temp_qc where status = 0 and p_id in(".$tmp_qc_p_id.") and grn_trn_id = ".$grn_trn_id." and type = 2) as reject_qty,(SELECT IFNULL(SUM(qty),0) FROM tbl_temp_qc where status = 0 and p_id in(".$pid_array[$i].") and grn_trn_id = ".$grn_trn_id." and type = 3) as reprocess_qty";
					$qc_result = $dbcon->query($qc_qry);
					$qc_row = brp_mysqli_fetch_assoc($qc_result);
					$total_accept_qc_qty = $end_qty_array[$i] - $qc_row['reject_qty'] - $qc_row['reprocess_qty'];
				}

				$ss_qry = "select * from tbl_start_stop_production where complete_status in(0,1) and p_id = " . $pid_array[$i];
				$ss_res = $dbcon->query($ss_qry);
				$ss_end_qty = $arr_end_qty[$i];
				$ss_end_qty_actual = $end_qty_array[$i];
				$ss_qty_variation = $qty_variation[$i];
				$ss_jobcard_close = $jobcard_close[$i];

				$diff_qty_plus = 0;
				$diff_qty_minus = 0;
				if($ss_end_qty >= $ss_end_qty_actual){
					$diff_qty_minus = $ss_end_qty - $ss_end_qty_actual;
				}else{
					$diff_qty_plus = $ss_end_qty_actual - $ss_end_qty;
				}
				while($ss_row = brp_mysqli_fetch_assoc($ss_res)){
					$end_qty_ss = $ss_row['end_qty'];
					$start_qty_ss = $ss_row['start_qty'];
					$pending_qty_ss = $ss_row['start_qty'] - $ss_row['end_qty'];
					if($ss_end_qty_actual > 0){
						if($pending_qty_ss > 0){
							$ss_info = array();	

							if($ss_end_qty_actual >= $pending_qty_ss){
								$ss_info['end_qty'] = $end_qty_ss + $pending_qty_ss;	
								$ss_end_qty_actual = $ss_end_qty_actual - $pending_qty_ss;					
							}else{
								$ss_info['end_qty'] = $end_qty_ss + $ss_end_qty_actual;
								$ss_end_qty_actual = $ss_end_qty_actual - $ss_end_qty_actual;	

								if($ss_qty_variation == '1'){
									$ss_info['is_qty_variation'] = 1;
									$ss_info['variation_qty_plus'] = $ss_row['variation_qty_plus'] + $diff_qty_plus;
									$ss_info['variation_qty_minus'] = $ss_row['variation_qty_minus'] + $diff_qty_minus;

									if(($ss_info['end_qty']+ $ss_info['variation_qty_plus'] + $ss_info['variation_qty_minus']) -  $ss_row['start_qty'] == '0'){
										$ss_info['complete_status'] = 3;
									}

									$ss_info['end_qty'] = $ss_info['end_qty']  + $diff_qty_plus + $diff_qty_minus;
								}

								if($ss_qty_variation == '1' && $ss_jobcard_close  == '1'){
									$ss_info['complete_status'] = 3;
								}
							}

							// $ss_info['end_qty'] = $end_qty_ss + $ss_end_qty_actual;

							if($ss_row['pending_qty'] > 0 && ($ss_info['end_qty'] - $ss_row['start_qty'] == '0')){
								$ss_info['complete_status'] = 0;
							}else if($ss_info['end_qty'] - $ss_row['start_qty'] == '0'){
								$ss_info['complete_status'] = 3;
							}

							/*if($ss_qty_variation[$i] == '1' && $ss_jobcard_close[$i]  == '1'){
								$ss_info['complete_status'] = 3;
							}*/
							$updatetrnid=update_record('tbl_start_stop_production',$ss_info,"start_stop_id=".$ss_row['start_stop_id']." and p_id = ".$pid_array[$i], $dbcon);
						}
					}
				}

				$end_pid_qty = $arr_end_qty[$i];
				$end_pid_qty_actual=$end_qty_array[$i];
				$ss_qty_variation = $qty_variation[$i];
				$ss_jobcard_close = $jobcard_close[$i];

				$diff_qty_plus = 0;
				$diff_qty_minus = 0;
				if($end_pid_qty >= $end_pid_qty_actual){
					$diff_qty_minus = $end_pid_qty - $end_pid_qty_actual;
				}else{
					$diff_qty_plus = $end_pid_qty_actual - $end_pid_qty;
				}
				
				$jb_whr = "";

				if(!empty($vender_id)){
					$jb_whr = " and vender_id = " . $vender_id;
				}

				  $query = "select ap.extra_stock,job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.product_con_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id,job_sub_trn.customer_id,ap.p_ref_id,job_sub_trn.pr_rate,job_sub_trn.is_qty_variation from tbl_job_work_sub_trn as job_sub_trn 
				  left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id 
				  left join tbl_job_work as job on job_trn.job_work_id=job.job_work_id 
				  left join tbl_allocate_process as ap on ap.p_id=job_sub_trn.p_id where job_sub_trn.grn_complete_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id in (".$pid_array[$i].") ". $jb_whr;
			
				$result=$dbcon->query($query);
				$cnt=brp_mysqli_num_rows($result);
				if($cnt>0){
					$grn_scrap_qty = $scrap_qty / $cnt;
					while($row=brp_mysqli_fetch_array($result))
					{
						$job_work_sub_trn_grn_pending_qty_raw=job_work_sub_trn_grn_pending_qty($dbcon,$row['job_work_sub_trn_id']);

						if($ss_qty_variation == '1'){
								$info_grn_sub_trn['is_qty_variation'] = 1;
								$info_grn_sub_trn['variation_qty_plus'] = $diff_qty_plus;
								$info_grn_sub_trn['variation_qty_minus'] = $diff_qty_minus;
								
								$job_work_sub_trn_grn_pending_qty_raw=$job_work_sub_trn_grn_pending_qty_raw-$info_grn_sub_trn['variation_qty_minus'];
								$job_work_sub_trn_grn_pending_qty_raw=$job_work_sub_trn_grn_pending_qty_raw+$info_grn_sub_trn['variation_qty_plus'];
							}

						
						if($end_pid_qty_actual>=$job_work_sub_trn_grn_pending_qty_raw){
							$job_work_sub_trn_grn_pending_qty=$job_work_sub_trn_grn_pending_qty_raw;
							$end_pid_qty_actual=$end_pid_qty_actual-$job_work_sub_trn_grn_pending_qty_raw;
						}else{
							$job_work_sub_trn_grn_pending_qty=$end_pid_qty_actual;
							$end_pid_qty_actual=$end_pid_qty_actual-$end_pid_qty_actual;
							
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

									if($row['product_base_unit'] != $row['product_con_unit']){
										$conv_used_qty = convert_stock($dbcon,$used_qty,$row['product_id'],"conv_unit");
									}else{
										$conv_used_qty = $used_qty;
									}
									$info_grn_sub_trn['product_conv_qty']		= $conv_used_qty;
									$info_grn_sub_trn['product_conv_unit']		= $row['product_con_unit'];
									$info_grn_sub_trn['job_work_po_trn_id']		= $job_work_po_trn_id;
									$info_grn_sub_trn['product_process_rate']	= $row['pr_rate'];
									$info_grn_sub_trn['extra_stock']	= $row['extra_stock'];
									$info_grn_sub_trn['product_process_unit']	= $row['product_base_unit'];
									// $info_grn_sub_trn['material_rate']			= $used_qty * $row['pr_rate'];
									$info_grn_sub_trn['total_process_rate']			= $used_qty * $row['pr_rate'];
									
									$mt_rate = convert_rate($dbcon,$row['pr_rate'],$row['product_id'],"conv_unit");
									
									// $info_grn_sub_trn['material_conv_rate']			= $conv_used_qty * $mt_rate;
									$info_grn_sub_trn['total_process_conv_rate']	= $conv_used_qty * $mt_rate;

									$info_grn_sub_trn['purchaseordertrn_id']	= $job_work_po_trn_id;

									$info_grn_sub_trn['product_scrap_id']		= $product_scrap_id;
									$info_grn_sub_trn['scrap_unit']				= $scrap_unit;
									$info_grn_sub_trn['rp_id']				= $row['p_ref_id'];
									
									$grn_scrap_qty =  $used_qty * $scrap_qty / $stop_qty;
									$info_grn_sub_trn['scrap_qty']				=round_up($grn_scrap_qty,5);
									
									$info_grn_sub_trn['cdate']					= date("Y-m-d H:i:s");
									$info_grn_sub_trn['user_id']				= $_SESSION['user_id'];
									$info_grn_sub_trn['company_id']				= $_SESSION['company_id'];
									
									$grn_trn_sub_id=add_record('tbl_grn_sub_trn',$info_grn_sub_trn,$dbcon,$branch_id);


								if($process_end_time_qc == '1'){
									$info_tmp_qc['grn_sub_trn_id'] = $grn_trn_sub_id;
									$info_tmp_qc['batch_id'] = $batch_gen_id;
									update_record('tbl_temp_qc', $info_tmp_qc,"status = 0 and grn_trn_id=".$grn_trn_id . " and rp_id = ".$info_grn_sub_trn['rp_id'], $dbcon);
								}
								
									$upd_trn_data['product_process_rate']		=$row['pr_rate'];
									$upd_trn_data['product_process_unit']		=$row['product_base_unit'];

									update_record('tbl_grn_trn', $upd_trn_data,"grn_trn_id=".$grn_trn_id, $dbcon);
									
								//tbl_grn_sub_trn entry end
								
								//update job work grn complite/not complite start
									grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id'],$qty_variation[$i],$jobcard_close[$i],$diff_qty_plus,$diff_qty_minus);
								//update job work grn complite/not complite end
								
								//allocate process trn entry start
								
								$allocate_trn_stop_qty=allocate_process_trn_stop_entry_start_entry_wise($dbcon,$used_qty,$row['p_id'],$start_stop_user_id,$grn_trn_sub_id,$diff_qty_plus,$diff_qty_minus);

								//allocate process trn entry end
								
								//allocate process table pen_qty update start 
									
									tbl_allocate_process_update_pen_qty($dbcon,$row['p_id'],$allocate_trn_stop_qty,$diff_qty_plus,$diff_qty_minus);
									
								//allocate process table pen_qty update end
								
								//allocate process pstatus update start
									tbl_allocate_process_update_p_status($dbcon,$row['p_id']);
									
								//allocate process pstatus update start

								$company_config = getCompanyConfiguration($dbcon);	
								//stock deduct start

								if($company_config['store_approval'] == '1'){
									if($row['extra_stock'] == 0){
										store_grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info,$grn_base_qty,$material_product_id,$material_pid,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);
									}
								}else{
									if($row['extra_stock'] == 0){
									grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info);
									}
								}
								//stock deduct end 
							}
						}
					}
				}
			}
		}else{
				$jb_whr = "";
			if(!empty($vender_id)){
					$jb_whr = " and job.vender_id = " . $vender_id;
				}

			// echo "ok";
			 $query = "select job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.product_con_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id,job_trn.rate_unit,ap.p_ref_id,job_sub_trn.pr_rate from tbl_job_work_sub_trn as job_sub_trn
					left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
					left join tbl_job_work as job on job_trn.job_work_id=job.job_work_id
					 left join tbl_allocate_process as ap on ap.p_id=job_sub_trn.p_id
					where job_sub_trn.grn_complete_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id in (".$p_id.") ". $jb_whr;
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);
			if($cnt>0){
				
				while($row=brp_mysqli_fetch_array($result))
				{
					$job_work_sub_trn_grn_pending_qty=job_work_sub_trn_grn_pending_qty($dbcon,$row['job_work_sub_trn_id'],$row['product_base_unit']);
					
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
							$used_conv_qty = 0;

							if($row['product_base_unit'] != $row['product_conv_unit']){
								$grn_qry = "select * from tbl_grn_trn where grn_trn_id = " . $grn_trn_id;
								$grn_res=$dbcon->query($grn_qry);
								$grn_row=brp_mysqli_fetch_array($grn_res);
								$used_conv_qty=($used_qty/$grn_row['product_qty'])*$grn_row['product_conv_qty'];
							}else{
								$used_conv_qty = $used_qty;
							}
							//tbl_grn_sub_trn entry start
								$info_grn_sub_trn['product_id']				= $row['product_id'];
								$info_grn_sub_trn['grn_trn_id']				= $grn_trn_id;
								$info_grn_sub_trn['jobwork_id']				= $row['job_work_id'];
								$info_grn_sub_trn['job_work_trn_id']		= $row['job_work_trn_id'];
								$info_grn_sub_trn['job_work_sub_trn_id']	= $row['job_work_sub_trn_id'];
								$info_grn_sub_trn['product_qty']			= $used_qty;
								$info_grn_sub_trn['product_base_unit']		= $row['product_base_unit'];
								$info_grn_sub_trn['product_conv_qty']		= $used_conv_qty;
								$info_grn_sub_trn['product_conv_unit']		= $row['product_con_unit'];
								$info_grn_sub_trn['job_work_po_trn_id']		= $job_work_po_trn_id;
								
								$info_grn_sub_trn['purchaseordertrn_id']	= $job_work_po_trn_id;

								$info_grn_sub_trn['product_scrap_id']		= $product_scrap_id;
								$info_grn_sub_trn['scrap_unit']				= $scrap_unit;
								$info_grn_sub_trn['rp_id']				= $row['p_ref_id'];

								$grn_scrap_qty =  $used_qty * $scrap_qty / $stop_qty;
								$info_grn_sub_trn['scrap_qty']				=round_up($grn_scrap_qty,5);
								
								$info_grn_sub_trn['cdate']					= date("Y-m-d H:i:s");
								$info_grn_sub_trn['user_id']				= $_SESSION['user_id'];
								$info_grn_sub_trn['company_id']				= $_SESSION['company_id'];

								$info_grn_sub_trn['product_process_rate']		=$row['pr_rate'];
								$info_grn_sub_trn['product_process_unit']		=$row['product_base_unit'];
								// $info_grn_sub_trn['material_rate']			= $used_qty * $row['pr_rate'];

								$info_grn_sub_trn['total_process_rate']			= $used_qty * $row['pr_rate'];
									
								$mt_rate = convert_rate($dbcon,$row['pr_rate'],$row['product_id'],"conv_unit");
								
								// $info_grn_sub_trn['material_conv_rate']			= $conv_used_qty * $mt_rate;
								$info_grn_sub_trn['total_process_conv_rate']	= $used_conv_qty * $mt_rate;
								
								$info_grn_sub_trn['material_conv_rate']			= $used_conv_qty * $mt_rate;
								$grn_trn_sub_id=add_record('tbl_grn_sub_trn',$info_grn_sub_trn,$dbcon,$branch_id);

								$upd_trn_data['product_process_rate']		=$row['pr_rate'];
								$upd_trn_data['product_process_unit']		=$row['product_base_unit'];

								update_record('tbl_grn_trn', $upd_trn_data,"grn_trn_id=".$grn_trn_id, $dbcon);
							//tbl_grn_sub_trn entry end
							
							//update job work grn complite/not complite start
								grn_status_update_in_tbl_job_work_sub_trn($dbcon,$row['job_work_sub_trn_id'],$qty_variation,$jobcard_close);
							//update job work grn complite/not complite end
							
							//allocate process trn entry start
							
								$allocate_trn_stop_qty=allocate_process_trn_stop_entry_start_entry_wise($dbcon,$used_qty,$row['p_id'],$start_stop_user_id,$grn_trn_sub_id,0,0);

								
							//allocate process trn entry end
							
							
							//allocate process table pen_qty update start 
								
								tbl_allocate_process_update_pen_qty($dbcon,$row['p_id'],$allocate_trn_stop_qty);
								
							//allocate process table pen_qty update end
							
							//allocate process pstatus update start
							
								tbl_allocate_process_update_p_status($dbcon,$row['p_id']);
								
							//allocate process pstatus update start
								
							$company_config = getCompanyConfiguration($dbcon);	
								//stock deduct start

								if($company_config['store_approval'] == '1'){
									store_grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info,$grn_base_qty,$material_product_id,$material_pid,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);
								}else{
									grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info);
								}	
							//stock deduct start
								// grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_paramter_info);
							//stock deduct end 
						}
						
					}
				}
			}
		}

		if($process_end_time_qc  == '1'){
			$tmp_qc_p_id =  implode(",",$pid_array);
			 $qc_qry = "SELECT qc.*,ap.p_ref_id FROM tbl_temp_qc as qc left join tbl_allocate_process as ap on ap.p_id = qc.p_id where qc.status = 0 and qc.p_id in(".$tmp_qc_p_id.") and qc.grn_trn_id = ".$grn_trn_id;
			$qc_res = $dbcon->query($qc_qry);
			while($qc_row = brp_mysqli_fetch_assoc($qc_res)){

				/* add qc */
			$qc_no = load_common_no($dbcon,QC_NO);
			
			$qc_info['qc_no']			= $qc_no;			
			$qc_info['qc_type']			= 2;			
			$qc_info['qc_date']		= date("Y-m-d");
			$qc_info['qc_remark']		= $qc_row['remark'];			
			$qc_info['grn_id']			= $grn_id;			
			$qc_info['grn_trn_id']		= $grn_trn_id;			
			$qc_info['po_ref_id']		= $qc_row['rp_id'];			
			$qc_info['cdate']			= date("Y-m-d H:i:s");
			$qc_info['user_id']		= $_SESSION['user_id'];
			$qc_info['company_id']		= $_SESSION['company_id'];
			$qc_info['qc_godown']		= $qc_row['new_godown_id'];	
			$qc_info['batch_id']		= $qc_row['batch_id'];	
			$qc_info['product_id']		= $qc_row['product_id'];	
			$qc_info['process_id']		= $qc_row['process_id'];	
			$qc_info['rejected_conv_new_product_id']	= $qc_row['new_product_id'];	

			$pmst_qry = "select * from product_mst where product_id = " . $qc_row['product_id'];
			$pro_rw = brp_mysqli_fetch_assoc($dbcon->query($pmst_qry));

			$unit_id =$qc_row['unit_id'];
			$base_unit = $pro_rw['product_base_unit'];
			$conv_unit_id =$pro_rw['product_conv_unit'];

			$accept=0;
			$reprocess=0;
			$reject=0;

			$accept_conv=0;
			$reprocess_conv=0;
			$reject_conv=0;

			if($qc_row['type'] == '1'){
				$accept=$qc_row['qty'];
			}else if($qc_row['type'] == '2'){
				$reject=$qc_row['qty'];
			}else if($qc_row['type'] == '3'){
				$reprocess=$qc_row['qty'];
			}

			if($base_unit==$conv_unit_id){
				$accept_conv=$accept;
				$reprocess_conv=$reprocess;
				$reject_conv=$reject;
					
				$qc_info['accepted_base_qty']= $accept;
				$qc_info['rejected_base_qty']= $reject;
				$qc_info['reprocess_base_qty']= $reprocess;
				
				$qc_info['accepted_conv_qty']= $accept;
				$qc_info['rejected_conv_qty']= $reject;
				$qc_info['reprocess_conv_qty']= $reprocess;	
			}else{

				$qry12="select base_qty,conv_qty from tbl_batch_data where batch_id = " . $qc_row['batch_id'];
				$res12=mysqli_fetch_assoc($dbcon->query($qry12));
				
				$batch_qty=$res12['base_qty'];
				$batch_conv_qty=$res12['conv_qty'];
				

				if($unit_id == $conv_unit_id){
					
					$qc_info['accepted_base_qty']= ($accept/$batch_conv_qty) * $batch_qty;
					$qc_info['rejected_base_qty']= ($reject/$batch_conv_qty) * $batch_qty;
					$qc_info['reprocess_base_qty']= ($reprocess/$batch_conv_qty) * $batch_qty;
					
					$qc_info['accepted_conv_qty']= $accept;
					$qc_info['rejected_conv_qty']= $reject;
					$qc_info['reprocess_conv_qty']= $reprocess;
				}else{
					$qc_info['accepted_base_qty']= $accept;
					$qc_info['rejected_base_qty']= $reject;
					$qc_info['reprocess_base_qty']= $reprocess;
					
					$qc_info['accepted_conv_qty']= ($accept/$batch_qty) * $batch_conv_qty;
					$qc_info['rejected_conv_qty']= ($reject/$batch_qty) * $batch_conv_qty;
					$qc_info['reprocess_conv_qty']= ($reprocess/$batch_qty) * $batch_conv_qty;
				}

			}

			$qc_info['qc_qty']= $accept;
			$qc_info['qc_unit']= $unit_id;

			if($qc_row['type'] == '1'){
				$qc_info['accepted_base_unit']= $base_unit;
				$qc_info['accepted_conv_unit']= $conv_unit_id;
				$qc_info['accepted_godown'] = $qc_row['new_godown_id'];
			}else if($qc_row['type'] == '2'){
				$qc_info['rejected_base_unit']= $base_unit;
				$qc_info['rejected_conv_unit']= $conv_unit_id;
				$qc_info['rejected_godown']= $qc_row['new_godown_id'];
			}else if($qc_row['type'] == '3'){
				$qc_info['reprocess_base_unit']= $base_unit;
				$qc_info['reprocess_conv_unit']= $conv_unit_id;
				$qc_info['reprocess_godown']= $qc_row['new_godown_id'];
			}
		
			$qc_info['branch_id']= $qc_row['branch_id'];

			$qc_id=add_record('tbl_qc', $qc_info, $dbcon);

			if($qc_id){
				update_common_no($dbcon,QC_NO);	

				$qc_info_trn['qc_id']				= $qc_id;			
				$qc_info_trn['qc_product']		= $qc_row['product_id'];			
				$qc_info_trn['qc_product_qty']	= $qc_row['qty'];		
				if($qc_row['type'] == '1'){
					$qc_info_trn['qc_accepted'] 		= $qc_row['qty'];	
				}
				if($qc_row['type'] == '2'){
					$qc_info_trn['qc_rejected']		= $qc_row['qty'];	
					$qc_info_trn['qc_reject_godown']		= $qc_row['new_godown_id'];	
				}
				if($qc_row['type'] == '3'){
					$qc_info_trn['qty_reprocess']		= $qc_row['qty'];	
					$qc_info_trn['qc_reporcess_godown']	= $qc_row['new_godown_id'];		
				}
				$qc_info_trn['stock_status']		= 1;	
				// $qc_info_trn['po_id']				= $POST['po_id'];		
				$qc_info_trn['qc_unit_id']		= $qc_row['unit_id'];
				// Umair Start 05-03-2021	
				
				$qc_info_trn['cdate']				= date("Y-m-d H:i:s");
				$qc_info_trn['user_id']			= $_SESSION['user_id'];
				$qc_info_trn['company_id']		= $_SESSION['company_id'];	

				$insertid_qc=add_record('tbl_qc_trn', $qc_info_trn, $dbcon,$branch_id);
				/* end qc */
				if($qc_row['type'] == '2'){
					$qc_rejinfo['product_id']	= $qc_row['new_product_id'];
					$qc_rejinfo['qc_id']	= $qc_id;
					$qc_rejinfo['qty']		= $qc_row['qty'];	
					$qc_rejinfo['unit_id']	= $qc_row['new_unit_id'];
					$qc_rejinfo['status']		= 0;
					$qc_rejinfo['batch_id']	= $qc_row['batch_id'];
					$qc_rejinfo['cdate']		= date("Y-m-d H:i:s"); 
					$qc_rejinfo['user_id']	= $_SESSION['user_id'];
					$qc_rejinfo['company_id']	= $_SESSION['company_id']; 

					$inserid=add_record('tbl_qc_reject_new_product', $qc_rejinfo, $dbcon);

					$new_pro = "select * from product_mst where product_id = " . $qc_row['new_product_id'];
					$pro_new = brp_mysqli_fetch_assoc($dbcon->query($pmst_qry));
					$rate_unit_qc = $qc_row['new_unit_id'];
					$remaining_qty_qc = $qc_row['qty'];

					if($qc_row['new_unit_id']==$pro_new['product_conv_unit']){
						$type="base_unit";
						$conv_qty_qc=$remaining_qty_qc;
						$base_qty_qc = ($conv_qty_qc/$pro_rw['product_conv_qty']) * $pro_rw['product_base_qty'];
					}else{
						$type="conv_unit";
						$base_qty_qc=$remaining_qty_qc;
						$conv_qty_qc = ($base_qty_qc/$pro_rw['product_base_qty']) *$pro_rw['product_conv_qty'];
					}

					$batch_qty_qc=$base_qty_qc;
					$batch_conv_qty_qc=$conv_qty_qc;

					$mfg_date = date("Y-m-d");
					$dt = get_exp_date_by_product($dbcon,$qc_row['new_product_id'],date("d-m-Y"));
					$exp_date = date('Y-m-d',strtotime($dt));
					
					$batch_info_rej['mfg_date']			= $mfg_date;
					$batch_info_rej['exp_date']			= $exp_date;
						
					
					$batch_info_rej['batch_qty']		= $remaining_qty_qc;
					$batch_info_rej['order_no']			= $qc_no;
					$batch_info_rej['product_id']		= $qc_row['new_product_id'];
					$batch_info_rej['grn_date']			= date('Y-m-d');
					$batch_info_rej['batch_type']		= $companyConfiguration['batch_type'];
					$batch_info_rej['production_type']	= '1';			
					$batch_info_rej['status']			= '0';			
					$batch_info_rej['grn_godown']			= $qc_row['new_godown_id'];			
					
					$batch_info_rej['qc_status']		= 1;
					$batch_info_rej['accept_qty']		= $remaining_qty_qc;
					$batch_info_rej['grn_accept_qty']		= $remaining_qty_qc;
					$batch_info_rej['qc_qty']			= $remaining_qty_qc;
					
					$batch_info_rej['cdate']			= date("Y-m-d H:i:s"); 
					$batch_info_rej['user_id']			= $_SESSION['user_id'];
					$batch_info_rej['company_id']		= $_SESSION['company_id'];	
					$batch_info_rej['branch_id']		= $qc_row['branch_id'];
					$batch_info_rej['batch_unit']		= $rate_unit_qc;
					$batch_info_rej['base_qty']			= $batch_qty_qc;
					$batch_info_rej['base_unit']		= $pro_new['product_base_unit'];
					$batch_info_rej['conv_qty']			= $batch_conv_qty_qc;
					$batch_info_rej['conv_unit']		= $pro_new['product_conv_unit'];
					$batch_info_rej['qc_id']			= $qc_id;

					$batch_gen_id = add_record('tbl_batch_data', $batch_info_rej, $dbcon);
				}else if($qc_row['type'] == '3'){
					$qry_12="select * from tbl_wororder_product_process where rp_id=". $qc_row['p_ref_id'] ." and process_priority >= (select process_priority from tbl_wororder_product_process where rp_id=". $qc_row['p_ref_id'] ." and process_id = ".$qc_row['new_process_id']." and product_id = ". $qc_row['product_id'].") and process_priority <= (select process_priority from tbl_wororder_product_process where rp_id=". $qc_row['p_ref_id'] ." and process_id = ". $qc_row['process_id'] ." and product_id = ". $qc_row['product_id'].") and product_id = ". $qc_row['product_id']." order by process_priority asc";
					$res_12 = $dbcon->query($qry_12);
					while($set_row_12=mysqli_fetch_assoc($res_12)){
						$reprocess_info['product_id'] = $set_row_12['product_id'];
						$reprocess_info['qc_id'] = $qc_id;
						$reprocess_info['rp_id'] = $qc_row['p_ref_id'];
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
						/* ***************************************** */
					$set11="select ap.*,rp.reject_status from tbl_allocate_process as ap
						left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
						where p_id=".$qc_row['p_id'];
						//var_dump($set11);
					$set_row=mysqli_fetch_assoc($dbcon->query($set11));

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

					$info7['process_id']		= $qc_row['new_process_id'];
					$info7['pt_alloc_id']		= $qc_row['p_id'];
					$info7['p_start_time']		= '';		
					$info7['p_end_time']		= '';		
					$info7['p_qty']				= $qc_row['qty'];		
					$info7['pen_qty']			= $qc_row['qty'];		
					$info7['p_ref_id']			= $qc_row['rp_id'];		
					$info7['p_ref_type']		= 'process_request';		
					$info7['p_product_id']		= $qc_row['product_id'];		
					$info7['pr_process_type']	= $set_row['pr_process_type'];		
					$info7['pr_process_id']		= $set_row['p_id'];		
					$info7['process_priority']		= 1;
					$info7['process_unit']		= $qc_row['unit_id'];		
					$info7['qc_id']		= $qc_row['temp_id'];		
					$info7['batch_id']		=$batch_gen_id;	
					
					$info7['cdate']				= date("Y-m-d H:i:s");
					$info7['user_id']			= $_SESSION['user_id'];
					$info7['company_id']		= $_SESSION['company_id'];	

					/* 
						Sanat Start code :: 04/05/22  for costing report for reprocess
					*/
					
					$info7['product_process_rate'] = $row__1['pr_rate'];
					$info7['product_process_unit'] = $row__1['product_base_unit'];
					$info7['total_process_rate'] = $qc_row['qty'] * $row__1['pr_rate'];
					$pro_rate = convert_rate($dbcon,$row__1['pr_rate'],$qc_row['product_id'],"conv_unit");
					$conv_reprocess_qty = convert_stock($dbcon,$qc_row['qty'],$qc_row['product_id'],"conv_unit");
					$info7['total_process_conv_rate']	= $conv_reprocess_qty * $pro_rate;

					$mat_rate_for_one =  $row__2['process_pus_material_rate'] / $row__2['product_qty'];
					$mat_conv_rate_for_one =  $row__2['process_pus_material_conv_rate'] / $row__2['product_conv_qty'];

					$info7['material_rate'] = (float)$mat_rate_for_one * (float)$qc_row['qty'];
					$info7['material_conv_rate'] = (float)$mat_conv_rate_for_one * (float)$conv_reprocess_qty;
					
					$info7['process_pus_material_rate'] = $info7['material_rate'] + $info7['total_process_rate'];
					$info7['process_pus_material_conv_rate'] = $info7['material_rate'] + $info7['total_process_conv_rate'];
					
					/* 
						Sanat End code :: 04/05/22  for costing report for reprocess
					*/
					
					$inserid_alloc=add_record('tbl_allocate_re_process', $info7, $dbcon,$branch_id);
					
					$info8['pt_alloc_id']	= $set_row['p_id'];			
					$info8['pt_ref_id']		= $qc_row['rp_id'];			
					$info8['pt_product_id']	= $qc_row['product_id'];			
					// $info8['pt_process_id']	= $process_id;			
					$info8['pt_process_id']	= $qc_row['new_process_id'];	
					$info8['pt_qty']		= $qc_row['qty'];			
					$info8['cdate']			= date("Y-m-d H:i:s");
					$info8['user_id']		= $_SESSION['user_id'];
					$info8['company_id']	= $_SESSION['company_id'];	
					
					add_record('tbl_allocate_re_process_trn', $info8, $dbcon,$branch_id);
						/* ***************************************** */
				}

			}
		}	
	}
		
	/*Added by Sanat :: Start :: 08-08-22 */
	$btc_qry = "select * from tbl_batch_data where status = 0 and grn_trn_id = " . $grn_trn_id . " and grn_id =" . $grn_id;
	$btc_res = $dbcon->query($btc_qry);

	while($btc_row = brp_mysqli_fetch_assoc($btc_res)){
		if($btc_row['qc_status'] == "1" && $auto_store_relese == '1'){
			auto_store_approval_entry($dbcon,$btc_row['batch_id']);
		}
	}
	/*Added by Sanat :: End :: 08-08-22 */
}
	
	
	function job_work_product_for_pending_grn($dbcon,$vender_id,$jobwork_trn_id){
		$company_config = getCompanyConfiguration($dbcon);		
		$production_pro_search = $company_config['production_pro_search'];
		$pro_search=explode(",", $production_pro_search);
		
		
		if(!empty($vender_id)){
			$vender_where=" and job.vender_id=".$vender_id;
		}
		if(!empty($jobwork_trn_id)){
			$job_where=" and job_trn.job_work_trn_id in (".$jobwork_trn_id.")";
		}

		if($company_config['jobwork_grn'] == '0'){
		 $query = "select GROUP_CONCAT(job.job_work_no) as job_work_no,GROUP_CONCAT(job.chalan_no) as chalan_no,GROUP_CONCAT(res.job_card_no) as job_card_no, sum(job_sub_trn.product_base_qty) as job_qty,GROUP_CONCAT(job_sub_trn.p_id) as p_id,GROUP_CONCAT(job_sub_trn.job_work_sub_trn_id) as job_sub_trn_id,job_trn.product_id,job_trn.process_id,job_trn.product_base_unit,job_trn.product_con_unit,promst.product_type,promst.product_name,process_ms.process_name,job_trn.qc_id, unit.unit_name, promst.batch_wise_stock_manage,promst.product_icode, dr.drawing_number,promst.product_mat_center from tbl_job_work as job
					left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
					left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id
					left join product_mst as promst on promst.product_id=job_trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = promst.drawing_id
					left join tbl_request_product as res on res.rp_id=job_sub_trn.rp_id
					left join unit_mst as unit on unit.unitid=job_trn.product_base_unit
					left join process_mst as process_ms on process_ms.process_id=job_trn.process_id
					where job.grn_complete_status=0 and job.job_work_type in (2,4) and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.grn_complete_status=0 and job_trn.grn_complete_status=0 ".$job_where.$vender_where." group by job_trn.process_id,job_trn.product_id,job.job_work_type,job_trn.qc_id" ;
		}else{
			 $query = "select GROUP_CONCAT(job.job_work_no) as job_work_no,GROUP_CONCAT(job.chalan_no) as chalan_no,GROUP_CONCAT(res.job_card_no) as job_card_no, sum(job_sub_trn.product_base_qty) as job_qty,GROUP_CONCAT(job_sub_trn.p_id) as p_id,GROUP_CONCAT(job_sub_trn.job_work_sub_trn_id) as job_sub_trn_id,job_trn.product_id,job_trn.process_id,job_trn.product_base_unit,job_trn.product_con_unit,promst.product_type,promst.product_name,process_ms.process_name,job_trn.qc_id, unit.unit_name, promst.batch_wise_stock_manage,promst.product_icode, dr.drawing_number, promst.product_mat_center from tbl_job_work as job
					left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
					left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id
					left join product_mst as promst on promst.product_id=job_trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = promst.drawing_id
					left join tbl_request_product as res on res.rp_id=job_sub_trn.rp_id
					left join unit_mst as unit on unit.unitid=job_trn.product_base_unit
					left join process_mst as process_ms on process_ms.process_id=job_trn.process_id
					where job.grn_complete_status=0 and job.job_work_type in (2,4) and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.grn_complete_status=0 and job_trn.grn_complete_status=0 ".$vender_where." ".$job_where." group by job_trn.process_id,job_trn.product_id,job.job_work_type,job_trn.qc_id,job.job_work_id" ;
		}
		// echo $query;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$i=1;
			while($row=brp_mysqli_fetch_array($result))
			{
				$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],$row['process_id']);
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

				$product_id = $row['product_id'];

				$type="conv_unit";
				$pending_conv_qty=convert_stock($dbcon,$pending_qty,$product_id ,$type);

				$drawing_number = "";
				$item_code = "";

				
				
				 if(in_array('drawing',$pro_search)){
			            $drawing_number = " -- (".$row['drawing_number'].")";
			        }
			        if(in_array('item',$pro_search)){
			            $item_code = " -- (".$row['product_icode'].")";
			        }

			    

				$process=p_id_wise_find_previous_and_next_process($dbcon,$row['p_id']);
				$process_pr=json_decode($process);

		
				$next_process_id=$process_pr->next_process_id;
				
				$rol['grn_godown'] = $row['product_mat_center'];
				
				$product_name = '"'.$row['product_name'].'"';	
				$unit_name = '"'.$row['unit_name'].'"';
				if($pending_qty>0){
					 $str .="<tr>";
						$str .="<td>".$i."</td>
								
								<td>".$row['product_name']." (".$row['process_name'].' '.$item_code.' '.$drawing_number." ".$reprocess.")
									</br> <strong> Jobwork No : ". $row['job_work_no'] ." </strong>" ." 
									</br> <strong> Jobwork chalan No : ". $row['chalan_no'] ." </strong>" ." 
									</br> <strong> Jobcard No : ". $row['job_card_no'] ." </strong>" ." 
								</td>
								<td>".$row['product_name']. "</td>
								<td>".$row['job_qty']." </br><span style='color:green; margin-left:5px;font-weight: bold;'>".$row['unit_name']."</span></td>
								<td>".$pending_qty." </br><span style='color:green; margin-left:5px;font-weight: bold;'>".$row['unit_name']."</span></td>
								<td>
									<input type='text' class='form-control entered_qty".$i."' max='".$pending_qty."' name='grn_qty[]' id='grn_qty$i' value='' onkeyup='product_convert_qty(1,".$i.");' /> 
									<input type='hidden' class='form-control".$i."' max='".$pending_conv_qty."' name='conv_grn_qty[]' id='conv_grn_qty$i' value='".$pending_conv_qty."' /> 
									<input type='hidden' class='form-control".$i."' max='".$pending_qty."' name='grn_qty_hide[]' id='grn_qty_hide$i' value='".$pending_qty."' /> 
									<input type='hidden' class='form-control".$i."' max='".$pending_conv_qty."' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$i' value='".$pending_conv_qty."' /> 	
									<span style='color:green; margin-left:5px;font-weight: bold;'>".$row['unit_name']."</span>";

							if($row['batch_wise_stock_manage'] == 1 && $next_process_id == 0){
								//$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$i.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["product_base_unit"].");' ><i class='fa fa-plus'></i></button>";
							}	
							$str.="</td>
								<td>
									<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$i' required >";
									$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
								$str.="</select>
								</td>
								<td>
								<!-- <input type='button' id='addprocess".$i."' class='btn btn-primary' data-original-title='Add Process' data-toggle='tooltip' data-placement='top' onclick='' value='Add'>--></td> 
								<input type='hidden' name='grn_pid[]' id='grn_pid$i' value='".$row['product_id']."' />
								<input type='hidden' name='product_id[]' id='product_id$i' value='".$row['product_id']."' />
								<input type='hidden' name='process_id[]' id='process_id$i' value='".$row['process_id']."' />
								<input type='hidden' name='product_base_unit[]' id='product_base_unit$i' value='".$row['product_base_unit']."' />
								<input type='hidden' name='product_conv_unit[]' id='product_conv_unit$i' value='".$row['product_con_unit']."' />
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
	
	function next_process_entry($dbcon,$product_qty,$unit_id,$previous_process_id,$next_process_id,$next_process_type,$next_process_priority,$workorder_process_id,$auto_store_relese=0,$batch_id=0){

		$query = "select extra_stock, extra_stock_material_reserve, p_product_id, p_ref_id, branch_id, product_version, batch_no,process_id, batch_process_start_time from tbl_allocate_process as allo
		where allo.p_id=".$previous_process_id ;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);

		$pre_pro_id = $row['process_id'];

		$query1 = "select wp.description from tbl_wororder_product_process as wp
		where wp.pr_process_id=".$workorder_process_id ;
		$result1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_array($result1);

		 $query2 = "select resource_id from tbl_work_order_resource_allocate where process_id=".$workorder_process_id ." and request_id =" .$row['p_ref_id'];
		$result2=$dbcon->query($query2);
		$row2=brp_mysqli_fetch_array($result2);
		
		$info['process_id']				= $next_process_id;
		$info['p_qty']					= $product_qty;
		$info['pen_qty']				= $product_qty;
		$info['resource_id']			= $row2['resource_id'];
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
		$info['extra_stock']			= $row['extra_stock'];
		$info['extra_stock_material_reserve']	= $row['extra_stock_material_reserve'];
		$info['process_unit']			= $unit_id;
		$info['process_type_data']		= "0";
		$info['product_version']		= $row['product_version'];
		$info['batch_no']			= $row['batch_no'];
		$info['description']			= $row1['description'];
		$info['batch_process_start_time']			= $row['batch_process_start_time'];
		$info['branch_id']			= $row['branch_id'];

		$info['cdate']					= date("Y-m-d H:i:s");
		$info['user_id']				= $_SESSION['user_id'];
		$info['company_id']				= $_SESSION['company_id'];
		
		$p_id=add_record('tbl_allocate_process',$info, $dbcon);

		return $p_id;
		
	}

	function next_reprocess_entry($dbcon,$product_qty,$unit_id,$previous_process_id,$next_process_id,$next_process_type,$next_process_priority){
		
		$query = "select * from tbl_allocate_re_process as allo
		where allo.p_id=".$previous_process_id;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);
		
		$info['process_id']				= $next_process_id;
		$info['pt_alloc_id']			= $row['pt_alloc_id'];
		$info['p_qty']					= $product_qty;
		$info['pen_qty']				= $product_qty;
		$info['p_status']				= "0";
		// $info['task_status']			= "0";
		$info['p_ref_id']				= $row['p_ref_id'];
		$info['p_ref_type']				= "GRN";
		$info['p_product_id']			= $row['p_product_id'];
		$info['pr_process_type']		= $next_process_type;
		$info['process_priority']		= $next_process_priority;
		$info['previous_process_id']	= $previous_process_id;
		// $info['process_stock']			= "0";
		// $info['process_used_stock']		= "0";

		$info['process_unit']			= $unit_id;
		// $info['process_type_data']		= "0";
		// $info['product_version']		= $row['product_version'];
		$info['batch_id']			= $row['batch_id'];
		$info['qc_id']			= $row['qc_id'];

		$info['cdate']					= date("Y-m-d H:i:s");
		$info['user_id']				= $_SESSION['user_id'];
		$info['company_id']				= $_SESSION['company_id'];
		
		/* 
			Sanat Start code :: 04/05/22  for costing report for reprocess
		*/
		

		$query__1 = "select job_sub_trn.rp_id,job_sub_trn.job_work_sub_trn_id,job_sub_trn.p_id,job_sub_trn.product_base_qty,job_sub_trn.product_base_unit,job_sub_trn.product_con_unit,job_sub_trn.job_work_sub_trn_id,job_sub_trn.job_work_trn_id,job_sub_trn.product_id,job_trn.job_work_id,job_trn.rate_unit,ap.p_ref_id,job_sub_trn.pr_rate from tbl_job_work_sub_trn as job_sub_trn
		left join tbl_job_work_trn as job_trn on job_trn.job_work_trn_id=job_sub_trn.job_work_trn_id
		left join tbl_allocate_process as ap on ap.p_id=job_sub_trn.p_id
		where job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.p_id =".$row['pt_alloc_id']." and job_sub_trn.rp_id =".$row['p_ref_id'];
		$result__1=$dbcon->query($query__1);
		$row__1=brp_mysqli_fetch_array($result__1);

		$info['product_process_rate'] = $row__1['pr_rate'];
		$info['product_process_unit'] = $row__1['rate_unit'];
		$info['total_process_rate'] = $product_qty * $row__1['pr_rate'];
		$pro_rate = convert_rate($dbcon,$row__1['pr_rate'],$row['p_product_id'],"conv_unit");
		$conv_product_qty = convert_stock($dbcon,$product_qty,$row['p_product_id'],"conv_unit");
		$info['total_process_conv_rate']	= $conv_product_qty * $pro_rate;
		$r_qty = $row['p_qty'];
		$r_conv_qty = convert_stock($dbcon,$r_qty,$row['p_product_id'],"conv_unit");
		$mat_rate_for_one =  $row['process_pus_material_rate'] / $r_qty;
		$mat_conv_rate_for_one =  $row['process_pus_material_conv_rate'] / $r_conv_qty;

		$info['material_rate'] = (float)$mat_rate_for_one * (float)$product_qty;
		$info['material_conv_rate'] = (float)$mat_conv_rate_for_one * (float)$conv_product_qty;

		$info['process_pus_material_rate'] = $info['material_rate'] + $info['total_process_rate'];
		$info['process_pus_material_conv_rate'] = $info['material_rate'] + $info['total_process_conv_rate'];

		/* 
			Sanat End code :: 04/05/22  for costing report for reprocess
		*/

		$p_id=add_record('tbl_allocate_re_process',$info, $dbcon,$row['branch_id']);
		
		return $p_id;
		
	}
	
	function production_reserve_add_process_stock($dbcon,$product_qty,$unit_id,$p_id,$process_stock_id,$stock_date,$ref_name,$ref_id,$auto_store_relese,$next_process_id){
		

		$query = "select product_id,process_id,godown_id,branch_id, used_base_stock, used_convert_stock from tbl_process_stock_trn as allo_mat
		where allo_mat.process_stock_id=".$process_stock_id ;

		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$row=brp_mysqli_fetch_array($result);

			$used_base_stock = 0;

			if($row['used_base_stock'] != "" && $row['used_base_stock'] != "0"){
				$used_base_stock = $row['used_base_stock'];
			}

			if($row['used_convert_stock'] != "" && $row['used_convert_stock'] != "0"){
				$used_conv_stock = $row['used_convert_stock'];
			}

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
			$info_stockadd['branch_id']				= $row['branch_id'];
			$info_stockadd['cdate']						= date("Y-m-d H:i:s");
			$info_stockadd['user_id']					= $_SESSION['user_id'];
			$info_stockadd['company_id']				= $_SESSION['company_id'];
			// var_dump($auto_store_relese);
			if($auto_store_relese==1){

			$que_1="select * from tbl_allocate_process where p_id=".$p_id;
			$rs_di_1=$dbcon->query($que_1);
			$re_1=brp_mysqli_fetch_assoc($rs_di_1);

			$info_store_req['rp_id']		= $re_1['p_ref_id'];
			$info_store_req['product_id']		= $re_1['p_product_id'];
			$info_store_req['process_id']		= $re_1['process_id'];
			$info_store_req['remark']		= "Auto Approval";
			$info_store_req['cdate']		= date("Y-m-d H:i:s");
			$info_store_req['user_id']	= $_SESSION['user_id'];
			$info_store_req['company_id']	= $_SESSION['company_id'];
			$info_store_req['branch_id']	= $row['branch_id'];
			$info_store_req['base_unit'] = $re['product_base_unit'];
			$info_store_req['conv_unit'] = $re['product_conv_unit'];
			
			$info_store_req['p_id']		= $p_id;
			$info_store_req['base_qty']	= $info_stockadd['base_stock'];
			$info_store_req['conv_qty']	= $info_stockadd['conv_stock'];
			$info_store_req['release_qty']	= $info_stockadd['base_stock'];
			// var_dump($info_store_req);
				$req_id = add_record('tbl_store_request',$info_store_req, $dbcon);
			
				if($req_id){
					$info_stockadd['approve_base_stock']				= $info_stockadd['base_stock'];
					$info_stockadd['approve_convert_stock']				= $info_stockadd['conv_stock'];

					$infor['p_id'] 					= $info_stockadd['p_id'];
					$infor['rp_id'] 				= $re_1['p_ref_id'];
					$infor['process_id'] 			= $info_stockadd['process_id'];
					$infor['release_qty'] 			= $info_stockadd['base_stock'];
					$infor['release_unit'] 			= $info_stockadd['base_unit'];
					$infor['release_conv_qty'] 		= $info_stockadd['conv_stock'];
					$infor['release_conv_unit'] 	= $info_stockadd['conv_unit'];
					$infor['issue_no'] 	= get_issue_no($dbcon);
					$infor['issue_date'] 	= date("Y-m-d");
					$infor['remark'] 				= "Auto Approval";
					$infor['cdate'] 				= date("Y-m-d H:i:s");
					$infor['user_id'] 				=	$_SESSION['user_id'];
					$infor['to_user_id'] 				=	$_SESSION['to_user_id'];
					$infor['company_id'] 			= $_SESSION['company_id'];
					// var_dump($infor);
					$req_t_id = add_record('tbl_store_release',$infor, $dbcon,$row['branch_id']);
					if($req_t_id){
						update_issue_no($dbcon);
					}

				}
				
			}
			
			$process_reserve_id=add_record('tbl_process_reserve_stock',$info_stockadd, $dbcon);

			if($process_reserve_id){
				$stock_info['used_base_stock'] = $used_base_stock + $info_stockadd['base_stock'];
				$stock_info['used_convert_stock'] = $used_conv_stock + $info_stockadd['conv_stock'];
				$upd1=update_record('tbl_process_stock_trn',$stock_info,"process_stock_id=".$process_stock_id, $dbcon);
			}

			
			$info_pstk['used_base_stock'] = $row['used_base_stock'] + $base_stock;
			$info_pstk['used_convert_stock'] = $row['used_convert_stock'] + $con_stock;

			$updateidss=update_record("tbl_process_stock_trn", $info_pstk,"process_stock_id=".$process_stock_id, $dbcon);

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
						$base_stock=$used_qty;
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
					$info_stockadd['perent_id']					= $row['process_reserve_id'];
					$info_stockadd['process_stock_id']			= $row['process_stock_id'];
					$info_stockadd['p_id']						= $p_id;
					$info_stockadd['stock_status']				= "0";
					$info_stockadd['cdate']						= date("Y-m-d H:i:s");
					$info_stockadd['user_id']					= $_SESSION['user_id'];
					$info_stockadd['company_id']				= $_SESSION['company_id'];

				$qry = "select * from tbl_process_reserve_stock where stock_status = 0 and stock_flage = 1 and p_id=".$p_id;
				$result1=$dbcon->query($qry);
				$row1=brp_mysqli_fetch_array($result1);
			
				$qry1 = "select * from tbl_process_stock_trn where stock_status = 0 and process_stock_id=".$row1['process_stock_id'];
				$result2=$dbcon->query($qry1);
				$row2=brp_mysqli_fetch_array($result2);
			
		
				$qry2 = "select strn.*,trn.process_id from tbl_grn_sub_trn as strn 
				left join tbl_grn_trn as trn on trn.grn_trn_id = strn.grn_trn_id
				where grn_trn_sub_id=".$ref_id;
				$result3=$dbcon->query($qry2);
				$row3=brp_mysqli_fetch_array($result3);

				$rate = $row2['process_base_rate'];
				$conv_rate = $row2['process_conv_rate']; 
				
				$total_rate		= (float)$rate * (float)$base_stock;
				$total_conv_rate = (float)$conv_rate * (float)$con_stock;
			
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];
	
				$process_reserve_id=add_record('tbl_process_reserve_stock',$info_stockadd, $dbcon,$row['branch_id']);
				
				$upd_pro_stk['used_base_stock'] = $row1['used_base_stock'] + $base_stock;
				$upd_pro_stk['used_conv_stock'] = $row1['used_conv_stock'] + $con_stock;


				$updatetrnid=update_record('tbl_process_reserve_stock',$upd_pro_stk,"process_reserve_id=".$row1['process_reserve_id'], $dbcon);


				$q_111 = "select trn.*,pro.product_name from tbl_workorder_direct_material_issue_trn as trn
							left join tbl_workorder_direct_material_issue as mst on mst.material_issue_id = trn.material_issue_id
						left join product_mst as pro on pro.product_id = trn.product_id
						where mst.status = 1 and trn.status = 0 and trn.flag = 0 and mst.rp_id = " . $row3['rp_id'] . " and mst.process_id = " . $row3['process_id'];
				$rel_111=$dbcon->query($q_111);
				$total_extra_rate = 0;
				if(brp_mysqli_num_rows($rel_111)>0){
					while($row_111 = brp_mysqli_fetch_assoc($rel_111)){

					$queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
					where ref_name ='workorder_direct_material_issue' and stock.ref_id in(".$row_111['material_issue_trn_id'].") and stock.stock_status = 0 and stock.stock_flage = 2 and product_id = " . $row_111['product_id'];
					$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
					
					$total_extra_rate = $relp_2['base_rate'] * $row_111['base_qty'];
					$total_extra_conv_rate = $relp_2['conv_rate'] * $row_111['conv_qty'];
					
					$upd_wo_mat['flag'] = 1;
					$updatetrnid=update_record('tbl_workorder_direct_material_issue_trn',$upd_wo_mat,"material_issue_trn_id=".$row_111['material_issue_trn_id'], $dbcon);
					}

				}

				$update_grn['material_rate'] = $total_rate + $total_extra_rate;
				$update_grn['material_conv_rate'] = $total_conv_rate + $total_extra_conv_rate;
				$update_grn['process_pus_material_rate'] = $update_grn['material_rate'] + $row3['total_process_rate'];
				$update_grn['process_pus_material_conv_rate'] = $update_grn['material_conv_rate'] + $row3['total_process_conv_rate'];
				
				$updatetrnid=update_record('tbl_grn_sub_trn',$update_grn,"grn_trn_sub_id=".$ref_id, $dbcon);
					
					production_deduct_process_stock($dbcon,$info_stockadd['base_stock'],$info_stockadd['base_unit'],$info_stockadd['p_id'],$info_stockadd['process_reserve_date'],$info_stockadd['godown_id'],$info_stockadd['ref_name'],$info_stockadd['ref_id'],$info_stockadd['process_stock_id'],$process_reserve_id);
				}
			}
		}
	}
	

	function production_process_reseve_stock($dbcon,$unit_id,$branch_id,$p_id,$product_id,$process_id,$process_reserve_id="",$process_stock_id="",$is_store_approval=0,$godown_id=""){
		

		/* if(!empty($reserve_id)){
			$rwhser=" and reserve_id=".$reserve_id;
			$rwhser22=" and ref_id=".$reserve_id;
		} */
		
		if(!empty($p_id)){
			$where_p_id=" and p_id in(".$p_id.")";	
		}
		if(!empty($godown_id)){
			$where_gd_id=" and godown_id = ".$godown_id;	
		}

		if(!empty($process_id)){
			$where_process_id=" and process_id=".$process_id;
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
		
		if($is_store_approval){

			$query1="select IFNULL(sum(approve_base_stock),0) as base_addqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=1 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id." ".$where_process_id." and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($result1);
		
		$query2="select IFNULL(sum(approve_convert_stock),0) as conv_addqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=conv_unit  and stock_flage=1 ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id." ".$where_process_id." and company_id=".$_SESSION['company_id']." and conv_unit =".$unit_id." and product_id=".$product_id;
		$result2=$dbcon->query($query2);
		$row2=brp_mysqli_fetch_assoc($result2);
		
		$query3="select IFNULL(sum(approve_base_stock),0) as base_usedqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_deduct." ".$where_process_stock_id."  ".$where_process_id." and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result3=$dbcon->query($query3);
		$row3=brp_mysqli_fetch_assoc($result3);
		
		$query4="select IFNULL(sum(approve_convert_stock),0) as conv_usedqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=conv_unit  ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_deduct." ".$where_process_stock_id."  ".$where_process_id." and company_id=".$_SESSION['company_id']." and stock_flage=2 and conv_unit =".$unit_id." and product_id=".$product_id;

		$result4=$dbcon->query($query4);
		$row4=brp_mysqli_fetch_assoc($result4);
		
		$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
		}else{

			$query1="select IFNULL(sum(base_stock),0) as base_addqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=1 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id."  ".$where_process_id."  and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($result1);
		
		$query2="select IFNULL(sum(conv_stock),0) as conv_addqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=conv_unit  and stock_flage=1 ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id."  ".$where_process_id."  and company_id=".$_SESSION['company_id']." and conv_unit =".$unit_id." and product_id=".$product_id;
		$result2=$dbcon->query($query2);
		$row2=brp_mysqli_fetch_assoc($result2);
		
		$query3="select IFNULL(sum(base_stock),0) as base_usedqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_deduct." ".$where_process_stock_id."  ".$where_process_id."  and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result3=$dbcon->query($query3);
		$row3=brp_mysqli_fetch_assoc($result3);
		
		$query4="select IFNULL(sum(conv_stock),0) as conv_usedqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=conv_unit  ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_deduct." ".$where_process_stock_id."  ".$where_process_id."  and company_id=".$_SESSION['company_id']." and stock_flage=2 and conv_unit =".$unit_id." and product_id=".$product_id;

		$result4=$dbcon->query($query4);
		$row4=brp_mysqli_fetch_assoc($result4);
		
		$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);

		$query5="select IFNULL(sum(base_stock),0) as base_addqty from tbl_process_reserve_stock where stock_status=0 and stock_flage=1 and base_unit=".$unit_id." ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id."  ".$where_process_id."  and company_id=".$_SESSION['company_id']." and product_id=".$product_id;
		$result5=$dbcon->query($query5);
		$row5=brp_mysqli_fetch_assoc($result5);
		
		 $query6="select IFNULL(sum(conv_stock),0) as conv_addqty from tbl_process_reserve_stock where stock_status=0 and base_unit!=conv_unit  and stock_flage=1 ".$where_branch." ".$where_p_id." ".$where_process_reserve_id_add." ".$where_process_stock_id."  ".$where_process_id."  and company_id=".$_SESSION['company_id']." and conv_unit =".$unit_id." and product_id=".$product_id;

		$result6=$dbcon->query($query6);
		$row6=brp_mysqli_fetch_assoc($result6);
		}

		// $company_config = getCompanyConfiguration($dbcon);

		if($is_store_approval){
			$res_qty=(($row1['base_addqty'] - $row5['base_addqty'])+($row2['conv_addqty'] - $row6['conv_addqty']))-($row3['base_usedqty']+$row4['conv_usedqty']);	
		}else{
			 $res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);	
		}
		// echo ' :: ' .$res_qty . ' :: ';
		
		return $res_qty;
		
	}
	
	///////////////////////////////////////Harshil Update THis Funtion////////////////////////////////////////////////////////////////////////
/*function production_process_reseve_stock($dbcon, $unit_id, $branch_id, $p_id, $product_id, $process_id, $process_reserve_id, $process_stock_id, $is_store_approval) {
    $params = array($unit_id, $branch_id, $p_id, $process_id, $process_reserve_id, $process_stock_id, $product_id, $_SESSION['company_id']);

      $sql = "SELECT IFNULL(SUM(CASE WHEN stock_flage=1 AND base_unit=".$unit_id." THEN approve_base_stock 
                                    WHEN stock_flage=1 AND conv_unit=".$unit_id." THEN approve_convert_stock ELSE 0 END), 0) 
                   - IFNULL(SUM(CASE WHEN stock_flage=2 AND base_unit=".$unit_id." THEN approve_base_stock 
                                    WHEN stock_flage=2 AND conv_unit=".$unit_id." THEN approve_convert_stock ELSE 0 END), 0) AS res_qty
            FROM tbl_process_reserve_stock
            WHERE stock_status=0 AND company_id=".$_SESSION['company_id']." AND product_id=".$product_id;

    if (!empty($p_id)) {
        $sql .= " AND p_id=".$p_id;
        $params[] = $p_id;
    }

    if (!empty($process_id)) {
        $sql .= " AND process_id=".$process_id;
        $params[] = $process_id;
    }

    if (!empty($process_reserve_id)) {
        $sql .= " AND (process_reserve_id=".$process_reserve_id." OR perent_id=".$process_reserve_id.")";
        $params[] = $process_reserve_id;
        $params[] = $process_reserve_id;
    }

    if (!empty($branch_id)) {
        $sql .= " AND branch_id=".$branch_id;
    }

    if (!empty($process_stock_id)) {
        $sql .= " AND process_stock_id=".$process_stock_id;
        $params[] = $process_stock_id;
    }

    if ($is_store_approval) {
        $sql .= " AND stock_flage=1";
    }

    $stmt = $dbcon->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row['res_qty'];
}*/

	
	//////////////////////////////////////////////////////////////////////////////////End Thi Funcation///////////////////////////////////////////////////////////////

	// Added by Sanat :: 04-10-2021

	
	

	



/* START JAYESH 
PURPOSE : checking process enable in prodcuct type wise */




	
	function purchase_order_product_for_pending_grn($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
	{
		 $company_config = getCompanyConfiguration($dbcon);		
		 $production_pro_search = $company_config['production_pro_search'];
		 $pro_search=explode(",", $production_pro_search);
		 
		$str='';
		if(!empty($eid)){
			$grn_ids=" and grn_id!=".$eid;
		}
		if(!empty($vender_id)){
			$ven=" and op.vender_id=".$vender_id;
		}
		if(!empty($id)){
			$po=" and po.purchaseorder_id in(".$id.")";
		}
		$branch_where=" and po.branch_id=".$branch_id;
		// var_dump($company_config['po_work_order_wise']);
		if($company_config['po_work_order_wise'] == 1){
			$group=",po.po_ref_id,po.po_ref_type,res.sp_id,po.purchaseordertrn_id";
			$left="left join tbl_request_product as res on res.rp_id=po.po_ref_id  left join tbl_set_main_process as setm on setm.sp_id=res.sp_id";
			$pera=",res.indent_no,res.sp_id,res.indent_date,setm.po_req_no,setm.po_req_date,res.product_remark";
		}
		//$branch_where=" and branch_id=".$branch_id;
		$query="select po.*,sum(po.product_qty)as produ_qty,sum(po.product_conv_qty)as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,group_concat(po.purchaseordertrn_id ORDER BY po.purchaseordertrn_id ASC) as trn_id,group_concat(po.po_ref_id ORDER BY po.po_ref_id DESC) as ref_id,con_unit.unit_name as conv_unit_name,op.po_type,group_concat(op.purchaseorder_no) as purchaseorder_no,p.batch_wise_stock_manage,p.product_icode,p.product_mat_center, dr.drawing_number".$pera." from tbl_purchaseordertrn as po 
		left join product_mst as p on p.product_id=po.product_id
		left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
		left join tbl_category as tc on p.product_category=tc.cat_id 
		left join unit_mst as unit on unit.unitid=po.unit_id
		left join unit_mst as con_unit on con_unit.unitid=po.conv_unit_id
		left join tbl_purchaseorder as op on op.purchaseorder_id=po.purchaseorder_id ".$left."
		where op.po_approval_status=1 and po.used_status=0 and po.remove_from_grn = 0 and purchaseordertrn_status=0 ".$branch_where." ".$ven." ".$po." group by po.product_id,po.unit_id,po.conv_unit_id".$group." order by po.purchaseorder_id asc";
		$rs_product=$dbcon->query($query);
		  // echo $query;die;
		
		$cnt=1;
		while($row=brp_mysqli_fetch_array($rs_product))
		{
			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$query1="select sum(product_qty) as done_qty,sum(product_conv_qty) as conv_done_qty from tbl_grn_sub_trn as po where status=0 and purchaseordertrn_id in (".$row['trn_id'].")";
			$rs_product1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_array($rs_product1);
			
			$pending_qty=$row['produ_qty']-$row1['done_qty'];
			$pending_conv_qty=$row['produ_con_qty']-$row1['conv_done_qty'];

			$is_diff_unit = 0;
			$diff_unit_type = "";
			if($row["unit_id"]!=$row["conv_unit_id"]){
  				$is_diff_unit = 1;
			}

			if($row["rate_unit"]==$row["conv_unit_id"]){
  				$diff_unit_type = "conv";
			}else{
				$diff_unit_type = "base";
			}

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
			if($company_config['grn_diff_from_po'] == '1'){ 
				$btn_delete =' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="po_short_close_for_grn('.$row['purchaseorder_id'].','.$row['trn_id'].')"><i class="fa fa-trash-o"></i></button>';
			}else{
				$btn_delete = "<button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button>";	
			}

			$drawing_number = "";
			$item_code = "";
			$product_remark = "";
			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],"-1");
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}

			if(!empty($row['product_remark'])){
	        	$product_remark = '</br> <span style="color:green"> Product Remark : </span>'. $row['product_remark'];
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
			}else{
				$rol['grn_godown'] = $row['product_mat_center'];
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
			$display_none = '';
			if($row['po_type']=='1'){ 
				$display_none .= "display:none"; 
			}
			else{ 
				$display_none.= "display:block";
			}

			$workorder ="";
			if($company_config['po_work_order_wise'] == 1){
				$workorder = "<p> Workorder No. : ". $row['po_req_no'] ."</p>";
			}

			$purchaseorder_no = "";
			if(!empty($row['purchaseorder_no'])){
				$purchaseorder_no =  "<p> Purchaseorder No. : ". $row['purchaseorder_no'] ."</p>";
			}

			if($pending_qty1 > 0){
			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']." </td>
						<td>".$row['product_name'].' '.$item_code.' '.$drawing_number.$workorder.$purchaseorder_no.$product_remark."
						
						<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
						<td>".$cat_name."</td>
						<td>";

						if($row['rate_unit'] == $row["conv_unit_id"]){
							$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['rate_unit'] == $row["unit_id"]){
							$str.="
									<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
								".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
								</div>";
						}
						$str .= "<input type='hidden' class='form-control' name='grn_rate_unit[]' id='grn_rate_unit$cnt' value='".$row['rate_unit']."' />";
							
							 if($row["unit_id"]!=$row["conv_unit_id"]){ 
								if($row['rate_unit'] != $row["conv_unit_id"]){
									$str.="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404; display:none;'>
										</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
									</div>";
								} else if($row['rate_unit'] != $row["unit_id"]){
									$str.="</br>
											<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219; display:none;'>
										".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
										</div>";
								}
								
							 } 
						$str.="</td>
						<td>";
						if($row['rate_unit'] == $row["conv_unit_id"]){
						$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['rate_unit'] == $row["unit_id"]){
							$str.="
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
									".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
								</div>";
						}
							
							if($row["unit_id"]!=$row["conv_unit_id"]){
							
								if($row['rate_unit'] != $row["conv_unit_id"]){
						$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['rate_unit'] != $row["unit_id"]){
							$str.="</br>
								<div  class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
									".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
								</div>";
						}
							}
						$str.="<td>";
						$product_name = '"'.$row['product_name'].'"';	
						
						$diff_unit_type = '"'.$diff_unit_type.'"';
						$bt = "";
						if($row['rate_unit'] == $row["conv_unit_id"]){
							$unit_name = '"'.$row['conv_unit_name'].'"';
							$diff_unit_name = '"'.$row['unit_name'].'"';
							
							$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['produ_con_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
							".$row['conv_unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["conv_unit_id"].",".$is_diff_unit.",".$pending_qty.",".$diff_unit_name.",".$row["unit_id"].",".$diff_unit_type.",\"\",".$row['trn_id'].");' ><i class='fa fa-plus'></i></button>";
									
							}

							$str .="<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
						}else if($row['rate_unit'] == $row["unit_id"]){
							$unit_name = '"'.$row['unit_name'].'"';	
							$diff_unit_name = '"'.$row['conv_unit_name'].'"';
							$str .="<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["unit_id"].",".$is_diff_unit.",".$pending_conv_qty.",".$diff_unit_name.",".$row["conv_unit_id"].",".$diff_unit_type.",\"\",".$row['trn_id'].");' ><i class='fa fa-plus'></i></button>";
							}

							$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						}
							
						if($row["unit_id"]!=$row["conv_unit_id"]){
								if($row['rate_unit'] != $row["conv_unit_id"]){
									$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
								<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['conv_unit_name']."
								<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
								}else if($row['rate_unit'] != $row["unit_id"]){
									$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
								<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
								".$row['unit_name']."
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
								}
							
						}else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";*/

							if($row['rate_unit'] != $row["conv_unit_id"]){

								$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";

								}else{

									$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
								}
						}
							
						$str.="</td>
						<!--<td>
							<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
							<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
							$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
							$str.="</select>
							<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
							<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
							<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
							<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
							
						</td>
						<td> ".$btn_delete."
							<!-- <button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button> -->
						</td>
					</tr>";
			}
			$cnt++;	
		}
		$str .= load_scrap_grn_product_data($cnt,$dbcon,$type);
		return $str;
	}


	// Maulik
				function purchaseorder_delivery_datewise_used_qty_update($dbcon,$purchaseordertrn_id,$base_stock,$product_base_unit,$conv_stock=0,$product_conv_unit=0){
					$query = "select po_delivery_date_id,(product_qty-used_qty) as pending_qty, used_qty,product_qty,delivery_date from tbl_purchaseorder_delivery_date where po_delivery_date_status=0 and grn_status=0 and  purchaseordertrn_id=".$purchaseordertrn_id." order by delivery_date asc";

					$result=$dbcon->query($query);
					while($row=brp_mysqli_fetch_assoc($result)){
						$used_qty=0;
						if($base_stock > 0 || $conv_stock > 0){
							if($product_base_unit == $row['unit_id']){
								if($base_stock>=$row['pending_qty']){
									$used_qty=$row['pending_qty'];
								}else{
									$used_qty=$base_stock;
								}
								$base_stock=$base_stock-$used_qty;
							}else{
								if($conv_stock>=$row['pending_qty']){
										$used_qty=$row['pending_qty'];
								}else{
									$used_qty=$conv_stock;
								}
								$conv_stock=$conv_stock-$used_qty;
							}
							$info2['used_qty'] = $row['used_qty']+$used_qty;

							if($info2['used_qty'] == $row['product_qty']){
								$info2['grn_status'] = 1;
								$today_date = date('Y-m-d');
								$delivery_date = date('Y-m-d',strtotime($row['delivery_date']));
								$date1=date_create($today_date);
								$date2=date_create($delivery_date);

								$diff=date_diff($date1,$date2);

								$delay_days = $diff->format("%a");
								$info2['delay_days']=$delay_days;
							}
							update_record('tbl_purchaseorder_delivery_date', $info2,"po_delivery_date_id=".$row['po_delivery_date_id'] , $dbcon);
						}

					}
				}


	// Added by Sanat :: 04-10-2021

				function store_request_approval_pending_count($dbcon,$process_id,$process_type,$type,$is_store_approval){

					if($_SESSION['user_type']!='2'){
						$check_branch = check_branch('',$_SESSION['branch_id']);
					}
					$s_ql = "select if(sum(base_qty), sum(base_qty),0) as total_qty from tbl_store_request 			
					where process_id=".$process_id." ".$check_branch." and company_id=".$_SESSION['company_id']." and store_request_status != 2" ;
		// echo $s_ql;
					$q=$dbcon->query($s_ql);
		// $cnt=brp_mysqli_num_rows($q);
					$rel=brp_mysqli_fetch_array($q);
					$total_request_qty=$rel['total_qty'];


					return $total_request_qty;
				}

				function store_release_count($dbcon,$process_id,$process_type,$type,$is_store_approval){

					if($_SESSION['user_type']!='2'){
						$check_branch = check_branch('',$_SESSION['branch_id']);
					}
					$s_ql = "select if(sum(release_qty), sum(release_qty),0) as total_release_qty from tbl_store_request 			
					where process_id=".$process_id." ".$check_branch." and company_id=".$_SESSION['company_id']." and store_request_status != 2" ;
		// echo $s_ql;
					$q=$dbcon->query($s_ql);
		// $cnt=brp_mysqli_num_rows($q);
					$rel=brp_mysqli_fetch_array($q);
		// $total_request_qty=$rel['total_qty'];
					$total_release_qty=$rel['total_release_qty'];


					return $total_release_qty;
				}

				function store_request_approval_pending_count_by_pid($dbcon,$process_id,$p_id,$process_type,$type,$is_store_approval){

					if($_SESSION['user_type']!='2'){
						$check_branch = check_branch('',$_SESSION['branch_id']);
					}
					$s_ql = "select if(sum(base_qty), sum(base_qty),0) as total_qty,if(sum(release_qty), sum(release_qty),0) as total_release_qty from tbl_store_request 			
					where process_id=".$process_id." ".$check_branch." and company_id=".$_SESSION['company_id']." and store_request_status != 2 and p_id in (".$p_id.")";
		// echo $s_ql;
					$q=$dbcon->query($s_ql);
		// $cnt=brp_mysqli_num_rows($q);
					$rel=brp_mysqli_fetch_array($q);
					$total_request_qty=$rel['total_qty'] - $rel['total_release_qty'];


					return $total_request_qty;
				}

				function count_store_release_material($dbcon){

					$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
					$whre="";
					if(!empty($_SESSION['branch_id'])){
						$whre=" and branch_id=".$branch_id;
					}	

					$query="select count(store_aprv_log_id) as total  from tbl_store_request_aprv_log where company_id=".$_SESSION['company_id'].$whre;

					$rs_cust=$dbcon->query($query);	
					$rel=brp_mysqli_fetch_array($rs_cust);

					$total=$rel['total'];

					if($total==0)
					{
						return 0;
					}
					else
					{
						return $total;
					}	
				}

				function update_product_setting($dbcon,$product_id,$setting){
	// $setting  = process_product,product_qc

					$qry = "SELECT product_setting_check from product_mst where product_id = " . $product_id;
					$result=$dbcon->query($qry);
					$rel=brp_mysqli_fetch_array($result);

					$product_setting = $rel['product_setting_check'];

					if(empty($product_setting)){
						$info['product_setting_check'] = $setting;	
						update_record('product_mst', $info,"product_id=" . $product_id, $dbcon);
					}else{
						$pro_setting = explode(",",$rel['product_setting_check']);
						if (!in_array($setting, $pro_setting)){
							array_push($pro_setting,$setting);
							$update_setting = implode(',',$pro_setting);
							$info['product_setting_check'] = $update_setting;
							update_record('product_mst', $info,"product_id=" . $product_id, $dbcon);	
						}
					}
				}


/* START JAYESH 
PURPOSE : checking process enable in prodcuct type wise */



function check_process_product_type($dbcon,$product_type){
	// $setting  = process_product,product_qc

	$qry = "SELECT process_required from pro_ms_product_type where product_type_id = " .$product_type;
	$result=$dbcon->query($qry);
	$rel=brp_mysqli_fetch_array($result);
	//echo "<pre>"; print_r($rel);
	return $rel['process_required'];

	
}
function sales_order_product_for_pending_grn($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
{
	$company_config = getCompanyConfiguration($dbcon);		
	$production_pro_search = $company_config['production_pro_search'];
	$pro_search=explode(",", $production_pro_search);

	$str='';
	if(!empty($vender_id)){
		$ven=" and grn_trn.customer_id=".$vender_id;
	}
	if(!empty($id)){
		$po=" and grn_trn.sales_order_id in(".$id.")";
	}
	$branch_where=" and grn_trn.branch_id=".$branch_id;

	$query="select grn_trn.*,grn_trn.product_qty as produ_qty,grn_trn.product_conv_qty as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,con_unit.unit_name as conv_unit_name, p.batch_wise_stock_manage,p.product_icode,p.product_mat_center, dr.drawing_number from tbl_grn_trn as grn_trn 
		left join product_mst as p on p.product_id=grn_trn.product_id
		left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
		left join tbl_category as tc on p.product_category=tc.cat_id 
		left join unit_mst as unit on unit.unitid=grn_trn.unit_id
		left join unit_mst as con_unit on con_unit.unitid=grn_trn.product_conv_unit
		where grn_trn.ref_type = 5 and grn_trn.grn_trn_status=3 ".$branch_where." ".$ven." ".$po; 
		$rs_product=$dbcon->query($query);		

		$cnt=1;


		while($row=brp_mysqli_fetch_array($rs_product))
		{
			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$pending_qty=$row['produ_qty'];
			$pending_conv_qty=$row['produ_con_qty'];

			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id']);
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}

			$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$row['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$row['product_icode'].")";
				        }

				
				if($row['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}
				
			$tolerance=get_pro_field($dbcon,$row['product_id'],'tolerance');
			$maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
			$minimum_tolerance=get_pro_field($dbcon,$row['product_id'],'minimum_tolerance');
			if($tolerance=="1"){
				//$maximum_tolerance=get_pro_field($dbcon, $row['product_id'], 'maximum_tolerance');
				$pending_qty1=$pending_qty;
			}else{
				$pending_qty1=$pending_qty;
			}
			/* Code By Umair: 29/10/2020 
			   Comment: I have removed the max value from the input tag for tolerance functionality for grn module.	
			   ".$pending_qty1."
			*/
			if(empty($row['grn_godown'])){
				$row['grn_godown'] = $row['product_mat_center'];				
			}

			$display_none = '';
			if($row['po_type']=='1'){ 
				$display_none .= "display:none"; 
			}
			else{ 
				$display_none.= "display:block";
			}


			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
						<td>".$row['product_name'].' '.$item_code.' '.$drawing_number."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
						<td>".$cat_name."</td>
						<td>";

							if($row['rate_unit'] == $row["product_conv_unit"]){
								$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".number_format($row['produ_con_qty'],4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";

							}else if($row['rate_unit'] == $row["unit_id"]){
								$str.="</br>
									<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
								".number_format($row['produ_qty'],4,".","")." </br> <span>".$row['unit_name']."</span> 
								</div>";
							}
							 if($row["unit_id"]!=$row["product_conv_unit"]){ 
								if($row['rate_unit'] != $row["product_conv_unit"]){
										$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
									</br> ".number_format($row['produ_con_qty'],4,".","")." </br> ".$row['conv_unit_name']." 
								</div>";
									} else if($row['rate_unit'] != $row["unit_id"]){
										$str.="</br>
										<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
										".number_format($row['produ_qty'],4,".","")." </br> <span>".$row['unit_name']."</span> 
										</div>";
									}
							 } 
						$str.="</td>
						<td>";
							if($row['rate_unit'] == $row["product_conv_unit"]){
								$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".number_format($pending_conv_qty,4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";
							}else if($row['rate_unit'] == $row["unit_id"]){
								$str.="</br>
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
									".number_format($pending_qty,4,".","")." </br> ".$row['unit_name']." 
								</div>";
							}	
							
							
							if($row["unit_id"]!=$row["product_conv_unit"]){
								if($row['rate_unit'] != $row["product_conv_unit"]){
									$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
								</br> ".number_format($pending_conv_qty,4,".","")." </br> ".$row['conv_unit_name']." 
							</div>";
								}else if($row['rate_unit'] != $row["unit_id"]){
									$str.="</br>
									<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
										".number_format($pending_qty,4,".","")." </br> ".$row['unit_name']." 
									</div>";
								}
							}
						$str.="<td>";
						$product_name = '"'.$row['product_name'].'"';
						
						if($row['rate_unit'] == $row["product_conv_unit"]){
							$unit_name = '"'.$row['conv_unit_name'].'"';	
							$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input readonly type='number' min='0' max='' data-pendingqty='".number_format($pending_conv_qty,4,".","")."' data-pid='".$row['product_id']."' data-qty='".$row['produ_conv_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement entered_qty".$cnt."' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
							".$row['conv_unit_name'];

							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["product_conv_unit"].");' ><i class='fa fa-plus'></i></button>";
									
							}
							
							$str .="<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_conv_qty']."' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />
							</div>
							";
						}else if($row['rate_unit'] == $row["unit_id"]){
							$unit_name ='"'.$row['unit_name'].'"';
							$str.="<div  style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input readonly type='number' min='0' max='' data-pendingqty='".number_format($pending_qty1,4,".","")."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['unit_name'];

							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",'".$row['product_name']."',".$product_name.",".$unit_name.",,".$row["unit_id"].");' ><i class='fa fa-plus'></i></button>";
									
							}
							
							$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						}

						
						if($row["unit_id"]!=$row["product_conv_unit"]){

							if($row['rate_unit'] != $row["product_conv_unit"]){
								$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
								<input type='number' readonly class='form-control'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['unit_name']."
								<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_conv_qty']."' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />
							</div>
							";
							}else if($row['rate_unit'] != $row["unit_id"]){
								$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
								<input readonly type='number' class='form-control'  name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />	
								".$row['unit_name']."
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
							}
							
						}else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />";*/
							if($row['rate_unit'] != $row["product_conv_unit"]){
									$str.="<input type='hidden' min='0' max='' class='form-control' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row["product_conv_unit"]."' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />";
							}else {
								$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
							}
						}
							
						$str.="</td>
						<!--<td>
							<input type='number' min='0' max='' data-pendingqty='".number_format($pending_qty1,4,".","")."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
							<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
							$str.= get_all_godown($dbcon,$row['grn_godown'],1);
							$str.="</select>
							<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
							<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$row['grn_trn_id']."' />
							<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$row['product_qc']."' />
							<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
							<input type='hidden' class='form-control' name='grn_rate_unit[]' id='grn_rate_unit$cnt' value='".$row['rate_unit']."' />
							
						</td>
						<td>
							<button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_direct_grn_data(".$row['grn_trn_id'].");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button>
						</td>
					</tr>";
			
			$cnt++;	
		}

	// $str .= load_scrap_grn_product_data($cnt,$dbcon,$type);
		return $str;

}
				function sales_order_product_for_pending_grn_old($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
				{

					$company_config = getCompanyConfiguration($dbcon);		
					$production_pro_search = $company_config['production_pro_search'];
					$pro_search=explode(",", $production_pro_search);

					$str='';
					if(!empty($eid)){
						$grn_ids=" and grn_id!=".$eid;
					}
					if(!empty($vender_id)){
						$ven=" and os.cust_id=".$vender_id;
					}
					if(!empty($id)){
						$po=" and so.sales_ordertrn_id=".$id;
					}
					$branch_where=" and so.branch_id=".$branch_id;

					$query="select so.*,sum(so.product_qty)as produ_qty,sum(so.product_conv_qty)as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,group_concat(so.sales_ordertrn_id ORDER BY so.sales_ordertrn_id ASC) as trn_id,con_unit.unit_name as conv_unit_name, p.batch_wise_stock_manage,p.product_icode, dr.drawing_number from tbl_sales_ordertrn as so 
					left join product_mst as p on p.product_id=so.product_id
					left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
					left join tbl_category as tc on p.product_category=tc.cat_id 
					left join unit_mst as unit on unit.unitid=so.unit_id
					left join unit_mst as con_unit on con_unit.unitid=so.conv_unit_id
					left join tbl_sales_order as os on os.sales_order_id=so.sales_order_id
					where os.approve_status=3  and sales_ordertrn_status=0 ".$branch_where." ".$ven." ".$po." group by so.product_id,so.unit_id,so.conv_unit_id"; 
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

			$drawing_number = "";
			$item_code = "";
			if(in_array('drawing',$pro_search)){
				$drawing_number = " -- (".$row['drawing_number'].")";
			}
			if(in_array('item',$pro_search)){
				$item_code = " -- (".$row['product_icode'].")";
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
			   $display_none = '';
			   if($row['po_type']=='1'){ 
			   	$display_none .= "display:none"; 
			   }
			   else{ 
			   	$display_none.= "display:block";
			   }
			   $str.="<tr id='trid".$cnt."'>
			   <!--<td>".$cnt."</td>-->
			   <td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
			   <td>".$row['product_name'].' '.$item_code.' '.$drawing_number."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
			   <td>".$cat_name."</td>
			   <td>";

			   if($row['rate_unit'] == $row["conv_unit_id"]){
			   	$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
			   	</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
			   	</div>";

			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$str.="</br>
			   	<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
			   	".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
			   	</div>";
			   }
			   if($row["unit_id"]!=$row["conv_unit_id"]){ 
			   	if($row['rate_unit'] != $row["conv_unit_id"]){
			   		$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
			   		</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
			   		</div>";
			   	} else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="</br>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
			   		".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
			   		</div>";
			   	}
			   } 
			   $str.="</td>
			   <td>";
			   if($row['rate_unit'] == $row["conv_unit_id"]){
			   	$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
			   	</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
			   	</div>";
			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$str.="</br>
			   	<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
			   	".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
			   	</div>";
			   }	


			   if($row["unit_id"]!=$row["conv_unit_id"]){
			   	if($row['rate_unit'] != $row["conv_unit_id"]){
			   		$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
			   		</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
			   		</div>";
			   	}else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="</br>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
			   		".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
			   		</div>";
			   	}
			   }
			   $str.="<td>";
			   $product_name = '"'.$row['product_name'].'"';

			   if($row['rate_unit'] == $row["conv_unit_id"]){
			   	$unit_name = '"'.$row['conv_unit_name'].'"';	
			   	$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
			   	<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_conv_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement entered_qty".$cnt."' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
			   	".$row['conv_unit_name'];

			   	if($row['batch_wise_stock_manage'] == 1){
			   		$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["conv_unit_id"].");' ><i class='fa fa-plus'></i></button>";

			   	}

			   	$str .="<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
			   	<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
			   	</div>
			   	";
			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$unit_name ='"'.$row['unit_name'].'"';
			   	$str.="<div  style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
			   	<input type='number' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
			   	".$row['unit_name'];

			   	if($row['batch_wise_stock_manage'] == 1){
			   		$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",'".$row['product_name']."',".$product_name.",".$unit_name.",,".$row["unit_id"].");' ><i class='fa fa-plus'></i></button>";

			   	}

			   	$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
			   	<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
			   	</div>
			   	";
			   }


			   if($row["unit_id"]!=$row["conv_unit_id"]){

			   	if($row['rate_unit'] != $row["conv_unit_id"]){
			   		$str.="<br/>
			   		<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
			   		<input type='number' class='form-control'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
			   		".$row['unit_name']."
			   		<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
			   		<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
			   		</div>
			   		";
			   	}else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="<br/>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
			   		<input type='number' class='form-control'  name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
			   		".$row['unit_name']."
			   		<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
			   		<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
			   		</div>
			   		";
			   	}

			   }else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";*/
							if($row['rate_unit'] != $row["conv_unit_id"]){
								$str.="<input type='hidden' min='0' max='' class='form-control' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." />
								<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";
							}else {
								$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
							}
						}

						$str.="</td>
						<!--<td>
						<input type='number' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
						<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
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

				function direct_order_product_for_pending_grn($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id){
					$company_config = getCompanyConfiguration($dbcon);		
					$production_pro_search = $company_config['production_pro_search'];
					$pro_search=explode(",", $production_pro_search);

					$str='';
					$ven = "";
	// if(!empty($vender_id)){
	// 	$ven=" and grn_trn.customer_id=".$vender_id;
	// }
					if(!empty($id)){
						$po=" and grn_trn.sales_order_id in(".$id.")";
					}
					$branch_where=" and grn_trn.branch_id=".$branch_id;

					$query="select grn_trn.*,grn_trn.product_qty as produ_qty,grn_trn.product_conv_qty as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,con_unit.unit_name as conv_unit_name, p.batch_wise_stock_manage,p.product_icode, p.product_mat_center, dr.drawing_number from tbl_grn_trn as grn_trn 
					left join product_mst as p on p.product_id=grn_trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
					left join tbl_category as tc on p.product_category=tc.cat_id 
					left join unit_mst as unit on unit.unitid=grn_trn.unit_id
					left join unit_mst as con_unit on con_unit.unitid=grn_trn.product_conv_unit
					where grn_trn.ref_type = 4 and grn_trn.grn_trn_status=3 ".$branch_where." ".$ven." ".$po; 
					$rs_product=$dbcon->query($query);		

					$cnt=1;


					while($row=brp_mysqli_fetch_array($rs_product))
					{
						$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
						$pending_qty=$row['produ_qty'];
						$pending_conv_qty=$row['produ_con_qty'];

						$is_diff_unit = 0;
						$diff_unit_type = "";
						if($row["unit_id"]!=$row["product_conv_unit"]){
			  				$is_diff_unit = 1;
						}

						if($row["rate_unit"]==$row["product_conv_unit"]){
			  				$diff_unit_type = "conv";
						}else{
							$diff_unit_type = "base";
						}

						$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],'-1');
						if($qc_paramter_info=='1')
						{
							$qc_st="yes";
							$sty="display:none;";
						}else{
							$qc_st="no";
							$sty="";
						}

						$drawing_number = "";
						$item_code = "";
						if(in_array('drawing',$pro_search)){
							$drawing_number = " -- (".$row['drawing_number'].")";
						}
						if(in_array('item',$pro_search)){
							$item_code = " -- (".$row['product_icode'].")";
						}


						if($row['product_qc']==1){
							$ronly="readonly";
						}else{
							$ronly="";
						}

						$tolerance=get_pro_field($dbcon,$row['product_id'],'tolerance');
						$maximum_tolerance=get_pro_field($dbcon,$row['product_id'],'maximum_tolerance');
						$minimum_tolerance=get_pro_field($dbcon,$row['product_id'],'minimum_tolerance');
						if($tolerance=="1"){
							// $maximum_tolerance=get_pro_field($dbcon, $row['product_id'], 'maximum_tolerance');
							$pending_qty1=$pending_qty;
						}else{
							$pending_qty1=$pending_qty;
						}

						if(empty($row['grn_godown'])){
							$row['grn_godown'] = $row['product_mat_center'];
						}
			/* Code By Umair: 29/10/2020 
			   Comment: I have removed the max value from the input tag for tolerance functionality for grn module.	
			   ".$pending_qty1."
			*/
			   $display_none = '';
			   if($row['po_type']=='1'){ 
			   	$display_none .= "display:none"; 
			   }
			   else{ 
			   	$display_none.= "display:block";
			   }
			   $str.="<tr id='trid".$cnt."'>
			   <!--<td>".$cnt."</td>-->
			   <td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
			   <td>".$row['product_name'].' '.$item_code.' '.$drawing_number."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
			   <td>".$cat_name."</td>
			   <td>";

			   if($row['rate_unit'] == $row["product_conv_unit"]){
			   	$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
			   	</br> ".number_format($row['produ_con_qty'],4,".","")." </br> ".$row['conv_unit_name']." 
			   	</div>";

			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$str.="</br>
			   	<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
			   	".number_format($row['produ_qty'],4,".","")." </br> <span>".$row['unit_name']."</span> 
			   	</div>";
			   }
			   if($row["unit_id"]!=$row["product_conv_unit"]){ 
			   	if($row['rate_unit'] != $row["product_conv_unit"]){
			   		$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
			   		</br> ".number_format($row['produ_con_qty'],4,".","")." </br> ".$row['conv_unit_name']." 
			   		</div>";
			   	} else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="</br>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
			   		".number_format($row['produ_qty'],4,".","")." </br> <span>".$row['unit_name']."</span> 
			   		</div>";
			   	}
			   } 
			   $str.="</td>
			   <td>";
			   if($row['rate_unit'] == $row["product_conv_unit"]){
			   	$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
			   	</br> ".number_format($pending_conv_qty,4,".","")." </br> ".$row['conv_unit_name']." 
			   	</div>";
			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$str.="</br>
			   	<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'>
			   	".number_format($pending_qty,4,".","")." </br> ".$row['unit_name']." 
			   	</div>";
			   }

			 
			   if($row["unit_id"]!=$row["product_conv_unit"]){
			   	if($row['rate_unit'] != $row["product_conv_unit"]){
			   		$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
			   		</br> ".number_format($pending_conv_qty,4,".","")." </br> ".$row['conv_unit_name']." 
			   		</div>";
			   	}else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="</br>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
			   		".number_format($pending_qty,4,".","")." </br> ".$row['unit_name']." 
			   		</div>";
			   	}
			   }
			   $str.="<td>";
			   $product_name = '"'.$row['product_name'].'"';
			   $diff_unit_type = '"'.$diff_unit_type.'"';

			   if($row['rate_unit'] == $row["product_conv_unit"]){
			   	$unit_name = '"'.$row['conv_unit_name'].'"';	
			   	$diff_unit_name = '"'.$row['unit_name'].'"';	
			   	$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
			   	<input readonly type='number' min='0' max='' data-pendingqty='".number_format($pending_conv_qty,4,".","")."' data-pid='".$row['product_id']."' data-qty='".$row['produ_conv_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement entered_qty".$cnt."' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
			   	".$row['conv_unit_name'];

			   	if($row['batch_wise_stock_manage'] == 1){
			   		$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["product_conv_unit"].",".$is_diff_unit.",".$pending_qty.",".$diff_unit_name.",".$row["unit_id"].",".$diff_unit_type.");' ><i class='fa fa-plus'></i></button>";

			   	}

			   	$str .="<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_conv_qty']."' />
			   	<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />
			   	</div>
			   	";
			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$unit_name ='"'.$row['unit_name'].'"';
			   	$diff_unit_name = '"'.$row['conv_unit_name'].'"';	
			   	$str.="<div  style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
			   	<input readonly type='number' min='0' max='' data-pendingqty='".number_format($pending_qty1,4,".","")."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
			   	".$row['unit_name'];

			   	if($row['batch_wise_stock_manage'] == 1){
			   		$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["unit_id"].",".$is_diff_unit.",".$pending_conv_qty.",".$diff_unit_name.",".$row["product_conv_unit"].",".$diff_unit_type.");' ><i class='fa fa-plus'></i></button>";

			   	}

			   	$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
			   	<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
			   	</div>
			   	";
			   }


			   if($row["unit_id"]!=$row["product_conv_unit"]){

			   	if($row['rate_unit'] != $row["product_conv_unit"]){
			   		$str.="<br/>
			   		<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
			   		<input type='number' readonly class='form-control'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
			   		".$row['unit_name']."
			   		<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_conv_qty']."' />
			   		<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />
			   		</div>
			   		";
			   	}else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="<br/>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
			   		<input readonly type='number' class='form-control'  name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
			   		".$row['unit_name']."
			   		<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
			   		<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
			   		</div>
			   		";
			   	}

			   }else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />";*/
							if($row['rate_unit'] != $row["product_conv_unit"]){
								$str.="<input type='hidden' min='0' max='' class='form-control' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."' ".$ronly." />
								<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_conv_qty']."' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />";
							}else {
								$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
							}
						}

						$str.="</td>
						<!--<td>
						<input type='number' min='0' max='' data-pendingqty='".number_format($pending_qty1,4,".","")."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
						<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
						<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
						$str.= get_all_godown($dbcon,$row['grn_godown'],1);
						$str.="</select>
						<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
						<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$row['grn_trn_id']."' />
						<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$row['product_qc']."' />
						<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
						<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
						<input type='hidden' class='form-control' name='grn_rate_unit[]' id='grn_rate_unit$cnt' value='".$row['rate_unit']."' />

						</td>
						<td>
						<button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_direct_grn_data(".$row['grn_trn_id'].");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button>
						</td>
						</tr>";

						$cnt++;	
					}


					return $str;
				}

				function direct_order_product_for_pending_grn_old($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
				{
					$str='';
					$cnt=1;

					if(!empty($eid)){
						$grn_ids="  g.grn_id=".$eid;

						$query="select * from tbl_grn as g inner join tbl_grn_trn as gt on g.grn_id = gt.grn_id where $grn_ids "; 

						$rs_product=$dbcon->query($query);
						$row=brp_mysqli_fetch_array($rs_product);
						$product_id = $row['product_id'];
						$product_qty = $row['product_qty'];
						$product_conv_qty = $row['product_conv_qty'];

		//$product_id = $row['product_id'];
					}

					$str.="<tr id='trid".$cnt."'>
					<!--<td>".$cnt."</td>-->
					<td><select class='form-control select4' title='Select product' name='grn_ptype[]' id='grn_ptype$cnt' onchange='load_product(this.value,$cnt)'>".get_product_type_company($dbcon,'','')."</select></td>
					<td><select class='form-control select4'  title='Select product' name='grn_pid[]' id='grn_pid$cnt' onchange='load_product_category(this.value,$cnt)'>".getproduct($dbcon,$product_id)."</select></td>
					<td><input class='form-control' type='text' name='grn_pcatid[]' id='grn_pcat$cnt' value='' ></td>

					<td><input class='form-control' type='text' name='grn_totlqt[]' id='grn_totlqt$cnt' ></td>
					<td><input class='form-control' type='text' name='grn_penqty[]' id='grn_penqty$cnt' value='$product_qty'></td>
					<td><input class='form-control' type='text' name='conv_grn_qty[]' id='conv_grn_qty$cnt' onkeyup='product_convert_qty(1,".$cnt.");' value='$product_conv_qty' ><input class='form-control' type='hidden' name='grn_qty[]' id='grn_qty$cnt' ><input class='form-control' type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' ><input class='form-control' type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' ><input class='form-control' type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' ></td>

					<td><select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
					$str.= get_all_godown($dbcon,$row['grn_godown'],1);
					$str.="</select></td>
					<!--<td>
					<button type='button' class='btn btn-round btn-success btn-xs' onclick='add_data(".$cnt.");' id='fieldadd".$cnt."'><i class='fa fa-plus'></i></button>
					</td>-->";



					return $str;
				}
// Added by Sanat :: 14-12-2021


function returnable_chalan_pending_grn($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
	{
		$ret_id = "";
		if(!empty($id)){
			$ret_id=" and g.returnable_id in(".$id.")";
		}
$ven="";
		if(!empty($vender_id)){
			$ven=" and chn.cust_id=".$vender_id;
		}
		$str='';
	 $query="select g.*,con_unit.unit_name as conv_unit_name,p.batch_wise_stock_manage,tc.cat_name,p.product_name,p.product_type,unit.unit_name,p.product_base_unit as unit_id,p.product_conv_unit as conv_unit_id,p.product_mat_center from tbl_returnable_channal_item as g 
		left join tbl_returnable_channal as chn on chn.id = g.returnable_id 
		left join product_mst as p on p.product_id=g.item_id
		left join tbl_category as tc on p.product_category=tc.cat_id 
		left join unit_mst as unit on unit.unitid=g.item_unit_id
		left join unit_mst as con_unit on con_unit.unitid=p.product_conv_unit
		where chn.returnable_type = 'returnable' and g.status =0 and g.approve_status=1 and g.grn_status= 0 and g.remove_from_grn = 0 ".$ret_id.$ven; 
		$rs_product=$dbcon->query($query);
		
		
		$cnt=1;
		while($row=brp_mysqli_fetch_array($rs_product))
		{
			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';

			 $query1 = "select IFNULL(sum(po.product_qty),0) as done_qty,IFNULL(sum(po.product_conv_qty),0) as conv_done_qty from tbl_grn_sub_trn as po 
				left join tbl_grn_trn as trn on trn.grn_trn_id = po.grn_trn_id 
				left join tbl_grn as grn on grn.grn_id = trn.grn_id 
				where grn.ref_type = 6 and po.status=0 and trn.returnable_trn_id in (".$row['id'].")";	

		/*	$query1 = "select IFNULL(sum(po.product_qty),0) as done_qty,IFNULL(sum(po.product_conv_qty),0) as conv_done_qty from tbl_grn_sub_trn as po left join tbl_grn_trn as trn on trn.grn_trn_id = po.grn_trn_id left join tbl_grn as grn on grn.grn_id = trn.grn_id where grn.ref_type = 6 and po.status=0 and grn.returnable_trn_id in (".$row['id'].")";*/

			$rs_product1 = $dbcon->query($query1);
			
			$row1=brp_mysqli_fetch_array($rs_product1);

			$qty = $row['item_qty'];
			
			$pending_qty=$row['item_qty']-$row1['done_qty'];

			$type1="conv_unit";
			$pending_conv_qty=convert_stock($dbcon,$pending_qty,$row['item_id'],$type1);
			

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
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['item_id'],"-1");
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
				where mst.grn_id=".$eid." and product_id=".$row['item_id']." and purchaseorder_id=".$row['purchaseorder_id'];
				$rol=mysqli_fetch_assoc($dbcon->query($query11));
				
				if($rol['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}
			}else{
				$rol['grn_godown'] = $row['product_mat_center'];
			}
			$tolerance=get_pro_field($dbcon,$row['item_id'],'tolerance');
			$maximum_tolerance=get_pro_field($dbcon,$row['item_id'],'maximum_tolerance');
			$minimum_tolerance=get_pro_field($dbcon,$row['item_id'],'minimum_tolerance');
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
			$display_none = '';
			if($row['po_type']=='1'){ 
				$display_none .= "display:none"; 
			}
			else{ 
				$display_none.= "display:block";
			}

			$btn_delete =' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="remove_returnable_chalan_data('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';


			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
						<td>".$row['product_name']."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['item_id']."' /></td>
						<td>".$cat_name."</td>
						<td>";

						$str.="
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
							".round_up($pending_qty,5)." </br> <span>".$row['unit_name']."</span> 
							</div>";

							if($row['item_unit_id'] != $row["conv_unit_id"]){
								$str.="
								<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #0b8219;'></br>
							".round_up($pending_conv_qty,5)." </br> <span>".$row['conv_unit_name']."</span> 
							</div>";
							}
						
						$str .= "<input type='hidden' class='form-control' name='grn_rate_unit[]' id='grn_rate_unit$cnt' value='".$row['item_unit_id']."' />";
							
							 if($row["item_unit_id"]!=$row["conv_unit_id"]){ 
								
									$str.="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404; display:none;'>
										</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
									</div>";
								
							 } 
						$str.="</td>
						<td>";
						
						$str.="
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
							".round_up($pending_qty,5)." </br> <span>".$row['unit_name']."</span> 
							</div>";
									 if($row["item_unit_id"]!=$row["conv_unit_id"]){ 

						$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
							}
						
						if($row['item_unit_id'] != $row["conv_unit_id"]){

						$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
						
							}
						$str.="<td>";
						$product_name = '"'.$row['product_name'].'"';	
						
						$unit_name = '"'.$row['unit_name'].'"';	
							
						$str .="<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['item_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['item_id'].",".$product_name.",".$unit_name.",".$row["item_unit_id"].");' ><i class='fa fa-plus'></i></button>";
									
							}

							$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						
						if($row['item_unit_id'] != $row["conv_unit_id"]){
							
							$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input type='number' class='form-control handle_qty'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['conv_unit_name']."
								<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
						}else{
							$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";
						}
							
						
							
						$str.="</td>
						
						<td style=".$display_none.">
							<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
							$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
							$str.="</select>
							<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
							<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
							<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
							<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['id']."' />
							
						</td>
						<td>
						    ".$btn_delete."
							<!-- <button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button> -->
						</td>
					</tr>";
			
			$cnt++;	
		}
		$str .= load_scrap_grn_product_data($cnt,$dbcon,$type,$id);
		return $str;
	}
// Added by Sanat :: 18-11-2021







		function get_bom_version_name($dbcon,$bom_version_id){
			$qry = "select version_name from pro_ms_bom_version where bom_version_id = ".$bom_version_id;
			$res = $dbcon->query($qry);
			$row_res=brp_mysqli_fetch_assoc($res);

			return $row_res['version_name'];
		}

		function wipstock($dbcon,$product_id,$unit_id,$branch_id,$customer_id=""){
			$whr = "";
			if($customer_id != ""){
				$whr = " and res.customer_id = " .$customer_id;
			}else{
		        $whr = " and customer_id = 0 and customer_id =''";
		    }
			$query="select IFNULL(sum(allocate_base_qty-allocate_base_qty_used),0) as stockqty from wip_stock_allocate as trn
			left join tbl_request_product as res on res.rp_id=trn.rp_id
			where trn.status=0 and trn.stock_flag = 1 and res.branch_id='".$branch_id."' and trn.company_id=".$_SESSION['company_id']." and res.rp_pid=".$product_id."";

			$result=$dbcon->query($query);
			$rel=brp_mysqli_fetch_assoc($result);

			return floatval($rel['stockqty']);
		//return $query;
		}


		function reprocess_start_count_using_p_id($dbcon,$p_id){
			$query="select COALESCE(sum(pen_qty),0) as sqty,COALESCE(sum(start_qty),0) as start_qty from tbl_allocate_re_process where p_id in(".$p_id.")";

			$rs_cust=$dbcon->query($query);	
			$rel=brp_mysqli_fetch_array($rs_cust);

	//$total=$rel['sqty']-$rel['stqty'];
			$total=$rel['sqty']-$rel['start_qty'];

			if($total==0)
			{
				return 0;
			}
			else
			{
				return $total;
			}
		}

		function total_reprocess_pending_qty($dbcon,$p_id){
			$query="select COALESCE(sum(pen_qty),0) as pen_qty from tbl_allocate_re_process where p_id in(".$p_id.")";

			$rs_cust=$dbcon->query($query);	
			$rel=brp_mysqli_fetch_array($rs_cust);

			$total=$rel['pen_qty'];

			if($total==0)
			{
				return 0;
			}
			else
			{
				return $total;
			}
		}

		function reprocess_end_count_using_p_id($dbcon,$p_id){
			$query="select COALESCE(sum(start_qty),0) as start_qty from tbl_allocate_re_process where p_id in(".$p_id.")";


			$rs_cust=$dbcon->query($query);	
			$rel=brp_mysqli_fetch_array($rs_cust);

			$total=$rel['start_qty'];

			if($total==0)
			{
				return 0;
			}
			else
			{
				return $total;
			}
		}


		function add_reprocess_start_stop_entry($dbcon,$qty,$p_id,$type){
	// type 1 - start, 2- end

			$qry= "select * from tbl_allocate_re_process where p_id = " . $p_id;
			$result=$dbcon->query($qry);
			$res=brp_mysqli_fetch_assoc($result);

			$info['product_id'] = $res['p_product_id'];
			$info['process_id'] = $res['process_id'];
			$info['re_pro_p_id'] = $p_id;
			$info['pt_alloc_id'] = $res['pt_alloc_id'];
			$info['qty'] = $qty;
			$info['process_type'] = $type;
			$info['status'] = 0;
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$branch_id  = $_SESSION['branch_id'];


			add_record('tbl_reprocess_trn_history',$info, $dbcon,$branch_id);


		}

//pathik start process stock 22-01-2022
		// function process_stock_for_mrp($dbcon,$product_id,$unit_id,$rp_id,$branch_id){

		// 	/*$query_pro="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn
		// 	where id not in (SELECT process_id FROM tbl_wororder_product_process ORDER BY process_priority DESC LIMIT 1) and trn.rp_id=".$rp_id;*/

		// 	$query_pro="select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn where trn.rp_id=".$rp_id." and process_priority < (SELECT MAX(process_priority) FROM tbl_wororder_product_process where rp_id=".$rp_id.")";

		// 	$result_pro=$dbcon->query($query_pro);
		// 	$rel_pro=brp_mysqli_fetch_assoc($result_pro);

		// 	$query_sto="select IFNULL(sum(base_stock),0) as stockqty from tbl_process_stock_trn as trn
		// 	where trn.stock_status!=2 and trn.branch_id=".$branch_id." and stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and process_id in (".$rel_pro['process_id_rp'].")";

		// 	$result_sto=$dbcon->query($query_sto);
		// 	$rel_sto=brp_mysqli_fetch_assoc($result_sto);

		// 	$query_res="select IFNULL(sum(base_stock),0) as used_stockqty from tbl_process_reserve_stock as trn
		// 	where trn.stock_status!=2 and trn.branch_id=".$branch_id." and stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and process_id in (".$rel_pro['process_id_rp'].")";

		// 	$result_res=$dbcon->query($query_res);
		// 	$rel_res=brp_mysqli_fetch_assoc($result_res);

		// 	$stock_qty=$rel_sto['stockqty']-$rel_res['used_stockqty'];
		// 	return $stock_qty;
		// //return $query;
		// }

		function process_stock_for_mrp($dbcon, $product_id, $unit_id, $rp_id, $branch_id) {
			$query_pro = "SELECT GROUP_CONCAT(process_id) AS process_id_rp 
						FROM tbl_wororder_product_process AS trn
						WHERE trn.rp_id = " . intval($rp_id);

			$result_pro = $dbcon->query($query_pro);
			$rel_pro = brp_mysqli_fetch_assoc($result_pro);

			// ✅ Check if process_id list is empty
			if (empty($rel_pro['process_id_rp'])) {
				return 0; // No processes, return 0 stock
			}

			$process_ids = $rel_pro['process_id_rp']; // safe now

			$query_sto = "SELECT IFNULL(SUM(base_stock), 0) AS stockqty 
						FROM tbl_process_stock_trn AS trn
						WHERE trn.stock_status = 0 
							AND trn.branch_id = " . intval($branch_id) . " 
							AND stock_flage = 1 
							AND trn.company_id = " . intval($_SESSION['company_id']) . " 
							AND trn.product_id = " . intval($product_id) . " 
							AND process_id IN (" . $process_ids . ")";

			$result_sto = $dbcon->query($query_sto);
			$rel_sto = brp_mysqli_fetch_assoc($result_sto);

			$query_res = "SELECT IFNULL(SUM(base_stock), 0) AS used_stockqty 
						FROM tbl_process_reserve_stock AS trn
						WHERE trn.stock_status = 0 
							AND trn.branch_id = " . intval($branch_id) . " 
							AND stock_flage = 1 
							AND trn.company_id = " . intval($_SESSION['company_id']) . " 
							AND trn.product_id = " . intval($product_id) . " 
							AND process_id IN (" . $process_ids . ")";

			$result_res = $dbcon->query($query_res);
			$rel_res = brp_mysqli_fetch_assoc($result_res);

			$stock_qty = $rel_sto['stockqty'] - $rel_res['used_stockqty'];
			return $stock_qty;
		}


		function allocate_process_and_process_stock_reserve($dbcon,$rp_id,$extra_stock){
			$company_config = getCompanyConfiguration($dbcon);
		//var_dump($rp_id);
			$query_sto="select IFNULL(sum(qty),0) as alloqty,process_id from mrp_process_reserve_temp as trn
			where trn.status = 0 and trn.rp_id=".$rp_id." group by process_id";

			$result_sto=$dbcon->query($query_sto);
			while($rel_sto=brp_mysqli_fetch_assoc($result_sto)){

				$query_p="select * from tbl_wororder_product_process as trn
				where trn.rp_id=".$rp_id." and process_id=".$rel_sto['process_id']." group by process_id";
				
				$result_p=$dbcon->query($query_p);
				$rel_p=brp_mysqli_fetch_assoc($result_p);

				$query1= "select pr_process_id,process_priority,process_type,process_id from tbl_wororder_product_process as wor_pro_process
				where wor_pro_process.rp_id=".$rp_id." and wor_pro_process.process_priority>".$rel_p['process_priority']." ORDER BY process_priority limit 1" ;
				$result1=$dbcon->query($query1);
				$cnt1=brp_mysqli_num_rows($result1);
				
				if($cnt1>0){
					$row1=brp_mysqli_fetch_array($result1);
					$process_priority=$row1['process_priority'];
					$process_type=$row1['process_type'];
					$process_id=$row1['process_id'];
				}

				$query_rp= "select wor_pro_process.*,p.batch_wise_stock_manage from tbl_request_product as wor_pro_process 
				left join product_mst as p on p.product_id = wor_pro_process.rp_pid
				where wor_pro_process.rp_id=".$rp_id ;
				$result_rp=$dbcon->query($query_rp);
				$row_rp=brp_mysqli_fetch_array($result_rp);

					//old allocate process entry start
				$info5['process_id']		= $rel_p['process_id'];			
				$info5['p_start_time']		= '';		
				$info5['p_end_time']		= '';		
				$info5['p_qty']				= $rel_sto['alloqty'];		
				$info5['pen_qty']			= 0;
				$info5['start_qty']			= $rel_sto['alloqty'];
				$info5['p_status']			= 3;	

				$info5['process_unit']		= $row_rp['process_unit'];		
				$info5['p_ref_id']			= $row_rp['rp_id'];		
				$info5['p_ref_type']		= 'process request';		
				$info5['p_product_id']		= $row_rp['rp_pid'];		
				$info5['pr_process_type']	= $rel_p['process_type'];		
				$info5['process_priority']	= $rel_p['process_priority'];		
				$info5['previous_process_id']= 0;
				$info5['extra_stock']= $extra_stock;
				$info5['product_version']	= $row_rp['product_version'];


				if($company_config['batch_wise_stock'] == '1' &&  $company_config['batch_process'] == '0' && $row_rp['batch_wise_stock_manage'] == '1'){
					$info5['batch_process_start_time'] = 1;
					$info6['batch_process_start_time'] = 1;
				}
					//$info5['description'] = $description;

					/*if($resourceinfo['process_type']=='1'){			
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}*/
					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];	
					// var_dump($info5);
					$last_insert=add_record('tbl_allocate_process', $info5, $dbcon, $row_rp['branch_id']);

					//old allocate process entry end

					//new allocate process entry start
					$info6['process_id']			= $process_id;			
					$info6['p_start_time']			= '';		
					$info6['p_end_time']			= '';		
					$info6['p_qty']					= $rel_sto['alloqty'];		
					$info6['pen_qty']				= $rel_sto['alloqty'];		
					$info6['process_unit']			= $row_rp['process_unit'];		
					$info6['p_ref_id']				= $row_rp['rp_id'];		
					$info6['extra_stock']				= $extra_stock;		
					$info6['p_ref_type']			= 'process request';		
					$info6['p_product_id']			= $row_rp['rp_pid'];		
					$info6['pr_process_type']		= $process_type;		
					$info6['process_priority']		= $process_priority;		
					$info6['previous_process_id']	= $last_insert;
					$info6['product_version']		= $row_rp['product_version'];
						//$info6['description'] 			= $description;

						/*if($resourceinfo['process_type']=='1'){			
							$info6['resource_id']	= $resourceinfo['resource_id'];
						}*/
						$info6['cdate']				= date("Y-m-d H:i:s");
						$info6['user_id']			= $_SESSION['user_id'];
						$info6['company_id']		= $_SESSION['company_id'];	
						//var_dump($info6);
						$addrecord=add_record('tbl_allocate_process', $info6, $dbcon, $row_rp['branch_id']);

					//new allocate process entry end

					//stock allocate start
						$stqty=$rel_sto['alloqty'];

						$query_pq="select trn.godown_id,trn.process_id,IFNULL(sum(qty),0) as allo_qty from mrp_process_reserve_temp as trn
						where trn.status = 0 and trn.rp_id=".$rp_id." and process_id=".$rel_sto['process_id']."  group by godown_id";

						$result_pq=$dbcon->query($query_pq);
						while($rel_pq=brp_mysqli_fetch_assoc($result_pq)){
							if($stqty>0){
								$query_psto="select * from tbl_process_stock_trn as trn
								where trn.stock_status=0 and trn.branch_id=".$row_rp['branch_id']." and stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$row_rp['rp_pid']." and process_id=".$rel_pq['process_id']." and godown_id=".$rel_pq['godown_id'];
								
								$result_psto=$dbcon->query($query_psto);
								while($rel_psto=brp_mysqli_fetch_assoc($result_psto)){
									if($stqty>0){
										$query_res="select IFNULL(sum(base_stock),0) as used_stockqty from tbl_process_reserve_stock as trn
										where trn.stock_status=0 and stock_flage=1 and trn.company_id=".$_SESSION['company_id']." and trn.product_id=".$product_id." and process_stock_id=".$rel_psto['process_stock_id'];

										$result_res=$dbcon->query($query_res);
										$rel_res=brp_mysqli_fetch_assoc($result_res);

										$stock_qty=$rel_psto['base_stock']-$rel_res['used_stockqty'];
										if($stock_qty>=$stqty){
											$usedstqty=$stqty;
										}else{
											$usedstqty=$stock_qty;
										}

										$stqty=$stqty-$usedstqty;

										$ref_name="direct process stock allocate";
										// $ref_id=$rel_psto['process_stock_id'];
										$ref_id=$rp_id;
										$stock_date=date("Y-m-d");
										if($usedstqty>0){
												//var_dump($usedstqty);
											$process_stock_id=$rel_psto['process_stock_id'];
											production_reserve_add_process_stock($dbcon,$usedstqty,$row_rp['process_unit'],$addrecord,$process_stock_id,$stock_date,$ref_name,$ref_id);	
										}
									}

								}
							}
						}

						
					//stock allocate end
					}
				}
//pathik start process stock 22-01-2022


				function load_grn_edit_data($dbcon,$grn_id,$type)
				{
					$company_config = getCompanyConfiguration($dbcon);
					$production_pro_search = $company_config['production_pro_search'];
					$pro_search=explode(",", $production_pro_search);

					$str='';

					$query="select grn_trn.*,grn_trn.product_qty as produ_qty,grn_trn.product_conv_qty as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,con_unit.unit_name as conv_unit_name,p.batch_wise_stock_manage,p.product_icode, dr.drawing_number, grn.purchaseorder_id from tbl_grn_trn as grn_trn 
					left join product_mst as p on p.product_id=grn_trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
					left join tbl_category as tc on p.product_category=tc.cat_id 
					left join unit_mst as unit on unit.unitid=grn_trn.unit_id
					left join unit_mst as con_unit on con_unit.unitid=grn_trn.product_conv_unit
					left join tbl_grn as grn on grn.grn_id=grn_trn.grn_id

					where grn_trn_status = 0 and grn_trn.grn_id = ".$grn_id;
					$rs_product=$dbcon->query($query);


					$cnt=1;
					while($row=brp_mysqli_fetch_array($rs_product))
					{
						$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';

						$pending_qty=$row['produ_qty'];
						$pending_conv_qty=$row['produ_con_qty'];
						$total_pending_qty=$row['produ_qty'];
						$total_pending_conv_qty=$row['produ_con_qty'];

						if($row['ref_type'] == '2'){
							$po_query="select product_qty,product_conv_qty from tbl_purchaseordertrn where purchaseordertrn_id = " . $row['purchaseordertrn_id'];
							$po_row = brp_mysqli_fetch_assoc($dbcon->query($po_query));
							
							$total_pending_qty=$po_row['product_qty'];
							$total_pending_conv_qty=$po_row['product_conv_qty'];
						}

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

			$drawing_number = "";
			$item_code = "";
			if(in_array('drawing',$pro_search)){
				$drawing_number = " -- (".$row['drawing_number'].")";
			}
			if(in_array('item',$pro_search)){
				$item_code = " -- (".$row['product_icode'].")";
			}
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],"-1");
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}

				$del_ronly="";
			if($row['store_accept'] == '1'){
				$ronly="readonly";
				$del_ronly="readonly";
			}else if($qc_paramter_info=='1'){
				if($row['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}	
			}else if($qc_paramter_info=='0'){
				if($row['product_qc']==1){
					$ronly="";
				}
			}else{
				$ronly="";
			}

			$btn_delete = "";
			if($del_ronly == ""){
				$btn_delete =' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_grn_data('.$row['grn_trn_id'].','.$row['purchaseorder_id'].')"><i class="fa fa-trash-o"></i></button>';

			}

			/*if($qc_paramter_info=='1'){
				if($row['product_qc']==1){
					$ronly="readonly";
				}	
			}else{
				$ronly="";
			}
			
			if($row['store']==1){
				$ronly="readonly";
			}else{
				$ronly="";
			}*/

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
			   $display_none = '';
			   if($row['po_type']=='1'){ 
			   	$display_none .= "display:none"; 
			   }
			   else{ 
			   	$display_none.= "display:block";
			   }
			   $str.="<tr id='trid".$cnt."'>
			   <!--<td>".$cnt."</td>-->
			   <td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
			   <td>".$row['product_name'].' '.$item_code.' '.$drawing_number."<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
			   <td>".$cat_name."</td>
			   <td>";

			   if($row['rate_unit'] == $row["product_conv_unit"]){
			   	$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
			   	</br> ".round_up($total_pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
			   	</div>";
			   } else if($row['rate_unit'] == $row["unit_id"]){
			   	$str.="
			   	<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
			   	".round_up($total_pending_qty,5)." </br> <span>".$row['unit_name']."</span> 
			   	</div>";
			   }
			   $str .= "<input type='hidden' class='form-control rate_unit' name='grn_rate_unit[]' id='grn_rate_unit$cnt' value='".$row['rate_unit']."' />";

			   if($row["unit_id"]!=$row["product_conv_unit"]){ 
			   	if($row['rate_unit'] != $row["product_conv_unit"]){
			   		$str.="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404; display:none;'>
			   		</br> ".round_up($total_pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
			   		</div>";
			   	} else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="</br>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219; display:none;'>
			   		".round_up($total_pending_qty,5)." </br> <span>".$row['unit_name']."</span> 
			   		</div>";
			   	}

			   } 
			   $str.="</td>
			   <td>";
			   if($row['rate_unit'] == $row["product_conv_unit"]){
			   	$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
			   	</br> ".round_up($total_pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
			   	</div>";
			   } else if($row['rate_unit'] == $row["unit_id"]){
			   	$str.="
			   	<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
			   	".round_up($total_pending_qty,5)." </br> ".$row['unit_name']." 
			   	</div>";
			   }

			   if($row["unit_id"]!=$row["product_conv_unit"]){
			   	if($row['rate_unit'] != $row["product_conv_unit"]){
			   		$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
			   		</br> ".round_up($total_pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
			   		</div>";
			   	} else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="</br>
			   		<div  class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
			   		".round_up($total_pending_qty,5)." </br> ".$row['unit_name']." 
			   		</div>";
			   	}
			   }
			   $str.="<td>";
			   $product_name = '"'.$row['product_name'].'"';	


			   if($row['rate_unit'] == $row["product_conv_unit"]){
			   	$unit_name = '"'.$row['conv_unit_name'].'"';
			   	$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
			   	<input type='number' min='0' max='' data-pendingqty='".round_up($total_pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['produ_con_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
			   	".$row['conv_unit_name'];
			   	if($row['batch_wise_stock_manage'] == 1 && $ronly == ""){
			   		$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["product_conv_unit"].");' ><i class='fa fa-plus'></i></button>";
			   		$batch_qty = 0;
			   		$batch_qty = get_batch_qty_using_grn_trn_id($dbcon,$row['grn_trn_id']);
			   		$str .="<input type='hidden' name='batch_total_qty[]' id='batch_total_qty$cnt' value='".$batch_qty."'>";

			   	}

			   	$str .="<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['produ_con_qty']."' />

			   	<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />
			   	</div>
			   	";
			   }else if($row['rate_unit'] == $row["unit_id"]){
			   	$unit_name = '"'.$row['unit_name'].'"';	
			   	$str .="<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
			   	<input type='number' min='0' max='' data-pendingqty='".round_up($total_pending_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
			   	".$row['unit_name'];
			   	if($row['batch_wise_stock_manage'] == 1 && $ronly == ""){
			   		$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["unit_id"].");' ><i class='fa fa-plus'></i></button>";

			   		$batch_qty = 0;
			   		$batch_qty = get_batch_qty_using_grn_trn_id($dbcon,$row['grn_trn_id']);
			   		$str .="<input type='hidden' name='batch_total_qty[]' id='batch_total_qty$cnt' value='".$batch_qty."'>";

			   	}

			   	$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />

			   	<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
			   	</div>
			   	";
			   }

			   if($row["unit_id"]!=$row["product_conv_unit"]){
			   	if($row['rate_unit'] != $row["product_conv_unit"]){
			   		$str.="<br/>
			   		<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
			   		<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
			   		".$row['conv_unit_name']."
			   		<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_conv_qty']."' />

			   		<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />
			   		</div>
			   		";
			   	}else if($row['rate_unit'] != $row["unit_id"]){
			   		$str.="<br/>
			   		<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
			   		<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
			   		".$row['unit_name']."
			   		<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />

			   		<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
			   		</div>
			   		";
			   	}

			   }else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='".$row['product_qty']."' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />";*/

							if($row['rate_unit'] != $row["product_conv_unit"]){

								$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$row['product_conv_qty']."' ".$ronly." />
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["product_conv_unit"]."' />";

							}else{

								$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='".$row['product_qty']."' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
							}

						}

						$str.="</td>
						
						<td style=".$display_none.">
						<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
						$str.= get_all_godown($dbcon,$row['grn_godown'],1);
						$str.="</select>
						<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' />
						<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$row['grn_trn_id']."' />
						<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$row['product_qc']."' />
						<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['po_ref_id']."' />
						<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['purchaseordertrn_id']."' />
						<input type='hidden' name='prev_conv_grn_qty[]' id='prev_conv_grn_qty$cnt' value='".$row['produ_con_qty']."' />

						<input type='hidden' name='prev_grn_qty[]' id='prev_grn_qty$cnt' value='".$row['product_qty']."' />

						</td>
						<td>
						".$btn_delete."
						</td>
						</tr>";

						$cnt++;	
					}
					if($type == '2'){
						$str .= load_scrap_grn_product_data($cnt,$dbcon,$type);	
					}

					return $str;
				}


				function get_batch_qty_using_grn_trn_id($dbcon,$grn_trn_id){
					$qry="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status = 0 and grn_trn_id = " . $grn_trn_id;
					$res=brp_mysqli_fetch_assoc($dbcon->query($qry));

					$batch_qty = $res['qty'];
					return $batch_qty;
				}

function get_batch_qty_using_grn_no($dbcon,$grn_no){
	$qry="select IFNULL(sum(batch_qty),0) as qty from tbl_batch_data where status in (0,1) and order_no = '" . $grn_no."'";
	$res=brp_mysqli_fetch_assoc($dbcon->query($qry));

	$batch_qty = $res['qty'];
	return $batch_qty;
}



function load_scrap_grn_product_data($cnt,$dbcon,$ref_type,$id="")
	{
		 $company_config = getCompanyConfiguration($dbcon);
		 $production_pro_search = $company_config['production_pro_search'];
		 $pro_search=explode(",", $production_pro_search);

		$str='';

		if($ref_type == 2){
			$ref_type = 4;
		}

		$whr = "";
		if($ref_type == '6' && !empty($id)){
			$whr = " and returnable_id = " . $id;
		}	
// var_dump($ref_type);
		 $query="select grn_trn.*,grn_trn.product_qty as produ_qty,grn_trn.product_conv_qty as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,con_unit.unit_name as conv_unit_name,p.batch_wise_stock_manage,p.product_icode, dr.drawing_number from tbl_grn_trn as grn_trn 
			left join product_mst as p on p.product_id=grn_trn.product_id
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_category as tc on p.product_category=tc.cat_id 
			left join unit_mst as unit on unit.unitid=grn_trn.unit_id
			left join unit_mst as con_unit on con_unit.unitid=grn_trn.product_conv_unit
			
			where grn_trn_status = 3 and grn_trn.ref_type = ".$ref_type . $whr;
		$rs_product=$dbcon->query($query);
		
		$temp = "";
			if($ref_type == 4){
				$temp = '"temp"';	
			}
		// $cnt=1;
		while($row=brp_mysqli_fetch_array($rs_product))
		{

			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			
			$pending_qty=$row['produ_qty'];
			$pending_conv_qty=$row['produ_con_qty'];

			$drawing_number = "";
			$item_code = "";

			$is_diff_unit = 0;
			$diff_unit_type = "";
			if($row["unit_id"]!=$row["product_conv_unit"]){
  				$is_diff_unit = 1;
			}

			if($row["rate_unit"]==$row["product_conv_unit"]){
  				$diff_unit_type = "conv";
			}else{
				$diff_unit_type = "base";
			}


			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],"-1");
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}


			if($row['store_accept'] == '1'){
				$ronly="readonly";
			}else if($qc_paramter_info=='1'){
				if($row['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}	
			}else if($qc_paramter_info=='0'){
				if($row['product_qc']==1){
					$ronly="";
				}
			}else{
				$ronly="";
			}

			$ronly="readonly";

			$btn_delete =' <button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="remove_direct_grn_data('.$row['grn_trn_id'].')"><i class="fa fa-trash-o"></i></button>';

	
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
			$display_none = '';
			if($row['po_type']=='1'){ 
				$display_none .= "display:none"; 
			}
			else{ 
				$display_none.= "display:block";
			}
			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
						<td>".$row['product_name'].' '.$item_code.' '.$drawing_number."<input type='hidden' class='form-control' name='grn_pid_tmp[]' id='grn_pid_tmp$cnt' value='".$row['product_id']."' /></td>
						<td>".$cat_name."</td>
						<td>";

						if($row['rate_unit'] == $row["product_conv_unit"]){
							$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['rate_unit'] == $row["unit_id"]){
							$str.="
									<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
								".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
								</div>";
						}
						$str .= "<input type='hidden' class='form-control' name='grn_rate_unit_tmp[]' id='grn_rate_unit_tmp$cnt' value='".$row['rate_unit']."' />";
							
						 if($row["unit_id"]!=$row["product_conv_unit"]){ 
							if($row['rate_unit'] != $row["product_conv_unit"]){
								$str.="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404; display:none;'>
									</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
								</div>";
							} else if($row['rate_unit'] != $row["unit_id"]){
								$str.="</br>
										<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219; display:none;'>
									".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
									</div>";
							}
							
						 } 
						$str.="</td>
						<td>";
						if($row['rate_unit'] == $row["product_conv_unit"]){
						$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['rate_unit'] == $row["unit_id"]){
							$str.="
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
									".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
								</div>";
						}
							
						if($row["unit_id"]!=$row["product_conv_unit"]){
							if($row['rate_unit'] != $row["product_conv_unit"]){
								$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
									</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
								</div>";
							} else if($row['rate_unit'] != $row["unit_id"]){
								$str.="</br>
									<div  class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
										".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
									</div>";
							}
						}
						$str.="<td>";
						$product_name = '"'.$row['product_name'].'"';	
						
						$diff_unit_type = '"'.$diff_unit_type.'"';
						if($row['rate_unit'] == $row["product_conv_unit"]){
							$unit_name = '"'.$row['conv_unit_name'].'"';
							$diff_unit_name = '"'.$row['unit_name'].'"';
							$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='conv_grn_qty_tmp[]' id='conv_grn_qty_tmp$cnt' value='".$row['produ_con_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
							".$row['conv_unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["product_conv_unit"].",".$is_diff_unit.",".$pending_qty.",".$diff_unit_name.",".$row["unit_id"].",".$diff_unit_type.",".$temp.");' ><i class='fa fa-plus'></i></button>";
									
							}

							$str .="<input type='hidden' name='conv_grn_qty_tmp_hide[]' id='conv_grn_qty_tmp_hide$cnt' value='".$row['produ_con_qty']."' />
							<input type='hidden' name='conv_unit_id_tmp[]' id='conv_unit_id_tmp$cnt' value='".$row["product_conv_unit"]."' />
							</div>
							";
						}else if($row['rate_unit'] == $row["unit_id"]){
							$unit_name = '"'.$row['unit_name'].'"';	
							$diff_unit_name = '"'.$row['conv_unit_name'].'"';
							$str .="<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='grn_qty_tmp[]' id='grn_qty_tmp$cnt' value='".$row['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["unit_id"].",".$is_diff_unit.",".$pending_conv_qty.",".$diff_unit_name.",".$row["product_conv_unit"].",".$diff_unit_type.",".$temp.");' ><i class='fa fa-plus'></i></button>";
	
							}

							$str .="<input type='hidden' name='grn_qty_tmp_hide[]' id='grn_qty_tmp_hide$cnt' value='".$row['product_qty']."' />
							<input type='hidden' name='unit_id_tmp[]' id='unit_id_tmp$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						}
							
						if($row["unit_id"]!=$row["product_conv_unit"]){
								if($row['rate_unit'] != $row["product_conv_unit"]){
									$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
								<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."'  name='conv_grn_qty_tmp[]' id='conv_grn_qty_tmp$cnt' value='".$row['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['conv_unit_name']."
								<input type='hidden' name='conv_grn_qty_tmp_hide[]' id='conv_grn_qty_tmp_hide$cnt' value='".$row['product_conv_qty']."' />
								<input type='hidden' name='conv_unit_id_tmp[]' id='conv_unit_id_tmp$cnt' value='".$row["product_conv_unit"]."' />
							</div>
							";
								}else if($row['rate_unit'] != $row["unit_id"]){
									$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
								<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' name='grn_qty_tmp[]' id='grn_qty_tmp$cnt' value='".$row['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
								".$row['unit_name']."
								<input type='hidden' name='grn_qty_tmp_hide[]' id='grn_qty_tmp_hide$cnt' value='".$row['product_qty']."' />
								<input type='hidden' name='unit_id_tmp[]' id='unit_id_tmp$cnt' value='".$row["unit_id"]."' />
							</div>
							";
								}
							
						}else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty_tmp[]' id='conv_grn_qty_tmp$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_tmp_hide[]' id='conv_grn_qty_tmp_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id_tmp[]' id='conv_unit_id_tmp$cnt' value='".$row["conv_unit_id"]."' />";*/

							if($row['rate_unit'] != $row["product_conv_unit"]){

								$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty_tmp[]' id='conv_grn_qty_tmp$cnt' value='".$row['product_conv_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_tmp_hide[]' id='grn_qty_tmp_hide$cnt' value='".$row['product_qty']."' />
							<input type='hidden' name='conv_unit_id_tmp[]' id='conv_unit_id_tmp$cnt' value='".$row["product_conv_unit"]."' />";

								}else{

									$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty_tmp[]' id='grn_qty_tmp$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_tmp_hide[]' id='grn_qty_tmp_hide$cnt' value='".$row['product_qty']."' />
							<input type='hidden' name='unit_id_tmp[]' id='unit_id_tmp$cnt' value='".$row["unit_id"]."' />";
								}
						}
							
						$str.="</td>
						<!--<td>
							<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$row['product_qty']."' ".$ronly." />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
							<select class='form-control' name='grn_godown_tmp[]' style='".$sty."' id='grn_godown_tmp$cnt' required >";
							$str.= get_all_godown($dbcon,$row['grn_godown'],1);
							$str.="</select>
							<input type='hidden' name='qc_type_tmp[]' id='qc_type_tmp$cnt' value='".$qc_st."' />
							<input type='hidden' name='grn_trn_id_tmp[]' id='grn_trn_id_tmp$cnt' value='".$row['grn_trn_id']."' />
							<input type='hidden' name='qc_status_tmp[]' id='qc_status_tmp$cnt' value='".$row['product_qc']."' />
							<input type='hidden' name='po_ref_id_tmp[]' id='po_ref_id_tmp$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id_tmp[]' id='purchaseordertrn_id_tmp$cnt' value='".$row['purchaseordertrn_id']."' />
							
						</td>
						<td>
							".$btn_delete."
						</td>
					</tr>";
			
			$cnt++;	
		}
		
		return $str;
	}
	
	/* added by jayesh for checking product lead time and process exist or not */
function check_product_lead_time_and_process($dbcon,$product_id)
{
	$qry= "select * from product_mst where product_id = ".$product_id;
	$result=$dbcon->query($qry);
	$res=brp_mysqli_fetch_assoc($result);
	if($res['product_lead_time'] != 0 && $res['product_lead_time'] != '' )
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

//pathik add 14-02-2022 start
	//this function remove conflict time so grn create stop .so i will check older code and add this function. 

	function upadte_batch_data_status($dbcon,$grn_id,$grn_trn_id,$order_no,$product_id,$rate_unit,$grn_base_qty,$grn_base_unit,$grn_conv_qty,$grn_conv_unit,$product_qc,$customer_id="",$to_godown_id="",$grn_godown="",$purchaseordertrn_id=""){


			$whr = "";
			if(!empty($purchaseordertrn_id)){
				$whr = " and purchaseordertrn_id = " . $purchaseordertrn_id;
			}
			/*if(!empty($grn_id)){
				$whr .= " and grn_id = " . $grn_id;
			}
			if(!empty($grn_trn_id)){
				$whr .= " and grn_trn_id = " . $grn_trn_id;
			}*/

			$qry = "select * from tbl_batch_data where status != 2 and grn_id = 0 and grn_trn_id = 0 and product_id=".$product_id." and order_no ='" . $order_no."' ". $whr;
			$res = $dbcon->query($qry);


			while($row_res=brp_mysqli_fetch_assoc($res)){

				$info['grn_id'] = $grn_id;
				$info['grn_trn_id'] = $grn_trn_id;
				$info['status'] = 0;
				$info['grn_godown'] = $grn_godown;

				$remaining_qty = $row_res['batch_qty'];

				$info['qc_status']	= $product_qc;
				if($product_qc==1){
					$info['accept_qty']	= $remaining_qty;
					$info['qc_qty']	= $remaining_qty;
				}
				$info['grn_accept_qty']	= $remaining_qty;


				if($grn_conv_unit==$rate_unit){
					$type="base_unit";
					$conv_qty=$remaining_qty;
					$base_qty = ($conv_qty/$grn_conv_qty) * $grn_base_qty;

				}else{
					$type="conv_unit";
					$base_qty=$remaining_qty;
					$conv_qty = ($base_qty/$grn_base_qty) * $grn_conv_qty;
				}

				$batch_qty=$base_qty;
				$batch_conv_qty=$conv_qty;

				$info['base_qty']		= $batch_qty;
				$info['base_unit']		= $grn_base_unit;
				$info['conv_qty']		= $batch_conv_qty;
				$info['conv_unit']		= $grn_conv_unit;
				$info['customer_id']	= $customer_id;

				$where = "batch_id = " . $row_res['batch_id'];

				$updatetrnid = update_record('tbl_batch_data',$info,$where,$dbcon);
			}


		}
	//this function remove conflict time so process stop not working .so i will check older code and add this function. 
	function allocate_process_trn_stop_entry_start_entry_wise($dbcon,$grn_sub_trn_qty,$p_id,$start_stop_user_id="",$grn_trn_sub_id=0,$diff_qty_plus=0,$diff_qty_minus=0){

		
	$query1 = "select allocate_trn.pt_id,(allocate_trn.pt_qty-allocate_trn.pt_used_qty) pending_qty,allocate_process.p_ref_id,allocate_process.process_id,allocate_trn.pt_used_qty,allocate_process.pen_qty,allocate_process.p_product_id,allocate_trn.is_qty_variation,allocate_trn.variation_qty_plus,allocate_trn.variation_qty_minus from tbl_allocate_process_trn as allocate_trn
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

				add_process_trn($dbcon,$p_id,$row1['p_ref_id'],$row1['p_product_id'],$row1['process_id'],$allocate_trn_update_qty,"1","0",$row1['pt_id'],$start_stop_user_id,0,$diff_qty_plus,$diff_qty_minus);

				$allocate_trn_used_update_qty=$row1['pt_used_qty']+$allocate_trn_update_qty;

				$info_allocate_trn_used_update['pt_used_qty']	= $allocate_trn_used_update_qty + $diff_qty_minus - $diff_qty_plus;
				$info_allocate_trn_used_update['variation_qty_plus']	= $row1['variation_qty_plus'] + $diff_qty_plus;
				$info_allocate_trn_used_update['variation_qty_minus']	= $row1['variation_qty_minus'] + $diff_qty_minus;

				if($diff_qty_plus > 0 || $diff_qty_minus > 0){
					$info_allocate_trn_used_update['is_qty_variation']	= 1;
				} 

				$updatetrnid=update_record('tbl_allocate_process_trn',$info_allocate_trn_used_update,"pt_id=".$row1['pt_id'] , $dbcon);

				$return_qty=$return_qty+$allocate_trn_update_qty;
			}
		}
	}

	return $return_qty;
}
//pathik end 14-02-2022

function update_workorder_complete_qty_and_Status($dbcon,$rp_id,$qty,$reject_qty = 0){
	
	$que="select * from tbl_request_product where rp_id in(".$rp_id.")";
	$rs_di=$dbcon->query($que);

	$re=brp_mysqli_fetch_assoc($rs_di);
	$sp_id = $re['sp_id'];
	$req_qty = $re['rp_req_qty'];
	$qty_variation = $re['variation_qty_minus'];
	$product_id = $re['rp_pid'];
	$update_qty = $re['finish_used_qty'];
	$update_qty = $update_qty + $qty + $reject_qty ;
	$final_qty = $update_qty  + $qty_variation;
	$upd_wo_stock['finish_used_qty'] =  $update_qty;

	if($final_qty >= $req_qty){
		$upd_wo_stock['finish_status'] =  1 ;
		$upd_wo_stock['job_card_status'] =  3 ;

		if($re['main_request'] == 1 && $sp_id != "" && $sp_id > 0){
			$upd_main['finish_status'] = 1;
			$updatetrnid=update_record('tbl_set_main_process',$upd_main,"sp_id=".$sp_id, $dbcon);
		}
	}


	$updatetrnid=update_record('tbl_request_product',$upd_wo_stock,"rp_id=".$rp_id, $dbcon);


	$que1="select * from wip_stock_allocate where stock_flag = 1 and status = 0 and rp_id=".$rp_id;
	$rs_di1=$dbcon->query($que1);
	$re1=brp_mysqli_fetch_assoc($rs_di1);
	
	$req_qty1 = $re1['allocate_base_qty'];
	$update_qty1 = $re1['finish_qty'];
	$update_conv_qty1 = $re1['finish_conv_qty'];

	$type = "conv_unit";
    $conv_qty = convert_stock_new($dbcon, $qty, $product_id, $type);
	
	$reject_conv_qty = 0;

	if($reject_qty != "" && $reject_qty > 0){
		$reject_conv_qty = convert_stock_new($dbcon, $reject_qty, $product_id, $type);
	}

	$update_qty1 = $update_qty1 + $qty + $reject_qty;
	$update_conv_qty1 = $update_conv_qty1 + $conv_qty + $reject_conv_qty;
	$upd_wo_alloc_stock['finish_qty'] =  $update_qty1;
	$upd_wo_alloc_stock['finish_conv_qty'] =  $update_conv_qty1;

	if($update_qty1 >= $req_qty1){
		$upd_wo_alloc_stock['status'] =  1 ;
	}
	$updatetrnid=update_record('wip_stock_allocate',$upd_wo_alloc_stock,"wip_stock_allocate_id=".$re1['wip_stock_allocate_id'], $dbcon);

	$reserve_qty = $qty;
	while($re=brp_mysqli_fetch_assoc($rs_di)){
		$sp_id = $re['sp_id'];
		$req_qty = $re['rp_req_qty'];
		$update_qty = $re['finish_used_qty'];
		$shortage_complete_qty = $re['shortage_complete_qty'];
		
		$pending_qty = $req_qty - $update_qty;

		if($reserve_qty>0){
			if($pending_qty>0){
				if($pending_qty>=$reserve_qty){
					$rqty=$reserve_qty;
					$reserve_qty=$reserve_qty-$reserve_qty;
				}else{
					$rqty=$pending_qty;
					$reserve_qty=$reserve_qty-$pending_qty;
				}
				$upd_wo_stock = array();
				$update_qty = $update_qty + $rqty + $reject_qty;
				$shortage_complete_qty = $shortage_complete_qty +  $rqty + $reject_qty;
				$upd_wo_stock['finish_used_qty'] =  $update_qty;
				$upd_wo_stock['shortage_complete_qty'] =  $shortage_complete_qty;
				// var_dump('used qty : ' . $update_qty . '  -- req_qty' . $req_qty);
				if($update_qty >= $req_qty){
					$upd_wo_stock['finish_status'] =  1 ;
					$upd_wo_stock['job_card_status'] =  3 ;

					if($re['main_request'] == 1){
						$upd_main['finish_status'] = 1;
						$updatetrnid=update_record('tbl_set_main_process',$upd_main,"sp_id=".$sp_id, $dbcon);
					}
				}
				$updatetrnid=update_record('tbl_request_product',$upd_wo_stock,"rp_id=".$re['rp_id'], $dbcon);
			}
		}
	}

}

function get_product_rate($dbcon,$product_id,$process_id,$mode,$unit_id,$rp_id="",$p_id="",$stock_id=""){
	if($mode == "item_mst"){
		 $que="select process_rate from tbl_product_process where status = 0 and product_id=".$product_id." and process_id = " . $process_id . "
		 order by pr_process_id desc limit 1";
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);

		return $re['process_rate'];
	}
}

function convert_rate($dbcon,$rate,$product_id,$type){
	$que_po="select * from product_mst where product_id=".$product_id;
	$resi_grn=$dbcon->query($que_po);
	$re=brp_mysqli_fetch_assoc($resi_grn);
	if($re['product_base_unit']!=$re['product_conv_unit']){
		if($type=="base_unit"){
			$ret_rate=($re['product_conv_qty'] / $re['product_base_qty']) * $rate;
		}else{
			$ret_rate=($re['product_base_qty'] / $re['product_conv_qty']) * $rate;
		}
	}else{
		$ret_rate=$rate;
	}
	
	return $ret_rate;
	
	//return $type;
}

function get_bom_costing_template($dbcon,$bom_costing_template_id){
	$query = "select bom_costing_template_id,template_name from tbl_bom_costing_template where status = 0";
	$rs = $dbcon->query($query);
	$str = '<option value="">Select Costing Templete</option>';
	while ($rel = brp_mysqli_fetch_assoc($rs)) {
		$sel = '';
		if($rel['bom_costing_template_id']==$bom_costing_template_id){
			$sel='selected="selected"';
		}
		$str .= '<option ' . $sel . ' value="' . $rel['bom_costing_template_id'] . '">' . $rel['template_name'] . '</option>';
	}
	return $str;
}

function get_bom_costing($dbcon,$product_id,$bom_id,$bom_costing_id){
	$query = "select bom_costing_id,costing_no from tbl_bom_costing where status = 0 and product_id =" . $product_id ." and bom_id = " . $bom_id;
	$rs = $dbcon->query($query);
	$str = '<option value="">Select BOM Costing</option>';
	while ($rel = brp_mysqli_fetch_assoc($rs)) {
		$sel = '';
		if($rel['bom_costing_id']==$bom_costing_id){
			$sel='selected="selected"';
		}
		$str .= '<option ' . $sel . ' value="' . $rel['bom_costing_id'] . '">' . $rel['costing_no'] . '</option>';
	}
	return $str;
}

function stock_transfer_pending_grn($dbcon,$id,$type,$mode,$eid,$vender_id,$branch_id)
	{
		$company_config = getCompanyConfiguration($dbcon);		
		$production_pro_search = $company_config['production_pro_search'];
		$pro_search=explode(",", $production_pro_search);

		$str='';
		if(!empty($eid)){
			$grn_ids=" and grn_id!=".$eid;
		}
		if(!empty($vender_id)){
			$ven=" and op.vender_id=".$vender_id;
		}
		if(!empty($id)){
			$st_trn=" and st.stock_transfer_id in(".$id.")";
		}
		// $branch_where=" and po.branch_id=".$branch_id;

		//$branch_where=" and branch_id=".$branch_id;
	$query="select trn.*,sum(trn.base_qty)as produ_qty,sum(trn.conv_qty)as produ_con_qty,tc.cat_name,p.product_name,p.product_type,unit.unit_name,group_concat(trn.stock_transfer_trn_id ORDER BY trn.stock_transfer_trn_id ASC) as trn_id,con_unit.unit_name as conv_unit_name,p.batch_wise_stock_manage,p.product_icode, dr.drawing_number, trn.base_unit as unit_id, trn.conv_unit as conv_unit_id, st.to_godown_id,p.product_mat_center from tbl_stock_transfer_trn as trn 
		left join product_mst as p on p.product_id=trn.product_id
		left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
		left join tbl_category as tc on p.product_category=tc.cat_id 
		left join unit_mst as unit on unit.unitid=trn.base_unit
		left join unit_mst as con_unit on con_unit.unitid=trn.conv_unit
		left join tbl_stock_transfer as st on st.stock_transfer_id=trn.stock_transfer_id 
		where st.approve_status=1 and trn.status=0 and trn.grn_status = 0 and st.status=0 ".$branch_where." ".$ven." ".$st_trn." group by trn.product_id,trn.base_unit,trn.conv_unit".$group;
		$rs_product=$dbcon->query($query);
		
		
		$cnt=1;
		while($row=brp_mysqli_fetch_array($rs_product))
		{
			$cat_name = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
			$query1="select sum(product_qty) as done_qty,sum(product_conv_qty) as conv_done_qty from tbl_grn_sub_trn as po where status=0 and stock_transfer_trn_id in (".$row['trn_id'].")";
			$rs_product1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_array($rs_product1);
			
			$pending_qty=$row['produ_qty']-$row1['done_qty'];
			$pending_conv_qty=$row['produ_con_qty']-$row1['conv_done_qty'];

		
			
			$btn_delete = "<button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button>";	
			
			$drawing_number = "";
			$item_code = "";
			 if(in_array('drawing',$pro_search)){
		            $drawing_number = " -- (".$row['drawing_number'].")";
		        }
		        if(in_array('item',$pro_search)){
		            $item_code = " -- (".$row['product_icode'].")";
		        }
			$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],"-1");
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
				where mst.grn_id=".$eid." and product_id=".$row['product_id']." and stock_transfer_id=".$row['stock_transfer_id'];
				$rol=mysqli_fetch_assoc($dbcon->query($query11));
				
				if($rol['product_qc']==1){
					$ronly="readonly";
				}else{
					$ronly="";
				}
			}else{
				$rol['grn_godown'] = $row['product_mat_center'];
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
			$display_none = '';
			if($row['po_type']=='1'){ 
				$display_none .= "display:none"; 
			}
			else{ 
				$display_none.= "display:block";
			}

			
			$str.="<tr id='trid".$cnt."'>
						<!--<td>".$cnt."</td>-->
						<td>".get_product_type_name($dbcon,$row['product_type'])." ".$row['abc']."</td>
						<td>".$row['product_name'].' '.$item_code.' '.$drawing_number."
						
						<input type='hidden' class='form-control' name='grn_pid[]' id='grn_pid$cnt' value='".$row['product_id']."' /></td>
						<td>".$cat_name."</td>
						<td>";

						if($row['stock_unit'] == $row["conv_unit_id"]){
							$str.="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['stock_unit'] == $row["unit_id"]){
							$str.="
									<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
								".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
								</div>";
						}
						$str .= "<input type='hidden' class='form-control' name='grn_rate_unit[]' id='grn_rate_unit$cnt' value='".$row['stock_unit']."' />";
							
							 if($row["unit_id"]!=$row["conv_unit_id"]){ 
								if($row['stock_unit'] != $row["conv_unit_id"]){
									$str.="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404; display:none;'>
										</br> ".round_up($row['produ_con_qty'],5)." </br> ".$row['conv_unit_name']." 
									</div>";
								} else if($row['stock_unit'] != $row["unit_id"]){
									$str.="</br>
											<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219; display:none;'>
										".round_up($row['produ_qty'],5)." </br> <span>".$row['unit_name']."</span> 
										</div>";
								}
								
							 } 
						$str.="</td>
						<td>";
						if($row['stock_unit'] == $row["conv_unit_id"]){
						$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['stock_unit'] == $row["unit_id"]){
							$str.="
								<div style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;'></br>
									".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
								</div>";
						}
							
							if($row["unit_id"]!=$row["conv_unit_id"]){
							
								if($row['stock_unit'] != $row["conv_unit_id"]){
						$str .="<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;color: #cc0404;display:none;'>
								</br> ".round_up($pending_conv_qty,5)." </br> ".$row['conv_unit_name']." 
							</div>";
						} else if($row['stock_unit'] != $row["unit_id"]){
							$str.="</br>
								<div  class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;color: #0b8219;display:none;'>
									".round_up($pending_qty,5)." </br> ".$row['unit_name']." 
								</div>";
						}
							}
						$str.="<td>";
						$product_name = '"'.$row['product_name'].'"';	
						
						if($row['stock_unit'] == $row["conv_unit_id"]){
							$unit_name = '"'.$row['conv_unit_name'].'"';
							$str .="<div style='background-color: #c3c1c1;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['produ_con_qty']."'".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
							".$row['conv_unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_conv_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["conv_unit_id"].");' ><i class='fa fa-plus'></i></button>";
									
							}

							$str .="<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
						}else if($row['stock_unit'] == $row["unit_id"]){
							$unit_name = '"'.$row['unit_name'].'"';	
							$str .="<div style='background-color: #eae7e7;margin: -5px;padding: 10px;'>
								<input type='number' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control handle_qty qty_mangement entered_qty".$cnt."' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."'".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
							".$row['unit_name'];
							if($row['batch_wise_stock_manage'] == 1){
								$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["unit_id"].");' ><i class='fa fa-plus'></i></button>";
									
							}

							$str .="<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
						}
							
						if($row["unit_id"]!=$row["conv_unit_id"]){
								if($row['stock_unit'] != $row["conv_unit_id"]){
									$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #c3c1c1;margin: -5px;padding: 10px;display:none;'>
								<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_con_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."'  name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." onkeyup='product_convert_qty(2,".$cnt.");' />
								".$row['conv_unit_name']."
								<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
								<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />
							</div>
							";
								}else if($row['stock_unit'] != $row["unit_id"]){
									$str.="<br/>
								<div class='auto_conversation_hide' style='background-color: #eae7e7;margin: -5px;padding: 10px;display:none;'>
								<input type='number' class='form-control handle_qty' min='0' max='' data-pendingqty='".round_up($pending_qty1,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." onkeyup='product_convert_qty(1,".$cnt.");' />
								".$row['unit_name']."
								<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
								<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
							</div>
							";
								}
							
						}else{
							/*$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";*/

							if($row['stock_unit'] != $row["conv_unit_id"]){

								$str.="<input type='hidden' min='0' max='' class='form-control ' name='conv_grn_qty[]' id='conv_grn_qty$cnt' value='".$rol['product_conv_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='conv_unit_id[]' id='conv_unit_id$cnt' value='".$row["conv_unit_id"]."' />";

								}else{

									$str.="<input type='hidden' min='0' max='' class='form-control ' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='grn_qty_hide[]' id='grn_qty_hide$cnt' value='' />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />";
								}
						}
							
						$str.="</td>
						<!--<td>
							<input type='number' min='0' max='' data-pendingqty='".round_up($pending_conv_qty,5)."' data-pid='".$row['product_id']."' data-qty='".$row['produ_qty']."' data-mini-tol='".$minimum_tolerance."' data-max-tol='".$maximum_tolerance."' data-tol='".$tolerance."' class='form-control qty_mangement' name='grn_qty[]' id='grn_qty$cnt' value='".$rol['product_qty']."' ".$ronly." />
							<input type='hidden' name='unit_id[]' id='unit_id$cnt' value='".$row["unit_id"]."' />
						</td>-->
						<td style=".$display_none.">
							<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$cnt' required >";
							$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
							$str.="</select>
							<!-- <input type='hidden' name='qc_type[]' id='qc_type$cnt' value='".$qc_st."' /> -->
							<input type='hidden' name='qc_type[]' id='qc_type$cnt' value='no'/>
							<input type='hidden' name='grn_trn_id[]' id='grn_trn_id$cnt' value='".$rol['grn_trn_id']."' />
							<input type='hidden' name='qc_status[]' id='qc_status$cnt' value='".$rol['product_qc']."' />
							<input type='hidden' name='po_ref_id[]' id='po_ref_id$cnt' value='".$row['ref_id']."' />
							<input type='hidden' name='purchaseordertrn_id[]' id='purchaseordertrn_id$cnt' value='".$row['trn_id']."' />
							<input type='hidden' name='to_godown_id' id='to_godown_id' value='".$row['to_godown_id']."' />
						</td>
						<td> ".$btn_delete."
							<!-- <button type='button' class='btn btn-round btn-danger btn-xs' onclick='remove_data(".$cnt.");' id='fieldremove".$cnt."'><i class='fa fa-times'></i></button> -->
						</td>
					</tr>";
			
			$cnt++;	
		}
		$str .= load_scrap_grn_product_data($cnt,$dbcon,$type);
		return $str;
	}

function store_grn_sub_trn_wise_stock_effect($dbcon,$grn_trn_sub_id,$qc_permission,$stop_qty,$material_product_id,$material_pid,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage){
	 $query = "select grn_sub_trn.grn_trn_sub_id,grn_sub_trn.rp_id,grn_sub_trn.product_id,grn_sub_trn.purchaseordertrn_id,grn_sub_trn.job_work_sub_trn_id,grn_sub_trn.product_qty,grn_sub_trn.product_base_unit, p.batch_wise_stock_manage, grn_sub_trn.grn_trn_id from tbl_grn_sub_trn as grn_sub_trn left join product_mst as p on p.product_id = grn_sub_trn.product_id
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
				$workorder_process_id=$process_pr->workorder_process_id;

				if($previous_process_pid=="0" && $next_process_id=="0"){
					
					store_p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$row1['p_id'],$row1['rp_id'],$row['product_qty'],$row['product_base_unit'],$stop_qty,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);
							//raw matirial stock deduct end
				}
				else if($previous_process_pid=="0"){
					
					store_p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$row1['p_id'],$row1['rp_id'],$row['product_qty'],$row['product_base_unit'],$stop_qty,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);
				}else if($next_process_id=="0"){
					
					if($row['batch_wise_stock_manage'] == '1'){
						$bt_query = "SELECT * FROM tbl_batch_temp_material_start_time_deduct WHERE type = 2 and status = 0 AND p_id = ". $row1['p_id'] . " and product_id = " . $row['product_id'] . " and rp_id = " . $row['rp_id'];
			$bt_result = $dbcon->query($bt_query);
			while ($bt_row = brp_mysqli_fetch_assoc($bt_result)) {
				

					$used_mat_qty =  $bt_row['deduct_qty'];
					
					$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$bt_row['p_id']." and rp_id = " . $bt_row['rp_id'] . " and product_id = " . $bt_row['product_id'];
					$mt_res = $dbcon->query($mt_qry);
					
					while($mt_row = brp_mysqli_fetch_assoc($mt_res)){
						
						$pen_qty = $mt_row['pending_qty'];
						$pen_conv_qty = $mt_row['pending_conv_qty'];
						if($used_mat_qty > 0){
							if($pen_qty > 0){
								if($pen_qty>=$used_mat_qty){
									$rqty=$used_mat_qty;
									$used_mat_qty=$used_mat_qty-$used_mat_qty;
								}else{
									$rqty=$pen_qty;
									$used_mat_qty=$used_mat_qty-$pen_qty;
								}

								$type="conv_unit";
								$base_stock=$rqty;
								$con_stock=convert_stock_new($dbcon,$rqty,$mt_row['product_id'],$type);

								$upd_mt_info['used_base_qty'] =  $mt_row['used_base_qty'] + $base_stock;
								$upd_mt_info['used_conv_qty'] =  $mt_row['used_conv_qty'] + $con_stock;

								$updatetrnid=update_record('tbl_material_release_trn',$upd_mt_info,"material_trn_id=".$mt_row['material_trn_id'], $dbcon);

							}
						}
					}
					start_time_production_deduct_process_reserve_stock($dbcon,$bt_row['deduct_qty'],$bt_row['base_unit'],$bt_row['deduct_conv_qty'],$bt_row['conv_unit'],$bt_row['p_id'],$bt_row['tmp_id'],"batch_process_end_time_deduct",date("Y-m-d"),$bt_row['reserve_id']);
				}
					}else{
						
						store_production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$row['grn_trn_sub_id'],"Grn_sub_trn",$stock_date,$stop_qty,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);
						//process stock deduct end
					}
					
				}else{
					
					if($row['batch_wise_stock_manage'] == '1'){
							$bt_query = "SELECT * FROM tbl_batch_temp_material_start_time_deduct WHERE type = 2 and status = 0 AND p_id = ". $row1['p_id'] . " and product_id = " . $row['product_id'] . " and rp_id = " . $row['rp_id'];
							$bt_result = $dbcon->query($bt_query);
							while ($bt_row = brp_mysqli_fetch_assoc($bt_result)) {
								

									$used_mat_qty =  $bt_row['deduct_qty'];
									
									$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$bt_row['p_id']." and rp_id = " . $bt_row['rp_id'] . " and product_id = " . $bt_row['product_id'];
									$mt_res = $dbcon->query($mt_qry);
									
									while($mt_row = brp_mysqli_fetch_assoc($mt_res)){
										
										$pen_qty = $mt_row['pending_qty'];
										$pen_conv_qty = $mt_row['pending_conv_qty'];
										if($used_mat_qty > 0){
											if($pen_qty > 0){
												if($pen_qty>=$used_mat_qty){
													$rqty=$used_mat_qty;
													$used_mat_qty=$used_mat_qty-$used_mat_qty;
												}else{
													$rqty=$pen_qty;
													$used_mat_qty=$used_mat_qty-$pen_qty;
												}

												$type="conv_unit";
												$base_stock=$rqty;
												$con_stock=convert_stock_new($dbcon,$rqty,$mt_row['product_id'],$type);

												$upd_mt_info['used_base_qty'] =  $mt_row['used_base_qty'] + $base_stock;
												$upd_mt_info['used_conv_qty'] =  $mt_row['used_conv_qty'] + $con_stock;

												$updatetrnid=update_record('tbl_material_release_trn',$upd_mt_info,"material_trn_id=".$mt_row['material_trn_id'], $dbcon);

											}
										}
									}
									start_time_production_deduct_process_reserve_stock($dbcon,$bt_row['deduct_qty'],$bt_row['base_unit'],$bt_row['deduct_conv_qty'],$bt_row['conv_unit'],$bt_row['p_id'],$bt_row['tmp_id'],"batch_process_end_time_deduct",date("Y-m-d"),$bt_row['reserve_id']);
								}
						}else{
						store_production_deduct_process_reserve_stock($dbcon,$row['product_qty'],$row['product_base_unit'],$row1['p_id'],$row['grn_trn_sub_id'],"Grn_sub_trn",$stock_date,$stop_qty,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);
							//process stock deduct end
					}
				}
			}
		}
	}
}

function store_p_id_wise_row_material_deduct($dbcon,$grn_trn_sub_id,$p_id,$rp_id,$product_qty,$unit_id,$stop_qty,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage){
	$query = "select rp_id,perent_id,req_qty_one,rp_pid as product_id,branch_id,purchase_unit,process_unit,customer_id from tbl_request_product as req_product
	where status = 0 and  req_product.perent_id=".$rp_id . " order by rp_id";
	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	
	if($cnt>0){
		$x = 0;
		while($row=brp_mysqli_fetch_array($result)){
			 
			if($arr_batch_wise_stock_manage[$x] == '1'){
			
			$query33 = "select rp_id,perent_id,req_qty_one,rp_pid as product_id,branch_id,purchase_unit,process_unit,customer_id from tbl_request_product as req_product
						where req_product.rp_id=".$row['rp_id'];
					$result33=$dbcon->query($query33);
					$row33=brp_mysqli_fetch_array($result33);

				
			$bt_query = "SELECT * FROM tbl_batch_temp_material_start_time_deduct WHERE type = 2 and status = 0 AND p_id = ". $p_id . "  and product_id = " . $row['product_id'] . " and rp_id = " . $row['rp_id'];
			$bt_result = $dbcon->query($bt_query);
			
			while ($bt_row = brp_mysqli_fetch_assoc($bt_result)) {
					
					$info['allocate_process_id']	= $bt_row['p_id'];
					$info['product_id']				= $bt_row['product_id'];
					$info['qty_need_for_single']	= $row33['req_qty_one'];
					$info['total_req_qty']			= $bt_row['deduct_qty'];
					$info['used_qty']				= $bt_row['deduct_qty'];
					$info['unit_id']				= $bt_row['base_unit'];
					$info['grn_trn_sub_id']			= '';
					$info['remark']			= 'Batch Process start time material deduct';
					$info['cdate']					= date("Y-m-d H:i:s");
					$info['user_id']				= $_SESSION['user_id'];
					$info['company_id']				= $_SESSION['company_id'];

				$qry = "select * from tbl_reserve_stock where stock_status != 2 and reserve_id = " . $bt_row['reserve_id'];
					$result1=$dbcon->query($qry);
					$row1=brp_mysqli_fetch_array($result1);
					
					$qry1 = "select * from tbl_stock_trn where stock_status = 0 and stock_id=".$row1['stock_id'];
					$result2=$dbcon->query($qry1);
					$row2=brp_mysqli_fetch_array($result2);
		
					$rate = $row2['base_rate'];
					$conv_rate = $row2['conv_rate']; 
					$info['rate']			= $rate;
					$info['total_rate']		= $rate * $bt_row['deduct_qty'];
					$info['conv_rate']			= $conv_rate;
					$info['total_conv_rate']		= $conv_rate * $bt_row['deduct_conv_qty'];
					
					$process_material_id=add_record('tbl_allocate_process_material',$info, $dbcon,$row['branch_id']);

					$used_mat_qty =  $bt_row['deduct_qty'];
				
					$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$bt_row['p_id']." and material_trn_id = ".$bt_row['mt_trn_id']."  and rp_id = "  . $bt_row['rp_id'];
					$mt_res = $dbcon->query($mt_qry);
					
					while($mt_row = brp_mysqli_fetch_assoc($mt_res)){
						
						$pen_qty = $mt_row['pending_qty'];
						$pen_conv_qty = $mt_row['pending_conv_qty'];

						// var_dump($used_mat_qty  .' --- ' . $pen_qty);
						if($used_mat_qty > 0){
							if($pen_qty > 0){
								if($pen_qty>=$used_mat_qty){
									$rqty=$used_mat_qty;
									$used_mat_qty=$used_mat_qty-$used_mat_qty;
								}else{
									$rqty=$pen_qty;
									$used_mat_qty=$used_mat_qty-$pen_qty;
								}

								$type="conv_unit";
								$base_stock=$rqty;
								$con_stock=convert_stock_new($dbcon,$rqty,$mt_row['product_id'],$type);

								$upd_mt_info['used_base_qty'] =  $mt_row['used_base_qty'] + $base_stock;
								$upd_mt_info['used_conv_qty'] =  $mt_row['used_conv_qty'] + $con_stock;

								$updatetrnid=update_record('tbl_material_release_trn',$upd_mt_info,"material_trn_id=".$mt_row['material_trn_id'], $dbcon);

							}
						}
					}
					
					 batch_start_time_material_deduct_reserve_stock($dbcon,$bt_row['deduct_qty'],$bt_row['base_unit'],$bt_row['deduct_conv_qty'],$bt_row['conv_unit'],$bt_row['p_id'],$bt_row['tmp_id'],'batch_process_end_time_deduct',date("Y-m-d"),$bt_row['reserve_id']);
					}
					
			}else{
			
			// $total_required_qty=$row['req_qty_one']*$product_qty;
			$req_qty_one = $material_used_qty[$x] / $stop_qty;

			if(empty($material_used_qty)){
				$req_qty_one = $row['req_qty_one'];				
			}

			$total_required_qty=$req_qty_one *$product_qty;

			$godown_id = $material_godown_id[$x];


			$gd_whr = "";

			if(!empty($godown_id)){
				$gd_whr = " and godown_id = '" . $godown_id. "'";				
			}
			
			
			$info['allocate_process_id']	= $p_id;
			$info['product_id']				= $row['product_id'];
			$info['qty_need_for_single']	= $req_qty_one;
			$info['total_req_qty']			= $total_required_qty;
			//$info['unit_id']				= $unit_id;
			$info['unit_id']				= $row['process_unit'];
			$info['grn_trn_sub_id']			= $grn_trn_sub_id;

			$qry2 = "select * from tbl_grn_sub_trn where grn_trn_sub_id=".$grn_trn_sub_id;
			$result3=$dbcon->query($qry2);
			$row3=brp_mysqli_fetch_array($result3);

			$qry = "select res.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_reserve_stock as res where stock_status != 2 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and stock_flage = 1 and ref_name = 'store_release' and godown_id = '".ON_FLOOR_GODOWN."' and p_id=".$p_id." and product_id = " . $row['product_id'] . $gd_whr;
			$result1=$dbcon->query($qry);
			while($row1=brp_mysqli_fetch_array($result1)){
				$qry1 = "select * from tbl_stock_trn where stock_status = 0 and stock_id=".$row1['stock_id'];
				$result2=$dbcon->query($qry1);
				$row2=brp_mysqli_fetch_array($result2);
	
				$rate = $row2['base_rate'];
				$conv_rate = $row2['conv_rate']; 
				$info['rate']			= $rate;
				$info['total_rate']		= $rate * $total_required_qty;

				$total_required_conv_qty = convert_stock($dbcon,$total_required_qty, $row['product_id'],"conv_unit");
				$info['conv_rate']			= $conv_rate;
				$info['total_conv_rate']		= $conv_rate * $total_required_conv_qty;
			
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];

				
			}
				
			$process_material_id=add_record('tbl_allocate_process_material',$info, $dbcon,$row['branch_id']);	
			$used_mat_qty =  $total_required_qty;
			
			 $mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and rp_id = "  . $row['rp_id'];
			$mt_res = $dbcon->query($mt_qry);
			
			while($mt_row = brp_mysqli_fetch_assoc($mt_res)){
				
				$pen_qty = $mt_row['pending_qty'];
				$pen_conv_qty = $mt_row['pending_conv_qty'];
				if($total_required_qty > 0){
					if($pen_qty > 0){
						if($pen_qty>=$used_mat_qty){
							$rqty=$used_mat_qty;
							$used_mat_qty=$used_mat_qty-$used_mat_qty;
						}else{
							$rqty=$pen_qty;
							$used_mat_qty=$used_mat_qty-$pen_qty;
						}

						$type="conv_unit";
						$base_stock=$rqty;
						// $con_stock=convert_stock_new($dbcon,$rqty,$mt_row['product_id'],$type);
						$con_stock=($base_stock/$mt_row['base_qty']) * $mt_row['conv_qty'];

						$upd_mt_info['used_base_qty'] =  $mt_row['used_base_qty'] + $base_stock;
						$upd_mt_info['used_conv_qty'] =  $mt_row['used_conv_qty'] + $con_stock;

						$updatetrnid=update_record('tbl_material_release_trn',$upd_mt_info,"material_trn_id=".$mt_row['material_trn_id'], $dbcon);

					}
				}
			}

			$qry_2 = "select * from tbl_allocate_process where p_id=".$p_id;
			$result_3=$dbcon->query($qry_2);
			$row_3=brp_mysqli_fetch_array($result_3);


			$q_111 = "select trn.*,pro.product_name from tbl_workorder_direct_material_issue_trn as trn
						left join tbl_workorder_direct_material_issue as mst on mst.material_issue_id = trn.material_issue_id
					left join product_mst as pro on pro.product_id = trn.product_id
					where mst.status = 1 and trn.status = 0 and trn.flag = 0 and mst.rp_id = " . $rp_id . " and mst.process_id = " . $row_3['process_id'];
			$rel_111=$dbcon->query($q_111);
			$total_extra_rate = 0;
			if(brp_mysqli_num_rows($rel_111)>0){
				while($row_111 = brp_mysqli_fetch_assoc($rel_111)){

				$queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
				where ref_name ='workorder_direct_material_issue' and stock.ref_id in(".$row_111['material_issue_trn_id'].") and stock.stock_status = 0 and stock.stock_flage = 2 and product_id = " . $row_111['product_id'];
				$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
				
				$total_extra_rate = $relp_2['base_rate'] * $row_111['base_qty'];
				$total_extra_conv_rate = $relp_2['conv_rate'] * $row_111['conv_qty']; 
				
				$upd_wo_mat['flag'] = 1;
				$updatetrnid=update_record('tbl_workorder_direct_material_issue_trn',$upd_wo_mat,"material_issue_trn_id=".$row_111['material_issue_trn_id'], $dbcon);
				}

			}

			$update_grn['material_rate'] = $row3['material_rate'] + $info['total_rate'] + $total_extra_rate;
			$update_grn['material_conv_rate'] = $row3['material_rate'] + $info['total_conv_rate']+$total_extra_conv_rate;
			$update_grn['process_pus_material_rate'] = $update_grn['material_rate'] + $row3['total_process_rate'];
			$update_grn['process_pus_material_conv_rate'] = $update_grn['material_conv_rate']+ $row3['total_process_conv_rate'];
			
			$updatetrnid=update_record('tbl_grn_sub_trn',$update_grn,"grn_trn_sub_id=".$grn_trn_sub_id , $dbcon);
		
			tbl_allocate_process_material_wise_reserve_stock_minus($dbcon,$process_material_id,$grn_trn_sub_id,ON_FLOOR_GODOWN,$row['rp_id']);
		}
			$x++; 	
			
		}
		

		/*  CHECK FROM HERE PENDING ISSUE FOR JET TECHNOLOGY  */
		if(!empty($material_godown_action)){
			$ss_qry = "select * from tbl_start_stop_production where p_id = " . $p_id;
			$ss_res = $dbcon->query($ss_qry);
			// echo "</br></br>";
			while($ss_row = brp_mysqli_fetch_assoc($ss_res)){
				// var_dump($ss_row['start_qty']);
				// var_dump($ss_row['end_qty']);
				if($ss_row['pending_qty'] == '0'){
					//store_release
				$mtrn_qry = "select * from tbl_material_release_trn where status = 0 AND start_stop_id = " . $ss_row['start_stop_id'] . " and p_id = " . $p_id . " and material_id = ". $ss_row['material_id'];
			
					$mtrn_res = $dbcon->query($mtrn_qry);
					$i = 0;
					while($mtrn_row = brp_mysqli_fetch_assoc($mtrn_res)){
						$pen_stock = $mtrn_row['base_qty'] - $mtrn_row['used_base_qty'];
						$pen_conv_stock = $mtrn_row['conv_qty'] - $mtrn_row['used_conv_qty'];
						
						if($pen_stock > 0){
							if($material_godown_action[$i] == '2'){

								 $rt_qry = "select mt.*,ss.complete_status from tbl_material_release_trn as mt  left join tbl_start_stop_production as ss on ss.start_stop_id = mt.start_stop_id where mt.status = 0 and mt.release_status = 1 and mt.return_status = 0 and mt.product_id = " . $mtrn_row['product_id'];
								$rt_res = $dbcon->query($rt_qry);
								// echo "</br></br>";

								while($rt_row = brp_mysqli_fetch_assoc($rt_res)){
									if(($rt_row['base_qty'] - $rt_row['used_base_qty']) > 0 && $rt_row['complete_status'] = '3'){
										$gd_info['product_id'] 		= $rt_row['product_id'];
										$gd_info['material_trn_id'] = $rt_row['material_trn_id'];
										$gd_info['base_qty'] 		= ($rt_row['base_qty'] - $rt_row['used_base_qty']);
										$gd_info['base_unit'] 		= $rt_row['base_unit'];
										$gd_info['conv_qty'] 		= $rt_row['conv_qty'] - $rt_row['used_conv_qty'];
										$gd_info['conv_unit'] 		= $rt_row['conv_unit'];
										$gd_info['status'] 			= 0;
										$gd_info['store_accept'] 	= 0;
										$gd_info['godown_id']		= $rt_row['to_godown_id'];
										$gd_info['cdate']			= date("Y-m-d H:i:s");
										$gd_info['user_id']			= $_SESSION['user_id'];
										$gd_info['company_id']		= $_SESSION['company_id'];

										$return_id=add_record('tbl_godown_stock_return',$gd_info, $dbcon);

										$upd_info['return_status'] = 1;
										$updatetrnid=update_record('tbl_material_release_trn',$upd_info,"material_trn_id=".$rt_row['material_trn_id'], $dbcon);

									}
								}
							}
							/*$query_1 = "select * from tbl_reserve_stock where  stock_status = 0 and stock_flage = 1 and p_id = " . $mtrn_row['p_id'] . " and product_id = " . $mtrn_row['product_id'] . " and ref_name = 'store_release' and ref_id = " . $mtrn_row['material_trn_id'];*/

							$query1 = "select res.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_reserve_stock as res where  stock_status = 0 and stock_flage = 1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and ref_name = 'store_release' and p_id = " . $mtrn_row['p_id'] . " and godown_id = '".ON_FLOOR_GODOWN."' and product_id = " . $mtrn_row['product_id']. " AND ref_id = ". $mtrn_row['material_trn_id'] . " ORDER BY res.reserve_id";
							$result1=$dbcon->query($query1);

							while($res1 =brp_mysqli_fetch_array($result1)){
								$pending_stock = $res1['pending_base_stock'];
								$stock_id = $res1['stock_id'];
								if($pen_stock>0){
								if($pending_stock>0){
									if($pending_stock>=$pen_stock){
										$rqty=$pen_stock;
										$pen_stock=$pen_stock-$pen_stock;
									}else{
										$rqty=$pending_stock;
										$pen_stock=$pen_stock-$pending_stock;
									}

									$type="conv_unit";
									$base_stock=$rqty;
									$con_stock=convert_stock_new($dbcon,$rqty,$mtrn_row['product_id'],$type);
									$pen_conv_stock = $pen_conv_stock  - $con_stock;

									$stock_date=date("Y-m-d");
	 								material_reserve_entry_godown_wise($dbcon,$stock_date,$res1['product_id'],$res1['godown_id'],$res1['base_unit'],$base_stock,$res1['convert_unit'],$con_stock,'2',$res1['request_id'],$res1['ref_name'],$res1['ref_id'],$res1['sales_order_trn_id'],$res1['stock_id'],$res1['branch_id'],$mtrn_row['p_id'],$res1['reserve_id']);

	 								$qry2 = "select * from tbl_stock_trn where stock_status = 0 and stock_id = " . $res1['stock_id'];
									$result2=$dbcon->query($qry2);
									$row2 = brp_mysqli_fetch_array($result2);

									$used_base_stock=$row2['used_base_stock']-$base_stock;
									$used_convert_stock=$row2['used_convert_stock']-$con_stock;

									$info_stock['used_base_stock']		= $used_base_stock;
									$info_stock['used_convert_stock']	= $used_convert_stock;
									$info_stock['ref_name']	= 'store_return_on_floor';
									
									$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$res1['stock_id'], $dbcon);
								}
							}
								
							}

						}
						
						$i++;
					}
				}
			}
		}
	}
}
function store_production_deduct_process_reserve_stock($dbcon,$product_qty,$unit_id,$p_id,$ref_id,$ref_name,$stock_date,$stop_qty,$material_used_qty,$material_godown_action,$material_godown_id,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage){
	$batch_wise_stock_manage = $arr_batch_wise_stock_manage[0];
	$rp_id = $arr_rp_id[0];

	if($batch_wise_stock_manage == '1'){
		$bt_query = "SELECT * FROM tbl_batch_temp_material_start_time_deduct WHERE type = 2 and status = 0 AND p_id = ". $p_id . " and product_id = " . $row['product_id'] . " and rp_id = " . $row['rp_id'];
		$bt_result = $dbcon->query($bt_query);
				
		while($bt_row = brp_mysqli_fetch_assoc($bt_result)) {
			if($bt_row['is_process_stock'] == '1'){

				$used_mat_qty =  $bt_row['deduct_qty'];
				
				$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$bt_row['p_id']." and rp_id = " . $bt_row['rp_id'] . " and product_id = " . $bt_row['product_id'] . " and stock_id = " . $bt_row['reserve_id'];
				$mt_res = $dbcon->query($mt_qry);
				
				while($mt_row = brp_mysqli_fetch_assoc($mt_res)){
					
					$pen_qty = $mt_row['pending_qty'];
					$pen_conv_qty = $mt_row['pending_conv_qty'];
					if($used_mat_qty > 0){
						if($pen_qty > 0){
							if($pen_qty>=$used_mat_qty){
								$rqty=$used_mat_qty;
								$used_mat_qty=$used_mat_qty-$used_mat_qty;
							}else{
								$rqty=$pen_qty;
								$used_mat_qty=$used_mat_qty-$pen_qty;
							}

							$type="conv_unit";
							$base_stock=$rqty;
							$con_stock=convert_stock_new($dbcon,$rqty,$mt_row['product_id'],$type);

							$upd_mt_info['used_base_qty'] =  $mt_row['used_base_qty'] + $base_stock;
							$upd_mt_info['used_conv_qty'] =  $mt_row['used_conv_qty'] + $con_stock;
							$product_qty =$product_qty -  $base_stock;
							$updatetrnid=update_record('tbl_material_release_trn',$upd_mt_info,"material_trn_id=".$mt_row['material_trn_id'], $dbcon);

						}
					}
				}
				start_time_production_deduct_process_reserve_stock($dbcon,$bt_row['deduct_qty'],$bt_row['base_unit'],$bt_row['deduct_conv_qty'],$bt_row['conv_unit'],$bt_row['p_id'],$bt_row['tmp_id'],"batch_process_end_time_deduct",date("Y-m-d"),$bt_row['reserve_id']);
			}		
		}
	}else{
		$query = "select process_reserve_id,product_id,process_id,branch_id,process_stock_id,godown_id from tbl_process_reserve_stock as allo_mat
		where allo_mat.stock_status=0 and stock_flage=1 and allo_mat.p_id=".$p_id." order by allo_mat.process_reserve_id desc";
		$result=$dbcon->query($query);
		while($row=brp_mysqli_fetch_array($result)){
			if($product_qty>"0"){
				$reserve_stock=production_process_reseve_stock($dbcon,$unit_id,$row['branch_id'],$p_id,$row['product_id'],$row['process_id'],$row['process_reserve_id'],$row['process_stock_id'],0,$row['godown_id']);

				//  var_dump('reseve qty' . $reserve_stock);
				//  var_dump('process_reserve_id ' . $row['process_reserve_id']);
				//  var_dump('godown_id ' . $row['godown_id']);
				//  var_dump('materialgodown_id ' . $material_godown_id);
				
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
						$base_stock=$used_qty;
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
					$info_stockadd['perent_id']					=  $row['process_reserve_id'];
					$info_stockadd['cdate']						= date("Y-m-d H:i:s");
					$info_stockadd['user_id']					= $_SESSION['user_id'];
					$info_stockadd['company_id']				= $_SESSION['company_id'];

				$qry = "select * from tbl_process_reserve_stock where stock_status = 0 and stock_flage = 1 and process_reserve_id = ".$row['process_reserve_id']." and p_id=".$p_id." order by p_id desc";
				$result1=$dbcon->query($qry);
				$row1=brp_mysqli_fetch_array($result1);


				$uppd_res_stock['used_base_stock'] = $row1['used_base_stock'] + $base_stock;
				$uppd_res_stock['used_conv_stock'] = $row1['used_conv_stock'] +  $con_stock;


				$updatetrnid=update_record('tbl_process_reserve_stock',$uppd_res_stock,"process_reserve_id=".$row1['process_reserve_id'], $dbcon);
			
				$qry1 = "select * from tbl_process_stock_trn where stock_status = 0 and process_stock_id=".$row1['process_stock_id'];
				$result2=$dbcon->query($qry1);
				$row2=brp_mysqli_fetch_array($result2);
			
		
				$qry2 = "select strn.*,trn.process_id from tbl_grn_sub_trn as strn 
				left join tbl_grn_trn as trn on trn.grn_trn_id = strn.grn_trn_id
				where grn_trn_sub_id=".$ref_id;
				$result3=$dbcon->query($qry2);
				$row3=brp_mysqli_fetch_array($result3);

				$rate = $row2['process_base_rate'];
				$conv_rate = $row2['process_conv_rate']; 
				
				$total_rate		= (float)$rate * (float)$base_stock;
				$total_conv_rate = (float)$conv_rate * (float)$con_stock;
			
				$info['cdate']					= date("Y-m-d H:i:s");
				$info['user_id']				= $_SESSION['user_id'];
				$info['company_id']				= $_SESSION['company_id'];

				$process_reserve_id=add_record('tbl_process_reserve_stock',$info_stockadd, $dbcon,$row['branch_id']);


				$q_111 = "select trn.*,pro.product_name from tbl_workorder_direct_material_issue_trn as trn
							left join tbl_workorder_direct_material_issue as mst on mst.material_issue_id = trn.material_issue_id
						left join product_mst as pro on pro.product_id = trn.product_id
						where mst.status = 1 and trn.status = 0 and trn.flag = 0 and mst.rp_id = " . $row3['rp_id'] . " and mst.process_id = " . $row3['process_id'];
				$rel_111=$dbcon->query($q_111);
				$total_extra_rate = 0;
				if(brp_mysqli_num_rows($rel_111)>0){
					while($row_111 = brp_mysqli_fetch_assoc($rel_111)){

					$queryp_2="select IFNULL(AVG(stock.base_rate),0) as base_rate,IFNULL(AVG(stock.conv_rate),0) as conv_rate,base_unit,convert_unit from tbl_stock_trn as stock
					where ref_name ='workorder_direct_material_issue' and stock.ref_id in(".$row_111['material_issue_trn_id'].") and stock.stock_status = 0 and stock.stock_flage = 2 and product_id = " . $row_111['product_id'];
					$relp_2=mysqli_fetch_assoc($dbcon->query($queryp_2));
					
					$total_extra_rate = $relp_2['base_rate'] * $row_111['base_qty'];
					$total_extra_conv_rate = $relp_2['conv_rate'] * $row_111['conv_qty'];
					
					$upd_wo_mat['flag'] = 1;
					$updatetrnid=update_record('tbl_workorder_direct_material_issue_trn',$upd_wo_mat,"material_issue_trn_id=".$row_111['material_issue_trn_id'], $dbcon);
					}

				}

				$update_grn['material_rate'] = $total_rate + $total_extra_rate;
				$update_grn['material_conv_rate'] = $total_conv_rate + $total_extra_conv_rate;
				$update_grn['process_pus_material_rate'] = $update_grn['material_rate'] + $row3['total_process_rate'];
				$update_grn['process_pus_material_conv_rate'] = $update_grn['material_conv_rate'] + $row3['total_process_conv_rate'];
				
				$updatetrnid=update_record('tbl_grn_sub_trn',$update_grn,"grn_trn_sub_id=".$ref_id, $dbcon);
					
					production_deduct_process_stock($dbcon,$info_stockadd['base_stock'],$info_stockadd['base_unit'],$info_stockadd['p_id'],$info_stockadd['process_reserve_date'],$info_stockadd['godown_id'],$info_stockadd['ref_name'],$info_stockadd['ref_id'],$info_stockadd['process_stock_id'],$row['process_reserve_id']);
				}
			}
		}
	}				

	
}

function material_reserve_entry_godown_wise($dbcon,$res_date,$product_id,$godown_id,$base_unit,$base_stock,$conv_unit,$conv_stock,$stock_flage,$req_id,$ref_name,$ref_id,$sales_order_trn_id,$stock_id,$branch_id,$p_id,$reserve_id=0){
// var_dump("unit ::: " . $base_unit);
	$info_rese['reserve_date']		= $res_date;
	$info_rese['product_id']		= $product_id;
	$info_rese['godown_id']			= $godown_id;
	$info_rese['base_unit']			= $base_unit;
	$info_rese['base_stock']		= $base_stock;
	$info_rese['convert_unit']		= $conv_unit;
	$info_rese['approve_base_stock']		= $base_stock;
	$info_rese['approve_convert_stock']		= $conv_stock;
	$info_rese['convert_stock']		= $conv_stock;
	$info_rese['stock_flage']		= $stock_flage;
	$info_rese['request_id']		= $req_id;
	$info_rese['ref_name']			= $ref_name;
	$info_rese['ref_id']			= $ref_id;
	$info_rese['sales_order_trn_id']= $sales_order_trn_id;
	$info_rese['stock_id']			= $stock_id;
	$info_rese['p_id']				= $p_id;
	$info_rese['perent_id']				= $reserve_id;
	
	$info_rese['cdate']					= date("Y-m-d H:i:s");
	$info_rese['user_id']				= $_SESSION['user_id'];
	$info_rese['company_id']			= $_SESSION['company_id'];		
						
	$reserve_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$branch_id);

	if($stock_flage == '1'){
		$query = "SELECT * FROM tbl_request_product WHERE rp_id = " . $req_id;
		$row = brp_mysqli_fetch_assoc($dbcon->query($query));
		if($row['workorder_type'] == '1'){
			$finish_used_qty = 0;
			$shortage_complete_qty = 0;

			if(!empty($row['finish_used_qty'])){
				$finish_used_qty = $row['finish_used_qty'];	
			}

			if(!empty($row['shortage_complete_qty'])){
				$shortage_complete_qty = $row['shortage_complete_qty'];	
			}
			
			$rp_info['finish_used_qty'] = $finish_used_qty + $conv_stock;
			$rp_info['shortage_complete_qty'] = $shortage_complete_qty + $conv_stock;

			if($rp_info['finish_used_qty'] >= $row['rp_req_qty']){
				$rp_info['status'] = 0; 
				$rp_info['finish_status'] = 1;
			}

			$updatetrnid=update_record('tbl_request_product',$rp_info,"rp_id=".$row['rp_id'], $dbcon);
		}

	}
	

	return $reserve_id;
}


function material_process_reserve_entry_godown_wise($dbcon,$res_date,$product_id,$godown_id,$base_unit,$base_stock,$conv_unit,$conv_stock,$stock_flage,$ref_name,$ref_id,$stock_id,$branch_id,$p_id,$process_id,$process_reserve_id=0){

	$info_rese['process_reserve_date']		= $res_date;
	$info_rese['product_id']		= $product_id;
	$info_rese['process_id']				= $process_id; // remain
	$info_rese['godown_id']			= $godown_id;
	$info_rese['base_unit']			= $base_unit;
	$info_rese['base_stock']		= $base_stock;
	$info_rese['conv_unit']		= $conv_unit;
	$info_rese['conv_stock']		= $conv_stock;
	$info_rese['approve_base_stock']		= $base_stock;
	$info_rese['approve_convert_stock']		= $conv_stock;
	$info_rese['stock_flage']		= $stock_flage;
	$info_rese['p_id']			= $p_id;
	$info_rese['ref_name']			= $ref_name;
	$info_rese['ref_id']			= $ref_id;
	
	$info_rese['process_stock_id']			= $stock_id;
	$info_rese['p_id']				= $p_id;
	$info_rese['perent_id']				= $process_reserve_id;
	$info_rese['cdate']					= date("Y-m-d H:i:s");
	$info_rese['user_id']				= $_SESSION['user_id'];
	$info_rese['company_id']			= $_SESSION['company_id'];		
						
	$reserve_id=add_record('tbl_process_reserve_stock',$info_rese, $dbcon,$branch_id);

	return $reserve_id;
}

	function wip_purchase_stock($dbcon,$product_id,$unit_id){
     $query1 = "select IFNULL(sum(wip_po_stock-wip_po_used_stock),0) as pobase_stock from tbl_purchaseordertrn as hsn
            left join tbl_purchaseorder as req on req.purchaseorder_id=hsn.purchaseorder_id
        where purchaseordertrn_status=0 and hsn.purchaseorder_id!=0 and req.po_approval_status=1 and unit_id=".$unit_id." and product_id=".$product_id;
         $result1 = $dbcon->query($query1);
        $row1 = brp_mysqli_fetch_array($result1);

        $query2 = "select IFNULL(sum(wip_po_stock_conv-wip_po_used_stock_conv),0) as poconv_stock from tbl_purchaseordertrn as hsn
            left join tbl_purchaseorder as req on req.purchaseorder_id=hsn.purchaseorder_id
        where purchaseordertrn_status=0 and hsn.purchaseorder_id!=0 and req.po_approval_status=1 and hsn.unit_id!=hsn.conv_unit_id and hsn.conv_unit_id=".$unit_id." and hsn.product_id=".$product_id;
         $result2 = $dbcon->query($query2);
       $row2 = brp_mysqli_fetch_array($result2);
        $po_stock=$row1['pobase_stock']+$row2['poconv_stock'];
        return floatval($po_stock);
}

function reprocess_jobwork_product_for_pending_grn($dbcon,$vender_id,$jobwork_trn_id){
	 	 $company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);
		if(!empty($jobwork_trn_id)){
			$job_where=" and job_trn.job_work_trn_id in (".$jobwork_trn_id.")";
		}

		if($company_config['jobwork_grn'] == '0'){
		 $query = "select GROUP_CONCAT(job.job_work_no) as job_work_no,GROUP_CONCAT(job.chalan_no) as chalan_no,GROUP_CONCAT(res.job_card_no) as job_card_no, sum(job_sub_trn.product_base_qty) as job_qty,GROUP_CONCAT(job_sub_trn.p_id) as p_id,GROUP_CONCAT(job_sub_trn.job_work_sub_trn_id) as job_sub_trn_id,job_trn.product_id,job_trn.process_id,job_trn.product_base_unit,job_trn.product_con_unit,promst.product_type,promst.product_name,process_ms.process_name,job_trn.qc_id, unit.unit_name, promst.batch_wise_stock_manage,promst.product_icode, dr.drawing_number,promst.product_mat_center from tbl_job_work as job
					left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
					left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id
					left join product_mst as promst on promst.product_id=job_trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = promst.drawing_id
					left join tbl_request_product as res on res.rp_id=job_sub_trn.rp_id
					left join unit_mst as unit on unit.unitid=job_trn.product_base_unit
					left join process_mst as process_ms on process_ms.process_id=job_trn.process_id
					where job.grn_complete_status=0 and job.is_reprocess=1 and job.job_work_type in (2,4) and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.grn_complete_status=0 and job_trn.grn_complete_status=0 and job.vender_id=".$vender_id." ".$job_where." group by job_trn.process_id,job_trn.product_id,job.job_work_type,job_trn.qc_id" ;
		}else{
			 $query = "select GROUP_CONCAT(job.job_work_no) as job_work_no,GROUP_CONCAT(job.chalan_no) as chalan_no,GROUP_CONCAT(res.job_card_no) as job_card_no, sum(job_sub_trn.product_base_qty) as job_qty,GROUP_CONCAT(job_sub_trn.p_id) as p_id,GROUP_CONCAT(job_sub_trn.job_work_sub_trn_id) as job_sub_trn_id,job_trn.product_id,job_trn.process_id,job_trn.product_base_unit,job_trn.product_con_unit,promst.product_type,promst.product_name,process_ms.process_name,job_trn.qc_id, unit.unit_name, promst.batch_wise_stock_manage,promst.product_icode, dr.drawing_number, promst.product_mat_center from tbl_job_work as job
					left join tbl_job_work_trn as job_trn on job_trn.job_work_id=job.job_work_id
					left join tbl_job_work_sub_trn as job_sub_trn on job_sub_trn.job_work_trn_id=job_trn.job_work_trn_id
					left join product_mst as promst on promst.product_id=job_trn.product_id
					left join tbl_drawing as dr on dr.drawing_id = promst.drawing_id
					left join tbl_request_product as res on res.rp_id=job_sub_trn.rp_id
					left join unit_mst as unit on unit.unitid=job_trn.product_base_unit
					left join process_mst as process_ms on process_ms.process_id=job_trn.process_id
					where job.grn_complete_status=0  and job.is_reprocess=1 and job.job_work_type in (2,4) and job.job_work_status=0 and job_trn.job_work_trn_status=0 and job_sub_trn.job_work_sub_trn_status=0 and job_sub_trn.grn_complete_status=0 and job_trn.grn_complete_status=0 and job.vender_id=".$vender_id." ".$job_where." group by job_trn.process_id,job_trn.product_id,job.job_work_type,job_trn.qc_id,job.job_work_id" ;
		}
		// echo $query;
		$result=$dbcon->query($query);
		$cnt=brp_mysqli_num_rows($result);
		if($cnt>0){
			$i=1;
			while($row=brp_mysqli_fetch_array($result))
			{
				$qc_paramter_info = check_product_qc_paramter($dbcon,$row['product_id'],$row['process_id']);
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

				$product_id = $row['product_id'];

				$type="conv_unit";
				$pending_conv_qty=convert_stock($dbcon,$pending_qty,$product_id ,$type);

				$drawing_number = "";
				$item_code = "";

				
				
				 if(in_array('drawing',$pro_search)){
			            $drawing_number = " -- (".$row['drawing_number'].")";
			        }
			        if(in_array('item',$pro_search)){
			            $item_code = " -- (".$row['product_icode'].")";
			        }

			    

				$process=p_id_wise_find_previous_and_next_reprocess($dbcon,$row['p_id']);
				$process_pr=json_decode($process);

		
				$next_process_id=$process_pr->next_process_id;
				
				$rol['grn_godown'] = $row['product_mat_center'];
				
				$product_name = '"'.$row['product_name'].'"';	
				$unit_name = '"'.$row['unit_name'].'"';
				if($pending_qty>0){
					 $str .="<tr>";
						$str .="<td>".$cnt."</td>
								
								<td>".$row['product_name']." (".$row['process_name'].' '.$item_code.' '.$drawing_number." ".$reprocess.")
									</br> <strong> Jobwork No : ". $row['job_work_no'] ." </strong>" ." 
									</br> <strong> Jobwork chalan No : ". $row['chalan_no'] ." </strong>" ." 
									</br> <strong> Jobcard No : ". $row['job_card_no'] ." </strong>" ." 
								</td>
								<td>".$row['product_name']. "</td>
								<td>".$row['job_qty']." </br><span style='color:green; margin-left:5px;font-weight: bold;'>".$row['unit_name']."</span></td>
								<td>".$pending_qty." </br><span style='color:green; margin-left:5px;font-weight: bold;'>".$row['unit_name']."</span></td>
								<td>
									<input type='text' class='form-control entered_qty".$i."' max='".$pending_qty."' name='grn_qty[]' id='grn_qty$i' value='".$pending_qty."' onkeyup='product_convert_qty(1,".$cnt.");' /> 
									<input type='hidden' class='form-control".$i."' max='".$pending_conv_qty."' name='conv_grn_qty[]' id='conv_grn_qty$i' value='".$pending_conv_qty."' /> 
									<input type='hidden' class='form-control".$i."' max='".$pending_qty."' name='grn_qty_hide[]' id='grn_qty_hide$i' value='".$pending_qty."' /> 
									<input type='hidden' class='form-control".$i."' max='".$pending_conv_qty."' name='conv_grn_qty_hide[]' id='conv_grn_qty_hide$i' value='".$pending_conv_qty."' /> 	
									<span style='color:green; margin-left:5px;font-weight: bold;'>".$row['unit_name']."</span>";

							if($row['batch_wise_stock_manage'] == 1 && $next_process_id == 0){
								//$str .="<button type='button' class='btn btn-round btn-success btn-xs' onclick='show_batch_data(".$cnt.",".$pending_qty.",".$row['product_id'].",".$product_name.",".$unit_name.",".$row["product_base_unit"].");' ><i class='fa fa-plus'></i></button>";
							}	
							$str.="</td>
								<td>
									<select class='form-control' name='grn_godown[]' style='".$sty."' id='grn_godown$i' required >";
									$str.= get_all_godown($dbcon,$rol['grn_godown'],1);
								$str.="</select>
								</td>
								<td>
								<!-- <input type='button' id='addprocess".$i."' class='btn btn-primary' data-original-title='Add Process' data-toggle='tooltip' data-placement='top' onclick='' value='Add'>--></td> 
								<input type='hidden' name='grn_pid[]' id='grn_pid$i' value='".$row['product_id']."' />
								<input type='hidden' name='product_id[]' id='product_id$i' value='".$row['product_id']."' />
								<input type='hidden' name='process_id[]' id='process_id$i' value='".$row['process_id']."' />
								<input type='hidden' name='product_base_unit[]' id='product_base_unit$i' value='".$row['product_base_unit']."' />
								<input type='hidden' name='product_conv_unit[]' id='product_conv_unit$i' value='".$row['product_con_unit']."' />
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


function production_material_release_stock_p_id_wise($dbcon,$product_id,$unit_id,$p_id,$request_id){

		$que="select * from product_mst as ta where product_id=".$product_id;
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);

			$p_id_where= "";
		
			$rwhser="";
			$rwhser1="";
			$rwhser2="";
			$rwhser22="";
			$rwhser23="";
			$where_branch="";	
			$where_godown=""			;	

		
		if(!empty($p_id)){
			$p_id_where=" and p_id=".$p_id;
		}
		
		if(!empty($request_id)){
			$rwhser1=" and request_id=".$request_id;
		}
		
		// $where_godown=" and godown_id='".ON_FLOOR_GODOWN."'";	
		$rwhser1 .=" and ref_name='store_release'";	

		if($re['product_conv_unit'] == $unit_id){
			$query1="select IFNULL(sum(convert_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and convert_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1;

			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($result1);
			
			$query2="select IFNULL(sum(base_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and base_unit=".$unit_id." and product_id=".$product_id.$rwhser1;
			$result2=$dbcon->query($query2);
			$row2=brp_mysqli_fetch_assoc($result2);
					
			$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
			
		}else{

			$query1="select IFNULL(sum(base_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1;

			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($result1);

			$query2="select IFNULL(sum(convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and convert_unit=".$unit_id." and product_id=".$product_id.$rwhser1;
			$result2=$dbcon->query($query2);
			$row2=brp_mysqli_fetch_assoc($result2);
			
			$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
			
		}
		
		
		return $res_qty;
		
	}

	function production_reserve_stock_p_id_wise_new($dbcon,$product_id,$unit_id,$p_id,$reserve_id,$totaladd,$godown_id="",$request_id=0){

		$que="select * from product_mst as ta where product_id=".$product_id;
		$rs_di=$dbcon->query($que);
		$re=brp_mysqli_fetch_assoc($rs_di);
		
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
		if(!empty($godown_id)){
			$where_godown=" and godown_id=".$godown_id;	
		}

		$rwhser1 .=" and ref_name !='store_release'";

		if($re['product_conv_unit'] == $unit_id){
			$query1="select IFNULL(sum(convert_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and convert_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1;

			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($result1);
			
			$query2="select IFNULL(sum(base_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and base_unit=".$unit_id." and product_id=".$product_id.$rwhser1;
			$result2=$dbcon->query($query2);
			$row2=brp_mysqli_fetch_assoc($result2);
			if(empty($totaladd)){
				$query3="select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status=0 and stock_flage=2 and convert_unit=".$unit_id." ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1;
				$result3=$dbcon->query($query3);
				$row3=brp_mysqli_fetch_assoc($result3);
				

				$query4="select IFNULL(sum(base_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status=0 and base_unit!=convert_unit ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and stock_flage=2 and base_unit=".$unit_id." and product_id=".$product_id.$rwhser1;

				$result4=$dbcon->query($query4);
				$row4=brp_mysqli_fetch_assoc($result4);
			
			}
			if(empty($totaladd)){
				$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
			}else{
				$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
			}
		}else{

			$query1="select IFNULL(sum(base_stock),0) as base_addqty from tbl_reserve_stock where stock_status in (0,1) and stock_flage=1 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1;

			$result1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($result1);

			$query2="select IFNULL(sum(convert_stock),0) as conv_addqty from tbl_reserve_stock where stock_status in (0,1) and base_unit!=convert_unit and stock_flage=1 ".$p_id_where." ".$rwhser." and company_id=".$_SESSION['company_id']." and convert_unit=".$unit_id." and product_id=".$product_id.$rwhser1;
			$result2=$dbcon->query($query2);
			$row2=brp_mysqli_fetch_assoc($result2);
			if(empty($totaladd)){
				$query3="select IFNULL(sum(base_stock),0) as base_usedqty from tbl_reserve_stock where stock_status=0 and stock_flage=2 and base_unit=".$unit_id." ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and product_id=".$product_id.$rwhser1;
				$result3=$dbcon->query($query3);
				$row3=brp_mysqli_fetch_assoc($result3);


				$query4="select IFNULL(sum(convert_stock),0) as conv_usedqty from tbl_reserve_stock where stock_status=0 and base_unit!=convert_unit ".$p_id_where." ".$rwhser22." and company_id=".$_SESSION['company_id']." and stock_flage=2 and convert_unit=".$unit_id." and product_id=".$product_id.$rwhser1;

				$result4=$dbcon->query($query4);
				$row4=brp_mysqli_fetch_assoc($result4);
			}
			if(empty($totaladd)){
				$res_qty=($row1['base_addqty']+$row2['conv_addqty'])-($row3['base_usedqty']+$row4['conv_usedqty']);
			}else{
				$res_qty=($row1['base_addqty']+$row2['conv_addqty']);
			}
		}
		
		
		return $res_qty;
		
	}
?>