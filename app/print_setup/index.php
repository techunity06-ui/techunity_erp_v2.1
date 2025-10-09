<?php
session_start();
$AJAX = true;
include("../../config/config.php");
//error_reporting(E_ALL);
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
	$appData = array();
	$i=1;
	$aColumns = array('ps.id', 'pt.print_type_name','ps.print_name','ps.fa_icon','ps.page_path','ps.priority','ps.approve_status','ps.status','ps.user_id','ps.cdate');
	$sIndexColumn = "ps.id";
	$isWhere = array("ps.status != 2 and ps.company_id IN (0,$_SESSION[company_id])");
	$sTable = "print_setup_mst as ps";			
	$isJOIN = array('left join print_type_mst as pt on pt.id=ps.print_type');
	$hOrder = "ps.id DESC";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['print_type_name'];
		$row_data[] = $row['print_name'];
		$row_data[] = $row['fa_icon'];
		$row_data[] = $row['page_path'];
		$row_data[] = $row['priority'];
		if($row['approve_status']==1){
			$ap_status=0;
			$row_data[] = '<button class="btn btn-xs btn-primary" data-original-title="Approved" data-toggle="tooltip" data-placement="top">Approved</button>';
		}else{
			$ap_status=1;
			$row_data[] = '<button class="btn btn-xs btn-warning" data-original-title="Pending" data-toggle="tooltip" data-placement="top">Pending</button>';
		}
		$app_btn = '<button class="btn btn-xs btn-warning" data-original-title="Approve status" data-toggle="tooltip" data-placement="top" onClick="approve_status('.$row['id'].','.$ap_status.');"><i class="fa fa-times"></i></button>';
		$row_data[]='<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.'/print_setup_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>
		<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['id'].')"><i class="fa fa-trash-o"></i></button> '.$app_btn;

		$appData[] = $row_data;
		$id++;
	}

	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {

	$info['print_name']	= $_POST['print_name'];	
	$info['print_type']	= $_POST['print_type'];	
	$info['page_path']	= strtolower($_POST['page_path']);	
	$info['priority']		= $_POST['priority'];							
	$info['fa_icon']	= $_POST['fa_icon'];							
	$info['status']	= $_POST['status'];
	$info['icon_color']	= $_POST['icon_color'];
	$info['with_out_logo']	= $_POST['with_out_logo'];
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['branch_id']	= $_SESSION['branch_id'];
	$inserid=add_record('print_setup_mst', $info, $dbcon);

	if($inserid)
		$arr['msg']="1";
	else
		$arr['msg']="0";
	echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "preedit") {		
	$q = $dbcon -> query("SELECT * FROM `print_setup_mst` WHERE `id` = '".$_POST['id']."'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($_POST['mode']) == "edit") {

	$info['print_name']	= $_POST['print_name'];	
	$info['print_type']	= $_POST['print_type'];	
	$info['page_path']	= strtolower($_POST['page_path']);	
	$info['priority']		= $_POST['priority'];							
	$info['fa_icon']	= $_POST['fa_icon'];
	$info['status']	= $_POST['status'];
	$info['with_out_logo']	= $_POST['with_out_logo'];
	$info['icon_color']	= $_POST['icon_color'];
	$info['user_id']	= $_SESSION['user_id'];
	$info['company_id']	= $_SESSION['company_id'];
	$info['branch_id']	= $_SESSION['branch_id'];
	$updateid=update_record('print_setup_mst', $info,"id=".$_POST['eid'] , $dbcon);
	if($updateid)
		$arr['msg']="2";
	else
		$arr['msg']="0";
	echo json_encode($arr);
}
else if(strtolower($_POST['mode']) == "delete") {

	$info['status']='2';
	$updateid=update_record('print_setup_mst', $info,"id=".$_POST['eid'] , $dbcon);

	if($updateid)	
		echo "1";
	else
		echo "0";

}
else if(strtolower($_POST['mode']) == "approve_status") {
	$info['approve_status']=$_POST['approve_status'];
	$updateid=update_record('print_setup_mst', $info,"id=".$_POST['eid'] , $dbcon);

	if($updateid)	
		echo "1";
	else
		echo "0";
}
else if(strtolower($POST['mode']) == "printsetup_permission_add") {
	$q = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND company_id='".$_SESSION['company_id']."'");
	if($q->num_rows > 0){
		$record = brp_mysqli_fetch_assoc($q);

		$print_permission = array_values(array_filter($_POST['print_permission']));
		$info['print_permission'] = implode(",",$print_permission);							
		$info['status']	= 0;
		$info['user_id']	= $_SESSION['user_id'];

		$updateid=update_record('print_permission', $info,"id=".$record['id'] , $dbcon);
	}else{
		$print_permission = array_values(array_filter($_POST['print_permission']));
		$info['print_permission'] = implode(",",$print_permission);						
		$info['status']	= 0;
		$info['user_id']	= $_SESSION['user_id'];
		$info['company_id']	= $_SESSION['company_id'];

		$updateid=add_record('print_permission', $info, $dbcon);
	}
	if($updateid)
		$arr['msg']="1";
	else
		$arr['msg']="0";

	echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "show_print_menu") {
	$menu ='';
	$where ='';
	$sql = $dbcon->query("SELECT * FROM `print_permission` WHERE `status` = '0' AND `company_id`='".$_SESSION['company_id']."'");
	$rels=mysqli_fetch_assoc($sql);
	$menu_show_permission = explode(",",$rels['print_permission']);
	$quserdata = $dbcon->query("SELECT * FROM `print_type_mst` WHERE `print_status` = '0'");
	//$recorduserData = $quserdata->fetch_assoc();

	$menu='<table class="display table table-bordered table-striped dataTable" id="dynamic-table" aria-describedby="dynamic-table_info" width="100%">
	<thead>
	<tr>
	<th class="text-center myHeader">Module Name</th>
	<th class="text-center myHeader">Menu Show</th>
	</tr>
	</thead>
	';
	if(brp_mysqli_num_rows($quserdata) > 0){
		$d=1;$i=1;
		while($row=brp_mysqli_fetch_assoc($quserdata)){
			$qry=$dbcon->query("SELECT * FROM `print_setup_mst` WHERE `status` = '0' AND `approve_status` = 1 AND print_type='".$row['id']."' AND company_id = '".$_SESSION['company_id']."' ORDER BY priority ASC");
			if(mysqli_num_rows($qry) > 0){
				$menu.='<tr class="headerRow" style="border-top: 2px solid;">
				<td><strong>'.$row['print_type_name'].'</strong></td>
				<td></td>
				</tr>';
				while($res=mysqli_fetch_assoc($qry)){
					if(in_array($res['id'], $menu_show_permission)){
						$mcheckCls = 'checked';
					}else{
						$mcheckCls = '';
					}
					$menu.='<tr class="sub_'.$d.'">
					<td>'.$res['print_name'].'</td>
					<td><input type="checkbox" style="width: 31px; height: 25px;" name="print_permission[]" value="'.$res['id'].'" class="mainChk vw'.$res['id'].'" data-id="'.$res['id'].'" '.$mcheckCls.'/></td>
					</tr>';
					$i++;
				}
				$d++;
			}
		}
	}
	$menu .='<input type="hidden" name="totalmenu" id="totalmenu"  value='.$i.' />
	</table>';
	echo $menu;
}
?>