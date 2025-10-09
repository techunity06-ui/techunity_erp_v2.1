<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

$company_config = getCompanyConfiguration($dbcon);		

$getspecialConfiguration=getspecialConfiguration($dbcon);

$is_store_approval = $company_config['store_approval'];

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
			// // echo "req -->" . $req_qty ."</br>";
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
				<th>Product Category</th>
				<th>Batch No / Serial No</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Pending Request Qty</th>
				<th>Priority</th>
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
			
			if($company_config['workorder_wise_production_merge'] ==1)
				{
					
 			$s_ql = "select p_id as allocate_id,p_qty as total_qty,pen_qty as total_pending,start_qty as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status,p.batch_wise_stock_manage,ap.batch_no, p.product_icode, dr.drawing_number,sp.po_req_no as work_order_no,rp.priority_status from tbl_allocate_process as ap
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
			left join tbl_set_main_process as sp on sp.sp_id=rp.sp_id
			where ap.batch_process_start_time = 1 and ap.batch_no ='' and ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type'" ;

				}
				else
				{
			
			 $s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status,p.batch_wise_stock_manage,ap.batch_no, rp.priority_status, p.product_icode, dr.drawing_number from tbl_allocate_process as ap
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
			where ap.batch_process_start_time = 1 and ap.batch_no ='' and ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type' group by rp.priority_status, ap.p_product_id, ap.branch_id, ap.product_version, ap.batch_no,ap.extra_stock" ;
				}
			
			

			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			if(brp_mysqli_num_rows($q) > 0){
				while($rel=brp_mysqli_fetch_array($q))
				{
					if($POST['type']=="1"){
						$working_qty=store_release_material_count_store_wise($dbcon,$rel['allocate_id'],1);
						$pending_qty=total_production_pending_qty($dbcon,$rel['allocate_id']);
					}else{
						$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
						$pending_qty=$working_qty;
					}
					// echo "wq : ". $working_qty."</br>";
					// echo "pen q : ". $pending_qty."</br>";
					if($working_qty>0){

						if($company_config['round_up_qty'] == '1'){
							$working_qty = round($working_qty);
						}


						$process=p_id_wise_find_previous_and_next_process($dbcon,$rel["allocate_id"]);
						$process_pr=json_decode($process);

						$previous_process_pid=$process_pr->previous_process_pid;
						$product_name = $rel['product_name'];

						// $working_qty = $rel['total_qty'] - $req_qty;
							$status="<strong style='color:red'>Not Started</strong>";
							
							$button = "";
							$batchBtn = "";

						$url = $rel["allocate_id"];
						if($rel['batch_no'] == "" || $rel['batch_no'] == "0"){
									$batchBtn='<button class="btn btn-xs btn-success" data-original-title="Batch Generate" data-toggle="tooltip" data-placement="top" onclick="batch_generate_model('. "'". $url."'".','. "'". $product_name."'".')">Create Batch<i class="fa fa-plus"></i></button>';
						
						
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
								<th>'.$cat_name.'</th>
								<th>'.$rel['batch_no'].'</th>
								<th>'.$rel['total_qty'].'</th>
								<th>'.$pending_qty.'</th>
								<th>'.$working_qty.'</th>
								<th>'.$rel['priority_status'].'</th>';
								if($_SESSION['branch_id']==0){
									$str.='<th>'.$branch_name.'</th>';
								}
								$str.='<th>'.$button.' ' . $batchBtn . ' </th>
							</tr>';
							$cnt++;
							$datacheck=1;
					}
				}
			}
		
			
	}else{
			$str.= '<tr><td colspan="10"> <center>No Process Found!!!!!</center></td></tr>';
		}
		$str.='</tbody>';
		echo $str;
	}
	else if(strtolower($POST['mode']) == "batch_generate_model") {

			// $p_id= urldecode($POST['p_ids']);
			$p_id= $POST['p_ids'];
			$html="";
			
			$query="select p.product_name,pr.process_name,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,ap.previous_process_id,p.reorder_qty,smain.sales_order_no, cust.l_name from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
								left join tbl_set_main_process as smain on smain.sp_id=rp.sp_id	
				left join unit_mst as umst on umst.unitid=ap.process_unit

				left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
			where ap.p_id in (".$p_id.")";

				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_production_pending_qty($dbcon,$p_id);
				if($is_store_approval){

					// var_dump('store');
					// $working_qty = store_approve_process_wise_production_count($dbcon,$rel['process_id'],1,1,1);

					$working_qty =	store_request_pending_count_store_wise($dbcon,$rel['process_id'],1,1,1);

				}else{
						// var_dump('else');
					$working_qty = production_start_count_using_p_id($dbcon,$p_id,$is_store_approval,1);
				}
				
				$q_1 = "select pr_process_type,previous_process_id from tbl_allocate_process where p_id = " .$rel["previous_process_id"]; 
				$q_rel=brp_mysqli_fetch_assoc($dbcon->query($q_1));
				if($rel["previous_process_id"] == 0)
				{
					$readonly = "";
				}
				else if($q_rel['pr_process_type'] == '2' && $q_rel['previous_process_id'] == '0'){
					$readonly = "";
				}else
				{
					$readonly = "readonly='readonly'";
				}
				
				$batch_no = "";	

				if($company_config['batch_wise_stock'] == '1' && $company_config['batch_stock'] == '1' ) {
					
					 
					 	$batch_no = get_batch_no($dbcon,$rel['p_product_id']);
					 	
						$readonly = "readonly='readonly'";
					}

					if($company_config['batch_wise_stock'] == '1') {
						if($getspecialConfiguration['smpl_permission']=="1") {
						 /*$batch_no = 	get_smpl_batch_no($dbcon,$p_id);
						 $readonly = "readonly='readonly'";*/
						 $batch_no = "";
						 $readonly = "";
					 	}
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
					if($company_config['customer_show_in_production'] == '1'){
					 	$html .='<th>Client Name</th>';
					 }
							$html .='<th>Sales Order No</th>';
					 // }
						

						$html .='<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Pending Qty</th>
						<th>Working Qty</th>
						<th>Batch Qty</th>
						<th>Priority</th>';
			
			$html .= '<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date, umst.unit_name, smain.sp_id as work_order_id, smain.sales_order_no,cust.l_name, req.priority_status from tbl_allocate_process as ap

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

				$batch_qty=production_start_count_using_p_id($dbcon,$row['p_id'],$is_store_approval,1);
				if($company_config['round_up_qty'] == '1'){
						 $batch_qty = round($batch_qty);
					 }

				$batch_qty=$row['pen_qty'];

				if($batch_qty > 0){

					$html .='<tr id="trid'.$row['p_id'].'">';
					 // if($getspecialConfiguration['hermattic_permission']=="1") {
					if($company_config['customer_show_in_production'] == '1'){
					 	$html .='<th>'.$row["l_name"].'</th>';
					 }
						 	
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

							$html.='<th>'.$row['priority_status'].'</th>';
							
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
			<input type="hidden" name="reorder_qty" id="reorder_qty" value="'.$rel["reorder_qty"].'" />
			<input type="hidden" name="batch_product_version" id="batch_product_version" value="'.$rel["product_version"].'" />';
			
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Generate Batch" onclick="generate_batch_using_model();" />
					</center>
				</div>';
			
			echo $html;
			
		}


		else if(strtolower($POST['mode']) == "add_batch_using_model") {

			/*echo "<pre>";
			print_r($POST);die;
			*/
			$branch_id=$POST['branch_id'];
			$start_qty=$POST['batch_qty'];
			
			$pid=$POST['pid'];
			$pid_wise_batch_qty=$POST['pid_wise_batch_qty'];
			$batch_no = $POST['batch_manu_no'];

			$process_id = $POST['process_id'];
			$product_id = $POST['product_id'];
			
			$work_order_no  = $POST['work_order_no'];
			$work_order_id = $POST['work_order_id'];


			if($company_config['batch_stock'] == '1'){
				$batch_no = get_batch_no($dbcon,$POST['product_id']);
			}

			$is_store_approval = $company_config['store_approval'];

			$query1 = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $POST['product_id'];
			$result1=$dbcon->query($query1);
			$pro_res =brp_mysqli_fetch_array($result1);
		
			for($i=0;$i<count($pid);$i++)
			{
				$qry = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $pid[$i];
				$res = $dbcon->query($qry);
				$cnt=brp_mysqli_num_rows($res);
				$result= brp_mysqli_fetch_assoc($res);
				$extra_stock = $result['extra_stock'];

				
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
								if($result['previous_process_id'] == '0' && $result['extra_stock'] == '0'){
									update_reserve_stock($p_id,$dbcon,$new_p_id,$result['p_ref_id']);
								}else{
									// update_process_reverse_stock($p_id,$dbcon,$new_p_id);
								}
							}
						}
				
			}
			echo "1";
		}
		
