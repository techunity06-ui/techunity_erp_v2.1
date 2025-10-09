<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
//{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
//	{
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
$company_config = getCompanyConfiguration($dbcon);		
if(strtolower($POST['mode']) == "fetch") {

	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];

	if($company_config['po_work_order_wise'] == 1){
		$pera=",GROUP_CONCAT(po_req_no) AS workorder_no,GROUP_CONCAT(res.job_card_no) AS job_card_no";
		$left="left join tbl_request_product as res on res.rp_id=strn.rp_id  left join tbl_set_main_process as setm on setm.sp_id=res.sp_id ";
	}else{
		$pera=",GROUP_CONCAT(po_req_no) AS workorder_no,GROUP_CONCAT(res.job_card_no) AS job_card_no";
		$left="left join tbl_request_product as res on res.rp_id=strn.rp_id  left join tbl_set_main_process as setm on setm.sp_id=res.sp_id ";
	}

	$where='';
	$where.=" job.job_work_type = 2 and trn.job_work_trn_status = 1 and strn.job_work_sub_trn_status = 0 and job.grn_complete_status = 0 and job.job_work_status = 0 and job.request_status = 0 and job.company_id=".$_SESSION['company_id'];

	$appData = array();
	$i=1;
	$aColumns = array('job.job_work_id','job.job_work_no','job.job_work_date','job.vehicle_no','branch.branch_name','job.g_total','l_name'.$pera);
	$sIndexColumn = "job.job_work_id";
	$isWhere = array($where);
	// $sTable = " tbl_job_work as job";
	$sTable = "tbl_job_work_sub_trn as strn";
	
	$isJOIN = array('left join tbl_job_work_trn as trn on strn.job_work_trn_id = trn.job_work_trn_id',
	'left join tbl_job_work as job on trn.job_work_id = job.job_work_id','left join branch_mst as branch on branch.branch_id = job.branch_id','left join tbl_ledger as l on l.l_id = job.vender_id '.$left);
	$hOrder = "job.job_work_id";
	$hGroupby = array("job.job_work_id");
	include($include.'pagging.php');
	$appData = array();
	$id=1;
			//echo "<pre>"; print_r($sqlReturn);
	foreach($sqlReturn as $row) {
		$row_data = array();

		if($company_config['po_work_order_wise'] == 1){
			$row_data[] = $row['workorder_no'];
		}
		$row_data[] = $row['job_work_no'];
		$row_data[] = date('d M, Y',strtotime($row['job_work_date']));
		$row_data[] = $row['job_card_no'];
		$row_data[] = $row['l_name'];
		$row_data[] = $row['vehicle_no'];
		if($company_config['branch_wise_manage']=='1'){
			$row_data[] = $row['branch_name'];
		}
		$row_data[] = $row['g_total'];
		$app_btn='';	
		

		$app_btn='<button class="btn btn-xs btn-success" data-original-title="Approve Stock" data-toggle="tooltip" data-placement="top" onclick="show_request_material_data('.$row['job_work_id'].')"><i class="fa fa-plus"></i> Request</button>';

		$row_data[] = $app_btn;

		$appData[] = $row_data;
		$id++;
			
			}
	
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode'])== "request_jobwork_material")
{

	$job_work_id = $POST['job_work_id'];

	$query1 = "select * from tbl_job_work_trn where job_work_id = " . $job_work_id;

	$result1=$dbcon->query($query1);
	// $start_qty=0;
	$s=1;

	while($row = brp_mysqli_fetch_array($result1)){

		$query2 = "select * from tbl_job_work_sub_trn where job_work_sub_trn_status = 0 and request_status = 0 and job_work_trn_id = " . $row['job_work_trn_id'];
		$result2=$dbcon->query($query2);

		while($res = brp_mysqli_fetch_array($result2)){

			$branch_id=$res['branch_id'];
			$start_qty=$res['product_base_qty'];
			$process_id = $row['process_id'];
			$product_id = $row['product_id'];
			
			$info['rp_id']		= $res['rp_id'];
			$info['product_id']		= $product_id;
			$info['process_id']	= $process_id;
			$info['job_work_sub_trn_id'] = $res['job_work_sub_trn_id'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $branch_id;
			$info['base_unit']  = $res['product_base_unit'];
			$info['conv_unit']  = $res['product_con_unit'];
			
			$pid=$res['p_id'];
			
			$conv_qty=convert_stock_new($dbcon,$start_qty,$product_id,"conv_unit");
			$info['p_id']		= $pid;
			$info['base_qty']	= $start_qty;
			$info['conv_qty']	= $conv_qty;

			$req_id = add_record('tbl_store_request',$info, $dbcon,$branch_id);

			$job_sub_trn['request_status'] = 1;
			$updateid=update_record('tbl_job_work_sub_trn',$job_sub_trn,"job_work_sub_trn_id=".$res['job_work_sub_trn_id'] , $dbcon);
				
				
		}
		$job_trn['request_status'] = 1;
		$updateid=update_record('tbl_job_work_trn',$job_trn,"job_work_trn_id=".$row['job_work_trn_id'] , $dbcon);
	}
	
	if($req_id > 0){
		$jobwork['request_status'] = 1;
		$updateid=update_record('tbl_job_work',$jobwork,"job_work_id=".$job_work_id , $dbcon);
		echo "1";
	}else{
		echo "0";	
	}

}
else if(brp_strtolower($POST['mode']) == "store_request_using_model") {
		$job_work_id = $POST['job_work_id'];

	$query1 = "select job.*,branch.branch_name,l.l_name from tbl_job_work as job 
				left join branch_mst as branch on branch.branch_id = job.branch_id
				left join tbl_ledger as l on l.l_id = job.vender_id
				where job.job_work_id = " . $job_work_id;
	$result1=$dbcon->query($query1);
	$rel = brp_mysqli_fetch_array($result1);
	$html = "";

	$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Jobwork No </label>
						<div class="col-md-6 col-xs-11" style="color: #0e8400;font-weight: 600;" >
						<input type="text" class="form-control" value="'.$rel["job_work_no"].'" readonly />
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Jobwork Date </label>
						<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
						<input type="text" class="form-control" value="'.date('d M, Y',strtotime($rel['job_work_date'])).'" readonly />
							
						</div>
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Vender Name </label>
							<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
							<input type="text" class="form-control" value="'.$rel['l_name'].'" readonly />
								
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Vehicle No</label>
							<div class="col-md-6 col-xs-11">
							<input type="text" class="form-control" value="'.$rel['vehicle_no'].'" readonly />
								
							</div>
						</div>
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">';

					if($company_config['branch_wise_manage']=='1'){
					$html .='<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Branch</label>
							<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
							<input type="text" class="form-control" value="'.$rel['branch_name'].'" readonly />
							</div>
						</div>	
					</div>';
					}
					$html .='<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Total Amount</label>
							<div class="col-md-6 col-xs-11" style="font-size: 18px;font-weight: 600;color: #d02424;">
							<input type="text" class="form-control" value="'.$rel['g_total'].'" readonly />
							</div>
						</div>
					</div>
					</div>
					';

		 $query="select job.*,product.product_name,product.product_icode,process.process_name,u.unit_name,sub_trn.p_id, ap.previous_process_id from tbl_job_work_trn as job 
		left join tbl_job_work_sub_trn as sub_trn on sub_trn.job_work_trn_id=job.job_work_trn_id 
		left join product_mst as product on product.product_id=job.product_id 
		left join process_mst as process on process.process_id=job.process_id 
		left join tbl_allocate_process as ap on ap.p_id=sub_trn.p_id 
		left join unit_mst as u on u.unitid=job.product_base_unit
	   where job.job_work_trn_status = 1 and job.release_status = 0 and job.grn_complete_status = 0 and job.job_work_id = " . $POST['job_work_id'] . " group by job.job_work_trn_id";
		$result=$dbcon->query($query);

		$html .= '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field" style="color: red;font-size: 16px;">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="20%">Product Name</th>
		<th class="text-center" width="15%">Process Name</th>
		<th class="text-center" width="6%">Quantity</th>
		<th class="text-center" width="5%">Unit</th>
		<th class="text-center" width="6%">Rate
		</th>
		<th class="text-center" width="8%">Total Amount
		</th>
		<th class="text-center" width="30%">Materials
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
				$html .= '<tr id="fieldtr'.$i.'" >

				
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
				'.$rate.'
				</td>';
				$amount  = $rel['product_base_qty']*$rate;
				
				$html .= '<td style="vertical-align:top;font-weight: bold;font-size: 14px;">
				'.$amount.'
				</td>
				<td style="vertical-align:top;">';
				 $qry = "select * from tbl_job_work_sub_trn where release_status = 0 and grn_complete_status = 0 and job_work_sub_trn_status = 0 and job_work_trn_id = " . $job_work_trn_id;
   				$result1=$dbcon->query($qry);
   		if($rel['previous_process_id'] == 0){
   		while ($trn_res = brp_mysqli_fetch_assoc($result1)) {
   			$query="select ap.p_id,ap.p_qty,ap.start_qty,ap.p_ref_id,product.product_name,process.process_name,u.unit_name, ap.previous_process_id from tbl_allocate_process as ap
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

							$html .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered">
									<tr id="field">
									<th class="text-center" width="60%">Product Name</th>
									<th class="text-center" width="20%">Quantity</th>
									<th class="text-center" width="20%">Unit</th>
									</tr>';
							$allocate_process_start_qty=$row['start_qty']+$used_qty;

					 $query2 = "select trp.*,p.product_name,bunit.unit_name from tbl_request_product as trp left join product_mst as p on p.product_id=trp.rp_pid left join tbl_allocate_process as ap on trp.rp_id=ap.p_ref_id left join unit_mst as bunit on bunit.unitid=trp.process_unit where status = 0 and trp.perent_id =".$row['p_ref_id']." group by rp_id" ;
						$result2=$dbcon->query($query2);
						$x = 1;
						while($row2=brp_mysqli_fetch_array($result2)){

						$o_qty=convert_stock($dbcon,$row2["req_qty_one"],$row2['rp_pid'],"base_unit");
						$row2["req_qty_one"]=round($row2["req_qty_one"],6);
						
						$o_qty=round($o_qty,6);
						$reqused_qty = 0;
						//$total_req_qty=$req_qty*$o_qty;
						$total_req_qty=$used_qty*$row2["req_qty_one"];
						$total_req_qty=round($total_req_qty,4);
						//$used_qty=$req_qty*$o_qty;
						$reqused_qty=$used_qty*$row2["req_qty_one"];
						$reqused_qty=round($reqused_qty,4);
							
							$html .= '<tr id="fieldtr'.$i.'" >
							
							<td style="vertical-align:top;">
							'.$row2['product_name'].'
							</td>

							
							<td style="vertical-align:top;">
							'.$reqused_qty.'
							</td>

							<td style="vertical-align:top;">
							'.$row2['unit_name'].'
							</td>
							
							</tr>';
							
							$start_qty=$start_qty-$reqused_qty;
							$x++;
						}
						$html .= "</table>";
						
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
   	

							$html .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered">
									<tr id="field">
									<th class="text-center" width="60%">Product Name</th>
									<th class="text-center" width="20%">Quantity</th>
									<th class="text-center" width="20%">Unit</th>
									</tr>';

									$html .= '<tr id="fieldtr'.$i.'" >
							
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
							$html .= "</table>";
   }
				$html .= '</td>
				</tr>';

				$i++;
			}
		}

		

		$html .= '</table></div>';
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Request" onclick="request_material('.$job_work_id.');" />
					</center>
				</div>';
			
			echo $html;
			
		}


?>