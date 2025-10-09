<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once("../../include/common_functions/common_functions.php");
							
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
		}

		else if(strtolower($POST['mode']) == "add") {
		}

		else if(strtolower($POST['mode']) == "resource_transfer") {
			$resource_allocate_id = $POST['eid'];
			$branch_id = $POST['branch_id'];


			$sql = "SELECT * FROM tbl_work_order_resource_allocate WHERE resource_allocate_id='".$resource_allocate_id."' AND company_id='".$_SESSION['company_id']."' AND resourse_allocation_status=0 ";
			$rs_order=$dbcon->query($sql);
			$rel=brp_mysqli_fetch_assoc($rs_order); 

			$resource_id = $POST['new_resource_id'];
			$request_id = $rel['request_id'];
			$process_id = $rel['process_id'];
			$product_id = $rel['product_id'];
			$qty = $POST['transfer_qty'];
			$time_per_qty = $rel['time_per_qty'];
			$completed_time = '';
			$action_type = 'add';
			$edit_id=$resource_allocate_id;
			$existing_qty = $POST['qty'];

			/*	
				Code By Umair: 16/12/2020
				Comment: Update tbl_resource_schedule
			*/
			update_existing_resource_schedule($dbcon, $POST['process_id'], $POST['work_order_id'],$POST['request_id'], $time_per_qty, $qty, $existing_qty, $branch_id);

			/*	
				Code By Umair: 16/12/2020
				Comment: Add New record based on the resource tbl_resource_schedule
			*/

			add_new_resource_schedule($dbcon, $POST['process_id'], $POST['work_order_id'],$POST['request_id'], $time_per_qty, $qty, $POST['new_resource_id'], $branch_id);	
			
			

			/*
				Comment: Transfer qty to another resource. tbl_work_order_resource_allocate this table is affected.
			*/

			$insertid = work_order_resource_allocate($dbcon, $resource_id, $request_id, $process_id, $product_id, $qty, $time_per_qty, $edit_id, $action_type, $completed_time, $branch_id);

			/*
				Comment: Transfer qty to another resource. tbl_allocate_process this table is affected.
			*/
			allocate_process_transfer($dbcon, $POST['resource_id'], $POST['new_resource_id'], $POST['request_id'],  $POST['process_id'], $POST['transfer_qty'],  $edit_id=null, $action_type, $branch_id);
			
			/*
				Comment: Insert Work Order Resource Transfer. Insert Log In tbl_resource_allocation_transfer table
			*/
			if($insertid){
				$resource_id_by = $POST['resource_id'];
				$resource_id_to = $POST['new_resource_id']; 
				$resource_transfer_allocate_id = $resource_allocate_id;
				$work_order_id = $POST['work_order_id'];

				work_order_resource_transfer($dbcon, $resource_id_by, $resource_id_to, $process_id, $product_id, $qty, $resource_transfer_allocate_id, $work_order_id, $branch_id);

				$update_resource['qty'] = $rel['qty']-$POST['transfer_qty'];
				$update_resource['total_time'] = ($rel['time_per_qty']*$update_resource['qty']);
				$update_resource['muser_id'] = $_SESSION['user_id'];
				$update_resource['mdate'] = date('Y-m-d H:i:s');

				update_record('tbl_work_order_resource_allocate', $update_resource,"resource_allocate_id = '".$resource_allocate_id."'" , $dbcon, $branch_id);

				$arr['msg'] = '1';
			}else{
				$arr['msg'] = '0';
			}

			echo json_encode($arr);
		}		
		
		else if(strtolower($POST['mode']) == "get_workorder_list") {
			$resourceid = $POST['resourceid'];
			$branch_id = $POST['branch_id'];
			$sql = "SELECT `wra`.`resource_allocate_id`, `wra`.`request_id`, `rp`.`sp_id`, `smp`.`po_req_no` 
					FROM `tbl_work_order_resource_allocate` as wra 
					LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
					LEFT JOIN tbl_set_main_process as smp ON `rp`.`sp_id` = `smp`.`sp_id`
					WHERE `wra`.`resource_id` = '".$resourceid."' AND `smp`.`company_id`='".$_SESSION['company_id']."' AND `wra`.`company_id`='".$_SESSION['company_id']."' and `wra`.`branch_id`='".$branch_id."' GROUP  BY `smp`.`po_req_no`";

			$rs_order=$dbcon->query($sql);
			$cnt=brp_mysqli_num_rows($rs_order);
			$str='';
			if($cnt>0){ 
				$str.='<option value="">--Select Work Order--</option>';
				while($rel=brp_mysqli_fetch_assoc($rs_order))
				{
					$sel='';
					$str .= '<option '.$sel.' value="'.$rel['sp_id'].'">'.$rel['po_req_no'].'</option>';
				}
			}else{
				$str.='<option value="">NO DATA FOUND</option>';
			}

			$arr['work_order_id'] = $str;
			echo json_encode($arr);		
		}

		else if(strtolower($POST['mode']) == "get_process_list") {
			$workorderid = $POST['workorderid'];
			$resource_id = $POST['resource_id'];
			/*$sql = "SELECT `rp`.`rp_id`,`rp`.`sp_id`,`wra`.`process_id`,`pm`.`process_name` FROM `tbl_request_product` as rp 
					LEFT JOIN tbl_work_order_resource_allocate as wra ON `rp`.`rp_id` = `wra`.`request_id`
					LEFT JOIN process_mst as pm ON `wra`.`process_id` = `pm`.`process_id`
					WHERE  `rp`.`sp_id` = '".$workorderid."' AND `wra`.`process_id` IS NOT NULL";*/

						
			$sql = "SELECT `rp`.`rp_id`,`rp`.`sp_id`,`wra`.`process_id`,`pm`.`process_name`  FROM `tbl_request_product` as rp
					LEFT JOIN tbl_work_order_resource_allocate as wra ON `wra`.`request_id` = `rp`.`rp_id`
					LEFT JOIN process_mst as pm ON `wra`.`process_id` = `pm`.`process_id`
					WHERE `rp`.`sp_id` = '".$workorderid."' AND `wra`.`resource_id`= '".$resource_id."' AND `wra`.`resourse_allocation_status`=0 AND `wra`.`qty`!=0 AND `wra`.`company_id`= '".$_SESSION['company_id']."' ";
		
			$rs_order=$dbcon->query($sql);
			$cnt=brp_mysqli_num_rows($rs_order);
			$str='';
			if($cnt>0){ 
				$str.='<option value="">--Select Process--</option>';
				while($rel=brp_mysqli_fetch_assoc($rs_order))
				{
					$sel='';
					$str .= '<option '.$sel.' value="'.$rel['process_id'].'" data-requestid="'.$rel['rp_id'].'">'.$rel['process_name'].' - ( Request No.'.$rel['rp_id'].' )</option>';
				}
			}else{
				$str.='<option value="">NO DATA FOUND</option>';
			}

			$arr['process_id'] = $str;
			echo json_encode($arr);			
		}

		else if(strtolower($POST['mode']) == "get_request_info") {
			$processid = $POST['processid'];
			$resource_id = $POST['resource_id'];
			$request_id = $POST['request_id'];
			$branch_id = $POST['branch_id'];

			/* $sql = "SELECT wors.* FROM tbl_work_order_resource_allocate as wors
					LEFT JOIN tbl_allocate_process as ap ON `ap`.`p_ref_id` = `wors`.`request_id`
					WHERE `wors`.`resource_id` = '".$resource_id."' AND `wors`.`request_id` = '".$request_id."' AND `wors`.`process_id` = '".$processid."' AND `wors`.`company_id` = '".$_SESSION['company_id']."' AND `wors`.`resourse_allocation_status` = 0 and `ap`.`p_status` = 0 and wors.branch_id = '".$branch_id."' "; */
					
			$sql = "SELECT wors.* FROM tbl_resource_schedule as wors
					WHERE `wors`.`resource_id` = '".$resource_id."' AND `wors`.`rp_id` = '".$request_id."' AND `wors`.`process_id` = '".$processid."' AND `wors`.`company_id` = '".$_SESSION['company_id']."' AND `wors`.`work_status` = 0 and wors.branch_id = '".$branch_id."' ";
					
			$rs_order=$dbcon->query($sql);
			$cnt=brp_mysqli_num_rows($rs_order);
			if($cnt>0){ 
				$rel=brp_mysqli_fetch_assoc($rs_order);
				$arr['msg'] = '1';

				$arr['qty'] = $rel['pen_qty'];
				$arr['request_id'] = $rel['rp_id'];
				$arr['resource_allocate_id'] = $rel['resource_schedule_id'];

				$where = "resource_id!='".$resource_id."'";
				$arr['new_resource_id'] = get_all_resource($dbcon,'', $where, $branch_id);

			}else{
				$arr['msg'] = '0';
			}
			echo json_encode($arr);			
		}


    }
}


