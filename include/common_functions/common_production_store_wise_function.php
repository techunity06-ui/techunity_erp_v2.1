<?php 

function batch_store_request_pending_count_store_wise($dbcon,$process_id,$process_type,$type){

	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}

	 $company_config = getCompanyConfiguration($dbcon);	
	 $where = "";
	 if($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0){
	 	$where = " and ap.batch_no ='' and ap.batch_process_start_time = 1";
	 }
	$s_ql = "select sum(p_qty) as total_qty from tbl_allocate_process as ap
	where ap.process_id=".$process_id." ".$check_branch." and ap.company_id=".$_SESSION['company_id'].$where." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		// var_dump($rel['total_qty']);
		$total_working=$total_working+$rel['total_qty'];

	}
	return $total_working;
}

function store_request_pending_count_store_wise($dbcon,$process_id,$process_type,$type){

	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}

	 $company_config = getCompanyConfiguration($dbcon);	
	 $where = "";
	 if($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0){
	 	// $where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
	 	$where = " and (ap.batch_process_start_time = 0 or ap.batch_no != '')";
	 }
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id from tbl_allocate_process as ap
	where ap.process_id=".$process_id." ".@$check_branch." and ap.company_id=".$_SESSION['company_id'].$where." and ap.p_status IN(0,1) and extra_stock = 0 and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		if($type=="1"){
			$working_qty=store_release_material_count_store_wise($dbcon,$rel['allocate_id'],$is_store_approval=null);
		}else{
			$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
		}
		$total_working=$total_working+$working_qty;

	}
	return $total_working;
}


function store_release_material_count_store_wise($dbcon,$pid,$is_store_approval){
	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}

	 $company_config = getCompanyConfiguration($dbcon);	
	
	 $s_ql = "select sum(p_qty) as total_qty from tbl_allocate_process as ap where ap.p_status IN(0,1) and p_id in (".$pid.")" ;

	//echo "</br></br>";
	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		$rel_qty = 0;
		// $qry = "select IFNULL(sum(release_qty),0) as total_release_qty from tbl_start_stop_production where p_id in (".$rel['allocate_id'].")";
		$qry = "select IFNULL(sum(base_qty),0) as total_qty,IFNULL(sum(release_qty),0) as total_release_qty from tbl_store_request where company_id=".$_SESSION['company_id']." and store_request_status != 2 and  p_id IN (".$pid.")";
		//	echo "</br></br>";
		$res = $dbcon->query($qry);
		$row = brp_mysqli_fetch_assoc($res);

		if(!empty($row['total_qty']) && $row['total_qty'] !=0 ){
			$rel_qty = $row['total_qty'];
		}

		$total_working = $rel['total_qty'] - $rel_qty;

		//var_dump($total_working);
	}
	return $total_working;
}


