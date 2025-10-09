<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch" || strtolower($POST['mode']) == "per_click" ) {
		$appData = array();
		$i=1;
		$aColumns = array('menumaster.id' ,'menumaster.menu_name', 'menumaster.menu_path' ,'menumaster.menu_order','menumaster.menu_description', 'menumaster.status', 'menumaster.parent_id','menumaster.user_id');
		$sIndexColumn = "menumaster.id";
		$isWhere = array("menumaster.status IN (0,1)","parent_id =".$POST['parent_id']);
		$sTable = "menu_master_access as menumaster";			
		$isJOIN = array();
		$hOrder = "menumaster.menu_order";
		include('../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['menu_name'];
			$row_data[] = $row['menu_description'];
			$row_data[] = ($row['menu_path'])?$row['menu_path']:'-';
			$row_data[] = $row['menu_order'];
			if($row['status']=='0'){
				$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
			}else{
				$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
			}
			if($row['parent_id']=='0')
			{
				if($row['menu_path'] == '0' || $row['menu_path'] == ''){
					$addcat='<button class="btn btn-xs btn-success"  onClick="pid_test('.$row['id'].',\''.$row['menu_name'].'\');" data-original-title="Add Sub Menu" data-toggle="tooltip" data-placement="top">Add Sub Menu</button>';
					$view_cron='';
				}else{
					$view_cron = '<button class="btn btn-xs btn-info" data-original-title="View Cron" data-toggle="tooltip" data-placement="top" onClick="open_cron_modal('.$row['id'].','.$row['parent_id'].')">View Cron</button>';
					$addcat='';
				}
			}
			else
			{
				if($row['menu_path'] == '0' || $row['menu_path'] == ''){
					$addcat='<button class="btn btn-xs btn-success"  onClick="pid_test('.$row['id'].',\''.$row['menu_name'].'\');" data-original-title="Add Sub Menu" data-toggle="tooltip" data-placement="top">Add Sub Menu</button>';
					$view_cron='';
				}else{
					$view_cron = '<button class="btn btn-xs btn-info" data-original-title="View Cron" data-toggle="tooltip" data-placement="top" onClick="open_cron_modal('.$row['id'].','.$row['parent_id'].')">View Cron</button>';
					
					$addcat='';
				}	
			}

			$row_data[] = $addcat.' '.$view_cron.' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . 'menu_master_access_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>
				<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_menu('.$row['id'].')"><i class="fa fa-trash-o"></i></button>
				<a class="btn btn-xs btn-danger" data-original-title="change status" data-toggle="tooltip" data-placement="top"  onclick="changeStatus('.$row['id'].','.$row['status'].')"><i class="fa fa-window-close"></i></a>';
			
			$appData[] = $row_data;
			$id++;
		}
		
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		$arr = [];
		$info['user_id']	= $_SESSION['user_id'];
		$info['parent_id']	= $_POST['parent_id'];		
		$info['menu_name']	= $POST['menu_name'];
		$info['menu_path']	= ($_POST['menu_path'])?$_POST['menu_path']:'0';
		$info['menu_description']	= $_POST['menu_description'];	
		$info['menu_order']	= $_POST['menu_order'];							
		$info['menu_fa_icon']	= $_POST['menu_fa_icon'];							
		$info['menu_image_url']	= $_POST['menu_image_url'];							
		$info['report_status_flag']	= ($_POST['report_status_flag'])?$_POST['report_status_flag']:'No';		
		$info['created_at']		= date("Y-m-d H:i:s");
		$info['status']	= $_POST['status'];
		$insertid=add_record('menu_master_access', $info, $dbcon);

		$slug_name_data = $_POST['slug_name'];
		$route_name_data = $_POST['route_name'];
		foreach ($slug_name_data as $key => $value) {
			if($value != ''){
				$slug_name = $slug_name_data[$key];
				$route_name = $route_name_data[$key];
				$q = $dbcon->query("SELECT * FROM `menu_master_access_routes` WHERE `slug_name` = '$slug_name.' and access_type = '$key' and access_id = '".$POST['eid']."'");
					$routeInfo['user_id'] = $_SESSION['user_id']; 
					$routeInfo['access_id'] = $insertid; 
					if($key == 'V'){
						$routeInfo['access_type'] = 'V';
					}
					if($key == 'C'){
						$routeInfo['access_type'] = 'C';
					}
					if($key == 'R'){
						$routeInfo['access_type'] = 'R';
					}
					if($key == 'U'){
						$routeInfo['access_type'] = 'U';
					}
					if($key == 'D'){
						$routeInfo['access_type'] = 'D';
					}
					if($key == 'A'){
						$routeInfo['access_type'] = 'A';
					}
					if($key == 'FA'){
						$routeInfo['access_type'] = 'FA';
					}
					if($key == 'O'){
						$routeInfo['access_type'] = 'O';
					}
					/*START JAYESH 16-7-2021 for clone*/
					if($key == 'CL'){
						$routeInfo['access_type'] = 'CL';
					}
					/*START JAYESH 16-7-2021 for clone*/
					$routeInfo['slug_name'] = $slug_name; 
					$routeInfo['route_path_name'] = $route_name; 
				if($q->num_rows > 0) {
					$routeInfo['updated_at'] =  date("Y-m-d H:i:s");
					$recordchangeid=update_record('menu_master_access_routes', $routeInfo,"access_id=".$POST['eid'] , $dbcon);
					if($recordchangeid){
						$arr['msg'] = '1';
					}else{
						$arr['msg'] = '-1';
					}
				}else{
					$routeInfo['created_at'] =  date("Y-m-d H:i:s");
					$recordchangeid=add_record('menu_master_access_routes', $routeInfo, $dbcon);
					if($recordchangeid){
						$arr['msg'] = '1';
					}else{
						$arr['msg'] = '-1';
					}
				}
			}else{
				$arr['msg'] = '1';
			}
		}
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "edit") {
		$arr = [];
		$info['user_id']	= $_SESSION['user_id'];
		$info['parent_id']	= $_POST['parent_id'];		
		$info['menu_name']	= $POST['menu_name'];
		$info['menu_path']	= ($_POST['menu_path'])?$_POST['menu_path']:'0';
		$info['menu_description']	= $_POST['menu_description'];	
		$info['menu_order']	= $_POST['menu_order'];							
		$info['menu_fa_icon']	= $_POST['menu_fa_icon'];							
		$info['menu_image_url']	= $_POST['menu_image_url'];							
		$info['report_status_flag']	= ($_POST['report_status_flag'])?$_POST['report_status_flag']:'No';		
		$info['updated_at']		= date("Y-m-d H:i:s");
		$info['status']	= $_POST['status'];				
		$updateid=update_record('menu_master_access', $info,"id=".$POST['eid'] , $dbcon);
		$slug_name_data = $_POST['slug_name'];
		$route_name_data = $_POST['route_name'];
		foreach ($slug_name_data as $key => $value) {
			if($value != ''){
				$slug_name = $slug_name_data[$key];
				$route_name = $route_name_data[$key];
				$q = "SELECT * FROM menu_master_access_routes WHERE access_type = '$key' and access_id = '".$POST['eid']."'"; 
				$result = $dbcon->query($q);
				$routeInfo['user_id'] = $_SESSION['user_id']; 
				$routeInfo['access_id'] = $POST['eid']; 
				if($key == 'V'){
					$routeInfo['access_type'] = 'V';
				}
				if($key == 'C'){
					$routeInfo['access_type'] = 'C';
				}
				if($key == 'R'){
					$routeInfo['access_type'] = 'R';
				}
				if($key == 'U'){
					$routeInfo['access_type'] = 'U';
				}
				if($key == 'D'){
					$routeInfo['access_type'] = 'D';
				}
				if($key == 'A'){
					$routeInfo['access_type'] = 'A';
				}
				if($key == 'FA'){
					$routeInfo['access_type'] = 'FA';
				}
				if($key == 'O'){
					$routeInfo['access_type'] = 'O';
				}
				$routeInfo['slug_name'] = $slug_name; 
				$routeInfo['route_path_name'] = $route_name; 
				if($result->num_rows > 0) {
					$row = mysqli_fetch_assoc($result);
					$routeInfo['updated_at'] =  date("Y-m-d H:i:s");
					$recordchangeid=update_record('menu_master_access_routes', $routeInfo,"id=".$row['id'], $dbcon);
					if($recordchangeid){
						$arr['msg'] = '2';
					}else{
						$arr['msg'] = '-1';
					}
				}else{
					$routeInfo['created_at'] =  date("Y-m-d H:i:s");
					$recordchangeid=add_record('menu_master_access_routes', $routeInfo, $dbcon);
					if($recordchangeid){
						$arr['msg'] = '2';
					}else{
						$arr['msg'] = '-1';
					}
				}
			}else{
				$arr['msg'] = '1';
			}
		}
		echo json_encode($arr);	
	}
	else if(strtolower($POST['mode']) == "delete") {
			$info['status']	= 2;
			$deletemenumasterid=update_record('menu_master_access', $info,"id=".$POST['eid'] , $dbcon);	
			$deletemenumasterroutesid=update_record('menu_master_access_routes', $info,"access_id=".$POST['eid'] , $dbcon);
			if($deletemenumasterroutesid)
				echo "1";	
			else
				echo "0";

	}
	else if(strtolower($POST['mode']) == "check_validate") {
		$slug_name = $_POST['currentVal'];
		$access_routes_id = $_POST['currentTableVal'];
		$where = '';
		if($access_routes_id != ''){
			$where .= " and id != '".$access_routes_id."'";
		}
		$q = "SELECT * FROM menu_master_access_routes WHERE slug_name = '$slug_name' and status = '0' $where"; 
		$result = $dbcon->query($q);
		$row = mysqli_fetch_assoc($result);
		if($row){
			echo '1';
		}else{
			echo '0';
		}
	}
	else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updatemenumasterid=update_record('menu_master_access', $info,"id=".$POST['eid'] , $dbcon);	
			$updatemenumasterroutesid=update_record('menu_master_access_routes', $info,"access_id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
	}
	else if(strtolower($POST['mode']) == "view_crone") {
		$str='';
		$query = "select GROUP_CONCAT(slug_name) as slug from menu_master_access_routes where access_id=".$POST['eid']." group by access_id";
		$result = $dbcon->query($query);
		$row = brp_mysqli_fetch_array($result);
		
		$split = explode(",",$row['slug']);
		
		for($i=0; $i<count($split); $i++){
			$a[] =  "'$split[$i]'";
		}
		$in = implode(",", $a);
		$a = count($split);
		$str .= '$q="select * from menu_master_access_routes where slug_name in ('.$in.')";<br>
		$result = $dbcon->query($q);<br>
		$cnt = brp_mysqli_num_rows($result);<br>
		$split = explode(",","'.$row[slug].'");<br>
		$a = count($split);<br>
		if($cnt>0){<br>
			$row = brp_mysqli_fetch_array($result);<br>
			for($j=0; $j<$a; $j++){<br>
				$route = "select * from menu_master_access_routes where slug_name='."'".'$split[$j]'."'".'";<br>
				$result1 = $dbcon->query($route);<br>
				$row1 = brp_mysqli_fetch_array($result1);<br>
				if(in_array($split[$j],$split)){<br>
									
				}else{<br>';
					for($i=0; $i<count($split); $i++){
						$a[] =  "'$split[$i]'";
						
						$slug_q = $dbcon->query("select * from menu_master_access_routes where slug_name='$split[$i]'");
						$ro2 = brp_mysqli_fetch_array($slug_q);				
						$str.='$insert_slug = "insert into menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name) values ('.'$_SESSION[user_id]'.','.'$row1[access_id]'.','.'$row1[access_type]'.','."'$ro2[slug_name]'".','."'$ro2[route_path_name]'".')";<br>
						$insert_slug_e = $dbcon->query($insert_slug);<br>';
					}
				$str.='}<br>
			}<br>	
		}else{<br>';
			$menu_cr = "select * from menu_master_access where id=".$POST['eid'];
			$menu_cr_e = $dbcon->query($menu_cr);
			$menu_r = brp_mysqli_fetch_array($menu_cr_e);
			$str .= '$insert_menu = "insert into menu_master_access (user_id,parent_id,menu_name,menu_path,menu_description,menu_order,menu_fa_icon,menu_image_url,report_status_flag) values ('."'$_SESSION[user_id]'".','."'$menu_r[parent_id]'".','."'$menu_r[menu_name]'".','."'$menu_r[menu_path]'".','."'$menu_r[menu_description]'".','."'$menu_r[menu_order]'".','."'$menu_r[menu_fa_icon]'".','."'$menu_r[menu_image_url]'".','."'$menu_r[report_status_flag]'".')";<br>
			$insert_menu_e = $dbcon->query($insert_menu);<br>
			$parent_id=brp_mysqli_insert_id($dbcon);<br>';


			for($i=0; $i<count($split); $i++){
				$a[] =  "'$split[$i]'";
				
				$slug_q = $dbcon->query("select * from menu_master_access_routes where slug_name='$split[$i]'");
				$ro2 = brp_mysqli_fetch_array($slug_q);				
				$str.='$insert_slug = "insert into menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name) values ('."'$_SESSION[user_id]'".','.'$parent_id'.','."'$ro2[access_type]'".','."'$ro2[slug_name]'".','."'$ro2[route_path_name]'".')";<br>
				$insert_slug_e = $dbcon->query($insert_slug);<br>';
			}
			
		$str .='}';
		
		/*$query = "select * from menu_master_access where id=".$POST['eid'];
		$result = $dbcon->query($query);
		$row = brp_mysqli_fetch_array($result);
		$str.='$q="select * from menu_master_access where menu_name ="'.$row['menu_name'].'";<br>
		$result = $dbcon->query($q);<br>
		$cnt = brp_mysqli_num_rows($result);<br>
		$row = brp_mysqli_fetch_array($result);<br>
		if($cnt>0){<br>';
			$sque = "select * from menu_master_access_routes access_id=".$POST['eid'];
			$result1 = $dbcon->query($sque);
			while($ro2 = brp_mysqli_fetch_array($result1)){
				$que = "select * from menu_master_access_routes where slug_name='".$ro2['slug_name']."'";
				$result2 = $dbcon->query($que);
				$cn1 = brp_mysqli_num_rows($result2);
				$ro1 = brp_mysqli_fetch_array($result2);
				if($cn1>0){
					
				}else{
					$str .= '$inser = "insert into menu_master_access_routes (user_id,access_id,access_type,slug_name,route_path_name) values ("'.$_SESSION['user_id'].'","'.$ro1['access_id'].'","'.$ro1['access_type'].'","'.$ro1['slug_name'].'","'.$ro1['route_path_name'].'")"';
				}
			}
		$str .='}<br>else{<br>';
			$ins = "INSERT INTO `menu_master_access` (`user_id`, `parent_id`, `process_id`, `menu_name`, `menu_path`, `menu_description`, `menu_order`, `menu_fa_icon`, `menu_image_url`, `report_status_flag`) VALUES ( ".$_SESSION['user_id'].", ".$POST['eid'].", 0, '".$row['menu_name']."', '".$row['menu_path']."', '".$row['menu_description']."', ".$row['menu_order'].", ".$row['menu_fa_icon'].", ".$row['menu_image_url'].", '".$row['report_status_flag']."')";
			$last_id = $dbcon->lastInsertId();
			$str .= '$insert_menu = "insert into menu_master_access (user_id, parent_id, process_id, menu_name, menu_path, menu_description, menu_order, menu_fa_icon, menu_image_url, report_status_flag) values ("'.$_SESSION['user_id'].'", "'.$POST['eid'].'", 0, "'.$row['menu_name'].'", "'.$row['menu_path'].'", "'.$row['menu_description'].'", "'.$row['menu_order'].'", "'.$row['menu_fa_icon'].'", "'.$row['menu_image_url'].'", "'.$row['report_status_flag'].'")";<br>
				$ins_menu = $dbcon->query($insert_menu);<br>
				$insert_id = = $dbcon->lastInsertId();<br>
				';
			$q = $dbcon->query("select * from menu_master_access_routes where access_id=".$POST['eid']."");
			
			while($row1 = brp_mysqli_fetch_array($q)){
				$str .= '$insert_slug = $dbcon->query("insert into menu_master_access_routes (user_id,access_id,slug_name,route_path_name,status) values ("'.$_SESSION['user_id'].'","'.$insert_id.'","'.$row1['slug_name'].'","'.$row1['route_path_name'].'","'.$row1['status'].'")");<br>';
			}
		$str.='}<br>';*/

		echo $str;
	}
?>