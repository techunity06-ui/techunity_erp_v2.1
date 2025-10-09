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
		    	ADMINISTRATOR_TYPE_MST_UPDATE,
		        ADMINISTRATOR_TYPE_MST_DELETE
		    ]);

		    $branch_id = $POST['branch_id'];
			$where='';
		    if($branch_id){
		        $where .= check_branch('qcparam',$branch_id);
		    }
			
			$appData = array();
			$i=1;
			$aColumns = array('qcparam.type_id', 'qcparam.type_name','qcparam.code','bmst.branch_name','qcparam.type_status','qcparam.user_id','qcparam.is_deletable');
			$sIndexColumn = "qcparam.type_id";
			$isWhere = array("qcparam.type_status = 0 and qcparam.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "mst_type as qcparam";			
			$isJOIN = array("left join branch_mst as bmst on bmst.branch_id=qcparam.branch_id");
			$hOrder = "qcparam.type_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['type_name'];
				$row_data[] = $row['code'];
				$row_data[] = $row['branch_name'];
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(ADMINISTRATOR_TYPE_MST_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_parameter('.$row['type_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_TYPE_MST_DELETE,$bulkAccessArray) && $row['is_deletable']=='0'){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_parameter('.$row['type_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
			
			$tr = $dbcon -> query("SELECT `type_id`,`type_name`,`type_status`,`company_id` FROM `mst_type` WHERE `type_name` ='".$POST['type_name']."' and company_id = '".$_SESSION['company_id']."' and `type_status`='0'");
			
			$cnt=mysqli_num_rows($tr);
			
			if($cnt>0) {
				$resp['msg'] = "-1";
			} else {
				$info['type_name']	= $_POST['type_name'];							
				$info['code']		= $_POST['code'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$inserid=add_record('mst_type', $info, $dbcon, $branch_id);
				if($inserid) {
					if(strtolower($POST['mode']) == "add") {
						$resp['msg'] = "1";
					} else {
						$zone_qry="select * from mst_type where type_id=".$inserid; 
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
			$q = $dbcon -> query("SELECT * FROM `mst_type` WHERE `type_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `type_id`,`type_name`,`type_status`,`company_id` FROM `mst_type` WHERE `type_id` != '".$POST['eid']."' and company_id = '".$_SESSION['company_id']."' and `type_name` ='".$POST['type_name']."' and `type_status`='0'");
			if($tr->num_rows > 0) {
				echo "-1";
			} else {
				$info['type_name']	= $_POST['type_name'];							
				$info['code']		= $_POST['code'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('mst_type', $info,"type_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				$info['type_status']='2';
				$updateid=update_record('mst_type', $info,"type_id=".$POST['eid'] , $dbcon);
				
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