function process_wise_store_production_count($dbcon,$process_id,$process_type,$type,$is_store_approval=0){

	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}

	 $company_config = getCompanyConfiguration($dbcon);	
	 $where = "";
	 if($company_config['batch_wise_stock'] == 1 && $company_config['batch_process'] == 0){
	 	$where = " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != '')) ";
	 }
	$s_ql = "select GROUP_CONCAT(p_id) as allocate_id from tbl_allocate_process as ap
	where ap.process_id=".$process_id." ".@$check_branch." and ap.company_id=".$_SESSION['company_id'].$where." and ap.p_status IN(0,1) and pr_process_type='".$process_type."' group by ap.p_product_id,ap.branch_id,ap.product_version" ;
	$q=$dbcon->query($s_ql);
	$total_working=0;
	while($rel=brp_mysqli_fetch_array($q))
	{
		if($type=="1"){
			$working_qty=production_store_wise_start_count_using_p_id($dbcon,$rel['allocate_id'],$is_store_approval);

		}else{
			$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
		}
		$total_working=$total_working+$working_qty;

	}
	return $total_working;
}
function production_store_wise_start_count_using_p_id($dbcon,$pid,$is_store_approval){
$company_config = getCompanyConfiguration($dbcon);	
	 $query="select p_status,p_id,p_product_id as product_id,p_qty as actual_qty,previous_process_id,process_unit,extra_stock,extra_stock_material_reserve from tbl_allocate_process 
	where p_id IN (".$pid.")";

	$result=$dbcon->query($query);
	$cnt=brp_mysqli_num_rows($result);
	$total_working_qty=0;
	if($cnt>0){
		while($row=brp_mysqli_fetch_assoc($result)){
			$working_qty=0;
			$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
			if($company_config['extra_stock'] == '1' && $row['extra_stock'] == '1'  && $row['extra_stock_material_reserve'] == '1'){
				// if($from == ""){
					$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
					$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
					$start_qty=$total_start_qty-$total_end_qty;
					$working_qty = $row['actual_qty'];
					$working_qty = $working_qty - $start_qty;
					$total_working_qty = $total_working_qty + $working_qty;
					
				// }
			}else{
			if($row['p_status']=="1"){
					//check working qty if process running. (its use process stop time)
				$total_start_qty=total_process_transaction_qty($dbcon,0,$row['p_id']);
				$total_end_qty=total_process_transaction_qty($dbcon,1,$row['p_id']);
				$start_qty=$total_start_qty;
			
				if($row['previous_process_id']=="0"){
					$matirial_available_qty=check_store_release_material_availability($dbcon,$row['p_id'],$is_store_approval);

					   

					/*if($matirial_available_qty>$start_qty){
						$working_qty=$matirial_available_qty-$start_qty;
					}else{*/
						$working_qty = $matirial_available_qty;
					// }

				}else{
						//$process_start_pending_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
					// $process_start_pending_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id,$is_store_approval);

					$process_start_pending_qty=check_store_release_material_availability($dbcon,$row['p_id'],$is_store_approval);


					if($process_start_pending_qty>$start_qty){
						$working_qty=$process_start_pending_qty-$start_qty;
					}else{
						$working_qty = $process_start_pending_qty;
					}

				}

			}else if($row['previous_process_id']=="0"){
					//check material availability when this is first process 
				$working_qty=check_store_release_material_availability($dbcon,$row['p_id'],$is_store_approval);
			}else{
					//$working_qty=check_process_stock_using_p_id($dbcon,$row['p_id']);
				// $working_qty=production_process_reseve_stock($dbcon,$row['process_unit'],$branch_id,$row['p_id'],$row['product_id'],$process_id,$process_reserve_id,$process_stock_id,$is_store_approval);

				$working_qty=check_store_release_material_availability($dbcon,$row['p_id'],$is_store_approval);



			}
		
// var_dump($working_qty);
			/*if($is_store_approval){
				$total_working_qty=$total_working_qty+$working_qty - $total_end_qty;
			}else{
				$total_working_qty=$total_working_qty+$working_qty;	
			}*/

					
			$total_working_qty=$total_working_qty+$working_qty;
			}

			
		}
		
	} 

	$value = $total_working_qty;
	$rounded_value = round($value, 4); 

	return $rounded_value;
}

function check_store_release_material_availability($dbcon,$p_id,$is_store_approval){
	 $query = "select IFNULL(sum(pending_qty),0) as pending_qty from tbl_start_stop_production where complete_status in (0,1) and p_id = " .$p_id;
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($result);

	return $row['pending_qty'];
}

 function count_production_stock_return($dbcon)
    {
       $query = "SELECT count(rt.return_id) as cnt_return FROM tbl_godown_stock_return as rt where ( 1 AND status = 0 and store_accept = 0 and rt.company_id = 1)";

        $rs_cust = $dbcon->query($query);
        $rel = brp_mysqli_fetch_array($rs_cust);
        if(brp_mysqli_num_rows($rs_cust) > 0){
        	 $total = $rel['cnt_return'];
        }else{
        	 $total = 0;
        }
       
            return $total;

    }

    

function batch_start_time_material_deduct_reserve_stock($dbcon,$product_qty,$unit_id,$product_conv_qty,$conv_unit,$p_id,$ref_id,$ref_name,$stock_date,$reserve_id){
	$query1 = "select * from tbl_reserve_stock as res_stock
					where stock_status != 2 and res_stock.used_status=0 and res_stock.stock_flage=1 and res_stock.reserve_id=".$reserve_id;
	$result1=$dbcon->query($query1);
	$row1=brp_mysqli_fetch_array($result1);

		$info_rese['reserve_date']		= date("Y-m-d");
		$info_rese['product_id']		= $row1['product_id'];
		$info_rese['godown_id']			= $row1['godown_id'];
		$info_rese['base_unit']			= $unit_id;
		$info_rese['base_stock']		= $product_qty;
		$info_rese['convert_unit']		= $conv_unit;
		$info_rese['convert_stock']		= $product_conv_qty;
		$info_rese['stock_flage']		= "2";
		$info_rese['request_id']		= $row1['request_id'];

		$info_rese['ref_name']			= $ref_name;
		$info_rese['ref_id']			= $ref_id;	
		
		$info_rese['perent_id']			= $row1['reserve_id'];

		$info_rese['p_id']				= $p_id;
		$info_rese['stock_id']			= $row1['stock_id'];							
		$info_rese['grn_trn_sub_id']	= $row1['grn_trn_sub_id'];							
		$info_rese['cdate']					= date("Y-m-d H:i:s");
		$info_rese['user_id']				= $_SESSION['user_id'];
		$info_rese['company_id']			= $_SESSION['company_id'];
		$info_rese['customer_id']			= $row1['customer_id'];
		$info_rese['perent_id']			= $row1['reserve_id'];
		
		$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row1['branch_id']);

		$used_res_info['used_base_stock'] =  $row1['used_base_stock'] + $product_qty;
		$used_res_info['used_convert_stock'] = $row1['used_convert_stock'] +$product_conv_qty;

		update_record('tbl_reserve_stock',$used_res_info,"reserve_id=".$row1['reserve_id'], $dbcon);
	/*	
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
		
		$updatetrnid=update_record('tbl_reserve_stock',$upd_res_info,"reserve_id=".$row1['reserve_id'], $dbcon);*/
		
		add_stock($dbcon,$row1['product_id'],$unit_id,$info_rese['reserve_date'],$ref_name,$ref_id,$row1['godown_id'],$product_qty,2,$row1['branch_id'],$row1['stock_id'],$reserve_id_id,$row1['customer_id']);	
}

