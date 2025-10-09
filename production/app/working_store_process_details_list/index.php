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
 $getspecialConfiguration=getspecialConfiguration($dbcon);
 $is_store_approval= $company_config['store_approval'];
		if(strtolower($POST['mode']) == "fetch_working") {
			$production_pro_search = $company_config['production_pro_search'];
			$pro_search=explode(",", $production_pro_search);

			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			
			$str='<tbody>';
			$str.='<tr>
				
				<th>#</th>';
				 if($company_config['workorder_wise_production_merge'] ==1)
				 {
					$str .='<th>Workorder No</th>'; 	
				 }
				$str .='
				<th>Product Name</th>
				<th>Product Category</th>';
			
				if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0'){
					$str .= '<th>Batch No / Serial No</th>';
				}
				$str .='<th>Qty</th>
				<th>Pending Qty</th>';

				if($POST['type']=="1"){
					$str .='<th>Start Pending Qty</th>';
				}else{
					$str .='<th>End Pending Qty</th>';
				}

				$str .='<th>Status</th>
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
			$grp = "";

			if($company_config['resource_wise_production'] == "1"){
					$grp = ",resource_id";
			}

			if($company_config['workorder_wise_production_merge'] ==1)
			{

			 $s_ql = "select GROUP_CONCAT(ap.p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,ap.p_product_id, p.product_name,tc.cat_name,p_status,sp.po_req_no as work_order_no, p.batch_wise_stock_manage,ap.batch_no,p.product_icode, dr.drawing_number  from tbl_allocate_process as ap
				
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
			left join tbl_set_main_process as sp on sp.sp_id=rp.sp_id
			where ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type'" .$whr ." group by ap.p_ref_id,ap.process_id,ap.branch_id,ap.product_version,ap.batch_no,ap.extra_stock".$grp ;
		}
			else{
				$s_ql = "select GROUP_CONCAT(ap.p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,ap.p_product_id, p.product_name,tc.cat_name,p_status, p.batch_wise_stock_manage,ap.batch_no,p.product_icode, dr.drawing_number  from tbl_allocate_process as ap
				
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			
			where ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type'" .$whr ." 
			group by ap.p_product_id,ap.branch_id,ap.product_version,ap.batch_no,ap.extra_stock".$grp ;
			}
