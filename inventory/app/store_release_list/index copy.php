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

		$getspecialConfiguration=getspecialConfiguration($dbcon);
		
		if(brp_strtolower($POST['mode']) == "fetch_working") {
			
			
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
					$str .='<th>Batch No</th>';
				}
				$str .='<th>Process</th>
				<th>Request Qty</th>
				<th>User</th>';
				
				if($_SESSION['branch_id']==0){
					$str.='<th>Branch Name</th>';
				}
				$str.='<th>Action</th>
				
			</tr>';
			if(!empty($POST['product_id'])){
				$Product_filter=" and tsr.rp_id=".$POST['product_id'];
			}

			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$check_branch = check_branch('tsr', $branch_id);
			
			 
			 $s_ql = "select sum(base_qty) as total_qty,sum(release_qty) as total_release_qty,branch.branch_name,p.product_name,pr.process_name,tc.cat_name, GROUP_CONCAT(tsr.p_id) AS pids,users.user_name,ap.batch_no, p.product_icode, dr.drawing_number, smain.po_req_no as work_order_no from tbl_store_request as tsr
			left join product_mst as p on p.product_id=tsr.product_id 
			left join process_mst as pr on pr.process_id=tsr.process_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=tsr.branch_id
			left join users on users.user_id = tsr.user_id
			left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			left join tbl_allocate_process as ap on tsr.p_id=ap.p_id 
			left join tbl_request_product as rp on rp.rp_id=ap.p_ref_id
			left join tbl_set_main_process as smain on smain.sp_id=rp.sp_id	
			where tsr.store_request_status = 0 ".$Product_filter." ".$check_branch." and tsr.company_id=".$_SESSION['company_id']." group by tsr.product_id,tsr.branch_id,tsr.process_id,ap.batch_no" ;

			$q=$dbcon->query($s_ql);
			// echo $s_ql;
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				$remaining_qty = $rel['total_qty'] - $rel['total_release_qty'];
				if($remaining_qty > 0){
					$start_url=urlencode($rel['pids']);
					$url = $rel["pids"];
							// $release='<a class="btn btn-xs btn-success" data-original-title="Store Release" data-toggle="tooltip" data-placement="top" title="Store Release" href="'.ROOT.INVENTORY_ROOT.'production_store_release/'.$start_url.'" >Release <i class="fa fa-plus"></i></a>';
				
					// $release = '<button class="btn btn-xs btn-success" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="">Release <i class="fa fa-rocket"></i></button>';
						$view='<button class="btn btn-xs btn-success" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="store_release_using_model('. "'". $url."'".')">Release <i class="fa fa-rocket"></i></button>';
					
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
							$str .='
							<th>'.$rel['product_name'].' '.$item_code.' '.$drawing_number.'</th>
							<th>'.$cat_name.'</th>';
							if($company_config['batch_wise_stock'] == '1' && $company_config['batch_process'] == '0'){ 
								$str .='<th>'.$rel['batch_no'].'</th>';
							}
							$str .='<th>'.$rel['process_name'].'</th>
							<th>'.$remaining_qty.'</th>
							<th>'.$rel['user_name'].'</th>';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							$str.='<th>'.$release.' '.$view.'</th>
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
		
		else if(brp_strtolower($POST['mode']) == "store_release_using_model") {
			$p_id=$POST['p_ids'];
			$html="";
			
			$query="select p.product_name,pr.process_name,ap.previous_process_id,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version,p.reorder_qty from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
			where ap.p_id in (".$p_id.") and ap.p_status in(0,1)";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_production_pending_qty($dbcon,$p_id);
				$working_qty=production_start_count_using_p_id($dbcon,$p_id,0);

				$req_qty = store_request_approval_pending_count_by_pid($dbcon,$rel['process_id'],$p_id,1,1,1);
				
				$req_pending_qty = $pending_qty - $req_qty;
				$date=date('d-m-Y');
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
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;"> Isuue No. </label>
							<div class="col-md-6 col-xs-11">
								<input class="form-control" type="text" readonly="true" name="issue_no" id="issue_no" value="'. get_issue_no($dbcon) .'">
							</div>
						</div>

						<div class="col-md-6">
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Isuue Date </label>
							<div class="col-md-6 col-xs-11" style="color: #c71313;font-weight: 600;">
								<input id="issue_date" name="issue_date" type="text" class="form-control default-date-picker required valid" title="Issue Date" placeholder="Issue Date" value="'.$date.'">
							</div>
						</div>

					</div>
					<div class="col-md-12" style="margin-bottom: 15px;">
					<div class="col-md-8">
						<div class="form-group">  	
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Total Request Qty</label>
							<div class="col-md-6 col-xs-11" style="color: #10827c;font-weight: 600;font-size: 20px;">
								'.$req_qty.' '.$rel["unit_name"].'
							</div>
						</div>	
					</div>
					</div>
					
					';
			
			$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<table class="display table table-bordered table-striped" id="">
					<tr>';
					/*if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$html .='<th>Sales Order No</th>';
					 }*/
						
					$html .='<th>Client Name</th>
							<th>Sales Order No</th>
					<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Request Qty</th>
						<th>Total Released Qty</th>
						<th>Release Pending Qty</th>
						<th>Released Qty</th>
					</tr>';
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,smain.sales_order_no, cust.l_name,
			(select if(sum(base_qty), sum(base_qty),0) as total_req_qty from tbl_store_request 			
		where p_id= ap.p_id and store_request_status = 0) as total_req_qty,
		(select if(sum(release_qty), sum(release_qty),0) as total_release_qty from tbl_store_request 		
		where p_id= ap.p_id and store_request_status = 0) as total_release_qty,smain.sales_order_no
			 from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = smain.sales_order_trn_id
					left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
					left join tbl_ledger cust on so.cust_id=cust.l_id
					where ap.p_id in (".$p_id.")" ;
// echo $query1;
			$result1=$dbcon->query($query1);
			$start_qty=0;
			$s=1;
			$unit = "";
			$total_req_qty = 0;
			while($row=brp_mysqli_fetch_array($result1)){
				$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],0);
				$remaining_qty = $row['total_req_qty'] - $row['total_release_qty'];
				if($remaining_qty > 0){
				$html .='<tr id="trid'.$row['p_id'].'">';
				$html .='<th>'.$row["l_name"].'</th>';
						
				 // if($getspecialConfiguration['hermattic_permission']=="1") {
					 	$html .='<th>'.$row["sales_order_no"].'</th>';
					 // }
						$html .='
							<th>'.$row["work_order_no"].'</th>
							<th>'.$row["work_order_date"].'</th>
							<th>'.$row["job_card_no"].'</th>
							<th>'.$row["job_card_date"].'</th>
							<th>'.$row['total_req_qty']. ' ' . $row["unit_name"].'</th>
							<th>'.$row['total_release_qty']. ' ' . $row["unit_name"].'</th>
							<th>'.$remaining_qty . ' ' . $row["unit_name"].'</th>
							<th><input type="text" class="form-control start_qty" name="start_qty1[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$remaining_qty.'" id="start_qty1'.$row['p_id'].'" value="'.$remaining_qty.'" onkeyup="check_start_validation();"/>
							 '.$row["unit_name"].'
							</th>							
						</tr>';

						$total_req_qty = $total_req_qty + $remaining_qty;
						}
						$unit = $row["unit_name"];

				$s++;
			} 
			$colspan = '7';
			// if($getspecialConfiguration['hermattic_permission']=="1") {
				$colspan = '9';
			// }

			$html .='<tr>
						<th colspan="'.$colspan.'" class="text-right"><b>Total Request Qty</b></th>
						<th><input type="text" name="total_req_qty" id="total_req_qty" class="form-control" value="'.$total_req_qty.'" readonly />  '.$unit.'</th>
					</tr>';
			$html .='</table>
			</div>';
			
			$html .='<input type="hidden" name="mode" id="mode" value="add_store_release_using_model" />
			<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
			<input type="hidden" name="max_available_qty" id="max_available_qty" value="'.$total_req_qty.'" />
			<input type="hidden" id="pending_qty" name="pending_qty" value="'.$total_req_qty.'">
			<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
			<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$select_branch_id.'" />
			<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel["p_product_id"].'" />
			<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />
			<input type="hidden" name="reorder_qty" id="reorder_qty" value="'.$rel["reorder_qty"].'" />
			<input type="hidden" name="previous_process_id" id="previous_process_id" value="'.$rel["previous_process_id"].'" />
			<input type="hidden" name="product_version" id="product_version" value="'.$rel["product_version"].'" />';
			
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-danger" value="Next" onclick="next_page();" />
					</center>
				</div>';
			
			echo $html;
			
		}else if(brp_strtolower($POST['mode']) == "load_release_no") {
			$release_no = load_common_no($dbcon,RELEASE_MATERIAL);

			echo $release_no;
		}
		else if(brp_strtolower($POST['mode']) == "get_store_request_material_data") {
			// echo "<pre>";
			// print_r($POST);
			$p_id= $POST['p_ids'];
			$pid=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];
			$html="";

			$p_ids = implode(",",$pid);
			// print_r($p_ids);
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,ap.previous_process_id,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,req.rp_id as req_id, pr.process_name,ap.process_unit,req.rp_pid,cunit.unit_name as conv_unit_name,p.product_base_unit,p.product_conv_unit, p.batch_wise_stock_manage from tbl_allocate_process as ap
						left join product_mst as p on p.product_id=ap.p_product_id 
						left join process_mst as pr on pr.process_id=ap.process_id
						left join tbl_request_product req on req.rp_id=ap.p_ref_id
						left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
						left join unit_mst as umst on umst.unitid=ap.process_unit
						left join unit_mst as cunit on cunit.unitid=req.purchase_unit
						where ap.p_id in (".$p_ids.")" ;

			$result1=$dbcon->query($query1);
			$release_no = load_common_no($dbcon,RELEASE_MATERIAL);
			$html .='
				<div class="col-md-12 text-center">
					<h2>Material List</h2>	
				</div>
				<div class="col-md-12 mtop20">
					<div class="col-md-6">
						<label class="col-md-6 text-right control-label" style="color: #404040;font-weight: 600;">Release No :</label>
						<div col-md-6>
							<input type="text" class="form-control " style="width:50%" name="release_no" id="release_no" value="'.$release_no.'" /> 
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-6 text-right control-label" style="color: #404040;font-weight: 600;">Release Date :</label>
						<div col-md-6>
							<input type="text" class="form-control " style="width:50%" name="release_date" id="release_date" value="'.date('d-m-Y').'" /> 
						</div>
					</div>
				</div>
				<div class="col-md-12 mtop20">
					<div class="col-md-6">
						<label class="col-md-6 text-right control-label" style="color: #404040;font-weight: 600;">To Godown :</label>
						<div col-md-6>
							<select class="select2" title="To Godown" id="to_godown_id" name="to_godown_id">
							 '.get_last_node_godown_list($dbcon).'
							 </select>
						</div>
					</div>
					<div class="col-md-6">
						<label class="col-md-6 text-right control-label" style="color: #404040;font-weight: 600;">To User :</label>
						<div col-md-6>
							<select class="select2" title="To User" id="to_user_id" name="to_user_id">
							 '. getalluser($dbcon).'
							 </select>
						</div>
					</div>
				</div>';
	$x=0;
	$arr_total = array();
	$cnt=brp_mysqli_num_rows($result1);
			while($row=brp_mysqli_fetch_array($result1)){
				$html .='<div class="col-md-12 bg-primary" style="margin-top:10px;">
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

				/*$query2 = "select trp.*,p.product_name from tbl_request_product as trp 
				left join product_mst as p on p.product_id=trp.rp_pid 
				left join tbl_allocate_process as ap on trp.rp_id=ap.p_ref_id 
				where trp.perent_id = " . $row['req_id'];*/

				if($row['previous_process_id'] == "0"){

		  $query2 = "select ap.p_id,res.godown_id,trp.*,p.product_name,p.batch_wise_stock_manage,bunit.unit_name,gd.gd_name,cunit.unit_name as conv_unit_name,p.product_base_unit,p.product_conv_unit from tbl_request_product as trp 
				left join product_mst as p on p.product_id=trp.rp_pid 
				left join tbl_allocate_process as ap on trp.perent_id=ap.p_ref_id 
				left join unit_mst as bunit on bunit.unitid=trp.process_unit
				left join unit_mst as cunit on cunit.unitid=trp.purchase_unit
				left join tbl_reserve_stock as res on res.p_id = ap.p_id and res.stock_flage = 1 and res.stock_status =0
				left join mst_godown as gd on gd.gd_id = res.godown_id 
				where  trp.status !=2 and trp.perent_id = " . $row['req_id'] . " group by rp_id";
				
				// echo $query2;

				$req_qty = $pid_wise_start_qty[$x];
				$result2=$dbcon->query($query2);
				$unitname = "";
				$i = 1;
				$qty_total = 0;
				while($row2=brp_mysqli_fetch_array($result2)){
					$product_name = $row2['product_name'];
					$unitname = $row2['unit_name'];
					$conv_unitname = $row2['conv_unit_name'];
					
					$total_qty = 0; 
					if (array_key_exists($product_name,$arr_total)){
						$total_qty = $arr_total[$product_name ];
					}

					$o_qty=convert_stock($dbcon,$row2["req_qty_one"],$row2['rp_pid'],"base_unit");
					//$row2["req_qty_one"]=round($row2["req_qty_one"],6);
					
					$o_qty=round($o_qty,6);
					
					//$total_req_qty=$req_qty*$o_qty;
					$total_req_qty=$req_qty*$row2["req_qty_one"];
					$total_req_qty=round($total_req_qty,5);
					//$used_qty=$req_qty*$o_qty;
					$used_qty=$req_qty*$row2["req_qty_one"];
					$used_qty=round($used_qty,5);

					$used_qty = round_up($used_qty,5);

					$c_used_qty=convert_stock($dbcon,$used_qty,$row2['rp_pid'],'conv_unit');
					$c_used_qty = round_up($c_used_qty,5);
					
					$html .= '<input type="hidden" id="stock_process_id" value=""> <div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$row2["product_name"].' </span> </label>
								
							  </div>

							  <div class="col-md-6">

							<!--	<label class="col-md-5 control-label" style="color: #404040;font-weight: 600;">Request Qty :</label> 
								<div class="col-md-7" style="display:flex;">
								<input type="text" class="form-control material_txt_qty_'.$i.' material_qty_'.($x+1).' col-md-7" name="material_qty'.$i.'[]" id="material_qty'.$i.'" data-work_order_no="'.$row["work_order_no"].'" value="'.$used_qty. '" data-pid="'.$row['p_id'].'" data-product_id = "'.$row2['rp_pid'].'" data-request_id = "'.$row2['rp_id'].'" data-req_qty="'.$used_qty.'" onchange="check_material_start_validation('.$i.');"/> <span class="col-md-5" style="color: #0e8400;font-weight: 600;margin-top:8px"> 
								' . $unitname .'</span>
								</div>	
								<input type="hidden" class="material"> 						
							  </div>
							  </div>
							  </div>-->	

								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> '.$used_qty.' ' . $unitname .'</span></label>
									<input type="hidden" id="mt_req_qty_'.$row2['p_id'].'_'.$row2['rp_pid'].'" value="'.$used_qty.'" class="form-control material_txt_qty_'.$i.' material_qty_'.($x+1).' col-md-7" name="material_qty'.$i.'[]"  data-work_order_no="'.$row["work_order_no"].'" data-pid="'.$row['p_id'].'" data-product_id = "'.$row2['rp_pid'].'" data-request_id = "'.$row2['rp_id'].'" data-req_qty="'.$used_qty.'">
									
									<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;   <span style="color: #0e8400;font-weight: 600;"> '.$c_used_qty.' ' . $conv_unitname .'</span></label>
									<input type="hidden" id="mt_req_qty_'.$row2['p_id'].'_'.$row2['rp_pid'].'" value="'.$c_used_qty.'" class="form-control material_txt_qty_'.$i.' material_qty2_'.($x+1).' col-md-7" name="material_qty'.$i.'[]"  data-work_order_no="'.$row["work_order_no"].'" data-pid="'.$row['p_id'].'" data-product_id = "'.$row2['rp_pid'].'" data-request_id = "'.$row2['rp_id'].'" data-req_qty="'.$c_used_qty.'">
									
							  </div>
							  </div>
							<div class="col-md-12">
							  <div class="col-md-12">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																<th width="20%" class="text-center">Godown</th>';
																if($row2['batch_wise_stock_manage'] == '1'){
																	$html.='<th width="20%" class="text-center">Batch No</th>';
																}
																$html.='<th width="10%" class="text-center">Stock Qty</th>
															<!--	<th width="10%" class="text-center">Unit</th> -->
																<th width="10%" class="text-center">Release Qty</th>
																<th width="10%" class="text-center">Action</th>
															</tr>
															<tbody id="field1" >
															<tr id="field">';
															if($row2['batch_wise_stock_manage']=='1'){
																$html .='<td width="20%" >
																	<select class="form-control" name="godown_id" id="godown_id'.$row2['p_id'].'_'.$row2['rp_pid'].'" title="Select Godown" onchange="load_batch_no(this.value,'.$row2['rp_pid'].','.$row2['process_unit'].','.$row2['p_id'].');
																	">
																	'.get_last_node_godown_list($dbcon).'
																	</select>
																	</td><td>
																	<select class="form-control" name="batch_no" id="batch_no'.$row2['p_id'].'_'.$row2['rp_pid'].'" title="Select Godown" onchange="
																	load_stock_qty('.$row2['batch_wise_stock_manage'].','.$row2['rp_pid'].','.$row2['process_unit'].','.$row2['p_id'].');
																	">
																	</select>
																</td>';
															}else{
																$html .= '<td width="20%" >
																			<select class="form-control" name="godown_id" id="godown_id'.$row2['p_id'].'_'.$row2['rp_pid'].'" title="Select Godown" onchange="load_stock_qty('.$row2['batch_wise_stock_manage'].','.$row2['rp_pid'].','.$row2['process_unit'].','.$row2['p_id'].')">
																			'.get_last_node_godown_list($dbcon).'
																			</select>
																		</td>';
															}
																
															$html .='<td width="30%" >
																
															<div class="col-md-10">
															<input id="stock_qty'.$row2['p_id'].'_'.$row2['rp_pid'].'" name="stock_qty" type="text" class="form-control numbersOnly" title="Stock Qty" value="" placeholder="Stock Qty" readonly >
															</div>
															<div class="col-md-2" style="padding: 0;margin-top: 5px;">
															<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $unitname .'</span></label>
															</div>
															<div class="col-md-10" style="margin-top: 10px;">
															<input id="stock_qty2'.$row2['p_id'].'_'.$row2['rp_pid'].'" name="stock_qty" type="text" class="form-control numbersOnly" title="Stock Qty" value="" placeholder="Stock Qty" readonly >
															</div>
															<div class="col-md-2" style="padding: 0;margin-top: 15px;">
															<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $conv_unitname .'</span></label>   
															</div>
															
																<input type="hidden" name="stock_id" id ="stock_id'.$row2['p_id'].'" value="'.$row2['process_unit'].'"/>
																</td>
																
															<!--	<td width="20%" class="text-center">
																
																
																</td> -->
																
																<td width="30%" >
																<div class="col-md-10">
																<input id="release_qty'.$row2['p_id'].'_'.$row2['rp_pid'].'" name="release_qty" type="number" class="form-control numbersOnly" title="Release Qty" value="" placeholder="Release Qty"  onkeyup="convert_qty(2,'.$row2['p_id'].','.$row2['rp_pid'].');">
																</div>
																<div class="col-md-2" style="padding: 0;margin-top: 5px;">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $unitname .'</span></label>
																</div>
																<div class="col-md-10" style="margin-top: 10px;">
																<input id="release_qty2'.$row2['p_id'].'_'.$row2['rp_pid'].'" name="release_qty" type="number" class="form-control numbersOnly" title="Release Qty" value="" placeholder="Release Qty" readonly>
																</div>
																<div class="col-md-2" style="padding: 0;margin-top: 15px;">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $conv_unitname .'</span></label>
																</div>

																</td>
																<td width="10%" class="text-center">
																	
																<input type="button"  name="addrow" id="addrow" onClick="return add_field('.$row2['batch_wise_stock_manage'].','.$row2['p_id'].','.$row2['rp_id'].','.$row['req_id'].','.$row2['rp_pid'].');"  class="btn btn-primary product_add_direct" value="Add"/>
																</td>
															</tr>
															</tbody>
														</table>
													</div>
												</div>
												<div id="release_productdata'.$row2['p_id'].'_'.$row2['rp_pid'].'">';
												auto_add_reserve_godown_material_entry($dbcon,$row2,$used_qty,$row['previous_process_id']);
								$arr_mat_data = get_material_temp_data($dbcon,$row2['p_id'],$row2['rp_pid'],$row2['process_unit'],$row2['batch_wise_stock_manage']);				
											$html .= $arr_mat_data['material'].'
												</div>
												<div class="clearfix"></div>	
											</div>
							  </div>

							  ';
							  $total_qty = $total_qty + $arr_mat_data['total_qty'];
							  
							  // echo "tota :" . $total_qty;
							$arr_total[$product_name] = $total_qty;
							$i++;
				}
				
			}else{
					$req_qty = $pid_wise_start_qty[$x];
					$req_qty = round_up($req_qty,5);
					
					$product_name = $row['product_name'];
					$unitname = $row['unit_name'];
					$conv_unitname = $row['conv_unit_name'];
						$c_used_qty=convert_stock($dbcon,$req_qty,$row['rp_pid'],'conv_unit');
						$c_used_qty = round_up($c_used_qty,5);
					// $total_qty = $req_qty ; 
					
					$process=p_id_wise_find_previous_and_next_process($dbcon,$row['p_id']);
				$process_pr=json_decode($process);
				$previous_process_pid=$process_pr->previous_process_pid;

				$q = "select ap.*,pr.process_name from tbl_allocate_process as ap left join process_mst as pr on pr.process_id = ap.process_id where p_id = ". $previous_process_pid;



				/*$q = "select ap.*,pr.process_name,res.godown_id from tbl_allocate_process as ap left join process_mst as pr on pr.process_id = ap.process_id
				left join tbl_process_reserve_stock as res on res.p_id = ap.p_id and res.stock_flage = 1 and res.stock_status =0
				 where p_id = ". $previous_process_pid;*/

				$res_2=$dbcon->query($q);
								
				$row_2=brp_mysqli_fetch_array($res_2);
					$product_name = $row['product_name'].' - ['. $row_2['process_name'] .']';
					
					$html .= '<input type="hidden" id="stock_process_id" value="'.$row_2["process_id"].'"> <div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$product_name .' </span> </label>
								
							  </div>

							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> '.$req_qty.' ' . $unitname .'</span></label>
									<input type="hidden" id="mt_req_qty_'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$req_qty.'" class="form-control material_txt_qty_'.$i.' material_qty_'.($x+1).' col-md-7" name="material_qty'.$i.'[]"  data-work_order_no="'.$row["work_order_no"].'" data-pid="'.$row['p_id'].'" data-product_id = "'.$row['rp_pid'].'" data-request_id = "'.$row['rp_id'].'" data-req_qty="'.$req_qty.'">
									
									<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;  <span style="color: #0e8400;font-weight: 600;"> '.$c_used_qty.' ' . $conv_unitname .'</span></label>
									<input type="hidden" id="mt_req_qty_'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$c_used_qty.'" class="form-control material_txt_qty_'.$i.' material_qty2_'.($x+1).' col-md-7" name="material_qty'.$i.'[]"  data-work_order_no="'.$row["work_order_no"].'" data-pid="'.$row['p_id'].'" data-product_id = "'.$row['rp_pid'].'" data-request_id = "'.$row['rp_id'].'" data-req_qty="'.$c_used_qty.'">
							  </div>
							  </div>
							<div class="col-md-12">
							  <div class="col-md-12">
												<div class="form-group">
													<div class="col-md-12 col-xs-11">
														<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
															<tr id="field">
																<th width="20%" class="text-center">Godown</th>';
																if($row['batch_wise_stock_manage'] == '1'){
																	$html.='<th width="20%" class="text-center">Batch No</th>';
																}
																$html.='<th width="10%" class="text-center">Stock Qty</th>
																<!-- <th width="20%" class="text-center">Unit</th> -->
																<th width="10%" class="text-center">Release Qty</th>
																<th width="10%" class="text-center">Action</th>
															</tr>
															<tbody id="field1" >
															<tr id="field">';
															if($row['batch_wise_stock_manage']=='1'){
																$html .='<td width="20%" >
																	<select class="form-control" name="godown_id" id="godown_id'.$row['p_id'].'_'.$row['rp_pid'].'" title="Select Godown" onchange="load_batch_no(this.value,'.$row['rp_pid'].','.$row['process_unit'].','.$row['p_id'].');
																	">
																	'.get_last_node_godown_list($dbcon).'
																	</select>
																	</td><td>
																	<select class="form-control" name="batch_no" id="batch_no'.$row['p_id'].'_'.$row['rp_pid'].'" title="Select Godown" onchange="
																	load_stock_qty('.$row['batch_wise_stock_manage'].','.$row['rp_pid'].','.$row['process_unit'].','.$row['p_id'].',1);
																	">
																	</select>
																</td>';
															}else{
																$html .= '<td width="20%" >
																<select class="form-control" name="godown_id" id="godown_id'.$row['p_id'].'_'.$row['rp_pid'].'" title="Select Godown" onchange="load_stock_qty('.$row['batch_wise_stock_manage'].','.$row['rp_pid'].','.$row['process_unit'].','.$row['p_id'].',1)">
																'.get_last_node_godown_list($dbcon).'
															</select>
																		</td>';
															}
																
															$html .='
																
																<td width="30%" >
																<div class="col-md-10">
																<input id="stock_qty'.$row['p_id'].'_'.$row['rp_pid'].'" name="stock_qty" type="text" class="form-control numbersOnly" title="Stock Qty" value="" placeholder="Stock Qty" readonly >
																</div>
																<div class="col-md-2" style="padding: 0;margin-top: 5px;">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $unitname .'</span></label>
																<input type="hidden" name="unit_id" id ="unit_id'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$row['process_unit'].'"/>
																</div>
																<div class="col-md-10" style="margin-top: 10px;">
																<input id="stock_qty2'.$row['p_id'].'_'.$row['rp_pid'].'" name="stock_qty" type="text" class="form-control numbersOnly" title="Stock Qty" value="" placeholder="Stock Qty" readonly > </div>
																<div class="col-md-2" style="padding: 0;margin-top: 15px;">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $conv_unitname .'</span></label>
																<input type="hidden" name="unit_id" id ="unit_id'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$row['process_unit'].'"/>
																</div>
																<input type="hidden" name="stock_id" id ="stock_id'.$row['p_id'].'" value="'.$row['process_unit'].'"/>
																</td>
																
															<!--	<td width="20%" class="text-center">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;"> ' . $unitname .'</span></label>
																<input type="hidden" name="unit_id" id ="unit_id'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$row['process_unit'].'"/>
																
																</td> -->
																
																<td width="30%" >
																<div class="col-md-10">
															<input id="release_qty'.$row['p_id'].'_'.$row['rp_pid'].'" name="release_qty" type="number" class="form-control numbersOnly" onkeyup="convert_qty(1,'.$row['p_id'].','.$row['rp_pid'].');" title="Release Qty" value="" placeholder="Release Qty">
															</div>
															<div class="col-md-2" style="padding: 0;margin-top: 5px;">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $unitname .'</span></label>
																<input type="hidden" name="unit_id" id ="unit_id'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$row['process_unit'].'"/>
															</div>
															<div class="col-md-10" style="margin-top: 10px;">
																<input id="release_qty2'.$row['p_id'].'_'.$row['rp_pid'].'" name="release_qty" type="number" class="form-control numbersOnly" title="Release Qty" value="" placeholder="Release Qty" readonly>
															</div>
															<div class="col-md-2" style="padding: 0;margin-top: 15px;">
																<label class="col-md-12 control-label" style="color: #0e8400;font-weight: 600;padding: 0;"> ' . $conv_unitname .'</span></label>
																<input type="hidden" name="unit_id" id ="unit_id'.$row['p_id'].'_'.$row['rp_pid'].'" value="'.$row['process_unit'].'"/>
															</div>

																
																</td>
																<td width="10%" class="text-center">
																	
																<input type="button"  name="addrow" id="addrow" onClick="return add_field('.$row['batch_wise_stock_manage'].','.$row['p_id'].','.$row['req_id'].','.$row['req_id'].','.$row['rp_pid'].','.$row['process_id'].');"  class="btn btn-primary product_add_direct" value="Add"/>
																</td>
															</tr>
															</tbody>
														</table>
													</div>
												</div>
												<div id="release_productdata'.$row['p_id'].'_'.$row['rp_pid'].'">';
												auto_add_reserve_godown_material_entry($dbcon,$row,$req_qty,$row['previous_process_id']);
								$arr_mat_data = get_material_temp_data($dbcon,$row['p_id'],$row['rp_pid'],$row['process_unit'],$row['batch_wise_stock_manage']);				
											$html .= $arr_mat_data['material'].'
												</div>
												<div class="clearfix"></div>	
											</div>
							  </div>
							  ';
							//   $total_qty = $total_qty + $arr_mat_data['total_qty'];
							  
							//   // echo "tota :" . $total_qty;
							// $arr_total[$product_name] = $total_qty;
							// $i++;

							   $qty_total =  $qty_total + $req_qty;
							  // echo "tota :" . $total_qty;
							$arr_total[$product_name] = $qty_total;
							$i++;
			}
				$x++;
			} 

			/*if($cnt > 0){
				$html .='<div class="col-md-12 bg-primary text-center control-label" style="margin-top:5px;">
				<label class="col-md-12 control-label" style="color: white;font-weight: 600; margin-top:10px;">Total Request Materials</label>
					
				</div>';
				$j = 1;
				foreach($arr_total as $key => $value){
					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$key.' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Release Qty : <span style="color: #0e8400;font-weight: 600;" id="material_total'.$j.'"> '.$value.' </span><span style="color: #0e8400;font-weight: 600;"> ' . $unitname .'</span>  </label>
								
							  </div></div>
							  ';
							  $j++;
				}
			}*/

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
			<input type="button"  id="back_btn" name="back" class="btn btn-success" value="Release" onclick="store_release();" />
						<input type="button"  style="margin-left:10px" id="back_btn" name="back" class="btn btn-danger" value="Back" onclick="previous_page();" />
					</div>';
			
			echo $html;
			
		}else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];

			$pro_qry = "select product_base_unit,product_conv_unit from product_mst where product_id = " . $product_id;
			$pro_row = brp_mysqli_fetch_assoc($dbcon->query($pro_qry));

			$process_stock=$POST['process_stock'];
			$stock_id=$POST['stock_id'];
			$godown_id = $POST['godown_id'];
			
			$unit_id = $POST['unit_id'];
			$previous_p_id = $POST['previous_p_id'];

			$pr_q = "select process_id from tbl_allocate_process where p_id = " . $previous_p_id;
			$pr_rw = brp_mysqli_fetch_assoc($dbcon->query($pr_q));
			$pre_process_id = $pr_rw['process_id'];

			if($process_stock == '1'){
				$stock = 0;
				/*$query = "select process_reserve_id,product_id,process_id,branch_id,process_stock_id,godown_id from tbl_process_reserve_stock as allo_mat
					where allo_mat.stock_status=0 and stock_flage=1 and allo_mat.godown_id = ".$godown_id." and allo_mat.p_id in(".$POST['p_id'].")";
					$result=$dbcon->query($query);
					while($row=brp_mysqli_fetch_array($result)){
						
								$reserve_stock=production_process_reseve_stock($dbcon,$unit_id,$row['branch_id'],$POST['p_id'],$row['product_id'],$row['process_id'],$row['process_reserve_id'],$row['process_stock_id'],0,$godown_id);

								$stock = $stock + $reserve_stock;
							}*/

					$stock = 	get_current_process_stock_new($dbcon, $product_id, $pre_process_id, $unit_id, $branch_id = "", $POST['godown_id'],$process_stock_id);

					// var_dump($stock);

					$rstock = get_material_temp_reserve_stock($dbcon, $product_id, $unit_id, $POST['godown_id'],"",$POST['p_id']);		
					// $stock = round_up($stock,5);		
					// $rstock = round_up($rstock,5);

					// var_dump($rstock);
					
					$stock = $stock - $rstock;
					$stock = round_up($stock,5);	
					$arr['stock_1'] = 		$stock;
								
					if($pro_row['product_base_unit'] == $pro_row['product_conv_unit']){
						$arr['stock_2'] = $stock;
					}else if($pro_row['product_conv_unit'] == $unit_id){
						$stock2=convert_stock($dbcon,$stock,$product_id,'base_unit');
						$stock2 = round_up($stock2,5);
						$arr['stock_2'] = $stock2;
					}else{
						$stock2=convert_stock($dbcon,$stock,$product_id,'conv_unit');
						$stock2 = round_up($stock2,5);
						$arr['stock_2'] = $stock2;
					}
					

					// echo $stock;
					echo json_encode($arr);		
			}else{
				$get_pro_type_qry="select product_type,product_base_unit,product_conv_unit from product_mst where product_id=".$product_id;
				$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
				

				$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9", "-1");
				if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
					if(!empty($POST['unit_id'])){
						$unit_id=$POST['unit_id'];
					}else{
						$unit_id=$get_pro_type_rel['product_base_unit'];
					}
					// $current_stock = get_current_stock_new($dbcon,$product_id,$unit_id);
					$current_stock = get_current_godown_stock_new($dbcon, $product_id, $unit_id, $POST['godown_id'],"",$stock_id);
					 
					$rstock = get_material_temp_reserve_stock($dbcon, $product_id, $unit_id, $POST['godown_id'],"",$POST['p_id']);
					// $rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['godown_id'],$stock_id);

					$stock=$current_stock-$rstock;	
					$stock = round_up($stock,5);
					$arr['stock_1'] = 		$stock;
					if($pro_row['product_base_unit'] == $pro_row['product_conv_unit']){
						$arr['stock_2'] = $stock;
					}else if($pro_row['product_conv_unit'] == $unit_id){
						$stock2=convert_stock($dbcon,$stock,$product_id,'base_unit');
						$stock2 = round_up($stock2,5);
						$arr['stock_2'] = $stock2;
					}else{
						$stock2=convert_stock($dbcon,$stock,$product_id,'conv_unit');
						$stock2 = round_up($stock2,5);
						$arr['stock_2'] = $stock2;
					}
					

					echo json_encode($arr);		
					// echo $stock;
				}
				else{
					$arr['stock_1'] = 		0;
					$arr['stock_2'] = 		0;
					echo json_encode($arr);		
					// echo 0;
				}
			}			
		}else if(brp_strtolower($POST['mode']) == "fieldadd") {

			$product_id 	= $POST["product_id"];
			$rp_id  		= $POST["rp_id"];
			$parent_rp_id   = $POST["parent_rp_id"];
			$p_id  			= $POST["p_id"];
			$unit_id 		= $POST["unit_id"];
			$release_qty	= $POST["qty"];
			$godown_id 		= $POST["godown_id"];
			$batch_no 		= $POST['batch_no'];
			$stock_id 		= $POST['stock_id'];
			$process_id 	= $POST['process_id'];

			$query = "select * from product_mst where product_id = " . $product_id;
			$result = $dbcon->query($query);
			$row = brp_mysqli_fetch_assoc($result);


			$info1['product_id']	= $product_id;
			$info1['rp_id']			= $rp_id;
			$info1['parent_rp_id']	= $parent_rp_id;
			$info1['p_id']			= $p_id ;
			$info1['godown_id']		= $godown_id ;
			$info1['cdate']				= date('Y-m-d H:i:s');
			$info1['user_id']			= $_SESSION['user_id'];	
			$info1['company_id']		= $_SESSION['company_id'];	
			$info1['base_unit']		= $row['product_base_unit'];
			$info1['conv_unit']		= $row['product_conv_unit'];
			$info1['status']		= 3;
			$info1['batch_no']		= $batch_no;
			
		/* $query_dstock="select i.*,(base_stock) as pending_base_stock,(convert_stock) as pending_conv_stock from tbl_stock_trn as i	
			where stock_status=0 and stock_flage=1 and i.product_id=".$product_id." and i.godown_id=".$godown_id;*/
			$whr = '';
			if($rp_id == $parent_rp_id){
				if(!empty($batch_no)){
					$whr .= " and batch_no = '".$batch_no."'";
				}
				if(!empty($stock_id)){
					$whr .= " and process_stock_id in(".$stock_id.")";
				}
				// if(!empty($process_id)){
				// 	$whr .= " and process_id =" . $process_id;
				// }
				 $query_dstock = "select i.*,(cast(base_stock AS DECIMAL(22,5)) - IFNULL((select sum(base_stock) from tbl_process_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.process_stock_id),0)) as pending_base_stock,(cast(conv_stock AS DECIMAL(22,5)) - IFNULL((select sum(conv_stock) from tbl_process_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.process_stock_id),0)) as pending_conv_stock from tbl_process_stock_trn as i where stock_status=0 and stock_flage=1 and i.product_id=".$product_id." and i.godown_id=".$godown_id.$whr;
			}else{
				if(!empty($batch_no)){
					$whr .= "and batch_no = '".$batch_no."'";
				}
				if(!empty($stock_id)){
					$whr .= " and stock_id in(".$stock_id.")";
				}
				$query_dstock = "select i.*,(cast(base_stock AS DECIMAL(22,5)) - IFNULL((select sum(base_stock) from tbl_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.stock_id),0)) as pending_base_stock,(cast(convert_stock AS DECIMAL(22,5)) - IFNULL((select sum(convert_stock) from tbl_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.stock_id),0)) as pending_conv_stock from tbl_stock_trn as i where stock_status=0 and stock_flage=1 and i.product_id=".$product_id." and i.godown_id=".$godown_id.$whr;
			}
			
			$result_dstock=$dbcon->query($query_dstock);
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
				if($row_dstock['convert_unit']==$unit_id){
					$pending_stock=$row_dstock['pending_conv_stock'];
				}else{
					$pending_stock=$row_dstock['pending_base_stock'];	
				}
				if($release_qty>0){
					if($pending_stock>=$release_qty){
						$rqty=$release_qty;
						$release_qty=$release_qty-$release_qty;
					}else{
						$rqty=$pending_stock;
						$release_qty=$release_qty-$pending_stock;
					}

					if($row['product_conv_unit']==$unit_id){
						$type="base_unit";
						$con_stock=$rqty;
						$base_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);
					}else{
						$type="conv_unit";
						$base_stock=$rqty;
						$con_stock=convert_stock_new($dbcon,$rqty,$product_id,$type);
					}

					
					$info1['base_qty']		= $base_stock;
					$info1['conv_qty']		= $con_stock;
					if($rp_id == $parent_rp_id){
						$info1['stock_id']		= $row_dstock['process_stock_id'];
					}else{
						$info1['stock_id']		= $row_dstock['stock_id'];
					}
					
					$inserpoid=add_record('tbl_material_release_trn',$info1, $dbcon);
				}
			}

			if($inserpoid){
				echo 1;
			}else{
				echo 0;
			}
	}else if(brp_strtolower($POST['mode']) == "load_material_tempoutward") {
		$p_id = $POST['p_id'];
		$product_id = $POST['product_id'];
		$unit_id = $POST['unit_id'];

		$q = "select batch_wise_stock_manage from product_mst where product_id =" . $product_id;
		$res=brp_mysqli_fetch_assoc($dbcon->query($q));
		$str = get_material_temp_data($dbcon,$p_id,$product_id,$unit_id,$res['batch_wise_stock_manage']);

		echo $str['material'];
	}else if(brp_strtolower($POST['mode'])== "delete_data") {
		$row=array();
		$material_trn_id = $POST['material_trn_id'];	
		$info['status']=2;	
		$updateid=update_record('tbl_material_release_trn', $info, "material_trn_id in(".$material_trn_id.")", $dbcon);

		if($updateid){
			$arr['res'] = "1";
		}else{
			$arr['res'] = "1";
		}

		echo json_encode($arr);
	}else if(strtolower($POST['mode'])== "load_batch_no")
	{
		
		$godwn_id=$POST['godwn_id'];
		$product_id=$POST['product_id'];
		$customer_id=$POST['customer_id'];
		$unit_id = $POST['unit_id'];
		$previous_process_id = $POST['previous_process_id'];
		$process_id = $POST['process_id'];

		$unitname = getunitname($dbcon,$unit_id);

		/*$query="select batch_no,stock_id from tbl_stock_trn as trn
		where trn.stock_status=0 and stock_flage=1 and product_id=".$product_id." and trn.godown_id=".$godwn_id." and cast(base_stock AS DECIMAL(10,5))>cast(used_base_stock AS DECIMAL(10,5))";*/

		if($previous_process_id == '0'){
			$query="select i.*,(cast(base_stock AS DECIMAL(22,5)) - IFNULL((select sum(base_stock) from tbl_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.stock_id),0)) as pending_base_stock,(cast(convert_stock AS DECIMAL(22,5)) - IFNULL((select sum(convert_stock) from tbl_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.stock_id),0)) as pending_conv_stock,group_concat(i.stock_id) as b_stock_id from tbl_stock_trn as i
		   where stock_status=0 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = ".$product_id." and batch_no != '' and godown_id = ".$godwn_id." group by batch_no";
	   }else{
			echo $query="select i.*,(cast(base_stock AS DECIMAL(22,5)) - IFNULL((select sum(base_stock) from tbl_process_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.process_stock_id),0)) as pending_base_stock,(cast(conv_stock AS DECIMAL(22,5)) - IFNULL((select sum(conv_stock) from tbl_process_stock_trn where stock_status = 0 and stock_flage = 2 and perent_id = i.process_stock_id),0)) as pending_conv_stock,group_concat(i.process_stock_id) as b_stock_id from tbl_process_stock_trn as i
			where stock_status=0 and  process_id = ".$process_id ." and  product_id = ".$product_id." and batch_no != '' and godown_id = ".$godwn_id." group by batch_no";
		}

		/* $query="select i.*,(IFNULL(sum(base_stock),0)-IFNULL(sum(used_base_stock),0)) as pending_base_stock,(IFNULL(sum(convert_stock),0)-IFNULL(sum(used_convert_stock),0)) as pending_conv_stock,group_concat(i.stock_id) as b_stock_id from tbl_stock_trn as i
			where stock_status=0 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) and product_id = ".$product_id." and batch_no != '' and godown_id = ".$godwn_id." group by batch_no"; */

			//echo $query;
			$rs_batch=$dbcon->query($query);
			$str="";
			if(mysqli_num_rows($rs_batch)>0)
			{
				$str= '<option value="">Choose Batch No</option>';
				while($rel=brp_mysqli_fetch_assoc($rs_batch))
				{	
					if($rel['pending_base_stock'] > 0){
						//$str.= '<option value="'.$rel['b_stock_id'].'" data-stock="'.$rel['base_stock'].'" data-batch_no="'.$rel['batch_no'].'" >'.$rel['batch_no'].' - (' . $rel['pending_base_stock'] . ' '. $unitname . ')</option>';
						$str.= '<option value="'.$rel['b_stock_id'].'" data-stock="'.$rel['base_stock'].'" data-batch_no="'.$rel['batch_no'].'" >'.$rel['batch_no'].'</option>';
					}
				}
			}else{
				$str .= '<option value="">No Batch Data !!</option>';
			}

		/*$str="";
		$result=$dbcon->query($query);
		if(mysqli_num_rows($result)>0)
		{	
			$str .= '<option value="">Select Batch Data</option>';
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$gstock=0;$rstock=0;
					$batch_id=$POST['stock_id'];
					
					$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$godwn_id,$branch_id,$batch_id,$customer_id);

					$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


					$stock=$gstock-$rstock;

				$str .= '<option value="'.$rel['stock_id'].'">'.$rel['batch_no'].' - (' . $stock . ' '. $unitname . ')</option>';
			}
		}else{
			$str .= '<option value="">No Batch Data !!</option>';
		}*/

		echo $str;
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
		$ret_qty_new=number_format($ret_qty, 4, ".", "");
		$ret_qty_new = round_up($ret_qty,5);
			//$ret_qty=$ret_qty;
		//	echo $ret_qty;
		$row['show_qty']=$ret_qty_new;
		$row['hide_qty']=$ret_qty;
		echo json_encode($row);
	}



	function get_material_temp_data($dbcon,$p_id,$product_id,$unit_id,$batch_wise_stock_manage){

		$query = "select trn.*,gd.gd_name,umst.unit_name,conv_mst.unit_name as convert_unit_name, sum(base_qty) as base_qty, sum(conv_qty) as conv_qty, group_concat(material_trn_id) as material_trn_id from tbl_material_release_trn as trn 
		left join unit_mst as umst on umst.unitid=trn.base_unit
		left join unit_mst as conv_mst on conv_mst.unitid=trn.conv_unit
		left join mst_godown as gd on gd.gd_id = trn.godown_id 
		where status = 3 and p_id =" .$p_id . " and product_id =" . $product_id." group by batch_no,godown_id";
		$result = $dbcon->query($query); 

		$str = "";
		$total_rel_qty = 0;
		if(brp_mysqli_num_rows($result) > 0){
			$str .= '<div class="col-md-12">
						<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">';
							if($batch_wise_stock_manage == '1'){
								$str .= '<th width="20%" class="text-center">Batch No</th>';			
							}
			$str.='<th width="20%" class="text-center">Godown</th>
										<th width="20%" class="text-center">Release Base Qty</th>
										<th width="20%" class="text-center">Base Unit</th>
										<th width="20%" class="text-center">Release Conv Qty</th>
										<th width="20%" class="text-center">Convert Unit</th>
										<th width="20%" class="text-center">Action</th>
									</tr>
									<tbody id="field1">';
			$x=1;				
			while($row = brp_mysqli_fetch_assoc($result)){
				
				// if($unit_id == $row['conv_unit']){
				// 	$qty = $row['conv_qty'];
				// 	$unitname = $row['convert_unit_name'];
				// }else{
				// 	$qty = $row['base_qty'];
				// 	$unitname = $row['unit_name'];
				// }

				$qty = $row['base_qty'];
				$conv_qty = $row['conv_qty'];
				$unitname = $row['unit_name'];
				$conv_unitname = $row['convert_unit_name'];


				$total_rel_qty = $total_rel_qty + $qty;
				$material_trn_id = "'". $row['material_trn_id'] . "'";
				$str .= '<tr>';
				if($batch_wise_stock_manage == '1'){
								$str .= '<td>'.$row['batch_no'].'</th>';			
							}
						$str .= '<td>'.$row['gd_name'].'</td>
					<td>'.$qty.'</td>
					<td>'.$unitname.'</td>
					<td>'.$conv_qty.'</td>
					<td>'.$conv_unitname.'</td>
					<td class="text-center"><button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_material_temp_data('.$material_trn_id.','.$p_id.','.$product_id.','.$unit_id.')"><i class="fa fa-trash-o"></i></button></td>
				</tr>';
				$x++;
			}
			$str .= '<input type="hidden" id="total_rel_material_'.$p_id.'_'.$product_id.'" value="'.$total_rel_qty.'">';
			$str .= "</tbody></table></div>";
		}	

		$arr['material'] = $str;
		$arr['total_qty'] = $total_rel_qty;
		return $arr;
	}


