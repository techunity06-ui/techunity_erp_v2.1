<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
include("../../include/function_database_query.php");
/*if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')*/ 
{ 
    /*if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) */
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		if(strtolower($POST['mode']) == "generate_report") {
				$date=date('Y-m-d',strtotime($POST['date']));
				$time = date('H:i:s');

				//$date = $date.' '.$time;
				$date = $date;
				$resource_id=$POST['resource_id'];
				$_SESSION['dashboard_resource_id'] = $resource_id;

				$set_branch_id=$POST['branch_id'];
				$_SESSION['dashboard_branch_id'] = $set_branch_id;

				$where='';
				
				$str = '';
				if($resource_id!='' && $date!=''){

					if(!empty($POST['resource_id'])){
						$where .= ' and rs.resource_id = "'.$resource_id.'" ';
					}
					if(!empty($POST['date'])){
						$date_min = $date.' '.$time;
						//$where .= 'and "'.$date_min.'" between `rs`.`expected_start_date` and `rs`.`expected_end_date` ';
						//$where .=  " and '".$date."'  >=   `rs`.`expected_start_date`  and  '".$date."' <= `rs`.`expected_end_date`  ";
						//$where .= 'and `rs`.`expected_end_date` >=  "'.$date.'" ';
						$where .=  " and '".$date_min."'  >=   `rs`.`expected_start_date`  ";
					}

					$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
					$where_db = check_branch('rs', $branch_id);
					$where.=" $where_db and rs.company_id=".$_SESSION['company_id'];

					echo $sql = "select `rs`.*, `pm`.`product_type`, `tc`.`cat_name`, `pm`.`product_name`, `sp`.`po_req_no`, `pc`.`process_name`,pm.image_name from tbl_resource_schedule as rs 
							left join product_mst as pm on `rs`.`p_product_id` = `pm`.`product_id`
							left join tbl_category as tc on `pm`.`product_category`=`tc`.`cat_id` 
							left join tbl_set_main_process as sp on `rs`.`sp_id` = `sp`.`sp_id` 
							left join process_mst as pc on `rs`.`process_id` = `pc`.`process_id` 
							where `rs`.`work_status` IN (0,1,2)  $where  ";

					$resource_schedule_exec = $dbcon->query($sql);
					$resource_schedule_count = brp_mysqli_num_rows($resource_schedule_exec);		

					if($resource_schedule_count > 0){

						$i=1;
						$flag = true;
						while($resource_row=brp_mysqli_fetch_assoc($resource_schedule_exec)){

							$process_type = $resource_row['pr_process_type'];
							$process_id = $resource_row['process_id'];
							$pen_qty = $resource_row['pen_qty'];
							$p_product_id = $resource_row['p_product_id'];


							// Get Main Product Name Of Bom
							$request_main_product = get_main_requested_product_of_workorder($dbcon, $resource_row['sp_id'], $branch_id);

							// Get Process Name Based on ID
							$process_name = get_pro_type_name($resource_row['product_type']);


							// Time Managment
							$expected_end_date = date('Y-m-d', strtotime($resource_row['expected_end_date']));
							$expected_end_date = strtotime($expected_end_date);
							$today = strtotime($date);
							if($expected_end_date > $today){

							     $expected_start_date = strtotime($resource_row['expected_start_date']);
							     $expected_date = date('Y-m-d', $expected_start_date);

							     if($date==$expected_date){
							     	$first_time = date('H:i', $expected_start_date);
							     }else{
							     	 $resource_start_shift = strtotime(RESOURCE_START_SHIFT_TIME);
							     	 $first_time = date('H:i', $resource_start_shift);
							     }

							     $resource_end_shift = strtotime(RESOURCE_END_SHIFT_TIME);
							     $end_time = date('H:i', $resource_end_shift);

							     $working_time_need = $first_time .' - '. $end_time;
							}else{

								 $resource_start_shift = strtotime(RESOURCE_START_SHIFT_TIME);
							     $first_time = date('H:i', $resource_start_shift);

								 $expected_end_date = strtotime($resource_row['expected_end_date']);
							     $end_time = date('H:i', $expected_end_date);

								$working_time_need = $first_time .' - '. $end_time;
							}

							// Delay Managment

							$delay_hour = '';$button='';
							// Status & Button Data
							if($resource_row['work_status']=='0')
							{
								
								$work_status="<strong style='color:red'>Not Started</strong>";	
								if($flag==1){
									$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.'start_process/'.$resource_row['p_product_id'].'/'.$process_type.'/'.$process_id.'/resource'.'" ><i class="fa fa-plus"></i></a>';
									$flag = false;
								}
								
								$expected_start_date = $resource_row['expected_start_date'];
								$actual_start_date = $resource_row['actual_start_date'];

								$work_dalay_status = work_delay_calculate_work_status_0($dbcon,$actual_start_date, $expected_start_date);
								
								$pen_qty = get_resource_daily_qty($dbcon, $process_type, $process_id, $p_product_id, $date, $branch_id);


							}
							else if($resource_row['work_status']=='1')
							{
								$work_status="<strong style='color:green'>Started</strong>";
								if($resource_row['pr_process_type']==1){
									if($flag==1){
										$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process Quantity" data-toggle="tooltip" data-placement="top" href="'.ROOT.'end_process_allocation/'.$resource_row['p_product_id'].'/'.$process_type.'/'.$process_id.'" ><i class="fa fa-power-off"></i></a>';
										$flag = false;
									}
								}else{
									$button='';
								}

								//$pen_qty = $resource_row['process_qty'];
								$pen_qty = $resource_row['p_qty']-$resource_row['start_qty'];
								$work_dalay_status = "<strong style='color:red'>Working</strong>";
							}
							else if($resource_row['work_status']=='2')
							{
								$work_status="<strong style='color:red'>Process End</strong>";
								/*$button='<a class="btn btn-xs btn-success" data-original-title="Allocate Process" data-toggle="tooltip" data-placement="top" href="'.ROOT.'start_process/'.$resource_row['p_product_id'].'/'.$process_type.'/'.$process_id.'" ><i class="fa fa-plus"></i></a>';*/

								$expected_end_date = $resource_row['expected_end_date'];
								$actual_end_date = $resource_row['actual_end_date'];

								$work_dalay_status = work_delay_calculate_work_status_2($dbcon,$expected_end_date, $actual_end_date);
								$button="<strong style='color:red'>Done</strong>";
							}

							$cat_name = ($resource_row['cat_name']!=null) ? $resource_row['cat_name'] : 'PRIMARY';
							
							if($resource_row['image_name']!=null){
								//$image_name1 = '<a href="'.ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
								$image_name1 = '<img src="'.ROOT.'view/upload/product_images/'.$resource_row['image_name'].'" style="width: 60px;height: 50px;">';
							}else{
								$image_name1 = '';
							}
							$str.='<tr>
								  <td style="text-align:center;">'.$i.'</td>
								  <td>'.$resource_row["product_name"].'</td>
								  <td>'.$image_name1.'</td>
								  <td>'.$cat_name.'</td>
								  <td>'.$process_name.'</td>
								  <td>'.$resource_row["process_name"].'</td>
								  <td>'.$resource_row["po_req_no"].'</td>
								  <td>'.$resource_row["job_card_number"].'</td>
								  <td>'.$resource_row["p_qty"].'</td>
								  <td>'.$pen_qty.'</td>
								  <td>'.$request_main_product["product_name"].'</td>
								  <td>'.$working_time_need.'</td>
								  <td>'.$work_dalay_status .' </td>
								  <td>'.$work_status.'</td>
								  <td style="text-align:center;">'.$button.'</td>
								</tr>';

							$i++;		
						}
					}else{
						$str.='<tr>
							  <td style="text-align:center;" colspan="14">DATA NOT EXISTS.</td>
						   </tr>';
					}
				}else{
					$str.='<tr>
							  <td style="text-align:center;" colspan="14">PLEASE SELECT REQUIRED FIELD.</td>
						   </tr>';
				}
				echo $str;
		}
		else if(strtolower($POST['mode'])== "fetch_resource_based_on_branch")
		{
			$branch_id = $POST['branch_id'];
			$resource_id = $POST['resource_id'];
			$data['vendor_id'] = get_resource_work_list($dbcon,$resource_id,$branch_id);
			echo json_encode($data);
		}	
	}
}

