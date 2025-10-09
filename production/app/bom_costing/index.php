<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	PRODUCTION_BOM_COSTING_LIST_SLUG_VIEW,PRODUCTION_BOM_COSTING_LIST_SLUG_CREATE,PRODUCTION_BOM_COSTING_LIST_SLUG_UPDATE,PRODUCTION_BOM_COSTING_LIST_SLUG_DELETE
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
if(brp_strtolower($POST['mode']) == "load_product_version") {
	$product_id = $POST['product_id'];
	
	$return_product_version = get_bom_productversion($dbcon,$product_id,"");

	echo $return_product_version;
}else if(brp_strtolower($POST['mode']) == "load_bom_costing_no") {
	$costing_no = load_common_no($dbcon,COSTING_NO);

	echo $costing_no;
}
else if(brp_strtolower($POST['mode']) == "add") {
	
	$info['costing_no']		= $POST['costing_no'];
	
	$info['costing_date']	= date('Y-m-d');
	$info['product_id']		= $POST['product_id'];
	$info['qty']	= $POST['qty'];
	$info['bom_id']	= $POST['bom_id'];
	
	$info['purchase_rate']	= $POST['purchase_rate'];
	$info['template_id']	= $POST['template_id'];
	$info['bom_version_id']			= $POST['bom_version_id'];
	$info['cdate']				= date("Y-m-d");
	$info['user_id']			= $_SESSION['user_id'];
	$info['company_id']			= $_SESSION['company_id'];

	$insert_id=add_record('tbl_bom_costing', $info, $dbcon);
	
	if($insert_id){
		update_common_no($dbcon,COSTING_NO);
		$log_entry=common_log_entry($dbcon,"bom_costing_add",1,"tbl_bom_costing",$insert_id);
		$arr['msg'] = '1';
		$arr['bom_costing_id'] = $insert_id;

		add_bom_costing_trn($dbcon,$insert_id,$POST);

	}else{
		$arr['msg'] = '0';
	}
	echo json_encode($arr);
}
else if(brp_strtolower($POST['mode']) == "get_bom_details") {
	$product_id=$POST['product_id'];
	$bom_version_id = $POST['bom_version_id'];
	$query="select bom_id,product_base_qty from tbl_bom where bom_status!=2 and bom_product=".$product_id." and  bom_version_id = ". $bom_version_id ." and company_id=".$_SESSION['company_id'];
	
	$result=$dbcon->query($query);
	$row=brp_mysqli_fetch_assoc($result);
	$arr['qty'] = $row['product_base_qty'];
	$arr['bom_id'] = $row['bom_id'];	
	echo json_encode($arr);
}else if(brp_strtolower($POST['mode']) == "generate_costing_report") {
	$bom_costing_id = $POST['bom_costing_id'];
	
	$bom_costing_template_id = 0;
	$query = "SELECT trn.*,bom.template_id,pro.product_name,buni.unit_name as base_unit_name,cuni.unit_name as conv_unit_name FROM tbl_bom_costing_trn AS trn LEFT JOIN product_mst as pro ON pro.product_id = trn.product_id 
		left join unit_mst as buni on buni.unitid = trn.base_unit
		left join unit_mst as cuni on cuni.unitid = trn.conv_unit
		left join tbl_bom_costing as bom on bom.bom_costing_id = trn.bom_costing_id
		WHERE trn.status = 0 and trn.bom_costing_id ='".$bom_costing_id."'";
	$result=$dbcon->query($query);

	$str = "";
	if(brp_mysqli_num_rows($result) > 0){
		$str .= '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 14px;font-weight: bold;">';
		$str .='<thead>
					<tr>
						<th class="text-center" style="width:5%;"><strong>SR.NO.</strong></th>
						<th class="text-center" style="width:25%;"><strong>PRODUCT NAME</strong></th>
						<th class="text-center" style="width:10%;"><strong>QTY</strong></th>
						<th class="text-center" style="width:40%;"><strong>PROCESS</strong></th>
						<th class="text-center" style="width:10%;"><strong>RAW MATERIAL RATE</strong></th>
						<th class="text-center" style="width:10%;"><strong>ACTION</strong></th>
					</tr>	
				</thead>';
		$total_rm_rate = 0;
		$total_process_rate = 0;
		$costing_rate = 0;
		while($row=brp_mysqli_fetch_assoc($result)){
			$bom_costing_template_id = $row['template_id'];
			$having_process =  check_having_process($dbcon,$row['bom_costing_trn_id']);
			$unit_name = "";
			$qty = 0;
			$rate = "";
			$total_rate = "";
			$input_rate = "";
			$btn_action = "";
			if($having_process){
				$qty = $row['base_qty'];
				$unit_name = $row['base_unit_name'];
				$rate = 0;
				$total_rate = 0;	
				$btn_action = '<button class="btn btn-primary" onClick="update_process_rate('.$row['bom_costing_trn_id'].','.$bom_costing_id.')">Update Rate</button>';
			}else{
				$qty = $row['conv_qty'];
				$unit_name = $row['conv_unit_name'];
				$rate = $row['conv_rate'];
				$total_rate = $row['total_conv_rate'];
				$input_rate = '<input type="number" class="form-control rm_rate" onkeydown="return numericonly(event)" id="txt_rm_'.$row['bom_costing_trn_id'].'" value="'.$total_rate.'">';
				$btn_action = '<button class="btn btn-primary" onClick="update_rm_rate('.$row['bom_costing_trn_id'].','.$bom_costing_id.')">Update Rate</button>';
			}

			$total_rm_rate = $total_rm_rate +   $total_rate;

			if($having_process){
				$rate = "";
			}
			$sr_no = (empty($row['sr_no'])) ? '0' : $row['sr_no'];
			$str .='<tr>';
			$str .= '<td>'.$sr_no.'</td>';
			$str .= '<td>'.$row['product_name'].'</td>';
			$str .= '<td  class="text-right">'.$qty.' <span style="color:green;"> ' . $unit_name . '</span></td>';
			$str .= '<td>';

			$query1 = "SELECT trn.*,pr.process_name FROM tbl_bom_costing_process AS trn 
					LEFT JOIN process_mst as pr ON pr.process_id = trn.process_id
					WHERE trn.status = 0 and trn.bom_costing_trn_id =" .$row['bom_costing_trn_id'] . " order by trn.priority ASC";

			$result1=$dbcon->query($query1);

			if(brp_mysqli_num_rows($result1) > 0){

				$str .= '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
				$str .='<thead>
							<tr>
								<th class="text-center" style="width:5%;"><strong>PRIORITY</strong></th>
								<th class="text-center" style="width:40%;"><strong>PROCESS NAME</strong></th>
								<th class="text-center" style="width:20%;"><strong>PROCESS TYPE</strong></th>
								<th class="text-center" style="width:15%;"><strong>RATE</strong></th>
								<th class="text-center" style="width:25%;"><strong>TOTAL RATE</strong></th>
							</tr>	
						</thead>';
				while($row1=brp_mysqli_fetch_assoc($result1)){
					$input_pro_rate = '<input type="number" class="form-control process_rate_'.$row['bom_costing_trn_id'].'" data-process-id = "'.$row1['process_id'].'" data-bom_costing_process_id = "'.$row1['bom_costing_process_id'].'" onkeydown="return numericonly(event)" id="txt_pro_rate_'.$row['bom_costing_process_id'].'" value="'.$row1['total_rate'].'">';
					$type = "";
					if($row1['process_type'] == '1'){
						$type = "INHOUSE";
					}else{
						$type = "OUTSIDE";
					}
					$str .='<tr>';
					$str .= '<td>'.$row1['priority'].'</td>';
					$str .= '<td>'.$row1['process_name'].'</td>';
					$str .= '<td>'.$type.'</td>';
					$str .= '<td class="text-right">'.$row1['rate'].'</td>';
					$str .= '<td class="text-right"> '.$input_pro_rate.'</td>';
					$str .='</tr>';

					$total_process_rate = $total_process_rate + ($row1['total_rate']);
				}

				$str .= "</table>";
			}

			$str .='</td>';
			$str .= '<td class="text-right"> '.$input_rate .' </td>';
			$str .= '<td>'.$btn_action.'</td>';
			$str .='</tr>';
		
		}		
		$costing_rate = $total_rm_rate + $total_process_rate;

		$str.="<tr>";
		$str.="<td class='text-right' colspan='5'>Total Process Rate</td>";
		$str.="<td>".$total_process_rate."</td>";
		$str.="</tr>";
		$str.="<tr>";
		$str.="<td colspan='5' class='text-right'>Total Raw Material Rate</td>";
		$str.="<td>".$total_rm_rate."</td>";
		$str.="</tr>";
		$str.="<tr>";
		$str.="<td colspan='5' class='text-right'>Costing Rate</td>";
		$str.="<td id='costing_rate'>".$costing_rate."</td>";
		$str.="</tr>";

		$grand_total = $costing_rate;
		 
		$str .= "</table>";
		$arr= load_template_row_data($dbcon,$bom_costing_template_id,$costing_rate,$bom_costing_id);
		$str .= $arr['str'];
		$grand_total = $arr['grand_total'];

		$update_costing['total_process_rate'] = $total_process_rate;
		$update_costing['total_raw_material_rate'] = $total_rm_rate;
		$update_costing['total_costing_rate'] = $grand_total;

		$updateid11=update_record('tbl_bom_costing', $update_costing, "bom_costing_id=".$bom_costing_id, $dbcon);

	}
	echo $str;
}else if(brp_strtolower($POST['mode']) == "load_bom_costing_template") {

	$query = "select bom_costing_template_id,template_name from tbl_bom_costing_template where status = 0";
	$rs = $dbcon->query($query);
	$str = '<option value="">Select Costing Templete</option>';
	while ($rel = brp_mysqli_fetch_assoc($rs)) {
		$sel = '';
		/*if($rel['branch_id']==$eid){
			$sel='selected="selected"';
		}*/
		$str .= '<option ' . $sel . ' value="' . $rel['bom_costing_template_id'] . '">' . $rel['template_name'] . '</option>';
	}
	echo $str;
}
else if(brp_strtolower($POST['mode']) == "load_bom_costing_template_data") {
	$qry1 = "select trn.*,tmp.template_name,tmp.bom_costing_template_id from  tbl_bom_costing_template_trn as trn left join tbl_bom_costing_template tmp ON trn.bom_costing_template_id = tmp.bom_costing_template_id  where trn.status = 0 and trn.bom_costing_template_id = ".$POST['template_id']; 
		$rs1=$dbcon->query($qry1);
		$x = 0;
		$costing_rate = $POST['costing_rate'];
		$grand_total = $POST['costing_rate'];
		if(brp_mysqli_num_rows($rs1) > 0){
			$arr_str = load_template_details($dbcon,$rs1,$costing_rate,$grand_total);
			$str .= $arr_str['str'];
			$grand_total = $arr_str['grand_total'];
		}
		
	$str .=get_grandtotal_costing($dbcon,$grand_total);
	echo $str;
}
else if(brp_strtolower($POST['mode']) == "update_rm_rate") {

	$bom_costing_trn_id = $POST['bom_costing_trn_id'];

	$pro_qry = "select * from  tbl_bom_costing_trn where bom_costing_trn_id = " . $bom_costing_trn_id; 
	$pro_rs=$dbcon->query($pro_qry);
	$pro_row = brp_mysqli_fetch_array($pro_rs);
	
	
	$base_qty = $pro_row['base_qty'];
	$conv_qty = $pro_row['conv_qty'];

	$total_conv_rate = $POST['total_rate'];


	$conv_rate = $total_conv_rate / $conv_qty;

	$info['conv_rate'] = $conv_rate;
	$info['total_conv_rate'] = $total_conv_rate;

	$base_rate = convert_rate($dbcon,$conv_rate,$pro_row['product_id'],"base_unit");

	$info['base_rate'] = $base_rate;
	$info['total_base_rate'] = $base_rate * $base_qty;

	$updateid11=update_record('tbl_bom_costing_trn', $info, "bom_costing_trn_id=".$bom_costing_trn_id, $dbcon);

}

