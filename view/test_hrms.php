<?php 
	session_start();
	include_once("../config/config.php");
	include_once("../config/session.php");


	$qry="SELECT * FROM `tbl_menu` WHERE `pid` = 368";
	$rs_state=$dbcon->query($qry);

	$i=0;
	while($row=mysqli_fetch_assoc($rs_state))
	{
		//echo '<pre>';print_r($row);exit;

	$qry1="INSERT INTO `menu_master_access` (`id`, `user_id`, `parent_id`, `process_id`, `menu_name`, `menu_path`, `menu_description`, `menu_order`, `menu_fa_icon`, `report_status_flag`, `created_at`, `updated_at`, `status`) 

		VALUES (NULL, '1', '42', '0', '".$row['menu_name']."', '".$row['page_name']."', '".$row['page_name']."',".$row['menuorder'].", 'fa-dot-circle-o', 'No', '2021-01-05 21:19:18', '2021-01-08 18:02:30', '0')";

		$dbcon->query($qry1);
		$insert_id=mysqli_insert_id($dbcon);
		if(isset($insert_id))
		{
			echo '<pre>'; echo 'Record Added Successfully'.$i;
			$i++;
		}
	}exit;