<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//check permission for get sales order details

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	MRP_GET_SALES_ORDER_SLUG_VIEW,MRP_GET_SALES_ORDER_SLUG_CREATE
]);
$companyConfiguration=getCompanyConfiguration($dbcon);

$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search=explode(",", $production_pro_search);

		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "generate_report_min_new") {
			
		/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/
		
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		//$where_db = check_branch('so_trn', $branch_id);
		
		if(!empty($branch_id)){
			//$pro_branch=" and so_trn.production_branch_id=".$branch_id;
		}
		
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)

		$aColumns = array('pro.product_name','bsm.batch_size_name','so_trn.mixing_batch_size','so_trn.extrusion_material','IFNULL(sum(req_batch-complate_batch),0) as pending_qty');


		$sIndexColumn = "so_trn.solid_production_planning_id";
		$isWhere = array("so_trn.status=0");

		$sTable = "solid_production_planning as so_trn";

		$isJOIN = array("left join product_mst as pro on pro.product_id=so_trn.extrusion_material","left join solid_batch_size_mst as bsm on bsm.batch_size_id=so_trn.mixing_batch_size");
		
		$hOrder = "pro.product_name";
		//$hGroupby = "so_trn.extrusion_material,so_trn.mixing_batch_size";
		$hGroupby =array("so_trn.extrusion_material","so_trn.mixing_batch_size");
		$having=" pending_qty > 0";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		
		//print_r($sqlReturn);
		foreach($sqlReturn as $row) {

			$row_data = array();
			
			$row_data[] = $row['product_name'];
			$row_data[] = $row['batch_size_name'];
			$row_data[] = $row['pending_qty'];
			$view_desc='<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_so_trn_modal('.$row['extrusion_material'].','.$row['mixing_batch_size'].')"><i class="fa fa-eye"></i></button>';
			
			$row_data[] = $view_desc;

			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );

	}else if(strtolower($POST['mode']) == "preview_solid_planning1") {
		$q="select pro.product_name,bsm.batch_size_name,IFNULL(sum(req_batch-complate_batch),0) as pending_qty from solid_production_planning as gd 
		left join product_mst as pro on pro.product_id=gd.extrusion_material
		left join solid_batch_size_mst as bsm on bsm.batch_size_id=gd.mixing_batch_size
		where gd.status=0 and gd.extrusion_material=".$POST['product_id']." and gd.mixing_batch_size=".$POST['batch_size']." group by gd.extrusion_material,gd.mixing_batch_size";
		$rel=$dbcon->query($q);
		$row=mysqli_fetch_array($rel);
		
		echo json_encode($row);
	}else if(strtolower($POST['mode']) == "save_mixing") {
		$end_qty=$_POST['mixing_finish_qty'];

		$info_dil['extrusion_material']			= $_POST['product_id'];
		$info_dil['mixing_batch_size']			= $_POST['batch_size_id'];
		$info_dil['end_qty']					= $end_qty;

		$info_dil['user_id']				= $_SESSION['user_id'];
		$info_dil['cdate']					= date("Y-m-d h:i:s");
		$info_dil['company_id']				= $_SESSION['company_id'];
		
		$inserid_k=add_record("tbl_mixing_end",$info_dil,$dbcon);

		$q="select IFNULL(req_batch-complate_batch,0) as pending_qty,solid_production_planning_id,complate_batch,calculation_batch_qty,bms.batch_size_name,mixing_complate_qty from solid_production_planning as gd 
		left join solid_batch_size_mst as bms on bms.batch_size_id=gd.mixing_batch_size
		where gd.status=0 and gd.extrusion_material=".$_POST['product_id']." and gd.mixing_batch_size=".$_POST['batch_size_id'];
		$rel=$dbcon->query($q);
		$cal_batch=0;
		while($row=mysqli_fetch_array($rel)){
			if($end_qty>0){
				if($end_qty>=$row['pending_qty']){
					$entry_qty=$row['pending_qty'];
				}else{
					$entry_qty=$end_qty;
				}
				$end_qty=$end_qty-$entry_qty;
				$cal_batch=$row['calculation_batch_qty'];
				$menc=$entry_qty*$cal_batch;
				$info['complate_batch']= $row['complate_batch']+$entry_qty;
				$info['mixing_complate_qty']= $row['mixing_complate_qty']+$menc;
				$updateid_k=update_record("solid_production_planning",$info,"solid_production_planning_id=".$row['solid_production_planning_id'],$dbcon);	

				$info_dil1['mixing_end_id']							= $inserid_k;
				$info_dil1['solid_production_planning_id']			= $row['solid_production_planning_id'];
				$info_dil1['end_qty']								= $entry_qty;

				$info_dil1['user_id']				= $_SESSION['user_id'];
				$info_dil1['cdate']					= date("Y-m-d h:i:s");
				$info_dil1['company_id']			= $_SESSION['company_id'];
				//var_dump($info_dil1);
				$inserid_ks=add_record("tbl_mixing_end_trn",$info_dil1,$dbcon);
				mixing_stock_effects($dbcon,$inserid_k,$info_dil1['end_qty'],$row['batch_size_name'],$inserid_ks,$cal_batch);
			}
		}
		
		if($inserid_k){
			$arry['msg']=1;
			$arry['id']=$inserid_k;
			
		}else{
			$arry['msg']=0;
		}
		echo json_encode($arry);
	}

	function mixing_stock_effects($dbcon,$mixing_id,$mixing_qty,$batch_size,$mixing_end_trn,$cal_batch){
		$q="select gd.extrusion_material,pro.product_base_unit,pro.product_conv_unit,trn.solid_production_planning_id,sop.excluding_allocate_qty from tbl_mixing_end_trn trn 
			left join tbl_mixing_end as gd on gd.mixing_end_id=trn.mixing_end_id
			left join product_mst as pro on pro.product_id=gd.extrusion_material
			left join solid_production_planning as sop on sop.solid_production_planning_id=trn.solid_production_planning_id
		where gd.status=0 and trn.mixing_end_trn_id=".$mixing_end_trn;
		$rel=$dbcon->query($q);
		$row=mysqli_fetch_array($rel);
		$excluding_allocate_qty=$row['excluding_allocate_qty'];
		for($i=0;$i<$mixing_qty;$i++)
		{
			$batch_no1 = get_batch_no($dbcon,$row['extrusion_material']);
			update_batch_no($dbcon,$row['extrusion_material']);
			
			$info_dil1['stock_date']	= date("Y-m-d");
			$info_dil1['product_id']	= $row['extrusion_material'];
			$info_dil1['base_unit']		= $row['product_base_unit'];
			$info_dil1['base_stock']	= $batch_size;
			$info_dil1['convert_unit']	= $row['product_conv_unit'];
			$info_dil1['convert_stock']	= $batch_size;
			$info_dil1['stock_flage']	= 1;
			$info_dil1['godown_id']		= 1;
			$info_dil1['ref_name']		= "mixing";
			$info_dil1['ref_id']		= $mixing_id;
			$info_dil1['cdate']			= date("Y-m-d h:i:s");
			$info_dil1['user_id']		= $_SESSION['user_id'];
			$info_dil1['company_id']	= $_SESSION['company_id'];
			$info_dil1['batch_no']		= $batch_no1;
			$inserid_ks=add_record("tbl_stock_trn",$info_dil1,$dbcon);

			if($inserid_ks){
				$info_dil1r['reserve_date']		= date("Y-m-d");
				$info_dil1r['product_id']		= $info_dil1['product_id'];
				$info_dil1r['godown_id']		= 1;
				$info_dil1r['base_unit']		= $info_dil1['base_unit'];
				$info_dil1r['base_stock']		= $info_dil1['base_stock'];
				$info_dil1r['convert_unit']		= $info_dil1['convert_unit'];
				$info_dil1r['convert_stock']	= $info_dil1['convert_stock'];
				$info_dil1r['stock_flage']		= 1;
				$info_dil1r['ref_name']			= "mixing";
				$info_dil1r['ref_id']			= $mixing_id;
				$info_dil1r['cdate']			= date("Y-m-d h:i:s");
				$info_dil1r['user_id']			= $_SESSION['user_id'];
				$info_dil1r['company_id']		= $_SESSION['company_id'];
				$info_dil1r['p_id']				= $row['solid_production_planning_id'];
				$info_dil1r['stock_id']			= $inserid_ks;
				$inserid_res=add_record("tbl_reserve_stock",$info_dil1r,$dbcon);

				$infods['used_base_stock']= $info_dil1r['base_stock'];
				$infods['used_convert_stock']= $info_dil1r['convert_stock'];
				$updateid_ks2=update_record("tbl_stock_trn",$infods,"stock_id=".$inserid_ks,$dbcon);
				
				$excluding_allocate_qty=$excluding_allocate_qty+$cal_batch;
				$infod['excluding_allocate_qty']= $excluding_allocate_qty;
				$updateid_k2=update_record("solid_production_planning",$infod,"solid_production_planning_id=".$row['solid_production_planning_id'],$dbcon);
			}
				
			//reserve_mixing($dbcon,$mixing_id,$inserid_ks,$info_dil1['base_stock'],$mixing_end_trn);
		}
		
	}
	function reserve_mixing($dbcon,$mixing_id,$stock_id,$stock_qty,$mixing_end_trn){
		$q="select IFNULL(gd.end_qty-gd.reserve_qty,0) as pending_qty,miz.extrusion_material,product_base_unit,product_conv_unit,mixing_end_trn_id,gd.solid_production_planning_id,sop.excluding_allocate_qty,gd.reserve_qty from tbl_mixing_end_trn as gd 
			left join tbl_mixing_end as miz on miz.mixing_end_id=gd.mixing_end_id
			left join product_mst as pro on pro.product_id=miz.extrusion_material
			left join solid_production_planning as sop on sop.solid_production_planning_id=gd.solid_production_planning_id
			where gd.status=0 and gd.mixing_end_id=".$mixing_id;
		$rel=$dbcon->query($q);
		while($row=mysqli_fetch_array($rel)){
			if($row['pending_qty']>0){
				$info_dil1['reserve_date']		= date("Y-m-d");
				$info_dil1['product_id']		= $row['extrusion_material'];
				$info_dil1['godown_id']			= 1;
				$info_dil1['base_unit']			= $row['product_base_unit'];
				$info_dil1['base_stock']		= $stock_qty;
				$info_dil1['convert_unit']		= $row['product_conv_unit'];
				$info_dil1['convert_stock']		= $stock_qty;
				$info_dil1['stock_flage']		= 1;
				$info_dil1['ref_name']			= "mixing";
				$info_dil1['ref_id']			= $mixing_id;
				$info_dil1['cdate']				= date("Y-m-d h:i:s");
				$info_dil1['user_id']			= $_SESSION['user_id'];
				$info_dil1['company_id']		= $_SESSION['company_id'];
				$info_dil1['p_id']				= $row['solid_production_planning_id'];
				$info_dil1['stock_id']			= $stock_id;
				$inserid_ks=add_record("tbl_reserve_stock",$info_dil1,$dbcon);
				if($inserid_ks){
					$infod['excluding_allocate_qty']= $row['excluding_allocate_qty']+$info_dil1['base_stock'];
					$updateid_k2=update_record("solid_production_planning",$infod,"solid_production_planning_id=".$row['solid_production_planning_id'],$dbcon);	
				}

				$info['reserve_qty']= $row['reserve_qty']+1;
				$updateid_k=update_record("tbl_mixing_end_trn",$info,"mixing_end_trn_id=".$row['mixing_end_trn_id'],$dbcon);	
			}
		}
	}
	
?>


