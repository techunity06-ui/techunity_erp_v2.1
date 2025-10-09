<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
if ($_POST != NULL) {
	$POST = bulk_filter($dbcon, $_POST);
} else {
	$POST = bulk_filter($dbcon, $_GET);
}

$companyConfiguration = getCompanyConfiguration($dbcon);
$production_pro_search = $companyConfiguration['production_pro_search'];
$pro_search = explode(",", $production_pro_search);



if (brp_strtolower($POST['mode']) == "run_auto_mode") {
	$pq = "select * from tbl_request_product where sp_id=" . $_POST['work_order_id'] . " AND main_request = '1'";
	$pq_res = $dbcon->query($pq);
	$pq_row = brp_mysqli_fetch_assoc($pq_res);
	$que1 = 0;
	$que2 = 0;
	$que3 = 0;
	$que4 = 0;
	$query_que = "select * from tbl_work_order_auto_mrp_trn where status=0 and work_order_id=" . $_POST['work_order_id'];
	$result_que = $dbcon->query($query_que);
	while ($raw_que = brp_mysqli_fetch_assoc($result_que)) {
		if ($raw_que['question_id'] == 1) {
			$que1 = 1;
		}
		if ($raw_que['question_id'] == 2) {
			$que2 = 1;
		}
		if ($raw_que['question_id'] == 3) {
			$que3 = 1;
		}
		if ($raw_que['question_id'] == 4) {
			$que4 = 1;
		}
	}

	$bom1 = "SELECT * FROM `tbl_request_product` as rpro
	WHERE main_request=0 and rpro.status in (0,3) AND rpro.perent_id=" . $pq_row['rp_id'];
	$result = $dbcon->query($bom1);
	while ($rel = brp_mysqli_fetch_assoc($result)) {

		planning_request_id($dbcon, $rel['rp_id'], $que1, $que2, $que3, $que4);
		run_auto_mode_req($dbcon, $rel['rp_id'], $que1, $que2, $que3, $que4);
	}
} else if (brp_strtolower($POST['mode']) == "check_auto_mrp") {
	$query = "SELECT * FROM `tbl_work_order_auto_mrp_trn` as rpro
			WHERE status=0 and work_order_id=" . $POST['work_order_id'];
	$result = $dbcon->query($query);
	$cnt = mysqli_num_rows($result);
	$rel = brp_mysqli_fetch_assoc($result);
	if ($cnt > 0) {
		//$rel=brp_mysqli_fetch_assoc($result);
		echo 1;
	} else {
		echo $cnt;
	}
} else if (brp_strtolower($POST['mode']) == "auto_mrp_question") {

	if ($POST['que1'] == 1) {
		$info1['work_order_id']			= $POST['work_order_id'];
		$info1['question_id']			= 1;

		$info1['cdate']				= date('Y-m-d H:i:s');
		$info1['user_id']			= $_SESSION['user_id'];
		$info1['company_id']		= $_SESSION['company_id'];
		$inserpoid = add_record('tbl_work_order_auto_mrp_trn', $info1, $dbcon);
	}

	if ($POST['que2'] == 1) {
		$info2['work_order_id']			= $POST['work_order_id'];
		$info2['question_id']			= 2;

		$info2['cdate']				= date('Y-m-d H:i:s');
		$info2['user_id']			= $_SESSION['user_id'];
		$info2['company_id']		= $_SESSION['company_id'];
		$inserpoid = add_record('tbl_work_order_auto_mrp_trn', $info2, $dbcon);
	}
	if ($POST['que3'] == 1) {
		$info3['work_order_id']			= $POST['work_order_id'];
		$info3['question_id']			= 3;

		$info3['cdate']				= date('Y-m-d H:i:s');
		$info3['user_id']			= $_SESSION['user_id'];
		$info3['company_id']		= $_SESSION['company_id'];
		$inserpoid = add_record('tbl_work_order_auto_mrp_trn', $info3, $dbcon);
	}
	if ($POST['que4'] == 1) {
		$info4['work_order_id']			= $POST['work_order_id'];
		$info4['question_id']			= 4;

		$info4['cdate']				= date('Y-m-d H:i:s');
		$info4['user_id']			= $_SESSION['user_id'];
		$info4['company_id']		= $_SESSION['company_id'];
		$inserpoid = add_record('tbl_work_order_auto_mrp_trn', $info4, $dbcon);
	}


	if ($POST['que1'] == 1 || $POST['que2'] == 1 || $POST['que3'] == 1 || $POST['que4'] == 1) {
		$arr['msg'] =  1;
	} else {
		$arr['msg'] =  0;
	}

	echo json_encode($arr);
}
function run_auto_mode_req($dbcon, $rp_id, $que1, $que2, $que3, $que4)
{

	$bom1 = "SELECT * FROM `tbl_request_product` as rpro
	WHERE main_request=0 and rpro.status in (0,3) AND rpro.perent_id=" . $rp_id;
	$result = $dbcon->query($bom1);
	while ($rel = brp_mysqli_fetch_assoc($result)) {
		planning_request_id($dbcon, $rel['rp_id'], $que1, $que2, $que3, $que4);
		run_auto_mode_req($dbcon, $rel['rp_id'], $que1, $que2, $que3, $que4);
	}
}

