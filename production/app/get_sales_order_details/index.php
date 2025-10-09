<?php
session_start(); //start session
$AJAX = true;
include ('../../include/urlfileinner.php');
//check permission for get sales order details
// error_reporting(E_ALL);
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	MRP_GET_SALES_ORDER_SLUG_VIEW,
	MRP_GET_SALES_ORDER_SLUG_CREATE
]);
$companyConfiguration = getCompanyConfiguration($dbcon);

$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search = explode(",", $production_pro_search);
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ {
	/*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */ {
		//print_r($_POST);
		if ($_POST != NULL) {
			$POST = bulk_filter($dbcon, $_POST);
		} else {
			$POST = bulk_filter($dbcon, $_GET);
		}

		if (strtolower($POST['mode']) == "generate_report_min_new") {

			/*$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
				  $delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);*/

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			//$where_db = check_branch('so_trn', $branch_id);
			$where_db='';
			if (!empty($branch_id)) {
				$pro_branch = " and so_trn.production_branch_id=" . $branch_id;
			}

			$appData = array();
			$i = 1;
			//+IFNULL(qc_total_rejected,0)

			$aColumns = array('mst.product_icode','so_trn.bom_id', 'dr.drawing_number', 'so.sales_order_no', 'so.sales_order_date', 'led.l_name', 'so_trn.product_qty', 'so_trn.priority_status', 'so_trn.sales_ordertrn_id', 'mst.product_name', 'uns.unit_name', 'tc.cat_name', 'so.delivery_date', 'bran.branch_name', 'so_trn.product_id', 'so_trn.work_order_qty', 'so_trn.unit_id', '(IFNULL(product_qty,0)-IFNULL(stock_add,0)) as pending_qty', 'so.jobwork_type', 'mst.bom_required', 'so_trn.production_branch_id', 'so.cust_id', 'so_trn.description', 'so.cust_id');


			$sIndexColumn = "so_trn.sales_ordertrn_id";
			$isWhere = array("so_trn.sales_ordertrn_status=0 and so_trn.bom_status=1 and so_trn.production_status=0 and  so.order_accept_status = 1 and so_trn.short_close_status=0 and so_trn.invoice_status=0 and so_trn.production_branch_id!=0 and so.company_id = " . $_SESSION['company_id'] . " and so_trn.with_out_stock_invoice=0 and so.approve_status=3" . $where_db . $pro_branch);

			$sTable = "tbl_sales_ordertrn as so_trn";

			$isJOIN = array(
				"left join tbl_sales_order as so on so.sales_order_id=so_trn.sales_order_id",
				"left join tbl_ledger as led on led.l_id=so.cust_id",
				"left join product_mst as mst on mst.product_id=so_trn.product_id",
				"left join unit_mst as uns on uns.unitid=so_trn.unit_id",
				"left join tbl_category as tc on mst.product_category=tc.cat_id", "left join (select IFNULL(sum(qc.product_qty),0) as stock_add,qc.sales_ordertrn_id from tbl_sales_order_production_trn as qc 
			where qc.sales_order_production_status=0 group by qc.sales_ordertrn_id) as qc on qc.sales_ordertrn_id=so_trn.sales_ordertrn_id",
				"left join branch_mst as bran on bran.branch_id=so_trn.branch_id",
				"left join tbl_drawing as dr on dr.drawing_id = mst.drawing_id"
			);

			$hOrder = "so.delivery_date desc";
			//$hGroupby = "pro.product_id";
			$having = " pending_qty > 0";
			include ($include . 'pagging.php');
			$appData = array();
			$id = 1;

			// print_r($sqlReturn);
			foreach ($sqlReturn as $row) {

				$customer_id = "";
				if ($row['jobwork_type'] == '1') {
					$customer_id = $row['cust_id'];
				}

				$row_data = array();
				//tbl_sales_order_production_trn
				//$pendingqty=$row['product_qty']-$row['work_order_qty'];
				$pendingqty = $row['pending_qty'];

				$cstock = get_current_stock_new($dbcon, $row["product_id"], $row["unit_id"], "", $customer_id);
				$rstock = reserve_stock($dbcon, $row["product_id"], $row["unit_id"], "", "", "", "", "", "", "", "", "", $customer_id);
				$wipstock = wipstock($dbcon, $row["product_id"], $row["unit_id"], "", $customer_id);
				$actualstock = $cstock - $rstock;
				$actualstock = $actualstock + $wipstock;
				$row_data[] = '<input type="checkbox" chk name="chk[]" data-soid="' . $row['sales_ordertrn_id'] . '" data-bomid="' . $row['bom_id'] . '" data-branchid="' . $row['production_branch_id'] . '"  value="' . $row['product_id'] . '"/>';
				$row_data[] = $row['sales_order_no'];
				$row_data[] = date('d-m-Y', strtotime($row["sales_order_date"]));

				if ($companyConfiguration['customer_show_in_production'] == '1') {
					$row_data[] = $row['l_name'];
				}

				$drawing_number = "";
				$item_code = "";
				if (in_array('drawing', $pro_search)) {
					$drawing_number = " -- (" . $row['drawing_number'] . ")";
				}
				if (in_array('item', $pro_search)) {
					$item_code = " -- (" . $row['product_icode'] . ")";
				}

				$row_data[] = $row['product_name'] . ' ' . $item_code . ' ' . $drawing_number;
				$row_data[] = ($row['cat_name'] != null) ? $row['cat_name'] : 'PRIMARY';
				$row_data[] = $row['product_qty'] . ' ' . $row['unit_name'];
				$row_data[] = $pendingqty . ' ' . $row['unit_name'];
				;
				$row_data[] = $actualstock . ' ' . $row['unit_name'];
				;
				$row_data[] = date('d M, Y', strtotime($row["delivery_date"]));
				if ($pendingqty >= $actualstock) {
					$validateqty = $actualstock;
				} else {
					$validateqty = $pendingqty;
				}

				$view = '';
				$view1 = "";
				$apprv_btn = '';
				$stock_allocate = '';
				$view_desc = '';
				if (in_array(MRP_GET_SALES_ORDER_SLUG_CREATE, $bulkAccessArray)) {

					if ($companyConfiguration['trading_stock'] == 0) {
						if ($row['bom_required'] == '1') {
							$view1 = '<a class="btn btn-xs btn-primary" data-original-title="Create Workorder" data-toggle="tooltip" data-placement="top" href="' . ROOT . 'production/sorequesproduct/' . $row['product_id'] . '/' . $row['sales_ordertrn_id'] . '"><i class="fa fa-paper-plane"></i> Create Workorder</a>';
						}
					}

					// if($row['bom_required'] == '0'){
					$view = '<button type="button" class="btn btn-xs btn-primary" data-original-title="Create Indent" data-toggle="tooltip" data-placement="top" onClick="open_create_workorder_modal(' . $row['product_id'] . ',' . $row["sales_ordertrn_id"] . ',\'' . $pendingqty . '\',' . $row['production_branch_id'] . ',' . $row['cust_id'] . ')"><i class="fa fa-dot-circle-o"></i> Create Indent</button>';
					// }else{

					if ($companyConfiguration['design_department'] == 0) {
						// check default bom exist 
						$ver_qry = "select bom_version_id from pro_ms_bom_version where product_id = " . $row['product_id'] . " and is_default_bom = 1 and bom_version_status = 0 and company_id = " . $_SESSION['company_id'];

						$res_ver = $dbcon->query($ver_qry);
						$ver_rel_q = brp_mysqli_fetch_assoc($res_ver);

						if (brp_mysqli_num_rows($res_ver) == 0) {
							$view = '<button type="button" class="btn btn-xs btn-primary" data-original-title="Create Indent" data-toggle="tooltip" data-placement="top" onClick="open_create_workorder_modal(' . $row['product_id'] . ',' . $row["sales_ordertrn_id"] . ',' . $pendingqty . ',' . $row['production_branch_id'] . ',' . $row['cust_id'] . ')"><i class="fa fa-dot-circle-o"></i> Create Indent</button>';
						}

					}

					// }



					$sno = "'" . $row['sales_order_no'] . "'";
					$pno = "'" . $row['product_name'] . "'";
					//$apprv_btn='<button type="button" class="btn btn-xs btn-success" data-original-title="Alloca" data-toggle="tooltip" data-placement="top" onClick="open_approv_quo1('.$sno.','.$pno.','.$row["sales_ordertrn_id"].','.$row["product_id"].','.$pendingqty.')"><i class="fa fa-exclamation-triangle"></i></button>';
					if ($actualstock > 0) {
						$stock_allocate = '<button type="button" class="btn btn-xs btn-success" data-original-title="Allocate Stock" data-toggle="tooltip" data-placement="top" onClick="open_stock_allocation_so(' . $row["sales_ordertrn_id"] . ',' . $validateqty . ',\'' . $row['unit_name'] . '\')">Allocate Stock</button>';
					}
				}
				if ($companyConfiguration['outside_jobwork']) {

					if ($row['jobwork_type'] == '0') {
						$row_data[] = '<button class="btn btn-xs btn-success" data-original-title="Normal Jobwork" data-toggle="tooltip" data-placement="top">Normal</button>';

					} else {
						$row_data[] = '<button class="btn btn-xs btn-danger" data-original-title="Outside Jobwork" data-toggle="tooltip" data-placement="top">Outside Jobwork</button>';

					}
				}

				if ($_SESSION['branch_id'] == 0) {
					$row_data[] = $row['branch_name'];
				}
				/*$view_desc='';
						 if(!empty($row['description'])){*/
				$view_desc = '<button class="btn btn-xs btn-primary" data-original-title="Sales Order Detail" data-toggle="tooltip" data-placement="top" type="button" onclick="open_so_trn_modal(' . $row['sales_ordertrn_id'] . ')"><i class="fa fa-eye"></i></button>';
				//}
				$row_data[] = $row['priority_status'];
				$row_data[] = $view1 . ' ' . $view . ' ' . $apprv_btn . ' ' . $stock_allocate . ' ' . $view_desc;

				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode($output);

		} else if (strtolower($POST['mode']) == "load_entry_stock") {
			$q = "select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=" . $POST['ref_sales_order_trn_id'];
			$rel = $dbcon->query($q);
			//$str=array();

			$row = mysqli_fetch_array($rel);
			$godown = get_godown_stock_so($dbcon, $row['product_id'], $row['unit_id']);
			$work_order = get_min_max_work_order_stock($dbcon, $row['product_id']);
			if ($companyConfiguration['trading_stock'] == 0) {
				$html = "
			<div class='col-md-5' > 
			" . $godown . "
			</div>
			<div class='col-md-7' >
			" . $work_order . "
			</div>
			<div class='col-md-12'>
			<center>
			<button type='submit' class='btn btn-success' id='save' name='save'>Save</button>
			</center>
			</div>
			";
			} else {
				$html = "
			<div class='col-md-12' > 
			" . $godown . "
			</div>
			<div class='col-md-12'>
			<center>
			<button type='submit' class='btn btn-success' id='save' name='save'>Save</button>
			</center>
			</div>
			";

			}

			echo $html;
		} else if (strtolower($POST['mode']) == "get_product_name") {
			echo get_product_name($dbcon, $POST['product_id']);
		} else if (strtolower($POST['mode']) == "add") {
			$q = "select * from tbl_sales_ordertrn as gd where sales_ordertrn_status=0 and sales_ordertrn_id=" . $POST['ref_sales_order_trn_id'];
			$rel = $dbcon->query($q);

			$row = mysqli_fetch_array($rel);
			foreach ($POST['so_godown'] as $i => $name) {
				$godwn_id = $POST['so_godown'][$i];
				$stock = $POST['so_stock'][$i];
				if ($stock > 0) {
					$info_e['sales_ordertrn_id'] = $row['sales_ordertrn_id'];
					$info_e['product_id'] = $row['product_id'];
					$info_e['product_qty'] = $stock;
					$info_e['godown_id'] = $godwn_id;
					$info_e['unit_id'] = $row['unit_id'];
					$info_e['allocate_qty'] = $stock;
					$info_e['remaning_invoice_qty'] = $stock;

					$info_e['cdate'] = date("Y-m-d");
					$info_e['company_id'] = $_SESSION['company_id'];
					$info_e['user_id'] = $_SESSION['user_id'];
					$inserinvoiceidexp = add_record('tbl_sales_order_production_trn', $info_e, $dbcon, $row['branch_id']);
					add_so_reserve_stock($dbcon, $stock, $row['unit_id'], $row['product_id'], $row['sales_ordertrn_id'], $godwn_id, "", $row['branch_id']);
				}

			}

			foreach ($POST['so_req_id'] as $p => $name1) {
				$request_id = $POST['so_req_id'][$p];
				$stock_alo = $POST['so_working_stock'][$p];
				if ($stock_alo > 0) {
					$info_w['sales_ordertrn_id'] = $row['sales_ordertrn_id'];
					$info_w['product_id'] = $row['product_id'];
					$info_w['product_qty'] = $stock_alo;
					$info_w['request_id'] = $request_id;
					$info_w['unit_id'] = $row['unit_id'];

					$info_w['cdate'] = date("Y-m-d");
					$info_w['company_id'] = $_SESSION['company_id'];
					$info_w['user_id'] = $_SESSION['user_id'];
					$inserinvoiceidexp1 = add_record('tbl_sales_order_production_trn', $info_w, $dbcon, $row['branch_id']);
				}

			}

			if ($inserinvoiceidexp || $inserinvoiceidexp1) {
				$arr['msg'] = "1";
			} else {
				$arr['msg'] = "0";
			}
			echo json_encode($arr);

		} else if (strtolower($POST['mode']) == "set_version") {
			$product_id = $_POST['product_id'];
			$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
			$qty = $_POST['qty'];

			$check_sales_order = "select * from tbl_sales_ordertrn where sales_ordertrn_id = '$sales_ordertrn_id' AND bom_id ='0' AND bom_status = '1' ";
			$check_sales_order_res = $dbcon->query($check_sales_order);
			if (brp_mysqli_num_rows($check_sales_order_res) > 0) {
				$product_bom_query = "select * from tbl_bom where bom_version_id IN (SELECT bom_version_id FROM `pro_ms_bom_version` WHERE  is_default_bom = '1' AND  product_id=" . $_POST['product_id'] . ")";
				if (brp_mysqli_num_rows($product_bom_query) > 0) {

					$product_bom_row = brp_mysqli_fetch_assoc($dbcon->query($product_bom_query));
					$info['bom_id'] = $product_bom_row['bom_id'];
					$info['bom_status'] = $product_bom_row['bom_status'];
					$updateid = update_record('tbl_sales_ordertrn', $info, "sales_ordertrn_id=" . $_POST['sales_ordertrn_id'], $dbcon, $POST['branch_id']);
				} else {

					$info['bom_status'] = 0;
					$updateid = update_record('tbl_sales_ordertrn', $info, "sales_ordertrn_id=" . $_POST['sales_ordertrn_id'], $dbcon, $POST['branch_id']);
					add_requst_rnd($dbcon, $product_id, $sales_ordertrn_id, $qty,'');
				}

				echo "1";

			} else {
				echo "0";
			}


		} else if (strtolower($POST['mode']) == "ger_version_by_product") {

			$product_id = $_POST['product_id'];
			$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
			$qty = $_POST['qty'];



			$qry = "SELECT * from pro_ms_bom_version where product_id=" . $POST['product_id'];
			$result = $dbcon->query($qry);

			$versionstr = '';

			if (brp_mysqli_num_rows($result) > 0) {
				while ($row = brp_mysqli_fetch_assoc($result)) {
					$versionstr .= '<option value="' . $row['bom_version_id'] . '">' . $row['version_name'] . '</option>';
				}
				$versionstr .= '<option selected="selected"  value="10000">R&D</option>';
			} else {
				$versionstr .= '<option selected="selected"  value="10000">R&D</option>';
			}

			$str = '<table class="table table-bordered">	<tr>
		<th colspan="2" style="text-align: center;"> <strong>Bom Version:</strong></th>
		<th colspan="3"><select class="select2 selproduct1" title="Select Bom Version" name="add_bom_version_id" id="add_bom_version_id">' . $versionstr . '</select>
		</th></tr><th colspan="5"  style="text-align: center;"><button type="button" onclick="product_custom_versions(' . $product_id . ',' . $sales_ordertrn_id . ',' . $qty . ');" class="btn btn-success" id="save" name="save">Save</button></th></tr></table>';
			echo $str;
		} else if (strtolower($POST['mode']) == "set_custom_version") {
			$product_id = $_POST['product_id'];
			$sales_ordertrn_id = $_POST['sales_ordertrn_id'];
			$version_id = $_POST['version_id'];
			$qty = $_POST['qty'];

			if ($version_id == "10000") {
				$info['bom_status'] = 0;
				$updateid = update_record('tbl_sales_ordertrn', $info, "sales_ordertrn_id=" . $_POST['sales_ordertrn_id'], $dbcon, $POST['branch_id']);
				add_requst_rnd($dbcon, $product_id, $sales_ordertrn_id, $qty, $version_id);
				echo "1";
			} else {
				echo $product_bom_query = "select * from tbl_bom where bom_version_id ='$version_id' AND  bom_product=" . $_POST['product_id'];
				$product_bom_res = $dbcon->query($product_bom_query);
				if (brp_mysqli_num_rows($product_bom_res) > 0) {
					$product_bom_row = brp_mysqli_fetch_assoc($dbcon->query($product_bom_query));
					$info['bom_id'] = $product_bom_row['bom_id'];
					$info['bom_status'] = $product_bom_row['bom_status'];

					$updateid = update_record('tbl_sales_ordertrn', $info, "sales_ordertrn_id=" . $_POST['sales_ordertrn_id'], $dbcon, $POST['branch_id']);

					add_requst_rnd($dbcon, $product_id, $sales_ordertrn_id, $qty, $version_id);

					echo "1";
				} else {
					echo "0";
				}
			}

		} else if (brp_strtolower($POST['mode']) == "show_stock_new") {

			$que_so = "select * from tbl_sales_ordertrn where sales_ordertrn_id=" . $POST['sales_order_trn_id'];
			$resi_so = $dbcon->query($que_so);
			$re_so = brp_mysqli_fetch_assoc($resi_so);

			$branch_id = $re_so['branch_id'];
			$product_id = $re_so['product_id'];
			$unit_id = $re_so['rate_unit'];
			//$rp_id=$POST['rp_id'];
			$unit_name = getunitname($dbcon, $unit_id);
			$diff_unit_name = "";
			$que_po = "select batch_wise_stock_manage,product_conv_unit,product_base_unit from product_mst where product_id=" . $product_id;
			$resi_grn = $dbcon->query($que_po);
			$re = brp_mysqli_fetch_assoc($resi_grn);

			$function = 'onkeyup="reserve_stock_convert_qty(1);"';
			if ($re['product_conv_unit'] == $re['product_base_unit']) {
				$diff_unit_name = $unit_name;
			} else if ($re['product_conv_unit'] == $unit_id) {
				$diff_unit_name = getunitname($dbcon, $re['product_base_unit']);
			} else {
				$diff_unit_name = getunitname($dbcon, $re['product_conv_unit']);
				$function = 'onkeyup="reserve_stock_convert_qty(2);"';
			}

			//$god_stock=req_stock_entry();
			//$wipstock=req_wipstock_entry();
			$str = ' 
		<div class="col-md-12" style="font-size: 25px;"><center><strong>Warehouse Stock</strong></center></div>
		<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
		<tr>
		<td style="font-weight: 600;">Warehouse</td>';
			if ($re['batch_wise_stock_manage'] == 1) {
				$str .= '<td style="font-weight: 600;">Batch No</td>';
			}
			$str .= '<td style="font-weight: 600;">Stock</td>
		<td style="font-weight: 600;">Reserve Stock</td>
		<td style="font-weight: 600;">Action</td>
		</tr>
		<tr>';
			if ($re['batch_wise_stock_manage'] == 1) {
				$str .= '<td>
			<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();load_batch_no();">
			' . load_available_stock_godown($dbcon, $product_id, $branch_id) . '
			</select>
			</td>
			<td>
			<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" onchange="load_godown_wise_stock();">
			</select>
			</td>';
			} else {
				$str .= '<td>
			<select class="form-control"  title="Select" placeholder="" name="st_godown_id" id="st_godown_id" onchange="load_godown_wise_stock();">
			' . load_available_stock_godown($dbcon, $product_id, $branch_id) . '
			</select>
			</td>
			<!--<td>
			<select class="form-control"  title="Select" placeholder="" name="st_stock_id" id="st_stock_id" >
			</select>
			</td>-->';
			}
			$str .= '<td>
		<div class="col-md-9">
									 <input type="number"  title="Stock" min="0" id="st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> ' . $unit_name . ' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									  	<input type="number"  title="Stock" min="0" id="diff_st_stock_total" name="st_stock_total"  class="form-control numbersOnly" readonly />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> ' . $diff_unit_name . ' </span>
									 </div>
								</td>
								<td>
									 
									 <div class="col-md-9">
									 <input type="number"  title="Enter Stock" min="0" id="st_stock_reserve" name="st_stock_reserve" ' . $function . ' class="form-control numbersOnly"  />
									 </div>
									 <div class="col-md-3" style="margin-top:5px">
									 	<span> ' . $unit_name . ' </span>
									 </div>
									 <div class="col-md-9" style="margin-top:5px">
									  	<input type="number"  title="Enter Stock" min="0" id="diff_st_stock_reserve" name="st_stock_reserve" readonly class="form-control numbersOnly"  />
									  </div>
									  <div class="col-md-3"  style="margin-top:10px">
									 	<span> ' . $diff_unit_name . ' </span>
									 </div>
								</td>
		<td>
		<input type="button"  name="addrow" id="addrow" onClick="return add_reserve_temp();"  class="btn btn-primary" value="Add"/>
		</td>
		</tr>
		</table>
		<input type="hidden" name="batch_wise_stock_manage" id="batch_wise_stock_manage" value="' . $re['batch_wise_stock_manage'] . '" />
		<div id="sale_productdata"></div>

		<div class="col-md-12" style="font-size: 25px;"><center><strong>WIP Stock</strong></center></div>';

			$query = "select IFNULL(sum(trn.allocate_base_qty-trn.allocate_base_qty_used),0) as stock_qty,wip.indent_no,wip.indent_date,wip.job_card_no,wip.job_card_date,setp.po_req_date,setp.po_req_no,trn.type_flag,trn.wip_stock_allocate_id from wip_stock_allocate as trn
		left join tbl_request_product as wip on wip.rp_id=trn.rp_id
		left join tbl_set_main_process as setp on setp.sp_id=wip.sp_id
		where trn.status=0 and allocate_base_qty>allocate_base_qty_used and trn.company_id=" . $_SESSION['company_id'] . " and wip.rp_pid=" . $product_id . "";

			$result = $dbcon->query($query);
			if (mysqli_num_rows($result) > 0) {
				$str .= '<table cellspacing="10" style=" border-spacing:10px;" class="display table table-bordered table-striped" id="product_list">
			<tr>
			<td style="font-weight: 600;">Indent/Job Card/Work Order</td>
			<td style="font-weight: 600;">Date</td>
			<td style="font-weight: 600;">Pending Qty</td>
			<td style="font-weight: 600;">Reserve Stock</td>
			</tr>';

				$i = 1;
				while ($rel = brp_mysqli_fetch_assoc($result)) {
					$stockqty = $rel['stock_qty'];

					if ($rel['type_flag'] == 1) {
						$refno = $rel['indent_no'];
						$refdate = $rel['indent_date'];
					} else if ($rel['type_flag'] == 2) {
						$refno = $rel['job_card_no'];
						$refdate = $rel['job_card_date'];
					} else if ($rel['type_flag'] == 3) {
						$refno = $rel['po_req_no'];
						$refdate = $rel['po_req_date'];
					}

					$str .= '<tr>
				<td style="font-weight: 600;">' . $refno . '</td>
				<td style="font-weight: 600;">' . $refdate . '</td>
				<td style="font-weight: 600;">' . $stockqty . '</td>
				<td style="font-weight: 600;">
				<input type="number"  title="Enter Stock" min="0" max="' . $stockqty . '" id="wip_stock_reserve' . $rel['wip_stock_allocate_id'] . '" name="wip_stock_reserve[]"  class="form-control numbersOnly wip_res_stock"  />
				<input type="hidden" class="wip_stock_id" name="wip_stock_allocate_id[]" id="wip_stock_allocate_id' . $rel['wip_stock_allocate_id'] . '" value="' . $rel['wip_stock_allocate_id'] . '" />
				</td>
				</tr>';
				}
				$str .= '</table>';
			}

			$str .= '<div class="col-md-12" >
		<center>
		<input type="button"  name="" id="" onClick="return save_reserve_stock();"  class="btn btn-primary" value="Save"/>

		<input type="hidden" name="product_id_model" id="product_id_model" value="' . $product_id . '" />
		<input type="hidden" name="unit_id_model" id="unit_id_model" value="' . $unit_id . '" />
		
		</center>
		</div>
		';


			echo $str;
		} else if (strtolower($POST['mode']) == "load_tempoutward") {


			$query = "select trn.work_order_reserve_temp_id,trn.reserve_qty,cat.gd_name,uns.unit_name,st.batch_no from work_order_reserve_temp as trn
		left join mst_godown as cat on cat.gd_id=trn.godown_id
		left join unit_mst as uns on uns.unitid=trn.unit_id
		left join tbl_stock_trn as st on st.stock_id=trn.stock_id
		where trn.status=0 and trn.sales_ordertrn_id=" . $POST['sales_ordertrn_id'];

			//echo $query;
			$result = $dbcon->query($query);
			echo '<div class="form-group">
		<div class="col-md-12 col-xs-11">
		<table cellspacing="10" style="border-spacing:10px;" class="display table table-bordered table-striped">
		<tr id="field">
		<th class="text-center" width="10%">Warehouse</th>';
			if ($POST['batch_wise_stock_manage'] == 1) {
				echo '<th class="text-center"width="15%">Batch No</th>';
			}
			echo '<th class="text-center"width="15%">Reserve Stock</th>
		<th class="text-center"width="10%">Action</th>
		</tr>';

			//echo $query;
			if (mysqli_num_rows($result) > 0) {
				$i = 1;
				$total = 0;
				while ($rel = brp_mysqli_fetch_assoc($result)) {

					echo '<tr id="fieldtr' . $i . '">
				<td style="vertical-align:top;" class="text-left">
				' . $rel['gd_name'] . '
				</td>';
					if ($POST['batch_wise_stock_manage'] == 1) {
						echo '<td style="vertical-align:top;" class="text-left">
					' . $rel['batch_no'] . '
					</td>';
					}
					echo '<td style="vertical-align:top;" class="text-center">
				' . $rel['reserve_qty'] . ' ' . $rel['unit_name'] . '
				</td>					

				<td style="vertical-align:top">

				<!--<button type="button" class="btn btn-round btn-warning btn-xs" onclick="edit_data(' . $rel['purchaseordertrn_id'] . ',\'tbl_purchaseordertrn\',\'purchaseordertrn_id\');" id="fieldedit' . $i . '"><i class="fa fa-pencil"></i></button>-->

				<button type="button" class="btn btn-round btn-danger btn-xs" onclick="delete_data_stock(' . $rel['work_order_reserve_temp_id'] . ');" id="fieldremove' . $i . '"><i class="fa fa-times"></i></button>
				</td>	
				</tr>';
					$total = $total + $rel['reserve_qty'];
					$i++;
				}
			} else {
				echo '<tr><td colspan="12" class="text-center">NO DATA FOUND</td></tr>';
			}
			echo '</table> 
		<input type="hidden" name="gstock_total" id="gstock_total" value="' . $total . '" />
		</div>
		</div>';
		} else if (strtolower($POST['mode']) == "load_batch_no") {

			$godwn_id = $POST['godwn_id'];
			$product_id = $POST['product_id'];
			$customer_id = $POST['customer_id'];
			$unit_id = $POST['unit_id'];

			$unitname = getunitname($dbcon, $unit_id);

			$query = "select batch_no,stock_id from tbl_stock_trn as trn
		where trn.stock_status=0 and stock_flage=1 and product_id=" . $product_id . " and trn.godown_id=" . $godwn_id . " and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))";

			//echo $query;
			$str = "";
			$result = $dbcon->query($query);
			if (mysqli_num_rows($result) > 0) {
				$str .= '<option value="">Select Batch Data</option>';
				$i = 1;
				while ($rel = brp_mysqli_fetch_assoc($result)) {
					$gstock = 0;
					$rstock = 0;
					$batch_id = $POST['stock_id'];

					$gstock = get_current_godown_stock_new($dbcon, $product_id, $unit_id, $godwn_id, $branch_id, $batch_id, $customer_id);

					$rstock = reserve_stock($dbcon, $product_id, $unit_id, $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $POST['st_godown_id'], $batch_id);


					$stock = $gstock - $rstock;

					$str .= '<option value="' . $rel['stock_id'] . '">' . $rel['batch_no'] . ' - (' . $stock . ' ' . $unitname . ')</option>';
				}
			} else {
				$str .= '<option value="">No Batch Data !!</option>';
			}

			echo $str;
		} else if (brp_strtolower($POST['mode']) == "godown_stock") {
			$gstock = 0;
			$rstock = 0;
			$diff_gstock = 0;
			$diff_rstock = 0;
			$diff_stock = 0;
			$batch_id = $POST['batch_id'];
			$gstock = get_current_godown_stock_new($dbcon, $POST['product_id'], $POST['unit_id'], $POST['st_godown_id'], $branch_id, $batch_id);

			$rstock = reserve_stock($dbcon, $POST['product_id'], $POST['unit_id'], $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $POST['st_godown_id'], $batch_id);


			$stock = $gstock - $rstock;
			//var_dump($gstock);
			//var_dump($stock);
			//var_dump($gstock-$rstock);
			// echo $stock;

			$query = "SELECT product_base_unit,product_conv_unit FROM product_mst WHERE product_id = " . $POST['product_id'];
			$row = brp_mysqli_fetch_assoc($dbcon->query($query));
			$res['stock'] = $stock;
			$diff_stock = 0;
			if ($row['product_conv_unit'] == $row['product_base_unit']) {
				$diff_stock = $stock;
			} else if ($POST['unit_id'] == $row['product_conv_unit']) {
				$diff_gstock = get_current_godown_stock_new($dbcon, $POST['product_id'], $row['product_base_unit'], $POST['st_godown_id'], $branch_id, $batch_id, $customer_id);

				$diff_rstock = reserve_stock($dbcon, $POST['product_id'], $row['product_base_unit'], $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $POST['st_godown_id'], $batch_id);

				$diff_stock = $diff_gstock - $diff_rstock;
			} else {
				$diff_gstock = get_current_godown_stock_new($dbcon, $POST['product_id'], $row['product_conv_unit'], $POST['st_godown_id'], $branch_id, $batch_id, $customer_id);

				$diff_rstock = reserve_stock($dbcon, $POST['product_id'], $row['product_conv_unit'], $reserve_id, $request_id, $complaint_id, $sales_order_trn_id, $branch_id, $is_store_approval, $p_id, $POST['st_godown_id'], $batch_id);

				$diff_stock = $diff_gstock - $diff_rstock;
			}
			$res['diff_stock'] = $diff_stock;

			echo json_encode($res);
		} else if (brp_strtolower($POST['mode']) == "fieldadd") {
			$info1['sales_ordertrn_id'] = $POST['sales_ordertrn_id'];
			$info1['reserve_qty'] = $POST['st_stock_reserve'];
			$info1['unit_id'] = $POST['unit_id'];
			$info1['godown_id'] = $POST['st_godown_id'];
			$info1['product_id'] = $POST['product_id'];
			$info1['stock_id'] = $POST['st_stock_id'];

			$info1['cdate'] = date('Y-m-d H:i:s');
			$info1['user_id'] = $_SESSION['user_id'];
			$info1['company_id'] = $_SESSION['company_id'];

			$inserpoid = add_record('work_order_reserve_temp', $info1, $dbcon, $branch_id);

			if ($inserpoid) {
				echo 1;
			}
		} else if (strtolower($POST['mode']) == "delete_data_stock") {
			$row = array();
			$info['status'] = 2;
			$updateid = update_record("work_order_reserve_temp", $info, "work_order_reserve_temp_id=" . $POST['eid'], $dbcon);

			if ($updateid)
				$row['res'] = "1";
			else
				$row['res'] = "0";
			echo json_encode($row);
		} else if (strtolower($POST['mode']) == "create_workorder") {

			$product_id = $POST['so_product_id'];
			$branch_id = $POST['production_branch_id'];
			$sales_ordertrn_id = $POST['sales_ordertrn_id'];

			$query1 = "select * from  tbl_invoicetype where type_id='9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
			$rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
			$id = $rows['taxinvoice_start'];
			$id = $id + 1;

			$new_query1 = "update tbl_invoicetype set taxinvoice_start = " . $id . " where type_id='9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
			$dbcon->query($new_query1);

			if ($rows['invoice_format'] == '2') {
				$info['po_req_no'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
			} else if ($rows['invoice_format'] == '1') {
				$info['po_req_no'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
			} else if ($rows['invoice_format'] == '3') {
				$info['po_req_no'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
			} else {
				$info['po_req_no'] = str_pad($id, 3, "0", STR_PAD_LEFT);
			}

			$query1 = "select req.*,`so`.`sales_order_no`, `so`.`cust_id`, `so`.`sales_order_date`,`so`.`po_no`, `so`.`po_date`,req.branch_id,so.jobwork_type from tbl_sales_ordertrn as req
		 left join tbl_sales_order as so ON `req`.`sales_order_id` = `so`.`sales_order_id`
 		 where req.sales_ordertrn_status=0 and req.sales_ordertrn_id=" . $sales_ordertrn_id . " group by req.sales_ordertrn_id";
			$rel1 = brp_mysqli_fetch_assoc($dbcon->query($query1));

			$info['po_req_date'] = date('Y-m-d', strtotime($POST['indent_date']));
			$info['rp_req_qty'] = $POST['indent_qty'];
			$info['in_process_qty_main'] = '';
			$info['rp_po_qty'] = $POST['indent_qty'];
			;
			$info['product_id'] = $product_id;
			$info['sales_order_trn_id'] = $sales_ordertrn_id;
			$info['company_id'] = $_SESSION['company_id'];
			$info['vendor_id'] = $POST['cust_id'];
			$info['sales_order_date'] = date('Y-m-d', strtotime($rel1['sales_order_date']));
			$info['po_no'] = $rel1['po_no'];
			$info['po_date'] = date('Y-m-d', strtotime($rel1['po_date']));
			$info['sales_order_no'] = $rel1['sales_order_no'];
			$info['bom_id'] = "";
			$info['bom_no'] = "";

			$info['cdate'] = date('Y-m-d H:i:s');
			$info['user_id'] = $_SESSION['user_id'];
			$info['po_req_no'] = load_series_no($dbcon, 9);

			$inserid = add_record('tbl_set_main_process', $info, $dbcon, $branch_id);
			if ($inserid) {
				$query_invoicetype = $dbcon->query("UPDATE tbl_invoicetype SET taxinvoice_start = taxinvoice_start +1 WHERE type_id = '9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id']);

				$set_pro = "SELECT product_base_unit,product_conv_unit FROM `product_mst` WHERE product_status=0 AND product_id='" . $product_id . "'";
				$setpro_rel = brp_mysqli_fetch_assoc($dbcon->query($set_pro));

				$info_su['sp_id'] = $inserid;
				$info_su['sr_no'] = 0;
				$info_su['rp_pid'] = $product_id;//product_id
				$info_su['rp_req_date'] = date("Y-m-d");
				$info_su['rp_req_qty'] = $POST['indent_qty'];//required qty
				$info_su['sales_order_trn_id'] = $sales_ordertrn_id;//required qty
				$info_su['rp_po_qty'] = $POST['indent_qty'];//po qty
				$info_su['in_process_qty'] = '';//process qty
				$info_su['rp_req_type'] = "min_max";//type
				$info_su['process_unit'] = $setpro_rel['product_base_unit'];
				$info_su['purchase_unit'] = $setpro_rel['product_conv_unit'];
				$info_su['perent_id'] = 0;
				$info_su['main_request'] = 1;
				$info_su['status'] = 0;
				$info_su['user_id'] = $_SESSION['user_id'];
				$info_su['company_id'] = $_SESSION['company_id'];

				$info_su['bom_id'] = '';
				$info_su['product_version'] = '';
				$info_su['jobwork_type'] = $info['jobwork_type'];
				$info_su['customer_id'] = $POST['cust_id'];

				$indent_no = load_common_no($dbcon, 17);
				update_common_no($dbcon, 17);
				$info_su['indent_status'] = 1;
				$info_su['indent_no'] = $indent_no;
				$info_su['indent_date'] = date('Y-m-d');
				$info_su['cdate'] = date('Y-m-d H:i:s');
				$info_su['branch_id'] = $branch_id;


				$inserid_sub1 = add_record('tbl_request_product', $info_su, $dbcon, $branch_id);

				$info_soallo['sales_ordertrn_id'] = $sales_ordertrn_id;
				$info_soallo['product_id'] = $product_id;
				$info_soallo['product_qty'] = $info['rp_req_qty'];
				$info_soallo['request_id'] = $inserid_sub1;
				$info_soallo['unit_id'] = $info_su['process_unit'];
				$info_soallo['user_id'] = $_SESSION['user_id'];
				$info_soallo['cdate'] = date("Y-m-d H:i:s");
				$info_soallo['company_id'] = $_SESSION['company_id'];

				$inser_so_allo = add_record('tbl_sales_order_production_trn', $info_soallo, $dbcon);
				$arr['msg'] = '1';
			} else {
				$arr['msg'] = '0';
			}

			echo json_encode($arr);
		} else if (strtolower($POST['mode']) == "preview_so_trn_pro_description") {
			$str = '';
			$qry = $dbcon->query("SELECT so_trn.*, pro.product_name, so.sales_order_date, so.sales_order_no FROM tbl_sales_ordertrn as so_trn LEFT JOIN product_mst as pro on pro.product_id=so_trn.product_id LEFT JOIN tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id  WHERE so_trn.sales_ordertrn_status = 0 and so_trn.sales_ordertrn_id = " . $POST['so_trn_id']);
			$res = brp_mysqli_fetch_assoc($qry);
			$str .= '<table class="display table table-bordered table-striped">
			<tbody>
			<tr>
			<td><strong>Sales Order No :</strong> ' . $res['sales_order_no'] . '</td>
			<td><strong>Sales Order Date :</strong> ' . date("d-M-Y", strtotime($res['sales_order_date'])) . '</td>
			</tr>
			<tr>
			<td><strong>Product Name :</strong> ' . $res['product_name'] . '</td>
			<td><strong>Request Qty :</strong> ' . $res['product_qty'] . '</td>
			</tr>
			<tr>
			<td colspan="2"><strong>Product Description:</strong><br>' . $res['description'] . '</td>
			</tr>
			</tbody>
			</table><br><br>';

			$query_img = "select mst.* from tbl_so_attch as mst 
		where mst.attach_status=0 and mst.design_dept=1 and mst.sales_order_id=" . $res['sales_order_id'];
			$result_img = $dbcon->query($query_img);

			$str .= '<h1 style="text-align:center">View Document</h1>
			<table class="display table table-bordered table-striped">
			<thead>
				<tr>
					<th>Sr.</th>
					<th>Document Name</th>
					<th>Attachment Document</th>
				</tr>
			</thead>
			<tbody>';
			$i = 1;
			$cnt = brp_mysqli_num_rows($result_img);
			if ($cnt > 0) {
				while ($row = brp_mysqli_fetch_array($result_img)) {
					$file_path = $dbcon->real_escape_string(DOMAIN . SO_ATTACH_VIEWING . $row['attach_file']);
					$str .= '<tr>
						<td>' . $i . '</td>
						<td>' . $row['attach_doc_name'] . '</td>
						<td>
							<a href="' . ROOT . SO_ATTACH_VIEWING . $row['attach_file'] . '" class="btn btn-info" target="_blank"><i class="fa fa-eye"></i> VIEW</a>
						</td>
					</tr>';
					$i++;
				}
			} else {
				$str .= '<tr>
					<td colspan="3" style="text-align:center">No Data Yet...!!!</td>
				</tr>';
			}
			$str .= '</tbody></table>';

			echo $str;
		} else if (strtolower($POST['mode']) == "create_workorder_shortage") {

			$so_array = $_POST['so_trn_id'];
			$product_array = $_POST['product_id'];
			$bom_array = $_POST['bom_id'];
			$branch_array = $_POST['branch_id'];
			$x = 0;
			$inserid = 0;
			foreach ($so_array as $so_trn_id) {
				$qry = "select so_trn.*,so.sales_order_no,so.sales_order_date from tbl_sales_ordertrn as so_trn
				left join tbl_sales_order as so on so.sales_order_id = so_trn.sales_order_id where so_trn.sales_ordertrn_id = " . $so_array[$x];
				$res = $dbcon->query($qry);
				$row = brp_mysqli_fetch_assoc($res);

				$product_id = $row['product_id'];
				$branch_id = $branch_array[$x];
				$bom_id = $row['bom_id'];
				// $bom_version_id = $row['']

				$bom_qry1 = "SELECT * FROM `tbl_bom` WHERE  bom_id=" . $bom_id;
				$bom_res1 = brp_mysqli_fetch_assoc($dbcon->query($bom_qry1));

				$bom_no = $bom_res1['bom_no'];
				$bom_version_id = $bom_res1['bom_version_id'];
				$query1 = "select * from  tbl_invoicetype where type_id='9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
				$rows = brp_mysqli_fetch_assoc($dbcon->query($query1));


				$id = $rows['taxinvoice_start'];
				$id = $id + 1;

				$new_query1 = "update tbl_invoicetype set taxinvoice_start = " . $id . " where type_id='9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
				$dbcon->query($new_query1);

				if ($rows['invoice_format'] == '2') {
					$info1['po_req_no'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
				} else if ($rows['invoice_format'] == '1') {
					$info1['po_req_no'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
				} else if ($rows['invoice_format'] == '3') {
					$info1['po_req_no'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
				} else {
					$info1['po_req_no'] = str_pad($id, 3, "0", STR_PAD_LEFT);
				}

				$info1['po_req_date'] = date("Y-m-d");
				$info1['rp_req_qty'] = $row['product_qty'];
				$info1['in_process_qty_main'] = $row['product_qty'];
				$info1['rp_po_qty'] = '0';
				$info1['product_id'] = $product_id;
				$info1['cdate'] = date("Y-m-d");
				$info1['mdate'] = date("Y-m-d");
				$info1['user_id'] = $_SESSION['user_id'];
				$info1['muser_id'] = $_SESSION['user_id'];
				$info1['auser_is'] = $_SESSION['user_id'];
				$info1['adata'] = '';
				$info1['vendor_id'] = '';
				$info1['bom_id'] = $bom_id;
				$info1['bom_no'] = $bom_no;
				$info1['sales_order_no'] = $row['sales_order_no'];
				$info1['sales_order_date'] = date("Y-m-d", strtotime($row['sales_order_date']));
				$info1['po_no'] = '';
				$info1['po_date'] = '';
				$info1['sp_status'] = 0;
				$info1['workorder_type'] = '1';
				$info1['branch_id'] = $branch_id;
				$info1['company_id'] = $_SESSION['company_id'];
				$info1['sales_order_trn_id'] = $so_array[$x];
				$info1['bom_version_id'] = $bom_version_id;
				$table = 'tbl_set_main_process';



				$inserid = add_record($table, $info1, $dbcon);

				if ($inserid) {
					$pqty = $info1['rp_req_qty'];
					$product_ids = implode(',', $_POST['product_id']);
					$pro_qry = "SELECT * FROM product_mst WHERE product_id IN (" . $product_ids . ")";

					$pro_rs = $dbcon->query($pro_qry);
					$pro_row = brp_mysqli_fetch_array($pro_rs);

					$info2['sp_id'] = $inserid;
					$info2['sr_no'] = 0;
					$info2['rp_req_no'] = '';
					$info2['rp_req_date'] = date("Y-m-d");
					$info2['rp_pid'] = $info1['product_id'];
					$info2['rp_req_qty'] = $info1['rp_req_qty'];
					$info2['req_qty_one'] = 1;
					$info2['rp_po_qty'] = 0;
					$info2['in_process_qty'] = 0;
					$info2['out_process_qty'] = '';
					$info2['rp_req_type'] = 'work_order';
					$info2['rp_po_req_no'] = '';
					$info2['rp_process_req_no'] = '';
					$info2['cdate'] = strtotime(date("Y-m-d"));
					$info2['user_id'] = $_SESSION['user_id'];
					$info2['company_id'] = $_SESSION['company_id'];
					$info2['status'] = 0;
					$info2['row_cnt'] = 0;
					$info2['process_unit'] = $pro_row['product_base_unit'];
					$info2['purchase_unit'] = $pro_row['product_conv_unit'];
					$info2['reserve_status'] = 0;
					$info2['used_rp_req_qty'] = '';
					$info2['used_status'] = 0;
					$info2['perent_id'] = 0;
					$info2['reserve_stock'] = '';
					$info2['main_request'] = 1;
					$info2['indent_no'] = '';
					$info2['indent_date'] = '';
					$info2['indent_status'] = '';
					$info2['job_card_no'] = '';
					$info2['job_card_date'] = '';
					$info2['job_card_status'] = '';
					$info2['reject_status'] = 0;
					$info2['sales_order_trn_id'] = $so_array[$x];
					$info2['branch_id'] = $branch_id;
					$info2['finish_used_qty'] = '';
					$info2['finish_status'] = 0;
					$info2['product_version'] = '';
					$info2['pre_trn_id'] = 0;
					$info2['shortclose_qty'] = 0;
					$info2['shortclose_remark'] = '';
					$info2['approval_status'] = '1';
					$info2['workorder_type'] = '1';
					$info2['bom_id'] = $bom_id;


					$table = 'tbl_request_product';
					$reqinserid = add_record($table, $info2, $dbcon);

					// var_dump($info2);
					
					
					$info_soallo['sales_ordertrn_id'] = $so_array[$x];
					$info_soallo['product_id'] = $row['product_id'];
					$info_soallo['product_qty'] = $pqty;
					$info_soallo['request_id'] = $reqinserid;
					$info_soallo['unit_id'] = $info2['process_unit'];
					$info_soallo['user_id'] = $_SESSION['user_id'];
					$info_soallo['cdate'] = date("Y-m-d H:i:s");
					$info_soallo['company_id'] = $_SESSION['company_id'];
					
					$inser_so_allo = add_record('tbl_sales_order_production_trn', $info_soallo, $dbcon);
					
					$workorder_query_pro = "SELECT * FROM `tbl_bom` as bom
							left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
							left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
							left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
							WHERE pro_bom_process.process_status = 0 and prover.bom_version_id='" . $bom_version_id . "' AND bom.bom_product='" . $product_id . "' and bom.bom_id =" . $bom_id;

					$workorder_query_result = $dbcon->query($workorder_query_pro);
					
					if (brp_mysqli_num_rows($workorder_query_result) > 0) {
						while ($wproduct_process = brp_mysqli_fetch_assoc($workorder_query_result)) {
							$wwpp_info['product_id'] = $product_id;
							$wwpp_info['rp_id'] = $reqinserid;
							$wwpp_info['process_priority'] = $wproduct_process['priority'];
							$wwpp_info['process_time'] = $wproduct_process['process_time'];
							$wwpp_info['process_type'] = $wproduct_process['process_type'];
							$wwpp_info['process_opening'] = $wproduct_process['process_opening'];
							$wwpp_info['process_id'] = $wproduct_process['process_id'];
							$wwpp_info['cdate'] = date("Y-m-d H:i:s");
							$wwpp_info['user_id'] = $_SESSION['user_id'];
							$wwpp_info['company_id'] = $_SESSION['company_id'];
							$wwpp_info['branch_id'] = $branch_id;
							$wpp_info['description'] = $wproduct_process['description'];
							//echo "<pre>"; print_r($wwpp_info);
							$inserestimateid = add_record('tbl_wororder_product_process', $wwpp_info, $dbcon);
						}
					}
					
					$bom_process = "SELECT * FROM `tbl_bom` as bom
					left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
					WHERE  prover.bom_version_id='" . $bom_version_id . "' AND bom.bom_product='" . $product_id . "'";
					$bom_rel = brp_mysqli_fetch_assoc($dbcon->query($bom_process));
					
					$query1 = "SELECT bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,pro.reorder_qty 
					from tbl_bomtrn as bom_trn 
					left join product_mst as pro on pro.product_id=bom_trn.product_id
					left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
					left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
					where bom_trn_status=0 and bom_id='" . $bom_rel['bom_id']. "'";
					$result1 = $dbcon->query($query1);
					$call = 1;
					$space = "";
					$i = 1;
					//$rel1=brp_mysqli_fetch_assoc($result1);
					//echo "<pre>"; print_r($rel1);
					
					while ($rel1 = brp_mysqli_fetch_assoc($result1)) {
						$base_one_qty = $rel1['product_base_qty'] / $bom_rel['product_base_qty'];
						$conv_one_qty = $rel1['product_conv_qty'] / $bom_rel['product_conv_qty'];
						// $conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");

						$base_qty = $base_one_qty * $info2['rp_req_qty'];
						$reorder_qty = 0;

						if (!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0) {
							$reorder_qty = $rel1['reorder_qty'];
							$chk_qty = ceil($base_qty / $reorder_qty);
							$base_qty = $reorder_qty * $chk_qty;
						}
						$conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");

						/*$base_one_qty=$rel1['product_base_qty'];
										  $conv_one_qty=convert_stock($dbcon,$base_one_qty,$rel1['product_id'],"conv_unit");


										  $base_qty=$base_one_qty*$rel1['product_base_qty'];
										  $conv_stock=convert_stock($dbcon,$base_qty,$rel1['product_id'],"conv_unit");*/


						$info_sub['sp_id'] = $inserid;
						$info_sub['sr_no'] = $i;
						$info_sub['rp_pid'] = $rel1['product_id'];
						$info_sub['rp_req_date'] = date("Y-m-d");


						//$info_sub['rp_req_qty']			= $POST['qty']*$conv_one_qty;
						//$info_sub['req_qty_one']		= $conv_one_qty;//required qty



						$info_sub['rp_req_qty'] = $base_qty;//required qty
						$info_sub['req_qty_one'] = $base_one_qty;//required qty

						/*$info_sub['rp_req_qty']		= $POST['qty']*$base_one_qty;
										  $info_sub['req_qty_one']		= $base_one_qty;//required qty*/
						$info_sub['rp_po_qty'] = "";//po qty
						$info_sub['in_process_qty'] = 0;//process qty
						$info_sub['rp_req_type'] = "work_order";//type
						$info_sub['process_unit'] = $rel1['product_base_unit'];
						$info_sub['purchase_unit'] = $rel1['product_conv_unit'];
						$info_sub['perent_id'] = $reqinserid;
						$info_sub['status'] = 3;
						$info_sub['user_id'] = $_SESSION['user_id'];
						$info_sub['company_id'] = $_SESSION['company_id'];
						//$info_sub['main_request']		= $POST['g_total'];

						$info_sub['product_version'] = $rel1['p_bom_id'];
						$info_sub['bom_id'] = $rel1['p_bom_id'];
						$info_sub['approval_status'] = '1';
						$info_sub['workorder_type'] = '1';

						//echo "<pre>"; print_r($info_sub);die;
						$inserid_sub = add_record('tbl_request_product', $info_sub, $dbcon, $branch_id);
						//echo "jayesh".$inserid_sub."test";
						/*   Material Formula */
						$material_query = "select * from tbl_bom_material_trn where bom_trn_id=" . $rel1['bom_trn_id'] . " AND bom_id =" . $rel1['bom_id'];
						$material_result = $dbcon->query($material_query);
						if (brp_mysqli_num_rows($material_result) > 0) {
							while ($mat_rel = brp_mysqli_fetch_assoc($material_result)) {
								$mat_data['sp_id'] = $inserid;
								$mat_data['rp_id'] = $inserid_sub;
								$mat_data['product_id'] = $rel1['product_id'];
								$mat_data['material_parameter_id'] = $mat_rel['material_parameter_id'];
								$mat_data['material_parameter_value'] = $mat_rel['material_parameter_value'];
								$mat_data['wo_material_trn_status'] = 0;
								$mat_data['user_id'] = $_SESSION['user_id'];
								$mat_data['company_id'] = $_SESSION['company_id'];
								$mat_data['branch_id'] = $branch_id;
								// $inserid_wo_sub=add_record('tbl_wo_material_trn', $mat_data, $dbcon,$branch_id);

							}
						}
						// var_dump($rel1['p_bom_id']);

						$query_pro1 = "SELECT * FROM `tbl_bom` as bom
						left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
						left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
						left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
						WHERE bom.bom_product='" . $rel1['product_id'] . "' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id =" . $rel1['p_bom_id'];

						$rel_pro1 = $dbcon->query($query_pro1);

						if (brp_mysqli_num_rows($rel_pro1) > 0) {
							while ($product_process_row = brp_mysqli_fetch_assoc($rel_pro1)) {
								$wpp_info['product_id'] = $rel1['product_id'];
								$wpp_info['rp_id'] = $inserid_sub;
								$wpp_info['process_priority'] = $product_process_row['priority'];
								$wpp_info['process_time'] = $product_process_row['process_time'];
								$wpp_info['process_type'] = $product_process_row['process_type'];
								$wpp_info['process_opening'] = $product_process_row['process_opening'];
								$wpp_info['process_id'] = $product_process_row['process_id'];
								$wpp_info['cdate'] = date("Y-m-d H:i:s");
								$wpp_info['user_id'] = $_SESSION['user_id'];
								$wpp_info['company_id'] = $_SESSION['company_id'];
								$wpp_info['branch_id'] = $branch_id;
								$wpp_info['description'] = $product_process_row['description'];

								// $inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
							}
						}

						// bom_child_tree($dbcon,$rel1['p_bom_id'],$inserid,$inserid_sub,$i,$base_qty,$bom_version_id,$branch_id);

						$i++;
					}

					$work_order_id = $inserid;
					/*$info_wo['sp_status']=2;
						$updateid12=update_record("tbl_set_main_process", $info_wo,"sp_id=".$work_order_id , $dbcon);
						*/

					$bom_q1 = "SELECT rp_id,rp_pid FROM `tbl_request_product` WHERE main_request=1 and sp_id=" . $work_order_id;
					$bom_rel_q1 = brp_mysqli_fetch_assoc($dbcon->query($bom_q1));

					$query = "select * from tbl_request_product as i
		where i.rp_id=" . $bom_rel_q1['rp_id'];
					$result = $dbcon->query($query);
					$row = brp_mysqli_fetch_assoc($result);

					$info['rp_req_date'] = date('Y-m-d');
					$info['rp_req_qty'] = $info1['rp_req_qty'];
					$info['rp_po_qty'] = 0;
					$info['in_process_qty'] = $info1['rp_req_qty'];
					$info['reject_status'] = 0;

					$info['status'] = 0;
					$info['cdate'] = date('Y-m-d H:i:s');
					$info['user_id'] = $_SESSION['user_id'];
					$info['company_id'] = $_SESSION['company_id'];

					if ($info['rp_po_qty'] > "0") {
						$indent_no = load_common_no($dbcon, 17);
						update_common_no($dbcon, 17);
						$info['indent_status'] = 1;
						$info['indent_no'] = $indent_no;
						$info['indent_date'] = date('Y-m-d');
					}
					if ($info['in_process_qty'] > "0") {
						$indent_no = load_common_no($dbcon, 19);
						update_common_no($dbcon, 19);
						$info['job_card_status'] = 1;
						$info['job_card_no'] = $indent_no;
						$info['job_card_date'] = date('Y-m-d');
					}
					/*if(!empty($POST['sales_order_trn_id'])){
							  $info['sales_order_trn_id']		= $POST['sales_order_trn_id'];
						  }*/
					$updateid = update_record("tbl_request_product", $info, "rp_id=" . $bom_rel_q1['rp_id'], $dbcon);

					$set_pro = "SELECT product_base_unit,product_conv_unit,product_base_qty,product_conv_qty,product_id,batch_wise_stock_manage FROM `product_mst` WHERE product_status=0 AND product_id='" . $bom_rel_q1['rp_pid'] . "'";
					$setpro_rel = brp_mysqli_fetch_assoc($dbcon->query($set_pro));

					//indnet wip stock add start
					if ($info['rp_po_qty'] > 0) {
						if ($setpro_rel['product_conv_unit'] == $row['purchase_unit']) {
							$type = "base_unit";
							$con_stock = $info['rp_po_qty'];
							$base_stock = convert_stock_new($dbcon, $info['rp_po_qty'], $bom_rel_q1['rp_pid'], $type);
						} else {
							$type = "conv_unit";
							$base_stock = $info['rp_po_qty'];
							$con_stock = convert_stock_new($dbcon, $info['rp_po_qty'], $bom_rel_q1['rp_pid'], $type);
						}

						$info_wip_add['rp_id'] = $bom_rel_q1['rp_id'];
						$info_wip_add['type_flag'] = 3;
						$info_wip_add['po_trn_id'] = 0;
						$info_wip_add['sales_order_trn_id'] = 0;
						//$info_wip_add['allocate_for_rp_id']		= 0;
						//$info_wip_add['allocate_table_id']		= $POST['sales_order_trn_id'];
						$info_wip_add['allocate_base_qty'] = $base_stock;
						$info_wip_add['allocate_base_unit'] = $setpro_rel['product_base_unit'];
						$info_wip_add['allocate_conv_qty'] = $con_stock;
						$info_wip_add['allocate_conv_unit'] = $setpro_rel['product_conv_unit'];
						$info_wip_add['stock_flag'] = 1;
						$info_wip_add['cdate'] = date("Y-m-d H:i:s");
						$info_wip_add['user_id'] = $_SESSION['user_id'];
						$info_wip_add['company_id'] = $_SESSION['company_id'];

						$inser_wip_add = add_record('wip_stock_allocate', $info_wip_add, $dbcon, $branch_id);

					}
					//indnet wip stock add end

					// jobcard wip stock add
					if ($info['in_process_qty'] > 0) {
						if ($setpro_rel['product_conv_unit'] == $row['process_unit']) {
							$type = "base_unit";
							$con_stock1 = $info['in_process_qty'];
							$base_stock1 = convert_stock_new($dbcon, $info['in_process_qty'], $bom_rel_q1['rp_pid'], $type);
						} else {
							$type = "conv_unit";
							$base_stock1 = $info['in_process_qty'];
							$con_stock1 = convert_stock_new($dbcon, $info['in_process_qty'], $bom_rel_q1['rp_pid'], $type);
						}

						$info_wip_add1['rp_id'] = $bom_rel_q1['rp_id'];
						$info_wip_add1['type_flag'] = 3;
						$info_wip_add1['po_trn_id'] = 0;
						$info_wip_add1['sales_order_trn_id'] = $so_array[$x];
						//$info_wip_add1['allocate_for_rp_id']		= 0;
						//$info_wip_add1['allocate_table_id']		= $POST['sales_order_trn_id'];
						$info_wip_add1['allocate_base_qty'] = $base_stock1;
						$info_wip_add1['allocate_base_unit'] = $setpro_rel['product_base_unit'];
						$info_wip_add1['allocate_conv_qty'] = $con_stock1;
						$info_wip_add1['allocate_conv_unit'] = $setpro_rel['product_conv_unit'];
						$info_wip_add1['stock_flag'] = 1;
						$info_wip_add1['cdate'] = date("Y-m-d H:i:s");
						$info_wip_add1['user_id'] = $_SESSION['user_id'];
						$info_wip_add1['company_id'] = $_SESSION['company_id'];

						$inser_wip_add1 = add_record('wip_stock_allocate', $info_wip_add1, $dbcon, $branch_id);

					}
					// jobcard wip stock end
					if ($pqty != '') {
						if ($pqty != "0") {

							$queryw_b = "select * from pro_bom_process where process_status=0 and bom_id=" . $row['bom_id'];
							$rs_custw_b = $dbcon->query($queryw_b);
							while ($relw_b = brp_mysqli_fetch_array($rs_custw_b)) {

								$queryw = "select * from tbl_product_process where  pr_process_id=" . $relw_b['pr_process_id'];
								$rs_custw = $dbcon->query($queryw);
								$relw = brp_mysqli_fetch_array($rs_custw);
								$infow['product_id'] = $relw['product_id'];
								$infow['rp_id'] = $row['rp_id'];
								$infow['process_priority'] = $relw_b['priority'];
								;
								$infow['process_time'] = $relw['process_time'];
								$infow['process_type'] = $relw['process_type'];
								$infow['process_opening'] = $relw['process_opening'];
								$infow['process_id'] = $relw['process_id'];
								$infow['cdate'] = date('Y-m-d');
								$infow['user_id'] = $_SESSION['user_id'];
								$infow['company_id'] = $_SESSION['company_id'];

								// $inserid_a2=add_record('tbl_wororder_product_process', $infow, $dbcon, $POST['branch_id']);

								/*
												  Code By Umair : 05/11/2020
												  Comment: Insert Work Order Resource Allocation In tbl_work_order_resource_allocate table
												  */
								if ($relw['process_type'] == '1') {
									$resource_id = $relw['resource_id'];
									$request_id = $row['rp_id'];
									$process_id = $relw['process_id'];
									$product_id = $relw['product_id'];
									$qty = $pqty;
									$time_per_qty = $relw['process_time'];

									$action_type = 'add';
									$edit_id = '';
									work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, '', $branch_id);



								}

							}
						}
					}
					if ($pqty != '') {
						if ($pqty != "0") {
							$process = get_product_process($dbcon, $row['rp_id'], $row['rp_pid']);
							$process_pr = json_decode($process);

							$process_id = $process_pr->process_id;
							$process_type = $process_pr->process_type;
							$process_priority = $process_pr->process_priority;

							/*Get Resource ID*/
							$resourceinfo = get_resource_from_product_process($dbcon, $row['rp_pid'], $process_id, $where = null);

							$info5['process_id'] = $process_id;
							$info5['p_start_time'] = '';
							$info5['p_end_time'] = '';
							$info5['p_qty'] = $pqty;
							$info5['pen_qty'] = $pqty;
							$info5['process_unit'] = $bom_rel_q1['process_unit'];
							$info5['p_ref_id'] = $bom_rel_q1['rp_id'];
							$info5['p_ref_type'] = 'process request';
							$info5['p_product_id'] = $bom_rel_q1['rp_pid'];
							$info5['pr_process_type'] = $process_type;
							$info5['process_priority'] = $process_priority;
							$info5['previous_process_id'] = 0;
							$info5['product_version'] = $bom_rel_q1['product_version'];

							if ($resourceinfo['process_type'] == '1') {
								$info5['resource_id'] = $resourceinfo['resource_id'];
							}


							if ($companyConfiguration['batch_wise_stock'] == '1' && $companyConfiguration['batch_process'] == '0' && $setpro_rel['batch_wise_stock_manage'] == '1') {
								$info5['batch_process_start_time'] = 1;
							}

							$info5['cdate'] = date("Y-m-d H:i:s");
							$info5['user_id'] = $_SESSION['user_id'];
							$info5['company_id'] = $_SESSION['company_id'];

							$inserid_alloc = add_record('tbl_allocate_process', $info5, $dbcon, $branch_id);

							$query_reserve = "select * from tbl_request_product where status=0 and perent_id=" . $row['rp_id'];
							$rs_reserve = $dbcon->query($query_reserve);
							while ($rel_reserve = brp_mysqli_fetch_array($rs_reserve)) {

								$query_resu1 = $dbcon->query("UPDATE tbl_reserve_stock SET p_id =" . $inserid_alloc . " WHERE p_id=0 and request_id =" . $rel_reserve['rp_id']);

							}
						}
					}
					//var_dump($info['rp_req_qty']);
					//var_dump($bom_rel_q1['rp_id']);

					//echo $bom_rel_q1['rp_id'];
					//echo "222";

					/*if($POST['smode']=="add_all"){
								 $all_request_data_use=all_request_data_use($dbcon,$bom_rel_q1['rp_id'],$info['rp_po_qty']);
							 }*/
					/* if(!empty($POST['sales_order_trn_id'])){
								 $query_invoicetype = $dbcon->query("UPDATE tbl_sales_ordertrn SET work_order_qty = work_order_qty +".$info['in_process_qty_main']." WHERE sales_ordertrn_id = ".$POST['sales_order_trn_id']);
							 } */

				}


				$x++;
			}

			if ($inserid) {
				$arr['msg'] = "1";
			} else {
				$arr['msg'] = "0";
			}
			echo json_encode($arr);

		} else if (strtolower($POST['mode']) == "save_reserve_stock") {
			//start godown stock
			$query_rstock = "select * from work_order_reserve_temp as i
		where i.status = 0 and i.sales_ordertrn_id =" . $POST['sales_ordertrn_id'];
			$result_rstock = $dbcon->query($query_rstock);
			while ($row_rstock = brp_mysqli_fetch_assoc($result_rstock)) {
				$reserve_qty = $row_rstock['reserve_qty'];
				$batch_where = "";
				if (!empty($row_rstock['stock_id'])) {
					$batch_where = " and i.stock_id=" . $row_rstock['stock_id'];
				}
				$query_dstock = "select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i	
			where stock_status=0 and stock_flage=1 and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5)) " . $batch_where . " and i.product_id=" . $row_rstock['product_id'] . " and i.godown_id=" . $row_rstock['godown_id'];
				$result_dstock = $dbcon->query($query_dstock);
				while ($row_dstock = brp_mysqli_fetch_assoc($result_dstock)) {
					if ($row_dstock['convert_unit'] == $row_rstock['unit_id']) {
						$pending_stock = $row_dstock['pending_conv_stock'];
					} else {
						$pending_stock = $row_dstock['pending_base_stock'];
					}
					if ($reserve_qty > 0) {
						if ($pending_stock > 0) {
							if ($pending_stock >= $reserve_qty) {
								$rqty = $reserve_qty;
								$reserve_qty = $reserve_qty - $reserve_qty;
							} else {
								$rqty = $pending_stock;
								$reserve_qty = $reserve_qty - $pending_stock;
							}

							$que = "select * from product_mst as ta where product_id=" . $row_rstock['product_id'];
							$rs_di = $dbcon->query($que);
							$re = brp_mysqli_fetch_assoc($rs_di);


							if ($re['product_conv_unit'] == $row_rstock['unit_id']) {
								$type = "base_unit";
								$con_stock = $rqty;
								$base_stock = convert_stock_new($dbcon, $rqty, $row_rstock['product_id'], $type);
							} else {
								$type = "conv_unit";
								$base_stock = $rqty;
								$con_stock = convert_stock_new($dbcon, $rqty, $row_rstock['product_id'], $type);
							}


							$info_rese['reserve_date'] = date('Y-m-d');
							$info_rese['product_id'] = $row_rstock['product_id'];
							$info_rese['godown_id'] = $row_dstock['godown_id'];
							$info_rese['base_unit'] = $re['product_base_unit'];
							$info_rese['base_stock'] = $base_stock;
							$info_rese['convert_unit'] = $re['product_conv_unit'];
							$info_rese['convert_stock'] = $con_stock;
							$info_rese['stock_flage'] = "1";
							$info_rese['request_id'] = $row_rstock['rp_id'];
							$info_rese['ref_name'] = "wo_allocate";
							$info_rese['ref_id'] = "0";
							$info_rese['sales_order_trn_id'] = $row_rstock['sales_ordertrn_id'];
							$info_rese['stock_id'] = $row_dstock['stock_id'];

							$info_rese['cdate'] = date("Y-m-d H:i:s");
							$info_rese['user_id'] = $_SESSION['user_id'];
							$info_rese['company_id'] = $_SESSION['company_id'];

							$reserve_id_id = add_record('tbl_reserve_stock', $info_rese, $dbcon, $row_dstock['branch_id']);


							update_workorder_complete_qty_and_Status($dbcon, $row_rstock['rp_id'], $rqty);
							$wo_res_temp_info['status'] = 3;

							$updatetrnid = update_record('work_order_reserve_temp', $wo_res_temp_info, "work_order_reserve_temp_id=" . $row_rstock['work_order_reserve_temp_id'], $dbcon);

							if ($row_dstock['base_unit'] == $re['product_base_unit']) {
								$used_base_stock = $row_dstock['used_base_stock'] + $base_stock;
								$used_convert_stock = $row_dstock['used_convert_stock'] + $con_stock;
							} else {
								$used_base_stock = $row_dstock['used_convert_stock'] + $con_stock;
								$used_convert_stock = $row_dstock['used_base_stock'] + $base_stock;
							}

							$info_stock['used_base_stock'] = $used_base_stock;
							$info_stock['used_convert_stock'] = $used_convert_stock;

							$updatetrnid = update_record('tbl_stock_trn', $info_stock, "stock_id=" . $row_dstock['stock_id'], $dbcon);

							$info_e['sales_ordertrn_id'] = $row_rstock['sales_ordertrn_id'];
							$info_e['product_id'] = $row_rstock['product_id'];
							$info_e['product_qty'] = $info_rese['base_stock'];
							$info_e['godown_id'] = $info_rese['godown_id'];
							$info_e['unit_id'] = $info_rese['base_unit'];
							$info_e['allocate_qty'] = $info_rese['base_stock'];
							$info_e['remaning_invoice_qty'] = $info_rese['base_stock'];

							$info_e['cdate'] = date("Y-m-d");
							$info_e['company_id'] = $_SESSION['company_id'];
							$info_e['user_id'] = $_SESSION['user_id'];
							$inserinvoiceidexp = add_record('tbl_sales_order_production_trn', $info_e, $dbcon, $row_dstock['branch_id']);
						}
					}
				}
			}
			//End godown stock
			//start wip stock
			$bstock = $POST['bstock'];
			$bid = $POST['bid'];

			for ($i = 0; $i < count($bstock); $i++) {
				$que12 = "select ta.*,req.rp_pid from wip_stock_allocate as ta 
			left join tbl_request_product as req on req.rp_id=ta.rp_id
			where wip_stock_allocate_id=" . $bid[$i];
				$rs_di11 = $dbcon->query($que12);
				$re12 = brp_mysqli_fetch_assoc($rs_di11);
				//var_dump($que12);
				$que = "select * from product_mst as ta where product_id=" . $re12['rp_pid'];
				$rs_di = $dbcon->query($que);
				$re = brp_mysqli_fetch_assoc($rs_di);

				if ($re['product_conv_unit'] == $re12['allocate_base_unit']) {
					$type = "base_unit";
					$con_stock = $bstock[$i];
					$base_stock = convert_stock_new($dbcon, $bstock[$i], $re12['rp_pid'], $type);

				} else {
					$type = "conv_unit";
					$base_stock = $bstock[$i];
					$con_stock = convert_stock_new($dbcon, $bstock[$i], $re12['rp_pid'], $type);

				}

				update_workorder_complete_qty_and_Status($dbcon, $re12['rp_id'], $bstock[$i]);


				$info_wip['rp_id'] = $re12['rp_id'];
				$info_wip['type_flag'] = $re12['type_flag'];

				$info_wip['sales_order_trn_id'] = $POST['sales_ordertrn_id'];
				//$info_wip['allocate_for_rp_id']		= $POST['rp_id'];
				$info_wip['allocate_for_rp_id'] = $re12['rp_id'];
				$info_wip['allocate_base_qty'] = $base_stock;
				$info_wip['allocate_base_unit'] = $re['product_base_unit'];
				$info_wip['allocate_conv_qty'] = $con_stock;
				//$info_wip['allocate_conv_qty_used']		= $row_rstock['rp_id'];
				$info_wip['allocate_conv_unit'] = $re['product_conv_unit'];
				$info_wip['perent_id'] = $re12['wip_stock_allocate_id'];
				$info_wip['stock_flag'] = 2;
				$info_wip['cdate'] = date("Y-m-d H:i:s");
				$info_wip['user_id'] = $_SESSION['user_id'];
				$info_wip['company_id'] = $_SESSION['company_id'];

				$reserve_wip_id = add_record('wip_stock_allocate', $info_wip, $dbcon, $re12['branch_id']);

				$info_w['sales_ordertrn_id'] = $info_wip['sales_order_trn_id'];
				$info_w['product_id'] = $re['product_id'];
				$info_w['product_qty'] = $info_wip['allocate_base_qty'];
				$info_w['request_id'] = $info_wip['allocate_for_rp_id'];
				$info_w['unit_id'] = $info_wip['allocate_base_unit'];

				$info_w['cdate'] = date("Y-m-d");
				$info_w['company_id'] = $_SESSION['company_id'];
				$info_w['user_id'] = $_SESSION['user_id'];
				$inserinvoiceidexp1 = add_record('tbl_sales_order_production_trn', $info_w, $dbcon, $re12['branch_id']);
			}

			//end wip stock
		}

	}

}
/*
else {
	die("Error - 1");
}*/

function add_requst_rnd($dbcon, $product_id, $sales_ordertrn_id, $qty, $version_id)
{
	$query1 = "select * from  tbl_invoicetype where type_id='9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
	$rows = brp_mysqli_fetch_assoc($dbcon->query($query1));
	$id = $rows['taxinvoice_start'];
	$id = $id + 1;

	$new_query1 = "update tbl_invoicetype set taxinvoice_start = " . $id . " where type_id='9' and company_id=" . $_SESSION['company_id'] . " AND financial_year_id = " . $_SESSION['financial_year_id'];
	$dbcon->query($new_query1);

	if ($rows['invoice_format'] == '2') {
		$info1['po_req_no'] = str_pad($id, 4, "0", STR_PAD_LEFT) . $rows['format_value'];
	} else if ($rows['invoice_format'] == '1') {
		$info1['po_req_no'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT);
	} else if ($rows['invoice_format'] == '3') {
		$info1['po_req_no'] = $rows['format_value'] . str_pad($id, 3, "0", STR_PAD_LEFT) . $rows['end_format_value'];
	} else {
		$info1['po_req_no'] = str_pad($id, 3, "0", STR_PAD_LEFT);
	}

	$info1['po_req_date'] = date("Y-m-d");
	$info1['rp_req_qty'] = $qty;
	$info1['in_process_qty_main'] = $qty;
	$info1['rp_po_qty'] = '0';
	$info1['product_id'] = $product_id;
	$info1['cdate'] = date("Y-m-d");
	$info1['mdate'] = date("Y-m-d");
	$info1['user_id'] = $_SESSION['user_id'];
	$info1['muser_id'] = $_SESSION['user_id'];
	$info1['auser_is'] = $_SESSION['user_id'];
	$info1['adata'] = '';
	$info1['vendor_id'] = '';
	$info1['bom_id'] = '';
	$info1['bom_no'] = '';
	$info1['sales_order_no'] = '';
	$info1['sales_order_date'] = strtotime(date("Y-m-d"));
	$info1['po_no'] = '';
	$info1['po_date'] = '';
	$info1['sp_status'] = '';
	$info1['branch_id'] = $POST['branch_id'];
	$info1['company_id'] = $_SESSION['company_id'];
	$info1['sales_order_trn_id'] = $sales_ordertrn_id;
	$info1['bom_version_id'] = '10000';
	$table = 'tbl_set_main_process';


	//echo "<pre>"; print_r($info1); die;

	$inserid = add_record($table, $info1, $dbcon);


	if ($inserid) {

		$info2['sp_id'] = $inserid;
		$info2['sr_no'] = 0;
		$info2['rp_req_no'] = '';
		$info2['rp_req_date'] = date("Y-m-d");
		$info2['rp_pid'] = $product_id;
		$info2['rp_req_qty'] = $qty;
		$info2['req_qty_one'] = 1;
		$info2['rp_po_qty'] = 0;
		$info2['in_process_qty'] = $qty;
		$info2['out_process_qty'] = '';
		$info2['rp_req_type'] = 'work_order';
		$info2['rp_po_req_no'] = '';
		$info2['rp_process_req_no'] = '';
		$info2['cdate'] = strtotime(date("Y-m-d"));
		$info2['user_id'] = $_SESSION['user_id'];
		$info2['company_id'] = $_SESSION['company_id'];
		$info2['status'] = 3;
		$info2['row_cnt'] = 0;
		$info2['process_unit'] = 3;
		$info2['purchase_unit'] = 3;
		$info2['reserve_status'] = 0;
		$info2['used_rp_req_qty'] = '';
		$info2['used_status'] = 0;
		$info2['perent_id'] = 0;
		$info2['reserve_stock'] = '';
		$info2['main_request'] = 1;
		$info2['indent_no'] = '';
		$info2['indent_date'] = '';
		$info2['indent_status'] = '';
		$info2['job_card_no'] = '';
		$info2['job_card_date'] = '';
		$info2['job_card_status'] = '';
		$info2['reject_status'] = 0;
		$info2['sales_order_trn_id'] = $sales_ordertrn_id;
		$info2['branch_id'] = '';
		$info2['finish_used_qty'] = '';
		$info2['finish_status'] = 0;
		$info2['product_version'] = '';
		$info2['pre_trn_id'] = 0;
		$info2['shortclose_qty'] = 0;
		$info2['shortclose_remark'] = '';
		/*$info2['work_order_no']		= '';	
						  $info2['work_order_date']		= '';	
						  $info2['work_order_status']		= '';	*/

		$table = 'tbl_request_product';
		$reqinserid = add_record($table, $info2, $dbcon);

		if ($version_id != "10000") {

			$bom_process = "SELECT * FROM `tbl_bom` as bom
							left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
							WHERE  prover.bom_version_id='" . $version_id . "' AND bom.bom_product='" . $product_id . "'";
			$bom_rel = mysqli_fetch_assoc($dbcon->query($bom_process));

			$query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name from tbl_bomtrn as bom_trn 
							left join product_mst as pro on pro.product_id=bom_trn.product_id
							left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
							left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
							where bom_trn_status=0 and bom_id=" . $bom_rel['bom_id'];
			$result1 = $dbcon->query($query1);
			$call = 1;
			$space = "";
			$i = 1;
			if (brp_mysqli_num_rows($result1) > 0) {

				while ($rel1 = mysqli_fetch_assoc($result1)) {

					$base_one_qty = $rel1['product_base_qty'] / $bom_rel['product_base_qty'];
					$conv_one_qty = convert_stock($dbcon, $base_one_qty, $rel1['product_id'], "conv_unit");

					$base_qty = $base_one_qty * $info_su['rp_req_qty'];
					$conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");

					$info_sub['sp_id'] = $inserid;
					$info_sub['sr_no'] = $i;
					$info_sub['rp_pid'] = $rel1['product_id'];
					$info_sub['rp_req_date'] = date("Y-m-d");

					$info_sub['rp_req_qty'] = $POST['qty'];
					$info_sub['req_qty_one'] = $conv_one_qty;//required qty
					$info_sub['rp_po_qty'] = "";//po qty
					$info_sub['in_process_qty'] = $POST['qty'];//process qty
					$info_sub['rp_req_type'] = "work_order";//type
					$info_sub['process_unit'] = $rel1['product_base_unit'];
					$info_sub['purchase_unit'] = $rel1['product_conv_unit'];
					$info_sub['perent_id'] = $reqinserid;
					$info_sub['status'] = 3;
					$info_sub['user_id'] = $_SESSION['user_id'];
					$info_sub['company_id'] = $_SESSION['company_id'];
					//$info_sub['main_request']		= $POST['g_total'];

					$info_sub['product_version'] = $rel1['p_bom_id'];
					$info_sub['bom_id'] = $rel1['p_bom_id'];

					//echo "<pre>"; print_r($info_sub);die;
					$inserid_sub = add_record('tbl_request_product', $info_sub, $dbcon, $POST['branch_id']);

					/*	$query_pro1="select prod.*,pmst.process_name from tbl_product_process as prod left join process_mst as pmst on pmst.process_id=prod.process_id where prod.product_id = ".$rel1['product_id']; */

					/*$query_pro1="select* from pro_bom_process where product_id = ".$rel1['product_id']." AND bom_id =".$bom_rel['bom_id'];	*/

					$query_pro1 = "SELECT * FROM `tbl_bom` as bom
						left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
						left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
						left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
						WHERE tbl_product_process.status = 0 and  bom.bom_product='" . $rel1['product_id'] . "'";

					$rel_pro1 = $dbcon->query($query_pro1);

					if (brp_mysqli_num_rows($rel_pro1) > 0) {
						while ($product_process_row = brp_mysqli_fetch_assoc($rel_pro1)) {
							$wpp_info['product_id'] = $rel1['product_id'];
							$wpp_info['rp_id'] = $inserid_sub;
							$wpp_info['process_priority'] = $product_process_row['process_priority'];
							$wpp_info['process_time'] = $product_process_row['process_time'];
							$wpp_info['process_type'] = $product_process_row['process_type'];
							$wpp_info['process_opening'] = $product_process_row['process_opening'];
							$wpp_info['process_id'] = $product_process_row['pr_process_id'];
							$wpp_info['cdate'] = date("Y-m-d H:i:s");
							$wpp_info['user_id'] = $_SESSION['user_id'];
							$wpp_info['company_id'] = $_SESSION['company_id'];
							$wpp_info['branch_id'] = $POST['branch_id'];

							$inserestimateid = add_record('tbl_wororder_product_process', $wpp_info, $dbcon);

						}

					}

					bom_child_tree($dbcon, $rel1['p_bom_id'], $inserid, $inserid_sub, $i, $qty, $version_id);


					$i++;
				}

			}
		}

	}

}

function bom_child_tree($dbcon, $bom_id, $sp_id, $rp_parent_id, $num, $qty, $bom_version_id, $branch_id)
{

	$query_m = "select * from tbl_bom as bom where bom_status=0 and bom_id=" . $bom_id;
	$result_m = $dbcon->query($query_m);
	$rel_m = mysqli_fetch_assoc($result_m);


	$query1 = "select bom_trn.*,pro.product_name,pro.product_type,bunit.unit_name as base_unit_name,cunit.unit_name as conv_unit_name,bom_trn.product_id,pro.reorder_qty from tbl_bomtrn as bom_trn 
		left join product_mst as pro on pro.product_id=bom_trn.product_id
		left join unit_mst as bunit on bunit.unitid=bom_trn.product_base_unit
		left join unit_mst as cunit on cunit.unitid=bom_trn.product_conv_unit
		where bom_trn_status=0 and bom_id=" . $bom_id;
	$result1 = $dbcon->query($query1);


	$k = 1;
	$call = 1;
	$space = "";
	while ($rel1 = brp_mysqli_fetch_assoc($result1)) {

		$sr_no = $num . '.' . $k;

		$base_one_qty = $rel1['product_base_qty'] / $rel_m['product_base_qty'];
		$conv_one_qty = convert_stock($dbcon, $base_one_qty, $rel1['product_id'], "conv_unit");
		$base_qty = $base_one_qty * $qty;

		$reorder_qty = 0;

		if (!empty($rel1['reorder_qty']) && $rel1['reorder_qty'] > 0) {
			$reorder_qty = $rel1['reorder_qty'];
			$chk_qty = ceil($base_qty / $reorder_qty);
			$base_qty = $reorder_qty * $chk_qty;
		}
		$conv_stock = convert_stock($dbcon, $base_qty, $rel1['product_id'], "conv_unit");

		$info_sub['sp_id'] = $sp_id;
		$info_sub['sr_no'] = $sr_no;
		$info_sub['rp_pid'] = $rel1['product_id'];
		$info_sub['rp_req_qty'] = $base_qty;//required qty
		$info_sub['req_qty_one'] = $base_one_qty;//required qty
		$info_sub['rp_req_date'] = date("Y-m-d");
		$info_sub['rp_po_qty'] = "";//po qty
		$info_sub['in_process_qty'] = 0;//process qty
		$info_sub['rp_req_type'] = "work_order";//type
		$info_sub['process_unit'] = $rel1['product_base_unit'];
		$info_sub['purchase_unit'] = $rel1['product_conv_unit'];
		$info_sub['perent_id'] = $rp_parent_id;
		$info_sub['status'] = 3;
		$info_sub['user_id'] = $_SESSION['user_id'];
		$info_sub['company_id'] = $_SESSION['company_id'];
		$info_sub['product_version'] = $rel1['p_bom_id'];
		$info_sub['bom_id'] = $rel1['p_bom_id'];
		$info_sub['workorder_type'] = 1;
		$info_sub['approval_status'] = '1';

		//echo "<pre>"; print_r($info_sub); die;

		$inserid_sub = add_record('tbl_request_product', $info_sub, $dbcon, $branch_id);

		$query_pro1 = "SELECT * FROM `tbl_bom` as bom
			left join pro_ms_bom_version as prover on prover.bom_version_id=bom.bom_version_id
			left join pro_bom_process on pro_bom_process.bom_version_id = prover.bom_version_id
			left join tbl_product_process on tbl_product_process.pr_process_id = pro_bom_process.pr_process_id
			WHERE tbl_product_process.status = 0 and  bom.bom_product='" . $rel1['product_id'] . "' AND pro_bom_process.process_status = '0' AND  pro_bom_process.pr_process_id != '' and bom.bom_id = " . $rel1['p_bom_id'];

		$rel_pro1 = $dbcon->query($query_pro1);

		if (brp_mysqli_num_rows($rel_pro1) > 0) {
			while ($product_process1 = brp_mysqli_fetch_assoc($rel_pro1)) {
				$wpp_info['product_id'] = $rel1['product_id'];
				$wpp_info['rp_id'] = $inserid_sub;
				$wpp_info['process_priority'] = $product_process1['priority'];
				$wpp_info['process_time'] = $product_process1['process_time'];
				$wpp_info['process_type'] = $product_process1['process_type'];
				$wpp_info['process_opening'] = $product_process1['pr_process_id'];
				$wpp_info['process_id'] = $product_process1['process_id'];
				$wpp_info['cdate'] = date("Y-m-d H:i:s");
				$wpp_info['user_id'] = $_SESSION['user_id'];
				$wpp_info['company_id'] = $_SESSION['company_id'];
				$wpp_info['branch_id'] = $branch_id;
				$wpp_info['description'] = $product_process1['description'];

				// $inserestimateid=add_record('tbl_wororder_product_process', $wpp_info, $dbcon);
			}
		}
		bom_child_tree($dbcon, $rel1['p_bom_id'], $sp_id, $inserid_sub, $sr_no, $qty, $bom_version_id, $branch_id);
		$k++;
	}

}

?>