function update_reserve_stock($p_id,$dbcon,$new_p_id,$rp_id){

	$q1 = "SELECT * FROM tbl_request_product WHERE status != 2 and perent_id = " . $rp_id;
	$r1 = $dbcon->query($q1);

	while($rw1 = brp_mysqli_fetch_assoc($r1)){
		$qry2 = "SELECT * FROM tbl_reserve_stock WHERE stock_flage = 1 and stock_status != 2 and p_id = " . $p_id . " and request_id =" . $rw1['rp_id'];
		$res2 = $dbcon->query($qry2);
		$cnt2=brp_mysqli_num_rows($res2);
		

		$req_qty_one = $rw1['req_qty_one'];

		if($cnt2 > 0){

			$qry6 = "SELECT * FROM  tbl_allocate_process WHERE p_id = " . $new_p_id;
	
			$res6 = $dbcon->query($qry6);
			$cnt6=brp_mysqli_num_rows($res6);
			$result6= brp_mysqli_fetch_assoc($res6);	

			$qty = $result6['pen_qty'];

			$qty = ($qty * $req_qty_one);
			while($result2= brp_mysqli_fetch_assoc($res2))
			{
				$total_stock = $result2['base_stock'];
				$total_convert_stock = $result2['convert_stock'];


				$arr_reserve_stock = [];

				foreach($result2 as $key => $value){
					$arr_reserve_stock[$key] = $value;
				}

				unset($arr_reserve_stock['reserve_id']);
				unset($arr_reserve_stock['base_stock_plus']);
				unset($arr_reserve_stock['convert_stock_plus']);
				unset($arr_reserve_stock['base_stock_minus']);
				unset($arr_reserve_stock['convert_stock_minus']);

				$arr_reserve_stock['p_id'] = $new_p_id;
				$arr_reserve_stock['stock_flage'] = 1;
				if($qty > 0){
					if($total_stock >= $qty){
						$arr_reserve_stock['base_stock'] =  $qty;
						$total_stock = $total_stock - $qty;
						$qty = $qty - $qty;
					}else{
						$arr_reserve_stock['base_stock'] =  $total_stock;
						$qty = $qty - $total_stock;
						$total_stock = $total_stock - $total_stock;
					}

					
					$type="conv_unit";
					$c_stock=convert_stock($dbcon,$arr_reserve_stock['base_stock'],$result6['product_id'],$type);

					$total_convert_stock = $total_convert_stock - $c_stock; 

					
					$arr_reserve_stock['convert_stock'] = $c_stock;
					$new_rs_id=add_record('tbl_reserve_stock', $arr_reserve_stock, $dbcon);

					$update_stock['base_stock'] = $total_stock;
					$update_stock['convert_stock'] = $total_convert_stock;
					$update_stock['stock_status'] = 0;
					if($total_stock <= 0){
						$update_stock['stock_status'] = 2;
					}
					
					$updateid=update_record('tbl_reserve_stock', $update_stock,"reserve_id=".$result2['reserve_id'], $dbcon);
			
				}
			}
		}
	}
}