//echo $s_ql;	
			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				if($POST['type']=="1"){
					$working_qty=production_store_wise_start_count_using_p_id($dbcon,$rel['allocate_id'],$is_store_approval);
					$pending_qty=total_production_pending_qty($dbcon,$rel['allocate_id']);
				}else{
					$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
					// $pending_qty=$working_qty;
					$pending_qty=total_production_pending_qty($dbcon,$rel['allocate_id']);
				}
				// var_dump($working_qty);
				// var_dump($pending_qty);
				$product_name = $rel['product_name'];

				if($working_qty>0){
					if($POST['type']=="1"){
						$status="<strong style='color:red'>Not Started</strong>";
						
						//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/process/'.$rel['branch_id'].'" >Start <i class="fa fa-plus"></i></a>';
						$button = "";
						$new_button = "";
						$batchBtn = "";
						$btn_material = "";
						
						$start_url=urlencode($rel['allocate_id']);
						$url = $rel["allocate_id"];

						if($company_config['production_start_type'] ==  '1'){
							$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_process_start/'.$start_url.'" >Start <i class="fa fa-plus"></i></a>';
							
						}else{
							$button='<button class="btn btn-xs btn-success" data-original-title="Start Process" data-toggle="tooltip" data-placement="top" onclick="show_process_action_model('. "'". $url."'".','. "'". $rel['p_product_id']."'".',1)">Start <i class="fa fa-plus"></i></button>';
							$btn_material='<button class="btn btn-xs btn-danger" data-original-title="Deduct Material" data-toggle="tooltip" data-placement="top" onclick="show_material_model('. "'". $url."'".','. "'". $rel['p_product_id']."'".')"><i class="fa fa-minus"></i> Material</button>';
						}

					}else{
						$status="<strong style='color:green'>Started</strong>";
						$companyConfiguration=getCompanyConfiguration($dbcon);
						$rr=$companyConfiguration['store_relese_first_process'];
						$start_url=urlencode($rel['allocate_id']);
						$url = $rel["allocate_id"];
						if($company_config['production_start_type'] ==  '1'){
							$button='<a class="btn btn-xs btn-danger" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" title="Process End" href="'.ROOT.PRODUCTION_ROOT.'production_process_end/'.$start_url.'" ><i class="fa fa-power-off"></i> End</a>';
						}else{
							$button='<button class="btn btn-xs btn-danger" data-original-title="End Process" data-toggle="tooltip" data-placement="top" onclick="show_process_action_model('. "'". $url."'".','. "'".$rel['p_product_id']."'".',2,'.$rr.')">End <i class="fa fa-power-off"></i></button>';
						}
						
					}
					
					$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }
					
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';	
					$str.='<tr>
							<th>'.$cnt.'</th>';
							 if($company_config['workorder_wise_production_merge'] ==1)
							{
								$str .='<th>'.$rel['work_order_no'].'</th>';
							}
							$str .='
							<th>'.$rel['product_name'].' '.$item_code.' '.$drawing_number.'</th>
							<th>'.$cat_name.'</th>';
							if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0'){
								$str .='<th>'.$rel['batch_no'].'</th>';
							}
							$str .='<th>'.$rel['total_qty'].'</th>
							<th>'.$pending_qty.'</th>
							<th>'.$working_qty.'</th>
							<th>'.$status.'</th>';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							$str.='<th>'.$button.' '.$btn_material. ' ' . $batchBtn .'</th>
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
		else if(strtolower($POST['mode']) == "get_product_name") {
			$product_id = $POST['product_id'];

			$product_name = get_product_name($dbcon,$product_id);
			echo $product_name;
		}
		else if(strtolower($POST['mode']) == "start_process_using_model") {

			// $p_id= urldecode($POST['p_ids']);
			$p_id= $POST['p_ids'];
			$html="";
			
			$query="select ap.resource_id,p.product_name,pr.process_name,ap.branch_id,ap.process_unit,p.product_base_unit,p.product_conv_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,ap.previous_process_id,ap.batch_no,p.batch_wise_stock_manage,p.reorder_qty from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
				
			where ap.p_id in (".$p_id.")";


				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_production_pending_qty($dbcon,$p_id);
				if($is_store_approval){
					// $working_qty =	store_approve_process_wise_production_count($dbcon,$rel['process_id'],1,1,1);
					$working_qty=production_store_wise_start_count_using_p_id($dbcon,$p_id,$is_store_approval);
				}else{
					$working_qty=production_store_wise_start_count_using_p_id($dbcon,$p_id,$is_store_approval);
				}

				if($company_config["production_start_stop_time"] == 0)
				{
					$time_readonly = "readonly='readonly' disabled";
				}
				else
				{
					$time_readonly = "";
				}
				

				
				if($rel["previous_process_id"] == 0)
				{
					$readonly = "";
				}
				else
				{
					$readonly = "readonly='readonly' ";
				}
				
				$process=p_id_wise_find_previous_and_next_process($dbcon,$p_id);
				$process_pr=json_decode($process);

				$previous_process_pid=$process_pr->previous_process_pid;
				
			$rate  = get_product_rate($dbcon,$rel['p_product_id'],$rel['process_id'],"item_mst");
			$html .='
				<div class="col-md-12" style="margin-bottom: 15px;">
				<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Product Name </label>
						<div class="col-md-8 col-xs-11" style="color: #0e8400;font-weight: 600;" >
							'.$rel["product_name"].'
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-3 control-label" style="color: #404040;font-weight: 600;"> Process Name </label>
						<div class="col-md-4 col-xs-11" style="color: #c71313;font-weight: 600;">
							'.$rel["process_name"].'
						</div>
						<label class="col-md-2 control-label" style="color: #404040;font-weight: 600;"> Rate </label>
						<div class="col-md-3 col-xs-11" style="color: #c71313;font-weight: 600;">
						<input type="text" class="form-control process_rate" name="process_rate" id="process_rate" value="'.$rate.'" />
						</div>
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Start Time </label>
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control form_datetime" id="pr_st_time1" name="pr_st_time1" value="'.date('d-m-Y h:i:s').'" '.$time_readonly.' />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Process No*</label>
							<div class="col-md-8 col-xs-11">
								<input id="pr_process_no" name="pr_process_no" type="text" class="form-control" title="Enter Challan No" value="'.$pno.'" placeholder="Process No" required readonly >
							</div>
						</div>
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Pending Qty</label>
							<div class="col-md-8 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
								'.$pending_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Start Qty *</label>
							<div class="col-md-4 col-xs-11">
								<input type="text" name="start_qty" id="start_qty" class="form-control" value="" readonly /> 
							</div>
							<div class="col-md-1" style="font-size: 18px;font-weight: 600;color: #d02424;">
								'.$rel["unit_name"].'
							</div>
						</div>
					</div>
					</div>
					';

					if($company_config['resource_wise_production'] == '1'){
						$html .= '<div class="col-md-12" style="margin-bottom: 15px;">
								<div class="col-md-6">
									<div class="form-group">  	
										<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Machine* </label>
										<div class="col-md-8 col-xs-11">
										<select class="select2" style="width:100%" id="machine">
											'.get_all_resource($dbcon).'
										</select>
										</div>
									</div>	
								</div>
							</div>';
					}

					$pass_pid = "'".$p_id."'";
					$html .= get_filters($dbcon,$pass_pid,$p_id,1);
					if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0'){
					if($company_config['batch_stock'] == '1' &&  $company_config['batch_process'] == '0' && $rel['batch_wise_stock_manage'] == '1'){
						
						$html .='<div class="col-md-12" style="margin-bottom: 15px;">
									<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="batch_id" name="batch_id" value="'.$rel['batch_no'].'" readonly>
												</div>
											</div>
										</div>
									</div>';
						
					}	
				}

				$html .= '<div class="col-md-12" id="tbl_filter_data" style="margin-bottom: 15px;margin-top: 15px;">';	

			$html .= get_start_process_model_data($dbcon,$p_id,1);	
			
			$html .= '</div>
			<div class="col-md-12" style="margin-bottom: 15px;">
						<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
						<div class="col-md-6 col-xs-11">
								<textarea id="remark" name="remark" '.$remark_req.' class="form-control" rows="3"></textarea> 
						</div>
					</div>';
			
			$html .='<input type="hidden" name="mode" id="mode" value="add_start_process_using_model" />
			<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
			<input type="hidden" name="max_available_qty" id="max_available_qty" value="'.$working_qty.'" />
			<input type="hidden" id="pending_qty" name="pending_qty" value="'.$pending_qty.'">
			<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
			<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
			<input type="hidden" name="product_conv_unit" id="product_conv_unit" value="'.$rel["product_conv_unit"].'" />
			<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$select_branch_id.'" />
			<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel["p_product_id"].'" />
			<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />
			<input type="hidden" name="reorder_qty" id="reorder_qty" value="'.$rel['reorder_qty'].'" />
			<input type="hidden" name="product_version" id="product_version" value="'.$rel["product_version"].'" />';
			
			$html .='<div class="col-md-12" >
						<center>
							<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Start The Process" onclick="process_start_using_model()" />
						</center>
					</div>';
			
			echo $html;
			
		}
		else if(strtolower($POST['mode']) == "get_scrap_unit") {
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['scrap_product_id'];
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
		
		else if(strtolower($POST['mode']) == "end_process_using_model") {
			$p_id= $POST['p_ids'];
			$process_end_time_qc = $POST['process_end_time_qc'];
			$html ="";
			
			
			$pno=load_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);
			$working_qty=production_end_count_using_p_id($dbcon,$p_id);

			if($company_config["production_start_stop_time"] == 0)
				{
					$time_readonly = "readonly='readonly' disabled";
				}
				else
				{
					$time_readonly = "";
				}
			
			
					$pass_pid = "'".$p_id."'";
					$html .= get_filters($dbcon,$pass_pid,$p_id,2);
					

					
					
					$html .='<div class="col-md-12" id="tbl_filter_data" style="margin-bottom: 15px;margin-top: 15px;">';
					if($process_end_time_qc == '1'){
						$html .= get_end_process_total_qty_data($dbcon,$p_id,2);	
					}else{
						$html .= get_end_process_model_data($dbcon,$p_id,2);	
					}
					
				$html .='</div>';

				if($process_end_time_qc == '1'){
					$html .='<div class="col-md-12" style="margin-bottom: 15px;" >
						<center>
							<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Next" onclick="next_entry_view()" />
						</center>
					</div>';
				}else{
			
				$html .= '<div class="col-md-12" style="margin-bottom: 15px;" >
					<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
					<div class="col-md-6 col-xs-11">
							<textarea id="remark" name="remark" '.$remark_req.' class="form-control" rows="3"></textarea> 
					</div>
				</div>';

			$chk_p_id = explode(",",$p_id);		
			$process=p_id_wise_find_previous_and_next_process($dbcon,$chk_p_id[0]);
			$process_pr=json_decode($process);

			$next_process_id=$process_pr->next_process_id;		

			/*if($company_config['store_relese_first_process'] == '1' && $qc_paramter_info== '0' && $next_process_id > 0){
				$function = "store_confirm_msg();";
			}else{

				// $function = "process_end_using_model();";
			}	*/
				$function = "next_page();";
				$html .='<div class="col-md-12" style="margin-bottom: 15px;" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="NEXT" onclick="'.$function.'" />
					</center>
				</div>';
			}

			
			echo $html;
			
		}
		else if(strtolower($POST['mode']) == "load_filter_data") {

			$where = "";
			$process_end_time_qc = $POST['process_end_time_qc'];

			if(!empty($POST['client_id'])){
				$where .= " and so.cust_id = " . $POST['client_id'];
			}
			if(!empty($POST['so_id'])){
				$where .= " and so.sales_order_id = " . $POST['so_id'];	
			}
			if(!empty($POST['job_card_no'])){
				$where .= " and ap.p_id = '" . $POST['job_card_no']."'";	
			}
			$p_id = $POST['p_id'];
			if($POST['type'] == '1'){
				get_start_process_model_data($dbcon,$p_id,0,$where);
			}else{
				if($process_end_time_qc == '1'){
						$html .= get_end_process_total_qty_data($dbcon,$p_id,0,$where);	
					}else{
						$html .= get_end_process_model_data($dbcon,$p_id,0,$where);	
					}
				// get_end_process_model_data($dbcon,$p_id,0,$where);
			}
			
		}else if(strtolower($POST['mode']) == "delete_temp_qc_data") {
			$info['status'] = 2;
			$updateid=update_record('tbl_temp_qc', $info,"status = 3 and user_id = " . $_SESSION['user_id'], $dbcon);
		}else if(strtolower($POST['mode']) == "show_raw_material_data") {
			$p_id= urldecode($POST['p_ids']);
			$pid=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];

			$sel_p_id  = implode(",",$pid);

			$meterial_data =	show_raw_material_data($dbcon,$sel_p_id,$POST['working_qty'],$POST['stop_qty']);

			$query_23="select ap.* from tbl_allocate_process as ap where p_status=1 and ap.p_id in (".$sel_p_id.")";
			$rel_23=brp_mysqli_fetch_assoc($dbcon->query($query_23));
			
			$qc_paramter_info = check_product_qc_paramter($dbcon,$rel_23['p_product_id'],$rel_23['process_id']);
			

			$chk_p_id = $pid;	
			$process=p_id_wise_find_previous_and_next_process($dbcon,$chk_p_id[0]);
			$process_pr=json_decode($process);

			$next_process_id=$process_pr->next_process_id;
			
			if($company_config['store_relese_first_process'] == '1' && $qc_paramter_info== '0' && $next_process_id > 0){
				$function = "store_confirm_msg();";
			}else{

				$function = "process_end_using_model();";
			}	

			echo $meterial_data;
			echo '<div class="col-md-12 mtop20" style="margin-bottom: 15px;" >
					<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
					<div class="col-md-6 col-xs-11">
							<textarea id="remark" name="remark" '.$remark_req.' class="form-control" rows="3"></textarea> 
					</div>
				</div>';

			echo '<div class="col-md-12" style="margin-bottom: 15px;" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="End The Process" onclick="'.$function.'" />
						<input type="button"  id="back_btn" name="back" class="btn btn-danger" value="Back" onclick="previous_page();" />
					</center>
				</div>';
			
		}else if(strtolower($POST['mode']) == "load_temp_qc_detail") {
			$where = "";
			$p_id = $POST['p_id'];
			$unit_name = getunitname($dbcon,$POST['unit_id']);
			$process_end_time_qc = $POST['process_end_time_qc'];

			$meterial_data =	show_raw_material_data($dbcon,$p_id,$POST['working_qty'],$POST['total_end_qty']);

			if(!empty($POST['client_id'])){
				$where .= " and so.cust_id = " . $POST['client_id'];
			}
			if(!empty($POST['so_id'])){
				$where .= " and so.sales_order_id = " . $POST['so_id'];	
			}
			if(!empty($POST['job_card_no'])){
				$where .= " and req.job_card_no = '" . $POST['job_card_no']."'";	
			}
			echo "<div class='col-md-6'><h3 class='m-bot20 mtop20 text-success text-center'> Total End Qty : " . $POST['total_end_qty'] . " " . $unit_name. "</h3></div>";
			echo "<div class='col-md-6'><h3 class='m-bot20 mtop20 text-success text-center'> Total QC Qty : <span id='lbl_qc_qty'>0</span> " . $unit_name. "</h3></div>";

			$str = '<div class="col-md-12" style="padding:0;margin-bottom: 15px;" id="accept_row"><h3 class="text-center" style="font-size: 18px;background-color: #5bc0de;
		    color: white; padding: 5px 10px;">Accept Qty Details</h3>
		    '. get_bifurcation_data($dbcon,'accept',$p_id,$where).'
		    <div class="col-md-12" style="margin-top:5px" id="accept_row_data"></div>
		    </div>';

		    if($POST['total_reject_qty'] > 0){
			    $str .= '<div class="col-md-12" style="padding:0;margin-bottom: 15px;" id="reject_row"><h3 class="text-center" style="font-size: 18px;background-color: #5bc0de;
			    color: white; padding: 5px 10px;">Reject Qty Details</h3>
			    '. get_bifurcation_data($dbcon,'reject',$p_id,$where).'
			    <div class="col-md-12" style="margin-top:5px" id="reject_row_data"></div>
			    </div>';
		    }
		     if($POST['total_reprocess_qty'] > 0){
			    $str .= '<div class="col-md-12" style="padding:0;margin-bottom: 15px;" id="reprocess_row"><h3 class="text-center" style="font-size: 18px;background-color: #5bc0de;
			    color: white; padding: 5px 10px;">Reprocess Qty Details</h3>
			    '. get_bifurcation_data($dbcon,'reprocess',$p_id,$where).'
			    <div class="col-md-12" style="margin-top:5px" id="reprocess_row_data"></div>
			    </div>';
			}

			echo $str;
			get_end_process_model_data($dbcon,$p_id,0,$where);	

			echo $meterial_data;

			echo '<div class="col-md-12 mtop20" style="margin-bottom: 15px;" >
					<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
					<div class="col-md-6 col-xs-11">
							<textarea id="remark" name="remark" '.$remark_req.' class="form-control" rows="3"></textarea> 
					</div>
				</div>';

			$chk_p_id = explode(",",$p_id);		
			$process=p_id_wise_find_previous_and_next_process($dbcon,$chk_p_id[0]);
			$process_pr=json_decode($process);

			$next_process_id=$process_pr->next_process_id;	

			// var_dump($company_config['store_relese_first_process']);
			// var_dump($qc_paramter_info);
			// var_dump($company_config['store_relese_first_process']);

			$query_23="select ap.* from tbl_allocate_process as ap where p_status=1 and ap.p_id in (".$p_id.")";
			$rel_23=brp_mysqli_fetch_assoc($dbcon->query($query_23));
		
			$qc_paramter_info = check_product_qc_paramter($dbcon,$rel_23['p_product_id'],$rel_23['process_id']);
			// var_dump($qc_paramter_info);

			if($company_config['store_relese_first_process'] == '1' && $qc_paramter_info== '0' && $next_process_id > 0){
				$function = "store_confirm_msg();";
			}else{
				$function = "process_end_using_model();";
			}	
			echo '<div class="col-md-12" style="margin-bottom: 15px;" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="End The Process" onclick="'.$function.'" />
						<input type="button"  id="back_btn" name="back" class="btn btn-danger" value="Back" onclick="previous_page();" />
					</center>
				</div>';
		}else if(strtolower($POST['mode']) == "add_temp_qc_field") {

				$set11="select p_ref_id as rp_id from tbl_allocate_process where p_id=".$POST['sel_p_id'];
				$set_row=mysqli_fetch_assoc($dbcon->query($set11));

				$info['product_id'] = $POST['product_id'];
				$info['process_id'] = $POST['process_id'];
				$info['p_id'] = $POST['sel_p_id'];
				$info['rp_id'] = $set_row['rp_id'];
				$info['qty'] = $POST['qty'];

				if(strtolower($POST['type']) == "accept"){
					$info['type'] = 1;
				}else if(strtolower($POST['type']) == "reject"){
					$info['type'] = 2;
					$info['new_product_id'] = $POST['new_product_id'];
					$info['new_unit_id'] = $POST['new_unit_id'];
					
				}else if(strtolower($POST['type']) == "reprocess"){
					$info['type'] = 3;
					$info['new_process_id'] = $POST['new_process_id'];
				}
				$info['new_godown_id'] = $POST['new_godown_id'];
				$info['unit_id'] = $POST['unit_id'];
				$info['cdate'] = date("Y-m-d H:i:s");
				$info['user_id'] = $_SESSION['user_id'];
				$info['company_id'] = $_SESSION['company_id'];
				$info['remark'] = $POST['reason'];
				$info['status'] = 3;
				$info['branch_id'] = $POST['branch_id'];

				$insertid=add_record('tbl_temp_qc', $info, $dbcon);

				if($insertid){
					echo "1";
				}else{
					echo "0";
				}
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
		}else if(strtolower($POST['mode']) == "get_jobcard_qty") {
			$p_id = $POST['p_id'];
			$working_qty=production_end_count_using_p_id($dbcon,$p_id);

			$query = "select IFNULL(sum(qty),0) as qty from tbl_temp_qc where status = 3 and p_id = " .$p_id;
			$res = brp_mysqli_fetch_assoc($dbcon->query($query));
			$pending_qty = $working_qty - $res['qty'];

			echo $pending_qty;

		}else if(strtolower($POST['mode']) == "get_jobcard_process_list") {
			$current_product_id = $POST['product_id'];
			$current_process_id = $POST['current_process_id'];
			$p_id = $POST['p_id'];
			$str = get_products_current_and_previous_process($dbcon, $current_product_id, $current_process_id,$p_id);
			echo $str;
		}else if(strtolower($POST['mode']) == "load_jobcard_datatable") {
			$p_id = $POST['p_id'];
			$type = $_POST['type'];
			$where = "";
			
			if($type == "accept"){
				$where =" and qc.type = 1";
			}else if($type == "reject"){
				$where =" and qc.type = 2";
			}else if($type == "reprocess"){
				$where =" and qc.type = 3";
			}
			
			 $query = "select qc.*,rp.job_card_no from tbl_temp_qc as qc 
						left join tbl_allocate_process as ap on ap.p_id = qc.p_id
						left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
						where qc.status = 3 and qc.user_id = ".$_SESSION['user_id']." and qc.p_id in(".$p_id.")".$where;
			$result = $dbcon->query($query);
			$cnt = brp_mysqli_num_rows($result);
			$x = 1;
			if($cnt){
				$str .= '<table class="display table table-bordered table-striped" id="">
						<tr>
							<th>Sr No.</th>
							<th>Jobcard No</th>
							<th>'.$type.' Qty</th>
							<th>'.$type.' Reason</th>
							<th>Acion</th>
						</tr>';	
				while ($row = brp_mysqli_fetch_assoc($result)) {
					$str .= '<tr>
								<th>'.$x.'</th>
								<th>'.$row['job_card_no'].'</th>
								<th>'.$row['qty'].'</th>
								<th>'.$row['remark'].'</th>
								<th><button class="btn btn-danger" onClick="remove_temp_qc_data('.$row['temp_id'].',\''.$type.'\','.$row['p_id'].','.$row['qty'].')"><i class="fa fa-trash"></i></th>
							</tr>';
							$x++;
				}
				$str .="</table>";
			}						
			echo $str;
		}else if(strtolower($POST['mode']) == "delete_temp_qc") {
			$info['status'] = 2;
			$updateid=update_record('tbl_temp_qc', $info,"temp_id =" . $POST['temp_id'],$dbcon);
			if($updateid){
				echo "1";
			}else{
				echo "0";
			}
		}else if(strtolower($POST['mode'])== "convert_qty")
		{
			$row=array();
			if($POST["type"]=="1"){
				$type="conv_unit";
				$ret_qty=convert_stock($dbcon,$_POST['base_qty'],$POST['product_id'],$type);
			}else if($POST["type"]=="2"){
				$type="base_unit";
				$ret_qty=convert_stock($dbcon,$_POST['conv_qty'],$POST['product_id'],$type);
			}else{
				$ret_qty="0";
			}
				//var_dump($ret_qty);
			$ret_qty_new=number_format($ret_qty, 5, ".", "");
			$ret_qty_new = round_up($ret_qty,5);
				//$ret_qty=$ret_qty;
			//	echo $ret_qty;
			$row['show_qty']=$ret_qty_new;
			$row['hide_qty']=$ret_qty;
			echo json_encode($row);
		}else if(strtolower($POST['mode'])== "delete_temp_batch_wise_deduct_qty"){
			$p_id = $POST['p_id'];
			$product_id = $POST['product_id'];
			$type = $POST['type'];
			$info['status'] = 2;

			$updateid=update_record('tbl_batch_temp_material_start_time_deduct', $info,"status = 3 and p_id IN (" . $p_id . ") and product_id IN (" . $product_id . ") and type = " . $type,$dbcon);

		}else if(strtolower($POST['mode'])== "batch_stock_model_open"){

			$deduct_qty = $POST['enter_base_qty'];
			$deduct_conv_qty = $POST['enter_conv_qty'];
			$product_id = $POST['product_id'];
			$p_id=$POST['p_id'];
			$rp_id=$POST['rp_id'];
			$process_stock=$POST['process_stock'];
			if($process_stock == '1'){
				$query_dstock = "select st.batch_no,rp.job_card_no,sp.po_req_no,ap.p_ref_id as request_id,i.*,(cast(i.base_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(base_stock),0) as bstock from tbl_process_reserve_stock where stock_status != 2  and   p_id IN (".$p_id .") and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_base_stock,(cast(i.conv_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(conv_stock),0) as cstock from tbl_process_reserve_stock where stock_status != 2 and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_conv_stock from tbl_process_reserve_stock as i 
					left join tbl_process_stock_trn as st on st.process_stock_id = i.process_stock_id 
					LEFT JOIN tbl_allocate_process as ap on ap.p_id = i.p_id
					LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id
					LEFT JOIN tbl_set_main_process as sp on sp.sp_id = rp.sp_id
					where i.stock_status != 2 and i.stock_flage=1 and i.ref_name = 'store_release' and i.product_id IN (".$product_id.") and i.p_id IN (" . $p_id. ") HAVING pending_base_stock > 0";
			}else{
			 $query_dstock = "select st.batch_no,rp.job_card_no,sp.po_req_no,i.*,(cast(i.base_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(base_stock),0) as bstock from tbl_reserve_stock where stock_status != 2 and request_id IN (" .$rp_id. ") and stock_flage = 2 and p_id IN (".$p_id .") and perent_id = i.reserve_id),0)) as pending_base_stock,(cast(i.convert_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(convert_stock),0) as cstock from tbl_reserve_stock where stock_status != 2 and stock_flage = 2 and request_id IN (" .$rp_id. ")  and p_id IN (".$p_id .") and perent_id = i.reserve_id),0)) as pending_conv_stock from tbl_reserve_stock as i 
				left join tbl_stock_trn as st on st.stock_id = i.stock_id 
				LEFT JOIN tbl_allocate_process as ap on ap.p_id = i.p_id
				LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id
				LEFT JOIN tbl_set_main_process as sp on sp.sp_id = rp.sp_id
				where i.stock_status != 2 and i.stock_flage=1 and i.ref_name = 'store_release' and  i.request_id IN (" .$rp_id. ") and i.product_id IN (".$product_id.") and i.p_id IN(".$p_id . ") HAVING pending_base_stock > 0";
			}
			
			// echo $query_dstock;

			$res_result=$dbcon->query($query_dstock);
			$res_cnt = brp_mysqli_num_rows($res_result);
			$html = "";
			if($res_cnt > 0){
				$div_base_qty = $deduct_qty / $res_cnt; 
				$div_conv_qty = $deduct_conv_qty / $res_cnt; 

				$div_base_qty = round_up($div_base_qty,5);
				$div_conv_qty = round_up($div_conv_qty,5);

				$str .= '<div class="col-md-12">
					<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Workorder No</th>
							<th width="15%" class="text-center">Jobcard No</th>
							<th width="15%" class="text-center">Batch No</th>
							<th width="15%" class="text-center">Godown</th>
							<th width="15%" class="text-center">Available Qty</th>
							<th width="35%" class="text-center">Deduct Qty</th>
					   </tr>
					<tbody id="field1">';
				$x=1;				
		
				while($rel=brp_mysqli_fetch_assoc($res_result))
				{	

					$reserve_id = "";
					$unitname = getunitname($dbcon,$rel['base_unit']);
					if($process_stock == '1'){
						$reserve_id = "data-reserve_id='".$rel['process_reserve_id']."'";
						$conv_unitname = getunitname($dbcon,$rel['conv_unit']);
					}else{
						$reserve_id = "data-reserve_id='".$rel['reserve_id']."'";
						$conv_unitname = getunitname($dbcon,$rel['convert_unit']);
					}
					
					$str .= '<tr>';
					$str .= '<td>'.$rel['po_req_no'].'</th>';			
					$str .= '<td>'.$rel['job_card_no'].'</th>';			
					$str .= '<td>'.$rel['batch_no'].'</th>';			
					$str .= '<td>'.get_godown_name($dbcon,$rel['godown_id']).'</td>
						<td>'.$rel['pending_base_stock'].' '. $unitname .'<p>'.$rel['pending_conv_stock'].' '. $conv_unitname .'</p></td>
						<td class="text-center">
							<div class="col-md-9">
								<input type="number" title="Enter Stock" min="0" id="deduct_base_stock_'.$x.'" name="deduct_base_stock" '.$function.' class="deduct_base_stock form-control numbersOnly" '.$reserve_id.' data-rp_id="'.$rel['request_id'].'" data-p_id="'.$rel['p_id'].'" data-mt_trn_id="'.$rel['ref_id'].'"  value="'.$div_base_qty.'" onkeyup="reserve_stock_convert_qty('.$x.');"/>
							</div>
							<div class="col-md-3" style="margin-top:5px">
							 	<span> '.$unitname.' </span>
							</div>
							<div class="col-md-9" style="margin-top:5px">
							  	<input type="number" title="Enter Stock" min="0" id="deduct_conv_stock_'.$x.'" name="deduct_conv_stock" readonly class="form-control numbersOnly" value="'.$div_conv_qty.'" onkeyup="reserve_stock_convert_qty(1);" />
							</div>
							<div class="col-md-3"  style="margin-top:10px">
								<span> '.$conv_unitname.' </span>
							</div>
						</td>
					</tr>';
					$x++;
				}
				$str .= '<input type="hidden" id="total_rel_material" value="">';
				$str .= "</tbody></table></div>";
			}

			$row['html_data'] = $str;
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "add_batch_wise_deduct_qty") {

			// echo "<pre>";
			// print_r($POST);die;
			  $info['product_id']=$POST['product_id'];
			  
			  $info['base_unit']=$POST['base_unit'];
			  $info['conv_unit']=$POST['conv_unit'];
			  $info['is_process_stock']=$POST['process_stock'];
			  $info['status']=3;
			  $info['type']=$POST['type'];
			  $info['cdate'] = date("Y-m-d H:i:s");
			  $info['user_id'] = $_SESSION['user_id'];
			  $info['company_id'] = $_SESSION['company_id'];
			  $arr_reserve_id = $POST['arr_reserve_id'];
			  $arr_p_id = $POST['p_id'];
			  $arr_rp_id = $POST['rp_id'];
			  $arr_mt_trn_id = $POST['mt_trn_id'];
			  $arr_base_qty = $POST['arr_base_qty'];
			  $arr_conv_qty = $POST['arr_conv_qty'];
			  for($i=0;$i<count($arr_reserve_id);$i++){
				  $info['reserve_id'] = $arr_reserve_id[$i];
				  $info['deduct_qty'] = $arr_base_qty[$i];
				  $info['deduct_conv_qty'] = $arr_conv_qty[$i];
				  $info['rp_id']=$arr_rp_id[$i];
			  	  $info['p_id']=$arr_p_id[$i];
			  	  $info['mt_trn_id']=$arr_mt_trn_id[$i];
				  $s_mat_id = add_record('tbl_batch_temp_material_start_time_deduct',$info, $dbcon);
			  }
			  

			if($s_mat_id){
				echo '1';
			}else{
				echo '0';
			}
		}
		else if(strtolower($POST['mode']) == "get_product_details_data") {
			
			$p_id= $POST['p_ids'];
			$process_end_time_qc = $POST['process_end_time_qc'];
			$html ="";

			$query="select ap.resource_id,p.product_name,pr.process_name,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,ap.branch_id,ap.batch_no,p.batch_wise_stock_manage,ap.batch_no,p.reorder_qty from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
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
			$working_qty=production_end_count_using_p_id($dbcon,$p_id);

			if($company_config["production_start_stop_time"] == 0)
				{
					$time_readonly = "readonly='readonly' disabled";
				}
				else
				{
					$time_readonly = "";
				}
			
			$html .='
				<div class="col-md-12" style="mtop20 margin-bottom: 15px;" >
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Product Name </label>
						<div class="col-md-8 col-xs-11" style="color: #0e8400;font-weight: 600;" >
							'.$rel["product_name"].'
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Process Name </label>
						<div class="col-md-8 col-xs-11" style="color: #c71313;font-weight: 600;">
							'.$rel["process_name"].'
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-bottom: 15px;" >
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">End Process Time</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control form_datetime" id="pr_end_time1" name="pr_end_time1" value="'.date('d-m-Y h:i:s').'" '.$time_readonly.' />
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Process No</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control" id="process_no" name="process_no" value="'.$pno.'" readonly />
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12" style="margin-bottom: 15px;" >
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Pending Qty</label>
							<div class="col-md-8 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
								'.$working_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Stop Qty *</label>
							<div class="col-md-4 col-xs-11">
								<input type="text" name="stop_qty" id="stop_qty" class="form-control" readonly value="'.$working_qty.'" /> 
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
							<div class="col-md-8 col-xs-11">
								<select class="form-control" name="grn_godown"  id="grn_godown" required >
									'.get_all_godown($dbcon,'',1).'
								</select>
							</div>
						</div>
					</div>
					</div>';
					if($company_config['resource_wise_production'] == '1'){
						$html .= '<div class="col-md-12" style="margin-bottom: 15px;">
								<div class="col-md-6">
									<div class="form-group">  	
										<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Machine* </label>
										<div class="col-md-8 col-xs-11">
										<select class="select2" style="width:100%" id="machine">
											'.get_all_resource($dbcon,$rel["resource_id"]).'
										</select>
										</div>
									</div>	
								</div>
							</div>';
					}

					$pass_pid = "'".$p_id."'";
					
					if($company_config['batch_wise_stock'] == '1'){
					if($company_config['batch_stock'] == '1' &&  $company_config['batch_process'] == '0' && $rel['batch_wise_stock_manage'] == '1'){

						$html .='
									<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="batch_id" name="batch_id" value="'.$rel['batch_no'].'" readonly>
												</div>
											</div>
										</div>
									';

					}
					if($company_config['batch_wise_stock'] == '1' && $company_config['batch_stock'] == '0' &&  $company_config['batch_process'] == '0' && $rel['batch_wise_stock_manage'] == '1'){

						$html .='
									<div class="col-md-6">
											<div class="form-group">
												<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
												<div class="col-md-8 col-xs-11">
													<input type="text" class="form-control" id="batch_no" name="batch_no" value="'.$rel['batch_no'].'" readonly>
												</div>
											</div>
										</div>
									';

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
							<div class="col-md-8 col-xs-11">
								<input type="text" class="form-control" id="batch_id" name="batch_id" value="'.$batch_no.'" '.$readonly.'>
							</div>
						</div>
					</div>';
					}

				}

					$html .='<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Scrap Product</label>
							<div class="col-md-8 col-xs-11">
								<select class="form-control select2" name="product_scrap_id" id="product_scrap_id" onChange="get_scrap_unit(this.value)">
                                  '.getScrapCode($dbcon,$id).' 
                                 </select>
							</div>
						</div>
					</div>
					
					';

					$html .= '<div class="col-md-12 mtop20 scrap_row" style="display:none;">
					<div class="col-md-6 ">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Scrap Unit</label>
							<div class="col-md-8 col-xs-11">
								<select class="form-control select2" name="scrap_unit" id="scrap_unit">
                                  <option value="">Choose Scrap Unit</option>
                                 </select>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Scrap Qty</label>
							<div class="col-md-8 col-xs-11">
								<input type="number" class="form-control" id="scrap_qty" name="scrap_qty" value="">
							</div>
						</div>
					</div>
					</div>';

					$html .= '</div>';

					$html .='<input type="hidden" name="mode" id="mode" value="end_process" />
					<input type="hidden" name="max_available_qty" id="max_available_qty" value="'.$working_qty.'" />
					<input type="hidden" id="pending_qty" name="pending_qty" value="'.$working_qty.'">
					<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
					<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
					<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$rel['branch_id'].'" />
					<input type="hidden" name="reorder_qty" id="reorder_qty" value="'.$rel['reorder_qty'].'" />
					<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel['p_product_id'].'" />
					<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />';
				echo $html;
		}  

