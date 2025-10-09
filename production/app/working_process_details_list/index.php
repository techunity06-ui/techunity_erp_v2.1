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
				<th>Pending Qty</th>
				<th>Working Qty</th>
				<th>Priority</th>
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
			$grp = "";

			if($company_config['resource_wise_production'] == "1"){
					$grp = ",resource_id";
			}


			if($company_config['workorder_wise_production_merge'] ==1)
			{

			 $s_ql = "select GROUP_CONCAT(ap.p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,ap.p_product_id, p.product_name,tc.cat_name,p_status,sp.po_req_no as work_order_no, p.batch_wise_stock_manage,ap.batch_no,p.product_icode, dr.drawing_number, rp.priority_status  from tbl_allocate_process as ap
				
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
			left join tbl_set_main_process as sp on sp.sp_id=rp.sp_id
			where ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type'" .$whr ." group by ap.p_ref_id,rp.priority_status, ap.process_id, ap.branch_id, ap.product_version, ap.batch_no".$grp ;
		}
			else{
				$s_ql = "select GROUP_CONCAT(ap.p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,ap.p_product_id, p.product_name,tc.cat_name,p_status, p.batch_wise_stock_manage, ap.batch_no, p.product_icode, dr.drawing_number, rp.priority_status  from tbl_allocate_process as ap

				
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id		
			where ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type'" .$whr ." 
			group by rp.priority_status, ap.p_product_id,ap.branch_id,ap.product_version,ap.batch_no".$grp ;
			}
