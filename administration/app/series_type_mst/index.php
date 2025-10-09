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
	    	ADMINISTRATOR_SERIES_TYPE_UPDATE,
	        ADMINISTRATOR_SERIES_TYPE_DELETE
	    ]);
	    $branch_id = $POST['branch_id'];
	    $where='';
		if($branch_id != '1000'){
	        $where .= check_branch('imst',$branch_id);
	    }
	    // if($branch_id == ""){
	    // 	 $output = array(
		   //      "sEcho" => 1,
		   //      "iTotalRecords" => 0,
		   //      "iTotalDisplayRecords" => 0,
		   //      "aaData" => array()
		   //  );
	     	
	    //  	echo json_encode( $output );
	    // }else{
			//check_user('imst')    mst data not user wise show 
		$appData = array();
		$i=1;
		$aColumns = array('imst.invoicetype_id','imst.gst_code','imst.invoice_type','imst.taxinvoice_start','imst.format_value','imst.type_id','imst.exciseinvoice_start','imst.invoice_format','imst.end_format_value','imst.deletable','imst.cdate', 'imst.status', 'imst.user_id','imst.financial_year_id','fyear.fiancial_year');
		$sIndexColumn = "invoicetype_id";
		$isWhere = array("imst.status = 0 and imst.company_id=$_SESSION[company_id]".$where);
		$sTable = "tbl_invoicetype as imst";			
		$isJOIN = array('left join tbl_financial_year as fyear ON fyear.financial_year_id = imst.financial_year_id');
		$hOrder = "imst.invoicetype_id desc";
		include($include.'pagging.php');
		$appData = array();
		$id=1;
		foreach($sqlReturn as $row) {
			$row_data = array();
			$row_data[] = $row['sr'];
			$row_data[] = $row['invoice_type'];
			$row_data[] = $row['taxinvoice_start'];
			if($row['invoice_format']=="1")
			{
				$row_data[] =$row['format_value'].$row['taxinvoice_start'];
			}
			else if($row['invoice_format']=="2")
			{
				$row_data[] =$row['taxinvoice_start'].$row['format_value'];
			}
			else if($row['invoice_format']=="3")
			{
				$row_data[] =$row['format_value'].$row['taxinvoice_start'].$row['end_format_value'];
			}
			else
			{
				$row_data[] = '';
			}
			
			$row_data[] = $row['gst_code'];
			$row_data[] = $row['fiancial_year'];
			$edit_btn='';$delete_btn='';
			if(in_array(ADMINISTRATOR_SERIES_TYPE_UPDATE,$bulkAccessArray)){
				$edit_btn='<button class="btn btn-xs btn-warning" data-original-title="Edit" data-toggle="tooltip" data-placement="top" onClick="edit_test('.$row['invoicetype_id'].');"><i class="fa fa-pencil"></i></button>';
			}
			if(in_array(ADMINISTRATOR_SERIES_TYPE_DELETE,$bulkAccessArray)){
				$delete_btn='<button class="btn btn-xs btn-danger" data-original-title="Delete" data-toggle="tooltip" data-placement="top" onClick="delete_catalog('.$row['invoicetype_id'].')"><i class="fa fa-trash-o"></i></button>';
			}
			
			
			if($row['deletable']=='1')
			{
				$delete_btn='';
			}
			
			if($row['type_id']=="1"){
				// $printcheckbox='<input type="checkbox" class="form-control" style="width:26px;" id="allchk'.$id.'" name="chk" value="'.$row["invoicetype_id"].'">';
				}else{
				$printcheckbox=''; 
			}
			
			$row_data[] = $edit_btn.' '.$delete_btn . $printcheckbox;
			
			$appData[] = $row_data;
			$id++;
		}
		$output['aaData'] = $appData;
		echo json_encode( $output );
	// }
	}
	else if(strtolower($POST['mode']) == "add") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];
		
		$tr = $dbcon -> query("SELECT `invoicetype_id`,`invoice_type`,`status` FROM `tbl_invoicetype` WHERE status=0 and `invoice_type` = '".$POST['invoicetype_name']."' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['invoice_type']		= $POST['invoicetype_name'];
			$info['taxinvoice_start']	= $POST['taxinvoicestart'];
			$info['exciseinvoice_start']= $POST['exciseinvoicestart'];
			$info['type_id']			= $POST['type_id'];
			$info['invoice_format'] 	= $POST['invoice_format'];	
			$info['format_value']		= $POST['format_value'];	
			$info['end_format_value'] 	= $POST['end_format_value'];							
			$info['gst_code'] 			= $POST['gst_code'];							
			$info['cdate']				= date("Y-m-d H:i:s");
			$info['user_id']			= $_SESSION['user_id'];
			$info['usertype_id']		= $_SESSION['user_type'];
			$info['company_id']			= $_SESSION['company_id'];
			$info['financial_year_id']	= $_SESSION['financial_year_id'];
			$inserid=add_record('tbl_invoicetype', $info, $dbcon, $branch_id);
			if($inserid)
			echo "1";
			else
			echo "0";
		}
		
	}
	else if(strtolower($POST['mode']) == "preedit") {	
		$q = $dbcon -> query("SELECT * FROM `tbl_invoicetype` WHERE `invoicetype_id` = '$POST[id]'");
		$r = $q->fetch_assoc();
		echo json_encode($r);
	}
	
	else if(strtolower($POST['mode']) == "edit") {
		$branch_id = ($_SESSION['user_type'] == '2' && isset($POST['branch_id']) && $POST['branch_id']) ? $POST['branch_id'] : $_SESSION['branch_id'];

		$tr = $dbcon -> query("SELECT `invoicetype_id`,`invoice_type`,`status` FROM `tbl_invoicetype` WHERE status=0 and `invoice_type` = '".$POST['invoicetype_name']."' and `invoicetype_id` != '".$POST['eid']."' and company_id=".$_SESSION['company_id']." AND financial_year_id = ".$_SESSION['financial_year_id']);
		if($tr->num_rows > 0) {
			echo '-1';
		} else {
			$info['invoice_type']		= $POST['invoicetype_name'];
			$info['taxinvoice_start']	= $POST['taxinvoicestart'];
			$info['exciseinvoice_start']= $POST['exciseinvoicestart'];
			$info['type_id']			= $POST['type_id'];
			$info['invoice_format']		= $POST['invoice_format'];
			$info['format_value']		= $POST['format_value'];
			$info['end_format_value']	= $POST['end_format_value'];
			$info['gst_code'] 			= $POST['gst_code'];	
			$info['cdate']			= date("Y-m-d H:i:s");
			$info['user_id']		= $_SESSION['user_id'];
			$info['usertype_id']	= $_SESSION['user_type'];
			$info['company_id']	= $_SESSION['company_id'];
			$info['financial_year_id']	= $_SESSION['financial_year_id'];
			$updateid=update_record('tbl_invoicetype', $info,"invoicetype_id=".$POST['eid'] , $dbcon, $branch_id);
			if($updateid)
				echo "1";
			else
				echo "0".$dbcon->error;
		}
		
		
	}
	else if(strtolower($POST['mode']) == "invoice_series_same") {
		$id=implode(",",$POST['typeid']);
		$upd="update tbl_invoicetype set series_same=0 where company_id=".$_SESSION['company_id'];
		$rose = $dbcon -> query($upd);
		$query="SELECT MAX(`taxinvoice_start`) as series FROM tbl_invoicetype WHERE invoicetype_id in (".$id.") and company_id=".$_SESSION['company_id'];
		$res = $dbcon -> query($query);
		$ro=mysqli_fetch_assoc($res);
		//echo $ro['series'];
		$update="update tbl_invoicetype set series_same=1,taxinvoice_start=".$ro['series']." where invoicetype_id in (".$id.") and company_id=".$_SESSION['company_id'];
		$ros = $dbcon -> query($update);
		if($ros){
			$row['status']=1;
			}else{
			$row['status']=0;
		}	
		echo json_encode($row);	
	}
	else if(strtolower($POST['mode']) == "delete") {
		$info['status']='2';
		$updateid=update_record('tbl_invoicetype', $info,"invoicetype_id=".$POST['eid'] , $dbcon);
		if($updateid)
		echo "1";
		else
		echo "0";
		
	}
	
	
?>