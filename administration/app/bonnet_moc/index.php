<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		//print_r($_POST);
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			//check permission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_BONNET_MOC_UPDATE,
		        ADMINISTRATOR_BONNET_MOC_DELETE
		    ]);

		    $branch_id = $POST['branch_id'];
			$where='';
		    if($branch_id){
		        $where .= check_branch('mst_d',$branch_id);
		    }
			
			$appData = array();
			$i=1;
			$aColumns = array('mst_d.bonnet_id', 'mst_d.bonnet_moc','bmst.branch_name','mst_d.bonnet_moc_status','mst_d.user_id','mst_d.is_deletable');
			$sIndexColumn = "mst_d.bonnet_id";
			$isWhere = array("mst_d.bonnet_moc_status = 0 and mst_d.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "bonnet_moc_mst as mst_d";			
			$isJOIN = array("left join branch_mst as bmst on bmst.branch_id=mst_d.branch_id");
			$hOrder = "mst_d.bonnet_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $id;
				$row_data[] = $row['bonnet_moc'];
				$row_data[] = $row['branch_name'];
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(ADMINISTRATOR_BONNET_MOC_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('.$row['bonnet_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_BONNET_MOC_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$row['bonnet_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add" || strtolower($POST['mode']) == "add_model") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `bonnet_id`,`bonnet_moc`,`bonnet_moc_status`,`company_id` FROM `bonnet_moc_mst` WHERE `bonnet_moc` ='".$POST['impregnation_name']."' and company_id = '".$_SESSION['company_id']."' and `impregnation_status`='0'");
			
			$cnt=mysqli_num_rows($tr);
			
			if($cnt>0) {
				$resp['msg'] = "-1";
			} else {
				$info['bonnet_moc']	= $_POST['bonnet_moc'];							
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$inserid=add_record('bonnet_moc_mst', $info, $dbcon, $branch_id);
				if($inserid) {
					if(strtolower($POST['mode']) == "add") {
						$resp['msg'] = "1";
					} else {
						$zone_qry="select * from bonnet_moc_mst where bonnet_id=".$inserid; 
						$zone_rel=mysqli_fetch_assoc($dbcon->query($zone_qry));
						$resp=$zone_rel;
						$resp['msg'] = "2";
					}
				} else {
					$resp['msg'] = "0";
				}
			}
			echo json_encode($resp);
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `bonnet_moc_mst` WHERE `bonnet_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `bonnet_id`,`bonnet_moc`,`bonnet_moc_status`,`company_id` FROM `bonnet_moc_mst` WHERE `bonnet_id` != '".$POST['eid']."' and company_id = '".$_SESSION['company_id']."' and `bonnet_moc` ='".$POST['bonnet_moc']."' and `bonnet_moc`='0'");
			if($tr->num_rows > 0) {
				echo "-1";
			} else {
				$info['bonnet_moc']	    = $_POST['bonnet_moc'];							
				$info['cdate']				= date("Y-m-d H:i:s");
				$info['user_id']			= $_SESSION['user_id'];
				$info['company_id']			= $_SESSION['company_id'];
				$updateid=update_record('bonnet_moc_mst', $info,"bonnet_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				$info['bonnet_moc_status']='2';
				$updateid=update_record('bonnet_moc_mst', $info,"bonnet_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			
		}
		
		
    }
  //  else {
    //    die("Error - 2");
    //}
}

//else {
   // die("Error - 1");
//}
?>