else if(brp_strtolower($POST['mode']) == "update_process_rate") {
	$bom_costing_trn_id = $POST['bom_costing_trn_id'];
	$process_rate = $POST['total_rate'];
	$arr_bom_costing_process_id = $POST['bom_costing_process_id'];

	$pro_qry = "select * from  tbl_bom_costing_trn where bom_costing_trn_id = " . $bom_costing_trn_id; 
	$pro_rs=$dbcon->query($pro_qry);
	$pro_row = brp_mysqli_fetch_array($pro_rs);

	$base_qty = $pro_row['base_qty'];
	
	for($i=0; $i < count($arr_bom_costing_process_id); $i++) {
		$total_rate = 0;

		if(!empty($process_rate[$i])){
			$total_rate = $process_rate[$i];
			$bom_costing_process_id = $arr_bom_costing_process_id[$i];
			$info['rate'] = $total_rate / $base_qty;
 			$info['total_rate'] = $total_rate;
 			$updateid11=update_record('tbl_bom_costing_process', $info, "bom_costing_process_id=".$bom_costing_process_id, $dbcon);
		}
	}
}
else if(brp_strtolower($POST['mode']) == "save_costing_data") {
	$bom_costing_id  =  $POST['bom_costing_id'];
	$grand_total = $POST['grand_total'];
	$arr_temp_name = $POST['temp_name'];
	$arr_value  =  $POST['value'];
	$arr_type = $POST['type'];  // 1 - plus , 2 - minus
	$arr_formula  =  $POST['formula'];  //  1- per, 2 - amount 
	$bom_costing_template_id = $POST['bom_costing_template_id'];

	$update_status['status'] = '2';
	$updateid11=update_record('tbl_bom_costing_extra_rate', $update_status, "bom_costing_id=".$bom_costing_id, $dbcon);	
	for($i=0;$i<count($arr_temp_name);$i++){
		
		$info['bom_costing_id'] = $bom_costing_id;
		$info['type_name'] = $arr_temp_name[$i];
		if($arr_formula[$i] == '1'){
			$info['per'] = $arr_value[$i];
			$info['amount'] ='0';
		}else{
			$info['per'] = '0';
			$info['amount'] = $arr_value[$i];	
		}
		
		$info['type'] = $arr_type[$i];
		$info['status'] = 0;
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];

		$reqinserid=add_record('tbl_bom_costing_extra_rate', $info, $dbcon);
	}
	
	$update_costing['total_costing_rate'] = $grand_total;
	$update_costing['template_id'] = $bom_costing_template_id;
	$updateid11=update_record('tbl_bom_costing', $update_costing, "bom_costing_id=".$bom_costing_id, $dbcon);
}
else if(brp_strtolower($POST['mode']) == "fetch") {
	// $s_date=explode(' - ',$POST['date']);
	// $_SESSION['start']=$s_date[0];
	// $_SESSION['end']=$s_date[1];
	$branch=$_SESSION['branch_id'];

	$where='';
	
	$appData = array();
	$i=1;
	$aColumns = array('cost.bom_costing_id','cost.costing_no','cost.costing_date','cost.product_id','product.product_name','cost.status','prover.version_name','cost.qty','cost.total_costing_rate');
	$sIndexColumn = "bom_id";
	$isWhere = array("cost.status=0 and cost.company_id = ".$_SESSION['company_id']);
	$sTable = "tbl_bom_costing as cost";			
	$isJOIN = array('left join product_mst as product on product.product_id=cost.product_id left join pro_ms_bom_version as prover on prover.bom_version_id=cost.bom_version_id');
		//$hGroupby = array("bom.bom_product");
	$hOrder = "cost.bom_costing_id desc";
	$having_clause='';
	include($include.'pagging.php');
	$appData = array();

	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$costing_date=date('d-m-Y',strtotime($row['costing_date']));
		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$row["sr"].'</a>';

		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$row["costing_no"].'</a>';

		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$costing_date.'</a>';

		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$row["product_name"].'</a>';

		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$row["version_name"].'</a>';
		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$row["qty"].'</a>';
		$row_data[] = '<a class="" data-original-title="Edit '.$row["costing_no"].'" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'">'.$row["total_costing_rate"].'</a>';
		

		$delete='';$edit='';

				
				if(in_array(PRODUCTION_BOM_COSTING_LIST_SLUG_DELETE,$bulkAccessArray)){
				 $delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bom_costing('.$row['bom_costing_id'].')"><i class="fa fa-trash-o"></i></button>';
				}
				
				if(in_array(PRODUCTION_BOM_COSTING_LIST_SLUG_UPDATE,$bulkAccessArray)){
					$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.PRODUCTION_ROOT.'bom_costing_edit/'.$row['bom_costing_id'].'"><i class="fa fa-pencil"></i></a>';
				}
				
				
				$row_data[] = $edit.' '.$delete;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}else if(brp_strtolower($POST['mode']) == "delete") {

				$info['status']			= 2;
				$updateestimateid=update_record('tbl_bom_costing', $info, "bom_costing_id=".$POST['bom_costing_id'], $dbcon);	
				
				if($updateestimateid){
					echo "1";	
				}else{
					echo "0";
				}
			
			$log_entry=common_log_entry($dbcon,"bom_add",3,"tbl_bom",$POST['eid']);
		}

