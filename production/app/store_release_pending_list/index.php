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
			
			
			$str='';
			$str.='<tr>
				
				<th>#</th>
				<th>Product Name</th>
				<th>Product Category</th>
				<th> Request Qty</th>
				<th> Pending Qty</th>
				<th> Released Qty</th>
				<!--<th>Start Time</th>
				<th>End Time</th>-->';
				if($_SESSION['branch_id']==0){
					$str.='<th>Branch Name</th>';
				}
				$str.='<th>Action</th>
				
			</tr>';
			if(!empty($POST['product_id'])){
				$Product_filter=" and tsr.rp_id=".$POST['product_id'];
			}
			if(!empty($POST['process_id'])){
				$process_id=" and tsr.process_id=".$POST['process_id'];
			}

			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$check_branch = check_branch('tsr', $branch_id);
			
			
			 $s_ql = "select if(sum(base_qty), sum(base_qty),0) as total_qty,if(sum(release_qty), sum(release_qty),0) as total_release_qty, branch.branch_name,p.product_name,tc.cat_name, GROUP_CONCAT(tsr.p_id) AS pids from tbl_store_request as tsr
			left join product_mst as p on p.product_id=tsr.product_id 
			left join tbl_category as tc on p.product_category=tc.cat_id
			left join branch_mst as branch on branch.branch_id=tsr.branch_id
			where tsr.store_request_status = 0".$process_id." ".$Product_filter." ".$check_branch." and tsr.company_id=".$_SESSION['company_id']."  group by tsr.product_id, tsr.branch_id" ;

			$q=$dbcon->query($s_ql);
			// echo $s_ql;
			$cnt=1;
			$datacheck="";
			$machine_make_new=array();
			while($rel=brp_mysqli_fetch_array($q))
			{
				$url = $rel["pids"];
				
						$view='<button class="btn btn-xs btn-primary" data-original-title="Store Request" data-toggle="tooltip" data-placement="top" onclick="store_release_using_model('. "'". $url."'".')"><i class="fa fa-eye"></i></button>';
					
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';	
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';	
					$str.='<tr>
							<th>'.$cnt.'</th>
							<th>'.$rel['product_name'].'</th>
							<th>'.$cat_name.'</th>
							<th>'.$rel['total_qty'].'</th>
							<th>'.($rel['total_qty'] - $rel['total_release_qty']).'</th>
							<th>'.$rel['total_release_qty'].'</th>
							';
							if($_SESSION['branch_id']==0){
								$str.='<th>'.$branch_name.'</th>';
							}
							$str.='<th>'.$view.'</th>
						</tr>';
						$cnt++;
						$datacheck=1;
				}
			
			if($datacheck!=1){
				$str.= '<tr><td colspan="9"> <center>No Process Found!!!!!</center></td></tr>';
			}
			
			echo $str;
		}
		else if(brp_strtolower($POST['mode']) == "store_release_using_model") {
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
				$working_qty=production_start_count_using_p_id($dbcon,$p_id,0);

				$req_qty = store_request_approval_pending_count($dbcon,$process_id,1,1,1);
				
				$req_pending_qty = $pending_qty - $req_qty;
				
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
					
					
					';
			
			$html .='<div class="col-md-12" style="margin-bottom: 15px;">
					<table class="display table table-bordered table-striped" id="">
					<tr>
						<th>Work Order No</th>
						<th>Work Order Date</th>
						<th>Job Card No</th>
						<th>Job Card Date</th>
						<th>Request Qty</th>
					</tr>';
			
			$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,
			(select if(sum(base_qty), sum(base_qty),0) as total_req_qty from tbl_store_request 			
		where p_id= ap.p_id and store_request_status = 0) as total_req_qty
			 from tbl_allocate_process as ap
					left join product_mst as p on p.product_id=ap.p_product_id 
					left join tbl_request_product req on req.rp_id=ap.p_ref_id
					left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
					left join unit_mst as umst on umst.unitid=ap.process_unit

					where ap.p_id in (".$p_id.")" ;

			$result1=$dbcon->query($query1);
			$start_qty=0;
			$s=1;

			$total_req_qty = 0;
			while($row=brp_mysqli_fetch_array($result1)){
				$start_qty=production_start_count_using_p_id($dbcon,$row['p_id'],0);
				$html .='<tr id="trid'.$row['p_id'].'">
							<th>'.$row["work_order_no"].'</th>
							<th>'.$row["work_order_date"].'</th>
							<th>'.$row["job_card_no"].'</th>
							<th>'.$row["job_card_date"].'</th>
							<th><input type="text" class="form-control start_qty" name="start_qty1[]" data-jobcardno="'.$row["job_card_no"].'" data-pid="'.$row['p_id'].'" data-start_qty="'.$start_qty.'" id="start_qty1'.$row['p_id'].'" value="'.$row['total_req_qty'].'" readonly/>
							 '.$row["unit_name"].'
							</th>							
						</tr>';

						$total_req_qty = $total_req_qty + $row['total_req_qty'];
				$s++;
			} 
			$html .='<tr>
						<td colspan="4" class="text-right"><b>Total Request Qty</b></td>
						<td><input type="text" name="total_req_qty" id="total_req_qty" class="form-control" value="'.$total_req_qty.'" readonly /> </td>
					</tr>';
			$html .='</table>
			</div>';
			
			$html .='
			<input type="hidden" name="p_id" id="p_id" value="'.$p_id.'" />
			<input type="hidden" name="product_base_unit" id="product_base_unit" value="'.$rel["process_unit"].'" />
			<input type="hidden" name="branch_id_model" id="branch_id_model" value="'.$select_branch_id.'" />
			<input type="hidden" name="product_id_model" id="product_id_model" value="'.$rel["p_product_id"].'" />
			<input type="hidden" name="process_id" id="process_id" value="'.$rel["process_id"].'" />
			<input type="hidden" name="product_version" id="product_version" value="'.$rel["product_version"].'" />';
			
		$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" name="submit" class="btn btn-danger" value="Next" onclick="next_page();" />
					</center>
				</div>';
			
			echo $html;
			
		}
		else if(brp_strtolower($POST['mode']) == "get_store_request_material_data") {
			/*echo "<pre>";
			print_r($POST);*/
			$p_id= $POST['p_ids'];
			$pid=$POST['pid'];
			$pid_wise_start_qty=$POST['pid_wise_start_qty'];
			$html="";
			
				$query1 = "select ap.p_id,ap.process_id,ap.p_qty,ap.pen_qty,p.product_name,req.job_card_no,req.job_card_date,smain.po_req_no as work_order_no,smain.po_req_date as work_order_date,umst.unit_name,req.rp_id as req_id, pr.process_name from tbl_allocate_process as ap
						left join product_mst as p on p.product_id=ap.p_product_id 
						left join process_mst as pr on pr.process_id=ap.process_id
						left join tbl_request_product req on req.rp_id=ap.p_ref_id
						left join tbl_set_main_process as smain on smain.sp_id=req.sp_id
						left join unit_mst as umst on umst.unitid=ap.process_unit
						where ap.p_id in (".$p_id.")" ;

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

				/*$query2 = "select trp.*,p.product_name from tbl_request_product as trp 
				left join product_mst as p on p.product_id=trp.rp_pid 
				left join tbl_allocate_process as ap on trp.rp_id=ap.p_ref_id 
				where trp.perent_id = " . $row['req_id'];*/

				$query2 = "select trp.*,p.product_name,bunit.unit_name from tbl_request_product as trp 
				left join product_mst as p on p.product_id=trp.rp_pid 
				left join tbl_allocate_process as ap on trp.rp_id=ap.p_ref_id 
				left join unit_mst as bunit on bunit.unitid=trp.process_unit
				where trp.perent_id = " . $row['req_id'];

				
				// echo $query2;
			
				$req_qty = $pid_wise_start_qty[$x];
				$result2=$dbcon->query($query2);
				$unitname = "";
				
				while($row2=brp_mysqli_fetch_array($result2)){
					$product_name = $row2['product_name'];
					$unitname = $row2['unit_name'];
					$total_qty = 0; 
					if (array_key_exists($product_name,$arr_total)){
						$total_qty = $arr_total[$product_name ];
					}

					$o_qty=convert_stock($dbcon,$row2["req_qty_one"],$row2['rp_pid'],"base_unit");
					$row2["req_qty_one"]=round($row2["req_qty_one"],6);
					
					$o_qty=round($o_qty,6);
					
					$total_req_qty=$req_qty*$o_qty;
					$total_req_qty=round($total_req_qty,4);
					$used_qty=$req_qty*$o_qty;
					$used_qty=round($used_qty,4);

					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$row2["product_name"].' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> '.$used_qty.' ' . $unitname .' </span>  </label>
								
							  </div></div>
							  ';
							  $total_qty = $total_qty + $used_qty;
							  
							  // echo "tota :" . $total_qty;
							$arr_total[$product_name] = $total_qty;
				}
				$x++;
				
			} 

			if($cnt > 0){
				$html .='<div class="col-md-12 bg-primary text-center control-label" style="margin-top:20px;">
				<label class="col-md-12 control-label" style="color: white;font-weight: 600; margin-top:10px;">Total Request Materials</label>
					
				</div>';
				foreach($arr_total as $key => $value){
					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$key.' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> '.$value.' ' . $unitname .' </span>  </label>
								
							  </div></div>
							  ';
				}
			}
			
			
			$html .='<div class="col-md-12 text-center" style="margin:25px 0;">
						<input type="button"  id="back_btn" name="back" class="btn btn-danger" value="Back" onclick="previous_page();" />
					</div>';
			
			echo $html;
			
		}
		
?>