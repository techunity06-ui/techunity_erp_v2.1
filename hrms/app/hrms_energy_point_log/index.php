<?php
session_start();
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
include("../../../include/function_database_query.php");
include_once("../../../include/common_functions.php");
include_once("../../../include/hrms_common_functions.php");

		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}else{
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report") 
		{
			$energypoint = "";
			if(isset($_POST['energy_point']) && !empty($_POST['energy_point'])){
				$energypoint = "hrmsenergy.energy_point_log_status = '".$_POST['energy_point']."' and";
			}
			$companyID = $_SESSION['company_id'];
			$userID =  $_SESSION['user_id'];
			$set="select * from tbl_company where company_id=".$_SESSION['company_id'];
			$set_head=mysqli_fetch_assoc($dbcon->query($set));		
			$qrycust="select hrmsenergy.id,hrmsenergy.employee_id,comp.company_name,referdocument.reference_document_type_name,hrmsemplo.employee_name,hrmsenergy.* from hrms_energy_point_log as hrmsenergy 
					 left join hrms_employee as hrmsemplo on hrmsemplo.id = hrmsenergy.employee_id
					 left join tbl_company as comp on comp.company_id = hrmsenergy.company_id
					 left join hrms_reference_document_type as referdocument on referdocument.id = hrmsenergy.energy_reference_type_id where  $energypoint hrmsenergy.company_id = $companyID".check_user('hrmsenergy');
			$employee_rel = $dbcon->query($qrycust);		
			$str .='<table  class="display table table-bordered table-striped" id="data_list">
					<tr id="logo" class="logo" style="display:none">
						<td colspan="8" style="text-align:center;">
							<strong>'.$set_head['company_name'].'</strong>
						</td>
					</tr>
					<tr>
						<th width="5%" style="text-align:center">Sr. NO.</th>
						<th width="12%" style="text-align:center">Company</th>
						<th width="12%" style="text-align:center">User</th>
						<th width="12%" style="text-align:center">Energy Status</th>
						<th width="12%" style="text-align:center">Points</th>
						<th width="27%" style="text-align:center">Reference Document Type</th>
						<th width="12%" style="text-align:center">Status</th>
					</tr>
					<tbody>';
			$i=1;
			if(mysqli_num_rows($employee_rel)>0)
			{
				while($re=mysqli_fetch_assoc($employee_rel))
				{
					$str.='<tr>
					  	<td data-label="SR. NO." style="text-align:center">'.$i.'</td>
					  	<td data-label="Company Name" style="text-align:center">'.$re['company_name'].'</td>
						<td data-label="User" style="text-align:center">'.$re["employee_name"].'</td>';
					if($re['energy_point_log_status']=='auto'){
						$str.='<td data-label="Energy Status" style="text-align:center;">Auto</td>';
					}else if($re['energy_point_log_status']=='appreciation'){
						$str.='<td data-label="Energy Status" style="text-align:center;">Appreciation</td>';
					}else if($re['energy_point_log_status']=='criticism'){
						$str.='<td data-label="Energy Status" style="text-align:center;">Criticism</td>';
					}else if($re['energy_point_log_status']=='review'){
						$str.='<td data-label="Energy Status" style="text-align:center;">Review</td>';
					}else{
						$str.='<td data-label="Energy Status" style="text-align:center;">Revert</td>';
					}
					$str.='<td data-label="Points" style="text-align:center">'.$re["energy_points"].'</td>
						<td data-label="Reference Document Type" style="text-align:center">'.$re["reference_document_type_name"].'</td>';
					
					if($re['status']=='0'){
						$str.='<td data-label="STATUS" style="text-align:center;">Active</td>';
					}else if($re['status']=='1'){
						$str.='<td data-label="STATUS" style="text-align:center;">In Active</td>';
					}		
					$str.='</tr>';		
					$i++;
				}
			} else {
				$str .='<tr><td colspan="10" style="text-align:center">NO DATA FOUND</td></tr>';
			} 
			$str .='</tbody></table>'; 
			echo $str;
		}
		
   
?>