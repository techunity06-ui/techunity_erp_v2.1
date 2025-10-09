<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
include($path."config/image.php");
// error_reporting(E_ALL);
$getspecialConfiguration=getspecialConfiguration($dbcon);


$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);
$image = new SimpleImage();
if(strtolower($POST['mode']) == "generate_report") {
	$s_date=explode(' - ',$POST['date']);

	$vender_id = $POST['ledger_id'];
	if (!is_array($vender_id)) {
    	$vender_id = [$vender_id]; // wrap it into an array if it's not
	}
	$led_id = implode(",",$vender_id);

	$where ="";
	if($led_id != ""){
		$where .= " and grn.vender_id in (".$led_id.")";
	}

	$po_no = $POST['po_no'];
	if (!is_array($po_no)) {
		$po_no = [$po_no];// wrap it into an array if it's not
	}
	$po_id = implode(",", $po_no);

	if($po_id != ""){
		$where .= " and po.purchaseorder_id in (".$po_id.")";
	}

	$product_id = $POST['product_id'];
	if (!is_array($product_id)) {
		$product_id = [$product_id];// wrap it into an array if it's not
	}
	$pro_id = implode(",", $product_id);

	if($pro_id != ""){
		$where .= " and grntrn.product_id in (".$pro_id.")";
	}
	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head= brp_mysqli_fetch_assoc($dbcon->query($set));		
	$potr = 'SELECT GROUP_CONCAT(trn.purchaseordertrn_id) as trn_id FROM tbl_grn_trn as trn
	left join tbl_grn as grn on grn.grn_id = trn.grn_id
	where  grn_trn_status=0 and grn.grn_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and grn.grn_date<="'.date('Y-m-d',strtotime($s_date[1])).'"';
	$po_ex = $dbcon->query($potr);
	$potrr = mysqli_fetch_array($po_ex);
	$potrtax = "select GROUP_CONCAT(tx_tax_id) as tax from tbl_tax_trn where tx_transaction_id in (".$potrr['trn_id'].") and tx_transaction_type = 'purchase_order' and tx_status=0";
	$potrex = $dbcon->query($potrtax);
	$potrtaxr = mysqli_fetch_array($potrex);
	// $tax = "select tax.*,led.l_name,led.l_id from tbl_tax as tax
	// left join tbl_ledger as led on led.l_id = tax.ledger_id
	// where tax_id in (".$potrtaxr['tax'].") group by ledger_id";
	// $tax_ex = $dbcon->query($tax);
	// $cnt = mysqli_num_rows($tax_ex);
	// $colspan = 22+$cnt;
	$tax_ids_raw = $potrtaxr['tax'] ?? ''; // Avoid undefined index notice
	$tax_ids_array = array_filter(array_map('intval', explode(',', $tax_ids_raw))); // Ensure integers only

	if (!empty($tax_ids_array)) {
		$tax_ids_str = implode(',', $tax_ids_array); // Safe, comma-separated values

		$tax = "SELECT tax.*, led.l_name, led.l_id 
				FROM tbl_tax AS tax
				LEFT JOIN tbl_ledger AS led ON led.l_id = tax.ledger_id
				WHERE tax_id IN ($tax_ids_str)
				GROUP BY ledger_id";

		$tax_ex = $dbcon->query($tax);
		$cnt = mysqli_num_rows($tax_ex);
		$colspan = 22 + $cnt;
	} else {
		// No tax IDs found - handle gracefully
		$tax_ex = false;
		$cnt = 0;
		$colspan = 22; // base value
	}
			//$str .= $tax;
	if($getspecialConfiguration['flowjet_permission'] == '1'){
		$colspan = 19;
	}


	
	$str .='<table  class="display table table-bordered table-striped" id="" style="overflow: auto;">
	<thead class="resdisplay">
	<tr id="logo">
	<td class="noborder" colspan="'.$colspan.'" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong></td>
	</tr>
	<tr>
	<td colspan="'.$colspan.'" class="noborder"><strong>GRN Register</strong></td>
	<td class="noborder"><strong>Date :</strong>'.date('d/m/Y').'</td>
	</tr>
	<tr>
	<th style="text-align:center;">Sr. NO.</th>';
	$str .='<th style="text-align:center;white-space:nowrap;">Grn No</th>';
	$str .='<th style="text-align:center;white-space:nowrap;">Grn Dt.</th>
	<th style="text-align:center;white-space:nowrap;">Grn Type</th>
	<th style="text-align:center;white-space:nowrap;">Gir No </th>
	<!--<th style="text-align:center;white-space:nowrap;">Date</th>-->
	<th style="text-align:center;white-space:nowrap;">PO No</th>
	<th style="text-align:center;white-space:nowrap;">PO Date</th>
	<th style="text-align:center;white-space:nowrap;">Supplier Name</th>';
	if($getspecialConfiguration['flowjet_permission'] == '0'){
		$str .='<th style="text-align:center;white-space:nowrap;">GST No.</th>
		<th style="text-align:center;white-space:nowrap;">State</th>
		<th style="text-align:center;white-space:nowrap;">Item Code</th>';
	}
	$str .='<th style="text-align:center;white-space:nowrap;">Item Description</th>
	<th style="text-align:center;white-space:nowrap;">Invoice Quantity</th>
	<!--<th style="text-align:center;white-space:nowrap;">Act Qty</th>-->
	<th style="text-align:center;white-space:nowrap;">Accepted Quantity</th>
	<th style="text-align:center;white-space:nowrap;">Rejected Quantity</th>
	<th style="text-align:center;white-space:nowrap;">UOM</th>';
	if($getspecialConfiguration['flowjet_permission'] == '0'){
		$str .='<th style="text-align:center;white-space:nowrap;">GST</th>';
	}
	$str .='<!--<th style="text-align:center;white-space:nowrap;">Chall Conv Qty</th>
	<th style="text-align:center;white-space:nowrap;">Acpt Conv Qty</th>-->
	<th style="text-align:center;white-space:nowrap;">Rate</th>
	<th style="text-align:center;white-space:nowrap;">Challan No.</th>
	<th style="text-align:center;white-space:nowrap;">Challan Date</th>
	<th style="text-align:center;white-space:nowrap;">Invoice No.</th>
	<th style="text-align:center;white-space:nowrap;">Invoice Date </th>';
	if($getspecialConfiguration['flowjet_permission'] == '0'){
		$str .='<th style="text-align:center;white-space:nowrap;">HSN Code </th>';
		$str .='<th style="text-align:center;white-space:nowrap;">Basic Value</th>
		<th style="text-align:center;white-space:nowrap;">CGST</th>
		<th style="text-align:center;white-space:nowrap;">SGST/UTGST</th>
		<th style="text-align:center;white-space:nowrap;">IGST</th>
		<th style="text-align:center;white-space:nowrap;">TCS(if Any)</th>';
	}
	// while($tax_r = mysqli_fetch_array($tax_ex)){
	// 	$str .= '<th style="text-align:center;white-space:nowrap;">'.$tax_r['l_name'].'</th>';
	// 	$totalname="total".$tax_r['l_name'];
	// 	$totalname=0;
	// }
	if ($tax_ex && mysqli_num_rows($tax_ex) > 0) {
		while ($tax_r = mysqli_fetch_array($tax_ex)) {
			$str .= '<th style="text-align:center;white-space:nowrap;">'.$tax_r['l_name'].'</th>';
			$totalname = "total" . $tax_r['l_name'];
			$$totalname = 0; // Dynamically creating variable named $totalXYZ
		}
	} else {
		// Optionally log the error
		echo "Error in SQL query: " . $dbcon->error;
	}
	if($getspecialConfiguration['flowjet_permission'] == '0'){
		$str .='<th style="text-align:center;white-space:nowrap;">Tot Val.</th>';
	}
	$str .='<th style="text-align:center;white-space:nowrap;">IQC Date</th>
	<th style="text-align:center;white-space:nowrap;">Insp. User</th>
	</tr>
	</thead>
	<tbody>';
	
	
	$grn_register = 'select grntrn.*,grn.grn_no,grn.grn_date,grn.invoice_no,grn.challan_no,grn.ref_type,grn.gir_no,potrn.purchaseordertrn_id,potrn.product_rate,po.purchaseorder_no,po.purchaseorder_date,led.l_name,pro.product_name,pro.product_icode,unit.unit_name,user.user_name,grn.cdate,cunit.unit_name as conv_unit,runit.unit_name as rate_unit_name,(potrn.cgst_tax_per+potrn.sgst_tax_per+potrn.igst_tax_per) as gst_per,potrn.cgst_tax_per,potrn.sgst_tax_per,potrn.igst_tax_per,mhsn.hsn_code,state.state_name,led.gst_no from tbl_grn_trn as grntrn 
	left join tbl_grn as grn on grn.grn_id = grntrn.grn_id
	left join tbl_purchaseordertrn as potrn on potrn.purchaseordertrn_id = grntrn.purchaseordertrn_id
	left join tbl_purchaseorder as po on po.purchaseorder_id = potrn.purchaseorder_id
	left join tbl_ledger as led on led.l_id = grn.vender_id
	left join state_mst as state on state.stateid=led.stateid
	left join product_mst as pro on pro.product_id = grntrn.product_id
	left join mst_hsn_code as mhsn on mhsn.hsn_id = pro.product_hsn
	left join unit_mst as unit on unit.unitid = grntrn.unit_id
	left join unit_mst as cunit on cunit.unitid = grntrn.product_conv_unit
	left join unit_mst as runit on runit.unitid = grntrn.rate_unit
	left join users as user on user.user_id = grntrn.user_id
	where grntrn.grn_trn_status=0 and grn.grn_status=0 and grn.ref_type in (2,4) '.$where.' and grn.grn_date>="'.date('Y-m-d',strtotime($s_date[0])).'" and grn.grn_date<="'.date('Y-m-d',strtotime($s_date[1])).'"';
		//$str .= $grn_register;
	$grn_ex = $dbcon->query($grn_register);
	$i = 1;
		//$str .= $grn_register;
	if(mysqli_num_rows($grn_ex)>0){
		
		while($grn_row = brp_mysqli_fetch_assoc($grn_ex)){
			$rate="";$grn_type="";
				/*if($grn_row['ref_type'] != 1){
					$grn_type .= "Purchase Order";
				}else if($grn_row['ref_type'] != 2){
					$grn_type .= "Job Work";
				}else{
					$grn_type="";
				}*/

				if($grn_row['ref_type']=='1'){ $grn_type="JOBWORK"; }else if($grn_row['ref_type']=='3'){ $grn_type="Inhouse";}else if($grn_row['ref_type']=='4'){ $grn_type="Direct";}else if($grn_row['ref_type']=='5'){ $grn_type="Ourside So";} else {  $grn_type="PO"; }

				if($grn_row['rate_unit']==$grn_row['unit_id']){
					$rate = $grn_row['product_qty'] * $grn_row['product_rate'];	
					// $grn_row['product_qty'] = $grn_row['product_qty'];
				}else{
					$rate = $grn_row['product_conv_qty'] * $grn_row['product_rate'];
					// $grn_row['product_qty'] = $grn_row['product_conv_qty'];
				}
				$purchase_order_date='';
				if($grn_row['purchaseorder_date']!="1970-01-01" && $grn_row['purchaseorder_date']!="0000-00-00" && $grn_row['purchaseorder_date']!="")
				{
					$purchase_order_date=date('d-m-Y',strtotime($grn_row['purchaseorder_date']));
				}

				$cgst_rate = $rate * $grn_row['cgst_tax_per'] /100;
				$sgst_rate = $rate * $grn_row['sgst_tax_per'] /100;
				$igst_rate = $rate * $grn_row['igst_tax_per'] /100;
				$tcs = "";
				$basic_val += $rate;

				$str .='<tr>
				<td style="text-align:center;white-space:nowrap">'.$i.'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['grn_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['grn_date'])).'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_type.'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['gir_no'].'</td>
				<!--<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['grn_date'])).'</td>-->
				<td style="text-align:center;white-space:nowrap">'.$grn_row['purchaseorder_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.$purchase_order_date.'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['l_name'].'</td>';
				if($getspecialConfiguration['flowjet_permission'] == '0'){
					$str .=	'<td style="text-align:center;white-space:nowrap">'.$grn_row['gst_no'].'</td>
							<td style="text-align:center;white-space:nowrap">'.$grn_row['state_name'].'</td>
							<td style="text-align:center;white-space:nowrap">'.$grn_row['product_icode'].'</td>';
					}
					$str .=	'<td style="text-align:center;white-space:nowrap">'.$grn_row['product_name'].'</td>
				<td style="text-align:center;white-space:nowrap">'.number_format($grn_row['product_qty'],4,".","").'121  <span style="color:green">'.$grn_row['unit_name'].'</span><br>'.number_format($grn_row['product_conv_qty'],4,".","").'  <span style="color:green">'.$grn_row['conv_unit'].' </td>
				<!--<td style="text-align:center;white-space:nowrap">'.$grn_row['product_qty'].'</td>-->
				<td style="text-align:center;white-space:nowrap">'.number_format($grn_row['product_qty'],4,".","").' <span style="color:green">'.$grn_row['unit_name'].'</span><br>'.number_format($grn_row['product_conv_qty'],4,".","").'  <span style="color:green">'.$grn_row['conv_unit'].'</td>
				<td style="text-align:center;white-space:nowrap">'.number_format(0,4,".","").'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['rate_unit_name'].'</td>';
				if($getspecialConfiguration['flowjet_permission'] == '0'){
					$str .=	'<td style="text-align:center;white-space:nowrap">'.$grn_row['gst_per'].' %</td>';
				}
				$str .=	'<!--<td style="text-align:center;white-space:nowrap">'.number_format($grn_row['product_conv_qty'],4,".","").' '.$grn_row['conv_unit'].'</td>
				<td style="text-align:center;white-space:nowrap">'.number_format($grn_row['product_conv_qty'],4,".","").' '.$grn_row['conv_unit'].'</td>-->
				<td style="text-align:center;white-space:nowrap">'.number_format($grn_row['product_rate'],2).'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['challan_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['grn_date'])).'</td>
				<td style="text-align:center;white-space:nowrap">'.$grn_row['invoice_no'].'</td>
				<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['grn_date'])).'</td>';
				if($getspecialConfiguration['flowjet_permission'] == '0'){
				$str .= '<td style="text-align:center;white-space:nowrap">'.$grn_row['hsn_code'].' </td>
					<td style="text-align:center;white-space:nowrap">'.number_format($rate,2).'</td>';
				}
				// $tax_ex = $dbcon->query($tax);
				if (!empty($tax)) {
					$tax_ex = $dbcon->query($tax);
					if (!$tax_ex) {
						echo "SQL Error: " . $dbcon->error;
					}
				} else {
					echo "No SQL query to execute — \$tax is empty.";
				}

				$total_am=0;
				while($tax_r = mysqli_fetch_array($tax_ex)){
					$tax_tr = "select mst.*,tx.ledger_id from tbl_tax_trn as mst 
					left join tbl_tax as tx on tx.tax_id=mst.tx_tax_id
					where tx_transaction_type='purchase_order' and tx_status=0 and  tx_transaction_id=".$grn_row['purchaseordertrn_id']." and tx.ledger_id=".$tax_r['l_id'];
					$tax_tex = $dbcon->query($tax_tr);
					$tr_tax = mysqli_fetch_array($tax_tex);
					if($tr_tax['ledger_id'] == $tax_r['l_id']){
						$tax_amt = $rate * $tr_tax['tx_tax_value'] /100;
						$total_am += $tax_amt;
						if($getspecialConfiguration['flowjet_permission'] == '0'){
							$str .= '<td style="text-align:center;white-space:nowrap;">'.number_format($tax_amt,2).'</td>';
						}
						$totalnameq1="total".$tax_r['l_name'];
						$totalnameq1+=$tax_amt;
					}else{
						if($getspecialConfiguration['flowjet_permission'] == '0'){
							$str .= '<td style="text-align:center;white-space:nowrap;">-</td>';
						}
					}
				}
				// if ($tax_ex && mysqli_num_rows($tax_ex) > 0) {
				// 	while ($tax_r = mysqli_fetch_array($tax_ex)) {
				// 		$tax_tr = "SELECT mst.*, tx.ledger_id 
				// 				FROM tbl_tax_trn AS mst 
				// 				LEFT JOIN tbl_tax AS tx ON tx.tax_id = mst.tx_tax_id
				// 				WHERE tx_transaction_type = 'purchase_order' 
				// 					AND tx_status = 0 
				// 					AND tx_transaction_id = ".$grn_row['purchaseordertrn_id']." 
				// 					AND tx.ledger_id = ".$tax_r['l_id'];

				// 		$tax_tex = $dbcon->query($tax_tr);

				// 		if ($tax_tex && $tr_tax = mysqli_fetch_array($tax_tex)) {
				// 			if ($tr_tax['ledger_id'] == $tax_r['l_id']) {
				// 				$tax_amt = $rate * $tr_tax['tx_tax_value'] / 100;
				// 				$total_am += $tax_amt;

				// 				if ($getspecialConfiguration['flowjet_permission'] == '0') {
				// 					$str .= '<td style="text-align:center;white-space:nowrap;">'.number_format($tax_amt, 2).'</td>';
				// 				}

				// 				$totalnameq1 = "total" . $tax_r['l_name'];
				// 				if (!isset($$totalnameq1)) $$totalnameq1 = 0;
				// 				$$totalnameq1 += $tax_amt;
				// 			} else {
				// 				if ($getspecialConfiguration['flowjet_permission'] == '0') {
				// 					$str .= '<td style="text-align:center;white-space:nowrap;">-</td>';
				// 				}
				// 			}
				// 		} else {
				// 			if ($getspecialConfiguration['flowjet_permission'] == '0') {
				// 				$str .= '<td style="text-align:center;white-space:nowrap;">-</td>';
				// 			}
				// 		}
				// 	}
				// } else {
				// 	// No tax data, output placeholders if needed
				// 	if ($getspecialConfiguration['flowjet_permission'] == '0') {
				// 		// You may want to output empty columns here if needed for layout
				// 	}
				// }

				if($getspecialConfiguration['flowjet_permission'] == '0'){
					$str .='<td style="text-align:center;white-space:nowrap">'.number_format($cgst_rate,2).'</td>
					<td style="text-align:center;white-space:nowrap">'.number_format($sgst_rate,2).'</td>
					<td style="text-align:center;white-space:nowrap">'.number_format($igst_rate,2).'</td>
					<td style="text-align:center;white-space:nowrap">'.number_format($tcs,2).'</td>';
				}
				$total_amt = $rate + $total_am + $cgst_rate + $sgst_rate + $igst_rate + $tcs;
				$tot_cgst += $cgst_rate;
				$tot_sgst += $sgst_rate;
				$tot_igst += $igst_rate;
				$tot_tcs += $tcs;
				$tot_amt += $total_amt;

				$fj_col = "";
				if($getspecialConfiguration['flowjet_permission'] == '0'){
					$fj_col = 'colspan="2" ';
					$str .='<td style="text-align:center;white-space:nowrap">'.number_format($total_amt,2).'</td>';
				}
				$str .='<td style="text-align:center;white-space:nowrap">'.date('d-m-Y',strtotime($grn_row['cdate'])).'</td>
				<td '.$fj_col.' style="text-align:center;white-space:nowrap">'.$grn_row['user_name'].'</td>
				</tr>';
				$i++;
			}

			if($getspecialConfiguration['flowjet_permission'] == '0'){
				
			$str .='<tr>
			<td colspan="23" style="text-align:right"><strong>Total</strong></td>
			<td style="text-align:center"><strong>'.number_format($basic_val,2).'</strong></td>';
			$tax_ex1 = $dbcon->query($tax);
			while($tax_rq = mysqli_fetch_array($tax_ex1)){
				$totalnameq="total".$tax_r['l_name'];
				$tax_tre = "select mst.*,tx.ledger_id from tbl_tax_trn as mst 
				left join tbl_tax as tx on tx.tax_id=mst.tx_tax_id
				where tx_transaction_type='purchase_order' and tx_status=0 and  tx_transaction_id in (".$potrr['trn_id'].") and tx.ledger_id=".$tax_r['l_id']." group by ledger_id";
				$tax_ter = $dbcon->query($tax_tre);
				$tr_tar = mysqli_fetch_array($tax_ter);
				if($tr_tar['ledger_id'] == $tax_r['ledger_id']){
					$str.='<td style="text-align:center"><strong>'.number_format($totalnameq,2).'</strong></td>';
				}else{
					$str.='<td style="text-align:center"><strong></strong></td>';
				}
				$str.='<td style="text-align:center"><strong></strong></td>';
			}
			$str .='
			<td style="text-align:center"><strong>'.number_format($tot_cgst,2).'</strong></td>
			<td style="text-align:center"><strong>'.number_format($tot_sgst,2).'</strong></td>
			<td style="text-align:center"><strong>'.number_format($tot_igst,2).'</strong></td>
			<td style="text-align:center"><strong>'.number_format($tot_tcs,2).'</strong></td>
			<td style="text-align:center"><strong>'.number_format($tot_amt,2).'</strong></td>
			<td style="text-align:center" colspan="2"><strong></strong></td>
			</tr>';
			}
		}else{
			$fj_col = "24";
			if($getspecialConfiguration['flowjet_permission'] == '1'){
					$fj_col = 'colspan="20" ';
				}
			$str .='<tr>
			<td colspan="25" style="text-align:center">No Data Yet..!!!</td>
			</tr>';
		}
		$str .='</tbody>				 
		</table>';
		
		echo $str;
	}
	?>