else if(strtolower($POST['mode']) == "get_material_deduct_details_data") {

			$string="";
			
			$sel_p_id  = $POST['p_id'];
			$query1= "select ap.p_id as p_id,sp.po_req_no,ap.p_product_id as product_id,ap.previous_process_id,p.product_name,umst.unit_name,req.rp_id as req_id, cmst.unit_name as conv_unit_name,p.batch_wise_stock_manage, req.purchase_unit,req.process_unit from tbl_allocate_process as ap 
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_request_product req on req.rp_id=ap.p_ref_id 
			left join tbl_set_main_process sp on sp.sp_id=req.sp_id 
			left join unit_mst as umst on umst.unitid=ap.process_unit 
			left join unit_mst as cmst on cmst.unitid=req.purchase_unit
			where ap.p_id in (".$sel_p_id.")" ;
			
			$result1=$dbcon->query($query1);
			
			$string .='
				<div class="col-md-12 text-center mtop20  bg-primary" style="margin-bottom:10px">
					<h3 style="color: white;font-weight: 500;padding:3px;">Material List</h3>	
				</div>';
			$x=0;
			$arr_total = array();
			$cnt=brp_mysqli_num_rows($result1);

			if($cnt > 0){
				$string .='<div class="col-md-12">
					<table class="display table table-bordered table-striped" id="">
					<tr>';
					// if($getspecialConfiguration['hermattic_permission']=="1") {
					 
					 // }
						$string .='<th width="5%">Sr.No.</th>
						<th width="20%">Workorder No</th>
						<th width="20%">Product Name</th>
						<th width="20%">Godown</th>
						<!-- <th>Unit</th> -->
						<th width="15%">Total Material Qty</th>
						<th width="15%">Total Used Qty</th>
						<th width="10%">Batch Wise</th>
						';
						$string .='</tr>
						<tbody>
						';
			}
			while($row=brp_mysqli_fetch_array($result1)){
				/*$product_name = $row['product_name'];
				$unitname = $row['unit_name'];*/
				if($row['previous_process_id'] == "0"){
					
				$query2 = "select rp_id as rp_id,rp_pid as product_id,trp.req_qty_one,trp.process_unit,trp.purchase_unit ,p.product_name,umst.unit_name as unit_name,cmst.unit_name as conv_unit_name, p.batch_wise_stock_manage, trp.purchase_unit, trp.process_unit from tbl_request_product as trp 
						 left join product_mst as p on p.product_id=trp.rp_pid 
						 left join unit_mst as umst on umst.unitid=trp.process_unit 
						 left join unit_mst as cmst on cmst.unitid=trp.purchase_unit 
						 where trp.status !=2 and trp.perent_id in(". $row['req_id'].")";

					$result2=$dbcon->query($query2);
					
					$x = 1;
					while($row2=brp_mysqli_fetch_array($result2)){

						 $mt_qry = "select (sum(mtr.base_qty)-sum(used_base_qty)) as total_release_qty,(sum(mtr.conv_qty)-sum(used_conv_qty)) as total_release_conv_qty,gd.gd_name,mtr.to_godown_id from tbl_material_release_trn as mtr left join mst_godown as gd on gd.gd_id=mtr.to_godown_id where mtr.release_status = 1 and mtr.status = 0 and mtr.p_id in(". $row['p_id'] . ") and mtr.product_id in(" . $row2['product_id'].")";
						// echo "</br></br>";
						$mt_res = $dbcon->query($mt_qry);
						$mt_row = brp_mysqli_fetch_assoc($mt_res);
						$product_name = $row2['product_name'];
						$unitname = $row2['unit_name'];
						$conv_unitname = $row2['conv_unit_name'];

						$string .= "<tr>";
						$string .= "<td>".$x."</td>";
						$string .= "<td>".$row['po_req_no']."</td>";
						$string .= "<td>".$product_name."</td>";
						$string .= "<td>".$mt_row['gd_name']."</td>";
						// $string .= "<td>".$unitname."</td>";
						$string .= "<td> <strong>".round_up($mt_row['total_release_qty'],5). " ". $unitname ."</br>".round_up($mt_row['total_release_conv_qty'],5). " ". $row2['conv_unit_name']  ."</strong></td>";
						$string .= "<td>
							<div class='col-md-10'>
							<input type='number' id ='total_used_qty".$x."' class='numbersOnly form-control used_material_qty' data-material_qty=".round_up($mt_row['total_release_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row2['product_id']."' data-request_id = '".$row2['rp_id']."' data-process_stock='0' data-unit_id='".$row2['process_unit']."' data-conv_unit_id='".$row2['purchase_unit']."' onkeyup='convert_qty(1,".$x.",".$row2['product_id'].")';/>
							</div>
							<div class='col-md-2'>
							<strong> ".$unitname."</strong>
							</div>
							<div class='col-md-10' style = 'margin-top:10px;'>
						<input type='number' id ='total_used_qty2_".$x."' class='numbersOnly form-control used_material_qty2' data-material_qty=".round_up($mt_row['total_release_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row2['product_id']."' data-request_id = '".$row2['rp_id']."' data-process_stock='0' readonly/>						
						</div>
						<div class='col-md-2'  style = 'margin-top:10px;'>
						<strong> ".$conv_unitname."</strong>
							</div>
						</td>
						<input type='hidden' id='batch_wise_stock_manage".$x."' value='".$row2['batch_wise_stock_manage']."'>
						";
						if($row2['batch_wise_stock_manage'] == '1'){
							$string .= "<td>
							<input type='hidden' id='total_batch_ded_qty".$x."' value='0'>
							<input type='hidden' id='total_batch_ded_conv_qty".$x."' value='0'>
							<button type='button' class='btn btn-round btn-success btn-xs' onclick='open_batch_wise_qty($x,1);' ><i class='fa fa-plus'></i></button>
							</td>";
						}else{
							$string .= "<td></td>";	
						}
						

						$string .= "</tr>";
						$x++;
					}

				}else{
					
					$unitname = $row['unit_name'];

					$process=p_id_wise_find_previous_and_next_process($dbcon,$row['p_id']);
					$process_pr=json_decode($process);
					$previous_process_pid=$process_pr->previous_process_pid;

					$q = "select ap.*,pr.process_name from tbl_allocate_process as ap 
					left join process_mst as pr on pr.process_id = ap.process_id 
					where p_id in(". $previous_process_pid.")";
					$res_2=$dbcon->query($q);
					// $row_2=brp_mysqli_fetch_array($res_2);

					

					$x = 1;
					while($row_2=brp_mysqli_fetch_array($res_2)){

						 $mt_qry = "select (sum(mtr.base_qty)-sum(used_base_qty)) as total_release_qty,(sum(mtr.conv_qty)-sum(used_conv_qty)) as total_release_conv_qty,gd.gd_name,mtr.to_godown_id from tbl_material_release_trn as mtr left join mst_godown as gd on gd.gd_id=mtr.to_godown_id where mtr.release_status = 1 and mtr.status = 0 and mtr.p_id in(". $row['p_id'] . ") and mtr.product_id in(" . $row['product_id'].")";
						// echo "</br></br>";
						$mt_res = $dbcon->query($mt_qry);
						$mt_row = brp_mysqli_fetch_assoc($mt_res);
						$product_name = $row['product_name'].' - ['. $row_2['process_name'] .']';
						$unitname = $row['unit_name'];
						$conv_unitname = $row['conv_unit_name'];

						$string .= "<tr>";
						$string .= "<td>".$x."</td>";
						$string .= "<td>".$row['po_req_no']."</td>";
						$string .= "<td>".$product_name."</td>";
						$string .= "<td>".$mt_row['gd_name']."</td>";
						// $string .= "<td>".$unitname."</td>";
						$string .= "<td> <strong>".round_up($mt_row['total_release_qty'],5). " ". $unitname ."</br>".round_up($mt_row['total_release_conv_qty'],5). " ". $conv_unitname ."</strong></td>";
						// $string .= "<td>".$mt_row['total_release_qty']."</td>";
						// $string .= "<td><input type='number' id ='total_used_qty".$x."' class='numbersOnly form-control used_material_qty' data-material_qty=".$mt_row['total_release_qty']." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row['product_id']."' data-request_id = '".$row['rp_id']."'/></td>";

						$string .= "<td>
							<div class='col-md-10'>
							<input type='number' id ='total_used_qty".$x."' class='numbersOnly form-control used_material_qty' data-material_qty=".round_up($mt_row['total_release_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row['product_id']."'  data-unit_id='".$row['process_unit']."' data-conv_unit_id='".$row['purchase_unit']."' data-request_id = '".$row['req_id']."' data-process_stock='1' onkeyup='convert_qty(1,".$x.",".$row['product_id'].")';/>
							</div>
							<div class='col-md-2'>
							<strong> ".$unitname."</strong>
							</div>
							<div class='col-md-10' style = 'margin-top:10px;'>
						<input type='number' id ='total_used_qty2_".$x."' class='numbersOnly form-control used_material_qty2' data-material_qty=".round_up($mt_row['total_release_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row['product_id']."' data-request_id = '".$row['req_id']."' data-process_stock='1' readonly/>						
						</div>
						<div class='col-md-2'  style = 'margin-top:10px;'>
						<strong> ".$conv_unitname."</strong>
							</div>
						</td>
						<input type='hidden' id='batch_wise_stock_manage".$x."' value='".$row2['batch_wise_stock_manage']."'>
						";

						if($row['batch_wise_stock_manage'] == '1'){
							$string .= "<td>
							<input type='hidden' id='total_batch_ded_qty".$x."' value='0'>
							<input type='hidden' id='total_batch_ded_conv_qty".$x."' value='0'>
							<button type='button' class='btn btn-round btn-success btn-xs' onclick='open_batch_wise_qty($x,1);' ><i class='fa fa-plus'></i></button>
							</td>";
						}else{
							$string .= "<td></td>";	
						}

						$string .= "</tr>";
						$x++;
					}
				}
				$x++;
				// echo $query2;
			} 

			if($cnt > 0){
				$string .= "</tbody></table></div>";
			}
			
			echo $string;
}else if(strtolower($POST['mode']) == "material_qty_deduct_start_time") {
	$arr_material_product_id = $POST['material_product_id'];
	$arr_material_used_qty = $POST['material_used_qty'];
	$arr_material_pid = $POST['material_pid'];
	$arr_material_godown_id = $POST['material_godown_id'];
	$arr_process_stock = $POST['process_stock'];
	$arr_rp_id = $POST['rp_id'];
	$arr_batch_wise_stock_manage = $POST['batch_wise_stock_manage'];

	// ded
	$arr['msg'] = 0;
	for($i=0;$i<count($arr_material_used_qty);$i++){

		$mtinfo['product_id'] = $arr_material_product_id[$i] ;
		$mtinfo['rp_id'] = $arr_rp_id[$i] ;
		$mtinfo['p_id'] = $arr_material_pid[$i] ;
		$mtinfo['deduct_qty'] = $arr_material_used_qty[$i] ;
		$mtinfo['godown_id'] = $arr_material_godown_id[$i] ;
		$mtinfo['is_process_stock'] = $arr_process_stock[$i] ;
		$mtinfo['cdate'] = date("Y-m-d H:i:s");
		$mtinfo['user_id'] = $_SESSION['user_id'];
		$mtinfo['company_id'] = $_SESSION['company_id'];

		$s_mat_id =	add_record('tbl_material_start_time_deduct',$mtinfo, $dbcon);

		if($s_mat_id){
			$arr['msg'] = 1;
		}

		if($arr_batch_wise_stock_manage[$i] == '1'){

			$batch_info = array();
			$batch_info['mt_id'] = $s_mat_id;
			$batch_info['status'] = 0;
			$bt_upd_id = update_record('tbl_batch_temp_material_start_time_deduct', $batch_info,"status = 3  and type = 1  and p_id =" . $arr_material_pid[$i] . " and product_id = " . $arr_material_product_id[$i],$dbcon);


			$bt_query = "SELECT * FROM tbl_batch_temp_material_start_time_deduct WHERE type = 1 and status = 0 AND p_id = ". $arr_material_pid[$i] . " and product_id = " . $arr_material_product_id[$i] . " and mt_id = " . $s_mat_id . " and rp_id = " . $arr_rp_id[$i];
			$bt_result = $dbcon->query($bt_query);
			while ($bt_row = brp_mysqli_fetch_assoc($bt_result)) {
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

								$updatetrnid=update_record('tbl_material_release_trn',$upd_mt_info,"material_trn_id=".$mt_row['material_trn_id'], $dbcon);

							}
						}
					}
					start_time_production_deduct_process_reserve_stock($dbcon,$bt_row['deduct_qty'],$bt_row['base_unit'],$bt_row['deduct_conv_qty'],$bt_row['conv_unit'],$bt_row['p_id'],$bt_row['tmp_id'],"batch_process_start_time_deduct",date("Y-m-d"),$bt_row['reserve_id']);
				}else{
					$query33 = "select rp_id,perent_id,req_qty_one,rp_pid as product_id,branch_id,purchase_unit,process_unit,customer_id from tbl_request_product as req_product
						where req_product.rp_id=".$bt_row['rp_id'];
					$result33=$dbcon->query($query33);
					$row33=brp_mysqli_fetch_array($result33);

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

					$qry = "select res.* from tbl_reserve_stock where stock_status != 2 and reserve_id = " . $bt_row['reserve_id'];
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
					
					$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$bt_row['p_id']." and rp_id = "  . $bt_row['rp_id'];
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
					
					 batch_start_time_material_deduct_reserve_stock($dbcon,$bt_row['deduct_qty'],$bt_row['base_unit'],$bt_row['deduct_conv_qty'],$bt_row['conv_unit'],$bt_row['p_id'],$bt_row['tmp_id'],'batch_process_start_time_deduct',date("Y-m-d"),$bt_row['reserve_id']);
				}
			}
		}else{
			$query = "select rp_id,perent_id,req_qty_one,rp_pid as product_id,branch_id,purchase_unit,process_unit,customer_id from tbl_request_product as req_product
			where req_product.rp_id=".$arr_rp_id[$i];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_array($result);
				if($arr_process_stock[$i] == '1'){

					$used_mat_qty =  $arr_material_used_qty[$i];
					
					$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$arr_material_pid[$i]." and rp_id = " . $arr_rp_id[$i];
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
					$stock_date=date("Y-m-d");
					production_deduct_process_reserve_stock($dbcon,$arr_material_used_qty[$i],$row['process_unit'],$arr_material_pid[$i],$s_mat_id,"process_start_time_deduct",$stock_date);
				}else if($arr_process_stock[$i] == '0'){
					

					$info['allocate_process_id']	= $arr_material_pid[$i];
					$info['product_id']				= $arr_material_product_id[$i];
					$info['qty_need_for_single']	= $row['req_qty_one'];
					$info['total_req_qty']			= $arr_material_used_qty[$i];
					$info['unit_id']				= $row['process_unit'];
					$info['grn_trn_sub_id']			= '';
					$info['remark']			= 'Process start time material deduct';

					$qry = "select res.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_reserve_stock where stock_status != 2 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and stock_flage = 1 and godown_id = ".$arr_material_godown_id[$i]." and p_id=".$arr_material_pid[$i]." and product_id = " . $arr_material_product_id[$i];
					$result1=$dbcon->query($qry);
					$row1=brp_mysqli_fetch_array($result1);
						$qry1 = "select * from tbl_stock_trn where stock_status = 0 and stock_id=".$row1['stock_id'];
						$result2=$dbcon->query($qry1);
						$row2=brp_mysqli_fetch_array($result2);
			
						$rate = $row2['base_rate'];
						$conv_rate = $row2['conv_rate']; 
						$info['rate']			= $rate;
						$info['total_rate']		= $rate * $arr_material_used_qty[$i];

						$total_required_conv_qty = convert_stock($dbcon,$arr_material_used_qty[$i], $row['product_id'],"conv_unit");
						$info['conv_rate']			= $conv_rate;
						$info['total_conv_rate']		= $conv_rate * $total_required_conv_qty;
					
					$info['cdate']					= date("Y-m-d H:i:s");
						$info['user_id']				= $_SESSION['user_id'];
						$info['company_id']				= $_SESSION['company_id'];

					$process_material_id=add_record('tbl_allocate_process_material',$info, $dbcon,$row['branch_id']);

					$used_mat_qty =  $arr_material_used_qty[$i];
					
					$mt_qry = "select *, (base_qty-used_base_qty) as pending_qty,(conv_qty-used_conv_qty) as pending_conv_qty from tbl_material_release_trn where status = 0 and release_status = 1 and p_id = ".$arr_material_pid[$i]." and rp_id = "  . $arr_rp_id[$i];
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
					tbl_allocate_process_material_wise_reserve_stock_minus($dbcon,$process_material_id,"","",$s_mat_id,"process_start_time_deduct");
				}
		}

	}
	echo json_encode($arr);
}


