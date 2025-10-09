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
  
	
	$qry = "SELECT 
            bv.version_name, 
            p.product_name,
            bom_trn.product_base_qty,
			bom_trn.product_conv_qty,
			bom_trn.bom_id
        FROM tbl_bomtrn AS bom_trn
        LEFT JOIN pro_ms_bom_version AS bv ON bv.bom_version_id = bom_trn.bom_version_id
        LEFT JOIN product_mst AS p ON p.product_id = bom_trn.product_id
        WHERE bom_trn_status = 0 
        AND bom_trn.product_id =".$POST['product_id'];
	$res = $dbcon->query($qry);
		$i = 1;
	while($pro_rel = brp_mysqli_fetch_assoc($res)){
		$str .= '
				<table  class="display table table-bordered table-striped" id="data_list">
					<tr>
						<td colspan="5" style="text-align:center;"><strong> Product Name : ' . $pro_rel['product_name'] . ' -- ' . $pro_rel['product_icode'] . ' --- BOM Version : '.$pro_rel['version_name'].' --- Used Qty : '. $pro_rel['product_base_qty'] .'
						</strong></td>
					</tr>
					<tr>
						<th width="5%" style="text-align:center">Sr. No.</th>
						<th width="30%" style="text-align:center">Product Name</th>
						<th width="12%" style="text-align:center">Bom Version Name</th>
						<th width="12%" style="text-align:center">Bom Base Qty</th>
						<th width="12%" style="text-align:center">Bom Conv Qty</th>
					</tr>
				 <tbody>';
               $query = "SELECT bom.bom_no,bv.version_name,
                  bom.product_base_qty, bom.product_conv_qty, p.product_name
             FROM 
                 tbl_bom AS bom
				 LEFT JOIN product_mst AS p ON p.product_id = bom.bom_product
				 LEFT JOIN pro_ms_bom_version AS bv ON bv.bom_version_id = bom.bom_version_id
             WHERE 
			 bom.bom_status = 0 and bom.bom_id = " . $pro_rel['bom_id'];
   
			$res1 = $dbcon->query($query);
			while($rel1 = brp_mysqli_fetch_assoc($res1)){
				
					$str .= '<tr>
					<td style="text-align:center">' . $i . '</td>
					<td style="text-align:center">' . $rel1['product_name'] . '</td>
					<td style="text-align:center">' . $rel1['version_name'] . '</td>
					<td style="text-align:center">' . $rel1['product_base_qty'] . '</td>
					<td style="text-align:center">' . $rel1['product_conv_qty'] . '</td>
					';

		$str .= '</tr>';
		$i++;
			}
	}
	$str .= '</tbody>				 
				  </table>';

	echo $str;
}
