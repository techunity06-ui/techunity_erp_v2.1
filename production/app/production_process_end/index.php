<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		$company_config = getCompanyConfiguration($dbcon);	
		if(strtolower($POST['mode']) == "end_process") {
			// echo "<pre>";print_r($POST);die;
			$branch_id = $POST['branch_id'];
			$stop_qty=$POST['stop_qty'];
			$process_end_time_qc = $POST['process_end_time_qc'];


			if(isset($POST['total_actual_end_qty'])){
				$total_stop_qty = $POST['total_actual_end_qty'];
			}else if(isset($POST['total_stop_qty'])){
				$total_stop_qty=$POST['total_stop_qty'];
			}else{
				$total_stop_qty=$POST['stop_qty'];
			}
			

			$start_stop_user_id = $POST['start_stop_user_id'];

			$qry = "select * from product_mst where product_id = " . $POST['product_id'];
			$result=$dbcon->query($qry);
			$res=mysqli_fetch_assoc($result);

			$batch_man_no = $POST['batch_man_no'];
			
			$info_grn['grn_no']				= load_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);
			$info_grn['grn_date']			= date("Y-m-d");
			$info_grn['gir_no']				= "Inhouse";
			$info_grn['invoice_no']			= "Inhouse";
			$info_grn['challan_no']			= "Inhouse";
			$info_grn['ref_type']			= "3";
			$info_grn['vender_id']			= "-1";
			$info_grn['remark']				= $POST['remark'];
			$info_grn['resource_id']	= $POST['machine'];
			
			$info_grn['cdate']				= date("Y-m-d H:i:s");
			$info_grn['user_id']			= $_SESSION['user_id'];
			$info_grn['company_id']			= $_SESSION['company_id'];

			$arr_material_product_id = "";
			$arr_material_used_qty = "";
			$arr_material_pid = "";
			$arr_material_godown_action = "";
			$arr_material_godown_id = "";
			$arr_process_stock = "";
			$arr_rp_id = "";
			$arr_batch_wise_stock_manage = "";

			$arr_end_qty = "";
			if(isset($POST['actual_end_qty'])){
				$arr_end_qty = $POST['actual_end_qty'];
			}
			$pid_wise_end_qty = $POST['pid_wise_end_qty'];
			

			if(isset($POST['material_product_id'])){
					$arr_material_product_id = $POST['material_product_id'];
			}

			if(isset($POST['material_used_qty'])){
				$arr_material_used_qty = $POST['material_used_qty'];
			}	

			if(isset($POST['material_pid'])){
					$arr_material_pid = $POST['material_pid'];
			}
			if(isset($POST['material_godown_id'])){
					$arr_material_godown_id = $POST['material_godown_id'];
			}
			if(isset($POST['material_godown_action'])){
					$arr_material_godown_action = $POST['material_godown_action'];
			}
			if(isset($POST['process_stock'])){
					$arr_process_stock = $POST['process_stock'];
			}
			if(isset($POST['rp_id'])){
					$arr_rp_id = $POST['rp_id'];
			}
			if(isset($POST['batch_wise_stock_manage'])){
					$arr_batch_wise_stock_manage = $POST['batch_wise_stock_manage'];
			}
			
			$grn_id=add_record('tbl_grn',$info_grn, $dbcon,$branch_id);
			// die;
			if($grn_id){
				$type="conv_unit";
				$total_stop_conv_qty = convert_stock($dbcon,$total_stop_qty,$POST['product_id'],$type);

				
				update_series_no_using_type_id($dbcon,INHOUSE_GRN,$_SESSION['company_id'],$branch_id1);

				$upd_p_id = implode(",",$POST['pid']);
				if($company_config['resource_wise_production'] == '1'){
					$upd_machine['resource_id'] = $POST['machine'];
					$updatetrnid=update_record('tbl_allocate_process',$upd_machine,"p_id in(".$upd_p_id.")" , $dbcon);
				}


				grn_trn_and_sub_trn_entry($dbcon,$POST['product_id'],$grn_id,$total_stop_qty,$POST['product_base_unit'],$POST['process_id'],$POST['grn_godown'],$POST['p_id'],$branch_id,$POST['pid'],$pid_wise_end_qty,0,$total_stop_conv_qty,$res['product_conv_unit'],$info_grn['grn_no'],$POST['batch_no'],$batch_man_no,$start_stop_user_id,"3",$POST['product_base_unit'],$POST['product_scrap_id'],$POST['scrap_unit'],$POST['scrap_qty'],$POST['auto_store_relese'],$process_end_time_qc,$arr_material_product_id,$arr_material_pid,$arr_material_used_qty,$arr_material_godown_action,$arr_material_godown_id,$POST['qty_variation'],$POST['jobcard_close'],$arr_end_qty,$arr_process_stock,$arr_rp_id,$arr_batch_wise_stock_manage);

				// grn_trn_and_sub_trn_entry($dbcon,$POST['product_id'],$grn_id,$total_stop_qty,$POST['product_base_unit'],$POST['process_id'],$POST['grn_godown'],$POST['p_id'],$branch_id,$POST['pid'],$POST['pid_wise_end_qty'],0,$total_stop_conv_qty,$res['product_conv_unit'],$info_grn['grn_no'],$POST['batch_no'],$batch_man_no,$start_stop_user_id,"3",$POST['product_base_unit'],$POST['product_scrap_id'],$POST['scrap_unit'],$POST['scrap_qty'],$arr_material_product_id,$arr_material_pid,$arr_material_used_qty,$arr_material_godown_action,$arr_material_godown_id);

			
				echo "1";
			}else{
				echo "0";
			}
			
		}
		else if(strtolower($POST['mode']) == "get_scrap_unit") {
			$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
			left join unit_mst as umst on umst.unitid=promst.product_base_unit
			left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
			WHERE product_id=".$POST['scrap_product_id'];
			$rs_type1=$dbcon->query($query1);
			$row1=brp_mysqli_fetch_assoc($rs_type1);

			if($row1['product_base_unit']!=$row1['product_conv_unit']){
				$row1['unit_status']="1";
				$opt='<option  value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
        			$opt .='<option  value="'.$row1['product_conv_unit'].'">'.$row1['convert_unit_name'].'</option>';
			}else{
				$row1['unit_status']="0";
				$opt='<option value="'.$row1['product_base_unit'].'">'.$row1['base_unit_name'].'</option>';
			}
				//$row1['qye']=$query1;
				/*$row1['unit_option']=$opt;		
			echo json_encode($row1);*/
			echo $opt;
		}
		else if(strtolower($POST['mode']) == "show_material_list_new") {
			
			$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
				WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['p_id'].")";
			$resul=$dbcon->query($bom);
			$rel1=mysqli_fetch_assoc($resul);
			
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			
			$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
			left join product_mst as pro on pro.product_id=rpro.rp_pid
			left join tbl_category as tc on pro.product_category=tc.cat_id
			left join unit_mst as bunit on bunit.unitid=rpro.process_unit
			left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
			WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
			$result=$dbcon->query($bom1);
			$i=1;
			while($rel=mysqli_fetch_assoc($result)){
				$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
				$rel["req_qty_one"]=round($rel["req_qty_one"],6);
				$o_qty=round($o_qty,6);
				//$o_qty=round($rel["req_qty_one"],6);
				$total_req_qty=$POST['pending_qty']*$o_qty;
				$total_req_qty=round($total_req_qty,4);
				$used_qty=$POST['max_start_qty']*$o_qty;
				$used_qty=round($used_qty,4);
				$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id);
				
				$cur_stock=round($cur_stock,4);
				$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
				//$cur_stock=reserve_stock($dbcon,$rel_n1['rp_pid'],$rel_n1['process_unit'],"",$rel_n1['rp_id']);
				echo '<tr>
						<td>'.$rel["product_name"].'
							<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" />
						</td>
						<td>'.$cat_name.'</td>
						<td>'.$o_qty.'
							<input type="hidden" class="" name="row_req_qty_one[]" id="row_req_qty_one'.$i.'" value="'.$o_qty.'" />
						</td>
						<td>'.$total_req_qty.'</td>
						<td>'.$cur_stock.'</td>
						<td>'.$used_qty.'</td>
						<td>'.$rel["base_unit_name"].'
							<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id'.$i.'" value="'.$rel['process_unit'].'" />
						</td>
				</tr>
				';
				$i++;
			}
		}
		else if(strtolower($POST['mode']) == "show_material_list_new_23-6-21") {

			$branch_id=$POST['branch_id'];
			
			if($POST['pre_alloc_id']=="0"){
				$bom="SELECT group_concat(rpro.p_ref_id ORDER BY rpro.p_ref_id) AS views FROM `tbl_allocate_process` as rpro
					WHERE rpro.p_status!=2 AND rpro.p_id in (".$POST['eid'].")";
				$resul=$dbcon->query($bom);
				$rel1=mysqli_fetch_assoc($resul);
				
				$bom1="SELECT rpro.*,tc.cat_name,pro.product_name,pro.product_min_stock,pro.product_setting_check,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name FROM `tbl_request_product` as rpro
				left join product_mst as pro on pro.product_id=rpro.rp_pid
				left join tbl_category as tc on pro.product_category=tc.cat_id
				left join unit_mst as bunit on bunit.unitid=rpro.process_unit
				left join unit_mst as cunit on cunit.unitid=rpro.purchase_unit
				WHERE rpro.status!=2 AND rpro.perent_id in (".$rel1['views'].") group by rpro.rp_pid" ;
				$result=$dbcon->query($bom1);
				$i=1;
				while($rel=mysqli_fetch_assoc($result))
				{
					
					//$rel["req_qty_one"]=round($rel["req_qty_one"],6);
					$o_qty=convert_stock($dbcon,$rel["req_qty_one"],$rel['rp_pid'],"base_unit");
					$rel["req_qty_one"]=round($rel["req_qty_one"],6);
					$o_qty=round($o_qty,6);
					
					$total_req_qty=$POST['pending_qty']*$o_qty;
					$total_req_qty=round($total_req_qty,4);
					$used_qty=$POST['max_start_qty']*$o_qty;
					$used_qty=round($used_qty,4);
					
					$cur_stock=reserve_stock($dbcon,$rel['rp_pid'],$rel['process_unit'],"","","","",$branch_id);
					$cstock=get_current_stock_new($dbcon,$rel["rp_pid"],$rel["process_unit"]);
					
					$rstock=reserve_stock($dbcon,$rel["rp_pid"],$rel["process_unit"],"","","","",$branch_id);
					
					
					//var_dump($cstock);
					//var_dump($rstock);
					$actualstock=$cur_stock+($cstock-$rstock);
					$cat_name = ($rel['cat_name']!=null) ? $rel['cat_name'] : 'PRIMARY';
					//$used_qty=round($cur_stock,4);
					echo '<table class="display table table-bordered table-striped" id="material_details">
							<thead>
							  <tr>
								<th>Product Name</th>
								<th>Product Category</th>
								<th>Qty Needed For Single Piece</th>
								<th>Total Required Qty</th>
								<th>Total Available Qty </th>
								<th>Estimate Usable Qty</th>
								<th>Actual Usable Qty</th>
								<th>Unit</th>
							  </tr>
							</thead>
						<tbody>								
						
						<tr>
							<td>'.$rel["product_name"].'
								<input type="hidden" class="" name="row_product_id[]" id="row_product_id'.$i.'" value="'.$rel['rp_pid'].'" />
							</td>
							<td>'.$cat_name.'</td>
							<td>'.$o_qty.'
								<input type="hidden" class="" name="row_req_qty_one[]" id="row_req_qty_one'.$i.'" value="'.$o_qty.'" />
							</td>
							<td>'.$total_req_qty.'</td>
							<td>'.$cur_stock.'</td>
							<td>'.$used_qty.'
								<input type="hidden" class="" name="row_estimate_qty[]" id="row_estimate_qty'.$i.'" value="'.$used_qty.'" />
							</td>
							<td>
								<input type="number" class="form-control" name="row_actual_qty[]" id="row_actual_qty'.$i.'" value="'.$used_qty.'"  max="'.$actualstock.'" />
							</td>
							<td>'.$rel["base_unit_name"].'
								<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id'.$i.'" value="'.$rel['process_unit'].'" />
							</td>
					</tr>
					</tbody>
					</table>
					';
					$i++;
				}
			 }else{
				$pid=$POST['pid'];
				
				$bom1="SELECT group_concat(previous_process_id) as pre_id FROM `tbl_allocate_process` as rpro WHERE rpro.p_id in (".$pid.")" ;
				$result=$dbcon->query($bom1);
				$rel=mysqli_fetch_assoc($result);
				
				$bom22="SELECT sum(rpro.process_stock-rpro.process_used_stock) as tprocess_stock,pmst.product_name,tc.cat_name,uni.unit_name,promst.process_name,rpro.process_unit FROM `tbl_allocate_process` as rpro 
				left join product_mst as pmst on pmst.product_id=rpro.p_product_id
				left join tbl_category as tc on pmst.product_category=tc.cat_id
				left join unit_mst as uni on uni.unitid=rpro.process_unit
				left join process_mst as promst on promst.process_id=rpro.process_id
				WHERE rpro.p_id in (".$rel['pre_id'].")";
				$result22=$dbcon->query($bom22);
				$rel2=mysqli_fetch_assoc($result22);
				$i=1;
				$cat_name = ($rel2['cat_name']!=null) ? $rel2['cat_name'] : 'PRIMARY';
				echo '<table class="display table table-bordered table-striped" id="material_details">
							<thead>
							  <tr>
								<th>Product Name</th>
								<th>Product Category</th>
								<th>Total Required Qty</th>
								<th>Total Available Qty </th>
								<th>Estimate Usable Qty</th>
								<th>Actual Usable Qty</th>
								<th>Unit</th>
							  </tr>
							</thead>
						<tbody>	
						<tr>
							<td>'.$rel2["product_name"].' ('.$rel2["process_name"].')</td>
							<td>'.$cat_name.'</td>
							<td>'.$POST["pending_qty"].'</td>
							<td>'.$rel2["tprocess_stock"].' </td>
							<td>'.$POST["max_start_qty"].'
								<input type="hidden" class="" name="row_estimate_qty[]" id="row_estimate_qty" value="'.$POST["max_start_qty"].'" />
							</td>
							<td>
								<input type="number" class="form-control" name="row_actual_qty[]" id="row_actual_qty" value="'.$POST["max_start_qty"].'"  max="'.$POST["tprocess_stock"].'" />
							</td>
							<td>'.$rel2["unit_name"].'
								<input type="hidden" class="" name="row_unit_id[]" id="row_unit_id" value="'.$rel2['process_unit'].'" />
							</td> 
						</tr>'; 
			} 

		}else if(strtolower($POST['mode']) == "open_scrap_entry") {
			
			$query_pro="select product_scrap_id,scrap_qty,material_issue_weight from product_mst as trn where product_id=".$POST['product_id'];
			$rel_pro=brp_mysqli_fetch_assoc($dbcon->query($query_pro));
			
			$query11="select product_sale_rate from product_mst as trn where product_id=".$rel_pro['product_scrap_id'];
			$rel1=brp_mysqli_fetch_assoc($dbcon->query($query11));
			
			
			$query_pl="select process_scrap_tolerance_plus,process_scrap_tolerance_minus from tbl_product_process as trn where trn.status = 0 and  product_id=".$POST['product_id']." and process_id=".$POST['process_id'];
			$rel_pl=brp_mysqli_fetch_assoc($dbcon->query($query_pl));
			
			$id=$rel_pro['product_scrap_id'];
			$expected_scrap=$POST['qty']*$rel_pro['scrap_qty'];
			
			if($expected_scrap!=0){
				if($rel_pl['process_scrap_tolerance_plus']!="0"){
					$max_tol=(($expected_scrap*$rel_pl['process_scrap_tolerance_plus'])/100)+$expected_scrap;
				}else{
					$max_tol=$expected_scrap;
				}
			}else{
				$max_tol=0;
			}
		
			if($expected_scrap!=0){
				if($rel_pl['process_scrap_tolerance_plus']!="0"){
					$min_tol=(($expected_scrap*$rel_pl['process_scrap_tolerance_minus'])/100)-$expected_scrap;
				}else{
					$min_tol=$expected_scrap;
				}
			}else{
				$min_tol=0;
			}
			if($min_tol<0){
				$min_tol=0;
			}
			
			
			$str="";
			$str.="
					<div class='col-md-12' >
						<div class='col-md-6' >
							<div class='col-md-4'> Expected Scrap </div>
							<div class='col-md-8'>
								<input type='number' class='form-control' name='scrap_expected_qty' id='scrap_expected_qty' value='".$expected_scrap."' readonly />
							</div>
						</div>
						<div class='col-md-6' >
							<div class='col-md-4'>Rate</div>
							<div class='col-md-8'>
								<input type='number' class='form-control' name='scrap_rate' id='scrap_rate' value='".$rel1['product_sale_rate']."'  />
							</div>
						</div>
					</div>
					<div class='col-md-12' style='margin-top: 15px;'>
						<div class='col-md-6' >
							<div class='col-md-4'>Scrap Received</div>
							<div class='col-md-8'>
								<input type='number' class='form-control' name='scrap_received_qty' id='scrap_received_qty' value='' min='".$min_tol."' max='".$max_tol."'  />
							</div>
						</div>
						<div class='col-md-6' >
							<div class='col-md-4'>Scrap Code</div>
							<div class='col-md-8'>
								<select class='form-control' name='product_scrap_id' id='product_scrap_id' onchange='scrap_rate_change();' >
                                  ".getScrapCode($dbcon,$id)."
                                 </select>
							</div>
						</div>
					</div>
				<div class='col-md-12' style='margin-top: 15px;' >
					<center>
						<input type='button' id='scrap_save' name='scrap_save' class='btn btn-success' value='Save' onclick='scrap_save1();' />
					</center>
					<input type='hidden' name='sproduct' id='sproduct' value='".$POST['product_id']."' >
					<input type='hidden' name='sprocess' id='sprocess' value='".$POST['process_id']."' >
					<input type='hidden' name='sallo_id' id='sallo_id' value='".$POST['allo_id']."' >
					<input type='hidden' name='sbranch_id' id='sbranch_id' value='".$POST['branch_id']."' >
				</div>
			";
			echo $str;
		}else if(strtolower($POST['mode']) == "scrap_save") {
			$branch_id=$POST['sbranch_id'];
			$info2['alloc_id']				=$POST['sallo_id'];
			$info2['product_id']			=$POST['sproduct'];
			$info2['process_id']			=$POST['sprocess'];
			$info2['scrap_product_id']		=$POST['product_scrap_id'];
			$info2['qty']					=$POST['scrap_received_qty'];
			$info2['cdate']					= date("Y-m-d H:i:s");
			$info2['user_id']				= $_SESSION['user_id'];
			$info2['company_id']			= $_SESSION['company_id'];
			
			$tbl_grn_trn_id=add_record('tbl_scrap_add',$info2, $dbcon,$branch_id);
			
			$query11="select product_base_unit from product_mst as trn where product_id=".$info2['scrap_product_id'];
			$rel1=brp_mysqli_fetch_assoc($dbcon->query($query11));
			
			add_stock($dbcon,$info2['scrap_product_id'],$rel1['product_base_unit'],date("Y-m-d"),"process_end",$tbl_grn_trn_id,1,$info2['qty'],"1",$branch_id);
								
		}
		else if(strtolower($POST['mode'])== "scrap_rate_change") {
			//$row=array();
			$query1="select product_sale_rate from product_mst where product_id=".$POST['product_scrap_id'];
			$rows=brp_mysqli_fetch_assoc($dbcon->query($query1));			
			echo json_encode($rows);
		}
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>