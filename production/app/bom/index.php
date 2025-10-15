<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
// error_reporting(E_ALL);

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_BOM_LIST_SLUG_VIEW,PRODUCTION_BOM_LIST_SLUG_CREATE,PRODUCTION_BOM_LIST_SLUG_UPDATE,PRODUCTION_BOM_LIST_SLUG_DELETE

]);		

$companyConfiguration=getCompanyConfiguration($dbcon);
$bom_pro_search=$companyConfiguration['bom_pro_search'];
$pro_search=explode(",", $bom_pro_search);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
if(brp_strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$branch=$_SESSION['branch_id'];

	$where='';
	if($POST['product_type']!=''){
		$where.=" and product.product_type=".$POST['product_type'];
	}else{
		 $query = "SELECT GROUP_CONCAT(product_type_id) as pty FROM pro_ms_product_type WHERE process_required=1 and product_type_status IN ('0','1')  and company_id=".$_SESSION['company_id'];
		  $rs_dispatch = $dbcon->query($query);
		  $rel = brp_mysqli_fetch_assoc($rs_dispatch);
		$where.=" and product.product_type in (".$rel['pty'].")";
		//$where.=" and product.product_type in (0,1,2,4)";
	}
	if($POST['child_usr_id']!=''){
		$where.=" and product.product_type=".$POST['child_usr_id'];
	}
		//$where.="  and bom_date>='".date('Y-m-d',strtotime($s_date[0]))."' AND bom_date<='".date('Y-m-d',strtotime($s_date[1]))."'";
		//$where.=" and branch_id=$branch";
	$appData = array();
	$i=1;
	$aColumns = array('bom_id','bom.bom_no','bom_date','bom_close_status','product.product_name','product.product_icode','bom_status','bom.cdate','bom.user_id','bom.bom_product','bom.bom_qty','product.image_name');
	$sIndexColumn = "bom_id";
	$isWhere = array("bom_status=0 and prover.is_default_bom='1' ".$where);
	$sTable = "tbl_bom as bom";			
	$isJOIN = array('left join product_mst as product on product.product_id=bom.bom_product left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id');
		//$hGroupby = array("bom.bom_product");
	$hOrder = "bom.bom_id desc";
	include($include.'pagging.php');
	$appData = array();

	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();

		if($row['image_name']!=null){
			$image_name = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$row["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$row['image_name'].'" style="width: 60px;height: 50px;"></a>';
		}else{
			$image_name = '';
		}

		$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["sr"].'</a>';

			// SANAT  hide BOM No  -  29-07-2021  

		/*$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["bom_no"].'</a>';*/

		$row_data[] = $image_name;

			// SANAT  hide BOM No  -  29-07-2021  

		/*$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'">'.date('d M, Y',strtotime($row["bom_date"])).'</a>';*/


		

		/*$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'">'.date('d M, Y',strtotime($row["bom_date"])).'</a>';*/

		$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["product_name"].'</a>';

		$row_data[] = '<a class="" data-original-title="Edit '.$row["bom_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'">'.$row["product_icode"].'</a>';

			//$row_data[] = $row['bom_qty'];

		if($row['bom_close_status']=='0')
		{ 
			$status_show="<strong style='color:green'>Open</strong>"; 
		} 
		else 
		{ 
			$status_show="<strong style='color:red'>Closed</strong>";  
		}

		$row_data[] = $status_show;

		$sales_order_print='';$invoicestatus='';$delete='';$edit='';

				/*if(in_array(PRODUCTION_BOM_LIST_SLUG_UPDATE,$bulkAccessArray)){
					if($row['bom_close_status']=='0')
					{
						$close_status='<a class="btn btn-xs btn-success" data-original-title="change BOM Status" data-toggle="tooltip" data-placement="top" onClick="change_bom_status('.$row['bom_id'].','.$row['bom_close_status'].')"><i class="fa fa-check-circle"></i></a>';
					}
					else
					{
						$close_status='<a class="btn btn-xs btn-danger" data-original-title="BOM Status Close" data-toggle="tooltip" data-placement="top" onClick="change_bom_status('.$row['bom_id'].','.$row['bom_close_status'].')"><i class="fa fa-window-close"></i></a>';
					}
				}*/
				//$invoicestatus='<a class="btn btn-xs btn-primary" data-original-title="Estimate" data-toggle="tooltip" data-placement="top" href="Javascript:;"><i class="fa fa-thumb-up">Invoice Done</i></a>';
				

				/*if(in_array(PRODUCTION_BOM_LIST_SLUG_VIEW,$bulkAccessArray)){
					$sales_order_print='<a class="btn btn-xs btn-primary" data-original-title="Print" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_print/'.$row['bom_id'].'"><i class="fa fa-print"></i></a>';
				}*/

				
				// $req_po_btn='<a class="btn btn-xs btn-info" data-original-title="Request PO" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_to_po_req/'.$row['bom_id'].'"><i class="fa fa-plus"></i></a>';
				
				//$bom_actual_btn='<a class="btn btn-xs btn-success" data-original-title="Add Actual Qty." data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_actual_add/'.$row['bom_id'].'"><i class="fa fa-plus"></i></a>';
				if(in_array(PRODUCTION_BOM_LIST_SLUG_DELETE,$bulkAccessArray)){
					// $delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bom('.$row['bom_product'].')"><i class="fa fa-trash-o"></i></button>';
				}
				
				if(in_array(PRODUCTION_BOM_LIST_SLUG_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_edit/'.$row['bom_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				
				/*$clone_btn='<a class="btn btn-xs btn-success" data-original-title="Clone BOM" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_clone/'.$row['bom_id'].'"><i class="fa fa-undo"></i></a>';*/

				// SANAT  hide clone button  -  29-07-2021  
				$clone_btn = "";

				/*$clone_btn='<button type="button" class="btn btn-xs btn-success" data-original-title="Clone BOM" data-toggle="tooltip" data-placement="top" onclick="open_copy_bom_model('.$row['bom_id'].');"><i class="fa fa-undo"></i></button>';*/
				
				
				$row_data[] = $sales_order_print.' '.$edit.' '.$delete.' '.$req_po_btn.' '.$clone_btn.' '.$close_status;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(brp_strtolower($POST['mode']) == "add") {
			$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);

			
			$info['bom_no']				= get_dynamic_bom_no($dbcon);
			$info['bom_date']			= date('Y-m-d');
			$info['bom_product']		= $POST['sel_product_id'];
			$info['remark']				= $_POST['remark'];
			$info['product_base_unit']	= $POST['base_unit'];
			$info['product_base_qty']	= $POST['base_qty'];
			$info['product_conv_unit']	= $POST['conv_unit'];
			$info['product_conv_qty']	= $POST['conv_qty'];
			//$info_bom['remark']		= $POST['product_id'];
			$info['bom_status']			= 0;
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['usertype_id']		= $_SESSION['usertype_id'];
			$info['company_id']			= $_SESSION['company_id'];

			/* Sanat :: Added bom_version_id :: 05-08-21 */
			$info['bom_version_id']			= $POST['sel_bom_version_id'];

			
			if(!empty($POST['bom_id'])){
				$updateid11=update_record('tbl_bom', $info, "bom_id=".$POST['bom_id'] , $dbcon);
				$inserestimateid=$POST['bom_id'];
			}else{
				$inserestimateid=add_record('tbl_bom', $info, $dbcon);
				update_common_no($dbcon,5);
			}
			
			/* Update In trn Table Start */
			// $infotrn['bom_id']			= $inserestimateid;
			// $infotrn['sale_product_id']	= $_POST['sel_product_id'];
			// $infotrn['bom_trn_status']	= '0';
			// $updateid=update_record('tbl_bomtrn', $infotrn, "bom_id=0 and user_id='$_SESSION[user_id]'" , $dbcon);
			
			// $infotrn1['p_bom_id']			= $inserestimateid;
			// $infotrn1['p_status']			= '0';
			// $updateid=update_record('tbl_bom_product_process', $infotrn1, "p_bom_id=0 and user_id='$_SESSION[user_id]'" , $dbcon);

			//$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);		
			/* Update In trn Table End */

			// $upd_sls_status= update_sales_order_status($dbcon,$POST['sales_order_id'],$POST['sales_order_pro_id'], 1);	


			if($inserestimateid){	
				$arr['msg']="1";
				$arr['eid']=$inserestimateid;
			//Insert LOG
				$log_entry=common_log_entry($dbcon,"bom_add",1,"tbl_bom",$inserestimateid);
			}
			else{
				$arr['msg']="0";
			}

			echo json_encode($arr);

		}		
		else if(brp_strtolower($POST['mode']) == "edit") {


			// $info['bom_no']				= $POST['bom_no'];
			$info['bom_date']			= date('Y-m-d',strtotime($POST['bom_date']));
			$info['bom_product']		= $_POST['sel_product_id'];
			$info['product_base_unit']	= $POST['base_unit'];
			$info['product_base_qty']	= $POST['base_qty'];
			$info['product_conv_unit']	= $POST['conv_unit'];
			$info['product_conv_qty']	= $POST['conv_qty'];
			$info['remark']				= $_POST['remark'];
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['usertype_id']			= $_SESSION['usertype_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['bom_status']			= 0;
			
			$info['bom_actual_add_status'] = $POST['bom_actual_add'];//Change Status if actual value added 
			
			$updateid=update_record('tbl_bom', $info,"bom_id=".$POST['mode_edit_id'] , $dbcon);
			if($updateid){	
				$arr['msg']="update";
				$arr['eid']=$POST['eid'];
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"bom_add",2,"tbl_bom",$POST['mode_edit_id']);
			}
			else{
				$arr['msg']=0;
			}
			echo json_encode($arr);	
		}
		else if(brp_strtolower($POST['mode']) == "delete") {

			$query="select count(bom_trn_id) as used_count from tbl_bomtrn as bom where bom.bom_id=".$POST['eid'];
			$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

			if($rel['used_count']=="0"){
				$info['bom_status']			= 2;
				$updateestimateid=update_record('tbl_bom', $info, "bom_id=".$POST['eid'], $dbcon);	
				$info1['bom_trn_status']	= 2;
				$updateestimateid1=update_record('tbl_bomtrn', $info1, "bom_id=".$POST['eid'], $dbcon);	

				if($updateestimateid){
					echo "1";	
				}else{
					echo "0";
				}
			}else{
				echo "2";	
			}

		//Insert LOG
			$log_entry=common_log_entry($dbcon,"bom_add",3,"tbl_bom",$POST['eid']);
		}
		else if(brp_strtolower($POST['mode']) == "fieldadd") {

		// echo "<pre>";
		// print_r($POST)

			$bom_id=intval($POST['bom_id']);
			$bom_no=get_dynamic_bom_no($dbcon);

				// $bom_no=load_series_no($dbcon,$POST['invoicetype_id']);
			

		/* $tr = $dbcon -> query("SELECT `product_id` FROM `tbl_bomtrn` WHERE bom_trn_status!=1 and `product_id` ='".$POST['product_id']."' and parent_id='".$POST['parent_id']."' and bom_id='".$bom_id."' ");
		if($tr->num_rows > 0 && !$POST['edit_id']) {
			echo "-1";
		}
		else { */
			if(!empty($POST['bom_id'])){
				$info1['bom_id']		= $POST['bom_id'];
			}else{
				/*$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);

				$bom_no=get_dynamic_bom_no($dbcon);
				$info_bom['bom_no']			= $bom_no;
				$info_bom['bom_date']		= date('Y-m-d');
				$info_bom['bom_product']	= $POST['sel_product_id'];
				$info_bom['product_base_unit']	= $POST['base_unit'];
				$info_bom['product_base_qty']	= $POST['base_qty'];
				$info_bom['product_conv_unit']	= $POST['conv_unit'];
				$info_bom['product_conv_qty']	= $POST['conv_qty'];
				//$info_bom['remark']		= $POST['product_id'];
				$info_bom['bom_status']		= 3;
				$info_bom['cdate']			= date("Y-m-d H:i:s");
				$info_bom['user_id']		= $_SESSION['user_id'];
				$info_bom['usertype_id']	= $_SESSION['usertype_id'];
				$info_bom['company_id']		= $_SESSION['company_id'];
				$info_bom['bom_version_id'] = $POST['p_bom_version_id'];
				
				$inser_bom_id=add_record("tbl_bom",$info_bom, $dbcon);
				$info1['bom_id']		= $inser_bom_id;*/

			}
			if(!empty($POST['p_bom_id'])){
				$info1['p_bom_id']		= $POST['p_bom_id'];
			}else{
				
				$p_bom_no=load_series_no($dbcon,$POST['invoicetype_id']);
				
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
				$bom_no=get_dynamic_bom_no($dbcon);
			//	$info_p_bom['bom_no']		= $p_bom_no;
				$info_p_bom['tot_standrad_qty']	= $POST['tot_standrad_qty'];
				$info_p_bom['bom_no']		= $bom_no;
				$info_p_bom['bom_date']		= date('Y-m-d');
				$info_p_bom['bom_product']	= $POST['product_id'];
				$info_p_bom['product_base_unit']	= $POST['product_base_unit'];
				$info_p_bom['product_base_qty']		= $POST['product_base_qty'];
				$info_p_bom['product_conv_unit']	= $POST['product_conv_unit'];
				$info_p_bom['product_conv_qty']		= $POST['product_conv_qty'];
				$info_p_bom['cdate']		= date("Y-m-d H:i:s");
				$info_p_bom['user_id']		= $_SESSION['user_id'];
				$info_p_bom['usertype_id']	= $_SESSION['usertype_id'];
				$info_p_bom['company_id']	= $_SESSION['company_id'];
				$info_p_bom['bom_version_id'] = $POST['bom_version_id'];
				$info_p_bom['conversation_factor']		= $POST['conversation_factor'];
				$inser_p_bom_id=add_record("tbl_bom",$info_p_bom, $dbcon);
				update_common_no($dbcon,5);
				$info1['p_bom_id']		= $inser_p_bom_id;
				
			}
			/* base_qty 
			conv_qty
			 product_base_qty
			 product_conv_qty
			 bom_id $info1['bom_id'] */
			 $queryq="select * from tbl_bom as bom where bom_status!=2 and bom.bom_id=".$info1['bom_id'];
			 $relq=brp_mysqli_fetch_assoc($dbcon->query($queryq));
			 if($relq['bom_status']!=3){
			 	$base_qty=$POST['product_base_qty']/$POST['base_qty']*$relq['product_base_qty'];

			// var_dump($POST['product_base_qty']);
			// var_dump($POST['base_qty']);
			// var_dump($relq['product_base_qty']);
			// var_dump($queryq);
			 	$conv_qty=$POST['product_conv_qty']/$POST['conv_qty']*$relq['product_conv_qty'];
			 }else{
			 	$base_qty=$POST['product_base_qty']/$POST['base_qty']*$POST['base_qty'];
			 	$conv_qty=$POST['product_conv_qty']/$POST['conv_qty']*$POST['conv_qty'];
			 }
			 $base_qty = $POST['product_base_qty'];
			 $conv_qty = $POST['product_conv_qty'];


			/*
			Code By Sanat : 06-08-2021
			Comment : Below code is update active status if any changes in bom version
			START
			*/

			$active_status['bom_active_status'] = 0;
			$condition = 'product_id = ' . $POST['sel_product_id'] . ' AND bom_version_id = ' . $POST['p_bom_version_id']; 

			$update_id=update_record('pro_ms_bom_version',$active_status, $condition, $dbcon);
			
			/* END */

			$info1['product_id']	= $POST['product_id'];
			$info1['bom_version_id'] = $POST['bom_version_id'];
			$info1['p_bom_version_id'] = $POST['p_bom_version_id'];
			
			//$info1['product_base_qty']	= $POST['product_base_qty'];
			$info1['product_base_qty']	= $base_qty;
			$info1['product_base_unit']	= $POST['product_base_unit'];
			
			$info1['product_conv_unit']	= $POST['product_conv_unit'];
			$info1['product_conv_qty']	= $conv_qty;
			//$info1['product_conv_qty']	= $POST['product_conv_qty'];
			
			/*
			Code By Umair : 01-06-2021
			Comment : Below code is commented by umair
			START
			*/
			/*$info1['product_width']		= $POST['product_width'];
			$info1['product_height']	= $POST['product_height'];
			$info1['product_thickness']	= $POST['product_thickness'];
			$info1['product_density']	= $POST['product_density'];*/
			/*END*/

			$info1['product_kg']		= $POST['product_kg'];
			$info1['conversation_factor']		= $POST['conversation_factor'];
			
			$info1['user_id']			= $_SESSION['user_id']; 	
			$info1['company_id']		= $_SESSION['company_id']; 	
			$info1['branch_id']			= $_SESSION['branch_id']; 
			$info1['bom_trn_status']	= "0";
			
			$table='tbl_bomtrn';$tableid='bom_trn_id';

			$materialinfo['bom_id'] = $info1['bom_id'];
			
			$materialinfo['product_id'] = $info1['product_id'];
			$materialinfo['user_id'] = $_SESSION['user_id'];
			$materialinfo['company_id'] = $_SESSION['company_id'];
			$materialinfo['bom_material_trn_status'] = 0;	

			if(empty($POST['edit_id']))
			{
				$inserid=add_record($table, $info1, $dbcon);

				/*
				Code By Umair : 01-06-2021
				Comment : Insert Parmater Data Into tbl_bom_material_trn table
				START
				*/
				foreach ($POST['values'] as $p_key => $p_value) {
					$p_name = brp_strtolower($p_value['name']);
					$materialinfo['bom_trn_id'] = $inserid;
					$materialinfo['material_parameter_id'] = str_replace('product_', '', $p_name);
					$materialinfo['material_parameter_value'] = $p_value['value'];

					add_record('tbl_bom_material_trn', $materialinfo, $dbcon);
				}
				/*
				END
				*/

				$info1['bom_trn_status'] = "0";
				echo "1";
			}
			else
			{
				$updateid=update_record($table,$info1,$tableid."=".$POST['edit_id'] , $dbcon);	

				/*
				Code By Umair : 01-06-2021
				Comment : Insert Parmater Data Into tbl_bom_material_trn table
				START
				*/
				$dbQuery = "delete from tbl_bom_material_trn Where bom_trn_id='".$POST['edit_id']."' and product_id='".$info1['product_id']."' "; 
				$dbcon->query($dbQuery);

				foreach ($POST['values'] as $p_key => $p_value) {
					$p_name = brp_strtolower($p_value['name']);
					$materialinfo['bom_trn_id'] = $POST['edit_id'];
					$materialinfo['material_parameter_id'] = str_replace('product_', '', $p_name);
					$materialinfo['material_parameter_value'] = $p_value['value'];
					add_record('tbl_bom_material_trn', $materialinfo, $dbcon);
				}
				/*
				END
				*/

				echo "2";
			}

		//}
		}	

		else if(brp_strtolower($POST['mode']) == "load_tempoutward1") {

			$thread=$POST['thread'];
			$sel_product_qty=$POST['sel_product_qty'];
			$level=$POST['level'];

			if(brp_strtolower($POST['form_mode']) == "edit"){
				/* Sanat ::  added bom version id condition -  04-08-2021*/
				$query="select mst.*,product.product_name,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name,product.product_type from tbl_bomtrn as mst 
				left join product_mst as product on product.product_id=mst.product_id 
				left join unit_mst as u on u.unitid=mst.product_base_unit
				where bom_trn_status=0 and mst.parent_id=".$POST['parent_id']." and mst.p_bom_version_id = ". $POST['bom_version_id'] ." and bom_id=".$POST['bom_id']." order by bom_trn_id Desc";
				$old_qty='';
			}
			else{
				$query="select mst.*,product.product_name,product.product_desc,product.product_setting_check,product.product_id,u.unit_name,product.product_type from tbl_bomtrn as mst 
				left join product_mst as product on product.product_id=mst.product_id 
				left join unit_mst as u on u.unitid=mst.product_uom
				where bom_trn_status=0 and mst.parent_id=".$POST['parent_id']." and bom_id=0 and mst.user_id=".$_SESSION['user_id'];
			}
		//echo $query;

			$result=$dbcon->query($query);
			echo '<div class="col-md-8 col-md-offset-2">
			<div class="form-group">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="8%">#</th>
			<th class="text-center" width="28%">Product Type</th>
			<th class="text-center" width="28%">Product Name<!--<br/>
			<input type="text" class="form-control" id="fil_product_search" placeholder="Search Product Name" value="" />-->
			</th>
			<th class="text-center hide_act_add" width="8%"> Qty </th>
			<th class="text-center hide_act_add" width="8%">Unit </th>
			<th class="text-center hide_act_add" width="8%">Visible </th>
			<th class="text-center" width="8%">Action</th>
			<th class="text-center po_req_mode" width="15%">
			<button type="button" onclick="bom_req_po()" class="btn btn-primary" title="Request PO for Due Products">PO <i class="fa fa-send"></i></button> 
			<input type="checkbox" id="all_chk_box" onclick="load_chk_box()" style="width: 23px;height: 23px;margin-top: 0px;">
			</th>
			</tr>
			<tbody id="fil_product_tbl">';
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$setting_array=explode(",",$rel['product_setting_check']);
					$check_pr_type_process = check_process_product_type($dbcon,$rel['product_type']);
					if($check_pr_type_process == '0')
					{
						$href="";
						$style="style='color:black !important;'";

					}
					else
					{
						$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['bom_trn_id']."'";
				//$href="href='".ROOT.PRODUCTION_ROOT."bom_add_sub_product/".$rel['bom_trn_id']."/".$rel['product_type']."/".$rel['product_grp']."/".$rel['product_id']."/".$level."/".$POST['bom_id']."/".$rel['product_qty']."/".$rel['sale_product_id']."'";

						$style="style='border-bottom:dotted 2px blue;'";
					}

					if($rel['po_req_status']=='1'){
						$dyn_text='Requested';
					}
					else{
						$dyn_text='<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id'.$i.'" value="'.$rel['bom_trn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;">';
					}


					if($rel['po_visible_status']=='0')
					{
						$check="checked";
					}
					else
					{
						$check="";
					}

					echo '<tr id="fieldtr'.$id.'" >

					<td style="vertical-align:top;">
					1.'.$i.'
					</td>

					<td style="vertical-align:top;">
					'.get_product_type_by_id($dbcon,$rel['product_type']).'
					</td>

					<td style="vertical-align:top;">
					<a '.$href.' '.$style.' >'.$rel['product_name'].'</a>'. $button. '
					<br/>'.$rel['product_desc'].'
					</td>

					<td style="vertical-align:top;">
					'.$rel['product_act_qty'].'
					</td>

					<td style="vertical-align:top;">
					'.$rel['unit_name'].'
					</td>

					<td>
					<input type="checkbox" name="visible_pro[]" id="chkv'.$rel['bom_trn_id'].'" value="1" '.$check.' onChange="update_visible('.$rel['bom_trn_id'].')" />
					</td>

					<td style="vertical-align:top">

					<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit_data('.$rel['bom_trn_id'].',\' tbl_bomtrn\',\'bom_trn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
					
					<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['bom_trn_id'].',\' tbl_bomtrn\',\'bom_trn_id\');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
					';
					
					echo '</td>

					<td style="vertical-align:top;text-align:center;" class="po_req_mode">
					'.$dyn_text.'
					</td>
					</tr>';
					$i++;
				}

			}
			else{
				echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</tbody></table>			 
			</div>
			</div>	';
		}
		else if(brp_strtolower($POST['mode']) == "load_alloted_tempoutward") {
			/* Sanat ::  added bom version id condition -  04-08-2021*/
			$query="select mst.*,tb.bom_id as bid,tb_t.tot_standrad_qty,product.product_name, product.product_icode,product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,tb.bom_no,tb.bom_date,bver.version_name from tbl_bomtrn as mst 
			inner join tbl_bom as tb on tb.bom_id=mst.bom_id
			left join tbl_bom as tb_t on tb_t.bom_id=mst.p_bom_id
			left join product_mst as product on product.product_id=mst.product_id 
			left join unit_mst as u on u.unitid=mst.product_base_unit
			left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
			left join pro_ms_bom_version as bver on bver.bom_version_id = mst.bom_version_id
			where mst.bom_trn_status=0 and tb.bom_product=".$POST['sel_product_id']." and mst.p_bom_version_id = ". $POST['bom_version_id'] ." order by mst.bom_trn_id asc";
		//exit;

			$multiplication=check_multiplication($dbcon,$POST['sel_product_id'],'');

		//exit;

		/*
		Code By Umair: 25/11/2020
		Comment: Get The Base Qty Of The Parent Product.	
		*/
		$main_base_qty = $POST['base_qty'];
		$main_conv_qty = $POST['conv_qty'];

		
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="10%">Product Type</th>
		
		<th class="text-center" width="10%">BOM No</th>
		<th class="text-center" width="8%">BOM Date</th>
		<th class="text-center" width="10%">BOM Version</th>
		<th class="text-center" width="28%">Product Name</th>
		<th class="text-center" width="10%">Product Itemcode</th>
		<th class="text-center hide_act_add" width="8%">Unit </th>
		<th class="text-center hide_act_add" width="10%"> Qty </th>

		<th class="text-center hide_act_add" width="8%">UOM </th>
		<th class="text-center hide_act_add" width="10%">ACtual Qty.</th>

		<th class="text-center hide_act_add" width="5%">Visible </th>
		<th class="text-center" width="10%">Action</th>
		<th class="text-center po_req_mode" width="15%">
		<button type="button" onclick="bom_req_po()" class="btn btn-primary" title="Request PO for Due Products">PO <i class="fa fa-send"></i></button> 
		<input type="checkbox" id="all_chk_box" onclick="load_chk_box()" style="width: 23px;height: 23px;margin-top: 0px;">
		</th>
		</tr>
		<tbody id="fil_product_tbl">';
		if(brp_mysqli_num_rows($result)>0)
		{

			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$pro_base_qty = calculate_pro_base_qty($dbcon, $POST['sel_product_id'], $rel['product_id'], $main_base_qty);
				$pro_conv_qty = calculate_pro_conv_qty($dbcon, $POST['sel_product_id'], $rel['product_id'], $main_conv_qty);
			// echo "<pre>";
			// print_r($rel);
			// exit;
				$setting_array=explode(",",$rel['product_setting_check']);
				$check_pr_type_process = check_process_product_type($dbcon,$rel['ptype']);
					if($check_pr_type_process == '0')
				// if($rel['ptype']=='3' || $rel['ptype']=='5')
				{
					$href="";
					$last_param=base64_encode($_POST['lastparam'].','.$rel['bom_id']);


				//echo $_POST['lastparam'];
		        // $href="href='#'";
			//	 $href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."'";
				 // $href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."/".$last_param."'";
					$style="style='color:black !important;'";

				}
				else
				{
					$product_id=$rel['product_id'];
					$bom_version_id = $rel['bom_version_id'];
					$pro_query="select pro_bom_process_id from pro_bom_process where process_status = 0 and product_id=".$product_id." and bom_version_id = " . $bom_version_id;
					$pro_result=$dbcon->query($pro_query);
					$count=brp_mysqli_num_rows($pro_result);



					$last_param=base64_encode($_POST['lastparam'].','.$rel['bom_id']);

				//exit;

					if($count > 0){
						$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."/".$last_param."/".$rel['p_bom_version_id']."'";
					}else{
						$href='onClick="process_error()"';
					}


				//$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['bom_trn_id']."'";
				// $href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."'";
				//$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$POST['bom_id']$rel['bom_trn_id']."'";
				//$href="href='".ROOT.PRODUCTION_ROOT."bom_add_sub_product/".$rel['bom_trn_id']."/".$rel['product_type']."/".$rel['product_grp']."/".$rel['product_id']."/".$level."/".$POST['bom_id']."/".$rel['product_qty']."/".$rel['sale_product_id']."'";

					$style="style='border-bottom:dotted 2px blue;cursor:pointer;'";
				}

				if($rel['po_req_status']=='1'){
					$dyn_text='Requested';
				}
				else{
					$dyn_text='<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id'.$i.'" value="'.$rel['bom_trn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;">';
				}


				if($rel['po_visible_status']=='0')
				{
					$check="checked";
				}
				else
				{
					$check="";
				}
			
					if($check_pr_type_process == '0')
				// if($rel['ptype']=='3' || $rel['ptype']=='5')
				{
					$add_process = "";
				}else{
					$add_process = '<a class="btn btn-xs btn-primary" data-original-title="Add Process" data-toggle="tooltip" onclick="direct_show_product_process('.$rel['product_id'].','.$rel['bom_version_id'].','.$rel['bom_trn_id'].')" data-placement="top"><i class="fa fa-plus"></i></a>';
				}

					$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }	
				        $btn_document = '<button type="button" style="margin:5px;" class="btn btn-round btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Document Upload" onclick="open_add_view_documents('.$rel['p_bom_id'].','.$rel['bom_version_id'].');" id="btn_doc'.$i.'"><i class="fa fa-plus"></i> Add Document</button>';


				echo '<tr id="fieldtr'.$id.'" >
				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				
				<td style="vertical-align:top;">
				'.get_product_type_by_id($dbcon,$rel['ptype']).'
				</td>

				<td style="vertical-align:top;">
				'.$rel['bom_no'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['bom_date'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['version_name'].'
				</td>

				
				<td style="vertical-align:top;">
				<a '.$href.' '.$style.' >'.$rel['product_name'].'</a>'. $button. '
				<br/>'.' '.$item_code. ' '.$drawing_number . '</br>' .$rel['product_desc'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['product_icode'].'
				</td>
				
				<td style="vertical-align:top;">
				'.$rel['base_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.(($rel['product_base_qty']/$main_base_qty)*get_proudct_multiple_qty($dbcon,$POST['sel_product_id'],$POST['id2'])).'
				</td>	
				<td style="vertical-align:top;">
				'.$rel['conv_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.(($rel['product_conv_qty']/$main_base_qty)*get_proudct_multiple_qty($dbcon,$POST['sel_product_id'],$POST['id2'])).'
				</td>
				
				<td>
				<input type="checkbox" name="visible_pro[]" id="chkv'.$rel['bom_trn_id'].'" value="1" '.$check.' onChange="update_visible('.$rel['bom_trn_id'].')" />
				</td>
				
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-pid="'.$rel['product_id'].'" data-placement="top" title="Edit" onclick="edit_data('.$rel['bom_trn_id'].','.$rel['product_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

				<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['bom_trn_id'].',\' tbl_bomtrn\',\'bom_trn_id\');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
				';

				echo ' ' . $add_process .'  '. $btn_document .'</td>
				
				<td style="vertical-align:top;text-align:center;" class="po_req_mode">
				'.$dyn_text.'
				</td>
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</tbody></table>			 
		</div>
		</div>	';
	}
	else if(brp_strtolower($POST['mode']) == "load_tempoutward") {
		/* Sanat ::  added bom version id condition -  04-08-2021*/

		$query="select mst.*,tb.bom_id as bid,product.product_name,product.product_icode, product.product_type as ptype,product.product_desc,product.product_setting_check,product.product_id,product.product_base_unit,u.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,dr.drawing_number,product.drawing_id,product.image_name,tb.bom_no,tb.bom_date,bver.version_name, dr.drawing_number from tbl_bomtrn as mst 


		inner join tbl_bom as tb on tb.bom_id=mst.bom_id
		left join product_mst as product on product.product_id=mst.product_id 
		left join unit_mst as u on u.unitid=mst.product_base_unit
		left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
		left join tbl_drawing as dr on dr.drawing_id = product.drawing_id
		left join pro_ms_bom_version as bver on bver.bom_version_id = mst.bom_version_id
		where mst.bom_trn_status=0 and tb.bom_product=".$POST['sel_product_id']." and mst.p_bom_version_id = ". $POST['bom_version_id'] ." order by mst.bom_trn_id asc";
		//exit;

			// echo $query;die;
		
		$result=$dbcon->query($query);

		echo '<div class="col-md-12">
		<div class="form-group">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="5%">#</th>
		<th class="text-center" width="10%">Product Type</th>
		<!-- <th class="text-center" width="10%">Product Image</th> -->
		<th class="text-center" width="10%">BOM No</th>
		<th class="text-center" width="8%">BOM Date</th>
		<th class="text-center" width="10%">BOM Version</th>
		<th class="text-center" width="18%">Product Name
		</th>
		<th class="text-center" width="10%">Product Itemcode
		</th>
		<th class="text-center hide_act_add" width="5%">Unit </th>
		<th class="text-center hide_act_add" width="8%"> Qty </th>

		<th class="text-center hide_act_add" width="5%">UOM </th>
		<th class="text-center hide_act_add" width="8%">Actual Qty.</th>

		<th class="text-center hide_act_add" width="5%">Visible </th>
		<th class="text-center" width="10%">Action</th>
		<th class="text-center po_req_mode" width="15%">
		<button type="button" onclick="bom_req_po()" class="btn btn-primary" title="Request PO for Due Products">PO <i class="fa fa-send"></i></button> 
		<input type="checkbox" id="all_chk_box" onclick="load_chk_box()" style="width: 23px;height: 23px;margin-top: 0px;">
		</th>
		</tr>
		<tbody id="fil_product_tbl">';
		if(brp_mysqli_num_rows($result)>0)
		{


			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
			// echo "<pre>";
			// print_r($rel);
			// // exit;
				$setting_array=explode(",",$rel['product_setting_check']);
				$check_pr_type_process = check_process_product_type($dbcon,$rel['ptype']);
				// var_dump($rel['ptype']);
				// var_dump($check_pr_type_process);
				if($check_pr_type_process == '0')
				// if($rel['ptype']=='3'  || $rel['ptype']=='5')
				{
					$href="";
					$a=base64_encode ('1,2');
				//echo base64_decode($a);
				//$last_param=$rel['bom_id'];
					$last_param=base64_encode($rel['bom_id']);
				// $href="href='#'";
				 // $href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."/".$last_param."'";
					$style="style='color:black !important;'";

				}
				else
				{
					$product_id=$rel['product_id'];
					$bom_version_id = $rel['bom_version_id'];
					$pro_query="select pro_bom_process_id from pro_bom_process where process_status = 0 and product_id=".$product_id." and bom_version_id = " . $bom_version_id;
					$pro_result=$dbcon->query($pro_query);
					$count=brp_mysqli_num_rows($pro_result);

					$last_param=base64_encode($rel['bom_id']);
				//$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['bom_trn_id']."'";
				// echo $pro_query;
					if($count > 0){
						$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$rel['p_bom_id']."/".$rel['bom_id']."/".$last_param."/".$rel['p_bom_version_id']."'";
					}else{
						$href='onClick="process_error()"';
					}


				//$href="href='".ROOT.PRODUCTION_ROOT."bom_allocate/".$POST['bom_id']$rel['bom_trn_id']."'";
				//$href="href='".ROOT.PRODUCTION_ROOT."bom_add_sub_product/".$rel['bom_trn_id']."/".$rel['product_type']."/".$rel['product_grp']."/".$rel['product_id']."/".$level."/".$POST['bom_id']."/".$rel['product_qty']."/".$rel['sale_product_id']."'";

					$style="style='border-bottom:dotted 2px blue;cursor:pointer;'";
				}
		//	exit;
				if($rel['po_req_status']=='1'){
					$dyn_text='Requested';
				}
				else{
					$dyn_text='<input type="checkbox" name="bom_trn_id[]" class="chk_box" id="bom_trn_id'.$i.'" value="'.$rel['bom_trn_id'].'" style="width: 23px;height: 23px;margin-top: 0px;">';
				}


				if($rel['po_visible_status']=='0')
				{
					$check="checked";
				}
				else
				{
					$check="";
				}
				$product_base_qty=number_format($rel['product_base_qty'],5,'.','');
				$product_conv_qty=number_format($rel['product_conv_qty'],5,'.','');

				if($rel['drawing_id']!=0){
					$drawing_number = $rel['drawing_number'];
				}else{
					$drawing_number = '0';
				}

				if($rel['image_name']!=null){
					$image_name = '<a href="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel["image_name"].'" target="_blank"><img src="'.ROOT.ADMINISTRATION_ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;"></a>';
				}else{
					$image_name = '';
				}
				$check_pr_type_process = check_process_product_type($dbcon,$rel['ptype']);
				if($check_pr_type_process == '0'){
				// if($rel['ptype']=='3'  || $rel['ptype']=='5'){
					$add_process = "";
				}else{
					$add_process = '<a class="btn btn-xs btn-primary" data-original-title="Add Process" data-toggle="tooltip" onclick="direct_show_product_process('.$rel['product_id'].','.$rel['bom_version_id'].','.$rel['bom_trn_id'].')" data-placement="top"><i class="fa fa-plus"></i></a>';
				}

				$drawing_number = "";
					$item_code = "";
					 if(in_array('drawing',$pro_search)){
				            $drawing_number = " -- (".$rel['drawing_number'].")";
				        }
				        if(in_array('item',$pro_search)){
				            $item_code = " -- (".$rel['product_icode'].")";
				        }

				 $btn_document = '<button type="button" style="margin:5px;" class="btn btn-round btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Document Upload" onclick="open_add_view_documents('.$rel['p_bom_id'].','.$rel['bom_version_id'].');" id="btn_doc'.$i.'"><i class="fa fa-plus"></i> Add Document</button>';       	

				echo '<tr id="fieldtr'.$id.'" >

				
				<td style="vertical-align:top;">
				'.$i.'
				</td>
				
				<td style="vertical-align:top;">
				'.get_product_type_by_id($dbcon,$rel['ptype']).'
				</td>

			<!--	<td style="vertical-align:top;">
				'.$image_name.'
				</td>  -->

				<td style="vertical-align:top;">
				'.$rel['bom_no'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['bom_date'].'
				</td>

				<td style="vertical-align:top;">
				'.$rel['version_name'].'
				</td>


				<td style="vertical-align:top;">
				<a '.$href.' '.$style.' >'.$rel['product_name'].'</a>'. $button. '
				<br/>'.' '.$item_code.' '.$drawing_number.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['product_icode'].'
				</td>
				<td style="vertical-align:top;">
				'.$rel['base_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$product_base_qty.'
				</td>
				<td style="vertical-align:top;">
				'.$rel['conv_unit_name'].'
				</td>

				<td style="vertical-align:top;">
				'.$product_conv_qty.'
				</td>
				
				<td>
				<input type="checkbox" name="visible_pro[]" id="chkv'.$rel['bom_trn_id'].'" value="1" '.$check.' onChange="update_visible('.$rel['bom_trn_id'].')" />
				</td>
				
				<td style="vertical-align:top">
				<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit_data('.$rel['bom_trn_id'].',\' tbl_bomtrn\',\'bom_trn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>

				<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_data('.$rel['bom_trn_id'].',\' tbl_bomtrn\',\'bom_trn_id\');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
				';

				echo ' '. $add_process. '  '. $btn_document  . '</td>
				
				<td style="vertical-align:top;text-align:center;" class="po_req_mode">
				'.$dyn_text.'
				</td>
				</tr>';
				$i++;
			}
		}
		else{
			echo '<tr><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</tbody></table>			 
		</div>
		</div>	';

	}
	else if(brp_strtolower($POST['mode'])== "preedit")
	{
		$q = $dbcon -> query("SELECT mst.*,pro.product_name,pro.product_specification,pro.product_type as ptype,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM tbl_bomtrn as mst 
			left join product_mst as pro on mst.product_id=pro.product_id
			left join unit_mst as bunit on bunit.unitid=mst.product_base_unit
			left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
			WHERE bom_trn_id = '$POST[id]'");

		$r = $q->fetch_assoc();
		// print_r($r);die;
		$multiplication=check_multiplication($dbcon,$POST['sel_product_id'],'');


		$pro_base_qty = calculate_pro_base_qty($dbcon, $POST['sel_product_id'], $POST['child_id'], $POST['base_qty']);
		$pro_conv_qty = calculate_pro_conv_qty($dbcon, $POST['sel_product_id'], $POST['child_id'], $POST['conv_qty']);
		// // echo "<pre>";
		// // print_r($r);
		// // exit;

		/*Code By Umair : 19-10-2020
			Below code is written by umair to resolve the qty issue at edit time.
		*/ 
			if($POST['id2']!='0'){

			/*$r['product_base_qty']=(($r['product_base_qty']/$multiplication)*get_proudct_multiple_qty($dbcon,$POST['sel_product_id'],$POST['id2']));
			$r['product_conv_qty']=(($r['product_conv_qty']/$multiplication)*get_proudct_multiple_qty($dbcon,$POST['sel_product_id'],$POST['id2']));*/

			// $r['product_base_qty'] =  number_format($pro_base_qty, 5, ".", "");
			// $r['product_conv_qty'] =  number_format($pro_conv_qty, 5, ".", "");
			
			// $r['product_base_qty_hide'] =  $pro_base_qty;
			// $r['product_conv_qty_hide'] =  $pro_conv_qty;
		}
		
		
		$r['producthtml'] = getrequiredproduct($dbcon,$r['product_id'],' and product_type='.$r["ptype"].'');
		$r['product_spec_hid']=get_pro_field($dbcon,$r['product_id'],'product_specification');

		/*
			Code By Umair: 1-06-2021
			Comment : Below Code is use for product specification dynamically
			START
		*/
			$html = '';	
			if($r['product_specification']!='' && $r['product_specification']!='0'){
				$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
				$rs_parameter=$dbcon->query($param_sql);	
				while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
					$parameter_name = ucfirst(brp_strtolower($rel_param['material_parameter_name']));	
					$parameter_id = 'product_'.$rel_param['material_parameter_id'];	

					$material_parameter_id = $rel_param['material_parameter_id'];

					$param_trn_sql = "select * from mst_material_spec_trn where material_parameter_id = '".$material_parameter_id."' and ms_id='".$r['product_specification']."' ";
					$rs_exec=$dbcon->query($param_trn_sql);	
					$rel_data=brp_mysqli_fetch_assoc($rs_exec);
					if($rel_data['material_parameter_value']){

						$bom_trn_sql = "select * from tbl_bom_material_trn where material_parameter_id = '".$material_parameter_id."' and bom_trn_id='".$POST['id']."' ";

						$bom_rs_exec=$dbcon->query($bom_trn_sql);	
						$bom_data=brp_mysqli_fetch_assoc($bom_rs_exec);
						$bom_material_parameter_value = $bom_data['material_parameter_value'];

						$html .= $parameter_name. ' : <input type="text" class="form-control get_ms_kg" name="'.$parameter_id.'" id="'.$parameter_id.'" value="'.$bom_material_parameter_value.'" data-parameter="'.$material_parameter_id.'" data-msid="'.$r['product_specification'].'" onkeyup="get_ms_kg();" />';
					}
				}
			// echo $html;die;
				if($html!=''){
					$html .= '<input type="hidden" name="msid" id="msid" value="'.$r['product_specification'].'">';
					$html .= '<input type="text" class="form-control" name="product_kg" id="product_kg" value="" readonly /> 
					<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET'; 
				}
			}
			$r['product_specification_code']=$html;
			/* END */
			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode'])== "load_product_data")
		{
			/*Sanat ::  added bom_version_id qquery  03-08-2021 */
		
			$query="select bom_id from tbl_bom where bom_status!=2 and bom_product=".$POST['product_id']." and company_id=".$_SESSION['company_id']. " AND bom_version_id = " .$POST['bom_version_id'];

			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);

			$bom_id=$row['bom_id'];

			$q = $dbcon -> query("SELECT mst.product_name,mst.product_base_unit,mst.product_base_qty,mst.product_conv_unit,mst.product_conv_qty,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name, drw.drawing_number, rev.revision_number, mst.drawing_id, mst.revision_id
				FROM product_mst as mst 
				left join unit_mst as bunit on bunit.unitid=mst.product_base_unit
				left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
				left join tbl_drawing as drw on drw.drawing_id=mst.drawing_id
				left join tbl_revision as rev on rev.drawing_id=mst.revision_id
				WHERE product_id=".$POST['product_id']);
			$r = $q->fetch_assoc();


			$r['bom_id']=$bom_id;
			$r['bom_version_id']=$POST['bom_version_id'];

			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode'])== "convert_qty1")
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
		//$ret_qty_new=number_format($ret_qty, 5, ".", "");
			$ret_qty=$ret_qty;
			echo $ret_qty;

		/* $row['show_qty']=$ret_qty_new;
		$row['hide_qty']=$ret_qty;
		echo json_encode($row); */
	}
	else if(brp_strtolower($POST['mode'])== "convert_qty")
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
		$ret_qty_new=number_format($ret_qty, 5, ".", "");
		//$ret_qty=$ret_qty;
	//	echo $ret_qty;
		$row['show_qty']=$ret_qty_new;
		$row['hide_qty']=$ret_qty;
		echo json_encode($row);
	}
	else if(brp_strtolower($POST['mode'])== "delete_data")
	{
		$row=array();
		/*Code By Umair: 20/10/2020
			I have commented the delete query and set the update query for delete the data.
		*/	
		//$dbcon->query("delete from tbl_bomtrn where bom_trn_id='$POST[eid]'");
			$info['bom_trn_status'] = 2;	
			$updateid=update_record($_POST['table'], $info,$_POST['whereid']."=".$POST['eid'] , $dbcon);


		/* 
			Code By Umair: 01/06/2021
			Comment: Delete record of material specification from tbl_bom_material_trn table
			START
		*/	
			$dbQuery = "delete from tbl_bom_material_trn Where bom_trn_id='".$POST['eid']."' "; 
			$dbcon->query($dbQuery);
			/* END */

			$row['res']="1";

			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])=="load_product")
		{
			$type_id=$POST['type_id'];

			$drawing_number = '';
			$item_code = '';

			// $companyConfiguration=getCompanyConfiguration($dbcon);
			// // $production_pro_type=$companyConfiguration['production_pro_type'];
			// $bom_pro_search=$companyConfiguration['bom_pro_search'];

			$whr=' and p.product_type='.$type_id.'';
			

			$query="select p.product_id,p.product_name,p.product_desc,p.product_icode, dr.drawing_number from product_mst as p left join tbl_drawing as dr on dr.drawing_id = p.drawing_id
			where p.product_status=0 and p.company_id in(0,".$_SESSION['company_id'].") ".$whr." order by p.product_name ASC";
			$result=$dbcon->query($query);
			$i=0;
			while($row=brp_mysqli_fetch_array($result)){
				if(in_array('drawing',$pro_search)){
					$drawing_number = " -- (".$row['drawing_number'].")";
				}
				if(in_array('item',$pro_search)){
					$item_code = " -- (".$row['product_icode'].")";
				}
				$row1[0][]=$row['product_id'];
				$row1[1][]=$row['product_name'].' '.$item_code.' '.$drawing_number;
			}
			echo json_encode($row1);
			// echo getrequiredproduct($dbcon,'',' and p.product_type='.$type_id.'');
		}
		else if(brp_strtolower($POST['mode'])=="load_type")
		{
			$pid=$POST['pid'];
			echo getrequiredproducttype($dbcon,'',' and p.product_id='.$pid.'');
		}
		else if(brp_strtolower($POST['mode'])=="get_sales_order_data")
		{
			$sales_order_id = $POST['sales_order_id'];
			echo get_sales_order_data($dbcon, $sales_order_id);
		}
		else if(brp_strtolower($POST['mode'])== "get_series_no")
		{
			$query="select * from tbl_invoicetype where status=0 and type_id=5 aand company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);
			echo $row['invoicetype_id'];
		}
		/* START :: Code By : Sanat  ::   29-07-2021*/
	  //added bom version series no  
		else if(brp_strtolower($POST['mode']) == "load_bom_version_datatable") {
			else if(brp_strtolower($POST['mode']) == "load_bom_version_datatable") {
    $whr = "";
    if($POST['bom_type'] != ""){
        $whr = " and bom_type = '" . $dbcon->real_escape_string($POST['bom_type']) . "'";
    }

    $query="Select tbv.bom_version_id,bom.bom_id,tbv.version_name,tbv.bom_no,revision_number,tbv.bom_type,tbv.product_id,is_default_bom,tbv.bom_unit_qty,tbv.bom_conv_qty,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from pro_ms_bom_version as tbv left join product_mst as product on product.product_id=tbv.product_id left join tbl_bom as bom on bom.bom_version_id=tbv.bom_version_id left join tbl_revision as rev on rev.revision_id=tbv.revision_id left join unit_mst as bunit on bunit.unitid=tbv.bom_unit left join unit_mst as cunit on cunit.unitid=tbv.bom_conv_unit where tbv.bom_version_status = 0 and tbv.product_id = ".$dbcon->real_escape_string($POST['sel_product_id']) . $whr;

			$result=$dbcon->query($query);

			$str = '<div class="col-md-12">
			<h4><b> Note: Standard BOM Version is selected </b></h4>
			</div>
			<div class="col-md-12">
			<div class="form-group">
			<table cellspacing="10"  style="border-spacing:10px;" class="display table table-bordered table-striped">
			<tr id="field">
			<th class="text-center" width="5%">#</th>
			<th class="text-center" width="15%">BOM Version</th>
			<th class="text-center" width="10%">BOM No</th>
			<th class="text-center" width="10%">Drawing Revision
			</th>

			<th class="text-center hide_act_add" width="10%">BOM Base QTY</th>
			<th class="text-center hide_act_add" width="10%">BOM Base Unit</th>
			<th class="text-center hide_act_add" width="10%">BOM Conv QTY</th>
			<th class="text-center hide_act_add" width="10%">BOM Conv Unit</th>
			';
			if($companyConfiguration['outside_jobwork']){
				$str .= '
			<th class="text-center" width="10%">BOM Type
			</th>';
			}
			$str .= '
			
			<th class="text-center" width="20%">Action</th>

			</tr>
			<tbody id="fil_bom_version_tbl">';
			echo $str;
			if(brp_mysqli_num_rows($result)>0)
			{

				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{
					$str1="";
					$sel6=$dbcon->query("select bom_id from tbl_bom as m where bom_product=". $rel['product_id'] . " AND bom_version_id = " . $rel['bom_version_id']);
					$row6=brp_mysqli_fetch_assoc($sel6);
					$bom_id=$row6['bom_id'];

					$bom_type = "";

					if($rel['bom_type'] == "0"){
						
						$bom_type = "<label class='label label-success'>Normal</label>";
					}else{
						$bom_type = "<label class='label label-danger'>Outside Jobwork</label>";
					}

					$isdefault = "";
					if($rel['is_default_bom'] == 1){
						$isdefault = "defaultbom";
					}

					if(in_array(PRODUCTION_BOM_LIST_SLUG_VIEW,$bulkAccessArray)){
						$sales_order_print='<a class="btn btn-xs btn-info" data-original-title="Print" data-toggle="tooltip'.$bom_id.'" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_print/'.$bom_id.'"><i class="fa fa-print"></i></a>';
					}
					$add_process = '<a class="btn btn-xs btn-primary" data-original-title="Add Process" data-toggle="tooltip" onclick="direct_show_product_process('.$rel['product_id'].','.$rel['bom_version_id'].')" data-placement="top"><i class="fa fa-plus"></i></a>';

					$copy = '<a class="btn btn-xs btn-success" data-original-title="Copy BOM" data-toggle="tooltip" onclick="copy_bom_validation('.$bom_id.')" data-placement="top"><i class="fa fa-copy"></i></a>';
					$bom_extra_no = "";
					if($companyConfiguration['bom_extra_no'] == '1'){
						$bom_extra_no = '<a class="btn btn-xs btn-primary" data-original-title="ADD BOM EXTRA NO" data-toggle="tooltip" href="'.ROOT.PRODUCTION_ROOT.'bom_extra_no_add/'.$bom_id.'" data-placement="top"><i class="fa fa-angellist"></i></a>';
					}
					
					/*$copy = '<button type="button" class="btn btn-primary" onclick="open_copy_bom_model()" id="copy_version" name="copy_version">Copy BOM</button>';*/
					$str1 .=  '<tr class="trversion '.$isdefault.'"  id="fieldtr_'.$rel['bom_version_id'].'" data-version="'.$rel['bom_version_id'].'" onclick="load_version_bom_data('.$rel['bom_version_id'].')" style="cursor :pointer ">

					<td style="vertical-align:top;">
					'.$i.'
					</td>

					<td style="vertical-align:top;">
					'.$rel['version_name'].'
					</td>

					<td style="vertical-align:top;">
					'.$rel['bom_no'].'
					</td>

					<td style="vertical-align:top;">
					'.$rel['revision_number'].'
					</td>

					<td style="vertical-align:top;">
					'.$rel['bom_unit_qty'].'
					</td>
					<td style="vertical-align:top;">
					'.$rel['base_unit_name'].'
					</td>
					<td style="vertical-align:top;">
					'.$rel['bom_conv_qty'].'
					</td>
					<td style="vertical-align:top;">
					'.$rel['conv_unit_name'].'
					</td>';
					if($companyConfiguration['outside_jobwork']){
					$str1 .= '<td style="vertical-align:top;">
					'.$bom_type.'
					</td>';
				}

				$btn_document = '<button type="button" style="margin:5px;" class="btn btn-round btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Document Upload" onclick="open_add_view_documents('.$bom_id.','.$rel['bom_version_id'].');" id="btn_doc'.$i.'"><i class="fa fa-plus"></i> Add Document</button>';

					$str1 .= '<td style="vertical-align:top">
					<button type="button" class="btn btn-round btn-warning btn-xs" data-toggle="tooltip" data-pid="'.$rel['product_id'].'" data-placement="top" title="Edit" onclick="edit_bom_version('.$rel['bom_version_id'].');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>
					
					<button type="button" class="btn btn-round btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Delete" onclick="delete_bom_version('.$rel['bom_version_id'].',\' pro_ms_bom_version\',\'bom_version_id\','.$bom_id.');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>

					'.$sales_order_print . ' '  .$add_process . ' ' . $copy. '  '. $bom_extra_no . '  ' . $btn_document;
					
					$str1 .= '</td></tr>';
					echo $str1;
					$i++;
				}
			}
			else{
				echo '<tr class="no-data"><td colspan="11" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</tbody></table>			 
			</div>
			</div>	';
		}
		else if(brp_strtolower($POST['mode'])== "get_version_series_no")
		{
			$query="SHOW TABLE STATUS LIKE 'pro_ms_bom_version'";
			$result=$dbcon->query($query);
			$row=brp_mysqli_fetch_assoc($result);

			echo $row['Auto_increment'];
		}

	// get revision data from drawing number

		else if(brp_strtolower($POST['mode'])== "get_revision_data")

		{
			$drawing_id=$POST['drawing_id'];

			/*$sql = "SELECT * FROM tbl_drawing WHERE drawing_id='".$drawing_id."' ";
			$q = $dbcon->query($sql);
			$r = $q->fetch_assoc();

			$drawing_number = $r['drawing_number'];*/

			$arr['revision_id'] = getrevision_return($dbcon,$drawing_id,'');

			
			echo json_encode($arr);
		}

		// insert  bom version in database
		else if(brp_strtolower($POST['mode'])== "add_bom_version")
		{
			// echo "<pre>";
			// print_r($POST);die;
			$info['version_name']	 	= $POST['version_name'];
			$info['bom_version_date']	= date('Y-m-d',strtotime($POST['bom_version_date']));

			$info['product_id']			= $POST['product_id'];

			$info['bom_no']				= $POST['bom_version_no'];
			$info['drawing_id']			= $POST['drawing_id'];
			$info['revision_id']		= $POST['revision_id'];
			$info['bom_active_status']	= $POST['bom_active_status'];
			$info['is_default_bom']		= $POST['is_default_bom'];
			$info['bom_unit_qty']		= $POST['bom_unit_qty'];
			$info['bom_unit']		= $POST['base_unit'];
			$info['bom_conv_qty']		= $POST['conv_qty'];
			$info['bom_conv_unit']		= $POST['conv_unit'];


			$info['bom_version_status']	= 0;
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['usertype_id']		= $_SESSION['usertype_id'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['branch_id']			= $_SESSION['branch_id'];

			if(isset($POST['bom_type'])){
				$info['bom_type']	= $POST['bom_type'];
			}
			
		
			if(!empty($POST['bom_version_id'])){
				
				$inserestimateid=update_record('pro_ms_bom_version', $info, "bom_version_id=".$POST['bom_version_id'] , $dbcon);
				$arr['msg']="update";

				if(!empty($POST['bom_id'])){
					$info_bom['product_base_unit']	= $POST['base_unit'];
					$info_bom['bom_qty']			= $POST['bom_qty'];
					$info_bom['product_base_qty']	= $POST['base_qty'];
					$info_bom['product_conv_unit']	= $POST['conv_unit'];
					$info_bom['product_conv_qty']	= $POST['conv_qty'];
					$info_bom['bom_type']	= $POST['bom_type'];
					$info_bom['cdate']				= date("Y-m-d H:i:s");
			

					$inserestimateid=update_record('tbl_bom', $info_bom, "bom_id=".$POST['bom_id'] , $dbcon);
				}
				// $inserestimateid=$POST['bom_version_id'];
			}else{
				$arr['msg']="1";
				$inserestimateid=add_record('pro_ms_bom_version', $info, $dbcon);

				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);

				// if(!isset($POST['is_auto_add']) && !$POST['is_auto_add']){
					$bom_no=get_dynamic_bom_no($dbcon);
					$info_bom['bom_no']			= $bom_no;
					$info_bom['bom_date']		= date('Y-m-d');
					$info_bom['bom_product']	= $POST['product_id'];
					$info_bom['product_base_unit']	= $POST['base_unit'];
					$info_bom['bom_qty']	= $POST['base_qty'];
					$info_bom['product_base_qty']	= $POST['base_qty'];
					$info_bom['product_conv_unit']	= $POST['conv_unit'];
					$info_bom['product_conv_qty']	= $POST['conv_qty'];
					$info_bom['bom_status']		= 0;
					$info_bom['conversation_factor']		= $POST['conversation_factor'];
					$info_bom['cdate']			= date("Y-m-d H:i:s");
					$info_bom['user_id']		= $_SESSION['user_id'];
					$info_bom['usertype_id']	= $_SESSION['usertype_id'];
					$info_bom['company_id']		= $_SESSION['company_id'];
					$info_bom['bom_version_id'] = $inserestimateid;
				// print_r($info_bom);die;
					$inser_bom_id=add_record("tbl_bom",$info_bom, $dbcon);
					update_common_no($dbcon,5);

				// }
				
				$query="select bom_version_id from pro_ms_bom_version where is_default_bom=1 and bom_version_status = 0 and product_id=".$POST['product_id'];
				// echo $query;die;
				$result=$dbcon->query($query);
				$count=brp_mysqli_num_rows($result);

				if($count == 0){
					$arrDef['is_default_bom']= 1;
					$uid = update_record('pro_ms_bom_version', $arrDef, "bom_version_id=".$inserestimateid , $dbcon);
				}

				$arr['bom_version_id']= $inserestimateid;
				if(isset($POST['is_auto_add']) && $POST['is_auto_add']){
					$update['bom_no'] = 'BOM-VER/'.$inserestimateid;

					$uid = update_record('pro_ms_bom_version', $update, "bom_version_id=".$inserestimateid , $dbcon);
				}
			}


			if($inserestimateid){
				if($POST['is_default_bom'] == 1){
					$id = 0;
					if(!empty($POST['bom_version_id'])){
						$id = $POST['bom_version_id'];
					}else{
						$id = $inserestimateid;
					}
					$info_default['is_default_bom'] = 0; 
					update_record('pro_ms_bom_version', $info_default, "bom_version_id!=".$id." AND product_id =".$POST['product_id'] , $dbcon);
				}

				$arr['eid']=$inserestimateid;
			//Insert LOG
				$log_entry=common_log_entry($dbcon,"bom_version_add",1,"pro_ms_bom_version",$inserestimateid);
			}
			else{
				$arr['msg']="0";
			}

			echo json_encode($arr);

		}
		else if(brp_strtolower($POST['mode'])== "check_version_used_in_other")
		{
			$row=array();
			$bom_id = $POST['bom_id'];


			$used_count = 0;

			$query="select count(bom_trn_id) as used_count from tbl_bomtrn as bom where bom.bom_trn_status = 0 and  bom.p_bom_id=".$bom_id;
			$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

			$used_count = $used_count + $rel['used_count'];

			$qry = "select count(sales_ordertrn_id) as used_count FROM `tbl_sales_ordertrn` as so_trn 
					 WHERE sales_ordertrn_status = 0 and so_trn.bom_id = 21";
			$rel1=brp_mysqli_fetch_assoc($dbcon->query($qry));
			$used_count = $used_count + $rel1['used_count'];
			if($used_count=="0"){
				$row['res']="0";
			}else{
				$row['res']="1";
			}

			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "check_default_bom_version")
		{
			$row=array();
			$bom_version_id = $POST['bom_version_id'];


			$query="select is_default_bom  from pro_ms_bom_version where bom_version_status = 0 and bom_version_id=".$bom_version_id;
			$rel=brp_mysqli_fetch_assoc($dbcon->query($query));

			echo $rel['is_default_bom'];
		}
		else if(brp_strtolower($POST['mode'])== "set_bom_default_version")
		{
			$row=array();
			$default_version = $POST['default_version'];
			$product_id = $POST['product_id'];
			
			$info['is_default_bom'] = 0;
			$updateestimateid=update_record('pro_ms_bom_version', $info, "product_id=".$product_id, $dbcon);	

			$d_info['is_default_bom'] = 1;
			$updateestimateid=update_record('pro_ms_bom_version', $d_info, "bom_version_id=".$default_version, $dbcon);	

			if($updateestimateid){
				$row['res']="1";
			
			}else{
				$row['res']="0";
			}

			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode'])== "delete_bom_version_data")
		{
			
			$info_v['bom_version_status'] = 2;	
			$updateid=update_record($_POST['table'], $info_v,$POST['whereid']."=".$POST['eid'] , $dbcon);

			$info['bom_status']	= 2;
			$updateestimateid=update_record('tbl_bom', $info, "bom_id=".$POST['bom_id'], $dbcon);	

			if($updateid){
				$row['res']="1";
			
			}else{
				$row['res']="0";
			}

			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode']) == "load_product_version") {
			$product_id = $POST['product_id'];
			$version_id = "";

			if($POST['type'] == "1"){
				$version_id = $POST['bom_version_id'];
			}

			$return_product_version = get_bom_productversion($dbcon,$product_id,$version_id);

			echo $return_product_version;
		}
		else if(brp_strtolower($POST['mode'])== "get_bom_version_data") {

			$bom_version_id = $POST['bom_version_id'];
			if($bom_version_id == ""){
				$bom_version_id = 0;
			}

			$q = $dbcon->query("SELECT bom.bom_version_id, bom.bom_no, bom.version_name, bom.is_default_bom, bom.bom_active_status, bom.bom_type, DATE_FORMAT(bom.bom_version_date, '%d-%m-%Y') as bom_version_date, bom.bom_unit_qty, bom.drawing_id, bom.revision_id, dr.drawing_number,bom.bom_unit,bom.bom_conv_qty,bom.bom_conv_unit,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM pro_ms_bom_version as bom
				left join tbl_drawing as dr on dr.drawing_id = bom.drawing_id
				left join unit_mst as bunit on bunit.unitid=bom.bom_unit
				left join unit_mst as cunit on cunit.unitid=bom.bom_conv_unit
				WHERE bom_version_status = 0 and bom_version_id =".$bom_version_id);

			$r = $q->fetch_assoc();

			echo json_encode($r);
		}
		else if(brp_strtolower($POST['mode']) == "bom_process_add") {

			$product_id = $POST['product_id'];
			$bom_version_id = $POST['bom_version_id'];
			$bom_id = $POST['bom_id'];

			$q = "select pr_process_id from pro_bom_process where product_id =" . $POST['product_id'] . " AND bom_version_id = " . $POST['bom_version_id'] . " order by priority ASC";

			$res_pro = $dbcon->query($q);
			$arr_process = brp_mysqli_fetch_all($res_pro);



		// $sel_process = $POST['sel_process'];
		// $arr_sel_process = explode(',',$sel_process);


			// $hidden = $_POST['multiple_value']; //get the values from the hidden field
			$hidden = $_POST['sel_process']; //get the values from the hidden field
            $hidden_in_array = explode(",", $hidden); //convert the values into array
            $filter_array = array_filter($hidden_in_array); //remove empty index 
            $arr_sel_process = array_values($filter_array); //reset the array key 

            $unsel_process = $POST['unsel_process'];
            $arr_unsel_process = explode(',',$unsel_process);

            $info['product_id'] = $product_id;
            $info['bom_version_id'] = $bom_version_id;
            $info['bom_id'] = $bom_id;
            $info['cdate']				= date("Y-m-d H:i:s");
            $info['user_id']			= $_SESSION['user_id'];
            $info['company_id']			= $_SESSION['company_id'];
            $info['process_status'] = 0; 

            $x = 1;
            foreach ($arr_sel_process as $process_id) {
            	$info['priority']	= $x;
            	$info['pr_process_id']	= $process_id;

				$desc_qry = "select description from tbl_temp_bom_process_desc where bom_id = " .  $bom_id . " and process_id = " . $process_id;
				$desc_pro = $dbcon->query($desc_qry);
				$desc_row=brp_mysqli_fetch_assoc($desc_pro);

				$info['description']	= $desc_row['description'];
            	
            	if(empty($POST['edit_id']) && empty($arr_process)){

            		$inserestimateid=add_record('pro_bom_process', $info, $dbcon);
            	}else if(array_search($process_id, array_column($arr_process, 'pr_process_id')) === false){

            		$inserestimateid=add_record('pro_bom_process', $info, $dbcon);
            	}else if(array_search($process_id, array_column($arr_process, 'pr_process_id')) !== false){
            		$update_info['priority'] = $x;
            		$update_info['bom_id'] = $bom_id;
            		$update_info['process_status'] = 0;
            		$update_info['description']	= $desc_row['description'];

            		$where = "product_id = " . $product_id ." AND bom_version_id = ". $bom_version_id . " AND pr_process_id=".$process_id;
            		$inserestimateid=update_record('pro_bom_process', $update_info, $where , $dbcon);	
            		if($inserestimateid == 0){
            			$inserestimateid = 1;
            		}

            	}
            	$x++;
            }

            if(!empty($POST['edit_id'])){

            	foreach ($arr_unsel_process as $process_id) {
            		if(array_search($process_id, array_column($arr_process, 'pr_process_id')) !== false){
            			$update_info['process_status'] = 2;
            			$where = "product_id = " . $product_id ." AND bom_version_id = ". $bom_version_id . " AND pr_process_id=".$process_id;
            			$inserestimateid=update_record('pro_bom_process', $update_info, $where, $dbcon);
            			if($inserestimateid == 0){
            				$inserestimateid = 1;
            			}	
            		}

            	}
            }

            if($inserestimateid){
            	if(empty($POST['edit_id'])){
            		$arr['msg']="1";
            	}else{
            		$arr['msg']="update";
            	}
            }else{
            	$arr['msg']="0";
            }

            echo json_encode($arr);
        }
        else if(brp_strtolower($POST['mode']) == "get_product_process_data") {

        	$del_id =	delete_record('tbl_temp_bom_process_desc' ,'1' ,$dbcon);
        	$sel1=$dbcon->query("select bom_id from tbl_bom as m where bom_product=". $POST['product_id'] . " AND bom_version_id = " . $POST['bom_version_id']);
        	$row1=brp_mysqli_fetch_assoc($sel1);
        	$bom_id=$row1['bom_id'];


       $q = "select bom.pr_process_id, bom.description,tp.process_type,pmst.process_name from pro_bom_process as bom 
        		left join tbl_product_process as tp on bom.pr_process_id = tp.pr_process_id 
        		left join process_mst as pmst on pmst.process_id=tp.process_id
        		where tp.status = 0 and bom.process_status = 0 AND bom.product_id =" . $POST['product_id'] . " AND bom.bom_version_id = " . $POST['bom_version_id'] . " order by bom.priority ASC";

        	$res_pro = $dbcon->query($q);
        	$arr_process=brp_mysqli_fetch_all($res_pro);
				// echo "<pre>";
				// print_r($arr_process);

        		foreach($arr_process as $temp){
					$info['bom_id'] =  $bom_id;
					$info['process_id'] = $temp['pr_process_id'];
					$info['description'] = $temp['description'];
					if($temp['description'] !=""){
						$inserestimateid=add_record('tbl_temp_bom_process_desc', $info, $dbcon);
						// var_dump($inserestimateid);
					}
					
				}

			$process_ids = "";
			$selected_process_ids = "";
			
			$selected_process_ids = implode(',', array_column($arr_process, 'pr_process_id'));


        	$multiple_value =implode(',', array_column($arr_process, 'pr_process_id'));

        	$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status = 0 and prod.product_id = ".$POST['product_id'] . " order by process_priority";
			// $rel_pro=brp_mysqli_fetch_assoc($dbcon->query($query_pro));

        	$rel_pro = $dbcon->query($query_pro);
        	$i=1;
        	$str='<div class="row m-bot20">
        			<div class="col-md-6 text-center"> <h4>All Process</h4></div>
        			<div class="col-md-6 text-center"><h4>Selected Process as priority</h4></div>	
        		</div>
        	<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
        	<input type="hidden" name="multiple_value" id="multiple_value" value="'.$multiple_value.'"/>
        	<input type="hidden" name="process_sel_product_id" id="process_sel_product_id" value="'. $POST['product_id'] .'"/>
        	<input type="hidden" name="process_bom_id" id="process_bom_id" value="'. $bom_id .'"/>
        	<input type="hidden" name="selected_desc_id" id="selected_desc_id" value=""/>';
        	// <select class="multi-select" multiple=""  name="process_item[]" id="process_item" >';
        	
        		$str .='<div class="row">
  <div class="col-md-5">
  <label for="chk_leftside_process">
        						<input type="checkbox" onClick="select_all_left_side_process()" id="chk_leftside_process" name="chk_leftside_process"/> Select All Process
        					</label>
    <ul id="process_left">';
   //      	while($product_process=brp_mysqli_fetch_assoc($rel_pro)){

   //      		$selected="";
   //      		if(empty($POST['edit_id'])){
			// 	// $selected = "ms-selected";	
   //      		}
			// // else if(in_array($product_process['pr_process_id'], $arr_process)){
   //      		else if(array_search($product_process['pr_process_id'], array_column($arr_process, 'pr_process_id')) !== false){
   //      			$selected="ms-selected";
   //      		}

   //      		$icon = "";
   //      		if($product_process['process_type'] == '1'){

   //      			$icon = ' [inhouse] ';
   //      		}else{

   //      			$icon = ' [outside]	';
   //      		}



   //      		$str .=  '
   //      		<option class="process_row '.$selected.'" data-cid="'.$i.'" value="'.$product_process['pr_process_id'].'">' . $product_process['process_name'] . $icon .'</option>';

			// 			/*$str .= '<div class="form-check process_row" data-cid="'.$i.'">

			// 			<input type="checkbox"  class="form-check-input"  name="process_item[]" id="process_item'.$product_process['pr_process_id'].'" '.$checked.' value="'.$product_process['pr_process_id'].'"/>
			  
			//   <label class="form-check-label" for="process_item'.$product_process['pr_process_id'].'">
			//     ' . $product_process['process_name'] . '
			//   </label>
			//   </div>';*/
			//   $i++;

			// }

			while($product_process=brp_mysqli_fetch_assoc($rel_pro)){

				if(!in_array($product_process['pr_process_id'],array_column($arr_process, 'pr_process_id'))){
       					$icon = "";
        		if($product_process['process_type'] == '1'){

        			$icon = ' [inhouse] ';
        		}else{

        			$icon = ' [outside]	';
        		}

				// $str .=  '<option class="process_row '.$selected.'" data-cid="'.$i.'" value="'.$product_process['process_id'].'">' . $product_process['process_name'] . $icon .'</option>';

				  $str .= '<li class="process_row" data-cid="'.$i.'"  id="'.$product_process['pr_process_id'].'">'.$product_process['process_name'] . $icon .'</li>';
				  $process_ids = $process_ids + ',' + $product_process['pr_process_id'];
				  $i++;
   					 }
				
				}


				 $str .='</ul>
  </div>
    <div class="col-md-2">
      <div>
        <button id="moveRight" class="bigBtn bigBtn btn btn-primary"> > </button>  
      </div>
       <div>
      <button id="moveLeft"  class="bigBtn bigBtn btn btn-danger"> < </button>
      </div>
      
    </div>
    <div class="col-md-5">
    <label for="chk_rightside_process">
        						<input type="checkbox" onClick="select_all_right_side_process()" id="chk_rightside_process" name="chk_rightside_process"/> Select All Process
        					</label>
<ul id="process_right">';


foreach($arr_process as $pro){
	if($pro['process_type'] == '1'){

        			$icon = ' [inhouse] ';
        		}else{

        			$icon = ' [outside]	';
        		}
	$str .= '<li   class="process_row" data-cid="'.$i.'" id="'.$pro['pr_process_id'].'"> '.$pro['process_name']. $icon .' </li>';
	$i++;
}
  // <li id="114"> <button style="margin-right:10px">+</button> rolling</li>
  // <li id="115"><button style="margin-right:10px">+</button>wiring </li>
  
$str .='</ul>  
  </div>
  <div class="col-md-12">
    <input type="hidden" id="process_ids" class="form-control" placeholder="All Process" value="'.$product_ids.'">
	<input type="hidden" id="selected_process_ids" class="form-control" placeholder="Selected Process" value="'.$selected_process_ids.'">
  </div>
</div>';
			$product_process = brp_mysqli_fetch_assoc($rel_pro);

			if(brp_mysqli_num_rows($rel_pro) > 0){
				if(isset($POST['direct'])){
					$function = 'direct_bom_process_add('.$POST['product_id'].','.$POST['bom_version_id'].',"direct")'; 					
				}else{
					$function = 'bom_process_add()'; 	
				}

				$str.="</select>
				<div class='col-md-12' >

				</div>
				<input type='hidden' id='selected_process_id'>
					<div class='col-md-12' id='row_process_desc' style='display:none;margin-top:15px;'>
						<div class='col-md-12'>	Description	</div>
						<div class='col-md-12' style='padding:0px'>
							<textarea class='form-control' rows='5' id='process_desc'></textarea>
						</div>
						<div class='col-md-12' style='margin-top: 15px;'>
							<center>
								<button type='button' id='btProcessDesc' name='btProcessDesc' onClick='save_process_desc()' class='btn btn-success btn-space'>Save</button>
							</center>
						</div>
					</div>
				<div class='col-md-12' style='margin-top: 15px;'>
				<div class='col-md-4' >
				<center>
				<button type='button' id='process_save' onClick='".$function ."' name='process_save' class='btn btn-success' >Submit</button>
				</center>
				</div>

				</div>
				</form>
				";
			}else{

				$str = '<form class="form-horizontal" role="form" id="bom_process_add" action="javascript:;" method="post" name="bom_process_add">
				<input type="hidden" name="multiple_value" id="multiple_value" value=""/>
				<div class="col-md-12" style="margin-top: 15px;">
				<h3>NO PROCESS ADDED</h3>
				</div>
				<div class="col-md-12" id="row_process_desc" style="display:none;margin-top:15px;">
						<div class="col-md-12">	Description	</div>
						<div class="col-md-12" style="padding:0px">
							<textarea class="form-control" rows="5" id="process_desc"></textarea>
						</div>
						<div class="col-md-12" style="margin-top: 15px;">
							<center>
								<button type="button" id="btProcessDesc" name="btProcessDesc" onClick="save_process_desc()" class="btn btn-success btn-space">Save</button>
							</center>
						</div>
					</div>
				</form>

				';
			}

			echo $str;
}
		// Process Parameter
else if(brp_strtolower($POST['mode']) == "add_process_value") {

			// print_r($POST);die;

	$product_id=$POST['product_id'];
	$process_id = $POST['process_id'];
	$query="select pr_process_id from tbl_product_process where status = 0 and product_id=".$product_id." and process_id=".$process_id;
	$result=$dbcon->query($query);
	$count=brp_mysqli_num_rows($result);
	if($count > 0){
		$arr['msg']="exist";
	}else{
		$info1['process_id']= $process_id;
		$info1['process_rate']= $POST['process_rate'];
		$info1['process_priority']= $POST['process_priority'];
		$info1['process_type']= $POST['process_type'];
		$info1['product_id']= $product_id;
		$info1['process_time']= $POST['process_time'];
		$info1['process_opening']= $POST['process_opening'];
		$info1['resource_id']= $POST['resource_id'];
		$info1['process_loss']= $POST['process_loss'];
		$info1['process_scrap_tolerance_plus']= $POST['process_scrap_tolerance_plus'];
		$info1['process_scrap_tolerance_minus']= $POST['process_scrap_tolerance_minus'];

		$info1['cdate'] = date("Y-m-d");
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']			= $_SESSION['company_id'];

		$table='tbl_product_process';$tableid='pr_process_id';


		$inserid=add_record($table, $info1, $dbcon);

		if($inserid){
			update_product_setting($dbcon,$product_id,'process_product');
	// $setting  = process_product,product_qc
			$log_entry=common_log_entry($dbcon,"product_process_add",1,"tbl_product_process",$inserid);
			$arr['msg']="1";
			$arr['process_id']=$inserid;
		}else{
			$arr['msg']="0";
		}
	}

	echo json_encode($arr);

}
else if(brp_strtolower($POST['mode'])== "check_product_process_required")
{
	$product_type = $POST['product_type'];
	$check_pr_type_process = check_process_product_type($dbcon,$product_type);

	echo $check_pr_type_process;
}
else if(brp_strtolower($POST['mode'])== "check_duplicate_process")
{
	$product_id=$POST['product_id'];
	$process_id = $POST['process_id'];
	$query="select pr_process_id from tbl_product_process where status = 0 and product_id=".$product_id." and process_id=".$process_id;
			// echo $query;
	$result=$dbcon->query($query);
	$count=brp_mysqli_num_rows($result);
	echo $count;
}
else if(brp_strtolower($POST['mode'])== "check_product_process")
{
	$product_id=$POST['product_id'];
	$bom_version_id = $POST['bom_version_id'];
	$query="select pro_bom_process_id from pro_bom_process where process_status = 0 and product_id=".$product_id." and bom_version_id = " . $bom_version_id;
	$result=$dbcon->query($query);
	$count=brp_mysqli_num_rows($result);
	echo $count;
} 

else if(brp_strtolower($POST['mode'])== "get_product_details")
{
	$product_id=$POST['product_id'];
	$query = $dbcon -> query("SELECT mst.product_name,mst.product_base_unit,mst.product_base_qty,mst.product_conv_unit,mst.product_conv_qty,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name
				FROM product_mst as mst 
				left join unit_mst as bunit on bunit.unitid=mst.product_base_unit
				left join unit_mst as cunit on cunit.unitid=mst.product_conv_unit
				WHERE product_id=".$product_id);
	// $query="select * from product_mst where product_id=".$product_id;
	// $result=$dbcon->query($query);
	
	$row=brp_mysqli_fetch_assoc($query);
	
	echo json_encode($row);
}  

/*  END :: Code By : Sanat ::   29-07-2021 */

else if(brp_strtolower($POST['mode'])== "check_duplicate")
{
	/*Sanat :: Added Bom version condition - 04-08-2021 */

	$pro_id=$POST['pro_id'];
	$bom_version_id = $POST['bom_version_id'];
	$query="select bom_id from tbl_bom where bom_status=0 and bom_product=".$pro_id." AND bom_version_id =". $bom_version_id;
		// echo $query;die;
	$result=$dbcon->query($query);
	$count=brp_mysqli_num_rows($result);
	echo $count;
}
else if(brp_strtolower($POST['mode'])== "check_version_name")
{
	/*Sanat :: Added Bom version condition - 04-08-2021 */

	$product_id=$POST['product_id'];
	$version_name = $POST['version_name'];

	$bom_version_id = $POST['bom_version_id'];
	$whr = "";
	if($bom_version_id !=""){
		$whr.= " AND bom_version_id !=" . $bom_version_id;
	}
	$query="select bom_version_id from  pro_ms_bom_version where bom_version_status=0 and version_name='".$version_name	."' AND product_id =". $product_id ." " .$whr;
		// echo $query;die;
	$result=$dbcon->query($query);
	$count=brp_mysqli_num_rows($result);
	echo $count;
}



else if(brp_strtolower($POST['mode'])== "get_bom_id")
{
	$pro_id=$POST['pro_id'];
	$query="select bom_id from tbl_bom where bom_status!=2 and bom_product=".$pro_id." and  bom_version_id = ". $POST['bom_version_id'] ." and company_id=".$_SESSION['company_id'];
		// echo $query;
	$result=$dbcon->query($query);
	$row=brp_mysqli_fetch_assoc($result);
		//$_SESSION['parent_bom']=$row['bom_id'];
		//$count=brp_mysqli_num_rows($result);
	
	echo $row['bom_id'];
		//echo $result['bom_id'];
}
else if(brp_strtolower($POST['mode'])== "get_p_bom_id")
{
	$product_id=$POST['product_id'];
    $bom_version_id=$POST['bom_version_id'];
	$query="select bom_id from tbl_bom where bom_status!=2 and bom_product=".$product_id." and bom_version_id = ". $bom_version_id ." and company_id=".$_SESSION['company_id'];
		// echo $query;
	$result=$dbcon->query($query);
	$row=brp_mysqli_fetch_assoc($result);
		//$_SESSION['parent_bom']=$row['bom_id'];
		//$count=brp_mysqli_num_rows($result);
	
	echo $row['bom_id'];
		//echo $result['bom_id'];
}
else if(brp_strtolower($POST['mode'])== "load_invoiceno")
{
	$row=array();
	$query1="select * from  tbl_invoicetype where invoicetype_id=".$POST['typeid'];
	$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
		//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
		//$end = $start+1;
	if($rows['invoice_format']=='2')
	{
		$row['invoiceno'] = str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1')
	{
		$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno'] = $rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno'] = str_pad($id,3,"0",STR_PAD_LEFT);
	}
	$row['challanno'] = str_pad($id,3,"0",STR_PAD_LEFT);
	echo json_encode($row);
}
else if(brp_strtolower($POST['mode'])== "entry_req_pro")
{
	$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);
	$sales_order_pro_id = $POST['sales_order_pro_id'];
	$entry_req_pro_qry ='INSERT INTO tbl_bomtrn (product_type, product_id, product_qty, user_id)
	SELECT (select product_type from tbl_product where product_id=seltbl.req_product_id) as req_product_type_id, req_product_id, req_product_qty, '.$_SESSION["user_id"].' FROM  tbl_req_product as seltbl where req_product_status=0 and product_id='.$sales_order_pro_id;
	$run_entry_req_pro_qry=$dbcon->query($entry_req_pro_qry);
	echo 1;
}
else if(brp_strtolower($POST['mode'])== "entry_bom_req_po")
{
	$pl_product=$POST['pl_product'];
	$pl_bom_id=$POST['pl_bom_id'];
	$planning_id=$POST['planning_id'];

	$bom_trn_id = implode(",",$POST['bom_trn_id']);
	$infopo['product_ref_id']	= $pl_product;
		$infopo['po_type_status']		= 2;//Type is Request
		$infopo['purchaseorder_no']		= load_po_no($dbcon,6);//Fixed Id for this company
		$infopo['purchaseorder_date']	= date('Y-m-d');
		$infopo['cdate']				= date("Y-m-d H:i:s");
		$infopo['mdate']				= date("Y-m-d H:i:s");
		$infopo['userid']				= $_SESSION['user_id'];
		$infopo['company_id']			= $_SESSION['company_id'];
		$infopo['usertype_id']	= $_SESSION['user_type'];
		//$infopo['branch_id']	= $_SESSION['branch_id'];
		$infopo['po_req_mode']	= 1;
		$infopo['po_ref_id']	= $planning_id;
		$infopo['po_ref_type']	= 'planning';
		$infopo['po_bom_id']	= $pl_bom_id;
		$inserpoid=add_record('tbl_purchaseorder', $infopo, $dbcon);
		
		$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id =17");//Fixed Id for this company
		
		//Change status  if po requested
	//	$upd_trn_qry = $dbcon->query("update tbl_bomtrn set po_req_status=1 where bom_trn_id in(".$bom_trn_id.") ");

		$sel_bom_trn = "select * from tbl_bomtrn where bom_trn_id in(".$bom_trn_id.")";
		$sel_bom_trn_rs = $dbcon->query($sel_bom_trn);
		while($bom_trn_rel=brp_mysqli_fetch_assoc($sel_bom_trn_rs)){
			
			
			$infpotrn['purchaseorder_id']	= $inserpoid;
			$infpotrn['product_type']		= $bom_trn_rel['product_type'];
			$infpotrn['product_id']			= $bom_trn_rel['product_id'];
			$infpotrn['product_qty']		= $bom_trn_rel['product_qty'];
			$infpotrn['product_rate']		= get_pro_field($dbcon,$bom_trn_rel['product_id'],'product_purchase_rate');
			$infpotrn['product_hsn_code']   = get_pro_field($dbcon,$bom_trn_rel['product_id'],'product_hsn');
			$infpotrn['unit_id']			= get_pro_field($dbcon,$bom_trn_rel['product_id'],'product_base_unit');
			$infpotrn['product_amount']		= $total=($infpotrn['product_rate']*$bom_trn_rel['product_qty']);
			$infpotrn['parent_pro']			= 0;
			$infpotrn['main_pro_status']	= 1;//Requested products
			$infpotrn['user_id']			= $_SESSION['user_id'];
			$infpotrn['po_ref_id']			= $planning_id;
			$infpotrn['po_ref_type']			= 'planning';
			$infpotrn['po_bom_id']			= $pl_bom_id;
			$infpotrn['po_bom_trn_id']			= $bom_trn_rel['bom_trn_id'];

			$inserpotrnid=add_record('tbl_purchasetrntemp', $infpotrn, $dbcon);
			
		}
	}
	else if(brp_strtolower($POST['mode'])== "get_bom_product_data")
	{
		$pid=$POST['product_id'];
		$pqty=$POST['product_qty'];
		//$mode_edit=$POST['mode_edit'];
		$mode_edit_id=$POST['mode_edit_id'];
		
		if(brp_strtolower($POST['mode_edit'])== "edit")
		{
			$deleteid=delete_record('tbl_bomtrn', "bom_id=$mode_edit_id", $dbcon);
			
			$entry_req_pro_qry ='INSERT INTO tbl_bomtrn (product_type, product_id, product_actual_qty,product_qty,bom_id,user_id)
			SELECT (select product_type from tbl_product where product_id=seltbl.req_product_id) as req_product_type_id, req_product_id, req_product_qty , req_product_qty * '.$pqty.' as tot_pqty,'.$mode_edit_id.','.$_SESSION["user_id"].' FROM  tbl_req_product as seltbl where req_product_status=0 and product_id='.$pid;
			$run_entry_req_pro_qry=$dbcon->query($entry_req_pro_qry);
			
			echo "1";
		}
		else
		{
			$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);
			
			$entry_req_pro_qry ='INSERT INTO tbl_bomtrn (product_type, product_id, product_actual_qty,product_qty,user_id,bom_actual_add_status,company_id,branch_id)
			SELECT (select product_type from tbl_product where product_id=seltbl.req_product_id) as req_product_type_id, req_product_id, req_product_qty , req_product_qty * '.$pqty.' as tot_pqty,'.$_SESSION["user_id"].',1,'.$_SESSION['company_id'].','.$_SESSION['branch_id'].' FROM  tbl_req_product as seltbl where req_product_status=0 and product_id='.$pid;
			$run_entry_req_pro_qry=$dbcon->query($entry_req_pro_qry);
			
			echo "1";
		}
	}
	else if(brp_strtolower($POST['mode'])== "change_actual_status")
	{
		$id=$POST['eid'];
		$bom_status=$POST['bom_status'];
		
		if($bom_status=='1')
		{
			$query="update tbl_bomtrn set bom_actual_add_status=0 where bom_trn_id=$id";
			$r=$dbcon->query($query);
		}
		else
		{
			$query="update tbl_bomtrn set bom_actual_add_status=1 where bom_trn_id=$id";
			$r=$dbcon->query($query);
		}
		if($r)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(brp_strtolower($POST['mode'])== "load_qty")
	{
		$branch_id = intval($POST['branch_id']);
		//echo get_branchwise_productqty($dbcon,$POST['product_id'],$branch_id);
	}
	else if(brp_strtolower($POST['mode'])== "get_bom_id_by_product")
	{
		$pid = intval($POST['pid']);
		$planning_id = $POST['planning_id'];
		
		$q=$dbcon->query("select bom_id from tbl_bom where bom_product='$pid' and bom_status='0'");
		$row=brp_mysqli_fetch_assoc($q);
		
		$r=$dbcon->query("select product_qty from tbl_planning_ordertrn where pl_order_id='$planning_id'");
		$row1=brp_mysqli_fetch_assoc($r);
		
		$data['bom']=$row['bom_id'];
		$data['qty']=$row1['product_qty'];
		
		echo json_encode($data);
		//echo get_branchwise_productqty($dbcon,$POST['product_id'],$branch_id);
	}
	else if(brp_strtolower($POST['mode'])== "change_bom_status")
	{
		$id=$POST['bom_id'];
		$bom_status=$POST['bom_status'];
		
		if($bom_status=='1')
		{
			$query="update tbl_bom set bom_close_status=0 where bom_id=$id";
			$r=$dbcon->query($query);
		}
		else
		{
			$query="update tbl_bom set bom_close_status=1 where bom_id=$id";
			$r=$dbcon->query($query);
		}
		if($r)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	else if(brp_strtolower($POST['mode'])== "add_grp")
	{
		$grp_name = $POST['grp_name'];
		$grp_model = $POST['grp_model'];
		
		$info['bg_name']=$grp_name;
		
		$inserid=add_record("tbl_bom_group", $info, $dbcon);
		
		if($inserid){
			if(brp_strtolower($POST['grp_model'])=="grp_model"){
				$query="select * from tbl_bom_group where bg_id=".$inserid;
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));		
				$row = $rel;
				$row['res']="2"; 
			}
			else{
				$row['res'] ="1";
			}
		}
		else{
			$row['res'] ="0";
		}
		
		echo json_encode($row);	
	}
	else if(brp_strtolower($POST['mode'])== "add_product_process") {
		$tr = $dbcon -> query("SELECT `p_process_id` FROM `tbl_bom_product_process` WHERE `p_bom_id` = '$POST[bom_id]' and `p_product_id` = '$POST[product_id]' and `p_process_id` = '$POST[process_id]' and p_status=0 and company_id=".$_SESSION['company_id'] );
		if($tr->num_rows > 0 && !$POST['edit_id']) {
			$row['res']='-1';
		}
		else{
			$info1['p_product_id']				= $POST['product_id'];
			$info1['p_process_id']				= $POST['process_id'];
			$info1['pr_make_time']				= $POST['pr_make_time'];
			$info1['p_bom_id']					= $POST['bom_id'];

			$info1['cdate']					= date("Y-m-d H:i:s");
			$info1['user_id']				= $_SESSION['user_id'];
			$info1['company_id']			= $_SESSION['company_id'];
			$info1['branch_id']			= $_SESSION['branch_id'];
			$table='tbl_bom_product_process';$tableid='bp_id';
			
			if(empty($POST['edit_id'])) {
				$inserid=add_record($table, $info1, $dbcon);
				
			}
			else {
				//$row['res']='2';
				$updateid=update_record($table, $info1, $tableid."=".$POST['edit_id'], $dbcon);	
			}
			$row['res']='1';
			
		}
		echo json_encode($row);
	}
	else if(brp_strtolower($POST['mode']) == "show_product_process") {
		
		$product_id=$POST['product_id'];
		$bom_id=$POST['bom_id'];
		$appData = array();
		$i=1;
		$aColumns = array('bp_id','p_process_id','pr_make_time','p_status','p.process_id','p.process_name','pt.process_type_name');
		$sIndexColumn = "bp_id"; 
		$isWhere = array("p_status=0 and p_product_id='$product_id' and p_bom_id='$bom_id'");
		$sTable = "tbl_bom_product_process as b";
		$isJOIN = array("inner join process_mst as p on p.process_id=b.p_process_id","inner join process_type_mst as pt on pt.process_type_id=p.process_type");
		$hOrder = "bp_id desc";
		include($path.'include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			
			$row_data[] = $row['process_type_name'];
			$row_data[] = $row['process_name'];
			$row_data[] = $row['pr_make_time'];
			
			$row_data[] = '<button type="button" class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_product_pro('.$row['bp_id'].');"><i class="fa fa-pencil"></i></button>
			<button type="button" class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_product_pro('.$row['bp_id'].')"><i class="fa fa-trash-o"></i></button>';

			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(brp_strtolower($POST['mode']) == "edit_product_pro") {	
		$q = $dbcon -> query("SELECT * FROM `tbl_bom_product_process` WHERE p_status=0 and `bp_id` = '$POST[bp_id]'");
		$r = $q->fetch_assoc(); 
		$process_type_id = get_process_type_by_id($dbcon,$r['p_process_id']);
		$r['process_type_id']=$process_type_id;
		echo json_encode($r);
	}
	else if(brp_strtolower($POST['mode']) == "delete_product_pro") {
		$info['p_status']='2';
		$updateid=update_record('tbl_bom_product_process', $info, "bp_id=".$POST['bp_id'], $dbcon);
		
		if($updateid)
			echo "1";
		else
			echo "0";
	}
	else if(brp_strtolower($POST['mode']) == "get_process") {
		
		$pid=$POST['p_id'];
		
		echo get_all_process_by_type($dbcon,$pid,'');
	}
	else if(brp_strtolower($POST['mode']) == "get_bom_by_product") {
		
		$bid=get_bom_id_by_product($dbcon,$POST['pid']);
		$planning_id=$POST['planning_id'];
		
		$qry="select * FROM `tbl_bomtrn` as trn 
		left join product_mst as product on product.product_id=trn.product_id 
		left join unit_mst as per on per.unitid=product.product_base_unit
		where bom_trn_status=0 and bom_id='$bid' and trn.parent_id='0'";
		$result=$dbcon->query($qry);		
		$i=1;$total=0;$discount=0;
		$cnt1=brp_mysqli_num_rows($result);
		$cnt=1;
		while($row=brp_mysqli_fetch_assoc($result))
		{
			$number="1.".$cnt;
			echo '
			<tr>';

			get_tree_bom_po($dbcon,$row['product_id'],$row['parent_id'],0,$cnt,$bid,$number,$row['product_qty'],$row['bom_trn_id'],$planning_id);

			echo '</tr>';

			$cnt++;$i++; 
			
		}
		//echo $bid;


	}
	else if(brp_strtolower($POST['mode']) == "update_bom_visibility") {
		
		$pid=$POST['pid'];
		$v_status=$POST['v_status'];
		
		if($v_status=='1')
		{
			$info['po_visible_status']='0';
		}
		else
		{
			$info['po_visible_status']='1';
		}
		
		$updateid=update_record('tbl_bomtrn', $info, "bom_trn_id=".$pid, $dbcon);
	}
	else if(brp_strtolower($POST['mode']) == "load_productdata") {
		
		$pid=$POST['eid'];
		
		$sel=$dbcon->query("select m.*,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from product_mst as m 
			left join unit_mst as bunit on bunit.unitid=m.product_base_unit
			left join unit_mst as cunit on cunit.unitid=m.product_conv_unit

		left join mst_material_spec as s on m.product_specification=s.ms_id where product_id='$pid'"); // s.m_type_density,
		$row=brp_mysqli_fetch_assoc($sel);
		
		$sel1=$dbcon->query("select bom_id from tbl_bom as m where bom_product='$pid'");
		$row1=brp_mysqli_fetch_assoc($sel1);
		$row['bom_id']=$row1['bom_id'];

		/*
			Code By Umair: 31-05-2021
			Comment : Below Code is use for product specification dynamically
			START
		*/
			$html = '';	
			if($row['product_specification']!='' && $row['product_specification']!='0'){
				$param_sql = "select * from tbl_material_parameter where material_parameter_status = 0 and company_id='".$_SESSION['company_id']."' ";
				$rs_parameter=$dbcon->query($param_sql);	
				while($rel_param=brp_mysqli_fetch_assoc($rs_parameter)){
					$parameter_name = ucfirst(brp_strtolower($rel_param['material_parameter_name']));	
					$parameter_id = 'product_'.$rel_param['material_parameter_id'];	

					$material_parameter_id = $rel_param['material_parameter_id'];

					$param_trn_sql = "select * from mst_material_spec_trn where material_parameter_id = '".$material_parameter_id."' and ms_id='".$row['product_specification']."' ";
					$rs_exec=$dbcon->query($param_trn_sql);	
					$rel_data=brp_mysqli_fetch_assoc($rs_exec);
					if($rel_data['material_parameter_value']){
						$html .= $parameter_name. ' : <input type="text" class="form-control get_ms_kg" name="'.$parameter_id.'" id="'.$parameter_id.'" value="'.$rel_data['material_parameter_value'].'" data-parameter="'.$material_parameter_id.'" data-msid="'.$row['product_specification'].'"  onkeyup="get_ms_kg();" />';
					}
				}
				if($html!=''){
					$html .= '<input type="hidden" name="msid" id="msid" value="'.$row['product_specification'].'">';
					$html .= '<input type="text" class="form-control" name="product_kg" id="product_kg" value="" readonly /> 
					<input type="checkbox" name="set_kg" id="set_kg" value="0" onclick="set_kg_to_qty(this.value)" />SET'; 
				}
			}
			$row['product_specification_code']=$html;
			/* END */

			echo json_encode($row);
		}
		else if(brp_strtolower($POST['mode']) == "clone_bom_trn_data") {
			$bom_id=$POST['bom_id'];
			$deleteid=delete_record('tbl_bomtrn', "bom_id=0", $dbcon);

		/*$copy_bomtrn_qry="INSERT INTO tbl_bomtrn (product_type,product_id,sale_product_id,parent_id,product_grp,product_qty,product_base_qty,product_piece_qty,product_base_unit,product_uom,product_act_qty,po_visible_status,bom_actual_add_status,bom_level,company_id,branch_id,product_width,product_height,product_thickness,product_kg,product_density,bom_id,bom_trn_status,user_id)
		select product_type,product_id,sale_product_id,parent_id,product_grp,product_qty,product_base_qty,product_piece_qty,product_base_unit,product_uom,product_act_qty,po_visible_status,bom_actual_add_status,bom_level,company_id,branch_id,product_width,product_height,product_thickness,product_kg,product_density,0,0,".$_SESSION['user_id']." from tbl_bomtrn where bom_id=".$bom_id." and bom_trn_status=0";
		$copy_bomtrn_qry_rs=$dbcon->query($copy_bomtrn_qry);*/
		$get_bom_trn_qry="select * from tbl_bomtrn where bom_trn_status=0 and parent_id=0 and bom_id=".$bom_id;
		$get_bom_trn_qry_rs=$dbcon->query($get_bom_trn_qry);
		while($trn_rel=brp_mysqli_fetch_assoc($get_bom_trn_qry_rs)){
			//DB,trn_id,parent_id
			$clone_bom_trn_data=clone_bom_trn_data_func($dbcon,$trn_rel['bom_trn_id'],0);
		}
	}
	else if(brp_strtolower($POST['mode']) == "load_product_types") {
		$product_type = $POST['product_type'];
		$return_product_type = get_bom_producttype($dbcon,$product_type);

		echo $return_product_type;
	}else if(brp_strtolower($POST['mode']) == "open_copy_bom_model") {

		$query_pro="select bom.*,pmst.product_name,bunit.unit_name as baseunit,cunit.unit_name as convunit from tbl_bom as bom 
		left join product_mst as pmst on pmst.product_id=bom.bom_product
		left join unit_mst as bunit on bunit.unitid=bom.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom.product_conv_unit
		where bom.bom_id=".$POST['bom_id'];
		$rel_pro=brp_mysqli_fetch_assoc($dbcon->query($query_pro));
		$bom_date=date('d-m-Y',strtotime($rel_pro['bom_date']));
		$str="";
		$str.="
		<!--<div class='col-md-12' >-->
		<div class='col-md-6' >
		<div class='col-md-4' style='white-space: nowrap;font-weight: 600;font-size: 17px;'>Bom No </div>
		<div class='col-md-8' style='white-space: nowrap;font-size: 15px;'>: ".$rel_pro['bom_no']."</div>
		</div>
		<div class='col-md-6' >
		<div class='col-md-4' style='white-space: nowrap;font-weight: 600;font-size: 17px;'>Bom Date</div>
		<div class='col-md-8' style='white-space: nowrap;font-size: 15px;'>: ".$bom_date."</div>
		</div>
		<div class='col-md-6' >
		<div class='col-md-4' style='white-space: nowrap;font-weight: 600;font-size: 17px;'>Bom Product</div>
		<div class='col-md-8' style='white-space: nowrap;font-size: 15px;'>: ".$rel_pro['product_name']."</div>
		</div>
		<!--</div>
		<div class='col-md-12' >-->
		<div class='col-md-6' >
		<div class='col-md-4' style='white-space: nowrap;font-weight: 600;font-size: 17px;'>Base Quantity</div>
		<div class='col-md-8' style='white-space: nowrap;font-size: 15px;'>: ".$rel_pro['product_base_qty']." ".$rel_pro['baseunit']."</div>
		</div>
		<div class='col-md-6' >
		<div class='col-md-4' style='white-space: nowrap;font-weight: 600;font-size: 17px;'>Conv Quantity</div>
		<div class='col-md-8' style='white-space: nowrap;font-size: 15px;'>: ".$rel_pro['product_conv_qty']." ".$rel_pro['convunit']."</div>
		</div>
		<div class='col-md-6' >
		<div class='col-md-4' style='white-space: nowrap;font-weight: 600;font-size: 17px;'>Remark</div>
		<div class='col-md-8'>: ".$rel_pro['remark']."</div>
		</div>
		<!--</div>-->
		<div class='col-md-12' style='margin-top: 15px;'>
		<div class='col-md-6' >
		<div class='col-md-4' style='font-weight: 600;font-size: 17px;'>Copy Bom Product</div>
		<div class='col-md-8'>
		<input name='copy_sel_product_id' id='copy_sel_product_id' onchange='load_product_version(this.value,1);' style='width:100%;' placeholder='Select product' class=' pro_version_id'/>
		<!-- <select class='select2 mprdct pro_version_id' title='Select product' name='copy_sel_product_id' id='copy_sel_product_id' onchange='load_product_version(this.value,1);'> -->";
		//$str.= get_bom_product($dbcon,"",'0');
		$str.="<!-- </select> -->
		<strong id='copy_bom_duplicate' style='color:red;display:none'>BOM For This Product Already Exist</strong>
		</div>
		</div>
		<div class='col-md-6' >
		<div class='col-md-4' style='font-weight: 600;font-size: 17px;'>Copy Bom Product</div>
		<div class='col-md-8'>
		<select class='select2 mprdct' title='Select product' name='copy_sel_product_version' id='copy_sel_product_version'>
		<option>Select Version</option>
		</select>
		</div>
		</div>

		</div>
		<div class='col-md-12' style='margin-top: 15px;'>
		<center>
		<input type='button' id='copy_save' name='copy_save' class='btn btn-success' value='Copy' onclick='copy_bom();' />
		</center>
		</div>
		<input type='hidden' name='sbom_id' id='sbom_id' value='".$POST['bom_id']."' >

		";
		echo $str;
	}else if(brp_strtolower($POST['mode']) == "copy_bom") {

				$sel_product_id=$POST["sel_product_id"];  // bom product to copy
				$bom_id=$POST["bom_id"];

				$sbom_id=$POST["sbom_id"];


				$bom_version_id=$POST["bom_version_id"]; // bom version to copy
				$product_id=$POST["product_id"]; // product 
				$bom_version =$POST["bom_version"]; // copy in this verstion  
			// $query11="select * from tbl_bom as trn where bom_id=".$POST['sbom_id'];
				$query11="select * from tbl_bom as trn where bom_product=".$sel_product_id." and bom_version_id =".$bom_version_id;
				$result=$dbcon->query($query11);
				$rel1=brp_mysqli_fetch_assoc($result);
				
				//$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE invoicetype_id = ".$POST['invoicetype_id']);
				/*$bom_no=get_dynamic_bom_no($dbcon);

				get_dynamic_bom_no_series_update($dbcon);
				$sbom_id= $rel1['bom_id'];
				$info2['bom_no']					= $bom_no;
				$info2['bom_group']					= $rel1['bom_group'];
				$info2['bom_date']					= date('Y-m-d');
				$info2['bom_product']				= $product_id;
				$info2['bom_qty']					= $rel1['bom_qty'];
				$info2['product_base_unit']			= $rel1['product_base_unit'];
				$info2['product_base_qty']			= $rel1['product_base_qty'];
				$info2['product_conv_unit']			= $rel1['product_conv_unit'];
				$info2['product_conv_qty']			= $rel1['product_conv_qty'];
				$info2['remark']					= $rel1['remark'];

				$info2['bom_actual_add_status']		= $rel1['bom_actual_add_status'];
				//$info2['bom_close_status']			= $rel1['bom_close_status'];
				$info2['bom_close_status']			= 0;
				$info2['tot_standrad_qty']			= $rel1['tot_standrad_qty'];
				$info2['branch_id']					= $rel1['branch_id'];
				$info2['cdate']					= date("Y-m-d H:i:s");
				$info2['user_id']				= $_SESSION['user_id'];
				$info2['usertype_id']			= $_SESSION['usertype_id'];
				$info2['company_id']			= $_SESSION['company_id'];*/

				/*Sanat :: added bom version - 04-08-21 */

				/*$info2['bom_version_id']=$bom_version;
				
				$bom_id=add_record('tbl_bom',$info2,$dbcon);*/


				$qry = "select * from pro_bom_process where bom_version_id = " . $bom_version_id . " AND product_id = " .$sel_product_id;
				
				$res=$dbcon->query($qry);

				$q = "select pr_process_id from pro_bom_process where  product_id =" .$product_id . " AND bom_version_id = " . $bom_version . " order by priority ASC";


				// echo $q;die;
				$res_pro = $dbcon->query($q);
				$arr_process = brp_mysqli_fetch_all($res_pro);
				$priority = count($arr_process);

				// echo $qry;die;
				while($rw=brp_mysqli_fetch_assoc($res)){

					$process['product_id'] = $product_id;
					$process['bom_version_id'] = $bom_version;
					$process['pr_process_id'] = $rw['pr_process_id'];
					$process['process_status'] = $rw['process_status'];
					$process['priority'] = $rw['priority'];
					$process['bom_id'] = $bom_id;
					$process['cdate'] = $rw['cdate'];
					$process['user_id'] = $rw['user_id'];
					$process['company_id'] = $rw['company_id'];

					$itm_prod_qry = "SELECT pr_process_id FROM tbl_product_process WHERE status = 0 and product_id = " . $product_id . " AND pr_process_id = " . $rw['pr_process_id'];

					$itm_prod_result = $dbcon->query($itm_prod_qry);
					$prod_cnt = brp_mysqli_num_rows($itm_prod_result);
					if($prod_cnt == '0'){

						$q_1 = "SELECT * FROM tbl_product_process WHERE status = 0 and pr_process_id = " .  $rw['pr_process_id'];
						$q_rw =  brp_mysqli_fetch_assoc($dbcon->query($q_1));


						$q_2 = "SELECT IFNULL(max(process_priority),0) as priory FROM `tbl_product_process` WHERE status = 0 and `product_id` =  " .  $product_id;
						$q_rw1 =  brp_mysqli_fetch_assoc($dbcon->query($q_2));

						$priority = $q_rw1['priory'] + 1;

						$info1['process_id']= $q_rw['process_id'];
						$info1['process_rate']= $q_rw['process_rate'];
						$info1['process_priority']= $priority;
						$info1['process_type']= $q_rw['process_type'];
						$info1['product_id']= $product_id;
						$info1['process_time']= $q_rw['process_time'];
						$info1['process_opening']= $q_rw['process_opening'];
						$info1['resource_id']= $q_rw['resource_id'];
						$info1['process_loss']= $q_rw['process_loss'];
						$info1['process_scrap_tolerance_plus']= $q_rw['process_scrap_tolerance_plus'];
						$info1['process_scrap_tolerance_minus']= $q_rw['process_scrap_tolerance_minus'];

						$info1['cdate'] = date("Y-m-d");
						$info1['user_id']			= $_SESSION['user_id'];
						$info1['company_id']			= $_SESSION['company_id'];
						
						$inserid=add_record('tbl_product_process', $info1, $dbcon);

						$process['pr_process_id'] = $inserid;
					}


					if(count($arr_process)  > 0){
						
						if(array_search($rw['pr_process_id'], array_column($arr_process, 'pr_process_id')) === false){
							$priority++;
							$process['priority'] = $priority;
							$proc_id=add_record('pro_bom_process', $process, $dbcon);
						}else if(array_search($rw['pr_process_id'], array_column($arr_process, 'pr_process_id')) !== false){
							// $update_info['priority'] = $rw['priority'];
							$update_info['process_status'] = 0;
							$where = "product_id = " . $product_id ." AND bom_version_id = ". $bom_version . " AND pr_process_id=".$rw['pr_process_id'];
							$inserestimateid=update_record('pro_bom_process', $update_info, $where , $dbcon);

						}
					}else{
						$proc_id=add_record('pro_bom_process',$process,$dbcon);
					}
				}
				
				$query="select * from tbl_bomtrn as trn where trn.bom_trn_status=0 and trn.p_bom_version_id=".$bom_version_id;
				$result1=$dbcon->query($query);

				while($row=brp_mysqli_fetch_assoc($result1)){

					$info_trn['bom_id']						= $bom_id;
					$info_trn['product_id']					= $row['product_id'];
					$info_trn['p_bom_id']					= $row['p_bom_id'];
					$info_trn['product_base_qty']			= $row['product_base_qty'];
					$info_trn['product_base_unit']			= $row['product_base_unit'];
					$info_trn['product_conv_unit']			= $row['product_conv_unit'];
					$info_trn['product_conv_qty']			= $row['product_conv_qty'];
					$info_trn['bom_trn_status']				= $row['bom_trn_status'];
					$info_trn['po_req_status']				= $row['po_req_status'];
					$info_trn['po_visible_status']			= $row['po_visible_status'];
					$info_trn['user_id']					= $_SESSION['user_id'];
					$info_trn['company_id']					= $_SESSION['company_id'];
					$info_trn['branch_id']					= $row['branch_id'];
					$info_trn['product_width']				= $row['product_width'];
					$info_trn['product_height']				= $row['product_height'];
					$info_trn['product_thickness']			= $row['product_thickness'];
					$info_trn['product_kg']					= $row['product_kg'];
					$info_trn['product_density']			= $row['product_density'];

					/*Sanat :: added bom version - 04-08-21 */

					$info_trn['bom_version_id']					= $row['bom_version_id'];
					$info_trn['p_bom_version_id']				= $bom_version;

					$bom_trn_id=add_record('tbl_bomtrn',$info_trn,$dbcon);

				}

				if($bom_id){
					$arr['msg']="1";
				}else{
					$arr['msg']="0";
				}

				echo json_encode($arr);

			}else if(brp_strtolower($POST['mode']) == "get_product_specification_cal") {
				$query="select * from mst_material_spec as trn where trn.ms_id=".$POST['msid'];
				$result1=$dbcon->query($query);
				$row=brp_mysqli_fetch_assoc($result1);

				$formula = $row['formula']; 
				$parameter_value = $POST['values'];

				$material_calculation = 0;
				$material_parameter_value = 0;
				foreach ($parameter_value as $key => $val){
					$material_parameter_id = str_replace('product_', '', brp_strtolower($val['name']));
					$material_parameter_value = floatval($val['value']);

					$p_query="select * from tbl_material_parameter as mp where mp.material_parameter_id=".$material_parameter_id;
					$p_result1=$dbcon->query($p_query);
					$p_row=brp_mysqli_fetch_assoc($p_result1);

					$material_parameter_code = $p_row['material_parameter_code'];

					$formula = str_replace($material_parameter_code, $material_parameter_value, $formula);
				}

				echo $material_calculation = do_maths($formula);


			}
			else if(brp_strtolower($POST['mode']) == "add_param_value") {

				$branch_id = $_SESSION['branch_id'];
			//$tolerance_plus = ($POST['tolerance_plus']) ? $POST['tolerance_plus']:'';	
			//$tolerance_minus = ($POST['tolerance_minus']) ? $POST['tolerance_minus']:'';	
				$info1['process_id'] = $POST['qc_process_id'];
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

				$inserid=add_record($table, $info1, $dbcon, $branch_id);

				update_product_setting($dbcon,$POST['pid'],'product_qc');
				
				echo "1";
			}else if(brp_strtolower($POST['mode']) == "check_copy_bom_product") {

				$query="select count(bom_trn_id) as used_count from tbl_bomtrn as bom where bom.bom_id=".$POST['bom_id'];
				$rel=brp_mysqli_fetch_assoc($dbcon->query($query));
				echo $rel['used_count'];


			} else if (brp_strtolower($POST['mode']) == "get_bom_version_list") {
				$bom_id = $POST['bom_id'];
				$product_id = $POST['product_id'];
				$bom_version_id = $POST['bom_version_id'];
				$query = "select bom.*,p.product_name from pro_ms_bom_version as bom 
							left join product_mst as p on p.product_id = bom.product_id
							where bom.product_id=" . $POST['product_id'] . " and  bom_version_status = 0 and  bom.bom_version_id != " . $POST['bom_version_id'];
				$result = $dbcon->query($query);
				$html = "";
				$radio = "";
				$product_name = get_product_name($dbcon, $POST['product_id']);
				$i = 1;
				$cnt = brp_mysqli_num_rows($result);
				while ($rel = brp_mysqli_fetch_assoc($result)) {
					// $product_name = $rel['product_name'];
					$radio .= '<div class="radio">
											  <label><input type="radio" name="opt_bom_version" value="' . $rel['bom_version_id'] . '">' . $rel['version_name'] . '</label>
											</div>
									';
					$i++;
				}

				$html .= '<div class="row" style="margin-top:25px; margin-bottom:25px;">
										<div class="col-md-12">
											<label class="col-md-4">Product Name : </label>
											<div class="col-md-6">
												<input type="text" readonly class="form-control" value="' . $product_name . '"/>
											</div>
										</div>
									</div>
									';
				if ($cnt > 0) {
					$html .= '<div class="row" style="margin:25px;">
										<div class="col-md-12">
										<lable style="color:red; font-size:22px;">SELECT DEFAULT VERSION </label>
										</div>
										<div class="col-md-6">
											' . $radio . '
										</div>
									</div>';

					$html .= '<div class="col-md-12" >
								<center>
									<input type="button" id="sp_btn" name="submit" class="btn btn-success" 
									onclick="set_default_bom(' . $POST['bom_version_id'] . ',\' pro_ms_bom_version\',\'bom_version_id\',' . $POST['bom_id'] . ');" value="Set Default Version" />
								</center>
							</div>';
				}else{
					$html .= '<div class="alert alert-info" style="text-align: center;font-size: 18px;">
							<strong><i class="fa fa-info-circle"></i> This is default bom version. are you sure you want to delete default version ??</strong> 
			  				</div>';
					$html .= '<div class="row" style="margin:25px;">
								<div class="col-md-12" style="margin-top:25px;">
									<center>
										<button type="button" class="btn btn-primary" onclick="delete_force_bom_default_version('.$bom_id.','.$bom_version_id.','.$product_id.')">Delete</button>
										<button type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
									</center>
								</div>
							</div>';
				}
				echo $html;
			}

			else if(brp_strtolower($POST['mode']) == "show_allocate_bom_list") {

				$qry="select bom.bom_no,bv.version_name,p.product_name from tbl_bom as bom 
					left join pro_ms_bom_version as bv on bv.bom_version_id = bom.bom_version_id
					left join product_mst as p on p.product_id=bom.bom_product
					where bom.bom_id=".$POST['bom_id'];

				$resp = $dbcon->query($qry);
				$res = brp_mysqli_fetch_assoc($resp);

				$html = "";

				$html .= '<div class="row" style="margin-top:25px">
								<div class="col-md-6">
									<label class="col-md-4">Product Name : </label>
									<div class="col-md-8">
										<input type="text" class="form-control" readonly value="'.$res['product_name'].'" />
									</div>
								</div>	
								<div class="col-md-6">
									<label class="col-md-3">BOM No. : </label>
									<div class="col-md-9">
										<input type="text" readonly class="form-control" value="'.$res['bom_no'].'" />
									</div>
								</div>
								</div>
								<div class="row" style="margin-top:25px;margin-bottom:25px">	
								<div class="col-md-6">
									<label class="col-md-4">BOM Version : </label>
									<div class="col-md-8">
										<input type="text" readonly class="form-control" value="'.$res['version_name'].'" />
									</div>
								</div>	
						  </div>';

				$query="select bom.bom_no,bv.version_name,p.product_name from tbl_bomtrn as bom_trn left join tbl_bom as bom on bom.bom_id = bom_trn.bom_id left join pro_ms_bom_version as bv on bv.bom_version_id = bom.bom_version_id left join product_mst as p on p.product_id=bom.bom_product where bom_trn_status = 0 and bom_trn.p_bom_id =".$POST['bom_id'];

				$html .=  '<div class="col-md-12">
								<div class="form-group">
								<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
								<tr id="field">
								<th class="text-center">#</th>
								<th class="text-center">Product Name</th>
								<th class="text-center">BOM No.</th>
								<th class="text-center">BOM Version</th>
								<tbody id="fil_product_tbl">';


				$html .= '<div class="alert alert-info" style="text-align: center;font-size: 25px;">
						  <strong><i class="fa fa-info-circle"></i></strong> BOM ALREADY IN USE IN BELOW ITEMS
						</div>';

				$result = $dbcon->query($query);
				$i = 1;
				while($rel=brp_mysqli_fetch_assoc($result)){
					
					$html .='<tr id="fieldtr'.$i.'" >
								<td style="vertical-align:top;">
								'.$i.'
								</td>
								
								<td style="vertical-align:top;">
								'.$rel['product_name'].'
								</td>

								<td style="vertical-align:top;">
								'.$rel['bom_no'].'
								</td>

								<td style="vertical-align:top;">
								'.$rel['version_name'].'
								</td>
							</tr>';	
					$i++;
				}

				$html .= "</tbody></table></div></div>";

				$html .='<div class="col-md-12" >
					<center>
						<input type="button" id="sp_btn" data-dismiss="modal" aria-hidden="true" name="submit" class="btn btn-danger" value="Close" />
					</center>
				</div>';

				echo $html;

			}
			else if(brp_strtolower($POST['mode']) == "get_process_desc") {
				$bom_id = $POST['bom_id'];
				
				$process_id = $POST['process_id'];
				
				 $query1="select id,description from tbl_temp_bom_process_desc where bom_id=".$bom_id." and process_id = " . $process_id;
				$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
				if($rows){
					echo json_encode( $rows );
				}else{
					echo '';
				}

				// echo $rows['description'];
			}
			else if(brp_strtolower($POST['mode']) == "save_process_desc") {
				// print_r($POST);die;
				$process_bom_id = $POST['process_bom_id'];
				$process_id = $POST['process_id'];
				$desc = $POST['desc'];

				$info['bom_id'] =$process_bom_id  ;
				$info['process_id'] = $process_id ;
				$info['description'] = $desc;

				if(empty($POST['eid'])){
					$inserid=add_record('tbl_temp_bom_process_desc', $info, $dbcon);
				}else{
					$inserid=update_record("tbl_temp_bom_process_desc", $info,"id=".$POST['eid'] , $dbcon);	
					$inserid =1;	
				}
				
				// $inserid=update_record("pro_bom_process", $info,"pro_bom_process_id=".$POST['eid'] , $dbcon);	
				
				if($inserid){
					if(!empty($POST['eid'])){
						echo 'update';
					}else{
						echo '1';	
					}
					
				}else{
					echo '0';
				}
			}

			// added by Sanat :: 17-02-22
			else if(brp_strtolower($POST['mode']) == "check_duplicate_product_validation") {
				$product_id = $POST['product_id'];
				$bom_version_id = $POST['bom_version_id'];
				$parent_bom_id = $POST['bom_id'];

				$query="select bom_id from tbl_bom where bom_status!=2 and bom_product=".$product_id." and company_id=".$_SESSION['company_id']. " AND bom_version_id = " .$bom_version_id;

				$result=$dbcon->query($query);
				$row=brp_mysqli_fetch_assoc($result);

				if(brp_mysqli_num_rows($result) > 0){
					$bom_id=$row['bom_id'];
					
					if($bom_id == $parent_bom_id){
						echo "1";
					}else{
						check_same_product_exists_in_parent($dbcon,$parent_bom_id,$bom_id);	
					}
					
				}else{
					echo '0';
				}

				
			}
			// added by Sanat :: 10-11-22
			else if(brp_strtolower($POST['mode']) == "check_duplicate_product_entry") {
				$product_id = $POST['product_id'];
				$bom_version_id = $POST['bom_version_id'];
				$parent_bom_id = $POST['bom_id'];
				$bom_trn_id = $POST['edit_id'];

				$bwhr = "";
				if(!empty($bom_trn_id)){
					$bwhr = " and bom_trn_id != " . $bom_trn_id;
				}
				$query="select bom_trn_id from tbl_bomtrn where bom_trn_status=0 and product_id = ".$product_id." and bom_version_id = ". $bom_version_id ." and bom_id=".$parent_bom_id . $bwhr;
				// echo "</br></br>";	
					$result=$dbcon->query($query);
				if(brp_mysqli_num_rows($result) > 0){
					echo '1';
				}else{
					echo '0';
				}
				
			}
			else if (brp_strtolower($POST['mode']) == "show_bom_used_in_list") {
	$bom_version_id = $POST['bom_version_id'];
	$bom_id = $POST['bom_id'];

	$str = '<div class="alert alert-info" style="text-align: center;font-size: 18px;">
	<strong><i class="fa fa-info-circle"></i></strong> BOM ALREADY IN USE IN BELOW ITEMS
  </div>';

	$query = "select * from tbl_bomtrn as bom where bom.bom_trn_status = 0 and  bom.p_bom_id=" . $bom_id;
	$result = $dbcon->query($query);

	$bom_cnt = brp_mysqli_num_rows($result);
	if($bom_cnt > 0){
	$str .= "<div class='row mtop20'>
	<div class='col-md-12'>";
	$str .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
	<tr id="field">
	<th class="text-center">#</th>
	<th class="text-center">Product Name</th>
	<th class="text-center">BOM No.</th>
	<th class="text-center">BOM Version</th>
	<tbody id="fil_product_tbl">';
	$i = 1;
	while($res = brp_mysqli_fetch_assoc($result)){
		$qry = "select bom.*,pro.product_name,bv.version_name from tbl_bom as bom
				left join product_mst as pro on pro.product_id = bom.bom_product
				left join pro_ms_bom_version as bv on bv.bom_version_id = bom.bom_version_id	
				where bom_status = 0 and  bom_id=" . $res['bom_id'];
		$result1 = $dbcon->query($qry);
		$res1 = brp_mysqli_fetch_assoc($result1);
		$str .= '<tr id="fieldtrR' . $i . '" >
								<td style="vertical-align:top;">
								' . $i . '
								</td>
								
								<td style="vertical-align:top;">
								' . $res1['product_name'] . '
								</td>

								<td style="vertical-align:top;">
								' . $res1['bom_no'] . '
								</td>

								<td style="vertical-align:top;">
								' . $res1['version_name'] . '
								</td>
							</tr>';
		$i++;
	}
	$str .= "</tbody></table></div></div>";
}

$qry = "SELECT so_trn.sales_ordertrn_id,pro.product_name,so.sales_order_no,cust.l_name,so.sales_order_date FROM `tbl_sales_ordertrn` as so_trn 
	left join product_mst as pro on pro.product_id = so_trn.product_id
	left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id 
	left join tbl_ledger cust on so.cust_id=cust.l_id
	WHERE sales_ordertrn_status = 0 and so_trn.bom_id = " . $bom_id;
	$res = $dbcon->query($qry);
	$so_cnt = brp_mysqli_num_rows($res);
	if($so_cnt > 0){
	
		$str .= "<div class='row mtop20'>
	<div class='col-md-12'>";
	$str .= '<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
	<tr id="field">
	<th class="text-center">#</th>
	<th class="text-center">Salesorder No</th>
	<th class="text-center">Salesorder Dae</th>
	<th class="text-center">Product Name</th>
	<th class="text-center">Customer Name</th>
	<tbody id="fil_product_tbl">';
	$x = 1;
	while($rl =  brp_mysqli_fetch_assoc($res)){
		$str .= '<tr id="fieldtrRs' . $x . '" >
								<td style="vertical-align:top;">
								' . $x . '
								</td>
								
								<td style="vertical-align:top;">
								' . $rl['sales_order_no'] . '
								</td>

								<td style="vertical-align:top;">
								' .date('d-m-Y',strtotime($rl['sales_order_date'])). '
								</td>

								<td style="vertical-align:top;">
								' . $rl['product_name'] . '
								</td>
								<td style="vertical-align:top;">
								' . $rl['l_name'] . '
								</td>
							</tr>';
		$x++;
	}
	$str .= "</tbody></table></div></div>";
}
	$str .= '<div class="alert alert-info" style="text-align: center;font-size: 18px;">
	<strong><i class="fa fa-info-circle"></i></strong> PLEASE REMOVE FROM ABOVE PRODUCT IF YOU WANT TO DELETE THIS BOM
  </div>';
	echo $str;
}
else if (brp_strtolower($POST['mode']) == "delete_default_bom_version") {
	$bom_id = $POST['bom_id'];
	$product_id = $POST['product_id'];
	$bom_version_id = $POST['bom_version_id'];

	$info['bom_version_status'] = 2;
	$updateid = update_record('pro_ms_bom_version', $info, "product_id=" . $product_id .' and bom_version_id = ' . $bom_version_id, $dbcon);

	// var_dump($updateid);
	$info_v['bom_status']	= 2;
	$updateestimateid = update_record('tbl_bom', $info_v, "bom_id=" . $bom_id, $dbcon);

	$info_trn['bom_trn_status'] = 2;
	$updateid1 = update_record('tbl_bomtrn', $info_trn, "bom_id=" . $bom_id, $dbcon);


	if ($updateid) {
		echo "1";
	} else {
		echo "0";
	}

}else if (brp_strtolower($POST['mode']) == "add_extra_bom_no") {
	$edit_id = $POST['edit_id'];
	$bom_id = $POST['bom_id'];
	$product_id = $POST['product_id'];
	$bom_version_id = $POST['bom_version_id'];

	$info = array();
	$info['ext_no']		 	= $POST['extra_no'];
	if($edit_id > 0){
		$updateid = update_record('tbl_bom_extra_no', $info, "ext_id=".$edit_id, $dbcon);
		if($updateid){
			echo "update";	
		}else{
			echo "0";
		}
	}else{
		$info['bom_id'] 		= $POST['bom_id'];
		$info['main_bom_id'] 	= $POST['main_bom_id'];
		$info['parent_bom_id']  = $POST['parent_bom_id'];
		$info['bom_version_id'] = $POST['bom_version'];
		$info['product_id'] 	= $POST['product_id'];
		$info['cdate']			= date("Y-m-d H:i:s");
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];	
		$inserid=add_record("tbl_bom_extra_no", $info, $dbcon);	
		if($inserid) {
			echo "1";
		}else{
			echo "0";
		}
	}
}else if (brp_strtolower($POST['mode']) == "delete_document") {
	$id	= $POST['id'];

	$image_de = "select * from tbl_bom_documents WHERE doc_id=".$id;
	$result = $dbcon->query($image_de);
	$row = brp_mysqli_fetch_array($result);

	unlink($row['file_path'].$row['file_name']);

	$sql = "UPDATE `tbl_bom_documents` SET status = 2 WHERE doc_id='".$id."' ";	
	$updatetrancationid = $dbcon->query($sql);		
	
	if($updatetrancationid)
		echo "1";	
	else
		echo "0";
}else if (brp_strtolower($POST['mode']) == "view_document_data") {
	$bom_id = $POST['bom_id'];
	$bom_version_id = $POST['bom_version_id'];

	 $qry="SELECT * FROM `tbl_bom_documents` WHERE status =  0 and bom_id = ". $bom_id ." AND bom_version_id = ". $bom_version_id ." and `company_id`= ".$_SESSION['company_id'];

	$result=$dbcon->query($qry);

	echo '<div class="form-group">
			<div class="col-md-12 col-xs-11">
			<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
				<tr id="field">
					<th class="text-center" width="10%">SR No.</th>
					<th class="text-center" width="25%">Document Name</th>
					<th class="text-center" width="25%">View</th>
					<th class="text-center" width="25%">Action</th>
				</tr>';
							
							//echo $query;
			if(brp_mysqli_num_rows($result)>0)
			{
				$i=1;
				while($rel=brp_mysqli_fetch_assoc($result))
				{

					$exts = array('gif', 'png', 'jpg'); 
					if(in_array(end(explode('.', $rel['file_name'])), $exts)){

						$filetype = '<a href="'.ROOT.'view/upload/bom_documents/'.$rel["file_name"].'" target="_blank"><img src="'.ROOT.'view/upload/bom_documents/'.$rel["file_name"].'" class="img-thumbnail" width="70" height="70"></a>';
					}else{
						$filetype = '<a href="'.ROOT.'view/upload/bom_documents/'.$rel["file_name"].'" target="_blank">Download File</a>';
					}	
					
				 echo '<tr id="fieldtr'.$i.'">
						
						<td style="vertical-align:top;" class="text-center">
							'.$i.'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$rel['image_name'].'
						</td>
						<td style="vertical-align:top;" class="text-center">
							'.$filetype.'
						</td>

						<td style="vertical-align:top;" class="text-center">
							<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_image('.$rel['doc_id'].','.$rel['bom_id'].','.$rel['bom_version_id'].');" id="fieldremove'.$i.'"><i class="fa fa-trash"></i></button>
						</td>					
					</tr>';
					$i++;
				}
			}else{
				 echo '<tr class="text-center"><td colspan="4" style="text-align:center">NO DATA FOUND</td></tr>';
			}

}else if (brp_strtolower($POST['mode']) == "save_bom_document") {
	// echo "<pre>";
	// print_r($_FILES);
	// print_r($POST);die;

	$bom_id = $POST['doc_bom_id'];
	$bom_version_id = $POST['doc_bom_version_id'];

	if(!empty($_FILES['dr_file']['tmp_name'][0])) {
		
		$cnt=count($_FILES['dr_file']['name']);
		$rand=rand(0,999999);
			

		$test = explode('.', $_FILES["dr_file"]["name"]);
		$ext = brp_strtolower(end($test));
		$name = $bom_id.'_'.$bom_version_id.'_'.$rand. '.' . $ext;
		$path='../../../view/upload/bom_documents/';

		if (!file_exists($path)){
     	   mkdir($path);
		}

		$location = $path . $name;  
		move_uploaded_file($_FILES["dr_file"]["tmp_name"], $location);

		$bom_doc_info['bom_id'] 	= $bom_id;
		$bom_doc_info['bom_version_id'] 	= $bom_version_id;
		$bom_doc_info['image_name'] = $POST['doc_image_name'];
		$bom_doc_info['file_name'] 	= $name;
		$bom_doc_info['file_path'] 	= $path;
		$bom_doc_info['status'] = 0;
		$bom_doc_info['user_id']		= $_SESSION['user_id'];
		$bom_doc_info['company_id']	= $_SESSION['company_id'];
		$bom_doc_info['cdate']		= date('Y-m-d H:i:s');
		
		$inserid = add_record('tbl_bom_documents', $bom_doc_info, $dbcon);
	
		if($inserid > 0){
			echo "1";
		}else{
			echo "0";
		}
	}else{
		echo "2";
	}
}

			function do_maths($expression) {
				$o = preg_replace('/[^0-9+\-*\/().]/', '', $expression);
				eval($o);
				return $o;
			}  	
			function clone_bom_trn_data_func($dbcon,$bom_trn_id,$parent_id){
				$bom_trn_qry="select * from tbl_bomtrn where bom_trn_status=0 and bom_trn_id=".$bom_trn_id;
				$bom_trn_qry_rs=$dbcon->query($bom_trn_qry);
				if(brp_mysqli_num_rows($bom_trn_qry_rs)){
					while($trn_rel=brp_mysqli_fetch_assoc($bom_trn_qry_rs)){
						$info1['product_id']			= $trn_rel['product_id'];
						$info1['product_type']			= $trn_rel['product_type'];
						$info1['product_qty']			= $trn_rel['product_qty'];
						$info1['parent_id']				= $parent_id;
						$info1['product_uom']			= $trn_rel['product_uom'];
						$info1['product_base_unit']		= $trn_rel['product_base_unit'];
						$info1['product_act_qty']		= $trn_rel['product_act_qty'];
						$info1['product_width']			= $trn_rel['product_width'];
						$info1['product_height']		= $trn_rel['product_height'];
						$info1['product_thickness']		= $trn_rel['product_thickness'];
						$info1['product_density']		= $trn_rel['product_density'];
						$info1['product_kg']			= $trn_rel['product_kg'];
						$info1['product_grp']			= $trn_rel['product_grp'];
						$info1['product_base_qty']		= $trn_rel['product_base_qty'];
						$info1['sale_product_id']		= $trn_rel['sale_product_id'];
						$info1['bom_level']				= $trn_rel['bom_level'];
						$info1['product_piece_qty']		= $trn_rel['product_piece_qty'];
						$info1['bom_actual_add_status']	= "1";
						$info1['user_id']				= $_SESSION['user_id']; 	
						$info1['company_id']			= $_SESSION['company_id']; 	
						$info1['branch_id']				= $_SESSION['branch_id']; 	
						$inserid=add_record("tbl_bomtrn", $info1, $dbcon);	

			//Check again for child trn entry
						$chk_trn_qry="select * from tbl_bomtrn where bom_trn_status=0 and parent_id=".$trn_rel['bom_trn_id'];
						$chk_trn_qry_rs=$dbcon->query($chk_trn_qry);
						if(brp_mysqli_num_rows($chk_trn_qry_rs)){
							while($inr_trn=brp_mysqli_fetch_assoc($chk_trn_qry_rs)){
								clone_bom_trn_data_func($dbcon,$inr_trn['bom_trn_id'],$inserid);
							}
						}
					}
				}
			}

			function update_sales_order_status($dbcon,$sales_order_id,$sales_order_pro_id, $sales_status){
				$upd_sls_trn['bom_use_trn_status'] = $sales_status;
	//$upd_sls_trn['cdate'] = date("Y-m-d H:i:s");
				$updateslsid=update_record('tbl_sales_ordertrn', $upd_sls_trn, "sales_ordertrn_status=0 and sales_order_id=".$sales_order_id." and product_id=".$sales_order_pro_id , $dbcon);


	//Update Main status if all used
				$sel_sales_ord_qry="select * from tbl_sales_ordertrn where sales_ordertrn_status=0 and bom_use_trn_status=0 and sales_order_id=".$sales_order_id;
				$sales_num_row=brp_mysqli_num_rows($dbcon->query($sel_sales_ord_qry));
				if(!$sales_num_row){
					$upd_sls['bom_use_status'] = 1;
					$upd_sls['cdate'] 		= date("Y-m-d H:i:s");
					$updateslsid = update_record('tbl_sales_order', $upd_sls, "sales_order_id=".$sales_order_id, $dbcon);
				}
				else{
					$upd_sls['bom_use_status'] = 0;
					$upd_sls['cdate'] 		= date("Y-m-d H:i:s");
					$updateslsid = update_record('tbl_sales_order', $upd_sls, "sales_order_id=".$sales_order_id, $dbcon);
				}
			}
			function load_po_no($dbcon,$typeid){
				$row=array();
				$query1="select * from tbl_invoicetype where type_id=".$typeid." and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
				$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));
				$id=$rows['taxinvoice_start'];
				$id=$id+1;
	//$start=(date('m')<'04') ? date('y',strtotime(date('y').'-1 year')) : date('y');
	//$end = $start+1;
				if($rows['invoice_format']=='2')
				{
					$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
				}
				else if($rows['invoice_format']=='1')
				{
					$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
				}
				else if($rows['invoice_format']=='3'){
					$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
				}
				else{
					$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
				}
				$row['challanno']=str_pad($id,3,"0",STR_PAD_LEFT);

				$inv_no=$row['invoiceno'];
				return ($inv_no);
			}

			function calculate_pro_base_qty($dbcon, $parent_id, $child_id, $parent_qty){

				$parentsql = "SELECT * FROM `tbl_bom` where bom_status!=2 and bom_product = '".$parent_id."' ";
				$parentrows=brp_mysqli_fetch_assoc($dbcon->query($parentsql));

				$childsql = "SELECT * FROM `tbl_bomtrn` where bom_trn_status!=2 and product_id = '".$child_id."' and bom_id = '".$parentrows['bom_id']."' ";
				$childrows=brp_mysqli_fetch_assoc($dbcon->query($childsql));

				$parent_base_qty = $parentrows['product_base_qty'];
				$child_base_qty = $childrows['product_base_qty'];


 		//$single_qty = floatval($child_base_qty) / floatval($parent_base_qty);
				$single_qty = $child_base_qty/$parent_base_qty;

				$req_qty = $single_qty * $parent_qty;

 		//return number_format($req_qty,5,'.','');
				return $req_qty;

			}

			function calculate_pro_conv_qty($dbcon, $parent_id, $child_id, $parent_qty){
				$parentsql = "SELECT * FROM `tbl_bom` where bom_status!=2 and bom_product = '".$parent_id."' ";
				$parentrows=brp_mysqli_fetch_assoc($dbcon->query($parentsql));

				$childsql = "SELECT * FROM `tbl_bomtrn` where bom_trn_status!=2 and product_id = '".$child_id."' and bom_id = '".$parentrows['bom_id']."' ";
				$childrows=brp_mysqli_fetch_assoc($dbcon->query($childsql));

				$parent_base_qty = $parentrows['product_conv_qty'];
				$child_base_qty = $childrows['product_conv_qty'];

 		//$single_qty = floatval($child_base_qty) / floatval($parent_base_qty);
				$single_qty = $child_base_qty / $parent_base_qty;

				$req_qty = $single_qty * $parent_qty;

 		//return number_format($req_qty,5,'.','');
				return $req_qty;

			}

		function check_same_product_exists_in_parent($dbcon,$parent_bom_id,$chk_bom_id){
			// echo "</br></br>";
			$query="select bom_id from tbl_bomtrn where bom_trn_status=0 and p_bom_id=".$parent_bom_id;
				// echo "</br></br>";	
			$result=$dbcon->query($query);
			if(brp_mysqli_num_rows($result) > 0){
				while($rel=brp_mysqli_fetch_assoc($result)){ 
					$bom_id = $rel['bom_id'];
					if($bom_id == $chk_bom_id){
						echo "1";
						break;
					}else{
						check_same_product_exists_in_parent($dbcon,$bom_id,$chk_bom_id);	
					}
				}	
			}else{
				echo "0";
			}	
		}
