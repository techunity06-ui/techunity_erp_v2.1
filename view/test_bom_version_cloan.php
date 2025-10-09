<?php
/*
	old bom to new bom (version wise bom ) convert cloan
	pro_ms_bom_version new entry 
	pro_bom_process  new entry
*/
session_start();
include_once("../config/config.php");
include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
include_once("../config/session.php");
include_once("../include/function_database_query.php");

$query="select bom.*,pmst.product_name from tbl_bom as bom
			left join product_mst as pmst on pmst.product_id=bom.bom_product	
			where bom.bom_status=0 and bom.company_id=".$_SESSION['company_id']." group by bom.bom_product";
		$result=$dbcon->query($query);
		$i=1;
		while($row=brp_mysqli_fetch_assoc($result)){
			
			$info['bom_no']				= "BOM_V".$i;
			$info['product_id']			= $row['bom_product'];
			$info['version_name']		= $row['product_name']."_V".$i;
			$info['is_default_bom']		= 1;
			$info['bom_version_status']	= 0;
			$info['bom_active_status']	= 1;
			$info['bom_version_date']	= date("Y-m-d");
			$info['bom_unit_qty']		= $row['product_base_qty'];
			$info['bom_unit']		= $row['product_base_unit'];
			$info['bom_conv_unit']		= $row['product_conv_unit'];

			// $type="base_unit";
			// $conv_qty=convert_stock($dbcon,$row['product_base_qty'],$row['bom_product'],$type);
			
			$info['bom_conv_qty']		= $row['product_conv_qty'];
			$info['branch_id']			= $row['branch_id'];
			
			
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['usertype_id']		= $_SESSION['usertype_id'];
			$info['company_id']			= $_SESSION['company_id'];
			
			$inserestimateid=add_record('pro_ms_bom_version', $info, $dbcon);
			
			$info_bom['bom_version_id']			= $inserestimateid;
			$info_bom_trn['p_bom_version_id']		= $inserestimateid;
			
			$updateid11=update_record('tbl_bom', $info_bom, "bom_id=".$row['bom_id'] , $dbcon);
			
			$updateid11=update_record('tbl_bomtrn', $info_bom_trn, "bom_id=".$row['bom_id'] , $dbcon);
				

			$query2="select * from tbl_product_process as bom
					where bom.status=0 and  bom.product_id=".$row['bom_product'];
			$result2=$dbcon->query($query2);
			while($row2=brp_mysqli_fetch_assoc($result2)){
				
				$infop['product_id']			= $row['bom_product'];
				$infop['bom_version_id']		= $inserestimateid;
				$infop['pr_process_id']			= $row2['pr_process_id'];
				$infop['process_status']		= 0;
				$infop['bom_id']				= $row['bom_id'];
				$infop['priority']				= $row2['process_priority'];
				
				
				$infop['cdate']					= date("Y-m-d H:i:s");
				$infop['user_id']				= $_SESSION['user_id'];
				$infop['company_id']			= $_SESSION['company_id'];
				//var_dump($info);
				$inserestimateid2=add_record('pro_bom_process', $infop, $dbcon);
			}

		$i++;
	}
	
	$query1="select * from tbl_bom as bom
			where bom.bom_status=0 and bom.company_id=".$_SESSION['company_id']."";
		$result1=$dbcon->query($query1);
		while($row1=brp_mysqli_fetch_assoc($result1)){
			
			$info_bom_trn_b['p_bom_version_id']	= $row1['bom_version_id'];
			$updateid11=update_record('tbl_bomtrn', $info_bom_trn_b, "bom_id=".$row1['bom_id'] , $dbcon);
		}

?>