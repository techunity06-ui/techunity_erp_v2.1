<?php
session_start(); //start session
$AJAX = true;
include("../../../config/config.php");
include("../../../config/session.php");
//if(@isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') { 
    //if(@isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],DOMAIN) !== false) {
	//print_r($_POST);
	if($_POST != NULL) {
		$POST = bulk_filter($dbcon,$_POST);
	}
	else {
		$POST = bulk_filter($dbcon,$_GET);
	}
	if(strtolower($POST['type']) == "fetch") {
	    $appData = array();
	    $i=1;
	    $aColumns = array('design_id','bankid', 'bank_name','design_of');
	    $sIndexColumn = "design_id";
	    $sTable = "coro_design";
	    $isWhere = array();
	    $isJOIN = array("INNER JOIN `bank_mst` ON `bankid` = `design_bank`");

	    include('pagging.php');
	    $appData = array();
	    $id=1;
	    foreach($sqlReturn as $row) {
		$row_data = array();
		$row_data[] = $row['sr'];
		$row_data[] = $row['bank_name'];
		$btn = '<button class="btn btn-xs btn-primary" data-original-title="Open Preview" data-toggle="tooltip" data-placement="top" onClick="preview_design('.$row['bankid'].')"><i class="fa fa-globe"></i></button>';
			if($row['design_of']!="0" || ($_SESSION[user_id]=="0" && $row['design_of']=="0") )
			{
			$btn.='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="window.open(\''.DOMAIN_CHEQUE.'edit-design/'.$row['design_id'].'\');"><i class="fa fa-pencil"></i></button><button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_design('.$row['design_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
		$row_data[]=$btn;
		$appData[] = $row_data;
		$id++;
	    }
	    $output['aaData'] = $appData;
	    echo json_encode( $output );
	}
	else if(strtolower($POST['type']) == "delete") {
	    if($_POST['token'] == $_SESSION['token']) {
		$query = $dbcon -> query("DELETE FROM `coro_design` WHERE `design_id` = '$POST[id]' AND `design_of` = '$_SESSION[user_id]'");
		// "DELETE FROM `coro_design` WHERE `design_id` = '$POST[id]' AND `design_of` = '$_SESSION[user_id]'";
		if($query) {
		    echo "1";
		}
		else {
		    echo "0";
		}
	    }
	}
	else {
	    die("Error - 3");
	}
    /*}
    else {
        die("Error - 2");
    }
}
else {
    die("Error - 1");
}*/
?>