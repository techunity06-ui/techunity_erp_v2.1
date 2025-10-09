<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}



if(strtolower($POST['mode']) == "save_chalan") {
	$branch_id=$POST['branch_id'];
	
	// print_r($_POST);die;
	 $job_work_id = $POST['job_work_id'];

	$q = "select * from tbl_job_work_trn where job_work_trn_status = 1 and release_status = 1 and grn_complete_status = 0 and job_work_id = " . $job_work_id;
    $result=$dbcon->query($q);
    $cnt1 = brp_mysqli_num_rows($result);


	while ($j_res = brp_mysqli_fetch_assoc($result)) {
		$start_qty=$j_res['product_base_qty'];
		$job_work_trn_id = $j_res['job_work_trn_id'];


		$qry = "select * from tbl_job_work_sub_trn where grn_complete_status = 0 and job_work_sub_trn_status = 0 and job_work_trn_id = " . $job_work_trn_id;
   		$result1=$dbcon->query($qry);
   		
   		while ($trn_res = brp_mysqli_fetch_assoc($result1)) {
   			$query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_re_process where p_id in (".$trn_res['p_id'].")";
			$result2=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result2);

			if($cnt){
				$allocate_process_qty=0;
				while($row=brp_mysqli_fetch_assoc($result2)){
					$allocate_process_qty=($row['p_qty']-$row['start_qty']);
					$working_qty=$row['p_qty'];
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
							
							$updatetrnid=update_record('tbl_allocate_re_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
							
							//location common_functions 
							
							add_reprocess_start_stop_entry($dbcon,$used_qty,$row['p_id'],1);
							
							$info_job_up['product_version']	= $row['product_version'];
							$info_job_up['job_work_trn_status']	= 0;
							// var_dump($job_work_trn_id);
							
							$updatetrn1id=update_record('tbl_job_work_trn',$info_job_up,"job_work_trn_id=".$job_work_trn_id, $dbcon);
							// echo "<pre>"; print_r($info_job_up);

							// var_dump($updatetrn1id);
							$start_qty=$start_qty-$used_qty;
						}
						
					}
				}
			}
   		}
		
		
	}
	if($cnt1 > 0){
		$arr['msg'] = '1';
		
		$info['chalan_no'] = $_POST['chalan_no'];
		$info['chalan_date'] = $_POST['chalan_date'];
		$info['chalan_status'] = 1;
		
		$updatetrn1id=update_record('tbl_job_work',$info,"job_work_id=".$job_work_id , $dbcon);

		if($updatetrn1id){
			update_series_no_using_type_id($dbcon,OUTSIDE_JOB_WORK_CHALAN,$_SESSION['company_id'],$branch_id);
		}
		
	}else{
		$arr['msg'] = '0';
	}

	echo brp_json_encode($arr);
}
else if(brp_strtolower($POST['mode']) == "load_jobwork_data") {
		
	 $query="select job.*,product.product_name,product.product_icode,process.process_name,u.unit_name,munit.unit_name as munit_name,trn.p_id,ap.previous_process_id from tbl_job_work_trn as job 
	 left join tbl_job_work_sub_trn as trn on trn.job_work_trn_id=job.job_work_trn_id 
	 left join product_mst as product on product.product_id=job.product_id 
	 left join process_mst as process on process.process_id=job.process_id 
	 left join unit_mst as u on u.unitid=job.product_base_unit 
	 left join unit_mst as munit on munit.unitid=job.material_unit 
	 left join tbl_allocate_process as ap on ap.p_id=trn.p_id 
	 where job.job_work_trn_status = 1 and job.release_status = 1 and job.grn_complete_status = 0 and job.branch_id = ".$POST['branch_id']." and job.job_work_id = ".$POST['job_work_id'] . " group by job_work_trn_id";
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field" style="color: red;font-size: 16px;">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="25%">Product Name</th>
		<th class="text-center" width="15%">Process Name</th>
		<th class="text-center" width="6%">Quantity</th>
		<th class="text-center" width="5%">Unit</th>
		<th class="text-center" width="5%">Material Unit</th>
		<th class="text-center" width="5%">Material Qty</th>
		<th class="text-center" width="6%">Rate
		</th>
		<th class="text-center" width="8%">Total Amount
		</th>
		<th class="text-center" width="30%">Materials
		</th>
		<th class="text-center" width="30%">Note
		</th>
		<th class="text-center" width="5%">Action
		</th>
		</tr>
		<tbody id="fil_product_tbl">';
		
		if(brp_mysqli_num_rows($result)>0)
		{

			$i=1;
			
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$job_work_trn_id = $rel['job_work_trn_id'];
				$amount = 0;
				$rate = 0;
				if($rel['pr_rate'] !=""){
					$rate = $rel['pr_rate'];
				}
				$start_qty=$rel['product_base_qty'];
				echo '<tr id="fieldtr'.$i.'" >

				
				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$i.'
				</td>
				
				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['product_name']." -- (".$rel['product_icode'].")".' </br>' . $rel['description'] . '</td>

				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['process_name'].'
				</td>

				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['product_base_qty'].'
				</td>

				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['unit_name'].'
				</td>

				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['munit_name'].'
				</td>

				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['material_qty'].'
				</td>

				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rate.'
				</td>';
				
				if(!empty($rel['material_qty'])){
					$amount  = $rel['material_qty']*$rate;
				}else{
					$amount  = $rel['product_base_qty']*$rate;
				}
				
				
				echo '<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$amount.'
				</td>
				<td style="vertical-align:top;">';

		if($rel['previous_process_id'] == '0'){			
				 $qry = "select * from tbl_job_work_sub_trn where release_status = 1 and grn_complete_status = 0 and job_work_sub_trn_status = 0 and job_work_trn_id = " . $job_work_trn_id;
   				$result1=$dbcon->query($qry);
   		
   		while ($trn_res = brp_mysqli_fetch_assoc($result1)) {
   			 $query="select ap.p_id,ap.p_qty,ap.start_qty,ap.p_ref_id,product.product_name,process.process_name,u.unit_name from tbl_allocate_process as ap
   			left join product_mst as product on product.product_id=ap.p_product_id 
			left join process_mst as process on process.process_id=ap.process_id 
			left join unit_mst as u on u.unitid=product.product_base_unit
   			where p_id in (".$trn_res['p_id'].")";
			$result2=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result2);

			if($cnt){
				$allocate_process_qty=0;
				while($row=brp_mysqli_fetch_assoc($result2)){
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

							echo '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered">
									<tr id="field">
									<th class="text-center" width="60%">Product Name</th>
									<th class="text-center" width="20%">Quantity</th>
									<th class="text-center" width="20%">Unit</th>
									</tr>';
							$allocate_process_start_qty=$row['start_qty']+$used_qty;

					 $query2 = "select trp.*,p.product_name,bunit.unit_name from tbl_request_product as trp left join product_mst as p on p.product_id=trp.rp_pid left join tbl_allocate_process as ap on trp.rp_id=ap.p_ref_id left join unit_mst as bunit on bunit.unitid=trp.process_unit where trp.perent_id =".$row['p_ref_id']." group by rp_id" ;
						$result2=$dbcon->query($query2);
						$x = 1;
						while($row2=brp_mysqli_fetch_array($result2)){

						$o_qty=convert_stock($dbcon,$row2["req_qty_one"],$row2['rp_pid'],"base_unit");
						$row2["req_qty_one"]=round($row2["req_qty_one"],6);
						
						$o_qty=round($o_qty,6);
						
						//$total_req_qty=$req_qty*$o_qty;
						$total_req_qty=$allocate_process_qty*$row2["req_qty_one"];
						$total_req_qty=round($total_req_qty,4);
						//$used_qty=$req_qty*$o_qty;
						$used_qty=$allocate_process_qty*$row2["req_qty_one"];
						$used_qty=round($used_qty,4);
							
							echo '<tr id="fieldtr'.$i.'" >
							
							<td style="vertical-align:top;">
							'.$row2['product_name'].'
							</td>

							
							<td style="vertical-align:top;">
							'.$used_qty.'
							</td>

							<td style="vertical-align:top;">
							'.$row2['unit_name'].'
							</td>
							
							</tr>';
							
							$start_qty=$start_qty-$used_qty;
							$x++;
						}
						
			
						echo "</table>";
						
					}
				}
			}
			// $x++;
   		}
   	}

   }else{
   	$process=p_id_wise_find_previous_and_next_process($dbcon,$rel['p_id']);
				$process_pr=json_decode($process);
				$previous_process_pid=$process_pr->previous_process_pid;



		$q = "select pr.process_name from tbl_allocate_process as ap left join process_mst as pr on pr.process_id = ap.process_id where p_id = ". $previous_process_pid;

		$res_2=$dbcon->query($q);
						
		$row_2=brp_mysqli_fetch_array($res_2);
				
   		echo '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered">
									<tr id="field">
									<th class="text-center" width="60%">Product Name</th>
									<th class="text-center" width="20%">Quantity</th>
									<th class="text-center" width="20%">Unit</th>
									</tr>';
									echo '<tr id="fieldtr'.$i.'" >
							
							<td style="vertical-align:top;">
							'.$rel['product_name'].' - ['. $row_2['process_name'] .']'.'
							</td>

							
							<td style="vertical-align:top;">
							'.$rel['product_base_qty'].'
							</td>

							<td style="vertical-align:top;">
							'.$rel['unit_name'].'
							</td>
							
							</tr>';
							echo "</table>";
   }
				echo '</td>
				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$rel['material_qty'].'
				</td>
				<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				 <input type="button" id="addrow" class="btn btn-xs btn-primary" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onclick="challan_edit_pop_up('.$job_work_trn_id.');" value="View"/>
				</td>
				</tr>';

				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}

		echo '</div>	';

	}else if(brp_strtolower($POST['mode']) == "load_jobwork_edit_data") {

		$query="select job.*,product.product_name,process.process_name,u.unit_name,munit.unit_name as munit_name,trn.p_id,ap.previous_process_id from tbl_job_work_trn as job 
	 left join tbl_job_work_sub_trn as trn on trn.job_work_trn_id=job.job_work_trn_id 
	 left join product_mst as product on product.product_id=job.product_id 
	 left join process_mst as process on process.process_id=job.process_id 
	 left join unit_mst as u on u.unitid=job.product_base_unit 
	 left join unit_mst as munit on munit.unitid=job.material_unit 
	 left join tbl_allocate_process as ap on ap.p_id=trn.p_id 
	 where  job.job_work_trn_id = ".$POST['job_work_trn_id'];

		$res_2=$dbcon->query($query);
						
		$row_2=brp_mysqli_fetch_array($res_2);
		

		echo '
			<div class="col-md-12"  style="font-size: 16px;border: 1px solid;margin-top: 1px;" >
				<div class="col-md-6">
					Product Name
				</div>

				<div class="col-md-6">
					'.$row_2["product_name"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Process Name
				</div>

				<div class="col-md-6">
					'.$row_2["process_name"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Quantity
				</div>

				<div class="col-md-6">
				'.$row_2["product_base_qty"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Unit
				</div>

				<div class="col-md-6">
				'.$row_2["unit_name"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Material Unit
				</div>

				<div class="col-md-6">
					'.$row_2["munit_name"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Material Qty
				</div>

				<div class="col-md-6">
				'.$row_2["material_qty"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Rate
				</div>

				<div class="col-md-6">
				'.$row_2["pr_rate"].'
				</div>
			</div>	
			<div class="col-md-12" style="font-size: 16px;border: 1px solid;margin-top: 1px;">
				<div class="col-md-6">
					Note
				</div>

				<div class="col-md-6">
				'.$row_2["unit_name"].'
				</div>
			</div>
			<div class="col-md-12">
				<center>
					<button type="button" class="btn btn-round btn-warning " data-toggle="tooltip" data-placement="top" title="" onclick="save_challan_data();" id="fieldremove">Edit</button>
				</center>
			</div>	
		';
	}
?>