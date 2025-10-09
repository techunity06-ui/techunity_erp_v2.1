<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
} else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//check permission for party industry add
	    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	ADMINISTRATOR_PROCESS_UPDATE,
	        ADMINISTRATOR_PROCESS_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
		$where='';
	    
	    if($branch_id != '1000'){
	        $where .= check_branch('zmst',$branch_id);
	    }
	    
	    if($branch_id == ""){
	    	 $output = array(
		        "sEcho" => 1,
		        "iTotalRecords" => 0,
		        "iTotalDisplayRecords" => 0,
		        "aaData" => array()
		    );
	     	
	     	echo json_encode( $output );
	     }else{
			
		$appData = array();
		$i=1;
		$aColumns = array('zmst.process_id', 'zmst.process_name','zmst.process_type','pt.process_type_name','zmst.dashbord_priority','zmst.cdate', 'zmst.process_status', 'zmst.user_id');
		$sIndexColumn = "zmst.process_id";
		$isWhere = array("zmst.process_status = 0 and zmst.company_id in (0,$_SESSION[company_id])".$where);
		$sTable = "process_mst as zmst";			
		$isJOIN = array('inner join process_type_mst as pt on pt.process_type_id=zmst.process_type');
		$hOrder = "zmst.process_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['process_name']; 
			$row_data[] = $row['process_type_name']; 
			$row_data[] = $row['dashbord_priority']; 


			$is_deletable = 1;

			$pr_qry = "SELECT count(pr_process_id) as used_process FROM tbl_product_process WHERE status = 0 AND process_id = " . $row['process_id'];
			$result_pr =  $dbcon->query($pr_qry);

			$pr_row = brp_mysqli_fetch_assoc($result_pr);

			if($pr_row['used_process'] > 0){
				$is_deletable = 0;					
			}

			
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_PROCESS_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_process('.$row['process_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_PROCESS_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_process('.$row['process_id'].','.$is_deletable.')"><i class="fa fa-trash-o"></i></button>';
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	}
	else if(strtolower($POST['mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `company_id`,`process_id`,`process_name`,`process_status` FROM `process_mst` WHERE process_status=0 and `company_id` = '".$_SESSION['company_id']."' and `process_name` ='".$POST['process_name']."'");
		if($tr->num_rows > 0) {
			$resp['msg'] = '-1';
		}
		else {
			$info['process_name']	= $POST['process_name'];							
			$info['process_type']	= $POST['process_type'];							
			$info['dashbord_priority']	= $POST['dashbord_priority'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$inserid=add_record('process_mst', $info, $dbcon, $branch_id);
			
			$info_m['user_id'] = $_SESSION['user_id'];
            $info_m['parent_id'] = '104';
            $info_m['process_id'] = $inserid;
            $info_m['menu_name'] = $info['process_name'];
            $info_m['menu_path'] = '#';
            $info_m['menu_description'] = $info['process_name'];
            $info_m['menu_order'] = $inserid;
            $info_m['menu_fa_icon'] = 'FA-DASHBOARD';
            $info_m['report_status_flag'] = 'Yes';
            $info_m['status'] = '0';
            $insertid = add_record('menu_master_access', $info_m, $dbcon);
            
            $infoRoutes['user_id'] = $_SESSION['user_id'];
            $infoRoutes['access_id'] = $insertid;
            $infoRoutes['access_type'] = 'V';
            $infoRoutes['slug_name'] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($info['process_name']));
            $infoRoutes['route_path_name'] = '0';
            $infoRoutes['status'] = '0';
            $insertRoutesid = add_record('menu_master_access_routes', $infoRoutes, $dbcon);

			if($inserid){
				$resp['msg'] = "1";
				if(strtolower($POST['process_model']) == "process_model"){
					$process_qry="select * from process_mst where process_id=".$inserid; 
					$process_rel=mysqli_fetch_assoc($dbcon->query($process_qry));
					$resp=$process_rel;
					$resp['msg'] = "2"; 
				}
			}
			else{
				$resp['msg'] = "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `process_mst` WHERE `process_id` = '$POST[process_id]'");
		$r = $q->fetch_assoc();
		
		$r['process_type_list']=get_process_type($dbcon,'');
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `company_id`,`process_id`,`process_name`,`process_status` FROM `process_mst` WHERE process_status=0 and `company_id` = '".$_SESSION['company_id']."' and `process_name` ='".$POST['process_name']."' and `process_id` != '".$POST['eid']."'");
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['process_name']	= $POST['process_name'];							
			$info['process_type']	= $POST['edit_process_type'];							
			$info['dashbord_priority']	= $POST['edit_dashbord_priority'];							
			$info['cdate']		= date("Y-m-d H:i:s");
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$updateid=update_record('process_mst', $info,"process_id=".$POST['eid'] , $dbcon, $branch_id);

			$info_m['user_id'] = $_SESSION['user_id'];
            $info_m['parent_id'] = '104';
            $info_m['process_id'] = $POST['eid'];
            $info_m['menu_name'] = $info['process_name'];
            $info_m['menu_path'] = '#';
            $info_m['menu_description'] = $info['process_name'];
            $info_m['menu_order'] = $POST['eid'];
            $info_m['menu_fa_icon'] = 'FA-DASHBOARD';
            $info_m['report_status_flag'] = 'Yes';
            $info_m['status'] = '0';
            $update_master_id = update_record('menu_master_access', $info_m, "process_id=".$POST['eid'] , $dbcon);

            $qum = $dbcon->query("SELECT * FROM `menu_master_access` WHERE `process_id` = '$POST[eid]'");
			$menu_data = $qum->fetch_assoc();
            
            $infoRoutes['user_id'] = $_SESSION['user_id'];
            $infoRoutes['access_id'] = $menu_data['id'];
            $infoRoutes['access_type'] = 'V';
            $infoRoutes['slug_name'] = 'dashboard-inhouse-'.str_replace(' ', '-', strtolower($info['process_name']));
            $infoRoutes['route_path_name'] = '0';
            $infoRoutes['status'] = '0';
            $insertRoutesid = update_record('menu_master_access_routes', $infoRoutes, "access_id=".$menu_data['id'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error; 
		}
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['process_status']='2';
		$updateid=update_record('process_mst', $info,"process_id=".$POST['process_id'] , $dbcon);
		
		$infom['status']='2';
		$deletemasterid=update_record('menu_master_access', $infom,"process_id=".$POST['process_id'], $dbcon);
		$qum = $dbcon->query("SELECT * FROM `menu_master_access` WHERE `process_id` = '$POST[process_id]'");
		$menu_data = $qum->fetch_assoc();
		$deleteroutesmasterid=update_record('menu_master_access_routes', $infom,"access_id=".$menu_data['id'], $dbcon);

		if($updateid)
			echo "1";
		else
			echo "0"; 
	}
	
	
?>