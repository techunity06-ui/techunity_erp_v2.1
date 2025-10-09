<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
// $bulkAccessArray = canCheckPermissionAccess($dbcon, [
//     INQUIRY_SLUG_EDIT,
//     INQUIRY_SLUG_DELETE
// ]);


$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "add") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

    $info['calibration_req_no']     = load_calibration_no($dbcon);//$_POST['maintenance_no'];
    $info['calibration_req_date']   = date('Y-m-d',strtotime($POST['calibration_req_date']));
    $info['cust_id']            = $POST['cust_id'];
    $info['bill_no']            = $_POST['bill_no'];
    $info['bill_date']          = date('Y-m-d',strtotime($POST['bill_date']));
    $info['remind_date']          = date('Y-m-d',strtotime($POST['remind_date']));
    $info['due_date']          = date('Y-m-d',strtotime($POST['due_date']));
    $info['tc_date']          = date('Y-m-d',strtotime($POST['tc_date']));
    $info['amount']              = $POST['amount'];
    $info['lci_used'] = $POST['lci_used'];
    $info['acceptance']		= $POST['acceptance'];
    $info['cdate']			    = date("Y-m-d H:i:s");
    $info['user_id']		    = $_SESSION['user_id'];
    $info['company_id']		    = $_SESSION['company_id'];

    // echo "<pre>";var_dump($POST);var_dump($info);die;
    $calibration_id=add_record('tbl_calibration', $info, $dbcon, $branch_id);
    if($calibration_id){	
        $arr['msg']="1";
        $infodate['calibration_id'] = $calibration_id;
        $infodate['calibration_date'] = date('Y-m-d',strtotime($POST['calibration_req_date']));
        $infodate['user_id']            = $_SESSION['user_id'];
        $infodate['company_id']         = $_SESSION['company_id'];
        $infodate['calibration_date_trn_status'] = 1;

        $cali_date_id=update_record('tbl_calibration_date_trn', $infodate, "calibration_date_trn_status = 0 AND maintenance_id=".$POST['maintenance_id'], $dbcon);
        if($cali_date_id){
            $infocalidate['maintenance_id'] = $POST['maintenance_id'];
            $infocalidate['calculate_date'] = date('Y-m-d',strtotime($POST['calibration_req_date']));
            $infocalidate['user_id']            = $_SESSION['user_id'];
            $infocalidate['company_id']         = $_SESSION['company_id'];
            $infocalidate['cdate']              = date("Y-m-d H:i:s");

            $cali_date_ids=add_record('tbl_calibration_date_trn', $infocalidate, $dbcon);
        }
    }
    else{
        $arr['msg']="0";
    }
    echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "edit") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
    
    // $info['maintenance_no']     = $_POST['maintenance_no'];
    $info['maintenance_date']   = date('Y-m-d',strtotime($POST['maintenance_date']));
    $info['cust_id']            = $POST['cust_id'];
    $info['product_id']         = $POST['product_id'];
    $info['product_category']   = $POST['product_category'];
    $info['product_icode']      = $_POST['product_icode'];
    $info['drawing_no']         = $_POST['drawing_no'];
    $info['ranges']              = $_POST['ranges'];
    $info['make']               = $_POST['make'];
    $info['accuracy']           = $_POST['accuracy'];
    $info['modal']              = $_POST['modal'];
    $info['use_for']            = $_POST['use_for'];
    $info['bill_no']            = $_POST['bill_no'];
    $info['bill_date']          = date('Y-m-d',strtotime($POST['bill_date']));
    $info['price']              = $POST['price'];
    $info['calibration_period'] = $POST['calibration_period'];
    $info['remind_before']      = $POST['remind_before'];
    $info['calibration_req']    = $POST['calibration_req'];
    $info['use_status']         = $POST['use_status'];
    $info['location']           = $_POST['location'];
    $info['remark']             = $_POST['remark'];
    // $info['cdate']              = date("Y-m-d H:i:s");
    $info['user_id']            = $_SESSION['user_id'];
    $info['company_id']         = $_SESSION['company_id'];
    $updateid = update_record('tbl_maintenance', $info, "maintenance_id=".$POST['eid'], $dbcon);
    if($updateid){
        $arr['msg']="update";

        //$infodate['maintenance_id'] = $maintenance_id;
        $infodate['calculate_date'] = date('Y-m-d',strtotime($POST['bill_date']));
        $infodate['user_id']            = $_SESSION['user_id'];
        $infodate['company_id']         = $_SESSION['company_id'];

        $cali_date_id=update_record('tbl_calibration_date_trn', $infodate, "calibration_date_trn_status = 0 AND maintenance_id=".$POST['eid'], $dbcon);
    }
    else{
        $arr['msg']=0;
    }

    echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "delete") {
    $info['calibration_status']	= 2;
    $updateid = update_record('tbl_calibration', $info, "calibration_id=".$POST['calibration_id'], $dbcon);

    if($updateid)
        echo "1";	
    else
        echo "0";			
}
else if(strtolower($POST['mode'])== "get_series_no") {
    $res['series_no'] = load_calibration_no($dbcon);
    echo json_encode($res);
}

/* Inquiry Related Functions */
function load_calibration_no($dbcon){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=53 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
	$rows=mysqli_fetch_assoc($dbcon->query($query1));
	$id=$rows['taxinvoice_start'];
	$id=$id+1;
	if($rows['invoice_format']=='2'){
		$row['invoiceno']= str_pad($id,4,"0",STR_PAD_LEFT).$rows['format_value'];
	}
	else if($rows['invoice_format']=='1'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT);
	}
	else if($rows['invoice_format']=='3'){
		$row['invoiceno']=$rows['format_value'].str_pad($id,3,"0",STR_PAD_LEFT).$rows['end_format_value'];
	}
	else{
		$row['invoiceno']=str_pad($id,3,"0",STR_PAD_LEFT);
	}
	return $row['invoiceno'];
}
function upload_attch_file($FILES){
    $rand=rand(0,99999999);
    if(!empty($FILES['inq_attch_file']['tmp_name'])) {
        $temp = explode(".", $FILES["inq_attch_file"]["name"]);
        $extension = strtolower(end($temp));
        $File = "inq_attch_".$rand.".".$extension;
        $tmp_name = $FILES["inq_attch_file"]["tmp_name"];
        move_uploaded_file($tmp_name,'..//'.INQ_ATTACH_UPING.$File);

        return  $File;				
    }
}
?>
