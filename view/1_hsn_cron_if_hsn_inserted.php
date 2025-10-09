<?php 
1
session_start();
ini_set('max_execution_time', 3000000);
include_once("../config/config.php");
include_once("../config/session.php");
include_once("../include/common_functions/common_functions.php");
	include("../include/function_database_query.php");
	
	reserve_mixing($dbcon,1,1,1000);
	function reserve_mixing($dbcon,$mixing_id,$stock_id,$stock_qty){
		$q="select IFNULL(gd.end_qty-gd.reserve_qty,0) as pending_qty,miz.extrusion_material,product_base_unit,product_conv_unit,mixing_end_trn_id from tbl_mixing_end_trn as gd 
			left join tbl_mixing_end as miz on miz.mixing_end_id=gd.mixing_end_id
			left join product_mst as pro on pro.product_id=miz.extrusion_material
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
				var_dump($info_dil1);
				$inserid_ks=add_record("tbl_reserve_stock",$info_dil1,$dbcon);

				$info['reserve_qty']= $row['reserve_qty']+1;
				$updateid_k=update_record("tbl_mixing_end_trn",$info,"mixing_end_trn_id=".$row['mixing_end_trn_id'],$dbcon);	
			}
		}
	}
?>