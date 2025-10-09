<?php

session_start();
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
//print_r($_POST);		
	if(strtolower($POST['mode']) == "fetch") {
		if(!empty($POST['req_product_id'])){
			// $whr='';
			//$whr.=" s1.req_product_id='".$POST["req_product_id"]."'";
			$whr.=" s1.bom_product='".$POST["req_product_id"]."'";
		}
		// if(!empty($POST['product_id'])){
		// 	$whr='';
		// 	$whr.=" pmst.product_id='".$POST["product_id"]."'";
		// 	//$whr.=" and pmst.product_id='".$POST["dproduct_id"]."'";
		// }
		$pid=$POST['product_id'];

			$i=1;
			$aColumns = array('s1.bom_id','s1.product_name','s1.bom_product','min(s1.product_base_qty)','s1.req_product_id','s1.req_unit_id','s1.req_product_base_qty');
			$sIndexColumn = "s1.bom_id";
			$isWhere = array();
			if(!empty($POST['product_id'])){
			$sTable = "( select bom.bom_id,bom.bom_product,pmst.product_name,btrn.product_id,btrn.product_base_qty, GROUP_CONCAT(btrn.product_id) as req_product_id, GROUP_CONCAT(btrn.product_base_unit) as req_unit_id, GROUP_CONCAT(btrn.product_base_qty) as req_product_base_qty from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
			left join product_mst as pmst on pmst.product_id=bom.bom_product
			where btrn.bom_trn_status=0 and pmst.product_type=0 and pmst.product_id in($pid) group by bom.bom_product ) s1";
			}else{
			$sTable = "( select bom.bom_id,bom.bom_product,pmst.product_name,btrn.product_id,btrn.product_base_qty, GROUP_CONCAT(btrn.product_id) as req_product_id, GROUP_CONCAT(btrn.product_base_unit) as req_unit_id, GROUP_CONCAT(btrn.product_base_qty) as req_product_base_qty from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
			left join product_mst as pmst on pmst.product_id=bom.bom_product
			where btrn.bom_trn_status=0 and pmst.product_type=0  group by bom.bom_product ) s1";
			}
			
					
			$isJOIN = array();
			if(!empty($POST['req_product_id'])){
				$isWhere = array("".$whr);
			}
			
			$hOrder = "s1.bom_product";
			$hGroupby = array("s1.bom_product");
			$having_clause = ''; 
			include($include.'pagging.php');
			//echo $squery;
			$appData = array();
			$id=1;
			
			foreach($sqlReturn as $row) {
				$row_data = array();

				$product_name = $row['product_name'];
				$bom_product = $row['bom_product'];

				$req_product_id = explode(',',$row['req_product_id']);
				$req_unit_id = explode(',',$row['req_unit_id']);
				$req_product_base_qty = explode(',',$row['req_product_base_qty']);

				//echo "<pre>";print_r($req_product_id);die;
				$making_qty = '';
				$all_making_qty = [];
				foreach ($req_product_id as $key => $req_pid) {
					$current_qty = get_current_stock_new($dbcon,$req_product_id[$key],$req_unit_id[$key]);

					if($current_qty=='0'){
						$making_qty = '0';
						break;
					}else{
						$reserve_qty = reserve_stock($dbcon,$req_product_id[$key],$req_unit_id[$key],'','','','','','','','','');
						$remaining_qty = $current_qty - $reserve_qty;

						$avg_qty = $remaining_qty / $req_product_base_qty[$key];
						$all_making_qty[] = floor($avg_qty);
					}
				}

				if($making_qty=='0' && empty($all_making_qty)){
					$fg_qty = $making_qty;
				}else{
					$fg_qty = min($all_making_qty);
				}
				
				$row_data[] = $row['sr'];
				$row_data[] = $product_name;
				$row_data[] = $fg_qty;
				
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
	/*else if(strtolower($POST['mode']) == "fetch_report") {
		$check_bom_sql = 'select s1.product_name,s1.bom_product,min(s1.product_base_qty),s1.req_product_id,s1.req_unit_id,s1.req_product_base_qty from
			( select bom.bom_product,pmst.product_name,btrn.product_id,btrn.product_base_qty, GROUP_CONCAT(btrn.product_id) as req_product_id, GROUP_CONCAT(btrn.product_base_unit) as req_unit_id, GROUP_CONCAT(btrn.product_base_qty) as req_product_base_qty from tbl_bom as bom
			left join tbl_bomtrn as btrn on btrn.bom_id=bom.bom_id
			left join product_mst as pmst on pmst.product_id=bom.bom_product
			where btrn.bom_trn_status=0 and pmst.product_type=0 group by bom.bom_product ) s1
			group by s1.bom_product';

		$bom_sql_exec=$dbcon->query($check_bom_sql);

		$html = '';
		if(brp_mysqli_num_rows($bom_sql_exec) > 0){
			
			$i='1';
			while($bom_row = brp_mysqli_fetch_assoc($bom_sql_exec)){

				$product_name = $bom_row['product_name'];
				$bom_product = $bom_row['bom_product'];

				$req_product_id = explode(',',$bom_row['req_product_id']);
				$req_unit_id = explode(',',$bom_row['req_unit_id']);
				$req_product_base_qty = explode(',',$bom_row['req_product_base_qty']);

				//echo "<pre>";print_r($req_product_id);die;
				$making_qty = '';
				$all_making_qty = [];
				foreach ($req_product_id as $key => $req_pid) {
					$current_qty = get_current_stock_new($dbcon,$req_product_id[$key],$req_unit_id[$key]);

					if($current_qty=='0'){
						$making_qty = '0';
						break;
					}else{
						$reserve_qty = reserve_stock($dbcon,$req_product_id[$key],$req_unit_id[$key]);
						$remaining_qty = $current_qty - $reserve_qty;

						$avg_qty = $remaining_qty / $req_product_base_qty[$key];
						$all_making_qty[] = floor($avg_qty);
					}
				}

				if($making_qty=='0' && empty($all_making_qty)){
					$fg_qty = $making_qty;
				}else{
					$fg_qty = min($all_making_qty);
				}
				
				$html .= '<tr>
							<td>'.$i.'</>
							<td>'.$product_name.'</td>
							<td>'.$fg_qty.'</td>
						</tr>';
				$i++;	
			}
		}
		//$html .= '<tr><td colspan="2">DATA NOT FOUND.</td></tr>';

		echo $html;
		
	}*/
		
?>