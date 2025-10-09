<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

		if(strtolower($POST['mode']) == "fetch") {
			$where="";
			$branch_id = $POST['branch_id'];
		    if($branch_id){
		        $where .= check_branch('c',$branch_id);
		    }
			//check paermission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_UPDATE,
		        CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_DELETE
		    ]);
		 
			$appData = array();
			$i=1;
			$aColumns = array('c.id','c.terms_condition_category_name','c.status');
			$sIndexColumn = "c.id";
			$isWhere = array("c.status = 0 ".$where." and c.company_id in (0,$_SESSION[company_id])");
			$sTable = "terms_condition_category_type as c";			
			$isJOIN = array();
			$hOrder = "c.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['terms_condition_category_name'];
				if($row['status']=='0'){
					$row_data[] = '<div class="external-event label label-success ui-draggable" style="cursor:auto;">Active</div>';
				}else{
					$row_data[] = '<div class="external-event label label-error ui-draggable" style="cursor:auto; background-color: #d9534f !important;">In Active</div>';
				}
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_terms_condition_category_type('.$row['id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(CUSTOMER_TERMS_CONDITION_CATEGORY_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_terms_condition_category_type('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				$row_data[] = $edit_btn.' '.$delete_btn; 
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `terms_condition_category_name`,`status`,`company_id` FROM `terms_condition_category_type` WHERE `company_id` = '".$_SESSION['company_id']."' and `terms_condition_category_name` ='".$POST['terms_condition_category_name']."' and status='0'");
			if($tr->num_rows > 0) {
				echo '-1';
			}else {
				$info['terms_condition_category_name']	= $POST['terms_condition_category_name'];			
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['status']	= $POST['status'];
				$info['created_at'] = date("Y-m-d H:i:s");
				$inserid=add_record('terms_condition_category_type', $info, $dbcon, $branch_id);
				if($inserid)
					echo "1";
				else
					echo "0";
			}
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon->query("SELECT * FROM `terms_condition_category_type` WHERE `id` = '".$POST['id']."'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `terms_condition_category_name`,`status` FROM `terms_condition_category_type` WHERE `company_id` = '".$_SESSION['company_id']."' and `terms_condition_category_name` ='".$POST['terms_condition_category_name']."' and status='0' and id != '".$POST['eid']."'");
			if($tr->num_rows > 0) {
				echo '-1';
			} else {
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$info['terms_condition_category_name']	= $POST['terms_condition_category_name'];
				$info['status']	= $POST['status'];			
				$info['created_at'] = date("Y-m-d H:i:s");
				$updateid=update_record('terms_condition_category_type', $info,"id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']='2';
			$updateid=update_record('terms_condition_category_type', $info,"id=".$POST['eid'] , $dbcon);
			
			if($updateid)
				echo "1";
			else
				echo "0";
			
		}
 
?>