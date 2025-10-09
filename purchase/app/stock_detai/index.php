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
	$set = "select * from tbl_company where company_id=".$_SESSION
	['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));
	
	$s_day = 1;
	$s_month = 4;
	$current_year = date("Y");
	$current_month = date("m");

	$e_day = 31;
	$e_month = 3;


	if($current_month >=4){
	$date = mktime(12, 0, 0, $s_month, $s_day, $current_year);
	$cdate = mktime(12, 0, 0, $e_month, $e_day, $current_year+1);
	}else{
	$date = mktime(12, 0, 0, $s_month, $s_day, $current_year-1);
	$cdate = mktime(12, 0, 0, $e_month, $e_day, $current_year);
	}
	$start_date = date("Y-m-d", $date);
	$ending_date = date("Y-m-d", $cdate);

	$product_name = "select product_name from product_mst where product_id=".$POST['product_id'];
	$product_row=mysqli_fetch_assoc($dbcon->query($product_name));
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
			<th>Sr.No</th>
			<th>Ref No.</th>
			<th>Ref Date</th>
			<th>Base Qty</th>
			<th>Base Unit</th>
			<th>Conv Qty</th>
			<th>Conv Unit</th>
			<th>Godown Name</th>
		</tr>';

		$query = "select stock.*,unit.unit_name as base_unit_name,cunit.unit_name as convert_unit_name,gd.gd_name from tbl_stock_trn as stock 
		left join unit_mst as unit on unit.unitid = stock.base_unit
		left join unit_mst as cunit on cunit.unitid = stock.convert_unit
		left join mst_godown as gd on gd.gd_id = stock.godown_id
		where stock.stock_status=0 and stock.ref_name != 'opening_stock' and stock.stock_date between '".date('Y-m-d',strtotime($start_date))."' and '".date('Y-m-d',strtotime($ending_date))."' and stock.company_id=".$_SESSION['company_id']." and stock.product_id=".$POST['product_id']." and MONTH(stock.stock_date) =".$POST['month'];

		$que = $dbcon->query($query);
		$cnt = brp_mysqli_num_rows($que);
		if($cnt>0){
			$i =1 ;$base_stock_in='';$conv_stock_in='';$base_stock_out='';$conv_stock_out='';
			while($row = brp_mysqli_fetch_array($que)){
				if($row['stock_flage'] == 1){
					$color = "green";
					$base_stock_in += $row['base_stock'];
					$conv_stock_in += $row['convert_stock'];
				}else{
					$color = "red";
					$base_stock_out += $row['base_stock'];
					$conv_stock_out += $row['convert_stock'];
				}
				if($row['ref_name']=='invoice_trn'){
					$tbl_name = 'tbl_invoicetrn';
					$tbl_main = 'tbl_invoice';
					$main_id  = 'invoice_id';
					$trn_id   = 'invoice_id';
					$tbl_id   = 'trancation_id';
					$ref_no   =  'invoice_no';
				}else if($row['ref_name']=='tbl_grn_trn'){
					$tbl_name = 'tbl_grn_trn';
					$tbl_main = 'tbl_grn';
					$main_id  = 'grn_id';
					$trn_id   = 'grn_id';
					$tbl_id   = 'grn_trn_id';
					$ref_no   = 'grn_no';
				}else if($row['ref_name']=='returning_receipt'){
					$tbl_name = 'tbl_returnable_channal_item';
					$tbl_main = 'tbl_returnable_channal';
					$main_id  = 'id';
					$trn_id   = 'returnable_id';
					$tbl_id   = 'id';
					$ref_no   = 'channal_id';
				}else if($row['ref_name']=='returnable'){
					$tbl_name = 'tbl_grn_trn';
					$tbl_main = 'tbl_grn';
					$main_id  = 'grn_id';
					$trn_id   = 'grn_id';
					$tbl_id   = 'grn_trn_id';
					$ref_no   = 'grn_no';
				}else{
					$tbl_name='';$tbl_main='';$main_id='';$tbl_id='';$ref_no='';
				}
				$ref_q ="select ref.".$ref_no." from ".$tbl_name." as reftrn 
				left join ".$tbl_main." as ref on ref.".$main_id."=reftrn.".$trn_id."
				where reftrn.".$tbl_id."=".$row['ref_id']; 
				$ref_e = $dbcon->query($ref_q);
				$ref_r = brp_mysqli_fetch_array($ref_e);
				$str .='<tr style="color:'.$color.'">
					<td>'.$i.'</td>
					<td>'.$ref_r[$ref_no].'</td>
					<td>'.date('d-m-Y',strtotime($row['stock_date'])).'</td>
					<td>'.number_format($row['base_stock'],4,".","").'</td>
					<td>'.$row['base_unit_name'].'</td>
					<td>'.number_format($row['convert_stock'],4,".","").'</td>
					<td>'.$row['convert_unit_name'].'</td>
					<td>'.$row['gd_name'].'</td>
				</tr>';
				
				
				$i++;
			}
			
			$str .= '<tr>
				<td colspan="3" style="text-align:right"><strong>Total</strong></td>
				<td colspan="2"><strong style="color:green">Inward Qty : '.number_format(abs($base_stock_in),4,".","").' </strong><br><strong style="color:red">Outward Qty : '.number_format(abs($base_stock_out),4,".","").'</strong></td>
				<td colspan="2"><strong style="color:green">'.number_format(abs($conv_stock_in),4,".","").'</strong><br><strong style="color:red">'.number_format(abs($conv_stock_out),4,".","").'</strong></td>
				<td></td>
			</tr>';
		}else{
			$str .='<tr>
				<td colspan="8" style="text-align:center"> No Data Yet...</td>
			</tr>';
		}
	
	$str.='</table>';
	echo $str;
}
?>