function update_existing_resource_schedule($dbcon, $process_id, $sp_id, $rp_id, $time_per_qty, $qty, $existing_qty, $branch_id){
			/*
				Code By Umair: 16/12/2020
				Comment : Update tbl_resource_schedule
			*/
			$res_sche_sql = 'select * from tbl_resource_schedule where process_id = "'.$process_id.'" and sp_id = "'.$sp_id.'" and rp_id = "'.$rp_id.'" and work_status in (0,1) and company_id = "'.$_SESSION['company_id'].'" and branch_id = "'.$branch_id.'" ';
			$res_sche_exec=$dbcon->query($res_sche_sql);
			$res_sche_data=brp_mysqli_fetch_assoc($res_sche_exec);
			
			$p_qty = $res_sche_data['p_qty'];
			$pen_qty = $res_sche_data['pen_qty'];
			$resource_schedule_id = $res_sche_data['resource_schedule_id'];

			$update_p_qty = $p_qty - $qty;
			$update_pen_qty = $pen_qty - $qty;

			$total_p_qty_in_min = $update_p_qty*$time_per_qty;
			$total_p_qty_in_hour = number_format($total_p_qty_in_min/60, 2, '.', '');

			$start_shift_time = RESOURCE_START_SHIFT_TIME;
			$end_shift_time = RESOURCE_END_SHIFT_TIME;

			$start_shift = strtotime($start_shift_time);
			$end_shift = strtotime($end_shift_time);

			$total_work_time = $end_shift - $start_shift;
			$total_work_time = $total_work_time / ( 60 * 60 );

			$working_time = $total_p_qty_in_hour;

			$start_time = strtotime(date('Y-m-d H:i:s'));
			$remaining_time = ($end_shift - $start_time) / ( 60 * 60 );
			$remaining_time = number_format((float)$remaining_time, 2, '.', '');


			$completd_date = calculate_next_date($remaining_time, $working_time, $start_time, $total_work_time, $start_shift_time);

			$update_resource_schedule['p_qty'] = $update_p_qty;
			$update_resource_schedule['pen_qty'] = $update_pen_qty;
			$update_resource_schedule['total_hour'] = $total_p_qty_in_hour;
			$update_resource_schedule['expected_end_date'] = $completd_date;

			$update_resource_schedule['muser_id'] = $_SESSION['user_id'];
			$update_resource_schedule['mdate'] = date('Y-m-d H:i:s');

			if($existing_qty==$qty){
				$update_resource_schedule['work_status'] = 3;
			}

			update_record('tbl_resource_schedule', $update_resource_schedule,"resource_schedule_id = '".$resource_schedule_id."'" , $dbcon, $branch_id);
}