function add_bom_costing_trn($dbcon,$bom_costing_id,$post_data){
	$pro_qry = "select * from  product_mst where product_id = " . $_POST['product_id']; 
	$pro_rs=$dbcon->query($pro_qry);
	$pro_row = brp_mysqli_fetch_array($pro_rs);
		
	$info2['bom_costing_id']	= $bom_costing_id;
	$info2['sr_no']				= 0;
	$info2['product_id']		= $post_data['product_id'];;
	

	$base_qty = $post_data['qty'];

	$conv_qty = convert_stock($dbcon,$base_qty,$post_data['product_id'],"conv_unit");
	$info2['base_qty']			= $base_qty;
	$info2['conv_qty']			= $conv_qty;
	$info2['base_unit']			= $pro_row['product_base_unit'];
	$info2['conv_unit']			= $pro_row['product_conv_unit'];

	$base_rate = get_costing_rate($dbcon,$post_data['purchase_rate'],$post_data['product_id'],$pro_row['product_base_unit']);
	$conv_rate = convert_rate($dbcon,$base_rate,$post_data['product_id'],"conv_unit");
	
	$total_base_rate = (float)$base_qty * (float)$base_rate;
	$total_conv_rate = (float)$conv_qty * (float)$conv_rate;


	$info2['base_rate']			= $base_rate;
	$info2['conv_rate']			= $conv_rate;
	$info2['total_base_rate']	= $total_base_rate;
	$info2['total_conv_rate']	= $total_conv_rate;
	$info2['parent_id']			= 0;
	$info2['cdate']				= date("Y-m-d");
	$info2['user_id']			= $_SESSION['user_id'];
	$info2['company_id']		= $_SESSION['company_id'];
	
	$table='tbl_bom_costing_trn';	
	$reqinserid=add_record($table, $info2, $dbcon);
	$pqty = $post_data['qty'];
	$workorder_query_pro="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and pro_bom_process.process_status = 0 and prover.bom_version_id='".$post_data['bom_version_id']."' AND bom.bom_product='".$post_data['product_id']."' and bom.bom_id =" .$post_data['bom_id']; 

	$workorder_query_result = $dbcon->query($workorder_query_pro);
	
	if(brp_mysqli_num_rows($workorder_query_result)>0){
		while($wproduct_process=brp_mysqli_fetch_assoc($workorder_query_result))
		{
			$wwpp_info['bom_costing_trn_id'] = 	$reqinserid;
			$wwpp_info['process_id'] = 	$wproduct_process['process_id'];	
			$wwpp_info['product_id'] = $post_data['product_id'];		
			$wwpp_info['process_type'] = 	$wproduct_process['process_type'];
			$wwpp_info['priority'] = 	$wproduct_process['priority'];
			$wwpp_info['rate'] = 	$wproduct_process['process_rate'];
			$wwpp_info['total_rate'] = (float)$base_qty * (float)$wproduct_process['process_rate'];
			
			$wwpp_info['cdate']				= date("Y-m-d H:i:s");
			$wwpp_info['user_id']			= $_SESSION['user_id'];
			$wwpp_info['company_id']			= $_SESSION['company_id'];
			
			$inserestimateid=add_record('tbl_bom_costing_process', $wwpp_info, $dbcon);
		}
	}		

	$bom_process="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			WHERE  bom_id=".$post_data['bom_id'];	
			$bom_rel=brp_mysqli_fetch_assoc($dbcon->query($bom_process));
	
	$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
	left join product_mst as pro on pro.product_id=bom_trn.product_id
	left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
	left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
	where bom_trn_status=0 and bom_id=".$post_data['bom_id'];	
	$result1=$dbcon->query($query1);
	$call=1;$space="";
	$i = 1;
	while($rel1=brp_mysqli_fetch_assoc($result1)){  
		
		/*$base_one_qty=$rel1['product_base_qty'];
		$conv_one_qty=$rel1['product_conv_qty'];
		// $base_qty=$base_one_qty*$info2['base_qty'];
		$base_qty=$base_one_qty;
		$conv_qty=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/

		$base_one_qty=$rel1['product_base_qty']/$bom_rel['product_base_qty'];
				$conv_one_qty=$rel1['product_conv_qty']/$bom_rel['product_conv_qty'];
		$base_qty=$base_one_qty*$info2['base_qty'];
		
		$conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

		$info_sub['bom_costing_id']	= $bom_costing_id;
		$info_sub['sr_no']				= $i;
		$info_sub['product_id']		= $rel1['product_id'];;
		$info_sub['base_qty']			= $base_qty;
		$info_sub['conv_qty']			= $conv_qty*$conv_one_qty;
		$info_sub['base_unit']			= $rel1['product_base_unit'];
		$info_sub['conv_unit']			= $rel1['product_conv_unit'];

		$base_rate = get_costing_rate($dbcon,$post_data['purchase_rate'],$rel1['product_id'],$rel1['product_base_unit']);
		$conv_rate = convert_rate($dbcon,$base_rate,$rel1['product_id'],"conv_unit");
		
		$info_sub['base_rate']			= $base_rate;
		$info_sub['conv_rate']			= $conv_rate;

		$info_sub['total_base_rate']	= $base_rate * $base_qty;
		$info_sub['total_conv_rate']	= $conv_rate * $conv_qty;
		
		$info_sub['parent_id']			= $reqinserid;
		$info_sub['cdate']				= date("Y-m-d");
		$info_sub['user_id']			= $_SESSION['user_id'];
		$info_sub['company_id']		= $_SESSION['company_id'];	
		
		$inserid_sub=add_record('tbl_bom_costing_trn', $info_sub, $dbcon);
		
		$query_pro1="SELECT * FROM `tbl_bom` as bom
		left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
		left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
		left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
		WHERE tbl_product_process.status = 0 and  bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id =" . $rel1['p_bom_id']; 
		
		$rel_pro1 = $dbcon->query($query_pro1);
		
		if(brp_mysqli_num_rows($rel_pro1)>0)
		{
			while($product_process_row=brp_mysqli_fetch_assoc($rel_pro1))
			{
				$wpp_info['bom_costing_trn_id'] = 	$inserid_sub;
				$wpp_info['process_id'] = 	$product_process_row['process_id'];	
				$wpp_info['product_id'] = $product_process_row['product_id'];		
				$wpp_info['process_type'] = 	$product_process_row['process_type'];
				$wpp_info['priority'] = 	$product_process_row['priority'];
				$wpp_info['rate'] =  $product_process_row['process_rate'];
				$wpp_info['total_rate'] =  (float)$base_qty * (float)$product_process_row['process_rate'];
				
				$wpp_info['cdate']				= date("Y-m-d H:i:s");
				$wpp_info['user_id']			= $_SESSION['user_id'];
				$wpp_info['company_id']			= $_SESSION['company_id'];
				
				$inserestimateid=add_record('tbl_bom_costing_process', $wpp_info, $dbcon);
			}
		}	
		
		bom_child_tree($dbcon,$rel1['p_bom_id'],$bom_costing_id,$inserid_sub,$i,$base_qty,$post_data['bom_version_id'],$post_data);
		
		$i++;
	}	
				
}


function bom_child_tree($dbcon,$bom_id,$bom_costing_id,$bom_costing_trn_id,$num,$qty,$bom_version_id,$post_data)
	{
		
		$query_m="select * from tbl_bom as bom where bom_status=0 and bom_id=".$bom_id;
		$result_m=$dbcon->query($query_m);
		$rel_m=mysqli_fetch_assoc($result_m);	
		
		
		$query1="select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,bom_trn.product_id from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=".$bom_id;	
		$result1=$dbcon->query($query1);
		
		
		$k=1;
		$call=1;$space="";
		while($rel1=brp_mysqli_fetch_assoc($result1)){ 
			
			$sr_no = $num.'.'.$k; 

			$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$conv_one_qty=$rel1['product_conv_qty']/$rel_m['product_conv_qty'];
			// $conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
			$base_qty=$base_one_qty*$qty;
			
			/*$base_one_qty=$rel1['product_base_qty']/$rel_m['product_base_qty'];
			$conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");
			// $base_qty=$base_one_qty*$qty;
			$base_qty=$base_one_qty;*/
			$conv_qty=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");

			$info_sub['bom_costing_id']	= $bom_costing_id;
			$info_sub['sr_no']				= $sr_no;
			$info_sub['product_id']		= $rel1['product_id'];
			$info_sub['base_qty']			= $base_qty;
			$info_sub['conv_qty']			= $conv_qty;
			$info_sub['base_unit']			= $rel1['product_base_unit'];
			$info_sub['conv_unit']			= $rel1['product_conv_unit'];
			$base_rate = get_costing_rate($dbcon,$post_data['purchase_rate'],$rel1['product_id'],$rel1['product_base_unit']);
			$conv_rate = convert_rate($dbcon,$base_rate,$rel1['product_id'],"conv_unit");

			$info_sub['base_rate']			= $base_rate;
			$info_sub['conv_rate']			= $conv_rate;
			$info_sub['total_base_rate']	= $base_rate * $base_qty;
			$info_sub['total_conv_rate']	= $conv_rate * $conv_qty;
			$info_sub['parent_id']			= $bom_costing_trn_id;
			$info_sub['cdate']				= date("Y-m-d");
			$info_sub['user_id']			= $_SESSION['user_id'];
			$info_sub['company_id']		= $_SESSION['company_id'];
			
			$inserid_sub=add_record('tbl_bom_costing_trn', $info_sub, $dbcon);
			
		$query_pro1="SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and   bom.bom_product='".$rel1['product_id']."' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id = " . $rel1['p_bom_id']; 
			
			$rel_pro1 = $dbcon->query($query_pro1);
			
			if(brp_mysqli_num_rows($rel_pro1)>0)
			{
				while($product_process1=brp_mysqli_fetch_assoc($rel_pro1))
				{
					$wwpp_info['bom_costing_trn_id'] = 	$inserid_sub;
					$wwpp_info['process_id'] = 	$product_process1['process_id'];	
					$wwpp_info['product_id'] = $rel1['product_id'];		
					$wwpp_info['process_type'] = 	$product_process1['process_type'];
					$wwpp_info['priority'] = 	$product_process1['priority'];
					$wwpp_info['rate'] =  $product_process1['process_rate'];
					$wwpp_info['total_rate'] =  (float)$base_qty * (float)$product_process1['process_rate'];
					
					$wwpp_info['cdate']				= date("Y-m-d H:i:s");
					$wwpp_info['user_id']			= $_SESSION['user_id'];
					$wwpp_info['company_id']			= $_SESSION['company_id'];
					
					$inserestimateid=add_record('tbl_bom_costing_process', $wwpp_info, $dbcon);
				}
			}
			bom_child_tree($dbcon,$rel1['p_bom_id'],$bom_costing_id,$inserid_sub,$sr_no,$info_sub['base_qty'],$bom_version_id,$post_data);
			$k++;	
		}
		
	}


	function get_costing_rate($dbcon,$rate_type,$product_id,$base_unit){  // RATE TYPE : 1 - last purchase rate, 2 average rate, 3 - last po rate, 4- purchase card rate, 5 - opening first, 6- opening last , 7 - opening avg
		//var_dump($rate_type);
		$financial_start_date = $_SESSION['financial_start_date'];
		$financial_end_date = $_SESSION['financial_end_date'];

		if($rate_type == '1'){ // RATE TYPE : 1 - last purchase rate   (purchase bill ma last add thyu hoy eno rate)
			$qry = "select product_rate as rate,rate_unit from tbl_potrancation  where potrancation_status = 0 and product_id = " . $product_id . " order by potrancation_id desc limit 1"; 
		}
		if($rate_type == '2'){  // RATE TYPE : 2 - average rate  (financial yr tbl_stock ma avrage rate   sum(rate)/counter)
			$qry = "select AVG(base_rate) as rate,base_unit as rate_unit from tbl_stock_trn where stock_flage = 1 and stock_status = 0 and product_id = " . $product_id . " and stock_date >='". $financial_start_date . "' and stock_date <='" .$financial_end_date ."'"; 
		}
		if($rate_type == '3'){  // RATE TYPE : 3 -  last po rate  ( purchase_order last rate)
			$qry = "select product_rate as rate,rate_unit from tbl_purchaseordertrn where purchaseordertrn_status = 0 and product_id = " . $product_id . " order by purchaseordertrn_id desc limit 1"; 
		}
		if($rate_type == '4'){  // RATE TYPE : 4 - purchase card rate   ( purchase card ma last rate )
			$qry = "select price as rate,unit_id as rate_unit from tbl_purchasecardtrn  where purchasecardtrn_status = 0 and product_id = " . $product_id . " order by purchasecardtrn_id desc limit 1"; 
		}
		if($rate_type == '5'){  // RATE TYPE : 4 - purchase card rate   ( opening stock first )
			$qry = "select base_rate as rate,opening_stock_unit as rate_unit from opening_stock_mst  where status = 0 and approve_status = 1 and product_id = " . $product_id . " order by opening_stock_id	limit 1"; 
		}

		if($rate_type == '6'){  // RATE TYPE : 4 - purchase card rate   ( opening stock first )
			$qry = "select base_rate as rate,opening_stock_unit as rate_unit from opening_stock_mst  where status = 0 and approve_status = 1 and product_id = " . $product_id . " order by opening_stock_id desc limit 1"; 
		}
		
		if($rate_type == '7'){  // RATE TYPE : 4 - purchase card rate   ( opening stock AVG )
			$qry = "select  AVG(base_rate) as rate,opening_stock_unit as rate_unit from opening_stock_mst  where status = 0 and approve_status = 1 and product_id = " . $product_id . " and cdate >='". $financial_start_date . "' and cdate <='" .$financial_end_date ."'"; 
		
		}
		$rs=$dbcon->query($qry);
		$row = brp_mysqli_fetch_array($rs);

		$rate = 0;

		if($base_unit != $row['rate_unit']){
			$rate = convert_rate($dbcon,$row['rate'],$product_id,"base_unit");
		}else{
			$rate = $row['rate'];
		}

		if(empty($rate)){
			$rate = 0;
		}
		return $rate;

	}

	function get_costing_process_list($dbcon,$bom_costing_trn_id){
		$query = "SELECT trn.*,pr.process_name FROM tbl_bom_costing_process AS trn 
					LEFT JOIN process_mst as pr ON pr.process_id = trn.process_id
					WHERE trn.status = 0 and trn.bom_costing_trn_id =" .$bom_costing_trn_id . " order by trn.priority ASC";

		$result=$dbcon->query($query);

		$str1 = "";
		if(brp_mysqli_num_rows($result) > 0){

			$str1 .= '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
			$str1 .='<thead>
						<tr>
							<th class="text-center" style="width:5%;"><strong>PRIORITY</strong></th>
							<th class="text-center" style="width:50%;"><strong>PROCESS NAME</strong></th>
							<th class="text-center" style="width:30%;"><strong>PROCESS TYPE</strong></th>
							<th class="text-center" style="width:15%;"><strong>RATE</strong></th>
						</tr>	
					</thead>';
			while($row=brp_mysqli_fetch_assoc($result)){

				$type = "";
				if($row['process_type'] == '1'){
					$type = "INHOUSE";
				}else{
					$type = "OUTSIDE";
				}
				$str1 .='<tr>';
				$str1 .= '<td>'.$row['priority'].'</td>';
				$str1 .= '<td>'.$row['process_name'].'</td>';
				$str1 .= '<td>'.$type.'</td>';
				$str1 .= '<td class="text-right">'.$row['rate'].'</td>';
				$str1 .='</tr>';
			}

			$str1 .= "</table>";
		}
		return $str1;
	}
	  function check_having_process($dbcon,$bom_costing_trn_id){
	  	$query = "SELECT * FROM tbl_bom_costing_process  
					WHERE status = 0 and bom_costing_trn_id =" .$bom_costing_trn_id;

		$result=$dbcon->query($query);

		
		if(brp_mysqli_num_rows($result) > 0){
			return '1';
		}else{
			return '0';
		}

	  }


	  function load_template_row_data($dbcon,$bom_costing_template_id,$costing_rate,$bom_costing_id){
	  	$grand_total = $costing_rate;
		
		$str = '<div class="col-md-6 col-md-offset-6" style="padding: 10px;">
							<div class="form-group">
								<label class="col-md-6 text-right control-label">Costing Template *</label>
								<div class="col-md-6 col-xs-11">
									<select class="select2" name="dyn_template_id" id="dyn_template_id" onchange="change_template(this.value,'.$costing_rate.','.$bom_costing_id.')">
										
										'.get_bom_costing_template($dbcon,$bom_costing_template_id).'
									</select>
								</div>
							</div>
						</div>
				<div id = "tbl_template_data">';
				$qry1 = "select * from  tbl_bom_costing_extra_rate where status =0 and bom_costing_id = ".$bom_costing_id; 
				$rs1=$dbcon->query($qry1);
				if(brp_mysqli_num_rows($rs1) == 0){
							 $qry1 = "select trn.*,tmp.template_name,tmp.bom_costing_template_id from  tbl_bom_costing_template_trn as trn left join tbl_bom_costing_template tmp ON trn.bom_costing_template_id = tmp.bom_costing_template_id  where trn.status = 0 and trn.bom_costing_template_id = ".$bom_costing_template_id; 
					$rs1=$dbcon->query($qry1);
				}
			$x = 0;
			if(brp_mysqli_num_rows($rs1) > 0){
				$arr_str = load_template_details($dbcon,$rs1,$costing_rate,$grand_total);
				$str .= $arr_str['str'];
				$grand_total = $arr_str['grand_total'];
			}
			$str .=get_grandtotal_costing($dbcon,$grand_total);
		$str .="</div>";
		
		$arr['str'] = $str;
		$arr['grand_total'] = $grand_total;
		return $arr;
	  }


	  function get_grandtotal_costing($dbcon,$grand_total){
	  	$str = '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
		$str.="<tr class='bg-info' style='font-size: 18px;font-weight: bold;'>";
		$str.="<td width='90%' class='text-right' id='total_costing_rate'>Total Costing Rate</td>";
		$str.="<td id='lbl_grand_total'>".$grand_total."</td>";
		$str.="</tr>";

		return $str;
	  }