function get_main_requested_product_of_workorder($dbcon,$sp_id, $branch_id=0){

	$where_db = check_branch('rp', $branch_id);
	$where=" $where_db and rp.company_id=".$_SESSION['company_id'];

	$main_sql = "select `pm`.`product_name` from tbl_request_product as rp 
			left join product_mst as pm on `rp`.`rp_pid` = `pm`.`product_id` 
			where `rp`.`main_request`='1' and `rp`.`sp_id` = '".$sp_id."' $where ";

	$main_req_pro_exec = $dbcon->query($main_sql);
	$main_req_pro_row = brp_mysqli_fetch_assoc($main_req_pro_exec);

	return $main_req_pro_row;
}

function work_delay_calculate_work_status_0($dbcon,$actual_start_date, $expected_start_date){
		$expected_start_date = strtotime($expected_start_date);
		if($actual_start_date=='0000-00-00 00:00:00'){
			 $actual_start_date = strtotime(date('Y-m-d H:i:s'));
		}else{
			$actual_start_date = strtotime($actual_start_date);
		}
		
		$delay_hour = ($expected_start_date - $actual_start_date)/(60*60);
		if($delay_hour > 0){
			$delay_hour = abs($delay_hour);
			$delay_hour = round($delay_hour,2);
			$time_unit = 'Early';

			$work_dalay_status="<strong style='color:green'>".$delay_hour." hours ".$time_unit."</strong>";
		}elseif($delay_hour < 0){
			$delay_hour = abs($delay_hour);
			$delay_hour = round($delay_hour,2);

			$time_unit = 'Delay';
			$work_dalay_status="<strong style='color:red'>".$delay_hour." hours ".$time_unit."</strong>";
		}elseif($delay_hour == 0){
			$delay_hour = abs($delay_hour);
			$delay_hour = round($delay_hour,2);

			$work_dalay_status="<strong style='color:green'>On Time</strong>";
		}

		return $work_dalay_status;
}

function work_delay_calculate_work_status_2($dbcon,$expected_end_date, $actual_end_date){
	$expected_end_date = strtotime($expected_end_date);
	$actual_end_date = strtotime($actual_end_date);

	$delay_hour = ($expected_end_date - $actual_end_date)/(60*60);
	if($delay_hour > 0){
		$delay_hour = abs($delay_hour);
		$delay_hour = round($delay_hour,2);
		$time_unit = 'Early';

		$work_dalay_status="<strong style='color:green'>".$delay_hour." hours ".$time_unit."</strong>";
	}elseif($delay_hour < 0){
		$delay_hour = abs($delay_hour);
		$delay_hour = round($delay_hour,2);
		$time_unit = 'Delay';
		$work_dalay_status="<strong style='color:red'>".$delay_hour." hours ".$time_unit."</strong>";
	}elseif($delay_hour == 0){
		$delay_hour = abs($delay_hour);
		$delay_hour = round($delay_hour,2);
		$work_dalay_status="<strong style='color:green'>On Time</strong>";
	}

	return $work_dalay_status;
}


?>