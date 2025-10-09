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
		//check permission for party industry add
	$bulkAccessArray = canCheckPermissionAccess($dbcon, [
		ADMINISTRATOR_BANK_MST_UPDATE,
		ADMINISTRATOR_BANK_MST_DELETE
	]);

	$appData = array();
	$i=1;
	$aColumns = array('bank.bankid', 'bank.bank_name','bank.bank_status');
	$sIndexColumn = "bank.bankid";
	$isWhere = array("bank.bank_status !=2");
	$sTable = "bank_mst as bank";
	$isJOIN = array();
	$hOrder = "bank.bank_name ASC";
	include($include.'pagging.php');
	$appData = array();
	$id=1;
	foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['bank_name'];

		$edit_btn='';$del_btn=''; 
		if($row['bankid']!='0'){ 
			if(in_array(ADMINISTRATOR_BANK_MST_UPDATE,$bulkAccessArray)){
				if($row['is_deletable']=='0')
				{
					$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_bank('.$row['bankid'].');"><i class="fa fa-pencil"></i></button>';
				}
			}
			if(in_array(ADMINISTRATOR_BANK_MST_DELETE,$bulkAccessArray)){
				if($row['is_deletable']=='0')
				{
					$del_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_bank('.$row['bankid'].')"><i class="fa fa-trash-o"></i></button>';
				}
			} 
		}



		$row_data[] = $pro_stck_btn.' '.$edit_btn.' '.$del_btn;
		$appData[] = $row_data;
		$id++;
	}
	$output['aaData'] = $appData;
	echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
	$tr = $dbcon -> query("SELECT `bankid`,`bank_name`,`bank_status` FROM `bank_mst` WHERE `bank_name` ='".$POST['bank_name']."' and `bank_status`=0 ");
	if($tr->num_rows > 0) {
		$r = $tr -> fetch_assoc();
		if($r['bank_status'] != 0) {
			$info['bank_status']=0;
			$updateid=update_record('bank_mst', $info,"bankid=".$r['bankid'] , $dbcon);	

			if($updateid)
				$row['res'] ="1";
			else
				$row['res'] ="0";
		}
		else {
			$row['res'] ="-1";
		}
	} else {
		$info['bank_name']		= $POST['bank_name'];							
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['userid']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$inserid=add_record('bank_mst', $info, $dbcon);

		if($inserid){
			$row['res'] ="1";
		}
		else{
			$row['res'] ="0";
		}
				
	}
	echo json_encode($row);
}
else if(strtolower($POST['mode']) == "preedit") {			
	$q = $dbcon -> query("SELECT * FROM `bank_mst` WHERE `bankid` = ".$POST['id']);
	$r = $q->fetch_assoc();
	echo json_encode($r);
}
else if(strtolower($POST['mode']) == "edit") {
	$tr = $dbcon -> query("SELECT `bankid`,`bank_name`,`bank_status` FROM `bank_mst` WHERE `bank_name` ='".$POST['bank_name']."' and `bankid` != '".$POST['eid']."' and `bank_status`=0");
	if($tr->num_rows > 0) {
		echo "-1";
	} else {
		$info['bank_name']		= $POST['bank_name'];
		$info['cdate']				= date("Y-m-d H:i:s");
		$info['userid']			= $_SESSION['user_id'];
		$info['company_id']			= $_SESSION['company_id'];
		$updateid=update_record('bank_mst', $info,"bankid=".$POST['eid'] , $dbcon);

		if($updateid)
			echo "1";
		else
			echo "0".$dbcon->error;
	}
}
else if(strtolower($POST['mode']) == "delete") {
	$info['bank_status']='2';
	$updateid=update_record('bank_mst', $info,"bankid=".$POST['eid'] , $dbcon);

	if($updateid)
		echo "1";
	else
		echo "0";
}
?>