function get_start_process_model_data($dbcon,$p_id,$return = 0,$where=""){
$company_config = getCompanyConfiguration($dbcon);	
$is_store_approval= $company_config['store_approval'];


	if(!$return){
		$p_id = $_POST['p_id'];
	}
	$str = ""; 

	$str .='
					<table class="display table table-bordered table-striped" id="">
					<tr>';
					// if($getspecialConfiguration['hermattic_permission']=="1") {
					 
					 // }
						$str .='<th>Client Name</th>';
							$str .='<th>Sales Order No</th>';
					$str .='
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Pending Qty</th>
						<th>Start Pending Qty</th>
						<th>Start Qty</th>';
			
			$str .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			
	 $query1 = "select ap.description, ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id,smain.sales_order_no,cust.l_name  from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.")" . $where;

			$result1=$dbcon->query($query1);
			$start_qty=0;
			$s=1;
			if(brp_mysqli_num_rows($result1) == 0){
				$str .= "<tr style='text-align:center;'><td colspan='10'>No data found for this selection</td></tr>";
			}else{
				while($row=brp_mysqli_fetch_array($result1)){
					
					$start_qty=production_store_wise_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);
					
					if($start_qty > 0){
						$str .='<tr id="trid'.$row['p_id'].'">';
					 // if($getspecialConfiguration['hermattic_permission']=="1") {
						 	
						 // }
						 	$str .='<th>'.$row["l_name"].'</th>';
						 	$str .='<th>'.$row["sales_order_no"].'</th>';
							$str .='
								<th>'.$row["work_order_no"].'</th>
								<th>'.$row["work_order_date"].'</th>
								<th>'.$row["job_card_no"].'</th>
								<th>'.$row["job_card_date"].'</th>
								<th>'.$row["description"].'</th>
								<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
								<th>'.$start_qty.' '.$row["unit_name"].'</th>
								
								<th><input type="text" class="form-control start_qty" name="start_qty1[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$start_qty.'" id="start_qty1'.$row['p_id'].'" value="" onkeyup="check_start_validation();" />
								 '.$row["unit_name"].'
								</th>';
								
								$str .= '<!--<th class="nosort">
									<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
								</th>-->											
							</tr>';
						}
					$s++;
				}
			} 

			$remark_req = 'data-required = "no"';
			if($getspecialConfiguration['hermattic_permission']=="1") {
				$remark_req = 'data-required = "yes"';
			}
			
			$str .='</table>';
			

					if($return){
						return $str;
					}else{
						echo $str;
					}	
}

