<?
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");
include_once("../../include/common_functions/common_production_functions.php");
include_once(COMMON_FUNCTION_PATH."common_production_store_wise_function.php");
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}


if(strtolower($POST['mode']) == "main_cat") {
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$frmdate=date('Y-m-d',strtotime($_SESSION['start']));
	$todate=date('Y-m-d',strtotime($_SESSION['end']));
	
	$product_type = $POST['id'];

	if(!empty($product_type)){
	     $where .=  " and product_type_id = " .$product_type;
	}
	$query = "select * from pro_ms_product_type where product_type_status=0 ".$where;
	$execu = $dbcon->query($query);
	//$header =get_header($dbcon,'text-align: center','100%','70px');
	$str .= "<table class='table table-bordered' style='width:100%;margin-top:30px'>
		<tr>
			<th>Sr. No.</th>
			<th>Product Type Name</th>
			<th>Opening Stock</th>
			<th>Closing Stock</th>
			<th>Opening Rate</th>
			<th>Closing Rate</th>
		</tr>";
	if(brp_mysqli_num_rows($execu)>0){
		$i=1;		
		while($row = brp_mysqli_fetch_array($execu)){
			// $cat_r = category_recusive($dbcon,$row['product_type_id'],$frmdate,$todate);
			$cat_r = product_type_category_wise_stock($dbcon,$row['product_type_id'],$frmdate,$todate);
			//exit;
			/*$cat_stock = category_wise_stock($dbcon,$row['cat_id'],$frmdate,$todate);*/
			$str .='<tr>
				<td><a data-original-title="Sub Category Report" data-toggle="tooltip" data-placement="top" class="noprint" href="'.ROOT.'pro_sub_cat/'.$row['product_type_id'].'">'.$i.'</a> <span class="printshow" style="display: none;">'.$i.'</span></td>
				<td><a data-original-title="Sub Category Report" data-toggle="tooltip" data-placement="top" class="noprint" href="'.ROOT.'pro_sub_cat/'.$row['product_type_id'].'">'.$row['product_type_name'].'</a> <span class="printshow" style="display: none;">'.$row['product_type_name'].'</span></td>
				<td><a data-original-title="Sub Category Report" data-toggle="tooltip" data-placement="top" class="noprint" href="'.ROOT.'pro_sub_cat/'.$row['product_type_id'].'"><strong style="color:green">'.$cat_r['base_opn_stock'].'</strong><br><strong style="color:orange">'.$cat_r['conv_opn_stock'].'</strong></a><span class="printshow" style="display: none;"><strong style="color:green">'.$cat_r['base_opn_stock'].'</strong><br><strong style="color:orange">'.$cat_r['conv_opn_stock'].'</strong></span></td>
				<td><a data-original-title="Sub Category Report" data-toggle="tooltip" data-placement="top" class="noprint" href="'.ROOT.'pro_sub_cat/'.$row['product_type_id'].'"><strong style="color:green">'.$cat_r['base_closing_stock'].'</strong><br><strong style="color:orange">'.$cat_r['conv_closing_stock'].'</strong></a> <span class="printshow" style="display: none;"><strong style="color:green">'.$cat_r['base_closing_stock'].'</strong><br><strong style="color:orange">'.$cat_r['conv_closing_stock'].'</strong></span></td>
				<td><a data-original-title="Sub Category Report" data-toggle="tooltip" data-placement="top" class="noprint" href="'.ROOT.'pro_sub_cat/'.$row['product_type_id'].'"><strong style="color:green">'.$cat_r['base_opn_rate'].'</strong><br><strong style="color:orange">'.$cat_r['conv_opn_rate'].'</strong></a><span class="printshow" style="display: none;"><strong style="color:green">'.$cat_r['base_opn_rate'].'</strong><br><strong style="color:orange">'.$cat_r['conv_opn_rate'].'</strong></span></td>
				<td><a data-original-title="Sub Category Report" data-toggle="tooltip" data-placement="top" class="noprint" href="'.ROOT.'pro_sub_cat/'.$row['product_type_id'].'"><strong style="color:green">'.$cat_r['base_closing_rate'].'</strong><br><strong style="color:orange">'.$cat_r['conv_closing_rate'].'</strong></a> <span class="printshow" style="display: none;"><strong style="color:green">'.$cat_r['base_closing_rate'].'</strong><br><strong style="color:orange">'.$cat_r['conv_closing_rate'].'</strong></span></td>
			</tr>';
			$i++;
		}
	}else{
		$str .= "<tr>
			<td colspan='5' style='text-align:center'>No Data Yet..!!</td>
		</tr>";
	}
	$str .="</table>";
	echo $str;
}
else if(strtolower($POST['mode']) == "pro_sub_cat")
{		
	$s_date=explode(' - ',$POST['date']);
	$_SESSION['start']=$s_date[0];
	$_SESSION['end']=$s_date[1];
	$frmdate=date('Y-m-d',strtotime($_SESSION['start']));
	$todate=date('Y-m-d',strtotime($_SESSION['end']));

	$parent_id = $POST['id'];
	$query = "select * from tbl_category where cat_pid=".$parent_id." and cat_status=0";
	$execu = $dbcon->query($query);
	$cat_pro = product_type_wise_pro_stock($dbcon,$parent_id,$frmdate,$todate,$stock="");
		$str .= '<table class="table table-bordered" style="width:100%;margin-top:30px">
			<tr>
				<td colspan="6" style="text-align:center"><strong>Product Type Name : '.get_product_type_name($dbcon,$parent_id).'</strong></td>
			</tr>
			<tr>
				<th>Sr. No.</th>
				<th>Product Name</th>
				<th>Opening Stock</th>
				<th>Closing Stock</th>
				<th>Base Rate</th>
				<th>Conv Rate</th>
				<th>Min</th>
				<th>Max</th>
			</tr>';
		$i=1;	
		if(brp_mysqli_num_rows($cat_pro)>0){
			$i=1;
			while($row = brp_mysqli_fetch_array($cat_pro)){
				
				$base_opening_stock = $row['opening_stock'] + $row['plus_opening_stock'] - $row['minus_opening_stock'];
				$conv_opening_stock = $row['conv_opening_stock'] + $row['plus_conv_opening_stock'] - $row['minus_conv_opening_stock']; 
				$base_closing_stock = $base_opening_stock + $row['closing_stock_plus'] - $row['closing_stock_minus'];
				$conv_closing_stock = $conv_opening_stock + $row['conv_closing_stock_plus'] - $row['conv_closing_stock_minus'];
				
				$str.='<tr>
					<td>'.$i.'</td>
					<td>'.$row['product_name'].'</td>
					<td><strong style="color:green">'.$base_opening_stock.' '.$row['baseunit'].'</strong><br><strong style="color:orange">'.$conv_opening_stock.' '.$row['conv_unit'].'</strong></td>
					<td><strong style="color:green">'.$base_closing_stock.' '.$row['baseunit'].'</strong><br><strong style="color:orange">'.$conv_closing_stock.' '.$row['conv_unit'].'</strong></td>
					<td>'.$row['base_rate'].'</td>
					<td>'.$row['conv_rate'].'</td>
					<td>'.$row['product_min_stock'].'</td>
					<td>'.$row['product_max_stock'].'</td>
				</tr>';
				$i++;	
			}
		}else{
			$str.='<tr>
				<td colspan="6" style="text-align:center">No Product Yet...!!!</td>
			</tr>';
		}	
		$str .= '<input type="hidden" name="pro_m" id="pro_m" val="1">';	
		
	
	echo $str;
}
?>