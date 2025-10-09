<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
}

else if(strtolower($POST['mode']) == "generate_stage_report")
{
	$cust_id=$POST['cust_id'];
	$product_id=$POST['product_id'];

	$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
	$set_head=mysqli_fetch_assoc($dbcon->query($set));		
	$qrycust="select * from tbl_customer where cust_id=".$POST['cust_id'];
	$cust_rel=mysqli_fetch_assoc($dbcon->query($qrycust));		
	$str .='
	<table  width="100%" class="display">
	</table>
	<table  class="" id="data_list">
	<tr id="logo" class="logo" style="display:none">
	<td colspan="8" style="text-align:center;">
	<strong>'.$set_head['company_name'].'</strong>
	</td>
	</tr>
	<tr style="border-bottom:0.5px #000 solid;">
	<td colspan="7">
	<strong>[ Production Jobs  ]</strong>
	</td>
	</tr>';
	//<th width="5%" style="text-align:center">Current Stage</th>
   	$i=1;
	$total=0;
	$str.='<tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
			<th width="5%" style="text-align:center">NO.</th>
			<th width="20%" style="text-align:center">Sales Order No</th>
			<th width="20%" style="text-align:center">Product</th>
			<th width="20%" style="text-align:center">Product Code</th>
			<th width="5%" style="text-align:center">Customer Name</th>
			
			<th width="5%" style="text-align:center">Unit</th>
			<th width="5%" style="text-align:center">Progress</th>
			</tr>';
			
			$p_bill_data=sostagereportdata($dbcon,$POST);
			$j=1;
			$k=0;
			if(count($p_bill_data)>0)
			{
				foreach ($p_bill_data as $key => $re) {
					$comp_per=get_stage_completed_per($dbcon,$re["sales_order_id"],$re["product_id"]);

					$current_stage=getcurrentstage($dbcon,$re["sales_order_id"],$re["product_id"]);
					//<td style="text-align:center">'.$current_stage.'</td>
					// echo "<pre>";<td style="text-align:center">'.$getcurrentstage($dbcon,$re["sales_order_id"],$re["product_id"]).'</td>
					// print_r($comp_per);

					$str_data='<tr style="border: 1px dashed #cccccc;">
					<td style="text-align:center">'.$j.'</td>
					<td style="text-align:center">'.$re["sales_order_no"].'</td>
					<td style="text-align:center">'.$re["product_name"].'</td>
					<td style="text-align:center">'.$re["product_hsn"].'</td>
					<td style="text-align:center">'.$re["l_name"].'</td>
					
					
					<td style="text-align:center">'.$re["unit_name"].'</td>';

					$str_data.= '<td style="text-align:center"><div class="progress progress-striped active progress-md">
                                  <div class="progress-bar progress-bar-danger"  role="progressbar" aria-valuenow="'.$comp_per.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$comp_per.'%">
                                      <span style="color: green;">'.$comp_per.'% Complete</span>
                                  </div>
                              </div></td></tr>';

					if(strtolower($POST['mode_type'])=='pending'){
						if($comp_per<100){
							$str.=$str_data;
							$k=1;
							$j++;
						}
					}
					if(strtolower($POST['mode_type'])=='completed'){
						if($comp_per>=100){
							$str.=$str_data;
							$k=1;
							$j++;
						}
					}
					
					if(strtolower($POST['mode_type'])=='all'){
						$str.=$str_data;
						$k=1;
						$j++;
					}
				}
				if($k==0){
					$str .='<tr>
				<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
				}
				
			}else{
				$str .='<tr>
				<td colspan="7" style="text-align:center">NO DATA FOUND  </td>
				</tr>';
			}
	$str .='				 
	</table>';
	echo $str;
}
?>