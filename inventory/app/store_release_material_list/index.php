<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

$getspecialConfiguration=getspecialConfiguration($dbcon);

		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(brp_strtolower($POST['mode']) == "fetch_working") {
	/* $s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1]; */
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$check_branch = check_branch('ap', $branch_id);

	$where='';
	
	$appData = array();
	$i=1;
	$aColumns = array('p.product_name','p.product_icode','dr.drawing_number','req.job_card_no','ap.batch_no','smain.po_req_no as work_order_no','tsr.p_id','tsr.release_qty','tsr.store_release_id','tsr.release_type','rel.issue_no','rel.issue_date','req.job_card_date','smain.po_req_date as work_order_date','umst.unit_name','users.user_name','req.rp_id as req_id', 'pr.process_name','br.branch_name','tsr.store_aprv_log_id','tsr.user_id');
	$sIndexColumn = "tsr.store_release_id";
	$isWhere = array("ap.extra_stock=0 and tsr.company_id=".$_SESSION['company_id']." ".$check_branch);
	$sTable = "tbl_store_request_aprv_log as tsr";			
	$isJOIN = array('left join tbl_allocate_process as ap on ap.p_id=tsr.p_id 
				left join product_mst as p on p.product_id=ap.p_product_id 
				left join tbl_store_release as rel on rel.release_id=tsr.store_release_id 
				left join process_mst as pr on pr.process_id=ap.process_id 
				left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
				left join tbl_request_product req on req.rp_id=ap.p_ref_id 
				left join tbl_set_main_process as smain on smain.sp_id=req.sp_id 
				left join users as users on users.user_id=tsr.request_user_id 
				left join branch_mst as br on br.branch_id=tsr.branch_id 
				left join unit_mst as umst on umst.unitid=ap.process_unit');
		//$hGroupby = array("bom.bom_product");
	$hOrder = "tsr.store_aprv_log_id desc";
	include($include.'pagging.php');
	$appData = array();

	$id=1;
	foreach($sqlReturn as $rel) {
		$row_data = array();

		$return = "";
				// $view='<button class="btn btn-xs btn-primary" data-original-title="Material Details" data-toggle="tooltip" data-placement="top" onclick="get_material_data('. $rel['p_id'].','.$rel['release_qty'].','. $rel['store_release_id'].')"><i class="fa fa-eye"></i></button>';
				$print_btn='';
				$menusql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
				$rels=mysqli_fetch_assoc($menusql);
				$menu_show_permissions = explode(",",$rels['print_permission']);
				
				if($getspecialConfiguration['smpl_permission'] ==1)
				{
	                $sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = 23 AND approve_status = 1 AND status = 0 ORDER BY priority");
	                
	                while($res = mysqli_fetch_assoc($sql)){
						if(in_array($res['id'],$menu_show_permissions)) {
							$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$rel['store_aprv_log_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
							$quotation_link .= "'".$_SERVER['SERVER_NAME'].ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['quotation_id'].'?'.time()."'";
						}
					}
				}else{
				    $sql=$dbcon->query("SELECT * FROM print_setup_mst WHERE print_type = ".MATERIAL_ISSUE_PRINT." AND approve_status = 1 AND status = 0 ORDER BY priority");    
				    
			    	while($res = mysqli_fetch_assoc($sql)){
						if(in_array($res['id'],$menu_show_permissions)) {
							$print_btn.='<a class="btn btn-xs btn-primary" data-original-title="'.$res['print_name'].'" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.$res['page_path'].'/'.$rel['p_id'].'/'.$rel['store_release_id'].'?'.time().'" style="background: '.$res['icon_color'].'; border-color: '.$res['icon_color'].';"><i class="'.$res['fa_icon'].'"></i></a>';
							$quotation_link .= "'".$_SERVER['SERVER_NAME'].ROOT.PRINT_ROOT.$res['page_path'].'/'.$row['quotation_id'].'?'.time()."'";
						}
					}
				}

				if($rel['release_type'] == '0'){
					// $return='<a class="btn btn-xs btn-success" data-original-title="Return Materials" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'production_return_material/'.$rel['store_release_id'].'/'.$rel['release_type'].'")"><i class="fa fa-reply"></i></a>';
	
				}
				if($getspecialConfiguration['smpl_permission'] ==1)
				{
					
					
					$print_btn_ms='<a class="btn btn-xs btn-success" data-original-title="Return Materials" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.'msjobcard_smpl/'.$rel['store_release_id'].'")">MSJC</a>';
					$print_btn_nail='<a class="btn btn-xs btn-success" data-original-title="Return Materials" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.'nailjobcard_smpl/'.$rel['store_release_id'].'")">NAILJC</a>';
					$print_btn_tp='<a class="btn btn-xs btn-success" data-original-title="Return Materials" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.'tpplatjobcard_smpl/'.$rel['store_release_id'].'")">TPPJC</a>';
					$print_btn_ts='<a class="btn btn-xs btn-success" data-original-title="Return Materials" data-toggle="tooltip" data-placement="top" target="_blank" href="'.ROOT.PRINT_ROOT.'tsscrewjobcard_smpl/'.$rel['store_release_id'].'")">TSSJC</a>';
					
				}
					$branch_name = ($rel["branch_name"]!=null) ? $rel["branch_name"] : 'All Branch';	

					$row_data[] = $rel['sr'] ;
					$row_data[] = $rel['product_name'] . '-- ('.$rel['product_icode'].') -- ('.$rel['drawing_number'].')';
					$row_data[] = $rel['issue_no'];
					$row_data[] = $rel['issue_date'];
					$row_data[] = $rel['work_order_no'];
					$row_data[] = $rel['job_card_no'];
					$row_data[] = $rel['batch_no'];
					$row_data[] = $rel['release_qty'];
					$row_data[] =  find_user_name($dbcon,$rel['user_id']);
					if($_SESSION['branch_id']==0){
						$row_data[] = $branch_name;
					}
					$row_data[] = $view. ' '. $return . ' '.$print_btn_ms.''.$print_btn_nail.''.$print_btn_tp.''.$print_btn_ts.' '.$print_btn;
					
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}

		else if(brp_strtolower($POST['mode']) == "get_release_material_data") {
			$p_id=$POST['p_id'];
			$req_qty = $POST['req_qty'];
			$store_release_id = $POST['store_release_id'];
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

				$query2 = "select mtr.*,p.product_name from tbl_store_release_material_trn as mtr 
				left join product_mst as p on p.product_id=mtr.product_id 
				where mtr.release_id = " . $store_release_id;
				
				$result2=$dbcon->query($query2);

				while($row2=brp_mysqli_fetch_array($result2)){
					$product_name = $row2['product_name'];
					
					$html .= '<div class="col-md-12" style="margin-top:10px;">
							<div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Product Name : <span style="color: #0e8400;font-weight: 600;"> '.$row2["product_name"].' </span> </label>
								
							  </div>
							  <div class="col-md-6">
								<label class="col-md-12 control-label" style="color: #404040;font-weight: 600;">Request Qty : <span style="color: #0e8400;font-weight: 600;"> '.$row2['release_qty'].' </span>  </label>
								
							  </div>
							  </div>
							  ';
				}
			} 

			$html .='<div class="col-md-12 text-center" style="margin:25px;">
						<input type="button"  style="margin-left:10px" id="back_btn" name="back" class="btn btn-danger" value="Close" data-dismiss="modal" aria-hidden="true" />
					</div>';
			
			echo $html;
			
		}
		
?>
