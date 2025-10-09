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

	if(strtolower($POST['mode']) == "fetch") {
		$branch_id = $POST['branch_id'];
		$whr='';
	    if($branch_id){
	        $whr .= check_branch('zmst',$branch_id);
	    }

		//check paermission for annexure
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_PRODUCT_UPDATE,
	        ADMINISTRATOR_PRODUCT_DELETE,
			ADMINISTRATOR_PRODUCT_APPROVE,
			ADMINISTRATOR_PRODUCT_CLONE
	    ]);
		
		
		if($POST['fil_product_type']!=''){
			$whr.=' and product_type='.$POST['fil_product_type'];
		}
			
		$appData = array();
		$i=1;
		$aColumns = array('zmst.product_id', 'zmst.product_type', 'zmst.product_icode','zmst.product_name','zmst.product_alias_name', 'zmst.cdate',  'dr.drawing_number', 'zmst.product_status', 'zmst.user_id', 'zmst.image_name');
		$sIndexColumn = "product_id";
		$isWhere = array("zmst.product_status !=2 and zmst.company_id in (0,$_SESSION[company_id])".$whr);
		$sTable = "product_mst as zmst";			
		$isJOIN = array('left join tbl_drawing as dr on dr.drawing_id=zmst.drawing_id');
		$hOrder = "zmst.product_status desc ,zmst.product_name";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$query = "select * from tbl_check_master_delete_setting where isdelete=0 and master_name=".MST_ITEM_LIST;
			$tr = $dbcon -> query($query);
			$checkLang =0;
			while($row_use=brp_mysqli_fetch_array($tr)){
				$used_data = getCheckusedMaster($dbcon,$row_use['column_name'],$row_use['tbl_name'],$row_use['column_status'],$row_use['used_status'],$row['product_id']);
				$checkLang += $used_data;
			}
			if($checkLang>0){
				$status="<strong style='color:green'>Approved</strong>";
				$change_status = '';
			}else{
				if($row['product_status']==0)
				{  
					$status="<strong style='color:green'>Approved</strong>";
					$change_status="<a class='btn btn-xs btn-success' data-original-title='change approve status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-check-square-o'></i></a>";
				}
				else
				{
					$status="<strong style='color:red' >Pending</strong>"; 
					$change_status="<a class='btn btn-xs btn-danger' data-original-title='change approve status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-window-close'></i></a>";
				}
			}
			
			$row_data[] = $row['sr'];
			if($row['image_name']!=null){
				$row_data[] = '<a href="'.ROOT.'view/upload/product_images/'.$row["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$row['image_name'].'" style="width: 60px;height: 50px;"></a>';
			}else{
				$row_data[] = '';
			}
			
			$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
			$row_data[] = stripcslashes($row['product_name']); 
			$row_data[] = stripcslashes($row['product_alias_name']); 
			$row_data[] = $row['product_icode']; 
			/*$row_data[] = nl2br($row['product_desc']); */
			$row_data[] = $row['drawing_number']; 
			$row_data[] = $status; 
			
			$edit_btn='';$delete_btn='';$clone_btn='';
			if(in_array(ADMINISTRATOR_PRODUCT_UPDATE,$bulkAccessArray)){
				if ($getspecialConfiguration['interpower_permission'] == 1) {
					$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'product_edit_ip/'.$row['product_id'].'?'.time().'"><i class="fa fa-pencil"></i></a>';
				} else { 
					$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'product_edit/'.$row['product_id'].'?'.time().'"><i class="fa fa-pencil"></i></a>';
				 } 
			
			}
			if(in_array(ADMINISTRATOR_PRODUCT_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product('.$row['product_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
				if(in_array(ADMINISTRATOR_PRODUCT_CLONE,$bulkAccessArray)){
				$clone_btn=' <a class="btn btn-xs btn-warning" data-original-title="Clone" data-toggle="tooltip" data-placement="top" href="'.ROOT.ADMINISTRATION_ROOT.'product_clone/'.$row['product_id'].'"><i class="fa fa-clone"></i></a>';
			}
			
			if($row['product_id']=='2862'){//Fixed Product Type Service ID
				$delete_btn='';$change_status='';
			}
				
			if(in_array(ADMINISTRATOR_PRODUCT_APPROVE,$bulkAccessArray)){
				//$change_status='';
			}else{
				//$change_status='';
			}
				
			$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status. ''.$clone_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		//echo '<pre>';print_r($POST);exit;
		$getspecialConfiguration = getspecialConfiguration($dbcon);
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		
		$where="";
		if($getspecialConfiguration['chemitek_permission']==1){
			$where = ' and product_icode='.$info['product_icode'];
		}

		$tr = $dbcon -> query("SELECT `product_id`,`product_name`,`product_status`,`product_type` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$POST['product_name']."' and `company_id`='".$_SESSION['company_id']."' AND `product_type`='".$POST['product_type']."' AND `product_category`='".$POST['product_category']."'".$where);
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}else{

			$info['product_type']	= $POST['product_type'];							
			$info['ledger_id']		= $POST['ledger_id'];	//add pathik						
			$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$_POST['product_name']));							
			$info['product_alias_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$_POST['product_alias_name']));							
			$info['product_desc']	= $_POST['product_desc'];
            $info['product_spec']	= $_POST['product_spec'];
			$info['product_spec_id']= $_POST['specification_id1'];
			$info['product_icode']	= $_POST['product_icode'];							
			$info['product_hsn']= $POST['product_hsn'];		
			$info['rack_no']		= $POST['rack_no'];					
			$info['product_purchase_rate']= $POST['product_purchase_rate'];							
			$info['product_sale_rate']= $POST['product_sale_rate'];							
			$info['product_base_unit']= $POST['product_base_unit'];
			
			$info['product_base_qty']= $POST['product_base_qty'];
			$info['product_conv_unit']= $POST['product_conv_unit'];
			$info['product_conv_qty']= $POST['product_conv_qty'];
			
			$info['product_gst']= $POST['product_gst'];							
			$info['product_sale_gst']= $POST['product_sale_gst'];							
			$info['product_purchase_gst']= $POST['product_purchase_gst'];							
			$info['product_opening']= $POST['product_opening'];							
			$info['product_opening_valuation']= $POST['product_opening_valuation'];							
			$info['product_min_stock']= $POST['product_min_stock'];							
			$info['product_max_stock']= $POST['product_max_stock'];		
			/* jayesh add new fields 15-7-2021 */
			$info['product_min_order']= $POST['product_min_order'];
			$info['product_max_order']= $POST['product_max_order'];	
			/* jayesh add new fields 15-7-2021 */					
			$info['parent_category']  = $POST['parent_category'];							
			$info['product_category'] = $POST['product_category'];							
			$info['product_barcode']= $POST['product_barcode'];							
			$info['multi_branch']= $POST['multi_branch'];	

			if($POST['direct_product_add'] == 1){
				$info['product_status']= '0';
			}else{
				$info['product_status']= '1';
			}						
			//$info['product_status']= '1';							
			$info['count_stock']= $POST['count_stock'];							
			$info['product_making_time']= $POST['product_making_time'];	
			$info['product_lead_time']= $POST['product_lead_time'];								
			$info['product_check']= implode(",",$POST['product_check']);							
			$info['product_setting_check']= implode(",",$POST['product_setting_check']);							
			//$info['product_icode_code']= $POST['product_icode_code'];
			$info['product_width']= $POST['product_width'];				
			$info['product_height']= $POST['product_height'];				
			$info['product_thickness']= $POST['product_thickness'];				
			$info['product_density']= $POST['product_density'];				
			$info['product_kg']= $POST['product_kg'];				
			$info['product_specification']= $POST['product_specification'];
			$info['drawing_id']= $POST['drawing_id'];
			$info['revision_id']= $POST['revision_id'];
			$info['product_net_weight']= $POST['product_net_weight'];
			
							
			$info['cdate']					= date("Y-m-d H:i:s");
			$info['user_id']				= $_SESSION['user_id'];
			$info['company_id']				= $_SESSION['company_id'];
			$info['branch_id']				= $POST['branchid'];
			$info['tolerance']				= $POST['tolerance'];
			$info['minimum_tolerance']		= $POST['minimum_tolerance'];
			$info['maximum_tolerance']		= $POST['maximum_tolerance'];
			$info['enable_stockbalance']	= isset($POST['enable_stockbalance']) ? '1' : '0';
			$info['enable_negative_stock']	= isset($POST['enable_negative_stock']) ? '1' : '0';
			/* jayesh add new fields 15-7-2021 */
			$info['minimum_tolerance_value']= $POST['minimum_tolerance_value'];
			$info['maximum_tolerance_value']= $POST['maximum_tolerance_value'];	
			/* jayesh add new fields 15-7-2021 */
			
			$info['material_issue_weight']	= $POST['material_issue_weight'];
			$info['product_scrap_id']		= $POST['product_scrap_id'];
			$info['scrap_desc']				= $POST['scrap_desc'];
			$info['scrap_qty']				= $POST['scrap_qty'];
			
			/* jayesh add new fields 15-7-2021 */
			$info['is_grn']				= $POST['is_grn'];
			$info['reorder_qty']		= $POST['reorder_qty'];	
			$info['self_life_days']		= $POST['self_life_days'];
			$info['warrenty_period']	= $POST['warrenty_period'];
			$info['weight']				= $POST['weight'];
			$info['model_no']			= $POST['model_no'];
			$info['item_type']			= $POST['item_type'];
			$info['item_status']		= $POST['item_status'];
			$info['item_status_date']	= $POST['item_status_date'];
			$info['item_status_reason']	= $POST['item_status_reason'];
			$info['product_mat_center']	= $POST['product_mat_center'];
			$info['product_stock_count']= $POST['product_stock_count'];
			$info['bom_required']		= $POST['bom_required'];
			$info['cat_no']				= $POST['cat_no'];
			$info['iso_verify']				= $POST['iso_verify'];
			/* jayesh add new fields 15-7-2021 */	
			/* hardi add new fields 15-3-2022 */	

			$info['base_weight']		= $POST['base_weight'];
			$info['conv_weight']		= $POST['conv_weight'];

			$info['first_name_id']			= $POST['product_first_name'];
			$info['product_surface_area']	= $POST['product_surface_area'];
			$info['product_impregnation']	= $POST['product_impregnation'];
			$info['product_model_name']		= $POST['product_model_name'];
			$info['product_installation']	= $POST['product_installation'];
			$info['product_mst_type']		= $POST['product_mst_type'];
			$info['pro_mst_type']			= $POST['pro_mst_type'];
			$info['pro_cartridge_mst']		= $POST['pro_cartridge_mst'];
			$info['pro_class_mst']			= $POST['pro_class_mst'];

			
			// if($getspecialConfiguration['power_drive']==1){
				$query_dy = "select * from tbl_item_master_field where item_master_field_status=0 and company_id=".$_SESSION['company_id']." order by priority ASC";
				$dy_result = $dbcon->query($query_dy);
				while($dy_row =  brp_mysqli_fetch_array($dy_result)){
					$field = $dy_row['item_master_field_db_name'];
					$info_field[$field]			= $POST[$field];	 		
				}
			// }
			/* hardi add new fields 15-3-2022 */

			$info['batch_wise_stock_manage']= $POST['batch_wise_stock_manage']; /* sanat add new fields 16-11-2021 */	
			
			if($_FILES['image_name']['tmp_name']!=''){
				$image_name = check_document_type($dbcon,$_FILES['image_name']['name'],$_FILES['image_name']['tmp_name'],$path.'view/upload/product_images/');
				if($image_name['type'] != 'success'){	
					$resp['msg'] = "4";	
					echo json_encode($resp);		
				}else{
					$info['image_name']=$image_name['name'];
				}
			}
			$info['smpl_size']	= $_POST['smpl_size'];
			$info['smpl_material']	= $_POST['smpl_material'];
			//echo "<pre>"; print_r($info);
			//exit;
			//solid special field start
				$info['printing_material']		= $_POST['printing_material'];
				$info['printing_balty']			= $_POST['printing_balty'];
				$info['printing_req']			= $_POST['printing_req'];
				$info['extrusion_material']		= $_POST['extrusion_material'];
				$info['extrusion_size']			= $_POST['extrusion_size'];
				$info['mixing_batch_size']		= $_POST['mixing_batch_size'];
			//solid special filed end 

			// RB Special filed
			$info['r_make_id']		= $_POST['product_rb_make'];
			$info['r_make_name']	= $_POST['product_rb_make_name'];
			
			// print_r($info);exit;
			 $inserid=add_record('product_mst', $info, $dbcon, $branch_id);
			 
			//
			$info_project_update['project_assigntrn_status']	= 0;
			$info_project_update['project_assign_id']	= $inserid;
			$updateid=update_record('tbl_project_assigntrn', $info_project_update,"project_assigntrn_status=3 and user_id=".$_SESSION['user_id'] , $dbcon, $branch_id);

			// if($getspecialConfiguration['power_drive']==1){
				$info_field['product_id']				= $inserid;
				$info_field['cdate']					= date("Y-m-d H:i:s");
				$info_field['user_id']					= $_SESSION['user_id'];
				$info_field['company_id']				= $_SESSION['company_id'];

				//var_dump($info_field);exit;
				$inserfield = add_record('product_name_field', $info_field, $dbcon, $branch_id);
			// }
			if($inserid){

				// Insert Stock
				// add_branch_stock_at_submit($dbcon, $POST['bstock'], $POST['bid'], $inserid, $POST['bpriority'], $POST['product_base_unit']);
				
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"product_add",1,"product_mst",$inserid);
			
				$dbcon->query("update tbl_product_unit set unit_product='$inserid' WHERE unit_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_branch_product_stock set product_id='$inserid' WHERE product_id='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_images set im_product='$inserid' WHERE im_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_party_purchase set product_id='$inserid' WHERE product_id='0' and card_type=1 and  user_id='$_SESSION[user_id]'");

				$dbcon->query("update tbl_purchasecardtrn set product_id='$inserid' WHERE product_id='0' and  user_id='$_SESSION[user_id]'");

				$dbcon->query("update tbl_product_stage set party_product='$inserid' WHERE party_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_job_party_purchase set job_party_product='$inserid' WHERE job_party_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
				
				$dbcon->query("update tbl_product_process set product_id='$inserid' WHERE product_id=0 and user_id='$_SESSION[user_id]'");
				/*START JAYESH UPDATE ALTERNATIVE PRODUCT 20-07-2021*/
				$dbcon->query("update tbl_product_acc_product set product_id='$inserid' WHERE product_id=0 and user_id='$_SESSION[user_id]'");

				//pathik start 11/11/2021
					$dbcon->query("update tbl_product_parameter set product_id='$inserid' WHERE product_id=0 and user_id='$_SESSION[user_id]'");
				//pathik end 11/11/2021
				// Hardi start 20/1/2022
				$dbcon->query("update tbl_product_die_allocation set product_id='$inserid' WHERE product_id=0 and status=0 and user_id='$_SESSION[user_id]' and company_id='$_SESSION[company_id]'");
				// Hardi end 20/1/2022
				
				if(strtolower($POST['product_model']) == "product_model"){
					$zone_qry="select * from product_mst where product_id=".$inserid; 
					$zone_rel=brp_mysqli_fetch_assoc($dbcon->query($zone_qry));
					$resp=$zone_rel;
					$resp['msg'] = "3";
				}
				else
				{
					$resp['msg'] = "1";
					$resp['product_add_type'] = $POST['product_add_type']; 
					$resp['direct_product_add'] = $POST['direct_product_add'];
					$resp['product_name'] = $POST['product_name']; 
					$resp['inserid']=$inserid;
				}
				
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `product_mst` WHERE `product_id` = '$POST[id]'");
		$r = brp_mysqli_fetch_assoc($q);
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
			$getspecialConfiguration = getspecialConfiguration($dbcon);
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$qry = "select product_type from product_mst where product_id = " . $POST['eid_main'];
			$pro_row = brp_mysqli_fetch_array($dbcon->query($qry));


			$info['product_type']			= $POST['product_type'];
			$info['ledger_id']				= $POST['ledger_id'];	//add pathik		
			$info['product_name']			= stripcslashes(mysqli_real_escape_string($dbcon,$_POST['product_name']));							
			$info['product_alias_name']		= stripcslashes(mysqli_real_escape_string($dbcon,$_POST['product_alias_name']));	
			$info['product_desc']			= $_POST['product_desc'];
            $info['product_spec']			= $_POST['product_spec'];
			$info['product_spec_id']= $_POST['specification_id1'];
			$info['product_icode']			= $_POST['product_icode'];							
			$info['product_hsn']			= $POST['product_hsn'];							
			$info['product_purchase_rate']	= $POST['product_purchase_rate'];							
			$info['product_sale_rate']		= $POST['product_sale_rate'];							
			$info['product_base_unit']		= $POST['product_base_unit'];
			$info['rack_no']				= $POST['rack_no'];		

			$info['product_base_qty']		= $POST['product_base_qty'];
			$info['product_conv_unit']		= $POST['product_conv_unit'];
			$info['product_conv_qty']		= $POST['product_conv_qty'];
			
			$info['product_gst']			= $POST['product_gst'];							
			$info['product_sale_gst']		= $POST['product_sale_gst'];							
			$info['product_purchase_gst']	= $POST['product_purchase_gst'];							
			$info['product_opening']		= $POST['product_opening'];							
			$info['product_opening_valuation']= $POST['product_opening_valuation'];							
			$info['product_min_stock']		= $POST['product_min_stock'];
			$info['product_max_stock']		= $POST['product_max_stock'];	
			/* jayesh add new fields 15-7-2021 */
			$info['product_min_order']		= $POST['product_min_order'];
			$info['product_max_order']		= $POST['product_max_order'];	
			/* jayesh add new fields 15-7-2021 */			
			$info['product_category']		= $POST['product_category'];							
			$info['parent_category']		= $POST['parent_category'];							
			$info['product_barcode']		= $POST['product_barcode'];							
			$info['multi_branch']			= $POST['multi_branch'];							
			$info['count_stock']			= $POST['count_stock'];							
			$info['product_making_time']	= $POST['product_making_time'];	
			$info['product_lead_time']		= $POST['product_lead_time'];							
			$info['product_check']			= implode(",",$POST['product_check']);
			$info['product_setting_check']	= implode(",",$POST['product_setting_check']);		

			$info['product_width']			= $POST['product_width'];				
			$info['product_height']			= $POST['product_height'];				
			$info['product_thickness']		= $POST['product_thickness'];				
			$info['product_density']		= $POST['product_density'];				
			$info['product_kg']				= $POST['product_kg'];
			$info['product_specification']	= $POST['product_specification'];	
			$info['drawing_id']				= $POST['drawing_id'];
			$info['revision_id']			= $POST['revision_id'];	
			$info['product_net_weight']		= $POST['product_net_weight'];			

							
			$info['cdate']					= date("Y-m-d H:i:s");
			$info['user_id']				= $_SESSION['user_id'];
			$info['company_id']				= $_SESSION['company_id'];
			$info['branch_id']				= $POST['branchid'];
			
			$info['tolerance']				= $POST['tolerance'];
			$info['minimum_tolerance']		= $POST['minimum_tolerance'];
			$info['maximum_tolerance']		= $POST['maximum_tolerance'];
			$info['enable_stockbalance']	= isset($POST['enable_stockbalance']) ? '1' : '0';
			$info['enable_negative_stock']	= isset($POST['enable_negative_stock']) ? '1' : '0';
			
			/* jayesh add new fields 15-7-2021 */
			$info['minimum_tolerance_value']= $POST['minimum_tolerance_value'];
			$info['maximum_tolerance_value']= $POST['maximum_tolerance_value'];	
			/* jayesh add new fields 15-7-2021 */	
			
			$info['material_issue_weight']	= $POST['material_issue_weight'];
			$info['product_scrap_id']		= $POST['product_scrap_id'];
			$info['scrap_desc']				= $POST['scrap_desc'];
			$info['scrap_qty']				= $POST['scrap_qty'];
			
			/* jayesh add new fields 15-7-2021 */
			$info['is_grn']					= $POST['is_grn'];
			$info['reorder_qty']			= $POST['reorder_qty'];	
			$info['self_life_days']			= $POST['self_life_days'];
			$info['warrenty_period']		= $POST['warrenty_period'];
			$info['weight']					= $POST['weight'];
			$info['model_no']				= $POST['model_no'];
			$info['item_type']				= $POST['item_type'];
			$info['item_status']			= $POST['item_status'];
			$info['item_status_date']		= $POST['item_status_date'];
			$info['item_status_reason']		= $POST['item_status_reason'];
			$info['product_mat_center']		= $POST['product_mat_center'];
			$info['product_stock_count']	= $POST['product_stock_count'];
			$info['bom_required']			= $POST['bom_required'];
			$info['cat_no']					= $POST['cat_no'];
			$info['iso_verify']				= $POST['iso_verify'];
			/* jayesh add new fields 15-7-2021 */	
			/* hardi add new fields 15-3-2022 */
			$info['base_weight']		= $POST['base_weight'];
			$info['conv_weight']		= $POST['conv_weight'];

			$info['first_name_id']			= $POST['product_first_name'];
			$info['product_surface_area']	= $POST['product_surface_area'];
			$info['product_impregnation']	= $POST['product_impregnation'];
			$info['product_model_name']		= $POST['product_model_name'];
			$info['product_installation']	= $POST['product_installation'];
			$info['product_mst_type']		= $POST['product_mst_type'];
			$info['pro_mst_type']			= $POST['pro_mst_type'];
			$info['pro_cartridge_mst']		= $POST['pro_cartridge_mst'];
			$info['pro_class_mst']			= $POST['pro_class_mst'];
			/* hardi add new fields 15-3-2022 */
			$info['batch_wise_stock_manage']= $POST['batch_wise_stock_manage']; /* sanat add new fields 16-11-2021 */	


			// if($getspecialConfiguration['power_drive']==1){
				$query_dy = "select * from tbl_item_master_field where item_master_field_status=0 and company_id=".$_SESSION['company_id']." order by priority ASC";
				$dy_result = $dbcon->query($query_dy);
				while($dy_row =  brp_mysqli_fetch_array($dy_result)){
					$field = $dy_row['item_master_field_db_name'];
					$info_field[$field]			= $POST[$field];	 		
				}
			// }
			
			if($_FILES['image_name']['tmp_name']!=''){
				$image_name = check_document_type($dbcon,$_FILES['image_name']['name'],$_FILES['image_name']['tmp_name'],$path.'view/upload/product_images/');
				//print_r($image_name);exit;
					if($image_name['type'] != 'success'){	
					$resp['msg'] = "4";	
					echo json_encode($resp);		
				}else{
					$info['image_name']=$image_name['name'];
				}
			}
			$info['smpl_size']	= $_POST['smpl_size'];
			$info['smpl_material']	= $_POST['smpl_material'];

			//solid special field start
			$info['printing_material']		= $_POST['printing_material'];
			$info['printing_balty']			= $_POST['printing_balty'];
			$info['printing_req']			= $_POST['printing_req'];
			$info['extrusion_material']		= $_POST['extrusion_material'];
			$info['extrusion_size']			= $_POST['extrusion_size'];
			$info['mixing_batch_size']	= $_POST['mixing_batch_size'];

			// RB Special filed
			$info['r_make_id']		= $_POST['product_rb_make'];
			$info['r_make_name']	= $_POST['product_rb_make_name'];
			
			
			//solid special filed end 
			//var_dump($info['printing_req']);
			//echo "<pre>";print_r($info);echo "</pre>";
			$updateid=update_record('product_mst', $info,"product_id=".$POST['eid_main'] , $dbcon, $branch_id);


			// if($getspecialConfiguration['power_drive']==1){
				$info_field['product_id']				= $POST['eid_main'];
				$info_field['cdate']					= date("Y-m-d H:i:s");
				$info_field['user_id']					= $_SESSION['user_id'];
				$info_field['company_id']				= $_SESSION['company_id'];
				
				$updatefieldid=update_record('product_name_field', $info_field,"product_id=".$POST['eid_main'] , $dbcon, $branch_id);
			// }

			if($updateid){
				if($pro_row['product_type'] != $POST['product_type']){
					$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
				}
			}
			
			// add_branch_stock_at_submit($dbcon, $POST['bstock'], $POST['bid'], $POST['eid_main'], $POST['bpriority'], $POST['product_base_unit']);
			//$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"product_add",2,"product_mst",$POST['eid_main']);
				
			$resp['msg'] = "2";
			
			echo json_encode($resp);
	}
	/*START JAYESH PRODUCT CLONE 17-06-2021*/
	else if(strtolower($POST['mode']) == "clone") {
		//echo "<pre>"; print_r($POST);die;
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		
		$tr = $dbcon -> query("SELECT `product_id`,`product_name`,`product_status`,`product_type` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$POST['product_name']."' and `company_id`='".$_SESSION['company_id']."' AND `product_type`='".$POST['product_type']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			
			$info['product_type']	= $POST['product_type'];							
			$info['ledger_id']		= $POST['ledger_id'];	//add pathik						
			$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$_POST['product_name']));							
			$info['product_alias_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$_POST['product_alias_name']));
			$info['product_desc']	= $_POST['product_desc'];
            $info['product_icode']	= $_POST['product_icode'];							
			$info['product_hsn']= $POST['product_hsn'];							
			$info['product_purchase_rate']= $POST['product_purchase_rate'];							
			$info['product_sale_rate']= $POST['product_sale_rate'];							
			$info['product_base_unit']= $POST['product_base_unit'];			
			$info['product_base_qty']= $POST['product_base_qty'];
			$info['product_conv_unit']= $POST['product_conv_unit'];
			$info['product_conv_qty']= $POST['product_conv_qty'];			
			$info['product_gst']= $POST['product_gst'];							
			$info['product_sale_gst']= $POST['product_sale_gst'];							
			$info['product_purchase_gst']= $POST['product_purchase_gst'];							
			$info['product_opening']= $POST['product_opening'];							
			$info['product_opening_valuation']= $POST['product_opening_valuation'];							
			$info['product_min_stock']= $POST['product_min_stock'];							
			$info['product_max_stock']= $POST['product_max_stock'];		
			$info['product_min_order']= $POST['product_min_order'];
			$info['product_max_order']= $POST['product_max_order'];	
			$info['product_category']= $POST['product_category'];							
			$info['parent_category'] = $POST['parent_category'];							
			$info['product_barcode']= $POST['product_barcode'];							
			$info['multi_branch']= $POST['multi_branch'];							
			$info['product_status']= '1';							
			$info['count_stock']= $POST['count_stock'];							
			$info['product_making_time']= $POST['product_making_time'];	
			$info['product_lead_time']= $POST['product_lead_time'];							
			$info['product_check']= implode(",",$POST['product_check']);							
			$info['product_setting_check']= implode(",",$POST['product_setting_check']);							
			$info['product_width']= $POST['product_width'];				
			$info['product_height']= $POST['product_height'];				
			$info['product_thickness']= $POST['product_thickness'];				
			$info['product_density']= $POST['product_density'];				
			$info['product_kg']= $POST['product_kg'];				
			$info['product_specification']= $POST['product_specification'];
			$info['drawing_id']= $POST['drawing_id'];
			$info['revision_id']= $POST['revision_id'];
			$info['product_net_weight']= $POST['product_net_weight'];
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $POST['branchid'];
			$info['tolerance']	= $POST['tolerance'];
			$info['minimum_tolerance']	= $POST['minimum_tolerance'];
			$info['maximum_tolerance']	= $POST['maximum_tolerance'];			
			$info['minimum_tolerance_value']= $POST['minimum_tolerance_value'];
			$info['maximum_tolerance_value']= $POST['maximum_tolerance_value'];	
			$info['material_issue_weight']	= $POST['material_issue_weight'];
			$info['product_scrap_id']		= $POST['product_scrap_id'];
			$info['scrap_desc']				= $POST['scrap_desc'];
			$info['scrap_qty']				= $POST['scrap_qty'];
			$info['is_grn']= $POST['is_grn'];
			$info['reorder_qty']= $POST['reorder_qty'];	
			$info['self_life_days']= $POST['self_life_days'];
			$info['warrenty_period']= $POST['warrenty_period'];
			$info['weight']= $POST['weight'];
			$info['model_no']= $POST['model_no'];
			$info['item_type']= $POST['item_type'];
			$info['item_status']= $POST['item_status'];
			$info['item_status_date']= date("Y-m-d",strtotime($POST['item_status_date']));
			$info['item_status_reason']= $POST['item_status_reason'];
			$info['product_mat_center']= $POST['product_mat_center'];
			$info['product_stock_count']= $POST['product_stock_count'];
			/* hardi add new fields 15-3-2022 */	

			$info['base_weight']		= $POST['base_weight'];
			$info['conv_weight']		= $POST['conv_weight'];

			$info['first_name_id']			= $POST['product_first_name'];
			$info['product_surface_area']	= $POST['product_surface_area'];
			$info['product_impregnation']	= $POST['product_impregnation'];
			$info['product_model_name']		= $POST['product_model_name'];
			$info['product_installation']	= $POST['product_installation'];
			$info['product_mst_type']		= $POST['product_mst_type'];
			$info['pro_mst_type']			= $POST['pro_mst_type'];
			$info['pro_cartridge_mst']		= $POST['pro_cartridge_mst'];
			$info['pro_class_mst']			= $POST['pro_class_mst'];
			/* hardi add new fields 15-3-2022 */
			/*$info['item_code_generate']= $POST['item_code_generate'];
			$info['common_item_diff_company']= $POST['common_item_diff_company'];
			$info['party_code_generate']= $POST['party_code_generate'];
			$info['multiple_make_item_master']= $POST['multiple_make_item_master'];*/
			
			if($_FILES['image_name']['tmp_name']!=''){
				$image_name = check_document_type($dbcon,$_FILES['image_name']['name'],$_FILES['image_name']['tmp_name'],$path.'view/upload/product_images/');
					if($image_name['type'] != 'success'){	
					$resp['msg'] = "4";	
					echo json_encode($resp);die;	
				}else{
					$info['image_name']=$image_name['name'];
				}
			}
			else
			{
				$tr = $dbcon -> query("SELECT `product_id`,`product_name`,`product_status`,`image_name` FROM `product_mst` WHERE  `product_id` ='".$POST['pid']."' ");
				$pi_row = brp_mysqli_fetch_array($tr);
				$image_name = $pi_row['image_name'];
			}	
			
			 $inserid=add_record('product_mst', $info, $dbcon, $branch_id);
		
			if($inserid){
				// Insert Stock
				// add_branch_stock_at_submit($dbcon, $POST['bstock'], $POST['bid'], $inserid, $POST['bpriority'], $POST['product_base_unit']);
				
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"product_add",1,"product_mst",$inserid);
				
				/* START JAYESH 16-7-2021 Multiple  tab insert data */
				clone_items_add_multiple_tabbing_data($dbcon,$inserid,$POST['eid_main']);
				
				if(isset($image_name))
				{
				$dbcon->query("update product_mst set image_name='$image_name' WHERE product_id='$inserid' and  user_id='$_SESSION[user_id]'");
				}
				/* END JAYESH 16-7-2021 Multiple  tab insert data */
				
				$dbcon->query("update tbl_product_unit set unit_product='$inserid' WHERE unit_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_branch_product_stock set product_id='$inserid' WHERE product_id='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_images set im_product='$inserid' WHERE im_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_party_purchase set party_product='$inserid' WHERE party_product='0' and  user_id='$_SESSION[user_id]'");

				$dbcon->query("update tbl_product_stage set party_product='$inserid' WHERE party_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_job_party_purchase set job_party_product='$inserid' WHERE job_party_product='0' and  user_id='$_SESSION[user_id]'");
				
				$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");
				
				$dbcon->query("update tbl_product_process set product_id='$inserid' WHERE product_id=0 and user_id='$_SESSION[user_id]'");						
				
				if(strtolower($POST['product_model']) == "product_model"){
					$zone_qry="select * from product_mst where product_id=".$inserid; 
					$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
					$resp=$zone_rel;
					$resp['msg'] = "3";
				}
				else
				{
					$resp['msg'] = "1";
				}
				
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	/*START JAYESH PRODUCT CLONE 17-06-2021*/
	else if(strtolower($POST['mode']) == "delete") {
		
		$product_id = $POST['eid'];
		// $sTable = array(TABLE_COMPLAINT_TRN=>'COMPLAINT MODULE',TABLE_BOM_TRN=>'BOM MODULE',TABLE_INQUIRY_TRN=>'INQUIRY MODULE');
		// $aColumns = array(array('product_id'),array('product_id'),array('product_id'));
		// $sWhere = array(array('complaint_trn_status=0 and product_id = "'.$product_id.'"'),
		//  	array('bom_trn_status=0 and product_id = "'.$product_id.'"'),
		//  	array('inquiry_trn_status=0 and product_id = "'.$product_id.'"'));
		// $checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);

		$query = "select * from tbl_check_master_delete_setting where isdelete=0 and master_name=".MST_ITEM_LIST;
		$tr = $dbcon -> query($query);
		$checkLang =0;
		while($row=brp_mysqli_fetch_array($tr)){
			$used_data = getCheckusedMaster($dbcon,$row['column_name'],$row['tbl_name'],$row['column_status'],$row['used_status'],$product_id);
			$checkLang += $used_data;
		}
		if($checkLang > 0){
			$resp['msg'] = '-1';
			//$resp['table'] = $checkLang;
		}else{
			$info['product_status']='2';
			$updateid=update_record('product_mst', $info,"product_id=".$POST['eid'] , $dbcon);
			
			$info_st['stock_status']=2;
			$updateid_stock=update_record("tbl_stock_trn", $info_st,"product_id=".$POST['eid']." and ref_name='opening_stock'" , $dbcon);

			//Delete For Allocate product stock - Maulik
			$pro_stock['status'] = 2;
			$updateid_stock=update_record("tbl_branch_product_stock", $pro_stock,"product_id=".$POST['eid'] , $dbcon);
			//End
			//Delete For Allocate Product Image - Maulik
			$pro_img['im_status'] = 2;
			$updateid_stock=update_record("tbl_product_images", $pro_img,"im_product=".$POST['eid'] , $dbcon);
			//End

			//Delete For Allocate Purchase Party - Maulik 
			$pro_pur_party['card_status'] = 2;
			$updateid_stock=update_record("tbl_product_party_purchase", $pro_pur_party,"party_product=".$POST['eid'], $dbcon);
			//End

			
 
			//Delete For Allocate Jobwork Party - Maulik 
			
			delete_record('tbl_product_job_party_purchase', "job_party_product=$POST[eid]", $dbcon);
			//End

			//Delete For Allocate Product Process - Maulik
			$pr_del_info['status'] = 2;
			// delete_record('tbl_product_process', "product_id=$POST[eid]", $dbcon);
			update_record("tbl_product_process", $pr_del_info,"product_id=".$POST['eid'], $dbcon);
			//End

			//Delete For Allocate Qc Parameter - Maulik 
			delete_record('tbl_product_parameter', "product_id=$POST[eid]", $dbcon);
			//End

			//Delete For Allocate Make - Maulik 
			delete_record('tbl_product_make_purchase', "make_product=$POST[eid]", $dbcon);
			//End
			delete_record('tbl_product_die_allocation', "product_id=$POST[eid]", $dbcon);
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"product_add",3,"product_mst",$POST['eid']);
			
			if($updateid)
				$resp['msg'] = '1';
			else
				$resp['msg'] = '0'; 
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "add_unit_converter") {
			
			$info1['unit_alt_qty']= $POST['utab_alt_qty'];
			$info1['unit_alt_unit']= $POST['utab_alt_unit'];
			$info1['unit_basic_qty']= $POST['utab_basic_qty'];
			$info1['unit_basic_unit']= $POST['utab_basic_unit'];
			$info1['unit_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_unit';$tableid='unit_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_unit_converter") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,unit.unit_name as uname,unit1.unit_name as uname1 from tbl_product_unit as mst 
				left join unit_mst as unit on unit.unitid=mst.unit_alt_unit  left join unit_mst as unit1 on unit1.unitid=mst.unit_basic_unit
				where mst.unit_product='$POST[product_id]' order by unit_id Desc";
			}
			else{
				$query="select mst.*,unit.unit_name as uname,unit1.unit_name as uname1 from tbl_product_unit as mst 
				left join unit_mst as unit on unit.unitid=mst.unit_alt_unit  left join unit_mst as unit1 on unit1.unitid=mst.unit_basic_unit
				where mst.user_id=".$_SESSION['user_id']." and mst.unit_product='0' order by unit_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Alt Qty.</th>
							<th width="10%" class="text-center">Alt Unit</th>
							<th width="15%" class="text-center">Base Qty.</th>
							<th width="15%" class="text-center">Base Unit</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['unit_alt_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['uname'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['unit_basic_qty'].'
						</td>
						<td style="vertical-align:top;" class="text-right">
							'.$rel['uname1'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_unit('.$rel['unit_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_unit('.$rel['unit_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "preedit_unit")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_unit WHERE unit_id	= '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_unit")
		{
			
			$deleteid=delete_record('tbl_product_unit', "unit_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
	
		else if(strtolower($POST['mode']) == "add_branch_stock") {
			
			$bstock=$POST['bstock'];
			$bid=$POST['bid'];
			$form_mode=$POST['form_mode'];
			$pid=$POST['pid'];
			$bpriority=$POST['bpriority'];
			$total_opening_stock=0;
			for($i=0;$i<count($bstock);$i++)
			{
				$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='$bid[$i]' and product_id='$pid'");
				$count=mysqli_num_rows($q);
				$roq=brp_mysqli_fetch_assoc($q);
				
				$info['product_stock']=$bstock[$i];
				$info['branch_id']=$bid[$i];
				$info['priority']=$bpriority[$i];
				$info['user_id']=$_SESSION['user_id'];
				$info['cdate']=date("Y-m-d h:i:s");
				$info['company_id']=$_SESSION['company_id'];
				//var_dump($info);
				$table='tbl_branch_product_stock';$tableid='branch_product_stock_id';
				
				if($count>0)
				{
					$updateid=update_record($table, $info,"branch_id='$bid[$i]' and product_id='$pid'", $dbcon);
					$ref_id=$roq["branch_product_stock_id"];			
				}else{
					
					if($pid>0)
					{
						$info['product_id']=$pid;
					}
					if(!empty($info['product_stock'])){
						if($info['product_stock']!="0.00"){
							$inserid=add_record($table, $info, $dbcon);
						}
					}
					$ref_id=$inserid;
				}
				$date1=date("Y-m-d");
				$ref_name="opening_stock";
				$info_st['stock_status']=2;
					$updateid_stock=update_record("tbl_stock_trn", $info_st,"godown_id=".$info['branch_id']." and product_id=".$pid." and ref_name='".$ref_name."'" , $dbcon);
				if(!empty($info['product_stock'])){
					if($info['product_stock']!="0.00"){
						add_stock($dbcon,$pid,$POST['unit_id'],$date1,$ref_name,$ref_id,$info['branch_id'],$info['product_stock'],1);
					}
				}
				$total_opening_stock=$total_opening_stock+$info['product_stock'];
			}
			//print_r($bid);
			echo $total_opening_stock;
			
		}
		else if(strtolower($POST['mode']) == "add_product_image_temp") {
			
			
			$count_uploaded_files = count( $_FILES['file']['name'] );
						
			 for( $i = 0; $i < $count_uploaded_files; $i++ )
   			 {
   			 	
   			 	$image_name = check_document_type($dbcon,$_FILES['file']['name'][$i],$_FILES['file']['tmp_name'][$i],$path.'view/upload/product_images/');
   			  	if($image_name['type'] != 'success'){					
					echo "-1";	die;		
				}else{
					$info1['im_name']=$image_name['name'];}
			 $info1['cdate']=date("Y-m-d");
			 $info1['user_id']			= $_SESSION['user_id'];
			 $info1['branch_id']			= $POST['branchid'];
			 $info1['im_product']			= $POST['pid'];
			
			 $table='tbl_product_images';$tableid='img_id';
			 $inserid=add_record($table, $info1, $dbcon);
			 
			 }
			 echo get_images_product($dbcon,'0');			
		}
		
		else if(strtolower($POST['mode']) == "load_product_images") {
			
			if((strtolower($POST['form_mode']) == "edit")|| (strtolower($POST['form_mode']) == "clone")){
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='$POST[product_id]' and im_status=0 order by img_id Desc";
			}
			else{
				
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='0' and im_status=0 order by img_id Desc";
			}	
				$rel=$dbcon->query($q);
				$path='view/upload/product_images/';
				$str="";
				$str.="<table></tr>";
				while($row  = brp_mysqli_fetch_assoc($rel))
				{
					$str.='<td>
					"'.DOMAIN.$path.$row['im_name'].'"
						<a onclick="delete_data_image('.$row['img_id'].');" href="#">
							<div class="img-wrap">
								<span class="close">&times;</span>
								<img src="'.ROOT.'view/img/close_img.jpg" width="30" height="30" class="img-thumbnail">
							</div>
							<img src="'.ROOT.$path.$row['im_name'].'" height="150" width="225" class="img-thumbnail" />
						</a>
					</td>';
				}
				$str.="</tr></table>";
				echo $str;
		}
		else if(strtolower($POST['mode'])== "delete_data_image")
		{
			
			$deleteid=delete_record('tbl_product_images', "img_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Party Purchase
		
		else if(strtolower($POST['mode']) == "add_party_purchase") {
			
			if(empty($POST['pid']) && empty($POST['edit_id'])){
				$que_card = "select * from tbl_product_party_purchase where card_status=0 and card_type=1 and party_id=0 and product_id=0 and company_id=".$_SESSION['company_id'];
				$result=$dbcon->query($que_card);
				$cnt = brp_mysqli_num_rows($result);
				$fetch_card = brp_mysqli_fetch_array($result);

				if($cnt > 0){
					$info_trn['party_purchase_id'] = $fetch_card['party_purchase_id'];
					$info_trn['vendor_id']			= $POST['party_id'];
					$info_trn['price']				= $POST['party_rate'];
					$info_trn['rate_tolerance'] 	= $POST['rate_tolerance'];
					$info_trn['discount_percentage']= $POST['discount_percentage'];
					$info_trn['affected_date']		= date('Y-m-d',strtotime($POST['affected_date']));
					$info_trn['valid_date'] 		= date('Y-m-d',strtotime($POST['valid_date']));
					$info_trn['quotation_number'] 	= $POST['quotation_no'];
					$info_trn['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
					$info_trn['user_id']			= $_SESSION['user_id'];
					$info_trn['company_id']			= $_SESSION['company_id'];
					$info_trn['cdate']				= date("Y-m-d H:i:s");
				}else{
					$purchase_card_no =load_common_no($dbcon,22);
					update_common_no($dbcon,22);
					$info['card_type'] 		= 1;
					$info['pur_card_no'] 	= $purchase_card_no;
					$info['pur_card_date']	= date('Y-m-d');
					$info['product_id']		= 0;
					$info['party_id']		= 0;
					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];

					$inserpoid=add_record('tbl_product_party_purchase', $info, $dbcon, $branch_id);


					$info_trn['party_purchase_id'] = $inserpoid;
					$info_trn['vendor_id']			= $POST['party_id'];
					$info_trn['price']				= $POST['party_rate'];
					$info_trn['rate_tolerance'] 	= $POST['rate_tolerance'];
					$info_trn['discount_percentage']= $POST['discount_percentage'];
					$info_trn['affected_date']		= date('Y-m-d',strtotime($POST['affected_date']));
					$info_trn['valid_date'] 		= date('Y-m-d',strtotime($POST['valid_date']));
					$info_trn['quotation_number'] 	= $POST['quotation_no'];
					$info_trn['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
					$info_trn['user_id']			= $_SESSION['user_id'];
					$info_trn['company_id']			= $_SESSION['company_id'];
					$info_trn['cdate']				= date("Y-m-d H:i:s");
				}
				
			}else if(!empty($POST['pid']) && empty($POST['edit_id'])){
				$que_card = "select * from tbl_product_party_purchase where card_status=0 and card_type=1 and party_id=0 and product_id=".$POST['pid']." and company_id=".$_SESSION['company_id'];
				$result=$dbcon->query($que_card);
				$cnt = brp_mysqli_num_rows($result);
				$fetch_card = brp_mysqli_fetch_array($result);

				if($cnt > 0){
					$info_trn['party_purchase_id'] 	= $fetch_card['party_purchase_id'];
					$info_trn['product_id']			= $POST['pid'];
					$info_trn['vendor_id']			= $POST['party_id'];
					$info_trn['price']				= $POST['party_rate'];
					$info_trn['rate_tolerance'] 	= $POST['rate_tolerance'];
					$info_trn['discount_percentage']= $POST['discount_percentage'];
					$info_trn['affected_date']		= date('Y-m-d',strtotime($POST['affected_date']));
					$info_trn['valid_date'] 		= date('Y-m-d',strtotime($POST['valid_date']));
					$info_trn['quotation_number'] 	= $POST['quotation_no'];
					$info_trn['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
					$info_trn['user_id']			= $_SESSION['user_id'];
					$info_trn['company_id']			= $_SESSION['company_id'];
					$info_trn['cdate']				= date("Y-m-d H:i:s");
				}else{
					$purchase_card_no =load_common_no($dbcon,22);
					update_common_no($dbcon,22);
					$info['card_type'] 		= 1;
					$info['pur_card_no'] 	= $purchase_card_no;
					$info['pur_card_date']	= date('Y-m-d');
					$info['product_id']		= $POST['pid'];
					$info['party_id']		= 0;
					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['company_id']		= $_SESSION['company_id'];

					$inserpoid=add_record('tbl_product_party_purchase', $info, $dbcon, $branch_id);


					$info_trn['party_purchase_id'] = $inserpoid;
					$info_trn['vendor_id']			= $POST['party_id'];
					$info_trn['product_id']			= $POST['pid'];
					$info_trn['price']				= $POST['party_rate'];
					$info_trn['rate_tolerance'] 	= $POST['rate_tolerance'];
					$info_trn['discount_percentage']= $POST['discount_percentage'];
					$info_trn['affected_date']		= date('Y-m-d',strtotime($POST['affected_date']));
					$info_trn['valid_date'] 		= date('Y-m-d',strtotime($POST['valid_date']));
					$info_trn['quotation_number'] 	= $POST['quotation_no'];
					$info_trn['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
					$info_trn['user_id']			= $_SESSION['user_id'];
					$info_trn['company_id']			= $_SESSION['company_id'];
					$info_trn['cdate']				= date("Y-m-d H:i:s");	
				}
			}else{
				$info_trn['product_id']			= $POST['pid'];
				$info_trn['price']				= $POST['party_rate'];
				$info_trn['rate_tolerance'] 	= $POST['rate_tolerance'];
				$info_trn['discount_percentage']= $POST['discount_percentage'];
				$info_trn['affected_date']		= date('Y-m-d',strtotime($POST['affected_date']));
				$info_trn['valid_date'] 		= date('Y-m-d',strtotime($POST['valid_date']));
				$info_trn['quotation_number'] 	= $POST['quotation_no'];
				$info_trn['quotation_date']		= date('Y-m-d',strtotime($POST['quotation_date']));
				$info_trn['user_id']			= $_SESSION['user_id'];
				$info_trn['company_id']			= $_SESSION['company_id'];
				$info_trn['cdate']				= date("Y-m-d H:i:s");
			}

			
			/*$info1['party_id']			= $POST['party_id'];
			$info1['party_rate']		= $POST['party_rate'];
			$info1['party_product']		= $POST['pid'];
			
			$info1['cdate'] 			= date("Y-m-d H:i:s");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];*/
			//var_dump($info1);
			$table='tbl_purchasecardtrn';$tableid='purchasecardtrn_id';
			
			if(empty($POST['edit_id']))
			{
				if(empty($POST['pid'])){
					$query_trn = "select * from tbl_purchasecardtrn where valid_date>='".$info_trn['valid_date']."' and purchasecardtrn_status=0 and product_id=0 and vendor_id=".$info_trn['vendor_id']." and company_id=".$_SESSION['company_id'];
				}else{
					$query_trn = "select * from tbl_purchasecardtrn where valid_date>='".$info_trn['valid_date']."' and purchasecardtrn_status=0 and product_id=".$POST['pid']." and vendor_id=".$info_trn['vendor_id']." and company_id=".$_SESSION['company_id'];
				}
				$result1=$dbcon->query($query_trn);
				$cntrn = brp_mysqli_num_rows($result1);
				if($cntrn>0){
					$arr['msg']="-1";
				}else{
					$inserid=add_record($table, $info_trn, $dbcon);
					if($inserid){
						$arr['msg']="1";
					}else{
						$arr['msg']="0";
					}
				}
			}
			else
			{
				if(empty($POST['pid'])){
					$query_trn = "select * from tbl_purchasecardtrn where valid_date>='".$info_trn['valid_date']."' and purchasecardtrn_status=0 and product_id=0 and vendor_id=".$info_trn['vendor_id']." and company_id=".$_SESSION['company_id']." and purchasecardtrn_id !=".$POST['edit_id'];
				}else{
					$query_trn = "select * from tbl_purchasecardtrn where valid_date>='".$info_trn['valid_date']."' and purchasecardtrn_status=0 and product_id=".$POST['pid']." and vendor_id=".$info_trn['vendor_id']." and company_id=".$_SESSION['company_id']." and purchasecardtrn_id !=".$POST['edit_id'];
				}
				
				$result=$dbcon->query($query_trn);
				$cntrn = brp_mysqli_num_rows($result);
				if($cntrn>0){
					$arr['msg']="-1";
				}else{
					$inserid=update_record($table, $info_trn,$tableid."=".$POST['edit_id'] , $dbcon);	
					if($inserid){
						$arr['msg']="1";
					}else{
						$arr['msg']="0";
					}
				}
			}
			
			echo json_encode($arr);
		}

		// Alternative Product START JAYESH DATe:20-07-2016 	
		
		else if(strtolower($POST['mode']) == "add_accessories_product") {
			
			$info1['acc_product_id']		= $POST['acc_product_id'];
			$info1['product_id']			= $POST['pid'];		
			$info1['acc_product_qty']		= $POST['acc_product_qty'];		
			$info1['acc_product_desc']		= text_rnremove($_POST['acc_product_desc']);
			$info1['cdate'] 				= date("Y-m-d H:i:s");
			$info1['user_id']				= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']				= $POST['branchid'];
			
			$table='tbl_product_acc_product';$tableid='acc_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		
	
		
		else if(strtolower($POST['mode']) == "load_accessories_product") {
			
			/* START JAYESH fetch clone data */
			if((strtolower($POST['form_mode']) == "edit") || (strtolower($POST['form_mode']) == "clone")){
				$query="select mst.*,p.product_name from tbl_product_acc_product as mst 
				left join  product_mst as p on p.product_id=mst.acc_product_id where mst.product_id='$POST[product_id]' order by mst.acc_id Desc";
			}
			else{
				 $query="select mst.*,p.product_name from tbl_product_acc_product as mst 
				left join  product_mst as p on p.product_id=mst.acc_product_id where mst.user_id=".$_SESSION['user_id']." and mst.product_id='0' order by mst.acc_id Desc";
				}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Product Name</th>
							<th width="20%" class="text-center">Qty</th>
							<th width="20%" class="text-center">Description</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['product_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['acc_product_qty'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['acc_product_desc'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_accessories_product('.$rel['acc_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_accessories_product('.$rel['acc_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
			echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		
		// End JAYESH 
		
		/*Code By Umair: 28/11/2020
		Comment: Add Make Info
		*/
		else if(strtolower($POST['mode']) == "add_make_request") {

			$info1['make_id']= $POST['make_id'];
			$info1['make_number_id']= $POST['make_number_id'];
			$info1['make_value']= $POST['make_value'];
			$info1['make_stock']= $POST['make_stock'];
			$info1['make_rate']= $POST['make_rate'];
			$info1['make_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_make_purchase';$tableid='make_purchase_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}
		else if(strtolower($POST['mode']) == "add_scrap_request") {

			$info1['scrap_code_id']= $POST['product_scrap_id'];
			$info1['material_issue_weight']= $POST['material_issue_weight'];
			$info1['scrap_product']= $POST['pid'];
			
			$info1['cdate'] 			= date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']		= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_scrap';$tableid='scrap_product_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}

		else if(strtolower($POST['mode']) == "add_product_stage") {
			$pid=$POST['pid'];
			// "select sum(stage_per) as tot_perc from tbl_product_stage where party_product='$pid'";
			


			$info1['stage_id']= $POST['party_stage_id'];
			$info1['stage_per']= $POST['stage_per'];
			$info1['party_product']= $POST['pid'];
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id'] = $_SESSION['user_id'];
			$info1['company_id']= $_SESSION['company_id'];
			$table='tbl_product_stage';$tableid='product_stage_id';
			if(empty($POST['edit_id_product_stage']))
			{
				$q=$dbcon->query("select sum(stage_per) as tot_perc from tbl_product_stage where party_product='$pid'");
					$count=mysqli_num_rows($q);
					$roq=brp_mysqli_fetch_assoc($q);
				$tot_per=$roq['tot_perc'];
				$all_per=$tot_per+$POST['stage_per'];
				if($all_per>100){
					echo '-1';
					exit;
				}
				$inserid=add_record($table,$info1,$dbcon);
			}
			else
			{
				$editid=$POST['edit_id_product_stage'];
				$q=$dbcon->query("select sum(stage_per) as tot_perc from tbl_product_stage where party_product='$pid' and product_stage_id!='$editid'");
				$count=mysqli_num_rows($q);
				$roq=brp_mysqli_fetch_assoc($q);
				$tot_per=$roq['tot_perc'];
				$all_per=$tot_per+$POST['stage_per'];
			if($all_per>100){
				echo '-1';
				exit;
			}
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id_product_stage'] , $dbcon);	
			}
           echo "1";
		}
		else if(strtolower($POST['mode']) == "load_stage_purchase") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,sm.stage_name from tbl_product_stage as mst 
				left join stage_mst as sm on sm.stage_id=mst.stage_id where mst.party_product='$POST[product_id]' order by mst.product_stage_id Desc";
			}
			else{
				$query="select mst.*,sm.stage_name from tbl_product_stage as mst 
				left join stage_mst as sm on sm.stage_id=mst.stage_id where mst.user_id=".$_SESSION['user_id']." and mst.party_product='0' order by mst.product_stage_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Stage</th>
							<th width="10%" class="text-center">Percentage</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['stage_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['stage_per'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_stage('.$rel['product_stage_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_stage('.$rel['product_stage_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '</table>			 
				</div>
			</div>';
		}
		else if(strtolower($POST['mode']) == "load_party_purchase") {
			/* START JAYESH fetch clone data */
			if((strtolower($POST['form_mode']) == "edit") || (strtolower($POST['form_mode']) == "clone")){
				$query="select pctrn.purchasecardtrn_id,led.l_name,pctrn.price,pctrn.rate_tolerance,pctrn.discount_percentage,pctrn.quotation_number,pctrn.quotation_date,pctrn.affected_date,pctrn.valid_date,pcrd.pur_card_no , pcrd.is_aproove from tbl_product_party_purchase as pcrd
				left join tbl_purchasecardtrn as pctrn on pctrn.party_purchase_id = pcrd.party_purchase_id
				left join tbl_ledger as led on led.l_id= pctrn.vendor_id
				where pctrn.purchasecardtrn_status=0 and pctrn.product_id=".$POST['product_id'];
			}
			else{
				$query="select pctrn.purchasecardtrn_id,pcrd.pur_card_no, pctrn.price, led.l_name, pctrn.rate_tolerance,pctrn.discount_percentage, pctrn.quotation_number, pctrn.quotation_date, pctrn.affected_date, pctrn.valid_date,pcrd.is_aproove from tbl_product_party_purchase as pcrd
				left join tbl_purchasecardtrn as pctrn on pctrn.party_purchase_id=pcrd.party_purchase_id
				left join tbl_ledger as led on led.l_id=pctrn.vendor_id
				where pctrn.purchasecardtrn_status=0 and pctrn.product_id=0 and pctrn.company_id=".$_SESSION['company_id'];
			}

			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="10%" class="text-center">Purchase Card No.</th>
							<th width="20%" class="text-center">Party</th>
							<th width="8%" class="text-center">Rate Tolerance</th>
							<th width="8%" class="text-center">Disc(%)</th>
							<th width="10%" class="text-center">Quotation No.</th>
							<th width="10%" class="text-center">Quotation Date</th>
							<th width="10%" class="text-center">Effective Date</th>
							<th width="10%" class="text-center">Valid Date</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Status</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$quot_date = '';
					if($rel['is_aproove']==1){
						$status = "<strong style='color:green'>Approved</strong>";
					}else{
						$status = "<strong style='color:red'>Pending</strong>";
					}
					if($rel['quotation_date'] != '1970-01-01' && $rel['quotation_date'] != '0000-00-00'){
						$quot_date = date('d-m-Y',strtotime($rel['quotation_date']));
					}
					$effec_date='';
					if($rel['affected_date'] != '1970-01-01' && $rel['affected_date'] != '0000-00-00'){
						$effec_date = date('d-m-Y',strtotime($rel['affected_date']));
					}
					$valid_date='';
					if($rel['valid_date'] != '1970-01-01' && $rel['affected_date'] != '0000-00-00' ){
						$valid_date =date('d-m-Y',strtotime($rel['valid_date']));
					}

					if($rel['']){

					}
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">'.$rel['pur_card_no'].'</td>
						<td style="vertical-align:top;">
							'.$rel['l_name'].'
						</td>
						<td style="vertical-align:top;">'.$rel['rate_tolerance'].'</td>
						<td style="vertical-align:top;">'.$rel['discount_percentage'].'</td>
						<td style="vertical-align:top;">'.$rel['quotation_number'].'</td>
						<td style="vertical-align:top;">'.$quot_date.'</td>
						<td style="vertical-align:top;">'.$effec_date.'</td>
						<td style="vertical-align:top;">'.$valid_date.'</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['price'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$status.'
						</td>
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_party_purchase('.$rel['purchasecardtrn_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_party_purchase('.$rel['purchasecardtrn_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
			echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}

		else if(strtolower($POST['mode']) == "load_make_info") {
			/* START JAYESH fetch clone data */
			if((strtolower($POST['form_mode']) == "edit")|| (strtolower($POST['form_mode']) == "clone")){
				$query="select mst.*,m.make_name,mn.make_number from tbl_product_make_purchase as mst 
				left join tbl_make as m on m.make_id=mst.make_id left join tbl_make_number as mn on mn.make_number_id=mst.make_number_id where mst.make_product='".$POST['product_id']."' order by mst.make_purchase_id Desc";
			}
			else{
				$query="select mst.*,m.make_name,mn.make_number from tbl_product_make_purchase as mst 
				left join tbl_make as m on m.make_id=mst.make_id left join tbl_make_number as mn on mn.make_number_id=mst.make_number_id where mst.user_id=".$_SESSION['user_id']." and mst.make_product='0' order by mst.make_purchase_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;margin-left:auto;margin-right:auto;width:80%" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Make</th>
							<th width="20%" class="text-center">Make Number</th>
                            <th width="10%" class="text-center">Stock</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['make_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['make_number'].' - '.$rel['make_value'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$rel['make_stock'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['make_rate'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_make_purchase('.$rel['make_purchase_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_make_purchase('.$rel['make_purchase_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
			echo '<tr><td colspan="5" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}

		else if(strtolower($POST['mode']) == "load_scrap_info") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select ps.*,pm.product_name,pm.product_icode from tbl_product_scrap as ps 
				left join product_mst as pm on pm.product_id=ps.scrap_code_id where ps.scrap_product='".$POST['product_id']."' order by ps.scrap_product_id Desc";
			}
			else{
				$query="select ps.*,pm.product_name,pm.product_icode from tbl_product_scrap as ps 
				left join product_mst as pm on pm.product_id=ps.scrap_code_id where ps.user_id=".$_SESSION['user_id']." and ps.scrap_product='0' order by ps.scrap_product_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;margin-left:auto;margin-right:auto;width:80%" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Mat. Issue Weight</th>
							<th width="20%" class="text-center">Scrap Code</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['material_issue_weight'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['product_name'].' - '.$rel['product_icode'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_scrap('.$rel['scrap_product_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_scrap('.$rel['scrap_product_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
			echo '<tr><td colspan="3" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_party")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_purchasecardtrn WHERE purchasecardtrn_id	= '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			
			if($r['quotation_date'] != '1970-01-01' && $r['quotation_date'] != '0000-00-00'){
				$r['quotation_date'] = date('d-m-Y',strtotime($r['quotation_date']));
			}else{
				$r['quotation_date']='';
			}
			
			if($r['affected_date'] != '1970-01-01' && $r['affected_date'] != '0000-00-00'){
				$r['affected_date'] = date('d-m-Y',strtotime($r['affected_date']));
			}else{
				$r['affected_date']='';
			}
			
			if($r['valid_date'] != '1970-01-01' && $r['affected_date'] != '0000-00-00' ){
				$r['valid_date'] =date('d-m-Y',strtotime($r['valid_date']));
			}else{
				$r['valid_date']='';
			}

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		
		/*START JAEYSH 20-07-2021 edit alternative product */
		else if(strtolower($POST['mode'])== "preedit_accessories_product")
		{
			$q = $dbcon -> query("SELECT tpap.*,pm.product_name FROM tbl_product_acc_product as tpap left join product_mst as pm on pm.product_id=tpap.acc_product_id WHERE acc_id= '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}

		else if(strtolower($POST['mode'])== "preedit_make")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_make_purchase as pm WHERE make_purchase_id	= '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "preedit_scrap")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_scrap as pm WHERE scrap_product_id	= '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "preedit_stage")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_stage WHERE product_stage_id	= '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_party")
		{
			$info['purchasecardtrn_status'] = 2;

			$deleteid = update_record("tbl_purchasecardtrn", $info,"purchasecardtrn_id=".$POST['eid'], $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		/*START JAEYSH DELETE ALTERNATIVE PRODUCT 20-07-2021*/
		else if(strtolower($POST['mode'])== "delete_data_alternative_product")
		{
			$deleteid=delete_record('tbl_product_acc_product', "acc_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		/*END JAYESH ALTERNATIVE PRODUCT */
		else if(strtolower($POST['mode'])== "delete_data_make")
		{
			$deleteid=delete_record('tbl_product_make_purchase', "make_purchase_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_data_scrap")
		{
			$deleteid=delete_record('tbl_product_scrap', "scrap_product_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "delete_data_stage")
		{
			$deleteid=delete_record('tbl_product_stage', "product_stage_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		// JOB Party Purchase
		else if(strtolower($POST['mode']) == "add_job_party_purchase") {
			$info1['job_party_id']= $POST['party_id'];
			$info1['job_party_process_id']= $POST['job_party_process_id'];
			$info1['job_party_rate']= $POST['party_rate'];
			$info1['job_party_product']= $POST['pid'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branchid'];
			
			$table='tbl_product_job_party_purchase';$tableid='job_party_purchase_id';
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			echo "1";
		}
		else if(strtolower($POST['mode']) == "load_job_party_purchase") {
			/* START JAYESH fetch clone data */
			if((strtolower($POST['form_mode']) == "edit")|| (strtolower($POST['form_mode']) == "clone")){
				$query="select mst.*,p.l_name,proc.process_name from tbl_product_job_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.job_party_id 
				left join process_mst as proc on proc.process_id=mst.job_party_process_id
				where mst.job_party_product='$POST[product_id]' order by mst.job_party_purchase_id Desc";
			}
			else{
				$query="select mst.*,p.l_name,proc.process_name from tbl_product_job_party_purchase as mst 
				left join tbl_ledger as p on p.l_id=mst.job_party_id
				left join process_mst as proc on proc.process_id=mst.job_party_process_id
				where mst.user_id=".$_SESSION['user_id']." and mst.job_party_product='0' order by mst.job_party_purchase_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
				
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Process</th>
							<th width="20%" class="text-center">Party</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['process_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['l_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['job_party_rate'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_job_party_purchase('.$rel['job_party_purchase_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_job_party_purchase('.$rel['job_party_purchase_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_job_party")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_job_party_purchase WHERE job_party_purchase_id	= '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_job_data_party")
		{
			
			$deleteid=delete_record('tbl_product_job_party_purchase', "job_party_purchase_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		
		// Product Parameter
		
		else if(strtolower($POST['mode']) == "add_param_value") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			//$tolerance_plus = ($POST['tolerance_plus']) ? $POST['tolerance_plus']:'';	
			//$tolerance_minus = ($POST['tolerance_minus']) ? $POST['tolerance_minus']:'';	
			$info1['process_id'] 			= $POST['qc_process_id'];
			$info1['param_id']= $POST['param_id'];
			$info1['param_value']= $POST['param_value'];
			$info1['product_id']= $POST['pid'];
			if($POST['tolerance_plus']){
				$info1['tolerance_plus']= $POST['tolerance_plus'];
			}
			if($POST['tolerance_minus']){
				$info1['tolerance_minus']= $POST['tolerance_minus'];
			}
			
			$info1['unit_id']= $POST['param_unit_id'];
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			
			$table='tbl_product_parameter';$tableid='pr_param_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon, $branch_id);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
			}
			
			echo "1";
		}
		
		else if(strtolower($POST['mode']) == "load_product_param") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			$where_db = check_branch('mst', $branch_id);
			$where=" $where_db and mst.company_id=".$_SESSION['company_id'];
			/* START JAYESH fetch data of clone */
			if((strtolower($POST['form_mode']) == "edit")||(strtolower($POST['form_mode']) == "clone")){
				$query="select mst.*,p.p_name,um.unit_name,pmst.process_name from tbl_product_parameter as mst 
				left join unit_mst as um on um.unitid=mst.unit_id
				left join process_mst as pmst on pmst.process_id=mst.process_id
				left join tbl_qc_param as p on p.p_id=mst.param_id where mst.product_id='$POST[product_id]' order by mst.process_id";
			}
			else{
				$query="select mst.*,p.p_name,um.unit_name,pmst.process_name from tbl_product_parameter as mst 
				left join unit_mst as um on um.unitid=mst.unit_id
				left join process_mst as pmst on pmst.process_id=mst.process_id
				left join tbl_qc_param as p on p.p_id=mst.param_id where mst.user_id=".$_SESSION['user_id']." $where and mst.product_id='0' order by mst.process_id";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Process</th>
							<th width="20%" class="text-center">Parameter</th>
							<th width="10%" class="text-center">Base Value</th>
							<th width="10%" class="text-center">Tolerance (+)</th>
							<th width="10%" class="text-center">Tolerance (-)</th>
							<th width="10%" class="text-center">Unit</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$tolerance_plus='-';$tolerance_minus='-';
					if($rel['tolerance_plus']){
						$tolerance_plus = $rel['tolerance_plus'];
					}
					if($rel['tolerance_minus']){
						$tolerance_minus = $rel['tolerance_minus'];
					}

					if($rel['process_id']=="-1"){
						$process_name="Purchase";
					}else{
						$process_name=$rel['process_name'];
					}
					echo '<tr id="fieldtr'.$id.'"  class="qc_row" data-cid="'.$i.'" >
						<td style="vertical-align:top;">
							'.$process_name.'
						</td>
						<td style="vertical-align:top;">
							'.$rel['p_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['param_value'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$tolerance_plus.'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$tolerance_minus.'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['unit_name'].'
						</td>
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_product_param('.$rel['pr_param_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_param('.$rel['pr_param_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					</tr>';
					$i++;
				}
			}
			else{
				echo '<tr><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_param")
		{
			$q = $dbcon->query("SELECT * FROM tbl_product_parameter WHERE pr_param_id = '$POST[id]'");
			$r = brp_mysqli_fetch_assoc($q);
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		else if(strtolower($POST['mode'])== "delete_data_param")
		{
			$deleteid=delete_record('tbl_product_parameter', "pr_param_id=$POST[eid]", $dbcon);
			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode'])== "get_product_code")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_code_series WHERE pr_type = '".$POST['pcode']."' and company_id=".$_SESSION['company_id']);
			$r = brp_mysqli_fetch_assoc($q);
			
			$pr_series=$r['pr_code_series']+1;
			$short_code=$r['pr_code_short'];
			
			$res['series']=$short_code."".sprintf('%05d',$pr_series);
			$res['code']=$pr_series;
			
			echo json_encode($res);
		}
		// Process Parameter
		else if(strtolower($POST['mode']) == "add_process_value") {

			$info1['process_id']= $POST['process_id'];
			$info1['process_rate']= $POST['process_rate'];
			$info1['process_priority']= $POST['process_priority'];
			$info1['process_type']= $POST['process_type'];
			$info1['product_id']= $POST['pid'];
			$info1['process_time']= $POST['process_time'];
			$info1['process_opening']= $POST['process_opening'];
			//$info1['resource_id']= implode(",",$POST['resource_id']);
			$info1['resource_id']= $POST['resource_id'];
			//echo "<pre>"; print_r($info1['resource_id']);die;
			$info1['process_loss']= $POST['process_loss'];
			$info1['process_scrap_tolerance_plus']= $POST['process_scrap_tolerance_plus'];
			$info1['process_scrap_tolerance_minus']= $POST['process_scrap_tolerance_minus'];
			
			$info1['cdate'] = date("Y-m-d");
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			
			$table='tbl_product_process';$tableid='pr_process_id';
			
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
				
				//Product Opening to process Allocation set 
				
				/*$process=get_product_process($dbcon,$POST['pid']);
						
				$process_pr=json_decode($process);
			
				$process_id=$process_pr->process_id;
				$process_type=$process_pr->process_type; */
				if($POST['process_opening']!="0"){
					if($POST['process_opening']!=""){
						$info5['process_id']	= $POST['process_id'];			
						$info5['p_qty']	= $POST['process_opening'];		
						$info5['pen_qty']	= $POST['process_opening'];		
						$info5['p_ref_id']	=$inserid;		
						$info5['p_ref_type']	='process_opening';		
						$info5['p_product_id']	= $POST['pid'];	
						$info5['pr_process_type']	= $POST['process_type'];						
						$info5['process_type_data']	= '1';						
						
						$info5['cdate']		= date("Y-m-d H:i:s");
						$info5['user_id']	= $_SESSION['user_id'];
						$info5['company_id']	= $_SESSION['company_id'];	
						
						$inserid_alloc=add_record('tbl_allocate_process', $info5, $dbcon);
					}
				}
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
				
				//Product Opening to process Allocation set 
				
				$q1=$dbcon->query("select p_id from tbl_allocate_process where p_ref_type='process_opening' and p_ref_id='$POST[edit_id]'");
				$count1=mysqli_num_rows($q1);
				
				/*$process=get_product_process($dbcon,$POST['pid']);
						
				$process_pr=json_decode($process);
			
				$process_id=$process_pr->process_id;
				$process_type=$process_pr->process_type; */
				
				$info6['process_id']	= $POST['process_id'];			
				$info6['p_qty']	= $POST['process_opening'];		
				$info6['pen_qty']	= $POST['process_opening'];		
				$info6['p_ref_id']	=$POST['edit_id'];		
				$info6['p_ref_type']	='process_opening';		
				$info6['p_product_id']	= $POST['pid'];	
				$info6['pr_process_type']	= $POST['process_type'];						
				$info6['process_type_data']	= '1';						
				
				$info6['cdate']		= date("Y-m-d H:i:s");
				$info6['user_id']	= $_SESSION['user_id'];
				$info6['company_id']	= $_SESSION['company_id'];
				
				if($count1>0)
				{
					update_record('tbl_allocate_process',$info6,"p_ref_type='process_opening' and p_ref_id=".$POST['edit_id'] , $dbcon);	
				}
				else
				{
					if($POST['process_opening']!="0"){
						if($POST['process_opening']!=""){
							$inserid_alloc=add_record('tbl_allocate_process', $info6, $dbcon);
						}
					}
				}
			}

			if($POST['pid']!='' && $POST['pid']!='0'){
				$sql = 'SELECT *  FROM `tbl_product_process` WHERE status = 0 AND `product_id` = '.$POST['pid'];
				$q = $dbcon->query($sql);
				$count = brp_mysqli_num_rows($q);

				$get_pro_sql = 'select product_setting_check from product_mst where product_id='.$POST['pid'];
				$exe = $dbcon->query($get_pro_sql);
				$res = brp_mysqli_fetch_assoc($exe);
				$product_setting_check = $res['product_setting_check'];

				if($count > 0){
					if($product_setting_check!=''){
						$product_setting_check_array = explode(',', $product_setting_check);
						if(in_array('process_product', $product_setting_check_array)){
							$check_process['product_setting_check'] = $product_setting_check;
						}else{
							$check_process['product_setting_check'] = 'process_product,'.$product_setting_check;
						}
					}else{
						$check_process['product_setting_check'] = 'process_product';
					}
					
					$updateProduct = update_record('product_mst', $check_process,"product_id = '".$POST['pid']."'" , $dbcon);
				}else{
					if($product_setting_check!=''){
						$product_setting_check_array = explode(',', $product_setting_check);
						if(in_array('process_product', $product_setting_check_array)){
							$check_process['product_setting_check'] = str_replace('process_product,', '', $product_setting_check);
						}
					}
					$updateProduct = update_record('product_mst', $check_process,"product_id = '".$POST['pid']."'" , $dbcon);
				}
			}
			
		}
		
		else if(strtolower($POST['mode']) == "load_product_process") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			//$where_db = check_branch('mst', $branch_id);
			$where=" $where_db and mst.company_id=".$_SESSION['company_id'];
			/* Start jayesh 17-06-2021 fetch clone data */
			if((strtolower($POST['form_mode']) == "edit")||(strtolower($POST['form_mode']) == "clone")){
				$query="select mst.*,p.process_name,reso.resource_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id left join tbl_resource as reso on mst.resource_id=reso.resource_id where mst.status = 0 AND mst.product_id='".$POST['product_id']."' order by mst.process_priority";
			}
			else{
				$query="select mst.*,p.process_name, reso.resource_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id left join tbl_resource as reso on mst.resource_id=reso.resource_id where mst.user_id=".$_SESSION['user_id']." $where and mst.status = 0 AND  mst.product_id='0' order by mst.process_priority";
			}
			
			
		   
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="15%" class="text-center">Process</th>
							<th width="10%" class="text-center">Priority</th>
							<th width="10%" class="text-center">Type</th>
							<th width="10%" class="text-center">Rate</th>
							<th width="10%" class="text-center">Time (In Min.)</th>
							<th width="15%" class="text-center">Resource</th>
							<th width="10%" class="text-center">Loss (In %)</th>
							<th width="10%" class="text-center">Scrap Tol. (+)</th>
							<th width="12%" class="text-center">Scrap Tol. (-)</th>
							<th width="12%" class="text-center">Action</th>
						</tr>';

						// <th width="10%" class="text-center">Opening Stock</th>
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				$process_time = 0;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$resource_names = explode(",",$rel['resource_id']);
					$resource_ids = $rel['resource_id'];
					$resource_data = array();
					$resource_name = '';
					if(count($resource_names)>1)
					{
						$resource = "select * from tbl_resource where resource_id IN ($resource_ids)";
						$resource_result=$dbcon->query($resource);
						while($resource_row = mysqli_fetch_array($resource_result))
						{
							$resource_data[] = $resource_row['resource_name'];
							
						}
						$resource_name = '<td style="vertical-align:top;" class="text-center hide_act_add">
							'.implode(",",$resource_data).'
						</td>';						
					}
					else
					{
						$resource_name = '<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['resource_name'].'
						</td>';
					}

					$is_deletable = 1;

					if($rel['product_id'] != '0' && $rel['product_id'] > 0){
						$bom_pr_qry = "SELECT count(pro_bom_process_id) as used_process FROM pro_bom_process WHERE process_status = 0 AND product_id = " . $rel['product_id'] . " AND pr_process_id = "	. $rel['pr_process_id'];
						$result_bom_pr =  $dbcon->query($bom_pr_qry);
						$bom_pr_row = brp_mysqli_fetch_assoc($result_bom_pr);

						if($bom_pr_row['used_process'] > 0){
							$is_deletable = 0;					
						}

					}
					
					
					$process_time+=$rel['process_time'];
					
					if($rel['process_type']=='1'){ $ptype="Inhouse"; } else { $ptype="Outside"; }
					echo '<tr id="fieldtr'.$id.'" class="process_row" data-cid="'.$i.'">
						<td style="vertical-align:top;">
							'.$rel['process_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_priority'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$ptype.'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_rate'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_time'].'
						</td>'.$resource_name.'						
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_loss'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_scrap_tolerance_plus'].'
						</td>
						<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_scrap_tolerance_minus'].'
						</td>
						
						<td style="vertical-align:top" class="text-center">
						
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_product_process('.$rel['pr_process_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_process('.$rel['pr_process_id'].','.$rel['process_priority'].','.$rel['product_id'].','.$is_deletable .');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;

					/*<td style="vertical-align:top;" class="text-center hide_act_add">
							'.$rel['process_opening'].'
						</td>*/
				}
				echo '<input type="hidden" id="total_proces_time" value="'.@$process_time.'">';
			}
			else{
				echo '<tr class="process_row" data-cid="0"><td colspan="10" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '</table>			 
				</div>
			</div>';
		}
		
		else if(strtolower($POST['mode'])== "preedit_process")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_process WHERE  pr_process_id = '".$POST['id']."'");
			$r = brp_mysqli_fetch_assoc($q);
			
			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
			echo json_encode($r);
		}
		//Added By Dhruv Start
		else if(strtolower($POST['mode'])== "getgstbyhsn")
		{
			$q = $dbcon -> query("SELECT tax_gst FROM `tbl_tax_category` where tax_cat_id= '".$POST['id']."'");
			$r = brp_mysqli_fetch_assoc($q);
			
			echo $r['tax_gst'];
		}
		//End by dhruv
		else if(strtolower($POST['mode'])== "delete_data_process")
		{
			$priority_id = $POST['priority_id'];
			$product_id = $POST['product_id'];

			$get_process_sql = 'select product_id from tbl_product_process where pr_process_id='.$POST['eid'];
			$exe1 = $dbcon->query($get_process_sql);
			$res_data = brp_mysqli_fetch_assoc($exe1);

			$pr_del_info['status'] = 2;
			$deleteid = update_record('tbl_product_process',$pr_del_info,"pr_process_id=".$POST['eid'], $dbcon);
			// $deleteid=delete_record('tbl_product_process', "pr_process_id=$POST[eid]", $dbcon);
			
			$q = $dbcon -> query("SELECT * FROM tbl_allocate_process WHERE p_ref_type='process_opening' and p_ref_id=".$POST['eid']."");
			$r = brp_mysqli_fetch_assoc($q);
			
			$info6['p_status']=2;
			update_record('tbl_allocate_process',$info6,"p_id=".$r['p_id'] , $dbcon);
			update_record('tbl_allocate_process_trn',$info6,"pt_alloc_id=".$r['p_id'] , $dbcon);

			if($deleteid){

				$get_pro_sql = 'select product_setting_check from product_mst where product_id='.$res_data['product_id'];
				$exe = $dbcon->query($get_pro_sql);
				$res = brp_mysqli_fetch_assoc($exe);
				$product_setting_check = $res['product_setting_check'];

				if($product_setting_check!=''){
					$product_setting_check_array = explode(',', $product_setting_check);
					if(in_array('process_product', $product_setting_check_array)){
						$check_process['product_setting_check'] = str_replace('process_product,', '', $product_setting_check);
					}
				}
				$updateProduct = update_record('product_mst', $check_process,"product_id = '".$res_data['product_id']."'" , $dbcon);

				$fetch_sql_process = "SELECT * FROM `tbl_product_process` WHERE status=0 AND product_id = $product_id AND process_priority > $priority_id AND company_id = '".$_SESSION['company_id']."' "; 
				$fetch_sql_exec = $dbcon->query($fetch_sql_process);
				if(brp_mysqli_num_rows($fetch_sql_exec) > 0){
					while ($row_data = brp_mysqli_fetch_assoc($fetch_sql_exec)) {
						$change_priority['process_priority'] = (int)$row_data['process_priority'] - 1;
						update_record('tbl_product_process', $change_priority, "pr_process_id = '".$row_data['pr_process_id']."'" , $dbcon);
					}
				}

				$row['res']="1";
			}
			else{
				$row['res']="0";
			}
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "change_status") 
		{
			$p_status=$POST['p_status'];
			$pid=$POST['pid'];
			
			if($p_status==0)
			{
				$info['product_status'] = 1;
			}
			else
			{
				$info['product_status'] = 0;
			}
			
			$updateid=update_record('product_mst', $info,"product_id=".$POST['pid'] , $dbcon);		
			
			if($updateid)
				echo "1";	
			else
				echo "0";	
		}

		else if(strtolower($POST['mode'])== "get_revision_data")
		{
			$drawing_id=$POST['drawing_id'];

			/*$sql = "SELECT * FROM tbl_drawing WHERE drawing_id='".$drawing_id."' ";
			$q = $dbcon->query($sql);
			$r = $q->fetch_assoc();

			$drawing_number = $r['drawing_number'];*/

			$arr['revision_id'] = getrevision_return($dbcon,$drawing_id,'');
			echo json_encode($arr);
			 
		}
		else if(strtolower($POST['mode'])== "view_product_image")
		{
				$id = $POST['id'];
				$qry="SELECT image_name FROM `product_mst` Where `company_id`='".$_SESSION['company_id']."' and `product_id`='".$id."' ";
				$result=$dbcon->query($qry);
				echo '<div class="form-group">
						<div class="col-md-12 col-xs-11">
						<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
							<tr id="field">
								<th class="text-center" width="25%">View</th>
							</tr>';
							
							//echo $query;
			if(mysqli_num_rows($result)>0)
			{
				$rel=brp_mysqli_fetch_assoc($result);
				
				$filetype = '<a href="'.ROOT.'view/upload/product_images/'.$rel["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel["image_name"].'" class="img-thumbnail" width="90" height="90"></a>';	
				
				 echo '<tr>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>					
					</tr>';
			}else{
				 echo '<tr><td colspan="2">NO DATA FOUND</td></tr>';
			}
		}

		else if(strtolower($POST['mode'])== "add_drawing_save")
		{
			/*$tr = $dbcon -> query("SELECT * FROM `tbl_drawing` WHERE drawing_status=0 and `drawing_number` ='".$POST['drawing_number']."' ");
			if($tr->num_rows > 0) {
				echo '-1';
			}
			else {*/
				$query="select * from tbl_invoicetype where status=0 and type_id=20 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=mysqli_fetch_assoc($result);

			$query1="select * from  tbl_invoicetype where invoicetype_id=".$row['invoicetype_id'];
			$rows=mysqli_fetch_assoc($dbcon->query($query1));
			$id=$rows['taxinvoice_start'];
			$id=$id+1;
			

			$getspecialConfiguration=getspecialConfiguration($dbcon);
			
			if($getspecialConfiguration['invoite_permission']==1){
				$queryno="select max(invoite_series) as sers from  tbl_drawing where drawing_status=0 and invoite_no='".$POST['drawing_number']."'";
				$resmo=$dbcon->query($queryno);
				$rowsmo=mysqli_fetch_assoc($resmo);
				if(!empty($rowsmo['sers'])){
					$id=$rowsmo['sers']+1;
				}else{
					$id=1;
				}
			}else{
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$row['invoicetype_id']);
			}
			$auto_no=str_pad($id,3,"0",STR_PAD_LEFT);
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
				$info['drawing_number']	= $POST['drawing_number'].$auto_no;
				$info['drawing_title']	= $POST['drawing_title'];
				$info['vender_id']	= $POST['vender_id'];
				$info['drawing_size']		= $POST['drawing_size'];
				$info['drawing_scale']	= $POST['drawing_scale'];
				$info['approve_status']	= 3;
				$info['invoite_no']	= $POST['drawing_number'];
				$info['invoite_series']	= $id;
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['drawing_status']	= 0;
				$info['cdate']	= date('Y-m-d H:i:s');
				$inserpoid=add_record('tbl_drawing', $info, $dbcon,$branch_id);
				if($inserpoid){
					echo $inserpoid;
				}else{
					echo '0';
				}
			//}
		}

		else if(strtolower($POST['mode'])== "load_drawing_number"){
			$drawing_id = getdrawingnumber($dbcon,$POST['drawing_id']); 
			echo $drawing_id;
		}
		else if(strtolower($POST['mode']) == "load_product_process_qc_show") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			//$where_db = check_branch('mst', $branch_id);
			$where_db = '';
			$where=" $where_db and mst.company_id=".$_SESSION['company_id'];
			$str="";
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select mst.*,p.process_name from tbl_product_process as mst 
						left join process_mst as p on p.process_id=mst.process_id
						where mst.status=0 and mst.product_id='".$POST['product_id']."'";
			}
			else{
				$query="select mst.*,p.process_name from tbl_product_process as mst 
				left join process_mst as p on p.process_id=mst.process_id 
				where mst.status=0 and mst.user_id=".$_SESSION['user_id']." $where and mst.product_id='0' ";
			}
		    
			$result=$dbcon->query($query);
						
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				$str .= '<option value="-1">Purchase</option>';
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$str .= '<option value="'.$rel['process_id'].'">'.$rel["process_name"].'</option>';
					$i++;
				}
			}
			else{
				$str .= '<option value="-1">Purchase</option>';
			}
			
			echo $str;
				
		} else if(strtolower($POST['mode']) == "add_product_tempimage") {
			
			
			$count_uploaded_files = count( $_FILES['image']['name'] );
						
			 for( $i = 0; $i < $count_uploaded_files; $i++ )
   			 {
   			 	
   			 	$image_name = check_img_type($dbcon,$_FILES['image']['name'][$i],$_FILES['image']['tmp_name'][$i],$path.'view/upload/umaboy_erp_data/');
   			  	if($image_name['type'] != 'success'){					
					echo "-1";	die;		
				}else{
					$info1['im_name']=$image_name['name'];
				}

				// $info1['im_name']=$tmp_name;
				 $info1['cdate']=date("Y-m-d");
				 $info1['im_status']=1;
				 $info1['user_id']			= $_SESSION['user_id'];
				 $info1['company_id']			= $_SESSION['company_id'];
				 $info1['branch_id']			= $POST['branchid'];
				 $info1['im_product']			= $POST['pid'];
				
				 $table='tbl_product_images';$tableid='img_id';
				 $inserid=add_record($table, $info1, $dbcon);
			 
			 }
			 echo get_tempimages_product($dbcon,'0');			
		}
		
		else if(strtolower($POST['mode']) == "show_images_tempdata") {
			
			if((strtolower($POST['form_mode']) == "edit")|| (strtolower($POST['form_mode']) == "clone")){
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='$POST[product_id]' and im_status=1 order by img_id Desc";
			}
			else{
				
				$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='0' and im_status=1 order by img_id Desc";
			}	
				$rel=$dbcon->query($q);
				$path='view/upload/umaboy_erp_data/';
				$str="";
				$str.="<table>";
				while($row  = brp_mysqli_fetch_assoc($rel))
				{
					$str.='<tr>
					<td>
						<a onclick="delete_data_tempimage('.$row['img_id'].');" href="#">
							<div class="img-wrap">
								<span class="close">&times;</span>
								<img src="'.ROOT.'view/img/close_img.jpg" width="30" height="30" class="img-thumbnail">
							</div>
						</a>
					</td>
					<td>'.DOMAIN_F.$path.$row['im_name'].'</td>
					</tr>';
				}
				$str.="</table>";
				echo $str;
		}
		else if(strtolower($POST['mode'])== "delete_data_tempimage")
		{
			$del_attch_qry="select * from tbl_product_images where img_id=".$POST['eid'];
		$del_attch_rel=mysqli_fetch_assoc($dbcon->query($del_attch_qry));
		unlink('..//..//..//view//upload//umaboy_erp_data//'.$del_attch_rel['im_name']);

			$deleteid=delete_record('tbl_product_images', "img_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "delete_data_die_allocation") {
			$deleteid=delete_record('tbl_product_die_allocation', "die_allocation_id=$POST[eid]", $dbcon);

			if($deleteid)
				$row['res']="1";
			else
				$row['res']="0";
			echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "add_die_allocation_request") {

			$info1['die_product_id']= $POST['die_product_id'];
			$info1['die_customer_id']= $POST['die_customer_id'];
			$info1['product_id']= $POST['product_id'];
			$info1['user_id']			= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $POST['branch_id'];
			$info1['created_at'] = date("Y-m-d H:i:s");
			$info1['updated_at'] = date("Y-m-d H:i:s");
			
			$table='tbl_product_die_allocation';
			$tableid='die_allocation_id';
			
			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);
			}
			else
			{
				$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon);	
			}
			
			echo "1";
		}else if(strtolower($POST['mode']) == "load_die_allocation_info") {
			
			if(strtolower($POST['form_mode']) == "edit"){
				$query="select ps.*,pm.product_name,pm.product_icode,led.l_name from tbl_product_die_allocation as ps left join product_mst as pm on pm.product_id=ps.die_product_id
				left join tbl_ledger as led on led.l_id=ps.die_customer_id
				 where ps.product_id='".$POST['product_id']."' AND ps.status=0 AND ps.company_id = ".$_SESSION['company_id']." order by ps.die_allocation_id Desc";
			}
			else{
				$query="select ps.*,pm.product_name,pm.product_icode,led.l_name from tbl_product_die_allocation as ps left join product_mst as pm on pm.product_id=ps.die_product_id
					left join tbl_ledger as led on led.l_id=ps.die_customer_id where ps.user_id=".$_SESSION['user_id']." and ps.product_id=0 and ps.status=0 AND ps.company_id = ".$_SESSION['company_id']." order by ps.die_allocation_id Desc";
			}
		    
			$result=$dbcon->query($query);
			echo '<div class="clearfix"></div>
					<div class="col-md-12 col-xs-11 margin_row">
					  <div class="form-group">
						<table cellspacing="10" style="border-spacing:10px;margin-left:auto;margin-right:auto;width:80%" class="display table table-bordered table-striped">
						<tr id="field">
							<th width="20%" class="text-center">Die Allocation Name</th>
							<th width="20%" class="text-center">Die Customer Name</th>
							<th width="10%" class="text-center">Action</th>
						</tr>';
			if(mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					echo '<tr id="fieldtr'.$id.'" >
						<td style="vertical-align:top;">
							'.$rel['product_name'].'
						</td>
						<td style="vertical-align:top;">
							'.$rel['l_name'].'
						</td>
						<td style="vertical-align:top" class="text-center">
							<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_die_allocation('.$rel['die_allocation_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_die_allocation('.$rel['die_allocation_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
						</td>	
					
					</tr>';
					$i++;
				}
			}
			else{
			echo '<tr><td colspan="3" class="text-center">NO DATA FOUND</td></tr>';
			}
				echo '
					</table>			 
				</div>
			</div>';
		}
		else if(strtolower($POST['mode'])== "preedit_die_allocation")
		{
			$q = $dbcon -> query("SELECT * FROM tbl_product_die_allocation as pm WHERE die_allocation_id	= '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		} else if(strtolower($POST['mode'])== "icode_validation"){
			$q = $dbcon -> query("SELECT * FROM product_mst WHERE product_status = 0 AND company_id = '".$_SESSION['company_id']."' AND product_icode = '".$POST['val']."' AND product_id != '".$POST['product_id']."'");
			if(brp_mysqli_num_rows($q) > 0){
				echo "1";
			}else{
				echo "0";
			}
		}
		else if(strtolower($POST['mode']) == "load_specification_content") {
			
			
		$specification= implode(',', array_map('quote', $_POST['specification_id']));
		$specification1= implode(',',$_POST['specification_id']);
		
		$annex_qry="select * from tbl_specification where specification_name IN (".$specification.") ORDER BY FIND_IN_SET(specification_name,'".$specification1."')";
		$rows = '';
		$aqry = $dbcon->query($annex_qry);
		$cnt = brp_mysqli_num_rows($aqry);
		$i = 1;
		while($annex_rel = brp_mysqli_fetch_array($aqry)){
		// $annex_rel = brp_mysqli_fetch_assoc($dbcon->query($annex_qry));
			$rows.="<strong>"."$annex_rel[specification_name]"."</strong><br>";
			$rows.="$annex_rel[specification_detail]"."<br>";
			if($cnt > $i){
				//$rows.="<div style='page-break-after: always'><span style='display: none;'></span></div><br>";
				$rows.="<div><span style='display: none;'></span></div>";
			}
			$i++;
		}
		echo $rows;
		//echo json_encode($annex_qry);
	}
	else if(brp_strtolower($POST['mode']) == "add_project_field") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$info1['product_id']		= $_POST['product_id'];
		$info1['description']		= stripslashes($_POST['product_disc']);
		$info1['product_disc']		= stripslashes($_POST['product_disc']);
		$info1['product_spec']		= stripslashes($_POST['product_spec']);
		$info1['product_hsn_code']	= $_POST['product_hsn_code'];
		$info1['product_qty']		= $_POST['product_qty'];
		$info1['product_rate']		= $_POST['product_rate'];
		$info1['product_amount']	= $_POST['product_qty']*$_POST['product_rate'];
		$info1['formulaid']			= $_POST['formulaid'];
		$info1['company_id']		= $_SESSION['company_id'];

		$info=get_product_common_tax($dbcon,$info1['product_amount'],$POST['formulaid']);
		$info1=array_merge($info1,$info);

		$table='tbl_project_assigntrn';$tableid='project_assigntrn_id';
		if(!empty($POST['project_assign_id']))
		{
			$info1['project_assign_id']= $POST['project_assign_id'];
			$table='tbl_project_assigntrn';
			$tableid='project_assigntrn_id';
		}
		else
		{
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['project_assigntrn_status']= 3;
		}
		if(empty($POST['edit_id']))
		{
			$inserid=add_record($table, $info1, $dbcon,$branch_id);
		}
		else
		{
			$updateid=update_record($table, $info1,$tableid."=".$POST['edit_id'] , $dbcon, $branch_id);	
			$inserid=$POST['edit_id'];
		}
	}
	else if(brp_strtolower($POST['mode'])== "edit_project_data"){
		$q = $dbcon -> query("select mst.*,pro.product_name from tbl_project_assigntrn as mst left join product_mst as pro on mst.product_id=pro.product_id where project_assigntrn_id = '$POST[id]'");
		$r = $q->fetch_assoc();

		echo brp_json_encode($r);
	}
	else if(brp_strtolower($POST['mode']) == "show_project_pro_data") {
		if(empty($POST['so_id'])){
			$query="select project_assigntrn_id,product.product_name,mst.description,product_qty,product_rate,mst.*,hsn.hsn_code as product_hsn from tbl_project_assigntrn as mst 
			left join product_mst as product on product.product_id=mst.product_id  
			left join mst_hsn_code as hsn on hsn.hsn_id=mst.product_hsn_code  
			where project_assigntrn_status=3 and mst.user_id=".$_SESSION['user_id'];
		}else{
			$query="select project_assigntrn_id,product.product_name,mst.description,product_qty,product_rate,mst.*,hsn.hsn_code as product_hsn from tbl_project_assigntrn as mst 
			left join product_mst as product on product.product_id=mst.product_id  
			left join mst_hsn_code as hsn on hsn.hsn_id=mst.product_hsn_code 
			where project_assigntrn_status=0 and project_assign_id	=".$POST['so_id'];
		}

		$result=$dbcon->query($query);
		$str='';
		$str.='<div class="form-group">
		<div class="col-md-12 col-xs-12">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table12 table-bordered table-striped">
		<tr id="field">
		<th class="text-center"width="25%">Product Name</th>
		<th class="text-center"width="8%">HSN Code</th>
		<th class="text-center"width="8%">Qty</th>
		<th class="text-center"width="10%">Rate</th>
		<th class="text-center"width="10%">Total Amount</th>
		<th class="text-center"width="10%">Action</th>
		</tr>';
		if(brp_mysqli_num_rows($result)>0)
		{
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$str.='<tr id="fieldtr'.$id.'" >
				<td data-label="PRODUCT NAME" style="vertical-align:top;text-align:left">
				'.$rel['product_name'].'
				'.(!empty($rel['description'])?'<br/><strong>Desc.</strong> :'.$rel['description']:'').'
				</td>

				<td data-label="HSN CODE" style="vertical-align:top;" class="text-center">';
				if(empty($rel['product_hsn'])){
					$str.=  '-';
				}else{
					$str.=  $rel['product_hsn'];
				}
				$str.='</td>
				<td data-label="QTY" style="vertical-align:top;" class="text-center">
				'.$rel['product_qty'].'
				</td>
				<td  data-label="RATE" style="vertical-align:top;" class="text-center">
				'.$rel['product_rate'].'
				</td>
				<td  data-label="TOTAL AMOUNT" style="vertical-align:top;" class="text-center">
				'.$rel['product_total'].'
				</td>				

				<td data-label="ACTION" style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_project_data('.$rel['project_assigntrn_id'].');" ><i class="fa fa-pencil"></i></button>
				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_project_data('.$rel['project_assigntrn_id'].');" id="fieldremove'.$i.'">X</button>
				</td>	
				</tr>';
				$i++;
			}
		}
		else{
			$str.='<tr><td colspan="6" class="text-center">NO DATA FOUND</td></tr>';
		}
		$str.='</table></div></div>';
		echo $str;
	}
	else if(brp_strtolower($POST['mode'])== "load_project_productdetail"){
		$pro_qry="select * from product_mst where product_id=".$POST['eid'];
		$pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));

		$qry1="select c_add_state as lst,com.stateid as cst from tbl_customer as led 
		left join tbl_cust_address as cust_addr On cust_addr.cust_id = led.cust_id
		left join tbl_company as com on com.company_id=led.company_id
		where led.cust_id =".$POST['cust_id'];
		$result1=$dbcon->query($qry1);
		$row1=mysqli_fetch_assoc($result1);

		if($row1['lst']==$row1['cst']){
			$qry2="select * from formula_mst as led 
			where formula_status=0 and tax_cat='INTRA' and tax_per_id=".$pro_rel['product_sale_gst'];
			$result2=$dbcon->query($qry2);
			$row2=mysqli_fetch_assoc($result2);
			$pro_rel['formula_id']=$row2['formulaid'];
		}else{
			$qry2="select * from formula_mst as led 
			where formula_status=0 and tax_cat='INTER' and tax_per_id=".$pro_rel['product_sale_gst'];
			$result2=$dbcon->query($qry2);
			$row2=mysqli_fetch_assoc($result2);
			$pro_rel['formula_id']=$row2['formulaid'];
		}
		echo json_encode($pro_rel);

	}
	else if(brp_strtolower($POST['mode'])== "delete_project_data"){
		$row=array();
		$info['project_assigntrn_status']=2;	
		$updateid=update_record("tbl_project_assigntrn", $info,"project_assigntrn_id=".$POST['eid'] , $dbcon);
		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo brp_json_encode($row);
	}
	else if(brp_strtolower($POST['mode'])=='get_child_category'){
		$html='';
		$query = "select * from tbl_category where cat_status=0 and cat_pid=".$POST['parent_id'];
		$result = $dbcon->query($query);
		$html.='<option value="">Choose Category</option>';
		while($row = brp_mysqli_fetch_array($result)){
			$html .= '<option value="'.$row['cat_id'].'">'.$row['cat_name'].'</option>';
		}
		echo $html;
	}


function add_branch_stock_at_submit($dbcon, $bstock, $bid, $pid, $bpriority, $unit_id){
	/*$bstock=$POST['bstock'];
	$bid=$POST['bid'];
	$pid=$POST['pid'];
	$bpriority=$POST['bpriority'];*/

	$total_opening_stock=0;
	for($i=0;$i<count($bstock);$i++)
	{
		$q=$dbcon->query("select branch_id,product_id from tbl_branch_product_stock where branch_id='$bid[$i]' and product_id='$pid'");
		$count=mysqli_num_rows($q);
		$roq=brp_mysqli_fetch_assoc($q);
		
		$info['product_stock']=$bstock[$i];
		$info['branch_id']=$bid[$i];
		$info['priority']=$bpriority[$i];
		$info['user_id']=$_SESSION['user_id'];
		$info['cdate']=date("Y-m-d h:i:s");
		$info['company_id']=$_SESSION['company_id'];
		//var_dump($info);
		$table='tbl_branch_product_stock';$tableid='branch_product_stock_id';
		
		if($count>0)
		{
			$updateid=update_record($table, $info,"branch_id='$bid[$i]' and product_id='$pid'", $dbcon);
			$ref_id=$roq["branch_product_stock_id"];			
		}else{
			
			if($pid>0)
			{
				$info['product_id']=$pid;
			}
			if(!empty($info['product_stock'])){
				if($info['product_stock']!="0.00"){
					$inserid=add_record($table, $info, $dbcon);
				}
			}
			$ref_id=$inserid;
		}
		$date1=date("Y-m-d");
		$ref_name="opening_stock";
		$info_st['stock_status']=2;
			$updateid_stock=update_record("tbl_stock_trn", $info_st,"godown_id=".$info['branch_id']." and product_id=".$pid." and ref_name='".$ref_name."'" , $dbcon);
		if(!empty($info['product_stock'])){
			if($info['product_stock']!="0.00"){
				add_stock($dbcon,$pid,$unit_id,$date1,$ref_name,$ref_id,$info['branch_id'],$info['product_stock'],1);
			}
		}
		$total_opening_stock=$total_opening_stock+$info['product_stock'];
	}
	//print_r($bid);
	return $total_opening_stock;
}

/* START JAYESH ROJASARA 27-07-2021 FETCH DATA ALL PRODUCTS*/

if(strtolower($POST['mode']) == "fetches") {
		$branch_id = $POST['branch_id'];
		$whr='';
	    if($branch_id){
	        $whr .= check_branch('zmst',$branch_id);
	    }

		//check paermission for annexure
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_PRODUCT_UPDATE,
	        ADMINISTRATOR_PRODUCT_DELETE,
			ADMINISTRATOR_PRODUCT_APPROVE,
			ADMINISTRATOR_PRODUCT_CLONE
	    ]);
		
		
		if($POST['fil_product_type']!=''){
			$whr.=' and product_type='.$POST['fil_product_type'];
		}
			
		/*$appData = array();
		$i=1;
		$aColumns = array('zmst.product_id', 'zmst.product_type', 'zmst.product_name', 'zmst.cdate',  'dr.drawing_number', 'zmst.product_status', 'zmst.user_id', 'zmst.image_name');
		$sIndexColumn = "product_id";
		$isWhere = array("zmst.product_status !=2 and zmst.company_id in (0,$_SESSION[company_id])".$whr);
		$sTable = "product_mst as zmst";			
		$isJOIN = array('left join tbl_drawing as dr on dr.drawing_id=zmst.drawing_id');
		$hOrder = "zmst.product_status desc ,zmst.product_name";
		//include('../../include/pagging.php');
		
		*/
		
		$sqlReturn=$dbcon->query("select * from product_mst as zmst left join tbl_drawing as dr on dr.drawing_id=zmst.drawing_id where zmst.product_status !=2 and zmst.company_id in (0,$_SESSION[company_id])".$whr." ORDER BY zmst.product_status desc ,zmst.product_name");
		$count=mysqli_num_rows($q);
		
		 foreach($sqlReturn as $row) { ?>
		 <tr>			
                <td><?php echo $row['sr']; ?></td>
                <td><?php if($row['image_name']!=null){?> <a href="<?php echo ROOT.'view/upload/product_images/'.$row["image_name"];?>" target="_blank"><img src="<?php ROOT.'view/upload/product_images/'.$row['image_name'];?>" style="width: 60px;height: 50px;"></a><?php }else { echo ''; } ?></td>
                <td><?php echo get_product_type_by_id($dbcon,$row['product_type']); ?></td>
                <td><?php echo stripcslashes($row['product_name']); ?></td>
                <td><?php echo $row['drawing_number']; ?></td>
               <td><?php if($row['product_status']==0){ ?>
               <strong style='color:green'>Approved</strong><?php }else{ ?><strong style='color:red' >Pending</strong>
               <?php } ?></td>
                <td><?php 
                
				if($row['product_status']==0)
				{ 
					$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-check-square-o'></i></a>";
				}
				else
				{
					$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-window-close'></i></a>";
				}
                
                			
			$edit_btn='';$delete_btn='';$clone_btn='';
			if(in_array(ADMINISTRATOR_PRODUCT_UPDATE,$bulkAccessArray)){
				$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_edit/'.$row['product_id'].'"><i class="fa fa-pencil"></i></a>';
			}
			if(in_array(ADMINISTRATOR_PRODUCT_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product('.$row['product_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
				if(in_array(ADMINISTRATOR_PRODUCT_CLONE,$bulkAccessArray)){
				$clone_btn=' <a class="btn btn-xs btn-warning" data-original-title="Clone" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_clone/'.$row['product_id'].'"><i class="fa fa-clone"></i></a>';
			}
			
			if($row['product_id']=='2862'){//Fixed Product Type Service ID
				$delete_btn='';$change_status='';
			}
				
			if(in_array(ADMINISTRATOR_PRODUCT_APPROVE,$bulkAccessArray)){
				//$change_status='';
			}else{
				//$change_status='';
			}
				
			echo $edit_btn.' '.$delete_btn. ' '. $change_status. ''.$clone_btn; 
			?></td></tr>		
			
		<?php } 
	}
?>