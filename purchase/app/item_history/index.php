<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
}
else if(strtolower($POST['mode']) == "po_product_report")
{
	//var_dump($POST);
	//$s_date=explode(' - ',$POST['date']);
	$set = "select * from tbl_company where company_id=".$_SESSION
	['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	/*$where = '';

	if($POST['purchaseorder_id']){
		$where .= " and po.purchaseorder_id=".$POST['purchaseorder_id'];
	}
	if($POST['vender_id']){
		$where .= " and po.vender_id=".$POST['vender_id'];
	}*/
	$product_name = "select product_name from product_mst where product_id=".$POST['product_id'];
	$product_row=mysqli_fetch_assoc($dbcon->query($product_name));

	$report_qty = get_month_wise_stock($dbcon,$POST['product_id'],'1');

	$report_qty2 = get_month_wise_stock($dbcon,$POST['product_id'],'2');
	/*var_dump($report_qty['opening_unit']);
	var_dump($report_qty2['opening_unit']);*/
	if($report_qty['opening_unit']){
		$report_qty['opening_unit'] = $report_qty['opening_unit'];
	}else{
		$report_qty['opening_unit'] = $report_qty2['opening_unit'];
	}
	$opening_balance = ($report_qty['opening_bal'] + $report_qty['opening_balance'])-($report_qty2['opening_bal'] + $report_qty2['opening_balance']);
	$closing_april = ($opening_balance+$report_qty['April'])- ($report_qty2['April']).' '.$report_qty['opening_unit'];
	$closing_may = ($closing_april +$report_qty['May'])-($report_qty2['May']).' '.$report_qty['opening_unit'];
	$closing_june = ($closing_may +$report_qty['June'])-($report_qty2['June']).' '.$report_qty['opening_unit'];
	$closing_july = ($closing_june +$report_qty['July'])-($report_qty2['July']).' '.$report_qty['opening_unit'];
	$closing_august = ($closing_july +$report_qty['August'])-($report_qty2['August']).' '.$report_qty['opening_unit'];
	$closing_september = ($closing_august +$report_qty['September'])-($report_qty2['September']).' '.$report_qty['opening_unit'];
	$closing_octomber = ($closing_september +$report_qty['October'])-($report_qty2['October']).' '.$report_qty['opening_unit'];
	$closing_november = ($closing_octomber +$report_qty['November'])-($report_qty2['November']).' '.$report_qty['opening_unit'];
	$closing_december = ($closing_november +$report_qty['December'])-($report_qty2['December']).' '.$report_qty['opening_unit'];
	$closing_january = ($closing_december +$report_qty['January'])-($report_qty2['January']).' '.$report_qty['opening_unit'];
	$closing_february = ($closing_january +$report_qty['February'])-($report_qty2['February']).' '.$report_qty['opening_unit'];
	$closing_march = ($closing_february +$report_qty['March'])-($report_qty2['March']).' '.$report_qty['opening_unit'];

	$str ='';
	$str .='<table width="100%" class="display table table-bordered table-striped">
		<tr>
			<th colspan="8" style="text-align:center">
				<h4>'.$set_head['company_name'].'</h4>
				<strong>'.$set_head['address'].'</strong><br>
				<strong>Product Name : '.$product_row['product_name'].'</strong>

			</th>
		</tr>
		<tr>
			<th rowspan="2" style="vertical-align:inherit;text-align:center">Particular</th>
			<th colspan="2" style="text-align:center">Inwards</th>
			<th colspan="2" style="text-align:center">Outwards</th>
			<th colspan="2" style="text-align:center">Closing Balance</th>
		</tr>
		<tr>
			<th style="text-align:center">Quantity</th>
			<th style="text-align:center">Value</th>
			<th style="text-align:center">Quantity</th>
			<th style="text-align:center">Value</th>
			<th style="text-align:center">Quantity</th>
			<th style="text-align:center">Value</th>
		</tr>
		<tr>
			<td><strong>Opening Balance</strong></td>
			<td style="text-align:center"></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center">'.$opening_balance.' '.$report_qty['opening_unit'].'</td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/4/'.$POST['product_id'].'">April</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/4/'.$POST['product_id'].'">'.($report_qty['April']?$report_qty['April']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/4/'.$POST['product_id'].'">'.($report_qty2['April']?$report_qty2['April']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/4/'.$POST['product_id'].'">'.$closing_april.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/5/'.$POST['product_id'].'">May</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/5/'.$POST['product_id'].'">'.($report_qty['May']?$report_qty['May']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center"></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/5/'.$POST['product_id'].'">'.($report_qty2['May']?$report_qty2['May']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/5/'.$POST['product_id'].'">'.$closing_may.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/6/'.$POST['product_id'].'">June</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/6/'.$POST['product_id'].'">'.($report_qty['June']?$report_qty['June']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/6/'.$POST['product_id'].'">'.($report_qty2['June']?$report_qty2['June']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/6/'.$POST['product_id'].'">'.$closing_june.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/7/'.$POST['product_id'].'">July</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/7/'.$POST['product_id'].'">'.($report_qty['July']?$report_qty['July']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/7/'.$POST['product_id'].'">'.($report_qty2['July']?$report_qty2['July']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/7/'.$POST['product_id'].'">'.$closing_july.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/8/'.$POST['product_id'].'">August</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/8/'.$POST['product_id'].'">'.($report_qty['August']?$report_qty['August']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/8/'.$POST['product_id'].'">'.($report_qty2['August']?$report_qty2['August']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/8/'.$POST['product_id'].'">'.$closing_august.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/9/'.$POST['product_id'].'">September</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/9/'.$POST['product_id'].'">'.($report_qty['September']?$report_qty['September']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/9/'.$POST['product_id'].'">'.($report_qty2['September']?$report_qty2['September']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/9/'.$POST['product_id'].'">'.$closing_september.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/10/'.$POST['product_id'].'">October</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/10/'.$POST['product_id'].'">'.($report_qty['October']?$report_qty['October']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/10/'.$POST['product_id'].'">'.($report_qty2['October']?$report_qty2['October']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/10/'.$POST['product_id'].'">'.$closing_octomber.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/11/'.$POST['product_id'].'">November</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/11/'.$POST['product_id'].'">'.($report_qty['November']?$report_qty['November']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/11/'.$POST['product_id'].'">'.($report_qty2['November']?$report_qty2['November']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/11/'.$POST['product_id'].'">'.$closing_november.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/12/'.$POST['product_id'].'">December</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/12/'.$POST['product_id'].'">'.($report_qty['December']?$report_qty['December']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/12/'.$POST['product_id'].'">'.($report_qty2['December']?$report_qty2['December']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/12/'.$POST['product_id'].'">'.$closing_december.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/1/'.$POST['product_id'].'">January</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/1/'.$POST['product_id'].'">'.($report_qty['January']?$report_qty['January']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/1/'.$POST['product_id'].'">'.($report_qty2['January']?$report_qty2['January']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/1/'.$POST['product_id'].'">'.$closing_january.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/2/'.$POST['product_id'].'">February</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/2/'.$POST['product_id'].'">'.($report_qty['February']?$report_qty['February']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/2/'.$POST['product_id'].'">'.($report_qty2['February']?$report_qty2['February']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/2/'.$POST['product_id'].'">'.$closing_february.'</a></td>
			<td style="text-align:center">-</td>
		</tr>

		<tr>
			<td><strong><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/3/'.$POST['product_id'].'">March</a></strong></td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/3/'.$POST['product_id'].'">'.($report_qty['March']?$report_qty['March']:0).' '.$report_qty['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/3/'.$POST['product_id'].'">'.($report_qty2['March']?$report_qty2['March']:0).' '.$report_qty2['unit_name'].'</a></td>
			<td style="text-align:center">-</td>
			<td style="text-align:center"><a href="'.ROOT.PURCHASE_ROOT.'stock_detai/3/'.$POST['product_id'].'">'.$closing_march.'</a></td>
			<td style="text-align:center">-</td>
		</tr>';

	

 	
	$str.='</table>';
	echo $str;
}
?>