<?php
session_start(); //start session
$AJAX = true;

include('../../include/urlfileinner.php');

//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') 
{ 
  //  if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) 
	{
		if($_POST != NULL) {
			$POST = bulk_filter($dbcon,$_POST);
		}
		else {
			$POST = bulk_filter($dbcon,$_GET);
		}
		
		if(strtolower($POST['mode']) == "fetch") {
			//check permission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	ADMINISTRATOR_CATEGORY_UPDATE,
		        ADMINISTRATOR_CATEGORY_DELETE
		    ]);
		  
			$where='';

		//branch , company, user check start - dhaval 
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$where_db = check_branch('tblcat', $branch_id);
		
		$where.=" $where_db";

		$where_company=check_company('tblcat');

		$where.=" $where_company";

	//	$where_user=check_user('tblcat');

		$where.=" $where_user";
		// branch , comapny , user check end - dhaval
		 
		 
			$appData = array();
			$i=1;
			$aColumns = array('tblcat.cat_id', 'tblcat.cat_name','tblcat.cat_pid', 'tblcat.cat_status','tblcat.user_id');
			$sIndexColumn = "tblcat.cat_id";
			$isWhere = array("tblcat.cat_status = 0 and tblcat.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_category as tblcat";			
			$isJOIN = array();
			$hOrder = "tblcat.cat_id desc";
			include($include.'pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['cat_name'];
				if($row['cat_pid'] == 0){
					$row_data[] = 'PRIMARY';
				}else{
					$row_data[] = get_category_by_id($dbcon,$row['cat_pid']);
				}
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(ADMINISTRATOR_CATEGORY_UPDATE,$bulkAccessArray)){
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_category('.$row['cat_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(ADMINISTRATOR_CATEGORY_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_category('.$row['cat_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
			//echo $POST['branch_id'];exit;
			$tr = $dbcon -> query("SELECT `cat_id`,`cat_name`,`cat_status`,`company_id` FROM `tbl_category` WHERE `cat_name` ='".$POST['cat_name']."' and `company_id` = '".$_SESSION['company_id']."' and `cat_pid` ='".$POST['cat_parent']."' and `cat_status`=0");
			if($tr->num_rows > 0) {
				$resp['msg'] = "-1";
			} else {
				$info['cat_name']	= $_POST['cat_name'];							
				$info['cat_pid']	= $_POST['cat_parent'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$inserid=add_record('tbl_category', $info, $dbcon, $branch_id);
				if($inserid){
					$resp['msg'] = "1";
					$resp['product_add_type'] = $_POST['product_add_type']; 
					$resp['direct_product_add'] = $_POST['direct_product_add'];
					$resp['category_name'] = $_POST['cat_name']; 
					$resp['inserid']=$inserid;
				}
				else{
					$resp['msg'] = "0";
				}
			}
			echo json_encode($resp);
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_category` WHERE `cat_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
			
			$tr = $dbcon -> query("SELECT `cat_id`,`cat_name`,`cat_status`,`company_id` FROM `tbl_category` WHERE `cat_name` ='".$POST['e_cat_name']."' and `company_id` = '".$_SESSION['company_id']."' and `cat_pid` ='".$POST['e_cat_parent']."' and `cat_id` != '".$POST['eid']."' and `cat_status`=0");
			if($tr->num_rows > 0) {
				echo '-1';
			} else {
				$info['cat_name']	= $_POST['e_cat_name'];							
				$info['cat_pid']	= $_POST['e_cat_parent'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('tbl_category', $info,"cat_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				$info['cat_status']='2';
				$updateid=update_record('tbl_category', $info,"cat_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			
		}
		else if(strtolower($POST['mode']) == "get_category_dropdown_data") {
			echo get_all_category($dbcon,$POST['id']);
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