function get_end_process_model_data($dbcon,$p_id,$return = 0,$where=""){
	$company_config = getCompanyConfiguration($dbcon);
	$process_end_time_qc = $company_config['process_end_time_qc'];
	if(!$return){
		$p_id = $_POST['p_id'];
	}
	$str = ""; 
	$str .='<table class="display table table-bordered table-striped" id="">
					<tr>';
					$str .='<th>Client Name</th>';
					// if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$str .='<th>Sales Order No</th>';
					 // }
						
					$str .='
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Pending Qty</th>
						<th>End Pending Qty</th>
						<th>QTY VARIATION</th>
						<th>Jobwork Close</th>
						<th>End Qty</th>
						<th>Actual Qty</th>';

						if($process_end_time_qc == '1'){
							$str .='
							<th>Accept Qty</th>
							<th>Reject Qty</th>
							<th>Reprocess Qty</th>
							';
						}
					/*if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0'){ 
						if($company_config['batch_type']=='0') {
							$str .='<th>Batch No</th>';
						}else{
							$str .='<th>Serial No</th>';
						}
					}*/
					$str .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			$query1 = "select ap.description, ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id,smain.sales_order_no,cust.l_name  from tbl_allocate_process as ap
							left join product_mst as p on p.product_id=ap.p_product_id 
							left join tbl_request_product req on req.rp_id=ap.p_ref_id
							left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
							left join unit_mst as umst on umst.unitid=ap.process_unit
							left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
							where ap.p_id in (".$p_id.") " . $where;

					$result1=$dbcon->query($query1);
					
					
					$start_qty=0;
					$s=1;
			if(brp_mysqli_num_rows($result1) == 0){
				$str .= "<tr style='text-align:center;'><td colspan='10'>No data found for this selection</td></tr>";
			}else{

				$total_working_qty = 0;
				$unit_name = "";
				while($row=brp_mysqli_fetch_array($result1)){
					$unit_name = $row['unit_name'];
					$start_qty=production_end_count_using_p_id($dbcon,$row['p_id']);
					if($start_qty > 0){
						$total_working_qty = $total_working_qty + $start_qty;
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

						if($process_end_time_qc == '1'){
							$readonly = 'readonly="readonly';
						}else{
							$readonly = "";
						}

						$str .='<tr id="trid'.$row['p_id'].'">';
						$str .='<th>'.$row["l_name"].'</th>';
				 // if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$str .='<th>'.$row["sales_order_no"].'</th>';
					 // }
						$str .='<th>'.$row["work_order_no"].'</th>
								<th>'.$row["work_order_date"].'</th>
								<th>'.$row["job_card_no"].'</th>
								<th>'.$row["job_card_date"].'</th>
								<th>'.$row["description"].'</th>
								<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
								<th>'.$start_qty.' '.$row["unit_name"].'</th>
								<th>
									<select class="select2 qty_variation" id="qty_variation'.$row['p_id'].'" onChange="toggle_actual_qty_readonly(this.value,'.$row['p_id'].')">
										<option value="0"> NO </option> 
										<option value="1"> YES </option> 
									</Select>
								</th>
								<th>
									<select class="jobcard_close" readonly="readonly" id="jobcard_close'.$row['p_id'].'">
										<option value="0"> NO </option> 
										<option value="1"> YES </option> 
									</Select>
								</th>
								<th><input '.$readonly.'type="number" class="form-control start_qty" name="end_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$start_qty.'" id="end_qty'.$row['p_id'].'" value="" onkeyup="check_start_validation('.$row['p_id'].');" />
								 '.$row["unit_name"].'
								</th>';

								
								$str.= '<th><input readonly type="number" class="form-control actual_qty" name="actual_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-accept_qty="" id="actual_qty'.$row['p_id'].'" value="0"  onkeyup="calculate_accept_qty('.$row['p_id'].');"/>
								 '.$row["unit_name"].'
								</th>';

							if($process_end_time_qc == '1'){	
								$str.= '<th><input '.$readonly.'type="number" class="form-control accept_qty" name="accept_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-accept_qty="" id="accept_qty'.$row['p_id'].'" value="0" />
								 '.$row["unit_name"].'
								</th>

								<th><input '.$readonly.'type="number" class="form-control reject_qty" name="reject_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-reject_qty="" id="reject_qty'.$row['p_id'].'" value="0" />
								 '.$row["unit_name"].'
								</th>

								<th><input '.$readonly.'type="number" class="form-control reprocess_qty" name="reprocess_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-reprocess_qty="" id="reprocess_qty'.$row['p_id'].'" value="0" />
								 '.$row["unit_name"].'
								</th>
								';
							}
								/*if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0'){ 
								$str .= '<th>'.$batches_data.'</th>';
							}*/
								$str .= '<!--<th class="nosort">
									<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
								</th>-->											
							</tr>';
							}
						$s++;
					}
				}

		

			$remark_req = 'data-required = "no"';
			if($getspecialConfiguration['hermattic_permission']=="1") {
				$remark_req = 'data-required = "yes"';
			}
				
			$str .='</table><input type="hidden" id="total_qc_qty" value="">';

			if($return){
					return $str;
				}else{
					echo $str;
				}	
}