function planning_request_id($dbcon, $rp_id, $que1, $que2, $que3, $que4)
{

	$query = "SELECT * FROM `tbl_request_product` as rpro
	WHERE rpro.status!=2 AND rpro.rp_id=" . $rp_id;
	$result = $dbcon->query($query);
	$rel = brp_mysqli_fetch_assoc($result);

	if ($rel['status'] == 3) {
		$query_perent = "SELECT * FROM `tbl_request_product` as rpro
		WHERE rpro.status!=2 AND rpro.rp_id=" . $rel['perent_id'];
		$result_perent = $dbcon->query($query_perent);
		$rel_perent = brp_mysqli_fetch_assoc($result_perent);
		if ($rel_perent['main_request'] == 1) {
			$request_qty = $rel_perent['rp_req_qty'] * $rel['req_qty_one'];
		} else {

			$request_qty = $rel_perent['in_process_qty'] * $rel['req_qty_one'];
		}
		if ($request_qty > 0) {
			$cstock = get_current_stock_new($dbcon, $rel["rp_pid"], $rel["purchase_unit"], $rel["branch_id"], $customer_id);
			$rstock = reserve_stock($dbcon, $rel["rp_pid"], $rel["purchase_unit"], "", "", "", "", $rel["branch_id"]);
			$wipstock = wipstock($dbcon, $rel["rp_pid"], $rel["purchase_unit"], $rel["branch_id"]);
			$godown_actualstock = $cstock - $rstock;
			$actualstock = $godown_actualstock + $wipstock;

			/* $query_process_ch = "select group_concat(process_id) as process_id_rp from tbl_wororder_product_process as trn
				where trn.rp_id=" . $rel["rp_id"];

			$result_process_ch = $dbcon->query($query_process_ch);
			$cnt_process_ch = mysqli_num_rows($result_process_ch); */

			$query_process_ch = "select count(pr_process_id) as process_id_rp from tbl_wororder_product_process as trn
				where trn.rp_id=" . $rel["rp_id"];

			$result_process_ch = $dbcon->query($query_process_ch);
			$rel_process_ch  = brp_mysqli_fetch_assoc($result_process_ch);
			$cnt_process_ch = $rel_process_ch['process_id_rp'];

			if ($cnt_process_ch > 0) {
				$ac_process_sto = process_stock_for_mrp($dbcon, $rel["rp_pid"], $rel["purchase_unit"], $rel["rp_id"], $rel["branch_id"]);
				$actualstock = $actualstock + $ac_process_sto;
			}
			if ($request_qty > 0) {
				if ($que1 == 1) {
					if ($actualstock > 0) {
						// if ($actualstock >= $request_qty) {
						if ($godown_actualstock > 0) {
							$query_god = "SELECT * FROM `tbl_stock_trn` as rpro
							WHERE rpro.stock_status=0 and rpro.stock_flage = 1 and rpro.branch_id=" . $rel["branch_id"] . " and  cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))  AND rpro.product_id=" . $rel["rp_pid"];
							$result_god = $dbcon->query($query_god);
							while ($rel_god = brp_mysqli_fetch_assoc($result_god)) {
								$stock_qty = $rel_god['base_stock'] - $rel_god['used_base_stock'];

								if ($stock_qty >= $request_qty) {
									$god_uses_stock = $request_qty;
								} else {
									$god_uses_stock = $stock_qty;
								}

								$request_qty = $request_qty - $god_uses_stock;
								
								if ($cnt_process_ch > 0) {
									$info1['reserve_qty']		= $god_uses_stock;
									$info1['unit_id']			= $rel_god['base_unit'];
								} else {
									$qty_conv = convert_stock($dbcon, $god_uses_stock, $rel_god['product_id'], "conv_unit");
									$info1['reserve_qty']		= $qty_conv;
									$info1['unit_id']			= $rel_god['convert_unit'];
								}

								$info1['rp_id']				= $rel['rp_id'];

								$info1['godown_id']			= $rel_god['godown_id'];
								$info1['product_id']		= $rel["rp_pid"];
								$info1['stock_id']			= $rel_god['stock_id'];

								$info1['cdate']				= date('Y-m-d H:i:s');
								$info1['user_id']			= $_SESSION['user_id'];
								$info1['company_id']		= $_SESSION['company_id'];
								//$info1['customer_id']		= $POST['customer_id'];	

								$inserpoid = add_record('work_order_reserve_temp', $info1, $dbcon);

								
							}
							reserve_auto_mrp_godown_stock($dbcon, $rel['rp_id']);
						}
						// }
					}
				}
			}

			if ($request_qty > 0) {
				if ($que2 == 1) {
					if ($wipstock > 0) {
						$query_wip = "select IFNULL(sum(trn.allocate_base_qty-trn.allocate_base_qty_used),0) as stock_qty,wip.indent_no,wip.indent_date,wip.job_card_no,wip.job_card_date,setp.po_req_date,setp.po_req_no,trn.type_flag,trn.wip_stock_allocate_id from wip_stock_allocate as trn
							left join tbl_request_product as wip on wip.rp_id=trn.rp_id
							left join tbl_set_main_process as setp on setp.sp_id=wip.sp_id
							where trn.status=0 and trn.branch_id=" . $rel['branch_id'] . " and allocate_base_qty>allocate_base_qty_used and trn.company_id=" . $_SESSION['company_id'] . " and wip.rp_pid=" . $rel['rp_pid'];

						$result_wip = $dbcon->query($query_wip);
						if (mysqli_num_rows($result_wip) > 0) {
							$i = 1;
							while ($rel_wip = brp_mysqli_fetch_assoc($result_wip)) {
								$stockqty = $rel['stock_qty'];

								$que12 = "select ta.*,req.rp_pid from wip_stock_allocate as ta 
									left join tbl_request_product as req on req.rp_id=ta.rp_id
									where wip_stock_allocate_id=" . $rel_wip['wip_stock_allocate_id'];
								$rs_di11 = $dbcon->query($que12);
								$re12 = brp_mysqli_fetch_assoc($rs_di11);

								if ($stockqty >= $request_qty) {
									$wip_used_stock = $request_qty;
								} else {
									$wip_used_stock = $stockqty;
								}
								$request_qty = $request_qty - $wip_used_stock;
								$que = "select * from product_mst as ta where product_id=" . $rel['rp_pid'];
								$rs_di = $dbcon->query($que);
								$re = brp_mysqli_fetch_assoc($rs_di);

								if ($re['product_conv_unit'] == $re12['allocate_base_unit']) {
									$type = "base_unit";
									$con_stock = $wip_used_stock;
									$basetock = convert_stock_new($dbcon, $wip_used_stock, $re12['rp_pid'], $type);
								} else {
									$type = "conv_unit";
									$base_stock = $wip_used_stock;
									$con_stock = convert_stock_new($dbcon, $wip_used_stock, $re12['rp_pid'], $type);
								}



								$info_wip['rp_id']						= $re12['rp_id'];
								$info_wip['type_flag']					= $re12['type_flag'];
								$info_wip['allocate_for_rp_id']			= $rel['rp_id'];
								$info_wip['allocate_base_qty']			= $base_stock;
								$info_wip['allocate_base_unit']			= $re['product_base_unit'];
								$info_wip['allocate_conv_qty']			= $con_stock;
								//$info_wip['allocate_conv_qty_used']		= $row_rstock['rp_id'];
								$info_wip['allocate_conv_unit']			= $re['product_conv_unit'];
								$info_wip['perent_id']					= $re12['wip_stock_allocate_id'];
								$info_wip['stock_flag']					= 2;
								$info_wip['cdate']						= date("Y-m-d H:i:s");
								$info_wip['user_id']					= $_SESSION['user_id'];
								$info_wip['company_id']					= $_SESSION['company_id'];

								$reserve_wip_id = add_record('wip_stock_allocate', $info_wip, $dbcon, $re12['branch_id']);
							}
						}
					}
				}
			}
			if ($que3 == 1) {
				//process stock entry start
				if ($ac_process_sto > 0) {
					$query_pro = "select process_id from tbl_wororder_product_process as trn
						where trn.rp_id=" . $rel['rp_id'] . " group by process_id order by process_priority DESC";

					$result_pro = $dbcon->query($query_pro);
					$cnt_process = mysqli_num_rows($result_pro);
					if ($cnt_process > 0) {
						$jp = 1;
						while ($rel_pro = brp_mysqli_fetch_assoc($result_pro)) {
							if ($jp != 1) {
								$query_sto = "select IFNULL(sum(base_stock),0) as stockqty,pmst.process_name,msgo.gd_name,trn.godown_id,trn.process_id from tbl_process_stock_trn as trn
												left join process_mst as pmst on pmst.process_id=trn.process_id
												left join mst_godown as msgo on msgo.gd_id=trn.godown_id
										where trn.stock_status=0 and trn.branch_id=" . $rel['branch_id'] . " and trn.stock_flage=1 and trn.company_id=" . $_SESSION['company_id'] . " and trn.product_id=" . $product_id . " and trn.process_id=" . $rel_pro['process_id'] . " group by trn.process_id,trn.godown_id";
								$j = 1;
								$result_sto = $dbcon->query($query_sto);
								while ($rel_sto = brp_mysqli_fetch_assoc($result_sto)) {

									$query_res = "select IFNULL(sum(base_stock),0) as used_stockqty from tbl_process_reserve_stock as trn
													where trn.stock_status=0 and stock_flage=1 and trn.company_id=" . $_SESSION['company_id'] . " and trn.product_id=" . $product_id . " and process_id=" . $rel_sto['process_id'] . " and godown_id=" . $rel_sto['godown_id'];

									$result_res = $dbcon->query($query_res);
									$rel_res = brp_mysqli_fetch_assoc($result_res);

									$process_stock = $rel_sto['stockqty'] - $rel_res['used_stockqty'];
									if ($process_stock > 0) {

										if ($process_stock >= $request_qty) {
											$prusedqty = $request_qty;
										} else {
											$prusedqty = $process_stock;
										}

										$request_qty = $request_qty - $prusedqty;

										$info_pro['rp_id']					= $rel['rp_id'];
										$info_pro['process_id']				= $rel_sto['process_id'];
										$info_pro['godown_id']				= $rel_sto['godown_id'];
										$info_pro['qty']					= $prusedqty;
										//$info_pro['unit_id']				= $re['product_base_unit'];
										$info_pro['cdate']					= date("Y-m-d H:i:s");
										$info_pro['user_id']				= $_SESSION['user_id'];
										$info_pro['company_id']				= $_SESSION['company_id'];

										if ($request_qty > 0) {
											$processstockadd_id = add_record('mrp_process_reserve_temp', $info_pro, $dbcon, $rel['branch_id']);
										}
									}
								}
							}
							$jp++;
						}
						allocate_process_and_process_stock_reserve($dbcon, $rel['rp_id']);
					}
				}


				//process stock entry stop
			}
		}


		if ($que4 == 1) {
			if ($request_qty > 0) {
				$query_process = "SELECT * FROM `tbl_wororder_product_process` as rpro
			WHERE rpro.rp_id=" . $rp_id;
				$result_process = $dbcon->query($query_process);
				$cnt = mysqli_num_rows($result_process);

				if ($cnt > 0) {
					$rel_process = brp_mysqli_fetch_assoc($result_process);
					$info['rp_req_date']		= date('Y-m-d');
					//$info['rp_req_qty']			=$POST['rp_req_qty'];
					//$info['rp_po_qty']			=$request_qty;
					$info['in_process_qty']			= $request_qty;
					$process_qty_conv = convert_stock($dbcon, $request_qty, $rel['rp_pid'], "conv_unit");
					$info['in_process_conv_qty'] = $process_qty_conv;
					//$info['reject_status']		=$POST['reject_status'];

					$info['status']				= 0;
					$info['cdate']				= date('Y-m-d H:i:s');
					$info['user_id']			= $_SESSION['user_id'];
					$info['company_id']			= $_SESSION['company_id'];

					/*if($info['rp_po_qty']>"0"){
					$indent_no=load_common_no($dbcon,17);
					update_common_no($dbcon,17);
					$info['indent_status']		= 1;
					$info['indent_no']			= $indent_no;
					$info['indent_date']		= date('Y-m-d');
				}*/
					//if($info['in_process_qty']>"0"){
					$indent_no = load_common_no($dbcon, 19);
					update_common_no($dbcon, 19);
					$info['job_card_status']		= 1;
					$info['job_card_no']			= $indent_no;
					$info['job_card_date']		= date('Y-m-d');
					//}

					$updateid = update_record("tbl_request_product", $info, "rp_id=" . $rel['rp_id'], $dbcon);

					$process = get_product_process($dbcon, $rel['rp_id'], $rel['rp_pid']);
					$process_pr = json_decode($process);

					$process_id = $process_pr->process_id;
					$process_type = $process_pr->process_type;
					$process_priority = $process_pr->process_priority;

					/*Get Resource ID*/
					$resourceinfo = get_resource_from_product_process($dbcon, $rel['rp_pid'], $process_id, $where = null);

					$info5['process_id']		= $process_id;
					$info5['p_start_time']		= '';
					$info5['p_end_time']		= '';
					$info5['p_qty']				= $info['in_process_qty'];
					$info5['pen_qty']			= $info['in_process_qty'];
					$info5['process_unit']		= $rel['process_unit'];
					$info5['p_ref_id']			= $rel['rp_id'];
					$info5['p_ref_type']		= 'process request';
					$info5['p_product_id']		= $rel['rp_pid'];
					$info5['pr_process_type']	= $process_type;
					$info5['process_priority']	= $process_priority;
					$info5['previous_process_id'] = 0;
					$info5['product_version']	= $rel['product_version'];

					if ($resourceinfo['process_type'] == '1') {
						$info5['resource_id']	= $resourceinfo['resource_id'];
					}

					$info5['cdate']				= date("Y-m-d H:i:s");
					$info5['user_id']			= $_SESSION['user_id'];
					$info5['company_id']		= $_SESSION['company_id'];

					$inserid_alloc = add_record('tbl_allocate_process', $info5, $dbcon, $rel['branch_id']);

					$query_reserve = "select * from tbl_request_product where status=0 and perent_id=" . $rel['rp_id'];
					$rs_reserve = $dbcon->query($query_reserve);
					while ($rel_reserve = brp_mysqli_fetch_array($rs_reserve)) {

						$query_resu1 = $dbcon->query("UPDATE tbl_reserve_stock SET p_id =" . $inserid_alloc . " WHERE p_id=0 and request_id =" . $rel_reserve['rp_id']);
					}
				} else {
					//purchase qty entry

					$info['rp_req_date']		= date('Y-m-d');
					//$info['rp_req_qty']			=$POST['rp_req_qty'];
					$info['rp_po_base_qty']			= $request_qty;
					$process_qty_conv = convert_stock($dbcon, $request_qty, $rel['rp_pid'], "conv_unit");
					$info['rp_po_qty']			= $process_qty_conv;
					//$info['in_process_qty']		=$POST['in_process_qty'];
					//$info['reject_status']		=$POST['reject_status'];

					$info['status']				= 0;
					$info['cdate']				= date('Y-m-d H:i:s');
					$info['user_id']			= $_SESSION['user_id'];
					$info['company_id']			= $_SESSION['company_id'];

					//if($info['rp_po_qty']>"0"){
					$indent_no = load_common_no($dbcon, 17);
					update_common_no($dbcon, 17);
					$info['indent_status']		= 1;
					$info['indent_no']			= $indent_no;
					$info['indent_date']		= date('Y-m-d');
					//}
					/*if($info['in_process_qty']>"0"){
					$indent_no=load_common_no($dbcon,JOBCARD);

					update_common_no($dbcon,JOBCARD);

					$info['job_card_status']		= 1;
					$info['job_card_no']			= $indent_no;
					$info['job_card_date']		= date('Y-m-d');
				}*/

					$updateid = update_record("tbl_request_product", $info, "rp_id=" . $rel['rp_id'], $dbcon);
				}
			}
		}
		if ($request_qty <= 0) {
			$info_l['status']			= 0;
			$info_l['cdate']			= date('Y-m-d H:i:s');
			$info_l['user_id']			= $_SESSION['user_id'];
			$info_l['company_id']		= $_SESSION['company_id'];
			$info_l['rp_req_date']		= date('Y-m-d');
			$updateid = update_record("tbl_request_product", $info_l, "rp_id=" . $rel['rp_id'], $dbcon);
		}
	}
}

