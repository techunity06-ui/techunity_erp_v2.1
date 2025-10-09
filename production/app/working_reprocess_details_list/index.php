<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

		//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
 $company_config = getCompanyConfiguration($dbcon);		
 $is_store_approval= $company_config['store_approval'];

		if(strtolower($POST['mode']) == "fetch_working") {
			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			
			$str='<tbody>';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Product Category</th>
				<th>Batch No / Serial No</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Working Qty</th>
				<th>Status</th>
				<!--<th>Start Time</th>
				<th>End Time</th>-->';
				if($_SESSION['branch_id']==0){
					$str.='<th>Branch Name</th>';
				}
				$str.='<th>Action</th>
				
			</tr>';
			if(!empty($POST['product_id'])){
				$Product_filter=" and ap.p_product_id=".$POST['product_id'];
			}

			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$check_branch = check_branch('ap', $branch_id);
			
			$whr = "";
			// if($is_store_approval){
			// 	$whr = " and  (res.approve_base_stock != '0') and res.stock_status = 0 and stock_flage = 1";
			// }

			$s_ql = "select GROUP_CONCAT(ap.p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status, batch.batch_no,p.batch_wise_stock_manage from tbl_allocate_re_process as ap left join product_mst as p on p.product_id=ap.p_product_id left join tbl_category as tc on p.product_category=tc.cat_id left join branch_mst as branch on branch.branch_id=ap.branch_id		
			left join tbl_batch_data as batch on batch.batch_id = ap.batch_id	
			where ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type'" .$whr ." group by ap.p_product_id" ;
// echo $s_ql;	
			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{

				if($POST['type']=="1"){
					$working_qty=reprocess_start_count_using_p_id($dbcon,$rel['allocate_id'],$is_store_approval);
					$pending_qty=total_reprocess_pending_qty($dbcon,$rel['allocate_id']);
				}else{
					$working_qty=reprocess_end_count_using_p_id($dbcon,$rel['allocate_id']);
					$pending_qty=$working_qty;
				}
				
				$product_name = $rel['product_name'];

				if($working_qty>0){
					if($POST['type']=="1"){
						$status="<strong style='color:red'>Not Started</strong>";
						
						//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/process/'.$rel['branch_id'].'" >Start <i class="fa fa-plus"></i></a>';
						$button = "";
						$new_button = "";
						$batchBtn = "";
						
						$start_url=urlencode($rel['allocate_id']);
						$url = $rel["allocate_id"];

						// if($company_config['production_start_type'] ==  '1'){
						// 		$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_process_start/'.$start_url.'" >Start <i class="fa fa-plus"></i></a>';
						// }else{
								$button='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="start_process_using_model('. "'". $url."'".','. "'". $product_name."'".')">Start <i class="fa fa-plus"></i></button>';
						// }
						

					}else{
						$status="<strong style='color:green'>Started</strong>";
						
						$start_url=urlencode($rel['allocate_id']);
						$url = $rel["allocate_id"];
						// if($company_config['production_start_type'] ==  '1'){
						// 	$button='<a class="btn btn-xs btn-danger" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" title="Process End" href="'.ROOT.PRODUCTION_ROOT.'production_process_end/'.$start_url.'" ><i class="fa fa-power-off"></i> End</a>';
						// }else{
						//$companyConfiguration=getCompanyConfiguration($dbcon);
						$rr=$companyConfiguration['store_relese_first_process'];
							$button='<button class="btn btn-xs btn-danger" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="end_process_using_model('. "'". $url."'".','. "'". $product_name."'".','.$rr.')">End <i class="fa fa-power-off"></i></button>';
						// }
						
					}
					
					
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';	
					$str.='<tr>
							<th>'.$cnt.'</th>
							<th>'.$rel['product_name'].'</th>
							<th>'.$cat_name.'</th>
							<th>'.$rel['batch_no'].'</th>
							<th>'.$rel['total_qty'].'</th>
							<th>'.$pending_qty.'</th>
							<th>'.$working_qty.'</th>
							<th>'.$status.'</th>';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							$str.='<th>'.$button.' ' . $batchBtn .'</th>
						</tr>';
						$cnt++;
						$datacheck=1;
				}
			}
			if($datacheck!=1){
				$str.= '<tr><td colspan="9"> <center>No Process Found!!!!!</center></td></tr>';
			}
			$str.='</tbody>';
			
			echo $str;
		}
		else if(strtolower($POST['mode']) == "start_process_using_model") {

			// $p_id= urldecode($POST['p_ids']);
			$p_id= $POST['p_ids'];
			$html="";
			
		 $query="select p.product_name,pr.process_name,ap.branch_id,p.product_base_unit as process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.previous_process_id,p.batch_wise_stock_manage from tbl_allocate_re_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=p.product_base_unit
				
			where ap.p_id in (".$p_id.")";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_reprocess_pending_qty($dbcon,$p_id);
				$working_qty=reprocess_start_count_using_p_id($dbcon,$p_id,$is_store_approval);
				
				if($rel["previous_process_id"] == 0)
				{
					$readonly = "";
				}
				else
				{
					$readonly = "readonly='readonly'";
				}
				
				$process=p_id_wise_find_previous_and_next_process($dbcon,$p_id);
				$process_pr=json_decode($process);

				$previous_process_pid=$process_pr->previous_process_pid;
			
			$html .='
				<div class="col-md-12" style="margin-bottom: 15px;">
				<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Product Name </label>
						<div class="col-md-6 col-xs-11" style="color: #0e8400;font-weight: 600;" >
							'.$rel["product_name"].'
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Process Name </label>
						<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
							'.$rel["process_name"].'
						</div>
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Start Time </label>
							<div class="col-md-6 col-xs-11">
								<input type="text" class="form-control" id="pr_st_time1" name="pr_st_time1" value="'.date('d-m-Y h:i:sa').'" readonly />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Process No*</label>
							<div class="col-md-6 col-xs-11">
								<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="'.$pno.'" placeholder="Process No" required readonly >
							</div>
						</div>
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Pending Qty</label>
							<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
								'.$pending_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Start Qty *</label>
							<div class="col-md-4 col-xs-11">
								<input type="text" name="start_qty" id="start_qty" class="form-control" value="'.$working_qty.'" /> 
							</div>
							<div class="col-md-1" style="font-size: 18px;font-weight: 600;color: #d02424;">
								'.$rel["unit_name"].'
							</div>
						</div>
					</div>
					</div>
					';

					/*if($company_config['batch_stock'] == '1' &&  $company_config['batch_process'] == '0' && $rel['batch_wise_stock_manage'] == '1'){
						
						$html .='<div class="col-md-12" style="margin-bottom: 15px;">
									<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
												<div class="col-md-6 col-xs-11">
													<input type="text" class="form-control" id="batch_id" name="batch_id" value="'.$rel['batch_no'].'" readonly>
												</div>
											</div>
										</div>
									</div>';
						
					}	*/
			
			/*$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<table class="display table table-bordered table-striped" id="">
					<tr>
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Pending Qty</th>
						<th>Working Qty</th>
						<th>Start Qty</th>';
			
			$html .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id from tbl_allocate_re_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					where ap.p_id in (".$p_id.")";

			$result1=$dbcon->query($query1);
			$start_qty=0;
			$s=1;
			while($row=brp_mysqli_fetch_array($result1)){
				$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);
				if($start_qty > 0){
					$html .='<tr id="trid'.$row['p_id'].'">
							<th>'.$row["work_order_no"].'</th>
							<th>'.$row["work_order_date"].'</th>
							<th>'.$row["job_card_no"].'</th>
							<th>'.$row["job_card_date"].'</th>
							<th></th>
							<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
							<th>'.$start_qty.' '.$row["unit_name"].'</th>
							
							<th><input type="text" class="form-control start_qty" name="start_qty1[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$start_qty.'" id="start_qty1'.$row['p_id'].'" value="" onkeyup="check_start_validation();" />
							 '.$row["unit_name"].'
							</th>';
							
							$html .= '<!--<th class="nosort">
								<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
							</th>-->											
						</tr>';
					}
				$s++;
			} */
			
			$html .='</table>
			</div>
			<div class="col-md-12" style="margin-bottom: 15px;">
						<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
						<div class="col-md-6 col-xs-11">
								<textarea id="remark" name="remark" class="form-control" rows="3"></textarea> 
						</div>
					</div>';
			
			
			$html .='<input type="hidden" name="mode" id="mode" value="add_start_process_using_model" />
			<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
			<input type="hidden" name="max_available_qty" id="max_available_qty" value="'.$working_qty.'" />
			<input type="hidden" id="pending_qty" name="pending_qty" value="'.$pending_qty.'">
			<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
			<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
			<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$select_branch_id.'" />
			<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel["p_product_id"].'" />
			<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />
			<input type="hidden" name="product_version" id="product_version" value="'.$rel["product_version"].'" />';
			
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Start The Process" onclick="process_start_using_model();" />
					</center>
				</div>';
			
			echo $html;
			
		}
		else if(strtolower($POST['mode']) == "end_process_using_model") {
			$p_id= $POST['p_ids'];
			$html ="";
		 $query="select p.product_name,pr.process_name,ap.branch_id,p.product_base_unit as process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.branch_id,p.batch_wise_stock_manage,ap.batch_id,ap.qc_id from tbl_allocate_re_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=p.product_base_unit
			where p_status=1 and ap.p_id in (".$p_id.")";
			$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
		
			$qc_paramter_info = check_product_qc_paramter($dbcon,$rel['p_product_id'],$rel['process_id']);
			if($qc_paramter_info=='1')
			{
				$qc_st="yes";
				$sty="display:none;";
			}else{
				$qc_st="no";
				$sty="";
			}
			$pno=load_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);
			$working_qty=reprocess_end_count_using_p_id($dbcon,$p_id);
			
			$html .='
				<div class="col-md-12" style="margin-bottom: 15px;" >
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Product Name </label>
						<div class="col-md-6 col-xs-11" style="color: #0e8400;font-weight: 600;" >
							'.$rel["product_name"].'
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Process Name </label>
						<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
							'.$rel["process_name"].'
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-bottom: 15px;" >
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">End Process Time</label>
							<div class="col-md-6 col-xs-11">
								<input type="text" class="form-control" id="pr_end_time1" name="pr_end_time1" value="'.date('d-m-Y h:i:sa').'" readonly />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Process No</label>
							<div class="col-md-6 col-xs-11">
								<input type="text" class="form-control" id="process_no" name="process_no" value="'.$pno.'" readonly />
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-bottom: 15px;" >
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Pending Qty</label>
							<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
								'.$working_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Stop Qty *</label>
							<div class="col-md-4 col-xs-11">
								<input type="text" name="stop_qty" id="stop_qty" class="form-control" value="'.$working_qty.'" /> 
							</div>
							<div class="col-md-1" style="font-size: 18px;font-weight: 600;color: #d02424;">
								'.$rel["unit_name"].'
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-bottom: 15px;" >
					<div class="col-md-6" style="'.$sty.'" >
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Godown *</label>
							<div class="col-md-6 col-xs-11">
								<select class="form-control" name="grn_godown"  id="grn_godown" required >
									'.get_all_godown($dbcon,'',1).'
								</select>
							</div>
						</div>
					</div>';

					/*if($company_config['batch_stock'] == '1' &&  $company_config['batch_process'] == '0' && $rel['batch_wise_stock_manage'] == '1'){

						$html .='<div class="col-md-12" style="margin-bottom: 15px;">
									<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
												<div class="col-md-6 col-xs-11">
													<input type="text" class="form-control" id="batch_id" name="batch_id" value="'.$rel['batch_no'].'" readonly>
												</div>
											</div>
										</div>
									</div>';

					}
					if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '1'){

						$batch_no = "";
						$readonly = "";
						if($company_config['batch_stock'] == '1'){
							$batch_no = get_batch_no($dbcon,$rel['p_product_id']);
							$readonly = "readonly";
						}

						$html .= '<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
							<div class="col-md-6 col-xs-11">
								<input type="text" class="form-control" id="batch_id" name="batch_id" value="'.$batch_no.'" '.$readonly.'>
							</div>
						</div>
					</div>';
					}*/

					$html .= '</div>';
				/*$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<table class="display table table-bordered table-striped" id="">
					<tr>
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Pending Qty</th>
						<th>Working Qty</th>
						<th>End Qty</th>';
					// if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0'){ 
					// 	if($company_config['batch_type']=='0') {
					// 		$html .='<th>Batch No</th>';
					// 	}else{
					// 		$html .='<th>Serial No</th>';
					// 	}
					// }
					$html .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
					$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id from tbl_allocate_re_process as ap
							left join product_mst as p on p.product_id=ap.p_product_id 
							left join tbl_request_product req on req.rp_id=ap.p_ref_id
							left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
							left join unit_mst as umst on umst.unitid=ap.process_unit
							where ap.p_id in (".$p_id.")" ;

					$result1=$dbcon->query($query1);
					
					
					$start_qty=0;
					$s=1;
					while($row=brp_mysqli_fetch_array($result1)){
						$start_qty=production_end_count_using_p_id($dbcon,$row['p_id']);
						if($start_qty > 0){
						$work_order_id = $row['work_order_id'];
							
						$batch_query = "select * from tbl_batch_data where grn_id = '$work_order_id'";
						$batch_result=$dbcon->query($batch_query);
						if(mysqli_num_rows($batch_result)>0)
						{
							$batch_data = array();
							while($batch_row = brp_mysqli_fetch_array($batch_result))
							{
								$batch_data[] = $batch_row['batch_no'];
							}
							
							$batches_data = implode(",",$batch_data);
						}
						//while($row=brp_mysqli_fetch_array($result1))



						$html .='<tr id="trid'.$row['p_id'].'">
									<th>'.$row["work_order_no"].'</th>
									<th>'.$row["work_order_date"].'</th>
									<th>'.$row["job_card_no"].'</th>
									<th>'.$row["job_card_date"].'</th>
									<th></th>
									<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
									<th>'.$start_qty.' '.$row["unit_name"].'</th>
									<th><input type="text" class="form-control start_qty" name="end_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$start_qty.'" id="end_qty'.$row['p_id'].'" value="" onkeyup="check_start_validation();" />
									 '.$row["unit_name"].'
									</th>';

								// 	if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0'){ 
								// 	$html .= '<th>'.$batches_data.'</th>';
								// }
									$html .= '<!--<th class="nosort">
										<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
									</th>-->											
								</tr>';
								}
						$s++;
					}
				
				$html .='</table>
				</div>';*/
				$html .='<div class="col-md-12" style="margin-bottom: 15px;" >
					<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
					<div class="col-md-6 col-xs-11">
							<textarea id="remark" name="remark" class="form-control" rows="3"></textarea> 
					</div>
				</div>
					<input type="hidden" name="mode" id="mode" value="end_process" />
					<input type="hidden" name="max_available_qty" id="max_available_qty" value="'.$working_qty.'" />
					<input type="hidden" id="pending_qty" name="pending_qty" value="'.$working_qty.'">
					<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
					<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
					<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$rel['branch_id'].'" />
					<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel['p_product_id'].'" />
					<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />
					<input type="hidden" name="batch_id" id="batch_id" value="'.$rel["batch_id"].'" />
					<input type="hidden" name="reprocess_qc_id" id="reprocess_qc_id" value="'.$rel["qc_id"].'" />
										
				';
				$html .='<div class="col-md-12" style="margin-bottom: 15px;" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="End The Process" onclick="process_end_using_model();" />
					</center>
				</div>';
				
			echo $html;
			
		}
		else if(strtolower($POST['mode']) == "add_start_process_using_model") {
			// echo "<pre>";
			// print_r($POST);die;
			$branch_id=$POST['branch_id'];
			$start_qty=$POST['start_qty'];

			$p_id = $POST['p_id'];
			
			$query="select p_id,p_qty,start_qty,p_ref_id from tbl_allocate_re_process where p_id in (".$POST['p_id'].")";
			$result=$dbcon->query($query);
			$cnt=brp_mysqli_num_rows($result);
			if($cnt){
				$allocate_process_qty=0;
				while($row=brp_mysqli_fetch_assoc($result)){
					$allocate_process_qty=($row['p_qty']-$row['start_qty']);
					$working_qty=reprocess_start_count_using_p_id($dbcon,$row['p_id']);
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
							// $info_allocate['task_status']	= 1;
							$updatetrnid=update_record('tbl_allocate_re_process',$info_allocate,"p_id=".$row['p_id'] , $dbcon);
							
							$start_qty=$start_qty-$used_qty;

							add_reprocess_start_stop_entry($dbcon,$used_qty,$p_id,1);
						}
						
					}
				}
			}
			echo "1";
			
		}
		else if(strtolower($POST['mode']) == "end_process") {
			// print_r($_POST);die;
			$branch_id = $POST['branch_id'];
			$stop_qty=$POST['stop_qty'];
			$product_id = $POST['product_id'];
			$grn_id = "";
			$total_stop_qty=$POST['stop_qty'];
			$product_base_unit = $POST['product_base_unit'];
			$process_id = $POST['process_id'];
			$grn_godown = $POST['grn_godown'];
			$p_id = $POST['p_id'];
			$batch_id= $POST['batch_id'];
			$reprocess_qc_id = $POST['reprocess_qc_id'];


			$qry = "select * from product_mst where product_id = " . $product_id;
			$result=$dbcon->query($qry);
			$res=mysqli_fetch_assoc($result);
			

			$qc_paramter_info = check_product_qc_paramter($dbcon,$product_id,$process_id);
		
			$companyConfiguration = getCompanyConfiguration($dbcon, $id = false);
		
			
			if($qc_paramter_info=="1"){
				$product_qc	= "0"; // QC pending 
			}
			else{
				$product_qc	=	"1"; // QC Done
			}
		

		/*Added by Sanat :: START  :: 19-11-21 */

		$base_unit = $product_base_unit;

		// $conv_qty = $stop_qty;
		$conv_unit = $res['product_conv_unit'];

		// check product batch stock setting 

		$qry1= "select * from tbl_allocate_re_process where p_id in(" . $p_id .")";
		$result1=$dbcon->query($qry1);
		while($res1=brp_mysqli_fetch_assoc($result1)){
			$batch_qry= "select * from tbl_batch_data where batch_id = " . $res1['batch_id'];
			$batch_result=$dbcon->query($batch_qry);
			$batch_res=brp_mysqli_fetch_assoc($batch_result);

		$allocate_trn_update_qty = 0;
		if($res1['start_qty']>"0" && $stop_qty > "0"){
			if($res1['start_qty']<=$stop_qty){
				//use $row1['pending_qty']
				$allocate_trn_update_qty=$res1['start_qty'];
			}else{
				//use $grn_sub_trn_qty
				$allocate_trn_update_qty=$stop_qty;
			}	
		// echo ">> ".$stop_qty . " <<<";
		$type="conv_unit";
		
		$conv_qty = ($allocate_trn_update_qty/$batch_res['base_qty']) * $batch_res['conv_qty'];

		$batch_info['grn_id']			= $batch_res['grn_id'];	
		$batch_info['grn_trn_id']		= $batch_res['grn_trn_id'];	
		$batch_info['batch_no']			= $batch_res['batch_no'];
		$batch_info['batch_qty']		= $allocate_trn_update_qty;
		$batch_info['order_no']			= $batch_res['grn_no'];
		$batch_info['product_id']		= $product_id;
		$batch_info['grn_date']			= date('Y-m-d',strtotime($batch_res['grn_date']));
		$batch_info['batch_type']		= $companyConfiguration['batch_type'];
		$batch_info['production_type']	= '1';			
		$batch_info['status']			= '0';			
		$batch_info['reprocess_qc']			= '1';			
		
		$batch_info['qc_status']		= $product_qc;
		if($qc_paramter_info==0){
			$batch_info['accept_qty']	= $base_qty;
			$batch_info['qc_qty']		= $base_qty;

		}
		$batch_info['cdate']			= date("Y-m-d H:i:s"); 
		$batch_info['user_id']			= $_SESSION['user_id'];
		$batch_info['company_id']		= $_SESSION['company_id'];	
		$batch_info['branch_id']		= $branch_id;
		$batch_info['batch_unit']		= $product_base_unit;
		$batch_info['base_qty']			= $allocate_trn_update_qty;
		$batch_info['base_unit']		= $base_unit;
		$batch_info['conv_qty']			= $conv_qty;
		$batch_info['conv_unit']		= $conv_unit;
		$batch_info['process_id']		= $process_id;
		$batch_info['p_id']				= $res1['p_id'];
		$batch_info['reprocess_qc_id']	= $reprocess_qc_id;

		$batch_gen_id = add_record('tbl_batch_data', $batch_info, $dbcon);	
		
		// $base_qty;
		$used_conv_qty = 0;

			add_reprocess_start_stop_entry($dbcon,$allocate_trn_update_qty,$res1['p_id'] ,2);
		// $allocate_trn_stop_qty=allocate_reprocess_trn_stop_entry_start_entry_wise($dbcon,$allocate_trn_update_qty,$row['p_id']);
		
			tbl_allocate_reprocess_update_pen_qty($dbcon,$res1['p_id'],$allocate_trn_update_qty);
			
		//allocate process table pen_qty update end
		
		//allocate process pstatus update start
		
			tbl_allocate_reprocess_update_p_status($dbcon,$res1['p_id']);
			
		//allocate process pstatus update start
				$stop_qty=$stop_qty - $allocate_trn_update_qty;
		
		}
	
}
echo "1";
}


?>
