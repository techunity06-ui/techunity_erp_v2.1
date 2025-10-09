<?php 
  
session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	ini_set('max_execution_time', 3000000);
	//echo "select * from menu_master_access where menu_name='INHOUSE PENDING PROCESS'";
	$menu_inhouse=$dbcon->query("select * from menu_master_access where menu_name='WD INHOUSE PENDING PROCESS'");
    $menu_inhouse_row=mysqli_fetch_array($menu_inhouse);
    $parent_id = $menu_inhouse_row['id'];
    
    //echo "SELECT * from menu_master_access_routes where access_id IN (SELECT id FROM `menu_master_access` WHERE parent_id ='$parent_id')";
    $get_permission_data=$dbcon->query("SELECT * from menu_master_access_routes where access_id IN (SELECT id FROM `menu_master_access` WHERE parent_id ='$parent_id')");
    
    $data_permission = array();
    while($row_permission=mysqli_fetch_array($get_permission_data))
    {
     $data_permission[] = $row_permission['id'];
    }

    $get_user_permission_data=$dbcon->query("SELECT * FROM `users` where user_id = '1'");
    $user_permission_row=mysqli_fetch_array($get_user_permission_data);
    
    $permsn_data = explode(",",$user_permission_row['user_access_permission']);
    //echo "jayesh<pre>"; print_r($permsn_data);
    
    $result=array_diff($data_permission,$permsn_data);
    
    $fullDiff = array_merge(array_diff($data_permission, $permsn_data), array_diff($permsn_data, $data_permission));


    $menu_master_access_rights_process_id_remove=$dbcon->query("DELETE from menu_master_access_routes where access_id IN (SELECT id FROM `menu_master_access` WHERE parent_id ='$parent_id')");
    
    
    $delete_inhouse_query=$dbcon->query("DELETE  FROM `menu_master_access_routes` WHERE `slug_name` LIKE '%dashboard-inhouse-%' ORDER BY `id`  DESC");
    $menu_master_access_process_id_remove=$dbcon->query("DELETE FROM `menu_master_access` WHERE parent_id ='$parent_id'");

        //echo "<br>";
        //echo "<br>";
        
        //WD INHOUSE PENDING PROCESS
	
	$new_process_permission = array();
	
  	$sel=$dbcon->query("select * from process_mst where process_status='0' order by process_name");
  
  //$res = mysqli_query($con,$q);
  $i = 0;
									while($row_p1=mysqli_fetch_array($sel))
									{
										$process_id = $row_p1['process_id'];
										$mq = $dbcon->query("select * from menu_master_access where process_id = '$process_id'");
									//	$mq_res = mysqli_query($con,$mq);
										
										 $mq_row=mysqli_fetch_array($mq_res);
										
										$access_id = $mq_row['id'];
										$date_time = date("Y-m-d H:i:s");
										
									$menu_processs_name = $row_p1['process_name'];
									
									if($menu_processs_name != '0')
									{
									
								echo $menu_master_access_q = "INSERT INTO `menu_master_access` (`id`, `user_id`, `parent_id`, `process_id`, `menu_name`, `menu_path`, `menu_description`, `menu_order`, `menu_fa_icon`, `menu_image_url`, `report_status_flag`, `created_at`, `updated_at`, `status`) VALUES (NULL, '1', '$parent_id', '$process_id', '$menu_processs_name', '#', '$menu_processs_name', '$i', 'FA-DASHBOARD', '', 'Yes', CURRENT_TIMESTAMP, NULL, '0');";
										
										$menu_mst = $dbcon->query($menu_master_access_q);
										 	$access_id=mysqli_insert_id($dbcon); 
										//echo mysqli_insert_id($conn); die;
									//	echo 	$access_id = $menu_mst_row['id']; die;
                                        $parent_id = $menu_inhouse_row['id'];
										
										$process_name = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($row_p1['process_name']));			
										$menu_q = "INSERT INTO `menu_master_access_routes` (`id`, `user_id`, `access_id`, `access_type`, `slug_name`, `route_path_name`, `created_at`, `updated_at`, `status`) VALUES (NULL, '1', '$access_id', 'V', '$process_name', '0', '$date_time', '$date_time', '0')";
										
										$dbcon->query($menu_q);
										
										$permission_id=mysqli_insert_id($dbcon); 
										echo $menu_q;
										
										$new_process_permission[] = $permission_id;
										// $menu_res = mysqli_query($con,$menu_q);
										 echo "<br>";
										//$process_array[] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($row_p1['process_name'])); */
										echo "<br>";
										$i++;
										}
									}
									
									
								//	echo "<pre> new permission"; print_r($new_process_permission); 
									
									
								//	echo "<pre> old permission"; print_r($fullDiff); 
									
								//	array_merge = $new_process_permission
									
									$new_per_data = array_values(array_filter(array_merge($fullDiff,$new_process_permission)));
									
								//	rray_values(array_filter($linksArray));
									
									
								//	echo "<pre>new per datat"; print_r($new_per_data); 
									
									
									
                                    $fullDiff_data = implode(",",$new_per_data);
									$update_user_permission_data=$dbcon->query("update `users` set user_access_permission = '$fullDiff_data' where user_id = '1'");
									
									echo "DONE";
									
									//echo "<pre>"; print_r($process_array);die;
   
   
   
   
   ?>