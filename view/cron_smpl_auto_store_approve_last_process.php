<?php 
session_start();
	include_once("../config/config.php");
	include_once(COMMON_FUNCTION_OUTER_PATH."common_functions.php");
	include_once("../config/session.php");
	include_once("../include/function_database_query.php");


$arr_batch = array('BBS164/86422','BBS164/86322','BBS164/86022','BBS164/85822','BBS164/85722','BBS164/85322','BB51/96222','BB51/96022','BB39/93622','BB39/93522','BB67/250922','BA161/135922','BBS164/86222','BBS164/86122','BBS164/85922','BBS164/85422','BBS164/84922','BA71/75522','BA71/75422','BB77/240922','BB77/240822','BB37/63822','AE95/78822');

	foreach($arr_batch as $row){
	echo	$query = "SELECT * FROM tbl_batch_data WHERE batch_no = '".$row."' order by batch_id desc limit 1";
		echo "</br>";
		$rel = brp_mysqli_fetch_assoc($dbcon->query($query));
		
		/*
				$stock_date = date("Y-m-d",strtotime($rel['cdate']));

				$qry__2 = "select * from tbl_grn_sub_trn where status = 0 and grn_trn_id=".$rel['grn_trn_id'] . " and product_id = " . $rel['product_id'];
									$result__3=$dbcon->query($qry__2);
									$row__3=brp_mysqli_fetch_array($result__3);


				$base_rate = $row__3['process_pus_material_rate'] / $row__3['product_qty']; //1000
				$conv_rate = $row__3['process_pus_material_conv_rate'] / $row__3['product_conv_qty'];

				$stock_id=add_stock($dbcon,$rel['product_id'],$rel['batch_unit'],$stock_date,"tbl_grn_trn",$rel['grn_trn_id'],$rel['grn_godown'],$rel['accept_qty'],1,$rel['branch_id'],"","",$rel['customer_id'],$rel['batch_id'],$rel['batch_no'],$base_rate,$conv_rate);*/
	}
?>