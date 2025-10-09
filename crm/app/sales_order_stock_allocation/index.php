<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
// error_reporting(E_ALL);

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
		
		//$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		// $where_db = check_branch('so_trn', $branch_id);
		$where_db='';
		$appData = array();
		$i=1;
		//+IFNULL(qc_total_rejected,0)

		$aColumns = array('mst.product_icode', 'dr.drawing_number','so.sales_order_no','so.sales_order_date','led.l_name','per.unit_name','cper.unit_name as cunit_name','so_trn.product_qty','so_trn.product_conv_qty','so_trn.sales_ordertrn_id','mst.product_name','tc.cat_name','so.delivery_date','bran.branch_name','so_trn.product_id','so_trn.work_order_qty','so_trn.unit_id','so_trn.conv_unit_id','(IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty,so.jobwork_type','so_trn.description');

		$sIndexColumn = "so_trn.sales_ordertrn_id";
		$isWhere = array("so_trn.sales_ordertrn_status=0 and so_trn.production_status=0 and mst.product_type!=8 and so.order_accept_status = 1 and so_trn.short_close_status=0 and so_trn.invoice_status=0 and so.approve_status=3".$where_db);

		$sTable = "tbl_sales_ordertrn as so_trn";

		$isJOIN = array("left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id",
						"left join unit_mst as per on per.unitid=so_trn.unit_id",
						"left join unit_mst as cper on cper.unitid=so_trn.conv_unit_id",
						"left join tbl_ledger as led on led.l_id=so.cust_id",
						"left join product_mst as mst on mst.product_id=so_trn.product_id",
						"left join tbl_category as tc on mst.product_category=tc.cat_id",
						"left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc 
			where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id",
			"left join branch_mst as bran on bran.branch_id=so_trn.branch_id","left join tbl_drawing as dr on dr.drawing_id = mst.drawing_id");
		
		$hOrder = "so.delivery_date desc";
		//$hGroupby = "pro.product_id";
		$having=" pending_qty > 0";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		
		//print_r($sqlReturn);
		foreach($sqlReturn as $row) {

			$row_data = array();
			//tbl_sales_order_production_trn
			//$pendingqty=$row['product_qty']-$row['work_order_qty'];
			$pendingqty=$row['pending_qty'];

			$cstock=get_current_stock_new($dbcon,$row["product_id"],$row["unit_id"]);
			$rstock=reserve_stock($dbcon,$row["product_id"],$row["unit_id"],"","","","","","","","","");
			$wipstock=wipstock($dbcon,$row["product_id"],$row["unit_id"],'');
			$actualstock=$cstock-$rstock; 
			$actualstock=$actualstock+$wipstock;

			$cstock_conv=get_current_stock_new($dbcon,$row["product_id"],$row["conv_unit_id"]);
			$rstock_conv=reserve_stock($dbcon,$row["product_id"],$row["conv_unit_id"],"","","","","","","","","");
			$wipstock_conv=wipstock($dbcon,$row["product_id"],$row["conv_unit_id"],'');
			$actualstock_conv=$cstock_conv-$rstock_conv; 
			$actualstock_conv=$actualstock_conv+$wipstock_conv;

			$row_data[] = $row['sales_order_no'];
			$row_data[] = $row['sales_order_date'];
			$row_data[] = $row['l_name'];

			$drawing_number = "";
			$item_code = "";
			if(in_array('drawing',$pro_search)){
				$drawing_number = " -- (".$row['drawing_number'].")";
			}
			if(in_array('item',$pro_search)){
				$item_code = " -- (".$row['product_icode'].")";
			}	

			if($pendingqty>=$actualstock){
				$validateqty=$actualstock;
			}else{
				$validateqty=$pendingqty;
			}

			$view='';$stock_allocate='';
			
			$unitname = $row['unit_name'];
			if ($row['unit_name'] == $row['cunit_name']) {
				$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $row['product_qty'] . "  " . $unitname;
				$row_data[] = $pendingqty . "  " . $unitname;
				$row_data[] = $actualstock . "  " . $unitname;

				if($actualstock>0){
					$stock_allocate='<button type="button" class="btn btn-xs btn-success" data-original-title="Allocate Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_allocation_so('.$row["sales_ordertrn_id"].','.$validateqty.',\''.$unitname.'\')">Allocate Stock</button>';
				}
			} else {
				$row_data[] = $row['product_name'].' '.$item_code.' '.$drawing_number;
				$row_data[] = ($row['cat_name']!=null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $row['product_qty'] . "  " . $unitname . '<br>' . $row['product_conv_qty'] . " ". $row['cunit_name'];

				$type="conv_unit";
				$pendingqty_conv=convert_stock_new($dbcon,$pendingqty,$row['product_id'],$type);

				$row_data[] = $pendingqty . "  " . $unitname . ' <br>' . $pendingqty_conv . " ".$row['cunit_name'];
				$row_data[] = $actualstock . "  " . $unitname .' <br>' . $actualstock_conv . " ".$row['cunit_name'];

				if($pendingqty_conv>=$actualstock_conv) {
					$validateqty_conv=$actualstock_conv;
				} else {
					$validateqty_conv=$pendingqty_conv;
				}

				if($actualstock>0){
					$stock_allocate='<button type="button" class="btn btn-xs btn-success" data-original-title="Allocate Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_allocation_so('.$row["sales_ordertrn_id"].','.$validateqty.',\''.$unitname.'\','.$validateqty_conv.',\''.$row['cunit_name'].'\')">Allocate Stock</button>';
				}
			}
			
			$row_data[] = date('d M, Y',strtotime($row["delivery_date"]));
			$row_data[] = $stock_allocate;
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode($output );

	}
	else if(strtolower($POST['mode']) == "load_entry_stock"){
		$q="select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=".$POST['ref_sales_order_trn_id'];
		$rel=$dbcon->query($q);
			//$str=array();

		$row=mysqli_fetch_array($rel);
		$godown=get_godown_stock_so($dbcon,$row['product_id'],$row['unit_id']);
		$work_order=get_min_max_work_order_stock($dbcon,$row['product_id']);
		if($companyConfiguration['trading_stock']==0){
			$html="
			<div class='col-md-5' > 
			".$godown."
			</div>
			<div class='col-md-7' >
			".$work_order."
			</div>
			<div class='col-md-12'>
			<center>
			<button type='submit' class='btn btn-success' id='save' name='save'>Save</button>
			</center>
			</div>
			";
		}else{
			$html="
			<div class='col-md-12' > 
			".$godown."
			</div>
			<div class='col-md-12'>
			<center>
			<button type='submit' class='btn btn-success' id='save' name='save'>Save</button>
			</center>
			</div>
			";

		}

		echo $html;
	}
	else if(strtolower($POST['mode']) == "get_product_name"){
		echo get_product_name($dbcon,$POST['product_id']);
	}
	else if(strtolower($POST['mode']) == "add"){
		$q="select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=".$POST['ref_sales_order_trn_id'];
		$rel=$dbcon->query($q);

		$row=mysqli_fetch_array($rel);
		foreach ($POST['so_godown'] as $i => $name) 
		{
			$godwn_id=$POST['so_godown'][$i];
			$stock=$POST['so_stock'][$i];
			if($stock>0){
				$info_e['sales_ordertrn_id']	=$row['sales_ordertrn_id'];
				$info_e['product_id']			=$row['product_id'];
				$info_e['product_qty']			=$stock;
				$info_e['godown_id']			=$godwn_id;
				$info_e['unit_id']				=$row['unit_id'];
				$info_e['allocate_qty']			=$stock;
				$info_e['remaning_invoice_qty']	=$stock;

				$info_e['cdate']				=date("Y-m-d");
				$info_e['company_id']			=$_SESSION['company_id'];
				$info_e['user_id']				=$_SESSION['user_id'];
				$inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row['branch_id']);
				add_so_reserve_stock($dbcon,$stock,$row['unit_id'],$row['product_id'],$row['sales_ordertrn_id'],$godwn_id,"",$row['branch_id']);
			}
			
		}

		foreach ($POST['so_req_id'] as $p => $name1) 
		{
			$request_id=$POST['so_req_id'][$p];
			$stock_alo=$POST['so_working_stock'][$p];
			if($stock_alo>0){
				$info_w['sales_ordertrn_id']	=$row['sales_ordertrn_id'];
				$info_w['product_id']			=$row['product_id'];
				$info_w['product_qty']			=$stock_alo;
				$info_w['request_id']			=$request_id;
				$info_w['unit_id']				=$row['unit_id'];

				$info_w['cdate']				=date("Y-m-d");
				$info_w['company_id']			=$_SESSION['company_id'];
				$info_w['user_id']				=$_SESSION['user_id'];
				$inserinvoiceidexp1=add_record('tbl_sales_order_production_trn', $info_w, $dbcon,$row['branch_id']);
			}
			
		}

		if($inserinvoiceidexp || $inserinvoiceidexp1){
			$arr['msg']="1";
		}else{
			$arr['msg']="0";
		}
		echo json_encode($arr);

	}
	else if(strtolower($POST['mode']) == "set_version"){
		$product_id = $_POST['product_id'];
		$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
		$qty = $_POST['qty'];				

		$check_sales_order = "select * from tbl_sales_ordertrn where sales_ordertrn_id = '$sales_ordertrn_id' AND bom_id ='0' AND bom_status = '1' ";
		$check_sales_order_res = $dbcon->query($check_sales_order);
		if(brp_mysqli_num_rows($check_sales_order_res)>0)
		{
			$product_bom_query="select * from tbl_bom where bom_version_id IN (SELECT bom_version_id FROM `pro_ms_bom_version` WHERE  is_default_bom = '1' AND  product_id=".$_POST['product_id'].")" ;
			if(brp_mysqli_num_rows($product_bom_query)>0)
			{

				$product_bom_row=brp_mysqli_fetch_assoc($dbcon->query($product_bom_query));			
				$info['bom_id'] = $product_bom_row['bom_id'];
				$info['bom_status'] = $product_bom_row['bom_status'];			
				$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
			}
			else
			{

				$info['bom_status'] = 0;			
				$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
				add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty,'');
			}
			
			echo "1";	
			
		}
		else
		{
			echo "0";	
		}


	}
	else if(strtolower($POST['mode']) == "ger_version_by_product"){

		$product_id = $_POST['product_id'];
		$sales_ordertrn_id= $_POST['sales_ordertrn_id'];
		$qty= $_POST['qty'];
		
		
		
		$qry="SELECT * from pro_ms_bom_version where product_id=".$POST['product_id'];
		$result=$dbcon->query($qry);
		
		$versionstr = '';
		
		if(brp_mysqli_num_rows($result) > 0)
		{	
			while($row=brp_mysqli_fetch_assoc($result))
			{
				$versionstr .= '<option value="'.$row['bom_version_id'].'">'.$row['version_name'].'</option>';
			}
			$versionstr .= '<option selected="selected"  value="10000">R&D</option>';
		}
		else
		{
			$versionstr .= '<option selected="selected"  value="10000">R&D</option>';
		}

		$str='<table class="table table-bordered">	<tr>
		<th colspan="2" style="text-align: center;"> <strong>Bom Version:</strong></th>
		<th colspan="3"><select class="select2 selproduct1" title="Select Bom Version" name="add_bom_version_id" id="add_bom_version_id">'.$versionstr.'</select>
		</th></tr><th colspan="5"  style="text-align: center;"><button type="button" onclick="product_custom_versions('.$product_id.','.$sales_ordertrn_id.','.$qty.');" class="btn btn-success" id="save" name="save">Save</button></th></tr></table>';							
		echo $str;
	}

	else if(strtolower($POST['mode']) == "set_custom_version"){
		$product_id = $_POST['product_id'];
		$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
		$version_id = $_POST['version_id'];	
		$qty = $_POST['qty'];

		if($version_id == "10000")
		{
			$info['bom_status'] = 0;			
			$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
			add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty,$version_id);				
			echo "1";
		}
		else
		{
			echo  $product_bom_query="select * from tbl_bom where bom_version_id ='$version_id' AND  bom_product=".$_POST['product_id']; 
			$product_bom_res=$dbcon->query($product_bom_query);	
			if(brp_mysqli_num_rows($product_bom_res)>0)
			{
				$product_bom_row=brp_mysqli_fetch_assoc($dbcon->query($product_bom_query));			
				$info['bom_id'] = $product_bom_row['bom_id'];
				$info['bom_status'] = $product_bom_row['bom_status'];	

				$updateid=update_record('tbl_sales_ordertrn', $info,"sales_ordertrn_id=".$_POST['sales_ordertrn_id'] ,$dbcon,$POST['branch_id']);
				
				add_requst_rnd($dbcon,$product_id,$sales_ordertrn_id,$qty,$version_id);		

				echo "1";	
			}
			else
			{
				echo "0";	
			}
		}	

	}
	else if(brp_strtolower($POST['mode']) == "show_stock_new") {

		$que_so="select * from tbl_sales_ordertrn where sales_ordertrn_id=".$POST['sales_order_trn_id'];
		$resi_so=$dbcon->query($que_so);
		$re_so=brp_mysqli_fetch_assoc($resi_so);


		$product_id=$re_so['product_id'];
		$branch_id=$re_so['branch_id'];
		// $unit_id=$re_so['rate_unit'];
		$unit_id=$re_so['unit_id'];

					//$rp_id=$POST['rp_id'];
		$unit_name = getunitname($dbcon,$unit_id);
		$diff_unit_name = "";
		$que_po="select batch_wise_stock_manage,product_conv_unit,product_base_unit from product_mst where product_id=".$product_id;
		$resi_grn=$dbcon->query($que_po);
		$re=brp_mysqli_fetch_assoc($resi_grn);

		$function = 'onkeyup="reserve_stock_convert_qty(1);"';
		if($re['product_conv_unit'] == $re['product_base_unit']){
				$diff_unit_name = $unit_name;				
			$function = 'onkeyup="reserve_stock_convert_qty(2);"';
			$diff_function = 'onkeyup="reserve_stock_convert_qty(1);"';		
		}else if($re['product_conv_unit'] == $unit_id){
			$diff_unit_name = getunitname($dbcon,$re['product_base_unit']);
			$function = 'onkeyup="reserve_stock_convert_qty(1);"';
			$diff_function = 'onkeyup="reserve_stock_convert_qty(2);"';
		}else{
			$diff_unit_name = getunitname($dbcon,$re['product_conv_unit']);
			$function = 'onkeyup="reserve_stock_convert_qty(2);"';
			$diff_function = 'onkeyup="reserve_stock_convert_qty(1);"';
		}


					//$god_stock=req_stock_entry();
					//$wipstock=req_wipstock_entry();
		$str=' 
		<div class="col-md-12" style="font-size: 25px;"><center><strong>Warehouse Stock</strong></center></div>
		<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
		<tr>
		<td style="font-weight: 600;">Warehouse</td>';
		if($re['batch_wise_stock_manage']==1){
			$str .='<td style="font-weight: 600;">Batch No</td>';
		}
		$str .='<td style="font-weight: 600;">Stock</td>
		<td style="font-weight: 600;">Reserve Stock</td>
		<td style="font-weight: 600;">Action</td>
		</tr>
		<tr>';
		if($re['batch_wise_stock_manage']==1){
			$str .='<td>
			<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();load_batch_no();">
			'.load_available_stock_godown($dbcon,$product_id,$branch_id).'
			</select>
			</td>
			<td>
			<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" onchange="load_godown_wise_stock();">
			</select>
			</td>';
		}else{
			$str .='<td>
			<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();">
			'.load_available_stock_godown($dbcon,$product_id,$branch_id).'
			</select>
			</td>
			<!--<td>
			<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" >
			</select>
			</td>-->';
		}
		$str .='<td>
		<div class="col-md-9">
									 <input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> '.$unit_name.' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									  	<input type="number"  title="Stock" min="0" id="diff_st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> '.$diff_unit_name.' </span>
									 </div>
		</td>
		<td>
		<div class="col-md-9">
									 <input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve" '.$function.' class="form-control numbersOnly"  />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> '.$unit_name.' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									  	<input type="number" '.$diff_function.'  title="Enter Stock" min="0" id="diff_st_stock_reserve" name="st_stock_reserve"  class="form-control numbersOnly"  />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> '.$diff_unit_name.' </span>
									 </div>
		</td>
		<td>
		<input type="button"  name="addrow" id="addrow" onClick="return add_reserve_temp();"  class="btn btn-primary" value="Add"/>
		</td>
		</tr>
		</table>
		<input type="hidden" name="batch_wise_stock_manage" id="batch_wise_stock_manage" value="'.$re['batch_wise_stock_manage'].'" />
		<div id="sale_productdata"></div>';

		$str .='<div class="col-md-12" >
		<center>
		<input type="button"  name="" id="" onClick="return save_reserve_stock();"  class="btn btn-primary" value="Save"/>

		<input type="hidden" name="product_id_model" id="product_id_model" value="'.$product_id.'" />
		<input type="hidden" name="unit_id_model" id="unit_id_model" value="'.$unit_id.'" />
		
		</center>
		</div>
		';


		echo $str;
	}
	else if(strtolower($POST['mode']) == "load_tempoutward") {


		$query="select trn.work_order_reserve_temp_id,trn.reserve_qty,cat.gd_name,uns.unit_name,st.batch_no from work_order_reserve_temp as trn
		left join mst_godown as cat on cat.gd_id=trn.godown_id
		left join unit_mst as uns on uns.unitid=trn.unit_id
		left join tbl_stock_trn as st on st.stock_id=trn.stock_id
		where trn.status=0 and trn.sales_ordertrn_id=".$POST['sales_ordertrn_id'];

			//echo $query;
		$result=$dbcon->query($query);
		echo '<div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="10%">Warehouse</th>';
		if($POST['batch_wise_stock_manage']==1){
			echo '<th class="text-center"width="15%">Batch No</th>';
		}
		echo '<th class="text-center"width="15%">Reserve Stock</th>
		<th class="text-center"width="10%">Action</th>
		</tr>';

			//echo $query;
		if(mysqli_num_rows($result)>0)
		{
			$i=1;$total=0;
			while($rel=brp_mysqli_fetch_assoc($result))
			{

				echo '<tr id="fieldtr'.$i.'">
				<td style="vertical-align:top;" class="text-left">
				'.$rel['gd_name'].'
				</td>';				
				if($POST['batch_wise_stock_manage']==1){
					echo '<td style="vertical-align:top;" class="text-left">
					'.$rel['batch_no'].'
					</td>';
				}
				echo '<td style="vertical-align:top;" class="text-center">
				'.$rel['reserve_qty'].' '.$rel['unit_name'].'
				</td>					

				<td style="vertical-align:top">

				<!--<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data('.$rel['purchaseordertrn_id'].',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit'.$i.'"><i class="fa fa-pencil"></i></button>-->

				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_stock('.$rel['work_order_reserve_temp_id'].');" id="fieldremove'.$i.'"><i class="fa fa-times"></i></button>
				</td>	
				</tr>';
				$total=$total+$rel['reserve_qty'];
				$i++;
			}
		}

		else{
			echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
		}
		echo '</table> 
		<input type="hidden" name="gstock_total" id="gstock_total" value="'.$total.'" />
		</div>
		</div>';
	}
	else if(strtolower($POST['mode'])== "load_batch_no")
	{
		
		$godwn_id=$POST['godwn_id'];
		$product_id=$POST['product_id'];
		$customer_id=$POST['customer_id'];
		$unit_id = $POST['unit_id'];

		$unitname = getunitname($dbcon,$unit_id);

		$query="select batch_no,stock_id from tbl_stock_trn as trn
		where trn.stock_status!=2 and stock_flage=1 and product_id=".$product_id." and trn.godown_id=".$godwn_id." and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";


			//echo $query;
		$str="";
		$result=$dbcon->query($query);
		

		if(mysqli_num_rows($result)>0)
		{	
			$str .= '<option value="">Select Batch Data</option>';
			$i=1;
			while($rel=brp_mysqli_fetch_assoc($result))
			{
				$gstock=0;$rstock=0;
					$batch_id=$POST['stock_id'];
					
					$gstock=get_current_godown_stock_new($dbcon,$product_id,$unit_id,$godwn_id,$branch_id,$batch_id,$customer_id);

					$rstock=reserve_stock($dbcon,$product_id,$unit_id,$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


					$stock=$gstock-$rstock;

				$str .= '<option value="'.$rel['stock_id'].'">'.$rel['batch_no'].' - (' . $stock . ' '. $unitname . ')</option>';
			}
		}else{
			$str .= '<option value="">No Batch Data !!</option>';
		}

		echo $str;
	}
	else if(brp_strtolower($POST['mode']) == "godown_stock") {
		$gstock=0;$rstock=0;
		$diff_gstock=0;$diff_rstock=0;$diff_stock=0;
		$batch_id=$POST['batch_id'];
		$gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$POST['unit_id'],$POST['st_godown_id'],$branch_id,$batch_id);

		$rstock=reserve_stock($dbcon,$POST['product_id'],$POST['unit_id'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);


		$stock=$gstock-$rstock;
			//var_dump($gstock);
			//var_dump($stock);
			//var_dump($gstock-$rstock);
		// echo $stock;

		$query = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id = " . $POST['product_id'];
					$row = brp_mysqli_fetch_assoc($dbcon->query($query));
		$res['stock'] = $stock;
					$diff_stock = 0;
					if($row['product_conv_unit'] == $row['product_base_unit']){
						$diff_stock = $stock;	
					}else if($POST['unit_id'] == $row['product_conv_unit']){
						$diff_gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$row['product_base_unit'],$POST['st_godown_id'],$branch_id,$batch_id,$customer_id);

						$diff_rstock=reserve_stock($dbcon,$POST['product_id'],$row['product_base_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);

						$diff_stock=$diff_gstock-$diff_rstock;
					}else{
						$diff_gstock=get_current_godown_stock_new($dbcon,$POST['product_id'],$row['product_conv_unit'],$POST['st_godown_id'],$branch_id,$batch_id,$customer_id);

						$diff_rstock=reserve_stock($dbcon,$POST['product_id'],$row['product_conv_unit'],$reserve_id,$request_id,$complaint_id,$sales_order_trn_id,$branch_id,$is_store_approval,$p_id,$POST['st_godown_id'],$batch_id);

						$diff_stock=$diff_gstock-$diff_rstock;
					}
					$res['diff_stock'] = $diff_stock;	
					
					echo json_encode($res);
	}
	else if(brp_strtolower($POST['mode']) == "fieldadd") {
		$info1['sales_ordertrn_id']	= $POST['sales_ordertrn_id'];
		$info1['reserve_qty']		= $POST['st_stock_reserve'];
		$info1['unit_id']			= $POST['unit_id'];
		$info1['godown_id']			= $POST['st_godown_id'];
		$info1['product_id']		= $POST['product_id'];
		$info1['stock_id']			= $POST['st_stock_id'];

		$info1['cdate']				= date('Y-m-d H:i:s');
		$info1['user_id']			= $_SESSION['user_id'];	
		$info1['company_id']		= $_SESSION['company_id'];	

		$inserpoid=add_record('work_order_reserve_temp',$info1, $dbcon, $branch_id);

		if($inserpoid){
			echo 1;
		}
	}
	else if(strtolower($POST['mode'])== "delete_data_stock")
	{
		$row=array();
		$info['status']=2;	
		$updateid=update_record("work_order_reserve_temp", $info, "work_order_reserve_temp_id=".$POST['eid'] , $dbcon);

		if($updateid)
			$row['res']="1";
		else
			$row['res']="0";
		echo json_encode($row);
	}
	
	
	else if(strtolower($POST['mode'])== "save_reserve_stock")
	{
		//start godown stock
		$query_rstock="select * from work_order_reserve_temp as i
		where i.status = 0 and i.sales_ordertrn_id =".$POST['sales_ordertrn_id'];
		$result_rstock=$dbcon->query($query_rstock);
		while($row_rstock=brp_mysqli_fetch_assoc($result_rstock)){
			$reserve_qty=$row_rstock['reserve_qty'];
			$batch_where="";
			if(!empty($row_rstock['stock_id'])){
				$batch_where=" and i.stock_id=".$row_rstock['stock_id'];
			}
			 $query_dstock="select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
			where stock_status!=2 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) ".$batch_where." and i.product_id=".$row_rstock['product_id']." and i.godown_id=".$row_rstock['godown_id'];
			$result_dstock=$dbcon->query($query_dstock);
			while($row_dstock=brp_mysqli_fetch_assoc($result_dstock)){
				if($row_dstock['convert_unit']==$row_rstock['unit_id']){
					$pending_stock=$row_dstock['pending_conv_stock'];
				}else{
					$pending_stock=$row_dstock['pending_base_stock'];	
				}
				if($reserve_qty>0){
					if($pending_stock>=$reserve_qty){
						$rqty=$reserve_qty;
						$reserve_qty=$reserve_qty-$reserve_qty;
					}else{
						$rqty=$pending_stock;
						$reserve_qty=$reserve_qty-$pending_stock;
					}

					$que="select * from product_mst as ta where product_id=".$row_rstock['product_id'];
					$rs_di=$dbcon->query($que);
					$re=brp_mysqli_fetch_assoc($rs_di);


					if($re['product_conv_unit']==$row_rstock['unit_id']){
						$type="base_unit";
						$con_stock=$rqty;
						$base_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
					}else{
						$type="conv_unit";
						$base_stock=$rqty;
						$con_stock=convert_stock_new($dbcon,$rqty,$row_rstock['product_id'],$type);
					}

					
					$info_rese['reserve_date']		= date('Y-m-d');
					$info_rese['product_id']		= $row_rstock['product_id'];
					$info_rese['godown_id']			= $row_dstock['godown_id'];
					$info_rese['base_unit']			= $re['product_base_unit'];
					$info_rese['base_stock']		= $base_stock;
					$info_rese['convert_unit']		= $re['product_conv_unit'];
					$info_rese['convert_stock']		= $con_stock;
					$info_rese['stock_flage']		= "1";
					$info_rese['request_id']		= $row_rstock['rp_id'];
					$info_rese['ref_name']			= "wo_allocate";
					$info_rese['ref_id']			= "0";
					$info_rese['sales_order_trn_id']= $row_rstock['sales_ordertrn_id'];
					$info_rese['stock_id']			= $row_dstock['stock_id'];

					$info_rese['cdate']					= date("Y-m-d H:i:s");
					$info_rese['user_id']				= $_SESSION['user_id'];
					$info_rese['company_id']			= $_SESSION['company_id'];		
										
					$reserve_id_id=add_record('tbl_reserve_stock',$info_rese, $dbcon,$row_dstock['branch_id']);
					
					$wo_res_temp_info['status'] = 3;
                                
                    $updatetrnid=update_record('work_order_reserve_temp',$wo_res_temp_info,"work_order_reserve_temp_id=".$row_rstock['work_order_reserve_temp_id'] , $dbcon);
					
					if($row_dstock['base_unit']==$re['product_base_unit']){
						$used_base_stock=$row_dstock['used_base_stock']+$base_stock;
						$used_convert_stock=$row_dstock['used_convert_stock']+$con_stock;
					}else{
						$used_base_stock=$row_dstock['used_convert_stock']+$con_stock;
						$used_convert_stock=$row_dstock['used_base_stock']+$base_stock;
					}

					$info_stock['used_base_stock']		= $used_base_stock;
					$info_stock['used_convert_stock']	= $used_convert_stock;
					
					$updatetrnid=update_record('tbl_stock_trn',$info_stock,"stock_id=".$row_dstock['stock_id'], $dbcon);

					$info_e['sales_ordertrn_id']	=$row_rstock['sales_ordertrn_id'];
					$info_e['product_id']			=$row_rstock['product_id'];
					$info_e['product_qty']			=$info_rese['base_stock'];
					$info_e['godown_id']			=$info_rese['godown_id'];
					$info_e['unit_id']				=$info_rese['base_unit'];
					$info_e['allocate_qty']			=$info_rese['base_stock'];
					$info_e['remaning_invoice_qty']	=$info_rese['base_stock'];
					
					$info_e['cdate']				=date("Y-m-d");
					$info_e['company_id']			=$_SESSION['company_id'];
					$info_e['user_id']				=$_SESSION['user_id'];
					$inserinvoiceidexp=add_record('tbl_sales_order_production_trn', $info_e, $dbcon,$row_dstock['branch_id']);
					update_salesorder_qty_and_status($dbcon,$POST['sales_ordertrn_id']);
				}
			}
		}
		
	}



function update_salesorder_qty_and_status($dbcon,$sales_ordertrn_id){
	 $que="select product_qty from tbl_sales_ordertrn where sales_ordertrn_id=".$sales_ordertrn_id;
	$rs_di=$dbcon->query($que);
	$re=brp_mysqli_fetch_assoc($rs_di);

	 $que1="select sum(product_qty) as product_qty from tbl_sales_order_production_trn where  sales_order_production_status = 0 and sales_ordertrn_id=".$sales_ordertrn_id ." group by sales_ordertrn_id";
	$rs_di1=$dbcon->query($que1);
	$re1=brp_mysqli_fetch_assoc($rs_di1);

	$so_qty = (float)$re['product_qty'];
	$done_qty = (float)$re1['product_qty'];
	if($done_qty >= $so_qty){
		$info['bom_status'] =  1 ;
		$updatetrnid=update_record('tbl_sales_ordertrn',$info,"sales_ordertrn_id=".$sales_ordertrn_id, $dbcon);	
	}
	
}
?>

