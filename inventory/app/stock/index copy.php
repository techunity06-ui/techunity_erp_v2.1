<?php

session_start();
$AJAX = true;

include('../../include/urlfileinner.php');

$companyConfiguration=getCompanyConfiguration($dbcon);


$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	OPENING_STOCK_LIST_SLUG_VIEW,OPENING_STOCK_LIST_SLUG_CREATE,OPENING_STOCK_LIST_SLUG_UPDATE,OPENING_STOCK_LIST_SLUG_DELETE,OPENING_STOCK_LIST_APPROVE
]);							
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {

	$branch=$_SESSION['branch_id'];

	$where='';
	if($POST['product_type']!=''){
		$where.=" and product_type=".$POST['product_type'];
	}
	if($POST['product_id']!=''){
		$where.=" and product_id in (".$POST['product_id'] .")";
	}

	$appData = array();
	$i=1;
	$aColumns = array('pro.product_id','pro.product_name','pro.product_name','pro.product_name','pro.product_name','pro.product_name','pro.product_base_unit');
	$sIndexColumn = "pro.product_id";
	$isWhere = array("pro.product_status = 0".$where);
	$sTable = "product_mst as pro";			
		$isJOIN = array();
		$hOrder = "pro.product_id desc";
		$hGroupby = array("pro.product_id");
		include($include.'pagging.php');
		$appData = array();
		
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			
			$row_data[] = '<a class="" data-original-title="View'.$row["product_id"].'" data-toggle="tooltip" data-placement="top">'.$row["sr"].'</a>';

			
			$row_data[] = '<a class="" data-original-title="View '.$row["product_id"].'" data-toggle="tooltip" data-placement="top">'.$row["product_name"].'</a>';

			$query_used="select opening_stock_qty from opening_stock_mst as rpro
				where product_id=".$row["product_id"]." and company_id = '".$_SESSION['company_id']."' and status=0 ";
			$rel_used=mysqli_fetch_assoc($dbcon->query($query_used));	
			
			$cstock=get_current_stock_new($dbcon,$row["product_id"],$row["product_base_unit"]);
			//$rstock=reserve_stock($dbcon,$row["product_id"],$rel["purchase_unit"]);
			//$actualstock=$cstock-$rstock;
			$row_data[] = $rel_used["opening_stock_qty"];
			$row_data[] = $cstock;

			$add='';$view='';


			
			//$row_data[] = 0;

		if(in_array(OPENING_STOCK_LIST_SLUG_VIEW,$bulkAccessArray)){
			$view='<a class="btn btn-xs btn-info" data-original-title="View Stock" data-toggle="tooltip" onClick="show_view_stock_modal('.$row["product_id"].')" data-placement="top"><i class="fa fa-eye"></i></a>';
		}
				if(in_array(OPENING_STOCK_LIST_SLUG_CREATE,$bulkAccessArray)){
			$add = '<a class="btn btn-xs btn-primary" data-original-title="Add Stock" data-toggle="tooltip" data-placement="top" onClick="show_add_stock_modal('.$row["product_id"].')"><i class="fa fa-plus"></i></a>';
				}
			


			$row_data[] =$view.' '. $add;

			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	if(strtolower($POST['mode']) == "fetch_stock_list") {

	$where='';
	if($POST['branch_id']!=''){
		$where.=" and osm.branch_id=".$POST['branch_id'];
	}
	if($POST['product_id']!=''){
		$where.=" and osm.product_id=".$POST['product_id'];
	}
	if($POST['location_id']!=''){
		$where.=" and osm.location_id=".$POST['location_id'];
	}
	

	$appData = array();
	$i=1;
	$aColumns = array('osm.opening_stock_id','branch_name','gd_name','product_name','opening_stock_qty','closing_stock','product_name','approve_status','approve_status','osm.product_id','osm.branch_id','osm.opening_stock_unit');
	$sIndexColumn = "osm.opening_stock_id";
	$isWhere = array("osm.status = 0 and osm.approve_status in (".$POST['approve_status'].")".$where);
	$isJOIN = array('left join product_mst as pmst on pmst.product_id=osm.product_id','left join mst_godown as location on osm.location_id =location.gd_id','left join branch_mst as bran on bran.branch_id=osm.branch_id');
	$sTable = "opening_stock_mst osm";			
		
		$hOrder = "osm.opening_stock_id desc";
		include($include.'pagging.php');
		$appData = array();
		
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();

			$opening_stock_id = $row["opening_stock_id"];
			
			$row_data[] = '<a class="" data-original-title="View'.$row["opening_stock_id"].'" data-toggle="tooltip" data-placement="top">'.$row["sr"].'</a>';

			
			$row_data[] = '<a class="" data-original-title="View '.$row["branch_name"].'" data-toggle="tooltip" data-placement="top">'.$row["branch_name"].'</a>';

			$row_data[] = '<a class="" data-original-title="View '.$row["gd_name"].'" data-toggle="tooltip" data-placement="top">'.$row["gd_name"].'</a>';

			$row_data[] = '<a class="" data-original-title="View '.$row["product_name"].'" data-toggle="tooltip" data-placement="top">'.$row["product_name"].'</a>';
			

			$row_data[] = $row["opening_stock_qty"];

			$cstock=get_current_stock_new($dbcon,$row["product_id"],$row["opening_stock_unit"],$row['branch_id']);
			$row_data[] = $row["closing_stock"];

			// process stock

			$query = "select mst.*,pmst.process_name from process_opening_stock_mst mst 
			left join process_mst as pmst on mst.process_id=pmst.process_id where mst.opening_stock_id = ". $row['opening_stock_id'];

			$result=$dbcon->query($query);
			$cnt=mysqli_num_rows($result);
			$str = "";
			if($cnt>0){
				$str = '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Process</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Opening Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Closing Stock</th>
						</tr>';
					while($rel3=mysqli_fetch_assoc($result)){ 
						$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['process_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['opening_stock_qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['closing_stock'].'</td>
							
						</tr>';	
					}
					$str .= '</table>';	
					
			}else{
				$str = "";
			}
			$row_data[] = $str;
			if($row['approve_status'] == '1'){
		  		$row_data[] ='<button class="btn btn-xs btn-success" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
		  	}else if($row['approve_status'] == '2'){
		  		$row_data[] ='<button class="btn btn-xs btn-danger" data-original-title="Rejected" data-toggle="tooltip" data-placement="top">Rejected</button>';
		  	}
		  	else{
		  		$row_data[] ='<button class="btn btn-xs btn-warning" data-original-title=" Pending" data-toggle="tooltip" data-placement="top"> Pending </button>';
		  	}  


		  	$q1="select mst.stock_id from tbl_stock_trn mst where mst.stock_flage = 1 and mst.ref_id = ".$opening_stock_id ." and mst.ref_name = 'opening_stock' and  EXISTS ( select tmp.stock_id from tbl_stock_trn tmp where tmp.perent_id = mst.stock_id )";
			
				$res11=$dbcon->query($q1);
				$is_stock_used=brp_mysqli_num_rows($res11);
				$stock_apprv_btn = "";
				if(in_array(OPENING_STOCK_LIST_APPROVE,$bulkAccessArray)){
				if($is_stock_used){
					$stock_apprv_btn='<button class="btn btn-xs btn-info m-bot15" data-original-title="Stock in use" data-toggle="tooltip" data-placement="top">Used Stock</button>';
			
				}else{
					$stock_apprv_btn='<button class="btn btn-xs btn-success" data-original-title="Approve/Reject Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_approv_model('.$opening_stock_id.','.$row['approve_status'].')"><i class="fa fa-exclamation-triangle"></i></button>';
				}		
			}
			
			$row_data[] =$stock_apprv_btn;

						
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode'])=="load_product")
	{
		$type_id=$POST['type_id'];
		echo getrequiredproduct($dbcon,'',' and p.product_type='.$type_id.'');
	}else if(strtolower($POST['mode'])=="check_batch_permission")
	{
		$product_id=$POST['product_id'];
		
		$query="select batch_wise_stock_manage from product_mst where product_id=".$POST['product_id'];

		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);


		if($companyConfiguration['batch_wise_stock'] == '1' && $row['batch_wise_stock_manage'] == "1"){
			$arr['batch_wise_stock_manage'] =  1;

			if($companyConfiguration["batch_stock"] == '1'){

				$batch_no = get_batch_no($dbcon,$product_id);
				$arr['batch_no'] = $batch_no;
				$arr['readonly'] = 1;
			}else{
				$arr['batch_no'] = "";	
				$arr['readonly'] = 0;
			}

		}else{
			$arr['batch_wise_stock_manage'] =  0;
			$arr['batch_no'] = "";
			$arr['readonly'] = 0;
		}

		echo json_encode($arr);
		

	}else if(strtolower($POST['mode']) == "get_product_process_data") {


		$query="select product_base_unit, product_name, branch_id from product_mst where product_id=".$POST['product_id'];

		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);
		
		$arr['product_data'] = $row;
		

		$query_pro="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.status = 0 and prod.product_id = ".$POST['product_id'];
		$rel_pro = $dbcon->query($query_pro);
		$i=1;
		$str = '';
		$count =  brp_mysqli_num_rows($rel_pro);
		$arr['process_counter'] = $count;
		if($count > 0){


		while($product_process=brp_mysqli_fetch_assoc($rel_pro)){

			$str .= '<div class="form-group">
			<input type="hidden" class="form-control" name="process_id[]" value="'. $product_process["process_id"].'" />
									<label class="col-md-3 control-label text-right">'. $product_process["process_name"].'</label>
									<div class="col-md-9 col-xs-11 getstock">
										<div style="display:flex;" class="col-md-6">
										 <input type="number" class="form-control" name="opening_stock_qty[]" id="opening_stock'. $product_process["process_id"].'" class="opening_stock_qty" onkeyup="product_convert_qty(1,'. $product_process["process_id"].');" />
																							
													<input type="hidden" id="opening_stock_qty_hide'. $product_process["process_id"].'" name="opening_stock_qty_hide[]" class="opening_stock_qty_hide" value="" />
													
													<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs unit_show">  </span>
													</div>
													
													<div class="col-md-6 convert_unit_block" style="display:none;" >
														<div style="display:flex;">
														
														<input type="number"  title="Enter Qty" min="0" id="opening_stock_conv_qty'. $product_process["process_id"].'" name="opening_stock_conv_qty[]"  class="form-control opening_stock_conv_qty" onkeyup="product_convert_qty(2,'. $product_process["process_id"].');" />
														
																											
														<input type="hidden" id="opening_stock_conv_qty_hide'. $product_process["process_id"].'" name="opening_stock_conv_qty_hide[]" class="opening_stock_conv_qty_hide" value="" />
														
														<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs convert_unit_show">  </span>
													</div>
													</div>
									</div>
								</div>';
			$i++;

		}
	}else{
		$str .= '<div class="form-group text-center">
			<label> No Process added for this product.</label>
		</div>';
	}

		$arr['html'] = $str;
		
			// echo $str;
		echo json_encode($arr);
	}
	else if(strtolower($POST['mode'])== "convert_qty")
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
		$ret_qty_new=number_format($ret_qty, 3, ".", "");
				//$ret_qty=$ret_qty;
			//	echo $ret_qty;
		$row['show_qty']=$ret_qty_new;
		$row['hide_qty']=$ret_qty;
		echo json_encode($row);
	}

	else if(strtolower($POST['mode'])== "load_product_unit")
	{
		$query1="SELECT promst.product_base_unit,promst.product_conv_unit,conv_mst.unit_name as convert_unit_name,umst.unit_name as base_unit_name FROM product_mst as promst
		left join unit_mst as umst on umst.unitid=promst.product_base_unit
		left join unit_mst as conv_mst on conv_mst.unitid=promst.product_conv_unit
		WHERE product_id=".$POST['product_id'];
		$rs_type1=$dbcon->query($query1);
		$row1=brp_mysqli_fetch_assoc($rs_type1);

		if($row1['product_base_unit']!=$row1['product_conv_unit']){
			$row1['unit_status']="1";
		}else{
			$row1['unit_status']="0";
		}
				//$row1['qye']=$query1;

		echo json_encode($row1);
	}else if(strtolower($POST['mode']) == "get_godown_list") {
		$branch_id = $POST['branch_id'];

		$where='';
		if($branch_id){
			$where .= ' AND branch_id = ' . $branch_id;
		}


		$qry = "SELECT	*	FROM mst_godown where g_status = 0 AND company_id = ". $_SESSION['company_id'] . " AND show_in_list = 1 " . $where;


		$result=$dbcon->query($qry);

		$html = "<option value=''>Select Location </option>";

		if(brp_mysqli_num_rows($result) > 0){

			while ($row = brp_mysqli_fetch_assoc($result)) {

				$html .= "<option value='".$row['gd_id']."'> ".$row['gd_name']." </option>";
			}	

		}
		echo $html;			
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$product_id=$POST['selected_product_id'];
		$branch_id = $POST['branch_id'];
		$location_id = $POST['location_id'];
		$query="select opening_stock_id from opening_stock_mst where product_id= ".$product_id." and status = 0 and branch_id=".$branch_id." AND location_id =". $location_id;
		// echo $query;die;
		$result=$dbcon->query($query);
		$count=brp_mysqli_num_rows($result);	

		if($count>0){
			$whr = "product_id= ".$product_id." and status = 0 and branch_id=".$branch_id." AND location_id =". $location_id;
			$update['active_status'] = 1;
			 $upd_id=update_record('opening_stock_mst', $update, $whr , $dbcon);
		}
			   $stock['product_id'] = $product_id;
			   $stock['branch_id'] = $branch_id;
			   $stock['location_id'] = $location_id;
			   $stock['opening_stock_qty'] = $POST['opening_stock'];
			   $stock['opening_stock_unit'] = $POST['unitid'];
			   $stock['opening_stock_conv_qty'] = $POST['opening_stock_conv_qty_main'];
			   $stock['opening_stock_conv_unit'] = $POST['conv_unitid'];
			   $stock['closing_stock'] = $POST['opening_stock'];
			   $stock['batch_no'] = $POST['batch_no'];
			   $stock['status'] = "0";
			   $stock['active_status'] = "0";
			   $stock['user_id'] = $_SESSION['user_id'];
			   $stock['company_id'] = $_SESSION['company_id'];

			   $inserestimateid=add_record('opening_stock_mst', $stock, $dbcon);

			   $arr_process_ids = $POST['process_id'];
			   $arr_process_stock_qty = $POST['opening_stock_qty'];
			   $arr_process_stock_conv_qty = $POST['opening_stock_conv_qty'];

			// $process_stock = array_combine($arr_process_id, $arr_process_stock_qty);
				
			if($inserestimateid){	
				/*$stock_date=date("Y-m-d");
				$stock_id=add_stock($dbcon,$product_id,$POST['unitid'],$stock_date,"opening_stock",$inserestimateid,$location_id,$POST['opening_stock'],"1",$branch_id,"","","","","");
*/

				$stock['opening_stock_id'] = $inserestimateid;
				 foreach ($arr_process_ids as $key => $process_id) {
			 	   $stock['process_id'] = $process_id;
			 	   $stock['opening_stock_qty'] = $arr_process_stock_qty[$key];
				   $stock['opening_stock_conv_qty'] = $arr_process_stock_conv_qty[$key];
				   $stock['closing_stock'] = $arr_process_stock_qty[$key];
				 	
				   $pro_add_id=add_record('process_opening_stock_mst', $stock, $dbcon);
			  	}

				$arr['msg']="1";
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"opening_stock_add",1,"opening_stock_mst",$inserestimateid);
			}
			else{
				$arr['msg']="0";
			}
		
				
		echo json_encode($arr);
		
	}else if(strtolower($POST['mode']) == "load_stock_details") {

			$qry="select osm.opening_stock_id,branch_name,gd_name,product_name,opening_stock_qty,closing_stock,approve_status from opening_stock_mst osm
			left join product_mst as pmst on pmst.product_id=osm.product_id
			left join mst_godown as location on osm.location_id =location.gd_id
			left join branch_mst as bran on bran.branch_id=osm.branch_id
			
			where osm.status = 0 and osm.opening_stock_id =".$POST['opening_stock_id'];
			$rel=mysqli_fetch_assoc($dbcon->query($qry));
			
		//Party PO Details Table View
			$str='';
			$str.='<div class="form-group">
			<table class="display table table-bordered table-striped">
			<tr>
			<td colspan="2"><strong>Product Name:</strong> '.$rel['product_name'].'</td>
			</tr>
			<tr>
			<td><strong>Branch Name:</strong> '.$rel['branch_name'].'</td>
			<td><strong>Location:</strong> '.$rel['gd_name'].'</td>
			</tr>
			<tr>
			<td><strong>Opening Stock:</strong> '.$rel['opening_stock_qty'].'</td>
			<td><strong>Closing Stock:</strong> '.$rel['closing_stock'].'</td>
			</tr>
			';
			$str.='</table></div>
			<hr/>
			';

			$query = "select mst.*,pmst.process_name from process_opening_stock_mst mst 
			left join tbl_product_process as prpro on prpro.pr_process_id = mst.process_id
			left join process_mst as pmst on prpro.process_id=pmst.process_id
			where prpro.status = 0 and mst.status = 0 mst.opening_stock_id = ". $rel['opening_stock_id'];



			$result=$dbcon->query($query);
			$cnt=mysqli_num_rows($result);
			
			if($cnt>0){
				$str.='<div class="form-group">';
				$str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Process</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Opening Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Closing Stock</th>
						</tr>';
					while($rel3=mysqli_fetch_assoc($result)){ 
						$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['process_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['opening_stock_qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel3['closing_stock'].'</td>
							
						</tr>';	
					}
					$str .= '</table></div>';	
					
			}
			
			$arr['mod_stock_div_sec'] = $str;
			
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode']) == "add_stock_apprv_hist") {
           
            $check_hist_qry = "selec log.stock_aprv_log_id, usr.user_name, log.approve_status, log.approve_remark, log.created_at, log.user_id 
                    FROM tbl_stock_aprv_log as log left join users as usr on usr.user_id=log.user_id 
                    where log.stock_aprv_log_status=0 and log.opening_stock_id=".$POST['opening_stock_id']." and log.user_id = ".$_SESSION['user_id']."
                    order by log.stock_aprv_log_id desc limit 1";
            $result = brp_mysqli_query($dbcon,$check_hist_qry);
            $history_data = brp_mysqli_fetch_all($result,MYSQLI_ASSOC);

            if($history_data[0]['approve_status'] !== $POST['approve_status']) {
                $info1['approve_remark']	= $POST['approve_remark'];
                $info1['approve_status']	= $POST['approve_status'];
                $info1['opening_stock_id']             = $POST['opening_stock_id'];
                $info1['user_id']		= $_SESSION['user_id'];
                $info1['company_id']	= $_SESSION['company_id'];

                $insert_id=add_record("tbl_stock_aprv_log", $info1, $dbcon);

                if($insert_id){

                	// add tbl_stock_trn
                	if($POST['approve_status'] == '1'){
                			$s_query = "select * from opening_stock_mst where 
									 opening_stock_id =". $POST['opening_stock_id'];
						$s_result=$dbcon->query($s_query);
						$cnt=brp_mysqli_num_rows($s_result);
						$row=brp_mysqli_fetch_array($s_result);
						if($cnt>0){
							$cstock=get_current_stock_new($dbcon,$row["product_id"],$row["opening_stock_unit"],$row['branch_id']);

							$stock = 0;
							if($row['opening_stock_qty'] > $cstock){
								$stock = $row['opening_stock_qty'] - $cstock;
								$info_stockadd['stock_flage']	= "1";
								$info_stockadd['stock_date']		= date("Y-m-d",strtotime($row['cdate']));
								$info_stockadd['product_id']				= $row['product_id'];
								$info_stockadd['base_stock']				= $stock;
								$info_stockadd['base_unit']					= $row['opening_stock_unit'];

								$type="conv_unit";
								$conv_stock=convert_stock($dbcon,$stock,$row['product_id'],$type);
								$info_stockadd['convert_stock']				= $conv_stock;
								$info_stockadd['convert_unit']					= $row['opening_stock_conv_unit'];
								$info_stockadd['stock_flage']				= "1";
								$info_stockadd['godown_id']					= $row['location_id'];
								$info_stockadd['ref_name']					= 'opening_stock';
								$info_stockadd['ref_id']					= $POST['opening_stock_id'];
								$info_stockadd['stock_status']				= "0";
								$info_stockadd['cdate']						= date("Y-m-d H:i:s");
								$info_stockadd['user_id']					= $_SESSION['user_id'];
								$info_stockadd['company_id']				= $_SESSION['company_id'];
								$info_stockadd['batch_no']					= $row['batch_no'];

								$opening_stock_id=add_record('tbl_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);
							}else if($row['opening_stock_qty'] < $cstock){
								$stock = $cstock - $row['opening_stock_qty'];
								$info_stockadd['stock_flage']	= "2";

								$qry_11 = "select * from tbl_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $row['product_id'] . " and godown_id = " . $row['location_id'] . " and branch_id = " . $row['branch_id'] . " and base_stock > used_base_stock";

								$res_11 = $dbcon->query($qry_11);

								while($row_11=brp_mysqli_fetch_array($res_11)){
											$avl_stock = $row_11['base_stock'] - $row_11['used_base_stock'];
											if($stock > 0) {
												$deduct_stock = 0;
												if($avl_stock >= $stock){
													$deduct_stock = $stock;
												} else{
													$deduct_stock = $avl_stock;
													$stock = $stock - $avl_stock;						
												}

												$info_stockadd['stock_flage']	= "2";
												$info_stockadd['stock_date']		= date("Y-m-d",strtotime($row['cdate']));
												$info_stockadd['product_id']				= $row['product_id'];
												$info_stockadd['base_stock']				= $deduct_stock;
												$info_stockadd['base_unit']					= $row['opening_stock_unit'];

												$type="conv_unit";
												$conv_stock=convert_stock($dbcon,$deduct_stock,$row['product_id'],$type);
												$info_stockadd['convert_stock']				= $conv_stock;
												$info_stockadd['convert_unit']					= $row['opening_stock_conv_unit'];
												
												$info_stockadd['godown_id']					= $row['location_id'];
												$info_stockadd['ref_name']					= 'opening_stock';
												$info_stockadd['ref_id']					= $row['opening_stock_id'];
												$info_stockadd['stock_status']				= "0";
												$info_stockadd['cdate']						= date("Y-m-d H:i:s");
												$info_stockadd['user_id']					= $_SESSION['user_id'];
												$info_stockadd['company_id']				= $_SESSION['company_id'];
												$info_stockadd['perent_id']					= $row_11['stock_id'];
												$info_stockadd['batch_no']					= $row['batch_no'];
												$opening_stock_id=add_record('tbl_stock_trn',$info_stockadd, $dbcon,$row['branch_id']);


												$used_stock =  $row_11['used_base_stock'] + $deduct_stock;
												$used_conv_stock =  $row_11['used_convert_stock'] + $conv_stock;

												$update_used_stock['used_base_stock'] = $used_stock; 
												$update_used_stock['used_convert_stock'] = $used_conv_stock;

												$updateid=update_record('tbl_stock_trn', $update_used_stock,"stock_id=".$row_11['stock_id'] , $dbcon);

											}
								}
							
							}

						//add in tbl_process_stock_trn
							$query1 = "select * from  process_opening_stock_mst where status = 0 and
									 opening_stock_id =". $POST['opening_stock_id'];
								$result1=$dbcon->query($query1);
								$cnt1=brp_mysqli_num_rows($result1);
								
								if($cnt1>0){

									while($rel3=mysqli_fetch_assoc($result1)){
										$pstock=get_current_process_stock_new($dbcon,$rel3["product_id"],$rel3["
											process_id"],$rel3["opening_stock_unit"],$rel3['branch_id']);

										$stock = 0;
										if($rel3['opening_stock_qty'] > $pstock){
											$stock = $rel3['opening_stock_qty'] - $pstock;
											// echo "ok ===" .$stock;
											$process_stockadd['process_stock_date']		= date("Y-m-d",strtotime($rel3['cdate']));
											$process_stockadd['product_id']				= $rel3['product_id'];
											$process_stockadd['process_id']				= $rel3['process_id'];
											$process_stockadd['base_stock']				= $stock;
											$process_stockadd['base_unit']				= $rel3['opening_stock_unit'];

											$type="conv_unit";
											$conv_stock=convert_stock($dbcon,$stock,$rel3['product_id'],$type);
											$process_stockadd['conv_stock']				= $conv_stock;

											// $process_stockadd['conv_stock']				= $rel3['opening_stock_conv_qty'];
											$process_stockadd['conv_unit']					= $rel3['opening_stock_conv_unit'];
											$process_stockadd['stock_flage']				= "1";
											$process_stockadd['godown_id']					= $rel3['location_id'];;
											$process_stockadd['ref_name']					= 'opening_stock';
											$process_stockadd['ref_id']					= $rel3['process_opening_stock_id'];
											$process_stockadd['stock_status']				= "0";
											$process_stockadd['cdate']						= date("Y-m-d H:i:s");
											$process_stockadd['user_id']					= $_SESSION['user_id'];
											$process_stockadd['company_id']				= $_SESSION['company_id'];
											$process_stockadd['batch_no']				= $rel3['batch_no'];

											$process_stock_id=add_record('tbl_process_stock_trn',$process_stockadd, $dbcon,$rel3['branch_id']);

										}
										else if($rel3['opening_stock_qty'] < $pstock){
											$stock = $pstock - $rel3['opening_stock_qty'];
											

									$qry_11 = "select * from tbl_process_stock_trn where stock_status = 0 and stock_flage = 1 and product_id = " . $rel3['product_id'] . " and godown_id = " . $rel3['location_id'] . " and branch_id = " . $rel3['branch_id'] . " and process_id = " . $rel3['process_id']." and base_stock > used_base_stock";

								$res_111 = $dbcon->query($qry_11);

								while($row_111=brp_mysqli_fetch_array($res_111)){
											$avl_stock = $row_111['base_stock'] - $res_111['used_base_stock'];
											if($stock > 0) {
												$deduct_stock = 0;
												if($avl_stock >= $stock){
													$deduct_stock = $stock;
												} else{
													$deduct_stock = $avl_stock;
													$stock = $stock - $avl_stock;						
												}

											
											$process_stockadd['stock_flage']	= "2";	

											$process_stockadd['process_stock_date']		= date("Y-m-d",strtotime($rel3['cdate']));
											$process_stockadd['product_id']				= $rel3['product_id'];
											$process_stockadd['process_id']				= $rel3['process_id'];
											$process_stockadd['base_stock']				= $deduct_stock;
											$process_stockadd['base_unit']				= $rel3['opening_stock_unit'];

											$type="conv_unit";

											$conv_stock=convert_stock($dbcon,$deduct_stock,$row13['product_id'],$type);
											$process_stockadd['conv_stock']				= $conv_stock;

											// $process_stockadd['conv_stock']				= $rel3['opening_stock_conv_qty'];
											$process_stockadd['conv_unit']					= $rel3['opening_stock_conv_unit'];
											$process_stockadd['stock_flage']				= "1";
											$process_stockadd['godown_id']					= $rel3['location_id'];;
											$process_stockadd['ref_name']					= 'opening_stock';
											$process_stockadd['ref_id']					= $rel3['process_opening_stock_id'];
											$process_stockadd['stock_status']				= "0";
											$process_stockadd['cdate']						= date("Y-m-d H:i:s");
											$process_stockadd['user_id']					= $_SESSION['user_id'];
											$process_stockadd['company_id']				= $_SESSION['company_id'];
											$process_stockadd['batch_no']				= $rel3['batch_no'];

											$process_stock_id=add_record('tbl_process_stock_trn',$process_stockadd, $dbcon,$rel3['branch_id']);


												$used_stock =  $row_111['used_base_stock'] + $deduct_stock;
												$used_conv_stock =  $row_111['used_convert_stock'] + $conv_stock;

												$update_used_stock['used_base_stock'] = $used_stock; 
												$update_used_stock['used_convert_stock'] = $used_conv_stock;

												$updateid=update_record('tbl_process_stock_trn', $update_used_stock,"process_stock_id=".$row_111['process_stock_id'] , $dbcon);

											}
								}

										}
									}

								}
						}
                	}else{

                		// remove stock from 
                		if($POST['current_status'] == '1'){
                			$whr = "stock_flage = 1 and ref_name = 'opening_stock' and ref_id=".$POST['opening_stock_id'];
                				$status['stock_status'] = 2;
                			 $upd_id=update_record('tbl_stock_trn', $status, $whr , $dbcon);
                			 $updid=update_record('tbl_process_stock_trn', $status, $whr , $dbcon);
                		}

                	}

                	 $infoso['approve_status'] = $POST['approve_status'];
                	 if($POST['approve_status'] == '0'){
                	 	 $infoso['approve_status'] = 2;
                	 }
                    $updateid=update_record('opening_stock_mst', $infoso,"opening_stock_id=".$POST['opening_stock_id'] , $dbcon);
                    $update_id=update_record('process_opening_stock_mst', $infoso,"opening_stock_id=".$POST['opening_stock_id'] , $dbcon);
                	                   
                }
                echo TRUE;
            } else {
                echo FALSE;
            }
	}
	else if(strtolower($POST['mode']) == "load_stock_hist_datatable") {
		$where='';
        if($POST['opening_stock_id']){
            $where.="  and log.opening_stock_id=".$POST['opening_stock_id'];
        }

		$appData = array();
        $i=1;
        $aColumns = array('log.stock_aprv_log_id','log.opening_stock_id', 'usr.user_name', 'log.approve_status', 'log.approve_remark', 'log.created_at', 'log.user_id');
        $sIndexColumn = "log.stock_aprv_log_id";
        $isWhere = array("log.stock_aprv_log_status = 0 ".$where." ");
        $sTable = "tbl_stock_aprv_log as log";
        $isJOIN = array('left join users as usr on usr.user_id=log.user_id');
        $hOrder = "log.stock_aprv_log_id desc";
        include($include.'pagging.php');
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['user_name'];
			
			if($row['approve_status']=='1'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Approved</div>';
			}
			else{
				$row_data[] = '<div class="external-event label label-danger ui-draggable" style="cursor:auto;">Rejected</div>';
			}
			
			$row_data[] = nl2br($row['approve_remark']);
			$row_data[] = date("d-M-Y h:i A",strtotime($row['created_at']));
			
			$appData[] = $row_data;
			$id++;
			//print_r($row_data);
		}
		$output['aaData'] = $appData;
		//print_r($output);
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "load_stock_brach_wise_details") {
			

			$qry="select osm.branch_id, branch_name from opening_stock_mst osm
			left join branch_mst as bran on bran.branch_id=osm.branch_id
			
			where osm.status = 0 and  osm.product_id =".$POST['product_id'] . " group by osm.branch_id";
			
			$result=$dbcon->query($qry);
			$count=brp_mysqli_num_rows($result);
			
			if($count == 0){
					$str = "<div class='mbot30 mtop20 text-center'>
						<h3>No Stock added for this product.</h3>
					</div";
			}else{
				$str = '<div class="panel-group m-bot20" id="accordion">';

			while($rel=mysqli_fetch_assoc($result)){
				$str .= ' <div class="panel panel-default">
                              <div class="panel-heading">
                                  <h4 class="panel-title">
                                      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$rel['branch_id'].'">
                                          '.$rel['branch_name'].'
                                      </a>
                                  </h4>
                              </div>
                              <div id="collapse'.$rel['branch_id'].'" class="panel-collapse collapse">
                                  <div class="panel-body">';

            $qry1="select osm.opening_stock_id,branch_name,gd_name,product_name,opening_stock_qty,closing_stock,approve_status from opening_stock_mst osm
			left join product_mst as pmst on pmst.product_id=osm.product_id
			left join mst_godown as location on osm.location_id =location.gd_id
			left join branch_mst as bran on bran.branch_id=osm.branch_id
			
			where  osm.status = 0 and osm.product_id =".$POST['product_id'] ." and osm.branch_id =".$rel['branch_id'];

			$res1=$dbcon->query($qry1);
			$cnt1=brp_mysqli_num_rows($res1);
		//Party PO Details Table View
			if($cnt1 > 0){
				$str.='<div class="form-group">';
				$str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Product Name</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Location</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Opening Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Closing Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Process Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Action</th>
						</tr>';
				while($rel2=mysqli_fetch_assoc($res1)){
					$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel2['product_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel2['gd_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel2['opening_stock_qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel2['closing_stock'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >';

					$query3 = "select mst.*,pmst.process_name from process_opening_stock_mst mst 
						left join tbl_product_process as prpro on prpro.pr_process_id = mst.process_id
						left join process_mst as pmst on prpro.process_id=pmst.process_id
						where prpro.status = 0 and mst.opening_stock_id = ". $rel2['opening_stock_id'];



			$result3=$dbcon->query($query3);
			$cnt3=mysqli_num_rows($result3);
			
			if($cnt3>0){
				$str.='<div class="form-group">';
				$str .= '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >Process</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Opening Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Closing Stock</th>
						</tr>';
					while($rel4=mysqli_fetch_assoc($result3)){ 
						$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel4['process_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel4['opening_stock_qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$rel4['closing_stock'].'</td>
							
						</tr>';	
					}
					$str .= '</table></div>';	
					
			}

					$str .= '</td>
						<td style="border:0.5px #444 solid;text-align:center;" >';

					$status = "";	$edit ="";$delete="";


					if($rel2['approve_status'] == '0'){
						$status = '<button class="btn btn-xs btn-warning m-bot15" data-original-title="Pending" data-toggle="tooltip" data-placement="top">Pending</button>';
					}else if($rel2['approve_status'] == '1'){
						$status =  '<button class="btn btn-xs btn-success m-bot15" data-original-title="Approved" data-toggle="tooltip" data-placement="top" onClick="show_stock_status_history('.$rel2['opening_stock_id'].')">Approved</button>';
					}else{
						$status =  '<button class="btn btn-xs btn-danger m-bot15" data-original-title="Rejected" data-toggle="tooltip" data-placement="top" onClick="show_stock_status_history('.$rel2['opening_stock_id'].')">Rejected</button>';
					}


					if($rel2['approve_status'] != '1'){
						if(in_array(OPENING_STOCK_LIST_SLUG_UPDATE,$bulkAccessArray)){
						$edit='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_stock_data('. $rel2['opening_stock_id'].')"><i class="fa fa-pencil"></i></a>';
					}
					if(in_array(OPENING_STOCK_LIST_SLUG_DELETE,$bulkAccessArray)){
						$delete='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_stock('.$rel2['opening_stock_id'].')"><i class="fa fa-trash-o"></i></button>';
					}
					}

				$str .= $status . '</br>' .$edit.' '. $delete;
					$str .='</td>
						</tr>';	
				}
				$str .= '</table>';
			}


            $str .= '</div>
                              </div>
                          </div>';
			}

			$str .= '</div>';
			}
			
			$arr['mod_stock_div_view'] = $str;
			
			echo json_encode($arr);

	}
	else if(strtolower($POST['mode']) == "delete") {
		
			$info['status']	= 2;
				$updateestimateid=update_record('opening_stock_mst', $info, "opening_stock_id=".$POST['eid'], $dbcon);	
				$updateid=update_record('process_opening_stock_mst', $info, "opening_stock_id=".$POST['eid'], $dbcon);	
			if($updateestimateid){
				echo "1";	
			}else{
				echo "0";
			}
		
		
		//Insert LOG
		$log_entry=common_log_entry($dbcon,"stock_add",3,"opening_stock_mst",$POST['eid']);
	}else if(strtolower($POST['mode']) == "get_edit_product_process_data") {

		$query="select mst.*, product_base_unit, product_name from opening_stock_mst mst 
		left join product_mst as pmst on pmst.product_id=mst.product_id
		where opening_stock_id=".$POST['opening_stock_id'];

		$result=$dbcon->query($query);
		$row=brp_mysqli_fetch_assoc($result);
		
		$arr['product_data'] = $row;
		

		 $query_pro="select mst.*, pmst.process_name from process_opening_stock_mst as mst 
		left join tbl_product_process as pro on pro.pr_process_id=mst.process_id
		left join process_mst as pmst on pmst.process_id=pro.process_id 
		where pro.status = 0 and mst.opening_stock_id = ".$POST['opening_stock_id'];
		$rel_pro = $dbcon->query($query_pro);
		$i=1;
		$str = '';
		// $arr['process_counter'] = brp_mysqli_num_rows($rel_pro);
$count =  brp_mysqli_num_rows($rel_pro);
		$arr['process_counter'] = $count;
		if($count > 0){
		while($product_process=brp_mysqli_fetch_assoc($rel_pro)){

			/*$str .= '<div class="col-md-12 m-bot15">
			<div class="form-group">
			<label class="col-md-4 control-label text-right">'. $product_process["process_name"].'</label>
			<div class="col-md-6 col-xs-11">
			<input type="hidden" class="form-control" name="process_id[]" value="'. $product_process["pr_process_id"].'" />
			<input type="text" class="form-control" name="process_stock_qty[]" id="'. $product_process["pr_process_id"].'" />
			</div>
			</div>
			</div>';*/

			$str .= '<div class="form-group">
			<input type="hidden" class="form-control" name="process_opening_stock_id[]" value="'. $product_process["process_opening_stock_id"].'" />
			<input type="hidden" class="form-control" name="process_id[]" value="'. $product_process["process_id"].'" />
									<label class="col-md-3 control-label text-right">'. $product_process["process_name"].'</label>
									<div class="col-md-9 col-xs-11 getstock">
										<div style="display:flex;" class="col-md-6">
										 <input type="number" class="form-control" name="opening_stock_qty[]" id="opening_stock'. $product_process["process_id"].'" class="opening_stock_qty" onkeyup="product_convert_qty(1,'. $product_process["process_id"].',1);" value="'. $product_process["opening_stock_qty"].'" />
																							
													<input type="hidden" id="opening_stock_qty_hide'. $product_process["process_id"].'" name="opening_stock_qty_hide[]" class="opening_stock_qty_hide" value="'. $product_process["opening_stock_qty"].'" />
													
													<span style="color: red;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning btn-xs unit_show">  </span>
													</div>
													
													<div class="col-md-6 convert_unit_block" style="display:none;" >
														<div style="display:flex;">
														
														<input type="number"  title="Enter Qty" min="0" id="opening_stock_conv_qty'. $product_process["process_id"].'" name="opening_stock_conv_qty[]"  class="form-control opening_stock_conv_qty" onkeyup="product_convert_qty(2,'. $product_process["process_id"].',1);" value="'. $product_process["opening_stock_conv_qty"].'"/>
														
																											
														<input type="hidden" id="opening_stock_conv_qty_hide'. $product_process["process_id"].'" name="opening_stock_conv_qty_hide[]" class="opening_stock_conv_qty_hide" value="'. $product_process["opening_stock_conv_qty"].'" />
														
														<span style="color: #105a03;font-weight: 600;margin: 10px;" class="btn btn-round btn-warning  btn-xs convert_unit_show">  </span>
													</div>
													</div>
									</div>
								</div>';
			$i++;

		}
		}else{
		$str .= '<div class="form-group text-center">
			<label> No Process added for this product.</label>
		</div>';
	}

		$arr['html'] = $str;
		
			// echo $str;
		echo json_encode($arr);
	}else if(strtolower($POST['mode']) == "update") {
		// echo "<pre>";
		// print_r($POST);die;
		$opening_stock_id = $POST['opening_stock_id'];
		$product_id=$POST['product_id'];
		$branch_id = $POST['branch_id'];
		$location_id = $POST['location_id'];
		$query="select opening_stock_id from opening_stock_mst where product_id= ".$product_id." and branch_id=".$branch_id." AND location_id =". $location_id . " and opening_stock_id != " . $opening_stock_id;
		// echo $query;die;
		$result=$dbcon->query($query);
		$count=brp_mysqli_num_rows($result);	

			   $stock['product_id'] = $product_id;
			   $stock['branch_id'] = $branch_id;
			   $stock['location_id'] =$location_id;
			   $stock['opening_stock_qty'] = $POST['opening_stock_qty_main'];
			   $stock['opening_stock_unit'] = $POST['unitid'];
			   $stock['opening_stock_conv_qty'] = $POST['opening_stock_conv_qty_main'];
			   $stock['opening_stock_conv_unit'] = $POST['conv_unitid'];
			   $stock['closing_stock'] = $POST['opening_stock_qty_main'];
			   $stock['batch_no'] = $POST['batch_no'];
			   $stock['status'] = "0";
			   $stock['user_id'] = $_SESSION['user_id'];
			   $stock['company_id'] = $_SESSION['company_id'];
			   $stock['cdate']	= date("Y-m-d H:i:s");

			   $updateid=update_record('opening_stock_mst', $stock,"opening_stock_id=".$opening_stock_id, $dbcon);

			   $process_opening_stock_id = $POST['process_opening_stock_id'];
			   $arr_process_ids = $POST['process_id'];
			   $arr_process_stock_qty = $POST['opening_stock_qty'];
			   $arr_process_stock_conv_qty = $POST['opening_stock_conv_qty'];

			// $process_stock = array_combine($arr_process_id, $arr_process_stock_qty);
				
			if($updateid){	

				$info['godown_id'] = $location_id;
				$info['base_stock'] = $POST['opening_stock_qty_main'];
				$info['convert_stock'] = $POST['opening_stock_conv_qty_main'];

				$updateid=update_record('tbl_stock_trn', $info,"ref_name='opening_stock' and ref_id=".$opening_stock_id, $dbcon);
				$pro_stock['batch_no'] = $POST['batch_no'];
				$pro_stock['opening_stock_id'] = $opening_stock_id;
				 foreach ($arr_process_ids as $key => $process_id) {
			 	   $pro_stock['process_id'] = $process_id;
			 	   $pro_stock['opening_stock_qty'] = $arr_process_stock_qty[$key];
				   $pro_stock['opening_stock_conv_qty'] = $arr_process_stock_conv_qty[$key];
				   $pro_stock['closing_stock'] = $arr_process_stock_qty[$key];
				 
					 $pro_add_id=update_record('process_opening_stock_mst', $pro_stock,"process_id = " . $process_id . " and process_opening_stock_id=".$process_opening_stock_id[$key], $dbcon);
			 	}

				$arr['msg']="1";
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"opening_stock_add",1,"opening_stock_mst",$inserestimateid);
			}
			else{
				$arr['msg']="0";
			}
		
				
		echo json_encode($arr);
		
	}
	else if(strtolower($POST['mode']) == "check_data"){
				$row[] ='';
				if(!empty($_FILES['excel_file']['tmp_name']))
				{
					$file_name = $_FILES['excel_file']['name'];
					$err = $_FILES["excel_file"]["tmp_name"];
					$exts = array('csv'); 
					if(in_array(end(explode('.', $file_name)), $exts))
					{
						move_uploaded_file($err,CUSTOMER_UPING.$file_name);
						$handle = fopen(CUSTOMER_UPING.$file_name, "r");
						$row = check_data($file_name,$dbcon);
					}
					else
					{
						$row['res'] = "-1";
					}
			}
			else
				$row['res'] ='0';
				echo json_encode($row);
		}
		else if(strtolower($POST['mode']) == "import_data"){
				if(!empty($_FILES['excel_file']['tmp_name']))
				{
					$file_name = $_FILES['excel_file']['name'];
					$err = $_FILES["excel_file"]["tmp_name"];
					move_uploaded_file($err,CUSTOMER_UPING.$file_name);
					$handle = fopen(CUSTOMER_UPING.$file_name, "r");
					($data = fgetcsv($handle,","));//get field rows
					$i=1;$error_array=array();
					while (($data = fgetcsv($handle,",")) !== FALSE) //get all data one by one
					{
						$error='';
						if(!empty($data['0']))
						{
							
							$info['company_name']=$data['0'];
							$info['cust_name']=$data['1'];
							$info['cust_address']=$data['2'];
				 $qstate="SELECT `stateid`,`state_name` FROM `state_mst` WHERE state_status=0 and `state_name` ='".$data['3']."'";
			 	$tr_state = mysqli_fetch_array($dbcon -> query($qstate));
				if(!empty($tr_state))
				{
					$info['stateid']=$tr_state['stateid'];
				}				
				else
				{
					$error='State Name Not Found';
					array_push($error_array,1);
				}				
				$qcity="SELECT `cityid`,`city_name` FROM `city_mst` WHERE city_status=0 and `city_name` ='".$data['4']."'";
				$tr_city = mysqli_fetch_array($dbcon -> query($qcity));
				if(!empty($tr_city))
				{
					$info['cityid']=$tr_city['cityid'];
				}				
				else
				{
					$error='City Name Not Found';
					array_push($error_array,1);
				}				
				$info['opening_balance']=$data['5'];
				if($data['6']=="Cr")
				{
					$info['balance_typeid']=1;
				}
				else if($data['6']=="Dr")
				{
					$info['balance_typeid']=2;
				}
				else if(!empty($data['7']))
				{
					$error='Please Mention Cr/Dr';
					array_push($error_array,1);
				}
				$info['cust_mobile']=$data['7'];
				$info['cust_email']=$data['8'];
				$info['cust_pincode']=$data['9'];
				$info['gst_no']=$data['10'];
				$info['pan_no']=$data['11'];
				
				$qcity="SELECT `countryid`,`country_name` FROM `country_mst` WHERE country_status=0 and `country_name` ='".$data['12']."'";
				$tr_city = mysqli_fetch_array($dbcon -> query($qcity));
				if(!empty($tr_city))
				{
					$info['countryid']=$tr_city['countryid'];
				}				
				else
				{
					$error='Country Name Not Found';
					array_push($error_array,1);
				}
				$info['party_type']=$data['13'];
				if($data['13']=="customer")
				{
					$info['party_type']=1;
				}
				else if($data['13']=="vendor")
				{
					$info['party_type']=2;
				}
				else
				{
						$info['party_type']=0;
				}
					$info['cdate']			= date("Y-m-d H:i:s");
					$info['user_id']		= $_SESSION['user_id'];
					$info['usertype_id']	= $_SESSION['user_type'];
					$info['company_id']		= $_SESSION['company_id'];
						
					$q="SELECT `cust_name`,`company_name` FROM `tbl_customer` WHERE cust_status=0 and `company_id` ='".$_SESSION['company_id']."' and `company_name` ='".$info['company_name']."' ";
							$tr = $dbcon -> query($q);
							$cnt=mysqli_num_rows($tr);
							if($cnt>0 ) {
								$error='Company Already Added';
								array_push($error_array,1);
							}
							else if(!empty($error))
							{
								$err='error';
								array_push($error_array,1);
							}
							else
							{
								add_record('tbl_customer', $info, $dbcon);
							}
							 
						}
						else
						{
							$error='Blank Row';
							array_push($error_array,1);
						}
						if(!empty($error))
						{
								
								$info1['line_num']=$i;
								$info1['error']=$error;
								$info1['company_id']=$_SESSION['company_id'];
								add_record('cust_tempdata', $info1, $dbcon);
						}
					$i++;	
					}
						if(in_array(1,$error_array))
						{
							$result['res']='5';
						}
						else
						{
							$result['res']='4';
						}	
				fclose($handle);//close file reading
				
			}
			else
			{$result['res']='0';}
			echo  json_encode($result);
		}else if(brp_strtolower($POST['mode'])== "convert_qty")
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
				$ret_qty_new=number_format($ret_qty, 3, ".", "");
				//$ret_qty=$ret_qty;
			//	echo $ret_qty;
				$row['show_qty']=$ret_qty_new;
				$row['hide_qty']=$ret_qty;
				echo json_encode($row);
			}

	else if(brp_strtolower($POST['mode'])== "get_opening_stock_hist"){
		$product_id=$POST['product_id'];
		$branch_id = $POST['branch_id'];
		$location_id = $POST['location_id'];


	 $query="select mst.*, branch.branch_name, gd.gd_name from opening_stock_mst as mst
		left join branch_mst as branch on branch.branch_id = mst.branch_id
		left join mst_godown as gd on gd.gd_id = mst.location_id
		
		where mst.product_id= ".$product_id." and mst.status = 0 and mst.branch_id=".$branch_id." AND mst.location_id =". $location_id ." order by opening_stock_id desc LIMIT 5 ";
		$result=$dbcon->query($query);
		$str = "";
		$x = 1;
		$count=brp_mysqli_num_rows($result);	

		while($row=mysqli_fetch_assoc($result)){ 
			if($x == 1) {
				$str = '<table class="display table table-bordered table-striped" style="font-size:12px;border-collapse: collapse;border-top:none;" cellpadding="0" cellspacing="0" width="100%" >
						<tr>
							<th style="border:0.5px #444 solid;text-align:center;" >#</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Branch</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Location</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Opening Stock</th>
							<th style="border:0.5px #444 solid;text-align:center;" >Status</th>
						</tr>';
			}
			
					

						$status = "";
						if($row['active_status'] == '0'){
							$status = '<button class="btn btn-xs btn-success" data-original-title="Active" data-toggle="tooltip" data-placement="top">Active</button>';
						}else{
							$status = '<button class="btn btn-xs btn-warning" data-original-title="Deactive" data-toggle="tooltip" data-placement="top">Deactive</button>';
						}
						$str .= '<tr>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$x.'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$row['branch_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$row['gd_name'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$row['opening_stock_qty'].'</td>
							<td style="border:0.5px #444 solid;text-align:center;" >'.$status.'</td>
							
						</tr>';	

						
					if($count == $x){
						$str .= '</table>';	
					}
					$x++;
		}
		echo $str;
	}
	

function check_data($filename,$dbcon)
{
	$error=array();
	$arr = explode(".", $filename);
	$fp = fopen(CUSTOMER_UPING.$filename, 'r');
	$frow = fgetcsv($fp);
	if(count($frow)==14) // Define coulmn count Here
	{
		$msg='';
		foreach($frow as $i)
		if ( !in_array($i, array('Product Name','Branch Name','Location Name','State','City','Opening Balance','Cr/Dr','Mobile no','Email','Pin Code','GSTIN','Pan No','Country','Party Type'), true ) ) 
		{
			$msg='error';
		}
		
		if(!empty($msg))
		{
			$error['res']="3";
		}
		else
		{
			delete_record('cust_tempdata', 'company_id='.$_SESSION['company_id'], $dbcon);
			$error['res']="1";
		}
	}
	else
	{
		$error['res']="0";
	}
	return $error;
}

?>
