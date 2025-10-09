<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include(COMMON_FUNCTION_PATH."common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
		
		if(strtolower($POST['mode']) == "fetch") {
			$bulkAccessArray = canCheckPermissionAccess($dbcon, [
						'template-access-permission-update',
						'template-access-permission-delete',
						'template-access-permission-status'
					]);
			$appData = array();
			$i=1;
			$aColumns = array('tempaccess.id', 'com.company_name', 'tempaccess.template_name', 'tempaccess.status', 'tempaccess.created_at');
			$sIndexColumn = "tempaccess.id";
			$isWhere = array("tempaccess.status IN (0,1) and tempaccess.company_id in (0,$_SESSION[company_id])");
			$sTable = "template_access_permission as tempaccess";			
			$isJOIN = array("left join tbl_company as com on com.company_id=tempaccess.company_id");
			$hOrder = "tempaccess.id desc";
			include('../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['company_name'];
				$row_data[] = $row['template_name'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				$edit_btn='';$delete_btn='';$change_status='';
				if($row['id']!='0'){
					if(in_array('template-access-permission-update',$bulkAccessArray)){
						$edit_btn='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'. ROOT . 'template_access_permission_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>';
					}
					if(in_array('template-access-permission-delete',$bulkAccessArray)){
						$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_template_access_permission('.$row['id'].')"><i class="fa fa-trash-o"></i></button>';
					}
				}
				if($row['status'] == '0')
				{  
					if(in_array('template-access-permission-status',$bulkAccessArray)){
						$change_status="<a class='btn btn-xs btn-danger' data-original-title='change status' data-toggle='tooltip' data-placement='top'  onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-window-close'></i></a>";	
					}
				} else {
					if(in_array('template-access-permission-status',$bulkAccessArray)){
						$change_status="<a class='btn btn-xs btn-success' data-original-title='change status' data-toggle='tooltip' data-placement='top' onclick='changeStatus(\"".$row['id']."\",\"".$row['status']."\")'><i class='fa fa-check-square-o'></i></a>";
					}
				}
					
				$row_data[] = $edit_btn.' '.$delete_btn. ' '. $change_status;
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$access_data = [];
			$info['user_id']	= $_SESSION['user_id']; 
			$info['company_id']	= $_SESSION['company_id'];
			$info['template_name']	= $_POST['template_name'];
			$access_data = array_values(array_filter($_POST['access']));
			$info['template_access_permission'] = implode(",",$access_data);
			$menu_data = array_values(array_filter($_POST['menu_show']));
			$info['template_menu_show_permission'] = implode(",",$menu_data);
			$info['created_at']	= date("Y-m-d H:i:s");
			$insertid=add_record('template_access_permission', $info, $dbcon);
			if($insertid)
				echo "1";
			else
				echo "0";
		
		}
		else if(strtolower($POST['mode']) == "edit") {
			$access_data = [];
			$q = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '".$POST['eid']."'");
			if($q->num_rows > 0){
				$record = $q->fetch_assoc();

				$info['user_id']	= $_SESSION['user_id']; 
				$info['company_id']	= $_SESSION['company_id'];
				$info['template_name']	= $_POST['template_name'];
				if(isset($record['template_access_permission']) && $record['template_access_permission'] == ''){
					// If blank then insert new access permission code
					$access_data = array_values(array_filter($_POST['access']));
					$info['template_access_permission'] = implode(",",$access_data);
				}else{
					// If already exist then first blank and insert new access permission code
					$updateinfo['template_access_permission'] = '';
					$access_data = array_values(array_filter($_POST['access']));
					$info['template_access_permission'] = implode(",",$access_data);
				}
				if(isset($record['template_menu_show_permission']) && $record['template_menu_show_permission'] == ''){
					// If blank then insert new access permission code
					$menu_data = array_values(array_filter($_POST['menu_show']));
					$info['template_menu_show_permission'] = implode(",",$menu_data);
				}else{
					// If blank then insert new access permission code
					$updateinfo['template_menu_show_permission'] = '';
					$menu_data = array_values(array_filter($_POST['menu_show']));
					$info['template_menu_show_permission'] = implode(",",$menu_data);
				}
				$info['updated_at']	= date("Y-m-d H:i:s");
				$updaterecord=update_record('template_access_permission', $updateinfo,"id=".$POST['eid'] , $dbcon);
				$updateid=update_record('template_access_permission', $info,"id=".$POST['eid'] , $dbcon);
			}
			if($updateid)
				echo "update";
			else
				echo "0";
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']	= 2;
			$updatetemplateaccessid=update_record('template_access_permission', $info,"id=".$POST['eid'] , $dbcon);			
			if($updatetemplateaccessid)
				echo "1";	
			else
				echo "0";			
		}
		else if(strtolower($POST['mode']) == "change_status") {
			$p_status = $POST['p_status'];

			$info['status'] = ($p_status=='0') ? '1' : '0';
			
			$updateid = update_record('template_access_permission', $info,"id=".$POST['eid'] , $dbcon);
			echo ($updateid) ? "1" : "0";
		}
		else if(strtolower($POST['mode']) == "show_user_menu") {
			$menu ='';
			$where ='';
			$quserdata = $dbcon->query("SELECT * FROM `template_access_permission` WHERE `id` = '".$POST['id']."'");
			$recordtemplateData = $quserdata->fetch_assoc();
			
			if(isset($recordtemplateData['template_access_permission']) && $recordtemplateData['template_access_permission'] != ''){
				$user_access_permission = explode(",",$recordtemplateData['template_access_permission']);
			}
			if(isset($recordtemplateData['template_menu_show_permission']) && $recordtemplateData['template_menu_show_permission'] != ''){
				$menu_show_permission = explode(",",$recordtemplateData['template_menu_show_permission']);
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
				  	  <th class="text-center">Module Name</th>
				  	  <th class="text-center">Menu Show</th>
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
						$menu.="<td data-id='".$d."' data-cls='vw' class='text-center allMenuShow'><input type='checkbox' style='width: 31px; height: 25px;' name='menu_show[]' value='' class='mainChk vw".$rel_menu['id']."' data-id='".$rel_menu['id']."'/></td>";
					}else{
						$menu.="<td class='text-center'>-</td>";
					}	
					foreach($opertion_name as $opkey => $opval){
						$access_type_sign = $opkey;
						$menu.="<td data-id='".$d."' data-cls='".$opkey."' class='text-center allMenuShow'><input type='checkbox' style='width: 31px; height: 25px;' name='access[]' value='' class='mainChk ".$opkey.$rel_menu['id']."' data-id='".$opkey.$rel_menu['id']."' data-child=".$opkey.$rel_menu['parent_id']." /></td>";
					}
					$menu.="</tr>";
				}
				$menu.="<tr class='sub_".$d."'>";
				$menu.="<td style='padding-left: 20px;'>".$rel_menu['menu_name']."</td>";
				if($rel_menu['menu_path'] != '#' && $rel_menu['report_status_flag'] == 'No'){
					$menu.="<td class='text-center'><input type='checkbox' style='width: 31px; height: 25px;' name='menu_show[]' $mcheckCls value='".$rel_menu['id']."' class='vw vw".$rel_menu['id']."' data-id='".$rel_menu['id']."' title='".$rel_menu['menu_name']."' /></td>";
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
						$menu.="<td class='text-center'><input type='checkbox' style='width: 31px; height: 25px;' name='access[]' $ccheckCls class='".$opkey.' '.$opkey.$access_id[$acess_type_array_index]."' value=".$primary_access_id[$acess_type_array_index]." data-id='".$access_type_sign.$access_id[$acess_type_array_index]."' data-child=".$access_type_sign.$parent_id[$acess_type_array_index]." title=".$slug_name_array[$acess_type_array_index]." /></td>";
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