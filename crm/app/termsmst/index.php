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

			$branch_id = $POST['branch_id'];
			$where='';
		    if($branch_id){
		        $where .= check_branch('termscondi',$branch_id);
		    }
			// check permission for terms and condition
		    $bulkAccessArray = canCheckPermissionAccess($dbcon, [
		        CUSTOMER_TERMS_CONDITION_SLUG_UPDATE,
		        CUSTOMER_TERMS_CONDITION_SLUG_DELETE
		    ]);
		 
			$appData = array();
			$i=1;
			$aColumns = array('termscondi.tc_name','termscondi.tc_details','termscondi.tc_for','termscondi.tc_priority','termscondi.tc_status','termscondi.tc_id', 'termscondi.user_id','termscondi.company_id');
			$sIndexColumn = "tc_id";
			$isWhere = array("termscondi.tc_status = 0 and termscondi.company_id in (0,$_SESSION[company_id])".$where);
			$sTable = "tbl_terms_condition as termscondi";			
			$isJOIN = array();
			$hOrder = "termscondi.tc_id desc";
			include('../../../include/pagging.php');
			$appData = array();
			$id=1;
			foreach($sqlReturn as $row) {
				$row_data = array();
				$row_data[] = $row['sr'];
				$row_data[] = $row['tc_name'];
				$row_data[] = nl2br($row['tc_details'],false);
				
				// $tc_for_name='';
				$for_arr=explode(",",$row['tc_for']);
				if(in_array('0',$for_arr)){
					$tc_for_name[]='DOMESTIC';
				}
				if(in_array('1',$for_arr)){
					$tc_for_name[]=' EXPORT';
				}
				$row_data[] = implode(",",$tc_for_name);
				
				$edit_btn=''; $delete_btn='';  
				if(in_array(CUSTOMER_TERMS_CONDITION_SLUG_UPDATE,$bulkAccessArray)){
					$edit_btn=' <a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.CRM_ROOT.'terms_edit/'.$row['tc_id'].'"><i class="fa fa-pencil"></i></a>'; 
				}
				if(in_array(CUSTOMER_TERMS_CONDITION_SLUG_DELETE,$bulkAccessArray)){
					$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_data('.$row['tc_id'].')"><i class="fa fa-trash-o"></i></button>'; 
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
			
			$tr = $dbcon -> query("SELECT `tc_id`,`tc_name`,`tc_status` FROM `tbl_terms_condition` WHERE `tc_name` ='".$POST['term_name']."' and tc_status='0'");
			if($tr->num_rows > 0) {
				
				echo '-1';
				
			}
			else {
				if(isset($POST['allow_change'])){ $allow="1"; } else { $allow="0"; }
				
				$info['tc_name']			= $_POST['term_name'];							
				$info['print_name']		= $_POST['print_name'];							
				$info['tc_priority']	= $POST['term_priority'];							
				$info['tc_category']	= $_POST['term_category'];							
				$info['tc_details']	= $_POST['terms_details'];
				
				//var_dump($_POST['term_for']);
				if(!empty($_POST['term_for'])){							
					$info['tc_for']	=  implode(",",$_POST['term_for']);
				}

				$info['tc_allow']	= 	$allow;						
				$info['cdate']		= date("Y-m-d H:i:s");
				$info['user_id']	= $_SESSION['user_id'];
				$info['company_id']	= $_SESSION['company_id'];
				// var_dump($info);die;
				$inserid=add_record('tbl_terms_condition', $info, $dbcon, $branch_id);
				//Insert LOG
				$log_entry=common_log_entry($dbcon,"terms_add",1,"tbl_terms_condition",$inserid);
			
				if($inserid)
					echo "1";
				else
					echo "0";
			}
		
		}
		else if(strtolower($POST['mode']) == "preedit") {			
			$q = $dbcon -> query("SELECT * FROM `tbl_terms_condition` WHERE `tc_id` = '$POST[id]'");
			$r = $q->fetch_assoc();
			echo json_encode($r);
		}
		else if(strtolower($POST['mode']) == "edit") {

			$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

			if(isset($POST['allow_change'])){ $allow="1"; } else { $allow="0"; }
				
			$info['tc_name']			= $_POST['term_name'];							
			$info['print_name']		= $_POST['print_name'];							
			$info['tc_priority']	= $POST['term_priority'];							
			$info['tc_category']	= $_POST['term_category'];							
			$info['tc_details']		= $_POST['terms_details'];	
			//var_dump($_POST['term_for']);		
			if(!empty($_POST['term_for'])){
				$info['tc_for']	=  implode(",",$_POST['term_for']);
			}				
										
			$info['tc_allow']		= 	$allow;													
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['branch_id']	= $_SESSION['branch_id'];
			$updateid=update_record('tbl_terms_condition', $info,"tc_id=".$POST['eid'] , $dbcon, $branch_id);
			
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"terms_add",2,"tbl_terms_condition",$POST['eid']);

			
			if($updateid)
				echo "update";
			else
				echo "0".$dbcon->error;
			
		}
		else if(strtolower($POST['mode']) == "delete") {
			
			$info['tc_status']='2';
			$updateid=update_record('tbl_terms_condition', $info,"tc_id=".$POST['eid'] , $dbcon);
				
			//Insert LOG
			$log_entry=common_log_entry($dbcon,"terms_add",3,"tbl_terms_condition",$POST['eid']);
			
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