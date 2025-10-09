	<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

$company_config = getCompanyConfiguration($dbcon);		
$production_pro_search = $company_config['production_pro_search'];
$pro_search=explode(",", $production_pro_search);
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}

		// $company_config = getCompanyConfiguration($dbcon);		
		$getspecialConfiguration=getspecialConfiguration($dbcon);
		
		if(brp_strtolower($POST['mode']) == "fetch_working") {
			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			$req_qty = store_request_approval_pending_count($dbcon,$process_id,1,1,1);
			// echo "req -->" . $req_qty ."</br>";
			$release_qty = store_release_count($dbcon,$process_id,1,1,1);
			// echo "rel -->" . $release_qty ."</br>";
			$str='<tbody>';
			$str.='<tr>
				
				<th>#</th>';
				 if($company_config['workorder_wise_production_merge'] ==1)
				{
					$str .='<th>Workorder No</th>';
				}
				$str .='<th>Product Name</th>
				<th>Product Category</th>';
				if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0'){ 
					$str.='<th>Batch No / Serial No</th>';
				}
				$str .='<th>Qty</th>
				<th>Pending Qty</th>
				<th>Pending Request Qty</th>
				<th>Priority</th>
				<!--<th>Start Time</th>
				<th>End Time</th>-->';
				if($_SESSION['branch_id']==0){
					$str.='<th>Branch Name</th>';
				}
				// $str.='<th>Material Status</th>';
				$str.='<th>Action</th>
				
			</tr>';
			if(!empty($POST['product_id'])){
				$Product_filter=" and ap.p_product_id=".$POST['product_id'];
			}

			$whr = ""; 
			if($company_config['batch_wise_stock'] == '1'){
				$whr= " and ((ap.batch_no ='' and ap.batch_process_start_time = 0) OR (ap.batch_process_start_time = 1 AND ap.batch_no != ''))";
			} 

			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$check_branch = check_branch('ap', $branch_id);
			
			if($company_config['workorder_wise_production_merge'] ==1)
			{
			

				 $s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status,p.batch_wise_stock_manage,ap.batch_no, p.product_icode, dr.drawing_number,smain.po_req_no as work_order_no, ap.previous_process_id, p.product_id, ap.process_id, rp.priority_status, ap.process_unit,ap.branch_id from tbl_allocate_process as ap
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
				left join tbl_category as tc on p.product_category=tc.cat_id
				left join branch_mst as branch on branch.branch_id=ap.branch_id
				left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
				left join tbl_set_main_process as smain on smain.sp_id=rp.sp_id	
				where ap.extra_stock = 0 and ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id'].$whr." and ap.p_status IN(0,1) and pr_process_type='$process_type' group by ap.p_ref_id,rp.priority_status,ap.process_id,ap.batch_no,ap.extra_stock";
			}else{

				$s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status,p.batch_wise_stock_manage,ap.batch_no, p.product_icode, dr.drawing_number, ap.previous_process_id,p.product_id,ap.process_id, ap.process_unit, ap.branch_id, rp.priority_status  from tbl_allocate_process as ap
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
				left join tbl_category as tc on p.product_category=tc.cat_id
				left join branch_mst as branch on branch.branch_id=ap.branch_id
				left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
				where ap.extra_stock = 0 and ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id'].$whr." and ap.p_status IN(0,1) and pr_process_type='$process_type' group by ap.p_ref_id,ap.p_product_id, rp.priority_status, ap.branch_id, ap.product_version, ap.batch_no,ap.extra_stock";

			}

