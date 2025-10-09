<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");

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
			
			$deleteid=delete_record('tbl_permission',"usertype_id=".$POST['usertype_id'], $dbcon);
			foreach ($POST['menu_id'] as $key => $name) 
			{	
				$info['usertype_id']= $POST['usertype_id'];							
				$info['menu_id']	= $POST['menu_id'][$key];							
				$info['permission']	= '1';		
				if(in_array($POST['menu_id'][$key],$POST['deletemenu_id']))
				{	$info['delete_permission']	= '1';}
				else { $info['delete_permission']	= '0'; }
				
				if(in_array($POST['menu_id'][$key],$POST['editmenu_id']))
				{	$info['edit_permission']	= '1';}
				else { $info['edit_permission']	= '0'; }
				
				if(in_array($POST['menu_id'][$key],$POST['aprvmenu_id']))
				{	$info['aprv_permission']	= '1';}
				else { $info['aprv_permission']	= '0'; }
				
				if(in_array($POST['menu_id'][$key],$POST['finalmenu_id']))
				{	$info['final_aprv_permission']	= '1';}
				else { $info['final_aprv_permission']	= '0'; }

				if(in_array($POST['menu_id'][$key],$POST['addmenu_id']))
				{	$info['add_permission']	= '1';}
				else { $info['add_permission']	= '0'; }

				if(in_array($POST['menu_id'][$key],$POST['othermenu_id']))
				{	$info['other_permission']	= '1';}
				else { $info['other_permission']	= '0'; }
				
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION[user_id];
				$inserid=add_record('tbl_permission', $info, $dbcon);
			}
			if($inserid)
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
		else if(strtolower($POST['mode']) == "show_menu") {			
			$menu ='';
			$where ='';
			if($_SESSION['user_id']!="0")
			{
				$where=' and menu_id!=70 and menu_id!=71 and menu_id!=72';
			}
			 $querymenu="select * from tbl_menu where status=0 and pid=0".$where;
			$result_menu=$dbcon->query($querymenu);		
			$i=0;
			$menu='<table class="display table table-bordered table-striped dataTable" id="dynamic-table" aria-describedby="dynamic-table_info" width="100%">
				  <thead>
				  <tr>
					  <th class="text-center">Menu Name</th>
					  <th class="text-center">Menu Show</th>
					  <th class="text-center">Add</th>
					  <th class="text-center">Edit</th>
					  <th class="text-center">Delete</th>
					  <th class="text-center">Other</th>
					  <th class="text-center">Approve<br/>Permission</th>
					  <th class="text-center">Final<br/>Approve</th>
				  </tr>
				  </thead>
				  ';
			while($rel_menu=mysqli_fetch_assoc($result_menu))
			{
				$chk='';
				$querypermission="select permission,add_permission,	other_permission,delete_permission,edit_permission,aprv_permission,final_aprv_permission from tbl_permission where usertype_id=".$POST['id']."  and menu_id=".$rel_menu['menu_id'];
				$rel_permission=mysqli_fetch_assoc($dbcon->query($querypermission));
				$cls='hidden';
				if($rel_menu['incl_per']=='1'){
					$cls='';
				}

				$menu.="<tr style='border-top: 2px solid;'>
						<td><b>".$rel_menu['menu_name']."</b></td>
						<td class='text-center'><input type='checkbox' ".($rel_permission['permission']==1?"checked='checked'":"")." class='mainmenu".$i."' style='width: 31px;
							height: 25px;' name='menu_id[]' id='vw".$i."' onClick='submenuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission['add_permission']==1?"checked='checked'":"")." class='addmain".$i."' style='width: 31px;
							height: 25px;' name='addmenu_id[]' id='add".$i."' onClick='add_menuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission['edit_permission']==1?"checked='checked'":"")." class='editmain".$i."' style='width: 31px;
							height: 25px;' name='editmenu_id[]' id='edt".$i."' onClick='edit_menuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission['delete_permission']==1?"checked='checked'":"")." class='deletemain".$i."' style='width: 31px;
							height: 25px;' name='deletemenu_id[]' id='del".$i."' onClick='delete_menuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission['other_permission']==1?"checked='checked'":"")." class='othermain".$i."' style='width: 31px;
							height: 25px;' name='othermenu_id[]' id='other".$i."' onClick='other_menuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
							<td class='text-center '><input type='checkbox' ".($rel_permission['aprv_permission']==1?"checked='checked'":"")." class='aprvmain".$i." ".$cls."' style='width: 31px;
							height: 25px;' name='aprvmenu_id[]' id='aprv".$i."' onClick='aprv_menuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
							<td class='text-center '><input type='checkbox' ".($rel_permission['final_aprv_permission']==1?"checked='checked'":"")." class='finalmain".$i." ".$cls."' style='width: 31px;
							height: 25px;' name='finalmenu_id[]' id='fnl".$i."' onClick='final_menuactive(".$i.");' value=".$rel_menu['menu_id']." /></td>
						</tr>";
				/*$menu .="<div class='col-md-6'>
						<div class='col-md-6' style='margin-top:10px;'>
							<strong>".$rel_menu['menu_name']."</strong>
						</div>
						<div class='col-md-3'>
							<input type='checkbox' ".$chk." class='mainmenu".$i."' style='width: 31px;
    height: 25px;' name='menu_id[]' id=".$rel_menu['menu_id']." onClick='submenuactive();' value=".$rel_menu['menu_id']." />
						</div>
						<br><br>";*/
					$querysubmenu="select * from tbl_menu where status=0 and pid=".$rel_menu['menu_id'];
					$result_submenu=$dbcon->query($querysubmenu);	
					$k=1;
					while($rel_submenu=mysqli_fetch_assoc($result_submenu))
					{
						$chk1='';
						$querypermission1="select permission,add_permission,	other_permission,delete_permission,edit_permission,aprv_permission,final_aprv_permission from tbl_permission where usertype_id=".$POST['id']." and menu_id=".$rel_submenu['menu_id'];
						$rel_permission1=mysqli_fetch_assoc($dbcon->query($querypermission1));
						$cls='hidden';
						if($rel_submenu['incl_per']=='1'){
							$cls='';
						}
								
						$menu.="<tr>
						<td style='padding-left: 20px;'>".$rel_submenu['menu_name']."</td>
						<td class='text-center'><input type='checkbox' ".($rel_permission1['permission']==1?"checked='checked'":"")." class='submenu".$i."' style='width: 16px;
    height: 15px;' name='menu_id[]' id='cvw".$i."' value=".$rel_submenu['menu_id']." /></td>
    						<td class='text-center'><input type='checkbox' ".($rel_permission1['add_permission']==1?"checked='checked'":"")." class='addsubmenu".$i."' style='width: 16px;
    height: 15px;' name='addmenu_id[]' id='cedt".$i."' value=".$rel_submenu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission1['edit_permission']==1?"checked='checked'":"")." class='editsubmenu".$i."' style='width: 16px;
    height: 15px;' name='editmenu_id[]' id='cadd".$i."' value=".$rel_submenu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission1['delete_permission']==1?"checked='checked'":"")." class='deletesubmenu".$i."' style='width: 16px;
    height: 15px;' name='deletemenu_id[]'  id='cdel".$i."' value=".$rel_submenu['menu_id']." /></td>
    						<td class='text-center'><input type='checkbox' ".($rel_permission1['other_permission']==1?"checked='checked'":"")." class='othersubmenu".$i."' style='width: 16px;
    height: 15px;' name='othermenu_id[]' id='cother".$i."' value=".$rel_submenu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission1['aprv_permission']==1?"checked='checked'":"")." class='aprvsubmenu".$i." ".$cls."' style='width: 16px;
    height: 15px;' name='aprvmenu_id[]' id='caprv".$i."' value=".$rel_submenu['menu_id']." /></td>
							<td class='text-center'><input type='checkbox' ".($rel_permission1['final_aprv_permission']==1?"checked='checked'":"")." class='finalsubmenu".$i." ".$cls."' style='width: 16px;
    height: 15px;' name='finalmenu_id[]' id='cfnl".$i."' value=".$rel_submenu['menu_id']." /></td>
						</tr>
						";
						/*$menu .=	"<div class='col-md-5' style='font-size: 14px;'>-".$rel_submenu['menu_name']."</div>
						<div class='col-md-3' style='text-align:center'>
						<input type='checkbox' ".$chk1." class='submenu".$i."' style='width: 16px;
    height: 15px;' name='menu_id[]' id=".$rel_submenu['menu_id']." value=".$rel_submenu['menu_id']." />
							<br> </div>";*/
						$k++;
					}	
					//$menu .="<br><br><br><br></div>";
				$i++;
			}
			
				$menu .="<input type='hidden' name='totalmenu' id='totalmenu'  value=".$i." /></div>";
			echo $menu;
		}
	

?>