function load_template_details($dbcon,$rs1,$costing_rate,$grand_total){
	$total_costing_value = $grand_total;
	$str = '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
	$i=1;
		while($row2 = brp_mysqli_fetch_array($rs1)){
			$str .="<tr id=''>";
			
	  	$str.="<td width='80%' class='text-right tmp_typename'>".$row2['type_name']."</td>";
			if($row2['type'] == '0') { // 0 - plus | 1 - minus
				$plus = 0;

				if(!empty($row2['per']) && $row2['per'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' id='input_rate_".$i."' class='form-control input_rate' data-cal-type='1' value='".$row2['per']."' onkeyup='calculate_rate(".$i.",".$costing_rate.",1)'>%</td>";
					$plus =  ($costing_rate * $row2['per']) / 100;
				}else if(!empty($row2['amount']) && $row2['amount'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='".$row2['amount']."'></td>";
					$plus = $row2['amount'];
				}else{
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='0'></td>";
					$plus = 0;
				}
				$str.="<td width='10%' data-operation='0' class='input_temp_rate' id='txt_tmp_total_".$i."' style='color:green'>".$plus."</td>";

				$total_costing_value = $total_costing_value + $plus;
				
			}else{
				$minus = 0;

				if(!empty($row2['per']) && $row2['per'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='1' onkeyup='calculate_rate(".$i.",".$costing_rate.",1)' value='".$row2['per']."'>%</td>";
					$minus =  ($costing_rate * $row2['per']) / 100;
				}else if(!empty($row2['amount']) && $row2['amount'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='".$row2['amount']."'></td>";
					$minus = $row2['amount'];
				}else{
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='0'></td>";
					$minus = 0;
				}
				$str.="<td width='10%' class='input_temp_rate' data-operation='1' id='txt_tmp_total_".$i."' style='color:red'>".$minus."</td>";

				$total_costing_value = $total_costing_value - $minus;

			}
			
			
			$str .="</tr>";
			$i++;
		}
		$str.="</table>";
		$arr['str'] = $str;
		$arr['grand_total'] = $total_costing_value;
		return $arr;
}