// echo $s_ql;
			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				$pre_process_id = 0;
				if($rel['previous_process_id']!="0" && $rel['previous_process_id'] > 0){
					$apqry = "select process_id from tbl_allocate_process where p_id = " .  $rel['previous_process_id'];
					$apres = $dbcon->query($apqry);
					$aprw = brp_mysqli_fetch_array($apres);
					$pre_process_id  = $aprw['process_id'];
				}
				
				$matirial_available_qty = 0;
				if($POST['type']=="1"){
					
					$working_qty=store_release_material_count_store_wise($dbcon,$rel['allocate_id'],1,'store_request');

					// $working_qty=store_release_material_count_store_wise($dbcon,$rel['allocate_id'],1);

					$pending_qty=total_production_pending_qty($dbcon,$rel['allocate_id']);
				}else{
					$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
					$pending_qty=$working_qty;
				}


				 if($company_config['round_up_qty'] == '1'){
				 	$working_qty =  round($working_qty);
				 }
				 // echo "wq : ". $working_qty."</br>";
				 // echo "pen q : ". $pending_qty."</br>";
				if($pending_qty > 0 && $working_qty>0){


					$process=p_id_wise_find_previous_and_next_process($dbcon,$rel["allocate_id"]);
					$process_pr=json_decode($process);

					$previous_process_pid=$process_pr->previous_process_pid;
					$product_name = $rel['product_name'];

					// $working_qty = $rel['total_qty'] - $req_qty;
						$status="<strong style='color:red'>Not Started</strong>";
						
						$button = "";
						$batchBtn = "";
						$btn_check_material = '<button class="btn btn-xs btn-primary" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="store_request_using_model('. "'". $url."'".')"><i class="fa fa-search"></i> Check Material Status</button>';


						/*if($company_config['batch_wise_stock'] == '1' &&  $company_config['batch_process'] == '0' && $rel['batch_wise_stock_manage'] == '1' && $previous_process_pid == '0'){
							$url = $rel["allocate_id"];
							if($rel['batch_no'] == "" || $rel['batch_no'] == "0"){
								$batchBtn='<button class="btn btn-xs btn-success" data-original-title="Batch Generate" data-toggle="tooltip" data-placement="top" onclick="batch_generate_model('. "'". $url."'".','. "'". $product_name."'".')">Create Batch<i class="fa fa-plus"></i></button>';
							}else{
								
								$start_url=urlencode($rel['allocate_id']);
								$url = $rel["allocate_id"];

								if($company_config['production_start_type'] ==  '1'){
									$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_store_request/'.$start_url.'" >Request <i class="fa fa-plus"></i></a>';
								}else{
									$button='<button class="btn btn-xs btn-success" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="store_request_using_model('. "'". $url."'".')">Request <i class="fa fa-plus"></i></button>';
								}
							}

						}else{*/
							$start_url=urlencode($rel['allocate_id']);
								$url = $rel["allocate_id"];

								if($company_config['production_start_type'] ==  '1'){
									$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_store_request/'.$start_url.'" >Request <i class="fa fa-plus"></i></a>';
								}else{
									$button='<button class="btn btn-xs btn-success" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="store_request_using_model('. "'". $url."'".')">Request <i class="fa fa-plus"></i></button>';
								}
						// }
					
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';

					$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }	
					$str.='<tr>
							<th>'.$cnt.'</th>';
							 if($company_config['workorder_wise_production_merge'] ==1)
							{
								$str .='<th>'.$rel['work_order_no'].'</th>';
							}
							$str .='<th>'.$rel['product_name'].' '.$item_code.' '.$drawing_number.'</th>
							<th>'.$cat_name.'</th>';
							if($company_config['batch_wise_stock'] == '1'  && $company_config['batch_process'] == '0'){ 
								$str.='<th>'.$rel['batch_no'].'</th>';
							}
							$str.='<th>'.$rel['total_qty'].'</th>
							<th>'.$pending_qty.'</th>
							<th>'.$working_qty.'</th>
							<th>'.$rel['priority_status'].'</th>';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							
							if($rel['previous_process_id']=="0"){
							
								$btn_check_material = '<button style="margin:5px" class="btn btn-xs btn-primary" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="check_material_stock(\' '.$rel["allocate_id"].' \')"><i class="fa fa-search"></i> Check Material Status</button>';
							
								// $matirial_available_qty=check_row_material_availability($dbcon,$rel["allocate_id"],0);
							}else{
							
								$btn_check_material = '<button style="margin:5px" class="btn btn-xs btn-primary" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="check_process_stock('.$rel['process_unit'].','.$rel['branch_id'].',\' '.$rel["allocate_id"].' \','.$rel['product_id'].','.$pre_process_id.')"><i class="fa fa-search"></i> Check Material Status</button>';
							
								// $matirial_available_qty=production_process_reseve_stock($dbcon,$rel['process_unit'],$rel['branch_id'],$rel["allocate_id"],$rel['product_id'],$pre_process_id,$process_reserve_id,$process_stock_id,0);
							}

							/*if(is_numeric($matirial_available_qty) &&  $matirial_available_qty > 0){
								$str.='<th class="text-success"> <strong> Material Available </strong> </th>';
							}else{
								$str.='<th class="text-danger"><strong> Material Pending </strong></th>';
							}*/

							$str.='<th>'.$button.' ' . $batchBtn . '   '. $btn_check_material  .  ' </th>
						</tr>';
						$cnt++;
						$datacheck=1;
				}
			}
			if($datacheck!=1){
				$str.= '<tr><td colspan="10"> <center>No Process Found!!!!!</center></td></tr>';
			}
				$str.='</tbody>';

			echo $str;
		}
		else if(brp_strtolower($POST['mode']) == "store_request_using_model") {
			// $p_id= urldecode($POST['p_ids']);
			$p_id= $POST['p_ids'];
			$process_id= $POST['process_id'];
			$html="";
			
			$query="select p.product_name,pr.process_name,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,p.reorder_qty from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
			where ap.p_id in (".$p_id.")";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_production_pending_qty($dbcon,$p_id);
				$working_qty=store_release_material_count_store_wise($dbcon,$p_id,1);
							
				$req_qty = store_request_approval_pending_count($dbcon,$process_id,1,1,1);
				// echo 'req  -->'.$req_qty . "</br>";
				// echo 'pen -->'.$pending_qty . "</br>";
				// echo 'working_qty -->'.$working_qty;
				$req_pending_qty = $pending_qty - $req_qty;
				// echo 'req_pending_qty -->'.$req_pending_qty;
				$req_pending_qty = $pending_qty;

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
								'.$req_pending_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Request Qty *</label>
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
			
			$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<table class="display table table-bordered table-striped" id="">
					<tr>';
					if($getspecialConfiguration['hermattic_permission']=="1") {
					 	//$html .='<th>Sales Order No</th>';
					 }

					 if($company_config['customer_show_in_production']=="1") {
					 	$html .='<th>Client Name</th>';
					 }
						
					$html .='
					
							<th>Sales Order No</th>
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						
						<th>Pending Qty</th>
						<th>Pending Request Qty</th>
						<th>Request Qty</th>
						<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->			
						<th>Priority</th>							
					</tr>';
			
			 $query1 = "select ap.description,ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name, req.priority_status,	(select if(sum(base_qty), sum(base_qty),0) as total_req_qty from tbl_store_request 			
		where p_id= ap.p_id and store_request_status = 0) as total_req_qty,smain.sales_order_no, cust.l_name
			 from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.")" ;

			$result1=$dbcon->query($query1);
			$start_qty=0;
			$s=1;
			while($row=brp_mysqli_fetch_array($result1)){
				$start_qty=store_release_material_count_store_wise($dbcon,$row['p_id'],1);
				// $r_qty = $row['total_req_qty'];
				// echo "-->" . $start_qty;	
				// $start_qty =  $start_qty - $r_qty;

				if($company_config['round_up_qty'] == '1'){
				 	$start_qty =  round($start_qty);
				 }
				if($start_qty > 0){
				$html .='<tr id="trid'.$row['p_id'].'">';
				 // if($getspecialConfiguration['hermattic_permission']=="1") {
				 if($company_config['customer_show_in_production']=="1") {
					$html .='<th>'.$row["l_name"].'</th>';
				}
						 	
					 	$html .='<th>'.$row["sales_order_no"].'</th>';
					 // }
						$html .='
							<th>'.$row["work_order_no"].'</th>
							<th>'.$row["work_order_date"].'</th>
							<th>'.$row["job_card_no"].'</th>
							<th>'.$row["job_card_date"].'</th>
							<th>'.$row['description'].'</th>
							
							<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
							<th>'.$start_qty.' '.$row["unit_name"].'</th>
							<th><input type="text" class="form-control start_qty" name="start_qty1[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-rp_id="'.$row['p_ref_id'].'" data-start_qty="'.$start_qty.'" id="start_qty1'.$row['p_id'].'" value="" onkeyup="check_start_validation();" />
							 '.$row["unit_name"].'
							</th>
							
							<!--<th class="nosort">
								<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
							</th>-->	
							<th>'.$row['priority_status'].'</th>										
						</tr>';
					}
				$s++;
			} 
			// $colspan = '7';
			// if($getspecialConfiguration['hermattic_permission']=="1") {
			 if($company_config['customer_show_in_production']=="1") {
				$colspan = '9';
			}else{
				 $colspan = '8';
			}
			// }

			$html .='<tr>
						<td colspan="'.$colspan.'" class="text-right"><b>Total Request Qty</b></td>
						<td><input type="text" name="total_req_qty" id="total_req_qty" class="form-control" value="" readonly /> </td>
					</tr>';
			$html .='</table>
			</div>';
			
			
			$html .='<input type="hidden" name="mode" id="mode" value="add_store_request_using_model" />
			<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
			<input type="hidden" name="max_available_qty" id="max_available_qty" value="'.$working_qty.'" />
			<input type="hidden" id="pending_qty" name="pending_qty" value="'.$req_pending_qty.'">
			<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
			<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
			<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$select_branch_id.'" />
			<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel["p_product_id"].'" />
			<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />
			<input type="hidden" name="reorder_qty" id="reorder_qty" value="'.$rel["reorder_qty"].'" />
			<input type="hidden" name="product_version" id="product_version" value="'.$rel["product_version"].'" />';
			
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" style="display:none" name="submit" class="btn btn-danger" value="Next" onclick="next_page();" />
					</center>
				</div>';
			
			echo $html;
			
		}else if(brp_strtolower($POST['mode']) == "get_store_request_material_data") {
			$p_id= urldecode($POST['p_ids']);
			$pid=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];
			$html="";

			$sel_p_id  = implode(",",$pid);
			

			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,ap.previous_process_id,p.product_name,ap.p_product_id, req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,req.rp_id as req_id, pr.process_name,cunit.unit_name as conv_unit_name,p.product_base_unit,p.product_conv_unit  from tbl_allocate_process as ap

						left join product_mst as p on p.product_id=ap.p_product_id 
						left join process_mst as pr on pr.process_id=ap.process_id
						left join tbl_request_product req on req.rp_id=ap.p_ref_id
						left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
						left join unit_mst as umst on umst.unitid=ap.process_unit
						left join unit_mst as cunit on cunit.unitid=req.purchase_unit
						where ap.p_id in (".$sel_p_id.")" ;
						// echo $query1;
			$result1=$dbcon->query($query1);
			
			$html .='
				<div class="col-md-12 text-center">
					<h2>Material List</h2>	
				</div>';
	$x=0;
	$arr_total = array();
	$cnt=brp_mysqli_num_rows($result1);
			while($row=brp_mysqli_fetch_array($result1)){

				$html .='<div class="col-md-12 bg-primary" style="margin-top:20px;">
					<div class="col-md-6" style="margin-top:8px;">
						<label class="col-md-6 text-right control-label" style="color: white;font-weight: 600;">Work Order No : </label>
						<div class="col-md-6 col-xs-11" style="color: white;font-weight: 600;" >
							'.$row["work_order_no"].'
						</div>
					</div>
					<div class="col-md-6" style="margin-top:8px;">
						<label class="col-md-6 text-right  control-label" style="color: white;font-weight: 600;"> Process Name : </label>
						<div class="col-md-6 col-xs-11" style="color: white;font-weight: 600;">
							'.$row["process_name"].'
						</div>
					</div>
				</div>';

				if($row['previous_process_id'] == "0"){

							$query2 = "select trp.*,p.product_name,bunit.unit_name ,cunit.unit_name as conv_unit_name,p.product_base_unit,p.product_conv_unit from tbl_request_product as trp 
					left join product_mst as p on p.product_id=trp.rp_pid 
					left join tbl_allocate_process as ap on trp.rp_id=ap.p_ref_id 
					left join unit_mst as bunit on bunit.unitid=trp.process_unit
					left join unit_mst as cunit on cunit.unitid=trp.purchase_unit
					where  trp.status !=2 and trp.perent_id = " . $row['req_id'] . " group by rp_id";

					$req_qty = $pid_wise_start_qty[$x];
					//var_dump($req_qty);
				$result2=$dbcon->query($query2);
				$unitname = "";
				$conv_unitname = "";
				$qty_total = 0;
				$conv_qty_total = 0;
				while($row2=brp_mysqli_fetch_array($result2)){
					$product_name = $row2['product_name'];
					
					$unitname = $row2['unit_name'];
					
					
					$total_qty = 0; 
					if (array_key_exists($product_name,$arr_total)){
						$total_qty = $arr_total[$product_name ];
					}
					

						$o_qty=convert_stock($dbcon,$row2["req_qty_one"],$row2['rp_pid'],"base_unit");
						// $row2["req_qty_one"]=round($row2["req_qty_one"],6);
						
						$o_qty=round($o_qty,6);
						
						//$total_req_qty=$req_qty*$o_qty;
						$total_req_qty=$req_qty*$row2["req_qty_one"];
						$total_req_qty=round($total_req_qty,5);
						//$used_qty=$req_qty*$o_qty;
						$used_qty=$req_qty*$row2["req_qty_one"];

						// $used_qty=round($used_qty,4);
						$used_qty = round_up($used_qty,5);
						// if($row2['product_base_unit'] != $row2['product_conv_unit']){
							$conv_unitname = $row2['conv_unit_name'];
							$c_used_qty=convert_stock($dbcon,$used_qty,$row2['rp_pid'],'conv_unit');
						// }
						$c_used_qty = round_up($c_used_qty,5);
						

				
					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$row2["product_name"].' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> 
								'.$used_qty. ' ' . $unitname .'</span> </label>';

								// if($row2['product_base_unit'] != $row2['product_conv_unit']){
								$html .='<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  <span style="color: #0e8400;font-weight: 600;"> 
								'.$c_used_qty. ' ' . $conv_unitname .'</span> </label>';
								// }
								
							  $html .= '</div></div>';
							  // $total_qty = $total_qty + ($req_qty * $row2['req_qty_one']);
							  $total_qty = $total_qty + $used_qty;
							  
							  // echo "tota :" . $total_qty;
							$arr_total[$product_name] = $total_qty;
				}
				
				
				}else{
					$req_qty = $pid_wise_start_qty[$x];
					
					$unitname = $row['unit_name'];


					// if($row['product_base_unit'] != $row['product_conv_unit']){
						$conv_unitname = $row['conv_unit_name'];
						$c_used_qty=convert_stock($dbcon,$req_qty,$row['p_product_id'],'conv_unit');
					// }

					$req_qty = round_up($req_qty,5);
					$c_used_qty = round_up($c_used_qty,5);
					

					$process=p_id_wise_find_previous_and_next_process($dbcon,$row['p_id']);
					$process_pr=json_decode($process);
					$previous_process_pid=$process_pr->previous_process_pid;

				$req_qty = round_up($req_qty,5);
					$c_used_qty = round_up($c_used_qty,5);


				$q = "select pr.process_name from tbl_allocate_process as ap left join process_mst as pr on pr.process_id = ap.process_id where p_id = ". $previous_process_pid;

				$res_2=$dbcon->query($q);
								
				$row_2=brp_mysqli_fetch_array($res_2);

				$product_name = $row['product_name'].' - ['. $row_2['process_name'] .']';
					// $qty_total = 0; 
					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$product_name.' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> 
								'.$req_qty. ' ' . $unitname .'</span>  </label>';

								// if($row['product_base_unit'] != $row['product_conv_unit']){
									$html .='<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  <span style="color: #0e8400;font-weight: 600;"> 
									'.$c_used_qty. ' ' . $conv_unitname .'</span> </label>';
									// }

							  $html .='</div></div>';
							  // $total_qty = $total_qty + ($req_qty * $row2['req_qty_one']);
							  $qty_total =  $qty_total + $req_qty;
							  
							  // echo "tota :" . $total_qty;
							$arr_total[$product_name] = $qty_total;
				}
				$x++;
				// echo $query2;
			} 

		/*	if($cnt > 0){
				$html .='<div class="col-md-12 bg-primary text-center control-label" style="margin-top:20px;">
				<label class="col-md-12 control-label" style="color: white;font-weight: 600; margin-top:10px;">Total Request Materials</label>
					
				</div>';
				foreach($arr_total as $key => $value){
					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$key.' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> '.$value.' ' .  $unitname.' </span>  </label>
								
							  </div></div>
							  ';
				}
			} */

			$remark_req = 'data-required = "no"';
			if($getspecialConfiguration['hermattic_permission']=="1") {
				$remark_req = 'data-required = "yes"';
			}
			
			$html .='<div class="col-md-12" style="margin-bottom: 15px;margin-top: 35px;">
						<label class="col-md-1 control-label" style="color: #404040;font-weight: 600;">Remarks </label>
						<div class="col-md-6 col-xs-11">
								<textarea id="remark" name="remark" '.$remark_req.' class="form-control" rows="3"></textarea> 
						</div>
					</div>';
			$html .='<div class="col-md-12 text-center" style="margin:25px;">
						<input type="button" id="request_btn" name="request_btn" onclick="store_request()" class="btn btn-success" value="Request" />
						<input type="button"  style="margin-left:10px" id="back_btn" name="back" class="btn btn-danger" value="Back" onclick="previous_page();" />
					</div>';
			
			echo $html;
			
		}


		else if(strtolower($POST['mode']) == "batch_generate_model") {

			// $p_id= urldecode($POST['p_ids']);
			$p_id= $POST['p_ids'];
			$html="";
			
			$query="select p.product_name,pr.process_name,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,ap.previous_process_id from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
				
			where ap.p_id in (".$p_id.")";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_production_pending_qty($dbcon,$p_id);
				if($is_store_approval){
					$working_qty =	store_approve_process_wise_production_count($dbcon,$rel['process_id'],1,1,1);
				}else{
					$working_qty=production_start_count_using_p_id($dbcon,$p_id,$is_store_approval);
				}
				
				if($rel["previous_process_id"] == 0)
				{
					$readonly = "";
				}
				else
				{
					$readonly = "readonly='readonly'";
				}
				
				$batch_no = "";	

				if($company_config['batch_wise_stock'] == '1' && $company_config['batch_stock'] == '1' ) {
						$batch_no = get_batch_no($dbcon,$rel['p_product_id']);
						$readonly = "readonly='readonly'";
					}
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
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch Pending Qty</label>
							<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
								'.$pending_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch Qty *</label>
							<div class="col-md-4 col-xs-11">
								<input type="text" name="batch_qty" id="batch_qty" class="form-control" value="" readonly /> 
							</div>
							<div class="col-md-1" style="font-size: 18px;font-weight: 600;color: #d02424;">
								'.$rel["unit_name"].'
							</div>
						</div>
					</div>
					</div>			
					';

					$html .= '<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Batch No *</label>
							<div class="col-md-8 col-xs-11">
								<input type="text" name="batch_no" id="batch_no" class="form-control" value="'.$batch_no.'" '.$readonly.' /> 
							</div>
						</div>
						</div>
					</div>';
			
			$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<table class="display table table-bordered table-striped" id="">
					<tr>';
					// if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$html .='<th>Client Name</th>
					 	<th>Sales Order No</th>';
					 // }
						

						$html .='<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Pending Qty</th>
						<th>Working Qty</th>
						<th>Batch Qty</th>';
			
			$html .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sp_id as work_order_id,smain.sales_order_no,cust.l_name from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.")";

			$result1=$dbcon->query($query1);
			$batch_qty=0;
			$s=1;
			while($row=brp_mysqli_fetch_array($result1)){
				$batch_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval);
				if($batch_qty > 0){
					$html .='<tr id="trid'.$row['p_id'].'">';
					 // if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$html .='<th>'.$row["l_name"].'</th>';
					 	$html .='<th>'.$row["sales_order_no"].'</th>';
					 // }
						$html .='<th>'.$row["work_order_no"].'</th>
							<th>'.$row["work_order_date"].'</th>
							<th>'.$row["job_card_no"].'</th>
							<th>'.$row["job_card_date"].'</th>
							
							<th>'.$row["pen_qty"].' '.$row["unit_name"].'</th>
							<th>'.$batch_qty.' '.$row["unit_name"].'</th>
							
							<th><input type="text" class="form-control batch_qty" name="batch_qty1[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-batch_qty="'.$batch_qty.'" id="batch_qty1'.$row['p_id'].'" value="" onkeyup="check_batch_validation();" />
							 '.$row["unit_name"].'
							</th>';
							
							$html .= '<!--<th class="nosort">
								<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
							</th>-->											
						</tr>';
					}
				$s++;
			} 
			
			$html .='</table>
			</div>
			';
			
			
			$html .='<input type="hidden" name="batch_mode" id="batch_mode" value="add_batch_using_model" />
			<input type="hidden" name="invoicetype_id" id="invoicetype_id" value="" />
			<input type="hidden" name="batch_max_available_qty" id="batch_max_available_qty" value="'.$working_qty.'" />
			<input type="hidden" id="batch_pending_qty" name="batch_pending_qty" value="'.$pending_qty.'">
			<input type="hidden" name="batch_p_id" id="batch_p_id" value="'.$p_id.'" />
			<input type="hidden" name="batch_product_base_unit" id="batch_product_base_unit" value="'.$rel["process_unit"].'" />
			<input type="hidden" name="batch_branch_id_model" id="batch_branch_id_model" value="'.$select_branch_id.'" />
			<input type="hidden" name="batch_product_id_model" id="batch_product_id_model" value="'.$rel["p_product_id"].'" />
			<input type="hidden" name="batch_process_id" id="batch_process_id" value="'.$rel["process_id"].'" />
			<input type="hidden" name="batch_product_version" id="batch_product_version" value="'.$rel["product_version"].'" />';
			
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Generate Batch" onclick="generate_batch_using_model();" />
					</center>
				</div>';
			
			echo $html;
			
		}


		else if(strtolower($POST['mode']) == "add_batch_using_model") {

			// echo "<pre>";
			// print_r($POST);die;
			
			$branch_id=$POST['branch_id'];
			$start_qty=$POST['batch_qty'];
			
			$pid=$POST['pid'];
			$pid_wise_batch_qty=$POST['pid_wise_batch_qty'];
			$batch_no = $POST['batch_manu_no'];
			
			$work_order_no  = $POST['work_order_no'];
			$work_order_id = $POST['work_order_id'];


			if($company_config['batch_stock'] == '1'){
				$batch_no = get_batch_no($dbcon,$POST['product_id']);
			}
		
			for($i=0;$i<count($pid);$i++)
			{				
					
				$qry = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $pid[$i];
				$res = $dbcon->query($qry);
				$cnt=brp_mysqli_num_rows($res);
				$result= brp_mysqli_fetch_assoc($res);
				if($result['p_status'] == '0'){ // process not start and tbl_allocate_process table entry done.
					
					$batch_qty = $pid_wise_batch_qty[$i];
							$arr_allocate_process = [];
							foreach($result as $key => $value){
								$arr_allocate_process[$key] = $value;
							}

							$p_id = $arr_allocate_process['p_id'];

							unset($arr_allocate_process['p_id']);
			
							$p_qty = $result['p_qty'];
							$pen_qty = $result['pen_qty'];
							$start_qty = $result['start_qty'];


							$update_info['p_qty'] = $batch_qty;
							$update_info['pen_qty'] = $batch_qty;
							$update_info['batch_no'] = $batch_no;
							

							$arr_allocate_process['p_qty'] = $p_qty - $batch_qty;
							$arr_allocate_process['pen_qty'] = $pen_qty -  $batch_qty;
							$arr_allocate_process['start_qty'] = 0;
							$arr_allocate_process['p_status'] = 0;	
							$arr_allocate_process['task_status'] = 0;	
							$arr_allocate_process['batch_no'] = "";	


							$updateid=update_record('tbl_allocate_process', $update_info,"p_id=".$p_id , $dbcon);
							if($updateid){
								update_batch_no($dbcon,$POST['product_id']);
							}
							if($arr_allocate_process['p_qty'] > 0){
								$new_p_id=add_record('tbl_allocate_process', $arr_allocate_process, $dbcon);

								if($result['previous_process_id'] == '0'){
									update_reserve_stock($p_id,$dbcon,$new_p_id);
								}else{
									// update_process_reverse_stock($p_id,$dbcon,$new_p_id);
								}
							}
						}
				
			}
			echo "1";
		}else if(brp_strtolower($POST['mode']) == "check_material_stock") {
			$p_id = $POST['p_id'];
			$matirial_available_qty=check_row_material_availability($dbcon,$p_id,0);

			if(is_numeric($matirial_available_qty) &&  $matirial_available_qty > 0){
				echo '1';
			}else{
				echo '0';
			}

		}else if(brp_strtolower($POST['mode']) == "check_process_stock") {
			$p_id = $POST['p_id'];
			$unit_id = $POST['unit_id'];
			$branch_id = $POST['branch_id'];
			$product_id = $POST['product_id'];
			$process_id = $POST['process_id'];

			$matirial_available_qty=production_process_reseve_stock($dbcon,$unit_id,$branch_id,$p_id,$product_id,$process_id);

			if(is_numeric($matirial_available_qty) &&  $matirial_available_qty > 0){
				echo '1';
			}else{
				echo '0';
			}

		}
		
