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
	
	
	$str .= '
	<table  width="100%"   class="display table  table-striped">
	</table>
	<table  class="display table table-bordered table-striped" id="data_list">
	<tr style="background-color: #b9b7b7;color: #1c1d1e;">
	<td colspan="4" style="text-align:center"><strong> Day Book
	</strong></td>
	<td colspan="2" style="text-align:right">Date
	<label>  : <strong>' . date('d/m/Y', strtotime($s_date[0])) . '</strong> To <strong>' . date('d/m/Y', strtotime($s_date[1])) . '</strong></label></td>
	</tr>

	<tr style="background-color: #d7d4d4;color: #1c1d1e;">
	<th width="10%" style="text-align:center">Date</th>
	<th width="10%" style="text-align:center">Type</th>
	<th width="10%" style="text-align:center">Vah/Bill No</th>
	<th width="35%" style="text-align:left">Account</th>
	<th width="10%" style="text-align:right">Debit</th>
	<th width="10%" style="text-align:right">Cradit</th>
	</tr>
	<tbody>';
	
	$qry = 'select mst.module_name,mst.module_id,general_book_id,ref_date,entry_type,amount,led.l_name from tbl_general_book as mst 
	left join tbl_ledger as led on led.l_id=mst.ledger_id
	where mst.genral_book_status=0 and mst.ref_date>="' . date('Y-m-d', strtotime($s_date[0])) . '" and mst.ref_date<="' . date('Y-m-d', strtotime($s_date[1])) . '" and mst.company_id='.$_SESSION["company_id"].' group by mst.module_id,mst.module_name ';
	// echo "</br></br>";
	//var_dump($qry);
	$result1 = $dbcon->query($qry);
	$i = 1;

	if (mysqli_num_rows($result1) > 0) {
		while ($re = mysqli_fetch_assoc($result1)) {

			if(strtolower($re['module_name'])=="purchase"){
				$qry_mo = 'select po_no from tbl_pono as mst 
				where mst.po_id='.$re["module_id"];
				$result_mo = $dbcon->query($qry_mo);
				$re_mo = mysqli_fetch_assoc($result_mo);
				$doc_no=$re_mo['po_no'];
			}else if(strtolower($re['module_name'])=="invoice"){
				$qry_mo = 'select invoice_no from tbl_invoice as mst 
				where mst.invoice_id='.$re["module_id"];
				$result_mo = $dbcon->query($qry_mo);
				$re_mo = mysqli_fetch_assoc($result_mo);
				$doc_no=$re_mo['invoice_no'];
			}else{
				$doc_no="";
			}
			
			
			$str .= '<tr>
			<td style="text-align:center">' . date('d/m/Y', strtotime($re['ref_date'])) . '</td>
			<td style="text-align:center">' .$re['module_name'] . '</td>
			<td style="text-align:center">' .$doc_no. '</td>
			<td style="text-align:left">' .$re['l_name'] . '</td>
			';
			if($re['entry_type']==1){
							//cradit
				$str .= '<td style="text-align:right"></td>
				<td style="text-align:right">' .$re['amount'] . '</td>';
				$total_cradit_amount=$total_cradit_amount+$re['amount'];
			}else{
				$str .= '<td style="text-align:right">' .$re['amount'] . '</td>
				<td style="text-align:right"></td>';
				$total_debit_amount=$total_debit_amount+$re['amount'];
			}
			
			$str .= '</tr>';
			 $rp=sub_entry_load($dbcon,$re["module_name"],$re["module_id"],$re["general_book_id"]);
			//print_r($rp);
			$str .=$rp['str'];
			
			$total_debit_amount=$total_debit_amount+$rp['total_debit_amount'];
			$total_cradit_amount=$total_cradit_amount+$rp['total_cradit_amount'];
		}
		$i++;
		$str .= '<tr style="background-color: #d7d4d4;">';
		$str .= '<td class="text-right" style="color: #1c1d1e;" colspan="4"><strong>TOTAL</strong></td>
		<td style="text-align:right;color:#d12a2a;" ><strong> '.$total_debit_amount.' </strong></td>
		<td style="text-align:right;color:green;" ><strong> '.$total_cradit_amount.'  </strong></td>';
		$str .= '</tr>';
	} else {
		$str .= '<tr>
		<td colspan="6" style="text-align:center">NO DATA FOUND  </td>
		</tr>';
	}
	$str .= '</tbody>				 
	</table>';

	echo $str;
}


function sub_entry_load($dbcon,$module_name,$module_id,$general_book_id){
	$str="";
		 $qry_sub1= 'select mst.module_name,mst.module_id,general_book_id,ref_date,entry_type,amount,led.l_name from tbl_general_book as mst 
			left join tbl_ledger as led on led.l_id=mst.ledger_id
			where mst.genral_book_status=0 and mst.ledger_id!=0 and mst.module_name="'.$module_name.'" and module_id='.$module_id.' and general_book_id!='.$general_book_id;
			$result_sub1 = $dbcon->query($qry_sub1);
			while($re_sub = mysqli_fetch_assoc($result_sub1))
			{
				
				if(strtolower($re_sub['module_name'])=="purchase"){
					$qry_mo = 'select po_no from tbl_pono as mst 
					where mst.po_id='.$re_sub["module_id"];
					$result_mo = $dbcon->query($qry_mo);
					$re_mo = mysqli_fetch_assoc($result_mo);
					$doc_no=$re_mo['po_no'];
				}else if(strtolower($re_sub['module_name'])=="invoice"){
					$qry_mo = 'select invoice_no from tbl_invoice as mst 
					where mst.invoice_id='.$re_sub["module_id"];
					$result_mo = $dbcon->query($qry_mo);
					$re_mo = mysqli_fetch_assoc($result_mo);
					$doc_no=$re_mo['invoice_no'];
				}else{
					$doc_no="";
				}


				$str .= '<tr>
				<td style="text-align:center"></td>
				<td style="text-align:center"></td>
				<td style="text-align:center"></td>
				<td style="text-align:left">' .$re_sub['l_name'] . '</td>
				';
				if($re_sub['entry_type']==1){
							//cradit
					$str .= '<td style="text-align:right"></td>
					<td style="text-align:right">' .$re_sub['amount'] . '</td>';
					$total_cradit_amount=$total_cradit_amount+$re_sub['amount'];
				}else{
					$str .= '<td style="text-align:right">' .$re_sub['amount'] . '</td>
					<td style="text-align:right"></td>';
					$total_debit_amount=$total_debit_amount+$re_sub['amount'];
				}

				$str .= '</tr>';
			}
		$rp["str"]=$str;
		$rp["total_debit_amount"]=$total_debit_amount;
		$rp["total_cradit_amount"]=$total_cradit_amount;
	return $rp;
}