function get_filters($dbcon,$pass_pid,$p_id,$type){
	$str = '<div class="col-md-12 mtop20" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Client</label>
							<div class="col-md-8 col-xs-11">
								<select class="select2" style="width:100%" id="client_id" onChange="load_process_data('.$pass_pid.','.$type.')">
									<option value=""> Select Client </option>
									'.get_customer_list_options($dbcon,$p_id,$type).'
								</select>
							</div>
						</div>	
					</div>

					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Sales Order</label>
							<div class="col-md-8 col-xs-11">
								<select class="select2" style="width:100%" id="sales_order_id" onChange="load_process_data('.$pass_pid.','.$type.')">
									<option value=""> Select Sales Order </option>
									'.get_so_list_options($dbcon,$p_id,$type).'
								</select>
							</div>
						</div>	
					</div>
					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Jobcard No</label>
							<div class="col-md-8 col-xs-11">
								<select class="select2" style="width:100%" id="job_card_no" onChange="load_process_data('.$pass_pid.','.$type.')">
									<option value=""> Select Jobcard No </option>
									'.get_job_card_no_list_options($dbcon,$p_id,$type).'
								</select>
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">User</label>
							<div class="col-md-8 col-xs-11">
								<select class="select2" id="usertype_id" name="usertype_id" onchange="load_user_menu(this.value,null)">
									  			<option value="">SELECT USER NAME</option>
												' . getalluser($dbcon,$_SESSION['user_id']) .'
									  		</select>
							</div>
						</div>	
					</div>

					</div>';
		return $str;
}