function start_time_production_deduct_process_reserve_stock($dbcon,$product_qty,$unit_id,$product_conv_qty,$conv_unit,$p_id,$ref_id,$ref_name,$stock_date,$process_reserve_id){
		$query = "select * from tbl_process_reserve_stock where stock_status!=2 and process_reserve_id=".$process_reserve_id;
		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_array($result);



		$info_stockadd = array();
		$info_stockadd['process_reserve_date']		= date("Y-m-d",strtotime($stock_date));
		$info_stockadd['product_id']				= $row['product_id'];
		$info_stockadd['process_id']				= $row['process_id'];
		$info_stockadd['base_stock']				= $product_qty;
		$info_stockadd['base_unit']					= $unit_id;
		$info_stockadd['conv_stock']				= $product_conv_qty;
		$info_stockadd['conv_unit']					= $conv_unit;
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
		$info_stockadd['branch_id']				= $row['branch_id'];

		$qry1 = "select * from tbl_process_stock_trn where stock_status = 0 and process_stock_id=".$row['process_stock_id'];
		$result2=$dbcon->query($qry1);
		$row2=brp_mysqli_fetch_array($result2);
		
		$process_reserve_id=add_record('tbl_process_reserve_stock',$info_stockadd, $dbcon);
		
		$upd_pro_stk['used_base_stock'] = $row['used_base_stock'] + $base_stock;
		$upd_pro_stk['used_conv_stock'] = $row['used_conv_stock'] + $con_stock;


		$updatetrnid=update_record('tbl_process_reserve_stock',$upd_pro_stk,"process_reserve_id=".$row['process_reserve_id'], $dbcon);
				
		production_deduct_process_stock($dbcon,$info_stockadd['base_stock'],$info_stockadd['base_unit'],$info_stockadd['p_id'],$info_stockadd['process_reserve_date'],$info_stockadd['godown_id'],$info_stockadd['ref_name'],$info_stockadd['ref_id'],$info_stockadd['process_stock_id'],$process_reserve_id);
				
		
	}

function process_wise_store_production_start_count_new($dbcon,$process_id,$process_type,$type,$is_store_approval){

	if($_SESSION['user_type']!='2'){
		$check_branch = check_branch('ap',$_SESSION['branch_id']);
	}

	$company_config = getCompanyConfiguration($dbcon);	
	 
	$total_working=0;

	$query = " SELECT IFNULL(SUM(ss.pending_qty),0) as pending_start_qty FROM 
					tbl_start_stop_production as ss 
					LEFT JOIN tbl_allocate_process as ap on ap.p_id = ss.p_id 
					WHERE ap.extra_stock = 0 AND ap.p_status IN (0,1) AND ss.complete_status IN (0,1) AND ap.pr_process_type = '1' AND ap.process_id = " . $process_id;

	
	$q = $dbcon->query($query);
	$rel=brp_mysqli_fetch_array($q);
	
	$total_working=$rel['pending_start_qty'];

	$query1 = " SELECT IFNULL(SUM(ap.p_qty),0) as pending_start_qty, (SELECT IFNULL(SUM(pt_qty),0) FROM tbl_allocate_process_trn WHERE p_status = 0 and pt_alloc_id = ap.p_id) as started_qty FROM 
					tbl_allocate_process as ap 
					WHERE ap.extra_stock = 1 AND ap.p_status IN (0,1) AND ap.pr_process_type = '1' AND ap.process_id = " . $process_id;
	$q1 = $dbcon->query($query1);
	$rel1=brp_mysqli_fetch_array($q1);


	$total_working = $total_working + (floatval($rel1['pending_start_qty']) - floatval($rel1['started_qty']));
	
	return floatval($total_working);
}
?>