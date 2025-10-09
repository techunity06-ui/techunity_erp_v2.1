<?php
session_start();
$AJAX = true;
include("../../config/config.php");
// error_reporting(E_ALL);
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
		
		/*
		Below code is depend on the tbl_resource_schedule table. Now this function is not an use.s 
		*/
		if(strtolower($POST['mode']) == "edit_resorce_schedule") {
			$resource_id = $POST['resource_id'];
			/*$sql = "SELECT wra.*,`p`.`product_name`,`r`.`resource_name`, `rp`.`sp_id`, (SELECT po_req_no FROM tbl_set_main_process WHERE sp_id=`rp`.`sp_id`) as work_order_no, `proc`.`process_name`, `ressch`.`expected_end_date`  FROM `tbl_work_order_resource_allocate` as wra 
			    LEFT JOIN product_mst as p ON `wra`.`product_id` = `p`.`product_id`
			    LEFT JOIN tbl_resource as r ON `wra`.`resource_id` = `r`.`resource_id`
			    LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
			    LEFT JOIN process_mst as proc ON `wra`.`process_id` = `proc`.`process_id`
			    INNER JOIN tbl_resource_schedule as ressch ON `wra`.`request_id` = `ressch`.`rp_id`
			    WHERE  `wra`.`resource_id`='".$resource_id."'  AND `wra`.`resourse_allocation_status`=0 AND `wra`.`company_id`='".$_SESSION['company_id']."' AND `wra`.`qty`!=0 ORDER BY `wra`.`resource_allocate_id`";*/

			$sql = "select rs.*, (SELECT po_req_no FROM tbl_set_main_process as sm WHERE sm.sp_id=`rs`.`sp_id`) as work_order_no, `p`.`product_name`,`proc`.`process_name`,pp.process_time,p.image_name from tbl_resource_schedule as rs 
							left join product_mst as p ON `rs`.`p_product_id` = `p`.`product_id` 
							left join process_mst as proc ON `rs`.`process_id` = `proc`.`process_id` 
							left join tbl_product_process as pp on pp.product_id=`rs`.`p_product_id` and pp.process_id=`rs`.`process_id`
							where pp.status=0 and rs.resource_id = '".$resource_id."' and `rs`.`company_id`='".$_SESSION['company_id']."' order by rs.resource_schedule_id ";    
			$result=$dbcon->query($sql);

			$where = 'resource_id="'.$resource_id.'"'; 
			$resource_info = get_resource_info_by_id($dbcon, $where);

				$str = '';
				$str .= '<table style="" id="" width="100%" border="0">
                                      <tbody><tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
                                        <td style="text-align:center" width="90%"> 
                                          <strong style="font-size:16px">
                                           '.$resource_info["resource_name"].'
                                          </strong>
                                        </td>
                                        <td style="text-align:center" width="10%"> 
                                          <strong style="font-size:12px">
                                            <b class="data_title"></b>
                                          </strong>
                                        </td>
                                      </tr>
                                    </tbody>
                                </table>';

                $str .= '<table width="100%" class="" style="font-size: 12px; border: 1px solid;margin-top: 15px;">
                                      <thead>
                                        <tr height="30px">          
                                          <th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
                                            <strong>SR. NO.</strong>
                                          </th>
                                          <th width="12%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Work Order No.</strong></th>
                                          <th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Expected Start Date</strong></th>
                                          <th width="18%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Product Name</strong></th>
                                          <th width="18%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Product Image</strong></th>
                                          <th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Process Name</strong></th>
                                          <th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Qty</strong></th>
                                          <th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Time per <br>Qty(In mins)</strong></th>
                                          <th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Pending Qty</strong></th>
                                          <th width="8%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Completed Hours</strong></th>
                                          <th width="10%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Expected End Date</strong></th>
                                        </tr>
                                      </thead>
                                      <tbody style="border: 1px solid;">';
                                      $cnt=brp_mysqli_num_rows($result);
									  if($cnt>0){ 
									  	  $i=1;	
									  	  $today = date('Y-m-d');
									  	  $total_time = 0;
	                                      while($rel=brp_mysqli_fetch_assoc($result))
										  {
										  	$main_qty = $rel["p_qty"];
										  	$pending_qty = $rel["pen_qty"];

										  	/*$remaing_time = $pending_qty*$rel["time_per_qty"];
										  	$total_hours = number_format($remaing_time/60, 2, '.', '');

										  	$total_time = $total_time + $total_hours;
										  	$where = 'resource_id="'.$rel["resource_id"].'"'; 
											$resourceData = get_resource_info_by_id($dbcon, $where);
											$working_hours = $resourceData["working_hours"];
											$numberofdays = $total_time/$working_hours;*/
 
											//$completd_date = get_completed_date_of_resource_based_on_working_hours($today, $numberofdays);
											//$today = $completd_date;
											$completd_date = $rel['expected_end_date'];
											if($rel['image_name']!=null){
												//$image_name1 = '<a href="'.ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
												$image_name1 = '<img src="'.ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;">';
											}else{
												$image_name1 = '';
											}
	                                 		$str .= '<tr>
	                                 					<td style="border:1px #444 solid;">'.$i.'</td>
	                                 					<td style="border:1px #444 solid;">'.$rel["work_order_no"].'</td>
	                                 					<td style="border:1px #444 solid;">'.date('d-M-Y', strtotime($rel["expected_start_date"])).'</td>
	                                 					<td style="border:1px #444 solid;">'.$rel["product_name"].'</td>
	                                 					<td style="border:1px #444 solid;">'.$image_name1.'</td>
	                                 					<td style="border:1px #444 solid;">'.$rel["process_name"].'</td>
	                                 					<td style="border:1px #444 solid;">'.$main_qty.'</td>
	                                 					<td style="border:1px #444 solid;">'.$rel["process_time"].'</td>
	                                 					<td style="border:1px #444 solid;">'.$pending_qty.'</td>
	                                 					<td style="border:1px #444 solid;">'.$rel["total_hour"].'</td>
	                                 					<td style="border:1px #444 solid;">'.date('d-M-Y H:i:s', strtotime($rel["expected_end_date"])).'</td>
	                                 				 </tr>';  
	                                 	  	$i++;			   	
	                                      }
	                                  }else{
	                                  	$str .= '<tr><td colspan="9">NO DATA FOUND</td></tr>';
	                                  }	
                                  	 
                $str .= '</tbody> </table>';

            $arr['msg'] = '1';
            $arr['data']  = $str;          
			echo json_encode($arr);
		}

		else if(strtolower($POST['mode']) == "generate_report") {
	
			$resource_id = $POST['resource_id'];
		$sql = "SELECT wra.*,`p`.`product_name`,`r`.`resource_name`, `rp`.`sp_id`,sp.po_req_no as work_order_no, `proc`.`process_name`, `ressch`.`expected_start_date`, `ressch`.`expected_end_date`,p.image_name  FROM `tbl_work_order_resource_allocate` as wra 
			    LEFT JOIN product_mst as p ON `wra`.`product_id` = `p`.`product_id`
			    LEFT JOIN tbl_resource as r ON `wra`.`resource_id` = `r`.`resource_id`
			    LEFT JOIN tbl_request_product as rp ON `wra`.`request_id` = `rp`.`rp_id`
			    LEFT JOIN process_mst as proc ON `wra`.`process_id` = `proc`.`process_id`
			    LEFT JOIN tbl_set_main_process AS sp on sp.sp_id=rp.sp_id
			    LEFT JOIN tbl_resource_schedule as ressch ON `wra`.`request_id` = `ressch`.`rp_id` and `wra`.`process_id`=`ressch`.`process_id` and `wra`.`product_id`=`ressch`.`p_product_id`
			    WHERE  `wra`.`resource_id`='".$resource_id."'  AND `wra`.`resourse_allocation_status`=0 AND `wra`.`company_id`='".$_SESSION['company_id']."' AND cast(wra.qty AS DECIMAL(50,5))!=0 and cast(wra.qty AS DECIMAL(50,5))>cast(wra.completed_qty AS DECIMAL(50,5))  group BY `wra`.`resource_allocate_id` order by `ressch`.`expected_start_date`"; 

			/*$sql = "select rs.*, (SELECT po_req_no FROM tbl_set_main_process as sm WHERE sm.sp_id=`rs`.`sp_id`) as work_order_no, `p`.`product_name`,`proc`.`process_name`,pp.process_time from tbl_resource_schedule as rs 
							left join product_mst as p ON `rs`.`p_product_id` = `p`.`product_id` 
							left join process_mst as proc ON `rs`.`process_id` = `proc`.`process_id` 
							left join tbl_product_process as pp on pp.product_id=`rs`.`p_product_id` and pp.process_id=`rs`.`process_id`
							where pp.status=0 and  rs.resource_id = '".$resource_id."' and `rs`.`company_id`='".$_SESSION['company_id']."' order by rs.resource_schedule_id ";  */  
			$result=$dbcon->query($sql);

			$where = 'resource_id="'.$resource_id.'"'; 
			$resource_info = get_resource_info_by_id($dbcon, $where);

			$str = '';
			$str .= '<table style="" id="" width="100%" border="0">
                      <tbody><tr style="border-bottom:0.5px #000 solid;border-top:0.5px #000 solid;">
                        <td style="text-align:center" width="90%"> 
                          <strong style="font-size:16px">
                           '.$resource_info["resource_name"].'
                          </strong>
                        </td>
                        <td style="text-align:center" width="10%"> 
                          <strong style="font-size:12px">
                            <b class="data_title"></b>
                          </strong>
                        </td>
                      </tr>
                    </tbody>
                   </table>';

            $str .= '<table width="100%" class="" style="font-size: 12px; border: 1px solid;margin-top: 15px;">
                                  <thead>
                                    <tr height="30px">          
                                      <th  width="5%" style="text-align:center;border:1px solid;border-top:none;">
                                        <strong>SR. NO.</strong>
                                      </th>
                                      <th width="12%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Work Order No.</strong></th>
                                      <th width="18%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Product Name</strong></th>
                                      <th width="18%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Product Image</strong></th>
                                      <th width="15%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Process Name</strong></th>
                                      <th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Qty</strong></th>
                                      <th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Time Per <br>Qty(In mins)</strong></th>
                                      <th width="7%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Pending Qty</strong></th>
                                      <th width="8%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Required Hours</strong></th>
                                      <th width="10%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Expected Start Date</strong></th>
                                      <th width="10%"  style="text-align:center;border-right:1px solid; border-bottom:1px solid;border-top: none;" ><strong>Expected End Date</strong></th>
                                    </tr>
                                  </thead>
                                  <tbody style="border: 1px solid;">';
                                  $cnt=brp_mysqli_num_rows($result);
								  if($cnt>0){ 
								  	  $i=1;	
								  	  $today = date('Y-m-d');
								  	  $total_time = 0;
                                      while($rel=brp_mysqli_fetch_assoc($result))
									  {
									  	$main_qty = intval($rel["qty"]);
										$completed_qty = intval($rel["completed_qty"]);
									  	$pending_qty = $main_qty - $completed_qty;

									  	$remaing_time = $pending_qty * $rel["time_per_qty"];

									  	$completed_hours = convertToHoursMins($remaing_time, '%02d Hours %02d Minutes');
									  	/*
										$total_hours = number_format($remaing_time/60, 2, '.', '');
									  	$total_time = $total_time + $total_hours;
									  	$where = 'resource_id="'.$rel["resource_id"].'"'; 
										$resourceData = get_resource_info_by_id($dbcon, $where);
										$working_hours = $resourceData["working_hours"];
										$numberofdays = $total_time/$working_hours;*/

										//$completd_date = get_completed_date_of_resource_based_on_working_hours($today, $numberofdays);
										//$today = $completd_date;
										
										$exp_start_date = expected_start_end_date($rel["expected_start_date"]);
										$exp_end_date = expected_start_end_date($rel["expected_end_date"]);
										if($rel['image_name']!=null){
											//$image_name1 = '<a href="'.ROOT.'view/upload/product_images/'.$rel1["image_name"].'" target="_blank"><img src="'.ROOT.'view/upload/product_images/'.$rel1['image_name'].'" style="width: 60px;height: 50px;"></a>';
											$image_name1 = '<img src="'.ROOT.'view/upload/product_images/'.$rel['image_name'].'" style="width: 60px;height: 50px;">';
										}else{
											$image_name1 = '';
										}

                                 		$str .= '<tr>
                                 					<td style="border:1px #444 solid;">'.$i.'</td>
                                 					<td style="border:1px #444 solid;">'.$rel["work_order_no"].'</td>
                                 					<td style="border:1px #444 solid;">'.$rel["product_name"].'</td>
                                 					<td style="border:1px #444 solid;">'.$image_name1.'</td>
                                 					<td style="border:1px #444 solid;">'.$rel["process_name"].'</td>
                                 					<td style="border:1px #444 solid;">'.$main_qty.'</td>
                                 					<td style="border:1px #444 solid;">'.$rel["time_per_qty"].'</td>
                                 					<td style="border:1px #444 solid;">'.$pending_qty.'</td>
                                 					<td style="border:1px #444 solid;">'.$completed_hours.'</td>
                                 					<td style="border:1px #444 solid;">'.$exp_start_date.'</td>
                                 					<td style="border:1px #444 solid;">'.$exp_end_date.'</td>
                                 				 </tr>';  
                                 	  	$i++;			   	
                                      }
                                  }else{
                                  	$str .= '<tr><td colspan="9">NO DATA FOUND</td></tr>';
                                  }	
                              	 
            $str .= '</tbody> </table>';

            $arr['msg'] = '1';
            $arr['data']  = $str;          
			echo json_encode($arr);
		}
		else if(strtolower($POST['mode'])== "fetch_resource_based_on_branch")
		{
			$branch_id = $POST['branch_id'];
			$data['resource_id'] = get_resource_work_list($dbcon,'',$branch_id);
			echo json_encode($data);
		}		
		
    }
}

// Conver the mins time to hours and mins format
function convertToHoursMins($time, $format = '%02d:%02d') {
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

// Get Expected Start and End Date Filter Option (Check the condition of start and end date)
function expected_start_end_date($expected_start_end_date){
	if($expected_start_end_date==null){
		$expected_date = '-';
	}else{
		$expected_date = date('d-M-Y h:i:s a', strtotime($expected_start_end_date));
	}
	return $expected_date;
}

?>