function add_new_resource_schedule($dbcon, $process_id, $sp_id, $rp_id, $time_per_qty, $qty, $new_resource_id, $branch_id){
	 $res_sche_sql = 'select * from tbl_resource_schedule where process_id = "'.$process_id.'" and sp_id = "'.$sp_id.'" and rp_id = "'.$rp_id.'" and work_status in (0,1) and  company_id = "'.$_SESSION['company_id'].'" ';
	$res_sche_exec=$dbcon->query($res_sche_sql);
	$res_sche_data=brp_mysqli_fetch_assoc($res_sche_exec);

	$total_p_qty_in_min = $qty*$time_per_qty;
	$total_p_qty_in_hour = number_format($total_p_qty_in_min/60, 2, '.', '');


	// Check resource last data and time work
	$last_date_time_of_resource = resource_finish_work_date($dbcon,$new_resource_id, $branch_id);
	$last_date_time_of_resource = json_decode($last_date_time_of_resource, true);


	$last_date = $last_date_time_of_resource['last_date'];
	$working_hours = $last_date_time_of_resource['resource_working_hour'];


	// Get Calcualte Total Hours
	$previous_hour_info = get_resource_total_hour_based_on_id($dbcon,$new_resource_id, $branch_id);
	
	$previous_hour = $previous_hour_info;
	$total_hours_of_res = $previous_hour + $total_p_qty_in_hour; 

	// Get First Expected Date
	$first_expected_date_of_reso = get_resource_first_expected_date($dbcon,$new_resource_id, $branch_id);
	$first_date = $first_expected_date_of_reso;

	$start_shift_time = RESOURCE_START_SHIFT_TIME;
	$end_shift_time = RESOURCE_END_SHIFT_TIME;

	$start_shift = strtotime($start_shift_time);
	$end_shift = strtotime($end_shift_time);

	$total_work_time = $end_shift - $start_shift;
	$total_work_time = $total_work_time / ( 60 * 60 );

	$working_time = $total_hours_of_res;

	$start_time = strtotime(date('Y-m-d H:i:s'));
	$remaining_time = ($end_shift - $start_time) / ( 60 * 60 );
	$remaining_time = number_format((float)$remaining_time, 2, '.', '');

	$completd_date = calculate_next_date($remaining_time, $working_time, $start_time, $total_work_time, $start_shift_time);

	$saveresource_sch['resource_id'] = $new_resource_id;
	$saveresource_sch['process_id'] = $res_sche_data['process_id'];
	$saveresource_sch['sp_id'] = $res_sche_data['sp_id'];
	$saveresource_sch['rp_id'] = $res_sche_data['rp_id'];
	$saveresource_sch['job_card_number'] = $res_sche_data['job_card_number'];
	$saveresource_sch['p_qty'] = $qty;
	$saveresource_sch['pen_qty'] = $qty;
	$saveresource_sch['total_hour'] = $total_hours_of_res;
	$saveresource_sch['expected_start_date'] = $last_date;
	$saveresource_sch['expected_end_date'] = $completd_date;
	$saveresource_sch['work_status'] = 0;

	$saveresource_sch['p_product_id'] = $res_sche_data['p_product_id'];
	$saveresource_sch['pr_process_type'] = $res_sche_data['pr_process_type'];
	$saveresource_sch['process_unit'] = $res_sche_data['process_unit'];
	$saveresource_sch['user_id'] = $_SESSION['user_id'];
	$saveresource_sch['cdate'] = date('Y-m-d H:i:s');
	$saveresource_sch['company_id'] = $_SESSION['company_id'];

	add_record('tbl_resource_schedule', $saveresource_sch, $dbcon, $branch_id);
}

?>