function get_smpl_batch_no($dbcon,$p_id){
	$companyConfiguration=getCompanyConfiguration($dbcon);
	if($companyConfiguration) 
	{
		$smpl_batch_prefix= $companyConfiguration['smpl_batch_prefix'];
	}
	$query = "SELECT st.batch_no,rp.job_card_no from tbl_allocate_process as ap
	LEFT JOIN tbl_reserve_stock as res on res.p_id = ap.p_id
	LEFT JOIN tbl_request_product as rp on rp.rp_id = ap.p_ref_id 
	LEFT JOIN tbl_stock_trn as st on st.stock_id = res.stock_id where res.stock_status = 0 and res.stock_flage = 1 and res.p_id in(".$p_id.")";	

	$row = brp_mysqli_fetch_assoc($dbcon->query($query));
	
	$batch_no = $row['batch_no'] .'/'.$row['job_card_no'].''.$smpl_batch_prefix;

	return $batch_no;
}


/*

function release_stock_action_modal($dbcon,$p_id,$rel_qty,$rel_conv_qty,$previous_process_id,$product_id,$main_product,$request_id){
	$qry = "select product_base_unit,product_conv_unit
			 			from product_mst where product_id = " . $product_id;
	$pr_res=$dbcon->query($qry);
	$pro_res =brp_mysqli_fetch_array($pr_res);

	
	$query1 = "select *	from tbl_reserve_stock where  stock_status !=2 and stock_flage = 1 and p_id = " . $p_id . " and product_id = " . $product_id . " and request_id = " . $request_id;  /// request_id check 

	$result1=$dbcon->query($query1);

	$stock = $rel_qty;
	$stock_conv = $rel_conv_qty;

	while($res =brp_mysqli_fetch_array($result1)){
		$approve_base_stock = 0;
		$approve_convert_stock =0;

		if($res['approve_base_stock'] != "" && $res['approve_base_stock'] > 0){
			$approve_base_stock = $res['approve_base_stock'];
		} 

		if($res['approve_convert_stock'] != "" && $res['approve_convert_stock'] > 0){
			$approve_convert_stock = $res['approve_convert_stock'];
		} 


		$bstock = $res['base_stock'] - $approve_base_stock;
		$cstock = $res['convert_stock'] - $approve_convert_stock;
		if($stock > 0){
			if($bstock > 0){
				$remaining_qty = 0;
				$remaining_conv_qty = 0;
				if($bstock <= $stock){
					$remaining_qty = $bstock;
				}else{
					$remaining_qty = $stock;
				}

				$stock = $stock - $remaining_qty;

				if($pro_res['product_base_unit'] != $pro_res['product_conv_unit']){
					$remaining_conv_qty = convert_stock($dbcon,$remaining_qty,$product_id,"conv_unit");
				}else{
					$remaining_conv_qty = $remaining_qty;
				}
				$approve_base_stock = $approve_base_stock + $remaining_qty;
				$approve_convert_stock = $approve_convert_stock + $remaining_conv_qty;

				$res_stock['approve_base_stock'] = $approve_base_stock;
				$res_stock['approve_convert_stock'] = $approve_convert_stock;
				$table='tbl_reserve_stock';$tableid='reserve_id';
				update_record($table, $res_stock, $tableid."=".$res['reserve_id'], $dbcon);
			}
		}
	}
				
}*/

		
?>
