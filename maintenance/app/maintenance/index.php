<?php
session_start(); //start session
$AJAX = true;
include('../../include/urlfileinner.php');
$incPath = $path.'include/';
$bulkAccessArray = canCheckPermissionAccess($dbcon, [
    MAINTENANCE_ADD_SLUG_UPDATE,
    MAINTENANCE_ADD_SLUG_DELETE
]);


$POST = ($_POST != NULL)? bulk_filter($dbcon,$_POST) : bulk_filter($dbcon,$_GET);

if(strtolower($POST['mode']) == "fetch") {
    if($POST['date']){
        $s_date=explode(' - ',$POST['date']);
        $start_date=$s_date[0];
        $end_date=$s_date[1];
    }else {
        $start_date = date('1-m-Y');
        $end_date = date("d-m-Y");
    } 

    $branch_id = $POST['branch_id'];
    $where='';
    if(!empty($start_date) && !empty($end_date)){
        $where.="  AND main.cdate BETWEEN '".date('Y-m-d 00:00:00',strtotime($start_date))."' AND '".date('Y-m-d 23:59:59',strtotime($end_date))."'";
    }
    
    $appData = array();
    $i=1;
    $aColumns = array('main.maintenance_id','main.maintenance_no','cust.l_name','main.product_icode','pro.product_name','main.ranges','main.use_status');
    $sIndexColumn = "main.maintenance_id";
    $isWhere = array("main.maintenance_status = 0  and main.company_id in (0,$_SESSION[company_id]) ".$where);
    $sTable = "tbl_maintenance as main";
    $isJOIN = array('left join product_mst as pro on pro.product_id = main.product_id left join tbl_ledger as cust on cust.l_id = main.cust_id');
    $hOrder = "main.maintenance_id desc";
    $hGroupby = array("main.maintenance_id");
    include($incPath.'pagging.php');
    //$appData = array();
    $id=1;

    foreach($sqlReturn as $row) {
        $row_data = array();
        $row_data[] = $row['sr'];
        $row_data[] = $row['maintenance_no'];
        $row_data[] = $row['l_name'];
        $row_data[] = $row['product_icode'];
        $row_data[] = $row['product_name'];
        $row_data[] = $row['ranges'];
        $row_data[] = (($row['use_status']==1) ? "In Use" : "No Use");
        $edit = $delete = $calibration = $tracking = '';
        if(in_array(MAINTENANCE_ADD_SLUG_UPDATE,$bulkAccessArray)){
            $edit = '<a class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" href="'.ROOT.MAINTENANCE_ROOT.'maintenance_edit/'.$row['maintenance_id'].'"><i class="fa fa-pencil"></i></a>';
        }
        if(in_array(MAINTENANCE_ADD_SLUG_DELETE,$bulkAccessArray)){
            $delete = '<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_maintenance('.$row['maintenance_id'].',\''.$row['maintenance_no'].'\')"><i class="fa fa-trash-o"></i></button>';
        }
        if($row['use_status']==1){
            $calibration = '<a class="btn btn-xs btn-primary" data-original-title="Add Calibration" data-toggle="tooltip" data-placement="top" href="'.ROOT.MAINTENANCE_ROOT.'calibration_add/'.$row['maintenance_id'].'" target="_blank"><i class="fa fa-plus-circle"></i></a>';
        }
        $tracking='<a class="btn btn-xs btn-default" data-original-title="Calibration Tracking" data-toggle="tooltip" data-placement="top" style="background-color: purple; color: white; border: 1px solid purple;" href="'.ROOT.MAINTENANCE_ROOT.'calibration_tracking/'.$row['maintenance_id'].'" target="_blank"><i class="fa fa-history"></i></a>';

        $row_data[] = $edit.' '.$delete.' '.$calibration.' '.$tracking;

        $appData[] = $row_data;
        $id++;
    }
    $output['aaData'] = $appData;
    echo json_encode( $output );
}
else if(strtolower($POST['mode']) == "add") {
    $branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

    $info['maintenance_no']     = load_maintenance_no($dbcon);//$_POST['maintenance_no'];
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
    $info['remind_before']		= $POST['remind_before'];
    $info['calibration_req']    = $POST['calibration_req'];
    $info['use_status']         = $POST['use_status'];
    $info['location']           = $_POST['location'];
    $info['remark']             = $_POST['remark'];
    $info['cdate']			    = date("Y-m-d H:i:s");
    $info['user_id']		    = $_SESSION['user_id'];
    $info['company_id']		    = $_SESSION['company_id'];

    // echo "<pre>";var_dump($POST);var_dump($info);die;
    $maintenance_id=add_record('tbl_maintenance', $info, $dbcon, $branch_id);
    if($maintenance_id){	
        $arr['msg']="1";
        $infodate['maintenance_id'] = $maintenance_id;
        $infodate['calculate_date'] = date('Y-m-d',strtotime($POST['bill_date']));
        $infodate['user_id']            = $_SESSION['user_id'];
        $infodate['company_id']         = $_SESSION['company_id'];
        $infodate['cdate']              = date("Y-m-d H:i:s");

        $cali_date_id=add_record('tbl_calibration_date_trn', $infodate, $dbcon);
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

        if($POST['use_status']==1){
            $infodate['calculate_date'] = date('Y-m-d',strtotime($POST['bill_date']));
            $infodate['user_id']            = $_SESSION['user_id'];
            $infodate['company_id']         = $_SESSION['company_id'];

            $cali_date_id=update_record('tbl_calibration_date_trn', $infodate, "calibration_date_trn_status = 0 AND maintenance_id=".$POST['eid'], $dbcon);
        }
    }
    else{
        $arr['msg']=0;
    }

    echo json_encode($arr);
}
else if(strtolower($POST['mode']) == "delete") {
    $info['maintenance_status']	= 2;
    $updateid = update_record('tbl_maintenance', $info, "maintenance_id=".$POST['maintenance_id'], $dbcon);

    if($updateid)
        echo "1";	
    else
        echo "0";			
}
else if(strtolower($POST['mode'])== "load_product_dtls") {
    $pro_qry="select pro.*, dwg.drawing_number from product_mst as pro left join tbl_drawing as dwg on dwg.drawing_id=pro.drawing_id where product_id=".$POST['product_id'];
    $pro_rel=mysqli_fetch_assoc($dbcon->query($pro_qry));
    echo json_encode($pro_rel);
}
else if(strtolower($POST['mode'])== "get_series_no") {
    $res['series_no'] = load_maintenance_no($dbcon);
    echo json_encode($res);
}

/* Inquiry Related Functions */
function load_maintenance_no($dbcon){
	//Load no by Type ID
	$row=array();
	$query1="select * from tbl_invoicetype where status=0 and type_id=52 and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id'];
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
