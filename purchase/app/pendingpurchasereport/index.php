<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');
// error_reporting(E_ALL);
if ($_POST != NULL) {
	$POST = bulk_filter($dbcon, $_POST);
} else {
	$POST = bulk_filter($dbcon, $_GET);
}

if (strtolower($POST['mode']) == "fetch") {
} else if (strtolower($POST['mode']) == "vendorwisereport" || strtolower($POST['mode']) == "all") {
	$cust_id = $POST['vender_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['vender_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="display table-bordered" id="data_list" style="margin-top:20px;">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>Party Wise Pending Purchase Order List </strong>
	</td>
	</tr>';

	$qry = 'Select * from (
	(Select invoice_date as trn_date,inv.g_total as total,2 as typeid,invoice_no as trn_data from tbl_invoice as inv 
	inner join tbl_quotation as quot on quot.quotation_id=inv.quotation_id
	where inv.invoice_status=0 and quot.quot_won_user_id=' . $user_id . ' and invoice_date>="' . date('Y-m-d', strtotime($s_date[0])) . '" and invoice_date<="' . date('Y-m-d', strtotime($s_date[1])) . '" order by invoice_date)';

	$query = "SELECT tl.l_name as vendorname,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id
	FROM tbl_purchaseorder as tp
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
	";

	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tp.po_approval_status=1 and tp.revise_status=0 and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		} else {
			$s_date = explode(' - ', $POST['rep_del_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tp.po_approval_status=1 and tp.purchaseorder_due_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_due_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		}
	}
	if ($POST['vender_id']) {
		$query .= " and tp.vender_id=" . $POST['vender_id'];
	}

	$result = $dbcon->query($query);
	$i = 1;
	$is_data = false;
	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {
			$str_h = "";
			$str_h .= '<tr style="margin-top:15px;">
			<td colspan="2" style="border-bottom:hidden;border-right:hidden">
			<strong>Vendor  : </strong>
			</td>
			<td colspan="5" style="border-bottom:hidden;">
			' . $vendor_list['vendorname'] . '
			</td>
			</tr>

			<tr >
			<td colspan="2" style="border-bottom:hidden;border-right:hidden">
			<strong>P.O NO  : </strong>
			</td>
			<td colspan="5" style="border-bottom:hidden;">
			' . $vendor_list['purchaseorder_no'] . '
			</td>
			</tr>

			<tr>
			<td colspan="2" style="border-right:hidden">
			<strong>P.O Date : </strong>
			</td>
			<td colspan="5" >
			' . date('d/m/Y', strtotime($vendor_list['purchaseorder_date'])) . '
			</td>
			</tr>';
			$str_h .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">NO.</th>
			<th width="20%" style="text-align:center">Indent No. <br> Indent Dt</th>
			<th width="25%" style="text-align:center">Item Details</th>';

			$str_h .= '<!--<th width="12%" style="text-align:center">UOM</th>-->
				<th width="10%" style="text-align:center">P.O.Qty</th>
				<th width="10%" style="text-align:center">Pending Qty</th>
				<th width="20%" style="text-align:center">Del.Date</th></tr>';

			$query = "SELECT tpt.po_ref_id,um.unit_name, cum.unit_name as conv_unit, tpt.unit_id,tpt.conv_unit_id,tpt.product_id, tl.l_name as vendorname, tpt.product_qty,tpt.product_conv_qty,(select sum(gtr.product_qty) from tbl_grn_trn as gtr where gtr.purchaseordertrn_id = tpt.purchaseordertrn_id and gtr.grn_trn_status=0) as used_qty,(select sum(gtr.product_conv_qty) from tbl_grn_trn as gtr where gtr.purchaseordertrn_id = tpt.purchaseordertrn_id and gtr.grn_trn_status=0) as usedc_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,tpt.purchaseordertrn_id
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as cum ON cum.unitid=tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.vender_id=" . $vendor_list['vender_id'] . " and tpt.purchaseorder_id=" . $vendor_list['purchaseorder_id'];
			
			$result1 = $dbcon->query($query);
			$j = 1;
			$temp_str = "";
			if (brp_mysqli_num_rows($result1) > 0) {
				while ($re = brp_mysqli_fetch_array($result1)) {
					$penbq = $re["product_qty"] - $re["used_qty"];
					$pencq = $re["product_conv_qty"] - $re["usedc_qty"];

					$indent_no_date = '';
					if (!empty($re['po_ref_id'])) {
						$indent_no_date = get_indent_no_date($dbcon, $re['po_ref_id']);
					}

					$purchase_order_due_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);

					if ($re['unit_id'] != $re['conv_unit_id']) {
						$qty = '<strong style="color:green">' . $re["product_qty"] . ' ' . $re['unit_name'] . '</strong> <br> <strong style="color:orange">' . $re['product_conv_qty'] . ' ' . $re['conv_unit'] . '</strong>';
						$pen = '<strong style="color:green">' . $penbq . ' ' . $re['unit_name'] . '</strong> <br> <strong style="color:orange">' . $pencq . ' ' . $re['conv_unit'] . '</strong>';
					} else {
						$qty = '<strong style="color:green">' . $re["product_qty"] . ' ' . $re['unit_name'] . '</strong>';
						$pen = '<strong style="color:green">' . $penbq . ' ' . $re['unit_name'] . '</strong>';
					}

					if ($penbq > 0) {
						$temp_str .= '<tr style="border: 1px dashed #cccccc;">
						<td style="text-align:center">' . $i . '</td>
						<td style="text-align:center">' . $indent_no_date . '</td>
						<td style="text-align:center">' . $re["product_name"] . '</td>';

						$temp_str .= '<!--<td style="text-align:center">' . $re["unit_name"] . '</td>-->
						<td style="text-align:center">' . $qty . '</td>
						<td style="text-align:center">' . $pen . '</td>
						<td style="text-align:center">' . $purchase_order_due_date . '</td>
						</tr>';
					}

					$j++;
				}				
			}

			if (!empty($temp_str)) {
				$is_data = true;
				$str .= $str_h .= $temp_str;
			}
		}
		if ($is_data !== true) {
			$str .= '<tr>
			<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			</tr>';	
		}
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "vendorwisebriefreport" || strtolower($POST['mode']) == "allbriefreport") {
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $cust_id;
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="table table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="9" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="9">
	<strong>Party Wise Purchase Detail</strong>
	</td>
	</tr>';
	//for summary part onlydetail
	/*echo $POST['']*/
	if (strtolower($POST['reporttype']) == 'summary' &&  strtolower($POST['formattype']) != "format2" && strtolower($POST['formattype']) != "format3" && strtolower($POST['formattype']) != "detail") {

		$query = "SELECT group_concat(distinct(tp.purchaseorder_id)) as pos,tl.l_name as vendorname,tl.m_address as venderaddress,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id, (select IFNULL(sum(tbl_purchaseordertrn.product_amount),0) from tbl_purchaseordertrn where tbl_purchaseordertrn.purchaseorder_id=tp.purchaseorder_id and tbl_purchaseordertrn.purchaseordertrn_status=0) as total, (select IFNULL(sum(tbl_purchaseordertrn.product_amount_tax),0) from tbl_purchaseordertrn where tbl_purchaseordertrn.purchaseorder_id=tp.purchaseorder_id and tbl_purchaseordertrn.purchaseordertrn_status=0) as totaltax,tp.po_approval_status
			FROM tbl_purchaseorder as tp 
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id  where tp.status=0 and tpt.purchaseordertrn_status=0 and tp.po_approval_status=1 group by tp.vender_id";
		//$query.=' group by tp.vender_id';
		//echo $query;
		if ($cust_id) {
			$where = " and tp.vender_id=" . $cust_id;
		}
		$vender_summary = $dbcon->query($query);
		if (mysqli_num_rows($vender_summary) > 0) {

			$total = 0;
			$j = 1;
			while ($venor_summary_arr = mysqli_fetch_assoc($vender_summary)) {

				$query = "SELECT tl.l_name as vendorname,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id,(select IFNULL(sum(tbl_purchaseordertrn.product_amount),0) from tbl_purchaseordertrn where tbl_purchaseordertrn.purchaseorder_id=tp.purchaseorder_id and tbl_purchaseordertrn.purchaseordertrn_status=0) as total,(select IFNULL(sum(tbl_purchaseordertrn.product_amount_tax),0) from tbl_purchaseordertrn where tbl_purchaseordertrn.purchaseorder_id=tp.purchaseorder_id and tbl_purchaseordertrn.purchaseordertrn_status=0) as totaltax,tp.po_approval_status
							FROM tbl_purchaseorder as tp 
							left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
							left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id where tp.status=0 and tpt.purchaseordertrn_status=0 and tp.po_approval_status=1 " . $where . " and tp.purchaseorder_id in (" . $venor_summary_arr['pos'] . ") group by tp.purchaseorder_id";

				//echo $query;
				$result_summary = $dbcon->query($query);

				if (mysqli_num_rows($result_summary) > 0) {
					$str .= '<tr></tr><tr>
						<td colspan="2" style="border-right: hidden;">
						<strong>Vendorss : </strong>
						</td>
						<td>
						' . $venor_summary_arr['vendorname'] . '
						</td>
						</tr>';

					$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">NO.</th>
			<th width="15%" style="text-align:center">PO No</th>	
			<th width="15%" style="text-align:center">PO Date</th>
			
			<th width="15%" style="text-align:center">Net Amount</th>
			<th width="15%" style="text-align:center">Gross Amount</th>
			<th width="15%" style="text-align:center">Item Status</th>
			<th width="10%" style="text-align:center">PO Status</th>
			</tr>
			';
					$total = 0;
					$j = 1;
					while ($result_summary_arr = mysqli_fetch_assoc($result_summary)) {
						$postatus = checkpopendorcomp($dbcon, $result_summary_arr['purchaseorder_id']);
						$postatus = ($postatus == 1) ? 'Completed' : 'Pending';

						if (isset($POST['po_status'])) {
							if ($POST['po_status'] == 1) {
								//completed status =2
								if ($POST['po_status_id'] == 2) {
									if ($postatus == 'Pending') {
										continue;
									}
								}

								if ($POST['po_status_id'] == 1) {
									if ($postatus == 'Completed') {
										continue;
									}
								}
							}
						}
						/*$po_approval_status=($result_summary_arr["po_approval_status"]==0) ? 'Pending' : 'Approved';*/
						if ($result_summary_arr['po_approval_status'] == 1) {
							$po_approval_status = 'Approved';
						} else if ($result_summary_arr['po_approval_status'] == 2) {
							$po_approval_status = 'Disapproved';
						} else if ($result_summary_arr['po_approval_status'] == 3) {
							$po_approval_status = 'Finance Pending';
						} else if ($result_summary_arr['po_approval_status'] == 4) {
							$po_approval_status = 'Disapproved';
						} else {
							$po_approval_status = 'Pending';
						}
						$t_tax = $result_summary_arr["total"] + $result_summary_arr["totaltax"];
						$postatus = checkpopendorcomp($dbcon, $result_summary_arr['purchaseorder_id']);
						$postatus = ($postatus == 1) ? 'Completed' : 'Pending';
						$purchaseorder_date = '';
						if ($result_summary_arr['purchaseorder_date'] != '') {
							$purchaseorder_date = date('d/m/Y', strtotime($result_summary_arr['purchaseorder_date']));
						}
						$str .= '<tr>
					<td style="text-align:center">' . $j . '</td>
					<td style="text-align:center">' . $result_summary_arr["purchaseorder_no"] . '</td>
					<td style="text-align:center">' . $purchaseorder_date . '</td>
					
					<td style="text-align:center">' . $result_summary_arr["total"] . '</td>
					<td style="text-align:center">' . $t_tax . '</td>
					<td style="text-align:center">' . $postatus . '</td>
					<td style="text-align:center">' . $po_approval_status . '</td>
					</tr>';
						$j++;
					}
				}
			}
		} else {
			$str .= '<tr>
				<td colspan="9" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
		}
		echo $str;
		exit;
	}
	//var_dump($POST);
	if (strtolower($POST['formattype']) == "format2") {

		echo vendorwiseformat2($dbcon, $POST);
		exit;
	}
	if (strtolower($POST['formattype']) == "format3") {

		echo vendorwiseformat3($dbcon, $POST);
		exit;
	}

	$query = "SELECT tl.l_name as vendorname,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id,tp.po_approval_status
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id where tp.status=0 ";

	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		} else {
			$s_date = explode(' - ', $POST['rep_del_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tp.purchaseorder_due_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_due_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		}
	}
	if (isset($POST['specific_vendor'])) {
		if ($POST['vendor_id']) {
			$query .= ' and tp.vender_id=' . $POST['vendor_id'];
		}
	}

	if (isset($POST['specific_item'])) {
		if ($POST['item_id']) {
			$query .= ' and tpt.product_id=' . $POST['item_id'];
		}
	}
	//echo $query.=' group by tp.purchaseorder_id';
	$result = $dbcon->query($query);
	$i = 1;

	if (brp_mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = brp_mysqli_fetch_array($result)) {
			$postatus = checkpopendorcomp($dbcon, $vendor_list['purchaseorder_id']);
			$postatus = ($postatus == 1) ? 'Completed' : 'Pending';

			if (isset($POST['po_status'])) {
				if ($POST['po_status'] == 1) {
					//completed status =2
					if ($POST['po_status_id'] == 2) {
						if ($postatus == 'Pending') {
							continue;
						}
					}

					if ($POST['po_status_id'] == 1) {
						if ($postatus == 'Completed') {
							continue;
						}
					}
				}
			}
			$po_approval_status = ($result_summary_arr["po_approval_status"] == 0) ? 'Pending' : 'Approved';


			$query = "SELECT um.unit_name,umc.unit_name as conv_unit,tpt.unit_id,tpt.rate_unit,tpt.conv_unit_id,tpt.product_id, tl.l_name as vendorname,tpt.product_qty,tpt.product_conv_qty, tpt.used_status, tpt.short_close_qty,tpt.product_discount,tpt.product_amount, (select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending, (select sum(tbl_grn_trn.product_conv_qty) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending_conv,p.product_name,p.product_icode,tpt.product_rate,tpt.product_disc,tpt.product_amount,p.product_desc,tp.purchaseorder_due_date,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid = tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.vender_id=" . $vendor_list['vender_id'] . " and tp.po_approval_status=1 and tpt.purchaseorder_id=" . $vendor_list['purchaseorder_id'];

			if (isset($POST['item_status'])) {
				if ($POST['item_status'] == 1) {
					if ($POST['item_status_id'] == 2) {
						$query .= ' and tpt.used_status=1';
					}

					if ($POST['item_status_id'] == 1) {
						$query .= ' and tpt.used_status=0';
					}
				}
			}

			if (isset($POST['specific_item'])) {
				if ($POST['item_id']) {
					$query .= ' and tpt.product_id=' . $POST['item_id'];
				}
			}
			//echo $query;
			$result1 = $dbcon->query($query);
			$j = 1;
			if (brp_mysqli_num_rows($result1) > 0) {
				$str .= '<tr></tr>
				<tr>
				<td colspan="2" style="border-right:hidden;border-bottom:hidden">
				<strong>Purchase Order NO  : </strong>
				</td>
				<td style="border-bottom:hidden">
				' . $vendor_list['purchaseorder_no'] . '
				</td>
				<td style="border-right:hidden;border-bottom:hidden">
				<strong>P.O Date : </strong>
				</td>
				<td style="border-bottom:hidden">
				' . date('d/m/Y', strtotime($vendor_list['purchaseorder_date'])) . '
				</td>

				<td style="border-right:hidden;border-bottom:hidden">
				<strong>PO Status: </strong>
				</td>
				<td style="border-right:hidden;border-bottom:hidden">
				' . $postatus . '
				</td>
				<td colspan="2" style="border-bottom:hidden"></td>
				</tr>
				<tr>
				<td colspan="2" style="border-right:hidden">
				<strong>Vendor : </strong>
				</td>
				<td >
				' . $vendor_list['vendorname'] . '
				</td>
				<td style="border-right:hidden">
				<strong>Stage: </strong>
				</td>
				<td>
				' . $po_approval_status . '
				</td>
				<td colspan="4"></td>
				</tr>';
				$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
				<th width="5%" style="text-align:center">NO.</th>
				<th width="20%" style="text-align:center">Item Details</th>';

				$str .= '<th width="10%" style="text-align:center">Quantity</th>
				<th width="10%" style="text-align:center">Pending Quantity</th>
				<th width="10%" style="text-align:center">Shortclose Qty</th>
				<th width="13%" style="text-align:center">Rate</th>
				<th width="10%" style="text-align:center">Discount</th>
				<th width="10%" style="text-align:center">Amount <br> Pending Amount</th>
				<th width="10%" style="text-align:center">Item status</th>
				</tr>';

				$tot_amt = 0;
				$tot_pend_amt = 0;
				while ($re = brp_mysqli_fetch_array($result1)) {
					//$re["product_qty"]."<br>";
					$pen = $re["product_qty"] - $re["pending"];
					$conv_pen = $re["product_conv_qty"] - $re["pending_conv"];

					$tot_amt = $tot_amt + $re['product_amount'];

					//$item_status=($re["remaining"]>0) ? 'Pending' : 'Completed';

					if ($re['unit_id'] != $re['conv_unit_id']) {
						$poqty = '<span style="color:green"><strong>' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong></span><br>
						<span style="color:orange"><strong>' . $re['product_conv_qty'] . ' ' . $re['conv_unit'] . '</strong></span>';
						$penqty = '<span style="color:green"><strong>' . $pen . ' ' . $re['unit_name'] . '</strong></span><br>
						<span style="color:orange"><strong>' . $conv_pen . ' ' . $re['conv_unit'] . '</strong></span>';
					} else {
						$poqty = '<span style="color:green"><strong>' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong></span>';

						$penqty = '<span style="color:green"><strong>' . $pen . ' ' . $re['unit_name'] . '</strong></span>';
					}

					if ($re['used_status'] == '1') {
						if ($re['short_close_qty'] != "") {
							$short_qty = '<span style="color:green"><strong>' . $re['short_close_qty'] . ' ' . $re['conv_unit'] . '</strong></span>';
						} else {
							$short_qty = '';
						}
						$item_status = 'Completed';
						$pening_amt = '0.00';
					} else {
						$short_qty = '';
						$item_status = 'Pending';
						if ($re['unit_id'] 	== $re['rate_unit']) {
							$pening_amt = $re["product_rate"] * $pen;
						} else {
							$pening_amt = $re["product_rate"] * $conv_pen;
						}
					}
					$tot_pend_amt = $tot_pend_amt + $pening_amt;
					$str .= '<tr style="  border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $i . '</td>
					<td style="text-align:center">' . $re["product_name"] . ' -- ' . $re["product_icode"] . '</td>';

					$str .= '<td style="text-align:center">' . $poqty . '</td>
					<td style="text-align:center">' . $penqty . '</td>
					<td style="text-align:center">' . $short_qty . '</td>
					<td style="text-align:center">' . number_format($re["product_rate"], 2) . '</td>
					<td style="text-align:center">' . number_format($re["product_discount"], 2) . '</td>
					<td style="text-align:center">' . number_format($re["product_amount"], 2) . '<br>' . number_format($pening_amt, 2) . '</td>
					
					<td style="text-align:center">' . $item_status . '</td>
					</tr>';
					$j++;
				}
				$str .= '<tr>
				<td colspan="5" style="text-align:center"></td>
				<td colspan="2" style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:right;border-right:hidden"><strong>Net Amount : <br>Pend Net Amount : </strong></td>
				<td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;">' . number_format($tot_amt, 2) . ' <br>' . number_format($tot_pend_amt, 2) . '</td>
				<td style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;text-align:center;"></td>
				</tr>';
			}
		}
	} else {
		$str .= '<tr>
		<td colspan="9" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "vendorwisesummaryreport" || strtolower($POST['mode']) == "allsummary") {
	$tot_last_po_qty = 0;
	$tot_last_req_qty = 0;
	$tot_last_pen_qty = 0;
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];
	$pr_row = get_product_detail($dbcon, $product_id);
	$comany_cnfg = getCompanyConfiguration($dbcon);
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = brp_mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['vendor_id'];
	$cust_rel = brp_mysqli_fetch_assoc($dbcon->query($qrycust));

	if ($POST['withconv'] == 1) {
		$colspan = "8";
	} else {
		$colspan = "7";
	}
	$str .= '<table  width="100%"   class="display">
	</table>
	<table  class="display table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="' . $colspan . '" style="border-bottom:hidden">
	<strong>[Purchase Detail]</strong>
	</td>
	</tr>';

	$query = "SELECT tl.l_name as vendorname,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id 
	left JOIN product_mst as p ON p.product_id=tpt.product_id 
	";
	$s_date = explode(' - ', $POST['rep_po_date']);
	$startdate = $s_date[0];
	$enddate = $s_date[1];
	$query .= " where tp.po_approval_status=1 and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";

	/*if($POST['po_date_type']){
		if($POST['po_date_type']=='po'){
			$s_date=explode(' - ',$POST['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" where tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
		}else{
			$s_date=explode(' - ',$POST['rep_po_date']);
			$startdate=$s_date[0];
			$enddate=$s_date[1];
			$query.=" where tp.purchaseorder_date>='".date('Y-m-d',strtotime($startdate))."' and tp.purchaseorder_date<='".date('Y-m-d',strtotime($enddate))."'";
		}

	}*/
	if ($POST['vendor_id']) {
		$query .= ' and tp.vender_id=' . $POST['vendor_id'];
	}
	/*if(isset($POST['specific_vendor'])){
		if($POST['vendor_id']){
			$query.=' and tp.vender_id='.$POST['vendor_id'];
		}
	}*/
	if ($POST['item_id']) {
		$query .= ' and tpt.product_id=' . $POST['item_id'];
	}
	/*if(isset($POST['specific_item'])){
		if($POST['item_id']){
			$query.=' and tpt.product_id='.$POST['item_id'];
		}
	}*/
	$query .= ' group by tp.vender_id';
	if ($POST['item_status_id'] == 2) {
		$query .= ' having remaining <= 0';
	}

	if ($POST['item_status_id'] == 1) {
		$query .= ' having remaining > 0';
	}
	//echo $query;
	/*if(isset($POST['item_status'])){
		if($POST['item_status']==1){
			if($POST['item_status_id']==2){
				$query.=' having remaining <= 0';
			}

			if($POST['item_status_id']==1){
				$query.=' having remaining > 0';

			}
		}
	}
*/

	//echo $query;
	$result = $dbcon->query($query);
	$i = 1;

	if (brp_mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = brp_mysqli_fetch_assoc($result)) {
			if ($POST['withconv'] == 1) {
				$col = "6";
			} else {
				$col = "5";
			}
			$str .= '<tr style="margin-top:15px;">
			<td colspan="2" style="border-right:hidden">
			<strong>Vendor  : </strong>
			</td>
			<td colspan="' . $col . '">
			' . $vendor_list['vendorname'] . '
			</td>
			</tr>';

			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">NO.</th>
			<th width="10%" style="text-align:center">PO No. <br> PO Date</th>';

			if ($comany_cnfg['po_work_order_wise'] == 1) {
				$str .= '<th width="10%" style="text-align:center">WO No. <br> WO Date</th>';
			}

			if ($POST['withconv'] == 1) {

				$str .= '<th width="20%" style="text-align:center">Item Details</th>
				';
			}
			$str .= '<th width="12%" style="text-align:center">P.O.Qty</th>
			<th width="12%" style="text-align:center">Rcv.Qty</th>
			<th width="12%" style="text-align:center;color:red;">Pending Qty</th>
			<th width="20%" style="text-align:center">Del.Date</th></tr>';
			$query = "SELECT um.unit_name, umc.unit_name as conv_unit,tsmp.po_req_no as workorderno,tsmp.po_req_date as workorderdate,tp.purchaseorder_no,tpt.unit_id,tpt.conv_unit_id,tpt.product_id, tl.l_name as vendorname, trp.indent_no, trp.indent_date, trp.indent_date, tpt.product_qty, tpt.product_conv_qty, p.product_name, p.product_desc, tp.purchaseorder_due_date, tpt.purchaseordertrn_id, (select IFNULL(sum(tbl_grn_trn.product_qty),0)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id and tbl_grn_trn.grn_trn_status=0 ) as pending, (select IFNULL(sum(tbl_grn_trn.product_conv_qty),0)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id and tbl_grn_trn.grn_trn_status=0) as pending_conv,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trpc ON tpt.po_ref_id=trpc.rp_id
			left JOIN tbl_set_main_process as tsmp ON tsmp.sp_id=trpc.sp_id

			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.status=0 and tp.vender_id=" . $vendor_list['vender_id'] . " and tp.po_approval_status=1";

			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";

			if ($POST['item_id']) {
				$query .= ' and tpt.product_id=' . $POST['item_id'];
			}
			//echo $query."<br><br>";
			$result1 = $dbcon->query($query);
			$j = 1;
			$tot_po_qty = 0;
			$tot_po_cqty = 0;
			$tot_req_qty = 0;
			$tot_req_cqty = 0;
			$tot_pen_qty = 0;
			$tot_pen_cqty = 0;
			if (brp_mysqli_num_rows($result1) > 0) {
				while ($re = brp_mysqli_fetch_assoc($result1)) {
					$pen = $re["product_qty"] - $re["pending"];
					$pen_conv = $re['product_conv_qty'] - $re['pending_conv'];
					$tot_po_qty = $tot_po_qty + $re["product_qty"];
					$tot_last_po_qty = $tot_last_po_qty + $re["product_qty"];
					$tot_req_qty = $tot_req_qty + $re["pending"];
					$tot_last_req_qty = $tot_last_req_qty + $re["pending"];
					$tot_pen_qty = $tot_pen_qty + $pen;

					$tot_last_pen_qty = $tot_last_pen_qty + $pen;
					$intent_date = '';
					if ($re['indent_date'] != '') {
						$intent_date = date('d/m/Y', strtotime($re['indent_date']));
					}
					/*$purchase_order_due_date='';
					if($re['purchaseorder_due_date']!=''){
						$purchase_order_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
					}*/

					$purchase_order_due_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);
					$workorderdate = '';
					if ($re['workorderdate'] != '') {
						$workorderdate = date('d/m/Y', strtotime($re['workorderdate']));
					}
					//$re["pending"]=($re["pending"]=='') ? 0 :$re["pending"];
					if ($re['unit_id'] != $re['conv_unit_id']) {
						$tot_po_cqty = $tot_po_cqty + $re['product_conv_qty'];
						$tot_req_cqty = $tot_req_cqty + $re["pending_conv"];
						$tot_pen_cqty = $tot_pen_cqty + $pen_conv;
						$tot_last_po_cqty = $tot_last_po_cqty + $re["product_conv_qty"];
						$tot_last_req_cqty = $tot_last_req_cqty + $re["pending_conv"];
						$tot_last_pen_cqty = $tot_last_pen_cqty + $pen_conv;

						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
						$rcv = "<strong style='color:green'>" . $re['pending'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['pending_conv'] . " " . $re['conv_unit'] . "</strong>";
						$pen = "<strong>" . $pen . " " . $re['unit_name'] . "</strong><br><strong>" . $pen_conv . " " . $re['conv_unit'] . "</strong>";
					} else {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
						$rcv = "<strong style='color:green'>" . $re['pending'] . " " . $re['unit_name'] . "</strong>";
						$pen = "<strong>" . $pen . " " . $re['unit_name'] . "</strong>";
					}

					$str .= '<tr style="  border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $j . '</td>
					<td style="text-align:center">' . $re["purchaseorder_no"] . $intent_date . '</td>';
					if ($comany_cnfg['po_work_order_wise'] == 1) {
						$str .= '<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td>';
					}


					if ($POST['withconv'] == 1) {
						$str .= '<td style="text-align:center">' . $re["product_name"] . '</td>';
					}
					$str .= '<td style="text-align:center">' . $qty . '</td>
					<td style="text-align:center">' . $rcv . '</td>
					<td style="text-align:center;color:red;">' . $pen . '</td>
					<td style="text-align:center">' . $purchase_order_due_date . '</td>
					</tr>';
					$j++;
				}

				if ($POST['withconv'] == 1) {
					if ($comany_cnfg['po_work_order_wise'] == 1) {
						$colspan = 3;
					} else {
						$colspan = 3 - 1;
					}
				} else {
					if ($comany_cnfg['po_work_order_wise'] == 1) {
						$colspan = 2;
					} else {
						$colspan = 2 - 1;
					}
				}

				$str .= '<tr style="border-top:0.5px #000 solid;">
				<td colspan=' . $colspan . '></td>
				<td style="text-align:center">Total :</td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_po_qty, 2) . '</strong> <br> <strong style="color:orange">' . number_format($tot_po_cqty, 2) . '</strong></td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_req_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_req_cqty, 2) . '</strong></td>
				<td style="text-align:center;color:red;"><strong>' . number_format($tot_pen_qty, 2) . '</strong><br><strong>' . number_format($tot_pen_cqty, 2) . '</strong></td>
				<td style="text-align:center;color:red;"></td>
				</tr>';
			} else {
				$str .= '<tr>
				<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
		}

		$str .= '<tr>
		<td colspan=' . $colspan . '></td>
		
		<td style="border-top:0.5px #000 solid;text-align:center;">Grand Total :</td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_po_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_last_po_cqty, 2) . '</strong></td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_req_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_last_req_cqty, 2) . '</strong></td>
		<td style="text-align:center;color:red;border-top:0.5px #000 solid;"><strong>' . number_format($tot_last_pen_qty, 2) . '</strong><br><strong>' . number_format($tot_last_pen_cqty, 2) . '</strong</td>
		<td style="text-align:center;color:red;border-top:0.5px #000 solid;"></td>
		</tr>';
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "allwiseratesummaryreport") {
	$tot_last_po_qty = 0;
	$tot_last_req_qty = 0;
	$tot_last_pen_qty = 0;
	$cust_id = $POST['cust_id'];
	$product_id = $POST['product_id'];
	$pr_row = get_product_detail($dbcon, $product_id);
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['cust_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[Purchase Detail]</strong>
	</td>
	</tr>';

	$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
	<th width="5%" style="text-align:center">NO.</th>
	<th width="10%" style="text-align:center">PO No.</th>';

	if ($POST['withconv'] == 1) {

		$str .= '<th width="20%" style="text-align:center">Item Details</th>
		';
	}
	$str .= '<th width="12%" style="text-align:center">P.O.Qty</th>
	<th width="12%" style="text-align:center">Rate</th>
	<th width="12%" style="text-align:center">Disc</th>
	<th width="12%" style="text-align:center">Amount</th>
	<th width="12%" style="text-align:center">Chn No. <br> Chn Date</th>
	<th width="12%" style="text-align:center">Bill No. <br> Bill Date</th>
	<th width="12%" style="text-align:center">Bill Qty</th>
	<th width="12%" style="text-align:center">Bill Amount</th>
	<th width="12%" style="text-align:center">WO No</th>';

	$query = "SELECT um.unit_name,tp.vender_id,tpt.product_id,tsmp.po_req_no as workorderno,tsmp.po_req_date as workorderdate,tp.purchaseorder_no,tpt.unit_id,tpt.product_id, tl.l_name as vendorname,trp.indent_no,trp.indent_date,trp.indent_date,tpt.product_qty,tpt.product_rate,tpt.product_discount,tpt.used_qty,p.product_name,p.product_desc,tp.purchaseorder_due_date,(select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_request_product as trpc ON tpt.po_ref_id=trpc.rp_id
	left JOIN tbl_set_main_process as tsmp ON tsmp.sp_id=trpc.sp_id
	left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
	left JOIN unit_mst as um ON um.unitid=tpt.unit_id
	left JOIN product_mst as p ON p.product_id=tpt.product_id";
	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		} else {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		}
	}
	if (isset($POST['specific_vendor'])) {
		if ($POST['vendor_id']) {
			$query .= ' and tp.vender_id=' . $POST['vendor_id'];
		}
	}

	if (isset($POST['specific_item'])) {
		if ($POST['item_id']) {
			$query .= ' and tpt.product_id=' . $POST['item_id'];
		}
	}

	if (isset($POST['item_status'])) {
		if ($POST['item_status'] == 1) {
			if ($POST['item_status_id'] == 2) {
				$query .= ' having remaining <= 0';
			}

			if ($POST['item_status_id'] == 1) {
				$query .= ' having remaining > 0';
			}
		}
	}

	$result1 = $dbcon->query($query);
	$j = 1;
	$tot_po_qty = 0;
	$tot_req_qty = 0;
	$tot_pen_qty = 0;
	if (mysqli_num_rows($result1) > 0) {
		while ($re = mysqli_fetch_assoc($result1)) {
			$pen = $re["product_qty"] - $re["pending"];
			$tot_po_qty = $tot_po_qty + $re["product_qty"];
			$tot_last_po_qty = $tot_last_po_qty + $re["product_qty"];
			$tot_req_qty = $tot_req_qty + $re["pending"];
			$tot_last_req_qty = $tot_last_req_qty + $re["pending"];
			$tot_pen_qty = $tot_pen_qty + $pen;
			$tot_last_pen_qty = $tot_last_pen_qty + $pen;
			$intent_date = '';
			if ($re['indent_date'] != '') {
				$intent_date = date('d/m/Y', strtotime($re['indent_date']));
			}
			$purchase_order_due_date = '';
			if ($re['purchaseorder_due_date'] != '') {
				$purchase_order_due_date = date('d/m/Y', strtotime($re['purchaseorder_due_date']));
			}
			$workorderdate = '';
			if ($re['workorderdate'] != '') {
				$workorderdate = date('d/m/Y', strtotime($re['workorderdate']));
			}
			$re["pending"] = ($re["pending"] == '') ? 0 : $re["pending"];

			$tot_amnt = $tot_amnt + ($re["product_qty"] * $re["product_rate"]);
			$tot_pending_amnt = $tot_pending_amnt + ($pen * $re["product_rate"]);
			$workorderdate = '';

			$checkbilldata = getbilldata($dbcon, $re['vender_id'], $re['product_id']);

			if ($checkbilldata->num_rows > 0) {
				$k = 1;
				foreach ($checkbilldata as $key_bill => $value_bill) {
					$str .= '<tr style="  border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $k . '</td>
					<td style="text-align:center">' . $re["purchaseorder_no"] . '</td>';

					if ($POST['withconv'] == 1) {
						$str .= '<td style="text-align:center">' . $re["product_name"] . '<br>' . $re["product_desc"] . '</td>';
					}
					$str .= '
					<td style="text-align:center">' . $re["product_qty"] . '</td>
					<td style="text-align:center">' . $re["product_rate"] . '</td>
					<td style="text-align:center">' . $re["product_discount"] . '</td>
					<td style="text-align:center;">' . $re["product_qty"] * $re["product_rate"] . '</td>
					<td>' . $value_bill["challan_no"] . '</td>
					<td>' . $value_bill["billno"] . '<br>' . $value_bill["billdate"] . '</td>
					<td>' . $value_bill["billqty"] . '</td>
					<td>' . $value_bill["billtotal"] . '</td>
					<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td></tr>';
					$k++;
				}
			} else {
				$str .= '<tr style="  border: 1px dashed #cccccc;">
				<td style="text-align:center">' . $j . '</td>
				<td style="text-align:center">' . $re["purchaseorder_no"] . '</td>';

				if ($POST['withconv'] == 1) {
					$str .= '<td style="text-align:center">' . $re["product_name"] . '<br>' . $re["product_desc"] . '</td>';
				}
				$str .= '
				<td style="text-align:center">' . $re["product_qty"] . '</td>
				<td style="text-align:center">' . $re["product_rate"] . '</td>
				<td style="text-align:center">' . $re["product_discount"] . '</td>
				<td style="text-align:center;">' . $re["product_qty"] * $re["product_rate"] . '</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td></tr>';
				$j++;
			}
		}

		if ($POST['withconv'] == 1) {
			$colspan = 2;
		} else {
			$colspan = 1;
		}

		$tot_last_amount = $tot_last_amount + ($re["product_qty"] * $re["product_rate"]);

		$str .= '<tr >
		<td colspan=' . $colspan . '></td>
		<td style="border-top:0.5px #000 solid;text-align:center;">Grand Total :</td>
		<td style="text-align:center;border-top:0.5px #000 solid;">' . number_format($tot_po_qty, 2) . '</td>

		<td style="text-align:center;border-top:0.5px #000 solid;"></td>
		<td style="text-align:center;border-top:0.5px #000 solid;"></td>
		<td style="text-align:center;border-top:0.5px #000 solid;">' . number_format($tot_amnt, 2) . '</td>
		</tr>';
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}

	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "vendorwiseratesummaryreport") {
	$tot_last_po_qty = 0;
	$tot_last_req_qty = 0;
	$tot_last_pen_qty = 0;
	$tot_last_amount = 0;
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];
	$comany_cnfg = getCompanyConfiguration($dbcon);
	$pr_row = get_product_detail($dbcon, $product_id);
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['vendor_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"  class="display">
	</table>
	<table  class="display table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="10" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="11">
	<strong>[Purchase Detail]</strong>
	</td>
	</tr>';

	$query = "SELECT tl.l_name as vendorname,tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date,tp.vender_id,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
	FROM tbl_purchaseorder as tp
	left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id 
	left JOIN product_mst as p ON p.product_id=tpt.product_id  where tp.status=0 and 	po_approval_status=1";

	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		} else {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		}
	}
	if (isset($POST['specific_vendor'])) {
		if ($POST['vendor_id']) {
			$query .= ' and tp.vender_id=' . $POST['vendor_id'];
		}
	}

	if (isset($POST['specific_item'])) {
		if ($POST['item_id']) {
			$query .= ' and tpt.product_id=' . $POST['item_id'];
		}
	}

	$query .= ' group by tp.vender_id';
	if (isset($POST['item_status'])) {
		if ($POST['item_status'] == 1) {
			if ($POST['item_status_id'] == 2) {
				$query .= ' having remaining <= 0';
			}

			if ($POST['item_status_id'] == 1) {
				$query .= ' having remaining > 0';
			}
		}
	}
	//echo $query;
	$result = $dbcon->query($query);
	$i = 1;

	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {

			$str .= '<tr style="margin-top:15px;">
			<td colspan="2">
			<strong>Vendor  : </strong>
			</td>
			<td colspan="9">
			' . $vendor_list['vendorname'] . '
			</td>
			</tr>';

			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">NO.</th>
			<th width="10%" style="text-align:center">PO No.</th>';

			if ($POST['withconv'] == 1) {
				$str .= '<th width="15%" style="text-align:center">Item Details</th>
				';
			}
			$str .= '
			<th width="8%" style="text-align:center">P.O.Qty</th>
			<th width="8%" style="text-align:center">Rate</th>
			<th width="8%" style="text-align:center">Disc</th>
			<th width="8%" style="text-align:center">Amount</th>
			<th width="8%" style="text-align:center">Chn No. <br> Chn Date</th>
			<th width="8%" style="text-align:center">Bill No. <br> Bill Date</th>
			<th width="8%" style="text-align:center">Bill Qty</th>
			<th width="8%" style="text-align:center">Bill Amount</th>';
			if ($comany_cnfg['po_work_order_wise'] == 1) {
				$str .= '<th width="8%" style="text-align:center">WO No</th>';
			}


			$query = "SELECT tsmp.po_req_no as workorderno, unit.unit_name, cunit.unit_name as conv_unit, tpt.purchaseordertrn_id, tsmp.po_req_date as workorderdate, tp.vender_id, tp.purchaseorder_id,tp.purchaseorder_no, tpt.unit_id, tpt.conv_unit_id, tpt.product_id,tpt.product_rate, tpt.product_disc, tl.l_name as vendorname, trp.indent_no, trp.indent_date, trp.indent_date, tpt.product_qty, tpt.product_conv_qty, p.product_name, p.product_desc, tp.purchaseorder_due_date, (select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trpc ON tpt.po_ref_id=trpc.rp_id
			left JOIN tbl_set_main_process as tsmp ON tsmp.sp_id=trpc.sp_id
			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as unit on unit.unitid = tpt.unit_id
			left JOIN unit_mst as cunit on cunit.unitid = tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.vender_id=" . $vendor_list['vender_id'] . " and tp.status=0 and 	tp.po_approval_status=1";

			//echo $query;
			$result1 = $dbcon->query($query);
			$tot_po_qty = 0;
			$tot_po_cqty = 0;
			$tot_req_qty = 0;
			$tot_pen_qty = 0;
			$tot_amnt = 0;
			if (brp_mysqli_num_rows($result1) > 0) {
				$j = 1;
				while ($re = brp_mysqli_fetch_assoc($result1)) {
					$challan_no_data = getallchallanno($dbcon, $re["product_id"], $re["purchaseordertrn_id"], 'challan_no');
					$chllanhtml = '-';
					if (count($challan_no_data)) {
						$chllanhtml = '<table border="2px solid:">
				    	 				<tr>
				    	 				<th> No</th>
				    	 				<th>Date</th>
				    	 				</tr>
				    	 				';
						foreach ($challan_no_data as $key_c => $value_c) {
							$chllanhtml .= '<tr>
				    	 				<th>' . $value_c['challan_no'] . '</th>
				    	 				<th>' . $value_c['grn_date'] . '</th>
				    	 				</tr>
				    	 				';
						}
						$chllanhtml .= '</table>';
					}


					if ($re['unit_id'] != $re['conv_unit_id']) {
						$tot_po_cqty = $tot_po_cqty + $re["product_conv_qty"];
						$tot_last_po_cqty = $tot_last_po_qty + $re['product_conv_qty'];
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
					} else {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
					}


					$pen = $re["product_qty"] - $re["pending"];
					$tot_po_qty = $tot_po_qty + $re["product_qty"];
					$tot_last_po_qty = $tot_last_po_qty + $re["product_qty"];
					$tot_req_qty = $tot_req_qty + $re["pending"];
					$tot_last_req_qty = $tot_last_req_qty + $re["pending"];
					$tot_pen_qty = $tot_pen_qty + $pen;
					$tot_last_pen_qty = $tot_last_pen_qty + $pen;
					$intent_date = '';
					if ($re['indent_date'] != '') {
						$intent_date = date('d/m/Y', strtotime($re['indent_date']));
					}
					$purchase_order_due_date = '';
					if ($re['purchaseorder_due_date'] != '') {
						$purchase_order_due_date = date('d/m/Y', strtotime($re['purchaseorder_due_date']));
					}
					$workorderdate = '';
					if ($re['workorderdate'] != '') {
						$workorderdate = date('d/m/Y', strtotime($re['workorderdate']));
					}
					$re["pending"] = ($re["pending"] == '') ? 0 : $re["pending"];

					$tot_amnt = $tot_amnt + ($re["product_qty"] * $re["product_rate"]);

					$que_gr = "select group_concat(grn_trn_id) as trn_id  from tbl_grn_trn where grn_trn_status=0 and purchaseordertrn_id=" . $re['purchaseordertrn_id'];
					$que_ge = $dbcon->query($que_gr);

					$que_gf = brp_mysqli_fetch_array($que_ge);

					$checkbilldata = getbilldata($dbcon, $que_gf['trn_id'], $re['product_id']);

					$billhtml = '';
					if ($checkbilldata->num_rows > 0) {
						$k = 1;
						foreach ($checkbilldata as $key_bill => $value_bill) {

							$str .= '<tr style="  border: 1px dashed #cccccc;">
							<td style="text-align:center">' . $j . '</td>
							<td style="text-align:center">' . $re["purchaseorder_no"] . '</td>';

							if ($POST['withconv'] == 1) {
								$str .= '<td style="text-align:center">' . $re["product_name"] . '</td>';
							}
							$str .= '
							<td style="text-align:center">' . $qty . '</td>
							<td style="text-align:center">' . $re["product_rate"] . '</td>
							<td style="text-align:center">' . $re["product_disc"] . '</td>
							<td style="text-align:center">' . $re["product_qty"] * $re["product_rate"] . '</td>

							<td>' . $chllanhtml . '</td>';

							if ($value_bill['billu'] != $value_bill['billcu']) {
								$valqty = '<strong style="color:green">' . $value_bill['billqty'] . ' ' . $value_bill['unit_name'] . '</strong><br><strong style="color:orange">' . $value_bill['billcqty'] . ' ' . $value_bill['conv_unit'] . '</strong>';
							} else {
								$valqty = '<strong style="color:green">' . $value_bill['billqty'] . ' ' . $value_bill['unit_name'] . '</strong>';
							}

							$str .= '<td>' . $value_bill["billno"] . '<br>' . $value_bill["billdate"] . '</td>
							<td>' . $valqty . '</td>
							<td>' . $value_bill["billtotal"] . '</td>';
							if ($comany_cnfg['po_work_order_wise'] == 1) {
								$str .= '<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td>';
							}
							$str .= '</tr>';
							$k++;
						}
					} else {

						$challan_no_data = getallchallanno($dbcon, $re["product_id"], $re["purchaseordertrn_id"], 'challan_no');
						$chllanhtml = '-';
						if (count($challan_no_data)) {
							$chllanhtml = '<table border="2px solid:">
				    	 				<tr>
				    	 				<th> No</th>
				    	 				<th> Date</th>
				    	 				</tr>
				    	 				';
							foreach ($challan_no_data as $key_c => $value_c) {
								$chllanhtml .= '<tr>
				    	 				<th>' . $value_c['challan_no'] . '</th>
				    	 				<th>' . $value_c['grn_date'] . '</th>
				    	 				</tr>
				    	 				';
							}
							$chllanhtml .= '</table>';
						}
						$str .= '<tr style="border: 1px dashed #cccccc;">
						<td style="text-align:center">' . $j . '</td>
						<td style="text-align:center">' . $re["purchaseorder_no"] . '</td>';

						if ($POST['withconv'] == 1) {
							$str .= '<td style="text-align:center">' . $re["product_name"] . '</td>';
						}
						$str .= '
						<td style="text-align:center">' . $qty . '</td>
						<td style="text-align:center">' . $re["product_rate"] . '</td>
						<td style="text-align:center">' . $re["product_disc"] . '</td>
						<td style="text-align:center">' . $re["product_qty"] * $re["product_rate"] . '</td>
						<td>' . $chllanhtml . '</td>
						<td></td>
						<td></td>
						<td></td>';
						if ($comany_cnfg['po_work_order_wise'] == 1) {
							$str .= '<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td>';
						}

						$str .= '</tr>';
					}
					$j++;
				}

				if ($POST['withconv'] == 1) {
					$colspan = 2;
				} else {
					$colspan = 1;
				}
				$tot_last_amount = $tot_last_amount + $tot_amnt;
				$str .= '<tr style="border-top:0.5px #000 solid;">
				<td colspan=' . $colspan . '></td>
				<td style="text-align:center">SubTotal :</td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_po_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_po_cqty) . '</strong></td>
				<td style="text-align:center"></td>
				<td style="text-align:center"></td>
				<td style="text-align:center;">' . number_format($tot_amnt, 2) . '</td>
				<td style="text-align:center;" colspan="5"></td>
				</tr>';
			} else {
				$str .= '<tr>
				<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
		}
		$str .= '<tr >
		<td colspan=' . $colspan . '></td>
		<td style="border-top:0.5px #000 solid;text-align:center;">Grand Total :</td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_po_qty, 2) . '</style></td>
		<td style="text-align:center;border-top:0.5px #000 solid;"></td>
		<td style="border-top:0.5px #000 solid;text-align:center;"></td>
		<td style="text-align:center;color:red;border-top:0.5px #000 solid;"><strong style="">' . number_format($tot_last_amount, 2) . '</td>
		<td style="text-align:center;" colspan="5"></td>
		</tr>';
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "itemwisereport") {
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['cust_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['cust_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="display table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>Item Wise Pending Purchase Order List </strong>
	</td>
	</tr>';

	$query = "SELECT group_concat(tp.purchaseorder_id) as poids,tp.purchaseorder_id,tp.product_id,p.product_icode,p.product_name,p.product_icode,p.product_desc FROM tbl_purchaseordertrn as tp left JOIN product_mst as p ON tp.product_id=p.product_id left JOIN tbl_purchaseorder as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id ";
	$where = '';
	if ($POST['product_id']) {
		$where = " and tp.product_id=" . $POST['product_id'];
	}
	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {

			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tpt.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "' " . $where . " group by tp.product_id";
		} else {
			$s_date = explode(' - ', $POST['rep_del_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tpt.purchaseorder_due_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_due_date<='" . date('Y-m-d', strtotime($enddate)) . "' " . $where . " group by tp.product_id";
		}
	}

	$result = $dbcon->query($query);
	$i = 1;

	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {
			$str .= '<tr style="margin-top:15px;">
			<td colspan="2" style="border-right:hidden;border-bottom:hidden">
			<strong>Item Code aa : </strong>
			</td>
			<td colspan="5" style="border-bottom:hidden">
			' . $vendor_list['product_icode'] . '
			</td>
			</tr>
			<tr >
			<td colspan="2" style="border-right:hidden;">
			<strong>Item Name  : </strong>
			</td>
			<td colspan="5" >
			' . $vendor_list['product_name'] . '
			</td>
			</tr>

			<!--<tr >
			<td colspan="2">
			<strong>Drawing NO / Version  : </strong>
			</td>
			<td colspan="5">
			' . $vendor_list['product_desc'] . '
			</td>
			</tr>-->';


			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">

			<th width="9%" style="text-align:center;padding:10px 5px">Indent No. PO NO</th>
			<th width="10%" style="text-align:center">Date Date</th>
			<th width="20%" style="text-align:center">Vendor</th>';

			/*if($POST['withconv']==1){
				$str.='<th width="10%" style="text-align:center">UOM <br>Conv.UOM</th>
				<th width="10%" style="text-align:center">P.O.Qty <br> Conv.Qty</th>
				<th width="23%" style="text-align:center">Pen Qty <br> Conv Pend Qty</th></tr>';
			}else{*/
			$str .= '<!--<th width="12%" style="text-align:center">UOM</th>-->
				<th width="10%" style="text-align:center">P.O.Qty</th>
				<th width="10%" style="text-align:center">Pending Qty</th>
				<th width="10%" style="text-align:center">Del. Date</th></tr>';

			//}

			$tot_qty = 0;
			$tot_pend_qty = 0;
			$query = "SELECT um.unit_name,umc.unit_name as conv_unit,tpt.product_id,tpt.unit_id,tpt.conv_unit_id, tl.l_name as vendorname, trp.indent_no,trp.indent_date,tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no,trp.indent_date,tpt.product_qty,tpt.product_conv_qty,tpt.used_qty, (select sum(gtr.product_qty) from tbl_grn_trn as gtr where gtr.grn_trn_status=0 and gtr.purchaseordertrn_id=tpt.purchaseordertrn_id) as used_qty, (select sum(gtr.product_conv_qty) from tbl_grn_trn as gtr where gtr.grn_trn_status=0 and gtr.purchaseordertrn_id=tpt.purchaseordertrn_id) as usedc_qty, p.product_name,tpt.po_ref_id,tpt.purchaseordertrn_id
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.purchaseorder_id in (" . (($vendor_list['poids'])) . ") and tpt.product_id=" . $vendor_list['product_id'];
			$query;
			$result1 = $dbcon->query($query);
			$j = 1;
			if (mysqli_num_rows($result1) > 0) {
				while ($re = mysqli_fetch_assoc($result1)) {
					$penq = $re["product_qty"] - $re["used_qty"];
					$pencq = $re["product_conv_qty"] - $re["usedc_qty"];
					$tot_pend_qty = $tot_pend_qty + $penq;
					$tot_qty = $tot_qty + $re["product_qty"];
					if ($re['unit_id'] != $re['conv_unit_id']) {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
						$pen = "<strong style='color:green'>" . $penq . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $pencq . " " . $re['conv_unit'] . "</strong>";
						$tot_cqty = $tot_cqty + $re["product_conv_qty"];
						$tot_pend_cqty = $tot_pend_cqty + $pencq;
					} else {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
						$pen = "<strong style='color:green'>" . $penq . " " . $re['unit_name'] . "</strong>";
					}
					$indent_no = get_indent_data($dbcon, $re['po_ref_id'], 'indent_no');
					$indent_date  = get_indent_data($dbcon, $re['po_ref_id'], 'indent_date');

					$del_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);
					$po_date = '';
					if (!empty($re["purchaseorder_date"])) {
						$po_date = date('d-m-Y', strtotime($re["purchaseorder_date"]));
					}
					/*$intent_date='';
					if($re['indent_date']!=''){
						$intent_date=date('d/m/Y',strtotime($re['indent_date']));
					}*/
					/*$unit=get_alter_unit($dbcon,$re["product_id"],$re["unit_id"]);
					$convert=convert_stock($dbcon,$re["product_qty"],$re["product_id"],$unit);
					$pend_conv=convert_stock($dbcon,$pen,$re["product_id"],$unit);*/
					if ($penq > 0) {
						$str .= '<tr style="  border: 1px dashed #cccccc;">

						<td style="text-align:center">' . $indent_no . $re["purchaseorder_no"] . '</td>
						<td style="text-align:center">' . $indent_date . ' ' . $po_date . '</td>
						<td style="text-align:center">' . $re["vendorname"] . '</td>';
						$j++;
						/*if($POST['withconv']==1){
							$str.='<td style="text-align:center">'.$re["unit_name"].'<br>'.$unit.'</td>
							<td style="text-align:center">'.$re["product_qty"].'<br>'.$convert.'</td>
							<td style="text-align:center">'.$pen.'<br>'.$pend_conv.'</td>
							</tr>';
						}else{*/
						$str .= '<!--<td style="text-align:center">' . $re["unit_name"] . '</td>-->
							<td style="text-align:center">' . $qty . '</td>
							<td style="text-align:center">' . $pen . '</td>
							<td style="text-align:center">' . $del_date . '</td>
							</tr>';
					}
				}
				$str .= '<tr style="border-top:0.5px #000 solid;">

				<td colspan="2"></td>
				<td ><strong>Total :</strong></td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_cqty, 2) . '</strong></td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_pend_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_pend_cqty, 2) . '</strong></td>
				<td style="text-align:center"></td>
				</tr>';
			}
		}
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "itemwisebriefreport") {
	if (strtolower($POST['formattype']) == "format2") {
		echo itemwiseformat2($dbcon, $POST);
		exit;
	}
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['cust_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = brp_mysqli_fetch_array($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['cust_id'];
	$cust_rel = brp_mysqli_fetch_array($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"  class="display">
	</table>
	<table  class="table table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="10" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="10">
	<strong>[ Item Wise Purchase Order Summary ] </strong>
	</td>
	</tr>';

	$query = "SELECT group_concat(tp.purchaseorder_id) as poids,tp.purchaseorder_id,tp.product_id,p.product_icode,p.product_name,p.product_desc FROM tbl_purchaseordertrn as tp left JOIN product_mst as p ON tp.product_id=p.product_id left JOIN tbl_purchaseorder as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id where tpt.status=0 ";

	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tpt.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		} else {
			$s_date = explode(' - ', $POST['rep_del_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " and tpt.purchaseorder_due_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_due_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		}
	}

	if ($POST['vendor_id'] > 0) {
		$query .= ' and tpt.vender_id=' . $POST['vendor_id'];
	}
	if ($POST['item_id'] > 0) {
		$query .= ' and tp.product_id=' . $POST['item_id'];
	}
	$query .= " group by tp.product_id";
	//echo $query;
	// exit;
	if ($POST['item_status_id'] == 2) {
		$item_st = 'and tpt.used_status = 1';
	} else if ($POST['item_status_id'] == 1) {
		$item_st = 'and tpt.used_status = 0';
	} else {
		$item_st = 'and tpt.used_status in (0,1)';
	}
	$result = $dbcon->query($query);
	$i = 1;

	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {
			$str .= '<tr style="margin-top:15px;">
			<td colspan="2">
			<strong>Item Code  : </strong>
			</td>
			<td colspan="5">
			' . $vendor_list['product_name'] . '<br>' . $vendor_list['product_icode'] . '
			</td>
			</tr>
			<!--<tr >
			<td colspan="2">
			<strong>Item Description  : </strong>
			</td>
			<td colspan="5">
			' . $vendor_list['product_desc'] . '
			</td>
			</tr>-->';
			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">No.</th>
			<th width="20%" style="text-align:center">PO NO <br>PO Date</th>
			<th width="20%" style="text-align:center">Vendor Name</th>';

			$str .= '<th width="10%" style="text-align:center">PO Qty</th>
			<th width="10%" style="text-align:center">Pending Qty</th>
			<th width="10%" style="text-align:center">Shortclose Qty</th>
			<th width="10%" style="text-align:center">Rate</th>
			<th width="10%" style="text-align:center">Discount </th>
			<th width="10%" style="text-align:center">Amount <br> Pending Amt </th>
			<th width="10%" style="text-align:center">Item Status</th>
			</tr>';
			$tot_po_qty = 0;
			$tot_po_cqty = 0;
			$tot_pen_qt = 0;
			$toe_pen_cqt = 0;
			$tot_short_qty = 0;
			$tot_pend_amt = 0;

			$query = "SELECT um.unit_name, umc.unit_name as conv_unit,tpt.product_amount,tpt.product_rate,tpt.product_disc,tpt.product_id,tpt.unit_id, tpt.conv_unit_id, tl.l_name as vendorname,tpt.used_status,tpt.rate_unit, tpt.product_discount, tp.purchaseorder_date,tp.purchaseorder_no,tpt.product_qty,tpt.product_conv_qty,tpt.short_close_qty,p.product_name,(select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending,(select sum(tbl_grn_trn.product_conv_qty) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id and tbl_grn_trn.grn_trn_status=0 ) as pending_conv,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=p.product_id )) as remaining
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id 
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.status=0  and tp.purchaseorder_id in (" . (($vendor_list['poids'])) . ")and tpt.product_id=" . $vendor_list['product_id'] . " " . $item_st;

			$result1 = $dbcon->query($query);
			$j = 1;
			$tot_amt = 0;
			$tot_pend_amt = 0;
			if (brp_mysqli_num_rows($result1) > 0) {
				while ($re = brp_mysqli_fetch_array($result1)) {
					$pen = $re["product_qty"] - $re["pending"];
					$penc = $re["product_conv_qty"] - $re["pending_conv"];

					if ($re['rate_unit'] == $re['unit_id']) {
						$pening_amt = ($re["product_rate"] * $pen) - $re['product_discount'];
					} else {
						$pening_amt = ($re["product_rate"] * $penc) - $re['product_discount'];
					}

					$tot_pen_qt = $tot_pen_qt + $pen;
					$toe_pen_cqt = $toe_pen_cqt + $penc;

					$tot_po_qty = $tot_po_qty + $re["product_qty"];
					$tot_po_cqty = $tot_po_cqty + $re["product_conv_qty"];

					$tot_short_qty = $tot_short_qty + $re['short_close_qty'];
					$purchaseorder_date = '';

					$tot_amt = $tot_amt + $re["product_amount"];

					//$item_status=($re["remaining"]>0) ? 'Pending' : 'Completed';
					if ($re['purchaseorder_date'] != '') {
						$purchaseorder_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
					}

					if ($re['unit_id'] != $re['conv_unit_id']) {
						$qty = '<span style="color:green"><strong>' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong></span><br>
						<span style="color:orange"><strong>' . $re['product_conv_qty'] . ' ' . $re['conv_unit'] . '</strong></span>';
						$pen_qty  = '<span style="color:green"><strong>' . $pen . ' ' . $re['unit_name'] . '</strong></span><br>
						<span style="color:orange"><strong>' . $penc . ' ' . $re['conv_unit'] . '</strong></span>';
					} else {
						$qty = '<span style="color:green"><strong>' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong></span>';
						$pen_qty = '<span style="color:green"><strong>' . $pen . ' ' . $re['unit_name'] . '</strong></span>';
					}

					if ($re['used_status'] == 1) {
						if (!empty($re['short_close_qty'])) {
							$short_qty = '<span style="color:green"><strong>' . $re['short_close_qty'] . ' ' . $re['unit_name'] . '</strong></span>';
							$pening_amt = '0.00';
						} else {
							$short_qty = '';
						}
						$item_status = 'Completed';
					} else {
						$short_qty = '';
						$item_status = 'Pending';
					}
					$tot_pend_amt = $tot_pend_amt + $pening_amt;

					$str .= '<tr style="border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $j . '</td>
					<td style="text-align:center">' . $re["purchaseorder_no"] . '<br>' . $purchaseorder_date . '</td>
					<td style="text-align:center">' . $re["vendorname"] . '</td>';

					$str .= '<td style="text-align:center">' . $qty . '</td>
					<td style="text-align:center">' . $pen_qty . '</td>
					<td style="text-align:center">' . $short_qty . '</td>
					<td style="text-align:center">' . number_format($re['product_rate'], 2) . '</td>
					<td style="text-align:center">' . number_format($re["product_discount"], 2) . '</td>
					<td style="text-align:center">' . number_format($re["product_amount"], 2) . ' <br>' . number_format($pening_amt, 2) . '</td>
					<td style="text-align:center">' . $item_status . '</td>

					</tr>';
					$j++;
				}
				$str .= '<tr style="border-top:0.5px solid black;">
				<td colspan="2"></td>
				<td >Total :</td>
				<td style="text-align:center"><span style="color:green"><strong>' . number_format($tot_po_qty, 2, ".", "") . '</strong></span> <br> <span style="color:orange"> <strong>' . number_format($tot_po_cqty, 2, ".", "") . '</strong></span></td>
				<td style="text-align:center"><span style="color:green"><strong>' . number_format($tot_pen_qt, 2, ".", "") . '</strong></span> <br> <span style="color:orange"> <strong>' . number_format($toe_pen_cqt, 2, ".", "") . '</strong></span></td>
				<td style="text-align:center"><span style="color:green"><strong>' . number_format($tot_short_qty, 2, ".", "") . '</strong></span></td>
				<td colspan="2" style="text-align:center"></td>

				<td style="text-align:center"><strong>' . number_format($tot_amt, 2) . ' <br>' . number_format($tot_pend_amt, 2) . '</strong></td>
				<td></td></tr>';
			} else {
				$str .= '<tr>
				<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
		}
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "purchaseorderstatusreport") {
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['vendor_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));

	$vendor = "select * from tbl_ledger where l_id=" . $POST['vendor_id'];
	$vendor_data = mysqli_fetch_assoc($dbcon->query($vendor));
	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="display table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="10">
	<strong>[ Pending Purchase Order ]</strong>
	</td>
	</tr>';

	$query = "SELECT tp.purchaseorder_id,tp.purchaseorder_no,tp.purchaseorder_date FROM tbl_purchaseorder as tp  where tp.vender_id=" . $POST['vendor_id'] . " and status=0 and tp.po_approval_status=1";

	if ($POST['pos_id'] != '') {
		$query .= " and tp.purchaseorder_id=" . $POST['pos_id'];
	}
	$query .= "  and tp.purchaseorder_date >= '" . date('Y-m-d', strtotime($s_date[0])) . "' AND tp.purchaseorder_date <= '" . date('Y-m-d', strtotime($s_date[1])) . "'";
	$result = $dbcon->query($query);

	if (isset($POST['po_status'])) {
		if ($POST['po_status'] == 1) {
			if ($POST['po_status_id'] == 2) {
				if (mysqli_num_rows($result) > 0) {

					while ($po_list = mysqli_fetch_assoc($result)) {
						$check = checkpopendorcomp($dbcon, $po_list['purchaseorder_id']);
						if ($check) {
							$allpos[] = $po_list['purchaseorder_id'];
						}
					}
				}
			}
			if ($POST['po_status_id'] == 1) {
				if (mysqli_num_rows($result) > 0) {

					while ($po_list = mysqli_fetch_assoc($result)) {
						$check = checkpopendorcomp($dbcon, $po_list['purchaseorder_id']);
						if ($check == 0) {
							$allpos[] = $po_list;
						}
					}
				}
			}
		}
	} else {
		if (mysqli_num_rows($result) > 0) {

			while ($po_list = mysqli_fetch_assoc($result)) {
				$allpos[] = $po_list;
			}
		}
	}

	if (count($allpos) > 0) {
		//while($po_list=mysqli_fetch_assoc($result))
		foreach ($allpos as $key => $po_list) {
			$st = checkpopendorcomp($dbcon, $po_list['purchaseorder_id']);
			if ($st == 0) {
				$status = 'Pending';
			} else {
				$status = 'Completed';
			}
			$str .= '<tr style="">
			<td colspan="3" style="border-right: hidden;border-bottom: hidden;">
			<strong>Vendor Name & Address :</strong>
			</td>
			<td colspan="2" style="border-right: hidden;border-bottom: hidden;">
			<strong></strong>
			</td>
			<td colspan="5" style="border-bottom: hidden;">
			<strong>PO NO :' . $po_list['purchaseorder_no'] . '</strong>
			</td>

			</tr><tr style="">
			<td colspan="3" style="border-right: hidden;border-bottom: hidden;">
			<strong>' . $vendor_data['l_name'] . '</strong>
			</td>
			<td colspan="2" style="border-right: hidden;border-bottom: hidden;">
			<strong></strong>
			</td>
			<td colspan="5" style="border-bottom: hidden;">
			<strong>PO Date :' . $po_list['purchaseorder_date'] . '</strong>
			</td>
			</tr>

			<tr style="">
			<td colspan="3" style="border-right: hidden;border-bottom: hidden;">
			<strong></strong>
			</td>
			<td colspan="2" style="border-right: hidden;border-bottom: hidden;">
			<strong></strong>
			</td>
			<td colspan="5" style="border-bottom: hidden;">
			<strong>PO Status : ' . $status . '</strong>
			</td>
			</tr>

			<tr style="">
			<td colspan="10">
			' . str_replace(",", "<br>", $vendor_data['m_address']) . '
			</td>
			</tr>';
			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="9%" style="text-align:center;padding:10px 5px;">NO</th>
			<th width="9%" style="text-align:center;">Indent No</th>
			<th width="29%" style="text-align:center">Description and Specification of Goods</th>';
			$str .= '<!--<th width="10%" style="text-align:center">UOM</th>-->
			<th width="10%" style="text-align:center">Quantity</th>
			<th width="10%" style="text-align:center">Pend. Qty </th>';

			$str .= '<th width="10%" style="text-align:center">Rate </th>
			<th width="10%" style="text-align:center" colspan="3">Amount</th>
			<th width="10%" style="text-align:center">Delivery Date</th>
			</tr>';

			$tot_qty = 0;
			$tot_pend_qty = 0;
			$query = "SELECT tpt.po_ref_id, tpt.purchaseordertrn_id,tpt.product_id,tpt.unit_id, tpt.conv_unit_id,um.unit_name,umc.unit_name as conv_unit,tpt.description,p.product_icode, tl.l_name as vendorname,tpt.product_rate, tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no, tpt.product_qty,tpt.product_conv_qty,p.product_name, (tpt.product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=p.product_id )) as remaining_b, (tpt.product_conv_qty - (select IFNULL(sum(tbl_grn_trn.product_conv_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=p.product_id )) as remaining_c 
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id";

			$query .= " where tp.purchaseorder_id=" . $po_list['purchaseorder_id'] . " and tpt.purchaseordertrn_status=0 and tp.po_approval_status=1";
			if (isset($POST['item_status'])) {
				if ($POST['item_status'] == 1) {
					if ($POST['item_status_id'] == 2) {
						$query .= ' having remaining_b <= 0';
					}

					if ($POST['item_status_id'] == 1) {
						$query .= ' having remaining_b > 0';
					}
				}
			}
			//echo $query."<br><br>";
			$result1 = $dbcon->query($query);
			$j = 1;
			if (mysqli_num_rows($result1) > 0) {
				while ($re = mysqli_fetch_assoc($result1)) {
					//$pen=$re["product_qty"]-$re["pending"];
					$tot = $re["product_qty"] * $re["product_rate"];
					$po_date = '';
					if ($re['purchaseorder_date'] != '') {
						$po_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
					}
					$po_due_date = '';
					if ($re['purchaseorder_due_date'] != '') {
						$po_due_date = date('d/m/Y', strtotime($re['purchaseorder_due_date']));
					}

					if ($re['unit_id'] != $re['conv_unit_id']) {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong> <br> <strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
						$pen = "<strong style='color:green'>" . $re['remaining_b'] . " " . $re['unit_name'] . "</strong> <br> <strong style='color:orange'>" . $re['remaining_c'] . " " . $re['conv_unit'] . "</strong>";
					} else {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
						$pen = "<strong style='color:green'>" . $re['remaining_b'] . " " . $re['unit_name'] . "</strong>";
					}

					$indent_no = get_indent_no_date($dbcon, $re['po_ref_id']);
					$delivery_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);

					$tot_pend_qty = $tot_pend_qty + $pen;
					$tot_qty = $tot_qty + $re["product_qty"];
					$str .= '<tr style="border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $j . '</td>
					<td style="text-align:center">' . $indent_no . '</td>
					<td style="text-align:center">' . $re["product_name"] . '</td>

					<!--<td style="text-align:center">' . $re["unit_name"] . '</td>-->
					<td style="text-align:center">' . $qty . '</td>
					<td style="text-align:center">' . $pen . '</td>
					<td style="text-align:center">' . number_format($re["product_rate"], 2) . '</td>
					<td style="text-align:center" colspan="3">' . number_format($tot, 2) . '</td>
					<td style="text-align:center">' . $delivery_date . '</td>
					</tr>';
					$j++;

					$str .= '<tr style="">
					<td style="text-align:center;border-right:hidden" ></td>
					<td colspan="2" style="text-align:center;border-right:hidden" >Inward Cum Inspection Details</td>
					<td style="text-align:center" colspan="7"></td></tr>';

					$str .= '<tr style="border: 1px dashed #cccccc;">
					<th width="9%" style="text-align:center;padding:10px 5px">NO</th>
					<th width="10%" style="text-align:center">GRN NO.</th>';
					$str .= '<th width="10%" style="text-align:center">GRN Date</th>
					<th width="10%" style="text-align:center">GRN Qty</th>
					<th width="10%" style="text-align:center">Accept. Qty </th>';

					$str .= '<th width="10%" style="text-align:center">Accept. Date  </th>
					<th width="10%" style="text-align:center">Rej Qty</th>
					<th width="10%" style="text-align:center" colspan="3">Challan NO</th>
					</tr>';

					$grnData = getgrndata($dbcon, $re["product_id"], $re["purchaseordertrn_id"]);
					if (mysqli_num_rows($grnData) > 0) {
						$k = 1;
						$tot_grn_qty = 0;
						$tot_insp_qty = 0;
						$tot_rej_qty = 0;
						while ($re_grn_data = brp_mysqli_fetch_array($grnData)) {
							$tot_grn_qty = $tot_grn_qty + $re_grn_data["product_qty"];

							$tot_insp_qty = $tot_insp_qty + $re_grn_data["qc_accepted"];
							$tot_rej_qty = $tot_rej_qty + $re_grn_data["qc_rejected"];

							if ($re_grn_data['unit_id'] != $re_grn_data['product_conv_unit']) {
								$tot_grn_cqty = $tot_grn_cqty + $re_grn_data["product_conv_qty"];
								$qty = "<strong style='color:green'>" . $re_grn_data['product_qty'] . " " . $re_grn_data['unit_name'] . "</strong><br><strong style='color:orange'>" . $re_grn_data['product_conv_qty'] . " " . $re_grn_data['conv_unit'] . "</strong>";
							} else {
								$qty = "<strong style='color:green'>" . $re_grn_data['product_qty'] . " " . $re_grn_data['unit_name'] . "</strong>";
							}

							if ($re_grn_data["qc_date"] != '' && $re_grn_data["qc_date"] != '1970-01-01' && $re_grn_data["qc_date"] != '0000-00-00') {
								$qc_date  = date('d-m-Y', strtotime($re_grn_data["qc_date"]));
							} else {
								$qc_date = '-';
							}

							$str .= '<tr style="border: 1px dashed #cccccc;">
							<td style="text-align:center">' . $k . '</td>
							<td style="text-align:center">' . $re_grn_data["grn_no"] . '</td>
							<td style="text-align:center">' . $re_grn_data["grn_date"] . '</td>
							<td style="text-align:center">' . $qty . '</td>
							<td style="text-align:center"><strong style="color:green">' . $re_grn_data["qc_accepted"] . ' ' . $re_grn_data["qc_unit"] . '</strong></td>
							<td style="text-align:center">' . $qc_date . '</td>
							<td style="text-align:center"><strong style="color:red">' . $re_grn_data["qc_rejected"] . ' ' . $re_grn_data['qc_unit'] . '</strong></td>
							<td style="text-align:center" colspan="3">' . $re_grn_data["challan_no"] . '</td>
							</tr>';
							$k++;
						}
						$str .= '<tr style="border: 1px dashed #cccccc;">
						<td style="text-align:center" colspan="2"></td>
						<td style="text-align:center">Total</td>
						<td style="text-align:center"><strong style="color:green">' . $tot_grn_qty . '</strong> <br><strong style="color:orange">' . $tot_grn_cqty . '</strong></td>
						<td style="text-align:center"><strong style="color:green">' . $tot_insp_qty . '</strong></td>
						<td style="text-align:center"></td>
						<td style="text-align:center"><strong style="color:red">' . $tot_rej_qty . '</strong></td>
						<td style="text-align:center" colspan="3"></td>
						
						</tr>';
					} else {
						$str .= '<tr>
						<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
					}
				}
			} else {
				$str .= '<tr>
				<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
		}
	} else {
		$str .= '<tr>
		<td colspan="10" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}

	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "itemwisesummaryreport") {
	$tot_last_po_qty = 0;
	$tot_last_req_qty = 0;
	$tot_last_pen_qty = 0;
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['cust_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);
	$comany_cnfg = getCompanyConfiguration($dbcon);

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['cust_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%" class="display">
	</table>
	<table  class="display table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="8">
	<strong>Item Wise Pending Purchase Order List </strong>
	</td>
	</tr>';

	$query = "SELECT group_concat(tp.purchaseorder_id) as poids,tp.purchaseorder_id,tp.product_id,p.product_icode,p.product_name,p.product_desc,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tp.purchaseordertrn_id and tbl_grn_trn.product_id=tp.product_id )) as remaining FROM tbl_purchaseordertrn as tp left JOIN product_mst as p ON tp.product_id=p.product_id left JOIN tbl_purchaseorder as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id where tpt.status=0 and tpt.po_approval_status=1";

	$s_date = explode(' - ', $POST['rep_po_date']);
	$startdate = $s_date[0];
	$enddate = $s_date[1];

	$query .= " and tp.purchaseordertrn_status=0 and tpt.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";

	// if($POST['po_date_type']){
	// 	if($POST['po_date_type']=='po'){
	// 		$s_date=explode(' - ',$POST['rep_po_date']);
	// 		$startdate=$s_date[0];
	// 		$enddate=$s_date[1];

	// 	}else{
	// 		$s_date=explode(' - ',$POST['rep_del_date']);
	// 		$startdate=$s_date[0];
	// 		$enddate=$s_date[1];
	// 		$query.=" and tpt.purchaseorder_due_date>='".date('Y-m-d',strtotime($startdate))."' and tpt.purchaseorder_due_date<='".date('Y-m-d',strtotime($enddate))."' ";
	// 	}

	// }
	if ($POST['vendor_id']) {
		$query .= ' and tpt.vender_id=' . $POST['vendor_id'];
	}
	// if(isset($POST['specific_vendor'])){
	// 	if($POST['vendor_id']){
	// 		$query.=' and tpt.vender_id='.$POST['vendor_id'];
	// 	}
	// }
	if ($POST['item_id']) {
		$query .= ' and tp.product_id=' . $POST['item_id'];
	}
	// if(isset($POST['specific_item'])){
	// 	if($POST['item_id']){
	// 		$query.=' and tp.product_id='.$POST['item_id'];
	// 	}
	// }
	$query .= ' group by tp.product_id';
	if ($POST['item_status_id'] == 2) {
		$query .= ' having remaining <= 0';
	}

	if ($POST['item_status_id'] == 1) {
		$query .= ' having remaining > 0';
	}
	//echo $query;
	// if(isset($POST['item_status'])){
	// 	if($POST['item_status']==1){
	// 		if($POST['item_status_id']==2){
	// 			$query.=' having remaining <= 0';
	// 		}

	// 		if($POST['item_status_id']==1){
	// 			$query.=' having remaining > 0';
	// 		}
	// 	}
	// }
	//echo $query;
	$result = $dbcon->query($query);
	$i = 1;

	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {

			$str .= '<tr style="margin-top:15px;">
			<td colspan="2">
			<strong>Item Name  : </strong>
			</td>
			<td colspan="6">
			' . $vendor_list['product_name'] . '
			</td>
			</tr>';


			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="9%" style="text-align:center"> No</th>
			<th width="9%" style="text-align:center">PO No <br> PO Date</th>';

			if ($comany_cnfg['po_work_order_wise'] == 1) {
				$str .= '<th width="9%" style="text-align:center">WO No <br> WO Date</th>';
			}


			$str .= '<th width="20%" style="text-align:center">Party Name</th>';

			/*if($POST['withconv']==1){
				$str.='
				<th width="10%" style="text-align:center">P.O.Qty </th>
				<th width="23%" style="text-align:center">Rcv Qty </th>
				<th width="23%" style="text-align:center;color:red">Pen Qty </th></tr>';
			}else{*/
			$str .= '
				<th width="12%" style="text-align:center">P.O.Qty</th>
				<th width="12%" style="text-align:center">Rcv. Qty </th>
				<th width="12%" style="text-align:center;color:red;">Pending Qty</th>
				<th width="20%" style="text-align:center">Del Date</th></tr>';
			//}

			$tot_qty = 0;
			$tot_cqty = 0;
			$tot_pend_qty = 0;
			$tot_pend_cqty = 0;
			$tot_rec_qty = 0;
			$tot_rec_cqty = 0;
			$query = "SELECT um.unit_name, umc.unit_name as conv_unit, tsmp.po_req_no as workorderno,tsmp.po_req_date as workorderdate,tpt.product_id,tpt.unit_id,tpt.conv_unit_id, tpt.purchaseordertrn_id, tl.l_name as vendorname, trp.indent_no,trp.indent_date,tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no,trp.indent_date,tpt.product_qty,tpt.product_conv_qty,p.product_name,(select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending,(select IFNULL(sum(tbl_grn_trn.product_conv_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending_conv, (product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trpc ON tpt.po_ref_id=trpc.rp_id
			left JOIN tbl_set_main_process as tsmp ON tsmp.sp_id=trpc.sp_id
			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id 
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.po_approval_status=1 and tp.status=0  and tpt.purchaseordertrn_status=0 and tp.purchaseorder_id in (" . (($vendor_list['poids'])) . ")and tpt.product_id=" . $vendor_list['product_id'];

			if (isset($POST['item_status'])) {
				if ($POST['item_status'] == 1) {
					if ($POST['item_status_id'] == 2) {
						$query .= ' having remaining <= 0';
					}

					if ($POST['item_status_id'] == 1) {
						$query .= ' having remaining > 0';
					}
				}
			}
			//echo $query.'<br><br>';
			$result1 = $dbcon->query($query);
			$j = 1;
			if (mysqli_num_rows($result1) > 0) {

				while ($re = mysqli_fetch_assoc($result1)) {
					$pen = $re["product_qty"] - $re["pending"];
					$pen_conv = $re['product_conv_qty'] - $re['pending_conv'];
					$tot_pend_qty = $tot_pend_qty + $pen;
					$tot_qty = $tot_qty + $re["product_qty"];
					$tot_last_po_qty = $tot_last_po_qty + $re["product_qty"];
					$tot_rec_qty = $tot_rec_qty + $re["pending"];
					$tot_last_req_qty = $tot_last_req_qty + $re["pending"];
					$tot_last_pen_qty = $tot_last_pen_qty + $pen;
					$workorderdate = '';
					if ($re['workorderdate'] != '') {
						$workorderdate = date('d/m/Y', strtotime($re['workorderdate']));
					}
					//$re["pending"]=($re["pending"]=='') ? 0 :$re["pending"];
					$purchaseorder_date = '';
					if ($re['purchaseorder_date'] != '') {
						$purchaseorder_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
					}

					if ($re['unit_id'] != $re['conv_unit_id']) {
						$tot_cqty = $tot_cqty + $re["product_conv_qty"];
						$tot_rec_cqty = $tot_rec_cqty + $re['pending_conv'];
						$tot_pend_cqty = $tot_pend_cqty + $pen_conv;

						$tot_last_po_cqty = $tot_last_po_cqty + $re["product_conv_qty"];
						$tot_last_req_cqty = $tot_last_req_cqty + $re["pending_conv"];
						$tot_last_pen_cqty = $tot_last_pen_cqty + $pen_conv;

						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";

						$rcv = "<strong style='color:green'>" . $re['pending'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['pending_conv'] . " " . $re['conv_unit'] . "</strong>";

						$pen = "<strong>" . $pen . " " . $re['unit_name'] . "</strong><br><strong>" . $pen_conv . " " . $re['conv_unit'] . "</strong>";
					} else {
						$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";

						$rcv = "<strong style='color:green'>" . $re['pending'] . " " . $re['unit_name'] . "</strong>";

						$pen = "<strong>" . $pen . " " . $re['unit_name'] . "</strong>";
					}

					$re["purchaseorder_due_date"] = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);

					$str .= '<tr style="  border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $j . '</td>
					<td style="text-align:center">' . $re["purchaseorder_no"] . '<br>' . $purchaseorder_date . '</td>';
					if ($comany_cnfg['po_work_order_wise'] == 1) {
						$str .= '<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td>';
					}
					$str .= '<td style="text-align:center">' . $re["vendorname"] . '</td>';
					$j++;
					/*if($POST['withconv']==1){
						$str.='<td style="text-align:center">'.$re["product_qty"].'</td>
						<td style="text-align:center">'.$re["pending"].'</td>
						<td style="text-align:center;color:red;">'.$pen.'</td>
						</tr>';
					}else{*/
					$str .= '<td style="text-align:center">' . $qty . '</td>
						<td style="text-align:center">' . $rcv . '</td>
						<td style="text-align:center;color:red;">' . $pen . '</td>
						<td style="text-align:center">' . $re["purchaseorder_due_date"] . '</td>
						</tr>';
					//}
				}
				if ($comany_cnfg['po_work_order_wise'] == 1) {
					$colspan = 3;
				} else {
					$colspan = 3 - 1;
				}
				$str .= '<tr style="border-top:0.5px #000 solid;">

				<td colspan="' . $colspan . '"></td>
				<td style="text-align:center">Total :</td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_cqty, 2) . '</strong></td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_rec_qty, 2) . '</style><br><strong style="color:orange">' . number_format($tot_rec_cqty, 2) . '</strong></td>
				<td style="text-align:center;color:red;"><strong>' . number_format($tot_pend_qty, 2) . '</strong><br><strong>' . number_format($tot_pend_cqty, 2) . '</strong></td>
				<td style="text-align:center"></td>
				</tr>';
			} else {
				$str .= '<tr>
				<td colspan="8" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
		}
		$str .= '<tr >
		<td colspan="' . $colspan . '"></td>
		<td style="border-top:0.5px #000 solid;text-align:center;">Grand Total :</td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_po_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_last_po_cqty, 2) . '</strong></td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_req_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_last_req_cqty, 2) . '</strong></td>
		<td style="text-align:center;color:red;border-top:0.5px #000 solid;"><strong>' . number_format($tot_last_pen_qty, 2) . '</strong><br><strong>' . number_format($tot_last_pen_cqty, 2) . '</strong></td>
		<td style="text-align:center"></td>
		</tr>';
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "itemwiseratesummaryreport") {
	$tot_last_po_qty = 0;
	$tot_last_req_qty = 0;
	$tot_last_pen_qty = 0;
	$tot_last_amount = 0;
	$tot_last_pen_amount = 0;

	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];

	$comany_cnfg = getCompanyConfiguration($dbcon);

	$pr_row = get_product_detail($dbcon, $product_id);

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = brp_mysqli_fetch_array($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['vendor_id'];
	$cust_rel = brp_mysqli_fetch_assoc($dbcon->query($qrycust));
	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="display table-bordered" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="12">
	<strong>Item Wise Pending Purchase Order List </strong>
	</td>
	</tr>';

	$query = "SELECT group_concat(tp.purchaseorder_id) as poids,tp.purchaseorder_id,tp.product_id,p.product_icode,p.product_name,p.product_desc,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tp.purchaseordertrn_id and tbl_grn_trn.product_id=tp.product_id )) as remaining FROM tbl_purchaseordertrn as tp left JOIN product_mst as p ON tp.product_id=p.product_id left JOIN tbl_purchaseorder as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id ";

	if ($POST['po_date_type']) {
		if ($POST['po_date_type'] == 'po') {
			$s_date = explode(' - ', $POST['rep_po_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tpt.po_approval_status=1 and tp.purchaseordertrn_status=0 and tpt.purchaseorder_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_date<='" . date('Y-m-d', strtotime($enddate)) . "'";
		} else {
			$s_date = explode(' - ', $POST['rep_del_date']);
			$startdate = $s_date[0];
			$enddate = $s_date[1];
			$query .= " where tpt.po_approval_status=1 and tpt.purchaseorder_due_date>='" . date('Y-m-d', strtotime($startdate)) . "' and tpt.purchaseorder_due_date<='" . date('Y-m-d', strtotime($enddate)) . "' ";
		}
	}
	if (isset($POST['specific_vendor'])) {
		if ($POST['vendor_id']) {
			$query .= ' and tpt.vender_id=' . $POST['vendor_id'];
		}
	}

	if (isset($POST['specific_item'])) {
		if ($POST['item_id']) {
			$query .= ' and tp.product_id=' . $POST['item_id'];
		}
	}
	$query .= ' group by tp.product_id';
	if (isset($POST['item_status'])) {
		if ($POST['item_status'] == 1) {
			if ($POST['item_status_id'] == 2) {
				$query .= ' having remaining <= 0';
			}

			if ($POST['item_status_id'] == 1) {
				$query .= ' having remaining > 0';
			}
		}
	}
	//echo $query;
	$result = $dbcon->query($query);
	$i = 1;

	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {

			$str .= '<tr style="margin-top:15px;">
			<td colspan="2">
			<strong>Item Name 111 : </strong>
			</td>
			<td colspan="10">
			' . $vendor_list['product_name'] . '
			</td>
			</tr>';


			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="9%" style="text-align:center"> No</th>
			<th width="9%" style="text-align:center">PO No <br> PO Date</th>';

			if ($comany_cnfg['po_work_order_wise'] == 1) {
				$str .= '<th width="9%" style="text-align:center">WO No <br> WO Date</th>';
			}


			$str .= '
			
			<th width="20%" style="text-align:center">Party Name</th>';

			if ($POST['withconv'] == 1) {
				$str .= '
				<th width="10%" style="text-align:center">Item Description </th>';
			}
			if ($POST['withconv'] == 1) {
				if ($comany_cnfg['po_work_order_wise'] == 1) {
					$colspan = 4;
				} else {
					$colspan = 4 - 1;
				}
			} else {
				if ($comany_cnfg['po_work_order_wise'] == 1) {
					$colspan = 3;
				} else {
					$colspan = 3 - 1;
				}
			}
			$str .= '
			<th width="12%" style="text-align:center">P.O.Qty</th>
			<th width="10%" style="text-align:center">Rcv Qty </th>
			<th width="10%" style="text-align:center;color:red;">Pending Qty</th>';
			$str .= '
			<th width="12%" style="text-align:center">Rate</th>
			<th width="12%" style="text-align:center">Disc </th>
			<th width="12%" style="text-align:center">Amount </th>
			<th width="12%" style="text-align:center;">Pending Amount</th>
			<th width="12%" style="text-align:center">Del Date</th>
			</tr>';

			$tot_qty = 0;
			$tot_cqty = 0;
			$tot_pend_qty = 0;
			$tot_pend_cqty = 0;
			$tot_rec_qty = 0;
			$tot_rec_cqty = 0;
			$tot_amnt = 0;
			$tot_pending_amnt = 0;
			$query = "SELECT um.unit_name,umc.unit_name as conv_unit,tpt.purchaseordertrn_id,tsmp.po_req_no as workorderno,tsmp.po_req_date as workorderdate,tpt.product_rate,tpt.product_disc,tpt.product_id,tpt.unit_id,tpt.conv_unit_id, tl.l_name as vendorname, trp.indent_no,trp.indent_date,tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no,trp.indent_date,tpt.product_qty,tpt.product_conv_qty,p.product_name, (select IFNULL(sum(tbl_grn_trn.product_qty),0)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id and tbl_grn_trn.grn_trn_status=0 ) as pending, (select IFNULL(sum(tbl_grn_trn.product_conv_qty),0)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id and tbl_grn_trn.grn_trn_status=0 ) as pending_cqty,(product_qty - (select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remaining
			FROM tbl_purchaseorder as tp
			left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
			left JOIN tbl_request_product as trpc ON tpt.po_ref_id=trpc.rp_id
			left JOIN tbl_set_main_process as tsmp ON tsmp.sp_id=trpc.sp_id
			left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
			left JOIN unit_mst as um ON um.unitid=tpt.unit_id
			left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
			left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.purchaseorder_id in (" . (($vendor_list['poids'])) . ")and tpt.product_id=" . $vendor_list['product_id'];

			if (isset($POST['item_status'])) {
				if ($POST['item_status'] == 1) {
					if ($POST['item_status_id'] == 2) {
						$query .= ' having remaining <= 0';
					}
					if ($POST['item_status_id'] == 1) {
						$query .= ' having remaining > 0';
					}
				}
			}
			//echo $query;
			$result1 = $dbcon->query($query);
			$j = 1;
			if (mysqli_num_rows($result1) > 0) {

				while ($re = mysqli_fetch_assoc($result1)) {
					$pen = $re["product_qty"] - $re["pending"];
					$pen_conv = $re["product_conv_qty"] - $re["pending_cqty"];
					$tot_po_qty = $tot_po_qty + $re["product_qty"];
					$tot_last_po_qty = $tot_last_po_qty + $re["product_qty"];
					$tot_req_qty = $tot_req_qty + $re["pending"];
					$tot_last_req_qty = $tot_last_req_qty + $re["pending"];
					$tot_pen_qty = $tot_pen_qty + $pen;
					$tot_last_pen_qty = $tot_last_pen_qty + $pen;
					$tot_pend_qty = $tot_pend_qty + $pen;
					$tot_qty = $tot_qty + $re["product_qty"];
					$tot_rec_qty = $tot_rec_qty + $re["pending"];
					$tot_amnt = $tot_amnt + ($re["product_qty"] * $re["product_rate"]);
					$tot_pending_amnt = $tot_pending_amnt + ($pen * $re["product_rate"]);
					$workorderdate = '';
					if ($re['workorderdate'] != '') {
						$workorderdate = date('d/m/Y', strtotime($re['workorderdate']));
					}
					$re["pending"] = ($re["pending"] == '') ? 0 : $re["pending"];
					$purchaseorder_date = '';
					if ($re['purchaseorder_date'] != '') {
						$purchaseorder_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
					}

					$re["purchaseorder_due_date"] = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);

					if ($re['unit_id'] != $re['conv_unit_id']) {
						$tot_cqty = $tot_cqty + $re['product_conv_qty'];
						$tot_rec_cqty = $tot_rec_cqty + $re['pending_cqty'];
						$tot_pend_cqty = $tot_pend_cqty + $pen_conv;

						$tot_last_po_cqty = $tot_last_po_cqty + $re['product_conv_qty'];
						$tot_last_req_cqty = $tot_last_req_cqty + $re['pending_cqty'];
						$tot_last_pen_cqty = $tot_last_pen_cqty + $pen_conv;


						$qty = '<strong style="color:green">' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong><br><strong style="color:orange">' . $re['product_conv_qty'] . ' ' . $re['conv_unit'] . '</strong>';
						$rcv = '<strong style="color:green">' . $re['pending'] . ' ' . $re['unit_name'] . '</strong><br><strong style="color:orange">' . $re['pending_cqty'] . ' ' . $re['conv_unit'] . '</strong>';

						$pen1 = '<strong>' . $pen . ' ' . $re['unit_name'] . '</strong><br><strong>' . $pen_conv . ' ' . $re['conv_unit'] . '</strong>';
					} else {
						$qty = '<strong style="color:green">' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong>';

						$rcv = '<strong style="color:green">' . $re['pending'] . ' ' . $re['unit_name'] . '</strong>';

						$pen1 = '<strong>' . $re['product_qty'] . ' ' . $re['unit_name'] . '</strong>';
					}

					$str .= '<tr style="  border: 1px dashed #cccccc;">
					<td style="text-align:center">' . $j . '</td>
					<td style="text-align:center">' . $re["purchaseorder_no"] . '<br>' . $purchaseorder_date . '</td>';
					if ($comany_cnfg['po_work_order_wise'] == 1) {
						$str .= '<td style="text-align:center">' . $re["workorderno"] . $workorderdate . '</td>';
					}

					$str .= '
					<td style="text-align:center">' . $re["vendorname"] . '</td>';
					$j++;
					if ($POST['withconv'] == 1) {
						$str .= '<td style="text-align:center">' . $re["product_name"] . '<br>' . $re["product_desc"] . '</td>';
					}
					$str .= '<td style="text-align:center">' . $qty . '</td>
					<td style="text-align:center">' . $rcv . '</td>
					<td style="text-align:center;color:red;">' . $pen1 . '</td>
					';
					$str .= '<td style="text-align:center">' . $re["product_rate"] . '</td>
					<td style="text-align:center">' . $re["product_disc"] . '</td>
					<td style="text-align:center;">' . $re["product_qty"] * $re["product_rate"] . '</td>
					<td style="text-align:center;">' . $pen * $re["product_rate"] . '</td>
					<td style="text-align:center">' . $re["purchaseorder_due_date"] . '</td>
					</tr>';
				}
				$str .= '<tr style="border-top:0.5px #000 solid;">

				<td colspan=' . $colspan . '></td>
				<td style="text-align:center">Total :</td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_cqty, 2) . '</strong></td>
				<td style="text-align:center"><strong style="color:green">' . number_format($tot_rec_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_rec_cqty, 2) . '</strong></td>
				<td style="text-align:center;color:red;"><strong>' . number_format($tot_pend_qty, 2) . '</strong><br><strong>' . number_format($tot_pend_cqty, 2) . '</strong></td>
				<td colspan="2"></td>
				<td style="text-align:center;">' . number_format($tot_amnt, 2) . '</td>
				<td style="text-align:center;">' . number_format($tot_pending_amnt, 2) . '</td>
				<td style="text-align:center"></td>
				</tr>';
				$tot_last_pen_amount = $tot_last_pen_amount + $tot_pending_amnt;
				$tot_last_amount = $tot_last_amount + $tot_amnt;
			} else {
				$str .= '<tr>
				<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
		}
		$str .= '<tr >
		<td colspan=' . $colspan . '></td>
		<td style="border-top:0.5px #000 solid;text-align:center;">Grand Total :</td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_po_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_last_po_cqty, 2) . '</strong></td>
		<td style="text-align:center;border-top:0.5px #000 solid;"><strong style="color:green">' . number_format($tot_last_req_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_last_req_cqty, 2) . '</style></td>
		<td style="text-align:center;color:red;border-top:0.5px #000 solid;"><strong>' . number_format($tot_last_pen_qty, 2) . '</strong><br><strong>' . number_format($tot_last_pen_cqty, 2) . '</strong></td>
		<td colspan="2" style="border-top:0.5px #000 solid;text-align:center;"></td>
		<td style="text-align:center;border-top:0.5px #000 solid;">' . number_format($tot_last_amount, 2) . '</td>
		<td style="text-align:center;color:red;border-top:0.5px #000 solid;">' . number_format($tot_last_pen_amount, 2) . '</td>

		<td style="text-align:center"></td>
		</tr>';
	} else {
		$str .= '<tr>
		<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '				 
	</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "getpendingpo") {
	$pohtml = getAllPO($dbcon, $POST['vendorid']);
	echo $pohtml;
} else if (strtolower($POST['mode']) == "getpo") {
	$pohtml = getAllPOs($dbcon, $POST['vendorid']);
	echo $pohtml;
} else if (strtolower($POST['mode']) == "getitemsbyvendorid") {
	if ($POST['vendorid']) {
		$pohtml = getAllitemsbyvendorid($dbcon, $POST['vendorid']);
	} else {
		$pohtml = getAllitemsbyvendorid($dbcon, '');
	}
	echo $pohtml;
} else if (strtolower($POST['mode']) == "getitemsbyvendoridjobcard") {
	$pohtml = getAllitemsbyvendoridjobcard($dbcon, $POST['vendorid']);
	echo $pohtml;
} else if (strtolower($POST['mode']) == "getpendingitems") {
	$pohtml = getAllPendingitems($dbcon, $POST['purchaseorderid']);
	echo $pohtml;
} else if (strtolower($POST['mode']) == "vendorwisepricelistreport") {
	$tot_last_po_qty = 0;
	$tot_last_req_qty = 0;
	$tot_last_pen_qty = 0;
	$tot_last_amount = 0;

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));

	$str .= '
	<table  width="100%"   class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>' . $set_head['company_name'] . '</strong>
	</td>
	</tr>

	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[Price List]</strong>
	</td>
	</tr>';
	$query = "SELECT group_concat(tp.product_id) as productids,tp.purchasecard_id,tl.m_address,tl.l_name
	FROM tbl_purchasecardtrn as tp
	left JOIN tbl_ledger as tl ON tl.l_id=tp.vendor_id
	where  tp.purchase_type=0";
	if (isset($POST['specific_vendor'])) {
		if ($POST['vendor_id']) {
			$query .= ' and tp.vendor_id=' . $POST['vendor_id'];
		}
	}

	if (isset($POST['specific_item'])) {
		if ($POST['item_id']) {
			$query .= ' and tp.product_id=' . $POST['item_id'];
		}
	}

	$query .= ' group by tp.vendor_id';
	$result = $dbcon->query($query);
	$i = 1;

	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($vendor_list = mysqli_fetch_assoc($result)) {

			$str .= '<tr style="margin-top:15px;">
			<td colspan="2">
			<strong>Vendor  : </strong>
			</td>
			<td colspan="5">
			' . $vendor_list['l_name'] . '
			</td>
			</tr>
			<tr style="">
			<td colspan="7">
			' . nl2br($vendor_data['m_address']) . '
			</td>
			</tr>';

			$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">Item Code</th>
			<th width="15%" style="text-align:center">Item Details</th>
			<th width="10%" style="text-align:center">UOM</th>';

			$str .= '
			<th width="12%" style="text-align:center">Rate</th>
			<th width="12%" style="text-align:center">Disc (%)</th>
			<th width="12%" style="text-align:center">GRate</th>';

			$query = "SELECT tp.purchasecard_id,tp.price,tp.discount_percentage,tp.grate,p.product_name,p.product_icode,um.unit_name 
			FROM product_mst as p
			left JOIN unit_mst as um ON um.unitid=p.product_base_unit
			left JOIN tbl_purchasecardtrn as tp ON tp.product_id=p.product_id
			left JOIN tbl_ledger as tl ON tl.l_id=tp.vendor_id
			where p.product_id in (" . $vendor_list['productids'] . ") and tp.purchase_type=0";

			$result1 = $dbcon->query($query);

			if (mysqli_num_rows($result1) > 0) {
				$k = 1;
				while ($re = mysqli_fetch_assoc($result1)) {
					$str .= '<tr style=" border: 1px dashed #cccccc;">
						<td style="text-align:center">' . $k . '</td>
						<td style="text-align:center">' . $re["product_name"] . ' -- ' . $re["product_icode"] . '</td>';

					$str .= '
						<td style="text-align:center">' . $re["unit_name"] . '</td>
						<td style="text-align:center">' . $re["price"] . '</td>
						<td style="text-align:center">' . $re["discount_percentage"] . '</td>
						<td style="text-align:center">' . $re["grate"] . '</td>

						</tr>';
					$k++;
				}
			} else {
				$str .= '<tr>
					<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
					</tr>';
			}
		}
	} else {
		$str .= '<tr>
			<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			</tr>';
	}
	$str .= '				 
		</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "followupreport") {
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];
	$pr_row = get_product_detail($dbcon, $product_id);
	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	$qrycust = "select * from tbl_customer where cust_id=" . $POST['vendor_id'];
	$cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));

	$vendor = "select * from tbl_ledger where l_id=" . $POST['vendor_id'];
	$vendor_data = mysqli_fetch_assoc($dbcon->query($vendor));
	$str .= '
		<table  width="100%"   class="display">
		</table>
		<table  class="display table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>' . $set_head['company_name'] . '</strong>
		</td>
		</tr>

		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7" style="border-bottom:hidden">
		<strong>[ Pending Purchase Orders ]</strong>
		</td>
		</tr>';
	$tot_qty = 0;
	$tot_pend_qty = 0;
	$query = "SELECT um.unit_name, cum.unit_name as conv_unit, tpt.product_id,tpt.unit_id,tpt.conv_unit_id,tpt.description,p.product_icode, tl.l_name as vendorname, trp.indent_no,trp.indent_date,tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no,trp.indent_date,tpt.product_qty,tpt.product_conv_qty,p.product_name,tpt.purchaseordertrn_id, (select IFNULL(sum(tbl_grn_trn.product_qty),0)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending, (select IFNULL(sum(tbl_grn_trn.product_conv_qty),0)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as pending_conv,(tpt.product_qty-(select IFNULL(sum(tbl_grn_trn.product_qty),0) from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id )) as remainingqty
		FROM tbl_purchaseorder as tp
		left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
		left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
		left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
		left JOIN unit_mst as um ON um.unitid=tpt.unit_id
		left JOIN unit_mst as cum on cum.unitid = tpt.conv_unit_id
		left JOIN product_mst as p ON p.product_id=tpt.product_id where tp.status=0 and tp.po_approval_status=1 and tp.revise_status=0";
	if ($POST['vendor_id'] != '') {
		$query .= " and tp.vender_id=" . $POST['vendor_id'];
	}

	if ($POST['pos_id'] != '') {
		$query .= " and tp.purchaseorder_id=" . $POST['pos_id'];
	}

	if ($POST['items_id'] != '') {
		$query .= " and tpt.product_id=" . $POST['items_id'];
	}

	if ($POST['fromdate'] != '') {
		$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($POST['fromdate'])) . "'";
	}

	if ($POST['todate'] != '') {
		$query .= " and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($POST['todate'])) . "'";
	}
	//$query.=" having remainingqty>0";
	//echo $query;
	$result1 = $dbcon->query($query);
	$j = 1;
	if (brp_mysqli_num_rows($result1) > 0) {

		$str .= '<tr style="">
		<td colspan="3" style="border-bottom:hidden;border-right:hidden">
		<strong>To, <br> ' . $vendor_data['l_name'] . '</strong>
		</td>
		<td colspan="4" style="border-bottom:hidden;">
		<strong></strong>
		</td>
		</tr>
		<tr style="">
		<td colspan="7" style="border-bottom:hidden;">
		' . nl2br($vendor_data['m_address']) . '
		</td>
		</tr>

		<tr style="">
		<td colspan="7" style="border-bottom:hidden;">
		Mobile : ' . $vendor_data['cust_mobile'] . ' <br>
		Email ID: ' . $vendor_data['cust_email'] . '
		</td>
		</tr>

		<tr style="">
		<td colspan="7" style="border-bottom:hidden;">
		Date : ' . date("d-m-Y") . '
		</td>
		</tr>

		<tr style="">
		<td colspan="7" style="border-bottom:hidden;">
		Dear Sir/Madam,
		</td>
		</tr>

		<tr style="">
		<td colspan="7" style="border-bottom:hidden;">
		Following Purchase order(s) are pending with you as on today. You are requested to dispatch the material on top priority basis.
		</td>
		</tr>
		<tr style="">
		<td colspan="7">
		Please note that in future, Your supply of the material should reach us before deliver date.
		</td>
		</tr>
		';

		$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
		<th width="10%" style="text-align:center;padding:10px 5px">PO NO </th>
		<th width="10%" style="text-align:center">PO Date</th>
		<th width="30%" style="text-align:center">Item Name</th>';
		//if($POST['withconv']==1){
		$str .= '<th width="10%" style="text-align:center">Po Qty</th>
		<th width="10%" style="text-align:center">Pen Qty</th>
		<th width="20%" style="text-align:center">Delivery Date</th></tr>';
		/*}else{
			$str.='<th width="12%" style="text-align:center">UOM</th>
			<th width="12%" style="text-align:center">P.O.Qty</th>
			<th width="23%" style="text-align:center">Pending Qty</th></tr>';
		}*/

		while ($re = brp_mysqli_fetch_array($result1)) {
			//$unit=get_alter_unit($dbcon,$re["product_id"],$re["unit_id"]);
			$pen1 = $re["product_qty"] - $re["pending"];
			$pen_conv1 = $re['product_conv_qty'] - $re['pending_conv'];
			//$convert=convert_stock($dbcon,$re["product_qty"],$re["product_id"],$unit);
			//$pend_conv=convert_stock($dbcon,$pen,$re["product_id"],$unit);

			if ($re['unit_id'] != $re['conv_unit_id']) {
				$tot_cqty = $tot_cqty + $re["product_conv_qty"];
				$tot_pend_cqty = $tot_pend_cqty + $pen_conv1;
				$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
				$pen = "<strong style='color:green'>" . $pen1 . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $pen_conv1 . " " . $re['conv_unit'] . "</strong>";
			} else {
				$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
				$pen = "<strong style='color:green'>" . $pen1 . " " . $re['unit_name'] . "</strong>";
			}


			$po_date = '';
			if ($re['purchaseorder_date'] != '') {
				$po_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
			}

			$po_due_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);
			/*if($re['purchaseorder_due_date']!=''){
					$po_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
				}*/
			if ($pen1 > 0) {
				$tot_pend_qty = $tot_pend_qty + $pen1;
				$tot_qty = $tot_qty + $re["product_qty"];
				$str .= '<tr style="border: 1px dashed #cccccc;">

					<td style="text-align:center">' . $re["purchaseorder_no"] . '</td>
					<td style="text-align:center">' . $po_date . '</td>
					<td style="text-align:center">' . $re["product_name"] . '</td>';

				//if($POST['withconv']==1){
				$str .= '<td style="text-align:center">' . $qty . '</td>
						<td style="text-align:center">' . $pen . '</td>
						<td style="text-align:center">' . $po_due_date . '</td>
					</tr>';
				/*}else{
						$str.='<td style="text-align:center">'.$re["unit_name"].'</td>
						<td style="text-align:center">'.$re["product_qty"].'</td>
						<td style="text-align:center">'.number_format($pen,2).'</td>
						</tr>';
					}*/
			}

			$j++;
		}
		$str .= '<tr style="border-top:0.5px #000 solid;">
			<td colspan="2"></td>
			<td >Total :</td>
			<td style="text-align:center"><strong style="color:green">' . number_format($tot_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_cqty, 2) . '</strong></td>
			<td style="text-align:center"><strong style="color:green">' . number_format($tot_pend_qty, 2) . '</strong><br><strong style="color:orange">' . number_format($tot_pend_cqty, 2) . '</strong></td>
			<td></td>
			</tr>';
	} else {
		$str .= '<tr>
			<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
			</tr>';
	}
	$str .= '				 
		</table>';
	echo $str;
} else if (strtolower($POST['mode']) == "itemgroupwisereport") {
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$cust_id = $POST['vendor_id'];
	$product_id = $POST['product_id'];

	$pr_row = get_product_detail($dbcon, $product_id);

	$set = "select * from tbl_company where company_id=" . $_SESSION['company_id'];
	$set_head = mysqli_fetch_assoc($dbcon->query($set));
	// $qrycust = "select * from tbl_customer where cust_id=" . $POST['vendor_id'];
	// $cust_rel = mysqli_fetch_assoc($dbcon->query($qrycust));

	// $vendor = "select * from tbl_ledger where l_id=" . $POST['vendor_id'];
	// $vendor_data = mysqli_fetch_assoc($dbcon->query($vendor));


	$str .= '
		<table  width="100%"   class="display">
		</table>
		<table  class="display table-bordered" id="data_list">
		<tr id="logo" class="logo" style="display:none">
		<td colspan="8" style="text-align:center;">
		<strong>' . $set_head['company_name'] . '</strong>
		</td>
		</tr>

		<tr style="border-bottom:0.5px #000 solid;">
		<td colspan="7" style="border:hidden">
		<strong>[ Pending Purchase Order ]</strong>
		</td>
		</tr>';
	$product_type = ['FINISH PRODUCT', 'ASSEMBLY PRODUCT', 'SUB ASSEMBLY', 'RAW MATERIAL', 'FINISH PART', 'BOI', 'CAPITAL GOODS', 'CONSUMABLE', 'Service'];
	if (count($POST['pr_type']) > 0) {

		foreach ($POST['pr_type'] as $key => $value) {

			foreach ($POST['pr_cat'] as $key1 => $value1) {
				$vendor = "select * from tbl_category where cat_id=" . $value1;
				$cat_data = mysqli_fetch_assoc($dbcon->query($vendor));
				if ($value1 == '0') {
					$cat_data['cat_name'] = 'PRIMARY';
				} else {
					$cat_data['cat_name'] = $cat_data['cat_name'];
				}
				$str .= '
					<tr style="border-top: solid white 30px;">
					
					<td colspan="2" style="border-right:hidden">
					<strong>Product Type : </strong>
					</td>
					<td colspan="" style="border-right:hidden">
					<strong>' . $product_type[$value] . '</strong>
					</td>
					<td colspan="4">
					</td>
					</tr>';
				$str .= '<tr style="">
					
					<td colspan="2" style="border-right:hidden;border-top:hidden">
					<strong>Item Category : </strong>
					</td>
					<td colspan="" style="border-right:hidden;border-top:hidden">
					<strong>' . $cat_data['cat_name'] . '</strong>
					</td>
					<td colspan="4" style="border-top:hidden">
					</td>
					</tr>';

				$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">Indent NO <br> Indent Date</th>
					<th width="9%" style="text-align:center">PO NO <br> PO Date</th>
					<th width="20%" style="text-align:center">Vendor Name</th>
					<th width="20%" style="text-align:center">Item Description</th>';

				$str .= '<th width="12%" style="text-align:center">P.O.Qty</th>
					<th width="10%" style="text-align:center">Pending Qty</th>
					<th width="12%" style="text-align:center">Delivery Date</th>
					</tr>';


				$tot_qty = 0;
				$tot_pend_qty = 0;
				$query = "SELECT um.unit_name,umc.unit_name as conv_unit,tpt.po_ref_id,tpt.unit_id,tpt.conv_unit_id,tpt.description,p.product_icode, tl.l_name as vendorname, trp.indent_no,trp.indent_date,tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no,trp.indent_date,tpt.product_qty,tpt.product_conv_qty,p.product_name,(select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.grn_trn_status=0 and tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as used_qty, (select sum(tbl_grn_trn.product_conv_qty) from tbl_grn_trn where tbl_grn_trn.grn_trn_status=0 and tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as usedc_qty,tpt.purchaseordertrn_id
					FROM tbl_purchaseorder as tp
					left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
					left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
					left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
					left JOIN unit_mst as um ON um.unitid=tpt.unit_id
					left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
					left JOIN product_mst as p ON p.product_id=tpt.product_id left JOIN tbl_category as tc ON p.product_category=tc.cat_id";
				$query .= " where p.product_type=" . $value;
				$query .= " and p.product_category=" . $value1;
				if ($POST['po_date_type']) {
					if ($POST['po_date_type'] == 'po' || $POST['po_date_type'] == 'PO') {

						if ($POST['fromdate'] != '') {
							$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($POST['fromdate'])) . "'";
						}

						if ($POST['todate'] != '') {
							$query .= " and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($POST['todate'])) . "'";
						}
					}
				}

				$result1 = $dbcon->query($query);
				$j = 1;
				if (mysqli_num_rows($result1) > 0) {
					while ($re = mysqli_fetch_assoc($result1)) {
						$pen = $re["product_qty"] - $re["used_qty"];
						$pen_conv = $re['product_conv_qty'] - $re['usedc_qty'];
						$po_date = '';
						if ($re['purchaseorder_date'] != '') {
							$po_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
						}

						if ($re['unit_id'] != $re['conv_unit_id']) {
							$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
							$pen = "<strong style='color:green'>" . $pen . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $pen_conv . " " . $re['conv_unit'] . "</strong>";
						} else {
							$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
							$pen = "<strong style='color:green'>" . $pen . " " . $re['unit_name'] . "</strong>";
						}
						$indent_no = get_indent_no_date($dbcon, $re['po_ref_id']);
						$delivery_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);
						/*$po_due_date='';
							if($re['purchaseorder_due_date']!=''){
								$po_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
							}*/
						/*$intent_date='';
							if($re['indent_date']!=''){
								$intent_date=date('d/m/Y',strtotime($re['indent_date']));
							}*/
						$re['description'] = ($re['description'] == 0) ? '' : $re['description'];
						if ($re["product_qty"] > $re["used_qty"]) {
							$tot_pend_qty = $tot_pend_qty + $pen;
							$tot_qty = $tot_qty + $re["product_qty"];
							$str .= '<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">' . $indent_no . '</td>
								<td style="text-align:center">' . $re["purchaseorder_no"] . '<br>' . $po_date . '</td>
								<td style="text-align:center">' . $re['vendorname'] . '</td>
								<td style="text-align:center">' . $re["product_name"] . '</td>

								<td style="text-align:center">' . $qty . '</td>
								<td style="text-align:center">' . $pen . '</td>
								<td style="text-align:center">' . $delivery_date . '</td>
								</tr>';
						}

						$j++;
					}
				} else {
					$str .= '<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
				}
			}
		}

		if (count($POST['pr_cat']) > 0) {
		} else {
			foreach ($POST['pr_type'] as $key => $value) {

				$str .= '<tr style="border-top: solid white 30px;">
					
					<td colspan="2" style="border-right:hidden">
					<strong>Product Type : </strong>
					</td>
					<td colspan="" style="border-right:hidden">
					<strong>' . $product_type[$value] . '</strong>
					</td>
					<td colspan="4">
					</td>
					</tr>';

				$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="10%" style="text-align:center">Indent NO <br> Indent Date</th>
					<th width="10%" style="text-align:center">PO NO <br> PO Date</th>
					<!--<th width="20%" style="text-align:center">Description & Drawing Number</th>-->
					<th width="12%" style="text-align:center">Vendor Name</th>
					<th width="25%" style="text-align:center">Item Description</th>';

				$str .= '<th width="10%" style="text-align:center">P.O.Qty</th>
					<th width="10%" style="text-align:center">Pending Qty</th>
					<th width="10%" style="text-align:center">Delivery Date</th>
					</tr>';


				$tot_qty = 0;
				$tot_pend_qty = 0;
				$query = "SELECT um.unit_name,umc.unit_name as conv_unit, tpt.po_ref_id, tpt.purchaseordertrn_id, tpt.unit_id, tpt.conv_unit_id, tpt.description,p.product_icode, tl.l_name as vendorname, trp.indent_no, trp.indent_date, tp.purchaseorder_date,tp.purchaseorder_due_date, tp.purchaseorder_no, trp.indent_date, tpt.product_qty,tpt.product_conv_qty, p.product_name, (select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.grn_trn_status=0 and tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as usedc_qty, (select sum(tbl_grn_trn.product_conv_qty)  from tbl_grn_trn where tbl_grn_trn.grn_trn_status=0 and tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as usedc_qty
					FROM tbl_purchaseorder as tp
					left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
					left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
					left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
					left JOIN unit_mst as um ON um.unitid=tpt.unit_id
					left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
					left JOIN product_mst as p ON p.product_id=tpt.product_id left JOIN tbl_category as tc ON p.product_category=tc.cat_id";
				$query .= " where p.product_type=" . $value;
				//$query.=" and p.product_category=".$value1;
				if ($POST['po_date_type']) {

					if ($POST['po_date_type'] == 'po' || $POST['po_date_type'] == 'PO') {

						if ($POST['fromdate'] != '') {
							$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($POST['fromdate'])) . "'";
						}

						if ($POST['todate'] != '') {
							$query .= " and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($POST['todate'])) . "'";
						}
					}
				}

				$result1 = $dbcon->query($query);
				$j = 1;
				if (mysqli_num_rows($result1) > 0) {

					while ($re = mysqli_fetch_assoc($result1)) {
						$pen = $re["product_qty"] - $re["used_qty"];
						$pen_conv = $re['product_conv_qty'] - $re['usedc_qty'];
						$po_date = '';
						if ($re['purchaseorder_date'] != '') {
							$po_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
						}

						if ($re['unit_id'] != $re['conv_unit_id']) {
							$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
							$pen = "<strong style='color:green'>" . $pen . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $pen_conv . "  " . $re['conv_unit'] . "</strong>";
						} else {
							$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
							$pen = "<strong style='color:green'>" . $pen . " " . $re['conv_unit'] . "</strong>";
						}
						$indent_no = get_indent_no_date($dbcon, $re['po_ref_id']);
						$delivery_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);
						/*$po_due_date='';
							if($re['purchaseorder_due_date']!=''){
								$po_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
							}
							$intent_date='';
							if($re['indent_date']!=''){
								$intent_date=date('d/m/Y',strtotime($re['indent_date']));
							}
							$re['description']=($re['description']==0) ? '':$re['description'];*/
						if ($re["product_qty"] > $re["used_qty"]) {
							$tot_pend_qty = $tot_pend_qty + $pen;
							$tot_qty = $tot_qty + $re["product_qty"];
							$str .= '<tr style="  border: 1px dashed #cccccc;">
								<td style="text-align:center">' . $indent_no . '</td>
								<td style="text-align:center">' . $re["purchaseorder_no"] . '<br>' . $po_date . '</td>
								<!--<td style="text-align:center">' . $re['product_icode'] . '<br>' . $po_due_date . '</td>-->
								<td style="text-align:center">' . $re['vendorname'] . '</td>
								<td style="text-align:center">' . $re["product_name"] . '</td>

								<td style="text-align:center">' . $qty . '</td>
								<td style="text-align:center">' . $pen . '</td>
								<td style="text-align:center">' . $delivery_date . '</td>
								</tr>';
						}
						$j++;
					}
				} else {
					$str .= '<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
				}
			}
			//exit;
		}
	}

	if (count($POST['pr_cat']) > 0) {
		if (count($POST['pr_type']) == 0) {
			foreach ($POST['pr_cat'] as $key1 => $value1) {


				$vendor = "select * from tbl_category where cat_id=" . $value1;
				$cat_data = mysqli_fetch_assoc($dbcon->query($vendor));

				if ($value1 == 0) {
					$cat_data['cat_name'] = 'PRIMARY';
				} else {
					$cat_data['cat_name'] = $cat_data['cat_name'];
				}
				$str .= '<tr></tr>
					<tr style="border-top:0.5px #000 solid;margin-top:10px;">
					
					<td colspan="2" style="border-right:hidden">
					<strong>Item Category : </strong>
					</td>
					<td style="border-right:hidden">
					<strong>' . $cat_data['cat_name'] . '</strong>
					</td>
					<td colspan="4">
					<strong></strong>
					</td>
					</tr>';

				$str .= '<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
					<th width="9%" style="text-align:center">Indent NO <br> Indent Date</th>
					<th width="9%" style="text-align:center">PO NO <br> PO Date</th>
					<!--<th width="20%" style="text-align:center">Description & Drawing Number</th>-->
					<th width="10%" style="text-align:center">Vendor Name</th> 
					<th width="20%" style="text-align:center">Item Description</th>';

				$str .= '
					<th width="10%" style="text-align:center">P.O.Qty</th>
					<th width="10%" style="text-align:center">Pending Qty</th>
					<th width="12%" style="text-align:center">Delivery Date</th>
					</tr>';

				$tot_qty = 0;
				$tot_pend_qty = 0;
				$query = "SELECT um.unit_name,umc.unit_name as conv_unit,tpt.unit_id,tpt.conv_unit_id,tpt.purchaseordertrn_id,tpt.po_ref_id,tpt.description,p.product_icode, tl.l_name as vendorname, trp.indent_no,trp.indent_date,tp.purchaseorder_date,tp.purchaseorder_due_date,tp.purchaseorder_no,trp.indent_date,tpt.product_qty,tpt.product_conv_qty, p.product_name,(select sum(tbl_grn_trn.product_qty)  from tbl_grn_trn where tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as used_qty, (select sum(tbl_grn_trn.product_conv_qty)  from tbl_grn_trn where tbl_grn_trn.grn_trn_status=0 and tbl_grn_trn.purchaseordertrn_id=tpt.purchaseordertrn_id and tbl_grn_trn.product_id=tpt.product_id ) as usedc_qty
					FROM tbl_purchaseorder as tp
					left JOIN tbl_purchaseordertrn as tpt ON tpt.purchaseorder_id=tp.purchaseorder_id
					left JOIN tbl_request_product as trp ON trp.rp_id=tp.po_ref_id
					left JOIN tbl_ledger as tl ON tl.l_id=tp.vender_id
					left JOIN unit_mst as um ON um.unitid=tpt.unit_id
					left JOIN unit_mst as umc ON umc.unitid=tpt.conv_unit_id
					left JOIN product_mst as p ON p.product_id=tpt.product_id left JOIN tbl_category as tc ON p.product_category=tc.cat_id";
				//$query.=" where p.product_type=".$value;
				$query .= " where p.product_category=" . $value1;
				if ($POST['po_date_type']) {

					if ($POST['po_date_type'] == 'po' || $POST['po_date_type'] == 'PO') {

						if ($POST['fromdate'] != '') {
							$query .= " and tp.purchaseorder_date>='" . date('Y-m-d', strtotime($POST['fromdate'])) . "'";
						}

						if ($POST['todate'] != '') {
							$query .= " and tp.purchaseorder_date<='" . date('Y-m-d', strtotime($POST['todate'])) . "'";
						}
					}
				}

				$result1 = $dbcon->query($query);
				$j = 1;
				if (mysqli_num_rows($result1) > 0) {
					while ($re = mysqli_fetch_assoc($result1)) {
						$pen = $re["product_qty"] - $re["used_qty"];
						$pen_conv = $re['product_conv_qty'] - $re['usedc_qty'];
						$po_date = '';
						if ($re['purchaseorder_date'] != '') {
							$po_date = date('d/m/Y', strtotime($re['purchaseorder_date']));
						}

						if ($re['unit_id'] != $re['conv_unit_id']) {
							$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $re['product_conv_qty'] . " " . $re['conv_unit'] . "</strong>";
							$pen = "<strong style='color:green'>" . $pen . " " . $re['unit_name'] . "</strong><br><strong style='color:orange'>" . $pen_conv . " " . $re['conv_unit'] . "</strong>";
						} else {
							$qty = "<strong style='color:green'>" . $re['product_qty'] . " " . $re['unit_name'] . "</strong>";
							$pen = "<strong style='color:green'>" . $pen . " " . $re['unit_name'] . "</strong>";
						}

						$indent_no = get_indent_no_date($dbcon, $re['po_ref_id']);
						$delivery_date = get_delivery_date_qty_po($dbcon, $re['purchaseordertrn_id']);
						/*$po_due_date='';
							if($re['purchaseorder_due_date']!=''){
								$po_due_date=date('d/m/Y',strtotime($re['purchaseorder_due_date']));
							}
							$intent_date='';
							if($re['indent_date']!=''){
								$intent_date=date('d/m/Y',strtotime($re['indent_date']));
							}*/
						/*$re['description']=($re['description']==0) ? '':$re['description'];*/
						if ($re["product_qty"] > $re["used_qty"]) {
							$tot_pend_qty = $tot_pend_qty + $pen;
							$tot_qty = $tot_qty + $re["product_qty"];
							$str .= '<tr style="border: 1px dashed #cccccc;">
								<td style="text-align:center">' . $indent_no . '</td>
								<td style="text-align:center">' . $re["purchaseorder_no"] . '<br>' . $po_date . '</td>
								<td style="text-align:center">' . $re['vendorname'] . '</td>
								<td style="text-align:center">' . $re["product_name"] . '</td>

								<td style="text-align:center">' . $qty . '</td>
								<td style="text-align:center">' . $pen . '</td>
								<td style="text-align:center">' . $delivery_date . '</td>
								</tr>';
						}
						$j++;
					}
				} else {
					$str .= '<tr>
						<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
				}
			}
		}
	}
	$str .= '				 
		</table>';
	echo $str;
}