function update_reserve_stock($p_id,$dbcon,$new_p_id){

	$qry2 = "SELECT *,(select if(sum(base_stock), sum(base_stock),0) from tbl_reserve_stock where stock_status = 0 and p_id = " . $p_id." and stock_flage = 1) as base_stock_plus,(select if(sum(convert_stock), sum(convert_stock),0) from tbl_reserve_stock where stock_status = 0 and p_id = " . $p_id." and stock_flage = 1) as convert_stock_plus,(select if(sum(base_stock), sum(base_stock),0) from tbl_reserve_stock where stock_status = 0 and p_id = " . $p_id." and stock_flage = 2) as base_stock_minus,(select if(sum(convert_stock), sum(convert_stock),0)  from tbl_reserve_stock where stock_status = 0 and p_id = " . $p_id." and stock_flage = 2) as convert_stock_minus FROM tbl_reserve_stock WHERE stock_flage = 1 and stock_status = 0 and p_id = " . $p_id . " group by product_id";
		$res2 = $dbcon->query($qry2);
		$cnt2=brp_mysqli_num_rows($res2);
		

		if($cnt2 > 0){

			while($result2= brp_mysqli_fetch_assoc($res2))
			{


			$total_stock = $result2['base_stock_plus'] - $result2['base_stock_minus'];
			$total_convert_stock = $result2['convert_stock_plus'] - $result2['convert_stock_minus'];

			
			$qry5 = "SELECT *,product_base_qty FROM tbl_request_product trp left join product_mst mst on mst.product_id = trp.rp_pid WHERE rp_id = ". $result2['request_id'];


			$res5 = $dbcon->query($qry5);
			$cnt5=brp_mysqli_num_rows($res5);
			$result5= brp_mysqli_fetch_assoc($res5);
	
		
			$qry4 = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $p_id;
			$res4 = $dbcon->query($qry4);
			$result4= brp_mysqli_fetch_assoc($res4);	

			$total_qty = 	$result5['rp_req_qty'] /  $result5['req_qty_one'];
			$qty = $result4['pen_qty'];
			$base_stock_per_qty = $result5['product_base_qty'];
			$req_qty_one = $result5['req_qty_one'];

	
			$update_stock['base_stock'] =  ($qty * $req_qty_one);

			$type="conv_unit";
			$c_stock=convert_stock($dbcon,$update_stock['base_stock'],$result2['product_id'],$type);
			// $update_stock['convert_stock'] = $result2['convert_stock'] - ($qty * $req_qty_one);
			$update_stock['convert_stock'] = $c_stock;
			
			$updateid=update_record('tbl_reserve_stock', $update_stock,"reserve_id=".$result2['reserve_id'], $dbcon);	
	
			$qry6 = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $new_p_id;
	
			$res6 = $dbcon->query($qry6);
			$cnt6=brp_mysqli_num_rows($res6);
			$result6= brp_mysqli_fetch_assoc($res6);			

			$arr_reserve_stock = [];

			foreach($result2 as $key => $value){
				$arr_reserve_stock[$key] = $value;
			}

			unset($arr_reserve_stock['reserve_id']);
			unset($arr_reserve_stock['base_stock_plus']);
			unset($arr_reserve_stock['convert_stock_plus']);
			unset($arr_reserve_stock['base_stock_minus']);
			unset($arr_reserve_stock['convert_stock_minus']);

			$qty = $result6['pen_qty'];
			$arr_reserve_stock['p_id'] = $new_p_id;
			$arr_reserve_stock['stock_flage'] = 1;

			$arr_reserve_stock['base_stock'] =  ($qty * $req_qty_one);

			$type="conv_unit";
			$c_stock=convert_stock($dbcon,$arr_reserve_stock['base_stock'],$result6['product_id'],$type);
			// $update_stock['convert_stock'] = $result2['convert_stock'] - ($qty * $req_qty_one);
			$arr_reserve_stock['convert_stock'] = $c_stock;


			$new_rs_id=add_record('tbl_reserve_stock', $arr_reserve_stock, $dbcon);
		}
	}
}


		
?>