function auto_add_reserve_godown_material_entry($dbcon,$req_data,$qty,$previous_process_id){
	// process_unit

	$unit_id = $req_data['process_unit'];
	$info_status['status'] = 2;

	$query = "select * from product_mst where product_id = " . $req_data['rp_pid'];
	$result = $dbcon->query($query);
	$row = brp_mysqli_fetch_assoc($result);
	
	if($previous_process_id == '0'){
		$updateid=update_record('tbl_material_release_trn', $info_status, "rp_id=".$req_data['rp_id']." and status = 3 and p_id = " . $req_data['p_id'], $dbcon);

		$query_dstock = "select st.batch_no,i.*,(cast(i.base_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(base_stock),0) as bstock from tbl_reserve_stock where stock_status = 0 and stock_flage = 2 and p_id=".$req_data['p_id'] ." and stock_id = i.stock_id),0)) as pending_base_stock,(cast(i.convert_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(convert_stock),0) as cstock from tbl_reserve_stock where stock_status = 0 and stock_flage = 2 and p_id=".$req_data['p_id'] ." and stock_id = i.stock_id),0)) as pending_conv_stock from tbl_reserve_stock as i left join tbl_stock_trn as st on st.stock_id = i.stock_id where i.stock_status=0 and i.stock_flage=1 and i.product_id=".$req_data['rp_pid']." and i.p_id=".$req_data['p_id'];
	}else{
		$updateid=update_record('tbl_material_release_trn', $info_status, "rp_id=".$req_data['req_id']." and status = 3  and p_id = " . $req_data['p_id'], $dbcon);

		$query_dstock = "select st.batch_no,i.*,(cast(i.base_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(base_stock),0) as bstock from tbl_process_reserve_stock where stock_status = 0  and   p_id=".$req_data['p_id'] ." and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_base_stock,(cast(i.conv_stock AS DECIMAL(22,5)) - IFNULL((select IFNULL(sum(conv_stock),0) as cstock from tbl_process_reserve_stock where stock_status = 0 and stock_flage = 2 and perent_id = i.process_reserve_id),0)) as pending_conv_stock from tbl_process_reserve_stock as i 
		left join tbl_process_stock_trn as st on st.process_stock_id = i.process_stock_id 
		where i.stock_status=0 and i.stock_flage=1 and i.product_id=".$req_data['rp_pid']." and i.p_id = " . $req_data['p_id'];
	}


	$info1['product_id']	= $req_data['rp_pid'];
	
	if($previous_process_id == 0){
		$info1['rp_id']			= $req_data['rp_id'];
		$info1['parent_rp_id']	= $req_data['perent_id'];
		
	}else{
		$info1['rp_id']			= $req_data['req_id'];
		$info1['parent_rp_id']	= $req_data['req_id'];
		
	}
	
	$info1['p_id']			= $req_data['p_id'] ;
	
	$info1['cdate']				= date('Y-m-d H:i:s');
	$info1['user_id']			= $_SESSION['user_id'];	
	$info1['company_id']		= $_SESSION['company_id'];	
	$info1['base_unit']		= $row['product_base_unit'];
	$info1['conv_unit']		= $row['product_conv_unit'];
	$info1['status']		= 3;
			
	$result_dstock=$dbcon->query($query_dstock);

	$release_qty = $qty;

	while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
		/*if($row_dstock['convert_unit']==$unit_id){
			$pending_stock=$row_dstock['pending_conv_stock'];
		}else{
			$pending_stock=$row_dstock['pending_base_stock'];	
		}*/
		$info1['godown_id']		= $row_dstock['godown_id'];
		$info1['batch_no']		= $row_dstock['batch_no'];	

		if($previous_process_id > 0){
			$info1['godown_id']		= $row_dstock['godown_id'];	
		}

		$pending_stock=$row_dstock['pending_base_stock'];	
		
		if($release_qty>0){
			if($pending_stock>=$release_qty){
				$rqty=$release_qty;
				$release_qty=$release_qty-$release_qty;
			}else{
				$rqty=$pending_stock;
				$release_qty=$release_qty-$pending_stock;
			}
	
			$type="conv_unit";
			$base_stock=$rqty;
			$con_stock=convert_stock_new($dbcon,$rqty,$req_data['rp_pid'],$type);
			
			$info1['base_qty']		= $base_stock;
			$info1['conv_qty']		= $con_stock;
			if($previous_process_id == '0'){
				$info1['stock_id']		= $row_dstock['stock_id'];
			}else{
				$info1['stock_id']		= $row_dstock['process_stock_id'];
			}
				// var_dump($info1);
			$inserpoid=add_record('tbl_material_release_trn',$info1, $dbcon);
		}
	}
}	
?>

