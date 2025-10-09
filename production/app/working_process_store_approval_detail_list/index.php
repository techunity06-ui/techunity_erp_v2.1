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
		
		if(brp_strtolower($POST['mode']) == "fetch_working") {
			
			$process_id=$POST['process_id'];
			$process_type=$_POST['process_type'];
			
			
			$str='';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Product Category</th>
				<th>Qty</th>
				<th>Pending Qty</th>
				<th>Approval Pending Qty</th>
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
			
			
			 $s_ql = "select GROUP_CONCAT(p_id) as allocate_id,sum(p_qty) as total_qty,sum(pen_qty) as total_pending,sum(start_qty) as total_start_qty,branch.branch_name,p.product_name,tc.cat_name,p_status from tbl_allocate_process as ap
				
			left join product_mst as p on p.product_id=ap.p_product_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=ap.branch_id
			where ap.process_id=".$process_id." ".$Product_filter." ".$check_branch." and ap.company_id=".$_SESSION['company_id']." and ap.p_status IN(0,1) and pr_process_type='$process_type' group by ap.p_product_id,ap.branch_id,ap.product_version" ;

			$q=$dbcon->query($s_ql);
			
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				if($POST['type']=="1"){
					$working_qty=production_start_count_using_p_id($dbcon,$rel['allocate_id']);
					$pending_qty=total_production_pending_qty($dbcon,$rel['allocate_id']);
				}else{
					$working_qty=production_end_count_using_p_id($dbcon,$rel['allocate_id']);
					$pending_qty=$working_qty;
				}
				
				if($working_qty>0){
					
						//$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'start_process/'.$rel['p_product_id'].'/'.$process_type.'/'.$process_id.'/process/'.$rel['branch_id'].'" >Start <i class="fa fa-plus"></i></a>';
						$start_url=urlencode($rel['allocate_id']);
						$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_store_approve/'.$start_url.'" >Approve <i class="fa fa-plus"></i></a>';
						
						$new_button='<button class="btn btn-xs btn-success" data-original-title="PO Approved" data-toggle="tooltip" data-placement="top" onclick="approve_using_model('.$rel['allocate_id'].')">A <i class="fa fa-plus"></i></button>';
						
					//	$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" title="Process Start" href="'.ROOT.PRODUCTION_ROOT.'production_process_start/'.$rel['allocate_id'].'" >Start <i class="fa fa-plus"></i></a>';
					
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';	
					$str.='<tr>
							<th>'.$cnt.'</th>
							<th>'.$rel['product_name'].'</th>
							<th>'.$cat_name.'</th>
							<th>'.$rel['total_qty'].'</th>
							<th>'.$pending_qty.'</th>
							<th>'.$working_qty.'</th>';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							$str.='<th>'.$button.' '.$new_button.'</th>
						</tr>';
						$cnt++;
						$datacheck=1;
				}
			}
			if($datacheck!=1){
				$str.= '<tr><td colspan="9"> <center>No Process Found!!!!!</center></td></tr>';
			}
			
			echo $str;
		}
		else if(brp_strtolower($POST['mode']) == "approve_using_model") {
			$p_id=$POST['p_ids'];
			$html="";
			
			$query="select p.product_name,pr.process_name,ap.branch_id,ap.process_unit,umst.unit_name,ap.p_product_id,ap.process_id,ap.product_version from tbl_allocate_process as ap 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join process_mst as pr on pr.process_id=ap.process_id
				left join unit_mst as umst on umst.unitid=ap.process_unit
			where ap.p_id in (".$p_id.")";
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				$select_branch_id=$rel['branch_id'];
				$pno=load_series_no_using_type_id($dbcon,INHOUSE_JOB_WORK,$_SESSION['company_id'],$branch_id1);
				$pending_qty=total_production_pending_qty($dbcon,$p_id);
				$working_qty=production_start_count_using_p_id($dbcon,$p_id);
				
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
							<label class="col-md-4 control-label" style="color: #404040;font-weight: 600;">Approve Qty *</label>
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
					<tr>
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Other Details</th>
						<th>Pending Qty</th>
						<th>Approve Pending Qty</th>
						<th>Approve Qty</th>
						<!--<th class="nosort">Action <input type="checkbox" onclick="checkAll();"  name="chk[]"/></th>	-->										
					</tr>';
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit
					where ap.p_id in (".$p_id.")" ;

			$result1=$dbcon->query($query1);
			$start_qty=0;
			$s=1;
			while($row=brp_mysqli_fetch_array($result1)){
				$start_qty=production_start_count_using_p_id($dbcon,$row['p_id']);
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
							</th>
							
							<!--<th class="nosort">
								<input type="checkbox" class="ck_pid" data-jobcardno="'.$row["job_card_no"].'"  chk name="chk[]"  value="'.$row['p_id'].'"/>
							</th>-->											
						</tr>';
				$s++;
			} 
			
			$html .='</table>
			</div>
			';
			
			
			$html .='<input type="hidden" name="mode" id="mode" value="add_store_approve" />
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
						<input type="button" id="sp_btn" name="submit" class="btn btn-success" value="Approve Quantity" onclick="store_approve_qty_using_model();" />
					</center>
				</div>';
			
			echo $html;
			
		}
		
		
?>