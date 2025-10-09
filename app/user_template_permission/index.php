<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");
//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
		if(strtolower($POST['mode']) == "fetch") {
			$appData = array();
			$i=1;
			$aColumns = array('permission_id', 'user.usertype_name', 'menu.menu_name', 'permission', 'per.status', 'per.cdate', 'per.user_id');
			$sIndexColumn = "permission_id";
			$isWhere = array("per.status = 0","per.user_id in (0,$_SESSION[user_id])");
			$sTable = "tbl_permission as per";			
			$isJOIN = array("inner join tbl_usertype as user on per.usertype_id=user.usertype_id","inner join tbl_menu as menu on menu.menu_id=per.menu_id");
			$hOrder = "per.permission_id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['usertype_name'];
				$row_data[] = $row['menu_name'];
				if($row['permission']==1)
				{
					$row_data[] = "YES";
				}
				else
				{
					$row_data[] = "NO";
				}
				if($row['user_id']=="0")
				{
				$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['permission_id'].');"><i class="fa fa-pencil"></i></button>
					<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_state('.$row['permission_id'].')"><i class="fa fa-trash-o"></i></button>
				';}
				else
				{
					$row_data[]='';
				}
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$access_data = [];
			$q = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$POST['usertype_id']."'");
			if($q->num_rows > 0){
				$record = $q->fetch_assoc();
				if(isset($_POST['template_id']) && $_POST['template_id'] != ''){
					$temp = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '".$_POST['template_id']."'");
					$temprecord = $temp->fetch_assoc();
					$updateinfo['user_access_permission'] = '';
					$updateinfo['menu_show_permission'] = '';
					$info['template_access_perm_id'] = $temprecord['id'];
					$info['user_access_permission'] = $temprecord['template_access_permission'];
					$info['menu_show_permission'] = $temprecord['template_menu_show_permission'];
				}else{				
					if(isset($record['user_access_permission']) && $record['user_access_permission'] == ''){
						// If blank then insert new access permission code
						$access_data = array_values(array_filter($_POST['access']));
						$info['user_access_permission'] = implode(",",$access_data);
					}else{
						// If already exist then first blank and insert new access permission code
						$updateinfo['user_access_permission'] = '';
						$access_data = array_values(array_filter($_POST['access']));
						$info['user_access_permission'] = implode(",",$access_data);
					}
					if(isset($record['menu_show_permission']) && $record['menu_show_permission'] == ''){
						// If blank then insert new access permission code
						$menu_data = array_values(array_filter($_POST['menu_show']));
						$info['menu_show_permission'] = implode(",",$menu_data);
					}else{
						// If blank then insert new access permission code
						$updateinfo['menu_show_permission'] = '';
						$menu_data = array_values(array_filter($_POST['menu_show']));
						$info['menu_show_permission'] = implode(",",$menu_data);
					}
					$info['template_access_perm_id'] = '';
				}
				//print "<pre>";
				//print_r($info);
				//die;
				$updaterecord=update_record('users', $updateinfo,"user_id=".$record['user_id'] , $dbcon);
				$updateid=update_record('users', $info,"user_id=".$record['user_id'] , $dbcon);
			}
			if($updateid)
				echo "1";
			else
				echo "0";
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_permission` WHERE `permission_id` = '$POST[id]'  AND `user_id` = '$_SESSION[user_id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$info['usertype_id']= $POST['usertype_id'];				
			$info['menu_id']= $POST['menu_id'];				
			$info['permission']= $POST['permission'];				
			$info['cdate']		= date("Y-m-d H:i:s");				
			$updateid=update_record('tbl_permission', $info,"permission_id=".$POST['eid'] , $dbcon);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']='2';
			$updateid=update_record('tbl_usertype', $info,"usertype_id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0";
		}
		else if(strtolower($POST['mode']) == "show_template_name") {
			$quserdata = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$_POST['id']."'");
			$recorduserData = $quserdata->fetch_assoc();
			$templateID = $recorduserData['template_access_perm_id'];
			$tempFun['template'] = getTemplateName($dbcon, $templateID);
			echo json_encode($tempFun);
		}
		else if(strtolower($POST['mode']) == "show_user_menu") {
			$menu ='';
			$where ='';
			if(isset($_POST['id']) && $_POST['id'] != ''){
				$quserdata = $dbcon->query("SELECT * FROM `users` WHERE `user_id` = '".$POST['id']."'");
				$recorduserData = $quserdata->fetch_assoc();
				
				if(isset($recorduserData['user_access_permission']) && $recorduserData['user_access_permission'] != ''){
					$user_access_permission = explode(",",$recorduserData['user_access_permission']);
				}
				if(isset($recorduserData['menu_show_permission']) && $recorduserData['menu_show_permission'] != ''){
					$menu_show_permission = explode(",",$recorduserData['menu_show_permission']);
				}
			}else{
				$templatedata = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '".$_POST['temp_id']."'");
				$recorduserData = $templatedata->fetch_assoc();
				
				if(isset($recorduserData['template_access_permission']) && $recorduserData['template_access_permission'] != ''){
					$user_access_permission = explode(",",$recorduserData['template_access_permission']);
				}
				if(isset($recorduserData['template_menu_show_permission']) && $recorduserData['template_menu_show_permission'] != ''){
					$menu_show_permission = explode(",",$recorduserData['template_menu_show_permission']);
				}
			}
			/*START JAYESH ADD CLONE PARAMETER*/
    $opertion_name = array('V'=>'View','C'=>'Create','R'=>'Read','U'=>'Update','D'=>'Delete','A'=>'Approve','FA'=>'Final Approve','O'=>'Others','CL'=>'Clone');
    /*END JAYESH ADD CLONE PARAMETER*/
			$querymenu="SELECT menumaster.*, CASE WHEN parent_id = 0 THEN id ELSE parent_id END AS sort FROM menu_master_access as menumaster where menumaster.status=0 ORDER BY sort, id";
			$result_menu=$dbcon->query($querymenu);		
			$i=0;
			$menu='<table class="display table table-bordered table-striped dataTable" id="dynamic-table" aria-describedby="dynamic-table_info" width="100%">
				  <thead>
				  <tr>
				  	  <th class="text-center myHeader">Module Name</th>
				  	  <th class="text-center myHeader">Menu Show</th>
					  <th class="text-center">View</th>
					  <th class="text-center">Create</th>
					  <th class="text-center">Read</th>
					  <th class="text-center">Update</th>
					  <th class="text-center">Delete</th>
					  <th class="text-center">Approve</th>
					  <th class="text-center">Final<br/>Approve</th>
					  <th class="text-center">Others</th>
					  <th class="text-center">Clone</th>
				  </tr>
				  </thead>
				  ';
			$arrayAcc = [];
			$d = 0;
			$ccheckCls = $mcheckCls = '';
			while($rel_menu=mysqli_fetch_assoc($result_menu))
			{
				$k = $i;
				if(in_array($rel_menu['id'], $menu_show_permission)){
					$mcheckCls = 'checked';
				}else{
					$mcheckCls = '';
				}
				$getArrayAccess = getAccessTypeMain($dbcon, $rel_menu['id']);
				$access_type_array = explode(',', $getArrayAccess['access_type']);
				$primary_access_id = explode(',', $getArrayAccess['primary_access_id']);
				$access_id = explode(',', $getArrayAccess['access_id']);
				$parent_id = explode(',', $getArrayAccess['parent_id']);
				$slug_name_array = explode(',', $getArrayAccess['slug_name']);
				
				if($rel_menu['parent_id'] == '0'){

					$d++;
					$menu.="<tr style='border-top: 2px solid;' class='headerRow'>";
					$menu.="<td></td>";
					if($rel_menu['menu_path'] != '#'){
						$menu.="<td data-id='".$d."' data-cls='vw' class='text-center allMenuShow'><input type='checkbox' style='width: 31px; height: 25px;' name='menu_show[]' value='' onclick='template_name()' class='mainChk vw".$rel_menu['id']."' data-id='".$rel_menu['id']."'/></td>";
					}else{
						$menu.="<td class='text-center'>-</td>";
					}
					foreach($opertion_name as $opkey => $opval){
						$access_type_sign = $opkey;
						$menu.="<td data-id='".$d."' data-cls='".$opkey."' class='text-center allMenuShow'><input type='checkbox' style='width: 31px; height: 25px;' name='access[]' value='' onclick='template_name()' class='mainChk ".$opkey.$rel_menu['id']."' data-id='".$opkey.$rel_menu['id']."' data-child=".$opkey.$rel_menu['parent_id']." /></td>";
					}
					$menu.="</tr>";
				}
				$menu.="<tr class='sub_".$d."'>";
				$menu.="<td style='padding-left: 20px;'>".$rel_menu['menu_name']."</td>";
				if($rel_menu['menu_path'] != '#' && $rel_menu['report_status_flag'] == 'No'){
					$menu.="<td class='text-center'><input type='checkbox' style='width: 31px; height: 25px;' name='menu_show[]' $mcheckCls value='".$rel_menu['id']."' onclick='template_name()' class='vw vw".$rel_menu['id']."' data-id='".$rel_menu['id']."' title='".$rel_menu['menu_name']."' /></td>";
				}else{
					$menu.="<td class='text-center'>-</td>";
				}
				foreach($opertion_name as $opkey => $opval){
					$access_type_sign = $opkey;
					if( in_array( $access_type_sign, $access_type_array)){
						$acess_type_array_index = array_search($access_type_sign, $access_type_array);
						if(in_array($primary_access_id[$acess_type_array_index], $user_access_permission)){
							$ccheckCls = 'checked';
						}else{
							$ccheckCls = '';
						}
						$menu.="<td class='text-center'><input type='checkbox' style='width: 31px; height: 25px;' name='access[]' $ccheckCls class='".$opkey.' '.$opkey.$access_id[$acess_type_array_index]."' value=".$primary_access_id[$acess_type_array_index]." onclick='template_name()' data-id='".$access_type_sign.$access_id[$acess_type_array_index]."' data-child=".$access_type_sign.$parent_id[$acess_type_array_index]." title=".$slug_name_array[$acess_type_array_index]." /></td>";
					}else{
						$menu.="<td class='text-center'>-</td>";
					}
				}
				$menu.="</tr>";
				$i++;
			}
			
			$menu .="<input type='hidden' name='totalmenu' id='totalmenu'  value=".$i." /></div>";
			echo $menu;
		}

function getAccessTypeMain($dbcon, $access_id, $access_type){
	$response =  [];
	$querypermission="select GROUP_CONCAT(menumaster.parent_id) as parent_id,GROUP_CONCAT(routesmaster.id) as primary_access_id, GROUP_CONCAT(routesmaster.access_id) as access_id, GROUP_CONCAT(routesmaster.access_type) as access_type, GROUP_CONCAT(routesmaster.slug_name) as slug_name from menu_master_access as menumaster left join menu_master_access_routes as routesmaster ON routesmaster.access_id = menumaster.id
			 	 where menumaster.status=0 and routesmaster.status=0 and routesmaster.access_id='".$access_id."'";
	$getRecordData = $dbcon->query($querypermission);		 	 
	if($getRecordData->num_rows > 0){		 	 
		$rel_permission=mysqli_fetch_assoc($getRecordData);
		$response['parent_id'] = $rel_permission['parent_id'];
		$response['primary_access_id'] = $rel_permission['primary_access_id'];
		$response['access_id'] = $rel_permission['access_id'];
		$response['access_type'] = $rel_permission['access_type'];
		$response['slug_name'] = $rel_permission['slug_name'];
		return $response; 
	}else{
		return false;
	} 
}

?>