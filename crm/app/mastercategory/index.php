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
			//check paermission for party industry add
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		    	CUSTOMER_MASTER_CATEGORY_SLUG_UPDATE,
		        CUSTOMER_MASTER_CATEGORY_SLUG_DELETE
		    ]);

			$cat=$POST['cat'];
			
			$where="";

			// $branch_id = $POST['branch_id'];
		
		 //    if($branch_id){
		 //        $where .= check_branch('m',$branch_id);
		 //    }

			if($cat!='')
			{
				$where.=" and mcd_cat_id='$cat'";
			}
		 
			$appData = array();
			$i=1;
			$aColumns = array('c.mc_name','m.mcd_name','priority','m.mcd_status','m.user_id','c.mc_id','m.mcd_cat_id','m.mcd_id','m.company_id');
			$sIndexColumn = "m.mcd_cat_id";
			$isWhere = array("m.mcd_status = 0 ".$where."  and m.company_id in (0,$_SESSION[company_id])");
			$sTable = "tbl_master_category_detail as m";			
			$isJOIN = array('left join  tbl_master_category as c on c.mc_id=m.mcd_cat_id');
			$hOrder = "m.mcd_cat_id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['mcd_name'];
				$row_data[] = $row['mc_name'];
				$row_data[] = $row['priority'];
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(CUSTOMER_MASTER_CATEGORY_SLUG_UPDATE,$bulkAccessArray)){ 
					$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_category('.$row['mcd_id'].');"><i class="fa fa-pencil"></i></button>'; 
				}
				if(in_array(CUSTOMER_MASTER_CATEGORY_SLUG_DELETE,$bulkAccessArray)){ 
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_category('.$row['mcd_id'].')"><i class="fa fa-trash-o"></i></button>'; 
				}
				if($row['mc_id'] != 10){
					$row_data[] = $edit_btn.' '.$delete_btn; 
				}else{
					$row_data[] = '';
				}
				$appData[] = $row_data;
				$id++;
			}
			$output['aaData'] = $appData;
			echo json_encode( $output );
		}
		else if(strtolower($POST['mode']) == "add") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `mcd_name`,`mcd_cat_id`,`mcd_status` FROM `tbl_master_category_detail` WHERE `mcd_name` ='".$POST['master_cat_name']."' and `mcd_cat_id` ='".$POST['master_cat']."' and mcd_status='0' and company_id = '".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				
				echo '-1';
				
			}
			else {
				$info['mcd_cat_id']	= $POST['master_cat'];							
				$info['mcd_name']	= $POST['master_cat_name'];							
				$info['priority']	= $POST['priority'];							
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				
				$inserid=add_record('tbl_master_category_detail', $info, $dbcon, $branch_id);
				if($inserid)
					echo "1";
				else
					echo "0";
			}
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_master_category_detail` WHERE `mcd_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {
			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			$tr = $dbcon -> query("SELECT `mcd_id`, `mcd_name`,`mcd_cat_id`,`mcd_status` FROM `tbl_master_category_detail` WHERE `mcd_name` ='".$POST['master_cat_name']."' and `mcd_cat_id` ='".$POST['master_cat']."' and mcd_status='0' and mcd_id != '".$POST['eid']."' and company_id = '".$_SESSION['company_id']."'");
			if($tr->num_rows > 0) {
				echo '-1';
			} else {
				$info['mcd_cat_id']	= $POST['master_cat'];							
				$info['mcd_name']	= $POST['master_cat_name'];								
				$info['priority']	= $POST['priority'];								
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				$updateid=update_record('tbl_master_category_detail', $info,"mcd_id=".$POST['eid'] , $dbcon, $branch_id);
				if($updateid)
					echo "1";
				else
					echo "0".$dbcon->error;
			}
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			
				$info['mcd_status']='2';
				$updateid=update_record('tbl_master_category_detail', $info,"mcd_id=".$POST['eid'] , $dbcon);
				
				if($updateid)
					echo "1";
				else
					echo "0";
			
		}
		else if(strtolower($POST['mode']) == "get_master_category_dropdown_data") {
			echo get_master_category($dbcon,0);
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