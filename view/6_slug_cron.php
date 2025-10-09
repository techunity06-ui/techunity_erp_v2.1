<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");
	include_once("../include/common_functions.php");
	include("../include/function_database_query.php");
	
	$cnt=1;
	$sel=$dbcon->query("select * from bigdatas_umaboy_erp.menu_master_access as m1 where parent_id=0 and  m1.menu_name NOT in (select m2.menu_name from v1_brperp_gajroll_final.menu_master_access as m2)");
	while($row=brp_mysqli_fetch_array($sel))
	{
		echo $cnt."-".$row['parent_id']."---".$row['menu_name']."<br>";
		$menu_id = $row['id'];
		// main menu items 
		$info['user_id'] = $row['user_id'];
		$info['parent_id'] = $row['parent_id'];
		$info['process_id'] = $row['process_id'];
		$info['menu_name'] = $row['menu_name'];
		$info['menu_path'] = $row['menu_path'];
		$info['menu_description'] = $row['menu_description'];
		$info['menu_order'] = $row['menu_order'];
		$info['menu_fa_icon'] = $row['menu_fa_icon'];
		$info['menu_image_url'] = $row['menu_image_url'];
		$info['report_status_flag'] = $row['report_status_flag'];
		$info['status'] = $row['status'];

		// $inserid=add_record("menu_master_access_gajroll",$info, $dbcon,'');
		
		// $sel1=$dbcon->query("select * from menu_master_access_routes where access_id='$menu_id'");
		// while($row1=brp_mysqli_fetch_array($sel1))
		// {
		// 	//echo $row1['menu_name']."--".$row1['slug_name']."<br>";

		// 	//slug menu items 
		// 	$info1['user_id'] = $row1['user_id'];
		// 	$info1['access_id'] = $inserid;
		// 	$info1['access_type'] = $row1['access_type'];
		// 	$info1['slug_name'] = $row1['slug_name'];
		// 	$info1['route_path_name'] = $row1['route_path_name'];
		// 	$info1['status'] = $row1['status'];

		// 	$inserid=add_record("menu_master_access_routes_gajroll",$info1, $dbcon,'');

		// }

		$cnt++;
	}
?>