<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	EXTRA_STOCK_DELETE
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
if(brp_strtolower($POST['mode']) == "load_productdata") {
		
		$pid=$POST['eid'];
		
		$sel=$dbcon->query("select m.*,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from product_mst as m 
			left join unit_mst as bunit on bunit.unitid=m.product_base_unit
			left join unit_mst as cunit on cunit.unitid=m.product_conv_unit

		left join mst_material_spec as s on m.product_specification=s.ms_id where product_id='$pid'"); // s.m_type_density,
		$row=brp_mysqli_fetch_assoc($sel);
			echo json_encode($row);
		}
else if(brp_strtolower($POST['mode']) == "add") {
	
	$info['product_id']		= $POST['product_id'];
	$info['stock_id']	= $POST['stock_id'];
	$info['batch_no']	= $POST['batch_no'];
	$info['base_qty']	= $POST['product_base_qty'];
	$info['base_unit']		= $POST['product_base_unit'];
	$info['conv_qty']	= $POST['product_conv_qty'];
	$info['conv_unit']	= $POST['product_conv_unit'];
	$info['vendor_id']	= $POST['vendor_id'];
	$info['remark']	= $POST['remark'];
	
	$info['status']			= 0;
	$info['cdate']				= date("Y-m-d");
	$info['user_id']			= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];
	$info['branch_id']			= $POST['branch_id'];

	$insert_id=add_record('smpl_extra_stock', $info, $dbcon);
	if($insert_id){
		$log_entry=common_log_entry($dbcon,"smpl_extra_stock_add",1,"smpl_extra_stock",$insert_id);
		$arr['msg'] = '1';
	}else{
		$arr['msg'] = '0';
	}
	echo json_encode($arr);
}

else if(brp_strtolower($POST['mode'])== "delete_data")
	{
			$extra_stock_id = $POST['eid'];
			$info['status'] = 2;
			$update_id=update_record('smpl_extra_stock',$info, "extra_stock_id = ".$extra_stock_id, $dbcon);

			if($update_id){
				$row['res']="1";
			}else{
				$row['res']="0";	
			}
			
			echo json_encode($row);
		}

else if(brp_strtolower($POST['mode']) == "fetch") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	// $branch=$_SESSION['branch_id'];

	$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

	$where='';
	
	$appData = array();
	$i=1;
	$aColumns = array('ex.extra_stock_id','p.product_name','ex.batch_no','l.l_name','b.branch_name','u.unit_name as base_unit_name','cunit.unit_name as convert_unit_name','ex.base_qty','ex.used_base_qty','ex.conv_qty','ex.used_conv_qty','ex.status','ex.remark');
	$sIndexColumn = "ex.extra_stock_id";
	$isWhere = array("ex.status!=2 and ex.company_id = ".$_SESSION['company_id']);
	$sTable = "smpl_extra_stock as ex";			
	$isJOIN = array('left join product_mst as p on p.product_id=ex.product_id', 'left join branch_mst as b on b.branch_id = ex.branch_id','left join unit_mst as u on u.unitid=ex.base_unit', 'left join unit_mst as cunit on cunit.unitid=ex.conv_unit','left join tbl_ledger as l on l.l_id = ex.vendor_id');
		//$hGroupby = array("bom.bom_product");
	$hOrder = "ex.extra_stock_id desc";
	include($include.'pagging.php');
	$appData = array();

	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		
		$row_data[] = $row["product_name"];
		$row_data[] = $row["batch_no"];
		$row_data[] = $row["base_qty"] . ' ' . $row['base_unit_name'];
		$row_data[] = $row["used_base_qty"] . ' ' . $row['base_unit_name'];
		$row_data[] = $row["conv_qty"] . ' ' . $row['convert_unit_name'];
		$row_data[] = $row["used_conv_qty"] . ' ' . $row['convert_unit_name'];
		$row_data[] = $row["l_name"];
		$row_data[] = $row["branch_name"];
		$row_data[] = $row["remark"];

		
		$delete='';$edit='';$apprv_btn='';

				
				if(in_array(EXTRA_STOCK_DELETE,$bulkAccessArray)){
				if($row['used_base_qty'] == '' || $row['used_base_qty'] == '0'){
							 $delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['extra_stock_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
			
			 }
				
				// if(in_array(INVENTORY_WORKORDER_MATERIAL_ISSUE_SLUG_UPDATE,$bulkAccessArray)){
					/*$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.INVENTORY_ROOT.'extra_stock_edit/'.$row['extra_stock_id'].'"><i class="fa fa-pencil"></i></a>';*/
				// }
				
				
				$row_data[] = $edit.' '.$delete;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}else if(strtolower($POST['mode'])=="load_stock_qty")
		{
			$product_id=$POST['product_id'];
			$stock_id=$POST['stock_id'];
			$get_pro_type_qry="select product_type,product_base_unit from product_mst where product_id=".$product_id;
			$get_pro_type_rel=mysqli_fetch_assoc($dbcon->query($get_pro_type_qry));
			

			$product_type_arr = array("0", "1", "2", "3", "4", "5", "6", "7", "9", "-1");
			if (in_array($get_pro_type_rel['product_type'], $product_type_arr)){
				if(!empty($POST['unit_id'])){
					$unit_id=$POST['unit_id'];
				}else{
					$unit_id=$get_pro_type_rel['product_base_unit'];
				}
				$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,'','',$stock_id);

				$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$stock_id,$stock_id);


				$stock=$gstock-$rstock;
				echo $stock;
				
			}
			else{
				echo 0;
			}
			
		}else if(strtolower($POST['mode'])=="load_batch_no")
		{
			$query="select i.*,(IFNULL(sum(base_stock),0)-IFNULL(sum(used_base_stock),0)) as pending_base_stock,(IFNULL(sum(convert_stock),0)-IFNULL(sum(used_convert_stock),0)) as pending_conv_stock,group_concat(i.stock_id) as b_stock_id from tbl_stock_trn as i
			where stock_status=0 and product_id = ".$POST['product_id']." and batch_no != '' group by batch_no";
			$rs_batch=$dbcon->query($query);
			$str= '<option value="">Choose Batch No</option>';
			while($rel=brp_mysqli_fetch_assoc($rs_batch))
			{	
				// if($rel['pending_base_stock'] > 0){
					$str.= '<option value="'.$rel['b_stock_id'].'" data-stock="'.$rel['base_stock'].'" >'.$rel['batch_no'].'</option>';
				// }
			}
			echo $str;
		}