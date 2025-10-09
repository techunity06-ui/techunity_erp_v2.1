<?php
session_start();
$AJAX = true;

include('../../include/urlfileinner.php');
include($path . "config/image.php");
error_reporting(E_ALL);
$image = new SimpleImage();
if ($_POST != NULL) {
	$POST = bulk_filter($dbcon, $_POST);
} else {
	$POST = bulk_filter($dbcon, $_GET);
}
if (strtolower($POST['mode']) == "load_bom_costing_template_data") {
	$qry1 = "select * from  tbl_bom_costing_extra_rate where status =0 and bom_costing_id = ".$POST['bom_costing_id']; 
		$rs1=$dbcon->query($qry1);
		$x = 0;
		$costing_rate = $POST['costing_rate'];
		$grand_total = $POST['costing_rate'];
		if(brp_mysqli_num_rows($rs1) > 0){
			$arr_str = load_template_details($dbcon,$rs1,$costing_rate,$grand_total);
			$str .= $arr_str['str'];
			$grand_total = $arr_str['grand_total'];
		}
		
	$str .=get_grandtotal_costing($dbcon,$grand_total);
	echo $str;
}else if(brp_strtolower($POST['mode']) == "save_costing_data") {
	$bom_costing_id  =  $POST['bom_costing_id'];
	$grand_total = $POST['grand_total'];
	$arr_temp_name = $POST['temp_name'];
	$arr_value  =  $POST['value'];
	$arr_type = $POST['type'];  // 1 - plus , 2 - minus
	$arr_formula  =  $POST['formula'];  //  1- per, 2 - amount 
	$sp_id = $POST['sp_id'];

	$update_status['status'] = '2';
	$updateid11=update_record('tbl_workorder_costing_extra_rate', $update_status, "sp_id=".$sp_id, $dbcon);	
	for($i=0;$i<count($arr_temp_name);$i++){
		
		$info['sp_id'] = $sp_id;
		$info['type_name'] = $arr_temp_name[$i];
		if($arr_formula[$i] == '1'){
			$info['per'] = $arr_value[$i];
			$info['amount'] ='0';
		}else{
			$info['per'] = '0';
			$info['amount'] = $arr_value[$i];	
		}
		
		$info['type'] = $arr_type[$i];
		$info['status'] = 0;
		$info['user_id']		= $_SESSION['user_id'];
		$info['company_id']		= $_SESSION['company_id'];

		$reqinserid=add_record('tbl_workorder_costing_extra_rate', $info, $dbcon);
	}
	
	/*$update_costing['total_costing_rate'] = $grand_total;
	$update_costing['template_id'] = $bom_costing_template_id;
	$updateid11=update_record('tbl_bom_costing', $update_costing, "bom_costing_id=".$bom_costing_id, $dbcon);*/
}


function load_template_details($dbcon,$rs1,$costing_rate,$grand_total){
	$str='';
	$total_costing_value = $grand_total;
	$str .= '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
	$i=1;
		while($row2 = brp_mysqli_fetch_array($rs1)){
			$str .="<tr id=''>";
			
	  	$str.="<td width='80%' class='text-right tmp_typename'>".$row2['type_name']."</td>";
			if($row2['type'] == '0') { // 0 - plus | 1 - minus
				$plus = 0;

				if(!empty($row2['per']) && $row2['per'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' id='input_rate_".$i."' class='form-control input_rate' data-cal-type='1' value='".$row2['per']."' onkeyup='calculate_rate(".$i.",".$costing_rate.",1)'>%</td>";
					$plus =  ($costing_rate * $row2['per']) / 100;
				}else if(!empty($row2['amount']) && $row2['amount'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='".$row2['amount']."'></td>";
					$plus = $row2['amount'];
				}else{
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='0'></td>";
					$plus = 0;
				}
				$str.="<td width='10%' data-operation='0' class='input_temp_rate' id='txt_tmp_total_".$i."' style='color:green'>".$plus."</td>";

				$total_costing_value = $total_costing_value + $plus;
				
			}else{
				$minus = 0;

				if(!empty($row2['per']) && $row2['per'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='1' onkeyup='calculate_rate(".$i.",".$costing_rate.",1)' value='".$row2['per']."'>%</td>";
					$minus =  ($costing_rate * $row2['per']) / 100;
				}else if(!empty($row2['amount']) && $row2['amount'] > 0){
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='".$row2['amount']."'></td>";
					$minus = $row2['amount'];
				}else{
					$str.="<td width='10%' class='text-right'><input type='text' class='form-control input_rate' id='input_rate_".$i."' data-cal-type='2' onkeyup='calculate_rate(".$i.",".$costing_rate.",2)' value='0'></td>";
					$minus = 0;
				}
				$str.="<td width='10%' class='input_temp_rate' data-operation='1' id='txt_tmp_total_".$i."' style='color:red'>".$minus."</td>";

				$total_costing_value = $total_costing_value - $minus;

			}
			
			
			$str .="</tr>";
			$i++;
		}
		$str.="</table>";
		$arr['str'] = $str;
		$arr['grand_total'] = $total_costing_value;
		return $arr;
}


  function get_grandtotal_costing($dbcon,$grand_total){
	$str='';
	  	$str .= '<table width="100%" class="maintable table table-bordered table-responsive" style="font-size: 12px;">';
		$str.="<tr class='bg-info' style='font-size: 18px;font-weight: bold;'>";
		$str.="<td width='90%' class='text-right' id='total_costing_rate'>Total Workorder Costing Rate</td>";
		$str.="<td id='lbl_grand_total'>".$grand_total."</td>";
		$str.="</tr></table>";

		return $str;
	  }