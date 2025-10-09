<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
include($path . "config/image.php");
$image = new SimpleImage();
if ($_POST != NULL) {
	$POST = bulk_filter($dbcon, $_POST);
} else {
	$POST = bulk_filter($dbcon, $_GET);
}
if (strtolower($POST['mode']) == "generate_report") {
	$s_date = explode(' - ', $POST['date']);
	$_SESSION['start'] = $s_date[0];
	$_SESSION['end'] = $s_date[1];
	$start_date = $s_date[0];
	$end_date = $s_date[1];

		
	// if($POST['prod_id'] != ""){
	// 	$where = " and product_id = " . $POST['prod_id'];
	// }
	
	$qry_pro = "select * from product_mst where product_id = " . $POST['prod_id'];
	$pro_rel = brp_mysqli_fetch_assoc($dbcon->query($qry_pro));
	$str .= '
				<table  width="100%"   class="display table  table-striped">
				</table>
				<table  class="display table table-bordered table-striped" id="data_list">
					<tr>
						<td colspan="2"><strong>Product Ledger </strong></td>
						<td colspan="1" style="text-align:center"><strong> Product Name : ' . $pro_rel['product_name'] . ' -- ' . $pro_rel['product_icode'] . '
						</strong></td>
						<td colspan="1" style="text-align:right">Date
						<label>  : <strong>' . date('d/m/Y', strtotime($s_date[0])) . '</strong> To <strong>' . date('d/m/Y', strtotime($s_date[1])) . '</strong></label></td>
					</tr>
					
					<tr>
						<th width="5%" style="text-align:center">Sr. NO.</th>
						<th width="12%" style="text-align:center">Date</th>
						<!--<th width="12%" style="text-align:center">Process</th> -->
						<th width="35%" style="text-align:center">Description</th>
						<th width="12%" style="text-align:center">Reserve Stock</th>
					</tr>
				 <tbody>';
	$query = "select * from tbl_stock_trn where stock_status=0";
	$rel = brp_mysqli_fetch_assoc($dbcon->query($query));

	$reserve_stock = get_current_reserve_stock_below_start_date($dbcon, $POST['prod_id'], $pro_rel['product_base_unit'], $start_date, $end_date);
	$total = '';
	$total = $reserve_stock;
	// var_dump($reserve_stock);
	$str .= '<tr>
					<td data-label="" style="text-align:center"></td>
					<td data-label="DATE" style="text-align:center">' . date('d/m/Y', strtotime($s_date[0])) . '</td> 
					<!-- <td data-label="PROCESS" style="text-align:center"></td> -->
					<td data-label="DESCRIPTION" style="text-align:center">Reserve Stock</td>
					';
	if ($reserve_stock > 0) {
		$str .= '<td data-label="RESERVE STOCK" style="text-align:center;color:green;">' . $reserve_stock . '</td>';
	} else if ($reserve_stock < 0) {
		$str .= '<td data-label="RESERVE STOCK" style="text-align:center;color:green;">' . $reserve_stock . '</td>';
	} else {
		$str .= '<td data-label="RESERVE STOCK" style="text-align:center;color:green;">0</td>';
	}
	
	 $qry = 'select mst.*,pro.product_base_unit,unit.unit_name,pc.process_name,sp.po_req_no,rp.job_card_no,so.sales_order_no
	 from tbl_reserve_stock as mst 
			left join product_mst as pro on pro.product_id = mst.product_id
			left join tbl_allocate_process as ap on ap.p_id = mst.p_id
			left join process_mst as pc on pc.process_id = ap.process_id
			left join tbl_request_product as rp on rp.rp_id = ap.p_ref_id
			left join tbl_set_main_process as sp on rp.sp_id = sp.sp_id
			left join tbl_sales_ordertrn as so_trn on so_trn.sales_ordertrn_id = mst.sales_order_trn_id
			left join tbl_sales_order as so on so_trn.sales_order_id = so.sales_order_id
			left join unit_mst as unit on unit.unitid = pro.product_base_unit
			where CASE WHEN pro.product_base_unit = mst.base_unit 
			THEN (mst.base_stock - (select IFNULL(sum(base_stock),0) from tbl_reserve_stock where stock_flage = 2 and stock_status = 0 and ref_id = mst.reserve_id)) > 0
			 ELSE (mst.convert_stock - (select  IFNULL(sum(convert_stock),0) from tbl_reserve_stock where stock_flage = 2 and stock_status = 0 and ref_id = mst.reserve_id) ) > 0
			END and mst.stock_status=0 and stock_flage = 1 and mst.reserve_date>="' . date('Y-m-d', strtotime($s_date[0])) . '" and mst.reserve_date<="' . date('Y-m-d', strtotime($s_date[1])) . '" and mst.product_id=' . $POST['prod_id'] . ' and mst.ref_name!="opening_stock"';
	// echo "</br></br>";
	//var_dump($qry);
	$result1 = $dbcon->query($qry);
	$i = 1;

	if (brp_mysqli_num_rows($result1) > 0) {
		$i = 1;
		$add_stock = "";
		$deduct_stock = "";
		while ($re = brp_mysqli_fetch_assoc($result1)) {
			$description  = "";
			if($re['p_id'] > 0){
				$description.="Workorder No. : " . $re['po_req_no'] . "</br>";
				$description.="Jobcard No. : " . $re['job_card_no']. "</br>";
			}
			
			if($re['sales_order_trn_id'] > 0){
				$description.="<strong> Salesorder No. : " . $re['sales_order_no'] . "</strong></br>";
			}
			 $description  .= get_reserve_stock_ledger($dbcon, $re['ref_name'], $re['ref_id']);
			$str .= '<tr>
							<td style="text-align:center">' . $i . '</td>
							<td style="text-align:center">' . date('d/m/Y', strtotime($re['reserve_date'])) . '</td>
							<!-- <td style="text-align:center">' .$re['process_name'] . '</td> -->
							<td style="text-align:center">' . $description . '</td>';
			if ($re['stock_flage'] == 1) {
				if ($re['product_base_unit'] == $re['base_unit']) {
					$str .= '<td style="text-align:center;color:green;">' . $re['base_stock'] . ' ' . $re['unit_name'] . '</td>';
					$add_stock = $re['base_stock'];
				} else {
					$str .= '<td style="text-align:center;color:green;">' . $re['convert_stock'] . ' ' . $re['unit_name'] . '</td>';
					$add_stock = $re['convert_stock'];
				}
				$total += $add_stock;
			} else {
				$str .= '<td style="text-align:center"> - </td>';
			}
			$str .= '</tr>';
			$str .= '<tr>';
			$str .= '<td class="text-right" colspan="3"><strong>TOTAL RESERVE STOCk </strong></td>
					<td style="text-align:center;color:green;" colspan="1"><strong> '.$total.' ' . $re['unit_name'] .' </strong></td>';
			$str .= '</tr>';
		}
		$i++;
	} else {
		$str .= '<tr>
							<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
						</tr>';
	}
	$str .= '</tbody>				 
				  </table>';

	echo $str;
}
