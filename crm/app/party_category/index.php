<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');

//print_r($_POST);
if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}
	
	if(strtolower($POST['mode']) == "fetch") {
		//$edit_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'edit',$dbcon);
		//$delete_btn_per=check_permission($_SESSION['page'],$_SESSION['user_id'],'delete',$dbcon);
		
		$bulkAccessArray = canCheckPermissionAccess($dbcon, [
	    	PARTY_CATEGORY_SLUG_UPDATE,
	        PARTY_CATEGORY_SLUG_DELETE
	    ]);
		
		$appData = array();
		$i=1;
		$aColumns = array('cc_id', 'cc_name','cc_status');
		$sIndexColumn = "cc_id";
		$isWhere = array("cc_status = 0");
		$sTable = "tbl_customer_category";			
		$isJOIN = array();
		$hOrder = "cc_id desc";
		include('../../../include/pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['cc_name'];
			
			$edit_btn=''; $delete_btn='';  
			if(in_array(PARTY_CATEGORY_SLUG_UPDATE,$bulkAccessArray)){
				$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_source_mst('.$row['cc_id'].');"><i class="fa fa-pencil"></i></button>'; 
			}
			if(in_array(PARTY_CATEGORY_SLUG_DELETE,$bulkAccessArray)){
				$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_source_mst('.$row['cc_id'].')"><i class="fa fa-trash-o"></i></button>'; 
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn; 
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	}
	else if(strtolower($POST['mode']) == "add") {
		
		$tr = $dbcon -> query("SELECT cc_id,cc_name,cc_status FROM `tbl_customer_category` WHERE cc_name LIKE '".$POST['party_cat']."' and cc_status='0'");
		if($tr->num_rows > 0) {
			
			$resp['resp']= '-1';
			
		}
		else {
			$info['cc_name']	= $POST['party_cat'];	
			$inserid=add_record('tbl_customer_category', $info, $dbcon);
			
			if($inserid){
				//Insert LOG
				
				if(strtolower($POST['source_mst_model']) == "source_mst_model"){
					$sel_qry = "select * from tbl_customer_category where cc_id=".$inserid;
					$sel_rel = mysqli_fetch_assoc($dbcon->query($sel_qry));
					$resp = $sel_rel;
					$resp['resp'] = "2";
				}
				else{
					$resp['resp']= "1";
				}
			}
			else{
				$resp['resp']= "0";
			}
		}
		echo json_encode($resp);
	}
	else if(strtolower($POST['mode']) == "preedit") {			
		$q = $dbcon -> query("SELECT * FROM `tbl_customer_category` WHERE `cc_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	else if(strtolower($POST['mode']) == "edit") {
		
		$info['cc_name']	= $POST['e_party_cat'];		
		$updateid=update_record('tbl_customer_category', $info,"cc_id=".$POST['eid'] , $dbcon);
				
		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
		
	}
	else if(strtolower($POST['mode']) == "delete") {
		//var_dump($POST['eid']);
		
		//check Entry Record in TRN tables
		/*$chk_arr[]=array("inquiry_id","tbl_inquiry","inquiry_status=0 and rb_id=".$POST['eid']);
		$chk_resp=check_delete_trn($dbcon,$chk_arr);
		if($chk_resp){
			echo '-1';
		}
		else{*/
			$info['cc_status']='2';
			$updateid=update_record('tbl_customer_category', $info,"cc_id=".$POST['eid'] , $dbcon);
		
			if($updateid)
			echo "1";
			else
			echo "0";	
		//}
	}
    
	
?>