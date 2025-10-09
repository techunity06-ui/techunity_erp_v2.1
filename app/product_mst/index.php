<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	//var_dump($POST);
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
		ADMINISTRATOR_PRODUCT_APPROVE
	]);


	if($POST['fil_product_type']!=''){
		$whr.=' and product_type='.$POST['fil_product_type'];
	}

	$appData = array();
	$i=1;
	$aColumns = array('zmst.product_id', 'zmst.product_type', 'zmst.product_name', 'zmst.product_icode', 'zmst.cdate',  'dr.drawing_number', 'zmst.product_status', 'zmst.user_id', 'zmst.image_name');
	$sIndexColumn = "product_id";
	$isWhere = array("zmst.product_status !=2 and zmst.company_id in (0,$_SESSION[company_id])".$whr);
	$sTable = "product_mst as zmst";			
	$isJOIN = array('left join tbl_drawing as dr on dr.drawing_id=zmst.drawing_id');
	$hOrder = "zmst.product_status desc ,zmst.product_id desc";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		if($row['product_status']==0)
		{  
			$status="<strong style='color:green'>Approved</strong>";
			$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-check-square-o'></i></a>";
		}
		else
		{
			$status="<strong style='color:red' >Pending</strong>"; 
			$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['product_id']."\",\"".$row['product_status']."\")'><i class='fa fa-window-close'></i></a>";
		}

		$row_data[] = $row['sr'];
		if($row['image_name']!=null){
			$row_data[] = '<a href="'.ROOT.'view/upload/product_images/'.$row["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$row['image_name'].'" style="width: 60px;height: 50px;"></a>';
		}else{
			$row_data[] = '';
		}

		$row_data[] = get_product_type_by_id($dbcon,$row['product_type']);
		$row_data[] = stripcslashes($row['product_name']);
		$row_data[] = $row['product_icode'];  
		/*$row_data[] = nl2br($row['product_desc']); */
		$row_data[] = $row['drawing_number']; 
		$row_data[] = $status; 

		$edit_btn='';$delete_btn='';$copy_btn='';
		if(in_array(ADMINISTRATOR_PRODUCT_UPDATE,$bulkAccessArray)){
			$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_edit/'.$row['product_id'].'"><i class="fa fa-pencil"></i></a>';
		}
		if(in_array(ADMINISTRATOR_PRODUCT_DELETE,$bulkAccessArray)){
			$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product('.$row['product_id'].')"><i class="fa fa-trash-o"></i></button>';
		}
		$copy_btn = '<a class="btn btn-xs btn-primary" data-original-title="Copy Product" data-toggle="tooltip" data-placement="top" href="'.ROOT.'product_copy/'.$row['product_id'].'"><i class="fa fa-clone"></i></a>';

		if($row['product_id']=='2862'){//Fixed Product Type Service ID
			$delete_btn='';$change_status='';
		}

		if(in_array(ADMINISTRATOR_PRODUCT_APPROVE,$bulkAccessArray)){
				//$change_status='';
		}else{
				//$change_status='';
		}

		$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status.' '.$copy_btn; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `product_id`,`product_name`,`product_icode`,`product_status` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$POST['product_name']."' or product_icode='".$POST['product_icode']."'");
	if($tr->num_rows > 0) {
		$resp['msg'] = '-1';
	}
	else {
		$info['product_type']	= $POST['product_type'];							
		$info['ledger_id']		= $POST['ledger_id'];	//add pathik						
		$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$POST['product_name']));							
		$info['product_desc']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_desc']));
		$info['product_spec']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_spec']));
		$info['product_icode']	= $_POST['product_icode'];							
		$info['product_hsn']	= $POST['product_hsn'];							
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
		$info['product_category']= $POST['product_category'];							
		$info['product_barcode']= $POST['product_barcode'];							
		$info['multi_branch']= $POST['multi_branch'];							
		$info['product_status']= '1';							
		$info['count_stock']= $POST['count_stock'];							
		$info['product_making_time']= $POST['product_making_time'];							
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


		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['branch_id']	= $POST['branchid'];
		$info['tolerance']	= $POST['tolerance'];
		$info['minimum_tolerance']	= $POST['minimum_tolerance'];
		$info['maximum_tolerance']	= $POST['maximum_tolerance'];

		$info['material_issue_weight']	= $POST['material_issue_weight'];
		$info['product_scrap_id']		= $POST['product_scrap_id'];
		$info['scrap_desc']				= $POST['scrap_desc'];
		$info['scrap_qty']				= $POST['scrap_qty'];
		$info['bom_required']			= $POST['bom_required'];

		if($_FILES['image_name']['tmp_name']!=''){
			$test = explode('.', $_FILES["image_name"]["name"]);
			$ext = end($test);
			$name = time() . '.' . $ext;
			$path='../../view/upload/product_images/';
			$location = $path . $name;  
			move_uploaded_file($_FILES["image_name"]["tmp_name"], $location);
			$info['image_name']=$name;
		}

		$inserid=add_record('product_mst', $info, $dbcon, $branch_id);
		//	exit;

		if($inserid){

				// Insert Stock
			add_branch_stock_at_submit($dbcon, $POST['bstock'], $POST['bid'], $POST['pid'], $POST['bpriority'], $POST['product_base_unit']);

				//Insert LOG
			$log_entry=common_log_entry($dbcon,"product_add",1,"product_mst",$inserid);

			$dbcon->query("update tbl_product_parameter set product_id='$inserid' WHERE product_id='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_unit set unit_product='$inserid' WHERE unit_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_branch_product_stock set product_id='$inserid' WHERE product_id='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_images set im_product='$inserid' WHERE im_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_party_purchase set party_product='$inserid' WHERE party_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_stage set party_product='$inserid' WHERE party_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_make_purchase set make_product='$inserid' WHERE make_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_job_party_purchase set job_party_product='$inserid' WHERE job_party_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_code_series set pr_code_series='".$POST['product_icode_code']."' WHERE pr_type='".$POST['product_type']."'");

			$dbcon->query("update tbl_product_process set product_id='$inserid' WHERE product_id=0 and user_id='".$_SESSION['user_id']."'");

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
else if(strtolower($POST['mode']) == "copy") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `product_id`,`product_name`,`product_icode`,`product_status` FROM `product_mst` WHERE product_status=0 and `product_name` ='".$POST['product_name']."' or product_icode='".$POST['product_icode']."'");
	if($tr->num_rows > 0) {
		$resp['msg'] = '-1';
	}
	else {
		$info['product_type']	= $POST['product_type'];							
		$info['ledger_id']		= $POST['ledger_id'];	//add pathik						
		$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$POST['product_name']));							
		$info['product_desc']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_desc']));
		$info['product_spec']	= stripcslashes(str_replace(array("\n", "\r", "\N"), '', $POST['product_spec']));
		$info['product_icode']	= $_POST['product_icode'];							
		$info['product_hsn']	= $POST['product_hsn'];							
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
		$info['product_category']= $POST['product_category'];							
		$info['product_barcode']= $POST['product_barcode'];							
		$info['multi_branch']= $POST['multi_branch'];							
		$info['product_status']= '1';							
		$info['count_stock']= $POST['count_stock'];							
		$info['product_making_time']= $POST['product_making_time'];							
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
		

		$info['cdate']		= date("Y-m-d H:i:s");
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['branch_id']	= $POST['branchid'];
		$info['tolerance']	= $POST['tolerance'];
		$info['minimum_tolerance']	= $POST['minimum_tolerance'];
		$info['maximum_tolerance']	= $POST['maximum_tolerance'];

		$info['material_issue_weight']	= $POST['material_issue_weight'];
		$info['product_scrap_id']		= $POST['product_scrap_id'];
		$info['scrap_desc']				= $POST['scrap_desc'];
		$info['scrap_qty']				= $POST['scrap_qty'];
		$info['bom_required']			= $POST['bom_required'];

		if($_FILES['image_name']['tmp_name']!=''){
			$test = explode('.', $_FILES["image_name"]["name"]);
			$ext = end($test);
			$name = time() . '.' . $ext;
			$path='../../view/upload/product_images/';
			$location = $path . $name;  
			move_uploaded_file($_FILES["image_name"]["tmp_name"], $location);
			$info['image_name']=$name;
		}

		$inserid=add_record('product_mst', $info, $dbcon, $branch_id);
		//	exit;

		if($inserid){

				// Insert Stock
			add_branch_stock_at_submit($dbcon, $POST['bstock'], $POST['bid'], $POST['pid'], $POST['bpriority'], $POST['product_base_unit']);

				//Insert LOG
			$log_entry=common_log_entry($dbcon,"product_add",1,"product_mst",$inserid);

			$dbcon->query("update tbl_product_parameter set product_id='$inserid' WHERE product_id='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_unit set unit_product='$inserid' WHERE unit_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_branch_product_stock set product_id='$inserid' WHERE product_id='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_images set im_product='$inserid' WHERE im_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_party_purchase set party_product='$inserid' WHERE party_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_make_purchase set make_product='$inserid' WHERE make_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_stage set party_product='$inserid' WHERE party_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_job_party_purchase set job_party_product='$inserid' WHERE job_party_product='0' and  user_id='".$_SESSION['user_id']."'");

			$dbcon->query("update tbl_product_code_series set pr_code_series='".$POST['product_icode_code']."' WHERE pr_type='".$POST['product_type']."'");

			$dbcon->query("update tbl_product_process set product_id='$inserid' WHERE product_id=0 and user_id='".$_SESSION['user_id']."'");

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
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `product_mst` WHERE `product_id` = '".$POST['id']."'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$info['product_type']	= $POST['product_type'];
	$info['ledger_id']		= $POST['ledger_id'];	//add pathik		
	$info['product_name']	= stripcslashes(mysqli_real_escape_string($dbcon,$POST['product_name']));	
	$info['product_desc']	= $_POST['product_desc'];
	$info['product_spec']	= $_POST['product_spec'];
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
	$info['product_category']= $POST['product_category'];							
	$info['product_barcode']= $POST['product_barcode'];							
	$info['multi_branch']= $POST['multi_branch'];							
	$info['count_stock']= $POST['count_stock'];							
	$info['product_making_time']= $POST['product_making_time'];							
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

	$info['material_issue_weight']	= $POST['material_issue_weight'];
	$info['product_scrap_id']		= $POST['product_scrap_id'];
	$info['scrap_desc']				= $POST['scrap_desc'];
	$info['scrap_qty']				= $POST['scrap_qty'];
	$info['bom_required']			= $POST['bom_required'];
	if($_FILES['image_name']['tmp_name']!=''){
		$test = explode('.', $_FILES["image_name"]["name"]);
		$ext = end($test);
		$name = time() . '.' . $ext;
		$path='../../view/upload/product_images/';
		$location = $path . $name;  
		move_uploaded_file($_FILES["image_name"]["tmp_name"], $location);
		$info['image_name']=$name;
	}

	$updateid=update_record('product_mst', $info,"product_id=".$POST['eid_main'] , $dbcon, $branch_id);

	add_branch_stock_at_submit($dbcon, $POST['bstock'], $POST['bid'], $POST['pid'], $POST['bpriority'], $POST['product_base_unit']);
			//$dbcon->query("update tbl_product_code_series set pr_code_series='$POST[product_icode_code]' WHERE pr_type='$POST[product_type]'");

			//Insert LOG
	$log_entry=common_log_entry($dbcon,"product_add",2,"product_mst",$POST['eid_main']);

	$resp['msg'] = "2";

	echo json_encode($resp);
}
else if(strtolower($POST['mode']) == "delete") {

	$product_id = $POST['eid'];
	$sTable = array(TABLE_COMPLAINT_TRN=>'COMPLAINT MODULE',TABLE_BOM_TRN=>'BOM MODULE',TABLE_INQUIRY_TRN=>'INQUIRY MODULE');
	$aColumns = array(array('product_id'),array('product_id'),array('product_id'));
	$sWhere = array(array('complaint_trn_status=0 and product_id = "'.$product_id.'"'),
		array('bom_trn_status=0 and product_id = "'.$product_id.'"'),
		array('inquiry_trn_status=0 and product_id = "'.$product_id.'"'));
	$checkLang = getCheckRelation($dbcon, $sTable, $aColumns, $sWhere);
	if(count($checkLang) > 0){
		$resp['msg'] = '-1';
		$resp['table'] = $checkLang;
	}else{
		$info['product_status']='2';
		$updateid=update_record('product_mst', $info,"product_id=".$POST['eid'] , $dbcon);

		$info_st['stock_status']=2;
		$updateid_stock=update_record("tbl_stock_trn", $info_st,"product_id=".$POST['eid']." and ref_name='opening_stock'" , $dbcon);

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
		where mst.unit_product='".$POST['product_id']."' order by unit_id Desc";
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
		while($rel=mysqli_fetch_assoc($result))
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
	$q = $dbcon -> query("SELECT * FROM tbl_product_unit WHERE unit_id	= '".$POST['id']."'");
	$r = $q->fetch_assoc();

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_data_unit")
{

	$deleteid=delete_record('tbl_product_unit', "unit_id=".$POST['eid'], $dbcon);

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
		$roq=mysqli_fetch_assoc($q);

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
			$updateid=update_record($table, $info,"branch_id='".$bid[$i]."' and product_id='".$pid, $dbcon);
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

	$test = explode('.', $_FILES["file"]["name"]);
	$ext = end($test);
	$name = rand(100, 999) . '.' . $ext;
	$path='../../view/upload/product_images/';
	$location = $path . $name;  
	move_uploaded_file($_FILES["file"]["tmp_name"], $location);

	$info1['im_name']=$name;
	$info1['cdate']=date("Y-m-d");
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['branch_id']			= $POST['branchid'];
	$info1['im_product']			= $POST['pid'];

	$table='tbl_product_images';$tableid='img_id';

	$inserid=add_record($table, $info1, $dbcon);

	echo get_images_product($dbcon,'0');


}
else if(strtolower($POST['mode']) == "load_product_images") {

	if(strtolower($POST['form_mode']) == "edit"){
		$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='".$POST['product_id']."' order by img_id Desc";
	}
	else{
		$q="select * from tbl_product_images where user_id=".$_SESSION['user_id']." and im_product='0' order by img_id Desc";
	}	
	$rel=$dbcon->query($q);
	$path='view/upload/product_images/';
	$str="";
	$str.="<table></tr>";
	while($row  = mysqli_fetch_assoc($rel))
	{
		$str.='<td>
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

	$deleteid=delete_record('tbl_product_images', "img_id=".$POST['eid'], $dbcon);

	if($deleteid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "add_party_purchase") {
	$info1['party_id']			= $POST['party_id'];
	$info1['party_rate']		= $POST['party_rate'];
	$info1['party_product']		= $POST['pid'];
	$info1['cdate'] 			= date("Y-m-d H:i:s");
	$info1['user_id']			= $_SESSION['user_id'];
	$info1['company_id']		= $_SESSION['company_id'];
	$info1['branch_id']			= $POST['branchid'];
			//var_dump($info1);
	$table='tbl_product_party_purchase';$tableid='party_purchase_id';

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
		$roq=mysqli_fetch_assoc($q);
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
		$roq=mysqli_fetch_assoc($q);
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
		left join stage_mst as sm on sm.stage_id=mst.stage_id where mst.party_product='".$POST['product_id']."' order by mst.product_stage_id Desc";
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
		while($rel=mysqli_fetch_assoc($result))
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
	if(strtolower($POST['form_mode']) == "edit"){
		$query="select mst.*,p.l_name from tbl_product_party_purchase as mst 
		left join tbl_ledger as p on p.l_id=mst.party_id where mst.party_product='".$POST['product_id']."' order by mst.party_purchase_id Desc";
	}
	else{
		$query="select mst.*,p.l_name from tbl_product_party_purchase as mst 
		left join tbl_ledger as p on p.l_id=mst.party_id where mst.user_id=".$_SESSION['user_id']." and mst.party_product='0' order by mst.party_purchase_id Desc";
	}

	$result=$dbcon->query($query);
	echo '<div class="clearfix"></div>
	<div class="col-md-12 col-xs-11 margin_row">
	<div class="form-group">
	<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
	<tr id="field">
	<th width="20%" class="text-center">Party</th>
	<th width="10%" class="text-center">Rate</th>
	<th width="10%" class="text-center">Action</th>
	</tr>';
	if(mysqli_num_rows($result)>0)
	{
		$i=1;
		while($rel=mysqli_fetch_assoc($result))
		{
			echo '<tr id="fieldtr'.$id.'" >
			<td style="vertical-align:top;">
			'.$rel['l_name'].'
			</td>
			<td style="vertical-align:top;" class="text-center hide_act_add">
			'.$rel['party_rate'].'
			</td>

			<td style="vertical-align:top" class="text-center">
			<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data_party_purchase('.$rel['party_purchase_id'].');" id="fieldtrnedit'.$i.'"><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_party_purchase('.$rel['party_purchase_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
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
	if(strtolower($POST['form_mode']) == "edit"){
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
		while($rel=mysqli_fetch_assoc($result))
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
		while($rel=mysqli_fetch_assoc($result))
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
else if(strtolower($POST['mode'])== "check_pro_unit"){
	$row=array();
	$tbl=array();

	$sql1=$dbcon -> query("SELECT * FROM tbl_bom WHERE bom_product = '".$_POST['product_id']."' AND bom_status = 0");
	$sql2=$dbcon -> query("SELECT * FROM tbl_purchaseordertrn WHERE product_id = '".$_POST['product_id']."' AND purchaseordertrn_status = 0");
	$sql3=$dbcon -> query("SELECT * FROM tbl_potrancation WHERE product_id = '".$_POST['product_id']."' AND potrancation_status = 0");
	$sql4=$dbcon -> query("SELECT rp.*, ai.* FROM tbl_request_product AS rp LEFT JOIN approve_indent AS ai ON ai.rp_id = rp.rp_id WHERE rp.rp_pid = '".$_POST['product_id']."' AND rp.status = 0 AND ai.approve_indent_status = 0");

	$q = brp_mysqli_fetch_assoc($sql1);
	$qr = brp_mysqli_fetch_assoc($sql2);
	$qry = brp_mysqli_fetch_assoc($sql3);
	$querys = brp_mysqli_fetch_assoc($sql4);

	if(mysqli_num_rows($sql1) > 0){
		if($q['product_base_unit']==$_POST['unit_id']){
			$row['bom_status'] = "1";
		} else{
				// $row['tbl'] = "BOM";
			array_push($tbl, "BOM");
			$row['bom_status'] = "0";
		}
	} else{
		$row['bom_status'] = "1";
	}
	if(mysqli_num_rows($sql2) > 0){
		if($qr['unit_id']==$_POST['unit_id']){
			$row['purchase_status'] = "1";
		} else{
			array_push($tbl, "Purchase Order");
				// $row['tbl'] = "Purchase Order";
			$row['purchase_status'] = "0";
		}
	} else{
		$row['purchase_status'] = "1";
	}
	if(mysqli_num_rows($sql3) > 0){
		if($qry['unit_id']==$_POST['unit_id']){
			$row['purchasebill_status'] = "1";
		} else{
			array_push($tbl, "Purchase Bill");
				// $row['tbl'] = "Purchase Bill";
			$row['purchasebill_status'] = "0";
		}
	} else{
		$row['purchasebill_status'] = "1";
	}
	if(mysqli_num_rows($sql4) > 0){
		if($querys['approve_unit']==$_POST['unit_id']){
			$row['indent_status'] = "1";
		} else{
			array_push($tbl, "Indent");
				// $row['tbl'] = "Indent";
			$row['indent_status'] = "0";
		}
	} else{
		$row['indent_status'] = "1";
	}

	if($row['bom_status']==1 && $row['purchase_status']==1 && $row['purchasebill_status']==1 && $row['indent_status']==1){
		$row['status'] = 1;
	} else {
		$row['status'] = 0;
		$row['table'] = implode(",", $tbl);
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "check_conv_unit"){
	$row=array();
	$tbl=array(); 

	$sql = $dbcon -> query("SELECT * FROM tbl_bom WHERE bom_product = '".$_POST['product_id']."' AND bom_status = 0");
	$sql1 = $dbcon -> query("SELECT * FROM tbl_purchaseordertrn WHERE product_id = '".$_POST['product_id']."' AND purchaseordertrn_status = 0");
	$q = brp_mysqli_fetch_assoc($sql);
	$qr = brp_mysqli_fetch_assoc($sql1);

	if(mysqli_num_rows($sql) > 0){
		if($q['product_conv_unit']==$_POST['unit_id']){
			$row['bom_status'] = "1";
		} else{
				// $row['tbl'] = "BOM";
			array_push($tbl, "BOM");
			$row['bom_status'] = "0";
		}
	}else{
		$row['bom_status'] = "1";
	}
	
	if(mysqli_num_rows($sql1) > 0){
		if($qr['conv_unit_id']==$_POST['unit_id']){
			$row['purchase_status'] = "1";
		} else{
			array_push($tbl, "Purchase Order");
				// $row['tbl'] = "Purchase Order";
			$row['purchase_status'] = "0";
		}
	}else{
		$row['purchase_status'] = "1";
	}

	if($row['bom_status']==1 && $row['purchase_status']==1){
		$row['status'] = 1;
	} else {
		$row['status'] = 0;
		$row['table'] = implode(",", $tbl);
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "preedit_party")
{
	$q = $dbcon -> query("SELECT * FROM tbl_product_party_purchase WHERE party_purchase_id	= '".$POST['id']."'");
	$r = $q->fetch_assoc();

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
}

else if(strtolower($POST['mode'])== "preedit_make")
{
	$q = $dbcon -> query("SELECT * FROM tbl_product_make_purchase as pm WHERE make_purchase_id	= '".$POST['id']."'");
	$r = $q->fetch_assoc();

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "preedit_scrap")
{
	$q = $dbcon -> query("SELECT * FROM tbl_product_scrap as pm WHERE scrap_product_id	= '".$POST['id']."'");
	$r = $q->fetch_assoc();

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "preedit_stage")
{
	$q = $dbcon -> query("SELECT * FROM tbl_product_stage WHERE product_stage_id	= '".$POST['id']."'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_data_party")
{
	$deleteid=delete_record('tbl_product_party_purchase', "party_purchase_id=".$POST['eid'], $dbcon);

	if($deleteid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "delete_data_make")
{
	$deleteid=delete_record('tbl_product_make_purchase', "make_purchase_id=".$POST['eid'], $dbcon);

	if($deleteid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "delete_data_scrap")
{
	$deleteid=delete_record('tbl_product_scrap', "scrap_product_id=".$POST['eid'], $dbcon);

	if($deleteid)
		$row['res']="1";
	else
		$row['res']="0";
	echo json_encode($row);
}
else if(strtolower($POST['mode'])== "delete_data_stage")
{
	$deleteid=delete_record('tbl_product_stage', "product_stage_id=".$POST['eid'], $dbcon);

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
	if(strtolower($POST['form_mode']) == "edit"){
		$query="select mst.*,p.l_name,proc.process_name from tbl_product_job_party_purchase as mst 
		left join tbl_ledger as p on p.l_id=mst.job_party_id 
		left join process_mst as proc on proc.process_id=mst.job_party_process_id
		where mst.job_party_product='".$POST['product_id']."' order by mst.job_party_purchase_id Desc";
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
		while($rel=mysqli_fetch_assoc($result))
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
	$q = $dbcon -> query("SELECT * FROM tbl_product_job_party_purchase WHERE job_party_purchase_id	= '".$POST['id']."'");
	$r = $q->fetch_assoc();

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

	if(strtolower($POST['form_mode']) == "edit"){
		$query="select mst.*,p.p_name,um.unit_name,pmst.process_name from tbl_product_parameter as mst 
		left join unit_mst as um on um.unitid=mst.unit_id
		left join process_mst as pmst on pmst.process_id=mst.process_id
		left join tbl_qc_param as p on p.p_id=mst.param_id where mst.product_id='".$POST['product_id']."' order by mst.process_id";
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
		while($rel=mysqli_fetch_assoc($result))
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
	$q = $dbcon->query("SELECT * FROM tbl_product_parameter WHERE pr_param_id = '".$POST['id']."'");
	$r = $q->fetch_assoc();

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
	$q = $dbcon -> query("SELECT * FROM tbl_product_code_series WHERE pr_type = '$POST[pcode]'");
	$r = $q->fetch_assoc();

	$pr_series=$r['pr_code_series']+1;
	$short_code=$r['pr_code_short'];

	$res['series']=$short_code."".sprintf('%05d',$pr_series);
	$res['code']=$pr_series;

	echo json_encode($res);
}
else if(strtolower($POST['mode']) == "add_process_value") {
	$info1['process_id']= $POST['process_id'];
	$info1['process_rate']= $POST['process_rate'];
	$info1['process_priority']= $POST['process_priority'];
	$info1['process_type']= $POST['process_type'];
	$info1['product_id']= $POST['pid'];
	$info1['process_time']= $POST['process_time'];
	$info1['process_opening']= $POST['process_opening'];
	$info1['resource_id']= $POST['resource_id'];
	$info1['process_loss']= $POST['process_loss'];
	$info1['process_scrap_tolerance_plus']= $POST['process_scrap_tolerance_plus'];
	$info1['process_scrap_tolerance_minus']= $POST['process_scrap_tolerance_minus'];
	$info1['status']= 0;

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

	if(strtolower($POST['form_mode']) == "edit"){
		$query="select mst.*,p.process_name,reso.resource_name from tbl_product_process as mst 
		left join process_mst as p on p.process_id=mst.process_id left join tbl_resource as reso on mst.resource_id=reso.resource_id where mst.status = 0 AND mst.product_id='".$POST['product_id']."'";
	}
	else{
		$query="select mst.*,p.process_name, reso.resource_name from tbl_product_process as mst 
		left join process_mst as p on p.process_id=mst.process_id left join tbl_resource as reso on mst.resource_id=reso.resource_id where mst.status = 0 AND mst.user_id=".$_SESSION['user_id']." $where and mst.product_id='0' ";
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
		while($rel=mysqli_fetch_assoc($result))
		{
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
			</td>
			<td style="vertical-align:top;" class="text-center hide_act_add">
			'.$rel['resource_name'].'
			</td>
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
			<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_process('.$rel['pr_process_id'].','.$rel['process_priority'].','.$rel['product_id'].');" id="fieldtrnremove'.$i.'"><i class="fa fa-times"></i></button>
			</td>	

			</tr>';
			$i++;
		}
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
	$q = $dbcon -> query("SELECT * FROM tbl_product_process WHERE status = 0 AND pr_process_id = '".$POST['id']."'");
	$r = $q->fetch_assoc();

			//$r['producthtml'] = getrequiredproduct($dbcon,$r['raw_product_id'],' and product_type='.$r["product_type"].'');
	echo json_encode($r);
}
else if(strtolower($POST['mode'])== "delete_data_process")
{
	$priority_id = $POST['priority_id'];
	$product_id = $POST['product_id'];

	$get_process_sql = 'select product_id from tbl_product_process where status = 0 AND pr_process_id='.$POST['eid'];
	$exe1 = $dbcon->query($get_process_sql);
	$res_data = brp_mysqli_fetch_assoc($exe1);

	$del_info['status'] = 2;
	// $deleteid=delete_record('tbl_product_process', "pr_process_id=$POST[eid]", $dbcon);
	$deleteid = update_record('tbl_product_process',$del_info,"pr_process_id=".$POST['eid'], $dbcon);

	$q = $dbcon -> query("SELECT * FROM tbl_allocate_process WHERE p_ref_type='process_opening' and p_ref_id=".$POST['eid']."");
	$r = $q->fetch_assoc();

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

		$fetch_sql_process = "SELECT * FROM `tbl_product_process` WHERE status=0 and  product_id = $product_id AND process_priority > $priority_id AND company_id = '".$_SESSION['company_id']."' "; 
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
		$rel=mysqli_fetch_assoc($result);

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
	$tr = $dbcon -> query("SELECT * FROM `tbl_drawing` WHERE drawing_status=0 and `drawing_number` ='".$POST['drawing_number']."' ");
	if($tr->num_rows > 0) {
		echo '-1';
	}
	else {
		$info['drawing_number']	= $POST['drawing_number'];
		$info['drawing_title']	= $POST['drawing_title'];
		$info['vender_id']	= $POST['vender_id'];
		$info['drawing_size']		= $POST['drawing_size'];
		$info['drawing_scale']	= $POST['drawing_scale'];
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['drawing_status']	= 0;
		$info['cdate']	= date('Y-m-d H:i:s');
		$inserpoid=add_record('tbl_drawing', $info, $dbcon);
		if($inserpoid){
			echo $inserpoid;
		}else{
			echo '0';
		}
	}
}
else if(strtolower($POST['mode'])== "load_drawing_number"){
	$drawing_id = getdrawingnumber($dbcon,$POST['drawing_id']); 
	echo $drawing_id;
}
else if(strtolower($POST['mode']) == "load_product_process_qc_show") {
	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
	$where_db = check_branch('mst', $branch_id);
	$where=" $where_db and mst.company_id=".$_SESSION['company_id'];
	$str="";
	if(strtolower($POST['form_mode']) == "edit"){
		$query="select mst.*,p.process_name from tbl_product_process as mst 
		left join process_mst as p on p.process_id=mst.process_id
		where mst.status=0 and  mst.product_id='".$POST['product_id']."'";
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
} else if(strtolower($POST['mode']) == "copy_product_data") {
	$cmode = $_POST['cmode'];
	$product_id = $_POST['product_id'];

	$sql1=$dbcon->query("select * from tbl_product_images where im_status=0 and user_id=".$_SESSION['user_id']." and im_product='".$POST['product_id']."'");
	$sql2=$dbcon->query("select * from tbl_product_party_purchase where party_product='".$POST['product_id']."'");
	$sql3=$dbcon->query("select * from tbl_product_job_party_purchase where job_party_product='".$POST['product_id']."'");
	$sql4=$dbcon->query("select * from tbl_product_process where status=0 and product_id='".$POST['product_id']."'");
	$sql5=$dbcon->query("select * from tbl_product_make_purchase where make_product='".$POST['product_id']."'");
	$sql6=$dbcon->query("select * from tbl_product_parameter where product_id='".$POST['product_id']."'");
	if(brp_mysqli_num_rows($sql1) > 0){
		while($row1=brp_mysqli_fetch_assoc($sql1)){
			$info1['im_name'] = $row1['im_name'];
			$info1['im_status'] = $row1['im_status'];
			$info1['branch_id'] = $row1['branch_id'];
			$info1['cdate']=date("Y-m-d");
			$info1['user_id']	= $_SESSION['user_id'];
			$info1['company_id']	= $_SESSION['company_id'];

			$table1='tbl_product_images';
			$insertid=add_record($table1, $info1, $dbcon);
		}
	}
	if(brp_mysqli_num_rows($sql2) > 0){
		while($row2=brp_mysqli_fetch_assoc($sql2)){
			$info2['party_id']			= $row2['party_id'];
			$info2['party_rate']		= $row2['party_rate'];
				//$info2['party_product']		= $row2['pid'];
			$info2['cdate'] 			= date("Y-m-d");
			$info2['user_id']			= $_SESSION['user_id'];
			$info2['company_id']		= $_SESSION['company_id'];
			$info2['branch_id']			= $row2['branch_id'];
			//var_dump($info1);
			$table2='tbl_product_party_purchase';
			$inserids=add_record($table2, $info2, $dbcon);
		}
	}
	if(brp_mysqli_num_rows($sql3) > 0){
		while($row3=brp_mysqli_fetch_assoc($sql3)){
			$info3['job_party_id']= $row3['party_id'];
			$info3['job_party_process_id']= $row3['job_party_process_id'];
			$info3['job_party_rate']= $row3['party_rate'];
				// $info3['job_party_product']= $row3['pid'];
			$info3['cdate'] = date("Y-m-d");
			$info3['user_id']			= $_SESSION['user_id'];
			$info3['company_id']			= $_SESSION['company_id'];
			$info3['branch_id']			= $row3['branch_id'];

			$table3='tbl_product_job_party_purchase';
			$insertids=add_record($table3, $info3, $dbcon);
		}
	}
	if(brp_mysqli_num_rows($sql4) > 0){
		while($row4=brp_mysqli_fetch_assoc($sql4)){
			$info4['process_id']= $row4['process_id'];
			$info4['process_rate']= $row4['process_rate'];
			$info4['process_priority']= $row4['process_priority'];
			$info4['process_type']= $row4['process_type'];
				// $info4['product_id']= $row4['pid'];
			$info4['process_time']= $row4['process_time'];
			$info4['process_opening']= $row4['process_opening'];
			$info4['resource_id']= $row4['resource_id'];
			$info4['process_loss']= $row4['process_loss'];
			$info4['process_scrap_tolerance_plus']= $row4['process_scrap_tolerance_plus'];
			$info4['process_scrap_tolerance_minus']= $row4['process_scrap_tolerance_minus'];

			$info4['cdate'] = date("Y-m-d");
			$info4['user_id']			= $_SESSION['user_id'];
			$info4['company_id']			= $_SESSION['company_id'];

			$table4='tbl_product_process';
			$insertID=add_record($table4, $info4, $dbcon);
		}
	}
	if(brp_mysqli_num_rows($sql5) > 0){
		while($row5=brp_mysqli_fetch_assoc($sql5)){
			$info5['make_id']= $row5['make_id'];
			$info5['make_number_id']= $row5['make_number_id'];
			$info5['make_value']= $row5['make_value'];
			$info5['make_stock']= $row5['make_stock'];
			$info5['make_rate']= $row5['make_rate'];
				// $info5['make_product']= $row5['pid'];
			$info5['cdate'] = date("Y-m-d");
			$info5['user_id'] = $_SESSION['user_id'];
			$info5['company_id'] = $_SESSION['company_id'];
			$info5['branch_id']	= $row5['branch_id'];

			$table5='tbl_product_make_purchase';

			$insertIds=add_record($table5, $info5, $dbcon);
		}
	}
	if(brp_mysqli_num_rows($sql6) > 0){
		while($row6=brp_mysqli_fetch_assoc($sql6)){	
			$info6['process_id'] = $row6['qc_process_id'];
			$info6['param_id']= $row6['param_id'];
			$info6['param_value']= $row6['param_value'];
			// $info6['product_id']= $row6['pid'];
			if($row6['tolerance_plus']){
				$info6['tolerance_plus']= $row6['tolerance_plus'];
			}
			if($row6['tolerance_minus']){
				$info6['tolerance_minus']= $row6['tolerance_minus'];
			}

			$info6['unit_id']= $row6['param_unit_id'];
			$info6['cdate'] = date("Y-m-d");
			$info6['user_id'] = $_SESSION['user_id'];
			$info6['company_id'] = $_SESSION['company_id'];
			$info6['branch_id'] = $_SESSION['branch_id'];

			$table6='tbl_product_parameter';
			$insertIDs=add_record($table6, $info6, $dbcon);
		}
	}
	echo '1';
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