function get_customer_list_options($dbcon,$p_id,$type){
	$query1 = "select ap.p_id,so.cust_id,cust.l_name from tbl_allocate_process as ap
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.")";

	$result1=$dbcon->query($query1);
	
	$str="";
	while($row=brp_mysqli_fetch_array($result1)){
		if($type == 1){
			$start_qty=production_store_wise_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
		}else{
			$start_qty=production_end_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
		}
		
		if($start_qty > 0){
			if(!empty($row['l_name'])){
				$str .= "<option value='".$row['cust_id']."'>".$row['l_name']."</option>";
			}
		}
	}

	return $str;
}

function get_so_list_options($dbcon,$p_id,$type){
	$query1 = "select ap.p_id,so.sales_order_id,so.sales_order_no from tbl_allocate_process as ap
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.")";

	$result1=$dbcon->query($query1);
	
	$str="";
	while($row=brp_mysqli_fetch_array($result1)){
		if($type == 1){
			$start_qty=production_store_wise_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
		}else{
			$start_qty=production_end_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	

		}
		if($start_qty > 0){
			if(!empty($row['sales_order_no'])){
				$str .= "<option value='".$row['sales_order_id']."'>".$row['sales_order_no']."</option>";
			}
		}
	}

	return $str;
}	

function get_job_card_no_list_options($dbcon,$p_id,$type,$where=""){
	$query1 = "select ap.p_id,req.job_card_no from tbl_allocate_process as ap
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.") ".$where;

	$result1=$dbcon->query($query1);
	
	$str="";
	while($row=brp_mysqli_fetch_array($result1)){
		if($type == 1){
			$start_qty=production_store_wise_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
		}else{
			$start_qty=production_end_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
		}
		if($start_qty > 0){
			if(!empty($row['job_card_no'])){
				$str .= "<option value='".$row['p_id']."'>".$row['job_card_no']."</option>";
			}
		}
	}

	return $str;
}	


function get_end_process_total_qty_data($dbcon,$p_id,$return = 0,$where=""){

	/*if(!$return){
		$p_id = $_POST['p_id'];
	}*/
	
	$query1 = "select ap.description, ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id,smain.sales_order_no,cust.l_name  from tbl_allocate_process as ap
							left join product_mst as p on p.product_id=ap.p_product_id 
							left join tbl_request_product req on req.rp_id=ap.p_ref_id
							left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
							left join unit_mst as umst on umst.unitid=ap.process_unit
							left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
							where ap.p_id in (".$p_id.") ".$where ;

	$result1=$dbcon->query($query1);
	
	
	$start_qty=0;
	$s=1;
	$total_working_qty = 0;
	$unit_name = "";
	if(brp_mysqli_num_rows($result1) == 0){
		$str .= "<tr style='text-align:center;'><td colspan='10'>No data found for this selection</td></tr>";
	}else{
		while($row=brp_mysqli_fetch_array($result1)){
			$unit_name = $row['unit_name'];
			$start_qty=production_end_count_using_p_id($dbcon,$row['p_id']);
			if($start_qty > 0){
				$total_working_qty = $total_working_qty + $start_qty;
			}
				$s++;
		}
	}

	$html .='<table class="display table table-bordered table-striped" id="">
			<tr>
				<th>Total Working Qty</th>
				<th>Total End Qty</th>
				<th>Total Accept Qty</th>
				<th>Total Reject Qty</th>
				<th>Total Reprocess Qty</th>
				<!--<th>Action</th> -->
			</tr>';	
	$html.='<tr>
				<th>
					<strong> '.$total_working_qty.' '.$unit_name.' </strong>
					<input type="hidden" id="total_qty_main" name="total_qty_main" value="'.$total_working_qty.'">
				</th>
				<th><input type="number" class="form-control total_end_qty" name="total_end_qty" id="total_end_qty" data-total_qty="'.$total_working_qty.'" value="'.$total_working_qty.'" />
						 '.$unit_name.'</th>
				<th><input type="number" class="form-control total_accept_qty" name="total_accept_qty" id="total_accept_qty" value="" />
						 '.$unit_name.'</th>
				<th><input type="number" class="form-control total_reject_qty" name="total_reject_qty" id="total_reject_qty" value="" />
						 '.$unit_name.'</th>
				<th><input type="number" id="total_reprocess_qty" class="form-control total_reprocess_qty" name="total_reprocess_qty" value="" />
						 '.$unit_name.'</th>
				<!-- <th><button class="btn btn-success"><i class="fa fa-plus"></i> Add</button></th>  -->
			</tr>';

	$html .= "</table>";
		
	if($return){
		return $html;
	}else{
		echo $html;
	}	
}


function get_bifurcation_data($dbcon,$type,$p_id,$where){
	$func = "get_jobcard_qty(this.value,'".$type."');";
	$margin = "";
	if($type == "accept"){
		$col = "col-md-4";
	}else{
		$col = "col-md-5";
		 $margin = ' style="margin-top:10px;"';
	}

	if($type == "reprocess"){
		$func .= "get_jobcard_process_list(this.value);";
	}

	$str = '<div class="col-md-12" style="margin-top:10px;">
				<div class="col-md-4">
					<label class="col-md-4 text-right">Jobcard No : </label>
					<div class="col-md-8">
						<select class="form-control select_jobcard" id="p_id_'.$type.'" onchange="'.$func.'">
						<option value=""> Select Jobcard</option>
						'.get_job_card_no_list_options($dbcon,$p_id,$type,$where).'
						</select>
					</div>
				</div>
				<div class="col-md-3">
					<label class="col-md-4 text-right">Qty : </label>
					<div class="col-md-8">
						<input type="number" class="form-control" id="p_id_'.$type.'_qty" value="">
						
						<label style="color:red; font-weight: bold;font-size: 14px;"> Working Qty : <span id="pending_'.$type.'_qty">0</span></label>
					</div>
				</div>
				<div class="'.$col.'">
					<label class="col-md-4 text-right">Reason : </label>
					<div class="col-md-8">
						<textarea rows="3" class="form-control" id="p_id_'.$type.'_reason"></textarea>
					</div>
				</div>';

				if($type == "reject"){
					$str .='<div class="col-md-4" '.$margin.'>
						<label class="col-md-4 text-right">New Product : </label>
						<div class="col-md-8">
							<input id="new_product_'.$type.'" class="new_product_reject" name="new_product_'.$type.'" placeholder="Select product" onchange="load_product_unit(this.value)"/>
						</div>
					</div>';

					$str .='<div class="col-md-3" '.$margin.'>
						<label class="col-md-6 text-right">Product Unit : </label>
						<div class="col-md-6">
							<select class="select2 form-control" name="new_unit_id" id="new_unit_id" placeholder="Select Unit" title="Select Unit" style="width:100%">
    					</select>
						</div>
					</div>';

				}

				if($type == "reprocess"){
					$str .='<div class="col-md-6" '.$margin.'>
						<label class="col-md-4 text-right">New Process : </label>
						<div class="col-md-8">
							<select class="form-control select_jobcard" id="new_process_id">
							</select>
						</div>
					</div>';	
				}

				$str .='<div class="col-md-4" '.$margin.'>
						<label class="col-md-4 text-right">Godown : </label>
						<div class="col-md-8">
							<select class="form-control" id="new_godown_id_'.$type.'"  name="new_godown_id_'.$type.'" required >
							<option value="">Select Godown</option>
									'.get_all_godown($dbcon,'',1).'
								</select>
						</div>
					</div>';

				$str .= '<div class="col-md-1" '.$margin.'>
					<button class="btn btn-success" onClick="add_temp_qc_field(\''.$type.'\')">Add</button>
				</div>
			</div>';

	return $str;
}