function reserve_auto_mrp_godown_stock($dbcon, $rp_id)
{
	
	$query_rstock = "select * from work_order_reserve_temp as i
	where i.status=0 and i.rp_id=" . $rp_id;
	$result_rstock = $dbcon->query($query_rstock);
	while ($row_rstock = brp_mysqli_fetch_assoc($result_rstock)) {

		$query_rpi = "select * from tbl_request_product as i
			where i.rp_id=" . $rp_id;
		$result_rpi = $dbcon->query($query_rpi);
		$row_rpi = brp_mysqli_fetch_assoc($result_rpi);

		$reserve_qty = $row_rstock['reserve_qty'];

		$upd_info['reserve_stock'] = $row_rpi['reserve_stock'] + $reserve_qty;
		update_record('tbl_request_product', $upd_info, "rp_id=" . $rp_id, $dbcon);
		$batch_where = "";
		if (!empty($row_rstock['stock_id'])) {
			$batch_where = " and i.stock_id=" . $row_rstock['stock_id'];
		}
		$query_dstock = "select i.*,(base_stock-used_base_stock) as pending_base_stock,(convert_stock-used_convert_stock) as pending_conv_stock from tbl_stock_trn as i
		where stock_status=0 and stock_flage=1 and i.branch_id=" . $row_rpi['branch_id'] . " and cast(base_stock AS DECIMAL(50,5))>cast(used_base_stock AS DECIMAL(50,5))  " . $batch_where . " and i.godown_id=" . $row_rstock['godown_id'];


		$result_dstock = $dbcon->query($query_dstock);
		while ($row_dstock = brp_mysqli_fetch_assoc($result_dstock)) {
			if ($row_dstock['convert_unit'] == $row_rstock['unit_id']) {
				$pending_stock = $row_dstock['pending_conv_stock'];
			} else {
				$pending_stock = $row_dstock['pending_base_stock'];
			}
			if ($reserve_qty > 0) {
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

				$que_s = "select perent_id from tbl_request_product as ta where rp_id=" . $row_rstock['rp_id'];
				$rs_dwi = $dbcon->query($que_s);
				$re_df = brp_mysqli_fetch_assoc($rs_dwi);

				$que_s1 = "select p_id from tbl_allocate_process as ta where previous_process_id=0 and p_ref_id=" . $re_df['perent_id'];
				$rs_dwi1 = $dbcon->query($que_s1);
				$re_df1 = brp_mysqli_fetch_assoc($rs_dwi1);


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
				$info_rese['p_id']				= $re_df1['p_id'];
				$info_rese['stock_id']			= $row_dstock['stock_id'];

				$info_rese['cdate']				= date("Y-m-d H:i:s");
				$info_rese['user_id']			= $_SESSION['user_id'];
				$info_rese['company_id']		= $_SESSION['company_id'];
				$info_rese['customer_id']		= $POST['customer_id'];
								
				$reserve_id_id = add_record('tbl_reserve_stock', $info_rese, $dbcon, $row_dstock['branch_id']);

				if ($row_dstock['base_unit'] == $re['product_base_unit']) {
					$used_base_stock = $row_dstock['used_base_stock'] + $base_stock;
					$used_convert_stock = $row_dstock['used_convert_stock'] + $con_stock;
				} else {
					$used_base_stock = $row_dstock['used_convert_stock'] + $con_stock;
					$used_convert_stock = $row_dstock['used_base_stock'] + $base_stock;
				}

				$info_stock['used_base_stock']		= $used_base_stock;
				$info_stock['used_convert_stock']	= $used_convert_stock;

				$updatetrnid = update_record('tbl_stock_trn', $info_stock, "stock_id=" . $row_dstock['stock_id'], $dbcon);
			}
		}
	}
}
