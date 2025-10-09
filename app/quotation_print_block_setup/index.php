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
		QUOTATION_PRINT_BLOCK_SETUP_UPDATE,
		QUOTATION_PRINT_BLOCK_SETUP_DELETE
	]);

	$appData = array();
	$i=1;
	$aColumns = array('tblqps.quotation_print_setup_id','tblqps.priority','tblqps.quotation_print_block_id','tblqpb.block_name','tblqps.status',);
	$sIndexColumn = "tblqps.quotation_print_setup_id";
	$isWhere = array("tblqps.status = 0  and tblqps.company_id in (0,$_SESSION[company_id])");
	$sTable = "tbl_quotation_print_setup as tblqps";			
	$isJOIN = array('LEFT JOIN tbl_quotation_print_block AS tblqpb ON tblqpb.quotation_print_block_id=tblqps.quotation_print_block_id');
	$hOrder = "tblqps.priority ASC";
	include('../../include/pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['block_name'];
		$row_data[] = $row['priority'];

		$edit_btn=''; $delete_btn='';  
		if(in_array(QUOTATION_PRINT_BLOCK_SETUP_UPDATE,$bulkAccessArray)){
			$edit_btn=' <button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_quotation_print_block_setup('.$row['quotation_print_setup_id'].');"><i class="fa fa-pencil"></i></button>'; 
		}
		if(in_array(QUOTATION_PRINT_BLOCK_SETUP_DELETE,$bulkAccessArray)){
			$delete_btn=' <button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_quotation_print_block_setup('.$row['quotation_print_setup_id'].')"><i class="fa fa-trash-o"></i></button>'; 
		}

		$row_data[] = $edit_btn.' '.$delete_btn; 
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {

	$branch_id = $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `quotation_print_setup_id`,`quotation_print_block_id`,`status`,`priority` FROM `tbl_quotation_print_setup` WHERE `quotation_print_block_id` ='".$POST['quotation_print_block_id']."' and status='0' and `company_id`='".$_SESSION['company_id']."'");
	if($tr->num_rows > 0) {

		$resp['resp']= '-1';

	}
	else {
		$info['priority']	= $POST['priority'];
		$info['quotation_print_block_id']	= $POST['quotation_print_block_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['user_id']	= $_SESSION['user_id'];
		$inserid=add_record('tbl_quotation_print_setup', $info, $dbcon, $branch_id);

		if($inserid){
				//Insert LOG
				//$log_entry=common_log_entry($dbcon,"quotation_print_block_mst_add",1,"tbl_quotation_print_setup",$inserid);
			if(strtolower($POST['variant_mst_model']) == "variant_mst_model"){
				$sel_qry="select * from tbl_quotation_print_setup where quotation_print_setup_id=".$inserid;
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
	$q = $dbcon -> query("SELECT * FROM `tbl_quotation_print_setup` WHERE `quotation_print_setup_id` = '".$POST['id']."'");
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {

	$branch_id = $_SESSION['branch_id'];

	$tr = $dbcon -> query("SELECT `quotation_print_setup_id`,`quotation_print_block_id`,`status`,`priority` FROM `tbl_quotation_print_setup` WHERE `quotation_print_block_id` ='".$POST['e_quotation_print_block_id']."' and status='0' and `quotation_print_setup_id` != '".$POST['eid']."'  and `company_id`='".$_SESSION['company_id']."'");
	if($tr->num_rows > 0) {

		echo "-1";

	} else {
		$info['priority']	= $POST['e_priority'];
		$info['quotation_print_block_id']	= $POST['e_quotation_print_block_id'];
		$info['company_id']	= $_SESSION['company_id'];
		$info['user_id']	= $_SESSION['user_id'];
			//$info['updated_at']	= date('Y-m-d H:i:s');			
		$updateid=update_record('tbl_quotation_print_setup', $info,"quotation_print_setup_id=".$POST['eid'] , $dbcon, $branch_id);

			//Insert LOG
			//$log_entry=common_log_entry($dbcon,"quotation_print_block_mst_add",2,"tbl_quotation_print_setup",$POST['eid']);

		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
	}

}
else if(strtolower($POST['mode']) == "delete") {
	$info['status']='2';
	$updateid=update_record('tbl_quotation_print_setup', $info,"quotation_print_setup_id=".$POST['eid'] , $dbcon);

			//Insert LOG
			//$log_entry=common_log_entry($dbcon,"quotation_print_block_mst_add",3,"tbl_quotation_print_setup",$POST['eid']);

	if($updateid)
		echo "1";
	else
		echo "0";
}
else if(strtolower($POST['mode']) == "show_data"){
	$res="";
	$query="SELECT tblqps.*, qpb.block_name as name, qpb.block_formate as formate FROM `tbl_quotation_print_setup` AS tblqps LEFT JOIN tbl_quotation_print_block AS qpb ON qpb.quotation_print_block_id=tblqps.quotation_print_block_id WHERE tblqps.company_id = '".$_SESSION['company_id']."' AND tblqps.status='0' ORDER BY tblqps.priority ASC";
	$q = $dbcon -> query($query);
	while($row=mysqli_fetch_assoc($q)){
		$res.=stripcslashes(mysqli_real_escape_string($dbcon,$row['formate']));
		$res.="<br><br>";
	}

	echo $res;
}

?>