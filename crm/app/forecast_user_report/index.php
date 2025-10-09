<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

$POST = ($_POST != NULL) ? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "load_forecast_report"){
	$str='';$left_join='';
	$company_data = get_company_data($dbcon,$_SESSION['company_id']);
	$companyConfiguration=getCompanyConfiguration($dbcon);
	$str.='<table class="display table table-bordered table-striped" style="width:100%;">
	<thead>
	<tr>
	<th class="text-center">Sr<br>No</th>
	<th class="text-center">Forecast No</th>
	<th class="text-center">Forecast Date</th>
	<th class="text-center">User Name</th>
	<th class="text-center">Branch Name</th>
	<th class="text-center">Forecast Type</th>
	<th class="text-center">From to To Date</th>';
	if($companyConfiguration['forecast_base']==3){
		$str.='<th class="text-center">Product Category</th>';
	}
	if($companyConfiguration['forecast_base']==2){
		$str.='<th class="text-center">Product Name</th>';
	}
	$str.='<th class="text-center">Target Amount</th>
	<th class="text-center">Target Quantity</th>
	<th class="text-center">Achieved Amount</th>
	<th class="text-center">Achieved Quantity</th>	  
	</tr>
	</thead>
	<tbody>';
	$left_join = "";
	$where = '';
	$variable = '';
	if(!empty($POST['financial_year_id'])){
		$where.= " AND futrn.financial_year_id = ".$POST['financial_year_id'];
	}
	if(!empty($POST['forecast_type'])){
		$where.= " AND futrn.forecast_type = ".$POST['forecast_type'];
	}
	if(!empty($POST['forecast_month'])){
		$where.= " AND futrn.forecast_month = ".$POST['forecast_month'];
	}
	if(!empty($POST['branch_id'])){
		$where.= " AND futrn.branch_id = ".$POST['branch_id'];
	}
	if(!empty($POST['f_user_id'])){
		$where.= " AND futrn.f_user_id = ".$POST['f_user_id'];
	}
	if(!empty($POST['f_product'])){
		if($companyConfiguration['forecast_base']==3){
			$variable = ', pro.product_name as product_name';
			$left_join=' LEFT JOIN product_mst AS pro ON pro.product_id = futrn.f_product';
		}else if($companyConfiguration['forecast_base']==2){
			$variable = ', cat.cat_name as product_name';
			$left_join=' LEFT JOIN tbl_category AS cat ON cat.cat_id = futrn.f_product';
		}
		$where.= " AND futrn.f_product = ".$POST['f_product'];
	}
	$qry = "SELECT futrn.*, fu.forecast_no, fu.forecast_date, fpm.f_period_name, bm.branch_name, user.user_name".$variable." FROM tbl_forecast_user_trn AS futrn LEFT JOIN tbl_forecast_user AS fu ON fu.forecast_user_id = futrn.forecast_usertable_id LEFT JOIN forecast_period_mst as fpm ON futrn.forecast_month = fpm.f_period_id LEFT JOIN branch_mst AS bm ON bm.branch_id = fu.branch_id LEFT JOIN users AS user ON user.user_id = fu.f_user_id".$left_join." WHERE futrn.status = 0 AND fu.forecast_status = 0 AND fu.company_id = ".$_SESSION['company_id'].$where." AND fu.forecast_base = ".$companyConfiguration['forecast_base']." ORDER BY fu.forecast_user_id ASC";
	$query = $dbcon->query($qry);
	if(brp_mysqli_num_rows($query)>0){
		$i = 1;
		while($res = brp_mysqli_fetch_assoc($query)){
			$row = get_for_target_amt_qty($dbcon,$res['f_user_id'],$res['forecast_start_date'],$res['forecast_end_date'],$res['f_product'], $res['forecast_base']);
			$str.='<tr>
			<td class="text-center">'.$i.'</td>
			<td class="text-center">'.$res['forecast_no'].'</td>
			<td class="text-center">'.date("d-M-Y", strtotime($res['forecast_date'])).'</td>
			<td class="text-center"><strong>'.$res['user_name'].'</strong></td>
			<td class="text-center">'.$res['branch_name'].'</td>
			<td class="text-center"><strong>'.get_for_target_p_name($dbcon,$res['forecast_type']).'</strong></td>
			<td class="text-center"><strong>'.date("d-M-Y", strtotime($res['forecast_start_date'])).' -<br>'.date("d-M-Y", strtotime($res['forecast_end_date'])).'</strong></td>';
			if($companyConfiguration['forecast_base']==3 || $companyConfiguration['forecast_base']==2){
				$str.='<td class="text-center">'.$res['product_name'].'</td>';
			}
			$str.='<td class="text-center"><strong>'.$res['target_amount'].'</strong></td>
			<td class="text-center"><strong>'.$res['target_qty'].'</strong></td>
			<td class="text-center"><span style="color: '.(($res['target_amount'] > $row['total']) ? 'red' : 'green').';">'.$row['total'].'</span></td>
			<td class="text-center"><span style="color: '.(($res['target_qty'] > $row['qty']) ? 'red' : 'green').';">'.$row['qty'].'</span></td>
			</tr>';
			$i++;
		}
	}else{
		$str.='<tr>
		<td colspan="12" class="text-center">No Data Found!!</td>
		</tr>';
	}
	$str.='</tbody>
	</table>';
	echo $str;
}

?>	