function show_raw_material_data($dbcon,$pid,$working_qty,$stop_qty){
			
			$string="";
			$pending_qty = $working_qty - $stop_qty;
			// $sel_p_id  = implode(",",$pid);
			$sel_p_id  = $pid;
			// var_dump($pid);
			// var_dump($stop_qty);
			$query1= "select GROUP_CONCAT(ap.p_id) as p_id,ap.extra_stock,ap.p_product_id as product_id,ap.previous_process_id,p.product_name,umst.unit_name,GROUP_CONCAT(req.rp_id) as req_id, cmst.unit_name as conv_unit_name,req.req_qty_one, p.batch_wise_stock_manage,ap.process_unit, req.purchase_unit from tbl_allocate_process as ap 
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_request_product req on req.rp_id=ap.p_ref_id 
			left join unit_mst as umst on umst.unitid=ap.process_unit 
			left join unit_mst as cmst on cmst.unitid=req.purchase_unit
			where ap.p_id in (".$sel_p_id.")" ;
			
			$result1=$dbcon->query($query1);
			
			$string .='
				<div class="col-md-12 text-center mtop20  bg-primary" style="margin-bottom:10px">
					<h3 style="color: white;font-weight: 500;padding:3px;">Material List</h3>	
				</div>';
			$x=0;
			$arr_total = array();
			$cnt=brp_mysqli_num_rows($result1);
			$row_ext=brp_mysqli_fetch_array($result1);
			$result1=$dbcon->query($query1);
			if($cnt > 0){
				$string .='<div class="col-md-12">
					<table class="display table table-bordered table-striped" id="">
					<tr>';
					// if($getspecialConfiguration['hermattic_permission']=="1") {
					 
					 // }
						$string .='<th width="5%">Sr.No.</th>
						<th width="20%">Product Name</th>
						<th width="20%">Godown</th>
						<!-- <th>Unit</th> -->
						<th width="15%">Total Material Qty</th>
						<th width="20%">Total Used Qty</th>';
						if($row_ext['extra_stock'] == '0'){
							$string .='<th width="5%">Batch Wise</th>';
						}
						if($pending_qty <= 0){
							$string .='<th width="20%">Pending Stock Godown Planning</th>';
						}
						$string .='</tr>
						<tbody>
						';
			}
			while($row=brp_mysqli_fetch_array($result1)){
				/*$product_name = $row['product_name'];
				$unitname = $row['unit_name'];*/
				if($row['previous_process_id'] == "0"){
					
				$query2 = "select GROUP_CONCAT(rp_id) as rp_id,GROUP_CONCAT(rp_pid) as product_id,p.product_name,umst.unit_name as unit_name,cmst.unit_name as conv_unit_name,trp.req_qty_one,p.batch_wise_stock_manage,trp.process_unit, trp.purchase_unit from tbl_request_product as trp 
						 left join product_mst as p on p.product_id=trp.rp_pid 
						 left join unit_mst as umst on umst.unitid=trp.process_unit 
						 left join unit_mst as cmst on cmst.unitid=trp.purchase_unit 
						 where trp.status !=2 and trp.perent_id in(". $row['req_id'].") group by rp_pid order by rp_id";

					$result2=$dbcon->query($query2);
					
					$x = 1;
					while($row2=brp_mysqli_fetch_array($result2)){

						 $mt_qry = "select sum(mtr.base_qty) AS base_qty,sum(mtr.conv_qty) as conv_qty, (sum(mtr.base_qty)-sum(used_base_qty)) as total_release_qty,(sum(mtr.conv_qty)-sum(used_conv_qty)) as total_release_conv_qty,gd.gd_name,mtr.to_godown_id from tbl_material_release_trn as mtr left join mst_godown as gd on gd.gd_id=mtr.to_godown_id where mtr.release_status = 1 and mtr.status = 0 and mtr.p_id in(". $row['p_id'] . ") and mtr.product_id in(" . $row2['product_id'].")";
						// echo "</br></br>";
						$mt_res = $dbcon->query($mt_qry);
						$mt_row = brp_mysqli_fetch_assoc($mt_res);
						$product_name = $row2['product_name'];
						$unitname = $row2['unit_name'];
						$conv_unitname = $row2['conv_unit_name'];

						$calc_used_base_qty = $stop_qty * $row2['req_qty_one'];
						if($row['extra_stock'] == '1'){
							$calc_used_conv_qty = convert_stock($dbcon,$calc_used_base_qty,$row2['product_id'],"conv_unit");	
							$mt_row['total_release_qty'] = $calc_used_base_qty;
							$mt_row['total_release_conv_qty'] = $calc_used_conv_qty;
						}else{
							$calc_used_conv_qty = ($calc_used_base_qty/$mt_row['base_qty']) * $mt_row['conv_qty'];
						}


						$string .= "<tr>";
						$string .= "<td>".$x."</td>";
						$string .= "<td>".$product_name."</td>";
						$string .= "<td>".$mt_row['gd_name']."</td>";
						// $string .= "<td>".$unitname."</td>";
						$string .= "<td> <strong>".round_up($mt_row['total_release_qty'],5). " ". $unitname ."</br>".round_up($mt_row['total_release_conv_qty'],5). " ". $row2['conv_unit_name']  ."</strong></td>";
						$string .= "<td>
							<div class='col-md-10'>
							<input type='number' id ='total_used_qty".$x."' class='numbersOnly form-control used_material_qty' data-material_qty=".round_up($mt_row['total_release_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row2['product_id']."' data-request_id = '".$row2['rp_id']."' onkeyup='convert_qty(1,".$x.",".$row2['product_id'].")' data-process_stock='0' data-unit_id='".$row2['process_unit']."' data-conv_unit_id='".$row2['purchase_unit']."'  value='".round_up($calc_used_base_qty,5)."';/>
							</div>
							<div class='col-md-2'>
							<strong> ".$unitname."</strong>
							</div>
							<div class='col-md-10' style = 'margin-top:10px;'>
						<input type='number' id ='total_used_qty2_".$x."' class='numbersOnly form-control used_material_qty2' data-material_qty=".round_up($mt_row['total_release_conv_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row2['product_id']."' data-request_id = '".$row2['rp_id']."' readonly  value='".round_up($calc_used_conv_qty,5)."'/>						
						</div>
						<div class='col-md-2'  style = 'margin-top:10px;'>
						<strong> ".$conv_unitname."</strong>
							</div>
						</td>
						<input type='hidden' id='batch_wise_stock_manage".$x."' value='".$row2['batch_wise_stock_manage']."'>
						";
						if($row2['batch_wise_stock_manage'] == '1' && $row['extra_stock'] == '0'){
							$string .= "<td>
							<input type='hidden' id='total_batch_ded_qty".$x."' value='0'>
							<input type='hidden' id='total_batch_ded_conv_qty".$x."' value='0'>
							<button type='button' class='btn btn-round btn-success btn-xs' onclick='open_batch_wise_qty($x,2);' ><i class='fa fa-plus'></i></button>
							</td>";
						}else{
							if($row['extra_stock'] == '1'){
								$string .= "
								<input type='hidden' id='total_batch_ded_qty".$x."' value='".$mt_row['total_release_qty']."'>
								<input type='hidden' id='total_batch_ded_conv_qty".$x."' value='".$mt_row['total_release_conv_qty']."'>
								";	
							}else{
								$string .= "<td></td>";		
							}
						}
						if($pending_qty <= 0){
						$string .= "<td>
									<select id='return_godown_".$row['p_id']."_".$row2['product_id']."' class='return_godown'>
										<option value='1'>On Floor Godown</option>
										<option value='2'>Return To Store</option>
									</select>
								</td>";
							}
						$string .= "</tr>";
						$x++;
					}

				}else{
					
					$unitname = $row['unit_name'];

					$process=p_id_wise_find_previous_and_next_process($dbcon,$row['p_id']);
					$process_pr=json_decode($process);
					$previous_process_pid=$process_pr->previous_process_pid;

					$q = "select ap.*,pr.process_name from tbl_allocate_process as ap 
					left join process_mst as pr on pr.process_id = ap.process_id 
					where p_id in(". $previous_process_pid.")";
					$res_2=$dbcon->query($q);
					// $row_2=brp_mysqli_fetch_array($res_2);

					

					$x = 1;
					while($row_2=brp_mysqli_fetch_array($res_2)){

					
						 $mt_qry = "select sum(mtr.base_qty) AS base_qty,sum(mtr.conv_qty) as conv_qty, (sum(mtr.base_qty)-sum(used_base_qty)) as total_release_qty,(sum(mtr.conv_qty)-sum(used_conv_qty)) as total_release_conv_qty,gd.gd_name,mtr.to_godown_id from tbl_material_release_trn as mtr left join mst_godown as gd on gd.gd_id=mtr.to_godown_id where mtr.release_status = 1 and mtr.status = 0 and mtr.p_id in(". $row['p_id'] . ") and mtr.product_id in(" . $row['product_id'].")";
						// echo "</br></br>";
						$mt_res = $dbcon->query($mt_qry);
						$mt_row = brp_mysqli_fetch_assoc($mt_res);
						$product_name = $row['product_name'].' - ['. $row_2['process_name'] .']';
						$unitname = $row['unit_name'];
						$conv_unitname = $row['conv_unit_name'];
						
						$calc_used_base_qty = $stop_qty;
						$calc_used_conv_qty = convert_stock($dbcon,$calc_used_base_qty,$row['product_id'],"conv_unit");

						if($row['extra_stock'] == '1'){
							$mt_row['total_release_qty'] = $calc_used_base_qty;
							$mt_row['total_release_conv_qty'] = $calc_used_conv_qty;
						}else{
							$calc_used_conv_qty = ($calc_used_base_qty/$mt_row['base_qty']) * $mt_row['conv_qty'];
						}

						$string .= "<tr>";
						$string .= "<td>".$x."</td>";
						$string .= "<td>".$product_name."</td>";
						$string .= "<td>".$mt_row['gd_name']."</td>";
						// $string .= "<td>".$unitname."</td>";
						$string .= "<td> <strong>".round_up($mt_row['total_release_qty'],5). " ". $unitname ."</br>".round_up($mt_row['total_release_conv_qty'],5). " ". $conv_unitname ."</strong></td>";
						// $string .= "<td>".$mt_row['total_release_qty']."</td>";
						// $string .= "<td><input type='number' id ='total_used_qty".$x."' class='numbersOnly form-control used_material_qty' data-material_qty=".$mt_row['total_release_qty']." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row['product_id']."' data-request_id = '".$row['rp_id']."'/></td>";

						$string .= "<td>
							<div class='col-md-10'>
							<input type='number' id ='total_used_qty".$x."' class='numbersOnly form-control used_material_qty' data-material_qty=".round_up($mt_row['total_release_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row['product_id']."' data-request_id = '".$row['rp_id']."' onkeyup='convert_qty(1,".$x.",".$row['product_id'].")' data-process_stock='1' data-unit_id='".$row['process_unit']."' data-conv_unit_id='".$row['purchase_unit']."'  value='".round_up($calc_used_base_qty,5)."';/>
							</div>
							<div class='col-md-2'>
							<strong> ".$unitname."</strong>
							</div>
							<div class='col-md-10' style = 'margin-top:10px;'>
						<input type='number' id ='total_used_qty2_".$x."' class='numbersOnly form-control used_material_qty2' data-material_qty=".round_up($mt_row['total_release_conv_qty'],5)." data-pid='".$row['p_id']."' data-godown_id='".$mt_row['to_godown_id']."' data-product_id = '".$row['product_id']."' data-request_id = '".$row['rp_id']."' readonly value='".round_up($calc_used_conv_qty)."'/>						
						</div>
						<div class='col-md-2'  style = 'margin-top:10px;'>
						<strong> ".$conv_unitname."</strong>
							</div>
						</td>
						<input type='hidden' id='batch_wise_stock_manage".$x."' value='".$row['batch_wise_stock_manage']."'>";
						if($row['batch_wise_stock_manage'] == '1' && $row['extra_stock'] == '0'){
							$string .= "<td>
							<input type='hidden' id='total_batch_ded_qty".$x."' value='0'>
							<input type='hidden' id='total_batch_ded_conv_qty".$x."' value='0'>
							<button type='button' class='btn btn-round btn-success btn-xs' onclick='open_batch_wise_qty($x,2);' ><i class='fa fa-plus'></i></button>
							</td>";
						}else{
							if($row['extra_stock'] == '1'){
								$string .= "
								<input type='hidden' id='total_batch_ded_qty".$x."' value='".$mt_row['total_release_qty']."'>
								<input type='hidden' id='total_batch_ded_conv_qty".$x."' value='".$mt_row['total_release_conv_qty']."'>
								";	
							}else{
								$string .= "<td></td>";		
							}
							
						}


						if($pending_qty <= 0){
						$string .= "<td>
									<select id='return_godown_".$row['p_id']."_".$row['rp_pid']."' class='return_godown'>
										<option value='1'>On Floor Godown</option>
										<option value='2'>Return To Store</option>
									</select>
								</td>";
							}
						$string .= "</tr>";
						$x++;
					}
				}
				$x++;
				// echo $query2;
			} 

			if($cnt > 0){
				$string .= "</tbody></table></div>";
			}
			
			return $string;
}


?>
