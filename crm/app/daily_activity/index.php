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

			
			$where='';
			$branch_id = $POST['branch_id'];
		    if($branch_id){
		        $where .= check_branch('dailyactivity',$branch_id);
		    }

		    if($_SESSION['user_type'] == '2'){
			    $user_id = $POST['user_id'];
			    if($user_id){
			        $where .= " and dailyactivity.user_id = '".$user_id."'";
			    }
			}else{
				$user_id = $_SESSION['user_id'];
				$where .= " and dailyactivity.user_id = '".$user_id."'";
			}
			// check permission for terms and condition
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		        CUSTOMER_DAILY_UPDATE_SLUG_UPDATE,
		        CUSTOMER_DAILY_UPDATE_SLUG_DELETE
		    ]);
		 
			$appData = array();
			$i=1;
			$aColumns = array('user.user_name','dailyactivity.daily_activity_date','dailyactivity.description','dailyactivity.status','dailyactivity.id', 'dailyactivity.user_id','dailyactivity.company_id');
			$sIndexColumn = "id";
			$isWhere = array("dailyactivity.status = 0 and dailyactivity.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_daily_activity_log as dailyactivity";			
			$isJOIN = array('left join users as user on user.user_id=dailyactivity.user_id');
			$hOrder = "dailyactivity.id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['user_name'];
				$row_data[] = $row['daily_activity_date'];
				$row_data[] = nl2br($row['description'],false);
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(CUSTOMER_DAILY_UPDATE_SLUG_UPDATE,$bulkAccessArray)){
					$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'daily_activity_edit/'.$row['id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
				if(in_array(CUSTOMER_DAILY_UPDATE_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
			
			$tr = $dbcon->query("SELECT `id`,`user_id`,`daily_activity_date`,`description`,`status` FROM `tbl_daily_activity_log` WHERE `daily_activity_date` ='".date('Y-m-d',strtotime($POST['daily_activity_date']))."' and user_id = '".$_SESSION['user_id']."' and status='0'");
			if($tr->num_rows > 0) {
				echo '-1';
			}else {
				$info['daily_activity_date'] = date('Y-m-d');
				$info['description']	= $_POST['description'];											
				$info['created_at']		= date("Y-m-d H:i:s");
				$info['updated_at']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				
				$insertid=add_record('tbl_daily_activity_log', $info, $dbcon, $branch_id);
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"daily_activity_add",1,"tbl_daily_activity_log",$insertid);
			
				if($insertid)
					echo "1";
				else
					echo "0";
			}
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_daily_activity_log` WHERE `id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$info['daily_activity_date'] = date('Y-m-d');
			$info['description']	= $_POST['description'];											
			$info['updated_at']		= date("Y-m-d H:i:s");										
			$info['user_id']	= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $_SESSION['branch_id'];
			$updateid=update_record('tbl_daily_activity_log', $info,"id=".$POST['eid'] , $dbcon, $branch_id);
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"daily_activity_edit",2,"tbl_daily_activity_log",$POST['eid']);

			if($updateid)
				echo "update";
			else
				echo "0".$dbcon->error;
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			$info['status']='2';
			$updateid=update_record('tbl_daily_activity_log', $info,"id=".$POST['eid'] , $dbcon);
				
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"daily_activity_add",3,"tbl_daily_activity_log",$POST['eid']);
			
			if($updateid)
				echo "1";
			else
				echo "0";
			
		}
    }
}
?>