//echo $s_ql;	
			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				if($POST['type']=="1"){
					$working_qty=production_start_count_using_p_id($dbcon,$rel['allocate_id'],$is_store_approval);
					$pending_qty=total_production_pending_qty($dbcon,$rel['allocate_id']);
				}else{
					$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
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

						if($company_config['production_start_type'] ==  '1'){
								$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_process_start/'.$start_url.'" >Start <i class="fa fa-plus"></i></a>';
						}else{


								// $button='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="start_process_using_model('. "'". $url."'".','. "'". $product_name."'".')">Start <i class="fa fa-plus"></i></button>';
/*							$button='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="show_process_action_model('. "'". $url."'".','. $rel['p_product_id'] .',1)">Start <i class="fa fa-plus"></i></button>';*/

								$button='<button class="btn btn-xs btn-success" data-original-title="Start Process" data-toggle="tooltip" data-placement="top" onclick="show_process_action_model('. "'". $url."'".','. "'". $rel['p_product_id']."'".',1)">Start <i class="fa fa-plus"></i></button>';

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

/*							$button='<button class="btn btn-xs btn-danger" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="show_process_action_model('. "'". $url."'".','. "'". $rel['p_product_id']."'".',2,'.$rr.')">End <i class="fa fa-power-off"></i></button>';*/

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
							<th>'.$rel['priority_status'].'</th>
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
				$str.= '<tr><td colspan="11"> <center>No Process Found!!!!!</center></td></tr>';
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
					$working_qty=production_start_count_using_p_id($dbcon,$p_id,$is_store_approval);
				}else{
					$working_qty=production_start_count_using_p_id($dbcon,$p_id,$is_store_approval);
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
		else if(strtolower($POST['mode']) == "get_product_name") {
			$product_id = $POST['product_id'];

			$product_name = get_product_name($dbcon,$product_id);
			echo $product_name;
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
				<div class="col-md-12" style="margin-bottom: 15px;" >
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
					$html .= get_filters($dbcon,$pass_pid,$p_id,2);
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

			if($company_config['store_relese_first_process'] == '1' && $qc_paramter_info== '0' && $next_process_id > 0){
				$function = "store_confirm_msg();";
			}else{
				$function = "process_end_using_model();";
			}	
				$html .='<div class="col-md-12" style="margin-bottom: 15px;" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="End The Process" onclick="'.$function.'" />
					</center>
				</div>';
			}

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
		}else if(strtolower($POST['mode']) == "load_temp_qc_detail") {
			$where = "";
			$p_id = $POST['p_id'];
			$unit_name = getunitname($dbcon,$POST['unit_id']);
			$process_end_time_qc = $POST['process_end_time_qc'];

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
							<th>Jobcar No</th>
							<th>'.$type.' Qty</th>
							<th>'.$type.' Reason</th>
							<th>Acion</th>
						</tr>';	
				while ($row = brp_mysqli_fetch_assoc($result)) {
					$str .= '<tr>
								<th>'.$x.'</th>
								<th>'.$row['job_card_no'].'</th>
								<th>'.$row['qty'].'</th>
								<th>'.$row['remark'].' Reason</th>
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
					if($company_config['customer_show_in_production']=="1") {
						$str .='<th>Client Name</th>';
					}
							$str .='<th>Sales Order No</th>';
					$str .='
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Priority</th>
						<th>Pending Qty</th>
						<th>Working Qty</th>
						<th>Start Qty</th>';
			
			$str .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			
	 $query1 = "select ap.description, ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date, umst.unit_name, smain.sp_id as work_order_id, smain.sales_order_no, cust.l_name, doc.doc_no, req.priority_status  from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join tbl_store_order_min_max as doc on doc.order_id=smain.store_order_id
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
					
					$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);
					if($start_qty > 0){
						$str .='<tr id="trid'.$row['p_id'].'">';
					 // if($getspecialConfiguration['hermattic_permission']=="1") {
						 	
						 // }
						$doc_no = "";
						if(!empty($row['doc_no'])){
							$doc_no = "<p> Document No : " . $row['doc_no'] ." </p>";
						}
						if($company_config['customer_show_in_production']=="1") {
						 	$str .='<th>'.$row["l_name"].'</th>';
						 }
						 	$str .='<th>'.$row["sales_order_no"].'</th>';
							$str .='
								<th>'.$row["work_order_no"].$doc_no.'</th>
								<th>'.$row["work_order_date"].'</th>
								<th>'.$row["job_card_no"].'</th>
								<th>'.$row["job_card_date"].'</th>
								<th>'.$row["description"].'</th>
								<th>'.$row["priority_status"].'</th>
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
					if($company_config['customer_show_in_production']=="1") {
					$str .='<th>Client Name</th>';
				}
					// if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$str .='<th>Sales Order No</th>';
					 // }
						
					$str .='
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Priority</th>
						<th>Pending Qty</th>
						<th>Working Qty</th>
						<th>End Qty</th>';
						if($process_end_time_qc == '1'){
							$str .='<th>Accept Qty</th>
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
			$query1 = "select ap.description, ap.p_id, ap.process_id, ap.p_qty, ap.pen_qty, p.product_name, req.job_card_no, req.job_card_date, smain.po_req_no as work_order_no, smain.po_req_date as work_order_date, umst.unit_name, smain.sp_id as work_order_id, smain.sales_order_no, cust.l_name, doc.doc_no, req.priority_status  from tbl_allocate_process as ap
							left join product_mst as p on p.product_id=ap.p_product_id 
							left join tbl_request_product req on req.rp_id=ap.p_ref_id
							left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
							left join tbl_store_order_min_max as doc on doc.order_id=smain.store_order_id
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

						$doc_no = "";
						if(!empty($row['doc_no'])){
							$doc_no = "<p> Document No : " . $row['doc_no'] ." </p>";
						}

						$str .='<tr id="trid'.$row['p_id'].'">';
						if($company_config['customer_show_in_production']=="1") {
							$str .='<th>'.$row["l_name"].'</th>';
						}
				 // if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$str .='<th>'.$row["sales_order_no"].'</th>';
					 // }
						$str .='<th>'.$row["work_order_no"].$doc_no.'</th>
								<th>'.$row["work_order_date"].'</th>
								<th>'.$row["job_card_no"].'</th>
								<th>'.$row["job_card_date"].'</th>
								<th>'.$row["description"].'</th>
								<th>'.$row["priority_status"].'</th>
								<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
								<th>'.$start_qty.' '.$row["unit_name"].'</th>
								<th><input '.$readonly.'type="number" class="form-control start_qty" name="end_qty[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$start_qty.'" id="end_qty'.$row['p_id'].'" value="" onkeyup="check_start_validation();" />
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
	$str = '<div class="col-md-12" style="margin-bottom: 15px;">
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
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Operator User</label>
							<div class="col-md-8 col-xs-11">
								<select class="select2" id="usertype_id" name="usertype_id">
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
			$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
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
			$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
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
			$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);	
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
	
	$query1 = "select ap.description, ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id, smain.sales_order_no,cust.l_name, req.priority_status  from tbl_allocate_process as ap
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



?>
