<?php
session_start(); //start session
$AJAX = true;
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");

if($_POST != NULL) {
	$POST = bulk_filter($dbcon,$_POST);
}
else {
	$POST = bulk_filter($dbcon,$_GET);
}

if(strtolower($POST['mode']) == "fetch") {
		//check paermission for party industry add
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		QUOTATION_PRINT_BLOCK_MST_UPDATE,
		QUOTATION_PRINT_BLOCK_MST_DELETE
	]);

	$appData = array();
	$i=1;

	$aColumns = array('tblqpb.quotation_print_block_id','tblqpb.block_name','tblqpb.status','tblqpb.block_formate','tblqpb.is_delete');
	$sIndexColumn = "tblqpb.quotation_print_block_id";
	$isWhere = array("tblqpb.status = 0  and tblqpb.company_id in (0,$_SESSION[company_id])");
	$sTable = "tbl_quotation_print_block as tblqpb";			
	$isJOIN = array();
	$hOrder = "tblqpb.quotation_print_block_id ASC";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['block_name'];
		// $row_data[] = nl2br(stripcslashes($row['block_formate']));

		$edit_btn=''; $delete_btn='';  
		if(in_array(QUOTATION_PRINT_BLOCK_MST_UPDATE,$bulkAccessArray)){
			$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_quotation_print_block_mst('.$row['quotation_print_block_id'].');"><i class="fa fa-pencil"></i></button>'; 
		}
		if(in_array(QUOTATION_PRINT_BLOCK_MST_DELETE,$bulkAccessArray)){
			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_quotation_print_block_mst('.$row['quotation_print_block_id'].')"><i class="fa fa-trash-o"></i></button>'; 
		}
		if($row['is_delete']==0){
			$row_data[] = $edit_btn.' '.$delete_btn; 
		}
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {

	$branch_id = $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `quotation_print_block_id`,`block_name`,`status`,`block_formate` FROM `tbl_quotation_print_block` WHERE `block_name` ='".$POST['block_name']."' and status='0' and `company_id`='".$_SESSION['company_id']."'");
	if($tr->num_rows > 0) {

		$resp['resp']= '-1';

	}
	else {
		$info['block_formate']	= text_rnremove(stripcslashes(mysqli_real_escape_string($dbcon,$_POST['block_formate'])));
		$info['block_name']	= $_POST['block_name'];
		$info['block_type']	= $_POST['block_type'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['user_id']	= $_SESSION['user_id'];
		$inserid=add_record('tbl_quotation_print_block', $info, $dbcon, $branch_id);

		if($inserid){
				//Insert LOG
				//$log_entry=common_log_entry($dbcon,"quotation_print_block_mst_add",1,"tbl_quotation_print_block",$inserid);
			if(strtolower($POST['variant_mst_model']) == "variant_mst_model"){
				$sel_qry="select * from tbl_quotation_print_block where quotation_print_block_id=".$inserid;
				$sel_rel=mysqli_fetch_assoc($dbcon->query($sel_qry));
				$resp=$sel_rel;
				$resp['resp']= "2";
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
	$q = $dbcon -> query("SELECT * FROM `tbl_quotation_print_block` WHERE `quotation_print_block_id` = '".$POST['id']."'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {

	$branch_id = $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `quotation_print_block_id`,`block_name`,`status`,`block_formate` FROM `tbl_quotation_print_block` WHERE `block_name` ='".$_POST['e_block_name']."' and status='0' and `quotation_print_block_id` != '".$_POST['eid']."'  and `company_id`='".$_SESSION['company_id']."'");
	if($tr->num_rows > 0) {

		echo "-1";

	} else {
		$info['block_formate']	= text_rnremove(stripcslashes(mysqli_real_escape_string($dbcon,$_POST['e_block_formate'])));
		$info['block_name']	= $_POST['e_block_name'];
		$info['block_type']	= $_POST['e_block_type'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['user_id']	= $_SESSION['user_id'];
			//$info['updated_at']	= date('Y-m-d H:i:s');			
		$updateid=update_record('tbl_quotation_print_block', $info,"quotation_print_block_id=".$POST['eid'] , $dbcon, $branch_id);

			//Insert LOG
			//$log_entry=common_log_entry($dbcon,"quotation_print_block_mst_add",2,"tbl_quotation_print_block",$POST['eid']);

		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
	}

}
else if(strtolower($POST['mode']) == "delete") {
	$info['status']='2';
	$updateid=update_record('tbl_quotation_print_block', $info,"quotation_print_block_id=".$POST['eid'] , $dbcon);

	//Insert LOG
	//$log_entry=common_log_entry($dbcon,"quotation_print_block_mst_add",3,"tbl_quotation_print_block",$POST['eid']);

	if($updateid)
		echo "1";
	else
		echo "0";
}
else if(brp_strtolower($POST['mode']) == "get_insert_tags_data") {
	
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION NO'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION NO');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION DATE'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION DATE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'INQUIRY REF NO'.EMAIL_INSERT_TAG_POSTFIX , 'INQUIRY REF NO');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'INQUIRY REF DATE'.EMAIL_INSERT_TAG_POSTFIX , 'INQUIRY REF DATE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'CUSTOMER NAME'.EMAIL_INSERT_TAG_POSTFIX , 'CUSTOMER NAME');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'CUSTOMER ADDRESS'.EMAIL_INSERT_TAG_POSTFIX , 'CUSTOMER ADDRESS');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'CUSTOMER GST NO'.EMAIL_INSERT_TAG_POSTFIX , 'CUSTOMER GST NO');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'USER DETAIL'.EMAIL_INSERT_TAG_POSTFIX , 'USER DETAIL');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'COMPANY NAME'.EMAIL_INSERT_TAG_POSTFIX , 'COMPANY NAME');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'COMPANY GST NO'.EMAIL_INSERT_TAG_POSTFIX , 'COMPANY GST NO');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'ANNEXTURE DETAIL'.EMAIL_INSERT_TAG_POSTFIX , 'ANNEXTURE DETAIL');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'TC TITLE'.EMAIL_INSERT_TAG_POSTFIX , 'TC TITLE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'TC DESCRIPTION'.EMAIL_INSERT_TAG_POSTFIX , 'TC DESCRIPTION');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT NAME'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT NAME');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT DESCRIPTION'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT DESCRIPTION');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT SPECIFICATION'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT SPECIFICATION');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT HSN CODE'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT HSN CODE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT QTY'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT QTY');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT RATE'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT RATE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT DISCOUNT'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT DISCOUNT');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT TOTAL'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT TOTAL');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PRODUCT IMAGE'.EMAIL_INSERT_TAG_POSTFIX , 'PRODUCT IMAGE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'TOTAL AMOUNT'.EMAIL_INSERT_TAG_POSTFIX , 'TOTAL AMOUNT');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'TOTAL BASIC AMOUNT'.EMAIL_INSERT_TAG_POSTFIX , 'TOTAL BASIC AMOUNT');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'TOTAL QTY'.EMAIL_INSERT_TAG_POSTFIX , 'TOTAL QTY');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'TOTAL IN WORDS'.EMAIL_INSERT_TAG_POSTFIX , 'TOTAL IN WORDS');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION SUBJECT'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION SUBJECT');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION REMARK'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION REMARK');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION ADDRESS'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION ADDRESS');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION HEADER GREETING'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION HEADER GREETING');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'QUOTATION FOOTER GREETING'.EMAIL_INSERT_TAG_POSTFIX , 'QUOTATION FOOTER GREETING');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'PAYMENT TERMS'.EMAIL_INSERT_TAG_POSTFIX , 'PAYMENT TERMS');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'DISPATCH MODE'.EMAIL_INSERT_TAG_POSTFIX , 'DISPATCH MODE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'DELIVERY DATE'.EMAIL_INSERT_TAG_POSTFIX , 'DELIVERY DATE');
	$insert_tag[] = array(EMAIL_INSERT_TAG_PREFIX.'SR NO'.EMAIL_INSERT_TAG_POSTFIX , 'SR NO');
	
	echo json_encode($insert_tag);
}

?>