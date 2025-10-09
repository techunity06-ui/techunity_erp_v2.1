<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	
	// print_r($_POST);
	$post = $_POST;
	$keys = array();
	$values = array();
	foreach($post as $x => $x_value){
		if($x_value!='0'){
			array_push($keys, $x_value);
			array_push($values, $x);
		}
	}
	array_pop($keys);
	array_pop($values);
	//if($pln_qry_rs->num_rows > 0)
	{
		$delimiter = ",";
		$filename = "products_".date('d-M-Y').".csv";
		
		//create a file pointer
		$f = fopen('php://memory', 'w');
		
		//set column headers
		$fields = $keys;
		// $fields = array('Product Type','Product Name','Description','Item Code','HSN Code','Sale Rate','Purchase Rate','GST Type','Sale GST','Purchase GST','Opening Stock','Minimum Stock','Maximum Stock','Category','Making Time','Specifaication','Valuation','Base Unit','Base Qty','Conv. Unit','Conv. Qty');
		// print_r($fields);
		// die;
		fputcsv($f, $fields, $delimiter);
		
		if($post['product_status']=='active'){ $status = 'product_status = 0'; } else if($post['product_status']=='inactive'){ $status = 'product_status = 1'; } else { $status = 'product_status != 2'; }
		
		//Get Semi Pro Name
		$get_pro_qry="select pro.*,sale_gst.tp_per as sale_gst_name,pur_gst.tp_per as pur_gst_name,pro_cat.cat_name,pro_spec.ms_name,base_unit.unit_name as base_unit_name,conv_unit.unit_name as conv_unit_name, hsn.hsn_code as product_hsn, branch.branch_name, drawing.drawing_number, revision.revision_number, godown.gd_name from product_mst as pro 
		left join tbl_tax_per_master as sale_gst on sale_gst.tp_id=pro.product_sale_gst
		left join tbl_tax_per_master as pur_gst on pur_gst.tp_id=pro.product_purchase_gst
		left join tbl_category as pro_cat on pro_cat.cat_id=pro.product_category
		left join mst_material_spec as pro_spec on pro_spec.ms_id=pro.product_specification
		left join unit_mst as base_unit on base_unit.unitid=pro.product_base_unit
		left join unit_mst as conv_unit on conv_unit.unitid=pro.product_conv_unit
		left join mst_hsn_code as hsn on hsn.hsn_id=pro.product_hsn
		left join branch_mst as branch on branch.branch_id=pro.branch_id
		left join tbl_drawing as drawing on drawing.drawing_id=pro.drawing_id
		left join tbl_revision as revision on revision.revision_id=pro.revision_id
		left join mst_godown as godown on godown.gd_id=pro.product_mat_center
		where ".$status." AND pro.company_id =".$_SESSION['company_id'];
		$get_pro_qry_rs=($dbcon->query($get_pro_qry));
		while($pro_rel=mysqli_fetch_assoc($get_pro_qry_rs)){
			$pro_type_name=get_product_type_name($dbcon,$pro_rel['product_type']);
			$res = array();
			foreach($values as $val){
				if($val=='product_type'){
					$pro_type_name=get_product_type_name($dbcon,$pro_rel['product_type']);
					$rel = $pro_type_name;
				}else if($val=='branch_id'){
					$rel = $pro_rel['branch_name'];
				}else if($val=='product_base_unit'){
					$rel = $pro_rel['base_unit_name'];
				}else if($val=='product_conv_unit'){
					$rel = $pro_rel['conv_unit_name'];
				}else if($val=='drawing_id'){
					$rel = $pro_rel['drawing_number'];
				}else if($val=='revision_id'){
					$rel = $pro_rel['revision_number'];
				}else if($val=='product_category'){
					$rel = $pro_rel['cat_name'];
				}else if($val=='bom_required'){
					$rel = ($pro_rel['bom_required']==1) ? "Yes" : "No";
				}else if($val=='batch_wise_stock_manage'){
					$rel = ($pro_rel['batch_wise_stock_manage']==1) ? "Yes" : "No";
				}else if($val=='product_mat_center'){
					$rel = $pro_rel['gd_name'];
				}else if($val=='product_specification'){
					$rel = $pro_rel['ms_name'];
				}else if($val=='product_status'){
					$rel = ($pro_rel['product_status']==1) ? "Inactive" : "Active";
				}else{
					$rel = $pro_rel[$val];
				}
				array_push($res, $rel);
			}
			$lineData = $res;
			fputcsv($f, $lineData, $delimiter);
		}
		// print_r($lineData);
		//move back to beginning of file
		fseek($f, 0);
		
		//set headers to download file rather than displayed
		// header('Content-Type: text/csv');
		// header('Content-Disposition: attachment; filename="' . $filename . '";');
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: ".date('D M d Y H:i:s O'));
		//header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: ".$now." GMT");
		
		// force download  
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		
		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename=".$filename."");
		header("Content-Transfer-Encoding: binary");
		
		//output all remaining data on a file pointer
		fpassthru($f);
	}
	exit;
?>